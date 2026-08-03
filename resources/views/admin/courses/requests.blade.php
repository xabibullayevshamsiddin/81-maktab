@extends('admin.layouts.main')

@section('title', 'Kurs so\'rovlari')

@section('content')
@php
  $adminUser = auth()->user();
@endphp
<section class="table-components">
  <div class="container-fluid">
    <div class="title-wrapper pt-30">
      <div class="row align-items-center">
        <div class="col-md-6"><div class="title"><h2>Kurs so'rovlari</h2></div></div>
      </div>
      <p class="text-sm text-muted mb-0 mt-10">Email tasdiq kutilayotgan kurslar va teacher akkauntlarning kurs ochish ruxsati so'rovlari.</p>
    </div>

    <div class="card-style mb-30">
      <div class="title-wrapper pt-20 pb-10 px-3">
        <h6 class="mb-0">Kurs ochish ruxsati so'rovlari</h6>
        <p class="text-sm text-muted mb-0">Foydalanuvchilar yuborgan so'rovlar. Ruxsat berilgach foydalanuvchi bitta kurs yaratishi mumkin.</p>
      </div>
      <div class="table-wrapper table-responsive px-3 pb-3">
        <table class="table">
          <thead>
            <tr>
              <th><h6>#</h6></th>
              <th><h6>Foydalanuvchi</h6></th>
              <th><h6>Rol</h6></th>
              <th><h6>Email</h6></th>
              <th><h6>So'rov vaqti</h6></th>
              <th><h6>Sabab</h6></th>
              <th><h6>Holat</h6></th>
              <th><h6>Amallar</h6></th>
            </tr>
          </thead>
          <tbody>
            @forelse($courseOpenRequestUsers as $reqUser)
              <tr>
                <td><p>{{ $reqUser->id }}</p></td>
                <td><p><strong>{{ $reqUser->name }}</strong></p></td>
                <td>
                  <span class="badge bg-{{ $reqUser->isTeacher() ? 'primary' : ($reqUser->isAdmin() ? 'danger' : 'secondary') }}" style="font-size:11px;">
                    {{ $reqUser->roleRelation?->name ?? 'user' }}
                  </span>
                </td>
                <td><p>{{ $reqUser->email }}</p></td>
                <td><p>{{ $reqUser->course_open_requested_at?->format('Y-m-d H:i') ?? '-' }}</p></td>
                <td><p style="max-width: 360px; white-space: normal;">{{ \Illuminate\Support\Str::limit($reqUser->course_open_request_reason ?: '-', 220) }}</p></td>
                <td>
                  @if((int) ($reqUser->created_courses_count ?? 0) >= 1)
                    <span class="badge bg-secondary">Kurs bor</span>
                  @else
                    <span class="badge bg-info">Kutilmoqda</span>
                  @endif
                </td>
                <td>
                  @if($reqUser->id !== auth()->id() && auth()->user()->canManage($reqUser))
                    <div class="d-flex flex-wrap gap-2 align-items-center">
                      <form action="{{ route('user.course-open.approve', $reqUser) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="main-btn success-btn btn-sm btn-hover">Ruxsat berish</button>
                      </form>
                      <form action="{{ route('user.course-open.reject', $reqUser) }}" method="POST" class="d-inline" data-confirm="Rad etilsinmi?" data-confirm-title="So'rovni rad etish" data-confirm-variant="primary" data-confirm-ok="Rad etish">
                        @csrf
                        <button type="submit" class="main-btn danger-btn btn-sm btn-hover">Rad etish</button>
                      </form>
                    </div>
                  @else
                    <span class="text-muted small">-</span>
                  @endif
                </td>
              </tr>
            @empty
              <tr><td colspan="7"><p class="mb-0">Kurs ochish uchun kutilayotgan so'rov yo'q.</p></td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
      @if($courseOpenRequestUsers->hasPages())
        <div class="p-3 pt-0">
          {{ $courseOpenRequestUsers->links() }}
        </div>
      @endif
    </div>

  </div>
</section>
@endsection
