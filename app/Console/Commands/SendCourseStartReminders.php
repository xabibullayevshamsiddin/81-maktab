<?php

namespace App\Console\Commands;

use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Services\TelegramService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendCourseStartReminders extends Command
{
    protected $signature = 'telegram:course-reminders';
    protected $description = 'Kurs boshlanishidan 1 kun oldin Telegram orqali eslatma yuborish';

    public function handle(): int
    {
        $tomorrow = now()->addDay()->startOfDay();
        $dayAfterTomorrow = now()->addDay()->endOfDay();

        $courses = Course::query()
            ->where('status', Course::STATUS_PUBLISHED)
            ->whereNotNull('start_date')
            ->whereBetween('start_date', [$tomorrow, $dayAfterTomorrow])
            ->get();

        if ($courses->isEmpty()) {
            $this->info('Ertaga boshlanadigan kurslar topilmadi.');
            return Command::SUCCESS;
        }

        $telegram = app(TelegramService::class);
        $sentCount = 0;

        foreach ($courses as $course) {
            $enrolledStudents = CourseEnrollment::query()
                ->where('course_id', $course->id)
                ->where('status', CourseEnrollment::STATUS_APPROVED)
                ->with('user:id,name,first_name,last_name,telegram_chat_id')
                ->get();

            foreach ($enrolledStudents as $enrollment) {
                $user = $enrollment->user;
                if (! $user || ! $user->telegram_chat_id) {
                    continue;
                }

                $userName = $user->buildNameFromParts() ?: $user->name ?: 'O\'quvchi';
                $courseTitle = $course->title;
                $startDate = $course->start_date->format('d.m.Y');

                $text = "📚 <b>Kurs boshlanishi haqida eslatma</b>\n\n"
                    ."Salom, <b>".htmlspecialchars($userName)."</b>!\n\n"
                    ."📋 Kurs: <b>".htmlspecialchars($courseTitle)."</b>\n"
                    ."📅 Boshlanish sanasi: <b>{$startDate}</b>\n\n"
                    ."⏰ Kurs ertaga boshlanadi. Tayyor bo'ling!\n"
                    ."🔗 Kursga kirish: ".route('courses.show', $course->slug ?? $course->id);

                try {
                    $telegram->sendMessage((int) $user->telegram_chat_id, $text);
                    $sentCount++;
                } catch (\Throwable $e) {
                    Log::error('Telegram course reminder failed', [
                        'course_id' => $course->id,
                        'user_id' => $user->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }

        $this->info("{$sentCount} ta eslatma yuborildi. {$courses->count()} ta kurs uchun.");
        return Command::SUCCESS;
    }
}
