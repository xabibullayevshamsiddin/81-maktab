<x-layouts.main title="{{ __('public.calendar.page_title') }}">
  <section class="news-hero news-hero-v2 profile-hero">
    <div class="container">
      <div class="news-hero-grid prime-reveal">
        <div class="news-hero-text">
          <span class="badge">{{ __('public.calendar.badge') }}</span>
          <h1 class="js-split-text"><strong>{{ __('public.calendar.hero_title') }}</strong></h1>
          <p>{{ __('public.calendar.hero_text') }}</p>
        </div>
        <div class="news-hero-visual">
          <div class="news-hero-3d-scene">
            <svg viewBox="0 0 350 300" fill="none" xmlns="http://www.w3.org/2000/svg">
              <ellipse cx="175" cy="270" rx="130" ry="16" fill="url(#calGroundGlow)" opacity="0.5"/>
              <rect x="70" y="40" width="210" height="220" rx="12" fill="url(#calGrad)" stroke="rgba(120,200,255,0.3)" stroke-width="1.5"/>
              <rect x="70" y="40" width="210" height="220" rx="12" fill="url(#calGlow)" opacity="0.3"/>
              <rect x="70" y="40" width="210" height="50" rx="12" fill="url(#calHeaderGrad)"/>
              <rect x="70" y="78" width="210" height="12" rx="0" fill="url(#calHeaderGrad)"/>
              <circle cx="105" cy="65" r="10" fill="rgba(56,189,248,0.4)"/>
              <circle cx="175" cy="65" r="10" fill="rgba(168,85,247,0.4)"/>
              <circle cx="245" cy="65" r="10" fill="rgba(56,189,248,0.3)"/>
              <rect x="90" y="105" width="30" height="25" rx="4" fill="rgba(56,189,248,0.15)" stroke="rgba(100,200,255,0.15)" stroke-width="0.5"/>
              <rect x="130" y="105" width="30" height="25" rx="4" fill="rgba(200,220,255,0.1)" stroke="rgba(100,200,255,0.1)" stroke-width="0.5"/>
              <rect x="170" y="105" width="30" height="25" rx="4" fill="rgba(168,85,247,0.15)" stroke="rgba(168,85,247,0.15)" stroke-width="0.5"/>
              <rect x="210" y="105" width="30" height="25" rx="4" fill="rgba(200,220,255,0.1)" stroke="rgba(100,200,255,0.1)" stroke-width="0.5"/>
              <rect x="90" y="140" width="30" height="25" rx="4" fill="rgba(200,220,255,0.1)" stroke="rgba(100,200,255,0.1)" stroke-width="0.5"/>
              <rect x="130" y="140" width="30" height="25" rx="4" fill="rgba(56,189,248,0.25)" stroke="rgba(56,189,248,0.3)" stroke-width="1"/>
              <circle cx="145" cy="152" r="4" fill="rgba(56,189,248,0.5)"/>
              <rect x="170" y="140" width="30" height="25" rx="4" fill="rgba(200,220,255,0.1)" stroke="rgba(100,200,255,0.1)" stroke-width="0.5"/>
              <rect x="210" y="140" width="30" height="25" rx="4" fill="rgba(200,220,255,0.1)" stroke="rgba(100,200,255,0.1)" stroke-width="0.5"/>
              <rect x="90" y="175" width="30" height="25" rx="4" fill="rgba(200,220,255,0.1)" stroke="rgba(100,200,255,0.1)" stroke-width="0.5"/>
              <rect x="130" y="175" width="30" height="25" rx="4" fill="rgba(200,220,255,0.1)" stroke="rgba(100,200,255,0.1)" stroke-width="0.5"/>
              <rect x="170" y="175" width="30" height="25" rx="4" fill="rgba(168,85,247,0.25)" stroke="rgba(168,85,247,0.3)" stroke-width="1"/>
              <circle cx="185" cy="187" r="4" fill="rgba(168,85,247,0.5)"/>
              <rect x="210" y="175" width="30" height="25" rx="4" fill="rgba(200,220,255,0.1)" stroke="rgba(100,200,255,0.1)" stroke-width="0.5"/>
              <rect x="90" y="210" width="30" height="25" rx="4" fill="rgba(200,220,255,0.1)" stroke="rgba(100,200,255,0.1)" stroke-width="0.5"/>
              <rect x="130" y="210" width="30" height="25" rx="4" fill="rgba(200,220,255,0.1)" stroke="rgba(100,200,255,0.1)" stroke-width="0.5"/>
              <rect x="170" y="210" width="30" height="25" rx="4" fill="rgba(200,220,255,0.1)" stroke="rgba(100,200,255,0.1)" stroke-width="0.5"/>
              <rect x="210" y="210" width="30" height="25" rx="4" fill="rgba(56,189,248,0.15)" stroke="rgba(100,200,255,0.15)" stroke-width="0.5"/>
              <circle cx="80" cy="30" r="1.5" fill="rgba(56,189,248,0.6)"><animate attributeName="cy" values="30;15;30" dur="3s" repeatCount="indefinite"/></circle>
              <circle cx="290" cy="40" r="2" fill="rgba(168,85,247,0.5)"><animate attributeName="cy" values="40;20;40" dur="4s" repeatCount="indefinite"/></circle>
              <circle cx="50" cy="150" r="1" fill="rgba(56,189,248,0.4)"><animate attributeName="cy" values="150;135;150" dur="3.5s" repeatCount="indefinite"/></circle>
              <circle cx="310" cy="160" r="1.5" fill="rgba(168,85,247,0.4)"><animate attributeName="cy" values="160;140;160" dur="2.8s" repeatCount="indefinite"/></circle>
              <defs>
                <linearGradient id="calGrad" x1="70" y1="40" x2="280" y2="260">
                  <stop offset="0%" stop-color="rgba(15,40,80,0.9)"/>
                  <stop offset="100%" stop-color="rgba(10,25,55,0.95)"/>
                </linearGradient>
                <linearGradient id="calGlow" x1="175" y1="40" x2="175" y2="260">
                  <stop offset="0%" stop-color="rgba(56,189,248,0.3)"/>
                  <stop offset="100%" stop-color="rgba(168,85,247,0.1)"/>
                </linearGradient>
                <linearGradient id="calHeaderGrad" x1="70" y1="40" x2="280" y2="90">
                  <stop offset="0%" stop-color="rgba(30,80,160,0.8)"/>
                  <stop offset="100%" stop-color="rgba(20,50,100,0.9)"/>
                </linearGradient>
                <radialGradient id="calGroundGlow" cx="0.5" cy="0.5" r="0.5">
                  <stop offset="0%" stop-color="rgba(56,189,248,0.3)"/>
                  <stop offset="100%" stop-color="rgba(168,85,247,0)"/>
                </radialGradient>
              </defs>
            </svg>
            <div class="news-hero-glow"></div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <main class="profile-main calendar-page">
    <div class="container">


      @if($events->isEmpty())
        <p class="profile-muted">{{ __('public.calendar.empty', ['year' => $year]) }}</p>
      @else
        @if($hasAnyEventsInYear)
          <section class="calendar-visual prime-reveal" aria-label="{{ __('public.calendar.badge') }}">
            <div class="calendar-visual-head">
              <p class="calendar-visual-hint">{{ __('public.calendar.visual_hint') }}</p>
            </div>
            @include('partials.calendar-year-grid', [
              'calendarMonths' => $calendarMonths,
              'year' => $year,
              'calendarDayRouteName' => 'calendar',
            ])
          </section>
        @endif

        <section class="calendar-list-section prime-reveal">
          <h2 class="calendar-list-heading">{{ __('public.calendar.list_title') }}</h2>
          <p class="calendar-list-lead">{{ __('public.calendar.list_lead') }}</p>

          <div class="calendar-event-list prime-stagger">
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
                <div class="calendar-day-accordion-body">
                  <div class="calendar-day-accordion-body-inner">
                    <ul class="profile-activity-list calendar-day-activity-list">
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
                  </div>
                </div>
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
    <script src="{{ app_public_asset('temp/js/calendar-page.js') }}?v={{ app_asset_version('temp/js/calendar-page.js') }}"></script>
  @endpush
</x-loyouts.main>
