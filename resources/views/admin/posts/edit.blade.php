@extends('admin.layouts.main')

@section('title', 'Post tahrirlash')

@section('content')
<section class="table-components">
  <div class="container-fluid">
    <div class="title-wrapper pt-30">
      <div class="row align-items-center">
        <div class="col-md-6">
          <div class="title">
            <h2>Post tahrirlash</h2>
          </div>
        </div>
        <div class="col-md-6">
          <div class="breadcrumb-wrapper">
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb">
                <li class="breadcrumb-item">
                  <a href="{{ route('posts.index') }}">Postlar</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">
                  Tahrirlash
                </li>
              </ol>
            </nav>
          </div>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-lg-12">
        <div class="card-style mb-30">
          @if ($errors->any())
            <div class="alert-box danger-alert mb-20">
              <div class="alert">
                {{ $errors->first() }}
              </div>
            </div>
          @endif

          @if ($categories->isEmpty())
            <div class="alert-box warning-alert mb-20">
              <div class="alert">

                <a href="{{ route('categories.create') }}">Kategoriya yaratish</a>
              </div>
            </div>
          @endif

          <form action="{{ route('posts.update', $post->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="mb-3">
              <label class="form-label">Nomi</label>
              <input type="text" class="form-control" name="title" value="{{ old('title', $post->title) }}" required>
            </div>

            <div class="mb-3">
              <label class="form-label">Kategoriya</label>
              <select class="form-control" name="category_id" required>
                <option value="">Kategoriyani tanlang</option>
                @foreach ($categories as $category)
                  <option value="{{ $category->id }}" {{ old('category_id', $post->category_id) == $category->id ? 'selected' : '' }}>
                    {{ $category->name }}
                  </option>
                @endforeach
              </select>
            </div>

            <div class="mb-3">
              <label class="form-label">Qisqacha tavsif</label>
              <textarea class="form-control" name="short_content" rows="3" required>{{ old('short_content', $post->short_content) }}</textarea>
            </div>

            <div class="mb-3">
              <label class="form-label">To'liq tavsif</label>
              <input type="text" class="form-control" name="content" value="{{ old('content', $post->content) }}" required>
            </div>

            @if ($post->image)
              <div class="mb-3">
                <label class="form-label d-block">Joriy rasm</label>
                <img src="{{ asset('storage/' . $post->image) }}" alt="{{ $post->title }}" style="width:120px;height:120px;object-fit:cover;border-radius:8px;">
              </div>
            @endif

            <div class="mb-3">
              <label class="form-label">Yangi rasm (ixtiyoriy)</label>
              <input type="file" class="form-control" name="image" accept="image/*">

            </div>

            <button type="submit" class="btn btn-primary">Saqlash</button>
            <a href="{{ route('posts.index') }}" class="btn btn-danger">Bekor qilish</a>
          </form>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection
