<?php

namespace App\Services;

use App\Models\TelegramVerification;

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
            "Assalomu alaykum!\n\nSaytda ro'yxatdan o'tish yoki kirish uchun havolani bosing."
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

        // Shu chat orqali oxirgi pending yozuvni topish
        $verification = TelegramVerification::query()
            ->where('status', TelegramVerification::STATUS_PENDING)
            ->where('expires_at', '>', now())
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
            ."Saytga qaytishingiz mumkin."
        );
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
                $user->update([
                    'email' => $newEmail,
                    'email_verified_at' => now(),
                ]);

                \App\Services\UserActivityLogger::log(
                    $user,
                    \App\Models\UserActivity::TYPE_EMAIL_CHANGED,
                    'Email manzili o\'zgartirildi',
                    ['old_email' => $user->email],
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
                $user->update(['phone' => $newPhone]);

                \App\Services\UserActivityLogger::log(
                    $user,
                    \App\Models\UserActivity::TYPE_PROFILE_UPDATED,
                    'Telefon raqami o\'zgartirildi',
                    ['old_phone' => $user->phone],
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
        \Illuminate\Support\Facades\Log::info('Telegram editMessage attempt', [
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'callback_query_id' => $callbackId,
        ]);
        if ($messageId) {
            $editedText = "📚 <b>Kurs ochish so'rovi</b>\n"
                ."━━━━━━━━━━━━━━━━━━━━\n\n"
                ."👨‍🏫 <b>O'qituvchi:</b> ".htmlspecialchars($user->name)."\n"
                ."━━━━━━━━━━━━━━━━━━━━\n\n"
                ."{$decisionEmoji} <b>{$decisionText}</b>";
            $result = $this->telegram->editMessageText($chatId, $messageId, $editedText);
            \Illuminate\Support\Facades\Log::info('Telegram editMessage result', ['result' => $result]);
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
        \Illuminate\Support\Facades\Log::info('Telegram enrollment editMessage attempt', [
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'enrollment_id' => $enrollmentId,
        ]);
        if ($messageId) {
            $editedText = "📝 <b>Yangi kursga yozilish arizasi</b>\n"
                ."━━━━━━━━━━━━━━━━━━━━\n\n"
                ."📚 <b>Kurs:</b> ".htmlspecialchars($enrollment->course?->title ?? 'Kurs')."\n"
                ."👤 <b>O'quvchi:</b> ".htmlspecialchars($student?->name ?? 'Noma\'lum')."\n"
                ."━━━━━━━━━━━━━━━━━━━━\n\n"
                ."{$decisionEmoji} <b>Ariza {$decisionText}</b>";
            $result = $this->telegram->editMessageText($chatId, $messageId, $editedText);
            \Illuminate\Support\Facades\Log::info('Telegram enrollment editMessage result', ['result' => $result]);
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
