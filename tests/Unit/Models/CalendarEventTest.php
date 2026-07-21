<?php

namespace Tests\Unit\Models;

use App\Models\CalendarEvent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CalendarEventTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_be_created(): void
    {
        $event = CalendarEvent::query()->create([
            "title" => "Test Event",
            "event_date" => now()->addDays(5)->toDateString(),
        ]);

        $this->assertDatabaseHas("calendar_events", ["title" => "Test Event"]);
    }

    public function test_date_scoping(): void
    {
        CalendarEvent::query()->create([
            "title" => "Past",
            "event_date" => now()->subMonth()->toDateString(),
        ]);
        CalendarEvent::query()->create([
            "title" => "Future",
            "event_date" => now()->addMonth()->toDateString(),
        ]);

        $future = CalendarEvent::query()->where("event_date", ">=", now())->get();
        $this->assertGreaterThanOrEqual(1, $future->count());
    }

    public function test_has_body(): void
    {
        $event = CalendarEvent::query()->create([
            "title" => "Sport Day",
            "event_date" => now()->addDays(10)->toDateString(),
            "body" => "Annual sports competition",
        ]);

        $this->assertSame("Annual sports competition", $event->body);
    }

    public function test_order_by_date(): void
    {
        CalendarEvent::query()->create([
            "title" => "Second", "event_date" => now()->addDays(5)->toDateString(),
        ]);
        CalendarEvent::query()->create([
            "title" => "First", "event_date" => now()->addDays(2)->toDateString(),
        ]);

        $events = CalendarEvent::query()->orderBy("event_date")->get();
        $this->assertSame("First", $events->first()->title);
    }

    public function test_time_note(): void
    {
        $event = CalendarEvent::query()->create([
            "title" => "Meeting",
            "event_date" => now()->addDays(3)->toDateString(),
            "time_note" => "10:00",
        ]);

        $this->assertSame("10:00", $event->time_note);
    }

    public function test_localized_body(): void
    {
        $event = CalendarEvent::query()->create([
            "title" => "Event",
            "title_en" => "Event EN",
            "event_date" => now()->addDays(1)->toDateString(),
        ]);

        app()->setLocale("en");
        $this->assertSame("Event EN", localized_model_value($event, "title"));
    }
}