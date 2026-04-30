<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    private const WEB_ROUTE_FILES = [
        'routes/web/public.php',
        'routes/web/auth.php',
        'routes/web/member.php',
        'routes/web/teacher.php',
        'routes/web/admin.php',
        'routes/web/ai.php',
        'routes/web/fallback.php',
    ];

    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('comments', function (Request $request) {
            return Limit::perMinute(30)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('chat-send', function (Request $request) {
            $uid = $request->user()?->id;

            return Limit::perMinute(45)->by($uid ? 'chat-u-'.$uid : 'chat-ip-'.$request->ip());
        });

        RateLimiter::for('ai-chat', function (Request $request) {
            $userId = $request->user()?->id;

            return Limit::perMinute($userId ? 30 : 10)
                ->by($userId ? 'ai-chat-u-'.$userId : 'ai-chat-ip-'.$request->ip());
        });

        RateLimiter::for('ai-feedback', function (Request $request) {
            $userId = $request->user()?->id;

            return Limit::perMinute($userId ? 60 : 20)
                ->by($userId ? 'ai-feedback-u-'.$userId : 'ai-feedback-ip-'.$request->ip());
        });

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            foreach (self::WEB_ROUTE_FILES as $routeFile) {
                Route::middleware('web')->group(base_path($routeFile));
            }
        });
    }
}
