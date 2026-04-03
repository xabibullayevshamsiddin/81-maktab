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
        <ul>
          <li class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <a href="{{ route('dashboard') }}">
              <span class="icon">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path
                    d="M8.74999 18.3333C12.2376 18.3333 15.1364 15.8128 15.7244 12.4941C15.8448 11.8143 15.2737 11.25 14.5833 11.25H9.99999C9.30966 11.25 8.74999 10.6903 8.74999 10V5.41666C8.74999 4.7263 8.18563 4.15512 7.50586 4.27556C4.18711 4.86357 1.66666 7.76243 1.66666 11.25C1.66666 15.162 4.83797 18.3333 8.74999 18.3333Z" />
                  <path
                    d="M17.0833 10C17.7737 10 18.3432 9.43708 18.2408 8.75433C17.7005 5.14918 14.8508 2.29947 11.2457 1.75912C10.5629 1.6568 10 2.2263 10 2.91665V9.16666C10 9.62691 10.3731 10 10.8333 10H17.0833Z" />
                </svg>
              </span>
              <span class="text">Dashboard</span>
            </a>
          </li>



          @if(!auth()->user()->isOnlyModerator() && (auth()->user()->isAdmin() || auth()->user()->isEditor()))
          <li class="nav-item {{ request()->routeIs('posts.*') ? 'active' : '' }}">
            <a href="{{ route('posts.index') }}">
              <span class="icon">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path
                    d="M10.8333 2.50008C10.8333 2.03984 10.4602 1.66675 9.99999 1.66675C9.53975 1.66675 9.16666 2.03984 9.16666 2.50008C9.16666 2.96032 9.53975 3.33341 9.99999 3.33341C10.4602 3.33341 10.8333 2.96032 10.8333 2.50008Z" />
                  <path
                    d="M17.5 5.41673C17.5 7.02756 16.1942 8.33339 14.5833 8.33339C12.9725 8.33339 11.6667 7.02756 11.6667 5.41673C11.6667 3.80589 12.9725 2.50006 14.5833 2.50006C16.1942 2.50006 17.5 3.80589 17.5 5.41673Z" />
                  <path
                    d="M11.4272 2.69637C10.9734 2.56848 10.4947 2.50006 10 2.50006C7.10054 2.50006 4.75003 4.85057 4.75003 7.75006V9.20873C4.75003 9.72814 4.62082 10.2393 4.37404 10.6963L3.36705 12.5611C2.89938 13.4272 3.26806 14.5081 4.16749 14.9078C7.88074 16.5581 12.1193 16.5581 15.8326 14.9078C16.732 14.5081 17.1007 13.4272 16.633 12.5611L15.626 10.6963C15.43 10.3333 15.3081 9.93606 15.2663 9.52773C15.0441 9.56431 14.8159 9.58339 14.5833 9.58339C12.2822 9.58339 10.4167 7.71791 10.4167 5.41673C10.4167 4.37705 10.7975 3.42631 11.4272 2.69637Z" />
                  <path
                    d="M7.48901 17.1925C8.10004 17.8918 8.99841 18.3335 10 18.3335C11.0016 18.3335 11.9 17.8918 12.511 17.1925C10.8482 17.4634 9.15183 17.4634 7.48901 17.1925Z" />
                </svg>
              </span>
              <span class="text">yangiliklar</span>
            </a>
          </li>
          @endif

          @if(auth()->user()->isAdmin())
          <li class="nav-item {{ request()->routeIs('teachers.*') ? 'active' : '' }}">
            <a href="{{ route('teachers.index') }}">
              <span class="icon">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path d="M10 2.5C8.61929 2.5 7.5 3.61929 7.5 5C7.5 6.38071 8.61929 7.5 10 7.5C11.3807 7.5 12.5 6.38071 12.5 5C12.5 3.61929 11.3807 2.5 10 2.5Z" />
                  <path d="M4.16667 15.8333C4.16667 13.302 6.21869 11.25 8.75 11.25H11.25C13.7813 11.25 15.8333 13.302 15.8333 15.8333V16.25C15.8333 16.7102 15.4602 17.0833 15 17.0833H5C4.53976 17.0833 4.16667 16.7102 4.16667 16.25V15.8333Z" />
                </svg>
              </span>
              <span class="text">Ustozlar</span>
            </a>
          </li>
          @endif

          @if(auth()->user()->isAdmin() || auth()->user()->isTeacher())
          <li class="nav-item {{ request()->routeIs('admin.courses.*') ? 'active' : '' }}">
            <a href="{{ route('admin.courses.index') }}">
              <span class="icon">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path d="M3.33333 4.16667C3.33333 3.70643 3.70643 3.33334 4.16667 3.33334H15.8333C16.2936 3.33334 16.6667 3.70643 16.6667 4.16667V15.8333C16.6667 16.2936 16.2936 16.6667 15.8333 16.6667H4.16667C3.70643 16.6667 3.33333 16.2936 3.33333 15.8333V4.16667Z" />
                  <path d="M6.25 7.08334H13.75V8.33334H6.25V7.08334Z" />
                  <path d="M6.25 10H13.75V11.25H6.25V10Z" />
                  <path d="M6.25 12.9167H10.8333V14.1667H6.25V12.9167Z" />
                </svg>
              </span>
              <span class="text">Kurslar</span>
            </a>
          </li>
          @endif
          @if(auth()->user()->isAdmin())
          <li class="nav-item {{ request()->routeIs('admin.course-enrollments.index') ? 'active' : '' }}">
            <a href="{{ route('admin.course-enrollments.index') }}">
              <span class="icon">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path d="M4.16667 3.33334H15.8333C16.7538 3.33334 17.5 4.07953 17.5 5.00001V15C17.5 15.9205 16.7538 16.6667 15.8333 16.6667H4.16667C3.24619 16.6667 2.5 15.9205 2.5 15V5.00001C2.5 4.07953 3.24619 3.33334 4.16667 3.33334Z" />
                  <path d="M5.83333 8.33334H14.1667V9.58334H5.83333V8.33334Z" />
                  <path d="M5.83333 11.25H11.6667V12.5H5.83333V11.25Z" />
                </svg>
              </span>
              <span class="text">Barcha yozilishlar</span>
            </a>
          </li>
          @elseif(auth()->user()->isTeacher())
          <li class="nav-item {{ request()->routeIs('teacher.enrollments.*') ? 'active' : '' }}">
            <a href="{{ route('teacher.enrollments.index') }}">
              <span class="icon">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path d="M4.16667 3.33334H15.8333C16.7538 3.33334 17.5 4.07953 17.5 5.00001V15C17.5 15.9205 16.7538 16.6667 15.8333 16.6667H4.16667C3.24619 16.6667 2.5 15.9205 2.5 15V5.00001C2.5 4.07953 3.24619 3.33334 4.16667 3.33334Z" />
                  <path d="M5.83333 8.33334H14.1667V9.58334H5.83333V8.33334Z" />
                  <path d="M5.83333 11.25H11.6667V12.5H5.83333V11.25Z" />
                </svg>
              </span>
              <span class="text">Kurs arizalari</span>
            </a>
          </li>
          @endif

          @if(!auth()->user()->isOnlyModerator() && (auth()->user()->isAdmin() || auth()->user()->isEditor()))
          <li class="nav-item {{ request()->routeIs('categories.*') ? 'active' : '' }}">
            <a href="{{ route('categories.index') }}">
              <span class="icon">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path d="M2.5 4.16667C2.5 3.24619 3.24619 2.5 4.16667 2.5H8.33333C8.748 2.5 9.14667 2.66473 9.43934 2.9574L10.2093 3.72733C10.502 4.02 10.9007 4.18473 11.3153 4.18473H15.8333C16.7538 4.18473 17.5 4.93093 17.5 5.8514V15.8333C17.5 16.7538 16.7538 17.5 15.8333 17.5H4.16667C3.24619 17.5 2.5 16.7538 2.5 15.8333V4.16667Z" />
                  <path d="M2.5 7.5H17.5V8.75H2.5V7.5Z" />
                </svg>
              </span>
              <span class="text">Kategoriyalar</span>
            </a>
          </li>
          @endif

          @if(auth()->user()->isModerator())
          <li class="nav-item {{ request()->routeIs('admin.comments.*') ? 'active' : '' }}">
            <a href="{{ route('admin.comments.index') }}">
              <span class="icon">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path d="M3.33333 3.33334C3.33333 2.41286 4.07953 1.66667 5 1.66667H8.33333C9.25381 1.66667 10 2.41286 10 3.33334V6.66667C10 7.58715 9.25381 8.33334 8.33333 8.33334H5C4.07953 8.33334 3.33333 7.58715 3.33333 6.66667V3.33334Z" />
                  <path d="M11.6667 3.33334C11.6667 2.41286 12.4129 1.66667 13.3333 1.66667H15C15.9205 1.66667 16.6667 2.41286 16.6667 3.33334V5C16.6667 5.92048 15.9205 6.66667 15 6.66667H13.3333C12.4129 6.66667 11.6667 5.92048 11.6667 5V3.33334Z" />
                  <path d="M3.33333 11.6667C3.33333 10.7462 4.07953 10 5 10H8.33333C9.25381 10 10 10.7462 10 11.6667V15C10 15.9205 9.25381 16.6667 8.33333 16.6667H5C4.07953 16.6667 3.33333 15.9205 3.33333 15V11.6667Z" />
                  <path d="M11.6667 10H16.6667V15C16.6667 15.9205 15.9205 16.6667 15 16.6667H13.3333C12.4129 16.6667 11.6667 15.9205 11.6667 15V10Z" />
                </svg>
              </span>
              <span class="text">Izohlar</span>
            </a>
          </li>
          @endif

          @if(auth()->user()->isAdmin())
          <li class="nav-item {{ request()->routeIs('notification') ? 'active' : '' }}">
            <a href="{{ route('notification') }}">
              <span class="icon">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path
                    d="M10.8333 2.50008C10.8333 2.03984 10.4602 1.66675 9.99999 1.66675C9.53975 1.66675 9.16666 2.03984 9.16666 2.50008C9.16666 2.96032 9.53975 3.33341 9.99999 3.33341C10.4602 3.33341 10.8333 2.96032 10.8333 2.50008Z" />
                  <path
                    d="M17.5 5.41673C17.5 7.02756 16.1942 8.33339 14.5833 8.33339C12.9725 8.33339 11.6667 7.02756 11.6667 5.41673C11.6667 3.80589 12.9725 2.50006 14.5833 2.50006C16.1942 2.50006 17.5 3.80589 17.5 5.41673Z" />
                  <path
                    d="M11.4272 2.69637C10.9734 2.56848 10.4947 2.50006 10 2.50006C7.10054 2.50006 4.75003 4.85057 4.75003 7.75006V9.20873C4.75003 9.72814 4.62082 10.2393 4.37404 10.6963L3.36705 12.5611C2.89938 13.4272 3.26806 14.5081 4.16749 14.9078C7.88074 16.5581 12.1193 16.5581 15.8326 14.9078C16.732 14.5081 17.1007 13.4272 16.633 12.5611L15.626 10.6963C15.43 10.3333 15.3081 9.93606 15.2663 9.52773C15.0441 9.56431 14.8159 9.58339 14.5833 9.58339C12.2822 9.58339 10.4167 7.71791 10.4167 5.41673C10.4167 4.37705 10.7975 3.42631 11.4272 2.69637Z" />
                  <path
                    d="M7.48901 17.1925C8.10004 17.8918 8.99841 18.3335 10 18.3335C11.0016 18.3335 11.9 17.8918 12.511 17.1925C10.8482 17.4634 9.15183 17.4634 7.48901 17.1925Z" />
                </svg>
              </span>
              <span class="text">Notifications</span>
            </a>
          </li>
          @endif

          @if(auth()->user()->isAdmin())
          <li class="nav-item {{ request()->routeIs('user') ? 'active' : '' }}">
            <a href="{{ route('user') }}">
              <span class="icon">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path
                    d="M1.66666 4.16667C1.66666 3.24619 2.41285 2.5 3.33332 2.5H16.6667C17.5872 2.5 18.3333 3.24619 18.3333 4.16667V9.16667C18.3333 10.0872 17.5872 10.8333 16.6667 10.8333H3.33332C2.41285 10.8333 1.66666 10.0872 1.66666 9.16667V4.16667Z" />
                  <path
                    d="M1.875 13.75C1.875 13.4048 2.15483 13.125 2.5 13.125H17.5C17.8452 13.125 18.125 13.4048 18.125 13.75C18.125 14.0952 17.8452 14.375 17.5 14.375H2.5C2.15483 14.375 1.875 14.0952 1.875 13.75Z" />
                  <path
                    d="M2.5 16.875C2.15483 16.875 1.875 17.1548 1.875 17.5C1.875 17.8452 2.15483 18.125 2.5 18.125H17.5C17.8452 18.125 18.125 17.8452 18.125 17.5C18.125 17.1548 17.8452 16.875 17.5 16.875H2.5Z" />
                </svg>
              </span>
              <span class="text">Foydalanuvchilar</span>
            </a>
          </li>
          @endif

          <li class="nav-item {{ request()->routeIs('home') ? 'active' : '' }}">
            <a href="{{ route('home') }}">
              <span class="icon">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path
                    d="M10.8333 2.50008C10.8333 2.03984 10.4602 1.66675 9.99999 1.66675C9.53975 1.66675 9.16666 2.03984 9.16666 2.50008C9.16666 2.96032 9.53975 3.33341 9.99999 3.33341C10.4602 3.33341 10.8333 2.96032 10.8333 2.50008Z" />
                  <path
                    d="M17.5 5.41673C17.5 7.02756 16.1942 8.33339 14.5833 8.33339C12.9725 8.33339 11.6667 7.02756 11.6667 5.41673C11.6667 3.80589 12.9725 2.50006 14.5833 2.50006C16.1942 2.50006 17.5 3.80589 17.5 5.41673Z" />
                  <path
                    d="M11.4272 2.69637C10.9734 2.56848 10.4947 2.50006 10 2.50006C7.10054 2.50006 4.75003 4.85057 4.75003 7.75006V9.20873C4.75003 9.72814 4.62082 10.2393 4.37404 10.6963L3.36705 12.5611C2.89938 13.4272 3.26806 14.5081 4.16749 14.9078C7.88074 16.5581 12.1193 16.5581 15.8326 14.9078C16.732 14.5081 17.1007 13.4272 16.633 12.5611L15.626 10.6963C15.43 10.3333 15.3081 9.93606 15.2663 9.52773C15.0441 9.56431 14.8159 9.58339 14.5833 9.58339C12.2822 9.58339 10.4167 7.71791 10.4167 5.41673C10.4167 4.37705 10.7975 3.42631 11.4272 2.69637Z" />
                  <path
                    d="M7.48901 17.1925C8.10004 17.8918 8.99841 18.3335 10 18.3335C11.0016 18.3335 11.9 17.8918 12.511 17.1925C10.8482 17.4634 9.15183 17.4634 7.48901 17.1925Z" />
                </svg>
              </span>
              <span class="text">saytga qaytish</span>
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
                <!-- notification start -->
                <div class="notification-box ml-15 d-none d-md-flex">
                  <button class="dropdown-toggle" type="button" id="notification" data-bs-toggle="dropdown"
                    aria-expanded="false">
                    <svg width="22" height="22" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg">
                      <path
                        d="M11 20.1667C9.88317 20.1667 8.88718 19.63 8.23901 18.7917H13.761C13.113 19.63 12.1169 20.1667 11 20.1667Z"
                        fill="" />
                      <path
                        d="M10.1157 2.74999C10.1157 2.24374 10.5117 1.83333 11 1.83333C11.4883 1.83333 11.8842 2.24374 11.8842 2.74999V2.82604C14.3932 3.26245 16.3051 5.52474 16.3051 8.24999V14.287C16.3051 14.5301 16.3982 14.7633 16.564 14.9352L18.2029 16.6342C18.4814 16.9229 18.2842 17.4167 17.8903 17.4167H4.10961C3.71574 17.4167 3.5185 16.9229 3.797 16.6342L5.43589 14.9352C5.6017 14.7633 5.69485 14.5301 5.69485 14.287V8.24999C5.69485 5.52474 7.60672 3.26245 10.1157 2.82604V2.74999Z"
                        fill="" />
                    </svg>
                    <span></span>
                  </button>
                  <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="notification">
                    <li>
                      <a href="#0">
                        <div class="image">
                          <img src="{{ asset('admin/images/lead/lead-6.png') }}" alt="" />
                        </div>
                        <div class="content">
                          <h6>
                            John Doe
                            <span class="text-regular">
                              comment on a product.
                            </span>
                          </h6>
                          <p>
                            Lorem ipsum dolor sit amet, consect etur adipiscing
                            elit Vivamus tortor.
                          </p>
                          <span>10 mins ago</span>
                        </div>
                      </a>
                    </li>
                    <li>
                      <a href="#0">
                        <div class="image">
                          <img src="{{ asset('admin/images/lead/lead-1.png') }}" alt="" />
                        </div>
                        <div class="content">
                          <h6>
                            Jonathon
                            <span class="text-regular">
                              like on a product.
                            </span>
                          </h6>
                          <p>
                            Lorem ipsum dolor sit amet, consect etur adipiscing
                            elit Vivamus tortor.
                          </p>
                          <span>10 mins ago</span>
                        </div>
                      </a>
                    </li>
                  </ul>
                </div>
                <!-- notification end -->
                <!-- message start -->
                <div class="header-message-box ml-15 d-none d-md-flex">
                  <button class="dropdown-toggle" type="button" id="message" data-bs-toggle="dropdown"
                    aria-expanded="false">
                    <svg width="22" height="22" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg">
                      <path
                        d="M7.74866 5.97421C7.91444 5.96367 8.08162 5.95833 8.25005 5.95833C12.5532 5.95833 16.0417 9.4468 16.0417 13.75C16.0417 13.9184 16.0364 14.0856 16.0259 14.2514C16.3246 14.138 16.6127 14.003 16.8883 13.8482L19.2306 14.629C19.7858 14.8141 20.3141 14.2858 20.129 13.7306L19.3482 11.3882C19.8694 10.4604 20.1667 9.38996 20.1667 8.25C20.1667 4.70617 17.2939 1.83333 13.75 1.83333C11.0077 1.83333 8.66702 3.55376 7.74866 5.97421Z"
                        fill="" />
                      <path
                        d="M14.6667 13.75C14.6667 17.2938 11.7939 20.1667 8.25004 20.1667C7.11011 20.1667 6.03962 19.8694 5.11182 19.3482L2.76946 20.129C2.21421 20.3141 1.68597 19.7858 1.87105 19.2306L2.65184 16.8882C2.13062 15.9604 1.83338 14.89 1.83338 13.75C1.83338 10.2062 4.70622 7.33333 8.25004 7.33333C11.7939 7.33333 14.6667 10.2062 14.6667 13.75ZM5.95838 13.75C5.95838 13.2437 5.54797 12.8333 5.04171 12.8333C4.53545 12.8333 4.12504 13.2437 4.12504 13.75C4.12504 14.2563 4.53545 14.6667 5.04171 14.6667C5.54797 14.6667 5.95838 14.2563 5.95838 13.75ZM9.16671 13.75C9.16671 13.2437 8.7563 12.8333 8.25004 12.8333C7.74379 12.8333 7.33338 13.2437 7.33338 13.75C7.33338 14.2563 7.74379 14.6667 8.25004 14.6667C8.7563 14.6667 9.16671 14.2563 9.16671 13.75ZM11.4584 14.6667C11.9647 14.6667 12.375 14.2563 12.375 13.75C12.375 13.2437 11.9647 12.8333 11.4584 12.8333C10.9521 12.8333 10.5417 13.2437 10.5417 13.75C10.5417 14.2563 10.9521 14.6667 11.4584 14.6667Z"
                        fill="" />
                    </svg>
                    <span></span>
                  </button>
                  <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="message">
                    <li>
                      <a href="#0">
                        <div class="image">
                          <img src="{{ asset('admin/images/lead/lead-5.png') }}" alt="" />
                        </div>
                        <div class="content">
                          <h6>Jacob Jones</h6>
                          <p>Hey!I can across your profile and ...</p>
                          <span>10 mins ago</span>
                        </div>
                      </a>
                    </li>
                    <li>
                      <a href="#0">
                        <div class="image">
                          <img src="{{ asset('admin/images/lead/lead-3.png') }}" alt="" />
                        </div>
                        <div class="content">
                          <h6>John Doe</h6>
                          <p>Would you mind please checking out</p>
                          <span>12 mins ago</span>
                        </div>
                      </a>
                    </li>
                    <li>
                      <a href="#0">
                        <div class="image">
                          <img src="{{ asset('admin/images/lead/lead-2.png') }}" alt="" />
                        </div>
                        <div class="content">
                          <h6>Anee Lee</h6>
                          <p>Hey! are you available for freelance?</p>
                          <span>1h ago</span>
                        </div>
                      </a>
                    </li>
                  </ul>
                </div>
                <!-- message end -->
                <!-- profile start -->
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
                      <a href="#0">
                        <i class="lni lni-alarm"></i> Notifications
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

        const errorsAny = @json($errors->any());
        const firstError = @json($errors->first());

        function resolveType(defaultType) {
          if (!toastTypeFlash) return defaultType;
          if (toastTypeFlash === 'warning') return 'warning';
          if (toastTypeFlash === 'error') return 'error';
          if (toastTypeFlash === 'success') return 'success';
          return defaultType;
        }

        // Remove old inline alerts to avoid duplicates
        document.querySelectorAll('.alert-box.success-alert, .alert-box.danger-alert').forEach((el) => {
          el.remove();
        });

        if (sessionSuccess) {
          showToast(sessionSuccess, resolveType('success'));
        }
        if (sessionError) {
          showToast(sessionError, resolveType('error'));
        }
        if (errorsAny) {
          showToast(firstError, 'error');
        }
      })();
    </script>

  </body>
</html>
