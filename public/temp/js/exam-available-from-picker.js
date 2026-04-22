/**
 * Profile/admin exam form: inline Flatpickr for available_from (sana + soat:daqiqa, Toshkent vaqti — serverda APP_TIMEZONE).
 */
(function () {
  var input = document.getElementById('exam-available-from');
  if (!input || typeof flatpickr === 'undefined') {
    return;
  }

  var wrap = input.closest('.exam-inline-calendar-wrap');
  var defaultVal = (wrap && wrap.getAttribute('data-default')) || input.value || '';
  var locale = {};
  if (wrap && wrap.getAttribute('data-locale')) {
    try {
      locale = JSON.parse(wrap.getAttribute('data-locale'));
    } catch (e) {
      locale = {};
    }
  }

  var localeOpts = Object.keys(locale).length
    ? locale
    : { firstDayOfWeek: 1 };

  var fp = flatpickr(input, {
    inline: true,
    enableTime: true,
    enableSeconds: false,
    time_24hr: true,
    minuteIncrement: 1,
    dateFormat: 'Y-m-d H:i',
    altInput: true,
    altFormat: 'd.m.Y, H:i',
    altInputClass: 'exam-fp-alt-input',
    allowInput: false,
    disableMobile: true,
    locale: localeOpts,
    defaultDate: defaultVal || null,
    defaultHour: 9,
    defaultMinute: 0,
    onReady: function (_selectedDates, _dateStr, instance) {
      if (instance.altInput) {
        instance.altInput.setAttribute('placeholder', 'Sana va vaqtni tanlang');
        instance.altInput.setAttribute('readonly', 'readonly');
      }
    },
  });

  var clearBtn = document.getElementById('exam-clear-available-from');
  if (clearBtn) {
    clearBtn.addEventListener('click', function () {
      fp.clear();
      input.value = '';
    });
  }
})();
