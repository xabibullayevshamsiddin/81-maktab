<?php

namespace Tests\Unit\Models;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
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

    private function createUser(string $roleName, array $overrides = []): User
    {
        $role = Role::query()->where("name", $roleName)->first();
        return User::query()->create(array_merge([
            "name" => "Test User",
            "email" => $roleName . "-" . uniqid() . "@example.com",
            "password" => bcrypt("password"),
            "role_id" => $role->id,
            "is_active" => true,
            "grade" => "5-A",
        ], $overrides));
    }

    public function test_has_default_role(): void
    {
        $user = $this->createUser(User::ROLE_USER);
        $this->assertTrue($user->hasRole(User::ROLE_USER));
        $this->assertFalse($user->isAdmin());
        $this->assertFalse($user->isSuperAdmin());
        $this->assertFalse($user->isTeacher());
    }

    public function test_super_admin_detection(): void
    {
        $user = $this->createUser(User::ROLE_SUPER_ADMIN, ["email" => "sa@b.com"]);
        $this->assertTrue($user->isSuperAdmin());
        $this->assertTrue($user->isAdmin());
    }

    public function test_admin_detection(): void
    {
        $user = $this->createUser(User::ROLE_ADMIN, ["email" => "ad@b.com"]);
        $this->assertTrue($user->isAdmin());
        $this->assertFalse($user->isSuperAdmin());
    }

    public function test_teacher_detection(): void
    {
        $user = $this->createUser(User::ROLE_TEACHER, ["email" => "t@b.com"]);
        $this->assertTrue($user->isTeacher());
        $this->assertFalse($user->isAdmin());
    }

    public function test_inactive_user(): void
    {
        $user = $this->createUser(User::ROLE_USER, ["is_active" => false, "email" => "in@b.com"]);
        $this->assertFalse($user->isActive());
    }

    public function test_parent_flag(): void
    {
        $user = $this->createUser(User::ROLE_USER, ["is_parent" => true, "email" => "p@b.com"]);
        $this->assertTrue($user->is_parent);
    }

    public function test_grade_selection(): void
    {
        $user = $this->createUser(User::ROLE_USER, [
            "grade_needs_selection" => true,
            "email" => "g@b.com",
        ]);
        $this->assertTrue($user->needsGradeSelection());
    }

    public function test_build_name(): void
    {
        $user = new User(["first_name" => "Ali", "last_name" => "Valiyev"]);
        $this->assertSame("Ali Valiyev", $user->buildNameFromParts());
    }

    public function test_localized_roles_uz(): void
    {
        app()->setLocale("uz");
        $user = $this->createUser(User::ROLE_TEACHER);
        $this->assertStringContainsString("qituvchi", $user->localizedRoleLabel());
    }

    public function test_localized_roles_ru(): void
    {
        app()->setLocale("ru");
        $user = $this->createUser(User::ROLE_ADMIN);
        $this->assertStringContainsString("Админ", $user->localizedRoleLabel());
    }

    public function test_localized_roles_en(): void
    {
        app()->setLocale("en");
        $user = $this->createUser(User::ROLE_USER);
        $this->assertStringContainsString("User", $user->localizedRoleLabel());
    }

    public function test_full_name_taken(): void
    {
        $this->createUser(User::ROLE_USER, [
            "first_name" => "Ali", "last_name" => "Valiyev", "email" => "a@b.com",
        ]);
        $this->assertTrue(User::isFullNameTaken("Ali", "Valiyev"));
        $this->assertFalse(User::isFullNameTaken("Ali", "Aliyev"));
    }

    public function test_scope_by_role(): void
    {
        $this->createUser(User::ROLE_USER, ["email" => "u1@b.com"]);
        $this->createUser(User::ROLE_USER, ["email" => "u2@b.com"]);
        $this->createUser(User::ROLE_ADMIN, ["email" => "a1@b.com"]);
        $this->assertCount(2, User::byRole(User::ROLE_USER)->get());
        $this->assertCount(1, User::byRole(User::ROLE_ADMIN)->get());
    }

    public function test_avatar_returns_null(): void
    {
        $user = $this->createUser(User::ROLE_USER, ["avatar" => null, "email" => "av@b.com"]);
        $this->assertNull($user->avatar_url);
    }
}