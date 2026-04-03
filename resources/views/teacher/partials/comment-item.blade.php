@php
  $showReplyForm = $showReplyForm ?? true;
  $likedCommentIds = $likedCommentIds ?? collect();
  $commentIsLiked = auth()->check() && $likedCommentIds->contains($comment->id);
  $authUser = auth()->user();
  $canManageComment = $authUser && $authUser->canManageCommentAsStaff($comment->user, $comment->user_id);

  $avatarAccent = (isset($comment->id) && ((int) $comment->id % 2) === 0);
  $roleKey = $comment->user?->role ?? 'guest';
  $roleLabel = $comment->user?->role_label ?? 'Mehmon';
@endphp

<article class="comment-card reveal {{ $showReplyForm ? '' : 'comment-item-reply' }} {{ $roleKey === 'super_admin' ? 'comment-card--super-admin' : '' }}" data-comment-id="{{ $comment->id }}">
  <div class="comment-avatar {{ $avatarAccent ? 'accent' : '' }}">
    <i class="fa-solid fa-user"></i>
  </div>

  <div class="comment-body">
    <div class="comment-meta">
      <strong>{{ $comment->author_name ?? 'Mehmon' }}</strong>
      <span class="comment-role-badge role-{{ $roleKey }}">{{ $roleLabel }}</span>
      <span class="comment-date">
        <i class="fa-regular fa-clock"></i>
        {{ $comment->created_at ? $comment->created_at->diffForHumans() : '' }}
      </span>
    </div>

    <p>{{ $comment->body }}</p>

    <div class="comment-actions">
      <form action="{{ route('teacher.comments.like', $comment) }}" method="POST" class="js-like-form" style="display:inline;">
        @csrf
        <button type="submit" class="like-btn comment-like {{ $commentIsLiked ? 'liked' : '' }}" aria-label="Yoqtirish">
          <i class="{{ $commentIsLiked ? 'fa-solid' : 'fa-regular' }} fa-heart"></i>
          <span class="like-count">{{ $comment->likes_count ?? 0 }}</span>
        </button>
      </form>

      @if ($showReplyForm)
        <button
          type="button"
          class="comment-reply js-comment-reply-toggle"
          aria-label="Javob"
          data-reply-parent-id="{{ $comment->id }}"
        >
          <i class="fa-regular fa-comment"></i>
          Javob
        </button>

        <div class="js-comment-reply-form-wrapper comment-reply-form-wrapper" hidden>
          <form
            class="comment-form comment-form-inline js-comment-form js-comment-reply-form"
            action="{{ route('teacher.comments.store') }}"
            method="POST"
          >
            @csrf
            <input type="hidden" name="parent_id" value="{{ $comment->id }}" />

            @guest
              <input
                type="text"
                class="comment-input"
                name="author_name"
                placeholder="Ismingiz (ixtiyoriy)"
                maxlength="80"
              />
            @endguest

            <input
              type="text"
              class="comment-input"
              name="body"
              placeholder="Javobingizni yozing"
              maxlength="500"
              required
            />
            <button class="btn btn-sm" type="submit">Javob yuborish</button>
          </form>
        </div>
      @endif

      @if ($canManageComment)
        <details class="comment-action-box">
          <summary><i class="fa-solid fa-pen" style="margin-right: 6px;"></i> Tahrirlash</summary>
          <form
            class="comment-form comment-form-inline js-comment-form js-comment-edit-form"
            action="{{ route('teacher.comments.update', $comment) }}"
            method="POST"
            data-comment-id="{{ $comment->id }}"
          >
            @csrf
            @method('PUT')
            <input
              type="text"
              class="comment-input"
              name="body"
              value="{{ $comment->body }}"
              maxlength="500"
              required
            />
            <button class="btn btn-sm" type="submit">Saqlash</button>
          </form>
        </details>

        <form
          class="js-comment-form js-comment-delete-form"
          action="{{ route('teacher.comments.destroy', $comment) }}"
          method="POST"
          data-comment-id="{{ $comment->id }}"
          onsubmit="return confirm('Izohni o\\'chirmoqchimisiz?')"
        >
          @csrf
          @method('DELETE')
          <button type="submit" class="btn btn-sm comment-delete-btn">
            <i class="fa-solid fa-trash" style="margin-right: 8px;"></i> O'chirish
          </button>
        </form>
      @endif
    </div>
  </div>

  @if ($comment->replies->isNotEmpty())
    <div class="comment-list comment-replies">
      @foreach($comment->replies as $reply)
        @include('teacher.partials.comment-item', ['comment' => $reply, 'showReplyForm' => false, 'likedCommentIds' => $likedCommentIds])
      @endforeach
    </div>
  @endif
</article>

