<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureEmailIsVerified
{
    public function handle(Request $request, Closure $next, $redirectToRoute = null): Response
    {
        if (! $request->user()) {
            return redirect()->route('login');
        }

        if (! $request->user()->hasVerifiedEmail()) {
            return $request->expectsJson()
                ? response()->json(['message' => 'Email tasdiqlanmagan.'], 403)
                : redirect()->intended(default: route($redirectToRoute ?: 'verification.notice'));
        }

        return $next($request);
    }
}
