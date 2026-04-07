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
        } else {
            // Admin should only see published or draft courses in the main index,
            // pending ones are moved to the requests page.
            $query->where('status', '!=', Course::STATUS_PENDING_VERIFICATION);
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

    public function requests(Request $request)
    {
        $user = auth()->user();
        abort_unless($user && $user->isAdmin(), 403);

        $q = trim((string) $request->query('q', ''));

        $query = Course::query()
            ->with(['teacher', 'creator'])
            ->where('status', Course::STATUS_PENDING_VERIFICATION)
            ->latest();

        if ($q !== '') {
            $query->where(function ($w) use ($q): void {
                $w->where('title', 'like', '%'.$q.'%')
                    ->orWhereHas('teacher', function ($t) use ($q): void {
                        $t->where('full_name', 'like', '%'.$q.'%');
                    });
            });
        }

        $courses = $query->paginate(10)->withQueryString();

        return view('admin.courses.requests', compact('courses'));
    }

    public function updateStatus(Request $request, Course $course)
    {
        $user = auth()->user();
        abort_unless($user && ($user->isAdmin() || ($user->isTeacher() && $user->ownsCourse($course))), 403);

        $validated = $request->validate([
            'status' => ['required', 'in:draft,pending_verification,published'],
            'rejection_reason' => ['nullable', 'string', 'max:500'],
        ]);

        $status = $validated['status'];
        $course->update([
            'status' => $status,
            'rejection_reason' => $status === 'published' ? null : ($validated['rejection_reason'] ?? $course->rejection_reason),
        ]);
        
        forget_public_course_caches();

        $msg = "Kurs holati yangilandi.";
        if ($status === 'published') {
            $msg = "Kurs muvaffaqiyatli tasdiqlandi va ommaga e'lon qilindi!";
        } elseif ($status === 'draft' && filled($validated['rejection_reason'] ?? '')) {
            $msg = "Kurs rad etildi va ustozga qaytarildi.";
        }

        return back()
            ->with('success', $msg)
            ->with('toast_type', $status === 'published' ? 'success' : 'warning');
    }
}
