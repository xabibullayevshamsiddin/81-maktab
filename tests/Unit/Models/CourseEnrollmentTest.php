<?php

namespace Tests\Unit\Models;

use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourseEnrollmentTest extends TestCase
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

    private function createCourse(): Course
    {
        $admin = User::query()->create([
            "name" => "Admin",
            "email" => "admin-" . uniqid() . "@test.com",
            "password" => bcrypt("password"),
            "role_id" => Role::idByName(Role::NAME_ADMIN),
            "is_active" => true,
        ]);

        return Course::query()->create([
            "teacher_id" => null,
            "created_by" => $admin->id,
            "title" => "Test Course",
            "price" => "Free",
            "duration" => "1 oy",
            "description" => "Test course description",
            "start_date" => now()->addWeek()->toDateString(),
            "status" => Course::STATUS_PUBLISHED,
        ]);
    }

    public function test_can_be_created(): void
    {
        $course = $this->createCourse();
        $user = User::query()->create([
            "name" => "Student",
            "email" => "student-" . uniqid() . "@test.com",
            "password" => bcrypt("password"),
            "role_id" => Role::idByName(Role::NAME_USER),
            "is_active" => true,
        ]);

        $enrollment = CourseEnrollment::query()->create([
            "course_id" => $course->id,
            "user_id" => $user->id,
            "status" => "pending",
        ]);

        $this->assertDatabaseHas("course_enrollments", [
            "course_id" => $course->id,
            "user_id" => $user->id,
        ]);
    }

    public function test_belongs_to_course_and_user(): void
    {
        $course = $this->createCourse();
        $user = User::query()->create([
            "name" => "Student",
            "email" => "s-" . uniqid() . "@test.com",
            "password" => bcrypt("password"),
            "role_id" => Role::idByName(Role::NAME_USER),
            "is_active" => true,
        ]);

        $enrollment = CourseEnrollment::query()->create([
            "course_id" => $course->id,
            "user_id" => $user->id,
            "status" => "pending",
        ]);

        $this->assertInstanceOf(Course::class, $enrollment->course);
        $this->assertInstanceOf(User::class, $enrollment->user);
    }

    public function test_status_workflow(): void
    {
        $course = $this->createCourse();
        $user = User::query()->create([
            "name" => "S",
            "email" => "s2-" . uniqid() . "@test.com",
            "password" => bcrypt("password"),
            "role_id" => Role::idByName(Role::NAME_USER),
            "is_active" => true,
        ]);

        $enrollment = CourseEnrollment::query()->create([
            "course_id" => $course->id,
            "user_id" => $user->id,
            "status" => "pending",
        ]);

        $this->assertSame("pending", $enrollment->status);

        $enrollment->update(["status" => "approved"]);
        $this->assertSame("approved", $enrollment->fresh()->status);

        $enrollment->update(["status" => "rejected"]);
        $this->assertSame("rejected", $enrollment->fresh()->status);
    }
}