<div class="input-style-1">
  <label>Foydalanuvchi (teacher akkaunt)</label>
  <select name="user_id">
    <option value="">Tanlanmagan</option>
    @foreach(($teacherUsers ?? collect()) as $u)
      <option value="{{ $u->id }}" {{ (string) old('user_id', $teacher?->user_id) === (string) $u->id ? 'selected' : '' }}>
        {{ $u->name }} ({{ $u->email }})
      </option>
    @endforeach
  </select>
  <small style="color:#64748b;">Kurs ochishda teacher akkaunt shu karta bilan bog‘lanadi.</small>
</div>

<div class="input-style-1">
  <label>Ism-familiya</label>
  <input type="text" name="full_name" value="{{ old('full_name', $teacher?->full_name) }}" required>
</div>

<div class="input-style-1">
  <label>Fan yo'nalishi</label>
  <input type="text" name="subject" value="{{ old('subject', $teacher?->subject) }}" required>
</div>

<div class="row">
  <div class="col-md-6">
    <div class="input-style-1">
      <label>Tajriba (yil)</label>
      <input type="number" min="0" max="60" name="experience_years" value="{{ old('experience_years', $teacher?->experience_years ?? 0) }}" required>
    </div>
  </div>
  <div class="col-md-6">
    <div class="input-style-1">
      <label>Sinflar</label>
      <input type="text" name="grades" value="{{ old('grades', $teacher?->grades) }}" placeholder="Masalan: 7-11-sinflar">
    </div>
  </div>
</div>

<div class="input-style-1">
  <label>Yutuqlar va mukofotlar</label>
  <textarea name="achievements" rows="4" placeholder="Har bir qatorga bitta yutuq (masalan: Toshkent shahar olimpiadasi — 1-o‘rin)">{{ old('achievements', $teacher?->achievements) }}</textarea>
  <small style="color:#64748b;display:block;margin-top:6px;">Saytda ustoz kartochkasi va batafsil sahifada ko‘rinadi. Tartib raqami emas — bu yerda faqat yutuqlar yoziladi.</small>
</div>

<div class="input-style-1">
  <label>Ro‘yxatdagi tartib (raqam)</label>
  <input type="number" min="0" max="9999" name="sort_order" value="{{ old('sort_order', $teacher?->sort_order ?? 0) }}">
  <small style="color:#64748b;display:block;margin-top:6px;"><strong>Nima bu?</strong> «Ustozlar» sahifasida kartochkalar qaysi ketma-ketlikda chiqishini belgilaydi: <strong>kichik raqam</strong> (0, 1, 2…) ustunlik — yuqoriroqda turadi. Yutuqlar bilan aloqasi yo‘q.</small>
</div>

<div class="input-style-1">
  <label>Bio</label>
  <textarea name="bio" rows="5">{{ old('bio', $teacher?->bio) }}</textarea>
</div>

<div class="input-style-1">
  <label>Rasm</label>
  <input type="file" name="image" accept=".jpg,.jpeg,.png,.webp">
  @if($teacher?->image)
    <div style="margin-top:10px;">
      <img src="{{ asset('storage/' . $teacher->image) }}" alt="{{ $teacher->full_name }}" style="width:140px;aspect-ratio:3/2;object-fit:cover;border-radius:10px;">
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

