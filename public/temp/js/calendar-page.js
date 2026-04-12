(() => {
  const yearSelect = document.querySelector('[data-calendar-year-select]');
  yearSelect?.addEventListener('change', () => {
    yearSelect.form?.submit();
  });

  function openTargetFromHash() {
    const h = window.location.hash;
    if (!h || h.indexOf('#calendar-day-') !== 0) return;
    const id = h.slice(1);
    const el = document.getElementById(id);
    if (el && el.tagName === 'DETAILS') {
      el.open = true;
    }
  }

  openTargetFromHash();
  window.addEventListener('hashchange', openTargetFromHash);

  document.querySelectorAll('a.calendar-cell[href^="#calendar-day-"]').forEach((a) => {
    a.addEventListener('click', () => {
      const id = (a.getAttribute('href') || '').slice(1);
      window.setTimeout(() => {
        const el = document.getElementById(id);
        if (el && el.tagName === 'DETAILS') {
          el.open = true;
        }
      }, 0);
    });
  });
})();
