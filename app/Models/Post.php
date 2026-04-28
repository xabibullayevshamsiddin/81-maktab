<?php

namespace App\Models;

use App\Support\PublicStorage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'title_en',
        'slug',
        'category_id',
        'post_kind',
        'short_content',
        'short_content_en',
        'content',
        'content_en',
        'image',
        'video_url',
        'video_path',
        'views',
    ];

    protected static function booted(): void
    {
        static::creating(function (Post $post): void {
            if (filled($post->category_id)) {
                return;
            }

            $fallbackCategoryId = Category::query()->value('id');

            if (! $fallbackCategoryId) {
                $fallbackCategoryId = Category::query()->create([
                    'name' => 'Umumiy',
                ])->id;
            }

            $post->category_id = $fallbackCategoryId;
        });

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

    /**
     * @param  Builder<Post>  $query
     * @return Builder<Post>
     */
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        $term = trim((string) $term);

        if ($term === '') {
            return $query;
        }

        $like = '%'.addcslashes($term, '%_\\').'%';

        return $query->where(function (Builder $query) use ($like): void {
            $query->where('title', 'like', $like)
                ->orWhere('title_en', 'like', $like)
                ->orWhere('slug', 'like', $like)
                ->orWhere('short_content', 'like', $like)
                ->orWhere('short_content_en', 'like', $like)
                ->orWhere('content', 'like', $like)
                ->orWhere('content_en', 'like', $like);
        });
    }

    /**
     * @param  Builder<Post>  $query
     * @return Builder<Post>
     */
    public function scopeWithRelations(Builder $query): Builder
    {
        return $query->with(['category']);
    }

    /**
     * @param  Builder<Post>  $query
     * @return Builder<Post>
     */
    public function scopeWithCounts(Builder $query): Builder
    {
        return $query->withCount(['comments', 'likes']);
    }

    /**
     * @param  Builder<Post>  $query
     * @return Builder<Post>
     */
    public function scopePopular(Builder $query): Builder
    {
        return $query->orderByDesc('views');
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
