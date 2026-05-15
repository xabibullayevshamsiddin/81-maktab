<x-loyouts.main :title="'Telegram orqali tasdiqlash'">
  <section class="signin-hero">
    <div class="container">
      <h1>Telegram orqali tasdiqlash</h1>
      <p>Ro'yxatdan o'tish tugashi uchun telefon raqamingizni Telegram bot orqali bir marta tasdiqlang.</p>
    </div>
  </section>

  <main class="signin-section">
    <div class="container">
      <div class="signin-card">
        <div class="signin-card-icon">
          <i class="fa-brands fa-telegram"></i>
        </div>

        <h2>Telefonni tasdiqlang</h2>
        <p class="signin-subtitle">
          Saytda kiritilgan raqam:
          <strong>{{ $verification->phone }}</strong>
        </p>

        <div class="register-chip-list" aria-hidden="true" style="justify-content:center; margin-bottom: 18px;">
          <span class="register-chip"><i class="fa-brands fa-telegram"></i> Telegram botni oching</span>
          <span class="register-chip"><i class="fa-solid fa-address-book"></i> Kontaktni yuboring</span>
          <span class="register-chip"><i class="fa-solid fa-circle-check"></i> Saytga qaytib yakunlang</span>
        </div>

        <div style="display:flex; gap:10px; justify-content:center; flex-wrap:wrap;">
          <a href="{{ $desktopDeepLinkUrl }}" class="btn">Telegram appni ochish</a>
          <a href="{{ $deepLinkUrl }}" class="btn btn-outline" target="_blank" rel="noopener">Brauzer orqali ochish</a>
        </div>
        <p class="register-field-note" style="margin-top: 12px;">
          Bot username: <strong>{{ '@'.$botUsername }}</strong>
        </p>
        <p class="register-field-note">
          Agar brauzer sizga Telegram o'rnatishni taklif qilsa, birinchi tugma orqali desktop ilovani oching.
        </p>

        <div
          id="telegram-register-status"
          class="register-alert"
          data-status-url="{{ route('register.telegram.status') }}"
          data-initial-state="{{ $verification->isVerified() ? 'verified' : ($verification->started_at ? 'started' : 'pending') }}"
          style="margin-top: 18px;"
        >
          <i class="fa-solid fa-hourglass-half"></i>
          <span id="telegram-register-status-text">
            @if ($verification->isVerified())
              Telefon raqamingiz tasdiqlandi. Endi ro'yxatdan o'tishni yakunlashingiz mumkin.
            @elseif ($verification->started_at)
              Bot ochilgan. Endi Telegram ichida telefon raqamingizni yuboring.
            @else
              Avval Telegram botni oching, keyin u yerdan telefon raqamingizni yuboring.
            @endif
          </span>
        </div>

        <form
          action="{{ route('register.telegram.complete') }}"
          method="POST"
          id="telegram-register-complete-form"
          style="{{ $verification->isVerified() ? '' : 'display:none;' }} margin-top: 16px;"
        >
          @csrf
          <button class="btn" type="submit">Ro'yxatdan o'tishni yakunlash</button>
        </form>

        <p class="register-field-note" style="margin-top: 14px;">
          Tasdiqlash oynasi 20 daqiqa faol turadi. Muddati tugasa, register formani qayta yuborasiz.
        </p>

        <p class="register-signin">
          Xato raqam kiritdingizmi? <a href="{{ route('register') }}">Qaytib tuzatish</a>
        </p>
      </div>
    </div>
  </main>

  <script>
    (function () {
      var box = document.getElementById('telegram-register-status');
      var text = document.getElementById('telegram-register-status-text');
      var form = document.getElementById('telegram-register-complete-form');

      if (!box || !text || !form) {
        return;
      }

      var statusUrl = box.getAttribute('data-status-url');
      var timerId = null;

      function renderStatus(state) {
        if (state === 'verified') {
          text.textContent = "Telefon raqamingiz tasdiqlandi. Endi ro'yxatdan o'tishni yakunlashingiz mumkin.";
          form.style.display = '';
          return true;
        }

        form.style.display = 'none';

        if (state === 'started') {
          text.textContent = "Bot ochilgan. Endi Telegram ichida telefon raqamingizni yuboring.";
          return false;
        }

        if (state === 'expired') {
          text.textContent = "Tasdiqlash muddati tugadi. Register sahifasiga qaytib, formani qayta yuboring.";
          return false;
        }

        if (state === 'missing') {
          text.textContent = "Tasdiqlash sessiyasi topilmadi. Register sahifasidan qayta boshlang.";
          return false;
        }

        text.textContent = "Avval Telegram botni oching, keyin u yerdan telefon raqamingizni yuboring.";
        return false;
      }

      function stopPolling() {
        if (timerId) {
          window.clearInterval(timerId);
          timerId = null;
        }
      }

      function fetchStatus() {
        if (!statusUrl) {
          return;
        }

        fetch(statusUrl, {
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
          },
          credentials: 'same-origin'
        })
          .then(function (response) {
            if (!response.ok) {
              throw new Error('Status unavailable');
            }

            return response.json();
          })
          .then(function (payload) {
            if (renderStatus(payload.state || 'pending')) {
              stopPolling();
            }
          })
          .catch(function () {
          });
      }

      if (!renderStatus(box.getAttribute('data-initial-state') || 'pending')) {
        timerId = window.setInterval(fetchStatus, 4000);
      }
    })();
  </script>
</x-loyouts.main>
