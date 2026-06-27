@php
  $c = $bookmark->bookmarkable;
  $courseTitle = localized_model_value($c, 'title');
  $courseDescription = localized_model_value($c, 'description');
  $coursePrice = localized_model_value($c, 'price');
  $courseDuration = localized_model_value($c, 'duration');
@endphp
<article class="news-card post-card prime-glow-hover">
  <img
    src="{{ $c->coverImageUrl() }}"
    alt="{{ $courseTitle }}"
    class="js-image-zoom-trigger zoomable-image"
    loading="lazy"
    decoding="async"
    role="button"
    tabindex="0"
  />
  <div style="padding: 0 16px; margin-top: 10px;">
    <span class="badge" style="margin-bottom: 0;">{{ __('public.courses.badge') }}</span>
  </div>
  <h3>{{ $courseTitle }}</h3>
  <p>{{ \Illuminate\Support\Str::limit(strip_tags($courseDescription), 220) }}</p>
  <ul class="course-meta course-meta--grid" style="padding: 0 16px; margin-bottom: 12px;">
    <li><i class="fa-solid fa-user"></i> {{ $c->instructorName() }}</li>
    <li><i class="fa-regular fa-clock"></i> {{ $courseDuration }}</li>
    <li><i class="fa-solid fa-money-bill"></i> {{ $coursePrice }}</li>
    <li><i class="fa-regular fa-calendar"></i> {{ $c->start_date?->format('Y-m-d') }}</li>
  </ul>
  <div class="icon-links">
    <div class="icon-link">
      @include('posts.partials.bookmark-button', [
        'toggleUrl' => route('course.bookmark.toggle', $c),
        'isSaved' => $bookmarkedCourseIds->contains($c->id),
        'ariaLabel' => __('public.bookmark.aria_course'),
      ])
    </div>
    <div class="icon-link-actions">
      <a href="{{ route('courses.show', $c) }}" class="btn btn-sm btn-prime">{{ __('public.common.details') }}</a>
    </div>
  </div>
</article>
