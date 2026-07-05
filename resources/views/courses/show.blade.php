@php
  $teacher = $course->teacher;
  $teacherAchievements = $course->instructorAchievements();
  $teacherBio = $course->instructorBio(260);
  $teacherSubject = $course->instructorSubject();
  $teacherName = $course->instructorName();
  $teacherImage = $course->instructorImageUrl();
  $teacherExperience = $course->instructorExperienceLabel();
  $teacherGrades = $course->instructorGradesLabel();
  $courseTitle = localized_model_value($course, 'title');
  $courseDescription = localized_model_value($course, 'description');
  $coursePrice = localized_model_value($course, 'price');
  $courseDuration = localized_model_value($course, 'duration');
@endphp

<x-layouts.main :title="$courseTitle.' | 81-IDUM'">
  <section class="news-hero course-details-page-hero">
    <div class="container">
      <div class="news-hero-content reveal">
        <span class="badge">{{ __('public.courses.badge') }}</span>
        <h1>{{ $courseTitle }}</h1>
        <p>{{ __('public.courses.modal_title') }}</p>
      </div>
    </div>
  </section>

	  <main class="course-details-page">
	    <section class="container course-details-page-shell">
	      <article class="course-details-page-card reveal">
        <div class="course-details-page-media">
          <img
            src="{{ $course->coverImageUrl() }}"
            alt="{{ $courseTitle }}"
            loading="eager"
            width="960"
            height="540"
          />
        </div>

        <div class="course-details-content course-details-content--page">
          <div class="course-details-head">
            <div>
              <span class="course-detail-badge">{{ __('public.courses.modal_title') }}</span>
              <h2>{{ $courseTitle }}</h2>
            </div>
          </div>

          @php $bookmarkedCourseIds = $bookmarkedCourseIds ?? collect(); @endphp
          <div class="course-details-toolbar icon-links" style="margin: 12px 0 0; padding-left: 0; padding-right: 0;">
            <div class="icon-link" style="flex:1;">
              <button
                type="button"
                class="btn btn-sm btn-outline share-btn js-share-trigger"
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
          </div>

	          <div class="course-details-grid">
	            <div class="course-details-main">
                <div class="course-details-hero-strip">
                  <div class="course-details-kpi">
                    <span>{{ __('public.courses_show.duration_kpi') }}</span>
                    <strong>{{ $courseDuration }}</strong>
                  </div>
                  <div class="course-details-kpi">
                    <span>{{ __('public.courses_show.payment') }}</span>
                    <strong>{{ $coursePrice }}</strong>
                  </div>
                  <div class="course-details-kpi">
                    <span>{{ __('public.courses_show.start_kpi') }}</span>
                    <strong>{{ $course->start_date?->format('Y-m-d') ?: '-' }}</strong>
                  </div>
                </div>

	              <ul class="course-details-meta">
	                <li><i class="fa-solid fa-user"></i> <span>{{ __('public.courses.author') }}: {{ $teacherName }}</span></li>
	                <li><i class="fa-solid fa-book-open"></i> <span>{{ __('public.courses.subject') }}: {{ $teacherSubject ?: __('public.common.not_entered') }}</span></li>
	                <li><i class="fa-regular fa-clock"></i> <span>{{ __('public.courses.duration') }}: {{ $courseDuration }}</span></li>
	                <li><i class="fa-solid fa-money-bill-wave"></i> <span>{{ __('public.courses.price') }}: {{ $coursePrice }}</span></li>
	                <li><i class="fa-regular fa-calendar-check"></i> <span>{{ __('public.courses.start_date') }}: {{ $course->start_date?->format('Y-m-d') ?: '-' }}</span></li>
	              </ul>

	              <div class="course-details-copy">
	                <h3>{{ __('public.courses.learn') }}</h3>
	                <p>{!! nl2br(e($courseDescription)) !!}</p>
	              </div>

                <div class="course-details-story-grid">
                  <article class="course-story-card">
                    <h3><i class="fa-solid fa-bullseye"></i> {{ __('public.courses_show.audience_title') }}</h3>
                    <p>{{ $teacherGrades ?: __('public.courses_show.audience_default') }}</p>
                  </article>
                  <article class="course-story-card">
                    <h3><i class="fa-solid fa-flag-checkered"></i> {{ __('public.courses_show.outcome_title') }}</h3>
                    <p>{{ $teacherBio ?: __('public.courses_show.outcome_default') }}</p>
                  </article>
                </div>
	            </div>

	            <aside class="course-teacher-card">
              <div class="course-teacher-card-head">
                <img src="{{ $teacherImage }}" alt="{{ $teacherName }}" loading="lazy" decoding="async">
                <div>
                  <span class="course-teacher-label">{{ __('public.courses.teacher_label') }}</span>
                  <h3>{{ $teacherName }}</h3>
                  <p>{{ $teacherSubject }}</p>
                </div>
              </div>

              <div class="course-teacher-facts">
                <span><i class="fa-solid fa-award"></i> {{ $teacherExperience }}</span>
                <span><i class="fa-solid fa-layer-group"></i> {{ $teacherGrades }}</span>
              </div>

              <p class="course-teacher-bio">{{ $teacherBio }}</p>

              <div class="course-teacher-achievements">
                <h4><i class="fa-solid fa-trophy"></i> {{ __('public.courses_show.achievements_title') }}</h4>
                @if(!empty($teacherAchievements))
                  <ul>
                    @foreach($teacherAchievements as $achievement)
                      <li><i class="fa-solid fa-check"></i> {{ $achievement }}</li>
                    @endforeach
                  </ul>
                @else
                  <p class="course-teacher-empty">{{ __('public.courses.teacher_empty') }}</p>
                @endif
              </div>

	              @if($teacher)
	                <a href="{{ route('teacher.show', $teacher) }}" class="btn btn-sm course-teacher-link">
	                  <i class="fa-solid fa-user-graduate"></i> {{ __('public.courses.teacher_profile') }}
	                </a>
	              @endif
                <a href="{{ route('courses') }}" class="btn btn-outline btn-sm course-teacher-link">
                  <i class="fa-solid fa-grid-2"></i> {{ __('public.courses_show.other_courses') }}
                </a>
	            </aside>
	          </div>
	        </div>
	      </article>
    </section>
  </main>
</x-loyouts.main>
