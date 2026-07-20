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
        'global_chat_enabled',
        'global_chat_disabled_message',
        'ai_chat_enabled',
        'ai_chat_disabled_message',
    ];

    private const LOCKABLE_PAGES = [
        'post'             => 'Yangiliklar',
        'teacher'          => 'O\'qituvchilar',
        'courses'          => 'Kurslar',
        'calendar'         => 'Taqvim',
        'contact'          => 'Aloqa',
        'about'            => 'Biz haqimizda',
        'search'           => 'Qidiruv',
        'feature-requests' => 'Takliflar',
        'profile'          => 'Profil',
        'exam'             => 'Imtihonlar',
    ];

    public function index()
    {
        $defaults = [
            'announcement_active' => '0',
            'announcement_type' => 'info',
            'global_chat_enabled' => '1',
            'ai_chat_enabled' => '1',
        ];
        $settings = [];
        foreach (self::KEYS as $key) {
            $settings[$key] = SiteSetting::get($key, $defaults[$key] ?? '');
        }

        return view('admin.settings.index', compact('settings'));
    }

    public function pageLocks()
    {
        $raw = SiteSetting::get('page_locks');
        $locks = $raw ? json_decode($raw, true) : [];

        return view('admin.settings.page-locks', [
            'pages' => self::LOCKABLE_PAGES,
            'locks' => $locks,
        ]);
    }

    public function lockPage(Request $request)
    {
        $validated = $request->validate([
            'page'     => ['required', 'string', 'in:' . implode(',', array_keys(self::LOCKABLE_PAGES))],
            'duration' => ['required', 'integer', 'min:1', 'max:10080'],
            'reason'   => ['nullable', 'string', 'max:300'],
        ]);

        $raw = SiteSetting::get('page_locks');
        $locks = $raw ? json_decode($raw, true) : [];

        $locks[$validated['page']] = [
            'locked_until' => now()->addMinutes((int) $validated['duration'])->toIso8601String(),
            'reason'       => $validated['reason'] ?? null,
            'page_name'    => self::LOCKABLE_PAGES[$validated['page']],
            'locked_by_name' => auth()->user()->name,
        ];

        SiteSetting::set('page_locks', json_encode($locks));

        return redirect()
            ->route('admin.settings.page-locks')
            ->with('success', self::LOCKABLE_PAGES[$validated['page']] . ' sahifasi ' . $validated['duration'] . ' daqiqaga bloklandi.');
    }

    public function unlockPage(Request $request)
    {
        $validated = $request->validate([
            'page' => ['required', 'string', 'in:' . implode(',', array_keys(self::LOCKABLE_PAGES))],
        ]);

        $raw = SiteSetting::get('page_locks');
        $locks = $raw ? json_decode($raw, true) : [];
        unset($locks[$validated['page']]);
        SiteSetting::set('page_locks', $locks ? json_encode($locks) : null);

        return redirect()
            ->route('admin.settings.page-locks')
            ->with('success', self::LOCKABLE_PAGES[$validated['page']] . ' sahifasi blokdan chiqarildi.');
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
            'global_chat_enabled' => ['nullable', 'string', 'in:1,0'],
            'global_chat_disabled_message' => ['nullable', 'string', 'max:1000'],
            'ai_chat_enabled' => ['nullable', 'string', 'in:1,0'],
            'ai_chat_disabled_message' => ['nullable', 'string', 'max:1000'],
        ]);

        $toggleKeys = ['announcement_active', 'global_chat_enabled', 'ai_chat_enabled'];

        foreach (self::KEYS as $key) {
            if (in_array($key, $toggleKeys, true)) {
                $v = $request->input($key);
                SiteSetting::set($key, ((string) $v) === '1' ? '1' : '0');

                continue;
            }

            SiteSetting::set($key, $validated[$key] ?? null);
        }

        return redirect()
            ->route('admin.settings.index')
            ->with('success', 'Sozlamalar saqlandi.');
    }
}
