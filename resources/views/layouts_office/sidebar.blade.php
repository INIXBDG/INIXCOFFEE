<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
    <div class="app-brand demo">
        <a href="{{ route('office.dashboard') }}" class="app-brand-link">
            <span class="app-brand-logo demo text-primary">
            </span>
            <span class="app-brand-text demo menu-text fw-bold ms-2">INIX - OFFICE</span>
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

        <!-- Makan Siang -->
        <li class="menu-item {{ request()->routeIs('office.vendor.makansiang.index') ? 'active open' : '' }}">
            <a href="{{ route('office.vendor.makansiang.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-dish"></i>
                <div class="text-truncate" data-i18n="contact">Makan Siang</div>
            </a>
        </li>
        <!-- Coffee Break -->
        <li class="menu-item {{ request()->routeIs('office.vendor.coffeebreak.index') ? 'active open' : '' }}">
            <a href="{{ route('office.vendor.coffeebreak.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bxs-coffee"></i>
                <div class="text-truncate" data-i18n="contact">Coffee Break</div>
            </a>
        </li>
        <!-- Bengkel -->
        <li class="menu-item {{ request()->routeIs('office.vendor.bengkel.index') ? 'active open' : '' }}">
            <a href="{{ route('office.vendor.bengkel.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-wrench"></i>
                <div class="text-truncate" data-i18n="contact">Bengkel</div>
            </a>
        </li>

        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">Souvenir</span>
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
                <div class="text-truncate" data-i18n="contact">Souvenir</div>
            </a>
        </li>

    </ul>

    <!-- Sticky Footer di Sidebar -->
    <div class="sidebar-footer">
        <a href="{{ route('home') }}" class="btn btn-primary w-100 d-flex align-items-center justify-content-center">
            <i class="bx bx-home me-2"></i>
            <span>BACK TO INIXCOFFE</span>
        </a>
    </div>
</aside>
