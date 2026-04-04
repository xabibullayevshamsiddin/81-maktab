@extends('admin.layouts.main')

@section('content')
<div class="row">
  <div class="col-lg-12">
    <div class="card-style mb-30">
      <h6 class="mb-20">Savolni tahrirlash: {{ $exam->title }}</h6>
      <form method="POST" action="{{ route('admin.exams.questions.update', [$exam, $question]) }}">
        @csrf
        @method('PUT')
        @include('admin.exams.questions.partials.form', ['question' => $question])
      </form>
    </div>
  </div>
</div>
@endsection

