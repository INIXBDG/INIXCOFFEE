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
        }
        .stat-card:hover { transform: translateY(-3px); box-shadow: var(--shadow-lg); }
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
        .stat-trend {
            font-size: .72rem; font-weight: 600; margin-top: .3rem;
            display: inline-flex; align-items: center; gap: .25rem;
        }
        .stat-trend.up { color: var(--success); }
        .stat-trend.down { color: var(--danger); }

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
        .btn-success-custom {
            background: var(--success); border: none; color: #fff; font-weight: 600;
            padding: .5rem 1.25rem; border-radius: 8px; transition: all .25s;
            font-size: .85rem;
        }
        .btn-success-custom:hover {
            background: #047857; transform: translateY(-1px); color: #fff;
        }
        .btn-danger-custom {
            background: var(--danger); border: none; color: #fff; font-weight: 600;
            padding: .5rem 1.25rem; border-radius: 8px; transition: all .25s;
            font-size: .85rem;
        }
        .btn-danger-custom:hover {
            background: #b91c1c; transform: translateY(-1px); color: #fff;
        }

        /* ===== CHARTS ===== */
        .chart-wrap { position: relative; height: 280px; }
        .chart-title {
            font-size: .875rem; font-weight: 700; color: var(--gray-700);
            margin-bottom: 1rem; display: flex; align-items: center; gap: .5rem;
        }
        .chart-title i { color: var(--pri); }

        /* ===== CALENDAR ===== */
        .calendar-grid {
            display: grid; grid-template-columns: repeat(7, 1fr);
            gap: .4rem;
        }
        .calendar-header {
            text-align: center; font-weight: 700; font-size: .7rem;
            color: var(--gray-400); padding: .5rem 0;
            text-transform: uppercase; letter-spacing: .5px;
        }
        .calendar-day {
            aspect-ratio: 1; display: flex; flex-direction: column;
            align-items: center; justify-content: center;
            border-radius: 8px; font-size: .8rem; cursor: pointer;
            transition: all .2s; border: 1px solid var(--gray-200);
            background: #fff; padding: .3rem; position: relative;
        }
        .calendar-day:hover {
            transform: scale(1.05); border-color: var(--pri);
            box-shadow: var(--shadow-sm); z-index: 2;
        }
        .calendar-day .day-num {
            font-weight: 700; font-size: .9rem; color: var(--gray-700);
        }
        .calendar-day .day-name {
            font-size: .6rem; color: var(--gray-400); text-transform: capitalize;
        }
        .calendar-day .late-indicator {
            position: absolute; bottom: 2px; right: 4px;
            font-size: .6rem; color: var(--danger); font-weight: 700;
        }
        .calendar-day.holiday {
            background: var(--warning-light); border-color: #fcd34d;
        }
        .calendar-day.late {
            background: var(--danger-light); border-color: #f87171;
        }
        .calendar-day.present {
            background: var(--success-light); border-color: #4ade80;
        }
        .calendar-day.weekend {
            background: var(--gray-50); color: var(--gray-400);
        }
        .calendar-day.today {
            border: 2px solid var(--pri);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, .15);
        }
        .calendar-legend {
            display: flex; gap: 1rem; flex-wrap: wrap;
            font-size: .78rem; color: var(--gray-600);
        }
        .legend-dot {
            display: inline-block; width: 10px; height: 10px;
            border-radius: 3px; margin-right: 4px; vertical-align: middle;
        }

        /* ===== OPPORTUNITY CARDS ===== */
        .opp-card {
            border-radius: 10px; padding: 1.1rem 1.25rem;
            margin-bottom: .75rem; border: 1px solid var(--gray-200);
            background: #fff; transition: all .2s;
            border-left: 4px solid var(--gray-400);
        }
        .opp-card:hover {
            transform: translateX(3px); box-shadow: var(--shadow-sm);
        }
        .opp-card.high {
            border-left-color: var(--danger); background: linear-gradient(90deg, rgba(220,38,38,.03), transparent);
        }
        .opp-card.medium {
            border-left-color: var(--warning); background: linear-gradient(90deg, rgba(217,119,6,.03), transparent);
        }
        .opp-card.low {
            border-left-color: var(--success); background: linear-gradient(90deg, rgba(5,150,105,.03), transparent);
        }
        .opp-title {
            font-weight: 700; color: var(--gray-900);
            font-size: .9rem; margin-bottom: .35rem;
        }
        .opp-desc {
            font-size: .82rem; color: var(--gray-600);
            line-height: 1.5; margin-bottom: .6rem;
        }
        .opp-meta {
            display: flex; gap: 1rem; flex-wrap: wrap;
            font-size: .72rem; color: var(--gray-400); font-weight: 600;
        }
        .opp-meta span { display: inline-flex; align-items: center; gap: .25rem; }

        /* ===== PREDICTION CARDS ===== */
        .pred-card {
            background: linear-gradient(135deg, var(--pri-light) 0%, #fff 100%);
            border: 1px solid var(--gray-200); border-radius: var(--radius);
            padding: 1.5rem; text-align: center; transition: all .25s;
        }
        .pred-card:hover {
            transform: translateY(-3px); box-shadow: var(--shadow);
            border-color: var(--pri);
        }
        .pred-label {
            font-size: .72rem; text-transform: uppercase; letter-spacing: .5px;
            color: var(--gray-400); font-weight: 700; margin-bottom: .5rem;
        }
        .pred-value {
            font-size: 2rem; font-weight: 700; color: var(--pri);
            margin: .3rem 0; line-height: 1;
        }
        .pred-conf {
            font-size: .78rem; color: var(--gray-400); font-weight: 500;
        }

        /* ===== MILESTONE ===== */
        .milestone-item {
            background: var(--gray-50); border: 1px solid var(--gray-200);
            border-radius: 8px; padding: 1rem 1.25rem;
            margin-bottom: .6rem; transition: all .2s;
        }
        .milestone-item:hover {
            background: #fff; border-color: var(--pri);
            box-shadow: var(--shadow-sm);
        }
        .milestone-target {
            font-weight: 700; color: var(--gray-900); font-size: .95rem;
        }
        .milestone-timeline {
            background: var(--pri-light); color: var(--pri);
            font-size: .7rem; font-weight: 700; padding: .25rem .65rem;
            border-radius: 10px; text-transform: uppercase; letter-spacing: .3px;
        }
        .milestone-actions {
            font-size: .8rem; color: var(--gray-600);
            margin-top: .4rem; line-height: 1.5;
        }

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

        /* ===== EMPTY STATE ===== */
        .empty-state {
            text-align: center; padding: 2.5rem 1rem; color: var(--gray-400);
        }
        .empty-state i {
            font-size: 2.5rem; margin-bottom: 1rem; opacity: .4;
            display: block;
        }
        .empty-state p { font-size: .88rem; margin: 0; font-weight: 500; }
        .empty-state small { font-size: .78rem; }

        /* ===== RISK BADGE ===== */
        .risk-badge {
            padding: .3rem .7rem; border-radius: 20px;
            font-size: .68rem; font-weight: 700; text-transform: uppercase;
            letter-spacing: .5px;
        }
        .risk-high { background: var(--danger-light); color: var(--danger); }
        .risk-medium { background: var(--warning-light); color: var(--warning); }
        .risk-low { background: var(--success-light); color: var(--success); }

        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #c1c1c1; border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: #a1a1c1; }

        @media (max-width: 767px) {
            .calendar-grid { gap: .25rem; }
            .calendar-day { font-size: .7rem; padding: .15rem; }
            .calendar-day .day-num { font-size: .78rem; }
            .stat-value { font-size: 1.3rem; }
            .pred-value { font-size: 1.6rem; }
        }
    </style>

    <div class="container-fluid px-4 py-4">
        <div class="d-sm-flex align-items-center justify-content-between page-header">
            <div>
                <h1 class="page-title"><i class="fa-solid fa-user-check me-2" style="color:var(--pri)"></i>Attendance Intelligence</h1>
                <p class="page-sub mb-0">Monitor dan analisis kehadiran karyawan secara real-time</p>
            </div>
            <div class="d-flex gap-2 mt-2 mt-sm-0">
                <button class="btn btn-outline-sec" id="btnRefresh"><i class="fa-solid fa-arrows-rotate me-1"></i>Refresh</button>
                <button class="btn btn-success-custom" id="btnExportCsv"><i class="fa-solid fa-file-csv me-1"></i>Excel</button>
                <button class="btn btn-danger-custom" id="btnExportPdf"><i class="fa-solid fa-file-pdf me-1"></i>PDF</button>
            </div>
        </div>

        {{-- ===== FILTER BAR ===== --}}
        <div class="card card-shell mb-4">
            <div class="card-body py-3">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <span class="fw-bold" style="font-size:.875rem;color:var(--pri)"><i class="fa-solid fa-filter me-1"></i>Filter Periode</span>
                    <div class="d-flex gap-2 flex-wrap">
                        <select id="filterBulan" class="form-select form-select-sm" style="width:140px"></select>
                        <select id="filterTahun" class="form-select form-select-sm" style="width:100px"></select>
                        <button class="btn btn-pri btn-sm" id="btnApplyFilter"><i class="fa-solid fa-check me-1"></i>Terapkan</button>
                    </div>
                </div>
            </div>
        </div>

        {{-- ===== METRIC CARDS ===== --}}
        <div class="row g-3 mb-4">
            <div class="col-xl-4 col-md-6">
                <div class="card stat-card h-100">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div>
                            <p class="stat-label">Attendance Rate</p>
                            <h3 class="stat-value" id="metricAttendanceRate">-</h3>
                            <div class="stat-trend up" id="trendAttendance"><i class="fa-solid fa-arrow-up"></i> -</div>
                        </div>
                        <div class="stat-icon" style="background:linear-gradient(135deg,#4f46e5,#7c3aed)"><i class="fa-solid fa-user-check"></i></div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-md-6">
                <div class="card stat-card h-100">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div>
                            <p class="stat-label">Punctuality Rate</p>
                            <h3 class="stat-value" id="metricPunctuality" style="color:var(--success)">-</h3>
                            <div class="stat-trend up" id="trendPunctuality"><i class="fa-solid fa-arrow-up"></i> -</div>
                        </div>
                        <div class="stat-icon" style="background:linear-gradient(135deg,#059669,#10b981)"><i class="fa-solid fa-clock"></i></div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-md-6">
                <div class="card stat-card h-100">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div>
                            <p class="stat-label">Avg Late (menit)</p>
                            <h3 class="stat-value" id="metricAvgLate" style="color:var(--warning)">-</h3>
                            <div class="stat-trend down" id="trendLate"><i class="fa-solid fa-arrow-down"></i> -</div>
                        </div>
                        <div class="stat-icon" style="background:linear-gradient(135deg,#d97706,#f59e0b)"><i class="fa-solid fa-triangle-exclamation"></i></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ===== TABS ===== --}}
        <ul class="nav nav-tabs nav-tabs-custom mb-4" id="mainTabs">
            <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tabAnalytics"><i class="fa-solid fa-chart-line me-2"></i>Analytics</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabCalendar"><i class="fa-solid fa-calendar-days me-2"></i>Kalender Kehadiran</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabPrediction"><i class="fa-solid fa-crystal-ball me-2"></i>Prediksi & Target</button></li>
        </ul>

        <div class="tab-content">
            {{-- ===== TAB: ANALYTICS ===== --}}
            <div class="tab-pane fade show active" id="tabAnalytics">
                <div class="row g-4">
                    <div class="col-lg-6">
                        <div class="card card-shell">
                            <div class="card-body">
                                <div class="chart-title"><i class="fa-solid fa-chart-line"></i>Trend Keterlambatan Harian</div>
                                <div class="chart-wrap"><canvas id="trendChart"></canvas></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="card card-shell">
                            <div class="card-body">
                                <div class="chart-title"><i class="fa-solid fa-chart-column"></i>Perbandingan per Divisi</div>
                                <div class="chart-wrap"><canvas id="deptChart"></canvas></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="card card-shell">
                            <div class="card-body">
                                <div class="chart-title"><i class="fa-solid fa-fire"></i>Attendance Heatmap (Jam Masuk)</div>
                                <div class="chart-wrap"><canvas id="heatmapChart"></canvas></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="card card-shell">
                            <div class="card-body">
                                <div class="chart-title"><i class="fa-solid fa-shield-halved"></i>Distribusi Risk Level</div>
                                <div class="chart-wrap"><canvas id="riskChart"></canvas></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-4 mt-2">
                    <div class="col-12">
                        <div class="card card-shell">
                            <div class="card-body">
                                <div class="chart-title"><i class="fa-solid fa-lightbulb" style="color:var(--warning)"></i>Peluang & Rekomendasi</div>
                                <div id="opportunitiesContainer"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ===== TAB: CALENDAR ===== --}}
            <div class="tab-pane fade" id="tabCalendar">
                <div class="row g-4">
                    <div class="col-lg-8">
                        <div class="card card-shell">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <button class="btn btn-outline-sec btn-sm" id="btnPrevMonth"><i class="fa-solid fa-chevron-left"></i></button>
                                    <h5 class="mb-0 fw-bold" id="calendarMonthLabel" style="color:var(--gray-900)">-</h5>
                                    <button class="btn btn-outline-sec btn-sm" id="btnNextMonth"><i class="fa-solid fa-chevron-right"></i></button>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div class="calendar-legend">
                                        <span><span class="legend-dot" style="background:var(--success-light);border:1px solid #4ade80"></span>Hadir</span>
                                        <span><span class="legend-dot" style="background:var(--danger-light);border:1px solid #f87171"></span>Telat</span>
                                        <span><span class="legend-dot" style="background:var(--warning-light);border:1px solid #fcd34d"></span>Libur/Cuti</span>
                                        <span><span class="legend-dot" style="background:var(--gray-50);border:1px solid var(--gray-200)"></span>Weekend</span>
                                    </div>
                                    <select id="calendarEmployee" class="form-select form-select-sm" style="width:200px">
                                        <option value="">Semua Karyawan</option>
                                    </select>
                                </div>
                                <div id="attendanceCalendar" class="calendar-grid"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="card card-shell h-100">
                            <div class="card-body">
                                <div class="chart-title"><i class="fa-solid fa-circle-info" style="color:var(--info)"></i>Legenda & Informasi</div>
                                <div class="p-3 rounded mb-3" style="background:var(--success-light);border:1px solid #4ade80">
                                    <div class="fw-bold mb-1" style="font-size:.85rem;color:var(--success)"><i class="fa-solid fa-check me-1"></i>Hadir Tepat Waktu</div>
                                    <small style="color:var(--gray-600)">Karyawan hadir sebelum atau tepat pada jam masuk yang ditentukan.</small>
                                </div>
                                <div class="p-3 rounded mb-3" style="background:var(--danger-light);border:1px solid #f87171">
                                    <div class="fw-bold mb-1" style="font-size:.85rem;color:var(--danger)"><i class="fa-solid fa-clock me-1"></i>Terlambat</div>
                                    <small style="color:var(--gray-600)">Karyawan hadir setelah jam masuk. Angka menunjukkan menit keterlambatan.</small>
                                </div>
                                <div class="p-3 rounded mb-3" style="background:var(--warning-light);border:1px solid #fcd34d">
                                    <div class="fw-bold mb-1" style="font-size:.85rem;color:var(--warning)"><i class="fa-solid fa-umbrella-beach me-1"></i>Libur / Cuti</div>
                                    <small style="color:var(--gray-600)">Hari libur nasional, cuti tahunan, atau izin resmi lainnya.</small>
                                </div>
                                <div class="p-3 rounded" style="background:var(--gray-50);border:1px solid var(--gray-200)">
                                    <div class="fw-bold mb-1" style="font-size:.85rem;color:var(--gray-700)"><i class="fa-solid fa-calendar-xmark me-1"></i>Weekend</div>
                                    <small style="color:var(--gray-600)">Hari Sabtu dan Minggu (non-working day).</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ===== TAB: PREDICTION ===== --}}
            <div class="tab-pane fade" id="tabPrediction">
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="pred-card h-100">
                            <div class="pred-label"><i class="fa-solid fa-calendar-day me-1"></i>Bulan Depan</div>
                            <div class="pred-value" id="predNextMonth">-</div>
                            <div class="pred-conf" id="predNextMonthConf">-</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="pred-card h-100">
                            <div class="pred-label"><i class="fa-solid fa-calendar-week me-1"></i>Kuartal Depan</div>
                            <div class="pred-value" id="predNextQuarter">-</div>
                            <div class="pred-conf" id="predNextQuarterConf">-</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="pred-card h-100">
                            <div class="pred-label"><i class="fa-solid fa-calendar me-1"></i>Tahun Depan</div>
                            <div class="pred-value" id="predNextYear">-</div>
                            <div class="pred-conf" id="predNextYearConf">-</div>
                        </div>
                    </div>
                </div>

                <div class="card card-shell">
                    <div class="card-body">
                        <div class="chart-title"><i class="fa-solid fa-flag-checkered" style="color:var(--pri)"></i>Milestone Target</div>
                        <div id="milestonesContainer"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        const formatPercent = v => `${v}%`;
        const formatMinutes = v => `${v} menit`;
        let trendChart, deptChart, heatmapChart, riskChart;
        let currentCalMonth = null, currentCalYear = null;

        $(document).ready(function() {
            initFilters();
            loadAnalytics();
            initCalendarControls();
            loadCalendar($('#filterBulan').val(), $('#filterTahun').val());

            $('#btnApplyFilter, #btnRefresh').click(() => loadAnalytics());
            $('#btnExportCsv').click(() => exportReport('csv'));
            $('#btnExportPdf').click(() => exportReport('pdf'));
            $('#filterBulan, #filterTahun').change(() => loadCalendar($('#filterBulan').val(), $('#filterTahun').val()));
            $('#calendarEmployee').change(() => loadCalendar(currentCalMonth, currentCalYear));
        });

        function initFilters() {
            for (let i = 1; i <= 12; i++) {
                const m = new Date(2000, i - 1).toLocaleString('id-ID', { month: 'long' });
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
                if (!res.success) { alert(res.message); return; }
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

            // Simulasi trend (bisa diganti data real dari backend)
            $('#trendAttendance').html(`<i class="fa-solid fa-arrow-up"></i> +${(Math.random()*2+0.5).toFixed(1)}% dari bulan lalu`);
            $('#trendPunctuality').html(`<i class="fa-solid fa-arrow-up"></i> +${(Math.random()*1.5+0.3).toFixed(1)}% dari bulan lalu`);
            $('#trendLate').html(`<i class="fa-solid fa-arrow-down"></i> -${(Math.random()*3+1).toFixed(1)} menit dari bulan lalu`);
        }

        function renderCharts(c) {
            destroyCharts();
            const chartFont = { family: "-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto", size: 11 };
            const gridColor = 'rgba(0,0,0,0.05)';

            // ===== TREND CHART =====
            trendChart = new Chart(document.getElementById('trendChart'), {
                type: 'line',
                data: {
                    labels: c.punctuality_trend.map(d => d.date),
                    datasets: [{
                        label: '% Keterlambatan',
                        data: c.punctuality_trend.map(d => d.late_rate),
                        borderColor: '#dc2626',
                        backgroundColor: 'rgba(220, 38, 38, 0.1)',
                        fill: true, tension: 0.4, pointRadius: 3,
                        pointBackgroundColor: '#dc2626', pointBorderColor: '#fff',
                        pointBorderWidth: 2, borderWidth: 2.5
                    }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { display: false, labels: { font: chartFont } } },
                    scales: {
                        y: { beginAtZero: true, max: 100, grid: { color: gridColor }, ticks: { font: chartFont, callback: v => v + '%' } },
                        x: { grid: { display: false }, ticks: { font: chartFont, maxRotation: 45 } }
                    }
                }
            });

            // ===== DEPT CHART =====
            deptChart = new Chart(document.getElementById('deptChart'), {
                type: 'bar',
                data: {
                    labels: c.department_comparison.map(d => d.divisi),
                    datasets: [{
                        label: 'Attendance Rate',
                        data: c.department_comparison.map(d => d.attendance_rate),
                        backgroundColor: 'rgba(79, 70, 229, 0.85)',
                        borderRadius: 6, borderSkipped: false
                    }, {
                        label: 'Punctuality Rate',
                        data: c.department_comparison.map(d => d.punctuality_rate),
                        backgroundColor: 'rgba(5, 150, 105, 0.85)',
                        borderRadius: 6, borderSkipped: false
                    }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { position: 'bottom', labels: { font: chartFont, usePointStyle: true, padding: 15 } } },
                    scales: {
                        y: { beginAtZero: true, max: 100, grid: { color: gridColor }, ticks: { font: chartFont, callback: v => v + '%' } },
                        x: { grid: { display: false }, ticks: { font: chartFont } }
                    }
                }
            });

            // ===== HEATMAP CHART (DIPERBAIKI) =====
            // Konversi label jam (misal "07_30") menjadi kategori warna
            const heatmapLabels = c.attendance_heatmap.labels.map(l => {
                // Ubah "07_30" menjadi "07:30"
                return l.replace('_', ':');
            });
            
            // Fungsi untuk menentukan warna bar berdasarkan jam
            const getBarColor = (label) => {
                const parts = label.split(':');
                const hour = parseInt(parts[0]);
                const minute = parseInt(parts[1] || 0);
                const totalMinutes = hour * 60 + minute;
                
                // Batas waktu masuk = 08:00 (480 menit)
                if (totalMinutes < 465) return 'rgba(5, 150, 105, 0.85)';     // < 07:45 → Hijau (Early)
                if (totalMinutes < 480) return 'rgba(16, 185, 129, 0.85)';    // 07:45-08:00 → Hijau muda (On-time)
                if (totalMinutes < 510) return 'rgba(245, 158, 11, 0.85)';    // 08:00-08:30 → Kuning (Sedikit telat)
                return 'rgba(220, 38, 38, 0.85)';                              // > 08:30 → Merah (Telat)
            };
            
            const barColors = heatmapLabels.map(getBarColor);
            
            // Cari index untuk garis batas masuk (08:00)
            const cutoffIndex = heatmapLabels.findIndex(l => l === '08:00');

            heatmapChart = new Chart(document.getElementById('heatmapChart'), {
                type: 'bar',
                data: {
                    labels: heatmapLabels,
                    datasets: [{
                        label: 'Jumlah Kedatangan',
                        data: c.attendance_heatmap.values,
                        backgroundColor: barColors,
                        borderRadius: 6,
                        borderSkipped: false,
                        barPercentage: 0.75,
                        categoryPercentage: 0.85
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: 'rgba(17, 24, 39, 0.95)',
                            titleFont: { size: 12, weight: 'bold' },
                            bodyFont: { size: 11 },
                            padding: 12,
                            cornerRadius: 8,
                            displayColors: false,
                            callbacks: {
                                title: function(items) {
                                    return `Jam ${items[0].label}`;
                                },
                                label: function(context) {
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const value = context.parsed.y;
                                    const percent = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                    return [
                                        `Karyawan: ${value} orang`,
                                        `Persentase: ${percent}%`
                                    ];
                                },
                                afterLabel: function(context) {
                                    const label = context.label;
                                    const parts = label.split(':');
                                    const totalMinutes = parseInt(parts[0]) * 60 + parseInt(parts[1] || 0);
                                    if (totalMinutes < 480) return '✓ Kategori: Tepat Waktu';
                                    if (totalMinutes < 510) return '⚠ Kategori: Sedikit Telat';
                                    return '✗ Kategori: Telat';
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { color: gridColor },
                            ticks: {
                                font: chartFont,
                                color: '#6b7280',
                                padding: 8
                            },
                            title: {
                                display: true,
                                text: 'Jumlah Karyawan',
                                font: { size: 11, weight: '600' },
                                color: '#6b7280'
                            }
                        },
                        x: {
                            grid: { display: false },
                            ticks: {
                                font: { size: 10, weight: '600' },
                                color: '#6b7280',
                                maxRotation: 45,
                                minRotation: 45,
                                padding: 5
                            },
                            title: {
                                display: true,
                                text: 'Jam Kedatangan',
                                font: { size: 11, weight: '600' },
                                color: '#6b7280'
                            }
                        }
                    }
                },
                plugins: [{
                    // Plugin custom untuk menggambar garis batas masuk (08:00)
                    id: 'cutoffLine',
                    afterDraw: (chart) => {
                        if (cutoffIndex < 0) return;
                        const ctx = chart.ctx;
                        const xAxis = chart.scales.x;
                        const yAxis = chart.scales.y;
                        
                        // Posisi x di tengah bar "08:00"
                        const x = xAxis.getPixelForValue(cutoffIndex);
                        
                        ctx.save();
                        ctx.setLineDash([6, 4]);
                        ctx.strokeStyle = '#dc2626';
                        ctx.lineWidth = 2;
                        ctx.beginPath();
                        ctx.moveTo(x, yAxis.top);
                        ctx.lineTo(x, yAxis.bottom);
                        ctx.stroke();
                        
                        // Label "Batas Masuk"
                        ctx.setLineDash([]);
                        ctx.fillStyle = '#dc2626';
                        ctx.font = 'bold 10px -apple-system, sans-serif';
                        ctx.textAlign = 'center';
                        ctx.fillText('↓ Batas Masuk', x, yAxis.top - 5);
                        ctx.restore();
                    }
                }, {
                    // Plugin custom untuk legend manual di atas chart
                    id: 'customLegend',
                    afterDraw: (chart) => {
                        const ctx = chart.ctx;
                        const { left, top, width } = chart.chartArea;
                        
                        ctx.save();
                        ctx.font = '600 10px -apple-system, sans-serif';
                        
                        const legendItems = [
                            { color: 'rgba(5, 150, 105, 0.85)', label: 'Tepat Waktu' },
                            { color: 'rgba(245, 158, 11, 0.85)', label: 'Sedikit Telat' },
                            { color: 'rgba(220, 38, 38, 0.85)', label: 'Telat' }
                        ];
                        
                        let x = left;
                        const y = top - 20;
                        
                        legendItems.forEach(item => {
                            // Kotak warna
                            ctx.fillStyle = item.color;
                            ctx.fillRect(x, y - 8, 12, 12);
                            
                            // Label
                            ctx.fillStyle = '#4b5563';
                            ctx.textAlign = 'left';
                            ctx.fillText(item.label, x + 16, y + 2);
                            
                            x += ctx.measureText(item.label).width + 32;
                        });
                        
                        ctx.restore();
                    }
                }]
            });

            // ===== RISK CHART =====
            riskChart = new Chart(document.getElementById('riskChart'), {
                type: 'doughnut',
                data: {
                    labels: ['High Risk', 'Medium Risk', 'Low Risk'],
                    datasets: [{
                        data: [c.risk_distribution.high, c.risk_distribution.medium, c.risk_distribution.low],
                        backgroundColor: ['#dc2626', '#d97706', '#059669'],
                        borderWidth: 3, borderColor: '#fff'
                    }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false, cutout: '70%',
                    plugins: { legend: { position: 'bottom', labels: { font: chartFont, usePointStyle: true, padding: 15 } } }
                }
            });
        }

        function destroyCharts() {
            [trendChart, deptChart, heatmapChart, riskChart].forEach(c => c?.destroy());
        }

        function renderOpportunities(opp) {
            const container = $('#opportunitiesContainer').empty();
            if (!opp || opp.length === 0) {
                container.html(`<div class="empty-state"><i class="fa-solid fa-lightbulb"></i><p>Tidak ada rekomendasi saat ini</p><small>Semua metrik berada dalam kondisi optimal</small></div>`);
                return;
            }
            opp.forEach(o => {
                const priorityClass = o.priority === 'high' ? 'high' : (o.priority === 'medium' ? 'medium' : 'low');
                const badgeClass = o.priority === 'high' ? 'risk-high' : (o.priority === 'medium' ? 'risk-medium' : 'risk-low');
                container.append(`
                    <div class="opp-card ${priorityClass}">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div class="opp-title">${o.title}</div>
                            <span class="risk-badge ${badgeClass}">${o.priority}</span>
                        </div>
                        <div class="opp-desc">${o.description}</div>
                        <div class="opp-meta">
                            <span><i class="fa-solid fa-chart-line"></i>${o.impact}</span>
                            <span><i class="fa-solid fa-clock"></i>${o.timeline}</span>
                            <span><i class="fa-solid fa-gauge-high"></i>${o.effort}</span>
                        </div>
                    </div>
                `);
            });
        }

        function renderPredictions(p) {
            if (!p || !p.projections) return;
            $('#predNextMonth').text(formatPercent(p.projections.next_month.estimated_rate));
            $('#predNextMonthConf').text(`Confidence: ${p.projections.next_month.confidence}`);
            $('#predNextQuarter').text(formatPercent(p.projections.next_quarter.estimated_rate));
            $('#predNextQuarterConf').text(`Confidence: ${p.projections.next_quarter.confidence}`);
            $('#predNextYear').text(formatPercent(p.projections.next_year.estimated_rate));
            $('#predNextYearConf').text(`Confidence: ${p.projections.next_year.confidence}`);

            const ms = $('#milestonesContainer').empty();
            if (!p.milestones || p.milestones.length === 0) {
                ms.html(`<div class="empty-state"><i class="fa-solid fa-flag"></i><p>Belum ada milestone target</p><small>Hubungi administrator untuk menambahkan target</small></div>`);
                return;
            }
            p.milestones.forEach(m => {
                ms.append(`
                    <div class="milestone-item">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="milestone-target"><i class="fa-solid fa-bullseye me-2" style="color:var(--pri)"></i>Target ${m.target}%</div>
                            <span class="milestone-timeline">${m.timeline}</span>
                        </div>
                        <div class="milestone-actions">${m.actions.join(' • ')}</div>
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
            const monthName = new Date(year, month - 1).toLocaleString('id-ID', { month: 'long', year: 'numeric' });
            $('#calendarMonthLabel').text(monthName.charAt(0).toUpperCase() + monthName.slice(1));
            $.get("{{ route('HR.absensi.calendar') }}", {
                month, year, id_karyawan: $('#calendarEmployee').val()
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
            for (let i = 0; i < firstDay; i++) container.append('<div></div>');
            if (days.length === 0) {
                container.append(`<div class="col-12 empty-state" style="grid-column:1/-1"><i class="fa-solid fa-calendar-xmark"></i><p>Tidak ada data kehadiran</p></div>`);
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
                let m = currentCalMonth - 1, y = currentCalYear;
                if (m < 1) { m = 12; y--; }
                loadCalendar(m, y);
            });
            $('#btnNextMonth').click(() => {
                let m = currentCalMonth + 1, y = currentCalYear;
                if (m > 12) { m = 1; y++; }
                loadCalendar(m, y);
            });
        }
    </script>
@endsection