@props(['title' => '81-IDUM'])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
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
    <link rel="stylesheet" href="{{ app_public_asset('temp/css/extracted-public.css') }}?v={{ filemtime(public_path('temp/css/extracted-public.css')) }}" />
    <link rel="icon" type="image/jpeg" href="{{ app_public_asset('temp/img/photo_2026-02-06_11-05-24-2.jpg') }}" />
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
		      // Any admin or teacher with a linked profile can create a course now.
		      // The course itself will need approval before publication.
		      $canCreateCourse = $authUser && ($authUser->isAdmin() || ($authUser->isTeacher() && $authUser->hasLinkedActiveTeacherProfile()));
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
            <button class="search-toggle" type="button" aria-label="Qidirish" title="Qidirish" id="search-open-btn">
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

    <footer class="footer">
      <div class="footer-container container">
        <!-- Column 1: Branding -->
        <div class="footer-column footer-brand">
          <a href="{{ route('home') }}" class="footer-logo">
            <img src="{{ app_public_asset('temp/img/photo_2026-02-06_11-05-24-2.jpg') }}" alt="{{ __('public.layout.logo_alt') }}" />
            <span>{{ __('public.layout.school_name') }}</span>
          </a>
          <p class="footer-desc">{{ __('public.layout.footer.description') }}</p>
          <div class="footer-socials">
            <a href="#" class="social-link" title="Telegram"><i class="fa-brands fa-telegram"></i></a>
            <a href="#" class="social-link" title="Instagram"><i class="fa-brands fa-instagram"></i></a>
            <a href="#" class="social-link" title="Facebook"><i class="fa-brands fa-facebook-f"></i></a>
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
            <a href="https://maps.app.goo.gl/erCMfrDY42DCogHL6" target="_blank" rel="noopener" class="btn btn-sm btn-outline-footer">
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

    <div id="search-modal" class="search-modal" hidden role="dialog" aria-modal="true" aria-label="Qidirish">
      <div class="search-modal-backdrop"></div>
      <div class="search-modal-box">
        <div class="search-modal-input-wrap">
          <i class="fa-solid fa-magnifying-glass search-modal-icon"></i>
          <input type="search" id="search-modal-input" class="search-modal-input" placeholder="Yangilik, ustoz, kurs, imtihon qidirish..." autocomplete="off" autofocus />
          <kbd class="search-modal-kbd">ESC</kbd>
        </div>
        <div id="search-modal-results" class="search-modal-results"></div>
        <div id="search-modal-empty" class="search-modal-empty" hidden>
          <i class="fa-solid fa-magnifying-glass" style="font-size:28px;opacity:0.2;"></i>
          <p>Hech narsa topilmadi</p>
        </div>
        <div id="search-modal-hint" class="search-modal-hint">
          <p>Kamida 2 ta belgi yozing</p>
        </div>
      </div>
    </div>

    <div id="global-modal-root"></div>

    <div id="toast-container" class="toast-container" aria-live="polite" aria-atomic="true"></div>

    <script src="{{ app_public_asset('temp/js/public-layout.js') }}?v={{ filemtime(public_path('temp/js/public-layout.js')) }}"></script>
    <script>
      (function() {
        /**
         * Universal Premium Letter Animation Engine v2.0 (Pro Max)
         * Supports nested HTML tags and high-performance scroll triggers.
         */
        const splitTextProcessor = {
          getRandomOffset() {
            const range = 100;
            const offsets = [
              { x: 0, y: -range }, { x: 0, y: range },
              { x: -range, y: 0 }, { x: range, y: 0 }
            ];
            return offsets[Math.floor(Math.random() * offsets.length)];
          },

          processNode(node, state) {
            if (node.nodeType === 3) { // Text node
              const text = node.textContent;
              const fragment = document.createDocumentFragment();
              const words = text.split(/(\s+)/);

              words.forEach((word) => {
                if (word.trim() === '') {
                  fragment.appendChild(document.createTextNode(word));
                  return;
                }

                const wordSpan = document.createElement('span');
                wordSpan.className = 'anim-word';
                wordSpan.style.display = 'inline-block';
                wordSpan.style.whiteSpace = 'nowrap';
                wordSpan.style.verticalAlign = 'top';

                [...word].forEach((char) => {
                  const letter = document.createElement('span');
                  letter.textContent = char;
                  letter.className = 'letter';
                  
                  const offset = this.getRandomOffset();
                  letter.style.transform = `translate(${offset.x}px, ${offset.y}px)`;
                  
                  wordSpan.appendChild(letter);
                  
                  setTimeout(() => {
                    letter.classList.add('active');
                  }, 100 + state.delay);
                  
                  state.delay += 30;
                });

                fragment.appendChild(wordSpan);
              });

              node.parentNode.replaceChild(fragment, node);
            } else if (node.nodeType === 1) { // Element node
              const children = Array.from(node.childNodes);
              children.forEach(child => this.processNode(child, state));
            }
          },

          animate(target) {
            if (target.dataset.animated === 'true') return;
            target.dataset.animated = 'true';
            
            const state = { delay: 0 };
            const children = Array.from(target.childNodes);
            children.forEach(child => this.processNode(child, state));
          }
        };

        const initGlobalAnimations = () => {
          const splitTargets = document.querySelectorAll('.js-split-text');
          if (!splitTargets.length) return;

          const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
          };

          const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
              if (entry.isIntersecting) {
                splitTextProcessor.animate(entry.target);
                observer.unobserve(entry.target);
              }
            });
          }, observerOptions);

          splitTargets.forEach(target => observer.observe(target));
        };

        window.addEventListener('load', initGlobalAnimations);
      })();
    </script>
    @stack('page_scripts')
  </body>
</html>
