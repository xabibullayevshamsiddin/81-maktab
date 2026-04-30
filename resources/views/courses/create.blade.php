<x-loyouts.main title="81-IDUM | Kurs ochish">
  @php
    $teacherPreviewCollection = (($isAdmin ?? false) === true ? $teachers : collect([$selectedTeacher]))
      ->filter()
      ->values();

    $teacherPreviewData = $teacherPreviewCollection
      ->map(fn ($teacher) => [
        'id' => (string) $teacher->id,
        'name' => $teacher->full_name,
        'subject' => $teacher->subject ?: "Fan ko'rsatilmagan",
        'experience_label' => ((int) $teacher->experience_years).' yil tajriba',
        'grades' => $teacher->grades ?: 'Barcha sinflar',
        'bio' => $teacher->shortBio(220),
        'image' => $teacher->imageUrl(),
        'achievements' => $teacher->achievementItems(4),
      ])
      ->values()
      ->all();

    $initialTeacherId = (string) old('teacher_id', $selectedTeacher?->id ?? '');
    $initialTeacher = collect($teacherPreviewData)->firstWhere('id', $initialTeacherId);
    $requiresEmailVerification = (bool) ($courseEmailVerificationEnabled ?? false);
    $courseOwner = $courseOwner ?? auth()->user();
    $courseOwnerName = trim((string) ($courseOwner?->name ?: $courseOwner?->buildNameFromParts())) ?: 'Kurs muallifi';
    $courseOwnerRole = $courseOwner?->localizedRoleLabel() ?: "O'qituvchi";
    $courseOwnerImage = $courseOwner?->avatar_url ?: app_public_asset('temp/img/how-to-be-teacher-malaysia-feature.png');
    $isAdminEditor = ($isAdmin ?? false) === true;
  @endphp

  <section class="news-hero" id="home">
    <div class="container">
      <div class="news-hero-content reveal">
        <h1>Kurs ochish</h1>
        <p>
          @if($requiresEmailVerification)
            Ustoz/Admin kurs ma'lumotlarini kiriting, email kod bilan tasdiqlang.
          @else
            Ustoz/Admin kurs ma'lumotlarini kiriting; kurs yaratilgach darhol saytda chiqadi.
          @endif
        </p>
      </div>
    </div>
  </section>

  <main class="news">
    <section class="container news reveal glass-section">
      <div class="course-create-shell">
        <div class="course-create-info-grid">
          <article class="course-create-guide">
            <span class="course-create-eyebrow">Kurs ochishdan oldin</span>
            <h2>Talabalar nimani ko'radi?</h2>
            <p>
              Kurs kartasida endi <strong>Kurs haqida ma'lumot</strong> tugmasi chiqadi.
              Shu oynada kurs tavsifi bilan birga kursni ochgan muallif,
              yo'nalish va yozilish ma'lumotlari ko'rsatiladi.
            </p>
            <ul class="course-create-guide-list">
              <li><i class="fa-solid fa-check"></i> Kurs nomi va narxni tushunarli kiriting.</li>
              <li><i class="fa-solid fa-check"></i> Tavsifda natija, mavzular va kimlar uchun ekanini yozing.</li>
              <li><i class="fa-solid fa-check"></i> Boshlanish sanasi va davomiylik real jadvalga mos bo'lsin.</li>
              @if($isAdminEditor)
                <li><i class="fa-solid fa-check"></i> Xohlasangiz kursni public ustoz kartasiga biriktiring.</li>
              @else
                <li><i class="fa-solid fa-check"></i> Kurs sizning akkauntingiz nomidan ochiladi; ustoz kartasiga bog'lash shart emas.</li>
              @endif
            </ul>
          </article>

          @if($isAdminEditor)
            <aside
              class="course-create-teacher-card"
              data-course-teacher-preview
              data-course-preview='@json($teacherPreviewData)'
              data-course-preview-fallback="{{ app_public_asset('temp/img/how-to-be-teacher-malaysia-feature.png') }}"
              data-course-initial-teacher-id="{{ $initialTeacherId }}"
            >
              <div class="course-create-teacher-media">
                <img
                  src="{{ $initialTeacher['image'] ?? app_public_asset('temp/img/how-to-be-teacher-malaysia-feature.png') }}"
                  alt="Ustoz rasmi"
                  data-preview-image
                >
                <div>
                  <span class="course-create-eyebrow">Public ustoz kartasi</span>
                  <h3 data-preview-name>{{ $initialTeacher['name'] ?? 'Ustoz tanlanmagan' }}</h3>
                  <p data-preview-subject>{{ $initialTeacher['subject'] ?? "Avval ustozni tanlang" }}</p>
                </div>
              </div>

              <div class="course-create-teacher-stats">
                <div class="course-create-teacher-stat">
                  <strong data-preview-experience>{{ $initialTeacher['experience_label'] ?? '-' }}</strong>
                  <span>Tajriba</span>
                </div>
                <div class="course-create-teacher-stat">
                  <strong data-preview-grades>{{ $initialTeacher['grades'] ?? '-' }}</strong>
                  <span>Sinflar</span>
                </div>
              </div>

              <p class="course-create-teacher-bio" data-preview-bio>
                {{ $initialTeacher['bio'] ?? "Tanlangan ustozning qisqa ma'lumoti shu yerda ko'rinadi." }}
              </p>

              <div class="course-create-achievements">
                <h3><i class="fa-solid fa-trophy"></i> Ustoz kartasi ma'lumotlari</h3>
                <ul data-preview-achievements>
                  @if(!empty($initialTeacher['achievements']))
                    @foreach($initialTeacher['achievements'] as $achievement)
                      <li><i class="fa-solid fa-award"></i> {{ $achievement }}</li>
                    @endforeach
                  @else
                    <li class="course-create-placeholder">Ustoz tanlanganda yutuqlar shu yerda chiqadi.</li>
                  @endif
                </ul>
              </div>
            </aside>
          @else
            <aside class="course-create-teacher-card">
              <div class="course-create-teacher-media">
                <img src="{{ $courseOwnerImage }}" alt="{{ $courseOwnerName }}">
                <div>
                  <span class="course-create-eyebrow">Kurs muallifi</span>
                  <h3>{{ $courseOwnerName }}</h3>
                  <p>{{ $courseOwnerRole }}</p>
                </div>
              </div>

              <div class="course-create-teacher-stats">
                <div class="course-create-teacher-stat">
                  <strong>Admin ruxsati</strong>
                  <span>Tekshirildi</span>
                </div>
                <div class="course-create-teacher-stat">
                  <strong>1 ta kurs</strong>
                  <span>Limit</span>
                </div>
              </div>

              <p class="course-create-teacher-bio">
                Kurs sizning teacher akkauntingiz nomidan ochiladi. Admin ruxsati bir martalik:
                kurs joylangandan keyin keyingi kurs uchun qayta so'rov yuborish kerak bo'ladi.
              </p>

              <div class="course-create-achievements">
                <h3><i class="fa-solid fa-shield-check"></i> Oqim</h3>
                <ul>
                  <li><i class="fa-solid fa-check"></i> Teacher akkaunt yetarli.</li>
                  <li><i class="fa-solid fa-check"></i> Ustoz kartasiga bog'lash shart emas.</li>
                  <li><i class="fa-solid fa-check"></i> Kursni faqat siz va admin boshqaradi.</li>
                </ul>
              </div>
            </aside>
          @endif
        </div>

        <form action="{{ route('teacher.courses.store') }}" method="POST" enctype="multipart/form-data" class="comment-form course-create-form" style="max-width: 720px;">
          @csrf

          @if($isAdminEditor)
            <select name="teacher_id" class="form-control" required data-course-teacher-select>
              <option value="">Ustozni tanlang</option>
              @foreach($teachers as $teacher)
                <option value="{{ $teacher->id }}" {{ old('teacher_id') == $teacher->id ? 'selected' : '' }}>
                  {{ $teacher->full_name }}{{ filled($teacher->subject) ? ' - '.$teacher->subject : '' }}
                </option>
              @endforeach
            </select>
          @else
            <p class="comment-hint" style="margin:0 0 16px;padding:12px 14px;background:rgba(13,63,120,0.06);border-radius:12px;border:1px solid var(--border, #d7e3f4);">
              <i class="fa-solid fa-user-check"></i>
              Kurs <strong>sizning akkauntingiz nomidan</strong> yaratiladi - public ustoz kartasini tanlash shart emas.
              <span class="profile-muted" style="display:block;margin-top:8px;font-size:13px;">Muallif: {{ $courseOwnerName }} - {{ $courseOwnerRole }}</span>
            </p>
          @endif

          <input type="text" name="title" class="comment-input" placeholder="Kurs nomi" value="{{ old('title') }}" required>
          <input type="text" name="title_en" class="comment-input" placeholder="Course title (EN, optional)" value="{{ old('title_en') }}">
          <input type="text" name="price" class="comment-input" placeholder="Narxi (masalan: 450 000 so'm)" value="{{ old('price') }}" required>
          <input type="text" name="price_en" class="comment-input" placeholder="Price (EN, optional)" value="{{ old('price_en') }}">
          <input type="text" name="duration" class="comment-input" placeholder="Davomiyligi (masalan: 3 oy)" value="{{ old('duration') }}" required>
          <input type="text" name="duration_en" class="comment-input" placeholder="Duration (EN, optional)" value="{{ old('duration_en') }}">
          <label class="comment-label" for="course-start-date">Boshlanish sanasi</label>
          @include('partials.flatpickr-inline-date-field', [
            'name' => 'start_date',
            'id' => 'course-start-date',
            'value' => old('start_date'),
            'required' => true,
          ])
          @error('start_date')
            <p class="form-message" style="color:#b91c1c;">{{ $message }}</p>
          @enderror
          <textarea name="description" rows="5" class="comment-input" placeholder="Kurs tavsifi" required>{{ old('description') }}</textarea>
          <textarea name="description_en" rows="5" class="comment-input" placeholder="Course description (EN, optional)">{{ old('description_en') }}</textarea>

          <label for="course-image" class="comment-label">Kurs rasmi (ixtiyoriy, JPG/PNG/WebP, max 4 MB)</label>
          <input type="file" id="course-image" name="image" class="comment-input" accept="image/jpeg,image/png,image/webp">
          @error('image')
            <p class="form-message" style="color:#b91c1c;">{{ $message }}</p>
          @enderror

          <button class="btn" type="submit">
            @if($requiresEmailVerification)
              <i class="fa-solid fa-paper-plane"></i> Email kod yuborish
            @else
              <i class="fa-solid fa-check"></i> Kursni joylash
            @endif
          </button>
        </form>
      </div>
    </section>
  </main>

  @push('page_scripts')
    <script src="{{ app_public_asset('temp/js/course-create-page.js') }}?v={{ filemtime(public_path('temp/js/course-create-page.js')) }}"></script>
  @endpush
</x-loyouts.main>
