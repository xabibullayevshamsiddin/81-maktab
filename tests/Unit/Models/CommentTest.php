<?php

namespace Tests\Unit\Models;

use App\Models\Category;
use App\Models\Comment;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommentTest extends TestCase
{
    use RefreshDatabase;

    private function createPost(): Post
    {
        $category = Category::query()->create(["name" => "Test Cat"]);
        return Post::query()->create([
            "title" => "Test Post",
            "slug" => "test-post-" . uniqid(),
            "category_id" => $category->id,
            "short_content" => "Short",
            "content" => "Full content",
            "image" => "posts/test.jpg",
        ]);
    }

    public function test_can_be_created(): void
    {
        $post = $this->createPost();
        $comment = Comment::query()->create([
            "commentable_type" => Post::class,
            "commentable_id" => $post->id,
            "user_name" => "Ali",
            "body" => "Great post!",
            "is_approved" => true,
        ]);

        $this->assertDatabaseHas("comments", ["body" => "Great post!"]);
    }

    public function test_commentable_polymorphic(): void
    {
        $post = $this->createPost();
        $comment = Comment::query()->create([
            "commentable_type" => Post::class,
            "commentable_id" => $post->id,
            "user_name" => "Ali",
            "body" => "Nice!",
            "is_approved" => true,
        ]);

        $this->assertInstanceOf(Post::class, $comment->commentable);
        $this->assertSame($post->id, $comment->commentable->id);
    }

    public function test_replies_relation(): void
    {
        $post = $this->createPost();
        $parent = Comment::query()->create([
            "commentable_type" => Post::class,
            "commentable_id" => $post->id,
            "user_name" => "Parent",
            "body" => "Parent comment",
            "is_approved" => true,
        ]);

        Comment::query()->create([
            "commentable_type" => Post::class,
            "commentable_id" => $post->id,
            "parent_id" => $parent->id,
            "user_name" => "Child",
            "body" => "Reply comment",
            "is_approved" => true,
        ]);

        $this->assertCount(1, $parent->fresh()->replies);
    }

    public function test_approval_filter(): void
    {
        $post = $this->createPost();
        Comment::query()->create([
            "commentable_type" => Post::class, "commentable_id" => $post->id,
            "user_name" => "A", "body" => "Approved", "is_approved" => true,
        ]);
        Comment::query()->create([
            "commentable_type" => Post::class, "commentable_id" => $post->id,
            "user_name" => "B", "body" => "Pending", "is_approved" => false,
        ]);

        $this->assertCount(1, Comment::query()->where("is_approved", true)->get());
        $this->assertCount(1, Comment::query()->where("is_approved", false)->get());
    }

    public function test_likes_relation(): void
    {
        $post = $this->createPost();
        $comment = Comment::query()->create([
            "commentable_type" => Post::class, "commentable_id" => $post->id,
            "user_name" => "Ali", "body" => "Liked", "is_approved" => true,
        ]);

        $this->assertCount(0, $comment->likes);
    }

    public function test_has_parent(): void
    {
        $post = $this->createPost();
        $parent = Comment::query()->create([
            "commentable_type" => Post::class, "commentable_id" => $post->id,
            "user_name" => "P", "body" => "Parent", "is_approved" => true,
        ]);
        $child = Comment::query()->create([
            "commentable_type" => Post::class, "commentable_id" => $post->id,
            "parent_id" => $parent->id,
            "user_name" => "C", "body" => "Child", "is_approved" => true,
        ]);

        $this->assertNotNull($child->parent);
        $this->assertSame($parent->id, $child->parent->id);
    }
}