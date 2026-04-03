<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\OneTimeCode;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;

class AuthController extends Controller
{
    private const OTP_VERIFY_MAX_ATTEMPTS = 5;
    private const OTP_VERIFY_DECAY_SECONDS = 600;
    private const OTP_RESEND_COOLDOWN_SECONDS = 60;

    /**
     * Vaqtincha: false bo‘lsa ro‘yxatdan o‘tish email kodisiz, darhol hisob ochiladi.
     * Email OTP ni qayta yoqish uchun true qiling.
     */
    private const REGISTER_EMAIL_OTP_ENABLED = false;

    /**
     * Vaqtincha: false bo‘lsa kirish email kodisiz — faqat email + parol.
     * Kirish OTP ni qayta yoqish uchun true qiling.
     */
    private const LOGIN_EMAIL_OTP_ENABLED = false;

    public function login()
    {
        return view('login.login');
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

        if (! $user->isActive()) {
            return back()
                ->withErrors(['email' => 'Hisobingiz bloklangan. Administrator bilan bog‘laning.'])
                ->onlyInput('email');
        }

        if (! self::LOGIN_EMAIL_OTP_ENABLED) {
            Auth::login($user);
            $request->session()->regenerate();

            return redirect()->intended(route('home'))
                ->with('success', 'Tizimga muvaffaqiyatli kirdingiz.')
                ->with('toast_type', 'success');
        }

        if (! $this->canSendOtpNow($user->email, OneTimeCode::PURPOSE_LOGIN)) {
            return back()
                ->withErrors(['email' => "Kod yuborish limiti: {$this->otpResendCooldownSecondsLeft($user->email, OneTimeCode::PURPOSE_LOGIN)} soniya kuting."])
                ->onlyInput('email');
        }

        try {
            $this->issueAndSendOtp($user->email, OneTimeCode::PURPOSE_LOGIN, [
                'user_id' => $user->id,
            ]);
        } catch (\Throwable $e) {
            Log::error('OTP login send failed', [
                'email' => $user->email,
                'error' => $e->getMessage(),
            ]);
            return back()
                ->withErrors(['email' => 'Emailga kod yuborilmadi. Sozlamalarni tekshiring.'])
                ->onlyInput('email');
        }

        $request->session()->put('otp_login_email', $user->email);

        return redirect()->route('login.verify.form')
            ->with('success', 'Emailga tasdiqlash kodi yuborildi.')
            ->with('toast_type', 'success');
    }

    public function register()
    {
        return view('login.regiter');
    }

