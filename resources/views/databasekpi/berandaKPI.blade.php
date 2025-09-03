<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Dashboard KPI</title>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css?family=Nunito:200,300,400,600,700,800,900" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/gh/BlackrockDigital/startbootstrap-sb-admin-2@gh-pages/css/sb-admin-2.min.css" rel="stylesheet">
  <script>
    (function() {
      try {
        let savedTheme = localStorage.getItem('theme');

        if (!savedTheme) {
          savedTheme = 'light';
          localStorage.setItem('theme', savedTheme);
        }

        document.documentElement.setAttribute('data-bs-theme', savedTheme);
      } catch (e) {}
    })();
  </script>

  <!-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet"> -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.0/css/all.min.css" integrity="sha512-DxV+EoADOkOygM4IR9yXP8Sb2qwgidEmeqAEmDKIOfPRQZOWbXCzLC6vjbZyy0vPisbH2SyW27+ddLVCN+OMzQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <style>
    body {
      overflow-x: hidden;
    }

    #wrapper {
      display: flex;
    }

    .sidebar-desktop {
      position: fixed;
      top: 0;
      bottom: 0;
      left: 0;
      width: 200px;
      height: 100vh;
      z-index: 1000;
    }

    #content-wrapper {
      margin-left: 220px;
      width: calc(100% - 220px);
    }

    .topbar {
      position: fixed;
      top: 0;
      right: 0;
      left: 0;
      z-index: 3;
    }

    .sidebar-mobile {
      position: fixed;
      top: 0;
      left: -220px;
      width: 150px;
      height: 100%;
      background-color: #102B48;
      z-index: 1050;
      transition: left .3s ease;
    }

    .sidebar-mobile.active {
      left: 0;
    }

    @media (max-width: 992px) {
      .sidebar-desktop {
        display: none;
      }

      .sidebar-mobile {
        display: block;
      }

      #content-wrapper {
        margin-left: 0;
        width: 100%;
      }

      .logo-navbar {
        width: 10rem;
      }
    }

    @media (min-width: 992px) {
      .sidebar-desktop {
        display: block;
      }

      .sidebar-mobile {
        display: none;
      }

      .logo-navbar {
        margin-left: 15rem;
      }
    }

    .sidebar-dark .nav-link:not(.active),
    .sidebar-dark .collapse-item:not(.active) {
      color: rgba(255, 255, 255, 0.8);
    }

    .nav-item.active>.nav-link,
    .nav-link.active {
      background-color: white;
      border-radius: 50px 0 0 50px;
      color: black !important;
    }

    .collapse-item.active {
      background-color: white;
      border-radius: 50px 0 0 50px;
      color: black !important;
    }

    .nav-item.active>.nav-link:hover,
    .nav-link.active:hover,
    .collapse-item.active:hover {
      color: black !important;
      background-color: #fff;
    }

    .nav-link,
    .collapse-item {
      cursor: pointer;
      transition: background-color .3s ease, color .3s ease;
    }

    .nav-item.active>.nav-link i,
    .nav-link.active i,
    .collapse-item.active i {
      color: black !important;
    }

    .nav-link[style*="cursor: default;"] {
      cursor: default !important;
      color: rgba(255, 255, 255, 0.85);
      font-weight: 600;
      padding-left: 1.5rem;
      pointer-events: none;
    }

    .label-nav-item {
      margin-bottom: -20px;
    }

    .nav-item.active-mobile>.nav-link {
      position: relative;
      background-color: white;
      color: black !important;
      z-index: 1;
      font-weight: bold;
      border-radius: 0;
      overflow: visible;
      border-top-left-radius: 50px;
      border-bottom-left-radius: 50px;
    }

    .nav-item.active>.nav-link {
      position: relative;
      background-color: white;
      color: black !important;
      z-index: 1;
      font-weight: bold;
      border-radius: 0;
      overflow: visible;
      border-top-left-radius: 50px;
      border-bottom-left-radius: 50px;
    }

    .nav-item.active>.nav-link::before {
      content: "";
      position: absolute;
      top: -20px;
      right: 0;
      width: 20px;
      height: 20px;
      background-color: #102B48;
      border-radius: 50%;
      box-shadow: 8px 8px 0 white;
    }

    .nav-item.active>.nav-link::after {
      content: "";
      position: absolute;
      bottom: -20px;
      right: 0;
      width: 20px;
      height: 20px;
      background-color: #102B48;
      border-radius: 50%;
      box-shadow: 8px -8px 0 white;
    }

    [data-bs-theme="dark"] .sidebar-desktop {
      background-color: #102B48;
      box-shadow: 10px 0 0 #343a40;
    }

    [data-bs-theme="dark"] .nav-item.active>.nav-link,
    [data-bs-theme="dark"] .collapse-item.active {
      color: #EEEEEE !important;
    }

    [data-bs-theme="dark"] .nav-item.active>.nav-link,
    [data-bs-theme="dark"] .collapse-item.active {
      background-color: #343a40 !important;
      color: #fff !important;
    }

    [data-bs-theme="dark"] .nav-item.active>.nav-link::before {
      background-color: #1E1E1E;
      box-shadow: 8px 8px 0 #343a40;
    }

    [data-bs-theme="dark"] .nav-item.active>.nav-link::after {
      background-color: #1E1E1E;
      box-shadow: 8px -8px 0 #343a40;
    }

    [data-bs-theme="light"] .nav-item.active>.nav-link::before {
      background-color: #F8F9FA;
      box-shadow: 8px 8px 0 #E9ECEF;
    }

    [data-bs-theme="light"] .nav-item.active>.nav-link::after {
      background-color: #F8F9FA;
      box-shadow: 8px -8px 0 #E9ECEF;
    }

    [data-bs-theme="light"] .sidebar-desktop {
      background-color: #102B48;
      box-shadow: 10px 0 0 #E9ECEF;
    }

    [data-bs-theme="auto"] .sidebar-desktop {
      box-shadow: 10px 0 0 #ffffff;
    }

    [data-bs-theme="light"] .nav-item>.nav-link,
    [data-bs-theme="light"] .nav-item>.nav-link i,
    [data-bs-theme="light"] .sidebar-brand {
      color: black !important;
    }

    [data-bs-theme="light"] .nav-item>.nav-link * {
      color: black !important;
    }

    [data-bs-theme="dark"] .sidebar-desktop,
    [data-bs-theme="dark"] .sidebar-mobile {
      background-color: #1e1e1e !important;
    }

    [data-bs-theme="light"] .sidebar-desktop,
    [data-bs-theme="light"] .sidebar-mobile {
      background-color: #f8f9fa !important;
    }

    [data-bs-theme="auto"] .sidebar-desktop,
    [data-bs-theme="auto"] .sidebar-mobile {
      background-color: #102B48 !important;
    }

    [data-bs-theme="dark"] .sidebar-mobile {
      box-shadow: 10px 0 0 #343a40;
    }

    [data-bs-theme="light"] .sidebar-mobile {
      box-shadow: 10px 0 0 #E9ECEF;
    }

    .cl-blue {
      background-color: #2E86FC;
      transition: background-color 0.3s ease;
    }

    .cl-blue:hover {
      background-color: #1c6fe0;
    }

    .cl-green {
      background-color: #28A745;
      transition: background-color 0.3s ease;
    }

    .cl-green:hover {
      background-color: #218838;
    }

    .cl-red {
      background-color: #E74C3C;
      transition: background-color 0.3s ease;
    }

    .cl-red:hover {
      background-color: #c0392b;
    }

    .cl-yellow {
      background-color: #FFC107;
      transition: background-color 0.3s ease;
    }

    .cl-yellow:hover {
      background-color: #e0a800;
    }

    .cl-grey {
      background-color: #6C757D;
      transition: background-color 0.3s ease;
    }

    .cl-grey:hover {
      background-color: #5a6268;
    }

    .w-blue {
      color: #2E86FC;
      transition: color 0.3s ease;
    }

    .w-blue:hover {
      color: #1c6fe0;
    }

    .w-green {
      color: #28A745;
      transition: color 0.3s ease;
    }

    .w-green:hover {
      color: #218838;
    }

    .w-red {
      color: #E74C3C;
      transition: color 0.3s ease;
    }

    .w-red:hover {
      color: #c0392b;
    }

    .w-yellow {
      color: #FFC107;
      transition: color 0.3s ease;
    }

    .w-yellow:hover {
      color: #e0a800;
    }

    .w-grey {
      color: #6C757D;
      transition: color 0.3s ease;
    }

    .w-grey:hover {
      color: #5a6268;
    }

    .bg-theme {
      background-color: var(--bs-body-bg) !important;
      color: var(--bs-body-color) !important;
    }
  </style>
