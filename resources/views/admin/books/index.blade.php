@extends('admin.layouts.main')
@section('title', 'Kitoblar')
@section('content')
<section class="table-components">
  <div class="container-fluid">
    <div class="title-wrapper pt-30">
      <div class="row align-items-center">
        <div class="col-md-6"><div class="title"><h2>Kutubxona — Kitoblar</h2></div></div>
        <div class="col-md-6">
          <div class="breadcrumb-wrapper">
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Kitoblar</li>
              </ol>
            </nav>
          </div>
        </div>
      </div>
    </div>

    <div class="tables-wrapper">
      <div class="row">
        <div class="col-lg-12">
          <div class="card-style mb-30">
            <div class="d-flex justify-content-between align-items-center mb-20 flex-wrap gap-2">
              <h6 class="mb-0">Barcha kitoblar ({{ $books->total() }})</h6>
              <div class="d-flex gap-2">
                <a href="{{ route('admin.book-categories.index') }}" class="btn btn-secondary btn-sm">Kategoriyalar</a>
                <a href="{{ route('admin.books.create') }}" class="btn btn-success">+ Kitob qo'shish</a>
              </div>
            </div>

            {{-- Filter --}}
            <form method="GET" action="{{ route('admin.books.index') }}" class="d-flex gap-2 mb-20 flex-wrap">
              <input type="text" name="q" value="{{ $q }}" placeholder="Qidirish..." class="form-control" style="max-width:220px;">
              <select name="category" class="form-control" style="max-width:180px;">
                <option value="">Barcha kategoriya</option>
                @foreach($categories as $cat)
                  <option value="{{ $cat->id }}" {{ $catId == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                @endforeach
              </select>
              <button type="submit" class="btn btn-primary btn-sm">Qidirish</button>
              @if($q || $catId)
                <a href="{{ route('admin.books.index') }}" class="btn btn-secondary btn-sm">Tozalash</a>
              @endif
            </form>

            @if(session('success'))
              <div class="alert-box success-alert mb-20"><div class="alert">{{ session('success') }}</div></div>
            @endif
            @if(session('error'))
              <div class="alert-box danger-alert mb-20"><div class="alert">{{ session('error') }}</div></div>
            @endif

            <div class="table-wrapper table-responsive">
              <table class="table">
                <thead>
                  <tr>
                    <th><h6>#</h6></th>
                    <th><h6>Muqova</h6></th>
                    <th><h6>Nomi</h6></th>
                    <th><h6>Kategoriya</h6></th>
                    <th><h6>Muallif</h6></th>
                    <th><h6>Sinf</h6></th>
                    <th><h6>Hajm</h6></th>
                    <th><h6>Ko'rishlar</h6></th>
                    <th><h6>Holat</h6></th>
                    <th><h6>Amallar</h6></th>
                  </tr>
                </thead>
                <tbody>
                  @forelse($books as $book)
                    <tr>
                      <td><p>{{ ($books->firstItem() ?? 1) + $loop->index }}</p></td>
                      <td>
                        <img src="{{ $book->coverImageUrl() }}" alt="" style="width:48px;height:64px;object-fit:cover;border-radius:6px;">
                      </td>
                      <td class="min-width"><p><strong>{{ $book->title }}</strong></p></td>
                      <td><p>{{ $book->category?->name ?? '—' }}</p></td>
                      <td><p>{{ $book->author ?? '—' }}</p></td>
                      <td><p>{{ $book->grade ?? '—' }}</p></td>
                      <td><p>{{ $book->fileSizeLabel() }}</p></td>
                      <td><p>{{ $book->view_count }} / {{ $book->download_count }}</p></td>
                      <td>
                        @if($book->is_active)
                          <span class="badge bg-success">Faol</span>
                        @else
                          <span class="badge bg-secondary">Nofaol</span>
                        @endif
                      </td>
                      <td class="min-width">
                        <div class="action">
                          <a href="{{ route('books.show', $book) }}" target="_blank" class="text-primary me-2" title="Ko'rish">
                            <i class="lni lni-eye"></i>
                          </a>
                          <a href="{{ route('admin.books.edit', $book) }}" class="text-warning me-2" title="Tahrirlash">
                            <i class="lni lni-pencil-alt"></i>
                          </a>
                          <form action="{{ route('admin.books.destroy', $book) }}" method="POST" style="display:inline;"
                            data-confirm="Kitobni o'chirishni xohlaysizmi?" data-confirm-title="Kitobni o'chirish"
                            data-confirm-variant="danger" data-confirm-ok="O'chirish">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-danger" style="background:none;border:none;padding:0;" title="O'chirish">
                              <i class="lni lni-trash-can"></i>
                            </button>
                          </form>
                        </div>
                      </td>
                    </tr>
                  @empty
                    <tr><td colspan="10"><p>Hozircha kitoblar yo'q.</p></td></tr>
                  @endforelse
                </tbody>
              </table>
            </div>
            @if($books->hasPages())
              <div class="p-3">{{ $books->links() }}</div>
            @endif
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection
