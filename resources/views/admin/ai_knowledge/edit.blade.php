@extends('admin.layouts.main')

@section('title', 'AI Bilimni Tahrirlash')

@section('content')
<section class="table-components">
  <div class="container-fluid">
    <div class="title-wrapper pt-30">
      <div class="row align-items-center">
        <div class="col-md-6">
          <div class="title">
            <h2>AI Bilimni Tahrirlash</h2>
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

          <form action="{{ route('ai-knowledges.update', $aiKnowledge->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row">
                <div class="col-md-6 mb-3">
                  <label class="form-label">Savol / Pattern (UZ)</label>
                  <input type="text" class="form-control" name="question" value="{{ old('question', $aiKnowledge->question) }}" required>
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label">Savol / Pattern (EN)</label>
                  <input type="text" class="form-control" name="question_en" value="{{ old('question_en', $aiKnowledge->question_en) }}">
                </div>
            </div>

            <div class="mb-3">
              <label class="form-label">Javob (UZ)</label>
              <textarea class="form-control" name="answer" rows="4" required>{{ old('answer', $aiKnowledge->answer) }}</textarea>
            </div>

            <div class="mb-3">
              <label class="form-label">Javob (EN)</label>
              <textarea class="form-control" name="answer_en" rows="4">{{ old('answer_en', $aiKnowledge->answer_en) }}</textarea>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                  <label class="form-label">Kalit so'zlar (vergul bilan)</label>
                  <input type="text" class="form-control" name="keywords" value="{{ old('keywords', $aiKnowledge->keywords) }}">
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label">Sinonimlar (vergul bilan)</label>
                  <input type="text" class="form-control" name="synonyms" value="{{ old('synonyms', $aiKnowledge->synonyms) }}">
                </div>
                <div class="col-md-3 mb-3">
                  <label class="form-label">Kategoriya</label>
                  <input type="text" class="form-control" name="category" value="{{ old('category', $aiKnowledge->category) }}">
                </div>
                <div class="col-md-3 mb-3">
                  <label class="form-label">Priority</label>
                  <input type="number" class="form-control" name="priority" value="{{ old('priority', $aiKnowledge->priority) }}">
                </div>
                <div class="col-md-3 mb-3">
                  <label class="form-label">Tartib</label>
                  <input type="number" class="form-control" name="sort_order" value="{{ old('sort_order', $aiKnowledge->sort_order) }}">
                </div>
            </div>

            <div class="form-check form-switch mb-3">
                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" {{ $aiKnowledge->is_active ? 'checked' : '' }}>
                <label class="form-check-label" for="is_active">Faol holatda</label>
            </div>

            <button type="submit" class="btn btn-primary">Yangilash</button>
            <a href="{{ route('ai-knowledges.index') }}" class="btn btn-danger">Bekor qilish</a>
          </form>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection
