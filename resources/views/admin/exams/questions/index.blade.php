@extends('admin.layouts.main')

@section('content')
@php
  $qCount = $questions->count();
  $need = $exam->required_questions;
  $ready = $exam->is_active;
  $pointsOk = ($pointsSum ?? 0) === (int) $exam->total_points;
@endphp
<div class="row">
  <div class="col-lg-12">
    <div class="card-style mb-30">
      <div style="display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap;">
        <div>
          <h6 class="mb-5">2-bosqich — savollar: {{ $exam->title }}</h6>
          <p style="margin:0;font-size:13px;color:#64748b;">
            Qo‘shilgan: <strong>{{ $qCount }}</strong> / {{ $need }} ·
            Ballar yig‘indisi: <strong>{{ $pointsSum }}</strong> / {{ $exam->total_points }}
            @if($qCount >= $need && ! $pointsOk)
              <span style="color:#b91c1c;font-weight:600;"> — Yig‘indi umumiy ballga teng emas, imtihon faol bo‘lmaydi</span>
            @elseif($ready)
              <span style="color:#16a34a;font-weight:600;"> — Imtihon faol</span>
            @else
              <span style="color:#ca8a04;font-weight:600;"> — Yana {{ max(0, $need - $qCount) }} ta savol kerak</span>
            @endif
          </p>
        </div>
        @if($qCount < $need)
          <a href="{{ route('admin.exams.questions.create', $exam) }}" class="main-btn primary-btn btn-hover">Savol qo'shish</a>
        @else
          <span class="text-sm" style="color:#64748b;">Reja bo‘yicha savollar to‘ldi.</span>
        @endif
      </div>

      @include('admin.partials.search-bar', [
        'placeholder' => 'Savol matni bo‘yicha...',
        'action' => route('admin.exams.questions.index', $exam),
      ])

      @forelse($questions as $question)
        <div style="border:1px solid #e5e7eb; border-radius:10px; padding:12px; margin-top:12px;">
          <span style="font-size:12px;font-weight:700;color:#1565c0;">{{ (int) $question->points }} ball</span>
          <strong>{{ $question->body }}</strong>
          <ul style="margin:10px 0 0 18px;">
            @foreach($question->options as $option)
              <li>{{ $option->label }}. {{ $option->body }} @if($option->is_correct) <b>(to'g'ri)</b> @endif</li>
            @endforeach
          </ul>
          <div style="display:flex; gap:8px; margin-top:10px;">
            <a href="{{ route('admin.exams.questions.edit', [$exam, $question]) }}" class="main-btn warning-btn btn-hover btn-sm">Tahrirlash</a>
            <form method="POST" action="{{ route('admin.exams.questions.destroy', [$exam, $question]) }}">
              @csrf @method('DELETE')
              <button type="submit" class="main-btn danger-btn btn-hover btn-sm">O'chirish</button>
            </form>
          </div>
        </div>
      @empty
        <p class="mt-20">Savollar hali qo'shilmagan.</p>
      @endforelse
    </div>
  </div>
</div>
@endsection

