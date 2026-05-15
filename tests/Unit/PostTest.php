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

        $post = Post::create($this->postPayload($category->id, [
            'title' => 'Test Post',
            'slug' => 'test-post',
        ]));

        $this->assertDatabaseHas('posts', [
            'title' => 'Test Post',
            'slug' => 'test-post',
        ]);
    }

    public function test_post_belongs_to_category(): void
    {
        $category = Category::create(['name' => 'Test Category']);

        $post = Post::create($this->postPayload($category->id, [
            'title' => 'Test Post',
            'slug' => 'test-post',
        ]));

        $this->assertInstanceOf(Category::class, $post->category);
        $this->assertEquals($category->id, $post->category->id);
    }

    public function test_post_has_comments(): void
    {
        $category = Category::create(['name' => 'Test Category']);

        $post = Post::create($this->postPayload($category->id, [
            'title' => 'Test Post',
            'slug' => 'test-post',
        ]));

        $this->assertEmpty($post->comments);
    }

    public function test_post_has_likes(): void
    {
        $category = Category::create(['name' => 'Test Category']);

        $post = Post::create($this->postPayload($category->id, [
            'title' => 'Test Post',
            'slug' => 'test-post',
        ]));

        $this->assertEmpty($post->likes);
    }

    public function test_post_slug_is_unique(): void
    {
        $category = Category::create(['name' => 'Test Category']);

        Post::create($this->postPayload($category->id, [
            'title' => 'Test Post',
            'slug' => 'test-post',
        ]));

        $this->expectException(\Illuminate\Database\QueryException::class);

        Post::create($this->postPayload($category->id, [
            'title' => 'Test Post',
            'slug' => 'test-post',
            'short_content' => 'Short content 2',
            'content' => 'Full content 2',
            'image' => 'posts/test-2.jpg',
        ]));
    }

    public function test_post_scope_search(): void
    {
        $category = Category::create(['name' => 'Test Category']);

        Post::create($this->postPayload($category->id, [
            'title' => 'Laravel Tutorial',
            'slug' => 'laravel-tutorial',
        ]));

        Post::create($this->postPayload($category->id, [
            'title' => 'PHP Guide',
            'slug' => 'php-guide',
            'image' => 'posts/php-guide.jpg',
        ]));

        $searchResults = Post::search('Laravel')->get();

        $this->assertCount(1, $searchResults);
        $this->assertEquals('Laravel Tutorial', $searchResults->first()->title);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function postPayload(int $categoryId, array $overrides = []): array
    {
        return array_merge([
            'title' => 'Test Post',
            'slug' => 'test-post',
            'short_content' => 'Short content',
            'content' => 'Full content',
            'category_id' => $categoryId,
            'post_kind' => 'general',
            'image' => 'posts/test.jpg',
        ], $overrides);
    }
}
