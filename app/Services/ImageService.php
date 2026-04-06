<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageService
{
    public function uploadAndOptimize(UploadedFile $file, string $directory = 'uploads', int $maxWidth = 1200, int $maxHeight = 800): string
    {
        $extension = strtolower((string) $file->getClientOriginalExtension());
        if (! in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true)) {
            $extension = 'jpg';
        }

        $filename = time().'_'.uniqid().'.'.$extension;
        $path = trim($directory, '/').'/'.$filename;

        [$source, $width, $height, $mime] = $this->createImageResource($file->getRealPath());
        $source = $this->normalizeOrientation($file->getRealPath(), $mime, $source, $width, $height);

        $ratio = min(
            1,
            $maxWidth > 0 ? ($maxWidth / max(1, $width)) : 1,
            $maxHeight > 0 ? ($maxHeight / max(1, $height)) : 1
        );

        $targetWidth = max(1, (int) round($width * $ratio));
        $targetHeight = max(1, (int) round($height * $ratio));

        $canvas = imagecreatetruecolor($targetWidth, $targetHeight);
        if (! $canvas) {
            imagedestroy($source);

            throw new \RuntimeException('Rasm uchun canvas yaratilmadi.');
        }

        $this->prepareCanvas($canvas);
        imagecopyresampled($canvas, $source, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height);

        $binary = $this->encodeImageBinary($canvas, $extension, 80);

        imagedestroy($canvas);
        imagedestroy($source);

        Storage::disk('public')->put($path, $binary);

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

        $extension = strtolower((string) pathinfo($originalPath, PATHINFO_EXTENSION));
        if (! in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true)) {
            $extension = 'jpg';
        }

        $filename = 'thumb_'.pathinfo($originalPath, PATHINFO_FILENAME).'.'.$extension;
        $thumbnailPath = trim($directory, '/').'/'.$filename;
        $absolutePath = Storage::disk('public')->path($originalPath);

        [$source, $sourceWidth, $sourceHeight, $mime] = $this->createImageResource($absolutePath);
        $source = $this->normalizeOrientation($absolutePath, $mime, $source, $sourceWidth, $sourceHeight);

        $canvas = imagecreatetruecolor($width, $height);
        if (! $canvas) {
            imagedestroy($source);

            throw new \RuntimeException('Thumbnail uchun canvas yaratilmadi.');
        }

        $this->prepareCanvas($canvas);

        $sourceRatio = $sourceWidth / max(1, $sourceHeight);
        $targetRatio = $width / max(1, $height);

        if ($sourceRatio > $targetRatio) {
            $cropHeight = $sourceHeight;
            $cropWidth = (int) round($sourceHeight * $targetRatio);
            $srcX = (int) floor(($sourceWidth - $cropWidth) / 2);
            $srcY = 0;
        } else {
            $cropWidth = $sourceWidth;
            $cropHeight = (int) round($sourceWidth / $targetRatio);
            $srcX = 0;
            $srcY = (int) floor(($sourceHeight - $cropHeight) / 2);
        }

        imagecopyresampled($canvas, $source, 0, 0, $srcX, $srcY, $width, $height, $cropWidth, $cropHeight);

        $binary = $this->encodeImageBinary($canvas, $extension, 80);

        imagedestroy($canvas);
        imagedestroy($source);

        Storage::disk('public')->put($thumbnailPath, $binary);

        return $thumbnailPath;
    }

    public function getImageUrl(?string $path): ?string
    {
        if (empty($path)) {
            return null;
        }

        return app_storage_asset($path) ?? app_public_asset("storage/{$path}");
    }

    public function storeSquareWebp(UploadedFile $file, string $directory = 'uploads', int $size = 320, int $quality = 82): string
    {
        [$source, $width, $height, $mime] = $this->createImageResource($file->getRealPath());
        $source = $this->normalizeOrientation($file->getRealPath(), $mime, $source, $width, $height);

        $canvas = imagecreatetruecolor($size, $size);
        if (! $canvas) {
            imagedestroy($source);

            throw new \RuntimeException('Canvas yaratilmadi.');
        }

        $this->prepareCanvas($canvas);

        $cropSide = min($width, $height);
        $srcX = (int) floor(($width - $cropSide) / 2);
        $srcY = (int) floor(($height - $cropSide) / 2);

        imagecopyresampled(
            $canvas,
            $source,
            0,
            0,
            $srcX,
            $srcY,
            $size,
            $size,
            $cropSide,
            $cropSide
        );

        $binary = $this->encodeImageBinary($canvas, 'webp', $quality);

        imagedestroy($canvas);
        imagedestroy($source);

        $path = trim($directory, '/').'/'.Str::uuid().'.webp';
        Storage::disk('public')->put($path, $binary);

        return $path;
    }

    private function createImageResource(string $path): array
    {
        $contents = @file_get_contents($path);

        if ($contents === false) {
            throw new \RuntimeException("Faylni o'qib bo'lmadi.");
        }

        $image = @imagecreatefromstring($contents);

        if (! $image) {
            throw new \RuntimeException('Rasm yaratilolmadi.');
        }

        $dimensions = @getimagesize($path);
        if (! is_array($dimensions) || empty($dimensions[0]) || empty($dimensions[1])) {
            imagedestroy($image);

            throw new \RuntimeException("Rasm o'lchami aniqlanmadi.");
        }

        $mime = strtolower((string) ($dimensions['mime'] ?? ''));

        return [$image, (int) $dimensions[0], (int) $dimensions[1], $mime];
    }

    private function normalizeOrientation(string $path, string $mime, $image, int &$width, int &$height)
    {
        if (! function_exists('exif_read_data')) {
            return $image;
        }

        if (! in_array($mime, ['image/jpeg', 'image/jpg'], true)) {
            return $image;
        }

        $exif = @exif_read_data($path);
        $orientation = (int) ($exif['Orientation'] ?? 1);

        $rotated = match ($orientation) {
            3 => imagerotate($image, 180, 0),
            6 => imagerotate($image, -90, 0),
            8 => imagerotate($image, 90, 0),
            default => $image,
        };

        if ($rotated !== $image && $rotated) {
            imagedestroy($image);
            $image = $rotated;

            if (in_array($orientation, [6, 8], true)) {
                [$width, $height] = [$height, $width];
            }
        }

        return $image;
    }

    private function prepareCanvas($canvas): void
    {
        imagealphablending($canvas, false);
        imagesavealpha($canvas, true);

        $transparent = imagecolorallocatealpha($canvas, 255, 255, 255, 127);
        imagefill($canvas, 0, 0, $transparent);
    }

    private function encodeImageBinary($image, string $extension, int $quality): string
    {
        $extension = strtolower($extension);
        $quality = max(0, min(100, $quality));

        ob_start();
        $encoded = match ($extension) {
            'png' => imagepng($image, null, (int) round((100 - $quality) / 10)),
            'webp' => imagewebp($image, null, $quality),
            'jpg', 'jpeg' => imagejpeg($image, null, $quality),
            default => imagejpeg($image, null, $quality),
        };
        $binary = ob_get_clean();

        if (! $encoded || ! is_string($binary) || $binary === '') {
            throw new \RuntimeException('Rasmni saqlash formati yaratilolmadi.');
        }

        return $binary;
    }
}
