@extends('layouts_office.app')

@section('office_contents')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .analytics-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
            margin-bottom: 20px;
        }

        .metric-value {
            font-size: 2rem;
            font-weight: 700;
            color: #0d6efd;
        }

        .metric-label {
            font-size: 13px;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .chart-container {
            position: relative;
            height: 300px;
        }

        .risk-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .risk-high {
            background: #fee2e2;
            color: #dc2626;
        }

        .risk-medium {
            background: #fef3c7;
            color: #d97706;
        }

        .risk-low {
            background: #dcfce7;
            color: #16a34a;
        }

        .opportunity-card {
            border-left: 4px solid #0d6efd;
            background: #f8fafc;
            padding: 16px;
            margin-bottom: 12px;
            border-radius: 0 8px 8px 0;
        }

        .opportunity-card.high {
            border-left-color: #dc2626;
            background: #fef2f2;
        }

        .opportunity-card.medium {
            border-left-color: #d97706;
            background: #fffbeb;
        }

        .filter-bar {
            background: #f8f9fa;
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 24px;
        }

        .loading-skeleton {
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: loading 1.5s infinite;
            border-radius: 4px;
            height: 20px;
        }

        @keyframes loading {
            0% {
                background-position: 200% 0;
            }

            100% {
                background-position: -200% 0;
            }
        }

        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 4px;
        }

        .calendar-day {
            aspect-ratio: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            font-size: 12px;
            cursor: pointer;
            transition: transform 0.2s;
            border: 1px solid #e5e7eb;
        }

        .calendar-day:hover {
            transform: scale(1.05);
        }

        .calendar-day .day-num {
            font-weight: 600;
        }

        .calendar-day .day-name {
            font-size: 10px;
            color: #6b7280;
        }

        .calendar-day .late-indicator {
            font-size: 10px;
            color: #dc2626;
            font-weight: 500;
        }

        .calendar-day.holiday {
            background: #fef3c7;
        }

        .calendar-day.late {
            background: #fee2e2;
        }

        .calendar-day.present {
            background: #dcfce7;
        }

        .calendar-day.weekend {
            background: #f3f4f6;
            color: #9ca3af;
        }

        .calendar-day.today {
            border: 2px solid #0d6efd;
        }
    </style>

    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4>Attendance Intelligence Dashboard</h4>
            <div>
                <button class="btn btn-outline-primary btn-sm me-2" id="btnRefresh"><i class="bi bi-arrow-clockwise"></i>
                    Refresh</button>
                <button class="btn btn-success btn-sm me-2" id="btnExportCsv"><i class="bi bi-file-earmark-spreadsheet"></i>Excel</button>
                <button class="btn btn-danger btn-sm" id="btnExportPdf"><i class="bi bi-file-earmark-pdf"></i> PDF</button>
            </div>
        </div>

        <div class="filter-bar">
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
                    <button class="btn btn-primary btn-sm w-100" id="btnApplyFilter">Terapkan Filter</button>
                </div>
            </div>
        </div>

        <div class="row mb-4 justify-content-center">
            <div class="col-md-4 col-12 mb-3">
                <div class="analytics-card p-3 text-center">
                    <div class="metric-value" id="metricAttendanceRate">-</div>
                    <div class="metric-label">Attendance Rate</div>
                </div>
            </div>

            <div class="col-md-4 col-12 mb-3">
                <div class="analytics-card p-3 text-center">
                    <div class="metric-value text-success" id="metricPunctuality">-</div>
                    <div class="metric-label">Punctuality Rate</div>
                </div>
            </div>

            <div class="col-md-4 col-12 mb-3">
                <div class="analytics-card p-3 text-center">
                    <div class="metric-value text-warning" id="metricAvgLate">-</div>
                    <div class="metric-label">Avg Late (menit)</div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="analytics-card p-3">
                    <h6 class="mb-3">Trend Keterlambatan Harian</h6>
                    <div class="chart-container"><canvas id="trendChart"></canvas></div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="analytics-card p-3">
                    <h6 class="mb-3">Perbandingan per Divisi</h6>
                    <div class="chart-container"><canvas id="deptChart"></canvas></div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-6">
                <div class="analytics-card p-3">
                    <h6 class="mb-3">Attendance Heatmap (Jam Masuk)</h6>
                    <div class="chart-container"><canvas id="heatmapChart"></canvas></div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="analytics-card p-3">
                    <h6 class="mb-3">Distribusi Risk Level</h6>
                    <div class="chart-container"><canvas id="riskChart"></canvas></div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col">
                <div class="analytics-card p-4 mt-4">
                    <h5 class="mb-4">Peluang & Rekomendasi Strategis</h5>
                    <div id="opportunitiesContainer"></div>
                </div>
            </div>
            <div class="col">
                <div class="analytics-card p-4 mt-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5>Kalender Kehadiran</h5>
                        <div>
                            <select id="calendarEmployee" class="form-select form-select-sm w-auto me-2">
                                <option value="">Semua Karyawan</option>
                            </select>
                            <button class="btn btn-sm btn-outline-secondary" id="btnPrevMonth">◀</button>
                            <button class="btn btn-sm btn-outline-secondary" id="btnNextMonth">▶</button>
                        </div>
                    </div>

                    <div class="legend mb-3 small">
                        <span class="badge me-2" style="background:#fee2e2;color:#dc2626">● Telat</span>
                        <span class="badge me-2" style="background:#fef3c7;color:#d97706">● Libur/Cuti</span>
                        <span class="badge me-2" style="background:#dcfce7;color:#16a34a">● Hadir</span>
                    </div>

                    <div id="attendanceCalendar" class="calendar-grid"></div>
                </div>
            </div>

        </div>

        <div class="analytics-card p-4 mt-4">
            <h5 class="mb-4">Prediksi & Target Masa Depan</h5>
            <div class="row">
                <div class="col-md-4">
                    <div class="p-3 border rounded bg-light">
                        <strong class="d-block mb-2">Bulan Depan</strong>
                        <div class="metric-value" id="predNextMonth">-</div>
                        <small class="text-muted" id="predNextMonthConf">-</small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-3 border rounded bg-light">
                        <strong class="d-block mb-2">Kuartal Depan</strong>
                        <div class="metric-value" id="predNextQuarter">-</div>
                        <small class="text-muted" id="predNextQuarterConf">-</small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-3 border rounded bg-light">
                        <strong class="d-block mb-2">Tahun Depan</strong>
                        <div class="metric-value" id="predNextYear">-</div>
                        <small class="text-muted" id="predNextYearConf">-</small>
                    </div>
                </div>
            </div>
            <div class="mt-4">
                <h6>Milestone Target:</h6>
                <div id="milestonesContainer"></div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const formatPercent = v => `${v}%`;
        const formatMinutes = v => `${v} menit`;
        let trendChart, deptChart, heatmapChart, riskChart;

        $(document).ready(function() {
            initFilters();
            loadAnalytics();

            $('#btnApplyFilter, #btnRefresh').click(() => loadAnalytics());
            $('#btnExportCsv').click(() => exportReport('csv'));
            $('#btnExportPdf').click(() => exportReport('pdf'));
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
            $.get("{{ route('office.HR.absensi.analytics') }}", getFilterParams(), function(res) {
                if (!res.success) {
                    alert(res.message);
                    return;
                }
                renderSummary(res.summary);
                renderCharts(res.charts);
                renderOpportunities(res.opportunities);
                renderPredictions(res.predictions);
                renderRiskTable(res.charts.risk_distribution, res.risk_analysis?.list || []);
            }).fail(() => alert('Gagal memuat data analytics'));
        }

        function renderSummary(s) {
            $('#metricAttendanceRate').text(formatPercent(s.attendance_rate));
            $('#metricPunctuality').text(formatPercent(s.punctuality_rate));
            $('#metricAvgLate').text(formatMinutes(s.avg_late_minutes));
            $('#metricAbsent').text(s.tidak_hadir);
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
                        borderColor: '#dc2626',
                        backgroundColor: 'rgba(220,38,38,0.1)',
                        fill: true,
                        tension: 0.3
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
                            max: 100
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
                            backgroundColor: '#0d6efd'
                        },
                        {
                            label: 'Punctuality Rate',
                            data: c.department_comparison.map(d => d.punctuality_rate),
                            backgroundColor: '#198754'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100
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
                        backgroundColor: '#6366f1'
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
                    }
                }
            });

            riskChart = new Chart(document.getElementById('riskChart'), {
                type: 'doughnut',
                data: {
                    labels: ['High Risk', 'Medium Risk', 'Low Risk'],
                    datasets: [{
                        data: [c.risk_distribution.high, c.risk_distribution.medium, c.risk_distribution
                            .low
                        ],
                        backgroundColor: ['#dc2626', '#f59e0b', '#16a34a']
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        }

        function destroyCharts() {
            [trendChart, deptChart, heatmapChart, riskChart].forEach(c => c?.destroy());
        }

        function renderOpportunities(opp) {
            const container = $('#opportunitiesContainer').empty();
            opp.forEach(o => {
                const priorityClass = o.priority === 'high' ? 'high' : (o.priority === 'medium' ? 'medium' : '');
                container.append(`
                <div class="opportunity-card ${priorityClass}">
                    <div class="d-flex justify-content-between">
                        <strong>${o.title}</strong>
                        <span class="badge bg-${o.priority === 'high' ? 'danger' : (o.priority === 'medium' ? 'warning' : 'secondary')}">${o.priority.toUpperCase()}</span>
                    </div>
                    <p class="small mb-2">${o.description}</p>
                    <div class="d-flex justify-content-between small text-muted">
                        <span>${o.impact}</span>
                        <span>${o.timeline}</span>
                        <span>${o.effort} effort</span>
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
            p.milestones.forEach(m => {
                ms.append(
                    `<div class="alert alert-light border mb-2"><strong>Target ${m.target}%</strong> dalam ${m.timeline}<br><small>${m.actions.join(' • ')}</small></div>`
                );
            });
        }

        function renderRiskTable(dist, list) {
            const body = $('#riskTableBody').empty();
            if (list.length === 0) {
                body.append(
                    '<tr><td colspan="5" class="text-center text-muted">Tidak ada karyawan dengan risk tinggi</td></tr>'
                );
                return;
            }
            list.forEach(r => {
                const badgeClass = r.risk_level === 'high' ? 'risk-high' : (r.risk_level === 'medium' ?
                    'risk-medium' : 'risk-low');
                body.append(`
                <tr>
                    <td>${r.nama}</td>
                    <td>${r.divisi}</td>
                    <td><strong>${r.risk_score}</strong></td>
                    <td><span class="risk-badge ${badgeClass}">${r.risk_level.toUpperCase()}</span></td>
                    <td class="small">${r.recommendation}</td>
                </tr>
            `);
            });
        }

        function exportReport(format) {
            const params = new URLSearchParams(getFilterParams());
            params.append('format', format);
            window.location.href = `{{ route('office.HR.absensi.export') }}?${params.toString()}`;
        }

        let topLateChart = null,
            currentCalMonth = null,
            currentCalYear = null;

        function loadDivisionStats() {
            $.get("{{ route('office.HR.absensi.division.stats') }}", {
                month: $('#filterBulan').val(),
                year: $('#filterTahun').val()
            }, function(res) {
                if (!res.success) return;
                renderDivisionStats(res.data);
            });
        }

        function renderDivisionStats(data) {
            const tbody = $('#divisionStatsBody').empty();
            const dateFilter = $('#statDateFilter').val();

            data.filter(d => dateFilter === 'all' || d.date === dateFilter).forEach(day => {
                Object.entries(day.divisions).forEach(([divisi, stats]) => {
                    const rateClass = stats.rate >= 90 ? 'text-success' : (stats.rate >= 75 ?
                        'text-warning' : 'text-danger');
                    tbody.append(`
                <tr>
                    <td>${day.day}/${$('#filterBulan').val()}</td>
                    <td><small>${divisi}</small></td>
                    <td class="text-center">${stats.total}</td>
                    <td class="text-center">${stats.hadir}</td>
                    <td class="text-center text-danger">${stats.telat}</td>
                    <td class="text-center text-warning">${stats.cuti}</td>
                    <td class="text-center fw-bold ${rateClass}">${stats.rate}%</td>
                </tr>
            `);
                });
            });

            if ($('#statDateFilter option').length === 1) {
                data.forEach(d => {
                    $('#statDateFilter').append(
                        `<option value="${d.date}">${d.day}/${$('#filterBulan').val()}</option>`);
                });
            }
        }

        function loadTopLate() {
            $.get("{{ route('office.HR.absensi.top.late') }}", {
                month: $('#filterBulan').val(),
                year: $('#filterTahun').val(),
                limit: 10
            }, function(res) {
                if (!res.success) return;
                renderTopLate(res.data);
            });
        }

        function renderTopLate(data) {
            if (topLateChart) topLateChart.destroy();

            const ctx = document.getElementById('topLateChart');
            topLateChart = new Chart(ctx, {
                type: 'horizontalBar',
                data: {
                    labels: data.map(d => d.nama?.split(' ')[0] || '-'),
                    datasets: [{
                        label: 'Menit Telat',
                        data: data.map(d => d.total_late_minutes),
                        backgroundColor: '#dc2626',
                        borderColor: '#b91c1c',
                        borderWidth: 1
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
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Menit'
                            }
                        }
                    }
                }
            });

            const list = $('#topLateList').empty();
            data.forEach((d, i) => {
                list.append(`
                    <div class="d-flex align-items-center py-2 border-bottom">
                        <span class="badge bg-danger rounded-circle me-2" style="width:24px;height:24px;line-height:24px">${i+1}</span>
                        <img src="${d.foto ? '/storage/'+d.foto : '/css/default-profile.jpg'}" class="rounded-circle me-2" style="width:32px;height:32px;object-fit:cover">
                        <div class="flex-grow-1">
                            <strong class="d-block">${d.nama}</strong>
                            <small class="text-muted">${d.jabatan} • ${d.divisi}</small>
                        </div>
                        <div class="text-end">
                            <div class="fw-bold text-danger">${d.total_late_minutes}m</div>
                            <small class="text-muted">${d.late_count}x telat</small>
                        </div>
                    </div>
                `);
            });
        }

        function loadCalendar(month, year) {
            currentCalMonth = month;
            currentCalYear = year;
            $.get("{{ route('office.HR.absensi.calendar') }}", {
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
                container.append(`<div class="text-center fw-bold small py-2 text-muted">${d}</div>`);
            });

            const firstDay = new Date(year, month - 1, 1).getDay();
            for (let i = 0; i < firstDay; i++) {
                container.append('<div></div>');
            }

            days.forEach(d => {
                const isToday = d.date === today;
                const classes = [`calendar-day`, d.status, isToday ? 'today' : ''];
                if (d.bgColor) classes.push(`style="background:${d.bgColor}"`);

                container.append(`
            <div class="${classes.join(' ')}" title="${d.status === 'late' ? 'Telat '+d.late_minutes+'m' : (d.status === 'leave' ? 'Cuti' : (d.status === 'holiday' ? 'Libur' : 'Hadir'))}">
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

        function loadCalendarEmployees() {
            $.get("{{ route('office.HR.employee.data') }}", {
                periode: 'all'
            }, function(res) {
                if (res.stats) {}
            });
        }

        function loadData(page = 1) {
            loadDivisionStats();
            loadTopLate();
            loadCalendar($('#filterBulan').val(), $('#filterTahun').val());
        }

        $(document).ready(function() {
            initCalendarControls();
            loadCalendarEmployees();

            $('#filterBulan, #filterTahun').change(() => {
                loadDivisionStats();
                loadTopLate();
                loadCalendar($('#filterBulan').val(), $('#filterTahun').val());
            });
        });
    </script>
@endsection
