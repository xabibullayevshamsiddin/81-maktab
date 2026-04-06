@extends('admin.layouts.main')

@section('title', 'Kategoriya yaratish')

@section('content')
<section class="table-components">
  <div class="container-fluid">
    <div class="title-wrapper pt-30">
      <div class="row align-items-center">
        <div class="col-md-6">
          <div class="title">
            <h2>Kategoriya yaratish</h2>
          </div>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-lg-6">
        <div class="card-style mb-30">
          @if ($errors->any())
            <div class="alert-box danger-alert mb-20">
              <div class="alert">{{ $errors->first() }}</div>
            </div>
          @endif

          <form action="{{ route('categories.store') }}" method="POST">
            @csrf

            <div class="mb-3">
              <label class="form-label">Kategoriya nomi</label>
              <input type="text" class="form-control" name="name" value="{{ old('name') }}" required>
            </div>

            <div class="mb-3">
              <label class="form-label">Kategoriya nomi (EN, ixtiyoriy)</label>
              <input type="text" class="form-control" name="name_en" value="{{ old('name_en') }}">
            </div>

            <button type="submit" class="btn btn-success">Saqlash</button>
            <a href="{{ route('categories.index') }}" class="btn btn-danger">Bekor qilish</a>
          </form>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection

