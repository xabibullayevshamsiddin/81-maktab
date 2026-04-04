@extends('admin.layouts.main')

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

