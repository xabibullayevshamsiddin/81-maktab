<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MiddlewareTest extends TestCase
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

    public function test_guest_redirected_to_login(): void
    {
        $response = $this->get(route("dashboard"));
        $response->assertRedirect(route("login"));
    }

    public function test_auth_user_can_access_profile(): void
    {
        $user = User::query()->create([
            "name" => "Test",
            "email" => "p-" . uniqid() . "@test.com",
            "password" => bcrypt("pw"),
            "role_id" => Role::idByName(Role::NAME_USER),
            "is_active" => true,
        ]);

        $this->actingAs($user)
            ->get(route("profile.show"))
            ->assertOk();
    }

    public function test_admin_can_access_dashboard(): void
    {
        $admin = User::query()->create([
            "name" => "Admin",
            "email" => "admind-" . uniqid() . "@test.com",
            "password" => bcrypt("pw"),
            "role_id" => Role::idByName(Role::NAME_ADMIN),
            "is_active" => true,
        ]);

        $this->actingAs($admin)
            ->get(route("dashboard"))
            ->assertOk();
    }

    public function test_regular_user_cannot_access_dashboard(): void
    {
        $user = User::query()->create([
            "name" => "Regular",
            "email" => "reg-" . uniqid() . "@test.com",
            "password" => bcrypt("pw"),
            "role_id" => Role::idByName(Role::NAME_USER),
            "is_active" => true,
        ]);

        $this->actingAs($user)
            ->get(route("dashboard"))
            ->assertForbidden();
    }

    public function test_blocked_user_redirected(): void
    {
        $user = User::query()->create([
            "name" => "Blocked",
            "email" => "blk-" . uniqid() . "@test.com",
            "password" => bcrypt("pw"),
            "role_id" => Role::idByName(Role::NAME_USER),
            "is_active" => false,
        ]);

        $this->actingAs($user)
            ->from(route("home"))
            ->get(route("courses"))
            ->assertRedirect(route("home"));
    }

    public function test_admin_can_access_admin_pages(): void
    {
        $admin = User::query()->create([
            "name" => "Admin2",
            "email" => "ad2-" . uniqid() . "@test.com",
            "password" => bcrypt("pw"),
            "role_id" => Role::idByName(Role::NAME_ADMIN),
            "is_active" => true,
        ]);

        $this->actingAs($admin)
            ->get(route("admin.settings.index"))
            ->assertOk();
    }

    public function test_editor_cannot_access_super_admin_pages(): void
    {
        $editor = User::query()->create([
            "name" => "Editor",
            "email" => "ed-" . uniqid() . "@test.com",
            "password" => bcrypt("pw"),
            "role_id" => Role::idByName(Role::NAME_EDITOR),
            "is_active" => true,
        ]);

        $this->actingAs($editor)
            ->get(route("admin.settings.index"))
            ->assertForbidden();
    }
}