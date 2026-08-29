<x-layouts.main title="Donation — 81-IDUM">

@push("page_styles")
<style>
.donation-hero {
    background: linear-gradient(135deg, #1e1b4b 0%, #312e81 50%, #4338ca 100%);
    padding: 130px 2rem 4rem;
    text-align: center;
    color: #fff;
    border-radius: 0 0 3rem 3rem;
    margin-bottom: 2rem;
}
.donation-hero h1 { font-size: 2.5rem; font-weight: 800; margin-bottom: 0.5rem; }
.donation-hero p { font-size: 1.1rem; opacity: 0.85; max-width: 600px; margin: 0 auto; }

.rank-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 1.5rem; }

.rank-card {
    border-radius: 1.25rem;
    padding: 2rem;
    text-align: center;
    transition: transform 0.3s, box-shadow 0.3s;
    background: var(--surface);
    color: var(--text);
    box-sizing: border-box;
    position: relative;
    border: 1px solid var(--border);
    overflow: hidden;
}
.rank-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 4px;
    background: var(--card-color, #6366f1);
}
.rank-card:hover { transform: translateY(-6px); box-shadow: 0 12px 32px rgba(0, 0, 0, 0.12); }

.rank-card .rank-icon { font-size: 2.5rem; margin-bottom: 0.5rem; color: var(--card-color); }
.rank-card .rank-label { font-size: 1.4rem; font-weight: 700; color: var(--card-color); margin-bottom: 0.25rem; }
.rank-card .price-base { font-size: 0.85rem; color: var(--muted); margin-bottom: 1rem; }

/* Duration selector */
.duration-selector {
    display: flex;
    gap: 0.5rem;
    margin-bottom: 1rem;
    justify-content: center;
}
.duration-btn {
    padding: 0.4rem 0.9rem;
    border-radius: 999px;
    border: 1px solid var(--border);
    background: transparent;
    color: var(--text);
    font-size: 0.8rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}
.duration-btn:hover { border-color: var(--card-color); }
.duration-btn.active {
    background: var(--card-color);
    color: #fff;
    border-color: var(--card-color);
}

/* Price display */
.price-display {
    background: var(--bg);
    border: 1px solid var(--border);
    border-radius: 0.875rem;
    padding: 1rem;
    margin-bottom: 1rem;
    min-height: 80px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}
.price-display .price-main {
    font-size: 1.5rem;
    font-weight: 800;
    color: var(--text);
}
.price-display .price-old {
    text-decoration: line-through;
    color: var(--muted);
    font-size: 0.85rem;
}
.price-display .discount-tag {
    display: inline-block;
    background: #22c55e20;
    color: #22c55e;
    padding: 0.2rem 0.6rem;
    border-radius: 999px;
    font-size: 0.7rem;
    font-weight: 700;
    margin-top: 0.25rem;
}

/* Features */
.rank-card .features { list-style: none; padding: 0; margin: 1rem 0; text-align: left; }
.rank-card .features li {
    padding: 0.5rem 0;
    border-bottom: 1px solid var(--border);
    display: flex;
    align-items: center;
    gap: 0.75rem;
    font-size: 0.85rem;
}
.rank-card .features li:last-child { border-bottom: none; }
.rank-card .features li i { width: 1.2rem; font-size: 0.8rem; }
.rank-card .features li .no { opacity: 0.35; }

/* Button */
.btn-select {
    display: block;
    padding: 0.75rem;
    border-radius: 9999px;
    font-weight: 700;
    text-decoration: none;
    transition: all 0.3s;
    width: 100%;
    border: none;
    cursor: pointer;
    font-size: 0.95rem;
    text-align: center;
    color: #fff;
}
.btn-select:hover { opacity: 0.9; transform: scale(1.02); }

