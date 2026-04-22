@extends('admin.layouts.main')

@section('title', 'Ustoz tahrirlash')

@section('content')
<section class="tab-components">
  <div class="container-fluid">
    <div class="title-wrapper pt-30">
      <div class="row align-items-center">
        <div class="col-md-6"><div class="title"><h2>Ustoz tahrirlash</h2></div></div>
      </div>
    </div>

    <div class="form-elements-wrapper">
      <div class="row">
        <div class="col-lg-12">
          <div class="card-style mb-30">
            <form action="{{ route('teachers.update', $teacher) }}" method="POST" enctype="multipart/form-data">
              @csrf
              @method('PUT')
              @include('admin.teachers.partials.form', ['teacher' => $teacher])
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection

