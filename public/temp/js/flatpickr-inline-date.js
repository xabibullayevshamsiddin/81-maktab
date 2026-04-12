/**
 * Inline Flatpickr — faqat sana (Y-m-d), imtihon jadvali bilan bir xil ko‘rinish, soat yo‘q.
 */
(function () {
  function parseLocale(wrap) {
    if (!wrap || !wrap.getAttribute('data-locale')) {
      return { firstDayOfWeek: 1 };
    }
    try {
      var loc = JSON.parse(wrap.getAttribute('data-locale'));
      return Object.keys(loc).length ? loc : { firstDayOfWeek: 1 };
    } catch (e) {
      return { firstDayOfWeek: 1 };
    }
  }

  function bindClearButtons() {
    document.querySelectorAll('.js-fp-inline-clear').forEach(function (btn) {
      if (btn._fpClearBound) {
        return;
      }
      btn._fpClearBound = true;
      btn.addEventListener('click', function () {
        var id = btn.getAttribute('data-fp-for');
        var input = id ? document.getElementById(id) : null;
        if (!input || !input._flatpickr) {
          return;
        }
        input._flatpickr.clear();
        if (input.hasAttribute('data-fp-auto-submit') && input.form) {
          input.form.submit();
        }
      });
    });
  }

  function initInput(input) {
    if (input._fpInlineDateInited || typeof flatpickr === 'undefined') {
      return;
    }
    var wrap = input.closest('.exam-inline-calendar-wrap');
    if (!wrap) {
      return;
    }
    input._fpInlineDateInited = true;

    var localeOpts = parseLocale(wrap);
    var autoSubmit = input.hasAttribute('data-fp-auto-submit');

    flatpickr(input, {
      inline: true,
      dateFormat: 'Y-m-d',
      altInput: true,
      altFormat: 'd.m.Y',
      altInputClass: 'exam-fp-alt-input',
      allowInput: false,
      disableMobile: true,
      locale: localeOpts,
      defaultDate: input.value || null,
      onReady: function (_d, _s, instance) {
        if (instance.altInput) {
          instance.altInput.setAttribute('placeholder', 'Sanani tanlang');
          instance.altInput.setAttribute('readonly', 'readonly');
        }
      },
      onChange: function () {
        if (autoSubmit && input.form) {
          input.form.submit();
        }
      },
    });

    bindClearButtons();
  }

  function run() {
    document.querySelectorAll('input.js-fp-inline-date').forEach(initInput);
    bindClearButtons();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', run);
  } else {
    run();
  }
})();
