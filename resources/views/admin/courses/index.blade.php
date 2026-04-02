@extends('admin.layouts.main')

@section('title', 'Kurslar nazorati')

@section('content')
<section class="table-components">
  <div class="container-fluid">
    <div class="title-wrapper pt-30">
      <div class="row align-items-center">
        <div class="col-md-6"><div class="title"><h2>Kurslar nazorati</h2></div></div>
      </div>
    </div>

    <div class="card-style mb-30">
      <div class="table-wrapper table-responsive">
        <table class="table">
          <thead>
            <tr>
              <th><h6>#</h6></th>
              <th><h6>Nomi</h6></th>
              <th><h6>Ustoz</h6></th>
              <th><h6>Muallif</h6></th>
              <th><h6>Boshlanish</h6></th>
              <th><h6>Status</h6></th>
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
                  <form action="{{ route('admin.courses.status', $course) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <select name="status" onchange="this.form.submit()" class="form-select form-select-sm" style="width:auto;">
                      <option value="draft" {{ $course->status === 'draft' ? 'selected' : '' }}>Draft</option>
                      <option value="pending_verification" {{ $course->status === 'pending_verification' ? 'selected' : '' }}>Pending</option>
                      <option value="published" {{ $course->status === 'published' ? 'selected' : '' }}>Published</option>
                    </select>
                  </form>
                </td>
              </tr>
            @empty
              <tr><td colspan="6"><p>Hozircha kurslar yo'q.</p></td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
</section>
@endsection

