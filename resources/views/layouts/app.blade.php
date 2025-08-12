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

    <title>INIXCOFFEE</title>
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="manifest" href="/site.webmanifest">
    <link rel="mask-icon" href="/safari-pinned-tab.svg" color="#5bbad5">
    <meta name="msapplication-TileColor" content="#2b5797">
    <meta name="theme-color" content="#333333">
    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">
    <link type="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.37/css/bootstrap-datetimepicker.min.css" />
    <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    {{-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"> --}}
    {{-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"> --}}

    {{-- <link rel="stylesheet" href="//cdn.datatables.net/2.0.3/css/dataTables.dataTables.min.css"> --}}
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
            /* From Uiverse.io by jamik-dev */
        .cube {
            position: absolute;
            width: 100px;
            height: 100px;
            top: 0;
            bottom: 0;
            left: 0;
            right: 0;
            margin: auto;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 6px;
        }

        .cube_item {
            height: 40px;
            width: 40px;
            border-radius: 10px;
            transition: all 0.2s ease-in;
        }

        .cube_x {
            background-color: #182f51;
            animation: animateLoaders 1s infinite;
        }

        .cube_y {
            background-color: #962D2D;
            animation: animateLoaders 1s 0.5s infinite;
        }
        .cube_z {
            background-color: #A5C7EF;
            animation: animateLoaders 1s 0.5s infinite;
        }

        @keyframes animateLoaders {
            0% {
                transform: scale(0.8);
            }

            50% {
                transform: scale(1.2);
            }

            100% {
                transform: scale(0.8);
            }
        }
       body, html {
            height: 100%;
            margin: 0;
            padding: 0;
            overflow: hidden;
        }
        #bgsvg {
            height: calc(100vh - 56px); /* Subtracts the navbar height */
            overflow-y: auto;
            padding: 20px;
            background-image: url('/css/background inix office-02.svg');
            background-size: cover;
            background-attachment: scroll;
        }
        .notification p {
            margin: 0;
            padding: 0;
        }

        .notification {
            background-color: #f9f9f9;
            padding: 10px;
            border-radius: 5px;
        }

        .link {
        color: black;
        background-color: transparent;
        text-decoration: none;
        }
        .link:hover {
        color: #182F51;
        background-color: transparent;
        text-decoration: none;
        }
        .link:active     {
        color: black;
        background-color: transparent;
        text-decoration: none;
        }
        .dropdown-menu {
            position: absolute;
            top: 100%;
            left: 0;
            z-index: 1000;
            display: none;
            float: left;
            min-width: 10rem;
            padding: .5rem 0;
            margin: .125rem 0 0;
            font-size: 1rem;
            color: #212529;
            text-align: left;
            list-style: none;
            background-color: #fff;
            background-clip: padding-box;
            border: 1px solid rgba(0,0,0,.15);
            border-radius: .25rem;
        }

        .dropdown-menu.show {
            display: block;
        }
        .card {
            display: flex;
            flex-direction: row;
            justify-content: space-between;
            width: auto;
            height: auto;
            border: 1px solid rgba(255, 255, 255, .25);
            border-radius: 20px;
            background-color: rgba(255, 255, 255, 0.45);
            box-shadow: 0 0 10px 1px rgba(0, 0, 0, 0.25);
            backdrop-filter: blur(2px);
            }

        .card img {
            height: 60%;
        }

        .circle {
            background: #ffffff;
            border-radius: 60%;
            color: #fff;
            height: 8.7em;
            position: relative;
            width: 8.7em;
        }

        .circle-content {
            hyphens: auto;
            margin: 0.75em;
            text-align: center;
        }
        .click-primary {
            border-radius: 5px;
            padding:       5px 10px;
            color:         #ffffff;
            display:       inline-block;
            font:          normal bold 14px/1 "Open Sans", sans-serif;
            text-align:    center;
            background:    #182f51;
            transition:    color 0.1s linear, background-color 0.2s linear;
        }

        .click-primary:hover {
            background:         #A5C7EF;
            color:              #ffffff;
            transition:    color 0.1s linear, background-color 0.2s linear;
        }


        .click-warning {
            background:    #f8be00;
            border-radius: 5px;
            padding:       5px 10px;
            color:         #000000;
            display:       inline-block;
            font:          normal bold 18px/1 "Open Sans", sans-serif;
            text-align:    center;
            transition:    color 0.1s linear, background-color 0.2s linear; /* Transisi warna teks selama 0.1 detik dan warna latar belakang selama 0.2 detik dengan perpindahan linear */
        }

        .click-warning:hover {
            background:         #A5C7EF; /* Warna merah saat tombol dihover */
            transition:    color 0.1s linear, background-color 0.2s linear; /* Transisi warna teks selama 0.1 detik dan warna latar belakang selama 0.2 detik dengan perpindahan linear */
        }

        .click-warning-icon {
            background:    #f8be00;
            border-radius: 1000px;
            width:         45px;
            height:        45px;
            color:         #ffffff;
            display:       flex;
            justify-content: center; /* Posisikan ikon secara horizontal di tengah */
            align-items:   center; /* Posisikan ikon secara vertikal di tengah */
            text-align:    center;
            text-decoration: none; /* Hilangkan dekorasi hyperlink */
        }

        .click-warning-icon i {
            line-height:   45px; /* Sesuaikan tinggi ikon dengan tinggi tombol */
        }

        .click-danger {
            background:         #983A3A;
            border-radius: 5px;
            padding:       5px 10px;
            color:         #ffffff;
            display:       inline-block;
            font:          normal bold 14px/1 "Open Sans", sans-serif;
            text-align:    center;
            /* background:    #182f51; */
            transition:    color 0.1s linear, background-color 0.2s linear;
        }

        .click-danger:hover {
            background:         #e05555;
            color:              #ffffff;
            transition:    color 0.1s linear, background-color 0.2s linear;
        }
        .click-danger-icon {
            background:    #983A3A;
            border-radius: 1000px;
            width:         45px;
            height:        45px;
            color:         #ffffff;
            display:       flex;
            justify-content: center; /* Posisikan ikon secara horizontal di tengah */
            align-items:   center; /* Posisikan ikon secara vertikal di tengah */
            text-align:    center;
            text-decoration: none; /* Hilangkan dekorasi hyperlink */
        }

        .click-danger-icon i {
            line-height:   45px; /* Sesuaikan tinggi ikon dengan tinggi tombol */
        }

        .click-secondary-icon {
            background:    #355C7C;
            border-radius: 5px;
            padding:       10px 20px;
            color:         #ffffff;
            display:       inline-block;
            font:          normal bold 12px/1 "Open Sans", sans-serif;
            text-align:    center;
            justify-content: center; /* Posisikan ikon secara horizontal di tengah */
            align-items:   center; /* Posisikan ikon secara vertikal di tengah */
            text-decoration: none; /* Hilangkan dekorasi hyperlink */
        }
        .click-secondary-icon i {
            line-height: 45px; /* Sesuaikan tinggi ikon dengan tinggi tombol */
        }
        .click-secondary {
            background:    #355C7C;
            border-radius: 5px;
            padding:       5px 10px;
            color:         #ffffff;
            display:       inline-block;
            font:          normal bold 18px/1 "Open Sans", sans-serif;
            text-align:    center;
            transition:    color 0.1s linear, background-color 0.2s linear; /* Transisi warna teks selama 0.1 detik dan warna latar belakang selama 0.2 detik dengan perpindahan linear */
        }


        .click-secondary:hover {
            color:         #A5C7EF; /* Warna merah saat tombol dihover */
            transition:    color 0.1s linear, background-color 0.2s linear; /* Transisi warna teks selama 0.1 detik dan warna latar belakang selama 0.2 detik dengan perpindahan linear */
        }

        /* #bgsvg{
            margin-top: 4px;
            background-image: url('/css/background inix office-02.svg');
            background-size: cover;
            background-attachment:scroll;
            height: 100vh;
            overflow-y: scroll;
        } */

        #logoinix {
            width: 400px;
        }

        .alert-custom {
            position: absolute;
            top: 10px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 1050; /* Memastikan alert berada di atas elemen lain */
            width: 100%; /* Atau sesuaikan dengan lebar yang diinginkan */
        }



        @media (max-width: 576px) {
            body{
                overflow-y: auto;
            }
            #bgsvg{
                background-image: url('/css/background inix office-02.svg') repeat-y;
                overflow-y: scroll;
            }
            .navbar-nav {
                flex-direction: row;
                padding-top: 10px;
            }

            #auth{
                display: none;
            }

            .nav-item {
                text-align: center;
                width: 100%;
                margin: 5px 0;
            }

            .navbar-brand {
                text-align: center;
                width: 100%;
                margin: 5px 0;
            }

            .navbar-brand img {
                margin-right: 0;
            }

            #logoinix {
                width: 250px;
            }
        }
        @media (min-width: 577px) and (max-width: 991px) {
            #bgsvg {
                background-image: url('/css/background inix office-02.svg') repeat-y;
            }

            .navbar-nav {
                flex-direction: column;
                padding-top: 10px;
            }

            #auth {
                display: none;
            }

            .nav-item {
                text-align: center;
                width: 100%;
                margin: 5px 0;
            }

            .navbar-brand {
                text-align: center;
                width: 100%;
                margin: 5px 0;
            }

            .navbar-brand img {
                margin-right: 0;
            }

            #logoinix {
                width: 250px;
            }
        }
        @media (max-width: 400px) {
            body {
                overflow-y: auto;
            }

            #bgsvg {
                background-image: url('/css/background inix office-02.svg') repeat-y;
                overflow-y: scroll;
            }

            .navbar-nav {
                flex-direction: column;
                padding-top: 5px;
            }

            #auth {
                display: none;
            }

            .nav-item {
                text-align: center;
                width: 100%;
                margin: 5px 0;
            }

            .navbar-brand {
                text-align: center;
                width: 100%;
                margin: 5px 0;
            }

            .navbar-brand img {
                margin-right: 0;
                max-width: 200px; /* Lebih kecil untuk layar kecil */
            }

            #logoinix {
                width: 250px;
            }

            /* Tambahan styling untuk teks atau elemen kecil */
            h1, h2, h3, h4, h5, h6 {
                font-size: smaller;
            }

            p {
                font-size: 14px;
            }

            button {
                font-size: 12px;
            }
        }
    </style>
