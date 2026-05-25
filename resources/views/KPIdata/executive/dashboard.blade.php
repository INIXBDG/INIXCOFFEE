@extends('databasekpi.berandaKPI')

@section('contentKPI')
    <style>
        .executive-card {
            border-radius: 12px;
            border: 1px solid rgba(0, 0, 0, 0.08);
            transition: all 0.2s ease
        }

        .executive-card:hover {
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            transform: translateY(-2px)
        }

        .metric-value {
            font-size: 2rem;
            font-weight: 700;
            line-height: 1.2
        }

        .metric-label {
            font-size: 0.875rem;
            opacity: 0.8
        }

        .matrix-card {
            aspect-ratio: 1;
            border-radius: 12px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s;
            border: 1px solid rgba(0, 0, 0, 0.08)
        }

        .matrix-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12)
        }

        .matrix-count {
            font-size: 2.5rem;
            font-weight: 800;
            line-height: 1
        }

        .matrix-label {
            font-size: 0.9rem;
            font-weight: 600;
            margin-top: 0.5rem
        }

        .matrix-desc {
            font-size: 0.75rem;
            opacity: 0.7;
            margin-top: 0.25rem
        }

        .bg-gradient-emerging {
            background: linear-gradient(135deg, #fbbf24, #f59e0b);
            color: #1f2937
        }

        .bg-gradient-high {
            background: linear-gradient(135deg, #10b981, #059669);
            color: #fff
        }

        .bg-gradient-support {
            background: linear-gradient(135deg, #f87171, #ef4444);
            color: #fff
        }

        .bg-gradient-core {
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            color: #fff
        }

        .chart-container {
            position: relative;
            height: 280px;
            width: 100%
        }

        .filter-select {
            border-radius: 8px;
            padding: 0.5rem 1rem;
            border: 1px solid rgba(0, 0, 0, 0.1);
            background: #fff;
            min-width: 140px;
            font-size: 0.875rem
        }

        .prediction-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600
        }

        .prediction-high {
            background: #d1fae5;
            color: #065f46
        }

        .prediction-medium {
            background: #fef3c7;
            color: #92400e
        }

        .prediction-low {
            background: #fee2e2;
            color: #991b1b
        }

        .loading-skeleton {
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: loading 1.5s infinite;
            border-radius: 6px
        }

        @keyframes loading {
            0% {
                background-position: 200% 0
            }

            100% {
                background-position: -200% 0
            }
        }

        .modal-employee-list {
            max-height: 450px;
            overflow-y: auto
        }

        .employee-item {
            padding: 0.75rem 1rem;
            border-radius: 8px;
            margin-bottom: 0.5rem;
            background: #f8fafc;
            border-left: 3px solid transparent;
            transition: all 0.15s
        }

        .employee-item:hover {
            background: #f1f5f9;
            border-left-color: #6366f1
        }

        .employee-name {
            font-weight: 600;
            font-size: 0.9rem
        }

        .employee-meta {
            font-size: 0.75rem;
            color: #64748b;
            margin-top: 0.25rem
        }

        .employee-score {
            font-size: 0.8rem;
            font-weight: 600
        }

        .score-badge {
            padding: 0.15rem 0.5rem;
            border-radius: 4px;
            font-size: 0.7rem;
            font-weight: 600
        }

        .score-high {
            background: #d1fae5;
            color: #065f46
        }

        .score-mid {
            background: #fef3c7;
            color: #92400e
        }

        .score-low {
            background: #fee2e2;
            color: #991b1b
        }

        .custom-scrollbar::-webkit-scrollbar {
            width: 4px
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: rgba(0, 0, 0, 0.2);
            border-radius: 2px
        }

        .empty-state {
            text-align: center;
            padding: 2rem;
            color: #64748b
        }

        .empty-state i {
            font-size: 2rem;
            margin-bottom: 0.5rem;
            opacity: 0.5
        }
    </style>

    <div class="content-wrapper">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-0">Executive Analytics</h4>
                <small class="text-muted">Dashboard untuk HRD, GM, dan Direktur Utama</small>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-outline-secondary btn-sm" id="btnRefresh">
                    <i class="bi bi-arrow-clockwise"></i> Refresh
                </button>
            </div>
        </div>

        <div class="card executive-card mb-4">
            <div class="card-body py-3">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label small mb-1">Divisi</label>
                        <select class="filter-select form-select form-select-sm" id="filterDivisi">
                            <option value="">Semua Divisi</option>
                            @foreach ($divisiList ?? [] as $div)
                                <option value="{{ $div }}">{{ $div }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small mb-1">Jabatan</label>
                        <select class="filter-select form-select form-select-sm" id="filterJabatan">
                            <option value="">Semua Jabatan</option>
                            @foreach ($jabatanList ?? [] as $jab)
                                <option value="{{ $jab }}">{{ $jab }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small mb-1">Tahun</label>
                        <select class="filter-select form-select form-select-sm" id="filterTahun">
                            @for ($y = date('Y'); $y >= 2023; $y--)
                                <option value="{{ $y }}" {{ $y == date('Y') ? 'selected' : '' }}>
                                    {{ $y }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small mb-1">Granularitas</label>
                        <select class="filter-select form-select form-select-sm" id="filterGranularity">
                            <option value="monthly">Bulanan</option>
                            <option value="quarterly">Kuartalan</option>
                            <option value="yearly">Tahunan</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-primary btn-sm w-100" id="btnApplyFilter">
                            <i class="bi bi-funnel"></i> Terapkan
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="card executive-card h-100">
                    <div class="card-body text-center py-4">
                        <div class="metric-value" id="metricAvgProgress">-</div>
                        <div class="metric-label">Rata-rata Progress</div>
                        <div class="mt-2">
                            <span class="prediction-badge prediction-medium" id="badgeTrend">-</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card executive-card h-100">
                    <div class="card-body text-center py-4">
                        <div class="metric-value" id="metricTotalTargets">-</div>
                        <div class="metric-label">Total Target</div>
                        <div class="mt-2 small text-muted">
                            <span id="metricCompleted">0</span> selesai
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card executive-card h-100">
                    <div class="card-body text-center py-4">
                        <div class="metric-value" id="metricHighPotential">-</div>
                        <div class="metric-label">High Potential</div>
                        <div class="mt-2 small text-muted">Karyawan berpotensi</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card executive-card h-100">
                    <div class="card-body text-center py-4">
                        <div class="metric-value" id="metricPrediction">-</div>
                        <div class="metric-label">Prediksi Next Period</div>
                        <div class="mt-2">
                            <small class="text-muted" id="predictionConfidence">-</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card executive-card">
                    <div class="card-header bg-transparent border-0 pt-3 pb-0">
                        <h6 class="mb-0">📈 Trend Performance</h6>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="chartTrend"></canvas>
                        </div>
                        <div class="mt-3 d-flex justify-content-between small text-muted">
                            <span id="trendInsight">-</span>
                            <span id="chartPeriod"></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card executive-card">
                    <div class="card-header bg-transparent border-0 pt-3 pb-0">
                        <h6 class="mb-0">🔮 Prediksi</h6>
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-3">
                            <div class="display-6 fw-bold" id="predictionValue">-</div>
                            <small class="text-muted">Estimasi periode berikutnya</small>
                        </div>
                        <div class="mb-3">
                            <label class="small text-muted mb-1">3 Periode Mendatang</label>
                            <div class="d-flex gap-2 justify-content-center">
                                <div class="text-center">
                                    <div class="fw-bold" id="predNext1">-</div>
                                    <small class="text-muted" style="font-size:0.7rem">P+1</small>
                                </div>
                                <div class="text-center">
                                    <div class="fw-bold" id="predNext2">-</div>
                                    <small class="text-muted" style="font-size:0.7rem">P+2</small>
                                </div>
                                <div class="text-center">
                                    <div class="fw-bold" id="predNext3">-</div>
                                    <small class="text-muted" style="font-size:0.7rem">P+3</small>
                                </div>
                            </div>
                        </div>
                        <div class="alert alert-light border mb-0" id="predictionRecommendation">
                            <small>-</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mt-4">
            <div class="card-body p-4">

                <!-- Header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h5 class="fw-semibold mb-1">Employee Potential Matrix</h5>
                        <small class="text-muted">Mapping performa dan growth karyawan</small>
                    </div>

                    <div class="dropdown">
                        <button class="btn btn-light border btn-sm dropdown-toggle" type="button"
                            data-bs-toggle="dropdown">
                            <i class="bi bi-funnel me-1"></i> Filter
                        </button>

                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                            <li><a class="dropdown-item" href="#" data-filter="all">Semua</a></li>
                            <li><a class="dropdown-item" href="#" data-filter="high_potential">High Potential</a>
                            </li>
                            <li><a class="dropdown-item" href="#" data-filter="core_players">Core Players</a></li>
                            <li><a class="dropdown-item" href="#" data-filter="emerging">Emerging</a></li>
                            <li><a class="dropdown-item" href="#" data-filter="needs_support">Needs Support</a>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Top Labels -->
                <div class="row text-center mb-2">
                    <div class="col-6">
                        <small class="text-muted fw-medium">Performance Rendah</small>
                    </div>
                    <div class="col-6">
                        <small class="text-muted fw-medium">Performance Tinggi</small>
                    </div>
                </div>

                <!-- Matrix -->
                <div class="row g-3">

                    <!-- Emerging -->
                    <div class="col-6">
                        <div class="card h-100 border-0 bg-warning bg-opacity-10 cursor-pointer" data-quadrant="emerging">

                            <div class="card-body text-center py-4">

                                <div class="mb-2">
                                    <span class="badge bg-warning text-dark rounded-pill px-3 py-2">
                                        Emerging
                                    </span>
                                </div>

                                <h3 class="fw-bold mb-0" id="countEmerging">0</h3>

                                <small class="text-muted">Potential Talent</small>
                            </div>
                        </div>
                    </div>

                    <!-- High Potential -->
                    <div class="col-6">
                        <div class="card h-100 border-0 bg-success bg-opacity-10 cursor-pointer" data-quadrant="high_potential">

                            <div class="card-body text-center py-4">

                                <div class="mb-2">
                                    <span class="badge bg-success rounded-pill px-3 py-2">
                                        High Potential
                                    </span>
                                </div>

                                <h3 class="fw-bold mb-0" id="countHighPotential">0</h3>

                                <small class="text-muted">Top Future Leaders</small>
                            </div>
                        </div>
                    </div>

                    <!-- Needs Support -->
                    <div class="col-6">
                        <div class="card h-100 border-0 bg-danger bg-opacity-10 cursor-pointer" data-quadrant="needs_support">

                            <div class="card-body text-center py-4">

                                <div class="mb-2">
                                    <span class="badge bg-danger rounded-pill px-3 py-2">
                                        Needs Support
                                    </span>
                                </div>

                                <h3 class="fw-bold mb-0" id="countNeedsSupport">0</h3>

                                <small class="text-muted">Need Improvement</small>
                            </div>
                        </div>
                    </div>

                    <!-- Core Players -->
                    <div class="col-6">
                        <div class="card h-100 border-0 bg-primary bg-opacity-10 cursor-pointer" data-quadrant="core_players">

                            <div class="card-body text-center py-4">

                                <div class="mb-2">
                                    <span class="badge bg-primary rounded-pill px-3 py-2">
                                        Core Players
                                    </span>
                                </div>

                                <h3 class="fw-bold mb-0" id="countCorePlayers">0</h3>

                                <small class="text-muted">Key Contributors</small>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Bottom Labels -->
                <div class="row text-center mt-3">
                    <div class="col-6">
                        <small class="text-muted fw-medium">Growth Rendah</small>
                    </div>
                    <div class="col-6">
                        <small class="text-muted fw-medium">Growth Tinggi</small>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <div class="modal fade" id="modalMatrix" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalMatrixTitle">Employee List</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="modalMatrixContent" class="modal-employee-list custom-scrollbar"></div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let chartTrend = null;
            let currentFilters = {
                divisi: '',
                jabatan: '',
                tahun: '{{ date('Y') }}',
                granularity: 'monthly'
            };
            let lastMatrixData = null;
            const endpoints = {
                trend: '{{ route('kpi.executive.analytics.trend') }}',
                prediction: '{{ route('kpi.executive.analytics.prediction') }}',
                matrix: '{{ route('kpi.executive.analytics.matrix') }}'
            };

            function initChart() {
                const ctx = document.getElementById('chartTrend');
                if (!ctx) return;
                chartTrend = new Chart(ctx.getContext('2d'), {
                    type: 'line',
                    data: {
                        labels: [],
                        datasets: [{
                            label: 'Average Progress (%)',
                            data: [],
                            borderColor: '#6366f1',
                            backgroundColor: 'rgba(99,102,241,0.1)',
                            borderWidth: 2,
                            fill: true,
                            tension: 0.4,
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
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                max: 100,
                                ticks: {
                                    callback: v => v + '%'
                                }
                            },
                            x: {
                                grid: {
                                    display: false
                                }
                            }
                        }
                    }
                });
            }

            async function fetchData() {
                showLoading();
                try {
                    const [trendRes, predictionRes, matrixRes] = await Promise.all([
                        fetch(endpoints.trend + '?' + new URLSearchParams(currentFilters)).then(r => r
                            .json()),
                        fetch(endpoints.prediction + '?' + new URLSearchParams(currentFilters)).then(
                            r => r.json()),
                        fetch(endpoints.matrix + '?' + new URLSearchParams(currentFilters)).then(r => r
                            .json())
                    ]);
                    lastMatrixData = matrixRes;
                    updateMetrics(trendRes, predictionRes, matrixRes);
                    updateChart(trendRes);
                    updateMatrix(matrixRes);
                } catch (error) {
                    console.error('Error fetching data:', error);
                    showAlert('Gagal memuat data analytics', 'error');
                } finally {
                    hideLoading();
                }
            }

            function updateMetrics(trendData, predictionData, matrixData) {
                const trend = trendData.trend || {};
                const summary = trendData.summary || {};
                const prediction = predictionData.prediction || {};
                const matrixSummary = matrixData.summary || {};
                const avgEl = document.getElementById('metricAvgProgress');
                if (avgEl) {
                    const val = summary.overall_average || trend[trendData.filters?.tahun + '-01']?.avg_progress ||
                        0;
                    avgEl.textContent = val.toFixed(1) + '%';
                }
                const totalEl = document.getElementById('metricTotalTargets');
                if (totalEl) totalEl.textContent = summary.total_targets || 0;
                const completedEl = document.getElementById('metricCompleted');
                if (completedEl) completedEl.textContent = summary.completed_targets || 0;
                const hpEl = document.getElementById('metricHighPotential');
                if (hpEl) hpEl.textContent = matrixSummary.high_potential_count || 0;
                const predEl = document.getElementById('metricPrediction');
                if (predEl) {
                    const predValue = prediction.next_period;
                    predEl.textContent = predValue !== null && predValue !== undefined ? predValue + '%' : '-';
                }
                const confEl = document.getElementById('predictionConfidence');
                if (confEl) confEl.textContent = prediction.confidence_level || '';
                const trendDir = trend.trend_direction || 'stable';
                const badge = document.getElementById('badgeTrend');
                if (badge) {
                    badge.textContent = trendDir === 'up' ? '▲ Meningkat' : (trendDir === 'down' ? '▼ Menurun' :
                        '→ Stabil');
                    badge.className = 'prediction-badge prediction-' + (trendDir === 'up' ? 'high' : (trendDir ===
                        'down' ? 'low' : 'medium'));
                }
                const predValEl = document.getElementById('predictionValue');
                if (predValEl) {
                    const pv = prediction.next_period;
                    predValEl.textContent = pv !== null && pv !== undefined ? pv + '%' : '-';
                }
                const p1 = document.getElementById('predNext1'),
                    p2 = document.getElementById('predNext2'),
                    p3 = document.getElementById('predNext3');
                if (p1) p1.textContent = prediction.next_3?.[0] ?? '-';
                if (p2) p2.textContent = prediction.next_3?.[1] ?? '-';
                if (p3) p3.textContent = prediction.next_3?.[2] ?? '-';
                const rec = document.getElementById('predictionRecommendation');
                if (rec) {
                    const recommendations = predictionData.recommendations || trendData.insights || [];
                    rec.innerHTML = recommendations.length > 0 ? '<small>💡 ' + recommendations[0] + '</small>' :
                        '<small>-</small>';
                }
                const insight = document.getElementById('trendInsight');
                if (insight) insight.textContent = trendData.insights?.[0] ||
                    'Data terkini berdasarkan filter yang dipilih';
                const period = document.getElementById('chartPeriod');
                if (period) period.textContent = currentFilters.tahun;
            }

            function updateChart(trendData) {
                if (!chartTrend) return;
                const trend = trendData.trend || {};
                const labels = Object.keys(trend).filter(k => !['count', 'trend_direction', 'trend_delta'].includes(
                    k)).sort();
                const data = labels.map(k => trend[k]?.avg_progress ?? 0);
                chartTrend.data.labels = labels;
                chartTrend.data.datasets[0].data = data;
                chartTrend.update();
            }

            function updateMatrix(matrixData) {
                const matrix = matrixData.matrix || {};
                const counts = {
                    high_potential: matrix.high_potential?.length || 0,
                    core_players: matrix.core_players?.length || 0,
                    emerging: matrix.emerging?.length || 0,
                    consistent: matrix.consistent?.length || 0,
                    needs_support: matrix.needs_support?.length || 0
                };
                const ids = ['countHighPotential', 'countCorePlayers', 'countEmerging', 'countNeedsSupport'];
                const keys = ['high_potential', 'core_players', 'emerging', 'needs_support'];
                ids.forEach((id, i) => {
                    const el = document.getElementById(id);
                    if (el) el.textContent = counts[keys[i]];
                });
            }

            function showLoading() {
                document.querySelectorAll('.metric-value').forEach(el => {
                    if (el) {
                        el.classList.add('loading-skeleton');
                        el.style.minHeight = '2rem';
                        el.textContent = '';
                    }
                });
            }

            function hideLoading() {
                document.querySelectorAll('.metric-value').forEach(el => {
                    if (el) {
                        el.classList.remove('loading-skeleton');
                        el.style.minHeight = 'auto';
                    }
                });
            }

            function showAlert(message, type = 'info') {
                const alert = document.createElement('div');
                alert.className =
                    `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show position-fixed top-0 end-0 m-3`;
                alert.style.zIndex = '9999';
                alert.innerHTML =
                    `${message}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>`;
                document.body.appendChild(alert);
                setTimeout(() => {
                    if (alert.parentNode) alert.remove();
                }, 5000);
            }

            function renderEmployeeList(employees) {
                if (!employees || employees.length === 0) {
                    return '<div class="empty-state"><i class="bi bi-people"></i><p>Tidak ada data karyawan</p></div>';
                }
                return employees.map(emp => {
                    const pScore = emp.performance_score || 0;
                    const gScore = emp.growth_score || 0;
                    const pClass = pScore >= 75 ? 'score-high' : (pScore >= 50 ? 'score-mid' : 'score-low');
                    const gClass = gScore >= 70 ? 'score-high' : (gScore >= 40 ? 'score-mid' : 'score-low');
                    const initials = (emp.nama || '').split(' ').map(n => n[0]).slice(0, 2).join('')
                        .toUpperCase();
                    return `
                        <div class="employee-item">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="employee-name">${emp.nama || '-'}</div>
                                    <div class="employee-meta">${emp.jabatan || '-'} • ${emp.divisi || '-'}</div>
                                </div>
                                <div class="text-end">
                                    <span class="score-badge ${pClass}">P: ${pScore}%</span>
                                    <span class="score-badge ${gClass} ms-1">G: ${gScore}%</span>
                                </div>
                            </div>
                        </div>
                    `;
                }).join('');
            }

            function showModalEmployees(filter) {
                const titleEl = document.getElementById('modalMatrixTitle');
                if (titleEl) {
                    titleEl.textContent = filter === 'all' ? 'Semua Karyawan' :
                        filter.split('_').map(w => w.charAt(0).toUpperCase() + w.slice(1)).join(' ');
                }
                const contentEl = document.getElementById('modalMatrixContent');
                if (!contentEl) return;
                if (!lastMatrixData) {
                    contentEl.innerHTML = '<div class="empty-state"><p>Memuat data...</p></div>';
                    return;
                }
                const matrix = lastMatrixData.matrix || {};
                const employees = filter === 'all' ?
                    Object.values(matrix).flat().filter(e => e) :
                    (matrix[filter] || []);
                contentEl.innerHTML = renderEmployeeList(employees);
            }

            document.getElementById('btnApplyFilter')?.addEventListener('click', function() {
                currentFilters = {
                    divisi: document.getElementById('filterDivisi')?.value || '',
                    jabatan: document.getElementById('filterJabatan')?.value || '',
                    tahun: document.getElementById('filterTahun')?.value || '{{ date('Y') }}',
                    granularity: document.getElementById('filterGranularity')?.value || 'monthly'
                };
                fetchData();
            });

            document.getElementById('btnRefresh')?.addEventListener('click', fetchData);

            document.querySelectorAll('#modalMatrix .dropdown-item').forEach(item => {
                item.addEventListener('click', function(e) {
                    e.preventDefault();
                    const filter = this.dataset.filter;
                    showModalEmployees(filter);
                });
            });

            document.querySelectorAll('.matrix-card').forEach(card => {
                card.addEventListener('click', function() {
                    const quadrant = this.dataset.quadrant;
                    showModalEmployees(quadrant);
                });
            });

            initChart();
            fetchData();
        });
    </script>
@endsection
