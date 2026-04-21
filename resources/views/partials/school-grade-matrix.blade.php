@php
  $selected = normalize_school_grade_list($selected ?? []);
  $sections = school_grade_sections();
@endphp

<div class="grade-matrix-card" data-grade-matrix>
  <div class="grade-matrix-toolbar">
    <p class="grade-matrix-hint">
      <i class="fa-solid fa-circle-info" aria-hidden="true"></i>
      <span><strong>Qator</strong> — shu sinfning barcha guruhlari; <strong>A–F</strong> ustuni — shu harf bo‘yicha barcha sinflar.</span>
    </p>
    <div class="grade-matrix-actions">
      <button type="button" class="grade-matrix-btn grade-matrix-btn--primary" data-grade-action="all">
        Barchasini tanlash
      </button>
      <button type="button" class="grade-matrix-btn" data-grade-action="none">
        Tozalash
      </button>
    </div>
  </div>
  <div class="grade-matrix-scroll">
    <table class="grade-matrix" role="grid" aria-label="Ruxsat etilgan sinflar">
      <thead>
        <tr>
          <th class="grade-matrix-corner" scope="col">
            <span class="grade-matrix-corner-inner">Sinf</span>
          </th>
          @foreach ($sections as $colIndex => $sec)
            <th scope="col">
              <button type="button" class="grade-matrix-col-head" data-grade-col-toggle="{{ (int) $colIndex }}" title="{{ $sec }} harfi bo‘yicha barcha sinflar">
                {{ $sec }}
              </button>
            </th>
          @endforeach
        </tr>
      </thead>
      <tbody>
        @php $map = school_grade_map(); @endphp
        @foreach (range(1, 11) as $g)
          <tr>
            <th scope="row">
              <button type="button" class="grade-matrix-row-head" data-grade-row-toggle title="{{ $g }}-sinf — barcha guruhlar">
                {{ $g }}
              </button>
            </th>
            @foreach ($sections as $sec)
              @php
                $value = $g.'-'.$sec;
                $isValid = in_array($sec, $map[$g] ?? [], true);
              @endphp
              <td>
                @if ($isValid)
                  <label class="grade-matrix-cell">
                    <input
                      type="checkbox"
                      name="allowed_grades[]"
                      value="{{ $value }}"
                      {{ in_array($value, $selected, true) ? 'checked' : '' }}
                    >
                    <span class="grade-matrix-sr-only">{{ $value }}</span>
                  </label>
                @endif
              </td>
            @endforeach
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>

<script>
(function () {
  document.querySelectorAll('[data-grade-matrix]').forEach(function (root) {
    root.querySelectorAll('[data-grade-row-toggle]').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var row = btn.closest('tr');
        if (!row) return;
        var boxes = row.querySelectorAll('input[type="checkbox"]');
        var allOn = Array.prototype.every.call(boxes, function (b) { return b.checked; });
        boxes.forEach(function (b) { b.checked = !allOn; });
      });
    });

    root.querySelectorAll('[data-grade-col-toggle]').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var idx = parseInt(btn.getAttribute('data-grade-col-toggle'), 10);
        if (isNaN(idx)) return;
        var table = root.querySelector('.grade-matrix');
        if (!table) return;
        var rows = table.querySelectorAll('tbody tr');
        var allOn = true;
        rows.forEach(function (row) {
          var tds = row.querySelectorAll('td');
          var td = tds[idx];
          if (!td) return;
          var inp = td.querySelector('input[type="checkbox"]');
          if (inp && !inp.checked) allOn = false;
        });
        var newVal = !allOn;
        rows.forEach(function (row) {
          var tds = row.querySelectorAll('td');
          var td = tds[idx];
          if (!td) return;
          var inp = td.querySelector('input[type="checkbox"]');
          if (inp) inp.checked = newVal;
        });
      });
    });

    var allBtn = root.querySelector('[data-grade-action="all"]');
    var noneBtn = root.querySelector('[data-grade-action="none"]');
    if (allBtn) {
      allBtn.addEventListener('click', function () {
        root.querySelectorAll('input[type="checkbox"]').forEach(function (b) { b.checked = true; });
      });
    }
    if (noneBtn) {
      noneBtn.addEventListener('click', function () {
        root.querySelectorAll('input[type="checkbox"]').forEach(function (b) { b.checked = false; });
      });
    }
  });
})();
</script>
