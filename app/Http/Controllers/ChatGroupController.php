<?php

namespace App\Http\Controllers;

use App\Models\ChatGroup;
use App\Models\ChatGroupJoinRequest;
use App\Models\ChatGroupMember;
use App\Models\ChatMessage;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ChatGroupController extends Controller
{
    private const MAX_GROUPS_JOINED = 3;

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $groups = ChatGroup::orderBy('name')
            ->get()
            ->map(function (ChatGroup $group) use ($user) {
                $isOwner = (int) $group->owner_id === (int) $user->id;

                $membership = ChatGroupMember::query()
                    ->where('chat_group_id', $group->id)
                    ->where('user_id', $user->id)
                    ->first();

                $isMember = $membership !== null;
                $memberRole = $membership?->role;

                $requestEntry = ChatGroupJoinRequest::query()
                    ->where('chat_group_id', $group->id)
                    ->where('user_id', $user->id)
                    ->latest('id')
                    ->first();

                $pendingRequestsCount = ($isOwner || $user->isAdmin() || $user->isModerator() || $memberRole === ChatGroupMember::ROLE_ADMIN)
                    ? ChatGroupJoinRequest::query()
                        ->where('chat_group_id', $group->id)
                        ->where('status', ChatGroupJoinRequest::STATUS_PENDING)
                        ->count()
                    : 0;

                $memberCount = ChatGroupMember::query()
                    ->where('chat_group_id', $group->id)
                    ->count();

                $canManage = $isOwner || $user->isAdmin() || $user->isModerator() || $memberRole === ChatGroupMember::ROLE_ADMIN;
                $canEdit = $isOwner || $user->isAdmin();
                $canView = $this->userCanViewGroup($group, $user);
                $canSend = $canView && ($isOwner || $user->isAdmin() || $isMember);

                return [
                    'id' => (int) $group->id,
                    'name' => $group->name,
                    'description' => (string) $group->description,
                    'image' => $group->image ? app_storage_asset($group->image) : null,
                    'privacy' => $group->privacy,
                    'owner_id' => (int) $group->owner_id,
                    'is_owner' => $isOwner,
                    'is_member' => $isMember,
                    'member_role' => $memberRole,
                    'can_manage' => $canManage,
                    'can_edit' => $canEdit,
                    'can_view' => $canView,
                    'can_send' => $canSend,
                    'request_status' => $requestEntry?->status,
                    'pending_requests_count' => $pendingRequestsCount,
                    'member_count' => $memberCount,
                ];
            })
            ->values()
            ->all();

        return response()->json(['groups' => $groups]);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        if (ChatGroup::query()->where('owner_id', $user->id)->exists()) {
            return response()->json(['ok' => false, 'error' => 'Siz allaqachon bitta gruppa yaratgansiz. Har bir foydalanuvchi faqat 1 ta gruppa ocha oladi.'], 422);
        }

        $data = $request->validate([
            'name' => 'required|string|max:120|min:2',
            'description' => 'nullable|string|max:500',
            'privacy' => 'nullable|in:open,closed',
        ]);

        $group = ChatGroup::create([
            'owner_id' => $user->id,
            'name' => $data['name'],
            'description' => $data['description'] ?? '',
            'privacy' => $data['privacy'] ?? ChatGroup::PRIVACY_CLOSED,
        ]);

        ChatGroupMember::create([
            'chat_group_id' => $group->id,
            'user_id' => $user->id,
            'role' => ChatGroupMember::ROLE_ADMIN,
        ]);

        return response()->json(['ok' => true, 'group' => [
            'id' => (int) $group->id,
            'name' => $group->name,
        ]]);
    }

    public function update(Request $request, ChatGroup $group): JsonResponse
    {
        $user = $request->user();

        if (! $this->userCanEditGroup($group, $user)) {
            return response()->json(['ok' => false], 403);
        }

        $data = $request->validate([
            'name' => 'sometimes|string|max:120|min:2',
            'description' => 'nullable|string|max:500',
            'privacy' => 'sometimes|in:open,closed',
        ]);

        if (isset($data['name'])) {
            $group->name = $data['name'];
        }
        if (array_key_exists('description', $data)) {
            $group->description = $data['description'] ?? '';
        }
        if (isset($data['privacy'])) {
            $group->privacy = $data['privacy'];
        }

        $group->save();

        return response()->json(['ok' => true, 'group' => [
            'id' => (int) $group->id,
            'name' => $group->name,
            'description' => (string) $group->description,
            'privacy' => $group->privacy,
        ]]);
    }

    public function updateImage(Request $request, ChatGroup $group): JsonResponse
    {
        $user = $request->user();

        if (! $this->userCanEditGroup($group, $user)) {
            return response()->json(['ok' => false], 403);
        }

        $request->validate(['image' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048']);

        if ($group->image) {
            Storage::disk('public')->delete($group->image);
        }

        $path = $request->file('image')->store('chat-groups', 'public');
        $group->image = $path;
        $group->save();

        return response()->json(['ok' => true, 'image' => app_storage_asset($path)]);
    }

    public function deleteImage(Request $request, ChatGroup $group): JsonResponse
    {
        $user = $request->user();

        if (! $this->userCanEditGroup($group, $user)) {
            return response()->json(['ok' => false], 403);
        }

        if ($group->image) {
            Storage::disk('public')->delete($group->image);
            $group->image = null;
            $group->save();
        }

        return response()->json(['ok' => true]);
    }

    public function destroy(Request $request, ChatGroup $group): JsonResponse
    {
        $user = $request->user();

        if (! $this->userCanEditGroup($group, $user)) {
            return response()->json(['ok' => false], 403);
        }

        if ($group->image) {
            Storage::disk('public')->delete($group->image);
        }

        ChatMessage::query()
            ->where('chat_group_id', $group->id)
            ->delete();

        $group->delete();

        return response()->json(['ok' => true]);
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

        $joinedCount = ChatGroupMember::query()
            ->where('user_id', $user->id)
            ->count();

        if ($joinedCount >= self::MAX_GROUPS_JOINED) {
            return response()->json(['ok' => false, 'error' => "Siz ko'pi bilan " . self::MAX_GROUPS_JOINED . " ta gruppaga a'zo bo'lishingiz mumkin."], 422);
        }

        if ($group->privacy === ChatGroup::PRIVACY_OPEN) {
            ChatGroupMember::create([
                'chat_group_id' => $group->id,
                'user_id' => $user->id,
                'role' => ChatGroupMember::ROLE_MEMBER,
            ]);

            ChatGroupJoinRequest::query()
                ->where('chat_group_id', $group->id)
                ->where('user_id', $user->id)
                ->update(['status' => ChatGroupJoinRequest::STATUS_ACCEPTED]);

            return response()->json(['ok' => true, 'joined' => true]);
        }

        $existingRequest = ChatGroupJoinRequest::query()
            ->where('chat_group_id', $group->id)
            ->where('user_id', $user->id)
            ->first();

        if ($existingRequest?->isPending()) {
            return response()->json(['ok' => true, 'pending' => true]);
        }

        ChatGroupJoinRequest::updateOrCreate([
            'chat_group_id' => $group->id,
            'user_id' => $user->id,
        ], [
            'status' => ChatGroupJoinRequest::STATUS_PENDING,
        ]);

        return response()->json(['ok' => true, 'pending' => true]);
    }

    public function leave(Request $request, ChatGroup $group): JsonResponse
    {
        $user = $request->user();

        if ((int) $group->owner_id === (int) $user->id) {
            return response()->json(['ok' => false, 'error' => 'Gruppa egasi gruppani tark eta olmaydi. Iloji bo\'lsa, gruppani o\'chiring.'], 422);
        }

        ChatGroupMember::query()
            ->where('chat_group_id', $group->id)
            ->where('user_id', $user->id)
            ->delete();

        return response()->json(['ok' => true]);
    }

    public function members(Request $request, ChatGroup $group): JsonResponse
    {
        $user = $request->user();

        if (! $this->userCanViewGroup($group, $user)) {
            return response()->json(['ok' => false], 403);
        }

        $members = ChatGroupMember::query()
            ->where('chat_group_id', $group->id)
            ->with('user:id,first_name,name,role_id,avatar,is_active')
            ->get()
            ->map(function (ChatGroupMember $m) use ($group) {
                $u = $m->user;
                return [
                    'id' => (int) $m->id,
                    'user_id' => (int) $m->user_id,
                    'role' => $m->role,
                    'is_owner' => (int) $group->owner_id === (int) $m->user_id,
                    'user_name' => $u?->first_name ?: $u?->name ?: '?',
                    'user_avatar' => $u && $u->avatar ? app_storage_asset($u->avatar) : null,
                ];
            })
            ->values();

        return response()->json(['members' => $members]);
    }

    public function updateMemberRole(Request $request, ChatGroup $group, ChatGroupMember $member): JsonResponse
    {
        $user = $request->user();

        if (! $this->userCanEditGroup($group, $user)) {
            return response()->json(['ok' => false], 403);
        }

        if ($member->chat_group_id !== $group->id) {
            return response()->json(['ok' => false], 404);
        }

        if ((int) $group->owner_id === (int) $member->user_id) {
            return response()->json(['ok' => false, 'error' => 'Gruppa egasining rolini o\'zgartirib bo\'lmaydi.'], 422);
        }

        $data = $request->validate(['role' => 'required|in:member,admin']);
        $member->update(['role' => $data['role']]);

        return response()->json(['ok' => true]);
    }

    public function removeMember(Request $request, ChatGroup $group, ChatGroupMember $member): JsonResponse
    {
        $user = $request->user();

        if (! $this->userCanEditGroup($group, $user)) {
            return response()->json(['ok' => false], 403);
        }

        if ($member->chat_group_id !== $group->id) {
            return response()->json(['ok' => false], 404);
        }

        if ((int) $group->owner_id === (int) $member->user_id) {
            return response()->json(['ok' => false, 'error' => 'Gruppa egasini o\'chirib bo\'lmaydi.'], 422);
        }

        $member->delete();

        return response()->json(['ok' => true]);
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
            return response()->json(['ok' => false, 'error' => 'So\'rov allaqachon qayta ko\'rilgan.'], 422);
        }

        $joinedCount = ChatGroupMember::query()
            ->where('user_id', $joinRequest->user_id)
            ->count();

        if ($joinedCount >= self::MAX_GROUPS_JOINED) {
            return response()->json(['ok' => false, 'error' => 'Foydalanuvchi gruppalar limitiga yetgan (max ' . self::MAX_GROUPS_JOINED . ').'], 422);
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
            return response()->json(['ok' => false, 'error' => 'So\'rov allaqachon qayta ko\'rilgan.'], 422);
        }

        $joinRequest->update(['status' => ChatGroupJoinRequest::STATUS_REJECTED]);

        return response()->json(['ok' => true]);
    }

    private function userCanManageGroup(ChatGroup $group, User $user): bool
    {
        if ((int) $group->owner_id === (int) $user->id) {
            return true;
        }

        if ($user->isAdmin() || $user->isModerator()) {
            return true;
        }

        return ChatGroupMember::query()
            ->where('chat_group_id', $group->id)
            ->where('user_id', $user->id)
            ->where('role', ChatGroupMember::ROLE_ADMIN)
            ->exists();
    }

    private function userCanViewGroup(ChatGroup $group, User $user): bool
    {
        if ((int) $group->owner_id === (int) $user->id) {
            return true;
        }

        if ($user->isAdmin() || $user->isModerator()) {
            return true;
        }

        return ChatGroupMember::query()
            ->where('chat_group_id', $group->id)
            ->where('user_id', $user->id)
            ->exists();
    }

    private function userCanEditGroup(ChatGroup $group, User $user): bool
    {
        if ((int) $group->owner_id === (int) $user->id) {
            return true;
        }

        return $user->isAdmin();
    }
}
