<?php

namespace App\Services\Ai;

use App\Models\AiKnowledge;
use App\Models\CalendarEvent;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\Exam;
use App\Models\Post;
use App\Models\User;
use App\Models\Teacher;
use App\Models\ContactMessage;
use App\Models\Result;
use App\Models\SiteSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class AiService
{
    private const GEMINI_CALLS_PER_MINUTE_SOFT_LIMIT = 14;

    /**
     * Main entry point for generating a response.
     */
    public function generateResponse(string $userMessage, ?object $user = null): array
    {
        $message = trim($userMessage);
        
        // 0. Smart analytics (intent-based, not keyword-locked)
        if ($analytics = $this->matchAnalyticalData($message)) {
            return ['success' => true, 'text' => $analytics, 'source' => 'analytics_data'];
        }

        // 0.05 Taqvim / sanaga bog‘liq tadbirlar (DB: calendar_events)
        if ($calendar = $this->matchCalendarAndEvents($message)) {
            return ['success' => true, 'text' => $calendar, 'source' => 'calendar_data'];
        }

        // 0.1 School profile / director / internal data summary
        if ($schoolData = $this->matchSchoolProfileData($message)) {
            return ['success' => true, 'text' => $schoolData, 'source' => 'school_profile'];
        }

        // 1. Try Local Machine/Static Knowledge (Greetings, Persona)
        if ($static = $this->matchStaticKnowledge($message)) {
            return ['success' => true, 'text' => $static, 'source' => 'static_knowledge'];
        }

        // 2. Universal Site Stats & Contact (New)
        if ($universal = $this->matchUniversalData($message)) {
            return ['success' => true, 'text' => $universal, 'source' => 'universal_data'];
        }

        // 3. Try Dynamic Data (Personal Results, Courses, Teachers) - Priority for "My Results"
        if ($dynamic = $this->matchDynamicData($message, $user)) {
            return ['success' => true, 'text' => $dynamic, 'source' => 'dynamic_data'];
        }

        // 4. Try Database Knowledge Base (Admin Managed Q&A)
        if ($dbResult = $this->matchDatabaseKnowledge($message)) {
            return ['success' => true, 'text' => $dbResult, 'source' => 'database_knowledge'];
        }

        // 5. Fallback to Gemini API
        return $this->callGemini($message, $user);
    }

    /**
     * Taqvimdagi tadbirlar: aniq sana (masalan 20 aprel) yoki «taqvim» so‘zi bilan yaqinlashuvchi voqealar.
     */
    private function matchCalendarAndEvents(string $message): ?string
    {
        $q = mb_strtolower(trim($message));

        $hasCalendarWords = Str::contains($q, [
            'taqvim', 'tadbir', 'kalendar', 'kalendr', 'sanada', 'voqea', 'voqe', 'jadval', 'calendar',
        ]);

        $parsedDate = $this->parseCalendarDateFromMessage($message);

        $hasDateQuestionIntent = Str::contains($q, [
            'nima', 'qanday', 'qachon', 'dars', 'kun', 'reja', 'uchrashuv', 'bo\'ladi', 'boladi', 'bo‘ladi',
            'boshlan', 'tugay', 'bo\'ladi', 'qanaqa',
        ]);

        if ($parsedDate === null && ! $hasCalendarWords) {
            return null;
        }

        // Sana topildi, lekin «taqvim» emas — faqat savol kontekstida (tug‘ilgan kun va hok. chalkashmasin)
        if ($parsedDate !== null && ! $hasCalendarWords && ! $hasDateQuestionIntent) {
            return null;
        }

        $maxEvents = max(1, (int) config('ai.calendar_max_events_per_answer', 15));
        $maxBody = max(0, (int) config('ai.calendar_max_body_chars', 280));
        $calendarUrl = route('calendar');

        if ($parsedDate !== null) {
            $rows = CalendarEvent::query()
                ->whereDate('event_date', $parsedDate->format('Y-m-d'))
                ->orderBy('sort_order')
                ->orderBy('id')
                ->limit($maxEvents)
                ->get();

            $dateLabel = $parsedDate->format('d.m.Y');
            if ($rows->isEmpty()) {
                return "📅 **{$dateLabel}** sanasi bo‘yicha taqvimda tadbir yozuvi topilmadi.\n"
                    . "📆 To‘liq jadval: {$calendarUrl}";
            }

            $lines = [];
            foreach ($rows as $ev) {
                $lines[] = $this->formatCalendarEventLine($ev, $maxBody);
            }

            return "📅 **{$dateLabel}** kuni taqvim bo‘yicha:\n"
                . implode("\n\n", $lines)
                . "\n\n📆 Batafsil: {$calendarUrl}";
        }

        $rows = CalendarEvent::query()
            ->where('event_date', '>=', Carbon::today()->startOfDay())
            ->orderBy('event_date')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->limit($maxEvents)
            ->get();

        if ($rows->isEmpty()) {
            return "📆 Hozircha rejalashtirilgan yaqin tadbirlar yo‘q.\n"
                . "Taqvim: {$calendarUrl}";
        }

        $lines = [];
        foreach ($rows as $ev) {
            $d = $ev->event_date instanceof Carbon ? $ev->event_date : Carbon::parse($ev->event_date);
            $lines[] = '• ' . $d->format('d.m.Y') . ' — ' . $this->formatCalendarEventLine($ev, $maxBody);
        }

        return "📆 **Yaqinlashayotgan tadbirlar** (oxirgi {$maxEvents} ta):\n"
            . implode("\n\n", $lines)
            . "\n\n📆 To‘liq taqvim: {$calendarUrl}";
    }

    private function formatCalendarEventLine(CalendarEvent $ev, int $maxBody): string
    {
        $title = localized_model_value($ev, 'title');
        $time = localized_model_value($ev, 'time_note');
        $body = localized_model_value($ev, 'body');
        $line = $title;
        if (filled($time)) {
            $line .= "\n  ⏱ " . $time;
        }
        if ($maxBody > 0 && filled($body)) {
            $plain = trim(preg_replace('/\s+/u', ' ', strip_tags((string) $body)) ?? '');
            $line .= "\n  " . Str::limit($plain, $maxBody);
        }

        return $line;
    }

    private function parseCalendarDateFromMessage(string $message): ?Carbon
    {
        $q = mb_strtolower($message);
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

        if (preg_match('/\b([1-9]|[12]\d|3[01])\s*[-]?\s*(' . $monthRx . ')\b/u', $q, $m)) {
            $day = (int) $m[1];
            $month = $this->monthNameToNumber($m[2]);
            if ($month !== null) {
                return $this->safeCalendarDate($year, $month, $day, $tz);
            }
        }

        if (preg_match('/\b(' . $monthRx . ')\s*[-]?\s*([1-9]|[12]\d|3[01])\b/u', $q, $m)) {
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
            . '|may(?:da|dan|dagi)?|iyun(?:da|dan|dagi)?|iyul(?:da|dan|dagi)?|avgust(?:da|dan|dagi)?'
            . '|sentyabr(?:da|dan|dagi)?|oktyabr(?:da|dan|dagi)?|noyabr(?:da|dan|dagi)?|dekabr(?:da|dan|dagi)?';
    }

    private function monthNameToNumber(string $name): ?int
    {
        $base = mb_strtolower(preg_replace('/(da|dan|dagi)$/u', '', mb_strtolower(trim($name))) ?? '');
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

    /**
     * Hardcoded static knowledge and small talk.
     */
    private function matchStaticKnowledge(string $message): ?string
    {
        $q = $this->cleanMessage($message);
        $hour = (int) Carbon::now((string) config('app.timezone', 'UTC'))->format('H');

        // Salom / Xayrli
        $greetWords = ['salom', 'assalom', 'assalomu alaykum', 'alaykum', 'hayrli', 'xayrli',
            'qalay', 'ishlar', 'keling', 'xush kelibsiz', 'hi ', 'hey ', 'hello'];
        if (Str::contains($q, $greetWords)) {
            if ($hour >= 5 && $hour < 12)  $greeting = 'Hayrli tong';
            elseif ($hour >= 12 && $hour < 17) $greeting = 'Hayrli kun';
            elseif ($hour >= 17 && $hour < 22) $greeting = 'Hayrli kech';
            else $greeting = 'Assalomu alaykum';

            $schoolName = SiteSetting::get('school_name', (string) __('public.layout.school_name'));
            return "{$greeting}! 😊 Men **{$schoolName}** saytining AI yordamchisiman.\n"
                . "Quyidagi mavzularda yordam bera olaman:\n"
                . "• Maktab, kurslar, o'qituvchilar haqida 🏫\n"
                . "• Imtihon natijalari va taqvim 📅\n"
                . "• Matematika, fan, tarix va boshqa umumiy bilim savollar 📚\n"
                . "• Ota-onalar va o'quvchilar uchun ma'lumotlar 👨‍👩‍👧\n\n"
                . "Savolingizni yozing! 🚀";
        }

        // Xayr / Ko'rishguncha
        if (Str::contains($q, ['xayr', 'ko\'rishguncha', 'sog\'lik', 'hayr', 'bye', 'goodbye', 'chao'])) {
            return "Xayr! 👋 Sizga yordam bera olganimdan mamnunman. Yana savollaringiz bo'lsa, doim shu yerdaman! 😊✨";
        }

        // Rahmat
        if (Str::contains($q, ['rahmat', 'katta rahmat', 'minnatdor', 'bor bo\'ling', 'tashakkur', 'raxmat', 'thanks', 'thank you'])) {
            return "Arziydi! 😊 Yordam bera olganimdan xursandman. Boshqa savollaringiz bo'lsa, yozing! ✅";
        }

        // Kimsan / Qandaysan
        if (Str::contains($q, ['qandaysan', 'yaxshimi', 'tuzukmi', 'kimsan', 'nima qilasan', 'sen kimsan', 'siz kimsiz'])) {
            return "Men 81-IDUM saytining AI yordamchisiman! ✨ Maktab haqida, darslarga oid, math va fan savollariga — hammaga javob berishga harakat qilaman. Savol bering! 🚀";
        }

        // Kim yaratgan
        if (Str::contains($q, ['kim yasagan', 'muallif', 'kim yaratgan', 'saytni kim', 'developer', 'dasturchi'])) {
            $siteCreditsIntro = (string) __('public.about.site_credits_intro');
            $siteCredits = trans('public.about.site_credits_members');
            $names = [];
            if (is_array($siteCredits)) {
                foreach ($siteCredits as $member) {
                    if ($name = trim((string) ($member['name'] ?? ''))) $names[] = $name;
                }
            }
            $nameStr = empty($names) ? 'Jamoa' : implode(', ', $names);
            return "{$siteCreditsIntro} ✨ Mualliflar: **{$nameStr}**. 👨‍💻";
        }

        // Admin / Boshqaruvchi
        if (Str::contains($q, ['admin', 'boshqaruvchi', 'kim yuritadi', 'mas\'ul'])) {
            return "Hozirgi paytda saytni **Xabibullayev Shamsiddin** boshqaradi. Qolgan hamkorlar moderator va editor sifatida yordam beradi. ✨";
        }

        // Hozir soat nechchi / bugungi sana
        if (Str::contains($q, ['soat nech', 'vaqt nech', 'bugun necha', 'bugungi sana', 'nechinchi'])) {
            $now = Carbon::now((string) config('app.timezone', 'UTC'));
            return "🕐 Hozir soat **{$now->format('H:i')}** ({$now->format('d.m.Y')}, {$now->translatedFormat('l')}).";
        }

        return null;
    }

    /**
     * Advanced fuzzy matching in the ai_knowledges table.
     */
    private function matchDatabaseKnowledge(string $message): ?string
    {
        $q = mb_strtolower(trim($message));
        $qClean = $this->cleanMessage($q);
        
        $knowledges = AiKnowledge::where('is_active', true)->get();
        $matches = [];

        foreach ($knowledges as $item) {
            $bestScore = 0;
            
            // 1. Compare against question_name (Fuzzy)
            similar_text($qClean, $this->cleanMessage($item->question_name), $percent);
            $bestScore = max($bestScore, $percent);

            // 2. Compare against keywords (Weighted)
            $keywords = explode(',', (string) $item->keywords);
            foreach ($keywords as $kw) {
                $kw = mb_strtolower(trim($kw));
                if ($kw === '') continue;

                // Exact substring match gets a huge boost!
                if (Str::contains($q, $kw)) {
                    $bestScore = max($bestScore, 90);
                }

                similar_text($qClean, $this->cleanMessage($kw), $kwPercent);
                $bestScore = max($bestScore, $kwPercent);
            }

            if ($bestScore >= 60) {
                $matches[] = [
                    'score' => $bestScore,
                    'answer' => $item->answer_text
                ];
            }
        }

        if (!empty($matches)) {
            usort($matches, fn($a, $b) => $b['score'] <=> $a['score']);
            return $matches[0]['answer'];
        }

        return null;
    }

    /**
     * Cleans noise words while PRESERVING intent words (qachon, kim, nima).
     */
    private function cleanMessage(string $text): string
    {
        $noise = [
            'savolim', 'bor', 'edi', 'ayting', 'bilasiz', 'haqida', 'bering', 'yana',
            'iltimos', 'qanaqa', 'qanday',
            '?', '!', '.', ',', '...', '-', ':', ';'
        ];
        
        $text = mb_strtolower($text);
        foreach ($noise as $n) {
            $text = str_replace($n, '', $text);
        }
        
        return Str::squish($text);
    }

    /**
     * Matches general site statistics and contact info.
     */
    private function matchUniversalData(string $message): ?string
    {
        $q = mb_strtolower(trim($message));
        $qClean = $this->cleanMessage($q);

        // 1. School Statistics
        if ($this->isMatch($q, $qClean, ['qancha', 'necha kishi', 'nechta', 'soni', 'statistika'])) {
            $teachers = Teacher::count();
            $users = \App\Models\User::count();
            $results = Result::count();

            return "Bizning maktabimiz haqida qisqacha ma'lumotlar:\n"
                . "• Ustozlarimiz soni: **{$teachers} ta** 👨‍🏫\n"
                . "• Ro'yxatdan o'tgan o'quvchilar: **{$users} ta** 🎓\n"
                . "• Topshirilgan imtihonlar: **{$results} ta** ✅\n"
                . "Biz doimo o'sib bormoqdamiz! 🚀";
        }

        // 2. Contact & Location — admin paneldan olinadi (SiteSetting orqali)
        if ($this->isMatch($q, $qClean, ['telefon', 'raqam', 'nomer', 'manzil', 'lokatsiya', 'qayerda', 'aloqa'])) {
            $phone   = SiteSetting::get('school_phone', '+998 71 123 45 67');
            $address = SiteSetting::get('school_address', (string) __('public.about.quick_facts.0.value'));

            return "Biz bilan bog'lanish uchun:\n"
                . "📞 Telefon: **{$phone}**\n"
                . "📍 Manzil: **{$address}**\n"
                . "Batafsil ma'lumotni 'Aloqa' sahifasidan olishingiz mumkin. ✨";
        }

        // 3. School Identity / Principal
        if ($this->isMatch($q, $qClean, ['direktor', 'maktab haqida', 'idum nima', '81'])) {
            $schoolName = SiteSetting::get('school_name', (string) __('public.layout.school_name'));

            return "**{$schoolName}** - bu zamonaviy ta'lim texnologiyalari va tajribali ustozlar jamlangan ilm maskani. ✨\n"
                . "Direktor va ma'muriyat haqida ma'lumot 'Maktab ma'muriyati' bo'limida keltirilgan. 😊";
        }

        return null;
    }

    private function matchSchoolProfileData(string $message): ?string
    {
        $q = $this->cleanMessage($message);
        $isSchoolDataIntent = Str::contains($q, [
            'direktor', 'rahbar', 'maktab haqida', 'maktab ichi', 'ichidagi malumot', 'ichidagi ma\'lumot',
            'maktab malumot', 'maktab ma\'lumot', 'pasport', 'boshqaruv', 'school info',
        ]);

        if (! $isSchoolDataIntent) {
            return null;
        }

        $schoolName = SiteSetting::get('school_name', (string) __('public.layout.school_name'));
        $schoolPhone = SiteSetting::get('school_phone', '+998 71 123 45 67');
        $schoolEmail = SiteSetting::get('school_email', 'info@school81.uz');
        $schoolAddress = SiteSetting::get('school_address', (string) __('public.about.quick_facts.0.value'));
        $directorName = $this->extractDirectorNameFromLocale();

        $teachers = Teacher::where('is_active', true)->count();
        $students = User::query()->whereHas('roleRelation', fn ($q) => $q->where('name', User::ROLE_USER))->count();
        $courses = Course::where('status', Course::STATUS_PUBLISHED)->count();
        $posts = Post::count();
        $events = CalendarEvent::where('event_date', '>=', now())->count();

        $directorLine = $directorName !== null
            ? "• Maktab direktori: {$directorName}"
            : "• Maktab direktori: hozircha admin panelda alohida saqlanmagan";

        return "🏫 {$schoolName} bo'yicha ichki ma'lumotlar:\n"
            . "{$directorLine}\n"
            . "• Faol ustozlar: {$teachers} ta\n"
            . "• Ro'yxatdan o'tgan o'quvchilar: {$students} ta\n"
            . "• Faol kurslar: {$courses} ta\n"
            . "• Yangiliklar: {$posts} ta\n"
            . "• Yaqin tadbirlar: {$events} ta\n"
            . "• Telefon: {$schoolPhone}\n"
            . "• Email: {$schoolEmail}\n"
            . "• Manzil: {$schoolAddress}";
    }

    private function extractDirectorNameFromLocale(): ?string
    {
        $locationText = (string) __('public.about.cards.location_text');
        if ($locationText === '') {
            return null;
        }

        if (preg_match("/muassasasiga\\s+(.+?)\\s+rahbarlik\\s+qiladi/iu", $locationText, $matches)) {
            return trim((string) ($matches[1] ?? '')) ?: null;
        }

        return null;
    }

    /**
     * Intent-aware analytics answers:
     * - totals
     * - today/this week/this month windows
     * - growth vs previous period
     * - most/least requested courses
     * - mixed multi-metric prompts
     */
    private function matchAnalyticalData(string $message): ?string
    {
        $q = $this->cleanMessage($message);

        $entities = $this->extractRequestedEntities($q);
        $period = $this->extractRequestedPeriod($q);
        $wantsGrowth = Str::contains($q, ['osish', 'o\'sish', 'kopay', 'ko\'pay', 'kamay', 'dinamika', 'taqqos', 'solishtir']);
        $wantsRanking = Str::contains($q, ['eng kop', 'eng ko\'p', 'top', 'mashhur', 'popular', 'least', 'eng kam']);

        if ($entities === [] && ! $wantsRanking) {
            return null;
        }

        if ($wantsRanking) {
            $rankingAnswer = $this->buildCoursePopularityAnswer($q, $period);
            if ($rankingAnswer !== null) {
                return $rankingAnswer;
            }
        }

        if ($entities === []) {
            return "Savolingizni biroz aniqlashtirib bering: qaysi ko'rsatkich kerak (kurslar, ustozlar, o'quvchilar, yangiliklar, imtihonlar)?";
        }

        $lines = [];
        foreach ($entities as $entity) {
            $count = $this->countEntityForPeriod($entity, $period);
            $label = $this->entityLabel($entity);
            $lines[] = "• {$label}: {$count} ta";

            if ($wantsGrowth) {
                $previousCount = $this->countEntityForPreviousPeriod($entity, $period);
                $delta = $count - $previousCount;
                $trend = $delta > 0 ? "o'sgan" : ($delta < 0 ? "kamaygan" : "o'zgarmagan");
                $lines[] = "  ↳ Oldingi davrga nisbatan: {$trend} ({$delta})";
            }
        }

        $periodLabel = $this->periodLabel($period);
        return "📊 {$periodLabel} bo'yicha natijalar:\n" . implode("\n", $lines);
    }

    private function extractRequestedEntities(string $q): array
    {
        $map = [
            'courses' => ['kurs', 'dars', 'fan'],
            'teachers' => ['ustoz', 'oqituvchi', 'o\'qituvchi', 'teacher', 'domla', 'muallim'],
            'students' => ['oquvchi', 'o\'quvchi', 'user', 'foydalanuvchi', 'talaba'],
            'posts' => ['yangilik', 'post', 'maqola', 'elon', 'e\'lon'],
            'exams' => ['imtihonlar', 'imtihon soni', 'examlar', 'exam soni'],
            'results' => ['imtihon', 'natija', 'test', 'result'],
            'enrollments' => ['royxat', 'ro\'yxat', 'ariza', 'enroll', 'qabul'],
        ];

        $entities = [];
        foreach ($map as $entity => $keywords) {
            foreach ($keywords as $kw) {
                if (Str::contains($q, $kw)) {
                    $entities[] = $entity;
                    break;
                }
            }
        }

        if ($entities === [] && Str::contains($q, ['nechta', 'qancha', 'jami', 'umumiy'])) {
            $entities = ['courses', 'teachers', 'students', 'posts'];
        }

        return array_values(array_unique($entities));
    }

    private function extractRequestedPeriod(string $q): string
    {
        if (Str::contains($q, ['bugun', 'hozir bugun', 'today'])) {
            return 'today';
        }
        if (Str::contains($q, ['kecha', 'yesterday'])) {
            return 'yesterday';
        }
        if (Str::contains($q, ['hafta', 'this week', 'shu hafta'])) {
            return 'week';
        }
        if (Str::contains($q, ['oy', 'this month', 'shu oy'])) {
            return 'month';
        }

        return 'all';
    }

    private function countEntityForPeriod(string $entity, string $period): int
    {
        [$start, $end] = $this->resolvePeriodRange($period);
        $query = match ($entity) {
            'courses' => Course::query(),
            'teachers' => Teacher::query()->where('is_active', true),
            'students' => User::query()->whereHas('roleRelation', fn ($q) => $q->where('name', User::ROLE_USER)),
            'posts' => Post::query(),
            'exams' => Exam::query(),
            'results' => Result::query(),
            'enrollments' => CourseEnrollment::query(),
            default => null,
        };

        if (! $query) {
            return 0;
        }

        if ($start && $end) {
            $query->whereBetween('created_at', [$start, $end]);
        }

        return (int) $query->count();
    }

    private function countEntityForPreviousPeriod(string $entity, string $period): int
    {
        if (! in_array($period, ['today', 'yesterday', 'week', 'month'], true)) {
            return 0;
        }

        [$start, $end] = $this->resolvePreviousPeriodRange($period);
        $query = match ($entity) {
            'courses' => Course::query(),
            'teachers' => Teacher::query()->where('is_active', true),
            'students' => User::query()->whereHas('roleRelation', fn ($q) => $q->where('name', User::ROLE_USER)),
            'posts' => Post::query(),
            'exams' => Exam::query(),
            'results' => Result::query(),
            'enrollments' => CourseEnrollment::query(),
            default => null,
        };

        if (! $query) {
            return 0;
        }

        return (int) $query->whereBetween('created_at', [$start, $end])->count();
    }

    private function resolvePeriodRange(string $period): array
    {
        return match ($period) {
            'today' => [now()->startOfDay(), now()->endOfDay()],
            'yesterday' => [now()->subDay()->startOfDay(), now()->subDay()->endOfDay()],
            'week' => [now()->startOfWeek(), now()->endOfWeek()],
            'month' => [now()->startOfMonth(), now()->endOfMonth()],
            default => [null, null],
        };
    }

    private function resolvePreviousPeriodRange(string $period): array
    {
        return match ($period) {
            'today' => [now()->subDay()->startOfDay(), now()->subDay()->endOfDay()],
            'yesterday' => [now()->subDays(2)->startOfDay(), now()->subDays(2)->endOfDay()],
            'week' => [now()->subWeek()->startOfWeek(), now()->subWeek()->endOfWeek()],
            'month' => [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()],
            default => [now()->startOfDay(), now()->endOfDay()],
        };
    }

    private function buildCoursePopularityAnswer(string $q, string $period): ?string
    {
        $isCourseRelated = Str::contains($q, ['kurs', 'dars', 'fan', 'enroll', 'ariza', 'qabul', 'mashhur', 'popular', 'eng kop', 'eng ko\'p', 'eng kam']);
        if (! $isCourseRelated) {
            return null;
        }

        [$start, $end] = $this->resolvePeriodRange($period);

        $enrollmentQuery = CourseEnrollment::query()
            ->selectRaw('course_id, COUNT(*) as total')
            ->groupBy('course_id')
            ->with('course:id,title');

        if ($start && $end) {
            $enrollmentQuery->whereBetween('created_at', [$start, $end]);
        }

        $rows = $enrollmentQuery->orderByDesc('total')->take(3)->get();
        if ($rows->isEmpty()) {
            return "Bu davrda kurslar bo'yicha ariza ma'lumotlari yetarli emas.";
        }

        $least = $rows->sortBy('total')->first();
        $top = $rows->first();
        $periodLabel = $this->periodLabel($period);

        return "📈 {$periodLabel} bo'yicha kurslar faolligi:\n"
            . "• Eng mashhur: " . ($top?->course?->title ?? 'Noma\'lum kurs') . " ({$top->total} ta ariza)\n"
            . "• Nisbatan kam: " . ($least?->course?->title ?? 'Noma\'lum kurs') . " ({$least->total} ta ariza)\n"
            . "• Top-3 ro'yxat: " . $rows->map(fn ($r) => ($r->course?->title ?? 'Noma\'lum') . " ({$r->total})")->implode(', ');
    }

    private function entityLabel(string $entity): string
    {
        return match ($entity) {
            'courses' => 'Kurslar',
            'teachers' => 'Faol ustozlar',
            'students' => 'O\'quvchilar',
            'posts' => 'Yangiliklar',
            'exams' => 'Imtihonlar',
            'results' => 'Imtihon natijalari',
            'enrollments' => 'Kurs arizalari',
            default => 'Ko\'rsatkich',
        };
    }

    private function periodLabel(string $period): string
    {
        return match ($period) {
            'today' => 'Bugun',
            'yesterday' => 'Kecha',
            'week' => 'Shu hafta',
            'month' => 'Shu oy',
            default => 'Umumiy',
        };
    }

    /**
     * Matches topics to actual database entities with typo tolerance.
     */
    private function matchDynamicData(string $message, ?object $user = null): ?string
    {
        $q = mb_strtolower(trim($message));
        $qClean = $this->cleanMessage($q);

        // 1. User Results
        if ($this->isMatch($q, $qClean, ['natija', 'ball', 'bal', 'imtihonim', 'score', 'imtihon natija', 'ochko', 'foiz'])) {
            if (! $user) {
                return "Sizning natijalaringizni ko'rish uchun avval tizimga kiring. 😊";
            }
            $lastResult = Result::where('user_id', $user->id)->with('exam')->latest()->first();
            if ($lastResult) {
                $passed = $lastResult->passed;
                $status = $passed === true ? "o'tdingiz ✅" : ($passed === false ? "yeta olmadingiz ❌" : "natija tekshirilmoqda ⏳");
                $points = $lastResult->points_earned !== null
                    ? " ({$lastResult->points_earned}/{$lastResult->points_max} ball)"
                    : '';
                return "Sizning oxirgi imtihoningiz: **{$lastResult->exam->title}**{$points}.\n"
                    . "Natijangiz: **{$lastResult->score}%** — {$status}.\n"
                    . "Batafsil ma'lumotni 'Profil' bo'limida ko'rishingiz mumkin. 🎓";
            }
            return "Siz hali imtihon topshirmagansiz. Imtihon bo'limiga o'tib sinab ko'ring! 📝";
        }

        // 2. User Profile
        if ($this->isMatch($q, $qClean, ['men kimman', 'ismim nima', 'profilim', 'akkauntim', 'mening ismim', 'mening rolim'])) {
            if (! $user) {
                return "Siz tizimga kirmagansiz. Iltimos, ro'yxatdan o'ting! 😊";
            }
            return "Sizning ismingiz **{$user->first_name} {$user->last_name}**. "
                . "Siz saytimizda **{$user->role_label}** maqomiga egasiz. ✨";
        }

        // 3. Courses — kengaytirilgan sinonimlar
        if ($this->isMatch($q, $qClean, ['kurs', 'dars', "o'quv", 'fanlar', 'kusrlar', 'kurslar', 'o\'rganish', 'dastur', 'program'])) {
            $courses = Course::where('status', 'published')->latest()->take(5)->get();
            if ($courses->isNotEmpty()) {
                $list = $courses->map(fn ($c) => "• {$c->title}")->implode("\n");
                return "Hozirgi faol kurslarimiz:\n{$list}\n\nBatafsil: 'Kurslar' bo'limidan ko'rishingiz mumkin. ✅";
            }
            return "Hozircha nashr etilgan kurslar yo'q. Tez orada yangi kurslar qo'shiladi! 😊";
        }

        // 4. Teachers — kengaytirilgan sinonimlar
        if ($this->isMatch($q, $qClean, ['ustoz', "o'qituvchi", 'domla', 'muallim', 'teacher', 'pedagog', 'o\'qtuvchi', 'o\'qi'])) {
            $teachers = Teacher::where('is_active', true)->latest()->take(6)->get();
            if ($teachers->isNotEmpty()) {
                $list = $teachers->map(fn ($t) => "• {$t->full_name}" . ($t->subject ? " — {$t->subject}" : ''))->implode("\n");
                return "Bizning tajribali ustozlarimizdan ba'zilari:\n{$list}\n\nTo'liq ro'yxat: 'Ustozlar' sahifasida. 👨‍🏫";
            }
            return "Hozircha o'qituvchilar ma'lumoti kiritilmagan. Keyinroq tekshirib ko'ring! ✨";
        }

        // 5. News/Events — kengaytirilgan sinonimlar
        if ($this->isMatch($q, $qClean, ['yangilik', 'tadbir', 'nima gap', "e'lon", 'post', 'xabar', 'yangililar', 'voqea', 'maqola'])) {
            $posts  = Post::latest()->take(3)->pluck('title')->toArray();
            $events = CalendarEvent::where('event_date', '>=', now())->orderBy('event_date')->take(3)->pluck('title')->toArray();

            if (empty($posts) && empty($events)) {
                return "Hozircha yangi xabarlar va tadbirlar yo'q. Yana bir oz kutib ko'ring! ✨";
            }

            $res = '';
            if (! empty($posts)) {
                $res .= "📰 **So'nggi yangiliklar:**\n• " . implode("\n• ", $posts) . "\n\n";
            }
            if (! empty($events)) {
                $res .= "📅 **Yaqin kunlardagi tadbirlar:**\n• " . implode("\n• ", $events);
            }

            return trim($res);
        }

        return null;
    }

    /**
     * Helper to check if message matching target keywords (with fuzzy support).
     */
    private function isMatch(string $q, string $qClean, array $keywords): bool
    {
        foreach ($keywords as $kw) {
            $kw = mb_strtolower(trim($kw));
            if (Str::contains($q, $kw)) return true;
            
            similar_text($qClean, $this->cleanMessage($kw), $percent);
            if ($percent >= 75) return true;
        }
        return false;
    }

    /**
     * Gemini API call with retry and fallback logic.
     */
    private function callGemini(string $message, ?object $user): array
    {
        $apiKey = (string) config('services.gemini.key');
        $model = (string) config('services.gemini.model', 'gemini-1.5-flash');

        if ($apiKey === '') {
            $fallbackText = "Kechirasiz, hozircha bu savolga aniq javob bera olmayman. ✨\n\nLekin men quyidagi mavzularda yordam bera olaman:\n"
                . "• Maktab haqida ma'lumotlar 🏫\n"
                . "• Eng so'nggi yangiliklar va tadbirlar 📅\n"
                . "• Kurslar va ustozlar haqida 👨‍🏫\n"
                . "• Imtihon natijalaringizni ko'rsatish 🎓\n\n"
                . "GEMINI_API_KEY sozlanmagani uchun tashqi AI o‘chiq. Admin bilim bazasi va sayt ichki ma’lumotlari ishlaydi.\n"
                . "Iltimos, savolingizni aniqroq yozing yoki kerakli bo'limga o'ting! 😊";

            return ['success' => true, 'text' => $fallbackText, 'source' => 'no_gemini_key'];
        }

        $systemInstruction = $this->buildSystemInstruction($user);
        $contents = [['role' => 'user', 'parts' => [['text' => $message]]]];

        $maxRetries = 2;
        $retryCount = 0;

        while ($retryCount <= $maxRetries) {
            try {
                $response = Http::timeout(45)->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}", [
                    'system_instruction' => ['parts' => [['text' => $systemInstruction]]],
                    'contents' => $contents,
                    'generationConfig' => ['temperature' => 0.7, 'maxOutputTokens' => 1024],
                ]);

                if ($response->successful()) {
                    $aiText = $response->json()['candidates'][0]['content']['parts'][0]['text'] ?? null;
                    if ($aiText) {
                        return ['success' => true, 'text' => $this->handleTicketCreation($aiText), 'source' => 'gemini'];
                    }
                }

                if ($response->status() === 429) {
                    $retryCount++;
                    sleep(2 * $retryCount); // Linear backoff
                    continue;
                }
                break;
            } catch (\Exception $e) {
                $retryCount++;
                sleep(1);
            }
        }

        // Ultimate Fallback: Smart local response if Gemini fails
        $localFallback = $this->matchDynamicData($message, $user);
        
        if ($localFallback) {
            return ['success' => true, 'text' => $localFallback, 'source' => 'local_fallback'];
        }

        $fallbackText = "Kechirasiz, hozircha bu savolga aniq javob bera olmayman. ✨\n\nLekin men quyidagi mavzularda yordam bera olaman:\n"
            . "• Maktab haqida ma'lumotlar 🏫\n"
            . "• Eng so'nggi yangiliklar va tadbirlar 📅\n"
            . "• Kurslar va ustozlar haqida 👨‍🏫\n"
            . "• Imtihon natijalaringizni ko'rsatish 🎓\n\n"
            . "Iltimos, savolingizni aniqroq yozing yoki kerakli bo'limga o'ting! 😊";

        return ['success' => true, 'text' => $fallbackText, 'source' => 'ultimate_fallback'];
    }

    private function buildSystemInstruction(?object $user): string
    {
        $tz          = (string) config('app.timezone', 'UTC');
        $now         = Carbon::now($tz);
        $schoolName  = SiteSetting::get('school_name', (string) __('public.layout.school_name'));
        $phone       = SiteSetting::get('school_phone', '');
        $address     = SiteSetting::get('school_address', '');
        $email       = SiteSetting::get('school_email', '');

        // Maktab statistikasi
        $teacherCount = Teacher::where('is_active', true)->count();
        $studentCount = \App\Models\User::whereHas('roleRelation', fn ($q) => $q->where('name', \App\Models\User::ROLE_USER))->count();
        $courseCount  = Course::where('status', Course::STATUS_PUBLISHED)->count();

        // O'qituvchilar ro'yxati (top 8)
        $teachersList = Teacher::where('is_active', true)
            ->latest('id')
            ->take(8)
            ->get()
            ->map(fn ($t) => "- {$t->full_name}" . ($t->subject ? " ({$t->subject})" : ''))
            ->implode("\n");

        // Nashr etilgan kurslar (top 6)
        $coursesList = Course::where('status', Course::STATUS_PUBLISHED)
            ->latest('id')
            ->take(6)
            ->get()
            ->map(fn ($c) => "- {$c->title}")
            ->implode("\n");

        // Foydalanuvchi konteksti
        $userContext = '';
        if ($user) {
            $userContext = "\n\n=== FOYDALANUVCHI ==="
                . "\nIsm: {$user->first_name} {$user->last_name}"
                . "\nRol: {$user->role_label}"
                . "\nFoydalanuvchiga ism bilan murojaat qiling.";
        }

        return <<<PROMPT
Sen {$schoolName} maktabi veb-saytining universal AI yordamchisisiz.

=== ASOSIY QO'LLANMA ===
1. FAQAT O'ZBEK TILIDA javob ber.
2. Har qanday savolga javob berishga harakat qil — maktab haqida ham, umumiy bilim (matematika, fizika, biologiya, tarix, geografiya, ingliz tili va boshqa fanlar) haqida ham.
3. Javoblar qisqa, aniq va foydali bo'lsin. Emojilar qo'sh. ✨
4. Agar maktabga oid ma'lumot so'ralsa, avvalo quyidagi ma'lumotlardan foydalan.
5. Bilmagan narsani taxmin qilib javob berma — "Bu haqda aniq ma'lumotim yo'q" de.

=== MAKTAB MA'LUMOTLARI ===
Maktab nomi: {$schoolName}
Telefon: {$phone}
Manzil: {$address}
Email: {$email}
Faol o'qituvchilar: {$teacherCount} ta
Ro'yxatdagi o'quvchilar: {$studentCount} ta
Faol kurslar: {$courseCount} ta

=== O'QITUVCHILAR (faollar) ===
{$teachersList}

=== MAVJud KURSLAR ===
{$coursesList}

=== HOZIRGI VAQT ===
Sana: {$now->format('d.m.Y')}, {$now->translatedFormat('l')}
Vaqt: {$now->format('H:i')} (Toshkent vaqti){$userContext}

=== MUHIM ===
- Maktab haqidagi savollarga yuqoridagi ma'lumotlardan foydalanib javob ber.
- Umumiy bilim savollarga (masalan: "Pythagoras teoremasi?", "Suv formulasi?") — oddiy, tushunarli javob ber.
- Siyosat, zararli kontent, noqonuniy narsalar haqida javob berma.
- Javob 5-6 jumladan oshmasin (oddiy suhbat uchun).
PROMPT;
    }

    private function handleTicketCreation(string $text): string
    {
        if (Str::contains($text, '[CREATE_TICKET:')) {
            $description = Str::between($text, '[CREATE_TICKET:', ']');
            $ticket = ContactMessage::create([
                'name' => auth()->check() ? auth()->user()->name : 'AI System',
                'email' => auth()->check() ? auth()->user()->email : 'ai@81-maktab.uz',
                'message' => "AI Murojaat: " . $description,
            ]);
            return Str::of($text)->before('[CREATE_TICKET:')->trim()->value() . "\n\n📌 №{$ticket->id} raqamli murojaat yaratildi! ✅";
        }
        return $text;
    }
}
