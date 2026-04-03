<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\User;
use Illuminate\Http\Request;

class AdminCourseEnrollmentController extends Controller
{
    public function indexAll(Request $request)
    {
        abort_unless(auth()->check() && auth()->user()->isAdmin(), 403);

        $status = $request->query('status');
        $query = CourseEnrollment::query()
            ->with(['course.teacher', 'course.creator', 'user', 'reviewer'])
            ->latest();

        if (in_array($status, [CourseEnrollment::STATUS_PENDING, CourseEnrollment::STATUS_APPROVED, CourseEnrollment::STATUS_REJECTED], true)) {
            $query->where('status', $status);
        }

        $enrollments = $query->paginate(30)->withQueryString();

        $pendingCount = CourseEnrollment::query()
            ->where('status', CourseEnrollment::STATUS_PENDING)
            ->count();

        return view('admin.course-enrollments.index', compact('enrollments', 'pendingCount'));
    }

    public function index(Course $course)
    {
        $this->authorizeManageCourse($course);

        $course->load('teacher');

        $enrollments = $course->enrollments()
            ->with(['user', 'reviewer'])
            ->latest()
            ->get();

        return view('admin.courses.enrollments', compact('course', 'enrollments'));
    }

    public function approve(Request $request, Course $course, CourseEnrollment $enrollment)
    {
        $this->authorizeManageCourse($course);
        abort_unless((int) $enrollment->course_id === (int) $course->id, 404);
        abort_unless($enrollment->isPending(), 422);

        $enrollment->update([
            'status' => CourseEnrollment::STATUS_APPROVED,
            'reviewed_at' => now(),
            'reviewed_by' => auth()->id(),
        ]);

        return back()
            ->with('success', 'Yozilish tasdiqlandi.')
            ->with('toast_type', 'success');
    }

    public function reject(Request $request, Course $course, CourseEnrollment $enrollment)
    {
        $this->authorizeManageCourse($course);
        abort_unless((int) $enrollment->course_id === (int) $course->id, 404);
        abort_unless($enrollment->isPending(), 422);

        $enrollment->update([
            'status' => CourseEnrollment::STATUS_REJECTED,
            'reviewed_at' => now(),
            'reviewed_by' => auth()->id(),
        ]);

        return back()
            ->with('success', 'Yozilish rad etildi.')
            ->with('toast_type', 'warning');
    }

    public function destroy(Request $request, Course $course, CourseEnrollment $enrollment)
    {
        $this->authorizeManageCourse($course);
        abort_unless((int) $enrollment->course_id === (int) $course->id, 404);

        $enrollment->delete();

        return back()
            ->with('success', 'Yozilish olib tashlandi.')
            ->with('toast_type', 'warning');
    }

    private function authorizeManageCourse(Course $course): void
    {
        /** @var User|null $user */
        $user = auth()->user();
        abort_unless($user && ($user->isAdmin() || ($user->isTeacher() && $user->ownsCourse($course))), 403);
    }
}
