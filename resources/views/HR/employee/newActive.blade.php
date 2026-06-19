@extends('layout_HR.app')

@section('content_HR')
    <div class="container-xxl flex-grow-1 container-p-y">

        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <div>
                <h1 class="h4 mb-1 fw-bold text-dark">Informasi Karyawan</h1>
                <p class="text-muted mb-0 small">Kelola dan pantau data SDM perusahaan Anda</p>
            </div>
            <p class="text-muted mb-0 small">Terakhir update: <span id="last-update"
                    class="fw-medium">{{ now()->format('d M Y, H:i') }}</span></p>
        </div>

        <div class="card mb-4 shadow-sm border-0">
            <div class="card-body">
                <div class="mb-3">
                    <h6 class="mb-0 fw-bold text-dark"><i class="iconify me-2 text-primary"
                            data-icon="mdi:filter"></i>Filter Data</h6>
                </div>
                <div class="row g-3">
                    <div class="col-md-3 col-sm-6">
                        <label class="form-label small text-muted fw-medium mb-1">Periode Data</label>
                        <select name="periode" id="periode" class="form-select form-select-sm">
                            <option value="all">Tanpa Filter</option>
                            <option value="12">12 Bulan Terakhir</option>
                            <option value="6">6 Bulan Terakhir</option>
                            <option value="3">3 Bulan Terakhir</option>
                            <option value="year">Pilih Tahun</option>
                        </select>
                    </div>
                    <div class="col-md-2 col-sm-6 d-none" id="year-selector">
                        <label class="form-label small text-muted fw-medium mb-1">Tahun</label>
                        <select name="year" id="year" class="form-select form-select-sm">
                            @for ($y = date('Y'); $y >= date('Y') - 5; $y--)
                                <option value="{{ $y }}">{{ $y }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-md-4 col-sm-12">
                        <label class="form-label small text-muted fw-medium mb-1">&nbsp;</label>
                        <div class="input-group input-group-sm">
                            <input type="text" id="search-employee" class="form-control form-control-sm"
                                placeholder="Cari nama, NIP, jabatan...">
                            <button class="btn btn-primary btn-sm" type="button" id="btn-search">
                                <i class="iconify" data-icon="mdi:magnify"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-12 d-flex align-items-end">
                        <button class="btn btn-light btn-sm w-100 border" id="btn-reset-filter">
                            <i class="iconify me-1" data-icon="mdi:refresh"></i>Reset Filter
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-12">
                <span class="badge bg-light text-dark border" id="active-filter-badge">
                    <i class="iconify me-1" data-icon="mdi:filter"></i>
                    <span id="filter-label">Menampilkan: Semua Data</span>
                </span>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="card h-100 border-0 shadow-sm" data-modal="modal-active"
                    style="cursor:pointer;transition:transform 0.2s">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col">
                                <div class="text-xs fw-medium text-primary text-uppercase mb-1">Karyawan Active</div>
                                <div class="h4 mb-0 fw-bold text-dark" id="stat-active">-</div>
                            </div>
                            <div class="col-auto">
                                <div
                                    class="avatar avatar-sm bg-primary-subtle rounded-circle d-flex align-items-center justify-content-center">
                                    <i class="iconify text-primary" data-icon="mdi:account" data-width="20"
                                        data-height="20"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card h-100 border-0 shadow-sm" data-modal="modal-new"
                    style="cursor:pointer;transition:transform 0.2s">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col">
                                <div class="text-xs fw-medium text-success text-uppercase mb-1">Karyawan Baru</div>
                                <div class="h4 mb-0 fw-bold text-dark" id="stat-new">-</div>
                            </div>
                            <div class="col-auto">
                                <div
                                    class="avatar avatar-sm bg-success-subtle rounded-circle d-flex align-items-center justify-content-center">
                                    <i class="iconify text-success" data-icon="mdi:account-plus" data-width="20"
                                        data-height="20"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card h-100 border-0 shadow-sm" data-modal="modal-resign"
                    style="cursor:pointer;transition:transform 0.2s">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col">
                                <div class="text-xs fw-medium text-secondary text-uppercase mb-1">Karyawan Resign</div>
                                <div class="h4 mb-0 fw-bold text-dark" id="stat-resign">-</div>
                            </div>
                            <div class="col-auto">
                                <div
                                    class="avatar avatar-sm bg-secondary-subtle rounded-circle d-flex align-items-center justify-content-center">
                                    <i class="iconify text-secondary" data-icon="mdi:account-remove" data-width="20"
                                        data-height="20"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card h-100 border-0 shadow-sm" data-modal="modal-retention"
                    style="cursor:pointer;transition:transform 0.2s">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col">
                                <div class="text-xs fw-medium text-info text-uppercase mb-1">Tingkat Retensi</div>
                                <div class="h4 mb-0 fw-bold text-dark"><span id="stat-retention">-</span><small
                                        class="fs-6">%</small></div>
                            </div>
                            <div class="col-auto">
                                <div
                                    class="avatar avatar-sm bg-info-subtle rounded-circle d-flex align-items-center justify-content-center">
                                    <i class="iconify text-info" data-icon="mdi:chart-line" data-width="20"
                                        data-height="20"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4 shadow-sm border-0">
            <div class="card-body">
                <div class="d-flex flex-wrap align-items-center justify-content-between mb-3">
                    <h6 class="mb-0 fw-bold text-dark"><i class="iconify me-2 text-primary"
                            data-icon="mdi:chart-timeline-variant"></i>Headcount Trend</h6>
                    <div>
                        <button class="btn btn-light btn-sm me-1 border" id="btn-export-trend-csv">
                            <i class="iconify me-1" data-icon="mdi:file-csv"></i>CSV
                        </button>
                        <button class="btn btn-light btn-sm border" id="btn-export-trend-pdf">
                            <i class="iconify me-1" data-icon="mdi:file-pdf"></i>PDF
                        </button>
                    </div>
                </div>
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <label class="form-label small text-muted fw-medium">Tanggal Mulai</label>
                        <input type="date" id="trend-start-date" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small text-muted fw-medium">Tanggal Akhir</label>
                        <input type="date" id="trend-end-date" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small text-muted fw-medium">Group By</label>
                        <select id="trend-group-by" class="form-select form-select-sm">
                            <option value="month">Bulanan</option>
                            <option value="quarter">Triwulan</option>
                            <option value="year">Tahunan</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button class="btn btn-primary btn-sm w-100" id="btn-apply-trend-filter">Terapkan</button>
                    </div>
                </div>
                <div class="row g-3 mb-4">
                    <div class="col-6 col-md-3">
                        <div class="p-3 bg-light-subtle rounded text-center border">
                            <div class="h5 mb-0 fw-bold text-primary" id="trend-total-active">-</div>
                            <small class="text-muted">Total Active</small>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="p-3 bg-light-subtle rounded text-center border">
                            <div class="h5 mb-0 fw-bold text-success" id="trend-total-new">-</div>
                            <small class="text-muted">Total New</small>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="p-3 bg-light-subtle rounded text-center border">
                            <div class="h5 mb-0 fw-bold text-secondary" id="trend-total-resign">-</div>
                            <small class="text-muted">Total Resign</small>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="p-3 bg-light-subtle rounded text-center border">
                            <div class="h5 mb-0 fw-bold text-info" id="trend-avg-new">-</div>
                            <small class="text-muted">Avg New/Bulan</small>
                        </div>
                    </div>
                </div>
                <div class="position-relative" style="height:300px">
                    <canvas id="trendChart"></canvas>
                </div>
            </div>
        </div>

        <div class="card mb-4 shadow-sm border-0">
            <div class="card-body">
                <div class="d-flex flex-wrap align-items-center justify-content-between mb-3">
                    <h6 class="mb-0 fw-bold text-dark"><i class="iconify me-2 text-primary"
                            data-icon="mdi:chart-pie"></i>Headcount Breakdown</h6>
                    <div>
                        <button class="btn btn-light btn-sm me-1 border" id="btn-export-breakdown-csv">
                            <i class="iconify me-1" data-icon="mdi:file-csv"></i>CSV
                        </button>
                        <button class="btn btn-light btn-sm border" id="btn-export-breakdown-pdf">
                            <i class="iconify me-1" data-icon="mdi:file-pdf"></i>PDF
                        </button>
                    </div>
                </div>
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <label class="form-label small text-muted fw-medium">Filter By</label>
                        <select id="breakdown-filter-by" class="form-select form-select-sm">
                            <option value="divisi">Divisi</option>
                            <option value="jabatan">Jabatan</option>
                            <option value="gender">Gender</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small text-muted fw-medium">Status</label>
                        <select id="breakdown-status" class="form-select form-select-sm">
                            <option value="all">Semua</option>
                            <option value="active">Active</option>
                            <option value="resign">Resign</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small text-muted fw-medium">Min. Masa Kerja (bulan)</label>
                        <input type="number" id="breakdown-min-tenure" class="form-control form-control-sm"
                            value="0" min="0">
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button class="btn btn-primary btn-sm w-100" id="btn-apply-breakdown-filter">Terapkan</button>
                    </div>
                </div>
                <div class="row g-3 mb-4">
                    <div class="col-4">
                        <div class="p-3 bg-light-subtle rounded text-center border">
                            <div class="h5 mb-0 fw-bold" id="breakdown-total-cats">-</div>
                            <small class="text-muted">Kategori</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="p-3 bg-light-subtle rounded text-center border">
                            <div class="h5 mb-0 fw-bold" id="breakdown-top-cat">-</div>
                            <small class="text-muted">Top Kategori</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="p-3 bg-light-subtle rounded text-center border">
                            <div class="h5 mb-0 fw-bold" id="breakdown-avg-retention">-</div>
                            <small class="text-muted">Avg Retensi</small>
                        </div>
                    </div>
                </div>
                <div class="position-relative" style="height:300px">
                    <canvas id="breakdownChart"></canvas>
                </div>
                <div id="breakdown-list" class="mt-3"></div>
            </div>
        </div>

        <div class="card mb-4 shadow-sm border-0">
            <div class="card-body">
                <div class="mb-3">
                    <h6 class="mb-0 fw-bold text-dark"><i class="iconify me-2 text-primary"
                            data-icon="mdi:account-group"></i>Employee List</h6>
                </div>
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label class="form-label small text-muted fw-medium">Kategori</label>
                        <select id="list-category" class="form-select form-select-sm">
                            <option value="all">Semua</option>
                            <option value="active">Active</option>
                            <option value="new">Baru</option>
                            <option value="resign">Resign</option>
                        </select>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button class="btn btn-primary btn-sm w-100" id="btn-load-employees">Load Data</button>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle" id="employee-table">
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
                <div class="d-flex justify-content-center gap-1 flex-wrap mt-3" id="employee-pagination"></div>
            </div>
        </div>

    </div>

    <div class="modal fade" id="modal-active" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold">Daftar Karyawan Active</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body pt-0">
                    <ul class="list-group list-group-flush" id="list-active"></ul>
                    <div class="d-flex justify-content-center gap-1 flex-wrap mt-3" id="pagination-active"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modal-new" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold">Daftar Karyawan Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body pt-0">
                    <ul class="list-group list-group-flush" id="list-new"></ul>
                    <div class="d-flex justify-content-center gap-1 flex-wrap mt-3" id="pagination-new"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modal-resign" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold">Daftar Karyawan Resign</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body pt-0">
                    <ul class="list-group list-group-flush" id="list-resign"></ul>
                    <div class="d-flex justify-content-center gap-1 flex-wrap mt-3" id="pagination-resign"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modal-retention" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold">Analisis Tingkat Retensi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body pt-0">
                    <div class="row">
                        <div class="col-md-4 text-center">
                            <div class="position-relative mx-auto mb-4" style="width:140px;height:140px">
                                <svg width="140" height="140" class="position-relative"
                                    style="transform:rotate(-90deg)">
                                    <circle cx="70" cy="70" r="55" fill="none" stroke="#e9ecef"
                                        stroke-width="10"></circle>
                                    <circle class="progress-ring" cx="70" cy="70" r="55" fill="none"
                                        stroke="#0d6efd" stroke-width="10" stroke-linecap="round"
                                        stroke-dasharray="345.575" stroke-dashoffset="345.575"
                                        style="transition:stroke-dashoffset 0.6s ease"></circle>
                                </svg>
                                <div class="position-absolute top-50 start-50 translate-middle fs-4 fw-bold">
                                    <span id="gauge-value">0</span><small class="fs-6">%</small>
                                </div>
                            </div>
                            <span class="badge bg-success-subtle text-success" id="retention-status">Baik</span>
                        </div>
                        <div class="col-md-8">
                            <h6 class="mb-3 fw-bold">Ringkasan Statistik</h6>
                            <table class="table table-sm table-borderless mb-4">
                                <tr>
                                    <td><strong>Total Karyawan</strong></td>
                                    <td id="summary-total" class="fw-bold">-</td>
                                </tr>
                                <tr>
                                    <td><strong>Karyawan Active</strong></td>
                                    <td id="summary-active" class="fw-bold">-</td>
                                </tr>
                                <tr>
                                    <td><strong>Karyawan Resign</strong></td>
                                    <td id="summary-resign" class="fw-bold">-</td>
                                </tr>
                                <tr>
                                    <td><strong>Rasio Retensi</strong></td>
                                    <td id="summary-ratio" class="fw-bold">-</td>
                                </tr>
                            </table>
                            <h6 class="mb-3 fw-bold">Peluang Peningkatan</h6>
                            <div id="opportunities-list"></div>
                            <h6 class="mb-3 mt-4 fw-bold">Rekomendasi</h6>
                            <div id="recommendations-list"></div>
                        </div>
                    </div>
                    <h6 class="mb-3 mt-4 fw-bold">Proyeksi</h6>
                    <table class="table table-sm table-bordered">
                        <thead class="table-light">
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

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

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

            $('.card[data-modal]').on('click', function() {
                const modalId = $(this).data('modal'),
                    category = modalId.replace('modal-', '');
                if (category === 'retention') loadRetentionAnalysis();
                else loadEmployeeList(category, 1);
                new bootstrap.Modal(document.getElementById(modalId)).show();
            });

            $('#periode, #year, #btn-search').on('change click', function() {
                updateFilterLabel();
                loadStats();
            });
            $('#search-employee').on('keypress', function(e) {
                if (e.which === 13) {
                    updateFilterLabel();
                    loadStats();
                }
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
                window.location = "{{ route('HR.employee.trend.export.csv') }}?" + $.param(
            getTrendParams());
            });
            $('#btn-export-trend-pdf').on('click', function() {
                window.location = "{{ route('HR.employee.trend.export.pdf') }}?" + $.param(
            getTrendParams());
            });

            $('#btn-apply-breakdown-filter').on('click', loadBreakdownChart);
            $('#btn-export-breakdown-csv').on('click', function() {
                window.location = "{{ route('HR.employee.breakdown.export.csv') }}?" + $.param(
                    getBreakdownParams());
            });
            $('#btn-export-breakdown-pdf').on('click', function() {
                window.location = "{{ route('HR.employee.breakdown.export.pdf') }}?" + $.param(
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
                }
            }

            function getTrendParams() {
                return {
                    start_date: $('#trend-start-date').val(),
                    end_date: $('#trend-end-date').val(),
                    group_by: $('#trend-group-by').val()
                }
            }

            function getBreakdownParams() {
                return {
                    filter_by: $('#breakdown-filter-by').val(),
                    status: $('#breakdown-status').val(),
                    min_tenure: $('#breakdown-min-tenure').val()
                }
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
                $.get("{{ route('HR.employee.data') }}", getFilterParams(), function(res) {
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
                $.get("{{ route('HR.employee.trend') }}", getTrendParams(), function(res) {
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
                            datasets: res.datasets.map(d => ({
                                ...d,
                                tension: 0.4,
                                fill: false,
                                borderWidth: 2,
                                pointRadius: 3,
                                pointHoverRadius: 5
                            }))
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'top',
                                    labels: {
                                        font: {
                                            size: 11,
                                            family: '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto'
                                        },
                                        padding: 15
                                    }
                                },
                                tooltip: {
                                    backgroundColor: 'rgba(33, 37, 41, 0.9)',
                                    titleFont: {
                                        size: 13
                                    },
                                    bodyFont: {
                                        size: 12
                                    },
                                    padding: 12,
                                    cornerRadius: 8
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    grid: {
                                        color: 'rgba(0,0,0,0.05)'
                                    },
                                    ticks: {
                                        font: {
                                            size: 10,
                                            family: '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto'
                                        }
                                    }
                                },
                                x: {
                                    grid: {
                                        display: false
                                    },
                                    ticks: {
                                        font: {
                                            size: 10,
                                            family: '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto'
                                        }
                                    }
                                }
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
                    const ctx = document.getElementById('breakdownChart');
                    if (breakdownChart) breakdownChart.destroy();
                    breakdownChart = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: res.chart.labels,
                            datasets: [{
                                label: 'Total',
                                data: res.chart.total,
                                backgroundColor: 'rgba(13, 110, 253, 0.85)',
                                borderRadius: 4,
                                borderSkipped: false
                            }, {
                                label: 'Active',
                                data: res.chart.active,
                                backgroundColor: 'rgba(25, 135, 84, 0.85)',
                                borderRadius: 4,
                                borderSkipped: false
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'top',
                                    labels: {
                                        font: {
                                            size: 11,
                                            family: '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto'
                                        },
                                        padding: 15
                                    }
                                },
                                tooltip: {
                                    backgroundColor: 'rgba(33, 37, 41, 0.9)',
                                    titleFont: {
                                        size: 13
                                    },
                                    bodyFont: {
                                        size: 12
                                    },
                                    padding: 12,
                                    cornerRadius: 8
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    grid: {
                                        color: 'rgba(0,0,0,0.05)'
                                    },
                                    ticks: {
                                        font: {
                                            size: 10,
                                            family: '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto'
                                        }
                                    }
                                },
                                x: {
                                    grid: {
                                        display: false
                                    },
                                    ticks: {
                                        font: {
                                            size: 10,
                                            family: '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto'
                                        },
                                        maxRotation: 45,
                                        minRotation: 45
                                    }
                                }
                            }
                        }
                    });
                    const list = $('#breakdown-list').empty();
                    res.breakdown.forEach(item => {
                        list.append(
                            `<div class="d-flex align-items-center py-2 border-bottom"><div class="fw-medium text-dark" style="min-width:120px">${item.label}</div><div class="flex-grow-1 mx-3" style="height:8px;background:#e9ecef;border-radius:4px;overflow:hidden"><div style="height:100%;background:#0d6efd;border-radius:4px;transition:width 0.4s ease;width:${item.retention}%"></div></div><div class="fw-bold text-dark" style="min-width:50px;text-align:right">${item.retention}%</div></div>`
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
                $.get("{{ route('HR.employee.category') }}", params, function(res) {
                    const tbody = $('#employee-table-body').empty();
                    if (res.data.length === 0) {
                        tbody.append(
                            '<tr><td colspan="6" class="text-center text-muted py-5"><img src="https://undraw.co/api/illustrations/random?seed=empty" alt="No data" class="mb-3" style="max-width:120px;opacity:0.6"><br>Tidak ada data yang ditemukan</td></tr>'
                        );
                    } else {
                        res.data.forEach(emp => {
                            tbody.append(
                                `<tr><td><strong class="text-dark">${emp.nama}</strong><br><small class="text-muted">${emp.nama_lengkap}</small></td><td class="text-muted">${emp.nip}</td><td>${emp.jabatan}</td><td>${emp.divisi}</td><td>${emp.tanggal_join}</td><td><span class="badge bg-${emp.status==='Aktif'?'success':'secondary'}-subtle text-${emp.status==='Aktif'?'success':'secondary'} border">${emp.status}</span></td></tr>`
                            );
                        });
                    }
                    const pag = $('#employee-pagination').empty();
                    if (res.pagination.last_page > 1) {
                        for (let i = 1; i <= res.pagination.last_page; i++) {
                            pag.append(
                                `<button class="btn btn-sm ${i===res.pagination.current_page?'btn-primary':'btn-light border'}" data-page="${i}">${i}</button>`
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
                    paginationId = `#pagination-${category}`;
                $(listId).empty();
                $(paginationId).empty();
                $.get("{{ route('HR.employee.category') }}", {
                    ...getFilterParams(),
                    category,
                    page
                }, function(res) {
                    if (res.data.length === 0) {
                        $(listId).html(
                            '<li class="list-group-item text-center text-muted py-4"><img src="https://undraw.co/api/illustrations/random?seed=nodata" alt="No data" class="mb-3" style="max-width:100px;opacity:0.5"><br>Tidak ada data</li>'
                        );
                    } else {
                        res.data.forEach(emp => {
                            const dateDisplay = category === 'resign' && emp.resigned_at ?
                                `<br><small class="text-danger">Resign: ${emp.resigned_at}</small>` :
                                emp.tanggal_join !== '-' ?
                                `<br><small class="text-muted">${emp.tanggal_join}</small>` : '';
                            $(listId).append(
                                `<li class="list-group-item d-flex justify-content-between align-items-center py-3"><div><strong class="text-dark">${emp.nama}</strong><br><small class="text-muted">${emp.nama_lengkap} • ${emp.nip}</small><br><small>${emp.jabatan} • ${emp.divisi}</small>${dateDisplay}</div><span class="badge bg-success-subtle text-success border">${emp.status}</span></li>`
                            );
                        });
                    }
                    if (res.pagination.last_page > 1) {
                        let pagination = '';
                        for (let i = 1; i <= res.pagination.last_page; i++) {
                            const active = i === res.pagination.current_page ? 'btn-primary' :
                                'btn-light border';
                            pagination +=
                                `<button class="btn btn-sm ${active}" data-page="${i}">${i}</button>`;
                        }
                        $(paginationId).html(pagination).off('click', 'button').on('click', 'button',
                            function() {
                                loadEmployeeList(category, $(this).data('page'));
                            });
                    }
                });
            }

            function loadRetentionAnalysis() {
                $.get("{{ route('HR.employee.data') }}", getFilterParams(), function(res) {
                    if (!res.stats || !res.insights) return;
                    const rate = res.stats.retention_rate,
                        circumference = 2 * Math.PI * 55,
                        offset = circumference - (rate / 100) * circumference;
                    $('#gauge-value').text(rate);
                    $('.progress-ring').css('stroke-dashoffset', offset);
                    if (rate >= 90) {
                        $('.progress-ring').css('stroke', '#198754');
                        $('#retention-status').text('Sangat Baik').removeClass().addClass(
                            'badge bg-success-subtle text-success border');
                    } else if (rate >= 75) {
                        $('.progress-ring').css('stroke', '#0d6efd');
                        $('#retention-status').text('Baik').removeClass().addClass(
                            'badge bg-primary-subtle text-primary border');
                    } else if (rate >= 60) {
                        $('.progress-ring').css('stroke', '#ffc107');
                        $('#retention-status').text('Cukup').removeClass().addClass(
                            'badge bg-warning-subtle text-warning border');
                    } else {
                        $('.progress-ring').css('stroke', '#6c757d');
                        $('#retention-status').text('Perlu Perhatian').removeClass().addClass(
                            'badge bg-secondary-subtle text-secondary border');
                    }
                    $('#summary-total').text(res.stats.total_employees);
                    $('#summary-active').text(res.stats.active);
                    $('#summary-resign').text(res.stats.resign);
                    $('#summary-ratio').text(`${res.stats.active} : ${res.stats.resign}`);
                    const oppList = $('#opportunities-list').empty();
                    res.insights.opportunities.forEach(opp => {
                        oppList.append(
                            `<div class="alert alert-success py-2 mb-2 border-0"><i class="iconify me-2" data-icon="mdi:lightbulb-on"></i>${opp}</div>`
                        );
                    });
                    const recList = $('#recommendations-list').empty();
                    res.insights.recommendations.slice(0, 3).forEach(rec => {
                        recList.append(
                            `<div class="alert alert-light py-2 mb-2 border"><i class="iconify me-2 text-primary" data-icon="mdi:check-circle"></i>${rec}</div>`
                        );
                    });
                    const projBody = $('#projections-body').empty();
                    Object.entries(res.insights.projections).forEach(([period, data]) => {
                        const label = period === 'next_quarter' ? 'Kuartal Depan' : 'Tahun Depan';
                        const confBadge = data.confidence === 'high' ?
                            'bg-success-subtle text-success border' : (data.confidence ===
                                'medium' ?
                                'bg-warning-subtle text-warning border' :
                                'bg-secondary-subtle text-secondary border');
                        projBody.append(
                            `<tr><td><strong>${label}</strong></td><td class="fw-bold">${data.estimated_active}</td><td class="fw-bold">${data.estimated_resign}</td><td><span class="badge ${confBadge}">${data.confidence}</span></td></tr>`
                        );
                    });
                });
            }
            updateFilterLabel();
        });
    </script>
@endsection
