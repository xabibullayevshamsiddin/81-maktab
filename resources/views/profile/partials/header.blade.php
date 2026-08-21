@php
  $user = $user ?? auth()->user();
  if ($user && ! $user->relationLoaded('roleRelation')) {
      $user->load('roleRelation');
  }

  $profileRoleKey = $user->role ?? 'guest';
  $profileRoleLabelKey = 'profile.roles.' . $profileRoleKey;
  $profileRoleLabel = \Illuminate\Support\Facades\Lang::has($profileRoleLabelKey)
    ? __($profileRoleLabelKey)
    : $user->role_label;
  $donorRank = $user->donation_rank;
  $donorIsActive = $user->isDonor();
  $profileOverviewDonorClass = $donorIsActive ? 'profile-overview--donor profile-overview--donor-' . $donorRank : '';
  $profileOverviewThemeClass = $user->donorThemeClass();

  $profileInitial = \Illuminate\Support\Str::upper(
    \Illuminate\Support\Str::substr(trim((string) ($user->name ?: 'U')), 0, 1)
  );
  $profileAvatarUrl = $user->avatar_url;
  $profileGradeLabel = $user->displayGrade(__('public.common.not_entered'));
  $activePanel = $activePanel ?? ($panel ?? 'settings');

  if (!isset($postCommentCount)) {
      $postCommentCount = \App\Models\Comment::query()->where('user_id', $user->id)->count();
  }
  if (!isset($teacherCommentCount)) {
      $teacherCommentCount = \App\Models\TeacherComment::query()->where('user_id', $user->id)->count();
  }
  if (!isset($courseEnrollmentCount)) {
      $courseEnrollmentCount = \App\Models\CourseEnrollment::query()->where('user_id', $user->id)->count();
  }
  if (!isset($canViewCourseEnrollments)) {
      $canViewCourseEnrollments = \App\Models\Course::query()->where('created_by', $user->id)->exists();
  }
  if (!isset($pendingTeacherEnrollmentCount)) {
      $pendingTeacherEnrollmentCount = $canViewCourseEnrollments
          ? \App\Models\CourseEnrollment::query()
              ->whereHas('course', fn ($query) => $query->where('created_by', $user->id))
              ->where('status', \App\Models\CourseEnrollment::STATUS_PENDING)
              ->count()
          : 0;
  }
  if (!isset($createdCourseCount)) {
      $createdCourseCount = \App\Models\Course::query()->where('created_by', $user->id)->count();
  }

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
@endphp

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
      padding-bottom: 48px !important;
      display: block !important;
      min-height: auto !important;
      background: var(--profile-bg) !important;
      animation: none !important;
    }

    .profile-hero::before,
    .profile-hero::after {
      display: none !important;
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
    <div class="news-hero-content prime-reveal">
      <span class="badge">{{ __('profile.badge') }}</span>
      <h1 class="js-split-text"><strong>{{ __('profile.title') }}</strong></h1>
      <p>{{ __('profile.intro') }}</p>
    </div>

    <section class="profile-overview-panel {{ $profileOverviewDonorClass }} {{ $profileOverviewThemeClass }} profile-bg-{{ $user->profile_bg_style ?? 'plain' }}" style="margin-top:32px;">
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
          @php
            $fontFamily = $user->name_font_family ?? '';
            $validFonts = ['orbitron','caveat','press-start','pacifico','righteous','bungee','permanent-marker'];
            $fontClass = in_array($fontFamily, $validFonts) ? ' font-'.$fontFamily : '';
          @endphp
          <h2 class="profile-overview-name{{ $fontClass }}">
            <span style="color: {{ $user->donorUsernameColor() ?? 'inherit' }}; font-weight: {{ $user->donorIsActive ? ($user->name_font_weight ?? '700') : 'inherit' }};">{{ $user->name }}</span>{{ $statusEmoji ? ' '.$statusEmoji : '' }}
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
      <a href="{{ route('profile.show', ['panel' => 'settings']) }}" class="profile-panel-tab {{ $activePanel === 'settings' ? 'is-active' : '' }}">
        <i class="fa-solid fa-user-gear"></i>
        <span>{{ __('public.profile_hub.tab_settings') }}</span>
      </a>
      <a href="{{ route('profile.show', ['panel' => 'security']) }}" class="profile-panel-tab {{ $activePanel === 'security' ? 'is-active' : '' }}">
        <i class="fa-solid fa-shield-halved"></i>
        <span>{{ __('public.profile_hub.tab_security') }}</span>
      </a>
      <a href="{{ route('profile.results.index') }}" class="profile-panel-tab {{ $activePanel === 'results' ? 'is-active' : '' }}">
        <i class="fa-solid fa-chart-column"></i>
        <span>{{ __('public.profile_hub.tab_results') }}</span>
      </a>

      <a href="{{ route('profile.show', ['panel' => 'activity']) }}" class="profile-panel-tab {{ $activePanel === 'activity' ? 'is-active' : '' }}">
        <i class="fa-solid fa-wave-square"></i>
        <span>{{ __('public.profile_hub.tab_activity') }}</span>
      </a>
      <a href="{{ route('profile.bookmarks.index') }}" class="profile-panel-tab {{ $activePanel === 'bookmarks' ? 'is-active' : '' }}">
        <i class="fa-solid fa-bookmark"></i>
        <span>{{ __('profile.bookmarks.nav') }}</span>
      </a>
      <a href="{{ route('profile.show', ['panel' => 'appearance']) }}" class="profile-panel-tab {{ $activePanel === 'appearance' ? 'is-active' : '' }}">
        <i class="fa-solid fa-palette" style="color: #8b5cf6;"></i>
        <span>{{ __('profile.appearance_tab') }}</span>
      </a>
    </nav>
  </section>
  </div>
</section>
