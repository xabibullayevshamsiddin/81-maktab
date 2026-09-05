<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminContactMessageController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()->canManageInbox(), 403);

        $q = trim((string) $request->query('q', ''));
        $status = (string) $request->query('status', 'all');
        if (!in_array($status, ['all', 'unread', 'read', 'blocked'], true)) {
            $status = 'all';
        }

        $query = ContactMessage::query()
            ->leftJoin('users', 'users.email', '=', 'contact_messages.email')
            ->select('contact_messages.*')
            ->selectRaw("
                CASE
                    WHEN users.donation_rank = 'vip' AND (users.donation_rank_expires_at IS NULL OR users.donation_rank_expires_at > NOW()) THEN 3
                    WHEN users.donation_rank = 'premium' AND (users.donation_rank_expires_at IS NULL OR users.donation_rank_expires_at > NOW()) THEN 2
                    WHEN users.donation_rank = 'supporter' AND (users.donation_rank_expires_at IS NULL OR users.donation_rank_expires_at > NOW()) THEN 1
                    ELSE 0
                END as donor_priority
            ")
            ->with(['readBy:id,first_name,name', 'blockedBy:id,first_name,name', 'senderUser:id,name'])
            ->orderByDesc('donor_priority')
            ->orderByDesc('contact_messages.created_at');

        if ($status === 'unread') {
            $query->whereNull('contact_messages.read_at');
        } elseif ($status === 'read') {
            $query->whereNotNull('contact_messages.read_at')->where('contact_messages.is_blocked', false);
        } elseif ($status === 'blocked') {
            $query->where('contact_messages.is_blocked', true);
        }

        if ($q !== '') {
            $query->where(function ($w) use ($q): void {
                $w->where('contact_messages.name', 'like', '%' . $q . '%')
                    ->orWhere('contact_messages.email', 'like', '%' . $q . '%')
                    ->orWhere('contact_messages.phone', 'like', '%' . $q . '%')
                    ->orWhere('contact_messages.note', 'like', '%' . $q . '%')
                    ->orWhere('contact_messages.message', 'like', '%' . $q . '%');
            });
        }

        $messages = $query->paginate(25)->appends(request()->query());

        return view('admin.contact-messages.index', compact('messages', 'status'));
    }

    public function show(Request $request, ContactMessage $contactMessage): View
    {
        abort_unless($request->user()->canManageInbox(), 403);

        $contactMessage->markAsReadBy($request->user());
        $contactMessage->load(['readBy:id,first_name,name', 'blockedBy:id,first_name,name', 'senderUser:id,name']);

        return view('admin.contact-messages.show', ['message' => $contactMessage]);
    }



    public function markRead(Request $request, ContactMessage $contactMessage): RedirectResponse
    {
        abort_unless($request->user()->canManageInbox(), 403);

        $contactMessage->markAsReadBy($request->user());

        return redirect()
            ->route('admin.contact-messages.index', $request->only(['q', 'status']))
            ->with('success', 'Xabar o‘qilgan deb belgilandi.');
    }

    public function block(Request $request, ContactMessage $contactMessage): RedirectResponse
    {
        abort_unless($request->user()->canManageInbox(), 403);

        $validated = $request->validate([
            'duration' => 'nullable|string|in:1h,1d,1w,1m,forever',
            'reason'   => 'nullable|string|max:500',
        ]);

        // Xabarni bloklash
        $contactMessage->update([
            'is_blocked'         => true,
            'blocked_at'         => now(),
            'blocked_by_user_id' => $request->user()->id,
        ]);

        // Agar yuboruvchida foydalanuvchi akkaunti bo'lsa — uni ham bloklash
        $senderUser = $contactMessage->senderUser;
        if ($senderUser) {
            $duration = $validated['duration'] ?? '1d';
            $blockInfo = $senderUser->calculateBlockDuration($duration);
            $blockedUntil = $blockInfo['blocked_until'];
            $durationText = $blockInfo['duration_text'];
            $reason = $validated['reason'] ?? 'Aloqa xabaridagi qoidabuzarlik tufayli bloklandi';

            $senderUser->increment('block_count');
            $senderUser->update([
                'is_blocked'    => true,
                'blocked_until' => $blockedUntil,
                'blocked_reason' => $reason,
                'blocked_by'    => $request->user()->id,
            ]);

            $senderUser->sendBlockNotification($request->user(), $durationText, $blockedUntil, $reason);

            $unblockTime = $blockedUntil ? $blockedUntil->format('d.m.Y H:i') : 'Cheksiz';
            return redirect()
                ->route('admin.contact-messages.index', $request->only(['q', 'status']))
                ->with('success', "Xabar bloklandi. {$senderUser->name} akkaunti ham bloklandi ({$unblockTime}).");
        }

        return redirect()
            ->route('admin.contact-messages.index', $request->only(['q', 'status']))
            ->with('success', 'Xabar bloklandi (spam/arxaiv).');
    }

    public function unblock(Request $request, ContactMessage $contactMessage): RedirectResponse
    {
        abort_unless($request->user()->canManageInbox(), 403);

        $contactMessage->update([
            'is_blocked' => false,
            'blocked_at' => null,
            'blocked_by_user_id' => null,
        ]);

        return redirect()
            ->route('admin.contact-messages.index', $request->only(['q', 'status']))
            ->with('success', 'Blokdan olindi.');
    }

    public function destroy(Request $request, ContactMessage $contactMessage): RedirectResponse
    {
        abort_unless($request->user()->canManageInbox(), 403);

        $contactMessage->delete();

        return redirect()
            ->route('admin.contact-messages.index')
            ->with('success', 'Xabar o‘chirildi.');
    }
}
