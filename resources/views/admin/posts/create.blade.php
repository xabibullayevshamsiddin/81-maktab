@extends('admin.layouts.main')

@section('title', 'Post yaratish')

@section('content')
<section class="table-components">
  <div class="container-fluid">
    <div class="title-wrapper pt-30">
      <div class="row align-items-center">
        <div class="col-md-6">
          <div class="title">
            <h2>Post yaratish</h2>
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
                  Yangi post
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
                <ul class="mb-0 ps-3">
                  @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                  @endforeach
                </ul>
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

          <form action="{{ route('posts.store') }}" method="POST" enctype="multipart/form-data" id="post-create-form">
            @csrf

            <div class="mb-3">
              <label class="form-label">Nomi</label>
              <input type="text" class="form-control" name="title" value="{{ old('title') }}" required>
            </div>

            <div class="mb-3">
              <label class="form-label">Nomi (EN, ixtiyoriy)</label>
              <input type="text" class="form-control" name="title_en" value="{{ old('title_en') }}">
            </div>

            <div class="mb-3">
              <label class="form-label">Kategoriya</label>
              <select class="form-control" name="category_id" required>
                <option value="">Kategoriyani tanlang</option>
                @foreach ($categories as $category)
                  <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                    {{ $category->name }}
                  </option>
                @endforeach
              </select>
              
            </div>

            <div class="mb-3">
              <label class="form-label">Yangilik turi</label>
                <select class="form-control" name="post_kind" required>
                  @foreach ($postKinds as $key => $meta)
                  <option value="{{ $key }}" {{ old('post_kind', 'general') === $key ? 'selected' : '' }}>
                    {{ localized_post_kind_label($key, 'uz') }}
                  </option>
                @endforeach
              </select>
            </div>

            <div class="mb-3">
              <label class="form-label">Qisqacha tavsif</label>
              <textarea class="form-control" name="short_content" required>{{ old('short_content') }}</textarea>
            </div>

            <div class="mb-3">
              <label class="form-label">Qisqacha tavsif (EN, ixtiyoriy)</label>
              <textarea class="form-control" name="short_content_en">{{ old('short_content_en') }}</textarea>
            </div>

            <div class="mb-3">
              <label class="form-label">To'liq tavsif</label>
              <textarea class="form-control" name="content" rows="8" required>{{ old('content') }}</textarea>
            </div>

            <div class="mb-3">
              <label class="form-label">To'liq tavsif (EN, ixtiyoriy)</label>
              <textarea class="form-control" name="content_en" rows="8">{{ old('content_en') }}</textarea>
            </div>

            <div class="mb-3">
              <label class="form-label">Rasm</label>
              <input type="file" class="form-control" name="image" accept="image/*" required>
            </div>

            <div class="mb-3">
              <label class="form-label">Video fayl (kompyuterdan, ixtiyoriy)</label>
              <input type="file" class="form-control" name="video_file" accept="video/mp4,video/webm">
              <small class="text-muted">MP4 yoki WebM. Sayt kodida video hajmi cheklanmaydi; amalda cheklov faqat PHP (php.ini / OSPanel) va veb-serverdan. Rasm kartochkada ustun; batafsil sahifada video ko‘rinadi.</small>
            </div>

            <div class="mb-3">
              <label class="form-label">Video havolasi (YouTube / Instagram, ixtiyoriy)</label>
              <input type="text" class="form-control" name="video_url" value="{{ old('video_url') }}" placeholder="https://www.youtube.com/watch?v=... yoki https://www.instagram.com/reel/...">
              <small class="text-muted">YouTube, Instagram (post/reel) havolasi qo‘llanadi. Agar video fayl ham yuklansa, avval fayl ijro etiladi.</small>
            </div>

            <button type="submit" class="btn btn-success">Qo'shish</button>
            <a href="{{ route('posts.index') }}" class="btn btn-danger">Bekor qilish</a>
          </form>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection
