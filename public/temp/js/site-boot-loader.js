/**
 * Boot loader: donor foydalanuvchilar uchun tezlashtirilgan.
 */
(function () {
  var loader = document.getElementById('site-boot-loader');
  if (!loader) return;

  // Donor bo'lsa minMs=0 (darhol yashiriladi), oddiy user uchun 600ms
  var isDonor = document.body.getAttribute('data-donor-theme') &&
                document.body.getAttribute('data-donor-theme') !== '';
  var minMs = isDonor ? 0 : 600;
  var removeDelayMs = isDonor ? 200 : 700;
  var start = Date.now();

  function hide() {
    var elapsed = Date.now() - start;
    var wait = Math.max(0, minMs - elapsed);
    window.setTimeout(function () {
      loader.classList.add('site-boot-loader--done');
      document.body.classList.remove('site-boot-loading');
      loader.setAttribute('aria-busy', 'false');
      window.setTimeout(function () {
        if (loader.parentNode) {
          loader.parentNode.removeChild(loader);
        }
      }, removeDelayMs);
    }, wait);
  }

  if (document.readyState === 'complete') {
    hide();
  } else {
    window.addEventListener('load', hide, { once: true });
  }
})();
