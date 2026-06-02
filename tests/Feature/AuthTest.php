<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
    }

    public function test_user_can_register(): void
    {
        $response = $this->post('/register', [
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'test@example.com',
            'phone' => '+998901234567',
            'grade' => '9-A',
            'password' => 'StrongPass123!@#',
            'password_confirmation' => 'StrongPass123!@#',
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
        ]);

        $this->assertAuthenticated();
    }

    public function test_user_can_login(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('StrongPass123!@#'),
            'is_active' => true,
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'StrongPass123!@#',
        ]);

        $this->assertAuthenticatedAs($user);
    }

    public function test_user_cannot_login_with_wrong_credentials(): void
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('StrongPass123!@#'),
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'WrongPassword123!',
        ]);

        $this->assertGuest();
    }

    public function test_user_can_logout(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $response = $this->post('/logout');

        $this->assertGuest();
    }

    public function test_registration_requires_valid_email(): void
    {
        $response = $this->post('/register', [
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'invalid-email',
            'phone' => '+998901234567',
            'grade' => '9-A',
            'password' => 'StrongPass123!@#',
            'password_confirmation' => 'StrongPass123!@#',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_registration_requires_password_confirmation(): void
    {
        $response = $this->post('/register', [
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'test@example.com',
            'phone' => '+998901234567',
            'grade' => '9-A',
            'password' => 'StrongPass123!@#',
            'password_confirmation' => 'DifferentPass123!@#',
        ]);

        $response->assertSessionHasErrors('password');
    }

    public function test_weak_password_is_rejected(): void
    {
        $response = $this->post('/register', [
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'test@example.com',
            'phone' => '+998901234567',
            'grade' => '9-A',
            'password' => 'weak123',
            'password_confirmation' => 'weak123',
        ]);

        $response->assertSessionHasErrors('password');
    }

    public function test_inactive_user_cannot_login(): void
    {
        User::factory()->create([
            'email' => 'blocked@example.com',
            'password' => bcrypt('StrongPass123!@#'),
            'is_active' => false,
        ]);

        $response = $this->post('/login', [
            'email' => 'blocked@example.com',
            'password' => 'StrongPass123!@#',
        ]);

        $this->assertGuest();
    }
}
