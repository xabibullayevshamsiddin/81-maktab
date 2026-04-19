<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && !$user->isActive()) {
            if ($request->expectsJson() || $request->isXmlHttpRequest() || $request->is('chat/*') || $request->is('ai-chat')) {
                return response()->json([
                    'message' => "Kechirasiz, sizning hisobingiz vaqtincha bloklangan. Iltimos, sababini bilish uchun 'Aloqa' bo'limi orqali bizga xabar yo'llang.",
                    'error' => 'Account blocked'
                ], 403);
            }

            return back()->with('error', "Kechirasiz, sizning hisobingiz vaqtincha bloklangan. Iltimos, sababini bilish uchun 'Aloqa' bo'limi orqali bizga xabar yo'llang.")
                ->with('toast_type', 'error');
        }

        return $next($request);
    }
}
