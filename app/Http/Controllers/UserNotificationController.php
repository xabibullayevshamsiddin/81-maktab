<?php

namespace App\Http\Controllers;

use App\Models\UserNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserNotificationController extends Controller
{
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
