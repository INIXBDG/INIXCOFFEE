<aside id="layout-menu" class="layout-menu menu-vertical menu bg-sidebar-premium" style="">

    <div class="app-brand demo">
        <a href="{{ route('HR.index') }}" class="app-brand-link">
            <span class="app-brand-logo demo brand-icon-wrapper">
                <i class="iconify brand-icon-primary text-dark" data-icon="mdi:account-cog" data-width="24"
                    data-height="24"></i>

                <i class="iconify brand-icon-animated text-dark" data-icon="mdi:human-greeting-variant" data-width="24"
                    data-height="24"></i>
                <i class="iconify brand-icon-animated text-dark" data-icon="mdi:badge-account-horizontal"
                    data-width="24" data-height="24"></i>
                <i class="iconify brand-icon-animated text-dark" data-icon="mdi:card-account-details" data-width="24"
                    data-height="24"></i>
            </span>
            <span class="app-brand-text demo menu-text fw-bold ms-2 text-dark">
                INIX <span class="brand-accent">HR</span>
            </span>
        </a>

        <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-block d-xl-none">
            <i class="iconify bx bx-chevron-left bx-sm align-middle"></i>
        </a>
    </div>

    <div class="menu-inner-shadow"></div>

    <ul class="menu-inner py-1">

        <li class="menu-item {{ request()->routeIs('HR.index') ? 'active' : '' }}">
            <a href="{{ route('HR.index') }}" class="menu-link">
                <span class="menu-link-icon">
                    <i class="iconify menu-icon" data-icon="mdi:view-dashboard-outline" data-width="20"
                        data-height="20"></i>
                </span>
                <span>Dashboard</span>
                <span class="menu-link-indicator"></span>
            </a>
        </li>

        <li class="menu-item">
            <div class="menu-divider my-2"></div>
        </li>

        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">
                <i class="iconify me-1" data-icon="mdi:people-group-outline" data-width="14" data-height="14"></i>
                Karyawan
            </span>
        </li>

        <li class="menu-item {{ request()->routeIs('HR.employee.*') ? 'active' : '' }}">
            <a href="{{ route('HR.employee.index') }}" class="menu-link">
                <span class="menu-link-icon">
                    <i class="iconify menu-icon" data-icon="mdi:account-group-outline" data-width="20"
                        data-height="20"></i>
                </span>
                <span>Informasi Karyawan</span>
                <span class="menu-link-indicator"></span>
            </a>
        </li>

        <li class="menu-item {{ request()->routeIs('HR.absensi.*') ? 'active' : '' }}">
            <a href="{{ route('HR.absensi.index') }}" class="menu-link">
                <span class="menu-link-icon">
                    <i class="iconify menu-icon" data-icon="mdi:calendar-clock-outline" data-width="20"
                        data-height="20"></i>
                </span>
                <span>Kehadiran Karyawan</span>
                <span class="menu-link-indicator"></span>
            </a>
        </li>

        <li class="menu-item {{ request()->routeIs('HR.reports.*') ? 'active' : '' }}">
            <a href="{{ route('HR.reports.index') }}" class="menu-link">
                <span class="menu-link-icon">
                    <i class="iconify menu-icon" data-icon="mdi:file-chart" data-width="20" data-height="20"></i>
                </span>
                <span>Laporan</span>
                <span class="menu-link-indicator"></span>
            </a>
        </li>

        <li class="menu-item {{ request()->routeIs('HR.hire.*') ? 'active' : '' }}">
            <a href="{{ route('HR.hire.index') }}" class="menu-link">
                <span class="menu-link-icon">
                    <i class="iconify menu-icon" data-icon="mdi:account-search" data-width="20" data-height="20"></i>
                </span>
                <span>Rekrutmen</span>
                <span class="menu-link-indicator"></span>
            </a>
        </li>


        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">
                <i class="iconify me-1" data-icon="mdi:tune" data-width="14" data-height="14"></i>
                Management
            </span>
        </li>

        <li class="menu-item {{ request()->routeIs('HR.structure.*') ? 'active' : '' }}">
            <a href="{{ route('HR.structure.index') }}" class="menu-link">
                <span class="menu-link-icon">
                    <i class="iconify menu-icon" data-icon="mdi:file-tree-outline" data-width="20" data-height="20"></i>
                </span>
                <span>Struktur Inixindo</span>
                <span class="menu-link-indicator"></span>
            </a>
        </li>
        <li class="menu-item {{ request()->routeIs('HR.job_desk.*') ? 'active' : '' }}">
            <a href="{{ route('HR.job_desk.index') }}" class="menu-link">
                <span class="menu-link-icon">
                    <i class="iconify menu-icon" data-icon="mdi:clipboard-text-outline" data-width="20" data-height="20"></i>
                </span>
                <span>Job Desk</span>
                <span class="menu-link-indicator"></span>
            </a>
        </li>

        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">
                <i class="iconify me-1" data-icon="mdi:tune" data-width="14" data-height="14"></i>
                Administrasi
            </span>
        </li>

        <li class="menu-item {{ request()->routeIs('HR.payroll.*') ? 'active' : '' }}">
            <a href="{{ route('HR.payroll.index') }}" class="menu-link">
                <span class="menu-link-icon">
                    <i class="iconify menu-icon" data-icon="mdi:wallet-outline" data-width="20" data-height="20"></i>
                </span>
                <span>Payroll</span>
                <span class="menu-link-indicator"></span>
            </a>
        </li>
        <li class="menu-item {{ request()->routeIs('rencanaPembelian.*') ? 'active' : '' }}">
            <a href="{{ route('rencanaPembelian.index') }}" class="menu-link">
                <span class="menu-link-icon">
                    <i class="iconify menu-icon" data-icon="mdi:cart-arrow-down" data-width="20" data-height="20"></i>
                </span>
                <span>Rencana Pembelian</span>
                <span class="menu-link-indicator"></span>
            </a>
        </li>

        <li class="menu-item {{ request()->routeIs('HR.rekap_spj.*') ? 'active' : '' }}">
            <a href="{{ route('HR.rekap_spj.index') }}" class="menu-link">
                <span class="menu-link-icon">
                    <i class="iconify menu-icon" data-icon="mdi:cash-register" data-width="20" data-height="20"></i>
                </span>
                <span>Rekap SPJ</span>
                <span class="menu-link-indicator"></span>
            </a>
        </li>

        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">
                <i class="iconify me-1" data-icon="mdi:chart-timeline-variant-shimmer" data-width="14"
                    data-height="14"></i>
                Performance
            </span>
        </li>

        <li class="menu-item {{ request()->routeIs('HR.executive.*') ? 'active' : '' }}">
            <a href="{{ route('HR.executive.index') }}" class="menu-link">
                <span class="menu-link-icon">
                    <i class="iconify menu-icon" data-icon="mdi:chart-timeline-variant" data-width="20" data-height="20"></i>
                </span>
                <span>Trend Performance</span>
                <span class="menu-link-indicator"></span>
            </a>
        </li>

        <li class="menu-item {{ request()->routeIs('HR.performance.*') ? 'active' : '' }}">
            <a href="{{ route('HR.performance.index') }}" class="menu-link">
                <span class="menu-link-icon">
                    <i class="iconify menu-icon" data-icon="mdi:chart-bar" data-width="20" data-height="20"></i>
                </span>
                <span>Performance</span>
                <span class="menu-link-indicator"></span>
            </a>
        </li>
    </ul>

    <div class="sidebar-footer px-3 pt-2 pb-3">
        <div class="glass-force footer-card p-3">
            <div class="footer-icon-wrapper mb-2">
                <i class="iconify footer-icon text-white-75" data-icon="mdi:shield-account" data-width="32"
                    data-height="32"></i>
                <span class="footer-icon-pulse"></span>
            </div>
            <p class="small mb-2 text-black-75">
                <strong class="text-black">INIX HR</strong><br>
                <span class="d-none d-lg-inline">
                    Sistem Manajemen SDM<br>
                    <em>aman, cepat & modern</em>
                </span>
            </p>
            <a href="/" class="btn btn-footer-custom w-100">
                <i class="iconify me-1" data-icon="mdi:storefront-outline" data-width="16" data-height="16"></i>
                Inixcoffee
            </a>
        </div>
    </div>

