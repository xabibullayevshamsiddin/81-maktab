@extends('admin.layouts.main')

@section('title', 'Kategoriyalar')

@section('content')
<section class="table-components">
  <div class="container-fluid">
    <div class="title-wrapper pt-30">
      <div class="row align-items-center">
        <div class="col-md-6">
          <div class="title">
            <h2>Kategoriyalar</h2>
          </div>
        </div>
        <div class="col-md-6">
          <div class="breadcrumb-wrapper">
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Kategoriyalar</li>
              </ol>
            </nav>
          </div>
        </div>
      </div>
    </div>

    <div class="tables-wrapper">
      <div class="row">
        <div class="col-lg-12">
          <div class="card-style mb-30">
            <div class="d-flex justify-content-between align-items-center mb-20">
              <h6 class="mb-0">Barcha kategoriyalar</h6>
              <a href="{{ route('categories.create') }}" class="btn btn-success">Kategoriya qo'shish</a>
            </div>

            @include('admin.partials.search-bar', [
              'placeholder' => 'Kategoriya nomi yoki slug...',
              'action' => route('categories.index'),
            ])

            @if (session('success'))
              <div class="alert-box success-alert mb-20">
                <div class="alert">{{ session('success') }}</div>
              </div>
            @endif

            @if (session('error'))
              <div class="alert-box danger-alert mb-20">
                <div class="alert">{{ session('error') }}</div>
              </div>
            @endif

            <div class="table-wrapper table-responsive">
              <table class="table">
                <thead>
                  <tr>
                    <th><h6>#</h6></th>
                    <th><h6>Nomi</h6></th>
                    <th><h6>Postlar soni</h6></th>
                    <th><h6>Amallar</h6></th>
                  </tr>
                </thead>
                <tbody>
                  @forelse ($categories as $category)
                    <tr>
                      <td><p>{{ $category->id }}</p></td>
                      <td><p>{{ $category->name }}</p></td>
                      <td><p>{{ $category->posts_count }}</p></td>
                      <td>
                        <div class="action">
                          <a href="{{ route('categories.edit', $category->id) }}" class="text-warning me-2" title="Tahrirlash">
                            <i class="lni lni-pencil-alt"></i>
                          </a>
                          <form action="{{ route('categories.destroy', $category->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Kategoriyani ochirishni xohlaysizmi?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-danger" title="O'chirish" style="background:none;border:none;padding:0;">
                              <i class="lni lni-trash-can"></i>
                            </button>
                          </form>
                        </div>
                      </td>
                    </tr>
                  @empty
                    <tr>
                      <td colspan="4"><p>Hozircha kategoriya yo'q.</p></td>
                    </tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection

