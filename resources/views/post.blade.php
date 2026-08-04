<x-layouts.main title="{{ __('public.posts.page_title') }}">
  @php
    $f = $filter ?? 'all';
    $filterOptions = [
      'all' => __('public.posts.filters.all'),
      'video_news' => __('public.posts.filters.video_news'),
      'social' => __('public.posts.filters.social'),
      'popular' => __('public.posts.filters.popular'),
      'likes' => __('public.posts.filters.likes'),
    ];
    $activeFilterLabel = $filterOptions[$f] ?? $filterOptions['all'];
  @endphp


  <section class="news-hero news-hero-v2" id="home">
    <div class="container">
      <div class="news-hero-grid prime-reveal">
        <div class="news-hero-text">
          <h1 class="js-split-text">{{ __('public.posts.hero_title') }}</h1>
          <p>{{ __('public.posts.hero_text') }}</p>
          <a href="#posts" class="news-hero-cta">
            {{ __('public.posts.jump') }} <i class="fa-solid fa-arrow-down" style="margin-left: 8px;"></i>
          </a>
        </div>
        <div class="news-hero-visual">
          <div class="news-hero-3d-scene">
            <svg viewBox="0 0 350 300" fill="none" xmlns="http://www.w3.org/2000/svg">
              <ellipse cx="175" cy="270" rx="140" ry="16" fill="url(#newsGroundGlow)" opacity="0.5"/>
              <rect x="60" y="50" width="230" height="200" rx="12" fill="url(#newsCardGrad)" stroke="rgba(120,200,255,0.3)" stroke-width="1.5"/>
              <rect x="60" y="50" width="230" height="200" rx="12" fill="url(#newsCardGlow)" opacity="0.3"/>
              <rect x="80" y="70" width="120" height="12" rx="6" fill="rgba(56,189,248,0.4)"/>
              <rect x="80" y="92" width="90" height="8" rx="4" fill="rgba(200,220,255,0.2)"/>
              <rect x="80" y="110" width="190" height="6" rx="3" fill="rgba(200,220,255,0.15)"/>
              <rect x="80" y="124" width="180" height="6" rx="3" fill="rgba(200,220,255,0.12)"/>
              <rect x="80" y="138" width="170" height="6" rx="3" fill="rgba(200,220,255,0.1)"/>
              <rect x="80" y="160" width="80" height="60" rx="8" fill="url(#newsImageGrad)" stroke="rgba(100,200,255,0.2)" stroke-width="1"/>
              <circle cx="100" cy="180" r="10" fill="rgba(56,189,248,0.3)"/>
              <polygon points="95,190 115,170 125,185 140,165 155,190" fill="rgba(168,85,247,0.3)"/>
              <rect x="175" y="160" width="95" height="6" rx="3" fill="rgba(200,220,255,0.15)"/>
              <rect x="175" y="174" width="85" height="6" rx="3" fill="rgba(200,220,255,0.12)"/>
              <rect x="175" y="188" width="75" height="6" rx="3" fill="rgba(200,220,255,0.1)"/>
              <rect x="175" y="202" width="65" height="6" rx="3" fill="rgba(200,220,255,0.08)"/>
              <circle cx="90" cy="35" r="1.5" fill="rgba(56,189,248,0.6)"><animate attributeName="cy" values="35;20;35" dur="3s" repeatCount="indefinite"/></circle>
              <circle cx="280" cy="45" r="2" fill="rgba(168,85,247,0.5)"><animate attributeName="cy" values="45;25;45" dur="4s" repeatCount="indefinite"/></circle>
              <circle cx="50" cy="150" r="1" fill="rgba(56,189,248,0.4)"><animate attributeName="cy" values="150;135;150" dur="3.5s" repeatCount="indefinite"/></circle>
              <circle cx="310" cy="160" r="1.5" fill="rgba(168,85,247,0.4)"><animate attributeName="cy" values="160;140;160" dur="2.8s" repeatCount="indefinite"/></circle>
              <defs>
                <linearGradient id="newsCardGrad" x1="60" y1="50" x2="290" y2="250">
                  <stop offset="0%" stop-color="rgba(15,40,80,0.9)"/>
                  <stop offset="100%" stop-color="rgba(10,25,55,0.95)"/>
                </linearGradient>
                <linearGradient id="newsCardGlow" x1="175" y1="50" x2="175" y2="250">
                  <stop offset="0%" stop-color="rgba(56,189,248,0.3)"/>
                  <stop offset="100%" stop-color="rgba(168,85,247,0.1)"/>
                </linearGradient>
                <linearGradient id="newsImageGrad" x1="80" y1="160" x2="160" y2="220">
                  <stop offset="0%" stop-color="rgba(56,189,248,0.2)"/>
                  <stop offset="100%" stop-color="rgba(168,85,247,0.15)"/>
                </linearGradient>
                <radialGradient id="newsGroundGlow" cx="0.5" cy="0.5" r="0.5">
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

  <main class="news">
    <section class="container news prime-reveal glass-section" id="posts" style="padding-bottom:30px;">
      <div class="section-head">
        <h2 class="js-split-text">{{ __('public.posts.section_title') }}</h2>
        <p>{{ __('public.posts.section_text') }}</p>
      </div>

      <form method="GET" action="{{ route('post') }}" class="post-filters">
        <div class="post-filter search-filter-wrap">
          <input
            type="text"
            name="q"
            value="{{ $q ?? '' }}"
            placeholder="{{ __('public.posts.search_placeholder') }}"
            class="comment-input js-post-search-input"
          />
          <button type="button" class="search-clear-btn js-post-filter-reset" title="{{ __('public.common.clear') }}" style="display: {{ $q ? 'flex' : 'none' }}">
            <i class="fa-solid fa-xmark"></i>
          </button>
        </div>

        <div class="post-filter">
          <select name="category_id" class="form-control">
            <option value="all" {{ empty($categoryId) || $categoryId === 'all' ? 'selected' : '' }}>
              {{ __('public.posts.all_categories') }}
            </option>
            @foreach($categories as $cat)
              <option value="{{ $cat->id }}" {{ (string) ($categoryId ?? '') === (string) $cat->id ? 'selected' : '' }}>
                {{ localized_model_value($cat, 'name') }}
              </option>
            @endforeach
          </select>
        </div>

        <div class="post-filter post-filter-dropdown-wrap">
          <input type="hidden" name="filter" value="{{ $f }}" class="post-filter-hidden-input" data-filter-input>

          <details class="post-filter-dropdown" data-post-filter-dropdown>
            <summary class="post-filter-dropdown-toggle">
              <span>{{ $activeFilterLabel }}</span>
              <i class="fa-solid fa-chevron-down"></i>
            </summary>

            <div class="post-filter-dropdown-menu">
              @foreach($filterOptions as $value => $label)
                <button
                  type="button"
                  class="post-filter-dropdown-item {{ $f === $value ? 'active' : '' }}"
                  data-filter-value="{{ $value }}"
                >
                  <span>{{ $label }}</span>
                  @if($f === $value)
                    <i class="fa-solid fa-check"></i>
                  @endif
                </button>
              @endforeach
            </div>
          </details>
        </div>

      </form>

      <div id="post-results" data-post-results>
        @include('posts.partials.list', [
          'posts' => $posts,
          'likedPostIds' => $likedPostIds,
          'postKindLabels' => $postKindLabels,
        ])
      </div>
    </section>
  </main>

  @push('page_scripts')
    <script src="{{ app_public_asset('temp/js/post-filters.js') }}?v={{ app_asset_version('temp/js/post-filters.js') }}"></script>
  @endpush
</x-loyouts.main>
