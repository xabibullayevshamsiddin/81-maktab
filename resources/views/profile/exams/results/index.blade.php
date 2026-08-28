<x-layouts.main title="Imtihon Natijalari">
@push('page_styles')
<style>
/* ====================================================================
   Profile Exam Results — Modern Glassmorphism & Responsive Layout
   ==================================================================== */
.results-page-wrapper {
  padding: 30px 0 70px;
}

.results-hero-bar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 16px;
  margin-bottom: 24px;
}

.results-back-btn {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 8px 18px;
  border-radius: 12px;
  font-size: 0.9rem;
  font-weight: 600;
  color: var(--text-color, #1e293b);
  background: var(--card-bg, rgba(255, 255, 255, 0.8));
  border: 1px solid var(--border-color, rgba(148, 163, 184, 0.2));
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
  text-decoration: none;
  transition: all 0.25s ease;
}

.results-back-btn:hover {
  transform: translateX(-3px);
  color: var(--primary-color, #4f46e5);
  border-color: var(--primary-color, #4f46e5);
}

.results-action-buttons {
  display: flex;
  align-items: center;
  gap: 10px;
  flex-wrap: wrap;
}

.btn-export-excel {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 8px 16px;
  border-radius: 12px;
  font-weight: 700;
  font-size: 0.85rem;
  background: linear-gradient(135deg, #10b981, #059669);
  color: #fff !important;
  border: none;
  text-decoration: none;
  box-shadow: 0 4px 12px rgba(16, 185, 129, 0.25);
  transition: all 0.2s ease;
}

.btn-export-excel:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(16, 185, 129, 0.35);
}

.btn-print-action {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 8px 16px;
  border-radius: 12px;
  font-weight: 600;
  font-size: 0.85rem;
  background: var(--card-bg, rgba(255, 255, 255, 0.8));
  color: var(--text-color, #334155);
  border: 1px solid var(--border-color, rgba(148, 163, 184, 0.25));
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-print-action:hover {
  background: rgba(99, 102, 241, 0.08);
  color: #4f46e5;
}

/* Glass Main Card */
.results-glass-card {
  background: var(--card-bg, rgba(255, 255, 255, 0.9));
  border: 1px solid var(--border-color, rgba(148, 163, 184, 0.2));
  border-radius: 20px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.04);
  backdrop-filter: blur(16px);
  -webkit-backdrop-filter: blur(16px);
  overflow: hidden;
}

/* Filter Controls Box */
.results-filter-box {
  padding: 20px 24px;
  border-bottom: 1px solid var(--border-soft, rgba(148, 163, 184, 0.12));
  display: flex;
  flex-wrap: wrap;
  gap: 16px;
  align-items: center;
  justify-content: space-between;
}

/* Desktop Table */
.results-table-responsive {
  width: 100%;
  overflow-x: auto;
}

.results-modern-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.92rem;
  text-align: left;
}

.results-modern-table thead tr {
  background: var(--table-head-bg, rgba(241, 245, 249, 0.8));
  border-bottom: 1px solid var(--border-color, rgba(148, 163, 184, 0.2));
}

.results-modern-table th {
  padding: 16px 18px;
  font-size: 0.82rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.04em;
  color: var(--muted-text, #64748b);
  white-space: nowrap;
}

.results-modern-table tbody tr {
  border-bottom: 1px solid var(--border-soft, rgba(148, 163, 184, 0.12));
  transition: background 0.2s ease;
}

.results-modern-table tbody tr:hover {
  background: var(--table-row-hover, rgba(99, 102, 241, 0.03));
}

.results-modern-table td {
  padding: 16px 18px;
  vertical-align: middle;
  color: var(--text-color, #1e293b);
}

/* User Info Pill */
.student-profile-cell {
  display: flex;
  align-items: center;
  gap: 12px;
  min-width: 200px;
}

.student-avatar-badge {
  width: 38px;
  height: 38px;
  border-radius: 12px;
  background: linear-gradient(135deg, #6366f1, #8b5cf6);
  color: #fff;
  font-weight: 700;
  font-size: 0.9rem;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  box-shadow: 0 4px 10px rgba(99, 102, 241, 0.25);
}

.student-meta-info {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.student-name {
  font-weight: 700;
  color: var(--text-color, #0f172a);
  font-size: 0.95rem;
}

.student-email {
  font-size: 0.8rem;
  color: var(--muted-text, #64748b);
}

/* Score Pill */
.score-pill-modern {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 5px 12px;
  border-radius: 10px;
  font-weight: 700;
  font-size: 0.88rem;
  background: rgba(99, 102, 241, 0.08);
  color: #4f46e5;
  border: 1px solid rgba(99, 102, 241, 0.2);
}

/* Result Tags */
.result-status-tag {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  padding: 4px 10px;
  border-radius: 999px;
  font-size: 0.78rem;
  font-weight: 700;
}

.result-tag-pass { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
.result-tag-fail { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
.result-tag-pending { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; }

.status-tag-submitted { background: rgba(16, 185, 129, 0.12); color: #059669; }
.status-tag-started { background: rgba(245, 158, 11, 0.12); color: #d97706; }
.status-tag-expired { background: rgba(239, 68, 68, 0.12); color: #dc2626; }

.btn-view-result {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 6px 14px;
  border-radius: 10px;
  font-size: 0.82rem;
  font-weight: 700;
  background: linear-gradient(135deg, #4f46e5, #6366f1);
  color: #fff !important;
  text-decoration: none;
  transition: all 0.2s ease;
}

.btn-view-result:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
}

/* ====================================================================
   Mobile Dedicated Cards Layout (< 992px)
   ==================================================================== */
.results-mobile-list {
  display: none;
  flex-direction: column;
  gap: 16px;
  padding: 16px;
}

.result-card-mobile {
  background: var(--card-bg, #ffffff);
  border: 1px solid var(--border-color, rgba(148, 163, 184, 0.25));
  border-radius: 16px;
  padding: 16px;
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.03);
  display: flex;
  flex-direction: column;
  gap: 14px;
}

.result-card-mobile__header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 10px;
  border-bottom: 1px solid var(--border-soft, rgba(148, 163, 184, 0.12));
  padding-bottom: 12px;
}

.result-card-mobile__student {
  display: flex;
  align-items: center;
  gap: 12px;
}

.result-card-mobile__grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 10px 14px;
  background: var(--detail-bg, rgba(241, 245, 249, 0.5));
  border-radius: 12px;
  padding: 12px;
}

.result-card-mobile__item {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.result-card-mobile__label {
  font-size: 0.72rem;
  font-weight: 700;
  text-transform: uppercase;
  color: var(--muted-text, #64748b);
  letter-spacing: 0.03em;
}

.result-card-mobile__val {
  font-size: 0.88rem;
  font-weight: 600;
  color: var(--text-color, #0f172a);
}

.result-card-mobile__actions {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 8px;
  padding-top: 6px;
  border-top: 1px solid var(--border-soft, rgba(148, 163, 184, 0.12));
}

@media (max-width: 991px) {
  .results-table-responsive {
    display: none !important;
  }
  .results-mobile-list {
    display: flex !important;
  }
}

/* ====================================================================
   Dark Mode Overrides
   ==================================================================== */
:root[data-theme='dark'] .results-glass-card {
  background: linear-gradient(180deg, rgba(15, 27, 45, 0.95), rgba(10, 19, 33, 0.98)) !important;
  border-color: rgba(255, 255, 255, 0.08) !important;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.25) !important;
}

:root[data-theme='dark'] .results-back-btn,
:root[data-theme='dark'] .btn-print-action {
  background: rgba(255, 255, 255, 0.05) !important;
  border-color: rgba(255, 255, 255, 0.1) !important;
  color: #f1f5f9 !important;
}

:root[data-theme='dark'] .results-back-btn:hover,
:root[data-theme='dark'] .btn-print-action:hover {
  border-color: #818cf8 !important;
  color: #818cf8 !important;
}

:root[data-theme='dark'] .results-modern-table thead tr {
  background: rgba(15, 23, 42, 0.6) !important;
  border-bottom-color: rgba(255, 255, 255, 0.08) !important;
}

:root[data-theme='dark'] .results-modern-table th {
  color: #94a3b8 !important;
}

:root[data-theme='dark'] .results-modern-table tbody tr {
  border-bottom-color: rgba(255, 255, 255, 0.05) !important;
}

:root[data-theme='dark'] .results-modern-table tbody tr:hover {
  background: rgba(255, 255, 255, 0.02) !important;
}

:root[data-theme='dark'] .student-name,
:root[data-theme='dark'] .result-card-mobile__val,
:root[data-theme='dark'] .results-modern-table td {
  color: #f1f5f9 !important;
}

:root[data-theme='dark'] .student-email,
:root[data-theme='dark'] .result-card-mobile__label {
  color: #94a3b8 !important;
}

:root[data-theme='dark'] .score-pill-modern {
  background: rgba(99, 102, 241, 0.15) !important;
  color: #a5b4fc !important;
  border-color: rgba(99, 102, 241, 0.3) !important;
}

:root[data-theme='dark'] .result-tag-pass {
  background: rgba(16, 185, 129, 0.16) !important;
  color: #34d399 !important;
  border-color: rgba(16, 185, 129, 0.3) !important;
}

:root[data-theme='dark'] .result-tag-fail {
  background: rgba(239, 68, 68, 0.16) !important;
  color: #f87171 !important;
  border-color: rgba(239, 68, 68, 0.3) !important;
}

:root[data-theme='dark'] .result-tag-pending {
  background: rgba(245, 158, 11, 0.16) !important;
  color: #fbbf24 !important;
  border-color: rgba(245, 158, 11, 0.3) !important;
}

:root[data-theme='dark'] .result-card-mobile {
  background: rgba(15, 23, 42, 0.6) !important;
  border-color: rgba(255, 255, 255, 0.08) !important;
}

:root[data-theme='dark'] .result-card-mobile__grid {
  background: rgba(15, 23, 42, 0.4) !important;
}
</style>
@endpush

  <section class="news-hero profile-hero">
    <div class="container">
      <div class="news-hero-content prime-reveal">
        <span class="badge"><i class="fa-solid fa-chart-pie me-1"></i> Imtihon Tahlili</span>
        <h1><strong>Imtihon Natijalari</strong></h1>
        <p>Barcha topshirilgan imtihonlar, o'quvchilar ballari va reyting ko'rsatkichlari bu yerda jamlangan.</p>
      </div>
    </div>
  </section>

  <main class="results-page-wrapper">
    <div class="container">

      {{-- Top Actions & Export Bar --}}
      <div class="results-hero-bar">
        <a href="{{ route('profile.exams.index') }}" class="results-back-btn">
          <i class="fa-solid fa-arrow-left"></i> Imtihonlar ro'yxatiga qaytish
        </a>

        @php
          $exportParams = array_filter([
            'exam_id' => $selectedExamId,
            'date_from' => request('date_from'),
            'date_to' => request('date_to'),
          ]);
        @endphp
        <div class="results-action-buttons">
          <a href="{{ route('profile.exams.results.export', $exportParams) }}" class="btn-export-excel">
            <i class="fa-solid fa-file-excel"></i> Excel export
          </a>
          <button type="button" class="btn-print-action" onclick="window.print()">
            <i class="fa-solid fa-print"></i> Chop etish
          </button>
        </div>
      </div>

      {{-- Main Glass Card Container --}}
      <div class="results-glass-card">

        {{-- Filters Bar --}}
        <div class="results-filter-box">
          <form method="get" action="{{ route('profile.exams.results') }}" class="d-flex flex-wrap gap-2 align-items-center" id="results-filter-form" style="flex: 1; min-width: 240px;">
            <div style="flex: 1; min-width: 200px;">
              <select name="exam_id" class="form-select" onchange="this.form.submit()" style="border-radius: 12px; padding: 10px 14px; font-weight: 600;">
                <option value="">— Barcha imtihonlar —</option>
                @foreach($exams as $ex)
                  <option value="{{ $ex->id }}" {{ (string) $selectedExamId === (string) $ex->id ? 'selected' : '' }}>
                    {{ $ex->title }}
                  </option>
                @endforeach
              </select>
            </div>
            @if(request()->filled('q'))
              <input type="hidden" name="q" value="{{ request('q') }}">
            @endif
          </form>

          <div style="flex: 1; max-width: 380px; min-width: 220px;">
            @include('admin.partials.search-bar', [
              'placeholder' => 'Ism, email yoki telefon...',
              'action' => route('profile.exams.results'),
              'hidden' => array_filter(['exam_id' => $selectedExamId, 'date_from' => request('date_from'), 'date_to' => request('date_to')]),
            ])
          </div>
        </div>

        {{-- 1. Desktop Table View (>= 992px) --}}
        <div class="results-table-responsive">
          <table class="results-modern-table">
            <thead>
              <tr>
                <th><i class="fa-solid fa-user me-1"></i> O'quvchi</th>
                @if(!$selectedExamId)
                  <th><i class="fa-solid fa-file-pen me-1"></i> Imtihon</th>
                @endif
                <th><i class="fa-solid fa-chart-simple me-1"></i> Ball (Jami)</th>
                <th><i class="fa-solid fa-clock me-1"></i> Status</th>
                <th><i class="fa-solid fa-shield-halved me-1"></i> Natija</th>
                <th><i class="fa-solid fa-school me-1"></i> Sinf</th>
                <th><i class="fa-solid fa-calendar-days me-1"></i> Sana</th>
                <th class="text-end"><i class="fa-solid fa-sliders me-1"></i> Amallar</th>
              </tr>
            </thead>
            <tbody>
              @forelse($results as $result)
                @php
                  $userName = $result->user->name ?? 'Noma\'lum o\'quvchi';
                  $initials = mb_strtoupper(mb_substr($userName, 0, 1));
                  $statusClass = match($result->status) {
                    'submitted' => 'status-tag-submitted',
                    'started' => 'status-tag-started',
                    'expired' => 'status-tag-expired',
                    default => ''
                  };
                  $statusLabel = match($result->status) {
                    'submitted' => 'Topshirildi',
                    'started' => 'Jarayonda',
                    'expired' => 'Vaqti o\'tdi',
                    default => $result->status
                  };
                @endphp
                <tr>
                  {{-- O'quvchi --}}
                  <td>
                    <div class="student-profile-cell">
                      <div class="student-avatar-badge">{{ $initials }}</div>
                      <div class="student-meta-info">
                        <span class="student-name">{{ $userName }}</span>
                        <span class="student-email">{{ $result->user->phone ?? $result->user->email ?? '-' }}</span>
                      </div>
                    </div>
                  </td>

                  {{-- Imtihon (agar tanlanmagan bo'lsa) --}}
                  @if(!$selectedExamId)
                    <td>
                      <span style="font-weight: 700; color: #4f46e5;">
                        {{ $result->exam->title ?? '—' }}
                        @if($result->exam?->trashed())
                          <small class="text-danger">(O'chirilgan)</small>
                        @endif
                      </span>
                    </td>
                  @endif

                  {{-- Ball --}}
                  <td>
                    <span class="score-pill-modern">
                      <i class="fa-solid fa-star"></i>
                      {{ $result->points_earned ?? 0 }} / {{ $result->points_max ?? 0 }}
                    </span>
                  </td>

                  {{-- Status --}}
                  <td>
                    <span class="result-status-tag {{ $statusClass }}">
                      @if($result->status === 'submitted') <i class="fa-solid fa-circle-check"></i> @endif
                      @if($result->status === 'started') <i class="fa-solid fa-hourglass-half"></i> @endif
                      @if($result->status === 'expired') <i class="fa-solid fa-triangle-exclamation"></i> @endif
                      {{ $statusLabel }}
                    </span>
                  </td>

                  {{-- Natija --}}
                  <td>
                    @if($result->passed === null)
                      <span class="result-status-tag result-tag-pending"><i class="fa-solid fa-clock-rotate-left"></i> Tekshiruvda</span>
                    @elseif($result->passed)
                      <span class="result-status-tag result-tag-pass"><i class="fa-solid fa-square-check"></i> O‘tdi</span>
                    @else
                      <span class="result-status-tag result-tag-fail"><i class="fa-solid fa-circle-xmark"></i> Yiqildi</span>
                    @endif
                  </td>

                  {{-- Sinf --}}
                  <td>
                    <span class="badge" style="background:rgba(99,102,241,0.08); color:#4f46e5; font-weight:700; border:1px solid rgba(99,102,241,0.2);">
                      {{ $result->user_grade ?? $result->user->grade ?? '—' }}
                    </span>
                  </td>

                  {{-- Sana --}}
                  <td>
                    <div class="d-flex flex-column" style="font-size: 0.85rem;">
                      <span style="font-weight: 700;">{{ $result->submitted_at?->format('d.m.Y') ?? '-' }}</span>
                      <span style="color: var(--muted-text, #64748b); font-size: 0.78rem;">{{ $result->submitted_at?->format('H:i') ?? '' }}</span>
                    </div>
                  </td>

                  {{-- Amallar --}}
                  <td class="text-end">
                    <a href="{{ route('profile.exams.results.show', $result) }}" class="btn-view-result">
                      <i class="fa-solid fa-eye"></i> Ko'rish
                    </a>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="{{ $selectedExamId ? 7 : 8 }}" style="padding: 40px 20px; text-align: center; color: var(--muted-text, #64748b);">
                    <i class="fa-solid fa-folder-open mb-2" style="font-size: 2.5rem; opacity: 0.4; display: block;"></i>
                    <span style="font-weight: 600; font-size: 1rem;">Hali birorta ham natija mavjud emas.</span>
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        {{-- 2. Dedicated Mobile Cards List (< 992px) --}}
        <div class="results-mobile-list">
          @forelse($results as $result)
            @php
              $userName = $result->user->name ?? 'Noma\'lum o\'quvchi';
              $initials = mb_strtoupper(mb_substr($userName, 0, 1));
              $statusClass = match($result->status) {
                'submitted' => 'status-tag-submitted',
                'started' => 'status-tag-started',
                'expired' => 'status-tag-expired',
                default => ''
              };
              $statusLabel = match($result->status) {
                'submitted' => 'Topshirildi',
                'started' => 'Jarayonda',
                'expired' => 'Vaqti o\'tdi',
                default => $result->status
              };
            @endphp
            <div class="result-card-mobile">
              {{-- Header: Status & Natija --}}
              <div class="result-card-mobile__header">
                <div>
                  <span class="result-status-tag {{ $statusClass }}">
                    {{ $statusLabel }}
                  </span>
                </div>

                <div>
                  @if($result->passed === null)
                    <span class="result-status-tag result-tag-pending"><i class="fa-solid fa-clock-rotate-left"></i> Tekshiruvda</span>
                  @elseif($result->passed)
                    <span class="result-status-tag result-tag-pass"><i class="fa-solid fa-square-check"></i> O‘tdi</span>
                  @else
                    <span class="result-status-tag result-tag-fail"><i class="fa-solid fa-circle-xmark"></i> Yiqildi</span>
                  @endif
                </div>
              </div>

              {{-- O'quvchi profili --}}
              <div class="result-card-mobile__student">
                <div class="student-avatar-badge">{{ $initials }}</div>
                <div class="student-meta-info">
                  <span class="student-name">{{ $userName }}</span>
                  <span class="student-email">{{ $result->user->phone ?? $result->user->email ?? '-' }}</span>
                </div>
              </div>

              {{-- Parametrlar gridi --}}
              <div class="result-card-mobile__grid">
                @if(!$selectedExamId)
                  <div class="result-card-mobile__item" style="grid-column: span 2;">
                    <span class="result-card-mobile__label">Imtihon:</span>
                    <span class="result-card-mobile__val" style="color:#4f46e5;">{{ $result->exam->title ?? '—' }}</span>
                  </div>
                @endif

                <div class="result-card-mobile__item">
                  <span class="result-card-mobile__label">To'plangan ball:</span>
                  <span class="result-card-mobile__val" style="color:#4f46e5;">
                    ⭐ {{ $result->points_earned ?? 0 }} / {{ $result->points_max ?? 0 }}
                  </span>
                </div>

                <div class="result-card-mobile__item">
                  <span class="result-card-mobile__label">Sinf:</span>
                  <span class="result-card-mobile__val">{{ $result->user_grade ?? $result->user->grade ?? '—' }}</span>
                </div>

                <div class="result-card-mobile__item" style="grid-column: span 2;">
                  <span class="result-card-mobile__label">Topshirilgan vaqt:</span>
                  <span class="result-card-mobile__val" style="font-size:0.82rem;font-weight:500;">
                    📅 {{ $result->submitted_at?->format('d.m.Y H:i') ?? '-' }}
                  </span>
                </div>
              </div>

              {{-- Footer amallari --}}
              <div class="result-card-mobile__actions">
                <span class="text-muted small">ID: #{{ $result->id }}</span>
                <a href="{{ route('profile.exams.results.show', $result) }}" class="btn-view-result">
                  <i class="fa-solid fa-eye"></i> Natijani ko'rish
                </a>
              </div>
            </div>
          @empty
            <div style="padding: 30px 16px; text-align: center; color: var(--muted-text, #64748b);">
              <i class="fa-solid fa-folder-open mb-2" style="font-size: 2.2rem; opacity: 0.4; display: block;"></i>
              <span style="font-weight: 600;">Hali birorta ham natija mavjud emas.</span>
            </div>
          @endforelse
        </div>

        {{-- Pagination --}}
        @if($results->hasPages())
          <div style="padding: 16px 20px; border-top: 1px solid var(--border-soft, rgba(148, 163, 184, 0.12));">
            {{ $results->links() }}
          </div>
        @endif

      </div>
    </div>
  </main>
</x-layouts.main>
