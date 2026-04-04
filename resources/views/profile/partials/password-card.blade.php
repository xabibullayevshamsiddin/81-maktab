<div class="signin-card profile-card" data-profile-section="password" id="profile-password-card">
  <div class="profile-card-head">
    <span class="profile-card-kicker">3-qadam</span>
    <h2>Parolni almashtirish</h2>
    <p class="signin-subtitle">
      Eski parolni kiriting va yangi parolni oddiyroq tarzda yangilang.
    </p>
  </div>

  <div class="profile-security-list">
    <div class="profile-security-item">
      <i class="fa-solid fa-shield-halved"></i>
      <span>Joriy parol tekshiriladi</span>
    </div>
    <div class="profile-security-item">
      <i class="fa-solid fa-key"></i>
      <span>Kamida 6 ta belgi bo'lishi kerak</span>
    </div>
    <div class="profile-security-item">
      <i class="fa-solid fa-lock"></i>
      <span>O'zingizga esda qoladigan parol tanlang</span>
    </div>
  </div>

  <p class="profile-security-note">
    <i class="fa-solid fa-circle-info"></i>
    Avval joriy parolni tasdiqlaysiz. Tasdiqlangandan keyingina yangi parol maydonlari ochiladi.
  </p>

  @if(! ($passwordChangeUnlocked ?? false))
    <form action="{{ route('profile.password.confirm') }}" method="POST" class="signin-form comment-form profile-form-stack" data-profile-async="password">
      @csrf

      <div class="profile-field">
        <label for="profile-current-password">Joriy parol</label>
        <span class="profile-field-hint">To'g'ri kiritilgach, yangi parol maydonlari ochiladi.</span>
        <div class="pw-wrap">
          <input
            type="password"
            id="profile-current-password"
            name="current_password"
            required
            autocomplete="current-password"
            placeholder="Joriy parolingiz"
          />
          <button
            type="button"
            class="pw-toggle"
            aria-label="Parolni ko'rsatish"
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
          Joriy parolni tasdiqlash
        </button>
      </div>
    </form>
  @else
    <p class="profile-password-confirmed">
      <i class="fa-solid fa-circle-check"></i>
      Joriy parol tasdiqlandi. Endi yangi parolni kiritishingiz mumkin.
    </p>

    <form action="{{ route('profile.password.update') }}" method="POST" class="signin-form comment-form profile-form-stack" data-profile-async="password">
      @csrf

      <div class="profile-form-grid">
        <div class="profile-field">
          <label for="profile-new-password">Yangi parol</label>
          <span class="profile-field-hint">Kamida 6 ta belgi yozsangiz bo'ladi.</span>
          <div class="pw-wrap">
            <input
              type="password"
              id="profile-new-password"
              name="password"
              required
              minlength="6"
              autocomplete="new-password"
              placeholder="Yangi parol"
            />
            <button
              type="button"
              class="pw-toggle"
              aria-label="Parolni ko'rsatish"
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
          <label for="profile-new-password-confirmation">Yangi parolni tasdiqlang</label>
          <span class="profile-field-hint">Yangi parolni aynan shu yerda qayta kiriting.</span>
          <div class="pw-wrap">
            <input
              type="password"
              id="profile-new-password-confirmation"
              name="password_confirmation"
              required
              minlength="6"
              autocomplete="new-password"
              placeholder="Parolni qayta kiriting"
            />
            <button
              type="button"
              class="pw-toggle"
              aria-label="Parolni ko'rsatish"
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
          Parolni yangilash
        </button>
      </div>
    </form>
  @endif
</div>
