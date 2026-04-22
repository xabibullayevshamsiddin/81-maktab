<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeacherCommentLike extends Model
{
    protected $guarded = [];

    public function teacherComment(): BelongsTo
    {
        return $this->belongsTo(TeacherComment::class, 'teacher_comment_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
