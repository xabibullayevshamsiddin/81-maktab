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
            // 1. Eski keshni tozalash
            $telegram->deleteMyCommands(['type' => 'default']);
            $telegram->deleteMyCommands(['type' => 'all_private_chats']);

            // 2. Default scope (barcha chatlar)
            $resDefault = $telegram->setMyCommands($commands, ['type' => 'default']);
            if ($resDefault === null) {
                $this->error('❌ Default scope uchun buyruqlarni ro\'yxatdan o\'tkazishda xatolik yuz berdi.');
                Log::error('telegram:set-commands: setMyCommands(default) returned null');
                return self::FAILURE;
            }

            // 3. All private chats scope (shaxsiy chatlar uchun)
            $resPrivate = $telegram->setMyCommands($commands, ['type' => 'all_private_chats']);
            if ($resPrivate === null) {
                $this->warn('⚠️ all_private_chats scope uchun setMyCommands null qaytardi (default ishlatiladi).');
            }

            // 4. Menu tugmasini commands turiga o'rnatish
            $telegram->setChatMenuButton(['type' => 'commands']);

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
