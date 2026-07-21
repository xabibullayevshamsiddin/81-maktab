<x-layouts.main title="Temalar galereyasi — 81-IDUM">

@push("page_styles")
<style>
/* ====== TEMALAR SHOWCASE (qayta dizayn) ====== */
:root {
  --ts-plain: #64748b;
  --ts-supporter: #3b82f6;
  --ts-premium: #8b5cf6;
  --ts-vip: #f59e0b;
  --ts-gold: #eab308;
  --ts-royal: #dc2626;
  --ts-phoenix: #ea580c;
}

/* ===== HERO ===== */
.ts-hero {
  position: relative;
  padding: 150px 1.5rem 4rem;
  text-align: center;
  color: #fff;
  border-radius: 0 0 2.5rem 2.5rem;
  margin-bottom: 2.5rem;
  overflow: hidden;
  background: #0f172a;
}
.ts-hero::before {
  content: '';
  position: absolute;
  inset: 0;
  background:
    radial-gradient(ellipse at 20% 20%, rgba(139,92,246,0.35), transparent 45%),
    radial-gradient(ellipse at 80% 30%, rgba(234,179,8,0.25), transparent 45%),
    radial-gradient(ellipse at 50% 80%, rgba(220,38,38,0.22), transparent 50%),
    linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #312e81 100%);
  z-index: 0;
}
/* yulduzlar/zarrachalar */
.ts-hero::after {
  content: '';
  position: absolute;
  inset: 0;
  background-image:
    radial-gradient(1px 1px at 10% 20%, rgba(255,255,255,0.6), transparent),
    radial-gradient(1px 1px at 30% 60%, rgba(255,255,255,0.5), transparent),
    radial-gradient(1px 1px at 70% 30%, rgba(255,255,255,0.7), transparent),
    radial-gradient(1px 1px at 90% 70%, rgba(255,255,255,0.5), transparent),
    radial-gradient(2px 2px at 50% 40%, rgba(255,255,255,0.4), transparent),
    radial-gradient(1px 1px at 15% 80%, rgba(255,255,255,0.5), transparent),
    radial-gradient(1px 1px at 85% 15%, rgba(255,255,255,0.6), transparent);
  background-size: 100% 100%;
  animation: ts-twinkle 6s ease-in-out infinite alternate;
  z-index: 0;
}
@keyframes ts-twinkle { from { opacity: 0.4; } to { opacity: 1; } }
.ts-hero-inner { position: relative; z-index: 2; }
.ts-hero-badge {
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  padding: 0.35rem 0.9rem;
  border-radius: 999px;
  background: rgba(255,255,255,0.1);
  backdrop-filter: blur(8px);
  border: 1px solid rgba(255,255,255,0.2);
  font-size: 0.75rem;
  font-weight: 700;
  margin-bottom: 1rem;
}
.ts-hero h1 {
  font-size: clamp(2.2rem, 6vw, 4rem);
  font-weight: 900;
  margin-bottom: 0.8rem;
  letter-spacing: -0.02em;
  background: linear-gradient(90deg, #c4b5fd, #fbbf24, #f87171, #c4b5fd);
  background-size: 200% auto;
  -webkit-background-clip: text;
  background-clip: text;
  -webkit-text-fill-color: transparent;
  animation: ts-gradient-shift 5s linear infinite;
}
@keyframes ts-gradient-shift { to { background-position: 200% center; } }
.ts-hero p {
  font-size: clamp(1rem, 2vw, 1.2rem);
  opacity: 0.9;
  max-width: 640px;
  margin: 0 auto 1.5rem;
  line-height: 1.6;
}
.ts-hero-cta {
  display: inline-flex;
  gap: 0.7rem;
  flex-wrap: wrap;
  justify-content: center;
}
.ts-hero-cta a {
  padding: 0.7rem 1.5rem;
  border-radius: 999px;
  font-weight: 700;
  font-size: 0.9rem;
  text-decoration: none;
  transition: transform 0.2s, box-shadow 0.2s;
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
}
.ts-hero-cta a:hover { transform: translateY(-2px); }
.ts-btn-glow {
  background: linear-gradient(135deg, #8b5cf6, #6366f1);
  color: #fff;
  box-shadow: 0 8px 24px rgba(139,92,246,0.4);
}
.ts-btn-glass {
  background: rgba(255,255,255,0.1);
  color: #fff;
  border: 1px solid rgba(255,255,255,0.25);
  backdrop-filter: blur(8px);
}

/* ===== SECTION TITLE ===== */
.ts-section-head {
  text-align: center;
  max-width: 720px;
  margin: 0 auto 2rem;
  padding: 0 1.5rem;
}
.ts-section-head h2 {
  font-size: clamp(1.5rem, 3.5vw, 2.2rem);
  font-weight: 800;
  color: var(--text);
  margin-bottom: 0.5rem;
}
.ts-section-head p { color: var(--muted); font-size: 0.95rem; line-height: 1.6; }

/* ===== THEME GRID ===== */
.ts-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(330px, 1fr));
  gap: 1.75rem;
  padding: 0 1.5rem 3rem;
  max-width: 1280px;
  margin: 0 auto;
}

