<x-layouts.main :title="__('public.course_create.page_title')">
  @php
    $teacherPreviewCollection = (($isAdmin ?? false) === true ? $teachers : collect([$selectedTeacher]))
      ->filter()
      ->values();

    $teacherPreviewData = $teacherPreviewCollection
      ->map(fn ($teacher) => [
        'id' => (string) $teacher->id,
        'name' => $teacher->full_name,
        'subject' => $teacher->subject ?: __('public.course_create.subject_missing'),
        'experience_label' => __('public.course_create.experience_years', ['years' => (int) $teacher->experience_years]),
        'grades' => $teacher->grades ?: __('profile.all_grades'),
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
    $courseOwnerName = trim((string) ($courseOwner?->name ?: $courseOwner?->buildNameFromParts())) ?: __('public.course_create.default_author');
    $courseOwnerRole = $courseOwner?->localizedRoleLabel() ?: __('public.course_create.default_role');
    $courseOwnerImage = $courseOwner?->avatar_url ?: app_public_asset('temp/img/ChatGPT Image Jul 5, 2026, 01_38_09 AM.png');
    $isAdminEditor = ($isAdmin ?? false) === true;
  @endphp

  <section class="news-hero" id="home">
    <div class="container">
      <div class="news-hero-content reveal">
        <h1>{{ __('public.course_create.hero_title') }}</h1>
        <p>
          @if($requiresEmailVerification)
            {{ __('public.course_create.hero_verify') }}
          @else
            {{ __('public.course_create.hero_instant') }}
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
            <span class="course-create-eyebrow">{{ __('public.course_create.guide_eyebrow') }}</span>
            <h2>{{ __('public.course_create.guide_title') }}</h2>
            <p>{!! __('public.course_create.guide_text') !!}</p>
            <ul class="course-create-guide-list">
              <li><i class="fa-solid fa-check"></i> {{ __('public.course_create.guide_item_1') }}</li>
              <li><i class="fa-solid fa-check"></i> {{ __('public.course_create.guide_item_2') }}</li>
              <li><i class="fa-solid fa-check"></i> {{ __('public.course_create.guide_item_3') }}</li>
              @if($isAdminEditor)
                <li><i class="fa-solid fa-check"></i> {{ __('public.course_create.guide_item_admin') }}</li>
              @else
                <li><i class="fa-solid fa-check"></i> {{ __('public.course_create.guide_item_teacher') }}</li>
              @endif
            </ul>
          </article>

          @if($isAdminEditor)
            <aside
              class="course-create-teacher-card"
              data-course-teacher-preview
              data-course-preview='@json($teacherPreviewData)'
              data-course-preview-fallback="{{ app_public_asset('temp/img/ChatGPT Image Jul 5, 2026, 01_38_09 AM.png') }}"
              data-course-initial-teacher-id="{{ $initialTeacherId }}"
            >
              <div class="course-create-teacher-media">
                <img
                  src="{{ $initialTeacher['image'] ?? app_public_asset('temp/img/ChatGPT Image Jul 5, 2026, 01_38_09 AM.png') }}"
                  alt="{{ __('public.course_create.teacher_image_alt') }}"
                  data-preview-image
                >
                <div>
                  <span class="course-create-eyebrow">{{ __('public.course_create.public_teacher_card') }}</span>
                  <h3 data-preview-name>{{ $initialTeacher['name'] ?? __('public.course_create.teacher_not_selected') }}</h3>
                  <p data-preview-subject>{{ $initialTeacher['subject'] ?? __('public.course_create.select_teacher_first') }}</p>
                </div>
              </div>

              <div class="course-create-teacher-stats">
                <div class="course-create-teacher-stat">
                  <strong data-preview-experience>{{ $initialTeacher['experience_label'] ?? '-' }}</strong>
                  <span>{{ __('public.course_create.experience') }}</span>
                </div>
                <div class="course-create-teacher-stat">
                  <strong data-preview-grades>{{ $initialTeacher['grades'] ?? '-' }}</strong>
                  <span>{{ __('public.course_create.grades') }}</span>
                </div>
              </div>

              <p class="course-create-teacher-bio" data-preview-bio>
                {{ $initialTeacher['bio'] ?? __('public.course_create.teacher_bio_placeholder') }}
              </p>

              <div class="course-create-achievements">
                <h3><i class="fa-solid fa-trophy"></i> {{ __('public.course_create.teacher_card_info') }}</h3>
                <ul data-preview-achievements>
                  @if(!empty($initialTeacher['achievements']))
                    @foreach($initialTeacher['achievements'] as $achievement)
                      <li><i class="fa-solid fa-award"></i> {{ $achievement }}</li>
                    @endforeach
                  @else
                    <li class="course-create-placeholder">{{ __('public.course_create.achievements_placeholder') }}</li>
                  @endif
                </ul>
              </div>
            </aside>
          @else
            <aside class="course-create-teacher-card">
              <div class="course-create-teacher-media">
                <img src="{{ $courseOwnerImage }}" alt="{{ $courseOwnerName }}">
                <div>
                  <span class="course-create-eyebrow">{{ __('public.course_create.course_author') }}</span>
                  <h3>{{ $courseOwnerName }}</h3>
                  <p>{{ $courseOwnerRole }}</p>
                </div>
              </div>

              <div class="course-create-teacher-stats">
                <div class="course-create-teacher-stat">
                  <strong>{{ __('public.course_create.admin_approved') }}</strong>
                  <span>{{ __('public.course_create.verified') }}</span>
                </div>
                <div class="course-create-teacher-stat">
                  <strong>{{ __('public.course_create.one_course') }}</strong>
                  <span>{{ __('public.course_create.limit') }}</span>
                </div>
              </div>

              <p class="course-create-teacher-bio">
                {{ __('public.course_create.teacher_bio_note') }}
              </p>

              <div class="course-create-achievements">
                <h3><i class="fa-solid fa-shield-check"></i> {{ __('public.course_create.flow') }}</h3>
                <ul>
                  <li><i class="fa-solid fa-check"></i> {{ __('public.course_create.flow_item_1') }}</li>
                  <li><i class="fa-solid fa-check"></i> {{ __('public.course_create.flow_item_2') }}</li>
                  <li><i class="fa-solid fa-check"></i> {{ __('public.course_create.flow_item_3') }}</li>
                </ul>
              </div>
            </aside>
          @endif
        </div>

        <form action="{{ route('teacher.courses.store') }}" method="POST" enctype="multipart/form-data" class="comment-form course-create-form" style="max-width: 720px;">
          @csrf

          @if($isAdminEditor)
            <select name="teacher_id" class="form-control" required data-course-teacher-select>
              <option value="">{{ __('public.course_create.select_teacher') }}</option>
              @foreach($teachers as $teacher)
                <option value="{{ $teacher->id }}" {{ old('teacher_id') == $teacher->id ? 'selected' : '' }}>
                  {{ $teacher->full_name }}{{ filled($teacher->subject) ? ' - '.$teacher->subject : '' }}
                </option>
              @endforeach
            </select>
          @else
            <p class="comment-hint" style="margin:0 0 16px;padding:12px 14px;background:rgba(13,63,120,0.06);border-radius:12px;border:1px solid var(--border, #d7e3f4);">
              <i class="fa-solid fa-user-check"></i>
              {!! __('public.course_create.account_note') !!}
              <span class="profile-muted" style="display:block;margin-top:8px;font-size:13px;">{{ __('public.course_create.author_line', ['name' => $courseOwnerName, 'role' => $courseOwnerRole]) }}</span>
            </p>
          @endif

          <input type="text" name="title" class="comment-input" placeholder="{{ __('public.course_create.title_placeholder') }}" value="{{ old('title') }}" required>
          <input type="text" name="title_en" class="comment-input" placeholder="{{ __('public.course_create.title_en_placeholder') }}" value="{{ old('title_en') }}">
          <input type="text" name="price" class="comment-input" placeholder="{{ __('public.course_create.price_placeholder') }}" value="{{ old('price') }}" required>
          <input type="text" name="price_en" class="comment-input" placeholder="{{ __('public.course_create.price_en_placeholder') }}" value="{{ old('price_en') }}">
          <input type="text" name="duration" class="comment-input" placeholder="{{ __('public.course_create.duration_placeholder') }}" value="{{ old('duration') }}" required>
          <input type="text" name="duration_en" class="comment-input" placeholder="{{ __('public.course_create.duration_en_placeholder') }}" value="{{ old('duration_en') }}">
          <label class="comment-label" for="course-start-date">{{ __('public.course_create.start_date') }}</label>
          @include('partials.flatpickr-inline-date-field', [
            'name' => 'start_date',
            'id' => 'course-start-date',
            'value' => old('start_date'),
            'required' => true,
          ])
          @error('start_date')
            <p class="form-message" style="color:#b91c1c;">{{ $message }}</p>
          @enderror
          <textarea name="description" rows="5" class="comment-input" placeholder="{{ __('public.course_create.description_placeholder') }}" required>{{ old('description') }}</textarea>
          <textarea name="description_en" rows="5" class="comment-input" placeholder="{{ __('public.course_create.description_en_placeholder') }}">{{ old('description_en') }}</textarea>

          <label for="course-image" class="comment-label">{{ __('public.course_create.image_label') }}</label>
          <input type="file" id="course-image" name="image" class="comment-input" accept="image/jpeg,image/png,image/webp">
          @error('image')
            <p class="form-message" style="color:#b91c1c;">{{ $message }}</p>
          @enderror

          <button class="btn" type="submit">
            @if($requiresEmailVerification)
              <i class="fa-solid fa-paper-plane"></i> {{ __('public.course_create.submit_verify') }}
            @else
              <i class="fa-solid fa-check"></i> {{ __('public.course_create.submit_publish') }}
            @endif
          </button>
        </form>
      </div>
    </section>
  </main>

  @push('page_scripts')
    <script src="{{ app_public_asset('temp/js/course-create-page.js') }}?v={{ app_asset_version('temp/js/course-create-page.js') }}"></script>
  @endpush
</x-loyouts.main>
