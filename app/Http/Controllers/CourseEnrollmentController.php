<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseEnrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class CourseEnrollmentController extends Controller
{
    public function store(Request $request, Course $course)
    {
        $this->ensureEnrollable($course);

        $user = $request->user();
        if ($user->isParent()) {
            return redirect()
                ->route('courses')
                ->with('error', 'Ota-ona akkaunti bilan kursga yozilish mumkin emas.')
                ->with('toast_type', 'warning');
        }

        $profilePhone = uz_phone_format((string) ($user->phone ?? ''));
        $profileGrade = normalize_school_grade((string) ($user->grade ?? ''));

        if (! $profilePhone) {
            return redirect()
                ->route('courses')
                ->with('error', 'Kursga yozilish uchun profilingizda telefon raqami bo‘lishi kerak.')
                ->with('toast_type', 'warning');
        }

        if (! $profileGrade) {
            return redirect()
                ->route('courses')
                ->with('error', 'Kursga yozilish uchun profilingizda sinf ma’lumoti bo‘lishi kerak.')
                ->with('toast_type', 'warning');
        }

        $validated = $request->validate([
            'subject_level' => ['required', 'string', 'max:120'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        $userId = (int) $user->id;

        if ((int) $course->created_by === $userId) {
            return redirect()
                ->route('courses')
                ->with('error', 'O‘z yaratgan kursingizga yozila olmaysiz.')
                ->with('toast_type', 'warning');
        }

        $existing = CourseEnrollment::query()
            ->where('course_id', $course->id)
            ->where('user_id', $userId)
            ->first();

        if ($existing) {
            if ($existing->isApproved()) {
                return redirect()
                    ->route('courses')
                    ->with('error', 'Siz bu kursga allaqachon qabul qilingansiz.')
                    ->with('toast_type', 'warning');
            }

            if ($existing->isPending()) {
                return redirect()
                    ->route('courses')
                    ->with('error', 'Sizning arizangiz hali ko‘rib chiqilmoqda. Tasdiqlanishini kuting.')
                    ->with('toast_type', 'warning');
            }

            if ($existing->isRejected()) {
                $existing->update([
                    'status' => CourseEnrollment::STATUS_PENDING,
                    'contact_phone' => $profilePhone,
                    'grade' => $profileGrade,
                    'subject_level' => $validated['subject_level'],
                    'note' => $validated['note'] ?? null,
                    'reviewed_at' => null,
                    'reviewed_by' => null,
                ]);

                $this->notifyCourseCreator($course, $existing, $user->name);

                return redirect()
                    ->route('courses')
                    ->with('success', 'Yangi ariza yuborildi. Kurs muallifi tez orada bog‘lanadi.')
                    ->with('toast_type', 'success');
            }
        }

        $enrollment = CourseEnrollment::query()->create([
            'course_id' => $course->id,
            'user_id' => $userId,
            'status' => CourseEnrollment::STATUS_PENDING,
            'contact_phone' => $profilePhone,
            'grade' => $profileGrade,
            'subject_level' => $validated['subject_level'],
            'note' => $validated['note'] ?? null,
        ]);

        $this->notifyCourseCreator($course, $enrollment, $user->name);

        return redirect()
            ->route('courses')
            ->with('success', 'Arizangiz qabul qilindi. Tasdiqlangach siz bilan bog‘lanamiz.')
            ->with('toast_type', 'success');
    }

    public function destroy(Request $request, Course $course)
    {
        $deleted = CourseEnrollment::query()
            ->where('course_id', $course->id)
            ->where('user_id', (int) $request->user()->id)
            ->delete();

        if (! $deleted) {
            return redirect()
                ->route('courses')
                ->with('error', 'Yozilish topilmadi.')
                ->with('toast_type', 'error');
        }

        return redirect()
            ->route('courses')
            ->with('success', 'Yozilish bekor qilindi.')
            ->with('toast_type', 'warning');
    }

    private function notifyCourseCreator(Course $course, CourseEnrollment $enrollment, string $applicantName): void
    {
        $course->loadMissing('creator');
        $creator = $course->creator;
        if (! $creator?->email || ! config('mail.enabled', true)) {
            return;
        }

        try {
            $enrollHtml = '
            <div style="background:#f3f6fb;padding:24px 12px;font-family:Arial,sans-serif;">
              <div style="max-width:520px;margin:0 auto;background:#ffffff;border-radius:14px;overflow:hidden;border:1px solid #e5e7eb;">
                <div style="background:linear-gradient(135deg,#10b981,#059669);padding:18px 20px;color:#fff;">
                  <h1 style="margin:0;font-size:20px;line-height:1.3;">81-maktab</h1>
                  <p style="margin:6px 0 0;font-size:13px;opacity:.95;">Yangi ariza</p>
                </div>
                <div style="padding:22px 20px;color:#111827;">
                  <h2 style="margin:0 0 14px;font-size:18px;">Yangi kursga yozilish arizasi</h2>
                  <table style="width:100%;font-size:14px;color:#374151;line-height:1.7;border-collapse:collapse;">
                    <tr><td style="padding:4px 8px 4px 0;font-weight:600;white-space:nowrap;">Kurs:</td><td style="padding:4px 0;">'.e($course->title).'</td></tr>
                    <tr><td style="padding:4px 8px 4px 0;font-weight:600;white-space:nowrap;">O\'quvchi:</td><td style="padding:4px 0;">'.e($applicantName).'</td></tr>
                    <tr><td style="padding:4px 8px 4px 0;font-weight:600;white-space:nowrap;">Telefon:</td><td style="padding:4px 0;">'.e($enrollment->contact_phone).'</td></tr>
                    <tr><td style="padding:4px 8px 4px 0;font-weight:600;white-space:nowrap;">Sinf:</td><td style="padding:4px 0;">'.e($enrollment->grade).'</td></tr>
                    <tr><td style="padding:4px 8px 4px 0;font-weight:600;white-space:nowrap;">Fan darajasi:</td><td style="padding:4px 0;">'.e($enrollment->subject_level).'</td></tr>
                  </table>
                  <p style="margin:18px 0 0;text-align:center;">
                    <a href="'.route('teacher.enrollments.index').'" style="display:inline-block;padding:10px 20px;border-radius:10px;background:#059669;color:#fff;text-decoration:none;font-weight:700;font-size:14px;">
                      Arizalarni ko\'rish
                    </a>
                  </p>
                </div>
              </div>
            </div>';

            Mail::html($enrollHtml, static function ($message) use ($creator) {
                $message->to($creator->email)->subject('Yangi kursga yozilish arizasi');
            });
        } catch (\Throwable $e) {
            Log::warning('Course enrollment notify email failed', [
                'course_id' => $course->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function ensureEnrollable(Course $course): void
    {
        abort_unless($course->status === Course::STATUS_PUBLISHED, 404);
        abort_unless($course->teacher && $course->teacher->is_active, 404);
    }
}
