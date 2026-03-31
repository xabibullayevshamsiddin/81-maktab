@php
  $showReplyForm = $showReplyForm ?? true;
  $authUser = auth()->user();
  $canManageComment = $authUser && (
    ((int) ($comment->user_id ?? 0) === (int) $authUser->id
      || $authUser->isAdmin()
      || $authUser->isEditor()
      || $authUser->isModerator()
    )
  );

  // Vizual dizayn uchun avatar rangini navbat bilan farqlaymiz.
  $avatarAccent = (isset($comment->id) && ((int) $comment->id % 2) === 0);
@endphp

<article class="comment-card reveal {{ $showReplyForm ? '' : 'comment-item-reply' }}" data-comment-id="{{ $comment->id }}">
  <div class="comment-avatar {{ $avatarAccent ? 'accent' : '' }}">
    <i class="fa-solid fa-user"></i>
  </div>

  <div class="comment-body">
    <div class="comment-meta">
      <strong>{{ $comment->author_name ?? 'Mehmon' }}</strong>
      <span class="comment-date">
        <i class="fa-regular fa-clock"></i>
        {{ $comment->created_at ? $comment->created_at->diffForHumans() : '' }}
      </span>
    </div>

    <p>{{ $comment->body }}</p>

    <div class="comment-actions">
      <button type="button" class="comment-like" aria-label="Yoqtirish">
        <i class="fa-regular fa-heart"></i>
        <span class="like-count">{{ $comment->likes_count ?? 0 }}</span>
      </button>

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
            action="{{ route('post.comments.store', $post) }}"
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
            action="{{ route('post.comments.update', [$post, $comment]) }}"
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
          action="{{ route('post.comments.destroy', [$post, $comment]) }}"
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
        @include('posts.partials.comment-item', ['comment' => $reply, 'post' => $post, 'showReplyForm' => false])
      @endforeach
    </div>
  @endif
</article>
