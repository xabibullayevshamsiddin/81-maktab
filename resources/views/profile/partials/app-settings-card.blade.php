<div class="signin-card profile-card">
  <div class="profile-card-head">
    <span class="profile-card-kicker">Sozlamalar</span>
    <h2>Ilova Sozlamalari</h2>
    <p class="signin-subtitle">Saytning ko'rinishi va ishlashini o'zgartiring.</p>
  </div>

  <div class="profile-form-grid" style="margin-top: 15px;">
    <div class="profile-field">
      <label>Sayt rejimi</label>
      <span class="profile-field-hint" style="margin-bottom: 10px;">Agar avto rejimni tanlasangiz, sayt qurilmangiz rejimiga qarab avtomatik o'zgaradi.</span>
      <button type="button" class="btn btn-outline btn-sm js-theme-auto-reset">
        <i class="fa-solid fa-desktop"></i> Avto rejimga qaytarish
      </button>
    </div>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const resetBtn = document.querySelector('.js-theme-auto-reset');
      if (resetBtn) {
        resetBtn.addEventListener('click', function() {
          localStorage.removeItem('site-theme');
          const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
          const theme = prefersDark ? 'dark' : 'light';
          document.documentElement.setAttribute('data-theme', theme);
          document.body.setAttribute('data-theme', theme);
          if (window.showToast) {
            window.showToast("Avto rejim faollashtirildi. Sayt endi qurilma rejimiga moslashadi.", 'success');
          }
        });
      }
    });
  </script>
</div>
