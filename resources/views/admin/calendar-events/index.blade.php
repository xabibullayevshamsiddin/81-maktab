@extends('admin.layouts.main')

@section('title', 'Taqvim tadbirlari')

@section('content')
<section class="table-components">
  <div class="container-fluid">
    <div class="title-wrapper pt-30">
      <div class="row align-items-center">
        <div class="col-md-6">
          <div class="title"><h2>Taqvim</h2></div>
        </div>
        <div class="col-md-6 text-end">
          <a href="{{ route('calendar-events.create') }}" class="main-btn primary-btn btn-hover">Yangi tadbir</a>
        </div>
      </div>
    </div>

    @php
      $hasGridEvents = count($countsByDate ?? []) > 0;
      $publicCalendarUrl = route('calendar', ['y' => $year]);
    @endphp

    <div class="row">
      <div class="col-lg-12">
        <div class="card-style mb-30 calendar-page" style="padding: 24px 20px 28px;">
          <div class="calendar-toolbar" style="margin-bottom: 20px;">
            <form method="get" action="{{ route('calendar-events.index') }}" class="calendar-year-form">
              <label for="admin-cal-y">Yil (ko‘rinish)</label>
              <select id="admin-cal-y" name="y" onchange="this.form.submit()">
                @for($yy = (int) now()->year + 1; $yy >= 2020; $yy--)
                  <option value="{{ $yy }}" {{ (int) $year === $yy ? 'selected' : '' }}>{{ $yy }}</option>
                @endfor
              </select>
            </form>
            @if($hasGridEvents)
              <div class="calendar-legend" aria-hidden="true">
                <span class="calendar-legend-item">
                  <span class="cal-dot cal-dot--event"></span> Tadbir bor
                </span>
                <span class="calendar-legend-item">
                  <span class="cal-dot cal-dot--today"></span> Bugun
                </span>
              </div>
            @endif
          </div>

          @if($hasGridEvents)
            <div class="calendar-visual-head" style="margin-bottom: 14px;">
              <p class="calendar-visual-hint" style="margin:0;">
                Ommaviy saytdagi taqvim bilan bir xil ko‘rinish. Kunning ustiga bosing — ochiq <strong>Taqvim</strong> sahifasida shu kun ochiladi (yangi oynada).
              </p>
            </div>
            @include('partials.calendar-year-grid', [
              'calendarMonths' => $calendarMonths,
              'year' => $year,
              'dayLinkPrefix' => $publicCalendarUrl,
              'openPublicInNewTab' => true,
            ])
          @else
            <p class="text-muted mb-4" style="font-size: 14px;">{{ $year }} yil uchun hozircha tadbir yo‘q — pastdagi jadvalda boshqa yillar bo‘lishi mumkin.</p>
          @endif

          <hr class="my-4" style="opacity:0.35;" />

          <h6 class="mb-3" style="font-weight:700;">Barcha tadbirlar (ro‘yxat)</h6>

          @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
          @endif

          <div class="table-wrapper table-responsive">
            <table class="table">
              <thead>
                <tr>
                  <th>Sana</th>
                  <th>Sarlavha</th>
                  <th>Vaqt</th>
                  <th></th>
                </tr>
              </thead>
              <tbody>
                @forelse($events as $ev)
                  <tr>
                    <td>{{ $ev->event_date->format('d.m.Y') }}</td>
                    <td>{{ $ev->title }}</td>
                    <td>{{ $ev->time_note ?: '—' }}</td>
                    <td>
                      <a href="{{ route('calendar-events.edit', $ev) }}" class="btn btn-sm btn-warning">Tahrirlash</a>
                      <form action="{{ route('calendar-events.destroy', $ev) }}" method="POST" class="d-inline" data-confirm="O‘chirilsinmi?" data-confirm-title="Voqeani o'chirish" data-confirm-variant="danger" data-confirm-ok="O'chirish">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger">O‘chirish</button>
                      </form>
                    </td>
                  </tr>
                @empty
                  <tr><td colspan="4" class="text-center text-muted">Tadbir yo‘q.</td></tr>
                @endforelse
              </tbody>
            </table>
          </div>
          @if($events->hasPages())
            <div class="mt-3">{{ $events->links() }}</div>
          @endif
        </div>
      </div>
    </div>
  </div>
</section>
@endsection
