@extends('admin.layouts.main')
@section('title', 'Kitob kategoriyalari')
@section('content')
<section class="table-components">
  <div class="container-fluid">
    <div class="title-wrapper pt-30">
      <div class="row align-items-center">
        <div class="col-md-6"><div class="title"><h2>Kitob kategoriyalari</h2></div></div>
        <div class="col-md-6">
          <div class="breadcrumb-wrapper">
            <nav><ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
              <li class="breadcrumb-item"><a href="{{ route('admin.books.index') }}">Kitoblar</a></li>
              <li class="breadcrumb-item active">Kategoriyalar</li>
            </ol></nav>
          </div>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-lg-5">
        <div class="card-style mb-30">
          <h6 class="mb-20">Yangi kategoriya</h6>
          @if(session('success'))<div class="alert-box success-alert mb-20"><div class="alert">{{ session('success') }}</div></div>@endif
          @if(session('error'))<div class="alert-box danger-alert mb-20"><div class="alert">{{ session('error') }}</div></div>@endif
          <form action="{{ route('admin.book-categories.store') }}" method="POST">
            @csrf
            <div class="mb-3">
              <label class="form-label">Nomi (UZ) *</label>
              <input type="text" name="name" class="form-control" required maxlength="100" value="{{ old('name') }}">
            </div>
            <div class="mb-3">
              <label class="form-label">Nomi (EN)</label>
              <input type="text" name="name_en" class="form-control" maxlength="100" value="{{ old('name_en') }}">
            </div>
            <div class="mb-3">
              <label class="form-label">Tartib raqami</label>
              <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', 0) }}">
            </div>
            <button type="submit" class="btn btn-success">Qo'shish</button>
          </form>
        </div>
      </div>
      <div class="col-lg-7">
        <div class="card-style mb-30">
          <h6 class="mb-20">Kategoriyalar ro'yxati</h6>
          <div class="table-wrapper table-responsive">
            <table class="table">
              <thead>
                <tr>
                  <th><h6>#</h6></th><th><h6>Nomi</h6></th><th><h6>EN</h6></th>
                  <th><h6>Kitoblar</h6></th><th><h6>Amal</h6></th>
                </tr>
              </thead>
              <tbody>
                @forelse($categories as $cat)
                  <tr>
                    <td><p>{{ $loop->iteration }}</p></td>
                    <td><p>{{ $cat->name }}</p></td>
                    <td><p>{{ $cat->name_en ?? '—' }}</p></td>
                    <td><p>{{ $cat->books_count }}</p></td>
                    <td>
                      <form action="{{ route('admin.book-categories.destroy', $cat) }}" method="POST" style="display:inline;"
                        data-confirm="Kategoriyani o'chirishni xohlaysizmi?" data-confirm-title="O'chirish"
                        data-confirm-variant="danger" data-confirm-ok="O'chirish">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-danger" style="background:none;border:none;padding:0;">
                          <i class="lni lni-trash-can"></i>
                        </button>
                      </form>
                    </td>
                  </tr>
                @empty
                  <tr><td colspan="5"><p>Kategoriyalar yo'q.</p></td></tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection
