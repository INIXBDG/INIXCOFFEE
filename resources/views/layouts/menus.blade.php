<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="vapid-public-key" content="{{ config('services.vapid.public_key') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="user-id" content="{{ auth()->id() }}">
    <meta name="pusher-app-key" content="{{ config('broadcasting.connections.pusher.key') }}">
    <meta name="pusher-app-cluster" content="{{ config('broadcasting.connections.pusher.options.cluster') }}">
    <title>INIXCOFFEE</title>
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="manifest" href="/site.webmanifest">
    <link rel="mask-icon" href="/safari-pinned-tab.svg" color="#5bbad5">
    <meta name="msapplication-TileColor" content="#2b5797">
    <meta name="theme-color" content="#333333">
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
        integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">
    <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.9/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
    @vite(['resources/css/pages/menus.css', 'resources/js/app.js', 'resources/js/pages/menus.js'])
    @stack('styles')
</head>

<body>
    @if (app()->environment('staging'))
        <div aria-hidden="true"
            style="position: fixed; top: 50%; left: 50%; z-index: 999998; color: rgba(220, 38, 38, 0.18); font-size: clamp(4rem, 12vw, 10rem); font-weight: 800; letter-spacing: 0.2em; pointer-events: none; transform: translate(-50%, -50%) rotate(-25deg); user-select: none; white-space: nowrap;">
            STAGING</div>
    @endif

    @php
        $jabatanUser = auth()->user()?->jabatan;
        $canManageNotif = in_array($jabatanUser, ['HRD', 'Koordinator Office', 'Office Manager']);
        $notifikasi = $notifikasi ?? [];
        $absenHariIni = $absenHariIni ?? null;
        $filteredNotif = collect($notifikasi)->sortByDesc('created_at');
        $nowTime = \Carbon\Carbon::now();
        $isEvening = $nowTime->hour >= 17;
        $hasAbsenPulang = !empty($absenHariIni) ? !empty($absenHariIni->jam_keluar) : false;
        $unreadNotifCount = $globalUnreadNotifCount ?? 0;
    @endphp

    <div id="app">
        <div class="modal fade" id="notificationModal" tabindex="-1" aria-labelledby="notificationModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-scrollable" style="max-width: 550px;">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="notificationModalLabel">Alert Pemberitahuan</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        @include('partials.notification-modal-body')
                    </div>
                    <div class="modal-footer">
                        @if ($unreadNotifCount > 0)
                            <form action="{{ route('notifications.markAllAsRead') }}" method="POST" class="d-inline">
                                @csrf
                                @method('PUT')
                                <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill px-4">Tandai
                                    Semua sebagai Dibaca</button>
                            </form>
                        @endif
                        <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-4"
                            data-bs-dismiss="modal">Tutup</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalPemberitahuan" tabindex="-1" aria-labelledby="modalPemberitahuanLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="col-md-12 d-flex justify-content-between">
                        <h5 class="modal-title" id="modalPemberitahuanLabel">Pengumuman</h5>
                        @if ($canManageNotif)
                            <a href="{{ route('notif.create') }}" class="btn btn-sm btn-custom mx-4">
                                <img src="{{ asset('icon/plus.svg') }}" width="20px">
                            </a>
                        @endif
                    </div>
                </div>
                <div class="modal-body" style="overflow-y: scroll; height:400px">
                    @if ($filteredNotif->isEmpty())
                        <p>Tidak ada notifikasi</p>
                    @else
                        @foreach ($filteredNotif as $notif)
                            <div class="card-body" id="notif">
                                <table>
                                    <tr>
                                        <td style="width:80%">
                                            @if ($notif->tipe_notifikasi == 'Libur')
                                                <div class="card-title" style="text-transform: capitalize">
                                                    Pengumuman <strong>{{ $notif->tipe_notifikasi }}</strong>
                                                    Dari {{ $notif->id_user }}
                                                    <b>{{ $notif->users->jabatan ?? '' }}</b>
                                                    <p>{{ $notif->isi_notifikasi }}<br>
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
                                                    <b>{{ $notif->users->jabatan ?? '' }}</b>
                                                    <p>{{ $notif->isi_notifikasi }}</p>
                                                    <p class="m-0">
                                                        {{ \Carbon\Carbon::parse($notif->created_at)->translatedFormat('d F Y \J\a\m H:i:s') }}
                                                    </p>
                                                </div>
                                            @endif
                                        </td>
                                        <td style="width: 20%">
                                            <div class="d-flex gap-2 align-items-center">
                                                @if ($canManageNotif)
                                                    <a href="{{ route('notif.edit', $notif->id) }}"
                                                        class="btn btn-warning">
                                                        <img src="{{ asset('icon/edit.svg') }}" width="20px">
                                                    </a>
                                                @endif
                                                <form action="{{ route('notif.destroy', $notif->id) }}"
                                                    method="POST" style="display:inline;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger"
                                                        style="padding: 0 7px;">
                                                        <img src="{{ asset('icon/trash.svg') }}" width="20px"
                                                            alt="delete">
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            <hr class="m-0">
                        @endforeach
                    @endif

                    @if (!$absenHariIni)
                        Anda belum absensi hari ini, harap segera melakukan absensi.
                    @else
                        Anda sudah absensi hari ini pada tanggal
                        {{ \Carbon\Carbon::parse($absenHariIni->tanggal)->translatedFormat('d F Y') }} di jam
                        {{ $absenHariIni->jam_masuk }}
                    @endif

                    @if ($hasAbsenPulang && $isEvening)
                        Terimakasih telah melakukan absensi pulang, hati hati dijalan!
                    @elseif ($isEvening)
                        Harap melakukan absensi pulang ya!
                    @endif

                    @if ($jabatanUser == 'Programmer')
                        Diupdate pada tanggal 2 Juli 2025
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-custom" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalAbsen" tabindex="-1" aria-labelledby="modalAbsenLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalAbsenLabel">Absensi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body d-flex flex-column align-items-center justify-content-center">
                    <div id="camera"
                        style="width: 320px; height: 320px; border: 2px solid #ddd; border-radius: 5px;"></div>
                    <br />
                    <div class="row">
                        <div class="btn-group w-100 flex-wrap" role="group" aria-label="Pilihan Absen">
                            <input type="radio" class="btn-check" name="keterangan" id="normal" value="Kantor"
                                autocomplete="off" disabled>
                            <label class="btn btn-outline-primary m-1" for="normal"><i
                                    class="bi bi-person-check"></i> Absen Normal</label>
                            <input type="radio" class="btn-check" name="keterangan" id="inhouse"
                                value="Inhouse Bandung" autocomplete="off" disabled>
                            <label class="btn btn-outline-warning m-1" for="inhouse"><i
                                    class="bi bi-house-door"></i> Absen Inhouse BDG</label>
                            <input type="radio" class="btn-check" name="keterangan" id="spj" value="SPJ"
                                autocomplete="off" disabled>
                            <label class="btn btn-outline-success m-1" for="spj"><i class="bi bi-truck"></i>
                                Absen SPJ</label>
                        </div>
                    </div>
                    <br />
                    <div class="d-flex flex-row justify-content-between w-100">
                        <button id="takeSnapshot" class="btn btn-primary mx-2">Absen Masuk</button>
                        <button id="pulang" class="btn btn-danger mx-2">Absen Pulang</button>
                    </div>
                    <br />
                    <div id="result" style="width: 320px; text-align: center;"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-custom" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

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
                            @if ($unreadNotifCount > 0)
                                <span id="notifBadge"
                                    class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                    {{ $unreadNotifCount }}
                                    <span class="visually-hidden">unread notifications</span>
                                </span>
                            @endif
                        </a>
                    </li>
                    <li class="nav-item order-0 order-md-1" style="margin-left: 10px" id="auth">
                        <h6 class="nav-link mt-1"
                            style="text-transform: capitalize; color:#fff; margin:0px; padding:8px;">
                            <p class="p-0 m-0">Selamat Datang {{ auth()->user()->username }}, Anda Login Sebagai</p>
                            <p class="p-0 m-0">{{ auth()->user()->jabatan }}</p>
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
            <div class="col-md-3 col-sm-3 col-xs-3 d-flex justify-content-center" id="navbarkanan">
                <div class="btn-group" role="group" aria-label="Navigation Switch" id="btngroupnavbar">
                    <input type="radio" class="btn-check" name="nav-options" id="pills-home-tab"
                        autocomplete="off" checked>
                    <label class="btn btn-primary" for="pills-home-tab">Home</label>
                    <input type="radio" class="btn-check" name="nav-options" id="pills-dashboard-tab"
                        autocomplete="off">
                    <label class="btn btn-primary" for="pills-dashboard-tab">Dashboard</label>
                    @can('Akses Development')
                        <input type="radio" class="btn-check" name="nav-options" id="pills-admin-tab"
                            autocomplete="off">
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
        <div class="tab-content" id="pills-tabContent">
            <div class="tab-pane fade" id="pills-home" role="tabpanel" aria-labelledby="pills-home-tab">
                @php
                    $cachedLayouts = \App\Models\DashboardLayout::cachedAll();
                    if (!isset($visibleSections)) {
                        $user = auth()->user();
                        $globalOrder = $cachedLayouts->pluck('section_key')->toArray();
                        if (empty($globalOrder)) {
                            $globalOrder = [
                                'karyawan',
                                'peserta',
                                'itsm',
                                'rkm',
                                'finance',
                                'performance',
                                'education',
                                'office',
                                'crm',
                                'management',
                                'project',
                            ];
                        }
                        $sectionRules = [
                            'karyawan' => true,
                            'peserta' => $user ? $user->can('Fitur Menu Peserta') : false,
                            'itsm' => true,
                            'rkm' => $user ? $user->can('Fitur Menu RKM') : false,
                            'finance' => $user ? $user->can('Fitur Menu Finance') : false,
                            'performance' => true,
                            'education' => $user ? $user->can('Fitur Menu Education') : false,
                            'office' => $user ? $user->can('Fitur Menu Office') : false,
                            'crm' => $user ? $user->can('Fitur CRM') : false,
                            'management' => $user ? $user->can('Fitur Menu Manajemen') : false,
                            'project' => $user ? $user->can('Fitur Menu Project') : false,
                        ];
                        $visibleSections = array_values(
                            array_filter($globalOrder, fn($k) => $sectionRules[$k] ?? false),
                        );
                    }
                    if (!isset($dashboardLayouts)) {
                        $dashboardLayouts = $cachedLayouts->keyBy('section_key');
                    }
                    $savedCardOrders = [];
                    foreach ($dashboardLayouts as $secKey => $layoutRow) {
                        $co = $layoutRow->card_order;
                        if (is_string($co)) {
                            $co = json_decode($co, true);
                        }
                        if (!empty($co) && is_array($co)) {
                            $savedCardOrders[$secKey] = $co;
                        }
                    }
                    $jabatanAuth = auth()->user()->jabatan;
                    $idKaryawan = auth()->user()->karyawan_id;
                    $isKoordinatorItsm =
                        auth()->check() &&
                        ((auth()->user()->karyawan && auth()->user()->karyawan->jabatan === 'Koordinator ITSM') ||
                            auth()->user()->jabatan === 'Koordinator ITSM' ||
                            strtolower(auth()->user()->username ?? (auth()->user()->name ?? '')) === 'ardhan');
                @endphp
                <div class="masonry-container">
                    @foreach ($visibleSections as $key)
                        @if ($key == 'karyawan')
                            <div class="masonry-item">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="text-center card-title">Karyawan</h5>
                                        <div class="row dashboard-section-row" data-section="karyawan">
                                            <div class="col-sm-6 mt-2" data-card-id="profil_saya">
                                                <div class="card" id="card-hover">
                                                    <div class="card-body d-flex">
                                                        <div class="col-md-2"><img src="{{ asset('icon/user.svg') }}"
                                                                class="img-responsive" width="30px"></div>
                                                        <div class="col-md-10" style="margin-left: 10px"><a
                                                                href="{{ route('user.show', auth()->user()->hashids) }}"
                                                                class="link stretched-link text-decoration-none">
                                                                <h5 class="card-title">Profil Saya</h5>
                                                            </a>
                                                            <p class="card-text">Profil saya sebagai karyawan INIXINDO
                                                                Bandung.</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            @can('View DataKaryawan')
                                                <div class="col-sm-6 mt-2" data-card-id="data_karyawan">
                                                    <div class="card" id="card-hover">
                                                        <div class="card-body d-flex">
                                                            <div class="col-md-2"><img
                                                                    src="{{ asset('icon/users.svg') }}"
                                                                    class="img-responsive" width="30px"></div>
                                                            <div class="col-md-10" style="margin-left: 10px"><a
                                                                    href="/user"
                                                                    class="link stretched-link text-decoration-none">
                                                                    <h5 class="card-title">Data Karyawan</h5>
                                                                </a>
                                                                <p class="card-text">Data lengkap semua karyawan.</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endcan
                                            @can('View Struktur Organisasi')
                                                <div class="col-sm-6 mt-2" data-card-id="struktur_organisasi">
                                                    <div class="card" id="card-hover">
                                                        <div class="card-body d-flex">
                                                            <div class="col-md-2"><img
                                                                    src="{{ asset('icon/sliders.svg') }}"
                                                                    class="img-responsive" width="30px"></div>
                                                            <div class="col-md-10" style="margin-left: 10px"><a
                                                                    href="{{ route('employee.structure.index') }}"
                                                                    class="link stretched-link text-decoration-none">
                                                                    <h5 class="card-title">Struktur Organisasi</h5>
                                                                </a>
                                                                <p class="card-text">Lihat struktur organisasi perusahaan
                                                                </p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endcan
                                            <div class="col-sm-6 mt-2" data-card-id="job_karyawan">
                                                <div class="card" id="card-hover">
                                                    <div class="card-body d-flex">
                                                        <div class="col-md-2"><img src="{{ asset('icon/user.svg') }}"
                                                                class="img-responsive" width="30px"></div>
                                                        <div class="col-md-10" style="margin-left: 10px"><a
                                                                href="{{ route('employee.jobdesk.index') }}"
                                                                class="link stretched-link text-decoration-none">
                                                                <h5 class="card-title">Job Karyawan</h5>
                                                            </a>
                                                            <p class="card-text">Lihat SOP, Job Desk, dan Job Profile
                                                                Anda</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-sm-6 mt-2" data-card-id="aktivitas_harian">
                                                <div class="card" id="card-hover">
                                                    <div class="card-body d-flex">
                                                        <div class="col-md-2"><img
                                                                src="{{ asset('icon/clipboard.svg') }}"
                                                                class="img-responsive" width="30px"></div>
                                                        <div class="col-md-10" style="margin-left: 10px"><a
                                                                href="/daily-activities"
                                                                class="link stretched-link text-decoration-none">
                                                                <h5 class="card-title">Aktivitas Harian</h5>
                                                            </a>
                                                            <p class="card-text">aktivitas per hari untuk masing-masing
                                                                divisi</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-sm-6 mt-2" data-card-id="management_ruangan">
                                                <div class="card" id="card-hover">
                                                    <div class="card-body d-flex">
                                                        <div class="col-md-2"><img
                                                                src="{{ asset('icon/layout-grid.svg') }}"
                                                                class="img-responsive" width="30px"></div>
                                                        <div class="col-md-10" style="margin-left: 10px"><a
                                                                href="/management-kelas"
                                                                class="link stretched-link text-decoration-none">
                                                                <h5 class="card-title">Management Ruangan</h5>
                                                            </a>
                                                            <p class="card-text">Management Ruangan yang ada di kantor
                                                                Inixindo.</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            @can('View Jabatan')
                                                <div class="col-sm-6 mt-2" data-card-id="jabatan">
                                                    <div class="card" id="card-hover">
                                                        <div class="card-body d-flex">
                                                            <div class="col-md-2"><img
                                                                    src="{{ asset('icon/award.svg') }}"
                                                                    class="img-responsive" width="30px"></div>
                                                            <div class="col-md-10" style="margin-left: 10px"><a
                                                                    href="/jabatan"
                                                                    class="link stretched-link text-decoration-none">
                                                                    <h5 class="card-title">Jabatan</h5>
                                                                </a>
                                                                <p class="card-text">Data Jabatan.</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endcan
                                            <div class="col-sm-6 mt-2" data-card-id="pengumuman">
                                                <div class="card" id="card-hover">
                                                    <div class="card-body d-flex">
                                                        <div class="col-md-2"><img src="{{ asset('icon/bell.svg') }}"
                                                                class="img-responsive" width="30px"></div>
                                                        <div class="col-md-10" style="margin-left: 10px"><a
                                                                href="#"
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
                                            <div class="col-sm-6 mt-2" data-card-id="absen">
                                                <div class="card" id="card-hover">
                                                    <div class="card-body d-flex">
                                                        <div class="col-md-2"><img
                                                                src="{{ asset('icon/camera.svg') }}"
                                                                class="img-responsive" width="30px"></div>
                                                        <div class="col-md-10" style="margin-left: 10px"><a
                                                                href="#"
                                                                class="btn-absen link stretched-link text-decoration-none"
                                                                data-bs-toggle="modal" data-bs-target="#modalAbsen">
                                                                <h5 class="card-title">Absen</h5>
                                                            </a>
                                                            <p class="card-text">Absensi.</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            @can('View RekapAbsensi')
                                                <div class="col-sm-6 mt-2" data-card-id="rekapitulasi_absensi">
                                                    <div class="card" id="card-hover">
                                                        <div class="card-body d-flex">
                                                            <div class="col-md-2"><img
                                                                    src="{{ asset('icon/archive.svg') }}"
                                                                    class="img-responsive" width="30px"></div>
                                                            <div class="col-md-10" style="margin-left: 10px"><a
                                                                    href="/rekapitulasiabsen"
                                                                    class="link stretched-link text-decoration-none">
                                                                    <h5 class="card-title">Rekapitulasi Absensi</h5>
                                                                </a>
                                                                <p class="card-text">Data Rekapitulasi Absen Karyawan.</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endcan
                                            <div class="col-sm-6 mt-2" data-card-id="catatan_absensi">
                                                <div class="card" id="card-hover">
                                                    <div class="card-body d-flex">
                                                        <div class="col-md-2"><img
                                                                src="{{ asset('icon/calendar.svg') }}"
                                                                class="img-responsive" width="30px"></div>
                                                        <div class="col-md-10" style="margin-left: 10px"><a
                                                                href="/absensi/karyawan"
                                                                class="link stretched-link text-decoration-none">
                                                                <h5 class="card-title">Catatan Absensi</h5>
                                                            </a>
                                                            <p class="card-text">Absensi anda pada bulan ini.</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-sm-6 mt-2" data-card-id="pengajuan_cuti">
                                                <div class="card" id="card-hover">
                                                    <div class="card-body d-flex">
                                                        <div class="col-md-2"><img
                                                                src="{{ asset('icon/clock.svg') }}"
                                                                class="img-responsive" width="30px"></div>
                                                        <div class="col-md-10" style="margin-left: 10px"><a
                                                                href="/pengajuancuti"
                                                                class="link stretched-link text-decoration-none">
                                                                <h5 class="card-title">Pengajuan Cuti</h5>
                                                            </a>
                                                            <p class="card-text">Klik disini untuk pengajuan cuti.</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-sm-6 mt-2" data-card-id="pengajuan_barang">
                                                <div class="card" id="card-hover">
                                                    <div class="card-body d-flex">
                                                        <div class="col-md-2"><img
                                                                src="{{ asset('icon/feather.svg') }}"
                                                                class="img-responsive" width="30px"></div>
                                                        <div class="col-md-10" style="margin-left: 10px"><a
                                                                href="/pengajuanbarang"
                                                                class="link stretched-link text-decoration-none">
                                                                <h5 class="card-title">Pengajuan Barang</h5>
                                                            </a>
                                                            <p class="card-text">Klik disini untuk pengajuan barang.
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-sm-6 mt-2" data-card-id="pengajuan_spj">
                                                <div class="card" id="card-hover">
                                                    <div class="card-body d-flex">
                                                        <div class="col-md-2"><img src="{{ asset('icon/send.svg') }}"
                                                                class="img-responsive" width="30px"></div>
                                                        <div class="col-md-10" style="margin-left: 10px"><a
                                                                href="/suratperjalanan"
                                                                class="link stretched-link text-decoration-none">
                                                                <h5 class="card-title">Pengajuan SPJ</h5>
                                                            </a>
                                                            <p class="card-text">Klik disini untuk pengajuan SPJ.</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-sm-6 mt-2" data-card-id="gaji_tunjangan">
                                                <div class="card" id="card-hover">
                                                    <div class="card-body d-flex">
                                                        <div class="col-md-2"><img
                                                                src="{{ asset('icon/dollar-sign.svg') }}"
                                                                class="img-responsive" width="30px"></div>
                                                        <div class="col-md-10" style="margin-left: 10px"><a
                                                                href="/tunjangan"
                                                                class="link stretched-link text-decoration-none">
                                                                <h5 class="card-title">Gaji &amp; Tunjangan</h5>
                                                            </a>
                                                            <p class="card-text">Gaji &amp; Tunjangan Karyawan.</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            @can('Managament Gaji')
                                                <div class="col-sm-6 mt-2" data-card-id="update_gaji">
                                                    <div class="card" id="card-hover">
                                                        <div class="card-body d-flex">
                                                            <div class="col-md-2"><img
                                                                    src="{{ asset('icon/hand-coins.svg') }}"
                                                                    class="img-responsive" width="30px"></div>
                                                            <div class="col-md-10" style="margin-left: 10px"><a
                                                                    href="{{ route('gaji.index') }}"
                                                                    class="link stretched-link text-decoration-none">
                                                                    <h5 class="card-title">Update Gaji Karyawan</h5>
                                                                </a>
                                                                <p class="card-text">Update Gaji Karyawan.</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endcan
                                            <div class="col-sm-6 mt-2" data-card-id="pengajuan_izin">
                                                <div class="card" id="card-hover">
                                                    <div class="card-body d-flex">
                                                        <div class="col-md-2"><img
                                                                src="{{ asset('icon/paperclip.svg') }}"
                                                                class="img-responsive" width="30px"></div>
                                                        <div class="col-md-10" style="margin-left: 10px"><a
                                                                href="/pengajuanizin"
                                                                class="link stretched-link text-decoration-none">
                                                                <h5 class="card-title">Pengajuan Izin</h5>
                                                            </a>
                                                            <p class="card-text">Pengajuan Izin 3 Jam.</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-sm-6 mt-2" data-card-id="lembur">
                                                <div class="card" id="card-hover">
                                                    <div class="card-body d-flex">
                                                        <div class="col-md-2"><img
                                                                src="{{ asset('icon/aperture.svg') }}"
                                                                class="img-responsive" width="30px"></div>
                                                        <div class="col-md-10" style="margin-left: 10px"><a
                                                                href="/lembur"
                                                                class="link stretched-link text-decoration-none">
                                                                <h5 class="card-title">Lembur</h5>
                                                            </a>
                                                            <p class="card-text">Lembur.</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            @php
                                                $jabatanAuth = auth()->user()->karyawan->jabatan ?? auth()->user()->jabatan ?? '';
                                            @endphp
                                            @if(in_array(strtolower($jabatanAuth), ['koordinator itsm', 'spv sales', 'education manager', 'gm', 'general manager']))
                                                <div class="col-sm-6 mt-2" data-card-id="persetujuan_pengajuan_divisi">
                                                    <div class="card" id="card-hover">
                                                        <div class="card-body d-flex">
                                                            <div class="col-md-2"><img
                                                                    src="{{ asset('icon/check-circle.svg') }}"
                                                                    class="img-responsive" width="30px"></div>
                                                            <div class="col-md-10" style="margin-left: 10px"><a
                                                                    href="{{ route('laporan-pending.index') }}"
                                                                    class="link stretched-link text-decoration-none">
                                                                    <h5 class="card-title">Persetujuan Pengajuan Divisi</h5>
                                                                </a>
                                                                <p class="card-text">Kelola persetujuan cuti, barang & SPJ divisi yang menunggu approval.</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @elseif ($key == 'peserta')
                            @can('Fitur Menu Peserta')
                                <div class="masonry-item">
                                    <div class="card">
                                        <div class="card-body">
                                            <h5 class="text-center card-title">Peserta</h5>
                                            <div class="row dashboard-section-row" data-section="peserta">
                                                @can('View Peserta')
                                                    <div class="col-sm-6 mt-2" data-card-id="data_peserta">
                                                        <div class="card" id="card-hover">
                                                            <div class="card-body d-flex">
                                                                <div class="col-md-2"><img
                                                                        src="{{ asset('icon/table.svg') }}"
                                                                        class="img-responsive" width="30px"></div>
                                                                <div class="col-md-10" style="margin-left: 10px"><a
                                                                        href="/peserta"
                                                                        class="link stretched-link text-decoration-none">
                                                                        <h5 class="card-title">Data Peserta</h5>
                                                                    </a>
                                                                    <p class="card-text">Data Peserta yang mengikuti kelas.</p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endcan
                                                @can('View Registrasi')
                                                    <div class="col-sm-6 mt-2" data-card-id="registrasi">
                                                        <div class="card" id="card-hover">
                                                            <div class="card-body d-flex">
                                                                <div class="col-md-2"><img
                                                                        src="{{ asset('icon/user-check.svg') }}"
                                                                        class="img-responsive" width="30px"></div>
                                                                <div class="col-md-10" style="margin-left: 10px"><a
                                                                        href="/registrasi"
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
                                                    <div class="col-sm-6 mt-2" data-card-id="perusahaan">
                                                        <div class="card" id="card-hover">
                                                            <div class="card-body d-flex">
                                                                <div class="col-md-2"><img
                                                                        src="{{ asset('icon/briefcase.svg') }}"
                                                                        class="img-responsive" width="30px"></div>
                                                                <div class="col-md-10" style="margin-left: 10px"><a
                                                                        href="/perusahaan"
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
                                                    <div class="col-sm-6 mt-2" data-card-id="registrasi_exam">
                                                        <div class="card" id="card-hover">
                                                            <div class="card-body d-flex">
                                                                <div class="col-md-2"><img
                                                                        src="{{ asset('icon/check-circle.svg') }}"
                                                                        class="img-responsive" width="30px"></div>
                                                                <div class="col-md-10" style="margin-left: 10px"><a
                                                                        href="/registexam"
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
                        @elseif ($key == 'itsm')
                            <div class="masonry-item">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="text-center card-title">IT Service Management</h5>
                                        <div class="row dashboard-section-row" data-section="itsm">
                                                @can('Fitur Webinar')
                                                    <div class="col-sm-6 mt-2" data-card-id="timeline_webinar">
                                                        <div class="card" id="card-hover">
                                                            <div class="card-body d-flex">
                                                                <div class="col-md-2"><i class="fa-solid fa-timeline"
                                                                        style="font-size: 30px;"></i></div>
                                                                <div class="col-md-10" style="margin-left: 10px"><a
                                                                        href="{{ route('timeline.index') }}"
                                                                        class="link stretched-link text-decoration-none">
                                                                        <h5 class="card-title">Timeline Webinar</h5>
                                                                    </a>
                                                                    <p class="card-text">mapping webinar pertahun dan timeline.
                                                                    </p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endcan
                                                @can('Fitur Content')
                                                    <!-- <div class="col-sm-6 mt-2" data-card-id="dashboard_content_harian">
                                                        <div class="card" id="card-hover">
                                                            <div class="card-body d-flex">
                                                                <div class="col-md-2"><i class="fa-solid fa-chart-line"
                                                                        style="font-size: 30px;"></i></div>
                                                                <div class="col-md-10" style="margin-left: 10px"><a
                                                                        href="{{ route('content-schedules.dashboard') }}"
                                                                        class="link stretched-link text-decoration-none">
                                                                        <h5 class="card-title">Dashboard Konten</h5>
                                                                    </a>
                                                                    <p class="card-text">monitoring performa dan analitik konten</p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div> -->
                                                    <div class="col-sm-6 mt-2" data-card-id="content_harian">
                                                        <div class="card" id="card-hover">
                                                            <div class="card-body d-flex">
                                                                <div class="col-md-2"><i class="fa-solid fa-newspaper"
                                                                        style="font-size: 30px;"></i></div>
                                                                <div class="col-md-10" style="margin-left: 10px"><a
                                                                        href="{{ route('content-schedules.index') }}"
                                                                        class="link stretched-link text-decoration-none">
                                                                        <h5 class="card-title">Content Harian</h5>
                                                                    </a>
                                                                    <p class="card-text">merekap konten harian</p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endcan
                                                @can('Fitur Penilaian Exam')
                                                    <div class="col-sm-6 mt-2" data-card-id="rekap_penilaian_exam">
                                                        <div class="card" id="card-hover">
                                                            <div class="card-body d-flex">
                                                                <div class="col-md-2"><i class="fa-solid fa-comment-dots"
                                                                        style="font-size: 30px;"></i></div>
                                                                <div class="col-md-10" style="margin-left: 10px"><a
                                                                        href="{{ route('exam.rekap-penilaian') }}"
                                                                        class="link stretched-link text-decoration-none">
                                                                        <h5 class="card-title">Rekap Penilaian Exam</h5>
                                                                    </a>
                                                                    <p class="card-text">melihat penilaian pelayanan exam.</p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endcan
                                                @can('Fitur Registry Feature')
                                                    <div class="col-sm-6 mt-2" data-card-id="registry_feature">
                                                        <div class="card" id="card-hover">
                                                            <div class="card-body d-flex">
                                                                <div class="col-md-2"><i class="fa-solid fa-book-bookmark"
                                                                        style="font-size: 30px;"></i></div>
                                                                <div class="col-md-10" style="margin-left: 10px"><a
                                                                        href="{{ route('registry.index') }}"
                                                                        class="link stretched-link text-decoration-none">
                                                                        <h5 class="card-title">Registry Feature</h5>
                                                                    </a>
                                                                    <p class="card-text">feature registry.</p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endcan
                                                @can('View ITSM Only')
                                                    <div class="col-sm-6 mt-2" data-card-id="laporan_insiden">
                                                        <div class="card" id="card-hover">
                                                            <div class="card-body d-flex">
                                                                <div class="col-md-2"><i class="fa-regular fa-file"
                                                                        style="font-size: 30px;"></i></div>
                                                                <div class="col-md-10" style="margin-left: 10px"><a
                                                                        href="{{ route('index.laporanInsiden') }}"
                                                                        class="link stretched-link text-decoration-none">
                                                                        <h5 class="card-title">Laporan Insiden</h5>
                                                                    </a>
                                                                    <p class="card-text">Laporkan Insiden dan Risiko disekitar
                                                                        anda.</p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-6 mt-2" data-card-id="papan_kanban">
                                                        <div class="card" id="card-hover">
                                                            <div class="card-body d-flex">
                                                                <div class="col-md-2"><img
                                                                        src="{{ asset('icon/layout-grid.svg') }}"
                                                                        class="img-responsive" width="30px"></div>
                                                                <div class="col-md-10" style="margin-left: 10px"><a
                                                                        href="/kanban"
                                                                        class="link stretched-link text-decoration-none">
                                                                        <h5 class="card-title">Papan Kanban</h5>
                                                                    </a>
                                                                    <p class="card-text">untuk menejemen projek.</p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-6 mt-2" data-card-id="sla_management">
                                                        <div class="card" id="card-hover">
                                                            <div class="card-body d-flex">
                                                                <div class="col-md-2"><i class="fa-solid fa-chart-line"
                                                                        style="font-size: 30px; color: #182f51;"></i></div>
                                                                <div class="col-md-10" style="margin-left: 10px"><a
                                                                        href="/sla-management"
                                                                        class="link stretched-link text-decoration-none">
                                                                        <h5 class="card-title">SLA Management</h5>
                                                                    </a>
                                                                    <p class="card-text">Pencapaian SLA ITSM.</p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-6 mt-2" data-card-id="documentation_fitur">
                                                        <div class="card" id="card-hover">
                                                            <div class="card-body d-flex">
                                                                <div class="col-md-2"><i class="fa-solid fa-book"
                                                                        style="font-size: 30px;"></i></div>
                                                                <div class="col-md-10" style="margin-left: 10px"><a
                                                                        href="{{ route('documentation.features.index') }}"
                                                                        class="link stretched-link text-decoration-none">
                                                                        <h5 class="card-title">Documentation Fitur</h5>
                                                                    </a>
                                                                    <p class="card-text">Documentation Fitur.</p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-6 mt-2" data-card-id="knowledge_management">
                                                        <div class="card" id="card-hover">
                                                            <div class="card-body d-flex">
                                                                <div class="col-md-2"><i class="fa-solid fa-book-open-reader"
                                                                        style="font-size: 30px;"></i></div>
                                                                <div class="col-md-10" style="margin-left: 10px"><a
                                                                        href="{{ route('knowledge-management.index') }}"
                                                                        class="link stretched-link text-decoration-none">
                                                                        <h5 class="card-title">Knowledge Management</h5>
                                                                    </a>
                                                                    <p class="card-text">Kelola SOP, FAQ, Tutorial, dan Panduan
                                                                        Instalasi ITSM.</p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-6 mt-2" data-card-id="kolaborasi">
                                                        <div class="card" id="card-hover">
                                                            <div class="card-body d-flex">
                                                                <div class="col-md-2"><i class="fa-solid fa-handshake"
                                                                        style="font-size: 30px;"></i></div>
                                                                <div class="col-md-10" style="margin-left: 10px"><a
                                                                        href="{{ route('colaborator.index') }}"
                                                                        class="link stretched-link text-decoration-none">
                                                                        <h5 class="card-title">Kolaborasi</h5>
                                                                    </a>
                                                                    <p class="card-text">Kolaborasi dengan Partner.</p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endcan
                                                <div class="col-sm-6 mt-2" data-card-id="it_helpdesk">
                                                    <div class="card" id="card-hover">
                                                        <div class="card-body d-flex">
                                                            <div class="col-md-2"><i class="fa-solid fa-headset"
                                                                    style="font-size: 30px;"></i></div>
                                                            <div class="col-md-10" style="margin-left: 10px"><a
                                                                    href="{{ route('tickets.index') }}"
                                                                    class="link stretched-link text-decoration-none">
                                                                    <h5 class="card-title">IT Helpdesk (Ticketing)</h5>
                                                                </a>
                                                                <p class="card-text">Laporkan Insiden dan Risiko yang anda
                                                                    alami.</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-sm-6 mt-2" data-card-id="pengajuan_subs">
                                                    <div class="card" id="card-hover">
                                                        <div class="card-body d-flex">
                                                            <div class="col-md-2"><i class="fa-solid fa-receipt"
                                                                    style="font-size: 30px;"></i></div>
                                                            <div class="col-md-10" style="margin-left: 10px"><a
                                                                    href="{{ route('pengajuansubs.index') }}"
                                                                    class="link stretched-link text-decoration-none">
                                                                    <h5 class="card-title">Pengajuan Subs</h5>
                                                                </a>
                                                                <p class="card-text">pengajuan dan manajemen
                                                                    subscriptions</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-sm-6 mt-2" data-card-id="survey_kepuasan">
                                                    <div class="card" id="card-hover">
                                                        <div class="card-body d-flex">
                                                            <div class="col-md-2"><i
                                                                    class="fa-solid fa-square-poll-vertical"
                                                                    style="font-size: 30px;"></i></div>
                                                            <div class="col-md-10" style="margin-left: 10px"><a
                                                                    href="{{ route('surveykepuasan.index') }}"
                                                                    class="link stretched-link text-decoration-none">
                                                                    <h5 class="card-title">Survey Kepuasan</h5>
                                                                </a>
                                                                <p class="card-text">Survey kepuasan pelayanan ITSM.</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                @if ($isKoordinatorItsm)
                                                    <div class="col-sm-6 mt-2" data-card-id="rekap_pembelian">
                                                        <div class="card" id="card-hover">
                                                            <div class="card-body d-flex">
                                                                <div class="col-md-2"><i class="fa-solid fa-receipt"
                                                                        style="font-size: 30px; color: #182f51;"></i></div>
                                                                <div class="col-md-10" style="margin-left: 10px"><a
                                                                        href="{{ route('itsm.rekap-pembelian.index') }}"
                                                                        class="link stretched-link text-decoration-none">
                                                                        <h5 class="card-title">Rekap Pembelian</h5>
                                                                    </a>
                                                                    <p class="card-text">Rekap pengajuan operasional &amp;
                                                                        elektronik ITSM.</p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                        @elseif ($key == 'rkm')
                            @can('Fitur Menu RKM')
                                <div class="masonry-item">
                                    <div class="card">
                                        <div class="card-body">
                                            <h5 class="text-center card-title">Rencana Kelas Mingguan</h5>
                                            <div class="row dashboard-section-row" data-section="rkm">
                                                @can('View RKM')
                                                    <div class="col-sm-6 mt-2" data-card-id="rencana_kelas_mingguan">
                                                        <div class="card" id="card-hover">
                                                            <div class="card-body d-flex">
                                                                <div class="col-md-2"><img
                                                                        src="{{ asset('icon/calendar-days.svg') }}"
                                                                        class="img-responsive" width="30px"></div>
                                                                <div class="col-md-10" style="margin-left: 10px"><a
                                                                        href="/rkm"
                                                                        class="link stretched-link text-decoration-none">
                                                                        <h5 class="card-title">Rencana Kelas Mingguan</h5>
                                                                    </a>
                                                                    <p class="card-text">Rencana kelas Training.</p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endcan
                                                @can('Index KelasSetting')
                                                    <div class="col-sm-6 mt-2" data-card-id="kelas_setting">
                                                        <div class="card" id="card-hover">
                                                            <div class="card-body d-flex">
                                                                <div class="col-md-2"><img src="{{ asset('icon/cog.svg') }}"
                                                                        class="img-responsive" width="30px"></div>
                                                                <div class="col-md-10" style="margin-left: 10px"><a
                                                                        href="{{ route('KelasSetting.index') }}"
                                                                        class="link stretched-link text-decoration-none">
                                                                        <h5 class="card-title">Kelas Setting</h5>
                                                                    </a>
                                                                    <p class="card-text">Setting seluruh kebutuhan kelas
                                                                        mingguan.</p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endcan
                                                @can('View Materi')
                                                    <div class="col-sm-6 mt-2" data-card-id="materi">
                                                        <div class="card" id="card-hover">
                                                            <div class="card-body d-flex">
                                                                <div class="col-md-2"><img
                                                                        src="{{ asset('icon/book-open.svg') }}"
                                                                        class="img-responsive" width="30px"></div>
                                                                <div class="col-md-10" style="margin-left: 10px"><a
                                                                        href="/materi"
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
                                                    <div class="col-sm-6 mt-2" data-card-id="feedback">
                                                        <div class="card" id="card-hover">
                                                            <div class="card-body d-flex">
                                                                <div class="col-md-2"><img
                                                                        src="{{ asset('icon/file-text.svg') }}"
                                                                        class="img-responsive" width="30px"></div>
                                                                <div class="col-md-10" style="margin-left: 10px"><a
                                                                        href="/feedback"
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
                                                @can('View CC')
                                                    <div class="col-sm-6 mt-2" data-card-id="credit_card_rkm">
                                                        <div class="card" id="card-hover">
                                                            <div class="card-body d-flex">
                                                                <div class="col-md-2"><img
                                                                        src="{{ asset('icon/wallet.svg') }}"
                                                                        class="img-responsive" width="30px"></div>
                                                                <div class="col-md-10" style="margin-left: 10px"><a
                                                                        href="/creditcard"
                                                                        class="link stretched-link text-decoration-none">
                                                                        <h5 class="card-title">Credit Card</h5>
                                                                    </a>
                                                                    <p class="card-text">Data Credit Card.</p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endcan
                                                @can('View Absensi&Sertifikat')
                                                    <div class="col-sm-6 mt-2" data-card-id="upload_rkm">
                                                        <div class="card" id="card-hover">
                                                            <div class="card-body d-flex">
                                                                <div class="col-md-2"><img
                                                                        src="{{ asset('icon/upload.svg') }}"
                                                                        class="img-responsive" width="30px"></div>
                                                                <div class="col-md-10" style="margin-left: 10px"><a
                                                                        href="/rkm/upload/page"
                                                                        class="link stretched-link text-decoration-none">
                                                                        <h5 class="card-title">Upload</h5>
                                                                    </a>
                                                                    <p class="card-text">Upload PDF Absensi &amp; Sertifikat
                                                                        Peserta.</p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endcan
                                                @can('View ListExam')
                                                    <div class="col-sm-6 mt-2" data-card-id="list_exam">
                                                        <div class="card" id="card-hover">
                                                            <div class="card-body d-flex">
                                                                <div class="col-md-2"><img
                                                                        src="{{ asset('icon/list-check.svg') }}"
                                                                        class="img-responsive" width="30px"></div>
                                                                <div class="col-md-10" style="margin-left: 10px"><a
                                                                        href="/listexams"
                                                                        class="link stretched-link text-decoration-none">
                                                                        <h5 class="card-title">List Exam</h5>
                                                                    </a>
                                                                    <p class="card-text">Data Exam.</p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endcan
                                                @can('Daftar Peserta Exam')
                                                    <div class="col-sm-6 mt-2" data-card-id="daftar_peserta_exam">
                                                        <div class="card" id="card-hover">
                                                            <div class="card-body d-flex">
                                                                <div class="col-md-2"><img
                                                                        src="{{ asset('icon/circle-user-round.svg') }}"
                                                                        class="img-responsive" width="30px"></div>
                                                                <div class="col-md-10" style="margin-left: 10px"><a
                                                                        href="/daftar-peserta-exam"
                                                                        class="link stretched-link text-decoration-none">
                                                                        <h5 class="card-title">Daftar Peserta Exam</h5>
                                                                    </a>
                                                                    <p class="card-text">Daftar peserta exam dan dokumentasi.
                                                                    </p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endcan
                                                @can('List Exam Sales')
                                                    <div class="col-sm-6 mt-2" data-card-id="harga_exam">
                                                        <div class="card" id="card-hover">
                                                            <div class="card-body d-flex">
                                                                <div class="col-md-2"><img src="{{ asset('icon/tag.svg') }}"
                                                                        class="img-responsive" width="30px"></div>
                                                                <div class="col-md-10" style="margin-left: 10px"><a
                                                                        href="/hargaExam"
                                                                        class="link stretched-link text-decoration-none">
                                                                        <h5 class="card-title">Harga Exam</h5>
                                                                    </a>
                                                                    <p class="card-text">Data Harga Exam.</p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endcan
                                                @can('View AnalisisRKM')
                                                    <div class="col-sm-6 mt-2" data-card-id="kelas_analisis">
                                                        <div class="card" id="card-hover">
                                                            <div class="card-body d-flex">
                                                                <div class="col-md-2"><img
                                                                        src="{{ asset('icon/stats.svg') }}"
                                                                        class="img-responsive" width="30px"></div>
                                                                <div class="col-md-10" style="margin-left: 10px"><a
                                                                        href="/kelasanalisis"
                                                                        class="link stretched-link text-decoration-none">
                                                                        <h5 class="card-title">Kelas Analisis</h5>
                                                                    </a>
                                                                    <p class="card-text">Analisis Rencana Kelas Mingguan.</p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endcan
                                                @can('View Komplain Peserta')
                                                    <div class="col-sm-6 mt-2" data-card-id="komplain_peserta">
                                                        <div class="card" id="card-hover">
                                                            <div class="card-body d-flex">
                                                                <div class="col-md-2"><i class="fa fa-comment"
                                                                        style="font-size: 30px;"></i></div>
                                                                <div class="col-md-10" style="margin-left: 10px"><a
                                                                        href="/komplain-peserta"
                                                                        class="link stretched-link text-decoration-none">
                                                                        <h5 class="card-title">Komplain Peserta</h5>
                                                                    </a>
                                                                    <p class="card-text">Komplain peserta.</p>
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
                        @elseif ($key == 'finance')
                            @can('Fitur Menu Finance')
                                <div class="masonry-item">
                                    <div class="card">
                                        <div class="card-body">
                                            <h5 class="text-center card-title">Finance</h5>
                                            <div class="row dashboard-section-row" data-section="finance">
                                                @can('View Invoice')
                                                    <div class="col-sm-6 mt-2" data-card-id="invoice">
                                                        <div class="card" id="card-hover">
                                                            <div class="card-body d-flex">
                                                                <div class="col-md-2"><img
                                                                        src="{{ asset('icon/credit-card.svg') }}"
                                                                        class="img-responsive" width="30px"></div>
                                                                <div class="col-md-10" style="margin-left: 10px"><a
                                                                        href="/invoice"
                                                                        class="link stretched-link text-decoration-none">
                                                                        <h5 class="card-title">Invoice</h5>
                                                                    </a>
                                                                    <p class="card-text">Data Invoice.</p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endcan
                                                @can('View Laporan Rugi')
                                                    <div class="col-sm-6 mt-2" data-card-id="laporan_laba_rugi">
                                                        <div class="card" id="card-hover">
                                                            <div class="card-body d-flex">
                                                                <div class="col-md-2"><img
                                                                        src="{{ asset('icon/chart-bar.svg') }}"
                                                                        class="img-responsive" width="30px"></div>
                                                                <div class="col-md-10" style="margin-left: 10px"><a
                                                                        href="/income-statement"
                                                                        class="link stretched-link text-decoration-none">
                                                                        <h5 class="card-title">Laporan Laba Rugi</h5>
                                                                    </a>
                                                                    <p class="card-text">Laporan Laba Rugi Inixindo Bandung.
                                                                    </p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endcan
                                                @can('View CC')
                                                    <div class="col-sm-6 mt-2" data-card-id="credit_card">
                                                        <div class="card" id="card-hover">
                                                            <div class="card-body d-flex">
                                                                <div class="col-md-2"><img
                                                                        src="{{ asset('icon/wallet.svg') }}"
                                                                        class="img-responsive" width="30px"></div>
                                                                <div class="col-md-10" style="margin-left: 10px"><a
                                                                        href="/creditcard"
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
                                                    <div class="col-sm-6 mt-2" data-card-id="hitung_tunjangan">
                                                        <div class="card" id="card-hover">
                                                            <div class="card-body d-flex">
                                                                <div class="col-md-2"><img
                                                                        src="{{ asset('icon/calculator.svg') }}"
                                                                        class="img-responsive" width="30px"></div>
                                                                <div class="col-md-10" style="margin-left: 10px"><a
                                                                        href="/tunjangangenerate"
                                                                        class="link stretched-link text-decoration-none">
                                                                        <h5 class="card-title">Hitung Tunjangan</h5>
                                                                    </a>
                                                                    <p class="card-text">Data Tunjangan Karyawan</p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endcan
                                                @can('View HitungLembur')
                                                    <div class="col-sm-6 mt-2" data-card-id="hitung_lembur">
                                                        <div class="card" id="card-hover">
                                                            <div class="card-body d-flex">
                                                                <div class="col-md-2"><img
                                                                        src="{{ asset('icon/timer.svg') }}"
                                                                        class="img-responsive" width="30px"></div>
                                                                <div class="col-md-10" style="margin-left: 10px"><a
                                                                        href="/overtime"
                                                                        class="link stretched-link text-decoration-none">
                                                                        <h5 class="card-title">Hitung Lembur</h5>
                                                                    </a>
                                                                    <p class="card-text">Data Lembur Karyawan</p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endcan
                                                @can('View Souvenir')
                                                    <div class="col-sm-6 mt-2" data-card-id="souvenir">
                                                        <div class="card" id="card-hover">
                                                            <div class="card-body d-flex">
                                                                <div class="col-md-2"><img
                                                                        src="{{ asset('icon/gift.svg') }}"
                                                                        class="img-responsive" width="30px"></div>
                                                                <div class="col-md-10" style="margin-left: 10px"><a
                                                                        href="/souvenir"
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
                                                    <div class="col-sm-6 mt-2" data-card-id="outstanding">
                                                        <div class="card" id="card-hover">
                                                            <div class="card-body d-flex">
                                                                <div class="col-md-2"><img
                                                                        src="{{ asset('icon/bookmark.svg') }}"
                                                                        class="img-responsive" width="30px"></div>
                                                                <div class="col-md-10" style="margin-left: 10px"><a
                                                                        href="/outstanding"
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
                                                    <div class="col-sm-6 mt-2" data-card-id="payment_advance">
                                                        <div class="card" id="card-hover">
                                                            <div class="card-body d-flex">
                                                                <div class="col-md-2"><i class="fa fa-cart-shopping"
                                                                        style="font-size: 30px;"></i></div>
                                                                <div class="col-md-10" style="margin-left: 10px"><a
                                                                        href="/paymantAdvance"
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
                            @endcan
                        @elseif ($key == 'performance')
                            <div class="masonry-item">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="text-center card-title">Performance Assesment</h5>
                                        <div class="row dashboard-section-row" data-section="performance">
                                            <div class="col-sm-6 mt-2" data-card-id="penilaian">
                                                <div class="card" id="card-hover">
                                                    <div class="card-body d-flex">
                                                        <div class="col-md-2"><i class="fa fa-ranking-star"
                                                                style="font-size: 30px;"></i></div>
                                                        <div class="col-md-10" style="margin-left: 10px"><a
                                                                href="{{ route('berandaKPI.get') }}"
                                                                class="link stretched-link text-decoration-none">
                                                                <h5 class="card-title">Penilaian</h5>
                                                            </a>
                                                            <p class="card-text">Dashboard Database Penilaian.</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            @if (in_array($jabatanAuth, ['Koordinator ITSM', 'HRD', 'Education Manager', 'GM', 'SPV Sales']))
                                                <div class="col-sm-6 mt-2" data-card-id="target_divisi">
                                                    <div class="card" id="card-hover">
                                                        <div class="card-body d-flex">
                                                            <div class="col-md-2"><i class="fa fa-bullseye"
                                                                    style="font-size: 30px;"></i></div>
                                                            <div class="col-md-10" style="margin-left: 10px"><a
                                                                    href="{{ route('kpi.index') }}"
                                                                    class="link stretched-link text-decoration-none">
                                                                    <h5 class="card-title">Target Divisi</h5>
                                                                </a>
                                                                <p class="card-text">Data target divisi.</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-sm-6 mt-2" data-card-id="overview_departement">
                                                    <div class="card" id="card-hover">
                                                        <div class="card-body d-flex">
                                                            <div class="col-md-2"><i class="fa fa-chart-line"
                                                                    style="font-size: 30px;"></i></div>
                                                            <div class="col-md-10" style="margin-left: 10px"><a
                                                                    href="{{ route('kpi.overview.index') }}"
                                                                    class="link stretched-link text-decoration-none">
                                                                    <h5 class="card-title">Overview Departement</h5>
                                                                </a>
                                                                <p class="card-text">Seluruh Perkembangan Target KPI
                                                                    Divisi</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                            <div class="col-sm-6 mt-2" data-card-id="overview_personal">
                                                <div class="card" id="card-hover">
                                                    <div class="card-body d-flex">
                                                        <div class="col-md-2"><i class="fa fa-chart-line"
                                                                style="font-size: 30px;"></i></div>
                                                        <div class="col-md-10" style="margin-left: 10px"><a
                                                                href="{{ route('kpi.overview.indexPersonal') }}"
                                                                class="link stretched-link text-decoration-none">
                                                                <h5 class="card-title">Overview Personal</h5>
                                                            </a>
                                                            <p class="card-text">Seluruh Progress Target KPI Anda.</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-sm-6 mt-2" data-card-id="penilaian_anda">
                                                <div class="card" id="card-hover">
                                                    <div class="card-body d-flex">
                                                        <div class="col-md-2"><i class="fa fa-user-check"
                                                                style="font-size: 30px;"></i></div>
                                                        <div class="col-md-10" style="margin-left: 10px"><a
                                                                href="{{ url('/penilaian360/index/' . $idKaryawan) }}"
                                                                class="link stretched-link text-decoration-none">
                                                                <h5 class="card-title">Penilaian Anda</h5>
                                                            </a>
                                                            <p class="card-text">Data Hasil Penilaian 360 Anda.</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-sm-6 mt-2" data-card-id="form_penilaian">
                                                <div class="card" id="card-hover">
                                                    <div class="card-body d-flex">
                                                        <div class="col-md-2"><i class="fa fa-file-pen"
                                                                style="font-size: 30px;"></i></div>
                                                        <div class="col-md-10" style="margin-left: 10px"><a
                                                                href="{{ url('/getFormPenilaianUser/' . $idKaryawan) }}"
                                                                class="link stretched-link text-decoration-none">
                                                                <h5 class="card-title">Form Penilaian</h5>
                                                            </a>
                                                            <p class="card-text">Form penilaian untuk anda.</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @elseif ($key == 'education')
                            @can('Fitur Menu Education')
                                <div class="masonry-item">
                                    <div class="card">
                                        <div class="card-body">
                                            <h5 class="text-center card-title">Education</h5>
                                            <div class="row dashboard-section-row" data-section="education">
                                                <div class="col-sm-6 mt-2" data-card-id="sertifikasi_pelatihan">
                                                    <div class="card" id="card-hover">
                                                        <div class="card-body d-flex">
                                                            <div class="col-md-2"><img
                                                                    src="{{ asset('icon/book.svg') }}"
                                                                    class="img-responsive" width="30px"></div>
                                                            <div class="col-md-10" style="margin-left: 10px"><a
                                                                    href="/development"
                                                                    class="link stretched-link text-decoration-none">
                                                                    <h5 class="card-title">Sertifikasi &amp; Pelatihan
                                                                    </h5>
                                                                </a>
                                                                <p class="card-text">untuk menejemen sertifikat dan
                                                                    Pelatihan Instruktur.</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                @can('View TunjanganEducation')
                                                    <div class="col-sm-6 mt-2" data-card-id="tunjangan_education">
                                                        <div class="card" id="card-hover">
                                                            <div class="card-body d-flex">
                                                                <div class="col-md-2"><img
                                                                        src="{{ asset('icon/layout-freeform.svg') }}"
                                                                        class="img-responsive" width="30px"></div>
                                                                <div class="col-md-10" style="margin-left: 10px"><a
                                                                        href="/tunjanganEducation"
                                                                        class="link stretched-link text-decoration-none">
                                                                        <h5 class="card-title">Tunjangan Education</h5>
                                                                    </a>
                                                                    <p class="card-text">Data Tunjangan Education.</p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endcan
                                                @can('Fitur Labs dan Subs')
                                                    <div class="col-sm-6 mt-2" data-card-id="pengajuan_lab">
                                                        <div class="card" id="card-hover">
                                                            <div class="card-body d-flex">
                                                                <div class="col-md-2"><i class="fa-solid fa-flask"
                                                                        style="font-size: 30px;"></i></div>
                                                                <div class="col-md-10" style="margin-left: 10px"><a
                                                                        href="{{ route('pengajuanlabsdansubs.index') }}"
                                                                        class="link stretched-link text-decoration-none">
                                                                        <h5 class="card-title">Pengajuan Lab</h5>
                                                                    </a>
                                                                    <p class="card-text">pengajuan dan manajemen labs</p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endcan
                                                @can('Fitur cv instruktur')
                                                    <div class="col-sm-6 mt-2" data-card-id="cv_instruktur">
                                                        <div class="card" id="card-hover">
                                                            <div class="card-body d-flex">
                                                                <div class="col-md-2"><i class="fa-solid fa-file-lines"
                                                                        style="font-size: 30px;"></i></div>
                                                                <div class="col-md-10" style="margin-left: 10px"><a
                                                                        href="{{ route('cv-instruktur.index') }}"
                                                                        class="link stretched-link text-decoration-none">
                                                                        <h5 class="card-title">CV Instruktur</h5>
                                                                    </a>
                                                                    <p class="card-text">untuk melihat dan export cv
                                                                        instruktur.</p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endcan
                                                @can('View RekapInstruktur')
                                                    <div class="col-sm-6 mt-2" data-card-id="rekap_mengajar">
                                                        <div class="card" id="card-hover">
                                                            <div class="card-body d-flex">
                                                                <div class="col-md-2"><img
                                                                        src="{{ asset('icon/target.svg') }}"
                                                                        class="img-responsive" width="30px"></div>
                                                                <div class="col-md-10" style="margin-left: 10px"><a
                                                                        href="/rekapmengajarinstruktur"
                                                                        class="link stretched-link text-decoration-none">
                                                                        <h5 class="card-title">Rekap Mengajar Instruktur</h5>
                                                                    </a>
                                                                    <p class="card-text">Data rekapan mengajar instruktur.</p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-6 mt-2" data-card-id="klaim_modul">
                                                        <div class="card" id="card-hover">
                                                            <div class="card-body d-flex">
                                                                <div class="col-md-2"><img
                                                                        src="{{ asset('icon/edit.svg') }}"
                                                                        class="img-responsive" width="30px"></div>
                                                                <div class="col-md-10" style="margin-left: 10px"><a
                                                                        href="/pengajuanklaimmodul"
                                                                        class="link stretched-link text-decoration-none">
                                                                        <h5 class="card-title">Klaim Modul</h5>
                                                                    </a>
                                                                    <p class="card-text">Klaim pembuatan/pengajuan modul.</p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endcan
                                                @can('View Rekomendasi Peserta')
                                                    <div class="col-sm-6 mt-2" data-card-id="rekomendasi_lanjutan">
                                                        <div class="card" id="card-hover">
                                                            <div class="card-body d-flex">
                                                                <div class="col-md-2"><img
                                                                        src="{{ asset('icon/trending-up.svg') }}"
                                                                        class="img-responsive" width="30px"></div>
                                                                <div class="col-md-10" style="margin-left: 10px"><a
                                                                        href="{{ route('rekomendasiLanjutan.index') }}"
                                                                        class="link stretched-link text-decoration-none">
                                                                        <h5 class="card-title">Rekomendasi Training Lanjutan
                                                                        </h5>
                                                                    </a>
                                                                    <p class="card-text">rekomendasi untuk peserta.</p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-6 mt-2" data-card-id="activity_report">
                                                        <div class="card" id="card-hover">
                                                            <div class="card-body d-flex">
                                                                <div class="col-md-2"><img
                                                                        src="{{ asset('icon/activity.svg') }}"
                                                                        class="img-responsive" width="30px"></div>
                                                                <div class="col-md-10" style="margin-left: 10px"><a
                                                                        href="/activityinstruktur"
                                                                        class="link stretched-link text-decoration-none">
                                                                        <h5 class="card-title">Activity Report</h5>
                                                                    </a>
                                                                    <p class="card-text">Activity Report Instruktur.</p>
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
                        @elseif ($key == 'office')
                            @can('Fitur Menu Office')
                                <div class="masonry-item">
                                    <div class="card">
                                        <div class="card-body">
                                            <h5 class="text-center card-title">Office</h5>
                                            <div class="row dashboard-section-row" data-section="office">
                                                <div class="col-sm-6 mt-2" data-card-id="dashboard_office">
                                                    <div class="card" id="card-hover">
                                                        <div class="card-body d-flex">
                                                            <div class="col-md-2"><img
                                                                    src="{{ asset('icon/layout-dashboard.svg') }}"
                                                                    class="img-responsive" width="30px"></div>
                                                            <div class="col-md-10" style="margin-left: 10px"><a
                                                                    href="{{ route('office.dashboard') }}"
                                                                    class="link stretched-link text-decoration-none">
                                                                    <h5 class="card-title">Dashboard Office</h5>
                                                                </a>
                                                                <p class="card-text">Dashboard Office Inixindo.</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                @can('View HR')
                                                    <div class="col-sm-6 mt-2" data-card-id="inix_hr">
                                                        <div class="card" id="card-hover">
                                                            <div class="card-body d-flex">
                                                                <div class="col-md-2"><img
                                                                        src="{{ asset('icon/pie-chart.svg') }}"
                                                                        class="img-responsive" width="30px"></div>
                                                                <div class="col-md-10" style="margin-left: 10px"><a
                                                                        href="{{ route('HR.index') }}"
                                                                        class="link stretched-link text-decoration-none">
                                                                        <h5 class="card-title">INIX HR</h5>
                                                                    </a>
                                                                    <p class="card-text">Arsip dan trend data perkembangan
                                                                        perusahaan.</p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endcan
                                                @can('View Rekrutmen')
                                                    <div class="col-sm-6 mt-2" data-card-id="new_hire">
                                                        <div class="card" id="card-hover">
                                                            <div class="card-body d-flex">
                                                                <div class="col-md-2"><img
                                                                        src="{{ asset('icon/user-plus.svg') }}"
                                                                        class="img-responsive" width="30px"></div>
                                                                <div class="col-md-10" style="margin-left: 10px"><a
                                                                        href="{{ route('HR.folders.index') }}"
                                                                        class="link stretched-link text-decoration-none">
                                                                        <h5 class="card-title">New Hire</h5>
                                                                    </a>
                                                                    <p class="card-text">Data pelamar baru dan jadwal rekrut.
                                                                    </p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endcan
                                                @can('View Inventaris')
                                                    <div class="col-sm-6 mt-2" data-card-id="inventaris">
                                                        <div class="card" id="card-hover">
                                                            <div class="card-body d-flex">
                                                                <div class="col-md-2"><img
                                                                        src="{{ asset('icon/box.svg') }}"
                                                                        class="img-responsive" width="30px"></div>
                                                                <div class="col-md-10" style="margin-left: 10px"><a
                                                                        href="{{ route('IndexInventaris') }}"
                                                                        class="link stretched-link text-decoration-none">
                                                                        <h5 class="card-title">Inventaris</h5>
                                                                    </a>
                                                                    <p class="card-text">Data Inventaris Inixindo.</p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endcan
                                                @can('View Klaim')
                                                    <div class="col-sm-6 mt-2" data-card-id="pengajuan_klaim">
                                                        <div class="card" id="card-hover">
                                                            <div class="card-body d-flex">
                                                                <div class="col-md-2"><img
                                                                        src="{{ asset('icon/file-minus.svg') }}"
                                                                        class="img-responsive" width="30px"></div>
                                                                <div class="col-md-10" style="margin-left: 10px"><a
                                                                        href="/pengajuan-klaim"
                                                                        class="link stretched-link text-decoration-none">
                                                                        <h5 class="card-title">Pengajuan Klaim</h5>
                                                                    </a>
                                                                    <p class="card-text">Pengajuan Absen, Jam Kerja, &amp;
                                                                        Cuti</p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endcan
                                                @can('View Catering')
                                                    <div class="col-sm-6 mt-2" data-card-id="pengajuan_catering">
                                                        <div class="card" id="card-hover">
                                                            <div class="card-body d-flex">
                                                                <div class="col-md-2"><img
                                                                        src="{{ asset('icon/truck.svg') }}"
                                                                        class="img-responsive" width="30px"></div>
                                                                <div class="col-md-10" style="margin-left: 10px"><a
                                                                        href="{{ route('catering.index') }}"
                                                                        class="link stretched-link text-decoration-none">
                                                                        <h5 class="card-title">Pengajuan Catering</h5>
                                                                    </a>
                                                                    <p class="card-text">Pengajuan Catering</p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endcan
                                                @can('Rencana Pembelian')
                                                    <div class="col-sm-6 mt-2" data-card-id="rencana_pembelian">
                                                        <div class="card" id="card-hover">
                                                            <div class="card-body d-flex">
                                                                <div class="col-md-2"><i class="fa-solid fa-cart-plus"
                                                                        style="font-size: 30px;"></i></div>
                                                                <div class="col-md-10" style="margin-left: 10px"><a
                                                                        href="{{ route('rencanaPembelian.index') }}"
                                                                        class="link stretched-link text-decoration-none">
                                                                        <h5 class="card-title">Rencana Pembelian</h5>
                                                                    </a>
                                                                    <p class="card-text">Pengajuan Rencana Pembelian.</p>
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
                        @elseif ($key == 'crm')
                            @can('Fitur CRM')
                                <div class="masonry-item">
                                    <div class="card">
                                        <div class="card-body">
                                            <h5 class="text-center card-title">Customer Relationship Management</h5>
                                            <div class="row dashboard-section-row" data-section="crm">
                                                <div class="col-sm-6 mt-2" data-card-id="fitur_crm">
                                                    <div class="card" id="card-hover">
                                                        <div class="card-body d-flex">
                                                            <div class="col-md-2"><img
                                                                    src="{{ asset('icon/contact.svg') }}"
                                                                    class="img-responsive" width="30px"></div>
                                                            <div class="col-md-10" style="margin-left: 10px"><a
                                                                    href="{{ route('CRM.index') }}"
                                                                    class="link stretched-link text-decoration-none">
                                                                    <h5 class="card-title">Fitur CRM</h5>
                                                                </a>
                                                                <p class="card-text">Masuk Fitur CRM</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-sm-6 mt-2" data-card-id="pengajuan_diluar_pa">
                                                    <div class="card" id="card-hover">
                                                        <div class="card-body d-flex">
                                                            <div class="col-md-2"><i class="fa-solid fa-basket-shopping"
                                                                    style="font-size: 30px;"></i></div>
                                                            <div class="col-md-10" style="margin-left: 10px"><a
                                                                    href="{{ route('expensehub.index') }}"
                                                                    class="link stretched-link text-decoration-none">
                                                                    <h5 class="card-title">Pengajuan Diluar PA</h5>
                                                                </a>
                                                                <p class="card-text">Pengajuan entertaint, reimburst, dan
                                                                    oleh-oleh.</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                @can('View Kelengkapan Data Penjualan')
                                                    <div class="col-sm-6 mt-2" data-card-id="rencana_pembelian">
                                                        <div class="card" id="card-hover">
                                                            <div class="card-body d-flex">
                                                                <div class="col-md-2"><i class="fa-solid fa-clipboard-check" style="font-size: 30px;"></i></div>
                                                                <div class="col-md-10" style="margin-left: 10px"><a
                                                                        href="{{ route('kelengkapan-data.index') }}"
                                                                        class="link stretched-link text-decoration-none">
                                                                        <h5 class="card-title">Kelengkapan Data Penjualan</h5>
                                                                    </a>
                                                                    <p class="card-text">periksa kelengkapan data penjualan.</p>
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
                        @elseif ($key == 'management')
                            @can('Fitur Menu Manajemen')
                                <div class="masonry-item">
                                    <div class="card">
                                        <div class="card-body">
                                            <h5 class="text-center card-title">Management</h5>
                                            <div class="row dashboard-section-row" data-section="management">
                                                <div class="col-sm-6 mt-2" data-card-id="set_target">
                                                    <div class="card" id="card-hover">
                                                        <div class="card-body d-flex">
                                                            <div class="col-md-2"><img
                                                                    src="{{ asset('icon/crosshair.svg') }}"
                                                                    class="img-responsive" width="30px"></div>
                                                            <div class="col-md-10" style="margin-left: 10px"><a
                                                                    href="/target"
                                                                    class="link stretched-link text-decoration-none">
                                                                    <h5 class="card-title">Set Target</h5>
                                                                </a>
                                                                <p class="card-text">Manajemen Target.</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endcan
                        @elseif ($key == 'project')
                            @can('Fitur Menu Project')
                                <div class="masonry-item">
                                    <div class="card">
                                        <div class="card-body">
                                            <h5 class="text-center card-title">Project</h5>
                                            <div class="row dashboard-section-row" data-section="project">
                                                <div class="col-sm-6 mt-2" data-card-id="administrasi">
                                                    <div class="card" id="card-hover">
                                                        <div class="card-body d-flex">
                                                            <div class="col-md-2"><img
                                                                    src="{{ asset('icon/folder.svg') }}"
                                                                    class="img-responsive" width="30px"></div>
                                                            <div class="col-md-10" style="margin-left: 10px"><a
                                                                    href="/projects/administrasi"
                                                                    class="link stretched-link text-decoration-none">
                                                                    <h5 class="card-title">Administrasi</h5>
                                                                </a>
                                                                <p class="card-text">Fitur Administrasi Projek yang akan
                                                                    dilakukan.</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-sm-6 mt-2" data-card-id="lead_projek">
                                                    <div class="card" id="card-hover">
                                                        <div class="card-body d-flex">
                                                            <div class="col-md-2"><img
                                                                    src="{{ asset('icon/layout.svg') }}"
                                                                    class="img-responsive" width="30px"></div>
                                                            <div class="col-md-10" style="margin-left: 10px"><a
                                                                    href="/projects/leads"
                                                                    class="link stretched-link text-decoration-none">
                                                                    <h5 class="card-title">Lead Projek</h5>
                                                                </a>
                                                                <p class="card-text">Fitur Lead Projek yang akan
                                                                    dilakukan.</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-sm-6 mt-2" data-card-id="kanban_project">
                                                    <div class="card" id="card-hover">
                                                        <div class="card-body d-flex">
                                                            <div class="col-md-2"><img
                                                                    src="{{ asset('icon/grid-3x3.svg') }}"
                                                                    class="img-responsive" width="30px"></div>
                                                            <div class="col-md-10" style="margin-left: 10px"><a
                                                                    href="/projects/kanban"
                                                                    class="link stretched-link text-decoration-none">
                                                                    <h5 class="card-title">Kanban</h5>
                                                                </a>
                                                                <p class="card-text">Kanban Teknis untuk Projek.</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-sm-6 mt-2" data-card-id="laporan_penjualan_project">
                                                    <div class="card" id="card-hover">
                                                        <div class="card-body d-flex">
                                                            <div class="col-md-2"><img
                                                                    src="{{ asset('icon/chart-no-axes-column.svg') }}"
                                                                    class="img-responsive" width="30px"></div>
                                                            <div class="col-md-10" style="margin-left: 10px"><a
                                                                    href="/projects/reports/sales"
                                                                    class="link stretched-link text-decoration-none">
                                                                    <h5 class="card-title">Laporan Penjualan Projek</h5>
                                                                </a>
                                                                <p class="card-text">Dashboard Penjualan Projek.</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                @can('Menu Visit Project')
                                                    <div class="col-sm-6 mt-2" data-card-id="aktivitas_visit_projek">
                                                        <div class="card" id="card-hover">
                                                            <div class="card-body d-flex">
                                                                <div class="col-md-2"><img
                                                                        src="{{ asset('icon/visit.svg') }}"
                                                                        class="img-responsive" width="30px"></div>
                                                                <div class="col-md-10" style="margin-left: 10px"><a
                                                                        href="/visit-projects"
                                                                        class="link stretched-link text-decoration-none">
                                                                        <h5 class="card-title">Aktivitas Visit Projek</h5>
                                                                    </a>
                                                                    <p class="card-text">Aktivitas Visit Projek.</p>
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
                        @endif
                    @endforeach
                </div>

                <script>
                    window.savedCardOrders = @json($savedCardOrders);
                </script>
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
                                                    <div class="col-md-2"><i class="fa-solid fa-sliders"
                                                            style="font-size: 30px;"></i></div>
                                                    <div class="col-md-10" style="margin-left: 10px"><a
                                                            href="{{ route('admin.layout-setting') }}"
                                                            class="link stretched-link text-decoration-none">
                                                            <h5 class="card-title">Setting Dashboard</h5>
                                                        </a>
                                                        <p class="card-text">Atur card dashboard secara drag &amp; drop.
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-sm-6 mt-2">
                                            <div class="card" id="card-hover">
                                                <div class="card-body d-flex">
                                                    <div class="col-md-2"><img src="{{ asset('icon/terminal.svg') }}"
                                                            class="img-responsive" width="30px"></div>
                                                    <div class="col-md-10" style="margin-left: 10px"><a
                                                            href="/inixcoffeeloglarapelixb95"
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
                                                    <div class="col-md-2"><img src="{{ asset('icon/shield.svg') }}"
                                                            class="img-responsive" width="30px"></div>
                                                    <div class="col-md-10" style="margin-left: 10px"><a
                                                            href="/permissions"
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
                                                    <div class="col-md-2"><img src="{{ asset('icon/lock.svg') }}"
                                                            class="img-responsive" width="30px"></div>
                                                    <div class="col-md-10" style="margin-left: 10px"><a href="/roles"
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
                                                    <div class="col-md-2"><img src="{{ asset('icon/settings.svg') }}"
                                                            class="img-responsive" width="30px"></div>
                                                    <div class="col-md-10" style="margin-left: 10px"><a
                                                            href="/userRolePermissions"
                                                            class="link stretched-link text-decoration-none">
                                                            <h5 class="card-title">Setting User</h5>
                                                        </a>
                                                        <p class="card-text">Users.</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-sm-6 mt-2">
                                            <div class="card" id="card-hover">
                                                <div class="card-body d-flex">
                                                    <div class="col-md-2"><img src="{{ asset('icon/zap.svg') }}"
                                                            class="img-responsive" width="30px"></div>
                                                    <div class="col-md-10" style="margin-left: 10px"><a
                                                            href="/user-dropdown"
                                                            class="link stretched-link text-decoration-none">
                                                            <h5 class="card-title">Shortcut</h5>
                                                        </a>
                                                        <p class="card-text">shortcut.</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endcan
                                    <div class="col-sm-6 mt-2">
                                        <div class="card" id="card-hover">
                                            <div class="card-body d-flex">
                                                <div class="col-md-2"><img src="{{ asset('icon/monitor.svg') }}"
                                                        class="img-responsive" width="30px"></div>
                                                <div class="col-md-10" style="margin-left: 10px"><a
                                                        href="https://webinix.sentry.io/issues/"
                                                        class="link stretched-link text-decoration-none">
                                                        <h5 class="card-title">Sentry Dashboard</h5>
                                                    </a>
                                                    <p class="card-text">Sentry laravel.</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 mt-2">
                                        <div class="card" id="card-hover">
                                            <div class="card-body d-flex">
                                                <div class="col-md-2"><img src="{{ asset('icon/watch.svg') }}"
                                                        class="img-responsive" width="30px"></div>
                                                <div class="col-md-10" style="margin-left: 10px"><a
                                                        href="/schedule-logs"
                                                        class="link stretched-link text-decoration-none">
                                                        <h5 class="card-title">Schedule Logs</h5>
                                                    </a>
                                                    <p class="card-text">Schedule logs for monitoring.</p>
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

            <div class="tab-pane fade" id="pills-dashboard" role="tabpanel"
                aria-labelledby="pills-dashboard-tab">
                @include('partials.dashboard')
            </div>
        </div>
    </main>
    <audio id="notifSound" src="{{ asset('bell.mp3') }}" preload="auto"></audio>

    <script src="{{ asset('assets/vendor/libs/jquery/jquery.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous">
    </script>
    <script src="{{ asset('js/webcam.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/locale/id.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        window.vapidPublicKey = '{{ config('services.vapid.public_key') }}';
        window.csrfToken = '{{ csrf_token() }}';
        window.subscribeUrl = '{{ route('webpush.subscribe') }}';
        window.menusConfig = {
            jabatan: @json(auth()->user()->jabatan),
            idKaryawan: @json(auth()->user()->karyawan_id),
            csrfToken: "{{ csrf_token() }}",
            urls: {
                cekip: "{{ route('cekip') }}",
                activityLogChart: "{{ route('activity.log.chart') }}",
                absensiMasuk: "{{ route('absensi.masuk') }}",
                absensiKeluar: "{{ route('absensi.keluar') }}",
                absensiKaryawan: "{{ route('absensi.karyawan') }}"
            }
        };
    </script>
    @stack('js')
    @stack('scripts')

    @if (session('success') || session('error') || $errors->any())
        @php
            $swalType = '';
            $swalTitle = '';
            $swalText = '';
            $swalHtml = '';
            if (session('success')) {
                $swalType = 'success';
                $swalTitle = 'Berhasil!';
                $swalText = session('success');
            } elseif (session('error')) {
                $swalType = 'error';
                $swalTitle = 'Gagal!';
                $swalText = session('error');
            } elseif ($errors->any()) {
                $swalType = 'error';
                $swalTitle = 'Terjadi Kesalahan!';
                $errorItems = implode('', array_map(fn($e) => '<li>' . e($e) . '</li>', $errors->all()));
                $swalHtml = '<ul style="text-align:left;margin:0;padding-left:20px;">' . $errorItems . '</ul>';
            }
        @endphp
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                setTimeout(function() {
                    Swal.fire({
                        icon: @json($swalType),
                        title: @json($swalTitle),
                        @if ($swalHtml)
                            html: @json($swalHtml),
                        @else
                            text: @json($swalText),
                        @endif
                        showConfirmButton: true,
                        confirmButtonText: 'OK',
                        allowOutsideClick: false
                    });
                }, 300);
            });
        </script>
    @endif
</body>

</html>
