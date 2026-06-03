@extends('layout_HR.app')
@section('content_HR')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary: #4e73df;
            --success: #1cc88a;
            --info: #36b9cc;
            --warning: #f6c23e;
            --danger: #e74a3b;
            --secondary: #858796;
            --light: #f8f9fc;
            --dark: #5a5c69;
            --border: #e3e6f0;
        }

        body {
            background-color: #fafbfc;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }

        .card {
            border: none;
            border-radius: 0.5rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            margin-bottom: 1rem;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
        }

        .card-header {
            background-color: transparent;
            border-bottom: 1px solid var(--border);
            padding: 1rem 1.25rem;
            font-weight: 600;
            color: var(--dark);
        }

        .card-body {
            padding: 1.25rem;
        }

        .form-control,
        .form-select {
            font-size: 0.875rem;
            padding: 0.5rem 0.75rem;
            border: 1px solid var(--border);
            border-radius: 0.375rem;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 0.25rem rgba(78, 115, 223, 0.15);
        }

        .btn {
            font-size: 0.875rem;
            padding: 0.5rem 1rem;
            border-radius: 0.375rem;
            font-weight: 500;
        }

        .btn-sm {
            padding: 0.375rem 0.75rem;
            font-size: 0.8125rem;
        }

        .btn-primary {
            background-color: var(--primary);
            border-color: var(--primary);
        }

        .btn-primary:hover {
            background-color: #3a5fd7;
            border-color: #3a5fd7;
        }

        .summary-card {
            border: 1px solid var(--border);
            border-radius: 0.5rem;
            cursor: default;
            transition: transform 0.2s;
        }

        .summary-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 0.25rem 0.5rem rgba(0, 0, 0, 0.075);
        }

        .stat-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 0.25rem;
            line-height: 1.2;
        }

        .stat-value.text-success {
            color: var(--success) !important;
        }

        .stat-value.text-warning {
            color: var(--warning) !important;
        }

        .stat-value.text-info {
            color: var(--info) !important;
        }

        .stat-label {
            font-size: 0.75rem;
            color: var(--secondary);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .table-payroll {
            font-size: 0.875rem;
            color: var(--dark);
        }

        .table-payroll thead th {
            background-color: var(--light);
            border-bottom: 2px solid var(--border);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            color: var(--secondary);
            letter-spacing: 0.5px;
            padding: 0.75rem 1rem;
        }

        .table-payroll tbody td {
            padding: 0.75rem 1rem;
            border-bottom: 1px solid var(--border);
            vertical-align: middle;
        }

        .table-payroll tbody tr:hover {
            background-color: rgba(78, 115, 223, 0.04);
        }

        .status-badge {
            font-size: 0.7rem;
            font-weight: 600;
            padding: 0.35em 0.65em;
            border-radius: 1rem;
            text-transform: uppercase;
        }

        .status-done {
            background-color: #dcfce7 !important;
            color: #16a34a !important;
        }

        .status-pending {
            background-color: #fef3c7 !important;
            color: #d97706 !important;
        }

        .pagination-custom {
            display: flex;
            gap: 0.25rem;
            flex-wrap: wrap;
        }

        .pagination-custom button {
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
            border-radius: 0.25rem;
            border: 1px solid var(--border);
            background: #fff;
            color: var(--dark);
            cursor: pointer;
            transition: all 0.15s;
        }

        .pagination-custom button:hover {
            background: var(--primary);
            color: #fff;
            border-color: var(--primary);
        }

        .pagination-custom button.active {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }

        .chart-card {
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 0.5rem;
            padding: 1rem;
            margin-bottom: 1rem;
        }

        .chart-card h6 {
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 0.75rem;
        }

        .chart-container {
            position: relative;
            height: 280px;
            width: 100%;
        }

        .modal-content {
            border: none;
            border-radius: 0.5rem;
            box-shadow: 0 0.5rem 2rem rgba(58, 59, 69, 0.2);
        }

        .modal-header {
            border-bottom: 1px solid var(--border);
            padding: 1rem 1.5rem;
        }

        .modal-title {
            font-weight: 600;
            color: var(--dark);
        }

        .modal-body {
            padding: 1.5rem;
        }

        .loading-overlay {
            position: absolute;
            inset: 0;
            background: rgba(255, 255, 255, 0.95);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10;
            border-radius: 0.5rem;
        }

        .loading-overlay.hidden {
            display: none;
        }

        .divider {
            height: 1px;
            background: var(--border);
            margin: 1.5rem 0;
        }

        .empty-state {
            text-align: center;
            padding: 2rem;
            color: var(--secondary);
        }

        .empty-state img {
            max-width: 150px;
            margin-bottom: 1rem;
            opacity: 0.8;
        }

        .page-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 0.25rem;
        }

        .page-subtitle {
            font-size: 0.9rem;
            color: var(--secondary);
        }

        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        ::-webkit-scrollbar-track {
            background: transparent;
        }

        ::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 3px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #a1a1c1;
        }

        @media (max-width: 767px) {
            .card-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.75rem;
            }

            .card-header .d-flex {
                width: 100%;
                flex-wrap: wrap;
                gap: 0.5rem;
            }

            .chart-container {
                height: 240px;
            }

            .stat-value {
                font-size: 1.3rem;
            }
        }
    </style>

    <div class="container-fluid px-4 py-4">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <div>
                <h1 class="page-title">Payroll & Tunjangan</h1>
                <p class="page-subtitle mb-0">Kelola dan pantau data penggajian karyawan</p>
            </div>
            <p class="text-muted mb-0 small">Periode: <span id="periodLabel" class="fw-semibold">-</span></p>
        </div>

        <div class="card mb-4 position-relative">
            <div class="card-body">
                <div class="mb-3 d-flex flex-wrap align-items-center justify-content-between">
                    <h6 class="m-0 fw-bold text-primary"><i class="fa-solid fa-filter me-2"></i>Filter & Export</h6>
                    <div class="d-flex gap-2 flex-wrap">
                        <select id="filterBulan" class="form-select form-select-sm" style="width:130px"></select>
                        <select id="filterTahun" class="form-select form-select-sm" style="width:90px"></select>
                        <button id="btnFilter" class="btn btn-primary btn-sm">
                            <i class="fa-solid fa-filter me-1"></i>Filter
                        </button>
                        <button id="exportCsv" class="btn btn-success btn-sm">
                            <i class="fa-solid fa-file-csv me-1"></i>CSV
                        </button>
                        <button id="exportPdf" class="btn btn-danger btn-sm">
                            <i class="fa-solid fa-file-pdf me-1"></i>PDF
                        </button>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-3 col-6">
                        <div class="summary-card text-center p-3 h-100">
                            <div class="stat-value" id="sumTotal">0</div>
                            <div class="stat-label">Total Karyawan</div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="summary-card text-center p-3 h-100">
                            <div class="stat-value text-success" id="sumDone">0</div>
                            <div class="stat-label">Sudah Dihitung</div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="summary-card text-center p-3 h-100">
                            <div class="stat-value text-warning" id="sumPending">0</div>
                            <div class="stat-label">Belum Dihitung</div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="summary-card text-center p-3 h-100">
                            <div class="stat-value text-primary" id="sumGross">Rp 0</div>
                            <div class="stat-label">Total Payroll</div>
                        </div>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-4 col-6">
                        <div class="summary-card text-center p-3 h-100">
                            <div class="stat-value" id="sumAvg">Rp 0</div>
                            <div class="stat-label">Rata-rata Gaji Bersih</div>
                        </div>
                    </div>
                    <div class="col-md-4 col-6">
                        <div class="summary-card text-center p-3 h-100">
                            <div class="stat-value" id="sumMedian">Rp 0</div>
                            <div class="stat-label">Median Gaji</div>
                        </div>
                    </div>
                    <div class="col-md-4 col-6">
                        <div class="summary-card text-center p-3 h-100">
                            <div class="stat-value text-info" id="sumAllowance">Rp 0</div>
                            <div class="stat-label">Total Tunjangan</div>
                        </div>
                    </div>
                </div>

                <div class="input-group input-group-sm mb-4">
                    <span class="input-group-text bg-light border-end-0">
                        <i class="fa-solid fa-search text-muted"></i>
                    </span>
                    <input type="text" id="searchPayroll" class="form-control form-control-sm border-start-0"
                        placeholder="Cari nama, kode, divisi, jabatan...">
                </div>

                <div class="row g-4 mb-4">
                    <div class="col-md-6">
                        <div class="chart-card">
                            <h6 class="mb-3"><i class="fa-solid fa-chart-pie me-2 text-primary"></i>Distribusi Gaji Bersih
                            </h6>
                            <div class="chart-container">
                                <canvas id="salaryRangeChart"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="chart-card">
                            <h6 class="mb-3"><i class="fa-solid fa-chart-bar me-2 text-success"></i>Tunjangan per Divisi
                                (Top 8)</h6>
                            <div class="chart-container">
                                <canvas id="allowanceChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-4 mb-4">
                    <div class="col-md-6">
                        <div class="chart-card">
                            <h6 class="mb-3"><i class="fa-solid fa-chart-column me-2 text-danger"></i>Potongan Terbanyak
                                (Top 8)</h6>
                            <div class="chart-container">
                                <canvas id="deductionChart"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="chart-card">
                            <h6 class="mb-3"><i class="fa-solid fa-chart-line me-2 text-info"></i>Trend Payroll Bulanan
                                (<span id="trendYear"></span>)</h6>
                            <div class="chart-container" style="height:220px">
                                <canvas id="trendChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="divider"></div>

                <h6 class="mb-3 fw-bold text-dark"><i class="fa-solid fa-users me-2 text-primary"></i>Detail Karyawan</h6>
                <div class="table-responsive">
                    <table class="table table-payroll table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama / Kode</th>
                                <th>Divisi / Jabatan</th>
                                <th class="text-end">Gaji Pokok</th>
                                <th class="text-end">Tunjangan</th>
                                <th class="text-end">Potongan</th>
                                <th class="text-end">Gaji Bersih</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="payrollBody"></tbody>
                    </table>
                </div>

                <div class="d-flex flex-wrap justify-content-between align-items-center mt-4 pt-3 border-top">
                    <small class="text-muted">
                        Menampilkan <span id="showingStart" class="fw-semibold">0</span>-<span id="showingEnd"
                            class="fw-semibold">0</span>
                        dari <span id="totalItems" class="fw-semibold">0</span> data
                    </small>
                    <div class="pagination-custom mt-2 mt-md-0" id="paginationContainer"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalDetail" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold"><i class="fa-solid fa-receipt me-2 text-primary"></i>Detail Payroll
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-4 pb-3 border-bottom">
                        <div class="col-md-6 mb-2">
                            <small class="text-muted d-block">Nama</small>
                            <strong id="modalNama" class="d-block"></strong>
                        </div>
                        <div class="col-md-6 mb-2">
                            <small class="text-muted d-block">Kode</small>
                            <span id="modalKode" class="d-block"></span>
                        </div>
                        <div class="col-md-6 mb-2">
                            <small class="text-muted d-block">Divisi</small>
                            <span id="modalDivisi" class="d-block"></span>
                        </div>
                        <div class="col-md-6 mb-2">
                            <small class="text-muted d-block">Jabatan</small>
                            <span id="modalJabatan" class="d-block"></span>
                        </div>
                    </div>
                    <h6 class="mb-3 fw-bold text-dark">Rincian Komponen</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Komponen</th>
                                    <th>Tipe</th>
                                    <th>Keterangan</th>
                                    <th class="text-end">Nilai</th>
                                </tr>
                            </thead>
                            <tbody id="modalDetails"></tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <th colspan="3" class="text-end">Total Bersih</th>
                                    <th class="text-end text-primary fs-5" id="modalNet"></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <div class="loading-overlay hidden" id="mainLoading">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        const formatIDR = (num) => {
            const value = typeof num === 'number' ? num : 0;
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                maximumFractionDigits: 0
            }).format(value);
        };
        const formatSigned = (num) => {
            const value = typeof num === 'number' ? num : 0;
            return (value < 0 ? '-' : '') + formatIDR(Math.abs(value));
        };
        let salaryChart = null,
            allowanceChart = null,
            trendChart = null,
            deductionChart = null;
        let currentPage = 1,
            allData = [],
            searchQuery = '';

        $(document).ready(function() {
            for (let i = 1; i <= 12; i++) {
                const monthName = new Date(2000, i - 1).toLocaleString('id-ID', {
                    month: 'long'
                });
                $('#filterBulan').append(`<option value="${String(i).padStart(2,'0')}">${monthName}</option>`);
            }
            const currentYear = new Date().getFullYear();
            for (let i = currentYear; i >= 2023; i--) {
                $('#filterTahun').append(`<option value="${i}">${i}</option>`);
            }
            $('#filterBulan').val(String(new Date().getMonth() + 1).padStart(2, '0'));
            $('#trendYear').text(currentYear);

            window.loadData = function(page = 1) {
                currentPage = page;
                $('#mainLoading').removeClass('hidden');
                const params = {
                    month: $('#filterBulan').val(),
                    year: $('#filterTahun').val(),
                    search: searchQuery,
                    page: page
                };
                $.get("{{ route('HR.payroll.dashboard') }}", params, function(res) {
                    if (!res.success) {
                        alert(res.message);
                        $('#mainLoading').addClass('hidden');
                        return;
                    }
                    allData = res.data;
                    $('#periodLabel').text(res.period.display);
                    $('#sumTotal').text(res.summary.total_karyawan);
                    $('#sumDone').text(res.summary.sudah_dihitung);
                    $('#sumPending').text(res.summary.belum_dihitung);
                    $('#sumGross').text(formatIDR(res.summary.total_gaji_bersih));
                    $('#sumAvg').text(formatIDR(res.summary.avg_gaji_bersih));
                    $('#sumMedian').text(formatIDR(res.summary.median_gaji_bersih));
                    $('#sumAllowance').text(formatIDR(res.summary.total_tunjangan));
                    renderChartsSafe(res.charts);
                    renderTable(res);
                    $('#mainLoading').addClass('hidden');
                }).fail(function(xhr, status, error) {
                    console.error('Error:', error);
                    alert('Gagal memuat data: ' + error);
                    $('#mainLoading').addClass('hidden');
                });
            };

            function renderCharts(charts) {
                ['salaryRangeChart', 'allowanceChart', 'trendChart', 'deductionChart'].forEach(id => {
                    const existing = Chart.getChart(id);
                    if (existing) existing.destroy();
                });
                const ctx1 = document.getElementById('salaryRangeChart');
                if (ctx1 && charts?.salary_ranges?.labels?.length > 0) {
                    salaryChart = new Chart(ctx1, {
                        type: 'doughnut',
                        data: {
                            labels: charts.salary_ranges.labels.filter(l => l),
                            datasets: [{
                                data: charts.salary_ranges.counts.map(c => c || 0),
                                backgroundColor: ['#4e73df', '#1cc88a', '#f6c23e', '#e74a3b',
                                    '#858796'
                                ],
                                borderWidth: 0
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            cutout: '70%',
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: {
                                        boxWidth: 12,
                                        font: {
                                            size: 10
                                        },
                                        padding: 10,
                                        usePointStyle: true
                                    }
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            const label = context.label || '';
                                            const value = context.parsed || 0;
                                            const total = context.dataset.data.reduce((a, b) => a + b,
                                                0);
                                            const percentage = total > 0 ? ((value / total) * 100)
                                                .toFixed(1) : 0;
                                            return `${label}: ${value} orang (${percentage}%)`;
                                        }
                                    }
                                }
                            }
                        }
                    });
                }
                const ctx2 = document.getElementById('allowanceChart');
                if (ctx2 && charts?.allowance_by_divisi?.labels?.length > 0) {
                    allowanceChart = new Chart(ctx2, {
                        type: 'bar',
                        data: {
                            labels: charts.allowance_by_divisi.labels.filter(l => l),
                            datasets: [{
                                label: 'Total Tunjangan',
                                data: charts.allowance_by_divisi.allowance?.map(v => v || 0) || [],
                                backgroundColor: 'rgba(28, 200, 138, 0.8)',
                                borderColor: '#1cc88a',
                                borderWidth: 1,
                                borderRadius: 4,
                                borderSkipped: false
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: false
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            return `Total: ${formatIDR(context.parsed.y)}`;
                                        }
                                    }
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
                                            size: 10
                                        },
                                        callback: v => 'Rp ' + ((v || 0) / 1000000).toFixed(1) + 'J'
                                    }
                                },
                                x: {
                                    grid: {
                                        display: false
                                    },
                                    ticks: {
                                        font: {
                                            size: 10
                                        },
                                        maxRotation: 45,
                                        minRotation: 45
                                    }
                                }
                            }
                        }
                    });
                }
                const ctx3 = document.getElementById('deductionChart');
                if (ctx3 && charts?.top_deductions?.labels?.length > 0) {
                    deductionChart = new Chart(ctx3, {
                        type: 'bar',
                        data: {
                            labels: charts.top_deductions.labels.filter(l => l),
                            datasets: [{
                                label: 'Total Potongan',
                                data: charts.top_deductions.total_values?.map(v => v || 0) || [],
                                backgroundColor: 'rgba(231, 74, 59, 0.8)',
                                borderColor: '#e74a3b',
                                borderWidth: 1,
                                borderRadius: 4,
                                borderSkipped: false
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            indexAxis: 'y',
                            plugins: {
                                legend: {
                                    display: false
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            const idx = context.dataIndex;
                                            const avg = charts.top_deductions.averages[idx] || 0;
                                            const empCount = charts.top_deductions.employee_counts[
                                                idx] || 0;
                                            return [`Total: ${formatIDR(context.parsed.x)}`,
                                                `Karyawan: ${empCount}`,
                                                `Rata-rata: ${formatIDR(avg)}`
                                            ];
                                        }
                                    }
                                }
                            },
                            scales: {
                                x: {
                                    beginAtZero: true,
                                    grid: {
                                        color: 'rgba(0,0,0,0.05)'
                                    },
                                    ticks: {
                                        font: {
                                            size: 10
                                        },
                                        callback: v => 'Rp ' + ((v || 0) / 1000000).toFixed(1) + 'J'
                                    }
                                },
                                y: {
                                    grid: {
                                        display: false
                                    },
                                    ticks: {
                                        font: {
                                            size: 10
                                        }
                                    }
                                }
                            }
                        }
                    });
                }
                const ctx4 = document.getElementById('trendChart');
                if (ctx4 && charts?.monthly_trend?.length > 0) {
                    trendChart = new Chart(ctx4, {
                        type: 'line',
                        data: {
                            labels: charts.monthly_trend.map(t => t?.month || ''),
                            datasets: [{
                                label: 'Total Gaji',
                                data: charts.monthly_trend.map(t => t?.total_gaji || 0),
                                borderColor: '#36b9cc',
                                backgroundColor: 'rgba(54, 185, 204, 0.1)',
                                fill: true,
                                tension: 0.3,
                                pointBackgroundColor: '#36b9cc',
                                pointBorderColor: '#fff',
                                pointBorderWidth: 2,
                                pointRadius: 4,
                                pointHoverRadius: 6
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: false
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            return `Total: ${formatIDR(context.parsed.y)}`;
                                        }
                                    }
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
                                            size: 10
                                        },
                                        callback: v => 'Rp ' + ((v || 0) / 1000000).toFixed(1) + 'J'
                                    }
                                },
                                x: {
                                    grid: {
                                        display: false
                                    },
                                    ticks: {
                                        font: {
                                            size: 10
                                        }
                                    }
                                }
                            }
                        }
                    });
                }
            }

            function renderChartsSafe(charts) {
                const safeCharts = {
                    salary_ranges: charts?.salary_ranges || {
                        labels: [],
                        counts: [],
                        averages: []
                    },
                    allowance_by_divisi: charts?.allowance_by_divisi || {
                        labels: [],
                        allowance: [],
                        avg_salary: []
                    },
                    monthly_trend: charts?.monthly_trend || [],
                    top_deductions: charts?.top_deductions || {
                        labels: [],
                        total_values: [],
                        employee_counts: [],
                        averages: []
                    }
                };
                renderCharts(safeCharts);
            }

            function renderTable(res) {
                let html = '';
                const start = (res.pagination.current_page - 1) * res.pagination.per_page + 1;
                const end = Math.min(res.pagination.current_page * res.pagination.per_page, res.pagination.total);
                $('#showingStart').text(res.pagination.total > 0 ? start : 0);
                $('#showingEnd').text(end);
                $('#totalItems').text(res.pagination.total);
                if (allData.length === 0) {
                    html =
                        `<tr><td colspan="9" class="text-center text-muted py-5"><img src="https://undraw.co/api/illustrations/random?seed=payroll" alt="No data" class="mb-3" style="max-width:120px;opacity:0.7"><br>Tidak ada data untuk periode ini</td></tr>`;
                } else {
                    allData.forEach((row, idx) => {
                        const no = (res.pagination.current_page - 1) * res.pagination.per_page + idx + 1;
                        const badgeClass = row.status === 'Sudah Dihitung' ? 'status-done' :
                            'status-pending';
                        const badgeText = row.status === 'Sudah Dihitung' ? 'Sudah' : 'Belum';
                        html += `<tr>
                            <td class="text-muted">${no}</td>
                            <td><strong class="d-block text-dark">${row.nama}</strong><small class="text-muted">${row.kode || '-'}</small></td>
                            <td><span class="d-block">${row.divisi}</span><small class="text-muted">${row.jabatan}</small></td>
                            <td class="text-end text-muted">${formatIDR(row.gaji_pokok)}</td>
                            <td class="text-end text-success fw-semibold">${formatSigned(row.total_tunjangan)}</td>
                            <td class="text-end text-danger">-${formatIDR(row.total_potongan)}</td>
                            <td class="text-end fw-bold text-primary">${formatIDR(row.gaji_bersih)}</td>
                            <td class="text-center"><span class="status-badge ${badgeClass}">${badgeText}</span></td>
                            <td class="text-center"><button class="btn btn-outline-primary btn-sm" onclick="openDetail(${row.id})" title="Lihat Detail"><i class="fa-solid fa-eye"></i></button></td>
                        </tr>`;
                    });
                }
                $('#payrollBody').html(html);
                let pagHtml = '';
                if (res.pagination.last_page > 1) {
                    for (let i = 1; i <= res.pagination.last_page; i++) {
                        const activeClass = i === currentPage ? 'active' : '';
                        pagHtml += `<button class="${activeClass}" onclick="loadData(${i})">${i}</button>`;
                    }
                }
                $('#paginationContainer').html(pagHtml);
            }

            window.openDetail = function(id) {
                const row = allData.find(d => d.id === id);
                if (!row) return;
                $('#modalNama').text(row.nama);
                $('#modalKode').text(row.kode || '-');
                $('#modalDivisi').text(row.divisi);
                $('#modalJabatan').text(row.jabatan);
                let html =
                    `<tr><td>Gaji Pokok</td><td>-</td><td>-</td><td class="text-end fw-semibold">${formatIDR(row.gaji_pokok)}</td></tr>`;
                row.details.forEach(d => {
                    const valueClass = d.nilai < 0 ? 'text-danger' : 'text-success';
                    html +=
                        `<tr><td>${d.nama || '-'}</td><td><small class="text-muted">${d.tipe || '-'}</small></td><td><small class="text-muted">${d.keterangan || '-'}</small></td><td class="text-end ${valueClass}">${formatSigned(d.nilai)}</td></tr>`;
                });
                $('#modalDetails').html(html);
                $('#modalNet').text(formatIDR(row.gaji_bersih));
                new bootstrap.Modal(document.getElementById('modalDetail')).show();
            };

            $('#btnFilter').click(function() {
                loadData(1);
            });
            $('#exportCsv').click(function() {
                const month = $('#filterBulan').val();
                const year = $('#filterTahun').val();
                window.location.href =
                    `{{ route('HR.payroll.export.csv') }}?month=${month}&year=${year}&search=${searchQuery}`;
            });
            $('#exportPdf').click(function() {
                const month = $('#filterBulan').val();
                const year = $('#filterTahun').val();
                window.location.href = `{{ route('HR.payroll.export.pdf') }}?month=${month}&year=${year}`;
            });
            $('#searchPayroll').on('keyup', function(e) {
                if (e.keyCode === 13 || this.value.length > 2 || this.value === '') {
                    searchQuery = this.value;
                    loadData(1);
                }
            });
            loadData(1);
        });
    </script>
@endsection
