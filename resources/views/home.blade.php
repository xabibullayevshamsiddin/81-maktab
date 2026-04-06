<x-loyouts.main title="81-IDUM">
  <section class="hero" id="home">
    <video autoplay muted loop playsinline class="bg-video">
      <source
        src="{{ app_public_asset('temp/img/PixVerse_V5.6_Image_Text_540P_tiriltirib_ber.mp4') }}"
        type="video/mp4"
      />
    </video>
    <div class="overlay"></div>

    <div class="container">
      <div class="card-home">
        <div class="home-content">
          <h1 class="hero-title">
            <span>{{ __('public.home.hero_top') }}</span>
            <strong>{{ __('public.home.hero_main') }}</strong>
          </h1>
          <p>{{ __('public.home.hero_text') }}</p>
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

  <main>
    <section class="container reveal glass-section" id="about" style="padding-bottom: 50px; margin-top:50px;">
      <div class="section-head">
        <h2>{{ __('public.home.welcome_title') }}</h2>
        <p>{{ __('public.home.welcome_text') }}</p>
      </div>
      <div class="about-modern">
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

    <section class="container news reveal glass-section" id="news" style="margin-top: 50px;padding-bottom:50px;">
      <div
        class="section-head"
        style="display: flex; align-items: end; justify-content: space-between; gap: 16px; flex-wrap: wrap;"
      >
        <div>
          <h2>{{ __('public.home.news_title') }}</h2>
          <p>{{ __('public.home.news_text') }}</p>
        </div>
        <a href="{{ route('post') }}" class="btn btn-sm">{{ __('public.home.news_all') }}</a>
      </div>

      <div class="news-container">
        @php $likedPostIds = $likedPostIds ?? collect(); @endphp
        @forelse($posts as $post)
          @php
            $postTitle = localized_model_value($post, 'title');
            $postShort = localized_model_value($post, 'short_content');
            $postCategory = localized_model_value($post->category, 'name');
            $kindLabel = localized_post_kind_label($post->post_kind ?? 'general');
          @endphp
          <article class="news-card">
            <img
              src="{{ app_storage_asset($post->image) }}"
              alt="{{ $postTitle }}"
              class="js-image-zoom-trigger zoomable-image"
              data-zoom-src="{{ app_storage_asset($post->image) }}"
              loading="lazy"
              decoding="async"
              role="button"
              tabindex="0"
            />

            @if($post->category || $post->hasVideo() || $kindLabel)
              <div style="padding: 0 16px; margin-top: 10px; display:flex; flex-wrap:wrap; gap:8px;">
                @if($post->category)
                  <span class="badge" style="margin-bottom: 0; background: rgba(21, 101, 192, 0.12); border: 1px solid rgba(21, 101, 192, 0.28); color: var(--primary);">
                    {{ $postCategory }}
                  </span>
                @endif
                @if($kindLabel)
                  <span class="badge" style="margin-bottom: 0; background: rgba(21, 101, 192, 0.1); color: var(--primary-2);">{{ $kindLabel }}</span>
                @endif
                @if($post->hasVideo())
                  <span class="badge" style="margin-bottom: 0; background: rgba(220, 38, 38, 0.12); color: #b91c1c;">{{ __('public.common.video') }}</span>
                @endif
              </div>
            @endif

            <h3>{{ $postTitle }}</h3>
            <p>{{ $postShort }}</p>

            <div class="icon-links">
              <div class="icon-link">
                <span class="meta"><i class="fa-regular fa-eye"></i> {{ $post->views }}</span>
                <span class="meta"><i class="fa-regular fa-comment"></i> {{ $post->comments_count }}</span>
                <form action="{{ route('post.like', $post) }}" method="POST" class="js-like-form" style="margin-left: 4px;">
                  @csrf
                  <button class="like-btn {{ $likedPostIds->contains($post->id) ? 'liked' : '' }}" type="submit" aria-label="{{ __('public.posts.like_aria') }}">
                    <i class="{{ $likedPostIds->contains($post->id) ? 'fa-solid' : 'fa-regular' }} fa-heart"></i>
                    <span class="like-count">{{ $post->likes_count }}</span>
                  </button>
                </form>
              </div>
              <div class="icon-link-actions">
                <button
                  type="button"
                  class="btn btn-sm btn-outline share-btn js-share-trigger"
                  data-share-url="{{ route('post.show', $post) }}"
                  data-share-title="{{ $postTitle }}"
                  data-share-text="{{ __('public.home.news_share_text') }}"
                  data-share-success="{{ __('public.home.news_share_success') }}"
                >
                  <i class="fa-solid fa-share-nodes"></i> {{ __('public.common.share') }}
                </button>
                <a href="{{ route('post.show', $post) }}" class="btn btn-sm">{{ __('public.common.details') }}</a>
              </div>
            </div>
          </article>
        @empty
          <p>{{ __('public.home.news_empty') }}</p>
        @endforelse
      </div>
    </section>

    <section class="teachers reveal" id="teachers">
      <div class="container teacher">
        <div class="teacher-content">
          <h2>{{ __('public.home.teachers_title') }}</h2>
          <p>{{ __('public.home.teachers_text') }}</p>
          <a href="{{ route('teacher') }}" class="btn">{{ __('public.home.teachers_action') }}</a>
        </div>

        @if(isset($featuredTeacher) && $featuredTeacher)
          @php
            $featuredTeacherSubject = localized_model_value($featuredTeacher, 'subject');
            $featuredTeacherBio = localized_model_value($featuredTeacher, 'bio');
          @endphp
          <article class="teacher-img">
            <img
              src="{{ $featuredTeacher->image ? app_storage_asset($featuredTeacher->image) : app_public_asset('temp/img/how-to-be-teacher-malaysia-feature.png') }}"
              alt="{{ $featuredTeacher->full_name }} profil rasmi"
              loading="lazy"
              decoding="async"
            />
            <h3>{{ $featuredTeacher->full_name }}</h3>
            <p>
              {{ $featuredTeacherBio ?: ($featuredTeacherSubject . ' fani bo\'yicha tajribali ustoz.') }}
            </p>
            <p class="profile-muted" style="margin-top:8px;">
              {{ $featuredTeacherSubject }}
              @if($featuredTeacher->experience_years)
                В· {{ __('public.common.years_experience', ['count' => $featuredTeacher->experience_years]) }}
              @endif
            </p>
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
        @else
          <article class="teacher-img">
            <img
              src="{{ app_public_asset('temp/img/how-to-be-teacher-malaysia-feature.png') }}"
              alt="{{ __('public.layout.nav.teachers') }}"
              loading="lazy"
              decoding="async"
            />
            <h3>{{ __('public.home.teacher_fallback_title') }}</h3>
            <p>{{ __('public.home.teacher_fallback_text') }}</p>
            <div class="teacher-img-actions">
              <a href="{{ route('teacher') }}" class="btn1">{{ __('public.common.details') }}</a>
              <button
                type="button"
                class="btn btn-outline btn-sm share-btn js-share-trigger"
                data-share-url="{{ route('teacher') }}"
                data-share-title="{{ __('public.layout.nav.teachers') }}"
                data-share-text="{{ __('public.home.teachers_page_share_text') }}"
                data-share-success="{{ __('public.home.teachers_page_share_success') }}"
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
