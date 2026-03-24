@extends('admin.layouts.main')

@section('title', "Post: $post->title")

@section('content')
<section class="table-components">
  <div class="container-fluid">
    <div class="title-wrapper pt-30">
      <div class="row align-items-center">
        <div class="col-md-6">
          <div class="title">
            <h2>Postni ko'rish</h2>
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
                  Ko'rish
                </li>
              </ol>
            </nav>
          </div>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-lg-8">
        <div class="card-style mb-30">
          <h6 class="mb-20">{{ $post->title }}</h6>

          @if ($post->image)
            <div class="mb-20">
              <img src="{{ asset('storage/' . $post->image) }}" alt="{{ $post->title }}" style="max-width:320px;border-radius:10px;">
            </div>
          @endif

          <p class="mb-15"><strong>Kategoriya:</strong> {{ $post->category?->name ?? "Tanlanmagan" }}</p>
          <p class="mb-15"><strong>Qisqacha:</strong> {{ $post->short_content }}</p>
          <p class="mb-15"><strong>To'liq:</strong> {{ $post->content }}</p>
          <p class="mb-20"><strong>Yaratilgan:</strong> {{ $post->created_at?->format('Y-m-d H:i') }}</p>

          <a href="{{ route('posts.edit', $post->id) }}" class="main-btn warning-btn btn-hover">Tahrirlash</a>
          <a href="{{ route('posts.index') }}" class="main-btn light-btn btn-hover">Orqaga</a>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection
