<x-layouts.main title="81-IDUM | Kursga yozilishlar">
@push('page_styles')
<style>
@media (max-width: 860px) {
  .profile-enrollments-table thead { display: none; }
  .profile-enrollments-table, .profile-enrollments-table tbody,
  .profile-enrollments-table tr, .profile-enrollments-table td {
    display: block; width: 100%;
  }
  .profile-enrollments-table tr {
    margin-bottom: 14px;
    border-radius: 16px;
    border: 1px solid rgba(13, 63, 120, 0.1);
    background: #fff;
    overflow: hidden;
  }
  .profile-enrollments-table td {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
    padding: 10px 16px;
    border: none;
    border-bottom: 1px solid rgba(13, 63, 120, 0.06);
    text-align: right;
  }
  .profile-enrollments-table td:last-child { border-bottom: none; }
  .profile-enrollments-table td::before {
    content: attr(data-label);
    font-weight: 700;
    font-size: 12px;
    text-transform: uppercase;
    color: #4b6282;
    text-align: left;
    flex-shrink: 0;
  }
}
:root[data-theme='dark'] .profile-enrollments-table tr { background: #0f1b2d; border-color: rgba(255,255,255,0.08); }
:root[data-theme='dark'] .profile-enrollments-table td { border-bottom-color: rgba(255,255,255,0.06); }
:root[data-theme='dark'] .profile-enrollments-table td::before { color: #94a3b8; }
</style>
@endpush
  <section class="news-hero profile-hero">
    <div class="container">
      <div class="news-hero-content prime-reveal">
        <span class="badge">Kurslar</span>
        <h1><strong>Kursga yozilish arizalari</strong></h1>
        <p>O'z kursingizga yozilgan o'quvchilarni ko'ring, telefon va sinf bo'yicha bog'laning, tasdiqlang yoki rad eting.</p>
      </div>
    </div>
  </section>

  <main class="profile-main">
    <div class="container">
      <p class="profile-toolbar">
        <a href="{{ route('profile.show') }}" class="btn btn-outline btn-sm">&larr; Profilga</a>
        @if(($pendingCount ?? 0) > 0)
          <span class="badge profile-badge-pending">Kutilmoqda: {{ $pendingCount }}</span>
        @endif
      </p>

      @if (session('success'))
        <p class="profile-success-message">{{ session('success') }}</p>
      @endif

      <div class="signin-card profile-card profile-enrollments-card">
        <div class="table-wrapper profile-table-wrap">
          <table class="table profile-enrollments-table">
            <thead>
              <tr>
                <th>Kurs</th>
                <th>O'quvchi</th>
                <th>Aloqa tel.</th>
                <th>Sinf</th>
                <th>Fan darajasi</th>
                <th>Holat</th>
                <th>Amallar</th>
                <th>Olib tashlash</th>
              </tr>
            </thead>
            <tbody>
              @forelse($enrollments as $row)
                <tr>
                  <td data-label="Kurs">
                    <strong>{{ $row->course?->title ?: '-' }}</strong>
                  </td>
                  <td data-label="O'quvchi">
                    {{ $row->user?->name ?: '-' }}<br>
                    <small class="profile-muted">{{ $row->user?->email }}</small>
                    @if($row->note)
                      <p class="profile-note"><em>Izoh:</em> {{ $row->note }}</p>
                    @endif
                  </td>
                  <td data-label="Aloqa tel.">{{ $row->contact_phone ?: '-' }}</td>
                  <td data-label="Sinf">{{ $row->grade ?: '-' }}</td>
                  <td data-label="Fan darajasi">{{ $row->subject_level ?: '-' }}</td>
                  <td data-label="Holat">
                    @if($row->isPending())
                      <span class="badge" style="background:#f59e0b;">Kutilmoqda</span>
                    @elseif($row->isApproved())
                      <span class="badge" style="background:#0f766e;">Tasdiqlangan</span>
                    @else
                      <span class="badge" style="background:#b91c1c;">Rad etilgan</span>
                    @endif
                  </td>
                  <td data-label="Amallar">
                    @if($row->isPending())
                      <form action="{{ route('teacher.enrollments.approve', $row) }}" method="POST" style="display:inline;">
                        @csrf
                        <button type="submit" class="btn btn-sm" style="margin-bottom:6px;">Tasdiqlash</button>
                      </form>
                      <form action="{{ route('teacher.enrollments.reject', $row) }}" method="POST" style="display:inline;" data-confirm="Rad etilsinmi?" data-confirm-title="Rad etish" data-confirm-variant="primary" data-confirm-ok="Rad etish">
                        @csrf
                        <button type="submit" class="btn btn-outline btn-sm profile-btn-danger">Rad etish</button>
                      </form>
                    @else
                      <span class="profile-muted">-</span>
                    @endif
                  </td>
                  <td data-label="Olib tashlash">
                    <form action="{{ route('teacher.enrollments.destroy', $row) }}" method="POST" data-confirm="Yozilish olib tashlansinmi?" data-confirm-title="Yozilishni olib tashlash" data-confirm-variant="danger" data-confirm-ok="Olib tashlash">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn-outline btn-sm profile-btn-danger">Olib tashlash</button>
                    </form>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="8" style="padding:20px;">Hozircha arizalar yo'q.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        @if($enrollments->hasPages())
          <div style="margin-top:16px;">{{ $enrollments->links() }}</div>
        @endif
      </div>
    </div>
  </main>
</x-loyouts.main>
