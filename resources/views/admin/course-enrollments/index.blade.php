@extends('admin.layouts.main')

@section('title', 'Barcha kurs yozilishlari')

@section('content')
<section class="table-components">
  <div class="container-fluid">
    <div class="title-wrapper pt-30">
      <div class="row align-items-center">
        <div class="col-md-8">
          <div class="title">
            <h2>Kursga yozilish arizalari</h2>
            <p class="text-sm">Barcha kurslar bo‘yicha: telefon, sinf, holat va nazorat.</p>
          </div>
        </div>
        <div class="col-md-4 text-end">
          <a href="{{ route('admin.courses.index') }}" class="btn btn-outline btn-sm">Kurslar ro‘yxati</a>
        </div>
      </div>
    </div>

    @if (session('success'))
      <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card-style mb-20">
      <div class="d-flex flex-wrap gap-2 align-items-center">
        <span class="text-sm">Filtr:</span>
        <a href="{{ route('admin.course-enrollments.index') }}" class="btn btn-sm {{ !request('status') ? 'btn-primary' : 'btn-outline-primary' }}">Hammasi</a>
        <a href="{{ route('admin.course-enrollments.index', ['status' => 'pending']) }}" class="btn btn-sm {{ request('status') === 'pending' ? 'btn-primary' : 'btn-outline-primary' }}">
          Kutilmoqda @if(($pendingCount ?? 0) > 0)<span class="badge bg-light text-dark ms-1">{{ $pendingCount }}</span>@endif
        </a>
        <a href="{{ route('admin.course-enrollments.index', ['status' => 'approved']) }}" class="btn btn-sm {{ request('status') === 'approved' ? 'btn-primary' : 'btn-outline-primary' }}">Tasdiqlangan</a>
        <a href="{{ route('admin.course-enrollments.index', ['status' => 'rejected']) }}" class="btn btn-sm {{ request('status') === 'rejected' ? 'btn-primary' : 'btn-outline-primary' }}">Rad etilgan</a>
      </div>
    </div>

    <div class="card-style mb-30">
      <div class="table-wrapper table-responsive">
        <table class="table">
          <thead>
            <tr>
              <th><h6>#</h6></th>
              <th><h6>Kurs</h6></th>
              <th><h6>Ustoz</h6></th>
              <th><h6>Muallif</h6></th>
              <th><h6>O‘quvchi</h6></th>
              <th><h6>Ariza tel.</h6></th>
              <th><h6>Sinf</h6></th>
              <th><h6>Fan darajasi</h6></th>
              <th><h6>Holat</h6></th>
              <th><h6>Sana</h6></th>
              <th><h6>Amallar</h6></th>
            </tr>
          </thead>
          <tbody>
            @forelse($enrollments as $row)
              <tr>
                <td><p>{{ $row->id }}</p></td>
                <td><p><strong>{{ $row->course?->title ?: '—' }}</strong></p></td>
                <td><p>{{ $row->course?->teacher?->full_name ?: '—' }}</p></td>
                <td><p>{{ $row->course?->creator?->name ?: '—' }}</p></td>
                <td>
                  <p><strong>{{ $row->user?->name ?: '—' }}</strong></p>
                  <small class="text-muted">{{ $row->user?->email }}</small>
                </td>
                <td><p>{{ $row->contact_phone ?: '—' }}</p></td>
                <td><p>{{ $row->grade ?: '—' }}</p></td>
                <td><p style="max-width:160px;">{{ $row->subject_level ?: '—' }}</p></td>
                <td>
                  @if($row->isPending())
                    <span class="badge bg-warning text-dark">Kutilmoqda</span>
                  @elseif($row->isApproved())
                    <span class="badge bg-success">Tasdiqlangan</span>
                  @else
                    <span class="badge bg-danger">Rad etilgan</span>
                  @endif
                </td>
                <td><p>{{ $row->created_at?->format('Y-m-d H:i') }}</p></td>
                <td>
                  @if($row->course && $row->isPending())
                    <form action="{{ route('admin.courses.enrollments.approve', [$row->course, $row]) }}" method="POST" class="d-inline">
                      @csrf
                      <button type="submit" class="btn btn-sm btn-success">Tasdiqlash</button>
                    </form>
                    <form action="{{ route('admin.courses.enrollments.reject', [$row->course, $row]) }}" method="POST" class="d-inline" onsubmit="return confirm('Rad etilsinmi?');">
                      @csrf
                      <button type="submit" class="btn btn-sm btn-outline-danger">Rad</button>
                    </form>
                  @else
                    @if($row->course)
                      <a href="{{ route('admin.courses.enrollments', $row->course) }}" class="text-primary small">Kurs bo‘yicha</a>
                    @else
                      <span class="text-muted">—</span>
                    @endif
                  @endif
                </td>
              </tr>
            @empty
              <tr><td colspan="11"><p>Hozircha yozilishlar yo‘q.</p></td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
      @if($enrollments->hasPages())
        <div class="p-3">{{ $enrollments->links() }}</div>
      @endif
    </div>
  </div>
</section>
@endsection
