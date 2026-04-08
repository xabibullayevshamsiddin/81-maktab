<?php

namespace App\Http\Controllers;

use App\Models\SiteSetting;
use Illuminate\Http\Request;

class AdminSettingsController extends Controller
{
    private const KEYS = [
        'school_name',
        'school_phone',
        'school_email',
        'school_address',
        'social_telegram',
        'social_instagram',
        'social_facebook',
        'social_youtube',
        'announcement_text',
        'announcement_type',
        'announcement_active',
    ];

    public function index()
    {
        $settings = [];
        foreach (self::KEYS as $key) {
            $settings[$key] = SiteSetting::get($key, '');
        }

        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'school_name' => ['nullable', 'string', 'max:255'],
            'school_phone' => ['nullable', 'string', 'max:60'],
            'school_email' => ['nullable', 'email', 'max:255'],
            'school_address' => ['nullable', 'string', 'max:500'],
            'social_telegram' => ['nullable', 'url', 'max:500'],
            'social_instagram' => ['nullable', 'url', 'max:500'],
            'social_facebook' => ['nullable', 'url', 'max:500'],
            'social_youtube' => ['nullable', 'url', 'max:500'],
            'announcement_text' => ['nullable', 'string', 'max:500'],
            'announcement_type' => ['nullable', 'string', 'in:info,success,warning,danger'],
            'announcement_active' => ['nullable', 'string', 'in:1,0'],
        ]);

        foreach (self::KEYS as $key) {
            SiteSetting::set($key, $validated[$key] ?? null);
        }

        return redirect()
            ->route('admin.settings.index')
            ->with('success', 'Sozlamalar saqlandi.');
    }
}
