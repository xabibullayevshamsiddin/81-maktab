<?php

namespace App\Models;

use App\Support\PublicStorage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Question extends Model
{
    public const TYPE_MCQ = 'multiple_choice';
    public const TYPE_TEXT = 'text';

    protected $fillable = [
        'exam_id',
        'body',
        'image_path',
        'sort_order',
        'points',
        'question_type',
        'model_answer',
    ];

    protected $appends = [
        'image_url',
    ];

    protected static function booted(): void
    {
        static::updating(function (Question $question): void {
            if ($question->isDirty('image_path')) {
                PublicStorage::delete($question->getOriginal('image_path'));
            }
        });

        static::deleted(function (Question $question): void {
            PublicStorage::delete($question->image_path);
        });
    }

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function options(): HasMany
    {
        return $this->hasMany(Option::class);
    }

    public function getImageUrlAttribute(): ?string
    {
        if (! filled($this->image_path)) {
            return null;
        }

        return app_storage_asset($this->image_path);
    }

    public function isTextType(): bool
    {
        return $this->question_type === self::TYPE_TEXT;
    }

    public function isMultipleChoice(): bool
    {
        return $this->question_type === self::TYPE_MCQ;
    }
}
