<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseEnrollment;

class PublicCourseController extends Controller
{
    public function index()
    {
        $courses = Course::query()
            ->with('teacher')
            ->where('status', Course::STATUS_PUBLISHED)
            ->whereHas('teacher', function ($query) {
                $query->where('is_active', true);
            })
            ->latest()
            ->get();

        $enrolledCourseIds = collect();
        $enrollmentByCourseId = collect();
        if (auth()->check()) {
            $rows = CourseEnrollment::query()
                ->where('user_id', auth()->id())
                ->whereIn('course_id', $courses->pluck('id'))
                ->get();

            $enrollmentByCourseId = $rows->keyBy('course_id');
            $enrolledCourseIds = $rows
                ->where('status', CourseEnrollment::STATUS_APPROVED)
                ->pluck('course_id');
        }

        return view('courses', compact('courses', 'enrolledCourseIds', 'enrollmentByCourseId'));
    }
}

