<x-loyouts.main :title="__('auth_pages.verify.page_title')">
  <section class="signin-hero">
    <div class="container">
      <h1>{{ __('auth_pages.verify.hero_title') }}</h1>
      <p>{{ __('auth_pages.verify.hero_text', ['email' => $email]) }}</p>
    </div>
  </section>

  <main class="signin-section">
    <div class="container">
      <div class="signin-card">
        <div class="signin-card-icon">
          <i class="fa-solid fa-shield-halved"></i>
        </div>
        <h2>{{ __('auth_pages.verify.card_title') }}</h2>
        <p class="signin-subtitle">{{ __('auth_pages.verify.subtitle') }}</p>

        <form
          action="{{ $mode === 'login' ? route('login.verify') : route('register.verify') }}"
          method="POST"
          class="signin-form"
        >
          @csrf
          <label for="otp-code">{{ __('auth_pages.verify.code') }}</label>
          <input
            type="text"
            id="otp-code"
            name="code"
            placeholder="{{ __('auth_pages.verify.code_placeholder') }}"
            maxlength="6"
            required
            autocomplete="one-time-code"
          />
          @error('code')
            <p class="form-message" style="color:#b91c1c;">{{ $message }}</p>
          @enderror
          <button class="btn" type="submit">{{ __('auth_pages.verify.submit') }}</button>
        </form>

        <form action="{{ $mode === 'login' ? route('login.verify.resend') : route('register.verify.resend') }}" method="POST" style="margin-top:10px;">
          @csrf
          <button class="btn btn-outline" type="submit">{{ __('auth_pages.verify.resend') }}</button>
        </form>
      </div>
    </div>
  </main>
</x-loyouts.main>
