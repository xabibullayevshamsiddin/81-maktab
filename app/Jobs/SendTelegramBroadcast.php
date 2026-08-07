<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\TelegramService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendTelegramBroadcast implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     */
    public int $backoff = 30;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private readonly string $message,
        private readonly ?int $userId = null,
    ) {
        $this->onQueue('broadcasts');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $telegram = app(TelegramService::class);

        // Agar ma'lum bir foydalanuvchi uchun bo'lsa
        if ($this->userId !== null) {
            $user = User::find($this->userId);
            if ($user && $user->telegram_chat_id) {
                $this->sendToUser($telegram, $user);
            }

            return;
        }

        // Barcha Telegram ga ulangan foydalanuvchilarga yuborish
        $users = User::whereNotNull('telegram_chat_id')
            ->where('telegram_chat_id', '>', 0)
            ->select('id', 'name', 'telegram_chat_id')
            ->get();

        $successCount = 0;
        $failCount = 0;

        foreach ($users as $user) {
            try {
                $result = $telegram->sendMessage((int) $user->telegram_chat_id, $this->message);
                if ($result !== null) {
                    $successCount++;
                } else {
                    $failCount++;
                }

                // Telegram API limitiga mos ravishda kutish (30消息/soniyada)
                usleep(350000); // 0.35 soniya
            } catch (\Throwable $e) {
                $failCount++;
                Log::error('Telegram broadcast failed', [
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::info('Telegram broadcast completed', [
            'total' => $successCount + $failCount,
            'success' => $successCount,
            'failed' => $failCount,
            'message_preview' => mb_substr($this->message, 0, 100),
        ]);
    }

    /**
     * Bitta foydalanuvchiga xabar yuborish.
     */
    private function sendToUser(TelegramService $telegram, User $user): bool
    {
        try {
            $result = $telegram->sendMessage((int) $user->telegram_chat_id, $this->message);
            return $result !== null;
        } catch (\Throwable $e) {
            Log::error('Telegram broadcast to single user failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
}
