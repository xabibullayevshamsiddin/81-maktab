@extends('admin.layouts.main')

@section('title', 'Ustoz qo\'shish')

@section('content')
<section class="tab-components">
  <div class="container-fluid">
    <div class="title-wrapper pt-30">
      <div class="row align-items-center">
        <div class="col-md-6"><div class="title"><h2>Ustoz qo'shish</h2></div></div>
      </div>
    </div>

    <div class="form-elements-wrapper">
      <div class="row">
        <div class="col-lg-12">
          <div class="card-style mb-30">
            <form action="{{ route('teachers.store') }}" method="POST" enctype="multipart/form-data">
              @csrf
              @include('admin.teachers.partials.form', ['teacher' => null])
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection

