<x-loyouts.main title="Qidiruv natijalari">
  <section class="news-hero" id="home">
    <div class="container">
      <div class="news-hero-content prime-reveal">
        <span class="badge">Qidiruv</span>
        <h1 class="js-split-text">Saytdan izlash</h1>
        <p>Barcha bo'limlar bo'yicha ma'lumotlarni qidiring (Yangiliklar, Ustozlar, Kurslar, Imtihonlar)</p>
      </div>
    </div>
  </section>

  <main>
    <section class="container courses-filter-section prime-reveal" style="padding-top: 40px; padding-bottom: 60px;">
      <form method="GET" action="{{ route('search') }}" class="exam-filter-panel" style="margin-bottom: 30px;">
        <div class="exam-filter-row">
          <div class="exam-filter-field" style="flex: 1;">
            <label class="exam-filter-label" for="global-search-q">Qidiruv so'zi</label>
            <div style="display: flex; gap: 10px;">
              <input type="search" id="global-search-q" name="q" class="exam-filter-input" placeholder="Nimani qidiryapsiz?..." value="{{ $q }}" required style="width: 100%;">
              <button type="submit" class="btn btn-prime" style="padding: 0 24px; border-radius: 12px; white-space: nowrap;">
                <i class="fa-solid fa-magnifying-glass"></i> Izlash
              </button>
            </div>
          </div>
        </div>
      </form>

      @if(empty($q))
        <div class="empty-state">
          <p>Qidirish uchun yuqoridagi qatorga biror nima yozing.</p>
        </div>
      @else
        <div class="section-head" style="text-align: left; margin-bottom: 30px;">
          <h2>"{{ $q }}" bo'yicha natijalar: {{ count($results) }} ta</h2>
        </div>

        @if(count($results) > 0)
          <div class="search-results-list" style="display: flex; flex-direction: column; gap: 16px;">
            @foreach($results as $res)
              <a href="{{ $res['url'] }}" class="search-result-item" style="display: flex; gap: 16px; padding: 16px; border: 1px solid var(--border); border-radius: 16px; background: var(--surface); text-decoration: none; color: inherit; transition: transform 0.2s, box-shadow 0.2s;">
                @if($res['image'])
                  <img src="{{ $res['image'] }}" alt="" style="width: 120px; height: 90px; object-fit: cover; border-radius: 8px; flex-shrink: 0;">
                @else
                  <div style="width: 120px; height: 90px; border-radius: 8px; background: rgba(13, 63, 120, 0.05); display: flex; align-items: center; justify-content: center; flex-shrink: 0; color: var(--primary);">
                    @if($res['type'] === 'post') <i class="fa-solid fa-newspaper fa-2x"></i>
                    @elseif($res['type'] === 'teacher') <i class="fa-solid fa-user-tie fa-2x"></i>
                    @elseif($res['type'] === 'course') <i class="fa-solid fa-book-open fa-2x"></i>
                    @elseif($res['type'] === 'exam') <i class="fa-solid fa-graduation-cap fa-2x"></i>
                    @endif
                  </div>
                @endif
                <div style="display: flex; flex-direction: column; justify-content: center; overflow: hidden;">
                  <div style="margin-bottom: 6px;">
                    @if($res['type'] === 'post') <span class="badge" style="background: rgba(16, 185, 129, 0.1); color: #10b981; padding: 2px 8px; font-size: 11px;">Yangilik</span>
                    @elseif($res['type'] === 'teacher') <span class="badge" style="background: rgba(59, 130, 246, 0.1); color: #3b82f6; padding: 2px 8px; font-size: 11px;">Ustoz</span>
                    @elseif($res['type'] === 'course') <span class="badge" style="background: rgba(245, 158, 11, 0.1); color: #f59e0b; padding: 2px 8px; font-size: 11px;">Kurs</span>
                    @elseif($res['type'] === 'exam') <span class="badge" style="background: rgba(139, 92, 246, 0.1); color: #8b5cf6; padding: 2px 8px; font-size: 11px;">Imtihon</span>
                    @endif
                  </div>
                  <h3 style="font-size: 16px; margin-bottom: 4px; color: var(--primary); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $res['title'] }}</h3>
                  <p style="font-size: 13px; color: var(--muted); margin: 0; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                    {{ \Illuminate\Support\Str::limit($res['description'], 120) }}
                  </p>
                </div>
              </a>
            @endforeach
          </div>

          <style>
            .search-result-item:hover {
              transform: translateY(-2px);
              box-shadow: 0 8px 24px rgba(13, 63, 120, 0.08);
            }
            @media (max-width: 560px) {
              .search-result-item {
                flex-direction: column;
              }
              .search-result-item img, .search-result-item > div:first-child {
                width: 100% !important;
                height: 160px !important;
              }
            }
          </style>
        @else
          <div class="empty-state">
            <i class="fa-solid fa-magnifying-glass fa-3x" style="color: var(--border); margin-bottom: 16px;"></i>
            <p>Kechirasiz, izlaganingiz bo'yicha hech narsa topilmadi.</p>
          </div>
        @endif
      @endif
    </section>
  </main>
</x-loyouts.main>
