<x-layouts.main title="Sotib olish — 81-IDUM">

@push("page_styles")
<style>
.checkout-wrapper { max-width: 480px; margin: 0 auto; padding: 100px 1rem 2rem; }
.checkout-card { background: var(--surface); border: 1px solid var(--border); border-radius: 1.5rem; padding: 2.5rem 2rem; box-shadow: var(--shadow); text-align: center; }
.checkout-card .icon { font-size: 4rem; margin-bottom: 0.5rem; }
.checkout-card h2 { color: var(--text); margin-bottom: 0.25rem; }
.checkout-card .price-text { font-size: 2rem; font-weight: 800; color: var(--text); margin-bottom: 1.5rem; }
.checkout-card .price-text small { font-size: 0.9rem; font-weight: 400; color: var(--muted); }
.steps { text-align: left; background: var(--bg); border-radius: 1rem; padding: 1.25rem 1.5rem; margin: 1.5rem 0; }
.steps h4 { color: var(--text); margin-bottom: 0.75rem; font-size: 0.95rem; }
.steps ol { padding-left: 1.25rem; margin: 0; color: var(--text); }
.steps ol li { padding: 0.4rem 0; font-size: 0.9rem; line-height: 1.5; }
.steps ol li span { color: var(--muted); }
.btn-tg { display:inline-flex; align-items:center; justify-content:center; gap:0.5rem; width:100%; padding:1rem; background:#1e96e1; color:#fff; border-radius:9999px; text-decoration:none; font-weight:700; font-size:1.05rem; transition:all 0.3s; border:none; cursor:pointer; }
.btn-tg:hover { opacity:0.9; transform:scale(1.01); }
.btn-code { display:inline-flex; align-items:center; justify-content:center; gap:0.5rem; width:100%; padding:1rem; background:var(--primary); color:#fff; border-radius:9999px; text-decoration:none; font-weight:700; font-size:1.05rem; transition:all 0.3s; margin-top:0.75rem; border:none; cursor:pointer; }
.btn-code:hover { opacity:0.9; transform:scale(1.01); }
.divider { display:flex; align-items:center; gap:1rem; margin:1.5rem 0; color:var(--muted); font-size:0.85rem; }
.divider hr { flex:1; border:none; border-top:1px solid var(--border); }
</style>
@endpush

<div class="checkout-wrapper">
    <div class="checkout-card">
        @php
            $color = $config["badge_color"];
            $iconClass = $config["badge_icon"];
            $label = $config["label"];
            $price = number_format($config["price"], 0, ".", " ");
        @endphp

        <div class="icon" style="color: {{ $color }};">
            <i class="{{ $iconClass }}"></i>
        </div>
        <h2>{{ $label }}</h2>
        <div class="price-text">{{ $price }} <small>som</small></div>

        <div class="steps">
            <h4>Qanday olish mumkin?</h4>
            <ol>
                <li><strong>1.</strong> <span>Telegram orqali @NgLord_404 ga yozing</span></li>
                <li><strong>2.</strong> <span>"{{ $label }}" rankini sotib olmoqchi ekanligingizni ayting</span></li>
                <li><strong>3.</strong> <span>Tolov qiling va 8 belgili kodni oling</span></li>
                <li><strong>4.</strong> <span>Kodni saytga kiriting va rankingiz aktivlashsin!</span></li>
            </ol>
        </div>

        <a href="https://t.me/NgLord_404" target="_blank" class="btn-tg">
            <i class="fa-brands fa-telegram"></i> @NgLord_404 ga yozish
        </a>

        <div class="divider"><hr><span>yoki</span><hr></div>

        <a href="{{ route("donation.activate.form") }}" class="btn-code">
            <i class="fa-solid fa-key"></i> Kodni kiritish
        </a>
    </div>
</div>

</x-loyouts.main>
