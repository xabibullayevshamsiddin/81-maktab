<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Trait ManagesCourses
 *
 * Handles course-related functionality for User model.
 * Includes course creation, teacher profiles, and course open approvals.
 *
 * @property-read int $created_courses_count
 * @property-read int $active_teacher_profile_count
 * @property-read string|null $donation_rank
 */
trait ManagesCourses
{
    /**
     * Relationships
     */
    public function teacherProfile(): HasOne
    {
        return $this->hasOne(\App\Models\Teacher::class);
    }

    public function createdCourses(): HasMany
    {
        return $this->hasMany(\App\Models\Course::class, 'created_by');
    }

    public function courseEnrollments(): HasMany
    {
        return $this->hasMany(\App\Models\CourseEnrollment::class);
    }

    /**
     * Teacher Profile Checks
     */
    public function hasLinkedActiveTeacherProfile(): bool
    {
        // Use preloaded count if available (for optimization)
        if (array_key_exists('active_teacher_profile_count', $this->attributes)) {
            return (int) $this->attributes['active_teacher_profile_count'] > 0;
        }

        return $this->teacherProfile()
            ->where('is_active', true)
            ->exists();
    }

    /**
     * Course Creation Checks
     */
    public function hasCreatedCourse(): bool
    {
        // Use preloaded count if available (for optimization)
        if (array_key_exists('created_courses_count', $this->attributes)) {
            return (int) $this->attributes['created_courses_count'] > 0;
        }

        return $this->createdCourses()->exists();
    }

    /**
     * Donor o'qituvchilar uchun Kurs ochish imtiyozlari:
     *  - Oddiy:        1 ta
     *  - Supporter:    2 ta
     *  - Premium:      3 ta
     *  - VIP:          5 ta
     */
    public function hasReachedCourseOpenLimit(): bool
    {
        $limit = $this->donorCourseLimit();

        // Use preloaded count if available (for optimization)
        if (array_key_exists('created_courses_count', $this->attributes)) {
            return (int) $this->attributes['created_courses_count'] >= $limit;
        }

        return $this->createdCourses()->count() >= $limit;
    }

    /**
     * Donor reytingiga qarab nechta kurs ocha olishini qaytaradi.
     */
    public function donorCourseLimit(): int
    {
        if (!$this->isDonor()) {
            return 1;
        }

        return match ($this->donation_rank) {
            \App\Models\Donation::RANK_SUPPORTER => 2,
            \App\Models\Donation::RANK_PREMIUM => 3,
            \App\Models\Donation::RANK_VIP => 5,
            default => 1,
        };
    }

    /**
     * Course Open Approval
     */
    public function hasCourseOpenApproval(): bool
    {
        return (bool) ($this->course_open_approved ?? false);
    }

    /**
     * Pending request awaiting admin response (not yet approved).
     */
    public function hasPendingCourseOpenRequest(): bool
    {
        return (bool) ($this->course_open_request_pending ?? false);
    }
}
