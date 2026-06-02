<?php

namespace Tests\Feature\Auth;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create roles
        $this->seed(\Database\Seeders\RoleSeeder::class);
    }

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_user_can_register_with_valid_data(): void
    {
        $response = $this->post('/register', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'phone' => '+998901234567',
            'grade' => '9-A',
            'password' => 'SecurePass123!@#',
            'password_confirmation' => 'SecurePass123!@#',
        ]);

        $response->assertRedirect('/');
        
        $this->assertDatabaseHas('users', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'phone' => '+998901234567',
            'grade' => '9-A',
        ]);
        
        $this->assertAuthenticated();
    }

    public function test_password_must_be_at_least_12_characters(): void
    {
        $response = $this->post('/register', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'phone' => '+998901234567',
            'grade' => '9-A',
            'password' => 'Short1!',
            'password_confirmation' => 'Short1!',
        ]);

        $response->assertSessionHasErrors('password');
    }

    public function test_password_must_contain_uppercase_letter(): void
    {
        $response = $this->post('/register', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'phone' => '+998901234567',
            'grade' => '9-A',
            'password' => 'lowercase123!@#',
            'password_confirmation' => 'lowercase123!@#',
        ]);

        $response->assertSessionHasErrors('password');
    }

    public function test_password_must_contain_lowercase_letter(): void
    {
        $response = $this->post('/register', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'phone' => '+998901234567',
            'grade' => '9-A',
            'password' => 'UPPERCASE123!@#',
            'password_confirmation' => 'UPPERCASE123!@#',
        ]);

        $response->assertSessionHasErrors('password');
    }

    public function test_password_must_contain_number(): void
    {
        $response = $this->post('/register', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'phone' => '+998901234567',
            'grade' => '9-A',
            'password' => 'NoNumbers!@#Abc',
            'password_confirmation' => 'NoNumbers!@#Abc',
        ]);

        $response->assertSessionHasErrors('password');
    }

    public function test_password_must_contain_special_character(): void
    {
        $response = $this->post('/register', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'phone' => '+998901234567',
            'grade' => '9-A',
            'password' => 'NoSpecial123Abc',
            'password_confirmation' => 'NoSpecial123Abc',
        ]);

        $response->assertSessionHasErrors('password');
    }

    public function test_password_must_be_confirmed(): void
    {
        $response = $this->post('/register', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'phone' => '+998901234567',
            'grade' => '9-A',
            'password' => 'SecurePass123!@#',
            'password_confirmation' => 'DifferentPass123!@#',
        ]);

        $response->assertSessionHasErrors('password');
    }

    public function test_email_must_be_unique(): void
    {
        User::factory()->create(['email' => 'existing@example.com']);

        $response = $this->post('/register', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'existing@example.com',
            'phone' => '+998901234567',
            'grade' => '9-A',
            'password' => 'SecurePass123!@#',
            'password_confirmation' => 'SecurePass123!@#',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_parent_can_register_without_grade(): void
    {
        $response = $this->post('/register', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'parent@example.com',
            'phone' => '+998901234567',
            'is_parent' => '1',
            'password' => 'SecurePass123!@#',
            'password_confirmation' => 'SecurePass123!@#',
        ]);

        $response->assertRedirect('/');
        
        $this->assertDatabaseHas('users', [
            'email' => 'parent@example.com',
            'is_parent' => true,
            'grade' => null,
        ]);
    }

    public function test_phone_must_be_in_uzbek_format(): void
    {
        $response = $this->post('/register', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'phone' => '1234567890', // Invalid format
            'grade' => '9-A',
            'password' => 'SecurePass123!@#',
            'password_confirmation' => 'SecurePass123!@#',
        ]);

        $response->assertSessionHasErrors('phone');
    }

    public function test_grade_must_be_valid_school_grade(): void
    {
        $response = $this->post('/register', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'phone' => '+998901234567',
            'grade' => '99-Z', // Invalid grade
            'password' => 'SecurePass123!@#',
            'password_confirmation' => 'SecurePass123!@#',
        ]);

        $response->assertSessionHasErrors('grade');
    }

    public function test_user_is_assigned_default_role_after_registration(): void
    {
        $this->post('/register', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'phone' => '+998901234567',
            'grade' => '9-A',
            'password' => 'SecurePass123!@#',
            'password_confirmation' => 'SecurePass123!@#',
        ]);

        $user = User::where('email', 'john@example.com')->first();
        
        $this->assertNotNull($user->role_id);
        $this->assertEquals(Role::NAME_USER, $user->roleRelation->name);
    }

    public function test_duplicate_full_name_is_rejected(): void
    {
        User::factory()->create([
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);

        $response = $this->post('/register', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'different@example.com',
            'phone' => '+998901234568',
            'grade' => '9-A',
            'password' => 'SecurePass123!@#',
            'password_confirmation' => 'SecurePass123!@#',
        ]);

        $response->assertSessionHasErrors('last_name');
    }
}
