<?php

namespace Tests\Feature;

use App\Models\Bookmark;
use App\Models\Category;
use App\Models\Post;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostBookmarkTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_teacher_can_save_and_unsave_post(): void
    {
        $teacher = $this->makeUser(User::ROLE_TEACHER);
        $post = $this->makePost();

        $this->actingAs($teacher)
            ->postJson(route('post.bookmark.toggle', $post), [])
            ->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonPath('bookmarked', true)
            ->assertJsonFragment(['message' => __('public.bookmark.added')]);

        $this->assertDatabaseHas('bookmarks', [
            'user_id' => $teacher->id,
            'bookmarkable_type' => Post::class,
            'bookmarkable_id' => $post->id,
        ]);

        $this->actingAs($teacher)
            ->postJson(route('post.bookmark.toggle', $post), [])
            ->assertOk()
            ->assertJsonPath('bookmarked', false);

        $this->assertDatabaseMissing('bookmarks', [
            'user_id' => $teacher->id,
            'bookmarkable_type' => Post::class,
            'bookmarkable_id' => $post->id,
        ]);
    }

    public function test_deleting_post_removes_bookmarks(): void
    {
        $user = $this->makeUser(User::ROLE_USER);
        $post = $this->makePost();

        Bookmark::query()->create([
            'user_id' => $user->id,
            'bookmarkable_type' => Post::class,
            'bookmarkable_id' => $post->id,
        ]);

        $post->delete();

        $this->assertDatabaseMissing('bookmarks', [
            'user_id' => $user->id,
            'bookmarkable_id' => $post->id,
        ]);
    }

    private function makePost(): Post
    {
        $category = Category::query()->create(['name' => 'Test cat']);

        return Post::query()->create([
            'title' => 'Bookmark test',
            'slug' => 'bookmark-test-'.uniqid(),
            'category_id' => $category->id,
            'short_content' => 'Short',
            'content' => 'Body',
            'image' => 'posts/placeholder.jpg',
        ]);
    }

    private function makeUser(string $roleName): User
    {
        $role = Role::query()->firstOrCreate(
            ['name' => $roleName],
            [
                'label' => User::ROLES[$roleName] ?? $roleName,
                'level' => User::ROLE_HIERARCHY[$roleName] ?? 1,
                'is_system' => true,
            ]
        );

        return User::query()->create([
            'name' => 'Test User',
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => $roleName.'-'.uniqid().'@example.com',
            'phone' => '+998901234567',
            'password' => bcrypt('password123'),
            'role_id' => $role->id,
            'is_active' => true,
            'is_parent' => false,
        ]);
    }
}
