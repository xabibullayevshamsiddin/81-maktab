<?php

namespace Tests\Feature;

use App\Models\ChatMessage;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
    }

    private function createUser(): User
    {
        return User::factory()->create([
            'role_id' => Role::where('name', Role::NAME_USER)->first()->id,
            'is_active' => true,
        ]);
    }

    private function createAdmin(): User
    {
        return User::factory()->create([
            'role_id' => Role::where('name', Role::NAME_ADMIN)->first()->id,
            'is_active' => true,
        ]);
    }

    public function test_guest_cannot_access_chat(): void
    {
        $response = $this->get('/chat');

        $response->assertRedirect('/login');
    }

    public function test_user_can_access_chat(): void
    {
        $user = $this->createUser();

        $response = $this->actingAs($user)->get('/chat');

        $response->assertStatus(200);
    }

    public function test_user_can_send_message(): void
    {
        $user = $this->createUser();

        $response = $this->actingAs($user)->post('/chat', [
            'message' => 'Salom!',
        ]);

        $this->assertDatabaseHas('chat_messages', [
            'user_id' => $user->id,
            'message' => 'Salom!',
        ]);
    }

    public function test_message_cannot_be_empty(): void
    {
        $user = $this->createUser();

        $response = $this->actingAs($user)->post('/chat', [
            'message' => '',
        ]);

        $response->assertSessionHasErrors('message');
    }

    public function test_admin_can_delete_message(): void
    {
        $admin = $this->createAdmin();
        $user = $this->createUser();

        $message = ChatMessage::create([
            'user_id' => $user->id,
            'message' => 'Delete me',
        ]);

        $response = $this->actingAs($admin)->delete("/chat/{$message->id}");

        $this->assertDatabaseMissing('chat_messages', [
            'id' => $message->id,
        ]);
    }

    public function test_regular_user_cannot_delete_others_messages(): void
    {
        $user1 = $this->createUser();
        $user2 = $this->createUser();

        $message = ChatMessage::create([
            'user_id' => $user1->id,
            'message' => 'Private message',
        ]);

        $response = $this->actingAs($user2)->delete("/chat/{$message->id}");

        // Should be forbidden
        $this->assertDatabaseHas('chat_messages', [
            'id' => $message->id,
        ]);
    }

    public function test_message_is_sanitized(): void
    {
        $user = $this->createUser();

        $response = $this->actingAs($user)->post('/chat', [
            'message' => '<script>alert("xss")</script>Hello',
        ]);

        // Script tags should be stripped
        $message = ChatMessage::where('user_id', $user->id)->first();
        
        if ($message) {
            $this->assertStringNotContainsString('<script>', $message->message);
        }
    }

    public function test_inactive_user_cannot_send_message(): void
    {
        $user = $this->createUser();
        $user->update(['is_active' => false]);

        $response = $this->actingAs($user)->post('/chat', [
            'message' => 'Should fail',
        ]);

        // Should be blocked
        $this->assertTrue(
            $response->getStatusCode() === 403 || $response->isRedirection(),
            'Inactive user should not send messages'
        );
    }
}
