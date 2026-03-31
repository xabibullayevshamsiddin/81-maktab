<x-loyouts.main title="81-IDUM | Yangiliklar">
  <section class="news-hero" id="home">
    <div class="container">
      <div class="news-hero-content reveal">
        <h1>81-IDUM Yangiliklari</h1>
        <p>Maktab hayotidagi eng dolzarb voqealar, tanlovlar va tadbirlar bilan tanishing.
Qiziqarli yangiliklar va muhim jarayonlar doimo siz bilan!</p>
      </div>
      <a href="#posts" class="btn" style="margin-top: 20px">
        Ma'lumotlarga o'tish <i class="fa-solid fa-arrow-down" style="margin-left: 10px;" ></i>
      </a>
    </div>
  </section>

  <main class="news">
    <section class="container news reveal glass-section" id="posts" style="padding-bottom:30px;">
      <div class="section-head">
        <h2>Yangiliklar</h2>
        <p>Qidirish, kategoriya bo‘yicha va saralash</p>
      </div>

      <form method="GET" action="{{ route('post') }}" class="post-filters">
        <div class="post-filter">
          <input
            type="text"
            name="q"
            value="{{ $q ?? '' }}"
            placeholder="Qidirish..."
            class="comment-input"
          />
        </div>

        <div class="post-filter">
          <select name="category_id" class="form-control">
            <option value="all" {{ empty($categoryId) || $categoryId === 'all' ? 'selected' : '' }}>
              Barchasi
            </option>
            @foreach($categories as $cat)
              <option value="{{ $cat->id }}" {{ (string)($categoryId ?? '') === (string)$cat->id ? 'selected' : '' }}>
                {{ $cat->name }}
              </option>
            @endforeach
          </select>
        </div>

        <div class="post-filter">
          <select name="sort" class="form-control">
            <option value="new" {{ ($sort ?? 'new') === 'new' ? 'selected' : '' }}>Eng yangilari</option>
            <option value="popular" {{ ($sort ?? '') === 'popular' ? 'selected' : '' }}>Ko‘p ko‘rilgan</option>
            <option value="likes" {{ ($sort ?? '') === 'likes' ? 'selected' : '' }}>Ko‘p yoqtirilgan</option>
            <option value="comments" {{ ($sort ?? '') === 'comments' ? 'selected' : '' }}>Ko‘p izoh</option>
          </select>
        </div>

        <div class="post-filter">
          <button type="submit" class="btn btn-sm">Topish</button>
          <a href="{{ route('post') }}" class="btn btn-sm btn-outline" style="margin-left: 10px;background-color:red;">Tozalash</a>
        </div>
      </form>

      <div class="post-grid">
        @forelse($posts as $post)
          <article class="news-card post-card">
            <img src="{{ asset('storage/' . $post->image) }}" alt="{{ $post->title }}" />

            @if($post->category)
              <div style="padding: 0 16px; margin-top: 10px;">
                <span class="badge" style="margin-bottom: 0;">{{ $post->category->name }}</span>
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
                  <button class="like-btn" type="submit" aria-label="Yoqtirish">
                    <i class="fa-regular fa-heart"></i>
                    <span class="like-count">{{ $post->likes_count }}</span>
                  </button>
                </form>
              </div>
            </div>

            <a href="{{ route('post.show', $post) }}" class="btn btn-sm" style="margin: 0 16px 16px;">Batafsil</a>
          </article>
        @empty
          <p>Hozircha yangiliklar yo‘q.</p>
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
    </section>
  </main>
</x-loyouts.main>

