(() => {
  const grid = document.getElementById('exam-grid');
  const qEl = document.getElementById('exam-filter-q');
  const gradeEl = document.getElementById('exam-filter-grade');
  const stateEl = document.getElementById('exam-filter-state');
  const sortEl = document.getElementById('exam-filter-sort');
  const countEl = document.getElementById('exam-filter-count');
  const zeroEl = document.getElementById('exam-filter-zero');
  if (!grid || !qEl || !gradeEl || !stateEl || !sortEl) return;

  const cards = Array.from(grid.querySelectorAll('[data-exam-card]'));
  const total = cards.length;

  function matches(card) {
    const term = (qEl.value || '').trim().toLowerCase();
    if (term) {
      const haystack = card.getAttribute('data-search-text') || '';
      if (!haystack.includes(term)) return false;
    }

    const gradeValue = gradeEl.value;
    if (gradeValue && card.getAttribute('data-unrestricted') !== '1') {
      const nums = (card.getAttribute('data-grade-nums') || '').split(',').filter(Boolean);
      if (!nums.includes(gradeValue)) return false;
    }

    const stateValue = stateEl.value;
    return !stateValue || card.getAttribute('data-state') === stateValue;
  }

  function sortMatched(list) {
    const mode = sortEl.value || 'id-desc';
    list.sort((a, b) => {
      if (mode === 'id-desc') return parseInt(b.getAttribute('data-exam-id'), 10) - parseInt(a.getAttribute('data-exam-id'), 10);
      if (mode === 'id-asc') return parseInt(a.getAttribute('data-exam-id'), 10) - parseInt(b.getAttribute('data-exam-id'), 10);

      if (mode === 'title-asc' || mode === 'title-desc') {
        const compare = (a.getAttribute('data-title-sort') || '').localeCompare(
          b.getAttribute('data-title-sort') || '',
          undefined,
          { sensitivity: 'base' }
        );
        return mode === 'title-desc' ? -compare : compare;
      }

      if (mode === 'duration-asc' || mode === 'duration-desc') {
        const delta = (parseInt(a.getAttribute('data-duration'), 10) || 0) - (parseInt(b.getAttribute('data-duration'), 10) || 0);
        return mode === 'duration-desc' ? -delta : delta;
      }

      if (mode === 'points-asc' || mode === 'points-desc') {
        const delta = (parseInt(a.getAttribute('data-points'), 10) || 0) - (parseInt(b.getAttribute('data-points'), 10) || 0);
        return mode === 'points-desc' ? -delta : delta;
      }

      return 0;
    });
  }

  function apply() {
    const matched = cards.filter(matches);
    sortMatched(matched);
    const hidden = cards.filter((card) => !matched.includes(card));

    matched.forEach((card) => {
      grid.appendChild(card);
      card.style.display = '';
    });

    hidden.forEach((card) => {
      grid.appendChild(card);
      card.style.display = 'none';
    });

    if (countEl) {
      countEl.textContent = matched.length === total
        ? `Jami: ${total} ta imtihon`
        : `Ko‘rsatilmoqda: ${matched.length} / ${total}`;
    }

    if (zeroEl) {
      zeroEl.hidden = matched.length > 0;
    }

    grid.style.display = matched.length > 0 ? '' : 'none';
  }

  [qEl, gradeEl, stateEl, sortEl].forEach((el) => {
    el.addEventListener('input', apply);
    el.addEventListener('change', apply);
  });

  apply();
})();
