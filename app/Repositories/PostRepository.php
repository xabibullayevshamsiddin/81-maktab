<?php

namespace App\Repositories;

use App\Models\Post;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class PostRepository
{
    public function getAll(): Collection
    {
        return Post::withRelations()->withCounts()->latest()->get();
    }

    public function getPaginated(int $perPage = 10): LengthAwarePaginator
    {
        return Post::withRelations()
            ->withCounts()
            ->latest()
            ->paginate($perPage);
    }

    public function getBySlug(string $slug): ?Post
    {
        return Post::with(['category', 'comments' => function ($q) {
            $q->latest();
        }])->withCounts()->where('slug', $slug)->first();
    }

    public function search(string $query, int $perPage = 6): LengthAwarePaginator
    {
        return Post::search($query)
            ->withRelations()
            ->withCounts()
            ->latest()
            ->paginate($perPage);
    }

    public function getPopular(int $count = 3): Collection
    {
        return Post::popular()
            ->withRelations()
            ->withCounts()
            ->latest()
            ->take($count)
            ->get();
    }

    public function getByCategory(int $categoryId, int $perPage = 6): LengthAwarePaginator
    {
        return Post::where('category_id', $categoryId)
            ->withRelations()
            ->withCounts()
            ->latest()
            ->paginate($perPage);
    }

    public function create(array $data): Post
    {
        return Post::create($data);
    }

    public function update(Post $post, array $data): Post
    {
        $post->update($data);

        return $post->fresh();
    }

    public function delete(Post $post): bool
    {
        return $post->delete();
    }

    public function incrementViews(Post $post): void
    {
        $post->increment('views');
    }
}
