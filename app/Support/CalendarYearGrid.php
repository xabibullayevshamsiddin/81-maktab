<?php

namespace App\Support;

use Carbon\Carbon;

class CalendarYearGrid
{
    /**
     * @param  array<string, int>  $countsByDate
     * @return list<array{month: int, label: string, weeks: list<list<array<string, mixed>|null>>}>
     */
    public static function build(int $year, array $countsByDate): array
    {
        $monthNames = __('public.calendar.month_names');
        if (! is_array($monthNames)) {
            $monthNames = [];
        }

        $out = [];

        for ($m = 1; $m <= 12; $m++) {
            $first = Carbon::createFromDate($year, $m, 1)->startOfDay();
            $lastDay = (int) $first->copy()->endOfMonth()->day;

            $weeks = [];
            $week = array_fill(0, 7, null);
            $pos = (int) $first->format('N') - 1;

            for ($day = 1; $day <= $lastDay; $day++) {
                $date = Carbon::createFromDate($year, $m, $day);
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

            $label = $monthNames[$m] ?? $first->copy()->locale(app()->getLocale())->translatedFormat('F');

            $out[] = [
                'month' => $m,
                'label' => $label,
                'weeks' => $weeks,
            ];
        }

        return $out;
    }
}
