<?php

namespace App\Http\Controllers;

use App\Models\ChatMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function messages(Request $request): JsonResponse
    {
        $afterId = (int) $request->query('after', 0);

        $query = ChatMessage::query()
            ->with('user:id,first_name,name,role_id,avatar')
            ->with('user.roleRelation:id,name');

        if ($afterId > 0) {
            $query->where('id', '>', $afterId);
        } else {
            $query->latest('id')->limit(50);
        }

        $messages = $afterId > 0
            ? $query->orderBy('id')->get()
            : $query->get()->reverse()->values();

        $currentUserId = (int) $request->user()->id;

        $data = $messages->map(function (ChatMessage $m) use ($currentUserId) {
            $user = $m->user;
            $role = $user?->roleRelation?->name ?? 'user';
            $isSuperAdmin = $role === 'super_admin';
            $isAdmin = in_array($role, ['super_admin', 'admin'], true);
            $avatarUrl = $user && $user->avatar ? app_storage_asset($user->avatar) : null;

            return [
                'id' => $m->id,
                'body' => e($m->body),
                'is_mine' => (int) $m->user_id === $currentUserId,
                'is_admin' => $isAdmin,
                'is_super_admin' => $isSuperAdmin,
                'user_name' => $user->first_name ?: $user->name ?? '?',
                'user_initial' => mb_strtoupper(mb_substr(trim($user->first_name ?: $user->name ?? '?'), 0, 1)),
                'avatar_url' => $avatarUrl,
                'time' => $m->created_at?->format('H:i'),
                'date' => $m->created_at?->format('d.m'),
            ];
        });

        return response()->json([
            'messages' => $data,
            'last_id' => $messages->last()?->id ?? $afterId,
        ]);
    }

    public function send(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'body' => ['required', 'string', 'max:1000'],
        ]);

        $message = ChatMessage::create([
            'user_id' => $request->user()->id,
            'body' => trim($validated['body']),
        ]);

        return response()->json(['ok' => true, 'id' => $message->id]);
    }
}
