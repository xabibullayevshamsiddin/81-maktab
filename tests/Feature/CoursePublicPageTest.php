<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CoursePublicPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (Role::DEFAULT_ROLES as $role) {
            if (!Role::query()->where("name", $role["name"])->exists()) {
                Role::query()->create($role);
            }
        }
    }

    public function test_course_list_page_loads(): void
    {
        $response = $this->get(route("courses"));

        $response->assertOk();
    }

    public function test_course_detail_page(): void
    {
        $admin = User::query()->create([
            "name" => "Admin",
            "email" => "cadmin-" . uniqid() . "@test.com",
            "password" => bcrypt("pw"),
            "role_id" => Role::idByName(Role::NAME_ADMIN),
            "is_active" => true,
        ]);

        $course = Course::query()->create([
            "teacher_id" => null,
            "created_by" => $admin->id,
            "title" => "Test Course Detail",
            "price" => "Free",
            "duration" => "2 oy",
            "description" => "Course description here",
            "start_date" => now()->addWeek()->toDateString(),
            "status" => Course::STATUS_PUBLISHED,
        ]);

        $response = $this->get(route("courses.show", $course));

        $response->assertOk();
        $response->assertSee("Test Course Detail");
    }

    public function test_course_not_found_redirects(): void
    {
        $response = $this->get("/courses/99999");

        $response->assertRedirect(route("courses"));
    }
}