<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\TelegramService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
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
     *
     * @param string $message Xabar matni
     * @param string $audience Audience turi: all, teachers, donors, students
     * @param int|null $broadcastId telegram_broadcasts jadvalidagi ID (logging uchun)
     * @param int|null $userId Faqat bitta foydalanuvchiga yuborish uchun
     */
    public function __construct(
        private readonly string $message,
        private readonly string $audience = 'all',
        private readonly ?int $broadcastId = null,
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
                $sent = $this->sendToUser($telegram, $user);
                $this->updateBroadcastLog($sent ? 1 : 0, 0, $sent ? 0 : 1);
            }

            return;
        }

        // Audience bo'yicha foydalanuvchilarni filtrlash
        $users = $this->getFilteredUsers();

        $totalRecipients = $users->count();
        $successCount = 0;
        $failCount = 0;
        $skippedCount = 0;

        foreach ($users as $user) {
            try {
                $result = $telegram->sendMessage((int) $user->telegram_chat_id, $this->message);
                if ($result !== null) {
                    $successCount++;
                } else {
                    $failCount++;
                }

                // Telegram API limitiga mos ravishda kutish (30 xabar/soniyada)
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

        // Log ni yangilash
        $this->updateBroadcastLog($totalRecipients, $successCount, $failCount, $skippedCount);

        Log::info('Telegram broadcast completed', [
            'audience' => $this->audience,
            'total' => $totalRecipients,
            'success' => $successCount,
            'failed' => $failCount,
            'message_preview' => mb_substr($this->message, 0, 100),
        ]);
    }

    /**
     * Audience bo'yicha foydalanuvchilarni filtrlash.
     */
    private function getFilteredUsers()
    {
        $query = User::whereNotNull('telegram_chat_id')
            ->where('telegram_chat_id', '>', 0)
            ->select('id', 'name', 'telegram_chat_id', 'role', 'donation_rank');

        return match ($this->audience) {
            'teachers' => $query->where('role', 'teacher')->get(),
            'donors' => $query->whereNotNull('donation_rank')->get(),
            'students' => $query->where('role', 'student')->get(),
            default => $query->get(), // 'all' — hamma
        };
    }

    /**
     * Broadcast log ni yangilash.
     */
    private function updateBroadcastLog(
        int $totalRecipients,
        int $sentCount,
        int $failedCount,
        int $skippedCount = 0,
    ): void {
        if ($this->broadcastId === null) {
            return;
        }

        try {
            DB::table('telegram_broadcasts')
                ->where('id', $this->broadcastId)
                ->update([
                    'total_recipients' => $totalRecipients,
                    'sent_count' => $sentCount,
                    'failed_count' => $failedCount,
                    'skipped_count' => $skippedCount,
                    'status' => ($failedCount === 0) ? 'completed' : 'partial',
                    'updated_at' => now(),
                ]);
        } catch (\Throwable $e) {
            Log::error('Failed to update broadcast log', [
                'broadcast_id' => $this->broadcastId,
                'error' => $e->getMessage(),
            ]);
        }
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
