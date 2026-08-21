<?php

namespace App\Services;

use App\Models\Result;
use App\Models\TelegramVerification;
use App\Models\User;
use Illuminate\Support\Facades\Log;

/**
 * Telegram xabarlarni qayta ishlash logikasi.
 * TelegramWebhookController va TelegramPollUpdates ikkalasi ham shu klassni ishlatadi.
 * 
 * // ═══════════════════════════════════════════════════════════════
 * // TELEGRAM XABAR FORMATI — ESLOMA
 * // ═══════════════════════════════════════════════════════════════
 * // Yashirin kodlar (parollar) uchun:
 * //   <tg-spoiler>matn</tg-spoiler>  — bosganda ochiladi
 * // 
 * // Tasdiqlash kodlari uchun:
 * //   <code>123456</code>  — monospace formatda ko'rinadi
 * // 
 * // Qalin harflar:
 * //   <b>matn</b>  — qalin ko'rinadi
 * // ═══════════════════════════════════════════════════════════════
 */
class TelegramUpdateHandler
{
    public function __construct(
        private TelegramService $telegram,
    ) {}

    /**
     | /start <token> — foydalanuvchi botni ochganda.
     */
    public function handleStart(int $chatId, string $token): void
    {
        $verification = TelegramVerification::query()
            ->where('token', $token)
            ->where('status', TelegramVerification::STATUS_PENDING)
            ->first();

        if (! $verification || $verification->isExpired()) {
            if ($verification) {
                $verification->update(['status' => TelegramVerification::STATUS_EXPIRED]);
            }

            $this->telegram->sendMessage($chatId,
                "⚠️ Havola eskirgan yoki noto'g'ri. Saytga qaytib, qayta urinib ko'ring."
            );

            return;
        }

        $this->telegram->requestContact($chatId,
            "📱 Telefon raqamni ulashish\n\n"
            .'\"Telefon raqamni ulashish\" tugmasini bosing. '
            .('register' === $verification->purpose
                ? "Saytda kiritgan raqamingizni ulang."
                : "Hisobingizga bog'langan raqamni ulang.")
        );
    }

    /**
     | /start (token bilan) — oddiy /start buyrug'i.
     */
    public function handleStartGeneric(int $chatId): void
    {
        $this->telegram->sendMessage($chatId,
            "Assalomu alaykum!\n\nSaytda ro'yxatdan o'tish yoki kirish uchun havolani bosing.",
            ['remove_keyboard' => true]
        );
    }

    /**
     | Kontakt ulashganda — raqamni tekshirish.
     */
    public function handleContact(array $message): void
    {
        $chatId = (int) ($message['chat']['id'] ?? 0);
        $fromId = (int) ($message['from']['id'] ?? 0);
        $contactUserId = (int) ($message['contact']['user_id'] ?? 0);
        $phoneNumber = (string) ($message['contact']['phone_number'] ?? '');

        if ($contactUserId !== $fromId) {
            $this->telegram->sendMessage($chatId,
                "❌ Xatolik: Iltimos, faqat o'z telefon raqamingizni ulang."
            );

            return;
        }

        if ($phoneNumber === '') {
            $this->telegram->sendMessage($chatId,
                "❌ Telefon raqam olinmadi. Qayta urinib ko'ring."
            );

            return;
        }

        $normalizedPhone = $this->normalizePhone($phoneNumber);

        // Ulangan raqamga mos pending yozuvni topish (ikki odam bir vaqtda tasdiqlasa, xato olinmasin)
        $verification = TelegramVerification::query()
            ->where('status', TelegramVerification::STATUS_PENDING)
            ->where('expires_at', '>', now())
            ->where('phone', $normalizedPhone)
            ->latest('id')
            ->first();

        if (! $verification) {
            $this->telegram->sendMessage($chatId,
                "⚠️ Tasdiqlash so'rovi topilmadi. Saytga qaytib, qayta urinib ko'ring."
            );

            return;
        }

        $storedPhone = $this->normalizePhone((string) ($verification->phone ?? ''));

        if ($normalizedPhone !== $storedPhone) {
            $this->telegram->sendMessage($chatId,
                "❌ Bu raqam saytda kiritgan raqamingiz bilan mos emas.\n\n"
                ."Kiritilgan: ...".substr($storedPhone, -4)."\n"
                ."Ulangan: ...".substr($normalizedPhone, -4)."\n\n"
                ."Iltimos, to'g'ri raqamni ulang."
            );

            return;
        }

        // Tasdiqlash
        $verification->update([
            'status' => TelegramVerification::STATUS_VERIFIED,
            'telegram_chat_id' => $chatId,
            'verified_at' => now(),
        ]);

        $purposeLabel = match ($verification->purpose) {
            'register' => "Ro'yxatdan o'tish",
            'login' => 'Kirish',
            'password_reset' => 'Parolni tiklash',
            default => 'Tasdiqlash',
        };

        $this->telegram->sendMessage($chatId,
            "✅ Tasdiqlandi!\n\n"
            .$purposeLabel." muvaffaqiyatli tasdiqlandi.\n"
            ."Saytga qaytishingiz mumkin.",
            ['remove_keyboard' => true]
        );
    }

