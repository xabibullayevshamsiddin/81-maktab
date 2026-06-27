<?php

namespace App\Models;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Collection;

class Bookmark extends Model
{
    protected $fillable = [
        'user_id',
        'bookmarkable_type',
        'bookmarkable_id',
    ];

    /**
     * @param  Collection<int, mixed>  $ids
     * @return Collection<int, int|string>
     */
    public static function bookmarkedIdsForUser(?Authenticatable $user, string $bookmarkableClass, Collection $ids): Collection
    {
        if (! $user instanceof User || ! $user->isActive()) {
            return collect();
        }

        $ids = $ids->filter(fn ($id) => $id !== null && $id !== '')->values();
        if ($ids->isEmpty()) {
            return collect();
        }

        return self::query()
            ->where('user_id', $user->id)
            ->where('bookmarkable_type', $bookmarkableClass)
            ->whereIn('bookmarkable_id', $ids)
            ->pluck('bookmarkable_id');
    }

    /**
     * @param  Collection<int, mixed>  $postIds
     * @return Collection<int, int|string>
     */
    public static function bookmarkedPostIdsForUser(?Authenticatable $user, Collection $postIds): Collection
    {
        return self::bookmarkedIdsForUser($user, Post::class, $postIds);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function bookmarkable(): MorphTo
    {
        return $this->morphTo();
    }
}
