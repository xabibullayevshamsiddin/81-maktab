@php
  $teacher = $course->teacher;
  $teacherAchievements = $teacher?->achievementItems(null, app()->getLocale()) ?? [];
  $teacherBio = $teacher?->shortBio(260, app()->getLocale()) ?? __('public.courses.teacher_bio_empty');
  $teacherSubject = $teacher ? localized_model_value($teacher, 'subject') : __('public.courses.subject_missing');
  $courseTitle = localized_model_value($course, 'title');
  $courseDescription = localized_model_value($course, 'description');
  $coursePrice = localized_model_value($course, 'price');
  $courseDuration = localized_model_value($course, 'duration');
@endphp

<x-loyouts.main :title="$courseTitle.' | 81-IDUM'">
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
      <div class="course-details-page-actions reveal">
        <a href="{{ route('courses') }}" class="btn btn-outline btn-sm">
          <i class="fa-solid fa-arrow-left"></i> {{ __('public.common.back') }}
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

          <div class="course-details-grid">
            <div class="course-details-main">
              <ul class="course-details-meta">
                <li><i class="fa-solid fa-user"></i> <span>{{ __('public.courses.author') }}: {{ $teacher?->full_name ?: '-' }}</span></li>
                <li><i class="fa-solid fa-book-open"></i> <span>{{ __('public.courses.subject') }}: {{ $teacherSubject ?: __('public.common.not_entered') }}</span></li>
                <li><i class="fa-regular fa-clock"></i> <span>{{ __('public.courses.duration') }}: {{ $courseDuration }}</span></li>
                <li><i class="fa-solid fa-money-bill-wave"></i> <span>{{ __('public.courses.price') }}: {{ $coursePrice }}</span></li>
                <li><i class="fa-regular fa-calendar-check"></i> <span>{{ __('public.courses.start_date') }}: {{ $course->start_date?->format('Y-m-d') ?: '-' }}</span></li>
              </ul>

              <div class="course-details-copy">
                <h3>{{ __('public.courses.learn') }}</h3>
                <p>{!! nl2br(e($courseDescription)) !!}</p>
              </div>
            </div>

            <aside class="course-teacher-card">
              <div class="course-teacher-card-head">
                <img src="{{ $teacher?->imageUrl() }}" alt="{{ $teacher?->full_name ?: 'Ustoz' }}" loading="lazy" decoding="async">
                <div>
                  <span class="course-teacher-label">{{ __('public.courses.teacher_label') }}</span>
                  <h3>{{ $teacher?->full_name ?: 'Ustoz' }}</h3>
                  <p>{{ $teacherSubject }}</p>
                </div>
              </div>

              <div class="course-teacher-facts">
                <span><i class="fa-solid fa-award"></i> {{ (int) ($teacher?->experience_years ?? 0) }} yil tajriba</span>
                <span><i class="fa-solid fa-layer-group"></i> {{ $teacher?->grades ?: 'Barcha sinflar' }}</span>
              </div>

              <p class="course-teacher-bio">{{ $teacherBio }}</p>

              <div class="course-teacher-achievements">
                <h4><i class="fa-solid fa-trophy"></i> Yutuqlar va tajriba</h4>
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
            </aside>
          </div>
        </div>
      </article>
    </section>
  </main>
</x-loyouts.main>
