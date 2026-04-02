<?php

namespace App\Http\Controllers;

use App\Models\TeacherComment;
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

    private function canManageComment(TeacherComment $comment): bool
    {
        if (! auth()->check()) {
            return false;
        }

        $user = auth()->user();

        if ((int) $comment->user_id === (int) $user->id) {
            return true;
        }

        return $user->isAdmin();
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
