@php
  $passQuotes = trans('public.exam.pass_quotes');
  $failQuotes = trans('public.exam.fail_quotes');
  $quote = $result->passed
    ? $passQuotes[array_rand($passQuotes)]
    : ($result->passed === false ? $failQuotes[array_rand($failQuotes)] : null);
@endphp
<x-loyouts.main :title="__('public.exam.result_page_title', ['title' => $result->exam->title])">
  <main class="news exam-page">
    <div class="exam-page-inner" style="max-width: 560px;">
      <header class="exam-hero">
        <span class="exam-hero-badge">
          <i class="fa-solid fa-flag-checkered"></i>
          {{ __('public.exam.finished_badge') }}
        </span>
        <h1 class="exam-title">
          {{ $result->exam->title }}
          @if($result->exam?->trashed())
            <span style="color:#ef4444; font-size:14px; display:block; margin-top:4px;">{{ __('public.exam.exam_deleted') }}</span>
          @endif
        </h1>
        <p class="exam-hero-lead">{!! __('public.exam.status_line', [
          'status' => $result->status,
          'grade' => $result->user_grade ?? $result->user->grade ?? '—',
        ]) !!}</p>
      </header>

      @if((int) ($result->rule_violation_count ?? 0) > 5)
        <p class="exam-result-pass is-fail" style="margin-bottom:16px;">
          <i class="fa-solid fa-ban"></i> {!! __('public.exam.violation_fail', ['count' => (int) $result->rule_violation_count]) !!}
        </p>
      @endif

      @if($result->passed === null)
        <p class="exam-result-pass is-pending">
          <i class="fa-solid fa-hourglass-half"></i> {{ __('public.exam.text_pending') }}
          <span style="display:block;font-size:13px;font-weight:600;opacity:0.9;margin-top:6px;">
            {{ __('public.exam.final_after_review') }}
          </span>
        </p>
      @elseif($result->passed !== null)
        <p class="exam-result-pass {{ $result->passed ? 'is-pass' : 'is-fail' }}">
          @if($result->passed)
            <i class="fa-solid fa-circle-check"></i> {{ __('public.exam.passed') }}
          @else
            <i class="fa-solid fa-circle-xmark"></i> {{ __('public.exam.failed') }}
          @endif
          <span style="display:block;font-size:13px;font-weight:600;opacity:0.9;margin-top:6px;">
            {{ __('public.exam.passing_points', ['points' => $result->exam->passing_points ?? '—']) }}
          </span>
        </p>

        @if($quote)
        <div class="exam-result-motivation {{ $result->passed ? 'is-pass' : 'is-fail' }}">
          <div class="motivation-icon">
            <i class="fa-solid {{ $result->passed ? 'fa-rocket' : 'fa-seedling' }}"></i>
          </div>
          <div class="motivation-text">
            "{{ $quote }}"
          </div>
        </div>
        @endif
      @endif

      <div class="exam-result-score">
        @if($result->points_max !== null && $result->points_earned !== null)
          <div class="exam-result-score-num">{{ $result->points_earned }} / {{ $result->points_max }}</div>
          <p style="margin:8px 0 0;font-size:14px;color:var(--muted);">{{ __('public.exam.score_points') }}</p>
        @else
          <div class="exam-result-score-num">{{ $result->score }} / {{ $result->total_questions }}</div>
          <p style="margin:8px 0 0;font-size:14px;color:var(--muted);">{{ __('public.exam.score_legacy') }}</p>
        @endif
        <p class="exam-result-meta">
          {{ __('public.exam.score_breakdown', ['score' => $result->score, 'total' => $result->total_questions]) }}<br>
          {{ __('public.exam.started_at', ['datetime' => $result->started_at?->format('d.m.Y H:i:s')]) }}<br>
          {{ __('public.exam.finished_at', ['datetime' => $result->submitted_at?->format('d.m.Y H:i:s')]) }}
        </p>
      </div>

      <p class="exam-result-meta" style="text-align:center;margin-bottom:16px;">
        {!! __('public.exam.saved_notice') !!}
      </p>

      <p class="exam-result-meta" style="text-align:center;margin-bottom:16px;">
        {!! __('public.exam.student_notice') !!}
      </p>

      <div style="text-align:center;">
        <a href="{{ route('exam.index') }}" class="exam-btn-primary">
          {{ __('public.exam.back_to_exams') }}
          <i class="fa-solid fa-list"></i>
        </a>
      </div>
    </div>
  </main>
</x-loyouts.main>
