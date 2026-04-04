@php
  $profileRoleKey = $user->role ?? 'guest';
  $profileCardStaffClass = match ($profileRoleKey) {
    'super_admin' => 'profile-card--super-admin',
    'admin' => 'profile-card--admin',
    default => '',
  };

  $profileInitial = \Illuminate\Support\Str::upper(
    \Illuminate\Support\Str::substr(trim((string) ($user->name ?: 'U')), 0, 1)
  );

  $postCommentCount = $postComments->count();
  $teacherCommentCount = $teacherComments->count();
  $likedPostCount = $likedPosts->count();
  $likedTeacherCount = $teacherLikes->count();
  $courseEnrollmentCount = $courseEnrollments->count();
  $createdCourseCount = $createdCourses->count();
  $pendingTeacherEnrollmentCount = ($pendingTeacherEnrollments ?? collect())->count();

  $profileStats = [
    [
      'icon' => 'fa-regular fa-comments',
      'value' => $postCommentCount + $teacherCommentCount,
      'label' => 'Izohlar',
    ],
    [
      'icon' => 'fa-regular fa-heart',
      'value' => $likedPostCount + $likedTeacherCount,
      'label' => 'Yoqtirishlar',
    ],
    [
      'icon' => 'fa-solid fa-book-open',
      'value' => $courseEnrollmentCount,
      'label' => 'Yozilgan kurslar',
    ],
    [
      'icon' => $canViewCourseEnrollments ? 'fa-solid fa-clipboard-check' : 'fa-solid fa-layer-group',
      'value' => $canViewCourseEnrollments ? $pendingTeacherEnrollmentCount : $createdCourseCount,
      'label' => $canViewCourseEnrollments ? 'Kutilayotgan arizalar' : 'Yaratilgan kurslar',
    ],
  ];

  $profileFacts = [
    [
      'icon' => 'fa-solid fa-envelope',
      'label' => 'Joriy email',
      'value' => $user->email,
      'hint' => 'Kirish va tasdiqlash xatlari shu manzilga boradi.',
    ],
    [
      'icon' => 'fa-solid fa-phone',
      'label' => 'Telefon',
      'value' => $user->phone ?: 'Kiritilmagan',
      'hint' => 'Admin yoki ustoz siz bilan bog\'lanishi uchun kerak bo\'ladi.',
    ],
    [
      'icon' => 'fa-solid fa-user-graduate',
      'label' => 'Sinf',
      'value' => $user->grade_label,
      'hint' => 'Imtihon va kurslar sizning sinfingizga qarab ko\'rsatiladi.',
    ],
    [
      'icon' => 'fa-solid fa-user-shield',
      'label' => 'Rol',
      'value' => $user->role_label,
      'hint' => 'Qaysi bo\'limlar sizga ochiq ekani rolga bog\'liq.',
    ],
  ];
@endphp