    public function registerStore(RegisterRequest $request)
    {
        $validated = $request->validated();

        if (! self::REGISTER_EMAIL_OTP_ENABLED) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'password' => $validated['password'],
            ]);
            $user->email_verified_at = now();
            $user->save();

            Auth::login($user);
            $request->session()->regenerate();

            return redirect()->route('home')
                ->with('success', 'Ro‘yxatdan o‘tish muvaffaqiyatli.')
                ->with('toast_type', 'success');
        }

        if (! $this->canSendOtpNow($validated['email'], OneTimeCode::PURPOSE_REGISTER)) {
            return back()
                ->withErrors(['email' => "Kod yuborish limiti: {$this->otpResendCooldownSecondsLeft($validated['email'], OneTimeCode::PURPOSE_REGISTER)} soniya kuting."])
                ->onlyInput('email');
        }

        try {
            $this->issueAndSendOtp($validated['email'], OneTimeCode::PURPOSE_REGISTER, [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'password' => Hash::make($validated['password']),
            ]);
        } catch (\Throwable $e) {
            Log::error('OTP register send failed', [
                'email' => $validated['email'],
                'error' => $e->getMessage(),
            ]);
            return back()
                ->withErrors(['email' => 'Emailga kod yuborilmadi. Sozlamalarni tekshiring.'])
                ->onlyInput('email');
        }

        $request->session()->put('otp_register_email', $validated['email']);

        return redirect()->route('register.verify.form')
            ->with('success', 'Ro‘yxatdan o‘tish kodi emailingizga yuborildi.')
            ->with('toast_type', 'success');
    }

    public function regiter_store(RegisterRequest $request)
    {
        return $this->registerStore($request);
    }

    public function showLoginVerify(Request $request)
    {
        if (! self::LOGIN_EMAIL_OTP_ENABLED) {
            return redirect()->route('login');
        }

        $email = (string) $request->session()->get('otp_login_email', '');
        if ($email === '') {
            return redirect()->route('login');
        }

        return view('login.verify-code', [
            'mode' => 'login',
            'email' => $email,
        ]);
    }

    public function verifyLoginCode(Request $request)
    {
        if (! self::LOGIN_EMAIL_OTP_ENABLED) {
            return redirect()->route('login');
        }

        $validated = $request->validate([
            'code' => ['required', 'digits:6'],
        ]);

        $email = (string) $request->session()->get('otp_login_email', '');
        if ($email === '') {
            return redirect()->route('login');
        }

        if (RateLimiter::tooManyAttempts($this->otpVerifyLimiterKey($email, OneTimeCode::PURPOSE_LOGIN), self::OTP_VERIFY_MAX_ATTEMPTS)) {
            return back()->withErrors([
                'code' => "Juda ko'p xato urinish. {$this->otpVerifySecondsLeft($email, OneTimeCode::PURPOSE_LOGIN)} soniyadan keyin qayta urinib ko'ring.",
            ]);
        }

        $otp = OneTimeCode::query()
            ->where('email', $email)
            ->where('purpose', OneTimeCode::PURPOSE_LOGIN)
            ->latest('id')
            ->first();

        if (! $this->isValidOtp($otp, $validated['code'])) {
            RateLimiter::hit($this->otpVerifyLimiterKey($email, OneTimeCode::PURPOSE_LOGIN), self::OTP_VERIFY_DECAY_SECONDS);
            return back()->withErrors(['code' => "Kod noto'g'ri yoki muddati tugagan."]);
        }

        $userId = (int) ($otp->meta['user_id'] ?? 0);
        $user = User::query()->find($userId);
        if (! $user) {
            return redirect()->route('login')->withErrors(['email' => "Foydalanuvchi topilmadi."]);
        }

        $otp->delete();
        RateLimiter::clear($this->otpVerifyLimiterKey($email, OneTimeCode::PURPOSE_LOGIN));
        $request->session()->forget('otp_login_email');
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('home')
            ->with('success', 'Tizimga muvaffaqiyatli kirdingiz.')
            ->with('toast_type', 'success');
    }

    public function resendLoginCode(Request $request)
    {
        if (! self::LOGIN_EMAIL_OTP_ENABLED) {
            return redirect()->route('login');
        }

        $email = (string) $request->session()->get('otp_login_email', '');
        if ($email === '') {
            return redirect()->route('login');
        }

        if (! $this->canSendOtpNow($email, OneTimeCode::PURPOSE_LOGIN)) {
            return back()->withErrors([
                'code' => "Qayta yuborishdan oldin {$this->otpResendCooldownSecondsLeft($email, OneTimeCode::PURPOSE_LOGIN)} soniya kuting.",
            ]);
        }

        $latest = OneTimeCode::query()
            ->where('email', $email)
            ->where('purpose', OneTimeCode::PURPOSE_LOGIN)
            ->latest('id')
            ->first();

        $meta = $latest?->meta ?? [];
        try {
            $this->issueAndSendOtp($email, OneTimeCode::PURPOSE_LOGIN, $meta);
        } catch (\Throwable $e) {
            Log::error('OTP login resend failed', [
                'email' => $email,
                'error' => $e->getMessage(),
            ]);
            return back()->withErrors(['code' => 'Kodni qayta yuborib bo‘lmadi.']);
        }

        return back()
            ->with('success', 'Yangi kod yuborildi.')
            ->with('toast_type', 'warning');
    }

    public function showRegisterVerify(Request $request)
    {
        if (! self::REGISTER_EMAIL_OTP_ENABLED) {
            return redirect()->route('register');
        }

        $email = (string) $request->session()->get('otp_register_email', '');
        if ($email === '') {
            return redirect()->route('register');
        }

        return view('login.verify-code', [
            'mode' => 'register',
            'email' => $email,
        ]);
    }

    public function verifyRegisterCode(Request $request)
    {
        if (! self::REGISTER_EMAIL_OTP_ENABLED) {
            return redirect()->route('register');
        }

        $validated = $request->validate([
            'code' => ['required', 'digits:6'],
        ]);

        $email = (string) $request->session()->get('otp_register_email', '');
        if ($email === '') {
            return redirect()->route('register');
        }

        if (RateLimiter::tooManyAttempts($this->otpVerifyLimiterKey($email, OneTimeCode::PURPOSE_REGISTER), self::OTP_VERIFY_MAX_ATTEMPTS)) {
            return back()->withErrors([
                'code' => "Juda ko'p xato urinish. {$this->otpVerifySecondsLeft($email, OneTimeCode::PURPOSE_REGISTER)} soniyadan keyin qayta urinib ko'ring.",
            ]);
        }

        $otp = OneTimeCode::query()
            ->where('email', $email)
            ->where('purpose', OneTimeCode::PURPOSE_REGISTER)
            ->latest('id')
            ->first();

        if (! $this->isValidOtp($otp, $validated['code'])) {
            RateLimiter::hit($this->otpVerifyLimiterKey($email, OneTimeCode::PURPOSE_REGISTER), self::OTP_VERIFY_DECAY_SECONDS);
            return back()->withErrors(['code' => "Kod noto'g'ri yoki muddati tugagan."]);
        }

        $meta = $otp->meta ?? [];
        if (empty($meta['email']) || empty($meta['password']) || empty($meta['name']) || empty($meta['phone'])) {
            return redirect()->route('register')->withErrors(['email' => "Ro'yxatdan o'tish ma'lumotlari topilmadi."]);
        }

        if (User::query()->where('email', $meta['email'])->exists()) {
            return redirect()->route('login')
                ->with('success', "Bu email bilan hisob allaqachon mavjud. Tizimga kiring.")
                ->with('toast_type', 'warning');
        }

        $user = User::create([
            'name' => $meta['name'],
            'email' => $meta['email'],
            'phone' => $meta['phone'],
            'password' => $meta['password'], // already hashed in registerStore
        ]);

        $otp->delete();
        RateLimiter::clear($this->otpVerifyLimiterKey($email, OneTimeCode::PURPOSE_REGISTER));
        $request->session()->forget('otp_register_email');
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('home')
            ->with('success', 'Ro‘yxatdan o‘tish muvaffaqiyatli yakunlandi.')
            ->with('toast_type', 'success');
    }

    public function resendRegisterCode(Request $request)
    {
        if (! self::REGISTER_EMAIL_OTP_ENABLED) {
            return redirect()->route('register');
        }

        $email = (string) $request->session()->get('otp_register_email', '');
        if ($email === '') {
            return redirect()->route('register');
        }

        if (! $this->canSendOtpNow($email, OneTimeCode::PURPOSE_REGISTER)) {
            return back()->withErrors([
                'code' => "Qayta yuborishdan oldin {$this->otpResendCooldownSecondsLeft($email, OneTimeCode::PURPOSE_REGISTER)} soniya kuting.",
            ]);
        }

        $latest = OneTimeCode::query()
            ->where('email', $email)
            ->where('purpose', OneTimeCode::PURPOSE_REGISTER)
            ->latest('id')
            ->first();

        $meta = $latest?->meta ?? [];
        try {
            $this->issueAndSendOtp($email, OneTimeCode::PURPOSE_REGISTER, $meta);
        } catch (\Throwable $e) {
            Log::error('OTP register resend failed', [
                'email' => $email,
                'error' => $e->getMessage(),
            ]);
            return back()->withErrors(['code' => 'Kodni qayta yuborib bo‘lmadi.']);
        }

        return back()
            ->with('success', 'Yangi kod yuborildi.')
            ->with('toast_type', 'warning');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login')
            ->with('error', 'Siz tizimdan chiqdingiz.')
            ->with('toast_type', 'error');
    }

    private function issueAndSendOtp(string $email, string $purpose, array $meta = []): void
    {
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

        $subject = $purpose === OneTimeCode::PURPOSE_LOGIN
            ? 'Kirish uchun tasdiqlash kodi'
            : "Ro'yxatdan o'tish kodi";

        $title = $purpose === OneTimeCode::PURPOSE_LOGIN
            ? 'Kirishni tasdiqlang'
            : "Ro'yxatdan o'tishni tasdiqlang";

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
                    Assalomu alaykum. Quyidagi 6 xonali kodni saytdagi tasdiqlash oynasiga kiriting.
                  </p>
                  <div style="text-align:center;margin:18px 0 16px;">
                    <span style="display:inline-block;letter-spacing:6px;font-weight:700;font-size:30px;padding:12px 18px;border-radius:10px;background:#eef2ff;color:#1d4ed8;">'.$code.'</span>
                  </div>
                  <p style="margin:0;color:#dc2626;font-size:13px;font-weight:600;">Kod 10 daqiqa amal qiladi.</p>
                  <p style="margin:14px 0 0;color:#6b7280;font-size:12px;line-height:1.6;">
                    Agar bu amalni siz bajarmagan bo\'lsangiz, ushbu xabarni e\'tiborsiz qoldiring.
                  </p>
                </div>
              </div>
            </div>
        ';

        Mail::html((string) $html, static function ($message) use ($email, $subject) {
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
}
