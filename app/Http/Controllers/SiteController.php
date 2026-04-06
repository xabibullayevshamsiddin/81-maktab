<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SiteController extends Controller
{
    public function redirectLegacyPublicPath(?string $path = null)
    {
        $normalizedPath = trim((string) $path, '/');

        return redirect()->to($normalizedPath === '' ? route('home') : url($normalizedPath));
    }

    public function switchLocale(Request $request, string $locale)
    {
        if (! array_key_exists($locale, supported_locales())) {
            abort(404);
        }

        $request->session()->put('locale', $locale);

        return redirect()->back()->cookie(
            cookie('site_locale', $locale, 60 * 24 * 365)
        );
    }

    public function notFound()
    {
        abort(404);
    }
}
