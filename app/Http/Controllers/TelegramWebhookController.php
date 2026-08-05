<?php

namespace App\Http\Controllers;

use App\Models\TelegramVerification;
use App\Services\TelegramService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TelegramWebhookController extends Controller
{
    public function __construct(
        private TelegramService $telegram,
    ) {}

    /**
     | Webhook endpoint — Telegram'dan kelgan xabarlarni qayta ishlash.
     */
    public function handle(Request $request, string $secret): JsonResponse
    {
        // Secret token tekshirish
        $expectedSecret = (string) config('telegram.webhook_secret', '');
        if ($expectedSecret === '' || ! hash_equals($expectedSecret, $secret)) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        // X-Telegram-Bot-Api-Secret-Token header tekshiruvi (qo'shimcha himoya)
        $headerSecret = $request->header('X-Telegram-Bot-Api-Secret-Token', '');
        if ($headerSecret !== '' && $expectedSecret !== '' && ! hash_equals($expectedSecret, $headerSecret)) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $update = $request->all();

        try {
            // Callback query (inline tugma bosilganda)
            if (isset($update['callback_query'])) {
                $this->handleCallbackQuery($update['callback_query']);

                return response()->json(['ok' => true]);
            }

            // Oddiy xabar
            if (isset($update['message'])) {
                $message = $update['message'];
                $chatId = (int) ($message['chat']['id'] ?? 0);
                $text = trim((string) ($message['text'] ?? ''));

                // /start <token> buyrug'i
                if (str_starts_with($text, '/start ')) {
                    $token = trim(substr($text, 7));
                    $this->handleStart($chatId, $token);

                    return response()->json(['ok' => true]);
                }

                // /start (token bilan)
                if ($text === '/start') {
                    $this->telegram->sendMessage($chatId, "Assalomu alaykum!\n\nSaytda ro'yxatdan o'tish yoki kirish uchun havolani bosing.");

                    return response()->json(['ok' => true]);
                }

                // Kontakt ulashganda
                if (isset($message['contact'])) {
                    $this->handleContact($message);

                    return response()->json(['ok' => true]);
                }
            }
        } catch (\Throwable $e) {
            Log::error('Telegram webhook processing error', [
                'error' => $e->getMessage(),
                'update_id' => $update['update_id'] ?? null,
            ]);
        }

        return response()->json(['ok' => true]);
    }

    /**
     | /start <token> — foydalanuvchi botni ochganda.
     */
    private function handleStart(int $chatId, string $token): void
    {
        $verification = TelegramVerification::query()
            ->where('token', $token)
            ->where('status', TelegramVerification::STATUS_PENDING)
            ->first();

        if (! $verification || $verification->isExpired()) {
            // Eskirgan yozuvni expired deb belgilash
            if ($verification) {
                $verification->update(['status' => TelegramVerification::STATUS_EXPIRED]);
            }

            $this->telegram->sendMessage($chatId,
                '⚠️ Havola eskirgan yoki noto\'g\'ri. Saytga qaytib, qayta urinib ko\'ring.'
            );

            return;
        }

        // Chat ID hali saqlanmaydi — faqat raqam so'raymiz
        $this->telegram->requestContact($chatId,
            "📱 Telefon raqamni ulashish\n\n"
            .'"Telefon raqamni ulashish" tugmasini bosing. '
            .('register' === $verification->purpose
                ? "Saytda kiritgan raqamingizni ulang."
                : "Hisobingizga bog'langan raqamni ulang.")
        );
    }

    /**
     | Kontakt ulashganda — raqamni tekshirish.
     */
    private function handleContact(array $message): void
    {
        $chatId = (int) ($message['chat']['id'] ?? 0);
        $fromId = (int) ($message['from']['id'] ?? 0);
        $contactUserId = (int) ($message['contact']['user_id'] ?? 0);
        $phoneNumber = (string) ($message['contact']['phone_number'] ?? '');

        // Tekshiruv: odam o'zining raqamini ulayotganini tekshirish
        if ($contactUserId !== $fromId) {
            $this->telegram->sendMessage($chatId,
                '❌ Xatolik: Iltimos, faqat o\'z telefon raqamingizni ulang.'
            );

            return;
        }

        if ($phoneNumber === '') {
            $this->telegram->sendMessage($chatId,
                '❌ Telefon raqam olinmadi. Qayta urinib ko\'ring.'
            );

            return;
        }

        $normalizedPhone = $this->normalizePhone($phoneNumber);

        // Shu chat orqali oxirgi pending yozuvni topish
        // Avval: shu chat orqali boshlangan oxirgi /start yozuvini topamiz
        $verification = TelegramVerification::query()
            ->where('status', TelegramVerification::STATUS_PENDING)
            ->where('expires_at', '>', now())
            ->whereRaw('(SELECT id FROM telegram_verifications tv 
                WHERE tv.token = telegram_verifications.token 
                AND tv.status = ? AND tv.expires_at > ?
                ORDER BY tv.id DESC LIMIT 1) = telegram_verifications.id', 
                [TelegramVerification::STATUS_PENDING, now()])
            ->latest('id')
            ->first();

        // Agar yuqoridagi so'rov ishlamasa, oddiy usul — oxirgi pending yozuv
        if (! $verification) {
            $verification = TelegramVerification::query()
                ->where('status', TelegramVerification::STATUS_PENDING)
                ->where('expires_at', '>', now())
                ->latest('id')
                ->first();
        }

        if (! $verification) {
            $this->telegram->sendMessage($chatId,
                '⚠️ Tasdiqlash so\'rovi topilmadi. Saytga qaytib, qayta urinib ko\'ring.'
            );

            return;
        }

        // Telefon raqam solishtirish
        $storedPhone = $this->normalizePhone((string) ($verification->phone ?? ''));

        if ($normalizedPhone !== $storedPhone) {
            $this->telegram->sendMessage($chatId,
                "❌ Bu raqam saytda kiritgan raqamingiz bilan mos emas.\n\n"
                ."Kiritilgan: ...".substr($storedPhone, -4)."\n"
                ."Ulangan: ...".substr($normalizedPhone, -4)."\n\n"
                ."Iltimos, to'g'ri raqamni ulang."
            );

            return;
        }

        // Tasdiqlash
        $verification->update([
            'status' => TelegramVerification::STATUS_VERIFIED,
            'telegram_chat_id' => $chatId,
            'verified_at' => now(),
        ]);

        $purposeLabel = match ($verification->purpose) {
            'register' => "Ro'yxatdan o'tish",
            'login' => 'Kirish',
            'password_reset' => 'Parolni tiklash',
            default => 'Tasdiqlash',
        };

        $this->telegram->sendMessage($chatId,
            "✅ Tasdiqlandi!\n\n"
            .$purposeLabel." muvaffaqiyatli tasdiqlandi.\n"
            ."Saytga qaytishingiz mumkin."
        );
    }

    /**
     | Callback query (inline tugma bosilganda — kelajak uchun).
     */
    private function handleCallbackQuery(array $callbackQuery): void
    {
        $callbackId = (string) ($callbackQuery['id'] ?? '');
        $data = (string) ($callbackQuery['data'] ?? '');
        $chatId = (int) ($callbackQuery['chat']['id'] ?? ($callbackQuery['message']['chat']['id'] ?? 0));

        // Password reset tasdiqlash callback
        if (str_starts_with($data, 'confirm_password_reset:')) {
            $token = substr($data, strlen('confirm_password_reset:'));
            $this->handlePasswordResetConfirm($chatId, $token, $callbackId);
        }
    }

    /**
     | Parolni tiklashni Telegram orqali tasdiqlash.
     */
    private function handlePasswordResetConfirm(int $chatId, string $token, string $callbackId): void
    {
        $verification = TelegramVerification::query()
            ->where('token', $token)
            ->where('purpose', TelegramVerification::PURPOSE_PASSWORD_RESET)
            ->where('status', TelegramVerification::STATUS_PENDING)
            ->first();

        if (! $verification || $verification->isExpired()) {
            $this->telegram->answerCallbackQuery($callbackId, 'Havola eskirgan.');
            $this->telegram->sendMessage($chatId, '⚠️ Havola eskirgan. Saytga qaytib, qayta urinib ko\'ring.');

            return;
        }

        $verification->update([
            'status' => TelegramVerification::STATUS_VERIFIED,
            'telegram_chat_id' => $chatId,
            'verified_at' => now(),
        ]);

        $this->telegram->answerCallbackQuery($callbackId, 'Tasdiqlandi!');
        $this->telegram->sendMessage($chatId,
            "✅ Parolni tiklash tasdiqlandi!\n\nSaytga qaytib, yangi parol kiriting."
        );
    }

    /**
     | Frontend polling uchun status endpoint.
     */
    public function status(string $token): JsonResponse
    {
        $verification = TelegramVerification::query()
            ->where('token', $token)
            ->first();

        if (! $verification) {
            return response()->json(['status' => 'not_found'], 404);
        }

        if ($verification->isExpired() && $verification->status === TelegramVerification::STATUS_PENDING) {
            $verification->update(['status' => TelegramVerification::STATUS_EXPIRED]);

            return response()->json(['status' => 'expired']);
        }

        return response()->json(['status' => $verification->status]);
    }

    /**
     | Tasdiqlangandan keyin — foydalanuvchini login qildirish va yo'naltirish.
     */
    public function complete(string $token)
    {
        $verification = TelegramVerification::query()
            ->where('token', $token)
            ->where('status', TelegramVerification::STATUS_VERIFIED)
            ->first();

        if (! $verification) {
            return redirect()->route('login')
                ->with('error', 'Tasdiqlash topilmadi yoki hali yakunlanmagan.');
        }

        $purpose = $verification->purpose;

        match ($purpose) {
            'register' => $this->completeRegister($verification),
            'login' => $this->completeLogin($verification),
            'password_reset' => $this->completePasswordReset($verification),
            default => null,
        };

        return redirect()->route('home');
    }

    /**
     | Ro'yxatdan o'tishni yakunlash.
     */
    private function completeRegister(TelegramVerification $verification): void
    {
        $payload = $verification->session_payload ?? [];

        if (empty($payload['name']) || empty($payload['email']) || empty($payload['password'])) {
            return;
        }

        // Foydalanuvchi yaratish
        $user = \App\Models\User::create([
            'name' => $payload['name'],
            'first_name' => $payload['first_name'] ?? null,
            'last_name' => $payload['last_name'] ?? null,
            'email' => $payload['email'],
            'username' => $payload['username'] ?? null,
            'password' => $payload['password'], // allaqachon hash qilingan
            'phone' => $verification->phone ?? null,
            'grade' => $payload['grade'] ?? null,
            'is_parent' => $payload['is_parent'] ?? false,
            'telegram_chat_id' => $verification->telegram_chat_id,
            'email_verified_at' => now(),
        ]);

        // Login qildirish
        \Illuminate\Support\Facades\Auth::login($user);

        // Yozuvni tozalash
        $verification->update(['status' => TelegramVerification::STATUS_COMPLETED]);
    }

    /**
     | Kirishni yakunlash.
     */
    private function completeLogin(TelegramVerification $verification): void
    {
        $payload = $verification->session_payload ?? [];
        $userId = (int) ($payload['user_id'] ?? 0);

        $user = \App\Models\User::find($userId);
        if (! $user) {
            return;
        }

        // Telegram chat ID ni saqlash (agar yo'q bo'lsa)
        if (! $user->telegram_chat_id && $verification->telegram_chat_id) {
            $user->update(['telegram_chat_id' => $verification->telegram_chat_id]);
        }

        // Login qildirish
        \Illuminate\Support\Facades\Auth::login($user);

        // Yozuvni tozalash
        $verification->update(['status' => TelegramVerification::STATUS_COMPLETED]);
    }

    /**
     | Parolni tiklashni yakunlash.
     */
    private function completePasswordReset(TelegramVerification $verification): void
    {
        $payload = $verification->session_payload ?? [];
        $userId = (int) ($payload['user_id'] ?? 0);

        $user = \App\Models\User::find($userId);
        if (! $user) {
            return;
        }

        // Yangi parolni yangilash (parolni tiklash uchun yangi parol session_payload'da saqlanadi)
        // Hozircha faqat login qildiramiz, parolni keyin o'zgartirish mumkin

        // Telegram chat ID ni saqlash
        if (! $user->telegram_chat_id && $verification->telegram_chat_id) {
            $user->update(['telegram_chat_id' => $verification->telegram_chat_id]);
        }

        // Login qildirish
        \Illuminate\Support\Facades\Auth::login($user);

        // Yozuvni tozalash
        $verification->update(['status' => TelegramVerification::STATUS_COMPLETED]);
    }

    /**
     | Telefon raqamni normallashtirish.
     */
    private function normalizePhone(string $phone): string
    {
        // Bo'shliq, tire, qavslarni olib tashlash
        $phone = preg_replace('/[\s\-\(\)\.]/', '', $phone);

        // Agar + bilan boshlanmasa, + qo'shish
        if (! str_starts_with($phone, '+')) {
            // 998 bilan boshlansa
            if (str_starts_with($phone, '998')) {
                $phone = '+'.$phone;
            }
            // 8 bilan boshlansa (eski format)
            elseif (str_starts_with($phone, '8') && strlen($phone) === 9) {
                $phone = '+998'.substr($phone, 1);
            }
        }

        return $phone;
    }

    /**
     | Parolni tiklash uchun Telegram xabar yuborish (AuthController dan chaqiriladi).
     */
    public static function sendPasswordResetRequest(int $chatId, string $token): void
    {
        $botUsername = (string) config('telegram.bot_username', '');
        $verification = TelegramVerification::where('token', $token)->first();

        if (! $verification || ! $chatId) {
            return;
        }

        $telegram = new TelegramService();
        $telegram->sendMessage($chatId,
            "🔐 Parolni tiklash so'rovi\n\n"
            ."Saytda parolni tiklash so'rovi yuborildi.\n"
            ."Tasdiqlaysizmi?",
            [
                'inline_keyboard' => [
                    [
                        [
                            'text' => '✅ Ha, tasdiqlayman',
                            'callback_data' => 'confirm_password_reset:'.$token,
                        ],
                    ],
                    [
                        [
                            'text' => '❌ Bekor qilish',
                            'callback_data' => 'cancel_password_reset:'.$token,
                        ],
                    ],
                ],
            ]
        );
    }
}
