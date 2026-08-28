<x-layouts.main title="81-IDUM | Kursga yozilish arizalari">
@push('page_styles')
<style>
/* ====================================================================
   Course Enrollments Page — Modern Glassmorphism & Responsive UI
   ==================================================================== */
.enrollments-page-wrapper {
  padding: 30px 0 70px;
}

.enrollments-hero-bar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 16px;
  margin-bottom: 24px;
}

.enrollments-back-btn {
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

.enrollments-back-btn:hover {
  transform: translateX(-3px);
  color: var(--primary-color, #4f46e5);
  border-color: var(--primary-color, #4f46e5);
}

.enrollments-stats-pills {
  display: flex;
  align-items: center;
  gap: 10px;
  flex-wrap: wrap;
}

.enrollment-stat-pill {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 6px 14px;
  border-radius: 999px;
  font-size: 0.85rem;
  font-weight: 700;
}

.enrollment-stat-pill--pending {
  background: rgba(245, 158, 11, 0.14);
  color: #d97706;
  border: 1px solid rgba(245, 158, 11, 0.3);
}

.enrollment-stat-pill--total {
  background: rgba(99, 102, 241, 0.12);
  color: #6366f1;
  border: 1px solid rgba(99, 102, 241, 0.25);
}

/* Glass Main Card */
.enrollments-glass-card {
  background: var(--card-bg, rgba(255, 255, 255, 0.9));
  border: 1px solid var(--border-color, rgba(148, 163, 184, 0.2));
  border-radius: 20px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.04);
  backdrop-filter: blur(16px);
  -webkit-backdrop-filter: blur(16px);
  overflow: hidden;
}

/* Desktop Table Styling */
.enrollments-table-responsive {
  width: 100%;
  overflow-x: auto;
}

.enrollments-modern-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.92rem;
  text-align: left;
}

.enrollments-modern-table thead tr {
  background: var(--table-head-bg, rgba(241, 245, 249, 0.8));
  border-bottom: 1px solid var(--border-color, rgba(148, 163, 184, 0.2));
}

.enrollments-modern-table th {
  padding: 16px 18px;
  font-size: 0.82rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.04em;
  color: var(--muted-text, #64748b);
  white-space: nowrap;
}

.enrollments-modern-table tbody tr {
  border-bottom: 1px solid var(--border-soft, rgba(148, 163, 184, 0.12));
  transition: background 0.2s ease;
}

.enrollments-modern-table tbody tr:hover {
  background: var(--table-row-hover, rgba(99, 102, 241, 0.03));
}

.enrollments-modern-table td {
  padding: 16px 18px;
  vertical-align: middle;
  color: var(--text-color, #1e293b);
}

/* Student Info Cell */
.student-profile-cell {
  display: flex;
  align-items: flex-start;
  gap: 12px;
  min-width: 220px;
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
  word-break: break-all;
}

.student-note-badge {
  display: inline-block;
  margin-top: 4px;
  padding: 3px 8px;
  background: rgba(245, 158, 11, 0.08);
  border: 1px dashed rgba(245, 158, 11, 0.35);
  border-radius: 6px;
  font-size: 0.75rem;
  color: #b45309;
  font-style: italic;
}

/* Course Pill */
.course-title-pill {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-weight: 700;
  color: var(--text-color, #0f172a);
  font-size: 0.92rem;
}

.course-title-pill i {
  color: #6366f1;
}

/* Phone & Badges */
.contact-phone-link {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  color: var(--text-color, #334155);
  font-weight: 600;
  text-decoration: none;
  font-size: 0.9rem;
  transition: color 0.2s ease;
}

.contact-phone-link:hover {
  color: #4f46e5;
}

.grade-pill {
  display: inline-block;
  padding: 4px 10px;
  border-radius: 8px;
  font-weight: 700;
  font-size: 0.82rem;
  background: rgba(99, 102, 241, 0.08);
  color: #4f46e5;
  border: 1px solid rgba(99, 102, 241, 0.2);
}

/* Status Badges */
.status-badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 5px 12px;
  border-radius: 999px;
  font-size: 0.82rem;
  font-weight: 700;
}

.status-badge--pending {
  background: #fef3c7;
  color: #92400e;
  border: 1px solid #fde68a;
}

.status-badge--approved {
  background: #dcfce7;
  color: #166534;
  border: 1px solid #bbf7d0;
}

.status-badge--rejected {
  background: #fee2e2;
  color: #991b1b;
  border: 1px solid #fecaca;
}

/* Action Buttons */
.action-btn-group {
  display: flex;
  align-items: center;
  gap: 6px;
  flex-wrap: wrap;
}

.btn-approve-modern {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  padding: 6px 12px;
  border-radius: 8px;
  font-size: 0.82rem;
  font-weight: 700;
  background: linear-gradient(135deg, #10b981, #059669);
  color: #fff;
  border: none;
  cursor: pointer;
  box-shadow: 0 2px 6px rgba(16, 185, 129, 0.2);
  transition: all 0.2s ease;
}

.btn-approve-modern:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 10px rgba(16, 185, 129, 0.3);
  color: #fff;
}

.btn-reject-modern {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  padding: 5px 11px;
  border-radius: 8px;
  font-size: 0.82rem;
  font-weight: 600;
  background: transparent;
  color: #dc2626;
  border: 1px solid #fca5a5;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-reject-modern:hover {
  background: rgba(239, 68, 68, 0.1);
  border-color: #dc2626;
}

.btn-delete-icon {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 32px;
  height: 32px;
  border-radius: 8px;
  color: #ef4444;
  background: rgba(239, 68, 68, 0.08);
  border: 1px solid rgba(239, 68, 68, 0.2);
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-delete-icon:hover {
  background: #ef4444;
  color: #fff;
  border-color: #ef4444;
}

/* ====================================================================
   Mobile Dedicated Cards Layout (< 992px)
   ==================================================================== */
.enrollments-mobile-list {
  display: none;
  flex-direction: column;
  gap: 16px;
  padding: 16px;
}

.enrollment-card-mobile {
  background: var(--card-bg, #ffffff);
  border: 1px solid var(--border-color, rgba(148, 163, 184, 0.25));
  border-radius: 16px;
  padding: 16px;
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.03);
  display: flex;
  flex-direction: column;
  gap: 14px;
}

.enrollment-card-mobile__header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 10px;
  border-bottom: 1px solid var(--border-soft, rgba(148, 163, 184, 0.12));
  padding-bottom: 12px;
}

.enrollment-card-mobile__student {
  display: flex;
  align-items: center;
  gap: 12px;
}

.enrollment-card-mobile__grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 10px 14px;
  background: var(--detail-bg, rgba(241, 245, 249, 0.5));
  border-radius: 12px;
  padding: 12px;
}

.enrollment-card-mobile__item {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.enrollment-card-mobile__label {
  font-size: 0.72rem;
  font-weight: 700;
  text-transform: uppercase;
  color: var(--muted-text, #64748b);
  letter-spacing: 0.03em;
}

.enrollment-card-mobile__val {
  font-size: 0.88rem;
  font-weight: 600;
  color: var(--text-color, #0f172a);
}

.enrollment-card-mobile__note {
  background: rgba(245, 158, 11, 0.07);
  border-left: 3px solid #f59e0b;
  border-radius: 4px 8px 8px 4px;
  padding: 8px 12px;
  font-size: 0.83rem;
  color: #92400e;
  line-height: 1.4;
}

.enrollment-card-mobile__actions {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 8px;
  padding-top: 6px;
  border-top: 1px solid var(--border-soft, rgba(148, 163, 184, 0.12));
}

@media (max-width: 991px) {
  .enrollments-table-responsive {
    display: none !important;
  }
  .enrollments-mobile-list {
    display: flex !important;
  }
}

/* ====================================================================
   Dark Mode Overrides
   ==================================================================== */
:root[data-theme='dark'] .enrollments-glass-card {
  background: linear-gradient(180deg, rgba(15, 27, 45, 0.95), rgba(10, 19, 33, 0.98)) !important;
  border-color: rgba(255, 255, 255, 0.08) !important;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.25) !important;
}

:root[data-theme='dark'] .enrollments-back-btn {
  background: rgba(255, 255, 255, 0.05) !important;
  border-color: rgba(255, 255, 255, 0.1) !important;
  color: #f1f5f9 !important;
}

:root[data-theme='dark'] .enrollments-back-btn:hover {
  border-color: #818cf8 !important;
  color: #818cf8 !important;
}

:root[data-theme='dark'] .enrollments-modern-table thead tr {
  background: rgba(15, 23, 42, 0.6) !important;
  border-bottom-color: rgba(255, 255, 255, 0.08) !important;
}

:root[data-theme='dark'] .enrollments-modern-table th {
  color: #94a3b8 !important;
}

:root[data-theme='dark'] .enrollments-modern-table tbody tr {
  border-bottom-color: rgba(255, 255, 255, 0.05) !important;
}

:root[data-theme='dark'] .enrollments-modern-table tbody tr:hover {
  background: rgba(255, 255, 255, 0.02) !important;
}

:root[data-theme='dark'] .student-name,
:root[data-theme='dark'] .course-title-pill,
:root[data-theme='dark'] .enrollment-card-mobile__val,
:root[data-theme='dark'] .enrollments-modern-table td {
  color: #f1f5f9 !important;
}

:root[data-theme='dark'] .student-email,
:root[data-theme='dark'] .enrollment-card-mobile__label,
:root[data-theme='dark'] .contact-phone-link {
  color: #94a3b8 !important;
}

:root[data-theme='dark'] .contact-phone-link:hover {
  color: #818cf8 !important;
}

:root[data-theme='dark'] .grade-pill {
  background: rgba(99, 102, 241, 0.15) !important;
  color: #a5b4fc !important;
  border-color: rgba(99, 102, 241, 0.3) !important;
}

:root[data-theme='dark'] .status-badge--pending {
  background: rgba(245, 158, 11, 0.16) !important;
  color: #fbbf24 !important;
  border-color: rgba(245, 158, 11, 0.3) !important;
}

:root[data-theme='dark'] .status-badge--approved {
  background: rgba(16, 185, 129, 0.16) !important;
  color: #34d399 !important;
  border-color: rgba(16, 185, 129, 0.3) !important;
}

:root[data-theme='dark'] .status-badge--rejected {
  background: rgba(239, 68, 68, 0.16) !important;
  color: #f87171 !important;
  border-color: rgba(239, 68, 68, 0.3) !important;
}

:root[data-theme='dark'] .enrollment-card-mobile {
  background: rgba(15, 23, 42, 0.6) !important;
  border-color: rgba(255, 255, 255, 0.08) !important;
}

:root[data-theme='dark'] .enrollment-card-mobile__grid {
  background: rgba(15, 23, 42, 0.4) !important;
}

:root[data-theme='dark'] .enrollment-card-mobile__note,
:root[data-theme='dark'] .student-note-badge {
  background: rgba(245, 158, 11, 0.12) !important;
  color: #fcd34d !important;
}
</style>
@endpush

  <section class="news-hero profile-hero">
    <div class="container">
      <div class="news-hero-content prime-reveal">
        <span class="badge"><i class="fa-solid fa-graduation-cap me-1"></i> Ustoz Paneli</span>
        <h1><strong>Kursga yozilish arizalari</strong></h1>
        <p>O'z kursingizga yozilgan o'quvchilarni ko'ring, telefon va sinf bo'yicha bog'laning, tasdiqlang yoki rad eting.</p>
      </div>
    </div>
  </section>

  <main class="enrollments-page-wrapper">
    <div class="container">

      {{-- Top Actions & Stats Bar --}}
      <div class="enrollments-hero-bar">
        <a href="{{ route('profile.show') }}" class="enrollments-back-btn">
          <i class="fa-solid fa-arrow-left"></i> Profilga qaytish
        </a>

        <div class="enrollments-stats-pills">
          <span class="enrollment-stat-pill enrollment-stat-pill--total">
            <i class="fa-solid fa-list-check"></i> Jami arizalar: {{ $enrollments->total() }} ta
          </span>
          @if(($pendingCount ?? 0) > 0)
            <span class="enrollment-stat-pill enrollment-stat-pill--pending">
              <i class="fa-solid fa-hourglass-half"></i> Kutilmoqda: {{ $pendingCount }} ta
            </span>
          @endif
        </div>
      </div>

      {{-- Success message --}}
      @if (session('success'))
        <div class="alert-box success-alert mb-4" style="border-radius: 14px; padding: 14px 20px; display: flex; align-items: center; gap: 10px;">
          <i class="fa-solid fa-circle-check text-success" style="font-size: 1.2rem;"></i>
          <span style="font-weight: 600;">{{ session('success') }}</span>
        </div>
      @endif

      {{-- Main Glass Card Container --}}
      <div class="enrollments-glass-card">

        {{-- 1. Desktop Table View (>= 992px) --}}
        <div class="enrollments-table-responsive">
          <table class="enrollments-modern-table">
            <thead>
              <tr>
                <th><i class="fa-solid fa-book-open me-1"></i> Kurs</th>
                <th><i class="fa-solid fa-user me-1"></i> O'quvchi</th>
                <th><i class="fa-solid fa-phone me-1"></i> Aloqa tel.</th>
                <th><i class="fa-solid fa-school me-1"></i> Sinf</th>
                <th><i class="fa-solid fa-chart-simple me-1"></i> Fan darajasi</th>
                <th><i class="fa-solid fa-shield-halved me-1"></i> Holat</th>
                <th><i class="fa-solid fa-sliders me-1"></i> Amallar</th>
                <th class="text-end"><i class="fa-solid fa-trash-can me-1"></i> Olib tashlash</th>
              </tr>
            </thead>
            <tbody>
              @forelse($enrollments as $row)
                @php
                  $userName = $row->user?->name ?: 'Noma\'lum o\'quvchi';
                  $userInitial = mb_substr($userName, 0, 1);
                @endphp
                <tr>
                  {{-- Kurs --}}
                  <td>
                    <span class="course-title-pill">
                      <i class="fa-solid fa-graduation-cap"></i>
                      {{ $row->course?->title ?: 'Noma\'lum kurs' }}
                    </span>
                  </td>

                  {{-- O'quvchi --}}
                  <td>
                    <div class="student-profile-cell">
                      <div class="student-avatar-badge">{{ $userInitial }}</div>
                      <div class="student-meta-info">
                        <span class="student-name">{{ $userName }}</span>
                        @if($row->user?->email)
                          <span class="student-email">{{ $row->user->email }}</span>
                        @endif
                        @if($row->note)
                          <span class="student-note-badge" title="{{ $row->note }}">
                            <i class="fa-solid fa-quote-left me-1"></i> {{ \Illuminate\Support\Str::limit($row->note, 45) }}
                          </span>
                        @endif
                      </div>
                    </div>
                  </td>

                  {{-- Aloqa --}}
                  <td>
                    @if($row->contact_phone)
                      <a href="tel:{{ preg_replace('/[^\d+]/', '', $row->contact_phone) }}" class="contact-phone-link">
                        <i class="fa-solid fa-phone-volume text-primary"></i> {{ $row->contact_phone }}
                      </a>
                    @else
                      <span class="text-muted">—</span>
                    @endif
                  </td>

                  {{-- Sinf --}}
                  <td>
                    @if($row->grade)
                      <span class="grade-pill">{{ $row->grade }}</span>
                    @else
                      <span class="text-muted">—</span>
                    @endif
                  </td>

                  {{-- Fan darajasi --}}
                  <td>
                    <span style="font-weight: 600;">{{ $row->subject_level ?: '—' }}</span>
                  </td>

                  {{-- Holat --}}
                  <td>
                    @if($row->isPending())
                      <span class="status-badge status-badge--pending">
                        <i class="fa-solid fa-hourglass-start"></i> Kutilmoqda
                      </span>
                    @elseif($row->isApproved())
                      <span class="status-badge status-badge--approved">
                        <i class="fa-solid fa-circle-check"></i> Tasdiqlangan
                      </span>
                    @else
                      <span class="status-badge status-badge--rejected">
                        <i class="fa-solid fa-circle-xmark"></i> Rad etilgan
                      </span>
                    @endif
                  </td>

                  {{-- Amallar --}}
                  <td>
                    @if($row->isPending())
                      <div class="action-btn-group">
                        <form action="{{ route('teacher.enrollments.approve', $row) }}" method="POST" style="margin:0;">
                          @csrf
                          <button type="submit" class="btn-approve-modern" title="Tasdiqlash">
                            <i class="fa-solid fa-check"></i> Tasdiqlash
                          </button>
                        </form>
                        <form action="{{ route('teacher.enrollments.reject', $row) }}" method="POST" style="margin:0;" data-confirm="Ushbu arizani rad etishni xohlaysizmi?" data-confirm-title="Arizani rad etish" data-confirm-variant="primary" data-confirm-ok="Rad etish">
                          @csrf
                          <button type="submit" class="btn-reject-modern" title="Rad etish">
                            <i class="fa-solid fa-xmark"></i> Rad etish
                          </button>
                        </form>
                      </div>
                    @else
                      <span class="text-muted small">Ko'rib chiqilgan</span>
                    @endif
                  </td>

                  {{-- Olib tashlash --}}
                  <td class="text-end">
                    <form action="{{ route('teacher.enrollments.destroy', $row) }}" method="POST" style="display:inline-block; margin:0;" data-confirm="Yozilish arizasini butunlay olib tashlamoqchimisiz?" data-confirm-title="Arizani o'chirish" data-confirm-variant="danger" data-confirm-ok="Olib tashlash">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn-delete-icon" title="Olib tashlash">
                        <i class="fa-solid fa-trash-can"></i>
                      </button>
                    </form>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="8" style="padding: 40px 20px; text-align: center; color: var(--muted-text, #64748b);">
                    <i class="fa-solid fa-inbox mb-2" style="font-size: 2.5rem; opacity: 0.4; display: block;"></i>
                    <span style="font-weight: 600; font-size: 1rem;">Hozircha kurslaringizga yozilish arizalari yo'q.</span>
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        {{-- 2. Dedicated Mobile Cards List (< 992px) --}}
        <div class="enrollments-mobile-list">
          @forelse($enrollments as $row)
            @php
              $userName = $row->user?->name ?: 'Noma\'lum o\'quvchi';
              $userInitial = mb_substr($userName, 0, 1);
            @endphp
            <div class="enrollment-card-mobile">
              {{-- Header: Kurs & Holat --}}
              <div class="enrollment-card-mobile__header">
                <span class="course-title-pill">
                  <i class="fa-solid fa-graduation-cap"></i>
                  {{ $row->course?->title ?: 'Kurs' }}
                </span>

                @if($row->isPending())
                  <span class="status-badge status-badge--pending">
                    <i class="fa-solid fa-hourglass-start"></i> Kutilmoqda
                  </span>
                @elseif($row->isApproved())
                  <span class="status-badge status-badge--approved">
                    <i class="fa-solid fa-circle-check"></i> Tasdiqlangan
                  </span>
                @else
                  <span class="status-badge status-badge--rejected">
                    <i class="fa-solid fa-circle-xmark"></i> Rad etilgan
                  </span>
                @endif
              </div>

              {{-- O'quvchi profili --}}
              <div class="enrollment-card-mobile__student">
                <div class="student-avatar-badge">{{ $userInitial }}</div>
                <div class="student-meta-info">
                  <span class="student-name">{{ $userName }}</span>
                  @if($row->user?->email)
                    <span class="student-email">{{ $row->user->email }}</span>
                  @endif
                </div>
              </div>

              {{-- Parametrlar katagi --}}
              <div class="enrollment-card-mobile__grid">
                <div class="enrollment-card-mobile__item">
                  <span class="enrollment-card-mobile__label">Aloqa tel:</span>
                  <span class="enrollment-card-mobile__val">
                    @if($row->contact_phone)
                      <a href="tel:{{ preg_replace('/[^\d+]/', '', $row->contact_phone) }}" class="contact-phone-link">
                        {{ $row->contact_phone }}
                      </a>
                    @else
                      —
                    @endif
                  </span>
                </div>

                <div class="enrollment-card-mobile__item">
                  <span class="enrollment-card-mobile__label">Sinf:</span>
                  <span class="enrollment-card-mobile__val">
                    @if($row->grade)
                      <span class="grade-pill">{{ $row->grade }}</span>
                    @else
                      —
                    @endif
                  </span>
                </div>

                <div class="enrollment-card-mobile__item">
                  <span class="enrollment-card-mobile__label">Fan darajasi:</span>
                  <span class="enrollment-card-mobile__val">{{ $row->subject_level ?: '—' }}</span>
                </div>

                <div class="enrollment-card-mobile__item">
                  <span class="enrollment-card-mobile__label">Ariza vaqti:</span>
                  <span class="enrollment-card-mobile__val" style="font-size: 0.8rem; font-weight: 500;">
                    {{ $row->created_at?->format('d.m.Y H:i') ?: '—' }}
                  </span>
                </div>
              </div>

              {{-- Izoh --}}
              @if($row->note)
                <div class="enrollment-card-mobile__note">
                  <strong>Izoh:</strong> {{ $row->note }}
                </div>
              @endif

              {{-- Footer amallari --}}
              <div class="enrollment-card-mobile__actions">
                <div>
                  @if($row->isPending())
                    <div class="action-btn-group">
                      <form action="{{ route('teacher.enrollments.approve', $row) }}" method="POST" style="margin:0;">
                        @csrf
                        <button type="submit" class="btn-approve-modern">
                          <i class="fa-solid fa-check"></i> Tasdiqlash
                        </button>
                      </form>
                      <form action="{{ route('teacher.enrollments.reject', $row) }}" method="POST" style="margin:0;" data-confirm="Ushbu arizani rad etishni xohlaysizmi?" data-confirm-title="Arizani rad etish" data-confirm-variant="primary" data-confirm-ok="Rad etish">
                        @csrf
                        <button type="submit" class="btn-reject-modern">
                          <i class="fa-solid fa-xmark"></i> Rad etish
                        </button>
                      </form>
                    </div>
                  @else
                    <span class="text-muted small">Ko'rib chiqilgan</span>
                  @endif
                </div>

                <form action="{{ route('teacher.enrollments.destroy', $row) }}" method="POST" style="margin:0;" data-confirm="Yozilish arizasini butunlay olib tashlamoqchimisiz?" data-confirm-title="Arizani o'chirish" data-confirm-variant="danger" data-confirm-ok="Olib tashlash">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="btn-delete-icon" title="Olib tashlash">
                    <i class="fa-solid fa-trash-can"></i>
                  </button>
                </form>
              </div>
            </div>
          @empty
            <div style="padding: 30px 16px; text-align: center; color: var(--muted-text, #64748b);">
              <i class="fa-solid fa-inbox mb-2" style="font-size: 2.2rem; opacity: 0.4; display: block;"></i>
              <span style="font-weight: 600;">Hozircha kurslaringizga yozilish arizalari yo'q.</span>
            </div>
          @endforelse
        </div>

        {{-- Pagination --}}
        @if($enrollments->hasPages())
          <div style="padding: 16px 20px; border-top: 1px solid var(--border-soft, rgba(148, 163, 184, 0.12));">
            {{ $enrollments->links() }}
          </div>
        @endif

      </div>
    </div>
  </main>
</x-layouts.main>

