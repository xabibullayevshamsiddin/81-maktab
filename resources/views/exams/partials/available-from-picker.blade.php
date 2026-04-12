@php
  $availableFromValue = old('available_from', isset($exam) && $exam->available_from ? $exam->available_from->format('Y-m-d H:i') : '');
  $wdShort = __('public.calendar.weekdays_short');
  $fpWeekdaysShorthand = [
    $wdShort[6] ?? 'Ya',
    $wdShort[0] ?? 'Du',
    $wdShort[1] ?? 'Se',
    $wdShort[2] ?? 'Ch',
    $wdShort[3] ?? 'Pa',
    $wdShort[4] ?? 'Ju',
    $wdShort[5] ?? 'Sha',
  ];
  $fpMonthsLong = array_values(__('public.calendar.month_names'));
  $flatpickrLocale = [
    'firstDayOfWeek' => 1,
    'weekdays' => [
      'shorthand' => $fpWeekdaysShorthand,
      'longhand' => array_values(__('public.calendar.weekdays_long')),
    ],
    'months' => [
      'longhand' => $fpMonthsLong,
      'shorthand' => array_map(static fn ($m) => mb_substr($m, 0, 3), $fpMonthsLong),
    ],
  ];
@endphp

<div class="exam-field exam-field--date exam-field--schedule-calendar {{ $wrapperClass ?? '' }}">
  <label for="exam-available-from">{{ $label ?? 'Sana va vaqt' }}</label>
  <p class="exam-form-hint exam-form-hint--schedule-calendar">
    @isset($hintTop)
      {!! $hintTop !!}
    @else
      Kalendar va pastdagi <strong>soat · daqiqa</strong> bilan tanlang. Vaqt <strong>O‘zbekiston (Toshkent)</strong> bo‘yicha saqlanadi (<code class="exam-tz-code">Asia/Tashkent</code>).
    @endisset
  </p>
  <div
    class="exam-inline-calendar-wrap"
    id="exam-available-from-wrap"
    data-default="{{ $availableFromValue }}"
    data-locale='@json($flatpickrLocale)'
  >
    <div class="exam-calendar-actions">
      <button type="button" class="btn btn-sm btn-outline exam-clear-date" id="exam-clear-available-from">
        Sanani va vaqtni olib tashlash
      </button>
    </div>
    <input
      id="exam-available-from"
      type="text"
      name="available_from"
      value="{{ $availableFromValue }}"
      autocomplete="off"
      readonly
      placeholder="Sana va vaqtni tanlang"
    >
  </div>
  <p class="exam-form-hint exam-form-hint--compact">
    <strong>Ixtiyoriy.</strong> «Olib tashlash» yoki boshlang‘ichda tanlanmasa — imtihon faol bo‘lishi bilan darhol boshlash mumkin.
  </p>
  @error('available_from')
    <p class="exam-form-error">{{ $message }}</p>
  @enderror
</div>
