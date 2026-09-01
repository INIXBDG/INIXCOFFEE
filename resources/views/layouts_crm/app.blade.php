<!doctype html>

<html lang="en" class="layout-menu-fixed layout-compact" data-assets-path="{{ asset('assets/') }}"
    data-template="vertical-menu-template-free">

<head>
    <meta charset="utf-8" />
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />

    <title>INIX - CRM</title>
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

    {{-- DataTables CSS --}}
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/dataTables.bootstrap5.min.css') }}">

    {{-- CSS Bawaan Select2 --}}
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/select2.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/select2-bootstrap-5-theme.min.css') }}" />

    <!-- Page CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/demo.css') }}" />

    <!-- Helpers -->
    <link rel="preload" href="{{ asset('assets/vendor/libs/jquery/jquery.js') }}" as="script">
    <link rel="preload" href="{{ asset('assets/vendor/libs/dataTables/jquery.dataTables.min.js') }}" as="script">
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
        .swal2-container {
            z-index: 999999 !important;
        }
    </style>
</head>

<body>

    <!-- Layout wrapper -->
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            <!-- Sidebar -->
            @include('layouts_crm.sidebar')
            <!-- End Sidebar-->

            <!-- Layout container -->
            <div class="layout-page">
                <!-- Navbar -->
                @include('layouts_crm.navbar')
                <!-- / Navbar -->

                <!-- Content Wrapper -->
                <div class="content-wrapper">
                    <!-- Contents -->
                    @yield('crm_contents')
                    <!-- / Contents -->
                    <!-- Footer -->
                    @include('layouts_crm.footer')
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
    <div class="modal fade" id="importExcelModal" tabindex="-1" aria-labelledby="importExcelModalLabel" aria-hidden="true">
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
    </div>

    <script src="{{ asset('assets/vendor/libs/jquery/jquery.js') }}"></script>

    <script src="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2@11.js') }}" defer></script>

    {{-- Global Validator JS --}}
    <script src="{{ asset('js/global-validator.js') }}" defer></script>

    {{-- Select2 JS --}}
    <script src="{{ asset('assets/vendor/libs/select2/select2.min.js') }}" defer></script>

    {{-- Moment JS --}}
    <script src="{{ asset('assets/vendor/libs/moment/moment-with-locales.min.js') }}" defer></script>

    {{-- Hapus atribut defer pada DataTables JS --}}
    <script src="{{ asset('assets/vendor/libs/dataTables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/dataTables/dataTables.bootstrap5.min.js') }}"></script>

    <!-- Bootstrap JS -->
    <script src="{{ asset('assets/vendor/libs/popper/popper.js') }}" defer></script>
    <script src="{{ asset('assets/vendor/js/bootstrap.js') }}" defer></script>

    <!-- Vendors JS -->
    <script src="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js') }}" defer></script>
    <script src="{{ asset('assets/vendor/libs/apex-charts/apexcharts.js') }}" defer></script>
    <script src="{{ asset('assets/vendor/js/menu.js') }}" defer></script>

    <!-- Custom JS -->
    <script src="{{ asset('assets/js/main.js') }}" defer></script>
    <script src="{{ asset('assets/js/dashboards-analytics.js') }}" defer></script>

    <!-- Iconify JS -->
    <script src="{{ asset('assets/vendor/libs/iconify/iconify.min.js') }}" defer></script>

    <!-- GitHub button -->
    <script async defer src="{{ asset('assets/vendor/libs/buttongithub/buttons.js') }}" defer></script>

    <script src="{{ asset('assets/vendor/libs/chartjs/chart.js') }}" defer></script>

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
        });
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

    @yield('scripts')
</body>

</html>
