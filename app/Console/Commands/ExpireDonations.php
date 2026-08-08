<?php

namespace App\Console\Commands;

use App\Models\User;
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

            $user->update([
                'donation_rank' => null,
                'donation_rank_expires_at' => null,
                'username_color' => null,
                'profile_theme' => null,
                'banner_image' => null,
            ]);

            UserActivityLogger::log(
                $user,
                'donation_expired',
                "Donor muddati tugadi: {$oldRank}",
                ['old_rank' => $oldRank],
                ['old_rank' => $oldRank, 'expired_at' => now()->toDateTimeString()]
            );

            $count++;
            $this->line("  - {$user->name} ({$user->email}): {$oldRank} → oddiy foydalanuvchi");
        }

        $this->info("Jami {$count} ta donor tushirildi.");

        return self::SUCCESS;
    }
}
