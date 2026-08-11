<?php

namespace App\Http\Controllers;

use App\Jobs\SendTelegramBroadcast;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
            'global_chat_enabled' => ['nullable', 'string', 'in:1,0'],
            'global_chat_disabled_message' => ['nullable', 'string', 'max:1000'],
            'ai_chat_enabled' => ['nullable', 'string', 'in:1,0'],
            'ai_chat_disabled_message' => ['nullable', 'string', 'max:1000'],
        ]);

        $toggleKeys = ['global_chat_enabled', 'ai_chat_enabled'];

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

    /**
     * Telegram orqali auditoriyaga e'lon yuborish.
     */
    public function broadcastTelegram(Request $request)
    {
        $validated = $request->validate([
            'message'  => ['required', 'string', 'min:1', 'max:4000'],
            'audience' => ['required', 'string', 'in:all,teachers,donors,students'],
        ], [
            'message.required'  => 'Xabar matni kiritilishi shart.',
            'message.min'       => 'Xabar matni kamida 1 ta belgi bo\'lishi kerak.',
            'message.max'       => 'Xabar matni 4000 ta belgidan oshmasligi kerak.',
            'audience.required' => 'Auditoriyani tanlash shart.',
            'audience.in'       => 'Noto\'g\'ri auditoriya tanlandi.',
        ]);

        $message  = $validated['message'];
        $audience = $validated['audience'];

        // Tanlangan auditoriyaga mos Telegram foydalanuvchilar sonini hisoblash
        $query = User::whereNotNull('telegram_chat_id')
            ->where('telegram_chat_id', '>', 0);

        $query = match ($audience) {
            'teachers' => $query->where('role', 'teacher'),
            'donors'   => $query->whereNotNull('donation_rank'),
            'students' => $query->where('role', 'student'),
            default    => $query,
        };

        $userCount = $query->count();

        if ($userCount === 0) {
            return back()->with('error', 'Tanlangan auditoriyada Telegram botiga ulangan foydalanuvchilar topilmadi.');
        }

        // Broadcast log ga yozish
        $broadcastId = DB::table('telegram_broadcasts')->insertGetId([
            'admin_id'         => auth()->id(),
            'message'          => $message,
            'audience'         => $audience,
            'total_recipients' => $userCount,
            'status'           => 'pending',
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);

        // Xabarni queue ga yuborish (async) — audience parametri bilan
        SendTelegramBroadcast::dispatch($message, $audience, $broadcastId);

        $audienceLabel = match ($audience) {
            'teachers' => 'o\'qituvchilar',
            'donors'   => 'donorlar',
            'students' => 'o\'quvchilar',
            default    => 'hamma',
        };

        return back()->with('success', "Elon xabari ({$audienceLabel}) {$userCount} ta foydalanuvchiga yuborish uchun navbatga qo'shildi. Xabarlar birma-bir yuborilmoqda.");
    }

    /**
     * Telegram e'lonlar tarixini ko'rish.
     */
    public function broadcastHistory()
    {
        $broadcasts = DB::table('telegram_broadcasts')
            ->join('users', 'users.id', '=', 'telegram_broadcasts.admin_id')
            ->select('telegram_broadcasts.*', 'users.name as admin_name')
            ->orderByDesc('telegram_broadcasts.created_at')
            ->limit(50)
            ->get();

        return view('admin.settings.broadcast-history', compact('broadcasts'));
    }
}
