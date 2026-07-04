<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserActivity;
use Illuminate\Support\Facades\Request;

class UserActivityLogger
{
    public static function log(
        User $user,
        string $type,
        string $description,
        ?array $oldValue = null,
        ?array $newValue = null
    ): UserActivity {
        $request = Request::instance();

        return UserActivity::create([
            'user_id' => $user->id,
            'type' => $type,
            'description' => $description,
            'old_value' => $oldValue,
            'new_value' => $newValue,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'device_type' => self::detectDevice($request->userAgent()),
            'occurred_at' => now(),
        ]);
    }

    private static function detectDevice(?string $userAgent): string
    {
        if (!$userAgent) {
            return 'desktop';
        }

        $agent = strtolower($userAgent);

        if (str_contains($agent, 'mobile') || str_contains($agent, 'android') || str_contains($agent, 'iphone')) {
            return 'mobile';
        }

        if (str_contains($agent, 'tablet') || str_contains($agent, 'ipad')) {
            return 'tablet';
        }

        return 'desktop';
    }
}
