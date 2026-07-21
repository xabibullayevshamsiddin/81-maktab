<?php

namespace Tests\Unit\Models;

use App\Models\ChatGroup;
use App\Models\ChatGroupMember;
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
            "message" => "Salom!",
        ]);

        $this->assertDatabaseHas("chat_messages", ["message" => "Salom!"]);
    }

    public function test_belongs_to_user(): void
    {
        $user = $this->createUser();
        $msg = ChatMessage::query()->create([
            "user_id" => $user->id,
            "message" => "Test",
        ]);

        $this->assertInstanceOf(User::class, $msg->user);
        $this->assertSame($user->id, $msg->user->id);
    }

    public function test_can_belong_to_group(): void
    {
        $user = $this->createUser();
        $group = ChatGroup::query()->create([
            "name" => "Test Group",
            "created_by" => $user->id,
        ]);

        $msg = ChatMessage::query()->create([
            "user_id" => $user->id,
            "chat_group_id" => $group->id,
            "message" => "Group message",
        ]);

        $this->assertInstanceOf(ChatGroup::class, $msg->group);
    }

    public function test_private_message(): void
    {
        $user1 = $this->createUser();
        $user2 = $this->createUser();

        $msg = ChatMessage::query()->create([
            "user_id" => $user1->id,
            "receiver_id" => $user2->id,
            "message" => "Private message",
        ]);

        $this->assertSame($user2->id, $msg->receiver_id);
    }
}