@extends('admin.layouts.main')

@section('title', 'Kurslar nazorati')

@section('content')
@php
  $adminUser = auth()->user();
  $canManageAllCourses = $adminUser->canManageSystem();
@endphp
<section class="table-components">
  <div class="container-fluid">
    <div class="title-wrapper pt-30">
      <div class="row align-items-center">
        <div class="col-md-6"><div class="title"><h2>@if($canManageAllCourses)Kurslar nazorati @else Mening kurslarim @endif</h2></div></div>
        <div class="col-md-6 text-end">
          @if($canManageAllCourses)
            <a href="{{ route('admin.course-enrollments.index') }}" class="btn btn-primary btn-sm">Barcha yozilishlar</a>
          @endif
        </div>
      </div>
    </div>

    @include('admin.partials.search-bar', [
      'placeholder' => 'Kurs nomi, tavsif, ustoz ismi...',
      'action' => route('admin.courses.index'),
    ])

    <div class="card-style mb-30">
      <div class="table-wrapper table-responsive">
        <table class="table">
          <thead>
            <tr>
              <th><h6>#</h6></th>
              <th><h6>Nomi</h6></th>
              <th><h6>Ustoz</h6></th>
              <th><h6>Boshlanish</h6></th>
              <th><h6>Status</h6></th>
              <th><h6>Yozilishlar</h6></th>
              <th><h6>Amallar</h6></th>
            </tr>
          </thead>
          <tbody>
            @forelse($courses as $course)
              @php
                $canManage = $canManageAllCourses || $adminUser->ownsCourse($course);
              @endphp
              <tr>
                <td><p>{{ $course->id }}</p></td>
                <td><p><strong>{{ $course->title }}</strong></p></td>
                <td><p>{{ $course->instructorName() }}</p></td>
                <td><p>{{ $course->start_date?->format('Y-m-d') }}</p></td>
                <td>
                  @if($canManage)
                    <form action="{{ route('admin.courses.status', $course) }}" method="POST">
                      @csrf
                      @method('PUT')
                      <select name="status" onchange="this.form.submit()" class="form-select form-select-sm" style="width:auto;">
                        <option value="draft" {{ $course->status === 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="pending_verification" {{ $course->status === 'pending_verification' ? 'selected' : '' }}>Pending</option>
                        <option value="published" {{ $course->status === 'published' ? 'selected' : '' }}>Published</option>
                      </select>
                    </form>
                  @else
                    <p>{{ $course->status }}</p>
                  @endif
                </td>
                <td>
                  <a href="{{ route('admin.courses.enrollments', $course) }}" class="text-primary">
                    {{ (int) $course->enrollments_count }} ta
                  </a>
                </td>
                <td>
                  @if($canManage)
                    @php
                      $editCourseUrl = $canManageAllCourses ? route('admin.courses.edit', $course) : route('teacher.courses.edit', $course);
                      $destroyCourseUrl = $canManageAllCourses ? route('admin.courses.destroy', $course) : route('teacher.courses.destroy', $course);
                    @endphp
                    <a href="{{ $editCourseUrl }}" class="btn btn-sm btn-outline-primary mb-1">Tahrirlash</a>
                    <form action="{{ $destroyCourseUrl }}" method="POST" class="d-inline" data-confirm="Kurs o‘chirilsinmi? Barcha yozilishlar ham o‘chadi." data-confirm-title="Kursni o'chirish" data-confirm-variant="danger" data-confirm-ok="O'chirish">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn-sm btn-outline-danger">O‘chirish</button>
                    </form>
                  @else
                    <span class="text-muted small">—</span>
                  @endif
                </td>
              </tr>
            @empty
              <tr><td colspan="7"><p>Hozircha kurslar yo'q.</p></td></tr>
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
