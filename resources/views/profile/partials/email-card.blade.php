<div class="signin-card profile-card" data-profile-section="email" id="profile-email-card">
  <div class="profile-card-head">
    <span class="profile-card-kicker">{{ __('profile.steps.email') }}</span>
    <h2>{{ __('profile.email_card.title') }}</h2>
    <p class="signin-subtitle">{{ __('profile.email_card.subtitle') }}</p>
  </div>

  <p class="profile-alert">
    <i class="fa-solid fa-envelope-circle-check"></i>
    <span class="profile-alert-copy">
      <span class="profile-alert-label">{{ __('profile.email_card.current_email') }}</span>
      <strong class="profile-break-text" data-profile-user-email>{{ $user->email }}</strong>
    </span>
  </p>

  <form action="{{ route('profile.email.update') }}" method="POST" class="signin-form comment-form profile-form-stack" data-profile-async="email">
    @csrf
    @method('PUT')

    <div class="profile-field">
      <label for="new-email">{{ __('profile.email_card.new_email') }}</label>
      <span class="profile-field-hint">{{ __('profile.email_card.new_email_hint_simple') }}</span>
      <input type="email" id="new-email" name="email" value="{{ old('email', $user->email) }}" required autocomplete="email" />
      @error('email')
        <p class="form-message profile-form-error">{{ $message }}</p>
      @enderror
    </div>

    <div class="profile-form-actions">
      <button class="btn" type="submit">
        <i class="fa-solid fa-floppy-disk"></i>
        {{ __('profile.email_card.save') }}
      </button>
    </div>
  </form>
</div>
