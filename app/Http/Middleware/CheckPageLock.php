<?php

namespace App\Http\Middleware;

use App\Models\SiteSetting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPageLock
{
    // Route name prefix → sahifa kaliti
    // MUHIM: uzunroq prefixlar avval kelishi kerak (profile.exams → exam, profile → profile)
    private const PAGE_MAP = [
        'profile.exams'    => 'exam',
        'profile.results'  => 'exam',
        'post'             => 'post',
        'teacher'          => 'teacher',
        'courses'          => 'courses',
        'calendar'         => 'calendar',
        'contact'          => 'contact',
        'about'            => 'about',
        'search'           => 'search',
        'feature-requests' => 'feature-requests',
        'profile'          => 'profile',
        'exam'             => 'exam',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $pageKey = $this->resolvePageKey($request);

        if ($pageKey === null) {
            return $next($request);
        }

        $lock = $this->getLock($pageKey);

        if ($lock === null) {
            return $next($request);
        }

        // Muddati o'tgan bo'lsa — avtomatik ochib yuboramiz
        if (now()->gt($lock['locked_until'])) {
            $this->removeLock($pageKey);
            return $next($request);
        }

        // Admin / editor / moderator / super_admin o'tib ketadi
        $user = $request->user();
        if ($user && ($user->canManageContent() || $user->canManageInbox() || $user->isSuperAdmin())) {
            return $next($request);
        }

        // Oddiy foydalanuvchilarga ogohlantirish sahifasi
        return response()->view('errors.page-locked', [
            'reason'      => $lock['reason'] ?? null,
            'locked_until' => $lock['locked_until'],
            'page_name'   => $lock['page_name'] ?? $pageKey,
        ], 503);
    }

    private function resolvePageKey(Request $request): ?string
    {
        $routeName = $request->route()?->getName() ?? '';

        foreach (self::PAGE_MAP as $prefix => $key) {
            if (str_starts_with($routeName, $prefix)) {
                return $key;
            }
        }

        return null;
    }

    private function getLock(string $pageKey): ?array
    {
        $raw = SiteSetting::get('page_locks');
        if (!$raw) return null;

        $locks = json_decode($raw, true);
        return $locks[$pageKey] ?? null;
    }

    private function removeLock(string $pageKey): void
    {
        $raw = SiteSetting::get('page_locks');
        $locks = $raw ? json_decode($raw, true) : [];
        unset($locks[$pageKey]);
        SiteSetting::set('page_locks', $locks ? json_encode($locks) : null);
    }
}
