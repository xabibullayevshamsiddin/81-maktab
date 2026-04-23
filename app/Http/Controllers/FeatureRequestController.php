<?php

namespace App\Http\Controllers;

use App\Models\FeatureRequest;
use App\Models\FeatureRequestReply;
use App\Models\FeatureRequestVote;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class FeatureRequestController extends Controller
{
    public function index(Request $request): View
    {
        if (! Schema::hasTable('feature_requests') || ! Schema::hasTable('feature_request_votes')) {
            $emptyPagination = new LengthAwarePaginator([], 0, 20);
            return view('feature-requests.index', [
                'featureRequests' => $emptyPagination,
                'votedRequestIds' => [],
            ]);
        }

        $hasRepliesTable = Schema::hasTable('feature_request_replies');
        $featureRequests = FeatureRequest::query()
            ->where('is_active', true)
            ->with(['user:id,name,first_name,last_name,role_id'])
            ->when($hasRepliesTable, fn ($q) => $q->with(['replies' => fn ($rq) => $rq->with('user:id,name,first_name,last_name,role_id')->latest('id')]))
            ->withCount('votes')
            ->orderByDesc('votes_count')
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        $votedRequestIds = [];
        if ($request->user()) {
            $votedRequestIds = FeatureRequestVote::query()
                ->where('user_id', $request->user()->id)
                ->pluck('feature_request_id')
                ->all();
        }

        return view('feature-requests.index', [
            'featureRequests' => $featureRequests,
            'votedRequestIds' => $votedRequestIds,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        if (! Schema::hasTable('feature_requests')) {
            return back()->with('error', 'Feature voting jadvali hali tayyor emas. Admin migratsiyani ishga tushirsin.');
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string', 'max:3000'],
        ]);

        FeatureRequest::query()->create([
            'user_id' => $request->user()->id,
            'title' => sanitize_plain_text($validated['title']),
            'description' => isset($validated['description'])
                ? sanitize_plain_text($validated['description'])
                : null,
            'is_active' => true,
            'status' => FeatureRequest::STATUS_PENDING,
        ]);

        return redirect()
            ->route('feature-requests.index')
            ->with('success', 'Taklifingiz qo\'shildi. Endi foydalanuvchilar ovoz berishi mumkin.');
    }

    public function vote(Request $request, FeatureRequest $featureRequest): RedirectResponse
    {
        if (! Schema::hasTable('feature_request_votes')) {
            return back()->with('error', 'Ovoz berish jadvali hali tayyor emas. Admin migratsiyani ishga tushirsin.');
        }

        abort_unless($featureRequest->is_active, 404);
        if (! in_array((string) $featureRequest->status, FeatureRequest::VOTABLE_STATUSES, true)) {
            return back()->with('error', 'Bu taklif uchun ovoz berish yopilgan.');
        }

        $existingVote = FeatureRequestVote::query()
            ->where('feature_request_id', $featureRequest->id)
            ->where('user_id', $request->user()->id)
            ->first();

        if ($existingVote) {
            $existingVote->delete();

            return back()->with('success', 'Ovozingiz bekor qilindi.');
        }

        FeatureRequestVote::query()->create([
            'feature_request_id' => $featureRequest->id,
            'user_id' => $request->user()->id,
        ]);

        return back()->with('success', 'Ovozingiz qabul qilindi.');
    }

    public function storeReply(Request $request, FeatureRequest $featureRequest): RedirectResponse
    {
        if (! Schema::hasTable('feature_request_replies')) {
            return back()->with('error', 'Javoblar jadvali hali tayyor emas. Admin migratsiyani ishga tushirsin.');
        }
        abort_unless($this->canReply($request->user()), 403);

        $validated = $request->validate([
            'message' => ['required', 'string', 'max:3000'],
        ]);

        FeatureRequestReply::query()->create([
            'feature_request_id' => $featureRequest->id,
            'user_id' => $request->user()->id,
            'message' => sanitize_plain_text($validated['message']),
        ]);

        return back()->with('success', 'Javob yozildi.');
    }

    public function destroyReply(Request $request, FeatureRequestReply $reply): RedirectResponse
    {
        if (! Schema::hasTable('feature_request_replies')) {
            return back()->with('error', 'Javoblar jadvali hali tayyor emas. Admin migratsiyani ishga tushirsin.');
        }

        $user = $request->user();
        $isOwner = (int) $reply->user_id === (int) $user?->id;
        abort_unless($this->canManageAll($user) || $isOwner, 403);

        $reply->delete();

        return back()->with('success', 'Javob o\'chirildi.');
    }

    public function updateStatus(Request $request, FeatureRequest $featureRequest): RedirectResponse
    {
        abort_unless($request->user()?->isAdmin() || $request->user()?->isSuperAdmin(), 403);

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:'.implode(',', FeatureRequest::ALL_STATUSES)],
            'admin_note' => ['nullable', 'string', 'max:3000'],
        ]);

        $newStatus = (string) $validated['status'];
        $wasPending = (string) $featureRequest->status === FeatureRequest::STATUS_PENDING;

        $featureRequest->update([
            'status' => $newStatus,
            'admin_note' => isset($validated['admin_note']) ? sanitize_plain_text($validated['admin_note']) : null,
            'announced_at' => $wasPending && $newStatus !== FeatureRequest::STATUS_PENDING
                ? now()
                : $featureRequest->announced_at,
        ]);

        return back()->with('success', 'Taklif statusi yangilandi.');
    }

    public function destroy(Request $request, FeatureRequest $featureRequest): RedirectResponse
    {
        $user = $request->user();
        $isOwner = (int) $featureRequest->user_id === (int) $user?->id;
        abort_unless($this->canManageAll($user) || $isOwner, 403);

        $featureRequest->delete();

        return back()->with('success', 'Taklif o\'chirildi.');
    }

    private function canManageAll(?User $user): bool
    {
        return $user?->isAdmin() || $user?->isSuperAdmin();
    }

    private function canReply(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return $user->isSuperAdmin() || $user->isAdmin() || $user->hasRole(User::ROLE_MODERATOR);
    }
}
