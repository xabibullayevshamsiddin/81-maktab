<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class TeacherCourseController extends Controller
{
    public function create()
    {
        $user = $this->authorizeCreator();
        $isAdmin = $user->isAdmin();

        if ($redirect = $this->teacherCourseBlockResponse($user)) {
            return $redirect;
        }

        $teachers = collect();
        $selectedTeacher = null;
        if ($isAdmin) {
            $teachers = Teacher::query()
                ->where('is_active', true)
                ->orderBy('full_name')
                ->get();
        } else {
            $selectedTeacher = $this->teacherProfileLinkedToUser($user);
            if (! $selectedTeacher) {
                return redirect()
                    ->route('profile.show')
                    ->with(
                        'error',
                        "Kurs ochish uchun admin sizning akkauntingizni ustoz kartasiga bog'lashi kerak (Admin > Ustozlar > Tahrirlash > Foydalanuvchi tanlash)."
                    )
                    ->with('toast_type', 'error');
            }
        }

        return view('courses.create', compact('teachers', 'isAdmin', 'selectedTeacher'));
    }

    public function store(Request $request)
    {
        $user = $this->authorizeCreator();

        if ($redirect = $this->teacherCourseBlockResponse($user)) {
            return $redirect;
        }

        $isAdmin = $user->isAdmin();
        $validated = $request->validate([
            'teacher_id' => [$isAdmin ? 'required' : 'nullable', 'integer', 'exists:teachers,id'],
            'title' => ['required', 'string', 'max:255'],
            'title_en' => ['nullable', 'string', 'max:255'],
            'price' => ['required', 'string', 'max:100'],
            'price_en' => ['nullable', 'string', 'max:100'],
            'duration' => ['required', 'string', 'max:120'],
            'duration_en' => ['nullable', 'string', 'max:120'],
            'description' => ['required', 'string'],
            'description_en' => ['nullable', 'string'],
            'start_date' => ['required', 'date'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        if ($isAdmin) {
            $teacher = Teacher::query()->findOrFail((int) $validated['teacher_id']);
        } else {
            $teacher = $this->teacherProfileLinkedToUser($user);
            if (! $teacher) {
                return redirect()
                    ->route('profile.show')
                    ->with(
                        'error',
                        "Kurs ochish uchun admin sizning akkauntingizni ustoz kartasiga bog'lashi kerak (Admin > Ustozlar > Tahrirlash > Foydalanuvchi tanlash)."
                    )
                    ->with('toast_type', 'error');
            }
        }

        abort_unless($teacher->is_active, 422, "Nofaol ustozga kurs biriktirib bo'lmaydi.");

        if (! $this->courseEmailVerificationEnabled()) {
            $payload = [
                'teacher_id' => (int) $teacher->id,
                'created_by' => (int) $user->id,
                'title' => $validated['title'],
                'title_en' => $validated['title_en'] ?? null,
                'price' => $validated['price'],
                'price_en' => $validated['price_en'] ?? null,
                'duration' => $validated['duration'],
                'duration_en' => $validated['duration_en'] ?? null,
                'description' => $validated['description'],
                'description_en' => $validated['description_en'] ?? null,
                'start_date' => $validated['start_date'],
                'status' => Course::STATUS_PUBLISHED,
                'publish_code' => null,
                'publish_code_expires_at' => null,
            ];

            if ($request->hasFile('image')) {
                $payload['image'] = $request->file('image')->store('courses', 'public');
            }

            Course::create($payload);
            $this->resetTeacherCourseOpenFlagsAfterCreate($user, $isAdmin);
            forget_public_course_caches();

            return redirect()
                ->route('courses')
                ->with('success', 'Kurs yaratildi va saytda chiqdi.')
                ->with('toast_type', 'success');
        }

        $code = (string) random_int(100000, 999999);

        $payload = [
            'teacher_id' => (int) $teacher->id,
            'created_by' => (int) $user->id,
            'title' => $validated['title'],
            'title_en' => $validated['title_en'] ?? null,
            'price' => $validated['price'],
            'price_en' => $validated['price_en'] ?? null,
            'duration' => $validated['duration'],
            'duration_en' => $validated['duration_en'] ?? null,
            'description' => $validated['description'],
            'description_en' => $validated['description_en'] ?? null,
            'start_date' => $validated['start_date'],
            'status' => Course::STATUS_PENDING_VERIFICATION,
            'publish_code' => $code,
            'publish_code_expires_at' => now()->addMinutes(15),
        ];

        if ($request->hasFile('image')) {
            $payload['image'] = $request->file('image')->store('courses', 'public');
        }

        $course = Course::create($payload);

        $this->resetTeacherCourseOpenFlagsAfterCreate($user, $isAdmin);

        $this->sendPublishCode($user->email, $course, $code);

        return redirect()
            ->route('teacher.courses.verify.form', $course)
            ->with('success', "Tasdiqlash kodi emailingizga yuborildi.")
            ->with('toast_type', 'success');
    }

    public function requestAccess()
    {
        abort_unless(auth()->check(), 403);

        $user = auth()->user();
        abort_unless($user->isTeacher(), 403);

        if (! $user->hasLinkedActiveTeacherProfile()) {
            return redirect()
                ->route('profile.show')
                ->with(
                    'error',
                    "Avval admin akkauntingizni ustoz kartasiga bog'lashi kerak, keyin kurs uchun ruxsat so'raysiz."
                )
                ->with('toast_type', 'error');
        }

        if ($user->hasReachedCourseOpenLimit()) {
            return redirect()
                ->route('profile.show')
                ->with('error', "Bitta teacher akkaunti faqat bitta kurs ocha oladi.")
                ->with('toast_type', 'warning');
        }

        if ($user->hasCourseOpenApproval()) {
            return redirect()
                ->route('teacher.courses.create')
                ->with('success', "Admin sizga ruxsat bergan, endi kursni ochishingiz mumkin.")
                ->with('toast_type', 'success');
        }

        if ($user->hasPendingCourseOpenRequest()) {
            return redirect()
                ->route('profile.show')
                ->with('error', "Kurs ochish so'rovingiz allaqachon yuborilgan. Admin javobini kuting.")
                ->with('toast_type', 'warning');
        }

        $user->update([
            'course_open_request_pending' => true,
            'course_open_requested_at' => now(),
            'course_open_approved' => false,
            'course_open_approved_at' => null,
        ]);

        return redirect()
            ->route('profile.show')
            ->with('success', "Kurs ochish uchun ruxsat so'rovi adminga yuborildi.")
            ->with('toast_type', 'success');
    }

    public function verifyForm(Course $course)
    {
        $user = $this->authorizeCreator();
        $this->ensureCanAccessCourse($user, $course);

        if ($redirect = $this->autoPublishCourseWhenEmailVerificationDisabled($course)) {
            return $redirect;
        }

        return view('courses.verify', compact('course'));
    }

    public function verifyCode(Request $request, Course $course)
    {
        $user = $this->authorizeCreator();
        $this->ensureCanAccessCourse($user, $course);

        if ($redirect = $this->autoPublishCourseWhenEmailVerificationDisabled($course)) {
            return $redirect;
        }

        $validated = $request->validate([
            'code' => ['required', 'digits:6'],
        ]);

        if ($course->status === Course::STATUS_PUBLISHED) {
            return redirect()->route('courses')
                ->with('success', "Kurs allaqachon saytda.")
                ->with('toast_type', 'success');
        }

        if (! $course->publish_code || ! $course->publish_code_expires_at || now()->greaterThan($course->publish_code_expires_at)) {
            return back()
                ->withErrors(['code' => 'Kod muddati tugagan. Qayta kod yuboring.']);
        }

        if ((string) $course->publish_code !== (string) $validated['code']) {
            return back()
                ->withErrors(['code' => "Kod noto'g'ri."]);
        }

        $course->update([
            'status' => Course::STATUS_PUBLISHED,
            'publish_code' => null,
            'publish_code_expires_at' => null,
        ]);
        forget_public_course_caches();

        return redirect()->route('courses')
            ->with('success', "Kurs tasdiqlandi va saytda chiqdi.")
            ->with('toast_type', 'success');
    }

    public function resendCode(Course $course)
    {
        $user = $this->authorizeCreator();
        $this->ensureCanAccessCourse($user, $course);

        if ($redirect = $this->autoPublishCourseWhenEmailVerificationDisabled($course)) {
            return $redirect;
        }

        $code = (string) random_int(100000, 999999);
        $course->update([
            'publish_code' => $code,
            'publish_code_expires_at' => now()->addMinutes(15),
            'status' => Course::STATUS_PENDING_VERIFICATION,
        ]);

        $this->sendPublishCode($user->email, $course, $code);

        return back()
            ->with('success', "Yangi kod yuborildi.")
            ->with('toast_type', 'warning');
    }

    public function edit(Course $course)
    {
        $user = $this->authorizeCreator();
        $this->ensureCanManageCourse($user, $course);

        $course->loadMissing('teacher');

        if (request()->routeIs('admin.courses.edit')) {
            $isAdmin = true;
            $teachers = Teacher::query()
                ->where('is_active', true)
                ->orderBy('full_name')
                ->get();

            return view('admin.courses.edit', compact('course', 'teachers', 'isAdmin'));
        }

        $selectedTeacher = $this->teacherProfileLinkedToUser($user);
        abort_unless(
            $selectedTeacher && (int) $course->teacher_id === (int) $selectedTeacher->id,
            403,
            "Bu kursni tahrirlash huquqingiz yo'q."
        );

        $isAdmin = false;
        $teachers = collect();

        return view('courses.edit', compact('course', 'selectedTeacher', 'isAdmin', 'teachers'));
    }

    public function update(Request $request, Course $course)
    {
        $user = $this->authorizeCreator();
        $this->ensureCanManageCourse($user, $course);

        $isAdmin = $user->isAdmin();

        $rules = [
            'title' => ['required', 'string', 'max:255'],
            'title_en' => ['nullable', 'string', 'max:255'],
            'price' => ['required', 'string', 'max:100'],
            'price_en' => ['nullable', 'string', 'max:100'],
            'duration' => ['required', 'string', 'max:120'],
            'duration_en' => ['nullable', 'string', 'max:120'],
            'description' => ['required', 'string'],
            'description_en' => ['nullable', 'string'],
            'start_date' => ['required', 'date'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ];

        if ($isAdmin) {
            $rules['teacher_id'] = ['required', 'integer', 'exists:teachers,id'];
        }

        $validated = $request->validate($rules);

        $teacherId = $isAdmin
            ? (int) $validated['teacher_id']
            : (int) $course->teacher_id;

        $teacher = Teacher::query()->findOrFail($teacherId);
        abort_unless($teacher->is_active, 422, "Nofaol ustozga kurs biriktirib bo'lmaydi.");

        if (! $isAdmin && (int) $course->teacher_id !== $teacherId) {
            abort(403);
        }

        $payload = [
            'teacher_id' => $teacherId,
            'title' => $validated['title'],
            'title_en' => $validated['title_en'] ?? null,
            'price' => $validated['price'],
            'price_en' => $validated['price_en'] ?? null,
            'duration' => $validated['duration'],
            'duration_en' => $validated['duration_en'] ?? null,
            'description' => $validated['description'],
            'description_en' => $validated['description_en'] ?? null,
            'start_date' => $validated['start_date'],
        ];

        if ($request->hasFile('image')) {
            if (! empty($course->image)) {
                Storage::disk('public')->delete($course->image);
            }
            $payload['image'] = $request->file('image')->store('courses', 'public');
        }

        $course->update($payload);
        forget_public_course_caches();

        if (request()->routeIs('admin.courses.update')) {
            return redirect()
                ->route('admin.courses.index')
                ->with('success', 'Kurs yangilandi.')
                ->with('toast_type', 'success');
        }

        return redirect()
            ->to(route('profile.show').'#profile-created-courses')
            ->with('success', 'Kurs yangilandi.')
            ->with('toast_type', 'success');
    }

    public function destroy(Request $request, Course $course)
    {
        $user = $this->authorizeCreator();
        $this->ensureCanManageCourse($user, $course);

        if (! empty($course->image)) {
            Storage::disk('public')->delete($course->image);
        }

        $course->delete();
        forget_public_course_caches();

        if (request()->routeIs('admin.courses.destroy')) {
            return redirect()
                ->route('admin.courses.index')
                ->with('success', "Kurs o'chirildi.")
                ->with('toast_type', 'warning');
        }

        return redirect()
            ->to(route('profile.show').'#profile-created-courses')
            ->with('success', "Kurs o'chirildi.")
            ->with('toast_type', 'warning');
    }

    private function authorizeCreator()
    {
        abort_unless(auth()->check(), 403);
        $user = auth()->user();
        abort_unless($user->isTeacher() || $user->isAdmin(), 403);

        return $user;
    }

    private function ensureCanAccessCourse($user, Course $course): void
    {
        $this->ensureCanManageCourse($user, $course);
    }

    private function ensureCanManageCourse($user, Course $course): void
    {
        if ($user->isAdmin()) {
            return;
        }

        abort_unless($user->isTeacher() && (int) $course->created_by === (int) $user->id, 403);
    }

    private function sendPublishCode(string $email, Course $course, string $code): void
    {
        if (! $this->mailDeliveryEnabled()) {
            Log::info('Course publish code email skipped because mail delivery is disabled', [
                'email' => $email,
                'course_id' => $course->id,
            ]);

            return;
        }

        try {
            $html = '
            <div style="background:#f3f6fb;padding:24px 12px;font-family:Arial,sans-serif;">
              <div style="max-width:520px;margin:0 auto;background:#ffffff;border-radius:14px;overflow:hidden;border:1px solid #e5e7eb;">
                <div style="background:linear-gradient(135deg,#0ea5e9,#2563eb);padding:18px 20px;color:#fff;">
                  <h1 style="margin:0;font-size:20px;line-height:1.3;">81-maktab</h1>
                  <p style="margin:6px 0 0;font-size:13px;opacity:.95;">Kurs tasdiqlash</p>
                </div>
                <div style="padding:22px 20px;color:#111827;">
                  <h2 style="margin:0 0 10px;font-size:18px;">Kursni tasdiqlang</h2>
                  <p style="margin:0 0 6px;color:#4b5563;font-size:14px;">Kurs: <strong>'.e($course->title).'</strong></p>
                  <p style="margin:0 0 16px;color:#4b5563;font-size:14px;line-height:1.6;">Quyidagi 6 xonali kodni kiriting:</p>
                  <div style="text-align:center;margin:18px 0 16px;">
                    <span style="display:inline-block;letter-spacing:6px;font-weight:700;font-size:30px;padding:12px 18px;border-radius:10px;background:#eef2ff;color:#1d4ed8;">'.$code.'</span>
                  </div>
                  <p style="margin:0;color:#dc2626;font-size:13px;font-weight:600;">Kod 15 daqiqa amal qiladi.</p>
                </div>
              </div>
            </div>';

            Mail::html($html, static function ($message) use ($email) {
                $message->to($email)->subject("Kurs tasdiqlash kodi");
            });
        } catch (\Throwable $e) {
            Log::warning('Course publish code email failed', [
                'email' => $email,
                'course_id' => $course->id,
                'code' => $code,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Teacher akkaunti uchun faqat shu userga bog'langan faol ustoz kartasi (boshqa fallback yo'q).
     */
    private function teacherProfileLinkedToUser(User $user): ?Teacher
    {
        return Teacher::query()
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->first();
    }

    private function courseEmailVerificationEnabled(): bool
    {
        return (bool) config('courses.require_email_verification', true);
    }

    private function mailDeliveryEnabled(): bool
    {
        return (bool) config('mail.enabled', true) && $this->mailConfigurationReady();
    }

    private function mailConfigurationReady(): bool
    {
        return match ((string) config('mail.default', 'smtp')) {
            'resend' => $this->hasConfiguredResendApiKey(),
            'smtp' => filled(config('mail.mailers.smtp.host')),
            default => true,
        };
    }

    private function hasConfiguredResendApiKey(): bool
    {
        $apiKey = trim((string) (config('resend.api_key') ?? config('services.resend.key') ?? ''));

        if ($apiKey === '' || ! str_starts_with($apiKey, 're_')) {
            return false;
        }

        $normalizedKey = strtolower($apiKey);

        return ! str_contains($normalizedKey, 'sizning_kalitingiz')
            && ! str_contains($normalizedKey, 'your_key')
            && ! str_contains($normalizedKey, 'your-api-key');
    }

    private function autoPublishCourseWhenEmailVerificationDisabled(Course $course): ?RedirectResponse
    {
        if ($this->courseEmailVerificationEnabled() || $course->status !== Course::STATUS_PENDING_VERIFICATION) {
            return null;
        }

        $course->update([
            'status' => Course::STATUS_PUBLISHED,
            'publish_code' => null,
            'publish_code_expires_at' => null,
        ]);
        forget_public_course_caches();

        return redirect()
            ->route('courses')
            ->with('success', "Email yuborish o'chirilgani uchun kurs darhol tasdiqlandi.")
            ->with('toast_type', 'success');
    }

    private function teacherCourseBlockResponse(User $user): ?RedirectResponse
    {
        if ($user->isAdmin()) {
            return null;
        }

        if (! $user->hasLinkedActiveTeacherProfile()) {
            return redirect()
                ->route('profile.show')
                ->with(
                    'error',
                    "Kurs ochish uchun admin sizning akkauntingizni ustoz kartasiga bog'lashi kerak (Admin > Ustozlar > Tahrirlash > Foydalanuvchi tanlash)."
                )
                ->with('toast_type', 'error');
        }

        if ($user->hasReachedCourseOpenLimit()) {
            return redirect()
                ->route('profile.show')
                ->with('error', "Teacher akkaunti faqat bitta kurs yaratishi mumkin.")
                ->with('toast_type', 'warning');
        }

        if (! $user->hasCourseOpenApproval()) {
            if ($user->hasPendingCourseOpenRequest()) {
                return redirect()
                    ->route('profile.show')
                    ->with('error', "Kurs ochish uchun admin ruxsatini kuting.")
                    ->with('toast_type', 'warning');
            }

            return redirect()
                ->route('profile.show')
                ->with('error', "Kurs ochishdan oldin profildan admin ruxsatini so'rang.")
                ->with('toast_type', 'warning');
        }

        return null;
    }

    /**
     * Bir martalik ruxsat ishlatilgandan keyin flaglarni tozalash (keyingi marta yana so'rash tartibi).
     */
    private function resetTeacherCourseOpenFlagsAfterCreate(User $user, bool $isAdmin): void
    {
        if ($isAdmin) {
            return;
        }

        $user->update([
            'course_open_approved' => false,
            'course_open_request_pending' => false,
        ]);
    }
}
