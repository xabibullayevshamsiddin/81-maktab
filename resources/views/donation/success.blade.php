<x-loyouts.main title="Tolov muvaffaqiyatli — 81-IDUM">

@push("page_styles")
<style>
.success-card { max-width: 450px; margin: 3rem auto; padding: 3rem 2rem; background: var(--surface); border: 1px solid var(--border); border-radius: 2rem; box-shadow: var(--shadow); text-align: center; }
.success-card h2 { color: var(--text); font-size: 1.5rem; font-weight: 700; margin: 1rem 0 0.5rem; }
.success-card p { color: var(--muted); margin-bottom: 2rem; }
</style>
@endpush

<div class="success-card">
    @php $color = $config["badge_color"] ?? "#4338ca"; @endphp
    <div style="font-size: 4rem;">🎉</div>
    <h2>Tolov muvaffaqiyatli amalga oshirildi!</h2>
    <span style="display:inline-flex; align-items:center; gap:0.5rem; padding:0.5rem 1.5rem; border-radius:9999px; font-weight:700; font-size:1.1rem; background:{{$color}}20; color:{{$color}};">
        <i class="{{ $config["badge_icon"] ?? "" }}"></i> {{ $config["label"] ?? "" }}
    </span>
    <p style="margin-top:1rem;">
        Sizning {{ $config["label"] ?? "" }} rankingiz aktivlashtirildi! <br>
        Barcha imtiyozlardan foydalanishingiz mumkin.
    </p>
    <a href="{{ route("home") }}" style="display:inline-block; padding:0.8rem 2rem; border-radius:9999px; background:var(--primary); color:#fff; text-decoration:none; font-weight:600;">Bosh sahifaga qaytish</a>
</div>

</x-loyouts.main>