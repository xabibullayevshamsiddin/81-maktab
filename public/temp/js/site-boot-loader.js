/**
 * Boot loader: sahifa to‘liq yuklangach silliq yopiladi (min vaqt — animatsiya ko‘rinsin).
 */
(function () {
  var loader = document.getElementById('site-boot-loader');
  if (!loader) return;

  /* Cinematic transition durations */
  var minMs = 1200;      /* Stay black for 1.2s */
  var removeDelayMs = 1500; /* Fade out takes longer */
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


