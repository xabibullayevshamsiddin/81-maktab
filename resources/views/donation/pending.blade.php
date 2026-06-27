<x-loyouts.main title="Tolov kutilmoqda — 81-IDUM">

@push("page_styles")
<style>
.pending-card { max-width: 450px; margin: 3rem auto; padding: 3rem 2rem; background: var(--surface); border: 1px solid var(--border); border-radius: 2rem; box-shadow: var(--shadow); text-align: center; }
.spinner { width: 60px; height: 60px; border: 4px solid var(--border); border-top-color: var(--primary); border-radius: 50%; animation: spin 1s linear infinite; margin: 0 auto 1rem; }
@keyframes spin { to { transform: rotate(360deg); } }
.pending-card h2 { color: var(--text); font-weight: 700; margin-bottom: 0.5rem; }
.pending-card p { color: var(--muted); }
</style>
@endpush

<div class="pending-card">
    <div class="spinner"></div>
    <h2>Tolov kutilmoqda</h2>
    <p>Tolovingiz qayta ishlanmoqda. <br> Bu bir necha daqiqa olishi mumkin.</p>
    <a href="{{ route("donation.index") }}" style="display:inline-block; margin-top:1.5rem; color:var(--primary);">Donation sahifasiga qaytish</a>
</div>

</x-loyouts.main>