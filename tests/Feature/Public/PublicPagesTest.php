<?php

namespace Tests\Feature\Public;

use App\Models\Category;
use App\Models\Course;
use App\Models\Post;
use App\Models\Role;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
    }

    /**
     * Homepage
     */
    public function test_homepage_loads_successfully(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_homepage_displays_latest_posts(): void
    {
        $category = Category::factory()->create();
        $post = Post::factory()->create([
            'title' => 'Test Post Title',
            'category_id' => $category->id,
        ]);

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Test Post Title');
    }

    /**
     * About Page
     */
    public function test_about_page_loads(): void
    {
        $response = $this->get('/about');

        $response->assertStatus(200);
    }

    /**
     * Contact Page
     */
    public function test_contact_page_loads(): void
    {
        $response = $this->get('/contact');

        $response->assertStatus(200);
    }

    /**
     * Posts
     */
    public function test_posts_page_loads(): void
    {
        $response = $this->get('/posts');

        $response->assertStatus(200);
    }

    public function test_single_post_page_loads(): void
    {
        $category = Category::factory()->create();
        $post = Post::factory()->create([
            'slug' => 'test-post',
            'category_id' => $category->id,
        ]);

        $response = $this->get("/posts/{$post->slug}");

        $response->assertStatus(200);
    }

    public function test_posts_can_be_filtered_by_category(): void
    {
        $category = Category::factory()->create(['name' => 'TestCat']);
        $post = Post::factory()->create([
            'title' => 'Categorized Post',
            'category_id' => $category->id,
        ]);

        $response = $this->get("/posts?category_id={$category->id}");

        $response->assertStatus(200);
    }

    public function test_posts_can_be_searched(): void
    {
        $category = Category::factory()->create();
        $post = Post::factory()->create([
            'title' => 'UniqueSearchableTitle',
            'category_id' => $category->id,
        ]);

        $response = $this->get('/posts?q=UniqueSearchableTitle');

        $response->assertStatus(200);
    }

    /**
     * Teachers
     */
    public function test_teachers_page_loads(): void
    {
        $response = $this->get('/teachers');

        $response->assertStatus(200);
    }

    public function test_teacher_profile_page_loads(): void
    {
        $teacher = Teacher::factory()->create([
            'slug' => 'test-teacher',
            'is_active' => true,
        ]);

        $response = $this->get("/teachers/{$teacher->slug}");

        $response->assertStatus(200);
    }

    public function test_inactive_teacher_returns_404(): void
    {
        $teacher = Teacher::factory()->create([
            'slug' => 'inactive-teacher',
            'is_active' => false,
        ]);

        $response = $this->get("/teachers/{$teacher->slug}");

        $response->assertStatus(404);
    }

    /**
     * Courses
     */
    public function test_courses_page_loads(): void
    {
        $response = $this->get('/courses');

        $response->assertStatus(200);
    }

    public function test_course_detail_page_loads(): void
    {
        $teacher = Teacher::factory()->create(['is_active' => true]);
        $user = User::factory()->create([
            'role_id' => Role::where('name', Role::NAME_TEACHER)->first()->id,
        ]);
        $course = Course::factory()->create([
            'status' => 'published',
            'teacher_id' => $teacher->id,
            'created_by' => $user->id,
        ]);

        $response = $this->get("/courses/{$course->id}");

        $response->assertStatus(200);
    }

    /**
     * Exams
     */
    public function test_exams_page_loads(): void
    {
        $response = $this->get('/exams');

        $response->assertStatus(200);
    }

    /**
     * Calendar
     */
    public function test_calendar_page_loads(): void
    {
        $response = $this->get('/calendar');

        $response->assertStatus(200);
    }

    /**
     * Global Search
     */
    public function test_global_search_works(): void
    {
        $response = $this->get('/search?q=test');

        $response->assertStatus(200);
    }

    public function test_global_search_with_empty_query(): void
    {
        $response = $this->get('/search?q=');

        $response->assertStatus(200);
    }

    public function test_search_escapes_sql_wildcards(): void
    {
        // Ensure no SQL errors with special characters
        $response = $this->get('/search?q=%25admin%25');

        $response->assertStatus(200);
    }

    public function test_search_handles_special_characters(): void
    {
        $response = $this->get('/search?q=' . urlencode("test'OR 1=1--"));

        $response->assertStatus(200);
    }

    /**
     * Locale
     */
    public function test_locale_switch_to_english(): void
    {
        $response = $this->get('/locale/en');

        $response->assertRedirect();
    }

    public function test_locale_switch_to_uzbek(): void
    {
        $response = $this->get('/locale/uz');

        $response->assertRedirect();
    }
}
