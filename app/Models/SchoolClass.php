<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class SchoolClass extends Model
{
    use HasFactory;

    protected $fillable = [
        'grade_number',
        'section',
        'name',
        'is_active',
        'sort_order',
        'max_students',
    ];

    protected $casts = [
        'grade_number'  => 'integer',
        'is_active'     => 'boolean',
        'sort_order'    => 'integer',
        'max_students'  => 'integer',
    ];

    /**
     * Current enrolled student count for this class.
     * Counts non-parent users with role USER whose grade matches this class's display_name.
     */
    public function currentStudentCount(): int
    {
        return \App\Models\User::query()
            ->where('grade', $this->display_name)
            ->where('is_parent', false)
            ->whereHas('roleRelation', fn ($q) => $q->where('name', \App\Models\User::ROLE_USER))
            ->count();
    }

    /** Returns true when the class has a capacity limit and it is reached or exceeded. */
    public function isFull(): bool
    {
        if ($this->max_students === null || $this->max_students <= 0) {
            return false;
        }

        return $this->currentStudentCount() >= $this->max_students;
    }

    /** Remaining free slots, or null if unlimited. */
    public function availableSlots(): ?int
    {
        if ($this->max_students === null || $this->max_students <= 0) {
            return null;
        }

        return max(0, $this->max_students - $this->currentStudentCount());
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function getDisplayNameAttribute(): string
    {
        return self::buildName((int) $this->grade_number, (string) $this->section);
    }

    public static function normalizeSection(string $section): string
    {
        $section = Str::upper(trim($section));
        $section = preg_replace('/[^A-Z0-9]/', '', $section) ?? '';

        return Str::limit($section, 10, '');
    }

    public static function buildName(int $gradeNumber, string $section): string
    {
        return $gradeNumber.'-'.self::normalizeSection($section);
    }

    public static function activeMap(): array
    {
        return self::query()
            ->active()
            ->orderBy('grade_number')
            ->orderBy('sort_order')
            ->orderBy('section')
            ->get(['grade_number', 'section'])
            ->groupBy('grade_number')
            ->map(fn ($classes) => $classes->pluck('section')->values()->all())
            ->all();
    }

    public static function activeNames(): array
    {
        return self::query()
            ->active()
            ->orderBy('grade_number')
            ->orderBy('sort_order')
            ->orderBy('section')
            ->get(['grade_number', 'section'])
            ->map(fn (SchoolClass $schoolClass): string => $schoolClass->display_name)
            ->values()
            ->all();
    }
}
