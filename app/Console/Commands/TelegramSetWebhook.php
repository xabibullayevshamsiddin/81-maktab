<?php

namespace App\Console\Commands;

use App\Services\TelegramService;
use Illuminate\Console\Command;

class TelegramSetWebhook extends Command
{
    protected $signature = 'telegram:set-webhook {--url=}';
    protected $description = 'Telegram webhook URL ni ro\'yxatdan o\'tkazish';

    public function handle(TelegramService $telegram): int
    {
        $url = $this->option('url');
        
        if (empty($url)) {
            $url = config('app.url');
        }

        if (empty($url)) {
            $this->error('APP_URL environment variable topilmadi!');
            $this->line('Iltimos, --url parametrini kiriting yoki APP_URL ni o\'rnating.');
            return 1;
        }

        $secret = config('telegram.webhook_secret', '');
        
        if (empty($secret)) {
            $this->error('TELEGRAM_WEBHOOK_SECRET environment variable topilmadi!');
            return 1;
        }

        $webhookUrl = rtrim($url, '/') . '/telegram/webhook/' . $secret;

        $this->line("Webhook URL: {$webhookUrl}");
        $this->line("Webhook ro'yxatdan o'tkazilmoqda...");

        $result = $telegram->setWebhook($webhookUrl, $secret);

        if ($result !== null) {
            $this->info('✅ Webhook muvaffaqiyatli ro\'yxatdan o\'tkazildi!');
            return 0;
        }

        $this->error('❌ Webhook ro\'yxatdan o\'tkazishda xatolik yuz berdi.');
        $this->line('Telegram Bot Token va APP_URL ni tekshiring.');
        return 1;
    }
}
