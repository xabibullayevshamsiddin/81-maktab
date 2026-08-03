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
  <label>Maksimal ishtirokchilar soni</label>
  <input type="number" min="0" max="100000" name="max_participants" value="{{ old('max_participants', $exam->max_participants ?? '') }}" placeholder="0 yoki bo'sh = cheksiz">
  <p class="text-sm mt-6" style="color:#64748b;">
    Belgilangan son topshirilgandan keyin imtihon boshqa ishtirokchilar uchun yopiladi. 0 yoki bo'sh qoldirsangiz — cheksiz.
  </p>
  @if ($errors->has('max_participants'))
    <p class="text-sm mt-6" style="color:#dc2626;">{{ $errors->first('max_participants') }}</p>
  @endif
</div>

@include('exams.partials.available-from-picker', [
  'exam' => $exam ?? null,
  'label' => 'Boshlash sanasi va vaqti (reja)',
  'hintTop' => 'Kalendar va <strong>soat · daqiqa</strong>. Vaqt <strong>O‘zbekiston (Toshkent, Asia/Tashkent)</strong> bo‘yicha.',
  'wrapperClass' => 'exam-available-from--admin',
])

<div class="input-style-1 exam-grade-field">
  <label>Ruxsat etilgan sinflar</label>
  <p class="text-sm mb-12 exam-grade-help">
    Faqat tanlangan sinflar topshira oladi. Hech narsa tanlanmasa, imtihon barcha sinflar uchun ochiq bo‘ladi.
  </p>
  @include('partials.school-grade-matrix', ['selected' => $selectedAllowedGrades])
  @if ($errors->has('allowed_grades') || $errors->has('allowed_grades.*'))
    <p class="text-sm mt-10" style="color:#dc2626;">{{ $errors->first('allowed_grades') ?: $errors->first('allowed_grades.*') }}</p>
  @endif
</div>

<div style="margin-top: 1.5rem;">
  <h6 style="display:flex;align-items:center;gap:8px; margin-bottom:8px; font-size:14px; font-weight:700; color:var(--text);">
    <i class="fa-solid fa-chalkboard-user" style="color: var(--primary);"></i>
    O'qituvchilar uchun ruxsat
  </h6>
  <p class="text-sm mb-12" style="color:#64748b;">
    Agar belgilansa, maktab o'qituvchilari (shu jumladan siz ham) bu imtihonni topshirish imkoniyatiga ega bo'ladilar.
  </p>
  <label class="d-flex align-items-center gap-3 cursor-pointer" style="user-select:none; padding: 1rem; border-radius: 10px; border: 1.5px solid var(--border); background: var(--surface); width: fit-content;">
    <input
      type="checkbox"
      name="allowed_grades[]"
      value="TEACHER"
      {{ in_array('TEACHER', $selectedAllowedGrades, true) ? 'checked' : '' }}
      style="width:1.3rem;height:1.3rem;cursor:pointer;accent-color:var(--primary);flex-shrink:0;"
    >
    <span>
      <strong style="color:var(--text);">O'qituvchilar ham imtihonni topshirishi mumkin</strong>
      <span class="d-block small" style="color:var(--muted);margin-top:2px;">
        O'qituvchi akkauntlari orqali imtihon boshlash va topshirishga ruxsat beriladi
      </span>
    </span>
  </label>
</div>

<div style="margin-top: 1.5rem;">
  <h6 style="display:flex;align-items:center;gap:8px; margin-bottom:8px; font-size:14px; font-weight:700; color:var(--text);">
    <i class="fa-solid fa-shield-halved" style="color: var(--primary);"></i>
    Xavfsizlik rejimi
  </h6>
  <p class="text-sm mb-12" style="color:#64748b;">
    Yoqilsa — o'quvchi imtihon boshlagan zahoti to'liq ekran (fullscreen) rejimi yonadi, sahifadan chiqsa yoki skrinshot olsa ogohlantirish keladi va qoidabuzarlik hisoblanadi. O'chirilsa — hech qanday cheklov bo'lmaydi, imtihon oddiy rejimda o'tadi.
  </p>
  <label class="d-flex align-items-center gap-3 cursor-pointer" style="user-select:none; padding: 1rem; border-radius: 10px; border: 1.5px solid var(--border); background: var(--surface); width: fit-content;">
    <input
      type="checkbox"
      name="security_enabled"
      value="1"
      id="exam-security-enabled"
      {{ old('security_enabled', isset($exam) ? ($exam->security_enabled ?? true) : true) ? 'checked' : '' }}
      style="width:1.3rem;height:1.3rem;cursor:pointer;accent-color:var(--primary);flex-shrink:0;"
    >
    <span>
      <strong style="color:var(--text);">Xavfsizlik himoyasini yoqish</strong>
      <span class="d-block small" style="color:var(--muted);margin-top:2px;">
        Fullscreen, tab o'zgartirish va skrinshot ogohlantirishlari faollashtiriladi
      </span>
    </span>
  </label>
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
    <br>
    Boshlash (reja): <strong>{{ $exam->availableFromLabel() ?? 'cheklov yo‘q' }}</strong>
  </p>
@else
  <p class="text-sm mb-20" style="color:#64748b;">
    1-bosqich: reja saqlangach avtomatik savollar sahifasiga o'tadi. Barcha savollar qo'shilguncha imtihon <strong>faol bo'lmaydi</strong>.
  </p>
@endif

<button type="submit" class="main-btn primary-btn btn-hover">Saqlash</button>
