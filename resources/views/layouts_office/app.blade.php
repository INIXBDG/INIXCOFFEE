<!DOCTYPE html>
<html lang="en" class="layout-menu-fixed layout-compact" data-assets-path="{{ asset('assets/') }}"
    data-template="vertical-menu-template-free">

<head>
    <meta charset="utf-8" />
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>INIX - OFFICE</title>

    <!-- Favicon -->
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="manifest" href="/site.webmanifest">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
        href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400&display=swap"
        rel="stylesheet" />

    <!-- Vendor CSS -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/iconify-icons.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/core.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/apex-charts/apex-charts.css') }}" />

    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

    <!-- Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css"
        rel="stylesheet" />

    <!-- Theme CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/demo.css') }}" />

    <!-- Custom Styles -->
    <style>
        :root {
            --menu-width: 260px;
        }

        .layout-menu {
            position: fixed;
            top: 0;
            left: 0;
            width: var(--menu-width);
            height: 100vh;
            z-index: 1050;
            transition: transform 0.3s ease;
            transform: translateX(0);
        }

        .layout-menu-collapsed .layout-menu {
            transform: translateX(calc(-1 * var(--menu-width)));
        }

        @media (max-width: 1199px) {
            .layout-menu {
                transform: translateX(calc(-1 * var(--menu-width)));
            }

            .layout-menu.active {
                transform: translateX(0);
            }

            .layout-overlay.show {
                opacity: 1;
                visibility: visible;
            }

            #layout-menu {
                z-index: 20;
            }
        }

        .menu-inner {
            padding-bottom: 100px;
            /* ruang untuk tombol logout/sticky footer */
            height: calc(100% - 70px);
            /* sesuaikan dengan tinggi app-brand + lainnya */
            overflow-y: auto;
        }

        .sidebar-footer {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 1rem;
            border-top: 1px solid var(--bs-border-color);
            background: var(--bs-body-bg);
        }

        .avatar {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            overflow: hidden;
            border: 2px solid rgba(255, 255, 255, 0.4);
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.12);
        }

        .avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
    </style>
</head>

<body>

    <!-- Layout wrapper -->
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">

            <!-- Menu / Sidebar -->
            <aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme flex-column">
                <div class="app-brand demo py-3 px-4">
                    <a href="{{ route('office.dashboard') }}" class="app-brand-link">
                        <span class="app-brand-logo demo me-2">
                            <!-- Logo di sini (SVG atau img) -->
                        </span>
                        <span class="app-brand-text demo menu-text fw-bold">INIX OFFICE</span>
                    </a>

                    <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-xl-none">
                        <i class="bx bx-chevron-left bx-sm align-middle"></i>
                    </a>
                </div>

                <div class="menu-inner flex-grow-1">
                    <!-- Menu items di sini (dari partial sidebar) -->
                    @include('layouts_office.sidebar')
                </div>

                <div class="sidebar-footer px-4 py-3">
                </div>
            </aside>
            <!-- / Sidebar -->

            <!-- Layout container -->
            <div class="layout-page">
                    <!-- Navbar content (search, user dropdown, dll) -->
                    @include('layouts_office.navbar')
                </nav>
                <!-- / Navbar -->

                <!-- Content wrapper -->
                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        @yield('office_contents')
                    </div>

                    @include('layouts_office.footer')

                    <div class="content-backdrop fade"></div>
                </div>
                <!-- / Content wrapper -->

            </div>
            <!-- / Layout page -->

        </div>

        <!-- Overlay -->
        <div class="layout-overlay layout-menu-toggle"></div>

    </div>
    <!-- / Layout wrapper -->

    <!-- Core JS -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script src="{{ asset('assets/vendor/libs/popper/popper.js') }}"></script>
    <script src="{{ asset('assets/vendor/js/bootstrap.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/apex-charts/apexcharts.js') }}"></script>

    <!-- Vendor JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    <!-- Template JS -->
    <script src="{{ asset('assets/vendor/js/menu.js') }}"></script>
    <script src="{{ asset('assets/js/main.js') }}"></script>

    <!-- Iconify -->
    <script src="https://code.iconify.design/3/3.1.0/iconify.min.js"></script>

    <!-- Mobile menu toggle logic -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const menuToggle = document.querySelectorAll(".layout-menu-toggle");
            const sidebar = document.getElementById("layout-menu");
            const overlay = document.querySelector(".layout-overlay");

            menuToggle.forEach(toggle => {
                toggle.addEventListener("click", function(e) {
                    e.preventDefault();
                    sidebar.classList.toggle("active");
                    overlay.classList.toggle("show");
                });
            });

            overlay.addEventListener("click", function() {
                sidebar.classList.remove("active");
                overlay.classList.remove("show");
            });

            // Tutup menu saat klik link di mobile
            document.querySelectorAll('#layout-menu .menu-link').forEach(link => {
                link.addEventListener('click', () => {
                    if (window.innerWidth < 1200) {
                        sidebar.classList.remove("active");
                        overlay.classList.remove("show");
                    }
                });
            });
        });
    </script>

    <!-- User profile AJAX (tetap) -->
    <script>
        $(document).ready(function() {
            const profileUrl = "{{ route('crm.profile') }}";
            const defaultAvatar = "{{ asset('assets/img/avatars/1.png') }}";

            $.ajax({
                url: profileUrl,
                method: 'GET',
                dataType: 'json',
                success: function(data) {
                    const photo = data.foto || defaultAvatar;
                    const fullName = data.nama_lengkap || data.username || 'User';
                    const role = data.jabatan || 'Staff';

                    $('#userAvatar, #userAvatarDropdown').attr('src', photo);
                    $('#userFullName').text(fullName);
                    $('#userRole').text(role);
                },
                error: function() {
                    $('#userFullName').text('User');
                    $('#userRole').text('Guest');
                }
            });

            $('#logoutButton').on('click', function(e) {
                e.preventDefault();
                $('#logout-form').submit();
            });
        });
    </script>

</body>

</html>
