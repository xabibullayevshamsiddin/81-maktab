<x-loyouts.main title="81-IDUM | {{ $teacher->full_name }}">
  <section class="sow-hero" id="home">
    <div class="overlay"></div>
    <div class="container">
      <div class="sow-hero-content reveal">
        <span class="badge">81-IDUM Ustozlar</span>
        <h1>{{ $teacher->full_name }} <strong>haqida</strong></h1>
        <p>
          Kasbiy yondashuv, zamonaviy metodika va o'quvchi natijasiga
          yo'naltirilgan ta'lim modeli haqida qisqacha ma'lumotlar.
        </p>
        <a href="#teachers-detail" class="btn">
          Batafsil bo'lim
          <i class="fa-solid fa-arrow-down" style="margin-left: 6px"></i>
        </a>
      </div>
    </div>
  </section>

  <main>
    <section class="container teachers-detail" id="teachers-detail">
      <div class="detail-grid">
        <div class="detail-content reveal">
          <span class="eyebrow">81-IDUM Ustozlar Jamoasi</span>
          <h2>{{ $teacher->subject }}</h2>
          <p>
            {{ $teacher->bio ?: "Ustozimiz har bir o'quvchining salohiyatiga mos yondashib, nazariya va amaliy mashg'ulotlarni birlashtirgan holda sifatli natijaga erishishni maqsad qiladi." }}
          </p>
          <ul class="detail-list">
            <li><i class="fa-solid fa-check"></i> {{ $teacher->experience_years }} yil tajriba</li>
            <li><i class="fa-solid fa-check"></i> Fan: {{ $teacher->subject }}</li>
            <li><i class="fa-solid fa-check"></i> Sinflar: {{ $teacher->grades ?: 'Barcha sinflar' }}</li>
          </ul>
          @auth
            <form action="{{ route('teacher.like', $teacher) }}" method="POST" class="js-like-form" style="margin-bottom: 14px;">
              @csrf
              <button class="like-btn {{ ($liked ?? false) ? 'liked' : '' }}" type="submit" aria-label="Ustozni yoqtirish">
                <i class="{{ ($liked ?? false) ? 'fa-solid' : 'fa-regular' }} fa-heart"></i>
                <span class="like-count">{{ $teacher->likes_count ?? 0 }}</span>
              </button>
            </form>
          @endauth
          <a href="{{ route('teacher') }}" class="btn">Ustozlar sahifasiga qaytish</a>
        </div>

        <article class="detail-image-card reveal">
          <img
            src="{{ $teacher->image ? asset('storage/' . $teacher->image) : asset('temp/img/how-to-be-teacher-malaysia-feature.png') }}"
            alt="{{ $teacher->full_name }} rasmi"
          />
          <div class="image-caption">
            <h3>{{ $teacher->full_name }}</h3>
            <p>{{ $teacher->subject }}</p>
          </div>
        </article>
      </div>
    </section>

    <section class="container comments-section" id="post-detail">
      <script>
        window.__POST_COMMENTS_CONFIG__ = {
          currentUserId: @json(auth()->check() ? auth()->id() : null),
          currentUserCanManageAll: @json(auth()->check() && (auth()->user()->isAdmin() || auth()->user()->isEditor() || auth()->user()->isModerator() || auth()->user()->isTeacher())),
          updateUrlTemplate: @json(route('teacher.comments.update', '__COMMENT_ID__')),
          destroyUrlTemplate: @json(route('teacher.comments.destroy', '__COMMENT_ID__')),
          storeUrl: @json(route('teacher.comments.store')),
          csrfToken: @json(csrf_token()),
        };
      </script>

      <div class="section-head">
        <h2>Fikr-mulohazalar</h2>
        <p>O'quvchilar va ota-onalar biz haqimizda nima deydi</p>
      </div>

      <div class="comments-stats reveal">
        <div class="stat-card">
          <span class="stat-icon"><i class="fa-solid fa-comments"></i></span>
          <span class="stat-num">{{ $comments->count() }}</span>
          <span class="stat-label">Izohlar</span>
        </div>
        <div class="stat-card">
          <span class="stat-icon"><i class="fa-solid fa-star"></i></span>
          <span class="stat-num">4.9</span>
          <span class="stat-label">Reyting</span>
        </div>
        <div class="stat-card">
          <span class="stat-icon"><i class="fa-solid fa-heart"></i></span>
          <span class="stat-num">1.2k</span>
          <span class="stat-label">Yoqtirishlar</span>
        </div>
      </div>

      <div class="comments-wrapper">
        <div class="comments-list">
          @if ($comments->isEmpty())
            <p class="comment-empty">Hozircha izohlar yo'q.</p>
          @else
            @foreach($comments as $comment)
              @include('teacher.partials.comment-item', ['comment' => $comment, 'showReplyForm' => true])
            @endforeach
          @endif
        </div>

        <div class="comment-form-box reveal">
          <h3><i class="fa-solid fa-pen-to-square"></i> Izoh qoldiring</h3>
          <form class="comment-form js-comment-form" action="{{ route('teacher.comments.store') }}" method="POST">
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
            <i class="fa-solid fa-info-circle"></i> Izohingiz moderator tomonidan
            ko'rib chiqiladi.
          </p>
        </div>
      </div>
    </section>
  </main>
</x-loyouts.main>

