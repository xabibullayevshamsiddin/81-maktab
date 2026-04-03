<?php

namespace App\Http\Controllers;

use App\Models\TeacherComment;
use App\Models\TeacherCommentLike;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

class TeacherCommentController extends Controller
{
    public function store(Request $request)
    {
        if ($response = $this->ensureCanInteract($request)) {
            return $response;
        }

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:500'],
            'author_name' => ['nullable', 'string', 'max:80'],
            'parent_id' => ['nullable', 'integer', 'exists:teacher_comments,id'],
        ]);

        $parentComment = null;
        if (! empty($validated['parent_id'])) {
            $parentComment = TeacherComment::query()
                ->whereKey($validated['parent_id'])
                ->firstOrFail();
        }

        $comment = new TeacherComment();
        $comment->body = $validated['body'];
        $comment->author_name = $request->user()?->name ?? ($validated['author_name'] ?? null);
        $comment->user_id = $request->user()?->id;
        $comment->is_approved = true;
        $comment->parent_id = $parentComment?->id;
        $comment->save();

        if ($request->wantsJson()) {
            $comment->refresh();
            $comment->load('user.roleRelation');

            return response()->json([
                'ok' => true,
                'message' => "Izoh qo'shildi.",
                'toast_type' => 'success',
                'comment' => [
                    'id' => $comment->id,
                    'author_name' => $comment->author_name ?? 'Mehmon',
                    'body' => $comment->body,
                    'created_at' => $comment->created_at?->format('d.m.Y H:i'),
                    'parent_id' => $comment->parent_id,
                    'user_id' => $comment->user_id,
                    'role_key' => $comment->user?->role ?? 'guest',
                    'role_label' => $comment->user?->role_label ?? 'Mehmon',
                    'likes_count' => 0,
                ],
            ]);
        }

        return back()
            ->with('success', "Izoh qo'shildi.")
            ->with('toast_type', 'success');
    }

    public function update(Request $request, TeacherComment $comment)
    {
        if (! $this->canManageComment($comment)) {
            abort(403);
        }

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:500'],
        ]);

        $comment->update([
            'body' => $validated['body'],
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'ok' => true,
                'message' => 'Izoh yangilandi.',
                'toast_type' => 'warning',
                'comment' => [
                    'id' => $comment->id,
                    'body' => $comment->body,
                ],
            ]);
        }

        return back()
            ->with('success', 'Izoh yangilandi.')
            ->with('toast_type', 'warning');
    }

    public function destroy(Request $request, TeacherComment $comment)
    {
        if (! $this->canManageComment($comment)) {
            abort(403);
        }

        $comment->delete();

        if ($request->wantsJson()) {
            return response()->json([
                'ok' => true,
                'message' => "Izoh o'chirildi.",
                'toast_type' => 'error',
            ]);
        }

        return back()
            ->with('success', "Izoh o'chirildi.")
            ->with('toast_type', 'error');
    }

    public function toggleCommentLike(Request $request, TeacherComment $comment)
    {
        if ($response = $this->ensureCanInteract($request)) {
            return $response;
        }

        $existing = TeacherCommentLike::query()
            ->where('teacher_comment_id', $comment->id)
            ->where('user_id', auth()->id())
            ->first();

        if ($existing) {
            $existing->delete();
            $likesCount = $comment->likes()->count();

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
            TeacherCommentLike::query()->create([
                'teacher_comment_id' => $comment->id,
                'user_id' => auth()->id(),
            ]);
        } catch (QueryException $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'ok' => false,
                    'message' => "Like qo'shishda xatolik. Qayta urinib ko'ring.",
                    'toast_type' => 'warning',
                ], 422);
            }

            return back()
                ->with('error', "Like qo'shishda xatolik. Qayta urinib ko'ring.")
                ->with('toast_type', 'warning');
        }

        $likesCount = $comment->likes()->count();

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

    private function canManageComment(TeacherComment $comment): bool
    {
        if (! auth()->check()) {
            return false;
        }

        $comment->loadMissing('user.roleRelation');

        return auth()->user()->canManageCommentAsStaff($comment->user, $comment->user_id);
    }

    private function ensureCanInteract(Request $request)
    {
        if (! auth()->check()) {
            return $this->denyInteraction(
                $request,
                "Izoh yozish uchun avval ro'yxatdan o'ting.",
                401
            );
        }

        if (! $request->user()?->isActive()) {
            return $this->denyInteraction(
                $request,
                "Siz block qilingansiz. Izoh yozish mumkin emas.",
                403
            );
        }

        return null;
    }

    private function denyInteraction(Request $request, string $message, int $statusCode)
    {
        if ($request->wantsJson()) {
            return response()->json([
                'ok' => false,
                'message' => $message,
                'toast_type' => 'warning',
            ], $statusCode);
        }

        return back()
            ->with('error', $message)
            ->with('toast_type', 'warning');
    }
}
