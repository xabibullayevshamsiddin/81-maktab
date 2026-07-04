@php
  $likedPostIds = $likedPostIds ?? collect();
  $bookmarkedPostIds = $bookmarkedPostIds ?? collect();
  $postTitle = localized_model_value($post, 'title');
  $postShort = localized_model_value($post, 'short_content');
  $postCategory = localized_model_value($post->category, 'name');
  $kindLabel = localized_post_kind_label($post->post_kind ?? 'general');
  $detailsBtnClass = $detailsBtnClass ?? 'btn btn-sm btn-prime';
  $detailsBtnStyle = $detailsBtnStyle ?? 'margin: 0 16px 16px;';
  $shareTextFinal = $shareText ?? __('public.posts.share_text');
  $shareSuccessFinal = $shareSuccess ?? __('public.posts.share_success');
@endphp
<article class="news-card post-card prime-glow-hover">
  <img
    src="{{ app_storage_asset($post->image) }}"
    alt="{{ $postTitle }}"
    class="js-image-zoom-trigger zoomable-image"
    data-zoom-src="{{ app_storage_asset($post->image) }}"
    onerror="this.src='{{ app_public_asset('temp/img/ChatGPT Image Jul 5, 2026, 01_48_59 AM.png') }}'; this.onerror=null; this.removeAttribute('data-zoom-src');"
    loading="lazy"
    decoding="async"
    role="button"
    tabindex="0"
  />

  @if($post->category || $post->hasVideo() || $kindLabel)
    <div style="padding: 0 16px; margin-top: 10px; display:flex; flex-wrap:wrap; gap:8px;">
      @if($post->category)
        <span class="badge" style="margin-bottom: 0;">{{ $postCategory }}</span>
      @endif
      @if($kindLabel)
        <span class="badge" style="margin-bottom: 0; background: rgba(21, 101, 192, 0.1); color: var(--primary-2);">{{ $kindLabel }}</span>
      @endif
      @if($post->hasVideo())
        <span class="badge" style="margin-bottom: 0; background: rgba(220, 38, 38, 0.12); color: #b91c1c;">{{ __('public.common.video') }}</span>
      @endif
    </div>
  @endif

  <h3>{{ $postTitle }}</h3>
  <p>{{ $postShort }}</p>

  <div class="icon-links">
    <div class="icon-link">
      <span class="meta"><i class="fa-regular fa-eye"></i> {{ $post->views }}</span>
      <span class="meta"><i class="fa-regular fa-comment"></i> <span class="comment-count">{{ $post->comments_count }}</span></span>

      <form action="{{ route('post.like', $post) }}" method="POST" class="js-like-form">
        @csrf
        <button class="like-btn {{ $likedPostIds->contains($post->id) ? 'liked' : '' }}" type="submit" aria-label="{{ __('public.posts.like_aria') }}">
          <i class="{{ $likedPostIds->contains($post->id) ? 'fa-solid' : 'fa-regular' }} fa-heart"></i>
          <span class="like-count">{{ $post->likes_count }}</span>
        </button>
      </form>


    </div>
    <div class="icon-link-actions">
      <button
        type="button"
        class="btn btn-sm btn-outline share-btn js-share-trigger"
        data-share-url="{{ route('post.show', $post) }}"
        data-share-title="{{ $postTitle }}"
        data-share-text="{{ $shareTextFinal }}"
        data-share-success="{{ $shareSuccessFinal }}"
      >
        <i class="fa-solid fa-share-nodes"></i> {{ __('public.common.share') }}
      </button>
    </div>
  </div>

  <a href="{{ route('post.show', $post) }}" class="{{ $detailsBtnClass }}" style="{{ $detailsBtnStyle }}">{{ __('public.common.details') }}</a>
</article>
