<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\TelegramService;
use Illuminate\Console\Command;

class UnblockExpiredUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:unblock-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Avtomatik ravishda muddati tugagan bloklangan foydalanuvchilarni ochish va Telegram xabar yuborish';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $blockedUsers = User::where('is_blocked', true)
            ->whereNotNull('blocked_until')
            ->where('blocked_until', '<=', now())
            ->get();

        if ($blockedUsers->isEmpty()) {
            $this->info('Muddati tugagan bloklangan foydalanuvchilar topilmadi.');
            return self::SUCCESS;
        }

        $telegram = app(TelegramService::class);
        $count = 0;

        foreach ($blockedUsers as $user) {
            $user->update([
                'is_blocked' => false,
                'is_active' => true,
                'blocked_until' => null,
                'blocked_reason' => null,
                'blocked_by' => null,
            ]);
            $count++;
            $this->line("  ✅ #{$user->id} {$user->name} — blok ochildi");

            // Telegram'ga xabar yuborish
            if ($user->telegram_chat_id) {
                $this->sendUnblockNotification($telegram, $user);
            }
        }

        $this->info("Jami {$count} ta foydalanuvchining bloki ochildi.");
        return self::SUCCESS;
    }

    /**
     * Foydalanuvchiga Telegram orqali blok ochilgani haqida xabar yuborish.
     */
    private function sendUnblockNotification(TelegramService $telegram, User $user): void
    {
        $text = "✅ <b>Hisobingiz blokdan ochildi!</b>"
            ."\n\n"
            ."Hurmatli {$user->name}, sizning vaqtincha bloklangan hisobingiz muddati tugadi va avtomatik ravishda ochildi."
            ."\n\n"
            ."🌐 Endi saytdan to'liq foydalanishingiz mumkin."
            ."\n\n"
            ."⏰ ochilgan vaqt: ".now()->format('d.m.Y H:i');

        $telegram->sendMessage((int) $user->telegram_chat_id, $text);
    }
}
