<x-loyouts.main title="Imtihonlar">
@push('page_styles')
    <link rel="stylesheet" href="{{ app_public_asset('temp/css/profile-exams.css') }}?v={{ filemtime(public_path('temp/css/profile-exams.css')) }}">
@endpush
<div class="container exam-public-container"><div class="row"><div class="col-12">
@php
  $qCount = $totalQuestionCount ?? $questions->count();
  $need = $exam->required_questions;
  $ready = $exam->is_active;
  $pointsOk = ($pointsSum ?? 0) === (int) $exam->total_points;
@endphp
<div class="row">
  <div class="col-lg-12">
    <div class="exam-public-card mb-30">
      <div style="display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap;">
        <div>
          <h6 class="mb-5">2-bosqich — savollar: {{ $exam->title }}</h6>
          <p style="margin:0;font-size:13px;color:#64748b;">
            Qo‘shilgan: <strong>{{ $qCount }}</strong> / {{ $need }} ·
            Ballar yig‘indisi: <strong>{{ $pointsSum }}</strong> / {{ $exam->total_points }}
            @if($qCount >= $need && ! $pointsOk)
              <span style="color:#b91c1c;font-weight:600;"> — Yig‘indi umumiy ballga teng emas, imtihon faol bo‘lmaydi</span>
            @elseif($ready)
              <span style="color:#16a34a;font-weight:600;"> — Imtihon faol</span>
            @else
              <span style="color:#ca8a04;font-weight:600;"> — Yana {{ max(0, $need - $qCount) }} ta savol kerak</span>
            @endif
          </p>
        </div>
        @if($qCount < $need)
          <a href="{{ route('profile.exams.questions.create', $exam) }}" class="btn">Savol qo'shish</a>
        @else
          <span class="text-sm" style="color:#64748b;">Reja bo‘yicha savollar to‘ldi.</span>
        @endif
      </div>

      @include('admin.partials.search-bar', [
        'placeholder' => 'Savol matni bo‘yicha...',
        'action' => route('profile.exams.questions.index', $exam),
      ])

      @forelse($questions as $question)
        <div class="question-item">
          <span class="question-point-badge">{{ (int) $question->points }} ball</span>
          <div class="exam-admin-preview">{!! render_exam_rich_text($question->body) !!}</div>
          @if($question->image_url)
            <img src="{{ $question->image_url }}" alt="Savol rasmi" class="exam-admin-image" loading="lazy">
          @endif
          @if($question->isTextType())
            <div style="margin-top:12px; padding:12px; border-radius:10px; background:#f8fafc; border:1px solid #e2e8f0;">
              <p style="margin:0 0 6px; font-size:12px; font-weight:800; color:#b45309;">Matnli savol</p>
              <p style="margin:0; font-size:13px; color:#475569;"><strong>Namunaviy javob:</strong></p>
              <div style="margin-top:6px; white-space:pre-wrap;">{{ $question->model_answer ?: "Namunaviy javob kiritilmagan." }}</div>
            </div>
          @else
            <ul style="margin:10px 0 0 18px;">
              @foreach($question->options as $option)
                <li>
                  <strong>{{ $option->label }}.</strong> {!! render_exam_rich_text($option->body) !!}
                  @if($option->is_correct) <b>(to'g'ri)</b> @endif
                </li>
              @endforeach
            </ul>
          @endif
          <div style="display:flex; gap:8px; margin-top:10px;">
            <a href="{{ route('profile.exams.questions.edit', [$exam, $question]) }}" class="btn btn-warning btn-sm">Tahrirlash</a>
            <form method="POST" action="{{ route('profile.exams.questions.destroy', [$exam, $question]) }}">
              @csrf @method('DELETE')
              <button type="submit" class="btn btn-danger btn-sm">O'chirish</button>
            </form>
          </div>
        </div>
      @empty
        <p class="mt-20">Savollar hali qo'shilmagan.</p>
      @endforelse
      @if($questions->hasPages())
        <div class="p-3">
          {{ $questions->links() }}
        </div>
      @endif
    </div>
  </div>
</div>
</div></div></div>
</x-loyouts.main>
