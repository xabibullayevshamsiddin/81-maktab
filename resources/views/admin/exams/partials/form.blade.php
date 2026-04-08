@php
  $selectedAllowedGrades = normalize_school_grade_list(old('allowed_grades', isset($exam) ? $exam->allowedGradeItems() : []));
@endphp

<div class="input-style-1">
  <label>Nomi</label>
  <input type="text" name="title" value="{{ old('title', $exam->title ?? '') }}" required>
</div>

<div class="input-style-1">
  <label>Reja: jami savollar soni (2-bosqichda shuncha savol qo'shiladi)</label>
  <input type="number" min="1" max="500" name="required_questions" value="{{ old('required_questions', $exam->required_questions ?? 10) }}" required>
</div>

<div class="input-style-1">
  <label>Umumiy ball (maksimal - savollar ballari yig'indisi shu songa teng bo'lishi kerak)</label>
  <input type="number" min="1" max="10000" name="total_points" value="{{ old('total_points', $exam->total_points ?? 100) }}" required>
</div>

<div class="input-style-1">
  <label>O'tish uchun minimal ball</label>
  <input type="number" min="1" max="10000" name="passing_points" value="{{ old('passing_points', isset($exam) ? ($exam->passing_points ?? max(1, (int) floor($exam->total_points / 2))) : max(1, (int) floor((int) old('total_points', 100) / 2))) }}" required>
</div>

<div class="input-style-1">
  <label>Davomiyligi (daqiqa)</label>
  <input type="number" min="1" name="duration_minutes" value="{{ old('duration_minutes', $exam->duration_minutes ?? 60) }}" required>
</div>

<div class="input-style-1">
  <label>Ruxsat etilgan sinflar</label>
  <p class="text-sm mb-10" style="color:#64748b;">
    Faqat tanlangan sinflar topshira oladi. Hech narsa tanlanmasa, imtihon barcha sinflar uchun ochiq bo'ladi.
  </p>
  <div class="grade-picker-grid">
    @foreach (school_grade_grouped_options() as $groupLabel => $options)
      <div class="grade-picker-group">
        <button type="button" class="grade-picker-group-btn" data-grade-group-toggle>
          <span>{{ $groupLabel }}</span>
          <i class="fa-solid fa-check-double" style="font-size:11px;opacity:0.5;"></i>
        </button>
        <div class="grade-picker-options">
          @foreach ($options as $value => $label)
            <label class="grade-picker-label">
              <input type="checkbox" name="allowed_grades[]" value="{{ $value }}" {{ in_array($value, $selectedAllowedGrades, true) ? 'checked' : '' }}>
              <span>{{ $label }}</span>
            </label>
          @endforeach
        </div>
      </div>
    @endforeach
  </div>
  <script>
    document.querySelectorAll('[data-grade-group-toggle]').forEach(function(btn) {
      btn.addEventListener('click', function() {
        var group = btn.closest('.grade-picker-group');
        if (!group) return;
        var boxes = group.querySelectorAll('input[type=checkbox]');
        var allChecked = Array.prototype.every.call(boxes, function(b) { return b.checked; });
        boxes.forEach(function(b) { b.checked = !allChecked; });
      });
    });
  </script>
  @if ($errors->has('allowed_grades') || $errors->has('allowed_grades.*'))
    <p class="text-sm mt-10" style="color:#dc2626;">{{ $errors->first('allowed_grades') ?: $errors->first('allowed_grades.*') }}</p>
  @endif
</div>

@if(isset($exam))
  <p class="text-sm mb-20" style="color:#64748b;">
    Savollar: <strong>{{ $exam->questions_count }}</strong> / {{ $exam->required_questions }} -
    @if($exam->is_active)
      <span style="color:#16a34a;font-weight:600;">Faol (foydalanuvchilar ko'ra oladi)</span>
    @else
      <span style="color:#ca8a04;font-weight:600;">Tayyorlanmoqda (savollar to'liq emas)</span>
    @endif
    <br>
    Ruxsat etilgan sinflar: <strong>{{ $exam->allowedGradesLabel() }}</strong>
  </p>
@else
  <p class="text-sm mb-20" style="color:#64748b;">
    1-bosqich: reja saqlangach avtomatik savollar sahifasiga o'tadi. Barcha savollar qo'shilguncha imtihon <strong>faol bo'lmaydi</strong>.
  </p>
@endif

<button type="submit" class="main-btn primary-btn btn-hover">Saqlash</button>
