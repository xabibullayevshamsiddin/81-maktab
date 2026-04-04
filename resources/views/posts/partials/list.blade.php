@php $likedPostIds = $likedPostIds ?? collect(); @endphp

<div class="post-grid">
  @forelse($posts as $post)
    <article class="news-card post-card">
      <img
        src="{{ asset('storage/' . $post->image) }}"
        alt="{{ $post->title }}"
        class="js-image-zoom-trigger zoomable-image"
        data-zoom-src="{{ asset('storage/' . $post->image) }}"
        role="button"
        tabindex="0"
      />

      @php
        $pk = $post->post_kind ?? 'general';
        $kindLabel = $postKindLabels[$pk]['label'] ?? null;
      @endphp
      @if($post->category || $post->hasVideo() || $kindLabel)
        <div style="padding: 0 16px; margin-top: 10px; display:flex; flex-wrap:wrap; gap:8px;">
          @if($post->category)
            <span class="badge" style="margin-bottom: 0;">{{ $post->category->name }}</span>
          @endif
          @if($kindLabel)
            <span class="badge" style="margin-bottom: 0; background: rgba(21, 101, 192, 0.1); color: var(--primary-2);">{{ $kindLabel }}</span>
          @endif
          @if($post->hasVideo())
            <span class="badge" style="margin-bottom: 0; background: rgba(220, 38, 38, 0.12); color: #b91c1c;">Video</span>
          @endif
        </div>
      @endif

      <h3>{{ $post->title }}</h3>
      <p>{{ $post->short_content }}</p>

      <div class="icon-links">
        <div class="icon-link">
          <span class="meta"><i class="fa-regular fa-eye"></i> {{ $post->views }}</span>
          <span class="meta"><i class="fa-regular fa-comment"></i> <span class="comment-count">{{ $post->comments_count }}</span></span>

          <form action="{{ route('post.like', $post) }}" method="POST" style="margin-left: 10px;" class="js-like-form">
            @csrf
            <button class="like-btn {{ $likedPostIds->contains($post->id) ? 'liked' : '' }}" type="submit" aria-label="Yoqtirish">
              <i class="{{ $likedPostIds->contains($post->id) ? 'fa-solid' : 'fa-regular' }} fa-heart"></i>
              <span class="like-count">{{ $post->likes_count }}</span>
            </button>
          </form>
        </div>
      </div>

      <a href="{{ route('post.show', $post) }}" class="btn btn-sm" style="margin: 0 16px 16px;">Batafsil</a>
    </article>
  @empty
    <p>Hozircha yangiliklar yo'q.</p>
  @endforelse
</div>

@if($posts->hasPages())
  <div class="news-pagination">
    @if ($posts->onFirstPage())
      <span class="btn btn-sm btn-outline" aria-disabled="true">Oldingi</span>
    @else
      <a class="btn btn-sm btn-outline" href="{{ $posts->previousPageUrl() }}">Oldingi</a>
    @endif

    <span class="news-page-info">
      {{ $posts->currentPage() }} / {{ $posts->lastPage() }}
    </span>

    @if ($posts->hasMorePages())
      <a class="btn btn-sm" href="{{ $posts->nextPageUrl() }}">Keyingi</a>
    @else
      <span class="btn btn-sm" aria-disabled="true">Keyingi</span>
    @endif
  </div>
@endif
