<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LogoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_logout_via_post(): void
    {
        $user = User::query()->create([
            'name' => 'Test User',
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'logout@example.com',
            'phone' => '+998901234567',
            'password' => 'password123',
            'role_id' => Role::defaultUserRoleId(),
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->post(route('logout'))
            ->assertRedirect(route('login'))
            ->assertSessionHas('error');

        $this->assertGuest();
    }

    public function test_logout_route_does_not_accept_get_requests(): void
    {
        $user = User::query()->create([
            'name' => 'Test User',
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'get-logout@example.com',
            'phone' => '+998901234567',
            'password' => 'password123',
            'role_id' => Role::defaultUserRoleId(),
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->get(route('logout'))
            ->assertNotFound();
    }
}
