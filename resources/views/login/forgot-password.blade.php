<x-loyouts.main :title="__('auth_pages.forgot.page_title')">
  <section class="signin-hero">
    <div class="container">
      <h1>{{ __('auth_pages.forgot.hero_title') }}</h1>
      <p>{{ __('auth_pages.forgot.hero_text') }}</p>
    </div>
  </section>

  <main class="signin-section">
    <div class="container">
      <div class="signin-card">
        <div class="signin-card-icon">
          <i class="fa-solid fa-unlock-keyhole"></i>
        </div>
        <h2>{{ __('auth_pages.forgot.card_title') }}</h2>
        <p class="signin-subtitle">{{ __('auth_pages.forgot.subtitle') }}</p>

        <div class="signin-helper-box">
          <strong>{{ __('auth_pages.forgot.helper_title') }}</strong>
          <p>{{ __('auth_pages.forgot.helper_text') }}</p>
        </div>

        <form action="{{ route('password.forgot.send') }}" method="POST" class="signin-form">
          @csrf
          <label for="forgot-email">{{ __('auth_pages.forgot.email') }}</label>
          <input
            type="email"
            id="forgot-email"
            name="email"
            value="{{ old('email', $email ?? '') }}"
            placeholder="{{ __('auth_pages.forgot.email_placeholder') }}"
            required
            autocomplete="email"
          />
          @error('email')
            <p class="form-message" style="color:#b91c1c;">{{ $message }}</p>
          @enderror

          <button class="btn" type="submit">{{ __('auth_pages.forgot.submit') }}</button>
        </form>

        <div class="signin-divider">
          <span>{{ __('auth_pages.common.or') }}</span>
        </div>

        <p class="signin-register">
          {{ __('auth_pages.forgot.login_text') }} <a href="{{ route('login') }}">{{ __('auth_pages.forgot.login_link') }}</a>
        </p>
      </div>
    </div>
  </main>
</x-loyouts.main>
