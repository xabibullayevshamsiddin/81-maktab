<?php

namespace App\Helpers;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class StorageHelper
{
    /**
     * Ensure all required storage directories exist.
     * Useful for Render free tier with ephemeral filesystem.
     */
    public static function ensureDirectories(): void
    {
        $directories = [
            storage_path('app/public/posts'),
            storage_path('app/public/posts/videos'),
            storage_path('app/public/books'),
            storage_path('app/public/courses'),
            storage_path('app/public/avatars'),
            storage_path('app/public/temp'),
            storage_path('framework/cache/data'),
            storage_path('framework/sessions'),
            storage_path('framework/views'),
            storage_path('logs'),
            base_path('bootstrap/cache'),
        ];

        foreach ($directories as $directory) {
            if (!File::isDirectory($directory)) {
                try {
                    File::makeDirectory($directory, 0775, true, true);
                } catch (\Throwable $e) {
                    Log::warning("Could not create directory: {$directory}", [
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }

        // Ensure storage link exists
        $linkPath = public_path('storage');
        $targetPath = storage_path('app/public');

        if (!file_exists($linkPath) && !is_link($linkPath)) {
            try {
                app('files')->link($targetPath, $linkPath);
            } catch (\Throwable $e) {
                Log::warning("Could not create storage link", [
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Ensure a specific subdirectory exists in storage.
     *
     * @param string $path Relative path within storage/app/public
     */
    public static function ensureStoragePath(string $path): void
    {
        $fullPath = storage_path("app/public/{$path}");

        if (!File::isDirectory($fullPath)) {
            try {
                File::makeDirectory($fullPath, 0775, true, true);
            } catch (\Throwable $e) {
                Log::warning("Could not create storage path: {$fullPath}", [
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }
}
