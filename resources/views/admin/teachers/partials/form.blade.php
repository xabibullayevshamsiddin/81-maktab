@pushOnce('admin_styles', 'admin-teacher-user-select-css')
  <link rel="stylesheet" href="{{ app_public_asset('temp/css/admin-teacher-user-select.css') }}?v={{ filemtime(public_path('temp/css/admin-teacher-user-select.css')) }}">
@endpushOnce
@pushOnce('admin_page_scripts', 'admin-teacher-user-select-js')
  <script src="{{ app_public_asset('temp/js/admin-teacher-user-select.js') }}?v={{ filemtime(public_path('temp/js/admin-teacher-user-select.js')) }}"></script>
@endpushOnce

<div class="input-style-1 teacher-user-link-field js-teacher-user-link-field">
  <label for="teacher-user-search">Foydalanuvchi (teacher akkaunt) <span class="text-muted" style="font-weight:400;">— ixtiyoriy</span></label>
  <input
    type="search"
    id="teacher-user-search"
    class="js-teacher-user-search"
    placeholder="Qidirish: ism, email yoki telefon…"
    autocomplete="off"
    aria-label="Teacher akkauntni qidirish"
  >
  <p class="js-teacher-user-count" aria-live="polite"></p>
  <select
    name="user_id"
    id="teacher-user-select"
    class="js-teacher-user-select"
    aria-label="Teacher akkaunt (ixtiyoriy)"
  >
    <option value="">— Tanlanmagan (faqat saytda kartochka) —</option>
    @foreach(($teacherUsers ?? collect()) as $u)
      <option value="{{ $u->id }}" {{ (string) old('user_id', $teacher?->user_id) === (string) $u->id ? 'selected' : '' }}>
        {{ $u->name }} — {{ $u->email }}@if(filled($u->phone)) · {{ $u->phone }}@endif
      </option>
    @endforeach
  </select>
  @error('user_id')
    <p class="text-danger small mt-2 mb-0">{{ $message }}</p>
  @enderror
  <small style="color:#64748b;display:block;margin-top:10px;max-width:640px;line-height:1.5;">
    <strong>Qanday biriktirish:</strong> avval <a href="{{ route('user') }}">Foydalanuvchilar</a> bo‘limida akkauntga <strong>«O‘qituvchi»</strong> rolini bering. Keyin shu yerda qidiruv maydoniga ism, email yoki telefon yozib, to‘g‘ri akkauntni tanlang — u kabinetdan kurs ochishi va shu ustoz kartochkasiga bog‘lanadi.
    Biriktirish shart emas: agar ustoz faqat saytda kartochka sifatida ko‘rinsa, «Tanlanmagan» qoldiring.
  </small>
</div>

<div class="table-responsive teacher-admin-main-grid mb-25">
  <table class="table table-bordered align-middle">
    <thead>
      <tr>
        <th><h6 class="mb-0">F.I.Sh</h6></th>
        <th><h6 class="mb-0">Lavozim</h6></th>
        <th style="min-width:110px;"><h6 class="mb-0">Staj (yil)</h6></th>
        <th><h6 class="mb-0">Toifa</h6></th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td class="min-width">
          <div class="input-style-1 mb-0">
            <input type="text" name="full_name" value="{{ old('full_name', $teacher?->full_name) }}" required aria-label="F.I.Sh" placeholder="Familiya Ism Sharif">
          </div>
        </td>
        <td>
          <div class="input-style-1 mb-0">
            <input type="text" name="lavozim" value="{{ old('lavozim', $teacher?->lavozim) }}" aria-label="Lavozim" placeholder="Masalan: O'qituvchi">
          </div>
        </td>
        <td>
          <div class="input-style-1 mb-0">
            <input type="number" min="0" max="60" name="experience_years" value="{{ old('experience_years', $teacher?->experience_years ?? 0) }}" required aria-label="Staj, yil">
          </div>
        </td>
        <td>
          <div class="input-style-1 mb-0">
            <input type="text" name="toifa" value="{{ old('toifa', $teacher?->toifa) }}" aria-label="Toifa" placeholder="Masalan: Oliy toifa">
          </div>
        </td>
      </tr>
    </tbody>
  </table>
