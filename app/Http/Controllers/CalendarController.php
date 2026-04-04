<?php

namespace App\Http\Controllers;

use App\Models\CalendarEvent;

class CalendarController extends Controller
{
    public function index()
    {
        $year = (int) request()->query('y', now()->year);
        if ($year < 2000 || $year > 2100) {
            $year = (int) now()->year;
        }

        $events = CalendarEvent::query()
            ->whereYear('event_date', $year)
            ->orderBy('event_date')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $grouped = $events->groupBy(fn (CalendarEvent $e) => $e->event_date->format('Y-m-d'));

        return view('calendar', compact('events', 'grouped', 'year'));
    }
}
