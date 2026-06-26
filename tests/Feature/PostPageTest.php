<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_post_list_shows_posts(): void
    {
        $category = Category::query()->create(["name" => "News"]);
        Post::query()->create([
            "title" => "First News",
            "slug" => "first-news",
            "category_id" => $category->id,
            "short_content" => "Short",
            "content" => "Full",
            "image" => "posts/test.jpg",
        ]);

        $response = $this->get(route("post"));

        $response->assertOk();
        $response->assertSee("First News");
    }

    public function test_public_post_detail_shows_content(): void
    {
        $category = Category::query()->create(["name" => "News"]);
        $post = Post::query()->create([
            "title" => "Detail Post",
            "slug" => "detail-post",
            "category_id" => $category->id,
            "short_content" => "Short content",
            "content" => "Full content with details",
            "image" => "posts/test.jpg",
        ]);

        $response = $this->get(route("post.show", $post));

        $response->assertOk();
        $response->assertSee("Detail Post");
    }

    public function test_post_detail_404_for_missing(): void
    {
        $response = $this->get("/post/non-existent-slug");

        $response->assertRedirect(route("post"));
    }

    public function test_post_stats_page(): void
    {
        $category = Category::query()->create(["name" => "Stats"]);
        $post = Post::query()->create([
            "title" => "Stats Post",
            "slug" => "stats-post",
            "category_id" => $category->id,
            "short_content" => "Short",
            "content" => "Full",
            "image" => "posts/stats.jpg",
        ]);

        $response = $this->get(route("post.stats", $post));

        $response->assertOk();
    }
}