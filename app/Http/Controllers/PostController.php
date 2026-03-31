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
    public function index()
    {
        $posts = Post::with('category')->latest()->get();

        return view('admin.posts.index', compact('posts'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = Category::orderBy('name')->get();

        return view('admin.posts.create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'short_content' => ['required', 'string'],
            'content' => ['required', 'string'],
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp'],
        ]);

        $validated['image'] = $request->file('image')->store('posts', 'public');
        $validated['slug'] = $this->makeUniqueSlug($validated['title']);

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

        return view('admin.posts.edit', compact('post', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Post $post)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'short_content' => ['required', 'string'],
            'content' => ['required', 'string'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp',],
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

        $post->delete();

        return redirect()->route('posts.index')
            ->with('error', "Post o'chirildi.")
            ->with('toast_type', 'error');
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

