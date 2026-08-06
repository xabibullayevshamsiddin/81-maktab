<?php

namespace App\Http\Controllers;

use App\Models\TelegramVerification;
use App\Services\TelegramService;
use App\Services\TelegramUpdateHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TelegramWebhookController extends Controller
{
    public function __construct(
        private TelegramService $telegram,
        private TelegramUpdateHandler $handler,
    ) {}

    /**
     | Webhook endpoint — Telegram'dan kelgan xabarlarni qayta ishlash.
     */
    public function handle(Request $request, string $secret): JsonResponse
    {
        $expectedSecret = (string) config('telegram.webhook_secret', '');
        if ($expectedSecret === '' || ! hash_equals($expectedSecret, $secret)) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $headerSecret = $request->header('X-Telegram-Bot-Api-Secret-Token', '');
        if ($headerSecret !== '' && $expectedSecret !== '' && ! hash_equals($expectedSecret, $headerSecret)) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $update = $request->all();

        try {
            if (isset($update['callback_query'])) {
                $this->handler->handleCallbackQuery($update['callback_query']);

                return response()->json(['ok' => true]);
            }

            if (isset($update['message'])) {
                $message = $update['message'];
                $chatId = (int) ($message['chat']['id'] ?? 0);
                $text = trim((string) ($message['text'] ?? ''));

                if (str_starts_with($text, '/start ')) {
                    $token = trim(substr($text, 7));
                    $this->handler->handleStart($chatId, $token);

                    return response()->json(['ok' => true]);
                }

                if ($text === '/start') {
                    $this->handler->handleStartGeneric($chatId);

                    return response()->json(['ok' => true]);
                }

                if (isset($message['contact'])) {
                    $this->handler->handleContact($message);

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

        match ($verification->purpose) {
            'register' => $this->completeRegister($verification),
            'login' => $this->completeLogin($verification),
            'password_reset' => $this->completePasswordReset($verification),
            default => null,
        };

        return redirect()->route('home');
    }

    private function completeRegister(TelegramVerification $verification): void
    {
        $payload = $verification->session_payload ?? [];

        if (empty($payload['name']) || empty($payload['email']) || empty($payload['password'])) {
            return;
        }

        $user = \App\Models\User::create([
            'name' => $payload['name'],
            'first_name' => $payload['first_name'] ?? null,
            'last_name' => $payload['last_name'] ?? null,
            'email' => $payload['email'],
            'username' => $payload['username'] ?? null,
            'password' => $payload['password'],
            'phone' => $verification->phone ?? null,
            'grade' => $payload['grade'] ?? null,
            'is_parent' => $payload['is_parent'] ?? false,
            'telegram_chat_id' => $verification->telegram_chat_id,
            'email_verified_at' => now(),
        ]);

        \Illuminate\Support\Facades\Auth::login($user);
        $verification->update(['status' => TelegramVerification::STATUS_COMPLETED]);
    }

    private function completeLogin(TelegramVerification $verification): void
    {
        $payload = $verification->session_payload ?? [];
        $userId = (int) ($payload['user_id'] ?? 0);

        $user = \App\Models\User::find($userId);
        if (! $user) {
            return;
        }

        if (! $user->telegram_chat_id && $verification->telegram_chat_id) {
            $user->update(['telegram_chat_id' => $verification->telegram_chat_id]);
        }

        \Illuminate\Support\Facades\Auth::login($user);
        $verification->update(['status' => TelegramVerification::STATUS_COMPLETED]);
    }

    private function completePasswordReset(TelegramVerification $verification): void
    {
        $payload = $verification->session_payload ?? [];
        $userId = (int) ($payload['user_id'] ?? 0);

        $user = \App\Models\User::find($userId);
        if (! $user) {
            return;
        }

        if (! $user->telegram_chat_id && $verification->telegram_chat_id) {
            $user->update(['telegram_chat_id' => $verification->telegram_chat_id]);
        }

        \Illuminate\Support\Facades\Auth::login($user);
        $verification->update(['status' => TelegramVerification::STATUS_COMPLETED]);
    }

    /**
     | Parolni tiklash uchun Telegram xabar yuborish.
     */
    public static function sendPasswordResetRequest(int $chatId, string $token): void
    {
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
