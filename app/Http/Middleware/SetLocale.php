<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $defaultLocale = (string) config('app.locale', 'uz');
        $locale = (string) ($request->session()->get('locale') ?: $request->cookie('site_locale') ?: $defaultLocale);

        if (! array_key_exists($locale, supported_locales())) {
            $locale = $defaultLocale;
        }

        app()->setLocale($locale);
        Carbon::setLocale($locale);

        return $next($request);
    }
}
