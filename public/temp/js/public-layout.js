(() => {
  function parseJson(value, fallback) {
    if (!value) return fallback;
    try {
      return JSON.parse(value);
    } catch (error) {
      return fallback;
    }
  }

  const root = document.documentElement;
  const body = document.body;

  function moveGlobalModals() {
    const modalRoot = document.getElementById('global-modal-root');
    if (!modalRoot) return;

    [
      '#image-lightbox',
      '#exam-rule-modal',
      '#exam-finish-confirm-modal',
      '.comment-modal',
      '.course-details-modal',
      'dialog.site-rules-dialog',
    ].forEach((selector) => {
      document.querySelectorAll(selector).forEach((element) => {
        if (!element || element.parentElement === modalRoot) return;
        modalRoot.appendChild(element);
      });
    });
  }

  function initHeaderClearance() {
    const header = document.querySelector('.header-main');
    if (!root || !header) return;

    const syncHeaderClearance = () => {
      const rect = header.getBoundingClientRect();
      const clearance = Math.max(96, Math.ceil(rect.bottom + 14));
      root.style.setProperty('--fixed-header-clearance', `${clearance}px`);
    };

    syncHeaderClearance();
    window.addEventListener('load', syncHeaderClearance, { passive: true });
    window.addEventListener('resize', syncHeaderClearance, { passive: true });

    if (window.ResizeObserver) {
      const observer = new ResizeObserver(syncHeaderClearance);
      observer.observe(header);
    }
  }

  function initLocaleSwitcher() {
    const localeLinks = Array.from(document.querySelectorAll('.locale-switcher-link[data-locale-switch]'));
    if (!localeLinks.length) return;

    let isSwitchingLocale = false;

    async function switchLocale(link) {
      if (!link || isSwitchingLocale || link.classList.contains('active')) return;

      isSwitchingLocale = true;
      localeLinks.forEach((item) => item.classList.toggle('is-loading', item === link));
      document.documentElement.classList.add('locale-switching-out');

      try {
        await new Promise((resolve) => window.setTimeout(resolve, 210));

        const response = await fetch(link.href, {
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            Accept: 'text/html',
          },
          credentials: 'same-origin',
        });

        if (!response.ok) {
          throw new Error('locale_fetch_failed');
        }

        const html = await response.text();
        sessionStorage.setItem('site-locale-transition', JSON.stringify({ at: Date.now() }));
        document.open();
        document.write(html);
        document.close();
      } catch (error) {
        document.documentElement.classList.remove('locale-switching-out');
        localeLinks.forEach((item) => item.classList.remove('is-loading'));
        window.location.href = link.href;
      }
    }

    document.addEventListener('click', (event) => {
      const link = event.target.closest('.locale-switcher-link[data-locale-switch]');
      if (!link) return;
      if (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey || event.button === 1) return;

      event.preventDefault();
      switchLocale(link);
    });
  }

  function initSiteRules() {
    document.addEventListener('click', (event) => {
      const button = event.target.closest('.site-rules-open');
      if (!button) return;

      const id = button.getAttribute('data-dialog');
      const dialog = id ? document.getElementById(id) : null;
      if (dialog && typeof dialog.showModal === 'function' && !dialog.open) {
        dialog.showModal();
      }
    });

    document.addEventListener('click', (event) => {
      if (event.target.closest('.site-rules-close')) {
        const dialog = event.target.closest('dialog');
        if (dialog && typeof dialog.close === 'function') {
          dialog.close();
        }
        return;
      }

      if (event.target.matches('.site-rules-dialog') && typeof event.target.close === 'function') {
        event.target.close();
      }
    });
  }

  function initPhoneInputs() {
    const phoneInputs = document.querySelectorAll('input[name="phone"], input[name="contact_phone"]');
    if (!phoneInputs.length) return;

    const pattern = body?.dataset.phonePattern || '';
    const title = body?.dataset.phoneTitle || '';
    const placeholder = '+998 90 123 45 67';

    const prettifyPhone = (value) => {
      const normalized = String(value || '').replace(/[^\d+]+/g, '');
      const match = normalized.match(/^\+998(\d{2})(\d{3})(\d{2})(\d{2})$/);
      return match ? `+998 ${match[1]} ${match[2]} ${match[3]} ${match[4]}` : value;
    };

    phoneInputs.forEach((input) => {
      input.setAttribute('type', 'tel');
      input.setAttribute('inputmode', 'tel');
      input.setAttribute('autocomplete', 'tel');
      input.setAttribute('maxlength', '17');
      if (pattern) input.setAttribute('pattern', pattern);
      if (title) input.setAttribute('title', title);
      input.setAttribute('placeholder', placeholder);
      input.value = prettifyPhone(input.value);
      input.addEventListener('blur', () => {
        input.value = prettifyPhone(input.value);
      });
    });
  }

  function initImageLightbox() {
    const lightbox = document.getElementById('image-lightbox');
    const lightboxImg = document.getElementById('image-lightbox-img');
    const lightboxCaption = document.getElementById('image-lightbox-caption');
    if (!lightbox || !lightboxImg || !lightboxCaption) return;

    function openLightbox(img) {
      const src = img.getAttribute('data-zoom-src') || img.currentSrc || img.getAttribute('src');
      const alt = (img.getAttribute('alt') || '').trim();
      if (!src) return;

      lightboxImg.setAttribute('src', src);
      lightboxImg.setAttribute('alt', alt);

      if (alt) {
        lightboxCaption.textContent = alt;
        lightboxCaption.hidden = false;
      } else {
        lightboxCaption.textContent = '';
        lightboxCaption.hidden = true;
      }

      lightbox.classList.add('open');
      lightbox.setAttribute('aria-hidden', 'false');
      document.body.classList.add('lightbox-open');
    }

    function closeLightbox() {
      lightbox.classList.remove('open');
      lightbox.setAttribute('aria-hidden', 'true');
      document.body.classList.remove('lightbox-open');
      lightboxImg.removeAttribute('src');
      lightboxImg.setAttribute('alt', '');
      lightboxCaption.textContent = '';
      lightboxCaption.hidden = true;
    }

    document.addEventListener('click', (event) => {
      const img = event.target.closest('.js-image-zoom-trigger');
      if (img) {
        openLightbox(img);
        return;
      }

      if (event.target.closest('.image-lightbox-close')) {
        closeLightbox();
        return;
      }

      if (event.target === lightbox) {
        closeLightbox();
      }
    });

    document.addEventListener('keydown', (event) => {
      const focusedZoomable = document.activeElement?.classList?.contains('js-image-zoom-trigger')
        ? document.activeElement
        : null;

      if (focusedZoomable && (event.key === 'Enter' || event.key === ' ')) {
        event.preventDefault();
        openLightbox(focusedZoomable);
        return;
      }

      if (event.key === 'Escape' && lightbox.classList.contains('open')) {
        closeLightbox();
      }
    });
  }

  function initToastAndTheme() {
    const container = document.getElementById('toast-container');
    if (!container) return;

    const toastTimerMs = 3200;
    const themeToggles = document.querySelectorAll('.js-theme-toggle');
    const storageKey = 'site-theme';
    const successMsg = body?.dataset.siteSuccess || '';
    const errorMsg = body?.dataset.siteError || '';
    const toastType = body?.dataset.siteToastType || '';
    const firstError = body?.dataset.siteFirstError || '';

    function showToast(message, type = 'success') {
      if (!message) return;

      const toast = document.createElement('div');
      toast.className = `toast toast-${type}`;
      toast.textContent = message;
      container.appendChild(toast);

      setTimeout(() => {
        toast.classList.add('toast-out');
        setTimeout(() => toast.remove(), 250);
      }, toastTimerMs);
    }

    async function copyTextToClipboard(text) {
      if (navigator.clipboard?.writeText && window.isSecureContext) {
        await navigator.clipboard.writeText(text);
        return;
      }

      const textarea = document.createElement('textarea');
      textarea.value = text;
      textarea.setAttribute('readonly', '');
      textarea.style.position = 'fixed';
      textarea.style.opacity = '0';
      textarea.style.pointerEvents = 'none';
      document.body.appendChild(textarea);
      textarea.focus();
      textarea.select();

      const copied = document.execCommand('copy');
      textarea.remove();

      if (!copied) {
        throw new Error('copy_failed');
      }
    }

    function applyTheme(theme) {
      document.documentElement.setAttribute('data-theme', theme);
      document.body.setAttribute('data-theme', theme);

      themeToggles.forEach((button) => {
        button.setAttribute('aria-pressed', theme === 'dark' ? 'true' : 'false');
        button.setAttribute('title', theme === 'dark' ? 'Kunduzgi rejim' : 'Tungi rejim');
      });
    }

    function resolveToastType(defaultType) {
      if (!toastType) return defaultType;
      if (toastType === 'warning') return 'warning';
      if (toastType === 'error') return 'error';
      if (toastType === 'success') return 'success';
      return defaultType;
    }

    if (themeToggles.length) {
      const currentTheme = document.documentElement.getAttribute('data-theme') || 'light';
      applyTheme(currentTheme);

      themeToggles.forEach((button) => {
        button.addEventListener('click', () => {
          const nextTheme = document.documentElement.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
          localStorage.setItem(storageKey, nextTheme);
          applyTheme(nextTheme);
        });
      });
    }

    if (successMsg) showToast(successMsg, resolveToastType('success'));
    if (errorMsg) showToast(errorMsg, 'error');
    if (firstError) showToast(firstError, 'error');

    window.showToast = showToast;
    window.copyTextToClipboard = copyTextToClipboard;
  }

  function initHeaderDropdowns() {
    const headerDropdowns = document.querySelectorAll('.js-header-dropdown');
    if (!headerDropdowns.length) return;

    document.addEventListener('click', (event) => {
      headerDropdowns.forEach((dropdown) => {
        if (!dropdown.contains(event.target)) {
          dropdown.removeAttribute('open');
        }
      });
    });

    document.addEventListener('keydown', (event) => {
      if (event.key !== 'Escape') return;
      headerDropdowns.forEach((dropdown) => dropdown.removeAttribute('open'));
    });
  }

  function getCommentConfig(form) {
    const scope = form?.closest('[data-comment-config]') || document.querySelector('[data-comment-config]');
    return parseJson(scope?.dataset.commentConfig, null);
  }

  function initInteractiveActions() {
    document.addEventListener('submit', async (event) => {
      const form = event.target.closest('form.js-like-form');
      if (!form) return;

      event.preventDefault();
      const btn = form.querySelector('button.like-btn');
      if (btn) btn.disabled = true;

      try {
        const response = await fetch(form.action, {
          method: 'POST',
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            Accept: 'application/json',
          },
          body: new FormData(form),
        });

        const data = await response.json();
        if (!data || !data.ok) {
          window.showToast?.(data?.message || 'Xatolik', data?.toast_type || 'error');
          return;
        }

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

        window.showToast?.(data.message || (data.liked ? "Like qo'shildi." : 'Like olib tashlandi.'), data.toast_type || 'success');
      } catch (error) {
        window.showToast?.('Like qilishda xatolik', 'error');
      } finally {
        if (btn) btn.disabled = false;
      }
    });

    document.addEventListener('click', async (event) => {
      const button = event.target.closest('.js-share-trigger');
      if (!button) return;

      event.preventDefault();
      if (button.disabled) return;

      const shareUrl = button.dataset.shareUrl || window.location.href;
      const shareTitle = button.dataset.shareTitle || document.title;
      const shareText = button.dataset.shareText || shareTitle;
      const successMessage = button.dataset.shareSuccess || 'Havola nusxalandi.';

      button.disabled = true;
      try {
        if (navigator.share) {
          try {
            await navigator.share({ title: shareTitle, text: shareText, url: shareUrl });
            window.showToast?.('Ulashish oynasi ochildi.', 'success');
            return;
          } catch (shareError) {
            if (shareError?.name === 'AbortError') return;
          }
        }

        await window.copyTextToClipboard?.(shareUrl);
        button.classList.add('share-btn-copied');
        window.showToast?.(successMessage, 'success');
      } catch (error) {
        window.showToast?.('Havolani ulashishda xatolik yuz berdi.', 'error');
      } finally {
        setTimeout(() => {
          button.disabled = false;
          button.classList.remove('share-btn-copied');
        }, 900);
      }
    });

    document.addEventListener('click', (event) => {
      const button = event.target.closest('button.js-comment-reply-toggle');
      if (!button) return;

      const wrapper = button.nextElementSibling?.classList.contains('js-comment-reply-form-wrapper')
        ? button.nextElementSibling
        : button.parentElement?.querySelector('.js-comment-reply-form-wrapper');

      if (wrapper) {
        wrapper.hidden = !wrapper.hidden;
      }
    });

    document.addEventListener('submit', async (event) => {
      const form = event.target.closest('form.js-comment-form');
      if (!form) return;

      const cfg = getCommentConfig(form);
      if (!cfg) return;

      event.preventDefault();

      const updateUrlTemplate = cfg.updateUrlTemplate || null;
      const destroyUrlTemplate = cfg.destroyUrlTemplate || null;
      const csrfToken = cfg.csrfToken || null;

      function escapeHtml(value) {
        return String(value ?? '')
          .replace(/&/g, '\u0026amp;')
          .replace(/</g, '\u0026lt;')
          .replace(/>/g, '\u0026gt;')
          .replace(/"/g, '\u0026quot;')
          .replace(/'/g, '\u0026#39;');
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

      function getAvatarInitial(value) {
        const text = String(value ?? '').trim();
        return text ? text.charAt(0).toUpperCase() : 'M';
      }

      function buildCommentAvatarHtml(comment) {
        const accentClass = parseInt(comment.id, 10) % 2 === 0 ? 'accent' : '';
        const hasAvatar = !!comment.avatar_url;
        const avatarClass = `comment-avatar ${accentClass}${hasAvatar ? ' comment-avatar--image' : ''}`.trim();

        if (hasAvatar) {
          return `
            <div class="${avatarClass}">
              <img src="${escapeHtml(comment.avatar_url)}" alt="${escapeHtml(comment.author_name || 'Mehmon')}" loading="lazy" />
            </div>
          `;
        }

        return `
          <div class="${avatarClass}">
            <span>${escapeHtml(comment.avatar_initial || getAvatarInitial(comment.author_name || 'M'))}</span>
          </div>
        `;
      }

      const button = form.querySelector('button[type="submit"], input[type="submit"]');
      if (button) button.disabled = true;

      const methodOverride = (form.querySelector('input[name="_method"]')?.value || '').toLowerCase();
      const parentIdValue = form.querySelector('input[name="parent_id"]')?.value || null;
      const deletingId = form.dataset.commentId || null;

      try {
        const response = await fetch(form.action, {
          method: 'POST',
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            Accept: 'application/json',
          },
          body: new FormData(form),
        });

        const data = await response.json();
        if (!data || !data.ok) {
          window.showToast?.(data?.message || 'Izoh bilan ishlashda xatolik', data?.toast_type || 'error');
          return;
        }

        const toastType = data.toast_type || 'success';
        window.showToast?.(data.message || 'OK', toastType);

        if (methodOverride === 'put' && data.comment?.id) {
          const el = document.querySelector(`article.comment-card[data-comment-id="${data.comment.id}"]`);
          const textEl = el?.querySelector('.comment-body p');
          if (textEl) textEl.textContent = data.comment.body ?? '';

          const details = form.closest('details');
          if (details) details.open = false;
          form.reset();
          return;
        }

        if (methodOverride === 'delete' && deletingId) {
          const el = document.querySelector(`article.comment-card[data-comment-id="${deletingId}"]`);
          if (el) el.remove();

          const details = form.closest('details');
          if (details) details.open = false;
          return;
        }

        const comment = data.comment || null;
        if (!comment) {
          form.reset();
          return;
        }

        const currentUserId = cfg.currentUserId ?? null;
        const roleKey = String(comment.role_key || 'guest');
        let canManageThis = false;
        if (currentUserId != null && comment.user_id != null && String(comment.user_id) === String(currentUserId)) {
          canManageThis = true;
        } else if (cfg.currentUserIsAdmin) {
          canManageThis = true;
        } else if (cfg.currentUserIsModerator) {
          canManageThis = !cfg.currentUserIsOnlyModerator || (roleKey !== 'super_admin' && roleKey !== 'admin');
        }

        const isReply = !!parentIdValue;
        const insertParentId = comment.parent_id ?? null;
        const editUrl = updateUrlTemplate ? updateUrlTemplate.replace('__COMMENT_ID__', String(comment.id)) : null;
        const destroyUrl = destroyUrlTemplate ? destroyUrlTemplate.replace('__COMMENT_ID__', String(comment.id)) : null;
        const roleLabel = String(comment.role_label || 'Mehmon');
        const roleBadgeHtml = `<span class="comment-role-badge role-${escapeHtml(roleKey)}">${escapeHtml(roleLabel)}</span>`;
        const isReplyComment = comment.parent_id != null;
        const bodyMaxLength = isReplyComment ? 50 : 100;
        const likeUrlTpl = cfg.commentLikeUrlTemplate || '';
        const likeCountStr = comment.likes_count != null ? String(comment.likes_count) : '0';
        const likeFormHtml = (likeUrlTpl && csrfToken)
          ? `<form action="${escapeHtml(likeUrlTpl.replace('__COMMENT_ID__', String(comment.id)))}" method="POST" class="js-like-form" style="display:inline;"><input type="hidden" name="_token" value="${escapeHtml(csrfToken)}" /><button type="submit" class="like-btn comment-like" aria-label="Yoqtirish"><i class="fa-regular fa-heart"></i> <span class="like-count">${likeCountStr}</span></button></form>`
          : `<span class="comment-like-fallback"><i class="fa-regular fa-heart"></i> <span class="like-count">${likeCountStr}</span></span>`;

        const canManageActionsHtml = canManageThis && editUrl && destroyUrl
          ? `
              <details class="comment-action-box">
                <summary><i class="fa-solid fa-pen" style="margin-right: 6px;"></i> Tahrirlash</summary>
                <form class="comment-form comment-form-inline js-comment-form js-comment-edit-form" action="${editUrl}" method="POST" data-comment-id="${comment.id}">
                  <input type="hidden" name="_token" value="${escapeHtml(csrfToken)}" />
                  <input type="hidden" name="_method" value="PUT" />
                  <input type="text" class="comment-input" name="body" maxlength="${bodyMaxLength}" required value="${escapeHtml(comment.body)}" />
                  <button class="btn btn-sm" type="submit">Saqlash</button>
                </form>
              </details>
              <form class="js-comment-form js-comment-delete-form" action="${destroyUrl}" method="POST" data-comment-id="${comment.id}" onsubmit="return confirm(\"Izohni o'chirmoqchimisiz?\")">
                <input type="hidden" name="_token" value="${escapeHtml(csrfToken)}" />
                <input type="hidden" name="_method" value="DELETE" />
                <button type="submit" class="btn btn-sm comment-delete-btn">
                  <i class="fa-solid fa-trash" style="margin-right: 8px;"></i> O'chirish
                </button>
              </form>
            `
          : '';

        function buildReplyLi() {
          const staffCardCls = roleKey === 'super_admin'
            ? ' comment-card--super-admin'
            : roleKey === 'admin'
              ? ' comment-card--admin'
              : roleKey === 'moderator'
                ? ' comment-card--moderator'
                : '';

          return `
            <article class="comment-card reveal comment-item-reply${staffCardCls}" data-comment-id="${escapeHtml(comment.id)}">
              ${buildCommentAvatarHtml(comment)}
              <div class="comment-body">
                <div class="comment-meta">
                  <strong>${escapeHtml(comment.author_name || 'Mehmon')}</strong>
                  ${roleBadgeHtml}
                  <span class="comment-date"><i class="fa-regular fa-clock"></i> ${escapeHtml(comment.created_at || '')}</span>
                </div>
                <p>${escapeHtml(comment.body || '')}</p>
                <div class="comment-actions">
                  ${likeFormHtml}
                  ${canManageActionsHtml}
                </div>
              </div>
            </article>
          `;
        }

        function buildTopLevelLi() {
          const showAuthorField = cfg.currentUserId == null;
          const authorFieldHtml = showAuthorField
            ? `
                <input type="text" class="comment-input" name="author_name" placeholder="Ismingiz (ixtiyoriy)" maxlength="80" />
              `
            : '';

          const replyFormHtml = `
            <button type="button" class="comment-reply js-comment-reply-toggle" aria-label="Javob" data-reply-parent-id="${escapeHtml(comment.id)}">
              <i class="fa-regular fa-comment"></i>
              Javob
            </button>
            <div class="js-comment-reply-form-wrapper comment-reply-form-wrapper" hidden>
              <form class="comment-form comment-form-inline js-comment-form js-comment-reply-form" action="${escapeHtml(form.action)}" method="POST">
                <input type="hidden" name="parent_id" value="${escapeHtml(comment.id)}" />
                <input type="hidden" name="_token" value="${escapeHtml(csrfToken)}" />
                ${authorFieldHtml}
                <input type="text" class="comment-input" name="body" placeholder="Javobingizni yozing" maxlength="50" required />
                <button class="btn btn-sm" type="submit">Javob yuborish</button>
              </form>
            </div>
          `;

          const staffCardCls = roleKey === 'super_admin'
            ? ' comment-card--super-admin'
            : roleKey === 'admin'
              ? ' comment-card--admin'
              : roleKey === 'moderator'
                ? ' comment-card--moderator'
                : '';

          return `
            <article class="comment-card reveal${staffCardCls}" data-comment-id="${escapeHtml(comment.id)}">
              ${buildCommentAvatarHtml(comment)}
              <div class="comment-body">
                <div class="comment-meta">
                  <strong>${escapeHtml(comment.author_name || 'Mehmon')}</strong>
                  ${roleBadgeHtml}
                  <span class="comment-date"><i class="fa-regular fa-clock"></i> ${escapeHtml(comment.created_at || '')}</span>
                </div>
                <p>${escapeHtml(comment.body || '')}</p>
                <div class="comment-actions">
                  ${likeFormHtml}
                  ${replyFormHtml}
                  ${canManageActionsHtml}
                </div>
              </div>
            </article>
          `;
        }

        if (isReply && insertParentId) {
          const parentArticle = document.querySelector(`article.comment-card[data-comment-id="${insertParentId}"]`);
          if (!parentArticle) {
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

          const replyWrapper = form.closest('.js-comment-reply-form-wrapper');
          if (replyWrapper) replyWrapper.hidden = true;
          form.reset();
          return;
        }

        const rootList = document.querySelector('#post-detail .comments-list') || document.querySelector('.comments-list');
        if (rootList) {
          rootList.querySelectorAll('.comment-empty').forEach((el) => el.remove());
          prependHtml(rootList, buildTopLevelLi());
        }

        form.reset();
      } catch (error) {
        window.showToast?.('Izoh yuborishda xatolik', 'error');
      } finally {
        if (button) button.disabled = false;
      }
    });
  }

  function initProMaxAnimations() {
    // 1. Scroll progress bar
    let scrollBar = document.getElementById('scroll-bar');
    if (!scrollBar) {
      scrollBar = document.createElement('div');
      scrollBar.id = 'scroll-bar';
      scrollBar.className = 'scroll-progress-bar';
      document.body.appendChild(scrollBar);
    }
    
    window.addEventListener('scroll', () => {
      const winScroll = document.body.scrollTop || document.documentElement.scrollTop;
      const height = document.documentElement.scrollHeight - document.documentElement.clientHeight;
      const scrolled = height > 0 ? (winScroll / height) * 100 : 0;
      scrollBar.style.width = scrolled + '%';
    }, { passive: true });

    // 2. Mouse Glow (olib tashlandi)

    // 3. CounterUp Animation
    const numElements = document.querySelectorAll('[data-count]');
    if (numElements.length > 0) {
      const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
          if (entry.isIntersecting) {
            const rawVal = entry.target.dataset.count;
            if (!entry.target.classList.contains('counted') && rawVal) {
              entry.target.classList.add('counted');
              let start = 0;
              const end = parseInt(rawVal.replace(/[, ]/g, ''), 10);
              if(isNaN(end)) return;
              
              const duration = 2000;
              let startTime = null;
              const suffix = entry.target.dataset.suffix || '';
              
              const step = (timestamp) => {
                if (!startTime) startTime = timestamp;
                const progress = Math.min((timestamp - startTime) / duration, 1);
                // easeOutExpo function for smooth slowing down at end
                const easeProgress = progress === 1 ? 1 : 1 - Math.pow(2, -10 * progress);
                const current = Math.floor(easeProgress * end);
                entry.target.innerText = current.toString().replace(/\B(?=(\d{3})+(?!\d))/g, " ") + suffix;
                
                if (progress < 1) {
                  window.requestAnimationFrame(step);
                } else {
                  entry.target.innerText = end.toString().replace(/\B(?=(\d{3})+(?!\d))/g, " ") + suffix;
                }
              };
              window.requestAnimationFrame(step);
            }
          }
        });
      }, { threshold: 0.5 });
      
      numElements.forEach(el => observer.observe(el));
    }

    // 4. Advanced 3D Tilt Effect on cards
    if (!window.matchMedia("(hover: none)").matches) {
      const tiltElements = document.querySelectorAll('.about-card, .news-card, .course-card');
      tiltElements.forEach(el => {
        el.addEventListener('mousemove', (e) => {
          const rect = el.getBoundingClientRect();
          const x = e.clientX - rect.left;
          const y = e.clientY - rect.top;
          
          const centerX = rect.width / 2;
          const centerY = rect.height / 2;
          
          const rotateX = ((y - centerY) / centerY) * -4;
          const rotateY = ((x - centerX) / centerX) * 4;
          
          el.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) scale3d(1.02, 1.02, 1.02)`;
          el.style.transition = 'none';
        });
        el.addEventListener('mouseleave', () => {
          el.style.transform = `perspective(1000px) rotateX(0deg) rotateY(0deg) scale3d(1, 1, 1)`;
          el.style.transition = 'all 0.5s cubic-bezier(0.25, 0.8, 0.25, 1)';
        });
      });
    }

    // 5. Stagger items helper
    const containers = document.querySelectorAll('.news-container, .courses-grid, .about-grid');
    containers.forEach(container => {
      Array.from(container.children).forEach((child, idx) => {
        child.style.setProperty('--stagger-index', idx);
        child.classList.add('stagger-item');
      });
      
      const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
          if(entry.isIntersecting) {
            Array.from(entry.target.children).forEach(child => child.classList.add('visible'));
            observer.unobserve(entry.target);
          }
        });
      }, { threshold: 0.1 });
      observer.observe(container);
    });
  }

  moveGlobalModals();
  initHeaderClearance();
  initLocaleSwitcher();
  initSiteRules();
  initPhoneInputs();
  initImageLightbox();
  initToastAndTheme();
  initHeaderDropdowns();
  initInteractiveActions();
  initProMaxAnimations();
})();