</aside>
<style>
    .bg-sidebar-premium {
        position: relative;
        overflow: hidden;
    }

    /* === BRAND ICON WRAPPER === */
    .brand-icon-wrapper {
        position: relative;
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    /* Paksa semua icon menumpuk di tengah */
    .brand-icon-wrapper i {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%) scale(1);
        transition: opacity 0.4s ease, transform 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
        will-change: transform, opacity;
    }

    /* Hapus animasi cycle lama agar tidak bentrok dengan JS */
    .brand-icon-primary,
    .brand-icon-animated {
        position: absolute;
        opacity: 0;
        filter: drop-shadow(0 0 6px rgba(99, 132, 255, 0.4));
    }

    .brand-icon-primary {
        opacity: 1;
    }

    /* Icon pertama tetap terlihat saat load */

    /* === PARTICLE EFFECT === */
    .icon-particle {
        position: absolute;
        width: 4px;
        height: 4px;
        border-radius: 50%;
        background: #6384ff;
        box-shadow: 0 0 6px #38bdf8, 0 0 10px rgba(99, 132, 255, 0.5);
        pointer-events: none;
        opacity: 0;
        transform: translate(-50%, -50%) scale(0);
    }

    @keyframes particleBurst {
        0% {
            opacity: 1;
            transform: translate(-50%, -50%) scale(1);
        }

        100% {
            opacity: 0;
            transform: translate(calc(-50% + var(--tx)), calc(-50% + var(--ty))) scale(0);
        }
    }

    /* === SISA STYLE TETAP SAMA (Menu, Footer, dll) === */
    .brand-accent {
        background: linear-gradient(135deg, #6384ff, #38bdf8);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        animation: accentShift 8s ease-in-out infinite alternate;
    }

    @keyframes accentShift {
        0% {
            filter: hue-rotate(0deg);
        }

        100% {
            filter: hue-rotate(30deg);
        }
    }

    .menu-link {
        border-radius: 8px !important;
        margin: 2px 8px !important;
        padding: 8px 12px !important;
        transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1) !important;
        position: relative;
        overflow: hidden;
    }

    .menu-link:hover {
        background: rgba(99, 132, 255, 0.12) !important;
        transform: translateX(3px);
        box-shadow: 0 2px 12px rgba(99, 132, 255, 0.15);
    }

    .menu-link:hover .menu-icon {
        transform: scale(1.15);
    }

    .menu-item.active .menu-link {
        background: linear-gradient(135deg, rgba(99, 132, 255, 0.25), rgba(56, 189, 248, 0.15)) !important;
        box-shadow: 0 2px 16px rgba(99, 132, 255, 0.2), inset 0 0 0 1px rgba(99, 132, 255, 0.3);
        font-weight: 600;
    }

    .menu-item.active .menu-link::after {
        content: '';
        position: absolute;
        left: 0;
        top: 15%;
        width: 3px;
        height: 70%;
        background: linear-gradient(180deg, #6384ff, #38bdf8);
        border-radius: 0 3px 3px 0;
        box-shadow: 0 0 10px rgba(99, 132, 255, 0.5);
    }

    .menu-icon {
        transition: all 0.3s ease !important;
    }

    .menu-link-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 24px;
        margin-right: 8px;
    }

    .menu-link-indicator {
        display: inline-block;
        width: 6px;
        height: 6px;
        border-radius: 50%;
        margin-left: auto;
        opacity: 0;
        transform: scale(0);
        transition: all 0.3s ease;
    }

    .menu-item.active .menu-link-indicator {
        opacity: 1;
        transform: scale(1);
        background: linear-gradient(135deg, #6384ff, #38bdf8);
        box-shadow: 0 0 8px rgba(99, 132, 255, 0.6);
    }

    .menu-divider {
        border-top: 1px solid rgba(99, 132, 255, 0.1) !important;
        margin: 8px 16px !important;
    }

    .sidebar-footer {
        position: relative;
        z-index: 1;
    }

    .footer-card {
        background: rgba(255, 255, 255, 0.06) !important;
        backdrop-filter: blur(12px) !important;
        border: 1px solid rgba(255, 255, 255, 0.08) !important;
        border-radius: 12px !important;
        position: relative;
        overflow: hidden;
    }

    .footer-icon-wrapper {
        position: relative;
        display: inline-block;
    }

    .footer-icon {
        position: relative;
        z-index: 1;
        animation: footerIconBob 3s ease-in-out infinite;
    }

    @keyframes footerIconBob {

        0%,
        100% {
            transform: translateY(0);
        }

        50% {
            transform: translateY(-3px);
        }
    }

    .footer-icon-pulse {
        position: absolute;
        top: 50%;
        left: 50%;
        width: 44px;
        height: 44px;
        margin: -22px 0 0 -22px;
        background: rgba(99, 132, 255, 0.15);
        border-radius: 50%;
        animation: footerPulse 20s ease-out infinite;
    }

    @keyframes footerPulse {
        0% {
            opacity: 0;
            transform: scale(0.5);
        }

        3% {
            opacity: 0.4;
            transform: scale(1);
        }

        12% {
            opacity: 0;
            transform: scale(1.8);
        }

        100% {
            opacity: 0;
            transform: scale(1.8);
        }
    }

    .btn-footer-custom {
        background: rgba(255, 255, 255, 0.08) !important;
        border: 1px solid rgba(255, 255, 255, 0.12) !important;
        border-radius: 8px !important;
        font-size: 0.75rem !important;
        font-weight: 500 !important;
        padding: 6px 12px !important;
        transition: all 0.3s ease !important;
        position: relative;
        z-index: 1;
    }

    .btn-footer-custom:hover {
        border-color: rgba(99, 132, 255, 0.4) !important;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(99, 132, 255, 0.2);
    }

    .menu-item {
        opacity: 0;
        transform: translateX(-10px);
        animation: slideInMenu 0.4s ease forwards;
    }

    .menu-item:nth-child(1) {
        animation-delay: 0.05s;
    }

    .menu-item:nth-child(2) {
        animation-delay: 0.10s;
    }

    .menu-item:nth-child(3) {
        animation-delay: 0.15s;
    }

    .menu-item:nth-child(4) {
        animation-delay: 0.20s;
    }

    .menu-item:nth-child(5) {
        animation-delay: 0.25s;
    }

    .menu-item:nth-child(6) {
        animation-delay: 0.30s;
    }

    .menu-item:nth-child(7) {
        animation-delay: 0.35s;
    }

    .menu-item:nth-child(8) {
        animation-delay: 0.40s;
    }

    .menu-item:nth-child(9) {
        animation-delay: 0.45s;
    }

    .menu-item:nth-child(10) {
        animation-delay: 0.50s;
    }

    @keyframes slideInMenu {
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    .menu-collapsed .brand-icon-wrapper {
        width: 28px;
        height: 28px;
    }

    .menu-collapsed .brand-icon-animated {
        display: none;
    }
</style>
<script>
    (function() {
        'use strict';

        const wrapper = document.querySelector('.brand-icon-wrapper');
        if (!wrapper) return;

        const icons = Array.from(wrapper.querySelectorAll('i'));
        let currentIndex = 0;
        const SHOW_DURATION = 4500; // Durasi tampil per icon (ms)

        // Setup awal: hanya icon pertama yang terlihat
        icons.forEach((icon, i) => {
            icon.style.opacity = i === 0 ? '1' : '0';
        });

        // Fungsi membuat partikel percikan
        function spawnParticles() {
            const count = 8 + Math.floor(Math.random() * 4); // 8-11 partikel per bounce
            for (let i = 0; i < count; i++) {
                const p = document.createElement('span');
                p.className = 'icon-particle';

                // Arah acak melingkar
                const angle = (i / count) * Math.PI * 2 + (Math.random() * 0.5);
                const dist = 18 + Math.random() * 12;
                p.style.setProperty('--tx', `${Math.cos(angle) * dist}px`);
                p.style.setProperty('--ty', `${Math.sin(angle) * dist}px`);

                wrapper.appendChild(p);
                requestAnimationFrame(() => p.style.animation = 'particleBurst 0.55s ease-out forwards');
                setTimeout(() => p.remove(), 600);
            }
        }

        // Fungsi trigger bounce + percikan
        function triggerBounce() {
            const currentIcon = icons[currentIndex];
            if (!currentIcon) return;

            // Bounce 1: Naik & miring sedikit
            currentIcon.style.transition = 'transform 0.22s cubic-bezier(0.34, 1.56, 0.64, 1)';
            currentIcon.style.transform = 'translate(-50%, -50%) scale(1.35) rotate(-8deg)';
            spawnParticles();

            setTimeout(() => {
                // Bounce 2: Turun & miring sebaliknya
                currentIcon.style.transition = 'transform 0.22s cubic-bezier(0.34, 1.56, 0.64, 1)';
                currentIcon.style.transform = 'translate(-50%, -50%) scale(0.85) rotate(5deg)';
                spawnParticles();

                setTimeout(() => {
                    // Kembali normal
                    currentIcon.style.transition = 'transform 0.25s ease';
                    currentIcon.style.transform = 'translate(-50%, -50%) scale(1) rotate(0deg)';
                }, 220);
            }, 240);
        }

        // Fungsi ganti icon
        function switchIcon() {
            const currentIcon = icons[currentIndex];
            currentIcon.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
            currentIcon.style.opacity = '0';
            currentIcon.style.transform = 'translate(-50%, -50%) scale(0.5) rotate(10deg)';

            currentIndex = (currentIndex + 1) % icons.length;
            const nextIcon = icons[currentIndex];

            // Reset next icon sebelum fade-in
            nextIcon.style.transition = 'none';
            nextIcon.style.opacity = '0';
            nextIcon.style.transform = 'translate(-50%, -50%) scale(0.5) rotate(-10deg)';

            requestAnimationFrame(() => {
                nextIcon.style.transition =
                    'opacity 0.4s ease, transform 0.4s cubic-bezier(0.34, 1.56, 0.64, 1)';
                nextIcon.style.opacity = '1';
                nextIcon.style.transform = 'translate(-50%, -50%) scale(1) rotate(0deg)';
            });
        }

        // Loop utama
        function startCycle() {
            // 55% sesi: Bounce 1
            setTimeout(() => triggerBounce(), SHOW_DURATION * 0.55);
            // 75% sesi: Bounce 2
            setTimeout(() => triggerBounce(), SHOW_DURATION * 0.75);
            // 92% sesi: Ganti icon
            setTimeout(() => {
                switchIcon();
                setTimeout(startCycle, 400); // Buffer kecil sebelum siklus baru
            }, SHOW_DURATION * 0.92);
        }

        // Mulai setelah halaman stabil
        setTimeout(startCycle, 1000);

        // === HOVER EFFECTS (Tetap dipertahankan) ===
        wrapper.addEventListener('mouseenter', () => {
            const currentIcon = icons[currentIndex];
            if (currentIcon) {
                currentIcon.style.transition = 'transform 0.4s cubic-bezier(0.34, 1.56, 0.64, 1)';
                currentIcon.style.transform = 'translate(-50%, -50%) scale(1.2) rotate(15deg)';
            }
        });
        wrapper.addEventListener('mouseleave', () => {
            const currentIcon = icons[currentIndex];
            if (currentIcon) {
                currentIcon.style.transition = 'transform 0.4s ease';
                currentIcon.style.transform = 'translate(-50%, -50%) scale(1) rotate(0deg)';
            }
        });

        document.querySelectorAll('.menu-link').forEach(link => {
            link.addEventListener('mouseenter', function() {
                const icon = this.querySelector('.menu-icon');
                if (icon) {
                    icon.style.transition = 'transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1)';
                    icon.style.transform = 'scale(1.2) rotate(-5deg)';
                }
            });
            link.addEventListener('mouseleave', function() {
                const icon = this.querySelector('.menu-icon');
                if (icon) {
                    icon.style.transition = 'transform 0.3s ease';
                    icon.style.transform = 'scale(1) rotate(0deg)';
                }
            });
        });
    })();
</script>
