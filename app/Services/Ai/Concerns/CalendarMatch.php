<?php

namespace App\Services\Ai\Concerns;

use App\Models\CalendarEvent;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * Taqvim va tadbirlarni qidirish uchun match metodlari.
 * AiService dan ajratilgan trait.
 */
trait CalendarMatch
{
    /**
     * Taqvimdagi tadbirlar: aniq sana (masalan 20 aprel) yoki «taqvim» so'zi bilan yaqinlashuvchi voqealar.
     */
    private function matchCalendarAndEvents(string $message): ?string
    {
        $q = $this->normalizeSearchText($message);

        $hasCalendarWords = Str::contains($q, [
            'taqvim', 'tadbir', 'kalendar', 'kalendr', 'sanada', 'voqea', 'voqe', 'jadval', 'calendar',
        ]);
        $hasWeekIntent = Str::contains($q, [
            'shu hafta', 'bu hafta', 'haftadagi', 'hafta ichida', 'week',
        ]);

        $parsedDate = $this->parseCalendarDateFromMessage($message);

        $hasDateQuestionIntent = Str::contains($q, [
            'nima', 'qanday', 'qachon', 'dars', 'kun', 'reja', 'uchrashuv', 'bo\'ladi', 'boladi', 'bo\'ladi',
            'boshlan', 'tugay', 'qanaqa',
        ]);

        if ($parsedDate === null && ! $hasCalendarWords && ! $hasWeekIntent) {
            return null;
        }

        // Sana topildi, lekin «taqvim» emas — faqat savol kontekstida (tug'ilgan kun va hok. chalkashmasin)
        if ($parsedDate !== null && ! $hasCalendarWords && ! $hasDateQuestionIntent) {
            return null;
        }

        $maxEvents = max(1, (int) config('ai.calendar_max_events_per_answer', 15));
        $maxBody = max(0, (int) config('ai.calendar_max_body_chars', 280));
        $calendarUrl = route('calendar');

        if ($hasWeekIntent) {
            $rows = CalendarEvent::query()
                ->whereBetween('event_date', [Carbon::now()->startOfWeek()->startOfDay(), Carbon::now()->endOfWeek()->endOfDay()])
                ->orderBy('event_date')
                ->orderBy('sort_order')
                ->orderBy('id')
                ->limit($maxEvents)
                ->get();

            if ($rows->isEmpty()) {
                return "📅 **Shu hafta** uchun taqvimda tadbir topilmadi.\n"
                    ."📆 To'liq jadval: {$calendarUrl}";
            }

            $lines = $rows->map(fn($ev) => '• ' . ($ev->event_date instanceof Carbon ? $ev->event_date : Carbon::parse($ev->event_date))->format('d.m.Y')
                . ' — ' . $this->formatCalendarEventLine($ev, $maxBody))->all();

            return "📅 **Shu haftadagi tadbirlar**:\n"
                . implode("\n\n", $lines)
                ."\n\n📆 To'liq jadval: {$calendarUrl}";
        }

        if ($parsedDate !== null) {
            $rows = CalendarEvent::query()
                ->whereDate('event_date', $parsedDate->format('Y-m-d'))
                ->orderBy('sort_order')
                ->orderBy('id')
                ->limit($maxEvents)
                ->get();

            if ($rows->isEmpty()) {
                return "📅 **{$parsedDate->format('d.m.Y')}** sanasi bo'yicha taqvimda tadbir yozuvi topilmadi.\n📆 To'liq jadval: {$calendarUrl}";
            }

            $lines = $rows->map(fn($ev) => $this->formatCalendarEventLine($ev, $maxBody))->all();

            return "📅 **{$parsedDate->format('d.m.Y')}** kuni taqvim bo'yicha:\n"
                .implode("\n\n", $lines)
                ."\n\n📆 Batafsil: {$calendarUrl}";
        }

        $rows = CalendarEvent::query()
            ->where('event_date', '>=', Carbon::today()->startOfDay())
            ->orderBy('event_date')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->limit($maxEvents)
            ->get();

        if ($rows->isEmpty()) {
            return "📆 Hozircha rejalashtirilgan yaqin tadbirlar yo'q.\nTaqvim: {$calendarUrl}";
        }

        $lines = $rows->map(fn($ev) => '• ' . ($ev->event_date instanceof Carbon ? $ev->event_date : Carbon::parse($ev->event_date))->format('d.m.Y') . ' — ' . $this->formatCalendarEventLine($ev, $maxBody))->all();

        return "📆 **Yaqinlashayotgan tadbirlar** (oxirgi {$maxEvents} ta):\n"
            .implode("\n\n", $lines)
            ."\n\n📆 To'liq taqvim: {$calendarUrl}";
    }

