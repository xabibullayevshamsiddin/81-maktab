<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
        Storage::fake('public');
    }

    private function createUser(array $overrides = []): User
    {
        return User::factory()->create(array_merge([
            'role_id' => Role::where('name', Role::NAME_USER)->first()->id,
            'is_active' => true,
        ], $overrides));
    }

    /**
     * Profile View
     */
    public function test_guest_cannot_view_profile(): void
    {
        $response = $this->get('/profile');

        $response->assertRedirect('/login');
    }

    public function test_user_can_view_own_profile(): void
    {
        $user = $this->createUser();

        $response = $this->actingAs($user)->get('/profile');

        $response->assertStatus(200);
        $response->assertSee($user->email);
    }

    /**
     * Password Change
     */
    public function test_user_can_change_password(): void
    {
        $user = $this->createUser([
            'password' => Hash::make('OldPassword123!@#'),
        ]);

        // First confirm current password
        $this->actingAs($user)->post('/profile/password/confirm', [
            'current_password' => 'OldPassword123!@#',
        ]);

        $response = $this->actingAs($user)->put('/profile/password', [
            'password' => 'NewSecure123!@#$',
            'password_confirmation' => 'NewSecure123!@#$',
        ]);

        $user->refresh();
        $this->assertTrue(Hash::check('NewSecure123!@#$', $user->password));
    }

    public function test_password_change_requires_current_password_confirmation(): void
    {
        $user = $this->createUser([
            'password' => Hash::make('OldPassword123!@#'),
        ]);

        // Skip current password confirmation step
        $response = $this->actingAs($user)->put('/profile/password', [
            'password' => 'NewSecure123!@#$',
            'password_confirmation' => 'NewSecure123!@#$',
        ]);

        // Should redirect or error
        $user->refresh();
        $this->assertFalse(Hash::check('NewSecure123!@#$', $user->password));
    }

    public function test_new_password_must_meet_strength_requirements(): void
    {
        $user = $this->createUser([
            'password' => Hash::make('OldPassword123!@#'),
        ]);

        $this->actingAs($user)->post('/profile/password/confirm', [
            'current_password' => 'OldPassword123!@#',
        ]);

        $response = $this->actingAs($user)->put('/profile/password', [
            'password' => 'weak',
            'password_confirmation' => 'weak',
        ]);

        $response->assertSessionHasErrors('password');
    }

    /**
     * Profile Update
     */
    public function test_user_can_update_profile_info(): void
    {
        $user = $this->createUser([
            'first_name' => 'Old',
            'last_name' => 'Name',
        ]);

        $response = $this->actingAs($user)->put('/profile', [
            'first_name' => 'New',
            'last_name' => 'Name',
            'phone' => '+998901234567',
        ]);

        $user->refresh();
        $this->assertEquals('New', $user->first_name);
    }

    /**
     * Avatar Upload
     */
    public function test_user_can_upload_avatar(): void
    {
        $user = $this->createUser();

        $response = $this->actingAs($user)->post('/profile/avatar', [
            'avatar' => UploadedFile::fake()->image('avatar.jpg', 200, 200),
        ]);

        $user->refresh();
        $this->assertNotNull($user->avatar);
    }

    public function test_avatar_rejects_non_image_files(): void
    {
        $user = $this->createUser();

        $response = $this->actingAs($user)->post('/profile/avatar', [
            'avatar' => UploadedFile::fake()->create('document.pdf', 100),
        ]);

        $response->assertSessionHasErrors('avatar');
    }

    /**
     * Email Change
     */
    public function test_user_can_request_email_change(): void
    {
        $user = $this->createUser(['email' => 'old@example.com']);

        $response = $this->actingAs($user)->post('/profile/email/request', [
            'email' => 'new@example.com',
        ]);

        // Should send OTP and store pending email
        $this->assertEquals(
            'new@example.com',
            session()->get('profile_email_change_pending')
        );
    }

    public function test_email_change_rejects_existing_email(): void
    {
        User::factory()->create(['email' => 'existing@example.com']);
        $user = $this->createUser(['email' => 'myemail@example.com']);

        $response = $this->actingAs($user)->post('/profile/email/request', [
            'email' => 'existing@example.com',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_email_change_rejects_same_email(): void
    {
        $user = $this->createUser(['email' => 'same@example.com']);

        $response = $this->actingAs($user)->post('/profile/email/request', [
            'email' => 'same@example.com',
        ]);

        $response->assertSessionHasErrors('email');
    }
}
