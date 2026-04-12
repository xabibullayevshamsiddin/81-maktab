@php
  $hasAnyEventsInYear = count($countsByDate ?? []) > 0;
@endphp
<x-loyouts.main title="{{ __('public.calendar.page_title') }}">
  <section class="news-hero profile-hero">
    <div class="container">
      <div class="news-hero-content reveal">
        <span class="badge">{{ __('public.calendar.badge') }}</span>
        <h1 class="js-split-text"><strong>{{ __('public.calendar.hero_title') }}</strong></h1>
        <p>{{ __('public.calendar.hero_text') }}</p>
      </div>
    </div>
  </section>

  <main class="profile-main calendar-page">
    <div class="container">
      <div class="calendar-toolbar reveal">
        <form method="get" action="{{ route('calendar') }}" class="calendar-year-form">
          <label for="cal-y">{{ __('public.calendar.year') }}</label>
          <select id="cal-y" name="y" data-calendar-year-select>
            @for($y = (int) now()->year + 1; $y >= 2020; $y--)
              <option value="{{ $y }}" {{ (int) $year === $y ? 'selected' : '' }}>{{ $y }}</option>
            @endfor
          </select>
        </form>
        @if($hasAnyEventsInYear)
          <div class="calendar-legend" aria-hidden="true">
            <span class="calendar-legend-item">
              <span class="cal-dot cal-dot--event"></span> {{ __('public.calendar.legend_events') }}
            </span>
            <span class="calendar-legend-item">
              <span class="cal-dot cal-dot--today"></span> {{ __('public.calendar.legend_today') }}
            </span>
          </div>
        @endif
      </div>

      @if($events->isEmpty())
        <p class="profile-muted">{{ __('public.calendar.empty', ['year' => $year]) }}</p>
      @else
        @if($hasAnyEventsInYear)
          <section class="calendar-visual reveal" aria-label="{{ __('public.calendar.badge') }}">
            <div class="calendar-visual-head">
              <p class="calendar-visual-hint">{{ __('public.calendar.visual_hint') }}</p>
            </div>
            @include('partials.calendar-year-grid', [
              'calendarMonths' => $calendarMonths,
              'year' => $year,
            ])
          </section>
        @endif

        <section class="calendar-list-section reveal">
          <h2 class="calendar-list-heading">{{ __('public.calendar.list_title') }}</h2>
          <p class="calendar-list-lead">{{ __('public.calendar.list_lead') }}</p>

          <div class="calendar-event-list">
            @foreach($grouped as $dateStr => $dayEvents)
              @php
                $d = \Carbon\Carbon::parse($dateStr);
                $wdLong = __('public.calendar.weekdays_long.' . $d->dayOfWeek);
              @endphp
              <details
                class="profile-activity-block calendar-day-block calendar-day-dtls"
                id="calendar-day-{{ $dateStr }}"
              >
                <summary class="calendar-day-summary">
                  <span class="calendar-day-summary-inner">
                    <i class="fa-regular fa-calendar"></i>
                    {{ (int) $d->format('d') }}
                    {{ __('public.calendar.month_names.' . $d->month) }}
                    {{ $d->year }} — {{ $wdLong }}
                    <span class="calendar-day-count">{{ __('public.calendar.items_count', ['count' => $dayEvents->count()]) }}</span>
                  </span>
                  <i class="fa-solid fa-chevron-down calendar-day-chevron" aria-hidden="true"></i>
                </summary>
                <ul class="profile-activity-list calendar-day-activity-list" style="margin:0;">
                  @foreach($dayEvents as $ev)
                    @php
                      $eventTitle = localized_model_value($ev, 'title');
                      $eventTime = localized_model_value($ev, 'time_note');
                      $eventBody = localized_model_value($ev, 'body');
                    @endphp
                    <li style="border:none;padding:0;margin:0 0 14px;">
                      <p class="profile-activity-title">{{ $eventTitle }}</p>
                      @if($eventTime)
                        <span class="profile-muted" style="font-size:13px;"><i class="fa-regular fa-clock"></i> {{ $eventTime }}</span>
                      @endif
                      @if($eventBody)
                        <p class="profile-activity-body" style="margin-top:8px;">{{ $eventBody }}</p>
                      @endif
                    </li>
                  @endforeach
                </ul>
              </details>
            @endforeach
          </div>
        </section>

        @if($events->hasPages())
          <div class="news-pagination" style="margin-top: 28px;">
            @if ($events->onFirstPage())
              <span class="btn btn-sm btn-outline" aria-disabled="true">{{ __('public.posts.previous') }}</span>
            @else
              <a class="btn btn-sm btn-outline" href="{{ $events->previousPageUrl() }}">{{ __('public.posts.previous') }}</a>
            @endif

            <span class="news-page-info">
              {{ $events->currentPage() }} / {{ $events->lastPage() }}
            </span>

            @if ($events->hasMorePages())
              <a class="btn btn-sm" href="{{ $events->nextPageUrl() }}">{{ __('public.posts.next') }}</a>
            @else
              <span class="btn btn-sm" aria-disabled="true">{{ __('public.posts.next') }}</span>
            @endif
          </div>
        @endif
      @endif
    </div>
  </main>
  @push('page_scripts')
    <script src="{{ app_public_asset('temp/js/calendar-page.js') }}?v={{ filemtime(public_path('temp/js/calendar-page.js')) }}"></script>
  @endpush
</x-loyouts.main>
