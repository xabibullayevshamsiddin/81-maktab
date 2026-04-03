<?php

namespace App\Models;

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
        'price',
        'duration',
        'description',
        'image',
        'start_date',
        'status',
        'publish_code',
        'publish_code_expires_at',
    ];

    protected $casts = [
        'start_date' => 'date',
        'publish_code_expires_at' => 'datetime',
    ];

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

    /**
     * Karta uchun rasm: avval kurs rasmi, bo‘lmasa ustoz rasmi, bo‘lmasa umumiy placeholder.
     */
    public function coverImageUrl(): string
    {
        if (! empty($this->image)) {
            return asset('storage/'.$this->image);
        }

        if ($this->teacher?->image) {
            return asset('storage/'.$this->teacher->image);
        }

        return asset('temp/img/how-to-be-teacher-malaysia-feature.png');
    }
}

