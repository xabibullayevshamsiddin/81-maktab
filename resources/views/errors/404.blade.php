<x-loyouts.main title="404 | Sahifa Topilmadi">
  <section class="error-page">
    <div class="container">
      <div class="error-shell reveal">
        <div class="error-card error-card--missing">
          <div class="error-head">
            <span class="error-badge">
              <i class="fa-solid fa-compass"></i>
              Sahifa topilmadi
            </span>
            <h1 class="error-code">404</h1>
          </div>

          <div class="error-main">
            <span class="error-icon">
              <i class="fa-solid fa-map-location-dot"></i>
            </span>

            <div class="error-copy">
              <h2 class="error-title">Bu manzil topilmadi</h2>
              <p class="error-text">
                Siz ochmoqchi bo'lgan sahifa mavjud emas, ko'chirilgan yoki o'chirilgan bo'lishi mumkin.
                Quyidagi yo'nalishlardan biriga o'tib davom eting.
              </p>
              <p class="error-help">
                Havola noto'g'ri yozilgan bo'lishi yoki eski link ishlatilgan bo'lishi ham mumkin.
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
              <span>Saytning asosiy bo'limiga qaytib, kerakli yo'nalishni tanlang.</span>
            </a>

            <a href="{{ route('courses') }}" class="error-link-card">
              <span class="error-link-icon"><i class="fa-solid fa-book-open"></i></span>
              <strong>Kurslar</strong>
              <span>Mavjud kurslar ro'yxatini ochib, kerakli sahifani qayta toping.</span>
            </a>

            <a href="{{ route('teacher') }}" class="error-link-card">
              <span class="error-link-icon"><i class="fa-solid fa-chalkboard-user"></i></span>
              <strong>Ustozlar</strong>
              <span>Ustozlar sahifasidan kerakli ma'lumotga boshqa yo'l bilan o'ting.</span>
            </a>
          </div>
        </div>
      </div>
    </div>
  </section>
</x-loyouts.main>
