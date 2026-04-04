<x-loyouts.main title="81-IDUM | {{ $post->title }}">
  <section class="news-hero" id="home">
    <div class="container">
      <div class="news-hero-content reveal">
        <h1>{{ $post->title }}</h1>
        @if($post->category)
          <p>{{ $post->category->name }}</p>
        @endif
      </div>
      <a href="{{ route('post') }}" class="btn">
        Orqaga <i class="fa-solid fa-arrow-left" style="margin-left: 6px"></i>
      </a>
    </div>
  </section>

  @php
    $commentLikeUrlTemplate = str_replace(
        '/comments/0/',
        '/comments/__COMMENT_ID__/',
        route('post.comments.like', ['post' => $post, 'comment' => 0])
    );
  @endphp
  <main class="news">
    <section class="container news reveal glass-section" id="post-detail">
      <script>
        window.__POST_COMMENTS_CONFIG__ = {
          currentUserId: @json(auth()->check() ? auth()->id() : null),
          currentUserIsAdmin: @json(auth()->check() && auth()->user()->isAdmin()),
          currentUserIsModerator: @json(auth()->check() && auth()->user()->hasRole('moderator')),
          currentUserIsOnlyModerator: @json(auth()->check() && auth()->user()->isOnlyModerator()),
          updateUrlTemplate: @json(route('post.comments.update', [$post, '__COMMENT_ID__'])),
          destroyUrlTemplate: @json(route('post.comments.destroy', [$post, '__COMMENT_ID__'])),
          commentLikeUrlTemplate: @json($commentLikeUrlTemplate),
          storeUrl: @json(route('post.comments.store', $post)),
          csrfToken: @json(csrf_token()),
        };
      </script>
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
              <video class="post-detail-video-native" controls playsinline preload="metadata" title="{{ $post->title }}">
                <source
                  src="{{ asset('storage/'.$post->video_path) }}"
                  type="{{ $videoExt === 'webm' ? 'video/webm' : 'video/mp4' }}"
                />
                Brauzeringiz video qo‘llab-quvvatlamaydi.
              </video>
            @elseif($ytEmbed)
              <div class="post-video-embed post-video-embed--detail-hero">
                <div class="post-video-embed-inner">
                  <iframe
                    src="{{ $ytEmbed[0] }}"
                    title="Video: {{ $post->title }}"
                    loading="lazy"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                    allowfullscreen
                  ></iframe>
                </div>
              </div>
            @elseif(filled($post->video_url))
              <div class="post-detail-video-external">
                <a class="btn" href="{{ $post->video_url }}" target="_blank" rel="noopener noreferrer">
                  <i class="fa-solid fa-up-right-from-square"></i> Videoni ochish
                </a>
              </div>
            @endif
          @else
            <img
              src="{{ asset('storage/' . $post->image) }}"
              alt="{{ $post->title }}"
              class="js-image-zoom-trigger zoomable-image"
              data-zoom-src="{{ asset('storage/' . $post->image) }}"
              role="button"
              tabindex="0"
            />
          @endif
        </div>

        @if($post->category)
          <div style="padding: 12px 16px 0;">
            <span class="badge">
              {{ $post->category->name }}
            </span>
          </div>
        @endif

        <div class="icon-links" style="padding-top: 8px;">
          <div class="icon-link">
            <span class="meta"><i class="fa-regular fa-eye"></i> {{ $post->views }}</span>
            <span class="meta"><i class="fa-regular fa-comment"></i> <span class="comment-count">{{ $post->comments_count }}</span></span>

            @php $postLikedByMe = isset($likedPostIds) && $likedPostIds->contains($post->id); @endphp
            <form action="{{ route('post.like', $post) }}" method="POST" style="display:inline;" class="js-like-form">
              @csrf
              <button class="like-btn {{ $postLikedByMe ? 'liked' : '' }}" type="submit" aria-label="Yoqtirish" style="padding-left: 10px;">
                <i class="{{ $postLikedByMe ? 'fa-solid' : 'fa-regular' }} fa-heart"></i>
                <span class="like-count">{{ $post->likes_count }}</span>
              </button>
            </form>
          </div>
        </div>

        <h3>{{ $post->title }}</h3>
        <p>{{ $post->short_content }}</p>

        <div class="post-content">
          {!! nl2br(e($post->content)) !!}
        </div>

        @php
          $canEdit = auth()->check() && auth()->user()->canAccessDashboard();
        @endphp

        @if($canEdit)
          <div class="post-admin-actions">
            <a href="{{ route('posts.edit', $post->id) }}" class="btn btn-sm post-admin-btn post-admin-btn-edit">Tahrirlash</a>
            <form action="{{ route('posts.destroy', $post->id) }}" method="POST" onsubmit="return confirm('Postni o\'chirmoqchimisiz?')">
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
            <p class="comment-empty">Hozircha izohlar yo'q.</p>
          @else
            @foreach($comments as $comment)
              @include('posts.partials.comment-item', ['comment' => $comment, 'post' => $post, 'showReplyForm' => true, 'likedCommentIds' => $likedCommentIds])
            @endforeach
          @endif
        </div>

        <div class="comment-form-box reveal">
          <div style="display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:10px;margin-bottom:12px;">
            <h3 style="margin:0;"><i class="fa-solid fa-pen-to-square"></i> Izoh qoldiring</h3>
            <x-site-rule-items area="comment" />
          </div>

          <form class="comment-form js-comment-form" action="{{ route('post.comments.store', $post) }}" method="POST">
            @csrf

            @guest
              <input
                type="text"
                class="comment-input"
                name="author_name"
                placeholder="Ismingiz (ixtiyoriy)"
                maxlength="80"
                value="{{ old('author_name') }}"
              />
            @endguest

            <textarea
              rows="4"
              class="comment-input"
              name="body"
              placeholder="Fikringizni yozing..."
              maxlength="500"
              required
            >{{ old('body') }}</textarea>

            <button type="submit" class="btn">
              <i class="fa-solid fa-paper-plane"></i> Yuborish
            </button>
          </form>

          <p class="comment-hint">
            <i class="fa-solid fa-info-circle"></i> Izohingiz moderator tomonidan ko'rib chiqiladi.
          </p>
        </div>
      </div>
    </section>
  </main>
</x-loyouts.main>
