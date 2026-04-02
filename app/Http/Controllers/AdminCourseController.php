<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;

class AdminCourseController extends Controller
{
    public function index()
    {
        abort_unless(auth()->check() && auth()->user()->isAdmin(), 403);

        $courses = Course::query()
            ->with(['teacher', 'creator'])
            ->latest()
            ->get();

        return view('admin.courses.index', compact('courses'));
    }

    public function updateStatus(Request $request, Course $course)
    {
        abort_unless(auth()->check() && auth()->user()->isAdmin(), 403);

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

