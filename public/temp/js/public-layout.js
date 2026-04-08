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

  function initShellUi() {
    const navbar = document.getElementById('navbar');
    const scrollTopBtn = document.getElementById('scroll-top');
    const year = document.getElementById('year');
    const navLinks = document.querySelectorAll('.nav-link');

    if (year) {
      year.textContent = String(new Date().getFullYear());
    }

    const getScrollThreshold = () => {
      if (navbar) {
        const rect = navbar.getBoundingClientRect();
        return Math.max(80, (navbar.offsetHeight || 80) + 40);
      }
      return 120;
    };

    const onScroll = () => {
      const scrollY = window.scrollY;

      if (navbar) {
        navbar.classList.toggle('scrolled', scrollY > 30);
      }

      if (scrollTopBtn) {
        scrollTopBtn.classList.toggle('show', scrollY > getScrollThreshold());
      }

      const fromTop = scrollY + 120;
      navLinks.forEach((link) => {
        const href = link.getAttribute('href');
        if (!href || !href.startsWith('#')) return;

        const section = document.querySelector(href);
        if (!section) return;

        const isActive =
          section.offsetTop <= fromTop &&
          section.offsetTop + section.offsetHeight > fromTop;

        link.classList.toggle('active', isActive);
      });
    };

    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();

    if (scrollTopBtn) {
      scrollTopBtn.addEventListener('click', () => {
        window.scrollTo({ top: 0, behavior: 'smooth' });
      });
    }
  }

  function initRevealAnimations() {
    const reveals = document.querySelectorAll('.reveal');
    if (!reveals.length) return;

    if ('IntersectionObserver' in window) {
      const observer = new IntersectionObserver(
        (entries) => {
          entries.forEach((entry) => {
            if (entry.isIntersecting) {
              entry.target.classList.add('visible');
              observer.unobserve(entry.target);
            }
          });
        },
        { threshold: 0.18 }
      );

      reveals.forEach((item) => observer.observe(item));
      return;
    }

    reveals.forEach((item) => item.classList.add('visible'));
  }

  function initPasswordToggles() {
    document.addEventListener('click', (event) => {
      const button = event.target.closest('.pw-toggle[data-target]');
      if (!button) return;

      event.preventDefault();
      const targetId = button.getAttribute('data-target');
      const input = targetId ? document.getElementById(targetId) : null;
      if (!input) return;

      const isHidden = input.type === 'password';
      input.type = isHidden ? 'text' : 'password';

      const icon = button.querySelector('i');
      if (icon) {
        icon.classList.toggle('fa-eye', !isHidden);
        icon.classList.toggle('fa-eye-slash', isHidden);
      }
    });
  }

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

    function positionSliders() {
      document.querySelectorAll('.locale-switcher').forEach(function (switcher) {
        var slider = switcher.querySelector('.locale-switcher-slider');
        var activeLink = switcher.querySelector('.locale-switcher-link.active');
        if (!slider || !activeLink) return;
        var parentRect = switcher.getBoundingClientRect();
        var linkRect = activeLink.getBoundingClientRect();
        slider.style.left = (linkRect.left - parentRect.left) + 'px';
        slider.style.width = linkRect.width + 'px';
      });
    }

    positionSliders();
    window.addEventListener('resize', positionSliders, { passive: true });

    let isSwitchingLocale = false;

    function triggerRipple(link) {
      const existing = link.querySelector('.locale-ripple');
      if (existing) existing.remove();
      const ripple = document.createElement('span');
      ripple.className = 'locale-ripple';
      Object.assign(ripple.style, {
        position: 'absolute',
        inset: '0',
        borderRadius: 'inherit',
        background: 'radial-gradient(circle at center, rgba(255,255,255,0.35), transparent 70%)',
        transform: 'scale(0)',
        opacity: '1',
        pointerEvents: 'none',
        animation: 'localeRippleOut 0.5s cubic-bezier(0.22,1,0.36,1) forwards',
      });
      link.style.position = 'relative';
      link.style.overflow = 'hidden';
      link.appendChild(ripple);
      ripple.addEventListener('animationend', () => ripple.remove(), { once: true });
    }

    if (!document.getElementById('locale-ripple-style')) {
      const rs = document.createElement('style');
      rs.id = 'locale-ripple-style';
      rs.textContent = `
        @keyframes localeRippleOut {
          0%   { transform: scale(0); opacity: 1; }
          100% { transform: scale(2.5); opacity: 0; }
        }
      `;
      document.head.appendChild(rs);
    }

    async function switchLocale(link) {
      if (!link || isSwitchingLocale || link.classList.contains('active')) return;

      isSwitchingLocale = true;
      triggerRipple(link);
      localeLinks.forEach((item) => item.classList.toggle('is-loading', item === link));

      await new Promise((resolve) => window.setTimeout(resolve, 100));
      document.documentElement.classList.add('locale-switching-out');

      await new Promise((resolve) => window.setTimeout(resolve, 350));
      sessionStorage.setItem('site-locale-transition', JSON.stringify({ at: Date.now() }));
      window.location.href = link.href;
    }

    document.addEventListener('click', (event) => {
      const link = event.target.closest('.locale-switcher-link[data-locale-switch]');
      if (!link) return;
      if (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey || event.button === 1) return;

      event.preventDefault();
      switchLocale(link);
    });
  }

  function initMobileMenu() {
    const menuToggle = document.getElementById('menu-toggle');
    const siteNav = document.getElementById('site-nav');
    if (!menuToggle || !siteNav) return;

    const closeMenu = () => {
      siteNav.classList.remove('open');
      document.documentElement.classList.remove('mobile-menu-open');
      document.body.classList.remove('mobile-menu-open');
      menuToggle.setAttribute('aria-expanded', 'false');
    };

    menuToggle.addEventListener(
      'click',
      (event) => {
        event.preventDefault();
        event.stopImmediatePropagation();

        const isOpen = siteNav.classList.toggle('open');
        document.documentElement.classList.toggle('mobile-menu-open', isOpen);
        document.body.classList.toggle('mobile-menu-open', isOpen);
        menuToggle.setAttribute('aria-expanded', String(isOpen));
      },
      true
    );

    siteNav.querySelectorAll('.nav-link').forEach((link) => {
      link.addEventListener('click', closeMenu, true);
    });

    document.addEventListener('click', (event) => {
      if (!siteNav.classList.contains('open')) return;
      if (siteNav.contains(event.target) || menuToggle.contains(event.target)) return;
      closeMenu();
    });

    document.addEventListener('keydown', (event) => {
      if (event.key === 'Escape') {
        closeMenu();
      }
    });

    window.addEventListener('resize', () => {
      if (window.innerWidth > 980) {
        closeMenu();
      }
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

      var iconMap = {
        success: 'fa-solid fa-circle-check',
        error: 'fa-solid fa-circle-exclamation',
        warning: 'fa-solid fa-triangle-exclamation',
      };
      var titleMap = {
        success: 'Muvaffaqiyatli',
        error: 'Xatolik',
        warning: 'Ogohlantirish',
      };

      var toast = document.createElement('div');
      toast.className = 'toast toast-' + type;
      toast.style.setProperty('--toast-duration', toastTimerMs + 'ms');
      toast.innerHTML =
        '<div class="toast-body">' +
          '<div class="toast-icon"><i class="' + (iconMap[type] || iconMap.success) + '"></i></div>' +
          '<div class="toast-content">' +
            '<p class="toast-title">' + (titleMap[type] || titleMap.success) + '</p>' +
            '<p class="toast-msg">' + message + '</p>' +
          '</div>' +
        '</div>' +
        '<button type="button" class="toast-close" aria-label="Yopish"><i class="fa-solid fa-xmark"></i></button>' +
        '<div class="toast-progress"><div class="toast-progress-bar"></div></div>';

      container.appendChild(toast);

      var dismissToast = function () {
        if (toast.classList.contains('toast-out')) return;
        toast.classList.add('toast-out');
        setTimeout(function () { toast.remove(); }, 380);
      };

      toast.querySelector('.toast-close').addEventListener('click', function (e) {
        e.stopPropagation();
        dismissToast();
      });
      toast.addEventListener('click', dismissToast);

      setTimeout(dismissToast, toastTimerMs);
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

    // 2. CounterUp Animation
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

    // 3. Advanced 3D Tilt Effect on cards
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

    // 4. Stagger items helper
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

  // ============================
  // 🌙☀️ THEME BURST ANIMATION
  // ============================
  function initThemeBurstEffect() {
    const prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (prefersReduced) return;

    const canvas = document.createElement('canvas');
    canvas.id = 'theme-burst-canvas';
    Object.assign(canvas.style, {
      position: 'fixed', top: '0', left: '0',
      width: '100%', height: '100%',
      pointerEvents: 'none', zIndex: '9998',
      opacity: '0', transition: 'opacity 0.15s ease',
    });
    document.body.appendChild(canvas);
    const ctx = canvas.getContext('2d');

    function resizeCanvas() {
      canvas.width = window.innerWidth;
      canvas.height = window.innerHeight;
    }
    resizeCanvas();
    window.addEventListener('resize', resizeCanvas, { passive: true });

    function burstEffect(originX, originY, goingDark) {
      canvas.style.opacity = '1';
      const particleColors = goingDark
        ? ['#7fc8ff', '#a0c4ff', '#dde8ff', '#ffffff', '#c8d8ff']
        : ['#ffd700', '#ffb700', '#fff7a0', '#ffffff', '#ffa500'];

      const particles = [];
      const starDots = [];
      let frame = 0;
      const totalFrames = 80;

      // Particles burst from origin
      for (let i = 0; i < 55; i++) {
        const angle = (Math.PI * 2 * i) / 55 + (Math.random() - 0.5) * 0.4;
        const speed = 2 + Math.random() * 9;
        particles.push({
          x: originX, y: originY,
          vx: Math.cos(angle) * speed,
          vy: Math.sin(angle) * speed,
          size: 1.5 + Math.random() * 3.5,
          color: particleColors[Math.floor(Math.random() * particleColors.length)],
          life: 1,
          decay: 0.015 + Math.random() * 0.015,
        });
      }

      // Star dots for dark mode
      if (goingDark) {
        for (let i = 0; i < 35; i++) {
          const angle = Math.random() * Math.PI * 2;
          const dist = 60 + Math.random() * Math.max(canvas.width, canvas.height) * 0.7;
          starDots.push({
            x: originX + Math.cos(angle) * dist,
            y: originY + Math.sin(angle) * dist,
            size: 0.5 + Math.random() * 1.8,
            delay: Math.floor(Math.random() * 30),
          });
        }
      }

      // Sun rays for light mode
      const rays = [];
      if (!goingDark) {
        for (let i = 0; i < 14; i++) {
          rays.push({
            angle: (Math.PI * 2 * i) / 14,
            len: 0,
            maxLen: 80 + Math.random() * 120,
          });
        }
      }

      function drawFrame() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        const t = frame / totalFrames;
        const eased = 1 - Math.pow(1 - t, 3);

        // --- Radial spread backdrop ---
        const radius = eased * Math.hypot(canvas.width, canvas.height) * 1.2;
        const grad = ctx.createRadialGradient(originX, originY, 0, originX, originY, radius);
        if (goingDark) {
          grad.addColorStop(0, `rgba(5, 15, 45, ${0.55 * (1 - t)})`);
          grad.addColorStop(0.5, `rgba(10, 25, 70, ${0.3 * (1 - t)})`);
          grad.addColorStop(1, 'rgba(0,0,0,0)');
        } else {
          grad.addColorStop(0, `rgba(255, 240, 120, ${0.65 * (1 - t)})`);
          grad.addColorStop(0.45, `rgba(255, 180, 40, ${0.28 * (1 - t)})`);
          grad.addColorStop(1, 'rgba(0,0,0,0)');
        }
        ctx.fillStyle = grad;
        ctx.fillRect(0, 0, canvas.width, canvas.height);

        // --- Celestial body ---
        const bodyAlpha = Math.max(0, 1 - t * 2.2);
        const bodyR = 18 + t * 14;
        ctx.save();
        ctx.globalAlpha = bodyAlpha;

        if (goingDark) {
          // Moon glow halo
          const halo = ctx.createRadialGradient(originX, originY, 0, originX, originY, bodyR * 3);
          halo.addColorStop(0, 'rgba(200,225,255,0.9)');
          halo.addColorStop(0.5, 'rgba(100,170,255,0.35)');
          halo.addColorStop(1, 'rgba(0,0,0,0)');
          ctx.fillStyle = halo;
          ctx.beginPath(); ctx.arc(originX, originY, bodyR * 3, 0, Math.PI * 2); ctx.fill();

          // Moon disc
          ctx.fillStyle = '#d0e8ff';
          ctx.beginPath(); ctx.arc(originX, originY, bodyR, 0, Math.PI * 2); ctx.fill();

          // Crescent shadow bite
          const biteTheme = document.documentElement.getAttribute('data-theme') === 'dark'
            ? '#0f1b2d' : '#e8f0fe';
          ctx.fillStyle = biteTheme;
          ctx.beginPath();
          ctx.arc(originX + bodyR * 0.32, originY - bodyR * 0.08, bodyR * 0.78, 0, Math.PI * 2);
          ctx.fill();

          // Stars spawning around moon
          if (frame > 15) {
            starDots.forEach(s => {
              if (frame < s.delay) return;
              const starT = Math.min(1, (frame - s.delay) / 25);
              ctx.globalAlpha = bodyAlpha * starT;
              ctx.fillStyle = '#ffffff';
              ctx.beginPath(); ctx.arc(s.x, s.y, s.size, 0, Math.PI * 2); ctx.fill();
            });
          }
        } else {
          // Sun outer corona
          const corona = ctx.createRadialGradient(originX, originY, bodyR * 0.5, originX, originY, bodyR * 3.5);
          corona.addColorStop(0, 'rgba(255,255,220,0.95)');
          corona.addColorStop(0.35, 'rgba(255,210,60,0.5)');
          corona.addColorStop(0.7, 'rgba(255,150,0,0.18)');
          corona.addColorStop(1, 'rgba(0,0,0,0)');
          ctx.fillStyle = corona;
          ctx.beginPath(); ctx.arc(originX, originY, bodyR * 3.5, 0, Math.PI * 2); ctx.fill();

          // Sun disc
          ctx.fillStyle = '#fffbe0';
          ctx.beginPath(); ctx.arc(originX, originY, bodyR, 0, Math.PI * 2); ctx.fill();

          // Rays
          rays.forEach(ray => {
            ray.len = Math.min(ray.maxLen, ray.len + 10);
            const rAlpha = 0.55 * bodyAlpha;
            ctx.strokeStyle = `rgba(255,220,80,${rAlpha})`;
            ctx.lineWidth = 2.5;
            ctx.lineCap = 'round';
            ctx.beginPath();
            ctx.moveTo(
              originX + Math.cos(ray.angle) * (bodyR + 4),
              originY + Math.sin(ray.angle) * (bodyR + 4)
            );
            ctx.lineTo(
              originX + Math.cos(ray.angle) * (bodyR + 4 + ray.len),
              originY + Math.sin(ray.angle) * (bodyR + 4 + ray.len)
            );
            ctx.stroke();
          });
        }
        ctx.restore();

        // --- Particles ---
        particles.forEach(p => {
          p.x += p.vx; p.y += p.vy;
          p.vx *= 0.94; p.vy *= 0.94;
          p.vy += 0.08; // soft gravity
          p.life -= p.decay;
          if (p.life <= 0) return;
          ctx.globalAlpha = p.life * bodyAlpha * 1.4;
          ctx.fillStyle = p.color;
          ctx.beginPath();
          ctx.arc(p.x, p.y, p.size * p.life, 0, Math.PI * 2);
          ctx.fill();
          ctx.globalAlpha = 1;
        });

        frame++;
        if (frame < totalFrames) {
          requestAnimationFrame(drawFrame);
        } else {
          canvas.style.opacity = '0';
          setTimeout(() => ctx.clearRect(0, 0, canvas.width, canvas.height), 200);
        }
      }

      requestAnimationFrame(drawFrame);
    }

    // Intercept theme toggle clicks BEFORE the theme changes
    document.addEventListener('click', (e) => {
      const btn = e.target.closest('.js-theme-toggle');
      if (!btn) return;
      const rect = btn.getBoundingClientRect();
      const cx = rect.left + rect.width / 2;
      const cy = rect.top + rect.height / 2;
      const nextDark = document.documentElement.getAttribute('data-theme') !== 'dark';
      // slight delay so the theme has applied first
      setTimeout(() => burstEffect(cx, cy, nextDark), 60);
    }, true);
  }

  // ============================
  // 🌐 LOCALE PAGE REVEAL (smooth slide-in after switch)
  // ============================
  function initLocalePageReveal() {
    let transitionData = null;
    try {
      const raw = sessionStorage.getItem('site-locale-transition');
      transitionData = raw ? JSON.parse(raw) : null;
    } catch (_) {}

    if (!transitionData) return;
    sessionStorage.removeItem('site-locale-transition');

    const elapsed = Date.now() - (transitionData.at || 0);
    if (elapsed > 4000) return;

    const prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (prefersReduced) return;

    if (!document.getElementById('locale-reveal-style')) {
      const style = document.createElement('style');
      style.id = 'locale-reveal-style';
      style.textContent = `
        @keyframes localeSlideIn {
          0%   { opacity: 0; transform: translateY(22px) scale(0.988); filter: blur(8px); }
          35%  { opacity: 0.7; filter: blur(2px); }
          100% { opacity: 1; transform: none; filter: none; }
        }
        .locale-page-entering {
          animation: localeSlideIn 0.58s cubic-bezier(0.22, 1, 0.36, 1) both;
        }

        @keyframes localeStaggerIn {
          0%   { opacity: 0; transform: translateY(18px); filter: blur(6px); }
          100% { opacity: 1; transform: none; filter: none; }
        }
        .locale-stagger-item {
          animation: localeStaggerIn 0.48s cubic-bezier(0.22, 1, 0.36, 1) both;
        }

        @keyframes localeBtnPop {
          0%   { transform: scale(1); box-shadow: 0 0 0 0 rgba(255,255,255,0.4); }
          40%  { transform: scale(1.18); box-shadow: 0 0 0 6px rgba(255,255,255,0.15); }
          70%  { transform: scale(0.95); }
          100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(255,255,255,0); }
        }
        .locale-switcher-link.is-active-switch {
          background: rgba(255,255,255,0.28) !important;
          animation: localeBtnPop 0.65s cubic-bezier(0.34,1.56,0.64,1) both;
        }
        [data-theme='dark'] .locale-switcher-link.is-active-switch {
          background: rgba(96,165,250,0.25) !important;
          box-shadow: 0 0 14px rgba(96,165,250,0.35);
        }
      `;
      document.head.appendChild(style);
    }

    const shell = document.querySelector('.site-shell');
    if (shell) {
      shell.classList.add('locale-page-entering');
      shell.addEventListener('animationend', () => {
        shell.classList.remove('locale-page-entering');
      }, { once: true });

      const staggerTargets = shell.querySelectorAll(
        '.page-header, main > *:not(script):not(style), .page-footer, .exam-hero, .exam-grid, .exam-filter-panel, .news, .hero, .section, article, .card-style, .post-card, .teacher-card, .course-card'
      );

      const seen = new Set();
      const uniqueTargets = [];
      staggerTargets.forEach((el) => {
        if (!seen.has(el)) {
          seen.add(el);
          uniqueTargets.push(el);
        }
      });

      uniqueTargets.slice(0, 12).forEach((el, i) => {
        el.style.animationDelay = (i * 60 + 80) + 'ms';
        el.classList.add('locale-stagger-item');
        el.addEventListener('animationend', () => {
          el.classList.remove('locale-stagger-item');
          el.style.animationDelay = '';
        }, { once: true });
      });
    }

    const allLocaleLinks = document.querySelectorAll('.locale-switcher-link.active');
    allLocaleLinks.forEach((link) => {
      link.classList.add('is-active-switch');
      setTimeout(() => link.classList.remove('is-active-switch'), 900);
    });
  }



  function initGlobalChat() {
    var widget = document.getElementById('chat-widget');
    if (!widget) return;

    var bubble = document.getElementById('chat-bubble');
    var panel = document.getElementById('chat-panel');
    var closeBtn = document.getElementById('chat-close-btn');
    var fullBtn = document.getElementById('chat-fullscreen-btn');
    var messagesEl = document.getElementById('chat-messages');
    var form = document.getElementById('chat-form');
    var input = document.getElementById('chat-input');
    var badge = document.getElementById('chat-badge');

    var messagesUrl = widget.getAttribute('data-chat-messages-url');
    var sendUrl = widget.getAttribute('data-chat-send-url');
    var csrf = widget.getAttribute('data-csrf');
    var lastId = 0;
    var isOpen = false;
    var pollTimer = null;

    function openPanel() {
      panel.hidden = false;
      panel.classList.remove('is-closing');
      isOpen = true;
      if (badge) badge.hidden = true;
      loadMessages();
      scrollDown();
      input.focus();
      startPolling();
    }

    function closePanel() {
      panel.classList.add('is-closing');
      setTimeout(function () {
        panel.hidden = true;
        panel.classList.remove('is-closing', 'is-fullscreen');
        isOpen = false;
        stopPolling();
      }, 220);
    }

    function toggleFullscreen() {
      panel.classList.toggle('is-fullscreen');
      var icon = fullBtn.querySelector('i');
      if (panel.classList.contains('is-fullscreen')) {
        icon.className = 'fa-solid fa-compress';
        fullBtn.title = 'Kichiklashtirish';
      } else {
        icon.className = 'fa-solid fa-expand';
        fullBtn.title = "To'liq ekran";
      }
    }

    function scrollDown() {
      setTimeout(function () { messagesEl.scrollTop = messagesEl.scrollHeight; }, 50);
    }

    function renderMsg(m) {
      var cls = 'chat-msg' + (m.is_mine ? ' is-mine' : '') + (m.is_super_admin ? ' is-super-admin' : '');
      var badge = '';
      if (m.is_super_admin) {
        badge = '<span class="chat-msg-super-badge"><i class="fa-solid fa-crown"></i> Super Admin</span>';
      } else if (m.is_admin) {
        badge = '<span class="chat-msg-admin-badge">Admin</span>';
      }
      var avatarCls = 'chat-msg-avatar' + (m.is_super_admin ? ' chat-msg-avatar--super' : '');
      return '<div class="' + cls + '">'
        + '<div class="' + avatarCls + '">' + m.user_initial + '</div>'
        + '<div class="chat-msg-body">'
        + '<div class="chat-msg-meta">'
        + '<span class="chat-msg-name">' + m.user_name + '</span>'
        + badge
        + '<span class="chat-msg-time">' + m.date + ' ' + m.time + '</span>'
        + '</div>'
        + '<div class="chat-msg-text">' + m.body + '</div>'
        + '</div></div>';
    }

    function loadMessages() {
      fetch(messagesUrl + '?after=' + lastId, {
        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
        credentials: 'same-origin',
      })
        .then(function (r) { return r.json(); })
        .then(function (data) {
          var msgs = data.messages || [];
          if (!msgs.length) return;

          if (lastId === 0) {
            messagesEl.innerHTML = msgs.map(renderMsg).join('');
          } else {
            messagesEl.insertAdjacentHTML('beforeend', msgs.map(renderMsg).join(''));
          }

          lastId = data.last_id || lastId;
          scrollDown();
        })
        .catch(function () {});
    }

    function pollNew() {
      if (!isOpen) return;
      fetch(messagesUrl + '?after=' + lastId, {
        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
        credentials: 'same-origin',
      })
        .then(function (r) { return r.json(); })
        .then(function (data) {
          var msgs = data.messages || [];
          if (!msgs.length) return;
          messagesEl.insertAdjacentHTML('beforeend', msgs.map(renderMsg).join(''));
          lastId = data.last_id || lastId;
          scrollDown();
        })
        .catch(function () {});
    }

    function startPolling() {
      stopPolling();
      pollTimer = setInterval(pollNew, 5000);
    }

    function stopPolling() {
      if (pollTimer) { clearInterval(pollTimer); pollTimer = null; }
    }

    function sendMessage(text) {
      fetch(sendUrl, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': csrf,
        },
        credentials: 'same-origin',
        body: JSON.stringify({ body: text }),
      })
        .then(function () { pollNew(); })
        .catch(function () {});
    }

    bubble.addEventListener('click', function () {
      if (isOpen) closePanel();
      else openPanel();
    });

    closeBtn.addEventListener('click', closePanel);
    fullBtn.addEventListener('click', toggleFullscreen);

    form.addEventListener('submit', function (e) {
      e.preventDefault();
      var text = input.value.trim();
      if (!text) return;
      input.value = '';
      sendMessage(text);
    });

    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && isOpen) closePanel();
    });
  }

  moveGlobalModals();
  initShellUi();
  initRevealAnimations();
  initPasswordToggles();
  initMobileMenu();
  initHeaderClearance();
  initLocaleSwitcher();
  initSiteRules();
  initPhoneInputs();
  initImageLightbox();
  initToastAndTheme();
  initHeaderDropdowns();
  initInteractiveActions();
  initProMaxAnimations();
  initThemeBurstEffect();
  initLocalePageReveal();
  initGlobalChat();
})();
