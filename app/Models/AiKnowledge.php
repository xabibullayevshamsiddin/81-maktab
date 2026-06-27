<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class AiKnowledge extends Model
{
    protected static array $columnPresenceCache = [];

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
        if (! static::hasDatabaseColumn('is_active')) {
            return $query;
        }

        return $query->where('is_active', true);
    }

    public function scopeOrderedForMatching(Builder $query): Builder
    {
        if (static::hasDatabaseColumn('priority')) {
            $query->orderByDesc('priority');
        }

        if (static::hasDatabaseColumn('sort_order')) {
            $query->orderBy('sort_order');
        }

        return $query->orderBy('id');
    }

    public function synonymItems(): array
    {
        if (! static::hasDatabaseColumn('synonyms')) {
            return [];
        }

        return collect(explode(',', (string) $this->synonyms))
            ->map(static fn (string $item) => trim($item))
            ->filter()
            ->values()
            ->all();
    }

    public static function hasDatabaseColumn(string $column): bool
    {
        $instance = new static();
        $table = $instance->getTable();
        $cacheKey = $table.'.'.$column;

        if (! array_key_exists($cacheKey, static::$columnPresenceCache)) {
            static::$columnPresenceCache[$cacheKey] = Schema::hasTable($table)
                && Schema::hasColumn($table, $column);
        }

        return static::$columnPresenceCache[$cacheKey];
    }

    public static function availableColumns(array $columns): array
    {
        return array_values(array_filter(
            $columns,
            static fn (string $column): bool => static::hasDatabaseColumn($column)
        ));
    }

    public static function flushColumnPresenceCache(): void
    {
        static::$columnPresenceCache = [];
    }
}
