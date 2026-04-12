{{--
  Yil bo‘yicha oylik panjaralar (ochiq taqvim va admin ko‘rinishi).
  $calendarMonths — CalendarYearGrid::build()
  $year
  $dayLinkPrefix — null: faqat #calendar-day-... ; to‘liq URL: sayt havolasi + hash (masalan route('calendar', ['y' => $year]))
--}}
@php
  $dayLinkPrefix = $dayLinkPrefix ?? null;
  $openPublicInNewTab = ! empty($openPublicInNewTab);
  $weekdayLabels = $weekdayLabels ?? __('public.calendar.weekdays_short');
  if (! is_array($weekdayLabels)) {
      $weekdayLabels = ['Du', 'Se', 'Ch', 'Pa', 'Ju', 'Sha', 'Ya'];
  }
@endphp
<div class="calendar-year-grid">
  @foreach($calendarMonths as $block)
    <div class="calendar-month-card">
      <h3 class="calendar-month-title">{{ $block['label'] }} {{ $year }}</h3>
      <div class="calendar-month-weekdays">
        @foreach($weekdayLabels as $wd)
          <span>{{ $wd }}</span>
        @endforeach
      </div>
      @foreach($block['weeks'] as $week)
        <div class="calendar-month-row">
          @foreach($week as $cell)
            @if($cell === null)
              <span class="calendar-cell calendar-cell--empty" aria-hidden="true"></span>
            @else
              @php
                $cls = 'calendar-cell';
                if (! empty($cell['hasEvents'])) {
                    $cls .= ' calendar-cell--events';
                }
                if (! empty($cell['isToday'])) {
                    $cls .= ' calendar-cell--today';
                }
                $hash = 'calendar-day-' . $cell['dateKey'];
                $href = $dayLinkPrefix !== null && $dayLinkPrefix !== ''
                  ? rtrim((string) $dayLinkPrefix, '#') . '#' . $hash
                  : '#' . $hash;
              @endphp
              <a
                href="{{ $href }}"
                class="{{ $cls }}"
                title="{{ $cell['dateKey'] }}"
                @if($openPublicInNewTab && $dayLinkPrefix)
                  target="_blank" rel="noopener noreferrer"
                @endif
              >
                {{ $cell['day'] }}
                @if(! empty($cell['hasEvents']))
                  <span class="calendar-cell-badge">{{ $cell['count'] }}</span>
                @endif
              </a>
            @endif
          @endforeach
        </div>
      @endforeach
    </div>
  @endforeach
</div>
