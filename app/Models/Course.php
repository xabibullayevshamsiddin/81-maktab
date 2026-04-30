<?php

namespace App\Models;

use App\Support\PublicStorage;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Course extends Model
{
    use HasFactory;

    public const STATUS_DRAFT = 'draft';
    public const STATUS_PENDING_VERIFICATION = 'pending_verification';
    public const STATUS_PUBLISHED = 'published';

    protected $fillable = [
        'teacher_id',
        'created_by',
        'title',
        'title_en',
        'price',
        'price_en',
        'duration',
        'duration_en',
        'description',
        'description_en',
        'image',
        'start_date',
        'status',
        'rejection_reason',
        'publish_code',
        'publish_code_expires_at',
    ];

    protected $casts = [
        'start_date' => 'date',
        'publish_code_expires_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::updating(function (Course $course): void {
            if ($course->isDirty('image')) {
                PublicStorage::delete($course->getOriginal('image'));
            }
        });

        static::deleted(function (Course $course): void {
            PublicStorage::delete($course->image);
        });
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(CourseEnrollment::class);
    }

    public function instructorName(): string
    {
        $teacherName = trim((string) $this->teacher?->full_name);
        if ($teacherName !== '') {
            return $teacherName;
        }

        $creatorName = trim((string) ($this->creator?->name ?: $this->creator?->buildNameFromParts()));

        return $creatorName !== '' ? $creatorName : 'Kurs muallifi';
    }

    public function instructorSubject(): string
    {
        if ($this->teacher) {
            $subject = trim((string) localized_model_value($this->teacher, 'subject'));
            if ($subject !== '') {
                return $subject;
            }
        }

        return $this->creator?->localizedRoleLabel() ?: "O'qituvchi";
    }

    public function instructorImageUrl(): string
    {
        if ($this->teacher?->image) {
            return app_storage_asset($this->teacher->image) ?? app_public_asset('temp/img/how-to-be-teacher-malaysia-feature.png');
        }

        return $this->creator?->avatar_url ?? app_public_asset('temp/img/how-to-be-teacher-malaysia-feature.png');
    }

    public function instructorBio(int $limit = 260): string
    {
        if ($this->teacher) {
            return $this->teacher->shortBio($limit);
        }

        return "Bu kurs {$this->instructorName()} tomonidan ochilgan. Batafsil ma'lumot va aloqa kursga yozilish arizasi orqali olib boriladi.";
    }

    public function instructorExperienceLabel(): string
    {
        if ($this->teacher) {
            return ((int) ($this->teacher->experience_years ?? 0)).' yil tajriba';
        }

        return "O'qituvchi akkaunti";
    }

    public function instructorGradesLabel(): string
    {
        if ($this->teacher) {
            return $this->teacher->grades ?: 'Barcha sinflar';
        }

        return $this->creator?->displayGrade('Barcha sinflar') ?: 'Barcha sinflar';
    }

    public function instructorAchievements(): array
    {
        return $this->teacher?->achievementItems(null, app()->getLocale()) ?? [];
    }

    /**
     * Karta uchun rasm: avval kurs rasmi, bo'lmasa ustoz rasmi, bo'lmasa umumiy placeholder.
     */
    public function coverImageUrl(): string
    {
        if (! empty($this->image)) {
            return app_storage_asset($this->image) ?? app_public_asset('temp/img/how-to-be-teacher-malaysia-feature.png');
        }

        return $this->instructorImageUrl();
    }
}
