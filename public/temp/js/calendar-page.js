(() => {
  const list = document.querySelector('.calendar-event-list');
  if (!list) return;

  const days = list.querySelectorAll('details.calendar-day-dtls');
  if (!days.length) return;

  const wide = window.matchMedia('(min-width: 769px)');
  const yearSelect = document.querySelector('[data-calendar-year-select]');

  function thresholdY() {
    return window.innerHeight * 0.11;
  }

  function setAllOpen(open) {
    days.forEach((day) => {
      day.open = open;
    });
  }

  function sync() {
    if (wide.matches) {
      setAllOpen(true);
    } else {
      setAllOpen(window.scrollY >= thresholdY());
    }
  }

  function onScroll() {
    if (!wide.matches && window.scrollY >= thresholdY()) {
      setAllOpen(true);
    }
  }

  if (typeof wide.addEventListener === 'function') {
    wide.addEventListener('change', sync);
  } else if (typeof wide.addListener === 'function') {
    wide.addListener(sync);
  }

  window.addEventListener('scroll', onScroll, { passive: true });
  yearSelect?.addEventListener('change', () => {
    yearSelect.form?.submit();
  });

  sync();
  onScroll();
})();
