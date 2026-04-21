<?php

namespace App\Http\Controllers;

use App\Models\CalendarEvent;
use App\Support\CalendarYearGrid;
use Illuminate\Http\Request;

class AdminCalendarEventController extends Controller
{
    public function index(Request $request)
    {
        $year = (int) $request->query('y', now()->year);
        if ($year < 2000 || $year > 2100) {
            $year = (int) now()->year;
        }

        $countsByDate = CalendarEvent::query()
            ->whereYear('event_date', $year)
            ->get(['event_date'])
            ->groupBy(fn (CalendarEvent $e) => $e->event_date->format('Y-m-d'))
            ->map(fn ($group) => $group->count())
            ->all();

        $calendarMonths = CalendarYearGrid::build($year, $countsByDate);

        $events = CalendarEvent::query()
            ->orderByDesc('event_date')
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->paginate(30)
            ->withQueryString();


        return view('admin.calendar-events.index', compact('events', 'year', 'calendarMonths', 'countsByDate'));
    }

    public function create()
    {
        return view('admin.calendar-events.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'title_en' => ['nullable', 'string', 'max:255'],
            'body' => ['nullable', 'string', 'max:10000'],
            'body_en' => ['nullable', 'string', 'max:10000'],
            'event_date' => ['required', 'date'],
            'time_note' => ['nullable', 'string', 'max:64'],
            'time_note_en' => ['nullable', 'string', 'max:64'],
        ]);

        $validated['sort_order'] = $this->nextSortOrderForDate((string) $validated['event_date']);

        CalendarEvent::query()->create($validated);
        forget_public_calendar_caches();

        return redirect()
            ->route('calendar-events.index')
            ->with('success', "Tadbir qo'shildi.");
    }

    public function edit(CalendarEvent $calendar_event)
    {
        return view('admin.calendar-events.edit', ['event' => $calendar_event]);
    }

    public function update(Request $request, CalendarEvent $calendar_event)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'title_en' => ['nullable', 'string', 'max:255'],
            'body' => ['nullable', 'string', 'max:10000'],
            'body_en' => ['nullable', 'string', 'max:10000'],
            'event_date' => ['required', 'date'],
            'time_note' => ['nullable', 'string', 'max:64'],
            'time_note_en' => ['nullable', 'string', 'max:64'],
        ]);

        $calendar_event->update($validated);
        forget_public_calendar_caches();

        return redirect()
            ->route('calendar-events.index')
            ->with('success', 'Yangilandi.');
    }

    public function destroy(CalendarEvent $calendar_event)
    {
        $calendar_event->delete();
        forget_public_calendar_caches();

        return redirect()
            ->route('calendar-events.index')
            ->with('success', "O'chirildi.");
    }

    private function nextSortOrderForDate(string $eventDate): int
    {
        return (int) CalendarEvent::query()
            ->whereDate('event_date', $eventDate)
            ->max('sort_order') + 1;
    }
}
