<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    private string $token;
    private string $baseUrl;

    public function __construct()
    {
        $this->token = (string) config('telegram.bot_token', '');
        $this->baseUrl = (string) config('telegram.api_base', 'https://api.telegram.org');
    }

    public static function defaultCommands(): array
    {
        return [
            ['command' => 'start', 'description' => 'Tasdiqlashni boshlash'],
            ['command' => 'help', 'description' => 'Yordam va buyruqlar'],
            ['command' => 'natijalarim', 'description' => 'Imtihon natijalarim'],
            ['command' => 'profil', 'description' => 'Profil ma\'lumotlarim'],
        ];
    }

    /**
     | Oddiy xabar yuborish.
     */
    public function sendMessage(int $chatId, string $text, ?array $replyMarkup = null): ?array
    {
        $payload = [
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => 'HTML',
        ];

        if ($replyMarkup !== null) {
            $payload['reply_markup'] = $replyMarkup;
        }

        return $this->callApi('sendMessage', $payload);
    }

    /**
     | Telefon raqamni ulashish tugmali klaviatura yuborish.
     */
    public function requestContact(int $chatId, string $text): ?array
    {
        $replyMarkup = [
            'keyboard' => [
                [
                    [
                        'text' => '📱 Telefon raqamni ulashish',
                        'request_contact' => true,
                    ],
                ],
            ],
            'resize_keyboard' => true,
            'one_time_keyboard' => true,
        ];

        return $this->sendMessage($chatId, $text, $replyMarkup);
    }

    /**
     | Inline tugmalar bilan xabar yuborish (tasdiqlash uchun).
     */
    public function sendInlineConfirm(int $chatId, string $text, string $confirmData, string $cancelData): ?array
    {
        $replyMarkup = [
            'inline_keyboard' => [
                [
                    ['text' => '✅ Tasdiqlayman', 'callback_data' => $confirmData],
                    ['text' => '❌ Bekor qilish', 'callback_data' => $cancelData],
                ],
            ],
        ];

        return $this->sendMessage($chatId, $text, $replyMarkup);
    }

    /**
     | Callback queryga javob berish (inline tugmalar uchun).
     */
    public function answerCallbackQuery(string $callbackQueryId, string $text = ''): ?array
    {
        return $this->callApi('answerCallbackQuery', [
            'callback_query_id' => $callbackQueryId,
            'text' => $text,
            'show_alert' => false,
        ]);
    }

    /**
     | Xabarni tahrirlash (inline tugmalarni o'chirish uchun).
     */
    public function editMessageText(int $chatId, int $messageId, string $text, ?array $replyMarkup = null): ?array
    {
        $payload = [
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'text' => $text,
            'parse_mode' => 'HTML',
        ];

        if ($replyMarkup !== null) {
            $payload['reply_markup'] = $replyMarkup;
        }

        return $this->callApi('editMessageText', $payload);
    }

    /**
     | Inline tugmalarni o'chirish (xabarni tahrirlamasdan).
     */
    public function editMessageReplyMarkup(int $chatId, int $messageId, ?array $replyMarkup = null): ?array
    {
        $payload = [
            'chat_id' => $chatId,
            'message_id' => $messageId,
        ];

        if ($replyMarkup !== null) {
            $payload['reply_markup'] = $replyMarkup;
        }

        return $this->callApi('editMessageReplyMarkup', $payload);
    }

    /**
     | Telegram'ga webhook manzilini ro'yxatdan o'tkazish.
     */
    public function setWebhook(string $url, string $secretToken): ?array
    {
        return $this->callApi('setWebhook', [
            'url' => $url,
            'secret_token' => $secretToken,
            'allowed_updates' => ['message', 'callback_query'],
        ]);
    }

    /**
     | Bot buyruqlarini Telegram Menu ga ro'yxatdan o'tkazish.
     */
    public function setMyCommands(array $commands): ?array
    {
        return $this->callApi('setMyCommands', [
            'commands' => $commands,
        ]);
    }

    /**
     | Webhookni o'chirish.
     */
    public function deleteWebhook(): ?array
    {
        return $this->callApi('deleteWebhook');
    }

    /**
     | Bot ma'lumotlarini olish (test uchun).
     */
    public function getMe(): ?array
    {
        return $this->callApi('getMe');
    }

    /**
     | getUpdates — webhook o'rniga polling uchun.
     */
    public function getUpdates(int $timeout = 30): array
    {
        $result = $this->callApi('getUpdates', [
            'timeout' => $timeout,
        ]);

        return is_array($result) ? $result : [];
    }

    /**
     | Update ni qayta ishlash tugallanganini belgilash.
     */
    public function markUpdateProcessed(int $updateId): ?array
    {
        return $this->callApi('getUpdates', [
            'offset' => $updateId + 1,
            'limit' => 1,
        ]);
    }

    /**
     | Telegram API ga so'rov yuborish.
     */
    private function callApi(string $method, array $data = []): ?array
    {
        if ($this->token === '') {
            Log::error('Telegram Bot Token is not configured.');

            return null;
        }

        $url = rtrim($this->baseUrl, '/').'/bot'.$this->token.'/'.$method;

        try {
            $response = Http::timeout(10)->post($url, $data);

            if ($response->successful()) {
                $body = $response->json();

                if (! ($body['ok'] ?? false)) {
                    Log::warning('Telegram API returned ok=false', [
                        'method' => $method,
                        'description' => $body['description'] ?? 'Unknown error',
                    ]);

                    return null;
                }

                $result = $body['result'] ?? null;
                // Telegram ba'zi metodlarda (masalan, answerCallbackQuery) true qaytaradi
                return is_array($result) ? $result : ($result === true ? [] : null);
            }

            Log::error('Telegram API HTTP error', [
                'method' => $method,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;
        } catch (\Throwable $e) {
            Log::error('Telegram API exception', [
                'method' => $method,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }
}
