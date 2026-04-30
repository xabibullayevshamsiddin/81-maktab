@extends('admin.layouts.main')

@section('title', 'Dashboard | Admin Panel')

@section('content')
@php
  $dashboardUser = auth()->user();
  $canManageContent = $dashboardUser->canManageContent();
  $canManageInbox = $dashboardUser->canManageInbox();
  $canManageEducation = $dashboardUser->canManageEducation();
  $canManageSystem = $dashboardUser->canManageSystem();
@endphp

<style>
  .dashboard-section {
    padding: 20px 0 60px;
  }
  .dashboard-header {
    margin-bottom: 40px;
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
  }
  .dashboard-welcome h2 {
    font-size: 2.5rem;
    background: linear-gradient(135deg, var(--admin-text-main), var(--admin-prime));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    margin-bottom: 5px;
  }
  .bento-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 24px;
    margin-bottom: 30px;
  }
  @media (max-width: 1200px) {
    .bento-grid { grid-template-columns: repeat(2, 1fr); }
  }
  @media (max-width: 576px) {
    .bento-grid { grid-template-columns: 1fr; }
  }
  
  .glass-card {
    background: var(--admin-card-bg);
    backdrop-filter: blur(20px);
    border: 1px solid var(--admin-glass-border);
    border-radius: var(--admin-card-radius);
    padding: 30px;
    transition: all 0.4s cubic-bezier(0.23, 1, 0.32, 1);
  }
  
  .icon-box {
    width: 64px;
    height: 64px;
    border-radius: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 28px;
    margin-bottom: 20px;
    background: white;
    box-shadow: 0 10px 20px rgba(0,0,0,0.05);
  }
  
  .stat-card {
    height: 100%;
    display: flex;
    flex-direction: column;
  }
  .stat-card .value {
    font-size: 2.2rem;
    font-weight: 800;
    font-family: var(--admin-font-heading);
    margin-bottom: 5px;
    color: var(--admin-text-main);
  }
  .stat-card .label {
    color: var(--admin-text-muted);
    font-weight: 600;
    font-size: 0.95rem;
  }
  .stat-card .meta {
    margin-top: auto;
    font-size: 0.85rem;
    color: var(--admin-text-muted);
    padding-top: 15px;
    border-top: 1px solid var(--admin-border-subtle);
  }

  .premium-list-item {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 15px;
    border-radius: 20px;
    transition: all 0.3s ease;
    border: 1px solid transparent;
    margin-bottom: 10px;
  }
  .premium-list-item:hover {
    background: white;
    border-color: var(--admin-glass-border);
    transform: translateX(5px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.03);
  }
  .list-icon {
    width: 48px;
    height: 48px;
    border-radius: 14px;
    background: var(--admin-bg);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    flex-shrink: 0;
  }
  
  .dashboard-grid-main {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 24px;
  }
  @media (max-width: 992px) {
    .dashboard-grid-main { grid-template-columns: 1fr; }
  }
</style>

