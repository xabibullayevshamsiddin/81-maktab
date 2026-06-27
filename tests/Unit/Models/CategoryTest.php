<?php

namespace Tests\Unit\Models;

use App\Models\Category;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_be_created(): void
    {
        $category = Category::query()->create(["name" => "Fanlar"]);
        $this->assertDatabaseHas("categories", ["name" => "Fanlar"]);
    }

    public function test_name_is_required(): void
    {
        $this->expectException(\Illuminate\Database\QueryException::class);
        Category::query()->create(["name" => null]);
    }

    public function test_has_posts(): void
    {
        $category = Category::query()->create(["name" => "Test"]);
        $this->assertCount(0, $category->posts);

        Post::query()->create([
            "title" => "Post 1",
            "slug" => "post-1-" . uniqid(),
            "category_id" => $category->id,
            "short_content" => "Short",
            "content" => "Full",
            "image" => "posts/img.jpg",
        ]);

        $this->assertCount(1, $category->fresh()->posts);
    }

    public function test_scope_order_by_name(): void
    {
        Category::query()->create(["name" => "Zebra"]);
        Category::query()->create(["name" => "Apple"]);
        Category::query()->create(["name" => "Mango"]);

        $categories = Category::orderByName()->get();

        $this->assertSame("Apple", $categories->first()->name);
        $this->assertSame("Zebra", $categories->last()->name);
    }

    public function test_localized_name(): void
    {
        $category = Category::query()->create([
            "name" => "Yangiliklar",
            "name_en" => "News",
        ]);

        app()->setLocale("en");
        $this->assertSame("News", localized_model_value($category, "name"));

        app()->setLocale("uz");
        $this->assertSame("Yangiliklar", localized_model_value($category, "name"));
    }

    public function test_post_count_attribute(): void
    {
        $category = Category::query()->create(["name" => "Count Test"]);
        $this->assertSame(0, $category->posts_count);

        Post::query()->create([
            "title" => "P1", "slug" => "p1c-" . uniqid(),
            "category_id" => $category->id,
            "short_content" => "S", "content" => "F", "image" => "img.jpg",
        ]);

        $this->assertSame(1, $category->fresh()->posts_count);
    }
}