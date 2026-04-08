<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Teacher;
use App\Models\Course;
use App\Models\Exam;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function search(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));

        if (mb_strlen($q) < 2) {
            return response()->json(['results' => []]);
        }

        $like = '%' . $q . '%';
        $results = [];

        $posts = Post::query()
            ->where(function ($w) use ($like) {
                $w->where('title', 'like', $like)
                    ->orWhere('title_en', 'like', $like)
                    ->orWhere('short_content', 'like', $like);
            })
            ->select(['id', 'title', 'title_en', 'slug', 'short_content', 'image'])
            ->latest()
            ->limit(5)
            ->get();

        foreach ($posts as $p) {
            $results[] = [
                'type' => 'post',
                'type_label' => 'Yangilik',
                'icon' => 'fa-solid fa-newspaper',
                'title' => localized_model_value($p, 'title'),
                'subtitle' => \Illuminate\Support\Str::limit(localized_model_value($p, 'short_content'), 80),
                'url' => route('post.show', $p),
                'image' => $p->image ? app_storage_asset($p->image) : null,
            ];
        }

        $teachers = Teacher::query()
            ->where('is_active', true)
            ->where(function ($w) use ($like) {
                $w->where('full_name', 'like', $like)
                    ->orWhere('subject', 'like', $like)
                    ->orWhere('subject_en', 'like', $like);
            })
            ->select(['id', 'full_name', 'slug', 'subject', 'subject_en', 'image'])
            ->limit(4)
            ->get();

        foreach ($teachers as $t) {
            $results[] = [
                'type' => 'teacher',
                'type_label' => 'Ustoz',
                'icon' => 'fa-solid fa-chalkboard-user',
                'title' => $t->full_name,
                'subtitle' => localized_model_value($t, 'subject'),
                'url' => route('teacher.show', $t),
                'image' => $t->image ? app_storage_asset($t->image) : null,
            ];
        }

        $courses = Course::query()
            ->where('status', 'published')
            ->where(function ($w) use ($like) {
                $w->where('title', 'like', $like)
                    ->orWhere('title_en', 'like', $like);
            })
            ->select(['id', 'title', 'title_en', 'slug'])
            ->limit(3)
            ->get();

        foreach ($courses as $c) {
            $results[] = [
                'type' => 'course',
                'type_label' => 'Kurs',
                'icon' => 'fa-solid fa-book-open',
                'title' => localized_model_value($c, 'title'),
                'subtitle' => null,
                'url' => route('courses') . '#course-' . $c->id,
                'image' => null,
            ];
        }

        $exams = Exam::query()
            ->where('is_active', true)
            ->where('title', 'like', $like)
            ->select(['id', 'title'])
            ->limit(3)
            ->get();

        foreach ($exams as $e) {
            $results[] = [
                'type' => 'exam',
                'type_label' => 'Imtihon',
                'icon' => 'fa-solid fa-graduation-cap',
                'title' => $e->title,
                'subtitle' => null,
                'url' => route('exam.start.page', $e),
                'image' => null,
            ];
        }

        return response()->json(['results' => $results]);
    }
}
