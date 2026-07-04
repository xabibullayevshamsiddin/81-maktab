@php
  $t = $bookmark->bookmarkable;
  $teacherSubject = localized_model_value($t, 'subject');
  $teacherLavozim = localized_model_value($t, 'lavozim');
  $roleLine = $teacherLavozim ?: $teacherSubject;
@endphp
<article class="news-card post-card prime-glow-hover">
  <img
    src="{{ $t->image ? app_storage_asset($t->image) : app_public_asset('temp/img/ChatGPT Image Jul 5, 2026, 01_38_09 AM.png') }}"
    alt="{{ $t->full_name }}"
    class="js-image-zoom-trigger zoomable-image"
    data-zoom-src="{{ $t->image ? app_storage_asset($t->image) : '' }}"
    loading="lazy"
    decoding="async"
    role="button"
    tabindex="0"
  />
  <div style="padding: 0 16px; margin-top: 10px;">
    <span class="badge" style="margin-bottom: 0;">{{ __('public.teachers.badge') }}</span>
  </div>
  <h3>{{ $t->full_name }}</h3>
  <p>{{ $t->shortBio(200) }}</p>
  @if(filled($roleLine))
    <p style="padding: 0 16px; margin: -8px 0 12px; color: var(--muted); font-size: 14px;">{{ $roleLine }}</p>
  @endif
  <div class="icon-links">
    <div class="icon-link">
      @include('posts.partials.bookmark-button', [
        'toggleUrl' => route('teacher.bookmark.toggle', $t),
        'isSaved' => $bookmarkedTeacherIds->contains($t->id),
        'ariaLabel' => __('public.bookmark.aria_teacher'),
      ])
    </div>
    <div class="icon-link-actions">
      <a href="{{ route('teacher.show', $t) }}" class="btn btn-sm btn-prime">{{ __('public.common.details') }}</a>
    </div>
  </div>
</article>
