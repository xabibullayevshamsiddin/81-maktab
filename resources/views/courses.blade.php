<x-layouts.main title="{{ __('public.courses.page_title') }}">
  <div class="bomba-mesh"></div>

  <section class="news-hero news-hero-v2" id="home">
    <div class="container">
      <div class="news-hero-grid prime-reveal">
        <div class="news-hero-text">
          <span class="badge">{{ __('public.courses.badge') }}</span>
          <h1 class="js-split-text">{{ __('public.courses.hero_title') }}</h1>
          <p>{{ __('public.courses.hero_text') }}</p>
        </div>
        <div class="news-hero-visual">
          <div class="news-hero-3d-scene">
            <svg viewBox="0 0 350 300" fill="none" xmlns="http://www.w3.org/2000/svg">
              <ellipse cx="175" cy="270" rx="130" ry="16" fill="url(#bookGroundGlow)" opacity="0.5"/>
              <rect x="90" y="60" width="160" height="200" rx="8" fill="url(#bookGrad1)" stroke="rgba(120,200,255,0.3)" stroke-width="1.5"/>
              <rect x="100" y="55" width="150" height="200" rx="8" fill="url(#bookGrad2)" stroke="rgba(120,200,255,0.25)" stroke-width="1"/>
              <rect x="110" y="50" width="140" height="200" rx="8" fill="url(#bookGrad3)" stroke="rgba(120,200,255,0.2)" stroke-width="1"/>
              <rect x="120" y="80" width="100" height="8" rx="4" fill="rgba(56,189,248,0.4)"/>
              <rect x="120" y="100" width="80" height="6" rx="3" fill="rgba(200,220,255,0.2)"/>
              <rect x="120" y="115" width="90" height="6" rx="3" fill="rgba(200,220,255,0.15)"/>
              <rect x="120" y="130" width="70" height="6" rx="3" fill="rgba(200,220,255,0.12)"/>
              <rect x="120" y="155" width="100" height="60" rx="8" fill="url(#bookImageGrad)" stroke="rgba(100,200,255,0.2)" stroke-width="1"/>
              <circle cx="145" cy="175" r="12" fill="rgba(56,189,248,0.25)"/>
              <polygon points="140,185 155,165 170,180 185,160 195,185" fill="rgba(168,85,247,0.25)"/>
              <rect x="120" y="225" width="60" height="6" rx="3" fill="rgba(200,220,255,0.1)"/>
              <circle cx="80" cy="40" r="1.5" fill="rgba(56,189,248,0.6)"><animate attributeName="cy" values="40;25;40" dur="3s" repeatCount="indefinite"/></circle>
              <circle cx="290" cy="50" r="2" fill="rgba(168,85,247,0.5)"><animate attributeName="cy" values="50;30;50" dur="4s" repeatCount="indefinite"/></circle>
              <circle cx="60" cy="150" r="1" fill="rgba(56,189,248,0.4)"><animate attributeName="cy" values="150;135;150" dur="3.5s" repeatCount="indefinite"/></circle>
              <circle cx="300" cy="170" r="1.5" fill="rgba(168,85,247,0.4)"><animate attributeName="cy" values="170;150;170" dur="2.8s" repeatCount="indefinite"/></circle>
              <defs>
                <linearGradient id="bookGrad1" x1="90" y1="60" x2="250" y2="260">
                  <stop offset="0%" stop-color="rgba(20,50,100,0.9)"/>
                  <stop offset="100%" stop-color="rgba(10,30,60,0.95)"/>
                </linearGradient>
                <linearGradient id="bookGrad2" x1="100" y1="55" x2="250" y2="255">
                  <stop offset="0%" stop-color="rgba(25,60,120,0.85)"/>
                  <stop offset="100%" stop-color="rgba(15,35,70,0.9)"/>
                </linearGradient>
                <linearGradient id="bookGrad3" x1="110" y1="50" x2="250" y2="250">
                  <stop offset="0%" stop-color="rgba(30,70,140,0.8)"/>
                  <stop offset="100%" stop-color="rgba(20,40,80,0.85)"/>
                </linearGradient>
                <linearGradient id="bookImageGrad" x1="120" y1="155" x2="220" y2="215">
                  <stop offset="0%" stop-color="rgba(56,189,248,0.2)"/>
                  <stop offset="100%" stop-color="rgba(168,85,247,0.15)"/>
                </linearGradient>
                <radialGradient id="bookGroundGlow" cx="0.5" cy="0.5" r="0.5">
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

  <main class="courses-page">
    <section class="container courses-filter-section prime-reveal" id="courses-list">
      <div class="section-head">
        <h2 class="js-split-text">{{ __('public.courses.section_title') }}</h2>
        <p>{{ __('public.courses.section_text') }}</p>
      </div>

      <form method="GET" action="{{ route('courses') }}" class="exam-filter-panel filter-shell" id="course-filter-form" data-auto-submit-filter data-filter-kind="courses" data-sticky-filter data-list-skeleton-target="courses-grid">
        <div class="exam-filter-row">
          <div class="exam-filter-field">
            <label class="exam-filter-label" for="course-filter-q">{{ __('public.posts.search_placeholder') }}</label>
            <input type="search" id="course-filter-q" name="q" class="exam-filter-input" placeholder="{{ __('public.courses.search_placeholder') }}" autocomplete="off" value="{{ $q ?? '' }}">
          </div>
          <div class="exam-filter-field">
            <label class="exam-filter-label" for="course-filter-subject">{{ __('public.courses.subject_filter') }}</label>
            <select id="course-filter-subject" name="subject" class="exam-filter-select">
              <option value="">{{ __('public.courses.all_subjects') }}</option>
              @foreach($allSubjects as $subj)
                <option value="{{ e($subj) }}" {{ ($selectedSubject ?? '') === $subj ? 'selected' : '' }}>{{ $subj }}</option>
              @endforeach
            </select>
          </div>
        </div>
        <div class="filter-toolbar">
          <div class="filter-active-tags" data-active-filter-tags></div>
          @if(($q ?? '') !== '' || ($selectedSubject ?? '') !== '')
            <a href="{{ route('courses') }}" class="filter-reset-link">
              <i class="fa-solid fa-rotate-left"></i>
              {{ __('public.common.clear_filters') }}
            </a>
          @endif
        </div>
      </form>

      @php
        $courseTotal = $courses->total();
        $courseShown = $courses->count();
      @endphp
      <p class="exam-filter-count" aria-live="polite">
        @if(($q ?? '') !== '' || ($selectedSubject ?? '') !== '')
          {{ __('public.posts.section_text') }}: {{ $courseShown }} / {{ $courseTotal }}
        @else
          {{ __('public.courses.section_title') }}: {{ $courseTotal }}
        @endif
      </p>
      <div class="courses-page-summary prime-stagger">
        <article class="filter-meta-card">
          <span class="filter-meta-label">{{ __('public.courses.shown_label') }}</span>
          <strong>{{ $courseShown }}</strong>
          <p>{{ __('public.courses.shown_hint') }}</p>
        </article>
        <article class="filter-meta-card">
          <span class="filter-meta-label">{{ __('public.courses.catalog_label') }}</span>
          <strong>{{ $courseTotal }}</strong>
          <p>{{ __('public.courses.catalog_hint') }}</p>
        </article>
      </div>

      <div class="courses-grid prime-stagger" id="courses-grid">
        @php $bookmarkedCourseIds = $bookmarkedCourseIds ?? collect(); @endphp
        @forelse($courses as $course)
          @php
            $courseTitle = localized_model_value($course, 'title');
            $courseDescription = localized_model_value($course, 'description');
            $coursePrice = localized_model_value($course, 'price');
            $courseDuration = localized_model_value($course, 'duration');
          @endphp
          <article class="course-card prime-glow-hover">
            <div class="course-card-media">
              <img
                src="{{ $course->coverImageUrl() }}"
                alt="{{ $courseTitle }}"
                loading="lazy"
                width="640"
                height="360"
              />
            </div>
            <div class="course-body">
              <div class="course-card-head">
                <div>
                  <span class="course-card-label">{{ __('public.courses.badge') }}</span>
                  <h3>{{ $courseTitle }}</h3>
                </div>
              </div>
              <p class="course-card-summary">{{ \Illuminate\Support\Str::limit(strip_tags($courseDescription), 220) }}</p>
              <ul class="course-meta course-meta--grid">
                <li><i class="fa-solid fa-user"></i> {{ $course->instructorName() }}</li>
                <li><i class="fa-regular fa-clock"></i> {{ $courseDuration }}</li>
                <li><i class="fa-solid fa-money-bill"></i> {{ $coursePrice }}</li>
                <li><i class="fa-regular fa-calendar"></i> {{ $course->start_date?->format('Y-m-d') }}</li>
              </ul>
              <div class="course-card-actions">
                <div class="course-card-toolbar">
                  <a
                    href="{{ route('courses.show', $course) }}"
                    class="btn btn-outline btn-sm course-info-trigger"
                  >
                    <i class="fa-solid fa-circle-info"></i> {{ __('public.courses.info_button') }}
                  </a>
                  <button
                    type="button"
                    class="btn btn-outline btn-sm share-btn js-share-trigger"
                    data-share-url="{{ route('courses.show', $course) }}"
                    data-share-title="{{ $courseTitle }}"
                    data-share-text="{{ __('public.courses.share_text') }}"
                    data-share-success="{{ __('public.courses.share_success') }}"
                  >
                    <i class="fa-solid fa-share-nodes"></i> {{ __('public.common.share') }}
                  </button>
                  @include('posts.partials.bookmark-button', [
                    'toggleUrl' => auth()->check() ? route('course.bookmark.toggle', $course) : null,
                    'isSaved' => $bookmarkedCourseIds->contains($course->id),
                    'ariaLabel' => __('public.bookmark.aria_course'),
                  ])
                </div>
                @auth
                  @php
                    $enrollmentByCourseId = $enrollmentByCourseId ?? collect();
                    $en = $enrollmentByCourseId->get($course->id);
                    $isOwnCourse = (int) $course->created_by === (int) auth()->id();
                    $canManageCourse = auth()->user()->canManageSystem() || $isOwnCourse;
                    $isParentUser = auth()->user()->isParentOnly();
                  @endphp
                  @if($canManageCourse)
                    @php
                      $useAdminCourseRoutes = auth()->user()->canManageSystem();
                      $editCourseUrl = $useAdminCourseRoutes ? route('admin.courses.edit', $course) : route('teacher.courses.edit', $course);
                      $destroyCourseUrl = $useAdminCourseRoutes ? route('admin.courses.destroy', $course) : route('teacher.courses.destroy', $course);
                    @endphp
                    <div class="course-card-manage-row">
                      <a href="{{ $editCourseUrl }}" class="btn btn-sm btn-prime">{{ __('public.courses.edit_course') }}</a>
                      <form action="{{ $destroyCourseUrl }}" method="POST" data-confirm="{{ __('public.courses.confirm_delete') }}" data-confirm-title="{{ __('public.courses.delete_title') }}" data-confirm-variant="danger" data-confirm-ok="{{ __('public.courses.delete_action') }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline btn-sm">{{ __('public.courses.delete_course') }}</button>
                      </form>
                    </div>
                  @endif
                  <details class="course-enroll-panel">
                    <summary class="course-enroll-summary">
                      <span>
                        <i class="fa-solid fa-paper-plane"></i>
                        Ariza va holat
                      </span>
                      <i class="fa-solid fa-chevron-down"></i>
                    </summary>
                    <div class="course-enroll-body">
                      @if($isParentUser)
                        <p class="course-enroll-hint">{{ __('public.courses.parent_cannot_enroll') }}</p>
                      @elseif($isOwnCourse)
                        <p class="course-enroll-hint">{{ __('public.courses.own_course_notice') }}</p>
                        @if($en)
                          <form action="{{ route('courses.enroll.cancel', $course) }}" method="POST" class="course-enroll-form" data-confirm="{{ __('public.courses.confirm_remove_enrollment') }}" data-confirm-title="{{ __('public.courses.remove_enrollment_title') }}" data-confirm-variant="primary" data-confirm-ok="{{ __('public.courses.yes') }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline btn-sm">{{ __('public.courses.remove_enrollment') }}</button>
                          </form>
                        @endif
                      @elseif($en && $en->status === \App\Models\CourseEnrollment::STATUS_APPROVED)
                        <span class="course-enrolled-pill"><i class="fa-solid fa-check"></i> {{ __('public.courses.approved') }}</span>
                        <form action="{{ route('courses.enroll.cancel', $course) }}" method="POST" class="course-enroll-form" data-confirm="{{ __('public.courses.confirm_cancel_enrollment') }}" data-confirm-title="{{ __('public.courses.cancel_enrollment_title') }}" data-confirm-variant="primary" data-confirm-ok="{{ __('public.courses.yes') }}">
                          @csrf
                          @method('DELETE')
                          <button type="submit" class="btn btn-outline btn-sm">{{ __('public.courses.cancel') }}</button>
                        </form>
                      @elseif($en && $en->status === \App\Models\CourseEnrollment::STATUS_PENDING)
                        <span class="course-enrolled-pill course-enrolled-pill--pending"><i class="fa-regular fa-clock"></i> {{ __('public.courses.pending') }}</span>
                        <p class="course-enroll-hint">{{ __('public.courses.pending_hint') }}</p>
                        <form action="{{ route('courses.enroll.cancel', $course) }}" method="POST" class="course-enroll-form" data-confirm="{{ __('public.courses.confirm_cancel_request') }}" data-confirm-title="{{ __('public.courses.cancel_request_title') }}" data-confirm-variant="primary" data-confirm-ok="{{ __('public.courses.yes') }}">
                          @csrf
                          @method('DELETE')
                          <button type="submit" class="btn btn-outline btn-sm">{{ __('public.courses.cancel') }}</button>
                        </form>
                      @elseif($en && $en->status === \App\Models\CourseEnrollment::STATUS_REJECTED)
                        <span class="course-enrolled-pill course-enrolled-pill--rejected"><i class="fa-solid fa-xmark"></i> {{ __('public.courses.rejected') }}</span>
                        <p class="course-enroll-hint">{{ __('public.courses.rejected_text') }}</p>
                        <form action="{{ route('courses.enroll', $course) }}" method="POST" class="course-enroll-form">
                          @csrf
                          <label class="course-enroll-label" for="enroll-level-{{ $course->id }}">{{ __('public.courses.subject_level') }} *</label>
                          <input type="text" id="enroll-level-{{ $course->id }}" name="subject_level" class="course-enroll-note" maxlength="120" value="{{ old('subject_level', $en->subject_level) }}" placeholder="{{ __('public.courses.subject_level_placeholder') }}" required />
                          <label class="course-enroll-label" for="enroll-note-{{ $course->id }}">{{ __('public.courses.note') }}</label>
                          <textarea id="enroll-note-{{ $course->id }}" name="note" class="course-enroll-note" rows="2" maxlength="500" placeholder="{{ __('public.courses.note_placeholder') }}">{{ old('note') }}</textarea>
                          @foreach (['subject_level','note'] as $f)
                            @error($f)
                              <span class="form-message course-form-message">{{ $message }}</span>
                            @enderror
                          @endforeach
                          <button type="submit" class="btn btn-prime course-enroll-submit">
                            <i class="fa-solid fa-paper-plane"></i> {{ __('public.courses.resubmit') }}
                          </button>
                        </form>
                      @else
                        <form action="{{ route('courses.enroll', $course) }}" method="POST" class="course-enroll-form">
                          @csrf
                          <label class="course-enroll-label" for="enroll-level-{{ $course->id }}">{{ __('public.courses.subject_level') }} *</label>
                          <input type="text" id="enroll-level-{{ $course->id }}" name="subject_level" class="course-enroll-note" maxlength="120" value="{{ old('subject_level') }}" placeholder="{{ __('public.courses.subject_level_placeholder') }}" required />
                          <label class="course-enroll-label" for="enroll-note-{{ $course->id }}">{{ __('public.courses.note') }}</label>
                          <textarea id="enroll-note-{{ $course->id }}" name="note" class="course-enroll-note" rows="2" maxlength="500" placeholder="{{ __('public.courses.note_contact_placeholder') }}">{{ old('note') }}</textarea>
                          @foreach (['subject_level','note'] as $f)
                            @error($f)
                              <span class="form-message course-form-message">{{ $message }}</span>
                            @enderror
                          @endforeach
                          <button type="submit" class="btn btn-prime course-enroll-submit">
                            <i class="fa-solid fa-pen-to-square"></i> {{ __('public.courses.submit') }}
                          </button>
                        </form>
                      @endif
                    </div>
                  </details>
                @else
                  <p class="course-enroll-guest">
                    <a href="{{ route('login') }}" class="btn btn-outline">{{ __('public.common.login') }}</a>
                    <a href="{{ route('register') }}" class="btn btn-prime">{{ __('public.common.register') }}</a>
                    <span class="course-enroll-hint">{{ __('public.courses.login_needed') }}</span>
                  </p>
                @endauth
              </div>
            </div>
          </article>
        @empty
          <p>{{ __('public.courses.empty') }}</p>
        @endforelse
      </div>
      @if($courses->hasPages())
        <div class="news-pagination" style="margin-top: 28px;">
          @if ($courses->onFirstPage())
            <span class="btn btn-sm btn-outline" aria-disabled="true">{{ __('public.posts.previous') }}</span>
          @else
            <a class="btn btn-sm btn-outline" href="{{ $courses->previousPageUrl() }}">{{ __('public.posts.previous') }}</a>
          @endif

          <span class="news-page-info">
            {{ $courses->currentPage() }} / {{ $courses->lastPage() }}
          </span>

          @if ($courses->hasMorePages())
            <a class="btn btn-sm" href="{{ $courses->nextPageUrl() }}">{{ __('public.posts.next') }}</a>
          @else
            <span class="btn btn-sm" aria-disabled="true">{{ __('public.posts.next') }}</span>
          @endif
        </div>
      @endif
    </section>
  </main>

</x-loyouts.main>
