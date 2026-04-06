<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Answer extends Model
{
    protected $fillable = [
        'result_id',
        'question_id',
        'option_id',
        'text_answer',
        'is_correct_override',
        'answered_at',
    ];

    protected $casts = [
        'answered_at' => 'datetime',
        'is_correct_override' => 'boolean',
    ];

    public function result(): BelongsTo
    {
        return $this->belongsTo(Result::class);
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    public function option(): BelongsTo
    {
        return $this->belongsTo(Option::class);
    }

    public function isCorrectAnswer(): bool
    {
        $this->loadMissing(['question', 'option']);

        if ($this->question && $this->question->isTextType()) {
            return (bool) $this->is_correct_override;
        }

        if ($this->option) {
            return (bool) $this->option->is_correct;
        }

        return false;
    }
}
