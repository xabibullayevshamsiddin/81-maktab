@php
  $showReplyForm = $showReplyForm ?? true;
  $likedCommentIds = $likedCommentIds ?? collect();
  $commentIsLiked = auth()->check() && $likedCommentIds->contains($comment->id);
  $authUser = auth()->user();
  $canManageComment = $authUser && $authUser->canManageCommentAsStaff($comment->user, $comment->user_id);

  $avatarAccent = (isset($comment->id) && ((int) $comment->id % 2) === 0);
  $avatarUrl = $comment->user?->avatar_url;
  $avatarInitial = \Illuminate\Support\Str::upper(
    \Illuminate\Support\Str::substr(trim((string) ($comment->author_name ?: "M")), 0, 1)
  );
  $roleKey = $comment->user?->role ?? "guest";
  $roleLabel = $comment->user?->role_label ?? "Mehmon";
  $commentBodyMax = $comment->parent_id ? 50 : 100;

  $userTheme = $comment->user?->effectiveTheme();
  $donorRank = $comment->user?->donation_rank;
  $donorBadge = $comment->user?->donorBadgeHtml() ?? "";
  $effectTheme = $userTheme ?: $donorRank;
  $effectThemeType = $effectTheme ? (\App\Models\Donation::themeConfig($effectTheme)["type"] ?? null) : null;
  $commentStyleClass = $effectTheme ? ("comment-style--" . ($comment->user?->comment_style ?? "border")) : "";
  $roleCardClass = match ($roleKey) {
    "super_admin" => "comment-card--super-admin",
    "admin" => "comment-card--admin",
    "moderator" => "comment-card--moderator",
    default => $effectTheme && $effectThemeType === "donor" ? ("comment-card--donor comment-card--donor-" . $effectTheme) : "",
  };
  $themeOverlayClass = in_array($roleKey, ['super_admin', 'admin', 'moderator']) && $effectTheme
    ? ($effectThemeType === "donor" ? ("comment-card--donor comment-card--donor-" . $effectTheme) : ("comment-card--theme-" . $effectTheme))
    : "";

  // Badge pozitsiyasi va status emoji
  $badgePos    = $comment->user?->badge_position ?? 'after';
  $statusEmoji = $comment->user?->status_emoji ?? '';
@endphp

