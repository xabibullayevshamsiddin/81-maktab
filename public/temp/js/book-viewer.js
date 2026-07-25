/**
 * BOOK VIEWER — PDF.js Custom Reader
 * 81-IDUM Aurora Glassmorphism
 * Fixes: auto-scale, right page blank, duplicate IDs, dog-ear drag, no reload
 */
(function () {
  'use strict';

  var PDFJS_CDN    = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js';
  var PDFJS_WORKER = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

  var pdfDoc      = null;
  var currentPage = 1;
  var totalPages  = 0;
  var scale       = 1.0;   /* FIX-1: calculated dynamically, not hardcoded */
  var isRendering = false;
  var isMobile    = window.innerWidth < 900;
  var isFlipping  = false;

  var canvasLeft, canvasRight, canvasSingle;
  var ctxLeft, ctxRight, ctxSingle;
  var loadingEl, pageInfoEl, zoomInfoEl;
  var progressFill, progressLabel;
  var thumbsWrap;
  var pdfUrl;

  /* ─────────────────────────────────────────
     FIX-1: Fit-to-page scale calculator
     Measures the actual container size and
     calculates the scale so the page fills it.
  ───────────────────────────────────────── */
  function calcFitScale(page) {
    var dpr = window.devicePixelRatio || 1;
    isMobile = window.innerWidth < 900;

    /* Natural PDF page size at scale=1 */
    var naturalVp = page.getViewport({ scale: 1 });
    var natW = naturalVp.width;
    var natH = naturalVp.height;

    var containerW, containerH;

    if (isMobile) {
      var single = document.getElementById('bv-single-page');
      containerW = single ? single.clientWidth  - 8  : window.innerWidth  - 32;
      containerH = single ? single.clientHeight - 8  : window.innerHeight - 200;
    } else {
      /* Each page gets half the book-wrap width minus spine (14px) */
      var bookWrap = document.getElementById('bv-book-wrap');
      var stageW   = bookWrap ? bookWrap.clientWidth  : window.innerWidth  - 80;
      var stageH   = bookWrap ? bookWrap.clientHeight : window.innerHeight - 260;
      containerW   = Math.floor((stageW - 14) / 2) - 4;
      containerH   = stageH - 4;
    }

    if (containerW <= 0 || containerH <= 0) return 1.0;

    var scaleW = containerW / natW;
    var scaleH = containerH / natH;
    var fit    = Math.min(scaleW, scaleH);

    /* Clamp: never below 0.3, never above 4.0 */
    return Math.min(Math.max(fit, 0.3), 4.0);
  }

  /* ─────────────────────────────────────────
     Bootstrap
  ───────────────────────────────────────── */
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
    loadPdfJs();
  }

  /* ─────────────────────────────────────────
     Load PDF.js
  ───────────────────────────────────────── */
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

  /* ─────────────────────────────────────────
     Open PDF
  ───────────────────────────────────────── */
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
      renderSpread(currentPage, false);
      buildThumbs();
    }).catch(function (err) {
      showError('PDF ochilmadi: ' + (err && err.message ? err.message : String(err)));
    });
  }

  /* ─────────────────────────────────────────
     FIX-3 + FIX-1: Render double-page spread
  ───────────────────────────────────────── */
  function renderSpread(leftPageNum, animate, direction) {
    if (isRendering) return;
    isRendering = true;

    var rightPageNum = leftPageNum + 1;
    isMobile = window.innerWidth < 900;

    /* ── MOBILE: single page ── */
    if (isMobile) {
      renderPageFit(leftPageNum, canvasSingle, ctxSingle, null, function () {
        isRendering = false;
        updateUI();
        showLoading(false);
      });
      return;
    }

    /* ── DESKTOP: render left first, then right with same scale ── */
    renderPageFit(leftPageNum, canvasLeft, ctxLeft, null, function (usedScale) {

      /* Right page — always render after left is done */
      if (rightPageNum <= totalPages) {
        renderPageFit(rightPageNum, canvasRight, ctxRight, usedScale, function () {
          isRendering = false;
          if (animate) {
            playFlip(direction, function () { updateUI(); showLoading(false); });
          } else {
            updateUI();
            showLoading(false);
          }
        });
      } else {
        /* Blank right page — same size as left */
        if (canvasRight && canvasLeft) {
          canvasRight.width        = canvasLeft.width;
          canvasRight.height       = canvasLeft.height;
          canvasRight.style.width  = canvasLeft.style.width;
          canvasRight.style.height = canvasLeft.style.height;
          if (ctxRight) ctxRight.clearRect(0, 0, canvasRight.width, canvasRight.height);
        }
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

  /* ─────────────────────────────────────────
     FIX-1: renderPageFit
     forcedScale = null  → auto-calculate fit
     forcedScale = number → use that scale (right page reuses left's scale)
     callback(usedScale) so caller can pass scale to right page
  ───────────────────────────────────────── */
  function renderPageFit(num, canvas, ctx, forcedScale, callback) {
    if (!pdfDoc || !canvas || !ctx) { if (callback) callback(scale); return; }
    if (num < 1 || num > totalPages) { if (callback) callback(scale); return; }

    pdfDoc.getPage(num).then(function (page) {
      var dpr        = window.devicePixelRatio || 1;
      var useScale   = (forcedScale != null) ? forcedScale : calcFitScale(page);
      scale          = useScale; /* keep global in sync */

      var viewport   = page.getViewport({ scale: useScale * dpr });

      canvas.width        = viewport.width;
      canvas.height       = viewport.height;
      canvas.style.width  = (viewport.width  / dpr) + 'px';
      canvas.style.height = (viewport.height / dpr) + 'px';

      page.render({ canvasContext: ctx, viewport: viewport }).promise
        .then(function ()  { if (callback) callback(useScale); })
        .catch(function () { if (callback) callback(useScale); });

    }).catch(function () { if (callback) callback(scale); });
  }

  /* ─────────────────────────────────────────
     Page flip CSS animation
  ───────────────────────────────────────── */
  function playFlip(direction, callback) {
    var flipEl = document.getElementById('bv-flip-leaf');
    if (!flipEl) { if (callback) callback(); return; }

    isFlipping = true;
    flipEl.classList.remove('bv-flipping-next', 'bv-flipping-prev');
    void flipEl.offsetWidth;
    flipEl.classList.add(direction === 'next' ? 'bv-flipping-next' : 'bv-flipping-prev');

    setTimeout(function () {
      flipEl.classList.remove('bv-flipping-next', 'bv-flipping-prev');
      isFlipping = false;
      if (callback) callback();
    }, 580);
  }

  /* ─────────────────────────────────────────
     Navigation — FIX-5: pure client-side,
     no href navigation, no page reload
     FIX-2: no showLoading on navigation
  ───────────────────────────────────────── */
  function goNext() {
    if (isFlipping || isRendering) return;
    var step = isMobile ? 1 : 2;
    if (currentPage + step - 1 >= totalPages) return;
    currentPage += step;
    renderSpread(currentPage, true, 'next');
  }

  function goPrev() {
    if (isFlipping || isRendering) return;
    var step = isMobile ? 1 : 2;
    if (currentPage <= 1) return;
    currentPage = Math.max(1, currentPage - step);
    renderSpread(currentPage, true, 'prev');
  }

  function goToPage(num) {
    if (isFlipping || isRendering) return;
    if (!isMobile && num % 2 === 0) num = Math.max(1, num - 1);
    currentPage = Math.max(1, Math.min(num, totalPages));
    renderSpread(currentPage, true, 'next');
  }

  /* ─────────────────────────────────────────
     Zoom — manual override of auto-scale
  ───────────────────────────────────────── */
  function zoomIn()  { scale = Math.min(scale + 0.2, 4.0); rerender(); }
  function zoomOut() { scale = Math.max(scale - 0.2, 0.3); rerender(); }

  function rerender() {
    if (isRendering) return;
    renderSpreadFixed(currentPage, false);
  }

  /* renderSpreadFixed: uses current global `scale`, no fit recalc */
  function renderSpreadFixed(leftPageNum, animate) {
    if (isRendering) return;
    isRendering = true;
    var rightPageNum = leftPageNum + 1;
    isMobile = window.innerWidth < 900;

    if (isMobile) {
      renderPageFit(leftPageNum, canvasSingle, ctxSingle, scale, function () {
        isRendering = false; updateUI(); showLoading(false);
      });
      return;
    }

    var leftDone = false, rightDone = false;
    function check() {
      if (!leftDone || !rightDone) return;
      isRendering = false; updateUI(); showLoading(false);
    }

    renderPageFit(leftPageNum, canvasLeft, ctxLeft, scale, function () {
      leftDone = true;
      if (rightPageNum <= totalPages) {
        renderPageFit(rightPageNum, canvasRight, ctxRight, scale, function () {
          rightDone = true; check();
        });
      } else {
        if (canvasRight && canvasLeft) {
          canvasRight.width  = canvasLeft.width;
          canvasRight.height = canvasLeft.height;
          canvasRight.style.width  = canvasLeft.style.width;
          canvasRight.style.height = canvasLeft.style.height;
          if (ctxRight) ctxRight.clearRect(0, 0, canvasRight.width, canvasRight.height);
        }
        rightDone = true; check();
      }
      check();
    });
  }

  /* ─────────────────────────────────────────
     Fullscreen
  ───────────────────────────────────────── */
  function toggleFullscreen() {
    var stage = document.getElementById('bv-stage');
    if (!stage) return;
    if (!document.fullscreenElement) {
      stage.requestFullscreen && stage.requestFullscreen();
    } else {
      document.exitFullscreen && document.exitFullscreen();
    }
  }

  /* ─────────────────────────────────────────
     Thumbnails
  ───────────────────────────────────────── */
  function buildThumbs() {
    if (!thumbsWrap || !pdfDoc) return;
    thumbsWrap.innerHTML = '';
    var thumbScale = 0.15;
    for (var i = 1; i <= Math.min(totalPages, 40); i++) {
      (function (pageNum) {
        var wrap = document.createElement('div');
        wrap.className = 'bv-thumb' + (pageNum === currentPage ? ' active' : '');
        wrap.setAttribute('data-page', pageNum);
        wrap.title = pageNum + '-sahifa';
        var c = document.createElement('canvas');
        wrap.appendChild(c);
        thumbsWrap.appendChild(wrap);
        pdfDoc.getPage(pageNum).then(function (page) {
          var vp = page.getViewport({ scale: thumbScale });
          c.width = vp.width; c.height = vp.height;
          page.render({ canvasContext: c.getContext('2d'), viewport: vp });
        });
        wrap.addEventListener('click', function () { goToPage(pageNum); });
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
    if (active) active.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
  }

  /* ─────────────────────────────────────────
     UI update
  ───────────────────────────────────────── */
  function updateUI() {
    var displayRight = isMobile ? currentPage : Math.min(currentPage + 1, totalPages);
    var label = isMobile
      ? currentPage + ' / ' + totalPages
      : currentPage + '-' + displayRight + ' / ' + totalPages;

    if (pageInfoEl)    pageInfoEl.textContent  = label;
    if (zoomInfoEl)    zoomInfoEl.textContent  = Math.round(scale * 100) + '%';

    var pct = totalPages > 1 ? ((currentPage - 1) / (totalPages - 1)) * 100 : 100;
    if (progressFill)  progressFill.style.width = pct.toFixed(1) + '%';
    if (progressLabel) progressLabel.textContent = Math.round(pct) + '% o\'qildi';

    var step = isMobile ? 1 : 2;
    /* FIX-5: query all buttons with these IDs/classes */
    document.querySelectorAll('#bv-btn-prev, .bv-nav-prev').forEach(function (b) {
      b.disabled = currentPage <= 1;
    });
    document.querySelectorAll('#bv-btn-next, .bv-nav-next').forEach(function (b) {
      b.disabled = currentPage + step - 1 >= totalPages;
    });

    updateThumbActive();

    var pnLeft  = document.getElementById('bv-pnum-left');
    var pnRight = document.getElementById('bv-pnum-right');
    if (pnLeft)  pnLeft.textContent  = currentPage;
    if (pnRight) pnRight.textContent = (currentPage + 1 <= totalPages) ? currentPage + 1 : '';
  }

  /* ─────────────────────────────────────────
     Loading / Error
  ───────────────────────────────────────── */
  function showLoading(show) {
    if (!loadingEl) return;
    loadingEl.classList.toggle('hidden', !show);
  }

  function showError(msg) {
    showLoading(false);
    var el  = document.getElementById('bv-error');
    var txt = document.getElementById('bv-error-text');
    if (el)  el.style.display = 'flex';
    if (txt) txt.textContent  = msg;
  }

  /* ─────────────────────────────────────────
     FIX-4: Dog-ear drag (mousedown → drag → mouseup)
     Drag the corner to flip pages realistically.
  ───────────────────────────────────────── */
  function bindDogEarDrag(el, direction) {
    if (!el) return;

    var dragging   = false;
    var startX     = 0;
    var startY     = 0;
    var flipLeaf   = null;
    var THRESHOLD  = 60; /* px to trigger flip */

    el.addEventListener('mousedown', function (e) {
      e.preventDefault();
      dragging  = true;
      startX    = e.clientX;
      startY    = e.clientY;
      flipLeaf  = document.getElementById('bv-flip-leaf');
      if (flipLeaf) {
        flipLeaf.style.transition = 'none';
        flipLeaf.classList.remove('bv-flipping-next', 'bv-flipping-prev');
      }
      document.addEventListener('mousemove', onDrag);
      document.addEventListener('mouseup',   onRelease);
    });

    function onDrag(e) {
      if (!dragging || !flipLeaf) return;
      var dx = e.clientX - startX;
      /* direction: 'next' → drag left (negative dx), 'prev' → drag right */
      var progress = direction === 'next'
        ? Math.max(0, Math.min(1, -dx / 200))
        : Math.max(0, Math.min(1,  dx / 200));
      var deg = direction === 'next'
        ? -(progress * 180)
        :  (progress * 180) - 180;
      flipLeaf.style.transform = 'rotateY(' + deg + 'deg)';
    }

    function onRelease(e) {
      if (!dragging) return;
      dragging = false;
      document.removeEventListener('mousemove', onDrag);
      document.removeEventListener('mouseup',   onRelease);

      var dx = e.clientX - startX;
      var triggered = direction === 'next' ? (-dx > THRESHOLD) : (dx > THRESHOLD);

      if (flipLeaf) {
        flipLeaf.style.transition = '';
        flipLeaf.style.transform  = '';
      }

      if (triggered) {
        if (direction === 'next') goNext();
        else                      goPrev();
      }
    }
  }

  /* ─────────────────────────────────────────
     FIX-5: Bind controls — no href navigation
     All buttons use addEventListener, not onclick/href
  ───────────────────────────────────────── */
  function bindControls() {

    /* Keyboard */
    document.addEventListener('keydown', function (e) {
      if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') return;
      if (e.key === 'ArrowRight' || e.key === 'ArrowDown') { e.preventDefault(); goNext(); }
      if (e.key === 'ArrowLeft'  || e.key === 'ArrowUp')   { e.preventDefault(); goPrev(); }
      if (e.key === '+' || e.key === '=') { e.preventDefault(); zoomIn(); }
      if (e.key === '-')                  { e.preventDefault(); zoomOut(); }
      if (e.key === 'f' || e.key === 'F') { e.preventDefault(); toggleFullscreen(); }
    });

    /* Touch swipe */
    var touchStartX = 0;
    document.addEventListener('touchstart', function (e) {
      touchStartX = e.touches[0].clientX;
    }, { passive: true });
    document.addEventListener('touchend', function (e) {
      var dx = e.changedTouches[0].clientX - touchStartX;
      if (Math.abs(dx) > 60) { dx < 0 ? goNext() : goPrev(); }
    }, { passive: true });

    /* FIX-5: bind ALL elements with these IDs (toolbar + bottom-nav duplicates) */
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

    /* FIX-4: Dog-ear drag */
    bindDogEarDrag(document.getElementById('bv-dog-ear-right'), 'next');
    bindDogEarDrag(document.getElementById('bv-dog-ear-left'),  'prev');

    /* Dog-ear click fallback (tap on mobile) */
    bindBtn('bv-dog-ear-left',  goPrev);
    bindBtn('bv-dog-ear-right', goNext);

    /* Page input */
    var pageInput = document.getElementById('bv-page-input');
    if (pageInput) {
      pageInput.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') {
          var n = parseInt(this.value, 10);
          if (!isNaN(n)) goToPage(n);
          this.blur();
        }
      });
    }

    /* Resize → recalculate fit */
    var resizeTimer;
    window.addEventListener('resize', function () {
      clearTimeout(resizeTimer);
      resizeTimer = setTimeout(function () {
        var wasMobile = isMobile;
        isMobile = window.innerWidth < 900;
        /* Always rerender on resize to recalculate fit-scale */
        if (!isRendering) {
          showLoading(true);
          renderSpread(currentPage, false);
        }
      }, 200);
    });

    /* Fullscreen change → rerender */
    document.addEventListener('fullscreenchange', function () {
      setTimeout(function () {
        if (!isRendering) { showLoading(true); renderSpread(currentPage, false); }
      }, 150);
    });
  }

  function bindBtn(id, fn) {
    var el = document.getElementById(id);
    if (el) el.addEventListener('click', function (e) { e.preventDefault(); fn(); });
  }

  /* ─────────────────────────────────────────
     Expose globals (for any remaining inline refs)
  ───────────────────────────────────────── */
  window.bvGoNext     = goNext;
  window.bvGoPrev     = goPrev;
  window.bvZoomIn     = zoomIn;
  window.bvZoomOut    = zoomOut;
  window.bvFullscreen = toggleFullscreen;
  window.bvGoFirst    = function () { goToPage(1); };
  window.bvGoLast     = function () { if (totalPages) goToPage(totalPages); };

  /* ─────────────────────────────────────────
     Start
  ───────────────────────────────────────── */
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

})();
