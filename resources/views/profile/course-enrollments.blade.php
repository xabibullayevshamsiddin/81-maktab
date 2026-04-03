<x-loyouts.main title="81-IDUM | Kursga yozilishlar">
  <section class="news-hero profile-hero">
    <div class="container">
      <div class="news-hero-content reveal">
        <span class="badge">Kurslar</span>
        <h1><strong>Kursga yozilish arizalari</strong></h1>
        <p>O‘z kursingizga yozilgan o‘quvchilarni ko‘ring, telefon va sinf bo‘yicha bog‘laning, tasdiqlang yoki rad eting.</p>
      </div>
    </div>
  </section>

  <main class="profile-main">
    <div class="container">
      <p style="margin-bottom: 20px;">
        <a href="{{ route('profile.show') }}" class="btn btn-outline btn-sm">← Profilga</a>
        @if(($pendingCount ?? 0) > 0)
          <span class="badge" style="margin-left: 10px; color:grey;">Kutilmoqda: {{ $pendingCount }}</span>
        @endif
      </p>

      @if (session('success'))
        <p style="color:#0f766e;font-weight:600;margin-bottom:16px;">{{ session('success') }}</p>
      @endif

      <div class="signin-card profile-card" style="max-width: 960px;">
        <div class="table-wrapper" style="overflow-x:auto;">
          <table class="table" style="width:100%; border-collapse:collapse;">
            <thead>
              <tr style="text-align:left; border-bottom:1px solid rgba(0,0,0,.1);">
                <th style="padding:10px;">Kurs</th>
                <th style="padding:10px;">O‘quvchi</th>
                <th style="padding:10px;">Aloqa tel.</th>
                <th style="padding:10px;">Sinf</th>
                <th style="padding:10px;">Fan darajasi</th>
                <th style="padding:10px;">Holat</th>
                <th style="padding:10px;">Amallar</th>
                <th style="padding:10px;">Olib tashlash</th>
              </tr>
            </thead>
            <tbody>
              @forelse($enrollments as $row)
                <tr style="border-bottom:1px solid rgba(0,0,0,.06);">
                  <td style="padding:10px; vertical-align:top;">
                    <strong>{{ $row->course?->title ?: '—' }}</strong>
                  </td>
                  <td style="padding:10px; vertical-align:top;">
                    {{ $row->user?->name ?: '—' }}<br>
                    <small class="profile-muted">{{ $row->user?->email }}</small>
                    @if($row->note)
                      <p style="margin:6px 0 0; font-size:12px; color:#64748b;"><em>Izoh:</em> {{ $row->note }}</p>
                    @endif
                  </td>
                  <td style="padding:10px; vertical-align:top;">{{ $row->contact_phone ?: '—' }}</td>
                  <td style="padding:10px; vertical-align:top;">{{ $row->grade ?: '—' }}</td>
                  <td style="padding:10px; vertical-align:top;">{{ $row->subject_level ?: '—' }}</td>
                  <td style="padding:10px; vertical-align:top;">
                    @if($row->isPending())
                      <span class="badge" style="background:#f59e0b;">Kutilmoqda</span>
                    @elseif($row->isApproved())
                      <span class="badge" style="background:#0f766e;">Tasdiqlangan</span>
                    @else
                      <span class="badge" style="background:#b91c1c;">Rad etilgan</span>
                    @endif
                  </td>
                  <td style="padding:10px; vertical-align:top;">
                    @if($row->isPending())
                      <form action="{{ route('teacher.enrollments.approve', $row) }}" method="POST" style="display:inline;">
                        @csrf
                        <button type="submit" class="btn btn-sm" style="margin-bottom:6px;">Tasdiqlash</button>
                      </form>
                      <form action="{{ route('teacher.enrollments.reject', $row) }}" method="POST" style="display:inline;" onsubmit="return confirm('Rad etilsinmi?');">
                        @csrf
                        <button type="submit" class="btn btn-outline btn-sm">Rad etish</button>
                      </form>
                    @else
                      <span class="profile-muted">—</span>
                    @endif
                  </td>
                  <td style="padding:10px; vertical-align:top;">
                    <form action="{{ route('teacher.enrollments.destroy', $row) }}" method="POST" onsubmit="return confirm('Yozilish olib tashlansinmi?');">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn-outline btn-sm">Olib tashlash</button>
                    </form>
                  </td>
                </tr>
              @empty
                <tr><td colspan="8" style="padding:20px;">Hozircha arizalar yo‘q.</td></tr>
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
