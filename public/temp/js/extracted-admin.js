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
})();
