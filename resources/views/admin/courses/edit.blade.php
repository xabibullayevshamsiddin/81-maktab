@extends('admin.layouts.main')

@section('title', 'Kursni tahrirlash')

@section('content')
<section class="table-components">
  <div class="container-fluid">
    <div class="title-wrapper pt-30">
      <div class="row align-items-center">
        <div class="col-md-8">
          <div class="title"><h2>Kursni tahrirlash</h2></div>
          <p class="text-sm text-muted mb-0">{{ $course->title }}</p>
        </div>
        <div class="col-md-4 text-end">
          <a href="{{ route('admin.courses.index') }}" class="btn btn-outline btn-sm">Orqaga</a>
        </div>
      </div>
    </div>

    <div class="card-style mb-30">
      <div class="card-body">
        <form action="{{ route('admin.courses.update', $course) }}" method="POST" enctype="multipart/form-data">
          @csrf
          @method('PUT')

          <div class="mb-3">
            <label class="form-label">Ustoz *</label>
            <select name="teacher_id" class="form-select" required>
              @foreach($teachers as $teacher)
                <option value="{{ $teacher->id }}" {{ (int) old('teacher_id', $course->teacher_id) === (int) $teacher->id ? 'selected' : '' }}>
                  {{ $teacher->full_name }} — {{ $teacher->subject }}
                </option>
              @endforeach
            </select>
            @error('teacher_id')
              <p class="text-danger small mt-1">{{ $message }}</p>
            @enderror
          </div>

          <div class="mb-3">
            <label class="form-label">Kurs nomi *</label>
            <input type="text" name="title" class="form-control" value="{{ old('title', $course->title) }}" required maxlength="255">
            @error('title')
              <p class="text-danger small mt-1">{{ $message }}</p>
            @enderror
          </div>

          <div class="mb-3">
            <label class="form-label">Kurs nomi (EN, ixtiyoriy)</label>
            <input type="text" name="title_en" class="form-control" value="{{ old('title_en', $course->title_en) }}" maxlength="255">
          </div>

          <div class="mb-3">
            <label class="form-label">Narxi *</label>
            <input type="text" name="price" class="form-control" value="{{ old('price', $course->price) }}" required maxlength="100">
            @error('price')
              <p class="text-danger small mt-1">{{ $message }}</p>
            @enderror
          </div>

          <div class="mb-3">
            <label class="form-label">Narxi (EN, ixtiyoriy)</label>
            <input type="text" name="price_en" class="form-control" value="{{ old('price_en', $course->price_en) }}" maxlength="100">
          </div>

          <div class="mb-3">
            <label class="form-label">Davomiyligi *</label>
            <input type="text" name="duration" class="form-control" value="{{ old('duration', $course->duration) }}" required maxlength="120">
            @error('duration')
              <p class="text-danger small mt-1">{{ $message }}</p>
            @enderror
          </div>

          <div class="mb-3">
            <label class="form-label">Davomiyligi (EN, ixtiyoriy)</label>
            <input type="text" name="duration_en" class="form-control" value="{{ old('duration_en', $course->duration_en) }}" maxlength="120">
          </div>

          <div class="mb-3">
            <label class="form-label">Boshlanish sanasi *</label>
            <input type="date" name="start_date" class="form-control" value="{{ old('start_date', $course->start_date?->format('Y-m-d')) }}" required>
            @error('start_date')
              <p class="text-danger small mt-1">{{ $message }}</p>
            @enderror
          </div>

          <div class="mb-3">
            <label class="form-label">Tavsif *</label>
            <textarea name="description" class="form-control" rows="6" required>{{ old('description', $course->description) }}</textarea>
            @error('description')
              <p class="text-danger small mt-1">{{ $message }}</p>
            @enderror
          </div>

          <div class="mb-3">
            <label class="form-label">Tavsif (EN, ixtiyoriy)</label>
            <textarea name="description_en" class="form-control" rows="6">{{ old('description_en', $course->description_en) }}</textarea>
          </div>

          <div class="mb-3">
            <label class="form-label">Yangi rasm (ixtiyoriy)</label>
            @if($course->image)
              <p class="small text-muted">Joriy: <a href="{{ asset('storage/'.$course->image) }}" target="_blank" rel="noopener">ko‘rish</a></p>
            @endif
            <input type="file" name="image" class="form-control" accept="image/jpeg,image/png,image/webp">
            @error('image')
              <p class="text-danger small mt-1">{{ $message }}</p>
            @enderror
          </div>

          <button type="submit" class="btn btn-primary">Saqlash</button>
        </form>
      </div>
    </div>
  </div>
</section>
@endsection
