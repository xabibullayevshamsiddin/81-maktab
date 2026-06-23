<?php

namespace App\Services;

use App\Models\OneTimeCode;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;

/**
 * OTP (One-Time Password) Service
 * 
 * Centralized service for handling OTP generation, sending, verification, and rate limiting.
 * Eliminates code duplication across AuthController and ProfileController.
 */
class OtpService
{
    /**
     * Configuration constants
     */
    public const OTP_VERIFY_MAX_ATTEMPTS = 5;
    public const OTP_VERIFY_DECAY_SECONDS = 600; // 10 minutes
    public const OTP_RESEND_COOLDOWN_SECONDS = 60; // 1 minute
    public const OTP_LENGTH = 6;
    public const OTP_EXPIRY_MINUTES = 10;

    /**
     * Rate limiter key prefixes
     */
    private const RATE_LIMIT_VERIFY_PREFIX = 'otp:verify:';
    private const RATE_LIMIT_RESEND_PREFIX = 'otp:resend:';

    /**
     * Generate a random OTP code.
     */
    public function generateCode(): string
    {
        return str_pad((string) random_int(0, 999999), self::OTP_LENGTH, '0', STR_PAD_LEFT);
    }

    /**
     * Create and send OTP to email.
     *
     * @param string $email
     * @param string $purpose OneTimeCode::PURPOSE_* constant
     * @param string|null $mailableClass Mailable class to use for email
     * @return OneTimeCode
     * @throws \Exception
     */
    public function sendOtp(string $email, string $purpose, ?string $mailableClass = null): OneTimeCode
    {
        $email = $this->normalizeEmail($email);

        // Check resend cooldown
        if ($this->isResendCooldownActive($email, $purpose)) {
            $secondsLeft = $this->getResendSecondsLeft($email, $purpose);
            throw new \Exception("Iltimos, {$secondsLeft} soniyadan keyin qayta urinib ko'ring.");
        }

        // Generate code
        $code = $this->generateCode();
        $expiresAt = now()->addMinutes(self::OTP_EXPIRY_MINUTES);

        // Save to database
        $otp = OneTimeCode::create([
            'email' => $email,
            'code' => $code,
            'purpose' => $purpose,
            'expires_at' => $expiresAt,
        ]);

        // Send email
        if ($mailableClass && class_exists($mailableClass)) {
            Mail::to($email)->send(new $mailableClass($code));
        }

        // Set resend cooldown
        $this->setResendCooldown($email, $purpose);

        return $otp;
    }

    /**
     * Verify OTP code.
     *
     * @param string $email
     * @param string $code
     * @param string $purpose
     * @return bool True if valid, false if invalid
     * @throws \Exception If too many attempts
     */
    public function verifyOtp(string $email, string $code, string $purpose): bool
    {
        $email = $this->normalizeEmail($email);

        // Check rate limiting
        if ($this->isTooManyVerifyAttempts($email, $purpose)) {
            $secondsLeft = $this->getVerifySecondsLeft($email, $purpose);
            throw new \Exception("Juda ko'p xato urinish. {$secondsLeft} soniyadan keyin qayta urinib ko'ring.");
        }

        // Get latest OTP
        $otp = OneTimeCode::query()
            ->where('email', $email)
            ->where('purpose', $purpose)
            ->latest('id')
            ->first();

        // Check if valid
        if (! $this->isValidOtp($otp, $code)) {
            $this->recordVerifyAttempt($email, $purpose);
            return false;
        }

        // Clear rate limiter on success
        $this->clearVerifyAttempts($email, $purpose);
        
        return true;
    }

    /**
     * Check if OTP is valid (exists, not expired, code matches).
     */
    public function isValidOtp(?OneTimeCode $otp, string $code): bool
    {
        if (! $otp) {
            return false;
        }

        if ($otp->expires_at && $otp->expires_at->isPast()) {
            return false;
        }

        return $otp->code === $code;
    }

    /**
     * Normalize email address.
     */
    public function normalizeEmail(string $email): string
    {
        return mb_strtolower(trim($email));
    }

    /**
     * Rate Limiting - Verify Attempts
     */
    public function isTooManyVerifyAttempts(string $email, string $purpose): bool
    {
        $key = $this->getVerifyLimiterKey($email, $purpose);
        return RateLimiter::tooManyAttempts($key, self::OTP_VERIFY_MAX_ATTEMPTS);
    }

    public function recordVerifyAttempt(string $email, string $purpose): void
    {
        $key = $this->getVerifyLimiterKey($email, $purpose);
        RateLimiter::hit($key, self::OTP_VERIFY_DECAY_SECONDS);
    }

    public function clearVerifyAttempts(string $email, string $purpose): void
    {
        $key = $this->getVerifyLimiterKey($email, $purpose);
        RateLimiter::clear($key);
    }

    public function getVerifySecondsLeft(string $email, string $purpose): int
    {
        $key = $this->getVerifyLimiterKey($email, $purpose);
        return RateLimiter::availableIn($key);
    }

    private function getVerifyLimiterKey(string $email, string $purpose): string
    {
        return self::RATE_LIMIT_VERIFY_PREFIX . $purpose . ':' . $email;
    }

    /**
     * Rate Limiting - Resend Cooldown
     */
    public function isResendCooldownActive(string $email, string $purpose): bool
    {
        $key = $this->getResendLimiterKey($email, $purpose);
        return RateLimiter::tooManyAttempts($key, 0); // 0 = always limited until decay
    }

    public function setResendCooldown(string $email, string $purpose): void
    {
        $key = $this->getResendLimiterKey($email, $purpose);
        RateLimiter::hit($key, self::OTP_RESEND_COOLDOWN_SECONDS);
    }

    public function getResendSecondsLeft(string $email, string $purpose): int
    {
        $key = $this->getResendLimiterKey($email, $purpose);
        return RateLimiter::availableIn($key);
    }

    private function getResendLimiterKey(string $email, string $purpose): string
    {
        return self::RATE_LIMIT_RESEND_PREFIX . $purpose . ':' . $email;
    }

    /**
     * Delete all OTPs for a specific email and purpose.
     */
    public function deleteOtps(string $email, string $purpose): void
    {
        $email = $this->normalizeEmail($email);
        
        OneTimeCode::query()
            ->where('email', $email)
            ->where('purpose', $purpose)
            ->delete();
    }

    /**
     * Clean up expired OTPs (for scheduled cleanup).
     */
    public function cleanupExpiredOtps(): int
    {
        return OneTimeCode::query()
            ->where('expires_at', '<', now())
            ->delete();
    }
}
