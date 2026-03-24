<x-loyouts.main title="81-IDUM | {{ $post->title }}">
  <section class="news-hero" id="home">
    <div class="container">
      <div class="news-hero-content reveal">
        <h1>{{ $post->title }}</h1>
        @if($post->category)
          <p>{{ $post->category->name }}</p>
        @else
          <p>Yangilik</p>
        @endif
      </div>
      <a href="#post" class="btn"
        >Pastga tushish <i class="fa-solid fa-arrow-down" style="margin-left: 6px"></i
      ></a>
    </div>
  </section>

  <main class="news">
    <section class="container news reveal glass-section" id="post">
      <article class="post-detail">
        <div class="post-image-wrapper">
          <img src="{{ asset('storage/' . $post->image) }}" alt="{{ $post->title }}" class="post-detail-image" />
          <div class="news-media-overlay">
            <div class="news-chip">
              <i class="fa-regular fa-newspaper"></i>
              <span>{{ $post->category?->name ?? 'Yangilik' }}</span>
            </div>
          </div>
        </div>

        <div class="post-content-wrapper">
          <h2 class="post-detail-title">{{ $post->title }}</h2>
          
          <div class="post-meta">
            <span><i class="fa-regular fa-eye"></i> {{ $post->views }} ko'rilish</span>
            <span><i class="fa-regular fa-comment"></i> {{ $post->comments_count }} izoh</span>
            <span><i class="fa-regular fa-heart"></i> {{ $post->likes_count }}</span>
          </div>

          <div class="post-detail-body">
            {!! $post->content !!}
          </div>

          <div class="post-actions">
            <form action="{{ route('post.like', $post) }}" method="POST" class="like-form">
              @csrf
              <button class="like-btn {{ $liked ? 'liked' : '' }}" type="submit" id="like-btn">
                <i class="{{ $liked ? 'fa-solid' : 'fa-regular' }} fa-heart"></i>
                <span id="like-text">{{ $liked ? 'Yoqdi' : 'Yoqtirish' }}</span>
              </button>
            </form>
            <a href="{{ route('post') }}" class="btn btn-sm btn-outline">Orqaga</a>
          </div>
        </div>
      </article>

      <div class="comments-section">
        <h3 class="comments-title">
          <i class="fa-regular fa-comments"></i> Izohlar ({{ $post->comments_count }})
        </h3>

        <form class="comment-form ajax-comment-form" action="{{ route('post.comments.store', $post) }}" method="POST">
          @csrf
          @guest
            <input type="text" class="comment-input" name="author_name" placeholder="Ismingiz (ixtiyoriy)" maxlength="80" value="{{ old('author_name') }}" />
          @endguest
          <textarea class="comment-input comment-textarea" name="body" placeholder="Fikringizni yozing..." maxlength="500" required>{{ old('body') }}</textarea>
          <button class="btn" type="submit">Yuborish</button>
        </form>

        @if (session('success'))
          <p class="form-message">{{ session('success') }}</p>
        @endif

        @if ($errors->any())
          <p class="form-message" style="color:#ffb3b3;">{{ $errors->first() }}</p>
        @endif

        @if($post->comments->isEmpty())
          <p class="comment-empty">Hozircha izohlar yo'q. Birinchi bo'lib izoh qoldiring!</p>
        @else
          <ul class="comment-list">
            @foreach($post->comments as $comment)
              @if(!$comment->parent_id)
              <li class="comment-item" id="comment-{{ $comment->id }}">
                <div class="comment-header">
                  <strong class="comment-author">
                    <i class="fa-regular fa-user"></i> {{ $comment->author_name ?? 'Mehmon' }}
                  </strong>
                  <span class="comment-date">{{ $comment->created_at?->format('d.m.Y H:i') }}</span>
                </div>
                <div class="comment-body-wrapper" id="comment-body-{{ $comment->id }}">
                  <p class="comment-body">{{ $comment->body }}</p>
                </div>
                <div class="comment-actions">
                  @auth
                    @php
                      $user = auth()->user();
                      $isAdmin = in_array($user->role, ['admin', 'moderator']);
                      $isOwner = $comment->user_id === $user->id;
                    @endphp
                    @if($isAdmin || $isOwner)
                      <button class="comment-action-btn edit-btn" data-comment-id="{{ $comment->id }}"><i class="fa-solid fa-pen"></i> Tahrirlash</button>
                      <form action="{{ route('comments.destroy', $comment) }}" method="POST" class="ajax-delete-form" data-comment-id="{{ $comment->id }}" style="display:inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="comment-action-btn delete-btn"><i class="fa-solid fa-trash"></i> O'chirish</button>
                      </form>
                    @endif
                    <button class="comment-action-btn reply-btn" data-comment-id="{{ $comment->id }}"><i class="fa-solid fa-reply"></i> Javob</button>
                  @endauth
                </div>
                <div class="comment-edit-form" id="edit-form-{{ $comment->id }}" style="display:none;">
                  <form action="{{ route('comments.update', $comment) }}" method="POST" class="ajax-edit-form" data-comment-id="{{ $comment->id }}">
                    @csrf
                    @method('PUT')
                    <textarea name="body" class="comment-input comment-textarea" maxlength="500" required>{{ $comment->body }}</textarea>
                    <div class="comment-edit-actions">
                      <button type="submit" class="btn btn-sm">Saqlash</button>
                      <button type="button" class="btn btn-sm btn-outline cancel-edit-btn" data-comment-id="{{ $comment->id }}">Bekor</button>
                    </div>
                  </form>
                </div>
                <div class="comment-reply-form" id="reply-form-{{ $comment->id }}" style="display:none;">
                  <form action="/comments/{{ $comment->id }}/reply" method="POST" class="ajax-reply-form" data-comment-id="{{ $comment->id }}">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    @guest
                      <input type="text" class="comment-input" name="author_name" placeholder="Ismingiz (ixtiyoriy)" maxlength="80" />
                    @endguest
                    <textarea name="body" class="comment-input comment-textarea" placeholder="Javobingizni yozing..." maxlength="500" required></textarea>
                    <div class="comment-edit-actions">
                      <button type="submit" class="btn btn-sm">Yuborish</button>
                      <button type="button" class="btn btn-sm btn-outline cancel-reply-btn" data-comment-id="{{ $comment->id }}">Bekor</button>
                    </div>
                  </form>
                </div>
                @if($comment->replies->isNotEmpty())
                <ul class="comment-list comment-replies">
                  @foreach($comment->replies as $reply)
                  <li class="comment-item" id="comment-{{ $reply->id }}">
                    <div class="comment-header">
                      <strong class="comment-author">
                        <i class="fa-regular fa-user"></i> {{ $reply->author_name ?? 'Mehmon' }}
                      </strong>
                      <span class="comment-date">{{ $reply->created_at?->format('d.m.Y H:i') }}</span>
                    </div>
                    <p class="comment-body">{{ $reply->body }}</p>
                    @auth
                      @php
                        $user = auth()->user();
                        $isAdmin = in_array($user->role, ['admin', 'moderator']);
                        $isOwner = $reply->user_id === $user->id;
                      @endphp
                      @if($isAdmin || $isOwner)
                      <div class="comment-actions">
                        <form action="{{ route('comments.destroy', $reply) }}" method="POST" class="delete-comment-form" style="display:inline;">
                          @csrf
                          @method('DELETE')
                          <button type="submit" class="comment-action-btn delete-btn"><i class="fa-solid fa-trash"></i> O'chirish</button>
                        </form>
                      </div>
                      @endif
                    @endauth
                  </li>
                  @endforeach
                </ul>
                @endif
              </li>
              @endif
            @endforeach
          </ul>
        @endif
      </div>

      @if($relatedPosts->isNotEmpty())
      <div class="related-posts-section">
        <h3 class="related-title">
          <i class="fa-solid fa-newspaper"></i> Boshqa yangiliklar
        </h3>
        <div class="news-container">
          @foreach($relatedPosts as $related)
            <article class="news-card">
              <div class="news-media">
                <img src="{{ asset('storage/' . $related->image) }}" alt="{{ $related->title }}" />
                <div class="news-media-overlay">
                  <div class="news-chip">
                    <i class="fa-regular fa-newspaper"></i>
                    <span>{{ $related->category?->name ?? 'Yangilik' }}</span>
                  </div>
                </div>
              </div>
              <div class="news-body">
                <h3>{{ $related->title }}</h3>
                <p>{{ $related->short_content }}</p>
                <ul class="news-meta">
                  <li><i class="fa-regular fa-eye"></i> {{ $related->views }}</li>
                  <li><i class="fa-regular fa-comment"></i> {{ $related->comments_count }}</li>
                </ul>
                <a href="{{ route('post.show', $related) }}" class="btn btn-sm news-cta">Batafsil</a>
              </div>
            </article>
          @endforeach
        </div>
      </div>
      @endif
    </section>
  </main>
</x-loyouts.main>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const likeForm = document.querySelector('.like-form');
  const likeBtn = document.getElementById('like-btn');
  const likeText = document.getElementById('like-text');
  const likeIcon = likeBtn.querySelector('i');

  if (likeForm) {
    likeForm.addEventListener('submit', function(e) {
      e.preventDefault();
      
      fetch(this.action, {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': '{{ csrf_token() }}',
          'Accept': 'application/json'
        }
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          likeBtn.classList.toggle('liked', data.liked);
          likeText.textContent = data.liked ? 'Yoqdi' : 'Yoqtirish';
          likeIcon.className = data.liked ? 'fa-solid fa-heart' : 'fa-regular fa-heart';
        }
      })
      .catch(error => console.error('Error:', error));
    });
  }

  document.querySelectorAll('.edit-btn').forEach(btn => {
    btn.addEventListener('click', function() {
      const commentId = this.dataset.commentId;
      document.getElementById('comment-body-' + commentId).style.display = 'none';
      this.style.display = 'none';
      const replyBtn = document.querySelector('.reply-btn[data-comment-id="' + commentId + '"]');
      if (replyBtn) replyBtn.style.display = 'none';
      document.getElementById('edit-form-' + commentId).style.display = 'block';
    });
  });

  document.querySelectorAll('.cancel-edit-btn').forEach(btn => {
    btn.addEventListener('click', function() {
      const commentId = this.dataset.commentId;
      document.getElementById('comment-body-' + commentId).style.display = 'block';
      document.querySelector('.edit-btn[data-comment-id="' + commentId + '"]').style.display = 'inline-block';
      const replyBtn = document.querySelector('.reply-btn[data-comment-id="' + commentId + '"]');
      if (replyBtn) replyBtn.style.display = 'inline-block';
      document.getElementById('edit-form-' + commentId).style.display = 'none';
    });
  });

  document.querySelectorAll('.reply-btn').forEach(btn => {
    btn.addEventListener('click', function() {
      const commentId = this.dataset.commentId;
      this.style.display = 'none';
      const editBtn = document.querySelector('.edit-btn[data-comment-id="' + commentId + '"]');
      if (editBtn) editBtn.style.display = 'none';
      document.getElementById('reply-form-' + commentId).style.display = 'block';
    });
  });

  document.querySelectorAll('.cancel-reply-btn').forEach(btn => {
    btn.addEventListener('click', function() {
      const commentId = this.dataset.commentId;
      document.querySelector('.reply-btn[data-comment-id="' + commentId + '"]').style.display = 'inline-block';
      const editBtn = document.querySelector('.edit-btn[data-comment-id="' + commentId + '"]');
      if (editBtn) editBtn.style.display = 'inline-block';
      document.getElementById('reply-form-' + commentId).style.display = 'none';
    });
  });

  document.querySelectorAll('.ajax-edit-form').forEach(form => {
    form.addEventListener('submit', function(e) {
      e.preventDefault();
      const commentId = this.dataset.commentId;
      const formData = new FormData(this);
      
      fetch(this.action, {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': '{{ csrf_token() }}',
          'Accept': 'application/json'
        },
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          document.getElementById('comment-body-' + commentId).querySelector('p').textContent = data.comment.body;
          document.getElementById('comment-body-' + commentId).style.display = 'block';
          document.querySelector('.edit-btn[data-comment-id="' + commentId + '"]').style.display = 'inline-block';
          const replyBtn = document.querySelector('.reply-btn[data-comment-id="' + commentId + '"]');
          if (replyBtn) replyBtn.style.display = 'inline-block';
          document.getElementById('edit-form-' + commentId).style.display = 'none';
        }
      })
      .catch(error => console.error('Error:', error));
    });
  });

  document.querySelectorAll('.ajax-reply-form').forEach(form => {
    form.addEventListener('submit', function(e) {
      e.preventDefault();
      const commentId = this.dataset.commentId;
      const formData = new FormData(this);
      
      console.log('Submitting reply to comment:', commentId);
      console.log('Action:', this.action);
      console.log('FormData:', Object.fromEntries(formData));
      
      fetch(this.action, {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': '{{ csrf_token() }}',
          'Accept': 'application/json'
        },
        body: formData
      })
      .then(response => {
        console.log('Response status:', response.status);
        return response.json();
      })
      .then(data => {
        console.log('Response data:', data);
        if (data.success) {
          const repliesList = document.querySelector('#comment-' + commentId + ' .comment-replies');
          if (repliesList) {
            const newReply = document.createElement('li');
            newReply.className = 'comment-item';
            newReply.id = 'comment-' + data.reply.id;
            newReply.innerHTML = `
              <div class="comment-header">
                <strong class="comment-author">
                  <i class="fa-regular fa-user"></i> ${data.reply.author_name}
                </strong>
                <span class="comment-date">${data.reply.created_at}</span>
              </div>
              <p class="comment-body">${data.reply.body}</p>
            `;
            repliesList.appendChild(newReply);
          }
          document.getElementById('reply-form-' + commentId).style.display = 'none';
          document.querySelector('.reply-btn[data-comment-id="' + commentId + '"]').style.display = 'inline-block';
          const editBtn = document.querySelector('.edit-btn[data-comment-id="' + commentId + '"]');
          if (editBtn) editBtn.style.display = 'inline-block';
          form.reset();
        }
      })
      .catch(error => console.error('Error:', error));
    });
  });

  document.querySelector('.ajax-comment-form')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const form = this;
    const formData = new FormData(form);
    
    fetch(form.action, {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': '{{ csrf_token() }}',
        'Accept': 'application/json'
      },
      body: formData
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        const commentList = document.querySelector('.comment-list');
        if (commentList) {
          const emptyMsg = document.querySelector('.comment-empty');
          if (emptyMsg) emptyMsg.remove();
          
          const newComment = document.createElement('li');
          newComment.className = 'comment-item';
          newComment.id = 'comment-' + data.comment.id;
          newComment.innerHTML = `
            <div class="comment-header">
              <strong class="comment-author">
                <i class="fa-regular fa-user"></i> ${data.comment.author_name}
              </strong>
              <span class="comment-date">${data.comment.created_at}</span>
            </div>
            <div class="comment-body-wrapper" id="comment-body-${data.comment.id}">
              <p class="comment-body">${data.comment.body}</p>
            </div>
            <div class="comment-actions">
              <button class="comment-action-btn reply-btn" data-comment-id="${data.comment.id}"><i class="fa-solid fa-reply"></i> Javob</button>
            </div>
            <div class="comment-reply-form" id="reply-form-${data.comment.id}" style="display:none;">
              <form action="/comments/${data.comment.id}/reply" method="POST" class="ajax-reply-form" data-comment-id="${data.comment.id}">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <input type="text" class="comment-input" name="author_name" placeholder="Ismingiz (ixtiyoriy)" maxlength="80" />
                <textarea name="body" class="comment-input comment-textarea" placeholder="Javobingizni yozing..." maxlength="500" required></textarea>
                <div class="comment-edit-actions">
                  <button type="submit" class="btn btn-sm">Yuborish</button>
                  <button type="button" class="btn btn-sm btn-outline cancel-reply-btn" data-comment-id="${data.comment.id}">Bekor</button>
                </div>
              </form>
            </div>
            <ul class="comment-list comment-replies"></ul>
          `;
          commentList.appendChild(newComment);
          
          document.querySelectorAll('.reply-btn').forEach(btn => {
            btn.addEventListener('click', function() {
              const commentId = this.dataset.commentId;
              this.style.display = 'none';
              const editBtn = document.querySelector('.edit-btn[data-comment-id="' + commentId + '"]');
              if (editBtn) editBtn.style.display = 'none';
              document.getElementById('reply-form-' + commentId).style.display = 'block';
            });
          });
          
          document.querySelectorAll('.cancel-reply-btn').forEach(btn => {
            btn.addEventListener('click', function() {
              const commentId = this.dataset.commentId;
              document.querySelector('.reply-btn[data-comment-id="' + commentId + '"]').style.display = 'inline-block';
              const editBtn = document.querySelector('.edit-btn[data-comment-id="' + commentId + '"]');
              if (editBtn) editBtn.style.display = 'inline-block';
              document.getElementById('reply-form-' + commentId).style.display = 'none';
            });
          });
          
          document.querySelectorAll('.ajax-reply-form').forEach(form => {
            form.addEventListener('submit', function(e) {
              e.preventDefault();
              const commentId = this.dataset.commentId;
              const formData = new FormData(this);
              
              fetch(this.action, {
                method: 'POST',
                headers: {
                  'X-CSRF-TOKEN': '{{ csrf_token() }}',
                  'Accept': 'application/json'
                },
                body: formData
              })
              .then(response => response.json())
              .then(data => {
                if (data.success) {
                  const repliesList = document.querySelector('#comment-' + commentId + ' .comment-replies');
                  if (repliesList) {
                    const newReply = document.createElement('li');
                    newReply.className = 'comment-item';
                    newReply.id = 'comment-' + data.reply.id;
                    newReply.innerHTML = `
                      <div class="comment-header">
                        <strong class="comment-author">
                          <i class="fa-regular fa-user"></i> ${data.reply.author_name}
                        </strong>
                        <span class="comment-date">${data.reply.created_at}</span>
                      </div>
                      <p class="comment-body">${data.reply.body}</p>
                    `;
                    repliesList.appendChild(newReply);
                  }
                  document.getElementById('reply-form-' + commentId).style.display = 'none';
                  document.querySelector('.reply-btn[data-comment-id="' + commentId + '"]').style.display = 'inline-block';
                  const editBtn = document.querySelector('.edit-btn[data-comment-id="' + commentId + '"]');
                  if (editBtn) editBtn.style.display = 'inline-block';
                  form.reset();
                }
              })
              .catch(error => console.error('Error:', error));
            });
          });
        }
        form.reset();
      }
    })
    .catch(error => console.error('Error:', error));
  });

  document.querySelectorAll('.ajax-delete-form').forEach(form => {
    form.addEventListener('submit', function(e) {
      e.preventDefault();
      if (!confirm('Izohni o\'chirishni istaysizmi?')) return;
      
      const commentId = this.dataset.commentId;
      const formData = new FormData(this);
      
      fetch(this.action, {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': '{{ csrf_token() }}',
          'Accept': 'application/json'
        },
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          document.getElementById('comment-' + commentId)?.remove();
        }
      })
      .catch(error => console.error('Error:', error));
    });
  });
});
</script>