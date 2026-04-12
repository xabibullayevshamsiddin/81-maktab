@php
  $likedTeacherIds = $likedTeacherIds ?? collect();
@endphp

<div class="teachers-grid related-teachers-grid">
  @foreach($relatedTeachers as $rt)
    @php
      $teacherSubject = localized_model_value($rt, 'subject');
      $teacherLavozim = localized_model_value($rt, 'lavozim');
      $teacherAchievements = localized_model_value($rt, 'achievements');
      $teacherAchievementPreview = \Illuminate\Support\Str::limit(trim((string) strtok($teacherAchievements, "\n")), 100);
    @endphp
    <article class="teacher-card reveal">
      <div class="teacher-photo-wrap">
        <img
          src="{{ $rt->image ? app_storage_asset($rt->image) : app_public_asset('temp/img/how-to-be-teacher-malaysia-feature.png') }}"
          alt="{{ $rt->full_name }} profil rasmi"
          class="teacher-photo"
          loading="lazy"
          decoding="async"
        />
      </div>
      <div class="teacher-top">
        <div>
          <h3>{{ $rt->full_name }}</h3>
          @php $teacherRoleLine = $teacherLavozim ?: $teacherSubject; @endphp
          @if(filled($teacherRoleLine))
            <p class="teacher-role">{{ $teacherRoleLine }}</p>
          @endif
        </div>
      </div>
      <p class="teacher-desc">{{ $rt->shortBio(220) }}</p>
      <ul class="teacher-meta">
        <li><i class="fa-solid fa-award"></i> {{ __('public.common.years_experience', ['count' => $rt->experience_years]) }}</li>
        <li><i class="fa-solid fa-users"></i> {{ $rt->grades ?: __('public.common.all_grades') }}</li>
      </ul>
      @if(filled($teacherAchievements))
        <p class="teacher-achievements-preview"><i class="fa-solid fa-trophy"></i> {{ $teacherAchievementPreview }}</p>
      @endif
      <div class="teacher-actions">
        @auth
          <form action="{{ route('teacher.like', $rt) }}" method="POST" class="js-like-form">
            @csrf
            <button class="like-btn {{ $likedTeacherIds->contains($rt->id) ? 'liked' : '' }}" type="submit" aria-label="{{ __('public.posts.like_aria') }}">
              <i class="{{ $likedTeacherIds->contains($rt->id) ? 'fa-solid' : 'fa-regular' }} fa-heart"></i>
              <span class="like-count">{{ $rt->likes_count ?? 0 }}</span>
            </button>
          </form>
        @endauth
        <button
          type="button"
          class="btn btn-sm btn-outline share-btn js-share-trigger"
          data-share-url="{{ route('teacher.show', $rt) }}"
          data-share-title="{{ $rt->full_name }}"
          data-share-text="{{ __('public.teachers.share_text') }}"
          data-share-success="{{ __('public.teachers.share_success') }}"
        >
          <i class="fa-solid fa-share-nodes"></i> {{ __('public.common.share') }}
        </button>
        <a href="{{ route('teacher.show', $rt) }}" class="btn btn-sm">{{ __('public.common.details') }}</a>
      </div>
    </article>
  @endforeach
</div>
