(() => {
  const form = document.querySelector('.post-filters');
  const results = document.querySelector('[data-post-results]');
  if (!form || !results) return;

  const hiddenInput = form.querySelector('[data-filter-input]');
  const dropdown = form.querySelector('[data-post-filter-dropdown]');
  const items = form.querySelectorAll('[data-filter-value]');
  const searchInput = form.querySelector('input[name="q"]');
  const categorySelect = form.querySelector('select[name="category_id"]');
  const clearBtn = form.querySelector('.search-clear-btn');
  let debounceTimer = null;
  let activeController = null;

  function toggleClearBtn() {
    if (!clearBtn || !searchInput) return;
    clearBtn.style.display = searchInput.value.trim() ? 'flex' : 'none';
  }

  function closeDropdowns() {
    document.querySelectorAll('[data-post-filter-dropdown]').forEach((currentDropdown) => {
      currentDropdown.removeAttribute('open');
    });
  }

  function syncDropdownLabel() {
    if (!hiddenInput || !dropdown) return;

    const activeItem = form.querySelector(`[data-filter-value="${hiddenInput.value}"]`);
    const labelTarget = dropdown.querySelector('.post-filter-dropdown-toggle span');
    if (activeItem && labelTarget) {
      labelTarget.textContent = activeItem.querySelector('span')?.textContent || 'Filter';
    }

    items.forEach((item) => {
      const isActive = item.getAttribute('data-filter-value') === hiddenInput.value;
      item.classList.toggle('active', isActive);

      const hasIcon = item.querySelector('i');
      if (isActive && !hasIcon) {
        const icon = document.createElement('i');
        icon.className = 'fa-solid fa-check';
        item.appendChild(icon);
      }
      if (!isActive && hasIcon) {
        hasIcon.remove();
      }
    });
  }

  async function fetchResults(url) {
    if (activeController) {
      activeController.abort();
    }

    activeController = new AbortController();
    results.classList.add('is-loading');
    results.style.pointerEvents = 'none';

    try {
      const response = await fetch(url, {
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          Accept: 'application/json',
        },
        signal: activeController.signal,
      });

      if (!response.ok) {
        throw new Error(`HTTP ${response.status}`);
      }

      const data = await response.json();
      
      // Smooth fade transition
      results.style.opacity = '0';
      setTimeout(() => {
        results.innerHTML = data.html || '';
        if (typeof window.initPrimeAnimations === 'function') {
          window.initPrimeAnimations();
        }
        results.style.opacity = '1';
        results.classList.remove('is-loading');
        results.style.pointerEvents = 'auto';
      }, 50);

      history.replaceState({}, '', url);
    } catch (error) {
      if (error.name !== 'AbortError') {
        window.location.href = url;
      }
      results.classList.remove('is-loading');
      results.style.pointerEvents = 'auto';
    } finally {
      // Done
    }
  }

  function buildUrl() {
    return `${form.action}?${new URLSearchParams(new FormData(form)).toString()}`;
  }

  function applyFilters() {
    syncDropdownLabel();
    fetchResults(buildUrl());
  }

  function scheduleApply() {
    window.clearTimeout(debounceTimer);
    debounceTimer = window.setTimeout(applyFilters, 300);
  }

  items.forEach((item) => {
    item.addEventListener('click', () => {
      if (hiddenInput) {
        hiddenInput.value = item.getAttribute('data-filter-value') || 'all';
      }
      closeDropdowns();
      applyFilters();
    });
  });

  searchInput?.addEventListener('input', () => {
    toggleClearBtn();
    scheduleApply();
  });
  categorySelect?.addEventListener('change', applyFilters);

  form.addEventListener('submit', (event) => {
    event.preventDefault();
    applyFilters();
  });

  clearBtn?.addEventListener('click', (event) => {
    event.preventDefault();
    if (searchInput) searchInput.value = '';
    toggleClearBtn();
    applyFilters();
  });

  results.addEventListener('click', (event) => {
    const link = event.target.closest('.news-pagination a');
    if (!link) return;

    event.preventDefault();
    fetchResults(link.href);
  });

  document.addEventListener('click', (event) => {
    if (!event.target.closest('[data-post-filter-dropdown]')) {
      closeDropdowns();
    }
  });

  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
      closeDropdowns();
    }
  });

  syncDropdownLabel();
})();
