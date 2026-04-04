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
          <span class="register-card-badge">81-IDUM a'zoligi</span>
          <div class="register-card-icon">
            <i class="fa-solid fa-user-plus"></i>
          </div>
          <h2>Hisob yarating</h2>
          <p class="register-subtitle">Quyidagi maydonlarni to'ldiring</p>
          <div class="register-chip-list" aria-hidden="true">
            <span class="register-chip"><i class="fa-solid fa-id-card"></i> Asosiy ma'lumotlar</span>
            <span class="register-chip"><i class="fa-solid fa-graduation-cap"></i> Sinf tanlanadi</span>
            <span class="register-chip"><i class="fa-solid fa-shield-halved"></i> Xavfsiz kirish</span>
          </div>

          <form action="{{ route('register.store') }}" method="POST" class="register-form" id="register-form-server">
            @csrf
            @if ($errors->any())
              <div class="register-alert">
                <i class="fa-solid fa-circle-exclamation"></i>
                <span>{{ $errors->first() }}</span>
              </div>
            @endif
            <div class="register-field">
              <label for="reg-name">Ism va familiya</label>
              <input
                type="text"
                id="reg-name"
                name="name"
                value="{{ old('name') }}"
                placeholder="masalan: Sardor Yuldashev"
                required
                autocomplete="name"
              />
              @error('name')
                <p class="form-message" style="color:#b91c1c;">{{ $message }}</p>
              @enderror
            </div>
            <div class="register-field">
              <label for="reg-email">Email</label>
              <input
                type="email"
                id="reg-email"
                name="email"
                value="{{ old('email') }}"
                placeholder="masalan: sardor@email.uz"
                required
                autocomplete="email"
              />
              @error('email')
                <p class="form-message" style="color:#b91c1c;">{{ $message }}</p>
              @enderror
            </div>
            <div class="register-field-grid">
              <div class="register-field">
                <label for="reg-phone">Telefon</label>
                <input
                  type="tel"
                  id="reg-phone"
                  name="phone"
                  value="{{ old('phone') }}"
                  placeholder="masalan: +998 90 123 45 67"
                  required
                  autocomplete="tel"
                  inputmode="tel"
                  maxlength="17"
                  pattern="{{ uz_phone_input_pattern() }}"
                  title="{{ uz_phone_input_title() }}"
                />
                @error('phone')
                  <p class="form-message" style="color:#b91c1c;">{{ $message }}</p>
                @enderror
              </div>
              <div class="register-field">
                <label for="reg-grade">Sinf</label>
                <div class="register-select-wrap">
                  <select id="reg-grade" name="grade" required>
                    <option value="">Sinfni tanlang</option>
                    @foreach (school_grade_grouped_options() as $groupLabel => $options)
                      <optgroup label="{{ $groupLabel }}">
                        @foreach ($options as $value => $label)
                          <option value="{{ $value }}" {{ old('grade') === $value ? 'selected' : '' }}>
                            {{ $label }}
                          </option>
                        @endforeach
                      </optgroup>
                    @endforeach
                  </select>
                </div>
                @error('grade')
                  <p class="form-message" style="color:#b91c1c;">{{ $message }}</p>
                @enderror
              </div>
            </div>
            <p class="register-field-note">Faqat maktabdagi mavjud sinf variantlari ko'rsatiladi.</p>
            <div class="register-field">
              <label for="reg-password">Parol</label>
              <div class="pw-wrap">
                <input
                  type="password"
                  id="reg-password"
                  name="password"
                  placeholder="Kamida 6 ta belgi"
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
              @error('password')
                <p class="form-message" style="color:#b91c1c;">{{ $message }}</p>
              @enderror
            </div>
            <div class="register-field">
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
            </div>
            <button class="btn" type="submit">Ro'yxatdan o'tish</button>
            <p class="register-submit-note">Davom etish orqali sayt qoidalari va foydalanuvchi tartibiga rozilik bildirasiz.</p>
            <p
              id="register-message"
              class="form-message register-global-message"
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
