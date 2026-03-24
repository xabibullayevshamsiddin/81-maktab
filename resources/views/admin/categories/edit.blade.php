@extends('admin.layouts.main')

@section('title', 'Kategoriya tahrirlash')

@section('content')
<section class="table-components">
  <div class="container-fluid">
    <div class="title-wrapper pt-30">
      <div class="row align-items-center">
        <div class="col-md-6">
          <div class="title">
            <h2>Kategoriya tahrirlash</h2>
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

          <form action="{{ route('categories.update', $category->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-3">
              <label class="form-label">Kategoriya nomi</label>
              <input type="text" class="form-control" name="name" value="{{ old('name', $category->name) }}" required>
            </div>

            <button type="submit" class="btn btn-primary">Yangilash</button>
            <a href="{{ route('categories.index') }}" class="btn btn-danger">Bekor qilish</a>
          </form>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection

