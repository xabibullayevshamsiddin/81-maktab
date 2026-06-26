<?php

namespace Tests\Unit\Models;

use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_default_roles_are_defined(): void
    {
        $roles = Role::defaultRoles();
        $this->assertCount(6, $roles);
        $this->assertSame("super_admin", $roles[0]["name"]);
    }

    public function test_role_can_be_created(): void
    {
        $role = Role::query()->create([
            "name" => "test_role_" . uniqid(),
            "label" => "Test Role",
            "level" => 3,
        ]);
        $this->assertDatabaseHas("roles", ["name" => $role->name]);
        $this->assertSame(3, $role->level);
    }

    public function test_role_id_by_name_found(): void
    {
        $role = Role::query()->create(["name" => "custom_found", "label" => "Custom", "level" => 1]);
        $this->assertSame($role->id, Role::idByName("custom_found"));
    }

    public function test_role_id_by_name_not_found(): void
    {
        $this->assertNull(Role::idByName("non_existent_role_xyz"));
    }

    public function test_default_user_role_id(): void
    {
        $role = Role::query()->create([
            "name" => Role::NAME_USER,
            "label" => "User",
            "level" => 1,
        ]);
        $this->assertSame($role->id, Role::defaultUserRoleId());
    }

    public function test_role_has_users_relation(): void
    {
        $role = Role::query()->create(["name" => "rel_user", "label" => "User", "level" => 1]);
        $this->assertCount(0, $role->users);
        $this->assertCount(0, $role->usersByPivot);
    }

    public function test_system_flag(): void
    {
        $role = Role::query()->create(["name" => "sys_" . uniqid(), "label" => "S", "level" => 1, "is_system" => true]);
        $this->assertTrue($role->is_system);
    }

    public function test_non_system_flag(): void
    {
        $role = Role::query()->create(["name" => "non_sys_" . uniqid(), "label" => "N", "level" => 1, "is_system" => false]);
        $this->assertFalse($role->is_system);
    }

    public function test_level_set_via_fillable(): void
    {
        $role = Role::query()->create(["name" => "lvl_" . uniqid(), "label" => "L", "level" => 5]);
        $this->assertSame(5, $role->level);
    }
}