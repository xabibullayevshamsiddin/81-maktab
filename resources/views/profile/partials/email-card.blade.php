<div class="signin-card profile-card" data-profile-section="email" id="profile-email-card">
  <div class="profile-card-head">
    <span class="profile-card-kicker">2-qadam</span>
    <h2>Emailni almashtirish</h2>
    <p class="signin-subtitle">
      Jarayon oddiy: yangi emailni yozasiz, kod olasiz, keyin kodni kiritib tasdiqlaysiz.
    </p>
  </div>

  <div class="profile-step-strip">
    <span class="profile-step-chip {{ $pendingEmail === '' ? 'is-active' : '' }}">
      <i class="fa-solid fa-at"></i>
      1. Yangi email
    </span>
    <span class="profile-step-chip {{ $pendingEmail !== '' ? 'is-active' : '' }}">
      <i class="fa-solid fa-key"></i>
      2. Kodni kiriting
    </span>
    <span class="profile-step-chip">
      <i class="fa-solid fa-circle-check"></i>
      3. Tasdiqlansin
    </span>
  </div>

  <p class="profile-alert">
    <i class="fa-solid fa-envelope-circle-check"></i>
    <span class="profile-alert-copy">
      <span class="profile-alert-label">Hozirgi email</span>
      <strong class="profile-break-text">{{ $user->email }}</strong>
    </span>
  </p>

  @if($pendingEmail !== '')
    <p class="profile-pending-email">
      <i class="fa-solid fa-hourglass-half"></i>
      <span class="profile-alert-copy">
        <span class="profile-alert-label">Tasdiqlanishi kutilmoqda</span>
        <strong class="profile-break-text">{{ $pendingEmail }}</strong>
      </span>
    </p>

    <form action="{{ route('profile.email.verify') }}" method="POST" class="signin-form comment-form profile-form-stack" data-profile-async="email">
      @csrf

      <div class="profile-field">
        <label for="email-code">6 xonali kod</label>
        <span class="profile-field-hint">Emailga kelgan kodni shu yerga kiriting.</span>
        <input type="text" id="email-code" name="code" inputmode="numeric" maxlength="6" placeholder="123456" required autocomplete="one-time-code" />
        @error('code')
          <p class="form-message profile-form-error">{{ $message }}</p>
        @enderror
      </div>

      <div class="profile-form-actions">
        <button class="btn" type="submit">
          <i class="fa-solid fa-check"></i>
          Emailni tasdiqlash
        </button>
      </div>
    </form>

    <div class="profile-email-actions">
      <form action="{{ route('profile.email.resend') }}" method="POST" data-profile-async="email">
        @csrf
        <button class="btn btn-outline" type="submit">Kodni qayta yuborish</button>
      </form>
      <form action="{{ route('profile.email.cancel') }}" method="POST" data-profile-async="email">
        @csrf
        <button class="btn btn-outline" type="submit">Bekor qilish</button>
      </form>
    </div>
  @else
    <form action="{{ route('profile.email.request') }}" method="POST" class="signin-form comment-form profile-form-stack" data-profile-async="email">
      @csrf

      <div class="profile-field">
        <label for="new-email">Yangi email</label>
        <span class="profile-field-hint">Tasdiqlash kodi aynan shu email manzilga yuboriladi.</span>
        <input type="email" id="new-email" name="email" value="{{ old('email') }}" required autocomplete="email" />
        @error('email')
          <p class="form-message profile-form-error">{{ $message }}</p>
        @enderror
      </div>

      <div class="profile-form-actions">
        <button class="btn" type="submit">
          <i class="fa-solid fa-paper-plane"></i>
          Kod yuborish
        </button>
      </div>
    </form>
  @endif
</div>
