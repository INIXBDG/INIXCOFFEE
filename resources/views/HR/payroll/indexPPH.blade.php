@extends('layout_HR.app')
@section('content_HR')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">

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
            --shadow: 0 4px 6px rgba(0, 0, 0, .07), 0 2px 4px rgba(0, 0, 0, .05);
            --shadow-lg: 0 10px 25px rgba(0, 0, 0, .1);
        }

        body {
            background: #fafbfc;
        }

        .card-shell {
            border: none;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            background: #fff;
        }

        .stat-card {
            border: none;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            background: #fff;
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
            flex-shrink: 0;
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

        .btn-pri:disabled,
        .btn-pri[disabled] {
            opacity: .5;
            transform: none;
            box-shadow: none;
        }

        .btn-outline-sec {
            background: #fff;
            border: 1px solid var(--gray-200);
            color: var(--gray-600);
            font-weight: 500;
            border-radius: 8px;
            transition: all .2s;
        }

        .btn-outline-sec:hover {
            background: var(--gray-50);
            border-color: var(--gray-400);
            color: var(--gray-900);
        }

        .dataTables_wrapper {
            padding: 0 !important;
        }

        .dataTables_wrapper .dataTables_info {
            padding: 1rem 1.5rem !important;
            font-size: .82rem;
            color: var(--gray-400);
            margin: 0 !important;
            border-top: 1px solid var(--gray-100);
        }

        .dataTables_wrapper .dataTables_paginate {
            padding: 1rem 1.5rem !important;
            margin: 0 !important;
            border-top: 1px solid var(--gray-100);
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button {
            padding: 0 !important;
            margin: 0 2px !important;
            border-radius: 6px !important;
            transition: all .2s !important;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.current,
        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background: var(--pri) !important;
            color: #fff !important;
            border: 1px solid var(--pri) !important;
            font-weight: 600;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.disabled {
            color: var(--gray-400) !important;
            background: transparent !important;
            border: 1px solid var(--gray-200) !important;
            opacity: .6;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button:not(.current):not(.disabled) {
            background: #fff !important;
            color: var(--gray-600) !important;
            border: 1px solid var(--gray-200) !important;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button:not(.current):not(.disabled):hover {
            background: var(--pri-light) !important;
            color: var(--pri) !important;
            border-color: var(--pri) !important;
        }

        #pphTable {
            border-collapse: separate;
            border-spacing: 0;
            width: 100%;
        }

        #pphTable thead th {
            font-size: .72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .5px;
            color: var(--gray-600);
            background: var(--gray-50);
            border-bottom: 2px solid var(--gray-200) !important;
            border-top: none !important;
            padding: .85rem 1rem;
        }

        #pphTable tbody tr {
            transition: background .15s;
        }

        #pphTable tbody tr:hover {
            background: var(--pri-light) !important;
        }

        #pphTable tbody td {
            vertical-align: middle;
            font-size: .875rem;
            border-bottom: 1px solid var(--gray-100) !important;
            border-top: none !important;
            padding: .85rem 1rem;
            color: var(--gray-700);
        }

        .info-card {
            background: var(--gray-50);
            border: 1px solid var(--gray-200);
            border-radius: 8px;
            padding: .85rem 1rem;
        }

        .info-card .label {
            font-size: .7rem;
            text-transform: uppercase;
            letter-spacing: .5px;
            color: var(--gray-400);
            font-weight: 700;
        }

        .info-card .value {
            font-weight: 600;
            color: var(--gray-900);
        }

        .status-badge {
            padding: .35rem .75rem;
            border-radius: 20px;
            font-size: .7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .5px;
            white-space: nowrap;
        }

        .badge-ptkp {
            background: var(--pri-light);
            color: var(--pri);
        }

        .badge-menikah {
            background: var(--success-light);
            color: var(--success);
        }

        .badge-belum {
            background: var(--warning-light);
            color: var(--warning);
        }

        .badge-tinggi {
            background: var(--danger-light);
            color: var(--danger);
        }

        .badge-sedang {
            background: var(--warning-light);
            color: var(--warning);
        }

        .badge-rendah {
            background: var(--info-light);
            color: var(--info);
        }

        .child-item {
            background: var(--gray-50);
            border: 1px solid var(--gray-200);
            border-radius: 8px;
            padding: .75rem 1rem;
            display: flex;
            align-items: center;
            gap: .75rem;
        }

        .child-number {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: var(--pri-light);
            color: var(--pri);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: .8rem;
            flex-shrink: 0;
        }

        .child-tanggungan {
            font-size: .7rem;
            padding: .2rem .5rem;
            border-radius: 10px;
            background: var(--success-light);
            color: var(--success);
            font-weight: 600;
        }

        .ptkp-info-box {
            background: var(--info-light);
            border: 1px solid #bae6fd;
            border-radius: 8px;
            padding: .75rem 1rem;
            font-size: .82rem;
        }

        .lock-box {
            background: var(--danger-light);
            border: 1px solid #fecaca;
            color: var(--danger);
            border-radius: 8px;
            padding: .75rem 1rem;
            font-size: .85rem;
            font-weight: 600;
        }

        .calculation-preview {
            background: linear-gradient(135deg, rgba(79, 70, 229, .06) 0%, rgba(99, 102, 241, .04) 100%);
            border: 1.5px solid rgba(79, 70, 229, .25);
            border-radius: var(--radius);
            padding: 1.25rem;
        }

        .calc-row {
            display: flex;
            justify-content: space-between;
        }

        .calc-total {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--pri);
            border-top: 2px solid var(--gray-200);
            padding-top: .6rem;
            margin-top: .4rem;
        }

        .mt-rincian .calc-row {
            font-size: .85rem;
        }

        .modal-header-custom {
            background: linear-gradient(135deg, var(--pri) 0%, var(--pri-dark) 100%);
            color: #fff;
            border-radius: 12px 12px 0 0;
            padding: 1.1rem 1.5rem;
        }

        .modal-header-custom .modal-title {
            font-weight: 700;
        }

        .modal-header-custom .btn-close {
            filter: brightness(0) invert(1);
        }

        .section-divider {
            font-size: .8rem;
            font-weight: 700;
            color: var(--gray-700);
            text-transform: uppercase;
            letter-spacing: .5px;
            display: flex;
            align-items: center;
            gap: .5rem;
        }

        .section-divider i {
            color: var(--pri);
        }

        .tanggungan-note {
            background: var(--warning-light);
            border: 1px solid #fcd34d;
            border-radius: 8px;
            padding: .6rem .9rem;
            font-size: .8rem;
            color: var(--warning);
        }

        .report-card {
            height: 100%;
        }

        .report-label {
            font-size: .72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .5px;
            color: var(--gray-400);
            margin-bottom: .35rem;
        }

        .wizard-bar {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2rem;
            position: relative;
        }

        .wizard-bar::before {
            content: '';
            position: absolute;
            top: 18px;
            left: 15%;
            right: 15%;
            height: 2px;
            background: var(--gray-200);
            z-index: 0;
        }

        .wizard-bar.wf6::before {
            left: 8%;
            right: 8%;
        }

        .wz-step {
            position: relative;
            z-index: 1;
            text-align: center;
            flex: 1;
        }

        .wz-circle {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: #fff;
            border: 2px solid var(--gray-200);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto .4rem;
            font-weight: 700;
            font-size: .8rem;
            color: var(--gray-400);
            transition: all .3s;
        }

        .wz-step.active .wz-circle {
            background: var(--pri);
            border-color: var(--pri);
            color: #fff;
            box-shadow: 0 0 0 4px rgba(79, 70, 229, .2);
        }

        .wz-step.done .wz-circle {
            background: var(--success);
            border-color: var(--success);
            color: #fff;
        }

        .wz-label {
            font-size: .75rem;
            color: var(--gray-400);
            font-weight: 600;
        }

        .wz-step.active .wz-label {
            color: var(--pri);
        }

        .wf-step .wz-detail {
            font-size: .7rem;
            color: var(--gray-400);
            margin-top: .1rem;
        }

        .wf-step.active .wz-detail {
            color: var(--pri);
            font-weight: 600;
        }

        .nav-pills .nav-link {
            font-weight: 600;
            color: var(--gray-600);
            border-radius: 8px;
        }

        .nav-pills .nav-link.active {
            background: var(--pri-light);
            color: var(--pri);
        }

        .ana-scroll {
            max-height: 360px;
            overflow-y: auto;
        }
    </style>

    <div class="container-fluid px-4 py-4">
        <div class="card card-shell mb-4">
            <div class="card-body py-3">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <span class="fw-bold small" style="color:var(--pri)">Periode</span>
                        <select class="form-select form-select-sm" style="width:140px" id="filterBulan"
                            onchange="loadData()"></select>
                        <select class="form-select form-select-sm" style="width:100px" id="filterTahun"
                            onchange="loadData()"></select>
                    </div>

                    <ul class="nav nav-pills gap-1" id="pphTabs" role="tablist">
                        <li class="nav-item">
                            <button class="nav-link active" id="tabData" data-bs-toggle="tab" data-bs-target="#paneData"
                                type="button">
                                <i class="fa-solid fa-table me-1"></i>Data PPh 21
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" id="tabAnalisis" data-bs-toggle="tab" data-bs-target="#paneAnalisis"
                                type="button">
                                <i class="fa-solid fa-chart-pie me-1"></i>Analisis &amp; Alur Kerja
                                <span class="badge bg-danger ms-1 d-none" id="badgeAnomali"></span>
                            </button>
                        </li>

                        <li class="nav-item">
                            <button class="nav-link" id="tabDesember" data-bs-toggle="tab" data-bs-target="#paneDesember"
                                type="button">
                                <i class="fa-solid fa-calendar-check me-1"></i>Perhitungan Desember
                            </button>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="lock-box mb-4 d-none" id="lockNotice">
            <i class="fa-solid fa-lock me-1"></i>Periode ini sudah dilaporkan ke Kortex sehingga data terkunci.
            Batalkan lapor Kortex jika perlu mengubah data.
        </div>

        <div class="tab-content">
            <div class="tab-pane fade show active" id="paneData">
                <div class="card card-shell">
                    <div class="card-body py-3 border-bottom">
                        <div class="d-flex gap-2 flex-wrap align-items-center justify-content-between">
                            <select class="form-select form-select-sm" style="width:140px" id="filterDivisi"
                                onchange="renderTable()">
                                <option value="">Semua Divisi</option>
                            </select>
                            <select class="form-select form-select-sm" style="width:160px" id="filterPtkp"
                                onchange="renderTable()">
                                <option value="">Semua Status PTKP</option>
                                <option>TK/0</option>
                                <option>TK/1</option>
                                <option>TK/2</option>
                                <option>TK/3</option>
                                <option>K/0</option>
                                <option>K/1</option>
                                <option>K/2</option>
                                <option>K/3</option>
                            </select>
                            <div class="input-group input-group-sm" style="width:220px">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="fa-solid fa-search small text-muted"></i>
                                </span>
                                <input type="text" class="form-control border-start-0"
                                    placeholder="Cari NIP atau nama..." id="filterSearch" onkeyup="renderTable()">
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                            <button class="btn btn-outline-success btn-sm" onclick="exportData('excel')">
                                <i class="fa-solid fa-file-excel me-1"></i>Export Excel
                            </button>
                            <button class="btn btn-outline-danger btn-sm" onclick="exportData('pdf')">
                                <i class="fa-solid fa-file-pdf me-1"></i>Export PDF
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table id="pphTable" class="table-modern mb-0" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>NIP</th>
                                        <th>Nama</th>
                                        <th>Divisi</th>
                                        <th class="text-end">Gaji Dasar</th>
                                        <th class="text-center">Status PTKP</th>
                                        <th class="text-center">Menikah</th>
                                        <th class="text-center">Jml Anak</th>
                                        <th class="text-end">PPH 21/Bulan</th>
                                        <th class="text-center">Lapor Kortex</th>
                                        <th class="text-center">Bukti Bayar</th>
                                        <th class="text-center">Kode Billing</th>
                                        <th class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="paneAnalisis">
                <div class="d-flex justify-content-end gap-2 mb-3">
                    <button class="btn btn-outline-success btn-sm" onclick="exportAnalisis('excel')">
                        <i class="fa-solid fa-file-excel me-1"></i>Export Excel
                    </button>
                    <button class="btn btn-outline-danger btn-sm" onclick="exportAnalisis('pdf')">
                        <i class="fa-solid fa-file-pdf me-1"></i>Export PDF
                    </button>
                </div>
                <div class="row g-3 mb-4">
                    <div class="col-xl-3 col-md-6">
                        <div class="card stat-card">
                            <div class="card-body d-flex align-items-center justify-content-between">
                                <div>
                                    <p class="text-muted small text-uppercase fw-bold mb-1" style="font-size:.75rem">
                                        Total Karyawan</p>
                                    <h3 class="fw-bold mb-0" id="statTotal">0</h3>
                                </div>
                                <div class="stat-icon" style="background:linear-gradient(135deg,#4f46e5,#7c3aed)">
                                    <i class="fa-solid fa-users"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="card stat-card">
                            <div class="card-body d-flex align-items-center justify-content-between">
                                <div>
                                    <p class="text-muted small text-uppercase fw-bold mb-1" style="font-size:.75rem">
                                        Sudah Diisi Bulan Ini</p>
                                    <h3 class="fw-bold mb-0" style="color:var(--success)" id="statSudah">0</h3>
                                </div>
                                <div class="stat-icon" style="background:linear-gradient(135deg,#059669,#10b981)">
                                    <i class="fa-solid fa-check"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="card stat-card">
                            <div class="card-body d-flex align-items-center justify-content-between">
                                <div>
                                    <p class="text-muted small text-uppercase fw-bold mb-1" style="font-size:.75rem">
                                        Belum Diisi Bulan Ini</p>
                                    <h3 class="fw-bold mb-0" style="color:var(--warning)" id="statBelum">0</h3>
                                </div>
                                <div class="stat-icon" style="background:linear-gradient(135deg,#d97706,#f59e0b)">
                                    <i class="fa-solid fa-exclamation"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="card stat-card">
                            <div class="card-body d-flex align-items-center justify-content-between">
                                <div>
                                    <p class="text-muted small text-uppercase fw-bold mb-1" style="font-size:.75rem">
                                        Total PPH 21 Bulan Ini</p>
                                    <h3 class="fw-bold mb-0" id="statTotalPph">Rp 0</h3>
                                </div>
                                <div class="stat-icon" style="background:linear-gradient(135deg,#0284c7,#38bdf8)">
                                    <i class="fa-solid fa-calculator"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-lg-4 col-md-6">
                        <div class="card stat-card report-card">
                            <div class="card-body" id="cardKortex"></div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <div class="card stat-card report-card">
                            <div class="card-body" id="cardBukti"></div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-12">
                        <div class="card stat-card report-card">
                            <div class="card-body" id="cardBilling"></div>
                        </div>
                    </div>
                </div>

                <div class="row g-3 mb-4" id="anaKesehatan"></div>

                <div class="card card-shell mb-4">
                    <div class="card-body">
                        <div class="section-divider mb-3">
                            <i class="fa-solid fa-list-check"></i>Status Alur Kerja
                            <span id="anaPeriode" class="text-muted fw-normal ms-1"></span>
                        </div>
                        <div class="wizard-bar wf6 mb-0" id="anaWorkflow"></div>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-lg-4">
                        <div class="card card-shell h-100">
                            <div class="card-body">
                                <div class="report-label">Kategori TER (A / B / C)</div>
                                <canvas id="chKategori" height="230"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="card card-shell h-100">
                            <div class="card-body">
                                <div class="report-label">Distribusi Nominal PPh 21</div>
                                <canvas id="chRentang" height="230"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="card card-shell h-100">
                            <div class="card-body">
                                <div class="report-label">Total PPh 21 per Divisi</div>
                                <canvas id="chDivisi" height="230"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-lg-5">
                        <div class="card card-shell h-100">
                            <div class="card-body">
                                <div class="section-divider mb-3">
                                    <i class="fa-solid fa-ranking-star"></i>10 PPh 21 Tertinggi
                                </div>
                                <div class="table-responsive ana-scroll">
                                    <table class="table table-sm mb-0">
                                        <tbody id="anaTop"></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-7">
                        <div class="card card-shell h-100">
                            <div class="card-body">
                                <div class="section-divider mb-3">
                                    <i class="fa-solid fa-triangle-exclamation"></i>Deteksi Anomali
                                    <span id="anaAnomaliCount" class="text-muted fw-normal ms-1"></span>
                                </div>
                                <div class="table-responsive ana-scroll">
                                    <table class="table table-sm mb-0">
                                        <tbody id="anaAnomali"></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="paneDesember">
                <div class="card card-shell">

                    {{-- Bar filter (di dalam kartu tabel) --}}
                    <div class="card-body py-3 border-bottom">
                        <div class="d-flex gap-2 flex-wrap align-items-center justify-content-between">
                            <div class="d-flex gap-2 flex-wrap">
                                <div class="input-group input-group-sm" style="width:220px">
                                    <span class="input-group-text bg-white border-end-0">
                                        <i class="fa-solid fa-search small text-muted"></i>
                                    </span>
                                    <input type="text" class="form-control border-start-0"
                                        placeholder="Cari NIP atau nama..." id="desSearch">
                                </div>
                                <select class="form-select form-select-sm" style="width:210px" id="desStatus">
                                    <option value="">Semua Status Pajak</option>
                                    <option value="kena">Kena PPh (pajak setahun &gt; 0)</option>
                                    <option value="tidak_kena">Tidak kena PPh</option>
                                    <option value="kurang_bayar">Kurang bayar (terhutang &gt; 0)</option>
                                    <option value="lebih_bayar">Lebih bayar (kelebihan potong)</option>
                                    <option value="belum_ptkp">Belum ada PTKP</option>
                                    <option value="ada_peringatan">Ada peringatan data</option>
                                </select>
                                <select class="form-select form-select-sm" style="width:170px" id="desDivisi">
                                    <option value="">Semua Divisi</option>
                                </select>
                                <button class="btn btn-outline-secondary btn-sm" onclick="resetFilterDesember()">
                                    <i class="fa-solid fa-rotate-left me-1"></i>Reset
                                </button>
                            </div>

                            <div class="d-flex gap-2 align-items-center">
                                <small class="text-muted" id="desJumlah"></small>
                                <button class="btn btn-outline-success btn-sm" onclick="exportDesember('excel')">
                                    <i class="fa-solid fa-file-excel me-1"></i>Export Excel
                                </button>
                                <button class="btn btn-outline-danger btn-sm" onclick="exportDesember('pdf')">
                                    <i class="fa-solid fa-file-pdf me-1"></i>Export PDF
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="card-body py-3 border-bottom">
                        <div class="ptkp-info-box">
                            <i class="fa-solid fa-circle-info me-1"></i>
                            Perhitungan PPh 21 setahun <strong id="desTahun">-</strong>:
                            bruto (gaji Jan-Des + THR) &minus; biaya jabatan (5%, maks Rp 6.000.000) = neto;
                            neto &minus; PTKP = PKP; PKP dikenai tarif Pasal 17 berlapis;
                            pajak setahun &minus; PPh yang sudah dipotong = PPh terhutang.
                        </div>
                        <div id="desThrInfo" class="mt-2"></div>
                    </div>

                    {{-- Tabel --}}
                    <div class="card-body p-0">
                        <div class="table-responsive" style="max-height:70vh">
                            <table class="table table-sm table-bordered mb-0 text-nowrap" id="desTable"
                                style="font-size:.8rem">
                                <thead id="desHead"></thead>
                                <tbody id="desBody"></tbody>
                                <tfoot id="desFoot"></tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalPph21" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content" style="border-radius:12px;border:none">
                <div class="modal-header modal-header-custom border-0">
                    <h5 class="modal-title" id="modalPph21Title">Setup PPH 21 Karyawan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body px-4 pt-4">
                    <div class="wizard-bar mb-4">
                        <div class="wz-step active" data-step="1">
                            <div class="wz-circle">1</div>
                            <div class="wz-label">Data PTKP</div>
                        </div>
                        <div class="wz-step" data-step="2">
                            <div class="wz-circle">2</div>
                            <div class="wz-label">Preview &amp; Simpan</div>
                        </div>
                    </div>

                    <div class="wz-content" id="wz1">
                        <div class="ptkp-info-box mb-3">
                            <i class="fa-solid fa-calendar-days me-1"></i>Periode: <strong id="infoPeriode">-</strong>
                        </div>

                        <div class="section-divider mb-3"><i class="fa-solid fa-user"></i>Data Karyawan</div>
                        <div class="row g-2 mb-3">
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Pilih Karyawan <span
                                        class="text-danger">*</span></label>
                                <select class="form-select form-select-sm" id="inputKaryawan"
                                    onchange="onKaryawanChange()">
                                    <option value="">— Pilih Karyawan —</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <div class="info-card h-100">
                                    <div class="label">NIP</div>
                                    <div class="value" id="infoNIP">-</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="info-card h-100">
                                    <div class="label">Gaji Dasar</div>
                                    <div class="value" id="infoGaji">-</div>
                                </div>
                            </div>
                        </div>

                        <div class="ptkp-info-box mb-3" id="awalTunjanganBox">
                            <i class="fa-solid fa-file-invoice-dollar me-1"></i>
                            <span id="awalTunjanganText">PPH 21 dari data Tunjangan: -</span>
                        </div>

                        <hr class="my-3">

                        <div class="section-divider mb-3"><i class="fa-solid fa-id-card"></i>Status PTKP</div>
                        <div class="ptkp-info-box mb-3">
                            <i class="fa-solid fa-info-circle me-1"></i><strong>PTKP</strong>
                            adalah jumlah penghasilan yang tidak dikenakan pajak. Pilih sesuai status perkawinan dan
                            tanggungan.
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Status PTKP <span class="text-danger">*</span></label>
                            <select class="form-select form-select-sm" id="inputPtkp" onchange="updatePreview()">
                                <option value="">— Pilih Status PTKP —</option>
                                <option value="TK/0">TK/0 - Tidak Kawin, 0 Tanggungan</option>
                                <option value="TK/1">TK/1 - Tidak Kawin, 1 Tanggungan</option>
                                <option value="TK/2">TK/2 - Tidak Kawin, 2 Tanggungan</option>
                                <option value="TK/3">TK/3 - Tidak Kawin, 3 Tanggungan</option>
                                <option value="K/0">K/0 - Kawin, 0 Tanggungan</option>
                                <option value="K/1">K/1 - Kawin, 1 Tanggungan</option>
                                <option value="K/2">K/2 - Kawin, 2 Tanggungan</option>
                                <option value="K/3">K/3 - Kawin, 3 Tanggungan</option>
                            </select>
                            <small class="text-muted mt-1 d-block" id="ptkpValueInfo">PTKP Tahunan: -</small>
                        </div>

                        <hr class="my-3">

                        <div class="section-divider mb-3">
                            <i class="fa-solid fa-heart"></i>Status Perkawinan &amp; Anak
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Status Menikah <span
                                    class="text-danger">*</span></label>
                            <div class="d-flex gap-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="statusMenikah" id="menikahYes"
                                        value="1" onchange="onStatusMenikahChange()">
                                    <label class="form-check-label" for="menikahYes">Menikah</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="statusMenikah" id="menikahNo"
                                        value="0" onchange="onStatusMenikahChange()">
                                    <label class="form-check-label" for="menikahNo">Belum Menikah</label>
                                </div>
                            </div>
                        </div>

                        <div id="formAnak" class="mb-3 d-none">
                            <label class="form-label small fw-bold">Jumlah Anak</label>
                            <div class="row g-2 align-items-end mb-3">
                                <div class="col-md-4">
                                    <select class="form-select form-select-sm" id="jumlahAnak"
                                        onchange="updateAnakList()">
                                        <option value="0">0 Anak</option>
                                        <option value="1">1 Anak</option>
                                        <option value="2">2 Anak</option>
                                        <option value="3">3 Anak</option>
                                        <option value="4">4 Anak</option>
                                        <option value="5">5 Anak</option>
                                    </select>
                                </div>
                                <div class="col-md-8">
                                    <small class="text-muted">
                                        <i class="fa-solid fa-circle-info me-1"></i>Hanya <strong>3 anak pertama</strong>
                                        yang menjadi tanggungan PTKP
                                    </small>
                                </div>
                            </div>
                            <div class="section-divider mb-2 mt-3"><i class="fa-solid fa-children"></i>Data Anak</div>
                            <div id="daftarAnak"></div>
                            <div id="tanggunganNote" class="tanggungan-note mt-2 d-none">
                                <i class="fa-solid fa-triangle-exclamation me-1"></i><span id="tanggunganNoteText"></span>
                            </div>
                        </div>

                        <div id="formAnakHidden" class="mb-3 d-none">
                            <div class="ptkp-info-box" style="background:var(--gray-50);border-color:var(--gray-200)">
                                <i class="fa-solid fa-info-circle me-1 text-muted"></i>
                                <span class="text-muted">Form data anak tidak diperlukan untuk status belum
                                    menikah.</span>
                            </div>
                        </div>
                    </div>

                    <div class="wz-content d-none" id="wz2">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <h6 class="fw-bold mb-3">
                                    <i class="fa-solid fa-calculator me-2 text-primary"></i>Preview Perhitungan PPH 21
                                    (TER)
                                </h6>
                                <div class="row g-3 mb-3">
                                    <div class="col-md-6">
                                        <div class="info-card">
                                            <div class="label">Karyawan</div>
                                            <div class="value" id="prevNama">-</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="info-card">
                                            <div class="label">Status PTKP</div>
                                            <div class="value">
                                                <span class="status-badge badge-ptkp" id="prevPtkpBadge">-</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="section-divider mb-2">
                                    <i class="fa-solid fa-money-bill-wave"></i>Dasar Perhitungan Bulanan
                                </div>
                                <div class="calculation-preview mb-3">
                                    <div class="calc-row calc-total" style="border-top:none;margin-top:0;padding-top:0">
                                        <span>Gaji Pokok (basis TER)</span>
                                        <span id="prevBulanan">Rp 0</span>
                                    </div>
                                </div>

                                <div class="section-divider mb-2">
                                    <i class="fa-solid fa-percent"></i>Tarif Efektif Rata-rata (PP 58/2023 &amp; PMK
                                    168/2023)
                                </div>
                                <div class="calculation-preview mb-3">
                                    <div class="calc-row mb-1">
                                        <span class="text-muted">Status PTKP</span>
                                        <span class="fw-bold" id="prevPtkpCode">-</span>
                                    </div>
                                    <div class="calc-row mb-1">
                                        <span class="text-muted">Kategori TER</span>
                                        <span class="fw-bold" id="prevKategori">-</span>
                                    </div>
                                    <div class="calc-row mb-1">
                                        <span class="text-muted">Tarif efektif</span>
                                        <span class="fw-bold" id="prevTarif">-</span>
                                    </div>
                                    <div class="calc-row">
                                        <span class="text-muted">PPH 21 hasil TER (bruto x tarif)</span>
                                        <span class="fw-bold" id="prevPphBulanan">Rp 0</span>
                                    </div>
                                </div>

                                <div id="blokMasaTerakhir" class="mb-3 d-none">
                                    <div class="section-divider mb-2">
                                        <i class="fa-solid fa-flag-checkered"></i>Masa Pajak Terakhir (hitung tahunan,
                                        Pasal 17)
                                    </div>
                                    <div class="calculation-preview mt-rincian" id="mtBody"></div>
                                </div>

                                <div class="calculation-preview">
                                    <div class="calc-row calc-total" style="border-top:none;margin-top:0;padding-top:0">
                                        <span>PPH 21 yang Disimpan
                                            <small class="text-muted fw-normal" id="prevDipakaiSumber"></small>
                                        </span>
                                        <span id="prevDipakai">Rp 0</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button class="btn btn-outline-sec" id="wBtnPrev" style="display:none" onclick="wzPrev()">
                        <i class="fa-solid fa-arrow-left me-1"></i>Sebelumnya
                    </button>
                    <button class="btn btn-pri" id="wBtnNext" onclick="wzNext()">
                        Lanjut <i class="fa-solid fa-arrow-right ms-1"></i>
                    </button>
                    <button class="btn btn-success" id="wBtnSave" style="display:none" onclick="simpanData()">
                        <i class="fa-solid fa-save me-1"></i>Simpan PPH 21
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalDetail" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content" style="border-radius:12px;border:none">
                <div class="modal-header modal-header-custom border-0">
                    <h5 class="modal-title">Detail PPH 21</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="detailContent"></div>
                <div class="modal-footer" style="border-top:1px solid var(--gray-100)">
                    <button type="button" class="btn btn-outline-sec" data-bs-dismiss="modal">Tutup</button>
                    <button type="button" class="btn btn-pri" id="btnEditFromDetail">
                        <i class="fa-solid fa-pen me-1"></i>Edit
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalUpload" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius:12px;border:none">
                <div class="modal-header modal-header-custom border-0">
                    <h5 class="modal-title" id="uploadTitle">Upload</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body px-4 pt-4">
                    <div class="ptkp-info-box mb-3">
                        <i class="fa-solid fa-calendar-days me-1"></i>Periode: <strong id="uploadPeriode">-</strong><br>
                        <span class="text-muted">Berlaku untuk seluruh karyawan pada periode ini.</span>
                    </div>
                    <div class="mb-3 d-none" id="uploadNomorGroup">
                        <label class="form-label small fw-bold">Nomor Kode Billing</label>
                        <input type="text" class="form-control form-control-sm" id="uploadNomor" maxlength="100"
                            placeholder="Masukkan nomor kode billing">
                    </div>
                    <div class="mb-2">
                        <label class="form-label small fw-bold" id="uploadFileLabel">Berkas</label>
                        <input type="file" class="form-control form-control-sm" id="uploadFile"
                            accept=".pdf,.jpg,.jpeg,.png">
                        <small class="text-muted d-block mt-1">Format PDF, JPG, atau PNG. Maksimal 5 MB.</small>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-sec" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-pri" id="btnSubmitUpload" onclick="submitUpload()">
                        <i class="fa-solid fa-upload me-1"></i>Simpan
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalDesember" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content" style="border-radius:12px;border:none">
                <div class="modal-header modal-header-custom border-0">
                    <h5 class="modal-title">Rincian PPh 21 Setahun</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="desDetail"></div>
            </div>
        </div>
    </div>

    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
        const ptkp2026 = [{
                id: 'TK/0',
                nominalPerBulan: 4500000
            },
            {
                id: 'TK/1',
                nominalPerBulan: 4875000
            },
            {
                id: 'TK/2',
                nominalPerBulan: 5250000
            },
            {
                id: 'TK/3',
                nominalPerBulan: 5625000
            },
            {
                id: 'K/0',
                nominalPerBulan: 4875000
            },
            {
                id: 'K/1',
                nominalPerBulan: 5250000
            },
            {
                id: 'K/2',
                nominalPerBulan: 5625000
            },
            {
                id: 'K/3',
                nominalPerBulan: 6000000
            },
        ];

        const TER_KATEGORI = {
            'TK/0': 'A',
            'TK/1': 'A',
            'K/0': 'A',
            'TK/2': 'B',
            'TK/3': 'B',
            'K/1': 'B',
            'K/2': 'B',
            'K/3': 'C'
        };

        const TER_TABEL = {
            A: [
                [5400000, 0],
                [5650000, 0.25],
                [5950000, 0.5],
                [6300000, 0.75],
                [6750000, 1],
                [7500000, 1.25],
                [8550000, 1.5],
                [9650000, 1.75],
                [10050000, 2],
                [10350000, 2.25],
                [10700000, 2.5],
                [11050000, 3],
                [11600000, 3.5],
                [12500000, 4],
                [13750000, 5],
                [15100000, 6],
                [16950000, 7],
                [19750000, 8],
                [24150000, 9],
                [26450000, 10],
                [28000000, 11],
                [30050000, 12],
                [32400000, 13],
                [35400000, 14],
                [39100000, 15],
                [43850000, 16],
                [47800000, 17],
                [51400000, 18],
                [56300000, 19],
                [62200000, 20],
                [68600000, 21],
                [77500000, 22],
                [89000000, 23],
                [103000000, 24],
                [125000000, 25],
                [157000000, 26],
                [206000000, 27],
                [337000000, 28],
                [454000000, 29],
                [550000000, 30],
                [695000000, 31],
                [910000000, 32],
                [1400000000, 33],
                [Infinity, 34],
            ],
            B: [
                [6200000, 0],
                [6500000, 0.25],
                [6850000, 0.5],
                [7300000, 0.75],
                [9200000, 1],
                [10750000, 1.5],
                [11250000, 2],
                [11600000, 2.5],
                [12600000, 3],
                [13600000, 4],
                [14950000, 5],
                [16400000, 6],
                [18450000, 7],
                [21850000, 8],
                [26000000, 9],
                [27700000, 10],
                [29350000, 11],
                [31450000, 12],
                [33950000, 13],
                [37100000, 14],
                [41100000, 15],
                [45800000, 16],
                [49500000, 17],
                [53800000, 18],
                [58500000, 19],
                [64000000, 20],
                [71000000, 21],
                [80000000, 22],
                [93000000, 23],
                [109000000, 24],
                [129000000, 25],
                [163000000, 26],
                [211000000, 27],
                [374000000, 28],
                [459000000, 29],
                [555000000, 30],
                [704000000, 31],
                [957000000, 32],
                [1405000000, 33],
                [Infinity, 34],
            ],
            C: [
                [6600000, 0],
                [6950000, 0.25],
                [7350000, 0.5],
                [7800000, 0.75],
                [8850000, 1],
                [9800000, 1.25],
                [10950000, 1.5],
                [11200000, 1.75],
                [12050000, 2],
                [12950000, 3],
                [14150000, 4],
                [15550000, 5],
                [17050000, 6],
                [19500000, 7],
                [22700000, 8],
                [26600000, 9],
                [28100000, 10],
                [30100000, 11],
                [32600000, 12],
                [35400000, 13],
                [38900000, 14],
                [43000000, 15],
                [47400000, 16],
                [51200000, 17],
                [55800000, 18],
                [60400000, 19],
                [66700000, 20],
                [74500000, 21],
                [83200000, 22],
                [95600000, 23],
                [110000000, 24],
                [134000000, 25],
                [169000000, 26],
                [221000000, 27],
                [390000000, 28],
                [463000000, 29],
                [561000000, 30],
                [709000000, 31],
                [965000000, 32],
                [1419000000, 33],
                [Infinity, 34],
            ],
        };

        const BULAN = [
            'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
        ];

        const URL_DATA = '{{ url('HR-dashboard/perhitungan-pph/data') }}';
        const URL_STORE = '{{ url('HR-dashboard/perhitungan-pph/store') }}';
        const URL_ISI = '{{ url('HR-dashboard/perhitungan-pph/isi-otomatis') }}';
        const URL_KORTEX = '{{ url('HR-dashboard/perhitungan-pph/lapor-kortex') }}';
        const URL_BATAL_KORTEX = '{{ url('HR-dashboard/perhitungan-pph/batal-kortex') }}';
        const URL_BUKTI = '{{ url('HR-dashboard/perhitungan-pph/upload-bukti-bayar') }}';
        const URL_KODE = '{{ url('HR-dashboard/perhitungan-pph/upload-kode-billing') }}';
        const URL_DELETE_ALL = "{{ route('HR.perhitungan-pph.delete-all') }}";
        const URL_ANALISIS = '{{ url('HR-dashboard/perhitungan-pph/analisis') }}';
        const URL_PREVIEW = '{{ url('HR-dashboard/perhitungan-pph/preview') }}';

        const URL_EXP_DATA_XLS = '{{ url('HR-dashboard/perhitungan-pph/export-data-excel') }}';
        const URL_EXP_DATA_PDF = '{{ url('HR-dashboard/perhitungan-pph/export-data-pdf') }}';
        const URL_EXP_ANA_XLS = '{{ url('HR-dashboard/perhitungan-pph/export-analisis-excel') }}';
        const URL_EXP_ANA_PDF = '{{ url('HR-dashboard/perhitungan-pph/export-analisis-pdf') }}';

        let previewToken = 0;
        let analisisKotor = true;
        let charts = {};
        let allKaryawans = [];
        let laporan = null;
        let terkunci = false;
        let dtTable = null;
        let wzStep = 1;
        let formMode = 'create';
        let uploadJenis = null;

        $(document).ready(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            initPeriode();
            initDataTable();
            loadData();
        });

        function esc(s) {
            return String(s ?? '').replace(/[&<>"']/g, c => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#39;'
            } [c]));
        }

        function errMsg(xhr, def) {
            if (xhr.responseJSON) {
                if (xhr.responseJSON.errors) {
                    const first = Object.values(xhr.responseJSON.errors)[0];
                    if (first && first[0]) return first[0];
                }
                if (xhr.responseJSON.message) return xhr.responseJSON.message;
            }
            return def;
        }

        function fmtRp(n) {
            return 'Rp ' + Math.round(n || 0).toLocaleString('id-ID');
        }

        function fmtTarif(t) {
            return String(t).replace('.', ',') + '%';
        }

        function initPeriode() {
            const now = new Date();

            let optBulan = '';
            BULAN.forEach((b, i) => optBulan += `<option value="${i + 1}">${b}</option>`);
            $('#filterBulan').html(optBulan).val(now.getMonth() + 1);

            let optTahun = '';
            for (let y = now.getFullYear() - 3; y <= now.getFullYear() + 1; y++) {
                optTahun += `<option value="${y}">${y}</option>`;
            }
            $('#filterTahun').html(optTahun).val(now.getFullYear());
        }

        function periode() {
            return {
                bulan: parseInt($('#filterBulan').val()),
                tahun: parseInt($('#filterTahun').val())
            };
        }

        function periodeLabel() {
            const p = periode();
            return BULAN[p.bulan - 1] + ' ' + p.tahun;
        }

        function badgeStatus(ok) {
            return ok ?
                `<span class="status-badge badge-menikah">Sudah</span>` :
                `<span class="status-badge badge-belum">Belum</span>`;
        }

        function sumberLabel(p) {
            if (p.sumber === 'tersimpan') return 'Tersimpan bulan ini';
            if (p.sumber === 'tunjangan') return 'Dari tunjangan (belum disimpan)';
            if (p.sumber === 'bulan_lalu') return `Dari bulan ${esc(p.dari)} (belum disimpan)`;
            return p.masa_terakhir ? 'Hitung tahunan (belum disimpan)' : 'Hitung TER dari PTKP (belum disimpan)';
        }

        function initDataTable() {
            dtTable = $('#pphTable').DataTable({
                data: [],
                columns: [{
                        data: 'nip'
                    },
                    {
                        data: null,
                        render: d =>
                            `<strong>${esc(d.nama)}</strong><br><small class="text-muted">${esc(d.jabatan)}</small>`
                    },
                    {
                        data: 'divisi'
                    },
                    {
                        data: null,
                        className: 'text-end',
                        render: d => fmtRp(getGajiDasar(d))
                    },
                    {
                        data: null,
                        className: 'text-center',
                        render: d => d.pph21 ?
                            `<span class="status-badge badge-ptkp">${esc(d.pph21.ptkp)}</span><br><small class="text-muted">TER ${esc(d.ter.kategori || '-')}</small>` :
                            `<span class="text-muted small">Belum diset</span>`
                    },
                    {
                        data: null,
                        className: 'text-center',
                        render: d => d.pph21 ?
                            (d.pph21.menikah ?
                                `<span class="status-badge badge-menikah">Menikah</span>` :
                                `<span class="status-badge badge-belum">Belum</span>`) :
                            `<span class="text-muted">-</span>`
                    },
                    {
                        data: null,
                        className: 'text-center',
                        render: d => d.pph21 ? d.pph21.anak.length : 0
                    },
                    {
                        data: null,
                        className: 'text-end',
                        render: d => {
                            const p = d.pph_bulan;
                            if (!p) return `<span class="text-muted">Belum ada</span>`;
                            let extra = '';
                            if (p.masa_terakhir) {
                                extra +=
                                    ` <span class="status-badge badge-ptkp" title="Masa pajak terakhir: dihitung tahunan (Pasal 17) dikurangi PPh yang sudah dipotong">Masa terakhir</span>`;
                            }
                            if (p.peringatan && p.peringatan.length) {
                                extra +=
                                    ` <i class="fa-solid fa-triangle-exclamation text-warning" title="${esc(p.peringatan.join(' | '))}"></i>`;
                            }
                            return `<strong>${fmtRp(p.nilai)}</strong>${extra}<br><small class="text-muted">${sumberLabel(p)}</small>`;
                        }
                    },
                    {
                        data: null,
                        className: 'text-center',
                        orderable: false,
                        render: () => badgeStatus(laporan && laporan.kortex.sudah)
                    },
                    {
                        data: null,
                        className: 'text-center',
                        orderable: false,
                        render: () => badgeStatus(laporan && laporan.bukti_bayar.sudah)
                    },
                    {
                        data: null,
                        className: 'text-center',
                        orderable: false,
                        render: () => badgeStatus(laporan && laporan.kode_billing.sudah)
                    },
                    {
                        data: null,
                        className: 'text-center',
                        orderable: false,
                        render: d => {
                            const lock = terkunci ? 'disabled' : '';
                            const tersimpan = d.pph_bulan && d.pph_bulan.tersimpan;
                            let h = '<div class="d-flex gap-1 justify-content-center">';
                            if (d.pph21) {
                                h +=
                                    `<button class="btn btn-sm btn-outline-sec" onclick="openDetail(${d.id})" title="Detail"><i class="fa-solid fa-eye"></i></button>`;
                                if (!tersimpan && d.pph_bulan) {
                                    h +=
                                        `<button class="btn btn-sm btn-success" ${lock} onclick="pakaiData(${d.id})" title="Simpan untuk bulan ini"><i class="fa-solid fa-check"></i></button>`;
                                }
                                h +=
                                    `<button class="btn btn-sm btn-pri" ${lock} onclick="openModal('edit', ${d.id})" title="Update"><i class="fa-solid fa-pen"></i></button>`;
                            } else {
                                h +=
                                    `<button class="btn btn-sm btn-pri" ${lock} onclick="openModal('create', ${d.id})" title="Isi data PTKP"><i class="fa-solid fa-pen-to-square me-1"></i>Isi</button>`;
                            }
                            return h + '</div>';
                        }
                    }
                ],
                language: {
                    search: '',
                    searchPlaceholder: 'Cari di tabel...',
                    lengthMenu: 'Tampilkan _MENU_ data',
                    info: 'Menampilkan _START_–_END_ dari _TOTAL_ karyawan',
                    infoEmpty: 'Tidak ada data',
                    paginate: {
                        previous: '‹',
                        next: '›'
                    },
                    zeroRecords: '<div class="text-center py-4 text-muted"><i class="fa-solid fa-filter-circle-xmark fa-2x mb-2 d-block"></i>Tidak ada data sesuai filter</div>',
                    loadingRecords: '<div class="text-center py-4"><i class="fa-solid fa-spinner fa-spin me-2"></i>Memuat...</div>'
                },
                responsive: true,
                pageLength: 15,
                order: [
                    [1, 'asc']
                ],
                dom: 'rt<"d-flex justify-content-between align-items-center mt-3"ip>',
            });
        }

        function loadData() {
            const p = periode();
            $.get(URL_DATA, p, function(res) {
                if (res.success) {
                    allKaryawans = res.data;
                    laporan = res.laporan;
                    terkunci = !!res.meta.terkunci;
                    populateFilterDivisi();
                    applyLockState();
                    renderLaporan();
                    renderTable();
                    analisisKotor = true;
                    desKotor = true;
                    if ($('#tabDesember').hasClass('active')) loadDesember();
                    if ($('#tabAnalisis').hasClass('active')) loadAnalisis();
                    else loadAnalisisBadge();
                }
            }).fail(function() {
                alert('Gagal memuat data');
            });
        }

        function applyLockState() {
            const k = laporan && laporan.kortex;
            $('#lockNotice').toggleClass('d-none', !terkunci);
            if (terkunci && k) {
                $('#lockNotice').html(
                    `<i class="fa-solid fa-lock me-1"></i>Periode ${esc(periodeLabel())} sudah dilaporkan ke Kortex (${esc(k.at)} oleh ${esc(k.by || '-')}), sehingga data terkunci. Tombol Tambah, Edit, Isi Otomatis, Simpan, dan Hapus dinonaktifkan. Klik <strong>Batalkan Lapor</strong> pada kartu Lapor Kortex jika perlu mengubah data.`
                );
            }
            $('#btnTambahPph, #btnIsiOtomatis, #btnHapusSemua, #wBtnSave')
                .prop('disabled', terkunci)
                .attr('title', terkunci ? 'Periode terkunci (sudah lapor Kortex)' : '');
        }

        function exportData(jenis) {
            const q = $.param({
                ...periode(),
                divisi: $('#filterDivisi').val() || '',
                ptkp: $('#filterPtkp').val() || '',
                search: ($('#filterSearch').val() || '').trim()
            });
            window.location.href = (jenis === 'pdf' ? URL_EXP_DATA_PDF : URL_EXP_DATA_XLS) + '?' + q;
        }

        function exportAnalisis(jenis) {
            window.location.href = (jenis === 'pdf' ? URL_EXP_ANA_PDF : URL_EXP_ANA_XLS) + '?' + $.param(periode());
        }

        function renderLaporan() {
            const l = laporan || {
                kortex: {
                    sudah: false
                },
                kode_billing: {
                    sudah: false
                },
                bukti_bayar: {
                    sudah: false
                }
            };
            const k = l.kortex;

            $('#cardKortex').html(`
                <div class="d-flex justify-content-between align-items-start gap-2">
                    <div>
                        <div class="report-label">Lapor Kortex</div>
                        ${badgeStatus(k.sudah)}
                        <div class="small text-muted mt-2">${k.sudah ? `Dilaporkan ${esc(k.at)} oleh ${esc(k.by || '-')}` : 'Belum dilaporkan untuk ' + esc(periodeLabel())}</div>
                    </div>
                    <div class="stat-icon" style="background:linear-gradient(135deg,#4f46e5,#7c3aed)"><i class="fa-solid fa-paper-plane"></i></div>
                </div>
                <div class="mt-3">
                    ${k.sudah
                        ? `<button class="btn btn-sm btn-outline-sec" onclick="batalKortex()"><i class="fa-solid fa-rotate-left me-1"></i>Batalkan Lapor</button>`
                        : `<button class="btn btn-sm btn-pri" onclick="laporKortex()"><i class="fa-solid fa-paper-plane me-1"></i>Lapor Kortex</button>`}
                </div>`);

            renderBerkas('#cardBukti', 'Bukti Bayar', 'fa-receipt', 'linear-gradient(135deg,#059669,#10b981)',
                l.bukti_bayar, 'bukti_bayar');
            renderBerkas('#cardBilling', 'Kode Billing', 'fa-barcode', 'linear-gradient(135deg,#0284c7,#38bdf8)',
                l.kode_billing, 'kode_billing');
        }

        function renderBerkas(sel, judul, icon, bg, data, jenis) {
            const info = data.sudah ? [
                    data.nomor ? `Kode: <strong>${esc(data.nomor)}</strong>` : '',
                    data.file ? `<a href="${data.url}" target="_blank" rel="noopener">${esc(data.file)}</a>` : '',
                    `Diunggah ${esc(data.at)} oleh ${esc(data.by || '-')}`
                ].filter(Boolean).join('<br>') :
                'Belum ada untuk ' + esc(periodeLabel());

            $(sel).html(`
                <div class="d-flex justify-content-between align-items-start gap-2">
                    <div style="min-width:0">
                        <div class="report-label">${judul}</div>
                        ${badgeStatus(data.sudah)}
                        <div class="small text-muted mt-2" style="word-break:break-word">${info}</div>
                    </div>
                    <div class="stat-icon" style="background:${bg}"><i class="fa-solid ${icon}"></i></div>
                </div>
                <div class="mt-3">
                    <button class="btn btn-sm ${data.sudah ? 'btn-outline-sec' : 'btn-pri'}" onclick="openUpload('${jenis}')"><i class="fa-solid fa-upload me-1"></i>${data.sudah ? 'Ganti' : 'Upload ' + judul}</button>
                </div>`);
        }

        function populateFilterDivisi() {
            const current = $('#filterDivisi').val();
            const divisis = [...new Set(allKaryawans.map(k => k.divisi))].filter(d => d && d !== '-');
            let html = '<option value="">Semua Divisi</option>';
            divisis.forEach(d => html += `<option>${esc(d)}</option>`);
            $('#filterDivisi').html(html).val(current || '');
        }

        function getGajiDasar(k) {
            return parseInt(k.bruto) || 0;
        }

        function getKaryawan(id) {
            return allKaryawans.find(k => k.id == id);
        }

        function getPphByKaryawanId(karyawanId) {
            const k = getKaryawan(karyawanId);
            return k ? k.pph21 : null;
        }

        function terTarif(bruto, kategori) {
            for (const [batas, tarif] of TER_TABEL[kategori]) {
                if (bruto <= batas) return tarif;
            }
            return 34;
        }

        function hitungPph21(bruto, ptkpCode) {
            const kategori = TER_KATEGORI[ptkpCode] || null;
            if (!bruto || !kategori) {
                return {
                    kategori,
                    tarif: 0,
                    bulanan: 0,
                    tahunan: 0,
                    bruto: bruto || 0
                };
            }
            const tarif = terTarif(bruto, kategori);
            const bulanan = Math.round(bruto * tarif / 100);
            return {
                kategori,
                tarif,
                bulanan,
                tahunan: bulanan * 12,
                bruto
            };
        }

        function teksAwal(k) {
            if (!k || k.pph_tunjangan === null || k.pph_tunjangan === undefined) {
                return 'Belum ada data PPH 21 di Tunjangan untuk periode ini. Nilai akan dihitung dari status PTKP.';
            }
            return `PPH 21 dari data Tunjangan periode ini: <strong>${fmtRp(k.pph_tunjangan)}</strong>`;
        }

        function renderTable() {
            const filterDivisi = $('#filterDivisi').val();
            const filterPtkp = $('#filterPtkp').val();
            const filterSearch = $('#filterSearch').val().toLowerCase();

            const filtered = allKaryawans.filter(k => {
                if (filterDivisi && k.divisi !== filterDivisi) return false;
                if (filterSearch && !k.nama.toLowerCase().includes(filterSearch) &&
                    !String(k.nip).includes(filterSearch)) return false;
                if (filterPtkp && (!k.pph21 || k.pph21.ptkp !== filterPtkp)) return false;
                return true;
            });

            dtTable.clear().rows.add(filtered).draw();

            let totalSudah = 0;
            let totalPph = 0;
            allKaryawans.forEach(k => {
                if (k.pph_bulan && k.pph_bulan.tersimpan) totalSudah++;
                if (k.pph_bulan) totalPph += k.pph_bulan.nilai;
            });

            $('#statTotal').text(allKaryawans.length);
            $('#statSudah').text(totalSudah);
            $('#statBelum').text(allKaryawans.length - totalSudah);
            $('#statTotalPph').text(fmtRp(totalPph));
        }

        function populateKaryawanDropdown(selectedId = '') {
            const select = $('#inputKaryawan');
            select.html('<option value="">— Pilih Karyawan —</option>');
            allKaryawans.forEach(k => {
                const hasPph = getPphByKaryawanId(k.id);
                const label = `${k.nip} - ${k.nama} (${k.jabatan})${hasPph ? ' ✓' : ''}`;
                select.append(
                    `<option value="${k.id}" ${k.id == selectedId ? 'selected' : ''}>${esc(label)}</option>`);
            });
        }

        function onKaryawanChange() {
            const id = $('#inputKaryawan').val();
            if (!id) {
                $('#infoNIP').text('-');
                $('#infoGaji').text('-');
                $('#awalTunjanganText').text('PPH 21 dari data Tunjangan: -');
                return;
            }
            const k = getKaryawan(id);
            $('#infoNIP').text(k.nip);
            $('#infoGaji').text(fmtRp(getGajiDasar(k)));
            $('#awalTunjanganText').html(teksAwal(k));
            updatePreview();
        }

        function onStatusMenikahChange() {
            const menikah = $('input[name="statusMenikah"]:checked').val();
            if (menikah === '1') {
                $('#formAnak').removeClass('d-none');
                $('#formAnakHidden').addClass('d-none');
                if ($('#daftarAnak').children().length === 0) {
                    $('#jumlahAnak').val('0');
                    updateAnakList();
                }
            } else {
                $('#formAnak').addClass('d-none');
                $('#formAnakHidden').removeClass('d-none');
                $('#jumlahAnak').val('0');
                $('#daftarAnak').html('');
                $('#tanggunganNote').addClass('d-none');
            }
            updatePreview();
        }

        function updateAnakList() {
            const jumlah = parseInt($('#jumlahAnak').val()) || 0;
            const container = $('#daftarAnak');
            const existingData = [];

            container.find('.child-name-input').each(function() {
                existingData.push($(this).val());
            });
            container.html('');

            for (let i = 0; i < jumlah; i++) {
                const isTanggungan = i < 3;
                const nama = existingData[i] || '';
                container.append(`
                    <div class="child-item mb-2">
                        <div class="child-number">${i + 1}</div>
                        <input type="text" class="form-control form-control-sm child-name-input" placeholder="Nama Anak ${i + 1}" value="${esc(nama)}">
                        ${isTanggungan ? '<span class="child-tanggungan">Tanggungan</span>' : '<span class="text-muted small">Non-tanggungan</span>'}
                    </div>`);
            }

            if (jumlah > 3) {
                $('#tanggunganNote').removeClass('d-none');
                $('#tanggunganNoteText').text(
                    `${jumlah} anak tercatat, namun hanya 3 anak pertama yang menjadi tanggungan PTKP`);
            } else {
                $('#tanggunganNote').addClass('d-none');
            }
            updatePreview();
        }

        function setDipakai(nilai, sumber) {
            $('#prevDipakai').text(fmtRp(nilai));
            $('#prevDipakaiSumber').text(sumber);
        }

        function updatePreview() {
            const kId = $('#inputKaryawan').val();
            const ptkpCode = $('#inputPtkp').val();
            if (!kId) return;

            const k = getKaryawan(kId);
            const bruto = getGajiDasar(k);
            const c = hitungPph21(bruto, ptkpCode);
            const dariTunjangan = k.pph_tunjangan !== null && k.pph_tunjangan !== undefined;

            $('#prevNama').text(`${k.nama} (${k.nip})`);
            $('#prevPtkpBadge').text(ptkpCode || '-');
            $('#prevBulanan').text(fmtRp(bruto));
            $('#prevPtkpCode').text(ptkpCode || '-');
            $('#prevKategori').text(c.kategori ? 'Kategori ' + c.kategori : '-');
            $('#prevTarif').text(c.kategori ? fmtTarif(c.tarif) : '-');
            $('#prevPphBulanan').text(fmtRp(c.bulanan));
            setDipakai(
                dariTunjangan ? k.pph_tunjangan : c.bulanan,
                dariTunjangan ? '(dari data Tunjangan)' : '(hitung TER dari PTKP)'
            );

            if (ptkpCode) {
                const p = ptkp2026.find(x => x.id === ptkpCode);
                $('#ptkpValueInfo').html(
                    `PTKP ${ptkpCode} = <strong>${fmtRp(p.nominalPerBulan)}/bulan</strong> (${fmtRp(p.nominalPerBulan * 12)}/tahun) — Kategori TER <strong>${c.kategori}</strong>`
                );
            } else {
                $('#ptkpValueInfo').text('PTKP Tahunan: -');
            }

            $('#blokMasaTerakhir').addClass('d-none');
            if (k.masa_terakhir && ptkpCode) loadPreviewTerakhir(k, ptkpCode, dariTunjangan);
        }

        function loadPreviewTerakhir(k, ptkpCode, dariTunjangan) {
            const token = ++previewToken;
            const p = periode();

            $('#blokMasaTerakhir').removeClass('d-none');
            $('#mtBody').html(
                '<div class="text-muted small"><i class="fa-solid fa-spinner fa-spin me-1"></i>Menghitung masa pajak terakhir...</div>'
            );

            $.get(URL_PREVIEW, {
                    karyawan_id: k.id,
                    ptkp: ptkpCode,
                    bulan: p.bulan,
                    tahun: p.tahun
                })
                .done(function(res) {
                    if (token !== previewToken) return;
                    const h = res.hasil;
                    if (!h || !h.masa_terakhir) {
                        $('#blokMasaTerakhir').addClass('d-none');
                        return;
                    }
                    const r = h.rincian;
                    const row = (l, v, cls = '') =>
                        `<div class="calc-row mb-1"><span class="text-muted">${l}</span><span class="fw-bold ${cls}">${v}</span></div>`;

                    let html =
                        row(`Penghasilan bruto (${r.jumlah_bulan} bulan)`, fmtRp(r.bruto_setahun)) +
                        row('Biaya jabatan (5%, maks Rp 500.000/bulan)', '- ' + fmtRp(r.biaya_jabatan)) +
                        row('Iuran JHT + JP karyawan', '- ' + fmtRp(r.iuran_pengurang)) +
                        row('Penghasilan neto', fmtRp(r.neto_setahun)) +
                        row(`PTKP setahun (${esc(ptkpCode)})`, '- ' + fmtRp(r.ptkp_setahun), 'text-success') +
                        row('Penghasilan Kena Pajak (PKP)', fmtRp(r.pkp)) +
                        row('PPh 21 terutang setahun (Pasal 17)', fmtRp(r.pph_setahun)) +
                        row('Sudah dipotong bulan sebelumnya', '- ' + fmtRp(r.sudah_dipotong)) +
                        `<div class="calc-row calc-total"><span>${r.selisih < 0 ? 'Kelebihan potong' : 'PPh 21 masa pajak terakhir'}</span><span>${fmtRp(Math.abs(r.selisih))}</span></div>`;

                    if (h.peringatan && h.peringatan.length) {
                        html += h.peringatan.map(w =>
                            `<div class="tanggungan-note mt-2"><i class="fa-solid fa-triangle-exclamation me-1"></i>${esc(w)}</div>`
                        ).join('');
                    }

                    $('#mtBody').html(html);
                    if (!dariTunjangan) setDipakai(h.nilai, '(masa pajak terakhir: hitung tahunan)');
                })
                .fail(function() {
                    if (token !== previewToken) return;
                    $('#mtBody').html(
                        '<div class="text-danger small">Gagal memuat perhitungan masa pajak terakhir.</div>');
                });
        }

        function goStep(n) {
            wzStep = n;
            $('.wz-step').each(function() {
                const s = +$(this).data('step');
                $(this).removeClass('active done');
                if (s < n) $(this).addClass('done');
                if (s === n) $(this).addClass('active');
            });
            $('.wz-content').addClass('d-none');
            $(`#wz${n}`).removeClass('d-none');
            $('#wBtnPrev').toggle(n > 1);
            $('#wBtnNext').toggle(n < 2);
            $('#wBtnSave').toggle(n === 2);
        }

        function wzNext() {
            if (wzStep !== 1) return;

            const kId = $('#inputKaryawan').val();
            const ptkp = $('#inputPtkp').val();
            const menikah = $('input[name="statusMenikah"]:checked').val();

            if (!kId) {
                alert('Pilih karyawan terlebih dahulu!');
                return;
            }
            if (!ptkp) {
                alert('Pilih status PTKP!');
                return;
            }
            if (menikah === undefined) {
                alert('Pilih status menikah!');
                return;
            }

            updatePreview();
            goStep(2);
        }

        function wzPrev() {
            if (wzStep > 1) goStep(wzStep - 1);
        }

        function resetWizard() {
            wzStep = 1;
            goStep(1);
            formMode = 'create';
            $('#inputKaryawan').prop('disabled', false).val('');
            $('#inputPtkp').val('');
            $('input[name="statusMenikah"]').prop('checked', false);
            $('#jumlahAnak').val('0');
            $('#daftarAnak').html('');
            $('#formAnak').addClass('d-none');
            $('#formAnakHidden').addClass('d-none');
            $('#tanggunganNote').addClass('d-none');
            $('#infoNIP').text('-');
            $('#infoGaji').text('-');
            $('#awalTunjanganText').text('PPH 21 dari data Tunjangan: -');
            $('#ptkpValueInfo').text('PTKP Tahunan: -');
            $('#modalPph21Title').text('Setup PPH 21 Karyawan');
            $('#wBtnSave').html('<i class="fa-solid fa-save me-1"></i>Simpan PPH 21');
            $('#infoPeriode').text(periodeLabel());
        }

        function openModal(mode, karyawanId = null) {
            if (terkunci) {
                alert('Periode ini sudah dilaporkan ke Kortex. Batalkan lapor Kortex terlebih dahulu untuk mengubah data.');
                return;
            }

            if (mode === 'create' && karyawanId && getPphByKaryawanId(karyawanId)) mode = 'edit';

            if (!karyawanId) {
                const kosong = allKaryawans.filter(k => !k.pph21);
                if (!kosong.length) {
                    alert('Semua karyawan sudah punya status PTKP. Gunakan tombol simpan atau edit pada tabel.');
                    return;
                }
            }

            resetWizard();
            formMode = mode;
            const $karyawan = $('#inputKaryawan');

            if (!karyawanId) {
                $karyawan.prop('disabled', false).html('<option value="">— Pilih Karyawan —</option>');
                allKaryawans.filter(k => !k.pph21).forEach(k => {
                    $karyawan.append(
                        `<option value="${k.id}">${esc(k.nip + ' - ' + k.nama + ' (' + k.jabatan + ')')}</option>`
                    );
                });
                $('#modalPph21Title').text('Tambah PPH 21 Karyawan');
                new bootstrap.Modal(document.getElementById('modalPph21')).show();
                return;
            }

            populateKaryawanDropdown(karyawanId);
            $karyawan.prop('disabled', true).val(String(karyawanId));

            if (mode === 'edit') {
                const pph = getPphByKaryawanId(karyawanId);
                if (!pph) {
                    alert('Data PPH 21 tidak ditemukan');
                    $karyawan.prop('disabled', false);
                    return;
                }
                $('#modalPph21Title').text('Update PPH 21 Karyawan');
                $('#wBtnSave').html('<i class="fa-solid fa-save me-1"></i>Update PPH 21');
                onKaryawanChange();
                $('#inputPtkp').val(pph.ptkp);

                if (pph.menikah) {
                    $('#menikahYes').prop('checked', true);
                    $('#formAnak').removeClass('d-none');
                    $('#jumlahAnak').val(pph.anak.length);
                    updateAnakList();
                    $('#daftarAnak .child-name-input').each(function(i) {
                        if (pph.anak[i]) $(this).val(pph.anak[i].nama);
                    });
                } else {
                    $('#menikahNo').prop('checked', true);
                    $('#formAnakHidden').removeClass('d-none');
                }
            } else {
                $('#modalPph21Title').text('Isi PPH 21 Karyawan');
                onKaryawanChange();
            }

            updatePreview();
            new bootstrap.Modal(document.getElementById('modalPph21')).show();
        }

        function simpanData() {
            if (terkunci) {
                alert('Periode ini sudah dilaporkan ke Kortex.');
                return;
            }

            const kId = $('#inputKaryawan').val();
            const ptkp = $('#inputPtkp').val();
            const menikah = $('input[name="statusMenikah"]:checked').val();
            const p = periode();

            const anak = [];
            if (menikah === '1') {
                $('#daftarAnak .child-name-input').each(function() {
                    const nama = $(this).val().trim();
                    if (nama) anak.push({
                        nama
                    });
                });
            }

            $.ajax({
                url: URL_STORE,
                method: 'POST',
                data: {
                    karyawan_id: kId,
                    ptkp: ptkp,
                    status_menikah: menikah,
                    anak: anak,
                    bulan: p.bulan,
                    tahun: p.tahun
                },
                success: function(res) {
                    if (res.success) {
                        let msg = res.message;
                        if (res.peringatan && res.peringatan.length) {
                            msg += '\n\nPerhatian:\n- ' + res.peringatan.join('\n- ');
                        }
                        alert(msg);
                        bootstrap.Modal.getInstance(document.getElementById('modalPph21')).hide();
                        loadData();
                    }
                },
                error: function(xhr) {
                    alert(errMsg(xhr, 'Gagal menyimpan data!'));
                }
            });
        }

        function pakaiData(karyawanId) {
            if (terkunci) {
                alert('Periode ini sudah dilaporkan ke Kortex.');
                return;
            }
            const pph = getPphByKaryawanId(karyawanId);
            if (!pph) return;
            const p = periode();

            $.ajax({
                url: URL_STORE,
                method: 'POST',
                data: {
                    karyawan_id: karyawanId,
                    ptkp: pph.ptkp,
                    status_menikah: pph.menikah ? 1 : 0,
                    anak: pph.anak,
                    pakai_data: 1,
                    bulan: p.bulan,
                    tahun: p.tahun
                },
                success: function(res) {
                    if (res.success) loadData();
                },
                error: function(xhr) {
                    alert(errMsg(xhr, 'Gagal menyimpan data!'));
                }
            });
        }

        function isiOtomatis() {
            if (terkunci) {
                alert('Periode ' + periodeLabel() + ' sudah dilaporkan ke Kortex. Batalkan lapor Kortex terlebih dahulu.');
                return;
            }
            if (!confirm(
                    `Isi otomatis PPH 21 periode ${periodeLabel()} untuk semua karyawan yang belum tersimpan, memakai data bulan sebelumnya?`
                )) {
                return;
            }

            $.ajax({
                url: URL_ISI,
                method: 'POST',
                data: periode(),
                success: function(res) {
                    alert(res.message);
                    loadData();
                },
                error: function(xhr) {
                    alert(errMsg(xhr, 'Gagal mengisi otomatis!'));
                }
            });
        }

        function laporKortex() {
            if (!confirm(
                    `Lapor Kortex untuk periode ${periodeLabel()}?\nBerlaku untuk semua karyawan dan data periode ini akan terkunci.`
                )) {
                return;
            }

            $.ajax({
                url: URL_KORTEX,
                method: 'POST',
                data: periode(),
                success: function(res) {
                    alert(res.message);
                    loadData();
                },
                error: function(xhr) {
                    alert(errMsg(xhr, 'Gagal melapor Kortex!'));
                }
            });
        }

        function batalKortex() {
            if (!confirm(`Batalkan lapor Kortex periode ${periodeLabel()}?`)) return;

            $.ajax({
                url: URL_BATAL_KORTEX,
                method: 'POST',
                data: periode(),
                success: function(res) {
                    alert(res.message);
                    loadData();
                },
                error: function(xhr) {
                    alert(errMsg(xhr, 'Gagal membatalkan lapor Kortex!'));
                }
            });
        }

        function openUpload(jenis) {
            uploadJenis = jenis;
            const isBilling = jenis === 'kode_billing';

            $('#uploadTitle').text(isBilling ? 'Upload Kode Billing' : 'Upload Bukti Bayar');
            $('#uploadFileLabel').text(isBilling ? 'Berkas Kode Billing' : 'Berkas Bukti Bayar');
            $('#uploadNomorGroup').toggleClass('d-none', !isBilling);
            $('#uploadPeriode').text(periodeLabel());
            $('#uploadFile').val('');
            $('#uploadNomor').val(isBilling && laporan && laporan.kode_billing.nomor ? laporan.kode_billing.nomor : '');
            new bootstrap.Modal(document.getElementById('modalUpload')).show();
        }

        function submitUpload() {
            const p = periode();
            const file = $('#uploadFile')[0].files[0];
            const nomor = $('#uploadNomor').val().trim();
            const isBilling = uploadJenis === 'kode_billing';

            if (!isBilling && !file) {
                alert('Pilih berkas bukti bayar terlebih dahulu!');
                return;
            }
            if (isBilling && !file && !nomor) {
                alert('Isi nomor kode billing atau pilih berkasnya!');
                return;
            }

            const fd = new FormData();
            fd.append('bulan', p.bulan);
            fd.append('tahun', p.tahun);
            if (file) fd.append('file', file);
            if (isBilling && nomor) fd.append('nomor', nomor);

            $('#btnSubmitUpload').prop('disabled', true);

            $.ajax({
                url: isBilling ? URL_KODE : URL_BUKTI,
                method: 'POST',
                data: fd,
                processData: false,
                contentType: false,
                success: function(res) {
                    alert(res.message);
                    bootstrap.Modal.getInstance(document.getElementById('modalUpload')).hide();
                    loadData();
                },
                error: function(xhr) {
                    alert(errMsg(xhr, 'Gagal mengunggah berkas!'));
                },
                complete: function() {
                    $('#btnSubmitUpload').prop('disabled', false);
                }
            });
        }

        function openDetail(karyawanId) {
            const k = getKaryawan(karyawanId);
            const pph = getPphByKaryawanId(karyawanId);
            if (!k || !pph) return;

            const bruto = getGajiDasar(k);
            const calc = hitungPph21(bruto, pph.ptkp);

            let anakHtml = '';
            if (pph.anak.length > 0) {
                pph.anak.forEach((a, i) => {
                    const isTanggungan = i < 3;
                    anakHtml +=
                        `<div class="child-item mb-2"><div class="child-number">${i + 1}</div><div class="child-name" style="flex:1;font-weight:500">${esc(a.nama)}</div>${isTanggungan ? '<span class="child-tanggungan">Tanggungan</span>' : '<span class="text-muted small">Non-tanggungan</span>'}</div>`;
                });
            } else {
                anakHtml = '<div class="text-muted small">Tidak ada data anak</div>';
            }
            if (pph.anak.length > 3) {
                anakHtml +=
                    `<div class="tanggungan-note mt-2"><i class="fa-solid fa-triangle-exclamation me-1"></i>${pph.anak.length} anak tercatat, hanya 3 anak pertama yang menjadi tanggungan PTKP</div>`;
            }

            const p = k.pph_bulan;
            const nilai = p ? p.nilai : calc.bulanan;
            const sumber = p ? sumberLabel(p) : 'Hitung TER dari PTKP';
            const catatanTerakhir = k.masa_terakhir ?
                `<div class="tanggungan-note mt-2"><i class="fa-solid fa-flag-checkered me-1"></i>Periode ini adalah masa pajak terakhir. Nilai di bawah memakai hitung tahunan (Pasal 17); buka <strong>Edit</strong> untuk melihat rinciannya.</div>` :
                '';

            const html = `
                <div class="ptkp-info-box mb-3"><i class="fa-solid fa-calendar-days me-1"></i>Periode: <strong>${esc(periodeLabel())}</strong></div>
                <div class="row g-2 mb-3">
                    <div class="col-md-6"><div class="info-card"><div class="label">NIP</div><div class="value">${esc(k.nip)}</div></div></div>
                    <div class="col-md-6"><div class="info-card"><div class="label">Nama</div><div class="value">${esc(k.nama)}</div></div></div>
                    <div class="col-md-6"><div class="info-card"><div class="label">Jabatan</div><div class="value">${esc(k.jabatan)}</div></div></div>
                    <div class="col-md-6"><div class="info-card"><div class="label">Gaji Pokok</div><div class="value">${fmtRp(bruto)}</div></div></div>
                </div>
                <div class="section-divider mb-2"><i class="fa-solid fa-id-card"></i>Status PTKP</div>
                <div class="row g-2 mb-3">
                    <div class="col-md-4"><div class="info-card"><div class="label">Status PTKP</div><div class="value"><span class="status-badge badge-ptkp">${esc(pph.ptkp)}</span></div></div></div>
                    <div class="col-md-4"><div class="info-card"><div class="label">Status Menikah</div><div class="value">${pph.menikah ? '<span class="status-badge badge-menikah">Menikah</span>' : '<span class="status-badge badge-belum">Belum</span>'}</div></div></div>
                    <div class="col-md-4"><div class="info-card"><div class="label">Jumlah Anak</div><div class="value">${pph.anak.length} Anak</div></div></div>
                </div>
                ${pph.menikah ? `<div class="section-divider mb-2"><i class="fa-solid fa-children"></i>Daftar Anak</div>${anakHtml}` : ''}
                <div class="section-divider mb-2 mt-3"><i class="fa-solid fa-calculator"></i>Perhitungan PPH 21 (TER)</div>
                <div class="calculation-preview">
                    <div class="calc-row mb-1"><span class="text-muted">Gaji Pokok</span><span class="fw-bold">${fmtRp(bruto)}</span></div>
                    <div class="calc-row mb-1"><span class="text-muted">Kategori TER (${esc(pph.ptkp)})</span><span class="fw-bold">${calc.kategori || '-'}</span></div>
                    <div class="calc-row mb-1"><span class="text-muted">Tarif efektif</span><span class="fw-bold">${calc.kategori ? fmtTarif(calc.tarif) : '-'}</span></div>
                    <div class="calc-row mb-1"><span class="text-muted">PPH 21 hasil TER</span><span class="fw-bold">${fmtRp(calc.bulanan)}</span></div>
                    <div class="calc-row calc-total"><span>PPH 21 Periode Ini <small class="text-muted fw-normal">(${sumber})</small></span><span>${fmtRp(nilai)}</span></div>
                </div>
                ${catatanTerakhir}`;

            $('#detailContent').html(html);
            $('#btnEditFromDetail').prop('disabled', terkunci).off('click').on('click', function() {
                bootstrap.Modal.getInstance(document.getElementById('modalDetail')).hide();
                setTimeout(() => openModal('edit', karyawanId), 300);
            });
            new bootstrap.Modal(document.getElementById('modalDetail')).show();
        }

        function confirmDeleteAllPph() {
            if (!confirm('Hapus semua data PPH 21 periode ' + periodeLabel() +
                    '?\nStatus PTKP karyawan tidak ikut dihapus.')) {
                return;
            }

            if (!confirm(
                    'PERINGATAN!\n\nData yang dihapus TIDAK DAPAT DIKEMBALIKAN LAGI.\n\nLanjutkan menghapus semua data PPH 21 periode ini?'
                )) {
                return;
            }

            const p = periode();

            $.ajax({
                    url: URL_DELETE_ALL,
                    method: 'DELETE',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content'),
                        bulan: p.bulan,
                        tahun: p.tahun
                    }
                })
                .done(function(res) {
                    alert(res.message || 'Semua data PPH 21 berhasil dihapus');
                    loadData();
                })
                .fail(function(xhr) {
                    alert(errMsg(xhr, 'Gagal menghapus data!'));
                });
        }

        $(document).on('shown.bs.tab', '#tabAnalisis', function() {
            if (analisisKotor) loadAnalisis();
        });

        $(document).on('shown.bs.tab', '#tabData', function() {
            if (dtTable) dtTable.columns.adjust().responsive.recalc();
        });

        function loadAnalisisBadge() {
            $.get(URL_ANALISIS, periode(), function(res) {
                if (!res.success) return;
                const n = res.anomali_total || 0;
                $('#badgeAnomali').text(n).toggleClass('d-none', n === 0);
            });
        }

        function loadAnalisis() {
            const p = periode();
            $('#anaPeriode').text('— ' + periodeLabel());
            $('#anaKesehatan').html(
                '<div class="col-12 text-center text-muted py-3"><i class="fa-solid fa-spinner fa-spin me-2"></i>Memuat analisis...</div>'
            );

            $.get(URL_ANALISIS, p, function(res) {
                if (!res.success) return;
                analisisKotor = false;
                renderAnalisis(res);
            }).fail(function() {
                $('#anaKesehatan').html(
                    '<div class="col-12 text-center text-danger py-3">Gagal memuat analisis.</div>');
            });
        }

        function statCard(judul, nilai, sub, icon, bg, warna = '') {
            return `
                <div class="col-xl-3 col-md-6">
                    <div class="card stat-card h-100">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div>
                                <p class="text-muted small text-uppercase fw-bold mb-1" style="font-size:.75rem">${judul}</p>
                                <h4 class="fw-bold mb-0" style="${warna}">${nilai}</h4>
                                <div class="small text-muted mt-1">${sub}</div>
                            </div>
                            <div class="stat-icon" style="background:${bg}"><i class="fa-solid ${icon}"></i></div>
                        </div>
                    </div>
                </div>`;
        }

        function makeChart(id, config) {
            if (charts[id]) charts[id].destroy();
            charts[id] = new Chart(document.getElementById(id), config);
        }

        function renderAnalisis(res) {
            const h = res.kesehatan;
            const n = res.anomali_total || 0;
            $('#badgeAnomali').text(n).toggleClass('d-none', n === 0);

            let delta = '<span class="text-muted">Belum ada data bulan lalu</span>';
            if (h.total_pph_bulan_lalu > 0) {
                const naik = h.selisih > 0;
                const sama = h.selisih === 0;
                const ikon = sama ? 'fa-minus' : (naik ? 'fa-arrow-trend-up' : 'fa-arrow-trend-down');
                const warna = sama ? 'text-muted' : (naik ? 'text-danger' : 'text-success');
                delta =
                    `<span class="${warna}"><i class="fa-solid ${ikon} me-1"></i>${naik ? '+' : ''}${fmtRp(h.selisih)} (${h.persen > 0 ? '+' : ''}${h.persen}%)</span> vs ${esc(h.bulan_lalu_label)}`;
            }

            $('#anaKesehatan').html(
                statCard('Karyawan Eligible', h.total_eligible, 'Aktif / resign di periode ini', 'fa-users',
                    'linear-gradient(135deg,#4f46e5,#7c3aed)') +
                statCard('Sudah Punya PTKP', `${h.punya_ptkp}/${h.total_eligible}`,
                    h.belum_ptkp ? `${h.belum_ptkp} belum diset` : 'Semua lengkap', 'fa-id-card',
                    'linear-gradient(135deg,#059669,#10b981)',
                    h.belum_ptkp ? 'color:var(--warning)' : 'color:var(--success)') +
                statCard('Nilai Bulan Ini Tersimpan', `${h.sudah_diisi}/${h.total_eligible}`,
                    h.belum_diisi ? `${h.belum_diisi} belum disimpan` : 'Semua tersimpan', 'fa-check-double',
                    'linear-gradient(135deg,#d97706,#f59e0b)',
                    h.belum_diisi ? 'color:var(--warning)' : 'color:var(--success)') +
                statCard('Total PPH 21', fmtRp(h.total_pph), delta, 'fa-calculator',
                    'linear-gradient(135deg,#0284c7,#38bdf8)')
            );

            const aktif = res.workflow.aktif;
            $('#anaWorkflow').html(res.workflow.steps.map((s, i) => {
                const cls = s.selesai ? 'done' : (i === aktif ? 'active' : '');
                return `
                    <div class="wz-step wf-step ${cls}">
                        <div class="wz-circle">${s.selesai ? '<i class="fa-solid fa-check"></i>' : i + 1}</div>
                        <div class="wz-label">${esc(s.judul)}</div>
                        <div class="wz-detail">${esc(s.detail)}</div>
                    </div>`;
            }).join(''));

            const d = res.distribusi;
            const kat = Object.keys(d.kategori);

            makeChart('chKategori', {
                type: 'doughnut',
                data: {
                    labels: kat.map(x => x === 'Belum PTKP' ? x : 'Kategori ' + x),
                    datasets: [{
                        data: kat.map(x => d.kategori[x].jumlah),
                        backgroundColor: ['#4f46e5', '#0284c7', '#059669', '#9ca3af']
                    }]
                },
                options: {
                    plugins: {
                        legend: {
                            position: 'bottom'
                        },
                        tooltip: {
                            callbacks: {
                                label: c =>
                                    ` ${c.label}: ${c.raw} orang (${fmtRp(d.kategori[kat[c.dataIndex]].total)})`
                            }
                        }
                    }
                }
            });

            makeChart('chRentang', {
                type: 'bar',
                data: {
                    labels: d.rentang.map(r => r.label),
                    datasets: [{
                        label: 'Jumlah karyawan',
                        data: d.rentang.map(r => r.jumlah),
                        backgroundColor: '#4f46e5'
                    }]
                },
                options: {
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            }
                        }
                    }
                }
            });

            makeChart('chDivisi', {
                type: 'bar',
                data: {
                    labels: d.divisi.map(x => x.divisi),
                    datasets: [{
                        label: 'Total PPH 21',
                        data: d.divisi.map(x => x.total),
                        backgroundColor: '#0284c7'
                    }]
                },
                options: {
                    indexAxis: 'y',
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: c =>
                                    ` ${fmtRp(c.raw)} (${d.divisi[c.dataIndex].jumlah} orang, rata-rata ${fmtRp(d.divisi[c.dataIndex].rata_rata)})`
                            }
                        }
                    }
                }
            });

            $('#anaTop').html(d.top.length ?
                d.top.map((r, i) => `
                    <tr>
                        <td class="text-muted">${i + 1}</td>
                        <td><strong>${esc(r.nama)}</strong><br><small class="text-muted">${esc(r.nip)} · ${esc(r.divisi)}</small></td>
                        <td class="text-center"><span class="status-badge badge-ptkp">${esc(r.ptkp || '-')}</span></td>
                        <td class="text-end fw-bold">${fmtRp(r.nilai)}</td>
                    </tr>`).join('') :
                '<tr><td class="text-muted text-center py-3">Belum ada data</td></tr>');

            $('#anaAnomaliCount').text(n ? `(${n})` : '');
            const lbl = {
                tinggi: 'Tinggi',
                sedang: 'Sedang',
                rendah: 'Rendah'
            };
            $('#anaAnomali').html(res.anomali.length ?
                res.anomali.map(a => `
                    <tr>
                        <td><span class="status-badge badge-${a.level}">${lbl[a.level]}</span></td>
                        <td><strong>${esc(a.nama)}</strong> <small class="text-muted">${esc(a.nip)}</small><br><small>${esc(a.pesan)}</small></td>
                    </tr>`).join('') :
                '<tr><td class="text-center text-success py-3"><i class="fa-solid fa-circle-check me-1"></i>Tidak ada anomali terdeteksi</td></tr>'
            );
        }

        const URL_DESEMBER = '{{ url('HR-dashboard/perhitungan-pph/desember') }}';
        let desKotor = true;
        let desData = [];
        const BLN_PENDEK = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

        $(document).on('shown.bs.tab', '#tabDesember', function() {
            if (desKotor) loadDesember();
        });

        const URL_DES_XLS = '{{ url('HR-dashboard/perhitungan-pph/desember/export-excel') }}';
        const URL_DES_PDF = '{{ url('HR-dashboard/perhitungan-pph/desember/export-pdf') }}';
        let desTimer = null;

        function desParams() {
            const $p = $('#paneDesember');
            return {
                tahun: periode().tahun,
                search: ($p.find('#desSearch').val() || '').trim(),
                status: $p.find('#desStatus').val() || '',
                divisi: $p.find('#desDivisi').val() || ''
            };
        }

        function loadDesember() {
            const p = desParams();
            $('#desTahun').text(p.tahun);
            $('#desBody').html(
                '<tr><td class="text-center py-4 text-muted"><i class="fa-solid fa-spinner fa-spin me-2"></i>Menghitung...</td></tr>'
            );

            $.get(URL_DESEMBER, p, function(res) {
                if (!res.success) return;
                desKotor = false;
                desData = res.data;

                const cur = $('#desDivisi').val();
                $('#desDivisi').html('<option value="">Semua Divisi</option>' +
                    res.divisi_list.map(d => `<option>${esc(d)}</option>`).join('')).val(cur || '');

                $('#desJumlah').text(`${res.jumlah} dari ${res.jumlah_semua} karyawan`);
                renderThrInfo(res.thr_info);
                renderDesember(res);
            }).fail(function(xhr) {
                $('#desBody').html(
                    `<tr><td class="text-center text-danger py-4">${esc(errMsg(xhr, 'Gagal memuat perhitungan Desember.'))}</td></tr>`
                );
            });
        }

        $(document).off('.des');
        $(document).on('change.des', '#paneDesember #desStatus, #paneDesember #desDivisi', function() {
            loadDesember();
        });
        $(document).on('input.des', '#paneDesember #desSearch', function() {
            clearTimeout(desTimer);
            desTimer = setTimeout(loadDesember, 400);
        });

        function resetFilterDesember() {
            $('#desSearch').val('');
            $('#desStatus').val('');
            $('#desDivisi').val('');
            loadDesember();
        }

        function exportDesember(jenis) {
            const url = (jenis === 'pdf' ? URL_DES_PDF : URL_DES_XLS) + '?' + $.param(desParams());
            window.location.href = url;
        }

        function renderDesember(res) {
            const stick = 'position:sticky;left:0;background:#fff;z-index:1';
            const th = (t, cls = '') => `<th class="text-center align-middle ${cls}">${t}</th>`;

            let head = '<tr>' + th('No') + `<th class="align-middle" style="${stick};z-index:2">Nama</th>` + th(
                'Gaji Pokok');
            BLN_PENDEK.forEach(b => head += th('Gaji ' + b));
            BLN_PENDEK.forEach(b => head += th('PPh ' + b));
            head += th('THR') + th('Total Setahun<br>(Gaji + THR)') + th('Biaya Jabatan') + th('Neto') +
                th('PTKP') + th('PKP') + th('Pajak Setahun') + th('PPh Sudah Dipotong') + th('PPh Terhutang') + th('Aksi') +
                '</tr>';
            $('#desHead').html(head);

            const td = v => `<td class="text-end">${fmtRp(v)}</td>`;

            $('#desBody').html(res.data.length ? res.data.map(r => {
                let tr =
                    `<tr><td class="text-center">${r.no}</td>
                    <td style="${stick}"><strong>${esc(r.nama)}</strong><br><small class="text-muted">${esc(r.nip)}</small>
                    ${r.peringatan.length ? `<i class="fa-solid fa-triangle-exclamation text-warning ms-1" title="${esc(r.peringatan.join(' | '))}"></i>` : ''}</td>`;
                tr += td(r.gaji_pokok);
                r.gaji_bulan.forEach(v => tr += td(v));
                r.pph_bulan.forEach((v, i) => {
                    const s = r.pph_sumber[i];
                    tr += s ?
                        `<td class="text-end fst-italic text-muted" title="Memakai data bulan ${String(s).padStart(2, '0')}">${fmtRp(v)}</td>` :
                        td(v);
                });
                tr += td(r.thr) + td(r.bruto) + td(r.biaya_jabatan) + td(r.neto);
                tr +=
                    `<td class="text-end">${fmtRp(r.ptkp)}<br><small class="text-muted">${esc(r.ptkp_kode || '-')}</small></td>`;
                tr += td(r.pkp) + td(r.pajak_setahun) + td(r.sudah_dipotong);
                tr +=
                    `<td class="text-end fw-bold ${r.terhutang < 0 ? 'text-danger' : ''}">${fmtRp(r.terhutang)}</td>`;
                tr +=
                    `<td class="text-center"><button class="btn btn-sm btn-outline-sec" onclick="openDesDetail(${r.id})"><i class="fa-solid fa-eye"></i></button></td></tr>`;
                return tr;
            }).join('') : '<tr><td class="text-center text-muted py-4">Tidak ada data</td></tr>');

            const t = res.total;
            $('#desFoot').html(
                `<tr class="fw-bold" style="background:var(--gray-50)">
                <td colspan="3" class="text-end">TOTAL</td><td colspan="24"></td>
                ${td(t.thr)}${td(t.bruto)}${td(t.biaya_jabatan)}${td(t.neto)}<td></td>${td(t.pkp)}${td(t.pajak_setahun)}${td(t.sudah_dipotong)}${td(t.terhutang)}<td></td></tr>`
            );
        }

        function openDesDetail(id) {
            const r = desData.find(x => x.id == id);
            if (!r) return;

            const row = (l, v, cls = '') =>
                `<div class="calc-row mb-1"><span class="text-muted">${l}</span><span class="fw-bold ${cls}">${v}</span></div>`;

            const lapisan = r.lapisan.length ?
                r.lapisan.map(l => row(
                    `${fmtRp(l.dasar)} x ${fmtTarif(l.tarif)} <small>(${fmtRp(l.dari)} - ${l.sampai ? fmtRp(l.sampai) : 'ke atas'})</small>`,
                    fmtRp(l.pajak))).join('') :
                row('PKP Rp 0, tidak ada pajak', fmtRp(0));

            $('#desDetail').html(
                `
                <div class="info-card mb-3"><div class="label">Karyawan</div><div class="value">${esc(r.nama)} (${esc(r.nip)}) - ${esc(r.divisi)}</div></div>
                <div class="calculation-preview mb-3">
                    ${row('Total gaji Jan-Des', fmtRp(r.total_gaji))}
                    ${row('THR (dari fitur THR)', fmtRp(r.thr))}
                    ${row('Total penghasilan bruto setahun', fmtRp(r.bruto))}
                    ${row('Biaya jabatan (5%, maks ' + fmtRp(r.maks_biaya_jabatan) + ')', '- ' + fmtRp(r.biaya_jabatan))}
                    ${row('Penghasilan neto', fmtRp(r.neto))}
                    ${row('PTKP (' + esc(r.ptkp_kode || '-') + ')', '- ' + fmtRp(r.ptkp), 'text-success')}
                    ${row('PKP', fmtRp(r.pkp))}
                </div>
                <div class="section-divider mb-2"><i class="fa-solid fa-layer-group"></i>Tarif Pasal 17 (berlapis)</div>
                <div class="calculation-preview mb-3">
                    ${lapisan}
                    <div class="calc-row calc-total"><span>Pajak setahun</span><span>${fmtRp(r.pajak_setahun)}</span></div>
                </div>
                <div class="calculation-preview">
                    ${row('Pajak setahun', fmtRp(r.pajak_setahun))}
                    ${row('PPh sudah dipotong (Jan-Des)', '- ' + fmtRp(r.sudah_dipotong))}
                    <div class="calc-row calc-total"><span>${r.terhutang < 0 ? 'Kelebihan potong' : 'PPh terhutang (dibayar Desember)'}</span><span>${fmtRp(Math.abs(r.terhutang))}</span></div>
                </div>
                ${r.catatan_pph.map(c => `<div class="ptkp-info-box mt-2"><i class="fa-solid fa-copy me-1"></i>${esc(c)}</div>`).join('')}
                ${r.peringatan.map(w => `<div class="tanggungan-note mt-2"><i class="fa-solid fa-triangle-exclamation me-1"></i>${esc(w)}</div>`).join('')}`
            );

            new bootstrap.Modal(document.getElementById('modalDesember')).show();
        }

        function renderThrInfo(i) {
            if (!i) {
                $('#desThrInfo').html(
                    `<div class="tanggungan-note"><i class="fa-solid fa-triangle-exclamation me-1"></i>Belum ada periode di fitur THR, nilai THR dihitung Rp 0.</div>`
                );
                return;
            }
            if (i.fallback) {
                $('#desThrInfo').html(
                    `<div class="tanggungan-note"><i class="fa-solid fa-triangle-exclamation me-1"></i>Belum ada periode THR untuk tahun ${esc($('#desTahun').text())}. Memakai periode THR terakhir yang dibuat: <strong>${esc(i.nama)} (${i.tahun})</strong>.</div>`
                );
                return;
            }
            $('#desThrInfo').html(
                `<div class="ptkp-info-box"><i class="fa-solid fa-gift me-1"></i>THR diambil dari fitur THR: <strong>${esc(i.nama)} (${i.tahun})</strong></div>`
            );
        }
    </script>
@endsection
