@extends('admin.layouts.main')

@section('content')
<div class="row">
  <div class="col-lg-12">
    <div class="card-style mb-30">
      <h6 class="mb-20">Yangi imtihon — 1-bosqich (reja)</h6>
      <form method="POST" action="{{ route('admin.exams.store') }}">
        @csrf
        @include('admin.exams.partials.form', ['exam' => null])
      </form>
    </div>
  </div>
</div>
@endsection

