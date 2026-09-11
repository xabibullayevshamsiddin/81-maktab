@extends('admin.layouts.main')

@section('content')
<div class="row">
  <div class="col-lg-8 offset-lg-2">
    <div class="card-style mb-30">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
        <h6>IELTS Testini tahrirlash</h6>
        <div style="display:flex;gap:8px;">
          <a href="{{ route('admin.ielts.show', $test) }}" class="main-btn primary-btn btn-hover btn-sm">Savollar</a>
          <a href="{{ route('admin.ielts.index') }}" class="main-btn light-btn btn-hover btn-sm">Orqaga</a>
        </div>
      </div>

      <form method="POST" action="{{ route('admin.ielts.update', $test) }}">
        @csrf
        @method('PUT')

        <div class="input-style-1 mb-20">
          <label for="title">Test nomi <span class="text-danger">*</span></label>
          <input type="text" id="title" name="title" value="{{ old('title', $test->title) }}" required>
          @error('title')
            <span class="text-danger text-sm">{{ $message }}</span>
          @enderror
        </div>

        <div class="row">
          <div class="col-md-6">
            <div class="select-style-1 mb-20">
              <label for="type">Test turi <span class="text-danger">*</span></label>
              <div class="select-position">
                <select id="type" name="type" required>
                  <option value="placement" {{ old('type', $test->type) == 'placement' ? 'selected' : '' }}>Placement (Daraja aniqlash - qisqa)</option>
                  <option value="full_mock" {{ old('type', $test->type) == 'full_mock' ? 'selected' : '' }}>Full Mock (To'liq imtihon)</option>
                </select>
              </div>
              @error('type')
                <span class="text-danger text-sm">{{ $message }}</span>
              @enderror
            </div>
          </div>

          <div class="col-md-6">
            <div class="input-style-1 mb-20">
              <label for="time_limit_minutes">Umumiy vaqt (daqiqa) <span class="text-danger">*</span></label>
              <input type="number" id="time_limit_minutes" name="time_limit_minutes" value="{{ old('time_limit_minutes', $test->time_limit_minutes) }}" min="5" max="240" required>
              @error('time_limit_minutes')
                <span class="text-danger text-sm">{{ $message }}</span>
              @enderror
            </div>
          </div>
        </div>

        <div class="form-check form-switch mb-25">
          <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $test->is_active) ? 'checked' : '' }}>
          <label class="form-check-label" for="is_active">Test faol (foydalanuvchilarga ko'rinsin)</label>
        </div>

        <div class="d-flex justify-content-end gap-2">
          <a href="{{ route('admin.ielts.index') }}" class="main-btn light-btn btn-hover">Bekor qilish</a>
          <button type="submit" class="main-btn primary-btn btn-hover">Saqlash</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
