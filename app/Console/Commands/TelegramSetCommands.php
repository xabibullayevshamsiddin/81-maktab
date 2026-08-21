<?php

namespace App\Console\Commands;

use App\Services\TelegramService;
use Illuminate\Console\Command;

class TelegramSetCommands extends Command
{
    protected $signature = 'telegram:set-commands';
    protected $description = 'Telegram bot buyruqlarini Menu ga ro\'yxatdan o\'tkazish';

    public function handle(TelegramService $telegram): int
    {
        $commands = TelegramService::defaultCommands();

        $this->line("📋 Bot buyruqlari Telegram Menu ga ro'yxatdan o'tkazilmoqda...");

        $result = $telegram->setMyCommands($commands);

        if ($result !== null) {
            $this->info('✅ Bot buyruqlari muvaffaqiyatli ro\'yxatdan o\'tkazildi!');
            $this->line('');
            foreach ($commands as $cmd) {
                $this->line("  /{$cmd['command']} — {$cmd['description']}");
            }

            return 0;
        }

        $this->error('❌ Buyruqlarni ro\'yxatdan o\'tkazishda xatolik yuz berdi.');

        return 1;
    }
}
