<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme glass-force">
    <div class="app-brand demo">
        <a href="{{ route('office.dashboard') }}" class="app-brand-link">
            <span class="app-brand-logo demo text-primary">
                {{-- Logo SVG atau Image --}}
            </span>
            <span class="app-brand-text demo menu-text fw-bold ms-2">INIXCOFFEE</span>
        </a>
        <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-xl-none">
            <i class="bx bx-chevron-left bx-sm align-middle"></i>
        </a>
    </div>

    <div class="menu-inner-shadow"></div>

    <ul class="menu-inner py-1">
        @php
            $user = Auth::user();
            $allowedRoles = [
                'Adm Sales',
                'HRD',
                'Finance & Accounting',
                'GM',
                'Sales',
                'Direktur Utama',
                'Direktur',
                'SPV Sales',
                'Customer Care',
                'Admin Holding',
            ];
        @endphp

        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">Office</span>
        </li>


        <li class="menu-item {{ request()->is('office/dashboard') ? 'active' : '' }}">
            <a href="{{ route('office.dashboard') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-home-smile"></i>
                <div class="text-truncate">Dashboard Office</div>
            </a>
        </li>
        <li class="menu-item {{ request()->is('office/certificate*') ? 'active' : '' }}">
            <a href="{{ route('office.certificate.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-award"></i>
                <div class="text-truncate">Generate Sertifikat</div>
            </a>
        </li>

        <li class="menu-item {{ request()->routeIs('catering.index') ? 'active open' : '' }}">
            <a href="{{ route('catering.index') }}" class="menu-link" target="_blank">
                <i class="menu-icon tf-icons bx bx-dish"></i>
                <div class="text-truncate" data-i18n="contact">Catering</div>
            </a>
        </li>

        <li class="menu-item {{ request()->routeIs('office.modul.index') ? 'active open' : '' }}">
            <a href="{{ route('office.modul.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-cart"></i>
                <div class="text-truncate" data-i18n="contact">Pemesanan Modul</div>
            </a>
        </li>

        @if (Auth::user()->jabatan === 'Finance & Accounting')
        <li class="menu-item {{ request()->routeIs('office.tagihanPerusahaan.index') ? 'active open' : '' }}">
            <a href="{{ route('office.tagihanPerusahaan.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-dollar-circle"></i>
                <div class="text-truncate" data-i18n="contact">Tagihan Perusahaan</div>
            </a>
        </li>
        @endif

        @if (Auth::user()->jabatan === 'HRD')
            <li class="menu-item {{ request()->routeIs('office.indexKegiatan') ? 'active open' : '' }}">
                <a href="{{ route('office.indexKegiatan') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-group"></i>
                    <div class="text-truncate" data-i18n="contact">Pengajuan Kegiatan</div>
                </a>
            </li>

            <li class="menu-item {{ request()->routeIs('administrasi.karyawan') ? 'active open' : '' }}">
                <a href="{{ route('administrasi.karyawan') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-receipt"></i>
                    <div class="text-truncate" data-i18n="contact">Administrasi Karyawan</div>
                </a>
            </li>
        @endif

        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">Vendor</span>
        </li>

        <li class="menu-item {{ request()->routeIs('office.vendor.makansiang.index') ? 'active open' : '' }}">
            <a href="{{ route('office.vendor.makansiang.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-dish"></i>
                <div class="text-truncate" data-i18n="contact">Makan Siang</div>
            </a>
        </li>
        <li class="menu-item {{ request()->routeIs('office.vendor.coffeebreak.index') ? 'active open' : '' }}">
            <a href="{{ route('office.vendor.coffeebreak.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bxs-coffee"></i>
                <div class="text-truncate" data-i18n="contact">Coffee Break</div>
            </a>
        </li>
        <li class="menu-item {{ request()->routeIs('office.vendor.bengkel.index') ? 'active open' : '' }}">
            <a href="{{ route('office.vendor.bengkel.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-wrench"></i>
                <div class="text-truncate" data-i18n="contact">Bengkel</div>
            </a>
        </li>

        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">Souvenir</span>
        </li>
        <li class="menu-item {{ request()->routeIs('souvenir.index') ? 'active open' : '' }}">
            <a href="{{ route('souvenir.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-gift"></i>
                <div class="text-truncate" data-i18n="contact">Souvenir</div>
            </a>
        </li>
        <li class="menu-item {{ request()->routeIs('pengajuansouvenir.index') ? 'active open' : '' }}">
            <a href="{{ route('pengajuansouvenir.index') }}" class="menu-link" target="_blank">
                <i class="menu-icon tf-icons bx bxs-gift"></i>
                <div class="text-truncate" data-i18n="contact">Pengajuan Souvenir</div>
            </a>
        </li>

        <li class="menu-item {{ request()->routeIs('office.vendor.souvenir.index') ? 'active open' : '' }}">
            <a href="{{ route('office.vendor.souvenir.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-donate-heart"></i>
                <div class="text-truncate" data-i18n="contact">Vendor Souvenir</div>
            </a>
        </li>

        <li class="menu-item {{ request()->routeIs('penambahansouvenir.index') ? 'active open' : '' }}">
            <a href="{{ route('penambahansouvenir.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-add-to-queue"></i>
                <div class="text-truncate" data-i18n="contact">Penambahan Souvenir</div>
            </a>
        </li>

        <li class="menu-item {{ request()->routeIs('penukaransouvenir.index') ? 'active open' : '' }}">
            <a href="{{ route('penukaransouvenir.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-shuffle"></i>
                <div class="text-truncate" data-i18n="contact">Penukaran Souvenir</div>
            </a>
        </li>

        <li class="menu-item {{ request()->routeIs('dashboard.souvenir') ? 'active open' : '' }}">
            <a href="{{ route('dashboard.souvenir') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-home-alt"></i>
                <div class="text-truncate" data-i18n="contact">Dashboard Souvenir</div>
            </a>
        </li>
        @if (Auth::user()->jabatan === 'HRD' || Auth::user()->jabatan === 'Office Boy')
            <li class="menu-header small text-uppercase">
                <span class="menu-header-text">Office Boy</span>
            </li>

            <li class="menu-item {{ request()->routeIs('office.DaftarTugas.index') ? 'active open' : '' }}">
                <a href="{{ route('office.DaftarTugas.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-group"></i>
                    <div class="text-truncate" data-i18n="contact">Daftar Tugas</div>
                </a>
            </li>
        @endif

        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">Driver</span>
        </li>

        <li class="menu-item {{ request()->routeIs('office.pickupDriver.index') ? 'active open' : '' }}">
            <a href="{{ route('office.pickupDriver.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-car"></i>
                <div class="text-truncate" data-i18n="contact">Koordinasi Driver</div>
            </a>
        </li>

        <li class="menu-item {{ request()->routeIs('office.biayaTransportasi.index') ? 'active open' : '' }}">
            <a href="{{ route('office.biayaTransportasi.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-receipt"></i>
                <div class="text-truncate" data-i18n="contact">Biaya Transportasi</div>
            </a>
        </li>

        <li class="menu-item {{ request()->routeIs('office.indexKondisiKendaraan') ? 'active open' : '' }}">
            <a href="{{ route('office.indexKondisiKendaraan') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-clipboard"></i>
                <div class="text-truncate" data-i18n="contact">Kondisi Kendaraan</div>
            </a>
        </li>

        @if(auth()->check() && isset(auth()->user()->karyawan) && in_array(auth()->user()->karyawan->jabatan, ['Finance & Accounting', 'GM', 'HRD', 'Driver']))
            <li class="menu-item {{ request()->routeIs('office.indexPerbaikanKendaraan') ? 'active open' : '' }}">
                <a href="{{ route('office.indexPerbaikanKendaraan') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-wrench"></i>
                    <div class="text-truncate" data-i18n="contact">Perbaikan Kendaraan</div>
                </a>
            </li>
        @endif

        @if(auth()->check() && isset(auth()->user()->karyawan) && in_array(auth()->user()->karyawan->jabatan, ['Finance & Accounting', 'GM']))

            <li class="menu-header small text-uppercase">
                <span class="menu-header-text">Accounting</span>
            </li>

            <li class="menu-item {{ request()->routeIs('index.analysis') ? 'active open' : '' }}">
                <a href="{{ route('index.analysis') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-wrench"></i>
                    <div class="text-truncate" data-i18n="contact">Jumlah laporan Analisis</div>
                </a>
            </li>

            <li class="menu-item {{ request()->is('outstanding') ? 'active open' : '' }}">
                <a href="/outstanding" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-task"></i>
                    <div class="text-truncate" data-i18n="contact">Outstanding</div>
                </a>
            </li>

        @endif

        <li class="menu-header mt-4 pb-3" style="padding-left: 12px; padding-right: 12px;">
            <a href="{{ route('home') }}"
                class="btn btn-primary d-flex align-items-center justify-content-center w-100">
                <i class="bx bx-home me-2"></i>BACK TO INIXCOFFE
            </a>
        </li>

    </ul>
</aside>
