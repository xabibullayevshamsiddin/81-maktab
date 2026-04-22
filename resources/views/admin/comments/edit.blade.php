@extends('admin.layouts.main')

@section('title', 'Izohni tahrirlash')

@section('content')
<section class="table-components">
  <div class="container-fluid">
    <div class="title-wrapper pt-30">
      <div class="row align-items-center">
        <div class="col-md-6">
          <div class="title"><h2>Izohni tahrirlash</h2></div>
        </div>
        <div class="col-md-6 text-end">
          <a href="{{ route('admin.comments.index', ['type' => $type]) }}" class="btn btn-outline-secondary btn-sm">Orqaga</a>
        </div>
      </div>
    </div>

    <div class="card-style mb-30" style="max-width: 720px;">
      @if($type === 'post' && $comment->post)
        <p class="mb-3"><strong>Post:</strong> <a href="{{ route('post.show', $comment->post->slug) }}" target="_blank">{{ $comment->post->title }}</a></p>
      @elseif($type === 'teacher')
        <p class="mb-3"><strong>Manba:</strong> Ustozlar sahifasi</p>
      @endif

      <form action="{{ route('admin.comments.update', ['type' => $type, 'id' => $comment->id]) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-3">
          <label class="form-label">Matn</label>
          <textarea name="body" class="form-control" rows="8" required maxlength="2000">{{ old('body', $comment->body) }}</textarea>
          @error('body')
            <div class="text-danger small">{{ $message }}</div>
          @enderror
        </div>
        <button type="submit" class="btn btn-primary">Saqlash</button>
      </form>
    </div>
  </div>
</section>
@endsection
