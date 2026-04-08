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

        if (! config('courses.require_email_verification')) {
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

        return view('courses.verify', compact('course'));
    }

    public function verifyCode(Request $request, Course $course)
    {
        $user = $this->authorizeCreator();
        $this->ensureCanAccessCourse($user, $course);

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

        $teachers = Teacher::query()
            ->where('is_active', true)
            ->orderBy('full_name')
            ->get();

        return view('admin.courses.edit', compact('course', 'teachers'));
    }

    public function update(Request $request, Course $course)
    {
        $user = $this->authorizeCreator();
        $this->ensureCanManageCourse($user, $course);

        $validated = $request->validate([
            'teacher_id' => ['required', 'integer', 'exists:teachers,id'],
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

        $teacher = Teacher::query()->findOrFail((int) $validated['teacher_id']);
        abort_unless($teacher->is_active, 422, "Nofaol ustozga kurs biriktirib bo'lmaydi.");

        $payload = [
            'teacher_id' => (int) $validated['teacher_id'],
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

        return redirect()
            ->route('admin.courses.index')
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

        return redirect()
            ->route('admin.courses.index')
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
        try {
            Mail::raw(
                "Kursni tasdiqlash kodi: {$code}\n\nKurs: {$course->title}\nKod 15 daqiqa amal qiladi.",
                static function ($message) use ($email) {
                    $message->to($email)->subject("Kurs tasdiqlash kodi");
                }
            );
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

        return null;
    }
}
