@php
  $currentOptions = isset($question)
    ? $question->options->keyBy('label')
    : collect();
  $currentCorrect = isset($question)
    ? optional($question->options->firstWhere('is_correct', true))->label
    : 'A';
  $currentImageUrl = old('existing_question_image', $question->image_url ?? null);
  $currentType = old('question_type', $question->question_type ?? 'multiple_choice');
@endphp

@if ($errors->any())
  <div class="alert-box danger-alert mb-20" style="display:block;">
    <div class="alert">
      <h4>Formada xatolar bor</h4>
      <ul style="margin:10px 0 0 18px;">
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  </div>
@endif

<div class="select-style-1 mb-20">
  <label>Savol turi</label>
  <div class="select-position">
    <select name="question_type" data-question-type-select>
      <option value="multiple_choice" {{ $currentType === 'multiple_choice' ? 'selected' : '' }}>Test (A, B, C, D variantli)</option>
      <option value="text" {{ $currentType === 'text' ? 'selected' : '' }}>Matnli ochiq savol</option>
    </select>
  </div>
  @error('question_type')
    <p class="text-danger" style="color:#b91c1c;font-size:13px;margin-top:6px;">{{ $message }}</p>
  @enderror
</div>

<div class="input-style-1">
  <label>Savol matni</label>
  <div class="exam-builder-toolbar" data-exam-toolbar>
    <button type="button" class="main-btn light-btn btn-hover btn-sm js-exam-wrap" data-before="<strong>" data-after="</strong>">Qalin yozuv</button>
    <button type="button" class="main-btn light-btn btn-hover btn-sm js-exam-wrap" data-before="<sup>" data-after="</sup>">Daraja (x2)</button>
    <button type="button" class="main-btn light-btn btn-hover btn-sm js-exam-wrap" data-before="<sub>" data-after="</sub>">Formula (H2)</button>
    <button type="button" class="main-btn light-btn btn-hover btn-sm js-exam-insert" data-insert="<br>">Qator tashlash</button>
  </div>
  <textarea name="body" rows="6" class="js-exam-rich-input" required>{{ old('body', $question->body ?? '') }}</textarea>
  @error('body')
    <p class="text-danger" style="color:#b91c1c;font-size:13px;margin-top:6px;">{{ $message }}</p>
  @enderror
  <p class="exam-builder-note">
    HTML yozishingiz shart emas. Kerakli matnni belgilang va yuqoridagi tugmani bosing.
  </p>
  <ol class="exam-builder-steps">
    <li>Oddiy savol bo'lsa, matnni yozib qo'ying.</li>
    <li>Biror so'zni ajratib ko'rsatmoqchi bo'lsangiz, o'sha joyni belgilang va `Qalin yozuv`ni bosing.</li>
    <li>Masalan `x2` yoki `H2O` ko'rinishidagi joy kerak bo'lsa, tegishli tugmadan foydalaning.</li>
  </ol>
</div>

<div class="input-style-1">
  <label>Rasm qo'shish</label>
  <input type="file" name="question_image" accept=".jpg,.jpeg,.png,.webp">
  @error('question_image')
    <p class="text-danger" style="color:#b91c1c;font-size:13px;margin-top:6px;">{{ $message }}</p>
  @enderror
  <p class="exam-builder-note">
    Chizma, grafik, jadval yoki formulani rasm qilib shu yerga yuklang. Jadvalni alohida yasash shart emas.
  </p>
  @if($currentImageUrl)
    <div class="exam-image-preview">
      <img src="{{ $currentImageUrl }}" alt="Savol rasmi" loading="lazy">
      <label class="exam-image-remove">
        <input type="checkbox" name="remove_question_image" value="1">
        Joriy rasmni olib tashlash
      </label>
    </div>
  @endif
</div>

<div class="input-style-1">
  <label>Bu savol uchun ball (to'g'ri javob uchun)</label>
  <input type="number" name="points" min="1" max="1000" value="{{ old('points', $question->points ?? 1) }}" required>
  @error('points')
    <p class="text-danger" style="color:#b91c1c;font-size:13px;margin-top:6px;">{{ $message }}</p>
  @enderror
</div>

<div class="input-style-1 mb-20" data-question-text-fields>
  <label>Namunaviy javob</label>
  <textarea name="model_answer" rows="4" class="form-control">{{ old('model_answer', $question->model_answer ?? '') }}</textarea>
  @error('model_answer')
    <p class="text-danger" style="color:#b91c1c;font-size:13px;margin-top:6px;">{{ $message }}</p>
  @enderror
  <p class="exam-builder-note">
    Bu javob o'quvchiga ko'rinmaydi. Faqat tekshiruvchi uchun namunaviy javob sifatida saqlanadi.
  </p>
</div>

<div data-question-mcq-fields>
  <div class="input-style-1">
    <label>Variantlar</label>
    <p class="exam-builder-note" style="margin-top:-4px;margin-bottom:10px;">
      Variantlarda ham xuddi shu usul ishlaydi. Kerakli joyni belgilang va yuqoridagi tugmalardan foydalaning.
    </p>
  </div>

  <div id="option-box">
    @foreach(['A','B','C','D'] as $label)
      <div class="input-style-1 option-row" data-label="{{ $label }}">
        <label>Variant {{ $label }}</label>
        <textarea name="options[{{ $label }}]" rows="2" class="js-exam-rich-input">{{ old('options.'.$label, $currentOptions[$label]->body ?? '') }}</textarea>
        @error('options.'.$label)
          <p class="text-danger" style="color:#b91c1c;font-size:13px;margin-top:6px;">{{ $message }}</p>
        @enderror
      </div>
    @endforeach
  </div>

  <div class="select-style-1">
    <label>To'g'ri javob</label>
    <div class="select-position">
      <select name="correct_label">
        @foreach(['A','B','C','D'] as $label)
          <option value="{{ $label }}" {{ old('correct_label', $currentCorrect) === $label ? 'selected' : '' }}>{{ $label }}</option>
        @endforeach
      </select>
    </div>
    @error('correct_label')
      <p class="text-danger" style="color:#b91c1c;font-size:13px;margin-top:6px;">{{ $message }}</p>
    @enderror
  </div>

  <button type="button" id="shuffle-options" class="main-btn info-btn btn-hover btn-sm mb-20">Variantlarni joyini almashtirish</button>
</div>
<br>
<button type="submit" class="main-btn primary-btn btn-hover">Saqlash</button>

