<?php

namespace App\Http\Controllers;

use App\Models\TelegramRegistrationVerification;
use App\Services\Telegram\TelegramBotService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TelegramWebhookController extends Controller
{
    public function __construct(
        private readonly TelegramBotService $telegramBot,
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        if (! $this->telegramBot->isConfigured()) {
            return response()->json(['ok' => true]);
        }

        $message = $request->input('message');
        if (! is_array($message)) {
            return response()->json(['ok' => true]);
        }

        try {
            $this->handleMessage($message);
        } catch (\Throwable $e) {
            Log::warning('Telegram register webhook failed', [
                'message' => $e->getMessage(),
            ]);
        }

        return response()->json(['ok' => true]);
    }

    private function handleMessage(array $message): void
    {
        $chatId = (int) data_get($message, 'chat.id', 0);
        $fromId = (int) data_get($message, 'from.id', 0);

        if ($chatId <= 0 || $fromId <= 0) {
            return;
        }

        $text = trim((string) ($message['text'] ?? ''));
        if ($text !== '' && Str::startsWith($text, '/start')) {
            $this->handleStartCommand($chatId, $fromId, $text, $message);

            return;
        }

        $contact = $message['contact'] ?? null;
        if (is_array($contact)) {
            $this->handleContact($chatId, $fromId, $message, $contact);

            return;
        }

        $this->telegramBot->sendMessage($chatId, "Tasdiqlash uchun avval saytda ro'yxatdan o'ting va keyin shu botga qayting.");
    }

    private function handleStartCommand(int $chatId, int $fromId, string $text, array $message): void
    {
        $parts = preg_split('/\s+/', $text, 2);
        $payload = trim((string) ($parts[1] ?? ''));

        if (! Str::startsWith($payload, 'verify_')) {
            $this->telegramBot->sendMessage($chatId, "Ro'yxatdan o'tishni tasdiqlash uchun saytdagi Telegram tugmasi orqali kiring.");

            return;
        }

        $token = Str::after($payload, 'verify_');
        $verification = TelegramRegistrationVerification::query()
            ->where('token', $token)
            ->whereNull('completed_at')
            ->first();

        if (! $verification || $verification->isExpired()) {
            $this->telegramBot->sendMessage($chatId, "Bu tasdiqlash havolasi eskirgan. Saytda ro'yxatdan o'tishni qayta boshlang.");

            return;
        }

        $verification->forceFill([
            'telegram_user_id' => $fromId,
            'telegram_chat_id' => $chatId,
            'telegram_username' => (string) data_get($message, 'from.username', ''),
            'started_at' => now(),
        ])->save();

        $this->telegramBot->requestContact($chatId, $verification);
    }

    private function handleContact(int $chatId, int $fromId, array $message, array $contact): void
    {
        $verification = TelegramRegistrationVerification::query()
            ->where('telegram_user_id', $fromId)
            ->whereNull('completed_at')
            ->latest('id')
            ->first();

        if (! $verification || $verification->isExpired()) {
            $this->telegramBot->sendMessage($chatId, "Faol tasdiqlash topilmadi. Saytdan qayta urinib ko'ring.");

            return;
        }

        $contactUserId = (int) data_get($contact, 'user_id', 0);
        if ($contactUserId !== $fromId) {
            $this->telegramBot->sendMessage($chatId, "Faqat o'zingizning Telegram kontaktingizni yuborishingiz mumkin.");

            return;
        }

        $expectedPhone = $this->normalizePhoneForCompare($verification->phone);
        $receivedPhone = $this->normalizePhoneForCompare((string) data_get($contact, 'phone_number', ''));

        if ($expectedPhone === '' || $expectedPhone !== $receivedPhone) {
            $this->telegramBot->sendMessage($chatId, "Telefon raqam mos kelmadi. Saytda kiritgan raqamingiz bilan aynan bir xil Telegram raqamidan foydalaning.");

            return;
        }

        $verification->forceFill([
            'telegram_chat_id' => $chatId,
            'telegram_username' => (string) data_get($message, 'from.username', ''),
            'telegram_phone' => uz_phone_format((string) data_get($contact, 'phone_number', '')),
            'verified_at' => now(),
        ])->save();

        $this->telegramBot->sendVerificationConfirmed($chatId);
    }

    private function normalizePhoneForCompare(?string $phone): string
    {
        return preg_replace('/\D+/', '', (string) $phone) ?? '';
    }
}
