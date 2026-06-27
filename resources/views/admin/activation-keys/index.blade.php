@extends("admin.layouts.main", ["title" => "Aktivatsiya kalitlari"])
@section("content")

@php
    $totalRevenue = 0;
    $usedKeys = \App\Models\ActivationKey::where("is_used", true)->get();
    foreach ($usedKeys as $k) {
        $p = \App\Models\Donation::priceForDuration($k->rank, $k->duration);
        $totalRevenue += $p;
    }
    $activeNow = \App\Models\User::whereNotNull("donation_rank")
        ->where(function($q) { $q->whereNull("donation_rank_expires_at")->orWhere("donation_rank_expires_at", ">", now()); })
        ->count();
@endphp

<style>
* { box-sizing: border-box; }
.page-title { margin-bottom: 2rem; }
.page-title h2 { font-size: 1.5rem; font-weight: 800; color: #1e293b; margin: 0 0 0.25rem; display: flex; align-items: center; gap: 0.5rem; }
.page-title p { color: #64748b; font-size: 0.9rem; margin: 0; }

/* Stats */
.stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 1rem; margin-bottom: 1.5rem; }
.stat { background: #fff; border: 1px solid #e2e8f0; border-radius: 20px; padding: 1.25rem; text-align: center; }
.stat .num { font-size: 1.6rem; font-weight: 800; }
.stat .lbl { font-size: 0.78rem; color: #64748b; margin-top: 0.2rem; font-weight: 500; }

/* Revenue */
.rev { background: linear-gradient(135deg, #f0fdf4, #dcfce7); border: 1px solid #86efac; border-radius: 20px; padding: 1.25rem 1.5rem; margin-bottom: 1.5rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; }
.rev .big { font-size: 1.7rem; font-weight: 800; color: #16a34a; }
.rev .sm { font-size: 0.8rem; color: #64748b; }

/* Card */
.card { background: #fff; border: 1px solid #e2e8f0; border-radius: 24px; padding: 1.5rem; margin-bottom: 1.5rem; }
.card h3 { font-size: 1rem; font-weight: 700; color: #1e293b; margin: 0 0 1rem; padding-bottom: 0.75rem; border-bottom: 1px solid #f1f5f9; }

/* Form */
.form-row { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem; margin-bottom: 1rem; }
.form-group label { display: block; font-size: 0.75rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.35rem; }
.form-group select, .form-group input { width: 100%; padding: 0.65rem 0.75rem; border: 2px solid #e2e8f0; border-radius: 12px; background: #f8fafc; color: #1e293b; font-size: 0.9rem; font-weight: 600; outline: none; transition: border 0.2s; }
.form-group select:focus, .form-group input:focus { border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99,102,241,0.1); }
.btn-primary { padding: 0.65rem 1.5rem; background: #6366f1; color: #fff; border: none; border-radius: 12px; font-weight: 700; font-size: 0.85rem; cursor: pointer; display: inline-flex; align-items: center; gap: 0.4rem; transition: all 0.2s; }
.btn-primary:hover { opacity: 0.9; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(99,102,241,0.3); }

/* Codes */
.codes { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 14px; padding: 1rem; margin-top: 1rem; }
.codes .title { font-size: 0.8rem; font-weight: 700; color: #1e293b; margin-bottom: 0.5rem; }
.codes code { display: block; font-family: "Courier New", monospace; font-size: 1.1rem; letter-spacing: 2px; color: #6366f1; padding: 0.15rem 0; font-weight: 700; }
.codes .note { font-size: 0.75rem; color: #64748b; margin-top: 0.5rem; }

/* Table */
.table-wrap { overflow-x: auto; }
table { width: 100%; border-collapse: collapse; font-size: 0.88rem; }
table th { text-align: left; padding: 0.6rem 0.5rem; font-size: 0.72rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.04em; border-bottom: 2px solid #e2e8f0; }
table td { padding: 0.6rem 0.5rem; border-bottom: 1px solid #f1f5f9; color: #1e293b; }
table tr:hover td { background: #f8fafc; }
.bdg-green { background: #22c55e18; color: #16a34a; padding: 0.1rem 0.55rem; border-radius: 999px; font-size: 0.72rem; font-weight: 700; }
.bdg-red { background: #ef444418; color: #dc2626; padding: 0.1rem 0.55rem; border-radius: 999px; font-size: 0.72rem; font-weight: 700; }
.bdg-gray { background: #64748b18; color: #64748b; padding: 0.1rem 0.55rem; border-radius: 999px; font-size: 0.72rem; font-weight: 700; }
.btn-sm { color: #ef4444; background: none; border: none; cursor: pointer; padding: 0.25rem; font-size: 0.85rem; }
.btn-sm:hover { color: #dc2626; }

@media (max-width:768px) { .form-row { grid-template-columns: 1fr; } .stats { grid-template-columns: repeat(2,1fr); } }
</style>

<div style="max-width: 1000px; margin: 0 auto; padding: 1.5rem 0;">
    <div class="page-title">
        <h2><span style="color:#6366f1;">Aktivatsiya</span> kalitlari</h2>
        <p>Kalit yaratish, boshqarish va hisobot</p>
    </div>

    <div class="stats">
        <div class="stat"><div class="num" style="color:#6366f1;">{{ $stats["total"] }}</div><div class="lbl">Jami kalitlar</div></div>
        <div class="stat"><div class="num" style="color:#22c55e;">{{ $stats["available"] }}</div><div class="lbl">Mavjud</div></div>
        <div class="stat"><div class="num" style="color:#ef4444;">{{ $stats["used"] }}</div><div class="lbl">Ishlatilgan</div></div>
        <div class="stat"><div class="num" style="color:#f59e0b;">{{ $activeNow }}</div><div class="lbl">Aktiv donorlar</div></div>
    </div>

    <div class="rev">
        <div>
            <div class="big">{{ number_format($totalRevenue, 0, ".", " ") }} som</div>
            <div class="sm">Kalitlar orqali toplangan mablag</div>
        </div>
        <div style="text-align:right;"><div style="font-size:1.1rem; font-weight:700;">{{ $stats["used"] }} ta</div><div class="sm">sotilgan</div></div>
    </div>

    <div class="card">
        <h3>Yangi kalit yaratish</h3>
        <form method="POST" action="{{ route("admin.activation-keys.store") }}">
            @csrf
            <div class="form-row">
                <div class="form-group">
                    <label>Rank</label>
                    <select name="rank">
                        @foreach($ranks as $val => $label)
                            <option value="{{ $val }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Muddat</label>
                    <select name="duration">
                        @foreach($durations as $val => $cfg)
                            <option value="{{ $val }}">{{ $cfg["label"] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Soni</label>
                    <input type="number" name="count" value="1" min="1" max="50">
                </div>
            </div>
            <button type="submit" class="btn-primary"><i class="mdi mdi-key-plus"></i> Kalit yaratish</button>
        </form>

        @if(session("generated_codes"))
            <div class="codes">
                <div class="title">Yaratilgan kalitlar:</div>
                @foreach(session("generated_codes") as $code)
                    <code>{{ $code }}</code>
                @endforeach
                <div class="note">Bu kodlarni boshqa oynada nusxalab oling!</div>
            </div>
        @endif
    </div>

    <div class="card">
        <h3>Kalitlar royhati</h3>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr><th>Kod</th><th>Rank</th><th>Muddat</th><th>Narxi</th><th>Holat</th><th>Ishlatgan</th><th>Sana</th><th></th></tr>
                </thead>
                <tbody>
                    @forelse($keys as $key)
                        @php $price = \App\Models\Donation::priceForDuration($key->rank, $key->duration); @endphp
                        <tr>
                            <td><code style="font-size:0.85rem; letter-spacing:1px; font-weight:700; color:#6366f1;">{{ $key->code }}</code></td>
                            <td><span style="color:{{\App\Models\Donation::configForRank($key->rank)["badge_color"] ?? "#000"}}; font-weight:600;">{{ $key->rankLabel() }}</span></td>
                            <td>{{ $key->durationLabel() }}</td>
                            <td style="font-weight:600;">{{ number_format($price, 0, ".", " ") }} som</td>
                            <td>
                                @if($key->is_used) <span class="bdg-red">Ishlatilgan</span>
                                @else <span class="bdg-green">Mavjud</span>
                                @endif
                            </td>
                            <td style="color:#64748b;">{{ $key->user?->name ?? "—" }}</td>
                            <td style="font-size:0.8rem; color:#64748b;">{{ $key->created_at->format("d.m.Y H:i") }}</td>
                            <td>
                                @unless($key->is_used)
                                    <form method="POST" action="{{ route("admin.activation-keys.destroy", $key) }}" style="display:inline;">
                                        @csrf @method("DELETE")
                                        <button type="submit" class="btn-sm" onclick="return confirm('Kalit ochirilsinmi?')"><i class="mdi mdi-trash-can-outline"></i></button>
                                    </form>
                                @endunless
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" style="text-align:center; color:#64748b; padding:2.5rem;">Hali kalit yaratilmagan</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div style="margin-top:1rem;">{{ $keys->links() }}</div>
    </div>
</div>
@endsection