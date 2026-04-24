<?php

namespace Tests\Feature;

use App\Models\OneTimeCode;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AuthTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'mail.enabled' => true,
            'courses.require_email_verification' => true,
        ]);

        Mail::fake();
        $this->createAuthTestTables();
    }

    protected function tearDown(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('one_time_codes');
        Schema::dropIfExists('roles_user');
        Schema::dropIfExists('users');
        Schema::dropIfExists('roles');
        Schema::enableForeignKeyConstraints();

        parent::tearDown();
    }

    public function test_registration_starts_email_verification_instead_of_creating_user_immediately(): void
    {
        $response = $this->post(route('register.store'), $this->registrationPayload());

        $response->assertRedirect(route('register.verify.form'));
        $this->assertGuest();
        $this->assertDatabaseMissing('users', [
            'email' => 'ali@example.com',
        ]);
        $this->assertDatabaseHas('one_time_codes', [
            'email' => 'ali@example.com',
            'purpose' => OneTimeCode::PURPOSE_REGISTER,
        ]);
    }

    public function test_register_verification_code_completes_registration(): void
    {
        $this->post(route('register.store'), $this->registrationPayload())
            ->assertRedirect(route('register.verify.form'));

        $otp = OneTimeCode::query()
            ->where('email', 'ali@example.com')
            ->where('purpose', OneTimeCode::PURPOSE_REGISTER)
            ->latest('id')
            ->firstOrFail();

        $otp->forceFill([
            'code_hash' => Hash::make('123456'),
        ])->save();

        $this->post(route('register.verify'), [
            'code' => '123456',
        ])->assertRedirect(route('home'));

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => 'ali@example.com',
            'first_name' => 'Ali',
            'last_name' => 'Valiyev',
            'grade' => '5-A',
            'is_parent' => false,
        ]);
    }

    public function test_login_requires_email_code_before_authenticating_user(): void
    {
        $user = $this->createUser([
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $this->post(route('authenticate'), [
            'email' => 'test@example.com',
            'password' => 'password123',
        ])->assertRedirect(route('login.verify.form'));

        $this->assertGuest();

        $otp = OneTimeCode::query()
            ->where('email', 'test@example.com')
            ->where('purpose', OneTimeCode::PURPOSE_LOGIN)
            ->latest('id')
            ->firstOrFail();

        $otp->forceFill([
            'code_hash' => Hash::make('654321'),
        ])->save();

        $this->post(route('login.verify'), [
            'code' => '654321',
        ])->assertRedirect(route('home'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_registration_completes_immediately_when_mail_delivery_is_disabled(): void
    {
        config(['mail.enabled' => false]);

        $this->post(route('register.store'), $this->registrationPayload())
            ->assertRedirect(route('home'));

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => 'ali@example.com',
        ]);
        $this->assertDatabaseMissing('one_time_codes', [
            'email' => 'ali@example.com',
            'purpose' => OneTimeCode::PURPOSE_REGISTER,
        ]);
    }

    public function test_login_completes_immediately_when_mail_delivery_is_disabled(): void
    {
        config(['mail.enabled' => false]);

        $user = $this->createUser([
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $this->post(route('authenticate'), [
            'email' => 'test@example.com',
            'password' => 'password123',
        ])->assertRedirect(route('home'));

        $this->assertAuthenticatedAs($user);
        $this->assertDatabaseMissing('one_time_codes', [
            'email' => 'test@example.com',
            'purpose' => OneTimeCode::PURPOSE_LOGIN,
        ]);
    }

    private function createAuthTestTables(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('one_time_codes');
        Schema::dropIfExists('roles_user');
        Schema::dropIfExists('users');
        Schema::dropIfExists('roles');
        Schema::enableForeignKeyConstraints();

        Schema::create('roles', static function (Blueprint $table): void {
            $table->id();
            $table->string('name')->unique();
            $table->string('label')->nullable();
            $table->unsignedTinyInteger('level')->default(1);
            $table->boolean('is_system')->default(false);
            $table->timestamps();
        });

        Schema::create('users', static function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('phone')->nullable();
            $table->string('grade')->nullable();
            $table->string('avatar')->nullable();
            $table->string('google_id')->nullable();
            $table->string('password');
            $table->unsignedBigInteger('role_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_parent')->default(false);
            $table->boolean('course_open_approved')->default(false);
            $table->boolean('course_open_request_pending')->default(false);
            $table->timestamp('course_open_requested_at')->nullable();
            $table->timestamp('course_open_approved_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('one_time_codes', static function (Blueprint $table): void {
            $table->id();
            $table->string('email')->index();
            $table->string('purpose', 40)->index();
            $table->string('code_hash');
            $table->timestamp('expires_at')->index();
            $table->json('meta')->nullable();
            $table->timestamps();
        });

        Role::query()->create([
            'name' => Role::NAME_USER,
            'label' => 'Foydalanuvchi',
            'level' => User::ROLE_HIERARCHY[User::ROLE_USER],
            'is_system' => true,
        ]);
    }

    private function registrationPayload(): array
    {
        return [
            'first_name' => 'Ali',
            'last_name' => 'Valiyev',
            'email' => 'ali@example.com',
            'phone' => '+998 90 123 45 67',
            'grade' => '5-A',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];
    }

    private function createUser(array $overrides = []): User
    {
        return User::query()->create(array_merge([
            'name' => 'Test User',
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'test@example.com',
            'phone' => '+998901234567',
            'grade' => '5-A',
            'password' => 'password123',
            'role_id' => Role::defaultUserRoleId(),
            'is_active' => true,
            'is_parent' => false,
        ], $overrides));
    }
}
