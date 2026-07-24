<x-layouts.main :title="__('public.donation.showcase_section_title').' — 81-IDUM'">

@push("page_styles")
<style>
:root{--ts-plain:#64748b;--ts-supporter:#3b82f6;--ts-premium:#8b5cf6;--ts-vip:#f59e0b;--ts-gold:#eab308;--ts-royal:#dc2626;--ts-phoenix:#ea580c}
.ts-hero{position:relative;padding:150px 1.5rem 4rem;text-align:center;color:#fff;border-radius:0 0 2.5rem 2.5rem;margin-bottom:2.5rem;overflow:hidden;background:#0f172a}
.ts-hero::before{content:'';position:absolute;inset:0;background:radial-gradient(ellipse at 20% 20%,rgba(139,92,246,.35),transparent 45%),radial-gradient(ellipse at 80% 30%,rgba(234,179,8,.25),transparent 45%),linear-gradient(135deg,#0f172a 0%,#1e1b4b 50%,#312e81 100%);z-index:0}
.ts-hero-inner{position:relative;z-index:2}
.ts-hero-badge{display:inline-flex;align-items:center;gap:.4rem;padding:.35rem .9rem;border-radius:999px;background:rgba(255,255,255,.1);backdrop-filter:blur(8px);border:1px solid rgba(255,255,255,.2);font-size:.75rem;font-weight:700;margin-bottom:1rem}
.ts-hero h1{font-size:clamp(2.2rem,6vw,4rem);font-weight:900;margin-bottom:.8rem;background:linear-gradient(90deg,#c4b5fd,#fbbf24,#f87171,#c4b5fd);background-size:200% auto;-webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent;animation:ts-gs 5s linear infinite}
@keyframes ts-gs{to{background-position:200% center}}
.ts-hero p{font-size:clamp(1rem,2vw,1.2rem);opacity:.9;max-width:640px;margin:0 auto 1.5rem;line-height:1.6}
.ts-hero-cta{display:inline-flex;gap:.7rem;flex-wrap:wrap;justify-content:center}
.ts-hero-cta a{padding:.7rem 1.5rem;border-radius:999px;font-weight:700;font-size:.9rem;text-decoration:none;transition:transform .2s;display:inline-flex;align-items:center;gap:.4rem}
.ts-hero-cta a:hover{transform:translateY(-2px)}
.ts-btn-glow{background:linear-gradient(135deg,#8b5cf6,#6366f1);color:#fff;box-shadow:0 8px 24px rgba(139,92,246,.4)}
.ts-btn-glass{background:rgba(255,255,255,.1);color:#fff;border:1px solid rgba(255,255,255,.25);backdrop-filter:blur(8px)}
.ts-section-head{text-align:center;max-width:720px;margin:0 auto 2rem;padding:0 1.5rem}
.ts-section-head h2{font-size:clamp(1.5rem,3.5vw,2.2rem);font-weight:800;color:var(--text);margin-bottom:.5rem}
.ts-section-head p{color:var(--muted);font-size:.95rem;line-height:1.6}
.ts-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(330px,1fr));gap:1.75rem;padding:0 1.5rem 3rem;max-width:1280px;margin:0 auto}
.ts-card{--tc:var(--ts-plain);position:relative;border-radius:1.5rem;overflow:hidden;background:var(--surface);border:1px solid color-mix(in srgb,var(--tc) 24%,var(--border));transition:transform .3s cubic-bezier(.34,1.56,.64,1),box-shadow .3s;box-shadow:inset 0 1px 0 rgba(255,255,255,.04),0 18px 46px rgba(0,0,0,.10)}
.ts-card:hover{transform:translateY(-8px);box-shadow:inset 0 1px 0 rgba(255,255,255,.06),0 26px 70px rgba(0,0,0,.18),0 0 0 1px color-mix(in srgb,var(--tc) 52%,transparent)}
.ts-card--premium::before,.ts-card--vip::before,.ts-card--admin-gold::before,.ts-card--admin-royal::before,.ts-card--admin-phoenix::before{content:'';position:absolute;inset:0;border-radius:inherit;padding:1px;background:linear-gradient(135deg,color-mix(in srgb,var(--tc) 96%,#fff) 0%,color-mix(in srgb,var(--tc) 42%,transparent) 22%,rgba(255,255,255,.08) 52%,color-mix(in srgb,var(--tc) 70%,transparent) 100%);background-size:200% 100%;-webkit-mask:linear-gradient(#fff 0 0) content-box,linear-gradient(#fff 0 0);-webkit-mask-composite:xor;mask-composite:exclude;animation:ts-bf 4s linear infinite;pointer-events:none;z-index:1}
@keyframes ts-bf{to{background-position:200% 0}}
.ts-card-head{position:relative;padding:1.75rem 1.5rem 1.25rem;text-align:center;color:#fff;overflow:hidden;background:linear-gradient(140deg,var(--tc),color-mix(in srgb,var(--tc) 55%,#000))}
.ts-card-head::after{content:'';position:absolute;top:-50%;left:-50%;width:200%;height:200%;background:linear-gradient(115deg,transparent 40%,rgba(255,255,255,.18) 50%,transparent 60%);background-size:200% 100%;animation:ts-sh 5s ease-in-out infinite;pointer-events:none}
@keyframes ts-sh{0%,100%{transform:translateX(-30%)}50%{transform:translateX(30%)}}
.ts-card--plain .ts-card-head{background:linear-gradient(140deg,#94a3b8,#475569)}
.ts-card--supporter{--tc:var(--ts-supporter)}.ts-card--premium{--tc:var(--ts-premium)}.ts-card--vip{--tc:var(--ts-vip)}.ts-card--admin-gold{--tc:var(--ts-gold)}.ts-card--admin-royal{--tc:var(--ts-royal)}.ts-card--admin-phoenix{--tc:var(--ts-phoenix)}
.ts-head-inner{position:relative;z-index:2}
.ts-icon-wrap{width:64px;height:64px;margin:0 auto .6rem;border-radius:50%;background:rgba(255,255,255,.2);backdrop-filter:blur(6px);border:2px solid rgba(255,255,255,.35);display:flex;align-items:center;justify-content:center}
.ts-icon-wrap i{font-size:1.6rem;filter:drop-shadow(0 2px 6px rgba(0,0,0,.3))}
.ts-title{font-size:1.5rem;font-weight:800}
.ts-tag{display:inline-block;font-size:.62rem;font-weight:800;text-transform:uppercase;letter-spacing:.1em;padding:.22rem .75rem;border-radius:999px;margin-top:.5rem;background:rgba(255,255,255,.9);color:var(--tc)}
.ts-tag--free{background:rgba(255,255,255,.95);color:#15803d}.ts-tag--admin{background:#fff;color:#b91c1c}
.ts-card-body{padding:1.5rem;position:relative;z-index:2}
.ts-meta{display:flex;justify-content:space-between;align-items:flex-end;margin-bottom:1.1rem;padding-bottom:1rem;border-bottom:1px dashed var(--border-soft)}
.ts-price{font-size:1.4rem;font-weight:900;color:var(--text);line-height:1}
.ts-price small{display:block;font-size:.68rem;color:var(--muted);font-weight:500;margin-top:.2rem}
.ts-lock-badge{font-size:.7rem;font-weight:800;display:inline-flex;align-items:center;gap:.3rem;padding:.3rem .7rem;border-radius:999px}
.ts-lock-badge--active{background:color-mix(in srgb,var(--tc) 15%,transparent);color:var(--tc)}
.ts-lock-badge--open{background:rgba(34,197,94,.15);color:#16a34a}
.ts-lock-badge--locked{background:rgba(148,163,184,.15);color:#94a3b8}
.ts-prev-label{font-size:.62rem;font-weight:800;color:var(--muted);text-transform:uppercase;letter-spacing:.08em;margin-bottom:.45rem;display:flex;align-items:center;gap:.3rem}
.ts-prev-profile{display:flex;align-items:center;gap:.7rem;padding:.75rem;border-radius:.75rem;background:color-mix(in srgb,var(--tc) 6%,var(--surface));border:1px solid color-mix(in srgb,var(--tc) 22%,transparent);margin-bottom:.6rem}
.ts-prev-av{width:42px;height:42px;border-radius:50%;background:linear-gradient(135deg,var(--tc),color-mix(in srgb,var(--tc) 60%,#000));color:#fff;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:1rem;flex-shrink:0}
.ts-prev-name{font-weight:800;font-size:.95rem;color:var(--tc);display:flex;align-items:center;gap:.35rem;flex-wrap:wrap;line-height:1.3}
.ts-prev-name-badge{display:inline-flex;align-items:center;gap:.2rem;font-size:.58rem;font-weight:800;padding:.1rem .5rem;border-radius:999px;background:color-mix(in srgb,var(--tc) 15%,transparent);color:var(--tc);text-transform:uppercase}
.ts-prev-chat{display:flex;align-items:flex-start;gap:.5rem;padding:.6rem .75rem;border-radius:.6rem;background:var(--surface);border-left:3px solid var(--tc);margin-bottom:.6rem}
.ts-prev-chat-av{width:26px;height:26px;border-radius:50%;background:var(--tc);color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.65rem;flex-shrink:0}
.ts-prev-chat-name{font-size:.75rem;font-weight:700;color:var(--tc)}
.ts-prev-chat-text{font-size:.68rem;color:var(--muted);margin-top:1px}
.ts-prev-comment{padding:.6rem .75rem;border-radius:.6rem;background:linear-gradient(135deg,color-mix(in srgb,var(--tc) 8%,var(--surface)) 0%,var(--surface) 60%);border-left:3px solid var(--tc);margin-bottom:.6rem}
.ts-prev-comment-name{font-size:.72rem;font-weight:700;color:var(--tc);margin-bottom:.2rem}
.ts-prev-comment-text{font-size:.68rem;color:var(--text);opacity:.85;line-height:1.4}
.ts-features{list-style:none;padding:0;margin:.5rem 0 1rem}
.ts-features li{display:flex;align-items:flex-start;gap:.45rem;font-size:.75rem;color:var(--text);padding:.3rem 0;line-height:1.4}
.ts-features li i{color:var(--tc);font-size:.65rem;margin-top:4px;flex-shrink:0}
.ts-features li.ts-feat-off{color:var(--muted);opacity:.6}
.ts-features li.ts-feat-off i{color:var(--muted)}
.ts-action{display:flex;align-items:center;justify-content:center;gap:.4rem;width:100%;padding:.75rem;border-radius:.75rem;font-weight:800;font-size:.85rem;text-decoration:none;transition:transform .15s,box-shadow .2s,opacity .2s}
.ts-action:hover{transform:translateY(-1px)}
.ts-action--primary{background:linear-gradient(135deg,var(--tc),color-mix(in srgb,var(--tc) 65%,#000));color:#fff;box-shadow:0 6px 18px color-mix(in srgb,var(--tc) 35%,transparent)}
.ts-action--current{background:color-mix(in srgb,var(--tc) 12%,transparent);color:var(--tc);border:1.5px solid var(--tc)}
.ts-action--locked{background:rgba(148,163,184,.15);color:#94a3b8;cursor:not-allowed;border:1.5px dashed rgba(148,163,184,.4)}
@media(max-width:640px){.ts-grid{grid-template-columns:1fr;padding:0 1rem 2rem}.ts-hero{padding:120px 1rem 3rem}}
</style>
@endpush

<section class="ts-hero">
  <div class="ts-hero-inner">
    <span class="ts-hero-badge"><i class="fa-solid fa-sparkles"></i> {{ __('public.donation.showcase_badge') }}</span>
    <h1>{{ __('public.donation.showcase_title') }}</h1>
    <p>{{ __('public.donation.showcase_subtitle') }}</p>
    <div class="ts-hero-cta">
      @auth
        <a href="{{ route('profile.show', ['panel' => 'appearance']) }}" class="ts-btn-glow"><i class="fa-solid fa-palette"></i> {{ __('public.donation.showcase_select_theme') }}</a>
      @else
        <a href="{{ route('login') }}" class="ts-btn-glow"><i class="fa-solid fa-right-to-bracket"></i> {{ __('public.common.login') }}</a>
      @endauth
      <a href="{{ route('donation.index') }}" class="ts-btn-glass"><i class="fa-solid fa-star"></i> {{ __('public.donation.showcase_donate') }}</a>
    </div>
  </div>
</section>

<div class="ts-section-head">
  <h2>{{ __('public.donation.showcase_section_title') }}</h2>
  <p>{{ __('public.donation.showcase_section_text') }}</p>
</div>

<div class="ts-grid">
  @php
    $allFeats = [
      'plain'        => [[true,'showcase_feat_plain.0'],[false,'showcase_feat_plain.1'],[false,'showcase_feat_plain.2'],[false,'showcase_feat_plain.3']],
      'supporter'    => [[true,'showcase_feat_supporter.0'],[true,'showcase_feat_supporter.1'],[true,'showcase_feat_supporter.2'],[true,'showcase_feat_supporter.3']],
      'premium'      => [[true,'showcase_feat_premium.0'],[true,'showcase_feat_premium.1'],[true,'showcase_feat_premium.2'],[true,'showcase_feat_premium.3']],
      'vip'          => [[true,'showcase_feat_vip.0'],[true,'showcase_feat_vip.1'],[true,'showcase_feat_vip.2'],[true,'showcase_feat_vip.3']],
      'admin-gold'   => [[true,'showcase_feat_admin.0'],[true,'showcase_feat_admin.1'],[true,'showcase_feat_admin.2'],[false,'']],
      'admin-royal'  => [[true,'showcase_feat_admin.0'],[true,'showcase_feat_admin.1'],[true,'showcase_feat_admin.2'],[false,'']],
      'admin-phoenix'=> [[true,'showcase_feat_admin.0'],[true,'showcase_feat_admin.1'],[true,'showcase_feat_admin.2'],[false,'']],
    ];
    $prices = [
      'plain'        => ['amount'=>__('public.donation.showcase_price_free'),'note'=>__('public.donation.showcase_price_free_note')],
      'supporter'    => ['amount'=>\App\Models\Donation::priceLabel('supporter'),'note'=>''],
      'premium'      => ['amount'=>\App\Models\Donation::priceLabel('premium'),'note'=>''],
      'vip'          => ['amount'=>\App\Models\Donation::priceLabel('vip'),'note'=>''],
      'admin-gold'   => ['amount'=>__('public.donation.showcase_price_special'),'note'=>__('public.donation.showcase_price_admin_note')],
      'admin-royal'  => ['amount'=>__('public.donation.showcase_price_special'),'note'=>__('public.donation.showcase_price_admin_note')],
      'admin-phoenix'=> ['amount'=>__('public.donation.showcase_price_special'),'note'=>__('public.donation.showcase_price_admin_note')],
    ];
    $sampleNames=['plain'=>'Aziz','supporter'=>'Jasur','premium'=>'Dilnoza','vip'=>'Sardor','admin-gold'=>'Admin','admin-royal'=>'Admin','admin-phoenix'=>'Admin'];
    $currentTheme=$currentUser?->profile_theme??$currentUser?->donation_rank;
  @endphp

  @foreach($themes as $key => $t)
    @php
      $color=$t['badge_color'];$icon=$t['badge_icon'];$label=$t['label'];$type=$t['type']??'donor';
      $sampleName=$sampleNames[$key]??'U';$initial=mb_substr($sampleName,0,1);
      $isActive=$currentUser&&$currentTheme===$key;$allowed=$themeAllowed[$key]??false;
      $isAdminTheme=$type==='admin';$isPlain=$type==='plain';
      $price=$prices[$key]??['amount'=>'—','note'=>''];
      $feats=$allFeats[$key]??[];
      $tagClass=$isAdminTheme?'ts-tag--admin':($isPlain?'ts-tag--free':'');
      $tagText=$isAdminTheme?__('public.donation.showcase_tag_admin'):($isPlain?__('public.donation.showcase_tag_free'):__('public.donation.showcase_tag_donor'));
    @endphp
    <div class="ts-card ts-card--{{ $key }}" style="--tc:{{ $color }};">
      <div class="ts-card-head">
        <div class="ts-head-inner">
          <div class="ts-icon-wrap"><i class="{{ $icon }}"></i></div>
          <div class="ts-title">{{ $label }}</div>
          <span class="ts-tag {{ $tagClass }}">{{ $tagText }}</span>
        </div>
      </div>
      <div class="ts-card-body">
        <div class="ts-meta">
          <div class="ts-price">{{ $price['amount'] }}<small>{{ $price['note'] }}</small></div>
          @if($isActive)
            <span class="ts-lock-badge ts-lock-badge--active"><i class="fa-solid fa-check-circle"></i> {{ __('public.donation.showcase_yours') }}</span>
          @elseif($allowed)
            <span class="ts-lock-badge ts-lock-badge--open"><i class="fa-solid fa-circle-check"></i> {{ __('public.donation.showcase_open') }}</span>
          @else
            <span class="ts-lock-badge ts-lock-badge--locked"><i class="fa-solid fa-lock"></i> {{ __('public.donation.showcase_locked') }}</span>
          @endif
        </div>

        <div class="ts-prev-label"><i class="fa-solid fa-id-card"></i> {{ __('public.donation.showcase_profile_preview') }}</div>
        <div class="ts-prev-profile">
          <div class="ts-prev-av">{{ $initial }}</div>
          <div class="ts-prev-name">{{ $sampleName }}<span class="ts-prev-name-badge"><i class="{{ $icon }}"></i> {{ $label }}</span></div>
        </div>

        <div class="ts-prev-label"><i class="fa-solid fa-comment-dots"></i> {{ __('public.donation.showcase_chat_preview') }}</div>
        <div class="ts-prev-chat">
          <div class="ts-prev-chat-av">{{ $initial }}</div>
          <div>
            <div class="ts-prev-chat-name">{{ $sampleName }}</div>
            <div class="ts-prev-chat-text">Salom! 🔥</div>
          </div>
        </div>

        <div class="ts-prev-label"><i class="fa-solid fa-comments"></i> {{ __('public.donation.showcase_comment_preview') }}</div>
        <div class="ts-prev-comment">
          <div class="ts-prev-comment-name">{{ $sampleName }}</div>
          <div class="ts-prev-comment-text">Juda zo'r!</div>
        </div>

        <div class="ts-prev-label" style="margin-top:.8rem;"><i class="fa-solid fa-gift"></i> {{ __('public.donation.showcase_features') }}</div>
        <ul class="ts-features">
          @foreach($feats as [$check, $langKey])
            <li class="{{ !$check ? 'ts-feat-off' : '' }}">
              <i class="fa-solid {{ $check ? 'fa-check' : 'fa-xmark' }}"></i>
              {{ $langKey ? __('public.donation.'.$langKey) : '—' }}
            </li>
          @endforeach
        </ul>

        @if($isActive)
          <a href="{{ route('profile.show', ['panel'=>'appearance']) }}" class="ts-action ts-action--current"><i class="fa-solid fa-check"></i> {{ __('public.donation.showcase_current') }}</a>
        @elseif($allowed)
          <a href="{{ route('profile.show', ['panel'=>'appearance']) }}" class="ts-action ts-action--primary"><i class="fa-solid fa-palette"></i> {{ __('public.donation.showcase_select') }}</a>
        @elseif($isPlain)
          <a href="{{ route('profile.show', ['panel'=>'appearance']) }}" class="ts-action ts-action--primary"><i class="fa-solid fa-user"></i> {{ __('public.donation.showcase_free_select') }}</a>
        @elseif($isAdminTheme)
          <span class="ts-action ts-action--locked"><i class="fa-solid fa-crown"></i> {{ __('public.donation.showcase_admin_only') }}</span>
        @else
          <a href="{{ route('donation.checkout', $key) }}" class="ts-action ts-action--primary"><i class="fa-solid fa-bolt"></i> {{ __('public.donation.showcase_buy', ['label'=>$label]) }}</a>
        @endif
      </div>
    </div>
  @endforeach
</div>

</x-loyouts.main>
