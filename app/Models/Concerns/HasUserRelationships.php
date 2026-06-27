<?php

namespace App\Models\Concerns;

use App\Models\Comment;
use App\Models\PostLike;
use App\Models\TeacherLike;
use App\Models\Bookmark;
use App\Models\UserNotification;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Trait HasUserRelationships
 * 
 * Handles general relationships for User model.
 * Includes comments, likes, bookmarks, and notifications.
 */
trait HasUserRelationships
{
    /**
     * Comment relationships
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * Like relationships
     */
    public function likes(): HasMany
    {
        return $this->hasMany(PostLike::class);
    }

    public function teacherLikes(): HasMany
    {
        return $this->hasMany(TeacherLike::class);
    }

    public function bookmarks(): HasMany
    {
        return $this->hasMany(Bookmark::class);
    }
}
