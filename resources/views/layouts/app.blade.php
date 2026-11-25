<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    {{--
    <meta name="mobile-web-app-capable" content="yes">
    <link rel="apple-touch-icon" sizes="180x180" href="{{asset('icon/apple-touch-icon-180x180.png')}}" /> --}}

    <meta name="user-id" content="{{ auth()->id() }}">
    <meta name="pusher-app-key" content="{{ config('broadcasting.connections.pusher.key') }}">
    <meta name="pusher-app-cluster" content="{{ config('broadcasting.connections.pusher.options.cluster') }}">


    <title>INIXCOFFEE</title>
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    {{-- <link rel="manifest" href="{{ asset('site.webmanifest') }}"> --}}
    <link rel="manifest" href="{{ asset('site.webmanifest') }}" crossorigin="use-credentials">
    <link rel="mask-icon" href="/safari-pinned-tab.svg" color="#5bbad5">
    <meta name="msapplication-TileColor" content="#2b5797">
    <meta name="theme-color" content="#333333">
    <!-- Fonts & CDN Preconnect -->
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">
    <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    {{-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"> --}}
    {{-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"> --}}

    {{-- <link rel="stylesheet" href="//cdn.datatables.net/2.0.3/css/dataTables.dataTables.min.css"> --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @yield('css')
    @stack('styles')
    @stack('css')
    <script src="{{ asset('assets/vendor/libs/jquery/jquery.js') }}"></script>
</head>

<body>
    {{-- Alerts handled by SweetAlert via Cookies --}}
    <div id="app">
        <div class="modal fade" id="notificationModal" tabindex="-1" aria-labelledby="notificationModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-scrollable" style="max-width: 550px;"> {{-- default 500-600px --}}
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="notificationModalLabel">Alert Pemberitahuan</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        @include('partials.notification-modal-body')
                    </div>
                    <div class="modal-footer">
                        @if(($globalUnreadNotifCount ?? 0) > 0)
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

    <nav class="navbar navbar-expand-md navbar-dark bg-dark shadow-sm">
        <div class="container-fluid">
            <div class="col-md-4 col-sm-4 col-xs-4 d-flex justify-content-start" id="navbarkiri">
                <ul class="navbar-nav">
                    <li class="nav-item d-flex">
                        <a class="nav-link" style="margin: 7px 3px 0px 3px" href="{{ url('/home') }}"
                            data-bs-toggle="tooltip" data-bs-placement="top" title="Home">
                            <img src="{{ asset('icon/home.svg') }}" class="img-responsive" width="30px">
                        </a>
                        <a class="nav-link position-relative" style="margin: 7px 3px 0px 3px" href="#"
                            data-bs-toggle="modal" data-bs-target="#notificationModal">
                            <img src="{{ asset('icon/whitebell.svg') }}" class="img-responsive" width="30px">
                            @if(($globalUnreadNotifCount ?? 0) > 0)
                                <span id="notifBadge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                    {{ $globalUnreadNotifCount }}
                                    <span class="visually-hidden">unread notifications</span>
                                </span>
                            @endif
                        </a>
                    </li>

                    <li class="nav-item order-0 order-md-1" style="margin-left: 10px" id="auth">
                        <h6 class="nav-link mt-1" style="text-transform: capitalize; color:#fff; margin:0px; padding:8px;">
                            <p class="p-0 m-0"> Selamat Datang {{ auth()->user()->username }}, Anda Login Sebagai</p>
                            <p class="p-0 m-0">{{ auth()->user()->jabatan}}</p>
                        </h6>
                    </li>
                </ul>
            </div>

            <div class="col-md-4 col-sm-4 col-xs-4 d-flex justify-content-center" id="navbartengah">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item text-left">
                        <a class="navbar-brand" href="{{ url('/') }}">
                            <img src="{{ asset('icon/logo_e-officew.svg') }}" class="img-responsive" id="logoinix">
                        </a>
                    </li>
                </ul>
            </div>

            <div class="col-md-1 col-sm-1 col-xs-1 d-flex justify-content-end" id="navbarpalingkanan">
                <ul class="navbar-nav">
                    <li class="nav-item mx-1">
                        <a class="nav-link" href="#" id="logout-link" data-bs-toggle="tooltip" data-bs-placement="top" title="Logout">
                            <img src="{{ asset('icon/power.svg') }}" class="img-responsive" width="30px">
                        </a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                            @csrf
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <audio id="notifSound" src="{{ asset('bell.mp3') }}" preload="auto"></audio>


    <main class="py-2" id="bgsvg">
        @yield('content')
    </main>

    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/locale/id.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js" defer></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @stack('js')
    @yield('scripts')
    @stack('scripts')

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
</body>
    @if (app()->environment('staging'))
        <div aria-hidden="true" style="position: fixed; top: 50%; left: 50%; z-index: 999998; color: rgba(220, 38, 38, 0.18); font-size: clamp(4rem, 12vw, 10rem); font-weight: 800; letter-spacing: 0.2em; pointer-events: none; transform: translate(-50%, -50%) rotate(-25deg); user-select: none; white-space: nowrap;">STAGING</div>
    @endif

</html>
