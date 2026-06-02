<?php

namespace Tests\Feature\Admin;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
    }

    private function createUserWithRole(string $roleName): User
    {
        $role = Role::where('name', $roleName)->first();

        return User::factory()->create([
            'role_id' => $role->id,
            'is_active' => true,
        ]);
    }

    /**
     * Dashboard Access
     */
    public function test_guest_cannot_access_admin_dashboard(): void
    {
        $response = $this->get('/admin');

        $response->assertRedirect('/login');
    }

    public function test_regular_user_cannot_access_admin_dashboard(): void
    {
        $user = $this->createUserWithRole(Role::NAME_USER);

        $response = $this->actingAs($user)->get('/admin');

        $response->assertStatus(403);
    }

    public function test_admin_can_access_admin_dashboard(): void
    {
        $user = $this->createUserWithRole(Role::NAME_ADMIN);

        $response = $this->actingAs($user)->get('/admin');

        $response->assertStatus(200);
    }

    public function test_super_admin_can_access_admin_dashboard(): void
    {
        $user = $this->createUserWithRole(Role::NAME_SUPER_ADMIN);

        $response = $this->actingAs($user)->get('/admin');

        $response->assertStatus(200);
    }

    public function test_editor_can_access_admin_dashboard(): void
    {
        $user = $this->createUserWithRole(Role::NAME_EDITOR);

        $response = $this->actingAs($user)->get('/admin');

        $response->assertStatus(200);
    }

    public function test_moderator_can_access_admin_dashboard(): void
    {
        $user = $this->createUserWithRole(Role::NAME_MODERATOR);

        $response = $this->actingAs($user)->get('/admin');

        $response->assertStatus(200);
    }

    /**
     * Post Management
     */
    public function test_regular_user_cannot_access_posts_admin(): void
    {
        $user = $this->createUserWithRole(Role::NAME_USER);

        $response = $this->actingAs($user)->get('/admin/posts');

        $response->assertStatus(403);
    }

    public function test_editor_can_access_posts_admin(): void
    {
        $user = $this->createUserWithRole(Role::NAME_EDITOR);

        $response = $this->actingAs($user)->get('/admin/posts');

        $response->assertStatus(200);
    }

    public function test_admin_can_access_posts_admin(): void
    {
        $user = $this->createUserWithRole(Role::NAME_ADMIN);

        $response = $this->actingAs($user)->get('/admin/posts');

        $response->assertStatus(200);
    }

    /**
     * User Management
     */
    public function test_regular_user_cannot_access_user_management(): void
    {
        $user = $this->createUserWithRole(Role::NAME_USER);

        $response = $this->actingAs($user)->get('/admin/users');

        $response->assertStatus(403);
    }

    public function test_editor_cannot_access_user_management(): void
    {
        $user = $this->createUserWithRole(Role::NAME_EDITOR);

        $response = $this->actingAs($user)->get('/admin/users');

        $response->assertStatus(403);
    }

    public function test_moderator_cannot_access_user_management(): void
    {
        $user = $this->createUserWithRole(Role::NAME_MODERATOR);

        $response = $this->actingAs($user)->get('/admin/users');

        $response->assertStatus(403);
    }

    public function test_admin_can_access_user_management(): void
    {
        $user = $this->createUserWithRole(Role::NAME_ADMIN);

        $response = $this->actingAs($user)->get('/admin/users');

        $response->assertStatus(200);
    }

    /**
     * Inactive User Blocking
     */
    public function test_inactive_user_is_blocked_from_authenticated_routes(): void
    {
        $user = $this->createUserWithRole(Role::NAME_ADMIN);
        $user->update(['is_active' => false]);

        $response = $this->actingAs($user)->get('/admin');

        // Should be redirected or get 403
        $this->assertTrue(
            $response->isRedirection() || $response->getStatusCode() === 403,
            'Inactive user should be blocked from admin panel'
        );
    }

    /**
     * Teacher Routes
     */
    public function test_regular_user_cannot_access_teacher_panel(): void
    {
        $user = $this->createUserWithRole(Role::NAME_USER);

        $response = $this->actingAs($user)->get('/teacher');

        $response->assertStatus(403);
    }

    public function test_teacher_can_access_teacher_panel(): void
    {
        $user = $this->createUserWithRole(Role::NAME_TEACHER);

        $response = $this->actingAs($user)->get('/teacher');

        // Teacher panel should be accessible
        $this->assertNotEquals(403, $response->getStatusCode());
    }
}
