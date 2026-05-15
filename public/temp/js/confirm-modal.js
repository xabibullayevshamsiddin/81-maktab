/**
 * Formlarga data-confirm="matn" qo‘shing — brauzer confirm() o‘rniga markaziy modal.
 * data-confirm-title, data-confirm-variant="danger|primary|info", data-confirm-ok
 */
(function () {
  var modalEl = null;
  var bypass = new WeakMap();
  var pendingResolve = null;

  function iconHtml(variant) {
    if (variant === 'info') return '<i class="fa-solid fa-circle-info"></i>';
    if (variant === 'success') return '<i class="fa-solid fa-circle-check"></i>';
    if (variant === 'primary') return '<i class="fa-solid fa-circle-question"></i>';
    return '<i class="fa-solid fa-triangle-exclamation"></i>';
  }

  function ensureModal() {
    if (modalEl) return modalEl;
    modalEl = document.getElementById('prime-confirm-modal');
    if (!modalEl) return null;

    var backdrop = modalEl.querySelector('.prime-confirm__backdrop');
    var btnCancel = modalEl.querySelector('[data-prime-confirm-cancel]');
    var btnOk = modalEl.querySelector('[data-prime-confirm-ok]');
    var titleEl = modalEl.querySelector('.prime-confirm__title');
    var msgEl = modalEl.querySelector('.prime-confirm__message');
    var iconContainer = modalEl.querySelector('.prime-confirm__icon-inner');

    function closeModal(result) {
      modalEl.classList.remove('is-open');
      modalEl.setAttribute('aria-hidden', 'true');
      
      // Delay removing the body class to allow exit animation to breathe
      setTimeout(function() {
        if (!modalEl.classList.contains('is-open')) {
          document.body.classList.remove('prime-confirm-open');
        }
      }, 400);

      var fn = pendingResolve;
      pendingResolve = null;
      if (fn) fn(!!result);
    }

    function applyContent(opts) {
      opts = opts || {};
      if (titleEl) titleEl.textContent = opts.title || 'Tasdiqlash';
      if (msgEl) msgEl.textContent = opts.message || '';
      var variant = opts.variant || 'danger';
      modalEl.classList.remove('prime-confirm--info', 'prime-confirm--success', 'prime-confirm--danger', 'prime-confirm--primary');
      modalEl.classList.add('prime-confirm--' + variant);
      
      if (iconContainer) {
        iconContainer.innerHTML = iconHtml(variant);
      }

      if (btnOk) {
        btnOk.textContent = opts.okText || (variant === 'danger' ? 'Ha, o‘chirish' : 'Tasdiqlash');
        var okClass = 'prime-confirm__btn--' + variant;
        btnOk.className = 'prime-confirm__btn ' + okClass;
      }
    }

    if (backdrop) backdrop.addEventListener('click', function () { closeModal(false); });
    if (btnCancel) btnCancel.addEventListener('click', function () { closeModal(false); });
    if (btnOk)
      btnOk.addEventListener('click', function () {
        closeModal(true);
      });

    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && modalEl.classList.contains('is-open')) closeModal(false);
    });

    modalEl._applyContent = applyContent;
    return modalEl;
  }

  function openModal(opts) {
    var el = ensureModal();
    if (!el) return Promise.resolve(false);
    return new Promise(function (resolve) {
      pendingResolve = resolve;
      el._applyContent(opts);
      el.setAttribute('aria-hidden', 'false');
      el.classList.add('is-open');
      document.body.classList.add('prime-confirm-open');
    });
  }

  /** JS ichidan (fetch va h.k.): window.primeConfirm({ message, title?, variant?, okText? }) */
  window.primeConfirm = function (opts) {
    return openModal(opts || {});
  };

  document.addEventListener(
    'submit',
    function (e) {
      var form = e.target;
      if (!form || !(form instanceof HTMLFormElement)) return;
      var msg = form.getAttribute('data-confirm');
      if (!msg) return;
      if (bypass.has(form)) {
        bypass.delete(form);
        return;
      }
      e.preventDefault();
      e.stopPropagation();

      var title = form.getAttribute('data-confirm-title') || '';
      var variant = form.getAttribute('data-confirm-variant') || 'danger';
      var okText = form.getAttribute('data-confirm-ok') || '';

      openModal({
        message: msg,
        title: title || undefined,
        variant: variant,
        okText: okText || undefined,
      }).then(function (confirmed) {
        if (!confirmed) return;
        bypass.set(form, true);
        if (typeof form.requestSubmit === 'function') {
          form.requestSubmit();
        } else {
          form.submit();
        }
      });
    },
    true
  );
})();
