@php
  $postTitle = localized_model_value($post, 'title');
  $postShort = localized_model_value($post, 'short_content');
  $postContent = localized_model_value($post, 'content');
  $postCategory = localized_model_value($post->category, 'name');
@endphp
<x-loyouts.main title="81-IDUM | {{ $postTitle }}">
  <section class="news-hero" id="home">
    <div class="container">
      <div class="news-hero-content reveal">
        <h1 class="js-split-text">{{ $postTitle }}</h1>
        @if($post->category)
          <p>{{ $postCategory }}</p>
        @endif
      </div>
      <a href="{{ route('post') }}" class="btn">
        {{ __('public.posts.show_back') }} <i class="fa-solid fa-arrow-left" style="margin-left: 6px"></i>
      </a>
    </div>
  </section>

  @php
    $commentLikeUrlTemplate = str_replace(
        '/comments/0/',
        '/comments/__COMMENT_ID__/',
        route('post.comments.like', ['post' => $post, 'comment' => 0])
    );
    $postCommentConfig = [
      'currentUserId' => auth()->check() ? auth()->id() : null,
      'currentUserIsAdmin' => auth()->check() && auth()->user()->isAdmin(),
      'currentUserIsModerator' => auth()->check() && auth()->user()->isModerator(),
      'currentUserIsOnlyModerator' => auth()->check() && auth()->user()->isOnlyModerator(),
      'updateUrlTemplate' => route('post.comments.update', [$post, '__COMMENT_ID__']),
      'destroyUrlTemplate' => route('post.comments.destroy', [$post, '__COMMENT_ID__']),
      'commentLikeUrlTemplate' => $commentLikeUrlTemplate,
      'storeUrl' => route('post.comments.store', $post),
      'csrfToken' => csrf_token(),
    ];
  @endphp
  <main class="news">
    <section
      class="container news reveal glass-section"
      id="post-detail"
      data-comment-config='@json($postCommentConfig)'
    >
      @if (session('success'))
        <p style="margin: 0 0 12px; color: #0f766e; font-weight: 700;">
          {{ session('success') }}
        </p>
      @endif

      @php
        $ytEmbed = $post->video_url ? \App\Support\YoutubeEmbed::parse($post->video_url) : null;
        $videoExt = filled($post->video_path) ? strtolower(pathinfo($post->video_path, PATHINFO_EXTENSION)) : '';
      @endphp
      <article class="news-card post-detail-card">
        <div class="post-detail-media">
          @if($post->hasVideo())
            @if(filled($post->video_path))
              <video class="post-detail-video-native" controls playsinline preload="metadata" title="{{ $postTitle }}">
                <source
                  src="{{ app_storage_asset($post->video_path) }}"
                  type="{{ $videoExt === 'webm' ? 'video/webm' : 'video/mp4' }}"
                />
                {{ __('public.posts.browser_no_video') }}
              </video>
            @elseif($ytEmbed)
              <div class="post-video-embed post-video-embed--detail-hero">
                <div class="post-video-embed-inner">
                  <iframe
                    src="{{ $ytEmbed[0] }}"
                    title="Video: {{ $postTitle }}"
                    loading="lazy"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                    allowfullscreen
                  ></iframe>
                </div>
              </div>
            @elseif(filled($post->video_url))
              <div class="post-detail-video-external">
                <a class="btn" href="{{ $post->video_url }}" target="_blank" rel="noopener noreferrer">
                  <i class="fa-solid fa-up-right-from-square"></i> {{ __('public.posts.open_video') }}
                </a>
              </div>
            @endif
          @else
            <img
              src="{{ app_storage_asset($post->image) }}"
              alt="{{ $postTitle }}"
              class="js-image-zoom-trigger zoomable-image"
              data-zoom-src="{{ app_storage_asset($post->image) }}"
              loading="lazy"
              decoding="async"
              role="button"
              tabindex="0"
            />
          @endif
        </div>

        @if($post->category)
          <div style="padding: 12px 16px 0;">
            <span class="badge">
              {{ $postCategory }}
            </span>
          </div>
        @endif

        <div class="icon-links" style="padding-top: 8px;">
          <div class="icon-link">
            <span class="meta"><i class="fa-regular fa-eye"></i> {{ $post->views }}</span>
            <span class="meta"><i class="fa-regular fa-comment"></i> <span class="comment-count">{{ $post->comments_count }}</span></span>

            @php $postLikedByMe = isset($likedPostIds) && $likedPostIds->contains($post->id); @endphp
            <form action="{{ route('post.like', $post) }}" method="POST" class="js-like-form">
              @csrf
              <button class="like-btn {{ $postLikedByMe ? 'liked' : '' }}" type="submit" aria-label="{{ __('public.posts.like_aria') }}">
                <i class="{{ $postLikedByMe ? 'fa-solid' : 'fa-regular' }} fa-heart"></i>
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
              data-share-text="{{ __('public.posts.share_text') }}"
              data-share-success="{{ __('public.posts.share_success') }}"
            >
              <i class="fa-solid fa-share-nodes"></i> {{ __('public.common.share') }}
            </button>
          </div>
        </div>

        <h3>{{ $postTitle }}</h3>
        <p>{{ $postShort }}</p>

        <div class="post-content">
          {!! nl2br(e($postContent)) !!}
        </div>

        @php
          $canEdit = auth()->check() && auth()->user()->canAccessDashboard();
        @endphp

        @if($canEdit)
          <div class="post-admin-actions">
            <a href="{{ route('posts.edit', $post->id) }}" class="btn btn-sm post-admin-btn post-admin-btn-edit">Tahrirlash</a>
            <form action="{{ route('posts.destroy', $post->id) }}" method="POST" data-confirm="Postni o'chirmoqchimisiz?" data-confirm-title="Postni o'chirish" data-confirm-variant="danger" data-confirm-ok="O'chirish">
              @csrf
              @method('DELETE')
              <button type="submit" class="btn btn-sm post-admin-btn post-admin-btn-delete">O'chirish</button>
            </form>
          </div>
        @endif
      </article>

      <div class="comments-wrapper" style="display:grid;">
        <div class="comments-list">
          @if ($comments->isEmpty())
            <p class="comment-empty">{{ __('public.posts.comments_empty') }}</p>
          @else
            @foreach($comments as $comment)
              @include('posts.partials.comment-item', ['comment' => $comment, 'post' => $post, 'showReplyForm' => true, 'likedCommentIds' => $likedCommentIds])
            @endforeach
          @endif
        </div>

        <div class="comment-form-box reveal">
          <div style="display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:10px;margin-bottom:12px;">
            <h3 style="margin:0;"><i class="fa-solid fa-pen-to-square"></i> {{ __('public.posts.leave_comment') }}</h3>
            <x-site-rule-items area="comment" />
          </div>

          @auth
            <form class="comment-form js-comment-form" action="{{ route('post.comments.store', $post) }}" method="POST">
              @csrf
              <textarea
                rows="4"
                class="comment-input"
                name="body"
                placeholder="{{ __('public.posts.comment_placeholder') }}"
                maxlength="100"
                required
              >{{ old('body') }}</textarea>

              <button type="submit" class="btn">
                <i class="fa-solid fa-paper-plane"></i> {{ __('public.posts.submit_comment') }}
              </button>
            </form>
          @else
            <p class="comment-hint" style="margin-bottom: 10px;">
              <i class="fa-solid fa-lock"></i> Izoh yozish uchun avval tizimga kiring.
            </p>
            <div style="display:flex;gap:10px;flex-wrap:wrap;">
              <a href="{{ route('login') }}" class="btn btn-outline">{{ __('public.common.login') }}</a>
              <a href="{{ route('register') }}" class="btn">{{ __('public.common.register') }}</a>
            </div>
          @endauth

          <p class="comment-hint">
            <i class="fa-solid fa-info-circle"></i> {{ __('public.posts.comment_hint') }}
          </p>
        </div>
      </div>

      @if(isset($relatedPosts) && $relatedPosts->isNotEmpty())
        <section class="related-posts-section reveal" aria-labelledby="related-posts-heading">
          <h2 id="related-posts-heading" class="js-split-text related-section-title">
            {{ __('public.posts.related_title') }}
          </h2>
          @include('posts.partials.related-grid', ['relatedPosts' => $relatedPosts, 'likedPostIds' => $likedPostIds])
          <p class="related-section-more">
            <a href="{{ route('post') }}" class="btn btn-outline btn-sm">{{ __('public.posts.related_all') }}</a>
          </p>
        </section>
      @endif
    </section>
  </main>
</x-loyouts.main>