<x-loyouts.main title="81-IDUM | Profil">
  <section class="news-hero profile-hero">
    <div class="container">
      <div class="news-hero-content reveal">
        <span class="badge">Shaxsiy kabinet</span>
        <h1><strong>Profil</strong></h1>
        <p>Profilingizni yangilang, emailni tasdiqlang va barcha faolliklaringizni bitta sahifada boshqaring.</p>
      </div>
    </div>
  </section>

  <main class="profile-main">
    <div class="container">
      <section class="profile-overview-panel">
        <div class="profile-overview-main">
          <div class="profile-avatar">{{ $profileInitial }}</div>

          <div class="profile-overview-copy">
            <span class="profile-kicker">Sizning profilingiz</span>
            <h2>{{ $user->name }}</h2>
            <p>
              Bu yerda ism, telefon va emailni yangilaysiz. Pastdagi bloklarda esa imtihon,
              kurs, izoh va yoqtirishlar bo'yicha barcha faolligingiz jamlangan.
            </p>

            <div class="profile-overview-tags">
              <span class="profile-role-badge comment-role-badge role-{{ $profileRoleKey }}">{{ $user->role_label }}</span>
              <span class="profile-chip">
                <i class="fa-solid fa-user-graduate"></i>
                {{ $user->grade_label }}
              </span>
              <span class="profile-chip">
                <i class="fa-solid fa-phone"></i>
                {{ $user->phone ?: 'Telefon kiritilmagan' }}
              </span>
            </div>
          </div>
        </div>

        <div class="profile-stats-grid">
          @foreach($profileStats as $stat)
            <div class="profile-stat-card">
              <span class="profile-stat-icon"><i class="{{ $stat['icon'] }}"></i></span>
              <strong>{{ number_format($stat['value']) }}</strong>
              <span>{{ $stat['label'] }}</span>
            </div>
          @endforeach
        </div>
      </section>

      <div class="profile-layout">
        <div class="profile-column profile-column-settings">
          <div class="signin-card profile-card {{ $profileCardStaffClass }}">
            <div class="profile-card-head">
              <span class="profile-card-kicker">1-qadam</span>
              <h2>Asosiy ma'lumotlar</h2>
              <p class="signin-subtitle">
                Bu bo'lim siz haqingizdagi eng muhim ma'lumotlarni ko'rsatadi. Ism va telefonni shu yerda yangilang.
              </p>
            </div>

            <div class="profile-facts">
              @foreach($profileFacts as $fact)
                <div class="profile-fact-card">
                  <span class="profile-fact-icon"><i class="{{ $fact['icon'] }}"></i></span>
                  <span class="profile-fact-label">{{ $fact['label'] }}</span>
                  <strong class="profile-fact-value" @if($fact['label'] === 'Joriy email') data-profile-user-email @endif>{{ $fact['value'] }}</strong>
                  <span class="profile-fact-hint">{{ $fact['hint'] }}</span>
                </div>
              @endforeach
            </div>

            <div class="profile-guide-box">
              <i class="fa-solid fa-circle-info"></i>
              <div>
                <strong>Qisqa eslatma</strong>
                <p>Ism va telefon shu formadan saqlanadi. Email esa pastdagi alohida blokda tasdiqlash kodi orqali almashtiriladi.</p>
              </div>
            </div>

            <form action="{{ route('profile.update') }}" method="POST" class="signin-form comment-form profile-form-stack">
              @csrf
              @method('PUT')

              <div class="profile-form-grid">
                <div class="profile-field">
                  <label for="profile-name">Ism yoki nik</label>
                  <span class="profile-field-hint">Sizga sayt ichida qanday murojaat qilinishi shu yerda ko'rinadi.</span>
                  <input type="text" id="profile-name" name="name" value="{{ old('name', $user->name) }}" required maxlength="120" autocomplete="name" />
                  @error('name')
                    <p class="form-message profile-form-error">{{ $message }}</p>
                  @enderror
                </div>

                <div class="profile-field">
                  <label for="profile-phone">Telefon</label>
                  <span class="profile-field-hint">Aloqa uchun ishlatiladi. Format: +998...</span>
                  <input type="text" id="profile-phone" name="phone" value="{{ old('phone', $user->phone) }}" maxlength="40" placeholder="+998..." autocomplete="tel" />
                  @error('phone')
                    <p class="form-message profile-form-error">{{ $message }}</p>
                  @enderror
                </div>
              </div>

              <div class="profile-form-actions">
                <button class="btn" type="submit">
                  <i class="fa-solid fa-floppy-disk"></i>
                  Saqlash
                </button>
                <span class="profile-helper-inline">O'zgarish saqlansa, profil darhol yangilanadi.</span>
              </div>
            </form>
          </div>

          @include('profile.partials.email-card')
          @include('profile.partials.password-card')
        </div>

        <div class="profile-column profile-column-activity">
          <section class="profile-activity-block">
            <div class="profile-block-head">
              <div class="profile-block-copy">
                <h3><i class="fa-solid fa-clipboard-question"></i> Onlayn imtihonlar</h3>
                <p>Sinfingizga mos imtihonlarni ochib, test topshirish bo'limiga tez o'tasiz.</p>
              </div>
              <span class="profile-section-count">Ochish</span>
            </div>

            <div class="profile-actions-row">
              <a href="{{ route('exam.index') }}" class="btn btn-sm">Imtihonlar sahifasi</a>
            </div>
          </section>

          @if($canViewCourseEnrollments ?? false)
            <section class="profile-activity-block">
              <div class="profile-block-head">
                <div class="profile-block-copy">
                  <h3><i class="fa-solid fa-clipboard-check"></i> Kursga yozilish arizalari</h3>
                  <p>O'z kurslaringizga tushgan arizalarni shu yerdan ko'rib, tasdiqlashingiz yoki rad etishingiz mumkin.</p>
                </div>
                <span class="profile-section-count">{{ $pendingTeacherEnrollmentCount }}</span>
              </div>

              @if(($pendingTeacherEnrollments ?? collect())->isNotEmpty())
                <ul class="profile-activity-list profile-pending-enrollment-list">
                  @foreach($pendingTeacherEnrollments as $pen)
                    <li class="profile-pending-enrollment-item">
                      <span class="profile-activity-title">{{ $pen->course?->title ?: '-' }}</span>

                      <div class="profile-inline-meta">
                        <span><i class="fa-regular fa-user"></i> O'quvchi: {{ $pen->user?->name ?: '-' }}</span>
                        @if($pen->contact_phone)
                          <span><i class="fa-solid fa-phone"></i> {{ $pen->contact_phone }}</span>
                        @endif
                      </div>

                      <div class="profile-pending-enrollment-actions">
                        <form action="{{ route('teacher.enrollments.approve', $pen) }}" method="POST" class="profile-inline-form">
                          @csrf
                          <button type="submit" class="btn btn-sm">Tasdiqlash</button>
                        </form>
                        <form action="{{ route('teacher.enrollments.reject', $pen) }}" method="POST" class="profile-inline-form" onsubmit="return confirm('Rad etilsinmi?');">
                          @csrf
                          <button type="submit" class="btn btn-outline btn-sm">Rad etish</button>
                        </form>
                      </div>
                    </li>
                  @endforeach
                </ul>
              @else
                <p class="profile-empty">Hozircha kutilayotgan ariza yo'q.</p>
              @endif

              <div class="profile-actions-row">
                <a href="{{ route('teacher.enrollments.index') }}" class="btn btn-sm">Barcha arizalar</a>
                <a href="{{ route('teacher.courses.create') }}" class="btn btn-outline btn-sm">Kurs ochish</a>
                @if(auth()->user()->isAdmin())
                  <a href="{{ route('admin.courses.index') }}" class="btn btn-outline btn-sm">Admin: kurslar</a>
                @endif
              </div>
            </section>
          @endif

          <section class="profile-activity-block">
            <div class="profile-block-head">
              <div class="profile-block-copy">
                <h3><i class="fa-regular fa-comments"></i> Yangiliklar postlariga izohlar</h3>
                <p>Yangiliklar ostida qoldirgan izohlaringiz shu yerda saqlanadi.</p>
              </div>
              <span class="profile-section-count">{{ $postCommentCount }}</span>
            </div>

            <ul class="profile-activity-list">
              @forelse($postComments as $c)
                <li>
                  @if($c->parent_id)
                    <span class="profile-tag">Javob</span>
                  @endif
                  <p class="profile-activity-body">{{ \Illuminate\Support\Str::limit($c->body, 160) }}</p>
                  @if($c->post)
                    <a class="profile-activity-link" href="{{ route('post.show', $c->post->slug) }}">{{ $c->post->title }}</a>
                  @endif
                  <span class="profile-activity-date">{{ $c->created_at?->diffForHumans() }}</span>
                </li>
              @empty
                <li class="profile-empty">Hozircha izoh yo'q.</li>
              @endforelse
            </ul>
          </section>

          <section class="profile-activity-block">
            <div class="profile-block-head">
              <div class="profile-block-copy">
                <h3><i class="fa-regular fa-message"></i> Ustozlar sahifasidagi izohlar</h3>
                <p>Ustozlar sahifasida yozgan izohlaringiz va javoblaringiz shu yerda turadi.</p>
              </div>
              <span class="profile-section-count">{{ $teacherCommentCount }}</span>
            </div>

            <ul class="profile-activity-list">
              @forelse($teacherComments as $c)
                <li>
                  @if($c->parent_id)
                    <span class="profile-tag">Javob</span>
                  @endif
                  <p class="profile-activity-body">{{ \Illuminate\Support\Str::limit($c->body, 160) }}</p>
                  <a class="profile-activity-link" href="{{ route('teacher') }}">Ustozlar sahifasi</a>
                  <span class="profile-activity-date">{{ $c->created_at?->diffForHumans() }}</span>
                </li>
              @empty
                <li class="profile-empty">Hozircha izoh yo'q.</li>
              @endforelse
            </ul>
          </section>

          <section class="profile-activity-block">
            <div class="profile-block-head">
              <div class="profile-block-copy">
                <h3><i class="fa-regular fa-heart"></i> Yoqtirilgan yangiliklar</h3>
                <p>Keyinroq qayta o'qish uchun saqlab qo'ygan postlaringiz shu yerda ko'rinadi.</p>
              </div>
              <span class="profile-section-count">{{ $likedPostCount }}</span>
            </div>

            <ul class="profile-activity-list profile-activity-list-compact">
              @forelse($likedPosts as $like)
                <li>
                  @if($like->post)
                    <a class="profile-activity-link" href="{{ route('post.show', $like->post->slug) }}">{{ $like->post->title }}</a>
                  @else
                    <span class="profile-muted">Post o'chirilgan</span>
                  @endif
                  <span class="profile-activity-date">{{ $like->created_at?->diffForHumans() }}</span>
                </li>
              @empty
                <li class="profile-empty">Hozircha yoqtirish yo'q.</li>
              @endforelse
            </ul>
          </section>

          <section class="profile-activity-block">
            <div class="profile-block-head">
              <div class="profile-block-copy">
                <h3><i class="fa-solid fa-chalkboard-user"></i> Yoqtirilgan ustozlar</h3>
                <p>Yoqtirgan ustozlaringizni keyin tez topish uchun shu ro'yxat saqlanadi.</p>
              </div>
              <span class="profile-section-count">{{ $likedTeacherCount }}</span>
            </div>

            <ul class="profile-activity-list profile-activity-list-compact">
              @forelse($teacherLikes as $tl)
                <li>
                  @if($tl->teacher)
                    <a class="profile-activity-link" href="{{ route('teacher.show', $tl->teacher->slug) }}">{{ $tl->teacher->full_name }}</a>
                  @else
                    <span class="profile-muted">Ustoz o'chirilgan</span>
                  @endif
                  <span class="profile-activity-date">{{ $tl->created_at?->diffForHumans() }}</span>
                </li>
              @empty
                <li class="profile-empty">Hozircha yoqtirish yo'q.</li>
              @endforelse
            </ul>
          </section>

          <section class="profile-activity-block">
            <div class="profile-block-head">
              <div class="profile-block-copy">
                <h3><i class="fa-solid fa-clipboard-list"></i> Yozilgan kurslar</h3>
                <p>Qaysi kurslarga yozilganingiz va arizangiz qaysi holatda turgani shu yerda ko'rsatiladi.</p>
              </div>
              <span class="profile-section-count">{{ $courseEnrollmentCount }}</span>
            </div>

            <ul class="profile-activity-list">
              @forelse($courseEnrollments as $enrollment)
                <li>
                  @if($enrollment->course)
                    <span class="profile-activity-title">{{ $enrollment->course->title }}</span>
                    @if($enrollment->isPending())
                      <span class="profile-tag">Kutilmoqda</span>
                    @elseif($enrollment->isApproved())
                      <span class="profile-tag profile-tag--approved">Qabul</span>
                    @else
                      <span class="profile-tag profile-tag--rejected">Rad</span>
                    @endif

                    <div class="profile-inline-meta">
                      @if($enrollment->course->teacher)
                        <span><i class="fa-solid fa-user-tie"></i> {{ $enrollment->course->teacher->full_name }}</span>
                      @endif
                      <span><i class="fa-solid fa-book-open"></i> <a class="profile-activity-link" href="{{ route('courses') }}">Kurslar sahifasi</a></span>
                    </div>
                  @else
                    <span class="profile-muted">Kurs o'chirilgan</span>
                  @endif

                  @if($enrollment->note)
                    <p class="profile-enroll-note">{{ \Illuminate\Support\Str::limit($enrollment->note, 200) }}</p>
                  @endif

                  <span class="profile-activity-date">{{ $enrollment->created_at?->diffForHumans() }}</span>
                </li>
              @empty
                <li class="profile-empty">Hozircha yozilgan kurs yo'q.</li>
              @endforelse
            </ul>
          </section>

          @if($createdCourses->isNotEmpty())
            <section class="profile-activity-block">
              <div class="profile-block-head">
                <div class="profile-block-copy">
                  <h3><i class="fa-solid fa-book-open"></i> Yaratilgan kurslar</h3>
                  <p>O'zingiz yaratgan kurslar va ularning joriy holati shu yerda ko'rinadi.</p>
                </div>
                <span class="profile-section-count">{{ $createdCourseCount }}</span>
              </div>

              <ul class="profile-activity-list profile-activity-list-compact">
                @foreach($createdCourses as $course)
                  <li>
                    <span class="profile-activity-title">{{ $course->title }}</span>
                    @php
                      $stLabel = match ($course->status) {
                        \App\Models\Course::STATUS_PUBLISHED => 'Saytda',
                        \App\Models\Course::STATUS_PENDING_VERIFICATION => 'Kod kutilmoqda',
                        default => 'Qoralama',
                      };
                    @endphp
                    <span class="profile-course-status profile-course-status--{{ $course->status }}">{{ $stLabel }}</span>

                    @if($course->teacher)
                      <div class="profile-inline-meta">
                        <span><i class="fa-solid fa-user-tie"></i> {{ $course->teacher->full_name }}</span>
                      </div>
                    @endif

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

  <script>
    (() => {
      const profileRoot = document.querySelector('.profile-main');
      if (!profileRoot) return;

      function bindPwToggles(root = document) {
        root.querySelectorAll('.pw-toggle').forEach((btn) => {
          if (btn.dataset.pwToggleBound === 'true') return;
          btn.dataset.pwToggleBound = 'true';

          btn.addEventListener('click', () => {
            const targetId = btn.getAttribute('data-target');
            const input = document.getElementById(targetId);
            const icon = btn.querySelector('i');
            if (!input) return;

            const isHidden = input.type === 'password';
            input.type = isHidden ? 'text' : 'password';
            icon?.classList.toggle('fa-eye', !isHidden);
            icon?.classList.toggle('fa-eye-slash', isHidden);
            btn.setAttribute('aria-label', isHidden ? "Parolni yashirish" : "Parolni ko'rsatish");
          });
        });
      }

      function clearFormErrors(form) {
        form.querySelectorAll('.profile-form-error.is-dynamic').forEach((node) => node.remove());
      }

      function renderFormErrors(form, errors) {
        clearFormErrors(form);

        Object.entries(errors || {}).forEach(([name, messages]) => {
          const field = form.querySelector(`[name="${name}"]`)?.closest('.profile-field');
          if (!field) return;

          const message = Array.isArray(messages) ? messages[0] : messages;
          if (!message) return;

          const errorEl = document.createElement('p');
          errorEl.className = 'form-message profile-form-error is-dynamic';
          errorEl.textContent = String(message);

          const anchor = field.querySelector('.pw-wrap') || field.querySelector('input, select, textarea');
          if (anchor) {
            anchor.insertAdjacentElement('afterend', errorEl);
          } else {
            field.appendChild(errorEl);
          }
        });
      }

      function setSubmitting(form, isSubmitting) {
        form.classList.toggle('is-submitting', isSubmitting);

        form.querySelectorAll('button[type="submit"], input[type="submit"]').forEach((el) => {
          el.disabled = isSubmitting;
          el.setAttribute('aria-busy', isSubmitting ? 'true' : 'false');
        });
      }

      function replaceSection(sectionName, html) {
        const current = profileRoot.querySelector(`[data-profile-section="${sectionName}"]`);
        if (!current || !html) return;

        current.outerHTML = html;
        bindPwToggles(profileRoot);
      }

      function syncEmailText(email) {
        if (!email) return;

        profileRoot.querySelectorAll('[data-profile-user-email]').forEach((el) => {
          el.textContent = email;
        });
      }

      bindPwToggles(profileRoot);

      document.addEventListener('submit', async (event) => {
        const form = event.target.closest('form[data-profile-async]');
        if (!form || !profileRoot.contains(form)) return;

        event.preventDefault();
        clearFormErrors(form);
        setSubmitting(form, true);

        try {
          const response = await fetch(form.action, {
            method: 'POST',
            headers: {
              'X-Requested-With': 'XMLHttpRequest',
              'Accept': 'application/json',
            },
            body: new FormData(form),
          });

          const data = await response.json().catch(() => ({}));

          if (!response.ok || !data.ok) {
            renderFormErrors(form, data.errors || {});
            if (typeof showToast === 'function') {
              showToast(data.message || 'Saqlashda xatolik yuz berdi.', data.toast_type || 'error');
            }
            return;
          }

          if (data.section && data.html) {
            replaceSection(data.section, data.html);
          }

          if (data.user_email) {
            syncEmailText(data.user_email);
          }

          if (typeof showToast === 'function') {
            showToast(data.message || 'Saqlandi.', data.toast_type || 'success');
          }

          if (data.section === 'password' && data.password_unlocked) {
            profileRoot.querySelector('#profile-new-password')?.focus();
          }

          if (data.section === 'email' && data.pending_email) {
            profileRoot.querySelector('#email-code')?.focus();
          }
        } catch (error) {
          if (typeof showToast === 'function') {
            showToast('Server bilan bog\'lanishda xatolik yuz berdi.', 'error');
          }
        } finally {
          setSubmitting(form, false);
        }
      });
    })();
  </script>
</x-loyouts.main>
