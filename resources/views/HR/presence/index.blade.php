@extends('layout_HR.app')
@section('content_HR')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4e73df;
            --success-color: #1cc88a;
            --info-color: #36b9cc;
            --warning-color: #f6c23e;
            --danger-color: #e74a3b;
            --secondary-color: #858796;
            --light-bg: #f8f9fc;
            --dark-text: #5a5c69;
            --border-color: #e3e6f0;
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
            border-bottom: 1px solid var(--border-color);
            padding: 1rem 1.25rem;
            font-weight: 600;
            color: var(--dark-text);
        }

        .card-body {
            padding: 1.25rem;
        }

        .form-control,
        .form-select {
            font-size: 0.875rem;
            padding: 0.5rem 0.75rem;
            border: 1px solid var(--border-color);
            border-radius: 0.375rem;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--primary-color);
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
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-primary:hover {
            background-color: #3a5fd7;
            border-color: #3a5fd7;
        }

        .metric-card {
            border-left: 4px solid var(--primary-color);
            transition: all 0.2s ease;
        }

        .metric-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
        }

        .metric-card.success {
            border-left-color: var(--success-color);
        }

        .metric-card.warning {
            border-left-color: var(--warning-color);
        }

        .metric-card.danger {
            border-left-color: var(--danger-color);
        }

        .metric-value {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--primary-color);
            line-height: 1.2;
        }

        .metric-card.success .metric-value {
            color: var(--success-color);
        }

        .metric-card.warning .metric-value {
            color: var(--warning-color);
        }

        .metric-card.danger .metric-value {
            color: var(--danger-color);
        }

        .metric-label {
            font-size: 0.75rem;
            color: var(--secondary-color);
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: 0.5px;
            margin-bottom: 0.25rem;
        }

        .chart-container {
            position: relative;
            height: 280px;
            width: 100%;
        }

        .badge {
            font-weight: 500;
            padding: 0.4em 0.7em;
            font-size: 0.75em;
            border-radius: 0.375rem;
        }

        .risk-badge {
            padding: 0.3rem 0.6rem;
            border-radius: 0.25rem;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .risk-high {
            background-color: #fee2e2 !important;
            color: #dc2626 !important;
        }

        .risk-medium {
            background-color: #fef3c7 !important;
            color: #d97706 !important;
        }

        .risk-low {
            background-color: #dcfce7 !important;
            color: #16a34a !important;
        }

        .opportunity-card {
            background: var(--light-bg);
            border-left: 4px solid var(--primary-color);
            padding: 1rem;
            margin-bottom: 0.75rem;
            border-radius: 0 0.375rem 0.375rem 0;
            font-size: 0.875rem;
            transition: background 0.2s;
        }

        .opportunity-card:hover {
            background: #f1f3f9;
        }

        .opportunity-card.high {
            border-left-color: var(--danger-color);
            background: #fef2f2;
        }

        .opportunity-card.medium {
            border-left-color: var(--warning-color);
            background: #fffbeb;
        }

        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 0.375rem;
        }

        .calendar-header {
            text-align: center;
            font-weight: 600;
            font-size: 0.7rem;
            color: var(--secondary-color);
            padding: 0.5rem 0;
            text-transform: uppercase;
        }

        .calendar-day {
            aspect-ratio: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            border-radius: 0.375rem;
            font-size: 0.8rem;
            cursor: pointer;
            transition: all 0.15s;
            border: 1px solid var(--border-color);
            background: #fff;
            padding: 0.25rem;
        }

        .calendar-day:hover {
            transform: scale(1.05);
            border-color: var(--primary-color);
            box-shadow: 0 0.25rem 0.5rem rgba(0, 0, 0, 0.1);
        }

        .calendar-day .day-num {
            font-weight: 600;
            font-size: 0.85rem;
            color: var(--dark-text);
        }

        .calendar-day .day-name {
            font-size: 0.65rem;
            color: var(--secondary-color);
        }

        .calendar-day .late-indicator {
            font-size: 0.65rem;
            color: var(--danger-color);
            font-weight: 600;
        }

        .calendar-day.holiday {
            background: #fef3c7;
            border-color: #fcd34d;
        }

        .calendar-day.late {
            background: #fee2e2;
            border-color: #f87171;
        }

        .calendar-day.present {
            background: #dcfce7;
            border-color: #4ade80;
        }

        .calendar-day.weekend {
            background: #f3f4f6;
            color: #9ca3af;
        }

        .calendar-day.today {
            border: 2px solid var(--primary-color);
            box-shadow: 0 0 0 2px rgba(78, 115, 223, 0.15);
        }

        .filter-bar {
            background: var(--light-bg);
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
            border: 1px solid var(--border-color);
        }

        .table {
            font-size: 0.875rem;
            color: var(--dark-text);
        }

        .table thead th {
            background-color: var(--light-bg);
            border-bottom: 2px solid var(--border-color);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            color: var(--secondary-color);
            letter-spacing: 0.5px;
        }

        .table tbody td {
            padding: 0.75rem 1rem;
            border-bottom: 1px solid var(--border-color);
            vertical-align: middle;
        }

        .table-hover tbody tr:hover {
            background-color: rgba(78, 115, 223, 0.04);
        }

        .prediction-card {
            background: var(--light-bg);
            border: 1px solid var(--border-color);
            border-radius: 0.5rem;
            padding: 1.25rem;
            text-align: center;
            transition: transform 0.2s;
        }

        .prediction-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 0.25rem 0.5rem rgba(0, 0, 0, 0.075);
        }

        .prediction-card .metric-value {
            font-size: 2rem;
            margin: 0.5rem 0;
        }

        .loading-skeleton {
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: loading 1.5s infinite;
            border-radius: 0.25rem;
            height: 1rem;
        }

        @keyframes loading {
            0% {
                background-position: 200% 0;
            }

            100% {
                background-position: -200% 0;
            }
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

        .empty-state {
            text-align: center;
            padding: 2rem;
            color: var(--secondary-color);
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
            color: var(--secondary-color);
        }

        @media (max-width: 767px) {
            .filter-bar .row {
                flex-direction: column;
            }

            .filter-bar .col-md-3 {
                width: 100%;
            }

            .calendar-grid {
                gap: 0.25rem;
            }

            .calendar-day {
                font-size: 0.7rem;
                padding: 0.125rem;
            }

            .metric-value {
                font-size: 1.5rem;
            }
        }
    </style>

    <div class="container-fluid">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <div>
                <h1 class="page-title">Attendance Intelligence Dashboard</h1>
                <p class="page-subtitle mb-0">Monitor dan analisis kehadiran karyawan secara real-time</p>
            </div>
            <div>
                <button class="btn btn-outline-primary btn-sm me-2" id="btnRefresh">
                    <i class="bi bi-arrow-clockwise me-1"></i>Refresh
                </button>
                <button class="btn btn-success btn-sm me-2" id="btnExportCsv">
                    <i class="bi bi-file-earmark-spreadsheet me-1"></i>Excel
                </button>
                <button class="btn btn-danger btn-sm" id="btnExportPdf">
                    <i class="bi bi-file-earmark-pdf me-1"></i>PDF
                </button>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-body">
                <div class="mb-3">
                    <h6 class="m-0 fw-bold text-primary"><i class="bi bi-funnel me-2"></i>Filter Data</h6>
                </div>
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label small text-muted">Bulan</label>
                        <select id="filterBulan" class="form-select form-select-sm"></select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small text-muted">Tahun</label>
                        <select id="filterTahun" class="form-select form-select-sm"></select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small text-muted">&nbsp;</label>
                        <button class="btn btn-primary btn-sm w-100" id="btnApplyFilter">Terapkan Filter</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-xl-4 col-md-6 mb-4">
                <div class="card metric-card h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col me-2">
                                <div class="metric-label">Attendance Rate</div>
                                <div class="metric-value" id="metricAttendanceRate">-</div>
                            </div>
                            <div class="col-auto">
                                <div
                                    class="avatar avatar-md bg-primary-subtle rounded-circle d-flex align-items-center justify-content-center">
                                    <i class="bi bi-check-circle-fill text-primary"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-md-6 mb-4">
                <div class="card metric-card success h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col me-2">
                                <div class="metric-label">Punctuality Rate</div>
                                <div class="metric-value" id="metricPunctuality">-</div>
                            </div>
                            <div class="col-auto">
                                <div
                                    class="avatar avatar-md bg-success-subtle rounded-circle d-flex align-items-center justify-content-center">
                                    <i class="bi bi-clock-fill text-success"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-md-6 mb-4">
                <div class="card metric-card warning h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col me-2">
                                <div class="metric-label">Avg Late (menit)</div>
                                <div class="metric-value" id="metricAvgLate">-</div>
                            </div>
                            <div class="col-auto">
                                <div
                                    class="avatar avatar-md bg-warning-subtle rounded-circle d-flex align-items-center justify-content-center">
                                    <i class="bi bi-exclamation-triangle-fill text-warning"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xl-6 col-lg-12 mb-4">
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="mb-3 d-flex flex-row align-items-center justify-content-between">
                            <h6 class="m-0 fw-bold text-primary"><i class="bi bi-graph-up me-2"></i>Trend Keterlambatan
                                Harian</h6>
                        </div>
                        <div class="chart-container">
                            <canvas id="trendChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-6 col-lg-12 mb-4">
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="mb-3 d-flex flex-row align-items-center justify-content-between">
                            <h6 class="m-0 fw-bold text-primary"><i class="bi bi-people-fill me-2"></i>Perbandingan per
                                Divisi</h6>
                        </div>
                        <div class="chart-container">
                            <canvas id="deptChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xl-6 col-lg-12 mb-4">
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="mb-3 d-flex flex-row align-items-center justify-content-between">
                            <h6 class="m-0 fw-bold text-primary"><i class="bi bi-calendar-heatmap me-2"></i>Attendance
                                Heatmap (Jam Masuk)</h6>
                        </div>
                        <div class="chart-container">
                            <canvas id="heatmapChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-6 col-lg-12 mb-4">
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="mb-3 d-flex flex-row align-items-center justify-content-between">
                            <h6 class="m-0 fw-bold text-primary"><i class="bi bi-shield-exclamation me-2"></i>Distribusi
                                Risk Level</h6>
                        </div>
                        <div class="chart-container">
                            <canvas id="riskChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xl-6 col-lg-12 mb-4">
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="mb-3">
                            <h6 class="m-0 fw-bold text-primary"><i class="bi bi-lightbulb-fill me-2"></i>Peluang &
                                Rekomendasi</h6>
                        </div>
                        <div id="opportunitiesContainer"></div>
                    </div>
                </div>
            </div>
            <div class="col-xl-6 col-lg-12 mb-4">
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="mb-3 d-flex flex-row align-items-center justify-content-between">
                            <h6 class="m-0 fw-bold text-primary"><i class="bi bi-calendar-check me-2"></i>Kalender
                                Kehadiran</h6>
                            <select id="calendarEmployee" class="form-select form-select-sm w-auto">
                                <option value="">Semua Karyawan</option>
                            </select>
                        </div>
                        <div class="legend mb-3 small">
                            <span class="badge me-2 risk-high">● Telat</span>
                            <span class="badge me-2 risk-medium">● Libur/Cuti</span>
                            <span class="badge me-2 risk-low">● Hadir</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <button class="btn btn-outline-secondary btn-sm" id="btnPrevMonth">
                                <i class="bi bi-chevron-left"></i>
                            </button>
                            <span class="fw-bold" id="calendarMonthLabel"></span>
                            <button class="btn btn-outline-secondary btn-sm" id="btnNextMonth">
                                <i class="bi bi-chevron-right"></i>
                            </button>
                        </div>
                        <div id="attendanceCalendar" class="calendar-grid"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-body">
                <div class="mb-3">
                    <h6 class="m-0 fw-bold text-primary"><i class="bi bi-predictive-analytics me-2"></i>Prediksi & Target
                        Masa Depan</h6>
                </div>
                <div class="row g-4 mb-4">
                    <div class="col-md-4">
                        <div class="prediction-card">
                            <strong class="d-block text-muted small text-uppercase">Bulan Depan</strong>
                            <div class="metric-value" id="predNextMonth">-</div>
                            <small class="text-muted" id="predNextMonthConf">-</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="prediction-card">
                            <strong class="d-block text-muted small text-uppercase">Kuartal Depan</strong>
                            <div class="metric-value" id="predNextQuarter">-</div>
                            <small class="text-muted" id="predNextQuarterConf">-</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="prediction-card">
                            <strong class="d-block text-muted small text-uppercase">Tahun Depan</strong>
                            <div class="metric-value" id="predNextYear">-</div>
                            <small class="text-muted" id="predNextYearConf">-</small>
                        </div>
                    </div>
                </div>
                <h6 class="fw-bold text-dark mb-3">Milestone Target:</h6>
                <div id="milestonesContainer"></div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        const formatPercent = v => `${v}%`;
        const formatMinutes = v => `${v} menit`;
        let trendChart, deptChart, heatmapChart, riskChart;
        let currentCalMonth = null,
            currentCalYear = null;

        $(document).ready(function() {
            initFilters();
            loadAnalytics();
            initCalendarControls();
            loadCalendar($('#filterBulan').val(), $('#filterTahun').val());

            $('#btnApplyFilter, #btnRefresh').click(() => loadAnalytics());
            $('#btnExportCsv').click(() => exportReport('csv'));
            $('#btnExportPdf').click(() => exportReport('pdf'));
            $('#filterBulan, #filterTahun').change(() => {
                loadCalendar($('#filterBulan').val(), $('#filterTahun').val());
            });
            $('#calendarEmployee').change(() => {
                loadCalendar(currentCalMonth, currentCalYear);
            });
        });

        function initFilters() {
            for (let i = 1; i <= 12; i++) {
                const m = new Date(2000, i - 1).toLocaleString('id-ID', {
                    month: 'long'
                });
                $('#filterBulan').append(`<option value="${String(i).padStart(2,'0')}">${m}</option>`);
            }
            for (let y = 2023; y <= 2027; y++) $('#filterTahun').append(`<option value="${y}">${y}</option>`);
            $('#filterBulan').val(String(new Date().getMonth() + 1).padStart(2, '0'));
            $('#filterTahun').val(new Date().getFullYear());
        }

        function getFilterParams() {
            return {
                month: $('#filterBulan').val(),
                year: $('#filterTahun').val(),
                divisi: $('#filterDivisi').val(),
                jabatan: 'all'
            };
        }

        function loadAnalytics() {
            $.get("{{ route('HR.absensi.analytics') }}", getFilterParams(), function(res) {
                if (!res.success) {
                    alert(res.message);
                    return;
                }
                renderSummary(res.summary);
                renderCharts(res.charts);
                renderOpportunities(res.opportunities);
                renderPredictions(res.predictions);
            }).fail(() => alert('Gagal memuat data analytics'));
        }

        function renderSummary(s) {
            $('#metricAttendanceRate').text(formatPercent(s.attendance_rate));
            $('#metricPunctuality').text(formatPercent(s.punctuality_rate));
            $('#metricAvgLate').text(formatMinutes(s.avg_late_minutes));
        }

        function renderCharts(c) {
            destroyCharts();
            trendChart = new Chart(document.getElementById('trendChart'), {
                type: 'line',
                data: {
                    labels: c.punctuality_trend.map(d => d.date),
                    datasets: [{
                        label: '% Keterlambatan',
                        data: c.punctuality_trend.map(d => d.late_rate),
                        borderColor: '#e74a3b',
                        backgroundColor: 'rgba(231, 74, 59, 0.1)',
                        fill: true,
                        tension: 0.3,
                        pointRadius: 3,
                        pointBackgroundColor: '#e74a3b',
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100,
                            grid: {
                                color: 'rgba(0,0,0,0.05)'
                            },
                            ticks: {
                                font: {
                                    size: 10
                                }
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
            deptChart = new Chart(document.getElementById('deptChart'), {
                type: 'bar',
                data: {
                    labels: c.department_comparison.map(d => d.divisi),
                    datasets: [{
                        label: 'Attendance Rate',
                        data: c.department_comparison.map(d => d.attendance_rate),
                        backgroundColor: '#4e73df',
                        borderRadius: 4,
                        borderSkipped: false
                    }, {
                        label: 'Punctuality Rate',
                        data: c.department_comparison.map(d => d.punctuality_rate),
                        backgroundColor: '#1cc88a',
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
                                    size: 11
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100,
                            grid: {
                                color: 'rgba(0,0,0,0.05)'
                            },
                            ticks: {
                                font: {
                                    size: 10
                                }
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
            heatmapChart = new Chart(document.getElementById('heatmapChart'), {
                type: 'bar',
                data: {
                    labels: c.attendance_heatmap.labels.map(l => l.replace('_', ' ')),
                    datasets: [{
                        label: 'Frekuensi',
                        data: c.attendance_heatmap.values,
                        backgroundColor: '#36b9cc',
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
                        }
                    },
                    scales: {
                        x: {
                            ticks: {
                                font: {
                                    size: 10
                                }
                            }
                        },
                        y: {
                            ticks: {
                                font: {
                                    size: 10
                                }
                            }
                        }
                    }
                }
            });
            riskChart = new Chart(document.getElementById('riskChart'), {
                type: 'doughnut',
                data: {
                    labels: ['High Risk', 'Medium Risk', 'Low Risk'],
                    datasets: [{
                        data: [c.risk_distribution.high, c.risk_distribution.medium, c.risk_distribution
                            .low],
                        backgroundColor: ['#e74a3b', '#f6c23e', '#1cc88a'],
                        borderWidth: 3,
                        borderColor: '#fff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                font: {
                                    size: 11
                                }
                            }
                        }
                    },
                    cutout: '70%'
                }
            });
        }

        function destroyCharts() {
            [trendChart, deptChart, heatmapChart, riskChart].forEach(c => c?.destroy());
        }

        function renderOpportunities(opp) {
            const container = $('#opportunitiesContainer').empty();
            if (opp.length === 0) {
                container.html(
                    `<div class="empty-state"><img src="https://undraw.co/api/illustrations/random?seed=insights" alt="No data"><p class="mb-0">Tidak ada rekomendasi saat ini</p></div>`
                    );
                return;
            }
            opp.forEach(o => {
                const priorityClass = o.priority === 'high' ? 'high' : (o.priority === 'medium' ? 'medium' : '');
                container.append(`
                    <div class="opportunity-card ${priorityClass}">
                        <div class="d-flex justify-content-between align-items-start">
                            <strong class="d-block mb-1 text-dark">${o.title}</strong>
                            <span class="badge bg-${o.priority === 'high' ? 'danger' : (o.priority === 'medium' ? 'warning' : 'secondary')}">${o.priority.toUpperCase()}</span>
                        </div>
                        <p class="small mb-2 text-muted">${o.description}</p>
                        <div class="d-flex justify-content-between small">
                            <span class="text-muted"><i class="bi bi-graph-up me-1"></i>${o.impact}</span>
                            <span class="text-muted"><i class="bi bi-clock me-1"></i>${o.timeline}</span>
                            <span class="text-muted"><i class="bi bi-tools me-1"></i>${o.effort}</span>
                        </div>
                    </div>
                `);
            });
        }

        function renderPredictions(p) {
            $('#predNextMonth').text(formatPercent(p.projections.next_month.estimated_rate));
            $('#predNextMonthConf').text(`Confidence: ${p.projections.next_month.confidence}`);
            $('#predNextQuarter').text(formatPercent(p.projections.next_quarter.estimated_rate));
            $('#predNextQuarterConf').text(`Confidence: ${p.projections.next_quarter.confidence}`);
            $('#predNextYear').text(formatPercent(p.projections.next_year.estimated_rate));
            $('#predNextYearConf').text(`Confidence: ${p.projections.next_year.confidence}`);
            const ms = $('#milestonesContainer').empty();
            if (!p.milestones || p.milestones.length === 0) {
                ms.html(
                    `<div class="empty-state"><img src="https://undraw.co/api/illustrations/random?seed=goals" alt="No milestones"><p class="mb-0">Belum ada milestone target</p></div>`);
                return;
            }
            p.milestones.forEach(m => {
                ms.append(`
                    <div class="alert alert-light border mb-2 py-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <strong>Target ${m.target}%</strong>
                            <span class="badge bg-primary">${m.timeline}</span>
                        </div>
                        <small class="text-muted d-block mt-1">${m.actions.join(' • ')}</small>
                    </div>
                `);
            });
        }

        function exportReport(format) {
            const params = new URLSearchParams(getFilterParams());
            params.append('format', format);
            window.location.href = `{{ route('HR.absensi.export') }}?${params.toString()}`;
        }

        function loadCalendar(month, year) {
            currentCalMonth = month;
            currentCalYear = year;
            const monthName = new Date(2000, month - 1).toLocaleString('id-ID', {
                month: 'long',
                year: 'numeric'
            });
            $('#calendarMonthLabel').text(monthName.charAt(0).toUpperCase() + monthName.slice(1));
            $.get("{{ route('HR.absensi.calendar') }}", {
                month,
                year,
                id_karyawan: $('#calendarEmployee').val()
            }, function(res) {
                if (!res.success) return;
                renderCalendar(res.calendar, res.month, res.year);
            });
        }

        function renderCalendar(days, month, year) {
            const container = $('#attendanceCalendar').empty();
            const today = new Date().toISOString().split('T')[0];
            ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'].forEach(d => {
                container.append(`<div class="calendar-header">${d}</div>`);
            });
            const firstDay = new Date(year, month - 1, 1).getDay();
            for (let i = 0; i < firstDay; i++) {
                container.append('<div></div>');
            }
            if (days.length === 0) {
                container.append(
                    `<div class="col-12 empty-state"><img src="https://undraw.co/api/illustrations/random?seed=calendar" alt="No data"><p class="mb-0">Tidak ada data kehadiran</p></div>`
                    );
                return;
            }
            days.forEach(d => {
                const isToday = d.date === today;
                let statusClass = '';
                if (d.status === 'late') statusClass = 'late';
                else if (d.status === 'leave' || d.status === 'holiday') statusClass = 'holiday';
                else if (d.status === 'present') statusClass = 'present';
                else if (d.is_weekend) statusClass = 'weekend';
                const classes = ['calendar-day', statusClass];
                if (isToday) classes.push('today');
                container.append(`
                    <div class="${classes.join(' ')}" title="${d.title || ''}">
                        <span class="day-num">${d.day}</span>
                        <span class="day-name">${d.day_name}</span>
                        ${d.late_minutes > 0 ? `<span class="late-indicator">+${d.late_minutes}m</span>` : ''}
                    </div>
                `);
            });
        }

        function initCalendarControls() {
            $('#btnPrevMonth').click(() => {
                let m = currentCalMonth - 1,
                    y = currentCalYear;
                if (m < 1) {
                    m = 12;
                    y--;
                }
                loadCalendar(m, y);
            });
            $('#btnNextMonth').click(() => {
                let m = currentCalMonth + 1,
                    y = currentCalYear;
                if (m > 12) {
                    m = 1;
                    y++;
                }
                loadCalendar(m, y);
            });
        }
    </script>
@endsection
