<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;

class FileUploadValidator
{
    /**
     * Maximum file size in kilobytes
     */
    private const MAX_FILE_SIZE_KB = 10240; // 10MB

    /**
     * Maximum file size for images in kilobytes
     */
    private const MAX_IMAGE_SIZE_KB = 5120; // 5MB

    /**
     * Maximum file size for videos in kilobytes
     */
    private const MAX_VIDEO_SIZE_KB = 51200; // 50MB

    /**
     * Allowed image MIME types
     */
    private const ALLOWED_IMAGE_MIMES = [
        'image/jpeg',
        'image/jpg',
        'image/png',
        'image/webp',
    ];

    /**
     * Allowed image extensions
     */
    private const ALLOWED_IMAGE_EXTENSIONS = [
        'jpg',
        'jpeg',
        'png',
        'webp',
    ];

    /**
     * Allowed video MIME types
     */
    private const ALLOWED_VIDEO_MIMES = [
        'video/mp4',
        'video/mpeg',
        'video/quicktime',
        'video/x-msvideo',
    ];

    /**
     * Allowed video extensions
     */
    private const ALLOWED_VIDEO_EXTENSIONS = [
        'mp4',
        'mpeg',
        'mov',
        'avi',
    ];

    /**
     * Validate an uploaded image file.
     *
     * @param  UploadedFile  $file
     * @param  int|null  $maxSizeKb  Maximum size in KB (default: 5MB)
     * @return void
     * @throws ValidationException
     */
    public function validateImage(UploadedFile $file, ?int $maxSizeKb = null): void
    {
        $maxSize = $maxSizeKb ?? self::MAX_IMAGE_SIZE_KB;

        // Check if file was uploaded successfully
        if (! $file->isValid()) {
            throw ValidationException::withMessages([
                'image' => 'Fayl yuklashda xatolik yuz berdi. Iltimos, qayta urinib ko\'ring.',
            ]);
        }

        // Check file size
        if ($file->getSize() > $maxSize * 1024) {
            $maxSizeMb = round($maxSize / 1024, 1);
            throw ValidationException::withMessages([
                'image' => "Rasm hajmi {$maxSizeMb}MB dan oshmasligi kerak.",
            ]);
        }

        // Check MIME type
        $mime = $file->getMimeType();
        if (! in_array($mime, self::ALLOWED_IMAGE_MIMES, true)) {
            throw ValidationException::withMessages([
                'image' => 'Faqat JPG, PNG va WEBP formatdagi rasmlar qabul qilinadi.',
            ]);
        }

        // Check file extension
        $extension = strtolower($file->getClientOriginalExtension());
        if (! in_array($extension, self::ALLOWED_IMAGE_EXTENSIONS, true)) {
            throw ValidationException::withMessages([
                'image' => 'Fayl kengaytmasi noto\'g\'ri. Faqat jpg, png, webp ruxsat etilgan.',
            ]);
        }

        // Check if file is actually an image by trying to get dimensions
        $imageInfo = @getimagesize($file->getRealPath());
        if ($imageInfo === false) {
            throw ValidationException::withMessages([
                'image' => 'Fayl haqiqiy rasm emas. Rasm faylini yuklang.',
            ]);
        }

        // Check image dimensions (minimum and maximum)
        [$width, $height] = $imageInfo;
        
        if ($width < 50 || $height < 50) {
            throw ValidationException::withMessages([
                'image' => 'Rasm juda kichik. Kamida 50x50 piksel bo\'lishi kerak.',
            ]);
        }

        if ($width > 8000 || $height > 8000) {
            throw ValidationException::withMessages([
                'image' => 'Rasm juda katta. Maksimal 8000x8000 piksel.',
            ]);
        }

        // Check for malicious content in EXIF data (basic check)
        $this->checkForMaliciousContent($file);
    }

