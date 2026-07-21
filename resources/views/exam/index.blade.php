<x-layouts.main :title="__('public.exam.page_title')">
  <main class="news exam-page">
    <div class="exam-page-inner">
      <header class="exam-hero">
        <span class="exam-hero-badge">
          <i class="fa-solid fa-graduation-cap"></i>
          {{ __('public.exam.badge') }}
        </span>
        <h1 class="exam-title js-split-text">{{ __('public.exam.title') }}</h1>
        <p class="exam-hero-lead">
          {!! __('public.exam.lead') !!}
        </p>
        @if($hasRestrictedExams)
          <p class="exam-hero-lead exam-hero-hint">
            <i class="fa-solid fa-lock"></i>
            {{ __('public.exam.restricted_hint') }}
          </p>
        @endif
      </header>

      @if($isParent ?? false)
        <div class="exam-card exam-parent-alert">
          <p class="exam-parent-alert-title">
            <i class="fa-solid fa-user-shield"></i>
            {{ __('public.exam.parent_title') }}
          </p>
          <p class="exam-parent-alert-text">{{ __('public.exam.parent_text') }}</p>
        </div>
      @endif

      @if($exams->isEmpty())
        <div class="exam-grid">
          <div class="exam-empty">
            <p class="exam-empty-message"><i class="fa-solid fa-inbox"></i> {{ __('public.exam.empty') }}</p>
          </div>
        </div>
      @else
        <div
          class="exam-filter-panel filter-shell"
          id="exam-filter-panel"
          data-sticky-filter
          data-i18n-count-total="{{ __('public.exam.count_total', ['total' => ':total']) }}"
          data-i18n-count-showing="{{ __('public.exam.count_showing', ['shown' => ':shown', 'total' => ':total']) }}"
          data-i18n-tag-search="{{ __('public.exam.tag_search', ['query' => ':query']) }}"
          data-i18n-filter-empty="{{ __('public.search.filter_empty') }}"
        >
          <div class="exam-filter-row">
            <div class="exam-filter-field">
              <label class="exam-filter-label" for="exam-filter-q">{{ __('public.exam.search_label') }}</label>
              <input type="search" id="exam-filter-q" class="exam-filter-input" placeholder="{{ __('public.exam.search_placeholder') }}" autocomplete="off">
            </div>
            <div class="exam-filter-field">
              <label class="exam-filter-label" for="exam-filter-grade">{{ __('public.exam.grade_label') }}</label>
              <select id="exam-filter-grade" class="exam-filter-select">
                <option value="">{{ __('public.common.all_grades') }}</option>
                @foreach(range(1, 11) as $g)
                  <option value="{{ $g }}">{{ $g }}{{ __('auth_pages.register.grade_group_suffix') }}</option>
                @endforeach
              </select>
            </div>
          </div>
          <div class="exam-filter-row">
            <div class="exam-filter-field">
              <label class="exam-filter-label" for="exam-filter-state">{{ __('public.exam.state_label') }}</label>
              <select id="exam-filter-state" class="exam-filter-select">
                <option value="">{{ __('public.exam.state_all') }}</option>
                <option value="open">{{ __('public.exam.state_open') }}</option>
                <option value="scheduled">{{ __('public.exam.state_scheduled') }}</option>
                <option value="done">{{ __('public.exam.state_done') }}</option>
                <option value="locked">{{ __('public.exam.state_locked') }}</option>
              </select>
            </div>
            <div class="exam-filter-field">
              <label class="exam-filter-label" for="exam-filter-sort">{{ __('public.exam.sort_label') }}</label>
              <select id="exam-filter-sort" class="exam-filter-select">
                <option value="id-desc">{{ __('public.exam.sort_newest') }}</option>
                <option value="id-asc">{{ __('public.exam.sort_oldest') }}</option>
                <option value="title-asc">{{ __('public.exam.sort_title_asc') }}</option>
                <option value="title-desc">{{ __('public.exam.sort_title_desc') }}</option>
                <option value="duration-asc">{{ __('public.exam.sort_duration_asc') }}</option>
                <option value="duration-desc">{{ __('public.exam.sort_duration_desc') }}</option>
                <option value="points-desc">{{ __('public.exam.sort_points_desc') }}</option>
                <option value="points-asc">{{ __('public.exam.sort_points_asc') }}</option>
              </select>
            </div>
          </div>
          <div class="filter-toolbar">
            <div class="filter-active-tags exam-filter-chip-row" id="exam-filter-tags"></div>
            <button type="button" class="filter-reset-link" id="exam-filter-reset">
              <i class="fa-solid fa-rotate-left"></i>
              {{ __('public.common.clear_filters') }}
            </button>
          </div>
        </div>
        <p class="exam-filter-count" id="exam-filter-count" aria-live="polite"></p>

        <div class="exam-grid" id="exam-grid">
          @foreach($exams as $exam)
            @php
              $row = $resultByExam[$exam->id] ?? null;
              $isLocked = ! $row && ! $exam->allowsUser($user);
              $isDone = $row && in_array($row->status, ['submitted', 'expired'], true);
              $isScheduled = ! $isLocked && ! $isDone && ! $exam->isOpenForStarting($user);
              $cardState = $isLocked ? 'locked' : ($isDone ? 'done' : ($isScheduled ? 'scheduled' : 'open'));
              $gradeNums = collect($exam->allowedGradeItems())
                ->map(function ($gradeItem) {
                    return preg_match('/^(\\d{1,2})-/', (string) $gradeItem, $matches) ? $matches[1] : null;
                })
                ->filter()
                ->unique()
                ->sort()
                ->values()
                ->implode(',');
              $searchText = mb_strtolower($exam->title, 'UTF-8');
            @endphp
            <article
              class="exam-card {{ $isLocked ? 'exam-card--locked' : '' }}"
              data-exam-card
              data-exam-id="{{ $exam->id }}"
              data-search-text="{{ e($searchText) }}"
              data-title-sort="{{ e($exam->title) }}"
              data-grade-nums="{{ e($gradeNums) }}"
              data-unrestricted="{{ $exam->hasGradeRestrictions() ? '0' : '1' }}"
              data-duration="{{ (int) $exam->duration_minutes }}"
              data-points="{{ (int) ($exam->total_points ?? 0) }}"
              data-state="{{ $cardState }}"
            >
              @if($isLocked)
                <span class="exam-card-lock-badge">
                  <i class="fa-solid fa-lock"></i>
                  {{ __('public.exam.locked_badge') }}
                </span>
              @endif

              <h2 class="exam-card-title">{{ $exam->title }}</h2>
              <div class="exam-card-meta">
                <span class="exam-meta-pill"><i class="fa-regular fa-clock"></i> {{ $exam->duration_minutes }} {{ __('public.exam.minutes_short') }}</span>
                <span class="exam-meta-pill"><i class="fa-solid fa-star"></i> {{ $exam->total_points ?? '-' }} {{ __('public.exam.points_short') }}</span>
              </div>

              @if($exam->hasGradeRestrictions())
                <p class="exam-card-grade-note">
                  <i class="fa-solid fa-user-graduate"></i>
                  {!! __('public.exam.allowed_grades', ['grades' => '<strong>'.e($exam->allowedGradesLabel()).'</strong>']) !!}
                </p>
              @endif

              @if($isLocked)
                <div class="exam-card-locked-box">
                  <p class="exam-card-locked-title">
                    <i class="fa-solid fa-shield-lock"></i>
                    {{ __('public.exam.locked_title') }}
                  </p>
                  <p class="exam-card-locked-text">
                    {!! __('public.exam.locked_text', ['grades' => $exam->allowedGradesLabel()]) !!}
                  </p>
                  <span class="exam-btn-locked">
                    <i class="fa-solid fa-lock"></i>
                    {{ __('public.exam.grade_mismatch') }}
                  </span>
                </div>
              @elseif($row && in_array($row->status, ['submitted', 'expired'], true))
                <div class="exam-card-actions">
                  <span class="exam-tag-done"><i class="fa-solid fa-circle-check"></i> {{ __('public.exam.submitted') }}</span>
                  <a href="{{ route('exam.result.show', $row) }}" class="exam-btn-secondary">
                    <i class="fa-solid fa-chart-simple"></i> {{ __('public.exam.view_result') }}
                  </a>
                </div>
              @elseif($isScheduled)
                <span class="exam-hero-badge exam-hero-badge--scheduled">
                  <i class="fa-regular fa-calendar-days"></i>
                  {{ __('public.exam.starts_from', ['date' => $exam->availableFromLabel()]) }}
                </span>
                <p class="exam-card-locked-text exam-card-note">
                  {{ __('public.exam.scheduled_note') }}
                </p>
                <a href="{{ route('exam.start.page', $exam) }}" class="exam-btn-secondary">
                  {{ __('public.exam.details') }}
                  <i class="fa-solid fa-arrow-right"></i>
                </a>
              @else
                <a href="{{ route('exam.start.page', $exam) }}" class="exam-btn-primary">
                  {{ __('public.exam.start') }}
                  <i class="fa-solid fa-arrow-right"></i>
                </a>
              @endif
            </article>
          @endforeach
        </div>

        <div class="exam-empty exam-filter-zero" id="exam-filter-zero" hidden>
          <p class="exam-empty-message">
            <i class="fa-solid fa-filter-circle-xmark"></i>
            {{ __('public.exam.filter_zero') }}
          </p>
        </div>
      @endif
    </div>
  </main>
</x-loyouts.main>
