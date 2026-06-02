<?php

namespace App\Models\Concerns;

use App\Models\Comment;
use App\Models\PostLike;
use App\Models\TeacherLike;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Trait HasUserRelationships
 * 
 * Handles general relationships for User model.
 * Includes comments, likes, and other user-related relationships.
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
}
