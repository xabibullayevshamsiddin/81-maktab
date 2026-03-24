<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCommentRequest;
use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\Request;

class PublicPostController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        $postsQuery = Post::query()
            ->with('category')
            ->withCount(['comments', 'likes'])
            ->latest();

        if ($q !== '') {
            $postsQuery->where(function ($sub) use ($q) {
                $sub->where('title', 'like', "%{$q}%")
                    ->orWhere('short_content', 'like', "%{$q}%");
            });
        }

        $posts = $postsQuery->paginate(6)->appends($request->query());

        $topPosts = Post::query()
            ->with('category')
            ->withCount(['comments', 'likes'])
            ->orderByDesc('views')
            ->latest()
            ->take(3)
            ->get();

        return view('post', compact('posts', 'topPosts', 'q'));
    }

    public function show(Post $post)
    {
        $post->load(['category', 'comments' => function ($q) {
            $q->latest()->with('replies');
        }])->loadCount(['comments', 'likes']);

        $post->increment('views');

        $liked = $post->likes()
            ->where(function ($q) {
                if (auth()->check()) {
                    $q->where('user_id', auth()->id());

                    return;
                }
                $q->whereNull('user_id')->where('ip_address', request()->ip());
            })
            ->exists();

        $relatedPosts = Post::query()
            ->where('id', '!=', $post->id)
            ->when($post->category_id, function ($q) use ($post) {
                $q->where('category_id', $post->category_id);
            })
            ->with('category')
            ->withCount(['comments', 'likes'])
            ->latest()
            ->take(3)
            ->get();

        return view('posts.show', compact('post', 'liked', 'relatedPosts'));
    }

    public function storeComment(StoreCommentRequest $request, Post $post)
    {
        $validated = $request->validated();
        $user = $request->user();
        $isAjax = $request->ajax() || $request->wantsJson();

        $comment = $post->comments()->create([
            'user_id' => $user?->id,
            'author_name' => $user?->name ?? ($validated['author_name'] ?? null),
            'body' => $validated['body'],
        ]);

        if ($isAjax) {
            return response()->json([
                'success' => true,
                'message' => "Izoh qo'shildi.",
                'comment' => [
                    'id' => $comment->id,
                    'author_name' => $comment->author_name ?? 'Mehmon',
                    'body' => $comment->body,
                    'created_at' => $comment->created_at->format('d.m.Y H:i'),
                ],
            ]);
        }

        return back()->with('success', "Izoh qo'shildi.");
    }

    public function toggleLike(Request $request, Post $post)
    {
        $user = $request->user();
        $isAjax = $request->ajax() || $request->wantsJson();

        if ($user) {
            $existing = $post->likes()->where('user_id', $user->id)->first();
            if ($existing) {
                $existing->delete();
                $liked = false;
            } else {
                $post->likes()->create([
                    'user_id' => $user->id,
                    'ip_address' => $request->ip(),
                    'user_agent' => substr((string) $request->userAgent(), 0, 255),
                ]);
                $liked = true;
            }
        } else {
            $existing = $post->likes()
                ->whereNull('user_id')
                ->where('ip_address', $request->ip())
                ->first();

            if ($existing) {
                $existing->delete();
                $liked = false;
            } else {
                $post->likes()->create([
                    'user_id' => null,
                    'ip_address' => $request->ip(),
                    'user_agent' => substr((string) $request->userAgent(), 0, 255),
                ]);
                $liked = true;
            }
        }

        $post->loadCount('likes');

        if ($isAjax) {
            return response()->json([
                'success' => true,
                'liked' => $liked,
                'likes_count' => $post->likes_count,
            ]);
        }

        return back();
    }

    public function updateComment(Request $request, Comment $comment)
    {
        $user = $request->user();
        $isAdmin = $user && in_array($user->role, ['admin', 'moderator']);
        $isOwner = $comment->user_id === $user?->id ||
            (! $comment->user_id && $comment->author_name === $user?->name);

        if (! $isAdmin && ! $isOwner) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['error' => 'Siz bu izohni tahrirlashingiz mumkin emas.'], 403);
            }

            return back()->with('error', 'Siz bu izohni tahrirlashingiz mumkin emas.');
        }

        $request->validate([
            'body' => 'required|string|max:500',
        ]);

        $comment->update([
            'body' => $request->body,
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Izoh tahrirlandi.',
                'comment' => [
                    'id' => $comment->id,
                    'body' => $comment->body,
                ],
            ]);
        }

        return back()->with('success', 'Izoh tahrirlandi.');
    }

    public function destroyComment(Request $request, Comment $comment)
    {
        $user = $request->user();
        $isAdmin = $user && in_array($user->role, ['admin', 'moderator']);
        $isOwner = $comment->user_id === $user?->id ||
            (! $comment->user_id && $comment->author_name === $user?->name);

        if (! $isAdmin && ! $isOwner) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['error' => 'Siz bu izohni o\'chirishingiz mumkin emas.'], 403);
            }

            return back()->with('error', 'Siz bu izohni o\'chirishingiz mumkin emas.');
        }

        $comment->delete();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Izoh o\'chirildi.']);
        }

        return back()->with('success', 'Izoh o\'chirildi.');
    }

    public function replyComment(StoreCommentRequest $request, $commentId)
    {
        $validated = $request->validated();
        $user = $request->user();
        $isAjax = $request->ajax() || $request->wantsJson();

        $parentComment = Comment::find($commentId);

        if (! $parentComment) {
            if ($isAjax) {
                return response()->json(['error' => 'Izoh topilmadi.'], 404);
            }

            return back()->with('error', 'Izoh topilmadi.');
        }

        $reply = Comment::create([
            'post_id' => $parentComment->post_id,
            'user_id' => $user?->id,
            'author_name' => $user?->name ?? ($validated['author_name'] ?? null),
            'body' => $validated['body'],
            'parent_id' => $parentComment->id,
        ]);

        if ($isAjax) {
            return response()->json([
                'success' => true,
                'message' => "Javob qo'shildi.",
                'reply' => [
                    'id' => $reply->id,
                    'author_name' => $reply->author_name ?? 'Mehmon',
                    'body' => $reply->body,
                    'created_at' => $reply->created_at->format('d.m.Y H:i'),
                ],
            ]);
        }

        return back()->with('success', "Javob qo'shildi.");
    }
}
