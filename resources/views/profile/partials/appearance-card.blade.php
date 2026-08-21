@php
  // Joriy faol tema — profile_theme (saqlangan) yoki donation_rank
  $currentTheme = $user->effectiveTheme() ?? 'plain';
  $themeCfg = \App\Models\Donation::themeConfig($currentTheme);
  $themeColor = $themeCfg["badge_color"] ?? "#6366f1";
  $themeIcon = $themeCfg["badge_icon"] ?? "fa-solid fa-star";
  $themeLabel = $themeCfg["label"] ?? "Foydalanuvchi";
  $userColor = $user->donorUsernameColor() ?? "#3b82f6";
  $donorIsActive = $user->isDonor();

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
  transition: border-color 0.2s, box-shadow 0.2s, transform 0.15s, opacity 0.2s;
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

/* Tanlangan tema — check belgisi (o'ng tepada) */
.ap-theme-card .atc-check {
  position: absolute;
  top: 6px; right: 6px;
  width: 20px; height: 20px;
  border-radius: 50%;
  background: var(--atc-color);
  color: #fff;
  display: none; /* faqat tanlanganda ko'rinadi */
  align-items: center;
  justify-content: center;
  font-size: 0.6rem;
  box-shadow: 0 2px 6px color-mix(in srgb, var(--atc-color) 40%, transparent);
  z-index: 2;
}

