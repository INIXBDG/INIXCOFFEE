<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <title>Database KPI</title>
    <link rel="stylesheet" href="{{ asset('template_KPI/dist/assets/vendors/mdi/css/materialdesignicons.min.css') }} ">
    <link rel="stylesheet" href="{{ asset('template_KPI/dist/assets/vendors/ti-icons/css/themify-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('template_KPI/dist/assets/vendors/css/vendor.bundle.base.css') }}">
    <link rel="stylesheet" href="{{ asset('template_KPI/dist/assets/vendors/font-awesome/css/font-awesome.min.css') }}">
    <link rel="stylesheet"
        href="{{ asset('template_KPI/dist/assets/vendors/font-awesome/css/font-awesome.min.css') }}" />
    <link rel="stylesheet"
        href="{{ asset('template_KPI/dist/assets/vendors/bootstrap-datepicker/bootstrap-datepicker.min.css') }}">
    <link rel="stylesheet" href="{{ asset('template_KPI/dist/assets/css/style.css') }}">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <style>
        .toast {
            position: fixed;
            top: 50px;
            right: 20px;
            background: linear-gradient(90deg, #ff6b6b, #ff8787);
            color: white;
            padding: 15px 20px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
            opacity: 0;
            transform: translateY(-20px);
            transition: opacity 0.5s ease, transform 0.5s ease;
            pointer-events: none;
            z-index: 1055;
        }

        .toast.show {
            opacity: 1;
            transform: translateY(0);
        }

        .sidebar-offcanvas.active {
            left: 0;
        }

        @media screen and (max-width: 991px) {
            .sidebar-offcanvas {
                position: fixed;
                left: -250px;
                transition: left 0.25s ease-in-out;
            }
        }

        .sidebar {
            height: 100vh;
            max-height: 100vh;
            overflow-y: auto;
        }

        .page-body-wrapper {
            display: flex;
            min-height: 100vh;
        }

        .page-body-wrapper {
            display: flex;
            height: 100vh;
            overflow: hidden;
        }

        html,
        body {
            overflow: hidden;
        }

        .sidebar {
            width: 250px;
            transition: all 0.3s ease;
        }

        .sidebar.sidebar-collapsed {
            width: 70px;
        }

        @media screen and (max-width: 991px) {
            .sidebar {
                position: fixed;
                top: 0;
                left: -250px;
                height: 100%;
                z-index: 1030;
            }

            .sidebar.sidebar-open {
                left: 0;
            }

            .main-panel {
                flex-grow: 1;
                width: 100%;
            }
        }


        .main-panel {
            flex-grow: 1;
            height: 100vh;
            overflow-y: auto;
        }

        .sidebar {
            width: 250px;
            transition: all 0.3s ease-in-out;
        }

        .sidebar.sidebar-hidden {
            margin-left: -250px;
        }

        @media screen and (max-width: 991px) {
            .sidebar {
                position: fixed;
                top: 0;
                left: -250px;
                height: 100%;
                z-index: 1030;
            }

            .sidebar.sidebar-open {
                left: 0;
            }
        }

        .loader {
            position: relative;
            width: 100px;
            height: 30px;
        }

        .bubble {
            text-align: center;
            position: absolute;
            width: 10px;
            height: 10px;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.9), rgba(240, 240, 240, 0.7));
            border-radius: 50%;
            bottom: 0;
            transform-origin: center;
        }

        .bubble:nth-child(1) {
            left: 0;
        }

        .bubble:nth-child(2) {
            left: 20px;
        }

        .bubble:nth-child(3) {
            left: 40px;
        }

        .bubble:nth-child(4) {
            left: 60px;
        }

        @keyframes bounce {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-30px);
            }
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        @keyframes merge {
            0% {
                transform: translateX(0) translateY(0);
            }

            100% {
                transform: translateX(calc((100px - var(--targetX)) * 1px)) translateY(-20px);
            }
        }

        @keyframes split {
            0% {
                transform: translateX(calc((100px - var(--targetX)) * 1px)) translateY(-20px);
            }

            100% {
                transform: translateX(0) translateY(0);
            }
        }
    </style>
</head>

<body id="page-top">
    <div class="container-scroller">
        <nav class="navbar default-layout-navbar col-lg-12 col-12 p-0 fixed-top d-flex flex-row">
            <div class="text-center navbar-brand-wrapper d-flex align-items-center justify-content-start">
                <a class="navbar-brand brand-logo" href="{{ route('berandaKPI.get') }}"><img
                        src="{{ asset('template_KPI/dist/assets/images/logo.svg') }}" alt="logo" /></a>
                <a class="navbar-brand brand-logo-mini" href="{{ route('berandaKPI.get') }}"><img
                        src="{{ asset('template_KPI/dist/assets/images/logo-mini.svg') }}" alt="logo" /></a>
            </div>
            <div class="navbar-menu-wrapper d-flex align-items-stretch">
                <button id="btnMobileSidebar" class="navbar-toggler" type="button">
                    <i class="mdi mdi-menu"></i>
                </button>
                <div class="search-field d-none d-md-block">
                    <form class="d-flex align-items-center h-100" action="#">
                        <div class="input-group">
                            <div class="input-group-prepend bg-transparent">
                                <i class="input-group-text border-0 mdi mdi-magnify"></i>
                            </div>
                            <input type="text" class="form-control bg-transparent border-0"
                                placeholder="Search projects">
                        </div>
                    </form>
                </div>
                <ul class="navbar-nav navbar-nav-right">
                    <li class="nav-item nav-profile dropdown">
                        <a class="nav-link dropdown-toggle" id="profileDropdown" href="#"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            <div class="nav-profile-img">
                                <div id="profile_navbar">
                                </div>
                                <span class="availability-status online"></span>
                            </div>
                            <div class="nav-profile-text">
                                <p class="mb-1 text-black">{{ auth()->user()->username }}</p>
                            </div>
                        </a>
                        <div class="dropdown-menu navbar-dropdown" aria-labelledby="profileDropdown">
                            <a class="dropdown-item" href="{{ route('activity.log') }}">
                                <i class="mdi mdi-cached me-2 text-success"></i> Activity Log </a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="{{ route('home') }}">
                                <i class="mdi mdi-logout me-2 text-primary"></i> Signout </a>
                        </div>
                    </li>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link count-indicator dropdown-toggle" id="notificationDropdown" href="#"
                            data-bs-toggle="modal" data-bs-target="#notificationModal">
                            <i class="mdi mdi-bell-outline"></i>
                            @if (auth()->user()->unreadNotifications->count() > 0)
                                <span class="position-absolute translate-middle badge rounded-pill bg-danger"
                                    style="margin-left: 30px;">
                                    {{ auth()->user()->unreadNotifications->count() }}
                                    <span class="visually-hidden">unread notifications</span>
                                </span>
                            @endif
                        </a>
                    </li>
                </ul>
                <button class="navbar-toggler navbar-toggler-right d-lg-none align-self-center" type="button"
                    data-toggle="offcanvas">
                    <span class="mdi mdi-menu"></span>
                </button>
            </div>
        </nav>
        <div class="container-fluid page-body-wrapper">
            <nav class="sidebar sidebar-offcanvas" id="sidebar">
                <ul class="nav">
                    <li class="nav-item nav-profile">
                        <a href="#" class="nav-link">
                            <div class="nav-profile-image">
                                <div id="profile_sidebar">
                                </div>
                                <span class="login-status online"></span>
                            </div>
                            <div class="nav-profile-text d-flex flex-column">
                                <span class="font-weight-bold mb-2">{{ auth()->user()->username }}</span>
                                <span class="text-secondary text-small">{{ auth()->user()->jabatan }}</span>
                            </div>
                            <i class="mdi mdi-bookmark-check text-success nav-profile-badge"></i>
                        </a>
                    </li>
                    <li class="nav-item {{ Request::routeIs('berandaKPI.get') ? 'active-item' : '' }}">
                        <a class="nav-link " href="{{ route('berandaKPI.get') }}">
                            <span class="menu-title">Dashboard</span>
                            <i class="mdi mdi-home menu-icon"></i>
                        </a>
                    </li>
                    @php
                        $auth = Auth()->user()->jabatan;
                    @endphp

                    @if ($auth === 'Koordinator ITSM' || $auth === 'HRD' || $auth === 'Education Manager' || $auth === 'GM' || $auth === 'SPV Sales')
                        <li class="nav-item">
                            <a class="nav-link" style="margin-left: -10px;">
                                <span class="menu-title">KPI</span>
                            </a>
                        </li>
                        <li class="nav-item {{ Request::routeIs('kpi.index') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ route('kpi.index') }}">
                                <span class="menu-title">Target Divisi</span>
                                <i class="fa-solid fa-bullseye menu-icon"></i>
                            </a>
                        </li>

                        <li class="nav-item {{ Request::routeIs('kpi.overview.index') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ route('kpi.overview.index') }}">
                                <span class="menu-title">Overview Departement</span>
                                <i class="fa-solid fa-users-viewfinder menu-icon"></i>
                            </a>
                        </li>

                        <li class="nav-item {{ Request::routeIs('kpi.overview.indexPersonal') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ route('kpi.overview.indexPersonal') }}">
                                <span class="menu-title">Overview Personal</span>
                                <i class="fa-solid fa-users-viewfinder menu-icon"></i>
                            </a>
                        </li>
                    @endif

                    @if ($auth === 'Technical Support' || $auth === 'Tim Digital' || $auth === 'Programmer' || $auth === 'Finance & Accounting')
                        <li class="nav-item {{ Request::routeIs('kpi.overview.indexPersonal') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ route('kpi.overview.indexPersonal') }}">
                                <span class="menu-title">Overview Personal</span>
                                <i class="fa-solid fa-users-viewfinder menu-icon"></i>
                            </a>
                        </li>
                    @endif

                    <li class="nav-item">
                        <a class="nav-link" style="margin-left: -10px;">
                            <span class="menu-title">Penilaian 360°</span>
                        </a>
                    </li>

                    @if ($auth === 'GM' || $auth === 'HRD' || $auth === 'Direktur Utama' || $auth === 'Direktur')
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="collapse" href="#forms" aria-expanded="true"
                                aria-controls="forms">
                                <span class="menu-title">Table Penilaian</span>
                                <i class="menu-arrow"></i>
                                <i class="mdi mdi-table menu-icon"></i>
                            </a>
                            <div class="collapse show" id="forms" style="">
                                <ul class="nav flex-column sub-menu">
                                    <li class="nav-item {{ request('tipe') === 'rutin' ? 'active' : '' }}">
                                        <a class="nav-link"
                                            href="{{ route('ketegoriKPI.get', ['tipe' => 'rutin']) }}">
                                            <span class="menu-title">Rutin</span>
                                        </a>
                                    </li>

                                    <li class="nav-item {{ request('tipe') === 'probation' ? 'active' : '' }}">
                                        <a class="nav-link"
                                            href="{{ route('ketegoriKPI.get', ['tipe' => 'probation']) }}">
                                            <span class="menu-title">Probation</span>
                                        </a>
                                    </li>

                                    <li class="nav-item {{ request('tipe') === 'kontrak' ? 'active' : '' }}">
                                        <a class="nav-link"
                                            href="{{ route('ketegoriKPI.get', ['tipe' => 'kontrak']) }}">
                                            <span class="menu-title">Kontrak</span>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </li>
                        <li class="nav-item {{ Request::routeIs('ketegori.kpi.create') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ route('ketegori.kpi.create') }}">
                                <span class="menu-title">Buat Penilaian</span>
                                <i class="mdi mdi-plus-box menu-icon"></i>
                            </a>
                        </li>
                        <li class="nav-item {{ Request::routeIs('penilaian.form.data') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ route('penilaian.form.data') }}">
                                <span class="menu-title">Data Form</span>
                                <i class="mdi mdi-file-document menu-icon"></i>
                            </a>
                        </li>
                    @endif

                    @php
                        $id_karyawan = Auth()->user()->karyawan_id;
                        $month = \Carbon\Carbon::now()->month;
                        $year = \Carbon\Carbon::now()->year;

                        if ($month >= 1 && $month <= 3) {
                            $Q = 'Q1';
                        } elseif ($month >= 4 && $month <= 6) {
                            $Q = 'Q2';
                        } elseif ($month >= 7 && $month <= 9) {
                            $Q = 'Q3';
                        } else {
                            $Q = 'Q4';
                        }
                    @endphp


                    @if ($auth != 'Direktur Utama' || $auth != 'Direktur')
                        <li class="nav-item {{ Request::is('penilaian360/index*') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ url('/penilaian360/index/' . $id_karyawan) }}">
                                <span class="menu-title">Hasil Penilaian Anda</span>
                                <i class="mdi mdi-file-document menu-icon"></i>
                            </a>
                        </li>

                        <li class="nav-item {{ Request::is('getFormPenilaianUser*') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ url('/getFormPenilaianUser/' . $id_karyawan) }}">
                                <span class="menu-title">Form Penilaian</span>
                                <i class="fa-solid fa-align-justify menu-icon"></i>
                            </a>
                        </li>
                    @endif

                    {{-- <li class="nav-item">
                  <a class="nav-link" style="margin-left: -10px;">
                    <span class="menu-title">Project</span>
                  </a>
                </li>
                @if (auth()->user()->jabatan === 'HRD' || auth()->user()->jabatan === 'GM' || auth()->user()->jabatan === 'Direktur Utama')
                <li class="nav-item {{ Request::routeIs('project.index') ? 'active' : '' }}">
                  <a class="nav-link" href="{{ route('project.index') }}">
                    <span class="menu-title">Tabel Data</span>
                    <i class="fa-solid fa-table menu-icon"></i>
                  </a>
                </li>
                @endif
                <li class="nav-item {{ Request::routeIs('project.control') ? 'active' : '' }}">
                  <a class="nav-link" href="{{ route('project.control') }}">
                    <span class="menu-title">Control Tugas</span>
                    <i class="fa-solid fa-list-check menu-icon"></i>
                  </a>
                </li> --}}
                </ul>
            </nav>
            <div class="modal fade" id="notificationModal" tabindex="-1" aria-labelledby="notificationModalLabel"
                aria-hidden="true">
                <div class="modal-dialog modal-dialog-scrollable" style="max-width: 550px;">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="notificationModalLabel">Alert Pemberitahuan</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            @include('partials.notifications')
                        </div>
                        <div class="modal-footer">
                            @if (auth()->user()->unreadNotifications->count() > 0)
                                <form action="{{ route('notifications.markAllAsRead') }}" method="POST"
                                    class="d-inline">
                                    @csrf
                                    @method('PUT')
                                    <button type="submit" class="btn btn-info btn-sm rounded-pill px-4">
                                        Tandai Semua sebagai Dibaca
                                    </button>
                                </form>
                            @endif
                            <button type="button" class="btn btn-danger btn-sm rounded-pill px-4"
                                data-bs-dismiss="modal">
                                Tutup
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="main-panel">
                <div aria-live="polite" aria-atomic="true" class="position-relative">
                    <div class="toast-container top-0 end-0 p-3"></div>
                </div>

                @yield('contentKPI')
                <footer class="footer mb-3">
                    <div class="d-sm-flex justify-content-center justify-content-sm-between">
                        <span class="text-muted text-center text-sm-left d-block d-sm-inline-block">Database KPI
                            Inixindo Bandung.</span>
                        <span class="float-none float-sm-right d-block mt-1 mt-sm-0 text-center">By ITSM Inixindo <i
                                class="mdi mdi-heart text-danger"></i></span>
                    </div>
                </footer>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="{{ asset('template_KPI/dist/assets/vendors/js/vendor.bundle.base.js') }}"></script>
    <script src="{{ asset('template_KPI/dist/assets/vendors/chart.js/chart.umd.js') }}"></script>
    <script src="{{ asset('template_KPI/dist/assets/vendors/bootstrap-datepicker/bootstrap-datepicker.min.js') }}">
    </script>
    <script src="{{ asset('template_KPI/dist/assets/js/off-canvas.js') }}"></script>
    <script src="{{ asset('template_KPI/dist/assets/js/misc.js') }}"></script>
    <script src="{{ asset('template_KPI/dist/assets/js/settings.js') }}"></script>
    <script src="{{ asset('template_KPI/dist/assets/js/todolist.js') }}"></script>
    <script src="{{ asset('template_KPI/dist/assets/js/jquery.cookie.js') }}"></script>
    <script src="{{ asset('template_KPI/dist/assets/js/dashboard.js') }}"></script>
    <script src="{{ asset('template_KPI/dist/assets/js/desktop-notification.js') }}"></script>
    <script>
        $(document).ready(function() {
            $.ajax({
                url: "{{ route('GetDataProfile.kpi') }}",
                type: 'GET',
                success: function(response) {
                    const data = response.data;
                    const profile_sidebar = $('#profile_sidebar');
                    const profile_navbar = $('#profile_navbar');

                    profile_sidebar.empty();

                    if (data.foto === null) {
                        profile_sidebar.append(`
              <img src="{{ asset('template_KPI/dist/assets/images/screenshots/user-profile.jpg') }}" alt="image" class="img-fluid rounded-circle">
            `);
                    } else {
                        profile_sidebar.append(`
              <img src="{{ asset('assets/img/avatars') }}/${data.foto}" alt="image" class="img-fluid rounded-circle">
            `);
                    }

                    profile_navbar.empty();

                    if (data.foto === null) {
                        profile_navbar.append(`
              <img src="{{ asset('template_KPI/dist/assets/images/screenshots/user-profile.jpg') }}" alt="image" class="img-fluid rounded-circle">
            `);
                    } else {
                        profile_navbar.append(`
              <img src="{{ asset('assets/img/avatars') }}/${data.foto}" alt="image" class="img-fluid rounded-circle">
            `);
                    }
                }
            });
        });

        document.addEventListener("DOMContentLoaded", function() {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl)
            })
        });

        $(document).ready(function() {
            $("#btnMobileSidebar").on("click", function() {
                if (window.innerWidth > 991) {
                    $("#sidebar").toggleClass("sidebar-hidden");
                } else {
                    $("#sidebar").toggleClass("sidebar-open");
                }
            });
        });

        const bubbles = document.querySelectorAll('.bubble');
        const loader = document.getElementById('loader');
        const positions = [0, 50, 100, 150];

        function animateBubble(index, animation, duration, delay = 0) {
            const bubble = bubbles[index];
            bubble.style.animation = 'none';
            void bubble.offsetWidth;
            bubble.style.animation = `${animation} ${duration}ms ease-in-out ${delay}ms forwards`;
        }

        function defaultBounce() {
            bubbles.forEach((b, i) => {
                b.style.animation = `bounce 1200ms infinite ${i * 150}ms`;
            });
        }

        function playRandomBehavior() {
            const behaviors = ['spin', 'merge-split', 'chaos'];
            const choice = behaviors[Math.floor(Math.random() * behaviors.length)];

            if (choice === 'spin') {
                const idx = Math.floor(Math.random() * 4);
                animateBubble(idx, 'spin', 1000);

            } else if (choice === 'merge-split') {
                bubbles.forEach(b => b.style.animation = 'none');

                const centerX = 75;
                bubbles.forEach((b, i) => {
                    b.style.setProperty('--targetX', positions[i]);
                    animateBubble(i, 'merge', 800);
                });

                setTimeout(() => {
                    bubbles.forEach((b, i) => {
                        b.style.setProperty('--targetX', positions[i]);
                        animateBubble(i, 'split', 800);
                    });
                    setTimeout(defaultBounce, 800);
                }, 800);

            } else if (choice === 'chaos') {
                bubbles.forEach((b, i) => {
                    const randDelay = Math.random() * 300;
                    const randDur = 800 + Math.random() * 400;
                    b.style.animation = `bounce ${randDur}ms infinite ${randDelay}ms`;
                });
            }

            const nextDelay = 3000 + Math.random() * 4000;
            setTimeout(playRandomBehavior, nextDelay);
        }
        defaultBounce();

        setTimeout(playRandomBehavior, 2000);
    </script>

    @yield('script')
</body>

</html>
