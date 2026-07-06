@extends('layout_HR.app')
@section('content_HR')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <div class="container-fluid py-4">
        <!-- Header -->
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
                        <option value="{{ $y }}" {{ $y == $currentYear ? 'selected' : '' }}>{{ $y }}
                        </option>
                    @endfor
                </select>
            </div>
        </div>

        <!-- Tab Navigation -->
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

        <!-- Tab Content -->
        <div class="tab-content" id="rekapTabsContent">

            <!-- TAB 1: Dashboard -->
            <div class="tab-pane fade show active" id="tabDashboard" role="tabpanel">
                <div id="dashboardContent">
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="text-muted mt-3">Memuat data dashboard...</p>
                    </div>
                </div>
            </div>

            <!-- TAB 2: Rekap Bulanan -->
            <div class="tab-pane fade" id="tabBulanan" role="tabpanel">
                <div id="bulananContent">
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status"></div>
                    </div>
                </div>
            </div>

            <!-- TAB 3: Rekap Mingguan -->
            <div class="tab-pane fade" id="tabMingguan" role="tabpanel">
                <div class="card-custom mb-3">
                    <div class="d-flex gap-2 align-items-center flex-wrap">
                        <label class="fw-semibold mb-0">Pilih Bulan:</label>
                        <select id="mingguanBulan" class="form-select form-select-sm w-auto">
                            @php
                                $bulanList = [
                                    'Januari',
                                    'Februari',
                                    'Maret',
                                    'April',
                                    'Mei',
                                    'Juni',
                                    'Juli',
                                    'Agustus',
                                    'September',
                                    'Oktober',
                                    'November',
                                    'Desember',
                                ];
                            @endphp
                            @foreach ($bulanList as $i => $b)
                                <option value="{{ $i + 1 }}" {{ $i + 1 == date('n') ? 'selected' : '' }}>
                                    {{ $b }}</option>
                            @endforeach
                        </select>
                        <button class="btn btn-primary-custom btn-sm" onclick="loadMingguan()">
                            <i class="bi bi-search"></i> Tampilkan
                        </button>
                    </div>
                </div>
                <div id="mingguanContent"></div>
            </div>

            <!-- TAB 4: Rekap Materi -->
            <div class="tab-pane fade" id="tabMateri" role="tabpanel">
                <div id="materiContent">
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status"></div>
                    </div>
                </div>
            </div>

            <!-- TAB 5: Profitabilitas -->
            <div class="tab-pane fade" id="tabProfit" role="tabpanel">
                <div id="profitContent">
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* Custom Tab Navigation - Modern & Clean */
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

        .nav-tab-btn i {
            font-size: 1.1rem;
        }

        /* Custom Card */
        .card-custom {
            background: #ffffff;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.04);
            border: 1px solid #eef0f3;
            margin-bottom: 20px;
        }

        /* Metric Card */
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

        /* Button */
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

        /* Table */
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

        .table-rekap tbody tr {
            transition: background-color 0.2s ease;
        }

        .table-rekap tbody tr:hover {
            background-color: #f8f9fa;
        }

        .table-rekap tfoot td {
            padding: 14px 16px;
            background: #f8f9fa;
            font-weight: 700;
            border-top: 2px solid #dee2e6;
        }

        /* Badge */
        .badge-soft {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .badge-soft-success {
            background: rgba(25, 135, 84, 0.12);
            color: #198754;
        }

        .badge-soft-warning {
            background: rgba(255, 193, 7, 0.18);
            color: #b8860b;
        }

        .badge-soft-info {
            background: rgba(13, 202, 240, 0.15);
            color: #0a7c94;
        }

        .badge-soft-primary {
            background: rgba(13, 110, 253, 0.12);
            color: #0d6efd;
        }

        /* Progress */
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

        /* Profit Indicator */
        .profit-positive {
            color: #198754;
            font-weight: 700;
        }

        .profit-negative {
            color: #b8860b;
            font-weight: 700;
        }

        /* Chart placeholder */
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
            .nav-tab-btn {
                padding: 10px 14px;
                font-size: 0.82rem;
            }

            .metric-value {
                font-size: 1.3rem;
            }
        }
    </style>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let currentYear = {{ $currentYear }};

        $(document).ready(function() {
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

            // Load data saat tab di klik
            $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function(e) {
                const target = $(e.target).data('bs-target');
                if (target === '#tabDashboard') loadDashboard();
                else if (target === '#tabBulanan') loadBulanan();
                else if (target === '#tabMingguan') loadMingguan();
                else if (target === '#tabMateri') loadMateri();
                else if (target === '#tabProfit') loadProfit();
            });
        });

        function formatRupiah(angka) {
            if (angka === null || angka === undefined || isNaN(angka)) return '0';
            return 'Rp ' + Math.round(angka).toLocaleString('id-ID');
        }

        function formatNumber(angka) {
            if (angka === null || angka === undefined || isNaN(angka)) return '0';
            return Math.round(angka).toLocaleString('id-ID');
        }

        // ==================== DASHBOARD ====================
        function loadDashboard() {
            $('#dashboardContent').html(
                '<div class="text-center py-5"><div class="spinner-border text-primary"></div></div>');

            $.ajax({
                url: `{{ url('HR-dashboard/rekapan-penjualan/data') }}/${currentYear}`,
                type: 'GET',
                success: function(res) {
                    if (!res.success) return;

                    const gt = res.grand_total;
                    const persentaseLengkap = gt.total_kelas > 0 ? ((gt.total_lengkap / gt.total_kelas) * 100)
                        .toFixed(1) : 0;
                    const persentaseBelum = (100 - persentaseLengkap).toFixed(1);
                    const margin = gt.total_harga_jual > 0 ? ((gt.total_nett / gt.total_harga_jual) * 100)
                        .toFixed(1) : 0;

                    let html = `
                <!-- Metric Cards -->
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
                    <!-- Kelengkapan Data -->
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

                    <!-- Chart Penjualan Bulanan -->
                    <div class="col-md-7">
                        <div class="card-custom h-100">
                            <h6 class="fw-bold mb-3"><i class="bi bi-bar-chart-fill text-primary me-2"></i>Tren Nett Penjualan Bulanan</h6>
                            <div class="chart-bar" id="dashboardChart"></div>
                            <div class="d-flex" id="dashboardChartLabels" style="gap: 8px;"></div>
                        </div>
                    </div>
                </div>
            `;

                    $('#dashboardContent').html(html);

                    // Render chart
                    const maxNett = Math.max(...res.data.map(d => d.total_nett), 1);
                    let chartHtml = '';
                    let labelsHtml = '';
                    res.data.forEach(d => {
                        const height = (d.total_nett / maxNett) * 100;
                        chartHtml +=
                            `<div class="chart-bar-item" style="height: ${Math.max(height, 2)}%;" title="${d.bulan}: ${formatRupiah(d.total_nett)}"></div>`;
                        labelsHtml +=
                            `<div class="chart-bar-label" style="flex: 1;">${d.bulan.substring(0, 3)}</div>`;
                    });
                    $('#dashboardChart').html(chartHtml);
                    $('#dashboardChartLabels').html(labelsHtml);
                },
                error: function() {
                    $('#dashboardContent').html(
                        '<div class="alert alert-warning">Gagal memuat data dashboard.</div>');
                }
            });
        }

        // ==================== BULANAN ====================
        function loadBulanan() {
            $('#bulananContent').html(
            '<div class="text-center py-5"><div class="spinner-border text-primary"></div></div>');

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
                    </tr>
                `;
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
                </div>
            `;
                    $('#bulananContent').html(html);
                }
            });
        }

        // ==================== MINGGUAN ====================
        function loadMingguan() {
            const bulan = $('#mingguanBulan').val();
            $('#mingguanContent').html(
                '<div class="text-center py-4"><div class="spinner-border text-primary"></div></div>');

            $.ajax({
                url: `{{ url('HR-dashboard/rekapan-penjualan/mingguan') }}/${currentYear}/${bulan}`,
                type: 'GET',
                success: function(res) {
                    if (!res.success) return;

                    let totalKelas = 0,
                        totalPax = 0,
                        totalNett = 0;
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
                    </tr>
                `;
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
                                    <td class="text-end"><strong>${formatRupiah(res.data.reduce((a,b) => a+b.total_harga_jual, 0))}</strong></td>
                                    <td class="text-end"><strong>${formatRupiah(totalNett)}</strong></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            `;
                    $('#mingguanContent').html(html);
                }
            });
        }

        // ==================== MATERI ====================
        function loadMateri() {
            $('#materiContent').html('<div class="text-center py-5"><div class="spinner-border text-primary"></div></div>');

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
                        <td>${i + 1}</td>
                        <td><strong>${d.nama_materi}</strong></td>
                        <td class="text-center"><span class="badge-soft badge-soft-primary">${d.total_kelas}</span></td>
                        <td class="text-center">${formatNumber(d.total_pax)}</td>
                        <td class="text-center">${d.rata_rata_pax}</td>
                        <td class="text-end">${formatRupiah(d.total_harga_jual)}</td>
                        <td class="text-end"><strong>${formatRupiah(d.total_nett)}</strong></td>
                        <td style="min-width: 180px;">
                            <div class="d-flex align-items-center gap-2">
                                <div class="progress-thin flex-grow-1">
                                    <div class="progress-thin-bar" style="width: ${kontribusi}%; background: #0d6efd;"></div>
                                </div>
                                <small class="fw-bold text-nowrap">${kontribusi.toFixed(1)}%</small>
                            </div>
                        </td>
                    </tr>
                `;
                    });

                    const html = `
                <div class="card-custom p-0 overflow-auto">
                    <table class="table-rekap">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Nama Materi</th>
                                <th class="text-center">Jumlah Kelas</th>
                                <th class="text-center">Total Pax</th>
                                <th class="text-center">Avg Pax/Kelas</th>
                                <th class="text-end">Total Harga Jual</th>
                                <th class="text-end">Total Nett</th>
                                <th>Kontribusi</th>
                            </tr>
                        </thead>
                        <tbody>${rows || '<tr><td colspan="8" class="text-center text-muted py-4">Tidak ada data</td></tr>'}</tbody>
                    </table>
                </div>
            `;
                    $('#materiContent').html(html);
                }
            });
        }

        // ==================== PROFITABILITAS ====================
        function loadProfit() {
            $('#profitContent').html('<div class="text-center py-5"><div class="spinner-border text-primary"></div></div>');

            $.ajax({
                url: `{{ url('HR-dashboard/rekapan-penjualan/profitabilitas') }}/${currentYear}`,
                type: 'GET',
                success: function(res) {
                    if (!res.success) return;

                    let rows = '';
                    let totalNett = 0,
                        totalFix = 0,
                        totalProfit = 0;
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
                    </tr>
                `;
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
                </div>
            `;
                    $('#profitContent').html(html);
                }
            });
        }
    </script>
@endsection
