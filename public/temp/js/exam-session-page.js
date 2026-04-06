(() => {
  const sessionRoot = document.querySelector('.exam-session-wrap[data-exam-session]');
  if (!sessionRoot) return;

  const sessionConfig = JSON.parse(sessionRoot.dataset.examSession || '{}');
  const examRuleModal = document.getElementById('exam-rule-modal');
  const finishConfirmModal = document.getElementById('exam-finish-confirm-modal');

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
    let disqualifiedNav = false;

    function reportRuleViolation() {
      if (disqualifiedNav || !sessionConfig.violationUrl || !sessionConfig.csrfToken) return;

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
            disqualifiedNav = true;
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
      showRuleModal();
      reportRuleViolation();
    }

    function maybeContextWarn() {
      const now = Date.now();
      if (now - lastContextWarnAt < 600) return;
      lastContextWarnAt = now;
      warnAndReport(null);
    }

    document.body.classList.add('exam-session-print-lock');
    document.getElementById('exam-rule-modal-ok')?.addEventListener('click', hideRuleModal);
    examRuleModal?.querySelector('.exam-rule-modal-backdrop')?.addEventListener('click', hideRuleModal);

    document.addEventListener('visibilitychange', () => {
      if (document.hidden) maybeContextWarn();
    });

    window.addEventListener('blur', () => {
      if (!document.hidden) maybeContextWarn();
    });

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
  document.getElementById('exam-finish-confirm-submit')?.addEventListener('click', () => {
    submitForm?.submit();
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
