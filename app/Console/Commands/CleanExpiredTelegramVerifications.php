<?php

namespace App\Console\Commands;

use App\Models\TelegramVerification;
use Illuminate\Console\Command;

class CleanExpiredTelegramVerifications extends Command
{
    protected $signature = 'telegram:clean-expired';

    protected $description = 'Clean expired Telegram verification records';

    public function handle(): int
    {
        $deleted = TelegramVerification::query()
            ->where('expires_at', '<', now()->subHour())
            ->delete();

        $this->info("Deleted {$deleted} expired Telegram verification records.");

        return Command::SUCCESS;
    }
}