</head>
<body>
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show m-0 alert-custom" role="alert" id="success-alert">
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 24 24"
                        class="success-svg"
                        style="width: 24px"
                    >
                        <path
                        fill-rule="evenodd"
                        d="m12 1c-6.075 0-11 4.925-11 11s4.925 11 11 11 11-4.925 11-11-4.925-11-11-11zm4.768 9.14c.0878-.1004.1546-.21726.1966-.34383.0419-.12657.0581-.26026.0477-.39319-.0105-.13293-.0475-.26242-.1087-.38085-.0613-.11844-.1456-.22342-.2481-.30879-.1024-.08536-.2209-.14938-.3484-.18828s-.2616-.0519-.3942-.03823c-.1327.01366-.2612.05372-.3782.1178-.1169.06409-.2198.15091-.3027.25537l-4.3 5.159-2.225-2.226c-.1886-.1822-.4412-.283-.7034-.2807s-.51301.1075-.69842.2929-.29058.4362-.29285.6984c-.00228.2622.09851.5148.28067.7034l3 3c.0983.0982.2159.1748.3454.2251.1295.0502.2681.0729.4069.0665.1387-.0063.2747-.0414.3991-.1032.1244-.0617.2347-.1487.3236-.2554z"
                        clip-rule="evenodd"
                        fill="green"
                        ></path>
                    </svg>
                    &nbsp {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <script>
                    setTimeout(function() {
                        let alert = document.getElementById('success-alert');
                        if (alert) {
                            alert.remove();
                        }
                    }, 5000); // 5000ms = 5 detik
                </script>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show m-0 alert-custom" role="alert" id="error-alert">
                    <svg
                        class="error-svg"
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 20 20"
                        aria-hidden="true"
                        style="width: 24px"
                    >
                        <path
                        fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                        clip-rule="evenodd"
                        fill="red"
                        ></path>
                    </svg>
                    &nbsp {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <script>
                    setTimeout(function() {
                        let alert = document.getElementById('error-alert');
                        if (alert) {
                            alert.remove();
                        }
                    }, 5000); // 5000ms = 5 detik
                </script>
            @endif
            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show m-0 alert-custom" role="alert">
                    @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                    @endforeach
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
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
                    @if(auth()->user()->unreadNotifications->count() > 0)
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
                            <a class="nav-link" style="margin: 7px 3px 0px 3px" href="{{ url('/home') }}" data-bs-toggle="tooltip" data-bs-placement="top" title="Home">
                                <img src="{{ asset('icon/home.svg') }}" class="img-responsive" width="30px">
                            </a>
                            <a class="nav-link position-relative" style="margin: 7px 3px 0px 3px" href="#" data-bs-toggle="modal" data-bs-target="#notificationModal">
                                <img src="{{ asset('icon/whitebell.svg') }}" class="img-responsive" width="30px">
                                @if(auth()->user()->unreadNotifications->count() > 0)
                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                        {{ auth()->user()->unreadNotifications->count() }}
                                        <span class="visually-hidden">unread notifications</span>
                                    </span>
                                @endif
                            </a>
                        </li>
                        <li class="nav-item order-0 order-md-1" style="margin-left: 10px" id="auth">
                            <h6 class="nav-link mt-1" style="text-transform: capitalize; color:#fff; margin:0px; padding:8px;">
                               <p class="p-0 m-0"> Selamat Datang {{ auth()->user()->username }}, Anda Login Sebagai</p> <p class="p-0 m-0">{{ auth()->user()->jabatan}}</p>
                            </h6>
                        </li>
                    </ul>
                </div>
                <div class="col-md-4 col-sm-4 col-xs-4 d-flex justify-content-center"  id="navbartengah">
                    <ul class="navbar-nav me-auto">
                        <li class="nav-item text-left">
                            <a class="navbar-brand" href="{{ url('/') }}">
                                <img src="{{ asset('icon/logo_e-officew.svg') }}" class="img-responsive" id="logoinix">
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="col-md-4 col-sm-4 col-xs-4 d-flex justify-content-end" id="navbarkanan">
                    <ul class="navbar-nav">
                        <li class="nav-item mx-1">
                            <a class="nav-link" href="{{ route('logout') }}" data-bs-toggle="tooltip" data-bs-placement="top" title="Logout"
                                onclick="event.preventDefault(); if(confirm('Apakah Anda Yakin?')) { document.getElementById('logout-form').submit(); }">
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

        <main class="py-2" id="bgsvg">
            @yield('content')
        </main>

    </div>
    @stack('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/locale/id.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.37/js/bootstrap-datetimepicker.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js" integrity="sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
</body>
</html>
