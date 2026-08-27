<?php

namespace App\Console\Commands;

use App\Services\TelegramService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class TelegramSetCommands extends Command
{
    protected $signature = 'telegram:set-commands';
    protected $description = 'Telegram bot buyruqlarini Menu ga ro\'yxatdan o\'tkazish';

    public function handle(TelegramService $telegram): int
    {
        $commands = TelegramService::defaultCommands();

        $this->line("📋 Bot buyruqlari Telegram Menu ga ro'yxatdan o'tkazilmoqda...");

        try {
            $result = $telegram->setMyCommands($commands);

            if ($result === null) {
                $this->error('❌ Buyruqlarni ro\'yxatdan o\'tkazishda xatolik: setMyCommands() null qaytardi.');
                Log::error('telegram:set-commands: setMyCommands() returned null');
                return self::FAILURE;
            }

            $this->info('✅ Bot buyruqlari muvaffaqiyatli ro\'yxatdan o\'tkazildi!');
            $this->line('');
            foreach ($commands as $cmd) {
                $this->line("  /{$cmd['command']} — {$cmd['description']}");
            }

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('❌ Xato: ' . $e->getMessage());
            Log::error('telegram:set-commands xato: ' . $e->getMessage(), [
                'exception' => $e,
            ]);
            return self::FAILURE;
        }
    }
}
