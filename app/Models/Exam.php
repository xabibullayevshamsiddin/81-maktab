<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class Exam extends Model
{
    protected $fillable = [
        'title',
        'duration_minutes',
        'required_questions',
        'total_points',
        'passing_points',
        'allowed_grades',
        'is_active',
        'available_from',
        'created_by',
    ];

    protected $casts = [
        'allowed_grades' => 'array',
        'is_active' => 'boolean',
        'available_from' => 'datetime',
    ];

    /**
     * Reja sanasi/vaqti berilgan bo‘lsa, faqat shu vaqtdan keyin boshlash mumkin (ilova vaqti — odatda Asia/Tashkent).
     * null = vaqt cheklovi yo‘q.
     */
    public function isOpenForStarting(): bool
    {
        if ($this->available_from === null) {
            return true;
        }

        $from = $this->available_from instanceof Carbon
            ? $this->available_from->copy()
            : Carbon::parse($this->available_from);

        return now()->greaterThanOrEqualTo($from);
    }

    /**
     * O‘zbekiston (ilova timezone) bo‘yicha ko‘rinish: sana + soat:daqiqa.
     */
    public function availableFromLabel(): ?string
    {
        if ($this->available_from === null) {
            return null;
        }

        return $this->available_from->timezone(config('app.timezone'))->format('d.m.Y H:i');
    }

    public function allowedGradeItems(): array
    {
        return normalize_school_grade_list($this->allowed_grades ?? []);
    }

    public function hasGradeRestrictions(): bool
    {
        return $this->allowedGradeItems() !== [];
    }

    public function allowedGradesLabel(string $fallback = User::UNIVERSAL_GRADE_LABEL): string
    {
        $grades = $this->allowedGradeItems();

        return $grades !== [] ? implode(', ', $grades) : $fallback;
    }

    public function allowsUser(?User $user): bool
    {
        if (! $this->hasGradeRestrictions()) {
            return true;
        }

        if (! $user) {
            return false;
        }

        if (method_exists($user, 'hasUniversalGrade') && $user->hasUniversalGrade()) {
            return true;
        }

        $userGrade = normalize_school_grade($user->grade);

        return $userGrade !== null && in_array($userGrade, $this->allowedGradeItems(), true);
    }

    public function sumQuestionPoints(): int
    {
        return (int) $this->questions()->sum('points');
    }

    /**
     * Reja bo‘yicha savollar soni to‘liq va har bir savol bali berilgan, yig‘indi umumiy ballga teng bo‘lsa faol.
     */
    public function syncActiveFromQuestions(): void
    {
        $count = $this->questions()->count();
        $sumPoints = $this->sumQuestionPoints();
        $total = (int) $this->total_points;

        $complete = $this->required_questions > 0
            && $count >= $this->required_questions
            && $sumPoints === $total;

        if ((bool) $this->is_active !== $complete) {
            $this->forceFill(['is_active' => $complete])->save();
        }
    }

    public function isQuestionQuotaFilled(): bool
    {
        return $this->questions()->count() >= $this->required_questions;
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }

    public function results(): HasMany
    {
        return $this->hasMany(Result::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function ownsExam(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        if ($user->isAdmin() || $user->isSuperAdmin()) {
            return true;
        }

        return (int) $this->created_by === (int) $user->id;
    }

    public function isOwnedByTeacher(): bool
    {
        return $this->created_by !== null;
    }
}
