<x-loyouts.main title="Imtihonlar">
@push('page_styles')
    <link rel="stylesheet" href="{{ app_public_asset('temp/css/profile-exams.css') }}?v={{ filemtime(public_path('temp/css/profile-exams.css')) }}">
@endpush
<div class="container exam-public-container"><div class="row"><div class="col-12">
<div class="row">
  <div class="col-lg-12">
    <div class="exam-public-card mb-30">
      <h6 class="mb-20">Savol qo'shish: {{ $exam->title }}</h6>
      <form method="POST" action="{{ route('profile.exams.questions.store', $exam) }}" enctype="multipart/form-data" novalidate>
        @csrf
        @include('profile.exams.questions.partials.form', ['question' => null])
      </form>
    </div>
  </div>
</div>
</div></div></div>
</x-loyouts.main>
