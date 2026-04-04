<x-loyouts.main title="Natija — {{ $result->exam->title }}">
  <main class="news exam-page">
    <div class="exam-page-inner" style="max-width: 560px;">
      <header class="exam-hero">
        <span class="exam-hero-badge">
          <i class="fa-solid fa-flag-checkered"></i>
          Yakunlandi
        </span>
        <h1 class="exam-title">{{ $result->exam->title }}</h1>
        <p class="exam-hero-lead">Holat: <strong>{{ $result->status }}</strong></p>
      </header>

      @if((int) ($result->rule_violation_count ?? 0) > 5)
        <p class="exam-result-pass is-fail" style="margin-bottom:16px;">
          <i class="fa-solid fa-ban"></i> Qoidabuzarlik ({{ (int) $result->rule_violation_count }} marta) sababli imtihon <strong>0 ball</strong> bilan yakunlandi — <strong>yiqildi</strong>.
        </p>
      @endif

      @if($result->passed !== null)
        <p class="exam-result-pass {{ $result->passed ? 'is-pass' : 'is-fail' }}">
          @if($result->passed)
            <i class="fa-solid fa-circle-check"></i> O‘tdi
          @else
            <i class="fa-solid fa-circle-xmark"></i> Yiqildi
          @endif
          <span style="display:block;font-size:13px;font-weight:600;opacity:0.9;margin-top:6px;">
            O‘tish uchun minimal ball: {{ $result->exam->passing_points ?? '—' }}
          </span>
        </p>
      @endif

      <div class="exam-result-score">
        @if($result->points_max !== null && $result->points_earned !== null)
          <div class="exam-result-score-num">{{ $result->points_earned }} / {{ $result->points_max }}</div>
          <p style="margin:8px 0 0;font-size:14px;color:var(--muted);">To‘plangan ball</p>
        @else
          <div class="exam-result-score-num">{{ $result->score }} / {{ $result->total_questions }}</div>
          <p style="margin:8px 0 0;font-size:14px;color:var(--muted);">To‘g‘ri javoblar (eski yozuv)</p>
        @endif
        <p class="exam-result-meta">
          To‘g‘ri javoblar (savollar): {{ $result->score }} / {{ $result->total_questions }}<br>
          Boshlangan: {{ $result->started_at?->format('d.m.Y H:i:s') }}<br>
          Yakunlangan: {{ $result->submitted_at?->format('d.m.Y H:i:s') }}
        </p>
      </div>

      <p class="exam-result-meta" style="text-align:center;margin-bottom:16px;">
        Natija serverda saqlanadi; maktab adminlari uni <strong>Admin panel → Imtihon natijalari</strong> bo‘limida ko‘radi.
      </p>

      <p class="exam-result-meta" style="text-align:center;margin-bottom:16px;">
        Siz uchun: <strong>Imtihonlar</strong> sahifasida topshirilgan imtihon yonidagi <strong>«Natijani ko‘rish»</strong>.
      </p>

      <div style="text-align:center;">
        <a href="{{ route('exam.index') }}" class="exam-btn-primary">
          Imtihonlar ro‘yxatiga
          <i class="fa-solid fa-list"></i>
        </a>
      </div>
    </div>
  </main>
</x-loyouts.main>
