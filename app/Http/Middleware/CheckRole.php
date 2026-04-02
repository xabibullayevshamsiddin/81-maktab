<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, string $roles): Response
    {
        if (! $request->user()) {
            return redirect()->route('login');
        }

        $allowed = array_filter(array_map('trim', explode(',', $roles)));
        $userRole = (string) $request->user()->role;

        if (! in_array($userRole, $allowed, true)) {
            abort(403, 'Sizda bu sahifaga kirish huquqi yo\'q.');
        }

        return $next($request);
    }
}
