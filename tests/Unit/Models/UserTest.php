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
        $this->seed(\Database\Seeders\RoleSeeder::class);
    }

    public function test_name_validation_rules_required(): void
    {
        $rules = User::nameValidationRules(true);

        $this->assertContains('required', $rules);
        $this->assertContains('string', $rules);
        $this->assertContains('max:120', $rules);
    }

    public function test_name_validation_rules_nullable(): void
    {
        $rules = User::nameValidationRules(false);

        $this->assertContains('nullable', $rules);
        $this->assertNotContains('required', $rules);
    }

    public function test_build_name_from_parts(): void
    {
        $user = User::factory()->make([
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);

        $this->assertEquals('John Doe', $user->buildNameFromParts());
    }

    public function test_build_name_handles_empty_parts(): void
    {
        $user = User::factory()->make([
            'first_name' => 'John',
            'last_name' => null,
        ]);

        $this->assertEquals('John', $user->buildNameFromParts());
    }

    public function test_is_full_name_taken_detects_duplicates(): void
    {
        User::factory()->create([
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);

        $this->assertTrue(User::isFullNameTaken('John', 'Doe'));
        $this->assertTrue(User::isFullNameTaken('john', 'doe')); // Case insensitive
        $this->assertFalse(User::isFullNameTaken('John', 'Smith'));
        $this->assertFalse(User::isFullNameTaken('Jane', 'Doe'));
    }

    public function test_is_full_name_taken_returns_false_for_empty(): void
    {
        $this->assertFalse(User::isFullNameTaken('', 'Doe'));
        $this->assertFalse(User::isFullNameTaken('John', ''));
        $this->assertFalse(User::isFullNameTaken('', ''));
    }

    public function test_user_gets_default_role_on_creation(): void
    {
        $user = User::create([
            'first_name' => 'New',
            'last_name' => 'User',
            'name' => 'New User',
            'email' => 'new@example.com',
            'phone' => '+998901234567',
            'password' => 'hashed_password',
        ]);

        $this->assertNotNull($user->role_id);
    }

    public function test_is_active_returns_boolean(): void
    {
        $activeUser = User::factory()->create(['is_active' => true]);
        $inactiveUser = User::factory()->create(['is_active' => false]);

        $this->assertTrue($activeUser->isActive());
        $this->assertFalse($inactiveUser->isActive());
    }

    public function test_scope_active_filters_inactive_users(): void
    {
        User::factory()->create(['is_active' => true]);
        User::factory()->create(['is_active' => true]);
        User::factory()->create(['is_active' => false]);

        $activeUsers = User::active()->count();

        $this->assertEquals(2, $activeUsers);
    }

    public function test_password_is_hashed_automatically(): void
    {
        $user = User::factory()->create([
            'password' => 'plaintext',
        ]);

        $this->assertNotEquals('plaintext', $user->password);
    }

    public function test_user_relationships_exist(): void
    {
        $user = User::factory()->create();

        // These should not throw exceptions
        $this->assertNotNull($user->comments());
        $this->assertNotNull($user->likes());
        $this->assertNotNull($user->teacherLikes());
        $this->assertNotNull($user->teacherProfile());
        $this->assertNotNull($user->createdCourses());
        $this->assertNotNull($user->courseEnrollments());
    }

    public function test_hidden_attributes_are_not_visible(): void
    {
        $user = User::factory()->create();
        $array = $user->toArray();

        $this->assertArrayNotHasKey('password', $array);
        $this->assertArrayNotHasKey('remember_token', $array);
    }
}
