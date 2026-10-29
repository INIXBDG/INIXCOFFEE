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

            0%,
            100% {
                box-shadow: 0 0 0 0 rgba(220, 38, 38, 0.4);
            }

            50% {
                box-shadow: 0 0 0 6px rgba(220, 38, 38, 0);
            }
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
            to {
                transform: rotate(360deg);
            }
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
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        ::-webkit-scrollbar-track {
            background: transparent;
        }

        ::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 3px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #a1a1c1;
        }

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

        .swal2-container {
            z-index: 999999 !important;
        }

        /* ===== HR GATE FULL PAGE ===== */
        .hr-gate-full {
            position: fixed;
            inset: 0;
            z-index: 99999;
            background: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
        }

        .hr-gate-full .gate-card {
            width: 100%;
            max-width: 420px;
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 14px;
            padding: 2rem 1.75rem;
            box-shadow: 0 10px 40px rgba(0, 0, 0, .08);
            text-align: center;
        }

        .hr-gate-full .gate-icon {
            width: 72px;
            height: 72px;
            margin: 0 auto 1.25rem;
            border-radius: 50%;
            background: #eef2ff;
            color: #4f46e5;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.7rem;
        }

        .hr-gate-full .gate-icon.danger {
            background: #fee2e2;
            color: #dc2626;
        }

        .hr-gate-full h5 {
            font-weight: 700;
            color: #111827;
            margin-bottom: .5rem;
        }

        .hr-gate-full p {
            color: #6b7280;
            font-size: .875rem;
            margin-bottom: 1.5rem;
        }

        .hr-gate-full .form-label {
            font-size: .78rem;
            font-weight: 600;
            color: #4b5563;
            text-align: left;
            display: block;
        }

        .hr-gate-full .form-control {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            height: 44px;
        }

        .hr-gate-full .form-control:focus {
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, .15);
        }

        .hr-gate-full .btn-primary {
            background: #4f46e5;
            border-color: #4f46e5;
            height: 44px;
            font-weight: 600;
            border-radius: 8px;
        }

        .hr-gate-full .btn-primary:hover {
            background: #3730a3;
            border-color: #3730a3;
        }

        .hr-gate-full {
            overflow-y: auto;
        }

        .hr-gate-full .gate-card {
            margin: auto;
        }

        .hr-gate-full textarea.form-control {
            height: auto;
        }
    </style>

    @stack('styles')
</head>

