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

  function escChatHtml(s) {
    return String(s ?? '').replace(/[&<>"']/g, function (ch) {
      return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[ch] || ch;
    });
  }

  function escAttr(s) {
    return String(s ?? '').replace(/"/g, '&quot;');
  }

  /** Chat panel uchun Escape; dialog ochiq bo‘lsa avvalo dialog yopiladi. */
  let chatPanelEscapeHandler = null;

  // Cinematic Audio System
  let primeAudioMuted = localStorage.getItem('site-audio-muted') === 'true';
  let primeAudioCtx = null;
  function getPrimeAudioCtx() {
    if (!primeAudioCtx) primeAudioCtx = new (window.AudioContext || window.webkitAudioContext)();
    if (primeAudioCtx.state === 'suspended') primeAudioCtx.resume();
    return primeAudioCtx;
  }

  window.playPrimeChatTick = function() {
    if (primeAudioMuted) return;
    try {
      const ctx = getPrimeAudioCtx();
      const t = ctx.currentTime;
      // Yumshoq "water drop" ding — sine + reverb tail
      const notes = [
        { f: 1318.5, o: 0,    d: 0.18, v: 0.07 },
        { f: 1760,   o: 0.04, d: 0.22, v: 0.05 },
      ];
      notes.forEach(({ f, o, d, v }) => {
        const t0 = t + o;
        const osc = ctx.createOscillator();
        const gain = ctx.createGain();
        osc.type = 'sine';
        osc.frequency.setValueAtTime(f, t0);
        osc.frequency.exponentialRampToValueAtTime(f * 0.98, t0 + d);
        gain.gain.setValueAtTime(0, t0);
        gain.gain.linearRampToValueAtTime(v, t0 + 0.008);
        gain.gain.exponentialRampToValueAtTime(0.001, t0 + d);
        osc.connect(gain);
        gain.connect(ctx.destination);
        osc.start(t0);
        osc.stop(t0 + d + 0.05);
      });
    } catch (e) {}
  }

  window.playPrimeSuccess = function() {
    if (primeAudioMuted) return;
    try {
      const ctx = getPrimeAudioCtx();
      const now = ctx.currentTime;
      // "Crystal chime" — 3 nota, sine, yumshoq attack, uzoq tail
      const notes = [
        { f: 523.25, o: 0,    d: 0.35, v: 0.08 },
        { f: 659.25, o: 0.06, d: 0.38, v: 0.07 },
        { f: 1046.5, o: 0.13, d: 0.42, v: 0.06 },
      ];
      notes.forEach(({ f, o, d, v }) => {
        const t0 = now + o;
        const osc = ctx.createOscillator();
        const gain = ctx.createGain();
        osc.type = 'sine';
        osc.frequency.setValueAtTime(f, t0);
        gain.gain.setValueAtTime(0, t0);
        gain.gain.linearRampToValueAtTime(v, t0 + 0.015);
        gain.gain.exponentialRampToValueAtTime(0.001, t0 + d);
        osc.connect(gain);
        gain.connect(ctx.destination);
        osc.start(t0);
        osc.stop(t0 + d + 0.05);
      });
    } catch (e) {}
  }


  /** Imtihon qoidasi: eslatuvchi, lekin keskin emas */
  window.playPrimeViolationSound = function() {
    try {
      const ctx = getPrimeAudioCtx();
      const now = ctx.currentTime;
      const pulse = (freq, start, dur, peak) => {
        const osc = ctx.createOscillator();
        const gain = ctx.createGain();
        osc.type = 'sawtooth';
        osc.frequency.setValueAtTime(freq, start);
        osc.frequency.exponentialRampToValueAtTime(freq * 0.85, start + dur * 0.85);
        gain.gain.setValueAtTime(0, start);
        gain.gain.linearRampToValueAtTime(peak, start + 0.035);
        gain.gain.exponentialRampToValueAtTime(0.001, start + dur);
        osc.connect(gain);
        gain.connect(ctx.destination);
        osc.start(start);
        osc.stop(start + dur + 0.02);
      };
      pulse(305, now, 0.32, 0.17);
      pulse(265, now + 0.4, 0.42, 0.15);
    } catch (e) {}
  };

  function playGlobalSearchOpenSound() {
    if (primeAudioMuted) return;
    try {
      const ctx = getPrimeAudioCtx();
      const now = ctx.currentTime;
      // "Swoosh up" — frequency sweep + soft chime
      const osc = ctx.createOscillator();
      const gain = ctx.createGain();
      osc.type = 'sine';
      osc.frequency.setValueAtTime(300, now);
      osc.frequency.exponentialRampToValueAtTime(1200, now + 0.18);
      gain.gain.setValueAtTime(0, now);
      gain.gain.linearRampToValueAtTime(0.06, now + 0.04);
      gain.gain.exponentialRampToValueAtTime(0.001, now + 0.22);
      osc.connect(gain);
      gain.connect(ctx.destination);
      osc.start(now);
      osc.stop(now + 0.25);
      // Chime on top
      const osc2 = ctx.createOscillator();
      const gain2 = ctx.createGain();
      osc2.type = 'sine';
      osc2.frequency.setValueAtTime(1760, now + 0.15);
      gain2.gain.setValueAtTime(0, now + 0.15);
      gain2.gain.linearRampToValueAtTime(0.05, now + 0.17);
      gain2.gain.exponentialRampToValueAtTime(0.001, now + 0.38);
      osc2.connect(gain2);
      gain2.connect(ctx.destination);
      osc2.start(now + 0.15);
      osc2.stop(now + 0.42);
    } catch (e) {}
  }

  function playGlobalSearchCloseSound() {
    if (primeAudioMuted) return;
    try {
      const ctx = getPrimeAudioCtx();
      const t = ctx.currentTime;
      // "Soft swoosh down"
      const osc = ctx.createOscillator();
      const gain = ctx.createGain();
      osc.type = 'sine';
      osc.frequency.setValueAtTime(900, t);
      osc.frequency.exponentialRampToValueAtTime(220, t + 0.2);
      gain.gain.setValueAtTime(0, t);
      gain.gain.linearRampToValueAtTime(0.055, t + 0.012);
      gain.gain.exponentialRampToValueAtTime(0.001, t + 0.22);
      osc.connect(gain);
      gain.connect(ctx.destination);
      osc.start(t);
      osc.stop(t + 0.25);
    } catch (e) {}
  }

  function playGlobalSearchNotFoundSound() {
    if (primeAudioMuted) return;
    try {
      const ctx = getPrimeAudioCtx();
      const now = ctx.currentTime;
      // "Gentle hollow knock" — sine, past freq, tez so'nadi
      const osc = ctx.createOscillator();
      const gain = ctx.createGain();
      osc.type = 'sine';
      osc.frequency.setValueAtTime(280, now);
      osc.frequency.exponentialRampToValueAtTime(180, now + 0.18);
      gain.gain.setValueAtTime(0, now);
      gain.gain.linearRampToValueAtTime(0.055, now + 0.01);
      gain.gain.exponentialRampToValueAtTime(0.001, now + 0.2);
      osc.connect(gain);
      gain.connect(ctx.destination);
      osc.start(now);
      osc.stop(now + 0.22);
    } catch (e) {}
  }

  window.playPrimeThemeToggleSound = function(isDark) {
    if (primeAudioMuted) return;
    try {
      const ctx = getPrimeAudioCtx();
      const now = ctx.currentTime;
      // Dark: "moonrise" — past, yumshoq descend
      // Light: "sunrise" — yuqori, yorqin ascend
      const osc = ctx.createOscillator();
      const gain = ctx.createGain();
      osc.type = 'sine';
      const startFreq = isDark ? 660 : 330;
      const endFreq   = isDark ? 330 : 880;
      osc.frequency.setValueAtTime(startFreq, now);
      osc.frequency.exponentialRampToValueAtTime(endFreq, now + 0.22);
      gain.gain.setValueAtTime(0, now);
      gain.gain.linearRampToValueAtTime(0.07, now + 0.018);
      gain.gain.exponentialRampToValueAtTime(0.001, now + 0.24);
      osc.connect(gain);
      gain.connect(ctx.destination);
      osc.start(now);
      osc.stop(now + 0.27);
      // Ikkinchi harmonik
      const osc2 = ctx.createOscillator();
      const gain2 = ctx.createGain();
      osc2.type = 'sine';
      osc2.frequency.setValueAtTime(startFreq * 1.5, now + 0.06);
      osc2.frequency.exponentialRampToValueAtTime(endFreq * 1.5, now + 0.26);
      gain2.gain.setValueAtTime(0, now + 0.06);
      gain2.gain.linearRampToValueAtTime(0.04, now + 0.08);
      gain2.gain.exponentialRampToValueAtTime(0.001, now + 0.28);
      osc2.connect(gain2);
      gain2.connect(ctx.destination);
      osc2.start(now + 0.06);
      osc2.stop(now + 0.32);
    } catch (e) {}
  };

  /** Prime Pro Max: Page Transition */
  function initPageTransitions() {
    const loader = document.getElementById('prime-page-loader');

    // Page load fade in
    window.addEventListener('load', () => {
      setTimeout(() => {
        if (loader) loader.classList.add('fade-out');
        document.body.classList.add('page-ready');
      }, 300);
    });

    // Page leave fade out on link click
    document.addEventListener('click', (e) => {
      const link = e.target.closest('a');
      if (!link) return;

      const href = link.getAttribute('href');
      const target = link.getAttribute('target');

      if (href && !href.startsWith('#') && !href.startsWith('javascript:') && !href.startsWith('tel:') && !href.startsWith('mailto:') && target !== '_blank' && !e.metaKey && !e.ctrlKey) {
        e.preventDefault();
        document.body.classList.remove('page-ready');
        if (loader) loader.classList.remove('fade-out');

        setTimeout(() => {
          window.location.href = href;
        }, 400);
      }
    });
  }

  /** Prime Pro Max: Cinematic Theme Toggle */
  function initCinematicThemeToggle() {
    const toggle = document.querySelector('.theme-toggle') || document.querySelector('[data-theme-toggle]');
    if (!toggle) return;

    toggle.addEventListener('click', (e) => {
      const isDark = root.getAttribute('data-theme') === 'dark';
      const nextTheme = isDark ? 'light' : 'dark';

      // Cinematic Reveal
      const canvas = document.getElementById('theme-transition-canvas');
      if (!canvas) {
        root.setAttribute('data-theme', nextTheme);
        localStorage.setItem('site-theme', nextTheme);
        return;
      }

      const rect = toggle.getBoundingClientRect();
      const x = rect.left + rect.width / 2;
      const y = rect.top + rect.height / 2;

      const ctx = canvas.getContext('2d');
      canvas.width = window.innerWidth;
      canvas.height = window.innerHeight;

      document.body.classList.add('theme-transition-active');

      let radius = 0;
      const maxRadius = Math.sqrt(Math.pow(window.innerWidth, 2) + Math.pow(window.innerHeight, 2));

      function animate() {
        radius += maxRadius / 20;
        ctx.beginPath();
        ctx.arc(x, y, radius, 0, Math.PI * 2);
        ctx.fillStyle = nextTheme === 'dark' ? '#07111f' : '#edf2fb';
        ctx.fill();

        if (radius < maxRadius) {
          requestAnimationFrame(animate);
        } else {
          root.setAttribute('data-theme', nextTheme);
          localStorage.setItem('site-theme', nextTheme);
          document.dispatchEvent(new CustomEvent('themeChanged', { detail: { theme: nextTheme } }));
          setTimeout(() => {
            document.body.classList.remove('theme-transition-active');
            ctx.clearRect(0, 0, canvas.width, canvas.height);
          }, 100);
        }
      }
      animate();
    });
  }

  /** Prime Pro Max: Animated Charts (ApexCharts) */
  function playPrimeConfetti(x, y, isGold = false) {
    const colors = isGold
        ? ['#f59e0b', '#fbbf24', '#fcd34d', '#ffffff']
        : ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#dc2626'];
    const count = isGold ? 48 : 32;
    for (let i = 0; i < count; i++) {
      const p = document.createElement('div');
      p.className = 'prime-particle' + (isGold ? ' is-gold' : '');
      const size = isGold ? Math.random() * 12 + 6 : Math.random() * 8 + 4;
      p.style.width = size + 'px';
      p.style.height = size + 'px';
      p.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
      p.style.left = x + 'px';
      p.style.top = y + 'px';

      const angle = Math.random() * Math.PI * 2;
      const dist = isGold ? Math.random() * 200 + 80 : Math.random() * 120 + 50;
      const tx = Math.cos(angle) * dist;
      const ty = Math.sin(angle) * dist;

      p.style.setProperty('--tx', tx + 'px');
      p.style.setProperty('--ty', ty + 'px');

      document.body.appendChild(p);
      setTimeout(() => p.remove(), isGold ? 1200 : 800);
    }
  }

  /** Imtihon o‘tdi: major arpeggio + yumshoq envelope + eng oxirida yengil “sparkle” */
  window.playPrimeResultPass = function() {
    if (primeAudioMuted) return;
    try {
      const ctx = getPrimeAudioCtx();
      const now = ctx.currentTime;
      const notes = [
        { f: 523.25, o: 0, d: 0.45, v: 0.11 },
        { f: 659.25, o: 0.09, d: 0.45, v: 0.1 },
        { f: 783.99, o: 0.18, d: 0.45, v: 0.1 },
        { f: 1046.5, o: 0.28, d: 0.58, v: 0.12 },
      ];
      notes.forEach(({ f, o, d, v }) => {
        const t0 = now + o;
        const osc = ctx.createOscillator();
        const gain = ctx.createGain();
        osc.type = 'triangle';
        osc.frequency.setValueAtTime(f, t0);
        gain.gain.setValueAtTime(0, t0);
        gain.gain.linearRampToValueAtTime(v, t0 + 0.04);
        gain.gain.exponentialRampToValueAtTime(0.001, t0 + d);
        osc.connect(gain);
        gain.connect(ctx.destination);
        osc.start(t0);
        osc.stop(t0 + d + 0.03);
      });
      const spark = ctx.createOscillator();
      const sg = ctx.createGain();
      spark.type = 'sine';
      spark.frequency.setValueAtTime(2093, now + 0.28);
      sg.gain.setValueAtTime(0, now + 0.28);
      sg.gain.linearRampToValueAtTime(0.035, now + 0.33);
      sg.gain.exponentialRampToValueAtTime(0.001, now + 0.72);
      spark.connect(sg);
      sg.connect(ctx.destination);
      spark.start(now + 0.28);
      spark.stop(now + 0.75);
    } catch (e) {}
  };

  /** Yiqilish: past tonli, pastga siljigan minor — keskin emas */
  window.playPrimeResultFail = function() {
    if (primeAudioMuted) return;
    try {
      const ctx = getPrimeAudioCtx();
      const now = ctx.currentTime;
      const phrase = [
        { f: 311.13, o: 0, d: 0.4, v: 0.095 },
        { f: 277.18, o: 0.13, d: 0.44, v: 0.085 },
        { f: 246.94, o: 0.28, d: 0.52, v: 0.075 },
      ];
      phrase.forEach(({ f, o, d, v }) => {
        const t0 = now + o;
        const osc = ctx.createOscillator();
        const gain = ctx.createGain();
        osc.type = 'triangle';
        osc.frequency.setValueAtTime(f, t0);
        osc.frequency.exponentialRampToValueAtTime(f * 0.96, t0 + d);
        gain.gain.setValueAtTime(0, t0);
        gain.gain.linearRampToValueAtTime(v, t0 + 0.05);
        gain.gain.exponentialRampToValueAtTime(0.001, t0 + d);
        osc.connect(gain);
        gain.connect(ctx.destination);
        osc.start(t0);
        osc.stop(t0 + d + 0.03);
      });
    } catch (e) {}
  };

  function initSeniorInteractions() {
    // 1. 3D Card Tilt (Removed as per user request)


  }


  function initChatUserPreviewChrome() {
    var dlg = document.getElementById('chat-user-preview-dialog');
    if (!dlg) return;
    var btn = document.getElementById('chat-user-preview-close');
    if (btn) {
      btn.addEventListener('click', function () {
        if (dlg.close) dlg.close();
      });
    }
    dlg.addEventListener('click', function (e) {
      if (e.target === dlg && dlg.close) dlg.close();
    });
    document.addEventListener('keydown', function (e) {
      if (e.key !== 'Escape') return;
      if (dlg.open) {
        dlg.close();
        return;
      }
      if (chatPanelEscapeHandler) chatPanelEscapeHandler();
    });
  }

  function initUserProfilePreview() {
    var previewLoadingForId = null;

    function userPreviewConfigEl() {
      return document.getElementById('user-preview-config') || document.getElementById('chat-widget');
    }

    function buildChatUserPreviewExtra(d) {
      var parts = [];
      var courses = d.courses;
      if (courses) {
        if (courses.created && courses.created.length) {
          var itemsCreated = courses.created.map(function (c) {
            var t = escChatHtml(c.title || '');
            if (c.url) {
              return '<li><a href="' + escAttr(c.url) + '" target="_blank" rel="noopener">' + t + '</a></li>';
            }
            return '<li><span>' + t + '</span> <span class="chat-user-preview-muted">(nashr emas)</span></li>';
          }).join('');
          parts.push(
            '<div class="chat-user-preview-section">'
            + '<h4 class="chat-user-preview-section-title">Kurslar — yaratgan</h4>'
            + '<ul class="chat-user-preview-links">' + itemsCreated + '</ul></div>'
          );
        }
        if (courses.enrolled && courses.enrolled.length) {
          var itemsEnr = courses.enrolled.map(function (c) {
            var t = escChatHtml(c.title || '');
            if (c.url) {
              return '<li><a href="' + escAttr(c.url) + '" target="_blank" rel="noopener">' + t + '</a></li>';
            }
            return '<li><span>' + t + '</span></li>';
          }).join('');
          parts.push(
            '<div class="chat-user-preview-section">'
            + '<h4 class="chat-user-preview-section-title">Kurslarda qatnashish</h4>'
            + '<ul class="chat-user-preview-links">' + itemsEnr + '</ul></div>'
          );
        }
      }
      var ex = d.exam_stats;
      if (ex) {
        var showExam = (ex.finished_total > 0)
          || (ex.started_incomplete > 0)
          || (ex.pending_grade > 0)
          || (ex.avg_percent != null)
          || (ex.pass_rate_percent != null);
        if (showExam) {
          var statParts = [];
          statParts.push(
            '<div class="chat-user-preview-stat-row"><span>Jami topshirilgan</span><strong>'
            + escChatHtml(String(ex.finished_total ?? 0)) + '</strong></div>'
          );
          statParts.push(
            '<div class="chat-user-preview-stat-row"><span>O‘tgan</span><strong class="chat-user-preview-stat--ok">'
            + escChatHtml(String(ex.passed ?? 0)) + '</strong></div>'
          );
          statParts.push(
            '<div class="chat-user-preview-stat-row"><span>Yiqilgan</span><strong class="chat-user-preview-stat--bad">'
            + escChatHtml(String(ex.failed ?? 0)) + '</strong></div>'
          );
          if ((ex.pending_grade ?? 0) > 0) {
            statParts.push(
              '<div class="chat-user-preview-stat-row"><span>Tekshiruvda (ball kutilmoqda)</span><strong>'
              + escChatHtml(String(ex.pending_grade)) + '</strong></div>'
            );
          }
          if ((ex.started_incomplete ?? 0) > 0) {
            statParts.push(
              '<div class="chat-user-preview-stat-row"><span>Hali tugatmagan</span><strong>'
              + escChatHtml(String(ex.started_incomplete)) + '</strong></div>'
            );
          }
          if (ex.avg_percent != null) {
            statParts.push(
              '<div class="chat-user-preview-stat-row"><span>O‘rtacha foiz (ball)</span><strong>'
              + escChatHtml(String(ex.avg_percent)) + '%</strong></div>'
            );
          }
          if (ex.pass_rate_percent != null) {
            statParts.push(
              '<div class="chat-user-preview-stat-row"><span>O‘tish darajasi</span><strong>'
              + escChatHtml(String(ex.pass_rate_percent)) + '%</strong></div>'
            );
            statParts.push(
              '<p class="chat-user-preview-stat-note">O‘tgan va yiqilgan hisoblangan imtihonlar nisbati.</p>'
            );
          }
          parts.push(
            '<div class="chat-user-preview-section">'
            + '<h4 class="chat-user-preview-section-title">Imtihonlar</h4>'
            + '<div class="chat-user-preview-stat-block">' + statParts.join('') + '</div></div>'
          );
        }
      }
      return parts.join('');
    }

    function openUserProfilePreview(userId) {
      var cfg = userPreviewConfigEl();
      var previewDialog = document.getElementById('chat-user-preview-dialog');
      var previewBase = cfg && cfg.getAttribute('data-user-preview-base');
      var csrf = cfg && cfg.getAttribute('data-csrf');
      var previewLoading = document.getElementById('chat-user-preview-loading');
      var previewContent = document.getElementById('chat-user-preview-content');
      var previewAvatar = document.getElementById('chat-user-preview-avatar');
      var previewNameEl = document.getElementById('chat-user-preview-name');
      var previewRoleEl = document.getElementById('chat-user-preview-role');
      var previewDetailsEl = document.getElementById('chat-user-preview-details');
      var previewExtraEl = document.getElementById('chat-user-preview-extra');
      var previewAdminEl = document.getElementById('chat-user-preview-admin-actions');
      var previewContactEl = document.getElementById('chat-user-preview-contact');
      if (!previewDialog || !previewBase || !previewLoading || !previewContent) return;
      previewLoadingForId = String(userId);
      previewLoading.hidden = false;
      previewLoading.textContent = 'Yuklanmoqda…';
      previewContent.hidden = true;
      if (previewContactEl) {
        previewContactEl.hidden = true;
        previewContactEl.innerHTML = '';
      }
      if (previewAdminEl) {
        previewAdminEl.hidden = true;
        previewAdminEl.innerHTML = '';
      }
      if (previewExtraEl) previewExtraEl.innerHTML = '';
      if (typeof previewDialog.showModal === 'function') {
        previewDialog.showModal();
      }
      fetch(previewBase + '/' + userId + '/preview', {
        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
        credentials: 'same-origin',
      })
        .then(function (r) {
          if (!r.ok) throw new Error();
          return r.json();
        })
        .then(function (d) {
          if (previewLoadingForId !== String(userId)) return;
          previewLoading.hidden = true;
          previewContent.hidden = false;

          // Super admin and Admin prime effect
          var themeKey = d.donor_theme || d.donor_rank;
          var donorRankKeys = ['supporter', 'premium', 'vip'];
          // Barcha mumkin bo'lgan tema klasslarini tozalash
          var allThemeClasses = ['is-super-admin', 'is-admin',
            'is-donor',
            'is-donor-supporter', 'is-donor-premium', 'is-donor-vip',
            'is-themed-supporter', 'is-themed-premium', 'is-themed-vip',
            'is-themed-admin-gold', 'is-themed-admin-royal', 'is-themed-admin-phoenix'];
          if (d.is_super_admin) {
            previewDialog.classList.add('is-super-admin');
            previewDialog.classList.remove(...allThemeClasses.filter(c => c !== 'is-super-admin'));
          } else if (themeKey) {
            previewDialog.classList.remove(...allThemeClasses);
            previewDialog.classList.add('is-themed-' + themeKey);
            if (donorRankKeys.indexOf(themeKey) !== -1) {
              previewDialog.classList.add('is-donor', 'is-donor-' + themeKey);
            }
            spawnSuperAdminParticles(previewDialog);
          } else if (d.is_admin) {
            previewDialog.classList.add('is-admin');
            previewDialog.classList.remove(...allThemeClasses.filter(c => c !== 'is-admin'));
            var oldPfx = previewDialog.querySelector('.sa-particles');
            if (oldPfx) oldPfx.remove();
          } else {
            previewDialog.classList.remove(...allThemeClasses);
            var oldPfx2 = previewDialog.querySelector('.sa-particles');
            if (oldPfx2) oldPfx2.remove();
          }

          if (previewNameEl) {
            previewNameEl.textContent = d.display_name || '';
            var previewNameStyle = '';
            if (d.donor_color && /^#[0-9a-f]{3,8}$/i.test(String(d.donor_color))) {
              previewNameStyle += 'color:' + d.donor_color + ';';
            }
            if (d.name_font_weight && /^(600|700|800)$/.test(String(d.name_font_weight))) {
              previewNameStyle += 'font-weight:' + d.name_font_weight + ';';
            }
            previewNameEl.setAttribute('style', previewNameStyle);
          }
          if (previewRoleEl) {
            var rl = escChatHtml(d.role_label || '');
            var lvlText = d.role_level ? 'LVL ' + String(d.role_level) + ' &bull; ' : '';
            if (d.is_super_admin) {
              previewRoleEl.innerHTML = '<span class="chat-user-preview-badge chat-user-preview-badge--super"><i class="fa-solid fa-crown"></i> ' + lvlText + rl + '</span>';
            } else if (d.is_admin) {
              previewRoleEl.innerHTML = '<span class="chat-user-preview-badge"><i class="fa-solid fa-shield-halved"></i> ' + lvlText + rl + '</span>';
            } else {
              previewRoleEl.innerHTML = '<span class="chat-user-preview-badge chat-user-preview-badge--base"><i class="fa-solid fa-user"></i> ' + lvlText + rl + '</span>';
            }
          if (d.donor_badge) {
            previewRoleEl.innerHTML = d.donor_badge + ' ' + previewRoleEl.innerHTML;
          }
          }
          if (previewAvatar) {
            previewAvatar.className = 'chat-user-preview-avatar';
            if (d.is_super_admin) previewAvatar.classList.add('chat-user-preview-avatar--super');
            previewAvatar.innerHTML = '';
            if (d.avatar_url) {
              var img = document.createElement('img');
              img.src = d.avatar_url;
              img.alt = '';
              img.className = 'chat-user-preview-avatar-img';
              img.loading = 'lazy';
              previewAvatar.appendChild(img);
            } else {
              var ini = (d.display_name || '?').trim().charAt(0).toUpperCase();
              previewAvatar.textContent = ini;
              previewAvatar.classList.add('chat-user-preview-avatar--initial');
              if (d.is_super_admin) previewAvatar.classList.add('chat-user-preview-avatar--super-initial');
            }
          }
          if (previewDetailsEl) {
            var rows = [];
            var adminProfile = d.admin_profile || null;
            if (adminProfile) {
              rows.push('<li><span>ID</span> #' + escChatHtml(String(adminProfile.id || '0')) + '</li>');
              if (adminProfile.name) {
                rows.push('<li><span>Login nomi</span> ' + escChatHtml(adminProfile.name) + '</li>');
              }
              if (adminProfile.first_name) {
                rows.push('<li><span>Ism</span> ' + escChatHtml(adminProfile.first_name) + '</li>');
              }
              if (adminProfile.last_name) {
                rows.push('<li><span>Familiya</span> ' + escChatHtml(adminProfile.last_name) + '</li>');
              }
              rows.push('<li><span>Rol kaliti</span> ' + escChatHtml(adminProfile.role_key || '—') + '</li>');
              rows.push('<li><span>Akkaunt holati</span> ' + escChatHtml(adminProfile.status || '—') + '</li>');
              rows.push('<li><span>Hisob turi</span> ' + (adminProfile.is_parent ? 'Ota-ona' : 'Foydalanuvchi') + '</li>');
              if (adminProfile.grade) {
                rows.push('<li><span>Sinf</span> ' + escChatHtml(adminProfile.grade) + '</li>');
              }
              if (adminProfile.registered_at) {
                rows.push('<li><span>Ro‘yxatdan o‘tgan</span> ' + escChatHtml(adminProfile.registered_at) + '</li>');
              }
              rows.push('<li><span>Email tasdiqlangan</span> ' + escChatHtml(adminProfile.email_verified_at || 'Yo‘q') + '</li>');
              rows.push('<li><span>Kurs ochish ruxsati</span> ' + (adminProfile.course_open_approved ? 'Bor' : 'Yo‘q') + '</li>');
              rows.push('<li><span>So‘rov holati</span> ' + (adminProfile.course_open_request_pending ? 'Kutilmoqda' : 'Yo‘q') + '</li>');
            } else {
              if (d.grade) {
                rows.push('<li><span>Sinf</span> ' + escChatHtml(d.grade) + '</li>');
              }
              if (d.is_parent) {
                rows.push('<li><span>Hisob turi</span> Ota-ona</li>');
              }
              if (d.donor_theme || d.donor_rank) {
                rows.push('<li><span>Tema</span> ' + d.donor_badge + '</li>');
                if (d.donor_expires) {
                  rows.push('<li><span>Tugash vaqti</span> ' + escChatHtml(d.donor_expires) + '</li>');
                }
              }
              if (d.member_year) {
                rows.push('<li><span>Ro‘yxatdan o‘tgan</span> ' + escChatHtml(d.member_year) + '</li>');
              }
            }
            previewDetailsEl.innerHTML = rows.length ? rows.join('') : '<li class="chat-user-preview-details-empty">Qo‘shimcha maydonlar kiritilmagan.</li>';
          }
          if (previewExtraEl) {
            previewExtraEl.innerHTML = buildChatUserPreviewExtra(d);
            previewExtraEl.querySelectorAll('strong, .chat-user-preview-section-title').forEach(el => {
              scrambleText(el, el.innerText);
            });
          }
          if (previewContactEl && d.contact) {
            previewContactEl.hidden = false;
            var emailText = escChatHtml(d.contact.email || '—');
            var phoneText = escChatHtml(d.contact.phone || '—');
            var emailLink = d.contact.email
              ? '<a href="mailto:' + escAttr(String(d.contact.email)) + '">' + emailText + '</a>'
              : '<span>—</span>';
            var phoneLink = d.contact.phone
              ? '<a href="tel:' + escAttr(String(d.contact.phone)) + '">' + phoneText + '</a>'
              : '<span>—</span>';
            previewContactEl.innerHTML = '<p class="chat-user-preview-contact-kicker">Aloqa ma‘lumotlari (Super Admin)</p>'
              + '<div class="chat-user-preview-contact-row"><span>Email</span><span>' + emailLink + '</span></div>'
              + '<div class="chat-user-preview-contact-row"><span>Telefon</span><span>' + phoneLink + '</span></div>';
          } else if (previewContactEl) {
            previewContactEl.hidden = true;
            previewContactEl.innerHTML = '';
          }
          var previewAdminEl2 = document.getElementById('chat-user-preview-admin-actions');
          if (previewAdminEl2 && d.super_admin_actions) {
            previewAdminEl2.hidden = false;
            var sa = d.super_admin_actions;
            var cfgSelf = userPreviewConfigEl();
            var selfId = cfgSelf && cfgSelf.getAttribute('data-current-user-id');
            var admParts = [];
            admParts.push('<p class="chat-user-preview-admin-kicker">Boshqaruv (' + (d.viewer_is_super_admin ? 'Super Admin' : 'Administrator') + ')</p>');
            admParts.push(
              '<p class="chat-user-preview-admin-status">Akkaunt holati: <strong>'
              + (sa.is_active ? 'Faol' : 'Bloklangan') + '</strong></p>'
            );
            if (sa.can_deactivate) {
              admParts.push(
                '<button type="button" class="chat-user-preview-btn chat-user-preview-btn--danger" data-sa-deactivate="'
                + escAttr(String(userId)) + '"><i class="fa-solid fa-ban"></i> Bloklash (kirishni to‘xtatish)</button>'
              );
            }
            if (sa.can_activate) {
              admParts.push(
                '<button type="button" class="chat-user-preview-btn chat-user-preview-btn--ok" data-sa-activate="'
                + escAttr(String(userId)) + '"><i class="fa-solid fa-unlock"></i> Akkauntni qayta yoqish</button>'
              );
            }
            if (!sa.can_deactivate && !sa.can_activate) {
              if (sa.is_self || (selfId && String(userId) === String(selfId))) {
                admParts.push('<p class="chat-user-preview-muted">Bu o‘z profilingiz.</p>');
              } else {
                admParts.push('<p class="chat-user-preview-muted">Bu akkaunt uchun boshqaruv amali mavjud emas.</p>');
              }
            }
            previewAdminEl2.innerHTML = admParts.join('');
          } else if (previewAdminEl2) {
            previewAdminEl2.hidden = true;
            previewAdminEl2.innerHTML = '';
          }
          previewLoading.hidden = true;
          previewContent.hidden = false;
        })
        .catch(function () {
          if (previewLoadingForId !== String(userId)) return;
          previewLoading.textContent = 'Ma’lumot yuklab bo‘lmadi.';
        });
    }

    function spawnSuperAdminParticles(container) {
      var old = container.querySelector('.sa-particles');
      if (old) old.remove();
      var wrap = document.createElement('div');
      wrap.className = 'sa-particles';
      wrap.setAttribute('aria-hidden', 'true');
      var ICONS = ['fa-crown', 'fa-star', 'fa-gem', 'fa-bolt'];
      for (var i = 0; i < 14; i++) {
        var p = document.createElement('span');
        p.className = 'sa-particle';
        var icon = ICONS[i % ICONS.length];
        p.innerHTML = '<i class="fa-solid ' + icon + '"></i>';
        p.style.setProperty('--sa-x', (Math.random() * 100).toFixed(1) + '%');
        p.style.setProperty('--sa-delay', (Math.random() * 2.4).toFixed(2) + 's');
        p.style.setProperty('--sa-dur', (2.2 + Math.random() * 2).toFixed(2) + 's');
        p.style.setProperty('--sa-size', (9 + Math.random() * 8).toFixed(1) + 'px');
        p.style.setProperty('--sa-opacity', (0.35 + Math.random() * 0.5).toFixed(2));
        wrap.appendChild(p);
      }
      container.appendChild(wrap);
    }

    window.openUserProfilePreview = openUserProfilePreview;

    document.addEventListener('click', function (e) {
      var tr = e.target.closest('[data-user-preview-id]');
      if (!tr) return;
      e.preventDefault();
      var id = tr.getAttribute('data-user-preview-id');
      if (id) openUserProfilePreview(id);
    });

    document.addEventListener('click', function (e) {
      var deBtn = e.target.closest('[data-sa-deactivate]');
      var acBtn = e.target.closest('[data-sa-activate]');
      if (!deBtn && !acBtn) return;
      e.preventDefault();
      var cfg = userPreviewConfigEl();
      var previewBase = cfg && cfg.getAttribute('data-user-preview-base');
      var csrf = cfg && cfg.getAttribute('data-csrf');
      if (!previewBase || !csrf) return;
      var uid = deBtn ? deBtn.getAttribute('data-sa-deactivate') : acBtn.getAttribute('data-sa-activate');
      if (!uid) return;
      var isDeact = !!deBtn;
      var confirmMsg = isDeact
        ? 'Bu foydalanuvchini bloklaysizmi? U saytga kira olmaydi.'
        : 'Akkauntni qayta faollashtirasizmi?';
      function doFetch() {
      fetch(previewBase + '/' + uid + '/' + (isDeact ? 'deactivate' : 'activate'), {
        method: 'POST',
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrf,
        },
        credentials: 'same-origin',
        body: '{}',
      })
        .then(function (r) {
          return r.json().then(function (j) {
            if (!r.ok) {
              throw new Error((j && (j.error || j.message)) || 'Xatolik');
            }
            return j;
          });
        })
        .then(function () {
          if (window.showToast) {
            window.showToast(isDeact ? 'Foydalanuvchi bloklandi.' : 'Akkaunt faollashtirildi.', 'success');
          }
          openUserProfilePreview(uid);
        })
        .catch(function (err) {
          if (window.showToast) {
            window.showToast(err && err.message ? err.message : 'Amal bajarilmadi.', 'error');
          }
        });
      }
      var p = window.primeConfirm && window.primeConfirm({
        message: confirmMsg,
        title: isDeact ? 'Foydalanuvchini bloklash' : 'Akkauntni faollashtirish',
        variant: isDeact ? 'danger' : 'primary',
        okText: isDeact ? 'Bloklash' : 'Ha',
      });
      if (p && typeof p.then === 'function') {
        p.then(function (ok) { if (ok) doFetch(); });
      } else if (window.confirm(confirmMsg)) {
        doFetch();
      }
    });
  }

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

    siteNav
      .querySelectorAll('a.nav-link, button.nav-link, .nav-dropdown-menu a, .nav-dropdown-form button')
      .forEach((link) => {
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
    function escapeHtml(value) {
      const div = document.createElement('div');
      div.textContent = String(value ?? '');
      return div.innerHTML;
    }

    function normalizeToastLink(link) {
      if (!link) return '';

      try {
        const url = new URL(String(link), window.location.origin);
        return url.origin === window.location.origin ? url.href : '';
      } catch (error) {
        return '';
      }
    }

    function showToast(message, type = 'success', options = {}) {
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
      var toastLink = normalizeToastLink(options.link || options.url || '');
      toast.className = 'toast toast-' + type;
      toast.style.setProperty('--toast-duration', toastTimerMs + 'ms');
      if (toastLink) {
        toast.style.cursor = 'pointer';
        toast.setAttribute('role', 'button');
        toast.setAttribute('tabindex', '0');
      }
      toast.innerHTML =
        '<div class="toast-body">' +
          '<div class="toast-icon"><i class="' + (iconMap[type] || iconMap.success) + '"></i></div>' +
          '<div class="toast-content">' +
            '<p class="toast-title">' + escapeHtml(titleMap[type] || titleMap.success) + '</p>' +
            '<p class="toast-msg">' + escapeHtml(message) + '</p>' +
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
      toast.addEventListener('click', function () {
        if (toastLink) {
          window.location.href = toastLink;
          return;
        }

        dismissToast();
      });
      toast.addEventListener('keydown', function (event) {
        if (!toastLink || (event.key !== 'Enter' && event.key !== ' ')) return;
        event.preventDefault();
        window.location.href = toastLink;
      });

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

    /** Sessiya flash: success/error uchun `data-site-toast-type` (warning/error/success) */
    function resolveFlashToastType(defaultType) {
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

      // Tizim rejimi o'zgarganda sayt rejimini ham avtomatik moslashtirish (agar o'zi tanlamagan bo'lsa)
      if (window.matchMedia) {
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', e => {
          if (!localStorage.getItem(storageKey)) {
            applyTheme(e.matches ? 'dark' : 'light');
          }
        });
      }
    }

    if (successMsg) showToast(successMsg, resolveFlashToastType('success'));
    if (errorMsg) showToast(errorMsg, resolveFlashToastType('error'));
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

  async function fetchLiveStats(url) {
    if (!url) return null;

    const finalUrl = new URL(url, window.location.origin);
    finalUrl.searchParams.set('_', String(Date.now()));

    const response = await fetch(finalUrl.toString(), {
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        Accept: 'application/json',
      },
      cache: 'no-store',
      credentials: 'same-origin',
    });

    if (!response.ok) {
      return null;
    }

    const payload = await response.json();

    return payload && payload.ok ? payload : null;
  }

  function initPublicLiveStats() {
    const postScope = document.querySelector('[data-post-stats-url]');
    if (postScope) {
      fetchLiveStats(postScope.dataset.postStatsUrl)
        .then((data) => {
          if (!data) return;

          const viewsEl = postScope.querySelector('.js-post-views-count');
          const commentsEl = postScope.querySelector('.js-post-comments-count');
          const likeCountEl = postScope.querySelector('.js-like-form .like-count');

          if (viewsEl && data.views != null) {
            viewsEl.textContent = String(data.views);
          }

          if (commentsEl && data.comments_count != null) {
            commentsEl.textContent = String(data.comments_count);
          }

          if (likeCountEl && data.likes_count != null) {
            likeCountEl.textContent = String(data.likes_count);
          }
        })
        .catch(() => {});
    }

    const teacherScope = document.querySelector('[data-teacher-stats-url]');
    if (teacherScope) {
      fetchLiveStats(teacherScope.dataset.teacherStatsUrl)
        .then((data) => {
          if (!data) return;

          const teacherLikeBtnCountEl = teacherScope.querySelector('.js-like-form .like-count');
          const teacherLikesStatEl = document.querySelector('.js-teacher-likes-stat');

          if (teacherLikeBtnCountEl && data.likes_count != null) {
            teacherLikeBtnCountEl.textContent = String(data.likes_count);
          }

          if (teacherLikesStatEl && data.likes_count != null) {
            teacherLikesStatEl.textContent = String(data.likes_count);
          }
        })
        .catch(() => {});
    }
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

          if (data.liked) {
            btn.classList.remove('prime-like-trigger');
            void btn.offsetWidth; // reflow
            btn.classList.add('prime-like-trigger');
          }
        }

        window.showToast?.(data.message || (data.liked ? "Like qo'shildi." : 'Like olib tashlandi.'), data.toast_type || 'success');
      } catch (error) {
        window.showToast?.('Like qilishda xatolik', 'error');
      } finally {
        if (btn) btn.disabled = false;
      }
    });

    document.addEventListener('submit', async (event) => {
      const form = event.target.closest('form.js-bookmark-form');
      if (!form) return;

      event.preventDefault();
      const btn = form.querySelector('button.bookmark-btn');
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

        if (btn) {
          const icon = btn.querySelector('i');
          const saved = !!data.bookmarked;
          btn.classList.toggle('is-saved', saved);
          btn.setAttribute('aria-pressed', saved ? 'true' : 'false');
          if (icon) {
            icon.classList.toggle('fa-solid', saved);
            icon.classList.toggle('fa-regular', !saved);
          }
        }

        window.showToast?.(data.message || (data.bookmarked ? 'Saqlandi.' : "Olib tashlandi."), data.toast_type || 'success');
      } catch (error) {
        window.showToast?.('Saqlashda xatolik', 'error');
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
      const form = event.target.closest('#contact-form');
      if (!form) return;
      event.preventDefault();
      const btn = form.querySelector('button[type="submit"]');
      if (btn) btn.disabled = true;
      try {
        const response = await fetch(form.action, {
          method: 'POST',
          headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
          body: new FormData(form),
        });
        const data = await response.json();
        if (data && data.ok) {
          playPrimeSuccess();
          playPrimeConfetti(event.clientX || window.innerWidth / 2, event.clientY || window.innerHeight / 2);
          window.showToast?.(data.message || 'Xabar yuborildi', 'success');
          form.reset();
        } else {
          window.showToast?.(data?.message || 'Xatolik', 'error');
        }
      } catch (e) {
        window.showToast?.('Ba’zi ma’lumotlar kiritilmagan yoki xatolik yuz berdi.', 'error');
      } finally {
        if (btn) btn.disabled = false;
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
        let submitBody = new FormData(form);

        const needsCommentTurnstile = methodOverride !== 'delete';

        const commentTsHost = document.getElementById('comment-turnstile-host');
        if (
          needsCommentTurnstile &&
          commentTsHost &&
          commentTsHost.getAttribute('data-sitekey') &&
          window.turnstile &&
          typeof window.turnstile.execute === 'function'
        ) {
          await new Promise(function (resolve, reject) {
            window.turnstile.execute(commentTsHost, {
              callback: function (token) {
                submitBody.set('cf-turnstile-response', token);
                resolve();
              },
              'error-callback': function () {
                reject(new Error('Robot tekshiruvi bajarilmadi. Qayta urinib ko‘ring.'));
              },
            });
          });
        }

        const response = await fetch(form.action, {
          method: 'POST',
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            Accept: 'application/json',
          },
          body: submitBody,
        });

        const raw = await response.text();
        let data = null;

        if (raw) {
          try {
            data = JSON.parse(raw);
          } catch (parseError) {
            data = null;
          }
        }

        if (!response.ok || !data || !data.ok) {
          let message = data?.message || 'Izoh bilan ishlashda xatolik';
          let toastLevel = data?.toast_type;

          if (data?.errors && typeof data.errors === 'object') {
            const keys = Object.keys(data.errors);
            if (keys.length) {
              const first = data.errors[keys[0]];
              if (Array.isArray(first) && first[0]) {
                message = first[0];
              } else if (typeof first === 'string') {
                message = first;
              }
            }
          }

          if (!toastLevel || (toastLevel !== 'warning' && toastLevel !== 'error' && toastLevel !== 'success')) {
            if (response.status === 401 || response.status === 403) {
              toastLevel = 'warning';
            } else if (response.status === 422) {
              toastLevel = 'warning';
            } else {
              toastLevel = 'error';
            }
          }

          if (!data) {
            if (response.status === 404) {
              message = "Izoh yuborish yo'li topilmadi. Sahifani yangilang.";
              toastLevel = 'error';
            } else if (response.status === 419) {
              message = 'Sessiya tugagan. Sahifani yangilang.';
              toastLevel = 'warning';
            } else if (response.status === 422) {
              message = "Kiritilgan ma'lumotlarni tekshirib qayta urinib ko'ring.";
              toastLevel = 'warning';
            }
          }

          window.showToast?.(message, toastLevel);
          return;
        }

        const toastType = data.toast_type || 'success';
        window.showToast?.(data.message || 'OK', toastType);

        if (data.ok && (toastType === 'success' || !toastType)) {
          playPrimeSuccess();
          playPrimeConfetti(event.clientX || window.innerWidth / 2, event.clientY || window.innerHeight / 2);
        }

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

        if (comment.is_approved === false) {
          form.reset();
          const details = form.closest('details');
          if (details) details.open = false;
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
              <form class="js-comment-form js-comment-delete-form" action="${destroyUrl}" method="POST" data-comment-id="${comment.id}" data-confirm="Izohni o'chirmoqchimisiz?" data-confirm-title="Izohni o'chirish" data-confirm-variant="danger" data-confirm-ok="O'chirish">
                <input type="hidden" name="_token" value="${escapeHtml(csrfToken)}" />
                <input type="hidden" name="_method" value="DELETE" />
                <button type="submit" class="btn btn-sm comment-delete-btn">
                  <i class="fa-solid fa-trash" style="margin-right: 8px;"></i> O'chirish
                </button>
              </form>
            `
          : '';

        const donorTheme = String(comment.donor_theme || comment.donor_rank || '');
        const donorRankKeys = ['supporter', 'premium', 'vip'];
        const donorThemeClass = donorTheme && donorRankKeys.indexOf(donorTheme) !== -1
          ? ` comment-card--donor comment-card--donor-${escapeHtml(donorTheme)}`
          : '';
        const donorBadgeHtml = comment.donor_badge || '';
        let authorStyle = '';
        if (comment.donor_color && /^#[0-9a-f]{3,8}$/i.test(String(comment.donor_color))) {
          authorStyle += `color:${comment.donor_color};`;
        }
        if (comment.name_font_weight && /^(600|700|800)$/.test(String(comment.name_font_weight))) {
          authorStyle += `font-weight:${comment.name_font_weight};`;
        }
        const authorStyleAttr = authorStyle ? ` style="${authorStyle}"` : '';

        function buildReplyLi() {
          const staffCardCls = roleKey === 'super_admin'
            ? ' comment-card--super-admin'
            : roleKey === 'admin'
              ? ' comment-card--admin'
              : roleKey === 'moderator'
                ? ' comment-card--moderator'
                : roleKey === 'teacher'
                  ? ' comment-card--teacher'
                  : '';

          return `
            <article class="comment-card reveal comment-item-reply${staffCardCls}${donorThemeClass}" data-comment-id="${escapeHtml(comment.id)}">
              ${buildCommentAvatarHtml(comment)}
              <div class="comment-body">
                <div class="comment-meta">
                  <strong${authorStyleAttr}>${escapeHtml(comment.author_name || 'Mehmon')}</strong>
                  ${donorBadgeHtml}
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
                : roleKey === 'teacher'
                  ? ' comment-card--teacher'
                  : '';

          return `
            <article class="comment-card reveal${staffCardCls}${donorThemeClass}" data-comment-id="${escapeHtml(comment.id)}">
              ${buildCommentAvatarHtml(comment)}
              <div class="comment-body">
                <div class="comment-meta">
                  <strong${authorStyleAttr}>${escapeHtml(comment.author_name || 'Mehmon')}</strong>
                  ${donorBadgeHtml}
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
        window.showToast?.(error && error.message ? error.message : 'Izoh yuborishda xatolik', 'error');
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

    // 3. Advanced 3D Tilt Effect (Removed as per user request)

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
    var clearBtn = document.getElementById('chat-clear-btn');
    var messagesEl = document.getElementById('chat-messages');
    var form = document.getElementById('chat-form');
    var input = document.getElementById('chat-input');
    var sendBtn = document.getElementById('chat-send-btn');
    var stickerButtons = panel.querySelectorAll('[data-chat-sticker]');
    var badge = document.getElementById('chat-badge');
    var composeStatus = document.getElementById('chat-compose-status');
    var composeStatusText = document.getElementById('chat-compose-status-text');

    var chatDisabledPanel = document.getElementById('chat-disabled-panel');
    var chatPanelMain = document.getElementById('chat-panel-main');
    var chatDisabledText = document.getElementById('chat-disabled-panel-text');
    var chatEnabled = widget.getAttribute('data-chat-enabled') !== '0';

    var chatStatusUrl = widget.getAttribute('data-chat-status-url');
    var messagesUrl = widget.getAttribute('data-chat-messages-url');
    var sendUrl = widget.getAttribute('data-chat-send-url');
    var deleteUrl = widget.getAttribute('data-chat-delete-url');
    var clearUrl = widget.getAttribute('data-chat-clear-url');
    var blockUrl = widget.getAttribute('data-chat-block-url');
    var groupsUrl = widget.getAttribute('data-chat-groups-url');
    var groupJoinBase = widget.getAttribute('data-chat-group-join-base');
    var groupRequestsBase = widget.getAttribute('data-chat-group-requests-base');
    var chatPreviewBase = widget.getAttribute('data-chat-user-preview-base');
    var csrf = widget.getAttribute('data-csrf');
    var currentUserId = String(widget.getAttribute('data-user-id') || '');
    var lastId = 0;
    var unreadCount = 0;
    var activeChannel = 'global'; // Always start fresh on page load
    var selectedGroup = null;
    var groupsLoaded = false;
    var chatTexts = parseJson(widget.getAttribute('data-chat-texts'), {});

    var channelTabs = panel.querySelectorAll('[data-chat-channel]');
    var chatTitleLabel = document.getElementById('chat-panel-title-label');
    var chatChannelLabel = document.getElementById('chat-channel-label');
    var groupShell = document.getElementById('chat-group-shell');
    var groupList = document.getElementById('chat-group-list');
    var currentGroupName = document.getElementById('chat-current-group-name');
    var currentGroupDescription = document.getElementById('chat-current-group-description');
    var groupJoinBtn = document.getElementById('chat-group-join-btn');
    var groupRequestsBtn = document.getElementById('chat-group-requests-btn');
    var pendingCountEl = document.getElementById('chat-group-pending-count');
    var groupRequestsPanel = document.getElementById('chat-group-requests-panel');
    var groupRequestsList = document.getElementById('chat-group-requests-list');
    var groupLeaveBtn = document.getElementById('chat-group-leave-btn');
    var groupMembersBtn = document.getElementById('chat-group-members-btn');
    var groupSettingsBtn = document.getElementById('chat-group-settings-btn');
    var groupPrivacyBadge = document.getElementById('chat-group-privacy-badge');
    var groupSubpanel = document.getElementById('chat-group-subpanel');
    var groupSubpanelTitle = document.getElementById('chat-group-subpanel-title');
    var groupSubpanelBody = document.getElementById('chat-group-subpanel-body');
    var groupSubpanelBackBtn = document.getElementById('chat-group-subpanel-back');
    var groupCreateBtn = document.getElementById('chat-group-create-btn');
    var groupCreateModal = document.getElementById('prime-group-create-modal');
    var isOpen = false;
    var isSending = false;
    var pollTimer = null;
    var canClearAll = false;
    var lastReadStorageKey = 'prime-chat-last-read:' + currentUserId;
    var lastReadId = getStoredChatLastReadId();

    function getStoredChatLastReadId() {
      try {
        return Math.max(0, parseInt(window.localStorage.getItem(lastReadStorageKey) || '0', 10) || 0);
      } catch (e) {
        return 0;
      }
    }

    function getChatText(key, fallback) {
      if (chatTexts && Object.prototype.hasOwnProperty.call(chatTexts, key)) {
        return String(chatTexts[key]);
      }
      return String(fallback || '');
    }

    function setStoredChatLastReadId(value) {
      var normalized = Math.max(0, parseInt(value, 10) || 0);
      lastReadId = normalized;
      try {
        window.localStorage.setItem(lastReadStorageKey, String(normalized));
      } catch (e) {
        /* localStorage bo'lmasa ham chat ishlayveradi */
      }
    }

    function setChatEnabledState(enabled, message) {
      chatEnabled = !!enabled;
      widget.setAttribute('data-chat-enabled', chatEnabled ? '1' : '0');

      if (message && chatDisabledText) {
        chatDisabledText.textContent = message;
      }

      if (activeChannel === 'group') {
        if (chatDisabledPanel) {
          chatDisabledPanel.hidden = true;
        }
        if (chatPanelMain) {
          chatPanelMain.hidden = false;
        }
      } else if (chatDisabledPanel && chatPanelMain) {
        chatPanelMain.hidden = !chatEnabled;
        chatDisabledPanel.hidden = chatEnabled;
      }

      if (fullBtn) {
        fullBtn.style.display = chatEnabled ? '' : 'none';
      }
    }

    function refreshChatAvailability() {
      if (activeChannel === 'group' || !chatStatusUrl) {
        return Promise.resolve(true);
      }

      return fetch(chatStatusUrl, {
        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
        credentials: 'same-origin',
      })
        .then(function (r) {
          if (!r.ok) return null;
          return r.json();
        })
        .then(function (data) {
          if (!data) return chatEnabled;

          if (data.chat_disabled) {
            setChatEnabledState(false, data.disabled_message || widget.getAttribute('data-chat-disabled-message'));
            stopPolling();
            return false;
          }

          setChatEnabledState(true);
          return true;
        })
        .catch(function () {
          return chatEnabled;
        });
    }

    function syncChatBadge() {
      if (!badge) return;

      if (unreadCount > 0) {
        badge.textContent = unreadCount > 99 ? '99+' : String(unreadCount);
        badge.hidden = false;
        return;
      }

      badge.textContent = '0';
      badge.hidden = true;
    }

    function countUnreadMessages(msgs, thresholdId) {
      var minId = Math.max(0, parseInt(thresholdId, 10) || 0);
      return msgs.reduce(function (count, msg) {
        if (!msg || msg.is_mine) return count;
        return (parseInt(msg.id, 10) || 0) > minId ? count + 1 : count;
      }, 0);
    }

    function markChatAsRead(uptoId) {
      var normalized = Math.max(lastId, Math.max(0, parseInt(uptoId, 10) || 0));
      if (normalized > lastReadId) {
        setStoredChatLastReadId(normalized);
      }
      unreadCount = 0;
      syncChatBadge();
    }

    function syncChatAdminActions() {
      if (!clearBtn) return;
      clearBtn.hidden = !canClearAll;
      clearBtn.disabled = !canClearAll;
    }

    function renderGroupList(groups) {
      if (!groupList) return;
      if (!groups || !groups.length) {
        groupList.innerHTML = '<div class="chat-empty-message" style="text-align:center;padding:2rem 1rem;">'
          + '<i class="fa-solid fa-users" style="font-size:2.5rem;opacity:0.25;display:block;margin-bottom:0.75rem;"></i>'
          + '<span>' + getChatText('no_groups', 'Hozircha guruhlar yo\'q. Birinchi bo\'lib guruh oching!') + '</span>'
          + '</div>';
        return;
      }
      var html = groups.map(function (group) {
        var active = selectedGroup && selectedGroup.id === group.id ? ' is-active' : '';
        var statusLabel = getChatText('group_status_join', 'Join');
        if (group.is_owner) {
          statusLabel = getChatText('group_status_owner', 'Owner');
        } else if (group.is_member) {
          statusLabel = getChatText('group_status_member', 'Member');
        } else if (group.request_status === 'pending') {
          statusLabel = getChatText('group_status_pending', 'Pending');
        }

        return '<button type="button" class="chat-group-item' + active + '" data-chat-group-id="' + group.id + '">' +
          '<span class="chat-group-item-details">' +
          '<strong>' + escChatHtml(group.name) + '</strong>' +
          '<span class="chat-group-item-description">' + escChatHtml(group.description || '') + '</span>' +
          '</span>' +
          '<span class="chat-group-item-status">' + escChatHtml(statusLabel) + '</span>' +
          '</button>';
      }).join('');

      groupList.innerHTML = html;
      var buttons = groupList.querySelectorAll('[data-chat-group-id]');
      buttons.forEach(function (button) {
        button.addEventListener('click', function () {
          var id = Number(button.getAttribute('data-chat-group-id'));
          var group = groups.find(function (g) { return g.id === id; });
          if (group) {
            selectGroup(group);
          }
        });
      });
    }

    function updateGroupControls() {
      if (!groupShell) return;
      var groupMeta = document.getElementById('chat-group-meta');
      var createBar = document.getElementById('chat-group-create-bar');
      var composePanel = document.getElementById('chat-form');

      if (!selectedGroup) {
        currentGroupName.textContent = getChatText('select_group', 'Guruh tanlang');
        currentGroupDescription.textContent = '';
        if(groupPrivacyBadge) groupPrivacyBadge.textContent = '';
        if(groupJoinBtn) groupJoinBtn.hidden = true;
        if(groupRequestsBtn) groupRequestsBtn.hidden = true;
        if(groupLeaveBtn) groupLeaveBtn.hidden = true;
        if(groupMembersBtn) groupMembersBtn.hidden = true;
        if(groupSettingsBtn) groupSettingsBtn.hidden = true;
        if(pendingCountEl) pendingCountEl.textContent = '0';
        if(groupRequestsPanel) groupRequestsPanel.hidden = true;

        if (groupMeta) groupMeta.hidden = true;
        if (createBar) createBar.hidden = false;
        if (groupList) groupList.hidden = false;
        if (messagesEl) messagesEl.hidden = true;
        if (composePanel) composePanel.hidden = true;

        return;
      }

      currentGroupName.textContent = selectedGroup.name || getChatText('group_list_title', 'Guruh');
      currentGroupDescription.textContent = selectedGroup.description || '';
      if(groupPrivacyBadge) {
        groupPrivacyBadge.textContent = selectedGroup.privacy === 'closed' ? '🔒' : '🔓';
      }

      var isOwner = !!selectedGroup.is_owner;
      var isMember = !!selectedGroup.is_member;
      var pending = selectedGroup.request_status === 'pending';
      var canManage = !!selectedGroup.can_manage;

      if(groupJoinBtn) {
        groupJoinBtn.hidden = isOwner || isMember;
        groupJoinBtn.disabled = pending;
        groupJoinBtn.textContent = pending ? getChatText('group_join_sent', 'So‘rov yuborildi') : (isOwner ? getChatText('group_status_you_own', 'Siz egaliksiz') : getChatText('join_group', 'Guruhga qo‘shilish'));
      }

      if(groupLeaveBtn) groupLeaveBtn.hidden = !isMember && !isOwner;
      if(groupMembersBtn) groupMembersBtn.hidden = !isMember && !isOwner && !canManage;
      if(groupSettingsBtn) groupSettingsBtn.hidden = !selectedGroup.can_edit;

      if(groupRequestsBtn) groupRequestsBtn.hidden = !canManage;      if (pendingCountEl) pendingCountEl.textContent = String(selectedGroup.pending_requests_count || 0);
      if (groupRequestsPanel) groupRequestsPanel.hidden = true;

      if (groupMeta) groupMeta.hidden = false;
      if (createBar) createBar.hidden = true;
      if (groupList) groupList.hidden = true;
      if (messagesEl) messagesEl.hidden = false;
      if (composePanel) composePanel.hidden = false;
    }

    function selectGroup(group) {
      selectedGroup = group;
      lastId = 0;
      messagesEl.innerHTML = '';
      renderGroupList(groupsData);
      updateGroupControls();
      if (isOpen) {
        loadMessages().then(function () {
          scrollDown();
        });
      }
    }

    function setActiveChannel(channel) {
      var prevChannel = activeChannel;
      activeChannel = channel === 'group' ? 'group' : 'global';

      // Update tab styles
      if (channelTabs && channelTabs.length) {
        channelTabs.forEach(function (tab) {
          tab.classList.toggle('chat-panel-tab--active', tab.getAttribute('data-chat-channel') === activeChannel);
        });
      }

      var composePanel = document.getElementById('chat-form');

      // Reset messages when switching channels
      lastId = 0;
      messagesEl.innerHTML = '';

      if (activeChannel === 'group') {
        // Show group shell, hide message composer
        if (groupShell) groupShell.hidden = false;
        // Reset selected group only when switching FROM global to group
        if (prevChannel !== 'group') { selectedGroup = null; }
        updateGroupControls();
        if (chatTitleLabel) chatTitleLabel.textContent = getChatText('group_list_title', 'Guruhlar');
        if (chatChannelLabel) chatChannelLabel.textContent = getChatText('group_chat', 'Guruh chat');
        // Load groups if not loaded yet, or re-render if already loaded
        if (!groupsLoaded) {
          loadGroups();
        } else {
          renderGroupList(groupsData);
          updateGroupControls();
        }
        // Only load group messages if a group is selected
        if (isOpen && selectedGroup) {
          loadMessages().then(function () { scrollDown(); });
        }
      } else {
        // Show global chat
        if (groupShell) groupShell.hidden = true;
        if (groupRequestsPanel) groupRequestsPanel.hidden = true;
        if (messagesEl) messagesEl.hidden = false;
        if (composePanel) composePanel.hidden = false;
        if (chatTitleLabel) chatTitleLabel.textContent = getChatText('global_chat', 'Global chat');
        if (chatChannelLabel) chatChannelLabel.textContent = getChatText('global_chat', 'Global chat');
        // Load global messages
        if (isOpen) {
          loadMessages().then(function () { scrollDown(); });
        }
      }
    }

    function syncChannelState() {
      if (!groupShell) return;
      if (activeChannel === 'group') {
        groupShell.hidden = false;
        updateGroupControls();
      } else {        if (groupRequestsPanel) groupRequestsPanel.hidden = true;
      }
    }

    function loadGroups() {
      if (!groupsUrl) return Promise.resolve([]);
      return fetch(groupsUrl, {
        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
        credentials: 'same-origin',
      })
        .then(function (r) {
          if (!r.ok) throw new Error(getChatText('group_load_failed', 'Guruhlar yuklanmadi.'));
          return r.json();
        })
        .then(function (data) {
          groupsData = Array.isArray(data.groups) ? data.groups : [];
          groupsLoaded = true;
          if (selectedGroup) {
            selectedGroup = groupsData.find(function (g) {
              return g.id === selectedGroup.id;
            }) || null;
          }
          renderGroupList(groupsData);
          updateGroupControls();
          return groupsData;
        })
        .catch(function () {
          groupsData = [];
          groupsLoaded = true;
          renderGroupList([]);
          updateGroupControls();
          return [];
        });
    }

    function openGroupSubpanel(title, type) {
      if (!groupSubpanel || !groupSubpanelTitle || !groupSubpanelBody) return;
      groupSubpanelTitle.textContent = title;
      groupSubpanelBody.innerHTML = '<div class="chat-loading"><i class="fa-solid fa-spinner fa-spin"></i></div>';
      groupSubpanel.hidden = false;
      groupSubpanel.setAttribute('data-subpanel-type', type);
      groupList.hidden = true;
    }

    function closeGroupSubpanel() {
      if (!groupSubpanel) return;
      groupSubpanel.hidden = true;
      groupSubpanelBody.innerHTML = '';
      groupList.hidden = false;
      groupSubpanel.removeAttribute('data-subpanel-type');
    }

    function renderGroupRequests(requests) {
      if (!groupSubpanelBody || groupSubpanel.getAttribute('data-subpanel-type') !== 'requests') return;
      if (!requests.length) {
        groupSubpanelBody.innerHTML = '<div class="chat-empty-message">' + getChatText('group_requests_empty', 'Hech qanday so‘rov yo‘q.') + '</div>';
        return;
      }
      groupSubpanelBody.innerHTML = requests.map(function (item) {
        return '<div class="chat-group-request-item">'
          + '<div class="chat-group-request-meta">'
          + (item.user_avatar ? '<img src="' + escAttr(item.user_avatar) + '" alt="" class="chat-group-request-avatar" />' : '<span class="chat-group-request-avatar-placeholder">' + escChatHtml((item.user_name || '?').charAt(0).toUpperCase()) + '</span>')
          + '<div>'
          + '<strong>' + escChatHtml(item.user_name || getChatText('group_request_unknown', 'Noma’lum')) + '</strong>'
          + '<span>' + escChatHtml(item.created_at || '') + '</span>'
          + '</div>'
          + '</div>'
          + '<div class="chat-group-request-actions">'
          + '<button type="button" class="chat-panel-btn chat-group-request-accept" data-request-id="' + item.id + '"><i class="fa-solid fa-check"></i></button>'
          + '<button type="button" class="chat-panel-btn chat-group-request-reject" data-request-id="' + item.id + '"><i class="fa-solid fa-xmark"></i></button>'
          + '</div>'
          + '</div>';
      }).join('');
      groupSubpanelBody.querySelectorAll('[data-request-id]').forEach(function (button) {
        button.addEventListener('click', function () {
          var requestId = Number(button.getAttribute('data-request-id'));
          if (button.classList.contains('chat-group-request-accept')) {
            respondToGroupRequest(requestId, 'accept');
          } else {
            respondToGroupRequest(requestId, 'reject');
          }
        });
      });
    }

    function loadGroupRequests() {
      if (!groupJoinBase || !selectedGroup) return Promise.resolve([]);
      return fetch(groupJoinBase + '/' + selectedGroup.id + '/requests', {
        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
        credentials: 'same-origin',
      })
        .then(function (r) {
          if (!r.ok) throw new Error(getChatText('group_requests_failed', 'So‘rovlar yuklanmadi.'));
          return r.json();
        })
        .then(function (data) {
          var requests = Array.isArray(data.requests) ? data.requests : [];
          renderGroupRequests(requests);
          return requests;
        })
        .catch(function () {
          if (groupSubpanelBody && groupSubpanel.getAttribute('data-subpanel-type') === 'requests') {
            groupSubpanelBody.innerHTML = '<div class="chat-empty-message">' + getChatText('group_requests_failed', 'So‘rovlar yuklanmadi.') + '</div>';
          }
          return [];
        });
    }

    function respondToGroupRequest(requestId, action) {
      if (!groupJoinBase || !selectedGroup) return;
      var url = groupJoinBase + '/' + selectedGroup.id + '/requests/' + requestId + '/' + action;
      return fetch(url, {
        method: 'POST',
        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
        credentials: 'same-origin',
      })
        .then(function (r) {
          if (!r.ok) throw new Error(getChatText('chat_action_failed', 'Amal bajarilmadi'));
          return r.json();
        })
        .then(function (data) {
          if (data.ok) {
            loadGroupRequests();
            loadGroups();
          }
        })
        .catch(function (err) {
          if (window.showToast) {
            window.showToast(err.message || getChatText('chat_action_failed', 'Xato yuz berdi'), 'error');
          }
        });
    }

    function leaveGroupRequest() {
      if (!groupJoinBase || !selectedGroup) return;
      if (confirm(getChatText('confirm_leave_group', 'Siz rostdan ham bu guruhdan chiqmoqchimisiz?'))) {
        fetch(groupJoinBase + '/' + selectedGroup.id + '/leave', {
          method: 'POST',
          headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrf,
          },
          credentials: 'same-origin',
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
          if (data.ok) {
            selectedGroup = null;
            loadGroups();
          } else if (data.error && window.showToast) {
            window.showToast(data.error, 'error');
          }
        });
      }
    }

    function renderGroupMembers(members) {
      if (!groupSubpanelBody || groupSubpanel.getAttribute('data-subpanel-type') !== 'members') return;
      if (!members.length) {
        groupSubpanelBody.innerHTML = '<div class="chat-empty-message">Hech qanday a’zo yo‘q.</div>';
        return;
      }
      groupSubpanelBody.innerHTML = members.map(function (item) {
        var isMe = String(item.user_id) === currentUserId;
        var roleBadge = item.is_owner ? '<span class="chat-group-role-badge owner">Ega</span>' : (item.role === 'admin' ? '<span class="chat-group-role-badge admin">Admin</span>' : '');
        var actions = '';
        if (selectedGroup.can_edit && !item.is_owner && !isMe) {
          actions = '<div class="chat-group-request-actions">'
            + '<button class="chat-panel-btn kick-member" data-member-id="'+item.id+'" title="Chetlatish"><i class="fa-solid fa-user-slash"></i></button>'
            + '</div>';
        }

        return '<div class="chat-group-request-item">'
          + '<div class="chat-group-request-meta">'
          + (item.user_avatar ? '<img src="' + escAttr(item.user_avatar) + '" alt="" class="chat-group-request-avatar" />' : '<span class="chat-group-request-avatar-placeholder">' + escChatHtml((item.user_name || '?').charAt(0).toUpperCase()) + '</span>')
          + '<div>'
          + '<strong>' + escChatHtml(item.user_name) + ' ' + roleBadge + '</strong>'
          + '</div>'
          + '</div>'
          + actions
          + '</div>';
      }).join('');

      groupSubpanelBody.querySelectorAll('.kick-member').forEach(function(btn) {
        btn.addEventListener('click', function() {
          if(!confirm("A'zoni chetlatmoqchimisiz?")) return;
          var mid = btn.getAttribute('data-member-id');
          fetch(groupJoinBase + '/' + selectedGroup.id + '/members/' + mid, {
            method: 'DELETE',
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
            credentials: 'same-origin',
          }).then(function(r){ return r.json(); }).then(function(d){
             if(d.ok) loadGroupMembers();
          });
        });
      });
    }

    function loadGroupMembers() {
      if (!groupJoinBase || !selectedGroup) return;
      return fetch(groupJoinBase + '/' + selectedGroup.id + '/members', {
        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
        credentials: 'same-origin',
      })
      .then(function(r) { return r.json(); })
      .then(function(data) {
         renderGroupMembers(data.members || []);
      })
      .catch(function() {
         if (groupSubpanelBody && groupSubpanel.getAttribute('data-subpanel-type') === 'members') {
            groupSubpanelBody.innerHTML = '<div class="chat-empty-message">Xatolik.</div>';
         }
      });
    }

    function renderGroupSettings() {
      if (!groupSubpanelBody || groupSubpanel.getAttribute('data-subpanel-type') !== 'settings') return;
      groupSubpanelBody.innerHTML = '<div class="chat-group-settings-form">'
        + '<div class="chat-group-settings-field"><label>Guruh nomi:</label><input type="text" id="cg-edit-name" value="'+escAttr(selectedGroup.name)+'" class="prime-group-create__input"/></div>'
        + '<div class="chat-group-settings-field"><label>Tavsif:</label><textarea id="cg-edit-desc" class="prime-group-create__textarea">'+escChatHtml(selectedGroup.description || '')+'</textarea></div>'
        + '<div class="chat-group-settings-field"><label>Maxfiylik (Yopiq bo\'lsa tasdiqlash kerak bo\'ladi):</label><select id="cg-edit-privacy" class="prime-group-create__input"><option value="open" '+(selectedGroup.privacy==='open'?'selected':'')+'>Ochiq</option><option value="closed" '+(selectedGroup.privacy==='closed'?'selected':'')+'>Yopiq</option></select></div>'
        + '<div class="chat-group-settings-actions"><button type="button" class="prime-group-create__btn prime-group-create__btn--primary" id="cg-save-btn">Saqlash</button>'
        + '<button type="button" class="prime-group-create__btn prime-group-create__btn--ghost" style="color:var(--danger, red); margin-top:8px" id="cg-del-btn">Guruhni O\'chirish</button></div>'
        + '</div>';

      document.getElementById('cg-save-btn').addEventListener('click', function() {
        var n = document.getElementById('cg-edit-name').value;
        var d = document.getElementById('cg-edit-desc').value;
        var p = document.getElementById('cg-edit-privacy').value;
        fetch(groupJoinBase + '/' + selectedGroup.id, {
          method: 'PUT',
          headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
          credentials: 'same-origin',
          body: JSON.stringify({ name: n, description: d, privacy: p })
        }).then(function(r) { return r.json(); }).then(function(res) {
          if(res.ok) {
            if(window.showToast) window.showToast('Saqlandi!', 'success');
            loadGroups();
            closeGroupSubpanel();
          }
        });
      });

      document.getElementById('cg-del-btn').addEventListener('click', function() {
        if(!confirm("Guruhni butunlay o'chirib yubormoqchimisiz?")) return;
        fetch(groupJoinBase + '/' + selectedGroup.id, {
          method: 'DELETE',
          headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
          credentials: 'same-origin',
        }).then(function(r) { return r.json(); }).then(function(res) {
          if(res.ok) {
            selectedGroup = null;
            loadGroups();
            closeGroupSubpanel();
          }
        });
      });
    }

    function openGroupCreateModal() {
      if(groupCreateModal) {
        var nameInput = document.getElementById('prime-group-create-name');
        var descInput = document.getElementById('prime-group-create-desc');
        if (nameInput) nameInput.value = '';
        if (descInput) descInput.value = '';

        var toggleBtns = groupCreateModal.querySelectorAll('.prime-group-create__toggle-btn');
        toggleBtns.forEach(function(btn) {
          if (btn.getAttribute('data-privacy') === 'closed') {
            btn.classList.add('prime-group-create__toggle-btn--active');
          } else {
            btn.classList.remove('prime-group-create__toggle-btn--active');
          }
        });

        groupCreateModal.classList.add('is-active');
        groupCreateModal.setAttribute('aria-hidden', 'false');
      }
    }

    function closeGroupCreateModal() {
      if(groupCreateModal) {
        groupCreateModal.classList.remove('is-active');
        groupCreateModal.setAttribute('aria-hidden', 'true');
      }
    }

    function submitGroupCreate() {
      var nameInput = document.getElementById('prime-group-create-name');
      var descInput = document.getElementById('prime-group-create-desc');
      var activePrivacyBtn = groupCreateModal ? groupCreateModal.querySelector('.prime-group-create__toggle-btn--active') : null;
      var privacy = activePrivacyBtn ? activePrivacyBtn.getAttribute('data-privacy') : 'closed';

      var name = nameInput ? nameInput.value.trim() : '';
      var desc = descInput ? descInput.value.trim() : '';

      if(name.length < 2) {
        if (window.showToast) window.showToast("Guruh nomi kamida 2ta harf bo'lishi kerak", 'error');
        return;
      }

      fetch(groupJoinBase, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': csrf,
        },
        credentials: 'same-origin',
        body: JSON.stringify({
          name: name,
          description: desc,
          privacy: privacy
        })
      })
      .then(function(r) { return r.json(); })
      .then(function(data) {
        if(data.ok) {
          closeGroupCreateModal();
          if (nameInput) nameInput.value = '';
          if (descInput) descInput.value = '';
          loadGroups();
          if (window.showToast) window.showToast("Guruh muvaffaqiyatli yaratildi", 'success');
        } else {
          if (window.showToast) {
            window.showToast(data.error || getChatText('chat_action_failed', 'Xato yuz berdi'), 'error');
          } else {
            alert(data.error || 'Xato yuz berdi');
          }
        }
      })
      .catch(function(err) {
        if (window.showToast) {
          window.showToast(err.message || getChatText('chat_action_failed', 'Xato yuz berdi'), 'error');
        } else {
          alert('Xato yuz berdi');
        }
      });
    }

    function requestJoinSelectedGroup() {
      if (!groupJoinBase || !selectedGroup) return;
      fetch(groupJoinBase + '/' + selectedGroup.id + '/join', {
        method: 'POST',
        headers: {
          'Accept': 'application/json',
          'X-CSRF-TOKEN': csrf,
        },
        credentials: 'same-origin',
      })
        .then(function (r) {
          if (!r.ok) {
            throw new Error(getChatText('group_join_failed', 'So‘rov yuborilmadi'));
          }
          return r.json();
        })
        .then(function (data) {
          if (data.ok) {
            if (data.pending) {
              selectedGroup.request_status = 'pending';
              updateGroupControls();
              renderGroupList(groupsData);
              if (window.showToast) {
                window.showToast(getChatText('group_join_sent', 'So‘rovingiz yuborildi.'), 'success');
              }
            }
          }
        })
        .catch(function (err) {
          if (window.showToast) {
            window.showToast(err.message || getChatText('chat_action_failed', 'Xato yuz berdi'), 'error');
          }
        });
    }

    var groupsData = [];

    function resetChatComposeState() {
      isSending = false;
      if (input) {
        input.disabled = false;
        input.removeAttribute('aria-busy');
      }
      if (sendBtn) {
        sendBtn.disabled = false;
        sendBtn.removeAttribute('aria-busy');
      }
      stickerButtons.forEach(function (btn) {
        btn.disabled = false;
      });
      syncComposeState();
    }

    function syncDockState() {
      document.body.classList.toggle('chat-panel-open', isOpen);
    }

    function positionPanel() {
      var rect = widget.getBoundingClientRect();
      var vw = window.innerWidth;
      var vh = window.innerHeight;
      var pw = Math.min(360, vw - 16);
      var ph = Math.min(480, vh - 16);
      var gap = 12;

      var cx = rect.left + rect.width / 2;
      var cy = rect.top + rect.height / 2;

      var openRight = cx < vw / 2;
      var openUp = cy > vh / 2;

      var left, top, origin;

      if (openRight) {
        left = Math.min(rect.left, vw - pw - 8);
      } else {
        left = Math.max(8, rect.right - pw);
      }

      if (openUp) {
        top = Math.max(8, rect.top - ph - gap);
        origin = (openRight ? 'bottom left' : 'bottom right');
      } else {
        top = rect.bottom + gap;
        origin = (openRight ? 'top left' : 'top right');
      }

      if (top + ph > vh - 8) top = vh - ph - 8;
      if (top < 8) top = 8;
      if (left + pw > vw - 8) left = vw - pw - 8;
      if (left < 8) left = 8;

      panel.style.left = left + 'px';
      panel.style.top = top + 'px';
      panel.style.setProperty('--chat-origin', origin);
    }

    function openPanel() {
      if (typeof window.primeCloseAiPanel === 'function') {
        window.primeCloseAiPanel();
      }
      positionPanel();
      panel.hidden = false;
      panel.classList.remove('is-closing');
      panel.classList.add('is-opening');
      widget.classList.add('is-open');
      isOpen = true;
      resetChatComposeState();
      syncDockState();
      markChatAsRead(lastId);

      if (!chatEnabled && chatDisabledPanel && chatPanelMain) {
        setChatEnabledState(false, widget.getAttribute('data-chat-disabled-message') || getChatText('chat_disabled_default', 'Global chat vaqtincha o‘chirilgan.'));

        if (!widget.getAttribute('data-chat-disabled-message')) {
          setChatEnabledState(false, getChatText('chat_disabled_default', 'Global chat vaqtincha o‘chirilgan.'));
        }

        refreshChatAvailability().then(function () {
          if (!chatEnabled) return;
          return loadMessages().then(function () {
            startPolling();
            markChatAsRead(lastId);
            scrollDown();
            if (input) input.focus();
            syncComposeState();
          });
        }).finally(function () {
          setTimeout(function () {
            panel.classList.remove('is-opening');
          }, 520);
        });

        return;
      }

      setChatEnabledState(true);

      if (activeChannel === 'group') {
        // Ensure group UI is visible
        if (groupShell) groupShell.hidden = false;
        var composePanel = document.getElementById('chat-form');
        if (!selectedGroup) {
          // No group selected - show group list
          if (messagesEl) messagesEl.hidden = true;
          if (composePanel) composePanel.hidden = true;
        }
        updateGroupControls();
        if (!groupsLoaded) {
          loadGroups().finally(function () {
            if (input) input.focus();
            syncComposeState();
            setTimeout(function () { panel.classList.remove('is-opening'); }, 520);
          });
        } else {
          renderGroupList(groupsData);
          updateGroupControls();
          if (selectedGroup) {
            loadMessages().finally(function () {
              markChatAsRead(lastId);
              scrollDown();
            });
          }
          if (input) input.focus();
          syncComposeState();
          setTimeout(function () { panel.classList.remove('is-opening'); }, 520);
        }
      } else {
        // Global chat
        if (groupShell) groupShell.hidden = true;
        if (messagesEl) messagesEl.hidden = false;
        var composePanelG = document.getElementById('chat-form');
        if (composePanelG) composePanelG.hidden = false;
        loadMessages().finally(function () {
          markChatAsRead(lastId);
          scrollDown();
          if (input) input.focus();
          syncComposeState();
          setTimeout(function () { panel.classList.remove('is-opening'); }, 520);
        });
      }
    }

    function closePanel() {
      if (!isOpen) return;
      panel.classList.add('is-closing');
      widget.classList.remove('is-open');
      widget.classList.add('is-bubble-return');
      isOpen = false;
      resetChatComposeState();
      syncDockState();
      setComposeState('idle');
      setTimeout(function () {
        widget.classList.remove('is-bubble-return');
      }, 460);
      setTimeout(function () {
        panel.hidden = true;
        panel.classList.remove('is-closing', 'is-fullscreen', 'is-opening');
        panel.classList.remove('is-fullscreen-enter', 'is-fullscreen-exit');
        document.body.classList.remove('chat-fullscreen-active');
        panel.style.removeProperty('left');
        panel.style.removeProperty('top');
      }, 440);
    }

    function toggleFullscreen() {
      if (!fullBtn) return;
      var icon = fullBtn.querySelector('i');
      if (!panel.classList.contains('is-fullscreen')) {
        panel.classList.remove('is-fullscreen-exit');
        panel.classList.add('is-fullscreen', 'is-fullscreen-enter');
        document.body.classList.add('chat-fullscreen-active');
        setTimeout(function () {
          panel.classList.remove('is-fullscreen-enter');
        }, 360);
        icon.className = 'fa-solid fa-compress';
        fullBtn.title = getChatText('chat_fullscreen_exit', 'Kichiklashtirish');
      } else {
        panel.classList.remove('is-fullscreen-enter');
        panel.classList.add('is-fullscreen-exit');
        setTimeout(function () {
          panel.classList.remove('is-fullscreen', 'is-fullscreen-exit');
          document.body.classList.remove('chat-fullscreen-active');
          panel.style.removeProperty('left');
          panel.style.removeProperty('top');
          if (isOpen) positionPanel();
        }, 320);
        icon.className = 'fa-solid fa-expand';
        fullBtn.title = getChatText('chat_fullscreen_enter', "To'liq ekran");
      }
    }

    function scrollDown() {
      setTimeout(function () { messagesEl.scrollTop = messagesEl.scrollHeight; }, 50);
    }

    function setComposeState(state) {
      var typing = state === 'typing';
      var sending = state === 'sending';

      widget.classList.toggle('is-typing', typing);
      widget.classList.toggle('is-sending', sending);
      form.classList.toggle('is-sending', sending);

      if (!composeStatus || !composeStatusText) return;

      if (state === 'idle' || !isOpen) {
        composeStatus.hidden = true;
        composeStatus.setAttribute('data-state', 'idle');
        return;
      }

      composeStatus.hidden = false;
      composeStatus.setAttribute('data-state', state);
      composeStatusText.textContent = sending ? getChatText('chat_sending', 'Yuborilmoqda') : getChatText('typing', 'Yozilyapti');
    }

    function syncComposeState() {
      if (isSending) {
        setComposeState('sending');
        return;
      }

      if (!isOpen) {
        setComposeState('idle');
        return;
      }

      if (document.activeElement === input && input.value.trim()) {
        setComposeState('typing');
        return;
      }

      setComposeState('idle');
    }

    function appendMessages(msgs, options) {
      if (!msgs.length) return [];

      if (options && options.replace) {
        messagesEl.innerHTML = '';
      }

      // Parallel poll responses can contain the same message; render each id only once.
      var existingIds = new Set(
        Array.prototype.map.call(messagesEl.querySelectorAll('[data-msg-id]'), function (node) {
          return String(node.getAttribute('data-msg-id') || '');
        })
      );
      msgs = msgs.filter(function (msg) {
        var id = String(msg && msg.id ? msg.id : '');
        if (!id || existingIds.has(id)) {
          return false;
        }
        existingIds.add(id);
        return true;
      });
      if (!msgs.length) return [];

      var temp = document.createElement('div');
      temp.innerHTML = msgs.map(renderMsg).join('');

      var nodes = Array.prototype.slice.call(temp.children);
      var animateClass = options && options.seeded ? 'is-seeded' : (options && options.fresh ? 'is-fresh' : '');

      nodes.forEach(function (node, index) {
        if (animateClass) {
          node.classList.add(animateClass);
          node.style.setProperty('--chat-msg-delay', (Math.min(index, 5) * 55) + 'ms');
        }
        if (options && options.burst && node.classList.contains('is-mine')) {
          node.classList.add('is-burst');
        }
        messagesEl.appendChild(node);
      });

      return nodes;
    }

    function renderMsg(m) {
      // Role effekti (super_admin/admin) va tema effekti alohida — aralashmaydi.
      // Super admin o'z animatsiyasida, donor/tema o'z effektlarida.
      var themeKey = m.donor_theme || m.donor_rank;
      var donorRankKeys = ['supporter', 'premium', 'vip'];

      var cls = 'chat-msg' + (m.is_mine ? ' is-mine' : '');
      if (m.is_super_admin) {
        // Super admin — eski animatsiya/dizayn saqlanadi, tema effekti QO'SHILMAYDI
        cls += ' is-super-admin';
      } else if (m.is_admin) {
        cls += ' is-admin';
      } else if (themeKey) {
        // Oddiy foydalanuvchi/donor — tema effekti
        cls += ' is-themed is-theme-' + themeKey;
        if (donorRankKeys.indexOf(themeKey) !== -1) {
          cls += ' is-donor is-donor-' + themeKey;
        }
      }

      var badge = '';
      if (m.is_super_admin) {
        badge = '<span class="chat-msg-super-badge"><i class="fa-solid fa-crown"></i> Super Admin</span>';
      } else if (m.is_admin) {
        badge = '<span class="chat-msg-admin-badge">Admin</span>';
      } else if (m.donor_badge) {
        badge = m.donor_badge;
      }
      var avatarCls = 'chat-msg-avatar chat-msg-avatar-btn' + (m.is_super_admin ? ' chat-msg-avatar--super' : '');
      var avatarInner = m.avatar_url
        ? '<img src="' + escAttr(m.avatar_url) + '" alt="" class="chat-msg-avatar-img" loading="lazy" decoding="async" />'
        : escChatHtml(m.user_initial);
      var actions = '';
      if (m.can_delete) {
        actions += '<button type="button" class="chat-msg-action" data-chat-delete="' + m.id + '" title="O\'chirish"><i class="fa-solid fa-trash-can"></i></button>';
      }
      if (m.can_block) {
        actions += '<button type="button" class="chat-msg-action chat-msg-action--block" data-chat-block="' + m.user_id + '" title="Bloklash"><i class="fa-solid fa-ban"></i></button>';
      }
      var actionsHtml = actions ? '<div class="chat-msg-actions">' + actions + '</div>' : '';
      var nameStyle = '';
      if (m.donor_color && /^#[0-9a-f]{3,8}$/i.test(String(m.donor_color))) {
        nameStyle += 'color:' + m.donor_color + ';';
      }
      if (m.name_font_weight && /^(600|700|800)$/.test(String(m.name_font_weight))) {
        nameStyle += 'font-weight:' + m.name_font_weight + ';';
      }
      return '<div class="' + cls + '" data-msg-id="' + m.id + '">'
        + '<button type="button" class="' + avatarCls + '" data-user-preview-id="' + m.user_id + '" title="Profil" aria-label="Foydalanuvchi profili">'
        + avatarInner
        + '</button>'
        + '<div class="chat-msg-body">'
        + '<div class="chat-msg-meta">'
        + '<span class="chat-msg-name"' + (nameStyle ? ' style="' + nameStyle + '"' : '') + '>' + escChatHtml(m.user_name) + (m.status_emoji ? ' ' + escChatHtml(m.status_emoji) : '') + '</span>'
        + badge
        + '<span class="chat-msg-time">' + m.date + ' ' + m.time + '</span>'
        + actionsHtml
        + '</div>'
        + '<div class="chat-msg-text">' + m.body + '</div>'
        + '</div></div>';
    }

    function loadMessages() {
      var isInitialSeed = lastId === 0;
      var currentChannel = activeChannel;
      var currentGroupId = selectedGroup ? selectedGroup.id : null;

      if (activeChannel === 'group' && !selectedGroup) {
        messagesEl.innerHTML = '<div class="chat-empty-message">' + getChatText('group_select_prompt', 'Iltimos, guruh tanlang.') + '</div>';
        return Promise.resolve([]);
      }

      var query = '?after=' + lastId;
      if (activeChannel === 'group' && selectedGroup) {
        query += '&group_id=' + encodeURIComponent(selectedGroup.id);
      }

      return fetch(messagesUrl + query, {
        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
        credentials: 'same-origin',
      })
        .then(function (r) { return r.json(); })
        .then(function (data) {
          if (activeChannel !== currentChannel || (selectedGroup ? selectedGroup.id : null) !== currentGroupId) {
            return [];
          }

          if (data.chat_disabled) {
            setChatEnabledState(false, data.disabled_message || widget.getAttribute('data-chat-disabled-message'));
            stopPolling();
            return [];
          }
          setChatEnabledState(true);
          var msgs = data.messages || [];
          canClearAll = !!data.can_clear_all;
          syncChatAdminActions();
          if (!msgs.length) return [];

          appendMessages(msgs, {
            replace: lastId === 0,
            seeded: lastId === 0,
            fresh: lastId !== 0,
          });

          lastId = data.last_id || lastId;

          if (isOpen) {
            markChatAsRead(lastId);
            scrollDown();
          } else if (isInitialSeed) {
            unreadCount = countUnreadMessages(msgs, lastReadId);
            syncChatBadge();
          }

          return msgs;
        })
        .catch(function () { return []; });
    }

    function pollNew(options) {
      options = options || {};
      var currentChannel = activeChannel;
      var currentGroupId = selectedGroup ? selectedGroup.id : null;

      if (activeChannel === 'group' && !selectedGroup) {
        return Promise.resolve([]);
      }

      var query = '?after=' + lastId;
      if (activeChannel === 'group' && selectedGroup) {
        query += '&group_id=' + encodeURIComponent(selectedGroup.id);
      }

      return fetch(messagesUrl + query, {
        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
        credentials: 'same-origin',
      })
        .then(function (r) { return r.json(); })
        .then(function (data) {
          if (activeChannel !== currentChannel || (selectedGroup ? selectedGroup.id : null) !== currentGroupId) {
            return [];
          }

          if (data.chat_disabled) {
            setChatEnabledState(false, data.disabled_message || widget.getAttribute('data-chat-disabled-message'));
            stopPolling();
            return [];
          }
          setChatEnabledState(true);
          var msgs = data.messages || [];
          canClearAll = !!data.can_clear_all;
          syncChatAdminActions();
          if (!msgs.length) return [];
          appendMessages(msgs, { fresh: isOpen, burst: isOpen && !!options.burst });
          lastId = data.last_id || lastId;

          if (isOpen) {
            markChatAsRead(lastId);
            scrollDown();
          } else {
            unreadCount += countUnreadMessages(msgs, lastReadId);
            syncChatBadge();
          }

          return msgs;
        })
        .catch(function () { return []; });
    }

    function shouldPauseChatPolling() {
      if (isSending) return true;
      if (!input) return false;
      if (document.activeElement !== input) return false;
      return !!input.value.trim();
    }

    function startPolling() {
      stopPolling();
      pollTimer = setInterval(function () {
        if (shouldPauseChatPolling()) return;
        pollNew();
      }, 5000);
    }

    function stopPolling() {
      if (pollTimer) { clearInterval(pollTimer); pollTimer = null; }
    }

    function sendMessage(text, options) {
      if (activeChannel === 'group' && !selectedGroup) {
        if (window.showToast) {
          window.showToast('Iltimos, guruh tanlang.', 'error');
        }
        return Promise.resolve();
      }

      if (!chatEnabled && activeChannel !== 'group') return Promise.resolve();
      if (isSending) return Promise.resolve();

      isSending = true;
      options = options || {};

      if (sendBtn) {
        sendBtn.disabled = true;
        sendBtn.setAttribute('aria-busy', 'true');
      }
      if (input) {
        input.setAttribute('aria-busy', 'true');
        input.disabled = true;
      }
      stickerButtons.forEach(function (btn) {
        btn.disabled = true;
      });

      var turnstileHost = document.getElementById('chat-turnstile-host');

      function doFetch(turnstileToken) {
        setComposeState('sending');


        var payload = { body: text };
        if (selectedGroup && selectedGroup.id) {
          payload.chat_group_id = selectedGroup.id;
        }
        if (turnstileToken) {
          payload.turnstile_token = turnstileToken;
        }

        return fetch(sendUrl, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrf,
          },
          credentials: 'same-origin',
          body: JSON.stringify(payload),
        })
          .then(function (r) {
            if (!r.ok) {
              return r.text().then(function (t) {
                var msg = 'Xabar yuborilmadi.';
                if (r.status === 419) {
                  msg = 'Sessiya tugagan — sahifani yangilang.';
                } else {
                  try {
                    var d = JSON.parse(t);
                    msg = (d && (d.error || d.message)) || msg;
                    if (d && d.errors) {
                      var firstErr = Object.values(d.errors)[0];
                      if (Array.isArray(firstErr) && firstErr[0]) {
                        msg = firstErr[0];
                      }
                    }
                  } catch (e) {
                    /* HTML yoki boshqa javob */
                  }
                }
                throw new Error(msg);
              });
            }
            input.value = '';
            playPrimeSuccess();
            playPrimeConfetti(window.innerWidth / 2, window.innerHeight / 2);
            return pollNew({ burst: true });
          })
          .catch(function (err) {
            if (options.restoreText && !input.value.trim()) {
              input.value = text;
            }
            if (window.showToast) {
              window.showToast(err && err.message ? err.message : getChatText('chat_network_error', 'Chat: tarmoq xatosi'), 'error');
            }
          })
          .finally(function () {
            isSending = false;
            input.disabled = false;
            input.removeAttribute('aria-busy');
            if (sendBtn) {
              sendBtn.disabled = false;
              sendBtn.removeAttribute('aria-busy');
            }
            stickerButtons.forEach(function (btn) {
              btn.disabled = false;
            });
            if (isOpen) {
              input.focus();
            }
            syncComposeState();
          });
      }

      if (
        turnstileHost &&
        turnstileHost.getAttribute('data-sitekey') &&
        window.turnstile &&
        typeof window.turnstile.execute === 'function'
      ) {
        return new Promise(function (resolve) {
          window.turnstile.execute(turnstileHost, {
            callback: function (token) {
              resolve(doFetch(token));
            },
            'error-callback': function () {
              if (window.showToast) {
                window.showToast('Robot tekshiruvi bajarilmadi. Qayta urinib ko‘ring.', 'error');
              }
              isSending = false;
              input.disabled = false;
              input.removeAttribute('aria-busy');
              if (sendBtn) {
                sendBtn.disabled = false;
                sendBtn.removeAttribute('aria-busy');
              }
              stickerButtons.forEach(function (btn) {
                btn.disabled = false;
              });
              syncComposeState();
              resolve(Promise.resolve());
            },
          });
        });
      }

      return doFetch(null);
    }

    // Drag support
    var isDragging = false;
    var dragStartX = 0, dragStartY = 0;
    var widgetStartX = 0, widgetStartY = 0;
    var hasDragged = false;

    function onDragStart(ex, ey) {
      isDragging = true;
      hasDragged = false;
      dragStartX = ex;
      dragStartY = ey;
      var rect = widget.getBoundingClientRect();
      widgetStartX = rect.left;
      widgetStartY = rect.top;
      widget.classList.add('is-dragging');
    }

    function onDragMove(ex, ey) {
      if (!isDragging) return;
      var dx = ex - dragStartX;
      var dy = ey - dragStartY;
      if (Math.abs(dx) > 4 || Math.abs(dy) > 4) hasDragged = true;
      if (!hasDragged) return;
      var newX = widgetStartX + dx;
      var newY = widgetStartY + dy;
      var maxX = window.innerWidth - 66;
      var maxY = window.innerHeight - 66;
      newX = Math.max(4, Math.min(newX, maxX));
      newY = Math.max(4, Math.min(newY, maxY));
      widget.style.left = newX + 'px';
      widget.style.top = newY + 'px';
      widget.style.right = 'auto';
      widget.style.bottom = 'auto';
    }

    function onDragEnd() {
      if (!isDragging) return;
      isDragging = false;
      widget.classList.remove('is-dragging');
    }

    bubble.addEventListener('pointerdown', function (e) {
      e.preventDefault();
      bubble.setPointerCapture(e.pointerId);
      onDragStart(e.clientX, e.clientY);
    });
    bubble.addEventListener('pointermove', function (e) {
      onDragMove(e.clientX, e.clientY);
    });
    bubble.addEventListener('pointerup', function (e) {
      onDragEnd();
      if (!hasDragged) {
        if (isOpen) closePanel();
        else openPanel();
      }
      hasDragged = false;
    });

    closeBtn.addEventListener('click', closePanel);
    if (fullBtn) fullBtn.addEventListener('click', toggleFullscreen);
    if (clearBtn) {
      clearBtn.addEventListener('click', function () {
        if (!canClearAll || !clearUrl) return;

        function doClearAll() {
          fetch(clearUrl, {
            method: 'DELETE',
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
            credentials: 'same-origin',
          }).then(function (r) {
            if (!r.ok) {
              throw new Error("Chatni tozalab bo'lmadi.");
            }
            messagesEl.innerHTML = '';
            lastId = 0;
            if (window.showToast) {
              window.showToast("Global chat tozalandi.", 'success');
            }
          }).catch(function (err) {
            if (window.showToast) {
              window.showToast(err && err.message ? err.message : "Chatni tozalab bo'lmadi.", 'error');
            }
          });
        }

        var cp = window.primeConfirm && window.primeConfirm({
          message: getChatText('global_chat_clear_confirm', "Global chatdagi barcha xabarlar o'chirilsinmi?"),
          title: getChatText('global_chat_clear_confirm_title', 'Global chatni tozalash'),
          variant: 'danger',
          okText: getChatText('global_chat_clear_ok', "Tozalash"),
        });

        if (cp && typeof cp.then === 'function') {
          cp.then(function (ok) { if (ok) doClearAll(); });
        } else if (window.confirm(getChatText('global_chat_clear_confirm', "Global chatdagi barcha xabarlar o'chirilsinmi?"))) {
          doClearAll();
        }
      });
    }

    if (channelTabs && channelTabs.length) {
      channelTabs.forEach(function (tab) {
        tab.addEventListener('click', function () {
          setActiveChannel(tab.getAttribute('data-chat-channel'));
        });
      });
    }

    if (groupJoinBtn) {
      groupJoinBtn.addEventListener('click', function () {
        requestJoinSelectedGroup();
      });
    }

    if (groupLeaveBtn) {
      groupLeaveBtn.addEventListener('click', function () {
        leaveGroupRequest();
      });
    }

    if (groupCreateBtn) {
      groupCreateBtn.addEventListener('click', function () {
        openGroupCreateModal();
      });
    }

    if (groupCreateModal) {
      var cancelBtn = groupCreateModal.querySelector('[data-group-create-cancel]');
      var okBtn = groupCreateModal.querySelector('[data-group-create-ok]');
      var toggleBtns = groupCreateModal.querySelectorAll('.prime-group-create__toggle-btn');

      if(cancelBtn) cancelBtn.addEventListener('click', closeGroupCreateModal);
      if(okBtn) okBtn.addEventListener('click', submitGroupCreate);

      toggleBtns.forEach(function(btn) {
        btn.addEventListener('click', function() {
          toggleBtns.forEach(function(b) { b.classList.remove('prime-group-create__toggle-btn--active'); });
          btn.classList.add('prime-group-create__toggle-btn--active');
        });
      });
    }

    if (groupMembersBtn) {
      groupMembersBtn.addEventListener('click', function () {
        if (!selectedGroup) return;
        openGroupSubpanel('A\'zolar', 'members');
        loadGroupMembers();
      });
    }

    if (groupSettingsBtn) {
      groupSettingsBtn.addEventListener('click', function () {
        openGroupSubpanel('Sozlamalar', 'settings');
        renderGroupSettings();
      });
    }

    var groupBackBtn = document.getElementById('chat-group-back-btn');
    if (groupBackBtn) {
      groupBackBtn.addEventListener('click', function () {
        selectedGroup = null;
        lastId = 0;
        messagesEl.innerHTML = '';
        updateGroupControls();
      });
    }

    if (groupRequestsBtn) {
      groupRequestsBtn.addEventListener('click', function () {
        if (!selectedGroup) return;
        openGroupSubpanel('So‘rovlar', 'requests');
        loadGroupRequests();
      });
    }

    if (groupSubpanelBackBtn) {
      groupSubpanelBackBtn.addEventListener('click', closeGroupSubpanel);
    }

    window.addEventListener('resize', function () {
      if (!isOpen) return;
      if (panel.classList.contains('is-fullscreen')) return;
      positionPanel();
    });

    form.addEventListener('submit', function (e) {
      e.preventDefault();
      if (!chatEnabled) return;
      if (sendBtn && sendBtn.disabled && !isSending) {
        resetChatComposeState();
      }
      var text = input.value.trim();
      if (!text || isSending) return;
      sendMessage(text, { restoreText: true });
    });

    stickerButtons.forEach(function (btn) {
      btn.addEventListener('click', function () {
        var sticker = btn.getAttribute('data-chat-sticker');
        if (!chatEnabled || !sticker || isSending) return;
        btn.classList.add('is-fired');
        setTimeout(function () {
          btn.classList.remove('is-fired');
        }, 420);

        // Append sticker instead of sending immediately
        input.value += sticker;
        input.focus();

        // Trigger UI updates
        syncComposeState();

        // Dispatch input event for other potential listeners
        input.dispatchEvent(new Event('input', { bubbles: true }));
      });
    });

    input.addEventListener('focus', syncComposeState);
    input.addEventListener('focus', function () {
      if (!isSending && ((sendBtn && sendBtn.disabled) || input.disabled)) {
        resetChatComposeState();
      }
    });
    input.addEventListener('input', function() {
      syncComposeState();
    });
    input.addEventListener('blur', function () {
      setTimeout(syncComposeState, 80);
    });

    messagesEl.addEventListener('click', function (e) {
      var delBtn = e.target.closest('[data-chat-delete]');
      if (delBtn) {
        var msgId = delBtn.getAttribute('data-chat-delete');
        function doDelete() {
        fetch(deleteUrl + '/' + msgId, {
          method: 'DELETE',
          headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
          credentials: 'same-origin',
        }).then(function (r) {
          if (r.ok) {
            var el = messagesEl.querySelector('[data-msg-id="' + msgId + '"]');
            if (el) {
              el.style.transition = 'opacity 0.2s ease, transform 0.2s ease';
              el.style.opacity = '0';
              el.style.transform = 'scale(0.9)';
              setTimeout(function () { el.remove(); }, 220);
            }
          }
        });
        }
        var dp = window.primeConfirm && window.primeConfirm({
          message: 'Bu xabar o‘chirilsinmi?',
          title: 'Xabarni o‘chirish',
          variant: 'danger',
          okText: 'O‘chirish',
        });
        if (dp && typeof dp.then === 'function') {
          dp.then(function (ok) { if (ok) doDelete(); });
        } else if (window.confirm('Bu xabar o‘chirilsinmi?')) {
          doDelete();
        }
        return;
      }

      var blockBtn = e.target.closest('[data-chat-block]');
      if (blockBtn) {
        var userId = blockBtn.getAttribute('data-chat-block');
        function doBlock() {
        fetch(blockUrl + '/' + userId, {
          method: 'POST',
          headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
          credentials: 'same-origin',
        }).then(function (r) {
          if (r.ok) {
            messagesEl.querySelectorAll('[data-msg-id]').forEach(function (el) {
              var btn = el.querySelector('[data-chat-block="' + userId + '"]');
              if (btn) btn.remove();
            });
          }
        });
        }
        var bp = window.primeConfirm && window.primeConfirm({
          message: 'Bu foydalanuvchini bloklaysizmi?',
          title: 'Bloklash',
          variant: 'danger',
          okText: 'Bloklash',
        });
        if (bp && typeof bp.then === 'function') {
          bp.then(function (ok) { if (ok) doBlock(); });
        } else if (window.confirm('Bu foydalanuvchini bloklaysizmi?')) {
          doBlock();
        }
      }
    });

    chatPanelEscapeHandler = function () {
      if (isOpen) closePanel();
    };

    syncChatAdminActions();
    syncChatBadge();

    if (chatEnabled) {
      // Always start with global chat on page load - simpler and more reliable
      activeChannel = 'global';
      // Sync UI tabs
      if (channelTabs && channelTabs.length) {
        channelTabs.forEach(function (tab) {
          tab.classList.toggle('chat-panel-tab--active', tab.getAttribute('data-chat-channel') === 'global');
        });
      }
      // Ensure correct initial UI state
      if (groupShell) groupShell.hidden = true;
      if (messagesEl) messagesEl.hidden = false;
      loadMessages();
      startPolling();
    }

    window.primeCloseGlobalChatPanel = function () {
      if (isOpen) closePanel();
    };
  }


  function initCommentTypingSound() {
    document.addEventListener(
      'input',
      function (e) {
        const t = e.target;
        if (!t || !t.classList || !t.classList.contains('comment-input')) return;
        if (t.id === 'chat-input') return;
      },
      true
    );
  }

  function initExamQuestionForms() {
    function bindExamQuestionForm(scope) {
      const toolbar = scope.querySelector('[data-exam-toolbar]');
      if (!toolbar || scope.dataset.examRichBound === 'true') return;

      scope.dataset.examRichBound = 'true';

      const labels = ['A', 'B', 'C', 'D'];
      const optionBox = scope.querySelector('#option-box');
      const questionTypeSelect = scope.querySelector('[data-question-type-select]');
      const mcqFields = scope.querySelector('[data-question-mcq-fields]');
      const textFields = scope.querySelector('[data-question-text-fields]');
      const shuffleButton = scope.querySelector('#shuffle-options');
      const richInputs = Array.from(scope.querySelectorAll('.js-exam-rich-input'));
      const savedSelections = new WeakMap();
      let activeInput = richInputs[0] || null;

      function rememberSelection(input) {
        if (!input) return;

        savedSelections.set(input, {
          start: input.selectionStart ?? input.value.length,
          end: input.selectionEnd ?? input.value.length,
        });
      }

      function syncActiveInput(input) {
        activeInput = input;
        rememberSelection(input);
      }

      function focusFirstEnabledInput() {
        const enabledInput = richInputs.find((input) => !input.disabled);
        if (enabledInput) {
          activeInput = enabledInput;
        }
      }

      richInputs.forEach((input) => {
        ['focus', 'click', 'keyup', 'mouseup', 'select', 'input'].forEach((eventName) => {
          input.addEventListener(eventName, () => {
            syncActiveInput(input);
          });
        });
      });

      function insertIntoActive(before, after = '') {
        if (!activeInput || activeInput.disabled) return;

        const savedSelection = savedSelections.get(activeInput);
        const start = savedSelection?.start ?? activeInput.selectionStart ?? activeInput.value.length;
        const end = savedSelection?.end ?? activeInput.selectionEnd ?? activeInput.value.length;
        const selected = activeInput.value.slice(start, end);
        const replacement = before + selected + after;

        activeInput.focus();

        if (typeof activeInput.setSelectionRange === 'function') {
          activeInput.setSelectionRange(start, end);
        }

        if (typeof activeInput.setRangeText === 'function') {
          activeInput.setRangeText(replacement, start, end, 'end');
        } else {
          activeInput.value = activeInput.value.slice(0, start) + replacement + activeInput.value.slice(end);
          const cursor = start + replacement.length;
          if (typeof activeInput.setSelectionRange === 'function') {
            activeInput.setSelectionRange(cursor, cursor);
          }
        }

        rememberSelection(activeInput);
        activeInput.dispatchEvent(new Event('input', { bubbles: true }));
      }

      scope.querySelectorAll('.js-exam-wrap, .js-exam-insert').forEach((button) => {
        button.addEventListener('mousedown', (event) => {
          event.preventDefault();
        });
      });

      scope.querySelectorAll('.js-exam-wrap').forEach((button) => {
        button.addEventListener('click', () => {
          insertIntoActive(button.dataset.before || '', button.dataset.after || '');
        });
      });

      scope.querySelectorAll('.js-exam-insert').forEach((button) => {
        button.addEventListener('click', () => {
          insertIntoActive(button.dataset.insert || '');
        });
      });

      function toggleQuestionMode() {
        if (!questionTypeSelect || !mcqFields || !textFields) return;

        const isText = questionTypeSelect.value === 'text';
        mcqFields.style.display = isText ? 'none' : '';
        textFields.style.display = isText ? '' : 'none';
        if (shuffleButton) {
          shuffleButton.style.display = isText ? 'none' : 'inline-flex';
        }

        mcqFields.querySelectorAll('textarea, select, input').forEach((field) => {
          field.disabled = isText;
        });
        textFields.querySelectorAll('textarea, select, input').forEach((field) => {
          field.disabled = !isText;
        });

        focusFirstEnabledInput();
      }

      if (questionTypeSelect) {
        questionTypeSelect.addEventListener('change', toggleQuestionMode);
        toggleQuestionMode();
      } else {
        focusFirstEnabledInput();
      }

      scope.querySelector('#shuffle-options')?.addEventListener('click', () => {
        if (!optionBox) return;

        const values = labels.map((label) => optionBox.querySelector(`[name="options[${label}]"]`)?.value || '');
        for (let i = values.length - 1; i > 0; i -= 1) {
          const j = Math.floor(Math.random() * (i + 1));
          [values[i], values[j]] = [values[j], values[i]];
        }

        labels.forEach((label, index) => {
          const field = optionBox.querySelector(`[name="options[${label}]"]`);
          if (field) {
            field.value = values[index];
            field.dispatchEvent(new Event('input', { bubbles: true }));
          }
        });
      });
    }

    document.querySelectorAll('form').forEach(bindExamQuestionForm);
  }

  function initPrimeAudioControl() {
    const toggle = document.createElement('div');
    toggle.className = 'prime-audio-toggle' + (primeAudioMuted ? ' is-muted' : '');
    toggle.id = 'prime-audio-control';
    toggle.innerHTML = '<i class="fa-solid ' + (primeAudioMuted ? 'fa-volume-xmark' : 'fa-volume-high') + '"></i>';
    toggle.title = primeAudioMuted ? 'Ovozlarni yoqish' : 'Ovozlarni o‘chirish';
    var audioSlot = document.getElementById('prime-audio-slot');
    if (audioSlot) {
      audioSlot.appendChild(toggle);
    } else {
      document.body.appendChild(toggle);
    }

    toggle.addEventListener('click', function() {
      primeAudioMuted = !primeAudioMuted;
      localStorage.setItem('site-audio-muted', String(primeAudioMuted));
      toggle.classList.toggle('is-muted', primeAudioMuted);
      toggle.innerHTML = '<i class="fa-solid ' + (primeAudioMuted ? 'fa-volume-xmark' : 'fa-volume-high') + '"></i>';
      toggle.title = primeAudioMuted ? 'Ovozlarni yoqish' : 'Ovozlarni o‘chirish';

      if (!primeAudioMuted) {
        playPrimeSuccess();
      }
    });

  }

  function initGlobalSearchModal() {
    var modal = document.getElementById('global-search-modal');
    var input = document.getElementById('global-search-input');
    var resultsWrap = document.getElementById('global-search-results');
    var cfg = document.getElementById('global-search-config');
    var openBtns = document.querySelectorAll('[data-global-search-open]');
    var searchUrl = cfg && cfg.getAttribute('data-search-url');
    if (!modal || !input || !resultsWrap || !searchUrl || !openBtns.length) return;

    var debounceTimer = null;
    var activeController = null;
    var closeTimer = null;
    var opened = false;
    var selectedIndex = -1;
    var requestSeq = 0;

    function lockBody(lock) {
      document.body.classList.toggle('global-search-open', !!lock);
      document.documentElement.classList.toggle('global-search-open', !!lock);
    }

    function renderLoading() {
      resultsWrap.innerHTML = '<p class="global-search-empty">Qidirilmoqda...</p>';
    }

    function renderEmpty(text) {
      resultsWrap.innerHTML = '<p class="global-search-empty">' + text + '</p>';
    }

    function renderResults(items) {
      if (!items || !items.length) {
        renderEmpty('Hech narsa topilmadi.');
        playGlobalSearchNotFoundSound();
        return;
      }

      var html = items.map(function (item) {
        var title = escChatHtml(String(item.title || ''));
        var desc = escChatHtml(String(item.description || ''));
        var href = escAttr(String(item.url || '#'));
        var type = String(item.type || 'result');
        var image = item.image ? '<img src="' + escAttr(String(item.image)) + '" alt="">' : '<span class="global-search-item-icon"><i class="fa-solid fa-layer-group"></i></span>';
        return ''
          + '<a href="' + href + '" class="global-search-item" data-global-search-result>'
          + '  <span class="global-search-item-media">' + image + '</span>'
          + '  <span class="global-search-item-main">'
          + '    <span class="global-search-item-type">' + escChatHtml(type) + '</span>'
          + '    <strong class="global-search-item-title">' + title + '</strong>'
          + '    <span class="global-search-item-desc">' + desc + '</span>'
          + '  </span>'
          + '</a>';
      }).join('');

      resultsWrap.innerHTML = html;
      selectedIndex = -1;
      if (!primeAudioMuted) {
        playPrimeSuccess();
      }
    }

    function resultItems() {
      return Array.prototype.slice.call(resultsWrap.querySelectorAll('.global-search-item'));
    }

    function setActiveIndex(nextIdx) {
      var items = resultItems();
      if (!items.length) {
        selectedIndex = -1;
        return;
      }
      if (nextIdx < 0) nextIdx = items.length - 1;
      if (nextIdx >= items.length) nextIdx = 0;
      selectedIndex = nextIdx;
      items.forEach(function (item, idx) {
        item.classList.toggle('is-active', idx === selectedIndex);
      });
      var active = items[selectedIndex];
      if (active && typeof active.scrollIntoView === 'function') {
        active.scrollIntoView({ block: 'nearest' });
      }
    }

    function closeModal() {
      if (modal.hidden) return;
      modal.classList.add('is-closing');
      modal.classList.remove('is-opening');
      if (closeTimer) {
        clearTimeout(closeTimer);
      }
      closeTimer = window.setTimeout(function () {
        modal.hidden = true;
        modal.classList.remove('is-closing');
      }, 220);
      lockBody(false);
      if (activeController) {
        activeController.abort();
        activeController = null;
      }
      opened = false;
      playGlobalSearchCloseSound();
    }

    function openModal() {
      if (opened) return;
      opened = true;
      if (closeTimer) {
        clearTimeout(closeTimer);
      }
      modal.hidden = false;
      modal.classList.remove('is-closing');
      modal.classList.add('is-opening');
      lockBody(true);
      if (input.value.trim() === '') {
        renderEmpty('Qidirishni boshlash uchun so‘z kiriting.');
      }
      window.setTimeout(function () {
        input.focus();
      }, 0);
      playGlobalSearchOpenSound();
    }

    openBtns.forEach(function (btn) {
      btn.addEventListener('click', function (e) {
        e.preventDefault();
        openModal();
      });
    });

    modal.addEventListener('click', function (e) {
      if (e.target === input) return;
      var insideInputWrap = e.target && e.target.closest && e.target.closest('.global-search-input-wrap');
      if (!insideInputWrap) {
        closeModal();
      }
    });

    resultsWrap.addEventListener('click', function (e) {
      var link = e.target && e.target.closest && e.target.closest('[data-global-search-result]');
      if (link) {
        closeModal();
      }
    });

    document.addEventListener('keydown', function (e) {
      if ((e.ctrlKey || e.metaKey) && String(e.key).toLowerCase() === 'k') {
        e.preventDefault();
        openModal();
        return;
      }

      if (modal.hidden) return;

      if (e.key === 'ArrowDown') {
        e.preventDefault();
        setActiveIndex(selectedIndex + 1);
        return;
      }

      if (e.key === 'ArrowUp') {
        e.preventDefault();
        setActiveIndex(selectedIndex - 1);
        return;
      }

      if (e.key === 'Enter') {
        var items = resultItems();
        if (selectedIndex >= 0 && items[selectedIndex]) {
          e.preventDefault();
          items[selectedIndex].click();
          return;
        }
      }

      if (e.key === 'Escape') {
        closeModal();
      }
    });

    input.addEventListener('input', function () {
      var q = input.value.trim();
      if (debounceTimer) {
        clearTimeout(debounceTimer);
      }

      if (q.length < 2) {
        renderEmpty('Kamida 2 ta harf kiriting.');
        return;
      }

      debounceTimer = window.setTimeout(function () {
        var seq = ++requestSeq;
        var loadingStartedAt = Date.now();
        var minLoadingMs = 1000;
        if (activeController) {
          activeController.abort();
        }
        activeController = new AbortController();
        renderLoading();
        fetch(searchUrl + '?q=' + encodeURIComponent(q), {
          headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
          },
          credentials: 'same-origin',
          signal: activeController.signal,
        })
          .then(function (r) {
            if (!r.ok) throw new Error('search_failed');
            return r.json();
          })
          .then(function (payload) {
            var waitMs = Math.max(0, minLoadingMs - (Date.now() - loadingStartedAt));
            window.setTimeout(function () {
              if (seq !== requestSeq) return;
              renderResults(payload && payload.results ? payload.results : []);
            }, waitMs);
          })
          .catch(function (err) {
            if (err && err.name === 'AbortError') return;
            var waitMs = Math.max(0, minLoadingMs - (Date.now() - loadingStartedAt));
            window.setTimeout(function () {
              if (seq !== requestSeq) return;
              renderEmpty('Qidiruvda xatolik bo‘ldi.');
            }, waitMs);
          });
      }, 220);
    });

    resultsWrap.addEventListener('mouseover', function (e) {
      var card = e.target && e.target.closest && e.target.closest('.global-search-item');
      if (!card) return;
      var items = resultItems();
      var idx = items.indexOf(card);
      if (idx >= 0) {
        setActiveIndex(idx);
      }
    });

  }

  /** Prime Pro Max: Animated Charts (ApexCharts) */
  function initPrimeCharts() {
    const chartContainers = document.querySelectorAll('.prime-chart-container');
    if (!chartContainers.length || typeof ApexCharts === 'undefined') return;

    chartContainers.forEach(container => {
      const type = container.getAttribute('data-chart-type') || 'area';
      const data = JSON.parse(container.getAttribute('data-chart-series') || '[]');
      const categories = JSON.parse(container.getAttribute('data-chart-categories') || '[]');
      const color = container.getAttribute('data-chart-color') || '#4f46e5';

      const options = {
        series: data,
        chart: {
          height: 350,
          type: type,
          toolbar: { show: false },
          zoom: { enabled: false },
          background: 'transparent',
          foreColor: 'var(--muted)',
          animations: {
            enabled: true,
            easing: 'easeinout',
            speed: 800,
            animateGradually: { enabled: true, delay: 150 },
            dynamicAnimation: { enabled: true, speed: 350 }
          }
        },
        dataLabels: { enabled: false },
        stroke: {
          curve: 'smooth',
          width: 3,
          colors: [color]
        },
        fill: {
          type: 'gradient',
          gradient: {
            shadeIntensity: 1,
            opacityFrom: 0.45,
            opacityTo: 0.05,
            stops: [20, 100, 100],
            colorStops: [
              { offset: 0, color: color, opacity: 0.4 },
              { offset: 100, color: color, opacity: 0 }
            ]
          }
        },
        markers: {
          size: 5,
          colors: [color],
          strokeColors: '#fff',
          strokeWidth: 2,
          hover: { size: 7 }
        },
        xaxis: {
          categories: categories,
          axisBorder: { show: false },
          axisTicks: { show: false }
        },
        yaxis: {
          labels: {
            formatter: (val) => val.toFixed(0)
          }
        },
        grid: {
          borderColor: 'var(--border-soft)',
          strokeDashArray: 4,
          padding: { left: 20, right: 20 }
        },
        theme: {
          mode: root.getAttribute('data-theme') || 'light'
        },
        tooltip: {
          theme: root.getAttribute('data-theme') || 'light',
          x: { show: true },
          marker: { show: true }
        }
      };

      const chart = new ApexCharts(container, options);
      chart.render();

      // Update chart theme on toggle
      document.addEventListener('themeChanged', () => {
        chart.updateOptions({
          theme: { mode: root.getAttribute('data-theme') },
          tooltip: { theme: root.getAttribute('data-theme') }
        });
      });
    });
  }

  function runInitializers() {
    moveGlobalModals();
    initChatUserPreviewChrome();
    initUserProfilePreview();
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
	    initPublicLiveStats();
	    initInteractiveActions();
	    initProMaxAnimations();
    initThemeBurstEffect();
    initLocalePageReveal();
    initGlobalChat();
    initCommentTypingSound();
    initExamQuestionForms();
    initPrimeAudioControl();
    initGlobalSearchModal();
    initSeniorInteractions();

    // Prime Pro Max Initializers
    initPrimeCharts();

    // Pointer interaction to unlock AudioContext
    const unlockAudio = () => {
      try {
        const ctx = getPrimeAudioCtx();
        // Industry standard: play a microscopic silent tone to force unlock the audio engine
        const osc = ctx.createOscillator();
        const gain = ctx.createGain();
        gain.gain.value = 0.0001;
        osc.connect(gain);
        gain.connect(ctx.destination);
        osc.start(0);
        osc.stop(ctx.currentTime + 0.001);
      } catch(e) {}
      document.removeEventListener('pointerdown', unlockAudio);
      document.removeEventListener('keydown', unlockAudio);
    };
    document.addEventListener('pointerdown', unlockAudio);
    document.addEventListener('keydown', unlockAudio);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', runInitializers);
  } else {
    runInitializers();
  }
})();


// DROPDOWN OVERRIDE — har marta animatsiya
(function() {
  function initDropdownAnimations() {
    var dds = document.querySelectorAll('.js-header-dropdown');
    if (!dds.length) return;

    function openDD(dd) {
      var menu = dd.querySelector('.nav-dropdown-menu');
      if (!menu) { dd.setAttribute('open', ''); return; }
      menu.classList.remove('is-closing');
      dd.setAttribute('open', '');
      menu.classList.remove('is-open');
      void menu.offsetWidth;
      menu.classList.add('is-open');
    }

    function closeDD(dd) {
      var menu = dd.querySelector('.nav-dropdown-menu');
      if (!menu) { dd.removeAttribute('open'); return; }
      menu.classList.remove('is-open');
      menu.classList.add('is-closing');
      function onEnd() {
        menu.classList.remove('is-closing');
        dd.removeAttribute('open');
        menu.removeEventListener('animationend', onEnd);
      }
      menu.addEventListener('animationend', onEnd, { once: true });
      // Fallback: animatsiya ishlamasa ham yopilsin
      setTimeout(function() {
        if (dd.hasAttribute('open')) {
          menu.classList.remove('is-closing');
          dd.removeAttribute('open');
        }
      }, 350);
    }

    dds.forEach(function(dd) {
      var summary = dd.querySelector('summary');
      if (!summary) return;
      // Eski click listener ni o'chirib yangi qo'yamiz
      var newSummary = summary.cloneNode(true);
      summary.parentNode.replaceChild(newSummary, summary);

      newSummary.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        if (dd.hasAttribute('open')) {
          closeDD(dd);
        } else {
          dds.forEach(function(o) { if (o !== dd && o.hasAttribute('open')) closeDD(o); });
          openDD(dd);
        }
      });
    });

    document.addEventListener('click', function(e) {
      dds.forEach(function(dd) {
        if (dd.hasAttribute('open') && !dd.contains(e.target)) closeDD(dd);
      });
    });

    document.addEventListener('keydown', function(e) {
      if (e.key !== 'Escape') return;
      dds.forEach(function(dd) { if (dd.hasAttribute('open')) closeDD(dd); });
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initDropdownAnimations);
  } else {
    initDropdownAnimations();
  }
})();


(function() {
  function initDropdownAnimations() {
    var dds = document.querySelectorAll('.js-header-dropdown');
    if (!dds.length) return;

    function openDD(dd) {
      var menu = dd.querySelector('.nav-dropdown-menu');
      if (!menu) { dd.setAttribute('open', ''); return; }
      menu.classList.remove('is-closing');
      dd.setAttribute('open', '');
      menu.classList.remove('is-open');
      void menu.offsetWidth;
      menu.classList.add('is-open');
    }

    function closeDD(dd) {
      var menu = dd.querySelector('.nav-dropdown-menu');
      if (!menu) { dd.removeAttribute('open'); return; }
      menu.classList.remove('is-open');
      menu.classList.add('is-closing');
      function onEnd() { menu.classList.remove('is-closing'); dd.removeAttribute('open'); }
      menu.addEventListener('animationend', onEnd, { once: true });
      setTimeout(function() { if (dd.hasAttribute('open')) { menu.classList.remove('is-closing'); dd.removeAttribute('open'); } }, 350);
    }

    dds.forEach(function(dd) {
      var summary = dd.querySelector('summary');
      if (!summary) return;
      var fresh = summary.cloneNode(true);
      summary.parentNode.replaceChild(fresh, summary);
      fresh.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        if (dd.hasAttribute('open')) {
          closeDD(dd);
        } else {
          dds.forEach(function(o) { if (o !== dd && o.hasAttribute('open')) closeDD(o); });
          openDD(dd);
        }
      });
    });

    document.addEventListener('click', function(e) {
      dds.forEach(function(dd) { if (dd.hasAttribute('open') && !dd.contains(e.target)) closeDD(dd); });
    });

    document.addEventListener('keydown', function(e) {
      if (e.key !== 'Escape') return;
      dds.forEach(function(dd) { if (dd.hasAttribute('open')) closeDD(dd); });
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initDropdownAnimations);
  } else {
    initDropdownAnimations();
  }
})();
