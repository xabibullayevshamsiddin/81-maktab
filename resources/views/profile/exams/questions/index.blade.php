<x-layouts.main title="Imtihon Savollari — {{ $exam->title }}">
@push('page_styles')
<style>
/* ====================================================================
   Exam Questions Management — Modern Glass UI
   ==================================================================== */
.questions-page-wrapper {
  padding: 30px 0 70px;
}

.questions-hero-bar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 16px;
  margin-bottom: 24px;
}

.questions-back-btn {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 8px 18px;
  border-radius: 12px;
  font-size: 0.9rem;
  font-weight: 600;
  color: var(--text-color, #1e293b);
  background: var(--card-bg, rgba(255, 255, 255, 0.8));
  border: 1px solid var(--border-color, rgba(148, 163, 184, 0.2));
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
  text-decoration: none;
  transition: all 0.25s ease;
}

.questions-back-btn:hover {
  transform: translateX(-3px);
  color: var(--primary-color, #4f46e5);
  border-color: var(--primary-color, #4f46e5);
}

.btn-add-question {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 9px 20px;
  border-radius: 12px;
  font-weight: 700;
  font-size: 0.9rem;
  background: linear-gradient(135deg, #4f46e5, #6366f1);
  color: #fff !important;
  box-shadow: 0 4px 14px rgba(79, 70, 229, 0.3);
  text-decoration: none;
  border: none;
  transition: all 0.25s ease;
}

.btn-add-question:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(79, 70, 229, 0.4);
}

/* Glass Main Card */
.questions-glass-card {
  background: var(--card-bg, rgba(255, 255, 255, 0.9));
  border: 1px solid var(--border-color, rgba(148, 163, 184, 0.2));
  border-radius: 20px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.04);
  backdrop-filter: blur(16px);
  -webkit-backdrop-filter: blur(16px);
  padding: 24px;
}

.questions-stats-bar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 12px;
  background: var(--detail-bg, rgba(241, 245, 249, 0.6));
  border-radius: 14px;
  padding: 14px 18px;
  margin-bottom: 20px;
}

.question-card-item {
  background: var(--card-bg, #ffffff);
  border: 1px solid var(--border-color, rgba(148, 163, 184, 0.2));
  border-radius: 16px;
  padding: 20px;
  margin-bottom: 16px;
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.02);
  transition: all 0.25s ease;
}

.question-card-item:hover {
  border-color: rgba(99, 102, 241, 0.3);
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.05);
}

.question-card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 10px;
  margin-bottom: 12px;
}

.question-badge-points {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  padding: 4px 12px;
  border-radius: 8px;
  font-size: 0.82rem;
  font-weight: 700;
  background: rgba(99, 102, 241, 0.1);
  color: #4f46e5;
  border: 1px solid rgba(99, 102, 241, 0.25);
}

