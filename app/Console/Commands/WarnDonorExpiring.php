<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\UserNotification;
use App\Services\TelegramService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WarnDonorExpiring extends Command
{
    protected $signature = 'donors:warn-expiring';
    protected $description = 'Muddati 3 kun yoki 1 kun qolgan donorlarni ogohlantirish';

    /**
     * Qaysi kunlarda ogohlantirish kerak (tugash sanasidan necha kun oldin).
     */
    private const WARNING_DAYS = [3, 1];

    public function handle(): int
    {
        $today = now()->startOfDay();
        $warnedCount = 0;

        foreach (self::WARNING_DAYS as $daysBefore) {
            // Aynan shu kun qolgan donorlarni topish
            // expires_at bugun + $daysBefore kun bo'lishi kerak
            $targetDate = $today->copy()->addDays($daysBefore);
            $nextDay = $targetDate->copy()->addDay();

            $users = User::query()
                ->whereNotNull('donation_rank')
                ->whereNotNull('donation_rank_expires_at')
                ->where('donation_rank_expires_at', '>=', $targetDate)
                ->where('donation_rank_expires_at', '<', $nextDay)
                ->get();

            foreach ($users as $user) {
                // Oldin ogohlantirilganligini tekshirish
                $alreadyWarned = DB::table('donor_warnings')
                    ->where('user_id', $user->id)
                    ->where('days_before', $daysBefore)
                    ->where('expires_at', $user->donation_rank_expires_at)
                    ->exists();

                if ($alreadyWarned) {
                    continue;
                }

                $this->warnUser($user, $daysBefore);
                $warnedCount++;
            }
        }

        if ($warnedCount === 0) {
            $this->info('Bugun ogohlantirish kerak donor yo\'q.');
        } else {
            $this->info("Jami {$warnedCount} ta donor ogohlantirildi.");
        }

        return self::SUCCESS;
    }

    /**
     * Bitta foydalanuvchini ogohlantirish.
     */
    private function warnUser(User $user, int $daysBefore): void
    {
        $rank = $user->donation_rank;
        $expiresAt = $user->donation_rank_expires_at;
        $rankLabel = $this->getRankLabel($rank);
        $daysText = $daysBefore === 1 ? '1 kun' : '3 kun';

        // Matn yaratish
        $message = "⚠️ <b>Donor maqomingiz tugayapti!</b>\n\n"
            . "Hurmatli {$user->name},\n\n"
            . "Sizning <b>{$rankLabel}</b> maqomingiz <b>{$daysText}</b>dan keyin tugaydi.\n"
            . "Tugash sanasi: " . $expiresAt->format('d.m.Y H:i') . "\n\n"
            . "Davom ettirish uchun: " . route('donate') . "\n\n"
            . "Maqomingiz tugagandan keyin barcha imtiyozlar o\'chadi.";

        $inAppNotified = false;
        $telegramNotified = false;

        // 1. Sayt ichidagi bildirishnoma
        try {
            UserNotification::create([
                'user_id' => $user->id,
                'type' => 'warning',
                'title' => "Donor maqomingiz {$daysText}dan keyin tugaydi",
                'body' => "{$rankLabel} maqomingiz {$expiresAt->format('d.m.Y')} da tugaydi. Davom ettirish uchun to'lovni yangilang.",
                'link' => route('donate'),
            ]);
            $inAppNotified = true;
        } catch (\Throwable $e) {
            Log::error('Donor in-app notification failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }

        // 2. Telegram orqali xabar
        if ($user->telegram_chat_id && $user->telegram_chat_id > 0) {
            try {
                $telegram = app(TelegramService::class);
                $result = $telegram->sendMessage((int) $user->telegram_chat_id, $message);
                $telegramNotified = ($result !== null);
            } catch (\Throwable $e) {
                Log::error('Donor Telegram notification failed', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // 3. Bazaga log saqlash
        DB::table('donor_warnings')->insert([
            'user_id' => $user->id,
            'rank' => $rank,
            'days_before' => $daysBefore,
            'notified_in_app' => $inAppNotified,
            'notified_telegram' => $telegramNotified,
            'expires_at' => $expiresAt,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $status = [];
        if ($inAppNotified) $status[] = 'sayt';
        if ($telegramNotified) $status[] = 'telegram';
        $statusStr = implode(' + ', $status) ?: 'xabar yuborilmadi';

        $this->line("  - {$user->name} ({$rank}): {$daysBefore} kun qoldi — {$statusStr}");
    }

    /**
     * Rank nomini o'zbek tilida qaytarish.
     */
    private function getRankLabel(string $rank): string
    {
        return match ($rank) {
            'supporter' => 'Supporter',
            'premium' => 'Premium',
            'vip' => 'VIP',
            default => $rank,
        };
    }
}
