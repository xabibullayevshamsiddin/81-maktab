@extends('admin.layouts.main')

@section('content')
<div class="row">
  <div class="col-lg-12">
    <div class="card-style mb-30">
      <div style="display:flex;flex-wrap:wrap;align-items:flex-end;justify-content:space-between;gap:16px;margin-bottom:20px;">
        <div>
          <h6 class="mb-10">Imtihon natijalari</h6>
          <p class="text-sm" style="color:#64748b;margin:0;">Imtihonni tanlang — faqat shu imtihonni topshirganlar chiqadi.</p>
        </div>
        <form method="get" action="{{ route('admin.exams.results') }}" style="min-width:260px;flex:1;max-width:420px;">
          <label class="text-sm" style="display:block;margin-bottom:6px;font-weight:600;">Imtihon</label>
          @if(request()->filled('q'))
            <input type="hidden" name="q" value="{{ request('q') }}">
          @endif
          <select name="exam_id" class="form-control" style="width:100%;padding:10px 12px;border-radius:8px;border:1px solid #e2e8f0;" onchange="this.form.submit()">
            <option value="">— Barcha imtihonlar —</option>
            @foreach($exams as $ex)
              <option value="{{ $ex->id }}" {{ (string) $selectedExamId === (string) $ex->id ? 'selected' : '' }}>
                {{ $ex->title }} @if($ex->trashed()) (O'chirilgan) @endif
              </option>
            @endforeach
          </select>
        </form>

        <form method="get" action="{{ route('admin.exams.results') }}" style="display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end;">
          @if(request()->filled('q'))
            <input type="hidden" name="q" value="{{ request('q') }}">
          @endif
          @if($selectedExamId)
            <input type="hidden" name="exam_id" value="{{ $selectedExamId }}">
          @endif
        </form>
      </div>

      @include('admin.partials.search-bar', [
        'placeholder' => 'Ism, email yoki telefon bo‘yicha...',
        'action' => route('admin.exams.results'),
        'hidden' => array_filter(['exam_id' => $selectedExamId, 'date_from' => request('date_from'), 'date_to' => request('date_to')]),
      ])

            <div style="display:flex;gap:10px;flex-wrap:wrap;margin:16px 0;">
        <a href="{{ route('admin.exams.results.export', array_filter(['exam_id' => $selectedExamId, 'date_from' => request('date_from'), 'date_to' => request('date_to')])) }}" class="main-btn primary-btn btn-hover btn-sm">
          <i class="mdi mdi-file-excel-outline" style="margin-right:4px;"></i> Excel export
        </a>
        <button type="button" class="main-btn light-btn btn-hover btn-sm" onclick="window.print()">
          <i class="mdi mdi-printer-outline" style="margin-right:4px;"></i> Chop etish
        </button>
      </div>

      <div class="table-wrapper table-responsive">
        <table class="table">
          <thead>
          <tr>
            <th>#</th>
            <th>O'quvchi</th>
            <th>Sinf</th>
            @if(!$selectedExamId)
              <th>Imtihon</th>
            @endif
            <th>Ball</th>
            <th>Qoidabuzarlik</th>
            <th>Natija</th>
            <th>To‘g‘ri</th>
            <th>Amal</th>
          </tr>
          </thead>
          <tbody>
          @forelse($results as $result)
            <tr>
              <td>{{ $result->id }}</td>
              <td>
                <div>
                  <strong>{{ $result->user->name ?? '—' }}</strong>
                  <div class="text-muted small">
                    {{ $result->user->phone ?? '' }}@if($result->user->phone && $result->user->email) / @endif{{ $result->user->email ?? '' }}
                  </div>
                </div>
              </td>
              <td>{{ $result->user_grade ?? $result->user->grade ?? '—' }}</td>
              @if(!$selectedExamId)
                <td>
                  {{ $result->exam->title ?? '—' }}
                  @if($result->exam?->trashed())
                    <span style="color:#b91c1c; font-size:10px; display:block;">(O'chirilgan)</span>
                  @endif
                </td>
              @endif
              <td>{{ $result->points_earned ?? '—' }} / {{ $result->points_max ?? '—' }}</td>
              <td style="white-space:nowrap;">
                @if((int) ($result->rule_violation_count ?? 0) > 0)
                  <span style="{{ (int) $result->rule_violation_count > 5 ? 'color:#b91c1c;font-weight:700;' : '' }}">{{ (int) $result->rule_violation_count }}</span>
                @else
                  0
                @endif
              </td>
              <td>
                @if($result->passed === null)
                  —
                @elseif($result->passed)
                  <span style="color:#16a34a;font-weight:700;">O‘tdi</span>
                @else
                  <span style="color:#b91c1c;font-weight:700;">Yiqildi</span>
                @endif
              </td>
              <td>{{ $result->score }} / {{ $result->total_questions }}</td>
              <td>
                <a href="{{ route('admin.exams.results.show', $result) }}" class="main-btn info-btn btn-hover btn-sm">Ko'rish</a>
                <form method="POST" action="{{ route('admin.exams.results.destroy', $result) }}" data-confirm="Bu natijani o‘chirishni tasdiqlaysizmi?" data-confirm-title="Natijani o'chirish" data-confirm-variant="danger" data-confirm-ok="O'chirish" style="display:inline;">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="main-btn danger-btn btn-hover btn-sm">O‘chirish</button>
                </form>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="{{ $selectedExamId ? 10 : 11 }}">
                @if($selectedExamId)
                  Bu imtihon bo‘yicha hali natija yo‘q.
                @else
                  Natija topilmadi.
                @endif
              </td>
            </tr>
          @endforelse
          </tbody>
        </table>
      </div>
      <div class="mt-20">{{ $results->links() }}</div>
    </div>
  </div>
</div>
@endsection