/* Top donors */
.top-donors { margin-top: 3rem; padding: 1.5rem; background: var(--surface); border: 1px solid var(--border); border-radius: 1rem; }
.top-donors h3 { margin-bottom: 1rem; color: var(--text); display: flex; align-items: center; gap: 0.5rem; }
.top-donors h3 i { color: #f59e0b; }
.donor-item { 
    display: flex; 
    align-items: center; 
    gap: 1rem; 
    padding: 0.75rem 1rem; 
    color: var(--text); 
    font-size: 0.9rem;
    background: var(--bg);
    border: 1px solid var(--border);
    border-radius: 0.75rem;
    margin-bottom: 0.5rem;
    transition: transform 0.2s, box-shadow 0.2s;
}
.donor-item:hover { transform: translateX(4px); box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
.donor-item.top-1 { border-color: #f59e0b; background: linear-gradient(135deg, #fef3c7 0%, var(--surface) 100%); }
.donor-item.top-1 .donor-meta { color: #92400e; }
.donor-item.top-1 .donor-amount small { color: #92400e; }
.donor-item.top-2 { border-color: #94a3b8; background: linear-gradient(135deg, #f1f5f9 0%, var(--surface) 100%); }
.donor-item.top-3 { border-color: #cd7f32; background: linear-gradient(135deg, #fef2e2 0%, var(--surface) 100%); }
:root[data-theme='dark'] .donor-item.top-1 .donor-meta { color: #fbbf24; }
:root[data-theme='dark'] .donor-item.top-1 .donor-amount small { color: #fbbf24; }
:root[data-theme='dark'] .donor-item.top-1 { background: linear-gradient(135deg, rgba(245,158,11,0.15) 0%, rgba(245,158,11,0.05) 100%); }
:root[data-theme='dark'] .donor-item.top-2 { background: linear-gradient(135deg, rgba(148,163,184,0.15) 0%, rgba(148,163,184,0.05) 100%); }
:root[data-theme='dark'] .donor-item.top-3 { background: linear-gradient(135deg, rgba(205,127,50,0.15) 0%, rgba(205,127,50,0.05) 100%); }
.donor-position {
    font-weight: 800;
    font-size: 1rem;
    min-width: 2rem;
    text-align: center;
}
.donor-position.gold { color: #f59e0b; }
.donor-position.silver { color: #94a3b8; }
.donor-position.bronze { color: #cd7f32; }
.donor-avatar {
    width: 2.75rem;
    height: 2.75rem;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid var(--border);
    flex-shrink: 0;
}
.donor-avatar-initial {
    width: 2.75rem;
    height: 2.75rem;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 1.1rem;
    color: #fff;
    border: 2px solid var(--border);
    flex-shrink: 0;
    text-transform: uppercase;
}
.donor-info { flex: 1; }
.donor-name { font-weight: 600; color: var(--text); }
.donor-meta { font-size: 0.75rem; color: var(--muted); margin-top: 0.15rem; }
.donor-amount {
    font-weight: 700;
    font-size: 1rem;
    color: var(--text);
    text-align: right;
}
.donor-amount small { display: block; font-size: 0.7rem; color: var(--muted); font-weight: 400; }

/* Telegram */
.telegram-section { max-width: 1100px; margin: 2rem auto; padding: 2rem; background: var(--surface); border: 1px solid var(--border); border-radius: 1.5rem; text-align: center; }
.telegram-section h3 { color: var(--text); }
.telegram-section p { color: var(--muted); }
.telegram-section .btn-tg { display:inline-flex; align-items:center; gap:0.5rem; padding:0.7rem 2rem; background:#1e96e1; color:#fff; border-radius:9999px; text-decoration:none; font-weight:600; }
.telegram-section .btn-tg:hover { opacity:0.9; }

/* Dark mode */
:root[data-theme='dark'] .rank-card:hover {
    box-shadow: 0 12px 32px rgba(0, 0, 0, 0.35);
}

@media (max-width: 768px) { .rank-grid { grid-template-columns: 1fr !important; } }
</style>
@endpush

@php
    $durations = \App\Models\Donation::DURATIONS();
    $supporterFeatures = [
        ["check" => true, "text" => "Rangli kommentlar (ko'k)"],
        ["check" => true, "text" => "10 MB gacha avatar yuklash"],
        ["check" => true, "text" => "AI chat: 100 ta so'rov/kun"],
        ["check" => true, "text" => "Profil yonida Supporter badge"],
        ["check" => true, "icon" => "fa-solid fa-face-smile", "text" => "Global chatda Telegram-style stikerlar"],
        ["check" => true, "icon" => "fa-solid fa-chalkboard-user", "text" => "O'qituvchilar: 2 ta kurs ochish"],
        ["check" => true, "text" => "Maxsus profil dizayni"],
        ["check" => false, "text" => "Sahifalar orasida tezkor o'tish"],
        ["check" => true, "icon" => "fa-solid fa-file-csv", "text" => "Imtihon natijalarini CSV export qilish"],
        ["check" => true, "icon" => "fa-solid fa-family", "text" => "Ota-ona: 3 tagacha farzandni bog'lash"],
    ];
    $premiumFeatures = [
        ["check" => true, "text" => "Rangli kommentlar (binafsha)"],
        ["check" => true, "text" => "25 MB gacha avatar yuklash"],
        ["check" => true, "text" => "AI chat: 300 ta so'rov/kun"],
        ["check" => true, "text" => "Premium badge"],
        ["check" => true, "icon" => "fa-solid fa-face-smile", "text" => "Global chatda Telegram-style stikerlar"],
        ["check" => true, "icon" => "fa-solid fa-bolt", "text" => "Sahifalar orasida tezkor o'tish"],
        ["check" => true, "text" => "Maxsus profil dizayni"],
        ["check" => true, "text" => __('public.donation.index_top_donors') . ' ro\'yhati'],
        ["check" => true, "icon" => "fa-solid fa-chalkboard-user", "text" => "O'qituvchilar: 3 ta kurs ochish"],
        ["check" => true, "icon" => "fa-solid fa-file-csv", "text" => "Imtihon natijalarini CSV export qilish"],
        ["check" => true, "icon" => "fa-solid fa-family", "text" => "Ota-ona: 4 tagacha farzandni bog'lash"],
    ];
    $vipFeatures = [
        ["check" => true, "text" => "Rangli kommentlar (oltin)"],
        ["check" => true, "text" => "50 MB gacha avatar yuklash"],
        ["check" => true, "text" => "AI chat: cheksiz so'rovlar"],
        ["check" => true, "text" => "VIP badge"],
        ["check" => true, "icon" => "fa-solid fa-face-smile", "text" => "Global chatda Telegram-style stikerlar"],
        ["check" => true, "icon" => "fa-solid fa-bolt", "text" => "Sahifalar orasida tezkor o'tish"],
        ["check" => true, "text" => "Maxsus profil dizayni"],
        ["check" => true, "text" => __('public.donation.index_top_donors') . ' ro\'yhati'],
        ["check" => true, "text" => "Prioritet support"],
        ["check" => true, "icon" => "fa-solid fa-chalkboard-user", "text" => "O'qituvchilar: 3 ta kurs ochish"],
        ["check" => true, "icon" => "fa-solid fa-file-csv", "text" => "Imtihon natijalarini CSV export qilish"],
        ["check" => true, "icon" => "fa-solid fa-family", "text" => "Ota-ona: 5 tagacha farzandni bog'lash"],
    ];
    
    // Pre-calculate prices for all durations
    $rankPrices = [];
    foreach($ranks as $key => $config) {
        $rankPrices[$key] = [];
        foreach($durations as $durKey => $durCfg) {
            $rankPrices[$key][$durKey] = [
                'price' => \App\Models\Donation::priceForDuration($key, $durKey),
                'discount' => \App\Models\Donation::rankDiscount($key, $durKey),
                'old_price' => $config['price'] * ($durCfg['days'] / 30),
                'label' => $durCfg['label'],
            ];
        }
    }
@endphp

<div class="donation-hero">
    <h1>{{ __('public.donation.index_hero_title') }}</h1>
    <p>{{ __('public.donation.index_hero_text') }}</p>
    <div style="margin-top: 1.25rem;">
        <a href="{{ route('donation.themes') }}" style="display:inline-flex; align-items:center; gap:0.4rem; padding:0.6rem 1.2rem; background:rgba(255,255,255,0.15); color:#fff; border-radius:999px; font-weight:700; font-size:0.9rem; text-decoration:none; backdrop-filter:blur(6px); transition:background 0.2s;">
            <i class="fa-solid fa-palette"></i> {{ __('public.donation.showcase_section_title') }}
        </a>
    </div>
</div>

<div class="container" style="max-width: 1100px; padding: 0 1rem 3rem;">
    
    {{-- Ota-ona bog'lash limiti haqida info --}}
    <div style="background: rgba(99,102,241,0.08); border: 1px solid rgba(99,102,241,0.2); border-radius: 12px; padding: 16px 20px; margin-bottom: 24px; display: flex; align-items: flex-start; gap: 12px;">
        <i class="fa-solid fa-circle-info" style="color: #818cf8; font-size: 18px; margin-top: 2px;"></i>
        <div>
            <div style="font-weight: 700; font-size: 14px; color: #e2e8f0; margin-bottom: 4px;">👨‍👩‍👧 Ota-ona: farzand bog'lash limiti</div>
            <div style="font-size: 13px; color: #94a3b8; line-height: 1.6;">
                Oddiy foydalanuvchilar <b style="color:#e2e8f0;">2 ta</b> farzand bog'lay oladi.
                Donor darajasi qancha yuqori bo'lsa — shuncha ko'p farzand bog'lash mumkin:
                <span style="color:#60a5fa;">Supporter → 3 ta</span>,
                <span style="color:#a78bfa;">Premium → 4 ta</span>,
                <span style="color:#fbbf24;">VIP → 5 ta</span>.
            </div>
        </div>
    </div>

    <div class="rank-grid">
        @foreach($ranks as $key => $config)
            @php
                $color = $config["badge_color"];
                $iconClass = $config["badge_icon"];
                $label = $config["label"];
                $basePrice = $config["price"];
                $features = $key === "supporter" ? $supporterFeatures : ($key === "premium" ? $premiumFeatures : $vipFeatures);
            @endphp

            <div class="rank-card" style="--card-color: {{ $color }};">
                <div class="rank-icon"><i class="{{ $iconClass }}"></i></div>
                <div class="rank-label">{{ $label }}</div>
                <div class="price-base">{{ number_format($basePrice, 0, ".", " ") }} som/oy</div>

                <!-- Duration selector -->
                <div class="duration-selector" data-rank="{{ $key }}">
                    @foreach($durations as $durKey => $durCfg)
                        <button type="button" 
                                class="duration-btn {{ $durKey === '1month' ? 'active' : '' }}"
                                data-dur="{{ $durKey }}"
                                onclick="switchDuration('{{ $key }}', '{{ $durKey }}')">
                            {{ $durCfg['label'] }}
                        </button>
                    @endforeach
                </div>

                <!-- Price display -->
                <div class="price-display" id="price-{{ $key }}" data-prices='@json($rankPrices[$key])'>
                    <div class="price-old" id="price-old-{{ $key }}"></div>
                    <div class="price-main" id="price-main-{{ $key }}">{{ number_format($rankPrices[$key]['1month']['price'], 0, ".", " ") }} som</div>
                    <div class="discount-tag" id="price-discount-{{ $key }}" style="display: none;"></div>
                </div>

                <ul class="features">
                    @foreach($features as $feat)
                        <li>
                            @if($feat["check"])
                                <i class="fa-solid fa-check" style="color:{{ $color }}"></i>
                            @else
                                <i class="fa-solid fa-xmark no"></i>
                            @endif
                            {{ $feat["text"] }}
                        </li>
                    @endforeach
                </ul>

                @auth
                    @php
                        $authUser = auth()->user();
                        $alreadyHas = $authUser->isDonor() && $authUser->donation_rank === $key;
                        $hasBetter = $authUser->isDonor() && \App\Models\Donation::configForRank($authUser->donation_rank)["priority"] > $config["priority"];
                    @endphp

                    @if($alreadyHas)
                        <div style="padding:0.6rem; border-radius:12px; background:#22c55e15; border:1px solid #22c55e30; color:#16a34a; font-weight:600; font-size:0.85rem; text-align:center;">
                            <i class="fa-solid fa-check-circle"></i> {{ __('public.donation.index_already_has', ['label' => $label]) }}
                        </div>
                    
                    @else
                        <a href="{{ route("donation.checkout", $key) }}" id="buy-btn-{{ $key }}" class="btn-select" style="background: {{ $color }};">
                            <i class="{{ $iconClass }}"></i> {{ __('public.donation.index_become', ['label' => $label]) }}
                        </a>
                    @endif
                @endauth
                @guest
                    <a href="{{ route('login') }}" class="btn-select" style="background: #0d3f78;">{{ __('public.common.login') }}</a>
                @endguest
            </div>
        @endforeach
    </div>

    <div class="top-donors">
        <h3><i class="fa-solid fa-trophy"></i> {{ __('public.donation.index_top_donors') }}</h3>
        @forelse($topDonors as $index => $donor)
            @php
                $posClass = '';
                $rankText = '';
                if ($index === 0) { $posClass = 'top-1'; $rankText = 'gold'; }
                elseif ($index === 1) { $posClass = 'top-2'; $rankText = 'silver'; }
                elseif ($index === 2) { $posClass = 'top-3'; $rankText = 'bronze'; }
                
                // Hisoblangan hissa miqdori — controller'da hisoblangan calculated_donated
                // Controller activation key donatlari uchun ham to'g'ri narxni hisoblaydi
                $totalAmount = $donor->calculated_donated ?? $donor->total_donated ?? 0;
                $donationCount = $donor->donation_count ?? 0;
            @endphp
            @php
                // Ismning birinchi harfi (initial) uchun
                $donorName = $donor->name ?: $donor->buildNameFromParts();
                $initial = strtoupper(mb_substr($donorName, 0, 1));
                // Rank rangiga qarab avatar fon rangi
                $avatarBg = $donor->donation_rank === 'vip' ? '#f59e0b' : ($donor->donation_rank === 'premium' ? '#8b5cf6' : '#3b82f6');
            @endphp
            <div class="donor-item {{ $posClass }}">
                <span class="donor-position {{ $rankText }}">#{{ $index + 1 }}</span>
                @if($donor->avatar_url)
                    <img src="{{ $donor->avatar_url }}" alt="" class="donor-avatar" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                    <div class="donor-avatar-initial" style="background: {{ $avatarBg }}; display: none;">{{ $initial }}</div>
                @else
                    <div class="donor-avatar-initial" style="background: {{ $avatarBg }};">{{ $initial }}</div>
                @endif
                <div class="donor-info">
                    <div class="donor-name">{{ $donor->name ?: $donor->buildNameFromParts() }}</div>
                    <div class="donor-meta">
                        {!! $donor->donorBadgeHtml() !!}
                        @if($donationCount > 0)
                            <span style="margin-left: 0.5rem;">{{ $donationCount }} ta donat</span>
                        @endif
                    </div>
                </div>
                <div class="donor-amount">
                    {{ number_format($totalAmount, 0, ".", " ") }} som
                    <small>Jami hissa</small>
                </div>
            </div>
        @empty
            <p style="color:#5e7088; text-align: center; padding: 2rem;">{{ __('public.donation.index_no_donors') }}</p>
        @endforelse
    </div>

    <div class="telegram-section">
        <div style="font-size: 2rem; margin-bottom: 0.5rem;">💬</div>
        <h3>{{ __('public.donation.index_tg_title') }}</h3>
        <p>{{ __('public.donation.index_tg_text') }}</p>
        <a href="{{ route("donation.activate.form") }}" class="btn-tg">
            <i class="fa-brands fa-telegram"></i> {{ __('public.donation.index_tg_btn') }}
        </a>
        <p style="font-size: 0.85rem; margin-top: 0.75rem;">
            @php
                $tgGeneralUrl = "https://t.me/NgLord_404?text=" . urlencode("Salom! Donor tariflari haqida savolim bor.");
            @endphp
            {{ __('public.donation.index_tg_contact') }} <a href="{{ $tgGeneralUrl }}" target="_blank" style="color:#0d3f78;">@NgLord_404</a>
        </p>
    </div>
</div>

@push('page_scripts')
<script>
function switchDuration(rank, dur) {
    // Update active button
    document.querySelectorAll(`[data-rank="${rank}"] .duration-btn`).forEach(btn => {
        btn.classList.toggle('active', btn.dataset.dur === dur);
    });
    
    // Get prices data
    const priceEl = document.getElementById(`price-${rank}`);
    const prices = JSON.parse(priceEl.dataset.prices);
    const priceData = prices[dur];
    
    // Update price display
    document.getElementById(`price-main-${rank}`).textContent = 
        new Intl.NumberFormat('uz-UZ').format(priceData.price) + ' som';
    
    // Update old price
    const oldPriceEl = document.getElementById(`price-old-${rank}`);
    if (priceData.discount > 0) {
        oldPriceEl.textContent = new Intl.NumberFormat('uz-UZ').format(priceData.old_price) + ' som';
        oldPriceEl.style.display = 'block';
    } else {
        oldPriceEl.style.display = 'none';
    }
    
    // Update discount tag
    const discountEl = document.getElementById(`price-discount-${rank}`);
    if (priceData.discount > 0) {
        discountEl.textContent = `-${priceData.discount}% chegrima`;
        discountEl.style.display = 'inline-block';
    } else {
        discountEl.style.display = 'none';
    }
    
    // Update buy button link
    const buyBtn = document.getElementById(`buy-btn-${rank}`);
    if (buyBtn) {
        const checkoutBase = @json(url('donation'));
        buyBtn.href = `${checkoutBase}/${rank}/checkout?duration=${dur}`;
    }
}
</script>
@endpush

</x-layouts.main>
