/**
 * Donor expiry countdown — real vaqtda yangilanadi.
 * Har daqiqada qoldiq vaqtni kamaytiradi, muddati tugasa "Muddati tugagan" deb ko'rsatadi.
 */
(function () {
  const el = document.getElementById('donor-expiry-text');
  if (!el) return;

  const expiresAt = new Date(el.dataset.donorExpiresAt).getTime();
  const expiredText = el.dataset.donorExpiredText || 'Muddati tugagan';

  function formatRemaining(ms) {
    if (ms <= 0) return expiredText;

    const totalMinutes = Math.floor(ms / 60000);
    const days = Math.floor(totalMinutes / (60 * 24));
    const hours = Math.floor((totalMinutes % (60 * 24)) / 60);
    const minutes = totalMinutes % 60;

    const parts = [];
    if (days > 0) parts.push(days + ' kun');
    if (hours > 0) parts.push(hours + ' soat');
    if (parts.length === 0 && minutes > 0) parts.push(minutes + ' daqiqa');

    return parts.length ? parts.join(' ') : 'Kamroq 1 daqiqa';
  }

  function tick() {
    const remaining = expiresAt - Date.now();
    el.textContent = formatRemaining(remaining);

    if (remaining <= 0) {
      clearInterval(timer);
    }
  }

  tick(); // darhol ko'rsatish
  const timer = setInterval(tick, 60000); // har daqiqada yangilanadi
})();
