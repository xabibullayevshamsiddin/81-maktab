<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Trait ManagesCourses
 * 
 * Handles course-related functionality for User model.
 * Includes course creation, teacher profiles, and course open approvals.
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
     * Teacher can only create one course, then cannot create more.
     */
    public function hasReachedCourseOpenLimit(): bool
    {
        // Use preloaded count if available (for optimization)
        if (array_key_exists('created_courses_count', $this->attributes)) {
            return (int) $this->attributes['created_courses_count'] >= 1;
        }

        return $this->createdCourses()->count() >= 1;
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
