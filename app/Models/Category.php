<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'name_en',
    ];

    public function scopeWithPostCount(Builder $query): Builder
    {
        return $query->withCount('posts');
    }

    public function scopeOrderByName(Builder $query): Builder
    {
        return $query->orderBy('name');
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    public function getPostsCountAttribute(): int
    {
        return $this->posts()->count();
    }
}
