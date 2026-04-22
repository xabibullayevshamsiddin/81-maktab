@extends('admin.layouts.main')

@section('title', 'AI Bilimlar Bazasi')

@section('content')
<section class="table-components">
  <div class="container-fluid">
    <div class="title-wrapper pt-30">
      <div class="row align-items-center">
        <div class="col-md-6">
          <div class="title">
            <h2>AI Bilimlar Bazasi</h2>
          </div>
        </div>
        <div class="col-md-6">
          <div class="breadcrumb-wrapper">
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">AI Bilimlar</li>
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
              <h6 class="mb-0">Barcha savol-javoblar</h6>
              <a href="{{ route('ai-knowledges.create') }}" class="btn btn-primary">Yangi qo'shish</a>
            </div>

            @if (session('success'))
              <div class="alert-box success-alert mb-20">
                <div class="alert">{{ session('success') }}</div>
              </div>
            @endif

            <div class="table-wrapper table-responsive">
              <table class="table">
                <thead>
                  <tr>
                    <th><h6>Savol (Pattern)</h6></th>
                    <th><h6>Kalit so'zlar</h6></th>
                    <th><h6>Kategoriya</h6></th>
                    <th><h6>Holati</h6></th>
                    <th><h6>Amallar</h6></th>
                  </tr>
                </thead>
                <tbody>
                  @forelse ($knowledges as $item)
                    <tr>
                      <td>
                        <p><strong>{{ $item->question }}</strong></p>
                        @if($item->question_en) <small class="text-muted">{{ $item->question_en }}</small> @endif
                      </td>
                      <td><p>{{ $item->keywords }}</p></td>
                      <td><p>{{ $item->category ?? '-' }}</p></td>
                      <td>
                        <span class="status-btn {{ $item->is_active ? 'success-btn' : 'close-btn' }}">
                          {{ $item->is_active ? 'Faol' : 'Faol emas' }}
                        </span>
                      </td>
                      <td>
                        <div class="action">
                          <a href="{{ route('ai-knowledges.edit', $item->id) }}" class="text-warning me-2" title="Tahrirlash">
                            <i class="lni lni-pencil-alt"></i>
                          </a>
                          <form action="{{ route('ai-knowledges.destroy', $item->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Haqiqatan ham o\'chirmoqchimisiz?')">
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
                      <td colspan="5" class="text-center"><p>Hozircha ma'lumotlar yo'q.</p></td>
                    </tr>
                  @endforelse
                </tbody>
              </table>
            </div>
            @if($knowledges->hasPages())
              <div class="p-3">
                {{ $knowledges->links() }}
              </div>
            @endif
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection
