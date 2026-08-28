@php
  $selectedAllowedGrades = normalize_school_grade_list(old('allowed_grades', isset($exam) ? $exam->allowedGradeItems() : []));
@endphp

<div class="exam-form-section">
  <h6 class="exam-form-section-title">Asosiy reja</h6>
  <div class="exam-field">
    <label for="exam-title">Nomi</label>
    <input id="exam-title" type="text" name="title" value="{{ old('title', $exam->title ?? '') }}" required>
  </div>

  <div class="exam-field">
    <label for="exam-required-q">Reja: jami savollar soni (2-bosqichda shuncha savol qo'shiladi)</label>
    <input id="exam-required-q" type="number" min="1" max="500" name="required_questions" value="{{ old('required_questions', $exam->required_questions ?? 10) }}" required>
  </div>

  <div class="exam-field">
    <label for="exam-total-pts">Umumiy ball (maksimal - savollar ballari yig'indisi shu songa teng bo'lishi kerak)</label>
    <input id="exam-total-pts" type="number" min="1" max="10000" name="total_points" value="{{ old('total_points', $exam->total_points ?? 100) }}" required>
  </div>

  <div class="exam-field">
    <label for="exam-pass-pts">O'tish uchun minimal ball</label>
    <input id="exam-pass-pts" type="number" min="1" max="10000" name="passing_points" value="{{ old('passing_points', isset($exam) ? ($exam->passing_points ?? max(1, (int) floor($exam->total_points / 2))) : max(1, (int) floor((int) old('total_points', 100) / 2))) }}" required>
  </div>

  <div class="exam-field">
    <label for="exam-duration">Davomiyligi (daqiqa)</label>
    <input id="exam-duration" type="number" min="1" name="duration_minutes" value="{{ old('duration_minutes', $exam->duration_minutes ?? 60) }}" required>
  </div>

  <div class="exam-field">
    <label for="exam-max-participants">Maksimal ishtirokchilar soni</label>
    <input id="exam-max-participants" type="number" min="0" max="100000" name="max_participants" value="{{ old('max_participants', $exam->max_participants ?? '') }}" placeholder="0 yoki bo'sh = cheksiz">
    <p class="exam-form-hint" style="margin-top:4px; margin-bottom:0; font-size:12px; color:var(--muted);">
      Belgilangan son topshirilgandan keyin imtihon boshqa ishtirokchilar uchun yopiladi. 0 yoki bo'sh qoldirsangiz — cheksiz.
    </p>
    @if ($errors->has('max_participants'))
      <p class="exam-form-error">{{ $errors->first('max_participants') }}</p>
    @endif
  </div>
</div>

<div class="exam-form-section exam-form-section--schedule">
  <div class="exam-schedule-head">
    <span class="exam-schedule-icon" aria-hidden="true"><i class="fa-regular fa-calendar-days"></i></span>
    <div>
      <h6 class="exam-form-section-title exam-form-section-title--schedule">Boshlash sanasi va vaqti (reja)</h6>
      <p class="exam-form-hint">Qachon va qaysi vaqtdan boshlab o'quvchilar imtihonni boshlashi mumkinligi. Tanlangan vaqtdan boshlab ruxsat beriladi.</p>
    </div>
  </div>
  @include('exams.partials.available-from-picker', ['exam' => $exam ?? null])
</div>

<div class="exam-form-section exam-grade-field">
  <h6 class="exam-form-section-title">Ruxsat etilgan sinflar</h6>
  <p class="exam-form-hint exam-grade-help">
    Faqat tanlangan sinflar topshira oladi. Hech narsa tanlanmasa, imtihon barcha sinflar uchun ochiq bo'ladi.
  </p>
  @include('partials.school-grade-matrix', ['selected' => $selectedAllowedGrades])
  @if ($errors->has('allowed_grades') || $errors->has('allowed_grades.*'))
    <p class="exam-form-error">{{ $errors->first('allowed_grades') ?: $errors->first('allowed_grades.*') }}</p>
  @endif
</div>

<div class="exam-form-section" style="margin-top: 1.5rem;">
  <h6 class="exam-form-section-title" style="display:flex;align-items:center;gap:8px;">
    <i class="fa-solid fa-chalkboard-user" style="color: var(--primary);"></i>
    O'qituvchilar uchun ruxsat
  </h6>
  <p class="exam-form-hint" style="margin-bottom:1rem;">
    Agar belgilansa, maktab o'qituvchilari (shu jumladan siz ham) bu imtihonni topshirish imkoniyatiga ega bo'ladilar.
  </p>
  <label class="cursor-pointer" style="display:flex;align-items:flex-start;gap:12px;user-select:none;padding:1rem;border-radius:10px;border:1.5px solid var(--border);background:var(--surface);width:100%;box-sizing:border-box;">
    <input
      type="checkbox"
      name="allowed_grades[]"
      value="TEACHER"
      {{ in_array('TEACHER', $selectedAllowedGrades, true) ? 'checked' : '' }}
      style="width:1.3rem;height:1.3rem;cursor:pointer;accent-color:var(--primary);flex-shrink:0;margin-top:2px;"
    >
    <span style="flex:1;min-width:0;">
      <strong style="color:var(--text);display:block;margin-bottom:4px;">O'qituvchilar ham imtihonni topshirishi mumkin</strong>
      <span style="color:var(--muted);font-size:0.85rem;line-height:1.5;display:block;">
        O'qituvchi akkauntlari orqali imtihon boshlash va topshirishga ruxsat beriladi
      </span>
    </span>
  </label>
</div>

<div class="exam-form-section" style="margin-top: 1.5rem;">
  <h6 class="exam-form-section-title" style="display:flex;align-items:center;gap:8px;">
    <i class="fa-solid fa-shield-halved" style="color: var(--primary);"></i>
    Xavfsizlik rejimi
  </h6>
  <p class="exam-form-hint" style="margin-bottom:1rem;">
    Yoqilsa — o'quvchi imtihon boshlagan zahoti to'liq ekran (fullscreen) rejimi yonadi, sahifadan chiqsa yoki skrinshot olsa ogohlantirish keladi va qoidabuzarlik hisoblanadi. O'chirilsa — hech qanday cheklov bo'lmaydi, imtihon oddiy rejimda o'tadi.
  </p>
  <label class="cursor-pointer" style="display:flex;align-items:flex-start;gap:12px;user-select:none;padding:1rem;border-radius:10px;border:1.5px solid var(--border);background:var(--surface);width:100%;box-sizing:border-box;">
    <input
      type="checkbox"
      name="security_enabled"
      value="1"
      id="exam-security-enabled"
      {{ old('security_enabled', isset($exam) ? ($exam->security_enabled ?? true) : true) ? 'checked' : '' }}
      style="width:1.3rem;height:1.3rem;cursor:pointer;accent-color:var(--primary);flex-shrink:0;margin-top:2px;"
    >
    <span style="flex:1;min-width:0;">
      <strong style="color:var(--text);display:block;margin-bottom:4px;">Xavfsizlik himoyasini yoqish</strong>
      <span style="color:var(--muted);font-size:0.85rem;line-height:1.5;display:block;">
        Fullscreen, tab o'zgartirish va skrinshot ogohlantirishlari faollashtiriladi
      </span>
    </span>
  </label>
</div>


@if(isset($exam))
  <p class="exam-form-hint mb-20">
    Savollar: <strong>{{ $exam->questions_count }}</strong> / {{ $exam->required_questions }} -
    @if($exam->is_active)
      <span style="color:#16a34a;font-weight:600;">Faol (foydalanuvchilar ko'ra oladi)</span>
    @else
      <span style="color:#ca8a04;font-weight:600;">Tayyorlanmoqda (savollar to'liq emas)</span>
    @endif
    <br>
    Ruxsat etilgan sinflar: <strong>{{ $exam->allowedGradesLabel() }}</strong>
    <br>
    Boshlash (reja): <strong>{{ $exam->availableFromLabel() ?? 'cheklov yo\'q' }}</strong>
    @if($exam->hasParticipantLimit())
      <br>
      Ishtirokchilar: <strong>{{ $exam->participantLimitLabel() }}</strong>
      @if($exam->isParticipantLimitReached())
        <span style="color:#dc2626;font-weight:600;">(limit to'ldi!)</span>
      @endif
    @endif
  </p>
@else
  <p class="exam-form-hint mb-20">
    1-bosqich: reja saqlangach avtomatik savollar sahifasiga o'tadi. Barcha savollar qo'shilguncha imtihon <strong>faol bo'lmaydi</strong>.
  </p>
@endif

<button type="submit" class="btn btn-primary">Saqlash</button>
