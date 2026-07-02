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
            --shadow-sm: 0 1px 3px rgba(0, 0, 0, .08), 0 1px 2px rgba(0, 0, 0, .05);
            --shadow: 0 4px 6px rgba(0, 0, 0, .07), 0 2px 4px rgba(0, 0, 0, .05);
            --shadow-lg: 0 10px 25px rgba(0, 0, 0, .1), 0 4px 10px rgba(0, 0, 0, .07);
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

        .badge-status {
            padding: .35rem .75rem;
            border-radius: 20px;
            font-size: .7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .5px;
        }

        .bs-draft {
            background: var(--gray-100);
            color: var(--gray-600);
        }

        .bs-calculated {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .bs-approved {
            background: var(--success-light);
            color: var(--success);
        }

        .bs-paid {
            background: #ede9fe;
            color: #6d28d9;
        }

        .bs-cancelled {
            background: var(--danger-light);
            color: var(--danger);
        }

        .bs-none {
            background: var(--warning-light);
            color: var(--warning);
        }

        .detail-panel {
            background: var(--gray-50);
            border-left: 4px solid var(--pri);
            border-radius: 0 var(--radius) var(--radius) 0;
            padding: 1.5rem;
        }

        .detail-section h6 {
            font-weight: 700;
            color: var(--gray-700);
            font-size: .8rem;
            text-transform: uppercase;
            letter-spacing: .6px;
            margin-bottom: .75rem;
            padding-bottom: .5rem;
            border-bottom: 1px solid var(--gray-200);
            display: flex;
            align-items: center;
            gap: .4rem;
        }

        .detail-item {
            display: flex;
            justify-content: space-between;
            padding: .45rem 0;
            font-size: .85rem;
            color: var(--gray-600);
            border-bottom: 1px dashed var(--gray-200);
        }

        .detail-item:last-child {
            border-bottom: none;
        }

        .detail-total {
            font-weight: 700;
            font-size: .95rem;
            color: var(--gray-900);
            border-top: 2px solid var(--gray-200);
            padding-top: .6rem;
            margin-top: .4rem;
            border-bottom: none !important;
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
            left: 8%;
            right: 8%;
            height: 2px;
            background: var(--gray-200);
            z-index: 0;
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
            font-size: .72rem;
            color: var(--gray-400);
            font-weight: 600;
        }

        .wz-step.active .wz-label {
            color: var(--pri);
        }

        .preview-box {
            background: linear-gradient(135deg, rgba(79, 70, 229, .06) 0%, rgba(99, 102, 241, .04) 100%);
            border: 1.5px solid rgba(79, 70, 229, .25);
            border-radius: var(--radius);
            padding: 1.25rem;
            margin-top: 1.25rem;
        }

        .preview-box h6 {
            font-weight: 700;
            color: var(--pri);
            margin-bottom: .75rem;
            font-size: .85rem;
        }

        .preview-row {
            display: flex;
            justify-content: space-between;
            padding: .35rem 0;
            font-size: .875rem;
        }

        .preview-total {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--pri);
            border-top: 2px solid var(--gray-200);
            padding-top: .6rem;
            margin-top: .4rem;
        }

        .form-label {
            font-weight: 600;
            color: var(--gray-700);
            font-size: .85rem;
            margin-bottom: .4rem;
        }

        .mono-input {
            font-family: 'Courier New', monospace;
            font-weight: 600;
        }

        .readonly-val {
            background: var(--gray-50) !important;
            border: 1px solid var(--gray-200) !important;
            color: var(--gray-600) !important;
            cursor: not-allowed;
        }

        .badge-source {
            font-size: .65rem;
            padding: .2rem .5rem;
            border-radius: 10px;
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

        .modal-header-custom {
            background: linear-gradient(135deg, var(--pri) 0%, var(--pri-dark) 100%);
            color: #fff;
            border-radius: 12px 12px 0 0;
        }

        .modal-header-custom .btn-close {
            filter: brightness(0) invert(1);
        }

        .chart-wrap {
            position: relative;
            height: 260px;
        }

        .tunjangan-source-info {
            background: var(--info-light);
            border: 1px solid #bae6fd;
            border-radius: 8px;
            padding: .75rem 1rem;
            margin-bottom: 1rem;
            font-size: .82rem;
        }

        .tunjangan-empty-info {
            background: var(--warning-light);
            border: 1px solid #fcd34d;
            border-radius: 8px;
            padding: .75rem 1rem;
            margin-bottom: 1rem;
            font-size: .82rem;
        }

        .dataTables_wrapper {
            padding: 0 !important;
        }

        .dataTables_wrapper .row {
            margin: 0 !important;
            border: none !important;
        }

        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter {
            padding: 1rem 1.5rem !important;
            background: var(--gray-50);
            border-bottom: 1px solid var(--gray-200);
            margin: 0 !important;
        }

        .dataTables_wrapper .dataTables_length label,
        .dataTables_wrapper .dataTables_filter label {
            margin: 0 !important;
            font-size: .85rem;
            font-weight: 600;
            color: var(--gray-600);
            display: flex;
            align-items: center;
            gap: .5rem;
        }

        .dataTables_wrapper .dataTables_length select {
            border: 1px solid var(--gray-200) !important;
            border-radius: 6px !important;
            padding: .35rem .75rem !important;
            font-size: .85rem !important;
            color: var(--gray-700) !important;
            background: #fff !important;
            transition: all .2s;
            width: auto !important;
            margin: 0 .5rem !important;
        }

        .dataTables_wrapper .dataTables_length select:focus {
            border-color: var(--pri) !important;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, .15) !important;
            outline: none !important;
        }

        .dataTables_wrapper .dataTables_filter input {
            border: 1px solid var(--gray-200) !important;
            border-radius: 6px !important;
            padding: .35rem .75rem !important;
            font-size: .85rem !important;
            color: var(--gray-700) !important;
            background: #fff !important;
            transition: all .2s;
            margin-left: .5rem !important;
        }

        .dataTables_wrapper .dataTables_filter input:focus {
            border-color: var(--pri) !important;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, .15) !important;
            outline: none !important;
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

        #payrollTable,
        #auditTable {
            border-collapse: separate;
            border-spacing: 0;
        }

        #payrollTable thead th,
        #auditTable thead th {
            font-size: .75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .5px;
            color: var(--gray-600);
            background: var(--gray-50);
            border-bottom: 2px solid var(--gray-200) !important;
            border-top: none !important;
        }

        #payrollTable tbody tr {
            cursor: pointer;
            transition: background .15s;
        }

        #payrollTable tbody tr:hover {
            background: var(--pri-light) !important;
        }

        #payrollTable tbody td,
        #auditTable tbody td {
            vertical-align: middle;
            font-size: .875rem;
            border-bottom: 1px solid var(--gray-100) !important;
            border-top: none !important;
        }

        #payrollTable tbody tr:last-child td,
        #auditTable tbody tr:last-child td {
            border-bottom: none !important;
        }

        .dt-loading {
            text-align: center;
            padding: 3rem;
            color: var(--gray-400);
        }

        .expand-btn {
            color: var(--gray-400);
            transition: transform .25s;
            font-size: .75rem;
        }

        .expanded .expand-btn {
            transform: rotate(90deg);
            color: var(--pri);
        }

        .audit-badge {
            padding: .3rem .65rem;
            border-radius: 12px;
            font-size: .68rem;
            font-weight: 700;
        }

        .audit-create {
            background: var(--success-light);
            color: var(--success);
        }

        .audit-edit {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .audit-approve {
            background: #ede9fe;
            color: #6d28d9;
        }

        #toast-container {
            position: fixed;
            top: 1.25rem;
            right: 1.25rem;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            gap: .5rem;
        }

        .toast-msg {
            padding: .75rem 1.25rem;
            border-radius: 8px;
            font-weight: 600;
            font-size: .875rem;
            color: #fff;
            box-shadow: var(--shadow-lg);
            display: flex;
            align-items: center;
            gap: .5rem;
            animation: slideIn .3s ease;
        }

        .toast-success {
            background: var(--success);
        }

        .toast-error {
            background: var(--danger);
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(50px);
            }

            to {
                opacity: 1;
                transform: none;
            }
        }

        .spinner-overlay {
            position: fixed;
            inset: 0;
            background: rgba(255, 255, 255, .6);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9998;
        }
    </style>

    <div id="toast-container"></div>

    <div class="container-fluid px-4 py-4">

        <div class="d-sm-flex align-items-center justify-content-between page-header">
            <div>
                <h1 class="page-title">Perhitungan BPJS</h1>
                <p class="page-sub mb-0">Periode: <strong>{{ \Carbon\Carbon::create()->month($bulanSekarang)->format('F') }}
                        {{ $tahunSekarang }}</strong></p>
            </div>
            <div class="d-flex gap-2 mt-2 mt-sm-0">
                <button class="btn btn-pri" onclick="openWizard()"><i class="fa-solid fa-plus me-2"></i>Buat
                    Payroll</button>
            </div>
        </div>

        <div class="card card-shell mb-4">
            <div class="card-body py-3">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <span class="fw-bold text-primary" style="font-size:.875rem;"><i
                            class="fa-solid fa-filter me-1"></i>Filter</span>
                    <div class="d-flex gap-2 flex-wrap" id="filterGroup">
                        <select id="fBulan" class="form-select form-select-sm" style="width:120px"
                            onchange="applyFilter()">
                            <option value="">Semua Bulan</option>
                            @foreach ([1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'] as $n => $nm)
                                <option value="{{ $n }}" {{ $n == $bulanSekarang ? 'selected' : '' }}>
                                    {{ $nm }}</option>
                            @endforeach
                        </select>
                        <select id="fTahun" class="form-select form-select-sm" style="width:95px"
                            onchange="applyFilter()">
                            <option value="">Semua Tahun</option>
                            @for ($y = date('Y') - 2; $y <= date('Y') + 1; $y++)
                                <option value="{{ $y }}" {{ $y == $tahunSekarang ? 'selected' : '' }}>
                                    {{ $y }}</option>
                            @endfor
                        </select>
                        <select id="fDivisi" class="form-select form-select-sm" style="width:140px"
                            onchange="applyFilter()">
                            <option value="">Semua Divisi</option>
                            @foreach ($karyawans->pluck('divisi')->unique()->filter()->sort() as $d)
                                <option value="{{ $d }}">{{ $d }}</option>
                            @endforeach
                        </select>
                        <select id="fStatus" class="form-select form-select-sm" style="width:155px"
                            onchange="applyFilter()">
                            <option value="">Semua Status</option>
                            <option value="sudah">Sudah Ada Payroll</option>
                            <option value="belum">Belum Ada Payroll</option>
                            <option value="draft">Draft</option>
                            <option value="calculated">Calculated</option>
                            <option value="approved">Approved</option>
                            <option value="paid">Paid</option>
                        </select>
                        <button class="btn btn-sm btn-outline-secondary" onclick="resetFilter()"><i
                                class="fa-solid fa-rotate me-1"></i>Reset</button>

                        <button class="btn btn-outline-success" onclick="exportExcel()"><i class="fa-solid fa-file-excel me-2"></i>Export Excel</button>
                        <button class="btn btn-outline-danger" onclick="exportPdf()"><i class="fa-solid fa-file-pdf me-2"></i>Export PDF</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="card stat-card">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div>
                            <p class="stat-label">Total Karyawan Aktif</p>
                            <h3 class="stat-value">{{ $totalKaryawan }}</h3>
                        </div>
                        <div class="stat-icon" style="background:linear-gradient(135deg,#4f46e5,#7c3aed)"><i
                                class="fa-solid fa-users"></i></div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card stat-card">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div>
                            <p class="stat-label">Sudah Ada Payroll</p>
                            <h3 class="stat-value" id="statSudah">{{ $sudahPayroll }}</h3>
                        </div>
                        <div class="stat-icon" style="background:linear-gradient(135deg,#059669,#10b981)"><i
                                class="fa-solid fa-check-circle"></i></div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card stat-card">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div>
                            <p class="stat-label">Belum Ada Payroll</p>
                            <h3 class="stat-value" id="statBelum">{{ $belumPayroll }}</h3>
                        </div>
                        <div class="stat-icon" style="background:linear-gradient(135deg,#d97706,#f59e0b)"><i
                                class="fa-solid fa-exclamation-circle"></i></div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card stat-card">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div>
                            <p class="stat-label">Total Gaji Pokok</p>
                            <h3 class="stat-value">Rp {{ number_format($totalGaji, 0, ',', '.') }}</h3>
                        </div>
                        <div class="stat-icon" style="background:linear-gradient(135deg,#0284c7,#38bdf8)"><i
                                class="fa-solid fa-money-bill-wave"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <ul class="nav nav-tabs nav-tabs-custom mb-4" id="mainTabs">
            <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tabPayroll"><i
                        class="fa-solid fa-table me-2"></i>Data Payroll</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabInfo"><i
                        class="fa-solid fa-circle-info me-2"></i>Informasi BPJS</button></li>
            <li class="nav-item"><button class="nav-link" id="tabStatBtn" data-bs-toggle="tab"
                    data-bs-target="#tabStat"><i class="fa-solid fa-chart-pie me-2"></i>Statistik</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabAudit"><i
                        class="fa-solid fa-clock-rotate-left me-2"></i>Riwayat</button></li>
        </ul>

        <div class="tab-content">
            <div class="tab-pane fade show active" id="tabPayroll">
                <div class="card card-shell">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table id="payrollTable" class="table mb-0" style="width:100%">
                                <thead>
                                    <tr>
                                        <th style="width:36px"></th>
                                        <th>NIP</th>
                                        <th>Nama Karyawan</th>
                                        <th>Jabatan / Divisi</th>
                                        <th>Status</th>
                                        <th>Gaji Pokok</th>
                                        <th>THP Bersih</th>
                                        <th>Total BPJS Perusahaan</th>
                                        <th>Status Payroll</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="tabInfo">
                <div class="row g-4">
                    <div class="col-lg-6">
                        <div class="card card-shell">
                            <div class="card-header"
                                style="background:linear-gradient(135deg,#059669,#10b981);color:#fff;border:none;border-radius:10px 10px 0 0;padding:1rem 1.5rem">
                                <h6 class="mb-0 fw-bold"><i class="fa-solid fa-building me-2"></i>Ditanggung Perusahaan
                                </h6>
                            </div>
                            <div class="card-body p-4">
                                {{-- Menambahkan elemen ke-4 ($formula) pada array untuk menampilkan rumus --}}
                                @foreach ([
                                    ['JHT – Jaminan Hari Tua', '3.70%', 'Salary BPJSTK', 'Salary BPJSTK × 3.7%'],
                                    ['JKM – Jaminan Kematian', '0.30%', 'Salary BPJSTK', 'Salary BPJSTK × 0.3%'],
                                    ['JKK – Jaminan Kecelakaan Kerja', '0.24%', 'Salary BPJSTK', 'Salary BPJSTK × 0.24%'],
                                    ['JP – Jaminan Pensiun', '2.00%', 'Salary BPJSTK', 'Salary BPJSTK × 2%'],
                                    ['BPJS Kesehatan', '4.00%', 'UMK Bandung', 'UMK Bandung × 4%']
                                ] as [$label, $pct, $base, $formula])
                                    <div class="py-2 px-3 rounded mb-2" style="background:rgba(5,150,105,.07)">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <div>
                                                <div class="fw-semibold" style="font-size:.875rem">{{ $label }}</div>
                                                <small class="text-muted">Dari {{ $base }}</small>
                                            </div>
                                            <span class="badge bg-success fs-6">{{ $pct }}</span>
                                        </div>
                                        {{-- Menampilkan Rumus --}}
                                        <div class="mt-1 text-muted" style="font-size:.75rem; font-family: monospace;">
                                            <i class="fa-solid fa-calculator me-1"></i> {{ $formula }}
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="card card-shell">
                            <div class="card-header"
                                style="background:linear-gradient(135deg,#0284c7,#38bdf8);color:#fff;border:none;border-radius:10px 10px 0 0;padding:1rem 1.5rem">
                                <h6 class="mb-0 fw-bold"><i class="fa-solid fa-user me-2"></i>Ditanggung Karyawan</h6>
                            </div>
                            <div class="card-body p-4">
                                @foreach ([
                                    ['JHT – Jaminan Hari Tua', '2.00%', 'Salary BPJSTK', 'Salary BPJSTK × 2%'],
                                    ['JP – Jaminan Pensiun', '1.00%', 'Salary BPJSTK', 'Salary BPJSTK × 1%'],
                                    ['BPJS Kesehatan', '1.00%', 'UMK Bandung', 'UMK Bandung × 1%']
                                ] as [$label, $pct, $base, $formula])
                                    <div class="py-2 px-3 rounded mb-2" style="background:rgba(2,132,199,.07)">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <div>
                                                <div class="fw-semibold" style="font-size:.875rem">{{ $label }}</div>
                                                <small class="text-muted">Dari {{ $base }}</small>
                                            </div>
                                            <span class="badge bg-info fs-6">{{ $pct }}</span>
                                        </div>
                                        {{-- Menampilkan Rumus --}}
                                        <div class="mt-1 text-muted" style="font-size:.75rem; font-family: monospace;">
                                            <i class="fa-solid fa-calculator me-1"></i> {{ $formula }}
                                        </div>
                                    </div>
                                @endforeach
                                <div class="mt-4 p-3 rounded"
                                    style="background:var(--gray-50);border:1px solid var(--gray-200)">
                                    <h6 class="fw-bold mb-3"
                                        style="font-size:.8rem;color:var(--gray-600);text-transform:uppercase;letter-spacing:.5px">
                                        Rumus THP Bersih</h6>
                                    <code
                                        style="display:block;background:#fff;padding:.75rem;border-radius:6px;border:1px solid var(--gray-200);font-size:.8rem">
                                        THP Bersih = (Gaji Pokok + Tunjangan)<br>
                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;− BPJS
                                        Karyawan<br>
                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;− Potongan Lainnya
                                    </code>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- BLOK CONTOH SIMULASI PERHITUNGAN --}}
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card card-shell">
                            <div class="card-header"
                                style="background:linear-gradient(135deg,#4f46e5,#7c3aed);color:#fff;border:none;border-radius:10px 10px 0 0;padding:1rem 1.5rem">
                                <h6 class="mb-0 fw-bold"><i class="fa-solid fa-lightbulb me-2"></i>Contoh Simulasi Perhitungan</h6>
                            </div>
                            <div class="card-body p-4">
                                <div class="alert alert-light border mb-4" style="font-size:.85rem;">
                                    <strong>Asumsi Dasar:</strong><br>
                                    • Salary BPJSTK = <strong>Rp 10.000.000</strong><br>
                                    • UMK Bandung = <strong>Rp 2.100.000</strong>
                                </div>
                                <div class="row g-4">
                                    <div class="col-md-6">
                                        <h6 class="fw-bold mb-3" style="color:var(--success)"><i class="fa-solid fa-building me-2"></i>Total BPJS Perusahaan</h6>
                                        <div style="font-size:.85rem;">
                                            <div class="d-flex justify-content-between py-2 border-bottom">
                                                <span>JHT (3.7% × 10.000.000)</span>
                                                <span class="fw-bold">Rp 370.000</span>
                                            </div>
                                            <div class="d-flex justify-content-between py-2 border-bottom">
                                                <span>JKM (0.3% × 10.000.000)</span>
                                                <span class="fw-bold">Rp 30.000</span>
                                            </div>
                                            <div class="d-flex justify-content-between py-2 border-bottom">
                                                <span>JKK (0.24% × 10.000.000)</span>
                                                <span class="fw-bold">Rp 24.000</span>
                                            </div>
                                            <div class="d-flex justify-content-between py-2 border-bottom">
                                                <span>JP (2% × 10.000.000)</span>
                                                <span class="fw-bold">Rp 200.000</span>
                                            </div>
                                            <div class="d-flex justify-content-between py-2 border-bottom">
                                                <span>BPJS Kes. (4% × 2.100.000)</span>
                                                <span class="fw-bold">Rp 84.000</span>
                                            </div>
                                            <div class="d-flex justify-content-between py-2 fw-bold" style="color:var(--success)">
                                                <span>TOTAL PERUSAHAAN</span>
                                                <span>Rp 708.000</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <h6 class="fw-bold mb-3" style="color:var(--info)"><i class="fa-solid fa-user me-2"></i>Total Potongan BPJS Karyawan</h6>
                                        <div style="font-size:.85rem;">
                                            <div class="d-flex justify-content-between py-2 border-bottom">
                                                <span>JHT (2% × 10.000.000)</span>
                                                <span class="fw-bold">Rp 200.000</span>
                                            </div>
                                            <div class="d-flex justify-content-between py-2 border-bottom">
                                                <span>JP (1% × 10.000.000)</span>
                                                <span class="fw-bold">Rp 100.000</span>
                                            </div>
                                            <div class="d-flex justify-content-between py-2 border-bottom">
                                                <span>BPJS Kes. (1% × 2.100.000)</span>
                                                <span class="fw-bold">Rp 21.000</span>
                                            </div>
                                            <div class="d-flex justify-content-between py-2 fw-bold" style="color:var(--info)">
                                                <span>TOTAL POTONGAN KARYAWAN</span>
                                                <span>Rp 321.000</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="tabStat">
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <div class="card card-shell">
                            <div class="card-body text-center">
                                <p class="stat-label mb-1">Total THP Kotor</p>
                                <h4 class="fw-bold text-primary mb-0" id="stTHPKotor">Rp —</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card card-shell">
                            <div class="card-body text-center">
                                <p class="stat-label mb-1">Total BPJS Perusahaan</p>
                                <h4 class="fw-bold text-success mb-0" id="stBPJSPer">Rp —</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card card-shell">
                            <div class="card-body text-center">
                                <p class="stat-label mb-1">Total BPJS Karyawan</p>
                                <h4 class="fw-bold text-warning mb-0" id="stBPJSKar">Rp —</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card card-shell">
                            <div class="card-body text-center">
                                <p class="stat-label mb-1">Rata-rata THP Bersih</p>
                                <h4 class="fw-bold text-info mb-0" id="stAvgTHP">Rp —</h4>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row g-4">
                    <div class="col-lg-7">
                        <div class="card card-shell">
                            <div class="card-body">
                                <h6 class="fw-bold mb-3" style="font-size:.875rem"><i
                                        class="fa-solid fa-chart-line me-2 text-primary"></i>Tren THP & BPJS Sepanjang
                                    Tahun</h6>
                                <div class="chart-wrap"><canvas id="chartTrend"></canvas></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-5">
                        <div class="card card-shell">
                            <div class="card-body">
                                <h6 class="fw-bold mb-3" style="font-size:.875rem"><i
                                        class="fa-solid fa-chart-pie me-2 text-primary"></i>Breakdown Komponen BPJS
                                    Perusahaan</h6>
                                <div class="chart-wrap"><canvas id="chartBPJS"></canvas></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="card card-shell">
                            <div class="card-body">
                                <h6 class="fw-bold mb-3" style="font-size:.875rem"><i
                                        class="fa-solid fa-calculator me-2 text-primary"></i>RINGKASAN TOTAL BIAYA BPJS</h6>
                                <div class="table-responsive">
                                    <table class="table table-bordered mb-0" style="font-size:.85rem">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Keterangan</th>
                                                <th class="text-end">Nilai</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td><strong>Total Gaji Karyawan dalam 1 Tahun</strong></td>
                                                <td class="text-end fw-bold" id="ringkasanTotalGajiTahunan">Rp 0</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Batas Maksimal (maks 40%)</strong></td>
                                                <td class="text-end fw-bold" id="ringkasanBatasMaksimal">Rp 0</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Total BPJS TK & Kes ditanggung pers. Dalam 1 tahun</strong></td>
                                                <td class="text-end fw-bold" id="ringkasanTotalBPJSTahunan">Rp 0</td>
                                            </tr>
                                            <tr style="background:var(--pri-light);">
                                                <td><strong>Persentase</strong></td>
                                                <td class="text-end fw-bold" id="ringkasanPersentase">0%</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                
                                <div class="row g-3 mt-3">
                                    <div class="col-md-6">
                                        <div class="p-3 rounded" style="background:var(--gray-50);border:1px solid var(--gray-200)">
                                            <h6 class="fw-bold mb-2" style="font-size:.8rem;color:var(--gray-600)">Rincian Bulanan</h6>
                                            <div class="d-flex justify-content-between mb-2">
                                                <span>Total Gaji Pokok:</span>
                                                <strong id="ringkasanGajiBulanan">Rp 0</strong>
                                            </div>
                                            <div class="d-flex justify-content-between mb-2">
                                                <span>BPJS Perusahaan:</span>
                                                <strong class="text-success" id="ringkasanBPJSPerBulan">Rp 0</strong>
                                            </div>
                                            <div class="d-flex justify-content-between">
                                                <span>BPJS Karyawan:</span>
                                                <strong class="text-info" id="ringkasanBPJSKarBulan">Rp 0</strong>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="p-3 rounded" style="background:var(--gray-50);border:1px solid var(--gray-200)">
                                            <h6 class="fw-bold mb-2" style="font-size:.8rem;color:var(--gray-600)">Rincian Tahunan</h6>
                                            <div class="d-flex justify-content-between mb-2">
                                                <span>Total Gaji:</span>
                                                <strong id="ringkasanGajiTahunanDetail">Rp 0</strong>
                                            </div>
                                            <div class="d-flex justify-content-between mb-2">
                                                <span>Total BPJS Perusahaan:</span>
                                                <strong class="text-success" id="ringkasanBPJSPerTahun">Rp 0</strong>
                                            </div>
                                            <div class="d-flex justify-content-between">
                                                <span>Total BPJS Karyawan:</span>
                                                <strong class="text-info" id="ringkasanBPJSKarTahun">Rp 0</strong>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="tabAudit">
                <div class="card card-shell">
                    <div class="card-body">
                        <table id="auditTable" class="table" style="width:100%">
                            <thead class="table-light">
                                <tr>
                                    <th>Tanggal</th>
                                    <th>User</th>
                                    <th>Aksi</th>
                                    <th>Karyawan</th>
                                    <th>Periode</th>
                                    <th>THP Bersih</th>
                                    <th>Status</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="wizardModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content" style="border-radius:12px;border:none">
                <div class="modal-header modal-header-custom border-0">
                    <h5 class="modal-title fw-bold"><span id="wizardTitle">Buat Payroll Baru</span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body px-4 pt-4">
                    <div class="wizard-bar mb-4">
                        @foreach ([[1, 'Karyawan'], [2, 'Gaji & Tunjangan'], [3, 'Potongan & BPJS'], [4, 'Review']] as [$s, $label])
                            <div class="wz-step {{ $s === 1 ? 'active' : '' }}" data-step="{{ $s }}">
                                <div class="wz-circle">{{ $s }}</div>
                                <div class="wz-label">{{ $label }}</div>
                            </div>
                        @endforeach
                    </div>
                    <div class="wz-content" id="wz1">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <h6 class="fw-bold mb-3"><i class="fa-solid fa-user-check me-2 text-primary"></i>Pilih
                                    Karyawan & Periode</h6>
                                <div class="row g-3">
                                    <div class="col-md-12 mb-1">
                                        <label class="form-label">Karyawan <span class="text-danger">*</span></label>
                                        <select class="form-select" id="wKaryawan">
                                            <option value="">— Pilih Karyawan —</option>
                                            @foreach ($karyawans as $k)
                                                <option value="{{ $k->id }}" data-nama="{{ $k->nama_lengkap }}"
                                                    data-nip="{{ $k->nip }}">
                                                    {{ $k->nama_lengkap }}
                                                    ({{ $k->jabatan ?? '-' }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Bulan</label>
                                        <select class="form-select" id="wBulan">
                                            @foreach ([1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'] as $n => $nm)
                                                <option value="{{ $n }}"
                                                    {{ $n == $bulanSekarang ? 'selected' : '' }}>{{ $nm }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Tahun</label>
                                        <select class="form-select" id="wTahun">
                                            @for ($y = date('Y') - 2; $y <= date('Y') + 1; $y++)
                                                <option value="{{ $y }}"
                                                    {{ $y == $tahunSekarang ? 'selected' : '' }}>{{ $y }}
                                                </option>
                                            @endfor
                                        </select>
                                    </div>
                                </div>
                                <div id="wz1Alert" class="mt-3 d-none"></div>
                            </div>
                        </div>
                    </div>
                    <div class="wz-content d-none" id="wz2">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <h6 class="fw-bold mb-3"><i class="fa-solid fa-money-bill-wave me-2 text-primary"></i>Gaji
                                    Pokok</h6>
                                <div class="row g-3 mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Gaji Pokok <span
                                                class="badge-source badge bg-info ms-1">dari tabel karyawan</span></label>
                                        <input type="text" class="form-control mono-input readonly-val"
                                            id="wGajiPokok" readonly>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Salary BPJSTK <span
                                                class="badge-source badge bg-secondary ms-1">dapat diubah</span></label>
                                        <input type="text" class="form-control mono-input" id="wSalaryBPJSTK"
                                            placeholder="0" oninput="formatAndCalc(this)">
                                        <div class="form-text">Default = gaji pokok. Ubah jika berbeda.</div>
                                    </div>
                                </div>
                                <hr>
                                <h6 class="fw-bold mb-2"><i class="fa-solid fa-gift me-2 text-primary"></i>Tunjangan</h6>
                                <div id="tunjanganSourceInfo"></div>
                                <div class="row g-3" id="tunjanganFields"></div>
                                <div class="preview-box mt-4">
                                    <h6><i class="fa-solid fa-calculator me-2"></i>Perhitungan Sementara</h6>
                                    <div class="preview-row"><span>Gaji Pokok</span><span class="fw-bold"
                                            id="pvGaji">Rp 0</span></div>
                                    <div class="preview-row"><span>Total Tunjangan</span><span
                                            class="text-success fw-bold" id="pvTunj">Rp 0</span></div>
                                    <div class="preview-row preview-total"><span>THP Kotor</span><span id="pvTHP">Rp
                                            0</span></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="wz-content d-none" id="wz3">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <h6 class="fw-bold mb-2"><i class="fa-solid fa-shield-alt me-2 text-success"></i>BPJS
                                    (Otomatis Terhitung)</h6>
                                <div class="alert alert-info py-2 mb-3">
                                    <i class="fa-solid fa-info-circle me-1"></i>
                                    Dasar BPJSTK: <strong id="dasarBPJSTK">Rp 0</strong> &nbsp;|&nbsp;
                                    UMK Bandung: <strong>Rp 2.100.000</strong>
                                </div>
                                <div class="row g-3 mb-3">
                                    <div class="col-md-6">
                                        <div class="p-3 rounded"
                                            style="background:rgba(5,150,105,.05);border:1px solid var(--success-light)">
                                            <div class="fw-bold text-success mb-2"
                                                style="font-size:.8rem;text-transform:uppercase">Perusahaan</div>
                                            @foreach ([['b3JHTper', 'JHT 3.7%'], ['b3JKMper', 'JKM 0.3%'], ['b3JKKper', 'JKK 0.24%'], ['b3JPper', 'JP 2%'], ['b3KESper', 'Kes. 4%']] as [$id, $label])
                                                <div class="d-flex justify-content-between py-1" style="font-size:.82rem">
                                                    <span class="text-muted">{{ $label }}</span>
                                                    <span class="fw-semibold" id="{{ $id }}">Rp 0</span>
                                                </div>
                                            @endforeach
                                            <div class="d-flex justify-content-between pt-2 mt-1 border-top fw-bold text-success"
                                                style="font-size:.9rem">
                                                <span>Total</span><span id="b3TotalPer">Rp 0</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="p-3 rounded"
                                            style="background:rgba(2,132,199,.05);border:1px solid var(--info-light)">
                                            <div class="fw-bold text-info mb-2"
                                                style="font-size:.8rem;text-transform:uppercase">Karyawan (Potongan)</div>
                                            @foreach ([['b3JHTkar', 'JHT 2%'], ['b3JPkar', 'JP 1%'], ['b3KESkar', 'Kes. 1%']] as [$id, $label])
                                                <div class="d-flex justify-content-between py-1" style="font-size:.82rem">
                                                    <span class="text-muted">{{ $label }}</span>
                                                    <span class="fw-semibold" id="{{ $id }}">Rp 0</span>
                                                </div>
                                            @endforeach
                                            <div class="d-flex justify-content-between pt-2 mt-1 border-top fw-bold text-info"
                                                style="font-size:.9rem">
                                                <span>Total Potongan</span><span id="b3TotalKar">Rp 0</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <hr>
                                <h6 class="fw-bold mb-2"><i class="fa-solid fa-minus-circle me-2 text-danger"></i>Potongan
                                    Lainnya <small class="text-muted fw-normal">(input manual)</small></h6>
                                <div class="row g-3">
                                    @foreach ([['wPPh', 'PPh 21'], ['wKasbon', 'Kasbon / Pinjaman'], ['wDenda', 'Denda / Telat'], ['wPotLain', 'Potongan Lainnya']] as [$id, $label])
                                        <div class="col-md-6">
                                            <label class="form-label">{{ $label }}</label>
                                            <input type="text" class="form-control mono-input"
                                                id="{{ $id }}" placeholder="0" oninput="formatAndCalc(this)">
                                        </div>
                                    @endforeach
                                </div>
                                <div class="preview-box mt-4">
                                    <h6><i class="fa-solid fa-calculator me-2"></i>Ringkasan Potongan</h6>
                                    <div class="preview-row"><span>THP Kotor</span><span class="fw-bold"
                                            id="pv3THPKotor">Rp 0</span></div>
                                    <div class="preview-row"><span>Potongan BPJS Karyawan</span><span
                                            class="text-danger fw-bold" id="pv3BPJS">Rp 0</span></div>
                                    <div class="preview-row"><span>Potongan Lainnya</span><span
                                            class="text-warning fw-bold" id="pv3Lain">Rp 0</span></div>
                                    <div class="preview-row preview-total"><span>THP Bersih</span><span
                                            id="pv3THPBersih">Rp 0</span></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="wz-content d-none" id="wz4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <h6 class="fw-bold mb-3"><i
                                        class="fa-solid fa-clipboard-check me-2 text-primary"></i>Review Sebelum Simpan
                                </h6>
                                <div class="row g-2 mb-3">
                                    <div class="col-md-6">
                                        <div class="p-3 rounded bg-light"><small class="text-muted d-block"
                                                style="font-size:.7rem;text-transform:uppercase">Karyawan</small><strong
                                                id="rv4Nama">—</strong></div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="p-3 rounded bg-light"><small class="text-muted d-block"
                                                style="font-size:.7rem;text-transform:uppercase">Periode</small><strong
                                                id="rv4Periode">—</strong></div>
                                    </div>
                                </div>
                                <div class="detail-panel">
                                    <div class="row g-4">
                                        <div class="col-md-4">
                                            <div class="detail-section">
                                                <h6><i class="fa-solid fa-wallet" style="color:var(--pri)"></i>Pendapatan
                                                </h6>
                                                <div class="detail-item"><span>Gaji Pokok</span><span class="fw-bold"
                                                        id="rv4Gaji">Rp 0</span></div>
                                                <div class="detail-item"><span>Total Tunjangan</span><span
                                                        class="text-success" id="rv4Tunj">Rp 0</span></div>
                                                <div class="detail-item detail-total"><span>THP Kotor</span><span
                                                        id="rv4THPKotor">Rp 0</span></div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="detail-section">
                                                <h6><i class="fa-solid fa-building" style="color:var(--success)"></i>BPJS
                                                    Perusahaan</h6>
                                                <div class="detail-item"><span>BPJS Ketenagakerjaan</span><span
                                                        id="rv4BPJSTKper">Rp 0</span></div>
                                                <div class="detail-item"><span>BPJS Kesehatan</span><span
                                                        id="rv4BPJSKesper">Rp 0</span></div>
                                                <div class="detail-item detail-total"><span>Total</span><span
                                                        class="text-success" id="rv4TotalBPJSper">Rp 0</span></div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="detail-section">
                                                <h6><i class="fa-solid fa-user-shield"
                                                        style="color:var(--info)"></i>Potongan Karyawan</h6>
                                                <div class="detail-item"><span>BPJS Ketenagakerjaan</span><span
                                                        id="rv4BPJSTKkar">Rp 0</span></div>
                                                <div class="detail-item"><span>BPJS Kesehatan</span><span
                                                        id="rv4BPJSKeskar">Rp 0</span></div>
                                                <div class="detail-item"><span>Potongan Lainnya</span><span
                                                        id="rv4PotLain">Rp 0</span></div>
                                                <div class="detail-item detail-total"><span>Total Potongan</span><span
                                                        class="text-danger" id="rv4TotalPot">Rp 0</span></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mt-3">
                                        <div class="col-md-6 offset-md-6">
                                            <div class="p-3 rounded text-center"
                                                style="background:linear-gradient(135deg,rgba(79,70,229,.08),rgba(79,70,229,.04));border:2px solid rgba(79,70,229,.2)">
                                                <div class="text-muted"
                                                    style="font-size:.75rem;text-transform:uppercase;letter-spacing:.5px">
                                                    THP Bersih</div>
                                                <div class="fw-bold" style="font-size:1.6rem;color:var(--pri)"
                                                    id="rv4THPBersih">Rp 0</div>
                                                <div class="text-muted" style="font-size:.75rem">Total Biaya Perusahaan:
                                                    <strong id="rv4TotalBiaya">Rp 0</strong></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button class="btn btn-secondary" id="wBtnPrev" style="display:none" onclick="wzPrev()"><i
                            class="fa-solid fa-arrow-left me-1"></i>Sebelumnya</button>
                    <button class="btn btn-pri" id="wBtnNext" onclick="wzNext()">Lanjut <i
                            class="fa-solid fa-arrow-right ms-1"></i></button>
                    <button class="btn btn-success" id="wBtnSave" style="display:none" onclick="savePayroll()">Simpan Payroll</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="detailModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content" style="border-radius:12px;border:none">
                <div class="modal-header modal-header-custom border-0">
                    <h5 class="modal-title fw-bold"><i class="fa-solid fa-file-invoice me-2"></i>Detail Payroll</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="detailModalBody">
                    <div class="text-center py-4"><i class="fa-solid fa-spinner fa-spin fa-2x text-primary"></i></div>
                </div>
                <div class="modal-footer border-0">
                    <button class="btn btn-success btn-sm d-none" id="detailBtnApprove" onclick="approvePayroll()"><i
                            class="fa-solid fa-check me-1"></i>Approve</button>
                    <button class="btn btn-danger btn-sm d-none" id="detailBtnDelete" onclick="deletePayroll()"><i
                            class="fa-solid fa-trash me-1"></i>Hapus</button>
                    <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        const CSRF = document.querySelector('meta[name="csrf-token"]').content;
        const BASE = '{{ url('HR-dashboard/perhitungan-tunjangan') }}';
        const BULAN_NAMES = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September',
            'Oktober', 'November', 'Desember'
        ];
        const UMK = 2100000;

        let wzStep = 1;
        let wzEditId = null;
        let wzEmployee = null;
        let wzTunjReadonly = false;
        let activeDetailId = null;
        let dtTable = null;
        let dtAudit = null;
        let charts = {};

        $(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': CSRF
                },
                cache: false
            });

            initDataTable();
            initAuditTable();
            applyFilter();

            $(document).on('input', '.mono-input:not(.readonly-val)', function() {
                let raw = $(this).val().replace(/\D/g, '');
                if (raw) $(this).val(fmtNum(+raw));
                calcPreview();
            });

            $('#tabStatBtn').on('shown.bs.tab', loadStats);
            $('#wizardModal').on('hidden.bs.modal', resetWizard);
        });

        function initDataTable() {
            dtTable = $('#payrollTable').DataTable({
                data: [],
                columns: [{
                        data: null,
                        orderable: false,
                        searchable: false,
                        render: () => `<i class="fa-solid fa-chevron-right expand-btn"></i>`
                    },
                    {
                        data: 'nip'
                    },
                    {
                        data: null,
                        render: d =>
                            `<div class="fw-semibold">${d.nama || d.nama_lengkap || '-'}</div><small class="text-muted">${d.divisi || '-'}</small>`
                    },
                    {
                        data: null,
                        render: d =>
                            `<div>${d.jabatan || '-'}</div><small class="text-muted">${d.divisi || '-'}</small>`
                    },
                    {
                        data: 'status_aktif',
                        render: v => v === 'Aktif' ?
                            `<span class="badge" style="background:var(--success-light);color:var(--success);font-size:.72rem">Aktif</span>` :
                            `<span class="badge" style="background:var(--danger-light);color:var(--danger);font-size:.72rem">Nonaktif</span>`
                    },
                    {
                        data: 'gaji_pokok',
                        render: v => 'Rp ' + fmtNum(v)
                    },
                    {
                        data: null,
                        render: d => d.payroll ?
                            `<span class="fw-bold text-primary">Rp ${fmtNum(d.payroll.thp_bersih)}</span>` :
                            `<span class="text-muted">—</span>`
                    },
                    {
                        data: null,
                        render: d => d.payroll ? `Rp ${fmtNum(d.payroll.total_bpjs_perusahaan)}` :
                            `<span class="text-muted">—</span>`
                    },
                    {
                        data: null,
                        render: d => d.payroll ? statusBadge(d.payroll.status) :
                            `<span class="badge-status bs-none">Belum Ada</span>`
                    },
                    {
                        data: null,
                        orderable: false,
                        render: d => d.payroll ?
                            `<button class="btn btn-sm btn-outline-primary" onclick="showDetail(${d.payroll.id},event)" title="Detail"><i class="fa-solid fa-eye"></i></button>` :
                            `<button class="btn btn-sm btn-pri" onclick="openWizardFor(${d.karyawan_id},event)" title="Buat Payroll"><i class="fa-solid fa-plus"></i></button>`
                    },
                ],
                rowCallback: function(row, data) {
                    if (data.payroll) {
                        $(row).attr('data-payroll-id', data.payroll.id);
                        $(row).addClass('has-payroll');
                        $(row).off('click').on('click', function(e) {
                            if ($(e.target).closest('button').length) return;
                            toggleDetailRow($(row), data);
                        });
                    }
                },
                language: {
                    search: '',
                    searchPlaceholder: 'Cari nama / NIP...',
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
                    [2, 'asc']
                ],
                dom: '<"d-flex justify-content-between align-items-center mb-3"lf>rt<"d-flex justify-content-between align-items-center mt-3"ip>',
            });
        }

        function initAuditTable() {
            dtAudit = $('#auditTable').DataTable({
                data: [],
                columns: [{
                        data: null,
                        render: d => d.payroll ? d.payroll.created_at : '—'
                    },
                    {
                        data: null,
                        render: d => d.payroll ? d.payroll.created_by : '—'
                    },
                    {
                        data: null,
                        render: () => `<span class="audit-badge audit-create">CREATE</span>`
                    },
                    {
                        data: 'nama'
                    },
                    {
                        data: null,
                        render: d => d.payroll ? `${BULAN_NAMES[d.payroll.bulan]} ${d.payroll.tahun}` : '—'
                    },
                    {
                        data: null,
                        render: d => d.payroll ? `Rp ${fmtNum(d.payroll.thp_bersih)}` : '—'
                    },
                    {
                        data: null,
                        render: d => d.payroll ? statusBadge(d.payroll.status) : '—'
                    },
                    {
                        data: null,
                        orderable: false,
                        render: d => d.payroll ?
                            `<button class="btn btn-sm btn-outline-primary" onclick="showDetail(${d.payroll.id},event)"><i class="fa-solid fa-eye"></i></button>` :
                            ''
                    },
                ],
                language: {
                    search: '',
                    searchPlaceholder: 'Cari...',
                    lengthMenu: 'Tampilkan _MENU_',
                    info: '_START_–_END_ dari _TOTAL_',
                    paginate: {
                        previous: '‹',
                        next: '›'
                    }
                },
                order: [
                    [0, 'desc']
                ],
                pageLength: 10,
                dom: '<"d-flex justify-content-between mb-3"lf>rt<"d-flex justify-content-between mt-3"ip>',
            });
        }

        function exportExcel() {
            const params = new URLSearchParams({
                bulan: $('#fBulan').val(),
                tahun: $('#fTahun').val(),
                divisi: $('#fDivisi').val(),
                status: $('#fStatus').val()
            });
            window.location.href = `{{ route('HR.perhitungan-tunjangan.export.excel') }}?${params.toString()}`;
        }

        function exportPdf() {
            const params = new URLSearchParams({
                bulan: $('#fBulan').val(),
                tahun: $('#fTahun').val(),
                divisi: $('#fDivisi').val(),
                status: $('#fStatus').val()
            });
            window.location.href = `{{ route('HR.perhitungan-tunjangan.export.pdf') }}?${params.toString()}`;
        }

        function applyFilter() {
            const params = {
                bulan: $('#fBulan').val(),
                tahun: $('#fTahun').val(),
                divisi: $('#fDivisi').val(),
                status: $('#fStatus').val(),
            };

            $.ajax({
                    url: BASE + '/get-data',
                    method: 'GET',
                    data: params,
                    dataType: 'json'
                })
                .done(function(res) {
                    if (!res.success) {
                        console.warn('API error:', res);
                        return;
                    }

                    const data = Array.isArray(res.data) ? res.data : [];

                    dtTable.clear().rows.add(data).draw();
                    dtAudit.clear().rows.add(data.filter(d => d.payroll)).draw();

                    const sudah = data.filter(d => d.payroll).length;
                    const belum = data.filter(d => !d.payroll).length;
                    $('#statSudah').text(sudah);
                    $('#statBelum').text(belum);
                })
                .fail(function(xhr) {
                    console.error('AJAX Failed:', xhr);
                    toast('Gagal memuat data filter', 'error');
                });
        }

        function resetFilter() {
            $('#fBulan').val('{{ $bulanSekarang }}');
            $('#fTahun').val('{{ $tahunSekarang }}');
            $('#fDivisi, #fStatus').val('');
            applyFilter();
        }

        function toggleDetailRow($row, data) {
            const existingNext = $row.next('.detail-expanded-row');
            if (existingNext.length) {
                existingNext.remove();
                $row.removeClass('expanded');
                return;
            }
            $('.detail-expanded-row').remove();
            $('.expanded').removeClass('expanded');
            $row.addClass('expanded');
            const colCount = dtTable.columns().count();
            const detailHtml = renderDetailHtml(data);
            $row.after(
                `<tr class="detail-expanded-row"><td colspan="${colCount}" style="padding:0;background:var(--gray-50)">${detailHtml}</td></tr>`
                );
        }

        function renderTunjItems(detail) {
            if (!detail || !detail.length) {
                return '<div class="text-muted" style="font-size:.8rem">— Tidak ada tunjangan —</div>';
            }
            return detail.map(t =>
                `<div class="detail-item"><span>${t.nama}</span><span>Rp ${fmtNum(t.total)}</span></div>`
            ).join('');
        }

        function renderDetailHtml(data) {
            const p = data.payroll;
            if (!p) return `<div class="p-4 text-center text-muted">Belum ada payroll untuk karyawan ini.</div>`;
            const tunjDetail = (p.tunjangan && p.tunjangan.detail) ? p.tunjangan.detail : [];
            return `
                <div class="detail-panel m-2">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="detail-section">
                                <h6><i class="fa-solid fa-wallet" style="color:var(--pri)"></i>Pendapatan</h6>
                                <div class="detail-item"><span>Gaji Pokok</span><span class="fw-bold">Rp ${fmtNum(data.gaji_pokok)}</span></div>
                                ${renderTunjItems(tunjDetail)}
                                <div class="detail-item detail-total"><span>THP Kotor</span><span class="text-primary">Rp ${fmtNum(p.thp_kotor)}</span></div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="detail-section">
                                <h6><i class="fa-solid fa-shield-alt" style="color:var(--success)"></i>BPJS Perusahaan</h6>
                                <div class="detail-item"><span>Total BPJS Ketenagakerjaan</span><span>Rp ${fmtNum((p.total_bpjs_perusahaan||0)-(p.bpjs_kes_perusahaan||0))}</span></div>
                                <div class="detail-item"><span>BPJS Kesehatan (4%)</span><span>Rp ${fmtNum(p.bpjs_kes_perusahaan||0)}</span></div>
                                <div class="detail-item detail-total"><span>Total</span><span class="text-success">Rp ${fmtNum(p.total_bpjs_perusahaan||0)}</span></div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="detail-section">
                                <h6><i class="fa-solid fa-user-shield" style="color:var(--info)"></i>Potongan Karyawan</h6>
                                <div class="detail-item"><span>BPJS Ketenagakerjaan</span><span>Rp ${fmtNum((p.total_bpjs_karyawan||0)-(p.bpjs_kes_karyawan||0))}</span></div>
                                <div class="detail-item"><span>BPJS Kesehatan (1%)</span><span>Rp ${fmtNum(p.bpjs_kes_karyawan||0)}</span></div>
                                <div class="detail-item detail-total"><span>THP Bersih</span><span class="fw-bold text-primary">Rp ${fmtNum(p.thp_bersih)}</span></div>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex gap-2 mt-3">
                        <button class="btn btn-sm btn-outline-primary" onclick="showDetail(${p.id},event)"><i class="fa-solid fa-eye me-1"></i>Detail Lengkap</button>
                        ${['draft','calculated'].includes(p.status)?`<button class="btn btn-sm btn-success" onclick="approvePayrollId(${p.id},event)"><i class="fa-solid fa-check me-1"></i>Approve</button>`:''}
                        ${p.status!=='paid'?`<button class="btn btn-sm btn-outline-danger" onclick="deletePayrollId(${p.id},event)"><i class="fa-solid fa-trash me-1"></i>Hapus</button>`:''}
                    </div>
                </div>`;
        }

        function showDetail(id, e) {
            if (e) e.stopPropagation();
            activeDetailId = id;
            $('#detailModalBody').html(
                '<div class="text-center py-5"><i class="fa-solid fa-spinner fa-spin fa-2x text-primary"></i></div>');
            $('#detailBtnApprove, #detailBtnDelete').addClass('d-none');
            new bootstrap.Modal('#detailModal').show();

            $.get(BASE + '/' + id).done(function(res) {
                if (!res.success) return;
                const p = res.data;
                const tunjDetail = p.tunjangan_detail || [];
                if (['draft', 'calculated'].includes(p.status)) $('#detailBtnApprove').removeClass('d-none');
                if (p.status !== 'paid') $('#detailBtnDelete').removeClass('d-none');

                const tunjHtml = renderTunjItems(tunjDetail);

                $('#detailModalBody').html(`
                    <div class="row g-2 mb-3">
                        <div class="col-6"><div class="p-2 rounded bg-light"><small class="text-muted d-block" style="font-size:.7rem">Karyawan</small><strong>${p.karyawan?.nama_lengkap ?? '—'}</strong></div></div>
                        <div class="col-6"><div class="p-2 rounded bg-light"><small class="text-muted d-block" style="font-size:.7rem">Periode</small><strong>${BULAN_NAMES[p.bulan]} ${p.tahun}</strong></div></div>
                        <div class="col-6"><div class="p-2 rounded bg-light"><small class="text-muted d-block" style="font-size:.7rem">Status</small>${statusBadge(p.status)}</div></div>
                        <div class="col-6"><div class="p-2 rounded bg-light"><small class="text-muted d-block" style="font-size:.7rem">Dibuat oleh</small><strong>${p.created_by?.name ?? p.created_by_name ?? '—'}</strong></div></div>
                    </div>
                    <div class="detail-panel">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="detail-section">
                                    <h6><i class="fa-solid fa-wallet" style="color:var(--pri)"></i>Pendapatan</h6>
                                    <div class="detail-item"><span>Gaji Pokok</span><span class="fw-bold">Rp ${fmtNum(p.gaji_pokok)}</span></div>
                                    ${tunjHtml}
                                    <div class="detail-item detail-total"><span>THP Kotor</span><span>Rp ${fmtNum(p.thp_kotor)}</span></div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="detail-section">
                                    <h6><i class="fa-solid fa-building" style="color:var(--success)"></i>BPJS Perusahaan</h6>
                                    <div class="detail-item"><span>JHT (3.7%)</span><span>Rp ${fmtNum(p.jht_perusahaan)}</span></div>
                                    <div class="detail-item"><span>JKM (0.3%)</span><span>Rp ${fmtNum(p.jkm_perusahaan)}</span></div>
                                    <div class="detail-item"><span>JKK (0.24%)</span><span>Rp ${fmtNum(p.jkk_perusahaan)}</span></div>
                                    <div class="detail-item"><span>JP (2%)</span><span>Rp ${fmtNum(p.jp_perusahaan)}</span></div>
                                    <div class="detail-item"><span>BPJS Kes. (4%)</span><span>Rp ${fmtNum(p.bpjs_kes_perusahaan)}</span></div>
                                    <div class="detail-item detail-total"><span>Total</span><span class="text-success">Rp ${fmtNum(p.total_bpjs_perusahaan)}</span></div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="detail-section">
                                    <h6><i class="fa-solid fa-user-shield" style="color:var(--info)"></i>Potongan Karyawan</h6>
                                    <div class="detail-item"><span>JHT (2%)</span><span>Rp ${fmtNum(p.jht_karyawan)}</span></div>
                                    <div class="detail-item"><span>JP (1%)</span><span>Rp ${fmtNum(p.jp_karyawan)}</span></div>
                                    <div class="detail-item"><span>BPJS Kes. (1%)</span><span>Rp ${fmtNum(p.bpjs_kes_karyawan)}</span></div>
                                    ${p.potongan_pph21?`<div class="detail-item"><span>PPh 21</span><span>Rp ${fmtNum(p.potongan_pph21)}</span></div>`:''}
                                    ${p.potongan_kasbon?`<div class="detail-item"><span>Kasbon</span><span>Rp ${fmtNum(p.potongan_kasbon)}</span></div>`:''}
                                    ${p.potongan_denda?`<div class="detail-item"><span>Denda</span><span>Rp ${fmtNum(p.potongan_denda)}</span></div>`:''}
                                    <div class="detail-item detail-total"><span>THP Bersih</span><span class="fw-bold text-primary">Rp ${fmtNum(p.thp_bersih)}</span></div>
                                </div>
                            </div>
                        </div>
                    </div>
                `);
            });
        }

        function approvePayroll() {
            approvePayrollId(activeDetailId);
        }

        function deletePayroll() {
            deletePayrollId(activeDetailId);
        }

        function approvePayrollId(id, e) {
            if (e) e.stopPropagation();
            if (!confirm('Approve payroll ini?')) return;
            $.ajax({
                    url: BASE + '/' + id + '/approve',
                    method: 'POST'
                })
                .done(function(res) {
                    toast(res.message, 'success');
                    bootstrap.Modal.getInstance('#detailModal')?.hide();
                    applyFilter();
                })
                .fail(function(xhr) {
                    toast(xhr.responseJSON?.message || 'Gagal approve', 'error');
                });
        }

        function deletePayrollId(id, e) {
            if (e) e.stopPropagation();
            if (!confirm('Hapus payroll ini? Tindakan tidak bisa dibatalkan.')) return;
            $.ajax({
                    url: BASE + '/' + id,
                    method: 'DELETE'
                })
                .done(function(res) {
                    toast(res.message, 'success');
                    bootstrap.Modal.getInstance('#detailModal')?.hide();
                    applyFilter();
                })
                .fail(function(xhr) {
                    toast(xhr.responseJSON?.message || 'Gagal hapus', 'error');
                });
        }

        function openWizard() {
            resetWizard();
            new bootstrap.Modal('#wizardModal').show();
        }

        function openWizardFor(id, e) {
            if (e) e.stopPropagation();
            resetWizard();
            $('#wKaryawan').val(id);
            new bootstrap.Modal('#wizardModal').show();
        }

        function getTunjanganItems() {
            const items = [];
            $('#tunjanganFields .tunj-input').each(function() {
                items.push({
                    jenis_tunjangan_id: $(this).data('jenis-id') || null,
                    nama: $(this).data('nama'),
                    total: parseNum($(this).val())
                });
            });
            return items;
        }

        function getTunjTotal() {
            return getTunjanganItems().reduce((s, t) => s + t.total, 0);
        }

        function renderTunjanganFields(detail) {
            if (!detail || !detail.length) {
                $('#tunjanganFields').html('');
                return;
            }
            const html = detail.map(t => `
                <div class="col-md-6">
                    <label class="form-label">${t.nama}</label>
                    <input type="text" class="form-control mono-input readonly-val tunj-input" value="${fmtNum(t.total)}" readonly
                        data-jenis-id="${t.jenis_tunjangan_id ?? ''}" data-nama="${t.nama}">
                </div>`).join('');
            $('#tunjanganFields').html(html);
        }

        function wzNext() {
            if (wzStep === 1) {
                const empId = $('#wKaryawan').val();
                const bulan = $('#wBulan').val();
                const tahun = $('#wTahun').val();
                if (!empId) {
                    toast('Pilih karyawan terlebih dahulu!', 'error');
                    return;
                }

                $('#wBtnNext').prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin me-1"></i>Memuat...');
                $.get(BASE + '/karyawan-data', {
                        karyawan_id: empId,
                        bulan,
                        tahun
                    })
                    .done(function(res) {
                        if (!res.success) {
                            toast(res.message || 'Gagal memuat data', 'error');
                            return;
                        }
                        if (res.existing_payroll) {
                            $('#wz1Alert').removeClass('d-none').html(
                                `<div class="alert alert-warning mb-0"><i class="fa-solid fa-triangle-exclamation me-2"></i>Karyawan ini sudah memiliki payroll <strong>${res.existing_payroll.status.toUpperCase()}</strong> untuk periode ini.</div>`
                                );
                        } else {
                            $('#wz1Alert').addClass('d-none');
                        }

                        wzEmployee = res.karyawan;
                        wzTunjReadonly = res.has_tunjangan;
                        $('#wGajiPokok').val(fmtNum(wzEmployee.gaji_pokok));
                        $('#wSalaryBPJSTK').val(fmtNum(wzEmployee.gaji_pokok));

                        renderTunjanganFields(res.tunjangan_detail);

                        if (res.has_tunjangan) {
                            const items = res.tunjangan_detail.map(t =>
                                `<strong>${t.nama}</strong>: Rp ${fmtNum(t.total)}`).join(' | ');
                            $('#tunjanganSourceInfo').html(
                                `<div class="tunjangan-source-info"><i class="fa-solid fa-circle-check text-info me-2"></i>Tunjangan diambil dari data yang sudah diapprove: ${items}</div>`
                                );
                        } else {
                            $('#tunjanganSourceInfo').html(
                                `<div class="tunjangan-empty-info"><i class="fa-solid fa-triangle-exclamation text-warning me-2"></i>Tidak ada data tunjangan approved untuk periode ini.</div>`
                                );
                        }
                        calcPreview();
                        goStep(2);
                    })
                    .fail(function(xhr) {
                        console.error('Gagal memuat data karyawan:', xhr);
                        toast('Gagal memuat data karyawan', 'error');
                    })
                    .always(function() {
                        $('#wBtnNext').prop('disabled', false).html(
                            'Lanjut <i class="fa-solid fa-arrow-right ms-1"></i>');
                    });
                return;
            }
            if (wzStep === 2) {
                if (!parseNum($('#wSalaryBPJSTK').val())) {
                    toast('Salary BPJSTK wajib diisi!', 'error');
                    return;
                }
                calcBPJS();
                goStep(3);
                return;
            }
            if (wzStep === 3) {
                updateReview();
                goStep(4);
                return;
            }
        }

        function wzPrev() {
            if (wzStep > 1) goStep(wzStep - 1);
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
            $('#wBtnNext').toggle(n < 4);
            $('#wBtnSave').toggle(n === 4);
        }

        function resetWizard() {
            wzStep = 1;
            wzEditId = null;
            wzEmployee = null;
            wzTunjReadonly = false;
            goStep(1);
            $('#wKaryawan').val('');
            $('#wz1Alert').addClass('d-none');
            $('#wSalaryBPJSTK,#wPPh,#wKasbon,#wDenda,#wPotLain').val('');
            $('#tunjanganFields').html('');
            $('#tunjanganSourceInfo').html('');
            $('#wizardTitle').text('Buat Payroll Baru');
        }

        function calcPreview() {
            const gaji = wzEmployee ? wzEmployee.gaji_pokok : 0;
            const tunj = getTunjTotal();
            const thp = gaji + tunj;
            $('#pvGaji').text('Rp ' + fmtNum(gaji));
            $('#pvTunj').text('Rp ' + fmtNum(tunj));
            $('#pvTHP').text('Rp ' + fmtNum(thp));
        }

        function calcBPJS() {
            const salary = parseNum($('#wSalaryBPJSTK').val()) || (wzEmployee?.gaji_pokok ?? 0);
            const umk = wzEmployee?.umk_bandung ?? UMK;
            const jhtPer = Math.round(salary * 3.7 / 100),
                jkmPer = Math.round(salary * 0.3 / 100),
                jkkPer = Math.round(salary * 0.24 / 100),
                jpPer = Math.round(salary * 2 / 100),
                kesPer = Math.round(umk * 4 / 100);
            const totalPer = jhtPer + jkmPer + jkkPer + jpPer + kesPer;
            const jhtKar = Math.round(salary * 2 / 100),
                jpKar = Math.round(salary * 1 / 100),
                kesKar = Math.round(umk * 1 / 100);
            const totalKar = jhtKar + jpKar + kesKar;

            $('#dasarBPJSTK').text('Rp ' + fmtNum(salary));
            $('#b3JHTper').text('Rp ' + fmtNum(jhtPer));
            $('#b3JKMper').text('Rp ' + fmtNum(jkmPer));
            $('#b3JKKper').text('Rp ' + fmtNum(jkkPer));
            $('#b3JPper').text('Rp ' + fmtNum(jpPer));
            $('#b3KESper').text('Rp ' + fmtNum(kesPer));
            $('#b3TotalPer').text('Rp ' + fmtNum(totalPer));
            $('#b3JHTkar').text('Rp ' + fmtNum(jhtKar));
            $('#b3JPkar').text('Rp ' + fmtNum(jpKar));
            $('#b3KESkar').text('Rp ' + fmtNum(kesKar));
            $('#b3TotalKar').text('Rp ' + fmtNum(totalKar));

            const gaji = wzEmployee?.gaji_pokok ?? 0;
            const tunj = getTunjTotal();
            const thpKotor = gaji + tunj;
            const potLain = ['#wPPh', '#wKasbon', '#wDenda', '#wPotLain'].reduce((s, id) => s + parseNum($(id).val()), 0);
            const thpBersih = thpKotor - totalKar - potLain;

            $('#pv3THPKotor').text('Rp ' + fmtNum(thpKotor));
            $('#pv3BPJS').text('Rp ' + fmtNum(totalKar));
            $('#pv3Lain').text('Rp ' + fmtNum(potLain));
            $('#pv3THPBersih').text('Rp ' + fmtNum(thpBersih));

            $(document).off('input.bpjs').on('input.bpjs', '#wPPh,#wKasbon,#wDenda,#wPotLain', function() {
                const pl = ['#wPPh', '#wKasbon', '#wDenda', '#wPotLain'].reduce((s, id) => s + parseNum($(id)
                .val()), 0);
                const net = thpKotor - totalKar - pl;
                $('#pv3Lain').text('Rp ' + fmtNum(pl));
                $('#pv3THPBersih').text('Rp ' + fmtNum(net));
            });
        }

        function updateReview() {
            if (!wzEmployee) return;
            const bulan = $('#wBulan').val(),
                tahun = $('#wTahun').val();
            const salary = parseNum($('#wSalaryBPJSTK').val()) || wzEmployee.gaji_pokok;
            const umk = wzEmployee.umk_bandung ?? UMK;
            const gaji = wzEmployee.gaji_pokok;
            const tunj = getTunjTotal();
            const thpKotor = gaji + tunj;
            const jhtPer = Math.round(salary * 3.7 / 100),
                jkmPer = Math.round(salary * 0.3 / 100),
                jkkPer = Math.round(salary * 0.24 / 100),
                jpPer = Math.round(salary * 2 / 100),
                kesPer = Math.round(umk * 4 / 100);
            const totalBPJSPer = jhtPer + jkmPer + jkkPer + jpPer + kesPer;
            const jhtKar = Math.round(salary * 2 / 100),
                jpKar = Math.round(salary * 1 / 100),
                kesKar = Math.round(umk * 1 / 100);
            const totalBPJSKar = jhtKar + jpKar + kesKar;
            const potLain = ['#wPPh', '#wKasbon', '#wDenda', '#wPotLain'].reduce((s, id) => s + parseNum($(id).val()), 0);
            const thpBersih = thpKotor - totalBPJSKar - potLain;
            const totalBiaya = thpKotor + totalBPJSPer;

            $('#rv4Nama').text(`${wzEmployee.nama} (${wzEmployee.nip})`);
            $('#rv4Periode').text(`${BULAN_NAMES[+bulan]} ${tahun}`);
            $('#rv4Gaji').text('Rp ' + fmtNum(gaji));
            $('#rv4Tunj').text('Rp ' + fmtNum(tunj));
            $('#rv4THPKotor').text('Rp ' + fmtNum(thpKotor));
            $('#rv4BPJSTKper').text('Rp ' + fmtNum(jhtPer + jkmPer + jkkPer + jpPer));
            $('#rv4BPJSKesper').text('Rp ' + fmtNum(kesPer));
            $('#rv4TotalBPJSper').text('Rp ' + fmtNum(totalBPJSPer));
            $('#rv4BPJSTKkar').text('Rp ' + fmtNum(jhtKar + jpKar));
            $('#rv4BPJSKeskar').text('Rp ' + fmtNum(kesKar));
            $('#rv4PotLain').text('Rp ' + fmtNum(potLain));
            $('#rv4TotalPot').text('Rp ' + fmtNum(totalBPJSKar + potLain));
            $('#rv4THPBersih').text('Rp ' + fmtNum(thpBersih));
            $('#rv4TotalBiaya').text('Rp ' + fmtNum(totalBiaya));
        }

        function formatAndCalc(el) {
            let raw = $(el).val().replace(/\D/g, '');
            if (raw) $(el).val(fmtNum(+raw));
            calcBPJS();
        }

        function savePayroll() {
            if (!wzEmployee) return;
            const payload = {
                karyawan_id: wzEmployee.id,
                bulan: +$('#wBulan').val(),
                tahun: +$('#wTahun').val(),
                gaji_pokok: wzEmployee.gaji_pokok,
                salary_bpjstk: parseNum($('#wSalaryBPJSTK').val()) || wzEmployee.gaji_pokok,
                umk_bandung: wzEmployee.umk_bandung ?? UMK,
                tunjangan: getTunjanganItems(),
                'potongan[pph21]': parseNum($('#wPPh').val()),
                'potongan[kasbon]': parseNum($('#wKasbon').val()),
                'potongan[denda]': parseNum($('#wDenda').val()),
                'potongan[lain]': parseNum($('#wPotLain').val()),
            };
            const url = wzEditId ? BASE + '/' + wzEditId : BASE;
            const method = wzEditId ? 'PUT' : 'POST';
            $('#wBtnSave').prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin me-1"></i>Menyimpan...');
            $.ajax({
                    url,
                    method,
                    data: payload,
                    dataType: 'json'
                })
                .done(function(res) {
                    bootstrap.Modal.getInstance('#wizardModal')?.hide();
                    toast(res.message, 'success');
                    applyFilter();
                })
                .fail(function(xhr) {
                    toast(xhr.responseJSON?.message || 'Terjadi kesalahan!', 'error');
                })
                .always(function() {
                    $('#wBtnSave').prop('disabled', false).html('<i class="fa-solid fa-save me-1"></i>Simpan Payroll');
                });
        }

        function loadStats() {
            const params = {
                bulan: $('#fBulan').val(),
                tahun: $('#fTahun').val(),
                divisi: $('#fDivisi').val()
            };
            $.get(BASE + '/stats', params).done(function(res) {
                if (!res.success) return;
                $('#stTHPKotor').text('Rp ' + fmtNum(res.total_payroll));
                $('#stBPJSPer').text('Rp ' + fmtNum(res.total_bpjs_perusahaan));
                $('#stBPJSKar').text('Rp ' + fmtNum(res.total_bpjs_karyawan));
                $('#stAvgTHP').text('Rp ' + fmtNum(res.avg_thp));
                initCharts(res);
            });
        }

        function initCharts(res) {
            Object.values(charts).forEach(c => c?.destroy());
            const trendLabels = (res.trend || []).map(t => BULAN_NAMES[t.bulan]);
            charts.trend = new Chart(document.getElementById('chartTrend'), {
                type: 'line',
                data: {
                    labels: trendLabels,
                    datasets: [{
                            label: 'Total THP',
                            data: (res.trend || []).map(t => t.total_thp),
                            borderColor: '#4f46e5',
                            backgroundColor: 'rgba(79,70,229,.1)',
                            tension: .4,
                            fill: true,
                            pointRadius: 4
                        },
                        {
                            label: 'BPJS Perusahaan',
                            data: (res.trend || []).map(t => t.total_bpjs_per),
                            borderColor: '#059669',
                            backgroundColor: 'rgba(5,150,105,.07)',
                            tension: .4,
                            fill: true,
                            pointRadius: 4
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: v => 'Rp ' + (v / 1e6).toFixed(1) + 'Jt'
                            }
                        }
                    }
                }
            });

            const bd = res.bpjs_breakdown || {};
            charts.bpjs = new Chart(document.getElementById('chartBPJS'), {
                type: 'doughnut',
                data: {
                    labels: ['JHT Perusahaan', 'JKM', 'JKK', 'JP Perusahaan', 'BPJS Kes.'],
                    datasets: [{
                        data: [bd.jht_perusahaan, bd.jkm_perusahaan, bd.jkk_perusahaan, bd.jp_perusahaan, bd
                            .kes_perusahaan
                        ],
                        backgroundColor: ['#4f46e5', '#06b6d4', '#10b981', '#f59e0b', '#ef4444'],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                boxWidth: 12,
                                font: {
                                    size: 11
                                }
                            }
                        }
                    }
                }
            });

            const divisiKeys = Object.keys(res.divisi_stats || {});
            if (res.ringkasan_biaya) {
                const rb = res.ringkasan_biaya;
                $('#ringkasanTotalGajiTahunan').text('Rp ' + fmtNum(rb.total_gaji_tahunan));
                $('#ringkasanBatasMaksimal').text('Rp ' + fmtNum(rb.batas_maksimal));
                $('#ringkasanTotalBPJSTahunan').text('Rp ' + fmtNum(rb.total_bpjs_tahunan));
                $('#ringkasanPersentase').text(rb.persentase.toFixed(2) + '%');
                
                $('#ringkasanGajiBulanan').text('Rp ' + fmtNum(rb.total_gaji_bulanan));
                $('#ringkasanBPJSPerBulan').text('Rp ' + fmtNum(rb.total_ditanggung_perusahaan));
                $('#ringkasanBPJSKarBulan').text('Rp ' + fmtNum(rb.total_ditanggung_karyawan));
                
                $('#ringkasanGajiTahunanDetail').text('Rp ' + fmtNum(rb.total_gaji_tahunan));
                $('#ringkasanBPJSPerTahun').text('Rp ' + fmtNum(rb.total_ditanggung_perusahaan * 12));
                $('#ringkasanBPJSKarTahun').text('Rp ' + fmtNum(rb.total_ditanggung_karyawan * 12));
            }
        }

        function fmtNum(n) {
            return Math.round(n || 0).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        }

        function parseNum(s) {
            if (!s) return 0;
            return parseInt(s.toString().replace(/\./g, '').replace(/,/g, '')) || 0;
        }

        function statusBadge(s) {
            if (!s) return `<span class="badge-status bs-none">BELUM ADA</span>`;
            const map = {
                draft: 'bs-draft',
                calculated: 'bs-calculated',
                approved: 'bs-approved',
                paid: 'bs-paid',
                cancelled: 'bs-cancelled'
            };
            const label = {
                draft: 'DRAFT',
                calculated: 'CALCULATED',
                approved: 'APPROVED',
                paid: 'PAID',
                cancelled: 'CANCELLED'
            };
            return `<span class="badge-status ${map[s] || 'bs-draft'}">${label[s] || s.toString().toUpperCase()}</span>`;
        }

        function toast(msg, type = 'success') {
            const el = $(
                `<div class="toast-msg toast-${type}"><i class="fa-solid ${type==='success'?'fa-check-circle':'fa-times-circle'}"></i>${msg}</div>`
                );
            $('#toast-container').append(el);
            setTimeout(() => el.fadeOut(300, () => el.remove()), 3500);
        }
    </script>
@endsection