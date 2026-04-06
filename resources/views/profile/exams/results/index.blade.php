<x-loyouts.main title="Imtihonlar">
@push('page_styles')
    <link rel="stylesheet" href="/temp/css/profile-exams.css?v={{ filemtime(public_path('temp/css/profile-exams.css')) }}">
@endpush
<div class="container exam-public-container"><div class="row"><div class="col-12">
<div class="row">
  <div class="col-lg-12">
    <div class="exam-public-card mb-30">
      <div style="display:flex;flex-wrap:wrap;align-items:flex-end;justify-content:space-between;gap:16px;margin-bottom:20px;">
        <div>
          <h6 class="mb-10">Imtihon natijalari</h6>
          <p class="text-sm" style="color:#64748b;margin:0;">Imtihonni tanlang — faqat shu imtihonni topshirganlar chiqadi.</p>
        </div>
        <form method="get" action="{{ route('profile.exams.results') }}" style="min-width:260px;flex:1;max-width:420px;">
          <label class="text-sm" style="display:block;margin-bottom:6px;font-weight:600;">Imtihon</label>
          @if(request()->filled('q'))
            <input type="hidden" name="q" value="{{ request('q') }}">
          @endif
          <select name="exam_id" class="form-control" style="width:100%;padding:10px 12px;border-radius:8px;border:1px solid #e2e8f0;" onchange="this.form.submit()">
            <option value="">— Barcha imtihonlar —</option>
            @foreach($exams as $ex)
              <option value="{{ $ex->id }}" {{ (string) $selectedExamId === (string) $ex->id ? 'selected' : '' }}>
                {{ $ex->title }}
              </option>
            @endforeach
          </select>
        </form>
      </div>

      @include('admin.partials.search-bar', [
        'placeholder' => 'Ism, email yoki telefon bo‘yicha...',
        'action' => route('profile.exams.results'),
        'hidden' => array_filter(['exam_id' => $selectedExamId]),
      ])

      <div class="table-wrapper exam-public-table-responsive">
        <table class="exam-public-table">
          <thead>
          <tr>
            <th>#</th>
            <th>Ism</th>
            <th>Telefon</th>
            <th>Email</th>
            @if(!$selectedExamId)
              <th>Imtihon</th>
            @endif
            <th>Ball</th>
            <th>Qoidabuzarlik</th>
            <th>Natija</th>
            <th>To‘g‘ri</th>
            <th>Holat</th>
            <th>Vaqt</th>
            <th>Amal</th>
          </tr>
          </thead>
          <tbody>
          @forelse($results as $result)
            <tr>
              <td>{{ $result->id }}</td>
              <td>{{ $result->user->name ?? '—' }}</td>
              <td>{{ $result->user->phone ?? '—' }}</td>
              <td style="font-size:13px;">{{ $result->user->email ?? '—' }}</td>
              @if(!$selectedExamId)
                <td>{{ $result->exam->title ?? '—' }}</td>
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
              <td>{{ $result->status }}</td>
              <td style="white-space:nowrap;font-size:13px;">{{ $result->submitted_at?->format('d.m.Y H:i') ?? '-' }}</td>
                  <a href="{{ route('profile.exams.results.show', $result) }}" class="btn btn-info btn-sm">Ko'rish</a>
                  <form method="POST" action="{{ route('profile.exams.results.destroy', $result) }}" onsubmit="return confirm('Bu natijani o‘chirishni tasdiqlaysizmi?');" style="display:inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm">O‘chirish</button>
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
</div></div></div>
</x-loyouts.main>
