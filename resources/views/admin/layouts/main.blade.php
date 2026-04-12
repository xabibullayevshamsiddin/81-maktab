<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" type="image/png" sizes="32x32" href="{{ app_public_asset('temp/img/favicon-32.png') }}?v={{ filemtime(public_path('temp/img/favicon-32.png')) }}" />
    <link rel="icon" type="image/png" sizes="16x16" href="{{ app_public_asset('temp/img/favicon-16.png') }}?v={{ filemtime(public_path('temp/img/favicon-16.png')) }}" />
    <link rel="apple-touch-icon" sizes="180x180" href="{{ app_public_asset('temp/img/favicon-180.png') }}?v={{ filemtime(public_path('temp/img/favicon-180.png')) }}" />
    <title>@yield('title', 'Admin Panel')</title>

    <!-- ========== All CSS files linkup ========= -->
    <link rel="stylesheet" href="{{ app_public_asset('panel-assets/css/bootstrap.min.css') }}" />
    <link rel="stylesheet" href="{{ app_public_asset('panel-assets/css/lineicons.css') }}" />
	    <link rel="stylesheet" href="{{ app_public_asset('panel-assets/css/materialdesignicons.min.css') }}" />
	    <link rel="stylesheet" href="{{ app_public_asset('panel-assets/css/fullcalendar.css') }}" />
	    <link rel="stylesheet" href="{{ app_public_asset('panel-assets/css/main.css') }}" />
      <link rel="stylesheet" href="{{ app_public_asset('temp/css/extracted-admin.css') }}?v={{ filemtime(public_path('temp/css/extracted-admin.css')) }}" />
      <link rel="stylesheet" href="{{ app_public_asset('temp/css/confirm-modal.css') }}?v={{ filemtime(public_path('temp/css/confirm-modal.css')) }}" />
      <link rel="stylesheet" href="{{ app_public_asset('temp/css/calendar-public.css') }}?v={{ filemtime(public_path('temp/css/calendar-public.css')) }}" />
      @stack('admin_styles')
  </head>
	  <body
      data-admin-success="{{ session('success') }}"
      data-admin-error="{{ session('error') }}"
      data-admin-toast-type="{{ session('toast_type') }}"
      data-admin-errors='@json(isset($errors) ? $errors->all() : [])'
    >
    <!-- ======== Preloader =========== -->
    <div id="preloader">
      <div class="spinner"></div>
    </div>
    <!-- ======== Preloader =========== -->

        <!-- ======== sidebar-nav start =========== -->
    <aside class="sidebar-nav-wrapper">
      <div class="navbar-logo">
        <a href="{{ route('dashboard') }}">
          <img src="{{ app_public_asset('temp/img/photo_2026-02-06_11-05-24-2.jpg') }}" alt="logo" style="width: 45px; height: 45px; border-radius: 50%; object-fit: cover;" />
        </a>
      </div>
      <nav class="sidebar-nav">
	        @php
	          $sidebarUser = auth()->user();
	          $canManageContent = $sidebarUser->canManageContent();
	          $canManageInbox = $sidebarUser->canManageInbox();
	          $canManageEducation = $sidebarUser->canManageEducation();
	          $canManageTeachers = $sidebarUser->canManageTeachers();
	          $canManageSystem = $sidebarUser->canManageSystem();
		          $adminAvatarUrl = $sidebarUser->avatar_url;
		          $adminAvatarInitial = $sidebarUser->avatar_initial;
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

          @if($canManageTeachers || $canManageEducation)
            <li class="sidebar-section">Ta'lim</li>

            @if($canManageTeachers)
              <li class="nav-item {{ request()->routeIs('teachers.*') ? 'active' : '' }}">
                <a href="{{ route('teachers.index') }}">
                  <span class="icon"><i class="mdi mdi-school-outline"></i></span>
                  <span class="text">Ustozlar</span>
                </a>
              </li>
            @endif

            @if($sidebarUser->canManageSystem())
              <li class="nav-item {{ request()->routeIs('admin.courses.index') ? 'active' : '' }}">
                <a href="{{ route('admin.courses.index') }}">
                  <span class="icon"><i class="mdi mdi-book-open-page-variant-outline"></i></span>
                  <span class="text">Kurslar</span>
                </a>
              </li>

              <li class="nav-item {{ request()->routeIs('admin.courses.requests') ? 'active' : '' }}">
  <a href="{{ route('admin.courses.requests') }}">
    <span class="icon"><i class="mdi mdi-book-open-outline"></i></span>
    <span class="text">Kurs so'rovlari</span>
    @php
      $pendingCoursesCount = \App\Models\Course::where('status', \App\Models\Course::STATUS_PENDING_VERIFICATION)->count();
      $pendingCourseOpenCount = \App\Models\User::query()
          ->whereHas('roleRelation', fn ($r) => $r->where('name', \App\Models\User::ROLE_TEACHER))
          ->where('course_open_request_pending', true)
          ->where('course_open_approved', false)
          ->count();
      $pendingRequestsTotal = $pendingCoursesCount + $pendingCourseOpenCount;
    @endphp
    @if($pendingRequestsTotal > 0)
      <span class="badge rounded-pill bg-danger ms-auto" style="font-size: 0.65rem;">{{ $pendingRequestsTotal }}</span>
    @endif
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
              <li class="nav-item {{ request()->routeIs('profile.exams.*') ? 'active' : '' }}">
                <a href="{{ route('profile.exams.index') }}">
                  <span class="icon"><i class="mdi mdi-pen"></i></span>
                  <span class="text">Mening imtihonlarim</span>
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

            @if($sidebarUser->isSuperAdmin())
              <li class="nav-item {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                <a href="{{ route('admin.settings.index') }}">
                  <span class="icon"><i class="mdi mdi-cog-outline"></i></span>
                  <span class="text">Sozlamalar</span>
                </a>
              </li>
            @endif
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
					                        <div class="image admin-avatar-frame admin-avatar-frame--header {{ $adminAvatarUrl ? 'has-image' : '' }}">
		                              @if($adminAvatarUrl)
				                            <img
				                              src="{{ $adminAvatarUrl }}"
				                              alt=""
				                              class="admin-avatar-img"
				                              onerror="this.parentElement.classList.add('is-broken')"
				                            />
		                              @endif
		                              <span class="admin-user-avatar-fallback">{{ $adminAvatarInitial }}</span>
					                        </div>
	                        <div>
	                          <h6 class="fw-500">{{ auth()->user()->name }}</h6>
	                          <p>{{ auth()->user()->role_label }}</p>
	                        </div>
	                      </div>
	                    </div>
                  </button>
                  <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profile">
		                    <li>
		                      <div class="author-info flex items-center !p-1">
			                        <div class="image admin-avatar-frame {{ $adminAvatarUrl ? 'has-image' : '' }}">
	                                @if($adminAvatarUrl)
			                            <img
			                              src="{{ $adminAvatarUrl }}"
			                              alt=""
			                              class="admin-avatar-img"
			                              onerror="this.parentElement.classList.add('is-broken')"
			                            >
	                                @endif
	                                <span class="admin-user-avatar-fallback">{{ $adminAvatarInitial }}</span>
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
                <a rel="nofollow">
                  Admin
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
    <script src="{{ app_public_asset('panel-assets/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ app_public_asset('panel-assets/js/Chart.min.js') }}"></script>
    <script src="{{ app_public_asset('panel-assets/js/dynamic-pie-chart.js') }}"></script>
    <script src="{{ app_public_asset('panel-assets/js/moment.min.js') }}"></script>
    <script src="{{ app_public_asset('panel-assets/js/fullcalendar.js') }}"></script>
    <script src="{{ app_public_asset('panel-assets/js/jvectormap.min.js') }}"></script>
	    <script src="{{ app_public_asset('panel-assets/js/world-merc.js') }}"></script>
	    <script src="{{ app_public_asset('panel-assets/js/polyfill.js') }}"></script>
	    <script src="{{ app_public_asset('panel-assets/js/main.js') }}"></script>
      <script src="{{ app_public_asset('temp/js/extracted-admin.js') }}?v={{ filemtime(public_path('temp/js/extracted-admin.js')) }}"></script>

    @yield('page_scripts')

    <!-- Admin toast (session flash -> top-right toast) -->

    <div id="admin-toast-container" aria-live="polite" aria-atomic="true"></div>

    @include('components.confirm-modal')
    <script src="{{ app_public_asset('temp/js/confirm-modal.js') }}?v={{ filemtime(public_path('temp/js/confirm-modal.js')) }}"></script>

  </body>
</html>
