<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Post;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        $query = Post::with('category')->latest();

        if ($q !== '') {
            $query->where(function ($w) use ($q): void {
                $w->where('title', 'like', '%'.$q.'%')
                    ->orWhere('slug', 'like', '%'.$q.'%')
                    ->orWhere('short_content', 'like', '%'.$q.'%');
            });
        }

        $posts = $query->get();

        return view('admin.posts.index', compact('posts'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = Category::orderBy('name')->get();
        $postKinds = config('post_kinds', []);

        return view('admin.posts.create', compact('categories', 'postKinds'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $postKindKeys = array_keys(config('post_kinds', []));

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'post_kind' => ['required', 'in:'.implode(',', $postKindKeys ?: ['general', 'video_news', 'social'])],
            'short_content' => ['required', 'string'],
            'content' => ['required', 'string'],
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp'],
            'video_url' => ['nullable', 'string', 'max:500'],
            'video_file' => array_merge(
                ['nullable', 'file'],
                $this->videoUploadRules()
            ),
        ]);

        $validated['image'] = $request->file('image')->store('posts', 'public');
        $validated['slug'] = $this->makeUniqueSlug($validated['title']);
        $validated['video_url'] = $this->normalizeVideoUrl($request->input('video_url'));
        unset($validated['video_file']);

        $validated['video_path'] = null;
        if ($request->hasFile('video_file')) {
            $validated['video_path'] = $request->file('video_file')->store('posts/videos', 'public');
        }

        Post::create($validated);

        return redirect()->route('posts.index')->with('success', "Post qo'shildi.");

    }

    /**
     * Display the specified resource.
     */
    public function show(Post $post)
    {
        return view('admin.posts.show', compact('post'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Post $post)
    {
        $categories = Category::orderBy('name')->get();
        $postKinds = config('post_kinds', []);

        return view('admin.posts.edit', compact('post', 'categories', 'postKinds'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Post $post)
    {
        $postKindKeys = array_keys(config('post_kinds', []));

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'post_kind' => ['required', 'in:'.implode(',', $postKindKeys ?: ['general', 'video_news', 'social'])],
            'short_content' => ['required', 'string'],
            'content' => ['required', 'string'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp',],
            'video_url' => ['nullable', 'string', 'max:500'],
            'video_file' => array_merge(
                ['nullable', 'file'],
                $this->videoUploadRules()
            ),
        ]);

        if ($request->hasFile('image')) {
            if (!empty($post->image)) {
                Storage::disk('public')->delete($post->image);
            }

            $validated['image'] = $request->file('image')->store('posts', 'public');
        }

        if ($post->title !== $validated['title']) {
            $validated['slug'] = $this->makeUniqueSlug($validated['title'], $post->id);
        }

        $validated['video_url'] = $this->normalizeVideoUrl($request->input('video_url'));
        unset($validated['video_file']);

        if ($request->boolean('remove_video_file')) {
            if (! empty($post->video_path)) {
                Storage::disk('public')->delete($post->video_path);
            }
            $validated['video_path'] = null;
        }

        if ($request->hasFile('video_file')) {
            if (! empty($post->video_path)) {
                Storage::disk('public')->delete($post->video_path);
            }
            $validated['video_path'] = $request->file('video_file')->store('posts/videos', 'public');
        }

        $post->update($validated);

        return redirect()->route('posts.index')
            ->with('success', 'Post yangilandi.')
            ->with('toast_type', 'warning');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $post)
    {
        if (!empty($post->image)) {
            Storage::disk('public')->delete($post->image);
        }
        if (! empty($post->video_path)) {
            Storage::disk('public')->delete($post->video_path);
        }

        $post->delete();

        return redirect()->route('posts.index')
            ->with('error', "Post o'chirildi.")
            ->with('toast_type', 'error');
    }

    private function normalizeVideoUrl(?string $url): ?string
    {
        if ($url === null) {
            return null;
        }

        $t = trim($url);

        if ($t === '') {
            return null;
        }

        if (! preg_match('#^https?://#i', $t)) {
            $t = 'https://'.ltrim($t, '/');
        }

        return $t;
    }

    /**
     * @return array<int, \Closure|string>
     */
    private function videoUploadRules(): array
    {
        return [
            function (string $attribute, mixed $value, \Closure $fail): void {
                if (! $value instanceof \Illuminate\Http\UploadedFile) {
                    return;
                }

                if (! $value->isValid()) {
                    $fail('Video fayl yuklanmadi yoki hajmi server limitidan oshib ketgan (PHP post_max_size / upload_max_filesize).');

                    return;
                }

                $ext = strtolower($value->getClientOriginalExtension());
                if (! in_array($ext, ['mp4', 'webm'], true)) {
                    $fail('Video faqat MP4 yoki WebM bo‘lishi kerak.');
                }
            },
        ];
    }

    private function makeUniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title);
        $slug = $base !== '' ? $base : 'post';

        $existsQuery = Post::query()->where('slug', $slug);
        if ($ignoreId) {
            $existsQuery->where('id', '!=', $ignoreId);
        }

        if (! $existsQuery->exists()) {
            return $slug;
        }

        $i = 2;
        while (true) {
            $candidate = "{$slug}-{$i}";
            $q = Post::query()->where('slug', $candidate);
            if ($ignoreId) {
                $q->where('id', '!=', $ignoreId);
            }

            if (! $q->exists()) {
                return $candidate;
            }
            $i++;
        }
    }
}

