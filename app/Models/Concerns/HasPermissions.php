<?php

namespace App\Models\Concerns;

use App\Models\Course;
use App\Models\User;

/**
 * Trait HasPermissions
 * 
 * Handles all permission-related functionality for User model.
 * Includes access checks, management permissions, and moderation capabilities.
 */
trait HasPermissions
{
    /**
     * Dashboard & Access Permissions
     */
    public function canAccessDashboard(): bool
    {
        return $this->hasAnyRole([
            self::ROLE_SUPER_ADMIN,
            self::ROLE_ADMIN,
            self::ROLE_EDITOR,
            self::ROLE_MODERATOR,
        ]);
    }

    /**
     * Content Management Permissions
     */
    public function canManageContent(): bool
    {
        return $this->hasAnyRole([
            self::ROLE_SUPER_ADMIN,
            self::ROLE_ADMIN,
            self::ROLE_EDITOR,
        ]);
    }

    /**
     * Inbox Management (Comments & Contact Messages)
     * Only super admin, admin, and moderator.
     * Editor is limited to news/calendar/categories (canManageContent).
     */
    public function canManageInbox(): bool
    {
        return $this->hasAnyRole([
            self::ROLE_SUPER_ADMIN,
            self::ROLE_ADMIN,
            self::ROLE_MODERATOR,
        ]);
    }

    /**
     * Education Management (Courses & Exams)
     */
    public function canManageEducation(): bool
    {
        return $this->hasAnyRole([
            self::ROLE_SUPER_ADMIN,
            self::ROLE_ADMIN,
            self::ROLE_TEACHER,
        ]);
    }

    /**
     * Exam Management
     */
    public function canManageExams(): bool
    {
        return $this->canManageEducation();
    }

    /**
     * Teacher Management
     */
    public function canManageTeachers(): bool
    {
        return $this->hasAnyRole([
            self::ROLE_SUPER_ADMIN,
            self::ROLE_ADMIN,
        ]);
    }

    /**
     * System Management (Users, Settings)
     */
    public function canManageSystem(): bool
    {
        return $this->hasAnyRole([
            self::ROLE_SUPER_ADMIN,
            self::ROLE_ADMIN,
        ]);
    }

    /**
     * Comment Moderation
     */
    public function canModerateCommentAuthor(?User $author): bool
    {
        if (! $this->isModerator()) {
            return false;
        }

        if (! $author) {
            return true;
        }

        $authorLevel = $author->roleLevel();
        $myLevel = $this->roleLevel();

        return $myLevel >= $authorLevel;
    }

    public function canManageCommentAsStaff(?User $commentAuthor, int|string|null $commentUserId): bool
    {
        if (! $this->isModerator()) {
            return false;
        }

        if ($commentUserId === null) {
            return true;
        }

        if ($commentAuthor === null) {
            return false;
        }

        return $this->canModerateCommentAuthor($commentAuthor);
    }

    /**
     * Course Ownership
     */
    public function ownsCourse(Course $course): bool
    {
        return (int) $course->created_by === (int) $this->id;
    }
}
