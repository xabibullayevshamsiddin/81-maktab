<x-loyouts.main title="81-IDUM | Yangi parol">
  <section class="signin-hero">
    <div class="container">
      <h1>Yangi parol o'rnatish</h1>
      <p>{{ $email }} manziliga yuborilgan kod orqali parolingizni yangilang.</p>
    </div>
  </section>

  <main class="signin-section">
    <div class="container">
      <div class="signin-card">
        <div class="signin-card-icon">
          <i class="fa-solid fa-shield-keyhole"></i>
        </div>
        <h2>Parolni yangilash</h2>
        <p class="signin-subtitle">Kod 10 daqiqa amal qiladi. Yangi parol oddiy bo'lishi mumkin.</p>

        <div class="signin-helper-box">
          <strong>Tanlangan email</strong>
          <span class="signin-email-pill">{{ $email }}</span>
        </div>

        <form action="{{ route('password.reset') }}" method="POST" class="signin-form">
          @csrf
          <input type="hidden" name="email" value="{{ old('email', $email) }}">

          <label for="reset-code">6 xonali kod</label>
          <input
            type="text"
            id="reset-code"
            name="code"
            value="{{ old('code') }}"
            placeholder="123456"
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

          <label for="reset-password">Yangi parol</label>
          <div class="pw-wrap">
            <input
            type="password"
            id="reset-password"
            name="password"
            placeholder="Yangi parol"
            required
            minlength="6"
            autocomplete="new-password"
          />
            <button type="button" class="pw-toggle" aria-label="Parolni ko'rsatish" data-target="reset-password">
              <i class="fa-regular fa-eye"></i>
            </button>
          </div>

          <label for="reset-password-confirmation">Parolni tasdiqlang</label>
          <div class="pw-wrap">
            <input
            type="password"
            id="reset-password-confirmation"
            name="password_confirmation"
            placeholder="Parolni qayta kiriting"
            required
            minlength="6"
            autocomplete="new-password"
          />
            <button type="button" class="pw-toggle" aria-label="Parolni ko'rsatish" data-target="reset-password-confirmation">
              <i class="fa-regular fa-eye"></i>
            </button>
          </div>
          @error('password')
            <p class="form-message" style="color:#b91c1c;">{{ $message }}</p>
          @enderror

          <button class="btn" type="submit">Parolni yangilash</button>
        </form>

        <div class="signin-inline-actions">
          <form action="{{ route('password.reset.resend') }}" method="POST" class="signin-secondary-form">
            @csrf
            <input type="hidden" name="email" value="{{ $email }}">
            <button class="btn btn-outline" type="submit">Kodni qayta yuborish</button>
          </form>

          <a href="{{ route('password.forgot.form', ['email' => $email]) }}" class="btn btn-outline">Emailni almashtirish</a>
        </div>
      </div>
    </div>
  </main>
</x-loyouts.main>