    private function formatCalendarEventLine(CalendarEvent $ev, int $maxBody): string
    {
        $title = localized_model_value($ev, 'title');
        $time = localized_model_value($ev, 'time_note');
        $body = localized_model_value($ev, 'body');
        $line = $title;
        if (filled($time)) {
            $line .= "\n  ⏱ ".$time;
        }
        if ($maxBody > 0 && filled($body)) {
            $plain = trim(preg_replace('/\s+/u', ' ', strip_tags((string) $body)) ?? '');
            $line .= "\n  ".Str::limit($plain, $maxBody);
        }

        return $line;
    }

    private function parseCalendarDateFromMessage(string $message): ?Carbon
    {
        $q = $this->normalizeSearchText($message);
        $tz = (string) config('app.timezone', 'UTC');

        if (preg_match('/\bbugun\b/u', $q)) {
            return Carbon::now($tz)->startOfDay();
        }
        if (preg_match('/\bertaga\b/u', $q)) {
            return Carbon::now($tz)->addDay()->startOfDay();
        }

        $year = (int) Carbon::now($tz)->year;
        if (preg_match('/\b(20[0-9]{2})\b/', $message, $ym)) {
            $y = (int) $ym[1];
            if ($y >= 2000 && $y <= 2100) {
                $year = $y;
            }
        }

        $monthRx = $this->calendarMonthRegexFragment();

        if (preg_match('/\b([1-9]|[12]\d|3[01])\s*[-]?\s*('.$monthRx.')\b/u', $q, $m)) {
            $day = (int) $m[1];
            $month = $this->monthNameToNumber($m[2]);
            if ($month !== null) {
                return $this->safeCalendarDate($year, $month, $day, $tz);
            }
        }

        if (preg_match('/\b('.$monthRx.')\s*[-]?\s*([1-9]|[12]\d|3[01])\b/u', $q, $m)) {
            $month = $this->monthNameToNumber($m[1]);
            $day = (int) $m[2];
            if ($month !== null) {
                return $this->safeCalendarDate($year, $month, $day, $tz);
            }
        }

        return null;
    }

    private function calendarMonthRegexFragment(): string
    {
        return 'yanvar(?:da|dan|dagi)?|fevral(?:da|dan|dagi)?|mart(?:da|dan|dagi)?|aprel(?:da|dan|dagi)?|april(?:da|dan|dagi)?'
            .'|may(?:da|dan|dagi)?|iyun(?:da|dan|dagi)?|iyul(?:da|dan|dagi)?|avgust(?:da|dan|dagi)?'
            .'|sentyabr(?:da|dan|dagi)?|oktyabr(?:da|dan|dagi)?|noyabr(?:da|dan|dagi)?|dekabr(?:da|dan|dagi)?';
    }

    private function monthNameToNumber(string $name): ?int
    {
        $base = $this->normalizeSearchText(preg_replace('/(da|dan|dagi)$/u', '', trim($name)) ?? '');
        $map = [
            'yanvar' => 1, 'fevral' => 2, 'mart' => 3, 'aprel' => 4, 'april' => 4,
            'may' => 5, 'iyun' => 6, 'iyul' => 7, 'avgust' => 8,
            'sentyabr' => 9, 'oktyabr' => 10, 'noyabr' => 11, 'dekabr' => 12,
        ];

        return $map[$base] ?? null;
    }

    private function safeCalendarDate(int $year, int $month, int $day, string $tz): ?Carbon
    {
        if ($day < 1 || $day > 31 || $month < 1 || $month > 12) {
            return null;
        }
        try {
            $d = Carbon::createFromDate($year, $month, $day, $tz)->startOfDay();
            if ((int) $d->day !== $day || (int) $d->month !== $month) {
                return null;
            }

            return $d;
        } catch (\Throwable) {
            return null;
        }
    }
}
