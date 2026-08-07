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

        if ($user) {
            // 1. Avval vaqtincha blokni tekshirish (blocked_until gaqarab avtomatik ochilishi kerak)
            if ($user->isCurrentlyBlocked()) {
                return $this->blockedResponse($request);
            }

            // 2. Keyin faollik holatini tekshirish
            if (! $user->isActive()) {
                return $this->blockedResponse($request);
            }
        }

        return $next($request);
    }

    private function blockedResponse(Request $request): Response
    {
        $message = "Kechirasiz, sizning hisobingiz vaqtincha bloklangan. Iltimos, sababini bilish uchun 'Aloqa' bo'limi orqali bizga xabar yo'llang.";

        if ($request->expectsJson() || $request->isXmlHttpRequest() || $request->is('chat/*') || $request->is('ai-chat')) {
            return response()->json([
                'message' => $message,
                'error' => 'Account blocked'
            ], 403);
        }

        return back()->with('error', $message)
            ->with('toast_type', 'error');
    }
}
