<?php

namespace App\Console\Commands;

use App\Models\TelegramVerification;
use App\Services\TelegramService;
use Illuminate\Console\Command;

class TelegramPollUpdates extends Command
{
    protected $signature = 'telegram:poll {--timeout=30} {--once}';
    protected $description = 'Telegram dan getUpdates orqali xabarlarni olish (webhook kerak emas)';

    public function handle(TelegramService $telegram): int
    {
        $timeout = (int) $this->option('timeout');
        $once = $this->option('once');

        $this->info('🔄 Telegram polling boshlandi...');

        do {
            $updates = $telegram->getUpdates($timeout);

            if (empty($updates)) {
                if ($once) {
                    $this->info('Xabar yo\'q.');
                    return Command::SUCCESS;
                }
                continue;
            }

            foreach ($updates as $update) {
                $this->processUpdate($update, $telegram);
                $telegram->markUpdateProcessed($update['update_id']);
            }

        } while (! $once);

        return Command::SUCCESS;
    }

    private function processUpdate(array $update, TelegramService $telegram): void
    {
        // Callback query
        if (isset($update['callback_query'])) {
            $this->handleCallbackQuery($update['callback_query'], $telegram);
            return;
        }

        // Oddiy xabar
        if (! isset($update['message'])) {
            return;
        }

        $message = $update['message'];
        $chatId = (int) ($message['chat']['id'] ?? 0);
        $text = trim((string) ($message['text'] ?? ''));

        // /start <token>
        if (str_starts_with($text, '/start ')) {
            $token = trim(substr($text, 7));
            $this->handleStart($chatId, $token, $telegram);
            return;
        }

        // /start (token bilan)
        if ($text === '/start') {
            $telegram->sendMessage($chatId, "Assalomu alaykum!\n\nSaytda ro'yxatdan o'tish yoki kirish uchun havolani bosing.");
            return;
        }

        // Kontakt ulashganda
        if (isset($message['contact'])) {
            $this->handleContact($message, $telegram);
            return;
        }
    }

    private function handleStart(int $chatId, string $token, TelegramService $telegram): void
    {
        $verification = TelegramVerification::query()
            ->where('token', $token)
            ->where('status', TelegramVerification::STATUS_PENDING)
            ->first();

        if (! $verification || $verification->isExpired()) {
            if ($verification) {
                $verification->update(['status' => TelegramVerification::STATUS_EXPIRED]);
            }
            $telegram->sendMessage($chatId, "⚠️ Havola eskirgan. Saytga qaytib, qayta urinib ko'ring.");
            return;
        }

        $telegram->requestContact($chatId,
            "📱 Telefon raqamni ulashish\n\n"
            .'Telefon raqamni ulashish tugmasini bosing. '
            .('register' === $verification->purpose
                ? "Saytda kiritgan raqamingizni ulang."
                : "Hisobingizga bog'langan raqamni ulang.")
        );
    }

    private function handleContact(array $message, TelegramService $telegram): void
    {
        $chatId = (int) ($message['chat']['id'] ?? 0);
        $fromId = (int) ($message['from']['id'] ?? 0);
        $contactUserId = (int) ($message['contact']['user_id'] ?? 0);
        $phoneNumber = (string) ($message['contact']['phone_number'] ?? '');

        if ($contactUserId !== $fromId) {
            $telegram->sendMessage($chatId, "❌ Iltimos, faqat o'z telefon raqamingizni ulang.");
            return;
        }

        if ($phoneNumber === '') {
            $telegram->sendMessage($chatId, "❌ Telefon raqam olinmadi.");
            return;
        }

        $normalizedPhone = $this->normalizePhone($phoneNumber);

        // Oxirgi pending yozuvni topish
        $verification = TelegramVerification::query()
            ->where('status', TelegramVerification::STATUS_PENDING)
            ->where('expires_at', '>', now())
            ->latest('id')
            ->first();

        if (! $verification) {
            $telegram->sendMessage($chatId, "⚠️ Tasdiqlash so'rovi topilmadi. Saytga qaytib, qayta urinib ko'ring.");
            return;
        }

        $storedPhone = $this->normalizePhone((string) ($verification->phone ?? ''));

        if ($normalizedPhone !== $storedPhone) {
            $telegram->sendMessage($chatId,
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

        $telegram->sendMessage($chatId,
            "✅ Tasdiqlandi!\n\n"
            .$purposeLabel." muvaffaqiyatli tasdiqlandi.\n"
            ."Saytga qaytishingiz mumkin."
        );
    }

    private function handleCallbackQuery(array $callbackQuery, TelegramService $telegram): void
    {
        $callbackId = (string) ($callbackQuery['id'] ?? '');
        $data = (string) ($callbackQuery['data'] ?? '');
        $chatId = (int) ($callbackQuery['chat']['id'] ?? ($callbackQuery['message']['chat']['id'] ?? 0));

        if (str_starts_with($data, 'confirm_password_reset:')) {
            $token = substr($data, strlen('confirm_password_reset:'));
            $verification = TelegramVerification::query()
                ->where('token', $token)
                ->where('purpose', TelegramVerification::PURPOSE_PASSWORD_RESET)
                ->where('status', TelegramVerification::STATUS_PENDING)
                ->first();

            if (! $verification || $verification->isExpired()) {
                $telegram->answerCallbackQuery($callbackId, 'Havola eskirgan.');
                $telegram->sendMessage($chatId, "⚠️ Havola eskirgan.");
                return;
            }

            $verification->update([
                'status' => TelegramVerification::STATUS_VERIFIED,
                'telegram_chat_id' => $chatId,
                'verified_at' => now(),
            ]);

            $telegram->answerCallbackQuery($callbackId, 'Tasdiqlandi!');
            $telegram->sendMessage($chatId, "✅ Parolni tiklash tasdiqlandi!\n\nSaytga qaytib, yangi parol kiriting.");
        }
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
