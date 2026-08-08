<?php

namespace App\Models;

use App\Support\PublicStorage;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

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
        'max_enrollments',
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

            Bookmark::query()
                ->where('bookmarkable_type', self::class)
                ->where('bookmarkable_id', $course->id)
                ->delete();
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

    public function bookmarks(): MorphMany
    {
        return $this->morphMany(Bookmark::class, 'bookmarkable');
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
            return app_storage_asset($this->teacher->image) ?? app_public_asset('temp/img/ChatGPT Image Jul 5, 2026, 01_38_09 AM.png');
        }

        return $this->creator?->avatar_url ?? app_public_asset('temp/img/ChatGPT Image Jul 5, 2026, 01_38_09 AM.png');
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
     * Karta uchun rasm: avval kurs rasmi, bo'lmasa kurs nomi bilan SVG placeholder.
     */
    public function coverImageUrl(): string
    {
        if (! empty($this->image)) {
            return app_storage_asset($this->image) ?? $this->generateCoursePlaceholder();
        }

        return $this->generateCoursePlaceholder();
    }

    /**
     * Kurs nomi bilan SVG placeholder yaratish.
     */
    private function generateCoursePlaceholder(): string
    {
        $title = trim((string) ($this->title ?: 'Kurs'));
        $locale = app()->getLocale();

        // Sarlavhani qisqartirish (maksimal 40 belgi)
        if (mb_strlen($title) > 40) {
            $title = mb_substr($title, 0, 37) . '...';
        }

        // SVG shablon
        $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="800" height="450" viewBox="0 0 800 450">
  <defs>
    <linearGradient id="bg" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" style="stop-color:#1e3a5f"/>
      <stop offset="100%" style="stop-color:#0d1b2a"/>
    </linearGradient>
  </defs>
  <rect width="800" height="450" fill="url(#bg)"/>
  <rect x="50" y="50" width="700" height="350" rx="20" fill="rgba(255,255,255,0.05)"/>
  <text x="400" y="180" text-anchor="middle" font-family="Arial, sans-serif" font-size="42" font-weight="bold" fill="white">📚</text>
  <text x="400" y="250" text-anchor="middle" font-family="Arial, sans-serif" font-size="32" font-weight="bold" fill="white">{$this->escapeXml($title)}</text>
  <text x="400" y="320" text-anchor="middle" font-family="Arial, sans-serif" font-size="18" fill="rgba(255,255,255,0.6)">81-IDUM Kurs</text>
</svg>
SVG;

        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }

    /**
     * XML uchun maxsus belgilarni escape qilish.
     */
    private function escapeXml(string $text): string
    {
        return htmlspecialchars($text, ENT_XML1, 'UTF-8');
    }

    /**
     * Tasdiqlangan yozilishlar soni.
     */
    public function approvedEnrollmentCount(): int
    {
        return $this->enrollments()
            ->where('status', CourseEnrollment::STATUS_APPROVED)
            ->count();
    }

    /**
     * Jami pending + approved yozilishlar soni.
     */
    public function totalEnrollmentCount(): int
    {
        return $this->enrollments()
            ->whereIn('status', [CourseEnrollment::STATUS_PENDING, CourseEnrollment::STATUS_APPROVED])
            ->count();
    }

    /**
     * Limit to'lganmi?
     */
    public function isEnrollmentLimitReached(): bool
    {
        if (! $this->max_enrollments) {
            return false;
        }

        return $this->approvedEnrollmentCount() >= $this->max_enrollments;
    }

    /**
     * Limit haqida matn.
     */
    public function enrollmentLimitText(): ?string
    {
        if (! $this->max_enrollments) {
            return null;
        }

        $approved = $this->approvedEnrollmentCount();
        return "{$approved} / {$this->max_enrollments} o'quvchi";
    }
}
