@extends('layout_HR.app')

@section('content_HR')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        :root {
            --pri: #4f46e5;
            --pri-light: #eef2ff;
            --success: #059669;
            --success-light: #d1fae5;
            --warning: #d97706;
            --warning-light: #fef3c7;
            --info: #0284c7;
            --info-light: #e0f2fe;
            --danger: #dc2626;
            --danger-light: #fee2e2;
            --purple: #7c3aed;
            --purple-light: #ede9fe;
            --gray-50: #f9fafb;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-400: #9ca3af;
            --gray-500: #6b7280;
            --gray-600: #4b5563;
            --gray-700: #374151;
            --gray-900: #111827;
            --radius: 10px;
            --shadow: 0 1px 3px rgba(0, 0, 0, .06), 0 1px 2px rgba(0, 0, 0, .04);
        }

        body {
            background: #fafbfc;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }

        .page-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--gray-900);
            margin: 0;
        }

        .page-sub {
            color: var(--gray-500);
            font-size: .875rem;
            margin: .15rem 0 0;
        }

        .clock {
            font-variant-numeric: tabular-nums;
            font-weight: 600;
            color: var(--gray-700);
        }

        .panel {
            background: #fff;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            padding: 1.25rem 1.4rem;
            height: 100%;
        }

        .panel-title {
            font-size: .875rem;
            font-weight: 700;
            color: var(--gray-700);
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: .5rem;
            flex-wrap: wrap;
        }

        .panel-title i {
            color: var(--pri);
        }

        .panel-title .right {
            margin-left: auto;
            font-size: .75rem;
            font-weight: 500;
            color: var(--gray-500);
        }

        .filters {
            display: flex;
            gap: .5rem;
            align-items: center;
            flex-wrap: wrap;
        }

        .filters select {
            border: 1px solid var(--gray-200);
            border-radius: 8px;
            padding: .4rem .75rem;
            font-size: .85rem;
            background: #fff;
            color: var(--gray-700);
        }

        .filters select:focus {
            border-color: var(--pri);
            outline: none;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, .12);
        }

        .btn-refresh {
            background: #fff;
            border: 1px solid var(--gray-200);
            border-radius: 8px;
            padding: .4rem .9rem;
            font-size: .85rem;
            color: var(--gray-700);
            font-weight: 600;
            cursor: pointer;
        }

        .btn-refresh:hover {
            border-color: var(--pri);
            color: var(--pri);
        }

        .clickable {
            cursor: pointer;
            transition: box-shadow .15s, background .15s, transform .15s;
        }

        .panel.clickable:hover {
            box-shadow: 0 4px 14px rgba(79, 70, 229, .15);
            transform: translateY(-1px);
        }

        .mini.clickable {
            border-radius: 6px;
            padding: .2rem .3rem;
            margin: -.2rem -.3rem;
        }

        .mini.clickable:hover,
        .bar-row.clickable:hover,
        .chip.clickable:hover,
        .note.clickable:hover {
            background: var(--pri-light);
        }

        .lnk {
            text-decoration: underline dotted;
            cursor: pointer;
        }

        .acts {
            display: inline-flex;
            gap: .3rem;
            align-items: center;
        }

        .btn-detail,
        .btn-exp {
            border: 0;
            border-radius: 6px;
            font-size: .68rem;
            font-weight: 700;
            padding: .18rem .55rem;
            cursor: pointer;
            line-height: 1.3;
        }

        .btn-detail {
            background: var(--pri-light);
            color: var(--pri);
        }

        .btn-detail:hover {
            background: var(--pri);
            color: #fff;
        }

        .btn-exp.pdf {
            background: var(--danger-light);
            color: var(--danger);
        }

        .btn-exp.pdf:hover {
            background: var(--danger);
            color: #fff;
        }

        .btn-exp.xls {
            background: var(--success-light);
            color: var(--success);
        }

        .btn-exp.xls:hover {
            background: var(--success);
            color: #fff;
        }

        .formula-box {
            background: var(--gray-50);
            border-left: 4px solid var(--pri);
            border-radius: 8px;
            padding: .7rem .9rem;
            font-size: .82rem;
            color: var(--gray-700);
            margin-bottom: .8rem;
        }

        .dtl-table {
            font-size: .78rem;
        }

        .dtl-table th {
            background: var(--pri-light);
            position: sticky;
            top: 0;
            white-space: nowrap;
        }

        /* kartu angka utama */
        .stat-label {
            font-size: .8rem;
            color: var(--gray-500);
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: .5rem;
        }

        .stat-label i {
            color: var(--pri);
        }

        .stat-value {
            font-size: 1.9rem;
            font-weight: 700;
            color: var(--gray-900);
            line-height: 1.1;
            margin: .5rem 0 .2rem;
        }

        .stat-value small {
            font-size: .9rem;
            font-weight: 600;
            color: var(--gray-500);
            margin-left: .2rem;
        }

        .stat-note {
            font-size: .78rem;
            color: var(--gray-500);
        }

        .delta {
            display: inline-block;
            font-size: .72rem;
            font-weight: 700;
            padding: .15rem .5rem;
            border-radius: 6px;
        }

        .delta.up {
            background: var(--success-light);
            color: var(--success);
        }

        .delta.down {
            background: var(--danger-light);
            color: var(--danger);
        }

        .delta.flat {
            background: var(--gray-100);
            color: var(--gray-600);
        }

        .minis {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: .5rem;
            margin-top: .9rem;
            padding-top: .9rem;
            border-top: 1px solid var(--gray-200);
        }

        .mini-v {
            font-size: 1.05rem;
            font-weight: 700;
            color: var(--gray-900);
            line-height: 1.1;
        }

        .mini-l {
            font-size: .72rem;
            color: var(--gray-500);
            margin-top: .15rem;
        }

        .chips {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: .6rem;
        }

        .chip {
            border: 1px solid var(--gray-200);
            border-left: 4px solid;
            border-radius: 8px;
            padding: .6rem .8rem;
        }

        .chip-n {
            font-size: 1.4rem;
            font-weight: 700;
            color: var(--gray-900);
            line-height: 1;
        }

        .chip-l {
            font-size: .75rem;
            color: var(--gray-500);
            margin-top: .2rem;
        }

        .sub-title {
            font-size: .8rem;
            font-weight: 700;
            color: var(--gray-600);
            margin-bottom: .5rem;
        }

        .person-list {
            display: flex;
            flex-direction: column;
            gap: .25rem;
            max-height: 260px;
            overflow-y: auto;
        }

        .person {
            display: flex;
            align-items: center;
            gap: .65rem;
            padding: .45rem .3rem;
            border-radius: 8px;
        }

        .person:hover {
            background: var(--gray-50);
        }

        .person.clickable:hover {
            background: var(--pri-light);
        }

        .avatar {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: var(--gray-200);
            color: var(--gray-600);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .75rem;
            font-weight: 700;
            flex-shrink: 0;
            overflow: hidden;
        }

        .avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .p-info {
            flex: 1;
            min-width: 0;
        }

        .p-name {
            font-size: .85rem;
            font-weight: 600;
            color: var(--gray-900);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .p-meta {
            font-size: .72rem;
            color: var(--gray-500);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .tag {
            font-size: .7rem;
            font-weight: 700;
            padding: .15rem .55rem;
            border-radius: 6px;
            flex-shrink: 0;
        }

        .banner {
            background: var(--warning-light);
            color: #92400e;
            font-size: .82rem;
            padding: .55rem .8rem;
            border-radius: 8px;
            margin-bottom: .9rem;
        }

        .note {
            display: flex;
            gap: .75rem;
            align-items: flex-start;
            padding: .75rem .9rem;
            border: 1px solid var(--gray-200);
            border-left: 4px solid;
            border-radius: 8px;
            margin-bottom: .5rem;
            background: #fff;
        }

        .note-icon {
            width: 30px;
            height: 30px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-size: .85rem;
        }

        .note-title {
            font-size: .83rem;
            font-weight: 700;
            color: var(--gray-900);
            display: flex;
            justify-content: space-between;
            gap: .5rem;
        }

        .note-title span:last-child {
            font-weight: 700;
            flex-shrink: 0;
        }

        .note-msg {
            font-size: .78rem;
            color: var(--gray-600);
            line-height: 1.45;
            margin-top: .15rem;
        }

        .event-date {
            font-size: .72rem;
            font-weight: 700;
            white-space: nowrap;
            flex-shrink: 0;
        }

        .bar-row {
            display: flex;
            align-items: center;
            gap: .7rem;
            margin-bottom: .6rem;
            border-radius: 6px;
        }

        .bar-label {
            width: 78px;
            font-size: .8rem;
            font-weight: 600;
            color: var(--gray-700);
            flex-shrink: 0;
        }

        .bar-track {
            flex: 1;
            height: 8px;
            background: var(--gray-100);
            border-radius: 4px;
            overflow: hidden;
        }

        .bar-fill {
            height: 100%;
            border-radius: 4px;
        }

        .bar-count {
            width: 34px;
            text-align: right;
            font-weight: 700;
            font-size: .85rem;
            color: var(--gray-900);
        }

        .divider {
            border-top: 1px solid var(--gray-200);
            margin: .9rem 0;
        }

        .chart-box {
            position: relative;
            height: 240px;
        }

        .chart-box.sm {
            height: 150px;
        }

        .empty {
            text-align: center;
            padding: 1.5rem .5rem;
            color: var(--gray-400);
            font-size: .82rem;
        }

        .empty i {
            display: block;
            font-size: 1.6rem;
            opacity: .4;
            margin-bottom: .4rem;
        }

        .skel {
            background: linear-gradient(90deg, var(--gray-100) 25%, var(--gray-200) 50%, var(--gray-100) 75%);
            background-size: 200% 100%;
            animation: pulse 1.5s infinite;
            border-radius: 8px;
        }

        @keyframes pulse {
            0% {
                background-position: 200% 0;
            }

            100% {
                background-position: -200% 0;
            }
        }

        ::-webkit-scrollbar {
            width: 5px;
            height: 5px;
        }

        ::-webkit-scrollbar-thumb {
            background: #d1d5db;
            border-radius: 3px;
        }

        @media (max-width: 767px) {
            .stat-value {
                font-size: 1.5rem;
            }

            .chips {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        #guideModal {
            --pri: #4f46e5;
            --pri-light: #eef2ff;
            --pri-dark: #3730a3;
            --success: #059669;
            --success-light: #d1fae5;
            --warning: #d97706;
            --warning-light: #fef3c7;
            --danger: #dc2626;
            --danger-light: #fee2e2;
            --info: #0284c7;
            --info-light: #e0f2fe;
            --gray-50: #f9fafb;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-400: #9ca3af;
            --gray-700: #374151;
            --gray-900: #111827;
        }

        #guideModal .modal-content {
            border: none;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, .1), 0 4px 10px rgba(0, 0, 0, .07);
        }

        #guideModal .modal-header-custom {
            background: linear-gradient(135deg, var(--pri) 0%, var(--pri-dark) 100%);
            color: #fff;
            border-radius: 12px 12px 0 0;
            padding: 1.1rem 1.5rem;
        }

        #guideModal .modal-header-custom .modal-title {
            font-weight: 700;
            font-size: 1rem;
        }

        #guideModal .modal-header-custom .btn-close {
            filter: brightness(0) invert(1);
        }

        #guideModal .nav-tabs-custom {
            border-bottom: 2px solid var(--gray-200);
        }

        #guideModal .nav-tabs-custom .nav-link {
            border: none;
            color: var(--gray-400);
            font-weight: 600;
            padding: .85rem 1.25rem;
            font-size: .875rem;
            transition: color .2s;
            display: flex;
            align-items: center;
            gap: .5rem;
            background: transparent;
        }

        #guideModal .nav-tabs-custom .nav-link:hover {
            color: var(--pri);
        }

        #guideModal .nav-tabs-custom .nav-link.active {
            color: var(--pri);
            border-bottom: 3px solid var(--pri);
            background: transparent;
        }

        #guideModal .guide-section {
            margin-bottom: 2rem;
        }

        #guideModal .guide-section h5 {
            font-size: 1rem;
            font-weight: 700;
            color: var(--gray-900);
            border-left: 4px solid var(--pri);
            padding-left: .75rem;
            margin-bottom: .75rem;
        }

        #guideModal .guide-section ol,
        #guideModal .guide-section ul {
            padding-left: 1.25rem;
            margin-bottom: .75rem;
        }

        #guideModal .guide-section li {
            margin-bottom: .4rem;
            font-size: .9rem;
            color: var(--gray-700);
            line-height: 1.55;
        }

        #guideModal .guide-section p {
            font-size: .9rem;
            color: var(--gray-700);
            line-height: 1.55;
        }

        #guideModal .guide-note {
            background: var(--pri-light);
            border-left: 4px solid var(--pri);
            border-radius: 8px;
            padding: .75rem 1rem;
            font-size: .85rem;
            color: var(--gray-700);
        }

        #guideModal .guide-example {
            background: var(--gray-50);
            border: 1px dashed var(--gray-400);
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1rem;
        }

        #guideModal .guide-example ol {
            margin-bottom: 0;
        }

        #guideModal .guide-itsm {
            background: var(--danger-light);
            border: 1px solid #fecaca;
            border-radius: 10px;
            padding: 1.25rem;
        }

        #guideModal .guide-qa {
            margin-bottom: 1.25rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--gray-200);
        }

        #guideModal .guide-qa:last-child {
            border-bottom: none;
        }

        #guideModal .guide-qa strong {
            display: block;
            font-size: .92rem;
            color: var(--gray-900);
            margin-bottom: .4rem;
            font-weight: 600;
        }

        #guideModal .guide-qa span {
            display: block;
            font-size: .88rem;
            color: var(--gray-700);
            line-height: 1.6;
        }

        #guideModal .guide-qa ul,
        #guideModal .guide-qa ol {
            margin-top: .5rem;
            margin-bottom: 0;
        }

        #guideModal .table {
            margin-bottom: 0;
            font-size: .85rem;
        }

        #guideModal .badge {
            font-weight: 600;
            padding: .35em .65em;
        }

        @media (max-width: 768px) {
            #guideModal .modal-dialog {
                margin: 0.5rem;
            }

            #guideModal .nav-tabs-custom .nav-link {
                padding: .65rem .85rem;
                font-size: .8rem;
            }

            #guideModal .guide-section h5 {
                font-size: .95rem;
            }
        }
    </style>

    <div class="container-fluid px-4 py-4">
        <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
            <div>
                <h1 class="page-title"><i class="fa-solid fa-gauge-high me-2" style="color:var(--pri)"></i>Dashboard HR</h1>
                <p class="page-sub"><span id="greetText">-</span> · <span id="todayDate">-</span> · <span class="clock"
                        id="liveClock">--:--:--</span></p>
            </div>
            <div class="filters">
                <select id="filterTahun">
                    @for ($y = date('Y'); $y >= 2023; $y--)
                        <option value="{{ $y }}">{{ $y }}</option>
                    @endfor
                </select>
                <select id="filterPeriode">
                    <option value="all">Setahun penuh</option>
                    <optgroup label="Triwulan">
                        <option value="q1">Triwulan 1 (Jan–Mar)</option>
                        <option value="q2">Triwulan 2 (Apr–Jun)</option>
                        <option value="q3">Triwulan 3 (Jul–Sep)</option>
                        <option value="q4">Triwulan 4 (Okt–Des)</option>
                    </optgroup>
                    <optgroup label="Bulan">
                        <option value="m1">Januari</option>
                        <option value="m2">Februari</option>
                        <option value="m3">Maret</option>
                        <option value="m4">April</option>
                        <option value="m5">Mei</option>
                        <option value="m6">Juni</option>
                        <option value="m7">Juli</option>
                        <option value="m8">Agustus</option>
                        <option value="m9">September</option>
                        <option value="m10">Oktober</option>
                        <option value="m11">November</option>
                        <option value="m12">Desember</option>
                    </optgroup>
                </select>
                <select id="filterDivisi">
                    <option value="all">Semua divisi</option>
                </select>
                <button class="btn-refresh" id="btnRefresh"><i class="fa-solid fa-arrows-rotate me-1"></i>Refresh</button>
                <span style="font-size:.75rem;color:var(--gray-500)">Diperbarui <span id="lastUpdate">-</span></span>
                <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal"
                    data-bs-target="#guideModal">
                    <i class="fa-solid fa-circle-question me-1"></i>Panduan
                </button>
            </div>
        </div>

        <div id="dashboardContent">
            <div class="row g-3">
                @for ($i = 0; $i < 4; $i++)
                    <div class="col-md-6 col-xl-3">
                        <div class="skel" style="height:190px"></div>
                    </div>
                @endfor
            </div>
        </div>
    </div>

    <div class="modal fade" id="detailModal" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="detailTitle">Detail</h5>
                    <div class="ms-auto me-3 d-flex gap-2">
                        <button class="btn btn-sm btn-outline-danger" onclick="exportCurrent('pdf')"><i
                                class="fa-solid fa-file-pdf me-1"></i>PDF</button>
                        <button class="btn btn-sm btn-outline-success" onclick="exportCurrent('excel')"><i
                                class="fa-solid fa-file-excel me-1"></i>Excel</button>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="detailBody"></div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="guideModal" tabindex="-1" aria-labelledby="guideModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header modal-header-custom border-0">
                    <h5 class="modal-title" id="guideModalLabel">
                        <i class="fa-solid fa-book-open me-2"></i>Panduan Dashboard HR Intelligence
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <ul class="nav nav-tabs nav-tabs-custom mb-4">
                        <li class="nav-item">
                            <button class="nav-link active" id="guide-tab1" data-bs-toggle="tab"
                                data-bs-target="#guide-content-1" type="button">
                                <i class="fa-solid fa-list-ol"></i>Cara Penggunaan
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" id="guide-tab2" data-bs-toggle="tab"
                                data-bs-target="#guide-content-2" type="button">
                                <i class="fa-solid fa-circle-info"></i>Penjelasan & Contoh
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" id="guide-tab3" data-bs-toggle="tab"
                                data-bs-target="#guide-content-3" type="button">
                                <i class="fa-solid fa-circle-question"></i>FAQ
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content">
                        <!-- TAB 1: CARA PENGGUNAAN -->
                        <div class="tab-pane fade show active" id="guide-content-1">

                            <div class="guide-section">
                                <h5>1. Mengenali Dashboard</h5>
                                <p>Dashboard ini menampilkan ringkasan lengkap data HR perusahaan: karyawan, kehadiran,
                                    penjualan kelas training, maintenance aset, rekrutmen, dan pengajuan cuti. Semua data
                                    ditampilkan dalam bentuk kartu angka, grafik, dan tabel yang mudah dibaca.</p>
                                <div class="guide-note">
                                    <strong>Tip:</strong> Gunakan filter <strong>Tahun</strong> dan <strong>Divisi</strong>
                                    di pojok kanan atas untuk melihat data sesuai kebutuhan Anda.
                                </div>
                            </div>

                            <div class="guide-section">
                                <h5>2. Filter Data</h5>
                                <ol>
                                    <li><strong>Pilih Tahun:</strong> Klik dropdown tahun di pojok kanan atas untuk melihat
                                        data tahun yang berbeda (tersedia dari 2023 hingga tahun depan).</li>
                                    <li><strong>Pilih Divisi:</strong> Pilih divisi tertentu atau biarkan "Semua divisi"
                                        untuk melihat seluruh perusahaan.</li>
                                    <li><strong>Refresh:</strong> Klik tombol <strong>Refresh</strong> untuk memuat ulang
                                        data terbaru.</li>
                                    <li>Setiap kali Anda mengubah filter, seluruh angka, grafik, dan tabel akan otomatis
                                        menyesuaikan.</li>
                                </ol>
                            </div>

                            <div class="guide-section">
                                <h5>3. Membaca Kartu Statistik Utama</h5>
                                <p>Di bagian atas dashboard ada 4 kartu besar yang menunjukkan:</p>
                                <ul>
                                    <li><strong>Total Karyawan:</strong> Jumlah karyawan aktif, dibagi menjadi Tetap,
                                        Kontrak, dan Probation. Klik angka untuk melihat detail per kategori.</li>
                                    <li><strong>Kehadiran:</strong> Persentase kehadiran bulan ini. Klik untuk melihat
                                        detail kehadiran per orang.</li>
                                    <li><strong>SPJ (Surat Perjalanan Dinas):</strong> Total pengeluaran perjalanan dinas.
                                        Klik untuk melihat rincian per bulan.</li>
                                    <li><strong>Rekrutmen:</strong> Jumlah pelamar yang sedang dalam proses. Klik untuk
                                        melihat tahapan rekrutmen.</li>
                                </ul>
                                <div class="guide-note">
                                    Setiap kartu memiliki tombol <strong>Detail</strong>, <strong>PDF</strong>, dan
                                    <strong>Excel</strong> di bagian bawah untuk mengunduh laporan.
                                </div>
                            </div>

                            <div class="guide-section">
                                <h5>4. Memahami Panel "Ringkasan Hari Ini"</h5>
                                <ol>
                                    <li>Panel ini menunjukkan kondisi hari ini: berapa orang yang hadir tepat waktu,
                                        terlambat, cuti/sakit, dan sedang interview.</li>
                                    <li><strong>Kartu warna-warni</strong> di bagian atas menunjukkan jumlah per status:
                                    </li>
                                    <ul>
                                        <li><span class="badge bg-success">Hadir tepat waktu</span> - Karyawan yang masuk
                                            sesuai jadwal</li>
                                        <li><span class="badge bg-danger">Terlambat</span> - Karyawan yang terlambat (klik
                                            untuk lihat siapa saja)</li>
                                        <li><span class="badge bg-info">Cuti / sakit</span> - Yang mengajukan cuti atau
                                            sakit</li>
                                        <li><span class="badge" style="background:#0d9488;color:#fff">Izin 3 jam</span> -
                                            Izin singkat</li>
                                    </ul>
                                    <li>Klik nama karyawan di daftar untuk melihat detail lebih lanjut.</li>
                                </ol>
                            </div>

                            <div class="guide-section">
                                <h5>5. Grafik dan Chart</h5>
                                <p>Dashboard memiliki beberapa grafik interaktif:</p>
                                <ul>
                                    <li><strong>Tren Kehadiran per Bulan:</strong> Garis hijau menunjukkan persentase
                                        kehadiran, garis merah menunjukkan keterlambatan. Klik titik pada grafik untuk
                                        melihat detail bulan tersebut.</li>
                                    <li><strong>Karyawan per Divisi:</strong> Grafik batang horizontal. Klik batang divisi
                                        untuk melihat daftar karyawan divisi tersebut.</li>
                                    <li><strong>Komposisi Karyawan:</strong> Grafik donat menunjukkan proporsi karyawan
                                        Tetap, Kontrak, dan Probation. Klik potongan grafik untuk filter.</li>
                                    <li><strong>Tahapan Rekrutmen:</strong> Grafik funnel menunjukkan berapa pelamar di
                                        setiap tahap (Melamar → Screening → Interview → Offer → Diterima).</li>
                                </ul>
                            </div>

                            <div class="guide-section">
                                <h5>6. Panel Performa Penjualan Kelas</h5>
                                <ol>
                                    <li>Panel ini menampilkan statistik training:</li>
                                    <ul>
                                        <li><strong>Total Kelas:</strong> Jumlah kelas yang dilaksanakan tahun ini</li>
                                        <li><strong>Total Peserta:</strong> Jumlah seluruh peserta (pax)</li>
                                        <li><strong>Nett Penjualan:</strong> Total pendapatan dari training</li>
                                    </ul>
                                    <li><strong>Top 3 Materi:</strong> Grafik batang menunjukkan 3 materi training paling
                                        populer. Klik tombol "Lihat Semua Materi" untuk detail lengkap.</li>
                                    <li>Klik setiap kartu angka untuk melihat detail per bulan.</li>
                                </ol>
                            </div>

                            <div class="guide-section">
                                <h5>7. Panel Maintenance Aset</h5>
                                <ol>
                                    <li>Menampilkan statistik pemeliharaan aset:</li>
                                    <ul>
                                        <li><strong>Total:</strong> Jumlah maintenance yang dijadwalkan</li>
                                        <li><strong>Sedang Proses:</strong> Yang sedang dikerjakan (On Progress)</li>
                                        <li><strong>Total Biaya:</strong> Total pengeluaran maintenance</li>
                                    </ul>
                                    <li><strong>Top 3 Kategori:</strong> Jenis maintenance yang paling sering dilakukan
                                        (Hardware, Software, AC, dll).</li>
                                    <li>Klik tombol <strong>Kelola & Import</strong> untuk masuk ke halaman maintenance
                                        lengkap (di sana ada fitur import Excel).</li>
                                </ol>
                            </div>

                            <div class="guide-section">
                                <h5>8. Panel Pengajuan Cuti</h5>
                                <ol>
                                    <li>Menampilkan statistik cuti:</li>
                                    <ul>
                                        <li>Total pengajuan cuti tahun ini</li>
                                        <li>Yang disetujui, menunggu, dan ditolak</li>
                                        <li>Persentase karyawan yang sedang cuti</li>
                                    </ul>
                                    <li>Bagian bawah menampilkan <strong>Pengajuan terbaru</strong> dengan nama karyawan,
                                        tipe cuti, dan tanggal.</li>
                                    <li>Klik pengajuan untuk melihat detail lengkap.</li>
                                </ol>
                            </div>

                            <div class="guide-section">
                                <h5>9. Panel Agenda dan Reminder</h5>
                                <ol>
                                    <li>Menampilkan jadwal penting:</li>
                                    <ul>
                                        <li><strong>Interview:</strong> Jadwal interview pelamar (14 hari ke depan)</li>
                                        <li><strong>Penilaian Karyawan:</strong> Pengingat untuk penilaian kinerja
                                            (probation/kontrak akan berakhir)</li>
                                    </ul>
                                    <li>Label warna menunjukkan urgensi:</li>
                                    <ul>
                                        <li><span class="badge bg-danger">Merah</span> - Sudah terlewat</li>
                                        <li><span class="badge bg-warning text-dark">Kuning</span> - Mendesak (≤7 hari)
                                        </li>
                                        <li><span class="badge" style="background:#7c3aed;color:#fff">Ungu</span> - Akan
                                            datang (≤30 hari)</li>
                                    </ul>
                                </ol>
                            </div>

                            <div class="guide-section">
                                <h5>10. Panel Insight (Perlu Perhatian)</h5>
                                <ol>
                                    <li>Panel ini memberikan peringatan otomatis berdasarkan data:</li>
                                    <ul>
                                        <li>Jika retensi karyawan rendah (&lt;60%)</li>
                                        <li>Jika keterlambatan tinggi</li>
                                        <li>Jika ada penilaian yang terlewat</li>
                                        <li>Jika pengeluaran SPJ naik drastis</li>
                                    </ul>
                                    <li>Klik insight untuk melihat detail dan tindakan yang perlu diambil.</li>
                                </ol>
                            </div>

                            <div class="guide-section">
                                <h5>11. Mengunduh Laporan (Export)</h5>
                                <ol>
                                    <li>Setiap panel memiliki tombol <strong>PDF</strong> dan <strong>Excel</strong>.</li>
                                    <li><strong>PDF:</strong> Untuk laporan formal yang siap cetak/dibagikan.</li>
                                    <li><strong>Excel:</strong> Untuk analisis lebih lanjut atau olah data.</li>
                                    <li>Laporan akan mengikuti filter yang aktif (Tahun dan Divisi).</li>
                                    <li>Untuk data Maintenance, ada tombol <strong>Import Excel</strong> di panel atau di
                                        halaman terpisah untuk upload data massal.</li>
                                </ol>
                            </div>

                            <div class="guide-section">
                                <h5>12. Melihat Detail Lengkap</h5>
                                <ol>
                                    <li>Klik tombol <strong>Detail</strong> atau klik angka/kartu untuk membuka jendela
                                        modal.</li>
                                    <li>Modal menampilkan:</li>
                                    <ul>
                                        <li>Rumus perhitungan</li>
                                        <li>Ringkasan angka penting</li>
                                        <li>Tabel data lengkap yang bisa dicari (ketik di kolom "Cari...")</li>
                                        <li>Tombol PDF dan Excel di pojok kanan atas modal</li>
                                    </ul>
                                    <li>Tutup modal dengan klik tombol X atau klik di luar area modal.</li>
                                </ol>
                            </div>
                        </div>

                        <!-- TAB 2: PENJELASAN & CONTOH -->
                        <div class="tab-pane fade" id="guide-content-2">

                            <div class="guide-section">
                                <h5>A. Sumber Data</h5>
                                <p>Data di dashboard berasal dari sistem yang sudah terintegrasi:</p>
                                <ul>
                                    <li><strong>Data Karyawan:</strong> Dari tabel karyawan (status_aktif, tanggal awal
                                        probation/kontrak/tetap)</li>
                                    <li><strong>Kehadiran:</strong> Dari sistem absensi (jam masuk, keterlambatan) dan
                                        pengajuan cuti/sakit/izin</li>
                                    <li><strong>Penjualan Kelas:</strong> Dari tabel RKM (Rencana Kerja Materi) dan
                                        analisisrkm (nett penjualan)</li>
                                    <li><strong>Maintenance:</strong> Dari tabel maintenance (tiket service, biaya, teknisi)
                                    </li>
                                    <li><strong>Rekrutmen:</strong> Dari tabel pelamar (tahap rekrutmen, jadwal interview)
                                    </li>
                                    <li><strong>Cuti:</strong> Dari tabel pengajuancuti (approval manager)</li>
                                    <li><strong>SPJ:</strong> Dari tabel surat_perjalanans (total biaya perjalanan dinas)
                                    </li>
                                </ul>
                                <div class="guide-note">
                                    <strong>Penting:</strong> Dashboard hanya menampilkan dan merangkum data, tidak mengubah
                                    data apa pun di database.
                                </div>
                            </div>

                            <div class="guide-section">
                                <h5>B. Arti Status dan Indikator</h5>
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered align-middle" style="font-size:.85rem">
                                        <thead class="table-light">
                                            <tr>
                                                <th style="width:150px">Indikator</th>
                                                <th>Artinya</th>
                                                <th style="width:200px">Contoh</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td><span class="badge bg-success">Hadir</span></td>
                                                <td>Karyawan masuk tepat waktu sesuai jadwal</td>
                                                <td>Masuk 07:55 (jadwal 08:00)</td>
                                            </tr>
                                            <tr>
                                                <td><span class="badge bg-danger">Telat</span></td>
                                                <td>Masuk melewati batas toleransi</td>
                                                <td>Masuk 08:25 (telat 25 menit)</td>
                                            </tr>
                                            <tr>
                                                <td><span class="badge bg-success">Tetap</span></td>
                                                <td>Karyawan tetap (permanent)</td>
                                                <td>Punya tanggal awal_tetap</td>
                                            </tr>
                                            <tr>
                                                <td><span class="badge bg-info">Kontrak</span></td>
                                                <td>Karyawan dengan perjanjian kontrak</td>
                                                <td>Punya tanggal awal_kontrak</td>
                                            </tr>
                                            <tr>
                                                <td><span class="badge bg-warning text-dark">Probation</span></td>
                                                <td>Karyawan masa percobaan</td>
                                                <td>Baru bergabung, belum tetap</td>
                                            </tr>
                                            <tr>
                                                <td><span class="badge bg-secondary">Pending</span></td>
                                                <td>Menunggu persetujuan atasan</td>
                                                <td>Cuti diajukan, belum disetujui</td>
                                            </tr>
                                            <tr>
                                                <td><span class="delta up">+5.2%</span></td>
                                                <td>Naik dari periode sebelumnya</td>
                                                <td>Kehadiran naik 5.2% dari bulan lalu</td>
                                            </tr>
                                            <tr>
                                                <td><span class="delta down">-3.1%</span></td>
                                                <td>Turun dari periode sebelumnya</td>
                                                <td>Retensi turun 3.1% dari tahun lalu</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="guide-section">
                                <h5>C. Contoh Penggunaan Nyata</h5>

                                <div class="guide-example">
                                    <p class="mb-2"><strong>Kasus 1: HRD ingin mengetahui performa training bulan
                                            ini</strong></p>
                                    <ol class="mb-3">
                                        <li>Pilih <strong>Tahun 2026</strong>, biarkan <strong>Semua divisi</strong>.</li>
                                        <li>Lihat panel <strong>Performa Penjualan Kelas</strong>:</li>
                                        <ul>
                                            <li>Total Kelas: <strong>206 kelas</strong></li>
                                            <li>Total Peserta: <strong>1.151 pax</strong></li>
                                            <li>Nett Penjualan: <strong>Rp 114,2 Juta</strong></li>
                                        </ul>
                                        <li>Lihat <strong>Top 3 Materi</strong>: Certified Ethical Hacker (9 kelas),
                                            Database Admin (8 kelas), Pengelolaan Data (7 kelas).</li>
                                        <li>Klik kartu <strong>Total Kelas</strong> → Muncul detail per bulan: Januari 15
                                            kelas, Februari 18 kelas, dst.</li>
                                        <li>Klik <strong>Excel</strong> untuk download data lengkap.</li>
                                    </ol>

                                    <div class="table-responsive mb-3">
                                        <table class="table table-sm table-bordered bg-white">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Bulan</th>
                                                    <th>Jumlah Kelas</th>
                                                    <th>Total Pax</th>
                                                    <th>Total Nett</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>Januari</td>
                                                    <td>15</td>
                                                    <td>89</td>
                                                    <td>Rp 8.250.000</td>
                                                </tr>
                                                <tr>
                                                    <td>Februari</td>
                                                    <td>18</td>
                                                    <td>102</td>
                                                    <td>Rp 9.180.000</td>
                                                </tr>
                                                <tr>
                                                    <td>Maret</td>
                                                    <td>22</td>
                                                    <td>125</td>
                                                    <td>Rp 11.250.000</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <div class="guide-example">
                                    <p class="mb-2"><strong>Kasus 2: Manager ingin mengecek maintenance aset yang
                                            mendesak</strong></p>
                                    <ol class="mb-3">
                                        <li>Lihat panel <strong>Ringkasan Maintenance Aset</strong>:</li>
                                        <ul>
                                            <li>Total: <strong>45 tiket</strong></li>
                                            <li>Sedang Proses: <strong>12 tiket</strong> (warna merah)</li>
                                            <li>Total Biaya: <strong>Rp 23,5 Juta</strong></li>
                                        </ul>
                                        <li>Lihat <strong>Top 3 Kategori</strong>: Hardware (15 tiket), Software (10 tiket),
                                            AC (8 tiket).</li>
                                        <li>Klik tombol <strong>Kelola & Import</strong> → Masuk ke halaman maintenance.
                                        </li>
                                        <li>Di halaman maintenance, pilih tab <strong>Sedang Dikerjakan</strong> untuk lihat
                                            detail 12 tiket yang on progress.</li>
                                        <li>Filter berdasarkan <strong>Teknisi</strong> untuk tahu siapa yang menangani.
                                        </li>
                                    </ol>
                                </div>

                                <div class="guide-example">
                                    <p class="mb-2"><strong>Kasus 3: HRD perlu menindaklanjuti keterlambatan
                                            tinggi</strong></p>
                                    <ol class="mb-3">
                                        <li>Lihat panel <strong>Perlu Perhatian</strong> (Insights).</li>
                                        <li>Jika muncul peringatan: <span class="badge bg-warning text-dark">Ketepatan
                                                waktu menurun</span> - Ketepatan waktu 78% dengan 45 keterlambatan bulan
                                            ini.</li>
                                        <li>Klik peringatan tersebut → Muncul detail keterlambatan.</li>
                                        <li>Lihat panel <strong>Ringkasan Hari Ini</strong> → Klik kartu
                                            <strong>Terlambat</strong> (warna merah).</li>
                                        <li>Muncul daftar nama yang terlambat hari ini beserta menit keterlambatan.</li>
                                        <li>Klik <strong>Detail</strong> → Lihat tabel lengkap: siapa, kapan, berapa menit,
                                            alasan.</li>
                                        <li>Download <strong>PDF</strong> untuk bahan pemanggilan karyawan.</li>
                                    </ol>
                                </div>

                                <div class="guide-example">
                                    <p class="mb-2"><strong>Kasus 4: Direksi ingin tahu retensi karyawan</strong></p>
                                    <ol class="mb-3">
                                        <li>Lihat kartu <strong>Total Karyawan</strong> → Ada badge <span
                                                class="delta up">Retensi 87.5%</span>.</li>
                                        <li>Klik badge tersebut → Muncul detail retensi:</li>
                                        <ul>
                                            <li>Karyawan aktif: 156 orang</li>
                                            <li>Resign tahun ini: 22 orang</li>
                                            <li>Rumus: 156 / (156 + 22) × 100 = 87.5%</li>
                                        </ul>
                                        <li>Lihat juga <strong>Panel Insight</strong>:</li>
                                        <ul>
                                            <li>Jika retensi &lt;60% → <span class="badge bg-danger">Retensi kritis</span>
                                            </li>
                                            <li>Jika retensi 60-75% → <span class="badge bg-warning text-dark">Perlu
                                                    diwaspadai</span></li>
                                            <li>Jika retensi ≥90% → <span class="badge bg-success">Sangat baik</span></li>
                                        </ul>
                                        <li>Klik <strong>Detail</strong> → Lihat daftar karyawan yang resign beserta
                                            alasannya.</li>
                                    </ol>
                                </div>
                            </div>

                            <div class="guide-section">
                                <h5>D. Interpretasi Grafik</h5>

                                <div class="guide-example">
                                    <p class="mb-2"><strong>Grafik Tren Kehadiran (Line Chart)</strong></p>
                                    <ul>
                                        <li><strong>Garis Hijau:</strong> Persentase kehadiran (Hadir + Telat + Izin) / Hari
                                            Kerja</li>
                                        <li><strong>Garis Merah:</strong> Persentase keterlambatan (Telat / Total Hadir)
                                        </li>
                                        <li><strong>Cara baca:</strong> Jika garis hijau turun di bulan Maret, berarti
                                            kehadiran bulan Maret lebih rendah dari bulan-bulan lain.</li>
                                        <li><strong>Tindakan:</strong> Klik titik Maret → Lihat detail siapa saja yang tidak
                                            hadir di bulan tersebut.</li>
                                    </ul>
                                </div>

                                <div class="guide-example">
                                    <p class="mb-2"><strong>Grafik Komposisi Karyawan (Doughnut Chart)</strong></p>
                                    <ul>
                                        <li><strong>Hijau:</strong> Karyawan Tetap</li>
                                        <li><strong>Biru:</strong> Karyawan Kontrak</li>
                                        <li><strong>Kuning:</strong> Karyawan Probation</li>
                                        <li><strong>Abu-abu:</strong> Belum diisi (data tanggal awal kosong)</li>
                                        <li><strong>Cara baca:</strong> Jika potongan kuning (Probation) terlalu besar
                                            (&gt;25%), artinya banyak karyawan baru bergabung atau banyak yang belum naik
                                            status.</li>
                                    </ul>
                                </div>

                                <div class="guide-example">
                                    <p class="mb-2"><strong>Grafik Funnel Rekrutmen</strong></p>
                                    <ul>
                                        <li>Bentuk corong menunjukkan penyaringan pelamar:</li>
                                        <ul>
                                            <li>Melamar: 150 orang</li>
                                            <li>Screening: 80 orang (yang lolos administrasi)</li>
                                            <li>Interview: 40 orang (yang dipanggil interview)</li>
                                            <li>Offer: 15 orang (yang diberi penawaran)</li>
                                            <li>Diterima: 10 orang (yang menerima offer)</li>
                                        </ul>
                                        <li><strong>Conversion Rate:</strong> 10/150 × 100 = 6.7% (hanya 6.7% pelamar yang
                                            akhirnya diterima)</li>
                                        <li><strong>Interpretasi:</strong> Jika conversion rate terlalu rendah (&lt;3%),
                                            mungkin proses seleksi terlalu ketat atau kriteria tidak sesuai.</li>
                                    </ul>
                                </div>
                            </div>

                            <div class="guide-section">
                                <h5>E. Tips dan Trik</h5>
                                <ul>
                                    <li><strong>Shortcut:</strong> Tekan F5 untuk refresh data cepat.</li>
                                    <li><strong>Search:</strong> Di setiap tabel detail, ada kolom "Cari..." - ketik nama
                                        atau kata kunci untuk filter instan.</li>
                                    <li><strong>Export:</strong> Selalu export ke Excel dulu jika ingin olah data lebih
                                        lanjut, baru ke PDF untuk presentasi.</li>
                                    <li><strong>Bookmark:</strong> Setelah pilih filter (misal: Divisi IT, Tahun 2026),
                                        bookmark halaman untuk akses cepat.</li>
                                    <li><strong>Mobile:</strong> Dashboard responsive - bisa dibuka di HP, tapi untuk export
                                        lebih baik pakai laptop/PC.</li>
                                </ul>
                            </div>
                        </div>

                        <!-- TAB 3: FAQ -->
                        <div class="tab-pane fade" id="guide-content-3">

                            <div class="guide-section">
                                <h5>Pertanyaan Umum (FAQ)</h5>

                                <div class="guide-qa">
                                    <strong>Mengapa angka di dashboard berbeda dengan laporan manual saya?</strong>
                                    <span>Dashboard menghitung data secara real-time dari database. Pastikan:</span>
                                    <ul>
                                        <li>Filter Tahun dan Divisi sudah sesuai</li>
                                        <li>Data di sistem sudah terupdate (misal: absen sudah masuk, cuti sudah disetujui)
                                        </li>
                                        <li>Tidak ada data duplikat atau error di database</li>
                                        <li>Jika masih berbeda, klik tombol Detail untuk lihat perhitungan per item</li>
                                    </ul>
                                </div>

                                <div class="guide-qa">
                                    <strong>Bagaimana cara mengembalikan filter ke kondisi awal?</strong>
                                    <span>Klik tombol <strong>Refresh</strong> di pojok kanan atas. Dashboard akan kembali
                                        ke:</span>
                                    <ul>
                                        <li>Tahun: Tahun berjalan (2026)</li>
                                        <li>Divisi: Semua divisi</li>
                                        <li>Data: Hari ini / bulan ini</li>
                                    </ul>
                                </div>

                                <div class="guide-qa">
                                    <strong>Mengapa grafik tidak muncul atau kosong?</strong>
                                    <span>Beberapa kemungkinan:</span>
                                    <ul>
                                        <li>Belum ada data untuk periode yang dipilih (misal: pilih tahun 2023 tapi data
                                            baru ada dari 2024)</li>
                                        <li>Filter divisi terlalu spesifik sehingga tidak ada data</li>
                                        <li>Koneksi internet lambat - coba refresh halaman</li>
                                        <li>Browser perlu di-clear cache (tekan Ctrl+F5)</li>
                                    </ul>
                                </div>

                                <div class="guide-qa">
                                    <strong>Kenapa ada karyawan dengan status "Belum diisi"?</strong>
                                    <span>Artinya data tanggal awal (probation/kontrak/tetap) belum diisi di database.
                                        Solusi:</span>
                                    <ul>
                                        <li>Masuk ke modul Data Karyawan</li>
                                        <li>Cari karyawan tersebut</li>
                                        <li>Isi field "Tanggal Awal Probation" atau "Tanggal Awal Kontrak"</li>
                                        <li>Simpan, lalu refresh dashboard</li>
                                    </ul>
                                </div>

                                <div class="guide-qa">
                                    <strong>Bagaimana cara import data maintenance dari Excel?</strong>
                                    <span>Ada 2 cara:</span>
                                    <ol>
                                        <li><strong>Dari Dashboard:</strong> Klik panel Maintenance → tombol "Kelola &
                                            Import" → di halaman maintenance klik "Import Excel"</li>
                                        <li><strong>Langsung:</strong> Masuk menu Maintenance → Import Excel</li>
                                    </ol>
                                    <span>Pastikan format Excel sesuai template (download template dulu). Kolom wajib: Nama
                                        Barang, Kategori, Tanggal Mulai, Biaya.</span>
                                </div>

                                <div class="guide-qa">
                                    <strong>Apa arti "Nett Penjualan" di panel training?</strong>
                                    <span>Nett Penjualan adalah pendapatan bersih dari training setelah dikurangi diskon
                                        atau penyesuaian. Rumusnya:</span>
                                    <ul>
                                        <li>Harga Jual × Jumlah Pax = Total Harga Jual (Gross)</li>
                                        <li>Total Harga Jual - Diskon/Potongan = Nett Penjualan</li>
                                        <li>Data ini diambil dari tabel "analisisrkm" yang diisi setelah training selesai
                                        </li>
                                    </ul>
                                </div>

                                <div class="guide-qa">
                                    <strong>Kenapa data cuti tidak muncul?</strong>
                                    <span>Pastikan:</span>
                                    <ul>
                                        <li>Pengajuan cuti sudah disetujui manager (approval_manager = approved)</li>
                                        <li>Tanggal cuti masuk dalam periode yang dipilih (filter tahun)</li>
                                        <li>Karyawan yang mengajukan masih status aktif</li>
                                        <li>Jika cuti masih "Pending", akan muncul di kategori "Menunggu persetujuan"</li>
                                    </ul>
                                </div>

                                <div class="guide-qa">
                                    <strong>Bisa export data dalam format lain selain PDF dan Excel?</strong>
                                    <span>Saat ini hanya tersedia PDF dan Excel. Jika butuh format lain (CSV, Word,
                                        PowerPoint), silakan:</span>
                                    <ul>
                                        <li>Export ke Excel dulu</li>
                                        <li>Buka di Excel/Google Sheets</li>
                                        <li>Save As ke format yang diinginkan</li>
                                        <li>Atau hubungi Tim ITSM untuk request fitur tambahan</li>
                                    </ul>
                                </div>

                                <div class="guide-qa">
                                    <strong>Bagaimana jika ada data yang salah atau error?</strong>
                                    <span>Jangan ubah data langsung di dashboard (tidak bisa). Lakukan:</span>
                                    <ol>
                                        <li>Catat detail error (tanggal, nama, angka yang salah)</li>
                                        <li>Cek sumber data di modul terkait (misal: data karyawan salah → edit di modul
                                            Karyawan)</li>
                                        <li>Setelah diperbaiki, refresh dashboard</li>
                                        <li>Jika error sistem (bukan data), klik tombol "Hubungi Tim ITSM" di bawah</li>
                                    </ol>
                                </div>

                                <div class="guide-qa">
                                    <strong>Apakah dashboard ini update otomatis?</strong>
                                    <span>Tidak real-time. Dashboard update saat:</span>
                                    <ul>
                                        <li>Halaman pertama kali dibuka</li>
                                        <li>Tombol Refresh diklik</li>
                                        <li>Filter diubah (tahun/divisi)</li>
                                        <li>Jika ada data baru masuk (misal: absen baru), klik Refresh untuk melihatnya</li>
                                    </ul>
                                </div>

                                <div class="guide-qa">
                                    <strong>Bisa akses dashboard dari HP?</strong>
                                    <span>Ya, dashboard responsive. Tapi untuk:</span>
                                    <ul>
                                        <li><strong>Melihat data:</strong> Bisa dari HP dengan nyaman</li>
                                        <li><strong>Export/Print:</strong> Lebih baik pakai laptop/PC</li>
                                        <li><strong>Input data:</strong> Harus lewat modul masing-masing (tidak bisa dari
                                            dashboard)</li>
                                    </ul>
                                </div>
                            </div>

                            <div class="guide-section">
                                <h5>Laporan Error & Permintaan Fitur</h5>
                                <div class="guide-itsm">
                                    <p class="mb-2"><strong>Jika Anda menemukan:</strong></p>
                                    <ul class="mb-3">
                                        <li>Error sistem (halaman tidak bisa dibuka, tombol tidak berfungsi)</li>
                                        <li>Data tidak sesuai atau hilang</li>
                                        <li>Ingin tambahan fitur baru</li>
                                        <li>Ingin perubahan tampilan</li>
                                    </ul>

                                    <p class="mb-2"><strong>Silakan hubungi Tim ITSM dengan langkah:</strong></p>
                                    <ol class="mb-3">
                                        <li>Klik tombol <strong>"Hubungi Tim ITSM"</strong> di bawah</li>
                                        <li>Pilih kategori:
                                            <ul>
                                                <li><strong>Laporan Error</strong> - untuk bug/kerusakan</li>
                                                <li><strong>Permintaan Fitur</strong> - untuk fitur baru</li>
                                                <li><strong>Permintaan Data</strong> - untuk report khusus</li>
                                            </ul>
                                        </li>
                                        <li>Tuliskan detail:
                                            <ul>
                                                <li>Judul: "Dashboard HR - [masalah Anda]"</li>
                                                <li>Deskripsi: Jelaskan apa yang terjadi</li>
                                                <li>Langkah: Bagaimana cara reproduce error</li>
                                                <li>Filter: Tahun/divisi apa yang dipakai</li>
                                                <li>Screenshot: Lampirkan gambar jika perlu</li>
                                            </ul>
                                        </li>
                                        <li>Klik Submit, tim ITSM akan merespon dalam 1×24 jam</li>
                                    </ol>

                                    <a href="{{ route('tickets.index') }}" class="btn btn-danger btn-sm" target="_blank">
                                        <i class="fa-solid fa-ticket me-1"></i>Hubungi Tim ITSM
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">
                        <i class="fa-solid fa-xmark me-1"></i>Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let charts = {};
        let detailCtx = {
            type: null,
            filter: '',
            bulan: 0
        };

        const esc = v => $('<div>').text(v ?? '').html().replace(/"/g, '&quot;').replace(/'/g, '&#39;');
        const int = v => Math.round(Number(v) || 0).toLocaleString('id-ID');
        const pct = v => (Math.round((Number(v) || 0) * 10) / 10).toLocaleString('id-ID');

        const filterParams = () => ({
            tahun: $('#filterTahun').val(),
            periode: $('#filterPeriode').val(),
            divisi: $('#filterDivisi').val()
        });

        function refreshPeriodOptions() {
            const thn = +$('#filterTahun').val(), now = new Date();
            const curY = now.getFullYear(), curM = now.getMonth() + 1;
            $('#filterPeriode option').each(function() {
                const v = this.value;
                let startMonth = null;
                if (/^q[1-4]$/.test(v)) startMonth = (+v[1] - 1) * 3 + 1;
                else if (/^m\d+$/.test(v)) startMonth = +v.slice(1);
                this.disabled = startMonth !== null && thn === curY && startMonth > curM;
            });
            if ($('#filterPeriode option:selected').prop('disabled')) $('#filterPeriode').val('all');
        }

        function formatRp(n) {
            n = Number(n) || 0;
            if (n >= 1e9) return 'Rp ' + pct(n / 1e9) + ' M';
            if (n >= 1e6) return 'Rp ' + pct(n / 1e6) + ' Jt';
            if (n >= 1e3) return 'Rp ' + int(n / 1e3) + ' Rb';
            return 'Rp ' + int(n);
        }

        function delta(v, unit) {
            const cls = v > 0 ? 'up' : (v < 0 ? 'down' : 'flat');
            const sign = v > 0 ? '+' : '';
            return `<span class="delta ${cls}">${sign}${pct(v)}${unit}</span>`;
        }

        function avatar(foto, nama) {
            if (foto) return `<img src="/storage/${esc(foto)}" alt="">`;
            return esc((nama || '?').split(' ').slice(0, 2).map(n => n[0] || '').join('').toUpperCase());
        }

        const dattr = (type, filter = '', bulan = 0) =>
            `data-detail="${esc(type)}" data-filter="${esc(filter)}" data-bulan="${Number(bulan) || 0}"`;

        const actions = (type, filter = '', bulan = 0) => {
            const a = `data-type="${esc(type)}" data-filter="${esc(filter)}" data-bulan="${Number(bulan) || 0}"`;
            return `<span class="acts">
                <button class="btn-detail" ${dattr(type, filter, bulan)}><i class="fa-solid fa-list-ul me-1"></i>Detail</button>
                <button class="btn-exp pdf" data-export="pdf" ${a}><i class="fa-solid fa-file-pdf me-1"></i>PDF</button>
                <button class="btn-exp xls" data-export="excel" ${a}><i class="fa-solid fa-file-excel me-1"></i>Excel</button>
            </span>`;
        };

        function exportUrl(c, format) {
            return "{{ route('HR.dashboard.export') }}?" + $.param({
                type: c.type,
                filter: c.filter || '',
                bulan: c.bulan || 0,
                ...filterParams(),
                format: format
            });
        }

        function exportCurrent(format) {
            if (detailCtx.type) window.open(exportUrl(detailCtx, format), '_blank');
        }

        $(document).on('click', '[data-export]', function(e) {
            e.stopPropagation();
            const el = $(this);
            window.open(exportUrl({
                type: el.attr('data-type'),
                filter: el.attr('data-filter') || '',
                bulan: el.attr('data-bulan') || 0
            }, el.attr('data-export')), '_blank');
        });

        $(document).on('click', '[data-detail]', function(e) {
            e.stopPropagation();
            const el = $(this);
            openDetail(el.attr('data-detail'), el.attr('data-filter') || '', el.attr('data-bulan') || 0);
        });

        function openDetail(type, filter = '', bulan = 0) {
            detailCtx = {
                type: type,
                filter: filter || '',
                bulan: Number(bulan) || 0
            };
            $('#detailTitle').text('Memuat...');
            $('#detailBody').html('<div class="skel" style="height:300px"></div>');
            bootstrap.Modal.getOrCreateInstance(document.getElementById('detailModal')).show();

            $.get("{{ route('HR.dashboard.detail') }}", {
                type: type,
                filter: detailCtx.filter,
                bulan: detailCtx.bulan,
                ...filterParams()
            }, res => {
                if (!res.success) {
                    $('#detailBody').html(`<div class="empty">${esc(res.message)}</div>`);
                    return;
                }
                $('#detailTitle').text(res.title);

                const sums = res.summary.map(s => `<div class="chip" style="border-left-color:var(--pri)">
                    <div class="chip-n" style="font-size:1.1rem">${esc(s.value)}</div>
                    <div class="chip-l">${esc(s.label)}${s.note ? ' · ' + esc(s.note) : ''}</div></div>`).join('');

                const head = res.columns.map(c => `<th>${esc(c)}</th>`).join('');
                const body = res.rows.length ?
                    res.rows.map((r, i) => `<tr><td>${i + 1}</td>${r.map(c => `<td>${esc(c)}</td>`).join('')}</tr>`)
                    .join('') :
                    `<tr><td colspan="${res.columns.length + 1}" class="text-center text-muted py-4">Tidak ada data</td></tr>`;

                $('#detailBody').html(
                    `
                    <div class="formula-box"><b>Rumus:</b> ${esc(res.formula)}<br><b>Penjelasan:</b> ${esc(res.explanation)}</div>
                    ${sums ? `<div class="chips mb-3">${sums}</div>` : ''}
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="sub-title mb-0">Rincian data (${int(res.rows.length)} baris)</div>
                        <input type="search" id="detailSearch" class="form-control form-control-sm" style="max-width:240px" placeholder="Cari...">
                    </div>
                    <div style="max-height:380px;overflow:auto"><table class="table table-sm table-hover dtl-table" id="detailTable">
                        <thead><tr><th>No</th>${head}</tr></thead><tbody>${body}</tbody></table></div>`
                );

                $('#detailSearch').on('input', function() {
                    const q = this.value.toLowerCase();
                    $('#detailTable tbody tr').each(function() {
                        $(this).toggle($(this).text().toLowerCase().includes(q));
                    });
                });
            }).fail(() => $('#detailBody').html('<div class="empty">Gagal memuat detail</div>'));
        }

        $(function() {
            const clock = () => $('#liveClock').text(new Date().toLocaleTimeString('id-ID', {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            }));
            clock();
            setInterval(clock, 1000);

            $.get("{{ route('HR.dashboard.divisions') }}", d => d.forEach(x => $('#filterDivisi').append(
                `<option value="${esc(x)}">${esc(x)}</option>`)));
            refreshPeriodOptions();
            loadDashboard();
            $('#btnRefresh').on('click', loadDashboard);
            $('#filterTahun').on('change', function() { refreshPeriodOptions(); loadDashboard(); });
            $('#filterPeriode, #filterDivisi').on('change', loadDashboard);
        });

        function loadDashboard() {
            $('#btnRefresh i').addClass('fa-spin');
                $.get("{{ route('HR.dashboard.data') }}", filterParams(), function(res) {
                    if (!res.success) {
                        alert(res.message);
                        return;
                    }
                    render(res);
                    $('#lastUpdate').text(new Date().toLocaleTimeString('id-ID', {
                        hour: '2-digit',
                        minute: '2-digit'
                    }));
                }).fail(() => alert('Gagal memuat dashboard'))
                .always(() => $('#btnRefresh i').removeClass('fa-spin'));
        }

        function render(d) {
            $('#greetText').text(d.header.greeting.text);
            $('#todayDate').text(d.header.today);

            $('#dashboardContent').html(`
                ${statCards(d)}
                <div class="row g-3 mt-1">
                    <div class="col-lg-8">${todayPanel(d.today)}</div>
                    <div class="col-lg-4">${insightsPanel(d.insights)}</div>
                </div>
                <div class="row g-3 mt-1">
                    <div class="col-lg-7"><div class="panel"><div class="panel-title"><i class="fa-solid fa-chart-line"></i>Tren kehadiran per bulan${actions('tren')}<span class="right">Tahun ${esc($('#filterTahun').val())}</span></div><div class="chart-box"><canvas id="chartTrend"></canvas></div><div class="stat-note mt-2">Klik titik bulan pada grafik untuk melihat rincian per karyawan</div></div></div>
                    <div class="col-lg-5"><div class="panel"><div class="panel-title"><i class="fa-solid fa-building"></i>Karyawan per divisi${actions('divisi')}<span class="right">${d.divisions.total_categories} divisi</span></div><div class="chart-box"><canvas id="chartDivision"></canvas></div><div class="stat-note mt-2">Klik batang divisi untuk melihat daftar karyawannya</div></div></div>
                </div>
                <div class="row g-3 mt-1">
                    <div class="col-lg-4">${compositionPanel(d.composition)}</div>
                    <div class="col-lg-4">${funnelPanel(d.recruitment)}</div>
                    <div class="col-lg-4">${agendaPanel(d.upcoming)}</div>
                </div>
                <div class="row g-3 mt-1"><div class="col-12">${leavePanel(d.leave)}</div></div>
                <div class="row g-3 mt-1">
                    <div class="col-lg-6">${movementPanel('Karyawan baru terbaru', 'fa-user-plus', 'var(--success)', d.recent_hires, false, 'hires')}</div>
                    <div class="col-lg-6">${movementPanel('Karyawan resign terbaru', 'fa-user-minus', 'var(--danger)', d.recent_resigns, true, 'resigns')}</div>
                </div>
                <div class="row g-3 mt-1"><div class="col-12">${olPanel(d.instruktur_ol)}</div></div>
                <div class="row g-3 mt-1">
                    <div class="col-lg-6">${salesPanel(d.sales_overview)}</div>
                    <div class="col-lg-6">${maintenancePanel(d.maintenance_overview)}</div>
                </div>
            `);
            setTimeout(() => initCharts(d), 50);
        }

        function stat(icon, title, value, note, badge, minis, type, bulan) {
            return `
                <div class="col-md-6 col-xl-3"><div class="panel clickable" ${dattr(type, '', bulan || 0)}>
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="stat-label"><i class="fa-solid ${icon}"></i>${title}</div>${badge}
                    </div>
                    <div class="stat-value">${value}</div>
                    <div class="stat-note">${note}</div>
                    <div class="minis">${minis.map(m => `<div class="mini clickable" ${dattr(m[3] || type, m[4] || '', m[5] || 0)}><div class="mini-v" style="color:${m[2] || 'var(--gray-900)'}">${m[0]}</div><div class="mini-l">${m[1]}</div></div>`).join('')}</div>
                    <div class="mt-3">${actions(type, '', bulan || 0)}</div>
                </div></div>`;
        }

        function statCards(d) {
            const c = d.composition, a = d.attendance, f = d.financial, r = d.recruitment;
            return `<div class="row g-3">
                ${stat('fa-users', 'Total karyawan', `${int(c.total)}<small>orang</small>`,
                    `<span class="lnk" style="color:var(--success)" ${dattr('hires')}>${int(c.new_this_year)} baru</span> · <span class="lnk" style="color:var(--danger)" ${dattr('resigns')}>${int(c.resign_this_year)} resign</span> pada periode ini`,
                    `<span class="delta clickable ${c.retention_rate >= 85 ? 'up' : 'flat'}" ${dattr('retensi')}>Retensi ${pct(c.retention_rate)}%</span>`,
                    [[int(c.tetap), 'Tetap', 'var(--success)', 'karyawan', 'Tetap'], [int(c.kontrak), 'Kontrak', 'var(--info)', 'karyawan', 'Kontrak'], [int(c.probation), 'Probation', 'var(--warning)', 'karyawan', 'Probation']],
                    'karyawan')}
                ${stat('fa-user-check', `Kehadiran ${esc(a.month_name)}`, `${pct(a.attendance_rate)}<small>%</small>`,
                    `${int(a.attended)} dari ${int(a.expected)} hari kerja`,
                    delta(a.vs_last_month, ' poin'),
                    [[pct(a.punctuality_rate) + '%', 'Tepat waktu', 'var(--success)', 'ketepatan'], [int(a.telat), 'Telat', 'var(--warning)', 'ketepatan'], [int(a.avg_late_minutes) + ' mnt', 'Rata-rata telat', 'var(--danger)', 'ketepatan']],
                    'kehadiran')}
                ${stat('fa-file-invoice-dollar', `SPJ ${esc(f.label)}`, formatRp(f.total_period),
                    `${int(f.count_period)} transaksi pada periode ini`,
                    delta(f.change, '%'),
                    [[int(f.count_period), 'Jumlah SPJ', null, 'spj'], [formatRp(f.avg_per_spj), 'Rata-rata', null, 'spj'], [formatRp(f.prev_total), 'Periode lalu', null, 'spj']],
                    'spj')}
                ${stat('fa-user-plus', 'Rekrutmen', `${int(r.aktif)}<small>proses</small>`,
                    `<span class="lnk" ${dattr('rekrutmen')}>${int(r.this_period)} pelamar pada periode ini</span>`,
                    delta(r.trend_percent, '%'),
                    [[int(r.total), 'Total', null, 'rekrutmen'], [int(r.hired), 'Diterima', 'var(--success)', 'rekrutmen', 'hired'], [int(r.rejected), 'Ditolak', 'var(--danger)', 'rekrutmen', 'rejected']],
                    'rekrutmen')}
            </div>`;
        }

        function personRow(e, tagHtml, metaText, attr = '') {
            return `<div class="person ${attr ? 'clickable' : ''}" ${attr}><div class="avatar">${avatar(e.foto, e.nama)}</div>
                <div class="p-info"><div class="p-name">${esc(e.nama)}</div><div class="p-meta">${metaText}</div></div>${tagHtml}</div>`;
        }

        const emptyBox = (icon, text) => `<div class="empty"><i class="fa-solid ${icon}"></i>${text}</div>`;

        function todayPanel(t) {
            const banner = t.is_holiday ?
                '<div class="banner">Hari ini libur nasional. Hanya karyawan yang tetap masuk yang tercatat.</div>' :
                (t.is_weekend ?
                    `<div class="banner">Hari ini ${esc(t.day_name)}. Hanya karyawan yang bekerja di akhir pekan yang dihitung.</div>` :
                    '');

            const chips = [
                ['Hadir tepat waktu', t.total_hadir, 'var(--success)', 'hadir'],
                ['Terlambat', t.total_telat, 'var(--danger)', 'telat'],
                ['Cuti / sakit', t.total_cuti, 'var(--info)', 'cuti_sakit'],
                ['Izin 3 jam', t.total_izin, '#0d9488', 'izin'],
                ['Menunggu persetujuan', t.total_pending, '#f97316', 'pending'],
                ['Tanpa keterangan', t.total_tidak_hadir, '#374151', 'tidak_hadir'],
            ].map(c =>
                `<div class="chip clickable" style="border-left-color:${c[2]}" ${dattr('hari_ini', c[3])}><div class="chip-n">${int(c[1])}</div><div class="chip-l">${c[0]}</div></div>`
            ).join('');

            const telat = t.telat_list.length ? t.telat_list.map(e =>
                    personRow(e,
                        `<span class="tag" style="background:var(--danger-light);color:var(--danger)">+${int(e.late_minutes)} mnt</span>`,
                        `${esc(e.jabatan)} · masuk ${esc(e.jam_masuk || '-')}`, dattr('hari_ini', 'telat'))).join('') :
                emptyBox('fa-circle-check', 'Tidak ada keterlambatan');

            const cuti = t.cuti_list.length ? t.cuti_list.map(e =>
                    personRow(e,
                        `<span class="tag" style="background:var(--info-light);color:var(--info)">${esc(e.tipe)}</span>`,
                        esc(e.jabatan), dattr('hari_ini', 'cuti_sakit'))).join('') :
                emptyBox('fa-calendar', 'Tidak ada cuti atau sakit');

            const interview = t.interview_list.length ? t.interview_list.map(i =>
                `<div class="person clickable" ${dattr('rekrutmen', 'interview')}><div class="avatar"><i class="fa-solid fa-user-tie"></i></div>
                <div class="p-info"><div class="p-name">${esc(i.nama)}</div><div class="p-meta">${esc(i.jabatan)} · ${esc(i.metode)}</div></div>
                <span class="tag" style="background:var(--purple-light);color:var(--purple)">${esc(i.waktu)}</span></div>`
            ).join('') : emptyBox('fa-calendar-check', 'Tidak ada interview');

            return `<div class="panel">
                <div class="panel-title"><i class="fa-solid fa-calendar-day"></i>${t.is_today ? 'Ringkasan hari ini' : 'Ringkasan akhir periode'}${actions('hari_ini')}<span class="right">${esc(t.day_name)}, ${esc(t.date)}</span></div>
                ${banner}
                <div class="chips">${chips}</div>
                <div class="divider"></div>
                <div class="row g-3">
                    <div class="col-md-4"><div class="sub-title">Terlambat</div><div class="person-list">${telat}</div></div>
                    <div class="col-md-4"><div class="sub-title">Cuti dan sakit</div><div class="person-list">${cuti}</div></div>
                    <div class="col-md-4"><div class="sub-title">Interview</div><div class="person-list">${interview}</div></div>
                </div></div>`;
        }

        function insightsPanel(list) {
            const map = {
                danger: ['var(--danger)', 'var(--danger-light)'],
                warning: ['var(--warning)', 'var(--warning-light)'],
                info: ['var(--info)', 'var(--info-light)'],
                success: ['var(--success)', 'var(--success-light)'],
                neutral: ['var(--gray-500)', 'var(--gray-100)'],
            };
            const body = list.length ? list.map(i => {
                const [c, bg] = map[i.type] || map.neutral;
                const attr = i.detail ? dattr(i.detail[0], i.detail[1]) : '';
                return `<div class="note ${attr ? 'clickable' : ''}" style="border-left-color:${c}" ${attr}>
                    <div class="note-icon" style="background:${bg};color:${c}"><i class="fa-solid ${esc(i.icon)}"></i></div>
                    <div style="min-width:0"><div class="note-title"><span>${esc(i.title)}</span><span style="color:${c}">${esc(i.metric)}</span></div>
                    <div class="note-msg">${esc(i.message)}</div></div></div>`;
            }).join('') : emptyBox('fa-circle-check', 'Semua metrik dalam kondisi baik');

            return `<div class="panel"><div class="panel-title"><i class="fa-solid fa-lightbulb"></i>Perlu perhatian<span class="right">Klik untuk detail</span></div>${body}</div>`;
        }

        function compositionPanel(c) {
            const total = c.total || 1;
            const rows = c.composition_chart.labels.map((l, i) => {
                const n = c.composition_chart.data[i],
                    color = c.composition_chart.colors[i];
                return `<div class="bar-row clickable" ${dattr('karyawan', l)}><div class="bar-label">${esc(l)}</div>
                    <div class="bar-track"><div class="bar-fill" style="width:${Math.round(n / total * 100)}%;background:${color}"></div></div>
                    <div class="bar-count">${int(n)}</div></div>`;
            }).join('');

            return `<div class="panel"><div class="panel-title"><i class="fa-solid fa-chart-pie"></i>Komposisi karyawan${actions('komposisi')}</div>
                <div class="chart-box sm mb-3"><canvas id="chartComposition"></canvas></div>
                ${rows}
                <div class="divider"></div>
                <div class="d-flex justify-content-between">
                    <div class="clickable" ${dattr('karyawan')}><div class="mini-l">Rata-rata masa kerja</div><div class="mini-v">${esc(c.avg_tenure_label)}</div></div>
                    <div class="text-end clickable" ${dattr('retensi')}><div class="mini-l">Retensi periode ini</div><div class="mini-v" style="color:var(--success)">${pct(c.retention_rate)}%</div></div>
                </div></div>`;
        }

        function funnelPanel(r) {
            const rows = r.stages.map(s => `<div class="bar-row clickable" ${dattr('rekrutmen', s.stage)}><div class="bar-label" style="width:84px">${esc(s.label)}</div>
                <div class="bar-track"><div class="bar-fill" style="width:${Math.min(s.percentage, 100)}%;background:${s.color}"></div></div>
                <div class="bar-count">${int(s.count)}</div></div>`).join('');

            return `<div class="panel"><div class="panel-title"><i class="fa-solid fa-filter"></i>Tahapan rekrutmen${actions('rekrutmen')}<span class="right">Diterima ${pct(r.conversion_rate)}%</span></div>
                ${r.total ? rows : emptyBox('fa-inbox', 'Belum ada pelamar')}
                <div class="divider"></div>
                <div class="stat-note">Persentase tiap tahap dihitung dari seluruh pelamar aktif (${int(r.total)}), termasuk yang ditolak.</div></div>`;
        }

        function agendaPanel(events) {
            const body = events.length ? events.map(e =>
                    `<div class="note clickable" style="border-left-color:${e.color}" ${dattr('agenda')}>
                    <div class="note-icon" style="background:${e.color}1a;color:${e.color}"><i class="fa-solid ${esc(e.icon)}"></i></div>
                    <div style="min-width:0;flex:1"><div class="note-title" style="display:block"><span>${esc(e.title)}</span></div><div class="note-msg">${esc(e.subtitle)}</div></div>
                    <div class="event-date" style="color:${e.color}">${esc(e.datetime)}</div></div>`).join('') :
                emptyBox('fa-calendar', 'Tidak ada agenda atau reminder');

            return `<div class="panel"><div class="panel-title"><i class="fa-solid fa-calendar-week"></i>Agenda dan reminder${actions('agenda')}</div>
                <div class="person-list" style="max-height:330px">${body}</div></div>`;
        }

        function leavePanel(l) {
            const chips = [
                ['Total pengajuan', l.total, 'var(--pri)', ''],
                ['Disetujui', l.disetujui, 'var(--success)', 'Disetujui'],
                ['Menunggu', l.menunggu, 'var(--warning)', 'Menunggu'],
                ['Ditolak', l.ditolak, 'var(--danger)', 'Ditolak'],
                ['Karyawan yang cuti', pct(l.rate_pegawai) + '%', 'var(--info)', 'Disetujui'],
                ['Hari cuti disetujui', l.hari_cuti, '#0d9488', 'Disetujui'],
            ].map(c =>
                `<div class="chip clickable" style="border-left-color:${c[2]}" ${dattr('cuti', c[3])}><div class="chip-n">${typeof c[1] === 'number' ? int(c[1]) : c[1]}</div><div class="chip-l">${c[0]}</div></div>`
            ).join('');

            const recent = l.recent.length ? l.recent.map(r => `<div class="person clickable" ${dattr('cuti', r[8])}><div class="avatar">${avatar(null, r[0])}</div>
                <div class="p-info"><div class="p-name">${esc(r[0])}</div><div class="p-meta">${esc(r[3])} · ${esc(r[4])} s/d ${esc(r[5])} · ${esc(r[6])}</div></div>
                <span class="tag" style="background:var(--info-light);color:var(--info)">${esc(r[8])}</span></div>`)
                .join('') :
                emptyBox('fa-calendar', 'Belum ada pengajuan cuti');

            return `<div class="panel"><div class="panel-title"><i class="fa-solid fa-umbrella-beach"></i>Pengajuan cuti${actions('cuti')}
                <span class="right">${int(l.pegawai_cuti)} dari ${int(l.aktif)} karyawan aktif = ${pct(l.rate_pegawai)}% · persetujuan ${pct(l.rate_approval)}%</span></div>
                <div class="chips">${chips}</div><div class="divider"></div>
                <div class="sub-title">Pengajuan terbaru</div><div class="person-list">${recent}</div></div>`;
        }

        function olPanel(ol) {
            const maxCore = ol.top_cores.length > 0 ? Math.max(...ol.top_cores.map(c => c.value)) : 1;
            const coreBars = ol.top_cores.map(core => {
                const pct = Math.round((core.value / maxCore) * 100);
                return `<div class="bar-row clickable" ${dattr('instruktur_ol', 'core:' + core.label)}>
                    <div class="bar-label" style="width:100px" title="${esc(core.label)}">${esc(core.label.length > 12 ? core.label.substring(0,12)+'...' : core.label)}</div>
                    <div class="bar-track"><div class="bar-fill" style="width:${pct}%;background:var(--purple)"></div></div>
                    <div class="bar-count">${int(core.value)}</div>
                </div>`;
            }).join('') || '<div class="empty">Belum ada data core</div>';

            return `<div class="panel">
                <div class="panel-title">
                    <i class="fa-solid fa-chalkboard-user" style="color:var(--purple)"></i>
                    Ringkasan Instruktur OL
                    ${actions('instruktur_ol')}
                    <span class="right" title="Data instruktur OL tidak punya tanggal periode">Snapshot saat ini</span>
                </div>
                
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="text-center p-3" style="background:var(--purple-light);border-radius:8px">
                            <div class="stat-label" style="justify-content:center"><i class="fa-solid fa-users"></i>Total Instruktur</div>
                            <div class="stat-value" style="color:var(--purple)">${int(ol.total)}<small>orang</small></div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-center p-3" style="background:var(--info-light);border-radius:8px">
                            <div class="stat-label" style="justify-content:center"><i class="fa-solid fa-file-check"></i>Kelengkapan Berkas</div>
                            <div class="stat-value" style="color:var(--info)">${pct(ol.doc_completion_rate)}<small>%</small></div>
                            <div class="stat-note">Berdasarkan CV & KTP</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="chart-box sm" style="height:120px"><canvas id="chartOlLsp"></canvas></div>
                    </div>
                </div>

                <div class="divider"></div>
                <div class="sub-title">Top 5 Keahlian (Core) Instruktur</div>
                ${coreBars}
                <div class="stat-note mt-2">Klik batang "Core" atau potongan chart untuk melihat detail instruktur terkait.</div>
            </div>`;
        }

        function salesPanel(sales) {
            const topMateri = sales.topMateri || [];
            const maxMateri = topMateri.length > 0 ? Math.max(...topMateri.map(m => m.value)) : 1;

            const materiBars = topMateri.map(m => {
                const pct = Math.round((m.value / maxMateri) * 100);
                return `<div class="bar-row clickable" ${dattr('penjualan_materi', 'materi:' + m.label)}>
                    <div class="bar-label" style="width:110px" title="${esc(m.label)}">${esc(m.label.length > 14 ? m.label.substring(0,14)+'...' : m.label)}</div>
                    <div class="bar-track"><div class="bar-fill" style="width:${pct}%;background:var(--success)"></div></div>
                    <div class="bar-count">${int(m.value)}</div>
                </div>`;
            }).join('') || '<div class="empty">Belum ada data materi</div>';

            const manageUrl = "{{ route('HR.rekap-penjualan.index') ?? '#' }}";

            return `<div class="panel">
                <div class="panel-title">
                    <i class="fa-solid fa-chart-line" style="color:var(--success)"></i>Performa Penjualan Kelas
                    <div class="right" style="display:flex; gap:.5rem; align-items:center;">
                        <a href="${manageUrl}" class="btn btn-sm btn-outline-success" style="font-size:.7rem; padding:.2rem .5rem; border-radius:6px;"><i class="fa-solid fa-external-link-alt me-1"></i>Kelola</a>
                    </div>
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-4 text-center p-2 clickable" style="background:var(--success-light);border-radius:8px" ${dattr('penjualan_kelas')}>
                        <div class="stat-label" style="justify-content:center;font-size:.75rem">Total Kelas</div>
                        <div class="stat-value" style="font-size:1.4rem;color:var(--success)">${int(sales.totalKelas)}</div>
                        <div style="font-size:.7rem;color:var(--gray-500);margin-top:4px">Klik untuk detail</div>
                    </div>
                    <div class="col-4 text-center p-2 clickable" style="background:var(--info-light);border-radius:8px" ${dattr('penjualan_peserta')}>
                        <div class="stat-label" style="justify-content:center;font-size:.75rem">Total Peserta</div>
                        <div class="stat-value" style="font-size:1.4rem;color:var(--info)">${int(sales.totalPax)}</div>
                        <div style="font-size:.7rem;color:var(--gray-500);margin-top:4px">Klik untuk detail</div>
                    </div>
                    <div class="col-4 text-center p-2 clickable" style="background:var(--purple-light);border-radius:8px" ${dattr('penjualan_nett')}>
                        <div class="stat-label" style="justify-content:center;font-size:.75rem">Nett Penjualan</div>
                        <div class="stat-value" style="font-size:1.1rem;color:var(--purple)">${formatRp(sales.totalNett)}</div>
                        <div style="font-size:.7rem;color:var(--gray-500);margin-top:4px">Klik untuk detail</div>
                    </div>
                </div>
                <div class="divider"></div>
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:.5rem">
                    <div class="sub-title mb-0">Top 3 Materi Terpopuler (Jumlah Kelas)</div>
                    <button class="btn btn-sm btn-outline-primary" ${dattr('penjualan_materi')}><i class="fa-solid fa-list me-1"></i>Lihat Semua Materi</button>
                </div>
                ${materiBars}
            </div>`;
        }

        function maintenancePanel(mnt) {
            const maxKat = mnt.top_kategori.length > 0 ? Math.max(...mnt.top_kategori.map(k => k.value)) : 1;
            const katBars = mnt.top_kategori.map(k => {
                const pct = Math.round((k.value / maxKat) * 100);
                return `<div class="bar-row clickable" ${dattr('maintenance', 'kategori:' + k.label)}>
                    <div class="bar-label" style="width:110px" title="${esc(k.label)}">${esc(k.label.length > 14 ? k.label.substring(0,14)+'...' : k.label)}</div>
                    <div class="bar-track"><div class="bar-fill" style="width:${pct}%;background:var(--warning)"></div></div>
                    <div class="bar-count">${int(k.value)}</div>
                </div>`;
            }).join('') || '<div class="empty">Belum ada data kategori</div>';

            // Ganti URL route di href berikut sesuai nama route Anda yang sebenarnya
            const manageUrl = "{{ route('HR.maintenance.index') ?? '#' }}";

            return `<div class="panel">
                <div class="panel-title">
                    <i class="fa-solid fa-screwdriver-wrench" style="color:var(--warning)"></i>Ringkasan Maintenance
                    <div class="right" style="display:flex; gap:.5rem; align-items:center;">
                        <a href="${manageUrl}" class="btn btn-sm btn-outline-warning" style="font-size:.7rem; padding:.2rem .5rem; border-radius:6px;"><i class="fa-solid fa-external-link-alt me-1"></i>Kelola & Import</a>
                        ${actions('maintenance')}
                    </div>
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-4 text-center p-2" style="background:var(--warning-light);border-radius:8px">
                        <div class="stat-label" style="justify-content:center;font-size:.75rem">Total Tiket</div>
                        <div class="stat-value" style="font-size:1.4rem;color:var(--warning)">${int(mnt.total_tiket)}</div>
                    </div>
                    <div class="col-4 text-center p-2" style="background:var(--danger-light);border-radius:8px">
                        <div class="stat-label" style="justify-content:center;font-size:.75rem">Sedang Proses</div>
                        <div class="stat-value" style="font-size:1.4rem;color:var(--danger)">${int(mnt.on_progress)}</div>
                    </div>
                    <div class="col-4 text-center p-2" style="background:var(--gray-100);border-radius:8px">
                        <div class="stat-label" style="justify-content:center;font-size:.75rem">Total Biaya</div>
                        <div class="stat-value" style="font-size:1.1rem;color:var(--gray-700)">${formatRp(mnt.total_biaya)}</div>
                    </div>
                </div>
                <div class="divider"></div>
                <div class="sub-title">Top 3 Jenis Maintenance (Frekuensi)</div>
                ${katBars}
            </div>`;
        }

        function movementPanel(title, icon, color, list, isResign, type) {
            const body = list.length ? list.map(e => {
                const meta = isResign ?
                    `${esc(e.jabatan)} · ${esc(e.alasan !== '-' ? e.alasan.substring(0, 30) : 'Tanpa alasan')}` :
                    `${esc(e.jabatan)} · ${esc(e.divisi)}`;
                const right =
                    `<div class="text-end"><div style="font-size:.75rem;font-weight:700;color:${color}">${esc(e.tanggal)}</div><div class="p-meta">${int(e.days_ago)} hari lalu</div></div>`;
                return personRow(e, right, meta, dattr(type));
            }).join('') : emptyBox(icon, 'Belum ada data');

            return `<div class="panel"><div class="panel-title"><i class="fa-solid ${icon}" style="color:${color}"></i>${title}${actions(type)}</div><div class="person-list">${body}</div></div>`;
        }

        function initCharts(d) {
            Object.values(charts).forEach(c => c?.destroy());
            charts = {};
            const font = {
                family: "-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto",
                size: 11
            };
            const grid = {
                color: 'rgba(0,0,0,0.05)'
            };
            const hover = (evt, els) => {
                evt.native.target.style.cursor = els.length ? 'pointer' : 'default';
            };

            const comp = document.getElementById('chartComposition');
            if (comp) {
                const cc = d.composition.composition_chart;
                const has = cc.data.some(v => v > 0);
                charts.comp = new Chart(comp, {
                    type: 'doughnut',
                    data: {
                        labels: has ? cc.labels : ['Belum ada data'],
                        datasets: [{
                            data: has ? cc.data : [1],
                            backgroundColor: has ? cc.colors : ['#e5e7eb'],
                            borderWidth: 3,
                            borderColor: '#fff'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '68%',
                        onHover: hover,
                        onClick: (e, els) => {
                            openDetail('karyawan', has && els.length ? cc.labels[els[0].index] : '');
                        },
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                enabled: has
                            }
                        }
                    }
                });
            }

            const trend = document.getElementById('chartTrend');
            if (trend) {
                charts.trend = new Chart(trend, {
                    type: 'line',
                    data: {
                        labels: d.attendance_trend.map(t => t.month),
                        datasets: [{
                                label: 'Kehadiran (%)',
                                data: d.attendance_trend.map(t => t.rate),
                                borderColor: '#059669',
                                backgroundColor: '#05966914',
                                fill: true,
                                tension: .35,
                                borderWidth: 2.5,
                                pointRadius: 3
                            },
                            {
                                label: 'Keterlambatan (%)',
                                data: d.attendance_trend.map(t => t.late_rate),
                                borderColor: '#dc2626',
                                tension: .35,
                                borderWidth: 2.5,
                                pointRadius: 3
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: {
                            mode: 'index',
                            intersect: false
                        },
                        onHover: hover,
                        onClick: (e, els) => {
                            openDetail(els.length ? 'kehadiran' : 'tren', '', els.length ? els[0].index + 1 :
                                0);
                        },
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    font,
                                    usePointStyle: true,
                                    padding: 15
                                }
                            },
                            tooltip: {
                                callbacks: {
                                    label: ctx => `${ctx.dataset.label}: ${pct(ctx.parsed.y)}%`
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                max: 100,
                                grid,
                                ticks: {
                                    font,
                                    callback: v => v + '%'
                                }
                            },
                            x: {
                                grid: {
                                    display: false
                                },
                                ticks: {
                                    font
                                }
                            }
                        }
                    }
                });
            }

            const div = document.getElementById('chartDivision');
            if (div) {
                charts.div = new Chart(div, {
                    type: 'bar',
                    data: {
                        labels: d.divisions.labels,
                        datasets: [{
                            data: d.divisions.data,
                            backgroundColor: '#4f46e5',
                            borderRadius: 4
                        }]
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        maintainAspectRatio: false,
                        onHover: hover,
                        onClick: (e, els) => {
                            if (!els.length) {
                                openDetail('divisi');
                                return;
                            }
                            const label = d.divisions.labels[els[0].index];
                            openDetail('karyawan', 'divisi:' + (label === 'Tanpa divisi' ? '' : label));
                        },
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                callbacks: {
                                    label: ctx => `${int(ctx.parsed.x)} orang`
                                }
                            }
                        },
                        scales: {
                            x: {
                                beginAtZero: true,
                                grid,
                                ticks: {
                                    font,
                                    precision: 0
                                }
                            },
                            y: {
                                grid: {
                                    display: false
                                },
                                ticks: {
                                    font
                                }
                            }
                        }
                    }
                });
            }

            const olLsp = document.getElementById('chartOlLsp');
            if (olLsp && d.instruktur_ol) {
                const olData = d.instruktur_ol.lsp_distribution;
                const hasData = olData.length > 0 && olData.some(v => v.value > 0);

                charts.olLsp = new Chart(olLsp, {
                    type: 'doughnut',
                    data: {
                        labels: hasData ? olData.map(d => d.label) : ['Belum ada data'],
                        datasets: [{
                            data: hasData ? olData.map(d => d.value) : [1],
                            backgroundColor: hasData ? ['#4f46e5', '#0284c7', '#059669', '#d97706',
                                '#7c3aed', '#ec4899'
                            ] : ['#e5e7eb'],
                            borderWidth: 2,
                            borderColor: '#fff'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '65%',
                        plugins: {
                            legend: {
                                position: 'right',
                                labels: {
                                    font: {
                                        family: "-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto",
                                        size: 10
                                    },
                                    boxWidth: 10,
                                    padding: 8
                                }
                            },
                            tooltip: {
                                enabled: hasData,
                                callbacks: {
                                    label: ctx => ` ${ctx.label}: ${int(ctx.parsed)} afiliasi`
                                }
                            }
                        },
                        onClick: (e, els) => {
                            if (hasData && els.length) {
                                const selectedLabel = olData[els[0].index].label;
                                openDetail('instruktur_ol', 'lsp:' + selectedLabel);
                            }
                        }
                    }
                });
            }
        }
    </script>
@endsection