<section class="dashboard-section">
  <div class="container-fluid">
    
    <!-- Header -->
    <div class="dashboard-header dashboard-card-item">
      <div class="dashboard-welcome">
        <h2>Salom, {{ $dashboardUser->name }}!</h2>
        <p class="text-muted">Bugun: {{ now()->translatedFormat('j-F, l') }} | {{ now()->format('H:i') }}</p>
      </div>
      <div class="dashboard-actions">
        <span class="dashboard-badge info">{{ $dashboardUser->role_label }}</span>
      </div>
    </div>

    <!-- Stats Bento Grid -->
    <div class="bento-grid">
      <!-- Users -->
      <div class="card-style dashboard-card-item">
        <div class="stat-card">
          <div class="icon-box" style="color: #6366f1; background: rgba(99, 102, 241, 0.1);">
            <i class="mdi mdi-account-group-outline"></i>
          </div>
          <div class="value">{{ number_format($stats['users']) }}</div>
          <div class="label">Foydalanuvchilar</div>
          <div class="meta">
            Ustozlar: <strong>{{ number_format($stats['teachers']) }}</strong>
          </div>
        </div>
      </div>

      <!-- News -->
      @if($canManageContent)
      <div class="card-style dashboard-card-item" style="animation-delay: 0.1s">
        <div class="stat-card">
          <div class="icon-box" style="color: #10b981; background: rgba(16, 185, 129, 0.1);">
            <i class="mdi mdi-newspaper-variant-outline"></i>
          </div>
          <div class="value">{{ number_format($stats['posts']) }}</div>
          <div class="label">Yangiliklar</div>
          <div class="meta">
            Kategoriyalar: <strong>{{ number_format($stats['categories']) }}</strong>
          </div>
        </div>
      </div>
      @endif

      <!-- Education: Courses -->
      @if($canManageEducation)
      <div class="card-style dashboard-card-item" style="animation-delay: 0.2s">
        <div class="stat-card">
          <div class="icon-box" style="color: #f59e0b; background: rgba(245, 158, 11, 0.1);">
            <i class="mdi mdi-book-open-page-variant-outline"></i>
          </div>
          <div class="value">{{ number_format($stats['courses']) }}</div>
          <div class="label">Kurslar</div>
          <div class="meta">
            Nashrda: <strong>{{ number_format($stats['published_courses']) }}</strong>
          </div>
        </div>
      </div>
      @endif

      <!-- Inbox: Messages/Comments -->
      @if($canManageInbox)
      <div class="card-style dashboard-card-item" style="animation-delay: 0.3s">
        <div class="stat-card">
          <div class="icon-box" style="color: #8b5cf6; background: rgba(139, 92, 246, 0.1);">
            <i class="mdi mdi-comment-text-multiple-outline"></i>
          </div>
          <div class="value">{{ number_format($stats['comments'] + $stats['contact_messages']) }}</div>
          <div class="label">Muloqotlar</div>
          <div class="meta">
            Yangi xabarlar: <strong>{{ number_format($stats['today_messages']) }}</strong>
          </div>
        </div>
      </div>
      @endif
    </div>

    <!-- Main Content Layout -->
    <div class="dashboard-grid-main">
      
      <!-- Left Column: Activity & KPI -->
      <div class="dashboard-left">
        
        <!-- KPI Card -->
        <div class="card-style mb-30 dashboard-card-item" style="animation-delay: 0.4s">
          <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="fw-bold">Tezkor Nazorat</h5>
            <i class="mdi mdi-lightning-bolt text-warning fs-4"></i>
          </div>
          
          <div class="row g-3">
            @if($canManageInbox)
            <div class="col-md-6">
              <div class="dashboard-kpi-row">
                <div>
                  <div class="fw-bold">Izohlar</div>
                  <div class="text-muted small">Moderatsiya kutayotganlar</div>
                </div>
                <span class="dashboard-badge {{ $stats['pending_comments'] > 0 ? 'warning' : 'success' }}">
                  {{ $stats['pending_comments'] }}
                </span>
              </div>
            </div>
            @endif

            @if($canManageEducation)
            <div class="col-md-6">
              <div class="dashboard-kpi-row">
                <div>
                  <div class="fw-bold">Kurs arizalari</div>
                  <div class="text-muted small">Yangi so'rovlar</div>
                </div>
                <span class="dashboard-badge {{ $stats['pending_enrollments'] > 0 ? 'warning' : 'success' }}">
                  {{ $stats['pending_enrollments'] }}
                </span>
              </div>
            </div>
            @endif

            @if($canManageEducation)
            <div class="col-md-6">
              <div class="dashboard-kpi-row">
                <div>
                  <div class="fw-bold">Imtihonlar</div>
                  <div class="text-muted small">Faol testlar</div>
                </div>
                <span class="dashboard-badge info">{{ $stats['active_exams'] }}</span>
              </div>
            </div>
            @endif

            <div class="col-md-6">
              <div class="dashboard-kpi-row">
                <div>
                  <div class="fw-bold">Tizim holati</div>
                  <div class="text-muted small">Oxirgi 24 soatlik faollik</div>
                </div>
                <span class="dashboard-badge success">Stabil</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Recent Activity Tabs or Mixed List -->
        <div class="row">
          @if($canManageContent)
          <div class="col-md-12 mb-30">
            <div class="card-style h-100 dashboard-card-item" style="animation-delay: 0.5s">
              <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="fw-bold">So'nggi Yangiliklar</h5>
                <a href="{{ route('posts.index') }}" class="btn btn-sm btn-light rounded-pill">Barchasi</a>
              </div>
              <div class="dashboard-list">
                @forelse($recentPosts as $post)
                <div class="premium-list-item">
                  <div class="list-icon" style="color: #10b981;">
                    <i class="mdi mdi-text-box-outline"></i>
                  </div>
                  <div class="flex-grow-1">
                    <div class="fw-bold text-dark">{{ \Illuminate\Support\Str::limit($post->title, 70) }}</div>
                    <div class="text-muted small">
                      {{ $post->category?->name ?? 'Kategoriyasiz' }} • {{ $post->views ?? 0 }} ko'rish
                    </div>
                  </div>
                  <div class="text-end text-muted small">
                    {{ $post->created_at?->diffForHumans() }}
                  </div>
                </div>
                @empty
                <div class="text-center py-5 text-muted">Hozircha ma'lumot yo'q</div>
                @endforelse
              </div>
            </div>
          </div>
          @endif
        </div>

      </div>

      <!-- Right Column: Sidebar Panels -->
      <div class="dashboard-right">
        
        <!-- Messaging/Inbox Sidebar -->
        @if($canManageInbox)
        <div class="card-style mb-30 dashboard-card-item" style="animation-delay: 0.6s">
          <h5 class="fw-bold mb-4">Yangi Xabarlar</h5>
          <div class="dashboard-list">
            @forelse($recentMessages as $msg)
            <div class="premium-list-item">
              <div class="list-icon" style="color: #6366f1;">
                <i class="mdi mdi-email-open-outline"></i>
              </div>
              <div class="overflow-hidden">
                <div class="fw-bold text-truncate">{{ $msg->name }}</div>
                <div class="text-muted small text-truncate">{{ $msg->message }}</div>
              </div>
            </div>
            @empty
            <div class="text-center py-4 text-muted">Xabarlar yo'q</div>
            @endforelse
          </div>
          <a href="{{ route('admin.contact-messages.index') }}" class="btn btn-primary w-100 mt-3 rounded-pill py-2">Inboxga o'tish</a>
        </div>
        @endif

        <!-- Quick Stats/Info -->
        <div class="card-style dashboard-card-item" style="animation-delay: 0.7s; background: linear-gradient(135deg, var(--admin-prime), var(--admin-secondary)); color: white;">
          <h5 class="fw-bold mb-3">Tizim Ma'lumoti</h5>
          <p class="small mb-4" style="opacity: 0.8;">Admin panelning barcha bo'limlari to'g'ri ishlamoqda. Server yuki: 12%</p>
          
          <div class="d-flex align-items-center gap-3 mb-3">
            <div class="rounded-circle bg-white/20 p-2">
              <i class="mdi mdi-database-outline fs-5"></i>
            </div>
            <div>
              <div class="fw-bold">Ma'lumotlar bazasi</div>
              <div class="small opacity-70">Zaxira: Bugun 04:00</div>
            </div>
          </div>
          
          <div class="d-flex align-items-center gap-3">
            <div class="rounded-circle bg-white/20 p-2">
              <i class="mdi mdi-shield-check-outline fs-5"></i>
            </div>
            <div>
              <div class="fw-bold">Xavfsizlik</div>
              <div class="small opacity-70">SSL sertifikati faol</div>
            </div>
          </div>
        </div>

      </div>

    </div>

  </div>
</section>
@endsection
