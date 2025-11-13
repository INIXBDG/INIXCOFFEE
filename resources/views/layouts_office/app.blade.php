<!DOCTYPE html>
<html lang="en" class="layout-menu-fixed layout-compact" data-assets-path="{{ asset('assets/') }}"
    data-template="vertical-menu-template-free">

<head>
    <meta charset="utf-8" />
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
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

    <!-- Page CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/demo.css') }}" />

    <!-- Custom Styles -->
    <style>
        .avatar {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            overflow: hidden;
            border: 2px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .sidebar-footer {
            position: sticky;
            bottom: 0;
            background: var(--bs-body-bg);
            border-top: 1px solid var(--bs-border-color);
            padding: 1rem;
            z-index: 1;
        }

        .menu-inner {
            padding-bottom: 80px;
            /* Space for sticky footer */
        }
    </style>
</head>

<body>
    <!-- Layout Wrapper -->
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">

            <!-- Sidebar -->
            @include('layouts_office.sidebar')
            <!-- /Sidebar -->

            <!-- Layout Page -->
            <div class="layout-page">

                <!-- Navbar -->
                @include('layouts_office.navbar')
                <!-- /Navbar -->

                <!-- Content Wrapper -->
                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        @yield('office_contents')
                    </div>

                    <!-- Footer -->
                    @include('layouts_office.footer')
                    <!-- /Footer -->
                    <div class="content-backdrop fade"></div>
                </div>
                <!-- /Content Wrapper -->
            </div>
            <!-- /Layout Page -->
        </div>

        <!-- Overlay -->
        <div class="layout-overlay layout-menu-toggle"></div>
    </div>
    <!-- /Layout Wrapper -->

    <!-- Core JS -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    <script src="{{ asset('assets/vendor/libs/popper/popper.js') }}"></script>
    <script src="{{ asset('assets/vendor/js/bootstrap.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/apex-charts/apexcharts.js') }}"></script>
    <script src="{{ asset('assets/vendor/js/menu.js') }}"></script>
    <script src="{{ asset('assets/js/main.js') }}"></script>
    <script src="{{ asset('assets/js/dashboards-analytics.js') }}"></script>

    <!-- Iconify -->
    <script src="https://code.iconify.design/3/3.1.0/iconify.min.js"></script>

    <!-- User Profile AJAX -->
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
