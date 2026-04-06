@php
  $totalQ = $orderedQuestions->count();
  $answeredCount = $answerMap->filter(function ($answer) {
    return $answer && ($answer->option_id !== null || filled($answer->text_answer));
  })->count();
  $progressPct = $totalQ > 0 ? min(100, (int) round($answeredCount / $totalQ * 100)) : 0;
  $examTitle = $result->exam->title ?? 'Imtihon';
@endphp

<x-loyouts.main title="{{ $examTitle }} - savol">
  <main class="news exam-page exam-session-wrap">
    <div class="exam-page-inner exam-anti-copy" id="exam-anti-root">
      <header class="exam-session-header">
        <div class="exam-session-header-row">
          <div class="exam-session-title-block">
            <p class="exam-session-exam-name">{{ $examTitle }}</p>
            <p class="exam-session-step-line" id="exam-step-line">
              Savol <span id="exam-step-current">1</span> / {{ $totalQ }}
            </p>
          </div>
          <div class="exam-timer exam-timer--compact">
            <div class="exam-timer-icon" aria-hidden="true">
              <i class="fa-solid fa-hourglass-half"></i>
            </div>
            <div>
              <div class="exam-timer-label">Qolgan vaqt</div>
              <div class="exam-timer-digits" id="timer" role="timer" aria-live="polite">--:--</div>
            </div>
          </div>
        </div>

        <div class="exam-session-header-row exam-session-header-row--2">
          <div class="exam-progress-block exam-progress-block--full">
            <div class="exam-progress-label">
              <span>Javob berilgan</span>
              <span><strong id="exam-answered-num">{{ $answeredCount }}</strong> / {{ $totalQ }}</span>
            </div>
            <div class="exam-progress-track">
              <div class="exam-progress-fill" id="exam-progress-fill" style="width: {{ $progressPct }}%;"></div>
            </div>
          </div>
          <p class="exam-secure-note">
            <i class="fa-solid fa-shield-halved" aria-hidden="true"></i>
            <span>Skrinshot, chop etish va nusxalash taqiqlanadi. 5 martadan ortiq buzilganda imtihon 0 ball bilan yakunlanadi.</span>
          </p>
        </div>
      </header>

      <form id="submit-form" action="{{ route('exam.submit', $result) }}" method="POST">
        @csrf
      </form>

      <div class="exam-step-stack">
        @foreach($orderedQuestions as $index => $question)
          <article
            class="exam-q-card exam-step {{ $index === 0 ? 'exam-step--active' : '' }}"
            data-step="{{ $index }}"
            data-question-id="{{ $question->id }}"
            @if($index !== 0) hidden @endif
          >
            <div class="exam-q-head">
              <span class="exam-q-num">{{ $index + 1 }}</span>
              <div class="exam-q-text">{!! render_exam_rich_text($question->body) !!}</div>
            </div>

            @if($question->image_url)
              <div class="exam-question-media">
                <img src="{{ $question->image_url }}" alt="Savol rasmi" loading="lazy">
              </div>
            @endif

            @if($question->isTextType())
              @php
                $textAnswer = optional($answerMap->get($question->id))->text_answer ?? '';
              @endphp
              <div class="exam-text-answer-block">
                <label class="exam-text-answer-label" for="exam_text_{{ $question->id }}">Javobingiz</label>
                <textarea
                  id="exam_text_{{ $question->id }}"
                  class="exam-text-answer-field"
                  data-text-question-id="{{ $question->id }}"
                  placeholder="Javobingizni shu yerga yozing..."
                  rows="7"
                >{{ $textAnswer }}</textarea>
                <p class="exam-answer-save-state" data-text-save-state="{{ $question->id }}">
                  Javobingiz yozilishi bilan avtomatik saqlanadi.
                </p>
              </div>
            @else
              <div class="exam-options">
                @foreach($question->options as $option)
                  <label class="exam-option">
                    <input
                      type="radio"
                      name="q_{{ $question->id }}"
                      value="{{ $option->id }}"
                      {{ (int) (optional($answerMap->get($question->id))->option_id ?? 0) === (int) $option->id ? 'checked' : '' }}
                      onchange="saveAnswer({{ $question->id }}, {{ $option->id }})"
                    >
                    <span class="exam-option-body">
                      <span class="exam-option-label">{{ $option->label }}.</span>
                      {!! render_exam_rich_text($option->body) !!}
                    </span>
                  </label>
                @endforeach
              </div>
            @endif
          </article>
        @endforeach
      </div>

      <nav class="exam-step-nav" aria-label="Savollar boyicha">
        <button type="button" class="exam-btn-secondary" id="exam-btn-prev" disabled>
          <i class="fa-solid fa-arrow-left"></i> Oldingi
        </button>
        <button type="button" class="exam-btn-primary" id="exam-btn-next">
          Keyingi savol <i class="fa-solid fa-arrow-right"></i>
        </button>
        <button type="button" class="exam-btn-primary exam-btn-submit-final" id="exam-btn-finish" hidden>
          Yakunlash va yuborish <i class="fa-solid fa-paper-plane"></i>
        </button>
      </nav>
    </div>
  </main>

  <div id="exam-rule-modal" class="exam-rule-modal" hidden role="dialog" aria-modal="true" aria-labelledby="exam-rule-modal-title">
    <div class="exam-rule-modal-backdrop" tabindex="-1"></div>
    <div class="exam-rule-modal-box">
      <h3 id="exam-rule-modal-title">Qoidalarga rioya qiling</h3>
      <p>
        Skrinshot (PrtSc, Snipping Tool, telefon ekran surati), belgilab nusxa olish va chop etish taqiqlanadi.
        <strong>5 martadan ortiq</strong> buzilish serverda qayd etiladi va imtihon <strong>0 ball, yiqildi</strong> deb yopiladi.
      </p>
      <button type="button" class="exam-btn-primary" id="exam-rule-modal-ok" style="width:100%;justify-content:center;margin-top:8px;">Tushunarli</button>
    </div>
  </div>

  <div id="exam-finish-confirm-modal" class="exam-rule-modal exam-finish-confirm-modal" hidden role="dialog" aria-modal="true" aria-labelledby="exam-finish-confirm-title">
    <div class="exam-rule-modal-backdrop" tabindex="-1" data-exam-finish-backdrop></div>
    <div class="exam-rule-modal-box exam-finish-confirm-box">
      <div class="exam-finish-confirm-icon" aria-hidden="true">
        <i class="fa-solid fa-circle-question"></i>
      </div>
      <h3 id="exam-finish-confirm-title">Imtihonni yakunlaysizmi?</h3>
      <p>Javoblaringiz yuboriladi. Keyin ularni o'zgartirish yoki imtihonga qaytish mumkin emas.</p>
      <div class="exam-rule-modal-actions">
        <button type="button" class="exam-btn-secondary" id="exam-finish-confirm-cancel">Bekor qilish</button>
        <button type="button" class="exam-btn-primary" id="exam-finish-confirm-submit">
          Ha, yuborish <i class="fa-solid fa-paper-plane"></i>
        </button>
      </div>
    </div>
  </div>

  <script>
    (function () {
      var modal = document.getElementById('exam-rule-modal');
      var root = document.getElementById('exam-anti-root');
      var lastContextWarnAt = 0;
      var disqualifiedNav = false;

      var violationUrl = @json(route('exam.violation', $result));
      var csrfToken = @json(csrf_token());

      document.body.classList.add('exam-session-print-lock');

      function reportRuleViolation() {
        if (disqualifiedNav) return;
        fetch(violationUrl, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
          },
          body: JSON.stringify({}),
        })
          .then(function (r) { return r.json().catch(function () { return {}; }); })
          .then(function (data) {
            if (data.redirect) {
              disqualifiedNav = true;
              window.location.href = data.redirect;
            }
          })
          .catch(function () {});
      }

      function warnAndReport(e) {
        if (e) {
          e.preventDefault();
          e.stopPropagation();
        }
        showRuleModal();
        reportRuleViolation();
      }

      function showRuleModal() {
        if (!modal) return;
        modal.hidden = false;
        document.body.style.overflow = 'hidden';
      }

      function hideRuleModal() {
        if (!modal) return;
        modal.hidden = true;
        document.body.style.overflow = '';
      }

      function showScreenshotWarn(e) {
        warnAndReport(e);
      }

      function maybeContextWarn() {
        var now = Date.now();
        if (now - lastContextWarnAt < 600) return;
        lastContextWarnAt = now;
        warnAndReport(null);
      }

      document.getElementById('exam-rule-modal-ok')?.addEventListener('click', hideRuleModal);
      modal?.querySelector('.exam-rule-modal-backdrop')?.addEventListener('click', hideRuleModal);

      document.addEventListener('visibilitychange', function () {
        if (document.hidden) maybeContextWarn();
      });
      window.addEventListener('blur', function () {
        if (document.hidden) return;
        maybeContextWarn();
      });

      window.addEventListener('beforeprint', function () {
        warnAndReport(null);
      });

      document.addEventListener('keydown', function (e) {
        var key = e.key || '';
        var kl = key.toLowerCase();
        var ctrl = e.ctrlKey || e.metaKey;
        var meta = e.metaKey;

        if (
          key === 'PrintScreen'
          || key === 'Print'
          || key === 'F13'
          || key === 'Snapshot'
          || e.keyCode === 44
        ) {
          showScreenshotWarn(e);
          return;
        }
        if (e.altKey && (key === 'PrintScreen' || e.keyCode === 44)) {
          showScreenshotWarn(e);
          return;
        }

        if (key === 'F12') {
          e.preventDefault();
          return;
        }

        if (meta && e.shiftKey && (kl === '3' || kl === '4' || kl === '5')) {
          showScreenshotWarn(e);
          return;
        }
        if (e.shiftKey && meta && kl === 's') {
          showScreenshotWarn(e);
          return;
        }

        if (ctrl && kl === 'p') {
          e.preventDefault();
          warnAndReport(null);
          return;
        }
        if (ctrl && e.shiftKey && kl === 's') {
          e.preventDefault();
          warnAndReport(null);
          return;
        }
        if (ctrl && ['c', 'x', 'u', 's'].indexOf(kl) !== -1) {
          e.preventDefault();
          return;
        }
        if (ctrl && e.shiftKey && ['i', 'j', 'c', 'p'].indexOf(kl) !== -1) {
          e.preventDefault();
          return;
        }
      }, true);

      if (root) {
        ['copy', 'cut', 'contextmenu', 'dragstart', 'paste', 'selectstart'].forEach(function (ev) {
          root.addEventListener(ev, function (e) {
            if (e.target && e.target.closest('.exam-text-answer-field')) {
              if (ev === 'copy' || ev === 'cut' || ev === 'paste') {
                e.preventDefault();
              }
              return;
            }

            e.preventDefault();
          }, true);
        });
      }
      document.addEventListener('paste', function (e) {
        if (e.target && e.target.closest('.exam-text-answer-field')) {
          e.preventDefault();
          return;
        }
        e.preventDefault();
      }, true);
    })();

    const expiresAt = new Date(@json(optional($result->expires_at)->toIso8601String())).getTime();
    const timerEl = document.getElementById('timer');
    const totalQuestions = {{ (int) $totalQ }};
    const steps = Array.prototype.slice.call(document.querySelectorAll('.exam-step'));
    const textSaveTimers = new Map();
    let currentIdx = 0;

    const btnPrev = document.getElementById('exam-btn-prev');
    const btnNext = document.getElementById('exam-btn-next');
    const btnFinish = document.getElementById('exam-btn-finish');
    const stepCurrentEl = document.getElementById('exam-step-current');

    function showStep(idx) {
      if (idx < 0 || idx >= steps.length) return;
      currentIdx = idx;
      steps.forEach(function (el, i) {
        var on = i === idx;
        el.classList.toggle('exam-step--active', on);
        el.hidden = !on;
      });
      if (stepCurrentEl) stepCurrentEl.textContent = String(idx + 1);
      if (btnPrev) btnPrev.disabled = idx === 0;
      var last = idx === steps.length - 1;
      if (btnNext) btnNext.hidden = last;
      if (btnFinish) btnFinish.hidden = !last;
    }

    async function flushPendingTextAnswers() {
      const dirtyFields = Array.prototype.slice.call(document.querySelectorAll('.exam-text-answer-field[data-dirty="1"]'));

      for (const field of dirtyFields) {
        await saveTextAnswer(Number(field.dataset.textQuestionId), field.value, field, true);
      }
    }

    if (btnPrev) btnPrev.addEventListener('click', async function () {
      await flushPendingTextAnswers();
      showStep(currentIdx - 1);
    });
    if (btnNext) btnNext.addEventListener('click', async function () {
      await flushPendingTextAnswers();
      showStep(currentIdx + 1);
    });

    var finishConfirmModal = document.getElementById('exam-finish-confirm-modal');

    function showFinishConfirmModal() {
      if (!finishConfirmModal) return;
      finishConfirmModal.hidden = false;
      document.body.style.overflow = 'hidden';
      document.getElementById('exam-finish-confirm-submit')?.focus();
    }

    function hideFinishConfirmModal() {
      if (!finishConfirmModal) return;
      finishConfirmModal.hidden = true;
      document.body.style.overflow = '';
    }

    if (btnFinish) btnFinish.addEventListener('click', function () {
      showFinishConfirmModal();
    });
    document.getElementById('exam-finish-confirm-cancel')?.addEventListener('click', hideFinishConfirmModal);
    finishConfirmModal?.querySelector('[data-exam-finish-backdrop]')?.addEventListener('click', hideFinishConfirmModal);
    document.getElementById('exam-finish-confirm-submit')?.addEventListener('click', async function () {
      await flushPendingTextAnswers();
      document.getElementById('submit-form').submit();
    });
    document.addEventListener('keydown', function (e) {
      if (e.key !== 'Escape' || !finishConfirmModal || finishConfirmModal.hidden) return;
      e.preventDefault();
      hideFinishConfirmModal();
    });

    function tick() {
      const now = Date.now();
      const diff = Math.max(0, expiresAt - now);
      const totalSec = Math.floor(diff / 1000);
      const min = String(Math.floor(totalSec / 60)).padStart(2, '0');
      const sec = String(totalSec % 60).padStart(2, '0');
      timerEl.textContent = min + ':' + sec;

      if (diff <= 0) {
        flushPendingTextAnswers().finally(function () {
          document.getElementById('submit-form').submit();
        });
      }
    }

    function updateProgress() {
      const checked = document.querySelectorAll('.exam-option input[type=radio]:checked').length;
      const textAnswered = Array.prototype.slice.call(document.querySelectorAll('.exam-text-answer-field'))
        .filter(function (field) { return field.value.trim() !== ''; }).length;
      const answered = checked + textAnswered;
      const fill = document.getElementById('exam-progress-fill');
      const num = document.getElementById('exam-answered-num');
      if (num) num.textContent = answered;
      if (fill && totalQuestions > 0) {
        fill.style.width = Math.min(100, Math.round(answered / totalQuestions * 100)) + '%';
      }
    }

    async function saveAnswer(questionId, optionId) {
      try {
        const response = await fetch(@json(route('exam.answer', $result)), {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': @json(csrf_token()),
          },
          body: JSON.stringify({
            question_id: questionId,
            option_id: optionId,
          }),
        });

        if (response.ok) updateProgress();

        if (!response.ok) {
          const data = await response.json().catch(function () { return {}; });
          if (data.message) alert(data.message);
        }
      } catch (e) {
        alert("Javobni saqlashda xato bo'ldi.");
      }
    }

    function setTextSaveState(questionId, text, kind) {
      const stateEl = document.querySelector('[data-text-save-state="' + questionId + '"]');
      if (!stateEl) return;

      stateEl.textContent = text;
      stateEl.classList.remove('is-saving', 'is-saved', 'is-error');
      if (kind) {
        stateEl.classList.add(kind);
      }
    }

    async function saveTextAnswer(questionId, textAnswer, field, forceNow) {
      if (!forceNow && field.dataset.lastSavedValue === textAnswer) {
        field.dataset.dirty = '0';
        updateProgress();
        return;
      }

      setTextSaveState(questionId, 'Javob saqlanmoqda...', 'is-saving');

      try {
        const response = await fetch(@json(route('exam.answer', $result)), {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': @json(csrf_token()),
          },
          body: JSON.stringify({
            question_id: questionId,
            text_answer: textAnswer,
          }),
        });

        if (!response.ok) {
          const data = await response.json().catch(function () { return {}; });
          throw new Error(data.message || "Javobni saqlab bo'lmadi.");
        }

        field.dataset.lastSavedValue = textAnswer;
        field.dataset.dirty = '0';
        setTextSaveState(
          questionId,
          textAnswer.trim() === '' ? "Javob o'chirildi." : 'Javob saqlandi.',
          'is-saved'
        );
        updateProgress();
      } catch (error) {
        field.dataset.dirty = '1';
        setTextSaveState(questionId, error.message || "Javobni saqlashda xato bo'ldi.", 'is-error');
      }
    }

    Array.prototype.slice.call(document.querySelectorAll('.exam-text-answer-field')).forEach(function (field) {
      field.dataset.lastSavedValue = field.value;
      field.dataset.dirty = '0';

      field.addEventListener('input', function () {
        field.dataset.dirty = '1';
        updateProgress();
        setTextSaveState(field.dataset.textQuestionId, 'Javob saqlanmoqda...', 'is-saving');

        const key = field.dataset.textQuestionId;
        if (textSaveTimers.has(key)) {
          clearTimeout(textSaveTimers.get(key));
        }

        textSaveTimers.set(key, setTimeout(function () {
          saveTextAnswer(Number(field.dataset.textQuestionId), field.value, field, false);
        }, 500));
      });

      field.addEventListener('blur', function () {
        const key = field.dataset.textQuestionId;
        if (textSaveTimers.has(key)) {
          clearTimeout(textSaveTimers.get(key));
          textSaveTimers.delete(key);
        }

        saveTextAnswer(Number(field.dataset.textQuestionId), field.value, field, true);
      });
    });

    tick();
    setInterval(tick, 1000);
    updateProgress();
    showStep(0);
  </script>
</x-loyouts.main>
