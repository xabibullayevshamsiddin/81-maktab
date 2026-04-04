<?php

namespace App\Http\Controllers;

use App\Models\CalendarEvent;
use Illuminate\Http\Request;

class AdminCalendarEventController extends Controller
{
    public function index()
    {
        $events = CalendarEvent::query()
            ->orderByDesc('event_date')
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->paginate(30);

        return view('admin.calendar-events.index', compact('events'));
    }

    public function create()
    {
        return view('admin.calendar-events.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'body' => ['nullable', 'string', 'max:10000'],
            'event_date' => ['required', 'date'],
            'time_note' => ['nullable', 'string', 'max:64'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
        ]);

        CalendarEvent::query()->create($validated);

        return redirect()
            ->route('calendar-events.index')
            ->with('success', 'Tadbir qo‘shildi.');
    }

    public function edit(CalendarEvent $calendar_event)
    {
        return view('admin.calendar-events.edit', ['event' => $calendar_event]);
    }

    public function update(Request $request, CalendarEvent $calendar_event)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'body' => ['nullable', 'string', 'max:10000'],
            'event_date' => ['required', 'date'],
            'time_note' => ['nullable', 'string', 'max:64'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
        ]);

        $calendar_event->update($validated);

        return redirect()
            ->route('calendar-events.index')
            ->with('success', 'Yangilandi.');
    }

    public function destroy(CalendarEvent $calendar_event)
    {
        $calendar_event->delete();

        return redirect()
            ->route('calendar-events.index')
            ->with('success', 'O‘chirildi.');
    }
}
