<x-loyouts.main title="Imtihonlar">
@push('page_styles')
    <link rel="stylesheet" href="{{ app_public_asset('temp/css/profile-exams.css') }}?v={{ filemtime(public_path('temp/css/profile-exams.css')) }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.css" crossorigin="anonymous">
@endpush
@push('page_scripts')
    <script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.js" crossorigin="anonymous"></script>
    <script src="{{ app_public_asset('temp/js/exam-available-from-picker.js') }}?v={{ filemtime(public_path('temp/js/exam-available-from-picker.js')) }}"></script>
@endpush
<div class="container exam-public-container"><div class="row"><div class="col-12">
<div class="row">
  <div class="col-lg-12">
    <div class="exam-public-card mb-30">
      <h6 class="mb-20">Imtihonni tahrirlash</h6>
      <form method="POST" action="{{ route('profile.exams.update', $exam) }}">
        @csrf
        @method('PUT')
        @include('profile.exams.partials.form', ['exam' => $exam])
      </form>
    </div>
  </div>
</div>
</div></div></div>
</x-loyouts.main>
