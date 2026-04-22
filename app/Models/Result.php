<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Result extends Model
{
    protected $fillable = [
        'exam_id',
        'user_id',
        'user_grade',
        'question_order_json',
        'score',
        'points_earned',
        'points_max',
        'passed',
        'total_questions',
        'started_at',
        'expires_at',
        'submitted_at',
        'status',
        'rule_violation_count',
    ];

    protected $casts = [
        'question_order_json' => 'array',
        'started_at' => 'datetime',
        'expires_at' => 'datetime',
        'submitted_at' => 'datetime',
        'passed' => 'boolean',
    ];

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class)->withTrashed();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(Answer::class);
    }
}

