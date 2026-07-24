<x-layouts.main :title="__('public.donation.pending_title').' — 81-IDUM'">
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
    <h2>{{ __('public.donation.pending_title') }}</h2>
    <p>{{ __('public.donation.pending_text') }}</p>
    <a href="{{ route('donation.index') }}" style="display:inline-block; margin-top:1.5rem; color:var(--primary);">
        {{ __('public.donation.pending_back') }}
    </a>
</div>
</x-loyouts.main>
