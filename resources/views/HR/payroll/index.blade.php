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
            background-color: #fafbfc;
        }

        .page-header {
            margin-bottom: 1.5rem;
        }

        .page-title {
            font-size: 1.6rem;
            font-weight: 700;
            color: var(--gray-900);
            margin-bottom: .15rem;
        }

        .page-sub {
            color: var(--gray-400);
            font-size: .875rem;
        }

        .stat-card {
            border: none;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            transition: transform .25s, box-shadow .25s;
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-lg);
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            color: #fff;
        }

        .stat-value {
            font-size: 1.45rem;
            font-weight: 700;
            color: var(--gray-900);
            margin: .4rem 0 .15rem;
        }

        .stat-label {
            color: var(--gray-400);
            font-size: .8rem;
            margin: 0;
        }

        .nav-tabs-custom {
            border-bottom: 2px solid var(--gray-200);
        }

        .nav-tabs-custom .nav-link {
            border: none;
            color: var(--gray-400);
            font-weight: 600;
            padding: .85rem 1.25rem;
            font-size: .875rem;
            transition: color .2s;
        }

        .nav-tabs-custom .nav-link:hover {
            color: var(--pri);
        }

        .nav-tabs-custom .nav-link.active {
            color: var(--pri);
            border-bottom: 3px solid var(--pri);
            background: transparent;
        }

        .card-shell {
            border: none;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
        }

        .card-shell .card-body {
            padding: 1.5rem;
        }

        .btn-pri {
            background: var(--pri);
            border: none;
            color: #fff;
            font-weight: 600;
            padding: .5rem 1.25rem;
            border-radius: 8px;
            transition: all .25s;
        }

        .btn-pri:hover {
            background: var(--pri-dark);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(79, 70, 229, .35);
            color: #fff;
        }

        .status-badge {
            padding: .35rem .75rem;
            border-radius: 20px;
            font-size: .7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .5px;
        }

        .status-done {
            background: var(--success-light);
            color: var(--success);
        }

        .status-pending {
            background: var(--warning-light);
            color: var(--warning);
        }

        #payrollTable {
            border-collapse: separate;
            border-spacing: 0;
            width: 100%;
        }

        #payrollTable thead th {
            font-size: .75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .5px;
            color: var(--gray-600);
            background: var(--gray-50);
            border-bottom: 2px solid var(--gray-200) !important;
            border-top: none !important;
            padding: 0.75rem 1rem;
        }

        #payrollTable tbody tr {
            transition: background .15s;
        }

        #payrollTable tbody tr:hover {
            background: var(--pri-light) !important;
        }

        #payrollTable tbody td {
            vertical-align: middle;
            font-size: .875rem;
            border-bottom: 1px solid var(--gray-100) !important;
            border-top: none !important;
            padding: 0.75rem 1rem;
        }

        .pagination-custom {
            display: flex;
            gap: 0.25rem;
            flex-wrap: wrap;
        }

        .pagination-custom button {
            padding: 0.35rem 0.75rem;
            font-size: 0.8rem;
            border-radius: 6px;
            border: 1px solid var(--gray-200);
            background: #fff;
            color: var(--gray-600);
            cursor: pointer;
            transition: all 0.15s;
            font-weight: 600;
        }

        .pagination-custom button:hover {
            background: var(--pri-light);
            color: var(--pri);
            border-color: var(--pri);
        }

        .pagination-custom button.active {
            background: var(--pri);
            color: white;
            border-color: var(--pri);
        }

        .chart-wrap {
            position: relative;
            height: 260px;
        }

        .modal-header-custom {
            background: linear-gradient(135deg, var(--pri) 0%, var(--pri-dark) 100%);
            color: #fff;
            border-radius: 12px 12px 0 0;
        }

        .modal-header-custom .btn-close {
            filter: brightness(0) invert(1);
        }

        .skeleton {
            background: linear-gradient(90deg, #f0f2f5 25%, #e6e9ef 50%, #f0f2f5 75%);
            background-size: 200% 100%;
            animation: skeleton-shimmer 1.4s ease-in-out infinite;
            border-radius: 8px;
        }

        @keyframes skeleton-shimmer {
            0% {
                background-position: 200% 0;
            }

            100% {
                background-position: -200% 0;
            }
        }

        .skeleton-stat-value {
            height: 28px;
            width: 70%;
            margin: .4rem 0 .15rem;
        }

        .skeleton-stat-label {
            height: 12px;
            width: 50%;
        }

        .skeleton-chart {
            height: 260px;
            border-radius: 10px;
        }

        .skeleton-table-row td {
            padding: 14px 12px;
            vertical-align: middle;
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

        .stat-card {
            cursor: pointer;
        }

        .stat-card:active {
            transform: scale(0.98);
        }

        .chart-clickable {
            cursor: pointer;
        }

        .insight-metric {
            background: #f8fafc;
            border-radius: 10px;
            padding: 1rem 1.25rem;
            border: 1px solid #e2e8f0;
        }

        .insight-metric .label {
            font-size: .7rem;
            text-transform: uppercase;
            letter-spacing: .4px;
            color: #64748b;
        }

        .insight-metric .value {
            font-size: 1.35rem;
            font-weight: 700;
            color: #0f172a;
        }

        .insight-table th {
            font-size: .72rem;
            text-transform: uppercase;
            letter-spacing: .4px;
        }

        .badge-soft-success {
            background: #d1fae5;
            color: #059669;
        }

        .badge-soft-warning {
            background: #fef3c7;
            color: #d97706;
        }

        .badge-soft-danger {
            background: #fee2e2;
            color: #dc2626;
        }

        .badge-soft-info {
            background: #e0f2fe;
            color: #0284c7;
        }

        #guidePayrollModal .guide-section h5 {
            font-size: 1rem;
        }

        #guidePayrollModal .nav-tabs-custom .nav-link {
            font-size: .85rem;
            font-weight: 600;
            color: #9ca3af;
            border: none;
            padding: .75rem 1rem;
        }

        #guidePayrollModal .nav-tabs-custom .nav-link.active {
            color: #4f46e5;
            border-bottom: 3px solid #4f46e5;
            background: transparent;
        }

        #guidePayrollModal .guide-section {
            margin-bottom: 2rem;
        }

        #guidePayrollModal .guide-section h5 {
            font-size: 1rem;
            font-weight: 700;
            color: var(--gray-900);
            border-left: 4px solid var(--pri);
            padding-left: .75rem;
            margin-bottom: .75rem;
        }

        #guidePayrollModal .guide-section ol,
        #guidePayrollModal .guide-section ul {
            padding-left: 1.25rem;
            margin-bottom: .75rem;
        }

        #guidePayrollModal .guide-section li {
            margin-bottom: .4rem;
            font-size: .9rem;
            color: var(--gray-700);
            line-height: 1.55;
        }

        #guidePayrollModal .guide-section p {
            font-size: .9rem;
            color: var(--gray-700);
            line-height: 1.55;
        }

        #guidePayrollModal .guide-note {
            background: var(--pri-light);
            border-radius: 8px;
            padding: .75rem 1rem;
            font-size: .85rem;
            color: var(--gray-700);
        }

        #guidePayrollModal .guide-example {
            background: var(--gray-50);
            border: 1px dashed var(--gray-400);
            border-radius: 8px;
            padding: 1rem;
            font-size: .85rem;
        }

        #guidePayrollModal .guide-itsm {
            background: var(--danger-light);
            border: 1px solid #fecaca;
            border-radius: 10px;
            padding: 1.25rem;
        }

        #guidePayrollModal .guide-qa {
            margin-bottom: 1rem;
        }

        #guidePayrollModal .guide-qa strong {
            display: block;
            font-size: .9rem;
            color: var(--gray-900);
            margin-bottom: .2rem;
        }

        #guidePayrollModal .guide-qa span {
            font-size: .88rem;
            color: var(--gray-700);
            line-height: 1.55;
        }

        #guidePayrollModal .guide-tabs {
            border-bottom: 2px solid #e5e7eb;
        }

        #guidePayrollModal .guide-tabs .nav-link {
            border: none;
            border-bottom: 3px solid transparent;
            color: #6b7280;
            font-weight: 600;
            padding: .75rem 1.1rem;
            background: transparent;
            transition: color .2s, border-color .2s;
        }

        #guidePayrollModal .guide-tabs .nav-link:hover {
            color: #4f46e5;
        }

        #guidePayrollModal .guide-tabs .nav-link.active {
            color: #4f46e5;
            border-bottom-color: #4f46e5;
            background: transparent;
        }
    </style>

    <div class="container-fluid px-4 py-4">
        <div class="d-sm-flex align-items-center justify-content-between page-header">
            <div>
                <h1 class="page-title">Dashboard Payroll & Tunjangan</h1>
                <p class="page-sub mb-0">Periode: <strong id="periodLabel">-</strong></p>
            </div>
            <div class="d-flex gap-2 mt-2 mt-sm-0">
                <button id="exportExcel" class="btn btn-outline-success"><i class="fa-solid fa-file-excel me-2"></i>Export
                    Excel</button>
                <button id="exportPdf" class="btn btn-outline-danger"><i class="fa-solid fa-file-pdf me-2"></i>Export
                    PDF</button>
                <button class="btn btn-outline-info btn-sm" data-bs-toggle="modal" data-bs-target="#guidePayrollModal">
                    <i class="fa-solid fa-circle-question me-1"></i>Panduan
                </button>
            </div>
        </div>

        <div class="card card-shell mb-4">
            <div class="card-body py-3">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <span class="fw-bold text-primary" style="font-size:.875rem;"><i
                            class="fa-solid fa-filter me-1"></i>Filter & Pencarian</span>
                    <div class="d-flex gap-2 flex-wrap" id="filterGroup">
                        <select id="filterBulan" class="form-select form-select-sm" style="width:130px"></select>
                        <select id="filterTahun" class="form-select form-select-sm" style="width:90px"></select>
                        <div class="input-group input-group-sm" style="width: 220px;">
                            <span class="input-group-text bg-white border-end-0"><i
                                    class="fa-solid fa-search text-muted"></i></span>
                            <input type="text" id="searchPayroll" class="form-control border-start-0"
                                placeholder="Cari nama, divisi...">
                        </div>
                        <button id="btnFilter" class="btn btn-sm btn-pri"><i
                                class="fa-solid fa-filter me-1"></i>Terapkan</button>
                    </div>
                </div>
            </div>
        </div>

        <ul class="nav nav-tabs nav-tabs-custom mb-4" id="mainTabs">
            <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tabDashboard"><i
                        class="fa-solid fa-gauge-high me-2"></i>Dashboard & Statistik</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabData"><i
                        class="fa-solid fa-table me-2"></i>Data Payroll</button></li>
        </ul>

        <div class="tab-content">
            <div class="tab-pane fade show active" id="tabDashboard">
                <div id="metricsArea"></div>
                <div id="chartsArea"></div>
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
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content" style="border-radius:12px;border:none">
                <div class="modal-header modal-header-custom border-0">
                    <h5 class="modal-title fw-bold"><i class="fa-solid fa-receipt me-2"></i>Detail Payroll</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-2 mb-4">
                        <div class="col-md-6">
                            <div class="p-3 rounded bg-light"><small class="text-muted d-block"
                                    style="font-size:.7rem;text-transform:uppercase">Nama</small><strong
                                    id="modalNama">-</strong></div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 rounded bg-light"><small class="text-muted d-block"
                                    style="font-size:.7rem;text-transform:uppercase">Kode</small><strong
                                    id="modalKode">-</strong></div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 rounded bg-light"><small class="text-muted d-block"
                                    style="font-size:.7rem;text-transform:uppercase">Divisi</small><strong
                                    id="modalDivisi">-</strong></div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 rounded bg-light"><small class="text-muted d-block"
                                    style="font-size:.7rem;text-transform:uppercase">Jabatan</small><strong
                                    id="modalJabatan">-</strong></div>
                        </div>
                    </div>
                    <h6 class="fw-bold mb-3"
                        style="font-size:.8rem;color:var(--gray-600);text-transform:uppercase;letter-spacing:.5px">Rincian
                        Komponen</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered" style="font-size:.85rem">
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
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalInsight" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content" style="border-radius:14px;border:none;overflow:hidden">
                <div class="modal-header border-0" id="insightHeader"
                    style="background:linear-gradient(135deg,#4f46e5,#3730a3);color:#fff;padding:1.25rem 1.5rem">
                    <div>
                        <h5 class="modal-title fw-bold mb-0" id="insightTitle">Detail</h5>
                        <small class="opacity-75" id="insightSubtitle"></small>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0" id="insightBody" style="min-height:320px">
                </div>
                <div class="modal-footer border-0 bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <!-- ========== MODAL PANDUAN PAYROLL (versi awam) ========== -->
    <div class="modal fade" id="guidePayrollModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content" style="border:none;border-radius:14px;overflow:hidden">
                <div class="modal-header border-0"
                    style="background:linear-gradient(135deg,#4f46e5,#3730a3);color:#fff;padding:1.25rem 1.5rem">
                    <div>
                        <h5 class="modal-title fw-bold mb-0">
                            <i class="fa-solid fa-book-open me-2"></i>Panduan Dashboard Payroll & Tunjangan
                        </h5>
                        <small class="opacity-75">Cara pakai sederhana, arti angka, dan apa yang harus dilakukan jika data
                            kosong</small>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body p-0">
                    <ul class="nav nav-tabs guide-tabs px-3 pt-3" role="tablist">
                        <li class="nav-item">
                            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#guide-cara"
                                type="button">
                                Cara Pakai
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#guide-arti" type="button">
                                Arti Angka & Grafik
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#guide-tips" type="button">
                                Data Tidak Muncul?
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#guide-contoh" type="button">
                                Contoh Kasus
                            </button>
                        </li>
                    </ul>
                    <div class="tab-content p-4">

                        <!-- ========== TAB 1: CARA PAKAI ========== -->
                        <div class="tab-pane fade show active" id="guide-cara">
                            <div class="mb-4">
                                <h6 class="fw-bold text-primary mb-3">1. Pilih bulan & tahun</h6>
                                <p class="mb-2">Di bagian atas halaman ada pilihan <strong>Bulan</strong> dan
                                    <strong>Tahun</strong>.
                                </p>
                                <ol class="mb-3">
                                    <li>Pilih bulan yang ingin dilihat (misalnya Maret).</li>
                                    <li>Pilih tahunnya (misalnya 2026).</li>
                                    <li>Klik tombol <strong>Terapkan</strong>.</li>
                                </ol>
                                <div class="alert alert-light border small mb-0">
                                    <strong>Catatan:</strong> Yang muncul adalah data <strong>bulan yang Anda
                                        pilih</strong>. Pastikan bulannya sama dengan bulan tunjangan yang sudah dihitung
                                    oleh HRD.
                                </div>
                            </div>

                            <div class="mb-4">
                                <h6 class="fw-bold text-primary mb-3">2. Lihat ringkasan di Dashboard</h6>
                                <p class="mb-2">Di tab <strong>Dashboard & Statistik</strong> Anda akan melihat:</p>
                                <ul>
                                    <li><strong>Kartu angka</strong> (Total Karyawan, Sudah Dihitung, dll) → <em>bisa
                                            diklik</em> untuk melihat detail.</li>
                                    <li><strong>Grafik</strong> (lingkaran, batang, garis) → <em>juga bisa diklik</em> untuk
                                        melihat rincian.</li>
                                </ul>
                                <p class="small text-muted mb-0">Setiap kartu dan grafik punya tampilan detail yang
                                    berbeda, jadi lebih mudah dipahami.</p>
                            </div>

                            <div class="mb-4">
                                <h6 class="fw-bold text-primary mb-3">3. Lihat daftar karyawan</h6>
                                <p class="mb-2">Pindah ke tab <strong>Data Payroll</strong>.</p>
                                <ul>
                                    <li>Ada tabel daftar nama karyawan.</li>
                                    <li>Bisa mencari nama, kode, divisi, atau jabatan di kotak pencarian.</li>
                                    <li>Klik ikon mata (👁) di samping nama untuk melihat rincian gaji, tunjangan, dan
                                        potongan orang tersebut.</li>
                                </ul>
                            </div>

                            <div class="mb-0">
                                <h6 class="fw-bold text-primary mb-3">4. Unduh laporan</h6>
                                <ul class="mb-0">
                                    <li><strong>Export Excel</strong> → cocok kalau ingin diolah lebih lanjut di Excel.</li>
                                    <li><strong>Export PDF</strong> → cocok untuk laporan yang akan dicetak atau dikirim ke
                                        atasan.</li>
                                </ul>
                            </div>
                        </div>

                        <!-- ========== TAB 2: ARTI ANGKA & GRAFIK ========== -->
                        <div class="tab-pane fade" id="guide-arti">
                            <div class="mb-4">
                                <h6 class="fw-bold text-success mb-3">Arti kartu ringkasan</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered" style="font-size:.9rem">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Kartu</th>
                                                <th>Artinya</th inter>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td><strong>Total Karyawan</strong></td>
                                                <td>Berapa banyak karyawan yang seharusnya masuk perhitungan di bulan ini.
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><strong>Sudah Dihitung</strong></td>
                                                <td>Karyawan yang sudah punya data tunjangan / potongan di bulan tersebut.
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><strong>Belum Dihitung</strong></td>
                                                <td>Karyawan yang masih kosong (belum ada data tunjangan sama sekali).</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Total Basic Salary</strong></td>
                                                <td>Jumlah seluruh gaji pokok + tunjangan jabatan.</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Total Tunjangan</strong></td>
                                                <td>Jumlah seluruh tunjangan (yang menambah gaji).</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Potongan BPJS Tenaga Kerja</strong></td>
                                                <td>Jumlah potongan BPJS Ketenagakerjaan (JHT, JKK, JKM, dll).</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Potongan BPJS Kesehatan</strong></td>
                                                <td>Jumlah potongan BPJS Kesehatan.</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Potongan PPh 21</strong></td>
                                                <td>Jumlah potongan pajak penghasilan (PPh 21) karyawan.</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="mb-0">
                                <h6 class="fw-bold text-success mb-3">Arti grafik</h6>
                                <ul class="mb-0" style="line-height:1.8">
                                    <li><strong>Distribusi Gaji Bersih</strong><br>
                                        Menunjukkan berapa banyak karyawan yang gaji bersihnya di rentang tertentu (misalnya
                                        di bawah 5 juta, 5–10 juta, dst).</li>
                                    <li class="mt-2"><strong>Tunjangan per Divisi</strong><br>
                                        Divisi mana yang total tunjangannya paling besar (hanya 8 teratas yang ditampilkan).
                                    </li>
                                    <li class="mt-2"><strong>Potongan Terbanyak</strong><br>
                                        Jenis potongan apa yang paling besar jumlahnya di bulan tersebut.</li>
                                    <li class="mt-2"><strong>Trend Payroll Bulanan</strong><br>
                                        Pergerakan total gaji sepanjang tahun (naik atau turun tiap bulan).</li>
                                </ul>
                                <p class="small text-muted mt-3 mb-0">Semua kartu dan grafik bisa diklik untuk melihat
                                    detail lebih lengkap.</p>
                            </div>
                        </div>

                        <!-- ========== TAB 3: DATA TIDAK MUNCUL ========== -->
                        <div class="tab-pane fade" id="guide-tips">
                            <div class="mb-4">
                                <h6 class="fw-bold text-warning mb-3">Kalau data kosong atau aneh, coba ini dulu</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered" style="font-size:.9rem">
                                        <thead class="table-light">
                                            <tr>
                                                <th style="width:30%">Yang terjadi</th>
                                                <th style="width:35%">Kemungkinan penyebab</th>
                                                <th>Yang bisa dilakukan</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>Tabel kosong / Total Karyawan = 0</td>
                                                <td>Bulan/tahun salah, atau pencarian terlalu spesifik</td>
                                                <td>
                                                    1. Cek lagi bulan & tahun<br>
                                                    2. Kosongkan kotak pencarian<br>
                                                    3. Klik Terapkan lagi
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Banyak yang “Belum Dihitung”</td>
                                                <td>Tunjangan bulan itu belum diinput / digenerate</td>
                                                <td>Cek lagi data tunjangan apakah sudah di generate atau belum</td>
                                            </tr>
                                            <tr>
                                                <td>Gaji Pokok muncul 0</td>
                                                <td>Data gaji belum diisi</td>
                                                <td>coba cek update data gaji karyawan yang bersangkutan</td>
                                            </tr>
                                            <tr>
                                                <td>Karyawan yang sudah resign masih muncul</td>
                                                <td>Tanggal resign-nya masih di dalam bulan yang dipilih</td>
                                                <td>Ini normal. Kalau filter diganti ke bulan setelah resign, dia akan
                                                    hilang</td>
                                            </tr>
                                            <tr>
                                                <td>Karyawan baru tidak muncul</td>
                                                <td>Tanggal mulai kerjanya masih di masa depan (belum masuk periode)</td>
                                                <td>Pastikan tanggal mulai kerja sudah diisi dengan benar di data karyawan
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="mb-0">
                                <h6 class="fw-bold text-warning mb-3">Tips praktis</h6>
                                <ul class="mb-0" style="line-height:1.8">
                                    <li>Selalu samakan bulan filter dengan bulan yang sudah dihitung tunjangannya oleh HRD.
                                    </li>
                                    <li>Kalau ingin cek cepat, klik saja kartu atau grafik — lebih cepat daripada buka tab
                                        Data Payroll.</li>
                                    <li>Pakai Export Excel kalau ingin menghitung ulang atau membuat pivot sendiri.</li>
                                    <li>Pakai Export PDF kalau ingin laporan yang rapi untuk atasan.</li>
                                    <li>Kalau angka terasa aneh, bandingkan dengan data di menu Tunjangan (HRD) untuk bulan
                                        yang sama.</li>
                                </ul>
                            </div>
                        </div>

                        <!-- ========== TAB 4: CONTOH KASUS ========== -->
                        <div class="tab-pane fade" id="guide-contoh">
                            <div class="mb-4">
                                <h6 class="fw-bold text-danger mb-2">Contoh 1 — Karyawan yang masih aktif</h6>
                                <div class="p-3 rounded bg-light border">
                                    <p class="mb-1"><strong>Situasi:</strong> Budi mulai kerja 1 Januari 2024 dan belum
                                        resign.</p>
                                    <p class="mb-1"><strong>Filter:</strong> Maret 2026</p>
                                    <p class="mb-0"><strong>Hasil:</strong> Budi muncul di daftar. Kalau tunjangannya
                                        sudah dihitung → status “Sudah Dihitung”. Kalau belum → “Belum Dihitung”.</p>
                                </div>
                            </div>

                            <div class="mb-4">
                                <h6 class="fw-bold text-danger mb-2">Contoh 2 — Karyawan resign di tengah bulan</h6>
                                <div class="p-3 rounded bg-light border">
                                    <p class="mb-1"><strong>Situasi:</strong> Ani resign tanggal 13 Maret 2026.</p>
                                    <p class="mb-1"><strong>Filter Maret 2026:</strong> Ani masih muncul (status Resign).
                                    </p>
                                    <p class="mb-0"><strong>Filter April 2026:</strong> Ani sudah tidak muncul lagi.</p>
                                </div>
                            </div>

                            <div class="mb-4">
                                <h6 class="fw-bold text-danger mb-2">Contoh 3 — Karyawan baru</h6>
                                <div class="p-3 rounded bg-light border">
                                    <p class="mb-1"><strong>Situasi:</strong> Citra mulai kerja tanggal 5 Maret 2026.</p>
                                    <p class="mb-1"><strong>Filter Maret 2026:</strong> Citra muncul dengan status New
                                        Hire.</p>
                                    <p class="mb-0"><strong>Filter Februari 2026:</strong> Citra tidak muncul (belum
                                        mulai kerja).</p>
                                </div>
                            </div>

                            <div class="mb-4">
                                <h6 class="fw-bold text-danger mb-2">Contoh 4 — Data tunjangan masih ada meski sudah lama
                                    resign</h6>
                                <div class="p-3 rounded bg-light border">
                                    <p class="mb-1"><strong>Situasi:</strong> Dedi sudah resign tahun 2024, tapi masih
                                        ada data tunjangan di Maret 2026 (data lama).</p>
                                    <p class="mb-0"><strong>Hasil:</strong> Dedi tetap muncul di daftar karena sistem
                                        tetap menampilkan orang yang punya data tunjangan di bulan tersebut.</p>
                                </div>
                            </div>

                            <div class="mb-0">
                                <h6 class="fw-bold text-danger mb-2">Contoh 5 — Potongan BPJS / PPh tidak terbaca</h6>
                                <div class="p-3 rounded bg-light border">
                                    <p class="mb-1"><strong>Situasi:</strong> Ada potongan, tapi di kartu ringkasan angka
                                        BPJS / PPh tetap 0.</p>
                                    <p class="mb-0"><strong>Penyebab & solusi:</strong> Nama jenis potongannya terlalu
                                        umum (misalnya “Potongan Lain”). Minta HRD ubah namanya supaya mengandung kata
                                        “BPJS”, “JHT”, “Kesehatan”, “PPh”, atau “Pajak” agar sistem bisa mengelompokkannya
                                        dengan benar.</p>
                                </div>
                            </div>

                            <div class="guide-section mt-4">
                                <div class="guide-itsm">
                                    <p class="mb-2"><strong>Jika ada permintaan perubahan fitur/tampilan, atau Anda
                                            menemukan error atau data yang tidak sesuai, silakan hubungi Tim ITSM.</strong>
                                    </p>
                                    <ol class="mb-3">
                                        <li>Klik tombol <strong>Hubungi Tim ITSM</strong> di bawah.</li>
                                        <li>Pilih jenis permintaan (laporan error, fitur baru, perbaikan, atau masalah
                                            data).</li>
                                        <li>Tuliskan apa yang terjadi, di bagian mana, filter apa yang dipakai, dan hasil
                                            yang Anda harapkan.</li>
                                        <li>Lampirkan tangkapan layar bila ada, lalu kirim.</li>
                                    </ol>
                                    <a href="{{ route('tickets.index') }}" class="btn btn-danger btn-sm" target="_blank">
                                        <i class="fa-solid fa-ticket me-1"></i>Hubungi Tim ITSM
                                    </a>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="modal-footer border-0 bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
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
        const formatShort = (n) => {
            if (n >= 1e9) return 'Rp ' + (n / 1e9).toFixed(1) + 'M';
            if (n >= 1e6) return 'Rp ' + (n / 1e6).toFixed(1) + 'Jt';
            if (n >= 1e3) return 'Rp ' + (n / 1e3).toFixed(0) + 'rb';
            return formatIDR(n);
        };

        let salaryChart = null,
            allowanceChart = null,
            trendChart = null,
            deductionChart = null;
        let currentPage = 1,
            allData = [],
            searchQuery = '';
        let lastSummary = {},
            lastCharts = {},
            lastPeriod = '';

        function skeletonMetrics() {
            return `
            <div class="row g-3 mb-4">
                ${[1,2,3,4].map(() => `
                            <div class="col-xl-3 col-md-6">
                                <div class="card stat-card">
                                    <div class="card-body d-flex align-items-center justify-content-between">
                                        <div style="flex:1">
                                            <div class="skeleton skeleton-stat-label"></div>
                                            <div class="skeleton skeleton-stat-value"></div>
                                        </div>
                                        <div class="skeleton" style="width:48px;height:48px;border-radius:10px;flex-shrink:0;"></div>
                                    </div>
                                </div>
                            </div>`).join('')}
            </div>
            <div class="row g-3 mb-4">
                ${[1,2,3,4].map(() => `
                            <div class="col-xl-3 col-md-6">
                                <div class="card stat-card">
                                    <div class="card-body d-flex align-items-center justify-content-between">
                                        <div style="flex:1">
                                            <div class="skeleton skeleton-stat-label"></div>
                                            <div class="skeleton skeleton-stat-value"></div>
                                        </div>
                                        <div class="skeleton" style="width:48px;height:48px;border-radius:10px;flex-shrink:0;"></div>
                                    </div>
                                </div>
                            </div>`).join('')}
            </div>`;
        }

        function skeletonCharts() {
            return `
            <div class="row g-4 mb-4">
                <div class="col-lg-6">
                    <div class="card card-shell"><div class="card-body">
                        <div class="skeleton mb-3" style="height:16px;width:55%;"></div>
                        <div class="skeleton skeleton-chart"></div>
                    </div></div>
                </div>
                <div class="col-lg-6">
                    <div class="card card-shell"><div class="card-body">
                        <div class="skeleton mb-3" style="height:16px;width:50%;"></div>
                        <div class="skeleton skeleton-chart"></div>
                    </div></div>
                </div>
            </div>
            <div class="row g-4 mb-4">
                <div class="col-lg-6">
                    <div class="card card-shell"><div class="card-body">
                        <div class="skeleton mb-3" style="height:16px;width:55%;"></div>
                        <div class="skeleton skeleton-chart"></div>
                    </div></div>
                </div>
                <div class="col-lg-6">
                    <div class="card card-shell"><div class="card-body">
                        <div class="skeleton mb-3" style="height:16px;width:60%;"></div>
                        <div class="skeleton" style="height:220px;border-radius:10px;"></div>
                    </div></div>
                </div>
            </div>`;
        }

        function skeletonTableRows(rows = 8) {
            let html = '';
            for (let r = 0; r < rows; r++) {
                html += `<tr class="skeleton-table-row">
                    <td><div class="skeleton" style="height:14px;width:24px;"></div></td>
                    <td><div class="skeleton mb-1" style="height:14px;width:70%;"></div><div class="skeleton" style="height:11px;width:40%;"></div></td>
                    <td><div class="skeleton mb-1" style="height:14px;width:60%;"></div><div class="skeleton" style="height:11px;width:45%;"></div></td>
                    <td class="text-end"><div class="skeleton ms-auto" style="height:14px;width:80px;"></div></td>
                    <td class="text-end"><div class="skeleton ms-auto" style="height:14px;width:70px;"></div></td>
                    <td class="text-end"><div class="skeleton ms-auto" style="height:14px;width:70px;"></div></td>
                    <td class="text-end"><div class="skeleton ms-auto" style="height:14px;width:90px;"></div></td>
                    <td class="text-center"><div class="skeleton mx-auto" style="height:24px;width:60px;border-radius:20px;"></div></td>
                    <td class="text-center"><div class="skeleton mx-auto" style="height:30px;width:36px;border-radius:6px;"></div></td>
                </tr>`;
            }
            return html;
        }

        /* ========== INSIGHT MODAL (berbeda per jenis) ========== */
        function openInsight(type, extra = {}) {
            const s = lastSummary;
            const c = lastCharts;
            const period = lastPeriod || '-';

            let title = '',
                subtitle = period,
                headerBg = 'linear-gradient(135deg,#4f46e5,#3730a3)';
            let body = '';

            // ========== STAT CARDS ==========
            if (type === 'total_karyawan') {
                title = 'Total Karyawan Eligible';
                headerBg = 'linear-gradient(135deg,#4f46e5,#7c3aed)';
                const active = allData.filter(r => r.status === 'Active' || r.status === 'Sudah Dihitung' || r.status ===
                    'Belum Dihitung');
                body = `
                <div class="p-4">
                    <div class="row g-3 mb-4">
                        <div class="col-md-4"><div class="insight-metric"><div class="label">Total Eligible</div><div class="value">${s.total_karyawan || 0}</div></div></div>
                        <div class="col-md-4"><div class="insight-metric"><div class="label">New Hire</div><div class="value text-info">${s.new_hire_count || 0}</div></div></div>
                        <div class="col-md-4"><div class="insight-metric"><div class="label">Active</div><div class="value text-success">${s.active_count || 0}</div></div></div>
                    </div>
                    <p class="text-muted small mb-3">Daftar karyawan pada halaman saat ini (lihat tab Data Payroll untuk daftar lengkap).</p>
                    <div class="table-responsive">
                        <table class="table table-sm insight-table">
                            <thead class="table-light"><tr><th>Nama</th><th>Divisi</th><th>Jabatan</th><th>Status</th><th class="text-end">Gaji Bersih</th></tr></thead>
                            <tbody>
                                ${allData.map(r => `
                                                <tr>
                                                    <td><strong>${r.nama}</strong><br><small class="text-muted">${r.kode||'-'}</small></td>
                                                    <td>${r.divisi}</td>
                                                    <td>${r.jabatan}</td>
                                                    <td><span class="badge badge-soft-info">${r.status}</span></td>
                                                    <td class="text-end fw-semibold">${formatIDR(r.gaji_bersih)}</td>
                                                </tr>`).join('') || '<tr><td colspan="5" class="text-center text-muted">Tidak ada data</td></tr>'}
                            </tbody>
                        </table>
                    </div>
                </div>`;
            } else if (type === 'sudah_dihitung') {
                title = 'Karyawan Sudah Dihitung';
                headerBg = 'linear-gradient(135deg,#059669,#10b981)';
                const list = allData.filter(r => r.status === 'Sudah Dihitung' || (r.total_tunjangan > 0 || r
                    .total_potongan > 0));
                body = `
                <div class="p-4">
                    <div class="alert alert-success d-flex align-items-center gap-2 py-2">
                        <i class="fa-solid fa-check-circle"></i>
                        <div><strong>${s.sudah_dihitung || 0}</strong> karyawan sudah memiliki data perhitungan di periode ini.</div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm insight-table">
                            <thead class="table-light"><tr><th>Nama</th><th>Divisi</th><th class="text-end">Tunjangan</th><th class="text-end">Potongan</th><th class="text-end">Gaji Bersih</th></tr></thead>
                            <tbody>
                                ${list.map(r => `
                                                <tr>
                                                    <td><strong>${r.nama}</strong></td>
                                                    <td>${r.divisi}</td>
                                                    <td class="text-end text-success">${formatIDR(r.total_tunjangan)}</td>
                                                    <td class="text-end text-danger">${formatIDR(r.total_potongan)}</td>
                                                    <td class="text-end fw-bold">${formatIDR(r.gaji_bersih)}</td>
                                                </tr>`).join('') || '<tr><td colspan="5" class="text-center text-muted py-4">Belum ada data di halaman ini</td></tr>'}
                            </tbody>
                        </table>
                    </div>
                </div>`;
            } else if (type === 'belum_dihitung') {
                title = 'Karyawan Belum Dihitung';
                headerBg = 'linear-gradient(135deg,#d97706,#f59e0b)';
                const list = allData.filter(r => r.status === 'Belum Dihitung' || (r.total_tunjangan === 0 && r
                    .total_potongan === 0));
                body = `
                <div class="p-4">
                    <div class="alert alert-warning d-flex align-items-center gap-2 py-2">
                        <i class="fa-solid fa-exclamation-triangle"></i>
                        <div><strong>${s.belum_dihitung || 0}</strong> karyawan belum memiliki data tunjangan/potongan di periode ini.</div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm insight-table">
                            <thead class="table-light"><tr><th>Nama</th><th>Divisi</th><th>Jabatan</th><th class="text-end">Gaji Pokok</th><th>Status</th></tr></thead>
                            <tbody>
                                ${list.map(r => `
                                                <tr>
                                                    <td><strong>${r.nama}</strong></td>
                                                    <td>${r.divisi}</td>
                                                    <td>${r.jabatan}</td>
                                                    <td class="text-end">${formatIDR(r.gaji_pokok)}</td>
                                                    <td><span class="badge badge-soft-warning">${r.status}</span></td>
                                                </tr>`).join('') || '<tr><td colspan="5" class="text-center text-muted py-4">Semua karyawan sudah dihitung (di halaman ini)</td></tr>'}
                            </tbody>
                        </table>
                    </div>
                </div>`;
            } else if (type === 'basic_salary') {
                title = 'Total Basic Salary';
                subtitle = 'Gaji Pokok + Tunjangan Jabatan · ' + period;
                headerBg = 'linear-gradient(135deg,#0ea5e9,#06b6d4)';
                const ranges = c.salary_ranges || {
                    labels: [],
                    counts: [],
                    averages: []
                };
                body = `
                <div class="p-4">
                    <div class="row g-3 mb-4">
                        <div class="col-md-4"><div class="insight-metric"><div class="label">Total Basic Salary</div><div class="value text-info">${formatIDR(s.total_salary || 0)}</div></div></div>
                        <div class="col-md-4"><div class="insight-metric"><div class="label">Rata-rata / Karyawan</div><div class="value">${formatIDR(s.total_karyawan ? (s.total_salary / s.total_karyawan) : 0)}</div></div></div>
                        <div class="col-md-4"><div class="insight-metric"><div class="label">Jumlah Karyawan</div><div class="value">${s.total_karyawan || 0}</div></div></div>
                    </div>
                    <h6 class="fw-bold mb-3" style="font-size:.8rem;color:#64748b;text-transform:uppercase">Distribusi berdasarkan Gaji Bersih</h6>
                    <div class="table-responsive">
                        <table class="table table-sm insight-table">
                            <thead class="table-light"><tr><th>Rentang</th><th class="text-center">Jumlah</th><th class="text-end">Rata-rata</th></tr></thead>
                            <tbody>
                                ${(ranges.labels || []).map((l, i) => `
                                                <tr>
                                                    <td><span class="badge badge-soft-info">${l}</span></td>
                                                    <td class="text-center fw-semibold">${ranges.counts[i] || 0}</td>
                                                    <td class="text-end">${formatIDR(ranges.averages?.[i] || 0)}</td>
                                                </tr>`).join('')}
                            </tbody>
                        </table>
                    </div>
                </div>`;
            } else if (type === 'total_tunjangan') {
                title = 'Total Tunjangan';
                headerBg = 'linear-gradient(135deg,#0284c7,#38bdf8)';
                const byDiv = c.allowance_by_divisi || {
                    labels: [],
                    allowance: [],
                    avg_salary: []
                };
                body = `
                <div class="p-4">
                    <div class="row g-3 mb-4">
                        <div class="col-md-6"><div class="insight-metric"><div class="label">Total Tunjangan (Bersih)</div><div class="value text-info">${formatIDR(s.total_tunjangan || 0)}</div></div></div>
                        <div class="col-md-6"><div class="insight-metric"><div class="label">Rata-rata per Karyawan</div><div class="value">${formatIDR(s.total_karyawan ? (s.total_tunjangan / s.total_karyawan) : 0)}</div></div></div>
                    </div>
                    <h6 class="fw-bold mb-3" style="font-size:.8rem;color:#64748b;text-transform:uppercase">Tunjangan per Divisi (Top)</h6>
                    <div class="table-responsive">
                        <table class="table table-sm insight-table">
                            <thead class="table-light"><tr><th>Divisi</th><th class="text-end">Total Tunjangan</th><th class="text-end">Rata-rata Gaji</th></tr></thead>
                            <tbody>
                                ${(byDiv.labels || []).map((l, i) => `
                                                <tr>
                                                    <td><strong>${l}</strong></td>
                                                    <td class="text-end text-success fw-semibold">${formatIDR(byDiv.allowance[i] || 0)}</td>
                                                    <td class="text-end">${formatIDR(byDiv.avg_salary?.[i] || 0)}</td>
                                                </tr>`).join('') || '<tr><td colspan="3" class="text-center text-muted">Tidak ada data</td></tr>'}
                            </tbody>
                        </table>
                    </div>
                </div>`;
            } else if (type === 'bpjs_tk' || type === 'bpjs_kes' || type === 'pph21') {
                const map = {
                    bpjs_tk: {
                        title: 'Potongan BPJS Tenaga Kerja',
                        color: '#dc2626',
                        key: 'total_potongan_bpjs_tk',
                        icon: 'helmet-safety'
                    },
                    bpjs_kes: {
                        title: 'Potongan BPJS Kesehatan',
                        color: '#e11d48',
                        key: 'total_potongan_bpjs_kes',
                        icon: 'heart-pulse'
                    },
                    pph21: {
                        title: 'Potongan PPh 21',
                        color: '#b91c1c',
                        key: 'total_potongan_pph21',
                        icon: 'file-invoice-dollar'
                    }
                };
                const m = map[type];
                title = m.title;
                headerBg = `linear-gradient(135deg,${m.color},#f87171)`;
                const topDed = c.top_deductions || {
                    labels: [],
                    total_values: [],
                    employee_counts: [],
                    averages: []
                };
                body = `
                <div class="p-4">
                    <div class="row g-3 mb-4">
                        <div class="col-md-4"><div class="insight-metric"><div class="label">Total Potongan</div><div class="value text-danger">${formatIDR(s[m.key] || 0)}</div></div></div>
                        <div class="col-md-4"><div class="insight-metric"><div class="label">Total Semua Potongan</div><div class="value">${formatIDR(s.total_potongan || 0)}</div></div></div>
                        <div class="col-md-4"><div class="insight-metric"><div class="label">% dari Total Potongan</div><div class="value">${s.total_potongan ? ((s[m.key] / s.total_potongan) * 100).toFixed(1) : 0}%</div></div></div>
                    </div>
                    <h6 class="fw-bold mb-3" style="font-size:.8rem;color:#64748b;text-transform:uppercase">Top Potongan Lainnya di Periode Ini</h6>
                    <div class="table-responsive">
                        <table class="table table-sm insight-table">
                            <thead class="table-light"><tr><th>Jenis Potongan</th><th class="text-center">Karyawan</th><th class="text-end">Total</th><th class="text-end">Rata-rata</th></tr></thead>
                            <tbody>
                                ${(topDed.labels || []).map((l, i) => `
                                                <tr>
                                                    <td>${l}</td>
                                                    <td class="text-center">${topDed.employee_counts?.[i] || 0}</td>
                                                    <td class="text-end text-danger fw-semibold">${formatIDR(topDed.total_values[i] || 0)}</td>
                                                    <td class="text-end">${formatIDR(topDed.averages?.[i] || 0)}</td>
                                                </tr>`).join('') || '<tr><td colspan="4" class="text-center text-muted">Tidak ada data potongan</td></tr>'}
                            </tbody>
                        </table>
                    </div>
                </div>`;
            }

            // ========== CHARTS ==========
            else if (type === 'salary_range') {
                title = 'Distribusi Gaji Bersih';
                headerBg = 'linear-gradient(135deg,#4f46e5,#7c3aed)';
                const ranges = c.salary_ranges || {
                    labels: [],
                    counts: [],
                    averages: []
                };
                const totalCount = (ranges.counts || []).reduce((a, b) => a + b, 0);
                body = `
                <div class="p-4">
                    <div class="row g-3 mb-4">
                        ${(ranges.labels || []).map((l, i) => `
                                        <div class="col-md-4 col-6">
                                            <div class="insight-metric text-center">
                                                <div class="label">${l}</div>
                                                <div class="value">${ranges.counts[i] || 0}</div>
                                                <small class="text-muted">${totalCount ? ((ranges.counts[i] / totalCount) * 100).toFixed(0) : 0}% · avg ${formatShort(ranges.averages?.[i] || 0)}</small>
                                            </div>
                                        </div>`).join('')}
                    </div>
                    <p class="text-muted small">Klik pada slice chart untuk fokus ke rentang tertentu. Data di atas adalah ringkasan seluruh karyawan eligible di periode ini.</p>
                </div>`;
            } else if (type === 'allowance_divisi') {
                title = 'Tunjangan per Divisi';
                headerBg = 'linear-gradient(135deg,#059669,#10b981)';
                const byDiv = c.allowance_by_divisi || {
                    labels: [],
                    allowance: [],
                    avg_salary: []
                };
                const maxAll = Math.max(...(byDiv.allowance || [0]), 1);
                body = `
                <div class="p-4">
                    <div class="mb-4">
                        ${(byDiv.labels || []).map((l, i) => {
                            const pct = ((byDiv.allowance[i] || 0) / maxAll) * 100;
                            return `
                                        <div class="mb-3">
                                            <div class="d-flex justify-content-between mb-1">
                                                <strong style="font-size:.875rem">${l}</strong>
                                                <span class="text-success fw-semibold">${formatIDR(byDiv.allowance[i] || 0)}</span>
                                            </div>
                                            <div class="progress" style="height:8px;border-radius:4px">
                                                <div class="progress-bar bg-success" style="width:${pct}%"></div>
                                            </div>
                                            <small class="text-muted">Rata-rata gaji: ${formatIDR(byDiv.avg_salary?.[i] || 0)}</small>
                                        </div>`;
                        }).join('') || '<p class="text-muted">Tidak ada data</p>'}
                    </div>
                </div>`;
            } else if (type === 'top_deductions') {
                title = 'Potongan Terbanyak';
                headerBg = 'linear-gradient(135deg,#dc2626,#f87171)';
                const d = c.top_deductions || {
                    labels: [],
                    total_values: [],
                    employee_counts: [],
                    averages: []
                };
                body = `
                <div class="p-4">
                    <div class="table-responsive">
                        <table class="table insight-table">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Jenis Potongan</th>
                                    <th class="text-center">Jumlah Karyawan</th>
                                    <th class="text-end">Total Nilai</th>
                                    <th class="text-end">Rata-rata / Orang</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${(d.labels || []).map((l, i) => `
                                                <tr>
                                                    <td class="text-muted">${i + 1}</td>
                                                    <td><strong>${l}</strong></td>
                                                    <td class="text-center"><span class="badge badge-soft-danger">${d.employee_counts?.[i] || 0}</span></td>
                                                    <td class="text-end text-danger fw-bold">${formatIDR(d.total_values[i] || 0)}</td>
                                                    <td class="text-end">${formatIDR(d.averages?.[i] || 0)}</td>
                                                </tr>`).join('') || '<tr><td colspan="5" class="text-center text-muted py-4">Tidak ada data potongan</td></tr>'}
                            </tbody>
                        </table>
                    </div>
                </div>`;
            } else if (type === 'monthly_trend') {
                title = 'Trend Payroll Bulanan';
                subtitle = 'Tahun ' + ($('#filterTahun').val() || new Date().getFullYear());
                headerBg = 'linear-gradient(135deg,#0284c7,#0ea5e9)';
                const trend = c.monthly_trend || [];
                body = `
                <div class="p-4">
                    <div class="table-responsive">
                        <table class="table table-sm insight-table">
                            <thead class="table-light">
                                <tr>
                                    <th>Bulan</th>
                                    <th class="text-center">New Hire</th>
                                    <th class="text-center">Active</th>
                                    <th class="text-center">Resign</th>
                                    <th class="text-end">Total Gaji Pokok</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${trend.map(t => `
                                                <tr>
                                                    <td><strong>${t.month}</strong></td>
                                                    <td class="text-center"><span class="badge badge-soft-info">${t.new_hires || 0}</span></td>
                                                    <td class="text-center"><span class="badge badge-soft-success">${t.actives || 0}</span></td>
                                                    <td class="text-center"><span class="badge badge-soft-danger">${t.resigns || 0}</span></td>
                                                    <td class="text-end fw-semibold">${formatIDR(t.total_gaji || 0)}</td>
                                                </tr>`).join('') || '<tr><td colspan="5" class="text-center text-muted">Tidak ada data</td></tr>'}
                            </tbody>
                        </table>
                    </div>
                </div>`;
            } else {
                title = 'Detail';
                body = `<div class="p-4 text-muted">Tidak ada data detail.</div>`;
            }

            $('#insightTitle').text(title);
            $('#insightSubtitle').text(subtitle);
            $('#insightHeader').css('background', headerBg);
            $('#insightBody').html(body);
            new bootstrap.Modal(document.getElementById('modalInsight')).show();
        }

        /* ========== MAIN ========== */
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

            window.loadData = function(page = 1) {
                currentPage = page;
                $('#metricsArea').html(skeletonMetrics());
                $('#chartsArea').html(skeletonCharts());
                $('#payrollBody').html(skeletonTableRows(8));
                $('#periodLabel').text('...');
                $('#showingStart, #showingEnd, #totalItems').text('0');
                $('#paginationContainer').html('');

                const params = {
                    month: $('#filterBulan').val(),
                    year: $('#filterTahun').val(),
                    search: searchQuery,
                    page: page
                };

                $.get("{{ route('HR.payroll.dashboard') }}", params, function(res) {
                    if (!res.success) {
                        alert(res.message);
                        $('#metricsArea').html('');
                        $('#chartsArea').html(
                            '<div class="text-center py-5 text-danger">Gagal memuat data</div>');
                        $('#payrollBody').html(
                            '<tr><td colspan="9" class="text-center text-danger py-5">Gagal memuat data</td></tr>'
                        );
                        return;
                    }
                    allData = res.data;
                    lastSummary = res.summary || {};
                    lastCharts = res.charts || {};
                    lastPeriod = res.period?.display || '-';
                    $('#periodLabel').text(lastPeriod);
                    renderMetrics(res.summary);
                    renderChartsSafe(res.charts);
                    renderTable(res);
                }).fail(function(xhr, status, error) {
                    console.error('Error:', error);
                    $('#metricsArea').html('');
                    $('#chartsArea').html(
                        '<div class="text-center py-5 text-danger">Gagal memuat data: ' + error +
                        '</div>');
                    $('#payrollBody').html(
                        '<tr><td colspan="9" class="text-center text-danger py-5">Gagal memuat data</td></tr>'
                    );
                });
            };

            function renderMetrics(summary) {
                $('#metricsArea').html(`
                <div class="row g-3 mb-4">
                    <div class="col-xl-3 col-md-6">
                        <div class="card stat-card" onclick="openInsight('total_karyawan')" title="Klik untuk detail">
                            <div class="card-body d-flex align-items-center justify-content-between">
                                <div><p class="stat-label">Total Karyawan</p><h3 class="stat-value">${summary.total_karyawan || 0}</h3></div>
                                <div class="stat-icon" style="background:linear-gradient(135deg,#4f46e5,#7c3aed)"><i class="fa-solid fa-users"></i></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="card stat-card" onclick="openInsight('sudah_dihitung')" title="Klik untuk detail">
                            <div class="card-body d-flex align-items-center justify-content-between">
                                <div><p class="stat-label">Sudah Dihitung</p><h3 class="stat-value" style="color:var(--success)">${summary.sudah_dihitung || 0}</h3></div>
                                <div class="stat-icon" style="background:linear-gradient(135deg,#059669,#10b981)"><i class="fa-solid fa-check-circle"></i></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="card stat-card" onclick="openInsight('belum_dihitung')" title="Klik untuk detail">
                            <div class="card-body d-flex align-items-center justify-content-between">
                                <div><p class="stat-label">Belum Dihitung</p><h3 class="stat-value" style="color:var(--warning)">${summary.belum_dihitung || 0}</h3></div>
                                <div class="stat-icon" style="background:linear-gradient(135deg,#d97706,#f59e0b)"><i class="fa-solid fa-exclamation-circle"></i></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="card stat-card" onclick="openInsight('basic_salary')" title="Klik untuk detail">
                            <div class="card-body d-flex align-items-center justify-content-between">
                                <div><p class="stat-label">Total Basic Salary</p><h3 class="stat-value" style="color:var(--info)">${formatIDR(summary.total_salary)}</h3></div>
                                <div class="stat-icon" style="background:linear-gradient(135deg,#0ea5e9,#06b6d4)"><i class="fa-solid fa-sack-dollar"></i></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row g-3 mb-4">
                    <div class="col-xl-3 col-md-6">
                        <div class="card stat-card" onclick="openInsight('total_tunjangan')" title="Klik untuk detail">
                            <div class="card-body d-flex align-items-center justify-content-between">
                                <div><p class="stat-label">Total Tunjangan</p><h3 class="stat-value" style="color:var(--info)">${formatIDR(summary.total_tunjangan)}</h3></div>
                                <div class="stat-icon" style="background:linear-gradient(135deg,#0284c7,#38bdf8)"><i class="fa-solid fa-gift"></i></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="card stat-card" onclick="openInsight('bpjs_tk')" title="Klik untuk detail">
                            <div class="card-body d-flex align-items-center justify-content-between">
                                <div><p class="stat-label">Potongan BPJS Tenaga Kerja</p><h3 class="stat-value" style="color:var(--danger)">${formatIDR(summary.total_potongan_bpjs_tk)}</h3></div>
                                <div class="stat-icon" style="background:linear-gradient(135deg,#dc2626,#f87171)"><i class="fa-solid fa-helmet-safety"></i></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="card stat-card" onclick="openInsight('bpjs_kes')" title="Klik untuk detail">
                            <div class="card-body d-flex align-items-center justify-content-between">
                                <div><p class="stat-label">Potongan BPJS Kesehatan</p><h3 class="stat-value" style="color:var(--danger)">${formatIDR(summary.total_potongan_bpjs_kes)}</h3></div>
                                <div class="stat-icon" style="background:linear-gradient(135deg,#e11d48,#fb7185)"><i class="fa-solid fa-heart-pulse"></i></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="card stat-card" onclick="openInsight('pph21')" title="Klik untuk detail">
                            <div class="card-body d-flex align-items-center justify-content-between">
                                <div><p class="stat-label">Potongan PPh 21</p><h3 class="stat-value" style="color:var(--danger)">${formatIDR(summary.total_potongan_pph21)}</h3></div>
                                <div class="stat-icon" style="background:linear-gradient(135deg,#b91c1c,#ef4444)"><i class="fa-solid fa-file-invoice-dollar"></i></div>
                            </div>
                        </div>
                    </div>
                </div>`);
            }

            function renderCharts(charts) {
                $('#chartsArea').html(`
                <div class="row g-4 mb-4">
                    <div class="col-lg-6">
                        <div class="card card-shell chart-clickable" onclick="openInsight('salary_range')">
                            <div class="card-body">
                                <h6 class="fw-bold mb-3" style="font-size:.875rem"><i class="fa-solid fa-chart-pie me-2 text-primary"></i>Distribusi Gaji Bersih <small class="text-muted fw-normal">(klik untuk detail)</small></h6>
                                <div class="chart-wrap"><canvas id="salaryRangeChart"></canvas></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="card card-shell chart-clickable" onclick="openInsight('allowance_divisi')">
                            <div class="card-body">
                                <h6 class="fw-bold mb-3" style="font-size:.875rem"><i class="fa-solid fa-chart-bar me-2 text-success"></i>Tunjangan per Divisi (Top 8) <small class="text-muted fw-normal">(klik untuk detail)</small></h6>
                                <div class="chart-wrap"><canvas id="allowanceChart"></canvas></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row g-4 mb-4">
                    <div class="col-lg-6">
                        <div class="card card-shell chart-clickable" onclick="openInsight('top_deductions')">
                            <div class="card-body">
                                <h6 class="fw-bold mb-3" style="font-size:.875rem"><i class="fa-solid fa-chart-column me-2 text-danger"></i>Potongan Terbanyak (Top 8) <small class="text-muted fw-normal">(klik untuk detail)</small></h6>
                                <div class="chart-wrap"><canvas id="deductionChart"></canvas></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="card card-shell chart-clickable" onclick="openInsight('monthly_trend')">
                            <div class="card-body">
                                <h6 class="fw-bold mb-3" style="font-size:.875rem"><i class="fa-solid fa-chart-line me-2 text-info"></i>Trend Payroll Bulanan (<span id="trendYear">${$('#filterTahun').val()}</span>) <small class="text-muted fw-normal">(klik untuk detail)</small></h6>
                                <div class="chart-wrap" style="height:220px"><canvas id="trendChart"></canvas></div>
                            </div>
                        </div>
                    </div>
                </div>`);

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
                                backgroundColor: ['#4f46e5', '#059669', '#d97706', '#dc2626',
                                    '#9ca3af'
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
                                            size: 11
                                        },
                                        padding: 15,
                                        usePointStyle: true
                                    }
                                }
                            },
                            onClick: (e, els) => {
                                if (els.length) openInsight('salary_range');
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
                                backgroundColor: 'rgba(5, 150, 105, 0.8)',
                                borderRadius: 6
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
                                    ticks: {
                                        callback: v => 'Rp ' + ((v || 0) / 1000000).toFixed(1) + 'J'
                                    }
                                },
                                x: {
                                    grid: {
                                        display: false
                                    }
                                }
                            },
                            onClick: (e, els) => {
                                if (els.length) openInsight('allowance_divisi');
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
                                backgroundColor: 'rgba(220, 38, 38, 0.8)',
                                borderRadius: 6
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
                                    ticks: {
                                        callback: v => 'Rp ' + ((v || 0) / 1000000).toFixed(1) + 'J'
                                    }
                                },
                                y: {
                                    grid: {
                                        display: false
                                    }
                                }
                            },
                            onClick: (e, els) => {
                                if (els.length) openInsight('top_deductions');
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
                                borderColor: '#0284c7',
                                backgroundColor: 'rgba(2, 132, 199, 0.1)',
                                fill: true,
                                tension: 0.4,
                                pointRadius: 4
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
                                    ticks: {
                                        callback: v => 'Rp ' + ((v || 0) / 1000000).toFixed(1) + 'J'
                                    }
                                },
                                x: {
                                    grid: {
                                        display: false
                                    }
                                }
                            },
                            onClick: (e, els) => {
                                if (els.length) openInsight('monthly_trend');
                            }
                        }
                    });
                }
            }

            function renderChartsSafe(charts) {
                const safeCharts = {
                    salary_ranges: charts?.salary_ranges || {
                        labels: [],
                        counts: []
                    },
                    allowance_by_divisi: charts?.allowance_by_divisi || {
                        labels: [],
                        allowance: []
                    },
                    monthly_trend: charts?.monthly_trend || [],
                    top_deductions: charts?.top_deductions || {
                        labels: [],
                        total_values: []
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
                    html = `<tr><td colspan="9" class="text-center text-muted py-5">
                        <i class="fa-solid fa-filter-circle-xmark fa-3x mb-3 d-block" style="color:var(--gray-400)"></i>
                        <div class="fw-semibold">Tidak ada data untuk periode ini</div>
                        <small>Coba ubah filter atau rentang waktu pencarian</small>
                    </td></tr>`;
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

                let html =
                    `<tr><td>Gaji Pokok</td><td>-</td><td>-</td><td class="text-end fw-semibold">${formatIDR(row.gaji_pokok)}</td></tr>`;
                if ((row.tunjangan_jabatan || 0) > 0) {
                    html +=
                        `<tr><td>Tunjangan Jabatan</td><td><small class="text-muted">Tunjangan</small></td><td><small class="text-muted">Dari log gaji</small></td><td class="text-end text-success">${formatIDR(row.tunjangan_jabatan)}</td></tr>`;
                }
                (row.details || []).forEach(d => {
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
            $('#exportExcel').click(function() {
                const month = $('#filterBulan').val(),
                    year = $('#filterTahun').val();
                window.location.href =
                    `{{ route('HR.payroll.export.excel') }}?month=${month}&year=${year}&search=${searchQuery}`;
            });
            $('#exportPdf').click(function() {
                const month = $('#filterBulan').val(),
                    year = $('#filterTahun').val();
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
