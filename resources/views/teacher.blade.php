<x-loyouts.main title="{{ __('public.teachers.page_title') }}">
  <section class="news-hero" id="home">
    <div class="container">
      <div class="news-hero-content reveal">
          <span class="badge">{{ __('public.teachers.badge') }}</span>
          <h1>{{ __('public.teachers.hero_title') }}</h1>
          <p>{{ __('public.teachers.hero_text') }}</p>
          <a href="#teachers-list" class="btn"
            >{{ __('public.teachers.hero_button') }}
            <i class="fa-solid fa-arrow-down" style="margin-left: 6px"></i
          ></a>
      </div>
    </div>
  </section>

    <main>
      <section class="container teachers-section" id="teachers-list">
        <div class="section-head">
          <h2>{{ __('public.teachers.list_title') }}</h2>
          <p>{{ __('public.teachers.list_text') }}</p>
        </div>

        <div class="teachers-grid">
          @forelse($teachers as $teacher)
            @php
              $teacherSubject = localized_model_value($teacher, 'subject');
              $teacherBio = localized_model_value($teacher, 'bio');
              $teacherAchievements = localized_model_value($teacher, 'achievements');
              $teacherAchievementPreview = \Illuminate\Support\Str::limit(trim((string) strtok($teacherAchievements, "\n")), 100);
            @endphp
            <article class="teacher-card reveal">
              <div class="teacher-photo-wrap">
                <img
                  src="{{ $teacher->image ? app_storage_asset($teacher->image) : app_public_asset('temp/img/how-to-be-teacher-malaysia-feature.png') }}"
                  alt="{{ $teacher->full_name }} profil rasmi"
                  class="teacher-photo"
                  loading="lazy"
                  decoding="async"
                />
              </div>
              <div class="teacher-top">
                <div>
                  <h3>{{ $teacher->full_name }}</h3>
                  <p class="teacher-role">{{ $teacherSubject }}</p>
                </div>
              </div>
              <p class="teacher-desc">
                {{ $teacherBio ?: __('public.teachers.fallback_bio') }}
              </p>
              <ul class="teacher-meta">
                <li><i class="fa-solid fa-award"></i> {{ __('public.common.years_experience', ['count' => $teacher->experience_years]) }}</li>
                <li><i class="fa-solid fa-users"></i> {{ $teacher->grades ?: __('public.common.all_grades') }}</li>
              </ul>
              @if(filled($teacherAchievements))
                <p class="teacher-achievements-preview"><i class="fa-solid fa-trophy"></i> {{ $teacherAchievementPreview }}</p>
              @endif
              <div class="teacher-actions">
                @php $likedTeacherIds = $likedTeacherIds ?? collect(); @endphp
                @auth
                  <form action="{{ route('teacher.like', $teacher) }}" method="POST" class="js-like-form" style="display:inline;">
                    @csrf
                    <button class="like-btn {{ $likedTeacherIds->contains($teacher->id) ? 'liked' : '' }}" type="submit" aria-label="{{ __('public.posts.like_aria') }}">
                      <i class="{{ $likedTeacherIds->contains($teacher->id) ? 'fa-solid' : 'fa-regular' }} fa-heart"></i>
                      <span class="like-count">{{ $teacher->likes_count ?? 0 }}</span>
                    </button>
                  </form>
                @endauth
                <button
                  type="button"
                  class="btn btn-sm btn-outline share-btn js-share-trigger"
                  data-share-url="{{ route('teacher.show', $teacher) }}"
                  data-share-title="{{ $teacher->full_name }}"
                  data-share-text="{{ __('public.teachers.share_text') }}"
                  data-share-success="{{ __('public.teachers.share_success') }}"
                >
                  <i class="fa-solid fa-share-nodes"></i> {{ __('public.common.share') }}
                </button>
                <a href="{{ route('teacher.show', $teacher) }}" class="btn btn-sm">{{ __('public.common.details') }}</a>
              </div>
            </article>
          @empty
            <p>{{ __('public.teachers.empty') }}</p>
          @endforelse
        </div>

        @if($teachers->hasPages())
          <div class="news-pagination" style="margin-top: 28px;">
            @if ($teachers->onFirstPage())
              <span class="btn btn-sm btn-outline" aria-disabled="true">{{ __('public.posts.previous') }}</span>
            @else
              <a class="btn btn-sm btn-outline" href="{{ $teachers->previousPageUrl() }}">{{ __('public.posts.previous') }}</a>
            @endif

            <span class="news-page-info">
              {{ $teachers->currentPage() }} / {{ $teachers->lastPage() }}
            </span>

            @if ($teachers->hasMorePages())
              <a class="btn btn-sm" href="{{ $teachers->nextPageUrl() }}">{{ __('public.posts.next') }}</a>
            @else
              <span class="btn btn-sm" aria-disabled="true">{{ __('public.posts.next') }}</span>
            @endif
          </div>
        @endif
      </section>

      <section class="teaching-approach">
        <div class="container approach-grid">
          <article class="approach-card reveal">
            <h3>{{ __('public.teachers.approach_title') }}</h3>
            <p>{{ __('public.teachers.approach_text') }}</p>
            <ul>
              <li><i class="fa-solid fa-check"></i> {{ __('public.teachers.approach_item_1') }}</li>
              <li><i class="fa-solid fa-check"></i> {{ __('public.teachers.approach_item_2') }}</li>
              <li><i class="fa-solid fa-check"></i> {{ __('public.teachers.approach_item_3') }}</li>
            </ul>
          </article>

          <article class="approach-image-card reveal">
            <img
              src="{{ app_public_asset('temp/img/how-to-be-teacher-malaysia-feature.png') }}"
              alt="{{ __('public.layout.nav.teachers') }}"
              loading="lazy"
              decoding="async"
            />
            <div class="approach-caption">
              <h3>{{ __('public.teachers.approach_caption_title') }}</h3>
              <p>{{ __('public.teachers.approach_caption_text') }}</p>
            </div>
          </article>
        </div>
      </section>

      <section class="teachers-stats-section">
        <div class="container teachers-stats">
          <div class="teachers-stat-item reveal">
            <strong data-target="40" class="stat-num">0</strong>
            <span>{{ __('public.teachers.stat_1') }}</span>
          </div>
          <div class="teachers-stat-item reveal">
            <strong data-target="18" class="stat-num">0</strong>
            <span>{{ __('public.teachers.stat_2') }}</span>
          </div>
          <div class="teachers-stat-item reveal">
            <strong data-target="1200" class="stat-num">0</strong>
            <span>{{ __('public.teachers.stat_3') }}</span>
          </div>
          <div class="teachers-stat-item reveal">
            <strong data-target="96" class="stat-num">0</strong>
            <span>{{ __('public.teachers.stat_4') }}</span>
          </div>
        </div>
      </section>

      <section class="container teachers-cta-section reveal">
        <div class="glass-section teachers-cta">
          <div>
            <h2>{{ __('public.teachers.cta_title') }}</h2>
            <p>{{ __('public.teachers.cta_text') }}</p>
          </div>
          <a href="{{ route('contact') }}" class="btn"
            >{{ __('public.teachers.cta_button') }}
            <i class="fa-solid fa-arrow-right" style="margin-left: 6px"></i
          ></a>
        </div>
      </section>
    </main>

</x-loyouts.main>
