<x-layouts.main :title="__('public.search.page_title')">
  <div class="bomba-mesh"></div>

  <section class="news-hero news-hero-v2 search-page-hero" id="home">
    <div class="container">
      <div class="news-hero-grid prime-reveal">
        <div class="news-hero-text">
          <span class="badge">{{ __('public.search.badge') }}</span>
          <h1 class="js-split-text">{{ __('public.search.hero_title') }}</h1>
          <p>{{ __('public.search.hero_text') }}</p>
        </div>
        <div class="news-hero-visual">
          <div class="news-hero-3d-scene">
            <svg viewBox="0 0 350 300" fill="none" xmlns="http://www.w3.org/2000/svg">
              <ellipse cx="175" cy="270" rx="130" ry="16" fill="url(#searchGroundGlow)" opacity="0.5"/>
              <circle cx="155" cy="130" r="70" fill="url(#searchGlassGrad)" stroke="rgba(120,200,255,0.3)" stroke-width="1.5"/>
              <circle cx="155" cy="130" r="70" fill="url(#searchGlassGlow)" opacity="0.3"/>
              <circle cx="155" cy="130" r="55" fill="rgba(7,17,31,0.6)" stroke="rgba(100,200,255,0.2)" stroke-width="1"/>
              <line x1="200" y1="180" x2="260" y2="240" stroke="rgba(56,189,248,0.6)" stroke-width="8" stroke-linecap="round"/>
              <circle cx="155" cy="130" r="30" fill="rgba(56,189,248,0.1)"/>
              <rect x="140" y="115" width="30" height="6" rx="3" fill="rgba(56,189,248,0.3)"/>
              <rect x="140" y="130" width="25" height="5" rx="2.5" fill="rgba(200,220,255,0.15)"/>
              <rect x="140" y="143" width="20" height="5" rx="2.5" fill="rgba(200,220,255,0.1)"/>
              <rect x="280" y="60" width="40" height="50" rx="8" fill="url(#searchDocGrad)" stroke="rgba(100,200,255,0.2)" stroke-width="1"/>
              <rect x="288" y="72" width="24" height="4" rx="2" fill="rgba(200,220,255,0.2)"/>
              <rect x="288" y="82" width="20" height="3" rx="1.5" fill="rgba(200,220,255,0.12)"/>
              <rect x="288" y="92" width="18" height="3" rx="1.5" fill="rgba(200,220,255,0.08)"/>
              <rect x="40" y="80" width="35" height="45" rx="8" fill="url(#searchDocGrad2)" stroke="rgba(100,200,255,0.2)" stroke-width="1"/>
              <rect x="47" y="90" width="20" height="4" rx="2" fill="rgba(168,85,247,0.2)"/>
              <rect x="47" y="100" width="18" height="3" rx="1.5" fill="rgba(200,220,255,0.12)"/>
              <rect x="47" y="110" width="15" height="3" rx="1.5" fill="rgba(200,220,255,0.08)"/>
              <circle cx="100" cy="50" r="1.5" fill="rgba(56,189,248,0.6)"><animate attributeName="cy" values="50;35;50" dur="3s" repeatCount="indefinite"/></circle>
              <circle cx="270" cy="40" r="2" fill="rgba(168,85,247,0.5)"><animate attributeName="cy" values="40;20;40" dur="4s" repeatCount="indefinite"/></circle>
              <circle cx="50" cy="180" r="1" fill="rgba(56,189,248,0.4)"><animate attributeName="cy" values="180;165;180" dur="3.5s" repeatCount="indefinite"/></circle>
              <circle cx="310" cy="190" r="1.5" fill="rgba(168,85,247,0.4)"><animate attributeName="cy" values="190;170;190" dur="2.8s" repeatCount="indefinite"/></circle>
              <defs>
                <linearGradient id="searchGlassGrad" x1="85" y1="60" x2="225" y2="200">
                  <stop offset="0%" stop-color="rgba(20,50,100,0.9)"/>
                  <stop offset="100%" stop-color="rgba(15,35,70,0.95)"/>
                </linearGradient>
                <linearGradient id="searchGlassGlow" x1="155" y1="60" x2="155" y2="200">
                  <stop offset="0%" stop-color="rgba(56,189,248,0.3)"/>
                  <stop offset="100%" stop-color="rgba(168,85,247,0.1)"/>
                </linearGradient>
                <linearGradient id="searchDocGrad" x1="280" y1="60" x2="320" y2="110">
                  <stop offset="0%" stop-color="rgba(25,60,120,0.8)"/>
                  <stop offset="100%" stop-color="rgba(15,35,70,0.9)"/>
                </linearGradient>
                <linearGradient id="searchDocGrad2" x1="40" y1="80" x2="75" y2="125">
                  <stop offset="0%" stop-color="rgba(100,60,180,0.7)"/>
                  <stop offset="100%" stop-color="rgba(60,40,120,0.8)"/>
                </linearGradient>
                <radialGradient id="searchGroundGlow" cx="0.5" cy="0.5" r="0.5">
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

  <main class="search-results-page">
    <section class="container courses-filter-section prime-reveal">
      <form method="GET" action="{{ route('search') }}" class="exam-filter-panel filter-shell search-results-form">
        <div class="exam-filter-row">
          <div class="exam-filter-field search-results-field">
            <label class="exam-filter-label" for="global-search-q">{{ __('public.search.query_label') }}</label>
            <div class="search-results-input-row">
              <input type="search" id="global-search-q" name="q" class="exam-filter-input" placeholder="{{ __('public.search.placeholder') }}" value="{{ $q }}" required>
              <button type="submit" class="btn btn-prime search-results-submit">
                <i class="fa-solid fa-magnifying-glass"></i>
                {{ __('public.search.submit') }}
              </button>
            </div>
          </div>
        </div>
      </form>

      @if(empty($q))
        <div class="notification-empty search-empty-state">
          <i class="fa-solid fa-magnifying-glass"></i>
          <p>{{ __('public.search.empty_query') }}</p>
        </div>
      @else
        <div class="section-head search-results-head">
          <h2>{{ __('public.search.results_title', ['query' => $q]) }}</h2>
          <p>{{ __('public.search.results_count', ['count' => count($results)]) }}</p>
        </div>

        @if(count($results) > 0)
          <div class="search-results-list prime-stagger">
            @foreach($results as $res)
              <a href="{{ $res['url'] }}" class="search-result-item">
                <span class="search-result-media">
                  @if($res['image'])
                    <img src="{{ $res['image'] }}" alt="" loading="lazy" decoding="async">
                  @else
                    <span class="search-result-fallback search-result-fallback--{{ $res['type'] }}">
                      @if($res['type'] === 'post')
                        <i class="fa-solid fa-newspaper"></i>
                      @elseif($res['type'] === 'teacher')
                        <i class="fa-solid fa-user-tie"></i>
                      @elseif($res['type'] === 'course')
                        <i class="fa-solid fa-book-open"></i>
                      @elseif($res['type'] === 'exam')
                        <i class="fa-solid fa-graduation-cap"></i>
                      @endif
                    </span>
                  @endif
                </span>

                <span class="search-result-main">
                  <span class="search-result-type search-result-type--{{ $res['type'] }}">
                    {{ data_get(trans('public.search.types'), $res['type'], __('public.search.types.default')) }}
                  </span>
                  <strong class="search-result-title">{{ $res['title'] }}</strong>
                  <span class="search-result-desc">{{ \Illuminate\Support\Str::limit($res['description'], 140) }}</span>
                </span>

                <span class="search-result-arrow">
                  <i class="fa-solid fa-arrow-up-right-from-square"></i>
                </span>
              </a>
            @endforeach
          </div>
        @else
          <div class="notification-empty search-empty-state">
            <i class="fa-solid fa-magnifying-glass"></i>
            <p>{{ __('public.search.no_results') }}</p>
          </div>
        @endif
      @endif
    </section>
  </main>
</x-loyouts.main>
