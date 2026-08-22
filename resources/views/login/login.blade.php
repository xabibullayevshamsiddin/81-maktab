<x-layouts.main :title="__('auth_pages.login.page_title')">
    @push('page_styles')
        <style>
            .phone-input-wrap {
                display: flex;
                align-items: stretch;
                border: 1.5px solid var(--border, #e2e8f0);
                border-radius: 12px;
                overflow: hidden;
                background: var(--surface, #fff);
                transition: border-color 0.2s ease, box-shadow 0.2s ease;
                min-height: 52px;
                height: auto;
                width: 100%;
                box-sizing: border-box;
            }
            .phone-input-wrap:focus-within {
                border-color: var(--primary, #3b82f6);
                box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.12);
            }
            .phone-prefix {
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 0 10px 0 12px;
                margin: 0;
                font-size: 14px;
                font-weight: 800;
                color: var(--primary, #3b82f6);
                background: rgba(59, 130, 246, 0.08);
                user-select: none;
                white-space: nowrap;
                letter-spacing: 0.3px;
                min-height: 52px;
                box-sizing: border-box;
            }
            :root[data-theme='dark'] .phone-prefix {
                background: rgba(99, 102, 241, 0.15);
                color: #a5b4fc;
            }
            .phone-input-wrap .phone-input {
                flex: 1;
                border: none !important;
                outline: none !important;
                background: transparent !important;
                border-radius: 0 !important;
                padding: 14px !important;
                box-shadow: none !important;
                color: var(--text, #1e293b);
                min-width: 0;
                height: 100%;
                font-size: 15px;
                min-height: 52px;
                box-sizing: border-box;
            }
            .phone-input-wrap .phone-input::placeholder {
                color: var(--muted, #94a3b8);
                opacity: 0.6;
            }
            .phone-input-wrap .phone-prefix {
                border-radius: 0 !important;
                border: none !important;
                box-shadow: none !important;
            }
            :root[data-theme='dark'] .phone-input-wrap {
                background: rgba(30, 41, 59, 0.6);
                border-color: rgba(99, 102, 241, 0.25);
            }
            :root[data-theme='dark'] .phone-input-wrap:focus-within {
                border-color: #6366f1;
                box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.2);
            }
            :root[data-theme='dark'] .phone-input {
                color: #f1f5f9;
            }
            .signin-forgot-wrap {
                margin: 10px 0 16px;
                text-align: right;
            }

            .signin-forgot-link {
                display: inline-flex;
                align-items: center;
                gap: 6px;
                font-size: 13px;
                font-weight: 700;
                color: #1d4ed8;
                text-decoration: none;
                padding: 6px 10px;
                border-radius: 10px;
                background: rgba(37, 99, 235, 0.08);
                transition: all 0.2s ease;
            }

            .signin-forgot-link:hover {
                color: #1e40af;
                background: rgba(37, 99, 235, 0.16);
            }

            :root[data-theme='dark'] .signin-forgot-link {
                color: #93c5fd;
                background: rgba(59, 130, 246, 0.18);
            }

            :root[data-theme='dark'] .signin-forgot-link:hover {
                color: #bfdbfe;
                background: rgba(59, 130, 246, 0.3);
            }

            .signin-alert {
                display: flex;
                align-items: flex-start;
                gap: 10px;
                margin: 0 0 16px;
                padding: 12px 14px;
                border-radius: 12px;
                border: 1px solid rgba(220, 38, 38, 0.18);
                background: rgba(254, 242, 242, 0.95);
                color: #991b1b;
                font-size: 14px;
                line-height: 1.5;
            }

            :root[data-theme='dark'] .signin-alert {
                border-color: rgba(248, 113, 113, 0.35);
                background: rgba(127, 29, 29, 0.28);
                color: #fecaca;
            }

        </style>
    @endpush

    <section class="signin-hero">
        <div class="container">
            <h1>{{ __('auth_pages.login.hero_title') }}</h1>
            <p>{{ __('auth_pages.login.hero_text') }}</p>
        </div>
    </section>

    <main class="signin-section">
        <div class="container">
            <div class="signin-card">
                <div class="signin-card-icon">
                    <i class="fa-solid fa-user-graduate"></i>
                </div>
                <h2>{{ __('auth_pages.login.card_title') }}</h2>
                <p class="signin-subtitle">{{ __('auth_pages.login.subtitle') }}</p>

                @if (session('error'))
                    <div class="signin-alert" role="alert">
                        <i class="fa-solid fa-circle-exclamation" style="margin-top:2px;"></i>
                        <span>{{ session('error') }}</span>
                    </div>
                @endif

                <form action="{{ route('authenticate') }}" method="POST" class="signin-form" id="signin-form-server">
                    @csrf
                    <label for="signin-phone">{{ __('auth_pages.login.phone') }}</label>
                    <div class="phone-input-wrap">
                      <span class="phone-prefix">+998</span>
                      <input type="tel" id="signin-phone" name="phone" placeholder="90 123 45 67" required autocomplete="tel" inputmode="tel" class="phone-input" oninput="this.value=this.value.replace(/^\+?998/,'').replace(/[^\d\s\-]/g,'').trim()" />
                    </div>
                    <script>
                      (function() {
                        var phoneInput = document.getElementById('signin-phone');
                        if (!phoneInput) return;
                        var form = phoneInput.closest('form');
                        if (form) {
                          form.addEventListener('submit', function() {
                            var v = phoneInput.value.replace(/[^\d]/g, '');
                            if (v.length === 9 && !v.startsWith('998')) {
                              phoneInput.value = '+998' + v;
                            }
                          });
                        }
                      })();
                    </script>
                    <label for="signin-password">{{ __('auth_pages.login.password') }}</label>
                    <div class="pw-wrap">
                        <input type="password" id="signin-password" name="password" placeholder="{{ __('auth_pages.login.password_placeholder') }}" required autocomplete="current-password" />
                        <button type="button" class="pw-toggle" aria-label="{{ __('auth_pages.common.show_password') }}" data-target="signin-password">
                            <i class="fa-regular fa-eye"></i>
                        </button>
                    </div>
                    @error('phone')
                        <p class="form-message" style="color:#b91c1c;">{{ $message }}</p>
                    @enderror
                    <div class="signin-forgot-wrap">
                        <a href="{{ route('password.forgot.form') }}" class="signin-forgot-link">
                            <i class="fa-solid fa-key"></i>
                            Parolni unutdingizmi?
                        </a>
                    </div>
                    <button class="btn" type="submit">{{ __('auth_pages.login.submit') }}</button>
                    <p id="signin-message" class="form-message" aria-live="polite"></p>
                </form>

                <p class="signin-register">
                    {{ __('auth_pages.login.register_text') }} <a href="{{ route('register') }}">{{ __('auth_pages.login.register_link') }}</a>
                </p>
            </div>
        </div>
    </main>

</x-layouts.main>
