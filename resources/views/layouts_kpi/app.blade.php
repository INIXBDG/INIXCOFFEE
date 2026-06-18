<!doctype html>

<html lang="en" class="layout-menu-fixed layout-compact" data-assets-path="{{ asset('assets/') }}"
    data-template="vertical-menu-template-free">

<head>
    <meta charset="utf-8" />
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />

    <title>INIXCOFFEE - KPI</title>
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="manifest" href="/site.webmanifest">
    <link rel="mask-icon" href="/safari-pinned-tab.svg" color="#5bbad5">

    <meta name="description" content="" />
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
        href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap"
        rel="stylesheet" />

    <!-- Vendor CSS -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/iconify-icons.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/core.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/apex-charts/apex-charts.css') }}" />

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

    <!-- CSS bawaan Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />

    <!-- Page CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/demo.css') }}" />

    <link rel="stylesheet" href="{{ asset('template_KPI/dist/assets/vendors/mdi/css/materialdesignicons.min.css') }} ">
    <link rel="stylesheet" href="{{ asset('template_KPI/dist/assets/vendors/ti-icons/css/themify-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('template_KPI/dist/assets/vendors/css/vendor.bundle.base.css') }}">
    <link rel="stylesheet" href="{{ asset('template_KPI/dist/assets/vendors/font-awesome/css/font-awesome.min.css') }}">
    <link rel="stylesheet"
        href="{{ asset('template_KPI/dist/assets/vendors/font-awesome/css/font-awesome.min.css') }}" />
    <link rel="stylesheet"
        href="{{ asset('template_KPI/dist/assets/vendors/bootstrap-datepicker/bootstrap-datepicker.min.css') }}">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">

    <!-- Helpers -->
    <script src="{{ asset('assets/vendor/js/helpers.js') }}"></script>
    <script src="{{ asset('assets/js/config.js') }}"></script>
    <style>
        .avatar {
            width: 40px;
            height: 40px;
            overflow: hidden;
            border-radius: 50%;
        }
        .avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
        }
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

        .bubble:nth-child(1) { left: 0; }
        .bubble:nth-child(2) { left: 20px; }
        .bubble:nth-child(3) { left: 40px; }
        .bubble:nth-child(4) { left: 60px; }

        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-30px); }
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        @keyframes merge {
            0% { transform: translateX(0) translateY(0); }
            100% { transform: translateX(calc((100px - var(--targetX)) * 1px)) translateY(-20px); }
        }

        @keyframes split {
            0% { transform: translateX(calc((100px - var(--targetX)) * 1px)) translateY(-20px); }
            100% { transform: translateX(0) translateY(0); }
        }
        .btn-plain {
            all: unset;
            cursor: pointer;
            display: block;
            padding: 0;
        }

        #totalPenilaianChart {
            max-height: 250px;
        }

        .chart-g-blue {
            background-color: linear-gradient(#8F87F1, #C68EFD, #E9A5F1, #FED2E2);
        }

        .loading-spinner {
            width: 60px;
            height: 60px;
            border: 6px solid transparent;
            border-top: 6px solid #a78bfa;
            border-right: 6px solid #38bdf8;
            border-bottom: 6px solid #34d399;
            border-left: 6px solid #facc15;
            border-radius: 50%;
            animation: spin 1.2s linear infinite;
            margin: auto;
        }

        @media only screen and (max-width:800px) {
            .doughnutjs-wrapper {
                width: 100%;
                max-width: 400px;
                height: auto;
            }

            canvas#myChart {
                width: 100% !important;
                height: 100% !important;
            }

            .card-trafic {
                max-height: none;
                height: 300px;
            }
        }

        .card-trafic {
            max-height: 170px;
            overflow-x: hidden;
        }

        @media (max-width: 768px) {
            #select_peringkatPenilaian {
                width: 100% !important;
            }
        }

        #btn_exportPDF_rangking {
            min-width: 50px;
        }

        .card-podium-1 { transform: scale(1.05); }
        .card-podium-2 { transform: scale(0.95); }
        .card-podium-3 { transform: scale(0.9); }

        #contentKPIDivisi::-webkit-scrollbar { height: 6px; }
        #contentKPIDivisi::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 3px; }
        #contentKPIDivisi::-webkit-scrollbar-thumb { background: #c1c1c1; border-radius: 3px; }
        #contentKPIDivisi::-webkit-scrollbar-thumb:hover { background: #a8a8a8; }
        #contentKPIDivisi { -webkit-overflow-scrolling: touch; }

        .progress-vertical {
            display: flex;
            align-items: flex-end;
            border-radius: 10px;
            overflow: hidden;
        }

        .progress-vertical .progress-bar {
            transition: height 0.6s ease;
            border-radius: 10px 10px 0 0;
        }

        .bar-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin: 0 15px;
        }

        .bar-label {
            margin-top: 8px;
            font-size: 14px;
            font-weight: 500;
            text-align: center;
        }

        .bar-value {
            margin-bottom: 6px;
            font-weight: bold;
            color: #444;
        }

        .legend-box {
            display: inline-block;
            width: 15px;
            height: 15px;
            border-radius: 3px;
        }

        .scroll-wrapper {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            padding-bottom: 10px;
        }

        .scroll-wrapper::-webkit-scrollbar { height: 6px; }
        .scroll-wrapper::-webkit-scrollbar-thumb { background: #ccc; border-radius: 10px; }

        .chart-container canvas {
            width: 100% !important;
            height: 400px !important;
        }

        /* Perbaikan Alignment FontAwesome pada Sidebar Sneat */
        .layout-menu .menu-icon.fa-solid {
            width: 1.5rem;
            margin-right: 0.5rem;
            text-align: center;
            vertical-align: middle;
            font-size: 1.15rem;
        }

        /* Perbaikan Tampilan Sub-Menu Sneat */
        .sub-menu {
            margin: 0;
            padding: 0;
            list-style: none;
        }
        
        .sub-menu .menu-link {
            padding-left: 3rem; 
            position: relative;
            font-size: 0.9375rem;
            color: #697a8d;
            transition: all 0.2s ease-in-out;
        }
        
        .sub-menu .menu-link:hover {
            color: #696cff;
            background-color: rgba(105, 108, 255, 0.04);
        }

        /* Pembuatan Bullet Indicator Sub-Menu */
        .sub-menu .menu-link::before {
            content: '';
            position: absolute;
            left: 1.75rem; 
            top: 50%;
            transform: translateY(-50%);
            width: 0.375rem;
            height: 0.375rem;
            background-color: #b1b1b1; 
            border-radius: 50%;
            transition: all 0.2s ease-in-out;
        }

        /* Indikator Bullet Saat Menu Aktif */
        .sub-menu .menu-item.active .menu-link {
            color: #696cff;
            font-weight: 600;
            background-color: rgba(105, 108, 255, 0.08);
        }

        .sub-menu .menu-item.active .menu-link::before {
            background-color: #696cff; 
            box-shadow: 0 0 0 3px rgba(105, 108, 255, 0.16);
        }
        
    </style>
</head>

<body>

    <!-- Layout wrapper -->
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            <!-- Sidebar -->
            @include('layouts_kpi.sidebar')
            <!-- End Sidebar-->

            <!-- Layout container -->
            <div class="layout-page">
                <!-- Navbar -->
                @include('layouts_crm.navbar')
                <!-- / Navbar -->

                <!-- Content Wrapper -->
                <div class="content-wrapper">
                    <!-- Contents -->
                    @yield('kpi_contents')
                    <!-- / Contents -->
                    <!-- Footer -->
                    @include('layouts_kpi.footer')
                    <!-- / Footer -->
                    <div class="content-backdrop fade"></div>
                </div>
                <!-- / Content Wrapper -->
            </div>
            <!-- / Layout container -->
        </div>
        <!-- Overlay -->
        <div class="layout-overlay layout-menu-toggle"></div>
        <!-- / Overlay -->
    </div>
    <!-- / Layout wrapper -->

    <!-- Modal Import Excel -->
    {{-- <div class="modal fade" id="importExcelModal" tabindex="-1" aria-labelledby="importExcelModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="importExcelModalLabel">Import Data Perusahaan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="{{ route('perusahaan.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                <div class="mb-3">
                    <label for="fileExcel" class="form-label">Pilih File Excel</label>
                    <input type="file" class="form-control" id="fileExcel" name="file" accept=".xlsx,.xls,.csv" required>
                </div>
                </div>

                <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">Import</button>
                </div>
            </form>

            </div>
        </div>
    </div>

    <div class="modal fade" id="importExcelModalContact" tabindex="-1" aria-labelledby="importExcelModalContactLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="importExcelModalContactLabel">Import Data Contact</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="{{ route('contact.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                <div class="mb-3">
                    <label for="fileExcel" class="form-label">Pilih File Excel</label>
                    <input type="file" class="form-control" id="fileExcel" name="file" accept=".xlsx,.xls,.csv" required>
                </div>
                </div>

                <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">Import</button>
                </div>
            </form>

            </div>
        </div>
    </div> --}}

    <!-- Core JS -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    <!-- Bootstrap JS -->
    <script src="{{ asset('assets/vendor/libs/popper/popper.js') }}"></script>
    <script src="{{ asset('assets/vendor/js/bootstrap.js') }}"></script>

    <!-- Vendors JS -->
    <script src="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/apex-charts/apexcharts.js') }}"></script>
    <script src="{{ asset('assets/vendor/js/menu.js') }}"></script>

    <!-- Custom JS -->
    <script src="{{ asset('assets/js/main.js') }}"></script>
    <script src="{{ asset('assets/js/dashboards-analytics.js') }}"></script>

    <!-- Iconify JS -->
    <script src="https://code.iconify.design/3/3.1.0/iconify.min.js"></script>

    <!-- GitHub button -->
    <script async defer src="https://buttons.github.io/buttons.js"></script>

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


    <!-- User Profile Ajax -->
    <script>
        $(document).ready(function() {
            var profileUrl = "{{ route('crm.profile') }}";

            $.ajax({
                url: profileUrl,
                method: 'GET',
                dataType: 'json',
                success: function(data) {
                    var defaultAvatar = "{{ asset('assets/img/avatars/1.png') }}";
                    var photo = data.foto || defaultAvatar;
                    var fullName = data.nama_lengkap || data.username || 'John Doe';
                    var role = data.jabatan || 'User';

                    $('#userAvatar').attr('src', photo);
                    $('#userAvatarDropdown').attr('src', photo);
                    $('#userFullName').text(fullName);
                    $('#userRole').text(role);
                },
                error: function(err) {
                    console.error('Gagal mengambil profil user:', err);
                    $('#userFullName').text('Failed to load user');
                    $('#userRole').text('');
                }
            });

            $('#logoutButton').on('click', function(e) {
                e.preventDefault();
                $('#logout-form').submit();
            });
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

            $("#btnMobileSidebar").on("click", function() {
                if (window.innerWidth > 991) {
                    $("#sidebar").toggleClass("sidebar-hidden");
                } else {
                    $("#sidebar").toggleClass("sidebar-open");
                }
            });
        });
                document.addEventListener("DOMContentLoaded", function() {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl)
            })
        });

        const bubbles = document.querySelectorAll('.bubble');
        const loader = document.getElementById('loader');
        const positions = [0, 50, 100, 150];

        function animateBubble(index, animation, duration, delay = 0) {
            const bubble = bubbles[index];
            if (!bubble) return; // Baris ini mencegah manipulasi elemen yang sudah dihapus oleh AJAX
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
</body>

</html>
