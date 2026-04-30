<x-loyouts.main title="{{ __('public.courses.page_title') }}">
  <section class="news-hero" id="home">
    <div class="container">
      <div class="news-hero-content prime-reveal">
        <span class="badge">{{ __('public.courses.badge') }}</span>
        <h1 class="js-split-text">{{ __('public.courses.hero_title') }}</h1>
        <p>{{ __('public.courses.hero_text') }}</p>
      </div>
    </div>
  </section>

  <main>
    <section class="container courses-filter-section prime-reveal" id="courses-list">
      <div class="section-head">
        <h2 class="js-split-text">{{ __('public.courses.section_title') }}</h2>
        <p>{{ __('public.courses.section_text') }}</p>
      </div>

      <form method="GET" action="{{ route('courses') }}" class="exam-filter-panel" style="margin-bottom:18px;" id="course-filter-form">
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
      </form>
      <script>
        (function () {
          var form = document.getElementById('course-filter-form');
          var qInput = document.getElementById('course-filter-q');
          var subjSelect = document.getElementById('course-filter-subject');
          if (!form) return;

          if (subjSelect) {
            subjSelect.addEventListener('change', function () {
              form.submit();
            });
          }

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

      <div class="courses-grid prime-stagger" id="courses-grid">
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
              <h3>{{ $courseTitle }}</h3>
              <p>{{ \Illuminate\Support\Str::limit(strip_tags($courseDescription), 220) }}</p>
              <ul class="course-meta">
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
                </div>
                @auth
                  @php
                    $enrollmentByCourseId = $enrollmentByCourseId ?? collect();
                    $en = $enrollmentByCourseId->get($course->id);
                    $isOwnCourse = (int) $course->created_by === (int) auth()->id();
                    $canManageCourse = auth()->user()->canManageSystem() || $isOwnCourse;
                    $isParentUser = auth()->user()->isParent();
                  @endphp
                  @if($canManageCourse)
                    @php
                      $useAdminCourseRoutes = auth()->user()->canManageSystem();
                      $editCourseUrl = $useAdminCourseRoutes ? route('admin.courses.edit', $course) : route('teacher.courses.edit', $course);
                      $destroyCourseUrl = $useAdminCourseRoutes ? route('admin.courses.destroy', $course) : route('teacher.courses.destroy', $course);
                    @endphp
                    <div style="display:flex; gap:8px; flex-wrap:wrap; margin-bottom:10px;">
                      <a href="{{ $editCourseUrl }}" class="btn btn-sm btn-prime">{{ __('public.courses.edit_course') }}</a>
                      <form action="{{ $destroyCourseUrl }}" method="POST" data-confirm="{{ __('public.courses.confirm_delete') }}" data-confirm-title="{{ __('public.courses.delete_title') }}" data-confirm-variant="danger" data-confirm-ok="{{ __('public.courses.delete_action') }}" style="display:inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline btn-sm">{{ __('public.courses.delete_course') }}</button>
                      </form>
                    </div>
                  @endif
                  @if($isParentUser)
                    <p class="course-enroll-hint" style="font-size:13px;margin:0;">
                      {{ __('public.courses.parent_cannot_enroll') }}
                    </p>
                  @elseif($isOwnCourse)
                    <p class="course-enroll-hint" style="font-size:13px;margin:0;">{{ __('public.courses.own_course_notice') }}</p>
                    @if($en)
                      <form action="{{ route('courses.enroll.cancel', $course) }}" method="POST" class="course-enroll-form" style="margin-top:10px;" data-confirm="{{ __('public.courses.confirm_remove_enrollment') }}" data-confirm-title="{{ __('public.courses.remove_enrollment_title') }}" data-confirm-variant="primary" data-confirm-ok="{{ __('public.courses.yes') }}">
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
                    <span class="course-enrolled-pill" style="background:rgba(245,158,11,.2);color:#b45309;"><i class="fa-regular fa-clock"></i> {{ __('public.courses.pending') }}</span>
                    <p class="course-enroll-hint" style="font-size:13px;margin:8px 0;">{{ __('public.courses.pending_hint') }}</p>
                    <form action="{{ route('courses.enroll.cancel', $course) }}" method="POST" class="course-enroll-form" data-confirm="{{ __('public.courses.confirm_cancel_request') }}" data-confirm-title="{{ __('public.courses.cancel_request_title') }}" data-confirm-variant="primary" data-confirm-ok="{{ __('public.courses.yes') }}">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn-outline btn-sm">{{ __('public.courses.cancel') }}</button>
                    </form>
                  @elseif($en && $en->status === \App\Models\CourseEnrollment::STATUS_REJECTED)
                    <span class="course-enrolled-pill" style="background:rgba(185,28,28,.12);color:#b91c1c;"><i class="fa-solid fa-xmark"></i> {{ __('public.courses.rejected') }}</span>
                    <p class="course-enroll-hint" style="font-size:13px;">{{ __('public.courses.rejected_text') }}</p>
                    <form action="{{ route('courses.enroll', $course) }}" method="POST" class="course-enroll-form">
                      @csrf
                      <label class="course-enroll-label" for="enroll-level-{{ $course->id }}">{{ __('public.courses.subject_level') }} *</label>
                      <input type="text" id="enroll-level-{{ $course->id }}" name="subject_level" class="course-enroll-note" maxlength="120" value="{{ old('subject_level', $en->subject_level) }}" placeholder="{{ __('public.courses.subject_level_placeholder') }}" required />
                      <label class="course-enroll-label" for="enroll-note-{{ $course->id }}">{{ __('public.courses.note') }}</label>
                      <textarea id="enroll-note-{{ $course->id }}" name="note" class="course-enroll-note" rows="2" maxlength="500" placeholder="{{ __('public.courses.note_placeholder') }}">{{ old('note') }}</textarea>
                      @foreach (['subject_level','note'] as $f)
                        @error($f)
                          <span class="form-message" style="color:#b91c1c;font-size:13px;">{{ $message }}</span>
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
                          <span class="form-message" style="color:#b91c1c;font-size:13px;">{{ $message }}</span>
                        @enderror
                      @endforeach
                      <button type="submit" class="btn btn-prime course-enroll-submit">
                        <i class="fa-solid fa-pen-to-square"></i> {{ __('public.courses.submit') }}
                      </button>
                    </form>
                  @endif
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
