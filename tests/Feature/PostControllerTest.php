<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'phone' => '+998901234567',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);
    }

    public function test_admin_can_view_posts_list(): void
    {
        $this->actingAs($this->admin);

        $response = $this->get('/admin/posts');

        $response->assertStatus(200);
    }

    public function test_admin_can_create_post(): void
    {
        $this->actingAs($this->admin);

        $category = Category::create(['name' => 'Test Category']);

        $response = $this->post('/admin/posts', [
            'title' => 'New Post',
            'category_id' => $category->id,
            'short_content' => 'Short content',
            'content' => 'Full content',
        ]);

        $this->assertDatabaseHas('posts', ['title' => 'New Post']);
    }

    public function test_admin_can_update_post(): void
    {
        $this->actingAs($this->admin);

        $category = Category::create(['name' => 'Test Category']);

        $post = Post::create([
            'title' => 'Old Title',
            'slug' => 'old-title',
            'short_content' => 'Short content',
            'content' => 'Full content',
            'category_id' => $category->id,
        ]);

        $response = $this->put("/admin/posts/{$post->id}", [
            'title' => 'New Title',
            'category_id' => $category->id,
            'short_content' => 'Short content',
            'content' => 'Full content',
        ]);

        $this->assertDatabaseHas('posts', ['title' => 'New Title']);
    }

    public function test_admin_can_delete_post(): void
    {
        $this->actingAs($this->admin);

        $category = Category::create(['name' => 'Test Category']);

        $post = Post::create([
            'title' => 'To Delete',
            'slug' => 'to-delete',
            'short_content' => 'Short content',
            'content' => 'Full content',
            'category_id' => $category->id,
        ]);

        $response = $this->delete("/admin/posts/{$post->id}");

        $this->assertDatabaseMissing('posts', ['id' => $post->id]);
    }

    public function test_guest_cannot_access_admin_routes(): void
    {
        $response = $this->get('/admin/posts');

        $response->assertRedirect('/login');
    }

    public function test_post_creation_requires_title(): void
    {
        $this->actingAs($this->admin);

        $category = Category::create(['name' => 'Test Category']);

        $response = $this->post('/admin/posts', [
            'category_id' => $category->id,
            'short_content' => 'Short content',
            'content' => 'Full content',
        ]);

        $response->assertSessionHasErrors('title');
    }
}
