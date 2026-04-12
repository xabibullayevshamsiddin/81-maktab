<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseEnrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class PublicCourseController extends Controller
{
    public function index(Request $request)
    {
        $page = max(1, (int) $request->query('page', 1));

        $courses = Cache::remember(cache_key_public_courses_page($page), now()->addMinutes(10), function () use ($request) {
            return Course::query()
                ->select([
                    'id',
                    'teacher_id',
                    'created_by',
                    'title',
                    'title_en',
                    'price',
                    'price_en',
                    'duration',
                    'duration_en',
                    'description',
                    'description_en',
                    'image',
                    'start_date',
                    'status',
                    'created_at',
                ])
                ->with(['teacher:id,full_name,image,is_active'])
                ->where('status', Course::STATUS_PUBLISHED)
                ->whereHas('teacher', function ($query) {
                    $query->where('is_active', true);
                })
                ->latest()
                ->paginate(9)
                ->appends($request->query());
        });

        $enrolledCourseIds = collect();
        $enrollmentByCourseId = collect();
        if (auth()->check()) {
            $rows = CourseEnrollment::query()
                ->where('user_id', auth()->id())
                ->whereIn('course_id', $courses->getCollection()->pluck('id'))
                ->get();

            $enrollmentByCourseId = $rows->keyBy('course_id');
            $enrolledCourseIds = $rows
                ->where('status', CourseEnrollment::STATUS_APPROVED)
                ->pluck('course_id');
        }

        return view('courses', compact('courses', 'enrolledCourseIds', 'enrollmentByCourseId'));
    }

    public function show(Course $course)
    {
        $course = Cache::remember(cache_key_public_course_show((int) $course->id), now()->addMinutes(10), function () use ($course) {
            return Course::query()
                ->select([
                    'id',
                    'teacher_id',
                    'created_by',
                    'title',
                    'title_en',
                    'price',
                    'price_en',
                    'duration',
                    'duration_en',
                    'description',
                    'description_en',
                    'image',
                    'start_date',
                    'status',
                ])
                ->with(['teacher:id,full_name,slug,subject,subject_en,lavozim,lavozim_en,toifa,toifa_en,experience_years,grades,achievements,achievements_en,image,is_active'])
                ->findOrFail($course->id);
        });

        abort_unless(
            $course->status === Course::STATUS_PUBLISHED
            && $course->teacher
            && $course->teacher->is_active,
            404
        );

        return view('courses.show', compact('course'));
    }
}
