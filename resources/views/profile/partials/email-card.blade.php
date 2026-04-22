<div class="signin-card profile-card" data-profile-section="email" id="profile-email-card">
  <div class="profile-card-head">
    <span class="profile-card-kicker">{{ __('profile.steps.email') }}</span>
    <h2>{{ __('profile.email_card.title') }}</h2>
    <p class="signin-subtitle">{{ __('profile.email_card.subtitle') }}</p>
  </div>

  <div class="profile-step-strip">
    <span class="profile-step-chip {{ $pendingEmail === '' ? 'is-active' : '' }}">
      <i class="fa-solid fa-at"></i>
      {{ __('profile.email_card.step_new') }}
    </span>
    <span class="profile-step-chip {{ $pendingEmail !== '' ? 'is-active' : '' }}">
      <i class="fa-solid fa-key"></i>
      {{ __('profile.email_card.step_code') }}
    </span>
    <span class="profile-step-chip">
      <i class="fa-solid fa-circle-check"></i>
      {{ __('profile.email_card.step_done') }}
    </span>
  </div>

  <p class="profile-alert">
    <i class="fa-solid fa-envelope-circle-check"></i>
    <span class="profile-alert-copy">
      <span class="profile-alert-label">{{ __('profile.email_card.current_email') }}</span>
      <strong class="profile-break-text">{{ $user->email }}</strong>
    </span>
  </p>

  @if($pendingEmail !== '')
    <p class="profile-pending-email">
      <i class="fa-solid fa-hourglass-half"></i>
      <span class="profile-alert-copy">
        <span class="profile-alert-label">{{ __('profile.email_card.pending_email') }}</span>
        <strong class="profile-break-text">{{ $pendingEmail }}</strong>
      </span>
    </p>

    <form action="{{ route('profile.email.verify') }}" method="POST" class="signin-form comment-form profile-form-stack" data-profile-async="email">
      @csrf

      <div class="profile-field">
        <label for="email-code">{{ __('profile.email_card.code_label') }}</label>
        <span class="profile-field-hint">{{ __('profile.email_card.code_hint') }}</span>
        <input type="text" id="email-code" name="code" inputmode="numeric" maxlength="6" placeholder="123456" required autocomplete="one-time-code" />
        @error('code')
          <p class="form-message profile-form-error">{{ $message }}</p>
        @enderror
      </div>

      <div class="profile-form-actions">
        <button class="btn" type="submit">
          <i class="fa-solid fa-check"></i>
          {{ __('profile.email_card.confirm') }}
        </button>
      </div>
    </form>

    <div class="profile-email-actions">
      <form action="{{ route('profile.email.resend') }}" method="POST" data-profile-async="email">
        @csrf
        <button class="btn btn-outline" type="submit">{{ __('profile.email_card.resend') }}</button>
      </form>
      <form action="{{ route('profile.email.cancel') }}" method="POST" data-profile-async="email">
        @csrf
        <button class="btn btn-outline" type="submit">{{ __('profile.email_card.cancel') }}</button>
      </form>
    </div>
  @else
    <form action="{{ route('profile.email.request') }}" method="POST" class="signin-form comment-form profile-form-stack" data-profile-async="email">
      @csrf

      <div class="profile-field">
        <label for="new-email">{{ __('profile.email_card.new_email') }}</label>
        <span class="profile-field-hint">{{ __('profile.email_card.new_email_hint') }}</span>
        <input type="email" id="new-email" name="email" value="{{ old('email') }}" required autocomplete="email" />
        @error('email')
          <p class="form-message profile-form-error">{{ $message }}</p>
        @enderror
      </div>

      <div class="profile-form-actions">
        <button class="btn" type="submit">
          <i class="fa-solid fa-paper-plane"></i>
          {{ __('profile.email_card.send_code') }}
        </button>
      </div>
    </form>
  @endif
</div>
