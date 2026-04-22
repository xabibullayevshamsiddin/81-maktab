<x-loyouts.main title="{{ __('public.posts.page_title') }}">
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


  <section class="news-hero" id="home">
    <div class="container">
      <div class="news-hero-content prime-reveal">
        <h1 class="js-split-text">{{ __('public.posts.hero_title') }}</h1>
        <p>{{ __('public.posts.hero_text') }}</p>
      </div>
      <a href="#posts" class="btn btn-prime" style="margin-top: 20px">
        {{ __('public.posts.jump') }} <i class="fa-solid fa-arrow-down" style="margin-left: 10px;"></i>
      </a>
    </div>
  </section>

  <main class="news">
    <section class="container news prime-reveal glass-section" id="posts" style="padding-bottom:30px;">
      <div class="section-head">
        <h2 class="js-split-text">{{ __('public.posts.section_title') }}</h2>
        <p>{{ __('public.posts.section_text') }}</p>
      </div>

      <form method="GET" action="{{ route('post') }}" class="post-filters">
        <div class="post-filter">
          <input
            type="text"
            name="q"
            value="{{ $q ?? '' }}"
            placeholder="{{ __('public.posts.search_placeholder') }}"
            class="comment-input"
          />
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
    <script src="{{ app_public_asset('temp/js/post-filters.js') }}?v={{ filemtime(public_path('temp/js/post-filters.js')) }}"></script>
  @endpush
</x-loyouts.main>
