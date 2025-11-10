<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">
    <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    {{-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"> --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    {{-- <link rel="stylesheet" href="//cdn.datatables.net/2.0.3/css/dataTables.dataTables.min.css"> --}}
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
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

        .vr {
            border-left: 1px solid white;
            height: 40px;
            margin-left: 4px;
            margin-right: 4px;
        }

        .nav-pills .nav-link {
            background-color: #182F51 !important;
            color: #f9f9f9;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        /* Hover effect */
        .nav-pills .nav-link:hover {
            background-color: #A5C7EF !important;
            color: #ffffff;
        }

        /* Active state */
        .nav-pills .nav-link.active {
            background-color: #A5C7EF !important;
            color: #ffffff;
        }

        /* Vertical divider */
        .vr.vr-blurry {
            border-left: 1px solid rgba(255, 255, 255, 0.3);
            height: 40px;
            margin: 0 10px;
            filter: blur(1px);
        }

        body,
        html {
            height: 100%;
            margin: 0;
            padding: 0;
            overflow: hidden;
        }

        #bgsvg {
            height: calc(100vh - 56px);
            /* Subtracts the navbar height */
            overflow-y: auto;
            padding: 20px;
            background-image: url('/css/background inix office-02.svg');
            background-size: cover;
            background-attachment: scroll;
        }

        .custom-radio {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background-color: #fff;
            border: 2px solid #007bff;
            appearance: none;
            -webkit-appearance: none;
            outline: none;
            cursor: pointer;
            transition: background-color 0.2s ease;
        }

        .custom-radio:checked {
            background-color: #007bff;
        }

        .form-check-label {
            font-size: 14px;
            text-justify: inter-word;
            margin-left: 5px;
        }

        #notif {
            padding: 0.5rem;

            table {
                width: 100%;

                tr {
                    display: flex;

                    td {
                        a.btn {
                            font-size: 0.8rem;
                            padding: 3px;
                        }
                    }

                    td:nth-child(2) {
                        text-align: right;
                        justify-content: space-around;
                    }
                }
            }

        }

        .btn-custom {
            background-color: #182F51;
            color: white;
        }

        .btn-custom:hover {
            background-color: #355C7C;
            color: white;
        }

        body {
            /* overflow: scroll; */
            height: 100%;
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

        .link:active {
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
            border: 1px solid rgba(0, 0, 0, .15);
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
            padding: 5px 5px;
            color: #ffffff;
            display: inline-block;
            font: normal bold 14px/1 "Open Sans", sans-serif;
            text-align: center;
            background: #182f51;
            transition: color 0.1s linear, background-color 0.2s linear;
        }

        .click-primary:hover {
            background: #A5C7EF;
            color: #ffffff;
            transition: color 0.1s linear, background-color 0.2s linear;
        }

        .click-warning {
            background: #f8be00;
            border-radius: 5px;
            padding: 5px 10px;
            color: #000000;
            display: inline-block;
            font: normal bold 18px/1 "Open Sans", sans-serif;
            text-align: center;
            transition: color 0.1s linear, background-color 0.2s linear;
            /* Transisi warna teks selama 0.1 detik dan warna latar belakang selama 0.2 detik dengan perpindahan linear */
        }

        .click-warning:hover {
            background: #A5C7EF;
            /* Warna merah saat tombol dihover */
            transition: color 0.1s linear, background-color 0.2s linear;
            /* Transisi warna teks selama 0.1 detik dan warna latar belakang selama 0.2 detik dengan perpindahan linear */
        }

        .click-warning-icon {
            background: #f8be00;
            border-radius: 1000px;
            width: 45px;
            height: 45px;
            color: #ffffff;
            display: flex;
            justify-content: center;
            /* Posisikan ikon secara horizontal di tengah */
            align-items: center;
            /* Posisikan ikon secara vertikal di tengah */
            text-align: center;
            text-decoration: none;
            /* Hilangkan dekorasi hyperlink */
        }

        .click-warning-icon i {
            line-height: 45px;
            /* Sesuaikan tinggi ikon dengan tinggi tombol */
        }

        .click-danger {
            background: #983A3A;
            border-radius: 5px;
            padding: 5px 10px;
            color: #ffffff;
            display: inline-block;
            font: normal bold 14px/1 "Open Sans", sans-serif;
            text-align: center;
            /* background:    #182f51; */
            transition: color 0.1s linear, background-color 0.2s linear;
        }

        .click-danger:hover {
            background: #e05555;
            color: #ffffff;
            transition: color 0.1s linear, background-color 0.2s linear;
        }

        .click-danger-icon {
            background: #983A3A;
            border-radius: 1000px;
            width: 45px;
            height: 45px;
            color: #ffffff;
            display: flex;
            justify-content: center;
            /* Posisikan ikon secara horizontal di tengah */
            align-items: center;
            /* Posisikan ikon secara vertikal di tengah */
            text-align: center;
            text-decoration: none;
            /* Hilangkan dekorasi hyperlink */
        }

        .click-danger-icon i {
            line-height: 45px;
            /* Sesuaikan tinggi ikon dengan tinggi tombol */
        }

        .click-secondary-icon {
            background: #355C7C;
            border-radius: 5px;
            padding: 10px 20px;
            color: #ffffff;
            display: inline-block;
            font: normal bold 12px/1 "Open Sans", sans-serif;
            text-align: center;
            justify-content: center;
            /* Posisikan ikon secara horizontal di tengah */
            align-items: center;
            /* Posisikan ikon secara vertikal di tengah */
            text-decoration: none;
            /* Hilangkan dekorasi hyperlink */
        }

        .click-secondary-icon i {
            line-height: 45px;
            /* Sesuaikan tinggi ikon dengan tinggi tombol */
        }

        .click-secondary {
            background: #355C7C;
            border-radius: 5px;
            padding: 5px 10px;
            color: #ffffff;
            display: inline-block;
            font: normal bold 18px/1 "Open Sans", sans-serif;
            text-align: center;
            transition: color 0.1s linear, background-color 0.2s linear;
            /* Transisi warna teks selama 0.1 detik dan warna latar belakang selama 0.2 detik dengan perpindahan linear */
        }

        .click-secondary:hover {
            color: #A5C7EF;
            /* Warna merah saat tombol dihover */
            transition: color 0.1s linear, background-color 0.2s linear;
            /* Transisi warna teks selama 0.1 detik dan warna latar belakang selama 0.2 detik dengan perpindahan linear */
        }

        /* #bgsvg{
            margin-top: 4px;
            background-image: url('/css/background inix office-02.svg');
            background-size: cover;
            background-attachment:scroll;

        } */
        #logoinix {
            width: 400px;
        }

        .alert-custom {
            position: absolute;
            top: 10px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 1050;
            /* Memastikan alert berada di atas elemen lain */
            width: 100%;
            /* Atau sesuaikan dengan lebar yang diinginkan */
        }

        /* Progress bar styles */
        #progress-container {
            width: 100%;
            height: 100px;
            position: relative;
            background-color: #e0e0e0;
            border-radius: 5px;
            margin-bottom: 10px;
            overflow: hidden;
            /* Ensure that elements stay inside the container */
        }

        #progress-bar {
            width: 0%;
            /* This will be dynamically updated with progress */
            height: 100%;
            background-color: rgba(0, 0, 0, 0);
            /* background-color: #4caf50; */
            background-position: left;
            background-image: url("{{ asset('css/jalan_terang.png') }}");
            background-repeat: repeat-x;
            position: absolute;
            transition: left 10s ease;
        }

        /* Car styling */
        #car {
            width: 110px;
            height: 110px;
            background-image: url("{{ asset('css/car.png') }}");
            background-size: cover;
            position: absolute;
            top: 0;
            /* Adjust to 0 to make the car appear inside the progress bar */
            left: 0%;
            /* Start at 0 */
            z-index: 2;
            /* Ensure the car is above the progress bar */
            transition: left 10s ease-in-out;
        }

        /* Goal label styling */
        .target-label-right {
            position: absolute;
            right: 0;
            top: 0;
            font-size: 20px;
            font-weight: bold;
            color: #000000;
            z-index: 1;
            transition: right 5s ease;
        }

        /* Horizontal labels */
        .horizontal-ruler-labels {
            position: relative;
            width: 100%;
            height: 20px;
            display: flex;
            justify-content: space-between;
            margin-top: 10px;
            padding-left: 0;
            padding-right: 10px;
        }

        .horizontal-ruler-labels .label {
            font-size: 14px;
            text-align: center;
            transform: translateX(-50%);
            /* To center the labels on their calculated position */
            white-space: nowrap;
        }

        .tab-pane {
            position: relative;
            transition: opacity 0.5s ease-in-out;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        #chartjs {
            height: 500px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 40px;
        }

        canvas {
            width: 100%;
        }

        .btn.active {
            background-color: #007bff !important;
            border-color: #007bff !important;
        }



        /* Optional: Custom styling for a more pronounced switch effect */
        /* start style toggle switch */
        #btngroupnavbar {
            border-radius: 50px;
            /* Membuat group tombol menjadi melingkar */
            overflow: hidden;
            /* Memastikan sudut melingkar terlihat */
        }

        /* --- Warna Dasar Tombol (saat tidak aktif) --- */
        #btngroupnavbar .btn-primary {
            transition: all 0.3s ease;
            /* Transisi halus untuk perubahan warna */
            background-color: #f0f0f0;
            /* Contoh: Abu-abu sangat muda */
            color: #555;
            /* Contoh: Teks abu-abu gelap */
            border-color: #ddd;
            /* Contoh: Border abu-abu */
        }

        /* --- Warna Tombol Saat Aktif/Terpilih --- */
        #btngroupnavbar .btn-check:checked+.btn-primary {
            background-color: #DC2525;
            /* GANTI DENGAN KODE WARNA HIJAU YANG ANDA INGINKAN */
            color: white;
            /* Warna teks putih saat aktif */
            box-shadow: 0 0 10px rgba(40, 167, 69, 0.5);
            /* Efek bayangan, sesuaikan dengan warna background */
            border-color: #ff1111ff;
            /* Border dengan warna yang sama saat aktif */
            z-index: 1;
        }

        /* --- Warna Tombol Saat Tidak Aktif (lebih spesifik) --- */
        #btngroupnavbar .btn-check:not(:checked)+.btn-primary {
            background-color: #e9ecef;
            /* Contoh: Abu-abu muda untuk tombol tidak terpilih */
            color: #495057;
            /* Contoh: Teks abu-abu gelap */
            border-color: #ced4da;
            /* Contoh: Border abu-abu */
        }

        /* end styling toggle switch*/

        @media (max-width: 576px) {

            /* Reorder the columns for mobile */
            #navbarkanan {
                order: 2;
                /* Move navbarkanan after navbarpalingkanan */
                /* padding: 10px; */
            }

            #btngroupnavbar {
                padding: 10px;
            }

            #navbarpalingkanan {
                order: 0;
                /* Move navbarpalingkanan before navbarkanan */
            }

            body {
                overflow-y: auto;
            }

            #bgsvg {
                background-image: url('/css/background inix office-02.svg') repeat-y;
                overflow-y: scroll;
            }

            .navbar-nav {
                flex-direction: row;
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
                max-width: 200px;
                /* Lebih kecil untuk layar kecil */
            }

            #logoinix {
                width: 250px;
            }

            /* Tambahan styling untuk teks atau elemen kecil */
            h1,
            h2,
            h3,
            h4,
            h5,
            h6 {
                font-size: smaller;
            }

            p {
                font-size: 14px;
            }

            button {
                font-size: 12px;
            }
        }

        @media only screen and (max-width: 768px) {
            a {
                width: auto;
                max-width: 100%;
            }

            .nav-tabs {
                display: flex;
                flex-wrap: nowrap;
                /* Mencegah tab terbungkus ke bawah */
                overflow-x: auto;
                /* Menambahkan scroll horizontal jika diperlukan */
            }

            .nav-item {
                white-space: nowrap;
                /* Menjaga teks tetap dalam satu baris */
            }

            .tab-pane {
                position: relative;
                transition: opacity 0.5s ease-in-out;
            }

            #chartjs {
                height: 300px;
                /* Kurangi tinggi pada perangkat mobile */
                padding: 20px;
                /* Kurangi padding agar sesuai dengan layar kecil */
            }

            #PenjualanPerSalesPerQuartalChart,
            #PenjualanPerSalesPerTahunChart {
                width: 100% !important;
                /* Pastikan canvas menggunakan lebar penuh */
                height: auto !important;
                /* Sesuaikan tinggi secara otomatis */
            }

            #salesKeySelect {
                width: 100% !important;
                /* Dropdown seleksi menyesuaikan lebar layar */
            }

            #progress-bar-container {
                height: 30px;
                /* Reduce the height for mobile */
            }

            #car {
                top: -5px;
                /* Adjust the car position for mobile */
            }

            .horizontal-ruler-labels .label {
                font-size: 10px;
                /* Reduce label size on mobile */
            }

            /* Hide every other ruler label to reduce clutter */
            .horizontal-ruler-labels .label:nth-child(odd) {
                display: none;
            }
        }

        input[type="radio"].btn-check:disabled+label.btn {
            background-color: #bfc3c6 !important;
            color: #fff !important;
            border-color: #aeb6bd !important;
            pointer-events: none !important;
            opacity: 1 !important;
        }
    </style>
