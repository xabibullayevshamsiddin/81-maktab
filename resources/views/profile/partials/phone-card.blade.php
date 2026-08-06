<div class="signin-card profile-card" data-profile-section="phone" id="profile-phone-card">
  <div class="profile-card-head">
    <span class="profile-card-kicker">{{ __('profile.steps.phone') }}</span>
    <h2>{{ __('profile.phone_card.title') }}</h2>
    <p class="signin-subtitle">{{ __('profile.phone_card.subtitle') }}</p>
  </div>

  <div class="profile-step-strip">
    <span class="profile-step-chip {{ $pendingPhone === '' ? 'is-active' : '' }}">
      <i class="fa-solid fa-phone"></i>
      {{ __('profile.phone_card.step_new') }}
    </span>
    <span class="profile-step-chip {{ $pendingPhone !== '' ? 'is-active' : '' }}">
      <i class="fa-brands fa-telegram"></i>
      Telegram
    </span>
    <span class="profile-step-chip">
      <i class="fa-solid fa-circle-check"></i>
      {{ __('profile.phone_card.step_done') }}
    </span>
  </div>

  <p class="profile-alert">
    <i class="fa-solid fa-phone"></i>
    <span class="profile-alert-copy">
      <span class="profile-alert-label">{{ __('profile.phone_card.current_phone') }}</span>
      <strong class="profile-break-text">{{ $user->phone ?: __('profile.phone_missing') }}</strong>
    </span>
  </p>

  @if($pendingPhone !== '')
    <p class="profile-pending-email">
      <i class="fa-brands fa-telegram"></i>
      <span class="profile-alert-copy">
        <span class="profile-alert-label">Yangi telefon (Telegram'ga yuborildi):</span>
        <strong class="profile-break-text">{{ $pendingPhone }}</strong>
      </span>
    </p>

    <div class="profile-email-telegram-notice" style="text-align:center; padding:1rem; background:rgba(42,171,238,0.08); border-radius:12px; border:1px solid rgba(42,171,238,0.2); margin-bottom:1rem;">
      <i class="fa-brands fa-telegram" style="font-size:1.5rem; color:#229ED9; margin-bottom:0.5rem; display:block;"></i>
      <p style="font-size:0.9rem; color:var(--text); margin:0;">Telegram'da tasdiqlash xabarini tekshiring</p>
      <p style="font-size:0.8rem; color:var(--muted); margin:0.25rem 0 0;">✅ yoki ❌ tugmasini bosing</p>
    </div>

    <div class="profile-form-actions">
      <form action="{{ route('profile.phone.verify') }}" method="POST" style="display:inline;">
        @csrf
        <button class="btn" type="submit">
          <i class="fa-solid fa-rotate"></i> Tekshirish
        </button>
      </form>
      <form action="{{ route('profile.phone.resend') }}" method="POST" style="display:inline;" data-profile-async="phone">
        @csrf
        <button class="btn btn-outline" type="submit">
          <i class="fa-solid fa-paper-plane"></i> Qayta yuborish
        </button>
      </form>
      <form action="{{ route('profile.phone.cancel') }}" method="POST" style="display:inline;" data-profile-async="phone">
        @csrf
        <button class="btn btn-outline" type="submit">
          <i class="fa-solid fa-xmark"></i> Bekor qilish
        </button>
      </form>
    </div>
  @else
    <form action="{{ route('profile.phone.request') }}" method="POST" class="signin-form comment-form profile-form-stack" data-profile-async="phone">
      @csrf

      <div class="profile-field">
        <label for="new-phone">{{ __('profile.phone_card.new_phone') }}</label>
        <span class="profile-field-hint">{{ __('profile.phone_card.new_phone_hint') }}</span>
        <input type="tel" id="new-phone" name="phone" value="{{ old('phone') }}" required autocomplete="tel" inputmode="tel" placeholder="+998..." />
        @error('phone')
          <p class="form-message profile-form-error">{{ $message }}</p>
        @enderror
      </div>

      <div class="profile-form-actions">
        <button class="btn" type="submit">
          <i class="fa-brands fa-telegram"></i>
          Telegram'ga yuborish
        </button>
      </div>
    </form>
  @endif
</div>
