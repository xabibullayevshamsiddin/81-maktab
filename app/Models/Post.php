<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Post extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function scopePublished(Builder $query): Builder
    {
        return $query->whereNotNull('published_at');
    }

    public function scopePopular(Builder $query): Builder
    {
        return $query->orderByDesc('views');
    }

    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        if (empty($search)) {
            return $query;
        }

        return $query->where(function ($q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
                ->orWhere('short_content', 'like', "%{$search}%");
        });
    }

    public function scopeWithRelations(Builder $query): Builder
    {
        return $query->with(['category', 'comments', 'likes']);
    }

    public function scopeWithCounts(Builder $query): Builder
    {
        return $query->withCount(['comments', 'likes']);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function likes(): HasMany
    {
        return $this->hasMany(PostLike::class);
    }

    public function isPublished(): bool
    {
        return ! is_null($this->published_at);
    }

    public function getExcerpt(int $length = 150): string
    {
        return \Illuminate\Support\Str::limit(strip_tags($this->content), $length);
    }
}
