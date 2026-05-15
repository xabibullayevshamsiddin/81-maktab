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

    private const TOGGLE_KEYS = [
        'announcement_active',
        'global_chat_enabled',
        'ai_chat_enabled',
    ];

    private const UNCACHED_KEYS = [
        'announcement_active',
        'announcement_text',
        'announcement_type',
        'global_chat_enabled',
        'global_chat_disabled_message',
        'ai_chat_enabled',
        'ai_chat_disabled_message',
    ];

    public static function get(string $key, ?string $default = null): ?string
    {
        if (in_array($key, self::UNCACHED_KEYS, true)) {
            $setting = static::query()
                ->where('key', $key)
                ->first(['value']);

            if (! $setting) {
                return $default;
            }

            return static::normalizeValue($key, $setting->value, $default);
        }

        $all = static::allCached();

        if (! array_key_exists($key, $all)) {
            return $default;
        }

        return static::normalizeValue($key, $all[$key], $default);
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

    private static function normalizeValue(string $key, mixed $value, ?string $default = null): ?string
    {
        if ($value === null) {
            return in_array($key, self::TOGGLE_KEYS, true) ? '0' : $default;
        }

        return (string) $value;
    }
}
