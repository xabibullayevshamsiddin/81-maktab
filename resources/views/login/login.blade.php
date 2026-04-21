<x-loyouts.main :title="__('auth_pages.login.page_title')">
    @push('page_styles')
        <style>
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

            .signin-social-btn {
                width: 100%;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 10px;
                border: 1px solid #dbe2ea;
                border-radius: 12px;
                padding: 11px 14px;
                text-decoration: none;
                font-weight: 700;
                color: #111827;
                background: #fff;
                transition: all 0.2s ease;
            }

            .signin-social-btn:hover {
                border-color: #c1cedd;
                background: #f8fafc;
                color: #111827;
            }

            .signin-social-btn .fa-google {
                color: #ea4335;
            }

            :root[data-theme='dark'] .signin-social-btn {
                background: rgba(255, 255, 255, 0.06);
                border-color: rgba(255, 255, 255, 0.18);
                color: #e5e7eb;
            }

            :root[data-theme='dark'] .signin-social-btn:hover {
                background: rgba(255, 255, 255, 0.1);
                border-color: rgba(255, 255, 255, 0.3);
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

                <form action="{{ route('authenticate') }}" method="POST" class="signin-form" id="signin-form-server">
                    @csrf
                    <label for="signin-email">{{ __('auth_pages.login.email') }}</label>
                    <input type="email" id="signin-email" name="email" placeholder="{{ __('auth_pages.login.email_placeholder') }}" required autocomplete="email" />
                    <label for="signin-password">{{ __('auth_pages.login.password') }}</label>
                    <div class="pw-wrap">
                        <input type="password" id="signin-password" name="password" placeholder="{{ __('auth_pages.login.password_placeholder') }}" required autocomplete="current-password" />
                        <button type="button" class="pw-toggle" aria-label="{{ __('auth_pages.common.show_password') }}" data-target="signin-password">
                            <i class="fa-regular fa-eye"></i>
                        </button>
                    </div>
                    @error('email')
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

                <a href="{{ route('auth.google.redirect') }}" class="signin-social-btn">
                    <i class="fa-brands fa-google"></i>
                    Google orqali kirish
                </a>

                <div class="signin-divider">
                    <span>{{ __('auth_pages.common.or') }}</span>
                </div>

                <p class="signin-register">
                    {{ __('auth_pages.login.register_text') }} <a href="{{ route('register') }}">{{ __('auth_pages.login.register_link') }}</a>
                </p>
            </div>
        </div>
    </main>

</x-loyouts.main>
