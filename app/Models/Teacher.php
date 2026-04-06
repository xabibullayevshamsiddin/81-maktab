<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Teacher extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'full_name',
        'slug',
        'subject',
        'subject_en',
        'experience_years',
        'grades',
        'achievements',
        'achievements_en',
        'bio',
        'bio_en',
        'image',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'experience_years' => 'integer',
        'sort_order' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (Teacher $teacher): void {
            if (! $teacher->slug && $teacher->full_name) {
                $teacher->slug = Str::slug($teacher->full_name);
            }
        });
    }

    public function courses(): HasMany
    {
        return $this->hasMany(Course::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function likes(): HasMany
    {
        return $this->hasMany(TeacherLike::class);
    }

    public function achievementItems(?int $limit = null, ?string $locale = null): array
    {
        $items = collect(preg_split("/\r\n|\r|\n/", localized_model_value($this, 'achievements', $locale)))
            ->map(static fn ($line) => trim((string) $line))
            ->filter()
            ->values();

        if ($limit !== null) {
            $items = $items->take($limit)->values();
        }

        return $items->all();
    }

    public function shortBio(int $limit = 220, ?string $locale = null): string
    {
        $subject = localized_model_value($this, 'subject', $locale);
        $bio = localized_model_value($this, 'bio', $locale);

        $fallback = $subject
            ? $subject." fani bo'yicha ".$this->experience_years." yillik tajribaga ega ustoz."
            : 'Tajribali va natijaga yonaltirilgan ustoz.';

        return Str::limit(trim((string) ($bio ?: $fallback)), $limit);
    }

    public function imageUrl(): string
    {
        if (! empty($this->image)) {
            return app_storage_asset($this->image) ?? app_public_asset('temp/img/how-to-be-teacher-malaysia-feature.png');
        }

        return app_public_asset('temp/img/how-to-be-teacher-malaysia-feature.png');
    }
}
