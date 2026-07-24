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
        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">Office</span>
        </li>

        @can('Fitur Menu Office')
            <li class="menu-item {{ request()->is('office/dashboard') ? 'active' : '' }}">
                <a href="{{ route('office.dashboard') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-home-smile"></i>
                    <div class="text-truncate">Dashboard Office</div>
                </a>
            </li>
        @endcan
        
        @can('View Inixcert')
            <li class="menu-item {{ request()->is('office/certificate*') ? 'active' : '' }}">
                <a href="{{ route('office.certificate.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-award"></i>
                    <div class="text-truncate">Generate Sertifikat</div>
                </a>
            </li>
        @endcan

        @can('View Catering')
            <li class="menu-item {{ request()->routeIs('catering.index') ? 'active open' : '' }}">
                <a href="{{ route('catering.index') }}" class="menu-link" target="_blank">
                    <i class="menu-icon tf-icons bx bx-dish"></i>
                    <div class="text-truncate" data-i18n="contact">Catering</div>
                </a>
            </li>   
        @endcan

        @can('View PO Modul')
            <li class="menu-item {{ request()->routeIs('office.modul.index') ? 'active open' : '' }}">
                <a href="{{ route('office.modul.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-cart"></i>
                    <div class="text-truncate" data-i18n="contact">Pemesanan Modul</div>
                </a>
            </li>
        @endcan

        @can('View Tagihan Perusahaan')
            <li class="menu-item {{ request()->routeIs('office.tagihanPerusahaan.index') ? 'active open' : '' }}">
                <a href="{{ route('office.tagihanPerusahaan.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-dollar-circle"></i>
                    <div class="text-truncate" data-i18n="contact">Tagihan Perusahaan</div>
                </a>
            </li>
        @endcan


        @can('View RAB Kegiatan')
            <li class="menu-header small text-uppercase">
                <span class="menu-header-text">Administrasi</span>
            </li>

            <li class="menu-item {{ request()->routeIs('office.indexKegiatan') ? 'active open' : '' }}">               
                <a href="{{ route('office.indexKegiatan') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-group"></i>
                    <div class="text-truncate" data-i18n="contact">Pengajuan Kegiatan</div>
                </a>
            </li>
        @endcan

        @can('View Administrasi Karyawan')
            <li class="menu-item {{ request()->routeIs('administrasi.karyawan') ? 'active open' : '' }}">
                <a href="{{ route('administrasi.karyawan') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-receipt"></i>
                    <div class="text-truncate" data-i18n="contact">Administrasi Karyawan</div>
                </a>
            </li>
        @endcan

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
        
        @can('View Alias')
        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">Alias</span>
        </li>

        <li class="menu-item {{ request()->routeIs('office.alias.index') ? 'active open' : '' }}">
            <a href="{{ route('office.alias.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-bar-chart-alt-2"></i>
                <div class="text-truncate" data-i18n="contact">Alias</div>
            </a>
        </li>
        @endcan

        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">EXAM</span>
        </li>

        <li class="menu-item {{ request()->routeIs('office.exam.index') ? 'active open' : '' }}">
            <a href="{{ route('office.exam.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-clipboard"></i>
                <div class="text-truncate" data-i18n="contact">Data Exam</div>
            </a>
        </li>

        @can('View RekapExam')
            <li class="menu-item {{ request()->routeIs('office.exam.rekap.index') ? 'active open' : '' }}">
                <a href="{{ route('office.exam.rekap.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-bar-chart-alt-2"></i>
                    <div class="text-truncate" data-i18n="contact">Rekap Exam</div>
                </a>
            </li>
        @endcan

        @can('View PoSertifa')
            <li class="menu-item {{ request()->routeIs('office.exam.po-exam-sertifa.index') ? 'active open' : '' }}">
                <a href="{{ route('office.certifa.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-spreadsheet"></i>
                    <div class="text-truncate" data-i18n="contact">PO Exam Sertifa</div>
                </a>
            </li>
        @endcan

        @canany(['View Souvenir', 'View PengajuanSouvenir', 'View Vendor Office', 'View PenambahanSouvenir', 'View PenukaranSouvenir', 'View DashboardSouvenir'])
            <li class="menu-header small text-uppercase">
                <span class="menu-header-text">Souvenir</span>
            </li>
        @endcanany

        @can('View Souvenir')
            <li class="menu-item {{ request()->routeIs('souvenir.index') ? 'active open' : '' }}">
                <a href="{{ route('souvenir.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-gift"></i>
                    <div class="text-truncate" data-i18n="contact">Souvenir</div>
                </a>
            </li>
        @endcan

        @can('View PengajuanSouvenir')
            <li class="menu-item {{ request()->routeIs('pengajuansouvenir.index') ? 'active open' : '' }}">
                <a href="{{ route('pengajuansouvenir.index') }}" class="menu-link" target="_blank">
                    <i class="menu-icon tf-icons bx bxs-gift"></i>
                    <div class="text-truncate" data-i18n="contact">Pengajuan Souvenir</div>
                </a>
            </li>
        @endcan

        @can('View Vendor Office')
            <li class="menu-item {{ request()->routeIs('office.vendor.souvenir.index') ? 'active open' : '' }}">
                <a href="{{ route('office.vendor.souvenir.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-donate-heart"></i>
                    <div class="text-truncate" data-i18n="contact">Vendor Souvenir</div>
                </a>
            </li>
        @endcan

        @can('View PenambahanSouvenir')
            <li class="menu-item {{ request()->routeIs('penambahansouvenir.index') ? 'active open' : '' }}">
                <a href="{{ route('penambahansouvenir.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-add-to-queue"></i>
                    <div class="text-truncate" data-i18n="contact">Penambahan Souvenir</div>
                </a>
            </li>
        @endcan

        @can('View PenukaranSouvenir')
            <li class="menu-item {{ request()->routeIs('penukaransouvenir.index') ? 'active open' : '' }}">
                <a href="{{ route('penukaransouvenir.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-shuffle"></i>
                    <div class="text-truncate" data-i18n="contact">Penukaran Souvenir</div>
                </a>
            </li>
        @endcan
        
        @can('View DashboardSouvenir')
            <li class="menu-item {{ request()->routeIs('dashboard.souvenir') ? 'active open' : '' }}">
                <a href="{{ route('dashboard.souvenir') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-home-alt"></i>
                    <div class="text-truncate" data-i18n="contact">Dashboard Souvenir</div>
                </a>
            </li>
        @endcan
        
        @canany(['View DaftarTugas OB', 'View StockOpname', 'View KondisiTools', 'View KoordinasiOfficeBoy'])
            <li class="menu-header small text-uppercase">
                <span class="menu-header-text">Office Boy</span>
            </li>
        @endcanany

        @can('View DaftarTugas OB')
            <li class="menu-item {{ request()->routeIs('office.DaftarTugas.index') ? 'active open' : '' }}">
                <a href="{{ route('office.DaftarTugas.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-group"></i>
                    <div class="text-truncate" data-i18n="contact">Daftar Tugas</div>
                </a>
            </li>
        @endcan

        @can('View StockOpname')
            <li class="menu-item {{ request()->routeIs('office.stockOpname.index') ? 'active open' : '' }}">
                <a href="{{ route('office.stockOpname.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-package"></i>
                    <div class="text-truncate" data-i18n="contact">Stock Opname</div>
                </a>
            </li>
        @endcan

        @can('View KondisiTools')
            <li class="menu-item {{ request()->routeIs('office.KondisiTools.index') ? 'active open' : '' }}">
                <a href="{{ route('office.KondisiTools.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-list-ul"></i>
                    <div class="text-truncate" data-i18n="contact">Kondisi Tools</div>
                </a>
            </li>
        @endcan

        @can('View KoordinasiOfficeBoy')
            <li class="menu-item {{ request()->routeIs('office.KoordinasiOb.index') ? 'active open' : '' }}">
                <a href="{{ route('office.KoordinasiOb.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-user-plus"></i>
                    <div class="text-truncate" data-i18n="contact">Koordinasi OB</div>
                </a>
            </li>
        @endcan

        @canany(['View PickupDriver', 'View BiayaTransportasi', 'View KondisiKendaraan', 'View PerbaikanKendaraan'])
            <li class="menu-header small text-uppercase">
                <span class="menu-header-text">Driver</span>
            </li>
        @endcanany

        @can('View PickupDriver')
            <li class="menu-item {{ request()->routeIs('office.pickupDriver.index') ? 'active open' : '' }}">
                <a href="{{ route('office.pickupDriver.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-car"></i>
                    <div class="text-truncate" data-i18n="contact">Koordinasi Driver</div>
                </a>
            </li>
        @endcan

        @can('View BiayaTransportasi')
            <li class="menu-item {{ request()->routeIs('office.biayaTransportasi.index') ? 'active open' : '' }}">
                <a href="{{ route('office.biayaTransportasi.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-receipt"></i>
                    <div class="text-truncate" data-i18n="contact">Biaya Transportasi</div>
                </a>
            </li>
        @endcan

        @can('View KondisiKendaraan')
            <li class="menu-item {{ request()->routeIs('office.indexKondisiKendaraan') ? 'active open' : '' }}">
                <a href="{{ route('office.indexKondisiKendaraan') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-clipboard"></i>
                    <div class="text-truncate" data-i18n="contact">Kondisi Kendaraan</div>
                </a>
            </li>
        @endcan

        @can('View PerbaikanKendaraan')
            <li class="menu-item {{ request()->routeIs('office.indexPerbaikanKendaraan') ? 'active open' : '' }}">
                <a href="{{ route('office.indexPerbaikanKendaraan') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-wrench"></i>
                    <div class="text-truncate" data-i18n="contact">Perbaikan Kendaraan</div>
                </a>
            </li>
        @endcan

        @canany(['View LaporanAnalisis Accounting', 'View PicPenagihan', 'View ApprovalPendapatan', 'View SOP Perusahaan'])
            <li class="menu-header small text-uppercase">
                <span class="menu-header-text">Accounting</span>
            </li>
        @endcanany

        @can('View LaporanAnalisis Accounting')
            <li class="menu-item {{ request()->routeIs('index.analysis') ? 'active open' : '' }}">
                <a href="{{ route('index.analysis') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-wrench"></i>
                    <div class="text-truncate" data-i18n="contact">Jumlah laporan Analisis</div>
                </a>
            </li>
        @endcan

        @can('View PicPenagihan')
            <li class="menu-item {{ request()->routeIs('pic-penagihan.index') ? 'active open' : '' }}">
                <a href="{{ route('picpenagihan.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-receipt"></i>
                    <div class="text-truncate" data-i18n="contact">DB PIC Penagihan</div>
                </a>
            </li>
        @endcan

        @can('View ApprovalPendapatan')
            <li class="menu-item {{ request()->routeIs('approvalPendapatan.index') ? 'active open' : '' }}">
                <a href="{{ route('approvalPendapatan.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-money"></i>
                    <div class="text-truncate" data-i18n="contact">Approval Pendapatan</div>
                </a>
            </li>
        @endcan

        @canany(['View LaporanAnalisis Accounting', 'View PicPenagihan', 'View ApprovalPendapatan', 'View SOP Perusahaan'])
            <li class="menu-item {{ request()->is('outstanding') ? 'active open' : '' }}">
                <a href="/outstanding" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-task"></i>
                    <div class="text-truncate" data-i18n="contact">Outstanding</div>
                </a>
            </li>
        @endcanany

        @can('View SOP Perusahaan')
            <li class="menu-item {{ request()->routeIs('sop.perusahaan.index') ? 'active open' : '' }}">
                <a href="{{ route('sop.perusahaan.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-task"></i>
                    <div class="text-truncate" data-i18n="contact">Persyaratan Dokumen</div>
                </a>
            </li>
        @endcan

        <li class="menu-header mt-4 pb-3" style="padding-left: 12px; padding-right: 12px;">
            <a href="{{ route('home') }}"
                class="btn btn-primary d-flex align-items-center justify-content-center w-100">
                <i class="bx bx-home me-2"></i>BACK TO INIXCOFFE
            </a>
        </li>

    </ul>
</aside>
