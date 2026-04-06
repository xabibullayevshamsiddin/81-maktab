<x-loyouts.main title="{{ __('public.calendar.page_title') }}">
  <section class="news-hero profile-hero">
    <div class="container">
      <div class="news-hero-content reveal">
        <span class="badge">{{ __('public.calendar.badge') }}</span>
        <h1><strong>{{ __('public.calendar.hero_title') }}</strong></h1>
        <p>{{ __('public.calendar.hero_text') }}</p>
      </div>
    </div>
  </section>

  <main class="profile-main calendar-page">
    <div class="container">
      <form method="get" action="{{ route('calendar') }}" class="calendar-year-form">
        <label for="cal-y" class="profile-muted">{{ __('public.calendar.year') }}</label>
        <select id="cal-y" name="y" class="comment-input" style="max-width:120px;" data-calendar-year-select>
          @for($y = (int) now()->year + 1; $y >= 2020; $y--)
            <option value="{{ $y }}" {{ (int) $year === $y ? 'selected' : '' }}>{{ $y }}</option>
          @endfor
        </select>
      </form>

      @if($events->isEmpty())
        <p class="profile-muted">{{ __('public.calendar.empty', ['year' => $year]) }}</p>
      @else
        <div class="calendar-event-list">
          @foreach($grouped as $dateStr => $dayEvents)
            @php $d = \Carbon\Carbon::parse($dateStr); @endphp
            <details class="profile-activity-block calendar-day-block calendar-day-dtls">
              <summary class="calendar-day-summary">
                <span class="calendar-day-summary-inner">
                  <i class="fa-regular fa-calendar"></i>
                  {{ $d->format('d.m.Y') }}
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
    <script src="{{ asset('temp/js/calendar-page.js') }}?v={{ filemtime(public_path('temp/js/calendar-page.js')) }}"></script>
  @endpush
</x-loyouts.main>
