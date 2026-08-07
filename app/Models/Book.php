<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;

class Book extends Model
{
    protected $fillable = [
        'book_category_id', 'title', 'title_en', 'description', 'description_en',
        'author', 'subject', 'year', 'grade', 'file_path', 'cover_image',
        'file_size', 'download_count', 'view_count', 'is_active', 'allow_download',
    ];

    protected $casts = [
        'is_active'      => 'boolean',
        'allow_download' => 'boolean',
        'year'           => 'integer',
        'file_size'      => 'integer',
        'download_count' => 'integer',
        'view_count'     => 'integer',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(BookCategory::class, 'book_category_id');
    }

    public function getLocalizedTitleAttribute(): string
    {
        $locale = app()->getLocale();
        if ($locale !== 'uz' && filled($this->title_en)) return $this->title_en;
        return $this->title;
    }

    public function getLocalizedDescriptionAttribute(): ?string
    {
        $locale = app()->getLocale();
        if ($locale !== 'uz' && filled($this->description_en)) return $this->description_en;
        return $this->description;
    }

    public function coverImageUrl(): string
    {
        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk('public');
        if ($this->cover_image && $disk->exists($this->cover_image)) {
            return $disk->url($this->cover_image);
        }
        return app_public_asset('temp/img/photo_2026-02-06_11-05-24-2.jpg');
    }

    public function fileSizeLabel(): string
    {
        $bytes = $this->file_size;
        if ($bytes >= 1048576) return round($bytes / 1048576, 1) . ' MB';
        if ($bytes >= 1024)    return round($bytes / 1024, 0) . ' KB';
        return $bytes . ' B';
    }

    /** @param \Illuminate\Database\Eloquent\Builder $query */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function incrementView(): void
    {
        $this->increment('view_count');
    }

    public function incrementDownload(): void
    {
        $this->increment('download_count');
    }
}
