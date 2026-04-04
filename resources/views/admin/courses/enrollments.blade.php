@extends('admin.layouts.main')

@section('title', 'Kursga yozilganlar')

@section('content')
<section class="table-components">
  <div class="container-fluid">
    <div class="title-wrapper pt-30">
      <div class="row align-items-center">
        <div class="col-md-8">
          <div class="title">
            <h2>Yozilishlar: {{ $course->title }}</h2>
            <p class="text-sm">Ustoz: {{ $course->teacher?->full_name ?: '-' }} · Boshlanish: {{ $course->start_date?->format('Y-m-d') }}</p>
          </div>
        </div>
        <div class="col-md-4 text-end">
          <a href="{{ route('admin.courses.index') }}" class="btn btn-outline btn-sm">Orqaga</a>
        </div>
      </div>
    </div>

    @if (session('success'))
      <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @include('admin.partials.search-bar', [
      'placeholder' => 'Ism, email, telefon bo‘yicha...',
      'action' => route('admin.courses.enrollments', $course),
    ])

    <div class="card-style mb-30">
      <div class="table-wrapper table-responsive">
        <table class="table">
          <thead>
            <tr>
              <th><h6>#</h6></th>
              <th><h6>Foydalanuvchi</h6></th>
              <th><h6>Email</h6></th>
              <th><h6>Aloqa (profil)</h6></th>
              <th><h6>Ariza telefoni</h6></th>
              <th><h6>Sinf</h6></th>
              <th><h6>Fan darajasi</h6></th>
              <th><h6>Izoh</h6></th>
              <th><h6>Holat</h6></th>
              <th><h6>Sana</h6></th>
              <th><h6>Amallar</h6></th>
            </tr>
          </thead>
          <tbody>
            @forelse($enrollments as $row)
              <tr>
                <td><p>{{ $row->id }}</p></td>
                <td><p><strong>{{ $row->user?->name ?: '-' }}</strong></p></td>
                <td><p>{{ $row->user?->email ?: '-' }}</p></td>
                <td><p>{{ $row->user?->phone ?: '—' }}</p></td>
                <td><p>{{ $row->contact_phone ?: '—' }}</p></td>
                <td><p>{{ $row->grade ?: '—' }}</p></td>
                <td><p style="max-width:140px;">{{ $row->subject_level ?: '—' }}</p></td>
                <td><p style="max-width:200px;">{{ $row->note ?: '—' }}</p></td>
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
                  @if($row->isPending())
                    <form action="{{ route('admin.courses.enrollments.approve', [$course, $row]) }}" method="POST" class="d-inline">
                      @csrf
                      <button type="submit" class="btn btn-sm btn-success">Tasdiqlash</button>
                    </form>
                    <form action="{{ route('admin.courses.enrollments.reject', [$course, $row]) }}" method="POST" class="d-inline" onsubmit="return confirm('Rad etilsinmi?');">
                      @csrf
                      <button type="submit" class="btn btn-sm btn-outline-danger">Rad</button>
                    </form>
                  @else
                    <span class="text-muted small">—</span>
                  @endif
                  <form action="{{ route('admin.courses.enrollments.destroy', [$course, $row]) }}" method="POST" class="d-inline ms-1" onsubmit="return confirm('Yozilish butunlay olib tashlansinmi?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-secondary">Olib tashlash</button>
                  </form>
                </td>
              </tr>
            @empty
              <tr><td colspan="11"><p>Hozircha yozilishlar yo‘q.</p></td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
</section>
@endsection
