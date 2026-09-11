@extends('admin.layouts.main')

@section('content')
<div class="row">
  <div class="col-lg-12">
    <div class="card-style mb-30">
      <div style="display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap;">
        <div>
          <div style="display:flex;align-items:center;gap:8px;margin-bottom:6px;">
            <a href="{{ route('admin.ielts.index') }}" class="text-sm text-primary" style="text-decoration:none;">
              <i class="mdi mdi-arrow-left"></i> IELTS testlariga qaytish
            </a>
          </div>
          <h5 class="mb-5">«{{ $test->title }}» natijalari</h5>
          <p class="text-sm text-gray">Ushbu testni topshirgan barcha foydalanuvchilar va ularning ballari</p>
        </div>
        <a href="{{ route('admin.ielts.show', $test) }}" class="main-btn primary-btn btn-hover btn-sm">
          <i class="mdi mdi-format-list-bulleted me-1"></i> Savollarni ko'rish
        </a>
      </div>

      <div class="table-wrapper table-responsive mt-20">
        <table class="table">
          <thead>
            <tr>
              <th>#</th>
              <th>Foydalanuvchi</th>
              <th>Holat</th>
              <th>Overall Band</th>
              <th>Reading</th>
              <th>Listening</th>
              <th>Writing</th>
              <th>Daraja</th>
              <th>Boshlangan</th>
              <th>Tugallangan</th>
            </tr>
          </thead>
          <tbody>
            @if($attempts->isEmpty())
              <tr>
                <td colspan="10" class="text-center py-4 text-muted">Ushbu test bo'yicha hali hech kim urinish qilmagan.</td>
              </tr>
            @else
              @foreach($attempts as $attempt)
                @php
                  $result = $attempt->result;
                  $bands = $result?->section_bands ?? [];
                @endphp
                <tr>
                  <td>{{ $attempt->id }}</td>
                  <td>
                    <strong>{{ $attempt->user?->name ?? 'O\'chirilgan user' }}</strong>
                    <div class="text-xs text-muted">{{ $attempt->user?->email }}</div>
                  </td>
                  <td>
                    @if($attempt->status === 'graded')
                      <span class="badge bg-success">Baholangan</span>
                    @elseif($attempt->status === 'submitted')
                      <span class="badge bg-warning text-dark">Topshirilgan</span>
                    @else
                      <span class="badge bg-info text-dark">Jarayonda</span>
                    @endif
                  </td>
                  <td>
                    @if($result?->overall_band !== null)
                      <span class="badge bg-primary" style="font-size: 0.95rem; font-weight: 700;">
                        {{ $result->overall_band }}
                      </span>
                    @else
                      <span class="text-muted">—</span>
                    @endif
                  </td>
                  <td>{{ $bands['reading'] ?? '—' }}</td>
                  <td>{{ $bands['listening'] ?? '—' }}</td>
                  <td>
                    {{ $bands['writing'] ?? '—' }}
                    @if(!empty($attempt->answers->whereNotNull('ai_feedback')->first()))
                      <i class="mdi mdi-robot text-primary" title="AI baholagan"></i>
                    @endif
                  </td>
                  <td>
                    <small class="text-muted">{{ $result?->level_label ?? '—' }}</small>
                  </td>
                  <td>
                    <small>{{ $attempt->started_at ? $attempt->started_at->format('d.m.Y H:i') : '—' }}</small>
                  </td>
                  <td>
                    <small>{{ $attempt->submitted_at ? $attempt->submitted_at->format('d.m.Y H:i') : '—' }}</small>
                  </td>
                </tr>
              @endforeach
            @endif
          </tbody>
        </table>
      </div>

      <div class="mt-20">
        {{ $attempts->links() }}
      </div>
    </div>
  </div>
</div>
@endsection
