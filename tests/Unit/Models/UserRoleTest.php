<?php

namespace Tests\Unit\Models;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserRoleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
    }

    public function test_user_has_super_admin_role(): void
    {
        $role = Role::where('name', Role::NAME_SUPER_ADMIN)->first();
        $user = User::factory()->create(['role_id' => $role->id]);

        $this->assertTrue($user->isSuperAdmin());
        $this->assertTrue($user->isAdmin());
        $this->assertTrue($user->isEditor());
        $this->assertFalse($user->isTeacher());
    }

    public function test_user_has_admin_role(): void
    {
        $role = Role::where('name', Role::NAME_ADMIN)->first();
        $user = User::factory()->create(['role_id' => $role->id]);

        $this->assertFalse($user->isSuperAdmin());
        $this->assertTrue($user->isAdmin());
        $this->assertTrue($user->isEditor());
        $this->assertFalse($user->isTeacher());
    }

    public function test_user_has_editor_role(): void
    {
        $role = Role::where('name', Role::NAME_EDITOR)->first();
        $user = User::factory()->create(['role_id' => $role->id]);

        $this->assertFalse($user->isSuperAdmin());
        $this->assertFalse($user->isAdmin());
        $this->assertTrue($user->isEditor());
        $this->assertFalse($user->isTeacher());
    }

    public function test_user_has_moderator_role(): void
    {
        $role = Role::where('name', Role::NAME_MODERATOR)->first();
        $user = User::factory()->create(['role_id' => $role->id]);

        $this->assertFalse($user->isSuperAdmin());
        $this->assertFalse($user->isAdmin());
        $this->assertTrue($user->isModerator());
        $this->assertTrue($user->isOnlyModerator());
        $this->assertFalse($user->isTeacher());
    }

    public function test_user_has_teacher_role(): void
    {
        $role = Role::where('name', Role::NAME_TEACHER)->first();
        $user = User::factory()->create(['role_id' => $role->id]);

        $this->assertFalse($user->isSuperAdmin());
        $this->assertFalse($user->isAdmin());
        $this->assertTrue($user->isTeacher());
    }

    public function test_user_has_regular_user_role(): void
    {
        $role = Role::where('name', Role::NAME_USER)->first();
        $user = User::factory()->create(['role_id' => $role->id]);

        $this->assertFalse($user->isSuperAdmin());
        $this->assertFalse($user->isAdmin());
        $this->assertFalse($user->isEditor());
        $this->assertFalse($user->isModerator());
        $this->assertFalse($user->isTeacher());
    }

    public function test_has_role_checks_specific_role(): void
    {
        $adminRole = Role::where('name', Role::NAME_ADMIN)->first();
        $user = User::factory()->create(['role_id' => $adminRole->id]);

        $this->assertTrue($user->hasRole('admin'));
        $this->assertFalse($user->hasRole('teacher'));
        $this->assertTrue($user->hasRole(['admin', 'editor']));
    }

    public function test_role_level_returns_correct_hierarchy(): void
    {
        $superAdminRole = Role::where('name', Role::NAME_SUPER_ADMIN)->first();
        $adminRole = Role::where('name', Role::NAME_ADMIN)->first();
        $userRole = Role::where('name', Role::NAME_USER)->first();

        $superAdmin = User::factory()->create(['role_id' => $superAdminRole->id]);
        $admin = User::factory()->create(['role_id' => $adminRole->id]);
        $user = User::factory()->create(['role_id' => $userRole->id]);

        $this->assertEquals(5, $superAdmin->roleLevel());
        $this->assertEquals(4, $admin->roleLevel());
        $this->assertEquals(1, $user->roleLevel());
    }

    public function test_super_admin_can_manage_content(): void
    {
        $role = Role::where('name', Role::NAME_SUPER_ADMIN)->first();
        $user = User::factory()->create(['role_id' => $role->id]);

        $this->assertTrue($user->canManageContent());
        $this->assertTrue($user->canManageInbox());
        $this->assertTrue($user->canManageEducation());
        $this->assertTrue($user->canManageSystem());
    }

    public function test_admin_can_manage_content(): void
    {
        $role = Role::where('name', Role::NAME_ADMIN)->first();
        $user = User::factory()->create(['role_id' => $role->id]);

        $this->assertTrue($user->canManageContent());
        $this->assertTrue($user->canManageInbox());
        $this->assertTrue($user->canManageEducation());
        $this->assertTrue($user->canManageSystem());
    }

    public function test_editor_can_manage_content_but_not_inbox(): void
    {
        $role = Role::where('name', Role::NAME_EDITOR)->first();
        $user = User::factory()->create(['role_id' => $role->id]);

        $this->assertTrue($user->canManageContent());
        $this->assertFalse($user->canManageInbox());
        $this->assertFalse($user->canManageSystem());
    }

    public function test_moderator_can_manage_inbox_but_not_content(): void
    {
        $role = Role::where('name', Role::NAME_MODERATOR)->first();
        $user = User::factory()->create(['role_id' => $role->id]);

        $this->assertFalse($user->canManageContent());
        $this->assertTrue($user->canManageInbox());
        $this->assertFalse($user->canManageSystem());
    }

    public function test_teacher_can_manage_education(): void
    {
        $role = Role::where('name', Role::NAME_TEACHER)->first();
        $user = User::factory()->create(['role_id' => $role->id]);

        $this->assertTrue($user->canManageEducation());
        $this->assertTrue($user->canManageExams());
        $this->assertFalse($user->canManageContent());
        $this->assertFalse($user->canManageSystem());
    }

    public function test_regular_user_cannot_manage_anything(): void
    {
        $role = Role::where('name', Role::NAME_USER)->first();
        $user = User::factory()->create(['role_id' => $role->id]);

        $this->assertFalse($user->canManageContent());
        $this->assertFalse($user->canManageInbox());
        $this->assertFalse($user->canManageEducation());
        $this->assertFalse($user->canManageSystem());
    }

    public function test_can_access_dashboard(): void
    {
        $superAdmin = User::factory()->create([
            'role_id' => Role::where('name', Role::NAME_SUPER_ADMIN)->first()->id,
        ]);
        $admin = User::factory()->create([
            'role_id' => Role::where('name', Role::NAME_ADMIN)->first()->id,
        ]);
        $editor = User::factory()->create([
            'role_id' => Role::where('name', Role::NAME_EDITOR)->first()->id,
        ]);
        $moderator = User::factory()->create([
            'role_id' => Role::where('name', Role::NAME_MODERATOR)->first()->id,
        ]);
        $user = User::factory()->create([
            'role_id' => Role::where('name', Role::NAME_USER)->first()->id,
        ]);

        $this->assertTrue($superAdmin->canAccessDashboard());
        $this->assertTrue($admin->canAccessDashboard());
        $this->assertTrue($editor->canAccessDashboard());
        $this->assertTrue($moderator->canAccessDashboard());
        $this->assertFalse($user->canAccessDashboard());
    }
}