<article class="comment-card reveal {{ $showReplyForm ? "" : "comment-item-reply" }} {{ $roleCardClass }} {{ $commentStyleClass }} {{ $themeOverlayClass }}" data-comment-id="{{ $comment->id }}">
  @php
    $replyCount = $comment->replies->count();
    $canReplyMore = $replyCount < 4;
  @endphp
  @if(auth()->check() && $comment->user_id)
    <button type="button" class="comment-avatar comment-avatar--btn {{ $avatarAccent ? "accent" : "" }} {{ $avatarUrl ? "comment-avatar--image" : "" }}" data-user-preview-id="{{ $comment->user_id }}" title="{{ __("public.comments.profile_title") }}" aria-label="{{ __("public.comments.profile_aria") }}">
      @if($avatarUrl)
        <img class="comment-avatar-image" src="{{ $avatarUrl }}" alt="" loading="lazy" decoding="async">
      @else
        <span>{{ $avatarInitial }}</span>
      @endif
    </button>
  @else
    <div class="comment-avatar {{ $avatarAccent ? "accent" : "" }} {{ $avatarUrl ? "comment-avatar--image" : "" }}">
      @if($avatarUrl)
        <img class="comment-avatar-image" src="{{ $avatarUrl }}" alt="{{ $comment->author_name ?? "Mehmon" }}" loading="lazy" decoding="async">
      @else
        <span>{{ $avatarInitial }}</span>
      @endif
    </div>
  @endif

  <div class="comment-body">
    <div class="comment-meta">
      @php
        $authorStyle = '';
        if ($effectTheme && $comment->user) {
          $c = $comment->user->donorUsernameColor();
          $w = $comment->user->name_font_weight ?? '700';
          if ($c) { $authorStyle .= 'color:' . $c . ';'; }
          $authorStyle .= 'font-weight:' . $w . ';';
        }
      @endphp
      @if($donorBadge && $badgePos === 'before'){!! $donorBadge !!} @endif
      <strong style="{{ $authorStyle }}">{{ $comment->author_name ?? "Mehmon" }}{{ $statusEmoji ? ' '.$statusEmoji : '' }}</strong>
      @if($donorBadge && $badgePos !== 'before') {!! $donorBadge !!}@endif
      <span class="comment-role-badge role-{{ $roleKey }}">{{ $roleLabel }}</span>
      <span class="comment-date">
        <i class="fa-regular fa-clock"></i>
        {{ $comment->created_at ? $comment->created_at->diffForHumans() : "" }}
      </span>
    </div>

    <p>{{ $comment->body }}</p>

    <div class="comment-actions">
      <form action="{{ route("post.comments.like", [$post, $comment]) }}" method="POST" class="js-like-form">
        @csrf
        <button type="submit" class="like-btn comment-like {{ $commentIsLiked ? "liked" : "" }}" aria-label="Yoqtirish">
          <i class="{{ $commentIsLiked ? "fa-solid" : "fa-regular" }} fa-heart"></i>
          <span class="like-count">{{ $comment->likes_count ?? 0 }}</span>
        </button>
      </form>

      @if ($showReplyForm && auth()->check() && $canReplyMore)
        <button
          type="button"
          class="btn btn-sm js-reply-trigger"
          data-comment-id="{{ $comment->id }}"
          data-post-id="{{ $post->id }}"
        >
          <i class="fa-solid fa-reply"></i> {{ __("public.comments.reply") }}
        </button>
      @endif

      @if($canManageComment)
      <details class="comment-manage-details">
        <summary class="btn btn-sm comment-manage-btn">
          <i class="fa-solid fa-ellipsis"></i>
        </summary>

        <details class="comment-manage-details">
          <summary class="btn btn-sm">
            <i class="fa-solid fa-pen"></i> {{ __("public.comments.edit") }}
          </summary>
          <form
            class="js-comment-form js-comment-edit-form"
            action="{{ route("post.comments.update", [$post, $comment]) }}"
            method="POST"
            data-comment-id="{{ $comment->id }}"
          >
            @csrf
            @method("PUT")
              <input
                type="text"
                class="comment-input"
                name="body"
                value="{{ $comment->body }}"
                maxlength="{{ $commentBodyMax }}"
                required
              />
            <button class="btn btn-sm" type="submit">Saqlash</button>
          </form>
        </details>

        <form
          class="js-comment-form js-comment-delete-form"
          action="{{ route("post.comments.destroy", [$post, $comment]) }}"
          method="POST"
          data-comment-id="{{ $comment->id }}"
          data-confirm="{{ __("public.comments.delete_confirm") }}" data-confirm-title="{{ __("public.comments.delete_title") }}" data-confirm-variant="danger" data-confirm-ok="{{ __("public.comments.delete_ok") }}"
        >
          @csrf
          @method("DELETE")
          <button type="submit" class="btn btn-sm comment-delete-btn">
            <i class="fa-solid fa-trash" style="margin-right: 8px;"></i> Ochirish
          </button>
        </form>
      </details>
      @endif
    </div>
  </div>

  @if ($comment->replies->isNotEmpty())
    <details class="comment-replies-toggle">
      <summary>{{ __("public.comments.read_replies", ["count" => $replyCount]) }}</summary>
      <div class="comment-list comment-replies">
        @foreach($comment->replies as $reply)
          @include("posts.partials.comment-item", ["comment" => $reply, "post" => $post, "showReplyForm" => false, "likedCommentIds" => $likedCommentIds])
        @endforeach
      </div>
    </details>
  @endif
</article>
