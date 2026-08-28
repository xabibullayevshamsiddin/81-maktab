<x-layouts.main :title="__('public.profile_exams.page_title')">
@push('page_styles')
<style>
/* ====================================================================
   Profile Exams — Modern Glassmorphism & Responsive Layout
   ==================================================================== */
.exams-page-wrapper {
  padding: 30px 0 70px;
}

.exams-hero-bar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 16px;
  margin-bottom: 24px;
}

.exams-back-btn {
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

.exams-back-btn:hover {
  transform: translateX(-3px);
  color: var(--primary-color, #4f46e5);
  border-color: var(--primary-color, #4f46e5);
}

.exams-action-buttons {
  display: flex;
  align-items: center;
  gap: 10px;
  flex-wrap: wrap;
}

.btn-create-exam {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 9px 20px;
  border-radius: 12px;
  font-weight: 700;
  font-size: 0.9rem;
  background: linear-gradient(135deg, #4f46e5, #6366f1);
  color: #fff !important;
  box-shadow: 0 4px 14px rgba(79, 70, 229, 0.3);
  text-decoration: none;
  border: none;
  transition: all 0.25s ease;
}

.btn-create-exam:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(79, 70, 229, 0.4);
}

.btn-results-link {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 9px 18px;
  border-radius: 12px;
  font-weight: 600;
  font-size: 0.9rem;
  background: var(--card-bg, rgba(255, 255, 255, 0.8));
  color: var(--text-color, #334155);
  border: 1px solid var(--border-color, rgba(148, 163, 184, 0.25));
  text-decoration: none;
  transition: all 0.25s ease;
}

.btn-results-link:hover {
  background: rgba(99, 102, 241, 0.08);
  color: #4f46e5;
  border-color: #818cf8;
}

/* Glass Card */
.exams-glass-card {
  background: var(--card-bg, rgba(255, 255, 255, 0.9));
  border: 1px solid var(--border-color, rgba(148, 163, 184, 0.2));
  border-radius: 20px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.04);
  backdrop-filter: blur(16px);
  -webkit-backdrop-filter: blur(16px);
  overflow: hidden;
}

.exams-search-header {
  padding: 20px 24px 0;
}

/* Desktop Table */
.exams-table-responsive {
  width: 100%;
  overflow-x: auto;
  padding: 6px 0 0;
}

.exams-modern-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.88rem;
  text-align: left;
  table-layout: auto;
}

.exams-modern-table thead tr {
  background: var(--table-head-bg, rgba(241, 245, 249, 0.8));
  border-bottom: 1px solid var(--border-color, rgba(148, 163, 184, 0.2));
}

.exams-modern-table th {
  padding: 12px 10px;
  font-size: 0.78rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.03em;
  color: var(--muted-text, #64748b);
  white-space: nowrap;
}

.exams-modern-table tbody tr {
  border-bottom: 1px solid var(--border-soft, rgba(148, 163, 184, 0.12));
  transition: background 0.2s ease;
}

.exams-modern-table tbody tr:hover {
  background: var(--table-row-hover, rgba(99, 102, 241, 0.03));
}

.exams-modern-table td {
  padding: 12px 10px;
  vertical-align: middle;
  color: var(--text-color, #1e293b);
}

/* Title & Pill Styles */
.exam-title-cell {
  display: flex;
  align-items: center;
  gap: 10px;
}

.exam-icon-badge {
  width: 34px;
  height: 34px;
  border-radius: 10px;
  background: linear-gradient(135deg, #0ea5e9, #0284c7);
  color: #fff;
  font-size: 0.95rem;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  box-shadow: 0 3px 8px rgba(14, 165, 233, 0.2);
}

.exam-meta-title {
  font-weight: 700;
  font-size: 0.9rem;
  color: var(--text-color, #0f172a);
  margin-bottom: 1px;
  line-height: 1.3;
}

.exam-meta-subtitle {
  font-size: 0.75rem;
  color: var(--muted-text, #64748b);
}

/* Status & Parameter Badges */
.badge-status-active {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 2px 8px;
  border-radius: 999px;
  font-size: 0.72rem;
  font-weight: 700;
  background: #dcfce7;
  color: #166534;
  border: 1px solid #bbf7d0;
}

.badge-status-incomplete {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 2px 8px;
  border-radius: 999px;
  font-size: 0.72rem;
  font-weight: 700;
  background: #fef3c7;
  color: #92400e;
  border: 1px solid #fde68a;
}

.badge-pill-points {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 3px 8px;
  border-radius: 6px;
  font-size: 0.78rem;
  font-weight: 700;
  background: rgba(99, 102, 241, 0.08);
  color: #4f46e5;
  border: 1px solid rgba(99, 102, 241, 0.2);
}

.badge-pill-grade {
  display: inline-block;
  padding: 2px 6px;
  border-radius: 6px;
  font-size: 0.74rem;
  font-weight: 600;
  background: rgba(148, 163, 184, 0.12);
  color: var(--text-color, #334155);
}

/* Table Action Buttons */
.exam-table-actions {
  display: flex;
  align-items: center;
  gap: 4px;
  justify-content: flex-end;
}

.btn-action-icon {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 30px;
  height: 30px;
  border-radius: 8px;
  font-size: 0.82rem;
  border: 1px solid transparent;
  text-decoration: none;
  transition: all 0.2s ease;
  cursor: pointer;
}

.btn-action-icon--results {
  background: rgba(14, 165, 233, 0.1);
  color: #0284c7;
  border-color: rgba(14, 165, 233, 0.25);
}
.btn-action-icon--results:hover {
  background: #0284c7;
  color: #fff;
}

.btn-action-icon--questions {
  background: rgba(99, 102, 241, 0.1);
  color: #4f46e5;
  border-color: rgba(99, 102, 241, 0.25);
}
.btn-action-icon--questions:hover {
  background: #4f46e5;
  color: #fff;
}

.btn-action-icon--edit {
  background: rgba(245, 158, 11, 0.1);
  color: #d97706;
  border-color: rgba(245, 158, 11, 0.25);
}
.btn-action-icon--edit:hover {
  background: #d97706;
  color: #fff;
}

.btn-action-icon--delete {
  background: rgba(239, 68, 68, 0.1);
  color: #dc2626;
  border-color: rgba(239, 68, 68, 0.25);
}
.btn-action-icon--delete:hover {
  background: #dc2626;
  color: #fff;
}

/* ====================================================================
   Mobile Dedicated Cards Layout (< 992px)
   ==================================================================== */
.exams-mobile-list {
  display: none;
  flex-direction: column;
  gap: 16px;
  padding: 16px;
}

.exam-card-mobile {
  background: var(--card-bg, #ffffff);
  border: 1px solid var(--border-color, rgba(148, 163, 184, 0.25));
  border-radius: 16px;
  padding: 16px;
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.03);
  display: flex;
  flex-direction: column;
  gap: 14px;
}

.exam-card-mobile__header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 10px;
  border-bottom: 1px solid var(--border-soft, rgba(148, 163, 184, 0.12));
  padding-bottom: 12px;
}

.exam-card-mobile__title-wrap {
  display: flex;
  align-items: center;
  gap: 10px;
}

.exam-card-mobile__grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 10px 14px;
  background: var(--detail-bg, rgba(241, 245, 249, 0.5));
  border-radius: 12px;
  padding: 12px;
}

.exam-card-mobile__item {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.exam-card-mobile__label {
  font-size: 0.72rem;
  font-weight: 700;
  text-transform: uppercase;
  color: var(--muted-text, #64748b);
  letter-spacing: 0.03em;
}

.exam-card-mobile__val {
  font-size: 0.88rem;
  font-weight: 600;
  color: var(--text-color, #0f172a);
}

.exam-card-mobile__actions {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 8px;
  padding-top: 8px;
  border-top: 1px solid var(--border-soft, rgba(148, 163, 184, 0.12));
}

@media (max-width: 991px) {
  .exams-table-responsive {
    display: none !important;
  }
  .exams-mobile-list {
    display: flex !important;
  }
}

/* ====================================================================
   Dark Mode Overrides
   ==================================================================== */
:root[data-theme='dark'] .exams-glass-card {
  background: linear-gradient(180deg, rgba(15, 27, 45, 0.95), rgba(10, 19, 33, 0.98)) !important;
  border-color: rgba(255, 255, 255, 0.08) !important;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.25) !important;
}

:root[data-theme='dark'] .exams-back-btn,
:root[data-theme='dark'] .btn-results-link {
  background: rgba(255, 255, 255, 0.05) !important;
  border-color: rgba(255, 255, 255, 0.1) !important;
  color: #f1f5f9 !important;
}

:root[data-theme='dark'] .exams-back-btn:hover,
:root[data-theme='dark'] .btn-results-link:hover {
  border-color: #818cf8 !important;
  color: #818cf8 !important;
}

:root[data-theme='dark'] .exams-modern-table thead tr {
  background: rgba(15, 23, 42, 0.6) !important;
  border-bottom-color: rgba(255, 255, 255, 0.08) !important;
}

:root[data-theme='dark'] .exams-modern-table th {
  color: #94a3b8 !important;
}

:root[data-theme='dark'] .exams-modern-table tbody tr {
  border-bottom-color: rgba(255, 255, 255, 0.05) !important;
}

:root[data-theme='dark'] .exams-modern-table tbody tr:hover {
  background: rgba(255, 255, 255, 0.02) !important;
}

:root[data-theme='dark'] .exam-meta-title,
:root[data-theme='dark'] .exam-card-mobile__val,
:root[data-theme='dark'] .exams-modern-table td {
  color: #f1f5f9 !important;
}

:root[data-theme='dark'] .exam-meta-subtitle,
:root[data-theme='dark'] .exam-card-mobile__label {
  color: #94a3b8 !important;
}

:root[data-theme='dark'] .badge-pill-points {
  background: rgba(99, 102, 241, 0.15) !important;
  color: #a5b4fc !important;
  border-color: rgba(99, 102, 241, 0.3) !important;
}

:root[data-theme='dark'] .badge-pill-grade {
  background: rgba(255, 255, 255, 0.08) !important;
  color: #cbd5e1 !important;
}

:root[data-theme='dark'] .badge-status-active {
  background: rgba(16, 185, 129, 0.16) !important;
  color: #34d399 !important;
  border-color: rgba(16, 185, 129, 0.3) !important;
}

:root[data-theme='dark'] .badge-status-incomplete {
  background: rgba(245, 158, 11, 0.16) !important;
  color: #fbbf24 !important;
  border-color: rgba(245, 158, 11, 0.3) !important;
}

:root[data-theme='dark'] .exam-card-mobile {
  background: rgba(15, 23, 42, 0.6) !important;
  border-color: rgba(255, 255, 255, 0.08) !important;
}

:root[data-theme='dark'] .exam-card-mobile__grid {
  background: rgba(15, 23, 42, 0.4) !important;
}
</style>
@endpush

  <section class="news-hero profile-hero">
    <div class="container">
      <div class="news-hero-content prime-reveal">
        <span class="badge"><i class="fa-solid fa-file-signature me-1"></i> Imtihonlar Markazi</span>
        <h1><strong>{{ __('public.profile_exams.title') }}</strong></h1>
        <p>O'z faningiz bo'yicha onlayn testlar va imtihonlarni yarating, savollar bankini boshqaring va o'quvchilar natijalarini kuzatib boring.</p>
      </div>
    </div>
  </section>

  <main class="exams-page-wrapper">
    <div class="container">

      {{-- Top Hero Bar --}}
      <div class="exams-hero-bar">
        <a href="{{ route('profile.show') }}" class="exams-back-btn">
          <i class="fa-solid fa-arrow-left"></i> Profilga qaytish
        </a>

        <div class="exams-action-buttons">
          <a href="{{ route('profile.exams.results') }}" class="btn-results-link">
            <i class="fa-solid fa-chart-simple text-primary"></i> {{ __('public.profile_exams.general_results') }}
          </a>
          <a href="{{ route('profile.exams.create') }}" class="btn-create-exam">
            <i class="fa-solid fa-plus-circle"></i> {{ __('public.profile_exams.new_exam') }}
          </a>
        </div>
      </div>

      {{-- Main Glass Card Container --}}
      <div class="exams-glass-card">

        {{-- Search Bar Header --}}
        <div class="exams-search-header">
          @include('admin.partials.search-bar', [
            'placeholder' => __('public.profile_exams.search_placeholder'),
            'action' => route('profile.exams.index'),
          ])
        </div>

        {{-- 1. Desktop Table View (>= 992px) --}}
        <div class="exams-table-responsive">
          <table class="exams-modern-table">
            <thead>
              <tr>
                <th style="width: 38px;">#</th>
                <th><i class="fa-solid fa-file-pen me-1"></i> {{ __('public.profile_exams.col_name') }}</th>
                <th><i class="fa-solid fa-list-ol me-1"></i> {{ __('public.profile_exams.col_questions') }}</th>
                <th><i class="fa-solid fa-star me-1"></i> Ball (O'tish)</th>
                <th><i class="fa-solid fa-calendar-days me-1"></i> Boshlanish / Sinf</th>
                <th><i class="fa-solid fa-users me-1"></i> Ishtirok</th>
                <th class="text-end" style="width: 145px;"><i class="fa-solid fa-sliders me-1"></i> {{ __('public.profile_exams.col_actions') }}</th>
              </tr>
            </thead>
            <tbody>
              @forelse($exams as $exam)
                @php
                  $isQuestionsComplete = $exam->questions_count >= $exam->required_questions;
                @endphp
                <tr>
                  <td>
                    <span style="font-weight: 700; color: var(--muted-text, #64748b);">{{ $exam->id }}</span>
                  </td>

                  {{-- Imtihon nomi --}}
                  <td>
                    <div class="exam-title-cell">
                      <div class="exam-icon-badge">
                        <i class="fa-solid fa-file-lines"></i>
                      </div>
                      <div>
                        <div class="exam-meta-title">{{ $exam->title }}</div>
                        <div class="exam-meta-subtitle">
                          @if($exam->is_active)
                            <span class="badge-status-active"><i class="fa-solid fa-circle-check"></i> Faol</span>
                          @else
                            <span class="badge-status-incomplete"><i class="fa-solid fa-clock"></i> Savollar to'lmagan</span>
                          @endif
                        </div>
                      </div>
                    </div>
                  </td>

                  {{-- Savollar --}}
                  <td>
                    <span class="badge-pill-points" style="{{ $isQuestionsComplete ? 'color:#10b981; border-color:rgba(16,185,129,0.3); background:rgba(16,185,129,0.08);' : '' }}">
                      {{ $exam->questions_count }} / {{ $exam->required_questions }}
                    </span>
                  </td>

                  {{-- Jami ball & O'tish bali --}}
                  <td>
                    <strong style="color: #4f46e5; font-size: 0.9rem;">{{ $exam->total_points }} ball</strong>
                    <div style="font-size: 0.76rem; color: var(--muted-text, #64748b);">
                      O'tish: {{ $exam->passing_points ?? '—' }}
                    </div>
                  </td>

                  {{-- Boshlanish vaqti & Sinf --}}
                  <td>
                    <div style="font-size: 0.82rem; font-weight: 600;">
                      {{ $exam->availableFromLabel() ?? 'Ixtiyoriy vaqt' }}
                    </div>
                    <div style="margin-top: 2px;">
                      <span class="badge-pill-grade" title="{{ $exam->allowedGradesLabel() }}">
                        {{ \Illuminate\Support\Str::limit($exam->allowedGradesLabel(), 18) }}
                      </span>
                    </div>
                  </td>

                  {{-- Ishtirokchilar --}}
                  <td>
                    <span style="{{ $exam->hasParticipantLimit() && $exam->isParticipantLimitReached() ? 'color:#dc2626;font-weight:700;' : 'font-weight:600;' }}; font-size: 0.84rem;">
                      {{ $exam->participantLimitLabel() }}
                    </span>
                  </td>

                  {{-- Amallar --}}
                  <td class="text-end">
                    <div class="exam-table-actions">
                      <a href="{{ route('profile.exams.results', ['exam_id' => $exam->id]) }}" class="btn-action-icon btn-action-icon--results" title="{{ __('public.profile_exams.results_btn') }}">
                        <i class="fa-solid fa-chart-pie"></i>
                      </a>
                      <a href="{{ route('profile.exams.questions.index', $exam) }}" class="btn-action-icon btn-action-icon--questions" title="{{ __('public.profile_exams.questions_btn') }}">
                        <i class="fa-solid fa-list-check"></i>
                      </a>
                      <a href="{{ route('profile.exams.edit', $exam) }}" class="btn-action-icon btn-action-icon--edit" title="{{ __('public.profile_exams.edit_btn') }}">
                        <i class="fa-solid fa-pen-to-square"></i>
                      </a>
                      <form method="POST" action="{{ route('profile.exams.destroy', $exam) }}" style="display:inline; margin:0;" data-confirm="Imtihon va barcha savollari o'chirilsinmi?" data-confirm-title="Imtihonni o'chirish" data-confirm-variant="danger" data-confirm-ok="O'chirish">
                        @csrf
                        @method('DELETE')
                        <button class="btn-action-icon btn-action-icon--delete" type="submit" title="{{ __('public.profile_exams.delete_btn') }}">
                          <i class="fa-solid fa-trash-can"></i>
                        </button>
                      </form>
                    </div>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="7" style="padding: 40px 20px; text-align: center; color: var(--muted-text, #64748b);">
                    <i class="fa-solid fa-folder-open mb-2" style="font-size: 2.5rem; opacity: 0.4; display: block;"></i>
                    <span style="font-weight: 600; font-size: 1rem;">{{ __('public.profile_exams.empty') }}</span>
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        {{-- 2. Dedicated Mobile Cards List (< 992px) --}}
        <div class="exams-mobile-list">
          @forelse($exams as $exam)
            @php
              $isQuestionsComplete = $exam->questions_count >= $exam->required_questions;
            @endphp
            <div class="exam-card-mobile">
              {{-- Header: Nomi & Holati --}}
              <div class="exam-card-mobile__header">
                <div class="exam-card-mobile__title-wrap">
                  <div class="exam-icon-badge" style="width:34px;height:34px;font-size:0.95rem;">
                    <i class="fa-solid fa-file-lines"></i>
                  </div>
                  <div>
                    <div class="exam-meta-title" style="font-size:0.92rem;">{{ $exam->title }}</div>
                    <div class="exam-meta-subtitle">ID: #{{ $exam->id }}</div>
                  </div>
                </div>

                <div>
                  @if($exam->is_active)
                    <span class="badge-status-active"><i class="fa-solid fa-circle-check"></i> Faol</span>
                  @else
                    <span class="badge-status-incomplete"><i class="fa-solid fa-clock"></i> Kutilmoqda</span>
                  @endif
                </div>
              </div>

              {{-- Parametrlar gridi --}}
              <div class="exam-card-mobile__grid">
                <div class="exam-card-mobile__item">
                  <span class="exam-card-mobile__label">Savollar:</span>
                  <span class="exam-card-mobile__val" style="{{ $isQuestionsComplete ? 'color:#10b981;' : '' }}">
                    {{ $exam->questions_count }} / {{ $exam->required_questions }} ta
                  </span>
                </div>

                <div class="exam-card-mobile__item">
                  <span class="exam-card-mobile__label">Jami ball:</span>
                  <span class="exam-card-mobile__val" style="color:#4f46e5;">
                    {{ $exam->total_points }} ball
                  </span>
                </div>

                <div class="exam-card-mobile__item">
                  <span class="exam-card-mobile__label">O'tish bali:</span>
                  <span class="exam-card-mobile__val">{{ $exam->passing_points ?? '—' }}</span>
                </div>

                <div class="exam-card-mobile__item">
                  <span class="exam-card-mobile__label">Sinflar:</span>
                  <span class="exam-card-mobile__val">{{ $exam->allowedGradesLabel() }}</span>
                </div>

                <div class="exam-card-mobile__item" style="grid-column: span 2;">
                  <span class="exam-card-mobile__label">Boshlanish vaqti / Ishtirok:</span>
                  <span class="exam-card-mobile__val" style="font-size:0.82rem;font-weight:500;">
                    📅 {{ $exam->availableFromLabel() ?? 'Ixtiyoriy vaqt' }} · 👥 {{ $exam->participantLimitLabel() }}
                  </span>
                </div>
              </div>

              {{-- Footer amallari --}}
              <div class="exam-card-mobile__actions">
                <div class="d-flex align-items-center gap-2">
                  <a href="{{ route('profile.exams.results', ['exam_id' => $exam->id]) }}" class="btn-action-icon btn-action-icon--results" title="Natijalar">
                    <i class="fa-solid fa-chart-pie"></i>
                  </a>
                  <a href="{{ route('profile.exams.questions.index', $exam) }}" class="btn-action-icon btn-action-icon--questions" title="Savollar">
                    <i class="fa-solid fa-list-check"></i>
                  </a>
                  <a href="{{ route('profile.exams.edit', $exam) }}" class="btn-action-icon btn-action-icon--edit" title="Tahrirlash">
                    <i class="fa-solid fa-pen-to-square"></i>
                  </a>
                </div>

                <form method="POST" action="{{ route('profile.exams.destroy', $exam) }}" style="margin:0;" data-confirm="Imtihon va barcha savollari o'chirilsinmi?" data-confirm-title="Imtihonni o'chirish" data-confirm-variant="danger" data-confirm-ok="O'chirish">
                  @csrf
                  @method('DELETE')
                  <button class="btn-action-icon btn-action-icon--delete" type="submit" title="O'chirish">
                    <i class="fa-solid fa-trash-can"></i>
                  </button>
                </form>
              </div>
            </div>
          @empty
            <div style="padding: 30px 16px; text-align: center; color: var(--muted-text, #64748b);">
              <i class="fa-solid fa-folder-open mb-2" style="font-size: 2.2rem; opacity: 0.4; display: block;"></i>
              <span style="font-weight: 600;">{{ __('public.profile_exams.empty') }}</span>
            </div>
          @endforelse
        </div>

        {{-- Pagination --}}
        @if($exams->hasPages())
          <div style="padding: 16px 20px; border-top: 1px solid var(--border-soft, rgba(148, 163, 184, 0.12));">
            {{ $exams->links() }}
          </div>
        @endif

      </div>
    </div>
  </main>
</x-layouts.main>

