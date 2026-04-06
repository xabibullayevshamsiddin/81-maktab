<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TeacherComment extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'is_approved' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(TeacherComment::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(TeacherComment::class, 'parent_id');
    }

    public function likes(): HasMany
    {
        return $this->hasMany(TeacherCommentLike::class, 'teacher_comment_id');
    }
}
