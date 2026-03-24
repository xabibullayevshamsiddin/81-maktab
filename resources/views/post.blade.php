<x-loyouts.main title="81-IDUM | Yangiliklar">

  <section class="news-hero" id="home">
    <div class="container">
      <div class="news-hero-content reveal">
          <h1>81-IDUM Yangiliklari</h1>
          <p>Maktab hayotidagi eng dolzarb voqealar, tanlovlar va tadbirlar.</p>
      </div>
      <a href="#news" class="btn"
            >Ma'lumotlarga o'tish
            <i class="fa-solid fa-arrow-down" style="margin-left: 6px"></i
          ></a>
    </div>
  </section>

    <main class="news">
      <section class="container news reveal glass-section" id="news">
        <div class="section-head">
          <h2>Yangiliklar</h2>
          <p>Maktab hayotidagi eng dolzarb voqealar</p>
        </div>
        <div class="news-actions">
          <form method="GET" action="{{ route('post') }}" class="news-search">
            <input type="text" name="q" value="{{ $q ?? '' }}" placeholder="Qidirish..." class="comment-input" />
          </form>
        </div>

        <div class="news-container">
          @forelse($posts as $post)
            <article class="news-card">
              <div class="news-media">
                <img
                  src="{{ asset('storage/' . $post->image) }}"
                  alt="{{ $post->title }}"
                />
                <div class="news-media-overlay">
                  <div class="news-chip">
                    <i class="fa-regular fa-newspaper"></i>
                    <span>{{ $post->category?->name ?? 'Yangilik' }}</span>
                  </div>
                </div>
              </div>

              <div class="news-body">
                <h3>{{ $post->title }}</h3>
                <p>{{ $post->short_content }}</p>

                <ul class="news-meta">
                  <li><i class="fa-regular fa-eye"></i> {{ $post->views }}</li>
                  <li><i class="fa-regular fa-comment"></i> {{ $post->comments_count }}</li>
                  <li>
                    <form action="{{ route('post.like', $post) }}" method="POST" class="like-form-inline">
                      @csrf
                      <button class="news-like" type="submit" aria-label="Yoqtirish">
                        <i class="fa-regular fa-heart"></i> <span class="likes-count">{{ $post->likes_count }}</span>
                      </button>
                    </form>
                  </li>
                </ul>

                <a href="{{ route('post.show', $post) }}" class="btn btn-sm news-cta">Batafsil</a>
              </div>
            </article>
          @empty
            <p>Hozircha yangiliklar yo'q.</p>
          @endforelse
        </div>



        @if($posts->hasPages())
          <nav class="news-pagination" aria-label="Pagination">
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
          </nav>
        @endif
      </section>
    </main>
</x-loyouts.main>

<script>
document.addEventListener('DOMContentLoaded', function() {
  document.querySelectorAll('.like-form-inline').forEach(function(form) {
    form.addEventListener('submit', function(e) {
      e.preventDefault();
      const btn = form.querySelector('.news-like');
      const icon = btn.querySelector('i');
      const countSpan = btn.querySelector('.likes-count');
      
      fetch(form.action, {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': '{{ csrf_token() }}',
          'Accept': 'application/json'
        }
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          icon.className = data.liked ? 'fa-solid fa-heart' : 'fa-regular fa-heart';
          countSpan.textContent = data.likes_count;
        }
      })
      .catch(error => console.error('Error:', error));
    });
  });
});
</script>
