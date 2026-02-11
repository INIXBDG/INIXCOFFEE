<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme" style="display: flex; flex-direction: column; height: 100vh;">
    <div class="app-brand demo" style="flex-shrink: 0;">
        <a href="{{ route('office.dashboard') }}" class="app-brand-link">
            <span class="app-brand-logo demo text-primary">
                {{-- Logo SVG atau Image --}}
            </span>
            <span class="app-brand-text demo menu-text fw-bold ms-2">INIX - OFFICE</span>
        </a>
        <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-xl-none">
            <i class="bx bx-chevron-left bx-sm align-middle"></i>
        </a>
    </div>

    <div class="menu-inner-shadow"></div>

    <ul class="menu-inner py-1" style="flex-grow: 1; overflow-y: auto; overflow-x: hidden; height: 100%;">
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

        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">DRIVER</span>
        </li>

        <li class="menu-item {{ request()->routeIs('office.pickupDriver.index') ? 'active open' : '' }}">
            <a href="{{ route('office.pickupDriver.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-car"></i>
                <div class="text-truncate" data-i18n="contact">Koordinasi Driver</div>
            </a>
        </li>

        @if ($user->jabatan === "Driver")
        
        @endif
        <li class="menu-item {{ request()->routeIs('office.biayaTransportasi.index') ? 'active open' : '' }}">
            <a href="{{ route('office.biayaTransportasi.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-receipt"></i>
                <div class="text-truncate" data-i18n="contact">Biaya Transportasi</div>
            </a>
        </li>


        <li class="menu-item mb-4"></li>
    </ul>

    <div class="sidebar-footer p-3 bg-menu-theme" style="flex-shrink: 0; border-top: 1px solid rgba(0,0,0,0.05);">
        <a href="{{ route('home') }}" class="btn btn-primary w-100 d-flex align-items-center justify-content-center shadow-sm">
            <i class="bx bx-home me-2"></i>
            <span>BACK TO INIXCOFFE</span>
        </a>
    </div>
</aside>