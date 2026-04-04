<x-loyouts.main title="81-IDUM | Taqvim">
  <section class="news-hero profile-hero">
    <div class="container">
      <div class="news-hero-content reveal">
        <span class="badge">Maktab taqvimi</span>
        <h1><strong>Tadbirlar va muhim sanalar</strong></h1>
        <p>Yilni tanlang — sanalar bo‘yicha tartiblangan ro‘yxat.</p>
      </div>
    </div>
  </section>

  <main class="profile-main calendar-page">
    <div class="container">
      <form method="get" action="{{ route('calendar') }}" class="calendar-year-form">
        <label for="cal-y" class="profile-muted">Yil</label>
        <select id="cal-y" name="y" class="comment-input" style="max-width:120px;" onchange="this.form.submit()">
          @for($y = (int) now()->year + 1; $y >= 2020; $y--)
            <option value="{{ $y }}" {{ (int) $year === $y ? 'selected' : '' }}>{{ $y }}</option>
          @endfor
        </select>
      </form>

      @if($events->isEmpty())
        <p class="profile-muted">{{ $year }} yil uchun tadbirlar hali kiritilmagan.</p>
      @else
        <div class="calendar-event-list">
          @foreach($grouped as $dateStr => $dayEvents)
            @php $d = \Carbon\Carbon::parse($dateStr); @endphp
            <details class="profile-activity-block calendar-day-block calendar-day-dtls">
              <summary class="calendar-day-summary">
                <span class="calendar-day-summary-inner">
                  <i class="fa-regular fa-calendar"></i>
                  {{ $d->format('d.m.Y') }}
                  <span class="calendar-day-count">{{ $dayEvents->count() }} ta</span>
                </span>
                <i class="fa-solid fa-chevron-down calendar-day-chevron" aria-hidden="true"></i>
              </summary>
              <ul class="profile-activity-list calendar-day-activity-list" style="margin:0;">
                @foreach($dayEvents as $ev)
                  <li style="border:none;padding:0;margin:0 0 14px;">
                    <p class="profile-activity-title">{{ $ev->title }}</p>
                    @if($ev->time_note)
                      <span class="profile-muted" style="font-size:13px;"><i class="fa-regular fa-clock"></i> {{ $ev->time_note }}</span>
                    @endif
                    @if($ev->body)
                      <p class="profile-activity-body" style="margin-top:8px;">{{ $ev->body }}</p>
                    @endif
                  </li>
                @endforeach
              </ul>
            </details>
          @endforeach
        </div>
        <script>
          (function () {
            var list = document.querySelector('.calendar-event-list');
            if (!list) return;
            var days = list.querySelectorAll('details.calendar-day-dtls');
            if (!days.length) return;
            var wide = window.matchMedia('(min-width: 769px)');
            function thresholdY() {
              return window.innerHeight * 0.11;
            }
            function setAllOpen(open) {
              days.forEach(function (d) {
                d.open = open;
              });
            }
            function sync() {
              if (wide.matches) {
                setAllOpen(true);
              } else {
                setAllOpen(window.scrollY >= thresholdY());
              }
            }
            function onScroll() {
              if (!wide.matches && window.scrollY >= thresholdY()) {
                setAllOpen(true);
              }
            }
            wide.addEventListener('change', sync);
            window.addEventListener('scroll', onScroll, { passive: true });
            sync();
            onScroll();
          })();
        </script>
      @endif
    </div>
  </main>
</x-loyouts.main>
