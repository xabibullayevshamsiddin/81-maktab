<?php

namespace App\Http\Controllers;

use App\Models\Teacher;
use App\Models\TeacherComment;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;

class PublicTeacherController extends Controller
{
    public function index()
    {
        $teachers = Teacher::query()
            ->withCount('likes')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('full_name')
            ->get();

        return view('teacher', compact('teachers'));
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
            ->whereNull('parent_id')
            ->with([
                'user.roleRelation',
                'replies' => function ($query) {
                    $query->with('user.roleRelation')->latest();
                },
            ])
            ->latest()
            ->get();

        return view('teacherShow', compact('teacher', 'comments', 'liked'));
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
}

