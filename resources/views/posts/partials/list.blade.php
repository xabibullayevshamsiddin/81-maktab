@php $likedPostIds = $likedPostIds ?? collect(); @endphp
@php $bookmarkedPostIds = $bookmarkedPostIds ?? collect(); @endphp

<div class="post-grid prime-stagger">
  @forelse($posts as $post)
    @include('posts.partials.post-card', [
      'post' => $post,
      'likedPostIds' => $likedPostIds,
      'bookmarkedPostIds' => $bookmarkedPostIds,
    ])
  @empty
    <p>{{ __('public.posts.empty') }}</p>
  @endforelse
</div>

@if($posts->hasPages())
  <div class="news-pagination">
    @if ($posts->onFirstPage())
      <span class="btn btn-sm btn-outline" aria-disabled="true">{{ __('public.posts.previous') }}</span>
    @else
      <a class="btn btn-sm btn-outline" href="{{ $posts->previousPageUrl() }}">{{ __('public.posts.previous') }}</a>
    @endif

    <span class="news-page-info">
      {{ $posts->currentPage() }} / {{ $posts->lastPage() }}
    </span>

    @if ($posts->hasMorePages())
      <a class="btn btn-sm" href="{{ $posts->nextPageUrl() }}">{{ __('public.posts.next') }}</a>
    @else
      <span class="btn btn-sm" aria-disabled="true">{{ __('public.posts.next') }}</span>
    @endif
  </div>
@endif
