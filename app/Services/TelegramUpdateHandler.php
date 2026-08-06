<?php

namespace App\Services;

use App\Models\TelegramVerification;

/**
 * Telegram xabarlarni qayta ishlash logikasi.
 * TelegramWebhookController va TelegramPollUpdates ikkalasi ham shu klassni ishlatadi.
 */
class TelegramUpdateHandler
{
    public function __construct(
        private TelegramService $telegram,
    ) {}

    /**
     | /start <token> — foydalanuvchi botni ochganda.
     */
    public function handleStart(int $chatId, string $token): void
    {
        $verification = TelegramVerification::query()
            ->where('token', $token)
            ->where('status', TelegramVerification::STATUS_PENDING)
            ->first();

        if (! $verification || $verification->isExpired()) {
            if ($verification) {
                $verification->update(['status' => TelegramVerification::STATUS_EXPIRED]);
            }

            $this->telegram->sendMessage($chatId,
                "⚠️ Havola eskirgan yoki noto'g'ri. Saytga qaytib, qayta urinib ko'ring."
            );

            return;
        }

        $this->telegram->requestContact($chatId,
            "📱 Telefon raqamni ulashish\n\n"
            .'\"Telefon raqamni ulashish\" tugmasini bosing. '
            .('register' === $verification->purpose
                ? "Saytda kiritgan raqamingizni ulang."
                : "Hisobingizga bog'langan raqamni ulang.")
        );
    }

    /**
     | /start (token bilan) — oddiy /start buyrug'i.
     */
    public function handleStartGeneric(int $chatId): void
    {
        $this->telegram->sendMessage($chatId,
            "Assalomu alaykum!\n\nSaytda ro'yxatdan o'tish yoki kirish uchun havolani bosing."
        );
    }

    /**
     | Kontakt ulashganda — raqamni tekshirish.
     */
    public function handleContact(array $message): void
    {
        $chatId = (int) ($message['chat']['id'] ?? 0);
        $fromId = (int) ($message['from']['id'] ?? 0);
        $contactUserId = (int) ($message['contact']['user_id'] ?? 0);
        $phoneNumber = (string) ($message['contact']['phone_number'] ?? '');

        if ($contactUserId !== $fromId) {
            $this->telegram->sendMessage($chatId,
                "❌ Xatolik: Iltimos, faqat o'z telefon raqamingizni ulang."
            );

            return;
        }

        if ($phoneNumber === '') {
            $this->telegram->sendMessage($chatId,
                "❌ Telefon raqam olinmadi. Qayta urinib ko'ring."
            );

            return;
        }

        $normalizedPhone = $this->normalizePhone($phoneNumber);

        // Shu chat orqali oxirgi pending yozuvni topish
        $verification = TelegramVerification::query()
            ->where('status', TelegramVerification::STATUS_PENDING)
            ->where('expires_at', '>', now())
            ->latest('id')
            ->first();

        if (! $verification) {
            $this->telegram->sendMessage($chatId,
                "⚠️ Tasdiqlash so'rovi topilmadi. Saytga qaytib, qayta urinib ko'ring."
            );

            return;
        }

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
     | Callback query (inline tugma bosilganda).
     */
    public function handleCallbackQuery(array $callbackQuery): void
    {
        $callbackId = (string) ($callbackQuery['id'] ?? '');
        $data = (string) ($callbackQuery['data'] ?? '');
        $chatId = (int) ($callbackQuery['chat']['id'] ?? ($callbackQuery['message']['chat']['id'] ?? 0));

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
            $this->telegram->sendMessage($chatId,
                "⚠️ Havola eskirgan. Saytga qaytib, qayta urinib ko'ring."
            );

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
     | Telefon raqamni normallashtirish.
     */
    public function normalizePhone(string $phone): string
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
