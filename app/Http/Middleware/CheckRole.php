<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Laravel route middleware `role:a,b,c` ni vergul bilan ajratib har birini alohida argument sifatida uzatadi.
     * Bitta `string $roles` parametri faqat birinchi rolni olardi — qolganlari e'tiborsiz qolardi.
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! $request->user()) {
            return redirect()->route('login');
        }

        $user = $request->user();

        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        $allowed = array_filter(array_map('trim', $roles));

        foreach ($allowed as $roleName) {
            if ($roleName !== '' && $user->hasRole($roleName)) {
                return $next($request);
            }
        }

        abort(403, 'Sizda bu sahifaga kirish huquqi yo\'q.');
    }
}
