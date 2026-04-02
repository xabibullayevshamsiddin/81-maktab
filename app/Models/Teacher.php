<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Teacher extends Model
{
    use HasFactory;

    protected $fillable = [
        'full_name',
        'slug',
        'subject',
        'experience_years',
        'grades',
        'bio',
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

    public function likes(): HasMany
    {
        return $this->hasMany(TeacherLike::class);
    }
}

