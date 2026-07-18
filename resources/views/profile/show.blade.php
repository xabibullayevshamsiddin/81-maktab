@php
  $profileRoleKey = $user->role ?? 'guest';
  $profileRoleLabelKey = 'profile.roles.' . $profileRoleKey;
  $profileRoleLabel = \Illuminate\Support\Facades\Lang::has($profileRoleLabelKey)
    ? __($profileRoleLabelKey)
    : $user->role_label;
  $donorRank = $user->donation_rank;
  $donorIsActive = $user->isDonor();
  $profileCardStaffClass = match ($profileRoleKey) {
    'super_admin' => 'profile-card--super-admin',
    'admin' => 'profile-card--admin',
    'moderator' => 'profile-card--moderator',
    default => '',
  };
  $profileOverviewDonorClass = $donorIsActive ? 'profile-overview--donor profile-overview--donor-' . $donorRank : '';
  $profileOverviewThemeClass = $user->donorThemeClass();

  $profileInitial = \Illuminate\Support\Str::upper(
    \Illuminate\Support\Str::substr(trim((string) ($user->name ?: 'U')), 0, 1)
  );
  $profileAvatarUrl = $user->avatar_url;
  $profileGradeLabel = $user->displayGrade(__('public.common.not_entered'));
  $profilePanel = $panel ?? 'settings';

  $activityPreviewLimit = 8;
  $activityStep = 8;
  $hasCreatedCourseForEnrollments = (bool) ($canViewCourseEnrollments ?? false);
  $canViewCourseEnrollments = $user->isAdmin() || $hasCreatedCourseForEnrollments;


  $profileStats = [
    [
      'icon' => 'fa-regular fa-comments',
      'value' => $postCommentCount + $teacherCommentCount,
      'label' => __('profile.stats.comments'),
    ],
    [
      'icon' => 'fa-solid fa-book-open',
      'value' => $courseEnrollmentCount,
      'label' => __('profile.stats.enrolled_courses'),
    ],
    [
      'icon' => $canViewCourseEnrollments ? 'fa-solid fa-clipboard-check' : 'fa-solid fa-layer-group',
      'value' => $canViewCourseEnrollments ? $pendingTeacherEnrollmentCount : $createdCourseCount,
      'label' => $canViewCourseEnrollments ? __('profile.stats.pending_requests') : __('profile.stats.created_courses'),
    ],
  ];

  $profileFacts = [
    [
      'icon' => 'fa-solid fa-envelope',
      'label' => __('profile.facts.current_email.label'),
      'value' => $user->email,
      'hint' => __('profile.facts.current_email.hint'),
      'track_email' => true,
    ],
    [
      'icon' => 'fa-solid fa-phone',
      'label' => __('profile.facts.phone.label'),
      'value' => $user->phone ?: __('public.common.not_entered'),
      'hint' => __('profile.facts.phone.hint'),
    ],
    [
      'icon' => 'fa-solid fa-user-graduate',
      'label' => __('profile.facts.grade.label'),
      'value' => $profileGradeLabel,
      'hint' => __('profile.facts.grade.hint'),
    ],
    [
      'icon' => 'fa-solid fa-user-shield',
      'label' => __('profile.facts.role.label'),
      'value' => $profileRoleLabel,
      'hint' => __('profile.facts.role.hint'),
    ],
    [
      'icon' => 'fa-solid fa-star',
      'label' => 'Donor reytingi',
      'value' => $user->isDonor()
        ? $user->donorRankLabel() . ' ' . $user->donorBadgeHtml()
        : 'Mavjud emas',
      'hint' => $user->isDonor() && $user->donation_rank_expires_at ? 'Tugash vaqti: ' . $user->donation_rank_expires_at->diffForHumans() : 'Donor bolish orqali imtiyozlarga ega boling',
    ],
  ];

  $profileI18n = [
    'showPassword' => __('profile.password_card.show_password'),
    'hidePassword' => __('profile.password_card.hide_password'),
    'preparingAvatar' => __('profile.js.preparing_avatar'),
    'avatarReady' => __('profile.js.avatar_ready'),
    'avatarFallback' => __('profile.js.avatar_fallback'),
    'avatarRemoved' => 'Rasm olib tashlanadi. Saqlasangiz bosh harf ko‘rinadi.',
    'avatarTooBig' => __('profile.js.avatar_too_big'),
    'saveError' => __('profile.js.save_error'),
    'saved' => __('profile.js.saved'),
    'serverError' => __('profile.js.server_error'),
  ];
@endphp

