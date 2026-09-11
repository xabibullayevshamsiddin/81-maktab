@extends('admin.layouts.main')

@section('content')
<div class="row">
  <div class="col-lg-8 offset-lg-2">
    <div class="card-style mb-30">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
        <h6>Yangi IELTS Testi yaratish</h6>
        <a href="{{ route('admin.ielts.index') }}" class="main-btn light-btn btn-hover btn-sm">Orqaga</a>
      </div>

      <form method="POST" action="{{ route('admin.ielts.store') }}">
        @csrf

        <div class="input-style-1 mb-20">
          <label for="title">Test nomi <span class="text-danger">*</span></label>
          <input type="text" id="title" name="title" value="{{ old('title') }}" placeholder="Masalan: IELTS General Placement Test #1" required>
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
                  <option value="placement" {{ old('type') == 'placement' ? 'selected' : '' }}>Placement (Daraja aniqlash - qisqa)</option>
                  <option value="full_mock" {{ old('type') == 'full_mock' ? 'selected' : '' }}>Full Mock (To'liq imtihon)</option>
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
              <input type="number" id="time_limit_minutes" name="time_limit_minutes" value="{{ old('time_limit_minutes', 25) }}" min="5" max="240" required>
              @error('time_limit_minutes')
                <span class="text-danger text-sm">{{ $message }}</span>
              @enderror
            </div>
          </div>
        </div>

        <div class="form-check form-switch mb-25">
          <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', '1') ? 'checked' : '' }}>
          <label class="form-check-label" for="is_active">Test faol (foydalanuvchilarga ko'rinsin)</label>
        </div>

        <div class="alert alert-info py-2 text-sm mb-25">
          <i class="mdi mdi-information-outline me-1"></i>
          Test yaratilgach, standart 4 ta IELTS bo'limi (Reading, Writing, Listening, Speaking) avtomatik hosil qilinadi. Keyingi bosqichda ularga matn (passage), audio va savollarni birma-bir kiritishingiz mumkin.
        </div>

        <div class="d-flex justify-content-end gap-2">
          <a href="{{ route('admin.ielts.index') }}" class="main-btn light-btn btn-hover">Bekor qilish</a>
          <button type="submit" class="main-btn primary-btn btn-hover">Yaratish va davom etish</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
