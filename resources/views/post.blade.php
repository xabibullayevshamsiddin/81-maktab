<x-loyouts.main title="81-IDUM | Yangiliklar">
  @php
    $f = $filter ?? 'all';
    $filterOptions = [
      'all' => 'Barchasi (eng yangi)',
      'video_news' => 'Videolik yangiliklar',
      'social' => 'Ijtimoiy yangiliklar',
      'has_video' => 'Video bor postlar',
      'new' => 'Eng yangilari',
      'popular' => "Eng ko'p ko'rilgan",
      'likes' => "Eng ko'p yoqtirilgan",
      'comments' => "Eng ko'p izoh olgan",
    ];
    $activeFilterLabel = $filterOptions[$f] ?? $filterOptions['all'];
  @endphp

  <style>
    .post-filter-hidden-input {
      display: none;
    }

    .post-filter-dropdown-wrap {
      position: relative;
    }

    .post-filter-dropdown {
      position: relative;
      width: 100%;
    }

    .post-filter-dropdown[open] {
      z-index: 6;
    }

    .post-filter-dropdown-toggle {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 12px;
      width: 100%;
      min-height: 54px;
      padding: 14px 18px;
      border-radius: 16px;
      border: 1px solid rgba(15, 23, 42, 0.08);
      background: rgba(255, 255, 255, 0.88);
      color: #0f172a;
      font-weight: 600;
      cursor: pointer;
      list-style: none;
      box-shadow: 0 12px 24px rgba(15, 23, 42, 0.08);
      backdrop-filter: blur(8px);
    }

    .post-filter-dropdown-toggle::-webkit-details-marker {
      display: none;
    }

    .post-filter-dropdown-toggle i {
      font-size: 12px;
      transition: transform 0.2s ease;
    }

    .post-filter-dropdown[open] .post-filter-dropdown-toggle i {
      transform: rotate(180deg);
    }

    .post-filter-dropdown-menu {
      position: absolute;
      top: calc(100% + 10px);
      left: 0;
      width: 100%;
      display: grid;
      gap: 8px;
      padding: 12px;
      border-radius: 18px;
      background: rgba(255, 255, 255, 0.98);
      border: 1px solid rgba(15, 23, 42, 0.08);
      box-shadow: 0 22px 40px rgba(15, 23, 42, 0.14);
      backdrop-filter: blur(12px);
    }

    .post-filter-dropdown-item {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 12px;
      width: 100%;
      padding: 12px 14px;
      border: none;
      border-radius: 14px;
      background: transparent;
      color: #0f172a;
      font: inherit;
      font-weight: 600;
      text-align: left;
      cursor: pointer;
      transition: background 0.2s ease, transform 0.2s ease, color 0.2s ease;
    }

    .post-filter-dropdown-item:hover,
    .post-filter-dropdown-item.active {
      background: rgba(21, 101, 192, 0.1);
      color: var(--primary-2);
      transform: translateY(-1px);
    }

    .post-filter-dropdown-item i {
      font-size: 13px;
      opacity: 0.9;
    }

    .post-filter-actions {
      flex: 0 0 auto;
      min-width: auto;
      display: flex;
      align-items: center;
    }

    @media (max-width: 768px) {
      .post-filter-dropdown-menu {
        position: static;
        margin-top: 10px;
      }
    }

    :root[data-theme='dark'] .post-filter-dropdown-toggle {
      border-color: rgba(148, 163, 184, 0.18);
      background: rgba(11, 21, 36, 0.94);
      color: #e5eefc;
      box-shadow: 0 12px 28px rgba(2, 8, 23, 0.36);
    }

    :root[data-theme='dark'] .post-filter-dropdown-menu {
      background: rgba(11, 21, 36, 0.98);
      border-color: rgba(148, 163, 184, 0.16);
      box-shadow: 0 24px 44px rgba(2, 8, 23, 0.42);
    }

    :root[data-theme='dark'] .post-filter-dropdown-item {
      color: #dbeafe;
    }

    :root[data-theme='dark'] .post-filter-dropdown-item:hover,
    :root[data-theme='dark'] .post-filter-dropdown-item.active {
      background: rgba(59, 130, 246, 0.16);
      color: #93c5fd;
    }
  </style>

  <section class="news-hero" id="home">
    <div class="container">
      <div class="news-hero-content reveal">
        <h1>81-IDUM Yangiliklari</h1>
        <p>Maktab hayotidagi eng dolzarb voqealar, tanlovlar va tadbirlar bilan tanishing.
