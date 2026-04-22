<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseEnrollment;
use Illuminate\Http\Request;

class TeacherEnrollmentController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        abort_unless(
            Course::query()->where('created_by', $user->id)->exists(),
            403
        );

        $enrollments = CourseEnrollment::query()
            ->whereHas('course', fn ($q) => $q->where('created_by', $user->id))
            ->with(['course.teacher', 'user'])
            ->latest()
            ->paginate(25);

        $pendingCount = CourseEnrollment::query()
            ->whereHas('course', fn ($q) => $q->where('created_by', $user->id))
            ->where('status', CourseEnrollment::STATUS_PENDING)
            ->count();

        return view('profile.course-enrollments', compact('enrollments', 'pendingCount'));
    }

    public function approve(Request $request, CourseEnrollment $enrollment)
    {
        $this->authorizeEnrollment($enrollment);

        abort_unless($enrollment->isPending(), 422);

        $enrollment->update([
            'status' => CourseEnrollment::STATUS_APPROVED,
            'reviewed_at' => now(),
            'reviewed_by' => auth()->id(),
        ]);

        return $this->successRedirect('Yozilish tasdiqlandi.', 'success');
    }

    public function reject(Request $request, CourseEnrollment $enrollment)
    {
        $this->authorizeEnrollment($enrollment);

        abort_unless($enrollment->isPending(), 422);

        $enrollment->update([
            'status' => CourseEnrollment::STATUS_REJECTED,
            'reviewed_at' => now(),
            'reviewed_by' => auth()->id(),
        ]);

        return $this->successRedirect('Yozilish rad etildi.', 'warning');
    }

    public function destroy(Request $request, CourseEnrollment $enrollment)
    {
        $this->authorizeEnrollment($enrollment);

        $enrollment->delete();

        return $this->successRedirect('Yozilish olib tashlandi.', 'warning');
    }

    private function authorizeEnrollment(CourseEnrollment $enrollment): void
    {
        $enrollment->loadMissing('course');
        abort_unless((int) $enrollment->course->created_by === (int) auth()->id(), 403);
    }

    private function successRedirect(string $message, string $toastType)
    {
        $back = url()->previous();
        if ($back && $back !== url()->current()) {
            return redirect()->to($back)
                ->with('success', $message)
                ->with('toast_type', $toastType);
        }

        return redirect()
            ->route('teacher.enrollments.index')
            ->with('success', $message)
            ->with('toast_type', $toastType);
    }
}
