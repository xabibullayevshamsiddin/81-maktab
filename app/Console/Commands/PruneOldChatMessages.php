<?php

namespace App\Console\Commands;

use App\Models\ChatMessage;
use Illuminate\Console\Command;

class PruneOldChatMessages extends Command
{
    protected $signature = 'chat:prune-messages {--max=50 : Maximum number of messages to keep}';

    protected $description = 'Prune old global chat messages keeping only the latest N messages';

    private const DEFAULT_MAX = 50;

    public function handle(): int
    {
        $max = (int) $this->option('max') ?: self::DEFAULT_MAX;

        $total = ChatMessage::count();

        if ($total <= $max) {
            $this->line("Total: {$total} messages. Nothing to prune.");
            return self::SUCCESS;
        }

        $keepFromId = ChatMessage::query()
            ->orderByDesc('id')
            ->skip($max)
            ->value('id');

        if ($keepFromId) {
            $deleted = ChatMessage::query()->where('id', '<=', $keepFromId)->delete();
            $this->info("Pruned {$deleted} old messages (keeping latest {$max}).");
        }

        return self::SUCCESS;
    }
}
