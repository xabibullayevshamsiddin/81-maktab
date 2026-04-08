<x-loyouts.main title="Onlayn imtihonlar">
  <main class="news exam-page">
    <div class="exam-page-inner">
      <header class="exam-hero">
        <span class="exam-hero-badge">
          <i class="fa-solid fa-graduation-cap"></i>
          Onlayn imtihon
        </span>
        <h1 class="exam-title js-split-text">Mavjud imtihonlar</h1>
        <p class="exam-hero-lead">
          Har bir imtihonni faqat <strong>bir marta</strong> topshirishingiz mumkin. Vaqtingiz tugagach, javoblaringiz avtomatik yuboriladi.
        </p>
        @if($hasRestrictedExams)
          <p class="exam-hero-lead exam-hero-hint" style="font-size:0.95rem;margin-top:8px;">
            <i class="fa-solid fa-lock" style="opacity:0.85;"></i>
            Sinfingizga mos kelmagan imtihonlar ham ko'rsatiladi, lekin ular qulflangan bo'ladi.
          </p>
        @endif
      </header>

      @if($exams->isEmpty())
        <div class="exam-grid">
          <div class="exam-empty">
            <p style="margin:0;font-size:16px;"><i class="fa-solid fa-inbox" style="opacity:0.5;"></i> Hozircha faol imtihon yo'q.</p>
          </div>
        </div>
      @else
        <div class="exam-filter-panel" id="exam-filter-panel">
          <div class="exam-filter-row">
            <div class="exam-filter-field">
              <label class="exam-filter-label" for="exam-filter-q">Nom bo‘yicha qidirish</label>
              <input type="search" id="exam-filter-q" class="exam-filter-input" placeholder="Imtihon nomi..." autocomplete="off">
            </div>
            <div class="exam-filter-field">
              <label class="exam-filter-label" for="exam-filter-grade">Sinf (masalan 3-sinf)</label>
              <select id="exam-filter-grade" class="exam-filter-select">
                <option value="">Barcha sinflar</option>
                @foreach(range(1, 11) as $g)
                  <option value="{{ $g }}">{{ $g }}-sinf</option>
                @endforeach
              </select>
            </div>
          </div>
          <div class="exam-filter-row">
            <div class="exam-filter-field">
              <label class="exam-filter-label" for="exam-filter-state">Holat</label>
              <select id="exam-filter-state" class="exam-filter-select">
                <option value="">Hammasi</option>
                <option value="open">Boshlash mumkin</option>
                <option value="done">Topshirilgan</option>
                <option value="locked">Qulflangan</option>
              </select>
            </div>
            <div class="exam-filter-field">
              <label class="exam-filter-label" for="exam-filter-sort">Saralash</label>
              <select id="exam-filter-sort" class="exam-filter-select">
                <option value="id-desc">Yangi avval</option>
                <option value="id-asc">Eski avval</option>
                <option value="title-asc">Nom (A → Z)</option>
                <option value="title-desc">Nom (Z → A)</option>
                <option value="duration-asc">Vaqt: qisqa → uzoq</option>
                <option value="duration-desc">Vaqt: uzoq → qisqa</option>
                <option value="points-desc">Ball: ko‘p → kam</option>
                <option value="points-asc">Ball: kam → ko‘p</option>
              </select>
            </div>
          </div>
        </div>
        <p class="exam-filter-count" id="exam-filter-count" aria-live="polite"></p>

        <div class="exam-grid" id="exam-grid">
        @foreach($exams as $exam)
          @php
            $row = $resultByExam[$exam->id] ?? null;
            $isLocked = ! $row && ! $exam->allowsUser($user);
            $isDone = $row && in_array($row->status, ['submitted', 'expired'], true);
            $cardState = $isLocked ? 'locked' : ($isDone ? 'done' : 'open');
            $gradeNums = collect($exam->allowedGradeItems())
              ->map(function ($gg) {
                return preg_match('/^(\d{1,2})-/', (string) $gg, $m) ? $m[1] : null;
              })
              ->filter()
              ->unique()
              ->sort()
              ->values()
              ->implode(',');
            $searchText = mb_strtolower($exam->title, 'UTF-8');
          @endphp
          <article
            class="exam-card {{ $isLocked ? 'exam-card--locked' : '' }}"
            data-exam-card
            data-exam-id="{{ $exam->id }}"
            data-search-text="{{ e($searchText) }}"
            data-title-sort="{{ e($exam->title) }}"
            data-grade-nums="{{ e($gradeNums) }}"
            data-unrestricted="{{ $exam->hasGradeRestrictions() ? '0' : '1' }}"
            data-duration="{{ (int) $exam->duration_minutes }}"
            data-points="{{ (int) ($exam->total_points ?? 0) }}"
            data-state="{{ $cardState }}"
          >
            @if($isLocked)
              <span class="exam-card-lock-badge">
                <i class="fa-solid fa-lock"></i>
                Qulflangan
              </span>
            @endif

            <h2 class="exam-card-title">{{ $exam->title }}</h2>
            <div class="exam-card-meta">
              <span class="exam-meta-pill"><i class="fa-regular fa-clock"></i> {{ $exam->duration_minutes }} daq.</span>
              <span class="exam-meta-pill"><i class="fa-solid fa-star"></i> {{ $exam->total_points ?? '-' }} ball</span>
            </div>
            @if($exam->hasGradeRestrictions())
              <p class="exam-card-grade-note">
                <i class="fa-solid fa-user-graduate"></i>
                Ruxsat etilgan sinflar: <strong>{{ $exam->allowedGradesLabel() }}</strong>
              </p>
            @endif

            @if($isLocked)
              <div class="exam-card-locked-box">
                <p class="exam-card-locked-title">
                  <i class="fa-solid fa-shield-lock"></i>
                  Bu imtihon siz uchun yopiq
                </p>
                <p class="exam-card-locked-text">
                  Faqat <strong>{{ $exam->allowedGradesLabel() }}</strong> sinflar topshira oladi.
                </p>
                <span class="exam-btn-locked">
                  <i class="fa-solid fa-lock"></i>
                  Sinf mos emas
                </span>
              </div>
            @elseif($row && in_array($row->status, ['submitted', 'expired'], true))
              <div class="exam-card-actions">
                <span class="exam-tag-done"><i class="fa-solid fa-circle-check"></i> Topshirilgan</span>
                <a href="{{ route('exam.result.show', $row) }}" class="exam-btn-secondary">
                  <i class="fa-solid fa-chart-simple"></i> Natijani ko'rish
                </a>
              </div>
            @else
              <a href="{{ route('exam.start.page', $exam) }}" class="exam-btn-primary">
                Boshlash
                <i class="fa-solid fa-arrow-right"></i>
              </a>
            @endif
          </article>
        @endforeach
        </div>

        <div class="exam-empty exam-filter-zero" id="exam-filter-zero" hidden>
          <p style="margin:0;font-size:16px;"><i class="fa-solid fa-filter-circle-xmark" style="opacity:0.55;"></i> Filtr bo‘yicha imtihon topilmadi. Boshqa shartlarni tanlang.</p>
        </div>

        <script>
          (function () {
            var grid = document.getElementById('exam-grid');
            var qEl = document.getElementById('exam-filter-q');
            var gradeEl = document.getElementById('exam-filter-grade');
            var stateEl = document.getElementById('exam-filter-state');
            var sortEl = document.getElementById('exam-filter-sort');
            var countEl = document.getElementById('exam-filter-count');
            var zeroEl = document.getElementById('exam-filter-zero');
            if (!grid || !qEl || !gradeEl || !stateEl || !sortEl) return;

            var cards = Array.prototype.slice.call(grid.querySelectorAll('[data-exam-card]'));
            var total = cards.length;

            function matches(card) {
              var t = (qEl.value || '').trim().toLowerCase();
              if (t) {
                var hay = card.getAttribute('data-search-text') || '';
                if (hay.indexOf(t) === -1) return false;
              }
              var gv = gradeEl.value;
              if (gv) {
                if (card.getAttribute('data-unrestricted') !== '1') {
                  var nums = (card.getAttribute('data-grade-nums') || '').split(',').filter(Boolean);
                  if (nums.indexOf(gv) === -1) return false;
                }
              }
              var sv = stateEl.value;
              if (sv && card.getAttribute('data-state') !== sv) return false;
              return true;
            }

            function sortMatched(list) {
              var mode = sortEl.value || 'id-desc';
              list.sort(function (a, b) {
                if (mode === 'id-desc') {
                  return parseInt(b.getAttribute('data-exam-id'), 10) - parseInt(a.getAttribute('data-exam-id'), 10);
                }
                if (mode === 'id-asc') {
                  return parseInt(a.getAttribute('data-exam-id'), 10) - parseInt(b.getAttribute('data-exam-id'), 10);
                }
                if (mode === 'title-asc' || mode === 'title-desc') {
                  var ta = a.getAttribute('data-title-sort') || '';
                  var tb = b.getAttribute('data-title-sort') || '';
                  var c = ta.localeCompare(tb, undefined, { sensitivity: 'base' });
                  return mode === 'title-desc' ? -c : c;
                }
                if (mode === 'duration-asc' || mode === 'duration-desc') {
                  var da = parseInt(a.getAttribute('data-duration'), 10) || 0;
                  var db = parseInt(b.getAttribute('data-duration'), 10) || 0;
                  return mode === 'duration-desc' ? db - da : da - db;
                }
                if (mode === 'points-asc' || mode === 'points-desc') {
                  var pa = parseInt(a.getAttribute('data-points'), 10) || 0;
                  var pb = parseInt(b.getAttribute('data-points'), 10) || 0;
                  return mode === 'points-desc' ? pb - pa : pa - pb;
                }
                return 0;
              });
            }

            function apply() {
              var matched = cards.filter(matches);
              sortMatched(matched);
              var hidden = cards.filter(function (c) { return matched.indexOf(c) === -1; });
              matched.forEach(function (c) {
                grid.appendChild(c);
                c.style.display = '';
              });
              hidden.forEach(function (c) {
                grid.appendChild(c);
                c.style.display = 'none';
              });
              if (countEl) {
                countEl.textContent = matched.length === total
                  ? 'Jami: ' + total + ' ta imtihon'
                  : 'Ko‘rsatilmoqda: ' + matched.length + ' / ' + total;
              }
              if (zeroEl) {
                zeroEl.hidden = matched.length > 0;
              }
              grid.style.display = matched.length > 0 ? '' : 'none';
            }

            [qEl, gradeEl, stateEl, sortEl].forEach(function (el) {
              el.addEventListener('input', apply);
              el.addEventListener('change', apply);
            });
            apply();
          })();
        </script>
      @endif
    </div>
  </main>
</x-loyouts.main>
