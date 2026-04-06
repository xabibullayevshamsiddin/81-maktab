@props(['title' => '81-IDUM'])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>{{ $title }}</title>
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"
      integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw=="
      crossorigin="anonymous"
      referrerpolicy="no-referrer"
    />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap"
      rel="stylesheet"
    />
    <script src="{{ app_public_asset('temp/js/theme-init.js') }}?v={{ filemtime(public_path('temp/js/theme-init.js')) }}"></script>
    <link rel="stylesheet" href="{{ app_public_asset('temp/css/style.css') }}?v={{ filemtime(public_path('temp/css/style.css')) }}" />
    <link rel="stylesheet" href="{{ app_public_asset('temp/css/extracted-public.css') }}?v={{ filemtime(public_path('temp/css/extracted-public.css')) }}" />
    @stack('page_styles')
  </head>

	    <body
        data-theme="light"
        data-site-success="{{ session('success') }}"
        data-site-error="{{ session('error') }}"
        data-site-toast-type="{{ session('toast_type') }}"
        data-site-first-error="{{ $errors->any() ? $errors->first() : '' }}"
        data-phone-pattern="{{ uz_phone_input_pattern() }}"
        data-phone-title="{{ uz_phone_input_title() }}"
      >
		    @php
		      $authUser = auth()->user();
		      $canOpenCourse = $authUser && $authUser->canOpenCourse();
		      $needsTeacherProfileLink = $authUser && $authUser->isTeacher() && ! $authUser->canOpenCourse();
		      $canAccessDashboard = $authUser && $authUser->canAccessDashboard();
		      $currentLocale = current_locale();
		      $supportedLocales = supported_locales();
	      $isExamSessionRoute = request()->routeIs('exam.session');
	      $accountMenuActive = $authUser && (
	        request()->routeIs('exam.*')
	        || request()->routeIs('profile.*')
	        || request()->routeIs('teacher.courses.*')
	        || request()->routeIs('dashboard')
	      );
	    @endphp
	    <div class="site-shell" data-locale-shell>
	    @unless($isExamSessionRoute)
	    <header class="page-header">
	      <div class="container">
	        <div class="header-main header-main--offset" id="navbar">
          <a class="logo" href="{{ route('home') }}" aria-label="{{ __('public.layout.nav.home') }}">
            <img
              src="{{ app_public_asset('temp/img/photo_2026-02-06_11-05-24-2.jpg') }}"
              alt="{{ __('public.layout.logo_alt') }}"
            />
          </a>

          <button
            class="menu-toggle"
            id="menu-toggle"
            type="button"
            aria-label="{{ __('public.layout.mobile_menu') }}"
            aria-expanded="false"
          >
            <i class="fa-solid fa-bars"></i>
          </button>

          <nav id="site-nav">
            <ul>
              <li><a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">{{ __('public.layout.nav.home') }}</a></li>
              <li><a class="nav-link {{ request()->routeIs('about') ? 'active' : '' }}" href="{{ route('about') }}">{{ __('public.layout.nav.about') }}</a></li>
              <li><a class="nav-link {{ request()->routeIs('courses') ? 'active' : '' }}" href="{{ route('courses') }}">{{ __('public.layout.nav.courses') }}</a></li>
              <li><a class="nav-link {{ request()->routeIs('post') ? 'active' : '' }}" href="{{ route('post') }}">{{ __('public.layout.nav.posts') }}</a></li>
              <li><a class="nav-link {{ request()->routeIs('calendar') ? 'active' : '' }}" href="{{ route('calendar') }}">{{ __('public.layout.nav.calendar') }}</a></li>
              <li><a class="nav-link {{ request()->routeIs('teacher*') ? 'active' : '' }}" href="{{ route('teacher') }}">{{ __('public.layout.nav.teachers') }}</a></li>
              <li class="mobile-theme-toggle-wrap">
                <button class="theme-toggle js-theme-toggle" type="button" aria-label="Tungi rejimni yoqish yoki oР В Р’В Р В РІР‚В Р В Р’В Р Р†Р вЂљРЎв„ўР В РІР‚в„ўР вЂ™Р’Вchirish" title="Tungi rejim">
                  <i class="fa-solid fa-moon theme-toggle-light-icon"></i>
                  <i class="fa-solid fa-sun theme-toggle-dark-icon"></i>
                </button>
              </li>
              @auth
	                <li class="nav-dropdown nav-dropdown--offset">
                  <details class="nav-dropdown-details js-header-dropdown">
                    <summary class="nav-link nav-dropdown-toggle {{ $accountMenuActive ? 'active' : '' }}">
                      {{ __('public.layout.account') }}
                      <i class="fa-solid fa-chevron-down"></i>
                    </summary>

                    <div class="nav-dropdown-menu">
                      <a class="nav-dropdown-item {{ request()->routeIs('exam.*') ? 'active' : '' }}" href="{{ route('exam.index') }}">
                        <i class="fa-solid fa-graduation-cap"></i>
                        {{ __('public.layout.menu.exams') }}
                      </a>
                      <a class="nav-dropdown-item {{ request()->routeIs('profile.*') ? 'active' : '' }}" href="{{ route('profile.show') }}">
                        <i class="fa-solid fa-user"></i>
                        {{ __('public.layout.menu.profile') }}
                      </a>
	                      @if($canOpenCourse)
	                        <a class="nav-dropdown-item {{ request()->routeIs('teacher.courses.*') ? 'active' : '' }}" href="{{ route('teacher.courses.create') }}">
	                          <i class="fa-solid fa-book-open"></i>
	                          {{ __('public.layout.menu.course_open') }}
	                        </a>
	                      @elseif($needsTeacherProfileLink)
	                        <span class="nav-dropdown-item nav-dropdown-item-disabled">
	                          <i class="fa-solid fa-circle-info"></i>
	                          <span>
	                            {{ __('public.layout.menu.course_open') }}
	                            <small class="nav-dropdown-item-note">Avval admin akkauntingizni ustoz kartasiga bog'lashi kerak.</small>
	                          </span>
	                        </span>
	                      @endif
                      @if($canAccessDashboard)
                        <a class="nav-dropdown-item {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                          <i class="fa-solid fa-table-columns"></i>
                          {{ __('public.layout.menu.dashboard') }}
                        </a>
                      @endif

                      <form class="nav-dropdown-form" action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="nav-dropdown-item">
                          <i class="fa-solid fa-right-from-bracket"></i>
                          {{ __('public.layout.menu.logout') }}
                        </button>
                      </form>
                    </div>
                  </details>
                </li>
              @endauth
              <li><a class="nav-link {{ request()->routeIs('contact') ? 'active' : '' }}" href="{{ route('contact') }}">{{ __('public.layout.nav.contact') }}</a></li>
            </ul>

            <div class="mobile-nav-extras">
              <div class="locale-switcher" aria-label="Language switcher">
                @foreach($supportedLocales as $localeKey => $localeLabel)
                  <a
                    href="{{ route('locale.switch', $localeKey) }}"
                    class="locale-switcher-link {{ $currentLocale === $localeKey ? 'active' : '' }}"
                    data-locale-switch
                    hreflang="{{ $localeKey }}"
                    lang="{{ $localeKey }}"
                  >
                    {{ $localeLabel }}
                  </a>
                @endforeach
              </div>

              @guest
                <div class="mobile-nav-actions">
                  <a href="{{ route('login') }}" class="btn btn-outline">{{ __('public.common.login') }}</a>
                  <a href="{{ route('register') }}" class="btn">{{ __('public.common.register') }}</a>
                </div>
              @else
                <div class="mobile-nav-user">
                  <span class="mobile-nav-user-name">{{ $authUser->name }}</span>
                  <span class="mobile-nav-user-role">{{ $authUser->role_label }}</span>
                </div>

                <div class="mobile-nav-actions mobile-nav-actions--auth">
                  <a href="{{ route('exam.index') }}" class="btn btn-outline">{{ __('public.layout.menu.exams') }}</a>
                  <a href="{{ route('profile.show') }}" class="btn btn-outline">{{ __('public.layout.menu.profile') }}</a>
                  @if($canOpenCourse)
                    <a href="{{ route('teacher.courses.create') }}" class="btn btn-outline">{{ __('public.layout.menu.course_open') }}</a>
                  @endif
                  @if($canAccessDashboard)
                    <a href="{{ route('dashboard') }}" class="btn btn-outline">{{ __('public.layout.menu.dashboard') }}</a>
                  @endif
                  <form action="{{ route('logout') }}" method="POST" class="mobile-nav-form">
                    @csrf
                    <button type="submit" class="btn">{{ __('public.layout.menu.logout') }}</button>
                  </form>
                </div>
              @endguest
            </div>
          </nav>

          <div class="login desktop-header-tools {{ auth()->guest() ? 'login--guest' : '' }}">
            <div class="locale-switcher" aria-label="Language switcher">
              @foreach($supportedLocales as $localeKey => $localeLabel)
                <a
                  href="{{ route('locale.switch', $localeKey) }}"
                  class="locale-switcher-link {{ $currentLocale === $localeKey ? 'active' : '' }}" data-locale-switch
                  hreflang="{{ $localeKey }}"
                  lang="{{ $localeKey }}"
                >
                  {{ $localeLabel }}
                </a>
              @endforeach
            </div>
            <button class="theme-toggle js-theme-toggle" type="button" aria-label="Tungi rejimni yoqish yoki oР В Р’В Р В РІР‚В Р В Р’В Р Р†Р вЂљРЎв„ўР В РІР‚в„ўР вЂ™Р’Вchirish" title="Tungi rejim">
              <i class="fa-solid fa-moon theme-toggle-light-icon"></i>
              <i class="fa-solid fa-sun theme-toggle-dark-icon"></i>
            </button>

            @auth
              <p class="header-user-name">{{ $authUser->name }}</p>
            @endauth

            @guest
              <a href="{{ route('login') }}" class="btn btn-outline">{{ __('public.common.login') }}</a>
              <a href="{{ route('register') }}" class="btn">{{ __('public.common.register') }}</a>
            @endguest
          </div>
	        </div>
	      </div>
	    </header>
	    @endunless

	    {{ $slot }}

	    @unless($isExamSessionRoute)
	    <button
	      id="scroll-top"
	      class="scroll-top"
      type="button"
      aria-label="Yuqoriga"
    >
      <i class="fa-solid fa-chevron-up"></i>
    </button>

    <div id="image-lightbox" class="image-lightbox" aria-hidden="true">
      <button type="button" class="image-lightbox-close" aria-label="Rasmni yopish">
        <i class="fa-solid fa-xmark"></i>
      </button>
      <div class="image-lightbox-stage">
        <img id="image-lightbox-img" class="image-lightbox-img" alt="" />
        <p id="image-lightbox-caption" class="image-lightbox-caption" hidden></p>
      </div>
    </div>

    <footer class="footer">
      <div class="footer-container container">
        <div class="footer-com">
          <img
            src="{{ app_public_asset('temp/img/photo_2026-02-06_11-05-24-2.jpg') }}"
            alt="{{ __('public.layout.logo_alt') }}"
            class="img2"
          />
          <h3>{{ __('public.layout.school_name') }}</h3>
          <p>{{ __('public.layout.footer.description') }}</p>
        </div>

        <div class="footer-col">
          <h4>{{ __('public.layout.footer.quick_links') }}</h4>
          <ul>
            <li><a href="{{ route('home') }}">{{ __('public.layout.nav.home') }}</a></li>
            <li><a href="{{ route('about') }}">{{ __('public.layout.nav.about') }}</a></li>
            <li><a href="{{ route('courses') }}">{{ __('public.layout.nav.courses') }}</a></li>
            <li><a href="{{ route('post') }}">{{ __('public.layout.nav.posts') }}</a></li>
            <li><a href="{{ route('calendar') }}">{{ __('public.layout.nav.calendar') }}</a></li>
            <li><a href="{{ route('teacher') }}">{{ __('public.layout.nav.teachers') }}</a></li>
            @auth
              <li><a href="{{ route('exam.index') }}">{{ __('public.layout.menu.exams') }}</a></li>
            @else
              <li><a href="{{ route('login') }}">{{ __('public.layout.footer.exams_guest') }}</a></li>
            @endauth
            <li><a href="{{ route('contact') }}">{{ __('public.layout.nav.contact') }}</a></li>
          </ul>
        </div>

        <div class="footer-cop">
          <h4>{{ __('public.layout.footer.contact') }}</h4>
          <a
            href="https://maps.app.goo.gl/erCMfrDY42DCogHL6"
            target="_blank"
            rel="noopener"
          >
            <i class="fa-solid fa-location-dot"></i> {{ __('public.layout.footer.map_label') }}
          </a>
          <p>
            <i class="fa-solid fa-phone"></i>
            <a href="tel:+998711234567">+998 71 123 45 67</a>
          </p>
          <p>
            <i class="fa-solid fa-envelope"></i>
            <a
              href="{{ gmail_compose_url('info@school81.uz', '81-IDUM murojaati') }}"
              target="_blank"
              rel="noopener"
            >
              info@school81.uz
            </a>
          </p>
        </div>
      </div>

	      <div class="footer-bottom">
	        &copy; <span id="year"></span> {{ __('public.layout.footer.copyright') }}
	      </div>
	    </footer>
	    @endunless
	    </div>

    <div id="global-modal-root"></div>

    <div id="toast-container" class="toast-container" aria-live="polite" aria-atomic="true"></div>

    <script src="{{ app_public_asset('temp/js/script.js') }}?v={{ filemtime(public_path('temp/js/script.js')) }}"></script>
    <script src="{{ app_public_asset('temp/js/public-layout.js') }}?v={{ filemtime(public_path('temp/js/public-layout.js')) }}"></script>
    @stack('page_scripts')
  </body>
</html>
