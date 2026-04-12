<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TurnstileService
{
    public static function verify(?string $token, ?string $remoteIp = null): bool
    {
        if (! config('services.turnstile.enabled')) {
            return true;
        }

        $secret = config('services.turnstile.secret');
        if (! is_string($secret) || $secret === '') {
            Log::warning('Turnstile: secret sozlanmagan.');

            return false;
        }

        if (! is_string($token) || trim($token) === '') {
            return false;
        }

        try {
            $response = Http::asForm()
                ->timeout(10)
                ->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
                    'secret' => $secret,
                    'response' => $token,
                    'remoteip' => $remoteIp,
                ]);
        } catch (\Throwable $e) {
            Log::warning('Turnstile tarmoq xatosi: '.$e->getMessage());

            return false;
        }

        if (! $response->successful()) {
            return false;
        }

        $data = $response->json();

        return ($data['success'] ?? false) === true;
    }
}
