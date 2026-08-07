<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\BookCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PublicBookController extends Controller
{
    public function index(Request $request)
    {
        $q       = trim((string) $request->query('q', ''));
        $catId   = $request->query('category');
        $grade   = $request->query('grade');
        $subject = $request->query('subject');
        $year    = $request->query('year');
        $sort    = $request->query('sort', 'newest');

        $query = Book::query()
            ->active()
            ->with('category:id,name,name_en');

        if ($q) {
            $query->where(function ($w) use ($q) {
                $w->where('title', 'like', "%$q%")
                  ->orWhere('title_en', 'like', "%$q%")
                  ->orWhere('author', 'like', "%$q%")
                  ->orWhere('subject', 'like', "%$q%");
            });
        }
        if ($catId)   $query->where('book_category_id', $catId);
        if ($grade)   $query->where('grade', $grade);
        if ($subject) $query->where('subject', $subject);
        if ($year)    $query->where('year', $year);

        match ($sort) {
            'popular'   => $query->orderByDesc('view_count'),
            'downloads' => $query->orderByDesc('download_count'),
            'oldest'    => $query->oldest(),
            default     => $query->latest(),
        };

        $books      = $query->paginate(12)->appends(request()->query());
        $categories = BookCategory::orderBy('sort_order')->orderBy('name')->get();

        // Filter options
        $grades   = Book::active()->whereNotNull('grade')->distinct()->orderBy('grade')->pluck('grade');
        $subjects = Book::active()->whereNotNull('subject')->distinct()->orderBy('subject')->pluck('subject');
        $years    = Book::active()->whereNotNull('year')->distinct()->orderByDesc('year')->pluck('year');

        return view('books.index', compact(
            'books', 'categories', 'grades', 'subjects', 'years',
            'q', 'catId', 'grade', 'subject', 'year', 'sort'
        ));
    }

    public function show(Book $book)
    {
        abort_unless($book->is_active, 404);
        $book->incrementView();
        return view('books.show', compact('book'));
    }

    public function download(Book $book)
    {
        abort_unless($book->is_active && $book->allow_download, 403);
        $disk = Storage::disk('public');
        abort_unless($disk->exists($book->file_path), 404);

        $book->incrementDownload();

        $filename = \Illuminate\Support\Str::slug($book->title) . '.pdf';

        return $disk->download($book->file_path, $filename);
    }

    public function stream(Book $book)
    {
        abort_unless($book->is_active, 404);
        $disk = Storage::disk('public');
        abort_unless($disk->exists($book->file_path), 404);

        // incrementView() faqat show() da chaqiriladi — stream() har byte-range
        // so'rovida chaqirilgani uchun bu yerda olib tashlandi.

        $path = $disk->path($book->file_path);

        return response()->file($path, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . \Illuminate\Support\Str::slug($book->title) . '.pdf"',
        ]);
    }
}
