<x-loyouts.main :title="__('auth_pages.login.page_title')">

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
                    <button class="btn" type="submit">{{ __('auth_pages.login.submit') }}</button>
                    <p id="signin-message" class="form-message" aria-live="polite"></p>
                </form>

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