.question-options-list {
  list-style: none;
  padding: 0;
  margin: 14px 0 0;
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.question-option-pill {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px 14px;
  border-radius: 10px;
  background: rgba(241, 245, 249, 0.6);
  border: 1px solid rgba(148, 163, 184, 0.15);
  font-size: 0.9rem;
}

.question-option-pill.is-correct {
  background: rgba(16, 185, 129, 0.1);
  border-color: rgba(16, 185, 129, 0.3);
  color: #065f46;
  font-weight: 600;
}

.question-actions-bar {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-top: 16px;
  padding-top: 14px;
  border-top: 1px solid var(--border-soft, rgba(148, 163, 184, 0.12));
}

/* Dark Mode Overrides */
:root[data-theme='dark'] .questions-glass-card {
  background: linear-gradient(180deg, rgba(15, 27, 45, 0.95), rgba(10, 19, 33, 0.98)) !important;
  border-color: rgba(255, 255, 255, 0.08) !important;
}

:root[data-theme='dark'] .questions-back-btn {
  background: rgba(255, 255, 255, 0.05) !important;
  border-color: rgba(255, 255, 255, 0.1) !important;
  color: #f1f5f9 !important;
}

:root[data-theme='dark'] .questions-stats-bar {
  background: rgba(15, 23, 42, 0.4) !important;
}

:root[data-theme='dark'] .question-card-item {
  background: rgba(15, 23, 42, 0.6) !important;
  border-color: rgba(255, 255, 255, 0.08) !important;
}

:root[data-theme='dark'] .question-option-pill {
  background: rgba(15, 23, 42, 0.4) !important;
  border-color: rgba(255, 255, 255, 0.06) !important;
  color: #e2e8f0 !important;
}

:root[data-theme='dark'] .question-option-pill.is-correct {
  background: rgba(16, 185, 129, 0.15) !important;
  border-color: rgba(16, 185, 129, 0.3) !important;
  color: #34d399 !important;
}
</style>
@endpush

  <section class="news-hero profile-hero">
    <div class="container">
      <div class="news-hero-content prime-reveal">
        <span class="badge"><i class="fa-solid fa-layer-group me-1"></i> 2-bosqich: Savollar</span>
        <h1><strong>{{ $exam->title }}</strong></h1>
        <p>Imtihon savollarini qo'shing, tahrirlang va ballarini boshqaring.</p>
      </div>
    </div>
  </section>

  <main class="questions-page-wrapper">
    <div class="container">

      @php
        $qCount = $totalQuestionCount ?? $questions->count();
        $need = $exam->required_questions;
        $ready = $exam->is_active;
        $pointsOk = ($pointsSum ?? 0) === (int) $exam->total_points;
      @endphp

      {{-- Top Actions Bar --}}
      <div class="questions-hero-bar">
        <a href="{{ route('profile.exams.index') }}" class="questions-back-btn">
          <i class="fa-solid fa-arrow-left"></i> Imtihonlar ro'yxatiga qaytish
        </a>

        @if($qCount < $need)
          <a href="{{ route('profile.exams.questions.create', $exam) }}" class="btn-add-question">
            <i class="fa-solid fa-plus-circle"></i> Yangi savol qo'shish
          </a>
        @else
          <span class="badge" style="background:#dcfce7; color:#166534; padding:8px 16px; border-radius:12px; font-weight:700;">
            <i class="fa-solid fa-circle-check me-1"></i> Barcha savollar to'ldi
          </span>
        @endif
      </div>

      {{-- Main Glass Card Container --}}
      <div class="questions-glass-card">

        {{-- Progress & Stats Bar --}}
        <div class="questions-stats-bar">
          <div style="font-size: 0.92rem; font-weight: 600;">
            <span>Savollar: <strong>{{ $qCount }}</strong> / {{ $need }} ta</span> ·
            <span>Ballar: <strong>{{ $pointsSum ?? 0 }}</strong> / {{ $exam->total_points }} ball</span>
          </div>

          <div>
            @if($qCount >= $need && ! $pointsOk)
              <span style="color:#b91c1c; font-weight:700; font-size:0.85rem;">
                <i class="fa-solid fa-triangle-exclamation me-1"></i> Ballar umumiyga teng emas!
              </span>
            @elseif($ready)
              <span style="color:#16a34a; font-weight:700; font-size:0.85rem;">
                <i class="fa-solid fa-circle-check me-1"></i> Imtihon to'liq faol
              </span>
            @else
              <span style="color:#d97706; font-weight:700; font-size:0.85rem;">
                <i class="fa-solid fa-hourglass-half me-1"></i> Yana {{ max(0, $need - $qCount) }} ta savol kerak
              </span>
            @endif
          </div>
        </div>

        {{-- Search Bar --}}
        <div class="mb-4">
          @include('admin.partials.search-bar', [
            'placeholder' => 'Savol matni bo‘yicha izlash...',
            'action' => route('profile.exams.questions.index', $exam),
          ])
        </div>

        {{-- Questions Stream --}}
        @forelse($questions as $question)
          <div class="question-card-item">
            <div class="question-card-header">
              <span class="question-badge-points">
                <i class="fa-solid fa-star"></i> {{ (int) $question->points }} ball
              </span>
              <span class="text-muted small">#{{ $loop->iteration }}</span>
            </div>

            <div class="exam-admin-preview" style="font-size: 1rem; font-weight: 600; line-height: 1.5;">
              {!! render_exam_rich_text($question->body) !!}
            </div>

            @if($question->image_url)
              <div style="margin-top: 12px;">
                <img src="{{ $question->image_url }}" alt="Savol rasmi" style="max-width: 100%; max-height: 280px; border-radius: 12px; border: 1px solid rgba(148,163,184,0.2);" loading="lazy">
              </div>
            @endif

            @if($question->isTextType())
              <div style="margin-top:14px; padding:14px; border-radius:12px; background:rgba(245,158,11,0.06); border:1px solid rgba(245,158,11,0.25);">
                <p style="margin:0 0 6px; font-size:0.8rem; font-weight:800; color:#b45309; text-transform:uppercase;">Matnli savol (Ochiq javob)</p>
                <p style="margin:0; font-size:0.88rem; color:#475569;"><strong>Namunaviy javob:</strong></p>
                <div style="margin-top:4px; white-space:pre-wrap; font-size:0.88rem;">{{ $question->model_answer ?: "Namunaviy javob kiritilmagan." }}</div>
              </div>
            @else
              <ul class="question-options-list">
                @foreach($question->options as $option)
                  <li class="question-option-pill {{ $option->is_correct ? 'is-correct' : '' }}">
                    <strong>{{ $option->label }}.</strong>
                    <span>{!! render_exam_rich_text($option->body) !!}</span>
                    @if($option->is_correct)
                      <span class="ms-auto" style="font-size: 0.78rem; background: #10b981; color: #fff; padding: 2px 8px; border-radius: 6px;">
                        <i class="fa-solid fa-check me-1"></i> To'g'ri
                      </span>
                    @endif
                  </li>
                @endforeach
              </ul>
            @endif

            <div class="question-actions-bar">
              <a href="{{ route('profile.exams.questions.edit', [$exam, $question]) }}" class="btn btn-warning btn-sm" style="border-radius: 8px;">
                <i class="fa-solid fa-pen-to-square me-1"></i> Tahrirlash
              </a>
              <form method="POST" action="{{ route('profile.exams.questions.destroy', [$exam, $question]) }}" style="margin:0;" data-confirm="Savolni o'chirmoqchimisiz?" data-confirm-title="Savolni o'chirish" data-confirm-variant="danger" data-confirm-ok="O'chirish">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger btn-sm" style="border-radius: 8px;">
                  <i class="fa-solid fa-trash-can me-1"></i> O'chirish
                </button>
              </form>
            </div>
          </div>
        @empty
          <div style="padding: 40px 20px; text-align: center; color: var(--muted-text, #64748b);">
            <i class="fa-solid fa-clipboard-question mb-2" style="font-size: 2.5rem; opacity: 0.4; display: block;"></i>
            <span style="font-weight: 600; font-size: 1rem;">Savollar hali qo'shilmagan.</span>
          </div>
        @endforelse

        {{-- Pagination --}}
        @if($questions->hasPages())
          <div style="padding-top: 16px; border-top: 1px solid var(--border-soft, rgba(148, 163, 184, 0.12));">
            {{ $questions->links() }}
          </div>
        @endif

      </div>
    </div>
  </main>
</x-layouts.main>

