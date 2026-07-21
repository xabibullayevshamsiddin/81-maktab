<?php

namespace Tests\Unit\Models;

use App\Models\Category;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostTest extends TestCase
{
    use RefreshDatabase;

    private function createCategory(): Category
    {
        return Category::query()->create(["name" => "Test Cat"]);
    }

    private function createPost(array $overrides = []): Post
    {
        $cat = $this->createCategory();
        return Post::query()->create(array_merge([
            "title" => "Test Post",
            "slug" => "test-post-" . uniqid(),
            "category_id" => $cat->id,
            "short_content" => "Short content here",
            "content" => "Full content here with more details.",
            "image" => "posts/test.jpg",
        ], $overrides));
    }

    public function test_can_be_created(): void
    {
        $post = $this->createPost(["title" => "Yangilik 1"]);
        $this->assertDatabaseHas("posts", ["title" => "Yangilik 1"]);
    }

    public function test_belongs_to_category(): void
    {
        $post = $this->createPost();
        $this->assertInstanceOf(Category::class, $post->category);
    }

    public function test_has_comments(): void
    {
        $post = $this->createPost();
        $this->assertCount(0, $post->comments);
    }

    public function test_has_likes(): void
    {
        $post = $this->createPost();
        $this->assertCount(0, $post->likes);
    }

    public function test_slug_is_unique(): void
    {
        $slug = "unique-slug-" . uniqid();
        $this->createPost(["slug" => $slug]);

        $this->expectException(\Illuminate\Database\QueryException::class);
        $this->createPost(["slug" => $slug]);
    }

    public function test_search_scope(): void
    {
        $this->createPost(["title" => "Laravel Tutorial", "slug" => "lt-" . uniqid()]);
        $this->createPost(["title" => "PHP Guide", "slug" => "php-" . uniqid()]);

        $results = Post::search("Laravel")->get();
        $this->assertCount(1, $results);
        $this->assertSame("Laravel Tutorial", $results->first()->title);
    }

    public function test_video_detection(): void
    {
        $post = $this->createPost();
        $this->assertFalse($post->hasVideo());

        $post2 = $this->createPost([
            "video_url" => "https://youtube.com/watch?v=test",
            "slug" => "vid-" . uniqid(),
        ]);
        $this->assertTrue($post2->hasVideo());
    }

    public function test_post_kind_default(): void
    {
        $post = $this->createPost();
        $this->assertSame("general", $post->post_kind);
    }

    public function test_latest_scope(): void
    {
        $this->createPost(["title" => "First", "slug" => "f-" . uniqid()]);
        $this->createPost(["title" => "Second", "slug" => "s-" . uniqid()]);

        $latest = Post::query()->latest()->get();
        $this->assertSame("Second", $latest->first()->title);
    }

    public function test_fallback_category(): void
    {
        $post = Post::query()->create([
            "title" => "No Cat",
            "slug" => "no-cat-" . uniqid(),
            "short_content" => "Short",
            "content" => "Full",
            "image" => "posts/default.jpg",
        ]);

        $this->assertNotNull($post->category);
    }

    public function test_localized_title(): void
    {
        $post = $this->createPost([
            "title" => "Yangilik",
            "title_en" => "News",
        ]);

        app()->setLocale("en");
        $this->assertSame("News", localized_model_value($post, "title"));

        app()->setLocale("uz");
        $this->assertSame("Yangilik", localized_model_value($post, "title"));
    }

    public function test_bookmarks_relation(): void
    {
        $post = $this->createPost();
        $this->assertCount(0, $post->bookmarks);
    }

    public function test_post_kind_config(): void
    {
        $kinds = config("post_kinds");
        $this->assertArrayHasKey("general", $kinds);
        $this->assertArrayHasKey("video_news", $kinds);
        $this->assertArrayHasKey("social", $kinds);
    }
}