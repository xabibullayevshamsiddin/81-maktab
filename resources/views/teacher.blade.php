<x-loyouts.main title="{{ __('public.teachers.page_title') }}">
  <section class="news-hero" id="home">
    <div class="container">
      <div class="news-hero-content reveal">
          <span class="badge">{{ __('public.teachers.badge') }}</span>
          <h1 class="js-split-text">{{ __('public.teachers.hero_title') }}</h1>
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
          <h2 class="js-split-text">{{ __('public.teachers.list_title') }}</h2>
          <p>{{ __('public.teachers.list_text') }}</p>
        </div>

        <div class="exam-filter-panel" style="margin-bottom:18px;">
          <div class="exam-filter-row">
            <div class="exam-filter-field">
              <label class="exam-filter-label" for="teacher-filter-q">Nom bo'yicha qidirish</label>
              <input type="search" id="teacher-filter-q" class="exam-filter-input" placeholder="Ustoz ismi..." autocomplete="off">
            </div>
            <div class="exam-filter-field">
              <label class="exam-filter-label" for="teacher-filter-subject">Fan bo'yicha</label>
              <select id="teacher-filter-subject" class="exam-filter-select">
                <option value="">Barcha fanlar</option>
                @php
                  $teacherItems = $teachers instanceof \Illuminate\Pagination\AbstractPaginator
                    ? $teachers->getCollection()
                    : collect($teachers);
                  $uniqueSubjects = $teacherItems->map(fn($t) => localized_model_value($t, 'subject'))->filter()->unique()->sort()->values();
                @endphp
                @foreach($uniqueSubjects as $subj)
                  <option value="{{ e(mb_strtolower($subj)) }}">{{ $subj }}</option>
                @endforeach
              </select>
            </div>
          </div>
        </div>
        <p class="exam-filter-count" id="teacher-filter-count" aria-live="polite"></p>

        <div class="teachers-grid" id="teachers-grid">
          @forelse($teachers as $teacher)
            @php
              $teacherSubject = localized_model_value($teacher, 'subject');
              $teacherLavozim = localized_model_value($teacher, 'lavozim');
              $teacherAchievements = localized_model_value($teacher, 'achievements');
              $teacherAchievementPreview = \Illuminate\Support\Str::limit(trim((string) strtok($teacherAchievements, "\n")), 100);
            @endphp
            <article class="teacher-card reveal" data-teacher-card data-search-text="{{ e(mb_strtolower($teacher->full_name)) }}" data-subject="{{ e(mb_strtolower($teacherSubject)) }}">
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
                  @php $teacherRoleLine = $teacherLavozim ?: $teacherSubject; @endphp
                  @if(filled($teacherRoleLine))
                    <p class="teacher-role">{{ $teacherRoleLine }}</p>
                  @endif
                </div>
              </div>
              <p class="teacher-desc">{{ $teacher->shortBio(220) }}</p>
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
                  <form action="{{ route('teacher.like', $teacher) }}" method="POST" class="js-like-form">
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

        <div class="exam-empty exam-filter-zero" id="teacher-filter-zero" hidden>
          <p style="margin:0;font-size:16px;"><i class="fa-solid fa-filter-circle-xmark" style="opacity:0.55;"></i> Filtr bo'yicha ustoz topilmadi.</p>
        </div>

        @if($teachers instanceof \Illuminate\Pagination\AbstractPaginator && $teachers->hasPages())
          @php
            $current = $teachers->currentPage();
            $last = $teachers->lastPage();
            $start = max(1, $current - 2);
            $end = min($last, $current + 2);
          @endphp
          <nav class="teachers-pagination" aria-label="Ustozlar pagination">
            @if($teachers->onFirstPage())
              <span class="teachers-page-btn is-disabled" aria-disabled="true">
                <i class="fa-solid fa-chevron-left"></i> Oldingi
              </span>
            @else
              <a class="teachers-page-btn" href="{{ $teachers->previousPageUrl() }}">
                <i class="fa-solid fa-chevron-left"></i> Oldingi
              </a>
            @endif

            <div class="teachers-page-numbers">
              @for($page = $start; $page <= $end; $page++)
                @if($page === $current)
                  <span class="teachers-page-number is-active" aria-current="page">{{ $page }}</span>
                @else
                  <a class="teachers-page-number" href="{{ $teachers->url($page) }}">{{ $page }}</a>
                @endif
              @endfor
            </div>

            <span class="teachers-page-info">Sahifa {{ $current }} / {{ $last }}</span>

            @if($teachers->hasMorePages())
              <a class="teachers-page-btn" href="{{ $teachers->nextPageUrl() }}">
                Keyingi <i class="fa-solid fa-chevron-right"></i>
              </a>
            @else
              <span class="teachers-page-btn is-disabled" aria-disabled="true">
                Keyingi <i class="fa-solid fa-chevron-right"></i>
              </span>
            @endif
          </nav>
        @endif

        <script>
          (function () {
            var grid = document.getElementById('teachers-grid');
            var qEl = document.getElementById('teacher-filter-q');
            var subjEl = document.getElementById('teacher-filter-subject');
            var countEl = document.getElementById('teacher-filter-count');
            var zeroEl = document.getElementById('teacher-filter-zero');
            if (!grid || !qEl || !subjEl) return;

            var cards = Array.prototype.slice.call(grid.querySelectorAll('[data-teacher-card]'));
            var total = cards.length;

            function apply() {
              var t = (qEl.value || '').trim().toLowerCase();
              var sv = (subjEl.value || '').toLowerCase();
              var count = 0;

              cards.forEach(function (c) {
                var show = true;
                if (t && (c.getAttribute('data-search-text') || '').indexOf(t) === -1) show = false;
                if (show && sv && (c.getAttribute('data-subject') || '').indexOf(sv) === -1) show = false;
                c.style.display = show ? '' : 'none';
                if (show) count++;
              });

              if (countEl) {
                countEl.textContent = count === total ? 'Jami: ' + total + ' ta ustoz' : "Ko'rsatilmoqda: " + count + ' / ' + total;
              }
              if (zeroEl) zeroEl.hidden = count > 0;
              grid.style.display = count > 0 ? '' : 'none';
            }

            qEl.addEventListener('input', apply);
            subjEl.addEventListener('change', apply);
            apply();
          })();
        </script>
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
        </div>
      </section>

      <section class="teachers-stats-section">
        <div class="container teachers-stats">
          <div class="teachers-stat-item reveal">
            <strong class="stat-num">{{ number_format($teacherStats['experienced_teachers']) }}</strong>
            <span>{{ __('public.teachers.stat_1') }}</span>
          </div>
          <div class="teachers-stat-item reveal">
            <strong class="stat-num">{{ number_format($teacherStats['subject_areas']) }}</strong>
            <span>{{ __('public.teachers.stat_2') }}</span>
          </div>
          <div class="teachers-stat-item reveal">
            <strong class="stat-num">{{ number_format($teacherStats['students']) }}</strong>
            <span>{{ __('public.teachers.stat_3') }}</span>
          </div>
          <div class="teachers-stat-item reveal">
            <strong class="stat-num">{{ number_format($teacherStats['satisfaction_percent']) }}</strong>
            <span>{{ __('public.teachers.stat_4') }}</span>
          </div>
        </div>
      </section>

      <section class="container teachers-cta-section reveal">
        <div class="glass-section teachers-cta">
          <div>
            <h2 class="js-split-text">{{ __('public.teachers.cta_title') }}</h2>
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
