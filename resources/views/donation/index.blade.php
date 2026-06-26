<x-loyouts.main title="Donation — 81-IDUM">

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
    border-radius: 1.5rem;
    padding: 2rem;
    text-align: center;
    transition: transform 0.3s, box-shadow 0.3s, background 0.3s, border-color 0.3s;
    background: var(--surface);
    color: var(--text);
    box-sizing: border-box;
    position: relative;
}
.rank-card::after {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 4px;
    background: var(--card-color, #6366f1);
    border-radius: 1.5rem 1.5rem 0 0;
    pointer-events: none;
    z-index: 2;
}
.rank-card::before {
    content: '';
    position: absolute;
    inset: 0;
    border-radius: inherit;
    border: 2px solid var(--border);
    pointer-events: none;
    z-index: 0;
}
.rank-card > * {
    position: relative;
    z-index: 1;
}
.rank-card:hover { transform: translateY(-8px); box-shadow: 0 14px 40px rgba(13, 63, 120, 0.11); }
.rank-card .rank-icon { font-size: 3rem; margin-bottom: 0.25rem; }
.rank-card .rank-label { font-size: 1.5rem; font-weight: 700; }
.rank-card .price-table { display: flex; flex-direction: column; gap: 0.5rem; margin: 1rem 0; }
.price-row { display: flex; justify-content: space-between; align-items: center; padding: 0.6rem 1rem; border-radius: 0.75rem; background: var(--bg); border: 1px solid var(--border); cursor: pointer; transition: all 0.2s; }
.price-row:hover { border-color: var(--primary-2); }
.price-row.popular { border-color: #f59e0b; background: #fef3c7; }
.price-row .dur { font-weight: 600; font-size: 0.9rem; }
.price-row .dur small { font-weight: 400; color: var(--muted); font-size: 0.8rem; }
.price-row .amt { font-weight: 700; font-size: 1rem; }
.price-row .discount-badge { background: #22c55e20; color: #22c55e; padding: 0.15rem 0.5rem; border-radius: 9999px; font-size: 0.7rem; font-weight: 700; margin-left: 0.5rem; }
.price-row .old-price { text-decoration: line-through; color: var(--muted); font-size: 0.8rem; margin-right: 0.25rem; }
.rank-card .features { list-style: none; padding: 0; margin: 1rem 0; text-align: left; }
.rank-card .features li { padding: 0.5rem 0; border-bottom: 1px solid var(--border); display: flex; align-items: center; gap: 0.75rem; font-size: 0.85rem; }
.rank-card .features li i { width: 1.2rem; font-size: 0.8rem; }
.rank-card .features li .no { opacity: 0.35; }
.btn-select { display: block; padding: 0.75rem; border-radius: 9999px; font-weight: 700; text-decoration: none; transition: all 0.3s; width: 100%; border: none; cursor: pointer; font-size: 0.95rem; text-align: center; color: #fff; }
.btn-select:hover { opacity: 0.9; transform: scale(1.01); }
.top-donors { margin-top: 3rem; padding: 1.5rem; background: var(--surface); border: 1px solid var(--border); border-radius: 1rem; }
.top-donors h3 { margin-bottom: 1rem; color: var(--text); }
.donor-item { display: flex; align-items: center; gap: 0.75rem; padding: 0.4rem 0; color: var(--text); font-size: 0.9rem; }
.donor-item .pos { font-weight: 700; color: var(--muted); min-width: 2rem; }
.donor-item .amt { margin-left:auto; color: var(--muted); font-size:0.85rem; }
.telegram-section { max-width: 1100px; margin: 2rem auto; padding: 2rem; background: var(--surface); border: 1px solid var(--border); border-radius: 1.5rem; text-align: center; }
.telegram-section h3 { color: var(--text); }
.telegram-section p { color: var(--muted); }
.telegram-section .btn-tg { display:inline-flex; align-items:center; gap:0.5rem; padding:0.7rem 2rem; background:#1e96e1; color:#fff; border-radius:9999px; text-decoration:none; font-weight:600; }
.telegram-section .btn-tg:hover { opacity:0.9; }

/* ===== DARK MODE ===== */
:root[data-theme='dark'] .price-row.popular {
    background: rgba(245, 158, 11, 0.12);
    border-color: rgba(245, 158, 11, 0.4);
}
:root[data-theme='dark'] .rank-card:hover {
    box-shadow: 0 14px 40px rgba(0, 0, 0, 0.35);
}

@media (max-width: 768px) { .rank-grid { grid-template-columns: 1fr !important; } }
</style>
@endpush

@php
    $durations = \App\Models\Donation::DURATIONS();
    $supporterFeatures = [
        ["check" => true, "text" => "Rangli kommentlar (kok)"],
        ["check" => true, "text" => "10 MB gacha avatar yuklash"],
        ["check" => true, "text" => "AI chat: 100 ta sorov/oy"],
        ["check" => true, "text" => "Profil yonida Supporter badge"],
        ["check" => false, "text" => "Maxsus profil dizayni"],
        ["check" => false, "text" => "Top donorlar royhati"],
    ];
    $premiumFeatures = [
        ["check" => true, "text" => "Rangli kommentlar (binafsha)"],
        ["check" => true, "text" => "25 MB gacha avatar yuklash"],
        ["check" => true, "text" => "AI chat: 300 ta sorov/oy"],
        ["check" => true, "text" => "Premium badge"],
        ["check" => true, "text" => "Maxsus profil dizayni"],
        ["check" => true, "text" => "Top donorlar royhati"],
    ];
    $vipFeatures = [
        ["check" => true, "text" => "Rangli kommentlar (oltin)"],
        ["check" => true, "text" => "50 MB gacha avatar yuklash"],
        ["check" => true, "text" => "AI chat: cheksiz sorovlar"],
        ["check" => true, "text" => "VIP badge"],
        ["check" => true, "text" => "Maxsus profil dizayni"],
        ["check" => true, "text" => "Top donorlar royhati"],
        ["check" => true, "text" => "Prioritet support"],
    ];
@endphp

<div class="donation-hero">
    <h1>81-IDUM ni qollab-quvvatlang</h1>
    <p>Sizning donatlaringiz maktab saytini yanada yaxshilash va server xarajatlarini qoplash uchun ishlatiladi.</p>
</div>

<div class="container" style="max-width: 1100px; padding: 0 1rem 3rem;">
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
                <div class="rank-icon" style="color:{{ $color }};"><i class="{{ $iconClass }}"></i></div>
                <div class="rank-label" style="color:{{ $color }};">{{ $label }}</div>
                <div style="font-size:0.85rem; color:#5e7088; margin-bottom:0.5rem;">{{ number_format($basePrice, 0, ".", " ") }} som/oy</div>

                <div class="price-table">
                    @foreach($durations as $durKey => $durCfg)
                        @php
                            $totalPrice = \App\Models\Donation::priceForDuration($key, $durKey);
                            $discount = \App\Models\Donation::rankDiscount($key, $durKey);
                            $oldTotal = $basePrice * ($durCfg["days"] / 30);
                            $isPopular = $durKey === "3months";
                        @endphp
                        <div class="price-row {{ $isPopular ? "popular" : "" }}" onclick="document.getElementById('buy-{{ $key }}-{{ $durKey }}').click();">
                            <div>
                                <div class="dur">{{ $durCfg["label"] }} @if($discount > 0)<span class="discount-badge">-{{ $discount }}%</span>@endif</div>
                            </div>
                            <div>
                                @if($discount > 0)
                                    <span class="old-price">{{ number_format((int)$oldTotal, 0, ".", " ") }}</span>
                                @endif
                                <span class="amt">{{ number_format($totalPrice, 0, ".", " ") }} som</span>
                            </div>
                        </div>
                    @endforeach
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
                            <i class="fa-solid fa-check-circle"></i> Siz allaqachon {{ $label }} olingan
                        </div>
                    @elseif($hasBetter)
                        <div style="padding:0.6rem; border-radius:12px; background:#6366f115; border:1px solid #6366f130; color:#6366f1; font-weight:600; font-size:0.85rem; text-align:center;">
                            <i class="fa-solid fa-arrow-up"></i> Sizda yuqoriroq rank mavjud
                        </div>
                    @else
                        <a href="{{ route("donation.checkout", $key) }}" class="btn-select" style="background: {{ $color }};">
                            <i class="{{ $iconClass }}"></i> {{ $label }}ga aylanish
                        </a>
                    @endif
                @endauth
                @guest
                    <a href="{{ route("login") }}" class="btn-select" style="background: #0d3f78;">Kirish</a>
                @endguest
            </div>
        @endforeach
    </div>

    <div class="top-donors">
        <h3>Top donorlar</h3>
        @forelse($topDonors as $index => $donor)
            <div class="donor-item">
                <span class="pos">#{{ $index + 1 }}</span>
                <img src="{{ $donor->avatar_url ?? app_public_asset("temp/img/default-avatar.png") }}" alt="" style="width:2.5rem; height:2.5rem; border-radius:50%; object-fit:cover;">
                <span>{{ $donor->name ?: $donor->buildNameFromParts() }}</span>
                {!! $donor->donorBadgeHtml() !!}
                <span class="amt">{{ number_format($donor->total_donated, 0, ".", " ") }} som</span>
            </div>
        @empty
            <p style="color:#5e7088;">Hali donorlar mavjud emas. Birinchi donor boling!</p>
        @endforelse
    </div>

    <div class="telegram-section">
        <div style="font-size: 2rem; margin-bottom: 0.5rem;">💬</div>
        <h3>Kod orqali aktivlashtirish</h3>
        <p>Agar siz Telegram orqali tolov qilgan bolsangiz, kodingizni kiriting:</p>
        <a href="{{ route("donation.activate.form") }}" class="btn-tg">
            <i class="fa-brands fa-telegram"></i> Kodni kiritish
        </a>
        <p style="font-size: 0.85rem; margin-top: 0.75rem;">
            Sotib olish uchun: <a href="https://t.me/NgLord_404" target="_blank" style="color:#0d3f78;">@NgLord_404</a>
        </p>
    </div>
</div>

</x-loyouts.main>