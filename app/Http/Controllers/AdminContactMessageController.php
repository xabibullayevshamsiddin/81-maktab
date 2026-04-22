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
            ->with(['readBy:id,first_name,name', 'blockedBy:id,first_name,name'])
            ->latest();

        if ($status === 'unread') {
            $query->whereNull('read_at');
        } elseif ($status === 'read') {
            $query->whereNotNull('read_at')->where('is_blocked', false);
        } elseif ($status === 'blocked') {
            $query->where('is_blocked', true);
        }

        if ($q !== '') {
            $query->where(function ($w) use ($q): void {
                $w->where('name', 'like', '%' . $q . '%')
                    ->orWhere('email', 'like', '%' . $q . '%')
                    ->orWhere('phone', 'like', '%' . $q . '%')
                    ->orWhere('note', 'like', '%' . $q . '%')
                    ->orWhere('message', 'like', '%' . $q . '%');
            });
        }

        $messages = $query->paginate(25)->withQueryString();

        return view('admin.contact-messages.index', compact('messages', 'status'));
    }

    public function show(Request $request, ContactMessage $contactMessage): View
    {
        abort_unless($request->user()->canManageInbox(), 403);

        $contactMessage->markAsReadBy($request->user());
        $contactMessage->load(['readBy:id,first_name,name', 'blockedBy:id,first_name,name']);

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

        $contactMessage->update([
            'is_blocked' => true,
            'blocked_at' => now(),
            'blocked_by_user_id' => $request->user()->id,
        ]);

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
