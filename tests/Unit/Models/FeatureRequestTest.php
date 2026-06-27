<?php

namespace Tests\Unit\Models;

use App\Models\FeatureRequest;
use App\Models\FeatureRequestVote;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FeatureRequestTest extends TestCase
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
            "email" => "fr-" . uniqid() . "@example.com",
            "password" => bcrypt("password"),
            "role_id" => Role::idByName(Role::NAME_USER),
            "is_active" => true,
        ]);
    }

    public function test_can_be_created_with_pending_status(): void
    {
        $user = $this->createUser();
        $fr = FeatureRequest::query()->create([
            "user_id" => $user->id,
            "title" => "New feature",
            "description" => "I want this feature",
            "status" => FeatureRequest::STATUS_PENDING,
        ]);

        $this->assertDatabaseHas("feature_requests", ["title" => "New feature"]);
        $this->assertSame(FeatureRequest::STATUS_PENDING, $fr->status);
    }

    public function test_has_votes(): void
    {
        $user = $this->createUser();
        $fr = FeatureRequest::query()->create([
            "user_id" => $user->id,
            "title" => "Vote test",
            "description" => "Test",
            "status" => FeatureRequest::STATUS_PENDING,
        ]);

        FeatureRequestVote::query()->create([
            "feature_request_id" => $fr->id,
            "user_id" => $user->id,
        ]);

        $this->assertCount(1, $fr->fresh()->votes);
    }

    public function test_has_replies(): void
    {
        $user = $this->createUser();
        $fr = FeatureRequest::query()->create([
            "user_id" => $user->id,
            "title" => "Reply test",
            "description" => "Test",
            "status" => FeatureRequest::STATUS_PENDING,
        ]);

        $this->assertCount(0, $fr->replies);
    }

    public function test_status_moderation(): void
    {
        $user = $this->createUser();
        $fr = FeatureRequest::query()->create([
            "user_id" => $user->id,
            "title" => "Status test",
            "description" => "Test",
            "status" => FeatureRequest::STATUS_PENDING,
        ]);

        $fr->update(["status" => FeatureRequest::STATUS_PLANNED]);
        $this->assertSame(FeatureRequest::STATUS_PLANNED, $fr->fresh()->status);

        $fr->update(["status" => FeatureRequest::STATUS_DONE]);
        $this->assertSame(FeatureRequest::STATUS_DONE, $fr->fresh()->status);
    }

    public function test_belongs_to_user(): void
    {
        $user = $this->createUser();
        $fr = FeatureRequest::query()->create([
            "user_id" => $user->id,
            "title" => "Owner test",
            "description" => "Test",
            "status" => FeatureRequest::STATUS_PENDING,
        ]);

        $this->assertInstanceOf(User::class, $fr->user);
    }

    public function test_is_active_flag(): void
    {
        $user = $this->createUser();
        $fr = FeatureRequest::query()->create([
            "user_id" => $user->id,
            "title" => "Active test",
            "description" => "Test",
            "is_active" => false,
            "status" => FeatureRequest::STATUS_PENDING,
        ]);

        $this->assertFalse($fr->is_active);

        $fr->update(["is_active" => true]);
        $this->assertTrue($fr->fresh()->is_active);
    }
}