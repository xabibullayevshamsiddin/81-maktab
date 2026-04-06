<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\TeacherComment;
use App\Models\User;
use Illuminate\Http\Request;

class AdminCommentController extends Controller
{
    public function index(Request $request)
    {
        $this->authorizeModerator();

        $type = $request->query('type', 'post');
        if (! in_array($type, ['post', 'teacher'], true)) {
            $type = 'post';
        }

        $q = trim((string) $request->query('q', ''));

        if ($type === 'teacher') {
            $query = TeacherComment::query()
                ->with(['user', 'parent', 'teacher'])
                ->latest();

            if ($q !== '') {
                $query->where(function ($w) use ($q): void {
                    $w->where('body', 'like', '%'.$q.'%')
                        ->orWhereHas('teacher', function ($t) use ($q): void {
                            $t->where('full_name', 'like', '%'.$q.'%');
                        })
                        ->orWhereHas('user', function ($u) use ($q): void {
                            $u->where('name', 'like', '%'.$q.'%')
                                ->orWhere('email', 'like', '%'.$q.'%')
                                ->orWhere('phone', 'like', '%'.$q.'%');
                        });
                });
            }

            $comments = $query->paginate(25)->withQueryString();
        } else {
            $query = Comment::query()
                ->with(['post', 'user', 'parent'])
                ->latest();

            if ($q !== '') {
                $query->where(function ($w) use ($q): void {
                    $w->where('body', 'like', '%'.$q.'%')
                        ->orWhereHas('user', function ($u) use ($q): void {
                            $u->where('name', 'like', '%'.$q.'%')
                                ->orWhere('email', 'like', '%'.$q.'%')
                                ->orWhere('phone', 'like', '%'.$q.'%');
                        })
                        ->orWhereHas('post', function ($p) use ($q): void {
                            $p->where('title', 'like', '%'.$q.'%');
                        });
                });
            }

            $comments = $query->paginate(25)->withQueryString();
        }

        return view('admin.comments.index', compact('comments', 'type'));
    }

    public function edit(string $type, int $id)
    {
        $this->authorizeModerator();
        abort_unless(in_array($type, ['post', 'teacher'], true), 404);

        if ($type === 'post') {
            $comment = Comment::query()->with(['post', 'user'])->findOrFail($id);
        } else {
            $comment = TeacherComment::query()->with(['user', 'teacher'])->findOrFail($id);
        }

        $this->ensureModeratorMayModifyComment($comment);

        return view('admin.comments.edit', compact('comment', 'type'));
    }

    public function update(Request $request, string $type, int $id)
    {
        $this->authorizeModerator();
        abort_unless(in_array($type, ['post', 'teacher'], true), 404);

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:2000'],
        ]);

        if ($type === 'post') {
            $comment = Comment::query()->with('user')->findOrFail($id);
        } else {
            $comment = TeacherComment::query()->with(['user', 'teacher'])->findOrFail($id);
        }

        $this->ensureModeratorMayModifyComment($comment);
        $comment->update(['body' => $validated['body']]);

        return redirect()
            ->route('admin.comments.index', ['type' => $type])
            ->with('success', 'Izoh yangilandi.')
            ->with('toast_type', 'warning');
    }

    public function destroy(string $type, int $id)
    {
        $this->authorizeModerator();
        abort_unless(in_array($type, ['post', 'teacher'], true), 404);

        if ($type === 'post') {
            $comment = Comment::query()->with('user')->findOrFail($id);
        } else {
            $comment = TeacherComment::query()->with(['user', 'teacher'])->findOrFail($id);
        }

        $this->ensureModeratorMayModifyComment($comment);
        $comment->delete();

        return redirect()
            ->route('admin.comments.index', ['type' => $type])
            ->with('success', 'Izoh o‘chirildi.')
            ->with('toast_type', 'error');
    }

    public function blockUser(User $user)
    {
        $this->authorizeBlocker();
        $current = auth()->user();

        if (! $current->canManage($user)) {
            return redirect()
                ->route('admin.comments.index')
                ->with('error', 'Bu foydalanuvchini bloklash huquqingiz yo‘q.')
                ->with('toast_type', 'error');
        }

        if ((int) $user->id === (int) $current->id) {
            return redirect()
                ->route('admin.comments.index')
                ->with('error', 'O‘zingizni bloklay olmaysiz.')
                ->with('toast_type', 'error');
        }

        $user->update(['is_active' => false]);

        return redirect()
            ->back()
            ->with('success', $user->name.' bloklandi.')
            ->with('toast_type', 'warning');
    }

    private function ensureModeratorMayModifyComment(Comment|TeacherComment $comment): void
    {
        abort_unless(
            auth()->user()->canModerateCommentAuthor($comment->user),
            403,
            'Moderator sifatida super admin yoki admin foydalanuvchilarining izohlarini tahrirlash yoki o‘chirish mumkin emas.'
        );
    }

    private function authorizeModerator(): void
    {
        abort_unless(auth()->check() && auth()->user()->isModerator(), 403);
    }

    private function authorizeBlocker(): void
    {
        abort_unless(auth()->check() && auth()->user()->isAdmin(), 403);
    }
}
