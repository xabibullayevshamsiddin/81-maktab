<x-layouts.main :title="__('public.about.page_title')">
  @php
    $passportFacts = trans('public.about.passport_facts');
    $educationFacts = trans('public.about.education_process_facts');
    $staffFacts = trans('public.about.staffing_facts');
    $resultFacts = trans('public.about.results_facts');
    $facilityFacts = trans('public.about.facilities');
    $facilityDomestic = trans('public.about.facility_domestic');
    $siteCreditsMembers = trans('public.about.site_credits_members');
    $siteCreditsContributors = trans('public.about.site_credits_contributors');
    $quickFacts = trans('public.about.quick_facts');
    $stats = trans('public.about.stats');
  @endphp

  <section class="news-hero about-hero-v2" id="home">
    <div class="container">
      <div class="about-hero-grid prime-reveal">
        <div class="about-hero-text">
          <span class="badge">{{ __('public.about.badge') }}</span>
          <h1 class="js-split-text">{!! __('public.about.hero_title') !!}</h1>
          <p>{{ __('public.about.hero_text') }}</p>
          <a href="#overview" style="margin-top: 20px;" class="btn btn-prime about-hero-cta">
            {{ __('public.about.jump') }}
            <i class="fa-solid fa-arrow-right" style="margin-left: 8px"></i>
          </a>
        </div>
        <div class="about-hero-visual" id="aboutHeroTilt">
          <div class="about-hero-3d-scene">
            <!-- 3D Glowing Orbit Rings -->
            <div class="hero-3d-orbit hero-3d-orbit--1"></div>
            <div class="hero-3d-orbit hero-3d-orbit--2"></div>

            <!-- School Building SVG Container -->
            <div class="school-building">
              <!-- Holographic Laser Scanner -->
              <div class="hero-laser-beam"></div>

              <svg viewBox="0 0 400 350" fill="none" xmlns="http://www.w3.org/2000/svg" class="school-svg">
                <!-- Ground glow -->
                <ellipse cx="200" cy="310" rx="160" ry="20" fill="url(#groundGlow)" opacity="0.6"/>
                <!-- Building base -->
                <rect x="60" y="120" width="280" height="190" rx="8" fill="url(#buildingGrad)" stroke="rgba(120,200,255,0.3)" stroke-width="1.5"/>
                <!-- Building shadow overlay -->
                <rect x="60" y="120" width="280" height="190" rx="8" fill="url(#buildingShadow)" opacity="0.4"/>
                <!-- Roof -->
                <polygon points="40,125 200,40 360,125" fill="url(#roofGrad)" stroke="rgba(120,200,255,0.4)" stroke-width="1.5"/>
                <polygon points="40,125 200,40 360,125" fill="url(#roofGlow)" opacity="0.3"/>
                <!-- Clock tower -->
                <rect x="175" y="50" width="50" height="75" rx="4" fill="url(#towerGrad)" stroke="rgba(120,200,255,0.3)" stroke-width="1"/>
                <circle cx="200" cy="78" r="16" fill="rgba(7,17,31,0.8)" stroke="rgba(100,200,255,0.5)" stroke-width="1.5"/>
                <line x1="200" y1="78" x2="200" y2="68" stroke="rgba(100,220,255,0.8)" stroke-width="1.5" stroke-linecap="round">
                  <animateTransform attributeName="transform" type="rotate" from="0 200 78" to="360 200 78" dur="12s" repeatCount="indefinite"/>
                </line>
                <line x1="200" y1="78" x2="208" y2="82" stroke="rgba(100,220,255,0.8)" stroke-width="1.5" stroke-linecap="round"/>
                <!-- Flag -->
                <line x1="200" y1="38" x2="200" y2="18" stroke="rgba(200,220,255,0.6)" stroke-width="1.5"/>
                <polygon points="200,18 220,24 200,30" fill="rgba(56,189,248,0.7)"/>
                <!-- Windows row 1 -->
                <rect x="85" y="145" width="35" height="35" rx="4" fill="url(#windowGrad)" stroke="rgba(100,200,255,0.3)" stroke-width="1"/>
                <rect x="140" y="145" width="35" height="35" rx="4" fill="url(#windowGrad)" stroke="rgba(100,200,255,0.3)" stroke-width="1"/>
                <rect x="225" y="145" width="35" height="35" rx="4" fill="url(#windowGrad)" stroke="rgba(100,200,255,0.3)" stroke-width="1"/>
                <rect x="280" y="145" width="35" height="35" rx="4" fill="url(#windowGrad)" stroke="rgba(100,200,255,0.3)" stroke-width="1"/>
                <!-- Windows row 2 -->
                <rect x="85" y="200" width="35" height="35" rx="4" fill="url(#windowGrad2)" stroke="rgba(100,200,255,0.3)" stroke-width="1"/>
                <rect x="140" y="200" width="35" height="35" rx="4" fill="url(#windowGrad)" stroke="rgba(100,200,255,0.3)" stroke-width="1"/>
                <rect x="225" y="200" width="35" height="35" rx="4" fill="url(#windowGrad2)" stroke="rgba(100,200,255,0.3)" stroke-width="1"/>
                <rect x="280" y="200" width="35" height="35" rx="4" fill="url(#windowGrad)" stroke="rgba(100,200,255,0.3)" stroke-width="1"/>
                <!-- Windows row 3 -->
                <rect x="85" y="255" width="35" height="30" rx="4" fill="url(#windowGrad2)" stroke="rgba(100,200,255,0.3)" stroke-width="1"/>
                <rect x="140" y="255" width="35" height="30" rx="4" fill="url(#windowGrad)" stroke="rgba(100,200,255,0.3)" stroke-width="1"/>
                <rect x="225" y="255" width="35" height="30" rx="4" fill="url(#windowGrad2)" stroke="rgba(100,200,255,0.3)" stroke-width="1"/>
                <rect x="280" y="255" width="35" height="30" rx="4" fill="url(#windowGrad)" stroke="rgba(100,200,255,0.3)" stroke-width="1"/>
                <!-- Door -->
                <rect x="180" y="260" width="40" height="50" rx="20" fill="url(#doorGrad)" stroke="rgba(100,200,255,0.4)" stroke-width="1.5"/>
                <circle cx="212" cy="288" r="3" fill="rgba(200,220,255,0.7)"/>
                <!-- Steps -->
                <rect x="170" y="305" width="60" height="5" rx="2" fill="rgba(100,180,255,0.2)"/>
                <rect x="165" y="310" width="70" height="5" rx="2" fill="rgba(100,180,255,0.15)"/>
                <!-- Decorative columns -->
                <rect x="168" y="130" width="6" height="180" rx="3" fill="rgba(100,180,255,0.15)"/>
                <rect x="226" y="130" width="6" height="180" rx="3" fill="rgba(100,180,255,0.15)"/>
                <!-- Glass reflections -->
                <line x1="85" y1="145" x2="100" y2="160" stroke="rgba(200,230,255,0.15)" stroke-width="1"/>
                <line x1="140" y1="145" x2="155" y2="160" stroke="rgba(200,230,255,0.15)" stroke-width="1"/>
                <line x1="225" y1="145" x2="240" y2="160" stroke="rgba(200,230,255,0.15)" stroke-width="1"/>
                <line x1="280" y1="145" x2="295" y2="160" stroke="rgba(200,230,255,0.15)" stroke-width="1"/>
                <!-- Floating particles -->
                <circle cx="80" cy="80" r="2" fill="rgba(56,189,248,0.6)">
                  <animate attributeName="cy" values="80;60;80" dur="3s" repeatCount="indefinite"/>
                </circle>
                <circle cx="320" cy="100" r="1.5" fill="rgba(168,85,247,0.6)">
                  <animate attributeName="cy" values="100;75;100" dur="4s" repeatCount="indefinite"/>
                </circle>
                <circle cx="50" cy="200" r="1.5" fill="rgba(56,189,248,0.4)">
                  <animate attributeName="cy" values="200;180;200" dur="3.5s" repeatCount="indefinite"/>
                </circle>
                <circle cx="350" cy="220" r="2" fill="rgba(168,85,247,0.5)">
                  <animate attributeName="cy" values="220;195;220" dur="2.8s" repeatCount="indefinite"/>
                </circle>
                <!-- Gradients -->
                <defs>
                  <linearGradient id="buildingGrad" x1="60" y1="120" x2="340" y2="310">
                    <stop offset="0%" stop-color="rgba(15,40,80,0.9)"/>
                    <stop offset="100%" stop-color="rgba(10,25,55,0.95)"/>
                  </linearGradient>
                  <linearGradient id="buildingShadow" x1="200" y1="120" x2="200" y2="310">
                    <stop offset="0%" stop-color="rgba(0,0,0,0)"/>
                    <stop offset="100%" stop-color="rgba(0,0,0,0.3)"/>
                  </linearGradient>
                  <linearGradient id="roofGrad" x1="200" y1="40" x2="200" y2="125">
                    <stop offset="0%" stop-color="rgba(30,80,160,0.8)"/>
                    <stop offset="100%" stop-color="rgba(15,40,80,0.9)"/>
                  </linearGradient>
                  <linearGradient id="roofGlow" x1="200" y1="40" x2="200" y2="125">
                    <stop offset="0%" stop-color="rgba(56,189,248,0.4)"/>
                    <stop offset="100%" stop-color="rgba(168,85,247,0.2)"/>
                  </linearGradient>
                  <linearGradient id="towerGrad" x1="175" y1="50" x2="225" y2="125">
                    <stop offset="0%" stop-color="rgba(20,50,100,0.9)"/>
                    <stop offset="100%" stop-color="rgba(10,30,60,0.95)"/>
                  </linearGradient>
                  <linearGradient id="windowGrad" x1="0" y1="0" x2="1" y2="1">
                    <stop offset="0%" stop-color="rgba(56,189,248,0.15)"/>
                    <stop offset="100%" stop-color="rgba(168,85,247,0.1)"/>
                  </linearGradient>
                  <linearGradient id="windowGrad2" x1="0" y1="0" x2="1" y2="1">
                    <stop offset="0%" stop-color="rgba(168,85,247,0.15)"/>
                    <stop offset="100%" stop-color="rgba(56,189,248,0.1)"/>
                  </linearGradient>
                  <linearGradient id="doorGrad" x1="200" y1="260" x2="200" y2="310">
                    <stop offset="0%" stop-color="rgba(56,189,248,0.3)"/>
                    <stop offset="100%" stop-color="rgba(10,30,60,0.8)"/>
                  </linearGradient>
                  <radialGradient id="groundGlow" cx="0.5" cy="0.5" r="0.5">
                    <stop offset="0%" stop-color="rgba(56,189,248,0.3)"/>
                    <stop offset="100%" stop-color="rgba(168,85,247,0)"/>
                  </radialGradient>
                </defs>
              </svg>
            </div>

            <!-- Floating 3D Glass Badges -->
            <div class="hero-glass-badge hero-glass-badge--top-left">
              <div class="hgb-icon"><i class="fa-solid fa-graduation-cap"></i></div>
              <div class="hgb-content">
                <span class="hgb-title">81-IDUM</span>
                <span class="hgb-sub"><span class="hgb-dot"></span> Davlat Maktabi</span>
              </div>
            </div>

            <div class="hero-glass-badge hero-glass-badge--top-right">
              <div class="hgb-icon hgb-icon--gold"><i class="fa-solid fa-trophy"></i></div>
              <div class="hgb-content">
                <span class="hgb-title">Top 100</span>
                <span class="hgb-sub">Nufuzli Maktablar</span>
              </div>
            </div>

            <div class="hero-glass-badge hero-glass-badge--bottom-right">
              <div class="hgb-icon hgb-icon--cyan"><i class="fa-solid fa-bolt"></i></div>
              <div class="hgb-content">
                <span class="hgb-title">1963-yildan</span>
                <span class="hgb-sub">Sifatli Ta'lim</span>
              </div>
            </div>

            <!-- Ambient Glow -->
            <div class="about-hero-glow"></div>
          </div>
        </div>
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
      <div class="container about-stats prime-stagger" id="about-stats-grid">
        @foreach($stats as $stat)
          <div class="about-stat-item">
            <strong class="num-counter js-counter" data-counter="{{ preg_replace('/[^0-9]/', '', $stat['value']) }}" data-suffix="{{ preg_replace('/[0-9,\s]/', '', $stat['value']) }}">0</strong>
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
            @php
              $isLeadAuthor = str_contains($member['name'], 'Shamsiddin') || str_contains($member['name'], 'Шамсиддин');
            @endphp
            <li class="site-credits-item {{ $isLeadAuthor ? 'site-credits-item--lead' : '' }}">
              <span class="site-credits-name">
                <span class="{{ $isLeadAuthor ? 'site-credits-name--lead' : '' }}">{{ $member['name'] }}</span>
                @if($isLeadAuthor)
                  <sup class="site-credits-exponent"><i class="fa-solid fa-code"></i> Leader Developer</sup>
                @endif
              </span>
              <span class="site-credits-date">{{ $member['meta'] ?? $member['date'] ?? '' }}</span>
            </li>
          @endforeach
        </ul>
        @if(is_array($siteCreditsContributors) && count($siteCreditsContributors) > 0)
          <h4 style="margin: 18px 0 10px; color: var(--primary)">{{ __('public.about.site_credits_contributors_title') }}</h4>
          <ul class="site-credits-list">
            @foreach($siteCreditsContributors as $member)
              <li class="site-credits-item">
                <span class="site-credits-name">{{ $member['name'] }}</span>
                <span class="site-credits-date">{{ $member['meta'] ?? $member['date'] ?? '' }}</span>
              </li>
            @endforeach
          </ul>
        @endif
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

  <script>
  document.addEventListener('DOMContentLoaded', function () {
    // ── Premium Counter Animation ──
    (function() {
      var statsGrid = document.getElementById('about-stats-grid');
      if (!statsGrid) return;
      var counters = statsGrid.querySelectorAll('.js-counter');
      if (!counters.length) return;

      function animateCounter(el, delay) {
        var target = parseInt(el.getAttribute('data-counter'), 10) || 0;
        var suffix = el.getAttribute('data-suffix') || '';
        var duration = 2200;
        var startTime = null;

        el.style.opacity = '0';
        el.style.transform = 'translateY(12px) scale(0.8)';
        el.style.filter = 'blur(6px)';
        el.style.transition = 'none';

        setTimeout(function() {
          el.style.transition = 'opacity 0.5s ease, transform 0.5s cubic-bezier(0.34, 1.56, 0.64, 1), filter 0.5s ease';
          el.style.opacity = '1';
          el.style.transform = 'translateY(0) scale(1)';
          el.style.filter = 'blur(0)';

          function animateCount(timestamp) {
            if (!startTime) startTime = timestamp;
            var elapsed = timestamp - startTime;
            var progress = Math.min(elapsed / duration, 1);

            var value;
            if (progress < 0.6) {
              value = Math.floor(Math.random() * target * 1.2);
            } else if (progress < 0.85) {
              var ease = 1 - Math.pow(1 - (progress - 0.6) / 0.25, 3);
              value = Math.floor(ease * target + (1 - ease) * target * 0.85 * Math.random());
            } else {
              var snapEase = 1 - Math.pow(1 - (progress - 0.85) / 0.15, 4);
              value = Math.floor(target * 0.95 + snapEase * target * 0.05);
            }

            el.textContent = value.toLocaleString() + suffix;

            if (progress < 1) {
              requestAnimationFrame(animateCount);
            } else {
              el.textContent = target.toLocaleString() + suffix;
              el.style.transition = 'transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1), text-shadow 0.4s ease';
              el.style.transform = 'scale(1.15)';
              el.style.textShadow = '0 0 20px rgba(96, 165, 250, 0.8), 0 0 40px rgba(96, 165, 250, 0.4)';
              setTimeout(function() {
                el.style.transform = 'scale(1)';
                el.style.textShadow = 'none';
              }, 350);
            }
          }
          requestAnimationFrame(animateCount);
        }, delay);
      }

      var observer = new IntersectionObserver(function(entries) {
        entries.forEach(function(entry) {
          if (entry.isIntersecting) {
            counters.forEach(function(c, i) {
              animateCounter(c, i * 250);
            });
            observer.unobserve(entry.target);
          }
        });
      }, { threshold: 0.2 });

      observer.observe(statsGrid);
    })();

    var tiltContainer = document.getElementById('aboutHeroTilt');
    if (!tiltContainer) return;

    var scene = tiltContainer.querySelector('.about-hero-3d-scene');
    var badges = tiltContainer.querySelectorAll('.hero-glass-badge');

    tiltContainer.addEventListener('mousemove', function (e) {
      var rect = tiltContainer.getBoundingClientRect();
      var x = e.clientX - rect.left - rect.width / 2;
      var y = e.clientY - rect.top - rect.height / 2;

      var rotateX = (-y / (rect.height / 2)) * 14;
      var rotateY = (x / (rect.width / 2)) * 14;

      if (scene) {
        scene.style.transform = 'rotateX(' + rotateX.toFixed(2) + 'deg) rotateY(' + rotateY.toFixed(2) + 'deg)';
      }

      badges.forEach(function (badge, idx) {
        var depth = (idx + 1) * 12;
        badge.style.transform = 'translateZ(' + depth + 'px) translateY(' + (rotateX * 0.4) + 'px)';
      });
    });

    tiltContainer.addEventListener('mouseleave', function () {
      if (scene) {
        scene.style.transform = 'rotateX(0deg) rotateY(0deg)';
      }
      badges.forEach(function (badge) {
        badge.style.transform = 'translateZ(0px) translateY(0px)';
      });
    });
  });
  </script>

</x-layouts.main>
