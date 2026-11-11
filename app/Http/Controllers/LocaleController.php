<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Cookie;

class LocaleController extends Controller
{
    public function switch(Request $request, string $locale)
    {
        abort_unless(array_key_exists($locale, supported_locales()), 404);

        Session::put('locale', $locale);
        Cookie::queue(Cookie::make('site_locale', $locale, 60 * 24 * 365));

        return redirect()->back();
    }
}
