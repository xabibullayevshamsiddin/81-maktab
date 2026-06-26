<?php

namespace Tests\Feature;

use App\Models\Teacher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeacherPublicPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_list_page_loads(): void
    {
        Teacher::query()->create([
            "full_name" => "Ali Ustoz",
            "slug" => "ali-ustoz",
            "subject" => "Matematika",
            "is_active" => true,
        ]);

        $response = $this->get(route("teacher"));

        $response->assertOk();
    }

    public function test_teacher_detail_page(): void
    {
        $teacher = Teacher::query()->create([
            "full_name" => "Hasan Ustoz",
            "slug" => "hasan-ustoz",
            "subject" => "Fizika",
            "bio" => "Tajribali ustoz.",
            "is_active" => true,
        ]);

        $response = $this->get(route("teacher.show", $teacher));

        $response->assertOk();
        $response->assertSee("Hasan Ustoz");
    }

    public function test_teacher_stats_page(): void
    {
        $teacher = Teacher::query()->create([
            "full_name" => "Stats Ustoz",
            "slug" => "stats-ustoz",
            "is_active" => true,
        ]);

        $response = $this->get(route("teacher.stats", $teacher));

        $response->assertOk();
    }
}