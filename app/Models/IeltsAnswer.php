<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IeltsAnswer extends Model
{
    use HasFactory;

    protected $fillable = ['ielts_attempt_id', 'ielts_question_id', 'answer_text', 'is_correct', 'ai_feedback'];

    protected $casts = [
        'is_correct' => 'boolean',
        'ai_feedback' => 'array',
    ];

    public function attempt()
    {
        return $this->belongsTo(IeltsAttempt::class, 'ielts_attempt_id');
    }

    public function question()
    {
        return $this->belongsTo(IeltsQuestion::class, 'ielts_question_id');
    }
}
