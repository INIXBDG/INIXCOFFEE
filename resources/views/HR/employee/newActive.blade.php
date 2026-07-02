@extends('layout_HR.app')

@section('content_HR')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">

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
        }

        body {
            background: #fafbfc;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }

        .page-header { margin-bottom: 1.5rem; }
        .page-title {
            font-size: 1.6rem; font-weight: 700; color: var(--gray-900);
            margin-bottom: .15rem;
        }
        .page-sub { color: var(--gray-400); font-size: .875rem; }

        /* ===== STAT CARDS ===== */
        .stat-card {
            border: none; border-radius: var(--radius); box-shadow: var(--shadow);
            transition: transform .25s, box-shadow .25s; background: #fff;
            cursor: pointer;
        }
        .stat-card:hover {
            transform: translateY(-3px); box-shadow: var(--shadow-lg);
            border: 1px solid var(--pri-light);
        }
        .stat-icon {
            width: 52px; height: 52px; border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.4rem; color: #fff;
        }
        .stat-value {
            font-size: 1.6rem; font-weight: 700; color: var(--gray-900);
            margin: .4rem 0 .15rem; line-height: 1.1;
        }
        .stat-label {
            color: var(--gray-400); font-size: .78rem; margin: 0;
            text-transform: uppercase; letter-spacing: .5px; font-weight: 600;
        }

        /* ===== TABS ===== */
        .nav-tabs-custom { border-bottom: 2px solid var(--gray-200); }
        .nav-tabs-custom .nav-link {
            border: none; color: var(--gray-400); font-weight: 600;
            padding: .85rem 1.25rem; font-size: .875rem; transition: color .2s;
            display: flex; align-items: center; gap: .5rem;
        }
        .nav-tabs-custom .nav-link:hover { color: var(--pri); }
        .nav-tabs-custom .nav-link.active {
            color: var(--pri); border-bottom: 3px solid var(--pri); background: transparent;
        }
        .nav-tabs-custom .nav-link .tab-count {
            background: var(--gray-100); color: var(--gray-600);
            font-size: .7rem; padding: 2px 8px; border-radius: 10px;
            font-weight: 700;
        }
        .nav-tabs-custom .nav-link.active .tab-count {
            background: var(--pri-light); color: var(--pri);
        }

        /* ===== CARDS ===== */
        .card-shell {
            border: none; border-radius: var(--radius);
            box-shadow: var(--shadow); background: #fff;
        }
        .card-shell .card-body { padding: 1.5rem; }

        /* ===== BUTTONS ===== */
        .btn-pri {
            background: var(--pri); border: none; color: #fff; font-weight: 600;
            padding: .5rem 1.25rem; border-radius: 8px; transition: all .25s;
            font-size: .85rem;
        }
        .btn-pri:hover {
            background: var(--pri-dark); transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(79, 70, 229, .35); color: #fff;
        }
        .btn-outline-sec {
            background: #fff; border: 1px solid var(--gray-200); color: var(--gray-600);
            font-weight: 500; padding: .4rem 1rem; border-radius: 8px;
            transition: all .2s; font-size: .85rem;
        }
        .btn-outline-sec:hover {
            background: var(--gray-50); border-color: var(--gray-400); color: var(--gray-900);
        }

        /* ===== CHARTS ===== */
        .chart-wrap { position: relative; height: 300px; }
        .chart-title {
            font-size: .875rem; font-weight: 700; color: var(--gray-700);
            margin-bottom: 1rem; display: flex; align-items: center; gap: .5rem;
        }
        .chart-title i { color: var(--pri); }

        /* ===== FILTER BAR ===== */
        .filter-badge {
            display: inline-flex; align-items: center; gap: .4rem;
            background: var(--pri-light); color: var(--pri);
            padding: .4rem .85rem; border-radius: 20px;
            font-size: .78rem; font-weight: 600;
        }

        /* ===== TABLE ===== */
        .table-modern { border-collapse: separate; border-spacing: 0; width: 100%; }
        .table-modern thead th {
            font-size: .72rem; font-weight: 700; text-transform: uppercase;
            letter-spacing: .5px; color: var(--gray-600); background: var(--gray-50);
            border-bottom: 2px solid var(--gray-200) !important; border-top: none !important;
            padding: 0.85rem 1rem;
        }
        .table-modern tbody tr { transition: background .15s; }
        .table-modern tbody tr:hover { background: var(--pri-light) !important; }
        .table-modern tbody td {
            vertical-align: middle; font-size: .875rem;
            border-bottom: 1px solid var(--gray-100) !important; border-top: none !important;
            padding: 0.85rem 1rem; color: var(--gray-700);
        }

        /* ===== BADGES ===== */
        .status-badge {
            padding: .35rem .75rem; border-radius: 20px; font-size: .7rem;
            font-weight: 700; text-transform: uppercase; letter-spacing: .5px;
        }
        .status-active { background: var(--success-light); color: var(--success); }
        .status-resign { background: var(--gray-100); color: var(--gray-600); }

        /* ===== FORM ===== */
        .form-control, .form-select {
            border: 1px solid var(--gray-200); border-radius: 8px;
            padding: .5rem .85rem; font-size: .875rem; color: var(--gray-700);
            transition: all .2s;
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--pri); box-shadow: 0 0 0 3px rgba(79, 70, 229, .12);
            outline: none;
        }
        .form-label {
            font-weight: 600; color: var(--gray-700);
            font-size: .78rem; margin-bottom: .4rem;
            text-transform: uppercase; letter-spacing: .3px;
        }

        /* ===== PAGINATION ===== */
        .pagination-custom { display: flex; gap: 0.25rem; flex-wrap: wrap; }
        .pagination-custom button {
            padding: 0.35rem 0.75rem; font-size: 0.8rem; border-radius: 6px;
            border: 1px solid var(--gray-200); background: #fff; color: var(--gray-600);
            cursor: pointer; transition: all 0.15s; font-weight: 600;
        }
        .pagination-custom button:hover { background: var(--pri-light); color: var(--pri); border-color: var(--pri); }
        .pagination-custom button.active { background: var(--pri); color: white; border-color: var(--pri); }

        /* ===== MODAL ===== */
        .modal-content { border: none; border-radius: 12px; box-shadow: var(--shadow-lg); }
        .modal-header-custom {
            background: linear-gradient(135deg, var(--pri) 0%, var(--pri-dark) 100%);
            color: #fff; border-radius: 12px 12px 0 0; padding: 1.1rem 1.5rem;
        }
        .modal-header-custom .modal-title { font-weight: 700; font-size: 1rem; }
        .modal-header-custom .btn-close { filter: brightness(0) invert(1); }

        /* ===== RETENTION GAUGE ===== */
        .gauge-wrap {
            position: relative; width: 160px; height: 160px; margin: 0 auto 1rem;
        }
        .gauge-value {
            position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);
            text-align: center;
        }
        .gauge-value .num {
            font-size: 2rem; font-weight: 700; color: var(--gray-900); line-height: 1;
        }
        .gauge-value .unit { font-size: .9rem; color: var(--gray-400); font-weight: 600; }

        /* ===== LIST ITEMS ===== */
        .emp-list-item {
            border: 1px solid var(--gray-200); border-radius: 8px;
            padding: 1rem 1.15rem; margin-bottom: .6rem;
            background: #fff; transition: all .2s;
        }
        .emp-list-item:hover {
            border-color: var(--pri); background: var(--pri-light);
            transform: translateX(3px);
        }
        .emp-name { font-weight: 700; color: var(--gray-900); font-size: .9rem; }
        .emp-meta { color: var(--gray-400); font-size: .78rem; }

        /* ===== BREAKDOWN BAR ===== */
        .breakdown-item {
            display: flex; align-items: center; gap: 1rem;
            padding: .6rem 0; border-bottom: 1px dashed var(--gray-200);
        }
        .breakdown-item:last-child { border-bottom: none; }
        .breakdown-label {
            min-width: 140px; font-weight: 600; color: var(--gray-700);
            font-size: .85rem;
        }
        .breakdown-bar-wrap {
            flex: 1; height: 8px; background: var(--gray-100);
            border-radius: 4px; overflow: hidden;
        }
        .breakdown-bar {
            height: 100%; background: linear-gradient(90deg, var(--pri), #7c3aed);
            border-radius: 4px; transition: width .6s ease;
        }
        .breakdown-value {
            min-width: 50px; text-align: right; font-weight: 700;
            color: var(--pri); font-size: .85rem;
        }

        /* ===== INSIGHT CARDS ===== */
        .insight-card {
            background: var(--gray-50); border: 1px solid var(--gray-200);
            border-radius: 8px; padding: .85rem 1rem; margin-bottom: .5rem;
            font-size: .85rem; color: var(--gray-700);
            display: flex; align-items: flex-start; gap: .6rem;
        }
        .insight-card i { color: var(--success); margin-top: 2px; }
        .insight-card.rec i { color: var(--pri); }

        /* ===== SUMMARY MINI ===== */
        .summary-mini {
            background: var(--gray-50); border: 1px solid var(--gray-200);
            border-radius: 8px; padding: 1rem; text-align: center;
        }
        .summary-mini .value {
            font-size: 1.4rem; font-weight: 700; line-height: 1.1;
        }
        .summary-mini .label {
            font-size: .72rem; color: var(--gray-400); text-transform: uppercase;
            letter-spacing: .5px; font-weight: 600; margin-top: .3rem;
        }

        /* ===== EMPTY STATE ===== */
        .empty-state {
            text-align: center; padding: 3rem 1rem; color: var(--gray-400);
        }
        .empty-state i { font-size: 2.5rem; margin-bottom: 1rem; opacity: .4; display: block; }
        .empty-state p { font-size: .9rem; margin: 0; font-weight: 500; }

        /* ===== SECTION DIVIDER ===== */
        .section-title {
            font-size: .85rem; font-weight: 700; color: var(--gray-700);
            margin-bottom: 1rem; display: flex; align-items: center; gap: .5rem;
        }
        .section-title i { color: var(--pri); }

        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #c1c1c1; border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: #a1a1c1; }

        @media (max-width: 767px) {
            .stat-value { font-size: 1.3rem; }
            .chart-wrap { height: 240px; }
        }
    </style>

    <div class="container-fluid px-4 py-4">
        {{-- ===== PAGE HEADER ===== --}}
        <div class="d-sm-flex align-items-center justify-content-between page-header">
            <div>
                <h1 class="page-title"><i class="fa-solid fa-users me-2" style="color:var(--pri)"></i>Informasi Karyawan</h1>
                <p class="page-sub mb-0">Kelola dan pantau data SDM perusahaan Anda</p>
            </div>
            <div class="text-end">
                <small class="text-muted">Terakhir update:</small>
                <div class="fw-semibold" style="font-size:.85rem;color:var(--gray-700)" id="last-update">{{ now()->format('d M Y, H:i') }}</div>
            </div>
        </div>

        {{-- ===== FILTER BAR ===== --}}
        <div class="card card-shell mb-4">
            <div class="card-body py-3">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
                    <span class="fw-bold" style="font-size:.875rem;color:var(--pri)"><i class="fa-solid fa-filter me-1"></i>Filter Data</span>
                    <button class="btn btn-outline-sec btn-sm" id="btn-reset-filter"><i class="fa-solid fa-rotate me-1"></i>Reset</button>
                </div>
                <div class="row g-3 align-items-end">
                    <div class="col-md-3 col-sm-6">
                        <label class="form-label">Periode Data</label>
                        <select name="periode" id="periode" class="form-select form-select-sm">
                            <option value="all">Tanpa Filter</option>
                            <option value="12">12 Bulan Terakhir</option>
                            <option value="6">6 Bulan Terakhir</option>
                            <option value="3">3 Bulan Terakhir</option>
                            <option value="year">Pilih Tahun</option>
                        </select>
                    </div>
                    <div class="col-md-2 col-sm-6 d-none" id="year-selector">
                        <label class="form-label">Tahun</label>
                        <select name="year" id="year" class="form-select form-select-sm">
                            @for ($y = date('Y'); $y >= date('Y') - 5; $y--)
                                <option value="{{ $y }}">{{ $y }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-md-4 col-sm-12">
                        <label class="form-label">Pencarian</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white border-end-0"><i class="fa-solid fa-search text-muted"></i></span>
                            <input type="text" id="search-employee" class="form-control border-start-0" placeholder="Cari nama, NIP, jabatan...">
                            <button class="btn btn-pri" type="button" id="btn-search"><i class="fa-solid fa-magnifying-glass"></i></button>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-12">
                        <span class="filter-badge" id="active-filter-badge">
                            <i class="fa-solid fa-circle-info"></i>
                            <span id="filter-label">Menampilkan: Semua Data</span>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- ===== STAT CARDS ===== --}}
        <div class="row g-3 mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="card stat-card h-100" data-modal="modal-active">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div>
                            <p class="stat-label">Karyawan Active</p>
                            <h3 class="stat-value" id="stat-active">-</h3>
                        </div>
                        <div class="stat-icon" style="background:linear-gradient(135deg,#4f46e5,#7c3aed)"><i class="fa-solid fa-user-check"></i></div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card stat-card h-100" data-modal="modal-new">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div>
                            <p class="stat-label">Karyawan Baru</p>
                            <h3 class="stat-value" id="stat-new" style="color:var(--success)">-</h3>
                        </div>
                        <div class="stat-icon" style="background:linear-gradient(135deg,#059669,#10b981)"><i class="fa-solid fa-user-plus"></i></div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card stat-card h-100" data-modal="modal-resign">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div>
                            <p class="stat-label">Karyawan Resign</p>
                            <h3 class="stat-value" id="stat-resign" style="color:var(--gray-600)">-</h3>
                        </div>
                        <div class="stat-icon" style="background:linear-gradient(135deg,#6b7280,#9ca3af)"><i class="fa-solid fa-user-minus"></i></div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card stat-card h-100" data-modal="modal-retention">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div>
                            <p class="stat-label">Tingkat Retensi</p>
                            <h3 class="stat-value" id="stat-retention" style="color:var(--info)">-<small style="font-size:.9rem">%</small></h3>
                        </div>
                        <div class="stat-icon" style="background:linear-gradient(135deg,#0284c7,#38bdf8)"><i class="fa-solid fa-chart-line"></i></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ===== TABS ===== --}}
        <ul class="nav nav-tabs nav-tabs-custom mb-4" id="mainTabs">
            <li class="nav-item">
                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tabOverview">
                    <i class="fa-solid fa-chart-pie"></i>Overview & Analytics
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabData">
                    <i class="fa-solid fa-table"></i>Data Karyawan
                    <span class="tab-count" id="tabDataCount">0</span>
                </button>
            </li>
        </ul>

        <div class="tab-content">
            {{-- ===== TAB 1: OVERVIEW & ANALYTICS ===== --}}
            <div class="tab-pane fade show active" id="tabOverview">
                {{-- Headcount Trend --}}
                <div class="card card-shell mb-4">
                    <div class="card-body">
                        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
                            <div class="chart-title mb-0"><i class="fa-solid fa-chart-line"></i>Headcount Trend</div>
                            <div class="d-flex gap-2">
                                <button class="btn btn-outline-sec btn-sm" id="btn-export-trend-csv"><i class="fa-solid fa-file-csv me-1"></i>CSV</button>
                                <button class="btn btn-outline-sec btn-sm" id="btn-export-trend-pdf"><i class="fa-solid fa-file-pdf me-1"></i>PDF</button>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-3">
                                <label class="form-label">Tanggal Mulai</label>
                                <input type="date" id="trend-start-date" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Tanggal Akhir</label>
                                <input type="date" id="trend-end-date" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Group By</label>
                                <select id="trend-group-by" class="form-select form-select-sm">
                                    <option value="month">Bulanan</option>
                                    <option value="quarter">Triwulan</option>
                                    <option value="year">Tahunan</option>
                                </select>
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <button class="btn btn-pri btn-sm w-100" id="btn-apply-trend-filter"><i class="fa-solid fa-check me-1"></i>Terapkan</button>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-6 col-md-3">
                                <div class="summary-mini">
                                    <div class="value" style="color:var(--pri)" id="trend-total-active">-</div>
                                    <div class="label">Total Active</div>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="summary-mini">
                                    <div class="value" style="color:var(--success)" id="trend-total-new">-</div>
                                    <div class="label">Total New</div>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="summary-mini">
                                    <div class="value" style="color:var(--gray-600)" id="trend-total-resign">-</div>
                                    <div class="label">Total Resign</div>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="summary-mini">
                                    <div class="value" style="color:var(--info)" id="trend-avg-new">-</div>
                                    <div class="label">Avg New/Bulan</div>
                                </div>
                            </div>
                        </div>

                        <div class="chart-wrap"><canvas id="trendChart"></canvas></div>
                    </div>
                </div>

                {{-- Headcount Breakdown --}}
                <div class="card card-shell mb-4">
                    <div class="card-body">
                        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
                            <div class="chart-title mb-0"><i class="fa-solid fa-chart-column"></i>Headcount Breakdown</div>
                            <div class="d-flex gap-2">
                                <button class="btn btn-outline-sec btn-sm" id="btn-export-breakdown-csv"><i class="fa-solid fa-file-csv me-1"></i>CSV</button>
                                <button class="btn btn-outline-sec btn-sm" id="btn-export-breakdown-pdf"><i class="fa-solid fa-file-pdf me-1"></i>PDF</button>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-3">
                                <label class="form-label">Filter By</label>
                                <select id="breakdown-filter-by" class="form-select form-select-sm">
                                    <option value="divisi">Divisi</option>
                                    <option value="jabatan">Jabatan</option>
                                    <option value="gender">Gender</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Status</label>
                                <select id="breakdown-status" class="form-select form-select-sm">
                                    <option value="all">Semua</option>
                                    <option value="active">Active</option>
                                    <option value="resign">Resign</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Min. Masa Kerja (bln)</label>
                                <input type="number" id="breakdown-min-tenure" class="form-control form-control-sm" value="0" min="0">
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <button class="btn btn-pri btn-sm w-100" id="btn-apply-breakdown-filter"><i class="fa-solid fa-check me-1"></i>Terapkan</button>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <div class="summary-mini">
                                    <div class="value" style="color:var(--pri)" id="breakdown-total-cats">-</div>
                                    <div class="label">Kategori</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="summary-mini">
                                    <div class="value" style="color:var(--success)" id="breakdown-top-cat">-</div>
                                    <div class="label">Top Kategori</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="summary-mini">
                                    <div class="value" style="color:var(--info)" id="breakdown-avg-retention">-</div>
                                    <div class="label">Avg Retensi</div>
                                </div>
                            </div>
                        </div>

                        <div class="chart-wrap"><canvas id="breakdownChart"></canvas></div>

                        <div class="mt-4">
                            <div class="section-title"><i class="fa-solid fa-list"></i>Detail Retensi per Kategori</div>
                            <div id="breakdown-list"></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ===== TAB 2: DATA KARYAWAN ===== --}}
            <div class="tab-pane fade" id="tabData">
                <div class="card card-shell">
                    <div class="card-body">
                        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
                            <div class="section-title mb-0"><i class="fa-solid fa-users"></i>Daftar Karyawan</div>
                            <div class="d-flex gap-2 align-items-center">
                                <select id="list-category" class="form-select form-select-sm" style="width:160px">
                                    <option value="all">Semua</option>
                                    <option value="active">Active</option>
                                    <option value="new">Baru</option>
                                    <option value="resign">Resign</option>
                                </select>
                                <button class="btn btn-pri btn-sm" id="btn-load-employees"><i class="fa-solid fa-download me-1"></i>Load Data</button>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table-modern mb-0">
                                <thead>
                                    <tr>
                                        <th>Nama</th>
                                        <th>NIP</th>
                                        <th>Jabatan</th>
                                        <th>Divisi</th>
                                        <th>Tanggal Join</th>
                                        <th class="text-center">Status</th>
                                    </tr>
                                </thead>
                                <tbody id="employee-table-body"></tbody>
                            </table>
                        </div>

                        <div class="d-flex flex-wrap justify-content-between align-items-center mt-3">
                            <small class="text-muted" id="table-info">Menampilkan data karyawan</small>
                            <div class="pagination-custom mt-2 mt-md-0" id="employee-pagination"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== MODAL: ACTIVE ===== --}}
    <div class="modal fade" id="modal-active" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header modal-header-custom border-0">
                    <h5 class="modal-title"><i class="fa-solid fa-user-check me-2"></i>Daftar Karyawan Active</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="list-active"></div>
                    <div class="pagination-custom mt-3 justify-content-center" id="pagination-active"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== MODAL: NEW ===== --}}
    <div class="modal fade" id="modal-new" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header modal-header-custom border-0">
                    <h5 class="modal-title"><i class="fa-solid fa-user-plus me-2"></i>Daftar Karyawan Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="list-new"></div>
                    <div class="pagination-custom mt-3 justify-content-center" id="pagination-new"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== MODAL: RESIGN ===== --}}
    <div class="modal fade" id="modal-resign" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header modal-header-custom border-0">
                    <h5 class="modal-title"><i class="fa-solid fa-user-minus me-2"></i>Daftar Karyawan Resign</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="list-resign"></div>
                    <div class="pagination-custom mt-3 justify-content-center" id="pagination-resign"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== MODAL: RETENTION ===== --}}
    <div class="modal fade" id="modal-retention" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header modal-header-custom border-0">
                    <h5 class="modal-title"><i class="fa-solid fa-chart-line me-2"></i>Analisis Tingkat Retensi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-4">
                        <div class="col-md-4 text-center">
                            <div class="gauge-wrap">
                                <svg width="160" height="160" style="transform:rotate(-90deg)">
                                    <circle cx="80" cy="80" r="65" fill="none" stroke="var(--gray-100)" stroke-width="12"></circle>
                                    <circle class="progress-ring" cx="80" cy="80" r="65" fill="none" stroke="var(--pri)" stroke-width="12" stroke-linecap="round" stroke-dasharray="408.4" stroke-dashoffset="408.4" style="transition:stroke-dashoffset 0.6s ease"></circle>
                                </svg>
                                <div class="gauge-value">
                                    <div class="num"><span id="gauge-value">0</span></div>
                                    <div class="unit">%</div>
                                </div>
                            </div>
                            <span class="status-badge status-active" id="retention-status">Baik</span>
                        </div>
                        <div class="col-md-8">
                            <div class="section-title"><i class="fa-solid fa-chart-simple"></i>Ringkasan Statistik</div>
                            <div class="row g-2 mb-3">
                                <div class="col-6"><div class="summary-mini"><div class="value" style="color:var(--pri);font-size:1.1rem" id="summary-total">-</div><div class="label">Total</div></div></div>
                                <div class="col-6"><div class="summary-mini"><div class="value" style="color:var(--success);font-size:1.1rem" id="summary-active">-</div><div class="label">Active</div></div></div>
                                <div class="col-6"><div class="summary-mini"><div class="value" style="color:var(--gray-600);font-size:1.1rem" id="summary-resign">-</div><div class="label">Resign</div></div></div>
                                <div class="col-6"><div class="summary-mini"><div class="value" style="color:var(--info);font-size:1.1rem" id="summary-ratio">-</div><div class="label">Rasio</div></div></div>
                            </div>

                            <div class="section-title"><i class="fa-solid fa-lightbulb" style="color:var(--warning)"></i>Peluang Peningkatan</div>
                            <div id="opportunities-list"></div>

                            <div class="section-title mt-3"><i class="fa-solid fa-circle-check"></i>Rekomendasi</div>
                            <div id="recommendations-list"></div>
                        </div>
                    </div>

                    <div class="section-title mt-4"><i class="fa-solid fa-crystal-ball"></i>Proyeksi</div>
                    <div class="table-responsive">
                        <table class="table-modern">
                            <thead>
                                <tr>
                                    <th>Periode</th>
                                    <th>Est. Active</th>
                                    <th>Est. Resign</th>
                                    <th class="text-center">Confidence</th>
                                </tr>
                            </thead>
                            <tbody id="projections-body"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        $(document).ready(function() {
            $.ajaxSetup({
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
            });
            let trendChart, breakdownChart;
            loadStats();
            loadTrendChart();
            loadBreakdownChart();

            // Click stat card to open modal
            $('.card[data-modal]').on('click', function() {
                const modalId = $(this).data('modal'),
                    category = modalId.replace('modal-', '');
                if (category === 'retention') loadRetentionAnalysis();
                else loadEmployeeList(category, 1);
                new bootstrap.Modal(document.getElementById(modalId)).show();
            });

            // Filter events
            $('#periode, #year, #btn-search').on('change click', function() {
                updateFilterLabel();
                loadStats();
            });
            $('#search-employee').on('keypress', function(e) {
                if (e.which === 13) { updateFilterLabel(); loadStats(); }
            });
            $('#btn-reset-filter').on('click', function() {
                $('#periode').val('all');
                $('#year').val($('#year option:first').val());
                $('#year-selector').addClass('d-none');
                $('#search-employee').val('');
                updateFilterLabel();
                loadStats();
            });
            $('#periode').on('change', function() {
                $('#year-selector').toggleClass('d-none', $(this).val() !== 'year');
            });

            // Trend events
            $('#btn-apply-trend-filter').on('click', loadTrendChart);
            $('#btn-export-trend-csv').on('click', function() {
                window.location = "{{ route('HR.employee.trend.export.csv') }}?" + $.param(getTrendParams());
            });
            $('#btn-export-trend-pdf').on('click', function() {
                window.location = "{{ route('HR.employee.trend.export.pdf') }}?" + $.param(getTrendParams());
            });

            // Breakdown events
            $('#btn-apply-breakdown-filter').on('click', loadBreakdownChart);
            $('#btn-export-breakdown-csv').on('click', function() {
                window.location = "{{ route('HR.employee.breakdown.export.csv') }}?" + $.param(getBreakdownParams());
            });
            $('#btn-export-breakdown-pdf').on('click', function() {
                window.location = "{{ route('HR.employee.breakdown.export.pdf') }}?" + $.param(getBreakdownParams());
            });

            // Employee table
            $('#btn-load-employees').on('click', function() { loadEmployeeTable(1); });

            function getFilterParams() {
                return {
                    periode: $('#periode').val(),
                    year: $('#year').val(),
                    search: $('#search-employee').val()
                };
            }

            function getTrendParams() {
                return {
                    start_date: $('#trend-start-date').val(),
                    end_date: $('#trend-end-date').val(),
                    group_by: $('#trend-group-by').val()
                };
            }

            function getBreakdownParams() {
                return {
                    filter_by: $('#breakdown-filter-by').val(),
                    status: $('#breakdown-status').val(),
                    min_tenure: $('#breakdown-min-tenure').val()
                };
            }

            function updateFilterLabel() {
                const p = $('#periode').val(), y = $('#year').val(), s = $('#search-employee').val();
                let lbl = 'Menampilkan: ';
                if (p === 'all' && !s) lbl += 'Semua Data';
                else {
                    lbl += p === 'all' ? 'Semua Periode' : (p === 'year' ? `Tahun ${y}` : `${p} Bulan Terakhir`);
                    if (s) lbl += ` • Search: "${s}"`;
                }
                $('#filter-label').text(lbl);
            }

            function loadStats() {
                $.get("{{ route('HR.employee.data') }}", getFilterParams(), function(res) {
                    if (res.stats) {
                        $('#stat-active').text(res.stats.active);
                        $('#stat-new').text(res.stats.new);
                        $('#stat-resign').text(res.stats.resign);
                        $('#stat-retention').html(res.stats.retention_rate + '<small style="font-size:.9rem">%</small>');
                        $('#tabDataCount').text(res.stats.active || 0);
                        $('#last-update').text(new Date().toLocaleString('id-ID', {
                            day: '2-digit', month: 'short', year: 'numeric',
                            hour: '2-digit', minute: '2-digit'
                        }));
                    }
                });
            }

            function loadTrendChart() {
                $.get("{{ route('HR.employee.trend') }}", getTrendParams(), function(res) {
                    $('#trend-total-active').text(res.summary.total_active);
                    $('#trend-total-new').text(res.summary.total_new);
                    $('#trend-total-resign').text(res.summary.total_resign);
                    $('#trend-avg-new').text(res.summary.avg_monthly_new);

                    if (trendChart) trendChart.destroy();
                    const chartFont = { family: "-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto", size: 11 };
                    const colors = ['#4f46e5', '#059669', '#6b7280'];

                    trendChart = new Chart(document.getElementById('trendChart'), {
                        type: 'line',
                        data: {
                            labels: res.labels,
                            datasets: res.datasets.map((d, i) => ({
                                ...d,
                                borderColor: colors[i] || colors[0],
                                backgroundColor: colors[i] ? colors[i] + '20' : colors[0] + '20',
                                tension: 0.4, fill: false, borderWidth: 2.5,
                                pointRadius: 4, pointHoverRadius: 6,
                                pointBackgroundColor: '#fff',
                                pointBorderColor: colors[i] || colors[0],
                                pointBorderWidth: 2
                            }))
                        },
                        options: {
                            responsive: true, maintainAspectRatio: false,
                            plugins: {
                                legend: { position: 'bottom', labels: { font: chartFont, usePointStyle: true, padding: 15 } },
                                tooltip: {
                                    backgroundColor: 'rgba(17, 24, 39, 0.95)',
                                    titleFont: { size: 12, weight: 'bold' },
                                    bodyFont: { size: 11 }, padding: 12, cornerRadius: 8
                                }
                            },
                            scales: {
                                y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.05)' }, ticks: { font: chartFont } },
                                x: { grid: { display: false }, ticks: { font: chartFont, maxRotation: 45 } }
                            }
                        }
                    });
                });
            }

            function loadBreakdownChart() {
                $.get("{{ route('HR.employee.breakdown') }}", getBreakdownParams(), function(res) {
                    $('#breakdown-total-cats').text(res.summary.total_categories);
                    $('#breakdown-top-cat').text(res.summary.top_category);
                    $('#breakdown-avg-retention').text(res.summary.avg_retention + '%');

                    if (breakdownChart) breakdownChart.destroy();
                    const chartFont = { family: "-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto", size: 11 };

                    breakdownChart = new Chart(document.getElementById('breakdownChart'), {
                        type: 'bar',
                        data: {
                            labels: res.chart.labels,
                            datasets: [{
                                label: 'Total',
                                data: res.chart.total,
                                backgroundColor: 'rgba(79, 70, 229, 0.85)',
                                borderRadius: 6, borderSkipped: false
                            }, {
                                label: 'Active',
                                data: res.chart.active,
                                backgroundColor: 'rgba(5, 150, 105, 0.85)',
                                borderRadius: 6, borderSkipped: false
                            }]
                        },
                        options: {
                            responsive: true, maintainAspectRatio: false,
                            plugins: {
                                legend: { position: 'bottom', labels: { font: chartFont, usePointStyle: true, padding: 15 } },
                                tooltip: {
                                    backgroundColor: 'rgba(17, 24, 39, 0.95)',
                                    titleFont: { size: 12, weight: 'bold' },
                                    bodyFont: { size: 11 }, padding: 12, cornerRadius: 8
                                }
                            },
                            scales: {
                                y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.05)' }, ticks: { font: chartFont } },
                                x: { grid: { display: false }, ticks: { font: chartFont, maxRotation: 45, minRotation: 45 } }
                            }
                        }
                    });

                    const list = $('#breakdown-list').empty();
                    if (!res.breakdown || res.breakdown.length === 0) {
                        list.html('<div class="empty-state"><i class="fa-solid fa-chart-simple"></i><p>Belum ada data breakdown</p></div>');
                        return;
                    }
                    res.breakdown.forEach(item => {
                        list.append(`
                            <div class="breakdown-item">
                                <div class="breakdown-label">${item.label}</div>
                                <div class="breakdown-bar-wrap"><div class="breakdown-bar" style="width:${item.retention}%"></div></div>
                                <div class="breakdown-value">${item.retention}%</div>
                            </div>
                        `);
                    });
                });
            }

            function loadEmployeeTable(page) {
                const params = { category: $('#list-category').val(), ...getFilterParams(), page };
                $.get("{{ route('HR.employee.category') }}", params, function(res) {
                    const tbody = $('#employee-table-body').empty();
                    if (res.data.length === 0) {
                        tbody.append('<tr><td colspan="6"><div class="empty-state"><i class="fa-solid fa-users-slash"></i><p>Tidak ada data yang ditemukan</p><small>Coba ubah filter atau kategori</small></div></td></tr>');
                    } else {
                        res.data.forEach(emp => {
                            const statusClass = emp.status === 'Aktif' ? 'status-active' : 'status-resign';
                            tbody.append(`
                                <tr>
                                    <td><strong style="color:var(--gray-900)">${emp.nama}</strong><br><small class="text-muted">${emp.nama_lengkap}</small></td>
                                    <td class="text-muted">${emp.nip}</td>
                                    <td>${emp.jabatan}</td>
                                    <td>${emp.divisi}</td>
                                    <td>${emp.tanggal_join}</td>
                                    <td class="text-center"><span class="status-badge ${statusClass}">${emp.status}</span></td>
                                </tr>
                            `);
                        });
                    }
                    $('#table-info').text(`Menampilkan ${res.data.length} dari ${res.pagination.total} data`);
                    const pag = $('#employee-pagination').empty();
                    if (res.pagination.last_page > 1) {
                        for (let i = 1; i <= res.pagination.last_page; i++) {
                            const active = i === res.pagination.current_page ? 'active' : '';
                            pag.append(`<button class="${active}" data-page="${i}">${i}</button>`);
                        }
                        pag.off('click', 'button').on('click', 'button', function() {
                            loadEmployeeTable($(this).data('page'));
                        });
                    }
                });
            }

            function loadEmployeeList(category, page) {
                const listId = `#list-${category}`, paginationId = `#pagination-${category}`;
                $(listId).empty();
                $(paginationId).empty();
                $.get("{{ route('HR.employee.category') }}", { ...getFilterParams(), category, page }, function(res) {
                    if (res.data.length === 0) {
                        $(listId).html('<div class="empty-state"><i class="fa-solid fa-inbox"></i><p>Tidak ada data</p></div>');
                    } else {
                        res.data.forEach(emp => {
                            const dateDisplay = category === 'resign' && emp.resigned_at
                                ? `<br><small style="color:var(--danger)"><i class="fa-solid fa-calendar-xmark me-1"></i>Resign: ${emp.resigned_at}</small>`
                                : emp.tanggal_join !== '-'
                                ? `<br><small class="emp-meta"><i class="fa-solid fa-calendar me-1"></i>${emp.tanggal_join}</small>`
                                : '';
                            $(listId).append(`
                                <div class="emp-list-item">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <div class="emp-name">${emp.nama}</div>
                                            <div class="emp-meta">${emp.nama_lengkap} • ${emp.nip}</div>
                                            <div class="emp-meta mt-1"><i class="fa-solid fa-briefcase me-1"></i>${emp.jabatan} • ${emp.divisi}</div>
                                            ${dateDisplay}
                                        </div>
                                        <span class="status-badge status-active">${emp.status}</span>
                                    </div>
                                </div>
                            `);
                        });
                    }
                    if (res.pagination.last_page > 1) {
                        let pagination = '';
                        for (let i = 1; i <= res.pagination.last_page; i++) {
                            const active = i === res.pagination.current_page ? 'active' : '';
                            pagination += `<button class="${active}" data-page="${i}">${i}</button>`;
                        }
                        $(paginationId).html(pagination).off('click', 'button').on('click', 'button', function() {
                            loadEmployeeList(category, $(this).data('page'));
                        });
                    }
                });
            }

            function loadRetentionAnalysis() {
                $.get("{{ route('HR.employee.data') }}", getFilterParams(), function(res) {
                    if (!res.stats || !res.insights) return;
                    const rate = res.stats.retention_rate;
                    const circumference = 2 * Math.PI * 65;
                    const offset = circumference - (rate / 100) * circumference;
                    $('#gauge-value').text(rate);
                    $('.progress-ring').css({ 'stroke-dasharray': circumference, 'stroke-dashoffset': offset });

                    let strokeColor, statusText, statusClass;
                    if (rate >= 90) { strokeColor = 'var(--success)'; statusText = 'Sangat Baik'; statusClass = 'status-active'; }
                    else if (rate >= 75) { strokeColor = 'var(--pri)'; statusText = 'Baik'; statusClass = 'status-active'; }
                    else if (rate >= 60) { strokeColor = 'var(--warning)'; statusText = 'Cukup'; statusClass = 'status-badge'; }
                    else { strokeColor = 'var(--gray-400)'; statusText = 'Perlu Perhatian'; statusClass = 'status-resign'; }

                    $('.progress-ring').css('stroke', strokeColor);
                    $('#retention-status').text(statusText).attr('class', 'status-badge ' + statusClass);

                    $('#summary-total').text(res.stats.total_employees);
                    $('#summary-active').text(res.stats.active);
                    $('#summary-resign').text(res.stats.resign);
                    $('#summary-ratio').text(`${res.stats.active} : ${res.stats.resign}`);

                    const oppList = $('#opportunities-list').empty();
                    if (res.insights.opportunities && res.insights.opportunities.length > 0) {
                        res.insights.opportunities.forEach(opp => {
                            oppList.append(`<div class="insight-card"><i class="fa-solid fa-lightbulb"></i><div>${opp}</div></div>`);
                        });
                    } else {
                        oppList.html('<div class="empty-state" style="padding:1rem"><small>Tidak ada peluang saat ini</small></div>');
                    }

                    const recList = $('#recommendations-list').empty();
                    if (res.insights.recommendations && res.insights.recommendations.length > 0) {
                        res.insights.recommendations.slice(0, 3).forEach(rec => {
                            recList.append(`<div class="insight-card rec"><i class="fa-solid fa-circle-check"></i><div>${rec}</div></div>`);
                        });
                    } else {
                        recList.html('<div class="empty-state" style="padding:1rem"><small>Tidak ada rekomendasi saat ini</small></div>');
                    }

                    const projBody = $('#projections-body').empty();
                    if (res.insights.projections) {
                        Object.entries(res.insights.projections).forEach(([period, data]) => {
                            const label = period === 'next_quarter' ? 'Kuartal Depan' : 'Tahun Depan';
                            let confClass = 'status-resign';
                            if (data.confidence === 'high') confClass = 'status-active';
                            else if (data.confidence === 'medium') confClass = 'status-badge';
                            projBody.append(`
                                <tr>
                                    <td><strong>${label}</strong></td>
                                    <td class="fw-bold" style="color:var(--success)">${data.estimated_active}</td>
                                    <td class="fw-bold" style="color:var(--danger)">${data.estimated_resign}</td>
                                    <td class="text-center"><span class="status-badge ${confClass}">${data.confidence}</span></td>
                                </tr>
                            `);
                        });
                    }
                });
            }

            updateFilterLabel();
        });
    </script>
@endsection