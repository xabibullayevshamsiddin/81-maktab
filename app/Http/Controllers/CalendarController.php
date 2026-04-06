<?php

namespace App\Http\Controllers;

use App\Models\CalendarEvent;
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

        $events = Cache::remember(cache_key_public_calendar_page($year, $page), now()->addMinutes(10), function () use ($year) {
            return CalendarEvent::query()
                ->select(['id', 'title', 'title_en', 'body', 'body_en', 'event_date', 'time_note', 'time_note_en', 'sort_order'])
                ->whereYear('event_date', $year)
                ->orderBy('event_date')
                ->orderBy('sort_order')
                ->orderBy('id')
                ->paginate(9)
                ->withQueryString();
        });

        $grouped = $events->getCollection()->groupBy(fn (CalendarEvent $e) => $e->event_date->format('Y-m-d'));

        return view('calendar', compact('events', 'grouped', 'year'));
    }
}
