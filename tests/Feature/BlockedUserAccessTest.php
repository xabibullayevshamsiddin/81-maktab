<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BlockedUserAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_blocked_regular_user_can_still_open_profile(): void
    {
        $user = $this->createUser(User::ROLE_USER, [
            'is_active' => false,
        ]);

        $this->actingAs($user)
            ->get(route('profile.show'))
            ->assertOk();
    }

    public function test_blocked_regular_user_can_open_exam_area(): void
    {
        $user = $this->createUser(User::ROLE_USER, [
            'is_active' => false,
        ]);

        $this->actingAs($user)
            ->from(route('home'))
            ->get(route('exam.index'))
            ->assertOk();
    }

    public function test_blocked_admin_cannot_open_admin_management_pages(): void
    {
        $admin = $this->createUser(User::ROLE_ADMIN, [
            'is_active' => false,
        ]);

        $this->actingAs($admin)
            ->from(route('home'))
            ->get(route('teachers.create'))
            ->assertRedirect(route('home'))
            ->assertSessionHas('error');
    }

    public function test_blocked_teacher_cannot_open_course_management_page(): void
    {
        $teacher = $this->createUser(User::ROLE_TEACHER, [
            'is_active' => false,
        ]);

        $this->actingAs($teacher)
            ->from(route('home'))
            ->get(route('teacher.courses.create'))
            ->assertRedirect(route('home'))
            ->assertSessionHas('error');
    }

    private function createUser(string $roleName, array $overrides = []): User
    {
        $role = Role::query()->firstOrCreate(
            ['name' => $roleName],
            [
                'label' => User::ROLES[$roleName] ?? $roleName,
                'level' => User::ROLE_HIERARCHY[$roleName] ?? 1,
                'is_system' => true,
            ]
        );

        $baseName = ucfirst(str_replace('_', ' ', $roleName));

        return User::query()->create(array_merge([
            'name' => $baseName.' User',
            'first_name' => $baseName,
            'last_name' => 'User',
            'email' => $roleName.'-'.uniqid().'@example.com',
            'phone' => '+998901234567',
            'grade' => $roleName === User::ROLE_USER ? '5-A' : null,
            'password' => 'password123',
            'role_id' => $role->id,
            'is_active' => true,
            'is_parent' => false,
        ], $overrides));
    }
}
