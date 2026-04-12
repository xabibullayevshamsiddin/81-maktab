/**
 * Admin: ustoz profiliga biriktirish — teacher ro‘lidagi akkauntlar selectida qidiruv.
 */
(function () {
  function norm(s) {
    return (s || '').toLowerCase().trim();
  }

  function initWrap(wrap) {
    var search = wrap.querySelector('.js-teacher-user-search');
    var select = wrap.querySelector('.js-teacher-user-select');
    var countEl = wrap.querySelector('.js-teacher-user-count');
    if (!search || !select || !countEl) {
      return;
    }

    function optionCount() {
      return select.querySelectorAll('option[value]:not([value=""])').length;
    }

    function applyFilter() {
      var q = norm(search.value);
      var options = select.querySelectorAll('option');
      var total = 0;
      var matched = 0;
      options.forEach(function (opt) {
        if (opt.value === '') {
          opt.hidden = false;
          return;
        }
        total++;
        var text = norm(opt.textContent);
        var match = q === '' || text.indexOf(q) !== -1;
        var isSelected = String(select.value) === String(opt.value);
        opt.hidden = !match && !isSelected;
        if (match) {
          matched++;
        }
      });

      if (q === '') {
        countEl.textContent =
          'Jami ' +
          total +
          ' ta «O‘qituvchi» ro‘lidagi akkaunt (boshqalarga biriktirilmagan). Qidiruv maydoniga yozing — ism, email yoki telefon bo‘yicha toraytiradi.';
      } else {
        countEl.textContent =
          matched +
          ' ta mos keldi. Tanlangan akkaunt qidiruvga mos kelmasa ham ro‘yxatda qoladi.';
      }
    }

    search.addEventListener('input', applyFilter);
    search.addEventListener('search', function () {
      if (search.value === '') {
        applyFilter();
      }
    });
    select.addEventListener('change', applyFilter);

    if (optionCount() === 0) {
      countEl.textContent =
        'Hozircha biriktirish uchun teacher akkaunt yo‘q (yoki barchasi boshqa ustozlarga bog‘langan).';
      search.disabled = true;
      search.placeholder = 'Akkaunt yo‘q';
    } else {
      applyFilter();
    }
  }

  function run() {
    document.querySelectorAll('.js-teacher-user-link-field').forEach(initWrap);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', run);
  } else {
    run();
  }
})();
