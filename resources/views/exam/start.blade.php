<x-loyouts.main title="Imtihon - {{ $exam->title }}">
  <main class="news exam-page exam-start-page">
    <div class="exam-page-inner" style="max-width: 560px;">
      <header class="exam-hero">
        <span class="exam-hero-badge">
          <i class="fa-solid fa-play"></i>
          Tayyorlik
        </span>
        <h1 class="exam-title">{{ $exam->title }}</h1>
        <p class="exam-hero-lead">
          Davomiylik: <strong>{{ $exam->duration_minutes }}</strong> daqiqa ·
          Jami ball: <strong>{{ $exam->total_points ?? '-' }}</strong> ·
          Savollar: <strong>{{ $exam->required_questions ?? '-' }}</strong>
        </p>
        <p class="exam-hero-lead exam-hero-hint" style="font-size:0.95rem;margin-top:8px;">
          <i class="fa-solid fa-users"></i>
          Ruxsat etilgan sinflar: <strong>{{ $exam->allowedGradesLabel() }}</strong>
        </p>
      </header>

      <article class="exam-card" style="text-align:center;">
        @if($existing && ($existing->status === 'submitted' || $existing->status === 'expired'))
          <p style="margin:0 0 16px;color:var(--muted);">Siz bu imtihonni allaqachon topshirgansiz.</p>
          <a href="{{ route('exam.result.show', $existing) }}" class="exam-btn-primary">
            Natijani ko'rish
            <i class="fa-solid fa-chart-simple"></i>
          </a>
        @elseif($existing)
          <p style="margin:0 0 16px;color:var(--muted);">Imtihon boshlangan - davom ettiring.</p>
          <a href="{{ route('exam.session', $existing) }}" class="exam-btn-primary">
            Davom ettirish
            <i class="fa-solid fa-arrow-right"></i>
          </a>
        @elseif(!($canStartNow ?? true))
          <p style="margin:0 0 12px;font-size:15px;color:var(--text);line-height:1.5;">
            <i class="fa-regular fa-calendar-days" style="margin-right:6px;opacity:0.85;"></i>
            Bu imtihon reja bo‘yicha <strong>{{ $exam->availableFromLabel() }}</strong> dan boshlash mumkin.
          </p>
          <p style="margin:0;font-size:14px;color:var(--muted);">Hozircha taymer ishga tushmaydi — sanasi kelgach «Boshlash» tugmasi ochiladi.</p>
        @else
          <form action="{{ route('exam.start', $exam) }}" method="POST">
            @csrf
            <button type="submit" class="exam-btn-primary" style="width:100%;justify-content:center;">
              Imtihonni boshlash
              <i class="fa-solid fa-bolt"></i>
            </button>
          </form>
          <p style="margin:16px 0 0;font-size:12px;color:var(--muted);line-height:1.5;">
            Boshlasangiz, taymer ishga tushadi. Sahifani yangilash tartibni o'zgartirmaydi.
          </p>
        @endif

        <div style="margin-top:22px;">
          <a href="{{ route('exam.index') }}" class="exam-btn-secondary">&larr; Imtihonlar ro'yxati</a>
        </div>
      </article>
    </div>
  </main>
</x-loyouts.main>
