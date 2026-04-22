<x-loyouts.main title="{{ __('public.teachers.page_title') }}">
  <section class="news-hero" id="home">
    <div class="container">
      <div class="news-hero-content reveal">
          <span class="badge">{{ __('public.teachers.badge') }}</span>
          <h1 class="js-split-text">{{ __('public.teachers.hero_title') }}</h1>
          <p>{{ __('public.teachers.hero_text') }}</p>
          <a href="#teachers-list" style="margin-top: 15px;" class="btn btn-prime"
            >{{ __('public.teachers.hero_button') }}
            <i class="fa-solid fa-arrow-down" style="margin-left: 6px"></i
          ></a>
      </div>
    </div>
  </section>

    <main>
      <section class="container teachers-section prime-reveal" id="teachers-list">
        <div class="section-head">
          <h2 class="js-split-text">{{ __('public.teachers.list_title') }}</h2>
          <p>{{ __('public.teachers.list_text') }}</p>
        </div>

        <form method="GET" action="{{ route('teacher') }}" class="exam-filter-panel" style="margin-bottom:18px;" id="teacher-filter-form">
          <div class="exam-filter-row">
            <div class="exam-filter-field">
              <label class="exam-filter-label" for="teacher-filter-q">Nom bo'yicha qidirish</label>
              <input type="search" id="teacher-filter-q" name="q" class="exam-filter-input" placeholder="Ustoz ismi..." autocomplete="off" value="{{ $q ?? '' }}">
            </div>
            <div class="exam-filter-field">
              <label class="exam-filter-label" for="teacher-filter-subject">Fan bo'yicha</label>
              <select id="teacher-filter-subject" name="subject" class="exam-filter-select">
                <option value="">Barcha fanlar</option>
                @foreach($allSubjects as $subj)
                  <option value="{{ e($subj) }}" {{ ($selectedSubject ?? '') === $subj ? 'selected' : '' }}>{{ $subj }}</option>
                @endforeach
              </select>
            </div>
          </div>
        </form>
        <script>
          (function () {
            var form = document.getElementById('teacher-filter-form');
            var qInput = document.getElementById('teacher-filter-q');
            var subjSelect = document.getElementById('teacher-filter-subject');
            if (!form) return;

            // Auto-submit on subject change immediately
            if (subjSelect) {
              subjSelect.addEventListener('change', function () {
                form.submit();
              });
            }

            // Auto-submit on text input with debounce (500ms)
            var debounceTimer;
            if (qInput) {
              qInput.addEventListener('input', function () {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(function () {
                  form.submit();
                }, 500);
              });
            }
          })();
        </script>
        @php
          $teacherTotal = $teachers->total();
          $teacherShown = $teachers->count();
        @endphp
        <p class="exam-filter-count" aria-live="polite">
          @if(($q ?? '') !== '' || ($selectedSubject ?? '') !== '')
            Ko'rsatilmoqda: {{ $teacherShown }} / {{ $teacherTotal }}
          @else
            Jami: {{ $teacherTotal }} ta ustoz
          @endif
        </p>

        <div class="teachers-grid prime-stagger" id="teachers-grid">
          @forelse($teachers as $teacher)
            @php
              $teacherSubject = localized_model_value($teacher, 'subject');
              $teacherLavozim = localized_model_value($teacher, 'lavozim');
              $teacherAchievements = localized_model_value($teacher, 'achievements');
              $teacherAchievementPreview = \Illuminate\Support\Str::limit(trim((string) strtok($teacherAchievements, "\n")), 100);
            @endphp
            <article class="teacher-card prime-glow-hover" data-teacher-card data-search-text="{{ e(mb_strtolower($teacher->full_name)) }}" data-subject="{{ e(mb_strtolower($teacherSubject)) }}">
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
                <a href="{{ route('teacher.show', $teacher) }}" class="btn btn-sm btn-prime">{{ __('public.common.details') }}</a>
              </div>
            </article>
          @empty
            <p>{{ __('public.teachers.empty') }}</p>
          @endforelse
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

      </section>

      <section class="teaching-approach prime-reveal">
        <div class="container approach-grid prime-stagger">
          <article class="approach-card">
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

    </main>

</x-loyouts.main>
