<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class TeacherCourseController extends Controller
{
    public function create()
    {
        $this->authorizeCreator();

        $teachers = Teacher::query()
            ->where('is_active', true)
            ->orderBy('full_name')
            ->get();

        return view('courses.create', compact('teachers'));
    }

    public function store(Request $request)
    {
        $user = $this->authorizeCreator();

        $validated = $request->validate([
            'teacher_id' => ['required', 'integer', 'exists:teachers,id'],
            'title' => ['required', 'string', 'max:255'],
            'price' => ['required', 'string', 'max:100'],
            'duration' => ['required', 'string', 'max:120'],
            'description' => ['required', 'string'],
            'start_date' => ['required', 'date'],
        ]);

        $teacher = Teacher::query()->findOrFail((int) $validated['teacher_id']);
        abort_unless($teacher->is_active, 422, "Nofaol ustozga kurs biriktirib bo'lmaydi.");

        $code = (string) random_int(100000, 999999);

        $course = Course::create([
            'teacher_id' => (int) $validated['teacher_id'],
            'created_by' => (int) $user->id,
            'title' => $validated['title'],
            'price' => $validated['price'],
            'duration' => $validated['duration'],
            'description' => $validated['description'],
            'start_date' => $validated['start_date'],
            'status' => Course::STATUS_PENDING_VERIFICATION,
            'publish_code' => $code,
            'publish_code_expires_at' => now()->addMinutes(15),
        ]);

        $this->sendPublishCode($user->email, $course, $code);

        return redirect()
            ->route('teacher.courses.verify.form', $course)
            ->with('success', "Tasdiqlash kodi emailingizga yuborildi.")
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

    private function authorizeCreator()
    {
        abort_unless(auth()->check(), 403);
        $user = auth()->user();
        abort_unless($user->isTeacher() || $user->isAdmin(), 403);

        return $user;
    }

    private function ensureCanAccessCourse($user, Course $course): void
    {
        if ($user->isAdmin()) {
            return;
        }

        abort_unless((int) $course->created_by === (int) $user->id, 403);
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
}

