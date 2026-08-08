@extends("admin.layouts.main", ["title" => "Donorlar"])
@section("content")
<style>
.page-head { margin-bottom: 2rem; }
.page-head h2 { font-size: 1.5rem; font-weight: 800; color: #1e293b; margin: 0 0 0.25rem; display: flex; align-items: center; gap: 0.5rem; }
.page-head p { color: #64748b; font-size: 0.9rem; margin: 0; }
.dark .page-head h2 { color: #e2e8f0; }
.dark .page-head p { color: #94a3b8; }

.donors-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    background: #fff;
    border-radius: 16px;
    overflow: hidden;
    border: 1px solid #e2e8f0;
}
.dark .donors-table {
    background: rgba(30, 41, 59, 0.7);
    border-color: rgba(255, 255, 255, 0.08);
}
.donors-table th {
    background: #f8fafc;
    padding: 0.75rem 1rem;
    font-size: 0.75rem;
    font-weight: 700;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    text-align: left;
    border-bottom: 1px solid #e2e8f0;
}
.dark .donors-table th {
    background: rgba(15, 23, 42, 0.5);
    color: #94a3b8;
    border-color: rgba(255, 255, 255, 0.08);
}
.donors-table td {
    padding: 0.75rem 1rem;
    font-size: 0.875rem;
    color: #334155;
    border-bottom: 1px solid #f1f5f9;
    vertical-align: middle;
}
.dark .donors-table td {
    color: #cbd5e1;
    border-color: rgba(255, 255, 255, 0.05);
}
.donors-table tr:last-child td { border-bottom: none; }
.donors-table tr:hover td { background: #f8fafc; }
.dark .donors-table tr:hover td { background: rgba(15, 23, 42, 0.3); }

.user-cell { display: flex; align-items: center; gap: 0.75rem; }
.user-avatar {
    width: 36px; height: 36px; border-radius: 50%;
    object-fit: cover; border: 2px solid #e2e8f0;
}
.user-avatar-fallback {
    width: 36px; height: 36px; border-radius: 50%;
    background: linear-gradient(135deg, #6366f1, #8b5cf6);
    color: #fff; display: flex; align-items: center; justify-content: center;
    font-weight: 700; font-size: 0.8rem;
}
.user-name { font-weight: 600; color: #1e293b; text-decoration: none; }
.user-name:hover { color: #6366f1; }
.dark .user-name { color: #e2e8f0; }
.dark .user-name:hover { color: #818cf8; }
.user-email { font-size: 0.75rem; color: #94a3b8; }

.status-badge {
    display: inline-flex; align-items: center; gap: 0.35rem;
    padding: 0.25rem 0.65rem; border-radius: 9999px;
    font-size: 0.75rem; font-weight: 600;
}
.status-active { background: #dcfce7; color: #166534; }
.status-expired { background: #fee2e2; color: #991b1b; }
.dark .status-active { background: rgba(34, 197, 94, 0.15); color: #4ade80; }
.dark .status-expired { background: rgba(239, 68, 68, 0.15); color: #f87171; }

.btn-revoke {
    display: inline-flex; align-items: center; gap: 0.35rem;
    padding: 0.35rem 0.75rem; border-radius: 8px;
    font-size: 0.75rem; font-weight: 600;
    background: #fef2f2; color: #dc2626; border: 1px solid #fecaca;
    cursor: pointer; transition: all 0.2s;
}
.btn-revoke:hover { background: #fee2e2; border-color: #f87171; }
.dark .btn-revoke { background: rgba(239, 68, 68, 0.1); color: #f87171; border-color: rgba(239, 68, 68, 0.2); }
.dark .btn-revoke:hover { background: rgba(239, 68, 68, 0.2); }

.pagination { margin-top: 1.5rem; display: flex; justify-content: center; }
</style>

<div class="page-head">
    <h2><i class="fa-solid fa-users-gear"></i> Donorlar</h2>
    <p>Barcha faol va eskirgan donor foydalanuvchilar ro'yxati</p>
</div>

@if(session('success'))
    <div style="padding: 0.75rem 1rem; background: #dcfce7; color: #166534; border-radius: 12px; margin-bottom: 1rem; font-weight: 600;">
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div style="padding: 0.75rem 1rem; background: #fee2e2; color: #991b1b; border-radius: 12px; margin-bottom: 1rem; font-weight: 600;">
        {{ session('error') }}
    </div>
@endif

@if($donors->isEmpty())
    <div style="text-align: center; padding: 3rem; color: #94a3b8;">
        <i class="fa-solid fa-users-slash" style="font-size: 2rem; margin-bottom: 0.5rem;"></i>
        <p>Hozircha donor foydalanuvchilar yo'q.</p>
    </div>
@else
    <table class="donors-table">
        <thead>
            <tr>
                <th>Foydalanuvchi</th>
                <th>Daraja</th>
                <th>Sotib olingan</th>
                <th>Muddat</th>
                <th>O'tgan</th>
                <th>Qolgan</th>
                <th>Holat</th>
                <th>Amal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($donors as $donor)
                @php
                    $lastDonation = $donor->donations->first();
                    $isExpired = $donor->isDonationExpired();
                    $durationDays = $lastDonation && $lastDonation->paid_at && $lastDonation->expires_at
                        ? $lastDonation->paid_at->diffInDays($lastDonation->expires_at)
                        : null;
                    $durationLabel = match(true) {
                        $durationDays === null => '-',
                        $durationDays >= 360 => '1 yillik',
                        $durationDays >= 85 && $durationDays <= 95 => '3 oylik',
                        $durationDays >= 28 && $durationDays <= 32 => '1 oylik',
                        default => $durationDays . ' kun',
                    };
                @endphp
                <tr>
                    <td>
                        <div class="user-cell">
                            @if($donor->avatar)
                                <img src="{{ app_storage_asset($donor->avatar) }}" alt="" class="user-avatar">
                            @else
                                <span class="user-avatar-fallback">{{ strtoupper(substr($donor->name, 0, 1)) }}</span>
                            @endif
                            <div>
                                <a href="{{ route('profile.show') }}" class="user-name">{{ $donor->name }}</a>
                                <div class="user-email">{{ $donor->email }}</div>
                            </div>
                        </div>
                    </td>
                    <td>{!! $donor->donorBadgeHtml() !!}</td>
                    <td>{{ $lastDonation?->paid_at?->format('d.m.Y') ?? '-' }}</td>
                    <td>{{ $durationLabel }}</td>
                    <td>{{ $lastDonation?->paid_at?->diffForHumans(null, true) ?? '-' }}</td>
                    <td>{{ $donor->formatRemainingTime() ?: '-' }}</td>
                    <td>
                        @if($isExpired)
                            <span class="status-badge status-expired">
                                <i class="fa-solid fa-circle-xmark"></i> Tugagan
                            </span>
                        @else
                            <span class="status-badge status-active">
                                <i class="fa-solid fa-circle-check"></i> Faol
                            </span>
                        @endif
                    </td>
                    <td>
                        @if($donor->donation_rank)
                            <form action="{{ route('admin.donors.revoke', $donor) }}" method="POST"
                                  onsubmit="return confirm('{{ $donor->name }} uchun donor holatini bekor qilishga ishonchingiz komilmi? Bu amalni ortga qaytarib bo\'lmaydi.');">
                                @csrf
                                <button type="submit" class="btn-revoke">
                                    <i class="fa-solid fa-ban"></i> Bekor qilish
                                </button>
                            </form>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="pagination">
        {{ $donors->links() }}
    </div>
@endif
@endsection
