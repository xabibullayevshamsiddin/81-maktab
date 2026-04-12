<x-loyouts.main title="81-IDUM | Kursga yozilishlar">
  <section class="news-hero profile-hero">
    <div class="container">
      <div class="news-hero-content reveal">
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
                  <td>
                    <strong>{{ $row->course?->title ?: '-' }}</strong>
                  </td>
                  <td>
                    {{ $row->user?->name ?: '-' }}<br>
                    <small class="profile-muted">{{ $row->user?->email }}</small>
                    @if($row->note)
                      <p class="profile-note"><em>Izoh:</em> {{ $row->note }}</p>
                    @endif
                  </td>
                  <td>{{ $row->contact_phone ?: '-' }}</td>
                  <td>{{ $row->grade ?: '-' }}</td>
                  <td>{{ $row->subject_level ?: '-' }}</td>
                  <td>
                    @if($row->isPending())
                      <span class="badge" style="background:#f59e0b;">Kutilmoqda</span>
                    @elseif($row->isApproved())
                      <span class="badge" style="background:#0f766e;">Tasdiqlangan</span>
                    @else
                      <span class="badge" style="background:#b91c1c;">Rad etilgan</span>
                    @endif
                  </td>
                  <td>
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
                  <td>
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
