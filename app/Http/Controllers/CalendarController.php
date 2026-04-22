<?php

namespace App\Http\Controllers;

use App\Models\CalendarEvent;
use App\Support\CalendarYearGrid;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class CalendarController extends Controller
{
    public function index()
    {
        $year = (int) request()->query('y', now()->year);
        if ($year < 2000 || $year > 2100) {
            $year = (int) now()->year;
        }

        $page = max(1, (int) request()->query('page', 1));

        $now = Carbon::now();
        $isRollingGridYear = $year === (int) $now->year;

        $rollingFrom = null;
        $rollingTo = null;
        if ($isRollingGridYear) {
            $rollingFrom = $now->copy()->startOfMonth()->startOfDay();
            $rollingTo = $now->copy()->startOfMonth()
                ->addMonths(CalendarYearGrid::PUBLIC_ROLLING_FUTURE_MONTHS_AFTER_CURRENT)
                ->endOfMonth()
                ->endOfDay();
        }

        /** @var array<string, int> $countsByDate */
        $countsCacheKey = $isRollingGridYear
            ? cache_key_public_calendar_counts($year).'.rolling.'.$now->format('Y-m')
            : cache_key_public_calendar_counts($year);

        $countsByDate = Cache::remember($countsCacheKey, now()->addMinutes(10), function () use ($year, $isRollingGridYear, $rollingFrom, $rollingTo) {
            $q = CalendarEvent::query()->select(['event_date']);

            if ($isRollingGridYear && $rollingFrom && $rollingTo) {
                $q->whereBetween('event_date', [$rollingFrom->toDateString(), $rollingTo->toDateString()]);
            } else {
                $q->whereYear('event_date', $year);
            }

            return $q->get()
                ->groupBy(fn (CalendarEvent $e) => $e->event_date->format('Y-m-d'))
                ->map(fn ($group) => $group->count())
                ->all();
        });

        $pageCacheKey = $isRollingGridYear
            ? cache_key_public_calendar_page($year, $page).'.rolling.'.$now->format('Y-m')
            : cache_key_public_calendar_page($year, $page);

        $events = Cache::remember($pageCacheKey, now()->addMinutes(10), function () use ($year, $isRollingGridYear, $rollingFrom, $rollingTo) {
            $q = CalendarEvent::query()
                ->select(['id', 'title', 'title_en', 'body', 'body_en', 'event_date', 'time_note', 'time_note_en', 'sort_order']);

            if ($isRollingGridYear && $rollingFrom && $rollingTo) {
                $q->whereBetween('event_date', [$rollingFrom->toDateString(), $rollingTo->toDateString()]);
            } else {
                $q->whereYear('event_date', $year);
            }

            return $q
                ->orderBy('event_date')
                ->orderBy('sort_order')
                ->orderBy('id')
                ->paginate(9)
                ->withQueryString();
        });

        $grouped = $events->getCollection()->groupBy(fn (CalendarEvent $e) => $e->event_date->format('Y-m-d'));

        $calendarMonths = CalendarYearGrid::buildPublicView($year, $countsByDate, $now);

        $hasAnyEventsInYear = $isRollingGridYear && $rollingFrom && $rollingTo
            ? CalendarEvent::query()
                ->whereBetween('event_date', [$rollingFrom->toDateString(), $rollingTo->toDateString()])
                ->exists()
            : CalendarEvent::query()
                ->whereYear('event_date', $year)
                ->exists();

        return view('calendar', compact('events', 'grouped', 'year', 'calendarMonths', 'countsByDate', 'hasAnyEventsInYear'));
    }
}