</head>

<body id="page-top">
  <div id="wrapper">
    <ul class="navbar-nav sidebar-desktop sidebar sidebar-dark accordion" id="accordionSidebar">
      <a class="sidebar-brand d-flex align-items-center justify-content-center" href="#">
        <div class="sidebar-brand-text mx-3">database kpi</div>
      </a>
      <hr class="sidebar-divider my-0">
      <li class="nav-item {{ Request::routeIs('berandaKPI.get') ? 'active' : '' }}">
        <a class="nav-link {{ Request::routeIs('berandaKPI.get') ? 'active' : '' }}" href="{{ route('berandaKPI.get') }}">
          <i class="fas fa-fw fa-tachometer-alt"></i>
          <span>Dashboard</span>
        </a>
      </li>
      @php
      use Illuminate\Support\Str;
      $penilaian360Routes = ['penilaian360*', 'ketegoriKPI.get', 'ketegori.kpi.create'];
      $isPenilaian360Active = collect($penilaian360Routes)->contains(function ($pattern) {
      if (Str::contains($pattern, '*')) {
      return Request::is($pattern);
      }
      return Route::currentRouteName() === $pattern;
      });
      @endphp
      <li class="nav-item label-nav-item">
        <label class="nav-link text-white font-weight-bold" style="cursor: default;">
          Penilaian 360°
        </label>
      </li>
      <li class="nav-item {{ Route::currentRouteName() == 'ketegoriKPI.get' ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('ketegoriKPI.get') }}">
          <i class="fas fa-table"></i>
          <span>Tabel Data</span>
        </a>
      </li>
      <li class="nav-item {{ Route::currentRouteName() == 'ketegori.kpi.create' ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('ketegori.kpi.create') }}">
          <i class="fas fa-plus-circle"></i>
          <span>Buat Penilaian</span>
        </a>
      </li>
      <li class="nav-item {{ Route::currentRouteName() == 'penilaian.form.data' ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('penilaian.form.data') }}">
          <i class="fa-solid fa-table-list"></i>
          <span>Data Form</span>
        </a>
      </li>
      <hr class="sidebar-divider d-none d-md-block">
    </ul>
    <ul class="navbar-nav sidebar-mobile sidebar-dark accordion" id="accordionSidebarMobile">
      <div class="d-flex justify-content-between align-items-center p-3">
        <a class="sidebar-brand d-flex align-items-left" href="#" style="text-decoration: none; font-size : 25px;">
          <div class="sidebar-brand-text mx-3">KPI</div>
        </a>
        <button id="closeSidebarMobile" class="btn btn-sm text-danger" style="font-size: 20px; background: transparent; border: none;">
          &times;
        </button>
      </div>
      <hr class="sidebar-divider my-0 mt-2 mb-2 bg-theme">
      <li class="nav-item {{ Route::currentRouteName() == 'berandaKPI.get' ? 'active' : '' }}">
        <a class="nav-link p-3 ml-2" href="{{ route('berandaKPI.get') }}" style="font-size: 12px;">
          <i class="fas fa-fw fa-tachometer-alt"></i>
          <span>Dashboard</span>
        </a>
      </li>
      <hr class="sidebar-divider my-0 mt-2 mb-2">
      <li class="nav-item {{ Route::currentRouteName() == 'ketegoriKPI.get' ? 'active' : '' }}">
        <a class="nav-link p-3 ml-2" href="{{ route('ketegoriKPI.get') }}" style="font-size: 12px;">
          <i class="fas fa-table"></i>
          <span>Tabel Data</span>
        </a>
      </li>
      <li class="nav-item {{ Route::currentRouteName() == 'ketegori.kpi.create' ? 'active' : '' }}">
        <a class="nav-link p-3 ml-2" href="{{ route('ketegori.kpi.create') }}" style="font-size: 12px;">
          <i class="fas fa-plus-circle"></i>
          <span>Buat Penilaian</span>
        </a>
      </li>
      <li class="nav-item {{ Route::currentRouteName() == 'penilaian.form.data' ? 'active' : '' }}">
        <a class="nav-link p-3 ml-2" href="{{ route('penilaian.form.data') }}" style="font-size: 12px;">
          <i class="fa-solid fa-table-list"></i>
          <span>Data Form</span>
        </a>
      </li>
    </ul>
    <div id="content-wrapper" class="d-flex flex-column">
      <div id="content">
        <nav class="navbar navbar-expand topbar mb-4 static-top shadow px-3 bg-theme">
          <button id="sidebarToggleMobile" class="btn btn-link d-lg-none rounded-circle me-3">
            <i class="fa fa-bars"></i>
          </button>
          <a class="navbar-brand d-flex align-items-center" href="#">
            <img src="logo_original.png" alt="Logo" class="logo-navbar" style="height: 40px;">
          </a>
          <ul class="navbar-nav ms-auto align-items-center">
            <li class="nav-item dropdown me-2">
              <button class="btn btn-link nav-link dropdown-toggle d-flex align-items-center" id="themeDropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fa-solid fa-moon theme-icon-active"></i>
              </button>
              <ul class="dropdown-menu dropdown-menu-end">
                <li>
                  <button type="button" class="dropdown-item d-flex align-items-center" data-bs-theme-value="light">
                    <i class="fa-solid fa-sun me-2"></i> Light
                    <i class="fa-solid fa-check ms-auto d-none"></i>
                  </button>
                </li>
                <li>
                  <button type="button" class="dropdown-item d-flex align-items-center" data-bs-theme-value="dark">
                    <i class="fa-solid fa-moon me-2"></i> Dark
                    <i class="fa-solid fa-check ms-auto d-none"></i>
                  </button>
                </li>
                <li>
                  <button type="button" class="dropdown-item d-flex align-items-center" data-bs-theme-value="auto">
                    <i class="fa-solid fa-circle-half-stroke me-2"></i> Auto
                    <i class="fa-solid fa-check ms-auto d-none"></i>
                  </button>
                </li>
              </ul>
            </li>
            <li class="nav-item">
              <a href="{{ route('home') }}" class="btn text-white" style="background-color: #d9534f;">
                <i class="fa-solid fa-arrow-right-from-bracket"></i>
              </a>
            </li>
          </ul>
        </nav>
        <div class="container-fluid bg-theme" style="margin-top: 40px;">
          <div class="pt-4 pb-4 mt-4">
            @yield('contentKPI')
          </div>
        </div>
      </div>
    </div>
  </div>
  <a class="scroll-to-top rounded" href="#page-top">
    <i class="fas fa-angle-up"></i>
  </a>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-easing/1.4.1/jquery.easing.min.js"></script>
  <script src="https://cdn.jsdelivr.net/gh/BlackrockDigital/startbootstrap-sb-admin-2@gh-pages/js/sb-admin-2.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.17.1/moment-with-locales.min.js"></script>
  <script type="text/javascript" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    $(document).ready(function() {
      $('#sidebarToggleMobile').on('click', function() {
        $('.sidebar-mobile').addClass('active');
      });
      $('#closeSidebarMobile').on('click', function() {
        $('.sidebar-mobile').removeClass('active');
      });
    });
  </script>
  <script>
    const themeButtons = document.querySelectorAll('[data-bs-theme-value]');
    const activeIcon = document.querySelector('#themeDropdown .theme-icon-active');
    const logoNavbar = document.querySelector('.logo-navbar');

    function applyThemeStyles(theme) {
      const sidebarDesktop = document.querySelector('.sidebar-desktop');
      const sidebarMobile = document.querySelector('.sidebar-mobile');
      const activeLinks = document.querySelectorAll('.nav-item.active > .nav-link, .nav-link.active');

      if (theme === 'auto') {
        sidebarDesktop.style.backgroundColor = '#102B48';
        sidebarMobile.style.backgroundColor = '#102B48';
        activeLinks.forEach(link => {
          link.style.backgroundColor = 'white';
          link.style.color = 'black';
        });
        logoNavbar.src = 'logo_original.png';
      }
      if (theme === 'light') {
        sidebarDesktop.style.backgroundColor = '#f8f9fa';
        sidebarMobile.style.backgroundColor = '#f8f9fa';
        activeLinks.forEach(link => {
          link.style.backgroundColor = '#e9ecef';
          link.style.color = '#000';
        });
        logoNavbar.src = "{{ asset('icon/logo_e-officeb.svg') }}";
      }
      if (theme === 'dark') {
        sidebarDesktop.style.backgroundColor = '#1e1e1e';
        sidebarMobile.style.backgroundColor = '#1e1e1e';
        activeLinks.forEach(link => {
          link.style.backgroundColor = '#333';
          link.style.color = '#fff';
        });
        logoNavbar.src = "{{ asset('icon/logo_e-officew.svg') }}";
      }
      if (theme === 'auto') {
        logoNavbar.src = "{{ asset('icon/logo_e-officeb.svg') }}";
      }
    }

    function setTheme(theme, save = true) {
      document.documentElement.setAttribute('data-bs-theme', theme);
      themeButtons.forEach(btn => {
        btn.classList.remove('active');
        btn.querySelector('.fa-check').classList.add('d-none');
      });
      const activeBtn = document.querySelector(`[data-bs-theme-value="${theme}"]`);
      if (activeBtn) {
        activeBtn.classList.add('active');
        activeBtn.querySelector('.fa-check').classList.remove('d-none');
        activeIcon.className = activeBtn.querySelector('i').className + ' theme-icon-active';
      }
      applyThemeStyles(theme);
      if (save) {
        localStorage.setItem('theme', theme);
      }
    }

    const savedTheme = localStorage.getItem('theme') || 'auto';
    setTheme(savedTheme, false);

    themeButtons.forEach(button => {
      button.addEventListener('click', () => {
        const theme = button.getAttribute('data-bs-theme-value');
        setTheme(theme);
      });
    });
  </script>
  @yield('script')
</body>

</html>