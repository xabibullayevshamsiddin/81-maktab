@extends('admin.layouts.main')

@section('content')
<div class="row">
  <div class="col-lg-12">
    <div class="card-style mb-30">
      <div style="display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap;">
        <div>
          <h6 class="mb-5">IELTS Testlari boshqaruvi</h6>
          <p class="text-sm text-gray">IELTS testlari, bo'limlari, matnlar (passages), audio va savollarni boshqarish</p>
        </div>
        <a href="{{ route('admin.ielts.create') }}" class="main-btn primary-btn btn-hover">
          <i class="mdi mdi-plus me-1"></i> Yangi IELTS testi
        </a>
      </div>

      <div class="mt-20">
        <form method="GET" action="{{ route('admin.ielts.index') }}" style="max-width: 400px; display: flex; gap: 8px;">
          <input type="text" name="q" value="{{ $q ?? '' }}" placeholder="Test nomi bo'yicha..." class="form-control" style="height: 42px; border-radius: 8px;">
          <button type="submit" class="main-btn light-btn btn-hover" style="height: 42px; padding: 0 16px;">Qidirish</button>
          @if(!empty($q))
            <a href="{{ route('admin.ielts.index') }}" class="main-btn danger-btn-outline btn-hover" style="height: 42px; padding: 0 12px; display: flex; align-items: center;">Tozalash</a>
          @endif
        </form>
      </div>

      <div class="table-wrapper table-responsive mt-20">
        <table class="table">
          <thead>
            <tr>
              <th>#</th>
              <th>Nomi</th>
              <th>Turi</th>
              <th>Vaqt</th>
              <th>Bo'limlar</th>
              <th>Savollar</th>
              <th>Urinishlar</th>
              <th>Holat</th>
              <th>Amallar</th>
            </tr>
          </thead>
          <tbody>
            @if($tests->isEmpty())
              <tr>
                <td colspan="9" class="text-center py-4 text-muted">Hozircha IELTS testlari mavjud emas.</td>
              </tr>
            @else
              @foreach($tests as $test)
                @php
                  $totalQuestions = $test->sections->flatMap->passages->flatMap->questions->count();
                @endphp
                <tr>
                  <td>{{ $test->id }}</td>
                  <td>
                    <strong>{{ $test->title }}</strong>
                  </td>
                  <td>
                    @if($test->type === 'placement')
                      <span class="badge bg-primary">Placement</span>
                    @else
                      <span class="badge bg-purple" style="background:#7c3aed;color:#fff;">Full Mock</span>
                    @endif
                  </td>
                  <td>{{ $test->time_limit_minutes }} daqiqa</td>
                  <td>{{ $test->sections_count ?? $test->sections->count() }} ta</td>
                  <td>
                    <span class="badge bg-info text-dark" style="font-size: 0.85rem;">{{ $totalQuestions }} ta</span>
                  </td>
                  <td>
                    <a href="{{ route('admin.ielts.results', $test) }}" class="badge bg-secondary text-white" style="font-size: 0.85rem; text-decoration: none;">
                      {{ $test->attempts_count }} ta natija
                    </a>
                  </td>
                  <td>
                    @if($test->is_active)
                      <span class="badge bg-success">Faol</span>
                    @else
                      <span class="badge bg-secondary">Nofaol</span>
                    @endif
                  </td>
                  <td>
                    <div style="display:flex;gap:6px;flex-wrap:wrap;">
                      <a href="{{ route('admin.ielts.show', $test) }}" class="main-btn primary-btn btn-hover btn-sm" title="Savollar va bo'limlarni boshqarish">
                        <i class="mdi mdi-format-list-bulleted me-1"></i> Savollar
                      </a>
                      <a href="{{ route('admin.ielts.results', $test) }}" class="main-btn light-btn btn-hover btn-sm" title="Urinishlar va natijalar">
                        <i class="mdi mdi-chart-box-outline"></i> Natijalar
                      </a>
                      <a href="{{ route('admin.ielts.edit', $test) }}" class="main-btn warning-btn btn-hover btn-sm" title="Tahrirlash">
                        <i class="mdi mdi-pencil"></i>
                      </a>
                      <form method="POST" action="{{ route('admin.ielts.destroy', $test) }}" onsubmit="return confirm('Rostdan ham ushbu testni va uning barcha bo\'lim, savollarini o\'chirmoqchimisiz?');" style="display:inline;">
                        @csrf
                        @method('DELETE')
                        <button class="main-btn danger-btn btn-hover btn-sm" type="submit" title="O'chirish">
                          <i class="mdi mdi-delete"></i>
                        </button>
                      </form>
                    </div>
                  </td>
                </tr>
              @endforeach
            @endif
          </tbody>
        </table>
      </div>

      <div class="mt-20">
        {{ $tests->links() }}
      </div>
    </div>
  </div>
</div>
@endsection
