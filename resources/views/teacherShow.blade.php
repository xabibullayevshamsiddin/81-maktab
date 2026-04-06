@php
  $teacherSubject = localized_model_value($teacher, 'subject');
  $teacherBio = localized_model_value($teacher, 'bio');
  $teacherAchievements = localized_model_value($teacher, 'achievements');
@endphp
<x-loyouts.main title="81-IDUM | {{ $teacher->full_name }}">
  <section class="sow-hero" id="home">
    <div class="overlay"></div>
    <div class="container">
      <div class="sow-hero-content reveal">
        <span class="badge">{{ __('public.teachers.badge') }}</span>
        <h1>{{ __('public.teachers.detail_title', ['name' => $teacher->full_name]) }}</h1>
        <p>{{ __('public.teachers.detail_text') }}</p>
        <a href="#teachers-detail" class="btn">
          {{ __('public.teachers.detail_jump') }}
          <i class="fa-solid fa-arrow-down" style="margin-left: 6px"></i>
        </a>
      </div>
    </div>
  </section>

  <main>
    <section class="container teachers-detail" id="teachers-detail">
      <div class="detail-grid">
        <div class="detail-content reveal">
          <span class="eyebrow">{{ __('public.teachers.detail_badge') }}</span>
          <h2>{{ $teacherSubject }}</h2>
          <p>
            {{ $teacherBio ?: __('public.teachers.detail_fallback') }}
          </p>
          <ul class="detail-list">
            <li><i class="fa-solid fa-check"></i> {{ __('public.common.years_experience', ['count' => $teacher->experience_years]) }}</li>
            <li><i class="fa-solid fa-check"></i> {{ __('public.teachers.detail_subject') }}: {{ $teacherSubject }}</li>
            <li><i class="fa-solid fa-check"></i> {{ __('public.teachers.detail_grades') }}: {{ $teacher->grades ?: __('public.common.all_grades') }}</li>
          </ul>
          @if(filled($teacherAchievements))
            <div class="teacher-achievements-block">
              <h3 class="teacher-achievements-title"><i class="fa-solid fa-trophy"></i> {{ __('public.teachers.achievements') }}</h3>
              <ul class="detail-list teacher-achievements-list">
                @foreach(preg_split("/\r\n|\r|\n/", $teacherAchievements) as $line)
                  @php $line = trim($line); @endphp
                  @if($line !== '')
                    <li><i class="fa-solid fa-award"></i> {{ $line }}</li>
                  @endif
                @endforeach
              </ul>
            </div>
          @endif
          @auth
            <form action="{{ route('teacher.like', $teacher) }}" method="POST" class="js-like-form" style="margin-bottom: 14px;">
              @csrf
              <button class="like-btn {{ ($liked ?? false) ? 'liked' : '' }}" type="submit" aria-label="{{ __('public.posts.like_aria') }}">
                <i class="{{ ($liked ?? false) ? 'fa-solid' : 'fa-regular' }} fa-heart"></i>
                <span class="like-count">{{ $teacher->likes_count ?? 0 }}</span>
              </button>
            </form>
          @endauth
          <div class="teacher-detail-actions">
            <a href="{{ route('teacher') }}" class="btn">{{ __('public.teachers.back_to_teachers') }}</a>
            <button
              type="button"
              class="btn btn-outline share-btn js-share-trigger"
              data-share-url="{{ route('teacher.show', $teacher) }}"
              data-share-title="{{ $teacher->full_name }}"
              data-share-text="{{ __('public.teachers.share_text') }}"
              data-share-success="{{ __('public.teachers.share_success') }}"
            >
              <i class="fa-solid fa-share-nodes"></i> {{ __('public.common.share') }}
            </button>
          </div>
        </div>

        <article class="detail-image-card reveal">
          <img
            src="{{ $teacher->image ? asset('storage/' . $teacher->image) : asset('temp/img/how-to-be-teacher-malaysia-feature.png') }}"
            alt="{{ $teacher->full_name }} rasmi"
            loading="lazy"
            decoding="async"
          />
          <div class="image-caption">
            <h3>{{ $teacher->full_name }}</h3>
            <p>{{ $teacherSubject }}</p>
          </div>
        </article>
      </div>
    </section>

    @php
      $teacherCommentLikeUrlTemplate = str_replace(
          '/0/like',
          '/__COMMENT_ID__/like',
          route('teacher.comments.like', ['comment' => 0])
      );
      $teacherCommentConfig = [
        'currentUserId' => auth()->check() ? auth()->id() : null,
        'currentUserIsAdmin' => auth()->check() && auth()->user()->isAdmin(),
        'currentUserIsModerator' => auth()->check() && auth()->user()->hasRole('moderator'),
        'currentUserIsOnlyModerator' => auth()->check() && auth()->user()->isOnlyModerator(),
        'updateUrlTemplate' => route('teacher.comments.update', '__COMMENT_ID__'),
        'destroyUrlTemplate' => route('teacher.comments.destroy', '__COMMENT_ID__'),
        'commentLikeUrlTemplate' => $teacherCommentLikeUrlTemplate,
        'storeUrl' => route('teacher.comments.store', $teacher),
        'csrfToken' => csrf_token(),
      ];
    @endphp
    <section
      class="container comments-section"
      id="post-detail"
      data-comment-config='@json($teacherCommentConfig)'
    >

      <div class="section-head">
        <h2>{{ __('public.teachers.comments_title') }}</h2>
        <p>{{ __('public.teachers.comments_text') }}</p>
      </div>

      <div class="comments-stats reveal">
        <div class="stat-card">
          <span class="stat-icon"><i class="fa-solid fa-comments"></i></span>
          <span class="stat-num">{{ $comments->count() }}</span>
          <span class="stat-label">{{ __('public.teachers.comments_count') }}</span>
        </div>
        <div class="stat-card">
          <span class="stat-icon"><i class="fa-solid fa-star"></i></span>
          <span class="stat-num">4.9</span>
          <span class="stat-label">{{ __('public.teachers.rating') }}</span>
        </div>
        <div class="stat-card">
          <span class="stat-icon"><i class="fa-solid fa-heart"></i></span>
          <span class="stat-num">1.2k</span>
          <span class="stat-label">{{ __('public.teachers.likes') }}</span>
        </div>
      </div>

      <div class="comments-wrapper">
        <div class="comments-list">
          @if ($comments->isEmpty())
            <p class="comment-empty">{{ __('public.posts.comments_empty') }}</p>
          @else
            @foreach($comments as $comment)
              @include('teacher.partials.comment-item', ['comment' => $comment, 'teacher' => $teacher, 'showReplyForm' => true, 'likedCommentIds' => $likedCommentIds])
            @endforeach
          @endif
        </div>

        <div class="comment-form-box reveal">
          <div style="display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:10px;margin-bottom:12px;">
            <h3 style="margin:0;"><i class="fa-solid fa-pen-to-square"></i> {{ __('public.posts.leave_comment') }}</h3>
            <x-site-rule-items area="comment" />
          </div>
          <form class="comment-form js-comment-form" action="{{ route('teacher.comments.store', $teacher) }}" method="POST">
            @csrf

            @guest
              <input
                type="text"
                class="comment-input"
                name="author_name"
                placeholder="{{ __('public.posts.guest_name') }}"
                maxlength="80"
                value="{{ old('author_name') }}"
              />
            @endguest

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
          <p class="comment-hint">
            <i class="fa-solid fa-info-circle"></i> {{ __('public.posts.comment_hint') }}
          </p>
        </div>
      </div>
    </section>
  </main>
</x-loyouts.main>
