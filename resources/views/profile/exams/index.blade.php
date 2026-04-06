<x-loyouts.main title="Imtihonlar">
@push('page_styles')
    <link rel="stylesheet" href="{{ app_public_asset('temp/css/profile-exams.css') }}?v={{ filemtime(public_path('temp/css/profile-exams.css')) }}">
@endpush
<div class="container exam-public-container"><div class="row"><div class="col-12">
<div class="row">
  <div class="col-lg-12">
    <div class="exam-public-card mb-30">
      <div style="display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap;">
        <h6 class="mb-10">Imtihonlar</h6>
        <a href="{{ route('profile.exams.create') }}" class="btn">Yangi imtihon</a>
      </div>

      @include('admin.partials.search-bar', [
        'placeholder' => "Imtihon nomi bo'yicha...",
        'action' => route('profile.exams.index'),
      ])

      <div class="table-wrapper exam-public-table-responsive mt-20">
        <table class="exam-public-table">
          <thead>
            <tr>
              <th>#</th>
              <th>Nomi</th>
              <th>Savollar</th>
              <th>Ball</th>
              <th>O'tish</th>
              <th>Davomiylik</th>
              <th>Sinflar</th>
              <th>Holat</th>
              <th>Amal</th>
            </tr>
          </thead>
          <tbody>
            @if($exams->isEmpty())
              <tr>
                <td colspan="9">Hozircha imtihon yo'q.</td>
              </tr>
            @else
              @foreach($exams as $exam)
                <tr>
                  <td>{{ $exam->id }}</td>
                  <td>{{ $exam->title }}</td>
                  <td>{{ $exam->questions_count }} / {{ $exam->required_questions }}</td>
                  <td>{{ $exam->total_points }}</td>
                  <td>{{ $exam->passing_points ?? '-' }}</td>
                  <td>{{ $exam->duration_minutes }} daq.</td>
                  <td title="{{ $exam->allowedGradesLabel() }}">{{ $exam->allowedGradesLabel() }}</td>
                  <td>{{ $exam->is_active ? 'Faol' : 'Tayyorlanmoqda' }}</td>
                  <td style="display:flex;gap:8px;flex-wrap:wrap;">
                    <a href="{{ route('profile.exams.questions.index', $exam) }}" class="btn btn-primary  btn-sm">Savollar</a>
                    <a href="{{ route('profile.exams.edit', $exam) }}" class="btn btn-warning btn-sm">Tahrirlash</a>
                    <form method="POST" action="{{ route('profile.exams.destroy', $exam) }}">
                      @csrf
                      @method('DELETE')
                      <button class="btn btn-danger btn-sm" type="submit">O'chirish</button>
                    </form>
                  </td>
                </tr>
              @endforeach
            @endif
          </tbody>
        </table>
      </div>
      @if($exams->hasPages())
        <div class="p-3">
          {{ $exams->links() }}
        </div>
      @endif
    </div>
  </div>
</div>
</div></div></div>
</x-loyouts.main>
