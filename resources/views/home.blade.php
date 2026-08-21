<x-layouts.main title="81-IDUM">
  <section class="hero" id="home">
    <video autoplay muted loop playsinline class="bg-video">
      <source
        src="{{ app_public_asset('temp/img/video_40mb.mp4') }}"
        type="video/mp4"
      />
    </video>
    <div class="hero-overlay"></div>

    <div class="container">
      <div class="hero-grid">
        <div class="hero-content">
          <div class="hero-badge prime-reveal">
            <span class="hero-badge-pulse"></span>
            <span>81-IDUM · Toshkent shahar</span>
          </div>

          <h1 class="hero-title hero-title-3d" id="animated-hero">
            <em class="hero-title-top js-hero-3d-top">{{ __('public.home.hero_top') }}</em>
            <strong class="hero-title-main js-hero-3d-main">{{ __('public.home.hero_main') }}</strong>
          </h1>

          <p class="hero-description prime-reveal prime-reveal--blur" style="transition-delay: 0.3s;">
            {{ __('public.home.hero_text') }}
          </p>

          <div class="hero-actions prime-reveal" style="transition-delay: 0.5s;">
            <a href="{{ route('courses') }}" class="btn-hero-primary">
              <span>{{ __('public.home.hero_courses_action') }}</span>
              <i class="fa-solid fa-arrow-right"></i>
            </a>
            <a href="#news" class="btn-hero-secondary">
              <i class="fa-regular fa-newspaper"></i>
              <span>{{ __('public.home.hero_news_action') }}</span>
            </a>
          </div>
        </div>

        <div class="hero-side-card prime-reveal" style="transition-delay: 0.6s;">
          <div class="hero-glass-panel">
            <div class="hero-panel-head">
              <div class="hero-panel-icon"><i class="fa-solid fa-graduation-cap"></i></div>
              <div>
                <div class="hero-panel-title">81-sonli IDUM</div>
                <div class="hero-panel-sub">Zamonaviy ta'lim maktabi</div>
              </div>
            </div>

            <div class="hero-panel-stats">
              <div class="hero-mini-stat">
                <span class="stat-val js-counter" data-counter="2097" data-suffix="+">0</span>
                <span class="stat-lbl">O'quvchi</span>
              </div>
              <div class="hero-mini-stat">
                <span class="stat-val js-counter" data-counter="90" data-suffix="+">0</span>
                <span class="stat-lbl">Pedagog</span>
              </div>
              <div class="hero-mini-stat">
                <span class="stat-val js-counter" data-counter="1963">0</span>
                <span class="stat-lbl">Yildan buyon</span>
              </div>
            </div>

            <div class="hero-social-strip">
              <span class="hero-social-lbl">Ijtimoiy tarmoqlar:</span>
              <div class="hero-social-links">
                <a href="https://www.instagram.com/81_idum/" target="_blank" aria-label="Instagram"><i class="fa-brands fa-instagram"></i></a>
                <a href="https://www.facebook.com/groups/751099325082714" target="_blank" aria-label="Facebook"><i class="fa-brands fa-facebook"></i></a>
                <a href="https://t.me/tashabbus81IDUM" target="_blank" aria-label="Telegram"><i class="fa-brands fa-telegram"></i></a>
                <a href="https://www.youtube.com/@81-idum" target="_blank" aria-label="YouTube"><i class="fa-brands fa-youtube"></i></a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <script>
      (function() {
        // ── 1. Counter IntersectionObserver ──
        function initCounters() {
          var counters = document.querySelectorAll('.js-counter');
          if (!counters.length) return;

          var observer = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
              if (entry.isIntersecting) {
                var el = entry.target;
                var target = parseInt(el.getAttribute('data-counter'), 10) || 0;
                var suffix = el.getAttribute('data-suffix') || '';
                var duration = 1800;
                var startTime = null;

                function animateCount(timestamp) {
                  if (!startTime) startTime = timestamp;
                  var progress = Math.min((timestamp - startTime) / duration, 1);
                  var easeProgress = 1 - Math.pow(1 - progress, 3);
                  var current = Math.floor(easeProgress * target);
                  el.textContent = current.toLocaleString() + suffix;
                  if (progress < 1) {
                    requestAnimationFrame(animateCount);
                  } else {
                    el.textContent = target.toLocaleString() + suffix;
                  }
                }
                requestAnimationFrame(animateCount);
                observer.unobserve(el);
              }
            });
          }, { threshold: 0.2 });

          counters.forEach(function(c) { observer.observe(c); });
        }

        // ── 2. Home Hero Scroll Parallax ──
        function initHeroParallax() {
          var heroSection = document.getElementById('home');
          if (!heroSection) return;

          var heroContent = heroSection.querySelector('.hero-content');
          var sideCard = heroSection.querySelector('.hero-side-card');
          var bgVideo = heroSection.querySelector('.bg-video');




          // Scroll Parallax Depth
          window.addEventListener('scroll', function() {
            var scrollY = window.pageYOffset || document.documentElement.scrollTop;
            if (scrollY < heroSection.offsetHeight + 100) {
              var speedContent = scrollY * 0.18;
              var speedSideCard = scrollY * 0.28;
              if (heroContent) heroContent.style.translate = '0px ' + speedContent + 'px';
              if (sideCard) sideCard.style.translate = '0px ' + speedSideCard + 'px';
              if (bgVideo) bgVideo.style.transform = 'translateY(' + (scrollY * 0.1) + 'px) scale(1.04)';
            }
          }, { passive: true });
        }

        if (document.readyState === 'loading') {
          document.addEventListener('DOMContentLoaded', function() {
            initCounters();
            initHeroParallax();
          });
        } else {
          initCounters();
          initHeroParallax();
        }
      })();
    </script>
  </section>

    <div class="site-section-nav-wrap">
      <div class="container">
        <nav class="site-section-nav" data-section-nav aria-label="{{ __('public.home.section_nav_aria') }}">
          <a href="#about" class="site-section-nav-link is-active">{{ __('public.home.section_nav_about') }}</a>
          <a href="#news" class="site-section-nav-link">{{ __('public.home.section_nav_news') }}</a>
          <a href="#teachers" class="site-section-nav-link">{{ __('public.home.section_nav_teachers') }}</a>
        </nav>
      </div>
    </div>
	
	  <main>
    <section class="container prime-reveal glass-section home-about-section" id="about">
      <div class="section-head">
        <h2 class="js-split-text">{{ __('public.home.welcome_title') }}</h2>
        <p>{{ __('public.home.welcome_text') }}</p>
      </div>
      <div class="about-modern prime-stagger">
        <article class="about-card">
          <h3>{{ __('public.home.students_title') }}</h3>
          <p>{{ __('public.home.students_text') }}</p>
          <a href="{{ route('about') }}" class="btn btn-sm">{{ __('public.home.students_action') }}</a>
        </article>
        <article class="about-card">
          <h3>{{ __('public.home.pedagogues_title') }}</h3>
          <p>{{ __('public.home.pedagogues_text') }}</p>
          <a href="{{ route('teacher') }}" class="btn btn-sm">{{ __('public.home.pedagogues_action') }}</a>
        </article>
        <article class="about-highlight">
          <span class="badge">{{ __('public.home.highlight_badge') }}</span>
          <h3>{{ __('public.home.highlight_title') }}</h3>
          <p>{{ __('public.home.highlight_text') }}</p>
        </article>
      </div>
    </section>

    <section class="container news prime-reveal glass-section home-news-section" id="news">
      <div class="section-head home-news-head">
        <div>
          <h2 class="js-split-text">{{ __('public.home.news_title') }}</h2>
          <p>{{ __('public.home.news_text') }}</p>
        </div>
        <a href="{{ route('post') }}" class="btn btn-sm">{{ __('public.home.news_all') }}</a>
      </div>

      <div class="news-container prime-stagger">
        @php
          $likedPostIds = $likedPostIds ?? collect();
          $bookmarkedPostIds = $bookmarkedPostIds ?? collect();
        @endphp
        @forelse($posts as $post)
          @include('posts.partials.post-card', [
            'post' => $post,
            'likedPostIds' => $likedPostIds,
            'bookmarkedPostIds' => $bookmarkedPostIds,
            'shareText' => __('public.home.news_share_text'),
            'shareSuccess' => __('public.home.news_share_success'),
          ])
        @empty
          <p>{{ __('public.home.news_empty') }}</p>
        @endforelse
      </div>
    </section>

    <section class="teachers prime-reveal" id="teachers">
      <div class="container teacher">
        <div class="teacher-content">
          <div class="home-teacher-eyebrow">
            <i class="fa-solid fa-award"></i> {{ __('public.home.teachers_eyebrow') }}
          </div>
          <h2 class="js-split-text">{{ __('public.home.teachers_title') }}</h2>
          <p>{{ __('public.home.teachers_text') }}</p>

          <div class="home-teacher-stats-grid">
            <div class="home-teacher-stat-card">
              <div class="stat-icon"><i class="fa-solid fa-user-graduate"></i></div>
              <div class="stat-info">
                <span class="stat-num">{{ __('public.home.teachers_stat_1_num') }}</span>
                <span class="stat-lbl">{{ __('public.home.teachers_stat_1_label') }}</span>
              </div>
            </div>
            <div class="home-teacher-stat-card">
              <div class="stat-icon"><i class="fa-solid fa-certificate"></i></div>
              <div class="stat-info">
                <span class="stat-num">{{ __('public.home.teachers_stat_2_num') }}</span>
                <span class="stat-lbl">{{ __('public.home.teachers_stat_2_label') }}</span>
              </div>
            </div>
            <div class="home-teacher-stat-card">
              <div class="stat-icon"><i class="fa-solid fa-star"></i></div>
              <div class="stat-info">
                <span class="stat-num">{{ __('public.home.teachers_stat_3_num') }}</span>
                <span class="stat-lbl">{{ __('public.home.teachers_stat_3_label') }}</span>
              </div>
            </div>
            <div class="home-teacher-stat-card">
              <div class="stat-icon"><i class="fa-solid fa-earth-americas"></i></div>
              <div class="stat-info">
                <span class="stat-num">{{ __('public.home.teachers_stat_4_num') }}</span>
                <span class="stat-lbl">{{ __('public.home.teachers_stat_4_label') }}</span>
              </div>
            </div>
          </div>

          <div class="home-teacher-cta-group">
            <a href="{{ route('teacher') }}" class="btn-island-primary">
              <span>{{ __('public.home.teachers_action') }}</span>
              <div class="btn-island-icon">
                <i class="fa-solid fa-arrow-right"></i>
              </div>
            </a>
          </div>
        </div>

        @if(isset($featuredTeacher) && $featuredTeacher)
          @php
            $featuredTeacherSubject = localized_model_value($featuredTeacher, 'subject');
            $featuredTeacherMetaLine = $featuredTeacherSubject ?: localized_model_value($featuredTeacher, 'lavozim');
          @endphp
          <article class="teacher-img prime-reveal prime-reveal--scale">
            <img
              src="{{ $featuredTeacher->image ? app_storage_asset($featuredTeacher->image) : app_public_asset('temp/img/ChatGPT Image Jul 5, 2026, 01_38_09 AM.png') }}"
              alt="{{ $featuredTeacher->full_name }} profil rasmi"
              loading="lazy"
              decoding="async"
            />
            <h3>{{ $featuredTeacher->full_name }}</h3>
            @php
              $featuredTeacherBio = localized_model_value($featuredTeacher, 'bio');
            @endphp
            @if(filled($featuredTeacherBio))
              <p class="teacher-desc">
                {{ \Illuminate\Support\Str::limit(trim($featuredTeacherBio), 160) }}
              </p>
            @endif
            @if(filled($featuredTeacherMetaLine) || $featuredTeacher->experience_years)
              <p class="profile-muted home-featured-teacher-meta" style="margin-top: 6px; font-weight: 600;">
                @if(filled($featuredTeacherMetaLine))
                  {{ $featuredTeacherMetaLine }}
                @endif
                @if(filled($featuredTeacherMetaLine) && $featuredTeacher->experience_years)
                  ·
                @endif
                @if($featuredTeacher->experience_years)
                  {{ __('public.common.years_experience', ['count' => $featuredTeacher->experience_years]) }}
                @endif
              </p>
            @endif
            <div class="teacher-img-actions">
              <a href="{{ route('teacher.show', $featuredTeacher) }}" class="btn1">{{ __('public.teachers.about_button') }}</a>
              <button
                type="button"
                class="btn btn-outline btn-sm share-btn js-share-trigger"
                data-share-url="{{ route('teacher.show', $featuredTeacher) }}"
                data-share-title="{{ $featuredTeacher->full_name }}"
                data-share-text="{{ __('public.home.teacher_share_text') }}"
                data-share-success="{{ __('public.home.teacher_share_success') }}"
              >
                <i class="fa-solid fa-share-nodes"></i> {{ __('public.common.share') }}
              </button>
            </div>
          </article>
        @endif
      </div>
    </section>
  </main>

</x-loyouts.main>
