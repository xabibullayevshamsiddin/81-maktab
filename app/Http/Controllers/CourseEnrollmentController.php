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

        $validated = $request->validate([
            'contact_phone' => ['required', 'string', 'max:40'],
            'grade' => ['required', 'string', 'max:32'],
            'subject_level' => ['required', 'string', 'max:120'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        $userId = (int) $request->user()->id;

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
                    'contact_phone' => $validated['contact_phone'],
                    'grade' => $validated['grade'],
                    'subject_level' => $validated['subject_level'],
                    'note' => $validated['note'] ?? null,
                    'reviewed_at' => null,
                    'reviewed_by' => null,
                ]);

                $this->notifyCourseCreator($course, $existing, $request->user()->name);

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
            'contact_phone' => $validated['contact_phone'],
            'grade' => $validated['grade'],
            'subject_level' => $validated['subject_level'],
            'note' => $validated['note'] ?? null,
        ]);

        $this->notifyCourseCreator($course, $enrollment, $request->user()->name);

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
        if (! $creator?->email) {
            return;
        }

        try {
            Mail::raw(
                "Yangi kursga yozilish arizasi:\n\n".
                "Kurs: {$course->title}\n".
                "O‘quvchi: {$applicantName}\n".
                "Telefon (arizadagi): {$enrollment->contact_phone}\n".
                "Sinf: {$enrollment->grade}\n".
                "Fan darajasi: {$enrollment->subject_level}\n\n".
                "Profil → «Kursga yozilishlar» bo‘limidan tasdiqlang yoki rad eting.\n".
                route('teacher.enrollments.index'),
                static function ($message) use ($creator) {
                    $message->to($creator->email)->subject('Yangi kursga yozilish arizasi');
                }
            );
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
