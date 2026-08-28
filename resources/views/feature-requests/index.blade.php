<x-layouts.main :title="__('public.feature_requests.page_title')">
  @push('page_styles')
    <style>
      /* ====================================================================
         Feature Requests / Takliflar — High-End Glassmorphism UI
         ==================================================================== */
      .features-page-wrapper {
        padding: 30px 0 80px;
      }

      /* Submit Proposal Glass Card */
      .feature-submit-card {
        background: var(--card-bg, rgba(255, 255, 255, 0.9));
        border: 1px solid var(--border-color, rgba(148, 163, 184, 0.25));
        border-radius: 20px;
        padding: 26px 30px;
        margin-bottom: 36px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.04);
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        transition: all 0.3s ease;
      }

      .feature-submit-card:focus-within {
        border-color: rgba(99, 102, 241, 0.4);
        box-shadow: 0 14px 40px rgba(99, 102, 241, 0.08);
      }

      .feature-submit-header {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 20px;
      }

      .feature-submit-icon {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
        color: #fff;
        font-size: 1.2rem;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
        flex-shrink: 0;
      }

      .feature-submit-title {
        font-size: 1.15rem;
        font-weight: 700;
        color: var(--text-color, #0f172a);
        margin: 0;
      }

      .feature-submit-subtitle {
        font-size: 0.85rem;
        color: var(--muted-text, #64748b);
        margin: 2px 0 0;
      }

      .feature-form-group {
        margin-bottom: 16px;
      }

      .feature-form-label {
        display: block;
        font-size: 0.84rem;
        font-weight: 700;
        color: var(--text-color, #334155);
        margin-bottom: 6px;
      }

      .feature-form-input,
      .feature-form-textarea {
        width: 100%;
        padding: 12px 16px;
        border-radius: 12px;
        border: 1px solid var(--border-color, rgba(148, 163, 184, 0.25));
        background: var(--input-bg, rgba(248, 250, 252, 0.8));
        color: var(--text-color, #0f172a);
        font-size: 0.95rem;
        font-family: inherit;
        transition: all 0.25s ease;
        outline: none;
      }

      .feature-form-input:focus,
      .feature-form-textarea:focus {
        background: var(--input-focus-bg, #ffffff);
        border-color: #6366f1;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
      }

      .feature-form-textarea {
        resize: vertical;
        min-height: 90px;
      }

      .btn-submit-proposal {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 11px 24px;
        border-radius: 12px;
        font-weight: 700;
        font-size: 0.92rem;
        background: linear-gradient(135deg, #4f46e5, #6366f1);
        color: #fff !important;
        border: none;
        box-shadow: 0 4px 14px rgba(79, 70, 229, 0.3);
        cursor: pointer;
        transition: all 0.25s ease;
      }

      .btn-submit-proposal:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(79, 70, 229, 0.4);
      }

      /* Proposal List & Cards (ProductHunt Style) */
      .features-feed-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        flex-wrap: wrap;
        gap: 12px;
      }

      .features-feed-title {
        font-size: 1.3rem;
        font-weight: 800;
        color: var(--text-color, #0f172a);
        margin: 0;
        display: flex;
        align-items: center;
        gap: 8px;
      }

      .features-feed-count {
        font-size: 0.85rem;
        font-weight: 700;
        padding: 4px 12px;
        border-radius: 999px;
        background: rgba(99, 102, 241, 0.1);
        color: #4f46e5;
        border: 1px solid rgba(99, 102, 241, 0.2);
      }

      .feature-list {
        display: flex;
        flex-direction: column;
        gap: 20px;
      }

      /* Main Proposal Item */
      .feature-card {
        background: var(--card-bg, rgba(255, 255, 255, 0.9));
        border: 1px solid var(--border-color, rgba(148, 163, 184, 0.25));
        border-radius: 20px;
        padding: 24px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        display: flex;
        gap: 20px;
        transition: all 0.25s ease;
      }

      .feature-card:hover {
        border-color: rgba(99, 102, 241, 0.35);
        box-shadow: 0 8px 30px rgba(99, 102, 241, 0.06);
        transform: translateY(-2px);
      }

      /* Left Side Tactile Upvote Box */
      .feature-vote-box {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        width: 68px;
        min-width: 68px;
        height: 76px;
        border-radius: 16px;
        border: 1.5px solid var(--border-color, rgba(148, 163, 184, 0.3));
        background: var(--vote-bg, rgba(248, 250, 252, 0.8));
        color: var(--text-color, #1e293b);
        cursor: pointer;
        padding: 8px 6px;
        transition: all 0.25s ease;
        text-decoration: none;
      }

      .feature-vote-box:hover {
        border-color: #6366f1;
        background: rgba(99, 102, 241, 0.08);
        color: #4f46e5;
        transform: scale(1.04);
      }

      .feature-vote-box.is-voted {
        background: linear-gradient(135deg, #4f46e5, #6366f1);
        border-color: #4f46e5;
        color: #ffffff !important;
        box-shadow: 0 4px 14px rgba(79, 70, 229, 0.35);
      }

      .feature-vote-box.is-voted:hover {
        background: linear-gradient(135deg, #4338ca, #4f46e5);
      }

      .vote-icon {
        font-size: 1.1rem;
        margin-bottom: 2px;
        transition: transform 0.2s ease;
      }

      .feature-vote-box:hover .vote-icon {
        transform: translateY(-2px);
      }

      .vote-count {
        font-size: 1rem;
        font-weight: 800;
        line-height: 1;
      }

      .vote-label {
        font-size: 0.65rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        margin-top: 3px;
        opacity: 0.85;
      }

      /* Proposal Main Details */
      .feature-content {
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 10px;
        min-width: 0;
      }

      .feature-header-row {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 12px;
        flex-wrap: wrap;
      }

      .feature-title-text {
        font-size: 1.12rem;
        font-weight: 800;
        color: var(--text-color, #0f172a);
        margin: 0 0 4px;
        line-height: 1.35;
      }

      .feature-author-meta {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 0.82rem;
        color: var(--muted-text, #64748b);
        flex-wrap: wrap;
      }

      .feature-user-avatar {
        width: 22px;
        height: 22px;
        border-radius: 50%;
        background: linear-gradient(135deg, #0ea5e9, #0284c7);
        color: #fff;
        font-size: 0.68rem;
        font-weight: 800;
        display: inline-flex;
        align-items: center;
        justify-content: center;
      }

      /* Status Badges */
      .feature-status-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 12px;
        border-radius: 999px;
        font-size: 0.78rem;
        font-weight: 700;
        white-space: nowrap;
      }

      .feature-body-text {
        font-size: 0.94rem;
        line-height: 1.55;
        color: var(--text-color, #334155);
        margin: 2px 0 0;
        white-space: pre-line;
      }

      /* Admin Official Note Callout */
      .feature-admin-callout {
        margin-top: 10px;
        background: rgba(99, 102, 241, 0.06);
        border-left: 3px solid #6366f1;
        border-radius: 4px 12px 12px 4px;
        padding: 10px 14px;
        font-size: 0.88rem;
        color: var(--text-color, #1e293b);
      }

      .feature-admin-callout strong {
        color: #4f46e5;
        display: flex;
        align-items: center;
        gap: 6px;
        margin-bottom: 2px;
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.03em;
      }

      /* Bottom Action Bar */
      .feature-action-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
        margin-top: 8px;
        padding-top: 12px;
        border-top: 1px solid var(--border-soft, rgba(148, 163, 184, 0.12));
      }

      .feature-admin-reply-form {
        display: flex;
        gap: 8px;
        flex: 1;
        min-width: 260px;
      }

      .feature-reply-input {
        flex: 1;
        padding: 8px 14px;
        border-radius: 10px;
        border: 1px solid var(--border-color, rgba(148, 163, 184, 0.25));
        background: var(--input-bg, rgba(248, 250, 252, 0.8));
        color: var(--text-color, #0f172a);
        font-size: 0.88rem;
        outline: none;
        transition: all 0.2s ease;
      }

      .feature-reply-input:focus {
        border-color: #6366f1;
        background: var(--input-focus-bg, #ffffff);
      }

      .btn-reply-send {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 8px 16px;
        border-radius: 10px;
        font-weight: 700;
        font-size: 0.82rem;
        background: rgba(99, 102, 241, 0.1);
        color: #4f46e5;
        border: 1px solid rgba(99, 102, 241, 0.25);
        cursor: pointer;
        transition: all 0.2s ease;
        white-space: nowrap;
      }

      .btn-reply-send:hover {
        background: #4f46e5;
        color: #fff;
      }

      .btn-delete-proposal {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 6px 12px;
        border-radius: 8px;
        font-size: 0.8rem;
        font-weight: 600;
        background: transparent;
        color: #ef4444;
        border: 1px solid rgba(239, 68, 68, 0.25);
        cursor: pointer;
        transition: all 0.2s ease;
      }

      .btn-delete-proposal:hover {
        background: #ef4444;
        color: #fff;
      }

      /* Replies Stream (Javoblar) */
      .feature-replies-block {
        margin-top: 14px;
        padding-top: 14px;
        border-top: 1px dashed var(--border-soft, rgba(148, 163, 184, 0.2));
      }

      .feature-replies-header {
        font-size: 0.82rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: var(--muted-text, #64748b);
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        gap: 6px;
      }

      .feature-replies-list {
        display: flex;
        flex-direction: column;
        gap: 10px;
      }

      .feature-reply-item {
        border-radius: 14px;
        padding: 14px 16px;
        border: 1px solid var(--border-color, rgba(148, 163, 184, 0.2));
        background: var(--reply-bg, rgba(248, 250, 252, 0.7));
        display: flex;
        flex-direction: column;
        gap: 8px;
        position: relative;
      }

      .feature-reply-item.is-super-admin {
        background: linear-gradient(135deg, rgba(88, 28, 135, 0.08), rgba(30, 64, 175, 0.06));
        border-color: rgba(99, 102, 241, 0.35);
        box-shadow: 0 4px 16px rgba(99, 102, 241, 0.06);
      }

      .feature-reply-item.is-admin {
        background: linear-gradient(135deg, rgba(15, 118, 110, 0.08), rgba(2, 132, 199, 0.06));
        border-color: rgba(6, 182, 212, 0.35);
        box-shadow: 0 4px 16px rgba(6, 182, 212, 0.06);
      }

      .feature-reply-head-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
      }

      .feature-reply-user {
        display: flex;
        align-items: center;
        gap: 8px;
        font-weight: 700;
        font-size: 0.88rem;
        color: var(--text-color, #0f172a);
      }

      .badge-role-pill {
        font-size: 0.7rem;
        font-weight: 800;
        text-transform: uppercase;
        padding: 2px 8px;
        border-radius: 6px;
      }

      .badge-role-superadmin {
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
        color: #fff;
      }

      .badge-role-admin {
        background: linear-gradient(135deg, #0ea5e9, #0284c7);
        color: #fff;
      }

      .badge-role-mod {
        background: linear-gradient(135deg, #f59e0b, #d97706);
        color: #fff;
      }

      .feature-reply-text {
        font-size: 0.9rem;
        line-height: 1.5;
        color: var(--text-color, #1e293b);
        margin: 0;
      }

      .feature-reply-date {
        font-size: 0.75rem;
        color: var(--muted-text, #64748b);
      }

      .btn-reply-delete {
        background: transparent;
        border: none;
        color: #ef4444;
        font-size: 0.75rem;
        font-weight: 600;
        cursor: pointer;
        padding: 2px 6px;
        border-radius: 4px;
        transition: all 0.2s ease;
      }

      .btn-reply-delete:hover {
        background: rgba(239, 68, 68, 0.1);
      }

      /* Mobile Layout adjustments */
      @media (max-width: 640px) {
        .feature-card {
          flex-direction: column;
          gap: 14px;
          padding: 18px 16px;
        }

        .feature-vote-box {
          width: 100%;
          height: auto;
          flex-direction: row;
          justify-content: center;
          gap: 10px;
          padding: 10px 16px;
        }

        .feature-vote-box .vote-label {
          margin-top: 0;
        }

        .feature-admin-reply-form {
          width: 100%;
          min-width: 100%;
        }
      }

      /* ====================================================================
         Dark Mode Overrides
         ==================================================================== */
      :root[data-theme='dark'] .feature-submit-card,
      :root[data-theme='dark'] .feature-card {
        background: linear-gradient(180deg, rgba(15, 27, 45, 0.95), rgba(10, 19, 33, 0.98)) !important;
        border-color: rgba(255, 255, 255, 0.08) !important;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3) !important;
      }

      :root[data-theme='dark'] .feature-vote-box {
        background: rgba(15, 23, 42, 0.6) !important;
        border-color: rgba(255, 255, 255, 0.1) !important;
        color: #f1f5f9 !important;
      }

      :root[data-theme='dark'] .feature-vote-box:hover {
        background: rgba(99, 102, 241, 0.2) !important;
        border-color: #818cf8 !important;
      }

      :root[data-theme='dark'] .feature-vote-box.is-voted {
        background: linear-gradient(135deg, #4f46e5, #6366f1) !important;
        border-color: #6366f1 !important;
        color: #ffffff !important;
      }

      :root[data-theme='dark'] .feature-form-input,
      :root[data-theme='dark'] .feature-form-textarea,
      :root[data-theme='dark'] .feature-reply-input {
        background: rgba(15, 23, 42, 0.6) !important;
        border-color: rgba(255, 255, 255, 0.1) !important;
        color: #f8fafc !important;
      }

      :root[data-theme='dark'] .feature-form-input:focus,
      :root[data-theme='dark'] .feature-form-textarea:focus,
      :root[data-theme='dark'] .feature-reply-input:focus {
        background: rgba(15, 23, 42, 0.9) !important;
        border-color: #818cf8 !important;
      }

      :root[data-theme='dark'] .feature-submit-title,
      :root[data-theme='dark'] .features-feed-title,
      :root[data-theme='dark'] .feature-title-text,
      :root[data-theme='dark'] .feature-reply-user {
        color: #f8fafc !important;
      }

      :root[data-theme='dark'] .feature-body-text,
      :root[data-theme='dark'] .feature-reply-text {
        color: #cbd5e1 !important;
      }

      :root[data-theme='dark'] .feature-form-label {
        color: #94a3b8 !important;
      }

      :root[data-theme='dark'] .feature-reply-item {
        background: rgba(15, 23, 42, 0.5) !important;
        border-color: rgba(255, 255, 255, 0.08) !important;
      }

      :root[data-theme='dark'] .feature-admin-callout {
        background: rgba(99, 102, 241, 0.12) !important;
        border-left-color: #818cf8 !important;
        color: #e2e8f0 !important;
      }

      :root[data-theme='dark'] .feature-admin-callout strong {
        color: #a5b4fc !important;
      }
    </style>
  @endpush

  {{-- Hero Header --}}
  <section class="news-hero" id="home">
    <div class="container">
      <div class="news-hero-content prime-reveal">
        <span class="badge"><i class="fa-solid fa-lightbulb me-1"></i> {{ __('public.feature_requests.badge') }}</span>
        <h1 class="js-split-text">{{ __('public.feature_requests.hero_title') }}</h1>
        <p>{{ __('public.feature_requests.hero_text') }}</p>
      </div>
    </div>
  </section>

  <main class="features-page-wrapper">
    <div class="container">

      {{-- 1. Submit Proposal Form --}}
      @auth
        <div class="feature-submit-card prime-reveal">
          <div class="feature-submit-header">
            <div class="feature-submit-icon">
              <i class="fa-solid fa-wand-magic-sparkles"></i>
            </div>
            <div>
              <h3 class="feature-submit-title">Yangi taklif yoki g'oya qoldiring</h3>
              <p class="feature-submit-subtitle">Qanday qulaylik yoki yangilik saytimizni yanada yaxshilaydi deb o'ylaysiz?</p>
            </div>
          </div>

          <form method="POST" action="{{ route('feature-requests.store') }}">
            @csrf
            <div class="feature-form-group">
              <label class="feature-form-label" for="feature-title">{{ __('public.feature_requests.title_label') }}</label>
              <input id="feature-title" type="text" name="title" class="feature-form-input" maxlength="180" required value="{{ old('title') }}" placeholder="{{ __('public.feature_requests.title_placeholder') }}">
            </div>

            <div class="feature-form-group">
              <label class="feature-form-label" for="feature-description">{{ __('public.feature_requests.description_label') }}</label>
              <textarea id="feature-description" name="description" class="feature-form-textarea" rows="3" maxlength="3000" placeholder="{{ __('public.feature_requests.description_placeholder') }}">{{ old('description') }}</textarea>
            </div>

            <button type="submit" class="btn-submit-proposal">
              <i class="fa-solid fa-paper-plane"></i> {{ __('public.feature_requests.submit') }}
            </button>
          </form>
        </div>
      @else
        <div class="feature-submit-card text-center prime-reveal" style="padding: 32px 20px;">
          <i class="fa-solid fa-lock mb-2" style="font-size: 2.2rem; color: #6366f1; opacity: 0.8;"></i>
          <h3 class="feature-submit-title">{{ __('public.feature_requests.login_required') }}</h3>
          <p class="feature-submit-subtitle mb-3">Taklif qoldirish va boshqalarning g'oyalariga ovoz berish uchun tizimga kiring.</p>
          <a href="{{ route('login') }}" class="btn-submit-proposal" style="text-decoration: none;">
            <i class="fa-solid fa-right-to-bracket"></i> {{ __('public.feature_requests.login') }}
          </a>
        </div>
      @endauth

      {{-- 2. Proposals Feed Header --}}
      <div class="features-feed-header prime-reveal">
        <h2 class="features-feed-title">
          <i class="fa-solid fa-fire text-primary"></i> {{ __('public.feature_requests.list_title') }}
        </h2>
        <span class="features-feed-count">
          Jami: {{ $featureRequests->total() }} ta taklif
        </span>
      </div>

      {{-- 3. Proposals List --}}
      @if($featureRequests->count() === 0)
        <div class="feature-submit-card text-center" style="padding: 48px 20px;">
          <i class="fa-solid fa-comment-dots mb-3" style="font-size: 3rem; opacity: 0.3; color: #64748b;"></i>
          <h3 class="feature-submit-title" style="color: var(--muted-text, #64748b);">{{ __('public.feature_requests.empty') }}</h3>
        </div>
      @else
        <div class="feature-list">
          @foreach($featureRequests as $requestItem)
            @php
              $authorName = trim((string) ($requestItem->user->first_name ?? '').' '.(string) ($requestItem->user->last_name ?? ''));
              if ($authorName === '') {
                  $authorName = $requestItem->user->name ?? __('public.feature_requests.default_user');
              }
              $authorInitial = mb_strtoupper(mb_substr($authorName, 0, 1));
              $hasVoted = in_array($requestItem->id, $votedRequestIds, true);
              
              $statusLabel = match ($requestItem->status) {
                \App\Models\FeatureRequest::STATUS_PLANNED => __('public.feature_requests.status_planned'),
                \App\Models\FeatureRequest::STATUS_IN_PROGRESS => __('public.feature_requests.status_progress'),
                \App\Models\FeatureRequest::STATUS_DONE => __('public.feature_requests.status_done'),
                \App\Models\FeatureRequest::STATUS_REJECTED => __('public.feature_requests.status_rejected'),
                default => __('public.feature_requests.status_review'),
              };
              
              $statusStyle = match ($requestItem->status) {
                \App\Models\FeatureRequest::STATUS_PLANNED => 'background:rgba(59,130,246,0.12); color:#2563eb; border:1px solid rgba(59,130,246,0.25);',
                \App\Models\FeatureRequest::STATUS_IN_PROGRESS => 'background:rgba(14,165,233,0.12); color:#0284c7; border:1px solid rgba(14,165,233,0.25);',
                \App\Models\FeatureRequest::STATUS_DONE => 'background:rgba(16,185,129,0.12); color:#059669; border:1px solid rgba(16,185,129,0.25);',
                \App\Models\FeatureRequest::STATUS_REJECTED => 'background:rgba(239,68,68,0.12); color:#dc2626; border:1px solid rgba(239,68,68,0.25);',
                default => 'background:rgba(245,158,11,0.12); color:#d97706; border:1px solid rgba(245,158,11,0.25);',
              };

              $statusIcon = match ($requestItem->status) {
                \App\Models\FeatureRequest::STATUS_PLANNED => 'fa-calendar-check',
                \App\Models\FeatureRequest::STATUS_IN_PROGRESS => 'fa-bolt',
                \App\Models\FeatureRequest::STATUS_DONE => 'fa-circle-check',
                \App\Models\FeatureRequest::STATUS_REJECTED => 'fa-circle-xmark',
                default => 'fa-hourglass-half',
              };

              $canVote = in_array((string) $requestItem->status, \App\Models\FeatureRequest::VOTABLE_STATUSES, true) && $requestItem->is_active;
              $canReply = auth()->check() && (auth()->user()->isSuperAdmin() || auth()->user()->isAdmin() || auth()->user()->hasRole(\App\Models\User::ROLE_MODERATOR));
              $canModerate = auth()->check() && (auth()->user()->isAdmin() || auth()->user()->isSuperAdmin());
              $canDelete = auth()->check() && ($canModerate || (int) auth()->id() === (int) $requestItem->user_id);
            @endphp

            <article class="feature-card">
              {{-- Left Tactile Upvote Button --}}
              <div>
                @auth
                  @if($canVote)
                    <form method="POST" action="{{ route('feature-requests.vote', $requestItem) }}" style="margin:0;">
                      @csrf
                      <button type="submit" class="feature-vote-box {{ $hasVoted ? 'is-voted' : '' }}" title="{{ $hasVoted ? 'Ovozni bekor qilish' : 'Ovoz berish' }}">
                        <i class="fa-solid {{ $hasVoted ? 'fa-check' : 'fa-chevron-up' }} vote-icon"></i>
                        <span class="vote-count">{{ (int) $requestItem->votes_count }}</span>
                        <span class="vote-label">{{ $hasVoted ? 'Ovoz berildi' : 'Ovoz' }}</span>
                      </button>
                    </form>
                  @else
                    <div class="feature-vote-box" style="opacity: 0.6; cursor: default;">
                      <i class="fa-solid fa-lock vote-icon"></i>
                      <span class="vote-count">{{ (int) $requestItem->votes_count }}</span>
                      <span class="vote-label">Yopiq</span>
                    </div>
                  @endif
                @else
                  <a href="{{ route('login') }}" class="feature-vote-box" title="Ovoz berish uchun tizimga kiring">
                    <i class="fa-solid fa-chevron-up vote-icon"></i>
                    <span class="vote-count">{{ (int) $requestItem->votes_count }}</span>
                    <span class="vote-label">Ovoz</span>
                  </a>
                @endauth
              </div>

              {{-- Main Content Column --}}
              <div class="feature-content">
                {{-- Header Row: Title & Status --}}
                <div class="feature-header-row">
                  <div>
                    <h3 class="feature-title-text">{{ $requestItem->title }}</h3>
                    <div class="feature-author-meta">
                      <span class="feature-user-avatar">{{ $authorInitial }}</span>
                      <span style="font-weight: 600; color: var(--text-color, #334155);">{{ $authorName }}</span>
                      <span>·</span>
                      <span>{{ $requestItem->created_at?->format('d.m.Y H:i') }}</span>
                    </div>
                  </div>

                  <div>
                    <span class="feature-status-badge" style="{{ $statusStyle }}">
                      <i class="fa-solid {{ $statusIcon }}"></i> {{ $statusLabel }}
                    </span>
                    @if($requestItem->announced_at)
                      <span class="feature-status-badge" style="background:rgba(99,102,241,0.08); color:#6366f1; border:1px solid rgba(99,102,241,0.2); margin-left:4px;">
                        <i class="fa-solid fa-bullhorn"></i> {{ $requestItem->announced_at->format('d.m.Y') }}
                      </span>
                    @endif
                  </div>
                </div>

                {{-- Description Body --}}
                @if($requestItem->description)
                  <p class="feature-body-text">{{ $requestItem->description }}</p>
                @endif

                {{-- Official Admin Sticky Note --}}
                @if($requestItem->admin_note)
                  <div class="feature-admin-callout">
                    <strong><i class="fa-solid fa-shield-check"></i> {{ __('public.feature_requests.admin_note') }}</strong>
                    <div>{{ $requestItem->admin_note }}</div>
                  </div>
                @endif

                {{-- Admin Reply Input & Actions --}}
                @auth
                  <div class="feature-action-bar">
                    @if($canReply)
                      <form method="POST" action="{{ route('feature-requests.replies.store', $requestItem) }}" class="feature-admin-reply-form">
                        @csrf
                        <input type="text" name="message" class="feature-reply-input" maxlength="3000" required placeholder="{{ __('public.feature_requests.reply_placeholder') }}">
                        <button type="submit" class="btn-reply-send">
                          <i class="fa-solid fa-reply"></i> {{ __('public.feature_requests.reply_submit') }}
                        </button>
                      </form>
                    @endif

                    @if($canDelete)
                      <div class="ms-auto">
                        <form method="POST" action="{{ route('feature-requests.destroy', $requestItem) }}" style="margin:0;"
                          data-confirm="{{ __('public.feature_requests.delete_confirm') }}"
                          data-confirm-title="{{ __('public.feature_requests.delete_title') }}"
                          data-confirm-variant="danger"
                          data-confirm-ok="{{ __('public.feature_requests.delete_ok') }}">
                          @csrf
                          @method('DELETE')
                          <button type="submit" class="btn-delete-proposal" title="{{ __('public.feature_requests.delete_tip') }}">
                            <i class="fa-solid fa-trash-can"></i> {{ __('public.feature_requests.delete') }}
                          </button>
                        </form>
                      </div>
                    @endif
                  </div>
                @endauth

                {{-- Replies Stream (Javoblar) --}}
                @if(($requestItem->replies ?? collect())->isNotEmpty())
                  <div class="feature-replies-block">
                    <div class="feature-replies-header">
                      <i class="fa-solid fa-comments"></i> {{ __('public.feature_requests.replies') }} ({{ $requestItem->replies->count() }})
                    </div>
                    <div class="feature-replies-list">
                      @foreach($requestItem->replies as $reply)
                        @php
                          $replyAuthor = trim((string) ($reply->user->first_name ?? '').' '.(string) ($reply->user->last_name ?? ''));
                          if ($replyAuthor === '') {
                            $replyAuthor = $reply->user->name ?? __('public.feature_requests.default_staff');
                          }
                          $replyInitial = mb_strtoupper(mb_substr($replyAuthor, 0, 1));
                          $isSuperAdminReply = (bool) ($reply->user?->isSuperAdmin());
                          $isAdminReply = $isSuperAdminReply || (bool) ($reply->user?->isAdmin());
                          $isModeratorReply = (bool) ($reply->user?->hasRole(\App\Models\User::ROLE_MODERATOR));
                          $replyRoleLabel = $isSuperAdminReply
                            ? 'Super Admin'
                            : ($isAdminReply
                                ? 'Admin'
                                : ($isModeratorReply ? 'Moderator' : 'Foydalanuvchi'));
                          
                          $roleClass = $isSuperAdminReply ? 'badge-role-superadmin' : ($isAdminReply ? 'badge-role-admin' : ($isModeratorReply ? 'badge-role-mod' : ''));
                          $cardClass = $isSuperAdminReply ? 'is-super-admin' : ($isAdminReply ? 'is-admin' : '');
                        @endphp
                        <div class="feature-reply-item {{ $cardClass }}">
                          <div class="feature-reply-head-row">
                            <div class="feature-reply-user">
                              <span class="feature-user-avatar" style="background: linear-gradient(135deg, #6366f1, #8b5cf6);">{{ $replyInitial }}</span>
                              <span>{{ $replyAuthor }}</span>
                              <span class="badge-role-pill {{ $roleClass }}">{{ $replyRoleLabel }}</span>
                            </div>
                            
                            <div class="d-flex align-items-center gap-2">
                              <span class="feature-reply-date">{{ $reply->created_at?->format('d.m.Y H:i') }}</span>
                              @auth
                                @php
                                  $canDeleteReply = (int) auth()->id() === (int) $reply->user_id || (auth()->user()->isAdmin() || auth()->user()->isSuperAdmin());
                                @endphp
                                @if($canDeleteReply)
                                  <form method="POST" action="{{ route('feature-requests.replies.destroy', $reply) }}" style="margin:0;"
                                    data-confirm="{{ __('public.feature_requests.reply_delete_confirm') }}"
                                    data-confirm-title="{{ __('public.feature_requests.reply_delete_title') }}"
                                    data-confirm-variant="danger"
                                    data-confirm-ok="{{ __('public.feature_requests.delete_ok') }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-reply-delete" title="Javobni o'chirish">
                                      <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                  </form>
                                @endif
                              @endauth
                            </div>
                          </div>
                          <p class="feature-reply-text">{{ $reply->message }}</p>
                        </div>
                      @endforeach
                    </div>
                  </div>
                @endif
              </div>
            </article>
          @endforeach
        </div>

        {{-- Pagination --}}
        @if($featureRequests->hasPages())
          <div style="margin-top: 24px; display: flex; justify-content: center;">
            {{ $featureRequests->links() }}
          </div>
        @endif
      @endif

    </div>
  </main>
</x-layouts.main>
