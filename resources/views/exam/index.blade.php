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
          <p class="exam-hero-lead exam-hero-hint">
            <i class="fa-solid fa-lock"></i>
            Sinfingizga mos kelmagan imtihonlar ham ko'rsatiladi, lekin ular qulflangan bo'ladi.
          </p>
        @endif
      </header>

      @if($isParent ?? false)
        <div class="exam-card exam-parent-alert">
          <p class="exam-parent-alert-title">
            <i class="fa-solid fa-user-shield"></i>
            Siz ota-ona sifatida ro'yxatdan o'tgansiz
          </p>
          <p class="exam-parent-alert-text">Ota-onalar imtihon topshira olmaydi. Imtihonlar faqat o'quvchilar uchun.</p>
        </div>
      @endif

      @if($exams->isEmpty())
        <div class="exam-grid">
          <div class="exam-empty">
            <p class="exam-empty-message"><i class="fa-solid fa-inbox"></i> Hozircha faol imtihon yo'q.</p>
          </div>
        </div>
      @else
        <div class="exam-filter-panel filter-shell" id="exam-filter-panel" data-sticky-filter>
          <div class="exam-filter-row">
            <div class="exam-filter-field">
              <label class="exam-filter-label" for="exam-filter-q">Nom bo'yicha qidirish</label>
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
                <option value="scheduled">Reja: kutilmoqda</option>
                <option value="done">Topshirilgan</option>
                <option value="locked">Qulflangan</option>
              </select>
            </div>
            <div class="exam-filter-field">
              <label class="exam-filter-label" for="exam-filter-sort">Saralash</label>
              <select id="exam-filter-sort" class="exam-filter-select">
                <option value="id-desc">Yangi avval</option>
                <option value="id-asc">Eski avval</option>
                <option value="title-asc">Nom (A -> Z)</option>
                <option value="title-desc">Nom (Z -> A)</option>
                <option value="duration-asc">Vaqt: qisqa -> uzoq</option>
                <option value="duration-desc">Vaqt: uzoq -> qisqa</option>
                <option value="points-desc">Ball: ko'p -> kam</option>
                <option value="points-asc">Ball: kam -> ko'p</option>
              </select>
            </div>
          </div>
          <div class="filter-toolbar">
            <div class="filter-active-tags exam-filter-chip-row" id="exam-filter-tags"></div>
            <button type="button" class="filter-reset-link" id="exam-filter-reset">
              <i class="fa-solid fa-rotate-left"></i>
              Filtrlarni tozalash
            </button>
          </div>
        </div>
        <p class="exam-filter-count" id="exam-filter-count" aria-live="polite"></p>

        <div class="exam-grid" id="exam-grid">
          @foreach($exams as $exam)
            @php
              $row = $resultByExam[$exam->id] ?? null;
              $isLocked = ! $row && ! $exam->allowsUser($user);
              $isDone = $row && in_array($row->status, ['submitted', 'expired'], true);
              $isScheduled = ! $isLocked && ! $isDone && ! $exam->isOpenForStarting($user);
              $cardState = $isLocked ? 'locked' : ($isDone ? 'done' : ($isScheduled ? 'scheduled' : 'open'));
              $gradeNums = collect($exam->allowedGradeItems())
                ->map(function ($gradeItem) {
                    return preg_match('/^(\\d{1,2})-/', (string) $gradeItem, $matches) ? $matches[1] : null;
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
              @elseif($isScheduled)
                <span class="exam-hero-badge exam-hero-badge--scheduled">
                  <i class="fa-regular fa-calendar-days"></i>
                  {{ $exam->availableFromLabel() }} dan boshlash
                </span>
                <p class="exam-card-locked-text exam-card-note">
                  Reja sanasi kelguncha imtihonni boshlash mumkin emas.
                </p>
                <a href="{{ route('exam.start.page', $exam) }}" class="exam-btn-secondary">
                  Batafsil
                  <i class="fa-solid fa-arrow-right"></i>
                </a>
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
          <p class="exam-empty-message">
            <i class="fa-solid fa-filter-circle-xmark"></i>
            Filtr bo'yicha imtihon topilmadi. Boshqa shartlarni tanlang.
          </p>
        </div>
      @endif
    </div>
  </main>
</x-loyouts.main>
