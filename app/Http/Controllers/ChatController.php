<?php

namespace App\Http\Controllers;

use App\Models\ChatMessage;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    private const MAX_MESSAGES = 50;

    public function messages(Request $request): JsonResponse
    {
        $afterId = (int) $request->query('after', 0);
        $currentUser = $request->user()->loadMissing('roleRelation');
        $currentUserId = (int) $currentUser->id;
        $canModerate = $currentUser->isAdmin() || $currentUser->isModerator();

        $query = ChatMessage::query()
            ->with('user:id,first_name,name,role_id,avatar,is_active')
            ->with('user.roleRelation:id,name');

        if ($afterId > 0) {
            $query->where('id', '>', $afterId);
        } else {
            $query->latest('id')->limit(self::MAX_MESSAGES);
        }

        $messages = $afterId > 0
            ? $query->orderBy('id')->get()
            : $query->get()->reverse()->values();

        $data = $messages->map(function (ChatMessage $m) use ($currentUserId, $canModerate) {
            $user = $m->user;
            $role = $user?->roleRelation?->name ?? 'user';
            $isSuperAdmin = $role === 'super_admin';
            $isAdmin = in_array($role, ['super_admin', 'admin'], true);
            // Hostda to‘g‘ri domen/HTTPS uchun asset() (APP_URL) ishonchliroq.
            $avatarUrl = $user && $user->avatar
                ? asset('storage/'.ltrim($user->avatar, '/'))
                : null;
            $isMine = (int) $m->user_id === $currentUserId;

            return [
                'id' => $m->id,
                'user_id' => (int) $m->user_id,
                'body' => e($m->body),
                'is_mine' => $isMine,
                'is_admin' => $isAdmin,
                'is_super_admin' => $isSuperAdmin,
                'can_delete' => $isMine || $canModerate,
                'can_block' => $canModerate && ! $isMine && ! $isAdmin,
                'user_name' => $user->first_name ?: $user->name ?? '?',
                'user_initial' => mb_strtoupper(mb_substr(trim($user->first_name ?: $user->name ?? '?'), 0, 1)),
                'avatar_url' => $avatarUrl,
                'time' => $m->created_at?->format('H:i'),
                'date' => $m->created_at?->format('d.m'),
            ];
        });

        $this->cleanOldMessages();

        return response()->json([
            'messages' => $data,
            'last_id' => $messages->last()?->id ?? $afterId,
            'can_moderate' => $canModerate,
        ]);
    }

    public function send(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user->is_active) {
            return response()->json(['ok' => false, 'error' => 'Sizning akkauntingiz bloklangan.'], 403);
        }

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:1000'],
        ]);

        $message = ChatMessage::create([
            'user_id' => $user->id,
            'body' => trim($validated['body']),
        ]);

        return response()->json(['ok' => true, 'id' => $message->id]);
    }

    public function destroy(Request $request, ChatMessage $chatMessage): JsonResponse
    {
        $user = $request->user()->loadMissing('roleRelation');
        $canModerate = $user->isAdmin() || $user->isModerator();

        if ((int) $chatMessage->user_id !== (int) $user->id && ! $canModerate) {
            return response()->json(['ok' => false], 403);
        }

        $chatMessage->delete();

        return response()->json(['ok' => true]);
    }

    public function blockUser(Request $request, User $user): JsonResponse
    {
        $current = $request->user()->loadMissing('roleRelation');

        if (! $current->isAdmin() && ! $current->isModerator()) {
            return response()->json(['ok' => false], 403);
        }

        if ((int) $user->id === (int) $current->id || $user->isAdmin()) {
            return response()->json(['ok' => false, 'error' => 'Bu foydalanuvchini bloklab bo\'lmaydi.'], 422);
        }

        $user->update(['is_active' => false]);

        return response()->json(['ok' => true]);
    }

    private function cleanOldMessages(): void
    {
        $total = ChatMessage::count();
        if ($total <= self::MAX_MESSAGES) {
            return;
        }

        $keepFromId = ChatMessage::query()
            ->orderByDesc('id')
            ->skip(self::MAX_MESSAGES)
            ->value('id');

        if ($keepFromId) {
            ChatMessage::query()->where('id', '<=', $keepFromId)->delete();
        }
    }
}
