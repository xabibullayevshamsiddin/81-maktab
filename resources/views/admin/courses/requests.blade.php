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
      <p class="text-sm text-muted mb-0 mt-10">Email tasdiq kutilayotgan kurslar va ustozlarning kurs ochish ruxsati so'rovlari.</p>
    </div>

    <div class="card-style mb-30">
      <div class="title-wrapper pt-20 pb-10 px-3">
        <h6 class="mb-0">Kurs ochish ruxsati (teacher)</h6>
        <p class="text-sm text-muted mb-0">Ustoz profildan yuborgan so'rovlar — ruxsat berilgach ustoz kurs yaratishi mumkin.</p>
      </div>
      <div class="table-wrapper table-responsive px-3 pb-3">
        <table class="table">
          <thead>
            <tr>
              <th><h6>#</h6></th>
              <th><h6>Ustoz (user)</h6></th>
              <th><h6>Email</h6></th>
              <th><h6>So'rov vaqti</h6></th>
              <th><h6>Holat</h6></th>
              <th><h6>Amallar</h6></th>
            </tr>
          </thead>
          <tbody>
            @forelse($courseOpenRequestUsers as $reqUser)
              <tr>
                <td><p>{{ $reqUser->id }}</p></td>
                <td><p><strong>{{ $reqUser->name }}</strong></p></td>
                <td><p>{{ $reqUser->email }}</p></td>
                <td><p>{{ $reqUser->course_open_requested_at?->format('Y-m-d H:i') ?? '—' }}</p></td>
                <td>
                  @if((int) ($reqUser->created_courses_count ?? 0) >= 1)
                    <span class="badge bg-secondary">Kurs bor</span>
                  @elseif((int) ($reqUser->active_teacher_profile_count ?? 0) < 1)
                    <span class="badge bg-warning text-dark">Profil yo'q</span>
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
                      <form action="{{ route('user.course-open.reject', $reqUser) }}" method="POST" class="d-inline" data-confirm="Rad etilsinmi?" data-confirm-title="So‘rovni rad etish" data-confirm-variant="primary" data-confirm-ok="Rad etish">
                        @csrf
                        <button type="submit" class="main-btn danger-btn btn-sm btn-hover">Rad etish</button>
                      </form>
                    </div>
                  @else
                    <span class="text-muted small">—</span>
                  @endif
                </td>
              </tr>
            @empty
              <tr><td colspan="6"><p class="mb-0">Kurs ochish uchun kutilayotgan so'rov yo'q.</p></td></tr>
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

    @include('admin.partials.search-bar', [
      'placeholder' => 'Kurs nomi, ustoz nomi...',
      'action' => route('admin.courses.requests'),
    ])

    <div class="card-style mb-30">
      <div class="title-wrapper pt-10 pb-10 px-3">
        <h6 class="mb-0">Email tasdiq kutilayotgan kurslar</h6>
        <p class="text-sm text-muted mb-0">Yaratilgan kurs email kod bilan tasdiqlanishi kutilmoqda.</p>
      </div>
      <div class="table-wrapper table-responsive">
        <table class="table">
          <thead>
            <tr>
              <th><h6>#</h6></th>
              <th><h6>Nomi</h6></th>
              <th><h6>Ustoz</h6></th>
              <th><h6>Muallif (User)</h6></th>
              <th><h6>Boshlanish</h6></th>
              <th style="min-width: 350px;"><h6>Amallar</h6></th>
            </tr>
          </thead>
          <tbody>
            @forelse($courses as $course)
              <tr>
                <td><p>{{ $course->id }}</p></td>
                <td><p><strong>{{ $course->title }}</strong></p></td>
                <td><p>{{ $course->teacher?->full_name ?: '-' }}</p></td>
                <td><p>{{ $course->creator?->name ?: '-' }}</p></td>
                <td><p>{{ $course->start_date?->format('Y-m-d') }}</p></td>
                <td>
                  <div style="display: flex; gap: 15px; align-items: flex-start; flex-wrap: wrap;">
                    <!-- Approve Form -->
                    <form action="{{ route('admin.courses.status', $course) }}" method="POST" data-confirm="Kurs tasdiqlansinmi? U ommaga e'lon qilinadi." data-confirm-title="Kursni tasdiqlash" data-confirm-variant="success" data-confirm-ok="Tasdiqlash">
                      @csrf
                      @method('PUT')
                      <input type="hidden" name="status" value="published">
                      <button type="submit" class="main-btn success-btn btn-sm btn-hover"><i class="lni lni-checkmark"></i> Rozi bo'lish</button>
                    </form>

                    <!-- Reject Form -->
                    <form action="{{ route('admin.courses.status', $course) }}" method="POST" style="flex: 1; min-width: 200px; display:flex; flex-direction:column; gap:5px;" data-confirm="Kurs rad etilsinmi? U ustozga qaytariladi." data-confirm-title="Kursni rad etish" data-confirm-variant="primary" data-confirm-ok="Rad etish">
                      @csrf
                      @method('PUT')
                      <input type="hidden" name="status" value="draft">
                      <textarea name="rejection_reason" class="form-control form-control-sm" placeholder="Rad etish sababi (ixtiyoriy)..." rows="2" style="font-size: 13px;"></textarea>
                      <button type="submit" class="main-btn danger-btn btn-sm btn-hover" style="align-self: flex-start;"><i class="lni lni-close"></i> Rad etish</button>
                    </form>
                  </div>
                </td>
              </tr>
            @empty
              <tr><td colspan="6"><p>Hozircha tekshiruvga yuborilgan kurslar yo'q.</p></td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
      @if($courses->hasPages())
        <div class="p-3">
          {{ $courses->links() }}
        </div>
      @endif
    </div>
  </div>
</section>
@endsection
