@php
  $currentOptions = isset($question)
    ? $question->options->keyBy('label')
    : collect();
  $currentCorrect = isset($question)
    ? optional($question->options->firstWhere('is_correct', true))->label
    : 'A';
@endphp

<div class="input-style-1">
  <label>Savol matni</label>
  <textarea name="body" rows="3" required>{{ old('body', $question->body ?? '') }}</textarea>
</div>

<div class="input-style-1">
  <label>Bu savol uchun ball (to‘g‘ri javob uchun)</label>
  <input type="number" name="points" min="1" max="1000" value="{{ old('points', $question->points ?? 1) }}" required>
  @error('points')
    <p class="text-danger" style="color:#b91c1c;font-size:13px;margin-top:6px;">{{ $message }}</p>
  @enderror
</div>

<div class="input-style-1">
  <label>Savollar ro‘yxatidagi tartib raqami</label>
  <input type="number" name="sort_order" min="0" value="{{ old('sort_order', $question->sort_order ?? 0) }}">
  <small style="color:#64748b;display:block;margin-top:6px;">
    Bu yutuq yoki ball emas — faqat <strong>ketma-ketlik</strong>: kichik raqamli savollar ro‘yxatda avvalroq turadi (0, 1, 2…). Bir xil raqam bo‘lsa, boshqa maydonlar bo‘yicha tartilanadi.
  </small>
</div>

<div id="option-box">
  @foreach(['A','B','C','D'] as $label)
    <div class="input-style-1 option-row" data-label="{{ $label }}">
      <label>Variant {{ $label }}</label>
      <input type="text" name="options[{{ $label }}]" value="{{ old('options.'.$label, $currentOptions[$label]->body ?? '') }}" required>
    </div>
  @endforeach
</div>

<div class="select-style-1">
  <label>To'g'ri javob</label>
  <div class="select-position">
    <select name="correct_label" required>
      @foreach(['A','B','C','D'] as $label)
        <option value="{{ $label }}" {{ old('correct_label', $currentCorrect) === $label ? 'selected' : '' }}>{{ $label }}</option>
      @endforeach
    </select>
  </div>
</div>

<button type="button" id="shuffle-options" class="main-btn info-btn btn-hover btn-sm mb-20">Variantlarni joyini almashtirish</button>
<br>
<button type="submit" class="main-btn primary-btn btn-hover">Saqlash</button>

<script>
  const labels = ['A', 'B', 'C', 'D'];
  document.getElementById('shuffle-options')?.addEventListener('click', () => {
    const box = document.getElementById('option-box');
    const values = labels.map(l => box.querySelector(`[name="options[${l}]"]`).value);
    for (let i = values.length - 1; i > 0; i--) {
      const j = Math.floor(Math.random() * (i + 1));
      [values[i], values[j]] = [values[j], values[i]];
    }
    labels.forEach((l, idx) => {
      box.querySelector(`[name="options[${l}]"]`).value = values[idx];
    });
  });
</script>

