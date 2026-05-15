<?php

namespace App\Services\Telegram;

use App\Models\TelegramRegistrationVerification;
use Illuminate\Support\Facades\Http;

class TelegramBotService
{
    public function isConfigured(): bool
    {
        return $this->botToken() !== '' && $this->botUsername() !== '';
    }

    public function botUsername(): string
    {
        $username = trim((string) config('services.telegram.bot_username', ''));
        $username = preg_replace('#^https?://t\.me/#i', '', $username) ?? $username;
        $username = preg_replace('#^t\.me/#i', '', $username) ?? $username;

        return ltrim(trim($username), '@');
    }

    public function deepLinkUrl(TelegramRegistrationVerification|string $verification): string
    {
        $token = $verification instanceof TelegramRegistrationVerification
            ? $verification->token
            : trim((string) $verification);

        return 'https://t.me/'.$this->botUsername().'?start=verify_'.$token;
    }

    public function requestContact(int|string $chatId, TelegramRegistrationVerification $verification): void
    {
        $this->sendMessage($chatId, implode("\n", [
            "Salom. Ro'yxatdan o'tishni tugatish uchun telefon raqamingizni shu bot ichida yuboring.",
            "Kutilayotgan raqam: {$verification->phone}",
            "Pastdagi tugmani bosib o'zingizning kontaktingizni ulashing.",
        ]), [
            'reply_markup' => [
                'keyboard' => [
                    [[
                        'text' => 'Telefon raqamni yuborish',
                        'request_contact' => true,
                    ]],
                ],
                'resize_keyboard' => true,
                'one_time_keyboard' => true,
            ],
        ]);
    }

    public function sendVerificationConfirmed(int|string $chatId): void
    {
        $this->sendMessage($chatId, implode("\n", [
            "Tasdiq muvaffaqiyatli bajarildi.",
            "Endi saytga qaytib, ro'yxatdan o'tishni yakunlash tugmasini bosing.",
        ]), [
            'reply_markup' => [
                'remove_keyboard' => true,
            ],
        ]);
    }

    public function sendMessage(int|string $chatId, string $text, array $extra = []): void
    {
        if ($this->botToken() === '') {
            return;
        }

        Http::asJson()
            ->connectTimeout(5)
            ->timeout(10)
            ->post($this->apiUrl('sendMessage'), array_merge([
                'chat_id' => $chatId,
                'text' => $text,
            ], $extra))
            ->throw();
    }

    private function apiUrl(string $method): string
    {
        return 'https://api.telegram.org/bot'.$this->botToken().'/'.$method;
    }

    private function botToken(): string
    {
        return trim((string) config('services.telegram.bot_token', ''));
    }
}