<body>
    @if (app()->environment('staging'))
        <div aria-hidden="true"
            style="position: fixed; top: 50%; left: 50%; z-index: 999998; color: rgba(220, 38, 38, 0.18); font-size: clamp(4rem, 12vw, 10rem); font-weight: 800; letter-spacing: 0.2em; pointer-events: none; transform: translate(-50%, -50%) rotate(-25deg); user-select: none; white-space: nowrap;">
            STAGING</div>
    @endif
    @php
        $hrGate = app(\App\Http\Controllers\HR\HRController::class);
        $hasPassword = (bool) $hrGate->master();
        $isUnlocked = $hasPassword ? $hrGate->unlocked() : false;
        $isHrd = $hrGate->isHrd();
    @endphp


    @if (!$hasPassword)
        <div id="hrGateCreate" class="hr-gate-full">
            <div class="gate-card">
                <div class="gate-icon"><i class="fa-solid fa-key"></i></div>
                <h5>Buat Password Master HR</h5>
                <p>Password ini dipakai untuk membuka seluruh fitur di aplikasi HR.<br>
                    Simpan baik-baik, password ini tidak bisa dilihat kembali.</p>

                @if ($errors->any())
                    <div class="alert alert-danger text-start">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $e)
                                <li>{{ $e }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if ($isHrd)
                    <form method="POST" action="{{ route('HR.master.setup') }}" autocomplete="off" class="text-start">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Password baru <span class="text-danger">*</span></label>
                            <input type="password" name="password" class="form-control" minlength="8" required
                                autofocus>
                            <div class="form-text">Minimal 8 karakter.</div>
                        </div>
                        <div class="mb-4">
                            <label class="form-label">Ulangi password <span class="text-danger">*</span></label>
                            <input type="password" name="password_confirmation" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Buat Password</button>
                    </form>
                @else
                    <div class="alert alert-warning text-start">
                        Hanya HRD yang dapat membuat password Master. Hubungi HRD atau Tim ITSM.
                    </div>
                @endif
            </div>
        </div>
    @elseif (!$isUnlocked)
        <div id="hrGateLock" class="hr-gate-full">
            <div class="gate-card">
                <div class="gate-icon"><i class="fa-solid fa-lock"></i></div>
                <h5>Area Terproteksi</h5>
                <p>Masukkan password Master untuk mengakses seluruh fitur HR.</p>

                <div id="hrUnlockWrap">
                    <form id="formHrUnlock" autocomplete="off" class="text-start">
                        <div class="alert alert-danger py-2 d-none" id="hrUnlockError"></div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="hrUnlockPassword"
                                    placeholder="Masukkan password" autocomplete="new-password" required autofocus>
                                <button type="button" class="btn btn-outline-secondary"
                                    id="btnHrTogglePw">Lihat</button>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100" id="btnHrUnlock">Buka</button>
                    </form>
                    @if ($isHrd)
                        <div class="text-end mt-3">
                            <a href="#" id="linkHrLupa" class="small">Lupa password?</a>
                        </div>
                    @endif
                </div>

                @if ($isHrd)
                    <form id="formHrLupa" autocomplete="off" class="text-start d-none">
                        <div class="alert alert-danger py-2 d-none" id="hrLupaError"></div>
                        <p class="small text-muted mb-3" style="text-align:left">
                            Jawab pertanyaan berikut. Jawaban dan waktu pengisian akan dicatat dan dapat ditinjau di
                            halaman Data Password.
                        </p>

                        <div class="mb-3">
                            <label class="form-label">1. Nomor identifikasi perpajakan yang tercatat atas nama badan
                                usaha tersebut adalah? <span class="text-danger">*</span></label>
                            <input type="text" name="npwp" class="form-control" maxlength="50"
                                autocomplete="off" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">2. Kode registrasi yang menjadi bukti terdaftarnya badan usaha
                                dalam sistem perizinan berusaha adalah? <span class="text-danger">*</span></label>
                            <input type="text" name="nib" class="form-control" maxlength="50"
                                autocomplete="off" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">3. Tanggal yang tercantum sebagai dasar awal terbentuknya badan
                                usaha dalam dokumen pendirian adalah? <span class="text-danger">*</span></label>
                            <input type="text" name="tgl_akta" class="form-control" maxlength="50"
                                autocomplete="off" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">4. Siapa nama pejabat yang tercatat sebagai pemegang posisi
                                tertinggi dalam jajaran direksi perusahaan? <span class="text-danger">*</span></label>
                            <input type="text" name="direktur" class="form-control" maxlength="100"
                                autocomplete="off" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Alasan membuat password baru <span
                                    class="text-danger">*</span></label>
                            <textarea name="alasan" rows="2" class="form-control" style="height:auto" minlength="10" maxlength="1000"
                                required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password Master baru <span class="text-danger">*</span></label>
                            <input type="password" name="password" class="form-control" minlength="8"
                                maxlength="50" autocomplete="new-password" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Ulangi password baru <span class="text-danger">*</span></label>
                            <input type="password" name="password_confirmation" class="form-control" minlength="8"
                                maxlength="50" autocomplete="new-password" required>
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" name="konfirmasi" value="1"
                                id="hrLupaKonf" required>
                            <label class="form-check-label small" for="hrLupaKonf">
                                Saya menyatakan bahwa saya adalah pemilik akun ini dan bertanggung jawab atas pembuatan
                                ulang password.
                            </label>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-light flex-fill" id="btnHrLupaBatal">Batal</button>
                            <button type="submit" class="btn btn-primary flex-fill" id="btnHrLupaSubmit">Simpan
                                Password Baru</button>
                        </div>
                    </form>
                @endif
            </div>
        </div>



        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
        <script>
            $(function() {
                $('#formHrUnlock').on('submit', function(e) {
                    e.preventDefault();
                    const $btn = $('#btnHrUnlock').prop('disabled', true).text('Memeriksa...');
                    $('#hrUnlockError').addClass('d-none');

                    $.post("{{ route('HR.unlock') }}", {
                            password: $('#hrUnlockPassword').val(),
                            _token: $('meta[name="csrf-token"]').attr('content')
                        })
                        .done(function() {
                            window.location.reload();
                        })
                        .fail(function(xhr) {
                            const msg = (xhr.responseJSON && xhr.responseJSON.message) || 'Password salah.';
                            $('#hrUnlockError').removeClass('d-none').text(msg);
                            $('#hrUnlockPassword').val('').trigger('focus');
                        })
                        .always(function() {
                            $btn.prop('disabled', false).text('Buka');
                        });
                });

                $('#btnHrTogglePw').on('click', function() {
                    const $i = $('#hrUnlockPassword');
                    const show = $i.attr('type') === 'password';
                    $i.attr('type', show ? 'text' : 'password');
                    $(this).text(show ? 'Sembunyikan' : 'Lihat');
                });

                $('#linkHrLupa').on('click', function(e) {
                    e.preventDefault();
                    $('#hrUnlockWrap, #hrGateDesc').addClass('d-none');
                    $('#formHrLupa').removeClass('d-none');
                });

                $('#btnHrLupaBatal').on('click', function() {
                    $('#formHrLupa').addClass('d-none')[0].reset();
                    $('#hrLupaError').addClass('d-none');
                    $('#hrUnlockWrap, #hrGateDesc').removeClass('d-none');
                    $('#hrUnlockPassword').trigger('focus');
                });

                $('#formHrLupa').on('submit', function(e) {
                    e.preventDefault();
                    const $btn = $('#btnHrLupaSubmit').prop('disabled', true).text('Menyimpan...');
                    $('#hrLupaError').addClass('d-none');

                    $.post("{{ route('HR.lupa') }}", $(this).serialize() + '&_token=' + encodeURIComponent($('meta[name="csrf-token"]').attr('content')))
                        .done(function() {
                            window.location.reload();
                        })
                        .fail(function(xhr) {
                            const j = xhr.responseJSON;
                            let msg = (j && j.message) || 'Terjadi kesalahan.';
                            if (j && j.errors) msg = Object.values(j.errors).flat().join(' ');
                            $('#hrLupaError').removeClass('d-none').text(msg);
                        })
                        .always(function() {
                            $btn.prop('disabled', false).text('Simpan Password Baru');
                        });
                });
            });
        </script>
    @else
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
                            ...
                        </footer>
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
                    <div class="modal-header border-bottom-0 pb-0"
                        style="background:var(--danger-light);border-radius:12px 12px 0 0;padding:1.1rem 1.5rem">
                        <h5 class="modal-title fw-bold" id="logoutModalLabel" style="color:var(--danger)"><i
                                class="fa-solid fa-right-from-bracket me-2"></i>Konfirmasi Logout</h5>
                        <button class="btn-close" type="button" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body py-4">
                        <p class="text-muted mb-0" style="font-size:.9rem">Apakah Anda yakin ingin mengakhiri sesi
                            saat
                            ini?</p>
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
        <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

        <script src="{{ asset('assets/vendor/js/menu.js') }}"></script>
        <script src="{{ asset('assets/js/main.js') }}"></script>

        <script src="{{ asset('js/compress_validator.js') }}"></script>

        <script src="{{ asset('template_dashboard_HR/vendor/jquery-easing/jquery.easing.min.js') }}"></script>

        <script src="https://code.iconify.design/3/3.1.0/iconify.min.js"></script>
        @if ($hasPassword && !$isUnlocked)
            <script>
                $(function() {
                    $('#formHrUnlock').on('submit', function(e) {
                        e.preventDefault();
                        const $btn = $('#btnHrUnlock').prop('disabled', true).text('Memeriksa...');
                        $('#hrUnlockError').addClass('d-none');

                        $.post("{{ route('HR.unlock') }}", {
                                password: $('#hrUnlockPassword').val(),
                                _token: $('meta[name="csrf-token"]').attr('content')
                            })
                            .done(function() {
                                window.location.reload();
                            })
                            .fail(function(xhr) {
                                const msg = xhr.status === 429 ?
                                    'Terlalu banyak percobaan. Coba lagi sebentar lagi.' :
                                    (xhr.responseJSON && xhr.responseJSON.message) || 'Password salah.';
                                $('#hrUnlockError').removeClass('d-none').text(msg);
                                $('#hrUnlockPassword').val('').trigger('focus');
                            })
                            .always(function() {
                                $btn.prop('disabled', false).text('Buka');
                            });
                    });

                    $('#btnHrTogglePw').on('click', function() {
                        const $i = $('#hrUnlockPassword');
                        const show = $i.attr('type') === 'password';
                        $i.attr('type', show ? 'text' : 'password');
                        $(this).text(show ? 'Sembunyikan' : 'Lihat');
                    });
                });
            </script>
        @endif
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
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        @if ($errors->any())
            @php
                $swalType = 'error';
                $swalTitle = 'Terjadi Kesalahan!';
                $errorItems = implode('', array_map(fn($e) => '<li>' . $e . '</li>', $errors->all()));
                $swalHtml = '<ul style="text-align:left;margin:0;padding-left:20px;">' . $errorItems . '</ul>';
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

        @if ($isUnlocked)
            <script>
                $(function() {
                    const IDLE_SEC = 300; // 5 menit
                    let idleLeft = IDLE_SEC;
                    let idleTimer = null;

                    function startIdle() {
                        idleLeft = IDLE_SEC;
                        clearInterval(idleTimer);
                        idleTimer = setInterval(function() {
                            idleLeft--;
                            const m = String(Math.floor(Math.max(idleLeft, 0) / 60)).padStart(2, '0');
                            const s = String(Math.max(idleLeft, 0) % 60).padStart(2, '0');
                            $('#lockTimer').text(m + ':' + s);

                            if (idleLeft <= 0) {
                                lockNow();
                            }
                        }, 1000);
                    }

                    function lockNow() {
                        $.post("{{ route('HR.lock') }}", {
                            _token: $('meta[name="csrf-token"]').attr('content')
                        }).always(function() {
                            window.location.reload(); // reload → muncul full gate lagi
                        });
                    }

                    // Tombol Kunci di navbar
                    $('#btnLockNow').on('click', function() {
                        if (confirm('Kunci aplikasi HR sekarang?')) {
                            lockNow();
                        }
                    });

                    // Reset timer saat ada aktivitas
                    $(document).on('mousemove keydown click scroll', function() {
                        idleLeft = IDLE_SEC;
                    });

                    // Mulai timer
                    startIdle();
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
    @endif
</body>

</html>
