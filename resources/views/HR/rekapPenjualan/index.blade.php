@extends('layout_HR.app')
@section('content_HR')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
            <div>
                <h3 class="fw-bold text-dark mb-1">
                    <i class="bi bi-graph-up-arrow text-primary me-2"></i>
                    Rekapitulasi Penjualan Kelas
                </h3>
                <p class="text-muted mb-0">Analisis komprehensif performa penjualan kelas berdasarkan periode dan materi</p>
            </div>
            <div class="d-flex align-items-center gap-2 bg-white p-2 px-3 rounded-3 shadow-sm">
                <label for="yearFilter" class="form-label mb-0 fw-semibold text-nowrap">
                    <i class="bi bi-calendar3 me-1"></i> Tahun:
                </label>
                <select id="yearFilter" class="form-select form-select-sm w-auto border-0 bg-light">
                    @for ($y = 2020; $y <= date('Y') + 2; $y++)
                        <option value="{{ $y }}" {{ $y == $currentYear ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>
        </div>

        <ul class="nav nav-tabs-custom mb-4" id="rekapTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-tab-btn active" data-bs-toggle="tab" data-bs-target="#tabDashboard" type="button">
                    <i class="bi bi-speedometer2"></i>
                    <span>Utama</span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-tab-btn" data-bs-toggle="tab" data-bs-target="#tabBulanan" type="button">
                    <i class="bi bi-calendar-month"></i>
                    <span>Rekap Bulanan</span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-tab-btn" data-bs-toggle="tab" data-bs-target="#tabMingguan" type="button">
                    <i class="bi bi-calendar-week"></i>
                    <span>Rekap Mingguan</span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-tab-btn" data-bs-toggle="tab" data-bs-target="#tabMateri" type="button">
                    <i class="bi bi-journal-bookmark"></i>
                    <span>Rekap Materi</span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-tab-btn" data-bs-toggle="tab" data-bs-target="#tabProfit" type="button">
                    <i class="bi bi-cash-coin"></i>
                    <span>Profitabilitas</span>
                </button>
            </li>
        </ul>

        <div class="tab-content" id="rekapTabsContent">
            <div class="tab-pane fade show active" id="tabDashboard" role="tabpanel">
                <div id="dashboardContent"></div>
            </div>
            <div class="tab-pane fade" id="tabBulanan" role="tabpanel">
                <div id="bulananContent"></div>
            </div>
            <div class="tab-pane fade" id="tabMingguan" role="tabpanel">
                <div class="card-custom mb-3">
                    <div class="d-flex gap-2 align-items-center flex-wrap">
                        <label class="fw-semibold mb-0">Pilih Bulan:</label>
                        <select id="mingguanBulan" class="form-select form-select-sm w-auto">
                            @php
                                $bulanList = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
                            @endphp
                            @foreach ($bulanList as $i => $b)
                                <option value="{{ $i + 1 }}" {{ $i + 1 == date('n') ? 'selected' : '' }}>{{ $b }}</option>
                            @endforeach
                        </select>
                        <button class="btn btn-primary-custom btn-sm" onclick="loadMingguan()">
                            <i class="bi bi-search"></i> Tampilkan
                        </button>
                    </div>
                </div>
                <div id="mingguanContent"></div>
            </div>
            <div class="tab-pane fade" id="tabMateri" role="tabpanel">
                <div id="materiContent"></div>
            </div>
            <div class="tab-pane fade" id="tabProfit" role="tabpanel">
                <div id="profitContent"></div>
            </div>
        </div>
    </div>

    <style>
        .nav-tabs-custom {
            display: flex;
            gap: 8px;
            border-bottom: 2px solid #e9ecef;
            padding-bottom: 0;
            flex-wrap: wrap;
        }
        .nav-tab-btn {
            background: transparent;
            border: none;
            border-bottom: 3px solid transparent;
            padding: 12px 20px;
            color: #6c757d;
            font-weight: 600;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.25s ease;
            cursor: pointer;
            border-radius: 8px 8px 0 0;
        }
        .nav-tab-btn:hover {
            color: #0d6efd;
            background-color: #f8f9fa;
        }
        .nav-tab-btn.active {
            color: #0d6efd;
            border-bottom-color: #0d6efd;
            background-color: rgba(13, 110, 253, 0.05);
        }
        .nav-tab-btn i { font-size: 1.1rem; }
        .card-custom {
            background: #ffffff;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.04);
            border: 1px solid #eef0f3;
            margin-bottom: 20px;
        }
        .metric-card {
            background: #ffffff;
            border-radius: 14px;
            padding: 22px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            border: 1px solid #eef0f3;
            transition: all 0.3s ease;
            height: 100%;
            position: relative;
            overflow: hidden;
        }
        .metric-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: var(--accent-color, #0d6efd);
        }
        .metric-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
        }
        .metric-icon {
            width: 52px;
            height: 52px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            background: var(--icon-bg, rgba(13, 110, 253, 0.1));
            color: var(--accent-color, #0d6efd);
        }
        .metric-label {
            font-size: 0.8rem;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
            margin-bottom: 6px;
        }
        .metric-value {
            font-size: 1.6rem;
            font-weight: 700;
            color: #212529;
            margin: 0;
            line-height: 1.2;
        }
        .metric-sub {
            font-size: 0.78rem;
            color: #6c757d;
            margin-top: 4px;
        }
        .btn-primary-custom {
            background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%);
            color: #fff;
            border: none;
            padding: 8px 18px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.85rem;
            transition: all 0.25s ease;
        }
        .btn-primary-custom:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(13, 110, 253, 0.3);
            color: #fff;
        }
        .table-rekap {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }
        .table-rekap thead th {
            background: linear-gradient(180deg, #f8f9fa 0%, #eef0f3 100%);
            color: #495057;
            font-weight: 700;
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 14px 16px;
            border-bottom: 2px solid #dee2e6;
            text-align: left;
        }
        .table-rekap tbody td {
            padding: 14px 16px;
            border-bottom: 1px solid #eef0f3;
            font-size: 0.9rem;
            color: #212529;
        }
        .table-rekap tbody tr { transition: background-color 0.2s ease; }
        .table-rekap tbody tr:hover { background-color: #f8f9fa; }
        .table-rekap tfoot td {
            padding: 14px 16px;
            background: #f8f9fa;
            font-weight: 700;
            border-top: 2px solid #dee2e6;
        }
        .badge-soft {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        .badge-soft-success { background: rgba(25, 135, 84, 0.12); color: #198754; }
        .badge-soft-warning { background: rgba(255, 193, 7, 0.18); color: #b8860b; }
        .badge-soft-info { background: rgba(13, 202, 240, 0.15); color: #0a7c94; }
        .badge-soft-primary { background: rgba(13, 110, 253, 0.12); color: #0d6efd; }
        .progress-thin {
            height: 6px;
            border-radius: 10px;
            background: #e9ecef;
            overflow: hidden;
        }
        .progress-thin-bar {
            height: 100%;
            border-radius: 10px;
            transition: width 0.6s ease;
        }
        .profit-positive { color: #198754; font-weight: 700; }
        .profit-negative { color: #b8860b; font-weight: 700; }
        .chart-bar {
            display: flex;
            align-items: flex-end;
            gap: 8px;
            height: 200px;
            padding: 15px 0;
            border-bottom: 2px solid #dee2e6;
        }
        .chart-bar-item {
            flex: 1;
            background: linear-gradient(180deg, #0d6efd 0%, #0b5ed7 100%);
            border-radius: 6px 6px 0 0;
            position: relative;
            transition: all 0.3s ease;
            min-height: 4px;
            cursor: pointer;
        }
        .chart-bar-item:hover {
            opacity: 0.85;
            transform: scaleY(1.02);
        }
        .chart-bar-label {
            text-align: center;
            font-size: 0.72rem;
            color: #6c757d;
            margin-top: 8px;
            font-weight: 600;
        }
        @media (max-width: 768px) {
            .nav-tab-btn { padding: 10px 14px; font-size: 0.82rem; }
            .metric-value { font-size: 1.3rem; }
        }
        #tableRekapMateri_wrapper .dataTables-header-custom,
        #tableRekapMateri_wrapper .dataTables-footer-custom {
            --bs-gutter-x: 0;
            margin-left: 0;
            margin-right: 0;
            padding-left: 20px;
            padding-right: 20px;
        }
        #tableRekapMateri_wrapper .dataTables-header-custom { padding-top: 18px; padding-bottom: 6px; }
        #tableRekapMateri_wrapper .dataTables-footer-custom { padding-top: 12px; padding-bottom: 18px; }
        #tableRekapMateri { min-width: 960px; }
        #tableRekapMateri thead th {
            white-space: nowrap;
            vertical-align: middle;
        }
        #tableRekapMateri thead th.th-no { width: 48px; text-align: center; }
        #tableRekapMateri thead th.th-center { text-align: center; }
        #tableRekapMateri thead th.th-end { text-align: right; }
        #tableRekapMateri thead th.th-materi { width: 34%; min-width: 260px; }
        #tableRekapMateri thead th.th-kontribusi { width: 210px; }
        #tableRekapMateri tbody td {
            vertical-align: middle;
            white-space: nowrap;
        }
        #tableRekapMateri tbody td.cell-no {
            width: 48px;
            text-align: center;
            color: #8a94a0;
            font-weight: 600;
        }
        #tableRekapMateri tbody td.cell-materi {
            white-space: normal;
            min-width: 260px;
            line-height: 1.45;
        }
        #tableRekapMateri tbody td.cell-currency { font-variant-numeric: tabular-nums; }
        #tableRekapMateri tbody td.cell-kontribusi { width: 210px; min-width: 200px; }
        #tableRekapMateri tbody tr:nth-child(even) { background-color: #fbfcfd; }

        .skeleton {
            background: linear-gradient(90deg, #f0f2f5 25%, #e6e9ef 50%, #f0f2f5 75%);
            background-size: 200% 100%;
            animation: skeleton-shimmer 1.4s ease-in-out infinite;
            border-radius: 8px;
        }
        @keyframes skeleton-shimmer {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }
        .skeleton-card {
            background: #fff;
            border-radius: 14px;
            padding: 22px;
            border: 1px solid #eef0f3;
            box-shadow: 0 4px 15px rgba(0,0,0,0.04);
            height: 100%;
        }
        .skeleton-line {
            height: 12px;
            margin-bottom: 10px;
        }
        .skeleton-line.sm { height: 10px; width: 60%; }
        .skeleton-line.md { height: 14px; width: 80%; }
        .skeleton-line.lg { height: 22px; width: 70%; }
        .skeleton-line.xl { height: 28px; width: 55%; }
        .skeleton-circle {
            width: 52px;
            height: 52px;
            border-radius: 12px;
        }
        .skeleton-chart {
            height: 200px;
            border-radius: 8px;
        }
        .skeleton-table-row {
            display: flex;
            gap: 12px;
            padding: 14px 0;
            border-bottom: 1px solid #f1f3f5;
        }
        .skeleton-table-row .skel-col {
            height: 14px;
            border-radius: 4px;
        }
    </style>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let currentYear = {{ $currentYear }};

        function skeletonDashboard() {
            return `
            <div class="row g-3 mb-4">
                ${[1,2,3,4].map(() => `
                    <div class="col-md-3 col-sm-6">
                        <div class="skeleton-card">
                            <div class="d-flex align-items-center gap-3">
                                <div class="skeleton skeleton-circle"></div>
                                <div class="flex-grow-1">
                                    <div class="skeleton skeleton-line sm"></div>
                                    <div class="skeleton skeleton-line xl"></div>
                                    <div class="skeleton skeleton-line sm" style="width:40%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                `).join('')}
            </div>
            <div class="row g-3">
                <div class="col-md-5">
                    <div class="skeleton-card">
                        <div class="skeleton skeleton-line md mb-4" style="width:50%"></div>
                        <div class="skeleton skeleton-line mb-3"></div>
                        <div class="skeleton skeleton-line mb-4" style="width:90%"></div>
                        <div class="skeleton skeleton-line mb-3"></div>
                        <div class="skeleton skeleton-line mb-4" style="width:75%"></div>
                        <div class="text-center mt-4">
                            <div class="skeleton mx-auto" style="width:80px;height:40px;border-radius:8px;"></div>
                            <div class="skeleton skeleton-line sm mx-auto mt-2" style="width:50%"></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-7">
                    <div class="skeleton-card">
                        <div class="skeleton skeleton-line md mb-4" style="width:45%"></div>
                        <div class="skeleton skeleton-chart"></div>
                        <div class="d-flex gap-2 mt-3">
                            ${[1,2,3,4,5,6,7,8,9,10,11,12].map(() => 
                                `<div class="skeleton flex-grow-1" style="height:10px;"></div>`
                            ).join('')}
                        </div>
                    </div>
                </div>
            </div>`;
        }

        function skeletonTable(rows = 8, cols = 7) {
            let header = '<div class="d-flex gap-3 mb-3 px-1">';
            for (let i = 0; i < cols; i++) {
                header += `<div class="skeleton flex-grow-1" style="height:14px;"></div>`;
            }
            header += '</div>';
            let body = '';
            for (let r = 0; r < rows; r++) {
                body += '<div class="skeleton-table-row">';
                for (let c = 0; c < cols; c++) {
                    const w = c === 0 ? '18%' : (c === cols - 1 ? '12%' : '14%');
                    body += `<div class="skeleton skel-col" style="width:${w}"></div>`;
                }
                body += '</div>';
            }
            return `<div class="card-custom p-4">${header}${body}</div>`;
        }

        function skeletonProfit() {
            return `
            <div class="row g-3 mb-3">
                ${[1,2,3].map(() => `
                    <div class="col-md-4">
                        <div class="skeleton-card">
                            <div class="skeleton skeleton-line sm mb-3"></div>
                            <div class="skeleton skeleton-line xl"></div>
                        </div>
                    </div>
                `).join('')}
            </div>
            ${skeletonTable(10, 4)}`;
        }

        function formatRupiah(angka) {
            if (angka === null || angka === undefined || isNaN(angka)) return '0';
            return 'Rp ' + Math.round(angka).toLocaleString('id-ID');
        }

        function formatNumber(angka) {
            if (angka === null || angka === undefined || isNaN(angka)) return '0';
            return Math.round(angka).toLocaleString('id-ID');
        }

        function loadDashboard() {
            $('#dashboardContent').html(skeletonDashboard());
            $.ajax({
                url: `{{ url('HR-dashboard/rekapan-penjualan/data') }}/${currentYear}`,
                type: 'GET',
                success: function(res) {
                    if (!res.success) return;
                    const gt = res.grand_total;
                    const persentaseLengkap = gt.total_kelas > 0 ? ((gt.total_lengkap / gt.total_kelas) * 100).toFixed(1) : 0;
                    const persentaseBelum = (100 - persentaseLengkap).toFixed(1);
                    const margin = gt.total_harga_jual > 0 ? ((gt.total_nett / gt.total_harga_jual) * 100).toFixed(1) : 0;

                    let html = `
                    <div class="row g-3 mb-4">
                        <div class="col-md-3 col-sm-6">
                            <div class="metric-card" style="--accent-color: #0d6efd; --icon-bg: rgba(13, 110, 253, 0.1);">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="metric-icon"><i class="bi bi-journal-text"></i></div>
                                    <div>
                                        <div class="metric-label">Total Kelas</div>
                                        <div class="metric-value">${formatNumber(gt.total_kelas)}</div>
                                        <div class="metric-sub">Sepanjang ${currentYear}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <div class="metric-card" style="--accent-color: #198754; --icon-bg: rgba(25, 135, 84, 0.1);">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="metric-icon"><i class="bi bi-people-fill"></i></div>
                                    <div>
                                        <div class="metric-label">Total Peserta</div>
                                        <div class="metric-value">${formatNumber(gt.total_pax)}</div>
                                        <div class="metric-sub">Peserta terdaftar</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <div class="metric-card" style="--accent-color: #0dcaf0; --icon-bg: rgba(13, 202, 240, 0.12);">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="metric-icon"><i class="bi bi-cash-stack"></i></div>
                                    <div>
                                        <div class="metric-label">Total Harga Jual</div>
                                        <div class="metric-value" style="font-size: 1.25rem;">${formatRupiah(gt.total_harga_jual)}</div>
                                        <div class="metric-sub">Gross revenue</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <div class="metric-card" style="--accent-color: #6f42c1; --icon-bg: rgba(111, 66, 193, 0.12);">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="metric-icon"><i class="bi bi-graph-up-arrow"></i></div>
                                    <div>
                                        <div class="metric-label">Nett Penjualan</div>
                                        <div class="metric-value" style="font-size: 1.25rem;">${formatRupiah(gt.total_nett)}</div>
                                        <div class="metric-sub">Margin: ${margin}%</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-5">
                            <div class="card-custom h-100">
                                <h6 class="fw-bold mb-3"><i class="bi bi-pie-chart-fill text-primary me-2"></i>Status Kelengkapan Data</h6>
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="fw-semibold" style="font-size: 0.85rem;"><i class="bi bi-check-circle-fill text-success me-1"></i>Data Lengkap</span>
                                        <span class="badge-soft badge-soft-success">${gt.total_lengkap} Kelas (${persentaseLengkap}%)</span>
                                    </div>
                                    <div class="progress-thin">
                                        <div class="progress-thin-bar" style="width: ${persentaseLengkap}%; background: #198754;"></div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="fw-semibold" style="font-size: 0.85rem;"><i class="bi bi-exclamation-circle-fill text-warning me-1"></i>Belum Lengkap</span>
                                        <span class="badge-soft badge-soft-warning">${gt.total_kelas - gt.total_lengkap} Kelas (${persentaseBelum}%)</span>
                                    </div>
                                    <div class="progress-thin">
                                        <div class="progress-thin-bar" style="width: ${persentaseBelum}%; background: #ffc107;"></div>
                                    </div>
                                </div>
                                <hr>
                                <div class="text-center">
                                    <div style="font-size: 2rem; font-weight: 700; color: #0d6efd;">${persentaseLengkap}%</div>
                                    <small class="text-muted">Tingkat Kelengkapan Keseluruhan</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-7">
                            <div class="card-custom h-100">
                                <h6 class="fw-bold mb-3"><i class="bi bi-bar-chart-fill text-primary me-2"></i>Tren Nett Penjualan Bulanan</h6>
                                <div class="chart-bar" id="dashboardChart"></div>
                                <div class="d-flex" id="dashboardChartLabels" style="gap: 8px;"></div>
                            </div>
                        </div>
                    </div>`;
                    $('#dashboardContent').html(html);

                    const maxNett = Math.max(...res.data.map(d => d.total_nett), 1);
                    let chartHtml = '';
                    let labelsHtml = '';
                    res.data.forEach(d => {
                        const height = (d.total_nett / maxNett) * 100;
                        chartHtml += `<div class="chart-bar-item" style="height: ${Math.max(height, 2)}%;" title="${d.bulan}: ${formatRupiah(d.total_nett)}"></div>`;
                        labelsHtml += `<div class="chart-bar-label" style="flex: 1;">${d.bulan.substring(0, 3)}</div>`;
                    });
                    $('#dashboardChart').html(chartHtml);
                    $('#dashboardChartLabels').html(labelsHtml);
                },
                error: function() {
                    $('#dashboardContent').html('<div class="alert alert-warning">Gagal memuat data dashboard.</div>');
                }
            });
        }

        function loadBulanan() {
            $('#bulananContent').html(skeletonTable(12, 7));
            $.ajax({
                url: `{{ url('HR-dashboard/rekapan-penjualan/data') }}/${currentYear}`,
                type: 'GET',
                success: function(res) {
                    if (!res.success) return;
                    const gt = res.grand_total;
                    let rows = '';
                    res.data.forEach(d => {
                        const rataRata = d.total_kelas > 0 ? d.total_nett / d.total_kelas : 0;
                        rows += `
                        <tr>
                            <td><strong>${d.bulan}</strong></td>
                            <td class="text-center"><span class="badge-soft badge-soft-primary">${d.total_kelas}</span></td>
                            <td class="text-center">${formatNumber(d.total_pax)}</td>
                            <td class="text-end">${formatRupiah(d.total_harga_jual)}</td>
                            <td class="text-end"><strong>${formatRupiah(d.total_nett)}</strong></td>
                            <td class="text-end">${formatRupiah(rataRata)}</td>
                            <td class="text-center">
                                <span class="badge-soft badge-soft-success">${d.total_lengkap}</span>
                                <span class="badge-soft badge-soft-warning">${d.total_belum}</span>
                            </td>
                        </tr>`;
                    });
                    const html = `
                    <div class="card-custom p-0 overflow-auto">
                        <table class="table-rekap">
                            <thead>
                                <tr>
                                    <th>Bulan</th>
                                    <th class="text-center">Jumlah Kelas</th>
                                    <th class="text-center">Total Pax</th>
                                    <th class="text-end">Total Harga Jual</th>
                                    <th class="text-end">Total Nett</th>
                                    <th class="text-end">Rata-rata / Kelas</th>
                                    <th class="text-center">Status Data</th>
                                </tr>
                            </thead>
                            <tbody>${rows || '<tr><td colspan="7" class="text-center text-muted py-4">Tidak ada data</td></tr>'}</tbody>
                            <tfoot>
                                <tr>
                                    <td><strong>GRAND TOTAL</strong></td>
                                    <td class="text-center"><strong>${formatNumber(gt.total_kelas)}</strong></td>
                                    <td class="text-center"><strong>${formatNumber(gt.total_pax)}</strong></td>
                                    <td class="text-end"><strong>${formatRupiah(gt.total_harga_jual)}</strong></td>
                                    <td class="text-end"><strong>${formatRupiah(gt.total_nett)}</strong></td>
                                    <td></td>
                                    <td class="text-center"><strong>${formatNumber(gt.total_lengkap)} / ${formatNumber(gt.total_kelas)}</strong></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>`;
                    $('#bulananContent').html(html);
                }
            });
        }

        function loadMingguan() {
            const bulan = $('#mingguanBulan').val();
            $('#mingguanContent').html(skeletonTable(5, 5));
            $.ajax({
                url: `{{ url('HR-dashboard/rekapan-penjualan/mingguan') }}/${currentYear}/${bulan}`,
                type: 'GET',
                success: function(res) {
                    if (!res.success) return;
                    let totalKelas = 0, totalPax = 0, totalNett = 0;
                    let rows = '';
                    res.data.forEach(w => {
                        totalKelas += w.total_kelas;
                        totalPax += w.total_pax;
                        totalNett += w.total_nett;
                        rows += `
                        <tr>
                            <td><strong>Minggu ${w.minggu}</strong><br><small class="text-muted">${w.tanggal_awal} - ${w.tanggal_akhir}</small></td>
                            <td class="text-center"><span class="badge-soft badge-soft-primary">${w.total_kelas}</span></td>
                            <td class="text-center">${formatNumber(w.total_pax)}</td>
                            <td class="text-end">${formatRupiah(w.total_harga_jual)}</td>
                            <td class="text-end"><strong>${formatRupiah(w.total_nett)}</strong></td>
                        </tr>`;
                    });
                    const html = `
                    <div class="card-custom">
                        <h6 class="fw-bold mb-3"><i class="bi bi-calendar-week text-primary me-2"></i>Rekap Mingguan - ${res.bulan} ${currentYear}</h6>
                        <div class="table-responsive">
                            <table class="table-rekap">
                                <thead>
                                    <tr>
                                        <th>Periode Minggu</th>
                                        <th class="text-center">Jumlah Kelas</th>
                                        <th class="text-center">Total Pax</th>
                                        <th class="text-end">Total Harga Jual</th>
                                        <th class="text-end">Total Nett</th>
                                    </tr>
                                </thead>
                                <tbody>${rows || '<tr><td colspan="5" class="text-center text-muted py-4">Tidak ada data</td></tr>'}</tbody>
                                <tfoot>
                                    <tr>
                                        <td><strong>TOTAL ${res.bulan.toUpperCase()}</strong></td>
                                        <td class="text-center"><strong>${formatNumber(totalKelas)}</strong></td>
                                        <td class="text-center"><strong>${formatNumber(totalPax)}</strong></td>
                                        <td class="text-end"><strong>${formatRupiah(res.data.reduce((a,b) => a + b.total_harga_jual, 0))}</strong></td>
                                        <td class="text-end"><strong>${formatRupiah(totalNett)}</strong></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>`;
                    $('#mingguanContent').html(html);
                }
            });
        }

        function loadMateri() {
            $('#materiContent').html(skeletonTable(10, 8));
            $.ajax({
                url: `{{ url('HR-dashboard/rekapan-penjualan/materi') }}/${currentYear}`,
                type: 'GET',
                success: function(res) {
                    if (!res.success) return;
                    const totalNettAll = res.data.reduce((a, b) => a + b.total_nett, 0);
                    let rows = '';
                    res.data.forEach((d, i) => {
                        const kontribusi = totalNettAll > 0 ? (d.total_nett / totalNettAll * 100) : 0;
                        rows += `
                        <tr>
                            <td class="cell-no">${i + 1}</td>
                            <td class="cell-materi"><strong>${d.nama_materi}</strong></td>
                            <td class="text-center"><span class="badge-soft badge-soft-primary">${d.total_kelas}</span></td>
                            <td class="text-center">${formatNumber(d.total_pax)}</td>
                            <td class="text-center">${d.rata_rata_pax}</td>
                            <td class="text-end cell-currency">${formatRupiah(d.total_harga_jual)}</td>
                            <td class="text-end cell-currency"><strong>${formatRupiah(d.total_nett)}</strong></td>
                            <td class="cell-kontribusi">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress-thin flex-grow-1">
                                        <div class="progress-thin-bar" style="width: ${kontribusi}%; background: #0d6efd;"></div>
                                    </div>
                                    <small class="fw-bold text-nowrap">${kontribusi.toFixed(1)}%</small>
                                </div>
                            </td>
                        </tr>`;
                    });
                    const html = `
                    <div class="card-custom p-0 overflow-auto">
                        <table class="table-rekap" id="tableRekapMateri">
                            <thead>
                                <tr>
                                    <th class="th-no">#</th>
                                    <th class="th-materi">Nama Materi</th>
                                    <th class="th-center">Jumlah Kelas</th>
                                    <th class="th-center">Total Pax</th>
                                    <th class="th-center">Avg Pax/Kelas</th>
                                    <th class="th-end">Total Harga Jual</th>
                                    <th class="th-end">Total Nett</th>
                                    <th class="th-kontribusi">Kontribusi</th>
                                </tr>
                            </thead>
                            <tbody>${rows || '<tr><td colspan="8" class="text-center text-muted py-4">Tidak ada data</td></tr>'}</tbody>
                        </table>
                    </div>`;
                    $('#materiContent').html(html);

                    $('#tableRekapMateri').DataTable({
                        pageLength: 15,
                        language: {
                            search: "",
                            searchPlaceholder: "Cari Materi...",
                            lengthMenu: "Tampilkan _MENU_ data",
                            info: "Menampilkan _START_–_END_ dari _TOTAL_ data",
                            infoEmpty: "Menampilkan 0–0 dari 0 data",
                            zeroRecords: "Tidak ditemukan data yang sesuai",
                            paginate: { next: "›", previous: "‹" }
                        },
                        dom: "<'row mb-3 align-items-center dataTables-header-custom'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6 d-flex justify-content-md-end'f>>" +
                             "<'row'<'col-sm-12'tr>>" +
                             "<'row mt-3 align-items-center dataTables-footer-custom'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7 d-flex justify-content-md-end'p>>",
                    });

                    if (!$('#custom-dt-style').length) {
                        $('head').append(`
                            <style id="custom-dt-style">
                                .dataTables-header-custom .dataTables_length label { font-size: 0.85rem; color: #6c757d; font-weight: 500; display: flex; align-items: center; gap: 8px; }
                                .dataTables-header-custom .dataTables_length select { border-radius: 6px; border: 1px solid #dee2e6; padding: 4px 30px 4px 12px; font-size: 0.85rem; outline: none; }
                                .dataTables-header-custom .dataTables_filter label { margin: 0; width: 100%; max-width: 250px; }
                                .dataTables-header-custom .dataTables_filter input { width: 100%; border-radius: 20px; border: 1px solid #dee2e6; padding: 6px 16px; font-size: 0.85rem; outline: none; transition: border-color 0.2s; background: #fff url('data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="%23adb5bd" viewBox="0 0 16 16"%3E%3Cpath d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0"/%3E%3C/svg%3E') no-repeat right 12px center; background-size: 14px; padding-right: 32px; }
                                .dataTables-header-custom .dataTables_filter input:focus { border-color: #0d6efd; box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.1); }
                                .dataTables-footer-custom .dataTables_info { font-size: 0.85rem; color: #adb5bd; padding-top: 0; }
                                .dataTables-footer-custom .pagination { margin: 0; gap: 4px; }
                                .dataTables-footer-custom .page-item .page-link { border-radius: 6px !important; border: 1px solid #eef0f3; color: #495057; font-size: 0.85rem; padding: 6px 12px; margin: 0; }
                                .dataTables-footer-custom .page-item.active .page-link { background-color: #0d6efd; border-color: #0d6efd; color: #fff; box-shadow: 0 2px 4px rgba(13, 110, 253, 0.2); }
                                .dataTables-footer-custom .page-item.disabled .page-link { color: #dee2e6; background-color: #fff; border-color: #eef0f3; }
                                .table-rekap { border-bottom: none !important; margin-bottom: 0 !important; }
                                table.dataTable.no-footer { border-bottom: 1px solid #eef0f3 !important; }
                                table.dataTable thead th, table.dataTable thead td { border-bottom: 2px solid #eef0f3 !important; }
                            </style>
                        `);
                    }
                }
            });
        }

        function loadProfit() {
            $('#profitContent').html(skeletonProfit());
            $.ajax({
                url: `{{ url('HR-dashboard/rekapan-penjualan/profitabilitas') }}/${currentYear}`,
                type: 'GET',
                success: function(res) {
                    if (!res.success) return;
                    let rows = '';
                    let totalNett = 0, totalFix = 0, totalProfit = 0;
                    res.data.forEach(d => {
                        totalNett += d.total_nett;
                        totalFix += d.total_fixcost;
                        totalProfit += d.profit;
                        const profitClass = d.profit >= 0 ? 'profit-positive' : 'profit-negative';
                        const profitIcon = d.profit >= 0 ? 'bi-arrow-up-right' : 'bi-arrow-down-right';
                        rows += `
                        <tr>
                            <td><strong>${d.bulan}</strong></td>
                            <td class="text-end">${formatRupiah(d.total_nett)}</td>
                            <td class="text-end">${formatRupiah(d.total_fixcost)}</td>
                            <td class="text-end ${profitClass}">
                                <i class="bi ${profitIcon} me-1"></i>${formatRupiah(d.profit)}
                            </td>
                        </tr>`;
                    });
                    const totalClass = totalProfit >= 0 ? 'profit-positive' : 'profit-negative';
                    const html = `
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <div class="metric-card" style="--accent-color: #0dcaf0; --icon-bg: rgba(13, 202, 240, 0.12);">
                                <div class="metric-label">Total Nett Penjualan</div>
                                <div class="metric-value" style="font-size: 1.2rem;">${formatRupiah(totalNett)}</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="metric-card" style="--accent-color: #ffc107; --icon-bg: rgba(255, 193, 7, 0.15);">
                                <div class="metric-label">Total Fix Cost</div>
                                <div class="metric-value" style="font-size: 1.2rem;">${formatRupiah(totalFix)}</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="metric-card" style="--accent-color: ${totalProfit >= 0 ? '#198754' : '#b8860b'}; --icon-bg: ${totalProfit >= 0 ? 'rgba(25, 135, 84, 0.1)' : 'rgba(184, 134, 11, 0.1)'};">
                                <div class="metric-label">Total Profit Tahun ${currentYear}</div>
                                <div class="metric-value" style="font-size: 1.2rem;" class="${totalClass}">${formatRupiah(totalProfit)}</div>
                            </div>
                        </div>
                    </div>
                    <div class="card-custom p-0 overflow-auto">
                        <table class="table-rekap">
                            <thead>
                                <tr>
                                    <th>Bulan</th>
                                    <th class="text-end">Total Nett</th>
                                    <th class="text-end">Fix Cost</th>
                                    <th class="text-end">Profit (Nett - Fix Cost)</th>
                                </tr>
                            </thead>
                            <tbody>${rows}</tbody>
                            <tfoot>
                                <tr>
                                    <td><strong>TOTAL</strong></td>
                                    <td class="text-end"><strong>${formatRupiah(totalNett)}</strong></td>
                                    <td class="text-end"><strong>${formatRupiah(totalFix)}</strong></td>
                                    <td class="text-end ${totalClass}"><strong>${formatRupiah(totalProfit)}</strong></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>`;
                    $('#profitContent').html(html);
                }
            });
        }

        $(document).ready(function() {
            $('#dashboardContent').html(skeletonDashboard());
            loadDashboard();

            $('#yearFilter').on('change', function() {
                currentYear = $(this).val();
                const activeTab = $('.nav-tab-btn.active').data('bs-target');
                if (activeTab === '#tabDashboard') loadDashboard();
                else if (activeTab === '#tabBulanan') loadBulanan();
                else if (activeTab === '#tabMingguan') loadMingguan();
                else if (activeTab === '#tabMateri') loadMateri();
                else if (activeTab === '#tabProfit') loadProfit();
            });

            $('#mingguanBulan').on('change', function() {
                loadMingguan();
            });

            $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function(e) {
                const target = $(e.target).data('bs-target');
                if (target === '#tabDashboard') loadDashboard();
                else if (target === '#tabBulanan') loadBulanan();
                else if (target === '#tabMingguan') loadMingguan();
                else if (target === '#tabMateri') loadMateri();
                else if (target === '#tabProfit') loadProfit();
            });
        });
    </script>
@endsection