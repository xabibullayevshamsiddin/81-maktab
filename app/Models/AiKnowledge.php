<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
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
        'synonyms',
        'category',
        'is_active',
        'priority',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'priority' => 'integer',
        'sort_order' => 'integer',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrderedForMatching(Builder $query): Builder
    {
        return $query
            ->orderByDesc('priority')
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function synonymItems(): array
    {
        return collect(explode(',', (string) $this->synonyms))
            ->map(static fn (string $item) => trim($item))
            ->filter()
            ->values()
            ->all();
    }
}
