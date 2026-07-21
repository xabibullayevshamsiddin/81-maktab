<?php

namespace Tests\Unit\Models;

use App\Models\Role;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeacherTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_be_created(): void
    {
        $teacher = Teacher::query()->create([
            "full_name" => "Ali Valiyev",
            "slug" => "ali-valiyev",
            "subject" => "Informatika",
            "is_active" => true,
        ]);

        $this->assertDatabaseHas("teachers", ["full_name" => "Ali Valiyev"]);
    }

    public function test_slug_is_auto_generated(): void
    {
        $teacher = Teacher::query()->create([
            "full_name" => "Hasan Hasanov",
            "subject" => "Fizika",
            "is_active" => true,
        ]);

        $this->assertSame("hasan-hasanov", $teacher->slug);
    }

    public function test_inactive_filter(): void
    {
        Teacher::query()->create(["full_name" => "Active", "slug" => "active-1", "is_active" => true]);
        Teacher::query()->create(["full_name" => "Inactive", "slug" => "inactive-1", "is_active" => false]);

        $active = Teacher::query()->where("is_active", true)->get();

        $this->assertCount(1, $active);
    }

    public function test_has_courses(): void
    {
        $teacher = Teacher::query()->create([
            "full_name" => "Test Teacher",
            "slug" => "test-teacher-1",
            "is_active" => true,
        ]);

        $this->assertCount(0, $teacher->courses);
    }

    public function test_image_url_returns_placeholder_when_null(): void
    {
        $teacher = Teacher::query()->create([
            "full_name" => "No Image",
            "slug" => "no-image-1",
            "image" => null,
            "is_active" => true,
        ]);

        $this->assertNotNull($teacher->imageUrl());
        $this->assertStringContainsString("temp/img", $teacher->imageUrl());
    }

    public function test_subject_localization(): void
    {
        $teacher = Teacher::query()->create([
            "full_name" => "Multi Lang",
            "slug" => "multi-lang-1",
            "subject" => "Matematika",
            "subject_en" => "Mathematics",
            "is_active" => true,
        ]);

        app()->setLocale("en");
        $this->assertSame("Mathematics", localized_model_value($teacher, "subject"));

        app()->setLocale("uz");
        $this->assertSame("Matematika", localized_model_value($teacher, "subject"));
    }

    public function test_lavozim_toifa_fields(): void
    {
        $teacher = Teacher::query()->create([
            "full_name" => "Full Profile",
            "slug" => "full-profile-1",
            "lavozim" => "Ustoz",
            "toifa" => "Oliy",
            "experience_years" => 10,
            "is_active" => true,
        ]);

        $this->assertSame("Ustoz", $teacher->lavozim);
        $this->assertSame("Oliy", $teacher->toifa);
        $this->assertSame(10, $teacher->experience_years);
    }

    public function test_achievements(): void
    {
        $teacher = Teacher::query()->create([
            "full_name" => "Achiever",
            "slug" => "achiever-1",
            "achievements" => "1st place\n2nd place",
            "is_active" => true,
        ]);

        $items = $teacher->achievementItems();
        $this->assertCount(2, $items);
    }

    public function test_short_bio(): void
    {
        $teacher = Teacher::query()->create([
            "full_name" => "Bio",
            "slug" => "bio-1",
            "bio" => "This is a very long bio that should be truncated properly for display purposes.",
            "is_active" => true,
        ]);

        $short = $teacher->shortBio(20);
        $this->assertStringEndsWith("...", $short);
    }

    public function test_bio_fallback(): void
    {
        $teacher = Teacher::query()->create([
            "full_name" => "No Bio",
            "slug" => "no-bio-1",
            "bio" => null,
            "is_active" => true,
        ]);

        $this->assertNotEmpty($teacher->shortBio());
    }
}