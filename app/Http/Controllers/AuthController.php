<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\OneTimeCode;
use App\Models\TelegramVerification;
use App\Models\User;
use App\Services\TelegramService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    private const OTP_VERIFY_MAX_ATTEMPTS = 5;

    private const OTP_VERIFY_DECAY_SECONDS = 600;

    private const OTP_RESEND_COOLDOWN_SECONDS = 60;

    public function login()
    {
        return view('login.login');
    }

    public function showForgotPassword(Request $request)
    {
        return view('login.forgot-password', [
            'email' => $this->normalizeEmail((string) $request->query('email', '')),
        ]);
    }

    public function authenticate(LoginRequest $request)
    {
        $credentials = $request->validated();

        $user = User::query()->where('email', $credentials['email'])->first();
        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            return back()
                ->withErrors([
                    'email' => "Email yoki parol noto'g'ri.",
                ])
                ->onlyInput('email');
        }

        // Agar foydalanuvchida telegram_chat_id bor va tasdiqlangan bo'lsa
        // — to'g'ridan-to'g'ri kirishga ruxsat berish
        if ($user->telegram_chat_id) {
            Auth::login($user, true);
            $request->session()->regenerate();

            return redirect()->intended(route('home'))
                ->with('success', 'Tizimga muvaffaqiyatli kirdingiz.')
                ->with('toast_type', 'success');
        }

        // Telegram_chat_id yo'q — Telegram orqali tasdiqlash kerak
        $token = $this->createTelegramVerification(
            TelegramVerification::PURPOSE_LOGIN,
            $user->email,
            $user->phone ?? '',
            ['user_id' => $user->id]
        );

        return redirect()->route('telegram.verify', ['token' => $token])
            ->with('success', 'Telegram orqali tasdiqlang.')
            ->with('toast_type', 'success');
    }

    public function register()
    {
        return view('login.register');
    }

    public function registerStore(RegisterRequest $request)
    {
        $validated = $request->validated();
        $validated['phone'] = uz_phone_format($validated['phone']);

        $fullName = trim(($validated['first_name'] ?? '').' '.($validated['last_name'] ?? ''));
        $isParent = ! empty($validated['is_parent']);

        // Telegram orqali tasdiqlash — foydalanuvchini hali yaratmaymiz
        $token = $this->createTelegramVerification(
            TelegramVerification::PURPOSE_REGISTER,
            $validated['email'],
            $validated['phone'],
            [
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'name' => $fullName,
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'grade' => $isParent ? null : ($validated['grade'] ?? null),
                'is_parent' => $isParent,
                'password' => Hash::make($validated['password']),
            ]
        );

        return redirect()->route('telegram.verify', ['token' => $token])
            ->with('success', 'Telegram orqali tasdiqlang.')
            ->with('toast_type', 'success');
    }

    /**
     | Telegram tasdiqlash sahifasini ko'rsatish (login va register uchun umumiy).
     */
    public function showTelegramVerify(Request $request, string $token)
    {
        $verification = TelegramVerification::query()
            ->where('token', $token)
            ->first();

        if (! $verification) {
            return redirect()->route('login')
                ->withErrors(['email' => 'Tasdiqlash havolasi topilmadi.'])
                ->with('toast_type', 'error');
        }

        if ($verification->isExpired() && $verification->status === TelegramVerification::STATUS_PENDING) {
            $verification->update(['status' => TelegramVerification::STATUS_EXPIRED]);

            return redirect()->route($verification->purpose === 'register' ? 'register' : 'login')
                ->withErrors(['email' => 'Tasdiqlash havolasi muddati tugagan. Qayta urinib ko\'ring.'])
                ->with('toast_type', 'error');
        }

        if ($verification->isVerified()) {
            // Tasdiqlangan — kerakli oqimga yo'naltirish
            return $this->completeTelegramVerification($verification);
        }

        return view('login.verify-code', [
            'mode' => $verification->purpose,
            'token' => $token,
            'bot_username' => config('telegram.bot_username', ''),
        ]);
    }

    /**
     | Telegram tasdiqlanganidan keyin oqimni yakunlash.
     */
    private function completeTelegramVerification(TelegramVerification $verification)
    {
        switch ($verification->purpose) {
            case TelegramVerification::PURPOSE_REGISTER:
                return $this->completeRegister($verification);

            case TelegramVerification::PURPOSE_LOGIN:
                return $this->completeLogin($verification);

            case TelegramVerification::PURPOSE_PASSWORD_RESET:
                // Parolni tiklash uchun redirect
                return redirect()->route('password.reset.form', ['email' => $verification->session_payload['email'] ?? '']);

            default:
                return redirect()->route('login');
        }
    }

    /**
     | Register oqimini yakunlash — foydalanuvchini yaratish.
     */
    private function completeRegister(TelegramVerification $verification)
    {
        $meta = $verification->session_payload ?? [];

        if (empty($meta['email']) || empty($meta['password']) || empty($meta['phone'])) {
            return redirect()->route('register')
                ->withErrors(['email' => "Ro'yxatdan o'tish ma'lumotlari topilmadi."]);
        }

        // Email allaqachon mavjudmi?
        if (User::query()->where('email', $meta['email'])->exists()) {
            return redirect()->route('login')
                ->with('success', 'Bu email bilan hisob allaqachon mavjud. Tizimga kiring.')
                ->with('toast_type', 'warning');
        }

        // Ism + familiya allaqachon mavjudmi?
        $metaFirst = (string) ($meta['first_name'] ?? '');
        $metaLast = (string) ($meta['last_name'] ?? '');
        if (User::isFullNameTaken($metaFirst, $metaLast)) {
            return redirect()->route('register')
                ->withErrors(['email' => 'Bu ism va familiya bilan hisob allaqachon mavjud. Ro\'yxatdan o\'tishni boshidan qayta boshlang.'])
                ->with('toast_type', 'warning');
        }

        $isParent = ! empty($meta['is_parent']);

        $user = User::create([
            'first_name' => $meta['first_name'] ?? '',
            'last_name' => $meta['last_name'] ?? '',
            'name' => $meta['name'] ?? trim(($meta['first_name'] ?? '').' '.($meta['last_name'] ?? '')),
            'email' => $meta['email'],
            'phone' => $meta['phone'],
            'grade' => $isParent ? null : ($meta['grade'] ?? null),
            'is_parent' => $isParent,
            'password' => $meta['password'],
            'telegram_chat_id' => $verification->telegram_chat_id,
            'email_verified_at' => now(),
        ]);

        $verification->delete();

        Auth::login($user, true);
        session()->regenerate();

        return redirect()->route('home')
            ->with('success', 'Ro\'yxatdan o\'tish muvaffaqiyatli yakunlandi.')
            ->with('toast_type', 'success');
    }

    /**
     | Login oqimini yakunlash.
     */
    private function completeLogin(TelegramVerification $verification)
    {
        $meta = $verification->session_payload ?? [];
        $userId = (int) ($meta['user_id'] ?? 0);

        $user = User::query()->find($userId);
        if (! $user) {
            return redirect()->route('login')
                ->withErrors(['email' => 'Foydalanuvchi topilmadi.']);
        }

        // Telegram chat_id ni saqlash (agar hali yo'q bo'lsa)
        if (! $user->telegram_chat_id && $verification->telegram_chat_id) {
            $user->update(['telegram_chat_id' => $verification->telegram_chat_id]);
        }

        $verification->delete();

        Auth::login($user, true);
        session()->regenerate();

        return redirect()->intended(route('home'))
            ->with('success', 'Tizimga muvaffaqiyatli kirdingiz.')
            ->with('toast_type', 'success');
    }

    /**
     | Parolni tiklash — email kiritilganda.
     */
    public function sendPasswordResetCode(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email:rfc', 'max:255'],
        ]);

        $email = $this->normalizeEmail($validated['email']);
        $user = User::query()->where('email', $email)->first();

        if (! $user) {
            return back()
                ->withErrors(['email' => 'Bu email bilan hisob topilmadi.'])
                ->onlyInput('email');
        }

        // Telegram chat_id bor — Telegram orqali tasdiqlash
        if ($user->telegram_chat_id) {
            $token = $this->createTelegramVerification(
                TelegramVerification::PURPOSE_PASSWORD_RESET,
                $user->email,
                $user->phone ?? '',
                ['user_id' => $user->id, 'email' => $user->email]
            );

            // Telegram orqali xabar yuborish
            \App\Http\Controllers\TelegramWebhookController::sendPasswordResetRequest(
                (int) $user->telegram_chat_id,
                $token
            );

            return redirect()->route('password.forgot.form', ['email' => $email])
                ->with('success', 'Telegram orqali tasdiqlash xabari yuborildi.')
                ->with('toast_type', 'success');
        }

        // Telegram chat_id yo'q — email orqali davom etish (zaxira kanal)
        if (! $this->canSendOtpNow($email, OneTimeCode::PURPOSE_PASSWORD_RESET)) {
            return back()
                ->withErrors([
                    'email' => "Kod yuborishdan oldin {$this->otpResendCooldownSecondsLeft($email, OneTimeCode::PURPOSE_PASSWORD_RESET)} soniya kuting.",
                ])
                ->onlyInput('email');
        }

        try {
            $this->issuePasswordResetOtp($user);
        } catch (\Throwable $e) {
            $this->logOtpSendFailure('OTP password reset send failed', $e, [
                'email' => $email,
                'user_id' => $user->id,
            ]);

            return back()
                ->withErrors(['email' => 'Parolni tiklash kodi yuborilmadi. Keyinroq qayta urinib ko\'ring.'])
                ->onlyInput('email');
        }

        return redirect()
            ->route('password.reset.form', ['email' => $email])
            ->with('success', "Tasdiqlash kodi {$email} manziliga yuborildi.")
            ->with('toast_type', 'success');
    }

    public function showPasswordResetForm(Request $request)
    {
        $email = $this->normalizeEmail((string) $request->query('email', ''));
        if ($email === '') {
            return redirect()->route('password.forgot.form');
        }

        return view('login.reset-password', [
            'email' => $email,
        ]);
    }

    public function resetPassword(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email:rfc', 'max:255'],
            'code' => ['required', 'digits:6'],
            'password' => [
                'required', 'string', 'min:8', 'confirmed',
            ],
        ], [
            'email.required' => 'Emailni kiriting.',
            'code.required' => 'Tasdiqlash kodini kiriting.',
            'code.digits' => 'Kod 6 xonali bo\'lishi kerak.',
            'password.required' => 'Yangi parolni kiriting.',
            'password.min' => 'Yangi parol kamida 8 belgidan iborat bo\'lishi kerak.',
            'password.confirmed' => 'Yangi parol tasdiqlanmadi.',
        ]);

        $email = $this->normalizeEmail($validated['email']);

        if (RateLimiter::tooManyAttempts($this->otpVerifyLimiterKey($email, OneTimeCode::PURPOSE_PASSWORD_RESET), self::OTP_VERIFY_MAX_ATTEMPTS)) {
            return back()
                ->withErrors([
                    'code' => "Juda ko'p xato urinish. {$this->otpVerifySecondsLeft($email, OneTimeCode::PURPOSE_PASSWORD_RESET)} soniyadan keyin qayta urinib ko'ring.",
                ])
                ->withInput($request->except(['password', 'password_confirmation']));
        }

        $user = User::query()->where('email', $email)->first();
        if (! $user) {
            return back()
                ->withErrors(['email' => 'Bu email bilan hisob topilmadi.'])
                ->withInput($request->except(['password', 'password_confirmation']));
        }

        $otp = OneTimeCode::query()
            ->where('email', $email)
            ->where('purpose', OneTimeCode::PURPOSE_PASSWORD_RESET)
            ->latest('id')
            ->first();

        if (! $this->isValidOtp($otp, $validated['code'])) {
            RateLimiter::hit($this->otpVerifyLimiterKey($email, OneTimeCode::PURPOSE_PASSWORD_RESET), self::OTP_VERIFY_DECAY_SECONDS);

            return back()
                ->withErrors(['code' => "Kod noto'g'ri yoki muddati tugagan."])
                ->withInput($request->except(['password', 'password_confirmation']));
        }

        $meta = $otp->meta ?? [];
        if ((int) ($meta['user_id'] ?? 0) !== (int) $user->id) {
            return back()
                ->withErrors(['email' => 'Parolni tiklash sessiyasi yaroqsiz. Kodni qayta yuboring.'])
                ->withInput($request->except(['password', 'password_confirmation']));
        }

        $user->forceFill([
            'password' => Hash::make($validated['password']),
            'remember_token' => Str::random(60),
        ])->save();

        $otp->delete();
        RateLimiter::clear($this->otpVerifyLimiterKey($email, OneTimeCode::PURPOSE_PASSWORD_RESET));

        return redirect()
            ->route('login')
            ->with('success', 'Parol yangilandi. Endi yangi parol bilan tizimga kiring.')
            ->with('toast_type', 'success');
    }

    public function resendPasswordResetCode(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email:rfc', 'max:255'],
        ]);

        $email = $this->normalizeEmail($validated['email']);
        $user = User::query()->where('email', $email)->first();

        if (! $user) {
            return redirect()
                ->route('password.forgot.form', ['email' => $email])
                ->withErrors(['email' => 'Bu email bilan hisob topilmadi.']);
        }

        // Agar foydalanuvchida telegram_chat_id bo'lsa — Telegram orqali qayta yuborish
        if ($user->telegram_chat_id) {
            $token = $this->createTelegramVerification(
                TelegramVerification::PURPOSE_PASSWORD_RESET,
                $user->email,
                $user->phone ?? '',
                ['user_id' => $user->id, 'email' => $user->email]
            );

            \App\Http\Controllers\TelegramWebhookController::sendPasswordResetRequest(
                (int) $user->telegram_chat_id,
                $token
            );

            return back()
                ->with('success', 'Telegram orqali yangi tasdiqlash xabari yuborildi.')
                ->with('toast_type', 'success');
        }

        // Email orqali qayta yuborish
        if (! $this->canSendOtpNow($email, OneTimeCode::PURPOSE_PASSWORD_RESET)) {
            return back()->withErrors([
                'code' => "Qayta yuborishdan oldin {$this->otpResendCooldownSecondsLeft($email, OneTimeCode::PURPOSE_PASSWORD_RESET)} soniya kuting.",
            ]);
        }

        try {
            $this->issuePasswordResetOtp($user);
        } catch (\Throwable $e) {
            $this->logOtpSendFailure('OTP password reset resend failed', $e, [
                'email' => $email,
                'user_id' => $user->id,
            ]);

            return back()->withErrors(['code' => 'Kodni qayta yuborib bo\'lmadi.']);
        }

        return redirect()
            ->route('password.reset.form', ['email' => $email])
            ->with('success', 'Yangi kod yuborildi.')
            ->with('toast_type', 'warning');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('error', 'Siz tizimdan chiqdingiz.')
            ->with('toast_type', 'error');
    }

    public function adminSendPasswordReset(Request $request, User $user)
    {
        $admin = $request->user();

        if (! $admin || ! $admin->canManage($user)) {
            return redirect()
                ->route('user')
                ->with('error', 'Siz bu foydalanuvchi uchun parol reset kodini yubora olmaysiz.')
                ->with('toast_type', 'error');
        }

        // Telegram chat_id bor — Telegram orqali
        if ($user->telegram_chat_id) {
            $token = $this->createTelegramVerification(
                TelegramVerification::PURPOSE_PASSWORD_RESET,
                $user->email,
                $user->phone ?? '',
                ['user_id' => $user->id, 'email' => $user->email, 'issued_by_admin_id' => (int) $admin->id]
            );

            \App\Http\Controllers\TelegramWebhookController::sendPasswordResetRequest(
                (int) $user->telegram_chat_id,
                $token
            );

            return redirect()
                ->route('user')
                ->with('success', "{$user->name} uchun parolni tiklash xabari Telegram ga yuborildi.")
                ->with('toast_type', 'success');
        }

        // Email orqali
        if (! $this->mailDeliveryEnabled()) {
            return redirect()
                ->route('user')
                ->with('error', $this->mailDeliveryDisabledMessage())
                ->with('toast_type', 'warning');
        }

        if (! $this->canSendOtpNow((string) $user->email, OneTimeCode::PURPOSE_PASSWORD_RESET)) {
            return redirect()
                ->route('user')
                ->with('error', "Kod yuborish limiti: {$this->otpResendCooldownSecondsLeft((string) $user->email, OneTimeCode::PURPOSE_PASSWORD_RESET)} soniya kuting.")
                ->with('toast_type', 'error');
        }

        try {
            $this->issuePasswordResetOtp($user, [
                'issued_by_admin_id' => (int) $admin->id,
            ]);
        } catch (\Throwable $e) {
            $this->logOtpSendFailure('Admin password reset send failed', $e, [
                'email' => $user->email,
                'target_user_id' => $user->id,
                'admin_user_id' => $admin->id,
            ]);

            return redirect()
                ->route('user')
                ->with('error', 'Parolni tiklash kodi yuborilmadi. Mail sozlamalarini tekshiring.')
                ->with('toast_type', 'error');
        }

        return redirect()
            ->route('user')
            ->with('success', "{$user->name} uchun parolni tiklash kodi {$user->email} manziliga yuborildi.")
            ->with('toast_type', 'success');
    }

    // =========================================================================
    // Telegram verification yaratish
    // =========================================================================

    /**
     | Telegram verification yozuvi yaratish.
     */
    private function createTelegramVerification(string $purpose, string $email, string $phone, array $payload = []): string
    {
        // Oldingi pending yozuvlarni expired qilish
        TelegramVerification::query()
            ->where('email', $email)
            ->where('purpose', $purpose)
            ->where('status', TelegramVerification::STATUS_PENDING)
            ->update(['status' => TelegramVerification::STATUS_EXPIRED]);

        $token = Str::random(config('telegram.token_length', 40));

        TelegramVerification::create([
            'token' => $token,
            'purpose' => $purpose,
            'email' => $email,
            'phone' => $phone,
            'session_payload' => $payload,
            'status' => TelegramVerification::STATUS_PENDING,
            'expires_at' => now()->addMinutes(config('telegram.expires_minutes', 10)),
        ]);

        return $token;
    }

    // =========================================================================
    // Email OTP (faqat parolni tiklash uchun qoldirildi)
    // =========================================================================

    private function issueAndSendOtp(string $email, string $purpose, array $meta = []): void
    {
        if (! $this->mailDeliveryEnabled()) {
            throw new \RuntimeException('Mail delivery is disabled.');
        }

        $code = (string) random_int(100000, 999999);

        OneTimeCode::query()
            ->where('email', $email)
            ->where('purpose', $purpose)
            ->delete();

        OneTimeCode::create([
            'email' => $email,
            'purpose' => $purpose,
            'code_hash' => Hash::make($code),
            'expires_at' => now()->addMinutes(10),
            'meta' => $meta,
        ]);

        $subject = 'Parolni tiklash kodi';
        $title = 'Parolni yangilang';
        $description = 'Parolni yangilash uchun quyidagi 6 xonali kodni kiriting. Agar kodni admin yuborgan bo\'lsa ham, shu kod ishlaydi.';

        $resetUrl = route('password.reset.form', ['email' => $email]);
        $actionHtml = '
              <p style="margin:16px 0 0;text-align:center;">
                <a href="'.$resetUrl.'" style="display:inline-block;padding:10px 16px;border-radius:10px;background:#2563eb;color:#ffffff;text-decoration:none;font-weight:700;">
                  Parolni yangilash oynasini ochish
                </a>
              </p>
        ';

        $html = '
            <div style="background:#f3f6fb;padding:24px 12px;font-family:Arial,sans-serif;">
              <div style="max-width:520px;margin:0 auto;background:#ffffff;border-radius:14px;overflow:hidden;border:1px solid #e5e7eb;">
                <div style="background:linear-gradient(135deg,#0ea5e9,#2563eb);padding:18px 20px;color:#fff;">
                  <h1 style="margin:0;font-size:20px;line-height:1.3;">81-maktab</h1>
                  <p style="margin:6px 0 0;font-size:13px;opacity:.95;">Xavfsizlik tasdiqlash xabari</p>
                </div>
                <div style="padding:22px 20px;color:#111827;">
                  <h2 style="margin:0 0 10px;font-size:18px;">'.$title.'</h2>
                  <p style="margin:0 0 16px;color:#4b5563;font-size:14px;line-height:1.6;">
                    '.$description.'
                  </p>
                  <div style="text-align:center;margin:18px 0 16px;">
                    <span style="display:inline-block;letter-spacing:6px;font-weight:700;font-size:30px;padding:12px 18px;border-radius:10px;background:#eef2ff;color:#1d4ed8;">'.$code.'</span>
                  </div>
                  '.$actionHtml.'
                  <p style="margin:0;color:#dc2626;font-size:13px;font-weight:600;">Kod 10 daqiqa amal qiladi.</p>
                  <p style="margin:14px 0 0;color:#6b7280;font-size:12px;line-height:1.6;">
                    Agar bu amalni siz bajarmagan bo\'lsangiz, ushbu xabarni e\'tiborsiz qoldiring.
                  </p>
                </div>
              </div>
            </div>
        ';

        \Illuminate\Support\Facades\Mail::html((string) $html, static function ($message) use ($email, $subject) {
            $message->to($email)->subject($subject);
        });

        RateLimiter::hit($this->otpResendLimiterKey($email, $purpose), self::OTP_RESEND_COOLDOWN_SECONDS);
    }

    private function isValidOtp(?OneTimeCode $otp, string $code): bool
    {
        if (! $otp) {
            return false;
        }

        if (! $otp->expires_at || now()->greaterThan($otp->expires_at)) {
            return false;
        }

        return Hash::check($code, $otp->code_hash);
    }

    private function canSendOtpNow(string $email, string $purpose): bool
    {
        return ! RateLimiter::tooManyAttempts($this->otpResendLimiterKey($email, $purpose), 1);
    }

    private function otpResendCooldownSecondsLeft(string $email, string $purpose): int
    {
        return RateLimiter::availableIn($this->otpResendLimiterKey($email, $purpose));
    }

    private function otpVerifySecondsLeft(string $email, string $purpose): int
    {
        return RateLimiter::availableIn($this->otpVerifyLimiterKey($email, $purpose));
    }

    private function otpResendLimiterKey(string $email, string $purpose): string
    {
        return 'otp-send:'.$purpose.':'.strtolower($email);
    }

    private function otpVerifyLimiterKey(string $email, string $purpose): string
    {
        return 'otp-verify:'.$purpose.':'.strtolower($email);
    }

    private function issuePasswordResetOtp(User $user, array $extraMeta = []): void
    {
        $this->issueAndSendOtp((string) $user->email, OneTimeCode::PURPOSE_PASSWORD_RESET, array_merge([
            'user_id' => (int) $user->id,
        ], $extraMeta));
    }

    private function mailDeliveryEnabled(): bool
    {
        return (bool) config('mail.enabled', true)
            && (bool) config('mail.code_delivery_enabled', false)
            && $this->mailConfigurationReady();
    }

    private function mailDeliveryDisabledMessage(): string
    {
        return 'Email yuborish vaqtincha ishlamayapti. Keyinroq qayta urinib ko\'ring.';
    }

    private function logOtpSendFailure(string $message, \Throwable $e, array $context = []): void
    {
        Log::error($message, array_merge($context, [
            'error' => $e->getMessage(),
            'exception' => $e::class,
            'mail' => $this->mailDebugContext(),
        ]));
    }

    private function mailDebugContext(): array
    {
        $defaultMailer = (string) config('mail.default', 'smtp');
        $mailerConfig = (array) config("mail.mailers.{$defaultMailer}", []);

        return [
            'default' => $defaultMailer,
            'host' => $mailerConfig['host'] ?? null,
            'port' => $mailerConfig['port'] ?? null,
            'encryption' => $mailerConfig['encryption'] ?? null,
            'timeout' => $mailerConfig['timeout'] ?? null,
            'local_domain' => $mailerConfig['local_domain'] ?? null,
            'from_address' => config('mail.from.address'),
            'from_name' => config('mail.from.name'),
            'resend_api_key_configured' => $this->hasConfiguredResendApiKey(),
            'username_configured' => filled($mailerConfig['username'] ?? null),
            'password_configured' => filled($mailerConfig['password'] ?? null),
        ];
    }

    private function mailConfigurationReady(): bool
    {
        return match ((string) config('mail.default', 'smtp')) {
            'resend' => $this->hasConfiguredResendApiKey(),
            'smtp' => filled(config('mail.mailers.smtp.host')),
            default => true,
        };
    }

    private function hasConfiguredResendApiKey(): bool
    {
        $apiKey = trim((string) (config('resend.api_key') ?? config('services.resend.key') ?? ''));

        if ($apiKey === '' || ! str_starts_with($apiKey, 're_')) {
            return false;
        }

        $normalizedKey = strtolower($apiKey);

        return ! str_contains($normalizedKey, 'sizning_kalitingiz')
            && ! str_contains($normalizedKey, 'your_key')
            && ! str_contains($normalizedKey, 'your-api-key');
    }

    private function normalizeEmail(string $email): string
    {
        return strtolower(trim($email));
    }
}
