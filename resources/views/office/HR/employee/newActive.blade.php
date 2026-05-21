@extends('layouts_office.app')

@section('office_contents')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        .card-stats {
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
            min-height: 120px;
            position: relative;
            overflow: hidden;
        }

        .card-stats:hover {
            transform: translateY(-3px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }

        .card-stats::after {
            content: '🔍';
            position: absolute;
            bottom: 8px;
            right: 12px;
            font-size: 12px;
            opacity: 0.6;
        }

        .stat-icon {
            font-size: 1.5rem;
            opacity: 0.8;
        }

        .modal-body {
            max-height: 70vh;
            overflow-y: auto;
        }

        .employee-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .employee-item {
            padding: 12px 16px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .employee-item:last-child {
            border-bottom: none;
        }

        .employee-info h6 {
            margin: 0 0 4px 0;
            font-weight: 600;
        }

        .employee-info small {
            color: #6c757d;
        }

        .pagination-container {
            display: flex;
            justify-content: center;
            margin-top: 20px;
            gap: 4px;
        }

        .pagination-container .page-link {
            padding: 6px 12px;
            font-size: 14px;
        }

        .insight-card {
            background: #f8f9fa;
            border-left: 4px solid #0d6efd;
            padding: 12px 16px;
            margin-bottom: 12px;
            border-radius: 0 4px 4px 0;
        }

        .insight-card.warning {
            border-left-color: #ffc107;
            background: #fff3cd;
        }

        .insight-card.success {
            border-left-color: #198754;
            background: #d1e7dd;
        }

        .retention-gauge {
            width: 150px;
            height: 150px;
            margin: 0 auto 20px;
            position: relative;
        }

        .retention-gauge svg {
            transform: rotate(-90deg);
        }

        .retention-gauge circle {
            fill: none;
            stroke-width: 10;
        }

        .retention-gauge .bg {
            stroke: #e9ecef;
        }

        .retention-gauge .progress {
            stroke: #0d6efd;
            stroke-linecap: round;
            transition: stroke-dashoffset 0.5s ease;
        }

        .retention-gauge .value {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 1.5rem;
            font-weight: bold;
        }

        .projection-table {
            width: 100%;
            font-size: 14px;
        }

        .projection-table th {
            background: #f8f9fa;
            padding: 8px 12px;
            text-align: left;
        }

        .projection-table td {
            padding: 8px 12px;
            border-bottom: 1px solid #eee;
        }

        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 3px;
        }

        ::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 3px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #a1a1a1;
        }

        .loading-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.9);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10;
            border-radius: 0.5rem;
        }

        .loading-overlay.hidden {
            display: none;
        }

        .filter-badge {
            font-size: 11px;
            padding: 2px 8px;
            border-radius: 10px;
            background: #e9ecef;
            color: #495057;
        }

        .section-card {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08);
            margin-bottom: 24px;
        }

        .section-header {
            padding: 16px 20px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .section-body {
            padding: 20px;
        }

        .chart-container {
            position: relative;
            height: 300px;
            margin-bottom: 20px;
        }

        .breakdown-item {
            display: flex;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #f1f1f1;
        }

        .breakdown-item:last-child {
            border-bottom: none;
        }

        .breakdown-bar {
            flex: 1;
            height: 24px;
            background: #e9ecef;
            border-radius: 4px;
            overflow: hidden;
            margin: 0 12px;
            position: relative;
        }

        .breakdown-bar-fill {
            height: 100%;
            background: #0d6efd;
            transition: width 0.3s ease;
        }

        .breakdown-bar-fill.active {
            background: #198754;
        }

        .breakdown-label {
            min-width: 120px;
            font-weight: 500;
        }

        .breakdown-value {
            min-width: 60px;
            text-align: right;
            font-weight: 600;
        }

        .export-btn-group {
            display: flex;
            gap: 8px;
        }

        .filter-row {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            align-items: flex-end;
            margin-bottom: 16px;
        }

        .filter-item {
            flex: 1;
            min-width: 150px;
        }

        .filter-item label {
            font-size: 12px;
            color: #6c757d;
            margin-bottom: 4px;
            display: block;
        }

        .filter-item .form-select,
        .filter-item .form-control {
            font-size: 14px;
        }

        .summary-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 12px;
            margin-bottom: 20px;
        }

        .summary-stat {
            background: #f8f9fa;
            padding: 12px;
            border-radius: 6px;
            text-align: center;
        }

        .summary-stat-value {
            font-size: 1.25rem;
            font-weight: 700;
            color: #0d6efd;
        }

        .summary-stat-label {
            font-size: 11px;
            color: #6c757d;
            margin-top: 4px;
        }
    </style>

    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="m-0">Informasi Karyawan</h3>
            <p class="text-muted mb-0">terakhir update: <span id="last-update">{{ now()->format('d M Y, H:i') }}</span></p>
        </div>

        <div class="row mb-3">
            <div class="col-md-3">
                <label class="form-label small text-muted">Periode Data</label>
                <select name="periode" id="periode" class="form-select form-select-sm">
                    <option value="all">Tanpa Filter</option>
                    <option value="12">12 Bulan Terakhir</option>
                    <option value="6">6 Bulan Terakhir</option>
                    <option value="3">3 Bulan Terakhir</option>
                    <option value="year">Pilih Tahun</option>
                </select>
            </div>
            <div class="col-md-2 d-none" id="year-selector">
                <label class="form-label small text-muted">Tahun</label>
                <select name="year" id="year" class="form-select form-select-sm">
                    @for ($y = date('Y'); $y >= date('Y') - 5; $y--)
                        <option value="{{ $y }}">{{ $y }}</option>
                    @endfor
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label small text-muted">&nbsp;</label>
                <div class="input-group input-group-sm">
                    <input type="text" id="search-employee" class="form-control"
                        placeholder="Cari nama, NIP, jabatan...">
                    <button class="btn btn-outline-secondary" type="button" id="btn-search"><i
                            class="fa-solid fa-search"></i></button>
                </div>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button class="btn btn-sm btn-outline-primary w-100" id="btn-reset-filter"><i
                        class="fa-solid fa-rotate-left me-1"></i>Reset Filter</button>
            </div>
        </div>

        <div class="row mb-2">
            <div class="col-12">
                <span class="filter-badge" id="active-filter-badge"><i class="fa-solid fa-filter me-1"></i><span
                        id="filter-label">Menampilkan: Semua Data</span></span>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-3 col-sm-6">
                <div class="card card-stats" data-modal="modal-active">
                    <div class="card-body">
                        <h5 class="card-title d-flex align-items-center"><i
                                class="fa-regular fa-user me-2 stat-icon"></i><span id="stat-active">-</span></h5>
                        <p class="card-text text-muted small mb-0">Karyawan Active</p>
                        <small class="text-muted" style="font-size: 11px;">Klik untuk lihat detail</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="card card-stats" data-modal="modal-new">
                    <div class="card-body">
                        <h5 class="card-title d-flex align-items-center"><i
                                class="fa-solid fa-user-plus me-2 stat-icon"></i><span id="stat-new">-</span></h5>
                        <p class="card-text text-muted small mb-0">Karyawan Baru</p>
                        <small class="text-muted" style="font-size: 11px;">Klik untuk lihat detail</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="card card-stats" data-modal="modal-resign">
                    <div class="card-body">
                        <h5 class="card-title d-flex align-items-center"><i
                                class="fa-solid fa-user-xmark me-2 stat-icon"></i><span id="stat-resign">-</span></h5>
                        <p class="card-text text-muted small mb-0">Karyawan Resign</p>
                        <small class="text-muted" style="font-size: 11px;">Klik untuk lihat detail</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="card card-stats" data-modal="modal-retention">
                    <div class="card-body">
                        <h5 class="card-title d-flex align-items-center"><i
                                class="fa-solid fa-chart-line me-2 stat-icon"></i><span id="stat-retention">-</span>%</h5>
                        <p class="card-text text-muted small mb-0">Tingkat Retensi</p>
                        <small class="text-muted" style="font-size: 11px;">Klik untuk analisis</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="section-card">
            <div class="section-header">
                <h5 class="m-0"><i class="fa-solid fa-chart-line me-2"></i>Headcount Trend</h5>
                <div class="export-btn-group">
                    <button class="btn btn-sm btn-outline-success" id="btn-export-trend-csv"><i
                            class="fa-solid fa-file-csv me-1"></i>CSV</button>
                    <button class="btn btn-sm btn-outline-danger" id="btn-export-trend-pdf"><i
                            class="fa-solid fa-file-pdf me-1"></i>PDF</button>
                </div>
            </div>
            <div class="section-body">
                <div class="filter-row">
                    <div class="filter-item">
                        <label>Tanggal Mulai</label>
                        <input type="date" id="trend-start-date" class="form-control form-control-sm">
                    </div>
                    <div class="filter-item">
                        <label>Tanggal Akhir</label>
                        <input type="date" id="trend-end-date" class="form-control form-control-sm">
                    </div>
                    <div class="filter-item">
                        <label>Group By</label>
                        <select id="trend-group-by" class="form-select form-select-sm">
                            <option value="month">Bulanan</option>
                            <option value="quarter">Triwulan</option>
                            <option value="year">Tahunan</option>
                        </select>
                    </div>
                    <div class="filter-item">
                        <label>&nbsp;</label>
                        <button class="btn btn-sm btn-primary w-100" id="btn-apply-trend-filter">Terapkan</button>
                    </div>
                </div>
                <div class="summary-stats">
                    <div class="summary-stat">
                        <div class="summary-stat-value" id="trend-total-active">-</div>
                        <div class="summary-stat-label">Total Active</div>
                    </div>
                    <div class="summary-stat">
                        <div class="summary-stat-value" id="trend-total-new">-</div>
                        <div class="summary-stat-label">Total New</div>
                    </div>
                    <div class="summary-stat">
                        <div class="summary-stat-value" id="trend-total-resign">-</div>
                        <div class="summary-stat-label">Total Resign</div>
                    </div>
                    <div class="summary-stat">
                        <div class="summary-stat-value" id="trend-avg-new">-</div>
                        <div class="summary-stat-label">Avg New/Bulan</div>
                    </div>
                </div>
                <div class="chart-container"><canvas id="trendChart"></canvas></div>
            </div>
        </div>

        <div class="section-card">
            <div class="section-header">
                <h5 class="m-0"><i class="fa-solid fa-chart-pie me-2"></i>Headcount Breakdown</h5>
                <div class="export-btn-group">
                    <button class="btn btn-sm btn-outline-success" id="btn-export-breakdown-csv"><i
                            class="fa-solid fa-file-csv me-1"></i>CSV</button>
                    <button class="btn btn-sm btn-outline-danger" id="btn-export-breakdown-pdf"><i
                            class="fa-solid fa-file-pdf me-1"></i>PDF</button>
                </div>
            </div>
            <div class="section-body">
                <div class="filter-row">
                    <div class="filter-item">
                        <label>Filter By</label>
                        <select id="breakdown-filter-by" class="form-select form-select-sm">
                            <option value="divisi">Divisi</option>
                            <option value="jabatan">Jabatan</option>
                            <option value="gender">Gender</option>
                        </select>
                    </div>
                    <div class="filter-item">
                        <label>Status</label>
                        <select id="breakdown-status" class="form-select form-select-sm">
                            <option value="all">Semua</option>
                            <option value="active">Active</option>
                            <option value="resign">Resign</option>
                        </select>
                    </div>
                    <div class="filter-item">
                        <label>Min. Masa Kerja (bulan)</label>
                        <input type="number" id="breakdown-min-tenure" class="form-control form-control-sm"
                            value="0" min="0">
                    </div>
                    <div class="filter-item">
                        <label>&nbsp;</label>
                        <button class="btn btn-sm btn-primary w-100" id="btn-apply-breakdown-filter">Terapkan</button>
                    </div>
                </div>
                <div class="summary-stats">
                    <div class="summary-stat">
                        <div class="summary-stat-value" id="breakdown-total-cats">-</div>
                        <div class="summary-stat-label">Kategori</div>
                    </div>
                    <div class="summary-stat">
                        <div class="summary-stat-value" id="breakdown-top-cat">-</div>
                        <div class="summary-stat-label">Top Kategori</div>
                    </div>
                    <div class="summary-stat">
                        <div class="summary-stat-value" id="breakdown-avg-retention">-</div>
                        <div class="summary-stat-label">Avg Retensi</div>
                    </div>
                </div>
                <div class="chart-container"><canvas id="breakdownChart"></canvas></div>
                <div id="breakdown-list"></div>
            </div>
        </div>

        <div class="section-card">
            <div class="section-header">
                <h5 class="m-0"><i class="fa-solid fa-users me-2"></i>Employee List</h5>
            </div>
            <div class="section-body">
                <div class="filter-row">
                    <div class="filter-item">
                        <label>Kategori</label>
                        <select id="list-category" class="form-select form-select-sm">
                            <option value="all">Semua</option>
                            <option value="active">Active</option>
                            <option value="new">Baru</option>
                            <option value="resign">Resign</option>
                        </select>
                    </div>
                    <div class="filter-item">
                        <label>&nbsp;</label>
                        <button class="btn btn-sm btn-primary w-100" id="btn-load-employees">Load Data</button>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover table-sm" id="employee-table">
                        <thead class="table-light">
                            <tr>
                                <th>Nama</th>
                                <th>NIP</th>
                                <th>Jabatan</th>
                                <th>Divisi</th>
                                <th>Tanggal Join</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="employee-table-body"></tbody>
                    </table>
                </div>
                <div class="pagination-container" id="employee-pagination"></div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modal-active" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Daftar Karyawan Active</h5><button type="button" class="btn-close"
                        data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body position-relative">
                    <div class="loading-overlay hidden">
                        <div class="spinner-border text-secondary"></div>
                    </div>
                    <ul class="employee-list" id="list-active"></ul>
                    <div class="pagination-container" id="pagination-active"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="modal-new" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Daftar Karyawan Baru</h5><button type="button" class="btn-close"
                        data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body position-relative">
                    <div class="loading-overlay hidden">
                        <div class="spinner-border text-secondary"></div>
                    </div>
                    <ul class="employee-list" id="list-new"></ul>
                    <div class="pagination-container" id="pagination-new"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="modal-resign" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Daftar Karyawan Resign</h5><button type="button" class="btn-close"
                        data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body position-relative">
                    <div class="loading-overlay hidden">
                        <div class="spinner-border text-secondary"></div>
                    </div>
                    <ul class="employee-list" id="list-resign"></ul>
                    <div class="pagination-container" id="pagination-resign"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="modal-retention" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Analisis Tingkat Retensi</h5><button type="button" class="btn-close"
                        data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4 text-center">
                            <div class="retention-gauge"><svg width="150" height="150">
                                    <circle class="bg" cx="75" cy="75" r="60"></circle>
                                    <circle class="progress" cx="75" cy="75" r="60" stroke-dasharray="377"
                                        stroke-dashoffset="377"></circle>
                                </svg>
                                <div class="value"><span id="gauge-value">0</span>%</div>
                            </div><span class="badge bg-success-subtle text-success" id="retention-status">Baik</span>
                        </div>
                        <div class="col-md-8">
                            <h6 class="mb-3">Ringkasan Statistik</h6>
                            <table class="projection-table mb-4">
                                <tr>
                                    <td><strong>Total Karyawan</strong></td>
                                    <td id="summary-total">-</td>
                                </tr>
                                <tr>
                                    <td><strong>Karyawan Active</strong></td>
                                    <td id="summary-active">-</td>
                                </tr>
                                <tr>
                                    <td><strong>Karyawan Resign</strong></td>
                                    <td id="summary-resign">-</td>
                                </tr>
                                <tr>
                                    <td><strong>Rasio Retensi</strong></td>
                                    <td id="summary-ratio">-</td>
                                </tr>
                            </table>
                            <h6 class="mb-3">Peluang Peningkatan</h6>
                            <div id="opportunities-list"></div>
                            <h6 class="mb-3 mt-4">Rekomendasi</h6>
                            <div id="recommendations-list"></div>
                        </div>
                    </div>
                    <h6 class="mb-3 mt-4">Proyeksi</h6>
                    <table class="projection-table">
                        <thead>
                            <tr>
                                <th>Periode</th>
                                <th>Est. Active</th>
                                <th>Est. Resign</th>
                                <th>Confidence</th>
                            </tr>
                        </thead>
                        <tbody id="projections-body"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        $(document).ready(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            let trendChart, breakdownChart;
            loadStats();
            loadTrendChart();
            loadBreakdownChart();

            $('.card-stats').on('click', function() {
                const modalId = $(this).data('modal'),
                    category = modalId.replace('modal-', '');
                if (category === 'retention') loadRetentionAnalysis();
                else loadEmployeeList(category, 1);
                new bootstrap.Modal(document.getElementById(modalId)).show();
            });

            $('#periode, #year, #btn-search, #search-employee').on('change keypress', function(e) {
                if (e.type === 'keypress' && e.which !== 13) return;
                updateFilterLabel();
                loadStats();
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

            $('#btn-apply-trend-filter').on('click', loadTrendChart);
            $('#btn-export-trend-csv').on('click', function() {
                window.location = "{{ route('office.HR.employee.trend.export.csv') }}?" + $.param(
                    getTrendParams());
            });
            $('#btn-export-trend-pdf').on('click', function() {
                window.location = "{{ route('office.HR.employee.trend.export.pdf') }}?" + $.param(
                    getTrendParams());
            });

            $('#btn-apply-breakdown-filter').on('click', loadBreakdownChart);
            $('#btn-export-breakdown-csv').on('click', function() {
                window.location = "{{ route('office.HR.employee.breakdown.export.csv') }}?" + $.param(
                    getBreakdownParams());
            });
            $('#btn-export-breakdown-pdf').on('click', function() {
                window.location = "{{ route('office.HR.employee.breakdown.export.pdf') }}?" + $.param(
                    getBreakdownParams());
            });

            $('#btn-load-employees').on('click', function() {
                loadEmployeeTable(1);
            });

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
                const p = $('#periode').val(),
                    y = $('#year').val(),
                    s = $('#search-employee').val();
                let lbl = 'Menampilkan: ';
                if (p === 'all' && !s) lbl += 'Semua Data';
                else {
                    lbl += p === 'all' ? 'Semua Periode' : (p === 'year' ? `Tahun ${y}` : `${p} Bulan Terakhir`);
                    if (s) lbl += ` • Search: "${s}"`;
                }
                $('#filter-label').text(lbl);
            }

            function loadStats() {
                $.get("{{ route('office.HR.employee.data') }}", getFilterParams(), function(res) {
                    if (res.stats) {
                        $('#stat-active').text(res.stats.active);
                        $('#stat-new').text(res.stats.new);
                        $('#stat-resign').text(res.stats.resign);
                        $('#stat-retention').text(res.stats.retention_rate);
                        $('#last-update').text(new Date().toLocaleString('id-ID', {
                            day: '2-digit',
                            month: 'short',
                            year: 'numeric',
                            hour: '2-digit',
                            minute: '2-digit'
                        }));
                    }
                });
            }

            function loadTrendChart() {
                $.get("{{ route('office.HR.employee.trend') }}", getTrendParams(), function(res) {
                    $('#trend-total-active').text(res.summary.total_active);
                    $('#trend-total-new').text(res.summary.total_new);
                    $('#trend-total-resign').text(res.summary.total_resign);
                    $('#trend-avg-new').text(res.summary.avg_monthly_new);
                    const ctx = document.getElementById('trendChart');
                    if (trendChart) trendChart.destroy();
                    trendChart = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: res.labels,
                            datasets: res.datasets
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'top'
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true
                                }
                            }
                        }
                    });
                });
            }

            function loadBreakdownChart() {
                $.get("{{ route('office.HR.employee.breakdown') }}", getBreakdownParams(), function(res) {
                    $('#breakdown-total-cats').text(res.summary.total_categories);
                    $('#breakdown-top-cat').text(res.summary.top_category);
                    $('#breakdown-avg-retention').text(res.summary.avg_retention + '%');
                    const ctx = document.getElementById('breakdownChart');
                    if (breakdownChart) breakdownChart.destroy();
                    breakdownChart = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: res.chart.labels,
                            datasets: [{
                                    label: 'Total',
                                    data: res.chart.total,
                                    backgroundColor: 'rgba(13,110,253,0.7)'
                                },
                                {
                                    label: 'Active',
                                    data: res.chart.active,
                                    backgroundColor: 'rgba(25,135,84,0.7)'
                                },
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'top'
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true
                                }
                            }
                        }
                    });
                    const list = $('#breakdown-list').empty();
                    res.breakdown.forEach(item => {
                        list.append(
                            `<div class="breakdown-item"><div class="breakdown-label">${item.label}</div><div class="breakdown-bar"><div class="breakdown-bar-fill active" style="width:${item.retention}%"></div></div><div class="breakdown-value">${item.retention}%</div></div>`
                            );
                    });
                });
            }

            function loadEmployeeTable(page) {
                const params = {
                    category: $('#list-category').val(),
                    ...getFilterParams(),
                    page
                };
                $.get("{{ route('office.HR.employee.category') }}", params, function(res) {
                    const tbody = $('#employee-table-body').empty();
                    if (res.data.length === 0) tbody.append(
                        '<tr><td colspan="6" class="text-center text-muted">Tidak ada data</td></tr>');
                    else res.data.forEach(emp => {
                        tbody.append(
                            `<tr><td><strong>${emp.nama}</strong><br><small class="text-muted">${emp.nama_lengkap}</small></td><td>${emp.nip}</td><td>${emp.jabatan}</td><td>${emp.divisi}</td><td>${emp.tanggal_join}</td><td><span class="badge bg-${emp.status === 'Aktif' ? 'success' : 'secondary'}-subtle text-${emp.status === 'Aktif' ? 'success' : 'secondary'}">${emp.status}</span></td></tr>`
                            );
                    });
                    const pag = $('#employee-pagination').empty();
                    if (res.pagination.last_page > 1) {
                        for (let i = 1; i <= res.pagination.last_page; i++) {
                            pag.append(
                                `<button class="btn btn-sm btn-outline-secondary ${i === res.pagination.current_page ? 'active' : ''}" data-page="${i}">${i}</button>`
                                );
                        }
                        pag.off('click', 'button').on('click', 'button', function() {
                            loadEmployeeTable($(this).data('page'));
                        });
                    }
                });
            }

            function loadEmployeeList(category, page) {
                const listId = `#list-${category}`,
                    paginationId = `#pagination-${category}`,
                    loading = $(`#modal-${category} .loading-overlay`);
                loading.removeClass('hidden');
                $(listId).empty();
                $(paginationId).empty();
                $.get("{{ route('office.HR.employee.category') }}", {
                    ...getFilterParams(),
                    category,
                    page
                }, function(res) {
                    if (res.data.length === 0) $(listId).html(
                        '<li class="employee-item text-muted text-center">Tidak ada data</li>');
                    else res.data.forEach(emp => {
                        const dateDisplay = category === 'resign' && emp.resigned_at ?
                            `<br><small class="text-danger">Resign: ${emp.resigned_at}</small>` :
                            emp.tanggal_join !== '-' ?
                            `<br><small class="text-muted">${emp.tanggal_join}</small>` : '';
                        $(listId).append(
                            `<li class="employee-item"><div class="employee-info"><h6>${emp.nama}</h6><small>${emp.nama_lengkap} • ${emp.nip}</small><small>${emp.jabatan} • ${emp.divisi}</small>${dateDisplay}</div><span class="badge bg-success-subtle text-success">${emp.status}</span></li>`
                            );
                    });
                    if (res.pagination.last_page > 1) {
                        let pagination = '';
                        for (let i = 1; i <= res.pagination.last_page; i++) {
                            const active = i === res.pagination.current_page ? 'active' : '';
                            pagination +=
                                `<button class="btn btn-sm btn-outline-secondary ${active}" data-page="${i}">${i}</button>`;
                        }
                        $(paginationId).html(pagination).off('click', 'button').on('click', 'button',
                            function() {
                                loadEmployeeList(category, $(this).data('page'));
                            });
                    }
                    loading.addClass('hidden');
                });
            }

            function loadRetentionAnalysis() {
                $.get("{{ route('office.HR.employee.data') }}", getFilterParams(), function(res) {
                    if (!res.stats || !res.insights) return;
                    const rate = res.stats.retention_rate,
                        circumference = 2 * Math.PI * 60,
                        offset = circumference - (rate / 100) * circumference;
                    $('#gauge-value').text(rate);
                    $('.retention-gauge .progress').css('stroke-dashoffset', offset);
                    if (rate >= 90) {
                        $('.retention-gauge .progress').css('stroke', '#198754');
                        $('#retention-status').text('Sangat Baik').removeClass().addClass(
                            'badge bg-success-subtle text-success');
                    } else if (rate >= 75) {
                        $('.retention-gauge .progress').css('stroke', '#0d6efd');
                        $('#retention-status').text('Baik').removeClass().addClass(
                            'badge bg-primary-subtle text-primary');
                    } else if (rate >= 60) {
                        $('.retention-gauge .progress').css('stroke', '#ffc107');
                        $('#retention-status').text('Cukup').removeClass().addClass(
                            'badge bg-warning-subtle text-warning');
                    } else {
                        $('.retention-gauge .progress').css('stroke', '#dc3545');
                        $('#retention-status').text('Perlu Perhatian').removeClass().addClass(
                            'badge bg-danger-subtle text-danger');
                    }
                    $('#summary-total').text(res.stats.total_employees);
                    $('#summary-active').text(res.stats.active);
                    $('#summary-resign').text(res.stats.resign);
                    $('#summary-ratio').text(`${res.stats.active} : ${res.stats.resign}`);
                    const oppList = $('#opportunities-list').empty();
                    res.insights.opportunities.forEach(opp => {
                        oppList.append(
                            `<div class="insight-card success"><i class="fa-solid fa-lightbulb me-2"></i>${opp}</div>`
                            );
                    });
                    const recList = $('#recommendations-list').empty();
                    res.insights.recommendations.slice(0, 3).forEach(rec => {
                        recList.append(
                            `<div class="insight-card"><i class="fa-solid fa-check me-2"></i>${rec}</div>`
                            );
                    });
                    const projBody = $('#projections-body').empty();
                    Object.entries(res.insights.projections).forEach(([period, data]) => {
                        const label = period === 'next_quarter' ? 'Kuartal Depan' : 'Tahun Depan';
                        const confBadge = data.confidence === 'high' ? 'bg-success' : (data
                            .confidence === 'medium' ? 'bg-warning text-dark' : 'bg-secondary');
                        projBody.append(
                            `<tr><td><strong>${label}</strong></td><td>${data.estimated_active}</td><td>${data.estimated_resign}</td><td><span class="badge ${confBadge}">${data.confidence}</span></td></tr>`
                            );
                    });
                });
            }
            updateFilterLabel();
        });
    </script>
@endsection
