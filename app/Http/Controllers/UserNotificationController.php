<?php

namespace App\Http\Controllers;

use App\Models\UserNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserNotificationController extends Controller
{
    public function index(Request $request): View
    {
        $filter = $request->query('filter', 'all');

        $notifications = UserNotification::query()
            ->where('user_id', $request->user()->id)
            ->when($filter === 'unread', fn ($query) => $query->whereNull('read_at'))
            ->latest('id')
            ->paginate(18)
            ->withQueryString();

        $summary = [
            'total' => UserNotification::query()
                ->where('user_id', $request->user()->id)
                ->count(),
            'unread' => UserNotification::query()
                ->where('user_id', $request->user()->id)
                ->whereNull('read_at')
                ->count(),
        ];

        return view('notifications.index', compact('notifications', 'summary', 'filter'));
    }

    public function summary(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        $notifications = UserNotification::query()
            ->where('user_id', $userId)
            ->latest('id')
            ->limit(4)
            ->get();

        return response()->json([
            'unread_count' => UserNotification::query()
                ->where('user_id', $userId)
                ->whereNull('read_at')
                ->count(),
            'notifications' => $notifications
                ->map(fn (UserNotification $notification): array => [
                    'id' => (int) $notification->id,
                    'type' => $this->toastType($notification->type),
                    'title' => $notification->title,
                    'body' => $notification->body,
                    'link' => $notification->link,
                    'read_at' => optional($notification->read_at)?->toIso8601String(),
                ])
                ->values(),
        ]);
    }

    public function pending(Request $request): JsonResponse
    {
        $notifications = UserNotification::query()
            ->where('user_id', $request->user()->id)
            ->whereNull('read_at')
            ->oldest('id')
            ->limit(5)
            ->get();

        if ($notifications->isNotEmpty()) {
            UserNotification::query()
                ->whereKey($notifications->modelKeys())
                ->update(['read_at' => now()]);
        }

        return response()->json([
            'notifications' => $notifications
                ->map(fn (UserNotification $notification): array => [
                    'id' => (int) $notification->id,
                    'type' => $this->toastType($notification->type),
                    'title' => $notification->title,
                    'body' => $notification->body,
                    'link' => $notification->link,
                ])
                ->values(),
        ]);
    }

    public function markAllRead(Request $request): RedirectResponse
    {
        UserNotification::query()
            ->where('user_id', $request->user()->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return redirect()
            ->route('notifications.index')
            ->with('success', "Bildirishnomalar o'qildi deb belgilandi.")
            ->with('toast_type', 'success');
    }

    private function toastType(string $type): string
    {
        return match ($type) {
            UserNotification::TYPE_ERROR => 'error',
            UserNotification::TYPE_WARNING => 'warning',
            UserNotification::TYPE_SUCCESS => 'success',
            default => 'success',
        };
    }
}
