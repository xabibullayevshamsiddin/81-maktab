<x-layouts.main title="{{ __('public.teachers.page_title') }}">
  <div class="bomba-mesh"></div>
  <section class="news-hero news-hero-v2" id="home">
    <div class="container">
      <div class="news-hero-grid prime-reveal">
        <div class="news-hero-text">
          <span class="badge">{{ __('public.teachers.badge') }}</span>
          <h1 class="js-split-text">{{ __('public.teachers.hero_title') }}</h1>
          <p>{{ __('public.teachers.hero_text') }}</p>
          <a href="#teachers-list" class="news-hero-cta">
            {{ __('public.teachers.hero_button') }}
            <i class="fa-solid fa-arrow-down" style="margin-left: 8px"></i>
          </a>
        </div>
        <div class="news-hero-visual">
          <div class="news-hero-3d-scene">
            <svg viewBox="0 0 400 380" fill="none" xmlns="http://www.w3.org/2000/svg">
              <!-- Ground glow -->
              <ellipse cx="200" cy="350" rx="150" ry="18" fill="url(#tGroundGlow)" opacity="0.5"/>
              
              <!-- Whiteboard behind teacher -->
              <rect x="80" y="40" width="240" height="160" rx="10" fill="url(#boardGrad)" stroke="rgba(120,200,255,0.25)" stroke-width="1.5"/>
              <rect x="80" y="40" width="240" height="160" rx="10" fill="url(#boardGlow)" opacity="0.3"/>
              <!-- Board content lines -->
              <rect x="105" y="70" width="80" height="6" rx="3" fill="rgba(56,189,248,0.35)"/>
              <rect x="105" y="85" width="100" height="5" rx="2.5" fill="rgba(200,220,255,0.15)"/>
              <rect x="105" y="98" width="70" height="5" rx="2.5" fill="rgba(200,220,255,0.12)"/>
              <rect x="105" y="111" width="90" height="5" rx="2.5" fill="rgba(200,220,255,0.1)"/>
              <rect x="105" y="124" width="60" height="5" rx="2.5" fill="rgba(168,85,247,0.2)"/>
              <rect x="105" y="137" width="85" height="5" rx="2.5" fill="rgba(200,220,255,0.08)"/>
              <rect x="105" y="150" width="75" height="5" rx="2.5" fill="rgba(200,220,255,0.06)"/>
              <!-- Board glow accent -->
              <circle cx="280" cy="80" r="20" fill="rgba(56,189,248,0.15)"/>
              <circle cx="280" cy="80" r="8" fill="rgba(56,189,248,0.25)"/>
              
              <!-- Teacher figure - glassmorphism style -->
              <!-- Body/suit -->
              <path d="M155 200 Q155 185 175 180 Q195 185 195 200 L200 310 Q200 320 190 320 L160 320 Q150 320 150 310 Z" fill="url(#suitGrad)" stroke="rgba(120,200,255,0.3)" stroke-width="1"/>
              <path d="M155 200 Q155 185 175 180 Q195 185 195 200 L200 310 Q200 320 190 320 L160 320 Q150 320 150 310 Z" fill="url(#suitGlow)" opacity="0.2"/>
              <!-- Suit details -->
              <rect x="170" y="205" width="10" height="60" rx="2" fill="rgba(200,220,255,0.08)"/>
              <line x1="175" y1="200" x2="175" y2="280" stroke="rgba(100,180,255,0.15)" stroke-width="0.5"/>
              <!-- Tie -->
              <polygon points="175,200 182,220 175,240 168,220" fill="url(#tieGrad)" stroke="rgba(100,200,255,0.3)" stroke-width="0.5"/>
              <!-- Collar -->
              <path d="M160 200 L175 210 L190 200" fill="none" stroke="rgba(200,220,255,0.25)" stroke-width="1"/>
              
              <!-- Left arm -->
              <path d="M155 200 Q130 220 120 260 Q118 270 125 275" fill="none" stroke="url(#armGrad)" stroke-width="12" stroke-linecap="round"/>
              <circle cx="125" cy="275" r="8" fill="url(#handGrad)" stroke="rgba(120,200,255,0.2)" stroke-width="0.5"/>
              
              <!-- Right arm holding pointer -->
              <path d="M195 200 Q220 210 240 190 Q250 180 255 170" fill="none" stroke="url(#armGrad)" stroke-width="12" stroke-linecap="round"/>
              <circle cx="255" cy="170" r="8" fill="url(#handGrad)" stroke="rgba(120,200,255,0.2)" stroke-width="0.5"/>
              <!-- Pointer stick -->
              <line x1="255" y1="170" x2="290" y2="90" stroke="rgba(200,220,255,0.5)" stroke-width="2" stroke-linecap="round"/>
              <circle cx="290" cy="88" r="4" fill="rgba(56,189,248,0.6)"/>
              
              <!-- Head -->
              <circle cx="175" cy="155" r="35" fill="url(#headGrad)" stroke="rgba(120,200,255,0.3)" stroke-width="1.5"/>
              <circle cx="175" cy="155" r="35" fill="url(#headGlow)" opacity="0.3"/>
              <!-- Hair -->
              <path d="M140 145 Q140 120 175 115 Q210 120 210 145" fill="rgba(15,30,60,0.8)" stroke="rgba(100,180,255,0.15)" stroke-width="0.5"/>
              <!-- Eyes -->
              <circle cx="165" cy="155" r="4" fill="rgba(200,220,255,0.4)"/>
              <circle cx="185" cy="155" r="4" fill="rgba(200,220,255,0.4)"/>
              <circle cx="166" cy="154" r="1.5" fill="rgba(56,189,248,0.8)"/>
              <circle cx="186" cy="154" r="1.5" fill="rgba(56,189,248,0.8)"/>
              <!-- Smile -->
              <path d="M168 165 Q175 172 182 165" fill="none" stroke="rgba(100,200,255,0.3)" stroke-width="1.5" stroke-linecap="round"/>
              
              <!-- Graduation cap floating -->
              <g transform="translate(220, 100) rotate(-15)">
                <polygon points="0,0 30,10 60,0 30,-10" fill="rgba(20,50,100,0.7)" stroke="rgba(100,200,255,0.3)" stroke-width="1"/>
                <rect x="25" y="-5" width="10" height="8" rx="2" fill="rgba(25,60,120,0.8)"/>
                <circle cx="30" cy="-8" r="3" fill="rgba(56,189,248,0.5)"/>
                <line x1="30" y1="-8" x2="40" y2="0" stroke="rgba(56,189,248,0.4)" stroke-width="1"/>
                <circle cx="40" cy="0" r="2" fill="rgba(56,189,248,0.4)"/>
              </g>
              
              <!-- Floating book left -->
              <g transform="translate(50, 120) rotate(-20)">
                <rect x="0" y="0" width="35" height="28" rx="3" fill="url(#floatBookGrad)" stroke="rgba(100,200,255,0.25)" stroke-width="1"/>
                <rect x="15" y="0" width="1" height="28" fill="rgba(100,200,255,0.15)"/>
                <rect x="5" y="6" width="18" height="3" rx="1.5" fill="rgba(200,220,255,0.2)"/>
                <rect x="5" y="12" width="14" height="2" rx="1" fill="rgba(200,220,255,0.12)"/>
                <rect x="5" y="17" width="16" height="2" rx="1" fill="rgba(200,220,255,0.08)"/>
              </g>
              
              <!-- Floating lightbulb right -->
              <g transform="translate(310, 150)">
                <circle cx="20" cy="15" r="15" fill="rgba(56,189,248,0.12)" stroke="rgba(56,189,248,0.3)" stroke-width="1"/>
                <path d="M15 25 L25 25 L23 30 L17 30 Z" fill="rgba(56,189,248,0.2)"/>
                <circle cx="20" cy="15" r="5" fill="rgba(56,189,248,0.3)"/>
                <line x1="20" y1="5" x2="20" y2="0" stroke="rgba(56,189,248,0.3)" stroke-width="1"/>
                <line x1="28" y1="8" x2="32" y2="4" stroke="rgba(56,189,248,0.2)" stroke-width="1"/>
                <line x1="12" y1="8" x2="8" y2="4" stroke="rgba(56,189,248,0.2)" stroke-width="1"/>
              </g>
              
              <!-- Floating pencil bottom-left -->
              <g transform="translate(60, 250) rotate(45)">
                <rect x="0" y="0" width="8" height="40" rx="1" fill="rgba(168,85,247,0.3)" stroke="rgba(168,85,247,0.4)" stroke-width="0.5"/>
                <polygon points="0,40 8,40 4,48" fill="rgba(251,191,36,0.4)"/>
                <rect x="0" y="0" width="8" height="6" rx="1" fill="rgba(200,220,255,0.2)"/>
              </g>
              
              <!-- Floating atoms/molecules -->
              <circle cx="320" cy="260" r="12" fill="none" stroke="rgba(168,85,247,0.25)" stroke-width="1"/>
              <circle cx="320" cy="260" r="4" fill="rgba(168,85,247,0.3)"/>
              <ellipse cx="320" cy="260" rx="12" ry="5" fill="none" stroke="rgba(56,189,248,0.2)" stroke-width="0.5" transform="rotate(45 320 260)"/>
              <ellipse cx="320" cy="260" rx="12" ry="5" fill="none" stroke="rgba(56,189,248,0.2)" stroke-width="0.5" transform="rotate(-45 320 260)"/>
              
              <!-- Floating particles -->
              <circle cx="70" cy="60" r="2" fill="rgba(56,189,248,0.6)"><animate attributeName="cy" values="60;40;60" dur="3s" repeatCount="indefinite"/></circle>
              <circle cx="340" cy="70" r="2.5" fill="rgba(168,85,247,0.5)"><animate attributeName="cy" values="70;45;70" dur="4s" repeatCount="indefinite"/></circle>
              <circle cx="50" cy="220" r="1.5" fill="rgba(56,189,248,0.5)"><animate attributeName="cy" values="220;200;220" dur="3.5s" repeatCount="indefinite"/></circle>
              <circle cx="350" cy="230" r="2" fill="rgba(168,85,247,0.4)"><animate attributeName="cy" values="350;210;250" dur="2.8s" repeatCount="indefinite"/></circle>
              <circle cx="100" cy="300" r="1" fill="rgba(56,189,248,0.4)"><animate attributeName="cy" values="300;285;300" dur="3.2s" repeatCount="indefinite"/></circle>
              <circle cx="300" cy="310" r="1.5" fill="rgba(168,85,247,0.3)"><animate attributeName="cy" values="310;290;310" dur="4.5s" repeatCount="indefinite"/></circle>
              
              <!-- Gradients -->
              <defs>
                <linearGradient id="boardGrad" x1="80" y1="40" x2="320" y2="200">
                  <stop offset="0%" stop-color="rgba(12,30,60,0.85)"/>
                  <stop offset="100%" stop-color="rgba(8,20,45,0.9)"/>
                </linearGradient>
                <linearGradient id="boardGlow" x1="200" y1="40" x2="200" y2="200">
                  <stop offset="0%" stop-color="rgba(56,189,248,0.2)"/>
                  <stop offset="100%" stop-color="rgba(168,85,247,0.05)"/>
                </linearGradient>
                <linearGradient id="suitGrad" x1="155" y1="180" x2="195" y2="320">
                  <stop offset="0%" stop-color="rgba(20,50,100,0.9)"/>
                  <stop offset="100%" stop-color="rgba(12,30,60,0.95)"/>
                </linearGradient>
                <linearGradient id="suitGlow" x1="175" y1="180" x2="175" y2="320">
                  <stop offset="0%" stop-color="rgba(56,189,248,0.25)"/>
                  <stop offset="100%" stop-color="rgba(168,85,247,0.08)"/>
                </linearGradient>
                <linearGradient id="tieGrad" x1="175" y1="200" x2="175" y2="240">
                  <stop offset="0%" stop-color="rgba(56,189,248,0.5)"/>
                  <stop offset="100%" stop-color="rgba(100,120,200,0.4)"/>
                </linearGradient>
                <linearGradient id="armGrad" x1="0" y1="0" x2="1" y2="0">
                  <stop offset="0%" stop-color="rgba(20,50,100,0.9)"/>
                  <stop offset="100%" stop-color="rgba(15,35,70,0.85)"/>
                </linearGradient>
                <radialGradient id="handGrad" cx="0.4" cy="0.4" r="0.6">
                  <stop offset="0%" stop-color="rgba(30,70,130,0.9)"/>
                  <stop offset="100%" stop-color="rgba(20,50,100,0.95)"/>
                </radialGradient>
                <linearGradient id="headGrad" x1="140" y1="120" x2="210" y2="190">
                  <stop offset="0%" stop-color="rgba(20,50,100,0.9)"/>
                  <stop offset="100%" stop-color="rgba(15,35,70,0.95)"/>
                </linearGradient>
                <linearGradient id="headGlow" x1="175" y1="120" x2="175" y2="190">
                  <stop offset="0%" stop-color="rgba(56,189,248,0.3)"/>
                  <stop offset="100%" stop-color="rgba(168,85,247,0.1)"/>
                </linearGradient>
                <linearGradient id="floatBookGrad" x1="0" y1="0" x2="35" y2="28">
                  <stop offset="0%" stop-color="rgba(100,60,180,0.6)"/>
                  <stop offset="100%" stop-color="rgba(60,40,120,0.7)"/>
                </linearGradient>
                <radialGradient id="tGroundGlow" cx="0.5" cy="0.5" r="0.5">
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

    <main>
      <section class="container teachers-section prime-reveal" id="teachers-list">
        <div class="section-head">
          <h2 class="js-split-text">{{ __('public.teachers.list_title') }}</h2>
          <p>{{ __('public.teachers.list_text') }}</p>
        </div>

        <form method="GET" action="{{ route('teacher') }}" class="exam-filter-panel" style="margin-bottom:18px;" id="teacher-filter-form">
          <div class="exam-filter-row">
            <div class="exam-filter-field">
              <label class="exam-filter-label" for="teacher-filter-q">{{ __('public.teachers.search_by_name') }}</label>
              <input type="search" id="teacher-filter-q" name="q" class="exam-filter-input" placeholder="{{ __('public.teachers.search_placeholder') }}" autocomplete="off" value="{{ $q ?? '' }}">
            </div>
            <div class="exam-filter-field">
              <label class="exam-filter-label" for="teacher-filter-subject">{{ __('public.teachers.filter_by_subject') }}</label>
              <select id="teacher-filter-subject" name="subject" class="exam-filter-select">
                <option value="">{{ __('public.courses.all_subjects') }}</option>
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
            {{ __('public.teachers.showing') }}: {{ $teacherShown }} / {{ $teacherTotal }}
          @else
            {{ __('public.teachers.total') }}: {{ $teacherTotal }} {{ __('public.teachers.teachers_count_label') }}
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
                  src="{{ $teacher->image ? app_storage_asset($teacher->image) : app_public_asset('temp/img/ChatGPT Image Jul 5, 2026, 01_38_09 AM.png') }}"
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
              @php $teacherBioText = localized_model_value($teacher, 'bio'); @endphp
              @if(filled($teacherBioText))
                <p class="teacher-desc">{{ \Illuminate\Support\Str::limit(trim($teacherBioText), 180) }}</p>
              @endif
              <ul class="teacher-meta">
                @if($teacher->experience_years)
                  <li><i class="fa-solid fa-award"></i> {{ __('public.common.years_experience', ['count' => $teacher->experience_years]) }}</li>
                @endif
                <li><i class="fa-solid fa-users"></i> {{ $teacher->grades ?: __('public.common.all_grades') }}</li>
              </ul>
              @if(filled($teacherAchievements))
                <p class="teacher-achievements-preview"><i class="fa-solid fa-trophy"></i> {{ $teacherAchievementPreview }}</p>
              @endif
              <div class="teacher-actions">
                @php
                  $likedTeacherIds = $likedTeacherIds ?? collect();
                  $bookmarkedTeacherIds = $bookmarkedTeacherIds ?? collect();
                @endphp
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
          <nav class="teachers-pagination" aria-label="{{ __('public.teachers.pagination_aria') }}">
            @if($teachers->onFirstPage())
              <span class="teachers-page-btn is-disabled" aria-disabled="true">
                <i class="fa-solid fa-chevron-left"></i> {{ __('public.posts.previous') }}
              </span>
            @else
              <a class="teachers-page-btn" href="{{ $teachers->previousPageUrl() }}">
                <i class="fa-solid fa-chevron-left"></i> {{ __('public.posts.previous') }}
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
                {{ __('public.posts.next') }} <i class="fa-solid fa-chevron-right"></i>
              </a>
            @else
              <span class="teachers-page-btn is-disabled" aria-disabled="true">
                {{ __('public.posts.next') }} <i class="fa-solid fa-chevron-right"></i>
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
