@extends('admin.layouts.main')

@section('content')
<div class="row">
  <div class="col-lg-12">
    <div class="card-style mb-30">
      <div style="display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap;">
        <h6 class="mb-10">Imtihonlar</h6>
        <a href="{{ route('admin.exams.create') }}" class="main-btn primary-btn btn-hover">Yangi imtihon</a>
      </div>

      @include('admin.partials.search-bar', [
        'placeholder' => "Imtihon nomi bo'yicha...",
        'action' => route('admin.exams.index'),
      ])

      <div class="table-wrapper table-responsive mt-20">
        <table class="table">
          <thead>
            <tr>
              <th>#</th>
              <th>Nomi</th>
              <th>Savollar</th>
              <th>Ball</th>
              <th>O'tish</th>
              <th>Davomiylik</th>
              <th>Boshlash (reja)</th>
              <th>Sinflar</th>
              <th>Holat</th>
              <th>Amal</th>
            </tr>
          </thead>
          <tbody>
            @if($exams->isEmpty())
              <tr>
                <td colspan="10">Hozircha imtihon yo'q.</td>
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
                  <td>{{ $exam->availableFromLabel() ?? '—' }}</td>
                  <td title="{{ $exam->allowedGradesLabel() }}">{{ $exam->allowedGradesLabel() }}</td>
                  <td>{{ $exam->is_active ? 'Faol' : 'Tayyorlanmoqda' }}</td>
                  <td style="display:flex;gap:8px;flex-wrap:wrap;">
                    <a href="{{ route('admin.exams.questions.index', $exam) }}" class="main-btn dark-btn btn-hover btn-sm">Savollar</a>
                    <a href="{{ route('admin.exams.edit', $exam) }}" class="main-btn warning-btn btn-hover btn-sm">Tahrirlash</a>
                    <form method="POST" action="{{ route('admin.exams.destroy', $exam) }}">
                      @csrf
                      @method('DELETE')
                      <button class="main-btn danger-btn btn-hover btn-sm" type="submit">O'chirish</button>
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
@endsection
