<?php

namespace App\Http\Controllers;

use App\Models\Teacher;
use App\Models\TeacherComment;
use App\Models\TeacherCommentLike;
use App\Models\TeacherLike;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class PublicTeacherController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $selectedSubject = trim((string) $request->query('subject', ''));

        $query = Teacher::query()
            ->select([
                'id',
                'full_name',
                'slug',
                'subject',
                'subject_en',
                'lavozim',
                'lavozim_en',
                'toifa',
                'toifa_en',
                'experience_years',
                'grades',
                'achievements',
                'achievements_en',
                'image',
                'sort_order',
                'is_active',
            ])
            ->withCount('likes')
            ->where('is_active', true)
            ->whereNotNull('image')
            ->where('image', '!=', '');

        if ($q !== '') {
            $query->where(function ($sub) use ($q): void {
                $sub->where('full_name', 'like', "%{$q}%")
                    ->orWhere('subject', 'like', "%{$q}%")
                    ->orWhere('subject_en', 'like', "%{$q}%")
                    ->orWhere('lavozim', 'like', "%{$q}%")
                    ->orWhere('lavozim_en', 'like', "%{$q}%");
            });
        }

        if ($selectedSubject !== '') {
            $query->where(function ($sub) use ($selectedSubject): void {
                $sub->where('subject', $selectedSubject)
                    ->orWhere('subject_en', $selectedSubject);
            });
        }

        $teachers = $query
            ->orderBy('sort_order')
            ->orderBy('full_name')
            ->paginate(12)
            ->withQueryString();

        // Collect all unique subjects for the filter dropdown (from all active teachers)
        $allSubjects = Teacher::query()
            ->where('is_active', true)
            ->whereNotNull('image')
            ->where('image', '!=', '')
            ->whereNotNull('subject')
            ->where('subject', '!=', '')
            ->orderBy('subject')
            ->pluck('subject')
            ->unique()
            ->values();

        $likedTeacherIds = collect();
        if (auth()->check()) {
            $ids = $teachers->getCollection()->pluck('id');
            if ($ids->isNotEmpty()) {
                $likedTeacherIds = TeacherLike::query()
                    ->where('user_id', auth()->id())
                    ->whereIn('teacher_id', $ids)
                    ->pluck('teacher_id');
            }
        }

        $teacherStats = $this->teacherPageStats();

        return view('teacher', compact('teachers', 'likedTeacherIds', 'teacherStats', 'q', 'selectedSubject', 'allSubjects'));
    }

    /**
     * @return array{experienced_teachers:int, subject_areas:int, students:int, satisfaction_percent:int}
     */
    private function teacherPageStats(): array
    {
        $activeTeachers = Teacher::query()
            ->where('is_active', true)
            ->whereNotNull('image')
            ->where('image', '!=', '');

        $experienced = (clone $activeTeachers)->where('experience_years', '>=', 3)->count();
        if ($experienced === 0) {
            $experienced = (clone $activeTeachers)->count();
        }

        $subjectAreas = (clone $activeTeachers)
            ->whereNotNull('subject')
            ->where('subject', '!=', '')
            ->distinct()
            ->count('subject');

        $students = User::query()->active()->byRole(User::ROLE_USER)->count();

        $commentBase = TeacherComment::query()
            ->whereNotNull('teacher_id')
            ->whereNull('parent_id')
            ->where('is_approved', true);

        $totalApproved = (clone $commentBase)->count();
        if ($totalApproved > 0) {
            $withLikes = (clone $commentBase)->has('likes')->count();
            $satisfaction = (int) round(min(100, max(0, (100 * $withLikes) / $totalApproved)));
        } else {
            $satisfaction = 96;
        }

        return [
            'experienced_teachers' => $experienced,
            'subject_areas' => $subjectAreas,
            'students' => $students,
            'satisfaction_percent' => $satisfaction,
        ];
    }

    public function show(Teacher $teacher)
    {
        abort_unless($teacher->is_active, 404);
        $teacher->loadCount('likes');

        $liked = false;
        if (auth()->check()) {
            $liked = $teacher->likes()
                ->where('user_id', auth()->id())
                ->exists();
        }

        $comments = TeacherComment::query()
            ->where('teacher_id', $teacher->id)
            ->where('is_approved', true)
            ->whereNull('parent_id')
            ->with([
                'user.roleRelation',
                'replies' => function ($query) use ($teacher) {
                    $query->where('teacher_id', $teacher->id)
                        ->where('is_approved', true)
                        ->with('user.roleRelation')
                        ->withCount('likes')
                        ->latest();
                },
            ])
            ->withCount('likes')
            ->latest()
            ->get();

        $likedCommentIds = $this->likedTeacherCommentIdsForUser($comments);

        $relatedTeachers = $this->relatedTeachersFor($teacher, 3);

        $likedTeacherIds = collect();
        if (auth()->check()) {
            $teacherIds = collect([$teacher->id])->merge($relatedTeachers->pluck('id'));
            if ($teacherIds->isNotEmpty()) {
                $likedTeacherIds = TeacherLike::query()
                    ->where('user_id', auth()->id())
                    ->whereIn('teacher_id', $teacherIds)
                    ->pluck('teacher_id');
            }
        }

        return view('teacherShow', compact(
            'teacher',
            'comments',
            'liked',
            'likedCommentIds',
            'relatedTeachers',
            'likedTeacherIds'
        ));
    }

    private function relatedTeachersFor(Teacher $teacher, int $limit = 3): Collection
    {
        $q = Teacher::query()
            ->select([
                'id',
                'full_name',
                'slug',
                'subject',
                'subject_en',
                'lavozim',
                'lavozim_en',
                'toifa',
                'toifa_en',
                'experience_years',
                'grades',
                'achievements',
                'achievements_en',
                'image',
                'sort_order',
                'is_active',
            ])
            ->withCount('likes')
            ->where('is_active', true)
            ->whereNotNull('image')
            ->where('image', '!=', '')
            ->where('id', '!=', $teacher->id);

        if (filled($teacher->subject)) {
            $q->orderByRaw('CASE WHEN subject = ? THEN 0 ELSE 1 END', [$teacher->subject]);
        } elseif (filled($teacher->lavozim)) {
            $q->orderByRaw('CASE WHEN lavozim = ? THEN 0 ELSE 1 END', [$teacher->lavozim]);
        }

        return $q->orderBy('sort_order')->orderBy('full_name')->limit($limit)->get();
    }

    public function toggleLike(Request $request, Teacher $teacher)
    {
        abort_unless(auth()->check(), 403);
        abort_unless($teacher->is_active, 404);

        $existing = $teacher->likes()
            ->where('user_id', auth()->id())
            ->first();

        if ($existing) {
            $existing->delete();
            $likesCount = $teacher->likes()->count();

            if ($request->wantsJson()) {
                return response()->json([
                    'ok' => true,
                    'message' => 'Like olib tashlandi.',
                    'liked' => false,
                    'likes_count' => $likesCount,
                    'toast_type' => 'warning',
                ]);
            }

            return back()
                ->with('success', 'Like olib tashlandi.')
                ->with('toast_type', 'warning');
        }

        try {
            $teacher->likes()->create([
                'user_id' => auth()->id(),
            ]);
        } catch (QueryException $e) {
            // Unique duplicate holatda ham counter qaytarib yuboramiz
        }

        $likesCount = $teacher->likes()->count();

        if ($request->wantsJson()) {
            return response()->json([
                'ok' => true,
                'message' => "Like qo'shildi.",
                'liked' => true,
                'likes_count' => $likesCount,
                'toast_type' => 'success',
            ]);
        }

        return back()
            ->with('success', "Like qo'shildi.")
            ->with('toast_type', 'success');
    }

    private function likedTeacherCommentIdsForUser(Collection $comments): Collection
    {
        if (! auth()->check()) {
            return collect();
        }

        $ids = $this->flattenTeacherCommentIds($comments);
        if ($ids->isEmpty()) {
            return collect();
        }

        return TeacherCommentLike::query()
            ->where('user_id', auth()->id())
            ->whereIn('teacher_comment_id', $ids)
            ->pluck('teacher_comment_id');
    }

    private function flattenTeacherCommentIds(Collection $comments): Collection
    {
        $ids = collect();
        foreach ($comments as $c) {
            $ids->push($c->id);
            if ($c->relationLoaded('replies') && $c->replies->isNotEmpty()) {
                $ids = $ids->merge($this->flattenTeacherCommentIds($c->replies));
            }
        }

        return $ids;
    }
}
