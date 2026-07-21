<x-layouts.main title="81-IDUM">
  <section class="hero" id="home">
    <video autoplay muted loop playsinline class="bg-video">
      <source
        src="{{ app_public_asset('temp/img/video_40mb.mp4') }}"
        type="video/mp4"
      />
    </video>
    <div class="overlay"></div>

	    <div class="container">
	      <div class="card-home">
	        <div class="home-content">
	          <h1 class="hero-title" id="animated-hero">
	            <span class="js-split-text">{{ __('public.home.hero_top') }}</span>
            <strong class="js-split-text">{{ __('public.home.hero_main') }}</strong>
          </h1>
          <p class="hero-text-fade prime-reveal prime-reveal--blur" style="transition-delay: 0.8s;">{{ __('public.home.hero_text') }}</p>
            <div class="home-primary-actions prime-reveal" style="transition-delay: 1s;">
              <a href="{{ route('courses') }}" class="btn">{{ __('public.home.hero_courses_action') }}</a>
              <a href="#news" class="btn btn-outline btn-outline-light">{{ __('public.home.hero_news_action') }}</a>
            </div>
	        </div>
	        <div class="home-btn">
          <a
            href="https://www.instagram.com/81_idum/"
            target="_blank"
            aria-label="Instagram"
          >
            <i class="fa-brands fa-instagram"></i>
          </a>
          <a
            href="https://www.facebook.com/groups/751099325082714"
            target="_blank"
            aria-label="Facebook"
          >
            <i class="fa-brands fa-facebook"></i>
          </a>
          <a
            href="https://t.me/tashabbus81IDUM"
            target="_blank"
            aria-label="Telegram"
          >
            <i class="fa-brands fa-telegram"></i>
          </a>
          <a
            href="https://www.youtube.com/@81-idum"
            target="_blank"
            aria-label="YouTube"
          >
            <i class="fa-brands fa-youtube"></i>
          </a>
        </div>
      </div>
	    </div>
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
          <h2 class="js-split-text">{{ __('public.home.teachers_title') }}</h2>
          <p>{{ __('public.home.teachers_text') }}</p>
          <a href="{{ route('teacher') }}" class="btn btn-prime">{{ __('public.home.teachers_action') }}</a>
        </div>

        @if(isset($featuredTeacher) && $featuredTeacher)
          @php
            $featuredTeacherSubject = localized_model_value($featuredTeacher, 'subject');
            $featuredTeacherMetaLine = $featuredTeacherSubject ?: localized_model_value($featuredTeacher, 'lavozim');
          @endphp
          <article class="teacher-img prime-reveal prime-reveal--scale">
            <img
              src="{{ app_storage_asset($featuredTeacher->image) }}"
              alt="{{ $featuredTeacher->full_name }} profil rasmi"
              loading="lazy"
              decoding="async"
            />
            <h3>{{ $featuredTeacher->full_name }}</h3>
            <p>
              {{ $featuredTeacher->shortBio(180) }}
            </p>
            @if(filled($featuredTeacherMetaLine) || $featuredTeacher->experience_years)
              <p class="profile-muted home-featured-teacher-meta">
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
