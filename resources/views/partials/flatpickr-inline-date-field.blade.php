@php
  $fieldName = $name;
  $fieldId = $id ?? $name;
  $fieldValue = $value ?? '';
  $isRequired = (bool) ($required ?? false);
  $autoSubmit = $autoSubmit ?? false;
  $optional = $optional ?? false;
  $wrapperClass = $wrapperClass ?? '';

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

@pushOnce('page_styles', 'flatpickr-inline-date-lib')
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.css" crossorigin="anonymous">
  <link rel="stylesheet" href="{{ app_public_asset('temp/css/flatpickr-inline-date.css') }}?v={{ filemtime(public_path('temp/css/flatpickr-inline-date.css')) }}">
@endpushOnce
@pushOnce('admin_styles', 'flatpickr-inline-date-lib-admin')
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.css" crossorigin="anonymous">
  <link rel="stylesheet" href="{{ app_public_asset('temp/css/flatpickr-inline-date.css') }}?v={{ filemtime(public_path('temp/css/flatpickr-inline-date.css')) }}">
@endpushOnce
@pushOnce('page_scripts', 'flatpickr-inline-date-lib-js')
  <script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.js" crossorigin="anonymous"></script>
  <script src="{{ app_public_asset('temp/js/flatpickr-inline-date.js') }}?v={{ filemtime(public_path('temp/js/flatpickr-inline-date.js')) }}"></script>
@endpushOnce
@pushOnce('admin_page_scripts', 'flatpickr-inline-date-lib-js-admin')
  <script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.js" crossorigin="anonymous"></script>
  <script src="{{ app_public_asset('temp/js/flatpickr-inline-date.js') }}?v={{ filemtime(public_path('temp/js/flatpickr-inline-date.js')) }}"></script>
@endpushOnce

@if(isset($label) && $label !== '')
  <label class="{{ $labelClass ?? 'form-label' }}" for="{{ $fieldId }}">{{ $label }}</label>
@endif

@if($optional)
  <div class="fp-inline-date-toolbar">
    <button type="button" class="fp-inline-clear-btn js-fp-inline-clear" data-fp-for="{{ $fieldId }}">Tozalash</button>
  </div>
@endif

<div
  class="exam-inline-calendar-wrap fp-inline-date-only {{ $wrapperClass }}"
  data-locale='@json($flatpickrLocale)'
>
  <input
    type="text"
    name="{{ $fieldName }}"
    id="{{ $fieldId }}"
    value="{{ $fieldValue }}"
    class="js-fp-inline-date {{ $inputClass ?? '' }}"
    autocomplete="off"
    @if($isRequired) required @endif
    @if($autoSubmit) data-fp-auto-submit @endif
  >
</div>
