(() => {
  const body = document.body;

  function renderFilterTags(container, tags) {
    if (!container) return;

    if (!tags.length) {
      container.innerHTML = '<span class="filter-empty-copy">Filtr tanlanmagan.</span>';
      return;
    }

    container.innerHTML = tags.map((tag) => (
      '<button type="button" class="filter-tag" data-filter-clear="' + String(tag.key) + '">'
      + '<span>' + String(tag.label) + '</span>'
      + '<i class="fa-solid fa-xmark" aria-hidden="true"></i>'
      + '</button>'
    )).join('');
  }

  function initAutoSubmitFilters() {
    document.querySelectorAll('[data-auto-submit-filter]').forEach((form) => {
      const qInput = form.querySelector('input[type="search"]');
      const selects = Array.from(form.querySelectorAll('select'));
      const tagsWrap = form.querySelector('[data-active-filter-tags]');
      let debounceTimer = null;

      const buildTags = () => {
        const tags = [];

        if (qInput && qInput.value.trim()) {
          tags.push({ key: 'q', label: 'Qidiruv: ' + qInput.value.trim() });
        }

        selects.forEach((select) => {
          if (!select.value) return;
          const option = select.options[select.selectedIndex];
          tags.push({
            key: select.name || select.id || 'filter',
            label: option ? option.textContent.trim() : select.value,
          });
        });

        renderFilterTags(tagsWrap, tags);
      };

      selects.forEach((select) => {
        select.addEventListener('change', () => {
          buildTags();
          form.submit();
        });
      });

      if (qInput) {
        qInput.addEventListener('input', () => {
          buildTags();
          window.clearTimeout(debounceTimer);
          debounceTimer = window.setTimeout(() => form.submit(), 450);
        });
      }

      tagsWrap?.addEventListener('click', (event) => {
        const chip = event.target.closest('[data-filter-clear]');
        if (!chip) return;

        const key = chip.getAttribute('data-filter-clear');
        if (!key) return;

        const field = form.querySelector('[name="' + key + '"]') || document.getElementById(key);
        if (!field) return;

        field.value = '';
        buildTags();
        form.submit();
      });

      buildTags();
    });
  }

  function initExamFilters() {
    const grid = document.getElementById('exam-grid');
    const qEl = document.getElementById('exam-filter-q');
    const gradeEl = document.getElementById('exam-filter-grade');
    const stateEl = document.getElementById('exam-filter-state');
    const sortEl = document.getElementById('exam-filter-sort');
    const countEl = document.getElementById('exam-filter-count');
    const zeroEl = document.getElementById('exam-filter-zero');
    const tagsWrap = document.getElementById('exam-filter-tags');
    const resetBtn = document.getElementById('exam-filter-reset');

    if (!grid || !qEl || !gradeEl || !stateEl || !sortEl) return;

    const cards = Array.from(grid.querySelectorAll('[data-exam-card]'));
    const total = cards.length;

    function matches(card) {
      const text = (qEl.value || '').trim().toLowerCase();
      if (text) {
        const haystack = card.getAttribute('data-search-text') || '';
        if (!haystack.includes(text)) return false;
      }

      const gradeValue = gradeEl.value;
      if (gradeValue && card.getAttribute('data-unrestricted') !== '1') {
        const numbers = (card.getAttribute('data-grade-nums') || '').split(',').filter(Boolean);
        if (!numbers.includes(gradeValue)) return false;
      }

      const stateValue = stateEl.value;
      if (stateValue && card.getAttribute('data-state') !== stateValue) return false;

      return true;
    }

    function sortMatched(list) {
      const mode = sortEl.value || 'id-desc';

      list.sort((a, b) => {
        if (mode === 'id-desc') return parseInt(b.getAttribute('data-exam-id'), 10) - parseInt(a.getAttribute('data-exam-id'), 10);
        if (mode === 'id-asc') return parseInt(a.getAttribute('data-exam-id'), 10) - parseInt(b.getAttribute('data-exam-id'), 10);

        if (mode === 'title-asc' || mode === 'title-desc') {
          const compare = (a.getAttribute('data-title-sort') || '').localeCompare(b.getAttribute('data-title-sort') || '', undefined, { sensitivity: 'base' });
          return mode === 'title-desc' ? -compare : compare;
        }

        if (mode === 'duration-asc' || mode === 'duration-desc') {
          const aValue = parseInt(a.getAttribute('data-duration'), 10) || 0;
          const bValue = parseInt(b.getAttribute('data-duration'), 10) || 0;
          return mode === 'duration-desc' ? bValue - aValue : aValue - bValue;
        }

        if (mode === 'points-asc' || mode === 'points-desc') {
          const aValue = parseInt(a.getAttribute('data-points'), 10) || 0;
          const bValue = parseInt(b.getAttribute('data-points'), 10) || 0;
          return mode === 'points-desc' ? bValue - aValue : aValue - bValue;
        }

        return 0;
      });
    }

    function buildExamTags() {
      const tags = [];

      if (qEl.value.trim()) {
        tags.push({ key: 'q', label: 'Qidiruv: ' + qEl.value.trim() });
      }

      if (gradeEl.value) {
        tags.push({ key: 'grade', label: gradeEl.options[gradeEl.selectedIndex]?.textContent.trim() || gradeEl.value });
      }

      if (stateEl.value) {
        tags.push({ key: 'state', label: stateEl.options[stateEl.selectedIndex]?.textContent.trim() || stateEl.value });
      }

      if (sortEl.value && sortEl.value !== 'id-desc') {
        tags.push({ key: 'sort', label: sortEl.options[sortEl.selectedIndex]?.textContent.trim() || sortEl.value });
      }

      renderFilterTags(tagsWrap, tags);
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
          ? 'Jami: ' + total + ' ta imtihon'
          : "Ko'rsatilmoqda: " + matched.length + ' / ' + total;
      }

      if (zeroEl) {
        zeroEl.hidden = matched.length > 0;
      }

      grid.style.display = matched.length > 0 ? '' : 'none';
      buildExamTags();
    }

    [qEl, gradeEl, stateEl, sortEl].forEach((el) => {
      el.addEventListener('input', apply);
      el.addEventListener('change', apply);
    });

    tagsWrap?.addEventListener('click', (event) => {
      const chip = event.target.closest('[data-filter-clear]');
      if (!chip) return;

      const key = chip.getAttribute('data-filter-clear');
      if (key === 'q') qEl.value = '';
      if (key === 'grade') gradeEl.value = '';
      if (key === 'state') stateEl.value = '';
      if (key === 'sort') sortEl.value = 'id-desc';
      apply();
    });

    resetBtn?.addEventListener('click', () => {
      qEl.value = '';
      gradeEl.value = '';
      stateEl.value = '';
      sortEl.value = 'id-desc';
      apply();
    });

    apply();
  }

  function initSectionNav() {
    document.querySelectorAll('[data-section-nav]').forEach((nav) => {
      const links = Array.from(nav.querySelectorAll('a[href^="#"]'));
      if (!links.length) return;

      const sections = links
        .map((link) => {
          const id = link.getAttribute('href');
          const section = id ? document.querySelector(id) : null;
          return section ? { link, section } : null;
        })
        .filter(Boolean);

      if (!sections.length) return;

      const activateCurrent = () => {
        const fromTop = window.scrollY + (parseInt(getComputedStyle(document.documentElement).getPropertyValue('--fixed-header-clearance') || '120', 10) || 120) + 40;

        let active = sections[0];
        sections.forEach((item) => {
          if (item.section.offsetTop <= fromTop) {
            active = item;
          }
        });

        links.forEach((link) => link.classList.toggle('is-active', active.link === link));
      };

      nav.addEventListener('click', (event) => {
        const link = event.target.closest('a[href^="#"]');
        if (!link) return;
        const target = document.querySelector(link.getAttribute('href'));
        if (!target) return;
        event.preventDefault();
        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
      });

      window.addEventListener('scroll', activateCurrent, { passive: true });
      activateCurrent();
    });
  }

  function initSearchShortcut() {
    const openBtn = document.querySelector('[data-global-search-open]');
    if (!openBtn) return;

    document.addEventListener('keydown', (event) => {
      const target = event.target;
      const isTypingField = target && (
        target.tagName === 'INPUT'
        || target.tagName === 'TEXTAREA'
        || target.isContentEditable
        || target.tagName === 'SELECT'
      );

      if ((event.ctrlKey || event.metaKey) && String(event.key).toLowerCase() === 'k') {
        event.preventDefault();
        openBtn.click();
        return;
      }

      if (!isTypingField && event.key === '/') {
        event.preventDefault();
        openBtn.click();
      }
    });
  }

  function initThemeAutoReset() {
    document.querySelectorAll('.js-theme-auto-reset').forEach((button) => {
      if (button.dataset.bound === 'true') return;
      button.dataset.bound = 'true';

      button.addEventListener('click', () => {
        localStorage.removeItem('site-theme');
        const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
        const theme = prefersDark ? 'dark' : 'light';

        document.documentElement.setAttribute('data-theme', theme);
        document.body?.setAttribute('data-theme', theme);

        if (window.showToast) {
          window.showToast("Avto rejim faollashtirildi. Sayt endi qurilma rejimiga moslashadi.", 'success');
        }
      });
    });
  }

  function init() {
    initAutoSubmitFilters();
    initExamFilters();
    initSectionNav();
    initSearchShortcut();
    initThemeAutoReset();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init, { once: true });
  } else {
    init();
  }
})();
