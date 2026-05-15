<x-loyouts.main :title="__('public.exam.start_page_title', ['title' => $exam->title])">
  <main class="news exam-page exam-start-page">
    <div class="exam-page-inner" style="max-width: 560px;">
      <header class="exam-hero">
        <span class="exam-hero-badge">
          <i class="fa-solid fa-play"></i>
          {{ __('public.exam.prep_badge') }}
        </span>
        <h1 class="exam-title">{{ $exam->title }}</h1>
        <p class="exam-hero-lead">
          {!! __('public.exam.duration_line', ['minutes' => $exam->duration_minutes]) !!} ·
          {!! __('public.exam.points_line', ['points' => $exam->total_points ?? '-']) !!} ·
          {!! __('public.exam.questions_line', ['count' => $exam->required_questions ?? '-']) !!}
        </p>
        <p class="exam-hero-lead exam-hero-hint" style="font-size:0.95rem;margin-top:8px;">
          <i class="fa-solid fa-users"></i>
          {{ __('public.exam.allowed_grades', ['grades' => $exam->allowedGradesLabel()]) }}
        </p>
      </header>

      <article class="exam-card" style="text-align:center;">
        @if($existing && ($existing->status === 'submitted' || $existing->status === 'expired'))
          <p style="margin:0 0 16px;color:var(--muted);">{{ __('public.exam.already_submitted') }}</p>
          <a href="{{ route('exam.result.show', $existing) }}" class="exam-btn-primary">
            {{ __('public.exam.view_result') }}
            <i class="fa-solid fa-chart-simple"></i>
          </a>
        @elseif($existing)
          <p style="margin:0 0 16px;color:var(--muted);">{{ __('public.exam.in_progress') }}</p>
          <a href="{{ route('exam.session', $existing) }}" class="exam-btn-primary">
            {{ __('public.exam.continue') }}
            <i class="fa-solid fa-arrow-right"></i>
          </a>
        @elseif(!($canStartNow ?? true))
          <p style="margin:0 0 12px;font-size:15px;color:var(--text);line-height:1.5;">
            <i class="fa-regular fa-calendar-days" style="margin-right:6px;opacity:0.85;"></i>
            {!! __('public.exam.scheduled_start', ['date' => $exam->availableFromLabel()]) !!}
          </p>
          <p style="margin:0;font-size:14px;color:var(--muted);">{{ __('public.exam.scheduled_wait') }}</p>
        @else
          <form action="{{ route('exam.start', $exam) }}" method="POST">
            @csrf
            <button type="submit" class="exam-btn-primary" style="width:100%;justify-content:center;">
              {{ __('public.exam.start_button') }}
              <i class="fa-solid fa-bolt"></i>
            </button>
          </form>
          <p style="margin:16px 0 0;font-size:12px;color:var(--muted);line-height:1.5;">
            {{ __('public.exam.start_notice') }}
          </p>
        @endif

        <div style="margin-top:22px;">
          <a href="{{ route('exam.index') }}" class="exam-btn-secondary">&larr; {{ __('public.exam.back_to_list') }}</a>
        </div>
      </article>
    </div>
  </main>
</x-loyouts.main>
