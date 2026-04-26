<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Course;
use App\Models\Post;
use App\Models\Teacher;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PublicMediaCleanupTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->createMediaTestTables();
    }

    protected function tearDown(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('courses');
        Schema::dropIfExists('teachers');
        Schema::dropIfExists('posts');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('users');
        Schema::enableForeignKeyConstraints();

        parent::tearDown();
    }

    public function test_deleting_post_removes_image_and_video_file(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('posts/cover.jpg', 'image');
        Storage::disk('public')->put('posts/videos/clip.mp4', 'video');

        $category = Category::query()->create(['name' => 'Yangilik']);
        $post = Post::query()->create([
            'title' => 'Tozalash testi',
            'slug' => 'tozalash-testi',
            'category_id' => $category->id,
            'short_content' => 'Qisqa matn',
            'content' => 'To\'liq matn',
            'image' => 'posts/cover.jpg',
            'video_path' => 'posts/videos/clip.mp4',
        ]);

        $post->delete();

        $this->assertFalse(Storage::disk('public')->exists('posts/cover.jpg'));
        $this->assertFalse(Storage::disk('public')->exists('posts/videos/clip.mp4'));
    }

    public function test_deleting_teacher_removes_teacher_and_cascaded_course_images(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('teachers/teacher.jpg', 'image');
        Storage::disk('public')->put('courses/course.jpg', 'image');

        $userId = DB::table('users')->insertGetId([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $teacher = Teacher::query()->create([
            'full_name' => 'Test Ustoz',
            'slug' => 'test-ustoz',
            'subject' => 'Matematika',
            'experience_years' => 5,
            'image' => 'teachers/teacher.jpg',
        ]);

        Course::query()->create([
            'teacher_id' => $teacher->id,
            'created_by' => $userId,
            'title' => 'Test kurs',
            'price' => '0',
            'duration' => '1 oy',
            'description' => 'Test kurs tavsifi',
            'start_date' => now()->toDateString(),
            'image' => 'courses/course.jpg',
        ]);

        $teacher->delete();

        $this->assertFalse(Storage::disk('public')->exists('teachers/teacher.jpg'));
        $this->assertFalse(Storage::disk('public')->exists('courses/course.jpg'));
    }

    public function test_prune_orphaned_media_command_deletes_only_with_delete_option(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('posts/used.jpg', 'image');
        Storage::disk('public')->put('posts/orphan.jpg', 'image');

        $category = Category::query()->create(['name' => 'Yangilik']);
        Post::query()->create([
            'title' => 'Bor post',
            'slug' => 'bor-post',
            'category_id' => $category->id,
            'short_content' => 'Qisqa matn',
            'content' => 'To\'liq matn',
            'image' => 'posts/used.jpg',
        ]);

        $this->artisan('storage:prune-orphaned-media')
            ->expectsOutputToContain('Orphaned media: 1 ta fayl.')
            ->assertExitCode(0);
        $this->assertTrue(Storage::disk('public')->exists('posts/orphan.jpg'));

        $this->artisan('storage:prune-orphaned-media --delete')
            ->expectsOutputToContain("O'chirildi: 1 ta fayl.")
            ->assertExitCode(0);
        $this->assertFalse(Storage::disk('public')->exists('posts/orphan.jpg'));
        $this->assertTrue(Storage::disk('public')->exists('posts/used.jpg'));
    }

    private function createMediaTestTables(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('courses');
        Schema::dropIfExists('teachers');
        Schema::dropIfExists('posts');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('users');
        Schema::enableForeignKeyConstraints();

        Schema::create('categories', static function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('posts', static function (Blueprint $table): void {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->string('title');
            $table->string('slug')->nullable();
            $table->text('short_content')->nullable();
            $table->text('content')->nullable();
            $table->string('image')->nullable();
            $table->string('video_path')->nullable();
            $table->timestamps();
        });

        Schema::create('users', static function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->timestamps();
        });

        Schema::create('teachers', static function (Blueprint $table): void {
            $table->id();
            $table->string('full_name');
            $table->string('slug')->unique();
            $table->string('subject')->nullable();
            $table->unsignedSmallInteger('experience_years')->default(0);
            $table->string('image')->nullable();
            $table->timestamps();
        });

        Schema::create('courses', static function (Blueprint $table): void {
            $table->id();
            $table->foreignId('teacher_id')->constrained('teachers')->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->string('price', 100);
            $table->string('duration', 120);
            $table->text('description');
            $table->date('start_date');
            $table->string('image')->nullable();
            $table->timestamps();
        });
    }
}
