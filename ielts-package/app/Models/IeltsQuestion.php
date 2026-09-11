<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IeltsQuestion extends Model
{
    use HasFactory;

    protected $fillable = ['ielts_passage_id', 'type', 'question_text', 'options', 'correct_answer', 'order'];

    protected $casts = [
        'options' => 'array',
    ];

    public function passage()
    {
        return $this->belongsTo(IeltsPassage::class, 'ielts_passage_id');
    }
}
