@extends('admin.layouts.main')

@section('title', 'Postlar')

@section('content')
<section class="table-components">
  <div class="container-fluid">
    <div class="title-wrapper pt-30">
      <div class="row align-items-center">
        <div class="col-md-6">
          <div class="title">
            <h2>Postlar</h2>
          </div>
        </div>
        <div class="col-md-6">
          <div class="breadcrumb-wrapper">
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb">
                <li class="breadcrumb-item">
                  <a href="{{ route('dashboard') }}">Dashboard</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">
                  Postlar
                </li>
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
              <h6 class="mb-0">Barcha postlar</h6>
              <a href="{{ route('posts.create') }}" class="btn btn-success">Post qo'shish</a>
            </div>

            @if (session('success'))
              <div class="alert-box success-alert mb-20">
                <div class="alert">
                  {{ session('success') }}
                </div>
              </div>
            @endif

            <div class="table-wrapper table-responsive">
              <table class="table">
                <thead>
                  <tr>
                    <th><h6>#</h6></th>
                    <th><h6>Rasm</h6></th>
                    <th><h6>Nomi</h6></th>
                    <th><h6>Kategoriya</h6></th>
                    <th><h6>Qisqacha tavsif</h6></th>
                    <th><h6>Amallar</h6></th>
                  </tr>
                </thead>
                <tbody>
                  @forelse ($posts as $post)
                    <tr>
                      <td><p>{{ $loop->iteration }}</p></td>
                      <td>
                        @if ($post->image)
                          <img src="{{ asset('storage/' . $post->image) }}" alt="{{ $post->title }}" style="width:60px;height:60px;object-fit:cover;border-radius:8px;">
                        @else
                          <p>-</p>
                        @endif
                      </td>
                      <td class="min-width"><p>{{ $post->title }}</p></td>
                      <td class="min-width"><p>{{ $post->category->name }}</p></td>
                      <td class="min-width"><p>{{ \Illuminate\Support\Str::limit($post->short_content, 80) }}</p></td>
                      <td class="min-width">
                        <div class="action">
                          <a href="{{ route('posts.show', $post->id) }}" class="text-primary me-2" title="Ko'rish">
                            <i class="lni lni-eye"></i>
                          </a>
                          <a href="{{ route('posts.edit', $post->id) }}" class="text-warning me-2" title="Tahrirlash">
                            <i class="lni lni-pencil-alt"></i>
                          </a>
                          <form action="{{ route('posts.destroy', $post->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Postni ochirishni xohlaysizmi?');">
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
                      <td colspan="6"><p>Hozircha postlar yo'q.</p></td>
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
