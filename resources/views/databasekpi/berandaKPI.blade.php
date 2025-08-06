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
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.0/css/all.min.css" integrity="sha512-DxV+EoADOkOygM4IR9yXP8Sb2qwgidEmeqAEmDKIOfPRQZOWbXCzLC6vjbZyy0vPisbH2SyW27+ddLVCN+OMzQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <style>
    body {
      overflow-x: hidden;
    }

    #wrapper {
      display: flex;
    }

    .sidebar {
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

    @media (max-width: 768px) {
      .sidebar {
        position: relative;
        width: 100%;
        height: auto;
      }

      #content-wrapper {
        margin-left: 0;
        width: 100%;
      }
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
    <ul class="navbar-nav sidebar sidebar-dark accordion" id="accordionSidebar" style="background-color: #102B48;">
      <a class="sidebar-brand d-flex align-items-center justify-content-center" href="#">
        <div class="sidebar-brand-text mx-3">database kpi</div>
      </a>
      <hr class="sidebar-divider my-0">

      <li class="nav-item mb01">
        <a class="nav-link {{ Request::routeIs('berandaKPI.get') ? 'active' : '' }}" href="{{ route('berandaKPI.get') }}">
          <i class="fas fa-fw fa-tachometer-alt"></i>
          <span>Dashboard</span>
        </a>
      </li>

      <li class="nav-item {{ Request::is('penilaian360*') || Request::routeIs('ketegoriKPI.get', 'ketegori.kpi.create') ? 'active' : '' }}">
        <a class="nav-link {{ Request::is('penilaian360*') || Request::routeIs('ketegoriKPI.get', 'ketegori.kpi.create') ? '' : 'collapsed' }}"
          href="#"
          data-toggle="collapse"
          data-target="#collapseTwo"
          aria-expanded="{{ Request::is('penilaian360*') || Request::routeIs('ketegoriKPI.get', 'ketegori.kpi.create') ? 'true' : 'false' }}"
          aria-controls="collapseTwo">
          <i class="fas fa-fw fa-ruler-combined c-w"></i>
          <span>Penilaian 360</span>
        </a>
        <div id="collapseTwo"
          class="collapse {{ Request::is('penilaian360*') || Request::routeIs('ketegoriKPI.get', 'ketegori.kpi.create') ? 'show' : '' }}"
          aria-labelledby="headingTwo"
          data-parent="#accordionSidebar">
          <div class="bg-white py-2 collapse-inner rounded">
            <h6 class="collapse-header">Menu 360:</h6>
            <a class="collapse-item {{ Route::currentRouteName() == 'ketegoriKPI.get' ? 'active' : '' }}" href="{{ route('ketegoriKPI.get') }}">Tabel Data</a>
            <a class="collapse-item {{ Route::currentRouteName() == 'ketegori.kpi.create' ? 'active' : '' }}" href="{{ route('ketegori.kpi.create') }}">Buat Penilaian</a>
          </div>
        </div>
      </li>


      <hr class="sidebar-divider d-none d-md-block">
    </ul>

    <div id="content-wrapper" class="d-flex flex-column">
      <div id="content">
        <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
          <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
            <i class="fa fa-bars"></i>
          </button>
          <ul class="navbar-nav ml-auto">
            <li class="nav-item text-start">
              <img src="{{ asset('icon/logo_e-officeb.svg') }}" alt="" width="40%">
            </li>
            <li class="nav-item">
              <a href="{{ route('home') }}" class="btn text-white cl-red rounded-pill"><i class="fa-solid fa-arrow-right-from-bracket"></i></a>
            </li>
          </ul>
        </nav>

        <div class="container-fluid" style="margin-top: 80px;">
          <!-- <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Dashboard KPI Karyawan</h1>
          </div>

          <div class="row">
            <div class="col-md-12">
              <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                  <h5 class="card-title font-weight-bold text-primary">Selamat Datang di Sistem Penilaian Kinerja</h5>
                  <p class="mb-0">Gunakan panel navigasi untuk mengakses data KPI karyawan, laporan, dan pengaturan lainnya.</p>
                </div>
              </div>
            </div>
          </div> -->
          @yield('contentKPI')
        </div>
      </div>

      <!-- <footer class="sticky-footer bg-white">
        <div class="container my-auto">
          <div class="text-center my-auto">
            <span>© 2025 HRD - Sistem Penilaian Kinerja</span>
          </div>
        </div>
      </footer> -->
    </div>
  </div>

  <a class="scroll-to-top rounded" href="#page-top">
    <i class="fas fa-angle-up"></i>
  </a>

  <!-- <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script> -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-easing/1.4.1/jquery.easing.min.js"></script>
  <script src="https://cdn.jsdelivr.net/gh/BlackrockDigital/startbootstrap-sb-admin-2@gh-pages/js/sb-admin-2.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.17.1/moment-with-locales.min.js"></script>
  <script type="text/javascript" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
  @yield('script')
</body>

</html>