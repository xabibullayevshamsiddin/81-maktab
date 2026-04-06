(() => {
  const profileRoot = document.querySelector('.profile-main[data-profile-i18n]');
  if (!profileRoot) return;

  const profileI18n = JSON.parse(profileRoot.dataset.profileI18n || '{}');

  const replaceTokens = (template, params = {}) => Object.entries(params).reduce((result, [key, value]) => {
    return result.replace(`:${key}`, String(value));
  }, String(template || ''));

  function bindPwToggles(rootEl = document) {
    rootEl.querySelectorAll('.pw-toggle').forEach((btn) => {
      if (btn.dataset.pwToggleBound === 'true') return;
      btn.dataset.pwToggleBound = 'true';

      btn.addEventListener('click', () => {
        const targetId = btn.getAttribute('data-target');
        const input = document.getElementById(targetId);
        const icon = btn.querySelector('i');
        if (!input) return;

        const isHidden = input.type === 'password';
        input.type = isHidden ? 'text' : 'password';
        icon?.classList.toggle('fa-eye', !isHidden);
        icon?.classList.toggle('fa-eye-slash', isHidden);
        btn.setAttribute('aria-label', isHidden ? profileI18n.hidePassword : profileI18n.showPassword);
      });
    });
  }

  function bindAvatarInput(rootEl = document) {
    const avatarInput = rootEl.querySelector('#profile-avatar');
    if (!avatarInput || avatarInput.dataset.avatarBound === 'true') return;

    avatarInput.dataset.avatarBound = 'true';

    const avatarBoxes = Array.from(rootEl.querySelectorAll('[data-profile-avatar-box]'));
    const avatarMeta = rootEl.querySelector('[data-profile-avatar-meta]');
    const avatarMetaDefault = avatarMeta ? avatarMeta.textContent.trim() : '';
    let previewObjectUrl = null;

    const setMeta = (message, state = '') => {
      if (!avatarMeta) return;
      avatarMeta.textContent = message;
      avatarMeta.classList.remove('is-ready', 'is-error', 'is-processing');
      if (state) avatarMeta.classList.add(state);
    };

    const loadAvatarSource = (src) => new Promise((resolve) => {
      if (!src) {
        resolve(false);
        return;
      }

      const image = new Image();
      image.onload = () => resolve(true);
      image.onerror = () => resolve(false);
      image.src = src;
    });

    const applyAvatarSource = async (box, src) => {
      const initial = box.dataset.profileAvatarInitial || '';
      const ok = await loadAvatarSource(src);

      if (ok) {
        box.classList.add('profile-avatar--image');
        box.style.backgroundImage = `url("${src}")`;
      } else {
        box.classList.remove('profile-avatar--image');
        box.style.backgroundImage = '';
      }

      box.textContent = initial;
    };

    const updatePreview = async (src) => {
      await Promise.all(avatarBoxes.map((box) => applyAvatarSource(box, src)));
    };

    const formatSize = (bytes) => {
      if (bytes < 1024) return `${bytes} B`;
      if (bytes < 1024 * 1024) return `${Math.round(bytes / 1024)} KB`;
      return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
    };

    const loadImage = (file) => new Promise((resolve, reject) => {
      const objectUrl = URL.createObjectURL(file);
      const image = new Image();

      image.onload = () => {
        URL.revokeObjectURL(objectUrl);
        resolve(image);
      };

      image.onerror = () => {
        URL.revokeObjectURL(objectUrl);
        reject(new Error('image_load_failed'));
      };

      image.src = objectUrl;
    });

    const optimizeAvatarFile = async (file) => {
      const image = await loadImage(file);
      const canvas = document.createElement('canvas');
      const size = 320;
      canvas.width = size;
      canvas.height = size;

      const context = canvas.getContext('2d');
      if (!context) {
        throw new Error('canvas_context_missing');
      }

      const sourceWidth = image.naturalWidth || image.width;
      const sourceHeight = image.naturalHeight || image.height;
      const cropSize = Math.min(sourceWidth, sourceHeight);
      const sourceX = (sourceWidth - cropSize) / 2;
      const sourceY = (sourceHeight - cropSize) / 2;

      context.clearRect(0, 0, size, size);
      context.drawImage(image, sourceX, sourceY, cropSize, cropSize, 0, 0, size, size);

      const blob = await new Promise((resolve, reject) => {
        canvas.toBlob((result) => {
          if (!result) {
            reject(new Error('blob_failed'));
            return;
          }

          resolve(result);
        }, 'image/webp', 0.82);
      });

      const safeName = (file.name || 'avatar')
        .replace(/\.[^.]+$/, '')
        .replace(/[^a-z0-9_-]+/gi, '-')
        .replace(/^-+|-+$/g, '')
        .slice(0, 40) || 'avatar';

      return new File([blob], `${safeName}.webp`, {
        type: 'image/webp',
        lastModified: Date.now(),
      });
    };

    avatarInput.addEventListener('change', async () => {
      const file = avatarInput.files?.[0];
      if (!file) {
        setMeta(avatarMetaDefault);
        return;
      }

      setMeta(profileI18n.preparingAvatar, 'is-processing');

      try {
        const optimized = await optimizeAvatarFile(file);
        const transfer = new DataTransfer();
        transfer.items.add(optimized);
        avatarInput.files = transfer.files;

        if (previewObjectUrl) URL.revokeObjectURL(previewObjectUrl);
        previewObjectUrl = URL.createObjectURL(optimized);

        await updatePreview(previewObjectUrl);
        setMeta(replaceTokens(profileI18n.avatarReady, {
          size: formatSize(optimized.size),
          dimensions: '320x320',
        }), 'is-ready');
      } catch (error) {
        if (previewObjectUrl) URL.revokeObjectURL(previewObjectUrl);
        previewObjectUrl = URL.createObjectURL(file);
        await updatePreview(previewObjectUrl);
        setMeta(profileI18n.avatarFallback, 'is-error');
      }
    });

    avatarBoxes.forEach((box) => {
      const existingSrc = box.dataset.profileAvatarUrl || '';
      if (existingSrc) {
        applyAvatarSource(box, existingSrc);
      }
    });

    window.addEventListener('beforeunload', () => {
      if (previewObjectUrl) URL.revokeObjectURL(previewObjectUrl);
    }, { once: true });
  }

  function clearFormErrors(form) {
    form.querySelectorAll('.profile-form-error.is-dynamic').forEach((node) => node.remove());
  }

  function renderFormErrors(form, errors) {
    clearFormErrors(form);

    Object.entries(errors || {}).forEach(([name, messages]) => {
      const field = form.querySelector(`[name="${name}"]`)?.closest('.profile-field');
      if (!field) return;

      const message = Array.isArray(messages) ? messages[0] : messages;
      if (!message) return;

      const errorEl = document.createElement('p');
      errorEl.className = 'form-message profile-form-error is-dynamic';
      errorEl.textContent = String(message);

      const anchor = field.querySelector('.pw-wrap') || field.querySelector('input, select, textarea');
      if (anchor) {
        anchor.insertAdjacentElement('afterend', errorEl);
      } else {
        field.appendChild(errorEl);
      }
    });
  }

  function setSubmitting(form, isSubmitting) {
    form.classList.toggle('is-submitting', isSubmitting);

    form.querySelectorAll('button[type="submit"], input[type="submit"]').forEach((el) => {
      el.disabled = isSubmitting;
      el.setAttribute('aria-busy', isSubmitting ? 'true' : 'false');
    });
  }

  function replaceSection(sectionName, html) {
    const current = profileRoot.querySelector(`[data-profile-section="${sectionName}"]`);
    if (!current || !html) return;

    current.outerHTML = html;
    bindPwToggles(profileRoot);
    bindProfileStagger(profileRoot);
  }

  function syncEmailText(email) {
    if (!email) return;

    profileRoot.querySelectorAll('[data-profile-user-email]').forEach((el) => {
      el.textContent = email;
    });
  }

  function bindProfileStagger(rootEl = profileRoot) {
    const items = Array.from(rootEl.querySelectorAll('.stagger-item'));
    if (!items.length) return;

    if (!('IntersectionObserver' in window)) {
      items.forEach((item) => item.classList.add('visible'));
      return;
    }

    const observer = new IntersectionObserver((entries) => {
      entries.forEach((entry) => {
        if (!entry.isIntersecting) return;

        entry.target.classList.add('visible');
        observer.unobserve(entry.target);
      });
    }, {
      threshold: 0.12,
      rootMargin: '0px 0px -40px 0px',
    });

    items.forEach((item, index) => {
      item.style.transitionDelay = `${Math.min(index * 70, 280)}ms`;
      observer.observe(item);
    });
  }

  bindPwToggles(profileRoot);
  bindAvatarInput(profileRoot);
  bindProfileStagger(profileRoot);

  document.addEventListener('submit', async (event) => {
    const form = event.target.closest('form[data-profile-async]');
    if (!form || !profileRoot.contains(form)) return;

    event.preventDefault();
    clearFormErrors(form);
    setSubmitting(form, true);

    try {
      const response = await fetch(form.action, {
        method: 'POST',
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          Accept: 'application/json',
        },
        body: new FormData(form),
      });

      const data = await response.json().catch(() => ({}));

      if (!response.ok || !data.ok) {
        renderFormErrors(form, data.errors || {});
        window.showToast?.(data.message || profileI18n.saveError, data.toast_type || 'error');
        return;
      }

      if (data.section && data.html) {
        replaceSection(data.section, data.html);
      }

      if (data.user_email) {
        syncEmailText(data.user_email);
      }

      window.showToast?.(data.message || profileI18n.saved, data.toast_type || 'success');

      if (data.section === 'password' && data.password_unlocked) {
        profileRoot.querySelector('#profile-new-password')?.focus();
      }

      if (data.section === 'email' && data.pending_email) {
        profileRoot.querySelector('#email-code')?.focus();
      }
    } catch (error) {
      window.showToast?.(profileI18n.serverError, 'error');
    } finally {
      setSubmitting(form, false);
    }
  });
})();
