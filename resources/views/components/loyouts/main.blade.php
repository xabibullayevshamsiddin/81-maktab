@props(['title' => '81-IDUM'])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />
    <title>{{ $title }}</title>
    {!! \Artesaos\SEOTools\Facades\SEOMeta::generate() !!}
    {!! \Artesaos\SEOTools\Facades\OpenGraph::generate() !!}
    @stack('seo')
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
    @unless(request()->routeIs('exam.session'))
    <link rel="stylesheet" href="{{ app_public_asset('temp/css/site-boot-loader.css') }}?v={{ filemtime(public_path('temp/css/site-boot-loader.css')) }}" />
    @endunless
    <link rel="stylesheet" href="{{ app_public_asset('temp/css/extracted-public.css') }}?v={{ filemtime(public_path('temp/css/extracted-public.css')) }}" />
    <link rel="stylesheet" href="{{ app_public_asset('temp/css/mobile-public.css') }}?v={{ filemtime(public_path('temp/css/mobile-public.css')) }}" />
    <link rel="stylesheet" href="{{ app_public_asset('temp/css/confirm-modal.css') }}?v={{ filemtime(public_path('temp/css/confirm-modal.css')) }}" />
    <link rel="stylesheet" href="{{ app_public_asset('temp/css/calendar-public.css') }}?v={{ filemtime(public_path('temp/css/calendar-public.css')) }}" />
    @if(turnstile_enabled())
    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
    @endif
    <link rel="icon" type="image/png" sizes="32x32" href="{{ app_public_asset('temp/img/favicon-32.png') }}?v={{ filemtime(public_path('temp/img/favicon-32.png')) }}" />
    <link rel="icon" type="image/png" sizes="16x16" href="{{ app_public_asset('temp/img/favicon-16.png') }}?v={{ filemtime(public_path('temp/img/favicon-16.png')) }}" />
    <link rel="apple-touch-icon" sizes="180x180" href="{{ app_public_asset('temp/img/favicon-180.png') }}?v={{ filemtime(public_path('temp/img/favicon-180.png')) }}" />
    @stack('page_styles')
  </head>

      <body
        @class([
          'site-boot-loading' => ! request()->routeIs('exam.session'),
          'page-home' => request()->routeIs('home'),
          'page-inner' => ! request()->routeIs('home')
        ])
        data-theme="light"
        data-site-success="{{ session('success') }}"
        data-site-error="{{ session('error') }}"
        data-site-toast-type="{{ session('toast_type') }}"
        data-site-first-error="{{ $errors->any() ? $errors->first() : '' }}"
        data-phone-pattern="{{ uz_phone_input_pattern() }}"
        data-phone-title="{{ uz_phone_input_title() }}"
      >
    @unless(request()->routeIs('exam.session'))
    <div id="site-boot-loader" class="site-boot-loader" aria-busy="true" aria-live="polite" role="status">
      <div class="site-boot-loader__backdrop" aria-hidden="true"></div>
      <div class="site-boot-loader__content">
        <div class="site-boot-loader__orbit" aria-hidden="true">
          <div class="site-boot-loader__ring"></div>
          <div class="site-boot-loader__ring-inner"></div>
          <div class="site-boot-loader__glow"></div>
          <div class="site-boot-loader__brand">
            <span class="site-boot-loader__num">81</span>
            <span class="site-boot-loader__name">IDUM</span>
          </div>
        </div>
        <p class="site-boot-loader__hint">{{ __('public.layout.boot_loading') }}</p>
        <div class="site-boot-loader__bar" aria-hidden="true">
          <span class="site-boot-loader__bar-fill"></span>
        </div>
      </div>
    </div>
    @endunless
		    @php
		      $authUser = auth()->user();
		      $teacherWithProfile = $authUser && $authUser->isTeacher() && $authUser->hasLinkedActiveTeacherProfile();
		      $teacherAtCourseLimit = $teacherWithProfile && $authUser->hasReachedCourseOpenLimit();
		      $canOpenCourseForm = $authUser && (
		        $authUser->isAdmin()
		        || ($teacherWithProfile && ! $teacherAtCourseLimit && $authUser->hasCourseOpenApproval())
		      );
		      $teacherNeedsCourseOpenRequest = $teacherWithProfile && ! $teacherAtCourseLimit && ! $authUser->hasCourseOpenApproval() && ! $authUser->hasPendingCourseOpenRequest();
		      $teacherCourseOpenPending = $teacherWithProfile && ! $teacherAtCourseLimit && $authUser->hasPendingCourseOpenRequest();
		      $canCreateCourse = $canOpenCourseForm;
		      $needsTeacherProfileLink = $authUser && $authUser->isTeacher() && ! $authUser->hasLinkedActiveTeacherProfile();
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
      <div class="prime-progress-container" aria-hidden="true">
        <div class="prime-progress-bar" id="prime-scroll-bar"></div>
      </div>
	    @unless($isExamSessionRoute)
	    @php
	      $announcementActive = \App\Models\SiteSetting::get('announcement_active', '0') === '1';
	      $announcementText = \App\Models\SiteSetting::get('announcement_text', '');
	      $announcementType = \App\Models\SiteSetting::get('announcement_type', 'info');
	    @endphp
	    @if($announcementActive && filled($announcementText))
	      <div class="global-announcement global-announcement--{{ $announcementType }}" id="global-announcement" role="alert">
	        <div class="global-announcement-inner">
	          <span class="global-announcement-icon">
	            @switch($announcementType)
	              @case('success') <i class="fa-solid fa-circle-check"></i> @break
	              @case('warning') <i class="fa-solid fa-triangle-exclamation"></i> @break
	              @case('danger') <i class="fa-solid fa-circle-exclamation"></i> @break
	              @default <i class="fa-solid fa-bullhorn"></i>
	            @endswitch
	          </span>
	          <p class="global-announcement-text">{{ $announcementText }}</p>
	          <button type="button" class="global-announcement-close" aria-label="Yopish" onclick="this.closest('.global-announcement').remove()">
	            <i class="fa-solid fa-xmark"></i>
	          </button>
	        </div>
	      </div>
	    @endif
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
                <button class="theme-toggle js-theme-toggle" type="button" aria-label="Tungi rejimni yoqish yoki o'chirish" title="Tungi rejim">
                  <i class="fa-solid fa-moon theme-toggle-light-icon"></i>
                  <i class="fa-solid fa-sun theme-toggle-dark-icon"></i>
                </button>
              </li>
              @auth
                <li class="mobile-ai-toggle-wrap">
                  <button
                    class="nav-link ai-header-toggle"
                    id="ai-header-toggle"
                    type="button"
                    aria-label="AI Yordamchi"
                    title="AI Yordamchi"
                  >
                    <i class="fa-solid fa-magic-wand-sparkles"></i>
                    <span>AI Bot</span>
                  </button>
                </li>
              @endauth
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
                      @if($canCreateCourse)
                        <a class="nav-dropdown-item {{ request()->routeIs('teacher.courses.*') ? 'active' : '' }}" href="{{ route('teacher.courses.create') }}">
                          <i class="fa-solid fa-book-open"></i>
                          {{ __('public.layout.menu.course_open') }}
                        </a>
                      @elseif($teacherCourseOpenPending)
                        <span class="nav-dropdown-item nav-dropdown-item-disabled">
                          <i class="fa-solid fa-hourglass-half"></i>
                          <span>{{ __('public.layout.menu.course_open') }} <small class="nav-dropdown-item-note">Admin ruxsatini kuting (profil).</small></span>
                        </span>
                      @elseif($teacherNeedsCourseOpenRequest)
                        <a class="nav-dropdown-item" href="{{ route('profile.show') }}#course-open-request">
                          <i class="fa-solid fa-paper-plane"></i>
                          Kurs ochish — ruxsat so'rang
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
                      <a class="nav-dropdown-item {{ request()->routeIs('contact') ? 'active' : '' }}" href="{{ route('contact') }}">
                        <i class="fa-solid fa-address-book"></i>
                        {{ __('public.layout.nav.contact') }}
                      </a>

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
              @guest
                <li><a class="nav-link {{ request()->routeIs('contact') ? 'active' : '' }}" href="{{ route('contact') }}">{{ __('public.layout.nav.contact') }}</a></li>
              @endguest
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
                <span class="locale-switcher-slider"></span>
              </div>

              @guest
                <div class="mobile-nav-actions">
                  <a href="{{ route('login') }}" class="btn btn-outline">{{ __('public.common.login') }}</a>
                  <a href="{{ route('register') }}" class="btn">{{ __('public.common.register') }}</a>
                </div>
              @else
                <div class="mobile-nav-user">
                  <span class="mobile-nav-user-name">{{ $authUser->first_name ?: $authUser->name }}</span>
                  <span class="mobile-nav-user-role">{{ $authUser->role_label }}</span>
                </div>

	                <div class="mobile-nav-actions mobile-nav-actions--auth">
	                  <a href="{{ route('exam.index') }}" class="btn btn-outline">{{ __('public.layout.menu.exams') }}</a>
	                  <a href="{{ route('profile.show') }}" class="btn btn-outline">{{ __('public.layout.menu.profile') }}</a>
	                  @if($canCreateCourse)
	                    <a href="{{ route('teacher.courses.create') }}" class="btn btn-outline">{{ __('public.layout.menu.course_open') }}</a>
	                  @elseif($teacherNeedsCourseOpenRequest)
	                    <a href="{{ route('profile.show') }}#course-open-request" class="btn btn-outline">Kurs — ruxsat so'rang</a>
	                  @elseif($teacherCourseOpenPending)
	                    <span class="btn btn-outline" style="opacity:.75;pointer-events:none;">Kurs — kutilmoqda</span>
	                  @endif
	                  @if($canAccessDashboard)
	                    <a href="{{ route('dashboard') }}" class="btn btn-outline">{{ __('public.layout.menu.dashboard') }}</a>
	                  @endif
	                  <form action="{{ route('logout') }}" method="POST" class="mobile-nav-form">
	                    @csrf
	                    <button type="submit" class="btn">{{ __('public.layout.menu.logout') }}</button>
	                  </form>
	                </div>
	                @if($needsTeacherProfileLink)
	                  <p class="mobile-nav-note">Ustoz bo'lsangiz, avval admin teacher profilingizni bog'lashi kerak.</p>
	                @endif
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
              <span class="locale-switcher-slider"></span>
            </div>
            <button class="theme-toggle nav-search-trigger" type="button" data-global-search-open aria-label="Search" title="Search" style="text-decoration: none; color: inherit;">
              <i class="fa-solid fa-magnifying-glass"></i>
            </button>
            <button class="theme-toggle js-theme-toggle" type="button" aria-label="Tungi rejimni yoqish yoki o'chirish" title="Tungi rejim">
              <i class="fa-solid fa-moon theme-toggle-light-icon"></i>
              <i class="fa-solid fa-sun theme-toggle-dark-icon"></i>
            </button>

            @auth
              <p class="header-user-name">{{ $authUser->first_name ?: $authUser->name }}</p>
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
    <div id="image-lightbox" class="image-lightbox" aria-hidden="true">
      <button type="button" class="image-lightbox-close" aria-label="Rasmni yopish">
        <i class="fa-solid fa-xmark"></i>
      </button>
      <div class="image-lightbox-stage">
        <img id="image-lightbox-img" class="image-lightbox-img" alt="" />
        <p id="image-lightbox-caption" class="image-lightbox-caption" hidden></p>
      </div>
    </div>

    <footer class="footer prime-reveal">
      <div class="footer-container container prime-stagger">
        <!-- Column 1: Branding -->
        <div class="footer-column footer-brand">
          <a href="{{ route('home') }}" class="footer-logo">
            <img src="{{ app_public_asset('temp/img/photo_2026-02-06_11-05-24-2.jpg') }}" alt="{{ __('public.layout.logo_alt') }}" />
            <span>{{ __('public.layout.school_name') }}</span>
          </a>
          <p class="footer-desc">{{ __('public.layout.footer.description') }}</p>
          <div class="footer-socials">
            @php
              $tg = \App\Models\SiteSetting::get('social_telegram', '#');
              $ig = \App\Models\SiteSetting::get('social_instagram', '#');
              $fb = \App\Models\SiteSetting::get('social_facebook', '#');
            @endphp
            <a href="{{ $tg }}" {!! $tg !== '#' ? 'target="_blank" rel="noopener"' : '' !!} class="social-link" title="Telegram"><i class="fa-brands fa-telegram"></i></a>
            <a href="{{ $ig }}" {!! $ig !== '#' ? 'target="_blank" rel="noopener"' : '' !!} class="social-link" title="Instagram"><i class="fa-brands fa-instagram"></i></a>
            <a href="{{ $fb }}" {!! $fb !== '#' ? 'target="_blank" rel="noopener"' : '' !!} class="social-link" title="Facebook"><i class="fa-brands fa-facebook-f"></i></a>
          </div>
        </div>

        <!-- Column 2: Explore -->
        <div class="footer-column">
          <h4 class="footer-title">{{ __('public.layout.footer.quick_links') }}</h4>
          <ul class="footer-links">
            <li><a href="{{ route('home') }}">{{ __('public.layout.nav.home') }}</a></li>
            <li><a href="{{ route('about') }}">{{ __('public.layout.nav.about') }}</a></li>
            <li><a href="{{ route('courses') }}">{{ __('public.layout.nav.courses') }}</a></li>
            <li><a href="{{ route('post') }}">{{ __('public.layout.nav.posts') }}</a></li>
          </ul>
        </div>

        <!-- Column 3: Resources -->
        <div class="footer-column">
          <h4 class="footer-title">Resurslar</h4>
          <ul class="footer-links">
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

        <!-- Column 4: Contact -->
        <div class="footer-column">
          <h4 class="footer-title">{{ __('public.layout.footer.contact') }}</h4>
          <ul class="footer-contact-list">
            <li>
              <i class="fa-solid fa-location-dot"></i>
              <span>Yashnobod tumani, Toshkent, O'zbekiston</span>
            </li>
            <li>
              <i class="fa-solid fa-phone"></i>
              <a href="tel:+998711234567">+998 71 123 45 67</a>
            </li>
            <li>
              <i class="fa-solid fa-envelope"></i>
              <a href="mailto:info@school81.uz">info@school81.uz</a>
            </li>
          </ul>
          <div class="footer-map-action">
            <a href="https://maps.app.goo.gl/erCMfrDY42DCogHL6" target="_blank" rel="noopener" class="btn btn-sm btn-outline-footer btn-prime">
              <i class="fa-solid fa-map-location-dot"></i> Xaritada ko'rish
            </a>
          </div>
        </div>
      </div>

      <div class="footer-bottom">
        <div class="container footer-bottom-inner">
          <p>&copy; <span id="year"></span> {{ __('public.layout.footer.copyright') }}</p>
          <div class="footer-bottom-links">
            <a href="#">Maxfiylik siyosati</a>
            <a href="#">Foydalanish shartlari</a>
          </div>
        </div>
      </div>
    </footer>
	    @endunless
	    </div>

    @auth
      @unless($isExamSessionRoute)
      @php
        $globalChatEnabled = \App\Models\SiteSetting::get('global_chat_enabled', '1') === '1';
        $globalChatDisabledMsg = trim((string) \App\Models\SiteSetting::get('global_chat_disabled_message', '')) ?: 'Global chat vaqtincha o‘chirilgan. Keyinroq urinib ko‘ring.';
      @endphp
      <div id="chat-widget" class="chat-widget"
        data-chat-messages-url="{{ request()->getBaseUrl() }}/chat/messages"
        data-chat-send-url="{{ request()->getBaseUrl() }}/chat/send"
        data-chat-delete-url="{{ request()->getBaseUrl() }}/chat"
        data-chat-block-url="{{ request()->getBaseUrl() }}/chat/block"
        data-chat-user-preview-base="{{ request()->getBaseUrl() }}/chat/user"
        data-csrf="{{ csrf_token() }}"
        data-user-id="{{ auth()->id() }}"
        data-chat-enabled="{{ $globalChatEnabled ? '1' : '0' }}"
        data-chat-disabled-message="{{ e($globalChatDisabledMsg) }}"
      >
        <button type="button" class="chat-bubble" id="chat-bubble" aria-label="Chat">
          <i class="fa-solid fa-comments"></i>
          <span class="chat-bubble-badge" id="chat-badge" hidden>0</span>
        </button>

        <div class="chat-panel" id="chat-panel" hidden>
          <div class="chat-panel-header">
            <div class="chat-panel-title">
              <i class="fa-solid fa-comments"></i>
              <span>Global chat</span>
            </div>
            <div class="chat-panel-actions">
              <button type="button" class="chat-panel-btn" id="chat-fullscreen-btn" aria-label="Kengaytirish" title="To'liq ekran">
                <i class="fa-solid fa-expand"></i>
              </button>
              <button type="button" class="chat-panel-btn" id="chat-close-btn" aria-label="Yopish">
                <i class="fa-solid fa-xmark"></i>
              </button>
            </div>
          </div>
          <div id="chat-disabled-panel" class="chat-disabled-panel" @if($globalChatEnabled) hidden @endif>
            <div class="chat-disabled-panel-icon" aria-hidden="true"><i class="fa-solid fa-lock"></i></div>
            <p id="chat-disabled-panel-text" class="chat-disabled-panel-text"></p>
          </div>
          <div id="chat-panel-main" class="chat-panel-main" @if(!$globalChatEnabled) hidden @endif>
          <div class="chat-panel-intro">
            <div class="chat-panel-kicker">
              <span class="chat-panel-live-dot chat-panel-live-dot--channel" aria-hidden="true"></span>
              <span>Umumiy chat</span>
            </div>
            <p class="chat-panel-subtitle">Savol bering, tezkor fikr yozing yoki sticker bilan javob qoldiring.</p>
          </div>
          <details class="chat-rules">
            <summary>Chat qoidalari</summary>
            <ul>
              <li>Hurmat bilan yozing, haqorat va trolling qilmang.</li>
              <li>Spam, reklama va takroriy xabarlarni yubormang.</li>
              <li>Telefon, manzil va boshqa shaxsiy ma’lumotlarni ochiq joylamang.</li>
              <li>Muammo bo‘lsa admin/moderator xabarni o‘chirishi yoki foydalanuvchini cheklashi mumkin.</li>
            </ul>
          </details>
          <div class="chat-feed-stack">
            <div class="chat-messages" id="chat-messages" aria-live="polite"></div>
          </div>
          <div class="chat-compose-status" id="chat-compose-status" hidden>
            <span class="chat-compose-status-icon" aria-hidden="true">
              <i class="fa-solid fa-pen-nib"></i>
            </span>
            <span class="chat-compose-status-text" id="chat-compose-status-text">Yozilyapti</span>
            <span class="chat-compose-status-dots" aria-hidden="true">
              <span></span>
              <span></span>
              <span></span>
            </span>
          </div>
          <form class="chat-input-wrap" id="chat-form">
            @if(turnstile_enabled())
            <div
              id="chat-turnstile-host"
              class="cf-turnstile chat-turnstile-host"
              data-sitekey="{{ turnstile_site_key() }}"
              data-size="invisible"
            ></div>
            @endif
            <div class="chat-sticker-row" aria-label="Tezkor stikerlar">
              <button type="button" class="chat-sticker-btn" data-chat-sticker="🔥" title="Fire">🔥</button>
              <button type="button" class="chat-sticker-btn" data-chat-sticker="👏" title="Clap">👏</button>
              <button type="button" class="chat-sticker-btn" data-chat-sticker="😄" title="Smile">😄</button>
              <button type="button" class="chat-sticker-btn" data-chat-sticker="👍" title="Like">👍</button>
              <button type="button" class="chat-sticker-btn" data-chat-sticker="🎉" title="Party">🎉</button>
              <button type="button" class="chat-sticker-btn" data-chat-sticker="❤️" title="Love">❤️</button>
            </div>
            <input type="text" id="chat-input" class="chat-input" placeholder="Xabar yozing..." maxlength="1000" autocomplete="off" />
            <button type="submit" class="chat-send-btn" id="chat-send-btn" aria-label="Yuborish">
              <i class="fa-solid fa-paper-plane"></i>
            </button>
          </form>
          </div>
        </div>
      </div>

      <span id="user-preview-config" hidden
        data-user-preview-base="{{ request()->getBaseUrl() }}/chat/user"
        data-csrf="{{ csrf_token() }}"
        data-current-user-id="{{ auth()->id() }}"
      ></span>
      @endunless
    @endauth

    @unless($isExamSessionRoute)
    <button
      id="scroll-top"
      class="scroll-top"
      type="button"
      aria-label="Yuqoriga"
    >
      <i class="fa-solid fa-chevron-up"></i>
    </button>
    @endunless

    @include('components.confirm-modal')
    <div id="global-modal-root"></div>
    <span id="global-search-config" hidden data-search-url="{{ route('search') }}"></span>

    <div id="global-search-modal" class="global-search-modal" hidden>
      <div class="global-search-shell" role="dialog" aria-modal="true" aria-labelledby="global-search-label">
        <label id="global-search-label" for="global-search-input" class="global-search-label">Butun sayt bo'yicha qidiruv</label>
        <div class="global-search-input-wrap">
          <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
          <input
            id="global-search-input"
            type="search"
            autocomplete="off"
            placeholder="Post, ustoz, kurs, imtihon..."
            maxlength="120"
          >
        </div>
        <div id="global-search-results" class="global-search-results"></div>
      </div>
    </div>

    @auth
      @unless($isExamSessionRoute)
      <dialog id="chat-user-preview-dialog" class="chat-user-preview-dialog" aria-labelledby="chat-user-preview-name">
        <div class="chat-user-preview-shell">
          <button type="button" class="chat-user-preview-close" id="chat-user-preview-close" aria-label="Yopish">
            <i class="fa-solid fa-xmark"></i>
          </button>
          <div class="chat-user-preview-body">
            <p class="chat-user-preview-loading" id="chat-user-preview-loading">Yuklanmoqda…</p>
            <div class="chat-user-preview-content" id="chat-user-preview-content" hidden>
              <div class="chat-user-preview-avatar" id="chat-user-preview-avatar"></div>
              <h3 class="chat-user-preview-name" id="chat-user-preview-name"></h3>
              <p class="chat-user-preview-role" id="chat-user-preview-role"></p>
              <ul class="chat-user-preview-details" id="chat-user-preview-details"></ul>
              <div class="chat-user-preview-extra" id="chat-user-preview-extra"></div>
              <div class="chat-user-preview-admin-actions" id="chat-user-preview-admin-actions" hidden></div>
              <div class="chat-user-preview-contact" id="chat-user-preview-contact" hidden></div>
            </div>
          </div>
        </div>
      </dialog>
      @endunless
    @endauth

    @if(turnstile_enabled())
    <div
      id="comment-turnstile-host"
      class="cf-turnstile comment-turnstile-host"
      data-sitekey="{{ turnstile_site_key() }}"
      data-size="invisible"
      aria-hidden="true"
    ></div>
    @endif

    <div id="toast-container" class="toast-container" aria-live="polite" aria-atomic="true"></div>

    @auth
      @unless($isExamSessionRoute)
      {{-- Ovoz tugmasi JS orqali shu konteynerga qo‘yiladi (global chat + AI bilan bir ustunda) --}}
      <div id="prime-audio-slot" class="prime-audio-slot"></div>
      @endunless
    @endauth

    <script src="{{ app_public_asset('temp/js/confirm-modal.js') }}?v={{ filemtime(public_path('temp/js/confirm-modal.js')) }}"></script>
    <script src="{{ app_public_asset('temp/js/public-layout.js') }}?v={{ filemtime(public_path('temp/js/public-layout.js')) }}"></script>
    <script>
      (function() {
        /**
         * PRIME ANIMATION ENGINE v3.0 (Pro Max Ultra)
         * - Letter-by-letter split text
         * - Scroll progress bar tracking
         * - Staggered grid/list entry
         * - Universal intersection reveals
         */
        
        const primeEngine = {
          initProgressBar() {
            const bar = document.getElementById('prime-scroll-bar');
            if (!bar) return;
            
            const updateBar = () => {
              const h = document.documentElement;
              const st = h.scrollTop || document.body.scrollTop;
              const sh = h.scrollHeight || document.body.scrollHeight;
              const scrollPercent = (st / (sh - h.clientHeight)) * 100;
              bar.style.width = scrollPercent + "%";
            };
            
            window.addEventListener('scroll', updateBar, { passive: true });
            updateBar();
          },

          splitText(target) {
            if (target.dataset.animated === 'true') return;
            target.dataset.animated = 'true';

            // H1/H2 (yoki ularning ichidagi js-split-text) uchun harfma-harf animatsiyani o‘chirib,
            // oddiy ko‘rinish qoldiramiz. Bu "son sanashga o‘xshash" effektni yo‘q qiladi.
            const headingHost = target.closest('h1, h2');
            if (headingHost) {
              target.classList.add('active');
              return;
            }

            const processNode = (node, state) => {
              if (node.nodeType === 3) {
                const fragment = document.createDocumentFragment();
                const words = node.textContent.split(/(\s+)/);

                words.forEach((word) => {
                  if (word.trim() === '') {
                    fragment.appendChild(document.createTextNode(word));
                    return;
                  }

                  const wordSpan = document.createElement('span');
                  wordSpan.className = 'anim-word';
                  wordSpan.style.cssText = 'display:inline-block; white-space:nowrap; vertical-align:top;';

                  [...word].forEach((char) => {
                    const letter = document.createElement('span');
                    letter.textContent = char;
                    letter.className = 'letter';
                    const lift = 88;
                    /* Klassik: barcha harflar yuqoridan pastga (eski Prime split-text) */
                    letter.style.transform = `translate3d(0, ${-lift}px, 0)`;
                    wordSpan.appendChild(letter);
                    setTimeout(() => letter.classList.add('active'), 100 + state.delay);
                    state.delay += 25;
                  });
                  fragment.appendChild(wordSpan);
                });
                node.parentNode.replaceChild(fragment, node);
              } else if (node.nodeType === 1) {
                Array.from(node.childNodes).forEach(child => processNode(child, state));
              }
            };

            const state = { delay: 0 };
            Array.from(target.childNodes).forEach(c => processNode(c, state));
            /* Brauzer/setTimeout xatolari uchun: ba’zi harflar .active olmasa ham matn ko‘rinsin */
            const safetyMs = 100 + state.delay + 400;
            window.setTimeout(() => {
              target.querySelectorAll('.letter:not(.active)').forEach((el) => el.classList.add('active'));
            }, safetyMs);
          },

          stagger(target) {
            if (target.dataset.animated === 'true') return;
            target.dataset.animated = 'true';
            
            const children = target.children;
            const delayStep = 100;
            
            Array.from(children).forEach((child, i) => {
              setTimeout(() => {
                child.style.opacity = '1';
                child.style.transform = 'translateY(0)';
              }, i * delayStep);
            });
            target.classList.add('active');
          },

          reveal(target) {
            target.classList.add('active');
          }
        };

        const initAllAnimations = () => {
          // Delay initialization by 350ms to allow browser scroll restoration to finish
          window.setTimeout(() => {
            primeEngine.initProgressBar();

            const activatePrimeEl = (el, staggerIdx = 0) => {
              const delay = staggerIdx * 100;
              window.setTimeout(() => {
                if (el.classList.contains('js-split-text')) primeEngine.splitText(el);
                else if (el.classList.contains('prime-stagger')) primeEngine.stagger(el);
                else if (el.classList.contains('prime-reveal')) primeEngine.reveal(el);
              }, delay);
            };

            const observer = new IntersectionObserver((entries) => {
              entries.forEach(entry => {
                if (entry.isIntersecting) {
                  // No stagger needed for normal scroll reveals
                  activatePrimeEl(entry.target, 0);
                  observer.unobserve(entry.target);
                }
              });
            }, { threshold: 0, rootMargin: '120px 0px 120px 0px' });

            const nodes = document.querySelectorAll('.js-split-text, .prime-stagger, .prime-reveal');
            nodes.forEach(el => observer.observe(el));

            /* Birinchi ekrandagi bloklar uchun: staggered yuklanish */
            requestAnimationFrame(() => {
              requestAnimationFrame(() => {
                const vh = window.innerHeight || document.documentElement.clientHeight;
                let inViewCount = 0;
                nodes.forEach((el) => {
                  const r = el.getBoundingClientRect();
                  if (r.bottom > 0 && r.top < vh) {
                    activatePrimeEl(el, inViewCount);
                    observer.unobserve(el);
                    inViewCount++;
                  }
                });
              });
            });
          }, 350);
        };

        if (document.readyState === 'loading') {
          document.addEventListener('DOMContentLoaded', initAllAnimations);
        } else {
          initAllAnimations();
        }

        /* Expose to window for AJAX pages */
        window.initPrimeAnimations = initAllAnimations;
      })();
    </script>
    @unless(request()->routeIs('exam.session'))
    <script src="{{ app_public_asset('temp/js/site-boot-loader.js') }}?v={{ filemtime(public_path('temp/js/site-boot-loader.js')) }}"></script>
    @endunless
    @auth
    @unless(request()->routeIs('exam.session'))
    @php
      $aiChatEnabled = \App\Models\SiteSetting::get('ai_chat_enabled', '1') === '1';
      $aiChatDisabledMsg = trim((string) \App\Models\SiteSetting::get('ai_chat_disabled_message', '')) ?: 'AI yordamchi vaqtincha o‘chirilgan. Keyinroq urinib ko‘ring.';
    @endphp
    <div
      id="ai-widget"
      class="ai-widget"
      data-ai-url="{{ route('ai.chat') }}"
      data-csrf="{{ csrf_token() }}"
      data-ai-mock-delim="{{ config('ai.mock_delimiter') }}"
      data-ai-enabled="{{ $aiChatEnabled ? '1' : '0' }}"
      data-ai-disabled-message="{{ e($aiChatDisabledMsg) }}"
    >
      <button type="button" class="ai-bubble prime-3d-target" id="ai-bubble" aria-label="AI Yordamchi" title="AI Yordamchi">
        <i class="fa-solid fa-magic-wand-sparkles" aria-hidden="true"></i>
      </button>

      <div class="chat-panel ai-panel" id="ai-panel">
        <div class="chat-panel-header">
          <div class="chat-panel-title">
            <i class="fa-solid fa-robot"></i>
            <span>81-AI Yordamchi</span>
          </div>
          <div class="chat-panel-actions">
            <button type="button" class="chat-panel-btn" id="ai-close-btn" aria-label="Yopish">
              <i class="fa-solid fa-xmark"></i>
            </button>
          </div>
        </div>
        <div id="ai-disabled-panel" class="ai-disabled-panel chat-disabled-panel" @if($aiChatEnabled) hidden @endif>
          <div class="chat-disabled-panel-icon" aria-hidden="true"><i class="fa-solid fa-lock"></i></div>
          <p id="ai-disabled-panel-text" class="chat-disabled-panel-text"></p>
        </div>
        <div id="ai-panel-main" class="ai-panel-main" @if(!$aiChatEnabled) hidden @endif>
        <div class="chat-panel-intro">
          <div class="chat-panel-kicker">
            <span class="chat-panel-live-dot chat-panel-live-dot--ai" aria-hidden="true"></span>
            <span>AI yordamchi</span>
          </div>
          <p class="chat-panel-subtitle">Assalomu alaykum! Men 81-IDUM saytining AI yordamchisiman. Maktabimiz, ta'lim, va boshqa fanlar bo'yicha yozing — sizga bajonidil yordam beraman! ✨</p>
        </div>

        <div class="chat-messages ai-messages" id="ai-messages" aria-live="polite">
          <div class="chat-msg is-ai reveal">
            <div class="chat-msg-content">Salom! Men 81-maktabning AI yordamchisiman. Sizga qanday yordam bera olaman? 😊</div>
          </div>
        </div>
        <div class="chat-compose-status ai-status" id="ai-compose-status" hidden>
          <span class="chat-compose-status-icon ai-typing-indicator">
             <i class="fa-solid fa-circle-notch fa-spin"></i>
          </span>
          <span class="chat-compose-status-text">O'ylamoqda...</span>
        </div>

        <div class="ai-quick-actions" style="display:flex; flex-wrap:wrap; gap:8px; padding:0 12px 10px;">
          <button type="button" class="ai-action-btn" data-msg="Bugun qanday darslar bor?" style="white-space:nowrap; padding:6px 12px; border-radius:20px; border:1px solid var(--border); background:var(--bg); color:var(--text); font-size:12px; cursor:pointer">Darslar 🗓️</button>
          <button type="button" class="ai-action-btn" data-msg="Mening imtihon natijalarimni ko'rsat" style="white-space:nowrap; padding:6px 12px; border-radius:20px; border:1px solid var(--border); background:var(--bg); color:var(--text); font-size:12px; cursor:pointer">Natijalarim 📝</button>
          <button type="button" class="ai-action-btn" data-msg="Maktab manzili va telefon raqami qanday?" style="white-space:nowrap; padding:6px 12px; border-radius:20px; border:1px solid var(--border); background:var(--bg); color:var(--text); font-size:12px; cursor:pointer">Aloqa 📞</button>
        </div>

        <form class="chat-input-wrap" id="ai-chat-form">
          <textarea
            class="chat-textarea"
            id="ai-textarea"
            placeholder="savolingizni kiriting..."
            rows="1"
            maxlength="5000"
            style="resize:none; padding:10px; border-radius:12px; font-family:inherit"
          ></textarea>
          <button type="submit" class="chat-send-btn" id="ai-send-btn" aria-label="Yuborish">
            <i class="fa-solid fa-paper-plane"></i>
          </button>
        </form>
        </div>
      </div>
    </div>
    @endunless
    @unless(request()->routeIs('exam.session'))
    <script>
      (function() {
        console.log('AI Script Initialized');
        var widget = document.getElementById('ai-widget');
        if (!widget) { console.log('AI Widget not found'); return; }

        var bubble = document.getElementById('ai-bubble');
        var panel = document.getElementById('ai-panel');
        var closeBtn = document.getElementById('ai-close-btn');
        var messagesEl = document.getElementById('ai-messages');
        var form = document.getElementById('ai-chat-form');
        var input = document.getElementById('ai-textarea');
        var sendBtn = document.getElementById('ai-send-btn');
        var statusWrap = document.getElementById('ai-compose-status');

        var aiUrl = widget.getAttribute('data-ai-url');
        var csrfToken = widget.getAttribute('data-csrf');
        var aiMockDelim = widget.getAttribute('data-ai-mock-delim') || '';
        var headerToggle = document.getElementById('ai-header-toggle');
        var isSending = false;
        var aiDisabledPanel = document.getElementById('ai-disabled-panel');
        var aiPanelMain = document.getElementById('ai-panel-main');
        var aiDisabledText = document.getElementById('ai-disabled-panel-text');
        var aiEnabled = widget.getAttribute('data-ai-enabled') !== '0';

        function resetAiComposeState() {
          isSending = false;
          if (sendBtn) {
            sendBtn.disabled = false;
            sendBtn.removeAttribute('aria-busy');
            sendBtn.style.pointerEvents = 'auto';
          }
          if (input) {
            input.disabled = false;
            input.removeAttribute('aria-busy');
          }
          if (statusWrap) {
            statusWrap.style.display = 'none';
            statusWrap.setAttribute('hidden', '');
          }
        }

        function closePanel() {
          resetAiComposeState();
          panel.classList.remove('is-open');
        }

        function openPanel() {
          if (typeof window.primeCloseGlobalChatPanel === 'function') {
            window.primeCloseGlobalChatPanel();
          }
          panel.classList.add('is-open');
          if (!aiEnabled && aiDisabledPanel && aiPanelMain) {
            aiPanelMain.hidden = true;
            aiDisabledPanel.hidden = false;
            if (aiDisabledText) {
              aiDisabledText.textContent = widget.getAttribute('data-ai-disabled-message') || 'AI yordamchi vaqtincha o‘chirilgan.';
            }
            if (window.playPrimeSuccess) window.playPrimeSuccess();
            return;
          }
          if (aiDisabledPanel) aiDisabledPanel.hidden = true;
          if (aiPanelMain) aiPanelMain.hidden = false;
          resetAiComposeState();
          if (window.playPrimeSuccess) window.playPrimeSuccess();
          input.focus();
        }

        window.primeCloseAiPanel = function() {
          if (panel.classList.contains('is-open')) closePanel();
        };

        function togglePanel(e) {
          e.preventDefault();
          e.stopPropagation();
          if (panel.classList.contains('is-open')) {
            closePanel();
          } else {
            openPanel();
          }
        }

        bubble.addEventListener('click', togglePanel);
        if (headerToggle) headerToggle.addEventListener('click', togglePanel);

        document.addEventListener('click', function (e) {
          if (panel.classList.contains('is-open') && !panel.contains(e.target) && !bubble.contains(e.target)) {
            closePanel();
          }
        });

        closeBtn.addEventListener('click', function(e) {
          e.preventDefault();
          closePanel();
        });

        function scrollToBottom() {
          messagesEl.scrollTo({ top: messagesEl.scrollHeight, behavior: 'smooth' });
        }

        function stripMockTailForDisplay(full) {
          if (!aiMockDelim || full.indexOf(aiMockDelim) === -1) return full;
          var head = full.split(aiMockDelim)[0].trim();
          return head || '…';
        }

        function addMessage(text, isAi) {
          if (!text || !text.trim()) return;
          var el = document.createElement('div');
          el.className = 'chat-msg ' + (isAi ? 'is-ai' : 'is-user');
          
          // Strip Markdown bold symbols if AI slips up
          var cleanText = text.replace(/\*\*(.*?)\*\*/g, '$1').replace(/\*(.*?)\*/g, '$1');
          
          var avatarHtml = isAi ? '<div class="ai-avatar"><i class="fa-solid fa-robot"></i></div>' : '<div class="ai-avatar"><i class="fa-solid fa-user-circle"></i></div>';
          
          el.innerHTML = avatarHtml + '<div class="chat-msg-content">' + cleanText.replace(/<[^>]+>/g, '') + '</div>';
          messagesEl.appendChild(el);
          
          // Force a tiny reflow for animation
          void el.offsetWidth;
          el.classList.add('reveal');
          el.style.opacity = '1';
          el.style.transform = 'translateY(0)';
          
          scrollToBottom();
        }

        form.addEventListener('submit', function (e) {
          e.preventDefault();
          if (!aiEnabled) return;
          if (sendBtn && sendBtn.disabled) {
            resetAiComposeState();
          }
          var txt = input.value.trim();
          if (!txt || isSending) return;

          console.log('Sending message:', txt);
          isSending = true;
          addMessage(stripMockTailForDisplay(txt), false);
          input.value = '';
          sendBtn.disabled = true;
          if (window.playPrimeChatTick) window.playPrimeChatTick();
          statusWrap.style.display = 'flex';
          statusWrap.removeAttribute('hidden');
          scrollToBottom();

          fetch(aiUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body: JSON.stringify({ message: txt })
          })
          .then(function(res) {
            return res.json().then(function(data) {
              return { ok: res.ok, status: res.status, data: data };
            });
          })
          .then(function(payload) {
            var data = payload.data;
            statusWrap.style.display = 'none';
            statusWrap.setAttribute('hidden', '');
            isSending = false;
            sendBtn.disabled = false;
            if (data && data.success) {
              addMessage(data.text, true);
              if (window.playPrimeResultPass) window.playPrimeResultPass();
            } else if (data && data.disabled) {
              aiEnabled = false;
              widget.setAttribute('data-ai-enabled', '0');
              addMessage(data.error || 'AI vaqtincha o‘chirilgan.', true);
            } else {
              addMessage((data && data.error) || "Xatolik yuz berdi.", true);
            }
          })
          .catch(function(err) {
            console.error('AI Fetch error:', err);
            statusWrap.style.display = 'none';
            statusWrap.setAttribute('hidden', '');
            isSending = false;
            sendBtn.disabled = false;
            addMessage("Tarmoqda xatolik yuz berdi. Iltimos qayta urinib ko'ring.", true);
          });
        });

        input.addEventListener('keydown', function (e) {
          if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            form.dispatchEvent(new Event('submit'));
          }
        });

        input.addEventListener('input', function() {
          if (window.playPrimeChatTick && !isSending) window.playPrimeChatTick();
        });

        // Quick Action Buttons Handler (with Cooldown)
        var actionBtns = document.querySelectorAll('.ai-action-btn');
        actionBtns.forEach(function(btn) {
          btn.addEventListener('click', function() {
             if (!aiEnabled || btn.disabled || isSending) return;
             
             // Visual feedback and cooldown
             actionBtns.forEach(b => {
                 b.disabled = true;
                 b.style.opacity = '0.5';
                 b.style.cursor = 'not-allowed';
             });

             var msg = this.getAttribute('data-msg');
             input.value = msg;
             form.dispatchEvent(new Event('submit'));

             // Re-enable after 3 seconds
             setTimeout(() => {
                 actionBtns.forEach(b => {
                     b.disabled = false;
                     b.style.opacity = '1';
                     b.style.cursor = 'pointer';
                 });
             }, 3000);
          });
        });
      })();
    </script>
    @endunless
    @endauth
    @stack('page_scripts')
  </body>
</html>
