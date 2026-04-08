<x-loyouts.main :title="__('auth_pages.register.page_title')">
    <section class="register-hero">
      <div class="container">
        <h1>{{ __('auth_pages.register.hero_title') }}</h1>
        <p>{{ __('auth_pages.register.hero_text') }}</p>
      </div>
    </section>

    <main class="register-section">
      <div class="container">
        <div class="register-card">
          <span class="register-card-badge">{{ __('auth_pages.register.badge') }}</span>
          <div class="register-card-icon">
            <i class="fa-solid fa-user-plus"></i>
          </div>
          <h2>{{ __('auth_pages.register.card_title') }}</h2>
          <p class="register-subtitle">{{ __('auth_pages.register.subtitle') }}</p>
          <div class="register-chip-list" aria-hidden="true">
            <span class="register-chip"><i class="fa-solid fa-id-card"></i> {{ __('auth_pages.register.chip_1') }}</span>
            <span class="register-chip"><i class="fa-solid fa-graduation-cap"></i> {{ __('auth_pages.register.chip_2') }}</span>
            <span class="register-chip"><i class="fa-solid fa-shield-halved"></i> {{ __('auth_pages.register.chip_3') }}</span>
          </div>

          <form action="{{ route('register.store') }}" method="POST" class="register-form" id="register-form-server">
            @csrf
            @if ($errors->any())
              <div class="register-alert">
                <i class="fa-solid fa-circle-exclamation"></i>
                <span>{{ $errors->first() }}</span>
              </div>
            @endif
            <div class="register-field-grid">
              <div class="register-field">
                <label for="reg-first-name">Ism</label>
                <input
                  type="text"
                  id="reg-first-name"
                  name="first_name"
                  value="{{ old('first_name') }}"
                  placeholder="Ismingiz"
                  required
                  autocomplete="given-name"
                />
                @error('first_name')
                  <p class="form-message" style="color:#b91c1c;">{{ $message }}</p>
                @enderror
              </div>
              <div class="register-field">
                <label for="reg-last-name">Familiya</label>
                <input
                  type="text"
                  id="reg-last-name"
                  name="last_name"
                  value="{{ old('last_name') }}"
                  placeholder="Familiyangiz"
                  required
                  autocomplete="family-name"
                />
                @error('last_name')
                  <p class="form-message" style="color:#b91c1c;">{{ $message }}</p>
                @enderror
              </div>
            </div>
            <div class="register-field">
              <label for="reg-email">{{ __('auth_pages.register.email') }}</label>
              <input
                type="email"
                id="reg-email"
                name="email"
                value="{{ old('email') }}"
                placeholder="{{ __('auth_pages.register.email_placeholder') }}"
                required
                autocomplete="email"
              />
              @error('email')
                <p class="form-message" style="color:#b91c1c;">{{ $message }}</p>
              @enderror
            </div>
            <div class="register-field-grid">
              <div class="register-field">
                <label for="reg-phone">{{ __('auth_pages.register.phone') }}</label>
                <input
                  type="tel"
                  id="reg-phone"
                  name="phone"
                  value="{{ old('phone') }}"
                  placeholder="{{ __('auth_pages.register.phone_placeholder') }}"
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
              <div class="register-field" id="reg-grade-field">
                <label for="reg-grade">{{ __('auth_pages.register.grade') }}</label>
                <div class="register-select-wrap">
                  <select id="reg-grade" name="grade" {{ old('is_parent') ? '' : 'required' }}>
                    <option value="">{{ __('auth_pages.register.grade_placeholder') }}</option>
                    @foreach (school_grade_grouped_options() as $groupLabel => $options)
                      @php
                        $localizedGroupLabel = app()->getLocale() === 'en'
                          ? str_replace('-sinf', __('auth_pages.register.grade_group_suffix'), $groupLabel)
                          : $groupLabel;
                      @endphp
                      <optgroup label="{{ $localizedGroupLabel }}">
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
            <div class="register-field" style="margin-top:4px;">
              <label class="register-parent-toggle" style="display:inline-flex;align-items:center;gap:10px;cursor:pointer;font-size:14px;font-weight:600;color:var(--text);">
                <input type="checkbox" id="reg-is-parent" name="is_parent" value="1" {{ old('is_parent') ? 'checked' : '' }} style="width:18px;height:18px;accent-color:var(--primary-2);">
                <span>Men ota-onaman</span>
              </label>
              <p class="register-field-note" style="margin-top:6px;font-size:12px;color:var(--muted);">Ota-onalar sinf tanlamaslik mumkin. Imtihon topshirish cheklanadi.</p>
            </div>
            <script>
              (function() {
                var cb = document.getElementById('reg-is-parent');
                var gradeField = document.getElementById('reg-grade-field');
                var gradeSelect = document.getElementById('reg-grade');
                if (!cb || !gradeField || !gradeSelect) return;
                function toggle() {
                  if (cb.checked) {
                    gradeField.style.display = 'none';
                    gradeSelect.removeAttribute('required');
                    gradeSelect.value = '';
                  } else {
                    gradeField.style.display = '';
                    gradeSelect.setAttribute('required', '');
                  }
                }
                cb.addEventListener('change', toggle);
                toggle();
              })();
            </script>
            <p class="register-field-note">{{ __('auth_pages.register.grade_note') }}</p>
            <div class="register-field">
              <label for="reg-password">{{ __('auth_pages.register.password') }}</label>
              <div class="pw-wrap">
                <input
                  type="password"
                  id="reg-password"
                  name="password"
                  placeholder="{{ __('auth_pages.register.password_placeholder') }}"
                  required
                  autocomplete="new-password"
                  minlength="8"
                />
                <button
                  type="button"
                  class="pw-toggle"
                  aria-label="{{ __('auth_pages.common.show_password') }}"
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
              <label for="reg-password-confirm">{{ __('auth_pages.register.password_confirm') }}</label>
              <div class="pw-wrap">
                <input
                  type="password"
                  id="reg-password-confirm"
                  name="password_confirmation"
                  placeholder="{{ __('auth_pages.register.password_confirm_placeholder') }}"
                  required
                  autocomplete="new-password"
                  minlength="8"
                />
                <button
                  type="button"
                  class="pw-toggle"
                  aria-label="{{ __('auth_pages.common.show_password') }}"
                  data-target="reg-password-confirm"
                >
                  <i class="fa-regular fa-eye"></i>
                </button>
              </div>
            </div>
            <button class="btn" type="submit">{{ __('auth_pages.register.submit') }}</button>
            <p class="register-submit-note">{{ __('auth_pages.register.submit_note') }}</p>
            <p
              id="register-message"
              class="form-message register-global-message"
              aria-live="polite"
            ></p>
          </form>

          <div class="register-divider">
            <span>{{ __('auth_pages.common.or') }}</span>
          </div>

          <p class="register-signin">
            {{ __('auth_pages.register.login_text') }} <a href="{{ route('login') }}">{{ __('auth_pages.register.login_link') }}</a>
          </p>
        </div>
      </div>
    </main>

</x-loyouts.main>
