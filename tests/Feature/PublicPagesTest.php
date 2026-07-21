<?php

namespace Tests\Feature;

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

    /**
     * @return array<string, string>
     */
    public static function publicGetRoutesProvider(): array
    {
        return [
            'home' => ['home', '/'],
            'about' => ['about', '/about'],
            'login' => ['login', '/login'],
            'register' => ['register', '/register'],
            'courses' => ['courses', '/courses'],
            'posts index' => ['post', '/post'],
            'teachers index' => ['teacher', '/teacher'],
            'contact' => ['contact', '/contact'],
            'calendar' => ['calendar', '/taqvim'],
            'search' => ['search', '/search'],
            'privacy policy' => ['privacy-policy', '/privacy-policy'],
            'terms' => ['terms', '/terms'],
            'feature requests' => ['feature-requests.index', '/feature-requests'],
        ];
    }

    /**
     * @dataProvider publicGetRoutesProvider
     */
    public function test_public_pages_are_accessible(string $routeName, string $expectedPath): void
    {
        $this->assertSame($expectedPath, route($routeName, [], false));

        $this->get(route($routeName))
            ->assertOk();
    }

    public function test_homepage_renders_public_styles_without_index_php_in_asset_paths(): void
    {
        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('/temp/css/style.css', false);
        $response->assertDontSee('/index.php/temp/css/style.css', false);
    }

    public function test_post_detail_page_is_accessible(): void
    {
        $category = Category::query()->create(['name' => 'Yangiliklar']);

        $post = Post::query()->create([
            'category_id' => $category->id,
            'title' => 'Test yangilik',
            'short_content' => 'Qisqa matn',
            'content' => 'To\'liq matn',
            'slug' => 'test-yangilik',
            'post_kind' => 'general',
            'image' => 'posts/test-yangilik.jpg',
        ]);

        $this->get(route('post.show', $post))
            ->assertOk()
            ->assertSee('Test yangilik');
    }

    public function test_teacher_detail_page_is_accessible(): void
    {
        $teacher = Teacher::query()->create([
            'full_name' => 'Ali Valiyev',
            'slug' => 'ali-valiyev',
            'subject' => 'Matematika',
            'is_active' => true,
        ]);

        $this->get(route('teacher.show', $teacher))
            ->assertOk()
            ->assertSee('Ali Valiyev');
    }

    public function test_course_detail_page_is_accessible(): void
    {
        $owner = User::query()->create([
            'name' => 'Course Owner',
            'first_name' => 'Course',
            'last_name' => 'Owner',
            'email' => 'course-owner@example.com',
            'phone' => '+998901234567',
            'password' => 'password123',
            'role_id' => Role::defaultUserRoleId(),
            'is_active' => true,
        ]);

        $course = Course::query()->create([
            'title' => 'Python asoslari',
            'description' => 'Kurs tavsifi',
            'price' => 0,
            'duration' => '2 oy',
            'start_date' => now()->toDateString(),
            'status' => Course::STATUS_PUBLISHED,
            'created_by' => $owner->id,
        ]);

        $this->get(route('courses.show', $course))
            ->assertOk()
            ->assertSee('Python asoslari');
    }

    public function test_legacy_home_path_redirects_to_root(): void
    {
        $this->get('/home')
            ->assertRedirect('/');
    }
}
