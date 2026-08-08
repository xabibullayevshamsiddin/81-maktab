@extends("admin.layouts.main", ["title" => "Donorlar"])
@section("content")
<style>
/* ── Page container ── */
.donors-page {
    padding: 1.5rem 2rem 3rem;
}
@media (max-width: 767px) {
    .donors-page { padding: 1rem; }
}

/* ── Header ── */
.donors-header {
    display: flex; align-items: flex-start; justify-content: space-between;
    gap: 1rem; margin-bottom: 1.75rem; flex-wrap: wrap;
}
.donors-header-left h2 {
    font-size: 1.4rem; font-weight: 800; color: #1e293b; margin: 0 0 0.25rem;
    display: flex; align-items: center; gap: 0.5rem;
}
.donors-header-left h2 i { color: #6366f1; font-size: 1.5rem; }
.donors-header-left p { color: #64748b; font-size: 0.85rem; margin: 0; }
.dark .donors-header-left h2 { color: #e2e8f0; }
.dark .donors-header-left h2 i { color: #818cf8; }
.dark .donors-header-left p { color: #94a3b8; }

/* ── Stats strip ── */
.donors-stats {
    display: flex; gap: 0.75rem; flex-wrap: wrap;
}
.donors-stat {
    display: flex; align-items: center; gap: 0.5rem;
    padding: 0.5rem 0.85rem; border-radius: 10px;
    background: #f8fafc; border: 1px solid #e2e8f0;
    font-size: 0.8rem; font-weight: 600; color: #475569;
}
.donors-stat i { font-size: 1rem; }
.donors-stat strong { font-size: 1rem; font-weight: 800; color: #1e293b; }
.dark .donors-stat { background: rgba(30,41,59,0.5); border-color: rgba(255,255,255,0.08); color: #94a3b8; }
.dark .donors-stat strong { color: #e2e8f0; }

.donors-stat--active { border-left: 3px solid #22c55e; }
.donors-stat--active i { color: #22c55e; }
.donors-stat--expired { border-left: 3px solid #ef4444; }
.donors-stat--expired i { color: #ef4444; }
.donors-stat--total { border-left: 3px solid #6366f1; }
.donors-stat--total i { color: #6366f1; }

/* ── Flash messages ── */
.flash-msg {
    padding: 0.75rem 1rem; border-radius: 12px; margin-bottom: 1rem;
    font-weight: 600; font-size: 0.85rem;
    display: flex; align-items: center; gap: 0.5rem;
}
.flash-success { background: #dcfce7; color: #166534; }
.flash-error { background: #fee2e2; color: #991b1b; }

/* ── Table wrapper ── */
.donors-table-wrap {
    background: #fff; border-radius: 14px;
    border: 1px solid #e2e8f0; overflow: hidden;
    box-shadow: 0 1px 3px rgba(0,0,0,0.04);
}
.dark .donors-table-wrap {
    background: rgba(30, 41, 59, 0.7);
    border-color: rgba(255, 255, 255, 0.08);
    box-shadow: 0 1px 3px rgba(0,0,0,0.2);
}

/* ── Table ── */
.donors-table {
    width: 100%; border-collapse: collapse; table-layout: auto;
}
.donors-table th {
    background: #f8fafc; padding: 0.7rem 1rem;
    font-size: 0.68rem; font-weight: 700;
    color: #64748b; text-transform: uppercase;
    letter-spacing: 0.06em; text-align: left;
    border-bottom: 2px solid #e2e8f0; white-space: nowrap;
}
.dark .donors-table th {
    background: rgba(15, 23, 42, 0.5);
    color: #94a3b8; border-color: rgba(255, 255, 255, 0.08);
}
.donors-table td {
    padding: 0.75rem 1rem; font-size: 0.85rem;
    color: #334155; border-bottom: 1px solid #f1f5f9;
    vertical-align: middle;
}
.dark .donors-table td {
    color: #cbd5e1; border-color: rgba(255, 255, 255, 0.05);
}
.donors-table tr:last-child td { border-bottom: none; }
.donors-table tbody tr { transition: background 0.15s; }
.donors-table tbody tr:hover td { background: #f8fafc; }
.dark .donors-table tbody tr:hover td { background: rgba(15, 23, 42, 0.3); }

/* Column sizing */
.col-user    { min-width: 220px; }
.col-rank    { min-width: 120px; }
.col-date    { min-width: 100px; }
.col-dur     { min-width: 80px; }
.col-time    { min-width: 90px; }
.col-left    { min-width: 110px; }
.col-status  { min-width: 90px; }
.col-action  { min-width: 110px; text-align: right; }

/* ── User cell ── */
.user-cell { display: flex; align-items: center; gap: 0.65rem; }
.user-avatar {
    width: 36px; height: 36px; border-radius: 10px;
    object-fit: cover; border: 2px solid #e2e8f0; flex-shrink: 0;
}
.user-avatar-fallback {
    width: 36px; height: 36px; border-radius: 10px;
    background: linear-gradient(135deg, #6366f1, #8b5cf6);
    color: #fff; display: flex; align-items: center; justify-content: center;
    font-weight: 700; font-size: 0.8rem; flex-shrink: 0;
}
.user-info { min-width: 0; }
.user-name {
    font-weight: 600; color: #1e293b; text-decoration: none;
    display: block; line-height: 1.3; font-size: 0.85rem;
}
.user-name:hover { color: #6366f1; }
.user-email {
    font-size: 0.72rem; color: #94a3b8; margin-top: 1px;
    overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
    max-width: 180px;
}
.dark .user-name { color: #e2e8f0; }
.dark .user-name:hover { color: #818cf8; }

/* ── Rank badge ── */
.rank-badge {
    display: inline-flex; align-items: center; gap: 0.3rem;
    padding: 0.25rem 0.6rem; border-radius: 9999px;
    font-size: 0.72rem; font-weight: 700;
    white-space: nowrap; line-height: 1.2;
}
.rank-badge i { font-size: 0.75rem; }
.rank-badge--supporter { background: rgba(59,130,246,0.1); color: #2563eb; }
.rank-badge--premium   { background: rgba(139,92,246,0.1); color: #7c3aed; }
.rank-badge--vip       { background: rgba(245,158,11,0.1); color: #d97706; }
.dark .rank-badge--supporter { background: rgba(59,130,246,0.18); color: #60a5fa; }
.dark .rank-badge--premium   { background: rgba(139,92,246,0.18); color: #a78bfa; }
.dark .rank-badge--vip       { background: rgba(245,158,11,0.18); color: #fbbf24; }
.rank-badge-days {
    font-size: 0.6rem; font-weight: 600; opacity: 0.65; margin-left: 1px;
}

/* ── Status badge ── */
.status-badge {
    display: inline-flex; align-items: center; gap: 0.3rem;
    padding: 0.25rem 0.6rem; border-radius: 9999px;
    font-size: 0.72rem; font-weight: 600; white-space: nowrap;
}
.status-active { background: #dcfce7; color: #166534; }
.status-expired { background: #fee2e2; color: #991b1b; }
.dark .status-active { background: rgba(34, 197, 94, 0.15); color: #4ade80; }
.dark .status-expired { background: rgba(239, 68, 68, 0.15); color: #f87171; }

/* ── Revoke button ── */
.btn-revoke {
    display: inline-flex; align-items: center; gap: 0.3rem;
    padding: 0.35rem 0.7rem; border-radius: 8px;
    font-size: 0.72rem; font-weight: 600;
    background: #fef2f2; color: #dc2626;
    border: 1px solid #fecaca; cursor: pointer;
    transition: all 0.2s; white-space: nowrap;
}
.btn-revoke:hover {
    background: #fee2e2; border-color: #f87171;
    transform: translateY(-1px); box-shadow: 0 2px 8px rgba(220,38,38,0.12);
}
.btn-revoke:active { transform: scale(0.97); }
.dark .btn-revoke {
    background: rgba(239, 68, 68, 0.1); color: #f87171;
    border-color: rgba(239, 68, 68, 0.2);
}
.dark .btn-revoke:hover { background: rgba(239, 68, 68, 0.2); }

/* ── Meta text ── */
.meta-text { font-size: 0.8rem; color: #64748b; }
.dark .meta-text { color: #94a3b8; }

/* ── Empty state ── */
.empty-state {
    text-align: center; padding: 5rem 2rem; color: #94a3b8;
    background: #fff; border-radius: 14px;
    border: 1px solid #e2e8f0;
}
.empty-state i { font-size: 3rem; margin-bottom: 1rem; display: block; opacity: 0.35; }
.empty-state p { margin: 0; font-size: 0.95rem; }
.dark .empty-state { background: rgba(30,41,59,0.7); border-color: rgba(255,255,255,0.08); }

/* ── Pagination ── */
.pagination { margin-top: 1.5rem; display: flex; justify-content: center; }

/* ── Responsive ── */
@media (max-width: 1024px) {
    .donors-page { padding: 1rem 1.25rem 2rem; }
    .donors-table-wrap { overflow-x: auto; -webkit-overflow-scrolling: touch; }
    .donors-table { min-width: 860px; }
}
@media (max-width: 767px) {
    .donors-header { flex-direction: column; }
    .donors-stats { width: 100%; }
    .donors-stat { flex: 1; min-width: 0; justify-content: center; }
}
</style>

<div class="donors-page">

    {{-- Header + stats --}}
    <div class="donors-header">
        <div class="donors-header-left">
            <h2><i class="mdi mdi-hand-heart-outline"></i> Donorlar</h2>
            <p>Barcha faol va eskirgan donor foydalanuvchilar ro'yxati</p>
        </div>
        @php
            $totalDonors = $donors->total();
            $activeCount = $donors->getCollection()->filter(fn($d) => !$d->isDonationExpired())->count();
            $expiredCount = $totalDonors - $activeCount;
        @endphp
        <div class="donors-stats">
            <div class="donors-stat donors-stat--total">
                <i class="mdi mdi-account-group-outline"></i>
                <strong>{{ $totalDonors }}</strong> jami
            </div>
            <div class="donors-stat donors-stat--active">
                <i class="mdi mdi-check-circle-outline"></i>
                <strong>{{ $activeCount }}</strong> faol
            </div>
            <div class="donors-stat donors-stat--expired">
                <i class="mdi mdi-close-circle-outline"></i>
                <strong>{{ $expiredCount }}</strong> tugagan
            </div>
        </div>
    </div>

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="flash-msg flash-success">
            <i class="mdi mdi-check-circle-outline"></i> {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="flash-msg flash-error">
            <i class="mdi mdi-alert-circle-outline"></i> {{ session('error') }}
        </div>
    @endif

    {{-- Empty state --}}
    @if($donors->isEmpty())
        <div class="empty-state">
            <i class="mdi mdi-account-off-outline"></i>
            <p>Hozircha donor foydalanuvchilar yo'q.</p>
        </div>
    @else
        {{-- Table --}}
        <div class="donors-table-wrap">
            <table class="donors-table">
                <thead>
                    <tr>
                        <th class="col-user">Foydalanuvchi</th>
                        <th class="col-rank">Daraja</th>
                        <th class="col-date">Sotib olingan</th>
                        <th class="col-dur">Muddat</th>
                        <th class="col-time">O'tgan</th>
                        <th class="col-left">Qolgan</th>
                        <th class="col-status">Holat</th>
                        <th class="col-action">Amal</th>
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
                            $rank = $donor->donation_rank;
                            $rankConfigs = [
                                'supporter' => ['label' => 'Supporter', 'icon' => 'mdi mdi-star', 'class' => 'supporter'],
                                'premium'   => ['label' => 'Premium',   'icon' => 'mdi mdi-gem',   'class' => 'premium'],
                                'vip'       => ['label' => 'VIP',       'icon' => 'mdi mdi-crown', 'class' => 'vip'],
                            ];
                            $rankInfo = $rank ? ($rankConfigs[$rank] ?? null) : null;
                            $daysLeft = 0;
                            if ($donor->donation_rank_expires_at) {
                                $diff = (int) $donor->donation_rank_expires_at->diffInDays(now(), false);
                                $daysLeft = $diff > 0 ? $diff : 0;
                            }
                        @endphp
                        <tr>
                            <td class="col-user">
                                <div class="user-cell">
                                    @if($donor->avatar)
                                        <img src="{{ app_storage_asset($donor->avatar) }}" alt="" class="user-avatar">
                                    @else
                                        <span class="user-avatar-fallback">{{ strtoupper(substr($donor->name, 0, 1)) }}</span>
                                    @endif
                                    <div class="user-info">
                                        <a href="{{ route('profile.show') }}" class="user-name">{{ $donor->name }}</a>
                                        <div class="user-email" title="{{ $donor->email }}">{{ $donor->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="col-rank">
                                @if($rankInfo)
                                    <span class="rank-badge rank-badge--{{ $rankInfo['class'] }}">
                                        <i class="{{ $rankInfo['icon'] }}"></i>
                                        {{ $rankInfo['label'] }}
                                        @if($daysLeft > 0)
                                            <span class="rank-badge-days">{{ $daysLeft }}k</span>
                                        @endif
                                    </span>
                                @else
                                    <span class="meta-text">-</span>
                                @endif
                            </td>
                            <td class="col-date meta-text">{{ $lastDonation?->paid_at?->format('d.m.Y') ?? '-' }}</td>
                            <td class="col-dur meta-text">{{ $durationLabel }}</td>
                            <td class="col-time meta-text">{{ $lastDonation?->paid_at?->diffForHumans(null, true) ?? '-' }}</td>
                            <td class="col-left meta-text">{{ $donor->formatRemainingTime() ?: '-' }}</td>
                            <td class="col-status">
                                @if($isExpired)
                                    <span class="status-badge status-expired">
                                        <i class="mdi mdi-close-circle-outline"></i> Tugagan
                                    </span>
                                @else
                                    <span class="status-badge status-active">
                                        <i class="mdi mdi-check-circle-outline"></i> Faol
                                    </span>
                                @endif
                            </td>
                            <td class="col-action">
                                @if($donor->donation_rank)
                                    <form action="{{ route('admin.donors.revoke', $donor) }}" method="POST"
                                          data-confirm="{{ $donor->name }} uchun donor holatini bekor qilishga ishonchingiz komilmi? Bu amalni ortga qaytarib bo'lmaydi."
                                          data-confirm-title="Donor holatini bekor qilish"
                                          data-confirm-variant="danger"
                                          data-confirm-ok="Ha, bekor qilish">
                                        @csrf
                                        <button type="submit" class="btn-revoke">
                                            <i class="mdi mdi-cancel"></i> Bekor qilish
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="pagination">
            {{ $donors->links() }}
        </div>
    @endif
</div>
@endsection
