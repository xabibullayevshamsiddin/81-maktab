@extends('admin.layouts.main')

@section('title', 'Sozlamalar | Admin Panel')

@section('content')
<div class="row">
  <div class="col-lg-8">
    <div class="card-style mb-30">
      <h6 class="mb-10">Sayt sozlamalari</h6>
      <p class="text-sm" style="color: var(--muted); margin-bottom:20px;">Maktab ma'lumotlari va ijtimoiy tarmoq havolalari.</p>

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

        <hr style="margin:28px 0;border-color:#e2e8f0;">

        <h6 class="mb-10" style="font-size:14px;margin-top:20px;">Global chat va AI (sayt vidjetlari)</h6>
        <p class="text-sm" style="color: var(--muted); margin-bottom:16px;">O‘chirilganida foydalanuvchilar xabar yozolmaydi; ochilganda faqat siz yozgan matn ko‘rinadi.</p>

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

  {{-- Telegram broadcast section --}}
  <div class="col-lg-4">
    <div class="card-style mb-30">
      <h6 class="mb-10" style="display:flex;align-items:center;gap:8px;">
        <i class="mdi mdi-send-outline" style="font-size:18px;color:#0088cc;"></i>
        Telegram elon yuborish
      </h6>
      <p class="text-sm" style="color: var(--muted); margin-bottom:20px;">
        Barcha Telegram botiga ulangan foydalanuvchilarga xabar yuboring.
      </p>

      @php
        $tgUserCount = \App\Models\User::whereNotNull('telegram_chat_id')
            ->where('telegram_chat_id', '>', 0)
            ->count();
      @endphp

      <div style="background: var(--badge-bg); border:1px solid var(--primary); border-radius:8px; padding:12px 16px; margin-bottom:20px;">
        <div style="display:flex;align-items:center;gap:8px;">
          <i class="mdi mdi-account-multiple-outline" style="font-size:16px; color: var(--primary);"></i>
          <span style="font-size:13px; color: var(--primary); font-weight:500;">{{ $tgUserCount }} ta foydalanuvchi ulangan</span>
        </div>
      </div>

      <form action="{{ route('admin.settings.broadcast-telegram') }}" method="POST"
            onsubmit="return confirm('Elon yuborilsinmi?');">
        @csrf

        <div style="background:var(--badge-bg);border-radius:8px;padding:14px 16px;margin-bottom:20px;">
          <label style="font-size:13px;font-weight:600;margin-bottom:10px;display:block;">📤 Qayerga yuborish:</label>
          <div style="display:flex;flex-direction:column;gap:8px;">
            <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
              <input type="checkbox" name="send_telegram" value="1" id="send_telegram_cb"
                     {{ old('send_telegram', '1') ? 'checked' : '' }}
                     style="width:18px;height:18px;accent-color:#0088cc;" />
              <span style="font-size:14px;">📱 Telegramga yuborish</span>
            </label>
            <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
              <input type="checkbox" name="also_site" value="1" id="also_site_checkbox"
                     {{ old('also_site') ? 'checked' : '' }}
                     style="width:18px;height:18px;accent-color:var(--primary);" />
              <span style="font-size:14px;">🌐 Saytda ko'rsatish</span>
            </label>
          </div>
        </div>

        <div id="telegram-options" style="margin-bottom:20px;">
          <div class="input-style-1 mb-20">
            <label>Auditoriya (Telegram uchun)</label>
            <select name="audience" class="form-control" style="padding:10px 12px;border-radius:8px;width:100%;">
              <option value="all">🌐 Hammaga</option>
              <option value="teachers">👨‍🏫 Faqat o'qituvchilarga</option>
              <option value="donors">⭐ Faqat donorlarga</option>
              <option value="students">🎓 Faqat o'quvchilarga</option>
            </select>
            @error('audience') <p class="text-danger" style="font-size:13px;">{{ $message }}</p> @enderror
          </div>
        </div>

        <div class="input-style-1 mb-20">
          <label>Xabar matni</label>
          <textarea
            name="message"
            rows="5"
            maxlength="4000"
            required
            class="form-control"
            style="padding:10px 12px;border-radius:8px;width:100%;resize:vertical;"
            placeholder="Elon matnini yozing..."
          >{{ old('message') }}</textarea>
          @error('message') <p class="text-danger" style="font-size:13px;">{{ $message }}</p> @enderror
        </div>
n        <div id="site-style-options" style="display:none;margin-bottom:20px;">
          <hr style="margin:0 0 16px 0;border-color:#e2e8f0;">
          <label style="font-size:13px;font-weight:600;margin-bottom:10px;display:block;">🎨 Banner uslubi (sayt uchun):</label>
          <div style="display:flex;gap:16px;flex-wrap:wrap;margin-bottom:16px;">
            <label style="display:flex;align-items:center;gap:6px;cursor:pointer;">
              <input type="radio" name="site_style" value="info" {{ old('site_style', 'info') === 'info' ? 'checked' : '' }} />
              <span style="font-size:13px;">🔵 Oddiy</span>
            </label>
            <label style="display:flex;align-items:center;gap:6px;cursor:pointer;">
              <input type="radio" name="site_style" value="warning" {{ old('site_style') === 'warning' ? 'checked' : '' }} />
              <span style="font-size:13px;">🟡 Ogohlantirish</span>
            </label>
            <label style="display:flex;align-items:center;gap:6px;cursor:pointer;">
              <input type="radio" name="site_style" value="urgent" {{ old('site_style') === 'urgent' ? 'checked' : '' }} />
              <span style="font-size:13px;">🔴 Shoshilinch</span>
            </label>
          </div>
          <div class="input-style-1 mb-12">
            <label style="font-size:12px;">Havola URL (ixtiyoriy)</label>
            <input type="url" name="link_url" value="{{ old('link_url') }}" placeholder="https://..."
                   style="padding:8px 12px;border-radius:8px;width:100%;" />
          </div>
          <div class="input-style-1 mb-0">
            <label style="font-size:12px;">Havola matni (ixtiyoriy)</label>
            <input type="text" name="link_label" value="{{ old('link_label') }}" placeholder="Batafsil"
                   style="padding:8px 12px;border-radius:8px;width:100%;" />
          </div>
        </div>

        <script>
          document.addEventListener('DOMContentLoaded', function() {
            var tgCb = document.getElementById('send_telegram_cb');
            var siteCb = document.getElementById('also_site_checkbox');
            var tgOpts = document.getElementById('telegram-options');
            var siteOpts = document.getElementById('site-style-options');
            function toggleAll() {
              tgOpts.style.display = tgCb && tgCb.checked ? 'block' : 'none';
              siteOpts.style.display = siteCb && siteCb.checked ? 'block' : 'none';
            }
            if (tgCb) tgCb.addEventListener('change', toggleAll);
            if (siteCb) siteCb.addEventListener('change', toggleAll);
            toggleAll();
          });
        </script>

        <button type="submit" class="main-btn primary-btn btn-hover" style="background: var(--primary); border-color: var(--primary); width:100%; display:flex; align-items:center; justify-content:center; gap:8px;">
          <i class="mdi mdi-send" style="font-size:16px;"></i>
          Yuborish
        </button>
      </form>

      <div style="margin-top:16px; padding-top:16px; border-top:1px solid var(--border);">
        <a href="{{ route('admin.settings.broadcast-history') }}" style="display:flex;align-items:center;gap:6px;font-size:13px;color:var(--primary);text-decoration:none;">
          <i class="mdi mdi-history" style="font-size:15px;"></i>
          E'lonlar tarixi
        </a>
      </div>
    </div>
  </div>
</div>
@endsection
