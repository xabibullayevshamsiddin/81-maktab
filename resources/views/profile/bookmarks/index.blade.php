@php
  use App\Models\Course;
  use App\Models\Post;
  use App\Models\Teacher;

  $likedPostIds = $likedPostIds ?? collect();
  $bookmarkedPostIds = $bookmarkedPostIds ?? collect();
  $bookmarkedTeacherIds = $bookmarkedTeacherIds ?? collect();
  $bookmarkedCourseIds = $bookmarkedCourseIds ?? collect();
@endphp
<x-layouts.main :title="__('profile.bookmarks.page_title')">
  <main class="news">
    <section class="container news glass-section prime-reveal" style="padding-top: 120px; padding-bottom: 48px;">
      <div class="section-head" style="margin-bottom: 24px;">
        <div>
          <p class="comment-hint" style="margin: 0 0 8px;">
            <a href="{{ route('profile.show') }}" class="btn btn-sm btn-outline">{{ __('profile.bookmarks.back') }}</a>
          </p>
          <h1 class="js-split-text" style="margin: 0 0 8px;">{{ __('profile.bookmarks.title') }}</h1>
          <p style="margin: 0; color: var(--muted); max-width: 640px;">{{ __('profile.bookmarks.intro') }}</p>
        </div>
        <div style="display:flex;flex-wrap:wrap;gap:8px;justify-content:flex-end;">
          <a href="{{ route('post') }}" class="btn btn-sm btn-outline">{{ __('public.posts.page_title') }}</a>
          <a href="{{ route('teacher') }}" class="btn btn-sm btn-outline">{{ __('public.teachers.page_title') }}</a>
          <a href="{{ route('courses') }}" class="btn btn-sm btn-prime">{{ __('public.courses.page_title') }}</a>
        </div>
      </div>

      @if($bookmarks->isEmpty())
        <p class="comment-hint">{{ __('profile.bookmarks.empty') }}</p>
      @else
        <div class="post-grid prime-stagger">
          @foreach($bookmarks as $bookmark)
            @continue(!$bookmark->bookmarkable)
            @if($bookmark->bookmarkable instanceof Post)
              @include('profile.bookmarks.partials.bookmark-post', compact('bookmark', 'likedPostIds', 'bookmarkedPostIds'))
            @elseif($bookmark->bookmarkable instanceof Teacher)
              @include('profile.bookmarks.partials.bookmark-teacher', compact('bookmark', 'bookmarkedTeacherIds'))
            @elseif($bookmark->bookmarkable instanceof Course)
              @include('profile.bookmarks.partials.bookmark-course', compact('bookmark', 'bookmarkedCourseIds'))
            @endif
          @endforeach
        </div>

        @if($bookmarks->hasPages())
          <div class="news-pagination" style="margin-top: 28px;">
            @if ($bookmarks->onFirstPage())
              <span class="btn btn-sm btn-outline" aria-disabled="true">{{ __('public.posts.previous') }}</span>
            @else
              <a class="btn btn-sm btn-outline" href="{{ $bookmarks->previousPageUrl() }}">{{ __('public.posts.previous') }}</a>
            @endif

            <span class="news-page-info">
              {{ $bookmarks->currentPage() }} / {{ $bookmarks->lastPage() }}
            </span>

            @if($bookmarks->hasMorePages())
              <a class="btn btn-sm" href="{{ $bookmarks->nextPageUrl() }}">{{ __('public.posts.next') }}</a>
            @else
              <span class="btn btn-sm" aria-disabled="true">{{ __('public.posts.next') }}</span>
            @endif
          </div>
        @endif
      @endif
    </section>
  </main>
</x-loyouts.main>
