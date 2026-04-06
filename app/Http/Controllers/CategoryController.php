<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        $query = Category::withCount('posts')->latest();

        if ($q !== '') {
            $query->where(function ($w) use ($q): void {
                $w->where('name', 'like', '%'.$q.'%')
                    ->orWhere('name_en', 'like', '%'.$q.'%')
                    ->orWhere('slug', 'like', '%'.$q.'%');
            });
        }

        $categories = $query->paginate(10)->withQueryString();

        return view('admin.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.categories.create');
    }

    public function store(StoreCategoryRequest $request)
    {
        $validated = $request->validated();

        Category::create($validated);
        forget_public_content_caches();

        return redirect()->route('categories.index')->with('success', 'Kategoriya qo\'shildi.');
    }

    public function edit(Category $category)
    {
        return view('admin.categories.edit', compact('category'));
    }

    public function update(UpdateCategoryRequest $request, Category $category)
    {
        $validated = $request->validated();

        $category->update($validated);
        forget_public_content_caches();

        return redirect()->route('categories.index')
            ->with('success', 'Kategoriya yangilandi.')
            ->with('toast_type', 'warning');
    }

    public function destroy(Category $category)
    {
        $category->delete();
        forget_public_content_caches();

        return redirect()->route('categories.index')
            ->with('error', 'Kategoriya o\'chirildi. Bog\'langan postlar kategoriyasiz qoldi.')
            ->with('toast_type', 'error');
    }
}
