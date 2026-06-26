@php
  // Joriy faol tema — profile_theme (saqlangan) yoki donation_rank
  $currentTheme = $user->profile_theme ?? $user->donation_rank;
  $themeCfg = \App\Models\Donation::themeConfig($currentTheme);
  $themeColor = $themeCfg["badge_color"] ?? "#6366f1";
  $themeIcon = $themeCfg["badge_icon"] ?? "fa-solid fa-star";
  $themeLabel = $themeCfg["label"] ?? "Foydalanuvchi";
  $userColor = $user->donorUsernameColor() ?? "#3b82f6";

  // Barcha temalar ro'yxati
  $allThemes = \App\Models\Donation::THEMES();
  $donorThemes = array_filter($allThemes, fn($t) => $t["type"] === "donor");
  $adminThemes = array_filter($allThemes, fn($t) => $t["type"] === "admin");

  // Har bir tema uchun ruxsat holati
  $themeAllowed = [];
  foreach ($allThemes as $key => $t) {
    $themeAllowed[$key] = \App\Models\Donation::themeAllowedForUser($key, $user);
  }
@endphp

<style>
/* ====== TEMALAR PANELI (to'liq kenglik) ====== */
.ap-section-title {
  font-size: 0.65rem;
  font-weight: 700;
  color: var(--muted);
  text-transform: uppercase;
  letter-spacing: 0.06em;
  margin-bottom: 0.5rem;
  display: flex;
  align-items: center;
  gap: 0.4rem;
}
.ap-section-title i { font-size: 0.7rem; }

/* Tema gridi — 3 ustun (desktop), 2 ustun (tablet), 1 ustun (mobile) */
.ap-theme-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
  gap: 0.65rem;
  margin-bottom: 1.25rem;
}

.ap-theme-card {
  position: relative;
  border-radius: 12px;
  border: 2px solid var(--border);
  padding: 0.65rem 0.5rem;
  text-align: center;
  cursor: pointer;
  background: var(--surface);
  transition: border-color 0.2s, box-shadow 0.2s, transform 0.15s;
}
.ap-theme-card:hover:not(.ap-theme-card--locked) {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}
.ap-theme-card input[type="radio"] { display: none; }
.ap-theme-card .atc-icon { font-size: 1.4rem; margin-bottom: 0.25rem; }
.ap-theme-card .atc-name { font-size: 0.75rem; font-weight: 700; }
.ap-theme-card .atc-status {
  display: inline-flex;
  align-items: center;
  gap: 0.2rem;
  font-size: 0.55rem;
  font-weight: 700;
  margin-top: 0.3rem;
  padding: 0.12rem 0.5rem;
  border-radius: 999px;
}

/* Faol tema */
.ap-theme-card--active {
  border-color: var(--atc-color);
  box-shadow: 0 0 0 1px var(--atc-color), 0 4px 16px color-mix(in srgb, var(--atc-color) 15%, transparent);
}
.ap-theme-card--active .atc-status {
  background: color-mix(in srgb, var(--atc-color) 15%, transparent);
  color: var(--atc-color);
}

/* Qulflangan tema */
.ap-theme-card--locked {
  opacity: 0.45;
  cursor: not-allowed;
  filter: grayscale(0.5);
}
.ap-theme-card--locked .atc-status {
  background: rgba(148,163,184,0.1);
  color: #94a3b8;
}

/* Ruxsat etilgan lekin faol emas */
.ap-theme-card--allowed:not(.ap-theme-card--active) {
  opacity: 0.8;
}
.ap-theme-card--allowed:not(.ap-theme-card--active) .atc-status {
  background: color-mix(in srgb, var(--atc-color) 8%, transparent);
  color: var(--atc-color);
}

