<x-layouts.main :title="__('auth_pages.forgot.page_title')">
  @push('page_styles')
    <style>
      .phone-input-wrap {
        display: flex;
        align-items: stretch;
        border: 1.5px solid var(--border, #e2e8f0);
        border-radius: 12px;
        overflow: hidden;
        background: var(--surface, #fff);
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
        min-height: 52px;
        height: auto;
        width: 100%;
        box-sizing: border-box;
      }
      .phone-input-wrap:focus-within {
        border-color: var(--primary, #3b82f6);
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.12);
      }
      .phone-prefix {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0 10px 0 12px;
        margin: 0;
        font-size: 14px;
        font-weight: 800;
        color: var(--primary, #3b82f6);
        background: rgba(59, 130, 246, 0.08);
        user-select: none;
        white-space: nowrap;
        letter-spacing: 0.3px;
        min-height: 52px;
        box-sizing: border-box;
      }
      :root[data-theme='dark'] .phone-prefix {
        background: rgba(99, 102, 241, 0.15);
        color: #a5b4fc;
      }
      .phone-input-wrap .phone-input {
        flex: 1;
        border: none !important;
        outline: none !important;
        background: transparent !important;
        border-radius: 0 !important;
        padding: 14px !important;
        box-shadow: none !important;
        color: var(--text, #1e293b);
        min-width: 0;
        height: 100%;
        font-size: 15px;
        min-height: 52px;
        box-sizing: border-box;
      }
      .phone-input-wrap .phone-input::placeholder {
        color: var(--muted, #94a3b8);
        opacity: 0.6;
      }
      .phone-input-wrap .phone-prefix {
        border-radius: 0 !important;
        border: none !important;
        box-shadow: none !important;
      }
      :root[data-theme='dark'] .phone-input-wrap {
        background: rgba(30, 41, 59, 0.6);
        border-color: rgba(99, 102, 241, 0.25);
      }
      :root[data-theme='dark'] .phone-input-wrap:focus-within {
        border-color: #6366f1;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.2);
      }
      :root[data-theme='dark'] .phone-input-wrap .phone-input {
        color: #f1f5f9;
      }
    </style>
  @endpush
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
          <label for="forgot-phone">{{ __('auth_pages.forgot.phone') }}</label>
          <div class="phone-input-wrap">
            <span class="phone-prefix">+998</span>
            <input
              type="tel"
              id="forgot-phone"
              name="phone"
              value="{{ old('phone', $phone ?? '') }}"
              placeholder="90 123 45 67"
              required
              autocomplete="tel"
              inputmode="tel"
              class="phone-input"
              oninput="this.value=this.value.replace(/^\+?998/,'').replace(/[^\d\s\-]/g,'').trim()"
            />
          </div>
          <script>
            (function() {
              var phoneInput = document.getElementById('forgot-phone');
              if (!phoneInput) return;
              var form = phoneInput.closest('form');
              if (form) {
                form.addEventListener('submit', function() {
                  var v = phoneInput.value.replace(/[^\d]/g, '');
                  if (v.length === 9 && !v.startsWith('998')) {
                    phoneInput.value = '+998' + v;
                  }
                });
              }
            })();
          </script>
          @error('phone')
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
