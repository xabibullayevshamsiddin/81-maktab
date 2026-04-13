@extends('admin.layouts.main')

@section('title', 'Yangi AI Bilim qo\'shish')

@section('content')
<section class="table-components">
  <div class="container-fluid">
    <div class="title-wrapper pt-30">
      <div class="row align-items-center">
        <div class="col-md-6">
          <div class="title">
            <h2>Yangi AI Bilim qo'shish</h2>
          </div>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-lg-8">
        <div class="card-style mb-30">
          @if ($errors->any())
            <div class="alert-box danger-alert mb-20">
              <div class="alert">{{ $errors->first() }}</div>
            </div>
          @endif

          <form action="{{ route('ai-knowledges.store') }}" method="POST">
            @csrf

            <div class="row">
                <div class="col-md-6 mb-3">
                  <label class="form-label">Savol / Pattern (UZ)</label>
                  <input type="text" class="form-control" name="question" value="{{ old('question') }}" placeholder="masalan: Maktab qachon ochilgan?" required>
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label">Savol / Pattern (EN)</label>
                  <input type="text" class="form-control" name="question_en" value="{{ old('question_en') }}" placeholder="e.g. When was the school opened?">
                </div>
            </div>

            <div class="mb-3">
              <label class="form-label">Javob (UZ)</label>
              <textarea class="form-control" name="answer" rows="4" required>{{ old('answer') }}</textarea>
            </div>

            <div class="mb-3">
              <label class="form-label">Javob (EN)</label>
              <textarea class="form-control" name="answer_en" rows="4">{{ old('answer_en') }}</textarea>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                  <label class="form-label">Kalit so'zlar (vergul bilan)</label>
                  <input type="text" class="form-control" name="keywords" value="{{ old('keywords') }}" placeholder="maktab, ochilish, sana">
                  <small class="text-muted">Foydalanuvchi xabarida shu so'zlar bo'lsa, ushbu javob qaytariladi.</small>
                </div>
                <div class="col-md-3 mb-3">
                  <label class="form-label">Kategoriya</label>
                  <input type="text" class="form-control" name="category" value="{{ old('category', 'Umumiy') }}">
                </div>
                <div class="col-md-3 mb-3">
                  <label class="form-label">Tartib</label>
                  <input type="number" class="form-control" name="sort_order" value="{{ old('sort_order', 0) }}">
                </div>
            </div>

            <div class="form-check form-switch mb-3">
                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" checked>
                <label class="form-check-label" for="is_active">Faol holatda</label>
            </div>

            <button type="submit" class="btn btn-primary">Saqlash</button>
            <a href="{{ route('ai-knowledges.index') }}" class="btn btn-danger">Bekor qilish</a>
          </form>
        </div>
      </div>
      
      <div class="col-lg-4">
          <div class="card-style mb-30">
              <h6>Yordam</h6>
              <p class="text-sm mt-10">
                  <strong>Pattern:</strong> AI foydalanuvchi xabaridan aynan shu gapni yoki uning qismini qidiradi.<br><br>
                  <strong>Kalit so'zlar:</strong> Agar ushbu maydonga 'narx, to'lov' deb yozsangiz, foydalanuvchi xabarida 'narx' YOKI 'to'lov' so'zi qatnashsa, AI ushbu javobni beradi.
              </p>
          </div>
      </div>
    </div>
  </div>
</section>
@endsection
