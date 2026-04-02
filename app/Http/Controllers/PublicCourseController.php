<?php

namespace App\Http\Controllers;

use App\Models\Course;

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

        return view('courses', compact('courses'));
    }
}