<x-layouts.main :title="__('profile.page_title')">
  @push('page_styles')
    <link rel="stylesheet"
      href="{{ app_public_asset('temp/css/profile-fix.css') }}?v={{ app_asset_version('temp/css/profile-fix.css') }}">
    <style>
      .page-header .header-main {
        width: calc(100% - 40px) !important;
        max-width: 1140px !important;
        top: 18px !important;
      }

      .profile-hero {
        padding-top: 150px !important;
        padding-bottom: 80px !important;
        display: block !important;
        min-height: auto !important;
      }

      .news-hero-content {
        margin-top: 20px !important;
        position: relative !important;
        z-index: 10 !important;
      }

      .profile-overview-panel {
        margin-top: 0 !important;
        position: relative !important;
        z-index: 20 !important;
      }

      /* ===== TIMELINE ===== */
      .profile-activity-block--timeline {
        background: linear-gradient(180deg, rgba(99,102,241,0.03) 0%, rgba(99,102,241,0) 100%);
        border: 1px solid rgba(99,102,241,0.12);
      }
      .profile-timeline {
        position: relative;
        padding-left: 18px;
      }
      .profile-timeline::before {
        content: '';
        position: absolute;
        left: 0;
        top: 4px;
        bottom: 4px;
        width: 3px;
        background: linear-gradient(180deg, #6366f1, #a78bfa);
        border-radius: 999px;
      }
      .profile-timeline-item {
        position: relative;
        padding-left: 22px;
        margin-bottom: 1.5rem;
        animation: timelineItemIn 0.55s cubic-bezier(0.22, 1, 0.36, 1) both;
      }
      .profile-timeline-item:last-child {
        margin-bottom: 0;
      }
      .profile-timeline-item:nth-child(1) { animation-delay: 0.05s; }
      .profile-timeline-item:nth-child(2) { animation-delay: 0.12s; }
      .profile-timeline-item:nth-child(3) { animation-delay: 0.19s; }
      .profile-timeline-item:nth-child(4) { animation-delay: 0.26s; }
      .profile-timeline-item:nth-child(5) { animation-delay: 0.33s; }
      .profile-timeline-item:nth-child(6) { animation-delay: 0.40s; }
      .profile-timeline-item:nth-child(7) { animation-delay: 0.47s; }
      .profile-timeline-item:nth-child(8) { animation-delay: 0.54s; }

      @keyframes timelineItemIn {
        from { opacity: 0; transform: translateX(-16px) scale(0.96); }
        to   { opacity: 1; transform: translateX(0) scale(1); }
      }
      .profile-timeline-marker {
        position: absolute;
        left: -22px;
        top: 14px;
      }
      .profile-timeline-dot {
        display: inline-flex;
        width: 14px;
        height: 14px;
        border-radius: 50%;
        background: #fff;
        border: 3px solid var(--act-color, #6366f1);
        box-shadow: 0 0 0 3px color-mix(in srgb, var(--act-color, #6366f1) 25%, transparent);
        transition: transform 0.2s ease;
      }
      .profile-timeline-item:hover .profile-timeline-dot {
        transform: scale(1.25);
      }
      .profile-timeline-pulse {
        position: absolute;
        top: 50%;
        left: 50%;
        width: 30px;
        height: 30px;
        border-radius: 50%;
        background: color-mix(in srgb, var(--act-color, #6366f1) 12%, transparent);
        transform: translate(-50%, -50%);
        animation: pulse 2.6s ease-in-out infinite;
      }
      @keyframes pulse {
        0%   { transform: translate(-50%, -50%) scale(0.8); opacity: 0.7; }
        50%  { transform: translate(-50%, -50%) scale(1.3); opacity: 0; }
        100% { transform: translate(-50%, -50%) scale(0.8); opacity: 0; }
      }
      .profile-timeline-card {
        background: var(--bg);
        border: 1px solid var(--border);
        border-radius: 16px;
        padding: 14px 16px;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.06);
        transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
      }
      .profile-timeline-item:hover .profile-timeline-card {
        transform: translateY(-3px);
        box-shadow: 0 18px 40px rgba(15, 23, 42, 0.10);
        border-color: color-mix(in srgb, var(--act-color, #6366f1) 40%, var(--border));
      }
      .profile-timeline-card-head {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
        margin-bottom: 8px;
      }
      .profile-timeline-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 38px;
        height: 38px;
        border-radius: 12px;
        font-size: 16px;
        flex-shrink: 0;
      }
      .profile-timeline-title {
        font-weight: 700;
        color: var(--text);
        flex: 1 1 auto;
        min-width: 0;
      }
      .profile-timeline-badge {
        display: inline-flex;
        align-items: center;
        padding: 4px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
        letter-spacing: 0.02em;
      }
      .profile-timeline-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        color: var(--muted);
        font-size: 12.5px;
      }
      .profile-timeline-meta > span {
        display: inline-flex;
        align-items: center;
        gap: 6px;
      }
      .profile-timeline-time,
      .profile-timeline-device,
      .profile-timeline-ip {
        opacity: 0.9;
      }
      .profile-timeline-diff {
        margin-top: 10px;
        padding: 10px 12px;
        border-radius: 12px;
        background: var(--bg);
        border: 1px dashed var(--border);
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        font-size: 12px;
        color: var(--text);
      }
      .profile-timeline-diff-old,
      .profile-timeline-diff-new {
        display: inline-flex;
        align-items: center;
        gap: 6px;
      }
      .profile-timeline-diff-old {
        color: #ef4444;
      }
      .profile-timeline-diff-new {
        color: #22c55e;
      }

      @media (max-width: 768px) {
        .profile-timeline {
          padding-left: 10px;
        }
        .profile-timeline-item {
          padding-left: 16px;
        }
        .profile-timeline-marker {
          left: -16px;
        }
        .profile-timeline-card {
          padding: 12px;
        }
      }

      @media (max-width: 991px) {
        .page-header .header-main {
          width: calc(100% - 20px) !important;
          top: 10px !important;
        }

        .profile-hero {
          padding-top: 120px !important;
          padding-bottom: 100px !important;
        }

        .news-hero-content {
          margin-top: 10px !important;
        }
      }
    </style>
  @endpush
  <section class="news-hero profile-hero {{ $user->donorThemeClass() }} banner-anim-{{ $user->banner_animation ?? 'none' }}">
    <div class="container">
      @if($user->donorBannerUrl())
        <img src="{{ $user->donorBannerUrl() }}" alt="Banner" class="donor-banner banner-anim-{{ $user->banner_animation ?? 'none' }}">
      @endif
      <div class="news-hero-content reveal">
        <span class="badge">{{ __('profile.badge') }}</span>
        <h1 class="js-split-text"><strong>{{ __('profile.title') }}</strong></h1>
        <p>{{ __('profile.intro') }}</p>
      </div>
    </div>
  </section>

  <main class="profile-main" data-profile-i18n='@json($profileI18n)' data-active-panel="{{ $profilePanel }}">
    <div class="container">
      <section class="profile-overview-panel {{ $profileOverviewDonorClass }} {{ $profileOverviewThemeClass }} profile-bg-{{ $user->profile_bg_style ?? 'plain' }}">
        <div class="profile-overview-main">
          <div class="profile-avatar" data-profile-avatar-box data-profile-avatar-initial="{{ $profileInitial }}"
            data-profile-avatar-url="{{ $profileAvatarUrl ?: '' }}">{{ $profileInitial }}</div>

          <div class="profile-overview-copy">
            <div class="profile-overview-headline">
              <span class="profile-kicker">
                <i class="fa-solid fa-id-card"></i>
                {{ __('profile.overview_kicker') }}
              </span>
              <span class="profile-overview-pulse">
                <i class="fa-solid fa-star"></i>
                {{ __('public.profile_hub.center') }}
              </span>
            </div>
            <div class="profile-overview-title-row">
              @php
                $badgePos = $user->badge_position ?? 'after';
                $statusEmoji = $user->status_emoji ?? '';
                $donorBadge = $user->donorBadgeHtml();
              @endphp
              @if($donorBadge && $badgePos === 'before')
                {!! $donorBadge !!}
              @endif
              <h2 class="profile-overview-name" style="color: {{ $user->donorUsernameColor() ?? 'inherit' }}; font-weight: {{ $user->donorIsActive ? ($user->name_font_weight ?? '700') : 'inherit' }};">
                {{ $user->name }}{{ $statusEmoji ? ' '.$statusEmoji : '' }}
              </h2>
              @if($donorBadge && $badgePos !== 'before')
                {!! $donorBadge !!}
              @endif
            </div>
            <p class="profile-overview-intro">
              {{ __('public.profile_hub.intro') }}
            </p>

            <div class="profile-overview-tags">
              <span
                class="profile-role-badge comment-role-badge role-{{ $profileRoleKey }}">{{ $profileRoleLabel }}</span>
              <span class="profile-chip">
                <i class="fa-solid fa-user-graduate"></i>
                {{ $profileGradeLabel }}
              </span>
              <span class="profile-chip">
                <i class="fa-solid fa-phone"></i>
                {{ $user->phone ?: __('profile.phone_missing') }}
              </span>
            </div>
          </div>
        </div>

        <div class="profile-stats-grid">
          @foreach($profileStats as $stat)
            <div class="profile-stat-card stagger-item">
              <span class="profile-stat-icon"><i class="{{ $stat['icon'] }}"></i></span>
              <strong class="num-counter" data-count="{{ $stat['value'] }}">{{ number_format($stat['value']) }}</strong>
              <span>{{ $stat['label'] }}</span>
            </div>
          @endforeach
        </div>
      </section>

      <section class="profile-panel-switcher">
        <div class="profile-panel-switcher-head">
          <span class="profile-panel-switcher-kicker">{{ __('public.profile_hub.sections_kicker') }}</span>
          <p>{{ __('public.profile_hub.sections_hint') }}</p>
        </div>
        <nav class="profile-panel-tabs" aria-label="{{ __('public.profile_hub.sections_aria') }}">
          <a href="{{ route('profile.show', ['panel' => 'settings']) }}" class="profile-panel-tab {{ $profilePanel === 'settings' ? 'is-active' : '' }}">
            <i class="fa-solid fa-user-gear"></i>
            <span>{{ __('public.profile_hub.tab_settings') }}</span>
          </a>
          <a href="{{ route('profile.show', ['panel' => 'security']) }}" class="profile-panel-tab {{ $profilePanel === 'security' ? 'is-active' : '' }}">
            <i class="fa-solid fa-shield-halved"></i>
            <span>{{ __('public.profile_hub.tab_security') }}</span>
          </a>
          <a href="{{ route('profile.results.index') }}" class="profile-panel-tab profile-panel-tab--link">
            <i class="fa-solid fa-chart-column"></i>
            <span>{{ __('public.profile_hub.tab_results') }}</span>
          </a>

          <a href="{{ route('profile.show', ['panel' => 'activity']) }}" class="profile-panel-tab {{ $profilePanel === 'activity' ? 'is-active' : '' }}">
            <i class="fa-solid fa-wave-square"></i>
            <span>{{ __('public.profile_hub.tab_activity') }}</span>
          </a>
          <a href="{{ route('profile.bookmarks.index') }}" class="profile-panel-tab profile-panel-tab--link">
            <i class="fa-solid fa-bookmark"></i>
            <span>{{ __('profile.bookmarks.nav') }}</span>
          </a>
          <a href="{{ route('profile.show', ['panel' => 'appearance']) }}" class="profile-panel-tab {{ $profilePanel === 'appearance' ? 'is-active' : '' }}">
            <i class="fa-solid fa-palette" style="color: #8b5cf6;"></i>
            <span>Donat korinishi</span>
          </a>
        </nav>
      </section>

      <div class="profile-layout profile-layout--stack-mobile">
        <div class="profile-column profile-column-settings profile-column-settings--mobile-last">
          @if($profilePanel === 'settings')
            <!-- Facts Column (Left) -->
            <div class="signin-card profile-card {{ $profileCardStaffClass }}">
              <div class="profile-card-head">
                <span class="profile-card-kicker">{{ __('profile.steps.primary') }}</span>
                <h2>{{ __('public.profile_hub.facts_title') }}</h2>
                <p class="signin-subtitle">{{ __('public.profile_hub.facts_subtitle') }}</p>
              </div>

              <div class="profile-facts">
                @foreach($profileFacts as $fact)
                  <div class="profile-fact-card stagger-item">
                    <span class="profile-fact-icon"><i class="{{ $fact['icon'] }}"></i></span>
                    <span class="profile-fact-label">{{ $fact['label'] }}</span>
                    <strong class="profile-fact-value" @if($fact['track_email'] ?? false) data-profile-user-email
                      @endif>{!! $fact['value'] !!}</strong>
                    <span class="profile-fact-hint">{{ $fact['hint'] }}</span>
                  </div>
                @endforeach
              </div>

              <div class="profile-guide-box">
                <div class="profile-guide-icon">
                  <i class="fa-solid fa-circle-info"></i>
                </div>
                <div class="profile-guide-copy">
                  <span class="profile-guide-kicker">{{ __('profile.main_card.note_title') }}</span>
                  <strong>{{ __('public.profile_hub.help') }}</strong>
                  <p style="font-size: 13px; color: var(--profile-text-muted); line-height: 1.5;">{{ __('public.profile_hub.help_text') }}</p>
                </div>
              </div>
            </div>
          @elseif($profilePanel === 'appearance')
            @include('profile.partials.appearance-card')
          @elseif($profilePanel === 'security')
            @include('profile.partials.email-card')
            @include('profile.partials.password-card')
            @include('profile.partials.app-settings-card')
          @endif
        </div>

        <div class="profile-column profile-column-activity profile-column-activity--mobile-first">
          @if($profilePanel === 'settings')
            <!-- Update Form Column (Right) -->
            <div class="signin-card profile-card">
              <div class="profile-card-head">
                <span class="profile-card-kicker">Tahrirlash</span>
                <h2>{{ __('profile.main_card.title') }}</h2>
                <p class="signin-subtitle">{{ __('profile.main_card.subtitle') }}</p>
              </div>

              <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data"
                class="signin-form comment-form profile-form-stack">
                @csrf
                @method('PUT')

                <div class="profile-avatar-upload">
                  <div class="profile-avatar-upload-copy">
                    @php
                      $avatarMaxKb = $user->donorMaxAvatarSize();
                      $avatarMaxMb = round($avatarMaxKb / 1024);
                    @endphp
                    <div class="profile-field">
                      <label for="profile-avatar">{{ __('profile.main_card.avatar_label') }}</label>
                      <span class="profile-field-hint">{!! __('profile.main_card.avatar_hint', ['max' => $avatarMaxMb]) !!}</span>
                      @if($donorIsActive)
                        <span class="profile-field-hint profile-field-hint--donor" style="color: {{ \App\Models\Donation::configForRank($donorRank)['badge_color'] ?? '#6366f1' }};">
                          <i class="fa-solid {{ \App\Models\Donation::configForRank($donorRank)['badge_icon'] ?? 'fa-star' }}"></i>
                          {!! __('profile.main_card.avatar_donor_bonus', ['max' => $avatarMaxMb, 'rank' => \App\Models\Donation::configForRank($donorRank)['label'] ?? 'Donor']) !!}
                        </span>
                      @endif
                      <input type="hidden" name="remove_avatar" value="0" data-profile-avatar-remove-flag />
                      <input type="file" id="profile-avatar" name="avatar" accept="image/jpeg,image/png,image/webp"
                        data-profile-avatar-max="{{ $avatarMaxKb }}"
                        data-profile-avatar-max-mb="{{ $avatarMaxMb }}" />
                      @if($profileAvatarUrl)
                        <div class="profile-actions-row profile-avatar-actions">
                          <button type="button" class="btn btn-outline btn-sm" data-profile-avatar-remove>
                            Rasmni olib tashlash
                          </button>
                        </div>
                      @endif
                      <span class="profile-avatar-meta"
                        data-profile-avatar-meta>{{ __('profile.main_card.avatar_meta', ['max' => $avatarMaxMb]) }}</span>
                      @error('avatar')
                        <p class="form-message profile-form-error">{{ $message }}</p>
                      @enderror
                    </div>
                  </div>
                </div>

                <div class="profile-form-grid">
                  <div class="profile-field">
                    <label for="profile-first-name">Ism</label>
                    <span class="profile-field-hint">Faqat harflar, probel va defis</span>
                    <input type="text" id="profile-first-name" name="first_name"
                      value="{{ old('first_name', $user->first_name) }}" required maxlength="120"
                      autocomplete="given-name" />
                    @error('first_name')
                      <p class="form-message profile-form-error">{{ $message }}</p>
                    @enderror
                  </div>
                  <div class="profile-field">
                    <label for="profile-last-name">Familiya</label>
                    <span class="profile-field-hint">Faqat harflar, probel va defis</span>
                    <input type="text" id="profile-last-name" name="last_name"
                      value="{{ old('last_name', $user->last_name) }}" required maxlength="120"
                      autocomplete="family-name" />
                    @error('last_name')
                      <p class="form-message profile-form-error">{{ $message }}</p>
                    @enderror
                  </div>
                </div>

                <div class="profile-form-grid">
                  <div class="profile-field">
                    <label for="profile-phone">{{ __('profile.main_card.phone_label') }}</label>
                    <span class="profile-field-hint">{{ __('profile.main_card.phone_hint') }}</span>
                    <input style="margin-top:25px;" type="text" id="profile-phone" name="phone"
                      value="{{ old('phone', $user->phone) }}" maxlength="40" placeholder="+998..." autocomplete="tel" />
                    @error('phone')
                      <p class="form-message profile-form-error">{{ $message }}</p>
                    @enderror
                  </div>
                </div>

                <div class="profile-form-actions">
                  <button class="btn" type="submit">
                    <i class="fa-solid fa-floppy-disk"></i>
                    {{ __('profile.main_card.save') }}
                  </button>
                  <span class="profile-helper-inline">{{ __('profile.main_card.save_hint') }}</span>
                </div>
              </form>
            </div>
          @endif
          @if($profilePanel === 'activity')
            @if(auth()->user()->canManageExams())
              <section class="profile-activity-block reveal">
                <div class="profile-block-head">
                  <div class="profile-block-copy">
                    <h3><i class="fa-solid fa-pen-nib"></i> {{ __('public.profile_hub.my_exams_title') }}</h3>
                    <p>Siz yaratgan imtihonlar va o'quvchilar natijalarini boshqaring.</p>
                  </div>
                  <span class="profile-section-count">{{ $createdExams->count() }}</span>
                </div>

                @if($createdExams->isNotEmpty())
                  <ul class="profile-activity-list profile-activity-list-compact">
                    @foreach($createdExams as $exm)
                      <li>
                        <span class="profile-activity-title">{{ $exm->title }}</span>
                        @if($exm->is_active)
                          <span class="profile-tag profile-tag--approved">Faol</span>
                        @else
                          <span class="profile-tag profile-tag--rejected">Nofaol
                            ({{ $exm->questions_count }}/{{ $exm->required_questions }} savol)</span>
                        @endif

                        <span class="profile-activity-date">{{ $exm->created_at?->diffForHumans() }}</span>
                      </li>
                    @endforeach
                  </ul>
                @else
                  <p class="profile-empty">{{ __('public.profile_hub.my_exams_empty') }}</p>
                @endif

                <div class="profile-actions-row">
                  <a href="{{ route('profile.exams.index') }}" class="btn btn-sm">Boshqarish</a>
                  <a href="{{ route('profile.exams.create') }}" class="btn btn-outline btn-sm">Yangi imtihon</a>
                </div>
              </section>
            @endif

            <section class="profile-activity-block reveal profile-activity-block--timeline" id="profile-timeline-section">
              <div class="profile-block-head">
                <div class="profile-block-copy">
                  <h3><i class="fa-solid fa-wave-square"></i> Harakatlar tarixi</h3>
                  <p>Sizning barcha faolliklar, yangilanishlar va o'zgarishlar ro'yxati.</p>
                </div>
                <span class="profile-section-count">{{ $activities->count() }}</span>
              </div>

              @if($activities->isNotEmpty())
                <div class="profile-timeline" role="list" aria-label="Harakatlar tarixi">
                  @foreach($activities as $act)
                    @php
                      $icon = $act->icon;
                      $color = $act->color;
                      $desc = $act->description;
                      $time = $act->occurred_at?->diffForHumans();
                      $date = $act->occurred_at?->format('d.m.Y H:i');
                      $device = match($act->device_type) {
                        'mobile' => '<i class="fa-solid fa-mobile-screen"></i> Telefon',
                        'tablet' => '<i class="fa-solid fa-tablet-screen-button"></i> Planshet',
                        default => '<i class="fa-solid fa-desktop"></i> Kompyuter',
                      };
                    @endphp
                    <div class="profile-timeline-item reveal" role="listitem" style="--act-color: {{ $color }};">
                      <div class="profile-timeline-marker" aria-hidden="true">
                        <span class="profile-timeline-dot"></span>
                        <span class="profile-timeline-pulse" aria-hidden="true"></span>
                      </div>
                      <div class="profile-timeline-card">
                        <div class="profile-timeline-card-head">
                          <span class="profile-timeline-icon" style="background:{{ $color }}15; color:{{ $color }};">
                            <i class="{{ $icon }}"></i>
                          </span>
                          <span class="profile-timeline-title">{{ $desc }}</span>
                          <span class="profile-timeline-badge" style="background:{{ $color }}15; color:{{ $color }};">
                            {{ $act->type_label }}
                          </span>
                        </div>
                        <div class="profile-timeline-meta">
                          <span class="profile-timeline-time" title="{{ $date }}">
                            <i class="fa-regular fa-clock"></i> {{ $time }}
                          </span>
                          <span class="profile-timeline-device">{!! $device !!}</span>
                          @if(!empty($act->ip_address))
                            <span class="profile-timeline-ip">
                              <i class="fa-solid fa-globe"></i> {{ $act->ip_address }}
                            </span>
                          @endif
                        </div>
                        @if(!empty($act->old_value) || !empty($act->new_value))
                          <div class="profile-timeline-diff">
                            @if(!empty($act->old_value))
                              <span class="profile-timeline-diff-old">
                                Eski: {{ json_encode($act->old_value) }}
                              </span>
                            @endif
                            @if(!empty($act->new_value))
                              <span class="profile-timeline-diff-new">
                                Yangi: {{ json_encode($act->new_value) }}
                              </span>
                            @endif
                          </div>
                        @endif
                      </div>
                    </div>
                  @endforeach
                </div>
              @else
                <p class="profile-empty">Hali faolliklar tarixi mavjud emas. Saytda harakat qilsangiz, bu yerda aks etadi.</p>
              @endif
            </section>

            <section class="profile-activity-block reveal">
              <div class="profile-block-head">
                <div class="profile-block-copy">
                  <h3><i class="fa-solid fa-clipboard-question"></i> {{ __('profile.blocks.exams.title') }}</h3>
                  <p>{{ __('profile.blocks.exams.text') }}</p>
                </div>
                <span class="profile-section-count">{{ __('profile.blocks.exams.count') }}</span>
              </div>

              <div class="profile-actions-row">
                <a href="{{ route('exam.index') }}" class="btn btn-sm">{{ __('profile.blocks.exams.button') }}</a>
              </div>
            </section>

            <section class="profile-activity-block reveal">
              <div class="profile-block-head">
                <div class="profile-block-copy">
                  <h3><i class="fa-solid fa-chart-column"></i> {{ __('public.profile_hub.my_results_title') }}</h3>
                  <p>Topshirgan imtihonlaringiz endi alohida sahifada jamlanadi, profil esa ixcham qoladi.</p>
                </div>
                <span class="profile-section-count">{{ $examResultsCount }} ta</span>
              </div>

              <div class="profile-actions-row">
                <a href="{{ route('profile.results.index') }}" class="btn btn-sm">{{ __('public.profile_results.page_title') }}</a>
                @if($examResultsCount > 0)
                  <a href="{{ route('profile.results.export') }}" class="btn btn-outline btn-sm">
                    <i class="fa-solid fa-file-csv"></i> Barchasini Excel (CSV)
                  </a>
                @endif
              </div>
            </section>

            @if($user->isTeacher())
              <section class="profile-activity-block reveal" id="course-open-request">
                <div class="profile-block-head">
                  <div class="profile-block-copy">
                    <h3><i class="fa-solid fa-book-open"></i> {{ __('profile.course_open.title') }}</h3>
                    <p>{!! __('profile.course_open.intro') !!}</p>
                    @if($user->isDonor())
                      <p style="font-size:0.85rem; color:var(--muted); margin-top:4px;">
                        <i class="fa-solid fa-gem" style="color:{{ $user->donorUsernameColor() ?? '#6366f1' }};"></i>
                        Donor imtiyoz: <strong>{{ $user->donorCourseLimit() }} ta</strong> kurs ochish mumkin
                        ({{ $user->donorRankLabel() }})
                      </p>
                    @endif
                  </div>
                </div>
                @if($user->hasReachedCourseOpenLimit())
                  <p class="profile-empty" style="margin:0;">
                    @if($user->isDonor())
                      Donor imtiyozlaringiz bilan jami <strong>{{ $user->donorCourseLimit() }}</strong> ta kurs ochdingiz.
                    @else
                      {{ __('profile.course_open.limit_reached') }}
                    @endif
                  </p>
                @elseif($user->hasCourseOpenApproval())
                  <p style="margin:0 0 12px;">{{ __('profile.course_open.approved') }}</p>
                  <a href="{{ route('teacher.courses.create') }}" class="btn btn-sm">{{ __('profile.course_open.goto_create') }}</a>
                @elseif($user->hasPendingCourseOpenRequest())
                  <p class="profile-empty" style="margin:0;">{{ __('profile.course_open.pending') }}</p>
                  @if($user->course_open_request_reason)
                    <p class="profile-request-note">{{ __('profile.course_open.reason_sent', ['reason' => $user->course_open_request_reason]) }}</p>
                  @endif
                @else
                  <form method="POST" action="{{ route('teacher.courses.request') }}" id="course-request-form">
                    @csrf
                    <input type="hidden" name="reason" value="Kurs ochish uchun ruxsat so‘rash" />
                    <button type="button" class="btn btn-sm" onclick="submitCourseRequest(this)">
                      {{ __('profile.course_open.request_button') }}
                    </button>
                  </form>

                @endif
              </section>
            @endif

            @if($canViewCourseEnrollments ?? false)
              <section class="profile-activity-block reveal">
                <div class="profile-block-head">
                  <div class="profile-block-copy">
                    <h3><i class="fa-solid fa-clipboard-check"></i> {{ __('profile.blocks.teacher_requests.title') }}</h3>
                    <p>{{ __('profile.blocks.teacher_requests.text') }}</p>
                  </div>
                  <span class="profile-section-count">{{ $pendingTeacherEnrollmentCount }}</span>
                </div>

                @if(($pendingTeacherEnrollments ?? collect())->isNotEmpty())
                  <ul class="profile-activity-list profile-pending-enrollment-list">
                    @foreach($pendingTeacherEnrollments as $pen)
                      <li class="profile-pending-enrollment-item">
                        <span
                          class="profile-activity-title">{{ $pen->course ? localized_model_value($pen->course, 'title') : '-' }}</span>

                        <div class="profile-inline-meta">
                          <span><i class="fa-regular fa-user"></i> {{ __('profile.blocks.teacher_requests.student') }}:
                            {{ $pen->user?->name ?: '-' }}</span>
                          @if($pen->contact_phone)
                            <span><i class="fa-solid fa-phone"></i> {{ $pen->contact_phone }}</span>
                          @endif
                        </div>

                        <div class="profile-pending-enrollment-actions">
                          <form action="{{ route('teacher.enrollments.approve', $pen) }}" method="POST"
                            class="profile-inline-form">
                            @csrf
                            <button type="submit"
                              class="btn btn-sm">{{ __('profile.blocks.teacher_requests.approve') }}</button>
                          </form>
                          <form action="{{ route('teacher.enrollments.reject', $pen) }}" method="POST"
                            class="profile-inline-form"
                            data-confirm="{{ __('profile.blocks.teacher_requests.reject_confirm') }}"
                            data-confirm-title="{{ __('profile.blocks.teacher_requests.reject') }}"
                            data-confirm-variant="primary" data-confirm-ok="{{ __('profile.blocks.teacher_requests.reject') }}">
                            @csrf
                            <button type="submit"
                              class="btn btn-outline btn-sm">{{ __('profile.blocks.teacher_requests.reject') }}</button>
                          </form>
                        </div>
                      </li>
                    @endforeach
                  </ul>
                @else
                  <p class="profile-empty">{{ __('profile.blocks.teacher_requests.empty') }}</p>
                @endif

                @php
                  $canCreateCourse = $user->isAdmin() || (
                    $user->isTeacher()
                    && !$user->hasReachedCourseOpenLimit()
                    && $user->hasCourseOpenApproval()
                  );
                @endphp
                <div class="profile-actions-row">
                  <a href="{{ route('teacher.enrollments.index') }}"
                    class="btn btn-sm">{{ __('profile.blocks.teacher_requests.all') }}</a>
                  @if($canCreateCourse)
                    <a href="{{ route('teacher.courses.create') }}"
                      class="btn btn-outline btn-sm">{{ __('profile.blocks.teacher_requests.open_course') }}</a>
                  @elseif($user->isTeacher() && !$user->hasReachedCourseOpenLimit())
                    <a href="{{ route('profile.show') }}#course-open-request" class="btn btn-outline btn-sm">{{ __('profile.course_open.shortcut') }}</a>
                  @endif
                  @if(auth()->user()->isAdmin())
                    <a href="{{ route('admin.courses.index') }}"
                      class="btn btn-outline btn-sm">{{ __('profile.blocks.teacher_requests.admin_courses') }}</a>
                  @endif
                </div>
              </section>
            @endif

            <section class="profile-activity-block reveal">
              <div class="profile-block-head">
                <div class="profile-block-copy">
                  <h3><i class="fa-regular fa-comments"></i> {{ __('profile.blocks.post_comments.title') }}</h3>
                  <p>{{ __('profile.blocks.post_comments.text') }}</p>
                </div>
                <span class="profile-section-count">{{ $postCommentCount }}</span>
              </div>

              <ul class="profile-activity-list profile-activity-list-compact profile-activity-list--trimmed" data-activity-list data-preview-limit="{{ $activityPreviewLimit }}">
                @forelse($postComments as $c)
                  <li class="profile-activity-item">
                    @if($c->parent_id)
                      <span class="profile-tag">{{ __('profile.reply_tag') }}</span>
                    @endif
                    <p class="profile-activity-body">{{ \Illuminate\Support\Str::limit($c->body, 160) }}</p>
                    @if($c->post)
                      <a class="profile-activity-link"
                        href="{{ route('post.show', $c->post->slug) }}">{{ localized_model_value($c->post, 'title') }}</a>
                    @endif
                    <span class="profile-activity-date">{{ $c->created_at?->diffForHumans() }}</span>
                  </li>
                @empty
                  <li class="profile-empty">{{ __('profile.blocks.post_comments.empty') }}</li>
                @endforelse
              </ul>
              @if($postCommentCount > $activityPreviewLimit)
                <div class="profile-actions-row profile-actions-row--activity">
                  <button type="button" class="btn btn-outline btn-sm" data-activity-more data-more-step="{{ $activityStep }}">
                    Yana ko'rsatish
                  </button>
                </div>
              @endif
            </section>

            <section class="profile-activity-block reveal">
              <div class="profile-block-head">
                <div class="profile-block-copy">
                  <h3><i class="fa-regular fa-message"></i> {{ __('profile.blocks.teacher_comments.title') }}</h3>
                  <p>{{ __('profile.blocks.teacher_comments.text') }}</p>
                </div>
                <span class="profile-section-count">{{ $teacherCommentCount }}</span>
              </div>

              <ul class="profile-activity-list profile-activity-list-compact profile-activity-list--trimmed" data-activity-list data-preview-limit="{{ $activityPreviewLimit }}">
                @forelse($teacherComments as $c)
                  <li class="profile-activity-item">
                    @if($c->parent_id)
                      <span class="profile-tag">{{ __('profile.reply_tag') }}</span>
                    @endif
                    <p class="profile-activity-body">{{ \Illuminate\Support\Str::limit($c->body, 160) }}</p>
                    <a class="profile-activity-link"
                      href="{{ route('teacher') }}">{{ __('profile.blocks.teacher_comments.page') }}</a>
                    <span class="profile-activity-date">{{ $c->created_at?->diffForHumans() }}</span>
                  </li>
                @empty
                  <li class="profile-empty">{{ __('profile.blocks.teacher_comments.empty') }}</li>
                @endforelse
              </ul>
              @if($teacherCommentCount > $activityPreviewLimit)
                <div class="profile-actions-row profile-actions-row--activity">
                  <button type="button" class="btn btn-outline btn-sm" data-activity-more data-more-step="{{ $activityStep }}">
                    Yana ko'rsatish
                  </button>
                </div>
              @endif
            </section>

            <section class="profile-activity-block reveal">
              <div class="profile-block-head">
                <div class="profile-block-copy">
                  <h3><i class="fa-solid fa-clipboard-list"></i> {{ __('profile.blocks.enrolled_courses.title') }}</h3>
                  <p>{{ __('profile.blocks.enrolled_courses.text') }}</p>
                </div>
                <span class="profile-section-count">{{ $courseEnrollmentCount }}</span>
              </div>

              <ul class="profile-activity-list">
                @forelse($courseEnrollments as $enrollment)
                  <li>
                    @if($enrollment->course)
                      <span class="profile-activity-title">{{ localized_model_value($enrollment->course, 'title') }}</span>
                      @if($enrollment->isPending())
                        <span class="profile-tag">{{ __('profile.blocks.enrolled_courses.pending') }}</span>
                      @elseif($enrollment->isApproved())
                        <span
                          class="profile-tag profile-tag--approved">{{ __('profile.blocks.enrolled_courses.approved') }}</span>
                      @else
                        <span
                          class="profile-tag profile-tag--rejected">{{ __('profile.blocks.enrolled_courses.rejected') }}</span>
                      @endif

                      <div class="profile-inline-meta">
                        <span><i class="fa-solid fa-user-tie"></i> {{ $enrollment->course->instructorName() }}</span>
                        <span><i class="fa-solid fa-book-open"></i> <a class="profile-activity-link"
                            href="{{ route('courses') }}">{{ __('profile.blocks.enrolled_courses.page') }}</a></span>
                      </div>
                    @else
                      <span class="profile-muted">{{ __('profile.blocks.enrolled_courses.deleted') }}</span>
                    @endif

                    @if($enrollment->note)
                      <p class="profile-enroll-note">{{ \Illuminate\Support\Str::limit($enrollment->note, 200) }}</p>
                    @endif

                    <span class="profile-activity-date">{{ $enrollment->created_at?->diffForHumans() }}</span>
                  </li>
                @empty
                  <li class="profile-empty">{{ __('profile.blocks.enrolled_courses.empty') }}</li>
                @endforelse
              </ul>
            </section>

            @if($createdCourses->isNotEmpty())
              <section class="profile-activity-block reveal" id="profile-created-courses">
                <div class="profile-block-head">
                  <div class="profile-block-copy">
                    <h3><i class="fa-solid fa-book-open"></i> {{ __('profile.blocks.created_courses.title') }}</h3>
                    <p>{{ __('profile.blocks.created_courses.text') }}</p>
                  </div>
                  <span class="profile-section-count">{{ $createdCourseCount }}</span>
                </div>

                <ul class="profile-activity-list profile-activity-list-compact">
                  @foreach($createdCourses as $course)
                    <li>
                      <span class="profile-activity-title">{{ localized_model_value($course, 'title') }}</span>
                      @php
                        $stLabel = match ($course->status) {
                          \App\Models\Course::STATUS_PUBLISHED => __('profile.blocks.created_courses.published'),
                          \App\Models\Course::STATUS_PENDING_VERIFICATION => __('profile.blocks.created_courses.pending_verification'),
                          default => __('profile.blocks.created_courses.draft'),
                        };
                      @endphp
                      <span class="profile-course-status profile-course-status--{{ $course->status }}">{{ $stLabel }}</span>

                      @if($course->status === \App\Models\Course::STATUS_DRAFT && $course->rejection_reason)
                        <div class="profile-rejection-block mt-10">
                          <span class="profile-tag profile-tag--rejected mb-5"
                            style="display: inline-block;">Rad etilgan</span>
                          <p class="profile-enroll-note" style="color: #b91c1c; border-left-color: #b91c1c;">
                            <strong>Sabab:</strong> {{ $course->rejection_reason }}
                          </p>
                        </div>
                      @endif

                      <div class="profile-inline-meta">
                        <span><i class="fa-solid fa-user-tie"></i> {{ $course->instructorName() }}</span>
                      </div>

                      <div class="profile-actions-row" style="margin-top:10px;">
                        @if($user->isTeacher() && (int) $course->created_by === (int) $user->id)
                          <a href="{{ route('teacher.courses.edit', $course) }}" class="btn btn-outline btn-sm">
                            <i class="fa-solid fa-pen"></i> Tahrirlash
                          </a>
                          <a href="{{ route('courses.show', $course) }}"
                            class="btn btn-sm">{{ __('public.common.details') }}</a>
                        @endif
                      </div>

                      <span class="profile-activity-date">{{ $course->created_at?->diffForHumans() }}</span>
                    </li>
                  @endforeach
                </ul>
              </section>
            @endif
          @endif



          @if(false && $profilePanel !== 'activity')
            <section class="profile-activity-block reveal profile-panel-aside-note">
              <div class="profile-block-head">
                <div class="profile-block-copy">
                  <h3><i class="fa-solid fa-layer-group"></i> Bo'limlar ajratildi</h3>
                  <p>{{ __('public.profile_hub.panel_hint') }}</p>
                </div>
              </div>
              <div class="profile-actions-row">
                <a href="{{ route('profile.show', ['panel' => 'activity']) }}" class="btn btn-sm">Faollikni ochish</a>
                <a href="{{ route('profile.results.index') }}" class="btn btn-outline btn-sm">{{ __('public.profile_hub.tab_results') }}</a>
              </div>
            </section>
          @endif

        </div>
      </div>
    </div>
  </main>

  @push('page_scripts')
    <script src="{{ app_public_asset('temp/js/profile-page.js') }}?v=2025062802"></script>
    <script>
    window.submitCourseRequest = function(btn) {
      if (!btn || btn.disabled) return;
      btn.disabled = true;
      const originalText = btn.textContent;
      btn.textContent = 'Yuborilmoqda...';

      const form = btn.closest('form');
      if (!form) {
        btn.disabled = false;
        btn.textContent = originalText;
        return;
      }

      const formData = new FormData(form);

      fetch(form.action, {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json' },
        body: formData,
      })
      .then(r => r.json().catch(() => ({ ok: false, message: 'Server xatosi' })))
      .then(data => {
        if (!data.ok) {
          alert(data.message || 'Xato yuz berdi');
          return;
        }
        if (window.showToast) {
          window.showToast(data.message || 'Muvaffaqiyatli yuborildi', data.toast_type || 'success');
        }
        if (data.redirect) {
          setTimeout(() => { window.location.href = data.redirect; }, 500);
        } else {
          setTimeout(() => { window.location.reload(); }, 600);
        }
      })
      .catch(e => alert('Server xatosi: ' + e.message))
      .finally(() => {
        btn.disabled = false;
        btn.textContent = originalText;
      });
    };
    </script>
  @endpush
</x-loyouts.main>
