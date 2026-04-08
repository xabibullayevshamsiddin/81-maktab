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
use Illuminate\Support\Str;

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

        if (! $user->isActive()) {
            return back()
                ->withErrors(['email' => 'Hisobingiz bloklangan. Administrator bilan bog‘laning.'])
                ->onlyInput('email');
        }

        if (! self::LOGIN_EMAIL_OTP_ENABLED) {
            Auth::login($user, true);
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
        $validated['phone'] = uz_phone_format($validated['phone']);

        $fullName = trim(($validated['first_name'] ?? '').' '.($validated['last_name'] ?? ''));
        $isParent = ! empty($validated['is_parent']);

        if (! self::REGISTER_EMAIL_OTP_ENABLED) {
            $user = User::create([
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'name' => $fullName,
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'grade' => $isParent ? null : $validated['grade'],
                'is_parent' => $isParent,
                'password' => $validated['password'],
            ]);
            $user->email_verified_at = now();
            $user->save();

            Auth::login($user, true);
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
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'name' => $fullName,
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'grade' => $isParent ? null : $validated['grade'],
                'is_parent' => $isParent,
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

    public function sendPasswordResetCode(Request $request)
    {
        return back()
            ->withErrors(['email' => 'Parolni email orqali tiklash vaqtincha o‘chirildi.'])
            ->onlyInput('email');

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
            Log::error('OTP password reset send failed', [
                'email' => $email,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
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
                'required',
                'string',
                'min:8',
                'confirmed',
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
        return back()->withErrors([
            'code' => 'Parolni tiklash kodini qayta yuborish vaqtincha o‘chirildi.',
        ]);

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

        if (! $this->canSendOtpNow($email, OneTimeCode::PURPOSE_PASSWORD_RESET)) {
            return back()->withErrors([
                'code' => "Qayta yuborishdan oldin {$this->otpResendCooldownSecondsLeft($email, OneTimeCode::PURPOSE_PASSWORD_RESET)} soniya kuting.",
            ]);
        }

        try {
            $this->issuePasswordResetOtp($user);
        } catch (\Throwable $e) {
            Log::error('OTP password reset resend failed', [
                'email' => $email,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors(['code' => 'Kodni qayta yuborib bo\'lmadi.']);
        }

        return redirect()
            ->route('password.reset.form', ['email' => $email])
            ->with('success', 'Yangi kod yuborildi.')
            ->with('toast_type', 'warning');
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
            return redirect()->route('login')->withErrors(['email' => 'Foydalanuvchi topilmadi.']);
        }

        $otp->delete();
        RateLimiter::clear($this->otpVerifyLimiterKey($email, OneTimeCode::PURPOSE_LOGIN));
        $request->session()->forget('otp_login_email');
        Auth::login($user, true);
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
        $metaIsParent = ! empty($meta['is_parent']);
        if (empty($meta['email']) || empty($meta['password']) || (empty($meta['name']) && empty($meta['first_name'])) || empty($meta['phone']) || (! $metaIsParent && empty($meta['grade']))) {
            return redirect()->route('register')->withErrors(['email' => "Ro'yxatdan o'tish ma'lumotlari topilmadi."]);
        }

        if (User::query()->where('email', $meta['email'])->exists()) {
            return redirect()->route('login')
                ->with('success', 'Bu email bilan hisob allaqachon mavjud. Tizimga kiring.')
                ->with('toast_type', 'warning');
        }

        $metaFirst = (string) ($meta['first_name'] ?? '');
        $metaLast = (string) ($meta['last_name'] ?? '');
        if (User::isFullNameTaken($metaFirst, $metaLast)) {
            return redirect()->route('register')
                ->withErrors(['email' => 'Bu ism va familiya bilan hisob allaqachon mavjud. Ro‘yxatdan o‘tishni boshidan qayta boshlang.'])
                ->with('toast_type', 'warning');
        }

        $user = User::create([
            'first_name' => $meta['first_name'] ?? '',
            'last_name' => $meta['last_name'] ?? '',
            'name' => $meta['name'] ?? trim(($meta['first_name'] ?? '').' '.($meta['last_name'] ?? '')),
            'email' => $meta['email'],
            'phone' => $meta['phone'],
            'grade' => $metaIsParent ? null : ($meta['grade'] ?? null),
            'is_parent' => $metaIsParent,
            'password' => $meta['password'],
        ]);

        $otp->delete();
        RateLimiter::clear($this->otpVerifyLimiterKey($email, OneTimeCode::PURPOSE_REGISTER));
        $request->session()->forget('otp_register_email');
        Auth::login($user, true);
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

    public function adminSendPasswordReset(Request $request, User $user)
    {
        return redirect()
            ->route('user')
            ->with('error', 'Parol reset kodini emailga yuborish vaqtincha o‘chirildi.')
            ->with('toast_type', 'error');

        $admin = $request->user();

        if (! $admin || ! $admin->canManage($user)) {
            return redirect()
                ->route('user')
                ->with('error', 'Siz bu foydalanuvchi uchun parol reset kodini yubora olmaysiz.')
                ->with('toast_type', 'error');
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
            Log::error('Admin password reset send failed', [
                'email' => $user->email,
                'target_user_id' => $user->id,
                'admin_user_id' => $admin->id,
                'error' => $e->getMessage(),
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

        $subject = match ($purpose) {
            OneTimeCode::PURPOSE_LOGIN => 'Kirish uchun tasdiqlash kodi',
            OneTimeCode::PURPOSE_PASSWORD_RESET => 'Parolni tiklash kodi',
            default => "Ro'yxatdan o'tish kodi",
        };

        $title = match ($purpose) {
            OneTimeCode::PURPOSE_LOGIN => 'Kirishni tasdiqlang',
            OneTimeCode::PURPOSE_PASSWORD_RESET => 'Parolni yangilang',
            default => "Ro'yxatdan o'tishni tasdiqlang",
        };

        $description = $purpose === OneTimeCode::PURPOSE_PASSWORD_RESET
            ? 'Parolni yangilash uchun quyidagi 6 xonali kodni kiriting. Agar kodni admin yuborgan bo\'lsa ham, shu kod ishlaydi.'
            : 'Assalomu alaykum. Quyidagi 6 xonali kodni saytdagi tasdiqlash oynasiga kiriting.';

        $actionHtml = '';
        if ($purpose === OneTimeCode::PURPOSE_PASSWORD_RESET) {
            $resetUrl = route('password.reset.form', ['email' => $email]);
            $actionHtml = '
                  <p style="margin:16px 0 0;text-align:center;">
                    <a href="'.$resetUrl.'" style="display:inline-block;padding:10px 16px;border-radius:10px;background:#2563eb;color:#ffffff;text-decoration:none;font-weight:700;">
                      Parolni yangilash oynasini ochish
                    </a>
                  </p>
            ';
        }

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

    private function issuePasswordResetOtp(User $user, array $extraMeta = []): void
    {
        $this->issueAndSendOtp((string) $user->email, OneTimeCode::PURPOSE_PASSWORD_RESET, array_merge([
            'user_id' => (int) $user->id,
        ], $extraMeta));
    }

    private function normalizeEmail(string $email): string
    {
        return strtolower(trim($email));
    }
}
