<x-layouts.main :title="__('profile.verify_course.page_title')">
  <section class="news-hero" id="home">
    <div class="container">
      <div class="news-hero-content prime-reveal">
        <h1>{{ __('profile.verify_course.title') }}</h1>
        <p><strong>{{ $course->title }}</strong> {{ __('profile.verify_course.subtitle', ['title' => $course->title]) }}</p>
      </div>
    </div>
  </section>
  <main class="news">
    <section class="container news reveal glass-section">
      <form action="{{ route('teacher.courses.verify', $course) }}" method="POST" class="comment-form" style="max-width: 520px;">
        @csrf
        <input type="text" name="code" class="comment-input" maxlength="6" placeholder="{{ __('profile.verify_course.code_placeholder') }}" required>
        <button class="btn" type="submit">{{ __('profile.verify_course.confirm_btn') }}</button>
      </form>
      <form action="{{ route('teacher.courses.verify.resend', $course) }}" method="POST" style="margin-top:12px;">
        @csrf
        <button class="btn btn-outline" type="submit">{{ __('profile.verify_course.resend_btn') }}</button>
      </form>
    </section>
  </main>
</x-loyouts.main>
