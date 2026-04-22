<x-loyouts.main :title="__('auth_pages.reset.page_title')">
  <section class="signin-hero">
    <div class="container">
      <h1>{{ __('auth_pages.reset.hero_title') }}</h1>
      <p>{{ __('auth_pages.reset.hero_text', ['email' => $email]) }}</p>
    </div>
  </section>

  <main class="signin-section">
    <div class="container">
      <div class="signin-card">
        <div class="signin-card-icon">
          <i class="fa-solid fa-shield-keyhole"></i>
        </div>
        <h2>{{ __('auth_pages.reset.card_title') }}</h2>
        <p class="signin-subtitle">{{ __('auth_pages.reset.subtitle') }}</p>

        <div class="signin-helper-box">
          <strong>{{ __('auth_pages.reset.selected_email') }}</strong>
          <span class="signin-email-pill">{{ $email }}</span>
        </div>

        <form action="{{ route('password.reset') }}" method="POST" class="signin-form">
          @csrf
          <input type="hidden" name="email" value="{{ old('email', $email) }}">

          <label for="reset-code">{{ __('auth_pages.reset.code') }}</label>
          <input
            type="text"
            id="reset-code"
            name="code"
            value="{{ old('code') }}"
            placeholder="{{ __('auth_pages.reset.code_placeholder') }}"
            maxlength="6"
            required
            autocomplete="one-time-code"
          />
          @error('code')
            <p class="form-message" style="color:#b91c1c;">{{ $message }}</p>
          @enderror
          @error('email')
            <p class="form-message" style="color:#b91c1c;">{{ $message }}</p>
          @enderror

          <label for="reset-password">{{ __('auth_pages.reset.password') }}</label>
          <div class="pw-wrap">
            <input
            type="password"
            id="reset-password"
            name="password"
            placeholder="{{ __('auth_pages.reset.password_placeholder') }}"
            required
            minlength="8"
            autocomplete="new-password"
          />
            <button type="button" class="pw-toggle" aria-label="{{ __('auth_pages.common.show_password') }}" data-target="reset-password">
              <i class="fa-regular fa-eye"></i>
            </button>
          </div>

          <label for="reset-password-confirmation">{{ __('auth_pages.reset.password_confirm') }}</label>
          <div class="pw-wrap">
            <input
            type="password"
            id="reset-password-confirmation"
            name="password_confirmation"
            placeholder="{{ __('auth_pages.reset.password_confirm_placeholder') }}"
            required
            minlength="8"
            autocomplete="new-password"
          />
            <button type="button" class="pw-toggle" aria-label="{{ __('auth_pages.common.show_password') }}" data-target="reset-password-confirmation">
              <i class="fa-regular fa-eye"></i>
            </button>
          </div>
          @error('password')
            <p class="form-message" style="color:#b91c1c;">{{ $message }}</p>
          @enderror

          <button class="btn" type="submit">{{ __('auth_pages.reset.submit') }}</button>
        </form>

        <div class="signin-inline-actions">
          <form action="{{ route('password.reset.resend') }}" method="POST" class="signin-secondary-form">
            @csrf
            <input type="hidden" name="email" value="{{ $email }}">
            <button class="btn btn-outline" type="submit">{{ __('auth_pages.reset.resend') }}</button>
          </form>

          <a href="{{ route('password.forgot.form', ['email' => $email]) }}" class="btn btn-outline">{{ __('auth_pages.reset.change_email') }}</a>
        </div>
      </div>
    </div>
  </main>
</x-loyouts.main>
