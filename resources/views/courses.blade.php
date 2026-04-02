<x-loyouts.main title="81-IDUM | Kurslar">
  <section class="news-hero" id="home">
    <div class="container">
      <div class="news-hero-content">
        <span class="badge">81-IDUM Kurslar</span>
        <h1>Bilim va kelajak shu yerda boshlanadi</h1>
        <p>Ustozlar tomonidan ochilgan va tasdiqlangan kurslar ro'yxati.</p>
      </div>
    </div>
  </section>

  <main>
    <section class="container courses-filter-section" id="courses-list">
      <div class="section-head">
        <h2>Barcha kurslar</h2>
        <p>Ustozlarga bog'langan faol kurslar</p>
      </div>

      <div class="courses-grid" id="courses-grid">
        @forelse($courses as $course)
          <article class="course-card reveal">
            <div class="course-body">
              <h3>{{ $course->title }}</h3>
              <p>{{ $course->description }}</p>
              <ul class="course-meta">
                <li><i class="fa-solid fa-user"></i> {{ $course->teacher?->full_name ?: '-' }}</li>
                <li><i class="fa-regular fa-clock"></i> {{ $course->duration }}</li>
                <li><i class="fa-solid fa-money-bill"></i> {{ $course->price }}</li>
                <li><i class="fa-regular fa-calendar"></i> {{ $course->start_date?->format('Y-m-d') }}</li>
              </ul>
            </div>
          </article>
        @empty
          <p>Hozircha kurslar yo'q.</p>
        @endforelse
      </div>
    </section>
  </main>
</x-loyouts.main>
