<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostTest extends TestCase
{
    use RefreshDatabase;

    public function test_post_can_be_created(): void
    {
        $category = Category::create(['name' => 'Test Category']);

        $post = Post::create([
            'title' => 'Test Post',
            'slug' => 'test-post',
            'short_content' => 'Short content',
            'content' => 'Full content',
            'category_id' => $category->id,
        ]);

        $this->assertDatabaseHas('posts', [
            'title' => 'Test Post',
            'slug' => 'test-post',
        ]);
    }

    public function test_post_belongs_to_category(): void
    {
        $category = Category::create(['name' => 'Test Category']);

        $post = Post::create([
            'title' => 'Test Post',
            'slug' => 'test-post',
            'short_content' => 'Short content',
            'content' => 'Full content',
            'category_id' => $category->id,
        ]);

        $this->assertInstanceOf(Category::class, $post->category);
        $this->assertEquals($category->id, $post->category->id);
    }

    public function test_post_has_comments(): void
    {
        $category = Category::create(['name' => 'Test Category']);

        $post = Post::create([
            'title' => 'Test Post',
            'slug' => 'test-post',
            'short_content' => 'Short content',
            'content' => 'Full content',
            'category_id' => $category->id,
        ]);

        $this->assertEmpty($post->comments);
    }

    public function test_post_has_likes(): void
    {
        $category = Category::create(['name' => 'Test Category']);

        $post = Post::create([
            'title' => 'Test Post',
            'slug' => 'test-post',
            'short_content' => 'Short content',
            'content' => 'Full content',
            'category_id' => $category->id,
        ]);

        $this->assertEmpty($post->likes);
    }

    public function test_post_slug_is_unique(): void
    {
        $category = Category::create(['name' => 'Test Category']);

        Post::create([
            'title' => 'Test Post',
            'slug' => 'test-post',
            'short_content' => 'Short content',
            'content' => 'Full content',
            'category_id' => $category->id,
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);

        Post::create([
            'title' => 'Test Post',
            'slug' => 'test-post',
            'short_content' => 'Short content 2',
            'content' => 'Full content 2',
            'category_id' => $category->id,
        ]);
    }

    public function test_post_scope_search(): void
    {
        $category = Category::create(['name' => 'Test Category']);

        Post::create([
            'title' => 'Laravel Tutorial',
            'slug' => 'laravel-tutorial',
            'short_content' => 'Short content',
            'content' => 'Full content',
            'category_id' => $category->id,
        ]);

        Post::create([
            'title' => 'PHP Guide',
            'slug' => 'php-guide',
            'short_content' => 'Short content',
            'content' => 'Full content',
            'category_id' => $category->id,
        ]);

        $searchResults = Post::search('Laravel')->get();

        $this->assertCount(1, $searchResults);
        $this->assertEquals('Laravel Tutorial', $searchResults->first()->title);
    }
}
