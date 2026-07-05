<x-layouts.main title="Kalit aktivlashtirish — 81-IDUM">

@push("page_styles")
<style>
.activate-card { max-width: 450px; margin: 0 auto; padding: 110px 2.5rem 2.5rem; background: var(--surface); border: 1px solid var(--border); border-radius: 1.5rem; box-shadow: var(--shadow); text-align: center; }
.activate-card h2 { color: var(--text); margin-bottom: 1rem; }
.activate-card p { color: var(--muted); margin-bottom: 2rem; }
.code-input { width: 100%; padding: 1rem; font-size: 1.5rem; font-family: monospace; letter-spacing: 4px; text-align: center; text-transform: uppercase; border: 2px solid var(--border); border-radius: 1rem; background: var(--bg); color: var(--text); outline: none; transition: border 0.2s; }
.code-input:focus { border-color: var(--primary); }
.btn-activate { width: 100%; padding: 1rem; border: none; border-radius: 9999px; font-size: 1.1rem; font-weight: 700; background: var(--primary); color: #fff; cursor: pointer; transition: all 0.3s; margin-top: 1rem; }
.btn-activate:hover { opacity: 0.9; }
.how-to { margin-top: 2rem; padding: 1.5rem; background: var(--bg); border-radius: 1rem; text-align: left; }
.how-to h4 { color: var(--text); margin-bottom: 0.75rem; }
.how-to ol { padding-left: 1.25rem; color: var(--muted); }
.how-to ol li { padding: 0.3rem 0; }
</style>
@endpush

<div class="activate-card">
    <div style="font-size: 3rem; margin-bottom: 1rem;">🔑</div>
    <h2>Aktivatsiya kodini kiriting</h2>
    <p>Telegram orqali sotib olgan kodingizni kiriting</p>

    <form method="POST" action="{{ route("donation.activate") }}">
        @csrf
        <input type="text" name="code" class="code-input" placeholder="XXXX-XXXX" maxlength="8" autocomplete="off" required>
        <button type="submit" class="btn-activate"><i class="fa-solid fa-check"></i> Aktivlashtirish</button>
    </form>

    <div class="how-to">
        <h4>Qanday olish mumkin?</h4>
        <ol>
            <li>Telegram orqali @NgLord_404 ga yozing</li>
            <li>Qaysi rank va necha oylik kerakligini ayting</li>
            <li>Tolovni amalga oshiring</li>
            <li>8 belgili kodni olasiz</li>
            <li>Yuqoridagi maydonga kodni kiriting</li>
            <li>Rankingiz aktivlashadi!</li>
        </ol>
        <a href="https://t.me/NgLord_404" target="_blank" style="display:inline-flex; align-items:center; gap:0.5rem; margin-top:1rem; padding:0.7rem 1.5rem; background:#1e96e1; color:#fff; border-radius:9999px; text-decoration:none; font-weight:600;">
            <i class="fa-brands fa-telegram"></i> @NgLord_404 ga yozish
        </a>
    </div>
</div>

</x-loyouts.main>