/* Admin tema badge — kichik "ADMIN" yorliq */
.ap-theme-card .atc-badge {
  position: absolute;
  top: -1px;
  right: -1px;
  font-size: 0.45rem;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  padding: 0.1rem 0.35rem;
  border-radius: 0 10px 0 8px;
  background: linear-gradient(135deg, #dc2626, #b91c1c);
  color: #fff;
}

/* Preview paneli */
.ap-preview {
  border-radius: 14px;
  padding: 1rem;
  margin-bottom: 1.25rem;
  border: 1px solid var(--border);
  background: var(--surface);
}
.ap-preview-row {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 0.75rem;
}
.ap-preview-box {
  padding: 0.6rem;
  border-radius: 10px;
  background: linear-gradient(135deg, color-mix(in srgb, var(--prev-color) 8%, transparent), transparent);
  border: 1px solid color-mix(in srgb, var(--prev-color) 20%, transparent);
}
.ap-preview-box .apb-label {
  font-size: 0.55rem;
  font-weight: 700;
  color: var(--muted);
  text-transform: uppercase;
  letter-spacing: 0.04em;
  margin-bottom: 0.3rem;
}
.ap-preview-box .apb-name {
  font-size: 0.9rem;
  font-weight: 800;
  color: var(--prev-color);
  display: flex;
  align-items: center;
  gap: 0.3rem;
  flex-wrap: wrap;
}

/* Chat xabar preview */
.ap-chat-msg {
  display: flex;
  align-items: flex-start;
  gap: 0.4rem;
  padding: 0.4rem;
  border-radius: 8px;
  background: var(--surface);
  border-left: 3px solid var(--prev-color);
}
.ap-chat-av {
  width: 22px; height: 22px; border-radius: 50%;
  background: var(--prev-color); color: #fff;
  display: flex; align-items: center; justify-content: center;
  font-weight: 700; font-size: 0.5rem; flex-shrink: 0;
}
.ap-chat-name { font-size: 0.72rem; font-weight: 700; color: var(--prev-color); }
.ap-chat-text { font-size: 0.65rem; color: var(--muted); }

/* Sozlamalar */
.ap-settings-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
  gap: 0.5rem;
  margin-bottom: 1rem;
}
.ap-setting-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.5rem 0.6rem;
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: 10px;
}
.ap-setting-row .asr-label { font-size: 0.75rem; font-weight: 600; }
.ap-setting-row .asr-desc { font-size: 0.62rem; color: var(--muted); }
.ap-setting-row select {
  padding: 0.3rem 0.45rem;
  border: 2px solid var(--border);
  border-radius: 7px;
  background: var(--bg);
  color: var(--text);
  font-size: 0.72rem;
  outline: none;
  width: 100px;
}

.ap-btn-save {
  width: 100%;
  padding: 0.7rem;
  background: var(--primary);
  color: #fff;
  border: none;
  border-radius: 10px;
  font-weight: 700;
  font-size: 0.82rem;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.4rem;
  transition: opacity 0.2s;
}
.ap-btn-save:hover { opacity: 0.9; }

/* Mobile */
@media (max-width:768px) {
  .ap-theme-grid { grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); }
  .ap-settings-grid { grid-template-columns: 1fr; }
}
</style>

