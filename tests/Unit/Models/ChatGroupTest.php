<?php

namespace Tests\Unit\Models;

use App\Models\ChatGroup;
use App\Models\ChatGroupMember;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatGroupTest extends TestCase
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
            "name" => "User",
            "email" => "u-" . uniqid() . "@example.com",
            "password" => bcrypt("password"),
            "role_id" => Role::idByName(Role::NAME_USER),
            "is_active" => true,
        ]);
    }

    public function test_can_be_created(): void
    {
        $user = $this->createUser();
        $group = ChatGroup::query()->create([
            "owner_id" => $user->id,
            "name" => "Test Group",
        ]);

        $this->assertDatabaseHas("chat_groups", ["name" => "Test Group"]);
    }

    public function test_has_members(): void
    {
        $user = $this->createUser();
        $group = ChatGroup::query()->create([
            "owner_id" => $user->id,
            "name" => "Group",
        ]);

        ChatGroupMember::query()->create([
            "chat_group_id" => $group->id,
            "user_id" => $user->id,
            "role" => "admin",
        ]);

        $this->assertCount(1, $group->fresh()->members);
    }

    public function test_has_messages(): void
    {
        $user = $this->createUser();
        $group = ChatGroup::query()->create([
            "owner_id" => $user->id,
            "name" => "Msg Group",
        ]);

        $this->assertCount(0, $group->messages);
    }

    public function test_privacy_and_image(): void
    {
        $user = $this->createUser();
        $group = ChatGroup::query()->create([
            "owner_id" => $user->id,
            "name" => "Private Group",
            "privacy" => ChatGroup::PRIVACY_CLOSED,
            "image" => "groups/img.jpg",
        ]);

        $this->assertSame(ChatGroup::PRIVACY_CLOSED, $group->privacy);
        $this->assertSame("groups/img.jpg", $group->image);
    }

    public function test_join_requests(): void
    {
        $user = $this->createUser();
        $group = ChatGroup::query()->create([
            "owner_id" => $user->id,
            "name" => "Join Group",
        ]);

        $this->assertCount(0, $group->joinRequests);
    }

    public function test_is_owned_by(): void
    {
        $user = $this->createUser();
        $group = ChatGroup::query()->create([
            "owner_id" => $user->id,
            "name" => "Owned",
        ]);

        $this->assertTrue($group->isOwnedBy($user));

        $other = $this->createUser();
        $this->assertFalse($group->isOwnedBy($other));
    }
}