</div>

<div class="row">
  <div class="col-md-6">
    <div class="input-style-1">
      <label>Lavozim (EN, ixtiyoriy)</label>
      <input type="text" name="lavozim_en" value="{{ old('lavozim_en', $teacher?->lavozim_en) }}">
    </div>
  </div>
  <div class="col-md-6">
    <div class="input-style-1">
      <label>Toifa (EN, ixtiyoriy)</label>
      <input type="text" name="toifa_en" value="{{ old('toifa_en', $teacher?->toifa_en) }}">
    </div>
  </div>
</div>

<div class="input-style-1">
  <label>Fan yo'nalishi <span class="text-muted" style="font-weight:400;">— ixtiyoriy</span></label>
  <input type="text" name="subject" value="{{ old('subject', $teacher?->subject) }}" placeholder="Masalan: Matematika" autocomplete="off">
  <small style="color:#64748b;display:block;margin-top:6px;">Bo‘sh qoldirsangiz, saytda fan nomi boshqa maydonlar (lavozim va hokazo) orqali ko‘rsatiladi yoki umuman ko‘rsatilmaydi.</small>
</div>

<div class="input-style-1">
  <label>Fan yo'nalishi (EN, ixtiyoriy)</label>
  <input type="text" name="subject_en" value="{{ old('subject_en', $teacher?->subject_en) }}">
</div>

<div class="input-style-1">
  <label>Sinflar <span class="text-muted" style="font-weight:400;">— ixtiyoriy</span></label>
  <input type="text" name="grades" value="{{ old('grades', $teacher?->grades) }}" placeholder="Masalan: 7-11-sinflar" autocomplete="off">
  <small style="color:#64748b;display:block;margin-top:6px;">Bo‘sh qoldirsangiz, saytda «barcha sinflar» deb ko‘rinadi.</small>
</div>

<div class="admin-teacher-achievements-wrap">
  <p class="admin-teacher-achievements-hint mb-15">
    <i class="fa-solid fa-trophy" style="color:#ca8a04;"></i>
    Yutuqlar va mukofotlar — <strong>majburiy emas</strong>. Qo'shsangiz, saytda alohida ajralib turadigan blokda ko'rinadi.
  </p>
  <div class="input-style-1 mb-0">
    <label>Yutuqlar va mukofotlar</label>
    <textarea name="achievements" rows="4" placeholder="Har bir qatorga bitta yutuq yozing">{{ old('achievements', $teacher?->achievements) }}</textarea>
  </div>
  <div class="input-style-1 mb-0">
    <label>Yutuqlar va mukofotlar (EN, ixtiyoriy)</label>
    <textarea name="achievements_en" rows="4">{{ old('achievements_en', $teacher?->achievements_en) }}</textarea>
  </div>
</div>

<div class="input-style-1">
  <label>Rasm <span class="text-muted" style="font-weight:400;">— ixtiyoriy</span></label>
  <input type="file" name="image" accept=".jpg,.jpeg,.png,.webp">
  <small style="color:#64748b;display:block;margin-top:6px;">Rasm qo‘yilmasa, saytda standart rasm ishlatiladi.</small>
  @if($teacher?->image)
    <div style="margin-top:10px;">
      <img src="{{ app_storage_asset($teacher->image) }}" alt="{{ $teacher->full_name }}" style="width:140px;aspect-ratio:3/2;object-fit:cover;border-radius:10px;">
    </div>
  @endif
</div>

<div class="form-check checkbox-style mb-20">
  <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $teacher?->is_active ?? true) ? 'checked' : '' }}>
  <label class="form-check-label" for="is_active">Faol</label>
</div>

<button type="submit" class="main-btn primary-btn btn-hover">
  {{ $teacher ? 'Saqlash' : "Qo'shish" }}
</button>
