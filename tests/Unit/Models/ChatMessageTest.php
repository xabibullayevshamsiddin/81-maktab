<?php

namespace Tests\Unit\Models;

use App\Models\ChatMessage;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatMessageTest extends TestCase
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
            "name" => "Chat User",
            "email" => "chat-" . uniqid() . "@example.com",
            "password" => bcrypt("password"),
            "role_id" => Role::idByName(Role::NAME_USER),
            "is_active" => true,
        ]);
    }

    public function test_can_send_message(): void
    {
        $user = $this->createUser();

        $msg = ChatMessage::query()->create([
            "user_id" => $user->id,
            "body" => "Salom!",
        ]);

        $this->assertDatabaseHas("chat_messages", ["body" => "Salom!"]);
    }

    public function test_belongs_to_user(): void
    {
        $user = $this->createUser();
        $msg = ChatMessage::query()->create([
            "user_id" => $user->id,
            "body" => "Test",
        ]);

        $this->assertInstanceOf(User::class, $msg->user);
        $this->assertSame($user->id, $msg->user->id);
    }
}
