@props(['title' => '81-IDUM'])

<!DOCTYPE html>
<html lang="uz">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>{{ $title }}</title>
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"
      integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw=="
      crossorigin="anonymous"
      referrerpolicy="no-referrer"
    />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap"
      rel="stylesheet"
    />
    <link rel="stylesheet" href="{{ asset('temp/css/style.css') }}?v={{ filemtime(public_path('temp/css/style.css')) }}" />
    <style>
      .header-user-name {
        color: #fff;
        font-weight: 600;
        white-space: nowrap;
      }

      .nav-dropdown {
        position: relative;
      }

      .nav-dropdown-details {
        position: relative;
      }

      .nav-dropdown-toggle {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        cursor: pointer;
        list-style: none;
      }

      .nav-dropdown-toggle::-webkit-details-marker {
        display: none;
      }

      .nav-dropdown-toggle i {
        font-size: 12px;
        transition: transform 0.2s ease;
      }

      .nav-dropdown-details[open] .nav-dropdown-toggle i {
        transform: rotate(180deg);
      }

      .nav-dropdown-menu {
        position: absolute;
        top: calc(100% + 12px);
        right: 0;
        min-width: 220px;
        display: grid;
        gap: 6px;
        padding: 10px;
        border-radius: 16px;
        background: rgba(6, 31, 58, 0.96);
        border: 1px solid rgba(255, 255, 255, 0.18);
        box-shadow: 0 18px 34px rgba(4, 23, 47, 0.26);
      }

      .nav-dropdown-item {
        display: flex;
        align-items: center;
        gap: 10px;
        width: 100%;
        padding: 11px 12px;
        border: none;
        border-radius: 12px;
        background: transparent;
        color: #fff;
        font: inherit;
        text-align: left;
        cursor: pointer;
        transition: background 0.2s ease, transform 0.2s ease;
      }

      .nav-dropdown-item:hover,
      .nav-dropdown-item.active {
        background: rgba(255, 255, 255, 0.12);
        transform: translateY(-1px);
      }

      .nav-dropdown-form {
        margin: 0;
      }

      @media (max-width: 980px) {
        .nav-dropdown {
          width: 100%;
        }

        .nav-dropdown-details {
          width: 100%;
        }

        .nav-dropdown-toggle {
          width: 100%;
          justify-content: space-between;
        }

        .nav-dropdown-menu {
          position: static;
          min-width: 100%;
          margin-top: 10px;
        }
      }
    </style>
  </head>

  <body>
    @php
      $authUser = auth()->user();
      $showAdminToolsDropdown = $authUser && $authUser->isAdmin();
      $canOpenCourse = $authUser && ($authUser->isTeacher() || $authUser->isAdmin());
      $canAccessDashboard = $authUser && ($authUser->isAdmin() || $authUser->isEditor());
      $adminToolsActive = request()->routeIs('teacher.courses.*');
    @endphp
    <header class="page-header">
      <div class="container">
        <div class="header-main" id="navbar" style="margin-top: 20px">
          <a class="logo" href="{{ route('home') }}" aria-label="81-IDUM bosh sahifa">
            <img
              src="{{ asset('temp/img/photo_2026-02-06_11-05-24-2.jpg') }}"
              alt="81-IDUM logotipi"
            />
          </a>

          <button
            class="menu-toggle"
            id="menu-toggle"
            type="button"
            aria-label="Menyuni ochish"
            aria-expanded="false"
          >
            <i class="fa-solid fa-bars"></i>
          </button>

          <nav id="site-nav">
            <ul>
              <li><a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">Bosh sahifa</a></li>
              <li><a class="nav-link {{ request()->routeIs('about') ? 'active' : '' }}" href="{{ route('about') }}">Maktab haqida</a></li>
              <li><a class="nav-link {{ request()->routeIs('courses') ? 'active' : '' }}" href="{{ route('courses') }}">Kurslar</a></li>
              <li><a class="nav-link {{ request()->routeIs('post') ? 'active' : '' }}" href="{{ route('post') }}">Yangiliklar</a></li>
              <li><a class="nav-link {{ request()->routeIs('teacher*') ? 'active' : '' }}" href="{{ route('teacher') }}">Ustozlar</a></li>
              @if($showAdminToolsDropdown)
                <li class="nav-dropdown">
                  <details class="nav-dropdown-details js-header-dropdown">
                    <summary class="nav-link nav-dropdown-toggle {{ $adminToolsActive ? 'active' : '' }}">
                      Boshqaruv
                      <i class="fa-solid fa-chevron-down"></i>
                    </summary>

                    <div class="nav-dropdown-menu">
                      <a class="nav-dropdown-item {{ request()->routeIs('teacher.courses.*') ? 'active' : '' }}" href="{{ route('teacher.courses.create') }}">
                        <i class="fa-solid fa-book-open"></i>
                        Kurs ochish
                      </a>

                      <form class="nav-dropdown-form" action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="nav-dropdown-item">
                          <i class="fa-solid fa-right-from-bracket"></i>
                          Logout
                        </button>
                      </form>
                    </div>
                  </details>
                </li>
              @else
                @if($canOpenCourse)
                  <li><a class="nav-link {{ request()->routeIs('teacher.courses.*') ? 'active' : '' }}" href="{{ route('teacher.courses.create') }}">Kurs ochish</a></li>
                @endif
              @endif
              <li><a class="nav-link {{ request()->routeIs('contact') ? 'active' : '' }}" href="{{ route('contact') }}">Aloqa</a></li>
            </ul>
          </nav>

          <div class="login">
            @auth
                 <p class="header-user-name">{{ $authUser->name }}</p>
            @endauth


            @auth
                @if($canAccessDashboard)
                  <a href="{{ route('dashboard') }}" class="btn btn-outline">dashboard</a>
                @endif

                @unless($showAdminToolsDropdown)
                  <form action="{{ route('logout') }}" method="POST" style="display:inline;">
                      @csrf
                      <button type="submit" class="btn btn-outline">Logout</button>
                  </form>
                @endunless
                 @else
            <a href="{{ route('login') }}" class="btn btn-outline">Kirish</a>
            <a href="{{ route('register') }}" class="btn">Ro'yxatdan o'tish</a>
              @endauth
          </div>
        </div>
      </div>
    </header>

    {{ $slot }}

    <button
      id="scroll-top"
      class="scroll-top"
      type="button"
      aria-label="Yuqoriga"
    >
      <i class="fa-solid fa-chevron-up"></i>
    </button>

    <footer class="footer">
      <div class="footer-container container">
        <div class="footer-com">
          <img
            src="{{ asset('temp/img/photo_2026-02-06_11-05-24-2.jpg') }}"
            alt="Maktab logotipi"
            class="img2"
          />
          <h3>81-maktab</h3>
          <p>
            81-sonli maktab zamonaviy va sifatli ta'lim, kuchli qadriyatlar
            hamda o'quvchi muvaffaqiyati uchun xizmat qiladi.
          </p>
        </div>

        <div class="footer-col">
          <h4>Tezkor havolalar</h4>
          <ul>
            <li><a href="{{ route('home') }}">Bosh sahifa</a></li>
            <li><a href="{{ route('about') }}">Maktab haqida</a></li>
            <li><a href="{{ route('courses') }}">Kurslar</a></li>
            <li><a href="{{ route('post') }}">Yangiliklar</a></li>
            <li><a href="{{ route('teacher') }}">Ustozlar</a></li>
            <li><a href="{{ route('contact') }}">Aloqa</a></li>
          </ul>
        </div>

        <div class="footer-cop">
          <h4>Aloqa</h4>
          <a
            href="https://yandex.uz/maps/org/51913117189/?ll=69.190318%2C41.306955&z=16"
          >
            <i class="fa-solid fa-location-dot"></i> Toshkent, Maktab No. 81
          </a>
          <p>
            <i class="fa-solid fa-phone"></i>
            <a href="tel:+998711234567">+998 71 123 45 67</a>
          </p>
          <p><i class="fa-solid fa-envelope"></i> info@school81.uz</p>
        </div>
      </div>

      <div class="footer-bottom">
        &copy; <span id="year"></span> 81-sonli maktab. Barcha huquqlar
        himoyalangan.
      </div>
    </footer>

    <script src="{{ asset('temp/js/script.js') }}?v={{ filemtime(public_path('temp/js/script.js')) }}"></script>

    <div id="toast-container" class="toast-container" aria-live="polite" aria-atomic="true"></div>

    <script>
      (() => {
        const container = document.getElementById('toast-container');
        if (!container) return;

        const toastTimerMs = 3200;

        function showToast(message, type = 'success') {
          if (!message) return;

          const toast = document.createElement('div');
          toast.className = `toast toast-${type}`;
          toast.textContent = message;
          container.appendChild(toast);

          // Slide-in animation already via CSS; remove after timeout.
          setTimeout(() => {
            toast.classList.add('toast-out');
            setTimeout(() => toast.remove(), 250);
          }, toastTimerMs);
        }

        // Flash messages -> toast.
        const successMsg = @json(session('success'));
        const errorMsg = @json(session('error'));
        const toastType = @json(session('toast_type'));

        function resolveToastType(defaultType) {
          if (!toastType) return defaultType;
          if (toastType === 'warning') return 'warning';
          if (toastType === 'error') return 'error';
          if (toastType === 'success') return 'success';
          return defaultType;
        }

        if (successMsg) showToast(successMsg, resolveToastType('success'));
        if (errorMsg) showToast(errorMsg, 'error');

        @if ($errors->any())
          showToast(@json($errors->first()), 'error');
        @endif

        const headerDropdowns = document.querySelectorAll('.js-header-dropdown');
        if (headerDropdowns.length) {
          document.addEventListener('click', (event) => {
            headerDropdowns.forEach((dropdown) => {
              if (!dropdown.contains(event.target)) {
                dropdown.removeAttribute('open');
              }
            });
          });

          document.addEventListener('keydown', (event) => {
            if (event.key !== 'Escape') return;

            headerDropdowns.forEach((dropdown) => {
              dropdown.removeAttribute('open');
            });
          });
        }

        // AJAX Like forms
        document.addEventListener('submit', async (event) => {
          const form = event.target.closest('form.js-like-form');
          if (!form) return;

          event.preventDefault();

          const btn = form.querySelector('button.like-btn');
          if (btn) btn.disabled = true;

          const action = form.action;

          try {
            const res = await fetch(action, {
              method: 'POST',
              headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
              },
              body: new FormData(form),
            });

            const data = await res.json();
            if (!data || !data.ok) {
              showToast(data?.message || 'Xatolik', data?.toast_type || 'error');
              return;
            }

            // Update UI
            if (btn && data.likes_count != null) {
              const icon = btn.querySelector('i');
              const countEl = btn.querySelector('.like-count');

              btn.classList.toggle('liked', !!data.liked);
              if (icon) {
                icon.classList.toggle('fa-solid', !!data.liked);
                icon.classList.toggle('fa-regular', !data.liked);
              }
              if (countEl) countEl.textContent = String(data.likes_count);
            }

            showToast(data.message || (data.liked ? "Like qo'shildi." : 'Like olib tashlandi.'), data.toast_type || 'success');
          } catch (e) {
            showToast('Like qilishda xatolik', 'error');
          } finally {
            if (btn) btn.disabled = false;
          }
        });

        // AJAX Comment forms (posts/show)
        // Reply toggle (show/hide reply form) - ishlaydigan bo‘lishi uchun bitta event listener yetarli.
        document.addEventListener('click', (event) => {
          const btn = event.target.closest('button.js-comment-reply-toggle');
          if (!btn) return;

          const wrapper = btn.nextElementSibling && btn.nextElementSibling.classList.contains('js-comment-reply-form-wrapper')
            ? btn.nextElementSibling
            : btn.parentElement?.querySelector('.js-comment-reply-form-wrapper');

          if (!wrapper) return;
          wrapper.hidden = !wrapper.hidden;
        });

        document.addEventListener('submit', async (event) => {
          const form = event.target.closest('form.js-comment-form');
          if (!form) return;

          event.preventDefault();

          const cfg = window.__POST_COMMENTS_CONFIG__ || {};
          const updateUrlTemplate = cfg.updateUrlTemplate || null;
          const destroyUrlTemplate = cfg.destroyUrlTemplate || null;
          const storeUrl = cfg.storeUrl || (form.dataset && form.dataset.storeUrl) || null;
          const csrfToken = cfg.csrfToken || null;

          function escapeHtml(value) {
            return String(value ?? '')
              .replace(/&/g, '&amp;')
              .replace(/</g, '&lt;')
              .replace(/>/g, '&gt;')
              .replace(/"/g, '&quot;')
              .replace(/'/g, '&#39;');
          }

          function prependHtml(parentEl, html) {
            if (!parentEl || !html) return;
            const wrapper = document.createElement('div');
            wrapper.innerHTML = String(html).trim();
            const node = wrapper.firstElementChild;
            if (node) {
              node.classList.add('visible');
              parentEl.prepend(node);
            }
          }

          const action = form.action;
          const btn = form.querySelector('button[type="submit"], input[type="submit"]');
          if (btn) btn.disabled = true;

          const methodOverride = (form.querySelector('input[name="_method"]')?.value || '').toLowerCase();
          const parentIdValue = form.querySelector('input[name="parent_id"]')?.value || null;
          const deletingId = form.dataset.commentId || null;

          try {
            const res = await fetch(action, {
              method: 'POST',
              headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
              },
              body: new FormData(form),
            });

            const data = await res.json();
            if (!data || !data.ok) {
              showToast(data?.message || 'Izoh bilan ishlashda xatolik', data?.toast_type || 'error');
              return;
            }

            const toastType = data.toast_type || 'success';
            showToast(data.message || 'OK', toastType);

            // EDIT
            if (methodOverride === 'put' && data.comment?.id) {
              const el = document.querySelector(`article.comment-card[data-comment-id="${data.comment.id}"]`);
              const textEl = el?.querySelector('.comment-body p');
              if (textEl) textEl.textContent = data.comment.body ?? '';

              const details = form.closest('details');
              if (details) details.open = false;
              form.reset();
              return;
            }

            // DELETE
            if (methodOverride === 'delete' && deletingId) {
              const el = document.querySelector(`article.comment-card[data-comment-id="${deletingId}"]`);
              if (el) el.remove();

              const details = form.closest('details');
              if (details) details.open = false;
              return;
            }

            // CREATE / REPLY
            const comment = data.comment || null;
            if (!comment) {
              form.reset();
              return;
            }

            const currentUserId = cfg.currentUserId ?? null;
            const canManageAll = !!cfg.currentUserCanManageAll;
            const canManageThis = canManageAll || (currentUserId != null && comment.user_id != null && String(comment.user_id) === String(currentUserId));

            const isReply = !!parentIdValue; // storeComment reply forms always send parent_id
            const insertParentId = comment.parent_id ?? null;

            const editUrl = updateUrlTemplate ? updateUrlTemplate.replace('__COMMENT_ID__', String(comment.id)) : null;
            const destroyUrl = destroyUrlTemplate ? destroyUrlTemplate.replace('__COMMENT_ID__', String(comment.id)) : null;
            const roleKey = String(comment.role_key || 'guest');
            const roleLabel = String(comment.role_label || 'Mehmon');
            const roleBadgeIconHtml = roleKey === 'super_admin'
              ? '<i class="fa-solid fa-fire-flame-curved" aria-hidden="true"></i>'
              : '';
            const roleBadgeHtml = `<span class="comment-role-badge role-${escapeHtml(roleKey)}">${roleBadgeIconHtml}${escapeHtml(roleLabel)}</span>`;

            const canManageActionsHtml = canManageThis && editUrl && destroyUrl
              ? `
                  <details class="comment-action-box">
                    <summary><i class="fa-solid fa-pen" style="margin-right: 6px;"></i> Tahrirlash</summary>
                    <form
                      class="comment-form comment-form-inline js-comment-form js-comment-edit-form"
                      action="${editUrl}"
                      method="POST"
                      data-comment-id="${comment.id}"
                    >
                      <input type="hidden" name="_token" value="${escapeHtml(csrfToken)}" />
                      <input type="hidden" name="_method" value="PUT" />
                      <input
                        type="text"
                        class="comment-input"
                        name="body"
                        maxlength="500"
                        required
                        value="${escapeHtml(comment.body)}"
                      />
                      <button class="btn btn-sm" type="submit">Saqlash</button>
                    </form>
                  </details>
                  <form
                    class="js-comment-form js-comment-delete-form"
                    action="${destroyUrl}"
                    method="POST"
                    data-comment-id="${comment.id}"
                    onsubmit="return confirm(\"Izohni o'chirmoqchimisiz?\")"
                  >
                    <input type="hidden" name="_token" value="${escapeHtml(csrfToken)}" />
                    <input type="hidden" name="_method" value="DELETE" />
                    <button type="submit" class="btn btn-sm comment-delete-btn">
                      <i class="fa-solid fa-trash" style="margin-right: 8px;"></i> O'chirish
                    </button>
                  </form>
                `
              : '';

            if (isReply && insertParentId) {
              const parentArticle = document.querySelector(`article.comment-card[data-comment-id="${insertParentId}"]`);
              if (!parentArticle) {
                // Fallback: rootga qo'shib qo'yamiz (kam hollarda)
                const rootList = document.querySelector('#post-detail .comments-list') || document.querySelector('.comments-list');
                if (rootList) prependHtml(rootList, buildReplyLi());
                form.reset();
                return;
              }

              let repliesContainer = parentArticle.querySelector('div.comment-list.comment-replies');
              if (!repliesContainer) {
                repliesContainer = document.createElement('div');
                repliesContainer.className = 'comment-list comment-replies';
                parentArticle.appendChild(repliesContainer);
              }

              prependHtml(repliesContainer, buildReplyLi());

              const wrapper = form.closest('.js-comment-reply-form-wrapper');
              if (wrapper) wrapper.hidden = true;
              form.reset();
              return;
            }

            // Top-level comment
            const rootList = document.querySelector('#post-detail .comments-list') || document.querySelector('.comments-list');
            if (rootList) {
              rootList.querySelectorAll('.comment-empty').forEach((el) => el.remove());
              prependHtml(rootList, buildTopLevelLi());
            }

            form.reset();

            return;

            function buildReplyLi() {
              return `
                <article class="comment-card reveal role-${escapeHtml(roleKey)} comment-item-reply" data-comment-id="${escapeHtml(comment.id)}">
                  <div class="comment-avatar role-${escapeHtml(roleKey)} ${(parseInt(comment.id, 10) % 2 === 0) ? 'accent' : ''}">
                    <i class="fa-solid fa-user"></i>
                  </div>
                  <div class="comment-body">
                    <div class="comment-meta">
                      <strong>${escapeHtml(comment.author_name || 'Mehmon')}</strong>
                      ${roleBadgeHtml}
                      <span class="comment-date"><i class="fa-regular fa-clock"></i> ${escapeHtml(comment.created_at || '')}</span>
                    </div>
                    <p>${escapeHtml(comment.body || '')}</p>
                    <div class="comment-actions">
                      <button type="button" class="comment-like" aria-label="Yoqtirish">
                        <i class="fa-regular fa-heart"></i> <span class="like-count">0</span>
                      </button>
                      ${canManageActionsHtml}
                    </div>
                  </div>
                </article>
              `;
            }

            function buildTopLevelLi() {
              const showAuthorField = (cfg.currentUserId == null);
              const authorFieldHtml = showAuthorField
                ? `
                    <input
                      type="text"
                      class="comment-input"
                      name="author_name"
                      placeholder="Ismingiz (ixtiyoriy)"
                      maxlength="80"
                    />
                  `
                : '';

              const replyFormHtml = `
                <button
                  type="button"
                  class="comment-reply js-comment-reply-toggle"
                  aria-label="Javob"
                  data-reply-parent-id="${escapeHtml(comment.id)}"
                >
                  <i class="fa-regular fa-comment"></i>
                  Javob
                </button>
                <div class="js-comment-reply-form-wrapper comment-reply-form-wrapper" hidden>
                  <form class="comment-form comment-form-inline js-comment-form js-comment-reply-form" action="${escapeHtml(form.action)}" method="POST">
                    <input type="hidden" name="parent_id" value="${escapeHtml(comment.id)}" />
                    <input type="hidden" name="_token" value="${escapeHtml(csrfToken)}" />
                    ${authorFieldHtml}
                    <input
                      type="text"
                      class="comment-input"
                      name="body"
                      placeholder="Javobingizni yozing"
                      maxlength="500"
                      required
                    />
                    <button class="btn btn-sm" type="submit">Javob yuborish</button>
                  </form>
                </div>
              `;

              return `
                <article class="comment-card reveal role-${escapeHtml(roleKey)}" data-comment-id="${escapeHtml(comment.id)}">
                  <div class="comment-avatar role-${escapeHtml(roleKey)} ${(parseInt(comment.id, 10) % 2 === 0) ? 'accent' : ''}">
                    <i class="fa-solid fa-user"></i>
                  </div>
                  <div class="comment-body">
                    <div class="comment-meta">
                      <strong>${escapeHtml(comment.author_name || 'Mehmon')}</strong>
                      ${roleBadgeHtml}
                      <span class="comment-date"><i class="fa-regular fa-clock"></i> ${escapeHtml(comment.created_at || '')}</span>
                    </div>
                    <p>${escapeHtml(comment.body || '')}</p>
                    <div class="comment-actions">
                      <button type="button" class="comment-like" aria-label="Yoqtirish">
                        <i class="fa-regular fa-heart"></i> <span class="like-count">0</span>
                      </button>
                      ${replyFormHtml}
                      ${canManageActionsHtml}
                    </div>
                  </div>
                </article>
              `;
            }
          } catch (e) {
            showToast('Izoh yuborishda xatolik', 'error');
          } finally {
            if (btn) btn.disabled = false;
          }
        });
      })();
    </script>
  </body>
</html>
