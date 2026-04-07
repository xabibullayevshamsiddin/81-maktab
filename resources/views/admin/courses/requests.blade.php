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
    </div>

    @include('admin.partials.search-bar', [
      'placeholder' => 'Kurs nomi, ustoz nomi...',
      'action' => route('admin.courses.requests'),
    ])

    <div class="card-style mb-30">
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
                    <form action="{{ route('admin.courses.status', $course) }}" method="POST" onsubmit="return confirm('Kurs tasdiqlansinmi? U ommaga e\'lon qilinadi.');">
                      @csrf
                      @method('PUT')
                      <input type="hidden" name="status" value="published">
                      <button type="submit" class="main-btn success-btn btn-sm btn-hover"><i class="lni lni-checkmark"></i> Rozi bo'lish</button>
                    </form>

                    <!-- Reject Form -->
                    <form action="{{ route('admin.courses.status', $course) }}" method="POST" style="flex: 1; min-width: 200px; display:flex; flex-direction:column; gap:5px;" onsubmit="return confirm('Kurs rad etilsinmi? U ustozga qaytariladi.');">
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
