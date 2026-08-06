<?php

namespace App\Console\Commands;

use App\Services\TelegramService;
use App\Services\TelegramUpdateHandler;
use Illuminate\Console\Command;

class TelegramPollUpdates extends Command
{
    protected $signature = 'telegram:poll {--timeout=30} {--once}';
    protected $description = 'Telegram dan getUpdates orqali xabarlarni olish (webhook kerak emas)';

    public function handle(TelegramService $telegram, TelegramUpdateHandler $handler): int
    {
        $timeout = (int) $this->option('timeout');
        $once = $this->option('once');

        $this->info('🔄 Telegram polling boshlandi...');

        do {
            $updates = $telegram->getUpdates($timeout);

            if (empty($updates)) {
                if ($once) {
                    $this->info("Xabar yo'q.");

                    return Command::SUCCESS;
                }
                continue;
            }

            foreach ($updates as $update) {
                $this->processUpdate($update, $handler);
                $telegram->markUpdateProcessed($update['update_id']);
            }

        } while (! $once);

        return Command::SUCCESS;
    }

    private function processUpdate(array $update, TelegramUpdateHandler $handler): void
    {
        if (isset($update['callback_query'])) {
            $handler->handleCallbackQuery($update['callback_query']);

            return;
        }

        if (! isset($update['message'])) {
            return;
        }

        $message = $update['message'];
        $chatId = (int) ($message['chat']['id'] ?? 0);
        $text = trim((string) ($message['text'] ?? ''));

        if (str_starts_with($text, '/start ')) {
            $token = trim(substr($text, 7));
            $handler->handleStart($chatId, $token);

            return;
        }

        if ($text === '/start') {
            $handler->handleStartGeneric($chatId);

            return;
        }

        if (isset($message['contact'])) {
            $handler->handleContact($message);
        }
    }
}
