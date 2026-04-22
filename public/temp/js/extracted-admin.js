(() => {
  function parseJson(value, fallback) {
    if (!value) return fallback;
    try {
      return JSON.parse(value);
    } catch (error) {
      return fallback;
    }
  }

  const body = document.body;

  const toastContainer = document.getElementById('admin-toast-container');
  if (toastContainer) {
    const toastTimerMs = 3200;
    const sessionSuccess = body?.dataset.adminSuccess || '';
    const sessionError = body?.dataset.adminError || '';
    const toastTypeFlash = body?.dataset.adminToastType || '';
    const errorMessages = parseJson(body?.dataset.adminErrors, []);

    function showToast(message, type) {
      if (!message) return;

      const toast = document.createElement('div');
      toast.className = `admin-toast admin-${type}`;
      toast.textContent = message;
      toastContainer.appendChild(toast);

      setTimeout(() => {
        toast.classList.add('admin-toast-out');
        setTimeout(() => toast.remove(), 250);
      }, toastTimerMs);
    }

    function resolveType(defaultType) {
      if (!toastTypeFlash) return defaultType;
      if (toastTypeFlash === 'warning') return 'warning';
      if (toastTypeFlash === 'error') return 'error';
      if (toastTypeFlash === 'success') return 'success';
      return defaultType;
    }

    if (errorMessages.length) {
      document.querySelectorAll('.alert-box.success-alert').forEach((el) => el.remove());
    } else {
      document.querySelectorAll('.alert-box.success-alert, .alert-box.danger-alert').forEach((el) => el.remove());
    }

    if (sessionSuccess) {
      showToast(sessionSuccess, resolveType('success'));
    }

    if (sessionError) {
      showToast(sessionError, resolveType('error'));
    }

    if (errorMessages.length) {
      const summary = errorMessages.length === 1
        ? errorMessages[0]
        : `${errorMessages.slice(0, 2).join(' · ')}${errorMessages.length > 2 ? ` (+${errorMessages.length - 2})` : ''}`;
      showToast(summary, 'error');
    }
  }

  document.querySelectorAll('form').forEach((scope) => {
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
    let activeInput = richInputs[0] || null;

    richInputs.forEach((input) => {
      input.addEventListener('focus', () => {
        activeInput = input;
      });
    });

    function insertIntoActive(before, after = '') {
      if (!activeInput) return;

      const start = activeInput.selectionStart ?? activeInput.value.length;
      const end = activeInput.selectionEnd ?? activeInput.value.length;
      const selected = activeInput.value.slice(start, end);
      const replacement = before + selected + after;

      activeInput.setRangeText(replacement, start, end, 'end');
      activeInput.focus();
    }

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
    }

    if (questionTypeSelect) {
      questionTypeSelect.addEventListener('change', toggleQuestionMode);
      toggleQuestionMode();
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
        if (field) field.value = values[index];
      });
    });
  });
})();
