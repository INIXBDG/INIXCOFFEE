<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Dashboard KPI</title>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css?family=Nunito:200,300,400,600,700,800,900" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/gh/BlackrockDigital/startbootstrap-sb-admin-2@gh-pages/css/sb-admin-2.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
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
      width: 100px;
      height: 100%;
      background-color: #102B48;
      z-index: 1050;
      transition: left 0.3s ease;
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
    }

    @media (min-width: 992px) {
      .sidebar-desktop {
        display: block;
      }

      .sidebar-mobile {
        display: none;
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
      background-color: #ffffff;
    }

    .nav-link,
    .collapse-item {
      cursor: pointer;
      transition: background-color 0.3s ease, color 0.3s ease;
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

    .cl-blue {
      background-color: #3F51B5;
    }

    .w-blue {
      color: #3F51B5;
    }

    .cl-green {
      background-color: #009688;
    }

    .w-green {
      color: #009688;
    }

    .cl-red {
      background-color: #C62828;
    }

    .w-red {
      color: #C62828;
    }

    .cl-yellow {
      background-color: #FFB300;
    }

    .w-yellow {
      color: #FFB300;
    }

    .cl-grey {
      background-color: #546E7A;
    }

    .w-grey {
      color: #546E7A;
    }
  </style>
</head>

<body id="page-top">

  <div id="wrapper">
    <ul class="navbar-nav sidebar-desktop sidebar sidebar-dark accordion" id="accordionSidebar" style="background-color: #102B48;  box-shadow: 10px 0 0 white">
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
          Penilaian 360
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
      <hr class="sidebar-divider d-none d-md-block">
    </ul>

    <ul class="navbar-nav sidebar-mobile sidebar-dark accordion" id="accordionSidebarMobile" style="box-shadow: 5px 0 10px rgba(0,0,0,0.3); box-shadow: 10px 0 0 white;">
      <div class="d-flex justify-content-between align-items-center p-3">
        <a class="sidebar-brand d-flex align-items-center" href="#" style="text-decoration: none;">
          <div class="sidebar-brand-text mx-3">KPI</div>
        </a>
        <button id="closeSidebarMobile" class="btn btn-sm text-white" style="font-size: 20px; background: transparent; border: none;">
          &times;
        </button>
      </div>
      <hr class="sidebar-divider my-0 mt-2 mb-2">
      <li class="nav-item {{ Route::currentRouteName() == 'berandaKPI.get' ? 'active' : '' }}">
        <a class="nav-link p-1 ml-2" href="{{ route('berandaKPI.get') }}" style="font-size: 25px;">
          <i class="fas fa-fw fa-tachometer-alt"></i>
          <!-- <span>Dashboard</span> -->
        </a>
      </li>
      <!-- <li class="nav-item label-nav-item mt-2 mb-1">
        <label class="nav-link text-white font-weight-bold" style="cursor: default;">
          Penilaian 360
        </label>
      </li> -->
      <hr class="sidebar-divider my-0 mt-2 mb-2">

      <li class="nav-item {{ Route::currentRouteName() == 'ketegoriKPI.get' ? 'active' : '' }}">
        <a class="nav-link p-1 ml-2" href="{{ route('ketegoriKPI.get') }}" style="font-size: 25px;">
          <i class="fas fa-table"></i>
          <!-- <span>Tabel Data</span> -->
        </a>
      </li>
      <li class="nav-item {{ Route::currentRouteName() == 'ketegori.kpi.create' ? 'active' : '' }}">
        <a class="nav-link p-1 ml-2" href="{{ route('ketegori.kpi.create') }}" style="font-size: 25px;">
          <i class="fas fa-plus-circle"></i>
          <!-- <span>Buat Penilaian</span> -->
        </a>
      </li>
    </ul>

    <div id="content-wrapper" class="d-flex flex-column">
      <div id="content">
        <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
          <button id="sidebarToggleMobile" class="btn btn-link d-lg-none rounded-circle mr-3">
            <i class="fa fa-bars"></i>
          </button>
          <ul class="navbar-nav ml-auto">
            <li class="nav-item text-start">
              <img src="{{ asset('icon/logo_e-officeb.svg') }}" alt="" width="40%">
            </li>
            <li class="nav-item">
              <a href="{{ route('home') }}" class="btn text-white cl-red rounded"><i class="fa-solid fa-arrow-right-from-bracket"></i></a>
            </li>
          </ul>
        </nav>
        <div class="container-fluid" style="margin-top: 80px;">
          @yield('contentKPI')
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
  @yield('script')
</body>

</html>