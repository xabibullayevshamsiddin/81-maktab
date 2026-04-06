<?php

namespace App\Http\Controllers;

use App\Models\Teacher;
use App\Models\TeacherComment;
use App\Models\TeacherCommentLike;
use App\Models\TeacherLike;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class PublicTeacherController extends Controller
{
    public function index(Request $request)
    {
        $page = max(1, (int) $request->query('page', 1));

        $teachers = Cache::remember(cache_key_public_teachers_page($page), now()->addMinutes(10), function () use ($request) {
            return Teacher::query()
                ->select([
                    'id',
                    'full_name',
                    'slug',
                    'subject',
                    'subject_en',
                    'experience_years',
                    'grades',
                    'achievements',
                    'achievements_en',
                    'bio',
                    'bio_en',
                    'image',
                    'sort_order',
                    'is_active',
                ])
                ->withCount('likes')
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('full_name')
                ->paginate(3)
                ->appends($request->query());
        });

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

        return view('teacher', compact('teachers', 'likedTeacherIds'));
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
            ->whereNull('parent_id')
            ->with([
                'user.roleRelation',
                'replies' => function ($query) use ($teacher) {
                    $query->where('teacher_id', $teacher->id)
                        ->with('user.roleRelation')
                        ->withCount('likes')
                        ->latest();
                },
            ])
            ->withCount('likes')
            ->latest()
            ->get();

        $likedCommentIds = $this->likedTeacherCommentIdsForUser($comments);

        return view('teacherShow', compact('teacher', 'comments', 'liked', 'likedCommentIds'));
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
