<?php

namespace App\Models;

use App\Support\PublicStorage;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Post extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected static function booted(): void
    {
        static::updating(function (Post $post): void {
            foreach (['image', 'video_path'] as $attribute) {
                if ($post->isDirty($attribute)) {
                    PublicStorage::delete($post->getOriginal($attribute));
                }
            }
        });

        static::deleted(function (Post $post): void {
            PublicStorage::deleteMany([
                $post->image,
                $post->video_path,
            ]);
        });
    }

    public function hasVideo(): bool
    {
        return filled($this->video_path) || filled($this->video_url);
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
}
