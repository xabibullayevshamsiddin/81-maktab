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
            // Render.com o'z-o me'yorida RENDER_EXTERNAL_URL beradi
            $url = env('RENDER_EXTERNAL_URL');
        }

        if (empty($url) || str_contains($url, 'localhost')) {
            $appUrl = config('app.url');
            if (! empty($appUrl) && ! str_contains($appUrl, 'localhost')) {
                $url = $appUrl;
            }
        }

        if (empty($url) || str_contains($url, 'localhost')) {
            $this->warn('⚠️ Webhook URL "localhost" ko\'rsatilgan yoki topilmadi!');
            $this->line('Telegram localhost (http://localhost) webhooks qabul qilmaydi.');
            $this->line('Render Environment variables qismida APP_URL=https://eight1-maktab.onrender.com ni kiriting.');
            return 1;
        }

        // Telegram faqat HTTPS larni qabul qiladi
        if (str_starts_with($url, 'http://')) {
            $url = 'https://' . substr($url, 7);
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

            // Bot buyruqlarini ham ro'yxatdan o'tkazish
            $telegram->setMyCommands(TelegramService::defaultCommands());
            $this->info('📋 Bot buyruqlari Menu ga qo\'shildi.');

            return 0;
        }

        $this->error('❌ Webhook ro\'yxatdan o\'tkazishda xatolik yuz berdi.');
        $this->line('Telegram Bot Token va APP_URL ni tekshiring.');
        return 1;
    }
}
