@extends("admin.layouts.main", ["title" => "Donation sozlamalari"])
@section("content")
<style>
.page-head { margin-bottom: 2rem; }
.page-head h2 { font-size: 1.5rem; font-weight: 800; color: #1e293b; margin: 0 0 0.25rem; display: flex; align-items: center; gap: 0.5rem; }
.page-head p { color: #64748b; font-size: 0.9rem; margin: 0; }
.dark .page-head h2 { color: #e2e8f0; }
.dark .page-head p { color: #94a3b8; }

.rank-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(340px, 1fr)); gap: 1.25rem; margin-bottom: 2rem; }

.rank-card {
    position: relative;
    border-radius: 28px;
    padding: 1.75rem;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    overflow: hidden;
}
.dark .rank-card {
    background: rgba(30, 41, 59, 0.7);
    border-color: rgba(255, 255, 255, 0.08);
}
.rank-card:hover { transform: translateY(-4px); box-shadow: 0 20px 60px -12px rgba(0,0,0,0.12); }

.rank-header { display: flex; align-items: center; gap: 1rem; margin-bottom: 1.5rem; }
.rank-icon {
    width: 3rem; height: 3rem; border-radius: 16px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.4rem; color: #fff;
    flex-shrink: 0;
}
.rank-info { flex: 1; }
.rank-info h3 { font-size: 1.15rem; font-weight: 700; margin: 0 0 0.15rem; }
.rank-info .price-now { font-size: 0.8rem; color: #64748b; }
.dark .rank-info .price-now { color: #94a3b8; }
.rank-info .price-now strong { font-size: 1.1rem; font-weight: 800; color: #1e293b; }
.dark .rank-info .price-now strong { color: #e2e8f0; }

.fields { display: flex; flex-direction: column; gap: 1rem; }
.field-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
.field { }
.field label { display: block; font-size: 0.75rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 0.4rem; }
.field .inp { position: relative; }
.field .inp .badge {
    position: absolute; left: 12px; top: 50%; transform: translateY(-50%);
    font-size: 0.75rem; font-weight: 700; color: #64748b; z-index: 2;
}
.field input {
    width: 100%; padding: 0.7rem 1rem 0.7rem 2.8rem;
    border: 2px solid #e2e8f0; border-radius: 12px;
    background: #f8fafc; color: #1e293b;
    font-size: 0.95rem; font-weight: 700;
    transition: all 0.2s; outline: none;
    box-sizing: border-box;
}
.dark .field input {
    background: rgba(15, 23, 42, 0.6);
    border-color: rgba(255, 255, 255, 0.1);
    color: #e2e8f0;
}
.field input:focus { border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99,102,241,0.12); }
.field .hint { font-size: 0.7rem; color: #64748b; margin-top: 0.3rem; display: flex; align-items: center; gap: 0.3rem; }

.actions { display: flex; gap: 1rem; align-items: center; flex-wrap: wrap; padding: 1.5rem 0; }
.btn-save {
    padding: 0.8rem 2rem; background: #6366f1; color: #fff;
    border: none; border-radius: 14px; font-weight: 700; font-size: 0.9rem;
    cursor: pointer; display: inline-flex; align-items: center; gap: 0.5rem;
    transition: all 0.25s; text-decoration: none;
}
.btn-save:hover { opacity: 0.92; transform: translateY(-2px); box-shadow: 0 8px 25px rgba(99,102,241,0.3); }
.btn-link {
    padding: 0.8rem 2rem; background: transparent; color: #64748b;
    border: 2px solid #e2e8f0; border-radius: 14px; font-weight: 600; font-size: 0.9rem;
    display: inline-flex; align-items: center; gap: 0.5rem;
    transition: all 0.25s; text-decoration: none;
}
.dark .btn-link { border-color: rgba(255,255,255,0.12); color: #94a3b8; }
.btn-link:hover { border-color: #6366f1; color: #6366f1; }

@media (max-width: 768px) {
    .rank-grid { grid-template-columns: 1fr; }
    .field-row { grid-template-columns: 1fr; }
}
</style>

<div style="max-width: 1100px; margin: 0 auto; padding: 1.5rem 0;">
    <div class="page-head">
        <h2><span style="color:#6366f1;">Donation</span> sozlamalari</h2>
        <p>Narxlar va chegirmalarni ozgartiring</p>
    </div>

    <form method="POST" action="{{ route("admin.donation-settings.update") }}">
        @csrf

        <div class="rank-grid">
            @php $ranks = [
                "supporter" => ["label" => "Supporter", "icon" => "mdi mdi-star", "color" => "#3b82f6"],
                "premium" => ["label" => "Premium", "icon" => "mdi mdi-diamond", "color" => "#8b5cf6"],
                "vip" => ["label" => "VIP", "icon" => "mdi mdi-crown", "color" => "#f59e0b"]
            ]; @endphp

            @foreach($ranks as $rank => $cfg)
                @php $priceVal = $settings["donation_{$rank}_price"] ?? "15000"; @endphp
                <div class="rank-card">
                    <div style="position:absolute; top:0; left:0; right:0; height:4px; background:{{ $cfg["color"] }}; border-radius:28px 28px 0 0;"></div>
                    <div class="rank-header">
                        <div class="rank-icon" style="background:{{ $cfg["color"] }};"><i class="{{ $cfg["icon"] }}"></i></div>
                        <div class="rank-info">
                            <h3 style="color:{{ $cfg["color"] }};">{{ $cfg["label"] }}</h3>
                            <div class="price-now">oyiga: <strong>{{ number_format((int)$priceVal, 0, ".", " ") }}</strong> som</div>
                        </div>
                    </div>

                    <div class="fields">
                        <div class="field">
                            <label>1 oylik narxi</label>
                            <div class="inp">
                                <span class="badge">som</span>
                                <input type="number" name="donation_{{ $rank }}_price" value="{{ $priceVal }}" min="1000" max="1000000" required>
                            </div>
                        </div>
                        <div class="field-row">
                            <div class="field">
                                <label>3 oylik chegirma</label>
                                <div class="inp">
                                    <span class="badge">%</span>
                                    <input type="number" name="donation_{{ $rank }}_discount_3months" value="{{ $settings["donation_{$rank}_discount_3months"] ?? "0" }}" min="0" max="50">
                                </div>
                                <div class="hint">3 oyga chegirma</div>
                            </div>
                            <div class="field">
                                <label>1 yillik chegirma</label>
                                <div class="inp">
                                    <span class="badge">%</span>
                                    <input type="number" name="donation_{{ $rank }}_discount_1year" value="{{ $settings["donation_{$rank}_discount_1year"] ?? "0" }}" min="0" max="50">
                                </div>
                                <div class="hint">1 yilga chegirma</div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="actions">
            <button type="submit" class="btn-save"><i class="mdi mdi-check"></i> Saqlash</button>
            <a href="{{ route("admin.activation-keys.index") }}" class="btn-link"><i class="mdi mdi-key-variant"></i> Kalitlar royhati</a>
        </div>
    </form>
</div>
@endsection