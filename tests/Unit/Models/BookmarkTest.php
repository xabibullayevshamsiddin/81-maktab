<?php

namespace Tests\Unit\Models;

use App\Models\Bookmark;
use App\Models\Category;
use App\Models\Post;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookmarkTest extends TestCase
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

    private function createUser(): User
    {
        return User::query()->create([
            "name" => "User",
            "email" => "bm-" . uniqid() . "@example.com",
            "password" => bcrypt("password"),
            "role_id" => Role::idByName(Role::NAME_USER),
            "is_active" => true,
        ]);
    }

    private function createPost(): Post
    {
        $cat = Category::query()->create(["name" => "Cat"]);
        return Post::query()->create([
            "title" => "Post",
            "slug" => "post-" . uniqid(),
            "category_id" => $cat->id,
            "short_content" => "Short",
            "content" => "Full",
            "image" => "posts/img.jpg",
        ]);
    }

    public function test_can_bookmark_post(): void
    {
        $user = $this->createUser();
        $post = $this->createPost();

        $bookmark = Bookmark::query()->create([
            "user_id" => $user->id,
            "bookmarkable_type" => Post::class,
            "bookmarkable_id" => $post->id,
        ]);

        $this->assertDatabaseHas("bookmarks", [
            "user_id" => $user->id,
            "bookmarkable_id" => $post->id,
        ]);
    }

    public function test_polymorphic_relation(): void
    {
        $user = $this->createUser();
        $post = $this->createPost();

        Bookmark::query()->create([
            "user_id" => $user->id,
            "bookmarkable_type" => Post::class,
            "bookmarkable_id" => $post->id,
        ]);

        $this->assertCount(1, $user->bookmarks);
    }

    public function test_bookmarkable_is_post(): void
    {
        $user = $this->createUser();
        $post = $this->createPost();

        $bookmark = Bookmark::query()->create([
            "user_id" => $user->id,
            "bookmarkable_type" => Post::class,
            "bookmarkable_id" => $post->id,
        ]);

        $this->assertInstanceOf(Post::class, $bookmark->bookmarkable);
    }
}