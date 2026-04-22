@extends('admin.layouts.main')

@section('content')
<div class="row">
  <div class="col-lg-12">
    <div class="card-style mb-30" style="max-width: 920px; margin: 0 auto;">
      <h6 class="mb-20">
        Natija: {{ $result->user->name }} - {{ $exam->title }}
        @if($exam?->trashed())
          <span style="color:#ef4444; font-size:14px; display:block; margin-top:4px;">(Ushbu imtihon tizimdan o'chirilgan)</span>
        @endif
      </h6>

      <div class="mb-30" style="background:#f8fafc; padding:16px; border-radius:12px; border:1px solid #e2e8f0;">
        <p><strong>Boshlangan:</strong> {{ $result->started_at?->format('d.m.Y H:i') }}</p>
        <p><strong>Tugallangan:</strong> {{ $result->submitted_at?->format('d.m.Y H:i') ?? 'Tugallanmagan' }}</p>
        <p><strong>Holat:</strong> {{ $result->status === 'submitted' ? 'Topshirildi' : ($result->status === 'expired' ? 'Vaqti tugagan' : 'Jarayonda') }}</p>
        <p><strong>Sinf:</strong> {{ $result->user_grade ?? $result->user->grade ?? '—' }}</p>
        <p><strong>To'plangan ball:</strong> {{ $result->points_earned }} / {{ $result->points_max ?? $exam?->total_points }}</p>
        <p><strong>Natija:</strong>
          @if($result->passed === null)
            <span style="color:#ca8a04;font-weight:700;">Tekshiruvda</span>
          @elseif($result->passed)
            <span style="color:#16a34a;font-weight:700;">O'tdi</span>
          @else
            <span style="color:#dc2626;font-weight:700;">Yiqildi</span>
          @endif
        </p>
        <p><strong>Qoidabuzarliklar:</strong> {{ $result->rule_violation_count }} ta</p>
      </div>

      <h6 class="mb-15">Javoblar</h6>
      @foreach($result->answers as $index => $answer)
        @php
          $question = $answer->question;
          $isCorrect = $answer->isCorrectAnswer();
        @endphp
        <div class="mb-20 p-3" style="border:1px solid {{ $isCorrect ? '#10b981' : '#ef4444' }}; border-radius:12px;">
          <p><strong>{{ $index + 1 }}.</strong> {!! render_exam_rich_text($question->body) !!}</p>

          @if($question->isTextType())
            <div class="mt-10 p-2" style="background:#fff; border-radius:8px; border:1px solid #e5e7eb;">
              <p class="mb-1"><strong>O'quvchi javobi:</strong></p>
              <p style="white-space: pre-wrap; margin:0;">{{ $answer->text_answer ?? 'Javob berilmagan' }}</p>
            </div>

            @if($question->model_answer)
              <div class="mt-10 p-2" style="background:#e0f2fe; border-radius:8px; border:1px solid #bae6fd;">
                <p class="mb-1"><strong>Namunaviy javob:</strong></p>
                <p style="white-space: pre-wrap; margin:0;">{{ $question->model_answer }}</p>
              </div>
            @endif

            <hr>
            <form action="{{ route('admin.exams.results.grade', [$result, $answer]) }}" method="POST" style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
              @csrf
              <label><strong>Baholash:</strong></label>
              <select name="is_correct" class="form-control" style="max-width: 180px; display:inline-block;" required>
                <option value="1" {{ $answer->is_correct_override === true ? 'selected' : '' }}>To'g'ri ({{ $question->points }} ball)</option>
                <option value="0" {{ $answer->is_correct_override === false ? 'selected' : '' }}>Noto'g'ri (0 ball)</option>
                <option value="" {{ $answer->is_correct_override === null ? 'selected' : '' }} hidden>Baholanmagan</option>
              </select>
              <button type="submit" class="main-btn primary-btn btn-hover btn-sm">Saqlash</button>
            </form>
          @else
            <div class="mt-10 p-2" style="background:#fff; border-radius:8px; border:1px solid #e5e7eb;">
              <p style="margin:0;"><strong>Tanlangan variant:</strong> {{ $answer->option?->label ?? '-' }}. {!! render_exam_rich_text($answer->option?->body ?? '') !!}</p>
            </div>
            <p class="mt-2" style="color:{{ $isCorrect ? '#16a34a' : '#dc2626' }}; font-weight:700;">
              {{ $isCorrect ? "To'g'ri" : "Noto'g'ri" }} ({{ $isCorrect ? $question->points : 0 }}/{{ $question->points }} ball)
            </p>
          @endif
        </div>
      @endforeach

      <div class="mt-20" style="display:flex; gap:10px; flex-wrap:wrap;">
        <a href="{{ route('admin.exams.results', ['exam_id' => $exam->id]) }}" class="main-btn primary-btn btn-hover btn-sm">Ortga qaytish</a>
      </div>
    </div>
  </div>
</div>
@endsection
