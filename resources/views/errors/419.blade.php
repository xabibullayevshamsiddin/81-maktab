<x-loyouts.main title="419 | Sessiya vaqti tugadi">
  <section class="error-page">
    <div class="container">
      <div class="error-shell reveal">
        <div class="error-card error-card--missing">
          <div class="error-head">
            <span class="error-badge">
              <i class="fa-solid fa-hourglass-end"></i>
              Sahifa muddati tugadi
            </span>
            <h1 class="error-code">419</h1>
          </div>

          <div class="error-main">
            <span class="error-icon">
              <i class="fa-solid fa-rotate"></i>
            </span>

            <div class="error-copy">
              <h2 class="error-title">Sessiya vaqti tugadi</h2>
              <p class="error-text">
                Xavfsizlik maqsadida, uzoq vaqt davomida amal bajarilmagani uchun ma'lumotlaringiz vaqtincha muzlatildi (CSRF Error).
              </p>
              <p class="error-help">
                Hech qisi yo'q, shunchaki quyidagi "Sahifani yangilash" tugmasini bosib, harakatingizni qayta takrorlasangiz kifoya.
              </p>

              <div class="error-actions">
                <button
                  type="button"
                  class="btn"
                  onclick="window.location.reload();"
                >
                  <i class="fa-solid fa-rotate-right"></i>
                  Sahifani yangilash
                </button>
                <button
                  type="button"
                  class="btn btn-outline error-back-btn"
                  onclick="if (window.history.length > 1) { window.history.back(); } else { window.location.href='{{ route('home') }}'; }"
                >
                  <i class="fa-solid fa-arrow-left"></i>
                  Orqaga qaytish
                </button>
              </div>
            </div>
          </div>

          <div class="error-links">
            <a href="javascript:window.location.reload();" class="error-link-card">
              <span class="error-link-icon"><i class="fa-solid fa-rotate-right"></i></span>
              <strong>Yangilash</strong>
              <span>Sahifani tarrilab formani birboshdan jo'natsangiz hammasi soz bo'ladi.</span>
            </a>

            <a href="{{ route('home') }}" class="error-link-card">
              <span class="error-link-icon"><i class="fa-solid fa-house"></i></span>
              <strong>Bosh sahifa</strong>
              <span>Va mēng yo'nalish bo'yicha saytning asosiy betiga o'tib olaman.</span>
            </a>
          </div>
        </div>
      </div>
    </div>
  </section>
</x-loyouts.main>
