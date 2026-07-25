<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\BookCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminBookController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $catId = $request->query('category');

        $query = Book::query()
            ->with('category')
            ->latest();

        if ($q) {
            $query->where(function ($w) use ($q) {
                $w->where('title', 'like', "%$q%")
                  ->orWhere('author', 'like', "%$q%")
                  ->orWhere('subject', 'like', "%$q%");
            });
        }
        if ($catId) $query->where('book_category_id', $catId);

        $books = $query->paginate(20)->withQueryString();
        $categories = BookCategory::orderBy('sort_order')->orderBy('name')->get();

        return view('admin.books.index', compact('books', 'categories', 'q', 'catId'));
    }

    public function create()
    {
        $categories = BookCategory::orderBy('sort_order')->orderBy('name')->get();
        return view('admin.books.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'            => ['required', 'string', 'max:255'],
            'title_en'         => ['nullable', 'string', 'max:255'],
            'description'      => ['nullable', 'string'],
            'description_en'   => ['nullable', 'string'],
            'author'           => ['nullable', 'string', 'max:255'],
            'subject'          => ['nullable', 'string', 'max:255'],
            'year'             => ['nullable', 'integer', 'min:1900', 'max:2100'],
            'grade'            => ['nullable', 'string', 'max:50'],
            'book_category_id' => ['nullable', 'exists:book_categories,id'],
            'is_active'        => ['boolean'],
            'allow_download'   => ['boolean'],
            'pdf_file'         => ['required', 'file', 'mimes:pdf', 'max:51200'],
            'cover_image'      => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $file = $request->file('pdf_file');
        $path = $file->store('books', 'public');
        $validated['file_path'] = $path;
        $validated['file_size'] = $file->getSize();

        if ($request->hasFile('cover_image')) {
            $validated['cover_image'] = $request->file('cover_image')->store('books/covers', 'public');
        }

        $validated['is_active']      = $request->boolean('is_active', true);
        $validated['allow_download'] = $request->boolean('allow_download', true);
        unset($validated['pdf_file']);

        Book::create($validated);

        return redirect()->route('admin.books.index')->with('success', "Kitob qo'shildi.");
    }

    public function edit(Book $book)
    {
        $categories = BookCategory::orderBy('sort_order')->orderBy('name')->get();
        return view('admin.books.edit', compact('book', 'categories'));
    }

    public function update(Request $request, Book $book)
    {
        $validated = $request->validate([
            'title'            => ['required', 'string', 'max:255'],
            'title_en'         => ['nullable', 'string', 'max:255'],
            'description'      => ['nullable', 'string'],
            'description_en'   => ['nullable', 'string'],
            'author'           => ['nullable', 'string', 'max:255'],
            'subject'          => ['nullable', 'string', 'max:255'],
            'year'             => ['nullable', 'integer', 'min:1900', 'max:2100'],
            'grade'            => ['nullable', 'string', 'max:50'],
            'book_category_id' => ['nullable', 'exists:book_categories,id'],
            'is_active'        => ['boolean'],
            'allow_download'   => ['boolean'],
            'pdf_file'         => ['nullable', 'file', 'mimes:pdf', 'max:51200'],
            'cover_image'      => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        if ($request->hasFile('pdf_file')) {
            Storage::disk('public')->delete($book->file_path);
            $file = $request->file('pdf_file');
            $validated['file_path'] = $file->store('books', 'public');
            $validated['file_size'] = $file->getSize();
        }

        if ($request->hasFile('cover_image')) {
            if ($book->cover_image) Storage::disk('public')->delete($book->cover_image);
            $validated['cover_image'] = $request->file('cover_image')->store('books/covers', 'public');
        }

        $validated['is_active']      = $request->boolean('is_active', true);
        $validated['allow_download'] = $request->boolean('allow_download', true);
        unset($validated['pdf_file']);

        $book->update($validated);

        return redirect()->route('admin.books.index')->with('success', 'Kitob yangilandi.');
    }

    public function destroy(Book $book)
    {
        Storage::disk('public')->delete($book->file_path);
        if ($book->cover_image) Storage::disk('public')->delete($book->cover_image);
        $book->delete();

        return redirect()->route('admin.books.index')
            ->with('error', "Kitob o'chirildi.")
            ->with('toast_type', 'error');
    }

    // Kategoriyalar CRUD
    public function categoriesIndex()
    {
        $categories = BookCategory::withCount('books')->orderBy('sort_order')->orderBy('name')->get();
        return view('admin.books.categories', compact('categories'));
    }

    public function categoriesStore(Request $request)
    {
        $validated = $request->validate([
            'name'       => ['required', 'string', 'max:100'],
            'name_en'    => ['nullable', 'string', 'max:100'],
            'sort_order' => ['integer'],
        ]);
        BookCategory::create($validated);
        return back()->with('success', "Kategoriya qo'shildi.");
    }

    public function categoriesDestroy(BookCategory $bookCategory)
    {
        $bookCategory->delete();
        return back()->with('error', "Kategoriya o'chirildi.")->with('toast_type', 'error');
    }
}
