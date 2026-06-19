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
            --menu-width: 260px;
            --sidebar-width-collapsed: 6.5rem;
            --sidebar-width-expanded: 14rem;
            --transition-speed: 0.3s;
            --shadow-sm: 0 0.125rem 0.25rem rgba(58, 59, 69, 0.08);
            --shadow-md: 0 0.15rem 1.75rem rgba(58, 59, 69, 0.12);
            --shadow-lg: 0 1rem 3rem rgba(58, 59, 69, 0.18);
            --border-radius-lg: 0.75rem;
            --backdrop-blur: blur(10px);
        }

        body {
            font-family: 'Public Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            background: #f8f9fc;
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

        .sidebar-footer {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 1rem;
            border-top: 1px solid rgba(255, 255, 255, 0.2);
            background: rgba(255, 255, 255, 0.1);
        }

        .sidebar .nav-item {
            position: relative;
            margin: 0.25rem 0;
        }

        .sidebar .nav-link {
            padding: 0.75rem 1rem;
            font-weight: 600;
            font-size: 0.85rem;
            color: rgba(255, 255, 255, 0.85);
            border-radius: 0.35rem;
            margin: 0 0.75rem;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .sidebar .nav-link:hover,
        .sidebar .nav-link:focus {
            color: #fff;
            background: rgba(255, 255, 255, 0.15);
            transform: translateX(4px);
        }

        .sidebar .nav-link.active {
            color: #fff;
            background: rgba(255, 255, 255, 0.25);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .sidebar .nav-link i {
            font-size: 0.9rem;
            width: 1.25rem;
            text-align: center;
            transition: transform 0.2s ease;
        }

        .sidebar .nav-link:hover i {
            transform: scale(1.1);
        }

        .sidebar-brand {
            height: 4.375rem;
            padding: 0 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: opacity var(--transition-speed);
        }

        .sidebar-brand-icon {
            font-size: 1.5rem;
            transition: transform 0.2s ease;
        }

        .sidebar-brand-text {
            font-weight: 800;
            font-size: 1.1rem;
            letter-spacing: 0.5px;
            white-space: nowrap;
            color: #fff;
        }

        .layout-menu {
            background: #ffffff9f;
        }

        .layout-page {
            min-height: 100vh;
        }

        .layout-wrapper {
            min-height: 100vh;
        }

        .layout-navbar {
            position: sticky;
            top: 0;
            z-index: 1030;
            width: 100%;
            padding: 12px 24px;
        }

        .layout-navbar.scrolled {
            padding: 0.35rem 1.5rem;
            box-shadow: var(--shadow-md);
        }

        .navbar-search .input-group {
            border-radius: 2rem;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            transition: box-shadow 0.2s ease;
        }

        .navbar-search .input-group:focus-within {
            box-shadow: 0 4px 16px rgba(78, 115, 223, 0.25);
        }

        .navbar-search .form-control {
            border: none;
            padding: 0.5rem 1rem;
            font-size: 0.9rem;
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

        .user-info {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .user-info .user-name {
            font-weight: 600;
            font-size: 0.85rem;
            color: #5a5c69;
        }

        .user-info .user-role {
            font-size: 0.7rem;
            color: #858796;
        }

        .dropdown-list {
            border: none;
            border-radius: var(--border-radius-lg);
            box-shadow: var(--shadow-lg);
            padding: 0.5rem 0;
            min-width: 20rem;
            animation: dropdownSlide 0.2s ease;
        }

        @keyframes dropdownSlide {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .dropdown-list .dropdown-header {
            padding: 0.75rem 1.25rem;
            font-weight: 700;
            font-size: 0.8rem;
            color: #5a5c69;
            border-bottom: 1px solid #e3e6f0;
        }

        .dropdown-list .dropdown-item {
            padding: 0.75rem 1.25rem;
            transition: background 0.15s ease;
            border-radius: 0.35rem;
            margin: 0.25rem 0.5rem;
        }

        .dropdown-list .dropdown-item:hover {
            background: #f8f9fc;
            transform: translateX(4px);
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

            0%,
            100% {
                box-shadow: 0 0 0 0 rgba(231, 74, 59, 0.4);
            }

            50% {
                box-shadow: 0 0 0 6px rgba(231, 74, 59, 0);
            }
        }

        footer.sticky-footer {
            padding: 1rem 0;
            background: rgba(255, 255, 255, 0.9);
            border-top: 1px solid #e3e6f0;
            margin-top: auto;
        }

        footer .copyright {
            font-size: 0.8rem;
            color: #858796;
            font-weight: 500;
        }

        .glass-force {
            background: rgba(255, 255, 255, 0.12) !important;
            backdrop-filter: blur(12px) !important;
            -webkit-backdrop-filter: blur(12px) !important;
            border: 1px solid rgba(255, 255, 255, 0.2) !important;
            border-radius: 12px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15) !important;
        }

        .scroll-to-top {
            position: fixed;
            right: 1.5rem;
            bottom: 1.5rem;
            width: 2.75rem;
            height: 2.75rem;
            background: #4e73df;
            color: #fff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: var(--shadow-md);
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
            background: #3a5fd7;
            transform: translateY(-4px);
            box-shadow: var(--shadow-lg);
        }

        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.9);
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
            border: 3px solid #e3e6f0;
            border-top-color: #4e73df;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        .text-gradient {
            background: linear-gradient(135deg, #4e73df, #224abe);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .card-hover {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .card-hover:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-lg);
        }

        .btn-glow {
            position: relative;
            overflow: hidden;
        }

        .btn-glow::after {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.3) 0%, transparent 70%);
            opacity: 0;
            transition: opacity 0.3s ease;
            pointer-events: none;
        }

        .btn-glow:hover::after {
            opacity: 1;
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

            .sidebar-footer {
                flex-shrink: 0;
            }

            @keyframes fadeIn {
                from {
                    opacity: 0;
                }

                to {
                    opacity: 1;
                }
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

        .sr-only {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            white-space: nowrap;
            border: 0;
        }

        a:focus,
        button:focus,
        input:focus,
        select:focus {
            outline: 2px solid #4e73df;
            outline-offset: 2px;
        }

        @media (prefers-contrast: high) {
            .layout-menu {
                border-right: 2px solid #fff;
            }

            .dropdown-list {
                border: 2px solid #5a5c69;
            }
        }

        #layout-menu.bg-gradient-primary .menu-link {
            color: rgba(160, 160, 160, 0.85) !important;
        }

        #layout-menu.bg-gradient-primary .menu-link:hover,
        #layout-menu.bg-gradient-primary .menu-link:focus {
            color: #535353 !important;
        }

        #layout-menu.bg-gradient-primary .menu-item.active>.menu-link {
            color: #fff !important;
            color: rgba(160, 160, 160, 0.85) !important;
            font-weight: 600;
        }

        #layout-menu.bg-gradient-primary .menu-header-text {
            color: rgba(255, 255, 255, 0.6) !important;
            padding: 1.25rem 1.5rem 0.5rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        #layout-menu.bg-gradient-primary .menu-divider {
            border-color: rgba(255, 255, 255, 0.15) !important;
            margin: 0.75rem 1rem;
        }

        .menu-icon.iconify {
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .sidebar-footer .glass-force {
            background: rgba(255, 255, 255, 0.1) !important;
            border: 1px solid rgba(255, 255, 255, 0.2) !important;
        }

        .layout-menu-collapsed .app-brand-text,
        .layout-menu-collapsed .menu-header-text,
        .layout-menu-collapsed .sidebar-footer p span {
            display: none !important;
        }

        .layout-menu-collapsed .sidebar-footer .glass-force {
            padding: 0.75rem !important;
        }

        .layout-menu-collapsed .sidebar-footer .btn span:not(.iconify) {
            display: none;
        }

        .layout-navbar {
            background-color: rgba(255, 255, 255, 0.501) !important;
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(231, 234, 243, 0.8);
            box-shadow: 0 0.125rem 0.25rem rgba(58, 59, 69, 0.08);
            padding: 0.5rem 1.5rem;
            transition: all 0.3s ease;
        }

        .layout-navbar.scrolled {
            padding: 0.35rem 1.5rem;
            box-shadow: 0 0.15rem 1.75rem rgba(58, 59, 69, 0.12);
        }

        .layout-navbar .form-control.bg-transparent {
            color: #697a8d;
        }

        .layout-navbar .form-control.bg-transparent::placeholder {
            color: #b4bdc6;
        }

        .layout-navbar .form-control.bg-transparent:focus {
            color: #697a8d;
            box-shadow: none;
        }

        .avatar-online::after {
            content: "";
            position: absolute;
            bottom: 0;
            right: 3px;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #71dd37;
            border: 2px solid #fff;
        }

        .dropdown-menu {
            border: none;
            border-radius: 0.75rem;
            box-shadow: 0 0.25rem 1rem rgba(58, 59, 69, 0.18);
            min-width: 14rem;
            animation: dropdownSlide 0.2s ease;
        }

        @keyframes dropdownSlide {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .dropdown-item {
            padding: 0.5rem 1.25rem;
            font-size: 0.9rem;
            color: #697a8d;
            transition: background 0.15s ease, color 0.15s ease;
            border-radius: 0.35rem;
            margin: 0.125rem 0.5rem;
        }

        .dropdown-item:hover,
        .dropdown-item:focus {
            background: #f8f9fc;
            color: #4e73df;
            transform: translateX(2px);
        }

        .dropdown-item.text-danger:hover {
            background: #fff5f5;
            color: #ff3969;
        }

        .dropdown-item i.iconify {
            font-size: 1.1rem;
            display: inline-flex;
            align-items: center;
        }

        .dropdown-divider {
            border-color: #e3e6f0;
            margin: 0.25rem 0;
        }

        .layout-menu-toggle .nav-link {
            color: #697a8d;
            padding: 0.5rem;
            border-radius: 0.375rem;
            transition: background 0.15s ease;
        }

        .layout-menu-toggle .nav-link:hover {
            background: rgba(78, 115, 223, 0.1);
            color: #4e73df;
        }

        @media (max-width: 1199px) {
            .layout-navbar {
                padding: 0.5rem 1rem;
            }

            .navbar-nav-right {
                width: 100%;
                justify-content: space-between;
            }

            .layout-page {
                width: 100%;
                margin-left: 0;
            }

            .layout-menu {
                transform: translateX(-100%);
            }

            .layout-menu.active {
                transform: translateX(0);
            }
        }

        .content-wrapper {
            min-height: calc(100vh - 70px);
            position: relative;
        }

        .container-fluid {
            width: 100%;
        }

        .footer {
            border-top: 1px solid #eaeaea;
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
            <div class="modal-content border-0 shadow-lg glass-force">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold" id="logoutModalLabel">Konfirmasi Logout</h5>
                    <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body py-4">
                    <p class="text-muted mb-0">Apakah Anda yakin ingin mengakhiri sesi saat ini?</p>
                </div>
                <div class="modal-footer border-top-0 pt-0 justify-content-center gap-3">
                    <button class="btn btn-outline-secondary px-4" type="button" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Batal
                    </button>
                    <a class="btn btn-primary px-4 btn-glow" href="{{ route('logout') }}"
                        onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
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
            const body = document.body;

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
                    window.scrollTo({
                        top: 0,
                        behavior: 'smooth'
                    });
                });
            }

            window.showLoading = function() {
                document.getElementById('globalLoading')?.classList.add('active');
            };

            window.hideLoading = function() {
                document.getElementById('globalLoading')?.classList.remove('active');
            };

            document.querySelectorAll('.badge-counter').forEach(badge => {
                badge.addEventListener('mouseenter', function() {
                    this.style.animationPlayState = 'paused';
                });
                badge.addEventListener('mouseleave', function() {
                    this.style.animationPlayState = 'running';
                });
            });

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
