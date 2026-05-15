<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureStudentGradeSelectionIsResolved
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (!$user || !method_exists($user, 'needsGradeSelection') || !$user->needsGradeSelection()) {
            return $next($request);
        }

        if (
            $request->routeIs([
                'profile.grade-selection.show',
                'profile.grade-selection.update',
                'profile.show',
                'logout',
                'locale.switch',
            ])
        ) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Davom etish uchun sinfingizni qayta tanlang.',
                'redirect' => route('profile.show'),
            ], 409);
        }

        return redirect()
            ->route('profile.show')
            ->with('error', $user->grade_selection_reason ?: 'Davom etish uchun sinfingizni qayta tanlang.')
            ->with('toast_type', 'warning');
    }
}
