<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Post;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PostVideoUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_post_with_local_video_file(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create([
            'phone' => '+998901234567',
            'role_id' => Role::query()->where('name', Role::NAME_ADMIN)->value('id'),
            'is_active' => true,
        ]);

        $category = Category::create(['name' => 'Video Category']);

        $response = $this->actingAs($admin)->post(route('posts.store'), [
            'title' => 'Post with Video',
            'category_id' => $category->id,
            'post_kind' => 'video_news',
            'short_content' => 'Short content',
            'content' => 'Full content',
            'image' => UploadedFile::fake()->image('cover.jpg'),
            'video_file' => UploadedFile::fake()->create('clip.mp4', 1024, 'video/mp4'),
        ]);

        $response->assertRedirect(route('posts.index'));

        $post = Post::query()->firstOrFail();

        $this->assertNotNull($post->video_path);
        $this->assertSame('video_news', $post->post_kind);
        Storage::disk('public')->assertExists($post->image);
        Storage::disk('public')->assertExists($post->video_path);
    }
}
