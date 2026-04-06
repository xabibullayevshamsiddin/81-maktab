<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Comment;
use App\Models\CommentLike;
use App\Models\Post;
use App\Models\PostLike;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class PublicPostController extends Controller
{
    private const COMMENT_BODY_MAX = 100;

    private const REPLY_BODY_MAX = 50;

    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $categoryId = $request->query('category_id');
        $filter = (string) $request->query('filter', 'all');

        $categories = Cache::remember(cache_key_public_post_categories(), now()->addMinutes(10), function () {
            return Category::query()
                ->select(['id', 'name', 'name_en'])
                ->orderBy('name')
                ->get();
        });

        $postsQuery = Post::query()
            ->select([
                'id',
                'category_id',
                'title',
                'title_en',
                'short_content',
                'short_content_en',
                'image',
                'slug',
                'views',
                'post_kind',
                'video_path',
                'video_url',
                'created_at',
            ])
            ->with(['category:id,name,name_en'])
            ->withCount(['comments', 'likes']);

        if ($categoryId !== null && $categoryId !== '' && $categoryId !== 'all') {
            $postsQuery->where('category_id', $categoryId);
        }

        if ($q !== '') {
            $postsQuery->where(function ($sub) use ($q) {
                $sub->where('title', 'like', "%{$q}%")
                    ->orWhere('title_en', 'like', "%{$q}%")
                    ->orWhere('short_content', 'like', "%{$q}%")
                    ->orWhere('short_content_en', 'like', "%{$q}%");
            });
        }

        $allowedFilters = [
            'all', 'video_news', 'social', 'has_video', 'new', 'popular', 'likes', 'comments',
        ];
        if (! in_array($filter, $allowedFilters, true)) {
            $filter = 'all';
        }

        switch ($filter) {
            case 'video_news':
                $postsQuery->where('post_kind', 'video_news');
                $postsQuery->latest();
                break;
            case 'social':
                $postsQuery->where('post_kind', 'social');
                $postsQuery->latest();
                break;
            case 'has_video':
                $postsQuery->where(function ($sub): void {
                    $sub->where(function ($w): void {
                        $w->whereNotNull('video_path')->where('video_path', '!=', '');
                    })->orWhere(function ($w): void {
                        $w->whereNotNull('video_url')->where('video_url', '!=', '');
                    });
                });
                $postsQuery->latest();
                break;
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
                $postsQuery->latest();
                break;
            case 'all':
            default:
                $postsQuery->latest();
                break;
        }

        $posts = $postsQuery->paginate(9)->appends($request->query());

        $likedPostIds = $this->likedPostIdsForUser($posts->pluck('id'));

        $viewData = [
            'posts' => $posts,
            'categories' => $categories,
            'q' => $q,
            'categoryId' => $categoryId,
            'filter' => $filter,
            'likedPostIds' => $likedPostIds,
            'postKindLabels' => config('post_kinds', []),
        ];

        if ($request->expectsJson()) {
            return response()->json([
                'html' => view('posts.partials.list', $viewData)->render(),
            ]);
        }

        return view('post', $viewData);
    }

    public function show(Post $post)
    {
        // Increment views on each open.
        $post->increment('views');

        $post->load('category');
        $post->loadCount(['comments', 'likes']);

        $likedPostIds = $this->likedPostIdsForUser(collect([$post->id]));

        // Load top-level comments and their replies.
        $comments = $post->comments()
            ->whereNull('parent_id')
            ->with([
                'user.roleRelation',
                'replies' => function ($query) {
                    $query->with('user.roleRelation')->withCount('likes')->latest();
                },
            ])
            ->withCount('likes')
            ->latest()
            ->get();

        $likedCommentIds = $this->likedPostCommentIdsForUser($comments);

        return view('posts.show', compact('post', 'likedPostIds', 'comments', 'likedCommentIds'));
    }

    public function storeComment(Request $request, Post $post)
    {
        if ($response = $this->ensureCanInteract($request)) {
            return $response;
        }

        $validated = $this->validateCommentPayload($request, $request->filled('parent_id'));

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
            $comment->load('user.roleRelation');

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
                    'role_key' => $comment->user?->role ?? 'guest',
                    'role_label' => $comment->user?->role_label ?? 'Mehmon',
                    'avatar_url' => $comment->user?->avatar_url,
                    'avatar_initial' => Str::upper(Str::substr(trim((string) ($comment->author_name ?: 'M')), 0, 1)),
                    'likes_count' => 0,
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

        $validated = $this->validateCommentPayload($request, (bool) $comment->parent_id, false);

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

    public function toggleCommentLike(Request $request, Post $post, Comment $comment)
    {
        $this->ensureCommentBelongsToPost($post, $comment);

        if ($response = $this->ensureCanInteract($request)) {
            return $response;
        }

        $existing = CommentLike::query()
            ->where('comment_id', $comment->id)
            ->where('user_id', auth()->id())
            ->first();

        if ($existing) {
            $existing->delete();
            $likesCount = $comment->likes()->count();

            if ($request->wantsJson()) {
                return response()->json([
                    'ok' => true,
                    'message' => 'Like olib tashlandi.',
                    'liked' => false,
                    'likes_count' => $likesCount,
                    'toast_type' => 'warning',
                ]);
            }

            return back()
                ->with('success', 'Like olib tashlandi.')
                ->with('toast_type', 'warning');
        }

        try {
            CommentLike::query()->create([
                'comment_id' => $comment->id,
                'user_id' => auth()->id(),
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

        $likesCount = $comment->likes()->count();

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

    private function likedPostIdsForUser(Collection $postIds): Collection
    {
        if (! auth()->check() || ! auth()->user()?->isActive()) {
            return collect();
        }

        $postIds = $postIds->filter(fn ($id) => $id !== null && $id !== '')->values();
        if ($postIds->isEmpty()) {
            return collect();
        }

        return PostLike::query()
            ->where('user_id', auth()->id())
            ->whereIn('post_id', $postIds)
            ->pluck('post_id');
    }

    private function likedPostCommentIdsForUser(Collection $comments): Collection
    {
        if (! auth()->check()) {
            return collect();
        }

        $ids = $this->flattenPostCommentIds($comments);
        if ($ids->isEmpty()) {
            return collect();
        }

        return CommentLike::query()
            ->where('user_id', auth()->id())
            ->whereIn('comment_id', $ids)
            ->pluck('comment_id');
    }

    private function flattenPostCommentIds(Collection $comments): Collection
    {
        $ids = collect();
        foreach ($comments as $c) {
            $ids->push($c->id);
            if ($c->relationLoaded('replies') && $c->replies->isNotEmpty()) {
                $ids = $ids->merge($this->flattenPostCommentIds($c->replies));
            }
        }

        return $ids;
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

        $comment->loadMissing('user.roleRelation');

        return auth()->user()->canManageCommentAsStaff($comment->user, $comment->user_id);
    }

    private function validateCommentPayload(Request $request, bool $isReply, bool $includeMeta = true): array
    {
        $bodyMax = $isReply ? self::REPLY_BODY_MAX : self::COMMENT_BODY_MAX;

        $rules = [
            'body' => ['required', 'string', 'max:'.$bodyMax],
        ];

        if ($includeMeta) {
            $rules['author_name'] = ['nullable', 'string', 'max:80'];
            $rules['parent_id'] = ['nullable', 'integer', 'exists:comments,id'];
        }

        return $request->validate($rules, [
            'body.max' => $isReply
                ? "Javob matni ".self::REPLY_BODY_MAX." belgidan oshmasin."
                : "Izoh matni ".self::COMMENT_BODY_MAX." belgidan oshmasin.",
        ]);
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
