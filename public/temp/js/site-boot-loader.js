/**
 * Boot loader: donor foydalanuvchilar uchun tezlashtirilgan.
 */
(function () {
  var loader = document.getElementById('site-boot-loader');
  if (!loader) return;

  var isDonor = document.body.getAttribute('data-donor-theme') &&
                document.body.getAttribute('data-donor-theme') !== '';
  var minMs = 0;
  var removeDelayMs = isDonor ? 120 : 250;
  var start = Date.now();

  function hide(immediate) {
    var elapsed = Date.now() - start;
    var wait = immediate ? 0 : Math.max(0, minMs - elapsed);
    window.setTimeout(function () {
      loader.classList.add('site-boot-loader--done');
      document.body.classList.remove('site-boot-loading');
      loader.setAttribute('aria-busy', 'false');
      window.setTimeout(function () {
        if (loader.parentNode) {
          loader.parentNode.removeChild(loader);
        }
      }, immediate ? 50 : removeDelayMs);
    }, wait);
  }

  window.hideSiteBootLoader = function() { hide(true); };

  if (document.readyState === 'interactive' || document.readyState === 'complete') {
    hide(false);
  } else {
    document.addEventListener('DOMContentLoaded', function () { hide(false); }, { once: true });
    window.addEventListener('load', function () { hide(true); }, { once: true });
  }

  // Always hide loader when page is restored from Back-Forward Cache (bfcache)
  window.addEventListener('pageshow', function () {
    hide(true);
  });
  window.addEventListener('popstate', function () {
    hide(true);
  });
})();