/* ===== THEME CARD ===== */
.ts-card {
  --tc: var(--ts-plain);
  position: relative;
  border-radius: 1.5rem;
  overflow: hidden;
  background: var(--surface);
  border: 1px solid var(--border);
  transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1), box-shadow 0.3s;
}
.ts-card:hover {
  transform: translateY(-8px);
  box-shadow: 0 24px 60px rgba(0,0,0,0.15), 0 0 0 1px var(--tc);
}

/* VIP/Premium/Admin uchun shimmer border */
.ts-card--premium::before,
.ts-card--vip::before,
.ts-card--admin-gold::before,
.ts-card--admin-royal::before,
.ts-card--admin-phoenix::before {
  content: '';
  position: absolute;
  inset: -1px;
  border-radius: inherit;
  padding: 1.5px;
  background: linear-gradient(135deg, var(--tc), transparent, var(--tc));
  background-size: 200% 100%;
  -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
  -webkit-mask-composite: xor;
  mask-composite: exclude;
  animation: ts-border-flow 4s linear infinite;
  pointer-events: none;
  z-index: 1;
}
@keyframes ts-border-flow { to { background-position: 200% 0; } }

/* Card header — gradient banner */
.ts-card-head {
  position: relative;
  padding: 1.75rem 1.5rem 1.25rem;
  text-align: center;
  color: #fff;
  overflow: hidden;
  background: linear-gradient(140deg, var(--tc), color-mix(in srgb, var(--tc) 55%, #000));
}
.ts-card-head::before {
  content: '';
  position: absolute;
  inset: 0;
  background:
    radial-gradient(circle at 30% 0%, rgba(255,255,255,0.25), transparent 50%),
    radial-gradient(circle at 70% 100%, rgba(0,0,0,0.2), transparent 50%);
  pointer-events: none;
}
.ts-card-head::after {
  content: '';
  position: absolute;
  top: -50%; left: -50%;
  width: 200%; height: 200%;
  background: linear-gradient(115deg, transparent 40%, rgba(255,255,255,0.18) 50%, transparent 60%);
  background-size: 200% 100%;
  animation: ts-sheen 5s ease-in-out infinite;
  pointer-events: none;
}
@keyframes ts-sheen {
  0%, 100% { transform: translateX(-30%); }
  50% { transform: translateX(30%); }
}

/* Rang variantlari */
.ts-card--plain .ts-card-head { background: linear-gradient(140deg, #94a3b8, #475569); }
.ts-card--supporter { --tc: var(--ts-supporter); }
.ts-card--premium { --tc: var(--ts-premium); }
.ts-card--vip { --tc: var(--ts-vip); }
.ts-card--admin-gold { --tc: var(--ts-gold); }
.ts-card--admin-royal { --tc: var(--ts-royal); }
.ts-card--admin-phoenix { --tc: var(--ts-phoenix); }

.ts-head-inner { position: relative; z-index: 2; }
.ts-icon-wrap {
  width: 64px; height: 64px;
  margin: 0 auto 0.6rem;
  border-radius: 50%;
  background: rgba(255,255,255,0.2);
  backdrop-filter: blur(6px);
  border: 2px solid rgba(255,255,255,0.35);
  display: flex;
  align-items: center;
  justify-content: center;
}
.ts-icon-wrap i { font-size: 1.6rem; filter: drop-shadow(0 2px 6px rgba(0,0,0,0.3)); }
.ts-title { font-size: 1.5rem; font-weight: 800; letter-spacing: 0.01em; }
.ts-tag {
  display: inline-block;
  font-size: 0.62rem;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.1em;
  padding: 0.22rem 0.75rem;
  border-radius: 999px;
  margin-top: 0.5rem;
  background: rgba(255,255,255,0.9);
  color: var(--tc);
}
.ts-tag--free { background: rgba(255,255,255,0.95); color: #15803d; }
.ts-tag--admin { background: #fff; color: #b91c1c; }

/* Card body */
.ts-card-body { padding: 1.5rem; position: relative; z-index: 2; }

/* Price + status */
.ts-meta {
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
  margin-bottom: 1.1rem;
  padding-bottom: 1rem;
  border-bottom: 1px dashed var(--border-soft);
}
.ts-price {
  font-size: 1.4rem;
  font-weight: 900;
  color: var(--text);
  line-height: 1;
}
.ts-price small {
  display: block;
  font-size: 0.68rem;
  color: var(--muted);
  font-weight: 500;
  margin-top: 0.2rem;
}
.ts-lock-badge {
  font-size: 0.7rem;
  font-weight: 800;
  display: inline-flex;
  align-items: center;
  gap: 0.3rem;
  padding: 0.3rem 0.7rem;
  border-radius: 999px;
}
.ts-lock-badge--active { background: color-mix(in srgb, var(--tc) 15%, transparent); color: var(--tc); }
.ts-lock-badge--open { background: rgba(34,197,94,0.15); color: #16a34a; }
.ts-lock-badge--locked { background: rgba(148,163,184,0.15); color: #94a3b8; }

/* Preview section */
.ts-prev-label {
  font-size: 0.62rem;
  font-weight: 800;
  color: var(--muted);
  text-transform: uppercase;
  letter-spacing: 0.08em;
  margin-bottom: 0.45rem;
  display: flex;
  align-items: center;
  gap: 0.3rem;
}

/* Profile preview */
.ts-prev-profile {
  display: flex;
  align-items: center;
  gap: 0.7rem;
  padding: 0.75rem;
  border-radius: 0.75rem;
  background: color-mix(in srgb, var(--tc) 6%, var(--surface));
  border: 1px solid color-mix(in srgb, var(--tc) 22%, transparent);
  margin-bottom: 0.6rem;
}
.ts-prev-av {
  width: 42px; height: 42px;
  border-radius: 50%;
  background: linear-gradient(135deg, var(--tc), color-mix(in srgb, var(--tc) 60%, #000));
  color: #fff;
  display: flex; align-items: center; justify-content: center;
  font-weight: 800; font-size: 1rem;
  flex-shrink: 0;
  box-shadow: 0 4px 12px color-mix(in srgb, var(--tc) 35%, transparent);
}
.ts-prev-name {
  font-weight: 800;
  font-size: 0.95rem;
  color: var(--tc);
  display: flex;
  align-items: center;
  gap: 0.35rem;
  flex-wrap: wrap;
  line-height: 1.3;
}
.ts-prev-name-badge {
  display: inline-flex;
  align-items: center;
  gap: 0.2rem;
  font-size: 0.58rem;
  font-weight: 800;
  padding: 0.1rem 0.5rem;
  border-radius: 999px;
  background: color-mix(in srgb, var(--tc) 15%, transparent);
  color: var(--tc);
  text-transform: uppercase;
  letter-spacing: 0.04em;
}

/* Chat preview */
.ts-prev-chat {
  display: flex;
  align-items: flex-start;
  gap: 0.5rem;
  padding: 0.6rem 0.75rem;
  border-radius: 0.6rem;
  background: var(--surface);
  border-left: 3px solid var(--tc);
  margin-bottom: 0.6rem;
}
.ts-prev-chat-av {
  width: 26px; height: 26px;
  border-radius: 50%;
  background: var(--tc);
  color: #fff;
  display: flex; align-items: center; justify-content: center;
  font-weight: 700; font-size: 0.65rem; flex-shrink: 0;
}
.ts-prev-chat-name { font-size: 0.75rem; font-weight: 700; color: var(--tc); }
.ts-prev-chat-text { font-size: 0.68rem; color: var(--muted); margin-top: 1px; }

/* Comment preview */
.ts-prev-comment {
  padding: 0.6rem 0.75rem;
  border-radius: 0.6rem;
  background: linear-gradient(135deg, color-mix(in srgb, var(--tc) 8%, var(--surface)) 0%, var(--surface) 60%);
  border-left: 3px solid var(--tc);
  margin-bottom: 0.6rem;
}
.ts-prev-comment-name { font-size: 0.72rem; font-weight: 700; color: var(--tc); margin-bottom: 0.2rem; }
.ts-prev-comment-text { font-size: 0.68rem; color: var(--text); opacity: 0.85; line-height: 1.4; }

/* Features list */
.ts-features {
  list-style: none;
  padding: 0;
  margin: 0.5rem 0 1rem;
}
.ts-features li {
  display: flex;
  align-items: flex-start;
  gap: 0.45rem;
  font-size: 0.75rem;
  color: var(--text);
  padding: 0.3rem 0;
  line-height: 1.4;
}
.ts-features li i {
  color: var(--tc);
  font-size: 0.65rem;
  margin-top: 4px;
  flex-shrink: 0;
}
.ts-features li.ts-feat-off { color: var(--muted); opacity: 0.6; }
.ts-features li.ts-feat-off i { color: var(--muted); }

/* Button */
.ts-action {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.4rem;
  width: 100%;
  padding: 0.75rem;
  border-radius: 0.75rem;
  font-weight: 800;
  font-size: 0.85rem;
  text-decoration: none;
  transition: transform 0.15s, box-shadow 0.2s, opacity 0.2s;
}
.ts-action:hover { transform: translateY(-1px); }
.ts-action--primary {
  background: linear-gradient(135deg, var(--tc), color-mix(in srgb, var(--tc) 65%, #000));
  color: #fff;
  box-shadow: 0 6px 18px color-mix(in srgb, var(--tc) 35%, transparent);
}
.ts-action--current {
  background: color-mix(in srgb, var(--tc) 12%, transparent);
  color: var(--tc);
  border: 1.5px solid var(--tc);
}
.ts-action--locked {
  background: rgba(148,163,184,0.15);
  color: #94a3b8;
  cursor: not-allowed;
  border: 1.5px dashed rgba(148,163,184,0.4);
}

/* Mobile */
@media (max-width: 640px) {
  .ts-grid { grid-template-columns: 1fr; padding: 0 1rem 2rem; }
  .ts-hero { padding: 120px 1rem 3rem; }
}
</style>
@endpush

<section class="ts-hero">
  <div class="ts-hero-inner">
    <span class="ts-hero-badge"><i class="fa-solid fa-sparkles"></i> 7 ta eksklyuziv tema</span>
    <h1>Profilingizni bezang</h1>
    <p>Har bir darajada profilingiz qanday ko'rinishini jonli ko'ring. Chatdagi, izohlardagi va profil bosh sahifasidagi ko'rinish — barchasi shu yerda.</p>
    <div class="ts-hero-cta">
      @auth
        <a href="{{ route('profile.show', ['panel' => 'appearance']) }}" class="ts-btn-glow"><i class="fa-solid fa-palette"></i> Temamni tanlash</a>
      @else
        <a href="{{ route('login') }}" class="ts-btn-glow"><i class="fa-solid fa-right-to-bracket"></i> Kirish</a>
      @endauth
      <a href="{{ route('donation.index') }}" class="ts-btn-glass"><i class="fa-solid fa-star"></i> Donat qilish</a>
    </div>
  </div>
</section>

<div class="ts-section-head">
  <h2>Tema galereyasi</h2>
  <p>Yuqoridan pastgacha — oddiydan eng eksklyuzivgacha. Har birining imtiyozlari va ko'rinishini solishtiring.</p>
</div>

<div class="ts-grid">
  @php
    $featuresByTheme = [
      'plain' => [
        ['check' => true, 'text' => 'Standart profil ko\'rinishi'],
        ['check' => false, 'text' => 'Rangli ism va badge'],
        ['check' => false, 'text' => 'Profil bezak effektlari'],
        ['check' => false, 'text' => 'Kattalashtirilgan avatar'],
      ],
      'supporter' => [
        ['check' => true, 'text' => 'Ko\'k rangli ism va badge'],
        ['check' => true, 'text' => 'Izoh va chatda ajralib turish'],
        ['check' => true, 'text' => 'Avatar 10 MB gacha'],
        ['check' => true, 'text' => 'AI chat — 100 xabar'],
      ],
      'premium' => [
        ['check' => true, 'text' => 'Binafsha rangli premium badge'],
        ['check' => true, 'text' => 'Profil shimmer effekti'],
        ['check' => true, 'text' => 'Avatar 25 MB gacha'],
        ['check' => true, 'text' => 'AI chat — 300 xabar'],
      ],
      'vip' => [
        ['check' => true, 'text' => 'Oltin VIP toji badge'],
        ['check' => true, 'text' => 'Animatsiyali profil ramka'],
        ['check' => true, 'text' => 'Avatar 50 MB gacha'],
        ['check' => true, 'text' => 'AI chat — cheksiz'],
      ],
      'admin-gold' => [
        ['check' => true, 'text' => 'Eksklyuziv oltin medal'],
        ['check' => true, 'text' => 'Premium glow + sheen'],
        ['check' => true, 'text' => 'Faqat super admin uchun'],
        ['check' => false, 'text' => '—'],
      ],
      'admin-royal' => [
        ['check' => true, 'text' => 'Qirollik qizil rang'],
        ['check' => true, 'text' => 'Premium glow + sheen'],
        ['check' => true, 'text' => 'Faqat super admin uchun'],
        ['check' => false, 'text' => '—'],
      ],
      'admin-phoenix' => [
        ['check' => true, 'text' => 'Olov Feniks rang'],
        ['check' => true, 'text' => 'Premium glow + sheen'],
        ['check' => true, 'text' => 'Faqat super admin uchun'],
        ['check' => false, 'text' => '—'],
      ],
    ];
    $prices = [
      'plain' => ['amount' => 'Bepul', 'note' => 'Hammaga ochiq'],
      'supporter' => ['amount' => \App\Models\Donation::priceLabel('supporter'), 'note' => 'Eng arzon daraja'],
      'premium' => ['amount' => \App\Models\Donation::priceLabel('premium'), 'note' => 'Eng mashhur'],
      'vip' => ['amount' => \App\Models\Donation::priceLabel('vip'), 'note' => 'Maksimal imtiyozlar'],
      'admin-gold' => ['amount' => 'Maxsus', 'note' => 'Faqat super admin'],
      'admin-royal' => ['amount' => 'Maxsus', 'note' => 'Faqat super admin'],
      'admin-phoenix' => ['amount' => 'Maxsus', 'note' => 'Faqat super admin'],
    ];
    $sampleNames = [
      'plain' => 'Aziz', 'supporter' => 'Jasur', 'premium' => 'Dilnoza',
      'vip' => 'Sardor', 'admin-gold' => 'Admin', 'admin-royal' => 'Admin', 'admin-phoenix' => 'Admin',
    ];
    $currentTheme = $currentUser?->profile_theme ?? $currentUser?->donation_rank;
  @endphp

  @foreach($themes as $key => $t)
    @php
      $color = $t['badge_color'];
      $icon = $t['badge_icon'];
      $label = $t['label'];
      $type = $t['type'] ?? 'donor';
      $sampleName = $sampleNames[$key] ?? 'Foydalanuvchi';
      $initial = mb_substr($sampleName, 0, 1);
      $isActive = $currentUser && $currentTheme === $key;
      $allowed = $themeAllowed[$key] ?? false;
      $isAdminTheme = $type === 'admin';
      $isPlain = $type === 'plain';
      $price = $prices[$key] ?? ['amount' => '—', 'note' => ''];
      $feats = $featuresByTheme[$key] ?? [];
      $tagClass = $isAdminTheme ? 'ts-tag--admin' : ($isPlain ? 'ts-tag--free' : '');
      $tagText = $isAdminTheme ? 'Super Admin' : ($isPlain ? 'Bepul' : 'Donor');
    @endphp

    <div class="ts-card ts-card--{{ $key }}" style="--tc: {{ $color }};">
      <div class="ts-card-head">
        <div class="ts-head-inner">
          <div class="ts-icon-wrap"><i class="{{ $icon }}"></i></div>
          <div class="ts-title">{{ $label }}</div>
          <span class="ts-tag {{ $tagClass }}">{{ $tagText }}</span>
        </div>
      </div>

      <div class="ts-card-body">
        {{-- Narx + holat --}}
        <div class="ts-meta">
          <div class="ts-price">{{ $price['amount'] }}<small>{{ $price['note'] }}</small></div>
          @if($isActive)
            <span class="ts-lock-badge ts-lock-badge--active"><i class="fa-solid fa-check-circle"></i> Sizniki</span>
          @elseif($allowed)
            <span class="ts-lock-badge ts-lock-badge--open"><i class="fa-solid fa-circle-check"></i> Ochiq</span>
          @else
            <span class="ts-lock-badge ts-lock-badge--locked"><i class="fa-solid fa-lock"></i> Qulflangan</span>
          @endif
        </div>

        {{-- Profil preview --}}
        <div class="ts-prev-label"><i class="fa-solid fa-id-card"></i> Profil ko'rinishi</div>
        <div class="ts-prev-profile">
          <div class="ts-prev-av">{{ $initial }}</div>
          <div class="ts-prev-name">
            {{ $sampleName }}
            <span class="ts-prev-name-badge"><i class="{{ $icon }}"></i> {{ $label }}</span>
          </div>
        </div>

        {{-- Chat preview --}}
        <div class="ts-prev-label"><i class="fa-solid fa-comment-dots"></i> Chatdagi ko'rinishi</div>
        <div class="ts-prev-chat">
          <div class="ts-prev-chat-av">{{ $initial }}</div>
          <div>
            <div class="ts-prev-chat-name">{{ $sampleName }}</div>
            <div class="ts-prev-chat-text">Salom hammaga! Bugun dushmanlar ustidan g'alaba! 🔥</div>
          </div>
        </div>

        {{-- Comment preview --}}
        <div class="ts-prev-label"><i class="fa-solid fa-comments"></i> Izohdagi ko'rinishi</div>
        <div class="ts-prev-comment">
          <div class="ts-prev-comment-name">{{ $sampleName }}</div>
          <div class="ts-prev-comment-text">Juda zo'r yangilik! Rahmat maktab rahbariyatiga.</div>
        </div>

        {{-- Features --}}
        <div class="ts-prev-label" style="margin-top:0.8rem;"><i class="fa-solid fa-gift"></i> Imtiyozlar</div>
        <ul class="ts-features">
          @foreach($feats as $f)
            <li class="{{ !$f['check'] ? 'ts-feat-off' : '' }}">
              <i class="fa-solid {{ $f['check'] ? 'fa-check' : 'fa-xmark' }}"></i>
              {{ $f['text'] }}
            </li>
          @endforeach
        </ul>

        {{-- Action --}}
        @if($isActive)
          <a href="{{ route('profile.show', ['panel' => 'appearance']) }}" class="ts-action ts-action--current">
            <i class="fa-solid fa-check"></i> Joriy tema
          </a>
        @elseif($allowed)
          <a href="{{ route('profile.show', ['panel' => 'appearance']) }}" class="ts-action ts-action--primary">
            <i class="fa-solid fa-palette"></i> Tanlash
          </a>
        @elseif($isPlain)
          <a href="{{ route('profile.show', ['panel' => 'appearance']) }}" class="ts-action ts-action--primary">
            <i class="fa-solid fa-user"></i> Bepul tanlash
          </a>
        @elseif($isAdminTheme)
          <span class="ts-action ts-action--locked"><i class="fa-solid fa-crown"></i> Faqat super admin</span>
        @else
          <a href="{{ route('donation.checkout', $key) }}" class="ts-action ts-action--primary">
            <i class="fa-solid fa-bolt"></i> {{ $label }} sotib olish
          </a>
        @endif
      </div>
    </div>
  @endforeach
</div>

</x-loyouts.main>
