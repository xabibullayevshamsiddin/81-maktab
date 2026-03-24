<?php

namespace App\Repositories;

use App\Models\Comment;
use Illuminate\Database\Eloquent\Collection;

class CommentRepository
{
    public function getAll(): Collection
    {
        return Comment::latestFirst()->get();
    }

    public function getApproved(): Collection
    {
        return Comment::approved()->latestFirst()->get();
    }

    public function getPending(): Collection
    {
        return Comment::pending()->latestFirst()->get();
    }

    public function getByPost(int $postId): Collection
    {
        return Comment::forPost($postId)->latestFirst()->get();
    }

    public function getById(int $id): ?Comment
    {
        return Comment::find($id);
    }

    public function create(array $data): Comment
    {
        return Comment::create($data);
    }

    public function update(Comment $comment, array $data): Comment
    {
        $comment->update($data);

        return $comment->fresh();
    }

    public function approve(Comment $comment): Comment
    {
        return $this->update($comment, ['is_approved' => true]);
    }

    public function delete(Comment $comment): bool
    {
        return $comment->delete();
    }
}
