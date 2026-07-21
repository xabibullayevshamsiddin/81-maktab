<?php

namespace Tests\Feature;

use App\Models\Teacher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TeacherImageDisplayTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_page_renders_storage_image_url(): void
    {
        Storage::fake('public');

        $imagePath = 'teachers/test-avatar.jpg';
        Storage::disk('public')->put($imagePath, 'fake-image-content');

        $teacher = Teacher::query()->create([
            'full_name' => 'Olim Olimov',
            'slug' => 'olim-olimov',
            'subject' => 'Informatika',
            'image' => $imagePath,
            'is_active' => true,
        ]);

        $expectedFragment = '/storage/'.$imagePath;

        $this->get(route('teacher.show', $teacher))
            ->assertOk()
            ->assertSee($expectedFragment, false);

        $this->assertStringContainsString(
            $expectedFragment,
            $teacher->fresh()->imageUrl()
        );
    }

    public function test_teacher_image_url_normalizes_legacy_storage_prefix(): void
    {
        Storage::fake('public');

        $teacher = Teacher::query()->create([
            'full_name' => 'Legacy Teacher',
            'slug' => 'legacy-teacher',
            'subject' => 'Fizika',
            'image' => 'storage/teachers/legacy.jpg',
            'is_active' => true,
        ]);

        $this->assertSame(
            Storage::disk('public')->url('teachers/legacy.jpg'),
            $teacher->imageUrl()
        );
    }
}
