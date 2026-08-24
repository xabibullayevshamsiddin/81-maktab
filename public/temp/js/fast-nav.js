(function () {
  if (document.body.getAttribute('data-fast-nav') !== '1') return;

  var prefetched = new Set();
  var hoverTimer = null;

  function isPrefetchable(link) {
    if (!link || !link.href) return false;
    if (link.origin !== location.origin) return false;
    if (link.hasAttribute('download')) return false;
    if (link.target === '_blank') return false;
    if (link.href.includes('#')) return false;
    if (/\/(logout|admin\/.*\/delete|.*\/destroy)/.test(link.pathname)) return false;
    if (prefetched.has(link.href)) return false;
    return true;
  }

  function prefetch(url) {
    prefetched.add(url);
    fetch(url, { credentials: 'same-origin', priority: 'low' }).catch(function () {});
  }

  document.addEventListener('mouseover', function (e) {
    var link = e.target.closest('a');
    if (!isPrefetchable(link)) return;
    clearTimeout(hoverTimer);
    hoverTimer = setTimeout(function () { prefetch(link.href); }, 90);
  });

  document.addEventListener('mouseout', function () {
    clearTimeout(hoverTimer);
  });

  document.addEventListener('touchstart', function (e) {
    var link = e.target.closest('a');
    if (isPrefetchable(link)) prefetch(link.href);
  }, { passive: true });
})();
