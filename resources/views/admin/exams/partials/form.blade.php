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
  <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(170px,1fr));gap:12px;">
    @foreach (school_grade_grouped_options() as $groupLabel => $options)
      <div style="border:1px solid #e2e8f0;border-radius:12px;padding:12px 14px;background:#fff;">
        <strong style="display:block;font-size:13px;color:#0f172a;">{{ $groupLabel }}</strong>
        <div style="display:flex;flex-wrap:wrap;gap:8px 12px;margin-top:10px;">
          @foreach ($options as $value => $label)
            <label style="display:inline-flex;align-items:center;gap:6px;font-size:13px;color:#334155;min-width:68px;">
              <input type="checkbox" name="allowed_grades[]" value="{{ $value }}" {{ in_array($value, $selectedAllowedGrades, true) ? 'checked' : '' }}>
              <span>{{ $label }}</span>
            </label>
          @endforeach
        </div>
      </div>
    @endforeach
  </div>
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
