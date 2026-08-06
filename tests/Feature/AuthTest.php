<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\TelegramVerification;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AuthTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'telegram.bot_username' => 'test_bot',
        ]);

        $this->withoutMiddleware(\Illuminate\Routing\Middleware\ThrottleRequests::class);
        $this->createAuthTestTables();
    }

    protected function tearDown(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('user_activities');
        Schema::dropIfExists('telegram_verifications');
        Schema::dropIfExists('one_time_codes');
        Schema::dropIfExists('roles_user');
        Schema::dropIfExists('users');
        Schema::dropIfExists('roles');
        Schema::enableForeignKeyConstraints();

        parent::tearDown();
    }

    public function test_registration_redirects_to_telegram_verification(): void
    {
        $response = $this->post(route('register.store'), $this->registrationPayload());

        $response->assertRedirect();
        $this->assertStringContainsString('telegram-verify/', $response->getTargetUrl());

        $this->assertDatabaseMissing('users', [
            'email' => 'ali@example.com',
        ]);

        $this->assertDatabaseHas('telegram_verifications', [
            'purpose' => TelegramVerification::PURPOSE_REGISTER,
            'status' => TelegramVerification::STATUS_PENDING,
        ]);
    }

    public function test_login_without_telegram_chat_id_redirects_to_verification(): void
    {
        $user = $this->createUser([
            'phone' => '+998901234567',
            'password' => 'password123',
            'telegram_chat_id' => null,
        ]);

        $response = $this->post(route('authenticate'), [
            'phone' => '+998 90 123 45 67',
            'password' => 'password123',
        ]);

        $response->assertRedirect();
        $this->assertStringContainsString('telegram-verify/', $response->getTargetUrl());
        $this->assertGuest();

        $this->assertDatabaseHas('telegram_verifications', [
            'purpose' => TelegramVerification::PURPOSE_LOGIN,
            'status' => TelegramVerification::STATUS_PENDING,
        ]);
    }

    public function test_login_with_telegram_chat_id_authenticates_directly(): void
    {
        $user = $this->createUser([
            'phone' => '+998901234567',
            'password' => 'password123',
            'telegram_chat_id' => 123456789,
        ]);

        $response = $this->post(route('authenticate'), [
            'phone' => '+998901234567',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('home'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_login_rejects_invalid_phone(): void
    {
        $this->createUser([
            'phone' => '+998901234567',
            'password' => 'password123',
        ]);

        $this->post(route('authenticate'), [
            'phone' => '+998999999999',
            'password' => 'password123',
        ])->assertSessionHasErrors('phone');

        $this->assertGuest();
    }

    public function test_login_rejects_invalid_password(): void
    {
        $this->createUser([
            'phone' => '+998901234567',
            'password' => 'password123',
        ]);

        $this->post(route('authenticate'), [
            'phone' => '+998901234567',
            'password' => 'wrong-password',
        ])->assertSessionHasErrors('phone');

        $this->assertGuest();
    }

    public function test_registration_rejects_invalid_payload(): void
    {
        $this->post(route('register.store'), [
            'first_name' => 'Ali',
            'last_name' => 'Valiyev',
            'email' => 'not-an-email',
            'phone' => '+998901234567',
            'grade' => '5-A',
            'password' => 'short',
            'password_confirmation' => 'short',
        ])->assertSessionHasErrors(['email', 'password']);

        $this->assertGuest();
    }

    public function test_telegram_verification_status_returns_pending(): void
    {
        $verification = TelegramVerification::create([
            'token' => 'test-token-123',
            'purpose' => TelegramVerification::PURPOSE_REGISTER,
            'email' => 'test@example.com',
            'phone' => '+998901234567',
            'status' => TelegramVerification::STATUS_PENDING,
            'expires_at' => now()->addMinutes(10),
        ]);

        $response = $this->get(route('telegram.status', ['token' => $verification->token]));

        $response->assertJson(['status' => 'pending']);
    }

    public function test_telegram_verification_status_returns_expired(): void
    {
        $verification = TelegramVerification::create([
            'token' => 'test-token-expired',
            'purpose' => TelegramVerification::PURPOSE_REGISTER,
            'email' => 'test@example.com',
            'phone' => '+998901234567',
            'status' => TelegramVerification::STATUS_PENDING,
            'expires_at' => now()->subMinute(),
        ]);

        $response = $this->get(route('telegram.status', ['token' => $verification->token]));

        $response->assertJson(['status' => 'expired']);
    }

    public function test_telegram_verification_status_returns_verified(): void
    {
        $verification = TelegramVerification::create([
            'token' => 'test-token-verified',
            'purpose' => TelegramVerification::PURPOSE_REGISTER,
            'email' => 'test@example.com',
            'phone' => '+998901234567',
            'status' => TelegramVerification::STATUS_VERIFIED,
            'expires_at' => now()->addMinutes(10),
            'verified_at' => now(),
            'telegram_chat_id' => 123456789,
        ]);

        $response = $this->get(route('telegram.status', ['token' => $verification->token]));

        $response->assertJson(['status' => 'verified']);
    }

    private function createAuthTestTables(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('user_activities');
        Schema::dropIfExists('telegram_verifications');
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
            $table->bigInteger('telegram_chat_id')->nullable();
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

        Schema::create('telegram_verifications', static function (Blueprint $table): void {
            $table->id();
            $table->string('token', 40)->unique();
            $table->string('purpose');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('email')->index();
            $table->string('phone')->index();
            $table->json('session_payload')->nullable();
            $table->bigInteger('telegram_chat_id')->nullable();
            $table->string('status')->default('pending');
            $table->timestamp('expires_at')->index();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
        });

        Schema::create('user_activities', static function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('type')->index();
            $table->text('description')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->string('device_type')->nullable();
            $table->timestamp('occurred_at')->nullable();
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
