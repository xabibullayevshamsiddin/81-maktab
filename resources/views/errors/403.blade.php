<x-loyouts.main title="403 | Kirish Cheklangan">
  <section class="error-page">
    <div class="container">
      <div class="error-shell reveal">
        <div class="error-card error-card--forbidden">
          <div class="error-head">
            <span class="error-badge">
              <i class="fa-solid fa-lock"></i>
              Kirish cheklangan
            </span>
            <h1 class="error-code">403</h1>
          </div>

          <div class="error-main">
            <span class="error-icon">
              <i class="fa-solid fa-shield-halved"></i>
            </span>

            <div class="error-copy">
              <h2 class="error-title">Bu bo'lim sizga yopiq</h2>
              <p class="error-text">
                Bu sahifa faqat mos ruxsatga ega foydalanuvchilar uchun ochiq. Hisobingiz roli
                yoki holati bu sahifaga kirish uchun yetarli emas.
              </p>
              <p class="error-help">
                Agar bu xato deb o'ylasangiz, administrator bilan bog'lanib ruxsatlaringizni tekshirtiring.
              </p>

              <div class="error-actions">
                <a href="{{ route('home') }}" class="btn">
                  <i class="fa-solid fa-house"></i>
                  Bosh sahifaga qaytish
                </a>
                <button
                  type="button"
                  class="btn btn-outline error-back-btn"
                  onclick="if (window.history.length > 1) { window.history.back(); } else { window.location.href='{{ route('home') }}'; }"
                >
                  <i class="fa-solid fa-arrow-left"></i>
                  Oldingi sahifaga qaytish
                </button>
              </div>
            </div>
          </div>

          <div class="error-links">
            <a href="{{ route('home') }}" class="error-link-card">
              <span class="error-link-icon"><i class="fa-solid fa-house"></i></span>
              <strong>Bosh sahifa</strong>
              <span>Asosiy bo'limga qaytib, davom etishingiz mumkin.</span>
            </a>

            <a href="{{ route('courses') }}" class="error-link-card">
              <span class="error-link-icon"><i class="fa-solid fa-book-open"></i></span>
              <strong>Kurslar</strong>
              <span>Ochiq bo'limlardan biriga o'tib, kurslarni ko'ring.</span>
            </a>

            <a href="{{ route('teacher') }}" class="error-link-card">
              <span class="error-link-icon"><i class="fa-solid fa-chalkboard-user"></i></span>
              <strong>Ustozlar</strong>
              <span>Ustozlar va ularning kurslari bo'limiga o'tishingiz mumkin.</span>
            </a>
          </div>
        </div>
      </div>
    </div>
  </section>
</x-loyouts.main>
