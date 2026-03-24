<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class ImageService
{
    public function uploadAndOptimize(UploadedFile $file, string $directory = 'uploads', int $maxWidth = 1200, int $maxHeight = 800): string
    {
        $filename = time().'_'.uniqid().'.'.$file->getClientOriginalExtension();
        $path = "{$directory}/{$filename}";

        $image = Image::make($file);

        if ($image->width() > $maxWidth || $image->height() > $maxHeight) {
            $image->resize($maxWidth, $maxHeight, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
        }

        $image->encode($file->getClientOriginalExtension(), 80);

        Storage::disk('public')->put($path, $image->stream());

        return $path;
    }

    public function deleteImage(?string $path): bool
    {
        if (empty($path)) {
            return false;
        }

        if (Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->delete($path);
        }

        return false;
    }

    public function createThumbnail(string $originalPath, string $directory = 'thumbnails', int $width = 300, int $height = 200): ?string
    {
        if (! Storage::disk('public')->exists($originalPath)) {
            return null;
        }

        $filename = 'thumb_'.basename($originalPath);
        $thumbnailPath = "{$directory}/{$filename}";

        $image = Image::make(Storage::disk('public')->path($originalPath));
        $image->fit($width, $height);

        Storage::disk('public')->put($thumbnailPath, $image->stream());

        return $thumbnailPath;
    }

    public function getImageUrl(?string $path): ?string
    {
        if (empty($path)) {
            return null;
        }

        return asset("storage/{$path}");
    }
}
