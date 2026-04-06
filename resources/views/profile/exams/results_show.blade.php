<x-loyouts.main title="Imtihonlar">
@push('page_styles')
    <link rel="stylesheet" href="/temp/css/profile-exams.css?v={{ filemtime(public_path('temp/css/profile-exams.css')) }}">
@endpush
<div class="container exam-public-container"><div class="row"><div class="col-12">
<div class="row">
  <div class="col-lg-12">
    <div class="exam-public-card mb-30" style="max-width: 800px; margin: 0 auto;">
      <h6 class="mb-20">Natija: {{ $result->user->name }} — {{ $exam->title }}</h6>
      
      <div class="mb-30" style="background:#f9f9f9; padding:15px; border-radius:8px;">
        <p><strong>Boshlangan:</strong> {{ $result->started_at?->format('d.m.Y H:i') }}</p>
        <p><strong>Tugallangan:</strong> {{ $result->submitted_at?->format('d.m.Y H:i') ?? 'Tugallanmagan' }}</p>
        <p><strong>Holat:</strong> {{ $result->status === 'submitted' ? 'Topshirildi' : ($result->status === 'expired' ? 'Vaqti tugagan' : 'Jarayonda') }}</p>
        <p><strong>To'plangan ball:</strong> {{ $result->points_earned }} / {{ $result->points_max ?? $exam->total_points }}</p>
        <p><strong>Natija:</strong> 
          @if($result->passed === null)
            <span style="color:#ca8a04;font-weight:700;">Tekshiruvda</span>
          @elseif($result->passed)
            <span class="text-success">O'tdi</span>
          @else
            <span class="text-danger">Yiqildi</span>
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
        <div class="mb-20 p-3" style="border:1px solid {{ $isCorrect ? '#10b981' : '#ef4444' }}; border-radius:8px;">
          <p><strong>{{ $index + 1 }}.</strong> {!! nl2br(e($question->body)) !!}</p>
          
          @if($question->isTextType())
            <div class="mt-10 p-2" style="background:#fff; border-radius:4px;">
              <p class="mb-1"><strong>O'quvchi javobi:</strong></p>
              <p style="white-space: pre-wrap;">{{ $answer->text_answer ?? 'Javob berilmagan' }}</p>
            </div>
            
            @if($question->model_answer)
              <div class="mt-10 p-2" style="background:#e0f2fe; border-radius:4px;">
                <p class="mb-1"><strong>Namunaviy javob:</strong></p>
                <p style="white-space: pre-wrap;">{{ $question->model_answer }}</p>
              </div>
            @endif

            <hr>
            <form action="{{ route('profile.exams.grade', [$result, $answer]) }}" method="POST" style="display:flex; gap:10px; align-items:center;">
              @csrf
              <label><strong>Baholash:</strong></label>
              <select name="is_correct" class="form-control" style="max-width: 150px; display:inline-block;" required>
                <option value="1" {{ $answer->is_correct_override === 1 ? 'selected' : '' }}>To'g'ri ({{ $question->points }} ball)</option>
                <option value="0" {{ $answer->is_correct_override === 0 ? 'selected' : '' }}>Noto'g'ri (0 ball)</option>
                <option value="" {{ $answer->is_correct_override === null ? 'selected' : '' }} hidden>Baholanmagan</option>
              </select>
              <button type="submit" class="btn btn-primary btn-sm">Saqlash</button>
            </form>
          @else
            <div class="mt-10 p-2" style="background:#fff; border-radius:4px;">
              <p><strong>Tanlangan variant:</strong> {{ $answer->option?->label ?? '-' }}. {{ $answer->option?->body }}</p>
            </div>
            <p class="mt-2 text-{{ $isCorrect ? 'success' : 'danger' }}">
              {{ $isCorrect ? "To'g'ri" : "Noto'g'ri" }} ({{ $isCorrect ? $question->points : 0 }}/{{ $question->points }} ball)
            </p>
          @endif
        </div>
      @endforeach

      <div class="mt-20">
        <a href="{{ route('profile.exams.results') }}?exam_id={{ $exam->id }}" class="btn btn-primary">Ortga qaytish</a>
      </div>
    </div>
  </div>
</div>
</div></div></div>
</x-loyouts.main>
