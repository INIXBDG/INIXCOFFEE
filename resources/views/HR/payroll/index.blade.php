@extends('layout_HR.app')
@section('content_HR')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

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

        body { background-color: #fafbfc; }

        .page-header { margin-bottom: 1.5rem; }
        .page-title { font-size: 1.6rem; font-weight: 700; color: var(--gray-900); margin-bottom: .15rem; }
        .page-sub { color: var(--gray-400); font-size: .875rem; }

        .stat-card {
            border: none; border-radius: var(--radius); box-shadow: var(--shadow);
            transition: transform .25s, box-shadow .25s;
        }
        .stat-card:hover { transform: translateY(-3px); box-shadow: var(--shadow-lg); }
        .stat-icon {
            width: 48px; height: 48px; border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.3rem; color: #fff;
        }
        .stat-value { font-size: 1.45rem; font-weight: 700; color: var(--gray-900); margin: .4rem 0 .15rem; }
        .stat-label { color: var(--gray-400); font-size: .8rem; margin: 0; }

        .nav-tabs-custom { border-bottom: 2px solid var(--gray-200); }
        .nav-tabs-custom .nav-link {
            border: none; color: var(--gray-400); font-weight: 600;
            padding: .85rem 1.25rem; font-size: .875rem; transition: color .2s;
        }
        .nav-tabs-custom .nav-link:hover { color: var(--pri); }
        .nav-tabs-custom .nav-link.active {
            color: var(--pri); border-bottom: 3px solid var(--pri); background: transparent;
        }

        .card-shell { border: none; border-radius: var(--radius); box-shadow: var(--shadow); }
        .card-shell .card-body { padding: 1.5rem; }

        .btn-pri {
            background: var(--pri); border: none; color: #fff; font-weight: 600;
            padding: .5rem 1.25rem; border-radius: 8px; transition: all .25s;
        }
        .btn-pri:hover {
            background: var(--pri-dark); transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(79, 70, 229, .35); color: #fff;
        }

        .status-badge {
            padding: .35rem .75rem; border-radius: 20px; font-size: .7rem;
            font-weight: 700; text-transform: uppercase; letter-spacing: .5px;
        }
        .status-done { background: var(--success-light); color: var(--success); }
        .status-pending { background: var(--warning-light); color: var(--warning); }

        #payrollTable { border-collapse: separate; border-spacing: 0; width: 100%; }
        #payrollTable thead th {
            font-size: .75rem; font-weight: 700; text-transform: uppercase;
            letter-spacing: .5px; color: var(--gray-600); background: var(--gray-50);
            border-bottom: 2px solid var(--gray-200) !important; border-top: none !important;
            padding: 0.75rem 1rem;
        }
        #payrollTable tbody tr { transition: background .15s; }
        #payrollTable tbody tr:hover { background: var(--pri-light) !important; }
        #payrollTable tbody td {
            vertical-align: middle; font-size: .875rem;
            border-bottom: 1px solid var(--gray-100) !important; border-top: none !important;
            padding: 0.75rem 1rem;
        }

        .pagination-custom { display: flex; gap: 0.25rem; flex-wrap: wrap; }
        .pagination-custom button {
            padding: 0.35rem 0.75rem; font-size: 0.8rem; border-radius: 6px;
            border: 1px solid var(--gray-200); background: #fff; color: var(--gray-600);
            cursor: pointer; transition: all 0.15s; font-weight: 600;
        }
        .pagination-custom button:hover { background: var(--pri-light); color: var(--pri); border-color: var(--pri); }
        .pagination-custom button.active { background: var(--pri); color: white; border-color: var(--pri); }

        .chart-wrap { position: relative; height: 260px; }

        .modal-header-custom {
            background: linear-gradient(135deg, var(--pri) 0%, var(--pri-dark) 100%);
            color: #fff; border-radius: 12px 12px 0 0;
        }
        .modal-header-custom .btn-close { filter: brightness(0) invert(1); }

        .loading-overlay {
            position: fixed; inset: 0; background: rgba(255, 255, 255, .6);
            display: flex; align-items: center; justify-content: center; z-index: 9998;
        }
        .loading-overlay.hidden { display: none; }

        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #c1c1c1; border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: #a1a1c1; }
    </style>

    <div class="container-fluid px-4 py-4">
        <div class="d-sm-flex align-items-center justify-content-between page-header">
            <div>
                <h1 class="page-title">Dashboard Payroll & Tunjangan</h1>
                <p class="page-sub mb-0">Periode: <strong id="periodLabel">-</strong></p>
            </div>
            <div class="d-flex gap-2 mt-2 mt-sm-0">
                <button id="exportExcel" class="btn btn-outline-success"><i class="fa-solid fa-file-excel me-2"></i>Export Excel</button>
                <button id="exportPdf" class="btn btn-outline-danger"><i class="fa-solid fa-file-pdf me-2"></i>Export PDF</button>
            </div>
        </div>

        <div class="card card-shell mb-4">
            <div class="card-body py-3">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <span class="fw-bold text-primary" style="font-size:.875rem;"><i class="fa-solid fa-filter me-1"></i>Filter & Pencarian</span>
                    <div class="d-flex gap-2 flex-wrap" id="filterGroup">
                        <select id="filterBulan" class="form-select form-select-sm" style="width:130px"></select>
                        <select id="filterTahun" class="form-select form-select-sm" style="width:90px"></select>
                        <div class="input-group input-group-sm" style="width: 220px;">
                            <span class="input-group-text bg-white border-end-0"><i class="fa-solid fa-search text-muted"></i></span>
                            <input type="text" id="searchPayroll" class="form-control border-start-0" placeholder="Cari nama, divisi...">
                        </div>
                        <button id="btnFilter" class="btn btn-sm btn-pri"><i class="fa-solid fa-filter me-1"></i>Terapkan</button>
                    </div>
                </div>
            </div>
        </div>

        <ul class="nav nav-tabs nav-tabs-custom mb-4" id="mainTabs">
            <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tabDashboard"><i class="fa-solid fa-gauge-high me-2"></i>Dashboard & Statistik</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabData"><i class="fa-solid fa-table me-2"></i>Data Payroll</button></li>
        </ul>

        <div class="tab-content">
            <div class="tab-pane fade show active" id="tabDashboard">
                <div class="row g-3 mb-4">
                    <div class="col-xl-3 col-md-6">
                        <div class="card stat-card">
                            <div class="card-body d-flex align-items-center justify-content-between">
                                <div><p class="stat-label">Total Karyawan</p><h3 class="stat-value" id="sumTotal">0</h3></div>
                                <div class="stat-icon" style="background:linear-gradient(135deg,#4f46e5,#7c3aed)"><i class="fa-solid fa-users"></i></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="card stat-card">
                            <div class="card-body d-flex align-items-center justify-content-between">
                                <div><p class="stat-label">Sudah Dihitung</p><h3 class="stat-value" id="sumDone" style="color:var(--success)">0</h3></div>
                                <div class="stat-icon" style="background:linear-gradient(135deg,#059669,#10b981)"><i class="fa-solid fa-check-circle"></i></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="card stat-card">
                            <div class="card-body d-flex align-items-center justify-content-between">
                                <div><p class="stat-label">Belum Dihitung</p><h3 class="stat-value" id="sumPending" style="color:var(--warning)">0</h3></div>
                                <div class="stat-icon" style="background:linear-gradient(135deg,#d97706,#f59e0b)"><i class="fa-solid fa-exclamation-circle"></i></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="card stat-card">
                            <div class="card-body d-flex align-items-center justify-content-between">
                                <div><p class="stat-label">Total Payroll</p><h3 class="stat-value" id="sumGross">Rp 0</h3></div>
                                <div class="stat-icon" style="background:linear-gradient(135deg,#0284c7,#38bdf8)"><i class="fa-solid fa-money-bill-wave"></i></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-xl-4 col-md-4">
                        <div class="card stat-card">
                            <div class="card-body d-flex align-items-center justify-content-between">
                                <div><p class="stat-label">Rata-rata Gaji Bersih</p><h3 class="stat-value" id="sumAvg">Rp 0</h3></div>
                                <div class="stat-icon" style="background:linear-gradient(135deg,#8b5cf6,#a78bfa)"><i class="fa-solid fa-chart-line"></i></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-4 col-md-4">
                        <div class="card stat-card">
                            <div class="card-body d-flex align-items-center justify-content-between">
                                <div><p class="stat-label">Median Gaji</p><h3 class="stat-value" id="sumMedian">Rp 0</h3></div>
                                <div class="stat-icon" style="background:linear-gradient(135deg,#ec4899,#f472b6)"><i class="fa-solid fa-scale-balanced"></i></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-4 col-md-4">
                        <div class="card stat-card">
                            <div class="card-body d-flex align-items-center justify-content-between">
                                <div><p class="stat-label">Total Tunjangan</p><h3 class="stat-value" id="sumAllowance" style="color:var(--info)">Rp 0</h3></div>
                                <div class="stat-icon" style="background:linear-gradient(135deg,#0284c7,#38bdf8)"><i class="fa-solid fa-gift"></i></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-4 mb-4">
                    <div class="col-lg-6">
                        <div class="card card-shell">
                            <div class="card-body">
                                <h6 class="fw-bold mb-3" style="font-size:.875rem"><i class="fa-solid fa-chart-pie me-2 text-primary"></i>Distribusi Gaji Bersih</h6>
                                <div class="chart-wrap"><canvas id="salaryRangeChart"></canvas></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="card card-shell">
                            <div class="card-body">
                                <h6 class="fw-bold mb-3" style="font-size:.875rem"><i class="fa-solid fa-chart-bar me-2 text-success"></i>Tunjangan per Divisi (Top 8)</h6>
                                <div class="chart-wrap"><canvas id="allowanceChart"></canvas></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row g-4 mb-4">
                    <div class="col-lg-6">
                        <div class="card card-shell">
                            <div class="card-body">
                                <h6 class="fw-bold mb-3" style="font-size:.875rem"><i class="fa-solid fa-chart-column me-2 text-danger"></i>Potongan Terbanyak (Top 8)</h6>
                                <div class="chart-wrap"><canvas id="deductionChart"></canvas></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="card card-shell">
                            <div class="card-body">
                                <h6 class="fw-bold mb-3" style="font-size:.875rem"><i class="fa-solid fa-chart-line me-2 text-info"></i>Trend Payroll Bulanan (<span id="trendYear"></span>)</h6>
                                <div class="chart-wrap" style="height:220px"><canvas id="trendChart"></canvas></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="tabData">
                <div class="card card-shell">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table id="payrollTable" class="table mb-0">
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
                    </div>
                </div>
                <div class="d-flex flex-wrap justify-content-between align-items-center mt-3 px-2">
                    <small class="text-muted">
                        Menampilkan <span id="showingStart" class="fw-semibold">0</span>-<span id="showingEnd" class="fw-semibold">0</span>
                        dari <span id="totalItems" class="fw-semibold">0</span> data
                    </small>
                    <div class="pagination-custom mt-2 mt-md-0" id="paginationContainer"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalDetail" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content" style="border-radius:12px;border:none">
                <div class="modal-header modal-header-custom border-0">
                    <h5 class="modal-title fw-bold"><i class="fa-solid fa-receipt me-2"></i>Detail Payroll</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-2 mb-4">
                        <div class="col-md-6"><div class="p-3 rounded bg-light"><small class="text-muted d-block" style="font-size:.7rem;text-transform:uppercase">Nama</small><strong id="modalNama">-</strong></div></div>
                        <div class="col-md-6"><div class="p-3 rounded bg-light"><small class="text-muted d-block" style="font-size:.7rem;text-transform:uppercase">Kode</small><strong id="modalKode">-</strong></div></div>
                        <div class="col-md-6"><div class="p-3 rounded bg-light"><small class="text-muted d-block" style="font-size:.7rem;text-transform:uppercase">Divisi</small><strong id="modalDivisi">-</strong></div></div>
                        <div class="col-md-6"><div class="p-3 rounded bg-light"><small class="text-muted d-block" style="font-size:.7rem;text-transform:uppercase">Jabatan</small><strong id="modalJabatan">-</strong></div></div>
                    </div>
                    <h6 class="fw-bold mb-3" style="font-size:.8rem;color:var(--gray-600);text-transform:uppercase;letter-spacing:.5px">Rincian Komponen</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered" style="font-size:.85rem">
                            <thead class="table-light">
                                <tr><th>Komponen</th><th>Tipe</th><th>Keterangan</th><th class="text-end">Nilai</th></tr>
                            </thead>
                            <tbody id="modalDetails"></tbody>
                            <tfoot class="table-light">
                                <tr><th colspan="3" class="text-end">Total Bersih</th><th class="text-end text-primary fs-5" id="modalNet"></th></tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <div class="loading-overlay hidden" id="mainLoading">
        <div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        const formatIDR = (num) => {
            const value = typeof num === 'number' ? num : 0;
            return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(value);
        };
        const formatSigned = (num) => {
            const value = typeof num === 'number' ? num : 0;
            return (value < 0 ? '-' : '') + formatIDR(Math.abs(value));
        };

        let salaryChart = null, allowanceChart = null, trendChart = null, deductionChart = null;
        let currentPage = 1, allData = [], searchQuery = '';

        $(document).ready(function() {
            for (let i = 1; i <= 12; i++) {
                const monthName = new Date(2000, i - 1).toLocaleString('id-ID', { month: 'long' });
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
                const params = { month: $('#filterBulan').val(), year: $('#filterTahun').val(), search: searchQuery, page: page };
                
                $.get("{{ route('HR.payroll.dashboard') }}", params, function(res) {
                    if (!res.success) { alert(res.message); $('#mainLoading').addClass('hidden'); return; }
                    allData = res.data;
                    $('#periodLabel').text(res.period.display);
                    $('#sumTotal').text(res.summary.total_karyawan);
                    $('#sumDone').text(res.summary.sudah_dihitung);
                    $('#sumPending').text(res.summary.belum_dihitung);
                    $('#sumGross').text(formatIDR(res.summary.total_payroll));
                    $('#sumAvg').text(formatIDR(res.summary.avg_gaji_bersih));
                    $('#sumMedian').text(formatIDR(res.summary.median_gaji_bersih));
                    $('#sumAllowance').text(formatIDR(res.summary.total_tunjangan));
                    renderChartsSafe(res.charts);
                    renderTable(res);
                    $('#mainLoading').addClass('hidden');
                }).fail(function(xhr, status, error) {
                    console.error('Error:', error); alert('Gagal memuat data: ' + error); $('#mainLoading').addClass('hidden');
                });
            };

            function renderCharts(charts) {
                ['salaryRangeChart', 'allowanceChart', 'trendChart', 'deductionChart'].forEach(id => {
                    const existing = Chart.getChart(id); if (existing) existing.destroy();
                });
                
                const ctx1 = document.getElementById('salaryRangeChart');
                if (ctx1 && charts?.salary_ranges?.labels?.length > 0) {
                    salaryChart = new Chart(ctx1, {
                        type: 'doughnut',
                        data: { labels: charts.salary_ranges.labels.filter(l => l), datasets: [{ data: charts.salary_ranges.counts.map(c => c || 0), backgroundColor: ['#4f46e5', '#059669', '#d97706', '#dc2626', '#9ca3af'], borderWidth: 0 }] },
                        options: { responsive: true, maintainAspectRatio: false, cutout: '70%', plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 }, padding: 15, usePointStyle: true } } } }
                    });
                }

                const ctx2 = document.getElementById('allowanceChart');
                if (ctx2 && charts?.allowance_by_divisi?.labels?.length > 0) {
                    allowanceChart = new Chart(ctx2, {
                        type: 'bar',
                        data: { labels: charts.allowance_by_divisi.labels.filter(l => l), datasets: [{ label: 'Total Tunjangan', data: charts.allowance_by_divisi.allowance?.map(v => v || 0) || [], backgroundColor: 'rgba(5, 150, 105, 0.8)', borderRadius: 6 }] },
                        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { callback: v => 'Rp ' + ((v || 0) / 1000000).toFixed(1) + 'J' } }, x: { grid: { display: false } } } }
                    });
                }

                const ctx3 = document.getElementById('deductionChart');
                if (ctx3 && charts?.top_deductions?.labels?.length > 0) {
                    deductionChart = new Chart(ctx3, {
                        type: 'bar',
                        data: { labels: charts.top_deductions.labels.filter(l => l), datasets: [{ label: 'Total Potongan', data: charts.top_deductions.total_values?.map(v => v || 0) || [], backgroundColor: 'rgba(220, 38, 38, 0.8)', borderRadius: 6 }] },
                        options: { responsive: true, maintainAspectRatio: false, indexAxis: 'y', plugins: { legend: { display: false } }, scales: { x: { beginAtZero: true, ticks: { callback: v => 'Rp ' + ((v || 0) / 1000000).toFixed(1) + 'J' } }, y: { grid: { display: false } } } }
                    });
                }

                const ctx4 = document.getElementById('trendChart');
                if (ctx4 && charts?.monthly_trend?.length > 0) {
                    trendChart = new Chart(ctx4, {
                        type: 'line',
                        data: { labels: charts.monthly_trend.map(t => t?.month || ''), datasets: [{ label: 'Total Gaji', data: charts.monthly_trend.map(t => t?.total_gaji || 0), borderColor: '#0284c7', backgroundColor: 'rgba(2, 132, 199, 0.1)', fill: true, tension: 0.4, pointRadius: 4 }] },
                        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { callback: v => 'Rp ' + ((v || 0) / 1000000).toFixed(1) + 'J' } }, x: { grid: { display: false } } } }
                    });
                }
            }

            function renderChartsSafe(charts) {
                const safeCharts = {
                    salary_ranges: charts?.salary_ranges || { labels: [], counts: [] },
                    allowance_by_divisi: charts?.allowance_by_divisi || { labels: [], allowance: [] },
                    monthly_trend: charts?.monthly_trend || [],
                    top_deductions: charts?.top_deductions || { labels: [], total_values: [] }
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
                    html = `<tr><td colspan="9" class="text-center text-muted py-5">
                        <i class="fa-solid fa-filter-circle-xmark fa-3x mb-3 d-block" style="color:var(--gray-400)"></i>
                        <div class="fw-semibold">Tidak ada data untuk periode ini</div>
                        <small>Coba ubah filter atau rentang waktu pencarian</small>
                    </td></tr>`;
                } else {
                    allData.forEach((row, idx) => {
                        const no = (res.pagination.current_page - 1) * res.pagination.per_page + idx + 1;
                        const badgeClass = row.status === 'Sudah Dihitung' ? 'status-done' : 'status-pending';
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
                            <td class="text-center"><button class="btn btn-sm btn-outline-primary" onclick="openDetail(${row.id})" title="Lihat Detail"><i class="fa-solid fa-eye"></i></button></td>
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
                
                let html = `<tr><td>Gaji Pokok</td><td>-</td><td>-</td><td class="text-end fw-semibold">${formatIDR(row.gaji_pokok)}</td></tr>`;
                row.details.forEach(d => {
                    const valueClass = d.nilai < 0 ? 'text-danger' : 'text-success';
                    html += `<tr><td>${d.nama || '-'}</td><td><small class="text-muted">${d.tipe || '-'}</small></td><td><small class="text-muted">${d.keterangan || '-'}</small></td><td class="text-end ${valueClass}">${formatSigned(d.nilai)}</td></tr>`;
                });
                $('#modalDetails').html(html);
                $('#modalNet').text(formatIDR(row.gaji_bersih));
                new bootstrap.Modal(document.getElementById('modalDetail')).show();
            };

            $('#btnFilter').click(function() { loadData(1); });
            $('#exportExcel').click(function() {
                const month = $('#filterBulan').val(), year = $('#filterTahun').val();
                window.location.href = `{{ route('HR.payroll.export.excel') }}?month=${month}&year=${year}&search=${searchQuery}`;
            });
            $('#exportPdf').click(function() {
                const month = $('#filterBulan').val(), year = $('#filterTahun').val();
                window.location.href = `{{ route('HR.payroll.export.pdf') }}?month=${month}&year=${year}`;
            });
            $('#searchPayroll').on('keyup', function(e) {
                if (e.keyCode === 13 || this.value.length > 2 || this.value === '') {
                    searchQuery = this.value; loadData(1);
                }
            });
            
            loadData(1);
        });
    </script>
@endsection