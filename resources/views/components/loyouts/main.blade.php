@props(['title' => '81-IDUM'])

<!DOCTYPE html>
<html lang="uz">
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
    <link rel="stylesheet" href="{{ asset('temp/css/style.css') }}?v={{ filemtime(public_path('temp/css/style.css')) }}" />
  </head>

  <body>
    <header class="page-header">
      <div class="container">
        <div class="header-main" id="navbar" style="top: 0;">
          <a class="logo" href="{{ route('home') }}" aria-label="81-IDUM bosh sahifa">
            <img
              src="{{ asset('temp/img/photo_2026-02-06_11-05-24-2.jpg') }}"
              alt="81-IDUM logotipi"
            />
          </a>

          <button
            class="menu-toggle"
            id="menu-toggle"
            type="button"
            aria-label="Menyuni ochish"
            aria-expanded="false"
          >
            <i class="fa-solid fa-bars"></i>
          </button>

          <nav id="site-nav">
            <ul>
              <li><a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">Bosh sahifa</a></li>
              <li><a class="nav-link {{ request()->routeIs('about') ? 'active' : '' }}" href="{{ route('about') }}">Maktab haqida</a></li>
              <li><a class="nav-link {{ request()->routeIs('courses') ? 'active' : '' }}" href="{{ route('courses') }}">Kurslar</a></li>
              <li><a class="nav-link {{ request()->routeIs('post') ? 'active' : '' }}" href="{{ route('post') }}">Yangiliklar</a></li>
              <li><a class="nav-link {{ request()->routeIs('teacher') ? 'active' : '' }}" href="{{ route('teacher') }}">Ustozlar</a></li>
              <li><a class="nav-link {{ request()->routeIs('contact') ? 'active' : '' }}" href="{{ route('contact') }}">Aloqa</a></li>
            </ul>
          </nav>

          <div class="login">
            @auth
                 <p style="color: white">{{ auth()->user()->name }}</p>
            @endauth


            @auth
                <a href="{{ route('dashboard') }}" class="btn btn-outline">dashboard</a>

                <form action="{{ route('logout') }}" method="POST" style="display:inline;">
                    @csrf
                    <button type="submit" class="btn btn-outline">Logout</button>
                </form>
                 @else
            <a href="{{ route('login') }}" class="btn btn-outline">Kirish</a>
            <a href="{{ route('register') }}" class="btn">Ro'yxatdan o'tish</a>
              @endauth
          </div>
        </div>
      </div>
    </header>

    {{ $slot }}

    <button
      id="scroll-top"
      class="scroll-top"
      type="button"
      aria-label="Yuqoriga"
    >
      <i class="fa-solid fa-chevron-up"></i>
    </button>

    <footer class="footer">
      <div class="footer-container container">
        <div class="footer-com">
          <img
            src="{{ asset('temp/img/photo_2026-02-06_11-05-24-2.jpg') }}"
            alt="Maktab logotipi"
            class="img2"
          />
          <h3>81-maktab</h3>
          <p>
            81-sonli maktab zamonaviy va sifatli ta'lim, kuchli qadriyatlar
            hamda o'quvchi muvaffaqiyati uchun xizmat qiladi.
          </p>
        </div>

        <div class="footer-col">
          <h4>Tezkor havolalar</h4>
          <ul>
            <li><a href="{{ route('home') }}">Bosh sahifa</a></li>
            <li><a href="{{ route('about') }}">Maktab haqida</a></li>
            <li><a href="{{ route('courses') }}">Kurslar</a></li>
            <li><a href="{{ route('post') }}">Yangiliklar</a></li>
            <li><a href="{{ route('teacher') }}">Ustozlar</a></li>
            <li><a href="{{ route('contact') }}">Aloqa</a></li>
          </ul>
        </div>

        <div class="footer-cop">
          <h4>Aloqa</h4>
          <a
            href="https://yandex.uz/maps/org/51913117189/?ll=69.190318%2C41.306955&z=16"
          >
            <i class="fa-solid fa-location-dot"></i> Toshkent, Maktab No. 81
          </a>
          <p>
            <i class="fa-solid fa-phone"></i>
            <a href="tel:+998711234567">+998 71 123 45 67</a>
          </p>
          <p><i class="fa-solid fa-envelope"></i> info@school81.uz</p>
        </div>
      </div>

      <div class="footer-bottom">
        &copy; <span id="year"></span> 81-sonli maktab. Barcha huquqlar
        himoyalangan.
      </div>
    </footer>

    <script src="{{ asset('temp/js/script.js') }}?v={{ filemtime(public_path('temp/js/script.js')) }}"></script>
  </body>
</html>
