@extends('admin.layouts.main')

@section('title', 'Sozlamalar | Admin Panel')

@section('content')
<div class="row">
  <div class="col-lg-8">
    <div class="card-style mb-30">
      <h6 class="mb-10">Sayt sozlamalari</h6>
      <p class="text-sm" style="color:#64748b;margin-bottom:20px;">Maktab ma'lumotlari va ijtimoiy tarmoq havolalari.</p>

      @if(session('success'))
        <div class="alert-box success-alert mb-20">
          <div class="alert">
            <p>{{ session('success') }}</p>
          </div>
        </div>
      @endif

      <form action="{{ route('admin.settings.update') }}" method="POST">
        @csrf
        @method('PUT')

        <h6 class="mb-10" style="font-size:14px;margin-top:20px;">Global e'lon (sayt tepasida)</h6>
        <p class="text-sm" style="color:#64748b;margin-bottom:16px;">Barcha tashrifchilar uchun sahifa yuqorisidagi tasma. Chat va AI vidjetlari alohida sozlanadi (pastdagi bo‘lim).</p>

        <div class="input-style-1 mb-20">
          <label>E'lon holati</label>
          <div style="display:flex;gap:16px;align-items:center;margin-top:6px;">
            <label style="display:flex;align-items:center;gap:6px;cursor:pointer;">
              <input type="radio" name="announcement_active" value="1" {{ old('announcement_active', $settings['announcement_active'] ?? '0') === '1' ? 'checked' : '' }} />
              <span style="font-size:14px;">Yoqilgan</span>
            </label>
            <label style="display:flex;align-items:center;gap:6px;cursor:pointer;">
              <input type="radio" name="announcement_active" value="0" {{ old('announcement_active', $settings['announcement_active'] ?? '0') !== '1' ? 'checked' : '' }} />
              <span style="font-size:14px;">O'chirilgan</span>
            </label>
          </div>
        </div>

        <div class="input-style-1 mb-20">
          <label>E'lon matni</label>
          <input type="text" name="announcement_text" value="{{ old('announcement_text', $settings['announcement_text'] ?? '') }}" placeholder="Masalan: 15-apreldan imtihonlar boshlanadi!" maxlength="500" />
          @error('announcement_text') <p class="text-danger" style="font-size:13px;">{{ $message }}</p> @enderror
        </div>

        <div class="input-style-1 mb-20">
          <label>E'lon turi (rang)</label>
          <select name="announcement_type" class="form-control" style="padding:10px 12px;border-radius:8px;">
            <option value="info" {{ old('announcement_type', $settings['announcement_type'] ?? 'info') === 'info' ? 'selected' : '' }}>Axborot (ko'k)</option>
            <option value="success" {{ old('announcement_type', $settings['announcement_type'] ?? '') === 'success' ? 'selected' : '' }}>Muvaffaqiyat (yashil)</option>
            <option value="warning" {{ old('announcement_type', $settings['announcement_type'] ?? '') === 'warning' ? 'selected' : '' }}>Ogohlantirish (sariq)</option>
            <option value="danger" {{ old('announcement_type', $settings['announcement_type'] ?? '') === 'danger' ? 'selected' : '' }}>Muhim (qizil)</option>
          </select>
        </div>

        <hr style="margin:28px 0;border-color:#e2e8f0;">

        <h6 class="mb-10" style="font-size:14px;margin-top:20px;">Global chat va AI (sayt vidjetlari)</h6>
        <p class="text-sm" style="color:#64748b;margin-bottom:16px;">O‘chirilganida foydalanuvchilar xabar yozolmaydi; ochilganda faqat siz yozgan matn ko‘rinadi.</p>

        <div class="input-style-1 mb-20">
          <label>Global chat</label>
          <div style="display:flex;gap:16px;align-items:center;margin-top:6px;">
            <label style="display:flex;align-items:center;gap:6px;cursor:pointer;">
              <input type="radio" name="global_chat_enabled" value="1" {{ old('global_chat_enabled', $settings['global_chat_enabled'] ?? '1') === '1' ? 'checked' : '' }} />
              <span style="font-size:14px;">Yoqilgan</span>
            </label>
            <label style="display:flex;align-items:center;gap:6px;cursor:pointer;">
              <input type="radio" name="global_chat_enabled" value="0" {{ old('global_chat_enabled', $settings['global_chat_enabled'] ?? '1') !== '1' ? 'checked' : '' }} />
              <span style="font-size:14px;">O‘chirilgan (xabar yozish mumkin emas)</span>
            </label>
          </div>
        </div>
        <div class="input-style-1 mb-20">
          <label>Global chat o‘chiq bo‘lganda matn</label>
          <textarea name="global_chat_disabled_message" rows="3" maxlength="1000" class="form-control" style="padding:10px 12px;border-radius:8px;width:100%;" placeholder="Masalan: Texnik ishlar olib borilmoqda.">{{ old('global_chat_disabled_message', $settings['global_chat_disabled_message'] ?? '') }}</textarea>
          @error('global_chat_disabled_message') <p class="text-danger" style="font-size:13px;">{{ $message }}</p> @enderror
        </div>

        <div class="input-style-1 mb-20">
          <label>AI yordamchi</label>
          <div style="display:flex;gap:16px;align-items:center;margin-top:6px;">
            <label style="display:flex;align-items:center;gap:6px;cursor:pointer;">
              <input type="radio" name="ai_chat_enabled" value="1" {{ old('ai_chat_enabled', $settings['ai_chat_enabled'] ?? '1') === '1' ? 'checked' : '' }} />
              <span style="font-size:14px;">Yoqilgan</span>
            </label>
            <label style="display:flex;align-items:center;gap:6px;cursor:pointer;">
              <input type="radio" name="ai_chat_enabled" value="0" {{ old('ai_chat_enabled', $settings['ai_chat_enabled'] ?? '1') !== '1' ? 'checked' : '' }} />
              <span style="font-size:14px;">O‘chirilgan (savol yozish mumkin emas)</span>
            </label>
          </div>
        </div>
        <div class="input-style-1 mb-20">
          <label>AI o‘chiq bo‘lganda matn</label>
          <textarea name="ai_chat_disabled_message" rows="3" maxlength="1000" class="form-control" style="padding:10px 12px;border-radius:8px;width:100%;" placeholder="Masalan: AI vaqtincha texnik ishlar uchun yopiq.">{{ old('ai_chat_disabled_message', $settings['ai_chat_disabled_message'] ?? '') }}</textarea>
          @error('ai_chat_disabled_message') <p class="text-danger" style="font-size:13px;">{{ $message }}</p> @enderror
        </div>

        <hr style="margin:28px 0;border-color:#e2e8f0;">

        <h6 class="mb-10" style="font-size:14px;margin-top:20px;">Maktab ma'lumotlari</h6>

        <div class="input-style-1 mb-20">
          <label>Maktab nomi</label>
          <input type="text" name="school_name" value="{{ old('school_name', $settings['school_name']) }}" placeholder="81-IDUM" />
          @error('school_name') <p class="text-danger" style="font-size:13px;">{{ $message }}</p> @enderror
        </div>

        <div class="input-style-1 mb-20">
          <label>Telefon</label>
          <input type="text" name="school_phone" value="{{ old('school_phone', $settings['school_phone']) }}" placeholder="+998 ..." />
          @error('school_phone') <p class="text-danger" style="font-size:13px;">{{ $message }}</p> @enderror
        </div>

        <div class="input-style-1 mb-20">
          <label>Email</label>
          <input type="email" name="school_email" value="{{ old('school_email', $settings['school_email']) }}" placeholder="info@school81.uz" />
          @error('school_email') <p class="text-danger" style="font-size:13px;">{{ $message }}</p> @enderror
        </div>

        <div class="input-style-1 mb-20">
          <label>Manzil</label>
          <input type="text" name="school_address" value="{{ old('school_address', $settings['school_address']) }}" placeholder="Maktab manzili" />
          @error('school_address') <p class="text-danger" style="font-size:13px;">{{ $message }}</p> @enderror
        </div>

        <h6 class="mb-10" style="font-size:14px;margin-top:28px;">Ijtimoiy tarmoqlar</h6>

        <div class="input-style-1 mb-20">
          <label>Telegram</label>
          <input type="url" name="social_telegram" value="{{ old('social_telegram', $settings['social_telegram']) }}" placeholder="https://t.me/..." />
          @error('social_telegram') <p class="text-danger" style="font-size:13px;">{{ $message }}</p> @enderror
        </div>

        <div class="input-style-1 mb-20">
          <label>Instagram</label>
          <input type="url" name="social_instagram" value="{{ old('social_instagram', $settings['social_instagram']) }}" placeholder="https://www.instagram.com/..." />
          @error('social_instagram') <p class="text-danger" style="font-size:13px;">{{ $message }}</p> @enderror
        </div>

        <div class="input-style-1 mb-20">
          <label>Facebook</label>
          <input type="url" name="social_facebook" value="{{ old('social_facebook', $settings['social_facebook']) }}" placeholder="https://www.facebook.com/..." />
          @error('social_facebook') <p class="text-danger" style="font-size:13px;">{{ $message }}</p> @enderror
        </div>

        <div class="input-style-1 mb-20">
          <label>YouTube</label>
          <input type="url" name="social_youtube" value="{{ old('social_youtube', $settings['social_youtube']) }}" placeholder="https://www.youtube.com/..." />
          @error('social_youtube') <p class="text-danger" style="font-size:13px;">{{ $message }}</p> @enderror
        </div>

        <button type="submit" class="main-btn primary-btn btn-hover">Saqlash</button>
      </form>
    </div>
  </div>
</div>
@endsection
