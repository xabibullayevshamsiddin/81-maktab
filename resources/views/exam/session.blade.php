@php
  $totalQ = $orderedQuestions->count();
  $answeredCount = $answerMap->filter(function ($answer) {
    return $answer && ($answer->option_id !== null || filled($answer->text_answer));
  })->count();
  $progressPct = $totalQ > 0 ? min(100, (int) round($answeredCount / $totalQ * 100)) : 0;
  $examTitle = $result->exam->title ?? __('public.exam.default_title');
  $sessionI18n = trans('public.exam.session');
  $watermarkLabel = trim((auth()->user()->name ?? 'Foydalanuvchi') . ' • #' . $result->id . ' • ' . $examTitle);
  $violationLimit = 5;
  $initialViolationCount = (int) ($result->rule_violation_count ?? 0);
  $remainingViolationCount = max(0, $violationLimit - $initialViolationCount);
  $violationFillPct = $violationLimit > 0 ? min(100, (int) round($initialViolationCount / $violationLimit * 100)) : 0;
  // Xavfsizlik o'chirilgan bo'lsa hech qanday cheklov qo'llanilmaydi
  $securityEnabled = (bool) ($result->exam->security_enabled ?? true);
@endphp

<x-loyouts.main :title="$examTitle . __('public.exam.session.title_suffix')">
  <main class="news exam-page exam-session-wrap">
    <div class="exam-page-inner exam-anti-copy" id="exam-anti-root">
      <div class="exam-watermark-layer" id="exam-watermark-layer" aria-hidden="true" data-watermark="{{ $watermarkLabel }}"></div>
      <header class="exam-session-header">
        <div class="exam-session-header-row">
          <div class="exam-session-title-block">
            <p class="exam-session-exam-name">{{ $examTitle }}</p>
            <p class="exam-session-step-line" id="exam-step-line">
              {!! __('public.exam.session.question_step', ['current' => '<span id="exam-step-current">1</span>', 'total' => $totalQ]) !!}
            </p>
          </div>
          <div class="exam-timer exam-timer--compact">
            <div class="exam-timer-icon" aria-hidden="true">
              <i class="fa-solid fa-hourglass-half"></i>
            </div>
            <div>
              <div class="exam-timer-label">{{ __('public.exam.session.time_left') }}</div>
              <div class="exam-timer-digits" id="timer" role="timer" aria-live="polite">--:--</div>
            </div>
          </div>
        </div>

        <div class="exam-session-header-row exam-session-header-row--2">
          <div class="exam-progress-block exam-progress-block--full">
            <div class="exam-progress-label">
              <span>{{ __('public.exam.session.answered') }}</span>
              <span><strong id="exam-answered-num">{{ $answeredCount }}</strong> / {{ $totalQ }}</span>
            </div>
            <div class="exam-progress-track">
              <div class="exam-progress-fill" id="exam-progress-fill" style="width: {{ $progressPct }}%;"></div>
            </div>
          </div>
          <div class="exam-secure-stack">
            <p class="exam-secure-note">
              <i class="fa-solid fa-shield-halved" aria-hidden="true"></i>
              <span>{{ __('public.exam.session.secure_note', ['limit' => $violationLimit]) }}</span>
            </p>
            <div class="exam-violation-panel {{ $remainingViolationCount === 1 ? 'is-danger' : '' }}" id="exam-violation-panel">
              <div class="exam-violation-head">
                <span>{{ __('public.exam.session.violation_limit') }}</span>
                <strong><span id="exam-violation-count">{{ $initialViolationCount }}</span> / {{ $violationLimit }}</strong>
              </div>
              <div class="exam-violation-track">
                <div class="exam-violation-fill" id="exam-violation-fill" style="width: {{ $violationFillPct }}%;"></div>
              </div>
              <p class="exam-violation-note" id="exam-violation-note">
                @if($remainingViolationCount > 1)
                  {{ __('public.exam.session.violation_remaining', ['count' => $remainingViolationCount]) }}
                @elseif($remainingViolationCount === 1)
                  {{ __('public.exam.session.violation_last_warning') }}
                @else
                  {{ __('public.exam.session.violation_exhausted') }}
                @endif
              </p>
            </div>
          </div>
        </div>
      </header>

      <form id="submit-form" action="{{ route('exam.submit', $result) }}" method="POST">
        @csrf
      </form>

      <div class="exam-step-stack">
        <!-- Question Navigation Grid -->
        <div class="exam-nav-grid-container">
          <div class="exam-nav-grid-header">
            <span class="exam-nav-grid-title"><i class="fa-solid fa-list-ol"></i> {{ __('public.exam.session.nav_title') }}</span>
            <span class="exam-nav-grid-hint">{{ __('public.exam.session.nav_hint') }}</span>
          </div>
          <div class="exam-nav-grid" id="exam-nav-grid">
            @foreach($orderedQuestions as $index => $question)
              @php
                $isAnswered = $answerMap->has($question->id) && ($answerMap->get($question->id)->option_id !== null || filled($answerMap->get($question->id)->text_answer));
              @endphp
              <button
                type="button"
                class="exam-grid-item {{ $isAnswered ? 'is-answered' : '' }} {{ $index === 0 ? 'is-active' : '' }}"
                onclick="showStep({{ $index }})"
                data-grid-step="{{ $index }}"
                data-grid-question-id="{{ $question->id }}"
                title="{{ __('public.exam.session.question_title', ['num' => $index + 1]) }}"
              >
                {{ $index + 1 }}
              </button>
            @endforeach
          </div>
        </div>

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
                <img src="{{ $question->image_url }}" alt="{{ __('public.exam.session.question_image_alt') }}" loading="lazy">
              </div>
            @endif

            @if($question->isTextType())
              @php
                $textAnswer = optional($answerMap->get($question->id))->text_answer ?? '';
              @endphp
              <div class="exam-text-answer-block">
                <label class="exam-text-answer-label" for="exam_text_{{ $question->id }}">{{ __('public.exam.session.your_answer') }}</label>
                <textarea
                  id="exam_text_{{ $question->id }}"
                  class="exam-text-answer-field"
                  data-text-question-id="{{ $question->id }}"
                  placeholder="{{ __('public.exam.session.answer_placeholder') }}"
                  rows="7"
                >{{ $textAnswer }}</textarea>
                <p class="exam-answer-save-state" data-text-save-state="{{ $question->id }}">
                  {{ __('public.exam.session.autosave_hint') }}
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

      <nav class="exam-step-nav" aria-label="{{ __('public.exam.session.nav_aria') }}">
        <button type="button" class="exam-btn-secondary" id="exam-btn-prev" disabled>
          <i class="fa-solid fa-arrow-left"></i> {{ __('public.exam.session.prev') }}
        </button>
        <button type="button" class="exam-btn-primary" id="exam-btn-next">
          {{ __('public.exam.session.next') }} <i class="fa-solid fa-arrow-right"></i>
        </button>
        <button type="button" class="exam-btn-primary exam-btn-submit-final" id="exam-btn-finish" hidden>
          {{ __('public.exam.session.finish_submit') }} <i class="fa-solid fa-paper-plane"></i>
        </button>
      </nav>
    </div>
  </main>

  <div id="exam-focus-guard" class="exam-focus-guard" hidden role="dialog" aria-modal="true" aria-labelledby="exam-focus-guard-title">
    <div class="exam-focus-guard-backdrop"></div>
    <div class="exam-focus-guard-box">
      <span class="exam-focus-guard-badge">{{ __('public.exam.session.protected_mode') }}</span>
      <h3 id="exam-focus-guard-title">{{ __('public.exam.session.focus_title') }}</h3>
      <p id="exam-focus-guard-text">{{ __('public.exam.session.focus_text') }}</p>
      <button type="button" class="exam-btn-primary" id="exam-focus-guard-resume" style="width:100%;justify-content:center;">
        {{ __('public.exam.session.focus_resume') }}
      </button>
    </div>
  </div>

  <div id="exam-rule-modal" class="exam-rule-modal" hidden role="dialog" aria-modal="true" aria-labelledby="exam-rule-modal-title">
    <div class="exam-rule-modal-backdrop" tabindex="-1"></div>
    <div class="exam-rule-modal-box">
      <h3 id="exam-rule-modal-title">{{ __('public.exam.session.rules_title') }}</h3>
      <p>{!! __('public.exam.session.rules_text') !!}</p>
      <button type="button" class="exam-btn-primary" id="exam-rule-modal-ok" style="width:100%;justify-content:center;margin-top:8px;">{{ __('public.exam.session.rules_ok') }}</button>
    </div>
  </div>

  <div id="exam-finish-confirm-modal" class="exam-rule-modal exam-finish-confirm-modal" hidden role="dialog" aria-modal="true" aria-labelledby="exam-finish-confirm-title">
    <div class="exam-rule-modal-backdrop" tabindex="-1" data-exam-finish-backdrop></div>
    <div class="exam-rule-modal-box exam-finish-confirm-box">
      <div class="exam-finish-confirm-icon" aria-hidden="true">
        <i class="fa-solid fa-circle-question"></i>
      </div>
      <h3 id="exam-finish-confirm-title">{{ __('public.exam.session.finish_confirm_title') }}</h3>
      <p>{{ __('public.exam.session.finish_confirm_text') }}</p>
      <div class="exam-rule-modal-actions">
        <button type="button" class="exam-btn-secondary" id="exam-finish-confirm-cancel">{{ __('public.exam.session.cancel') }}</button>
        <button type="button" class="exam-btn-primary" id="exam-finish-confirm-submit">
          {{ __('public.exam.session.confirm_submit') }} <i class="fa-solid fa-paper-plane"></i>
        </button>
      </div>
    </div>
  </div>

  <script>
    (function () {
      var examSessionI18n = @json($sessionI18n);
      var modal = document.getElementById('exam-rule-modal');
      var modalTitleEl = document.getElementById('exam-rule-modal-title');
      var modalTextEl = modal ? modal.querySelector('p') : null;
      var modalOkBtn = document.getElementById('exam-rule-modal-ok');
      var defaultModalTitle = modalTitleEl ? modalTitleEl.textContent : '';
      var defaultModalText = modalTextEl ? modalTextEl.innerHTML : '';
      var defaultModalOkText = modalOkBtn ? modalOkBtn.textContent : '';
      var root = document.getElementById('exam-anti-root');
      var watermarkLayer = document.getElementById('exam-watermark-layer');
      var focusGuard = document.getElementById('exam-focus-guard');
      var focusGuardTitleEl = document.getElementById('exam-focus-guard-title');
      var focusGuardTextEl = document.getElementById('exam-focus-guard-text');
      var focusGuardResumeBtn = document.getElementById('exam-focus-guard-resume');
      var violationPanelEl = document.getElementById('exam-violation-panel');
      var violationCountEl = document.getElementById('exam-violation-count');
      var violationFillEl = document.getElementById('exam-violation-fill');
      var violationNoteEl = document.getElementById('exam-violation-note');
      var lastContextWarnAt = 0;
      var lastViolationReportAt = 0;
      var disqualifiedNav = false;
      var modalLockActive = false;
      var transientIgnoreUntil = 0;
      var currentViolationCount = {{ $initialViolationCount }};
      var fullscreenSupported = !!(
        document.documentElement.requestFullscreen
        || document.documentElement.webkitRequestFullscreen
        || document.documentElement.msRequestFullscreen
      );

      var violationUrl = @json(route('exam.violation', $result));
      var csrfToken = @json(csrf_token());
      var violationLimit = {{ $violationLimit }};
      var securityEnabled = {{ $securityEnabled ? 'true' : 'false' }};

      // Xavfsizlik o'chirilgan bo'lsa — xavfsizlik panelini yashirish
      if (!securityEnabled) {
        var secureStack = document.querySelector('.exam-secure-stack');
        if (secureStack) secureStack.style.display = 'none';
      }

      document.body.classList.add('exam-session-print-lock');

      function syncBodyLock() {
        var shouldLock = (modal && !modal.hidden)
          || (focusGuard && !focusGuard.hidden)
          || (typeof finishConfirmModal !== 'undefined' && finishConfirmModal && !finishConfirmModal.hidden);
        document.body.style.overflow = shouldLock ? 'hidden' : '';
      }

      function buildWatermark() {
        if (!watermarkLayer) return;

        var label = watermarkLayer.getAttribute('data-watermark') || 'Protected exam';
        watermarkLayer.innerHTML = '';

        for (var i = 0; i < 18; i += 1) {
          var tile = document.createElement('span');
          tile.className = 'exam-watermark-tile';
          tile.textContent = label + ' • ' + String(i + 1).padStart(2, '0');
          watermarkLayer.appendChild(tile);
        }
      }

      function setTransientIgnore(ms) {
        transientIgnoreUntil = Date.now() + (ms || 0);
      }

      function isTransientIgnoreActive() {
        return Date.now() < transientIgnoreUntil;
      }

      function getFullscreenElement() {
        return document.fullscreenElement || document.webkitFullscreenElement || document.msFullscreenElement || null;
      }

      function isFullscreenActive() {
        return !!getFullscreenElement();
      }

      function requestProtectedFullscreen() {
        var target = document.documentElement;
        var fn = target.requestFullscreen || target.webkitRequestFullscreen || target.msRequestFullscreen;

        if (!fn) {
          return Promise.resolve(false);
        }

        try {
          var result = fn.call(target);

          if (result && typeof result.then === 'function') {
            return result.then(function () { return true; }).catch(function () { return false; });
          }

          return Promise.resolve(true);
        } catch (err) {
          return Promise.resolve(false);
        }
      }

      function showFocusGuard(title, text, buttonText) {
        if (!focusGuard) return;
        if (focusGuardTitleEl) focusGuardTitleEl.textContent = title || examSessionI18n.focus_title;
        if (focusGuardTextEl) focusGuardTextEl.textContent = text || examSessionI18n.focus_text;
        if (focusGuardResumeBtn) {
          focusGuardResumeBtn.textContent = buttonText || (fullscreenSupported ? examSessionI18n.focus_resume_retry : examSessionI18n.continue_btn);
        }
        focusGuard.hidden = false;
        if (root) root.classList.add('is-obscured');
        syncBodyLock();
      }

      function hideFocusGuard() {
        if (!focusGuard) return;
        focusGuard.hidden = true;
        if (root) root.classList.remove('is-obscured');
        syncBodyLock();
      }

      function updateViolationUi(count) {
        currentViolationCount = Math.max(0, Math.min(violationLimit, Number(count || 0)));

        var remaining = Math.max(0, violationLimit - currentViolationCount);
        var fillPct = violationLimit > 0 ? Math.min(100, Math.round((currentViolationCount / violationLimit) * 100)) : 0;

        if (violationCountEl) violationCountEl.textContent = String(currentViolationCount);
        if (violationFillEl) violationFillEl.style.width = fillPct + '%';

        if (violationPanelEl) {
          violationPanelEl.classList.toggle('is-danger', remaining <= 1);
          violationPanelEl.classList.toggle('is-warning', remaining === 2);
        }

        if (violationNoteEl) {
          if (remaining > 1) {
            violationNoteEl.textContent = examSessionI18n.violation_remaining.replace(':count', String(remaining));
          } else if (remaining === 1) {
            violationNoteEl.textContent = examSessionI18n.violation_last_warning;
          } else {
            violationNoteEl.textContent = examSessionI18n.violation_exhausted;
          }
        }
      }

      // Natija sahifasidan "Orqaga" (BFCache) — eski imtihon ko‘rinmasin; server holatini qayta olamiz
      window.addEventListener('pageshow', function (ev) {
        if (ev.persisted) {
          window.location.reload();
        }
      });

      function reportRuleViolation(reason) {
        if (disqualifiedNav) return;
        var now = Date.now();
        if (now - lastViolationReportAt < 1400) return;
        lastViolationReportAt = now;
        fetch(violationUrl, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
          },
          body: JSON.stringify({
            reason: reason || 'generic',
          }),
        })
          .then(function (r) { return r.json().catch(function () { return {}; }); })
          .then(function (data) {
            if (data.redirect) {
              disqualifiedNav = true;
              window.location.href = data.redirect;
              return;
            }

            var count = Number(data.count || 0);
            if (!Number.isFinite(count) || count <= 0) return;
            updateViolationUi(count);

            var remaining = Math.max(0, violationLimit - count);

            if (remaining === 1) {
              showLockedLastChanceModal();
            }
          })
          .catch(function () {});
      }

      function playViolationSound() {
        try {
          // Short professional alert beep (Base64 WAV)
          var audioContext = new (window.AudioContext || window.webkitAudioContext)();
          var osc = audioContext.createOscillator();
          var gain = audioContext.createGain();
          osc.type = 'sine';
          osc.frequency.setValueAtTime(880, audioContext.currentTime); // High pitched beep
          gain.gain.setValueAtTime(0.1, audioContext.currentTime);
          gain.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.5);
          osc.connect(gain);
          gain.connect(audioContext.destination);
          osc.start();
          osc.stop(audioContext.currentTime + 0.5);
        } catch (e) {
          // Fallback if AudioContext is blocked or not supported
          console.warn("Audio feedback failed:", e);
        }
      }

      function warnAndReport(e, reason) {
        if (e) {
          e.preventDefault();
          e.stopPropagation();
        }
        playViolationSound();
        reportRuleViolation(reason);
      }

      function showRuleModal() {
        if (!modal) return;
        modal.hidden = false;
        syncBodyLock();
      }

      function hideRuleModal() {
        if (!modal || modalLockActive) return;
        modal.hidden = true;
        if (modalTitleEl) modalTitleEl.textContent = defaultModalTitle;
        if (modalTextEl) modalTextEl.innerHTML = defaultModalText;
        if (modalOkBtn) modalOkBtn.textContent = defaultModalOkText;
        syncBodyLock();
      }

      function showLockedLastChanceModal() {
        showFocusGuard(
          examSessionI18n.last_chance_title,
          examSessionI18n.last_chance_text,
          fullscreenSupported ? examSessionI18n.focus_resume_retry : examSessionI18n.understood
        );
      }

      function showScreenshotWarn(e) {
        showFocusGuard(
          examSessionI18n.screenshot_title,
          examSessionI18n.screenshot_text,
          fullscreenSupported ? examSessionI18n.reenter : examSessionI18n.continue_btn
        );
        warnAndReport(e, 'screen-capture');
      }

      function maybeContextWarn(reason) {
        var now = Date.now();
        if (now - lastContextWarnAt < 1000) return;
        lastContextWarnAt = now;
        warnAndReport(null, reason || 'context-loss');
      }

      function requestResumeProtectedMode(title, text, reason) {
        if (disqualifiedNav || examSubmitLocked || isTransientIgnoreActive()) return;
        showFocusGuard(title, text, fullscreenSupported ? examSessionI18n.focus_resume_retry : examSessionI18n.continue_btn);
        maybeContextWarn(reason);
      }

      async function resumeProtectedMode() {
        setTransientIgnore(1500);

        if (fullscreenSupported && !isFullscreenActive()) {
          var fullOk = await requestProtectedFullscreen();

          if (!fullOk && !isFullscreenActive()) {
            showFocusGuard(
              examSessionI18n.fullscreen_required_title,
              examSessionI18n.fullscreen_required_text,
              examSessionI18n.retry_fullscreen
            );
            return;
          }
        }

        hideFocusGuard();
      }

      document.getElementById('exam-rule-modal-ok')?.addEventListener('click', hideRuleModal);
      modal?.querySelector('.exam-rule-modal-backdrop')?.addEventListener('click', function (e) {
        if (modalLockActive) {
          e.preventDefault();
          e.stopPropagation();
          return;
        }
        hideRuleModal();
      });
      document.addEventListener('keydown', function (e) {
        if (e.key !== 'Escape' || !modal || modal.hidden) return;
        e.preventDefault();
        if (!modalLockActive) {
          hideRuleModal();
        }
      });

      buildWatermark();
      updateViolationUi(currentViolationCount);

      if (securityEnabled) {
        document.addEventListener('visibilitychange', function () {
          if (document.hidden) {
            requestResumeProtectedMode(
              examSessionI18n.hidden_title,
              examSessionI18n.hidden_text,
              'visibility-hidden'
            );
          }
        });

        window.addEventListener('beforeprint', function () {
          requestResumeProtectedMode(
            examSessionI18n.print_title,
            examSessionI18n.print_text,
            'print-attempt'
          );
        });

        window.addEventListener('blur', function () {
          if (document.hidden || isTransientIgnoreActive()) return;
          requestResumeProtectedMode(
            examSessionI18n.blur_title,
            examSessionI18n.blur_text,
            'window-blur'
          );
        });

        window.addEventListener('pagehide', function () {
          if (disqualifiedNav || examSubmitLocked || isTransientIgnoreActive()) return;
          maybeContextWarn('pagehide');
        });

        function handleFullscreenChange() {
          if (isTransientIgnoreActive()) return;
          if (fullscreenSupported && !isFullscreenActive() && !document.hidden) {
            requestResumeProtectedMode(
              examSessionI18n.exit_fullscreen_title,
              examSessionI18n.exit_fullscreen_text,
              'fullscreen-exit'
            );
            return;
          }
          if (isFullscreenActive() && focusGuard && !focusGuard.hidden && document.visibilityState === 'visible') {
            hideFocusGuard();
          }
        }
        document.addEventListener('fullscreenchange', handleFullscreenChange);
        document.addEventListener('webkitfullscreenchange', handleFullscreenChange);
        document.addEventListener('msfullscreenchange', handleFullscreenChange);

        document.addEventListener('keydown', function (e) {
          var key = e.key || '';
          var kl = key.toLowerCase();
          var ctrl = e.ctrlKey || e.metaKey;
          var meta = e.metaKey;

          if (key === 'PrintScreen' || key === 'Print' || key === 'F13' || key === 'Snapshot' || e.keyCode === 44) {
            showScreenshotWarn(e); return;
          }
          if (e.altKey && (key === 'PrintScreen' || e.keyCode === 44)) {
            showScreenshotWarn(e); return;
          }
          if (key === 'F12') { e.preventDefault(); return; }
          if (meta && e.shiftKey && (kl === '3' || kl === '4' || kl === '5')) {
            showScreenshotWarn(e); return;
          }
          if (e.shiftKey && meta && kl === 's') { showScreenshotWarn(e); return; }
          if (ctrl && kl === 'p') {
            e.preventDefault();
            requestResumeProtectedMode(examSessionI18n.print_title, examSessionI18n.print_text, 'print-shortcut');
            return;
          }
          if (ctrl && e.shiftKey && kl === 's') {
            e.preventDefault();
            requestResumeProtectedMode(examSessionI18n.save_forbidden_title, examSessionI18n.save_forbidden_text, 'save-shortcut');
            return;
          }
          if (ctrl && ['c', 'x', 'u', 's'].indexOf(kl) !== -1) { e.preventDefault(); return; }
          if (ctrl && e.shiftKey && ['i', 'j', 'c', 'p'].indexOf(kl) !== -1) { e.preventDefault(); return; }
        }, true);

        document.addEventListener('keyup', function (e) {
          var key = e.key || '';
          if (key === 'PrintScreen' || key === 'Print' || key === 'F13' || key === 'Snapshot' || e.keyCode === 44) {
            showScreenshotWarn(e);
          }
        }, true);

        if (root) {
          ['copy', 'cut', 'contextmenu', 'dragstart', 'paste', 'selectstart'].forEach(function (ev) {
            root.addEventListener(ev, function (e) {
              if (e.target && e.target.closest('.exam-text-answer-field')) {
                if (ev === 'copy' || ev === 'cut' || ev === 'paste') e.preventDefault();
                return;
              }
              e.preventDefault();
            }, true);
          });
        }
        document.addEventListener('paste', function (e) {
          if (e.target && e.target.closest('.exam-text-answer-field')) { e.preventDefault(); return; }
          e.preventDefault();
        }, true);

        focusGuardResumeBtn?.addEventListener('click', function () {
          resumeProtectedMode();
        });

        if (fullscreenSupported) {
          showFocusGuard(
            examSessionI18n.start_protected_title,
            examSessionI18n.start_protected_text,
            examSessionI18n.focus_resume
          );
        }
      }
    })();

    const examSessionI18n = @json($sessionI18n);
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

      var isFirst = idx === 0;
      var isLast = idx === steps.length - 1;

      if (stepCurrentEl) stepCurrentEl.textContent = String(idx + 1);

      if (btnPrev) {
        btnPrev.disabled = isFirst;
        btnPrev.style.opacity = isFirst ? '0.5' : '1';
        btnPrev.style.cursor = isFirst ? 'not-allowed' : 'pointer';
      }

      if (btnNext) {
        // Instead of hiding, we can disable it on the last step or keep it hidden if Finish is shown.
        // User asked to disable it, so let's keep it visible but disabled on the last step.
        btnNext.disabled = isLast;
        btnNext.style.opacity = isLast ? '0.5' : '1';
        btnNext.style.cursor = isLast ? 'not-allowed' : 'pointer';
        // If we want to show Finish button alongside or instead:
        btnNext.hidden = isLast;
      }

      if (btnFinish) {
        btnFinish.hidden = !isLast;
      }

      // Add active state to navigation grid if it exists
      var gridItems = document.querySelectorAll('.exam-grid-item');
      gridItems.forEach(function(item, i) {
        item.classList.toggle('is-active', i === idx);
      });
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

    var examSubmitLocked = false;
    var examTimerIntervalId = null;
    var examTimerExpiredHandled = false;

    function showExamGradingLoader() {
      var loader = document.querySelector('.prime-exam-loader');
      if (!loader) {
        loader = document.createElement('div');
        loader.className = 'prime-exam-loader';
        loader.innerHTML =
          '<div class="prime-grading-container">' +
          '<div class="prime-grading-title">' + examSessionI18n.grading_title + '</div>' +
          '<div class="prime-grading-bar-wrap"><div class="prime-grading-bar"></div></div>' +
          '</div>';
        document.body.appendChild(loader);
      }
      window.setTimeout(function () {
        loader.classList.add('is-active');
      }, 50);
    }

    /** POST → JSON (ExamController@submit) → ovoz/konfetti (public-layout.js), keyin redirect */
    function submitExamWithFeedback() {
      if (examSubmitLocked) return;
      examSubmitLocked = true;

      var form = document.getElementById('submit-form');
      if (!form) return;

      showExamGradingLoader();

      var tokenEl = form.querySelector('input[name="_token"]');
      fetch(form.action, {
        method: 'POST',
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          Accept: 'application/json',
          'X-CSRF-TOKEN': tokenEl ? tokenEl.value : '',
        },
        body: new FormData(form),
      })
        .then(function (r) {
          return r.json();
        })
        .then(function (data) {
          if (!data || !data.redirect) {
            form.submit();
            return;
          }
          var passed = data.passed;
          window.setTimeout(function () {
            if (passed === true) {
              if (typeof window.playPrimeResultPass === 'function') window.playPrimeResultPass();
              if (typeof window.playPrimeConfetti === 'function') {
                window.playPrimeConfetti(window.innerWidth / 2, window.innerHeight / 2, true);
              }
              document.body.classList.add('prime-success-glow');
            } else {
              if (typeof window.playPrimeResultFail === 'function') window.playPrimeResultFail();
              document.body.classList.add('prime-failure-shake');
            }
            window.setTimeout(function () {
              window.location.href = data.redirect;
            }, 2400);
          }, 1200);
        })
        .catch(function () {
          form.submit();
        });
    }

    document.getElementById('exam-finish-confirm-submit')?.addEventListener('click', async function () {
      await flushPendingTextAnswers();
      hideFinishConfirmModal();
      submitExamWithFeedback();
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

      if (diff <= 0 && !examTimerExpiredHandled) {
        examTimerExpiredHandled = true;
        if (examTimerIntervalId) {
          clearInterval(examTimerIntervalId);
          examTimerIntervalId = null;
        }
        flushPendingTextAnswers().finally(function () {
          submitExamWithFeedback();
        });
      }
    }

    function updateProgress() {
      const checkedInputs = document.querySelectorAll('.exam-option input[type=radio]:checked');
      const answeredQuestionIds = new Set();

      checkedInputs.forEach(function(input) {
        // name is "q_{questionId}"
        const qId = input.name.replace('q_', '');
        answeredQuestionIds.add(qId);
      });

      const textFields = document.querySelectorAll('.exam-text-answer-field');
      textFields.forEach(function(field) {
        if (field.value.trim() !== '') {
          answeredQuestionIds.add(field.dataset.textQuestionId);
        }
      });

      const answered = answeredQuestionIds.size;
      const fill = document.getElementById('exam-progress-fill');
      const num = document.getElementById('exam-answered-num');
      if (num) num.textContent = answered;
      if (fill && totalQuestions > 0) {
        fill.style.width = Math.min(100, Math.round(answered / totalQuestions * 100)) + '%';
      }

      // Update Grid Items
      const gridItems = document.querySelectorAll('.exam-grid-item');
      gridItems.forEach(function(item) {
        const qId = item.dataset.gridQuestionId;
        item.classList.toggle('is-answered', answeredQuestionIds.has(qId));
      });
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
        alert(examSessionI18n.save_error);
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
        setTextSaveState(questionId, error.message || examSessionI18n.save_error, 'is-error');
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
    examTimerIntervalId = setInterval(tick, 1000);
    updateProgress();
    showStep(0);
  </script>
</x-loyouts.main>