<form method="POST" action="{{ route("profile.update-appearance") }}">
  @csrf
  @method("PUT")

  {{-- ====== TEMALAR BO'LIMI (to'liq kenglik) ====== --}}
  <div class="ap-section-title"><i class="fa-solid fa-palette"></i> Mavjud temalar</div>
  <div class="ap-theme-grid">

    @foreach($allThemes as $key => $t)
      @php
        $rc = $t["badge_color"];
        $ri = $t["badge_icon"];
        $rl = $t["label"];
        $active = $currentTheme === $key;
        $allowed = $themeAllowed[$key];
        $isAdminTheme = !empty($t["requires_admin"]);
        $cardClass = $active ? "ap-theme-card--active" : ($allowed ? "ap-theme-card--allowed" : "ap-theme-card--locked");
      @endphp
      <label class="ap-theme-card {{ $cardClass }}" style="--atc-color: {{ $rc }};">
        @if($isAdminTheme)
          <span class="atc-badge">Admin</span>
        @endif
        <input type="radio" name="donor_theme" value="{{ $key }}"
          {{ $active ? "checked" : "" }}
          {{ !$allowed ? "disabled" : "" }}>
        <div class="atc-icon" style="color:{{ $rc }};"><i class="{{ $ri }}"></i></div>
        <div class="atc-name" style="color:{{ $rc }};">{{ $rl }}</div>
        <div class="atc-status">
          @if($active)
            <i class="fa-solid fa-check"></i> Aktiv
          @elseif($allowed)
            <i class="fa-solid fa-circle"></i> Tanlash
          @else
            <i class="fa-solid fa-lock"></i> Qulflangan
          @endif
        </div>
      </label>
    @endforeach

  </div>

  {{-- ====== PREVIEW BO'LIMI ====== --}}
  <div class="ap-section-title"><i class="fa-solid fa-eye"></i> Korinish preview</div>
  <div class="ap-preview" style="--prev-color: {{ $themeColor }};">
    @if($donorIsActive || $themeAllowed[$currentTheme] ?? false)
      <div class="ap-preview-row">
        <div class="ap-preview-box">
          <div class="apb-label">Profil</div>
          <div class="apb-name"><i class="{{ $themeIcon }}" style="font-size:0.75rem;"></i> {{ $user->name ?? $user->buildNameFromParts() }}</div>
          {!! $user->donorBadgeHtml() !!}
        </div>
        <div class="ap-chat-msg">
          <div class="ap-chat-av">{{ mb_substr($themeLabel, 0, 1) }}</div>
          <div>
            <div class="ap-chat-name">{{ $themeLabel }} foydalanuvchi</div>
            <div class="ap-chat-text">Chatdagi xabar ko'rinishi</div>
          </div>
        </div>
      </div>
    @else
      <div style="padding:0.75rem; text-align:center;">
        <p style="color:var(--muted); font-size:0.8rem; margin:0;">Donor boling va profilingizni bezang!</p>
        <a href="{{ route('donation.index') }}" class="btn btn-sm" style="margin-top:0.5rem;"><i class="fa-solid fa-star"></i> Donat qilish</a>
      </div>
    @endif
  </div>

  {{-- ====== SOZLAMALAR BO'LIMI ====== --}}
  <div class="ap-section-title"><i class="fa-solid fa-sliders"></i> Sozlamalar</div>
  <div class="ap-settings-grid">
    <div class="ap-setting-row">
      <div><div class="asr-label">Badge stili</div><div class="asr-desc">Badge korinishi</div></div>
      <select name="badge_style">
        <option value="default" {{ ($user->badge_style??"default")=="default"?"selected":"" }}>Standart</option>
        <option value="pill" {{ ($user->badge_style??"")=="pill"?"selected":"" }}>Uzun</option>
        <option value="icon" {{ ($user->badge_style??"")=="icon"?"selected":"" }}>Ikonka</option>
      </select>
    </div>
    <div class="ap-setting-row">
      <div><div class="asr-label">Izoh stili</div><div class="asr-desc">Izoxda ajratish</div></div>
      <select name="comment_style">
        <option value="border" {{ ($user->comment_style??"border")=="border"?"selected":"" }}>Chet chiziq</option>
        <option value="filled" {{ ($user->comment_style??"")=="filled"?"selected":"" }}>Fon bilan</option>
      </select>
    </div>
    <div class="ap-setting-row">
      <div><div class="asr-label">Chatda badge</div><div class="asr-desc">Chatda korsatish</div></div>
      <select name="chat_style">
        <option value="show" {{ ($user->chat_style??"show")=="show"?"selected":"" }}>Korsatilsin</option>
        <option value="hide" {{ ($user->chat_style??"")=="hide"?"selected":"" }}>Yashirilsin</option>
      </select>
    </div>
    <div class="ap-setting-row">
      <div><div class="asr-label">Qolgan vaqt</div><div class="asr-desc">Badgeda kun korsatish</div></div>
      <select name="show_expiry_badge">
        <option value="1" {{ ($user->show_expiry_badge??"1")=="1"?"selected":"" }}>Korsatilsin</option>
        <option value="0" {{ ($user->show_expiry_badge??"")=="0"?"selected":"" }}>Yashirilsin</option>
      </select>
    </div>
    <div class="ap-setting-row">
      <div><div class="asr-label">Ism qalinligi</div><div class="asr-desc">Ism qalinligi</div></div>
      <select name="name_font_weight">
        <option value="600" {{ ($user->name_font_weight??"700")=="600"?"selected":"" }}>Normal</option>
        <option value="700" {{ ($user->name_font_weight??"700")=="700"?"selected":"" }}>Qalin</option>
        <option value="800" {{ ($user->name_font_weight??"")=="800"?"selected":"" }}>Juda qalin</option>
      </select>
    </div>
  </div>

  <button type="submit" class="ap-btn-save"><i class="fa-solid fa-check"></i> Saqlash</button>
</form>
