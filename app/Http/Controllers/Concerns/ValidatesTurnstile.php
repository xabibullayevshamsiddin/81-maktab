<?php

namespace App\Http\Controllers\Concerns;

use App\Services\TurnstileService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

trait ValidatesTurnstile
{
    /**
     * Cloudflare Turnstile (cf-turnstile-response yoki JSON: turnstile_token).
     *
     * @throws ValidationException
     */
    protected function validateTurnstile(Request $request): void
    {
        if (! config('services.turnstile.enabled')) {
            return;
        }

        $token = $request->input('cf-turnstile-response')
            ?: $request->input('turnstile_token');

        if (! is_string($token) || trim($token) === '') {
            throw ValidationException::withMessages([
                'turnstile' => ['Iltimos, «Robot emasman» tekshiruvini bajaring.'],
            ]);
        }

        if (! TurnstileService::verify($token, $request->ip())) {
            throw ValidationException::withMessages([
                'turnstile' => ['Tekshiruv muvaffaqiyatsiz. Sahifani yangilab qayta urinib ko‘ring.'],
            ]);
        }
    }
}