    /**
     * Validate an uploaded video file.
     *
     * @param  UploadedFile  $file
     * @param  int|null  $maxSizeKb  Maximum size in KB (default: 50MB)
     * @return void
     * @throws ValidationException
     */
    public function validateVideo(UploadedFile $file, ?int $maxSizeKb = null): void
    {
        $maxSize = $maxSizeKb ?? self::MAX_VIDEO_SIZE_KB;

        // Check if file was uploaded successfully
        if (! $file->isValid()) {
            throw ValidationException::withMessages([
                'video' => 'Fayl yuklashda xatolik yuz berdi. Iltimos, qayta urinib ko\'ring.',
            ]);
        }

        // Check file size
        if ($file->getSize() > $maxSize * 1024) {
            $maxSizeMb = round($maxSize / 1024, 1);
            throw ValidationException::withMessages([
                'video' => "Video hajmi {$maxSizeMb}MB dan oshmasligi kerak.",
            ]);
        }

        // Check MIME type
        $mime = $file->getMimeType();
        if (! in_array($mime, self::ALLOWED_VIDEO_MIMES, true)) {
            throw ValidationException::withMessages([
                'video' => 'Faqat MP4, MPEG, MOV va AVI formatdagi videolar qabul qilinadi.',
            ]);
        }

        // Check file extension
        $extension = strtolower($file->getClientOriginalExtension());
        if (! in_array($extension, self::ALLOWED_VIDEO_EXTENSIONS, true)) {
            throw ValidationException::withMessages([
                'video' => 'Fayl kengaytmasi noto\'g\'ri. Faqat mp4, mpeg, mov, avi ruxsat etilgan.',
            ]);
        }
    }

    /**
     * Get validation rules for image uploads (for use in Request validation).
     *
     * @param  bool  $required
     * @param  int|null  $maxSizeKb
     * @return array
     */
    public function imageRules(bool $required = true, ?int $maxSizeKb = null): array
    {
        $maxSize = $maxSizeKb ?? self::MAX_IMAGE_SIZE_KB;

        return [
            $required ? 'required' : 'nullable',
            'file',
            'image',
            'mimes:' . implode(',', self::ALLOWED_IMAGE_EXTENSIONS),
            'max:' . $maxSize,
            'dimensions:min_width=50,min_height=50,max_width=8000,max_height=8000',
        ];
    }

    /**
     * Get validation rules for video uploads (for use in Request validation).
     *
     * @param  bool  $required
     * @param  int|null  $maxSizeKb
     * @return array
     */
    public function videoRules(bool $required = false, ?int $maxSizeKb = null): array
    {
        $maxSize = $maxSizeKb ?? self::MAX_VIDEO_SIZE_KB;

        return [
            $required ? 'required' : 'nullable',
            'file',
            'mimes:' . implode(',', self::ALLOWED_VIDEO_EXTENSIONS),
            'max:' . $maxSize,
        ];
    }

    /**
     * Check for potentially malicious content in uploaded files.
     *
     * @param  UploadedFile  $file
     * @return void
     * @throws ValidationException
     */
    private function checkForMaliciousContent(UploadedFile $file): void
    {
        // Read first 1KB of file to check for PHP code, scripts, etc.
        $handle = fopen($file->getRealPath(), 'r');
        if ($handle === false) {
            return;
        }

        $content = fread($handle, 1024);
        fclose($handle);

        if ($content === false) {
            return;
        }

        // Check for PHP tags
        if (stripos($content, '<?php') !== false || stripos($content, '<?=') !== false) {
            throw ValidationException::withMessages([
                'file' => 'Fayl xavfli kod o\'z ichiga oladi.',
            ]);
        }

        // Check for script tags
        if (stripos($content, '<script') !== false) {
            throw ValidationException::withMessages([
                'file' => 'Fayl xavfli skript o\'z ichiga oladi.',
            ]);
        }

        // Check for HTML event handlers
        if (preg_match('/\bon\w+\s*=/i', $content)) {
            throw ValidationException::withMessages([
                'file' => 'Fayl xavfli kod o\'z ichiga oladi.',
            ]);
        }
    }

    /**
     * Get maximum allowed file sizes.
     *
     * @return array
     */
    public static function getMaxSizes(): array
    {
        return [
            'image' => self::MAX_IMAGE_SIZE_KB,
            'video' => self::MAX_VIDEO_SIZE_KB,
            'general' => self::MAX_FILE_SIZE_KB,
        ];
    }

    /**
     * Get allowed image extensions.
     *
     * @return array
     */
    public static function getAllowedImageExtensions(): array
    {
        return self::ALLOWED_IMAGE_EXTENSIONS;
    }

    /**
     * Get allowed video extensions.
     *
     * @return array
     */
    public static function getAllowedVideoExtensions(): array
    {
        return self::ALLOWED_VIDEO_EXTENSIONS;
    }
}
