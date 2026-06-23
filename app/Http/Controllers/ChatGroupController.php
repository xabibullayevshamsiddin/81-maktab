<?php

namespace App\Http\Controllers;

use App\Models\ChatGroup;
use App\Models\ChatGroupJoinRequest;
use App\Models\ChatGroupMember;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatGroupController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $groups = ChatGroup::orderBy('name')
            ->get()
            ->map(function (ChatGroup $group) use ($user) {
                $isOwner = (int) $group->owner_id === (int) $user->id;
                $isMember = ChatGroupMember::query()
                    ->where('chat_group_id', $group->id)
                    ->where('user_id', $user->id)
                    ->exists();

                $requestEntry = ChatGroupJoinRequest::query()
                    ->where('chat_group_id', $group->id)
                    ->where('user_id', $user->id)
                    ->latest('id')
                    ->first();

                $pendingRequestsCount = ($isOwner || $user->isAdmin() || $user->isModerator())
                    ? ChatGroupJoinRequest::query()
                        ->where('chat_group_id', $group->id)
                        ->where('status', ChatGroupJoinRequest::STATUS_PENDING)
                        ->count()
                    : 0;

                return [
                    'id' => (int) $group->id,
                    'name' => $group->name,
                    'description' => (string) $group->description,
                    'is_owner' => $isOwner,
                    'is_member' => $isMember,
                    'can_manage' => $isOwner || $user->isAdmin() || $user->isModerator(),
                    'request_status' => $requestEntry?->status,
                    'pending_requests_count' => $pendingRequestsCount,
                ];
            })
            ->values()
            ->all();

        return response()->json(['groups' => $groups]);
    }

    public function join(Request $request, ChatGroup $group): JsonResponse
    {
        $user = $request->user();

        if ((int) $group->owner_id === (int) $user->id) {
            return response()->json(['ok' => true]);
        }

        if (ChatGroupMember::query()
            ->where('chat_group_id', $group->id)
            ->where('user_id', $user->id)
            ->exists()) {
            return response()->json(['ok' => true]);
        }

        $existingRequest = ChatGroupJoinRequest::query()
            ->where('chat_group_id', $group->id)
            ->where('user_id', $user->id)
            ->where('status', ChatGroupJoinRequest::STATUS_PENDING)
            ->first();

        if ($existingRequest) {
            return response()->json(['ok' => true, 'pending' => true]);
        }

        ChatGroupJoinRequest::create([
            'chat_group_id' => $group->id,
            'user_id' => $user->id,
            'status' => ChatGroupJoinRequest::STATUS_PENDING,
        ]);

        return response()->json(['ok' => true, 'pending' => true]);
    }

    public function requests(Request $request, ChatGroup $group): JsonResponse
    {
        $user = $request->user();

        if (! $this->userCanManageGroup($group, $user)) {
            return response()->json(['ok' => false], 403);
        }

        $requests = ChatGroupJoinRequest::query()
            ->where('chat_group_id', $group->id)
            ->where('status', ChatGroupJoinRequest::STATUS_PENDING)
            ->with('user:id,first_name,name,avatar')
            ->orderBy('created_at')
            ->get()
            ->map(function (ChatGroupJoinRequest $request) {
                $user = $request->user;

                return [
                    'id' => (int) $request->id,
                    'user_id' => (int) $request->user_id,
                    'user_name' => $user?->first_name ?: $user?->name ?: '?',
                    'user_avatar' => $user && $user->avatar ? app_storage_asset($user->avatar) : null,
                    'created_at' => $request->created_at?->format('d.m.Y H:i'),
                ];
            })
            ->values();

        return response()->json(['requests' => $requests]);
    }

    public function accept(Request $request, ChatGroup $group, ChatGroupJoinRequest $joinRequest): JsonResponse
    {
        $user = $request->user();

        if (! $this->userCanManageGroup($group, $user)) {
            return response()->json(['ok' => false], 403);
        }

        if ($joinRequest->chat_group_id !== $group->id) {
            return response()->json(['ok' => false], 404);
        }

        if (! $joinRequest->isPending()) {
            return response()->json(['ok' => false, 'error' => 'So‘rov allaqachon qayta ko‘rilgan.'], 422);
        }

        $joinRequest->update(['status' => ChatGroupJoinRequest::STATUS_ACCEPTED]);

        ChatGroupMember::firstOrCreate([
            'chat_group_id' => $group->id,
            'user_id' => $joinRequest->user_id,
        ]);

        return response()->json(['ok' => true]);
    }

    public function reject(Request $request, ChatGroup $group, ChatGroupJoinRequest $joinRequest): JsonResponse
    {
        $user = $request->user();

        if (! $this->userCanManageGroup($group, $user)) {
            return response()->json(['ok' => false], 403);
        }

        if ($joinRequest->chat_group_id !== $group->id) {
            return response()->json(['ok' => false], 404);
        }

        if (! $joinRequest->isPending()) {
            return response()->json(['ok' => false, 'error' => 'So‘rov allaqachon qayta ko‘rilgan.'], 422);
        }

        $joinRequest->update(['status' => ChatGroupJoinRequest::STATUS_REJECTED]);

        return response()->json(['ok' => true]);
    }

    private function userCanManageGroup(ChatGroup $group, User $user): bool
    {
        if ((int) $group->owner_id === (int) $user->id) {
            return true;
        }

        return $user->isAdmin() || $user->isModerator();
    }
}
