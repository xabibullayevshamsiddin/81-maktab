<x-loyouts.main title="81-IDUM | Profil">
  <section class="news-hero profile-hero">
    <div class="container">
      <div class="news-hero-content reveal">
        <span class="badge">Shaxsiy kabinet</span>
        <h1><strong>Profil</strong></h1>
        <p>Ism, telefon va emailni boshqaring; yangiliklar va ustozlar bo‘yicha faolligingizni ko‘ring.</p>
      </div>
    </div>
  </section>

  <main class="profile-main">
    <div class="container">
      <div class="profile-layout">
        <div class="profile-column profile-column-settings">
          <div class="signin-card profile-card">
            <h2>Asosiy maʼlumotlar</h2>
            <p class="signin-subtitle">Ism va telefon — kod talab qilinmaydi.</p>

            <form action="{{ route('profile.update') }}" method="POST" class="signin-form comment-form">
              @csrf
              @method('PUT')
              <label for="profile-name">Ism (nik)</label>
              <input type="text" id="profile-name" name="name" value="{{ old('name', $user->name) }}" required maxlength="120" autocomplete="name" />
              @error('name')
                <p class="form-message" style="color:#b91c1c;">{{ $message }}</p>
              @enderror

              <label for="profile-phone">Telefon</label>
              <input type="text" id="profile-phone" name="phone" value="{{ old('phone', $user->phone) }}" maxlength="40" placeholder="+998 …" autocomplete="tel" />
              @error('phone')
                <p class="form-message" style="color:#b91c1c;">{{ $message }}</p>
              @enderror

              <p class="profile-meta-line">
                <span class="profile-role-badge">{{ $user->role_label }}</span>
                <span class="profile-muted">Joriy email: {{ $user->email }}</span>
              </p>

              <button class="btn" type="submit">Saqlash</button>
            </form>
          </div>

          <div class="signin-card profile-card">
            <h2>Emailni almashtirish</h2>
            <p class="signin-subtitle">Yangi manzilga kod yuboriladi; kodni tasdiqlagach email yangilanadi.</p>

            @if($pendingEmail !== '')
              <p class="profile-pending-email">
                <i class="fa-solid fa-envelope"></i>
                Tasdiqlanishi kutilmoqda: <strong>{{ $pendingEmail }}</strong>
              </p>

              <form action="{{ route('profile.email.verify') }}" method="POST" class="signin-form comment-form">
                @csrf
                <label for="email-code">6 xonali kod</label>
                <input type="text" id="email-code" name="code" inputmode="numeric" maxlength="6" placeholder="123456" required autocomplete="one-time-code" />
                @error('code')
                  <p class="form-message" style="color:#b91c1c;">{{ $message }}</p>
                @enderror
                <button class="btn" type="submit">Emailni tasdiqlash</button>
              </form>

              <div class="profile-email-actions">
                <form action="{{ route('profile.email.resend') }}" method="POST">
                  @csrf
                  <button class="btn btn-outline" type="submit">Kodni qayta yuborish</button>
                </form>
                <form action="{{ route('profile.email.cancel') }}" method="POST">
                  @csrf
                  <button class="btn btn-outline" type="submit">Bekor qilish</button>
                </form>
              </div>
            @else
              <form action="{{ route('profile.email.request') }}" method="POST" class="signin-form comment-form">
                @csrf
                <label for="new-email">Yangi email</label>
                <input type="email" id="new-email" name="email" value="{{ old('email') }}" required autocomplete="email" />
                @error('email')
                  <p class="form-message" style="color:#b91c1c;">{{ $message }}</p>
                @enderror
                <button class="btn" type="submit">Kod yuborish</button>
              </form>
            @endif
          </div>
        </div>

        <div class="profile-column profile-column-activity">
          @if($canViewCourseEnrollments ?? false)
            <section class="profile-activity-block">
              <h3><i class="fa-solid fa-clipboard-check"></i> Kursga yozilish arizalari</h3>
              <p class="profile-muted" style="margin-bottom:12px;">O‘z kursingizga tushgan arizalarni ko‘ring va tasdiqlang.</p>
              @if(($pendingTeacherEnrollments ?? collect())->isNotEmpty())
                <ul class="profile-activity-list profile-pending-enrollment-list">
                  @foreach($pendingTeacherEnrollments as $pen)
                    <li class="profile-pending-enrollment-item">
                      <strong>{{ $pen->course?->title ?: '—' }}</strong>
                      <span class="profile-muted"> · {{ $pen->user?->name ?: '—' }}</span>
                      @if($pen->contact_phone)
                        <span class="profile-muted"> · {{ $pen->contact_phone }}</span>
                      @endif
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
              @endif
              <div class="profile-actions-row">
                <a href="{{ route('teacher.enrollments.index') }}" class="btn btn-sm">Barcha arizalar</a>
                <a href="{{ route('admin.courses.index') }}" class="btn btn-outline btn-sm">Admin: kurslar</a>
              </div>
            </section>
          @endif

          <section class="profile-activity-block">
            <h3><i class="fa-regular fa-comments"></i> Yangiliklar postlariga izohlar</h3>
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
                <li class="profile-empty">Hozircha izoh yo‘q.</li>
              @endforelse
            </ul>
          </section>

          <section class="profile-activity-block">
            <h3><i class="fa-regular fa-message"></i> Ustozlar sahifasidagi izohlar</h3>
            <ul class="profile-activity-list">
              @forelse($teacherComments as $c)
                <li>
                  @if($c->parent_id)
                    <span class="profile-tag">Javob</span>
                  @endif
                  <p class="profile-activity-body">{{ \Illuminate\Support\Str::limit($c->body, 160) }}</p>
                  <a class="profile-activity-link" href="{{ route('teacher') }}">Ustozlar</a>
                  <span class="profile-activity-date">{{ $c->created_at?->diffForHumans() }}</span>
                </li>
              @empty
                <li class="profile-empty">Hozircha izoh yo‘q.</li>
              @endforelse
            </ul>
          </section>

          <section class="profile-activity-block">
            <h3><i class="fa-regular fa-heart"></i> Yoqtirilgan yangiliklar</h3>
            <ul class="profile-activity-list profile-activity-list-compact">
              @forelse($likedPosts as $like)
                <li>
                  @if($like->post)
                    <a class="profile-activity-link" href="{{ route('post.show', $like->post->slug) }}">{{ $like->post->title }}</a>
                  @else
                    <span class="profile-muted">Post o‘chirilgan</span>
                  @endif
                  <span class="profile-activity-date">{{ $like->created_at?->diffForHumans() }}</span>
                </li>
              @empty
                <li class="profile-empty">Hozircha yoqtirish yo‘q.</li>
              @endforelse
            </ul>
          </section>

          <section class="profile-activity-block">
            <h3><i class="fa-solid fa-chalkboard-user"></i> Yoqtirilgan ustozlar</h3>
            <ul class="profile-activity-list profile-activity-list-compact">
              @forelse($teacherLikes as $tl)
                <li>
                  @if($tl->teacher)
                    <a class="profile-activity-link" href="{{ route('teacher.show', $tl->teacher->slug) }}">{{ $tl->teacher->full_name }}</a>
                  @else
                    <span class="profile-muted">Ustoz o‘chirilgan</span>
                  @endif
                  <span class="profile-activity-date">{{ $tl->created_at?->diffForHumans() }}</span>
                </li>
              @empty
                <li class="profile-empty">Hozircha yoqtirish yo‘q.</li>
              @endforelse
            </ul>
          </section>

          <section class="profile-activity-block">
            <h3><i class="fa-solid fa-clipboard-list"></i> Yozilgan kurslar</h3>
            <ul class="profile-activity-list">
              @forelse($courseEnrollments as $enrollment)
                <li>
                  @if($enrollment->course)
                    <span class="profile-activity-title">{{ $enrollment->course->title }}</span>
                    @if($enrollment->isPending())
                      <span class="profile-tag">Kutilmoqda</span>
                    @elseif($enrollment->isApproved())
                      <span class="profile-tag" style="background:rgba(15,118,110,.15);color:#0f766e;">Qabul</span>
                    @else
                      <span class="profile-tag" style="background:rgba(185,28,28,.12);color:#b91c1c;">Rad</span>
                    @endif
                    @if($enrollment->course->teacher)
                      <span class="profile-muted">{{ $enrollment->course->teacher->full_name }}</span>
                    @endif
                    <a class="profile-activity-link" href="{{ route('courses') }}">Kurslar sahifasi</a>
                  @else
                    <span class="profile-muted">Kurs o‘chirilgan</span>
                  @endif
                  @if($enrollment->note)
                    <p class="profile-enroll-note">{{ \Illuminate\Support\Str::limit($enrollment->note, 200) }}</p>
                  @endif
                  <span class="profile-activity-date">{{ $enrollment->created_at?->diffForHumans() }}</span>
                </li>
              @empty
                <li class="profile-empty">Hozircha yozilgan kurs yo‘q.</li>
              @endforelse
            </ul>
          </section>

          @if($createdCourses->isNotEmpty())
            <section class="profile-activity-block">
              <h3><i class="fa-solid fa-book-open"></i> Yaratilgan kurslar</h3>
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
                      <span class="profile-muted">{{ $course->teacher->full_name }}</span>
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
</x-loyouts.main>
