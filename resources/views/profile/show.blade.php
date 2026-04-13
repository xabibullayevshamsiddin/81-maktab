@php
  $profileRoleKey = $user->role ?? 'guest';
  $profileRoleLabelKey = 'profile.roles.' . $profileRoleKey;
  $profileRoleLabel = \Illuminate\Support\Facades\Lang::has($profileRoleLabelKey)
    ? __($profileRoleLabelKey)
    : $user->role_label;
  $profileCardStaffClass = match ($profileRoleKey) {
    'super_admin' => 'profile-card--super-admin',
    'admin' => 'profile-card--admin',
    'moderator' => 'profile-card--moderator',
    default => '',
  };

  $profileInitial = \Illuminate\Support\Str::upper(
    \Illuminate\Support\Str::substr(trim((string) ($user->name ?: 'U')), 0, 1)
  );
  $profileAvatarUrl = $user->avatar_url;
  $profileGradeLabel = $user->displayGrade(__('public.common.not_entered'));

  $postCommentCount = $postComments->count();
  $teacherCommentCount = $teacherComments->count();
  $likedPostCount = $likedPosts->count();
  $likedTeacherCount = $teacherLikes->count();
  $courseEnrollmentCount = $courseEnrollments->count();
  $createdCourseCount = $createdCourses->count();
	  $pendingTeacherEnrollmentCount = ($pendingTeacherEnrollments ?? collect())->count();
	  $canViewCourseEnrollments = $user->isAdmin() || ($user->isTeacher() && $user->hasLinkedActiveTeacherProfile());


	  $profileStats = [
    [
      'icon' => 'fa-regular fa-comments',
      'value' => $postCommentCount + $teacherCommentCount,
      'label' => __('profile.stats.comments'),
    ],
    [
      'icon' => 'fa-regular fa-heart',
      'value' => $likedPostCount + $likedTeacherCount,
      'label' => __('profile.stats.likes'),
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
  ];

  $profileI18n = [
    'showPassword' => __('profile.password_card.show_password'),
    'hidePassword' => __('profile.password_card.hide_password'),
    'preparingAvatar' => __('profile.js.preparing_avatar'),
    'avatarReady' => __('profile.js.avatar_ready'),
    'avatarFallback' => __('profile.js.avatar_fallback'),
    'saveError' => __('profile.js.save_error'),
    'saved' => __('profile.js.saved'),
    'serverError' => __('profile.js.server_error'),
  ];
@endphp

<x-loyouts.main :title="__('profile.page_title')">
  @push('page_styles')
    <link rel="stylesheet" href="{{ app_public_asset('temp/css/profile-fix.css') }}?v={{ filemtime(public_path('temp/css/profile-fix.css')) }}">
    <style>
      .page-header .header-main {
        width: calc(100% - 40px) !important;
        max-width: 1140px !important;
        top: 18px !important;
      }
      .profile-hero {
        padding-top: 150px !important;
        padding-bottom: 60px !important;
        display: block !important;
      }
      .news-hero-content {
        margin-top: 20px !important;
      }
      @media (max-width: 991px) {
        .page-header .header-main {
          width: calc(100% - 20px) !important;
          top: 10px !important;
        }
        .profile-hero {
          padding-top: 120px !important;
        }
      }
    </style>
  @endpush
  <section class="news-hero profile-hero">
    <div class="container">
      <div class="news-hero-content reveal">
        <span class="badge">{{ __('profile.badge') }}</span>
        <h1 class="js-split-text"><strong>{{ __('profile.title') }}</strong></h1>
        <p>{{ __('profile.intro') }}</p>
      </div>
    </div>
  </section>

  <main class="profile-main" data-profile-i18n='@json($profileI18n)'>
    <div class="container">
      <section class="profile-overview-panel">
        <div class="profile-overview-main">
          <div class="profile-avatar" data-profile-avatar-box data-profile-avatar-initial="{{ $profileInitial }}"
            data-profile-avatar-url="{{ $profileAvatarUrl ?: '' }}">{{ $profileInitial }}</div>

          <div class="profile-overview-copy">
            <span class="profile-kicker">{{ __('profile.overview_kicker') }}</span>
            <h2>{{ $user->name }}</h2>
            <p class="profile-overview-intro">{{ __('profile.overview_text') }}</p>

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

      <div class="profile-layout profile-layout--stack-mobile">
        <div class="profile-column profile-column-settings profile-column-settings--mobile-last">
          <div class="signin-card profile-card {{ $profileCardStaffClass }}">
            <div class="profile-card-head">
              <span class="profile-card-kicker">{{ __('profile.steps.primary') }}</span>
              <h2>{{ __('profile.main_card.title') }}</h2>
              <p class="signin-subtitle">{{ __('profile.main_card.subtitle') }}</p>
            </div>

            <div class="profile-facts">
              @foreach($profileFacts as $fact)
                <div class="profile-fact-card stagger-item">
                  <span class="profile-fact-icon"><i class="{{ $fact['icon'] }}"></i></span>
                  <span class="profile-fact-label">{{ $fact['label'] }}</span>
                  <strong class="profile-fact-value" @if($fact['track_email'] ?? false) data-profile-user-email
                  @endif>{{ $fact['value'] }}</strong>
                  <span class="profile-fact-hint">{{ $fact['hint'] }}</span>
                </div>
              @endforeach
            </div>

            <div class="profile-guide-box">
              <i class="fa-solid fa-circle-info"></i>
              <div>
                <strong>{{ __('profile.main_card.note_title') }}</strong>
                <p>{{ __('profile.main_card.note_text') }}</p>
              </div>
            </div>

            <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data"
              class="signin-form comment-form profile-form-stack">
              @csrf
              @method('PUT')

              <div class="profile-avatar-upload">
                <div class="profile-avatar-upload-copy">
                  <div class="profile-field">
                    <label for="profile-avatar">{{ __('profile.main_card.avatar_label') }}</label>
                    <span class="profile-field-hint">{{ __('profile.main_card.avatar_hint') }}</span>
                    <input type="file" id="profile-avatar" name="avatar" accept="image/jpeg,image/png,image/webp" />
                    <span class="profile-avatar-meta"
                      data-profile-avatar-meta>{{ __('profile.main_card.avatar_meta') }}</span>
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
                  <input type="text" id="profile-first-name" name="first_name" value="{{ old('first_name', $user->first_name) }}" required
                    maxlength="120" autocomplete="given-name" />
                  @error('first_name')
                    <p class="form-message profile-form-error">{{ $message }}</p>
                  @enderror
                </div>
                <div class="profile-field">
                  <label for="profile-last-name">Familiya</label>
                  <span class="profile-field-hint">Faqat harflar, probel va defis</span>
                  <input type="text" id="profile-last-name" name="last_name" value="{{ old('last_name', $user->last_name) }}" required
                    maxlength="120" autocomplete="family-name" />
                  @error('last_name')
                    <p class="form-message profile-form-error">{{ $message }}</p>
                  @enderror
                </div>
              </div>

              <div class="profile-form-grid">
                <div class="profile-field">
                  <label for="profile-phone">{{ __('profile.main_card.phone_label') }}</label>
                  <span class="profile-field-hint">{{ __('profile.main_card.phone_hint') }}</span>
                  <input style="margin-top:25px;" type="text" id="profile-phone" name="phone" value="{{ old('phone', $user->phone) }}"
                    maxlength="40" placeholder="+998..." autocomplete="tel" />
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

          @include('profile.partials.email-card')
          @include('profile.partials.password-card')
        </div>

        <div class="profile-column profile-column-activity profile-column-activity--mobile-first">
          @if(auth()->user()->canManageExams())
            <section class="profile-activity-block reveal">
              <div class="profile-block-head">
                <div class="profile-block-copy">
                  <h3><i class="fa-solid fa-pen-nib"></i> Mening imtihonlarim</h3>
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
                        <span class="profile-tag profile-tag--rejected">Nofaol ({{ $exm->questions_count }}/{{ $exm->required_questions }} savol)</span>
                      @endif

                      <span class="profile-activity-date">{{ $exm->created_at?->diffForHumans() }}</span>
                    </li>
                  @endforeach
                </ul>
              @else
                <p class="profile-empty">Hali hech qanday imtihon yaratmadingiz.</p>
              @endif

              <div class="profile-actions-row">
                <a href="{{ route('profile.exams.index') }}" class="btn btn-sm">Boshqarish</a>
                <a href="{{ route('profile.exams.create') }}" class="btn btn-outline btn-sm">Yangi imtihon</a>
              </div>
            </section>
          @endif

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

          @if(($examResults ?? collect())->isNotEmpty())
            @php
              $erTotal = $examResults->count();
              $erPassed = $examResults->where('passed', true)->count();
              $erFailed = $erTotal - $erPassed;
              $erAvgScore = $examResults->avg('points_earned');
              $erMaxScore = $examResults->max('points_earned');
              $erPassRate = $erTotal > 0 ? round($erPassed / $erTotal * 100) : 0;
            @endphp
            <section class="profile-activity-block reveal" id="exam-results-section">
              <div class="profile-block-head">
                <div class="profile-block-copy">
                  <h3><i class="fa-solid fa-chart-column"></i> Mening natijalarim</h3>
                  <p>Topshirgan imtihonlaringiz natijalari va statistikasi.</p>
                </div>
                <span class="profile-section-count">{{ $erTotal }} ta</span>
              </div>

              <div class="profile-exam-stats">
                <div class="profile-exam-stat-card profile-exam-stat--primary">
                  <span class="profile-exam-stat-num">{{ $erTotal }}</span>
                  <span class="profile-exam-stat-label">Jami imtihon</span>
                </div>
                <div class="profile-exam-stat-card profile-exam-stat--success">
                  <span class="profile-exam-stat-num">{{ $erPassed }}</span>
                  <span class="profile-exam-stat-label">O'tdi</span>
                </div>
                <div class="profile-exam-stat-card profile-exam-stat--danger">
                  <span class="profile-exam-stat-num">{{ $erFailed }}</span>
                  <span class="profile-exam-stat-label">Yiqildi</span>
                </div>
                <div class="profile-exam-stat-card profile-exam-stat--info">
                  <span class="profile-exam-stat-num">{{ $erPassRate }}%</span>
                  <span class="profile-exam-stat-label">O'tish darajasi</span>
                </div>
              </div>

              <div class="profile-results-actions">
                <button type="button" class="btn btn-sm btn-outline" onclick="window.print()">
                  <i class="fa-solid fa-print"></i> Chop etish
                </button>
                <a href="{{ route('profile.results.export') }}" class="btn btn-sm btn-outline">
                  <i class="fa-solid fa-file-csv"></i> Excel (CSV)
                </a>
              </div>

              <div class="profile-exam-results-list" id="exam-results-table">
                @foreach($examResults as $er)
                  <div class="profile-exam-result-card {{ $er->passed ? 'is-pass' : 'is-fail' }}">
                    <div class="profile-exam-result-top">
                      <div class="profile-exam-result-info">
                        <h4 class="profile-exam-result-title">{{ $er->exam->title ?? '-' }}</h4>
                        <span class="profile-exam-result-date">
                          {{ $er->submitted_at?->format('d.m.Y H:i') ?? '-' }}
                        </span>
                      </div>
                      <div class="profile-exam-result-badge {{ $er->passed ? 'badge-pass' : 'badge-fail' }}">
                        @if($er->passed)
                          <i class="fa-solid fa-circle-check"></i> O'tdi
                        @else
                          <i class="fa-solid fa-circle-xmark"></i> Yiqildi
                        @endif
                      </div>
                    </div>
                    <div class="profile-exam-result-bottom">
                      <div class="profile-exam-result-metric">
                        <span class="profile-exam-result-metric-val">{{ $er->points_earned ?? 0 }}<small>/{{ $er->points_max ?? 0 }}</small></span>
                        <span class="profile-exam-result-metric-label">Ball</span>
                      </div>
                      <div class="profile-exam-result-metric">
                        <span class="profile-exam-result-metric-val">{{ $er->score }}<small>/{{ $er->total_questions }}</small></span>
                        <span class="profile-exam-result-metric-label">To'g'ri</span>
                      </div>
                      <div class="profile-exam-result-metric">
                        @php $pct = $er->points_max > 0 ? round($er->points_earned / $er->points_max * 100) : 0; @endphp
                        <span class="profile-exam-result-metric-val">{{ $pct }}%</span>
                        <span class="profile-exam-result-metric-label">Foiz</span>
                      </div>
                      <div class="profile-exam-result-metric">
                        <span class="profile-exam-result-metric-val" style="font-size:12px;">{{ $er->status === 'expired' ? 'Vaqt tugagan' : 'Topshirilgan' }}</span>
                        <span class="profile-exam-result-metric-label">Holat</span>
                      </div>
                    </div>
                  </div>
                @endforeach
              </div>
            </section>
          @endif

          @if($user->isTeacher() && $user->hasLinkedActiveTeacherProfile())
            <section class="profile-activity-block reveal" id="course-open-request">
              <div class="profile-block-head">
                <div class="profile-block-copy">
                  <h3><i class="fa-solid fa-book-open"></i> Kurs ochish ruxsati</h3>
                  <p>Teacher akkaunti faqat <strong>bitta</strong> kurs yaratishi mumkin. Kurs ochishdan oldin adminga ruxsat so'rashingiz kerak.</p>
                </div>
              </div>
              @if($user->hasReachedCourseOpenLimit())
                <p class="profile-empty" style="margin:0;">Siz ruxsat asosida kurs yaratgansiz (bitta chegara).</p>
              @elseif($user->hasCourseOpenApproval())
                <p style="margin:0 0 12px;">Admin ruxsat berdi — endi forma orqali kurs ochishingiz mumkin.</p>
                <a href="{{ route('teacher.courses.create') }}" class="btn btn-sm">Kurs ochish sahifasiga o'tish</a>
              @elseif($user->hasPendingCourseOpenRequest())
                <p class="profile-empty" style="margin:0;">So'rovingiz adminga yuborilgan. Admin javobini kuting.</p>
              @else
                <form action="{{ route('teacher.courses.request') }}" method="POST" class="profile-inline-form">
                  @csrf
                  <button type="submit" class="btn btn-sm">Kurs ochish uchun admin ruxsatini so'rash</button>
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
                          data-confirm-variant="primary"
                          data-confirm-ok="{{ __('profile.blocks.teacher_requests.reject') }}">
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
                      && $user->hasLinkedActiveTeacherProfile()
                      && ! $user->hasReachedCourseOpenLimit()
                      && $user->hasCourseOpenApproval()
                    );
                    $needsLink = $user->isTeacher() && ! $user->hasLinkedActiveTeacherProfile();
                  @endphp
                  <div class="profile-actions-row">
                    <a href="{{ route('teacher.enrollments.index') }}"
                      class="btn btn-sm">{{ __('profile.blocks.teacher_requests.all') }}</a>
                    @if($canCreateCourse)
                      <a href="{{ route('teacher.courses.create') }}"
                        class="btn btn-outline btn-sm">{{ __('profile.blocks.teacher_requests.open_course') }}</a>
                    @elseif($user->isTeacher() && $user->hasLinkedActiveTeacherProfile() && ! $user->hasReachedCourseOpenLimit())
                      <a href="{{ route('profile.show') }}#course-open-request"
                        class="btn btn-outline btn-sm">Kurs — ruxsat</a>
                    @endif
                    @if(auth()->user()->isAdmin())
                      <a href="{{ route('admin.courses.index') }}"
                        class="btn btn-outline btn-sm">{{ __('profile.blocks.teacher_requests.admin_courses') }}</a>
                    @endif
                  </div>
                  @if($needsLink)
                    <p class="profile-empty" style="margin-top:12px;">Admin teacher akkauntingizni ustoz kartasiga bog'lashi kerak.</p>
                  @endif
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

            <ul class="profile-activity-list">
              @forelse($postComments as $c)
                <li>
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
          </section>

          <section class="profile-activity-block reveal">
            <div class="profile-block-head">
              <div class="profile-block-copy">
                <h3><i class="fa-regular fa-message"></i> {{ __('profile.blocks.teacher_comments.title') }}</h3>
                <p>{{ __('profile.blocks.teacher_comments.text') }}</p>
              </div>
              <span class="profile-section-count">{{ $teacherCommentCount }}</span>
            </div>

            <ul class="profile-activity-list">
              @forelse($teacherComments as $c)
                <li>
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
          </section>

          <section class="profile-activity-block reveal">
            <div class="profile-block-head">
              <div class="profile-block-copy">
                <h3><i class="fa-regular fa-heart"></i> {{ __('profile.blocks.liked_posts.title') }}</h3>
                <p>{{ __('profile.blocks.liked_posts.text') }}</p>
              </div>
              <span class="profile-section-count">{{ $likedPostCount }}</span>
            </div>

            <ul class="profile-activity-list profile-activity-list-compact">
              @forelse($likedPosts as $like)
                <li>
                  @if($like->post)
                    <a class="profile-activity-link"
                      href="{{ route('post.show', $like->post->slug) }}">{{ localized_model_value($like->post, 'title') }}</a>
                  @else
                    <span class="profile-muted">{{ __('profile.blocks.liked_posts.deleted') }}</span>
                  @endif
                  <span class="profile-activity-date">{{ $like->created_at?->diffForHumans() }}</span>
                </li>
              @empty
                <li class="profile-empty">{{ __('profile.blocks.liked_posts.empty') }}</li>
              @endforelse
            </ul>
          </section>

          <section class="profile-activity-block reveal">
            <div class="profile-block-head">
              <div class="profile-block-copy">
                <h3><i class="fa-solid fa-chalkboard-user"></i> {{ __('profile.blocks.liked_teachers.title') }}</h3>
                <p>{{ __('profile.blocks.liked_teachers.text') }}</p>
              </div>
              <span class="profile-section-count">{{ $likedTeacherCount }}</span>
            </div>

            <ul class="profile-activity-list profile-activity-list-compact">
              @forelse($teacherLikes as $tl)
                <li>
                  @if($tl->teacher)
                    <a class="profile-activity-link"
                      href="{{ route('teacher.show', $tl->teacher->slug) }}">{{ $tl->teacher->full_name }}</a>
                  @else
                    <span class="profile-muted">{{ __('profile.blocks.liked_teachers.deleted') }}</span>
                  @endif
                  <span class="profile-activity-date">{{ $tl->created_at?->diffForHumans() }}</span>
                </li>
              @empty
                <li class="profile-empty">{{ __('profile.blocks.liked_teachers.empty') }}</li>
              @endforelse
            </ul>
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
                      @if($enrollment->course->teacher)
                        <span><i class="fa-solid fa-user-tie"></i> {{ $enrollment->course->teacher->full_name }}</span>
                      @endif
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
                        <span class="profile-tag profile-tag--rejected mb-5" style="display: inline-block;">{{ __('Rad etilgan') }}</span>
                        <p class="profile-enroll-note" style="color: #b91c1c; border-left-color: #b91c1c;">
                          <strong>{{ __('Sabab') }}:</strong> {{ $course->rejection_reason }}
                        </p>
                      </div>
                    @endif

                    @if($course->teacher)
                      <div class="profile-inline-meta">
                        <span><i class="fa-solid fa-user-tie"></i> {{ $course->teacher->full_name }}</span>
                      </div>
                    @endif

                    <div class="profile-actions-row" style="margin-top:10px;">
                      @if($user->isTeacher() && (int) $course->created_by === (int) $user->id)
                        <a href="{{ route('teacher.courses.edit', $course) }}" class="btn btn-outline btn-sm">
                          <i class="fa-solid fa-pen"></i> Tahrirlash
                        </a>
                        <a href="{{ route('courses.show', $course) }}" class="btn btn-sm">{{ __('public.common.details') }}</a>
                      @endif
                    </div>

                    <span class="profile-activity-date">{{ $course->created_at?->diffForHumans() }}</span>
                  </li>
                @endforeach
              </ul>
            </section>
          @endif

        </div>
      </div>
    </div>
  </main>

  @push('page_scripts')
    <script
      src="{{ app_public_asset('temp/js/profile-page.js') }}?v={{ filemtime(public_path('temp/js/profile-page.js')) }}"></script>
  @endpush
</x-loyouts.main>
