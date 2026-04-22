@extends('admin.layouts.main')

@push('admin_styles')
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.css" crossorigin="anonymous">
  <link rel="stylesheet" href="{{ app_public_asset('temp/css/profile-exams.css') }}?v={{ filemtime(public_path('temp/css/profile-exams.css')) }}">
@endpush

@section('page_scripts')
  <script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.js" crossorigin="anonymous"></script>
  <script src="{{ app_public_asset('temp/js/exam-available-from-picker.js') }}?v={{ filemtime(public_path('temp/js/exam-available-from-picker.js')) }}"></script>
@endsection

@section('content')
<div class="row">
  <div class="col-lg-12">
    <div class="card-style mb-30">
      <h6 class="mb-20">Imtihonni tahrirlash</h6>
      <form method="POST" action="{{ route('admin.exams.update', $exam) }}">
        @csrf
        @method('PUT')
        @include('admin.exams.partials.form', ['exam' => $exam])
      </form>
    </div>
  </div>
</div>
@endsection