    /**
     | /natijalarim — foydalanuvchining imtihon natijalarini ko'rsatish.
     */
    public function handleResultsCommand(int $chatId): void
    {
        try {
            $user = User::where('telegram_chat_id', $chatId)->first();

            if (! $user) {
                $this->telegram->sendMessage($chatId,
                    "⚠️ Hisobingiz ulanmagan.\n\n"
                    ."Saytda profil sozlamalaridan Telegram'ni ulang."
                );

                return;
            }

            $results = Result::where('user_id', $user->id)
                ->where('status', 'submitted')
                ->latest('submitted_at')
                ->take(5)
                ->with('exam:id,title')
                ->get();

            if ($results->isEmpty()) {
                $this->telegram->sendMessage($chatId,
                    "📭 Hali imtihon natijalaringiz yo'q."
                );

                return;
            }

            $lines = [];
            foreach ($results as $result) {
                $examTitle = $result->exam?->title ?? 'Noma\'lum imtihon';
                $score = $result->points_earned ?? 0;
                $maxScore = $result->points_max ?? 0;
                $passed = $result->passed;
                $correctCount = $result->score ?? 0;
                $totalQuestions = $result->total_questions ?? 0;
                $statusEmoji = $passed === true ? '✅' : ($passed === false ? '❌' : '⏳');
                $statusText = $passed === true ? "O'tdi" : ($passed === false ? 'Yiqildi' : 'Kutilmoqda');
                $date = $result->submitted_at?->format('d.m.Y H:i') ?? '';

                $lines[] = "{$statusEmoji} <b>".htmlspecialchars($examTitle)."</b>\n"
                    ."   📈 Ball: <b>{$score}</b>/{$maxScore}  ✅ To'g'ri: <b>{$correctCount}</b>/{$totalQuestions}\n"
                    ."   📅 {$date}";
            }

            $text = "📊 <b>So'nggi imtihon natijalaringiz</b>\n"
                ."━━━━━━━━━━━━━━━━━━━━\n\n"
                .implode("\n\n", $lines);

            $this->telegram->sendMessage($chatId, $text);
        } catch (\Throwable $e) {
            Log::error('Telegram handleResultsCommand failed', [
                'chat_id' => $chatId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     | /profilim — foydalanuvchi profilini ko'rsatish.
     */
    public function handleProfileCommand(int $chatId): void
    {
        try {
            $user = User::where('telegram_chat_id', $chatId)->first();

            if (! $user) {
                $this->telegram->sendMessage($chatId,
                    "⚠️ Hisobingiz ulanmagan.\n\n"
                    ."Saytda profil sozlamalaridan Telegram'ni ulang."
                );

                return;
            }

            $user->load('roleRelation');

            $roleLabel = $user->role_label;

            // Donor holati
            if ($user->isDonor()) {
                $donorRank = $user->donorRankLabel() ?? $user->donation_rank;
                $donorExpiry = $user->donation_rank_expires_at?->format('d.m.Y') ?? '';
                $donorLine = "💎 <b>Donor:</b> {$donorRank}";
                if ($donorExpiry) {
                    $donorLine .= " (muddati: {$donorExpiry})";
                }
            } else {
                $donorLine = "💎 <b>Donor:</b> Donor emas";
            }

            // Kurslar
            $enrolledCount = $user->courseEnrollments()->count();
            $enrolledCourses = $user->courseEnrollments()
                ->with('course:id,title')
                ->get()
                ->pluck('course.title')
                ->filter()
                ->values();

            $coursesLine = "📚 <b>Kurslar:</b> {$enrolledCount} ta";
            if ($enrolledCourses->isNotEmpty()) {
                $coursesLine .= "\n" . $enrolledCourses->map(fn ($t) => "   • ".htmlspecialchars($t))->implode("\n");
            }

            // Teacher bo'lsa — yaratgan kurslari
            $teacherLine = '';
            if ($user->isTeacher()) {
                $createdCount = $user->createdCourses()->count();
                $teacherLine = "\n\n👨‍🏫 <b>O'qituvchi:</b> {$createdCount} ta kurs yaratgan";
            }

            $text = "👤 <b>Profil</b>\n"
                ."━━━━━━━━━━━━━━━━━━━━\n\n"
                ."📝 <b>Ism:</b> ".htmlspecialchars($user->name)."\n"
                ."🛡 <b>Rol:</b> {$roleLabel}\n\n"
                ."{$donorLine}\n\n"
                ."{$coursesLine}"
                ."{$teacherLine}";

            $this->telegram->sendMessage($chatId, $text);
        } catch (\Throwable $e) {
            Log::error('Telegram handleProfileCommand failed', [
                'chat_id' => $chatId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     | /help — mavjud buyruqlar ro'yxati.
     */
    public function handleHelpCommand(int $chatId): void
    {
        try {
            $text = "ℹ️ <b>Mavjud buyruqlar</b>\n"
                ."━━━━━━━━━━━━━━━━━━━━\n\n"
                ."/start — Botni ishga tushirish\n"
                ."/help — Shu xabarni ko'rsatish\n"
                ."/natijalarim — Imtihon natijalaringiz\n"
                ."/profilim — Profil ma'lumotlaringiz\n\n"
                ."━━━━━━━━━━━━━━━━━━━━\n\n"
                ."💡 Buyruqni guruh chatida yuborishingiz mumkin.";

            $this->telegram->sendMessage($chatId, $text);
        } catch (\Throwable $e) {
            Log::error('Telegram handleHelpCommand failed', [
                'chat_id' => $chatId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     | Callback query (inline tugma bosilganda).
     */
    public function handleCallbackQuery(array $callbackQuery): void
    {
        $callbackId = (string) ($callbackQuery['id'] ?? '');
        $data = (string) ($callbackQuery['data'] ?? '');
        $chatId = (int) ($callbackQuery['message']['chat']['id'] ?? $callbackQuery['chat']['id'] ?? 0);

        if (str_starts_with($data, 'confirm_password_reset:')) {
            $token = substr($data, strlen('confirm_password_reset:'));
            $this->handlePasswordResetConfirm($chatId, $token, $callbackId, $callbackQuery);
        } elseif (str_starts_with($data, 'confirm_email_change:')) {
            $token = substr($data, strlen('confirm_email_change:'));
            $this->handleEmailChangeConfirm($chatId, $token, $callbackId, $callbackQuery);
        } elseif (str_starts_with($data, 'cancel_email_change:')) {
            $token = substr($data, strlen('cancel_email_change:'));
            $this->handleEmailChangeCancel($chatId, $token, $callbackId, $callbackQuery);
        } elseif (str_starts_with($data, 'confirm_phone_change:')) {
            $token = substr($data, strlen('confirm_phone_change:'));
            $this->handlePhoneChangeConfirm($chatId, $token, $callbackId, $callbackQuery);
        } elseif (str_starts_with($data, 'cancel_phone_change:')) {
            $token = substr($data, strlen('cancel_phone_change:'));
            $this->handlePhoneChangeCancel($chatId, $token, $callbackId, $callbackQuery);
        } elseif (str_starts_with($data, 'approve_course_open:')) {
            $userId = (int) substr($data, strlen('approve_course_open:'));
            $this->handleCourseOpenDecision($chatId, $userId, true, $callbackId, $callbackQuery);
        } elseif (str_starts_with($data, 'reject_course_open:')) {
            $userId = (int) substr($data, strlen('reject_course_open:'));
            $this->handleCourseOpenDecision($chatId, $userId, false, $callbackId, $callbackQuery);
        } elseif (str_starts_with($data, 'approve_enrollment:')) {
            $enrollmentId = (int) substr($data, strlen('approve_enrollment:'));
            $this->handleEnrollmentDecision($chatId, $enrollmentId, true, $callbackId, $callbackQuery);
        } elseif (str_starts_with($data, 'reject_enrollment:')) {
            $enrollmentId = (int) substr($data, strlen('reject_enrollment:'));
            $this->handleEnrollmentDecision($chatId, $enrollmentId, false, $callbackId, $callbackQuery);
        }
    }

    /**
     | Parolni tiklashni Telegram orqali tasdiqlash.
     */
    private function handlePasswordResetConfirm(int $chatId, string $token, string $callbackId): void
    {
        $verification = TelegramVerification::query()
            ->where('token', $token)
            ->where('purpose', TelegramVerification::PURPOSE_PASSWORD_RESET)
            ->where('status', TelegramVerification::STATUS_PENDING)
            ->first();

        if (! $verification || $verification->isExpired()) {
            $this->telegram->answerCallbackQuery($callbackId, 'Havola eskirgan.');
            $this->telegram->sendMessage($chatId,
                "⚠️ Havola eskirgan. Saytga qaytib, qayta urinib ko'ring."
            );

            return;
        }

        $verification->update([
            'status' => TelegramVerification::STATUS_VERIFIED,
            'telegram_chat_id' => $chatId,
            'verified_at' => now(),
        ]);

        $this->telegram->answerCallbackQuery($callbackId, 'Tasdiqlandi!');
        $this->telegram->sendMessage($chatId,
            "✅ Parolni tiklash tasdiqlandi!\n\nSaytga qaytib, yangi parol kiriting."
        );
    }

    /**
     | Email o'zgartirishni Telegram orqali tasdiqlash.
     */
    private function handleEmailChangeConfirm(int $chatId, string $token, string $callbackId): void
    {
        $verification = TelegramVerification::query()
            ->where('token', $token)
            ->where('purpose', TelegramVerification::PURPOSE_EMAIL_CHANGE)
            ->where('status', TelegramVerification::STATUS_PENDING)
            ->first();

        if (! $verification || $verification->isExpired()) {
            $this->telegram->answerCallbackQuery($callbackId, 'Havola eskirgan.');
            $this->telegram->sendMessage($chatId,
                "⚠️ Havola eskirgan. Saytga qaytib, qayta urinib ko'ring."
            );

            return;
        }

        // Email o'zgartirish
        $userId = $verification->session_payload['user_id'] ?? 0;
        $newEmail = $verification->session_payload['new_email'] ?? '';

        if ($userId && $newEmail) {
            $user = \App\Models\User::find($userId);
            if ($user) {
                $oldEmail = $user->email;
                $user->update([
                    'email' => $newEmail,
                    'email_verified_at' => now(),
                ]);

                \App\Services\UserActivityLogger::log(
                    $user,
                    \App\Models\UserActivity::TYPE_EMAIL_CHANGED,
                    'Email manzili o\'zgartirildi',
                    ['old_email' => $oldEmail],
                    ['new_email' => $newEmail]
                );
            }
        }

        $verification->update([
            'status' => TelegramVerification::STATUS_COMPLETED,
        ]);

        $this->telegram->answerCallbackQuery($callbackId, 'Email o\'zgartirildi!');
        $this->telegram->sendMessage($chatId,
            "✅ Email manzili muvaffaqiyatli o'zgartirildi!\n\n"
            .'Yangi email: <b>' . htmlspecialchars($newEmail) . '</b>'
        );
    }

    /**
     | Email o'zgartirishni bekor qilish.
     */
    private function handleEmailChangeCancel(int $chatId, string $token, string $callbackId): void
    {
        $verification = TelegramVerification::query()
            ->where('token', $token)
            ->where('purpose', TelegramVerification::PURPOSE_EMAIL_CHANGE)
            ->where('status', TelegramVerification::STATUS_PENDING)
            ->first();

        if ($verification) {
            $verification->update(['status' => TelegramVerification::STATUS_EXPIRED]);
        }

        $this->telegram->answerCallbackQuery($callbackId, 'Bekor qilindi.');
        $this->telegram->sendMessage($chatId,
            "❌ Email o'zgartirish bekor qilindi."
        );
    }

    /**
     | Telefon raqamni o'zgartirishni Telegram orqali tasdiqlash.
     */
    private function handlePhoneChangeConfirm(int $chatId, string $token, string $callbackId): void
    {
        $verification = TelegramVerification::query()
            ->where('token', $token)
            ->where('purpose', TelegramVerification::PURPOSE_PHONE_CHANGE)
            ->where('status', TelegramVerification::STATUS_PENDING)
            ->first();

        if (! $verification || $verification->isExpired()) {
            $this->telegram->answerCallbackQuery($callbackId, 'Havola eskirgan.');
            $this->telegram->sendMessage($chatId,
                "⚠️ Havola eskirgan. Saytga qaytib, qayta urinib ko'ring."
            );

            return;
        }

        // Telefon o'zgartirish
        $userId = $verification->session_payload['user_id'] ?? 0;
        $newPhone = $verification->session_payload['new_phone'] ?? '';

        if ($userId && $newPhone) {
            $user = \App\Models\User::find($userId);
            if ($user) {
                $oldPhone = $user->phone;
                $user->update(['phone' => $newPhone]);

                \App\Services\UserActivityLogger::log(
                    $user,
                    \App\Models\UserActivity::TYPE_PROFILE_UPDATED,
                    'Telefon raqami o\'zgartirildi',
                    ['old_phone' => $oldPhone],
                    ['new_phone' => $newPhone]
                );
            }
        }

        $verification->update([
            'status' => TelegramVerification::STATUS_COMPLETED,
        ]);

        $this->telegram->answerCallbackQuery($callbackId, 'Telefon o\'zgartirildi!');
        $this->telegram->sendMessage($chatId,
            "✅ Telefon raqami muvaffaqiyatli o'zgartirildi!\n\n"
            .'Yangi raqam: <b>' . htmlspecialchars($newPhone) . '</b>'
        );
    }

    /**
     | Telefon o'zgartirishni bekor qilish.
     */
    private function handlePhoneChangeCancel(int $chatId, string $token, string $callbackId): void
    {
        $verification = TelegramVerification::query()
            ->where('token', $token)
            ->where('purpose', TelegramVerification::PURPOSE_PHONE_CHANGE)
            ->where('status', TelegramVerification::STATUS_PENDING)
            ->first();

        if ($verification) {
            $verification->update(['status' => TelegramVerification::STATUS_EXPIRED]);
        }

        $this->telegram->answerCallbackQuery($callbackId, 'Bekor qilindi.');
        $this->telegram->sendMessage($chatId,
            "❌ Telefon o'zgartirish bekor qilindi."
        );
    }

    /**
     | Kurs ochish so'rovi — ruxsat yoki rad etish.
     */
    private function handleCourseOpenDecision(int $chatId, int $userId, bool $approved, string $callbackId, array $callbackQuery = []): void
    {
        // Ruxsat tekshirish — faqat admin ruxsat bera oladi
        $actingUser = \App\Models\User::where('telegram_chat_id', $chatId)->first();
        if (! $actingUser || ! $actingUser->isAdmin()) {
            $this->telegram->answerCallbackQuery($callbackId, "Sizda bu amalni bajarish huquqi yo'q.");
            return;
        }

        $user = \App\Models\User::find($userId);
        if (! $user || ! $user->course_open_request_pending) {
            $this->telegram->answerCallbackQuery($callbackId, 'So\'rov topilmadi.');
            return;
        }

        $user->update([
            'course_open_approved' => $approved,
            'course_open_request_pending' => false,
            'course_open_approved_at' => $approved ? now() : null,
        ]);

        // Teacherga xabar yuborish
        if ($user->telegram_chat_id) {
            $text = $approved
                ? "📚 Kurs ochish so'rovingiz tasdiqlandi!\n\nEndi kurs yaratishingiz mumkin."
                : "📚 Kurs ochish so'rovingiz rad etildi.";
            $this->telegram->sendMessage((int) $user->telegram_chat_id, $text);
        }

        $decisionText = $approved ? 'Ruxsat berildi' : 'Rad etildi';
        $decisionEmoji = $approved ? '✅' : '❌';
        $this->telegram->answerCallbackQuery($callbackId, $decisionText);
        
        // Asl xabarni tahrirlash — tugmalarni o'chirish
        $messageId = (int) ($callbackQuery['message']['message_id'] ?? 0);
        if ($messageId) {
            $editedText = "📚 <b>Kurs ochish so'rovi</b>\n"
                ."━━━━━━━━━━━━━━━━━━━━\n\n"
                ."👨‍🏫 <b>O'qituvchi:</b> ".htmlspecialchars($user->name)."\n"
                ."━━━━━━━━━━━━━━━━━━━━\n\n"
                ."{$decisionEmoji} <b>{$decisionText}</b>";
            $this->telegram->editMessageText($chatId, $messageId, $editedText, ['inline_keyboard' => []]);
        }
    }

    /**
     | Kursga yozilish — ruxsat yoki rad etish.
     */
    private function handleEnrollmentDecision(int $chatId, int $enrollmentId, bool $approved, string $callbackId, array $callbackQuery = []): void
    {
        // Ruxsat tekshirish — admin yoki kurs egasi (o'qituvchi)
        $actingUser = \App\Models\User::where('telegram_chat_id', $chatId)->first();
        $enrollment = \App\Models\CourseEnrollment::with(['course', 'user'])->find($enrollmentId);
        $course = $enrollment?->course;

        if (! $actingUser || ! $course || ! ($actingUser->isAdmin() || ($actingUser->isTeacher() && $actingUser->ownsCourse($course)))) {
            $this->telegram->answerCallbackQuery($callbackId, "Sizda bu amalni bajarish huquqi yo'q.");
            return;
        }

        if (! $enrollment || ! $enrollment->isPending()) {
            $this->telegram->answerCallbackQuery($callbackId, 'Ariza topilmadi.');
            return;
        }

        $enrollment->update([
            'status' => $approved ? \App\Models\CourseEnrollment::STATUS_APPROVED : \App\Models\CourseEnrollment::STATUS_REJECTED,
            'reviewed_at' => now(),
            'reviewed_by' => $this->getUserIdFromChatId($chatId),
        ]);

        // Studentga xabar yuborish
        $student = $enrollment->user;
        if ($student && $student->telegram_chat_id) {
            $courseTitle = $enrollment->course ? $enrollment->course->title : 'Kurs';
            $text = $approved
                ? "✅ Kursga yozilish tasdiqlandi!\n\n<b>Kurs:</b> ".htmlspecialchars($courseTitle)."\nKurs boshlanishini kuting."
                : "❌ Kursga yozilish rad etildi.\n\n<b>Kurs:</b> ".htmlspecialchars($courseTitle);
            $this->telegram->sendMessage((int) $student->telegram_chat_id, $text);
        }

        $decisionText = $approved ? 'Tasdiqlandi' : 'Rad etildi';
        $decisionEmoji = $approved ? '✅' : '❌';
        $this->telegram->answerCallbackQuery($callbackId, $decisionText);
        
        // Asl xabarni tahrirlash — tugmalarni o'chirish
        $messageId = (int) ($callbackQuery['message']['message_id'] ?? 0);
        if ($messageId) {
            $editedText = "📝 <b>Yangi kursga yozilish arizasi</b>\n"
                ."━━━━━━━━━━━━━━━━━━━━\n\n"
                ."📚 <b>Kurs:</b> ".htmlspecialchars($enrollment->course?->title ?? 'Kurs')."\n"
                ."👤 <b>O'quvchi:</b> ".htmlspecialchars($student?->name ?? 'Noma\'lum')."\n"
                ."━━━━━━━━━━━━━━━━━━━━\n\n"
                ."{$decisionEmoji} <b>Ariza {$decisionText}</b>";
            $this->telegram->editMessageText($chatId, $messageId, $editedText, ['inline_keyboard' => []]);
        }
    }

    /**
     | Chat ID dan user ID ni olish.
     */
    private function getUserIdFromChatId(int $chatId): ?int
    {
        $user = \App\Models\User::where('telegram_chat_id', $chatId)->first();
        return $user?->id;
    }

    /**
     | Telefon raqamni normallashtirish.
     */
    public function normalizePhone(string $phone): string
    {
        $phone = preg_replace('/[\s\-\(\)\.]/', '', $phone);

        if (! str_starts_with($phone, '+')) {
            if (str_starts_with($phone, '998')) {
                $phone = '+'.$phone;
            } elseif (str_starts_with($phone, '8') && strlen($phone) === 9) {
                $phone = '+998'.substr($phone, 1);
            }
        }

        return $phone;
    }
}
