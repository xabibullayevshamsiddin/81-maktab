<div class="signin-card profile-card" data-profile-section="password" id="profile-password-card">
  <div class="profile-card-head">
    <span class="profile-card-kicker">{{ __('profile.steps.password') }}</span>
    <h2>{{ __('profile.password_card.title') }}</h2>
    <p class="signin-subtitle">{{ __('profile.password_card.subtitle') }}</p>
  </div>

  <div class="profile-security-list">
    <div class="profile-security-item">
      <i class="fa-solid fa-shield-halved"></i>
      <span>{{ __('profile.password_card.item_current') }}</span>
    </div>
    <div class="profile-security-item">
      <i class="fa-solid fa-key"></i>
      <span>{{ __('profile.password_card.item_min') }}</span>
    </div>
    <div class="profile-security-item">
      <i class="fa-solid fa-lock"></i>
      <span>{{ __('profile.password_card.item_easy') }}</span>
    </div>
  </div>

  <p class="profile-security-note">
    <i class="fa-solid fa-circle-info"></i>
    {{ __('profile.password_card.note') }}
  </p>

  @if(! ($passwordChangeUnlocked ?? false))
    <form action="{{ route('profile.password.confirm') }}" method="POST" class="signin-form comment-form profile-form-stack" data-profile-async="password">
      @csrf

      <div class="profile-field">
        <label for="profile-current-password">{{ __('profile.password_card.current_label') }}</label>
        <span class="profile-field-hint">{{ __('profile.password_card.current_hint') }}</span>
        <div class="pw-wrap">
          <input
            type="password"
            id="profile-current-password"
            name="current_password"
            required
            autocomplete="current-password"
            placeholder="{{ __('profile.password_card.current_placeholder') }}"
          />
          <button
            type="button"
            class="pw-toggle"
            aria-label="{{ __('profile.password_card.show_password') }}"
            data-target="profile-current-password"
          >
            <i class="fa-regular fa-eye"></i>
          </button>
        </div>
        @error('current_password')
          <p class="form-message profile-form-error">{{ $message }}</p>
        @enderror
      </div>

      <div class="profile-form-actions">
        <button class="btn" type="submit">
          <i class="fa-solid fa-shield-halved"></i>
          {{ __('profile.password_card.confirm_current') }}
        </button>
      </div>
    </form>
  @else
    <p class="profile-password-confirmed">
      <i class="fa-solid fa-circle-check"></i>
      {{ __('profile.password_card.confirmed') }}
    </p>

    <form action="{{ route('profile.password.update') }}" method="POST" class="signin-form comment-form profile-form-stack" data-profile-async="password">
      @csrf

      <div class="profile-form-grid">
        <div class="profile-field">
          <label for="profile-new-password">{{ __('profile.password_card.new_label') }}</label>
          <span class="profile-field-hint">{{ __('profile.password_card.new_hint') }}</span>
          <div class="pw-wrap">
            <input
              type="password"
              id="profile-new-password"
              name="password"
              required
              minlength="6"
              autocomplete="new-password"
              placeholder="{{ __('profile.password_card.new_placeholder') }}"
            />
            <button
              type="button"
              class="pw-toggle"
              aria-label="{{ __('profile.password_card.show_password') }}"
              data-target="profile-new-password"
            >
              <i class="fa-regular fa-eye"></i>
            </button>
          </div>
          @error('password')
            <p class="form-message profile-form-error">{{ $message }}</p>
          @enderror
        </div>

        <div class="profile-field">
          <label for="profile-new-password-confirmation">{{ __('profile.password_card.confirm_label') }}</label>
          <span class="profile-field-hint">{{ __('profile.password_card.confirm_hint') }}</span>
          <div class="pw-wrap">
            <input
              type="password"
              id="profile-new-password-confirmation"
              name="password_confirmation"
              required
              minlength="6"
              autocomplete="new-password"
              placeholder="{{ __('profile.password_card.confirm_placeholder') }}"
            />
            <button
              type="button"
              class="pw-toggle"
              aria-label="{{ __('profile.password_card.show_password') }}"
              data-target="profile-new-password-confirmation"
            >
              <i class="fa-regular fa-eye"></i>
            </button>
          </div>
        </div>
      </div>

      <div class="profile-form-actions">
        <button class="btn" type="submit">
          <i class="fa-solid fa-lock"></i>
          {{ __('profile.password_card.update') }}
        </button>
      </div>
    </form>
  @endif
</div>
