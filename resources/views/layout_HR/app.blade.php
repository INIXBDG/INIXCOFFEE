<!DOCTYPE html>
<html lang="id" class="layout-menu-fixed layout-compact" data-assets-path="{{ asset('assets/') }}"
    data-template="vertical-menu-template-free">

<head>
    <meta charset="utf-8" />
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="description" content="INIX HR - Human Resource Management System">
    <meta name="author" content="INIXINDO">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'INIX HR')</title>

    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="manifest" href="/site.webmanifest">

    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
        href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400&display=swap"
        rel="stylesheet" />

    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/iconify-icons.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/core.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/apex-charts/apex-charts.css') }}" />

    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css"
        rel="stylesheet" />

    <link rel="stylesheet" href="{{ asset('assets/css/demo.css') }}" />
    <link href="{{ asset('template_dashboard_HR/vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet">

    <style>
        :root {
            --pri: #4f46e5;
            --pri-light: #eef2ff;
            --pri-dark: #3730a3;
            --success: #059669;
            --success-light: #d1fae5;
            --warning: #d97706;
            --warning-light: #fef3c7;
            --info: #0284c7;
            --info-light: #e0f2fe;
            --danger: #dc2626;
            --danger-light: #fee2e2;
            --gray-50: #f9fafb;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-400: #9ca3af;
            --gray-600: #4b5563;
            --gray-700: #374151;
            --gray-900: #111827;
            --radius: 10px;
            --shadow-sm: 0 1px 3px rgba(0, 0, 0, .08), 0 1px 2px rgba(0, 0, 0, .05);
            --shadow: 0 4px 6px rgba(0, 0, 0, .07), 0 2px 4px rgba(0, 0, 0, .05);
            --shadow-lg: 0 10px 25px rgba(0, 0, 0, .1), 0 4px 10px rgba(0, 0, 0, .07);
            --menu-width: 260px;
            --transition-speed: 0.3s;
        }

        body {
            font-family: 'Public Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            background: #fafbfc;
            -webkit-font-smoothing: antialiased;
        }

        .layout-wrapper {
            min-height: 100vh;
            background-image: url('/css/background inix office-02.svg');
            background-repeat: no-repeat;
            background-position: center;
            background-size: cover;
            background-attachment: fixed;
        }

        .menu-inner {
            padding-bottom: 100px;
            height: calc(100% - 70px);
            flex: 1;
            overflow-y: auto;
            overflow-x: hidden;
        }

        .layout-menu {
            background: #ffffff9f;
        }

        .layout-page {
            min-height: 100vh;
        }

        /* ===== NAVBAR ===== */
        .layout-navbar {
            position: sticky;
            top: 0;
            z-index: 1030;
            width: 100%;
            padding: 0.75rem 1.5rem;
            background-color: rgba(255, 255, 255, 0.85) !important;
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--gray-200);
            box-shadow: var(--shadow-sm);
            transition: all 0.3s ease;
        }

        .layout-navbar.scrolled {
            padding: 0.5rem 1.5rem;
            box-shadow: var(--shadow);
            background-color: rgba(255, 255, 255, 0.95) !important;
        }

        .navbar-search .input-group {
            border-radius: 2rem;
            overflow: hidden;
            box-shadow: var(--shadow-sm);
            transition: box-shadow 0.2s ease;
            border: 1px solid var(--gray-200);
        }

        .navbar-search .input-group:focus-within {
            box-shadow: 0 0 0 3px rgba(79, 70, 229, .12);
            border-color: var(--pri);
        }

        .navbar-search .form-control {
            border: none;
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
        }

        .navbar-search .form-control:focus {
            box-shadow: none;
        }

        /* ===== FIX: Anti Text Decoration untuk Sidebar ===== */
        #layout-menu,
        #layout-menu *,
        .sidebar,
        .sidebar * {
            text-decoration: none !important;
            text-decoration-line: none !important;
            text-underline-offset: 0 !important;
        }

        /* Specific untuk menu links */
        #layout-menu a,
        #layout-menu .menu-link,
        #layout-menu .app-brand-link,
        .sidebar a,
        .sidebar .nav-link {
            text-decoration: none !important;
            border-bottom: none !important;
        }

        /* Hover, focus, active states */
        #layout-menu a:hover,
        #layout-menu a:focus,
        #layout-menu a:active,
        #layout-menu a:visited,
        #layout-menu .menu-link:hover,
        #layout-menu .menu-link:focus,
        #layout-menu .menu-link.active,
        .sidebar a:hover,
        .sidebar a:focus {
            text-decoration: none !important;
            border-bottom: none !important;
        }

        /* Icon elements */
        #layout-menu i,
        #layout-menu .iconify,
        #layout-menu svg,
        .sidebar i,
        .sidebar .iconify {
            text-decoration: none !important;
        }

        /* Text spans */
        #layout-menu span,
        #layout-menu .menu-text,
        .sidebar span {
            text-decoration: none !important;
        }

        /* Active menu item indicator */
        #layout-menu .menu-item.active .menu-link,
        #layout-menu .menu-item.active .menu-link::after {
            text-decoration: none !important;
        }

        /* Dropdown items dalam sidebar */
        #layout-menu .dropdown-item,
        .sidebar .dropdown-item {
            text-decoration: none !important;
        }

        /* ===== AVATAR ===== */
        .avatar {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            overflow: hidden;
            border: 2px solid var(--pri-light);
            box-shadow: var(--shadow-sm);
        }

        .avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .avatar-online::after {
            content: "";
            position: absolute;
            bottom: 0;
            right: 3px;
            width: 9px;
            height: 9px;
            border-radius: 50%;
            background: var(--success);
            border: 2px solid #fff;
            box-shadow: 0 0 0 2px rgba(5, 150, 105, .2);
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .user-info .user-name {
            font-weight: 600;
            font-size: 0.85rem;
            color: var(--gray-700);
        }

        .user-info .user-role {
            font-size: 0.7rem;
            color: var(--gray-400);
        }

        /* ===== DROPDOWN ===== */
        .dropdown-menu {
            border: none;
            border-radius: 12px;
            box-shadow: var(--shadow-lg);
            min-width: 14rem;
            padding: 0.5rem;
            animation: dropdownSlide 0.2s ease;
        }

        @keyframes dropdownSlide {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .dropdown-item {
            padding: 0.6rem 1rem;
            font-size: 0.85rem;
            color: var(--gray-600);
            transition: all 0.15s ease;
            border-radius: 8px;
            margin: 2px 0;
            display: flex;
            align-items: center;
            gap: 0.6rem;
        }

        .dropdown-item:hover,
        .dropdown-item:focus {
            background: var(--pri-light);
            color: var(--pri);
            transform: translateX(2px);
        }

        .dropdown-item.text-danger:hover {
            background: var(--danger-light);
            color: var(--danger);
        }

        .dropdown-item i.iconify {
            font-size: 1.1rem;
            display: inline-flex;
            align-items: center;
        }

        .dropdown-divider {
            border-color: var(--gray-200);
            margin: 0.35rem 0;
        }

        .badge-counter {
            position: absolute;
            top: 0.25rem;
            right: 0.25rem;
            font-size: 0.65rem;
            padding: 0.25rem 0.45rem;
            animation: badgePulse 2s infinite;
            border-radius: 1rem;
        }

        @keyframes badgePulse {
            0%, 100% { box-shadow: 0 0 0 0 rgba(220, 38, 38, 0.4); }
            50% { box-shadow: 0 0 0 6px rgba(220, 38, 38, 0); }
        }

        /* ===== FOOTER ===== */
        footer.sticky-footer,
        .footer {
            padding: 1rem 0;
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(8px);
            border-top: 1px solid var(--gray-200);
            margin-top: auto;
        }

        footer .copyright {
            font-size: 0.8rem;
            color: var(--gray-400);
            font-weight: 500;
        }

        .text-gradient {
            background: linear-gradient(135deg, var(--pri), var(--pri-dark));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* ===== SCROLL TO TOP ===== */
        .scroll-to-top {
            position: fixed;
            right: 1.5rem;
            bottom: 1.5rem;
            width: 2.75rem;
            height: 2.75rem;
            background: var(--pri);
            color: #fff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: var(--shadow);
            transition: all 0.2s ease;
            z-index: 1000;
            opacity: 0;
            visibility: hidden;
            transform: translateY(20px);
        }

        .scroll-to-top.show {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .scroll-to-top:hover {
            background: var(--pri-dark);
            transform: translateY(-4px);
            box-shadow: var(--shadow-lg);
            color: #fff;
        }

        /* ===== LOADING OVERLAY ===== */
        .loading-overlay {
            position: fixed;
            inset: 0;
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(4px);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }

        .loading-overlay.active {
            opacity: 1;
            visibility: visible;
        }

        .spinner {
            width: 3rem;
            height: 3rem;
            border: 3px solid var(--gray-200);
            border-top-color: var(--pri);
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* ===== GLASS MORPHISM ===== */
        .glass-force {
            background: rgba(255, 255, 255, 0.12) !important;
            backdrop-filter: blur(12px) !important;
            -webkit-backdrop-filter: blur(12px) !important;
            border: 1px solid rgba(255, 255, 255, 0.2) !important;
            border-radius: 12px;
            box-shadow: var(--shadow-lg) !important;
        }

        /* ===== FOCUS STATES ===== */
        a:focus,
        button:focus,
        input:focus,
        select:focus {
            outline: 2px solid var(--pri);
            outline-offset: 2px;
        }

        /* ===== RESPONSIVE ===== */
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

            .layout-page {
                margin-left: 0 !important;
            }

            footer.sticky-footer {
                margin-left: 0 !important;
            }

            #layout-menu {
                z-index: 20;
                height: 100vh;
                overflow-y: auto;
                overflow-x: hidden;
            }

            .navbar-search {
                display: none !important;
            }
        }

        @media (max-width: 576px) {
            .layout-wrapper {
                background-image: url('/css/background inix office-02.svg');
                background-repeat: repeat-y;
                overflow-y: auto;
            }
        }

        @media (min-width: 768px) {
            .layout-wrapper {
                background-attachment: fixed;
            }
        }

        /* ===== SCROLLBAR ===== */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #c1c1c1; border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: #a1a1c1; }

        /* ===== MENU TOGGLE ===== */
        .layout-menu-toggle .nav-link {
            color: var(--gray-600);
            padding: 0.5rem;
            border-radius: 8px;
            transition: all 0.15s ease;
        }

        .layout-menu-toggle .nav-link:hover {
            background: var(--pri-light);
            color: var(--pri);
        }
    </style>

    @stack('styles')
</head>

<body>

    <div class="loading-overlay" id="globalLoading">
        <div class="spinner"></div>
    </div>

    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">

            @include('layout_HR.sidebar')

            <div class="layout-page">

                @include('layout_HR.navbar')
                <div class="content-wrapper">
                    <div class="container-fluid py-4 px-4">
                        @yield('content_HR')
                    </div>

                    <footer class="footer bg-white py-3 mt-auto">
                        <div class="container my-auto">
                            <div class="copyright text-center">
                                <span>© {{ date('Y') }} <strong class="text-gradient">INIXINDO</strong> • HR
                                    Management System</span>
                                <span class="mx-2">•</span>
                                <span>✨ Inixindo Juara!</span>
                            </div>
                        </div>
                    </footer>

                    <div class="content-backdrop fade"></div>
                </div>

            </div>

        </div>

        <div class="layout-overlay layout-menu-toggle"></div>

    </div>

    <a class="scroll-to-top" href="#page-top" id="scrollTopBtn">
        <i class="iconify" data-icon="mdi:chevron-up" data-width="24"></i>
    </a>

    <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="logoutModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow-lg" style="border-radius:12px">
                <div class="modal-header border-bottom-0 pb-0" style="background:var(--danger-light);border-radius:12px 12px 0 0;padding:1.1rem 1.5rem">
                    <h5 class="modal-title fw-bold" id="logoutModalLabel" style="color:var(--danger)"><i class="fa-solid fa-right-from-bracket me-2"></i>Konfirmasi Logout</h5>
                    <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body py-4">
                    <p class="text-muted mb-0" style="font-size:.9rem">Apakah Anda yakin ingin mengakhiri sesi saat ini?</p>
                </div>
                <div class="modal-footer border-top-0 pt-0 justify-content-center gap-3">
                    <button class="btn btn-outline-secondary px-4" type="button" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Batal
                    </button>
                    <a class="btn px-4" href="{{ route('logout') }}"
                        onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                        style="background:var(--danger);color:#fff;font-weight:600;border:none;border-radius:8px">
                        <i class="fas fa-sign-out-alt me-2"></i>Logout
                    </a>
                </div>
            </div>
        </div>
    </div>

    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
        @csrf
    </form>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script src="{{ asset('assets/vendor/libs/popper/popper.js') }}"></script>
    <script src="{{ asset('assets/vendor/js/bootstrap.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/apex-charts/apexcharts.js') }}"></script>

    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    <script src="{{ asset('assets/vendor/js/menu.js') }}"></script>
    <script src="{{ asset('assets/js/main.js') }}"></script>

    <script src="{{ asset('template_dashboard_HR/vendor/jquery-easing/jquery.easing.min.js') }}"></script>

    <script src="https://code.iconify.design/3/3.1.0/iconify.min.js"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const menuToggle = document.querySelectorAll(".layout-menu-toggle");
            const sidebar = document.getElementById("layout-menu");
            const overlay = document.querySelector(".layout-overlay");

            const savedState = localStorage.getItem('sidebar-collapsed');
            if (savedState === 'true') {
                document.body.classList.add('layout-menu-collapsed');
            }

            menuToggle.forEach(toggle => {
                toggle.addEventListener("click", function(e) {
                    e.preventDefault();
                    document.body.classList.toggle("layout-menu-collapsed");

                    const isCollapsed = document.body.classList.contains('layout-menu-collapsed');
                    localStorage.setItem('sidebar-collapsed', isCollapsed);

                    if (window.innerWidth < 1200) {
                        sidebar?.classList.toggle("active");
                        overlay?.classList.toggle("show");
                    }
                });
            });

            overlay?.addEventListener("click", function() {
                sidebar?.classList.remove("active");
                overlay?.classList.remove("show");
            });

            document.querySelectorAll('#layout-menu .menu-link').forEach(link => {
                link.addEventListener('click', () => {
                    if (window.innerWidth < 1200) {
                        sidebar?.classList.remove("active");
                        overlay?.classList.remove("show");
                    }
                });
            });

            const topbar = document.querySelector('.layout-navbar');
            const scrollTopBtn = document.getElementById('scrollTopBtn');

            window.addEventListener('scroll', function() {
                if (window.scrollY > 10) {
                    topbar?.classList.add('scrolled');
                } else {
                    topbar?.classList.remove('scrolled');
                }

                if (scrollTopBtn) {
                    if (window.scrollY > 300) {
                        scrollTopBtn.classList.add('show');
                    } else {
                        scrollTopBtn.classList.remove('show');
                    }
                }
            });

            if (scrollTopBtn) {
                scrollTopBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                });
            }

            window.showLoading = function() {
                document.getElementById('globalLoading')?.classList.add('active');
            };

            window.hideLoading = function() {
                document.getElementById('globalLoading')?.classList.remove('active');
            };

            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && window.innerWidth < 1200) {
                    sidebar?.classList.remove("active");
                    overlay?.classList.remove("show");
                    document.body.classList.remove('layout-menu-collapsed');
                }
            });

            const currentPath = window.location.pathname;
            document.querySelectorAll('.sidebar .menu-link, .sidebar .nav-link').forEach(link => {
                if (link.href === currentPath || link.href.startsWith(currentPath)) {
                    link.classList.add('active');
                    const collapse = link.closest('.collapse, .menu-sub');
                    if (collapse) {
                        collapse.classList.add('show');
                        const toggle = document.querySelector(
                            `[data-target="#${collapse.id}"], [href="#${collapse.id}"]`);
                        toggle?.classList.remove('collapsed');
                    }
                }
            });
        });
    </script>

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

                    $('#userAvatar, #userAvatarDropdown, .avatar-img').attr('src', photo);
                    $('#userFullName, .user-name').text(fullName);
                    $('#userRole, .user-role').text(role);
                },
                error: function() {
                    $('#userFullName, .user-name').text('User');
                    $('#userRole, .user-role').text('Guest');
                }
            });

            $('#logoutButton').on('click', function(e) {
                e.preventDefault();
                $('#logout-form').submit();
            });
        });
    </script>

    @stack('scripts')
</body>

</html>