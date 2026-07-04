<?php

namespace App\Listeners;

use App\Models\UserActivity;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Request;

class LogUserLogin
{
    public function handle(Login $event): void
    {
        $user = $event->user;
        $request = Request::instance();

        UserActivity::create([
            'user_id' => $user->id,
            'type' => UserActivity::TYPE_LOGIN,
            'description' => 'Tizimga kirildi',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'device_type' => $this->detectDevice($request->userAgent()),
            'occurred_at' => now(),
        ]);
    }

    private function detectDevice(?string $userAgent): string
    {
        if (!$userAgent) return 'desktop';

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
