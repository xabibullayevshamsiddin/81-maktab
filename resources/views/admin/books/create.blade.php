@extends('admin.layouts.main')
@section('title', isset($book) ? 'Kitobni tahrirlash' : 'Kitob qo\'shish')
@section('content')
<section class="table-components">
  <div class="container-fluid">
    <div class="title-wrapper pt-30">
      <div class="row align-items-center">
        <div class="col-md-6">
          <div class="title"><h2>{{ isset($book) ? 'Kitobni tahrirlash' : "Kitob qo'shish" }}</h2></div>
        </div>
        <div class="col-md-6">
          <div class="breadcrumb-wrapper">
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.books.index') }}">Kitoblar</a></li>
                <li class="breadcrumb-item active">{{ isset($book) ? 'Tahrirlash' : "Qo'shish" }}</li>
              </ol>
            </nav>
          </div>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-lg-8">
        <div class="card-style mb-30">
          <form action="{{ isset($book) ? route('admin.books.update', $book) : route('admin.books.store') }}"
                method="POST" enctype="multipart/form-data">
            @csrf
            @if(isset($book)) @method('PUT') @endif

            @if($errors->any())
              <div class="alert-box danger-alert mb-20">
                <div class="alert">{{ $errors->first() }}</div>
              </div>
            @endif

            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label">Nomi (UZ) *</label>
                <input type="text" name="title" class="form-control" value="{{ old('title', $book->title ?? '') }}" required maxlength="255">
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label">Nomi (EN)</label>
                <input type="text" name="title_en" class="form-control" value="{{ old('title_en', $book->title_en ?? '') }}" maxlength="255">
              </div>
            </div>

            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label">Muallif</label>
                <input type="text" name="author" class="form-control" value="{{ old('author', $book->author ?? '') }}" maxlength="255">
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label">Fan nomi</label>
                <input type="text" name="subject" class="form-control" value="{{ old('subject', $book->subject ?? '') }}" maxlength="255" placeholder="Masalan: Matematika">
              </div>
            </div>

            <div class="row">
              <div class="col-md-4 mb-3">
                <label class="form-label">Kategoriya</label>
                <select name="book_category_id" class="form-control">
                  <option value="">— Tanlang —</option>
                  @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ old('book_category_id', $book->book_category_id ?? '') == $cat->id ? 'selected' : '' }}>
                      {{ $cat->name }}
                    </option>
                  @endforeach
                </select>
              </div>
              <div class="col-md-4 mb-3">
                <label class="form-label">Sinf</label>
                <input type="text" name="grade" class="form-control" value="{{ old('grade', $book->grade ?? '') }}" maxlength="50" placeholder="Masalan: 9 yoki Barcha">
              </div>
              <div class="col-md-4 mb-3">
                <label class="form-label">Nashr yili</label>
                <input type="number" name="year" class="form-control" value="{{ old('year', $book->year ?? '') }}" min="1900" max="2100">
              </div>
            </div>

            <div class="mb-3">
              <label class="form-label">Tavsif (UZ)</label>
              <textarea name="description" class="form-control" rows="3">{{ old('description', $book->description ?? '') }}</textarea>
            </div>
            <div class="mb-3">
              <label class="form-label">Tavsif (EN)</label>
              <textarea name="description_en" class="form-control" rows="3">{{ old('description_en', $book->description_en ?? '') }}</textarea>
            </div>

            <div class="mb-3">
              <label class="form-label">PDF fayl {{ isset($book) ? '(yangilash uchun tanlang)' : '*' }}</label>
              <input type="file" name="pdf_file" class="form-control" accept=".pdf" {{ isset($book) ? '' : 'required' }}>
              @if(isset($book))
                <small class="text-muted">Hozirgi: {{ basename($book->file_path) }} ({{ $book->fileSizeLabel() }})</small>
              @endif
            </div>

            <div class="mb-3">
              <label class="form-label">Muqova rasmi (JPG/PNG/WebP, max 2MB)</label>
              <input type="file" name="cover_image" class="form-control" accept="image/*">
              @if(isset($book) && $book->cover_image)
                <div class="mt-2">
                  <img src="{{ $book->coverImageUrl() }}" alt="" style="height:80px;border-radius:6px;">
                </div>
              @endif
            </div>

            <div class="row mb-3">
              <div class="col-md-6">
                <div class="form-check">
                  <input type="hidden" name="is_active" value="0">
                  <input type="checkbox" name="is_active" value="1" class="form-check-input" id="is_active"
                    {{ old('is_active', $book->is_active ?? true) ? 'checked' : '' }}>
                  <label class="form-check-label" for="is_active">Faol (saytda ko'rinsin)</label>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-check">
                  <input type="hidden" name="allow_download" value="0">
                  <input type="checkbox" name="allow_download" value="1" class="form-check-input" id="allow_download"
                    {{ old('allow_download', $book->allow_download ?? true) ? 'checked' : '' }}>
                  <label class="form-check-label" for="allow_download">Yuklab olishga ruxsat</label>
                </div>
              </div>
            </div>

            <div class="d-flex gap-2">
              <button type="submit" class="btn btn-success">
                {{ isset($book) ? 'Saqlash' : "Qo'shish" }}
              </button>
              <a href="{{ route('admin.books.index') }}" class="btn btn-secondary">Bekor qilish</a>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection
