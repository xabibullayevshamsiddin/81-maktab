<?php

namespace Tests\Unit\Models;

use App\Models\Course;
use App\Models\Role;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourseTest extends TestCase
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

    private function createTeacher(): Teacher
    {
        return Teacher::query()->create([
            "full_name" => "Ustoz Ismoil",
            "slug" => "ustoz-ismoil-" . uniqid(),
            "subject" => "Matematika",
            "is_active" => true,
        ]);
    }

    private function createAdmin(): User
    {
        return User::query()->create([
            "name" => "Admin",
            "email" => "admin-" . uniqid() . "@test.com",
            "password" => bcrypt("password"),
            "role_id" => Role::idByName(Role::NAME_ADMIN),
            "is_active" => true,
        ]);
    }

    private function createCourse(array $overrides = []): Course
    {
        $teacher = $this->createTeacher();
        $admin = $this->createAdmin();
        return Course::query()->create(array_merge([
            "teacher_id" => $teacher->id,
            "created_by" => $admin->id,
            "title" => "Test Course",
            "price" => "300 000 som",
            "duration" => "2 oy",
            "description" => "Test description",
            "start_date" => now()->addWeek()->toDateString(),
            "status" => Course::STATUS_PUBLISHED,
        ], $overrides));
    }

    public function test_can_be_created_as_draft(): void
    {
        $course = $this->createCourse([
            "title" => "Draft Course",
            "status" => Course::STATUS_DRAFT,
        ]);
        $this->assertDatabaseHas("courses", ["title" => "Draft Course", "status" => "draft"]);
    }

    public function test_status_constants(): void
    {
        $this->assertSame("draft", Course::STATUS_DRAFT);
        $this->assertSame("pending_verification", Course::STATUS_PENDING_VERIFICATION);
        $this->assertSame("published", Course::STATUS_PUBLISHED);
    }

    public function test_belongs_to_teacher(): void
    {
        $course = $this->createCourse();
        $this->assertInstanceOf(Teacher::class, $course->teacher);
    }

    public function test_instructor_name_from_teacher(): void
    {
        $course = $this->createCourse();
        $this->assertSame("Ustoz Ismoil", $course->instructorName());
    }

    public function test_instructor_name_fallback(): void
    {
        $creator = User::query()->create([
            "name" => "Jahon Ismoilov",
            "first_name" => "Jahon",
            "last_name" => "Ismoilov",
            "email" => "jahon-" . uniqid() . "@test.com",
            "password" => bcrypt("pw"),
            "role_id" => Role::idByName(Role::NAME_TEACHER),
            "is_active" => true,
        ]);
        $course = Course::query()->create([
            "teacher_id" => null,
            "created_by" => $creator->id,
            "title" => "Fallback Test",
            "price" => "0",
            "duration" => "1 oy",
            "description" => "Test",
            "start_date" => now()->addWeek()->toDateString(),
            "status" => Course::STATUS_PUBLISHED,
        ]);
        $this->assertSame("Jahon Ismoilov", $course->instructorName());
    }

    public function test_instructor_subject(): void
    {
        $course = $this->createCourse();
        $this->assertSame("Matematika", $course->instructorSubject());
    }

    public function test_has_enrollments(): void
    {
        $course = $this->createCourse();
        $this->assertCount(0, $course->enrollments);
    }

    public function test_cover_image_url(): void
    {
        $course = $this->createCourse();
        $this->assertNotEmpty($course->coverImageUrl());
    }

    public function test_instructor_experience(): void
    {
        $teacher = Teacher::query()->create([
            "full_name" => "Exp Teacher",
            "slug" => "exp-teacher-" . uniqid(),
            "subject" => "Fizika",
            "experience_years" => 5,
            "is_active" => true,
        ]);
        $admin = $this->createAdmin();
        $course = Course::query()->create([
            "teacher_id" => $teacher->id,
            "created_by" => $admin->id,
            "title" => "Exp Test",
            "price" => "0",
            "duration" => "1 oy",
            "description" => "Test",
            "start_date" => now()->addWeek()->toDateString(),
            "status" => Course::STATUS_PUBLISHED,
        ]);
        $this->assertStringContainsString("5 yil", $course->instructorExperienceLabel());
    }

    public function test_instructor_grades(): void
    {
        $course = $this->createCourse();
        $this->assertNotEmpty($course->instructorGradesLabel());
    }

    public function test_creator_relation(): void
    {
        $course = $this->createCourse();
        $this->assertInstanceOf(User::class, $course->creator);
    }

    public function test_bookmarks_relation(): void
    {
        $course = $this->createCourse();
        $this->assertCount(0, $course->bookmarks);
    }
}