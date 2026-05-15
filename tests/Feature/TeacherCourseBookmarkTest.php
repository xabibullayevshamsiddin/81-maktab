<?php

namespace Tests\Feature;

use App\Models\Bookmark;
use App\Models\Course;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeacherCourseBookmarkTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_bookmark_teacher_and_course(): void
    {
        $user = User::factory()->create(['is_active' => true]);

        $teacher = Teacher::query()->create([
            'full_name' => 'Ustoz Test',
            'slug' => 'ustoz-test-'.uniqid(),
            'subject' => 'Matematika',
            'experience_years' => 5,
            'image' => 'teachers/t.jpg',
            'is_active' => true,
        ]);

        $course = Course::query()->create([
            'teacher_id' => $teacher->id,
            'created_by' => $user->id,
            'title' => 'Kurs A',
            'title_en' => 'Course A',
            'price' => '100',
            'price_en' => '100',
            'duration' => '1 oy',
            'duration_en' => '1 mo',
            'description' => 'Desc',
            'description_en' => 'Desc',
            'image' => 'courses/c.jpg',
            'start_date' => '2026-05-01',
            'status' => Course::STATUS_PUBLISHED,
        ]);

        $this->actingAs($user)
            ->postJson(route('teacher.bookmark.toggle', $teacher))
            ->assertOk()
            ->assertJsonPath('bookmarked', true);

        $this->assertDatabaseHas('bookmarks', [
            'user_id' => $user->id,
            'bookmarkable_type' => Teacher::class,
            'bookmarkable_id' => $teacher->id,
        ]);

        $this->actingAs($user)
            ->postJson(route('course.bookmark.toggle', $course))
            ->assertOk()
            ->assertJsonPath('bookmarked', true);

        $this->assertDatabaseHas('bookmarks', [
            'user_id' => $user->id,
            'bookmarkable_type' => Course::class,
            'bookmarkable_id' => $course->id,
        ]);

        $teacher->delete();

        $this->assertDatabaseMissing('bookmarks', [
            'bookmarkable_type' => Teacher::class,
            'bookmarkable_id' => $teacher->id,
        ]);
    }
}
