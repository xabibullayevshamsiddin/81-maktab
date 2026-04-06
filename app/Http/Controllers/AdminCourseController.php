<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;

class AdminCourseController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        abort_unless($user && ($user->isAdmin() || $user->isTeacher()), 403);

        $q = trim((string) $request->query('q', ''));

        $query = Course::query()
            ->with(['teacher', 'creator'])
            ->withCount('enrollments')
            ->latest();

        if (! $user->isAdmin()) {
            $query->where('created_by', $user->id);
        }

        if ($q !== '') {
            $query->where(function ($w) use ($q): void {
                $w->where('title', 'like', '%'.$q.'%')
                    ->orWhere('description', 'like', '%'.$q.'%')
                    ->orWhere('duration', 'like', '%'.$q.'%')
                    ->orWhereHas('teacher', function ($t) use ($q): void {
                        $t->where('full_name', 'like', '%'.$q.'%');
                    });
            });
        }

        $courses = $query->paginate(10)->withQueryString();

        return view('admin.courses.index', compact('courses'));
    }

    public function updateStatus(Request $request, Course $course)
    {
        $user = auth()->user();
        abort_unless($user && ($user->isAdmin() || ($user->isTeacher() && $user->ownsCourse($course))), 403);

        $validated = $request->validate([
            'status' => ['required', 'in:draft,pending_verification,published'],
        ]);

        $course->update([
            'status' => $validated['status'],
        ]);
        forget_public_course_caches();

        return back()
            ->with('success', "Kurs holati yangilandi.")
            ->with('toast_type', 'warning');
    }
}