/* Faol tema (server-side), :checked holati (CSS :has) yoki JS --selected klassi */
.ap-theme-card--active,
.ap-theme-card--selected,
.ap-theme-card:has(input[type="radio"]:checked) {
  border-color: var(--atc-color);
  box-shadow: 0 0 0 1px var(--atc-color), 0 6px 20px color-mix(in srgb, var(--atc-color) 18%, transparent);
  opacity: 1;
}
.ap-theme-card--active .atc-check,
.ap-theme-card--selected .atc-check,
.ap-theme-card:has(input[type="radio"]:checked) .atc-check {
  display: flex;
}
/* Status matni tanlanganda */
.ap-theme-card:has(input[type="radio"]:checked) .atc-status::before {
  content: "\f00c"; /* fa-check */
  font-family: "Font Awesome 6 Free";
  font-weight: 900;
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
  color: #fff;
}
.ap-theme-card .atc-badge--admin { background: linear-gradient(135deg, #dc2626, #b91c1c); }
.ap-theme-card .atc-badge--free { background: linear-gradient(135deg, #16a34a, #15803d); }

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

/* Sozlamalar grid */
.ap-settings-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 0.6rem;
  margin-bottom: 1.25rem;
}

.ap-setting-row {
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  gap: 0.65rem;
  padding: 0.75rem 0.85rem;
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: 14px;
  transition: border-color 0.2s, box-shadow 0.2s, transform 0.15s;
  position: relative;
  overflow: hidden;
}

.ap-setting-row::before {
  content: '';
  position: absolute;
  inset: 0;
  background: linear-gradient(135deg, rgba(99,102,241,0.03) 0%, transparent 60%);
  pointer-events: none;
}

.ap-setting-row:hover {
  border-color: rgba(99,102,241,0.35);
  box-shadow: 0 4px 16px rgba(99,102,241,0.08);
  transform: translateY(-1px);
}

.ap-setting-row .asr-label {
  font-size: 0.72rem;
  font-weight: 700;
  color: var(--text);
  letter-spacing: 0.01em;
  line-height: 1.3;
  display: flex;
  align-items: center;
  gap: 0.3rem;
  flex-wrap: wrap;
}

.ap-setting-row .asr-desc {
  font-size: 0.6rem;
  color: var(--muted);
  line-height: 1.4;
  margin-top: 0.15rem;
}

/* Select dropdown — neon glassmorphism */
.ap-setting-row select {
  width: 100%;
  padding: 0.5rem 2.2rem 0.5rem 0.7rem;
  border: 1.5px solid var(--border);
  border-radius: 12px;
  background: linear-gradient(135deg, var(--bg) 0%, rgba(99,102,241,0.03) 100%);
  color: var(--text);
  font-size: 0.72rem;
  font-weight: 600;
  outline: none;
  cursor: pointer;
  appearance: none;
  -webkit-appearance: none;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%236366f1' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right 0.5rem center;
  transition: all 0.35s cubic-bezier(0.34, 1.56, 0.64, 1);
  position: relative;
  backdrop-filter: blur(8px);
}

.ap-setting-row select:hover {
  border-color: rgba(99,102,241,0.45);
  box-shadow: 0 4px 20px rgba(99,102,241,0.12), inset 0 1px 0 rgba(255,255,255,0.1);
  transform: translateY(-2px) scale(1.01);
  background: linear-gradient(135deg, var(--bg) 0%, rgba(99,102,241,0.06) 100%);
}

.ap-setting-row select:focus {
  border-color: rgba(99,102,241,0.6);
  box-shadow: 0 0 0 4px rgba(99,102,241,0.12), 0 8px 24px rgba(99,102,241,0.15), inset 0 1px 0 rgba(255,255,255,0.15);
  transform: translateY(-2px) scale(1.01);
  background: linear-gradient(135deg, rgba(99,102,241,0.04) 0%, rgba(99,102,241,0.08) 100%);
}

.ap-setting-row select:active {
  transform: scale(0.97) translateY(0);
  box-shadow: 0 2px 8px rgba(99,102,241,0.15);
  transition-duration: 0.12s;
}

/* Neon border sweep — select o'zgarganda */
@keyframes neonSweep {
  0% { background-position: -200% center; }
  100% { background-position: 200% center; }
}
@keyframes neonPop {
  0% { transform: scale(1); }
  30% { transform: scale(1.04); }
  60% { transform: scale(0.97); }
  100% { transform: scale(1); }
}
@keyframes glowFade {
  0% { box-shadow: 0 0 0 0 rgba(99,102,241,0.4), 0 4px 20px rgba(99,102,241,0.2); }
  50% { box-shadow: 0 0 0 8px rgba(99,102,241,0), 0 4px 20px rgba(99,102,241,0.3); }
  100% { box-shadow: 0 0 0 0 rgba(99,102,241,0), 0 2px 8px rgba(99,102,241,0.05); }
}
.ap-setting-row select.is-changed {
  animation: neonPop 0.45s cubic-bezier(0.34, 1.56, 0.64, 1), glowFade 0.6s ease-out;
  border-color: rgba(168,85,247,0.6);
  box-shadow: 0 0 0 3px rgba(168,85,247,0.15), 0 4px 20px rgba(99,102,241,0.25), inset 0 1px 0 rgba(255,255,255,0.15);
  background: linear-gradient(135deg, rgba(99,102,241,0.06) 0%, rgba(168,85,247,0.06) 100%);
}



/* Full-width setting row */
.ap-setting-row--full {
  flex-direction: column;
  align-items: stretch;
  gap: 0.75rem;
}

/* Cursor Type Grid */
.cursor-type-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
  gap: 0.5rem;
}

.cursor-type-card {
  position: relative;
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 0.75rem 0.5rem;
  border: 2px solid var(--border);
  border-radius: 12px;
  background: var(--surface);
  cursor: pointer;
  transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
  text-align: center;
}

.cursor-type-card:hover {
  border-color: var(--cursor-color);
  transform: translateY(-2px);
  box-shadow: 0 4px 12px color-mix(in srgb, var(--cursor-color) 20%, transparent);
}

.cursor-type-card--active {
  border-color: var(--cursor-color);
  background: color-mix(in srgb, var(--cursor-color) 8%, var(--surface));
  box-shadow: 0 0 0 2px var(--cursor-color), 0 4px 16px color-mix(in srgb, var(--cursor-color) 25%, transparent);
}

.cursor-type-radio {
  display: none;
}

.cursor-type-icon {
  position: relative;
  width: 48px;
  height: 48px;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 0.4rem;
}

.cursor-type-icon > i {
  font-size: 1.4rem;
  color: var(--cursor-color);
  z-index: 2;
  transition: transform 0.3s ease;
}

.cursor-type-card:hover .cursor-type-icon > i {
  transform: scale(1.15);
}

.cursor-type-preview {
  position: absolute;
  width: 100%;
  height: 100%;
  border-radius: 50%;
  opacity: 0;
  transition: opacity 0.3s ease;
}

.cursor-type-card:hover .cursor-type-preview,
.cursor-type-card--active .cursor-type-preview {
  opacity: 1;
}

/* Cursor Preview Animations */
.cursor-preview--orbit {
  border: 2px solid var(--cursor-color);
  animation: cursorOrbitSpin 3s linear infinite;
}

.cursor-preview--pulse {
  background: var(--cursor-color);
  animation: cursorPulsePulse 1.5s ease-in-out infinite;
}

.cursor-preview--glass {
  background: radial-gradient(circle, rgba(139,92,246,0.3) 0%, transparent 70%);
  filter: blur(2px);
}

.cursor-preview--trailing {
  background: radial-gradient(circle, var(--cursor-color) 0%, transparent 70%);
  animation: cursorTrailingGlow 2s ease-in-out infinite;
}

.cursor-preview--arrow {
  background: linear-gradient(135deg, var(--cursor-color) 0%, transparent 60%);
  transform: rotate(45deg);
  animation: cursorArrowRotate 4s ease-in-out infinite;
}

.cursor-preview--color_shifter {
  background: linear-gradient(135deg, #06b6d4, #8b5cf6, #ec4899, #f59e0b);
  background-size: 300% 300%;
  animation: cursorColorShift 4s ease infinite;
}

@keyframes cursorOrbitSpin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

@keyframes cursorPulsePulse {
  0%, 100% { transform: scale(0.8); opacity: 0.4; }
  50% { transform: scale(1.2); opacity: 0.7; }
}

@keyframes cursorTrailingGlow {
  0%, 100% { opacity: 0.3; transform: scale(0.9); }
  50% { opacity: 0.6; transform: scale(1.1); }
}

@keyframes cursorArrowRotate {
  0%, 100% { transform: rotate(45deg); }
  25% { transform: rotate(135deg); }
  50% { transform: rotate(225deg); }
  75% { transform: rotate(315deg); }
}

@keyframes cursorColorShift {
  0% { background-position: 0% 50%; }
  50% { background-position: 100% 50%; }
  100% { background-position: 0% 50%; }
}

.cursor-type-name {
  font-size: 0.7rem;
  font-weight: 700;
  color: var(--text);
  display: block;
}

.cursor-type-desc {
  font-size: 0.6rem;
  color: var(--muted);
  display: block;
  margin-top: 0.15rem;
}

/* Mobile cursor grid */
@media (max-width: 768px) {
  .cursor-type-grid {
    grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
  }
  .cursor-type-icon {
    width: 40px;
    height: 40px;
  }
  .cursor-type-icon > i {
    font-size: 1.1rem;
  }
}

/* Qulflangan (Non-Donor) sozlama satri */
.ap-setting-row--locked {
  position: relative;
  opacity: 0.72;
  background: color-mix(in srgb, var(--surface) 93%, #f59e0b 7%);
  border: 1px dashed rgba(245, 158, 11, 0.4);
  transition: all 0.25s ease;
}
.ap-setting-row--locked::before {
  background: linear-gradient(135deg, rgba(245,158,11,0.04) 0%, transparent 60%);
}
.ap-setting-row--locked:hover {
  opacity: 1;
  border-color: rgba(245, 158, 11, 0.7);
  box-shadow: 0 4px 16px rgba(245, 158, 11, 0.1);
  transform: translateY(-1px);
}
.ap-setting-row--locked select,
.ap-setting-row--locked input {
  opacity: 0.5;
  cursor: not-allowed;
  pointer-events: none;
}
.ap-lock-tag {
  display: inline-flex;
  align-items: center;
  gap: 0.2rem;
  font-size: 0.56rem;
  font-weight: 700;
  padding: 0.1rem 0.4rem;
  border-radius: 999px;
  background: linear-gradient(135deg, rgba(245, 158, 11, 0.18), rgba(217, 119, 6, 0.22));
  color: #f59e0b;
  border: 1px solid rgba(245, 158, 11, 0.3);
}
.ap-lock-unlock-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.3rem;
  width: 100%;
  font-size: 0.65rem;
  font-weight: 700;
  color: #f59e0b;
  text-decoration: none;
  padding: 0.38rem 0.6rem;
  border-radius: 9px;
  background: rgba(245, 158, 11, 0.1);
  border: 1.5px solid rgba(245, 158, 11, 0.35);
  transition: all 0.2s;
  white-space: nowrap;
}
.ap-lock-unlock-btn:hover {
  background: rgba(245, 158, 11, 0.2);
  border-color: rgba(245, 158, 11, 0.7);
  color: #fbbf24;
  box-shadow: 0 0 14px rgba(245, 158, 11, 0.2);
}

/* Emoji input full width */
.ap-setting-row input[type="text"] {
  width: 100%;
  padding: 0.4rem 0.6rem;
  border: 1.5px solid var(--border);
  border-radius: 9px;
  background: var(--bg);
  color: var(--text);
  font-size: 1.1rem;
  outline: none;
  text-align: center;
  transition: border-color 0.2s, box-shadow 0.2s;
}
.ap-setting-row input[type="text"]:focus {
  border-color: rgba(99,102,241,0.6);
  box-shadow: 0 0 0 3px rgba(99,102,241,0.12);
}

.ap-btn-save {
  width: 100%;
  padding: 0.8rem;
  background: linear-gradient(135deg, #6366f1, #4f46e5);
  color: #fff;
  border: none;
  border-radius: 12px;
  font-weight: 700;
  font-size: 0.85rem;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.45rem;
  transition: transform 0.15s, box-shadow 0.2s, opacity 0.2s;
  box-shadow: 0 4px 18px rgba(99,102,241,0.3);
}
.ap-btn-save:hover {
  transform: translateY(-1px);
  box-shadow: 0 6px 24px rgba(99,102,241,0.4);
}
.ap-btn-save:active {
  transform: translateY(0);
  box-shadow: 0 2px 10px rgba(99,102,241,0.25);
}

/* Responsive */
@media (max-width: 1024px) {
  .ap-settings-grid { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 768px) {
  .ap-theme-grid { grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); }
  .ap-settings-grid { grid-template-columns: 1fr; }
}
</style>

<form method="POST" action="{{ route("profile.update-appearance") }}">
  @csrf
  @method("PUT")

  {{-- ====== TEMALAR BO'LIMI (to'liq kenglik) ====== --}}
  <div class="ap-section-title" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.5rem;">
    <span><i class="fa-solid fa-palette"></i> {{ __('profile.appearance.themes_title') }}</span>
    <a href="{{ route('donation.themes') }}" style="font-size:0.65rem; font-weight:700; color:var(--primary); text-decoration:none;">
      {{ __('profile.appearance.themes_view_all') }} <i class="fa-solid fa-arrow-right" style="font-size:0.55rem;"></i>
    </a>
  </div>
  <div class="ap-theme-grid">

    @foreach($allThemes as $key => $t)
      @php
        $rc = $t["badge_color"];
        $ri = $t["badge_icon"];
        $rl = $t["label"];
        $active = $currentTheme === $key;
        $allowed = $themeAllowed[$key];
        $isAdminTheme = !empty($t["requires_admin"]);
        $isPlain = ($t["type"] ?? "") === "plain";
        $cardClass = $active ? "ap-theme-card--active" : ($allowed ? "ap-theme-card--allowed" : "ap-theme-card--locked");
      @endphp
      <label class="ap-theme-card {{ $cardClass }}" style="--atc-color: {{ $rc }};">
        @if($isAdminTheme)
          <span class="atc-badge atc-badge--admin">{{ __('profile.appearance.tag_admin') }}</span>
        @elseif($isPlain)
          <span class="atc-badge atc-badge--free">{{ __('profile.appearance.tag_free') }}</span>
        @endif
        <span class="atc-check"><i class="fa-solid fa-check"></i></span>
        <input type="radio" name="donor_theme" value="{{ $key }}"
          {{ $active ? "checked" : "" }}
          {{ !$allowed ? "disabled" : "" }}>
        <div class="atc-icon" style="color:{{ $rc }};"><i class="{{ $ri }}"></i></div>
        <div class="atc-name" style="color:{{ $rc }};">{{ $rl }}</div>
        <div class="atc-status" data-status-allow="{{ $allowed ? '1' : '0' }}">
          @if($active)
            <i class="fa-solid fa-check"></i> {{ __('profile.appearance.theme_active') }}
          @elseif($allowed)
            <i class="fa-solid fa-circle"></i> {{ __('profile.appearance.theme_select') }}
          @else
            <i class="fa-solid fa-lock"></i> {{ __('profile.appearance.theme_locked') }}
          @endif
        </div>
      </label>
    @endforeach

  </div>

  {{-- ====== PREVIEW BO'LIMI ====== --}}
  <div class="ap-section-title"><i class="fa-solid fa-eye"></i> {{ __('profile.appearance.preview_title') }}</div>
  <div class="ap-preview" style="--prev-color: {{ $themeColor }};">
    @php $canPreview = $themeAllowed[$currentTheme] ?? false; @endphp
    @if($canPreview)
      <div class="ap-preview-row">
        <div class="ap-preview-box">
          <div class="apb-label">{{ __('profile.appearance.preview_profile') }}</div>
          <div class="apb-name"><i class="{{ $themeIcon }}" style="font-size:0.75rem;"></i> {{ $user->name ?? $user->buildNameFromParts() }}</div>
          @if($donorIsActive)
            {!! $user->donorBadgeHtml() !!}
          @endif
        </div>
        <div class="ap-chat-msg">
          <div class="ap-chat-av">{{ mb_substr($themeLabel, 0, 1) }}</div>
          <div>
            <div class="ap-chat-name">{{ __('profile.appearance.preview_chat_user', ['theme' => $themeLabel]) }}</div>
            <div class="ap-chat-text">{{ __('profile.appearance.preview_chat') }}</div>
          </div>
        </div>
      </div>
    @else
      <div style="padding:0.75rem; text-align:center;">
        <p style="color:var(--muted); font-size:0.8rem; margin:0;">{{ __('profile.appearance.preview_locked') }}</p>
        <a href="{{ route('donation.index') }}" class="btn btn-sm" style="margin-top:0.5rem;"><i class="fa-solid fa-star"></i> {{ __('profile.appearance.preview_donate_btn') }}</a>
      </div>
    @endif
  </div>

  {{-- ====== SOZLAMALAR BO'LIMI ====== --}}
  <div class="ap-section-title"><i class="fa-solid fa-sliders"></i> {{ __('profile.appearance.settings_title') }}</div>
  <div class="ap-settings-grid">
    <div class="ap-setting-row {{ !$donorIsActive ? 'ap-setting-row--locked' : '' }}">
      <div>
        <div class="asr-label">
          {{ __('profile.appearance.badge_style_label') }}
          @if(!$donorIsActive) <span class="ap-lock-tag"><i class="fa-solid fa-lock"></i> Donater</span> @endif
        </div>
        <div class="asr-desc">{{ __('profile.appearance.badge_style_desc') }}</div>
      </div>
      @if($donorIsActive)
        <select name="badge_style">
          <option value="default" {{ ($user->badge_style??"default")=="default"?"selected":"" }}>{{ __('profile.appearance.badge_style_default') }}</option>
          <option value="pill" {{ ($user->badge_style??"")=="pill"?"selected":"" }}>{{ __('profile.appearance.badge_style_pill') }}</option>
          <option value="icon" {{ ($user->badge_style??"")=="icon"?"selected":"" }}>{{ __('profile.appearance.badge_style_icon') }}</option>
        </select>
      @else
        <a href="{{ route('donation.index') }}" class="ap-lock-unlock-btn" title="Donater bo'lish orqali oching">
          <i class="fa-solid fa-crown"></i> {{ __('profile.appearance.preview_donate_btn') }}
        </a>
      @endif
    </div>

    <div class="ap-setting-row {{ !$donorIsActive ? 'ap-setting-row--locked' : '' }}">
      <div>
        <div class="asr-label">
          {{ __('profile.appearance.comment_style_label') }}
          @if(!$donorIsActive) <span class="ap-lock-tag"><i class="fa-solid fa-lock"></i> Donater</span> @endif
        </div>
        <div class="asr-desc">{{ __('profile.appearance.comment_style_desc') }}</div>
      </div>
      @if($donorIsActive)
        <select name="comment_style">
          <option value="border" {{ ($user->comment_style??"border")=="border"?"selected":"" }}>{{ __('profile.appearance.comment_style_border') }}</option>
          <option value="filled" {{ ($user->comment_style??"")=="filled"?"selected":"" }}>{{ __('profile.appearance.comment_style_filled') }}</option>
        </select>
      @else
        <a href="{{ route('donation.index') }}" class="ap-lock-unlock-btn" title="Donater bo'lish orqali oching">
          <i class="fa-solid fa-crown"></i> {{ __('profile.appearance.preview_donate_btn') }}
        </a>
      @endif
    </div>

    <div class="ap-setting-row {{ !$donorIsActive ? 'ap-setting-row--locked' : '' }}">
      <div>
        <div class="asr-label">
          {{ __('profile.appearance.chat_badge_label') }}
          @if(!$donorIsActive) <span class="ap-lock-tag"><i class="fa-solid fa-lock"></i> Donater</span> @endif
        </div>
        <div class="asr-desc">{{ __('profile.appearance.chat_badge_desc') }}</div>
      </div>
      @if($donorIsActive)
        <select name="chat_style">
          <option value="show" {{ ($user->chat_style??"show")=="show"?"selected":"" }}>{{ __('profile.appearance.chat_badge_show') }}</option>
          <option value="hide" {{ ($user->chat_style??"")=="hide"?"selected":"" }}>{{ __('profile.appearance.chat_badge_hide') }}</option>
        </select>
      @else
        <a href="{{ route('donation.index') }}" class="ap-lock-unlock-btn" title="Donater bo'lish orqali oching">
          <i class="fa-solid fa-crown"></i> {{ __('profile.appearance.preview_donate_btn') }}
        </a>
      @endif
    </div>

    <div class="ap-setting-row {{ !$donorIsActive ? 'ap-setting-row--locked' : '' }}">
      <div>
        <div class="asr-label">
          {{ __('profile.appearance.expiry_label') }}
          @if(!$donorIsActive) <span class="ap-lock-tag"><i class="fa-solid fa-lock"></i> Donater</span> @endif
        </div>
        <div class="asr-desc">{{ __('profile.appearance.expiry_desc') }}</div>
      </div>
      @if($donorIsActive)
        <select name="show_expiry_badge">
          <option value="1" {{ ($user->show_expiry_badge??"1")=="1"?"selected":"" }}>{{ __('profile.appearance.expiry_show') }}</option>
          <option value="0" {{ ($user->show_expiry_badge??"")=="0"?"selected":"" }}>{{ __('profile.appearance.expiry_hide') }}</option>
        </select>
      @else
        <a href="{{ route('donation.index') }}" class="ap-lock-unlock-btn" title="Donater bo'lish orqali oching">
          <i class="fa-solid fa-crown"></i> {{ __('profile.appearance.preview_donate_btn') }}
        </a>
      @endif
    </div>

    <div class="ap-setting-row {{ !$donorIsActive ? 'ap-setting-row--locked' : '' }}">
      <div>
        <div class="asr-label">
          {{ __('profile.appearance.font_weight_label') }}
          @if(!$donorIsActive) <span class="ap-lock-tag"><i class="fa-solid fa-lock"></i> Donater</span> @endif
        </div>
        <div class="asr-desc">{{ __('profile.appearance.font_weight_label') }}</div>
      </div>
      @if($donorIsActive)
        <select name="name_font_weight">
          <option value="600" {{ ($user->name_font_weight??"700")=="600"?"selected":"" }}>{{ __('profile.appearance.font_weight_normal') }}</option>
          <option value="700" {{ ($user->name_font_weight??"700")=="700"?"selected":"" }}>{{ __('profile.appearance.font_weight_bold') }}</option>
          <option value="800" {{ ($user->name_font_weight??"")=="800"?"selected":"" }}>{{ __('profile.appearance.font_weight_bolder') }}</option>
        </select>
      @else
        <a href="{{ route('donation.index') }}" class="ap-lock-unlock-btn" title="Donater bo'lish orqali oching">
          <i class="fa-solid fa-crown"></i> {{ __('profile.appearance.preview_donate_btn') }}
        </a>
      @endif
    </div>

    <div class="ap-setting-row {{ !$donorIsActive ? 'ap-setting-row--locked' : '' }}">
      <div>
        <div class="asr-label">
          Shirft tanlash
          @if(!$donorIsActive) <span class="ap-lock-tag"><i class="fa-solid fa-lock"></i> Donater</span> @endif
        </div>
        <div class="asr-desc">Chatda ismingiz qanday shirftda ko'rinishini tanlang</div>
      </div>
      @if($donorIsActive)
        <select name="name_font_family">
          <option value="" {{ empty($user->name_font_family)?"selected":"" }}>Standart</option>
          <option value="orbitron" {{ ($user->name_font_family??"")=="orbitron"?"selected":"" }}> futuristic — Orbitron</option>
          <option value="caveat" {{ ($user->name_font_family??"")=="caveat"?"selected":"" }}> qo'lyozma — Caveat</option>
          <option value="press-start" {{ ($user->name_font_family??"")=="press-start"?"selected":"" }}> retro — Press Start 2P</option>
          <option value="pacifico" {{ ($user->name_font_family??"")=="pacifico"?"selected":"" }}> zamonaviy — Pacifico</option>
          <option value="righteous" {{ ($user->name_font_family??"")=="righteous"?"selected":"" }}> kuchli — Righteous</option>
          <option value="bungee" {{ ($user->name_font_family??"")=="bungee"?"selected":"" }}> chiziqli — Bungee</option>
          <option value="permanent-marker" {{ ($user->name_font_family??"")=="permanent-marker"?"selected":"" }}> marker — Permanent Marker</option>
        </select>
      @else
        <a href="{{ route('donation.index') }}" class="ap-lock-unlock-btn" title="Donater bo'lish orqali oching">
          <i class="fa-solid fa-crown"></i> {{ __('profile.appearance.preview_donate_btn') }}
        </a>
      @endif
    </div>

    <div class="ap-setting-row ap-setting-row--cursor {{ !$donorIsActive ? 'ap-setting-row--locked' : '' }}">
      <div>
        <div class="asr-label">
          {{ __('profile.appearance.cursor_label') }}
          @if(!$donorIsActive) <span class="ap-lock-tag"><i class="fa-solid fa-lock"></i> Donater</span> @endif
        </div>
        <div class="asr-desc">{{ __('profile.appearance.cursor_desc') }}</div>
      </div>
      @if($donorIsActive)
        @php $currentCursor = $user->donor_cursor_type ?? 'off'; @endphp
        <input type="hidden" name="donor_cursor_animation" id="cursor-animation-hidden" value="{{ ($currentCursor !== 'off') ? '1' : '0' }}">
        <select name="donor_cursor_type" id="cursor-type-select">
          <option value="off" {{ $currentCursor === 'off' ? 'selected' : '' }}>O'chirilgan</option>
          <option value="orbit" {{ $currentCursor === 'orbit' ? 'selected' : '' }}>Orbit</option>
          <option value="pulse" {{ $currentCursor === 'pulse' ? 'selected' : '' }}>Pulse</option>
          <option value="glass" {{ $currentCursor === 'glass' ? 'selected' : '' }}>Glass Lens</option>
          <option value="trailing" {{ $currentCursor === 'trailing' ? 'selected' : '' }}>Trailing</option>
          <option value="arrow" {{ $currentCursor === 'arrow' ? 'selected' : '' }}>Arrow</option>
          <option value="color_shifter" {{ $currentCursor === 'color_shifter' ? 'selected' : '' }}>Color Shift</option>
        </select>
      @else
        <a href="{{ route('donation.index') }}" class="ap-lock-unlock-btn" title="Donater bo'lish orqali oching">
          <i class="fa-solid fa-crown"></i> {{ __('profile.appearance.preview_donate_btn') }}
        </a>
      @endif
    </div>

    <div class="ap-setting-row {{ !$donorIsActive ? 'ap-setting-row--locked' : '' }}">
      <div>
        <div class="asr-label">
          {{ __('profile.appearance.bg_label') }}
          @if(!$donorIsActive) <span class="ap-lock-tag"><i class="fa-solid fa-lock"></i> Donater</span> @endif
        </div>
        <div class="asr-desc">{{ __('profile.appearance.bg_desc') }}</div>
      </div>
      @if($donorIsActive)
        <select name="profile_bg_style">
          <option value="plain"    {{ ($user->profile_bg_style??'plain')=='plain'   ?'selected':'' }}>{{ __('profile.appearance.bg_plain') }}</option>
          <option value="gradient" {{ ($user->profile_bg_style??'')=='gradient'     ?'selected':'' }}>{{ __('profile.appearance.bg_gradient') }}</option>
          <option value="mesh"     {{ ($user->profile_bg_style??'')=='mesh'         ?'selected':'' }}>{{ __('profile.appearance.bg_mesh') }}</option>
          <option value="aurora"   {{ ($user->profile_bg_style??'')=='aurora'       ?'selected':'' }}>{{ __('profile.appearance.bg_aurora') }}</option>
        </select>
      @else
        <a href="{{ route('donation.index') }}" class="ap-lock-unlock-btn" title="Donater bo'lish orqali oching">
          <i class="fa-solid fa-crown"></i> {{ __('profile.appearance.preview_donate_btn') }}
        </a>
      @endif
    </div>

    <div class="ap-setting-row {{ !$donorIsActive ? 'ap-setting-row--locked' : '' }}">
      <div>
        <div class="asr-label">
          {{ __('profile.appearance.badge_pos_label') }}
          @if(!$donorIsActive) <span class="ap-lock-tag"><i class="fa-solid fa-lock"></i> Donater</span> @endif
        </div>
        <div class="asr-desc">{{ __('profile.appearance.badge_pos_desc') }}</div>
      </div>
      @if($donorIsActive)
        <select name="badge_position">
          <option value="after"  {{ ($user->badge_position??'after')=='after' ?'selected':'' }}>{{ __('profile.appearance.badge_pos_after') }}</option>
          <option value="before" {{ ($user->badge_position??'')=='before'     ?'selected':'' }}>{{ __('profile.appearance.badge_pos_before') }}</option>
        </select>
      @else
        <a href="{{ route('donation.index') }}" class="ap-lock-unlock-btn" title="Donater bo'lish orqali oching">
          <i class="fa-solid fa-crown"></i> {{ __('profile.appearance.preview_donate_btn') }}
        </a>
      @endif
    </div>

    <div class="ap-setting-row {{ !$donorIsActive ? 'ap-setting-row--locked' : '' }}">
      <div>
        <div class="asr-label">
          {{ __('profile.appearance.banner_anim_label') }}
          @if(!$donorIsActive) <span class="ap-lock-tag"><i class="fa-solid fa-lock"></i> Donater</span> @endif
        </div>
        <div class="asr-desc">{{ __('profile.appearance.banner_anim_desc') }}</div>
      </div>
      @if($donorIsActive)
        <select name="banner_animation">
          <option value="none"  {{ ($user->banner_animation??'none')=='none'  ?'selected':'' }}>{{ __('profile.appearance.banner_anim_none') }}</option>
          <option value="pulse" {{ ($user->banner_animation??'')=='pulse'     ?'selected':'' }}>{{ __('profile.appearance.banner_anim_pulse') }}</option>
          <option value="wave"  {{ ($user->banner_animation??'')=='wave'      ?'selected':'' }}>{{ __('profile.appearance.banner_anim_wave') }}</option>
          <option value="slide" {{ ($user->banner_animation??'')=='slide'     ?'selected':'' }}>{{ __('profile.appearance.banner_anim_slide') }}</option>
        </select>
      @else
        <a href="{{ route('donation.index') }}" class="ap-lock-unlock-btn" title="Donater bo'lish orqali oching">
          <i class="fa-solid fa-crown"></i> {{ __('profile.appearance.preview_donate_btn') }}
        </a>
      @endif
    </div>

    <div class="ap-setting-row {{ !$donorIsActive ? 'ap-setting-row--locked' : '' }}">
      <div>
        <div class="asr-label">
          {{ __('profile.appearance.status_emoji_label') }}
          @if(!$donorIsActive) <span class="ap-lock-tag"><i class="fa-solid fa-lock"></i> Donater</span> @endif
        </div>
        <div class="asr-desc">{{ __('profile.appearance.status_emoji_desc') }}</div>
      </div>
      @if($donorIsActive)
        <input type="text" name="status_emoji"
          value="{{ $user->status_emoji }}"
          maxlength="2"
          placeholder="🔥">
      @else
        <a href="{{ route('donation.index') }}" class="ap-lock-unlock-btn" title="Donater bo'lish orqali oching">
          <i class="fa-solid fa-crown"></i> {{ __('profile.appearance.preview_donate_btn') }}
        </a>
      @endif
    </div>

    <div class="ap-setting-row {{ !$donorIsActive ? 'ap-setting-row--locked' : '' }}">
      <div>
        <div class="asr-label">
          <i class="fa-solid fa-i-cursor" style="font-size:0.75rem; opacity:0.7;"></i>
          Matn tanlash effekti
          @if(!$donorIsActive) <span class="ap-lock-tag"><i class="fa-solid fa-lock"></i> Donater</span> @endif
        </div>
        <div class="asr-desc">Saytdagi matnni belgilaganda rang effektini tanlang</div>
      </div>
      @if($donorIsActive)
        <select name="donor_text_selection">
          <option value="off"     {{ ($user->donor_text_selection ?? 'off') === 'off'     ? 'selected' : '' }}>O'chirilgan</option>
          <option value="blue"    {{ ($user->donor_text_selection ?? '') === 'blue'    ? 'selected' : '' }}>Ko'k (Blue)</option>
          <option value="purple"  {{ ($user->donor_text_selection ?? '') === 'purple'  ? 'selected' : '' }}>Binafsha (Purple)</option>
          <option value="cyan"    {{ ($user->donor_text_selection ?? '') === 'cyan'    ? 'selected' : '' }}>Moviy (Cyan)</option>
          <option value="gold"    {{ ($user->donor_text_selection ?? '') === 'gold'    ? 'selected' : '' }}>Oltin (Gold)</option>
          <option value="rose"    {{ ($user->donor_text_selection ?? '') === 'rose'    ? 'selected' : '' }}>Qizil-pushti (Rose)</option>
          <option value="emerald" {{ ($user->donor_text_selection ?? '') === 'emerald' ? 'selected' : '' }}>Yashil (Emerald)</option>
        </select>
      @else
        <a href="{{ route('donation.index') }}" class="ap-lock-unlock-btn" title="Donater bo'lish orqali oching">
          <i class="fa-solid fa-crown"></i> {{ __('profile.appearance.preview_donate_btn') }}
        </a>
      @endif
    </div>

  </div>

  <button type="submit" class="ap-btn-save"><i class="fa-solid fa-check"></i> {{ __('profile.appearance.save') }}</button>
</form>

<script>
// Tema kartasini bosganda vizual tanlanganlik (eski brauzerlar uchun ham)
(function () {
  var grid = document.querySelector('.ap-theme-grid');
  if (!grid) return;

  var cards = grid.querySelectorAll('.ap-theme-card');

  function markSelected() {
    cards.forEach(function (card) {
      var radio = card.querySelector('input[type="radio"]');
      var status = card.querySelector('.atc-status');
      var allow = status && status.getAttribute('data-status-allow') === '1';
      if (radio && radio.checked) {
        card.classList.add('ap-theme-card--selected');
        if (status && allow) status.innerHTML = '<i class="fa-solid fa-check"></i> {{ __('profile.appearance.theme_active') }}';
      } else {
        card.classList.remove('ap-theme-card--selected');
        if (status && allow && !card.classList.contains('ap-theme-card--active')) {
          status.innerHTML = '<i class="fa-solid fa-circle"></i> {{ __('profile.appearance.theme_select') }}';
        }
      }
    });
  }

  cards.forEach(function (card) {
    card.addEventListener('change', markSelected);
    card.addEventListener('click', function () {
      var radio = card.querySelector('input[type="radio"]');
      if (radio && !radio.disabled) {
        radio.checked = true;
        markSelected();
      }
    });
  });

  markSelected();
})();

// Kursor turini tanlash (select dropdown)
(function () {
  var cursorSelect = document.getElementById('cursor-type-select');
  var hiddenAnimation = document.getElementById('cursor-animation-hidden');
  if (!cursorSelect) return;

  function toggleAnimation() {
    if (hiddenAnimation) {
      hiddenAnimation.value = cursorSelect.value === 'off' ? '0' : '1';
    }
  }

  cursorSelect.addEventListener('change', toggleAnimation);
  toggleAnimation();
})();

// Select dropdownlarga smooth animatsiya
(function () {
  var selects = document.querySelectorAll('.ap-setting-row select');
  selects.forEach(function (sel) {
    sel.addEventListener('change', function () {
      this.classList.remove('is-changed');
      void this.offsetWidth;
      this.classList.add('is-changed');
      var self = this;
      setTimeout(function () { self.classList.remove('is-changed'); }, 600);
    });
  });
})();
</script>
