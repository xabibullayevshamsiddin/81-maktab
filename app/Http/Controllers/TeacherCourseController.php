<?php

namespace App\Http\Controllers;

use App\Http\Requests\RequestCourseOpenAccessRequest;
use App\Models\Course;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
        $courseOwner = $user;
        if ($isAdmin) {
            $teachers = Teacher::query()
                ->where('is_active', true)
                ->orderBy('full_name')
                ->get();
        }

        return view('courses.create', compact('teachers', 'isAdmin', 'selectedTeacher', 'courseOwner'));
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
            'price' => ['nullable', 'string', 'max:255'],
            'price_en' => ['nullable', 'string', 'max:255'],
            'duration' => ['nullable', 'string', 'max:255'],
            'duration_en' => ['nullable', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'description_en' => ['nullable', 'string'],
            'start_date' => ['required', 'date'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ], [
            'title.required' => 'Kurs sarlavhasini yozing.',
            'title.max' => 'Sarlavha 255 belgidan oshmasligi kerak.',
            'description.required' => 'Kurs haqida qisqacha yozing.',
            'start_date.required' => 'Kurs qachon boshlanishini kiriting.',
            'start_date.date' => 'Boshlanish sanasi noto‘g‘ri formatda.',
            'teacher_id.required' => 'Ustozni tanlang.',
            'teacher_id.exists' => 'Tanlangan ustoz mavjud emas.',
            'image.image' => 'Fayl rasm bo‘lishi kerak.',
            'image.max' => 'Rasm hajmi 5 MB dan oshmasligi kerak.',
            'image.mimes' => 'Rasm JPG, PNG yoki WebP formatida bo‘lishi kerak.',
        ]);

        $teacherId = null;
        if ($isAdmin) {
            $teacher = Teacher::query()->findOrFail((int) $validated['teacher_id']);
            abort_unless($teacher->is_active, 422, "Nofaol ustozga kurs biriktirib bo'lmaydi.");
            $teacherId = (int) $teacher->id;
        }

        $payload = [
            'teacher_id' => $teacherId,
            'created_by' => (int) $user->id,
            'title' => $validated['title'],
            'title_en' => $validated['title_en'] ?? null,
            'price' => $validated['price'] ?? null,
            'price_en' => $validated['price_en'] ?? null,
            'duration' => $validated['duration'] ?? null,
            'duration_en' => $validated['duration_en'] ?? null,
            'description' => $validated['description'],
            'description_en' => $validated['description_en'] ?? null,
            'start_date' => $validated['start_date'],
            'status' => Course::STATUS_PUBLISHED,
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

    public function requestAccess(RequestCourseOpenAccessRequest $request)
    {
        abort_unless(auth()->check(), 403);

        $user = auth()->user();
        abort_unless($user->isTeacher(), 403);

        if ($user->hasReachedCourseOpenLimit()) {
            $message = $user->isDonor()
                ? "Donor imtiyozlaringiz bilan jami {$user->donorCourseLimit()} ta kurs ochdingiz."
                : "Bitta o'qituvchi akkaunti faqat bitta kurs ocha oladi.";

            return response()->json(['ok' => false, 'message' => $message, 'toast_type' => 'warning']);
        }

        if ($user->hasCourseOpenApproval()) {
            $message = 'Admin sizga ruxsat bergan, endi kursni ochishingiz mumkin.';

            return response()->json(['ok' => true, 'message' => $message, 'toast_type' => 'success', 'redirect' => route('teacher.courses.create')]);
        }

        if ($user->hasPendingCourseOpenRequest()) {
            $message = "Kurs ochish so'rovingiz allaqachon yuborilgan. Admin javobini kuting.";

            return response()->json(['ok' => false, 'message' => $message, 'toast_type' => 'warning']);
        }

        $validated = $request->validated();
        $reason = trim((string) ($validated['reason'] ?? ''));

        $user->update([
            'course_open_request_pending' => true,
            'course_open_requested_at' => now(),
            'course_open_request_reason' => $reason,
            'course_open_approved' => false,
            'course_open_approved_at' => null,
        ]);

        $message = "Kurs ochish uchun ruxsat so'rovi adminga yuborildi.";

        return response()->json(['ok' => true, 'message' => $message, 'toast_type' => 'success']);
    }

    public function edit(Course $course)
    {
        $user = $this->authorizeCreator();
        $this->ensureCanManageCourse($user, $course);

        $course->loadMissing(['teacher', 'creator']);

        if (request()->routeIs('admin.courses.edit')) {
            $isAdmin = true;
            $teachers = Teacher::query()
                ->where('is_active', true)
                ->orderBy('full_name')
                ->get();

            return view('admin.courses.edit', compact('course', 'teachers', 'isAdmin'));
        }

        $selectedTeacher = $course->teacher;
        $isAdmin = false;
        $teachers = collect();

        return view('courses.edit', compact('course', 'selectedTeacher', 'isAdmin', 'teachers'));
    }

    public function update(Request $request, Course $course)
    {
        $user = $this->authorizeCreator();
        $this->ensureCanManageCourse($user, $course);

        $isAdmin = $user->isAdmin();

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'title_en' => ['nullable', 'string', 'max:255'],
            'price' => ['nullable', 'string', 'max:255'],
            'price_en' => ['nullable', 'string', 'max:255'],
            'duration' => ['nullable', 'string', 'max:255'],
            'duration_en' => ['nullable', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'description_en' => ['nullable', 'string'],
            'start_date' => ['required', 'date'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ], [
            'title.required' => 'Kurs sarlavhasini yozing.',
            'title.max' => 'Sarlavha 255 belgidan oshmasligi kerak.',
            'description.required' => 'Kurs haqida qisqacha yozing.',
            'start_date.required' => 'Kurs qachon boshlanishini kiriting.',
            'start_date.date' => 'Boshlanish sanasi noto‘g‘ri formatda.',
            'image.image' => 'Fayl rasm bo‘lishi kerak.',
            'image.max' => 'Rasm hajmi 5 MB dan oshmasligi kerak.',
            'image.mimes' => 'Rasm JPG, PNG yoki WebP formatida bo‘lishi kerak.',
        ]);

        if ($isAdmin) {
            $request->validate(['teacher_id' => ['nullable', 'integer', 'exists:teachers,id']]);
            $teacherId = isset($validated['teacher_id']) ? (int) $validated['teacher_id'] : null;
        } else {
            $teacherId = (int) $course->teacher_id ?: null;
        }

        if ($teacherId !== null) {
            $teacher = Teacher::query()->findOrFail($teacherId);
            abort_unless($teacher->is_active, 422, "Nofaol ustozga kurs biriktirib bo'lmaydi.");
        }

        $payload = [
            'teacher_id' => $teacherId,
            'title' => $validated['title'],
            'title_en' => $validated['title_en'] ?? null,
            'price' => $validated['price'] ?? null,
            'price_en' => $validated['price_en'] ?? null,
            'duration' => $validated['duration'] ?? null,
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

    private function ensureCanManageCourse($user, Course $course): void
    {
        if ($user->isAdmin()) {
            return;
        }

        abort_unless($user->isTeacher() && (int) $course->created_by === (int) $user->id, 403);
    }

    private function teacherCourseBlockResponse(User $user): ?RedirectResponse
    {
        if ($user->isAdmin()) {
            return null;
        }

        if ($user->hasReachedCourseOpenLimit()) {
            return redirect()
                ->route('profile.show', ['panel' => 'activity'])
                ->with('error', $user->isDonor()
                    ? "Donor imtiyozlaringiz bilan jami {$user->donorCourseLimit()} ta kurs ochdingiz."
                    : "O'qituvchi akkaunti faqat bitta kurs yaratishi mumkin.")
                ->with('toast_type', 'warning');
        }

        if (! $user->hasCourseOpenApproval()) {
            if ($user->hasPendingCourseOpenRequest()) {
                return redirect()
                    ->route('profile.show', ['panel' => 'activity'])
                    ->with('error', 'Kurs ochish uchun admin ruxsatini kuting.')
                    ->with('toast_type', 'warning');
            }

            return redirect()
                ->route('profile.show', ['panel' => 'activity'])
                ->with('error', "Kurs ochishdan oldin profildan admin ruxsatini so'rang.")
                ->with('toast_type', 'warning');
        }

        return null;
    }

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
