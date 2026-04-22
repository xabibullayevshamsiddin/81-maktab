<?php

namespace App\Support;

use Carbon\Carbon;

class CalendarYearGrid
{
    /**
     * Ochiq taqvim (joriy yil): joriy oydan boshlab keyingi shuncha oy oxirigacha siljuvchi oyna.
     * Oy almashganda eng boshidagi (o‘tgan) oy tushadi, oxiriga yangi oy qo‘shiladi.
     */
    public const PUBLIC_ROLLING_FUTURE_MONTHS_AFTER_CURRENT = 4;

    /**
     * @param  array<string, int>  $countsByDate
     * @return array{month: int, year: int, label: string, weeks: list<list<array<string, mixed>|null>>}
     */
    public static function buildMonth(int $year, int $month, array $countsByDate): array
    {
        $monthNames = __('public.calendar.month_names');
        if (! is_array($monthNames)) {
            $monthNames = [];
        }

        $first = Carbon::createFromDate($year, $month, 1)->startOfDay();
        $lastDay = (int) $first->copy()->endOfMonth()->day;

        $weeks = [];
        $week = array_fill(0, 7, null);
        $pos = (int) $first->format('N') - 1;

        for ($day = 1; $day <= $lastDay; $day++) {
            $date = Carbon::createFromDate($year, $month, $day);
            $key = $date->format('Y-m-d');
            $count = (int) ($countsByDate[$key] ?? 0);

            $week[$pos] = [
                'day' => $day,
                'dateKey' => $key,
                'count' => $count,
                'hasEvents' => $count > 0,
                'isToday' => $date->isToday(),
            ];
            $pos++;
            if ($pos === 7) {
                $weeks[] = $week;
                $week = array_fill(0, 7, null);
                $pos = 0;
            }
        }
        if ($pos > 0) {
            $weeks[] = $week;
        }

        $label = $monthNames[$month] ?? $first->copy()->locale(app()->getLocale())->translatedFormat('F');

        return [
            'month' => $month,
            'year' => $year,
            'label' => $label,
            'weeks' => $weeks,
        ];
    }

    /**
     * @param  array<string, int>  $countsByDate
     * @return list<array{month: int, year: int, label: string, weeks: list<list<array<string, mixed>|null>>}>
     */
    public static function build(int $year, array $countsByDate): array
    {
        $out = [];

        for ($m = 1; $m <= 12; $m++) {
            $out[] = self::buildMonth($year, $m, $countsByDate);
        }

        return $out;
    }

    /**
     * Ochiq taqvim: joriy yilda o‘tgan oylar chiqmaydi; joriy oy + keyingi
     * {@see self::PUBLIC_ROLLING_FUTURE_MONTHS_AFTER_CURRENT} oy (jami 5 ta oy).
     * Masalan, aprel bo‘lsa aprel…avgust; mayga o‘tsa may…sentabr (aprel yo‘qoladi, oxiriga yangi oy qo‘shiladi).
     * Boshqa yillar — to‘liq 12 oy.
     *
     * @param  array<string, int>  $countsByDate
     * @return list<array{month: int, year: int, label: string, weeks: list<list<array<string, mixed>|null>>}>
     */
    public static function buildPublicView(int $viewYear, array $countsByDate, ?Carbon $now = null): array
    {
        $now = $now ?? Carbon::now();
        $cy = (int) $now->year;

        if ($viewYear !== $cy) {
            return self::build($viewYear, $countsByDate);
        }

        $windowStart = $now->copy()->startOfMonth();
        $windowEnd = $now->copy()->startOfMonth()->addMonths(self::PUBLIC_ROLLING_FUTURE_MONTHS_AFTER_CURRENT);

        $out = [];
        for ($cur = $windowStart->copy(); $cur->lte($windowEnd); $cur->addMonth()) {
            $out[] = self::buildMonth((int) $cur->year, (int) $cur->month, $countsByDate);
        }

        return $out;
    }
}
