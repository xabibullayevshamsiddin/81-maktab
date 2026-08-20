<?php

namespace App\Console\Commands;

use App\Models\ContactMessage;
use App\Models\CourseEnrollment;
use App\Models\Result;
use App\Models\User;
use App\Models\UserActivity;
use App\Services\TelegramService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendAdminStatsReport extends Command
{
    protected $signature = 'stats:send-report {--period=daily : daily yoki weekly}';

    protected $description = 'Kunlik yoki haftalik statistika hisobotini adminlarga Telegram orqali yuborish';

    public function handle(): int
    {
        $period = $this->option('period');
        $since = $period === 'weekly' ? now()->subWeek() : now()->subDay();

        $periodLabel = $period === 'weekly' ? 'Haftalik' : 'Kunlik';
        $dateRange = $since->format('d.m.Y') . ' — ' . now()->format('d.m.Y');

        // ── Statistikani yig'ish ──

        // Yangi ro'yxatdan o'tganlar
        $newUsers = User::where('created_at', '>=', $since)->count();

        // Faol foydalanuvchilar (UserActivity orqali)
        $activeUsers = UserActivity::where('occurred_at', '>=', $since)
            ->distinct('user_id')
            ->count('user_id');

        // Yangi kurslarga yozilishlar
        $newEnrollments = CourseEnrollment::where('created_at', '>=', $since)->count();

        // Yangi imtihon topshirishlar
        $newExamSubmissions = Result::where('submitted_at', '>=', $since)
            ->where('status', 'submitted')
            ->count();

        // Yangi aloqa xabarlari
        $newContactMessages = ContactMessage::where('created_at', '>=', $since)->count();

        // ── Matnni yaratish ──

        $text = "📊 <b>{$periodLabel} hisobot</b>\n"
            . "━━━━━━━━━━━━━━━━━━━━\n\n"
            . "📅 <b>Sanalar:</b> {$dateRange}\n\n"
            . "👤 Yangi foydalanuvchilar: <b>{$newUsers}</b>\n"
            . "⚡ Faol foydalanuvchilar: <b>{$activeUsers}</b>\n"
            . "📚 Yangi kursga yozilishlar: <b>{$newEnrollments}</b>\n"
            . "📝 Yangi imtihon topshirishlar: <b>{$newExamSubmissions}</b>\n"
            . "📬 Yangi aloqa xabarlari: <b>{$newContactMessages}</b>\n\n"
            . "━━━━━━━━━━━━━━━━━━━━";

        // ── Adminlarni topish va xabar yuborish ──

        $admins = User::whereHas('roleRelation', fn ($q) => $q->whereIn('name', ['admin', 'super_admin']))
            ->whereNotNull('telegram_chat_id')
            ->get();

        $sentCount = 0;

        foreach ($admins as $admin) {
            try {
                $telegram = app(TelegramService::class);
                $telegram->sendMessage((int) $admin->telegram_chat_id, $text);
                $sentCount++;
            } catch (\Throwable $e) {
                Log::error('Telegram admin stats report failed', [
                    'admin_id' => $admin->id,
                    'period' => $period,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->info("{$periodLabel} hisobot {$sentCount} ta adminga yuborildi.");

        return self::SUCCESS;
    }
}
