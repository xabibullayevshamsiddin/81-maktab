<?php

namespace Tests\Unit\Services;

use App\Models\OneTimeCode;
use App\Services\OtpService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class OtpServiceTest extends TestCase
{
    use RefreshDatabase;

    private OtpService $otpService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->otpService = new OtpService();
        
        // Clear rate limiters
        RateLimiter::clear('otp:verify:login:test@example.com');
        RateLimiter::clear('otp:resend:login:test@example.com');
    }

    public function test_generates_6_digit_code(): void
    {
        $code = $this->otpService->generateCode();

        $this->assertIsString($code);
        $this->assertEquals(6, strlen($code));
        $this->assertMatchesRegularExpression('/^\d{6}$/', $code);
    }

    public function test_normalizes_email_to_lowercase(): void
    {
        $normalized = $this->otpService->normalizeEmail('TEST@EXAMPLE.COM');

        $this->assertEquals('test@example.com', $normalized);
    }

    public function test_is_valid_otp_returns_true_for_valid_code(): void
    {
        $otp = OneTimeCode::create([
            'email' => 'test@example.com',
            'code' => '123456',
            'purpose' => OneTimeCode::PURPOSE_LOGIN,
            'expires_at' => now()->addMinutes(10),
        ]);

        $isValid = $this->otpService->isValidOtp($otp, '123456');

        $this->assertTrue($isValid);
    }

    public function test_is_valid_otp_returns_false_for_wrong_code(): void
    {
        $otp = OneTimeCode::create([
            'email' => 'test@example.com',
            'code' => '123456',
            'purpose' => OneTimeCode::PURPOSE_LOGIN,
            'expires_at' => now()->addMinutes(10),
        ]);

        $isValid = $this->otpService->isValidOtp($otp, '654321');

        $this->assertFalse($isValid);
    }

    public function test_is_valid_otp_returns_false_for_expired_code(): void
    {
        $otp = OneTimeCode::create([
            'email' => 'test@example.com',
            'code' => '123456',
            'purpose' => OneTimeCode::PURPOSE_LOGIN,
            'expires_at' => now()->subMinutes(1), // Expired
        ]);

        $isValid = $this->otpService->isValidOtp($otp, '123456');

        $this->assertFalse($isValid);
    }

    public function test_is_valid_otp_returns_false_for_null_otp(): void
    {
        $isValid = $this->otpService->isValidOtp(null, '123456');

        $this->assertFalse($isValid);
    }

    public function test_verify_otp_returns_true_for_valid_code(): void
    {
        OneTimeCode::create([
            'email' => 'test@example.com',
            'code' => '123456',
            'purpose' => OneTimeCode::PURPOSE_LOGIN,
            'expires_at' => now()->addMinutes(10),
        ]);

        $isValid = $this->otpService->verifyOtp('test@example.com', '123456', OneTimeCode::PURPOSE_LOGIN);

        $this->assertTrue($isValid);
    }

    public function test_verify_otp_returns_false_for_invalid_code(): void
    {
        OneTimeCode::create([
            'email' => 'test@example.com',
            'code' => '123456',
            'purpose' => OneTimeCode::PURPOSE_LOGIN,
            'expires_at' => now()->addMinutes(10),
        ]);

        $isValid = $this->otpService->verifyOtp('test@example.com', '999999', OneTimeCode::PURPOSE_LOGIN);

        $this->assertFalse($isValid);
    }

    public function test_verify_otp_throws_exception_after_too_many_attempts(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Juda ko'p xato urinish");

        OneTimeCode::create([
            'email' => 'test@example.com',
            'code' => '123456',
            'purpose' => OneTimeCode::PURPOSE_LOGIN,
            'expires_at' => now()->addMinutes(10),
        ]);

        // Make 5 failed attempts
        for ($i = 0; $i < 5; $i++) {
            try {
                $this->otpService->verifyOtp('test@example.com', '999999', OneTimeCode::PURPOSE_LOGIN);
            } catch (\Exception $e) {
                // Continue to next attempt
            }
        }

        // 6th attempt should throw
        $this->otpService->verifyOtp('test@example.com', '999999', OneTimeCode::PURPOSE_LOGIN);
    }

    public function test_verify_otp_clears_rate_limiter_on_success(): void
    {
        OneTimeCode::create([
            'email' => 'test@example.com',
            'code' => '123456',
            'purpose' => OneTimeCode::PURPOSE_LOGIN,
            'expires_at' => now()->addMinutes(10),
        ]);

        // Make some failed attempts
        for ($i = 0; $i < 3; $i++) {
            $this->otpService->verifyOtp('test@example.com', '999999', OneTimeCode::PURPOSE_LOGIN);
        }

        // Successful verification
        $this->otpService->verifyOtp('test@example.com', '123456', OneTimeCode::PURPOSE_LOGIN);

        // Should not be rate limited anymore
        $isTooMany = $this->otpService->isTooManyVerifyAttempts('test@example.com', OneTimeCode::PURPOSE_LOGIN);
        
        $this->assertFalse($isTooMany);
    }

    public function test_delete_otps_removes_specific_otps(): void
    {
        OneTimeCode::create([
            'email' => 'test@example.com',
            'code' => '123456',
            'purpose' => OneTimeCode::PURPOSE_LOGIN,
            'expires_at' => now()->addMinutes(10),
        ]);

        OneTimeCode::create([
            'email' => 'test@example.com',
            'code' => '654321',
            'purpose' => OneTimeCode::PURPOSE_REGISTER,
            'expires_at' => now()->addMinutes(10),
        ]);

        $this->assertEquals(2, OneTimeCode::count());

        $this->otpService->deleteOtps('test@example.com', OneTimeCode::PURPOSE_LOGIN);

        $this->assertEquals(1, OneTimeCode::count());
        
        $remaining = OneTimeCode::first();
        $this->assertEquals(OneTimeCode::PURPOSE_REGISTER, $remaining->purpose);
    }

    public function test_cleanup_expired_otps_removes_old_codes(): void
    {
        OneTimeCode::create([
            'email' => 'test1@example.com',
            'code' => '111111',
            'purpose' => OneTimeCode::PURPOSE_LOGIN,
            'expires_at' => now()->subMinutes(10), // Expired
        ]);

        OneTimeCode::create([
            'email' => 'test2@example.com',
            'code' => '222222',
            'purpose' => OneTimeCode::PURPOSE_LOGIN,
            'expires_at' => now()->addMinutes(10), // Valid
        ]);

        $this->assertEquals(2, OneTimeCode::count());

        $deleted = $this->otpService->cleanupExpiredOtps();

        $this->assertEquals(1, $deleted);
        $this->assertEquals(1, OneTimeCode::count());
        
        $remaining = OneTimeCode::first();
        $this->assertEquals('test2@example.com', $remaining->email);
    }

    public function test_resend_cooldown_prevents_immediate_resend(): void
    {
        // Set cooldown
        $this->otpService->setResendCooldown('test@example.com', OneTimeCode::PURPOSE_LOGIN);

        $isActive = $this->otpService->isResendCooldownActive('test@example.com', OneTimeCode::PURPOSE_LOGIN);

        $this->assertTrue($isActive);
    }

    public function test_get_resend_seconds_left_returns_positive_number(): void
    {
        $this->otpService->setResendCooldown('test@example.com', OneTimeCode::PURPOSE_LOGIN);

        $secondsLeft = $this->otpService->getResendSecondsLeft('test@example.com', OneTimeCode::PURPOSE_LOGIN);

        $this->assertGreaterThan(0, $secondsLeft);
        $this->assertLessThanOrEqual(60, $secondsLeft);
    }

    public function test_get_verify_seconds_left_after_rate_limit(): void
    {
        // Hit rate limit
        for ($i = 0; $i < 5; $i++) {
            $this->otpService->recordVerifyAttempt('test@example.com', OneTimeCode::PURPOSE_LOGIN);
        }

        $secondsLeft = $this->otpService->getVerifySecondsLeft('test@example.com', OneTimeCode::PURPOSE_LOGIN);

        $this->assertGreaterThan(0, $secondsLeft);
        $this->assertLessThanOrEqual(600, $secondsLeft); // 10 minutes max
    }
}
