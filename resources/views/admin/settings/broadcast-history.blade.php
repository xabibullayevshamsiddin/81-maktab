@extends('admin.layouts.main')

@section('title', 'E\'lonlar tarixi | Admin Panel')

@section('content')
<div class="row">
  <div class="col-lg-12">
    <div class="card-style mb-30">
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
        <h6 class="mb-0" style="display:flex;align-items:center;gap:8px;">
          <i class="mdi mdi-history" style="font-size:18px;color:var(--primary);"></i>
          Telegram e'lonlar tarixi
        </h6>
        <a href="{{ route('admin.settings.index') }}" style="display:flex;align-items:center;gap:4px;font-size:13px;color:var(--primary);text-decoration:none;">
          <i class="mdi mdi-arrow-left" style="font-size:15px;"></i>
          Orqaga
        </a>
      </div>

      @if($broadcasts->isEmpty())
        <div style="text-align:center;padding:40px 0;color:var(--muted);">
          <i class="mdi mdi-send-clock-outline" style="font-size:48px;display:block;margin-bottom:12px;opacity:0.4;"></i>
          <p>Hali e'lon yuborilmagan.</p>
        </div>
      @else
        <div class="table-responsive">
          <table style="width:100%;border-collapse:collapse;">
            <thead>
              <tr style="border-bottom:2px solid var(--border);">
                <th style="padding:10px 12px;text-align:left;font-size:13px;color:var(--muted);">Sana</th>
                <th style="padding:10px 12px;text-align:left;font-size:13px;color:var(--muted);">Admin</th>
                <th style="padding:10px 12px;text-align:left;font-size:13px;color:var(--muted);">Auditoriya</th>
                <th style="padding:10px 12px;text-align:left;font-size:13px;color:var(--muted);">Matn</th>
                <th style="padding:10px 12px;text-align:center;font-size:13px;color:var(--muted);">Yuborildi</th>
                <th style="padding:10px 12px;text-align:center;font-size:13px;color:var(--muted);">Xatolik</th>
                <th style="padding:10px 12px;text-align:center;font-size:13px;color:var(--muted);">Holat</th>
              </tr>
            </thead>
            <tbody>
              @foreach($broadcasts as $bc)
                <tr style="border-bottom:1px solid var(--border);transition:background .15s;" onmouseover="this.style.background='var(--badge-bg)'" onmouseout="this.style.background=''">
                  <td style="padding:10px 12px;font-size:13px;white-space:nowrap;">
                    {{ \Carbon\Carbon::parse($bc->created_at)->format('d.m.Y H:i') }}
                  </td>
                  <td style="padding:10px 12px;font-size:13px;">
                    {{ $bc->admin_name }}
                  </td>
                  <td style="padding:10px 12px;font-size:13px;">
                    @php
                      $audienceLabels = [
                        'all' => '🌐 Hammaga',
                        'teachers' => "👨‍🏫 O'qituvchilar",
                        'donors' => '⭐ Donorlar',
                        'students' => "🎓 O'quvchilar",
                      ];
                    @endphp
                    {{ $audienceLabels[$bc->audience] ?? $bc->audience }}
                  </td>
                  <td style="padding:10px 12px;font-size:13px;max-width:300px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                    {{ \Illuminate\Support\Str::limit(strip_tags($bc->message), 80) }}
                  </td>
                  <td style="padding:10px 12px;font-size:13px;text-align:center;">
                    <span style="color:#10b981;font-weight:500;">{{ $bc->sent_count }}</span>
                    <span style="color:var(--muted);font-size:11px;">/{{ $bc->total_recipients }}</span>
                  </td>
                  <td style="padding:10px 12px;font-size:13px;text-align:center;">
                    @if($bc->failed_count > 0)
                      <span style="color:#ef4444;font-weight:500;">{{ $bc->failed_count }}</span>
                    @else
                      <span style="color:var(--muted);">0</span>
                    @endif
                  </td>
                  <td style="padding:10px 12px;font-size:13px;text-align:center;">
                    @php
                      $statusConfig = [
                        'completed' => ['label' => 'Tugallangan', 'color' => '#10b981', 'icon' => 'mdi-check-circle'],
                        'partial' => ['label' => 'Qisman', 'color' => '#f59e0b', 'icon' => 'mdi-alert-circle'],
                        'pending' => ['label' => 'Kutilmoqda', 'color' => '#3b82f6', 'icon' => 'mdi-clock-outline'],
                        'sending' => ['label' => 'Yuborilmoqda', 'color' => '#8b5cf6', 'icon' => 'mdi-send-clock'],
                      ];
                      $sc = $statusConfig[$bc->status] ?? ['label' => $bc->status, 'color' => '#64748b', 'icon' => 'mdi-help-circle'];
                    @endphp
                    <span style="display:inline-flex;align-items:center;gap:4px;color:{{ $sc['color'] }};font-size:12px;font-weight:500;">
                      <i class="mdi {{ $sc['icon'] }}" style="font-size:14px;"></i>
                      {{ $sc['label'] }}
                    </span>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      @endif
    </div>
  </div>
</div>
@endsection
