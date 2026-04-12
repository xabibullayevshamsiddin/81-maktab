@php
  $likedPostIds = $likedPostIds ?? collect();
@endphp

<div class="post-grid related-posts-grid">
  @foreach($relatedPosts as $rpost)
    @php
      $postTitle = localized_model_value($rpost, 'title');
      $postShort = localized_model_value($rpost, 'short_content');
      $postCategory = localized_model_value($rpost->category, 'name');
      $kindLabel = localized_post_kind_label($rpost->post_kind ?? 'general');
    @endphp
    <article class="news-card post-card">
      <img
        src="{{ app_storage_asset($rpost->image) }}"
        alt="{{ $postTitle }}"
        class="js-image-zoom-trigger zoomable-image"
        data-zoom-src="{{ app_storage_asset($rpost->image) }}"
        loading="lazy"
        decoding="async"
        role="button"
        tabindex="0"
      />

      @if($rpost->category || $rpost->hasVideo() || $kindLabel)
        <div style="padding: 0 16px; margin-top: 10px; display:flex; flex-wrap:wrap; gap:8px;">
          @if($rpost->category)
            <span class="badge" style="margin-bottom: 0;">{{ $postCategory }}</span>
          @endif
          @if($kindLabel)
            <span class="badge" style="margin-bottom: 0; background: rgba(21, 101, 192, 0.1); color: var(--primary-2);">{{ $kindLabel }}</span>
          @endif
          @if($rpost->hasVideo())
            <span class="badge" style="margin-bottom: 0; background: rgba(220, 38, 38, 0.12); color: #b91c1c;">{{ __('public.common.video') }}</span>
          @endif
        </div>
      @endif

      <h3>{{ $postTitle }}</h3>
      <p>{{ $postShort }}</p>

      <div class="icon-links">
        <div class="icon-link">
          <span class="meta"><i class="fa-regular fa-eye"></i> {{ $rpost->views }}</span>
          <span class="meta"><i class="fa-regular fa-comment"></i> <span class="comment-count">{{ $rpost->comments_count }}</span></span>

          <form action="{{ route('post.like', $rpost) }}" method="POST" class="js-like-form">
            @csrf
            <button class="like-btn {{ $likedPostIds->contains($rpost->id) ? 'liked' : '' }}" type="submit" aria-label="{{ __('public.posts.like_aria') }}">
              <i class="{{ $likedPostIds->contains($rpost->id) ? 'fa-solid' : 'fa-regular' }} fa-heart"></i>
              <span class="like-count">{{ $rpost->likes_count }}</span>
            </button>
          </form>
        </div>
        <div class="icon-link-actions">
          <button
            type="button"
            class="btn btn-sm btn-outline share-btn js-share-trigger"
            data-share-url="{{ route('post.show', $rpost) }}"
            data-share-title="{{ $postTitle }}"
            data-share-text="{{ __('public.posts.share_text') }}"
            data-share-success="{{ __('public.posts.share_success') }}"
          >
            <i class="fa-solid fa-share-nodes"></i> {{ __('public.common.share') }}
          </button>
        </div>
      </div>

      <a href="{{ route('post.show', $rpost) }}" class="btn btn-sm" style="margin: 0 16px 16px;">{{ __('public.common.details') }}</a>
    </article>
  @endforeach
</div>
