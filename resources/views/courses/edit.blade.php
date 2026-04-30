<x-loyouts.main title="81-IDUM | Kursni tahrirlash">
  @php
    $teacherPreviewCollection = collect([$selectedTeacher])->filter()->values();

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
    $initialTeacher = collect($teacherPreviewData)->firstWhere('id', $initialTeacherId) ?? [
      'name' => $course->instructorName(),
      'subject' => $course->instructorSubject(),
      'experience_label' => $course->instructorExperienceLabel(),
      'grades' => $course->instructorGradesLabel(),
      'bio' => $course->instructorBio(220),
      'image' => $course->instructorImageUrl(),
      'achievements' => $course->instructorAchievements(),
    ];
  @endphp

  <section class="news-hero" id="home">
    <div class="container">
      <div class="news-hero-content reveal">
        <h1>Kursni tahrirlash</h1>
        <p>
          Ma'lumotlarni yangilang va saqlang. O'zgarishlar saytdagi kurs kartasiga qo'llanadi.
        </p>
      </div>
    </div>
  </section>

  <main class="news">
    <section class="container news reveal glass-section">
      <div class="course-create-shell">
        <p style="margin:0 0 18px;">
          <a href="{{ route('profile.show') }}#profile-created-courses" class="btn btn-outline btn-sm">
            <i class="fa-solid fa-arrow-left"></i> Profilga qaytish
          </a>
          <a href="{{ route('courses.show', $course) }}" class="btn btn-outline btn-sm" style="margin-left:8px;">
            <i class="fa-solid fa-eye"></i> Kursni ko'rish
          </a>
        </p>

        <div class="course-create-info-grid">
          <article class="course-create-guide">
            <span class="course-create-eyebrow">Eslatma</span>
            <h2>Kurs kartasi</h2>
            <p>
              Saqlagach, kurs nomi, narx va tavsif saytda yangilanadi. Kurs muallifi akkauntingiz orqali aniqlanadi,
              public ustoz kartasiga bog'lash majburiy emas.
            </p>
          </article>

          <aside
            class="course-create-teacher-card"
            @if($selectedTeacher)
              data-course-teacher-preview
              data-course-preview='@json($teacherPreviewData)'
              data-course-preview-fallback="{{ app_public_asset('temp/img/how-to-be-teacher-malaysia-feature.png') }}"
              data-course-initial-teacher-id="{{ $initialTeacherId }}"
            @endif
          >
            <div class="course-create-teacher-media">
              <img
                src="{{ $initialTeacher['image'] ?? app_public_asset('temp/img/how-to-be-teacher-malaysia-feature.png') }}"
                alt="{{ $initialTeacher['name'] ?? 'Kurs muallifi' }}"
                @if($selectedTeacher) data-preview-image @endif
              >
              <div>
                <span class="course-create-eyebrow">Kurs muallifi</span>
                <h3 @if($selectedTeacher) data-preview-name @endif>{{ $initialTeacher['name'] ?? 'Kurs muallifi' }}</h3>
                <p @if($selectedTeacher) data-preview-subject @endif>{{ $initialTeacher['subject'] ?? "O'qituvchi" }}</p>
              </div>
            </div>

            <div class="course-create-teacher-stats">
              <div class="course-create-teacher-stat">
                <strong @if($selectedTeacher) data-preview-experience @endif>{{ $initialTeacher['experience_label'] ?? '-' }}</strong>
                <span>Tajriba</span>
              </div>
              <div class="course-create-teacher-stat">
                <strong @if($selectedTeacher) data-preview-grades @endif>{{ $initialTeacher['grades'] ?? '-' }}</strong>
                <span>Sinflar</span>
              </div>
            </div>

            <p class="course-create-teacher-bio" @if($selectedTeacher) data-preview-bio @endif>
              {{ $initialTeacher['bio'] ?? '' }}
            </p>

            <div class="course-create-achievements">
              <h3><i class="fa-solid fa-trophy"></i> Yutuqlar</h3>
              <ul @if($selectedTeacher) data-preview-achievements @endif>
                @if(!empty($initialTeacher['achievements']))
                  @foreach($initialTeacher['achievements'] as $achievement)
                    <li><i class="fa-solid fa-award"></i> {{ $achievement }}</li>
                  @endforeach
                @else
                  <li class="course-create-placeholder">Kurs akkaunt muallifi nomidan ko'rsatiladi.</li>
                @endif
              </ul>
            </div>
          </aside>
        </div>

        <form action="{{ route('teacher.courses.update', $course) }}" method="POST" enctype="multipart/form-data" class="comment-form course-create-form" style="max-width: 720px;">
          @csrf
          @method('PUT')

          <p class="comment-hint" style="margin:0 0 16px;padding:12px 14px;background:rgba(13,63,120,0.06);border-radius:12px;border:1px solid var(--border, #d7e3f4);">
            <i class="fa-solid fa-user-check"></i>
            Kurs <strong>sizning akkauntingiz nomidan</strong> boshqariladi. Public ustoz kartasiga bog'lash shart emas.
          </p>

          <input type="text" name="title" class="comment-input" placeholder="Kurs nomi" value="{{ old('title', $course->title) }}" required>
          <input type="text" name="title_en" class="comment-input" placeholder="Course title (EN, optional)" value="{{ old('title_en', $course->title_en) }}">
          <input type="text" name="price" class="comment-input" placeholder="Narxi" value="{{ old('price', $course->price) }}" required>
          <input type="text" name="price_en" class="comment-input" placeholder="Price (EN, optional)" value="{{ old('price_en', $course->price_en) }}">
          <input type="text" name="duration" class="comment-input" placeholder="Davomiyligi" value="{{ old('duration', $course->duration) }}" required>
          <input type="text" name="duration_en" class="comment-input" placeholder="Duration (EN, optional)" value="{{ old('duration_en', $course->duration_en) }}">
          <label class="comment-label" for="course-start-date-edit">Boshlanish sanasi</label>
          @include('partials.flatpickr-inline-date-field', [
            'name' => 'start_date',
            'id' => 'course-start-date-edit',
            'value' => old('start_date', $course->start_date?->format('Y-m-d')),
            'required' => true,
          ])
          @error('start_date')
            <p class="form-message" style="color:#b91c1c;">{{ $message }}</p>
          @enderror
          <textarea name="description" rows="5" class="comment-input" placeholder="Kurs tavsifi" required>{{ old('description', $course->description) }}</textarea>
          <textarea name="description_en" rows="5" class="comment-input" placeholder="Course description (EN, optional)">{{ old('description_en', $course->description_en) }}</textarea>

          <label for="course-image-edit" class="comment-label">Kurs rasmi (ixtiyoriy, yangi yuklasangiz almashtiriladi)</label>
          @if($course->image)
            <p class="comment-hint" style="margin-top:0;">Joriy rasm: <a href="{{ $course->coverImageUrl() }}" target="_blank" rel="noopener">ko'rish</a></p>
          @endif
          <input type="file" id="course-image-edit" name="image" class="comment-input" accept="image/jpeg,image/png,image/webp">
          @error('image')
            <p class="form-message" style="color:#b91c1c;">{{ $message }}</p>
          @enderror

          <button class="btn" type="submit">
            <i class="fa-solid fa-floppy-disk"></i> O'zgarishlarni saqlash
          </button>
        </form>
      </div>
    </section>
  </main>

  @push('page_scripts')
    <script src="{{ app_public_asset('temp/js/course-create-page.js') }}?v={{ filemtime(public_path('temp/js/course-create-page.js')) }}"></script>
  @endpush
</x-loyouts.main>
