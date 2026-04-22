<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SiteSetting extends Model
{
    protected $fillable = [
        'key',
        'value',
    ];

    private const CACHE_KEY = 'site_settings_all';

    private const CACHE_TTL_SECONDS = 300;

    public static function get(string $key, ?string $default = null): ?string
    {
        $all = static::allCached();

        if (! array_key_exists($key, $all)) {
            return $default;
        }

        $value = $all[$key];

        /*
         * Ma’lumot bazasida value NULL bo‘lsa, `$v ?? $default` ilgari defaultni berardi.
         * Yoqilgan/o‘chirilgan kalitlar uchun NULL ni «o‘chiq» deb qabul qilamiz.
         */
        if ($value === null) {
            $toggleKeys = ['announcement_active', 'global_chat_enabled', 'ai_chat_enabled'];
            if (in_array($key, $toggleKeys, true)) {
                return '0';
            }

            return $default;
        }

        return (string) $value;
    }

    public static function set(string $key, ?string $value): void
    {
        static::query()->updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );

        Cache::forget(self::CACHE_KEY);
    }

    public static function allCached(): array
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL_SECONDS, function () {
            return static::query()->pluck('value', 'key')->all();
        });
    }
}
