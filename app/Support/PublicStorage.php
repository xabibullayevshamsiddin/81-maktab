<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PublicStorage
{
    public static function delete(?string $path): bool
    {
        $deleted = false;

        foreach (self::candidatePaths($path) as $candidate) {
            if (Storage::disk('public')->exists($candidate)) {
                $deleted = Storage::disk('public')->delete($candidate) || $deleted;
            }
        }

        return $deleted;
    }

    /**
     * @param  iterable<string|null>  $paths
     */
    public static function deleteMany(iterable $paths): int
    {
        $count = 0;

        foreach ($paths as $path) {
            if (self::delete($path)) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * @return array<int, string>
     */
    public static function candidatePaths(?string $path): array
    {
        $path = self::normalize($path);

        if ($path === null) {
            return [];
        }

        return [$path];
    }

    public static function normalize(?string $path): ?string
    {
        $path = trim((string) $path);

        if ($path === '') {
            return null;
        }

        $urlPath = parse_url($path, PHP_URL_PATH);
        if (is_string($urlPath) && $urlPath !== '') {
            $path = $urlPath;
        }

        $path = str_replace('\\', '/', $path);
        $path = ltrim($path, '/');

        foreach (['public/storage/', 'storage/', 'public/'] as $prefix) {
            if (Str::startsWith($path, $prefix)) {
                $path = Str::after($path, $prefix);
                break;
            }
        }

        $path = ltrim($path, '/');

        if ($path === '' || Str::contains($path, ['..', "\0"])) {
            return null;
        }

        return $path;
    }
}
