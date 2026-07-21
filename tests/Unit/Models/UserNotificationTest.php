<?php

namespace Tests\Unit\Models;

use App\Models\Role;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserNotificationTest extends TestCase
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

    public function test_can_be_created(): void
    {
        $user = User::query()->create([
            "name" => "U",
            "email" => "notif-" . uniqid() . "@test.com",
            "password" => bcrypt("pw"),
            "role_id" => Role::idByName(Role::NAME_USER),
            "is_active" => true,
        ]);

        $notification = UserNotification::query()->create([
            "user_id" => $user->id,
            "type" => "info",
            "title" => "Test Notification",
            "body" => "This is a test notification.",
        ]);

        $this->assertDatabaseHas("user_notifications", ["title" => "Test Notification"]);
    }

    public function test_belongs_to_user(): void
    {
        $user = User::query()->create([
            "name" => "U2",
            "email" => "notif2-" . uniqid() . "@test.com",
            "password" => bcrypt("pw"),
            "role_id" => Role::idByName(Role::NAME_USER),
            "is_active" => true,
        ]);

        $notification = UserNotification::query()->create([
            "user_id" => $user->id,
            "type" => "warning",
            "title" => "Warning",
            "body" => "Warning body",
        ]);

        $this->assertInstanceOf(User::class, $notification->user);
    }

    public function test_read_at(): void
    {
        $user = User::query()->create([
            "name" => "U3",
            "email" => "notif3-" . uniqid() . "@test.com",
            "password" => bcrypt("pw"),
            "role_id" => Role::idByName(Role::NAME_USER),
            "is_active" => true,
        ]);

        $notification = UserNotification::query()->create([
            "user_id" => $user->id,
            "type" => "info",
            "title" => "Read Test",
            "body" => "Body",
        ]);

        $this->assertNull($notification->read_at);

        $notification->update(["read_at" => now()]);
        $this->assertNotNull($notification->fresh()->read_at);
    }
}