Qiziqarli yangiliklar va muhim jarayonlar doimo siz bilan!</p>
      </div>
      <a href="#posts" class="btn" style="margin-top: 20px">
        Ma'lumotlarga o'tish <i class="fa-solid fa-arrow-down" style="margin-left: 10px;"></i>
      </a>
    </div>
  </section>

  <main class="news">
    <section class="container news reveal glass-section" id="posts" style="padding-bottom:30px;">
      <div class="section-head">
        <h2>Yangiliklar</h2>
        <p>Qidirish, kategoriya va tur bo'yicha</p>
      </div>

      <form method="GET" action="{{ route('post') }}" class="post-filters">
        <div class="post-filter">
          <input
            type="text"
            name="q"
            value="{{ $q ?? '' }}"
            placeholder="Qidirish..."
            class="comment-input"
          />
        </div>

        <div class="post-filter">
          <select name="category_id" class="form-control">
            <option value="all" {{ empty($categoryId) || $categoryId === 'all' ? 'selected' : '' }}>
              Barchasi
            </option>
            @foreach($categories as $cat)
              <option value="{{ $cat->id }}" {{ (string) ($categoryId ?? '') === (string) $cat->id ? 'selected' : '' }}>
                {{ $cat->name }}
              </option>
            @endforeach
          </select>
        </div>

        <div class="post-filter post-filter-dropdown-wrap">
          <input type="hidden" name="filter" value="{{ $f }}" class="post-filter-hidden-input" data-filter-input>

          <details class="post-filter-dropdown" data-post-filter-dropdown>
            <summary class="post-filter-dropdown-toggle">
              <span>{{ $activeFilterLabel }}</span>
              <i class="fa-solid fa-chevron-down"></i>
            </summary>

            <div class="post-filter-dropdown-menu">
              @foreach($filterOptions as $value => $label)
                <button
                  type="button"
                  class="post-filter-dropdown-item {{ $f === $value ? 'active' : '' }}"
                  data-filter-value="{{ $value }}"
                >
                  <span>{{ $label }}</span>
                  @if($f === $value)
                    <i class="fa-solid fa-check"></i>
                  @endif
                </button>
              @endforeach
            </div>
          </details>
        </div>

        <div class="post-filter post-filter-actions">
          <a href="{{ route('post') }}" class="btn btn-sm btn-outline">Tozalash</a>
        </div>
      </form>

      <div id="post-results" data-post-results>
        @include('posts.partials.list', [
          'posts' => $posts,
          'likedPostIds' => $likedPostIds,
          'postKindLabels' => $postKindLabels,
        ])
      </div>
    </section>
  </main>

  <script>
    (() => {
      const form = document.querySelector('.post-filters');
      const results = document.querySelector('[data-post-results]');
      if (!form || !results) return;

      const hiddenInput = form.querySelector('[data-filter-input]');
      const dropdown = form.querySelector('[data-post-filter-dropdown]');
      const items = form.querySelectorAll('[data-filter-value]');
      const searchInput = form.querySelector('input[name="q"]');
      const categorySelect = form.querySelector('select[name="category_id"]');
      const resetLink = form.querySelector('a[href="{{ route('post') }}"]');
      let debounceTimer = null;
      let activeController = null;

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
        results.style.opacity = '0.55';

        try {
          const response = await fetch(url, {
            headers: {
              'X-Requested-With': 'XMLHttpRequest',
              'Accept': 'application/json',
            },
            signal: activeController.signal,
          });

          if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
          }

          const data = await response.json();
          results.innerHTML = data.html || '';
          history.replaceState({}, '', url);
        } catch (error) {
          if (error.name !== 'AbortError') {
            window.location.href = url;
          }
        } finally {
          results.style.opacity = '1';
        }
      }

      function buildUrl() {
        const formData = new FormData(form);
        const params = new URLSearchParams(formData);
        return `${form.action}?${params.toString()}`;
      }

      function applyFilters() {
        syncDropdownLabel();
        fetchResults(buildUrl());
      }

      function scheduleApply() {
        window.clearTimeout(debounceTimer);
        debounceTimer = window.setTimeout(() => {
          applyFilters();
        }, 300);
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

      if (searchInput) {
        searchInput.addEventListener('input', scheduleApply);
      }

      if (categorySelect) {
        categorySelect.addEventListener('change', applyFilters);
      }

      form.addEventListener('submit', (event) => {
        event.preventDefault();
        applyFilters();
      });

      if (resetLink) {
        resetLink.addEventListener('click', (event) => {
          event.preventDefault();
          if (searchInput) {
            searchInput.value = '';
          }
          if (categorySelect) {
            categorySelect.value = 'all';
          }
          if (hiddenInput) {
            hiddenInput.value = 'all';
          }
          applyFilters();
        });
      }

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
  </script>
</x-loyouts.main>