</head>

<body>
    @if (session('success'))
    <div class="alert alert-success alert-dismissible fade show m-0 alert-custom" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif
    @if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show m-0 alert-custom" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif
    @if ($errors->any())
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

    <div class="modal fade" id="modalPemberitahuan" tabindex="-1" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="col-md-12 d-flex justify-content-between">
                        <h5 class="modal-title" id="exampleModalLabel">Pengumuman</h5>
                        @if (auth()->user()->jabatan == 'HRD' ||
                        auth()->user()->jabatan === 'Koordinator Office' ||
                        auth()->user()->jabatan == 'Office Manager')
                        <a href="{{ route('notif.create') }}" class="btn btn-sm btn-custom mx-4"><img
                                src="{{ asset('icon/plus.svg') }}" class="" width="20px"></a>
                        @endif
                    </div>
                </div>
                <div class="modal-body" style="overflow-y: scroll; height:400px">
                    {{-- {{$notifikasi}} --}}
                    @if (
                    $notifikasi->sortByDesc('created_at')->filter(function ($notif) {
                    return \Carbon\Carbon::parse($notif->tanggal_akhir)->lt(
                    \Carbon\Carbon::parse($notif->tanggal_akhir)->addWeek());
                    })->isEmpty())
                    <p>Tidak ada notifikasi</p>
                    @else
                    @foreach ($notifikasi as $notif)
                    @if (\Carbon\Carbon::parse($notif->tanggal_akhir)->lt(\Carbon\Carbon::parse($notif->tanggal_akhir)->addWeek()))
                    <div class="card-body" id="notif">
                        <table>
                            <tr>
                                <td style="width:80%">
                                    @if ($notif->tipe_notifikasi == 'Libur')
                                    <div class="card-title" style="text-transform: capitalize">
                                        Pengumuman <strong>{{ $notif->tipe_notifikasi }}</strong>
                                        Dari {{ $notif->id_user }}
                                        <b>{{ $notif->users->jabatan }}</b>
                                        <p>{{ $notif->isi_notifikasi }}<br>
                                            {{-- {{\Carbon\Carbon::parse($notif->tanggal_akhir)->addWeek()}} --}}
                                            @if ($notif->tanggal_awal == $notif->tanggal_akhir)
                                            Pada Tanggal
                                            {{ \Carbon\Carbon::parse($notif->tanggal_awal)->translatedFormat('d F Y') }}
                                            @else
                                            Pada Tanggal
                                            {{ \Carbon\Carbon::parse($notif->tanggal_awal)->translatedFormat('d F Y') }}
                                            Sampai Tanggal
                                            {{ \Carbon\Carbon::parse($notif->tanggal_akhir)->translatedFormat('d F Y') }}
                                            @endif
                                        </p>
                                        <p class="m-0">
                                            {{ \Carbon\Carbon::parse($notif->created_at)->translatedFormat('d F Y \J\a\m H:i:s') }}
                                        </p>
                                    </div>
                                    @else
                                    <div class="card-title" style="text-transform: capitalize">
                                        Pengumuman <strong>{{ $notif->tipe_notifikasi }}</strong>
                                        Dari {{ $notif->id_user }}
                                        <b>{{ $notif->users->jabatan }}</b>
                                        <p>{{ $notif->isi_notifikasi }}</p>
                                        <p class="m-0">
                                            {{ \Carbon\Carbon::parse($notif->created_at)->translatedFormat('d F Y \J\a\m H:i:s') }}
                                        </p>
                                    </div>
                                    @endif
                                </td>
                                <td style="width: 20%">
                                    <div class="d-flex gap-2 align-items-center">
                                        @if (auth()->user()->jabatan == 'HRD' ||
                                        auth()->user()->jabatan == 'Office Manager' ||
                                        auth()->user()->jabatan === 'Koordinator Office')
                                        <a href="{{ route('notif.edit', $notif->id) }}" class="btn btn-warning" id="dismiss-notification">
                                            <img src="{{ asset('icon/edit.svg') }}" width="20px">
                                        </a>
                                        @endif

                                        <form action="{{ route('notif.destroy', $notif->id) }}" method="POST" style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger" id="dismiss-notification" style="padding: 0 7px;">
                                                <img src="{{ asset('icon/trash.svg') }}" width="20px" alt="delete">
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <hr class="m-0" id="hr">
                    @endif
                    @endforeach
                    @endif

                    @if (!$absenHariIni)
                    Anda belum absensi hari ini, harap segera melakukan absensi.
                    @else
                    Anda sudah absensi hari ini pada tanggal {{\Carbon\Carbon::parse($absenHariIni->tanggal)->translatedFormat('d F Y')}} di jam {{$absenHariIni->jam_masuk}}
                    @endif
                    @if (!empty($absenHariIni->jam_keluar)&\Carbon\Carbon::now()->between(
                    \Carbon\Carbon::createFromTimeString('17:00:00'),
                    \Carbon\Carbon::createFromTimeString('23:59:59')
                    ))
                    Terimakasih telah melakukan absensi pulang, hati hati dijalan!
                    @elseif (\Carbon\Carbon::now()->between(
                    \Carbon\Carbon::createFromTimeString('17:00:00'),
                    \Carbon\Carbon::createFromTimeString('23:59:59')
                    ))
                    Harap melakukan absensi pulang ya!
                    @endif



                    @if (auth()->user()->jabatan == 'Programmer')
                    Diupdate pada tanggal 2 Juli 2025
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-custom" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="modalAbsen" tabindex="-1" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Absensi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                        aria-label="Close"></button> <!-- Tombol X -->
                </div>
                <div class="modal-body d-flex flex-column align-items-center justify-content-center">
                    <div id="camera"
                        style="width: 320px; height: 320px; border: 2px solid #ddd; border-radius: 5px;"></div>
                    <br />
                    <div class="row">
                        <div class="btn-group w-100 flex-wrap" role="group" aria-label="Pilihan Absen">
                            <input type="radio" class="btn-check" name="keterangan" id="normal"
                                value="Kantor" autocomplete="off" disabled>
                            <label class="btn btn-outline-primary m-1" for="normal">
                                <i class="bi bi-person-check"></i> Absen Normal
                            </label>

                            <input type="radio" class="btn-check" name="keterangan" id="inhouse"
                                value="Inhouse Bandung" autocomplete="off" disabled>
                            <label class="btn btn-outline-warning m-1" for="inhouse">
                                <i class="bi bi-house-door"></i> Absen Inhouse BDG
                            </label>

                            <input type="radio" class="btn-check" name="keterangan" id="spj"
                                value="SPJ" autocomplete="off" disabled>
                            <label class="btn btn-outline-success m-1" for="spj">
                                <i class="bi bi-truck"></i> Absen SPJ
                            </label>
                        </div>
                    </div>

                    <br />
                    <div class="d-flex flex-row justify-content-between w-100">
                        <button id="takeSnapshot" class="btn btn-primary mx-2">Absen Masuk</button>
                        {{-- <button id="tipeabsen" class="btn btn-primary mx-2">Absen Masuk</button> --}}
                        <button id="pulang" class="btn btn-danger mx-2">Absen Pulang</button>
                    </div>

                    <br />
                    <div id="result" class="" style="width: 320px; text-align: center;"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-custom" data-bs-dismiss="modal">Tutup</button>
                    <!-- Tombol Tutup -->
                </div>
            </div>
        </div>
    </div>
    <!-- Modal Spinner -->
    <div class="modal fade" id="loadingModal" tabindex="-1" aria-labelledby="spinnerModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="cube">
                <div class="cube_item cube_x"></div>
                <div class="cube_item cube_y"></div>
                <div class="cube_item cube_x"></div>
                <div class="cube_item cube_z"></div>
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
                            @if (auth()->user()->unreadNotifications->count() > 0)
                            <span
                                class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                {{ auth()->user()->unreadNotifications->count() }}
                                <span class="visually-hidden">unread notifications</span>
                            </span>
                            @endif
                        </a>
                    </li>
                    <li class="nav-item order-0 order-md-1" style="margin-left: 10px" id="auth">
                        <h6 class="nav-link mt-1"
                            style="text-transform: capitalize; color:#fff; margin:0px; padding:8px;">
                            <p class="p-0 m-0"> Selamat Datang {{ auth()->user()->username }}, Anda Login Sebagai
                            </p>
                            <p class="p-0 m-0">{{ auth()->user()->jabatan }}</p>
                        </h6>
                    </li>
                </ul>
            </div>
            <div class="col-md-4 col-sm-4 col-xs-4 d-flex justify-content-center" id="navbartengah">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item text-left">
                        <a class="navbar-brand" href="{{ url('/') }}">
                            <img src="{{ asset('icon/logo_e-officew.svg') }}" class="img-responsive"
                                id="logoinix">
                        </a>
                    </li>
                </ul>
            </div>
            <div class="col-md-3 col-sm-3 col-xs-3 d-flex justify-content-center" id="navbarkanan">
                <div class="btn-group" role="group" aria-label="Navigation Switch" id="btngroupnavbar">
                    <input type="radio" class="btn-check" name="nav-options" id="pills-home-tab" autocomplete="off" checked>
                    <label class="btn btn-primary" for="pills-home-tab">Home</label>

                    <input type="radio" class="btn-check" name="nav-options" id="pills-dashboard-tab" autocomplete="off">
                    <label class="btn btn-primary" for="pills-dashboard-tab">Dashboard</label>

                    @can('Akses Development')
                    <input type="radio" class="btn-check" name="nav-options" id="pills-admin-tab" autocomplete="off">
                    <label class="btn btn-primary" for="pills-admin-tab">SuperAdmin</label>
                    @endcan
                </div>
            </div>
            <div class="col-md-1 col-sm-1 col-xs-1 d-flex justify-content-end" id="navbarpalingkanan">
                <ul class="navbar-nav">
                    <li class="nav-item mx-1">
                        <a class="nav-link" href="#" id="logout-link" data-bs-toggle="tooltip"
                            data-bs-placement="top" title="Logout">
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
    <main class="container-fluid" style="height: 92vh" id="bgsvg">
        {{-- {{auth()->user()->hashids}} --}}
        <div class="tab-content" id="pills-tabContent">
            <div class="tab-pane fade" id="pills-home" role="tabpanel" aria-labelledby="pills-home-tab">
                <div class="row justify-content-between">
                    <div class="col-md-12 col-sm-12 col-xs-12 col-lg-6 col-xl-6">
                        <div class="row">
                            <div class="col-md-12 mt-1">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="text-center card-title">Karyawan</h5>
                                        <div class="row">
                                            <div class="col-sm-6 mt-2">
                                                <div class="card" id="card-hover">
                                                    <div class="card-body d-flex">
                                                        <div class="col-md-2">
                                                            <img src="{{ asset('icon/user.svg') }}"
                                                                class="img-responsive" width="30px">
                                                        </div>
                                                        <div class="col-md-10" style="margin-left: 10px">
                                                            <a href="{{ route('user.show', auth()->user()->hashids) }}" class="link stretched-link text-decoration-none">
                                                                <h5 class="card-title">Profil Saya</h5>
                                                            </a>
                                                            <p class="card-text">Profil saya sebagai karyawan INIXINDO Bandung.</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            @can('View DataKaryawan')
                                            <div class="col-sm-6 mt-2">
                                                <div class="card" id="card-hover">
                                                    <div class="card-body d-flex">
                                                        <div class="col-md-2">
                                                            <img src="{{ asset('icon/users.svg') }}"
                                                                class="img-responsive" width="30px">
                                                        </div>
                                                        <div class="col-md-10" style="margin-left: 10px">
                                                            <a href="/user"
                                                                class="link stretched-link text-decoration-none">
                                                                <h5 class="card-title">Data Karyawan</h5>
                                                            </a>
                                                            <p class="card-text">Data lengkap semua karyawan.</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            @endcan
                                            {{-- @can('View DataKaryawan') --}}
                                            <div class="col-sm-6 mt-2">
                                                <div class="card" id="card-hover">
                                                    <div class="card-body d-flex">
                                                        <div class="col-md-2">
                                                            <img src="{{ asset('icon/list-check.svg') }}"
                                                                class="img-responsive" width="30px">
                                                        </div>
                                                        <div class="col-md-10" style="margin-left: 10px">
                                                            <a href="/daily-activities"
                                                                class="link stretched-link text-decoration-none">
                                                                <h5 class="card-title">Aktivitas Harian</h5>
                                                            </a>
                                                            <p class="card-text">aktivitas per hari untuk masing-masing divisi</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            {{-- @endcan --}}
                                            @can('View Jabatan')
                                            <div class="col-sm-6 mt-2">
                                                <div class="card" id="card-hover">
                                                    <div class="card-body d-flex">
                                                        <div class="col-md-2">
                                                            <img src="{{ asset('icon/award.svg') }}"
                                                                class="img-responsive" width="30px">
                                                        </div>
                                                        <div class="col-md-10" style="margin-left: 10px">
                                                            <a href="/jabatan"
                                                                class="link stretched-link text-decoration-none">
                                                                <h5 class="card-title">Jabatan</h5>
                                                            </a>
                                                            <p class="card-text">Data Jabatan.</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            @endcan
                                            <div class="col-sm-6 mt-2">
                                                <div class="card" id="card-hover">
                                                    <div class="card-body d-flex">
                                                        <div class="col-md-2">
                                                            <img src="{{ asset('icon/bell.svg') }}"
                                                                class="img-responsive" width="30px">
                                                        </div>
                                                        <div class="col-md-10" style="margin-left: 10px">
                                                            <a href="#"
                                                                class="link stretched-link text-decoration-none"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#modalPemberitahuan">
                                                                <h5 class="card-title">Pengumuman</h5>
                                                            </a>
                                                            <p class="card-text">Pemberitahuan.</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-sm-6 mt-2">
                                                <div class="card" id="card-hover">
                                                    <div class="card-body d-flex">
                                                        <div class="col-md-2">
                                                            <img src="{{ asset('icon/camera.svg') }}"
                                                                class="img-responsive" width="30px">
                                                        </div>
                                                        <div class="col-md-10" style="margin-left: 10px">
                                                            <a href="#" id="btnAbsen"
                                                                class="link stretched-link text-decoration-none"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#modalAbsen">
                                                                <h5 class="card-title">Absen</h5>
                                                            </a>
                                                            <p class="card-text">Absensi.</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            @can('View RekapAbsensi')
                                            <div class="col-sm-6 mt-2">
                                                <div class="card" id="card-hover">
                                                    <div class="card-body d-flex">
                                                        <div class="col-md-2">
                                                            <img src="{{ asset('icon/archive.svg') }}"
                                                                class="img-responsive" width="30px">
                                                        </div>
                                                        <div class="col-md-10" style="margin-left: 10px">
                                                            <a href="/rekapitulasiabsen"
                                                                class="link stretched-link text-decoration-none">
                                                                <h5 class="card-title">Rekapitulasi Absensi</h5>
                                                            </a>
                                                            <p class="card-text">Data Rekapitulasi Absen Karyawan.
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            @endcan
                                            <div class="col-sm-6 mt-2">
                                                <div class="card" id="card-hover">
                                                    <div class="card-body d-flex">
                                                        <div class="col-md-2">
                                                            <img src="{{ asset('icon/calendar.svg') }}"
                                                                class="img-responsive" width="30px">
                                                        </div>
                                                        <div class="col-md-10" style="margin-left: 10px">
                                                            <a href="/absensi/karyawan"
                                                                class="link stretched-link text-decoration-none">
                                                                <h5 class="card-title">Catatan Absensi
                                                                </h5>
                                                            </a>
                                                            <p class="card-text">Absensi anda pada bulan ini.</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-sm-6 mt-2">
                                                <div class="card" id="card-hover">
                                                    <div class="card-body d-flex">
                                                        <div class="col-md-2">
                                                            <img src="{{ asset('icon/clock.svg') }}"
                                                                class="img-responsive" width="30px">
                                                        </div>
                                                        <div class="col-md-10" style="margin-left: 10px">
                                                            <a href="/pengajuancuti"
                                                                class="link stretched-link text-decoration-none">
                                                                <h5 class="card-title">Pengajuan Cuti</h5>
                                                            </a>
                                                            <p class="card-text">Klik disini untuk pengajuan cuti.
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-sm-6 mt-2">
                                                <div class="card" id="card-hover">
                                                    <div class="card-body d-flex">
                                                        <div class="col-md-2">
                                                            <img src="{{ asset('icon/feather.svg') }}"
                                                                class="img-responsive" width="30px">
                                                        </div>
                                                        <div class="col-md-10" style="margin-left: 10px">
                                                            <a href="/pengajuanbarang"
                                                                class="link stretched-link text-decoration-none">
                                                                <h5 class="card-title">Pengajuan Barang</h5>
                                                            </a>
                                                            <p class="card-text">Klik disini untuk pengajuan
                                                                barang.</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-sm-6 mt-2">
                                                <div class="card" id="card-hover">
                                                    <div class="card-body d-flex">
                                                        <div class="col-md-2">
                                                            <img src="{{ asset('icon/send.svg') }}"
                                                                class="img-responsive" width="30px">
                                                        </div>
                                                        <div class="col-md-10" style="margin-left: 10px">
                                                            <a href="/suratperjalanan"
                                                                class="link stretched-link text-decoration-none">
                                                                <h5 class="card-title">Pengajuan SPJ</h5>
                                                            </a>
                                                            <p class="card-text">Klik disini untuk pengajuan SPJ.
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-sm-6 mt-2">
                                                <div class="card" id="card-hover">
                                                    <div class="card-body d-flex">
                                                        <div class="col-md-2">
                                                            <img src="{{ asset('icon/dollar-sign.svg') }}"
                                                                class="img-responsive" width="30px">
                                                        </div>
                                                        <div class="col-md-10" style="margin-left: 10px">
                                                            <a href="/tunjangan"
                                                                class="link stretched-link text-decoration-none">
                                                                <h5 class="card-title">Gaji & Tunjangan</h5>
                                                            </a>
                                                            <p class="card-text">Gaji & Tunjangan Karyawan.</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            @can("Managament Gaji")
                                            <div class="col-sm-6 mt-2">
                                                <div class="card" id="card-hover">
                                                    <div class="card-body d-flex">
                                                        <div class="col-md-2">
                                                            <img src="{{ asset('icon/dollar-sign.svg') }}"
                                                                class="img-responsive" width="30px">
                                                        </div>
                                                        <div class="col-md-10" style="margin-left: 10px">
                                                            <a href="{{route('gaji.index')}}"
                                                                class="link stretched-link text-decoration-none">
                                                                <h5 class="card-title">Update Gaji Karyawan</h5>
                                                            </a>
                                                            <p class="card-text">Update Gaji Karyawan.</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            @endcan
                                            <div class="col-sm-6 mt-2">
                                                <div class="card" id="card-hover">
                                                    <div class="card-body d-flex">
                                                        <div class="col-md-2">
                                                            <img src="{{ asset('icon/paperclip.svg') }}"
                                                                class="img-responsive" width="30px">
                                                        </div>
                                                        <div class="col-md-10" style="margin-left: 10px">
                                                            <a href="/pengajuanizin"
                                                                class="link stretched-link text-decoration-none">
                                                                <h5 class="card-title">Pengajuan Izin</h5>
                                                            </a>
                                                            <p class="card-text">Pengajuan Izin 3 Jam.</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-sm-6 mt-2">
                                                <div class="card" id="card-hover">
                                                    <div class="card-body d-flex">
                                                        <div class="col-md-2">
                                                            <img src="{{ asset('icon/aperture.svg') }}"
                                                                class="img-responsive" width="30px">
                                                        </div>
                                                        <div class="col-md-10" style="margin-left: 10px">
                                                            <a href="/lembur"
                                                                class="link stretched-link text-decoration-none">
                                                                <h5 class="card-title">Lembur</h5>
                                                            </a>
                                                            <p class="card-text">Lembur.</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-sm-6 mt-2">
                                                <div class="card" id="card-hover">
                                                    <div class="card-body d-flex">
                                                        <div class="col-md-2">
                                                            <i class="fa-solid fa-square-poll-vertical" style="font-size: 30px;"></i>
                                                        </div>
                                                        <div class="col-md-10" style="margin-left: 10px">
                                                            <a href="{{ route('surveykepuasan.index') }}"
                                                                class="link stretched-link text-decoration-none">
                                                                <h5 class="card-title">Survey Kepuasan</h5>
                                                            </a>
                                                            <p class="card-text">survey kepuasan pelayanan ITSM.</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @can('Fitur Menu Peserta')
                            <div class="col-md-12 mt-1">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="text-center card-title">Peserta</h5>
                                        <div class="row">
                                            @can('View Peserta')
                                            <div class="col-sm-6 mt-2">
                                                <div class="card" id="card-hover">
                                                    <div class="card-body d-flex">
                                                        <div class="col-md-2">
                                                            <img src="{{ asset('icon/table.svg') }}"
                                                                class="img-responsive" width="30px">
                                                        </div>
                                                        <div class="col-md-10" style="margin-left: 10px">
                                                            <a href="/peserta"
                                                                class="link stretched-link text-decoration-none">
                                                                <h5 class="card-title">Data Peserta</h5>
                                                            </a>
                                                            <p class="card-text">Data Peserta yang mengikuti kelas.
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            @endcan
                                            @can('View Registrasi')
                                            <div class="col-sm-6 mt-2">
                                                <div class="card" id="card-hover">
                                                    <div class="card-body d-flex">
                                                        <div class="col-md-2">
                                                            <img src="{{ asset('icon/user-check.svg') }}"
                                                                class="img-responsive" width="30px">
                                                        </div>
                                                        <div class="col-md-10" style="margin-left: 10px"
                                                            id="">
                                                            <a href="/registrasi"
                                                                class="link stretched-link text-decoration-none">
                                                                <h5 class="card-title">Registrasi</h5>
                                                            </a>
                                                            <p class="card-text">Registrasi peserta kelas.</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            @endcan
                                            @can('View Perusahaan')
                                            <div class="col-sm-6 mt-2">
                                                <div class="card" id="card-hover">
                                                    <div class="card-body d-flex">
                                                        <div class="col-md-2">
                                                            <img src="{{ asset('icon/briefcase.svg') }}"
                                                                class="img-responsive" width="30px">
                                                        </div>
                                                        <div class="col-md-10" style="margin-left: 10px">
                                                            <a href="/perusahaan"
                                                                class="link stretched-link text-decoration-none">
                                                                <h5 class="card-title">Perusahaan</h5>
                                                            </a>
                                                            <p class="card-text">Data Perusahaan.</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            @endcan
                                            @can('View RegistExam')
                                            <div class="col-sm-6 mt-2">
                                                <div class="card" id="card-hover">
                                                    <div class="card-body d-flex">
                                                        <div class="col-md-2">
                                                            <img src="{{ asset('icon/list-check.svg') }}"
                                                                class="img-responsive" width="30px">
                                                        </div>
                                                        <div class="col-md-10" style="margin-left: 10px">
                                                            <a href="/registexam"
                                                                class="link stretched-link text-decoration-none">
                                                                <h5 class="card-title">Registrasi Exam</h5>
                                                            </a>
                                                            <p class="card-text">Data Registrasi Kelas Exam.</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            @endcan
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endcan
                            <div class="col-md-12 mt-1">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="text-center card-title">IT Service Management</h5>
                                        <div class="row">
                                            @if (Auth::user()->karyawan && Auth::user()->karyawan->divisi === 'IT Service Management')
                                            <div class="col-sm-6 mt-2">
                                                <div class="card" id="card-hover">
                                                    <div class="card-body d-flex">
                                                        <div class="col-md-2">
                                                            <i class="fa-regular fa-file" style="font-size: 30px;"></i>
                                                        </div>
                                                        <div class="col-md-10" style="margin-left: 10px">
                                                            <a href="{{ route('index.laporanInsiden') }}"
                                                                class="link stretched-link text-decoration-none">
                                                                <h5 class="card-title">Laporan Insiden</h5>
                                                            </a>
                                                            <p class="card-text">Laporkan Insiden dan Risiko disekitar anda.</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-sm-6 mt-2">
                                                <div class="card" id="card-hover">
                                                    <div class="card-body d-flex">
                                                        <div class="col-md-2">
                                                            <img src="{{ asset('icon/calendar.svg') }}"
                                                                class="img-responsive" width="30px">
                                                        </div>
                                                        <div class="col-md-10" style="margin-left: 10px">
                                                            <a href="/kanban"
                                                                class="link stretched-link text-decoration-none">
                                                                <h5 class="card-title">Papan Kanban</h5>
                                                            </a>
                                                            <p class="card-text">untuk menejemen projek.
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            @endif
                                            <div class="col-sm-6 mt-2">
                                                <div class="card" id="card-hover">
                                                    <div class="card-body d-flex">
                                                        <div class="col-md-2">
                                                            <i class="fa-regular fa-file" style="font-size: 30px;"></i>
                                                        </div>
                                                        <div class="col-md-10" style="margin-left: 10px">
                                                            <a href="{{ route('tickets.index') }}"
                                                                class="link stretched-link text-decoration-none">
                                                                <h5 class="card-title">IT Helpdesk (Ticketing)</h5>
                                                            </a>
                                                            <p class="card-text">Laporkan Insiden dan Risiko yang anda alami.</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                    <div class="col-md-12 col-sm-12 col-xs-12 col-lg-6 col-xl-6">
                        <div class="row">
                            @can('Fitur Menu RKM')
                            <div class="row">
                                <div class="col-md-12 mt-1">
                                    <div class="card">
                                        <div class="card-body">
                                            <h5 class="text-center card-title">Rencana Kelas Mingguan</h5>
                                            <div class="row">
                                                @can('View RKM')
                                                <div class="col-sm-6 mt-2">
                                                    <div class="card" id="card-hover">
                                                        <div class="card-body d-flex">
                                                            <div class="col-md-2">
                                                                <img src="{{ asset('icon/calendar.svg') }}"
                                                                    class="img-responsive" width="30px">
                                                            </div>
                                                            <div class="col-md-10" style="margin-left: 10px">
                                                                <a href="/rkm"
                                                                    class="link stretched-link text-decoration-none">
                                                                    <h5 class="card-title">Rencana Kelas Mingguan
                                                                    </h5>
                                                                </a>
                                                                <p class="card-text">Rencana kelas Training.</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                @endcan
                                                @can('View Materi')
                                                <div class="col-sm-6 mt-2">
                                                    <div class="card" id="card-hover">
                                                        <div class="card-body d-flex">
                                                            <div class="col-md-2">
                                                                <img src="{{ asset('icon/book-open.svg') }}"
                                                                    class="img-responsive" width="30px">
                                                            </div>
                                                            <div class="col-md-10" style="margin-left: 10px">
                                                                <a href="/materi"
                                                                    class="link stretched-link text-decoration-none">
                                                                    <h5 class="card-title">Materi</h5>
                                                                </a>
                                                                <p class="card-text">Data Materi.</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                @endcan
                                                @can('View Feedback')
                                                <div class="col-sm-6 mt-2">
                                                    <div class="card" id="card-hover">
                                                        <div class="card-body d-flex">
                                                            <div class="col-md-2">
                                                                <img src="{{ asset('icon/file-text.svg') }}"
                                                                    class="img-responsive" width="30px">
                                                            </div>
                                                            <div class="col-md-10" style="margin-left: 10px">
                                                                <a href="/feedback"
                                                                    class="link stretched-link text-decoration-none">
                                                                    <h5 class="card-title">Feedback</h5>
                                                                </a>
                                                                <p class="card-text">Feedback Pelayanan.</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                @endcan
                                                @can('View Exam')
                                                <div class="col-sm-6 mt-2">
                                                    <div class="card" id="card-hover">
                                                        <div class="card-body d-flex">
                                                            <div class="col-md-2">
                                                                <img src="{{ asset('icon/assept-document.svg') }}"
                                                                    class="img-responsive" width="30px">
                                                            </div>
                                                            <div class="col-md-10" style="margin-left: 10px">
                                                                <a href="/exam"
                                                                    class="link stretched-link text-decoration-none">
                                                                    <h5 class="card-title">Pengajuan Exam</h5>
                                                                </a>
                                                                <p class="card-text">Pengajuan Exam.</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                @endcan
                                                @can('View Absensi&Sertifikat')
                                                <div class="col-sm-6 mt-2">
                                                    <div class="card" id="card-hover">
                                                        <div class="card-body d-flex">
                                                            <div class="col-md-2">
                                                                <img src="{{ asset('icon/upload.svg') }}"
                                                                    class="img-responsive" width="30px">
                                                            </div>
                                                            <div class="col-md-10" style="margin-left: 10px">
                                                                <a href="/rkm/upload/page"
                                                                    class="link stretched-link text-decoration-none">
                                                                    <h5 class="card-title">Upload</h5>
                                                                </a>
                                                                <p class="card-text">Upload PDF Absensi &
                                                                    Sertifikat Peserta.</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                @endcan
                                                @can('View ListExam')
                                                <div class="col-sm-6 mt-2">
                                                    <div class="card" id="card-hover">
                                                        <div class="card-body d-flex">
                                                            <div class="col-md-2">
                                                                <img src="{{ asset('icon/list-check.svg') }}"
                                                                    class="img-responsive" width="30px">
                                                            </div>
                                                            <div class="col-md-10" style="margin-left: 10px">
                                                                <a href="/listexams"
                                                                    class="link stretched-link text-decoration-none">
                                                                    <h5 class="card-title">List Exam</h5>
                                                                </a>
                                                                <p class="card-text">Data Exam.</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                @endcan
                                                @can('View AnalisisRKM')
                                                <div class="col-sm-6 mt-2">
                                                    <div class="card" id="card-hover">
                                                        <div class="card-body d-flex">
                                                            <div class="col-md-2">
                                                                <img src="{{ asset('icon/stats.svg') }}"
                                                                    class="img-responsive" width="30px">
                                                            </div>
                                                            <div class="col-md-10" style="margin-left: 10px">
                                                                <a href="/kelasanalisis"
                                                                    class="link stretched-link text-decoration-none">
                                                                    <h5 class="card-title">Kelas Analisis</h5>
                                                                </a>
                                                                <p class="card-text">Analisis Rencana Kelas
                                                                    Mingguan.</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                @endcan

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endcan
                            @can('Fitur Menu Finance')
                            <div class="row">
                                {{-- RKM --}}
                                <div class="col-md-12 mt-1">
                                    <div class="card">
                                        <div class="card-body">
                                            <h5 class="text-center card-title">Finance</h5>
                                            <div class="row">
                                                <div class="col-sm-6 mt-2">
                                                    <div class="card" id="card-hover">
                                                        <div class="card-body d-flex">
                                                            <div class="col-md-2">
                                                                <img src="{{ asset('icon/calendar.svg') }}"
                                                                    class="img-responsive" width="30px">
                                                            </div>
                                                            <div class="col-md-10" style="margin-left: 10px">
                                                                <a href="/kanban"
                                                                    class="link stretched-link text-decoration-none">
                                                                    <h5 class="card-title">Papan Kanban</h5>
                                                                </a>
                                                                <p class="card-text">untuk menejemen projek.
                                                                </p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                @can('View CC')
                                                <div class="col-sm-6 mt-2">
                                                    <div class="card" id="card-hover">
                                                        <div class="card-body d-flex">
                                                            <div class="col-md-2">
                                                                <img src="{{ asset('icon/credit-card.svg') }}"
                                                                    class="img-responsive" width="30px">
                                                            </div>
                                                            <div class="col-md-10"
                                                                style="margin-left: 10px">
                                                                <a href="/creditcard"
                                                                    class="link stretched-link text-decoration-none">
                                                                    <h5 class="card-title">Credit Card</h5>
                                                                </a>
                                                                <p class="card-text">Data Credit Card.</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                @endcan
                                                @can('View Tunjangan')
                                                <div class="col-sm-6 mt-2">
                                                    <div class="card" id="card-hover">
                                                        <div class="card-body d-flex">
                                                            <div class="col-md-2">
                                                                <img src="{{ asset('icon/credit-card.svg') }}"
                                                                    class="img-responsive" width="30px">
                                                            </div>
                                                            <div class="col-md-10"
                                                                style="margin-left: 10px">
                                                                <a href="/tunjangangenerate"
                                                                    class="link stretched-link text-decoration-none">
                                                                    <h5 class="card-title">Hitung Tunjangan
                                                                    </h5>
                                                                </a>
                                                                <p class="card-text">Data Tunjangan
                                                                    Karyawan</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                @endcan
                                                @can('View HitungLembur')
                                                <div class="col-sm-6 mt-2">
                                                    <div class="card" id="card-hover">
                                                        <div class="card-body d-flex">
                                                            <div class="col-md-2">
                                                                <img src="{{ asset('icon/credit-card.svg') }}"
                                                                    class="img-responsive" width="30px">
                                                            </div>
                                                            <div class="col-md-10"
                                                                style="margin-left: 10px">
                                                                <a href="/overtime"
                                                                    class="link stretched-link text-decoration-none">
                                                                    <h5 class="card-title">Hitung Lembur
                                                                    </h5>
                                                                </a>
                                                                <p class="card-text">Data Lembur Karyawan
                                                                </p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                @endcan
                                                @can('View Souvenir')
                                                <div class="col-sm-6 mt-2">
                                                    <div class="card" id="card-hover">
                                                        <div class="card-body d-flex">
                                                            <div class="col-md-2">
                                                                <img src="{{ asset('icon/award.svg') }}"
                                                                    class="img-responsive" width="30px">
                                                            </div>
                                                            <div class="col-md-10"
                                                                style="margin-left: 10px">
                                                                <a href="/souvenir"
                                                                    class="link stretched-link text-decoration-none">
                                                                    <h5 class="card-title">Souvenir</h5>
                                                                </a>
                                                                <p class="card-text">Data Souvenir.</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                @endcan
                                                @can('View Outstanding')
                                                <div class="col-sm-6 mt-2">
                                                    <div class="card" id="card-hover">
                                                        <div class="card-body d-flex">
                                                            <div class="col-md-2">
                                                                <img src="{{ asset('icon/bookmark.svg') }}"
                                                                    class="img-responsive" width="30px">
                                                            </div>
                                                            <div class="col-md-10"
                                                                style="margin-left: 10px">
                                                                <a href="/outstanding"
                                                                    class="link stretched-link text-decoration-none">
                                                                    <h5 class="card-title">Outstanding</h5>
                                                                </a>
                                                                <p class="card-text">Data Outstanding.</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                @endcan
                                                @can('View PaymantAdvance')
                                                <div class="col-sm-6 mt-2">
                                                    <div class="card" id="card-hover">
                                                        <div class="card-body d-flex">
                                                            <div class="col-md-2">
                                                                <i class="fa fa-cart-shopping"
                                                                    style="font-size: 30px;"></i>
                                                            </div>
                                                            <div class="col-md-10" style="margin-left: 10px">
                                                                <a href="/paymantAdvance"
                                                                    class="link stretched-link text-decoration-none">
                                                                    <h5 class="card-title">Payment Advance</h5>
                                                                </a>
                                                                <p class="card-text">Pengajuan Payment Advance.</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                @endcan
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endcan
                            <div class="row">
                                <div class="col-md-12 mt-1">
                                    <div class="card">
                                        <div class="card-body">
                                            <h5 class="text-center card-title">Performance Assesment</h5>
                                            <div class="row">
                                                @if (auth()->user()->jabatan === "HRD" || auth()->user()->jabatan === 'GM' || auth()->user()->jabatan === "Direktur Utama")
                                                <div class="col-sm-6 mt-2">
                                                    <div class="card" id="card-hover">
                                                        <div class="card-body d-flex">
                                                            <div class="col-md-2">
                                                                <i class="fa fa-ranking-star" style="font-size: 30px;"></i>
                                                            </div>
                                                            <div class="col-md-10" style="margin-left: 10px">
                                                                <a href="{{ route('berandaKPI.get') }}" class="link stretched-link text-decoration-none">
                                                                    <h5 class="card-title">Penilaian</h5>
                                                                </a>
                                                                <p class="card-text">Data Penilaian Semua Karyawan.</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                @endif

                                                @php
                                                $id_karyawan = Auth()->user()->karyawan_id;
                                                $month = \Carbon\Carbon::now()->month;
                                                $year = \Carbon\Carbon::now()->year;
                                                $Q = '[Kesalahan Program]';

                                                if ($month >= 1 && $month <= 3) {
                                                    $Q='Q1' ;
                                                    } elseif ($month>= 4 && $month <= 6) {
                                                        $Q='Q2' ;
                                                        } elseif ($month>= 7 && $month <= 9) {
                                                            $Q='Q3' ;
                                                            } elseif ($month>= 10 && $month <= 12) {
                                                                $Q='Q4' ;
                                                                }
                                                                @endphp

                                                                <div class="col-sm-6 mt-2">
                                                                <div class="card" id="card-hover">
                                                                    <div class="card-body d-flex">
                                                                        <div class="col-md-2">
                                                                            <img src="{{ asset('icon/bookOpen.svg') }}" class="img-responsive" width="30px">
                                                                        </div>
                                                                        <div class="col-md-10" style="margin-left: 10px">
                                                                            <a href="{{ '/penilaian360/index/' . $id_karyawan }}" class="link stretched-link text-decoration-none">
                                                                                <h5 class="card-title">Penilaian 360</h5>
                                                                            </a>
                                                                            <p class="card-text">Data Penilaian {{ $Q }} Anda Tahun {{ $year }}.</p>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                            </div>
                                            <div class="col-sm-6 mt-2">
                                                <div class="card" id="card-hover">
                                                    <div class="card-body d-flex">
                                                        <div class="col-md-2">
                                                            <img src="{{ asset('icon/bookOpen.svg') }}" class="img-responsive" width="30px">
                                                        </div>
                                                        <div class="col-md-10" style="margin-left: 10px">
                                                            <a href="{{ '/getFormPenilaianUser/' . $id_karyawan }}" class="link stretched-link text-decoration-none">
                                                                <h5 class="card-title">Evaluator</h5>
                                                            </a>
                                                            <p class="card-text">
                                                                Form Penilaian {{ $Q }} Yang Harus Anda Evaluasi di Tahun {{ $year }} .
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @can('Fitur Menu Education')
                        <div class="row">
                            <div class="col-md-12 mt-1">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="text-center card-title">Education</h5>
                                        <div class="row">
                                            <div class="col-sm-6 mt-2">
                                                <div class="card" id="card-hover">
                                                    <div class="card-body d-flex">
                                                        <div class="col-md-2">
                                                            <img src="{{ asset('icon/calendar.svg') }}"
                                                                class="img-responsive" width="30px">
                                                        </div>
                                                        <div class="col-md-10" style="margin-left: 10px">
                                                            <a href="/kanban"
                                                                class="link stretched-link text-decoration-none">
                                                                <h5 class="card-title">Papan Kanban</h5>
                                                            </a>
                                                            <p class="card-text">untuk menejemen projek.
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            @can('View TunjanganEducation')
                                            <div class="col-sm-6 mt-2">
                                                <div class="card" id="card-hover">
                                                    <div class="card-body d-flex">
                                                        <div class="col-md-2">
                                                            <img src="{{ asset('icon/table.svg') }}"
                                                                class="img-responsive" width="30px">
                                                        </div>
                                                        <div class="col-md-10"
                                                            style="margin-left: 10px">
                                                            <a href="/tunjanganEducation"
                                                                class="link stretched-link text-decoration-none">
                                                                <h5 class="card-title">Tunjangan
                                                                    Education
                                                                </h5>
                                                            </a>
                                                            <p class="card-text">Data Tunjangan
                                                                Education.
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            @endcan
                                            @can('View RekapInstruktur')
                                            <div class="col-sm-6 mt-2">
                                                <div class="card" id="card-hover">
                                                    <div class="card-body d-flex">
                                                        <div class="col-md-2">
                                                            <img src="{{ asset('icon/target.svg') }}"
                                                                class="img-responsive" width="30px">
                                                        </div>
                                                        <div class="col-md-10"
                                                            style="margin-left: 10px">
                                                            <a href="/rekapmengajarinstruktur"
                                                                class="link stretched-link text-decoration-none">
                                                                <h5 class="card-title">Rekap Mengajar
                                                                    Instruktur
                                                                </h5>
                                                            </a>
                                                            <p class="card-text">Data rekapan mengajar
                                                                instruktur.
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            @endcan
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endcan
                        @can('Fitur Menu Office')
                        <div class="row">
                            <div class="col-md-12 mt-1">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="text-center card-title">Office</h5>
                                        <div class="row">
                                            @can('View Inventaris')
                                            <div class="col-sm-6 mt-2">
                                                <div class="card" id="card-hover">
                                                    <div class="card-body d-flex">
                                                        <div class="col-md-2">
                                                            <img src="{{ asset('icon/file-text.svg') }}"
                                                                class="img-responsive" width="30px">
                                                        </div>
                                                        <div class="col-md-10"
                                                            style="margin-left: 10px">
                                                            <a href="{{ route('IndexInventaris') }}"
                                                                class="link stretched-link text-decoration-none">
                                                                <h5 class="card-title">Inventaris</h5>
                                                            </a>
                                                            <p class="card-text">Data Inventaris
                                                                Inixindo.
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            @endcan
                                            @can('View Klaim')
                                            <div class="col-sm-6 mt-2">
                                                <div class="card" id="card-hover">
                                                    <div class="card-body d-flex">
                                                        <div class="col-md-2">
                                                            <img src="{{ asset('icon/paperclip.svg') }}"
                                                                class="img-responsive" width="30px">
                                                        </div>
                                                        <div class="col-md-10" style="margin-left: 10px">
                                                            <a href="/pengajuan-klaim"
                                                                class="link stretched-link text-decoration-none">
                                                                <h5 class="card-title">Pengajuan Klaim</h5>
                                                            </a>
                                                            <p class="card-text">Pengajuan Absen, Jam Kerja, & Cuti</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            @endcan
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endcan
                        @can('Fitur CRM')
                        <div class="row">
                            <div class="col-md-12 mt-1">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="text-center card-title">Customer Relationship Management</h5>
                                        <div class="row">
                                            <div class="col-sm-6 mt-2">
                                                <div class="card" id="card-hover">
                                                    <div class="card-body d-flex">
                                                        <div class="col-md-2">
                                                            <img src="{{ asset('icon/credit-card.svg') }}"
                                                                class="img-responsive"
                                                                width="30px">
                                                        </div>
                                                        <div class="col-md-10"
                                                            style="margin-left: 10px">
                                                            <a href="{{ route('CRM.index') }}"
                                                                class="link stretched-link text-decoration-none">
                                                                <h5 class="card-title">Fitur CRM
                                                                </h5>
                                                            </a>
                                                            <p class="card-text">Masuk Fitur CRM
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endcan
                        @can('Fitur Menu Manajemen')
                        <div class="row">
                            <div class="col-md-12 mt-1">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="text-center card-title">Management</h5>
                                        <div class="row">
                                            <div class="col-sm-6 mt-2">
                                                <div class="card" id="card-hover">
                                                    <div class="card-body d-flex">
                                                        <div class="col-md-2">
                                                            <img src="{{ asset('icon/target.svg') }}"
                                                                class="img-responsive"
                                                                width="30px">
                                                        </div>
                                                        <div class="col-md-10"
                                                            style="margin-left: 10px">
                                                            <a href="/target"
                                                                class="link stretched-link text-decoration-none">
                                                                <h5 class="card-title">Set Target
                                                                </h5>
                                                            </a>
                                                            <p class="card-text">Manajemen Target.
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endcan

                    </div>
                </div>
            </div>
        </div>
        <div class="tab-pane fade" id="pills-admin" role="tabpanel" aria-labelledby="pills-admin-tab">
            <div class="row">
                <div class="col-md-12 mt-1">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="text-center card-title">Fitur Menu Development</h5>
                            <div class="row">
                                @can('Akses Development')
                                <div class="col-sm-6 mt-2">
                                    <div class="card" id="card-hover">
                                        <div class="card-body d-flex">
                                            <div class="col-md-2">
                                                <img src="{{ asset('icon/bell.svg') }}"
                                                    class="img-responsive" width="30px">
                                            </div>
                                            <div class="col-md-10" style="margin-left: 10px">
                                                <a href="/inixcoffeeloglarapelixb95"
                                                    class="link stretched-link text-decoration-none">
                                                    <h5 class="card-title">logs</h5>
                                                </a>
                                                <p class="card-text">logs prod.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6 mt-2">
                                    <div class="card" id="card-hover">
                                        <div class="card-body d-flex">
                                            <div class="col-md-2">
                                                <img src="{{ asset('icon/target.svg') }}"
                                                    class="img-responsive" width="30px">
                                            </div>
                                            <div class="col-md-10" style="margin-left: 10px">
                                                <a href="/permissions"
                                                    class="link stretched-link text-decoration-none">
                                                    <h5 class="card-title">Setting Permission</h5>
                                                </a>
                                                <p class="card-text">Permissions.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6 mt-2">
                                    <div class="card" id="card-hover">
                                        <div class="card-body d-flex">
                                            <div class="col-md-2">
                                                <img src="{{ asset('icon/target.svg') }}"
                                                    class="img-responsive" width="30px">
                                            </div>
                                            <div class="col-md-10" style="margin-left: 10px">
                                                <a href="/roles"
                                                    class="link stretched-link text-decoration-none">
                                                    <h5 class="card-title">Setting Role</h5>
                                                </a>
                                                <p class="card-text">Roles.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6 mt-2">
                                    <div class="card" id="card-hover">
                                        <div class="card-body d-flex">
                                            <div class="col-md-2">
                                                <img src="{{ asset('icon/target.svg') }}"
                                                    class="img-responsive" width="30px">
                                            </div>
                                            <div class="col-md-10" style="margin-left: 10px">
                                                <a href="/userRolePermissions"
                                                    class="link stretched-link text-decoration-none">
                                                    <h5 class="card-title">Setting User</h5>
                                                </a>
                                                <p class="card-text">Users.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endcan
                                <div class="col-sm-6 mt-2">
                                    <div class="card" id="card-hover">
                                        <div class="card-body d-flex">
                                            <div class="col-md-2">
                                                <img src="{{ asset('icon/bell.svg') }}"
                                                    class="img-responsive" width="30px">
                                            </div>
                                            <div class="col-md-10" style="margin-left: 10px">
                                                <a href="/user-dropdown"
                                                    class="link stretched-link text-decoration-none">
                                                    <h5 class="card-title">Shortcut</h5>
                                                </a>
                                                <p class="card-text">shortcut.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                @if (auth()->user()->jabatan === 'Koordinator ITSM')
                                <div class="col mt-12">
                                    <div class="mt-5 mb-3">
                                        Uptime Monitoring
                                    </div>
                                    <div class="nav nav-tabs mt-3" role="tablist">
                                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#nav-inixcoffee" type="button" role="tab" aria-controls="nav-inixcoffee" aria-selected="false">Inixcoffee</button>
                                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#nav-inixlatte" type="button" role="tab" aria-controls="nav-inixlatte" aria-selected="false">Inixlatte</button>
                                    </div>
                                    <div class="tab-content">
                                        <div class="tab-pane fade" id="nav-inixcoffee" role="tabpanel">
                                            <div class="card">
                                                <div class="card-body">
                                                    <div class="card-title">uptime monitoring INIXCOFFEE</div>
                                                    <div class="p-4">
                                                        <canvas id="uptimeChartInixcoffee" height="350"></canvas>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="tab-pane fade" id="nav-inixlatte" role="tabpanel">
                                            <div class="card">
                                                <div class="card-body">
                                                    <div class="card-title">uptime monitoring INIXLATTE</div>
                                                    <div class="p-4">
                                                        <canvas id="uptimeChartInixlatte" height="350"></canvas>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="tab-pane fade" id="pills-dashboard" role="tabpanel" aria-labelledby="pills-dashboard-tab">
            @include('partials.dashboard')
        </div>
        {{-- </div> --}}
    </main>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js" integrity="sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    <script src="{{ asset('js/webcam.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/locale/id.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.js"></script>
    {{-- SweetAlert2 --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.getElementById('logout-link').addEventListener('click', function(e) {
            e.preventDefault();

            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Anda akan keluar dari aplikasi",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, keluar',
                cancelButtonText: 'Batal',
                showClass: {
                    popup: 'animate__animated animate__fadeInDown animate__faster'
                },
                hideClass: {
                    popup: 'animate__animated animate__fadeOutUp animate__faster'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('logout-form').submit();
                }
            });

        });
    </script>
    <script>
        let chartDataCache = null;

        $(document).ready(function() {
            handleNotificationDismissal();
            // initializeYearlySales();

            $('#tahun').change(function() {
                initializeYearlySales();
            });
            cekip();
            cekjabatan();
            let activeSubTabId = '#sales-tab-pane';
            let activeNestedTabId = '#pills-perquartal';

            // Handle Home button click with fade effect
            $('#pills-home-tab').click(function() {
                $('#loadingModal').modal('show');
                $('.tab-pane.show').fadeOut(100, function() {
                    $(this).removeClass('show active');
                    // After fadeOut, show the home tab with fadeIn
                    $('#pills-home').fadeIn(100).addClass('show active');
                });
                setTimeout(() => {
                    $('#loadingModal').modal('hide');
                }, 1000);
            });

            // Pasang handler click yang memanggil fungsi
            $('#pills-dashboard-tab').on('click', function() {
                loadDashboard().catch(function(err) {
                    // optional: tangani error global di sini
                    console.error(err);
                });
            });
            $('#pills-admin-tab').click(function() {
                $('#loadingModal').modal('show');
                // initializeYearlySales();
                $('.tab-pane.show').fadeOut(100, function() {
                    $(this).removeClass('show active');

                    // After fadeOut, show the dashboard tab with fadeIn
                    $('#pills-admin').fadeIn(100).addClass('show active');
                    setTimeout(() => {
                        $('#loadingModal').modal('hide');
                    }, 1000);
                });
            });
            // console.log(progress, carprogress);

            // Saat tab berubah
            $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function(e) {
                const target = $(e.target).attr('data-bs-target');
                if (target === '#nav-inixcoffee') {
                    ajaxUptime(target, 'https://192.168.95.60:8001/');
                } else if (target === '#nav-inixlatte') {
                    ajaxUptime(target, 'http://192.168.95.60:8002/');
                }
            });
        });

        function ajaxUptime(target, url) {
            $.ajax({
                url: "{{ route('activity.log.chart') }}",
                method: "GET",
                dataType: "json",
                success: function(response) {
                    console.log("Data mentah dari server:", response); // Tambahkan ini
                    if (!response || typeof response !== 'object' || response.error) {
                        console.error("Data tidak valid:", response);
                        return;
                    }
                    chartDataCache = response;

                    // Coba render chart yang aktif saat ini
                    const activeTab = $('.nav-link.active').attr('data-bs-target');
                    if (activeTab === '#nav-inixcoffee') {
                        renderChartIfNeeded('uptimeChartInixcoffee', 'https://192.168.95.60:8001/');
                    } else if (activeTab === '#nav-inixlatte') {
                        renderChartIfNeeded('uptimeChartInixlatte', 'http://192.168.95.60:8002/');
                    }
                },
                error: function(xhr) {
                    console.error("AJAX error:", xhr.responseText);
                    alert("Gagal memuat data chart: " + xhr.status + " - " + xhr.statusText);
                }
            });
        }

        function renderChartIfNeeded(canvasId, url) {
            const ctx = document.getElementById(canvasId);
            console.log(ctx);
            console.log(chartDataCache);
            console.log(chartDataCache[url]);
            if (!ctx || !chartDataCache || !chartDataCache[url]) {
                return;
            }

            if (ctx.chartInstance) return;

            const data = chartDataCache[url];
            console.log("Data untuk URL:", url, data); // Tambahkan ini
            if (!data || !Array.isArray(data.labels) || !Array.isArray(data.response_times) || !Array.isArray(data.statuses)) {
                console.warn("Data tidak lengkap untuk URL:", url);
                return;
            }
            console.log("Status asli:", data.statuses); // Tambahkan ini
            const upData = data.statuses.map(s => s === true ? 1 : null);
            const downData = data.statuses.map(s => s === false ? 1 : null);
            console.log("downData yang dihasilkan:", downData); // Tambahkan ini
            console.log(data.statuses);
            const chart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: data.labels,
                    datasets: [{
                        label: 'Response Time (ms)',
                        data: data.response_times,
                        backgroundColor: 'rgba(54,162,235,0.5)',
                        borderColor: 'rgba(54,162,235,1)',
                        borderWidth: 1,
                        yAxisID: 'y'
                    }, {
                        label: 'UP',
                        type: 'line',
                        data: upData,
                        borderColor: 'rgba(40,167,69,1)',
                        borderWidth: 2,
                        tension: 0.3,
                        fill: false,
                        yAxisID: 'y1'
                    }, {
                        label: 'DOWN',
                        type: 'line',
                        data: downData,
                        borderColor: 'rgba(220,53,69,1)',
                        borderWidth: 2,
                        tension: 0.3,
                        fill: false,
                        yAxisID: 'y1'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Response Time (ms)'
                            }
                        },
                        y1: {
                            beginAtZero: true,
                            position: 'right',
                            grid: {
                                drawOnChartArea: false
                            },
                            title: {
                                display: true,
                                text: 'Status'
                            },
                            ticks: {
                                stepSize: 1,
                                callback: v => v === 1 ? 'UP' : ''
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Waktu'
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top',
                            labels: {
                                usePointStyle: true,
                                generateLabels: function(chart) {
                                    const datasets = chart.data.datasets;
                                    return datasets.map((dataset, i) => {
                                        const label = dataset.label || '';
                                        let pointStyle = 'rect';

                                        if (label === 'UP' || label === 'DOWN') {
                                            pointStyle = 'circle';
                                        }

                                        return {
                                            text: label,
                                            fillStyle: dataset.backgroundColor || dataset.borderColor,
                                            strokeStyle: dataset.borderColor,
                                            lineWidth: 2,
                                            hidden: !chart.isDatasetVisible(i),
                                            index: i,
                                            pointStyle: pointStyle,
                                            fontColor: '#000',
                                        };
                                    });
                                }
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(ctx) {
                                    if (ctx.dataset.label === 'Response Time (ms)') return `Response Time: ${ctx.parsed.y} ms`;
                                    if (ctx.dataset.label === 'UP') return 'Status: UP';
                                    if (ctx.dataset.label === 'DOWN') return 'Status: DOWN';
                                }
                            }
                        }
                    }
                }
            });

            ctx.chartInstance = chart;
        }

        /**
         * loadDashboard({ force: boolean }) -> Promise
         * - force: jika true, akan memuat ulang meskipun sudah dimuat sebelumnya
         */
        function loadDashboard({
            force = false
        } = {}) {
            let isLoaded = false;
            let isLoading = false;
            if (isLoaded && !force) {
                return Promise.resolve({
                    status: 'already_loaded'
                });
            }
            if (isLoading) {
                return Promise.reject(new Error('already_loading'));
            }

            isLoading = true;
            $('#pills-dashboard-tab').prop('disabled', true); // optional UX: disable tombol
            $('#loadingModal').modal('show');
            setTimeout(() => {
                $('#loadingModal').modal('hide');
            }, 400);

            return new Promise((resolve, reject) => {
                // tunggu semua fadeOut selesai
                $('.tab-pane.show').fadeOut(100).promise().done(function() {
                    $('.tab-pane.show').removeClass('show active');

                    const $contentContainer = $('#dashboard-content');

                    $.ajax({
                            url: '/partials/dashboard',
                            type: 'GET',
                            dataType: 'html'
                        })
                        .done(function(html) {
                            $contentContainer.html(html);
                            // muat script dashboard.js
                            $.getScript('{{ asset("js/dashboard.js") }}')
                                .done(function() {
                                    console.log('dashboard.js berhasil dimuat dan dijalankan');

                                    if (typeof initializeYearlySales === 'function') {
                                        initializeYearlySales();
                                    }

                                    isLoaded = true;
                                    isLoading = false;
                                    $('#loadingModal').modal('hide');
                                    $('#pills-dashboard-tab').prop('disabled', false);
                                    $('#pills-dashboard').fadeIn(100).addClass('show active');

                                    resolve({
                                        status: 'loaded'
                                    });
                                })
                                .fail(function() {
                                    console.error('Gagal memuat dashboard.js');
                                    $contentContainer.append('<p>Terjadi kesalahan saat memuat dashboard.</p>');
                                    isLoading = false;
                                    $('#loadingModal').modal('hide');
                                    $('#pills-dashboard-tab').prop('disabled', false);
                                    reject(new Error('getScript_failed'));
                                });

                        })
                        .fail(function(xhr, status, error) {
                            console.error('Gagal memuat konten dashboard:', error);
                            $contentContainer.html('<p>Terjadi kesalahan saat memuat dashboard.</p>');
                            isLoading = false;
                            $('#loadingModal').modal('hide');
                            $('#pills-dashboard-tab').prop('disabled', false);
                            reject(new Error('ajax_failed'));
                        });
                });
            });
        }

        function cekjabatan() {
            var jabatan = '{{ auth()->user()->jabatan }}'
            if (jabatan === 'Direktur' || jabatan === 'Direktur Utama') {
                // Activate the Dashboard tab if jabatan is 'Direktur'
                $('#pills-dashboard-tab').addClass('active');
                $('#pills-dashboard').addClass('show active');
                $('#pills-home-tab').removeClass('active');
                $('#pills-home').removeClass('show active');
                $('#loadingModal').modal('show');
                // initializeYearlySales();
                loadDashboard().catch(function(err) {
                    // optional: tangani error global di sini
                    console.error(err);
                });
                setTimeout(() => {
                    $('#loadingModal').modal('hide');
                }, 3000);
            } else {
                // Otherwise, activate the Home tab
                $('#pills-home-tab').addClass('active');
                $('#pills-home').addClass('show active');
                $('#pills-dashboard-tab').removeClass('active');
                $('#pills-dashboard').removeClass('show active');
                setTimeout(() => {
                    $('#loadingModal').modal('hide');
                    $('#modalPemberitahuan').modal('show');
                }, 1000);
            }
        }
        // Example function to format target values as "M", "JT", etc.

        function cekip() {
            $.ajax({
                url: "{{ route('cekip') }}", // Sesuaikan dengan route Anda
                type: 'GET',
                success: function(response) {
                    var data = response.success;
                    if (data === 'Absen Normal') {
                        // Disable the Inhouse and SPJ radio buttons
                        $('#inhouse').prop('disabled', true);
                        $('#spj').prop('disabled', true);
                        $('#normal').prop('checked', true);

                        // Enable the Normal radio button
                        $('#normal').prop('disabled', false);
                    } else if (data === 'Absen Luar') {
                        // Disable the Normal radio button
                        $('#normal').prop('disabled', true);

                        // Enable the Inhouse and SPJ radio buttons
                        $('#inhouse').prop('disabled', false);
                        $('#spj').prop('disabled', false);

                        // Automatically select the SPJ radio button
                        $('#spj').prop('checked', true);
                    } else {
                        // If some other value, enable all buttons
                        $('#normal').prop('disabled', false);
                        $('#inhouse').prop('disabled', false);
                        $('#spj').prop('disabled', false);
                    }
                    $('#absen').show();
                },
                error: function(xhr, status, error) {
                    alert(xhr.responseJSON.error);
                    // console.log(xhr.responseJSON.error);
                }
            });
        }

        // Fungsi untuk absen masuk
        $('#btnAbsen').on('click', function(e) {
            let stream;

            Webcam.set({
                width: 320,
                height: 320,
                image_format: 'jpeg',
                jpeg_quality: 50,
                force_flash: false,
                flip_horiz: true,
                constraints: {
                    facingMode: "user",
                    width: {
                        ideal: 320
                    },
                    height: {
                        ideal: 320
                    }
                }
            });

            Webcam.attach('#camera');

            Webcam.on('live', function() {
                stream = Webcam.stream;
            });

            // Ambil foto ketika tombol ditekan
            $('#takeSnapshot').off('click').on('click', function() {
                Webcam.snap(function(data_uri) {
                    // Display the captured image
                    $('#result').html('<img src="' + data_uri + '"/>');

                    const now = new Date();
                    const tanggal = now.toISOString().split('T')[0];
                    const jam_masuk = now.toTimeString().split(' ')[0];

                    var karyawan = "{{ auth()->user()->karyawan_id }}";
                    var jabatan = "{{ auth()->user()->jabatan }}";
                    var keterangan = $('input[name="keterangan"]:checked').val();

                    // Determine shift based on current hour
                    var jamSekarang = now.toTimeString().split(' ')[
                        0]; // Mendapatkan waktu dalam format HH:mm:ss
                    var hariSekarang = now.getDay();
                    var shift = null;

                    if (jabatan == 'Office Boy') {
                        if (hariSekarang === 6 || hariSekarang === 0) {
                            // Shift akhir pekan (Sabtu dan Minggu)
                            if (jamSekarang >= '03:00:00' && jamSekarang < '08:00:00') {
                                shift = 1;
                            } else if (jamSekarang >= '08:00:00' && jamSekarang < '23:00:00') {
                                shift = 2;
                            } else {
                                shift = 'Tidak Sesuai Shift';
                            }
                        } else {
                            // Shift hari biasa (Senin hingga Jumat)
                            if (jamSekarang >= '03:00:00' && jamSekarang < '10:00:00') {
                                shift = 1;
                            } else if (jamSekarang >= '14:00:00' && jamSekarang < '23:00:00') {
                                shift = 2;
                            } else {
                                shift = 'Tidak Sesuai Shift';
                            }
                        }
                    } else {
                        shift = 1; // Default untuk jabatan lainnya
                    }


                    // Kirim data absen masuk ke server
                    $.ajax({
                        url: "{{ route('absensi.masuk') }}",
                        type: 'POST',
                        data: {
                            _token: "{{ csrf_token() }}",
                            id_karyawan: karyawan,
                            tanggal: tanggal,
                            jabatan: jabatan,
                            jam_masuk: jam_masuk,
                            keterangan: keterangan,
                            shift: shift,
                            foto: data_uri,
                        },
                        success: function(response) {
                            // alert(response.success);
                            $('#modalAbsen').modal('hide');
                            window.location.href = "{{ route('absensi.karyawan') }}";
                        },
                        error: function(xhr, status, error) {
                            alert(xhr.responseJSON.error);
                            console.log(xhr.responseJSON);
                            location.reload();
                        }
                    });
                });
            });
        });

        // Fungsi untuk absen pulang
        $('#pulang').off('click').on('click', function() {
            const now = new Date();
            const tanggal = now.toISOString().split('T')[0];
            const jam_pulang = now.toTimeString().split(' ')[0];
            console.log('Tanggal:', tanggal);
            console.log('Jam Pulang:', jam_pulang);
            var karyawan = "{{ auth()->user()->karyawan_id }}";
            var jabatan = "{{ auth()->user()->jabatan }}";
            var hariSekarang = '';
            var jamSekarang = '';
            var keterangan_pulang = $('input[name="keterangan"]:checked').val();
            if (!keterangan_pulang) {
                alert('Silakan pilih keterangan pulang.');
                return; // Stop execution if keterangan is not selected
            }
            if (jabatan == 'Office Boy') {
                if (hariSekarang === 6 || hariSekarang === 0) {
                    // Shift akhir pekan (Sabtu dan Minggu)
                    if (jamSekarang >= '14:00:00' && jamSekarang < '23:59:00') {
                        shift = 1;
                    } else if (jamSekarang >= '00:00:00' && jamSekarang < '09:00:00') {
                        shift = 2;
                    } else {
                        shift = 'Tidak Sesuai Shift';
                    }
                } else {
                    // Shift hari biasa (Senin hingga Jumat)
                    if (jamSekarang >= '14:00:00' && jamSekarang < '23:59:59') {
                        shift = 1;
                    } else if (jamSekarang >= '00:00:00' && jamSekarang < '09:00:00') {
                        shift = 2;
                    } else {
                        shift = 'Tidak Sesuai Shift';
                    }
                }
            } else {
                shift = 1; // Default untuk jabatan lainnya
            }
            // Kirim data absen pulang ke server
            $.ajax({
                url: "{{ route('absensi.keluar') }}",
                type: 'POST',
                data: {
                    _token: "{{ csrf_token() }}",
                    id_karyawan: karyawan,
                    tanggal: tanggal,
                    jam_keluar: jam_pulang,
                    shift: shift,
                    keterangan_pulang: keterangan_pulang,
                    jabatan: jabatan, // Tambahkan jabatan ke request
                    client_time: now.toISOString() // Kirim waktu client untuk logging
                },
                success: function(response) {
                    if (response.success) {
                        // alert(response.success);
                        // $('#modalPemberitahuan').modal('hide');
                        window.location.href = "{{ route('absensi.karyawan') }}";
                    } else {
                        alert('Respons tidak valid dari server. Silakan coba lagi.');
                        console.log('Unexpected response:', response);
                    }
                },
                error: function(xhr) {
                    let errorMsg = 'Terjadi kesalahan. Silakan coba lagi.';
                    if (xhr.responseJSON && xhr.responseJSON.errors) {
                        errorMsg = Object.values(xhr.responseJSON.errors).flat().join('\n');
                    } else if (xhr.responseJSON && xhr.responseJSON.error) {
                        errorMsg = xhr.responseJSON.error;
                    }
                    alert(errorMsg);
                    console.log('Error response:', xhr.responseJSON);
                    window.location.href = "{{ route('absensi.karyawan') }}"; // Redirect even on error
                }
            });
        });

        // Hentikan kamera saat modal ditutup
        $('#modalAbsen').on('hidden.bs.modal', function() {
            Webcam.reset();
        });

        function handleNotificationDismissal() {
            // Prevent default action of the buttonz
            // event.preventDefault();

            // Hide the closest card-body to the clicked button
            $(this).closest('.card-body').hide();

            // Check if there are any visible notifications left
            if ($('#modalPemberitahuan .card-body:visible').length == 0) {
                $('hr').hide();
                // $('#modalPemberitahuan .modal-body').append('<p>Tidak ada notifikasi</p>');
            }
        }
        $('#modalPemberitahuan').on('click', '.btn-danger', handleNotificationDismissal);
    </script>
</body>

</html>