@extends('admin.layouts.main')

@section('title', 'Ustoz')

@section('content')
<section class="tab-components">
  <div class="container-fluid">
    <div class="title-wrapper pt-30">
      <div class="row align-items-center">
        <div class="col-md-6"><div class="title"><h2>{{ $teacher->full_name }}</h2></div></div>
      </div>
    </div>

    <div class="card-style mb-30">
      <div class="row g-3 align-items-start">
        <div class="col-md-4">
          <img
            src="{{ $teacher->image ? asset('storage/' . $teacher->image) : asset('temp/img/how-to-be-teacher-malaysia-feature.png') }}"
            alt="{{ $teacher->full_name }}"
            style="width:100%;max-width:360px;aspect-ratio:3/2;object-fit:cover;border-radius:14px;"
          >
        </div>
        <div class="col-md-8">
          <p><strong>Fan:</strong> {{ $teacher->subject }}</p>
          <p><strong>Tajriba:</strong> {{ $teacher->experience_years }} yil</p>
          <p><strong>Sinflar:</strong> {{ $teacher->grades ?: '-' }}</p>
          <p><strong>Ro‘yxat tartibi (sort_order):</strong> {{ $teacher->sort_order }} <span class="text-muted" style="font-size:12px;">— ustozlar sahifasida ketma-kelik</span></p>
          <p><strong>Status:</strong> {{ $teacher->is_active ? 'Faol' : 'Nofaol' }}</p>
          <p><strong>Slug:</strong> {{ $teacher->slug }}</p>
          <hr>
          @if(filled($teacher->achievements))
            <p><strong>Yutuqlar:</strong></p>
            <pre style="white-space:pre-wrap;font-size:14px;">{{ $teacher->achievements }}</pre>
            <hr>
          @endif
          <p>{{ $teacher->bio ?: 'Bio kiritilmagan.' }}</p>
          <a href="{{ route('teachers.edit', $teacher) }}" class="main-btn warning-btn btn-hover btn-sm">Tahrirlash</a>
          <a href="{{ route('teachers.index') }}" class="main-btn light-btn btn-hover btn-sm">Orqaga</a>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection

