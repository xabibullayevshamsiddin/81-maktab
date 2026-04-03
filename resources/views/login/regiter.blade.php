<x-loyouts.main title="81-IDUM | Ro'yxatdan o'tish">

    <section class="register-hero">
      <div class="container">
        <h1>Ro'yxatdan o'tish</h1>
        <p>Yangi hisob yarating va 81-IDUM jamoasiga qo'shiling</p>
      </div>
    </section>

    <main class="register-section">
      <div class="container">
        <div class="register-card">
          <div class="register-card-icon">
            <i class="fa-solid fa-user-plus"></i>
          </div>
          <h2>Hisob yarating</h2>
          <p class="register-subtitle">Quyidagi maydonlarni to'ldiring</p>

          <form action="{{ route('register.store') }}" method="POST" class="register-form" id="register-form-server">
            @csrf
            <label for="reg-name">Ism va familiya</label>
            <input
              type="text"
              id="reg-name"
              name="name"
              placeholder="masalan: Sardor Yuldashev"
              required
              autocomplete="name"
            />
            <label for="reg-email">Email</label>
            <input
              type="email"
              id="reg-email"
              name="email"
              placeholder="masalan: sardor@email.uz"
              required
              autocomplete="email"
            />
            <label for="reg-phone">Telefon</label>
            <input
              type="tel"
              id="reg-phone"
              name="phone"
              placeholder="masalan: +998 90 123 45 67"
              required
              autocomplete="tel"
            />
            <label for="reg-password">Parol</label>
            <div class="pw-wrap">
              <input
                type="password"
                id="reg-password"
                name="password"
                placeholder="Kamida 8 ta belgi"
                required
                autocomplete="new-password"
                minlength="6"
              />
              <button
                type="button"
                class="pw-toggle"
                aria-label="Parolni ko'rsatish"
                data-target="reg-password"
              >
                <i class="fa-regular fa-eye"></i>
              </button>
            </div>
            <label for="reg-password-confirm">Parolni tasdiqlang</label>
            <div class="pw-wrap">
              <input
                type="password"
                id="reg-password-confirm"
                name="password_confirmation"
                placeholder="Parolni qayta kiriting"
                required
                autocomplete="new-password"
                minlength="6"
              />
              <button
                type="button"
                class="pw-toggle"
                aria-label="Parolni ko'rsatish"
                data-target="reg-password-confirm"
              >
                <i class="fa-regular fa-eye"></i>
              </button>
            </div>
            @if ($errors->any())
              <p class="form-message" style="color:#b91c1c;">{{ $errors->first() }}</p>
            @endif
            <button class="btn" type="submit">Ro'yxatdan o'tish</button>
            <p
              id="register-message"
              class="form-message"
              aria-live="polite"
            ></p>
          </form>

          <div class="register-divider">
            <span>yoki</span>
          </div>

          <p class="register-signin">
            Hisobingiz bormi? <a href="{{ route('login') }}">Tizimga kirish</a>
          </p>
        </div>
      </div>
    </main>

</x-loyouts.main>
