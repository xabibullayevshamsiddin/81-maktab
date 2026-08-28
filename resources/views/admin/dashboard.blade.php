@extends('admin.layouts.main')

@section('title', 'Dashboard | Admin Panel')

@section('content')
@php
  $dashboardUser = auth()->user();
  $canManageContent = $dashboardUser->canManageContent();
  $canManageInbox = $dashboardUser->canManageInbox();
  $canManageEducation = $dashboardUser->canManageEducation();
  $canManageSystem = $dashboardUser->canManageSystem();
  $isSuperAdmin = $dashboardUser->isSuperAdmin();
@endphp

<style>
  .dashboard-section {
    padding: 20px 0 60px;
  }
  .dashboard-header {
    margin-bottom: 32px;
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
  }
  .dashboard-welcome h2 {
    font-size: 2rem;
    font-weight: 800;
    color: var(--admin-text-main);
    margin-bottom: 4px;
  }
  .dashboard-welcome p {
    color: var(--admin-text-muted);
    font-size: 0.9rem;
  }

  /* Stat Cards */
  .bento-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
    margin-bottom: 28px;
  }
  @media (max-width: 1200px) {
    .bento-grid { grid-template-columns: repeat(2, 1fr); }
  }
  @media (max-width: 576px) {
    .bento-grid { grid-template-columns: 1fr; }
  }

  .stat-card {
    background: var(--admin-card-bg);
    border: 1px solid var(--admin-glass-border);
    border-radius: 16px;
    padding: 24px;
    transition: all 0.3s cubic-bezier(0.23, 1, 0.32, 1);
    position: relative;
    overflow: hidden;
  }
  .stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(0,0,0,0.06);
  }
  .stat-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    border-radius: 16px 16px 0 0;
  }
  .stat-card--users::before { background: linear-gradient(90deg, #6366f1, #818cf8); }
  .stat-card--posts::before { background: linear-gradient(90deg, #10b981, #34d399); }
  .stat-card--courses::before { background: linear-gradient(90deg, #f59e0b, #fbbf24); }
  .stat-card--messages::before { background: linear-gradient(90deg, #8b5cf6, #a78bfa); }

  .stat-card .icon-box {
    width: 52px;
    height: 52px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    margin-bottom: 16px;
  }
  .stat-card .value {
    font-size: 2rem;
    font-weight: 800;
    color: var(--admin-text-main);
    line-height: 1;
    margin-bottom: 4px;
  }
  .stat-card .label {
    color: var(--admin-text-muted);
    font-weight: 600;
    font-size: 0.9rem;
    margin-bottom: 12px;
  }
  .stat-card .meta {
    font-size: 0.8rem;
    color: var(--admin-text-muted);
    padding-top: 12px;
    border-top: 1px solid var(--admin-border-subtle);
  }
  .stat-card .meta strong {
    color: var(--admin-text-main);
  }

  /* KPI Card */
  .kpi-card {
    background: var(--admin-card-bg);
    border: 1px solid var(--admin-glass-border);
    border-radius: 16px;
    padding: 24px;
    margin-bottom: 24px;
  }
  .kpi-card h5 {
    font-weight: 700;
    font-size: 1rem;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 8px;
  }
  .kpi-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 14px 16px;
    border-radius: 12px;
    transition: all 0.2s ease;
    border: 1px solid transparent;
  }
  .kpi-row:hover {
    background: var(--admin-bg);
    border-color: var(--admin-glass-border);
  }
  .kpi-row .kpi-label {
    font-weight: 600;
    font-size: 0.9rem;
    color: var(--admin-text-main);
  }
  .kpi-row .kpi-desc {
    font-size: 0.8rem;
    color: var(--admin-text-muted);
  }
  .kpi-badge {
    padding: 4px 12px;
    border-radius: 999px;
    font-size: 0.85rem;
    font-weight: 700;
  }
  .kpi-badge--success { background: rgba(16, 185, 129, 0.12); color: #059669; }
  .kpi-badge--warning { background: rgba(245, 158, 11, 0.12); color: #d97706; }
  .kpi-badge--info { background: rgba(99, 102, 241, 0.12); color: #4f46e5; }
  .kpi-badge--danger { background: rgba(239, 68, 68, 0.12); color: #dc2626; }

  /* Chart Card */
  .chart-card {
    background: var(--admin-card-bg);
    border: 1px solid var(--admin-glass-border);
    border-radius: 16px;
    padding: 24px;
    margin-bottom: 24px;
  }
  .chart-card h5 {
    font-weight: 700;
    font-size: 1rem;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
  }

  /* List Items */
  .list-card {
    background: var(--admin-card-bg);
    border: 1px solid var(--admin-glass-border);
    border-radius: 16px;
    padding: 24px;
    margin-bottom: 24px;
  }
  .list-card h5 {
    font-weight: 700;
    font-size: 1rem;
    margin-bottom: 16px;
    display: flex;
    align-items: center;
    justify-content: space-between;
  }
  .list-item {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 12px;
    border-radius: 12px;
    transition: all 0.2s ease;
    margin-bottom: 4px;
  }
  .list-item:hover {
    background: var(--admin-bg);
  }
  .list-item-icon {
    width: 42px;
    height: 42px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    flex-shrink: 0;
  }
  .list-item-content {
    flex: 1;
    min-width: 0;
  }
  .list-item-title {
    font-weight: 600;
    font-size: 0.9rem;
    color: var(--admin-text-main);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }
  .list-item-meta {
    font-size: 0.8rem;
    color: var(--admin-text-muted);
  }
  .list-item-time {
    font-size: 0.75rem;
    color: var(--admin-text-muted);
    white-space: nowrap;
  }

  /* System Info Card */
  .system-card {
    background: linear-gradient(135deg, #4f46e5, #7c3aed);
    border-radius: 16px;
    padding: 24px;
    color: white;
  }
  .system-card h5 {
    font-weight: 700;
    font-size: 1rem;
    margin-bottom: 16px;
  }
  .system-info-row {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 0;
  }
  .system-info-row:not(:last-child) {
    border-bottom: 1px solid rgba(255,255,255,0.15);
  }
  .system-info-icon {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    background: rgba(255,255,255,0.15);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    flex-shrink: 0;
  }
  .system-info-label {
    font-size: 0.8rem;
    opacity: 0.8;
  }
  .system-info-value {
    font-weight: 600;
    font-size: 0.9rem;
  }

  /* Grid Layout */
  .dashboard-grid {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 24px;
  }
  @media (max-width: 992px) {
    .dashboard-grid { grid-template-columns: 1fr; }
  }

  /* Empty State */
  .empty-state {
    text-align: center;
    padding: 32px 16px;
    color: var(--admin-text-muted);
  }
  .empty-state i {
    font-size: 2rem;
    margin-bottom: 12px;
    opacity: 0.5;
  }

  /* Quick Actions */
  .quick-actions {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
  }
  .quick-action-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 16px;
    border-radius: 10px;
    font-size: 0.85rem;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.2s ease;
    border: 1px solid var(--admin-glass-border);
    background: var(--admin-card-bg);
    color: var(--admin-text-main);
  }
  .quick-action-btn:hover {
    background: var(--admin-bg);
    transform: translateY(-1px);
  }
  .quick-action-btn i {
    font-size: 16px;
  }
</style>

<section class="dashboard-section">
  <div class="container-fluid">

    <!-- Header -->
    <div class="dashboard-header dashboard-card-item">
      <div class="dashboard-welcome">
        <h2>Salom, {{ $dashboardUser->name }}!</h2>
        <p>Bugun: {{ now()->translatedFormat('j-F, l') }} | {{ now()->format('H:i') }}</p>
      </div>
      <div class="dashboard-actions">
        <span class="dashboard-badge info">{{ $dashboardUser->role_label }}</span>
      </div>
    </div>

    <!-- Quick Actions -->
    <div class="quick-actions mb-24 dashboard-card-item" style="animation-delay: 0.05s">
      @if($canManageContent)
        <a href="{{ route('posts.create') }}" class="quick-action-btn">
          <i class="mdi mdi-plus-circle-outline"></i> Yangi yangilik
        </a>
      @endif
      @if($canManageEducation)
        <a href="{{ route('admin.courses.index') }}" class="quick-action-btn">
          <i class="mdi mdi-book-plus-outline"></i> Kurs boshqarish
        </a>
      @endif
      <a href="{{ route('user') }}" class="quick-action-btn">
        <i class="mdi mdi-account-group-outline"></i> Foydalanuvchilar
      </a>
    </div>

    <!-- Stats Bento Grid -->
    <div class="bento-grid">
      <!-- Users -->
      <div class="stat-card stat-card--users dashboard-card-item" style="animation-delay: 0.1s">
        <div class="icon-box" style="color: #6366f1; background: rgba(99, 102, 241, 0.1);">
          <i class="mdi mdi-account-group-outline"></i>
        </div>
        <div class="value">{{ number_format($stats['users']) }}</div>
        <div class="label">Foydalanuvchilar</div>
        <div class="meta">
          Ustozlar: <strong>{{ number_format($stats['teachers']) }}</strong>
          @if($monthlyStats['new_users_this_month'] ?? 0)
            • Bu oy: <strong>+{{ $monthlyStats['new_users_this_month'] }}</strong>
          @endif
        </div>
      </div>

      <!-- News -->
      @if($canManageContent)
      <div class="stat-card stat-card--posts dashboard-card-item" style="animation-delay: 0.15s">
        <div class="icon-box" style="color: #10b981; background: rgba(16, 185, 129, 0.1);">
          <i class="mdi mdi-newspaper-variant-outline"></i>
        </div>
        <div class="value">{{ number_format($stats['posts']) }}</div>
        <div class="label">Yangiliklar</div>
        <div class="meta">
          Kategoriyalar: <strong>{{ number_format($stats['categories']) }}</strong>
          @if($monthlyStats['new_posts_this_month'] ?? 0)
            • Bu oy: <strong>+{{ $monthlyStats['new_posts_this_month'] }}</strong>
          @endif
        </div>
      </div>
      @endif

      <!-- Education: Courses -->
      @if($canManageEducation)
      <div class="stat-card stat-card--courses dashboard-card-item" style="animation-delay: 0.2s">
        <div class="icon-box" style="color: #f59e0b; background: rgba(245, 158, 11, 0.1);">
          <i class="mdi mdi-book-open-page-variant-outline"></i>
        </div>
        <div class="value">{{ number_format($stats['courses']) }}</div>
        <div class="label">Kurslar</div>
        <div class="meta">
          Nashrda: <strong>{{ number_format($stats['published_courses']) }}</strong>
          • Kutilmoqda: <strong>{{ number_format($stats['pending_courses']) }}</strong>
        </div>
      </div>
      @endif

      <!-- Inbox: Messages/Comments -->
      @if($canManageInbox)
      <div class="stat-card stat-card--messages dashboard-card-item" style="animation-delay: 0.25s">
        <div class="icon-box" style="color: #8b5cf6; background: rgba(139, 92, 246, 0.1);">
          <i class="mdi mdi-comment-text-multiple-outline"></i>
        </div>
        <div class="value">{{ number_format($stats['comments'] + $stats['contact_messages']) }}</div>
        <div class="label">Muloqotlar</div>
        <div class="meta">
          Yangi xabarlar: <strong>{{ number_format($stats['today_messages']) }}</strong>
        </div>
      </div>
      @endif
    </div>

    <!-- Main Content Layout -->
    <div class="dashboard-grid">

      <!-- Left Column -->
      <div class="dashboard-left">

        <!-- KPI Card -->
        <div class="kpi-card dashboard-card-item" style="animation-delay: 0.3s">
          <h5>
            <i class="mdi mdi-lightning-bolt" style="color: #f59e0b;"></i>
            Tezkor Nazorat
          </h5>

          @if($canManageInbox)
          <div class="kpi-row">
            <div>
              <div class="kpi-label">Izohlar</div>
              <div class="kpi-desc">Moderatsiya kutayotganlar</div>
            </div>
            <span class="kpi-badge {{ $stats['pending_comments'] > 0 ? 'kpi-badge--warning' : 'kpi-badge--success' }}">
              {{ $stats['pending_comments'] }}
            </span>
          </div>
          @endif

          @if($canManageEducation)
          <div class="kpi-row">
            <div>
              <div class="kpi-label">Kurs arizalari</div>
              <div class="kpi-desc">Yangi so'rovlar</div>
            </div>
            <span class="kpi-badge {{ $stats['pending_enrollments'] > 0 ? 'kpi-badge--warning' : 'kpi-badge--success' }}">
              {{ $stats['pending_enrollments'] }}
            </span>
          </div>

          <div class="kpi-row">
            <div>
              <div class="kpi-label">Imtihonlar</div>
              <div class="kpi-desc">Faol testlar</div>
            </div>
            <span class="kpi-badge kpi-badge--info">{{ $stats['active_exams'] }}</span>
          </div>
          @endif

          @if($canManageSystem)
          <div class="kpi-row">
            <div>
              <div class="kpi-label">Tizim holati</div>
              <div class="kpi-desc">Oxirgi 24 soatlik faollik</div>
            </div>
            <span class="kpi-badge kpi-badge--success">Stabil</span>
          </div>
          @endif
        </div>

        <!-- Activity Chart -->
        <div class="chart-card dashboard-card-item" style="animation-delay: 0.35s">
          <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
            <div>
              <h5 class="mb-1" style="font-size: 1.1rem; font-weight: 700; color: var(--admin-text-main);">
                <i class="mdi mdi-chart-timeline-variant text-primary me-1"></i> Sayt Haftalik Tashriflari & Faollik
              </h5>
              <p class="text-muted small mb-0">Oxirgi 7 kunlik real tashriflar va noyob mehmonlar dinamikasi</p>
            </div>
            <div class="btn-group btn-group-sm" role="group" id="chart-dataset-toggle">
              <button type="button" class="btn btn-primary active" data-view="both" style="font-size: 0.78rem; font-weight: 600;">Barchasi</button>
              <button type="button" class="btn btn-outline-primary" data-view="views" style="font-size: 0.78rem; font-weight: 600;">Ko'rishlar</button>
              <button type="button" class="btn btn-outline-primary" data-view="uniques" style="font-size: 0.78rem; font-weight: 600;">Noyob mehmonlar</button>
            </div>
          </div>

          {{-- Pro Summary Metrics Grid --}}
          <div class="row g-2 mb-3">
            <div class="col-6 col-md-3">
              <div style="background: var(--admin-bg, rgba(99, 102, 241, 0.04)); border: 1px solid var(--admin-border-subtle, #e2e8f0); border-radius: 12px; padding: 10px 12px;">
                <div style="font-size: 0.75rem; color: var(--admin-text-muted); font-weight: 600;">Bugungi tashriflar</div>
                <div style="font-size: 1.15rem; font-weight: 800; color: #6366f1; margin-top: 2px;">
                  {{ $weeklyStats['today_views'] ?? 0 }} <span style="font-size: 0.75rem; font-weight: 600; color: var(--admin-text-muted);">/ {{ $weeklyStats['today_uniques'] ?? 0 }} noyob</span>
                </div>
              </div>
            </div>
            <div class="col-6 col-md-3">
              <div style="background: var(--admin-bg, rgba(16, 185, 129, 0.04)); border: 1px solid var(--admin-border-subtle, #e2e8f0); border-radius: 12px; padding: 10px 12px;">
                <div style="font-size: 0.75rem; color: var(--admin-text-muted); font-weight: 600;">Haftalik jami</div>
                <div style="font-size: 1.15rem; font-weight: 800; color: #10b981; margin-top: 2px;">
                  {{ $weeklyStats['total_views'] ?? 0 }} <span style="font-size: 0.75rem; font-weight: 600; color: var(--admin-text-muted);">ko'rish</span>
                </div>
              </div>
            </div>
            <div class="col-6 col-md-3">
              <div style="background: var(--admin-bg, rgba(245, 158, 11, 0.04)); border: 1px solid var(--admin-border-subtle, #e2e8f0); border-radius: 12px; padding: 10px 12px;">
                <div style="font-size: 0.75rem; color: var(--admin-text-muted); font-weight: 600;">O'rtacha kunlik</div>
                <div style="font-size: 1.15rem; font-weight: 800; color: #f59e0b; margin-top: 2px;">
                  {{ $weeklyStats['avg_daily'] ?? 0 }} <span style="font-size: 0.75rem; font-weight: 600; color: var(--admin-text-muted);">/kun</span>
                </div>
              </div>
            </div>
            <div class="col-6 col-md-3">
              <div style="background: var(--admin-bg, rgba(139, 92, 246, 0.04)); border: 1px solid var(--admin-border-subtle, #e2e8f0); border-radius: 12px; padding: 10px 12px;">
                <div style="font-size: 0.75rem; color: var(--admin-text-muted); font-weight: 600;">Eng faol kun</div>
                <div style="font-size: 0.95rem; font-weight: 800; color: #8b5cf6; margin-top: 4px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                  {{ $weeklyStats['peak_day'] ?? '—' }}
                </div>
              </div>
            </div>
          </div>

          <div style="position:relative; height:270px;">
            <canvas id="dashboard-weekly-activity-chart" aria-label="Haftalik faollik grafigi"></canvas>
          </div>
        </div>

        <!-- Recent Posts -->
        @if($canManageContent)
        <div class="list-card dashboard-card-item" style="animation-delay: 0.4s">
          <h5>
            So'nggi Yangiliklar
            <a href="{{ route('posts.index') }}" class="btn btn-sm btn-light rounded-pill">Barchasi</a>
          </h5>
          @forelse($recentPosts as $post)
          <div class="list-item">
            <div class="list-item-icon" style="color: #10b981; background: rgba(16, 185, 129, 0.1);">
              <i class="mdi mdi-text-box-outline"></i>
            </div>
            <div class="list-item-content">
              <div class="list-item-title">{{ \Illuminate\Support\Str::limit($post->title, 60) }}</div>
              <div class="list-item-meta">{{ $post->category?->name ?? 'Kategoriyasiz' }} • {{ $post->views ?? 0 }} ko'rish</div>
            </div>
            <div class="list-item-time">{{ $post->created_at?->diffForHumans() }}</div>
          </div>
          @empty
          <div class="empty-state">
            <i class="mdi mdi-text-box-outline"></i>
            <p>Hozircha yangiliklar yo'q</p>
          </div>
          @endforelse
        </div>
        @endif

      </div>

      <!-- Right Column -->
      <div class="dashboard-right">

        <!-- Messages -->
        @if($canManageInbox)
        <div class="list-card dashboard-card-item" style="animation-delay: 0.45s">
          <h5>
            Yangi Xabarlar
            <a href="{{ route('admin.contact-messages.index') }}" class="btn btn-sm btn-primary rounded-pill">Inbox</a>
          </h5>
          @forelse($recentMessages as $msg)
          <div class="list-item">
            <div class="list-item-icon" style="color: #6366f1; background: rgba(99, 102, 241, 0.1);">
              <i class="mdi mdi-email-open-outline"></i>
            </div>
            <div class="list-item-content">
              <div class="list-item-title">{{ $msg->name }}</div>
              <div class="list-item-meta">{{ \Illuminate\Support\Str::limit($msg->message, 50) }}</div>
            </div>
            <div class="list-item-time">{{ $msg->created_at?->diffForHumans() }}</div>
          </div>
          @empty
          <div class="empty-state">
            <i class="mdi mdi-email-outline"></i>
            <p>Xabarlar yo'q</p>
          </div>
          @endforelse
        </div>
        @endif

        <!-- System Info (Admin only) -->
        @if($canManageSystem)
        <div class="system-card dashboard-card-item" style="animation-delay: 0.5s">
          <h5>Tizim Ma'lumoti</h5>

          <div class="system-info-row">
            <div class="system-info-icon">
              <i class="mdi mdi-database-outline"></i>
            </div>
            <div>
              <div class="system-info-label">Ma'lumotlar bazasi</div>
              <div class="system-info-value">Zaxira: Bugun 04:00</div>
            </div>
          </div>

          <div class="system-info-row">
            <div class="system-info-icon">
              <i class="mdi mdi-shield-check-outline"></i>
            </div>
            <div>
              <div class="system-info-label">Xavfsizlik</div>
              <div class="system-info-value">SSL sertifikati faol</div>
            </div>
          </div>

          @if($monthlyStats['server_uptime'] ?? null)
          <div class="system-info-row">
            <div class="system-info-icon">
              <i class="mdi mdi-clock-outline"></i>
            </div>
            <div>
              <div class="system-info-label">Server ishlash vaqti</div>
              <div class="system-info-value">{{ $monthlyStats['server_uptime'] }}</div>
            </div>
          </div>
          @endif
        </div>
        @endif

        <!-- Recent Users -->
        <div class="list-card dashboard-card-item" style="animation-delay: 0.55s">
          <h5>
            Yangi Foydalanuvchilar
            <a href="{{ route('user') }}" class="btn btn-sm btn-light rounded-pill">Barchasi</a>
          </h5>
          @forelse($recentUsers as $user)
          <div class="list-item">
            <div class="list-item-icon" style="color: #8b5cf6; background: rgba(139, 92, 246, 0.1);">
              <i class="mdi mdi-account-outline"></i>
            </div>
            <div class="list-item-content">
              <div class="list-item-title">{{ $user->name }}</div>
              <div class="list-item-meta">{{ $user->roleRelation?->label ?? 'Foydalanuvchi' }}</div>
            </div>
            <div class="list-item-time">{{ $user->created_at?->diffForHumans() }}</div>
          </div>
          @empty
          <div class="empty-state">
            <i class="mdi mdi-account-outline"></i>
            <p>Foydalanuvchilar yo'q</p>
          </div>
          @endforelse
        </div>

      </div>

    </div>

  </div>
</section>
@endsection

@section('page_scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
  var canvas = document.getElementById('dashboard-weekly-activity-chart');
  if (!canvas || typeof Chart === 'undefined') {
    return;
  }

  var daysData = @json($weeklyActivity);
  var labels = daysData.map(function(d) { return d.label + ' (' + d.full_date + ')'; });
  var viewsValues = daysData.map(function(d) { return d.views || d.count || 0; });
  var uniquesValues = daysData.map(function(d) { return d.uniques || 0; });
  var authValues = daysData.map(function(d) { return d.auth_users || 0; });

  var ctx = canvas.getContext('2d');

  // Gradient 1: Pageviews (Indigo/Purple)
  var gradientViews = ctx.createLinearGradient(0, 0, 0, 260);
  gradientViews.addColorStop(0, 'rgba(99, 102, 241, 0.28)');
  gradientViews.addColorStop(1, 'rgba(99, 102, 241, 0.00)');

  // Gradient 2: Unique Visitors (Emerald/Green)
  var gradientUniques = ctx.createLinearGradient(0, 0, 0, 260);
  gradientUniques.addColorStop(0, 'rgba(16, 185, 129, 0.28)');
  gradientUniques.addColorStop(1, 'rgba(16, 185, 129, 0.00)');

  var chart = new Chart(ctx, {
    type: 'line',
    data: {
      labels: labels,
      datasets: [
        {
          label: "Umumiy ko'rishlar (Pageviews)",
          data: viewsValues,
          borderColor: '#6366f1',
          backgroundColor: gradientViews,
          borderWidth: 2.8,
          pointRadius: 4,
          pointBackgroundColor: '#fff',
          pointBorderColor: '#6366f1',
          pointBorderWidth: 2,
          pointHoverRadius: 6.5,
          fill: true,
          tension: 0.38,
        },
        {
          label: "Noyob mehmonlar (Unique)",
          data: uniquesValues,
          borderColor: '#10b981',
          backgroundColor: gradientUniques,
          borderWidth: 2.4,
          borderDash: [4, 4],
          pointRadius: 3.5,
          pointBackgroundColor: '#fff',
          pointBorderColor: '#10b981',
          pointBorderWidth: 2,
          pointHoverRadius: 6,
          fill: true,
          tension: 0.38,
        }
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          display: true,
          position: 'top',
          align: 'end',
          labels: {
            boxWidth: 12,
            boxHeight: 12,
            usePointStyle: true,
            pointStyle: 'circle',
            font: { size: 11.5, weight: '600' },
            color: '#64748b',
          }
        },
        tooltip: {
          backgroundColor: '#0f172a',
          titleColor: '#f8fafc',
          titleFont: { size: 13, weight: 'bold' },
          bodyColor: '#e2e8f0',
          bodyFont: { size: 12 },
          borderColor: '#334155',
          borderWidth: 1,
          cornerRadius: 10,
          padding: 12,
          boxPadding: 6,
          usePointStyle: true,
          callbacks: {
            title: function(items) {
              if (!items.length) return '';
              var idx = items[0].dataIndex;
              var d = daysData[idx];
              return d.label + ' • ' + d.date;
            },
            afterBody: function(items) {
              if (!items.length) return '';
              var idx = items[0].dataIndex;
              var d = daysData[idx];
              return '🔐 Tizimdagi faol a\'zolar: ' + (d.auth_users || 0) + ' ta';
            }
          }
        },
      },
      scales: {
        x: {
          grid: { display: false },
          ticks: { color: '#94a3b8', font: { size: 11.5 } },
        },
        y: {
          beginAtZero: true,
          grid: { color: 'rgba(148, 163, 184, 0.12)' },
          ticks: { color: '#94a3b8', font: { size: 11.5 }, precision: 0 },
        },
      },
      interaction: {
        intersect: false,
        mode: 'index',
      },
    },
  });

  // Filter Buttons Toggle
  var toggleGroup = document.getElementById('chart-dataset-toggle');
  if (toggleGroup) {
    var buttons = toggleGroup.querySelectorAll('button');
    buttons.forEach(function(btn) {
      btn.addEventListener('click', function() {
        buttons.forEach(function(b) {
          b.classList.remove('btn-primary', 'active');
          b.classList.add('btn-outline-primary');
        });
        btn.classList.remove('btn-outline-primary');
        btn.classList.add('btn-primary', 'active');

        var viewMode = btn.getAttribute('data-view');
        if (viewMode === 'both') {
          chart.data.datasets[0].hidden = false;
          chart.data.datasets[1].hidden = false;
        } else if (viewMode === 'views') {
          chart.data.datasets[0].hidden = false;
          chart.data.datasets[1].hidden = true;
        } else if (viewMode === 'uniques') {
          chart.data.datasets[0].hidden = true;
          chart.data.datasets[1].hidden = false;
        }
        chart.update();
      });
    });
  }
});
</script>
@endsection
