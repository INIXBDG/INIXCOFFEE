<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
    <style>
        /* ===== Sidebar Modern Theme ===== */
        .layout-menu {
            background: #ffffff !important;
            border-right: 1px solid #f1f5f9 !important;
            box-shadow: 2px 0 12px rgba(0, 0, 0, .03) !important;
        }

        /* Brand Header */
        .app-brand {
            padding: 1.5rem 1.25rem !important;
            border-bottom: 1px solid #f1f5f9;
        }

        .app-brand-link {
            display: flex;
            align-items: center;
            text-decoration: none !important;
        }

        .app-brand-logo {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff !important;
            box-shadow: 0 4px 12px rgba(99, 102, 241, .25);
        }

        .app-brand-logo svg {
            width: 22px !important;
            height: 22px !important;
        }

        .app-brand-text {
            font-size: 1.1rem !important;
            font-weight: 700 !important;
            color: #1e293b !important;
            letter-spacing: -.5px;
        }

        /* Menu Inner */
        .menu-inner {
            padding: 1rem .75rem !important;
        }

        .menu-inner::-webkit-scrollbar {
            width: 4px;
        }

        .menu-inner::-webkit-scrollbar-track {
            background: transparent;
        }

        .menu-inner::-webkit-scrollbar-thumb {
            background: #e2e8f0;
            border-radius: 10px;
        }

        .menu-inner::-webkit-scrollbar-thumb:hover {
            background: #cbd5e1;
        }

        /* Section Header (KPI, Penilaian 360°, dll) */
        .menu-header {
            font-size: .7rem !important;
            font-weight: 700 !important;
            text-transform: uppercase !important;
            letter-spacing: .8px !important;
            color: #94a3b8 !important;
            padding: 1.25rem 1rem .5rem !important;
            margin: 0 !important;
        }

        .menu-header-item {
            padding: .25rem 1rem .5rem !important;
            margin: 0 !important;
        }

        .menu-header-item .menu-title {
            font-size: .7rem !important;
            font-weight: 700 !important;
            text-transform: uppercase !important;
            letter-spacing: .8px !important;
            color: #94a3b8 !important;
            opacity: 1 !important;
        }

        /* Menu Item */
        .menu-item {
            margin-bottom: 2px !important;
        }

        .menu-link {
            display: flex !important;
            align-items: center !important;
            padding: .7rem 1rem !important;
            border-radius: 10px !important;
            color: #475569 !important;
            font-size: .875rem !important;
            font-weight: 500 !important;
            transition: all .2s ease !important;
            text-decoration: none !important;
            margin-left: 0 !important;
            position: relative;
        }

        .menu-link:hover {
            background: #f8fafc !important;
            color: #6366f1 !important;
        }

        .menu-link:hover .menu-icon {
            color: #6366f1 !important;
        }

        /* Menu Icon */
        .menu-icon {
            font-size: 1rem !important;
            width: 32px !important;
            min-width: 32px !important;
            height: 32px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            border-radius: 8px !important;
            background: #f1f5f9 !important;
            color: #64748b !important;
            margin-right: .75rem !important;
            transition: all .2s ease !important;
        }

        .menu-link:hover .menu-icon {
            background: rgba(99, 102, 241, .1) !important;
            color: #6366f1 !important;
        }

        /* Active State */
        .menu-item.active .menu-link,
        .menu-item.active-item .menu-link {
            background: linear-gradient(135deg, rgba(99, 102, 241, .08), rgba(139, 92, 246, .08)) !important;
            color: #6366f1 !important;
            font-weight: 600 !important;
        }

        .menu-item.active .menu-icon,
        .menu-item.active-item .menu-icon {
            background: linear-gradient(135deg, #6366f1, #8b5cf6) !important;
            color: #fff !important;
            box-shadow: 0 4px 10px rgba(99, 102, 241, .3) !important;
        }

        /* Sub Menu */
        .sub-menu {
            padding: .25rem 0 .25rem 2.75rem !important;
            margin: 0 !important;
        }

        .sub-menu .menu-item {
            margin-bottom: 0 !important;
        }

        .sub-menu .menu-link {
            padding: .5rem 1rem !important;
            font-size: .825rem !important;
            border-radius: 8px !important;
            position: relative;
        }

        .sub-menu .menu-link::before {
            content: '';
            position: absolute;
            left: -.5rem;
            top: 50%;
            transform: translateY(-50%);
            width: 4px;
            height: 4px;
            border-radius: 50%;
            background: #cbd5e1;
            transition: all .2s ease;
        }

        .sub-menu .menu-link:hover::before,
        .sub-menu .menu-item.active .menu-link::before {
            background: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, .15);
        }

        .sub-menu .menu-icon {
            display: none !important;
        }

        /* Menu Arrow */
        .menu-arrow {
            margin-left: auto !important;
            font-size: .75rem !important;
            color: #94a3b8 !important;
            transition: transform .2s ease !important;
        }

        .menu-link[aria-expanded="true"] .menu-arrow {
            transform: rotate(90deg);
        }

        /* Collapse */
        .collapse {
            margin-top: .25rem !important;
        }

        /* Footer Button */
        .sidebar-footer {
            padding: 1rem;
            border-top: 1px solid #f1f5f9;
            margin-top: auto;
        }

        .btn-back-home {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            padding: .75rem 1rem;
            border-radius: 12px;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            color: #fff !important;
            font-weight: 600;
            font-size: .8rem;
            letter-spacing: .3px;
            text-decoration: none !important;
            border: 0;
            box-shadow: 0 4px 14px rgba(99, 102, 241, .3);
            transition: all .25s ease;
        }

        .btn-back-home:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(99, 102, 241, .4);
            color: #fff !important;
        }

        .btn-back-home i {
            font-size: 1.1rem;
            margin-right: .5rem;
        }

        /* Menu Divider */
        .menu-divider {
            border-color: #f1f5f9 !important;
            margin: .5rem 0 !important;
        }

        /* Hide shadow element */
        .menu-inner-shadow {
            display: none !important;
        }
    </style>

    <div class="app-brand demo">
        <a href="{{ route('berandaKPI.get') }}" class="app-brand-link">
            <span class="app-brand-logo demo">
                <span class="text-white">
                    <svg width="25" viewBox="0 0 25 42" version="1.1" xmlns="http://www.w3.org/2000/svg"
                        xmlns:xlink="http://www.w3.org/1999/xlink">
                        <defs>
                            <path
                                d="M13.7918663,0.358365126 L3.39788168,7.44174259 C0.566865006,9.69408886 -0.379795268,12.4788597 0.557900856,15.7960551 C0.68998853,16.2305145 1.09562888,17.7872135 3.12357076,19.2293357 C3.8146334,19.7207684 5.32369333,20.3834223 7.65075054,21.2172976 L7.59773219,21.2525164 L2.63468769,24.5493413 C0.445452254,26.3002124 0.0884951797,28.5083815 1.56381646,31.1738486 C2.83770406,32.8170431 5.20850219,33.2640127 7.09180128,32.5391577 C8.347334,32.0559211 11.4559176,30.0011079 16.4175519,26.3747182 C18.0338572,24.4997857 18.6973423,22.4544883 18.4080071,20.2388261 C17.963753,17.5346866 16.1776345,15.5799961 13.0496516,14.3747546 L10.9194936,13.4715819 L18.6192054,7.984237 L13.7918663,0.358365126 Z"
                                id="path-1"></path>
                            <path
                                d="M5.47320593,6.00457225 C4.05321814,8.216144 4.36334763,10.0722806 6.40359441,11.5729822 C8.61520715,12.571656 10.0999176,13.2171421 10.8577257,13.5094407 L15.5088241,14.433041 L18.6192054,7.984237 C15.5364148,3.11535317 13.9273018,0.573395879 13.7918663,0.358365126 C13.5790555,0.511491653 10.8061687,2.3935607 5.47320593,6.00457225 Z"
                                id="path-3"></path>
                            <path
                                d="M7.50063644,21.2294429 L12.3234468,23.3159332 C14.1688022,24.7579751 14.397098,26.4880487 13.008334,28.506154 C11.6195701,30.5242593 10.3099883,31.790241 9.07958868,32.3040991 C5.78142938,33.4346997 4.13234973,34 4.13234973,34 C4.13234973,34 2.75489982,33.0538207 2.37032616e-14,31.1614621 C-0.55822714,27.8186216 -0.55822714,26.0572515 -4.05231404e-15,25.8773518 C0.83734071,25.6075023 2.77988457,22.8248993 3.3049379,22.52991 C3.65497346,22.3332504 5.05353963,21.8997614 7.50063644,21.2294429 Z"
                                id="path-4"></path>
                            <path
                                d="M20.6,7.13333333 L25.6,13.8 C26.2627417,14.6836556 26.0836556,15.9372583 25.2,16.6 C24.8538077,16.8596443 24.4327404,17 24,17 L14,17 C12.8954305,17 12,16.1045695 12,15 C12,14.5672596 12.1403557,14.1461923 12.4,13.8 L17.4,7.13333333 C18.0627417,6.24967773 19.3163444,6.07059163 20.2,6.73333333 C20.3516113,6.84704183 20.4862915,6.981722 20.6,7.13333333 Z"
                                id="path-5"></path>
                        </defs>
                        <g id="g-app-brand" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                            <g id="Brand-Logo" transform="translate(-27.000000, -15.000000)">
                                <g id="Icon" transform="translate(27.000000, 15.000000)">
                                    <g id="Mask" transform="translate(0.000000, 8.000000)">
                                        <mask id="mask-2" fill="white">
                                            <use xlink:href="#path-1"></use>
                                        </mask>
                                        <use fill="currentColor" xlink:href="#path-1"></use>
                                        <g id="Path-3" mask="url(#mask-2)">
                                            <use fill="currentColor" xlink:href="#path-3"></use>
                                            <use fill-opacity="0.2" fill="#FFFFFF" xlink:href="#path-3"></use>
                                        </g>
                                        <g id="Path-4" mask="url(#mask-2)">
                                            <use fill="currentColor" xlink:href="#path-4"></use>
                                            <use fill-opacity="0.2" fill="#FFFFFF" xlink:href="#path-4"></use>
                                        </g>
                                    </g>
                                    <g id="Triangle"
                                        transform="translate(19.000000, 11.000000) rotate(-300.000000) translate(-19.000000, -11.000000) ">
                                        <use fill="currentColor" xlink:href="#path-5"></use>
                                        <use fill-opacity="0.2" fill="#FFFFFF" xlink:href="#path-5"></use>
                                    </g>
                                </g>
                            </g>
                        </g>
                    </svg>
                </span>
            </span>
            <span class="app-brand-text demo menu-text ms-2">INIX - KPI</span>
        </a>
    </div>

    <div class="menu-divider mt-0"></div>

    <ul class="menu-inner py-1">
        {{-- ===== MAIN ===== --}}
        <li class="menu-header-item">
            <span class="menu-title">Main</span>
        </li>
        <li class="menu-item {{ Request::routeIs('berandaKPI.get') ? 'active-item' : '' }}">
            <a class="menu-link" href="{{ route('berandaKPI.get') }}">
                <i class="menu-icon fa-solid fa-house"></i>
                <span class="menu-title">Dashboard</span>
            </a>
        </li>

        {{-- ===== KPI SECTION ===== --}}
        @php $auth = Auth()->user()->jabatan; @endphp

        <li class="menu-header-item">
            <span class="menu-title">KPI</span>
        </li>

        @if (
            $auth === 'Koordinator ITSM' ||
                $auth === 'HRD' ||
                $auth === 'Education Manager' ||
                $auth === 'GM' ||
                $auth === 'SPV Sales')
            <li class="menu-item {{ Request::routeIs('kpi.index') ? 'active' : '' }}">
                <a class="menu-link" href="{{ route('kpi.index') }}">
                    <i class="menu-icon fa-solid fa-bullseye"></i>
                    <span class="menu-title">Target Divisi</span>
                </a>
            </li>
            <li class="menu-item {{ Request::routeIs('kpi.overview.index') ? 'active' : '' }}">
                <a class="menu-link" href="{{ route('kpi.overview.index') }}">
                    <i class="menu-icon fa-solid fa-users-viewfinder"></i>
                    <span class="menu-title">Overview Departement</span>
                </a>
            </li>
        @endif

        <li class="menu-item {{ Request::routeIs('kpi.overview.indexPersonal') ? 'active' : '' }}">
            <a class="menu-link" href="{{ route('kpi.overview.indexPersonal') }}">
                <i class="menu-icon fa-solid fa-user-check"></i>
                <span class="menu-title">Overview Personal</span>
            </a>
        </li>

        @if ($auth === 'GM' || $auth === 'HRD' || $auth === 'Direktur Utama' || $auth === 'Direktur')
            <li class="menu-item {{ Request::routeIs('kpi.dataTarget.index') ? 'active' : '' }}">
                <a class="menu-link" href="{{ route('kpi.dataTarget.index') }}">
                    <i class="menu-icon fa-solid fa-database"></i>
                    <span class="menu-title">Data Target</span>
                </a>
            </li>
        @endif

        {{-- ===== PENILAIAN 360° SECTION ===== --}}
        <li class="menu-header-item">
            <span class="menu-title">Penilaian 360°</span>
        </li>

        @if ($auth === 'GM' || $auth === 'HRD' || $auth === 'Direktur Utama' || $auth === 'Direktur')
            <li class="menu-item">
                <a class="menu-link" data-bs-toggle="collapse" href="#forms" aria-expanded="true"
                    aria-controls="forms">
                    <i class="menu-icon fa-solid fa-table"></i>
                    <span class="menu-title">Table Penilaian</span>
                    <i class="menu-arrow fa-solid fa-chevron-right"></i>
                </a>
                <div class="collapse show" id="forms">
                    <ul class="nav flex-column sub-menu">
                        <li class="menu-item {{ request('tipe') === 'rutin' ? 'active' : '' }}">
                            <a class="menu-link" href="{{ route('ketegoriKPI.get', ['tipe' => 'rutin']) }}">
                                <span class="menu-title">Rutin</span>
                            </a>
                        </li>
                        <li class="menu-item {{ request('tipe') === 'probation' ? 'active' : '' }}">
                            <a class="menu-link" href="{{ route('ketegoriKPI.get', ['tipe' => 'probation']) }}">
                                <span class="menu-title">Probation</span>
                            </a>
                        </li>
                        <li class="menu-item {{ request('tipe') === 'kontrak' ? 'active' : '' }}">
                            <a class="menu-link" href="{{ route('ketegoriKPI.get', ['tipe' => 'kontrak']) }}">
                                <span class="menu-title">Kontrak</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            <li class="menu-item {{ Request::routeIs('ketegori.kpi.create') ? 'active' : '' }}">
                <a class="menu-link" href="{{ route('ketegori.kpi.create') }}">
                    <i class="menu-icon fa-solid fa-square-plus"></i>
                    <span class="menu-title">Buat Penilaian</span>
                </a>
            </li>

            <li class="menu-item {{ Request::routeIs('penilaian.form.data') ? 'active' : '' }}">
                <a class="menu-link" href="{{ route('penilaian.form.data') }}">
                    <i class="menu-icon fa-solid fa-laptop-file"></i>
                    <span class="menu-title">Data Form</span>
                </a>
            </li>
        @endif

        @php
            $id_karyawan = Auth()->user()->karyawan_id;
        @endphp

        @if ($auth != 'Direktur Utama' && $auth != 'Direktur')
            <li class="menu-item {{ Request::is('penilaian360/index*') ? 'active' : '' }}">
                <a class="menu-link" href="{{ url('/penilaian360/index/' . $id_karyawan) }}">
                    <i class="menu-icon fa-solid fa-file-lines"></i>
                    <span class="menu-title">Hasil Penilaian Anda</span>
                </a>
            </li>
            <li class="menu-item {{ Request::is('getFormPenilaianUser*') ? 'active' : '' }}">
                <a class="menu-link" href="{{ url('/getFormPenilaianUser/' . $id_karyawan) }}">
                    <i class="menu-icon fa-solid fa-pen-to-square"></i>
                    <span class="menu-title">Form Penilaian</span>
                </a>
            </li>
        @endif
    </ul>

    <div class="sidebar-footer mt-auto">
        <a href="{{ route('home') }}" class="btn-back-home">
            <i class="bx bx-home"></i>
            <span>BACK TO INIXCOFFE</span>
        </a>
    </div>
</aside>
