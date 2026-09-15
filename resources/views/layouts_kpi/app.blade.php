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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"
        integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@mdi/font/css/materialdesignicons.min.css">

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

    <!-- CSS bawaan Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css"
        rel="stylesheet" />

    <!-- Page CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/demo.css') }}" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">

    <!-- Helpers -->
    <script src="{{ asset('assets/vendor/js/helpers.js') }}"></script>
    <script src="{{ asset('assets/js/config.js') }}"></script>
    <link rel="stylesheet" href="{{ asset('assets/vendor/vendorStyle/kpistyle.css') }}">
</head>

<body>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    @if (app()->environment('staging'))
        <div aria-hidden="true" style="position: fixed; top: 50%; left: 50%; z-index: 999998; color: rgba(220, 38, 38, 0.18); font-size: clamp(4rem, 12vw, 10rem); font-weight: 800; letter-spacing: 0.2em; pointer-events: none; transform: translate(-50%, -50%) rotate(-25deg); user-select: none; white-space: nowrap;">STAGING</div>
    @endif

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

        <!-- Notification Modal -->
    <div id="app">
        <div class="modal fade" id="notificationModal" tabindex="-1" aria-labelledby="notificationModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-scrollable" style="max-width: 550px;"> {{-- default 500-600px --}}
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="notificationModalLabel">Alert Pemberitahuan</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        @include('partials.notifications')
                    </div>
                    <div class="modal-footer">
                        @if(auth()->check() && auth()->user()->unreadNotifications->count() > 0)
                        <form action="{{ route('notifications.markAllAsRead') }}" method="POST" class="d-inline">
                            @csrf
                            @method('PUT')
                            <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill px-4">
                                Tandai Semua sebagai Dibaca
                            </button>
                        </form>
                        @endif
                        <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-4" data-bs-dismiss="modal">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Core JS -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <!-- DataTables JS -->
    <script defer src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script defer src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    <!-- Bootstrap JS -->
    <script defer src="{{ asset('assets/vendor/libs/popper/popper.js') }}"></script>
    <script defer src="{{ asset('assets/vendor/js/bootstrap.js') }}"></script>

    <!-- Vendors JS -->
    <script defer src="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js') }}"></script>
    <script defer src="{{ asset('assets/vendor/libs/apex-charts/apexcharts.js') }}"></script>
    <script defer src="{{ asset('assets/vendor/js/menu.js') }}"></script>

    <!-- Custom JS -->
    <script defer src="{{ asset('assets/js/main.js') }}"></script>
    <script defer src="{{ asset('assets/js/dashboards-analytics.js') }}"></script>

    <!-- Iconify JS -->
    <script defer src="https://code.iconify.design/3/3.1.0/iconify.min.js"></script>

    <!-- GitHub button -->
    <script async defer src="https://buttons.github.io/buttons.js"></script>

    <script defer src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script defer src="{{ asset('js/global-validator.js') }}"></script>

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

    <script>
        (function() {
            'use strict';

            function initDropdowns() {
                if (typeof bootstrap === 'undefined' || !bootstrap.Dropdown) return;
                document.querySelectorAll('[data-bs-toggle="dropdown"]').forEach(function(el) {
                    var existing = bootstrap.Dropdown.getInstance(el);
                    if (existing) existing.dispose();
                    new bootstrap.Dropdown(el, {
                        popperConfig: function(defaultConfig) {
                            return Object.assign({}, defaultConfig, {
                                strategy: 'fixed'
                            });
                        }
                    });
                });
            }

            function initModals() {
                if (typeof bootstrap === 'undefined' || !bootstrap.Modal) return;
                document.querySelectorAll('.modal').forEach(function(el) {
                    var existing = bootstrap.Modal.getInstance(el);
                    if (existing) existing.dispose();
                    new bootstrap.Modal(el, {
                        backdrop: true,
                        keyboard: true,
                        focus: true
                    });
                });
                document.addEventListener('hidden.bs.modal', function() {
                    document.body.style.overflow = '';
                    document.body.style.paddingRight = '';
                    document.querySelectorAll('.modal-backdrop').forEach(function(bd) {
                        bd.remove();
                    });
                    document.body.classList.remove('modal-open');
                });
            }

            function patchJQueryPlugins() {
                if (typeof $ === 'undefined' || typeof bootstrap === 'undefined') return;

                // Patch $.fn.modal → Bootstrap 5
                $.fn.modal = function(option, relatedTarget) {
                    return this.each(function() {
                        var instance = bootstrap.Modal.getOrCreateInstance(this);
                        if (typeof option === 'string' && typeof instance[option] === 'function') {
                            instance[option](relatedTarget);
                        }
                    });
                };

                // Patch $.fn.dropdown → Bootstrap 5
                $.fn.dropdown = function(option) {
                    return this.each(function() {
                        var instance = bootstrap.Dropdown.getOrCreateInstance(this);
                        if (typeof option === 'string' && typeof instance[option] === 'function') {
                            instance[option]();
                        }
                    });
                };

                // Support data-toggle="modal" lama sekaligus data-bs-toggle="modal"
                $(document).off('click.bs.modal.fix').on(
                    'click.bs.modal.fix',
                    '[data-bs-toggle="modal"], [data-toggle="modal"]',
                    function(e) {
                        e.preventDefault();
                        var target = $(this).data('bs-target') || $(this).data('target') || $(this).attr('href');
                        if (!target) return;
                        var $modal = $(target);
                        if (!$modal.length) return;
                        bootstrap.Modal.getOrCreateInstance($modal[0]).show();
                    }
                );
            }

            function runAllFixes() {
                initDropdowns();
                initModals();
                patchJQueryPlugins();

                // Re-init setelah setiap AJAX call (DataTables, dll.)
                $(document).ajaxComplete(function() {
                    setTimeout(function() {
                        initDropdowns();
                        patchJQueryPlugins();
                    }, 150);
                });

                if (typeof MutationObserver !== 'undefined') {
                    var observer = new MutationObserver(function(mutations) {
                        var needReinit = false;
                        mutations.forEach(function(m) {
                            m.addedNodes.forEach(function(node) {
                                if (node.nodeType === 1 && node.querySelector) {
                                    if (
                                        node.querySelector('[data-bs-toggle="dropdown"]') ||
                                        node.querySelector('[data-bs-toggle="modal"]') ||
                                        node.classList.contains('modal')
                                    ) {
                                        needReinit = true;
                                    }
                                }
                            });
                        });
                        if (needReinit) {
                            setTimeout(function() {
                                initDropdowns();
                                initModals();
                                patchJQueryPlugins();
                            }, 100);
                        }
                    });
                    observer.observe(document.body, {
                        childList: true,
                        subtree: true
                    });
                }
            }

            if (document.readyState === 'complete') {
                runAllFixes();
            } else {
                window.addEventListener('load', runAllFixes);
            }
        })();
    </script>
    @if($errors->any())
    @php
        $swalType = 'error';
        $swalTitle = 'Terjadi Kesalahan!';
        $errorItems = implode('', array_map(fn($e) => '<li>'.$e.'</li>', $errors->all()));
        $swalHtml  = '<ul style="text-align:left;margin:0;padding-left:20px;">'.$errorItems.'</ul>';
    @endphp
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                Swal.fire({
                    icon: @json($swalType),
                    title: @json($swalTitle),
                    html: @json($swalHtml),
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true,
                    allowOutsideClick: false
                });
            }, 300);
        });
    </script>
    @endif

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            function getCookie(name) {
                let matches = document.cookie.match(new RegExp(
                    "(?:^|; )" + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + "=([^;]*)"
                ));
                return matches ? decodeURIComponent(matches[1].replace(/\+/g, ' ')) : undefined;
            }

            let successAlert = getCookie('swal_success');
            let errorAlert = getCookie('swal_error');

            if (successAlert) {
                document.cookie = "swal_success=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
                setTimeout(function() {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: successAlert,
                        showConfirmButton: false,
                        timer: 3000,
                        timerProgressBar: true,
                        allowOutsideClick: false
                    });
                }, 300);
            } else if (errorAlert) {
                document.cookie = "swal_error=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
                setTimeout(function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: errorAlert,
                        showConfirmButton: false,
                        timer: 3000,
                        timerProgressBar: true,
                        allowOutsideClick: false
                    });
                }, 300);
            }
        });
    </script>

    <script type="module">
        document.addEventListener('DOMContentLoaded', () => {
            initializeTable();
        });
    </script>
</body>

</html>
