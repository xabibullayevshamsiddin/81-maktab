<x-loyouts.main :title="__('public.about.page_title')">
  @php
    $passportFacts = trans('public.about.passport_facts');
    $educationFacts = trans('public.about.education_process_facts');
    $staffFacts = trans('public.about.staffing_facts');
    $resultFacts = trans('public.about.results_facts');
    $facilityFacts = trans('public.about.facilities');
    $facilityDomestic = trans('public.about.facility_domestic');
    $siteCreditsMembers = trans('public.about.site_credits_members');
    $quickFacts = trans('public.about.quick_facts');
    $stats = trans('public.about.stats');
  @endphp

  <section class="news-hero" id="home">
    <div class="container">
      <div class="news-hero-content prime-reveal">
        <span class="badge">{{ __('public.about.badge') }}</span>
        <h1 class="js-split-text">{!! __('public.about.hero_title') !!}</h1>
        <p>{{ __('public.about.hero_text') }}</p>
        <a href="#overview" style="margin-top: 15px;" class="btn btn-prime">
          {{ __('public.about.jump') }}
          <i class="fa-solid fa-arrow-down" style="margin-left: 6px"></i>
        </a>
      </div>
    </div>
  </section>

  <main>
    <section class="container about-overview prime-reveal" id="overview">
      <div class="section-head">
        <h2 class="js-split-text">{{ __('public.about.overview_title') }}</h2>
        <p>{{ __('public.about.overview_text') }}</p>
      </div>

      <div class="about-grid prime-stagger">
        <article class="about-card prime-glow-hover">
          <h3>{{ __('public.about.cards.location_title') }}</h3>
          <p>{{ __('public.about.cards.location_text') }}</p>
        </article>

        <article class="about-card prime-glow-hover">
          <h3>{{ __('public.about.cards.education_title') }}</h3>
          <p>{{ __('public.about.cards.education_text') }}</p>
        </article>

        <article class="about-card prime-glow-hover">
          <h3>{{ __('public.about.cards.staff_title') }}</h3>
          <p>{{ __('public.about.cards.staff_text') }}</p>
        </article>
      </div>

      <div class="glass-section prime-reveal" style="margin-top: 26px">
        <h3 style="margin-bottom: 12px; color: var(--primary)">
          {{ __('public.about.quick_facts_title') }}
        </h3>
        <ul class="fact-list">
          @foreach($quickFacts as $fact)
            <li><strong>{{ $fact['label'] }}:</strong> {{ $fact['value'] }}</li>
          @endforeach
        </ul>
      </div>
    </section>

    <section class="about-stats-section prime-reveal">
      <div class="container about-stats prime-stagger">
        @foreach($stats as $stat)
          <div class="about-stat-item">
            <strong class="num-counter" data-count="{{ preg_replace('/[^0-9]/', '', $stat['value']) }}" data-suffix="{{ preg_replace('/[0-9,\s]/', '', $stat['value']) }}">{{ $stat['value'] }}</strong>
            <span>{{ $stat['label'] }}</span>
          </div>
        @endforeach
      </div>
    </section>

    <section class="container milestone-section">
      <div class="section-head">
        <h2 class="js-split-text">{{ __('public.about.official_title') }}</h2>
        <p>{{ __('public.about.official_text') }}</p>
      </div>

      <div class="about-grid prime-stagger">
        <article class="about-card prime-glow-hover">
          <h3>{{ __('public.about.passport_title') }}</h3>
          <ul class="fact-list">
            @foreach($passportFacts as $fact)
              <li><strong>{{ $fact['label'] }}:</strong> {{ $fact['value'] }}</li>
            @endforeach
          </ul>
        </article>

        <article class="about-card prime-glow-hover">
          <h3>{{ __('public.about.education_process_title') }}</h3>
          <ul class="fact-list">
            @foreach($educationFacts as $fact)
              <li><strong>{{ $fact['label'] }}:</strong> {{ $fact['value'] }}</li>
            @endforeach
          </ul>
        </article>

        <article class="about-card prime-glow-hover">
          <h3>{{ __('public.about.staffing_title') }}</h3>
          <ul class="fact-list">
            @foreach($staffFacts as $fact)
              <li><strong>{{ $fact['label'] }}:</strong> {{ $fact['value'] }}</li>
            @endforeach
          </ul>
        </article>
      </div>

      <div class="glass-section prime-reveal" style="margin-top: 26px">
        <h3 style="margin-bottom: 12px; color: var(--primary)">
          {{ __('public.about.results_title') }}
        </h3>
        <ul class="fact-list">
          @foreach($resultFacts as $fact)
            <li><strong>{{ $fact['label'] }}:</strong> {{ $fact['value'] }}</li>
          @endforeach
        </ul>
      </div>
    </section>

    <section class="container milestone-section" style="padding-top: 0">
      <div class="section-head">
        <h2 class="js-split-text">{{ __('public.about.facilities_title') }}</h2>
        <p>{{ __('public.about.facilities_text') }}</p>
      </div>

      <div class="about-grid prime-stagger">
        @foreach($facilityFacts as $facility)
          <article class="about-card prime-glow-hover">
            <h3>{{ $facility['title'] }}</h3>
            <ul class="fact-list">
              @foreach($facility['items'] as $item)
                <li>{{ $item }}</li>
              @endforeach
            </ul>
          </article>
        @endforeach
      </div>

      <div class="about-grid prime-stagger" style="margin-top: 26px">
        <article class="about-card prime-glow-hover about-card--wide">
          <h3>{{ $facilityDomestic['title'] }}</h3>
          <ul class="fact-list">
            @foreach($facilityDomestic['items'] as $item)
              <li>{{ $item }}</li>
            @endforeach
          </ul>
        </article>
      </div>

      <div class="glass-section site-credits-block prime-reveal" style="margin-top: 26px">
        <h3 style="margin-bottom: 10px; color: var(--primary)">{{ __('public.about.site_credits_title') }}</h3>
        <p class="site-credits-intro">{{ __('public.about.site_credits_intro') }}</p>
        <ul class="site-credits-list">
          @foreach($siteCreditsMembers as $member)
            <li class="site-credits-item">
              <span class="site-credits-name">{{ $member['name'] }}</span>
              <span class="site-credits-date">{{ $member['date'] }}</span>
            </li>
          @endforeach
        </ul>
      </div>
    </section>

    <section class="container about-cta prime-reveal">
      <div class="glass-section about-cta-box">
        <div>
          <h2 class="js-split-text">{{ __('public.about.cta_title') }}</h2>
          <p>{{ __('public.about.cta_text') }}</p>
        </div>
        <a href="{{ route('contact') }}" class="btn btn-prime">
          {{ __('public.about.cta_button') }}
          <i class="fa-solid fa-arrow-right" style="margin-left: 6px"></i>
        </a>
      </div>
    </section>
  </main>

</x-loyouts.main>
