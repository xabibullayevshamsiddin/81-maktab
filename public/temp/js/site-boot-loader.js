/**
 * Boot loader: faqat birinchi tashrifda ko‘rsatiladi, keyingi sahifalar darhol ochiladi.
 */
(function () {
  var loader = document.getElementById('site-boot-loader');
  if (!loader) return;

  // sessionStorage tekshiruvini olib tashladik, endi har sahifa yuklanganda chiqadi.
  // var seenKey = 'site-boot-seen';
  // if (sessionStorage.getItem(seenKey) === '1') { ... }

  var minMs = 600;
  var removeDelayMs = 700;
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
