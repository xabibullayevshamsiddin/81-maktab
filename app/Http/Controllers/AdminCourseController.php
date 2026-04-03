<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;

class AdminCourseController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        abort_unless($user && ($user->isAdmin() || $user->isTeacher()), 403);

        $query = Course::query()
            ->with(['teacher', 'creator'])
            ->withCount('enrollments')
            ->latest();

        if (! $user->isAdmin()) {
            $query->where('created_by', $user->id);
        }

        $courses = $query->get();

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

        return back()
            ->with('success', "Kurs holati yangilandi.")
            ->with('toast_type', 'warning');
    }
}

