<x-layouts.main :title="__('auth_pages.register.page_title')">
    @push('page_styles')
      <style>
      </style>
    @endpush

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

          @if (session('error'))
            <div class="register-alert" role="alert">
              <i class="fa-solid fa-circle-exclamation"></i>
              <span>{{ session('error') }}</span>
            </div>
          @endif

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
                <label>{{ __('auth_pages.register.grade') }}</label>
                {{-- Hidden input for form submission --}}
                <input type="hidden" id="reg-grade" name="grade" value="{{ old('grade', '') }}" {{ old('is_parent') ? '' : 'required' }}>
                {{-- Custom dropdown --}}
                <div class="reg-custom-select" id="reg-grade-custom" role="combobox" aria-haspopup="listbox" aria-expanded="false" tabindex="0">
                  <div class="reg-cs-trigger" id="reg-cs-trigger">
                    <i class="fa-solid fa-graduation-cap reg-cs-icon"></i>
                    <span class="reg-cs-value" id="reg-cs-value">{{ __('auth_pages.register.grade_placeholder') }}</span>
                    <i class="fa-solid fa-chevron-down reg-cs-arrow"></i>
                  </div>
                  <div class="reg-cs-dropdown" id="reg-cs-dropdown" role="listbox">
                    @foreach (school_grade_grouped_options() as $groupLabel => $options)
                      @php
                        $localizedGroupLabel = app()->getLocale() === 'en'
                          ? str_replace('-sinf', __('auth_pages.register.grade_group_suffix'), $groupLabel)
                          : $groupLabel;
                      @endphp
                      <div class="reg-cs-group">
                        <div class="reg-cs-group-label">{{ $localizedGroupLabel }}</div>
                        @foreach ($options as $value => $label)
                          <div class="reg-cs-option {{ old('grade') === $value ? 'is-selected' : '' }}"
                               role="option"
                               data-value="{{ $value }}"
                               aria-selected="{{ old('grade') === $value ? 'true' : 'false' }}">
                            {{ $label }}
                          </div>
                        @endforeach
                      </div>
                    @endforeach
                  </div>
                </div>
                @error('grade')
                  <p class="form-message" style="color:#b91c1c;">{{ $message }}</p>
                @enderror
              </div>
            </div>
            <div class="register-parent-wrap">
              <label class="register-parent-toggle" for="reg-is-parent">
                <input type="checkbox" id="reg-is-parent" name="is_parent" value="1" {{ old('is_parent') ? 'checked' : '' }}>
                <span class="register-parent-switch"></span>
                <span class="register-parent-label">
                  <i class="fa-solid fa-user-shield"></i>
                  Men ota-onaman
                </span>
              </label>
              <p class="register-field-note">Agar siz ota-ona bo'lsangiz, sinf tanlash shart emas.</p>
            </div>
            <script>
              (function() {
                var cb        = document.getElementById('reg-is-parent');
                var gradeField = document.getElementById('reg-grade-field');
                var gradeHidden = document.getElementById('reg-grade');
                var cs        = document.getElementById('reg-grade-custom');
                var trigger   = document.getElementById('reg-cs-trigger');
                var valueEl   = document.getElementById('reg-cs-value');
                var dropdown  = document.getElementById('reg-cs-dropdown');
                var allOpts   = cs ? cs.querySelectorAll('.reg-cs-option') : [];
                var isOpen    = false;

                /* ---- Init preselected ---- */
                var pre = gradeHidden ? gradeHidden.value : '';
                if (pre) {
                  allOpts.forEach(function(o) {
                    if (o.dataset.value === pre) {
                      o.classList.add('is-selected');
                      valueEl.textContent = o.textContent.trim();
                      valueEl.classList.remove('is-placeholder');
                    }
                  });
                } else {
                  if (valueEl) valueEl.classList.add('is-placeholder');
                }

                function selectOption(el) {
                  allOpts.forEach(function(o) { o.classList.remove('is-selected'); o.setAttribute('aria-selected','false'); });
                  el.classList.add('is-selected');
                  el.setAttribute('aria-selected','true');
                  valueEl.textContent = el.textContent.trim();
                  valueEl.classList.remove('is-placeholder');
                  if (gradeHidden) gradeHidden.value = el.dataset.value;
                  closeDD();
                }
                function openDD()  { if (!cs) return; isOpen=true;  cs.setAttribute('aria-expanded','true'); }
                function closeDD() { if (!cs) return; isOpen=false; cs.setAttribute('aria-expanded','false'); }
                function toggleDD() { isOpen ? closeDD() : openDD(); }

                if (trigger) trigger.addEventListener('click', function(e){ e.stopPropagation(); toggleDD(); });
                if (cs) cs.addEventListener('keydown', function(e) {
                  if (e.key==='Enter'||e.key===' '){ e.preventDefault(); toggleDD(); }
                  if (e.key==='Escape'){ e.preventDefault(); closeDD(); }
                  if ((e.key==='ArrowDown'||e.key==='ArrowUp') && isOpen) {
                    e.preventDefault();
                    var opts = Array.from(allOpts);
                    var focused = dropdown.querySelector('.is-focused');
                    var idx = focused ? opts.indexOf(focused) : -1;
                    if (focused) focused.classList.remove('is-focused');
                    idx = e.key==='ArrowDown' ? Math.min(idx+1,opts.length-1) : Math.max(idx-1,0);
                    opts[idx].classList.add('is-focused');
                    opts[idx].scrollIntoView({block:'nearest'});
                  }
                  if (e.key==='Enter' && isOpen) {
                    var focused = dropdown.querySelector('.is-focused');
                    if (focused) selectOption(focused);
                  }
                });
                allOpts.forEach(function(opt) {
                  opt.addEventListener('click', function(){ selectOption(opt); });
                  opt.addEventListener('mouseenter', function(){
                    allOpts.forEach(function(o){ o.classList.remove('is-focused'); });
                    opt.classList.add('is-focused');
                  });
                });
                document.addEventListener('click', function(e){ if(cs && !cs.contains(e.target)) closeDD(); });

                /* ---- Parent toggle ---- */
                function toggle() {
                  if (!cb) return;
                  if (cb.checked) {
                    gradeField.style.display = 'none';
                    if (gradeHidden) { gradeHidden.removeAttribute('required'); gradeHidden.value = ''; }
                    if (valueEl) { valueEl.textContent = '{{ __("auth_pages.register.grade_placeholder") }}'; valueEl.classList.add('is-placeholder'); }
                    closeDD();
                  } else {
                    gradeField.style.display = '';
                    if (gradeHidden) gradeHidden.setAttribute('required', '');
                  }
                }
                if (cb) { cb.addEventListener('change', toggle); toggle(); }
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

          <p class="register-signin">
            {{ __('auth_pages.register.login_text') }} <a href="{{ route('login') }}">{{ __('auth_pages.register.login_link') }}</a>
          </p>
        </div>
      </div>
    </main>

</x-loyouts.main>
