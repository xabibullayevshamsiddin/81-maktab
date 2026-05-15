(() => {
  const sessionRoot = document.querySelector('.exam-session-wrap[data-exam-session]');
  if (!sessionRoot) return;

  const sessionConfig = JSON.parse(sessionRoot.dataset.examSession || '{}');
  const examRuleModal = document.getElementById('exam-rule-modal');
  const finishConfirmModal = document.getElementById('exam-finish-confirm-modal');
  let isNavigatingAway = false;

  function syncExamModalState() {
    const hasOpenModal =
      !!(examRuleModal && !examRuleModal.hidden) ||
      !!(finishConfirmModal && !finishConfirmModal.hidden);

    document.documentElement.classList.toggle('exam-modal-open', hasOpenModal);
    document.body.classList.toggle('exam-modal-open', hasOpenModal);
  }

  (function initAntiCheat() {
    const root = document.getElementById('exam-anti-root');
    let lastContextWarnAt = 0;

    function reportRuleViolation() {
      if (isNavigatingAway || !sessionConfig.violationUrl || !sessionConfig.csrfToken) return;

      fetch(sessionConfig.violationUrl, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          Accept: 'application/json',
          'X-CSRF-TOKEN': sessionConfig.csrfToken,
        },
        body: JSON.stringify({}),
      })
        .then((response) => response.json().catch(() => ({})))
        .then((data) => {
          if (data.redirect) {
            isNavigatingAway = true;
            window.location.href = data.redirect;
          }
        })
        .catch(() => {});
    }

    function showRuleModal() {
      if (!examRuleModal) return;
      examRuleModal.hidden = false;
      syncExamModalState();
    }

    function hideRuleModal() {
      if (!examRuleModal) return;
      examRuleModal.hidden = true;
      syncExamModalState();
    }

    function warnAndReport(event) {
      if (event) {
        event.preventDefault();
        event.stopPropagation();
      }
      if (typeof window.playPrimeViolationSound === 'function') {
        window.playPrimeViolationSound();
      }
      showRuleModal();
      reportRuleViolation();
    }

    function maybeContextWarn() {
      const now = Date.now();
      if (now - lastContextWarnAt < 80) return;  // Reduced: 600 → 80ms
      lastContextWarnAt = now;
      warnAndReport(null);
    }

    document.body.classList.add('exam-session-print-lock');
    document.getElementById('exam-rule-modal-ok')?.addEventListener('click', hideRuleModal);
    examRuleModal?.querySelector('.exam-rule-modal-backdrop')?.addEventListener('click', hideRuleModal);

    // ---------------------------------------------------------
    // SCREENSHOT SHIELD — Focus-loss blackout
    // ---------------------------------------------------------
    // Win+Shift+S, Snipping Tool, Alt+PrtScr — barchasi
    // oynadan fokusni oladi. Biz DAN OLDIN qora qopqoq
    // yopamiz → screenshot faqat qora ekranni ko'rsatadi.
    const shield = document.createElement('div');
    shield.id = 'exam-screenshot-shield';
    shield.setAttribute('aria-hidden', 'true');
    shield.style.cssText = [
      'position:fixed',
      'inset:0',
      'z-index:2147483647',   // max z-index
      'background:#050d1a',
      'display:none',
      'align-items:center',
      'justify-content:center',
      'flex-direction:column',
      'gap:18px',
      'color:#e6eefb',
      'font-family:Inter,sans-serif',
      'pointer-events:none',
      'user-select:none',
    ].join(';');
    shield.innerHTML = [
      '<svg width="54" height="54" viewBox="0 0 24 24" fill="none" stroke="#7db4ff" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">',
        '<rect x="3" y="11" width="18" height="11" rx="2"/>',
        '<path d="M7 11V7a5 5 0 0 1 10 0v4"/>',
      '</svg>',
      '<p style="font-size:17px;font-weight:600;margin:0;">Ekran himoyasi yoqilgan</p>',
      '<p style="font-size:13px;color:#9cb1cb;margin:0;">Imtihon tarkibini ko\u02bbchirish taqiqlanadi</p>',
    ].join('');
    document.body.appendChild(shield);

    function showShield() {
      shield.style.display = 'flex';
    }

    function hideShield() {
      shield.style.display = 'none';
    }

    document.addEventListener('visibilitychange', () => {
      if (document.hidden) {
        showShield();
        maybeContextWarn();
      } else {
        hideShield();
      }
    });

    window.addEventListener('blur', () => {
      showShield();
      if (!document.hidden) maybeContextWarn();
    });

    window.addEventListener('focus', hideShield);

    window.addEventListener('beforeprint', () => {
      warnAndReport(null);
    });

    document.addEventListener('keydown', (event) => {
      const key = event.key || '';
      const lowerKey = key.toLowerCase();
      const ctrl = event.ctrlKey || event.metaKey;
      const meta = event.metaKey;

      if (key === 'PrintScreen' || key === 'Print' || key === 'F13' || key === 'Snapshot' || event.keyCode === 44) {
        warnAndReport(event);
        return;
      }

      if (event.altKey && (key === 'PrintScreen' || event.keyCode === 44)) {
        warnAndReport(event);
        return;
      }

      if (key === 'F12') {
        event.preventDefault();
        return;
      }

      if (meta && event.shiftKey && ['3', '4', '5'].includes(lowerKey)) {
        warnAndReport(event);
        return;
      }

      if (event.shiftKey && meta && lowerKey === 's') {
        warnAndReport(event);
        return;
      }

      if (ctrl && lowerKey === 'p') {
        event.preventDefault();
        warnAndReport(null);
        return;
      }

      if (ctrl && event.shiftKey && lowerKey === 's') {
        event.preventDefault();
        warnAndReport(null);
        return;
      }

      if (ctrl && ['c', 'x', 'u', 's'].includes(lowerKey)) {
        event.preventDefault();
        return;
      }

      if (ctrl && event.shiftKey && ['i', 'j', 'c', 'p'].includes(lowerKey)) {
        event.preventDefault();
      }
    }, true);

    if (root) {
      ['copy', 'cut', 'contextmenu', 'dragstart', 'paste', 'selectstart'].forEach((eventName) => {
        root.addEventListener(eventName, (event) => event.preventDefault(), true);
      });
    }

    // ---------------------------------------------------------
    // PRIME BACK-BUTTON LOCK — History Flood Method
    // ---------------------------------------------------------
    // Bitta pushState yetarli emas — browser virtual entry'dan
    // real history'ga o'tib ketadi. Yechim: 50 ta soxta entry
    // qo'shib history stack'ni to'ldirish.
    // Har bir "orqaga" tugmasi bosimi bitta entry'ni kamaytiradi,
    // popstate handler esa darhol uni qaytaradi →
    // student hech qachon taqvimdan chiqib keta olmaydi.
    const LOCK_DEPTH = 50;
    for (let i = 0; i < LOCK_DEPTH; i++) {
      history.pushState(null, null, window.location.href);
    }
    window.addEventListener('popstate', () => {
      // Har safar bosganda stack'ni to'ldirish davom ettiriladi
      for (let i = 0; i < LOCK_DEPTH; i++) {
        history.pushState(null, null, window.location.href);
      }
      maybeContextWarn();
    });

    // Also prevent accidental tab close/refresh
    window.addEventListener('beforeunload', (e) => {
      if (isNavigatingAway) return; // Allow if already disqualified/redirecting
      e.preventDefault();
      e.returnValue = ''; // Standard way to show "Are you sure you want to leave?"
    });

    document.addEventListener('paste', (event) => event.preventDefault(), true);
  })();

  const timerEl = document.getElementById('timer');
  const totalQuestions = parseInt(sessionRoot.dataset.totalQuestions || '0', 10);
  const steps = Array.from(document.querySelectorAll('.exam-step'));
  const btnPrev = document.getElementById('exam-btn-prev');
  const btnNext = document.getElementById('exam-btn-next');
  const btnFinish = document.getElementById('exam-btn-finish');
  const stepCurrentEl = document.getElementById('exam-step-current');
  const submitForm = document.getElementById('submit-form');
  const expiresAt = sessionConfig.expiresAt ? new Date(sessionConfig.expiresAt).getTime() : 0;
  let currentIdx = 0;

  function showStep(idx) {
    if (idx < 0 || idx >= steps.length) return;

    currentIdx = idx;
    steps.forEach((el, i) => {
      const on = i === idx;
      el.classList.toggle('exam-step--active', on);
      el.hidden = !on;
    });

    if (stepCurrentEl) stepCurrentEl.textContent = String(idx + 1);
    if (btnPrev) btnPrev.disabled = idx === 0;

    const last = idx === steps.length - 1;
    if (btnNext) btnNext.hidden = last;
    if (btnFinish) btnFinish.hidden = !last;
  }

  function showFinishConfirmModal() {
    if (!finishConfirmModal) return;
    finishConfirmModal.hidden = false;
    syncExamModalState();
    document.getElementById('exam-finish-confirm-submit')?.focus();
  }

  function hideFinishConfirmModal() {
    if (!finishConfirmModal) return;
    finishConfirmModal.hidden = true;
    syncExamModalState();
  }

  function tick() {
    if (!timerEl || !expiresAt) return;

    const diff = Math.max(0, expiresAt - Date.now());
    const totalSec = Math.floor(diff / 1000);
    const min = String(Math.floor(totalSec / 60)).padStart(2, '0');
    const sec = String(totalSec % 60).padStart(2, '0');
    timerEl.textContent = `${min}:${sec}`;

    if (diff <= 0) {
      submitForm?.submit();
    }
  }

  function updateProgress() {
    const checked = document.querySelectorAll('.exam-option input[type="radio"]:checked').length;
    let textAnswered = 0;
    document.querySelectorAll('.js-exam-textarea').forEach(ta => {
      if (ta.value.trim().length > 0) textAnswered++;
    });
    
    const totalAnswered = checked + textAnswered;
    const fill = document.getElementById('exam-progress-fill');
    const num = document.getElementById('exam-answered-num');

    if (num) num.textContent = String(totalAnswered);
    if (fill && totalQuestions > 0) {
      fill.style.width = `${Math.min(100, Math.round((totalAnswered / totalQuestions) * 100))}%`;
    }
  }

  async function saveAnswer(questionId, optionId, textAnswer) {
    if (!sessionConfig.answerUrl || !sessionConfig.csrfToken) return;

    try {
      const response = await fetch(sessionConfig.answerUrl, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          Accept: 'application/json',
          'X-CSRF-TOKEN': sessionConfig.csrfToken,
        },
        body: JSON.stringify({
          question_id: questionId,
          option_id: optionId ?? null,
          text_answer: textAnswer ?? null,
        }),
      });

      if (response.ok) updateProgress();

      if (!response.ok) {
        const data = await response.json().catch(() => ({}));
        if (data.message) {
          alert(data.message);
        }
      }
    } catch (error) {
      alert(sessionConfig.answerError || "Javobni saqlashda xato bo‘ldi.");
    }
  }

  btnPrev?.addEventListener('click', () => showStep(currentIdx - 1));
  btnNext?.addEventListener('click', () => showStep(currentIdx + 1));
  btnFinish?.addEventListener('click', showFinishConfirmModal);

  document.querySelectorAll('.js-exam-answer-input').forEach(input => {
    input.addEventListener('change', (e) => {
      const el = e.target;
      const questionId = el.dataset.questionId;
      
      if (el.tagName.toLowerCase() === 'textarea') {
        saveAnswer(questionId, null, el.value);
      } else {
        saveAnswer(questionId, el.value, null);
      }
    });
  });
  document.getElementById('exam-finish-confirm-cancel')?.addEventListener('click', hideFinishConfirmModal);
  finishConfirmModal?.querySelector('[data-exam-finish-backdrop]')?.addEventListener('click', hideFinishConfirmModal);
  document.getElementById('exam-finish-confirm-submit')?.addEventListener('click', async () => {
    if (!submitForm) return;
    
    // 1. Show Senior Loader
    let loader = document.querySelector('.prime-exam-loader');
    if (!loader) {
      loader = document.createElement('div');
      loader.className = 'prime-exam-loader';
      loader.innerHTML = `
        <div class="prime-grading-container">
          <div class="prime-grading-title">Natijalaringiz tahlil qilinmoqda...</div>
          <div class="prime-grading-bar-wrap">
            <div class="prime-grading-bar"></div>
          </div>
        </div>
      `;
      document.body.appendChild(loader);
    }
    
    hideFinishConfirmModal();
    setTimeout(() => loader.classList.add('is-active'), 50);

    // 2. AJAX Submission
    try {
      isNavigatingAway = true; // Unlock navigation before fetch/redirect
      const response = await fetch(submitForm.action, {
        method: 'POST',
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': sessionConfig.csrfToken
        },
        body: new FormData(submitForm)
      });

      const data = await response.json();
      
      // 3. Royale Feedback
      if (data.redirect) {
        const passed = data.passed;
        const score = data.score_raw || 0;
        
        // Let the loader settle for a moment
        setTimeout(() => {
          if (passed) {
            if (typeof window.playPrimeResultPass === 'function') window.playPrimeResultPass();
            if (typeof window.playPrimeConfetti === 'function') {
                window.playPrimeConfetti(window.innerWidth / 2, window.innerHeight / 2, true);
            }
            document.body.classList.add('prime-success-glow');
          } else {
            if (typeof window.playPrimeResultFail === 'function') window.playPrimeResultFail();
            document.body.classList.add('prime-failure-shake');
          }

          // 4. Final Redirect
          setTimeout(() => {
            window.location.href = data.redirect;
          }, 2400);
        }, 1200);
      } else {
        // Fallback if no redirect but data ok
        submitForm.submit();
      }
    } catch (err) {
      // Fallback on error
      submitForm.submit();
    }
  });

  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && finishConfirmModal && !finishConfirmModal.hidden) {
      event.preventDefault();
      hideFinishConfirmModal();
    }
  });

  document.addEventListener('change', (event) => {
    const input = event.target.closest('.js-exam-answer-input');
    if (!input) return;

    saveAnswer(input.dataset.questionId, input.value);
  });

  syncExamModalState();
  tick();
  setInterval(tick, 1000);
  updateProgress();
  showStep(0);
})();
