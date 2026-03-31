<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Comment;
use App\Models\Post;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

class PublicPostController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $categoryId = $request->query('category_id');
        $sort = (string) $request->query('sort', 'new');

        $categories = Category::orderBy('name')->get();

        $postsQuery = Post::query()
            ->with('category')
            ->withCount(['comments', 'likes']);

        if ($categoryId !== null && $categoryId !== '' && $categoryId !== 'all') {
            $postsQuery->where('category_id', $categoryId);
        }

        if ($q !== '') {
            $postsQuery->where(function ($sub) use ($q) {
                $sub->where('title', 'like', "%{$q}%")
                    ->orWhere('short_content', 'like', "%{$q}%");
            });
        }

        // Sorting
        switch ($sort) {
            case 'popular':
                $postsQuery->orderByDesc('views');
                break;
            case 'likes':
                $postsQuery->orderByDesc('likes_count');
                break;
            case 'comments':
                $postsQuery->orderByDesc('comments_count');
                break;
            case 'new':
            default:
                $postsQuery->latest();
                break;
        }

        $posts = $postsQuery->paginate(10)->appends($request->query());

        return view('post', [
            'posts' => $posts,
            'categories' => $categories,
            'q' => $q,
            'categoryId' => $categoryId,
            'sort' => $sort,
        ]);
    }

    public function show(Post $post)
    {
        // Increment views on each open.
        $post->increment('views');

        $post->load('category');
        $post->loadCount(['comments', 'likes']);

        $liked = false;
        if (auth()->check() && auth()->user()?->isActive()) {
            $liked = $post->likes()
                ->where('user_id', auth()->id())
                ->exists();
        }

        // Load top-level comments and their replies.
        $comments = $post->comments()
            ->whereNull('parent_id')
            ->with(['replies' => function ($query) {
                $query->latest();
            }])
            ->latest()
            ->get();

        return view('posts.show', compact('post', 'liked', 'comments'));
    }

    public function storeComment(Request $request, Post $post)
    {
        if ($response = $this->ensureCanInteract($request)) {
            return $response;
        }

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:500'],
            'author_name' => ['nullable', 'string', 'max:80'],
            'parent_id' => ['nullable', 'integer', 'exists:comments,id'],
        ]);

        $parentComment = null;
        if (! empty($validated['parent_id'])) {
            $parentComment = $post->comments()
                ->whereKey($validated['parent_id'])
                ->firstOrFail();
        }

        $comment = new Comment();
        $comment->post_id = $post->id;
        $comment->body = $validated['body'];
        $comment->author_name = $request->user()?->name ?? ($validated['author_name'] ?? null);
        $comment->user_id = $request->user()?->id;

        // Show comments immediately.
        $comment->is_approved = true;
        $comment->parent_id = $parentComment?->id;
        $comment->save();

        if ($request->wantsJson()) {
            $comment->refresh();

            return response()->json([
                'ok' => true,
                'message' => "Izoh qo'shildi.",
                'toast_type' => 'success',
                'comment' => [
                    'id' => $comment->id,
                    'author_name' => $comment->author_name ?? 'Mehmon',
                    'body' => $comment->body,
                    'created_at' => $comment->created_at?->format('d.m.Y H:i'),
                    'parent_id' => $comment->parent_id,
                    'user_id' => $comment->user_id,
                ],
            ]);
        }

        return back()
            ->with('success', "Izoh qo'shildi.")
            ->with('toast_type', 'success');
    }

    public function updateComment(Request $request, Post $post, Comment $comment)
    {
        $this->ensureCommentBelongsToPost($post, $comment);

        if (! $this->canManageComment($comment)) {
            abort(403);
        }

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:500'],
        ]);

        $comment->update([
            'body' => $validated['body'],
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'ok' => true,
                'message' => 'Izoh yangilandi.',
                'toast_type' => 'warning',
                'comment' => [
                    'id' => $comment->id,
                    'body' => $comment->body,
                ],
            ]);
        }

        return back()
            ->with('success', 'Izoh yangilandi.')
            ->with('toast_type', 'warning');
    }

    public function destroyComment(Request $request, Post $post, Comment $comment)
    {
        $this->ensureCommentBelongsToPost($post, $comment);

        if (! $this->canManageComment($comment)) {
            abort(403);
        }

        $comment->delete();

        if ($request->wantsJson()) {
            return response()->json([
                'ok' => true,
                'message' => "Izoh o'chirildi.",
                'toast_type' => 'error',
            ]);
        }

        return back()
            ->with('success', "Izoh o'chirildi.")
            ->with('toast_type', 'error');
    }

    private function ensureCommentBelongsToPost(Post $post, Comment $comment): void
    {
        if ((int) $comment->post_id !== (int) $post->id) {
            abort(404);
        }
    }

    private function canManageComment(Comment $comment): bool
    {
        if (! auth()->check()) {
            return false;
        }

        $user = auth()->user();

        if ((int) $comment->user_id === (int) $user->id) {
            return true;
        }

        return $user->isAdmin() || $user->isEditor() || $user->isModerator();
    }

    public function toggleLike(Request $request, Post $post)
    {
        if ($response = $this->ensureCanInteract($request)) {
            return $response;
        }

        $existing = $post->likes()
            ->where('user_id', auth()->id())
            ->first();

        if ($existing) {
            $existing->delete();
            $likesCount = $post->likes()->count();

            if ($request->wantsJson()) {
                return response()->json([
                    'ok' => true,
                    'message' => "Like olib tashlandi.",
                    'liked' => false,
                    'likes_count' => $likesCount,
                    'toast_type' => 'warning',
                ]);
            }

            return back()
                ->with('success', "Like olib tashlandi.")
                ->with('toast_type', 'warning');
        }

        try {
            $post->likes()->create([
                'user_id' => auth()->id(),
                'ip_address' => null,
                'user_agent' => substr((string) $request->userAgent(), 0, 255),
            ]);
        } catch (QueryException $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'ok' => false,
                    'message' => "Like qo'shishda xatolik. Qayta urinib ko'ring.",
                    'toast_type' => 'warning',
                ], 422);
            }

            return back()
                ->with('error', "Like qo'shishda xatolik. Qayta urinib ko'ring.")
                ->with('toast_type', 'warning');
        }

        $likesCount = $post->likes()->count();

        if ($request->wantsJson()) {
            return response()->json([
                'ok' => true,
                'message' => "Like qo'shildi.",
                'liked' => true,
                'likes_count' => $likesCount,
                'toast_type' => 'success',
            ]);
        }

        return back()
            ->with('success', "Like qo'shildi.")
            ->with('toast_type', 'success');
    }

    private function ensureCanInteract(Request $request)
    {
        if (! auth()->check()) {
            return $this->denyInteraction(
                $request,
                "Like bosish va izoh yozish uchun avval ro'yxatdan o'ting.",
                401
            );
        }

        if (! $request->user()?->isActive()) {
            return $this->denyInteraction(
                $request,
                "Siz block qilingansiz. Like bosish va izoh yozish mumkin emas.",
                403
            );
        }

        return null;
    }

    private function denyInteraction(Request $request, string $message, int $statusCode)
    {
        if ($request->wantsJson()) {
            return response()->json([
                'ok' => false,
                'message' => $message,
                'toast_type' => 'warning',
            ], $statusCode);
        }

        return back()
            ->with('error', $message)
            ->with('toast_type', 'warning');
    }
}
