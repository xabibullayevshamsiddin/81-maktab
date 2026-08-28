<?php

namespace App\Http\Middleware;

use App\Models\DailySiteVisit;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class TrackSiteVisits
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Faqat muvaffaqiyatli GET so'rovlarni hisoblash
        if (! $request->isMethod('GET') || $response->getStatusCode() >= 400) {
            return $response;
        }

        // API, chat polling, statik fayllar yoki fon so'rovlarini hisobga olmaslik
        if ($this->shouldSkipTracking($request)) {
            return $response;
        }

        try {
            $this->recordVisit($request);
        } catch (\Throwable $e) {
            // Tashriflar hisobi asosiy sayt ishlashiga xalal bermasligi kerak
        }

        return $response;
    }

    private function shouldSkipTracking(Request $request): bool
    {
        $path = $request->path();

        // Chat yoki fon polling so'rovlari
        if ($request->is(
            'chat/*',
            'ai-chat/*',
            'api/*',
            'livewire/*',
            '_debugbar/*',
            'telescope/*',
            'health'
        )) {
            return true;
        }

        // AJAX / JSON so'rovlar
        if ($request->ajax() || $request->wantsJson()) {
            return true;
        }

        // Statik fayl kengaytmalari
        if (preg_match('/\.(js|css|png|jpg|jpeg|gif|svg|ico|webp|woff|woff2|ttf|eot|map|txt|xml|json)$/i', $path)) {
            return true;
        }

        // Qidiruv botlarini filtrlash (ixtiyoriy, botlar tashriflar sonini buzmasligi uchun)
        $userAgent = (string) $request->header('User-Agent', '');
        if (preg_match('/(bot|crawl|spider|slurp|facebookexternalhit|whatsapp|telegrambot|semrush)/i', $userAgent)) {
            return true;
        }

        return false;
    }

    private function recordVisit(Request $request): void
    {
        $today = now()->toDateString();
        $ip = $request->ip() ?: '127.0.0.1';
        $ua = (string) $request->header('User-Agent', '');
        $userId = $request->user()?->id;

        // 1. Noyob mehmon (IP + User-Agent) bugun kirganmi?
        $uniqueVisitorKey = 'visit_u_' . $today . '_' . md5($ip . '|' . $userAgentKey = substr($ua, 0, 100));
        $isNewUnique = false;
        if (! Cache::has($uniqueVisitorKey)) {
            Cache::put($uniqueVisitorKey, 1, now()->endOfDay()->addHours(2));
            $isNewUnique = true;
        }

        // 2. Tizimga kirgan foydalanuvchi bugun kirganmi?
        $isNewAuth = false;
        if ($userId) {
            $authVisitorKey = 'visit_auth_' . $today . '_' . $userId;
            if (! Cache::has($authVisitorKey)) {
                Cache::put($authVisitorKey, 1, now()->endOfDay()->addHours(2));
                $isNewAuth = true;
            }
        }

        // DB yozuvini yangilash
        DailySiteVisit::forDate($today);

        DailySiteVisit::query()
            ->where('date', $today)
            ->update([
                'page_views' => DB::raw('page_views + 1'),
                'unique_visitors' => $isNewUnique ? DB::raw('unique_visitors + 1') : DB::raw('unique_visitors'),
                'auth_visits' => $isNewAuth ? DB::raw('auth_visits + 1') : DB::raw('auth_visits'),
                'updated_at' => now(),
            ]);
    }
}
