<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="shortcut icon" href="{{ asset('admin/images/favicon.svg') }}" type="image/x-icon" />
    <title>@yield('title', 'Admin Panel')</title>

    <!-- ========== All CSS files linkup ========= -->
    <link rel="stylesheet" href="{{ asset('admin/css/bootstrap.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('admin/css/lineicons.css') }}" />
    <link rel="stylesheet" href="{{ asset('admin/css/materialdesignicons.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('admin/css/fullcalendar.css') }}" />
    <link rel="stylesheet" href="{{ asset('admin/css/main.css') }}" />
    <style>
      .sidebar-section {
        padding: 18px 25px 8px;
        color: #9aa4ca;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
      }

      .sidebar-nav-wrapper .sidebar-nav .divider.section-divider {
        padding: 6px 25px;
      }

      .sidebar-nav-wrapper .sidebar-nav .divider.section-divider hr {
        margin: 0;
      }

      .sidebar-nav-wrapper .sidebar-nav ul .nav-item a .icon i {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 19px;
        line-height: 1;
      }
    </style>
  </head>
  <body>
    <!-- ======== Preloader =========== -->
    <div id="preloader">
      <div class="spinner"></div>
    </div>
    <!-- ======== Preloader =========== -->

        <!-- ======== sidebar-nav start =========== -->
    <aside class="sidebar-nav-wrapper">
      <div class="navbar-logo">
        <a href="{{ route('dashboard') }}">
          <img src="{{ asset('admin/images/logo/logo.svg') }}" alt="logo" />
        </a>
      </div>
      <nav class="sidebar-nav">
        @php
          $sidebarUser = auth()->user();
          $canManageContent = $sidebarUser->canManageContent();
          $canManageInbox = $sidebarUser->canManageInbox();
          $canManageEducation = $sidebarUser->canManageEducation();
          $canManageSystem = $sidebarUser->canManageSystem();
          $examsIndexActive = request()->routeIs('admin.exams.index')
              || request()->routeIs('admin.exams.create')
              || request()->routeIs('admin.exams.edit')
              || request()->routeIs('admin.exams.questions.*');
        @endphp

        <ul>
          <li class="sidebar-section">Asosiy</li>
          <li class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <a href="{{ route('dashboard') }}">
              <span class="icon"><i class="mdi mdi-view-dashboard-outline"></i></span>
              <span class="text">Dashboard</span>
            </a>
          </li>

          @if($canManageContent || $canManageInbox)
            <li class="sidebar-section">Kontent</li>

            @if($canManageContent)
              <li class="nav-item {{ request()->routeIs('posts.*') ? 'active' : '' }}">
                <a href="{{ route('posts.index') }}">
                  <span class="icon"><i class="mdi mdi-newspaper-variant-outline"></i></span>
                  <span class="text">Yangiliklar</span>
                </a>
              </li>

              <li class="nav-item {{ request()->routeIs('categories.*') ? 'active' : '' }}">
                <a href="{{ route('categories.index') }}">
                  <span class="icon"><i class="mdi mdi-shape-outline"></i></span>
                  <span class="text">Kategoriyalar</span>
                </a>
              </li>

              <li class="nav-item {{ request()->routeIs('calendar-events.*') ? 'active' : '' }}">
                <a href="{{ route('calendar-events.index') }}">
                  <span class="icon"><i class="mdi mdi-calendar-month-outline"></i></span>
                  <span class="text">Taqvim</span>
                </a>
              </li>

            @endif

            @if($canManageInbox)
              <li class="nav-item {{ request()->routeIs('admin.comments.*') ? 'active' : '' }}">
                <a href="{{ route('admin.comments.index') }}">
                  <span class="icon"><i class="mdi mdi-comment-text-multiple-outline"></i></span>
                  <span class="text">Izohlar</span>
                </a>
              </li>

              <li class="nav-item {{ request()->routeIs('admin.contact-messages.*') ? 'active' : '' }}">
                <a href="{{ route('admin.contact-messages.index') }}">
                  <span class="icon"><i class="mdi mdi-email-outline"></i></span>
                  <span class="text">Aloqa xabarlari</span>
                </a>
              </li>
            @endif
          @endif

          @if($canManageEducation)
            <li class="sidebar-section">Ta'lim</li>

            @if($sidebarUser->canManageSystem())
              <li class="nav-item {{ request()->routeIs('teachers.*') ? 'active' : '' }}">
                <a href="{{ route('teachers.index') }}">
                  <span class="icon"><i class="mdi mdi-school-outline"></i></span>
                  <span class="text">Ustozlar</span>
                </a>
              </li>

              <li class="nav-item {{ request()->routeIs('admin.courses.*') ? 'active' : '' }}">
                <a href="{{ route('admin.courses.index') }}">
                  <span class="icon"><i class="mdi mdi-book-open-page-variant-outline"></i></span>
                  <span class="text">Kurslar</span>
                </a>
              </li>

              <li class="nav-item {{ request()->routeIs('admin.course-enrollments.index') ? 'active' : '' }}">
                <a href="{{ route('admin.course-enrollments.index') }}">
                  <span class="icon"><i class="mdi mdi-clipboard-text-outline"></i></span>
                  <span class="text">Kurs yozilishlari</span>
                </a>
              </li>

              <li class="nav-item {{ $examsIndexActive ? 'active' : '' }}">
                <a href="{{ route('admin.exams.index') }}">
                  <span class="icon"><i class="mdi mdi-file-document-edit-outline"></i></span>
                  <span class="text">Imtihonlar</span>
                </a>
              </li>

              <li class="nav-item {{ request()->routeIs('admin.exams.results') ? 'active' : '' }}">
                <a href="{{ route('admin.exams.results') }}">
                  <span class="icon"><i class="mdi mdi-chart-box-outline"></i></span>
                  <span class="text">Imtihon natijalari</span>
                </a>
              </li>
            @elseif($sidebarUser->hasRole('teacher'))
              <li class="nav-item {{ request()->routeIs('teacher.enrollments.*') ? 'active' : '' }}">
                <a href="{{ route('teacher.enrollments.index') }}">
                  <span class="icon"><i class="mdi mdi-clipboard-text-outline"></i></span>
                  <span class="text">Kurs arizalari</span>
                </a>
              </li>
            @endif
          @endif

          @if($canManageSystem)
            <li class="sidebar-section">Tizim</li>

            <li class="nav-item {{ request()->routeIs('user') ? 'active' : '' }}">
              <a href="{{ route('user') }}">
                <span class="icon"><i class="mdi mdi-account-group-outline"></i></span>
                <span class="text">Foydalanuvchilar</span>
              </a>
            </li>
          @endif

          <li class="divider section-divider"><hr></li>
          <li class="nav-item">
            <a href="{{ route('home') }}">
              <span class="icon"><i class="mdi mdi-open-in-new"></i></span>
              <span class="text">Saytga qaytish</span>
            </a>
          </li>
        </ul>
      </nav>

    </aside>
    <div class="overlay"></div>



    <main class="main-wrapper">

      <header class="header">
        <div class="container-fluid">
          <div class="row">
            <div class="col-lg-5 col-md-5 col-6">
              <div class="header-left d-flex align-items-center">
                <div class="menu-toggle-btn mr-15">
                  <button id="menu-toggle" class="main-btn primary-btn btn-hover">
                    <i class="lni lni-chevron-left me-2"></i> Menu
                  </button>
                </div>
                <div class="header-search d-none d-md-flex">
                  <form action="#">
                    <input type="text" placeholder="Search..." />
                    <button><i class="lni lni-search-alt"></i></button>
                  </form>
                </div>
              </div>
            </div>
            <div class="col-lg-7 col-md-7 col-6">
              <div class="header-right">
                <!-- message start -->
                <div class="profile-box ml-15">
                  <button class="dropdown-toggle bg-transparent border-0" type="button" id="profile"
                    data-bs-toggle="dropdown" aria-expanded="false">
                    <div class="profile-info">
                      <div class="info">
                        <div class="image">
                          <img src="{{ asset('admin/images/profile/profile-image.png') }}" alt="" />
                        </div>
                        <div>
                          <h6 class="fw-500">{{ auth()->user()->name }}</h6>
                          <p>Admin</p>
                        </div>
                      </div>
                    </div>
                  </button>
                  <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profile">
                    <li>
                      <div class="author-info flex items-center !p-1">
                        <div class="image">
                          <img src="{{ asset('admin/images/profile/profile-image.png') }}" alt="image">
                        </div>
                        <div class="content">
                          <h4 class="text-sm">{{ auth()->user()->name }}</h4>
                          <a class="text-black/40 dark:text-white/40 hover:text-black dark:hover:text-white text-xs" href="#">{{ auth()->user()->email ?? 'admin@example.com' }}</a>
                        </div>
                      </div>
                    </li>
                    <li class="divider"></li>
                    <li>
                      <a href="#0">
                        <i class="lni lni-user"></i> View Profile
                      </a>
                    </li>
                    <li>
                      <a href="#0"> <i class="lni lni-inbox"></i> Messages </a>
                    </li>
                    <li>
                      <a href="#0"> <i class="lni lni-cog"></i> Settings </a>
                    </li>
                    <li class="divider"></li>
                    <li>
                      @auth
                        <form action="{{ route('logout') }}" method="POST" style="margin:0;">
                          @csrf
                          <button type="submit" style="background:transparent;border:0;width:100%;text-align:left;padding:10px 16px;">
                            <i class="lni lni-exit"></i> Sign Out
                          </button>
                        </form>
                      @else
                        <a href="{{ route('login') }}"> <i class="lni lni-enter"></i> Sign In </a>
                      @endauth
                    </li>
                  </ul>
                </div>
                <!-- profile end -->
              </div>
            </div>
          </div>
        </div>
      </header>
      <!-- ========== header end ========== -->



    @yield('content')

    <!-- ========== footer start =========== -->
    <footer class="footer">
      <div class="container-fluid">
        <div class="row">
          <div class="col-md-6 order-last order-md-first">
            <div class="copyright text-center text-md-start">
              <p class="text-sm">
                Designed and Developed by
                <a href="https://plainadmin.com" rel="nofollow" target="_blank">
                  PlainAdmin
                </a>
              </p>
            </div>
          </div>
          <div class="col-md-6">
            <div class="terms d-flex justify-content-center justify-content-md-end">
              <a href="#0" class="text-sm">Term & Conditions</a>
              <a href="#0" class="text-sm ml-15">Privacy & Policy</a>
            </div>
          </div>
        </div>
      </div>
    </footer>
    <!-- ========== footer end =========== -->
    </main>
    <!-- ======== main-wrapper end =========== -->

    <!-- ========= All Javascript files linkup ======== -->
    <script src="{{ asset('admin/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('admin/js/Chart.min.js') }}"></script>
    <script src="{{ asset('admin/js/dynamic-pie-chart.js') }}"></script>
    <script src="{{ asset('admin/js/moment.min.js') }}"></script>
    <script src="{{ asset('admin/js/fullcalendar.js') }}"></script>
    <script src="{{ asset('admin/js/jvectormap.min.js') }}"></script>
    <script src="{{ asset('admin/js/world-merc.js') }}"></script>
    <script src="{{ asset('admin/js/polyfill.js') }}"></script>
    <script src="{{ asset('admin/js/main.js') }}"></script>

    @yield('page_scripts')

    <!-- Admin toast (session flash -> top-right toast) -->
    <style>
      #admin-toast-container {
        position: fixed;
        top: 115px;
        right: 18px;
        z-index: 999999;
        display: flex;
        flex-direction: column;
        gap: 10px;
        pointer-events: none;
      }

      #admin-toast-container .admin-toast {
        pointer-events: auto;
        max-width: 420px;
        padding: 12px 14px;
        border-radius: 14px;
        color: #fff;
        font-weight: 700;
        font-size: 14px;
        box-shadow: 0 18px 50px rgba(0, 0, 0, 0.25);
        border: 1px solid rgba(255, 255, 255, 0.2);
        background: rgba(8, 24, 43, 0.92);
        opacity: 0;
        transform: translateX(24px) translateY(6px);
        animation: adminToastIn 260ms ease forwards;
      }

      #admin-toast-container .admin-toast.admin-success {
        background: rgba(15, 118, 110, 0.14);
        border-color: rgba(15, 118, 110, 0.35);
        color: #0f766e;
      }

      #admin-toast-container .admin-toast.admin-warning {
        background: rgba(245, 158, 11, 0.14);
        border-color: rgba(245, 158, 11, 0.35);
        color: #f59e0b;
      }

      #admin-toast-container .admin-toast.admin-error {
        background: rgba(220, 38, 38, 0.12);
        border-color: rgba(220, 38, 38, 0.3);
        color: #dc2626;
      }

      #admin-toast-container .admin-toast.admin-toast-out {
        animation: adminToastOut 220ms ease forwards;
      }

      @keyframes adminToastIn {
        to {
          opacity: 1;
          transform: translateX(0) translateY(0);
        }
      }

      @keyframes adminToastOut {
        to {
          opacity: 0;
          transform: translateX(18px) translateY(6px);
        }
      }
    </style>

    <div id="admin-toast-container" aria-live="polite" aria-atomic="true"></div>

    <script>
      (() => {
        const container = document.getElementById('admin-toast-container');
        if (!container) return;

        const toastTimerMs = 3200;

        function showToast(message, type) {
          if (!message) return;

          const toast = document.createElement('div');
          toast.className = `admin-toast admin-${type}`;
          toast.textContent = message;
          container.appendChild(toast);

          setTimeout(() => {
            toast.classList.add('admin-toast-out');
            setTimeout(() => toast.remove(), 250);
          }, toastTimerMs);
        }

        const sessionSuccess = @json(session('success'));
        const sessionError = @json(session('error'));
        const toastTypeFlash = @json(session('toast_type'));

        const errorsAny = @json(isset($errors) && $errors->any());
        const errorMessages = @json(isset($errors) ? $errors->all() : []);

        function resolveType(defaultType) {
          if (!toastTypeFlash) return defaultType;
          if (toastTypeFlash === 'warning') return 'warning';
          if (toastTypeFlash === 'error') return 'error';
          if (toastTypeFlash === 'success') return 'success';
          return defaultType;
        }

        // Session flash bilan toast takrorlanmasin. Validatsiya xatosi (.danger-alert) ni o‘chirma — aks holda forma xabarlari yo‘qoladi.
        if (errorsAny) {
          document.querySelectorAll('.alert-box.success-alert').forEach((el) => el.remove());
        } else {
          document.querySelectorAll('.alert-box.success-alert, .alert-box.danger-alert').forEach((el) => el.remove());
        }

        if (sessionSuccess) {
          showToast(sessionSuccess, resolveType('success'));
        }
        if (sessionError) {
          showToast(sessionError, resolveType('error'));
        }
        if (errorsAny && errorMessages.length) {
          const summary =
            errorMessages.length === 1
              ? errorMessages[0]
              : errorMessages.slice(0, 2).join(' · ') + (errorMessages.length > 2 ? ` (+${errorMessages.length - 2})` : '');
          showToast(summary, 'error');
        }
      })();
    </script>

  </body>
</html>
