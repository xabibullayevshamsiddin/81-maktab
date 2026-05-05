<x-loyouts.main title="Qidiruv natijalari">
  <section class="news-hero search-page-hero" id="home">
    <div class="container">
      <div class="news-hero-content prime-reveal">
        <span class="badge">Qidiruv</span>
        <h1 class="js-split-text">Saytdan izlash</h1>
        <p>Barcha bo'limlar bo'yicha ma'lumotlarni qidiring: yangiliklar, ustozlar, kurslar va imtihonlar.</p>
      </div>
    </div>
  </section>

  <main class="search-results-page">
    <section class="container courses-filter-section prime-reveal">
      <form method="GET" action="{{ route('search') }}" class="exam-filter-panel filter-shell search-results-form">
        <div class="exam-filter-row">
          <div class="exam-filter-field search-results-field">
            <label class="exam-filter-label" for="global-search-q">Qidiruv so'zi</label>
            <div class="search-results-input-row">
              <input type="search" id="global-search-q" name="q" class="exam-filter-input" placeholder="Nimani qidiryapsiz?..." value="{{ $q }}" required>
              <button type="submit" class="btn btn-prime search-results-submit">
                <i class="fa-solid fa-magnifying-glass"></i>
                Izlash
              </button>
            </div>
          </div>
        </div>
      </form>

      @if(empty($q))
        <div class="notification-empty search-empty-state">
          <i class="fa-solid fa-magnifying-glass"></i>
          <p>Qidirish uchun yuqoridagi maydonga biror so'z yozing.</p>
        </div>
      @else
        <div class="section-head search-results-head">
          <h2>"{{ $q }}" bo'yicha natijalar</h2>
          <p>{{ count($results) }} ta mos natija topildi.</p>
        </div>

        @if(count($results) > 0)
          <div class="search-results-list prime-stagger">
            @foreach($results as $res)
              <a href="{{ $res['url'] }}" class="search-result-item">
                <span class="search-result-media">
                  @if($res['image'])
                    <img src="{{ $res['image'] }}" alt="" loading="lazy" decoding="async">
                  @else
                    <span class="search-result-fallback search-result-fallback--{{ $res['type'] }}">
                      @if($res['type'] === 'post')
                        <i class="fa-solid fa-newspaper"></i>
                      @elseif($res['type'] === 'teacher')
                        <i class="fa-solid fa-user-tie"></i>
                      @elseif($res['type'] === 'course')
                        <i class="fa-solid fa-book-open"></i>
                      @elseif($res['type'] === 'exam')
                        <i class="fa-solid fa-graduation-cap"></i>
                      @endif
                    </span>
                  @endif
                </span>

                <span class="search-result-main">
                  <span class="search-result-type search-result-type--{{ $res['type'] }}">
                    @if($res['type'] === 'post') Yangilik
                    @elseif($res['type'] === 'teacher') Ustoz
                    @elseif($res['type'] === 'course') Kurs
                    @elseif($res['type'] === 'exam') Imtihon
                    @else Natija
                    @endif
                  </span>
                  <strong class="search-result-title">{{ $res['title'] }}</strong>
                  <span class="search-result-desc">{{ \Illuminate\Support\Str::limit($res['description'], 140) }}</span>
                </span>

                <span class="search-result-arrow">
                  <i class="fa-solid fa-arrow-up-right-from-square"></i>
                </span>
              </a>
            @endforeach
          </div>
        @else
          <div class="notification-empty search-empty-state">
            <i class="fa-solid fa-magnifying-glass"></i>
            <p>Kechirasiz, izlaganingiz bo'yicha hech narsa topilmadi.</p>
          </div>
        @endif
      @endif
    </section>
  </main>
</x-loyouts.main>
