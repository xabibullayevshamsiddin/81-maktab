/**
 * BOOK VIEWER — PDF.js Custom Reader
 * 81-IDUM Aurora Glassmorphism
 */
(function () {
  'use strict';

  var PDFJS_CDN    = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js';
  var PDFJS_WORKER = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

  var pdfDoc      = null;
  var currentPage = 1;
  var totalPages  = 0;
  var scale       = 1.0;
  var isRendering = false;
  var isMobile    = window.innerWidth < 900;
  var isFlipping  = false;

  var canvasLeft, canvasRight, canvasSingle;
  var ctxLeft, ctxRight, ctxSingle;
  var loadingEl, pageInfoEl, zoomInfoEl;
  var progressFill, progressLabel;
  var thumbsWrap;
  var pdfUrl;

  var baseFitScale = 1.0;

  /* ── Scale: Large, crystal clear reading scale ── */
  function calcFitScale(natW, natH) {
    var stage = document.getElementById('bv-stage');
    var rect  = stage ? stage.getBoundingClientRect() : null;
    var sw = rect && rect.width  > 0 ? rect.width  : window.innerWidth;
    var sh = rect && rect.height > 0 ? rect.height : (window.innerHeight - 165);
    var pw = Math.floor((sw - 14) / 2) - 4;
    var ph = sh - 10;
    if (pw <= 0 || ph <= 0 || natW <= 0 || natH <= 0) return 1.0;
    
    /* Optimal large reading fit: fill width or height comfortably (1.25x multiplier for crisp large text) */
    var scaleH = ph / natH;
    var scaleW = pw / natW;
    var optimal = Math.max(scaleH, scaleW * 0.95);
    baseFitScale = Math.min(Math.max(optimal, 0.8), 3.0);
    return baseFitScale;
  }

  function calcFitScaleMobile(natW, natH) {
    var stage = document.getElementById('bv-stage');
    var rect  = stage ? stage.getBoundingClientRect() : null;
    var w = rect && rect.width  > 0 ? rect.width  - 8 : window.innerWidth - 16;
    var h = rect && rect.height > 0 ? rect.height - 10 : (window.innerHeight - 165);
    if (w <= 0 || h <= 0 || natW <= 0 || natH <= 0) return 1.0;
    var optimal = Math.max(h / natH, w / natW);
    baseFitScale = Math.min(Math.max(optimal, 0.8), 3.0);
    return baseFitScale;
  }

  /* ── Bootstrap ── */
  function init() {
    var root = document.getElementById('bv-root');
    pdfUrl = root && root.getAttribute('data-pdf-url');
    if (!pdfUrl) return;

    canvasLeft   = document.getElementById('bv-canvas-left');
    canvasRight  = document.getElementById('bv-canvas-right');
    canvasSingle = document.getElementById('bv-canvas-single');
    loadingEl    = document.getElementById('bv-loading');
    pageInfoEl   = document.getElementById('bv-page-info');
    zoomInfoEl   = document.getElementById('bv-zoom-info');
    progressFill = document.getElementById('bv-progress-fill');
    progressLabel= document.getElementById('bv-progress-label');
    thumbsWrap   = document.getElementById('bv-thumbs');

    if (canvasLeft)   ctxLeft   = canvasLeft.getContext('2d');
    if (canvasRight)  ctxRight  = canvasRight.getContext('2d');
    if (canvasSingle) ctxSingle = canvasSingle.getContext('2d');

    bindControls();
    initTabs();
    loadBookmarks();
    renderBookmarkList();
    var bmBtn = document.getElementById('bv-btn-bookmark');
    if (bmBtn) bmBtn.addEventListener('click', function () { toggleBookmark(); });
    preloadPageSound();
    loadPdfJs();
  }

  /* ── Load PDF.js ── */
  function loadPdfJs() {
    if (window.pdfjsLib) { startPdf(); return; }
    var s = document.createElement('script');
    s.src = PDFJS_CDN;
    s.onload = function () {
      window.pdfjsLib.GlobalWorkerOptions.workerSrc = PDFJS_WORKER;
      startPdf();
    };
    s.onerror = function () {
      showError('PDF.js yuklanmadi. Internet aloqasini tekshiring.');
    };
    document.head.appendChild(s);
  }

  /* ── Open PDF ── */
  function startPdf() {
    showLoading(true);
    window.pdfjsLib.getDocument({
      url: pdfUrl,
      cMapUrl: 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/cmaps/',
      cMapPacked: true
    }).promise.then(function (doc) {
      pdfDoc      = doc;
      totalPages  = doc.numPages;
      currentPage = 1;
      renderSpread(1, false, null);
      buildThumbs();
    }).catch(function (err) {
      showError('PDF ochilmadi: ' + (err && err.message ? err.message : String(err)));
    });
  }

  /* ── Render one page ── */
  function renderOnePage(pageNum, canvas, ctx, scaleFix, cb) {
    if (!pdfDoc || !canvas || !ctx) { cb && cb(scale); return; }
    if (pageNum < 1 || pageNum > totalPages) { cb && cb(scale); return; }

    pdfDoc.getPage(pageNum).then(function (page) {
      var dpr = window.devicePixelRatio || 1;
      var vp1 = page.getViewport({ scale: 1 });

      var useScale;
      if (scaleFix != null) {
        useScale = scaleFix;
      } else {
        isMobile = window.innerWidth < 900;
        useScale = isMobile
          ? calcFitScaleMobile(vp1.width, vp1.height)
          : calcFitScale(vp1.width, vp1.height);
      }
      scale = useScale;

      var vp = page.getViewport({ scale: useScale * dpr });
      canvas.width        = vp.width;
      canvas.height       = vp.height;
      canvas.style.width  = (vp.width  / dpr) + 'px';
      canvas.style.height = (vp.height / dpr) + 'px';
      checkZoomState();

      page.render({ canvasContext: ctx, viewport: vp }).promise
        .then(function ()  { cb && cb(useScale); })
        .catch(function () { cb && cb(useScale); });

    }).catch(function () { cb && cb(scale); });
  }

  /* ── Render spread: left then right with SAME scale ── */
  function renderSpread(leftNum, animate, direction) {
    if (isRendering) return;
    isRendering = true;

    isMobile = window.innerWidth < 900;
    var rightNum = leftNum + 1;

    if (isMobile) {
      renderOnePage(leftNum, canvasSingle, ctxSingle, null, function () {
        isRendering = false;
        updateUI();
        showLoading(false);
      });
      return;
    }

    /* Left page — auto scale */
    renderOnePage(leftNum, canvasLeft, ctxLeft, null, function (usedScale) {

      /* Right page — same scale as left */
      if (rightNum <= totalPages) {
        renderOnePage(rightNum, canvasRight, ctxRight, usedScale, function () {
          isRendering = false;
          if (animate) {
            playFlip(direction, function () { updateUI(); showLoading(false); });
          } else {
            updateUI();
            showLoading(false);
          }
        });
      } else {
        clearCanvas(canvasRight, ctxRight, canvasLeft);
        isRendering = false;
        if (animate) {
          playFlip(direction, function () { updateUI(); showLoading(false); });
        } else {
          updateUI();
          showLoading(false);
        }
      }
    });
  }

  /* ── Render spread with fixed scale (zoom/resize) ── */
  function renderSpreadFixed(leftNum) {
    if (isRendering) return;
    isRendering = true;

    isMobile = window.innerWidth < 900;
    var rightNum = leftNum + 1;

    if (isMobile) {
      renderOnePage(leftNum, canvasSingle, ctxSingle, scale, function () {
        isRendering = false; updateUI(); showLoading(false);
      });
      return;
    }

    renderOnePage(leftNum, canvasLeft, ctxLeft, scale, function () {
      if (rightNum <= totalPages) {
        renderOnePage(rightNum, canvasRight, ctxRight, scale, function () {
          isRendering = false; updateUI(); showLoading(false);
        });
      } else {
        clearCanvas(canvasRight, ctxRight, canvasLeft);
        isRendering = false; updateUI(); showLoading(false);
      }
    });
  }

  function clearCanvas(canvas, ctx, ref) {
    if (!canvas) return;
    if (ref) {
      canvas.width        = ref.width;
      canvas.height       = ref.height;
      canvas.style.width  = ref.style.width;
      canvas.style.height = ref.style.height;
    }
    if (ctx) ctx.clearRect(0, 0, canvas.width, canvas.height);
  }

  /* ── Page flip with 3D Canvas Snapshot ── */
  function playFlip(direction, callback) {
    var flipContainer = document.querySelector('.bv-flip-container');
    var flipEl = document.getElementById('bv-flip-leaf');
    if (!flipEl) { callback && callback(); return; }

    var canvasFront = document.getElementById('bv-canvas-flip-front');
    var canvasBack  = document.getElementById('bv-canvas-flip-back');

    try {
      if (canvasFront && canvasRight && canvasRight.width > 0) {
        canvasFront.width  = canvasRight.width;
        canvasFront.height = canvasRight.height;
        var ctxF = canvasFront.getContext('2d');
        ctxF.drawImage(canvasRight, 0, 0);
      }
      if (canvasBack && canvasLeft && canvasLeft.width > 0) {
        canvasBack.width  = canvasLeft.width;
        canvasBack.height = canvasLeft.height;
        var ctxB = canvasBack.getContext('2d');
        ctxB.drawImage(canvasLeft, 0, 0);
      }
    } catch (e) {}

    if (flipContainer) flipContainer.classList.add('is-active');
    isFlipping = true;
    flipEl.classList.remove('bv-flipping-next', 'bv-flipping-prev');
    void flipEl.offsetWidth;
    flipEl.classList.add(direction === 'next' ? 'bv-flipping-next' : 'bv-flipping-prev');

    setTimeout(function () {
      flipEl.classList.remove('bv-flipping-next', 'bv-flipping-prev');
      if (flipContainer) flipContainer.classList.remove('is-active');
      isFlipping = false;
      callback && callback();
    }, 420);
  }

  /* ── Navigation ── */
  function goNext() {
    if (isFlipping || isRendering) return;
    if (isMobile) {
      if (currentPage >= totalPages) return;
      currentPage += 1;
    } else {
      if (currentPage + 1 >= totalPages) return;
      currentPage += 2;
    }
    playPageSound();
    renderSpread(currentPage, true, 'next');
  }

  function goPrev() {
    if (isFlipping || isRendering) return;
    if (isMobile) {
      if (currentPage <= 1) return;
      currentPage -= 1;
    } else {
      if (currentPage <= 1) return;
      currentPage = Math.max(1, currentPage - 2);
    }
    playPageSound();
    renderSpread(currentPage, true, 'prev');
  }

  function goToPage(num) {
    if (isFlipping || isRendering) return;
    num = Math.max(1, Math.min(num, totalPages));
    if (!isMobile && num % 2 === 0) num = Math.max(1, num - 1);
    currentPage = num;
    playPageSound();
    renderSpread(currentPage, true, 'next');
  }

  function checkZoomState() {
    var stage = document.getElementById('bv-stage');
    if (!stage) return;
    if (scale > (baseFitScale * 1.05)) {
      stage.classList.add('is-zoomed');
    } else {
      stage.classList.remove('is-zoomed');
    }
  }

  /* ── Zoom ── */
  function zoomIn()  {
    scale = Math.min(scale + 0.25, 4.0);
    checkZoomState();
    if (!isRendering) renderSpreadFixed(currentPage);
  }
  function zoomOut() {
    scale = Math.max(scale - 0.25, 0.4);
    checkZoomState();
    if (!isRendering) renderSpreadFixed(currentPage);
  }

  /* ── Fullscreen ── */
  function toggleFullscreen() {
    var stage = document.getElementById('bv-stage');
    if (!stage) return;
    if (!document.fullscreenElement) {
      stage.requestFullscreen && stage.requestFullscreen();
    } else {
      document.exitFullscreen && document.exitFullscreen();
    }
  }

  /* ── Page Turn Sound (MP3) ── */
  var pageFlipAudio = null;
  var pageFlipAudioReady = false;

  function preloadPageSound() {
    try {
      if (pageFlipAudio) return;
      var root = document.getElementById('bv-root');
      var base = (root && root.getAttribute('data-base-url')) || '';
      pageFlipAudio = new Audio(base + '/temp/sounds/oxidvideos-page-flip-1-178322.mp3');
      pageFlipAudio.volume = 0.5;
      pageFlipAudio.preload = 'auto';
      pageFlipAudio.addEventListener('canplaythrough', function() {
        pageFlipAudioReady = true;
      });
      pageFlipAudio.load();
    } catch (e) {}
  }

  function playPageSound() {
    try {
      if (localStorage.getItem('site-audio-muted') === 'true') return;
      if (!pageFlipAudio) preloadPageSound();
      if (!pageFlipAudio) return;
      pageFlipAudio.currentTime = 0;
      pageFlipAudio.play().catch(function() {});
    } catch (e) {}
  }

  /* ── Thumbnails (lazy-load + retry) ── */
  function buildThumbs() {
    if (!thumbsWrap || !pdfDoc) return;
    thumbsWrap.innerHTML = '';
    var ts = 0.15;

    function renderThumb(n, c, attempt) {
      attempt = attempt || 1;
      pdfDoc.getPage(n).then(function (page) {
        var vp = page.getViewport({ scale: ts });
        c.width = vp.width; c.height = vp.height;
        return page.render({ canvasContext: c.getContext('2d'), viewport: vp }).promise;
      }).catch(function (err) {
        if (attempt < 3) {
          setTimeout(function () { renderThumb(n, c, attempt + 1); }, 600 * attempt);
        } else {
          console.error('[BookViewer] Thumbnail render xatosi (sahifa ' + n + '):', err);
        }
      });
    }

    var obs = window.IntersectionObserver
      ? new IntersectionObserver(function (entries) {
          entries.forEach(function (entry) {
            if (!entry.isIntersecting) return;
            var wrap = entry.target;
            if (wrap.dataset.loaded) return;
            wrap.dataset.loaded = '1';
            obs.unobserve(wrap);
            renderThumb(
              parseInt(wrap.getAttribute('data-page'), 10),
              wrap.querySelector('canvas')
            );
          });
        }, { root: thumbsWrap, rootMargin: '0px 120px 0px 120px', threshold: 0 })
      : null;

    for (var i = 1; i <= totalPages; i++) {
      (function (n) {
        var wrap = document.createElement('div');
        wrap.className = 'bv-thumb';
        wrap.setAttribute('data-page', n);
        wrap.title = n + '-sahifa';
        var c = document.createElement('canvas');
        wrap.appendChild(c);
        thumbsWrap.appendChild(wrap);
        wrap.addEventListener('click', function () { goToPage(n); });

        if (obs) {
          obs.observe(wrap);
        } else {
          /* IntersectionObserver yo'q — darhol render (eski brauzer) */
          renderThumb(n, c);
        }
      })(i);
    }
  }

  function updateThumbActive() {
    if (!thumbsWrap) return;
    thumbsWrap.querySelectorAll('.bv-thumb').forEach(function (el) {
      var p = parseInt(el.getAttribute('data-page'), 10);
      el.classList.toggle('active', p === currentPage || p === currentPage + 1);
    });
    var active = thumbsWrap.querySelector('.bv-thumb.active');
    if (active) {
      var wL = thumbsWrap.scrollLeft;
      var wR = wL + thumbsWrap.clientWidth;
      var eL = active.offsetLeft;
      var eR = eL + active.offsetWidth;
      if (eL < wL) thumbsWrap.scrollLeft = eL - 8;
      else if (eR > wR) thumbsWrap.scrollLeft = eR - thumbsWrap.clientWidth + 8;
    }
  }

  /* ── UI update ── */
  function updateUI() {
    var rightNum = isMobile ? currentPage : Math.min(currentPage + 1, totalPages);
    var label = isMobile
      ? currentPage + ' / ' + totalPages
      : currentPage + '-' + rightNum + ' / ' + totalPages;

    if (pageInfoEl) {
      pageInfoEl.textContent = label;
      var inp = document.getElementById('bv-page-input-inline');
      if (inp) {
        if (!inp.style.display || inp.style.display === 'none') {
          pageInfoEl.style.display = '';
        }
        inp.max = totalPages;
      }
    }

    if (zoomInfoEl) {
      if (zoomInfoEl.tagName === 'INPUT') {
        zoomInfoEl.value = Math.round(scale * 100);
      } else {
        zoomInfoEl.textContent = Math.round(scale * 100) + '%';
      }
    }

    var pct = totalPages > 1 ? ((currentPage - 1) / (totalPages - 1)) * 100 : 100;
    if (progressFill)  progressFill.style.width = pct.toFixed(1) + '%';
    if (progressLabel) progressLabel.textContent = Math.round(pct) + '% o\'qildi';

    var step = isMobile ? 1 : 2;
    document.querySelectorAll('#bv-btn-prev, .bv-nav-prev').forEach(function (b) {
      b.disabled = currentPage <= 1;
    });
    document.querySelectorAll('#bv-btn-next, .bv-nav-next').forEach(function (b) {
      b.disabled = isMobile ? currentPage >= totalPages : currentPage + 1 >= totalPages;
    });

    var pnL = document.getElementById('bv-pnum-left');
    var pnR = document.getElementById('bv-pnum-right');
    if (pnL) pnL.textContent = currentPage;
    if (pnR) pnR.textContent = (currentPage + 1 <= totalPages) ? currentPage + 1 : '';

    updateThumbActive();
    updateBookmarkBtn();
  }

  /* ── Loading / Error ── */
  function showLoading(show) {
    if (!loadingEl) return;
    if (show) {
      loadingEl.style.display = 'flex';
      loadingEl.classList.remove('hidden');
    } else {
      loadingEl.classList.add('hidden');
    }
  }

  function showError(msg) {
    showLoading(false);
    var el  = document.getElementById('bv-error');
    var txt = document.getElementById('bv-error-text');
    if (el)  el.style.display = 'flex';
    if (txt) txt.textContent  = msg;
  }

  /* ── Dog-ear drag ── */
  function bindDogEarDrag(el, direction) {
    if (!el) return;
    var dragging = false, startX = 0;
    el.addEventListener('mousedown', function (e) {
      e.preventDefault();
      dragging = true; startX = e.clientX;
      var fl = document.getElementById('bv-flip-leaf');
      if (fl) { fl.style.transition = 'none'; fl.classList.remove('bv-flipping-next', 'bv-flipping-prev'); }

      function onDrag(e) {
        var fl = document.getElementById('bv-flip-leaf');
        if (!fl || !dragging) return;
        var dx = e.clientX - startX;
        var p  = direction === 'next' ? Math.max(0, Math.min(1, -dx / 200)) : Math.max(0, Math.min(1, dx / 200));
        fl.style.transform = 'rotateY(' + (direction === 'next' ? -(p * 180) : (p * 180) - 180) + 'deg)';
      }
      function onRelease(e) {
        dragging = false;
        document.removeEventListener('mousemove', onDrag);
        document.removeEventListener('mouseup', onRelease);
        var fl = document.getElementById('bv-flip-leaf');
        if (fl) { fl.style.transition = ''; fl.style.transform = ''; }
        var dx = e.clientX - startX;
        if (direction === 'next' ? -dx > 60 : dx > 60) {
          direction === 'next' ? goNext() : goPrev();
        }
      }
      document.addEventListener('mousemove', onDrag);
      document.addEventListener('mouseup', onRelease);
    });
  }

  /* ── Bind controls ── */
  function bindControls() {
    document.addEventListener('keydown', function (e) {
      if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') return;
      if (e.key === 'ArrowRight' || e.key === 'ArrowDown') { e.preventDefault(); goNext(); }
      if (e.key === 'ArrowLeft'  || e.key === 'ArrowUp')   { e.preventDefault(); goPrev(); }
      if (e.key === '+' || e.key === '=') { e.preventDefault(); zoomIn(); }
      if (e.key === '-')                  { e.preventDefault(); zoomOut(); }
      if (e.key === 'f' || e.key === 'F') { e.preventDefault(); toggleFullscreen(); }
    });

    var touchStartX = 0;
    var touchStartY = 0;
    var swipeTarget  = document.getElementById('bv-stage') || document;

    swipeTarget.addEventListener('touchstart', function (e) {
      touchStartX = e.touches[0].clientX;
      touchStartY = e.touches[0].clientY;
    }, { passive: true });

    swipeTarget.addEventListener('touchend', function (e) {
      var dx = e.changedTouches[0].clientX - touchStartX;
      var dy = e.changedTouches[0].clientY - touchStartY;
      /* Gorizontal swipe — vertikal scroll bilan aralashmasin */
      if (Math.abs(dx) > Math.abs(dy) && Math.abs(dx) > 50) {
        dx < 0 ? goNext() : goPrev();
      }
    }, { passive: true });

    /* Mouse drag on book pages (desktop) */
    var mouseStartX = 0;
    var mouseDragging = false;
    var bookWrap = document.getElementById('bv-book-wrap');
    var dragTarget = bookWrap || document.getElementById('bv-stage');
    if (dragTarget) {
      dragTarget.addEventListener('mousedown', function (e) {
        /* Dog-ear elementlarini o'tkazib yuborish */
        if (e.target.classList.contains('bv-dog-ear')) return;
        mouseDragging = true;
        mouseStartX   = e.clientX;
      });
      dragTarget.addEventListener('mouseup', function (e) {
        if (!mouseDragging) return;
        mouseDragging = false;
        var dx = e.clientX - mouseStartX;
        if (Math.abs(dx) > 60) {
          dx < 0 ? goNext() : goPrev();
        }
      });
      dragTarget.addEventListener('mouseleave', function () {
        mouseDragging = false;
      });
    }

    document.querySelectorAll('#bv-btn-prev, .bv-nav-prev').forEach(function (el) {
      el.addEventListener('click', function (e) { e.preventDefault(); goPrev(); });
    });
    document.querySelectorAll('#bv-btn-next, .bv-nav-next').forEach(function (el) {
      el.addEventListener('click', function (e) { e.preventDefault(); goNext(); });
    });

    bindBtn('bv-btn-zoom-in',    zoomIn);
    bindBtn('bv-btn-zoom-out',   zoomOut);
    bindBtn('bv-btn-fullscreen', toggleFullscreen);
    bindBtn('bv-btn-first',      function () { goToPage(1); });
    bindBtn('bv-btn-last',       function () { if (totalPages) goToPage(totalPages); });

    /* Zoom input */
    var zi = document.getElementById('bv-zoom-info');
    if (zi && zi.tagName === 'INPUT') {
      function applyZoom() {
        var v = parseInt(zi.value, 10);
        if (isNaN(v)) return;
        v = Math.min(Math.max(v, 30), 400);
        zi.value = v;
        scale = v / 100;
        if (!isRendering) renderSpreadFixed(currentPage);
      }
      zi.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') { e.preventDefault(); applyZoom(); zi.blur(); }
        e.stopPropagation();
      });
      zi.addEventListener('change', applyZoom);
      zi.addEventListener('focus',  function () { zi.select(); });
    }

    /* Page input inline */
    var pi   = document.getElementById('bv-page-input-inline');
    var piSp = document.getElementById('bv-page-info');
    if (pi) {
      function applyPage() {
        var v = parseInt(pi.value, 10);
        pi.style.display = 'none';
        if (piSp) piSp.style.display = '';
        if (!isNaN(v)) goToPage(v);
      }
      pi.addEventListener('keydown', function (e) {
        if (e.key === 'Enter')  { e.preventDefault(); applyPage(); }
        if (e.key === 'Escape') { pi.style.display = 'none'; if (piSp) piSp.style.display = ''; }
        e.stopPropagation();
      });
      pi.addEventListener('blur',  applyPage);
      pi.addEventListener('focus', function () { pi.select(); });
    }

    bindDogEarDrag(document.getElementById('bv-dog-ear-right'), 'next');
    bindDogEarDrag(document.getElementById('bv-dog-ear-left'),  'prev');
    bindBtn('bv-dog-ear-left',  goPrev);
    bindBtn('bv-dog-ear-right', goNext);

    var rt;
    window.addEventListener('resize', function () {
      clearTimeout(rt);
      rt = setTimeout(function () {
        isMobile = window.innerWidth < 900;
        if (!isRendering) renderSpread(currentPage, false, null);
      }, 200);
    });

    document.addEventListener('fullscreenchange', function () {
      setTimeout(function () {
        if (!isRendering) renderSpread(currentPage, false, null);
      }, 150);
    });
  }

  function bindBtn(id, fn) {
    var el = document.getElementById(id);
    if (el) el.addEventListener('click', function (e) { e.preventDefault(); fn(); });
  }

  /* ── Tabs ── */
  function initTabs() {
    document.querySelectorAll('.bv-tab').forEach(function (tab) {
      tab.addEventListener('click', function () {
        document.querySelectorAll('.bv-tab').forEach(function (t) { t.classList.remove('active'); });
        document.querySelectorAll('.bv-tab-panel').forEach(function (p) { p.classList.add('bv-tab-panel--hidden'); });
        tab.classList.add('active');
        var panel = document.getElementById('bv-tab-' + tab.getAttribute('data-tab'));
        if (panel) panel.classList.remove('bv-tab-panel--hidden');
      });
    });
  }

  /* ── Bookmarks (localStorage) ── */
  var BM_KEY = 'bv_bookmarks_' + (window.location.pathname);
  var bookmarks = [];

  function loadBookmarks() {
    try { bookmarks = JSON.parse(localStorage.getItem(BM_KEY) || '[]'); } catch(e) { bookmarks = []; }
  }
  function saveBookmarks() {
    try { localStorage.setItem(BM_KEY, JSON.stringify(bookmarks)); } catch(e) {}
  }
  function toggleBookmark() {
    var p = currentPage;
    var idx = bookmarks.indexOf(p);
    if (idx === -1) { bookmarks.push(p); bookmarks.sort(function(a,b){return a-b;}); }
    else { bookmarks.splice(idx, 1); }
    saveBookmarks();
    renderBookmarkList();
    updateBookmarkBtn();
  }
  function updateBookmarkBtn() {
    var btn  = document.getElementById('bv-btn-bookmark');
    var icon = document.getElementById('bv-bm-icon');
    if (!btn || !icon) return;
    var active = bookmarks.indexOf(currentPage) !== -1;
    btn.classList.toggle('active', active);
    icon.className = active ? 'fa-solid fa-bookmark' : 'fa-regular fa-bookmark';
  }
  function renderBookmarkList() {
    var list  = document.getElementById('bv-bm-list');
    var empty = document.getElementById('bv-bm-empty');
    if (!list) return;
    list.innerHTML = '';
    if (bookmarks.length === 0) {
      if (empty) empty.style.display = '';
      return;
    }
    if (empty) empty.style.display = 'none';
    bookmarks.forEach(function (p) {
      var chip = document.createElement('span');
      chip.className = 'bv-bm-chip';
      chip.innerHTML = '<i class="fa-solid fa-bookmark"></i>' + p + '-sahifa<span class="bv-bm-del" title="O\'chirish">×</span>';
      chip.querySelector('.bv-bm-del').addEventListener('click', function (e) {
        e.stopPropagation();
        bookmarks.splice(bookmarks.indexOf(p), 1);
        saveBookmarks(); renderBookmarkList(); updateBookmarkBtn();
      });
      chip.addEventListener('click', function () { goToPage(p); });
      list.appendChild(chip);
    });
  }

  /* ── Globals ── */
  window.bvGoNext     = goNext;
  window.bvGoPrev     = goPrev;
  window.bvZoomIn     = zoomIn;
  window.bvZoomOut    = zoomOut;
  window.bvFullscreen = toggleFullscreen;
  window.bvGoFirst    = function () { goToPage(1); };
  window.bvGoLast     = function () { if (totalPages) goToPage(totalPages); };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

})();
