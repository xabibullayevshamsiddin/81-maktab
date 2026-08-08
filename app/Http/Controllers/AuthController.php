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

/**
 * AuthController — Tizimga kirish, ro'yxatdan o'tish, parolni tiklash.
 * 
 * // ═══════════════════════════════════════════════════════════════
 * // TELEGRAM XABAR FORMATI — ESLOMA
 * // ═══════════════════════════════════════════════════════════════
 * // Yashirin kodlar (parollar) uchun:
 * //   <tg-spoiler>matn</tg-spoiler>  — bosganda ochiladi
 * // 
 * // Tasdiqlash kodlari uchun:
 * //   <code>123456</code>  — monospace formatda ko'rinadi
 * // 
 * // Qalin harflar:
 * //   <b>matn</b>  — qalin ko'rinadi
 * // 
 * // Misol:
 * //   $text = "🔑 <b>Kod:</b>\n" . "<code>{$code}</code>";
 * //   $text = "🔐 <b>Parol:</b>\n" . "<tg-spoiler>{$password}</tg-spoiler>";
 * // ═══════════════════════════════════════════════════════════════
 */
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
            'phone' => (string) $request->query('phone', ''),
        ]);
    }

    public function authenticate(LoginRequest $request)
    {
        $credentials = $request->validated();
        $phone = $this->normalizePhone($credentials['phone']);

        $user = User::query()->where('phone', $phone)->first();
        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            return back()
                ->withErrors([
                    'phone' => "Telefon raqami yoki parol noto'g'ri.",
                ])
                ->onlyInput('phone');
        }

        // Telegram chat_id bor — 2FA: Telegramga 6 xonali kod yuborish
        if ($user->telegram_chat_id) {
            $loginCode = (string) random_int(100000, 999999);
            
            // Kodni session'ga saqlash
            $request->session()->put('login_verification', [
                'user_id' => $user->id,
                'code' => $loginCode,
                'expires_at' => now()->addMinutes(5)->timestamp,
            ]);
            
            // Telegramga kod yuborish
            // // YASHIRIN KODLAR FORMATI:
            // // <tg-spoiler>matn</tg-spoiler> — Telegramda yashirin (bosganda ko'rinadi)
            // // <code>matn</code> — monospace formatda ko'rinadi (kodlar uchun)
            // // <b>matn</b> — qalin harflar
            $telegram = app(\App\Services\TelegramService::class);
            $text = "🔐 <b>Tizimga kirish tasdiqlash kodi</b>\n"
                ."━━━━━━━━━━━━━━━━━━━━\n\n"
                ."👤 <b>Foydalanuvchi:</b> ".htmlspecialchars($user->name)."\n"
                ."⏰ <b>Sana:</b> ".now()->format('d.m.Y H:i')."\n\n"
                ."🔑 <b>Tasdiqlash kodi:</b>\n"
                ."<code>{$loginCode}</code>\n\n"
                ."━━━━━━━━━━━━━━━━━━━━\n\n"
                ."⚠️ <b>Muhim!</b>\n"
                ."• Kod 5 daqiqa amal qiladi\n"
                ."• Kodni hech kimga bermang\n"
                ."• Agar siz bu so'rovni yubormagan bo'lsangiz, e'tiborsiz qoldiring";
            
            $telegram->sendMessage((int) $user->telegram_chat_id, $text);
            
            // Kod kiritish sahifasiga yo'naltirish
            return redirect()->route('login.verify.form')
                ->with('success', 'Telegramga tasdiqlash kodi yuborildi.')
                ->with('toast_type', 'success');
        }

        // Telegram chat_id yo'q — to'g'ridan-to'g'ri kirishga ruxsat berish
        Auth::login($user, true);
        $request->session()->regenerate();

        return redirect()->intended(route('home'))
            ->with('success', 'Tizimga muvaffaqiyatli kirdingiz.')
            ->with('toast_type', 'success');
    }

    /**
     | Login uchun 2FA tasdiqlash sahifasini ko'rsatish.
     */
    public function showLoginVerifyForm(Request $request)
    {
        $verification = $request->session()->get('login_verification');
        
        if (! $verification || ! isset($verification['user_id'])) {
            return redirect()->route('login')
                ->withErrors(['phone' => 'Sessiya muddati tugagan. Qayta kiring.'])
                ->with('toast_type', 'error');
        }
        
        // Kod muddati tugaganini tekshirish
        if (now()->timestamp > ($verification['expires_at'] ?? 0)) {
            $request->session()->forget('login_verification');
            return redirect()->route('login')
                ->withErrors(['phone' => 'Tasdiqlash kodi muddati tugagan. Qayta kiring.'])
                ->with('toast_type', 'error');
        }
        
        return view('login.login-verify-code');
    }

    /**
     | Login uchun 2FA kodini tekshirish.
     */
    public function verifyLoginCode(Request $request)
    {
        $validated = $request->validate([
            'code' => ['required', 'digits:6'],
        ], [
            'code.required' => 'Tasdiqlash kodini kiriting.',
            'code.digits' => 'Kod 6 xonali bo\'lishi kerak.',
        ]);
        
        $verification = $request->session()->get('login_verification');
        
        if (! $verification || ! isset($verification['user_id'])) {
            return redirect()->route('login')
                ->withErrors(['phone' => 'Sessiya muddati tugagan. Qayta kiring.'])
                ->with('toast_type', 'error');
        }
        
        // Kod muddati tugaganini tekshirish
        if (now()->timestamp > ($verification['expires_at'] ?? 0)) {
            $request->session()->forget('login_verification');
            return redirect()->route('login')
                ->withErrors(['phone' => 'Tasdiqlash kodi muddati tugagan. Qayta kiring.'])
                ->with('toast_type', 'error');
        }
        
        // Kodni tekshirish
        if ($validated['code'] !== $verification['code']) {
            return back()
                ->withErrors(['code' => 'Kod noto\'g\'ri.'])
                ->onlyInput('code');
        }
        
        // Foydalanuvchini topish va kirishni yakunlash
        $user = User::find($verification['user_id']);
        if (! $user) {
            $request->session()->forget('login_verification');
            return redirect()->route('login')
                ->withErrors(['phone' => 'Foydalanuvchi topilmadi.'])
                ->with('toast_type', 'error');
        }
        
        // Sessiyani tozalash
        $request->session()->forget('login_verification');

        // Kirishni yakunlash
        Auth::login($user, true);
        $request->session()->regenerate();

        return redirect()->intended(route('home'))
            ->with('success', 'Tizimga muvaffaqiyatli kirdingiz.')
            ->with('toast_type', 'success');
    }

    /**
     | Login uchun 2FA kodini qayta yuborish.
     */
    public function resendLoginCode(Request $request)
    {
        $verification = $request->session()->get('login_verification');
        
        if (! $verification || ! isset($verification['user_id'])) {
            return redirect()->route('login')
                ->withErrors(['phone' => 'Sessiya muddati tugagan. Qayta kiring.'])
                ->with('toast_type', 'error');
        }
        
        $user = User::find($verification['user_id']);
        if (! $user || ! $user->telegram_chat_id) {
            return redirect()->route('login')
                ->withErrors(['phone' => 'Foydalanuvchi topilmadi.'])
                ->with('toast_type', 'error');
        }
        
        // Yangi kod yaratish
        $loginCode = (string) random_int(100000, 999999);
        
        // Session'ni yangilash
        $request->session()->put('login_verification', [
            'user_id' => $user->id,
            'code' => $loginCode,
            'expires_at' => now()->addMinutes(5)->timestamp,
        ]);
        
        // Telegramga yangi kod yuborish
        $telegram = app(\App\Services\TelegramService::class);
        $text = "🔐 <b>Tizimga kirish tasdiqlash kodi (yangi)</b>\n"
            ."━━━━━━━━━━━━━━━━━━━━\n\n"
            ."👤 <b>Foydalanuvchi:</b> ".htmlspecialchars($user->name)."\n"
            ."⏰ <b>Sana:</b> ".now()->format('d.m.Y H:i')."\n\n"
            ."🔑 <b>Tasdiqlash kodi:</b>\n"
            ."<code>{$loginCode}</code>\n\n"
            ."━━━━━━━━━━━━━━━━━━━━\n\n"
            ."⚠️ <b>Muhim!</b>\n"
            ."• Kod 5 daqiqa amal qiladi\n"
            ."• Kodni hech kimga bermang\n"
            ."• Agar siz bu so'rovni yubormagan bo'lsangiz, e'tiborsiz qoldiring";
        
        $telegram->sendMessage((int) $user->telegram_chat_id, $text);
        
        return back()
            ->with('success', 'Telegramga yangi tasdiqlash kodi yuborildi.')
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
                // Parolni tiklash uchun redirect — telefon raqam bilan
                return redirect()->route('password.reset.form', ['phone' => $verification->phone ?? '']);

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

        // Telefon allaqachon mavjudmi?
        $normalizedPhone = uz_phone_format($meta['phone'] ?? '');
        if ($normalizedPhone && User::query()->where('phone', $normalizedPhone)->exists()) {
            return redirect()->route('register')
                ->withErrors(['phone' => 'Bu telefon raqami allaqachon ro\'yxatdan o\'tgan.'])
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
     | Parolni tiklash — telefon raqam kiritilganda.
     */
    public function sendPasswordResetCode(Request $request)
    {
        $validated = $request->validate([
            'phone' => ['required', 'string', 'max:20'],
        ]);

        $phone = $this->normalizePhone($validated['phone']);
        $user = User::query()->where('phone', $phone)->first();

        if (! $user) {
            return back()
                ->withErrors(['phone' => 'Bu telefon raqami bilan hisob topilmadi.'])
                ->onlyInput('phone');
        }

        // Telegram chat_id bor — Telegram orqali 6 xonali kod yuborish
        if ($user->telegram_chat_id) {
            $token = $this->createTelegramVerification(
                TelegramVerification::PURPOSE_PASSWORD_RESET,
                $user->email,
                $user->phone ?? '',
                ['user_id' => $user->id, 'phone' => $user->phone]
            );

            // Telegram orqali 6 xonali kod yuborish
            \App\Http\Controllers\TelegramWebhookController::sendPasswordResetRequest(
                (int) $user->telegram_chat_id,
                $token
            );

            return redirect()->route('password.reset.form', ['phone' => $phone])
                ->with('success', 'Telegram orqali 6 xonali tasdiqlash kodi yuborildi.')
                ->with('toast_type', 'success');
        }

        // Telegram chat_id yo'q — xato berish
        return back()
            ->withErrors(['phone' => 'Bu foydalanuvchi Telegram bilan bog\'lanmagan. Admin bilan bog\'laning.'])
            ->onlyInput('phone');
    }

    public function showPasswordResetForm(Request $request)
    {
        $phone = (string) $request->query('phone', '');
        if ($phone === '') {
            return redirect()->route('password.forgot.form');
        }

        $phone = $this->normalizePhone($phone);

        return view('login.reset-password', [
            'phone' => $phone,
        ]);
    }

    public function resetPassword(Request $request)
    {
        $validated = $request->validate([
            'phone' => ['required', 'string', 'max:20'],
            'code' => ['required', 'digits:6'],
            'password' => [
                'required', 'string', 'min:8', 'confirmed',
            ],
        ], [
            'phone.required' => 'Telefon raqamni kiriting.',
            'code.required' => 'Tasdiqlash kodini kiriting.',
            'code.digits' => 'Kod 6 xonali bo\'lishi kerak.',
            'password.required' => 'Yangi parolni kiriting.',
            'password.min' => 'Yangi parol kamida 8 belgidan iborat bo\'lishi kerak.',
            'password.confirmed' => 'Yangi parol tasdiqlanmadi.',
        ]);

        $phone = $this->normalizePhone($validated['phone']);
        $code = $validated['code'];

        // TelegramVerification orqali tasdiqlash — kodni tekshirish
        $verification = TelegramVerification::query()
            ->where('phone', $phone)
            ->where('purpose', TelegramVerification::PURPOSE_PASSWORD_RESET)
            ->where('verification_code', $code)
            ->where('status', TelegramVerification::STATUS_PENDING)
            ->latest('id')
            ->first();

        if (! $verification) {
            return back()
                ->withErrors(['code' => 'Kod noto\'g\'ri yoki muddati tugagan.'])
                ->withInput($request->except(['password', 'password_confirmation']));
        }

        // Muddati tugaganini tekshirish
        if ($verification->isExpired()) {
            $verification->update(['status' => TelegramVerification::STATUS_EXPIRED]);
            return back()
                ->withErrors(['code' => 'Kod muddati tugagan. Yangi kod oling.'])
                ->withInput($request->except(['password', 'password_confirmation']));
        }

        $meta = $verification->session_payload ?? [];
        $userId = (int) ($meta['user_id'] ?? 0);

        $user = User::query()->find($userId);
        if (! $user) {
            return back()
                ->withErrors(['phone' => 'Foydalanuvchi topilmadi.'])
                ->withInput($request->except(['password', 'password_confirmation']));
        }

        // Parolni yangilash
        $user->forceFill([
            'password' => Hash::make($validated['password']),
            'remember_token' => Str::random(60),
        ])->save();

        // Tasdiqlash yozuvini o'chirish
        $verification->update(['status' => TelegramVerification::STATUS_COMPLETED]);

        return redirect()
            ->route('login')
            ->with('success', 'Parol yangilandi. Endi yangi parol bilan tizimga kiring.')
            ->with('toast_type', 'success');
    }

    public function resendPasswordResetCode(Request $request)
    {
        $validated = $request->validate([
            'phone' => ['required', 'string', 'max:20'],
        ]);

        $phone = $this->normalizePhone($validated['phone']);
        $user = User::query()->where('phone', $phone)->first();

        if (! $user) {
            return redirect()
                ->route('password.forgot.form', ['phone' => $phone])
                ->withErrors(['phone' => 'Bu telefon raqami bilan hisob topilmadi.']);
        }

        // Telegram orqali qayta yuborish — yangi kod bilan
        if ($user->telegram_chat_id) {
            $token = $this->createTelegramVerification(
                TelegramVerification::PURPOSE_PASSWORD_RESET,
                $user->email,
                $user->phone ?? '',
                ['user_id' => $user->id, 'phone' => $user->phone]
            );

            \App\Http\Controllers\TelegramWebhookController::sendPasswordResetRequest(
                (int) $user->telegram_chat_id,
                $token
            );

            return back()
                ->with('success', 'Telegram orqali yangi 6 xonali kod yuborildi.')
                ->with('toast_type', 'success');
        }

        // Telegram chat_id yo'q
        return back()
            ->withErrors(['phone' => 'Bu foydalanuvchi Telegram bilan bog\'lanmagan. Admin bilan bog\'laning.']);
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
                ['user_id' => $user->id, 'phone' => $user->phone, 'issued_by_admin_id' => (int) $admin->id]
            );

            \App\Http\Controllers\TelegramWebhookController::sendPasswordResetRequest(
                (int) $user->telegram_chat_id,
                $token
            );

            return redirect()
                ->route('user')
                ->with('success', "{$user->name} uchun parolni tiklash kodi Telegram ga yuborildi.")
                ->with('toast_type', 'success');
        }

        // Telegram chat_id yo'q
        return redirect()
            ->route('user')
            ->with('error', "{$user->name} Telegram bilan bog'lanmagan. Avval Telegram tasdiqlashini so'rang.")
            ->with('toast_type', 'warning');
    }

    /**
     * Admin tomonidan foydalanuvchiga vaqtincha parol yaratish va Telegram ga yuborish.
     */
    public function adminGenerateTempPassword(Request $request, User $user)
    {
        $admin = $request->user();

        if (! $admin || ! $admin->canManage($user)) {
            return redirect()
                ->route('user')
                ->with('error', 'Siz bu foydalanuvchi uchun vaqtincha parol yarata olmaysiz.')
                ->with('toast_type', 'error');
        }

        // Telegram chat_id yo'q — xabar berish
        if (! $user->telegram_chat_id) {
            return redirect()
                ->route('user')
                ->with('error', "{$user->name} Telegram bilan bog'lanmagan. Avval Telegram tasdiqlashini so'rang.")
                ->with('toast_type', 'warning');
        }

        // Vaqtincha parol yaratish (8 ta belgi: harflar va raqamlar)
        $tempPassword = strtoupper(Str::random(2)) . Str::random(4) . rand(10, 99);

        // Parolni yangilash + barcha sessiyalarni bekor qilish
        $user->forceFill([
            'password' => Hash::make($tempPassword),
            'remember_token' => Str::random(60),
        ])->save();

        // Fayl sessiyalarini ham tozalash — barcha qurilmalardan chiqarish
        // Sessiya formati: login_web_{hash}";i:{user_id};
        $sessionPath = storage_path('framework/sessions');
        if (is_dir($sessionPath)) {
            $files = glob($sessionPath . '/*');
            foreach ($files as $file) {
                $content = file_get_contents($file);
                // PHP serialize formatida user_id ni qidirish
                if (is_string($content) && preg_match('/login_web_[a-f0-9]+";i:' . $user->id . ';/', $content)) {
                    unlink($file);
                }
            }
        }

        // Telegram ga yuborish
        // // YASHIRIN KOD FORMATI:
        // // <tg-spoiler>matn</tg-spoiler> — Telegramda yashirin ko'rinadi
        // // Foydalanuvchi bosganda matn ochiladi (parollar uchun ishlatiladi)
        // // Boshqa formatlar:
        // // <code>matn</code> — monospace (tasdiqlash kodlari uchun)
        // // <b>matn</b> — qalin harflar
        $telegram = app(\App\Services\TelegramService::class);
        $adminName = htmlspecialchars($admin->buildNameFromParts() ?: $admin->name);
        $userName = htmlspecialchars($user->buildNameFromParts() ?: $user->name);

        $text = "🔐 <b>Himoya paroli yaratildi</b>\n"
            ."━━━━━━━━━━━━━━━━━━━━\n\n"
            ."👤 <b>Foydalanuvchi:</b> {$userName}\n"
            ."👨‍💼 <b>Admin:</b> {$adminName}\n"
            ."⏰ <b>Sana:</b> ".now()->format('d.m.Y H:i')."\n\n"
            ."🔑 <b>Sizning yangi parolingiz:</b>\n"
            ."<tg-spoiler>{$tempPassword}</tg-spoiler>\n\n"
            ."━━━━━━━━━━━━━━━━━━━━\n\n"
            ."⚠️ <b>Muhim!</b>\n"
            ."• Kirgandan keyin parolni o'zgartiring\n"
            ."• Parolni hech kimga bermang\n"
            ."• Faqat o'zingiz ishlating";

        $telegram->sendMessage((int) $user->telegram_chat_id, $text);

        // Admin ga xabar
        return redirect()
            ->route('user')
            ->with('success', "{$user->name} uchun vaqtincha parol yaratildi va Telegram ga yuborildi.")
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

        // PASSWORD_RESET uchun 6 xonali kod yaratish
        $verificationCode = null;
        if ($purpose === TelegramVerification::PURPOSE_PASSWORD_RESET) {
            $verificationCode = (string) random_int(100000, 999999);
        }

        TelegramVerification::create([
            'token' => $token,
            'verification_code' => $verificationCode,
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

    private function normalizePhone(string $phone): string
    {
        $phone = preg_replace('/[\s\-\(\)\.]/', '', $phone);

        if (! str_starts_with($phone, '+')) {
            if (str_starts_with($phone, '998')) {
                $phone = '+'.$phone;
            } elseif (str_starts_with($phone, '8') && strlen($phone) === 9) {
                $phone = '+998'.substr($phone, 1);
            }
        }

        return $phone;
    }
}
