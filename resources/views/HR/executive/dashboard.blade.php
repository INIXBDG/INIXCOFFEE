@extends('layout_HR.app')
@section('content_HR')
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

        .matrix-cell {
            aspect-ratio: 1.2;
            border-radius: 8px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s;
            border: 2px solid rgba(0, 0, 0, 0.1);
            padding: 1rem;
            text-align: center;
            min-height: 140px;
            user-select: none;
        }

        .matrix-cell:hover {
            transform: scale(1.03);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
            z-index: 10;
            border-color: rgba(99, 102, 241, 0.5);
        }

        .matrix-cell:active {
            transform: scale(0.98);
        }

        .matrix-count {
            font-size: 2rem;
            font-weight: 800;
            line-height: 1;
            margin-bottom: 0.5rem;
        }

        .matrix-title {
            font-size: 0.85rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
        }

        .matrix-desc {
            font-size: 0.7rem;
            opacity: 0.85;
            line-height: 1.2;
        }

        .matrix-wrapper {
            display: flex;
            align-items: stretch;
        }

        .matrix-axis-vertical {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding-right: 1rem;
            min-width: 120px;
        }

        .axis-label-vertical {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 1rem;
            color: #374151;
            writing-mode: vertical-rl;
            transform: rotate(180deg);
            text-orientation: mixed;
            white-space: nowrap;
            height: 100%;
        }

        .matrix-grid-container {
            flex: 1;
        }

        /* Update existing matrix-grid */
        .matrix-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 0.75rem;
        }

        .matrix-axis-horizontal {
            display: flex;
            justify-content: space-between;
            margin-top: 0.75rem;
            padding-left: 120px;
            /* Space for vertical axis */
        }

        .bg-star {
            background: linear-gradient(135deg, #22c55e, #16a34a);
            color: white;
        }

        .bg-high-potential {
            background: linear-gradient(135deg, #84cc16, #65a30d);
            color: white;
        }

        .bg-potential-gem {
            background: linear-gradient(135deg, #facc15, #eab308);
            color: #1f2937;
        }

        .bg-high-performer {
            background: linear-gradient(135deg, #4ade80, #22c55e);
            color: white;
        }

        .bg-core-player {
            background: linear-gradient(135deg, #fde047, #facc15);
            color: #1f2937;
        }

        .bg-inconsistent {
            background: linear-gradient(135deg, #f87171, #ef4444);
            color: white;
        }

        .bg-solid-performer {
            background: linear-gradient(135deg, #fbbf24, #f59e0b);
            color: #1f2937;
        }

        .bg-average-performer {
            background: linear-gradient(135deg, #f87171, #ef4444);
            color: white;
        }

        .bg-risk {
            background: linear-gradient(135deg, #dc2626, #b91c1c);
            color: white;
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
            font-size: 0.875rem;
            cursor: pointer;
        }

        .filter-select:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
            outline: none;
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
            max-height: 500px;
            overflow-y: auto
        }

        .employee-item {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 0.75rem;
            background: #f8fafc;
            border-left: 4px solid #6366f1;
            transition: all 0.15s;
            cursor: pointer;
        }

        .employee-item:hover {
            background: #f1f5f9;
            transform: translateX(4px);
            border-left-color: #4f46e5;
        }

        .employee-name {
            font-weight: 700;
            font-size: 0.95rem;
            margin-bottom: 0.25rem;
        }

        .employee-meta {
            font-size: 0.8rem;
            color: #64748b;
            margin-bottom: 0.5rem;
        }

        .score-container {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .score-badge {
            padding: 0.35rem 0.65rem;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
        }

        .score-high {
            background: #d1fae5;
            color: #065f46;
        }

        .score-mid {
            background: #fef3c7;
            color: #92400e;
        }

        .score-low {
            background: #fee2e2;
            color: #991b1b;
        }

        .custom-scrollbar::-webkit-scrollbar {
            width: 6px
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: rgba(0, 0, 0, 0.2);
            border-radius: 3px
        }

        .empty-state {
            text-align: center;
            padding: 3rem 2rem;
            color: #64748b
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.5
        }

        .axis-label {
            font-weight: 600;
            font-size: 0.9rem;
            color: #374151;
        }

        .matrix-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 0.75rem;
        }

        .nav-pills-custom .nav-link {
            border-radius: 8px;
            padding: 0.5rem 1rem;
            font-weight: 600;
            font-size: 0.875rem;
            cursor: pointer;
            border: 1px solid transparent;
        }

        .nav-pills-custom .nav-link:hover {
            background: #f1f5f9;
        }

        .nav-pills-custom .nav-link.active {
            background: #6366f1;
            color: white;
            border-color: #4f46e5;
        }

        .btn-apply {
            transition: all 0.2s;
        }

        .btn-apply:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
        }

        .btn-apply:active {
            transform: translateY(0);
        }

        .btn-refresh {
            transition: all 0.2s;
        }

        .btn-refresh:hover {
            background: #f1f5f9;
        }

        .btn-refresh:active i {
            animation: spin 0.5s linear;
        }

        @keyframes spin {
            from {
                transform: rotate(0deg);
            }

            to {
                transform: rotate(360deg);
            }
        }

        .matrix-filter-dropdown .dropdown-item {
            cursor: pointer;
            padding: 0.5rem 1rem;
        }

        .matrix-filter-dropdown .dropdown-item:hover {
            background: #f1f5f9;
        }

        .matrix-filter-dropdown .dropdown-item.active {
            background: #6366f1;
            color: white;
        }

        .toast-container {
            position: fixed;
            top: 1rem;
            right: 1rem;
            z-index: 9999;
        }
    </style>

    <div class="content-wrapper m-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-0">Executive Analytics</h4>
                <small class="text-muted">Dashboard untuk HRD, GM, dan Direktur Utama</small>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-outline-secondary btn-sm btn-refresh" id="btnRefresh" title="Refresh Data">
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
                        <button class="btn btn-primary btn-sm w-100 btn-apply" id="btnApplyFilter">
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
                        <h6 class="mb-0">Trend Performance</h6>
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
                        <h6 class="mb-0">Prediksi</h6>
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
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h5 class="fw-semibold mb-1">Employee Potential Matrix</h5>
                        <small class="text-muted">Mapping performa dan potensi karyawan</small>
                    </div>
                    <ul class="nav nav-pills nav-pills-custom" id="matrixTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="kpi-tab" data-bs-toggle="pill" data-bs-target="#kpi"
                                type="button" role="tab">
                                <i class="bi bi-graph-up me-1"></i> Performance (KPI)
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="assessment-tab" data-bs-toggle="pill"
                                data-bs-target="#assessment" type="button" role="tab">
                                <i class="bi bi-people me-1"></i> Assessment (360°)
                            </button>
                        </li>
                    </ul>
                </div>

                <div class="tab-content" id="matrixTabContent">
                    <div class="tab-pane fade show active" id="kpi" role="tabpanel">
                        <div class="mb-3 d-flex justify-content-between align-items-center">
                            <div class="axis-label">Performance vs Potential Matrix (KPI Based)</div>
                            <div class="text-end">
                                <small class="text-muted">Klik kotak untuk lihat detail karyawan</small>
                            </div>
                        </div>

                        <div class="matrix-wrapper">
                            <div class="matrix-axis-vertical">
                                <div class="axis-label-vertical">High Potential</div>
                                <div class="axis-label-vertical">Moderate Potential</div>
                                <div class="axis-label-vertical">Low Potential</div>
                            </div>

                            <!-- Matrix Grid -->
                            <div class="matrix-grid-container">
                                <div class="matrix-grid mb-3">
                                    <div class="matrix-cell bg-potential-gem" data-quadrant="potential_gem"
                                        data-type="kpi">
                                        <div class="matrix-count" id="countPotentialGem">0</div>
                                        <div class="matrix-title">"Potential Gem"</div>
                                        <div class="matrix-desc">High Potential<br>Low Performance</div>
                                    </div>
                                    <div class="matrix-cell bg-high-potential" data-quadrant="high_potential"
                                        data-type="kpi">
                                        <div class="matrix-count" id="countHighPotential">0</div>
                                        <div class="matrix-title">"High Potential"</div>
                                        <div class="matrix-desc">High Potential<br>Moderate Performance</div>
                                    </div>
                                    <div class="matrix-cell bg-star" data-quadrant="star" data-type="kpi">
                                        <div class="matrix-count" id="countStar">0</div>
                                        <div class="matrix-title">"Star"</div>
                                        <div class="matrix-desc">High Potential<br>High Performance</div>
                                    </div>
                                    <div class="matrix-cell bg-inconsistent" data-quadrant="inconsistent"
                                        data-type="kpi">
                                        <div class="matrix-count" id="countInconsistent">0</div>
                                        <div class="matrix-title">"Inconsistent Player"</div>
                                        <div class="matrix-desc">Moderate Potential<br>Low Performance</div>
                                    </div>
                                    <div class="matrix-cell bg-core-player" data-quadrant="core_player" data-type="kpi">
                                        <div class="matrix-count" id="countCorePlayer">0</div>
                                        <div class="matrix-title">"Core Player"</div>
                                        <div class="matrix-desc">Moderate Potential<br>Moderate Performance</div>
                                    </div>
                                    <div class="matrix-cell bg-high-performer" data-quadrant="high_performer"
                                        data-type="kpi">
                                        <div class="matrix-count" id="countHighPerformer">0</div>
                                        <div class="matrix-title">"High Performer"</div>
                                        <div class="matrix-desc">Moderate Potential<br>High Performance</div>
                                    </div>
                                    <div class="matrix-cell bg-risk" data-quadrant="risk" data-type="kpi">
                                        <div class="matrix-count" id="countRisk">0</div>
                                        <div class="matrix-title">"Risk"</div>
                                        <div class="matrix-desc">Low Potential<br>Low Performance</div>
                                    </div>
                                    <div class="matrix-cell bg-average-performer" data-quadrant="average_performer"
                                        data-type="kpi">
                                        <div class="matrix-count" id="countAveragePerformer">0</div>
                                        <div class="matrix-title">"Average Performer"</div>
                                        <div class="matrix-desc">Low Potential<br>Moderate Performance</div>
                                    </div>
                                    <div class="matrix-cell bg-solid-performer" data-quadrant="solid_performer"
                                        data-type="kpi">
                                        <div class="matrix-count" id="countSolidPerformer">0</div>
                                        <div class="matrix-title">"Solid Performer"</div>
                                        <div class="matrix-desc">Low Potential<br>High Performance</div>
                                    </div>
                                </div>

                                <!-- Horizontal Axis (Performance) -->
                                <div class="matrix-axis-horizontal">
                                    <div class="col-4 text-center" style="font-size: 1rem;">Low Performance</div>
                                    <div class="col-4 text-center" style="font-size: 1rem;">ModeratePerformance</div>
                                    <div class="col-4 text-center" style="font-size: 1rem;">High Performance</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="assessment" role="tabpanel">
                        <div class="mb-3 d-flex justify-content-between align-items-center">
                            <div class="axis-label">Performance vs Potential Matrix (360° Assessment)</div>
                            <div class="text-end">
                                <small class="text-muted">Klik kotak untuk lihat detail karyawan</small>
                            </div>
                        </div>

                        <div class="matrix-wrapper">
                            <div class="matrix-axis-vertical">
                                <div class="axis-label-vertical">High Potential</div>
                                <div class="axis-label-vertical">Moderate Potential</div>
                                <div class="axis-label-vertical">Low Potential</div>
                            </div>

                            <!-- Matrix Grid -->
                            <div class="matrix-grid-container">
                                <div class="matrix-grid mb-3">
                                    <div class="matrix-cell bg-potential-gem" data-quadrant="potential_gem"
                                        data-type="assessment">
                                        <div class="matrix-count" id="countPotentialGem360">0</div>
                                        <div class="matrix-title">"Potential Gem"</div>
                                        <div class="matrix-desc">High Potential<br>Low Performance</div>
                                    </div>
                                    <div class="matrix-cell bg-high-potential" data-quadrant="high_potential"
                                        data-type="assessment">
                                        <div class="matrix-count" id="countHighPotential360">0</div>
                                        <div class="matrix-title">"High Potential"</div>
                                        <div class="matrix-desc">High Potential<br>Moderate Performance</div>
                                    </div>
                                    <div class="matrix-cell bg-star" data-quadrant="star" data-type="assessment">
                                        <div class="matrix-count" id="countStar360">0</div>
                                        <div class="matrix-title">"Star"</div>
                                        <div class="matrix-desc">High Potential<br>High Performance</div>
                                    </div>
                                    <div class="matrix-cell bg-inconsistent" data-quadrant="inconsistent"
                                        data-type="assessment">
                                        <div class="matrix-count" id="countInconsistent360">0</div>
                                        <div class="matrix-title">"Inconsistent Player"</div>
                                        <div class="matrix-desc">Moderate Potential<br>Low Performance</div>
                                    </div>
                                    <div class="matrix-cell bg-core-player" data-quadrant="core_player"
                                        data-type="assessment">
                                        <div class="matrix-count" id="countCorePlayer360">0</div>
                                        <div class="matrix-title">"Core Player"</div>
                                        <div class="matrix-desc">Moderate Potential<br>Moderate Performance</div>
                                    </div>
                                    <div class="matrix-cell bg-high-performer" data-quadrant="high_performer"
                                        data-type="assessment">
                                        <div class="matrix-count" id="countHighPerformer360">0</div>
                                        <div class="matrix-title">"High Performer"</div>
                                        <div class="matrix-desc">Moderate Potential<br>High Performance</div>
                                    </div>
                                    <div class="matrix-cell bg-risk" data-quadrant="risk" data-type="assessment">
                                        <div class="matrix-count" id="countRisk360">0</div>
                                        <div class="matrix-title">"Risk"</div>
                                        <div class="matrix-desc">Low Potential<br>Low Performance</div>
                                    </div>
                                    <div class="matrix-cell bg-average-performer" data-quadrant="average_performer"
                                        data-type="assessment">
                                        <div class="matrix-count" id="countAveragePerformer360">0</div>
                                        <div class="matrix-title">"Average Performer"</div>
                                        <div class="matrix-desc">Low Potential<br>Moderate Performance</div>
                                    </div>
                                    <div class="matrix-cell bg-solid-performer" data-quadrant="solid_performer"
                                        data-type="assessment">
                                        <div class="matrix-count" id="countSolidPerformer360">0</div>
                                        <div class="matrix-title">"Solid Performer"</div>
                                        <div class="matrix-desc">Low Potential<br>High Performance</div>
                                    </div>
                                </div>

                                <!-- Horizontal Axis (Performance) -->
                                <div class="matrix-axis-horizontal">
                                    <div class="col-4 text-center"><small
                                            class="text-muted fw-medium bg-light px-2 py-1 rounded">Low Performance</small>
                                    </div>
                                    <div class="col-4 text-center"><small
                                            class="text-muted fw-medium bg-light px-2 py-1 rounded">Moderate
                                            Performance</small></div>
                                    <div class="col-4 text-center"><small
                                            class="text-muted fw-medium bg-light px-2 py-1 rounded">High
                                            Performance</small></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalMatrix" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalMatrixTitle">Employee List</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="modalMatrixContent" class="modal-employee-list custom-scrollbar"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="toast-container" id="toastContainer"></div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let chartTrend = null;
            let isFetching = false;
            let currentFilters = {
                divisi: '',
                jabatan: '',
                tahun: '{{ date('Y') }}',
                granularity: 'monthly'
            };
            let lastMatrixData = {
                kpi: null,
                assessment: null
            };

            const endpoints = {
                trend: '{{ route('HR.executive.analytics.trend') }}',
                prediction: '{{ route('HR.executive.analytics.prediction') }}',
                matrix: '{{ route('HR.executive.analytics.matrix') }}'
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
                if (isFetching) return;
                isFetching = true;
                showLoading();

                try {
                    const params = new URLSearchParams(currentFilters);
                    const [trendRes, predictionRes, matrixKpiRes, matrixAssessmentRes] = await Promise.all([
                        fetch(endpoints.trend + '?' + params).then(r => r.json()),
                        fetch(endpoints.prediction + '?' + params).then(r => r.json()),
                        fetch(endpoints.matrix + '?' + new URLSearchParams({
                            ...currentFilters,
                            type: 'kpi'
                        })).then(r => r.json()),
                        fetch(endpoints.matrix + '?' + new URLSearchParams({
                            ...currentFilters,
                            type: 'assessment'
                        })).then(r => r.json())
                    ]);

                    lastMatrixData = {
                        kpi: matrixKpiRes,
                        assessment: matrixAssessmentRes
                    };
                    updateMetrics(trendRes, predictionRes, matrixKpiRes);
                    updateChart(trendRes);
                    updateMatrix(matrixKpiRes, 'kpi');
                    updateMatrix(matrixAssessmentRes, 'assessment');
                    showToast('Data berhasil diperbarui', 'success');
                } catch (error) {
                    console.error('Error fetching data:', error);
                    showToast('Gagal memuat data analytics', 'error');
                } finally {
                    hideLoading();
                    isFetching = false;
                }
            }

            function updateMetrics(trendData, predictionData, matrixData) {
                const trend = trendData?.trend || {};
                const summary = trendData?.summary || {};
                const prediction = predictionData?.prediction || {};
                const matrixSummary = matrixData?.summary || {};

                const avgEl = document.getElementById('metricAvgProgress');
                if (avgEl) {
                    const val = summary.overall_average || 0;
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
                    predEl.textContent = (predValue !== null && predValue !== undefined) ? predValue + '%' : '-';
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
                    predValEl.textContent = (pv !== null && pv !== undefined) ? pv + '%' : '-';
                }

                ['predNext1', 'predNext2', 'predNext3'].forEach((id, i) => {
                    const el = document.getElementById(id);
                    if (el) el.textContent = prediction.next_3?.[i] ?? '-';
                });

                const rec = document.getElementById('predictionRecommendation');
                if (rec) {
                    const recommendations = predictionData?.recommendations || trendData?.insights || [];
                    rec.innerHTML = recommendations.length > 0 ? '<small>💡 ' + recommendations[0] + '</small>' :
                        '<small>-</small>';
                }

                const insight = document.getElementById('trendInsight');
                if (insight) insight.textContent = trendData?.insights?.[0] ||
                    'Data terkini berdasarkan filter yang dipilih';

                const period = document.getElementById('chartPeriod');
                if (period) period.textContent = currentFilters.tahun;
            }

            function updateChart(trendData) {
                if (!chartTrend) return;
                const trend = trendData?.trend || {};
                const labels = Object.keys(trend).filter(k => !['count', 'trend_direction', 'trend_delta',
                    'avg_progress', 'median_progress', 'total_targets', 'completed', 'std_deviation',
                    'periods'
                ].includes(k)).sort();
                const data = labels.map(k => trend[k]?.avg_progress ?? 0);

                chartTrend.data.labels = labels;
                chartTrend.data.datasets[0].data = data;
                chartTrend.update('none');
            }

            function updateMatrix(matrixData, type) {
                if (!matrixData) return;
                const matrix = matrixData.matrix || {};
                const suffix = type === 'assessment' ? '360' : '';

                const counts = {
                    star: matrix.star?.length || 0,
                    high_potential: matrix.high_potential?.length || 0,
                    potential_gem: matrix.potential_gem?.length || 0,
                    high_performer: matrix.high_performer?.length || 0,
                    core_player: matrix.core_player?.length || 0,
                    inconsistent: matrix.inconsistent?.length || 0,
                    solid_performer: matrix.solid_performer?.length || 0,
                    average_performer: matrix.average_performer?.length || 0,
                    risk: matrix.risk?.length || 0
                };

                const ids = ['countStar', 'countHighPotential', 'countPotentialGem', 'countHighPerformer',
                    'countCorePlayer', 'countInconsistent', 'countSolidPerformer', 'countAveragePerformer',
                    'countRisk'
                ];
                const keys = ['star', 'high_potential', 'potential_gem', 'high_performer', 'core_player',
                    'inconsistent', 'solid_performer', 'average_performer', 'risk'
                ];

                ids.forEach((id, i) => {
                    const el = document.getElementById(id + suffix);
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

            function showToast(message, type = 'info') {
                const container = document.getElementById('toastContainer');
                if (!container) return;

                const toast = document.createElement('div');
                toast.className =
                    `toast align-items-center text-bg-${type === 'error' ? 'danger' : (type === 'success' ? 'success' : 'primary')} border-0 show mb-2`;
                toast.setAttribute('role', 'alert');
                toast.innerHTML = `
                    <div class="d-flex">
                        <div class="toast-body">${message}</div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                    </div>
                `;

                container.appendChild(toast);

                setTimeout(() => {
                    if (toast.parentNode) {
                        toast.remove();
                    }
                }, 4000);
            }

            function renderEmployeeList(employees) {
                if (!employees || employees.length === 0) {
                    return '<div class="empty-state"><i class="bi bi-people"></i><p class="mb-0">Tidak ada data karyawan</p></div>';
                }

                return employees.map(emp => {
                    const pScore = emp.performance_score || 0;
                    const gScore = emp.growth_score || 0;
                    const threeSixty = emp.three_sixty_score || 0;

                    const pClass = pScore >= 75 ? 'score-high' : (pScore >= 50 ? 'score-mid' : 'score-low');
                    const gClass = gScore >= 70 ? 'score-high' : (gScore >= 40 ? 'score-mid' : 'score-low');
                    const tClass = threeSixty >= 80 ? 'score-high' : (threeSixty >= 60 ? 'score-mid' :
                        'score-low');

                    return `
                        <div class="employee-item">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <div class="employee-name">${emp.nama || '-'}</div>
                                    <div class="employee-meta">${emp.jabatan || '-'} • ${emp.divisi || '-'}</div>
                                </div>
                            </div>
                            <div class="score-container">
                                <span class="score-badge ${pClass}"><i class="bi bi-graph-up"></i> ${pScore}%</span>
                                <span class="score-badge ${gClass}"><i class="bi bi-arrow-up-right"></i> ${gScore}%</span>
                                <span class="score-badge ${tClass}"><i class="bi bi-people"></i> ${threeSixty}%</span>
                            </div>
                            ${emp.key_strengths?.length ? `<div class="mt-2"><small class="text-success fw-bold">✓ ${emp.key_strengths.join(', ')}</small></div>` : ''}
                            ${emp.development_areas?.length ? `<div class="mt-1"><small class="text-warning fw-bold">⚠ ${emp.development_areas.join(', ')}</small></div>` : ''}
                        </div>
                    `;
                }).join('');
            }

            function showModalEmployees(quadrant, type) {
                const titles = {
                    star: 'Star',
                    high_potential: 'High Potential',
                    potential_gem: 'Potential Gem',
                    high_performer: 'High Performer',
                    core_player: 'Core Player',
                    inconsistent: 'Inconsistent Player',
                    solid_performer: 'Solid Performer',
                    average_performer: 'Average Performer',
                    risk: 'Risk'
                };

                const typeLabel = type === 'assessment' ? ' (360° Assessment)' : ' (KPI Performance)';
                const titleEl = document.getElementById('modalMatrixTitle');
                if (titleEl) titleEl.textContent = `${titles[quadrant] || quadrant}${typeLabel}`;

                const contentEl = document.getElementById('modalMatrixContent');
                if (!contentEl) return;

                const data = lastMatrixData[type];
                if (!data) {
                    contentEl.innerHTML = '<div class="empty-state"><p class="mb-0">Memuat data...</p></div>';
                    return;
                }

                const matrix = data.matrix || {};
                const employees = matrix[quadrant] || [];
                contentEl.innerHTML = renderEmployeeList(employees);

                const modal = new bootstrap.Modal(document.getElementById('modalMatrix'));
                modal.show();
            }

            function getFilters() {
                return {
                    divisi: document.getElementById('filterDivisi')?.value || '',
                    jabatan: document.getElementById('filterJabatan')?.value || '',
                    tahun: document.getElementById('filterTahun')?.value || '{{ date('Y') }}',
                    granularity: document.getElementById('filterGranularity')?.value || 'monthly'
                };
            }

            document.getElementById('btnApplyFilter')?.addEventListener('click', function() {
                currentFilters = getFilters();
                fetchData();
            });

            document.getElementById('filterDivisi')?.addEventListener('change', function() {
                currentFilters.divisi = this.value;
            });
            document.getElementById('filterJabatan')?.addEventListener('change', function() {
                currentFilters.jabatan = this.value;
            });
            document.getElementById('filterTahun')?.addEventListener('change', function() {
                currentFilters.tahun = this.value;
            });
            document.getElementById('filterGranularity')?.addEventListener('change', function() {
                currentFilters.granularity = this.value;
            });

            document.getElementById('btnRefresh')?.addEventListener('click', function() {
                fetchData();
            });

            document.querySelectorAll('.matrix-cell').forEach(cell => {
                cell.addEventListener('click', function(e) {
                    e.preventDefault();
                    const quadrant = this.dataset.quadrant;
                    const type = this.dataset.type || 'kpi';
                    showModalEmployees(quadrant, type);
                });
            });

            document.querySelectorAll('.dropdown-menu .dropdown-item').forEach(item => {
                item.addEventListener('click', function(e) {
                    e.preventDefault();
                    document.querySelectorAll('.dropdown-menu .dropdown-item').forEach(i => i
                        .classList.remove('active'));
                    this.classList.add('active');
                    const filter = this.dataset.filter;
                    if (filter) {
                        const quadrant = this.closest('.card').querySelector(
                            '.matrix-cell[data-quadrant="' + filter + '"]');
                        if (quadrant) quadrant.click();
                    }
                });
            });

            document.getElementById('modalMatrix')?.addEventListener('hidden.bs.modal', function() {
                const contentEl = document.getElementById('modalMatrixContent');
                if (contentEl) contentEl.innerHTML = '';
            });

            initChart();
            fetchData();
        });
    </script>
@endsection
