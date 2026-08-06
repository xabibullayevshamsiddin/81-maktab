<x-layouts.main :title="__('auth_pages.verify.page_title')">
  @push('page_styles')
    <style>
      .tg-verify-hero { text-align: center; padding: 130px 1rem 48px; background: linear-gradient(135deg, #0a2f5e 0%, #14559b 50%, #1a6bb5 100%); color: #fff; }
      .tg-verify-hero h1 { font-size: clamp(28px, 4vw, 36px); font-weight: 700; color: #fff; margin-bottom: 8px; }
      .tg-verify-hero p { color: #d8e7ff; font-size: 1rem; max-width: 500px; margin: 0 auto; }
      .tg-verify-card {
        max-width: 420px; margin: 2rem auto 4rem;
        background: var(--surface); border: 1px solid var(--border);
        border-radius: 1.25rem; padding: 2.5rem 2rem; text-align: center;
      }
      .tg-verify-card-icon {
        width: 72px; height: 72px; border-radius: 50%;
        background: linear-gradient(135deg, #2AABEE, #229ED9);
        display: flex; align-items: center; justify-content: center;
        margin: 0 auto 1.25rem; font-size: 2rem; color: #fff;
        box-shadow: 0 8px 24px rgba(42, 171, 238, 0.25);
      }
      .tg-verify-card h2 { font-size: 1.35rem; font-weight: 700; color: var(--text); margin-bottom: 0.5rem; }
      .tg-verify-card .tg-subtitle { color: var(--muted); font-size: 0.9rem; margin-bottom: 1.5rem; line-height: 1.6; }

      .tg-qr-wrap {
        display: inline-flex; justify-content: center;
        background: #fff; border-radius: 12px; padding: 16px;
        border: 1px solid var(--border);
      }
      .tg-qr-note {
        margin-top: 0.75rem; font-size: 0.75rem;
        color: var(--muted); line-height: 1.5;
      }
      .tg-qr-note i { color: #229ED9; }

      .tg-status {
        margin-top: 1.5rem; padding: 0.85rem 1rem;
        border-radius: 10px; font-size: 0.9rem; font-weight: 600;
        display: flex; align-items: center; justify-content: center; gap: 0.5rem;
      }
      .tg-status.waiting { background: rgba(59,130,246,0.08); color: #2563eb; border: 1px solid rgba(59,130,246,0.2); }
      :root[data-theme='dark'] .tg-status.waiting { background: rgba(59,130,246,0.15); color: #60a5fa; border-color: rgba(59,130,246,0.3); }
      .tg-status.verified { background: rgba(22,163,74,0.08); color: #16a34a; border: 1px solid rgba(22,163,74,0.2); }
      :root[data-theme='dark'] .tg-status.verified { background: rgba(22,163,74,0.15); color: #4ade80; border-color: rgba(22,163,74,0.3); }
      .tg-status.expired { background: rgba(220,38,38,0.08); color: #dc2626; border: 1px solid rgba(220,38,38,0.2); }
      :root[data-theme='dark'] .tg-status.expired { background: rgba(220,38,38,0.15); color: #f87171; border-color: rgba(220,38,38,0.3); }

      .tg-steps { margin-top: 1.5rem; text-align: left; }
      .tg-steps li {
        display: flex; align-items: flex-start; gap: 0.6rem;
        padding: 0.5rem 0; font-size: 0.85rem; color: var(--muted); line-height: 1.5;
      }
      .tg-steps li .step-num {
        min-width: 24px; height: 24px; border-radius: 50%;
        background: rgba(42,171,238,0.1); color: #229ED9;
        display: flex; align-items: center; justify-content: center;
        font-size: 0.75rem; font-weight: 700; flex-shrink: 0; margin-top: 1px;
      }
      .tg-steps li strong { color: var(--text); }

      .tg-verify-back {
        display: inline-flex; align-items: center; gap: 0.4rem;
        margin-top: 1.25rem; color: var(--muted); font-size: 0.85rem;
        text-decoration: none; font-weight: 600;
      }
      .tg-verify-back:hover { color: var(--text); }

      .spinner-dots { display: inline-flex; gap: 4px; vertical-align: middle; }
      .spinner-dots span {
        width: 5px; height: 5px; border-radius: 50%; background: currentColor;
        animation: dotPulse 1.4s infinite ease-in-out;
      }
      .spinner-dots span:nth-child(2) { animation-delay: 0.2s; }
      .spinner-dots span:nth-child(3) { animation-delay: 0.4s; }
      @keyframes dotPulse {
        0%, 80%, 100% { opacity: 0.3; transform: scale(0.8); }
        40% { opacity: 1; transform: scale(1); }
      }
    </style>
  @endpush

  <section class="tg-verify-hero">
    <div class="container">
      <h1>Telegram orqali tasdiqlash</h1>
      <p>Telefon raqamingizni Telegram bot orqali tasdiqlang</p>
    </div>
  </section>

  <main class="signin-section">
    <div class="container">
      <div class="tg-verify-card">
        <div class="tg-verify-card-icon">
          <i class="fa-brands fa-telegram"></i>
        </div>
        <h2>Tasdiqlash kerak</h2>
        <p class="tg-subtitle">Telegram avtomatik ochilmoqda...</p>

        {{-- QR kod --}}
        <div class="tg-qr-wrap" id="tg-qr"></div>
        <div class="tg-qr-note">
          <i class="fa-solid fa-circle-info"></i>
          Telegram ilovasidagi <strong>Menu → Skaner</strong> bilan skanerlang
        </div>

        {{-- Status --}}
        <div class="tg-status waiting" id="tg-status">
          <span class="spinner-dots"><span></span><span></span><span></span></span>
          Kutilmoqda...
        </div>

        {{-- Qo'llanma --}}
        <ol class="tg-steps">
          <li><span class="step-num">1</span><span>Telegram ochiladi yoki QR kodni skanerlang</span></li>
          <li><span class="step-num">2</span><span>Botga <strong>/start</strong> yuboring</span></li>
          <li><span class="step-num">3</span><span><strong>"📱 Telefon raqamni ulashish"</strong> tugmasini bosing</span></li>
          <li><span class="step-num">4</span><span>Raqamingizni ulang</span></li>
        </ol>

        <a href="{{ route($mode === 'login' ? 'login' : 'register') }}" class="tg-verify-back">
          <i class="fa-solid fa-arrow-left"></i> Orqaga qaytish
        </a>
      </div>
    </div>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
  <script>
    (function() {
      var token = '{{ $token }}';
      var botUsername = '{{ $bot_username ?: "maktab81_verify_bot" }}';
      var statusUrl = '{{ route("telegram.status", ["token" => $token]) }}';
      var completeUrl = '{{ route("telegram.complete", ["token" => $token]) }}';
      var deepLink = 'https://t.me/' + botUsername + '?start=' + token;

      // ========== QR KOD ==========
      var qrContainer = document.getElementById('tg-qr');
      if (qrContainer && typeof QRCode !== 'undefined') {
        new QRCode(qrContainer, {
          text: deepLink,
          width: 180,
          height: 180,
          colorDark: '#1a1a2e',
          colorLight: '#ffffff',
          correctLevel: QRCode.CorrectLevel.M
        });
      }

      // ========== AVTOMATIK TELEGRAM'GA JO'NATISH ==========
      // 2 soniyadan keyin Telegram sahifasini YANGI TABDA ochish
      setTimeout(function() {
        window.open(deepLink, '_blank');
      }, 2000);

      // ========== POLLING ==========
      var statusEl = document.getElementById('tg-status');
      var pollCount = 0;
      var maxPolls = 150;

      function checkStatus() {
        fetch(statusUrl)
          .then(function(r) { return r.json(); })
          .then(function(data) {
            if (data.status === 'verified') {
              clearInterval(pollInterval);
              statusEl.className = 'tg-status verified';
              statusEl.innerHTML = '<i class="fa-solid fa-circle-check"></i> Tasdiqlandi!';
              setTimeout(function() { window.location.href = completeUrl; }, 1000);
            } else if (data.status === 'expired') {
              clearInterval(pollInterval);
              statusEl.className = 'tg-status expired';
              statusEl.innerHTML = '<i class="fa-solid fa-circle-xmark"></i> Havola eskirdi.';
            } else {
              pollCount++;
              if (pollCount >= maxPolls) {
                clearInterval(pollInterval);
                statusEl.className = 'tg-status expired';
                statusEl.innerHTML = '<i class="fa-solid fa-clock"></i> Vaqt tugadi.';
              }
            }
          })
          .catch(function() {});
      }

      var pollInterval = setInterval(checkStatus, 2000);
      checkStatus();
    })();
  </script>
</x-layouts.main>
