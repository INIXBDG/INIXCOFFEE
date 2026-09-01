@extends('layouts_kpi.app')

@section('kpi_contents')
    {{-- PRELOAD CRITICAL RESOURCES --}}
    <link rel="preload" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"></noscript>

    <style>
        /* === CRITICAL CSS ONLY (inline, ~100 lines) === */
        .kpi-overview-wrapper { padding: 1.5rem; }

        .filter-bar {
            background: #fff; border-radius: 16px; padding: 1.25rem 1.5rem;
            box-shadow: 0 2px 12px rgba(0,0,0,.04); margin-bottom: 1.5rem;
        }
        .filter-bar .form-select { border-radius: 10px; border: 1px solid #e2e8f0; }
        .filter-bar .form-select:focus { border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99,102,241,.1); }
        .filter-bar .btn { border-radius: 10px; font-weight: 600; }
        .filter-bar .btn-primary {
            background: linear-gradient(135deg, #6366f1, #8b5cf6); border: 0;
            box-shadow: 0 4px 12px rgba(99,102,241,.25);
        }
        .filter-bar .btn-primary:hover { transform: translateY(-1px); box-shadow: 0 6px 16px rgba(99,102,241,.35); }

        .stat-card {
            background: #fff; border-radius: 16px; box-shadow: 0 2px 12px rgba(0,0,0,.04);
            border: 0; transition: transform .25s ease, box-shadow .25s ease;
            contain: layout style paint; /* CSS Containment - isolasi repaint */
        }
        .stat-card:hover { transform: translateY(-3px); box-shadow: 0 8px 24px rgba(0,0,0,.08); }
        .stat-card small {
            font-size: .78rem; font-weight: 500; color: #64748b;
            text-transform: uppercase; letter-spacing: .5px;
        }
        .stat-card h4 { font-size: 1.5rem; font-weight: 700; color: #1e293b; margin-top: .25rem; min-height: 2.25rem; }
        .stat-icon {
            width: 48px; height: 48px; border-radius: 12px;
            display: flex; align-items: center; justify-content: center; font-size: 1.2rem;
        }

        .content-card {
            background: #fff; border-radius: 16px; border: 0;
            box-shadow: 0 2px 12px rgba(0,0,0,.04);
        }
        .content-card .card-title {
            font-size: 1rem; font-weight: 700; color: #1e293b;
            display: flex; align-items: center; gap: .5rem;
        }
        .content-card .card-title i { color: #6366f1; }

        /* Skeleton matching exact size of real content → LCP can reserve space */
        .skeleton {
            background: linear-gradient(90deg, #f1f5f9 25%, #e2e8f0 50%, #f1f5f9 75%);
            background-size: 200% 100%;
            animation: skeleton-loading 1.5s ease-in-out infinite;
            border-radius: 4px;
        }
        @keyframes skeleton-loading {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }
        .skeleton-text { height: 12px; width: 60%; margin-bottom: 6px; }
        .skeleton-title { height: 28px; width: 80%; }
        .skeleton-card {
            height: 110px; background: linear-gradient(90deg, #f1f5f9 25%, #e2e8f0 50%, #f1f5f9 75%);
            background-size: 200% 100%; animation: skeleton-loading 1.5s ease-in-out infinite;
            border-radius: 16px;
        }

        .emp-grid {
            display: grid; grid-template-columns: repeat(3, 1fr); gap: 0.75rem;
            position: relative; z-index: 1;
            content-visibility: auto; /* Skip render off-screen cards */
            contain-intrinsic-size: 0 80px;
        }
        .emp-card {
            background: #fff; border: 1px solid #e2e8f0; border-left: 4px solid #e2e8f0;
            border-radius: 8px; padding: 0.75rem 1rem;
            display: flex; align-items: center; gap: 0.75rem;
            cursor: pointer; transition: all 0.2s ease;
            position: relative; overflow: visible; z-index: 1;
        }
        .emp-card:hover {
            background: #f8fafc; border-color: #cbd5e1;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1); z-index: 10;
        }
        .emp-card.status-top { border-left-color: #10b981; }
        .emp-card.status-process { border-left-color: #f59e0b; }
        .emp-card.status-low { border-left-color: #ef4444; }
        .emp-card.status-new { border-left-color: #6366f1; }

        .emp-avatar {
            width: 40px; height: 40px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 1rem; font-weight: 600; flex-shrink: 0;
            color: #fff; background: #94a3b8;
        }
        .emp-info { flex: 1; min-width: 0; }
        .emp-name {
            font-weight: 600; font-size: 0.85rem; color: #1e293b;
            margin-bottom: 0.15rem; white-space: nowrap;
            overflow: hidden; text-overflow: ellipsis;
        }
        .emp-jabatan {
            font-size: 0.75rem; color: #64748b; margin-bottom: 0.25rem;
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }
        .emp-progress-wrapper { display: flex; align-items: center; gap: 0.5rem; }
        .emp-progress-val {
            font-size: 1.1rem; font-weight: 700; min-width: 45px;
            text-align: right; color: #475569;
        }
        .emp-progress-val.col-green, .emp-progress-val.col-amber,
        .emp-progress-val.col-red, .emp-progress-val.col-indigo { color: #475569; }
        .emp-badge .badge {
            font-size: 0.7rem; padding: 0.25rem 0.5rem; font-weight: 500;
            background: #f1f5f9; color: #fefefe; border: 1px solid #e2e8f0;
        }
        .emp-card.status-new .emp-badge .badge {
            background: rgba(99,102,241,0.1); color: #4f46e5;
            border-color: rgba(99,102,241,0.2);
        }
        .emp-actions { flex-shrink: 0; margin-left: 0.25rem; position: relative; z-index: 10; }
        .emp-export-menu {
            position: absolute; right: 0; top: calc(100% + 4px);
            background: #fff; border: 1px solid #e2e8f0; border-radius: 8px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.12); min-width: 130px;
            z-index: 9999; padding: 4px 0;
        }
        .emp-export-menu .dropdown-item { font-size: 0.82rem; padding: 0.4rem 0.85rem; }
        .emp-export-menu .dropdown-item:hover { background: #f8fafc; }
        .emp-actions .dropdown-toggle {
            padding: 0.25rem 0.5rem; font-size: 0.85rem;
            border-color: #e2e8f0; color: #64748b; background: #fff;
        }
        .emp-actions .dropdown-toggle:hover { background: #f8fafc; border-color: #cbd5e1; }

        @media (max-width: 1200px) { .emp-grid { grid-template-columns: repeat(3, 1fr); } }
        @media (max-width: 992px) { .emp-grid { grid-template-columns: repeat(2, 1fr); } }
        @media (max-width: 576px) {
            .emp-grid { grid-template-columns: 1fr; }
            .emp-card { padding: 1rem; }
        }

        .low-perf-item {
            background: rgba(239,68,68,.05); border: 1px solid rgba(239,68,68,.15);
            border-left: 4px solid #ef4444; border-radius: 10px;
            padding: .85rem 1rem; transition: background .2s ease;
        }
        .low-perf-item:hover { background: rgba(239,68,68,.1); }
        .low-perf-item .lp-name { font-weight: 700; font-size: .88rem; color: #1e293b; }
        .low-perf-item .lp-jabatan { font-size: .78rem; color: #64748b; }
        .low-perf-item .lp-val { font-weight: 800; font-size: 1.1rem; color: #ef4444; }

        .modern-table thead th {
            background: #f8fafc !important; border-bottom: 1px solid #e2e8f0 !important;
            font-weight: 600; color: #475569; font-size: .78rem;
            text-transform: uppercase; letter-spacing: .5px;
        }
        .modern-table tbody td { font-size: .88rem; color: #334155; vertical-align: middle; }
        .modern-table tbody tr:hover { background: #f8fafc; }
        .modern-table tbody tr:last-child td { border-bottom: 0; }

        .loading-state, .empty-state {
            display: flex; flex-direction: column; align-items: center;
            justify-content: center; padding: 3rem 1rem;
            color: #94a3b8; text-align: center;
        }
        .loading-state i, .empty-state i { font-size: 2.5rem; margin-bottom: .75rem; opacity: .5; }
        .loading-state p, .empty-state p { margin: 0; font-size: .88rem; }
    </style>

    <div class="container flex-grow-1 mt-4">

        {{-- Filter Bar --}}
        <div class="filter-bar">
            <form action="{{ route('kpi.overview.get') }}" method="get" id="FormFilter">
                <input type="hidden" name="id_karyawan" value="{{ $user_id ?? auth()->id() }}" id="inputIdKaryawan">
                <div class="row align-items-center g-3">
                    <div class="col-md-3">
                        <h5 class="fw-bold mb-0 text-dark" id="overviewTitle">
                            @if(isset($user_id) && $user_id == auth()->id())
                                Overview Personal: {{ auth()->user()->karyawan->nama_lengkap ?? 'Anda' }}
                                <span class="text-muted fw-normal">| {{ $divisi }}</span>
                            @else
                                Overview Divisi {{ $divisi ?? 'Pilih Departemen' }}
                            @endif
                            {{ request('tahun', now()->year) }}
                        </h5>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" name="divisi" id="selectDivisi">
                            <option disabled>Pilih Departement</option>
                            @foreach ($departments as $data)
                                <option value="{{ $data }}" {{ $divisi === $data ? 'selected' : '' }}>
                                    {{ $data }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select class="form-select" name="tahun" id="selectTahun">
                            <option value="">Periode Tahun</option>
                            @for ($year = 2025; $year <= now()->year; $year++)
                                <option value="{{ $year }}"
                                    {{ request('tahun', now()->year) == $year ? 'selected' : '' }}>{{ $year }}
                                </option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-md-4 d-flex gap-2">
                        <button type="submit" class="btn btn-primary flex-grow-1">
                            <i class="fa-solid fa-filter me-1"></i> Filter
                        </button>
                        <div class="dropdown">
                            <button class="btn btn-outline-secondary dropdown-toggle" type="button"
                                data-bs-toggle="dropdown">
                                <i class="fa-solid fa-file-export me-1"></i> Export
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                <li><a href="#" class="dropdown-item btn-export-dept" data-type="excel"><i class="fa-solid fa-file-excel text-success me-2"></i> Excel</a></li>
                                <li><a href="#" class="dropdown-item btn-export-dept" data-type="pdf"><i class="fa-solid fa-file-pdf text-danger me-2"></i> PDF</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        {{-- Stat Cards with FIXED SIZE - Critical for LCP reservation --}}
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="stat-card h-100" fetchpriority="high">
                    <div class="card-body d-flex justify-content-between align-items-center p-3">
                        <div style="min-width: 0; flex: 1;">
                            <small>Total Target</small>
                            <h4 class="mb-0" id="totalTarget" aria-busy="true">
                                <span class="skeleton skeleton-title">&nbsp;</span>
                            </h4>
                        </div>
                        <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                            <i class="fa-solid fa-star" aria-hidden="true"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card h-100">
                    <div class="card-body d-flex justify-content-between align-items-center p-3">
                        <div style="min-width: 0; flex: 1;">
                            <small>Rata-Rata Progress</small>
                            <h4 class="mb-0" id="rataProgress" aria-busy="true">
                                <span class="skeleton skeleton-title">&nbsp;</span>
                            </h4>
                        </div>
                        <div class="stat-icon bg-success bg-opacity-10 text-success">
                            <i class="fa-solid fa-chart-line" aria-hidden="true"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card h-100">
                    <div class="card-body d-flex justify-content-between align-items-center p-3">
                        <div style="min-width: 0; flex: 1;">
                            <small>KPI Sedang Berjalan</small>
                            <h4 class="mb-0" id="kpiAktif" aria-busy="true">
                                <span class="skeleton skeleton-title">&nbsp;</span>
                            </h4>
                        </div>
                        <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                            <i class="fa-solid fa-clock" aria-hidden="true"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card h-100">
                    <div class="card-body d-flex justify-content-between align-items-center p-3">
                        <div style="min-width: 0; flex: 1;">
                            <small>KPI Selesai</small>
                            <h4 class="mb-0" id="kpiSelesai" aria-busy="true">
                                <span class="skeleton skeleton-title">&nbsp;</span>
                            </h4>
                        </div>
                        <div class="stat-icon bg-info bg-opacity-10 text-info">
                            <i class="fa-solid fa-check" aria-hidden="true"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Employee Grid - with size-matched skeleton --}}
        <div class="content-card mb-4">
            <div class="card-body p-4">
                <h6 class="card-title mb-1" id="employeeTitle">
                    <i class="fa-solid fa-users" aria-hidden="true"></i> Karyawan di Departemen
                </h6>
                <p class="text-muted small mb-3">Klik kartu untuk melihat detail KPI karyawan</p>
                <div id="employeeList">
                    <div class="emp-grid" aria-busy="true" aria-label="Memuat data karyawan">
                        @for ($i = 0; $i < 6; $i++)
                            <div class="emp-card" style="min-height: 72px; cursor: default;">
                                <div class="skeleton" style="width: 40px; height: 40px; border-radius: 50%;"></div>
                                <div class="emp-info">
                                    <div class="skeleton skeleton-text" style="width: 70%;"></div>
                                    <div class="skeleton skeleton-text" style="width: 50%;"></div>
                                </div>
                            </div>
                        @endfor
                    </div>
                </div>
            </div>
        </div>

        {{-- Low Performance --}}
        <div class="content-card mb-4">
            <div class="card-body p-4">
                <h6 class="card-title mb-1">
                    <i class="fa-solid fa-triangle-exclamation text-danger" aria-hidden="true"></i> Perlu Perhatian
                </h6>
                <p class="text-muted small mb-3">Karyawan dengan progress KPI di bawah 50%</p>
                <div id="lowPerformanceList">
                    <div class="loading-state">
                        <i class="fa-solid fa-spinner fa-spin"></i>
                        <p>Memuat data...</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Charts - Lazy rendered, no blocking --}}
        <div class="row g-3 mb-4">
            <div class="col-lg-8">
                <div class="content-card h-100">
                    <div class="card-body p-4">
                        <h6 class="card-title mb-3">
                            <i class="fa-solid fa-chart-bar" aria-hidden="true"></i> Statistik Karyawan
                        </h6>
                        <div style="position:relative; height:380px;">
                            <canvas id="kpiChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="content-card h-100">
                    <div class="card-body p-4">
                        <h6 class="card-title mb-3">
                            <i class="fa-solid fa-chart-pie" aria-hidden="true"></i> Distribusi Nilai
                        </h6>
                        <div style="position:relative; height:300px;">
                            <canvas id="kpiPieChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Target Table --}}
        <div class="content-card">
            <div class="card-body p-4">
                <h6 class="card-title mb-3">
                    <i class="fa-solid fa-list-check" aria-hidden="true"></i> Daftar Target KPI
                </h6>
                <div class="table-responsive">
                    <table class="table modern-table align-middle mb-0" id="targetTable">
                        <thead>
                            <tr>
                                <th>Judul</th>
                                <th>Periode</th>
                                <th>Target</th>
                                <th>Progress</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="5">
                                    <div class="loading-state">
                                        <i class="fa-solid fa-spinner fa-spin"></i>
                                        <p>Memuat data target...</p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    {{-- Export Filter Modal --}}
    <div class="modal fade" id="exportFilterModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Filter Export Data</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Tahun</label>
                        <select class="form-select" id="filterTahun">
                            <option value="">Semua Tahun</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Periode</label>
                        <select class="form-select" id="filterPeriode">
                            <option value="all">Semua Periode</option>
                            <option value="tahunan">Tahunan</option>
                            <option value="kuartalan">Kuartalan</option>
                            <option value="bulanan">Bulanan</option>
                        </select>
                    </div>
                    <div class="mb-3 d-none" id="filterQuarterWrap">
                        <label class="form-label fw-semibold">Kuartal</label>
                        <select class="form-select" id="filterQuarter">
                            <option value="1">Q1 (Jan – Mar)</option>
                            <option value="2">Q2 (Apr – Jun)</option>
                            <option value="3">Q3 (Jul – Sep)</option>
                            <option value="4">Q4 (Okt – Des)</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" id="btnApplyExport">Terapkan &amp; Export</button>
                </div>
            </div>
        </div>
    </div>

    {{-- JQUERY DIHAPUS (tidak digunakan di kode ini) --}}
    {{-- Chart.js & SweetAlert2 DI-DEFER, hanya dimuat saat dibutuhkan --}}
    <script>
    (function() {
        'use strict';

        // === LAZY LOAD LIBRARIES (hanya dimuat saat dibutuhkan) ===
        function loadScript(src) {
            return new Promise((resolve, reject) => {
                const s = document.createElement('script');
                s.src = src;
                s.async = true;
                s.onload = resolve;
                s.onerror = reject;
                document.head.appendChild(s);
            });
        }

        let chartJsLoaded = false;
        let sweetalertLoaded = false;
        let chartJsPromise = null;
        let sweetalertPromise = null;

        function ensureChartJs() {
            if (chartJsLoaded) return Promise.resolve();
            if (!chartJsPromise) {
                chartJsPromise = loadScript('https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js')
                    .then(() => { chartJsLoaded = true; });
            }
            return chartJsPromise;
        }

        function ensureSweetAlert() {
            if (sweetalertLoaded) return Promise.resolve();
            if (!sweetalertPromise) {
                sweetalertPromise = loadScript('https://cdn.jsdelivr.net/npm/sweetalert2@11')
                    .then(() => { sweetalertLoaded = true; });
            }
            return sweetalertPromise;
        }

        // Load Chart.js di IDLE time (tidak blokir LCP paint)
        if ('requestIdleCallback' in window) {
            requestIdleCallback(() => ensureChartJs(), { timeout: 2000 });
        } else {
            setTimeout(() => ensureChartJs(), 1500);
        }

        let kpiChart = null;
        let kpiPieChart = null;
        let exportCtx = {};
        let exportModal = null;

        function getExportModal() {
            if (!exportModal) {
                exportModal = new bootstrap.Modal(document.getElementById('exportFilterModal'));
            }
            return exportModal;
        }

        function initYearOptions() {
            const sel = document.getElementById('filterTahun');
            const cur = new Date().getFullYear();
            sel.innerHTML = '<option value="">Semua Tahun</option>';
            for (let y = cur; y >= cur - 4; y--) {
                sel.insertAdjacentHTML('beforeend', `<option value="${y}">${y}</option>`);
            }
        }

        function getInitials(nama) {
            return (nama || '?').split(' ').slice(0, 2).map(w => w[0]).join('').toUpperCase();
        }

        function classifyEmployee(progress, currentMonth) {
            const isYearEnd = currentMonth === 12;
            if (isYearEnd) {
                if (progress >= 75) return { cls: 'status-top', av: 'av-green', val: 'col-green', badge: '<span class="badge bg-success">Top</span>' };
                if (progress >= 50) return { cls: 'status-process', av: 'av-amber', val: 'col-amber', badge: '<span class="badge bg-warning text-dark">Cukup</span>' };
                return { cls: 'status-low', av: 'av-red', val: 'col-red', badge: '<span class="badge bg-danger">Kurang</span>' };
            }
            if (progress === 0) {
                if (currentMonth <= 3) return { cls: 'status-new', av: 'av-indigo', val: 'col-indigo', badge: '<span class="badge bg-primary">Baru</span>' };
                return { cls: 'status-low', av: 'av-red', val: 'col-red', badge: '<span class="badge bg-danger">Perlu Bimbingan</span>' };
            }
            if (progress >= 75) return { cls: 'status-top', av: 'av-green', val: 'col-green', badge: '<span class="badge bg-success">On Track</span>' };
            return { cls: 'status-process', av: 'av-amber', val: 'col-amber', badge: '<span class="badge bg-warning text-dark">Dalam Proses</span>' };
        }

        function loadData() {
            const divisi = document.getElementById('selectDivisi').value || '';
            const tahun = document.getElementById('selectTahun').value || {{ now()->year }};
            const idKaryawan = document.getElementById('inputIdKaryawan').value || {{ auth()->id() }};

            const namaUser = "{{ auth()->user()->karyawan->nama_lengkap ?? 'Anda' }}";
            if (divisi) {
                document.getElementById('overviewTitle').innerHTML =
                    `Overview Personal: ${namaUser} <span class="text-muted fw-normal">| ${divisi}</span> ${tahun}`;
                document.getElementById('employeeTitle').innerHTML =
                    `<i class="fa-solid fa-user" aria-hidden="true"></i> Data KPI Anda`;
            } else {
                document.getElementById('overviewTitle').textContent = `Overview Divisi ${tahun}`;
                document.getElementById('employeeTitle').innerHTML = `<i class="fa-solid fa-users" aria-hidden="true"></i> Karyawan di Departemen`;
            }

            const formData = new FormData(document.getElementById('FormFilter'));
            if (!formData.has('id_karyawan') && idKaryawan) {
                formData.append('id_karyawan', idKaryawan);
            }
            const params = new URLSearchParams(formData);

            fetch(`{{ route('kpi.overview.get') }}?${params}`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(r => {
                    if (!r.ok) throw new Error(`HTTP ${r.status}`);
                    return r.json();
                })
                .then(data => {
                    updateStats(data);
                    updateEmployeeGrid(data.karyawan_departemen);
                    updateLowPerf(data.karyawan_departemen);
                    updateCharts(data.statistik_karyawan, data.distribusi_nilai);
                    updateTargetTable(data.daftar_target_kpi);
                })
                .catch(err => {
                    console.error('Load error:', err);
                    ensureSweetAlert().then(() => {
                        Swal.fire({ icon: 'error', title: 'Gagal', text: 'Tidak dapat memuat data. Coba lagi.' });
                    });
                });
        }

        function updateStats(data) {
            const totalEl = document.getElementById('totalTarget');
            const rataEl = document.getElementById('rataProgress');
            const aktifEl = document.getElementById('kpiAktif');
            const selesaiEl = document.getElementById('kpiSelesai');

            totalEl.textContent = (data.total_target || 0) + ' Target';
            rataEl.textContent = (data.rata_rata_progress || 0).toFixed(1) + '%';
            aktifEl.textContent = data.kpi_aktif || 0;
            selesaiEl.textContent = data.kpi_selesai || 0;

            [totalEl, rataEl, aktifEl, selesaiEl].forEach(el => el.removeAttribute('aria-busy'));
        }

        function updateEmployeeGrid(employees) {
            const el = document.getElementById('employeeList');
            if (!employees || !employees.length) {
                el.innerHTML =
                    `<div class="empty-state"><i class="fa-solid fa-users-slash" aria-hidden="true"></i><p>Tidak ada data karyawan</p></div>`;
                return;
            }
            const month = new Date().getMonth() + 1;
            const year = new Date().getFullYear();
            let html = '<div class="emp-grid">';

            employees.forEach(emp => {
                const progressNumber = parseFloat(emp.rata_rata_progress ?? 0);
                const progressDisplay = progressNumber.toFixed(1);

                const info = classifyEmployee(progressNumber, month);
                const initial = getInitials(emp.nama);

                html += `
                    <div class="emp-card ${info.cls}" data-id="${emp.id_karyawan}">
                        <div class="emp-avatar ${info.av}">${initial}</div>
                        <div class="emp-info">
                            <div class="emp-name">${emp.nama}</div>
                            <div class="emp-jabatan">${emp.jabatan}</div>
                            <div class="emp-progress-wrapper">
                                <div class="emp-progress-val ${info.val}">${progressDisplay}%</div>
                                <div class="emp-badge">${info.badge}</div>
                            </div>
                        </div>
                        <div class="emp-actions">
                            <button class="btn btn-sm btn-outline-primary emp-export-btn"
                                type="button"
                                data-id="${emp.id_karyawan}"
                                data-tahun="${year}">
                                <i class="fas fa-download" aria-hidden="true"></i>
                            </button>
                            <div class="emp-export-menu" style="display:none;">
                                <a class="dropdown-item btn-export-emp" href="#"
                                    data-type="excel" data-id="${emp.id_karyawan}" data-tahun="${year}">
                                    <i class="fas fa-file-excel text-success me-1" aria-hidden="true"></i> Excel
                                </a>
                                <a class="dropdown-item btn-export-emp" href="#"
                                    data-type="pdf" data-id="${emp.id_karyawan}" data-tahun="${year}">
                                    <i class="fas fa-file-pdf text-danger me-1" aria-hidden="true"></i> PDF
                                </a>
                            </div>
                        </div>
                    </div>`;
            });

            html += '</div>';
            el.innerHTML = html;
        }

        function updateLowPerf(employees) {
            const el = document.getElementById('lowPerformanceList');
            const low = (employees || []).filter(e => (e.rata_rata_progress || 0) < 50);
            if (!low.length) {
                el.innerHTML =
                    `<div class="empty-state"><i class="fa-solid fa-circle-check text-success" aria-hidden="true"></i><p>Semua karyawan dalam performa baik</p></div>`;
                return;
            }
            el.innerHTML = low.map(emp => `
                <div class="low-perf-item d-flex justify-content-between align-items-center mb-2">
                    <div>
                        <div class="lp-name">${emp.nama}</div>
                        <div class="lp-jabatan">${emp.jabatan}</div>
                    </div>
                    <div class="lp-val">${(emp.rata_rata_progress || 0).toFixed(1)}%</div>
                </div>`).join('');
        }

        function updateCharts(empStats, distribution) {
            // Pastikan Chart.js sudah dimuat sebelum render
            ensureChartJs().then(() => {
                if (kpiChart) { kpiChart.destroy(); kpiChart = null; }
                if (kpiPieChart) { kpiPieChart.destroy(); kpiPieChart = null; }

                const names = (empStats || []).map(i => i.nama);
                const progress = (empStats || []).map(i => (i.rata_rata_progress || 0).toFixed(1));
                const targets = (empStats || []).map(i => i.total_target || 0);

                const barColors = progress.map(p => p >= 75 ? 'rgba(16,185,129,.65)' : p >= 50 ?
                    'rgba(245,158,11,.65)' : 'rgba(239,68,68,.65)');
                const borderColors = progress.map(p => p >= 75 ? '#10b981' : p >= 50 ? '#f59e0b' : '#ef4444');

                kpiChart = new Chart(document.getElementById('kpiChart'), {
                    type: 'bar',
                    data: {
                        labels: names,
                        datasets: [{
                                label: 'Progress (%)',
                                data: progress,
                                backgroundColor: barColors,
                                borderColor: borderColors,
                                borderWidth: 2,
                                borderRadius: 8,
                                yAxisID: 'y'
                            },
                            {
                                label: 'Total Target',
                                data: targets,
                                type: 'line',
                                borderColor: '#6366f1',
                                backgroundColor: 'transparent',
                                borderWidth: 3,
                                pointBackgroundColor: '#6366f1',
                                pointRadius: 5,
                                fill: false,
                                yAxisID: 'y1',
                                tension: 0.4
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { position: 'top', labels: { usePointStyle: true, padding: 15 } },
                            tooltip: {
                                backgroundColor: 'rgba(15,23,42,.9)',
                                padding: 12,
                                cornerRadius: 8,
                                callbacks: {
                                    label: ctx => ctx.dataset.label === 'Progress (%)' ?
                                        `Progress: ${ctx.parsed.y}%` : `Total Target: ${ctx.parsed.y}`
                                }
                            }
                        },
                        scales: {
                            y: {
                                type: 'linear', position: 'left', min: 0, max: 100,
                                ticks: { callback: v => v + '%' },
                                grid: { drawOnChartArea: false },
                                title: { display: true, text: 'Progress (%)', font: { weight: '600' } }
                            },
                            y1: {
                                type: 'linear', position: 'right', min: 0,
                                grid: { drawOnChartArea: false },
                                title: { display: true, text: 'Total Target', font: { weight: '600' } }
                            },
                            x: {
                                grid: { display: false },
                                title: { display: true, text: 'Karyawan', font: { weight: '600' } }
                            }
                        }
                    }
                });

                const pieLabels = Object.keys(distribution || {});
                const pieData = Object.values(distribution || {});

                kpiPieChart = new Chart(document.getElementById('kpiPieChart'), {
                    type: 'doughnut',
                    data: {
                        labels: pieLabels,
                        datasets: [{
                            data: pieData,
                            backgroundColor: ['#10b981', '#f59e0b', '#ef4444', '#6366f1'],
                            borderWidth: 0,
                            hoverOffset: 8
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '65%',
                        plugins: {
                            legend: { position: 'bottom', labels: { usePointStyle: true, padding: 15 } },
                            tooltip: {
                                backgroundColor: 'rgba(15,23,42,.9)',
                                padding: 12,
                                cornerRadius: 8,
                                callbacks: {
                                    label: ctx => {
                                        const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
                                        const pct = total ? Math.round((ctx.parsed / total) * 100) : 0;
                                        return `${ctx.label}: ${ctx.parsed} (${pct}%)`;
                                    }
                                }
                            }
                        }
                    }
                });
            });
        }

        function updateTargetTable(targets) {
            const tbody = document.querySelector('#targetTable tbody');
            if (!targets || !targets.length) {
                tbody.innerHTML =
                    `<tr><td colspan="5"><div class="empty-state"><i class="fa-solid fa-inbox" aria-hidden="true"></i><p>Tidak ada data target</p></div></td></tr>`;
                return;
            }
            tbody.innerHTML = targets.map(t => {
                const pct = parseFloat((t.progress ?? 0).toFixed(2));

                let barColor, badgeCls;
                switch (t.status) {
                    case 'Selesai': barColor = '#10b981'; badgeCls = 'bg-success'; break;
                    case 'Gagal': barColor = '#343a40'; badgeCls = 'bg-dark'; break;
                    case 'Sedang Berjalan': barColor = '#0d6efd'; badgeCls = 'bg-primary'; break;
                    case 'Belum Mulai': default: barColor = '#6c757d'; badgeCls = 'bg-secondary'; break;
                }
                return `
                    <tr>
                        <td><strong>${t.judul}</strong></td>
                        <td>${t.periode}</td>
                        <td>${t.target}</td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="progress flex-grow-1" style="height:8px;border-radius:4px;">
                                    <div class="progress-bar" style="width:${pct}%;background:${barColor};"></div>
                                </div>
                                <small class="fw-semibold">${pct}%</small>
                            </div>
                        </td>
                        <td><span class="badge ${badgeCls}">${t.status}</span></td>
                    </tr>`;
            }).join('');
        }

        function handleDeptExport(event, type) {
            event.preventDefault();
            const divisi = document.getElementById('selectDivisi').value;
            const tahun = document.getElementById('selectTahun').value || {{ now()->year }};
            const idKaryawan = document.getElementById('inputIdKaryawan').value || {{ auth()->id() }};

            if (!divisi) {
                ensureSweetAlert().then(() => {
                    Swal.fire({ icon: 'warning', title: 'Perhatian', text: 'Pilih Departemen terlebih dahulu.' });
                });
                return;
            }

            const baseUrl = type === 'excel'
                ? '{{ route('kpi.departement.export.excel') }}'
                : '{{ route('kpi.departement.export.pdf') }}';

            window.location.href = `${baseUrl}?divisi=${encodeURIComponent(divisi)}&tahun=${tahun}&id_karyawan=${idKaryawan}`;
        }

        function handleEmpExport(type, id, tahun) {
            exportCtx = { type, id, tahun };
            document.getElementById('filterTahun').value = tahun;
            document.getElementById('filterPeriode').value = 'all';
            document.getElementById('filterQuarterWrap').classList.add('d-none');
            getExportModal().show();
        }

        document.getElementById('filterPeriode').addEventListener('change', function() {
            document.getElementById('filterQuarterWrap').classList.toggle('d-none', this.value !== 'kuartalan');
        });

        document.getElementById('btnApplyExport').addEventListener('click', function() {
            const params = new URLSearchParams({
                id_karyawan: exportCtx.id,
                tahun: document.getElementById('filterTahun').value || exportCtx.tahun,
                periode: document.getElementById('filterPeriode').value,
                quarter: document.getElementById('filterQuarterWrap').classList.contains('d-none') ?
                    '' : document.getElementById('filterQuarter').value
            });
            const baseUrl = exportCtx.type === 'pdf' ?
                '{{ route('kpi.monitoring.export.pdf') }}' :
                '{{ route('kpi.monitoring.export.excel') }}';
            window.open(`${baseUrl}?${params}`);
            getExportModal().hide();
        });

        document.addEventListener('click', function(e) {
            if (!e.target.closest('.filter-bar .dropdown')) {
                document.querySelectorAll('.filter-bar .dropdown-menu').forEach(m => m.classList.remove('show'));
            }

            if (!e.target.closest('.emp-actions')) {
                document.querySelectorAll('.emp-export-menu').forEach(m => m.style.display = 'none');
            }

            const deptBtn = e.target.closest('.btn-export-dept');
            if (deptBtn) { e.preventDefault(); handleDeptExport(e, deptBtn.dataset.type); return; }

            const exportBtn = e.target.closest('.emp-export-btn');
            if (exportBtn) {
                e.stopPropagation();
                const menu = exportBtn.nextElementSibling;
                const isOpen = menu.style.display === 'block';
                document.querySelectorAll('.emp-export-menu').forEach(m => m.style.display = 'none');
                menu.style.display = isOpen ? 'none' : 'block';
                return;
            }

            const empExport = e.target.closest('.btn-export-emp');
            if (empExport) {
                e.preventDefault();
                e.stopPropagation();
                document.querySelectorAll('.emp-export-menu').forEach(m => m.style.display = 'none');
                handleEmpExport(empExport.dataset.type, empExport.dataset.id, empExport.dataset.tahun);
                return;
            }

            const empCard = e.target.closest('.emp-card');
            if (empCard && !e.target.closest('.emp-actions')) {
                const id = empCard.dataset.id;
                if (id) window.location.href = `/kpi-data/overview/index/personal/${id}`;
            }
        });

        initYearOptions();

        const deptToggle = document.querySelector('.filter-bar .dropdown-toggle');
        if (deptToggle) {
            deptToggle.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                const menu = this.nextElementSibling;
                document.querySelectorAll('.filter-bar .dropdown-menu').forEach(m => {
                    if (m !== menu) m.classList.remove('show');
                });
                menu.classList.toggle('show');
            });
        }

        @if (auth()->check() && auth()->user()->karyawan && auth()->user()->karyawan->divisi)
            document.getElementById('selectDivisi').value = '{{ auth()->user()->karyawan->divisi }}';
        @endif

        document.getElementById('FormFilter').addEventListener('submit', function(e) {
            e.preventDefault();
            loadData();
        });

        loadData();
    })();
    </script>
@endsection