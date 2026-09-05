<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\TelegramService;
use App\Services\UserActivityLogger;
use Illuminate\Console\Command;

class ExpireDonations extends Command
{
    protected $signature = 'donations:expire';
    protected $description = 'Muddati tugagan donorlarni oddiy foydalanuvchiga tushirish';

    public function handle(): int
    {
        $expiredUsers = User::query()
            ->whereNotNull('donation_rank')
            ->where('donation_rank_expires_at', '<', now())
            ->get();

        if ($expiredUsers->isEmpty()) {
            $this->info('Muddati tugagan donor yo\'q.');
            return self::SUCCESS;
        }

        $count = 0;

        foreach ($expiredUsers as $user) {
            $oldRank = $user->donation_rank;

            $user->forceFill([
                'donation_rank' => null,
                'donation_rank_expires_at' => null,
                'username_color' => null,
                'profile_theme' => null,
                'banner_image' => null,
            ])->save();

            UserActivityLogger::log(
                $user,
                'donation_expired',
                "Donor muddati tugadi: {$oldRank}",
                ['old_rank' => $oldRank],
                ['old_rank' => $oldRank, 'expired_at' => now()->toDateTimeString()]
            );

            // Telegram xabar yuborish
            if ($user->telegram_chat_id) {
                try {
                    $config = \App\Models\Donation::configForRank($oldRank);
                    $label = $config["label"] ?? ucfirst($oldRank);
                    $userName = htmlspecialchars($user->buildNameFromParts() ?: $user->name);
                    $text = "⏰ <b>Donor muddati tugadi</b>\n"
                        ."━━━━━━━━━━━━━━━━━━━━\n\n"
                        ."Salom, <b>{$userName}</b>!\n\n"
                        ."Sizning <b>{$label}</b> obunangiz muddati tugadi.\n\n"
                        ."🔐 Imtiyozlar vaqtincha cheklandi.\n"
                        ."💎 Yangi obuna sotib olish uchun saytga kiring.\n\n"
                        ."━━━━━━━━━━━━━━━━━━━━";
                    $telegram = app(TelegramService::class);
                    $telegram->sendMessage((int) $user->telegram_chat_id, $text);
                } catch (\Throwable $e) {
                    // ignore
                }
            }

            $count++;
            $this->line("  - {$user->name} ({$user->email}): {$oldRank} → oddiy foydalanuvchi");
        }

        $this->info("Jami {$count} ta donor tushirildi.");

        return self::SUCCESS;
    }
}
