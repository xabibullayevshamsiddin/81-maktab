<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AiKnowledge extends Model
{
    protected $table = 'ai_knowledges';

    protected $fillable = [
        'question',
        'question_en',
        'answer',
        'answer_en',
        'keywords',
        'category',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function getQuestionNameAttribute(): string
    {
        return localized_model_value($this, 'question');
    }

    public function getAnswerTextAttribute(): string
    {
        return localized_model_value($this, 'answer');
    }
}
