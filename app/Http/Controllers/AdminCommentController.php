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

            $comments = $query->paginate(25)->appends(request()->query());
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

            $comments = $query->paginate(25)->appends(request()->query());
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
            ->with('toast_type', 'success');
    }

    public function blockUser(Request $request, User $user)
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

        $validated = $request->validate([
            'duration' => ['required', 'in:1h,1d,1w,1m,forever'],
            'reason' => ['required', 'string', 'max:500'],
        ]);

        $blockedUntil = match ($validated['duration']) {
            '1h' => now()->addHour(),
            '1d' => now()->addDay(),
            '1w' => now()->addWeek(),
            '1m' => now()->addMonth(),
            'forever' => null,
        };

        $durationText = match ($validated['duration']) {
            '1h' => '1 soat',
            '1d' => '1 kun',
            '1w' => '1 hafta',
            '1m' => '1 oy',
            'forever' => 'Butun umr',
        };

        $user->update([
            'is_blocked' => true,
            'is_active' => false,
            'blocked_until' => $blockedUntil,
            'blocked_reason' => $validated['reason'],
            'blocked_by' => $current->id,
        ]);

        // Telegram xabar
        if ($user->telegram_chat_id) {
            $adminName = htmlspecialchars($current->buildNameFromParts() ?: $current->name);
            $userName = htmlspecialchars($user->buildNameFromParts() ?: $user->name);
            $unblockTime = $blockedUntil ? $blockedUntil->format('d.m.Y H:i') : 'Cheksiz';

            $text = "\ud83d\udeab <b>Hisobingiz bloklandi</b>\n"
                ."\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\n\n"
                ."\ud83d\udc64 <b>Foydalanuvchi:</b> {$userName}\n"
                ."\ud83d\udc68\u200d\ud83d\udcbb <b>Bloklagan:</b> {$adminName}\n"
                ."\u23f0 <b>Muddat:</b> {$durationText}\n"
                ."\ud83d\udcc5 <b>Qachon gacha:</b> {$unblockTime}\n\n"
                ."\ud83d\udcdd <b>Sabab:</b>\n"
                .htmlspecialchars($validated['reason']) . "\n\n"
                ."\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501";

            $telegram = app(\App\Services\TelegramService::class);
            $telegram->sendMessage((int) $user->telegram_chat_id, $text);
        }

        return redirect()
            ->back()
            ->with('success', $user->name.' '.$durationText.' muddatga bloklandi.')
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
