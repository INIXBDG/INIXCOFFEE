@extends('layout_HR.app')

@section('title', 'Bonus Tahunan - INIX HR')

@section('content_HR')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css" rel="stylesheet">
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
            --purple: #7c3aed;
            --purple-light: #ede9fe;
            --gray-50: #f9fafb;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-400: #9ca3af;
            --gray-600: #4b5563;
            --gray-700: #374151;
            --gray-900: #111827;
            --radius: 12px;
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.07), 0 2px 4px -2px rgba(0, 0, 0, 0.05);
            --shadow-lg: 0 10px 25px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -4px rgba(0, 0, 0, 0.05);
        }

        .page-header { margin-bottom: 1.5rem; }
        .page-title { font-size: 1.65rem; font-weight: 800; color: var(--gray-900); letter-spacing: -0.02em; }
        .page-sub { color: var(--gray-400); font-size: 0.875rem; }
        .stat-card { border: none; border-radius: var(--radius); box-shadow: var(--shadow); transition: transform 0.2s, box-shadow 0.2s; overflow: hidden; background: #fff; height: 100%; }
        .stat-card:hover { transform: translateY(-3px); box-shadow: var(--shadow-lg); }
        .stat-icon { width: 48px; height: 48px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.35rem; color: #fff; flex-shrink: 0; }
        .stat-value { font-size: 1.5rem; font-weight: 800; color: var(--gray-900); margin: 0.35rem 0 0.15rem; }
        .stat-value.sm { font-size: 1.1rem; }
        .stat-label { color: var(--gray-400); font-size: 0.78rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin: 0; }
        .card-shell { border: none; border-radius: var(--radius); box-shadow: var(--shadow); background: #fff; }
        #bonusTable { border-collapse: separate; border-spacing: 0; width: 100%; }
        #bonusTable thead th { font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.6px; color: var(--gray-600); background: var(--gray-50); border-bottom: 2px solid var(--gray-200) !important; border-top: none !important; padding: 0.85rem 1rem; vertical-align: middle; white-space: nowrap; }
        #bonusTable tbody tr { transition: background 0.15s; }
        #bonusTable tbody tr:hover { background: #f8faff !important; }
        #bonusTable tbody tr.row-tidak-berhak { background: #fafafa; }
        #bonusTable tbody tr.row-tidak-berhak td { opacity: 0.6; }
        #bonusTable tbody td { vertical-align: middle; font-size: 0.875rem; border-bottom: 1px solid var(--gray-100) !important; padding: 0.85rem 1rem; }
        .badge-status { padding: 0.32rem 0.7rem; border-radius: 20px; font-size: 0.72rem; font-weight: 700; display: inline-block; }
        .status-tetap { background: var(--success-light); color: var(--success); }
        .status-kontrak { background: var(--info-light); color: var(--info); }
        .status-probation { background: var(--warning-light); color: var(--warning); }
        .status-resign { background: var(--danger-light); color: var(--danger); }
        .badge-tidak-berhak { background: var(--danger-light); color: var(--danger); border-radius: 20px; font-size: 0.68rem; font-weight: 700; padding: 0.2rem 0.55rem; display: inline-block; margin-top: 0.25rem; }
        .badge-diedit { background: var(--purple-light); color: var(--purple); border-radius: 20px; font-size: 0.65rem; font-weight: 700; padding: 0.15rem 0.5rem; display: inline-block; margin-top: 0.2rem; }
        .bank-badge { background: #fff8e6; color: #b45309; border: 1px solid #fde68a; border-radius: 6px; padding: 0.2rem 0.55rem; font-family: 'SFMono-Regular', Consolas, monospace; font-size: 0.8rem; font-weight: 600; display: inline-flex; align-items: center; gap: 0.35rem; }
        .skema-select { font-size: 0.8rem; font-weight: 600; padding: 0.32rem 0.6rem; border-radius: 8px; border: 1.5px solid var(--gray-200); transition: all 0.2s; cursor: pointer; }
        .skema-select:focus { border-color: var(--pri); box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.15); outline: none; }
        .skema-select:disabled { cursor: not-allowed; }
        .skema-standar { background-color: #f3f4f6; color: #374151; }
        .skema-2x { background-color: #e0e7ff; color: #3730a3; font-weight: 700; border-color: #c7d2fe; }
        .skema-4x { background-color: #ede9fe; color: #5b21b6; font-weight: 800; border-color: #ddd6fe; }
        .skema-custom { background-color: #fef3c7; color: #92400e; border-color: #fde68a; }
        .bonus-amount { font-size: 0.95rem; font-weight: 800; color: var(--pri); letter-spacing: -0.01em; }
        .export-option { border: 1.5px solid var(--gray-200); border-radius: 10px; padding: 0.75rem 0.9rem; cursor: pointer; transition: all 0.15s; display: block; margin-bottom: 0.5rem; }
        .export-option:hover { border-color: var(--pri); background: var(--pri-light); }
        .export-option input { margin-right: 0.5rem; }
        .export-option.active { border-color: var(--pri); background: var(--pri-light); }
        .export-option .opt-title { font-weight: 700; font-size: 0.88rem; color: var(--gray-900); }
        .export-option .opt-desc { font-size: 0.75rem; color: var(--gray-600); display: block; margin-left: 1.5rem; }
        #toast-container { position: fixed; top: 1.5rem; right: 1.5rem; z-index: 99999; display: flex; flex-direction: column; gap: 0.5rem; }
        .toast-msg { padding: 0.85rem 1.25rem; border-radius: 10px; font-weight: 600; font-size: 0.875rem; color: #fff; box-shadow: var(--shadow-lg); display: flex; align-items: center; gap: 0.6rem; animation: slideIn 0.25s ease-out; }
        .toast-success { background: #059669; }
        .toast-error { background: #dc2626; }
        @keyframes slideIn { from { opacity: 0; transform: translateX(50px); } to { opacity: 1; transform: none; } }
        .bonus-tabs { border-bottom: 2px solid var(--gray-200); gap: 0.25rem; margin-bottom: 1.25rem; }
        .bonus-tabs .nav-link { border: none; border-bottom: 3px solid transparent; margin-bottom: -2px; color: var(--gray-600); font-weight: 600; font-size: 0.9rem; padding: 0.7rem 1.25rem; display: inline-flex; align-items: center; gap: 0.45rem; background: transparent; }
        .bonus-tabs .nav-link:hover { color: var(--pri); }
        .bonus-tabs .nav-link.active { color: var(--pri); border-bottom-color: var(--pri); background: transparent; }
        .dt-wrap .dt-top, .dt-wrap .dt-bottom { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 0.75rem; padding: 0.85rem 1.1rem; }
        .dt-wrap .dt-top { border-bottom: 1px solid var(--gray-100); }
        .dt-wrap .dt-bottom { border-top: 1px solid var(--gray-100); }
        .dt-wrap div.dataTables_wrapper div.dataTables_length label, .dt-wrap div.dataTables_wrapper div.dataTables_info { font-size: 0.8rem; color: var(--gray-600); margin: 0; padding: 0; }
        .dt-wrap div.dataTables_wrapper div.dataTables_length select { width: auto; display: inline-block; }
        .dt-wrap div.dataTables_wrapper div.dataTables_paginate { margin: 0; }
        .dt-wrap .page-link { font-size: 0.8rem; font-weight: 600; }
        .dt-wrap .page-item.active .page-link { background: var(--pri); border-color: var(--pri); }
        .section-title { font-size: 0.95rem; font-weight: 700; color: var(--gray-900); margin: 0; display: flex; align-items: center; gap: 0.45rem; }
        .section-sub { font-size: 0.75rem; color: var(--gray-600); margin: 0; }
        .chart-box { position: relative; height: 290px; }
        .mini-table th { font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.4px; color: var(--gray-600); background: var(--gray-50); font-weight: 700; white-space: nowrap; }
        .mini-table td, .mini-table th { padding: 0.55rem 0.75rem; font-size: 0.8rem; vertical-align: middle; }
        .bar-track { height: 6px; background: var(--gray-100); border-radius: 20px; overflow: hidden; min-width: 70px; }
        .bar-fill { height: 100%; background: linear-gradient(90deg, var(--pri), #818cf8); border-radius: 20px; }
        .info-tile { border: 1px solid var(--gray-200); border-radius: 10px; padding: 0.85rem 1rem; background: var(--gray-50); height: 100%; }
        .info-tile .k { font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.5px; color: var(--gray-600); font-weight: 700; }
        .info-tile .v { font-size: 1rem; font-weight: 700; color: var(--gray-900); }
        .attention-card { border: 1px solid var(--gray-200); border-radius: 10px; padding: 0.9rem 1rem; height: 100%; background: #fff; }
        .rank-no { width: 24px; height: 24px; border-radius: 50%; background: var(--pri-light); color: var(--pri); font-size: 0.72rem; font-weight: 700; display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .accordion-button:not(.collapsed) { background: var(--pri-light); color: var(--pri-dark); }
        .accordion-button { font-size: 0.85rem; font-weight: 600; }
        .accordion-body { font-size: 0.82rem; color: var(--gray-700); }
        .rule-banner { background: linear-gradient(135deg, rgba(79, 70, 229, 0.05) 0%, rgba(99, 102, 241, 0.08) 100%); border: 1.5px solid rgba(79, 70, 229, 0.2); border-radius: var(--radius); padding: 1.15rem 1.4rem; }
        .rule-badge { background: #ffffff; border: 1px solid var(--gray-200); padding: 0.35rem 0.75rem; border-radius: 8px; font-size: 0.8rem; font-weight: 600; color: var(--gray-700); display: inline-flex; align-items: center; gap: 0.4rem; }
    </style>

    <div id="toast-container"></div>

    <div class="container-fluid px-3 px-md-4 py-4">
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between page-header gap-3">
            <div>
                <h1 class="page-title mb-1">
                    <i class="fa-solid fa-gift text-primary me-2"></i>Bonus Tahunan Karyawan
                </h1>
                <p class="page-sub mb-0">
                    Pencairan: <span class="badge bg-primary px-2 py-1 fs-6">Desember <span
                            id="labelTahun">{{ $tahunSekarang }}</span></span>
                    <span class="mx-2">•</span>
                    <span class="text-muted"><i class="fa-regular fa-calendar-check me-1"></i>Periode Kerja: <strong>Januari
                            - Desember <span id="labelTahunBonus">{{ $tahunSekarang - 1 }}</span></strong></span>
                    <span class="mx-2">•</span>
                    <span class="text-muted">Cut-off: <strong>31 Desember <span
                                id="labelCutoffTahun">{{ $tahunSekarang - 1 }}</span></strong></span>
                </p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <div class="btn-group btn-group-sm" role="group" aria-label="Mode tampilan angka">
                    <button type="button" class="btn btn-outline-primary" id="btnDesimalOn"
                        onclick="setModeDesimal(true)" title="Tampilkan data asli dengan 2 desimal">
                        <i class="fa-solid fa-eye me-1"></i>Real (,00)
                    </button>
                    <button type="button" class="btn btn-outline-primary" id="btnDesimalOff"
                        onclick="setModeDesimal(false)" title="Hapus desimal tanpa pembulatan">
                        <i class="fa-solid fa-scissors me-1"></i>Tanpa Desimal
                    </button>
                </div>
                <button type="button" class="btn btn-outline-secondary btn-sm px-3" onclick="recalculateAll()"
                    title="Hitung Ulang & Simpan Semua Data">
                    <i class="fa-solid fa-rotate me-1"></i>Hitung Ulang
                </button>
                <button type="button" class="btn btn-outline-success btn-sm px-3" onclick="exportExcel()">
                    <i class="fa-solid fa-file-excel me-1"></i>Export Excel
                </button>
                <button type="button" class="btn btn-danger btn-sm px-3" onclick="openExportPdfModal()">
                    <i class="fa-solid fa-file-pdf me-1"></i>Export PDF Surat
                </button>
            </div>
        </div>

        <div class="card card-shell mb-3">
            <div class="card-body py-3">
                <div class="row g-2 align-items-center">
                    <div class="col-12 col-md-auto">
                        <span class="fw-bold text-primary small d-flex align-items-center gap-1">
                            <i class="fa-solid fa-calendar-days"></i> Tahun Pencairan:
                        </span>
                    </div>
                    <div class="col-8 col-md-auto">
                        <label class="visually-hidden" for="filterTahun">Tahun Pencairan</label>
                        <select id="filterTahun" class="form-select form-select-sm" style="min-width: 170px;"
                            onchange="loadData()">
                            @for ($y = date('Y') - 2; $y <= date('Y') + 1; $y++)
                                <option value="{{ $y }}" {{ $y == $tahunSekarang ? 'selected' : '' }}>Pencairan
                                    {{ $y }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-4 col-md-auto">
                        <span class="badge bg-light text-secondary border" id="loadingBadge" style="display:none;">
                            <span class="spinner-border spinner-border-sm text-primary me-1" role="status"></span>Memuat...
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <ul class="nav bonus-tabs" id="bonusTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="tab-table-btn" data-bs-toggle="tab" data-bs-target="#tab-table"
                    type="button" role="tab" aria-controls="tab-table" aria-selected="true">
                    <i class="fa-solid fa-table-list"></i> Tabel Data Bonus
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="tab-analitik-btn" data-bs-toggle="tab" data-bs-target="#tab-analitik"
                    type="button" role="tab" aria-controls="tab-analitik" aria-selected="false">
                    <i class="fa-solid fa-chart-line"></i> Dashboard & Analitik Bonus Tahunan
                </button>
            </li>
        </ul>

        <div class="tab-content">

            <div class="tab-pane fade show active" id="tab-table" role="tabpanel" aria-labelledby="tab-table-btn"
                tabindex="0">

                <div class="card card-shell mb-3">
                    <div class="card-body py-3">
                        <div class="row g-2 align-items-center">
                            <div class="col-12 col-md-auto">
                                <span class="fw-bold text-primary small d-flex align-items-center gap-1">
                                    <i class="fa-solid fa-filter"></i> Filter Data:
                                </span>
                            </div>

                            <div class="col-6 col-md-auto">
                                <label class="visually-hidden" for="filterDivisi">Divisi</label>
                                <select id="filterDivisi" class="form-select form-select-sm" style="min-width: 140px;"
                                    onchange="applyFilters()">
                                    <option value="all">Semua Divisi</option>
                                    @foreach ($divisiList as $div)
                                        <option value="{{ $div }}">{{ $div }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-6 col-md-auto">
                                <label class="visually-hidden" for="filterStatus">Status</label>
                                <select id="filterStatus" class="form-select form-select-sm" style="min-width: 140px;"
                                    onchange="applyFilters()">
                                    <option value="all">Semua Status</option>
                                    <option value="Karyawan Tetap">Karyawan Tetap</option>
                                    <option value="Karyawan Kontrak">Karyawan Kontrak</option>
                                    <option value="Karyawan Probation">Karyawan Probation</option>
                                </select>
                            </div>

                            <div class="col-6 col-md-auto">
                                <label class="visually-hidden" for="filterSkema">Skema</label>
                                <select id="filterSkema" class="form-select form-select-sm" style="min-width: 140px;"
                                    onchange="applyFilters()">
                                    <option value="all">Semua Skema</option>
                                    <option value="standar">Standar (Prorata)</option>
                                    <option value="2x">2x Basic Salary</option>
                                    <option value="4x">4x Basic Salary</option>
                                    <option value="custom">Custom</option>
                                </select>
                            </div>

                            <div class="col-6 col-md-auto">
                                <label class="visually-hidden" for="filterIkut">Keikutsertaan</label>
                                <select id="filterIkut" class="form-select form-select-sm" style="min-width: 150px;" onchange="applyFilters()">
                                    <option value="all">Semua Keikutsertaan</option>
                                    <option value="ikut">Ikut Dihitung</option>
                                    <option value="keluar">Dikecualikan</option>
                                </select>
                            </div>

                            <div class="col-12 col-md">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-white border-end-0 text-muted"><i
                                            class="fa-solid fa-magnifying-glass"></i></span>
                                    <input type="text" id="searchKeyword" class="form-control border-start-0"
                                        placeholder="Cari Nama, NIP, Jabatan, Divisi, Rekening..." oninput="applyFilters()">
                                </div>
                            </div>

                            <div class="col-auto">
                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="resetFilter()"
                                    title="Reset Filter">
                                    <i class="fa-solid fa-arrow-rotate-left"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card card-shell dt-wrap">
                    <div
                        class="card-header bg-white py-3 border-0 d-flex flex-wrap align-items-center justify-content-between gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <h5 class="mb-0 fw-bold text-dark">
                                <i class="fa-solid fa-list-check text-primary me-2"></i>Daftar Nominatif Bonus Tahunan
                            </h5>
                            <span class="badge bg-light text-secondary border" id="rowCountBadge">0 Data</span>
                        </div>
                        
                        <div class="d-flex gap-2">
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-success dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                    <i class="fa-solid fa-user-check me-1"></i>Pilih Peserta
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                                    <li><h6 class="dropdown-header">Berlaku untuk data yang tampil (sesuai filter):</h6></li>
                                    <li><a class="dropdown-item" href="javascript:void(0)" onclick="bulkIkut(true)">
                                        <i class="fa-solid fa-user-plus text-success me-2"></i>Ikutkan semua yang tampil</a></li>
                                    <li><a class="dropdown-item" href="javascript:void(0)" onclick="bulkIkut(false)">
                                        <i class="fa-solid fa-user-minus text-danger me-2"></i>Keluarkan semua yang tampil</a></li>
                                </ul>
                            </div>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button"
                                    id="bulkActionBtn" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fa-solid fa-wand-magic-sparkles me-1"></i>Set Skema Semua
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow border-0"
                                    aria-labelledby="bulkActionBtn">
                                    <li>
                                        <h6 class="dropdown-header">Ubah Skema Sekaligus:</h6>
                                    </li>
                                    <li><a class="dropdown-item" href="javascript:void(0)"
                                            onclick="applyBulkScheme('standar')"><i
                                                class="fa-solid fa-check text-muted me-2"></i>Semua ke Standar (1x /
                                            Prorata)</a></li>
                                    <li><a class="dropdown-item" href="javascript:void(0)"
                                            onclick="applyBulkScheme('2x')"><i
                                                class="fa-solid fa-bolt text-primary me-2"></i>Semua ke 2x Basic
                                            Salary</a></li>
                                    <li><a class="dropdown-item" href="javascript:void(0)"
                                            onclick="applyBulkScheme('4x')"><i class="fa-solid fa-fire me-2"
                                                style="color:#7c3aed;"></i>Semua ke 4x Basic Salary</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table id="bonusTable" class="table table-hover mb-0 w-100">
                            <thead>
                                <tr>
                                    <th style="width: 50px;" class="text-center">No</th>
                                    <th style="min-width: 170px;">Jabatan</th>
                                    <th style="min-width: 220px;">Nama Lengkap</th>
                                    <th style="min-width: 150px;" class="text-end">Basic Salary</th>
                                    <th style="min-width: 160px;" class="text-center">Rekening Maybank</th>
                                    <th style="min-width: 150px;" class="text-center">Status Karyawan</th>
                                    <th style="min-width: 190px;" class="text-center">Masa Kerja</th>
                                    <th style="min-width: 170px;" class="text-center">Skema Bonus</th>
                                    <th style="min-width: 160px;" class="text-end">Nominal Bonus</th>
                                    <th style="width: 90px;" class="text-center">Ikut Hitung</th>
                                    <th style="width: 70px;" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>

                    <div class="card-footer bg-light py-3">
                        <div class="row g-2 fw-bold">
                            <div class="col-12 col-md-6">
                                <small class="text-muted text-uppercase" style="letter-spacing: 0.5px;">Total Basic Salary
                                    Tampil:</small>
                                <span class="text-dark ms-1" id="tfootTotalBasic">Rp 0</span>
                            </div>
                            <div class="col-12 col-md-6 text-md-end">
                                <small class="text-muted text-uppercase" style="letter-spacing: 0.5px;">Total Bonus
                                    Tampil:</small>
                                <span class="text-primary fs-6 ms-1" id="tfootTotalBonus">Rp 0</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="tab-analitik" role="tabpanel" aria-labelledby="tab-analitik-btn"
                tabindex="0">

                <div class="card card-shell p-4 mb-4 rule-banner">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                        <div>
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <span class="badge bg-primary text-white" id="an-badge-tahun">-</span>
                                <h5 class="fw-bold text-dark mb-0">Bonus Tahunan - Pencairan Desember <span
                                        id="an-tahun-title">-</span></h5>
                            </div>
                            <p class="text-muted small mb-0">
                                Bonus dihitung berdasarkan masa kerja karyawan pada tahun <strong id="an-tahun-kerja">-</strong>
                                (Januari - Desember) dan basic salary (gaji).
                            </p>
                        </div>
                        <div class="text-md-end">
                            <div class="small text-muted">Status Pencairan</div>
                            <div class="fw-bold fs-5 text-primary" id="an-countdown">-</div>
                        </div>
                    </div>

                    <hr class="my-3 opacity-25">

                    <div class="d-flex flex-wrap gap-2 align-items-center">
                        <div class="rule-badge">
                            <i class="fa-solid fa-calendar-check text-primary"></i>
                            <span>Cut-off: <strong class="text-dark">31 Desember <span id="an-cutoff">-</span></strong></span>
                        </div>
                        <div class="rule-badge">
                            <i class="fa-solid fa-shield-halved text-success"></i>
                            <span>Masa Kerja Penuh: <strong class="text-dark">12 Bulan</strong></span>
                        </div>
                        <div class="rule-badge">
                            <i class="fa-solid fa-circle-info text-info"></i>
                            <span>Rumus: <strong class="text-dark">Pengali &times; (Gaji / 12) &times; Bulan Kerja</strong></span>
                        </div>
                        <div class="rule-badge">
                            <i class="fa-solid fa-user-check text-warning"></i>
                            <span>Direksi: <strong class="text-dark">Default 12 Bulan</strong></span>
                        </div>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-12 col-sm-6 col-xl-3">
                        <div class="card stat-card p-3">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <p class="stat-label">Penerima Bonus</p>
                                    <h3 class="stat-value" id="statTotalKaryawan">0</h3>
                                    <small class="text-muted" style="font-size: 0.72rem;">Belum berhak: <span
                                            id="statTidakBerhak">0</span></small>
                                </div>
                                <div class="stat-icon" style="background: linear-gradient(135deg, #4f46e5, #7c3aed);">
                                    <i class="fa-solid fa-users"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-xl-3">
                        <div class="card stat-card p-3">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <p class="stat-label">Total Pengeluaran Bonus</p>
                                    <h3 class="stat-value text-primary" id="statTotalBonus">Rp 0</h3>
                                    <small class="text-muted" style="font-size: 0.72rem;">Pencairan Desember</small>
                                </div>
                                <div class="stat-icon" style="background: linear-gradient(135deg, #059669, #10b981);">
                                    <i class="fa-solid fa-money-bill-wave"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-xl-3">
                        <div class="card stat-card p-3">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <p class="stat-label">Total Basic Salary</p>
                                    <h3 class="stat-value" id="statTotalBasic">Rp 0</h3>
                                    <small class="text-muted" style="font-size: 0.72rem;">Gaji</small>
                                </div>
                                <div class="stat-icon" style="background: linear-gradient(135deg, #0284c7, #38bdf8);">
                                    <i class="fa-solid fa-wallet"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-xl-3">
                        <div class="card stat-card p-3">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <p class="stat-label">Distribusi Skema</p>
                                    <div class="d-flex flex-wrap gap-1 mt-2" style="font-size: 0.75rem;">
                                        <span class="badge bg-secondary" id="badgeStandar" title="Skema Standar/Prorata">1x: 0</span>
                                        <span class="badge" style="background: #4f46e5 !important;" id="badge2x" title="2x Gaji">2x: 0</span>
                                        <span class="badge" style="background: #7c3aed !important;" id="badge4x" title="4x Gaji">4x: 0</span>
                                        <span class="badge bg-warning text-dark" id="badgeCustom" title="Custom">Custom: 0</span>
                                    </div>
                                    <small class="text-muted d-block mt-1" style="font-size: 0.7rem;">Ubah skema via tab
                                        Tabel</small>
                                </div>
                                <div class="stat-icon" style="background: linear-gradient(135deg, #d97706, #f59e0b);">
                                    <i class="fa-solid fa-calculator"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-6 col-md-4 col-xl-2">
                        <div class="card stat-card p-3">
                            <p class="stat-label">Rata-rata Bonus</p>
                            <h3 class="stat-value sm" id="m-avg">Rp 0</h3>
                            <small class="text-muted">per penerima</small>
                        </div>
                    </div>
                    <div class="col-6 col-md-4 col-xl-2">
                        <div class="card stat-card p-3">
                            <p class="stat-label">Median Bonus</p>
                            <h3 class="stat-value sm" id="m-median">Rp 0</h3>
                            <small class="text-muted">nilai tengah</small>
                        </div>
                    </div>
                    <div class="col-6 col-md-4 col-xl-2">
                        <div class="card stat-card p-3">
                            <p class="stat-label">Bonus Tertinggi</p>
                            <h3 class="stat-value sm text-success" id="m-max">Rp 0</h3>
                            <small class="text-muted text-truncate d-block" id="m-max-name">-</small>
                        </div>
                    </div>
                    <div class="col-6 col-md-4 col-xl-2">
                        <div class="card stat-card p-3">
                            <p class="stat-label">Bonus Terendah</p>
                            <h3 class="stat-value sm text-danger" id="m-min">Rp 0</h3>
                            <small class="text-muted text-truncate d-block" id="m-min-name">-</small>
                        </div>
                    </div>
                    <div class="col-6 col-md-4 col-xl-2">
                        <div class="card stat-card p-3">
                            <p class="stat-label">Rasio Bonus / Basic</p>
                            <h3 class="stat-value sm text-primary" id="m-rasio">0%</h3>
                            <small class="text-muted">beban bonus thd gaji</small>
                        </div>
                    </div>
                    <div class="col-6 col-md-4 col-xl-2">
                        <div class="card stat-card p-3">
                            <p class="stat-label">Masa Kerja Penuh</p>
                            <h3 class="stat-value sm" id="m-full">0%</h3>
                            <small class="text-muted">penerima 12 bulan</small>
                        </div>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-12 col-lg-6">
                        <div class="card card-shell p-3 h-100">
                            <div class="mb-2">
                                <h6 class="section-title"><i class="fa-solid fa-chart-line text-primary"></i> Tren Bonus Antar Tahun Pencairan</h6>
                                <p class="section-sub">Total bonus (Rp) dan jumlah penerima per tahun pencairan</p>
                            </div>
                            <div class="chart-box"><canvas id="chart-tren"></canvas></div>
                        </div>
                    </div>
                    <div class="col-12 col-lg-6">
                        <div class="card card-shell p-3 h-100">
                            <div class="mb-2">
                                <h6 class="section-title"><i class="fa-solid fa-chart-area text-success"></i> Distribusi Berdasarkan Bulan Kerja</h6>
                                <p class="section-sub">Jumlah penerima dan total bonus per bulan kerja (1 - 12 bulan)</p>
                            </div>
                            <div class="chart-box"><canvas id="chart-distribusi"></canvas></div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="card card-shell p-3">
                            <div class="mb-2">
                                <h6 class="section-title"><i class="fa-solid fa-chart-simple text-warning"></i> Kurva Nominal Bonus Seluruh Penerima (Terurut)</h6>
                                <p class="section-sub">Profil nominal bonus dari terendah hingga tertinggi untuk melihat sebaran dan kesenjangan nilai</p>
                            </div>
                            <div class="chart-box"><canvas id="chart-kurva"></canvas></div>
                        </div>
                    </div>
                </div>

                <div class="card card-shell p-3 mb-4">
                    <div class="mb-3">
                        <h6 class="section-title"><i class="fa-solid fa-circle-info text-primary"></i> Ringkasan Informasi Bonus Tahunan</h6>
                        <p class="section-sub">Rangkuman lengkap tahun pencairan terpilih</p>
                    </div>
                    <div class="row g-3" id="info-ringkasan"></div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-12 col-xl-6">
                        <div class="card card-shell p-3 h-100">
                            <h6 class="section-title mb-1"><i class="fa-solid fa-sliders text-primary"></i> Rincian Berdasarkan Skema Bonus</h6>
                            <p class="section-sub mb-2">Jumlah penerima, akumulasi basic salary, dan total bonus tiap skema</p>
                            <div class="table-responsive">
                                <table class="table mini-table mb-0">
                                    <thead>
                                        <tr>
                                            <th>Skema</th>
                                            <th class="text-end">Penerima</th>
                                            <th class="text-end">Total Basic</th>
                                            <th class="text-end">Total Bonus</th>
                                            <th class="text-end">Rata-rata</th>
                                            <th>Porsi</th>
                                        </tr>
                                    </thead>
                                    <tbody id="info-skema-body"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-xl-6">
                        <div class="card card-shell p-3 h-100">
                            <h6 class="section-title mb-1"><i class="fa-solid fa-id-badge text-info"></i> Rincian Berdasarkan Status Karyawan</h6>
                            <p class="section-sub mb-2">Alokasi bonus per status kepegawaian</p>
                            <div class="table-responsive">
                                <table class="table mini-table mb-0">
                                    <thead>
                                        <tr>
                                            <th>Status</th>
                                            <th class="text-end">Penerima</th>
                                            <th class="text-end">Total Basic</th>
                                            <th class="text-end">Total Bonus</th>
                                            <th class="text-end">Rata-rata</th>
                                            <th>Porsi</th>
                                        </tr>
                                    </thead>
                                    <tbody id="info-status-body"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card card-shell p-3 mb-4">
                    <h6 class="section-title mb-1"><i class="fa-solid fa-building text-info"></i> Rincian Berdasarkan Divisi</h6>
                    <p class="section-sub mb-2">Alokasi anggaran bonus tiap divisi (diurutkan dari terbesar)</p>
                    <div class="table-responsive" style="max-height: 340px; overflow-y: auto;">
                        <table class="table mini-table mb-0">
                            <thead>
                                <tr>
                                    <th>Divisi</th>
                                    <th class="text-end">Penerima</th>
                                    <th class="text-end">Total Basic</th>
                                    <th class="text-end">Total Bonus</th>
                                    <th class="text-end">Rata-rata</th>
                                    <th>Porsi Anggaran</th>
                                </tr>
                            </thead>
                            <tbody id="info-divisi-body"></tbody>
                        </table>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-12 col-xl-5">
                        <div class="card card-shell p-3 h-100">
                            <h6 class="section-title mb-1"><i class="fa-solid fa-trophy text-success"></i> 10 Penerima Bonus Tertinggi</h6>
                            <p class="section-sub mb-2">Berdasarkan nominal bonus</p>
                            <div id="info-top-body"></div>
                        </div>
                    </div>
                    <div class="col-12 col-xl-7">
                        <div class="card card-shell p-3 h-100">
                            <h6 class="section-title mb-1"><i class="fa-solid fa-triangle-exclamation text-danger"></i> Perlu Perhatian HR</h6>
                            <p class="section-sub mb-2">Data yang perlu dilengkapi atau diperiksa sebelum pencairan</p>
                            <div class="row g-2" id="info-attention"></div>
                        </div>
                    </div>
                </div>

                <div class="card card-shell p-3 mb-4">
                    <h6 class="section-title mb-1"><i class="fa-solid fa-code-compare text-primary"></i> Perbandingan Antar Tahun Pencairan</h6>
                    <p class="section-sub mb-2">Perkembangan anggaran bonus tahunan dari tahun ke tahun</p>
                    <div class="table-responsive">
                        <table class="table mini-table mb-0">
                            <thead>
                                <tr>
                                    <th>Tahun Pencairan</th>
                                    <th>Periode Kerja</th>
                                    <th class="text-end">Penerima</th>
                                    <th class="text-end">Total Basic</th>
                                    <th class="text-end">Total Bonus</th>
                                    <th class="text-end">Selisih</th>
                                    <th class="text-end">Perubahan</th>
                                </tr>
                            </thead>
                            <tbody id="info-tahun-body"></tbody>
                        </table>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-12 col-xl-6">
                        <div class="card card-shell p-3 h-100">
                            <h6 class="section-title mb-1"><i class="fa-solid fa-calculator text-warning"></i> Simulasi Perhitungan Skema</h6>
                            <p class="section-sub mb-2">Contoh dengan basic salary Rp 5.000.000</p>
                            <div class="table-responsive">
                                <table class="table mini-table mb-0">
                                    <thead>
                                        <tr>
                                            <th>Bulan Kerja</th>
                                            <th class="text-end">Standar (1x)</th>
                                            <th class="text-end">2x</th>
                                            <th class="text-end">4x</th>
                                        </tr>
                                    </thead>
                                    <tbody id="info-simulasi-body"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-xl-6">
                        <div class="card card-shell p-3 h-100">
                            <h6 class="section-title mb-1"><i class="fa-solid fa-circle-question text-primary"></i> Ketentuan & Pertanyaan Umum</h6>
                            <p class="section-sub mb-2">Penjelasan aturan yang digunakan sistem</p>
                            <div class="accordion" id="faqBonus">
                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button" type="button" data-bs-toggle="collapse"
                                            data-bs-target="#faq1">Siapa saja yang dihitung bonusnya?</button>
                                    </h2>
                                    <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqBonus">
                                        <div class="accordion-body">
                                            Seluruh karyawan yang memiliki NIP, berstatus aktif (status karyawan 1), dan bekerja pada tahun periode kerja. Tidak dihitung: jabatan Outsource, kode karyawan berawalan OL, jabatan "Pilih Jabatan", serta karyawan nonaktif.
                                        </div>
                                    </div>
                                </div>
                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                            data-bs-target="#faq2">Bagaimana bulan kerja dihitung?</button>
                                    </h2>
                                    <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqBonus">
                                        <div class="accordion-body">
                                            Karyawan yang masuk sebelum tahun periode kerja dihitung 12 bulan. Karyawan yang masuk pada tahun periode kerja dihitung dari bulan masuk hingga Desember (12 - bulan masuk + 1). Karyawan yang masuk setelah tahun tersebut belum berhak. Divisi Direksi dihitung default 12 bulan.
                                        </div>
                                    </div>
                                </div>
                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                            data-bs-target="#faq3">Apa perbedaan skema Standar, 2x, 4x, dan Custom?</button>
                                    </h2>
                                    <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqBonus">
                                        <div class="accordion-body">
                                            Standar: (Gaji / 12) &times; bulan kerja. 2x dan 4x: nilai standar dikalikan 2 atau 4. Custom: nominal ditetapkan manual dan tidak mengikuti kelipatan gaji.
                                        </div>
                                    </div>
                                </div>
                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                            data-bs-target="#faq4">Apa yang terjadi bila data diedit?</button>
                                    </h2>
                                    <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqBonus">
                                        <div class="accordion-body">
                                            Seluruh kolom dapat diedit dan hanya tersimpan pada data bonus tahunan. Data master karyawan dan log gaji tidak berubah. Basic salary berasal dari gaji saja, tanpa tunjangan jabatan.
                                        </div>
                                    </div>
                                </div>
                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                            data-bs-target="#faq5">Apa fungsi tombol Hitung Ulang?</button>
                                    </h2>
                                    <div id="faq5" class="accordion-collapse collapse" data-bs-parent="#faqBonus">
                                        <div class="accordion-body">
                                            Menghitung ulang dan menyimpan seluruh data bonus karyawan yang berhak untuk tahun pencairan terpilih. Data yang sudah diedit manual tetap dipertahankan.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <div class="modal fade" id="modalEditBonus" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 14px;">
                <div class="modal-header bg-primary text-white border-0" style="border-radius: 14px 14px 0 0;">
                    <h5 class="modal-title fw-bold">
                        <i class="fa-solid fa-sliders me-2"></i>Penyesuaian Data & Bonus
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <input type="hidden" id="editKaryawanId">
                    <input type="hidden" id="editTahun">

                    <div class="alert alert-info py-2 small mb-3">
                        <i class="fa-solid fa-circle-info me-1"></i>Perubahan hanya tersimpan pada data bonus tahunan.
                        Data master karyawan dan log gaji tidak berubah.
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-8">
                            <label class="form-label fw-bold small text-dark">Nama Lengkap</label>
                            <input type="text" id="editNamaInput" class="form-control">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-bold small text-dark">NIP</label>
                            <input type="text" id="editNipInput" class="form-control">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-bold small text-dark">Jabatan</label>
                            <input type="text" id="editJabatanInput" class="form-control">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-bold small text-dark">Divisi</label>
                            <input type="text" id="editDivisiInput" class="form-control">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-bold small text-dark">Rekening Maybank</label>
                            <input type="text" id="editRekeningInput" class="form-control">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-bold small text-dark">Status Karyawan</label>
                            <select id="editStatusInput" class="form-select">
                                <option value="Karyawan Tetap">Karyawan Tetap</option>
                                <option value="Karyawan Kontrak">Karyawan Kontrak</option>
                                <option value="Karyawan Probation">Karyawan Probation</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-bold small text-dark">Tanggal Bergabung</label>
                            <input type="date" id="editTanggalBergabung" class="form-control">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-bold small text-dark">Bulan Kerja (1 - 12)</label>
                            <input type="number" id="editBulanKerja" min="1" max="12" step="1" class="form-control"
                                oninput="updateLiveCalculationPreview()">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-bold small text-dark">Teks Masa Kerja</label>
                            <input type="text" id="editMasaKerjaTeks" class="form-control"
                                placeholder="Kosongkan untuk otomatis">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-dark">
                            <i class="fa-solid fa-money-bill-wave text-success me-1"></i>Basic Salary (Gaji)
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-white fw-bold text-muted">Rp</span>
                            <input type="number" id="inputEditBasicSalary" class="form-control fw-bold text-dark"
                                placeholder="Contoh: 5000000" oninput="updateLiveCalculationPreview()">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-dark">
                            <i class="fa-solid fa-calculator text-primary me-1"></i>Pilih Skema Bonus
                        </label>
                        <select id="editSkema" class="form-select" onchange="onModalSkemaChanged()">
                            <option value="standar">Standar (Prorata Masa Kerja / 1x Gaji)</option>
                            <option value="2x">2x Basic Salary (2 Kali Gaji)</option>
                            <option value="4x">4x Basic Salary (4 Kali Gaji)</option>
                            <option value="custom">Custom Nominal Khusus (Input Manual)</option>
                        </select>
                    </div>

                    <div class="mb-3" id="groupCustomNominal" style="display: none;">
                        <label class="form-label fw-bold small text-dark">Nominal Bonus Kustom (Rp)</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light fw-bold">Rp</span>
                            <input type="number" id="editCustomNominal" class="form-control fw-bold"
                                placeholder="Masukkan nominal khusus..." oninput="updateLiveCalculationPreview()">
                        </div>
                        <small class="text-muted" style="font-size: 0.73rem;">Nominal bonus langsung yang tidak mengikuti
                            kelipatan gaji.</small>
                    </div>

                    <div class="p-3 rounded-3 mb-3 border bg-primary bg-opacity-10 border-primary border-opacity-25">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-primary fw-bold text-uppercase d-block"
                                    style="font-size: 0.68rem; letter-spacing: 0.5px;">
                                    <i class="fa-solid fa-wand-magic-sparkles me-1"></i>Estimasi Nominal Bonus
                                </small>
                                <span class="small text-muted" id="liveCalcExplanation">-</span>
                            </div>
                            <div class="text-end">
                                <span class="h4 fw-bold text-primary mb-0 d-block" id="liveCalcResult">Rp 0</span>
                            </div>
                        </div>
                    </div>

                    <div class="mb-2">
                        <label class="form-label fw-bold small text-dark">Catatan / Keterangan (Opsional)</label>
                        <textarea id="editCatatan" class="form-control" rows="2" placeholder="Catatan khusus pemberian bonus..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary px-4 fw-bold shadow-sm" onclick="saveCustomBonus()">
                        <i class="fa-solid fa-floppy-disk me-1"></i>Simpan Perubahan
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalExportPdf" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 14px;">
                <div class="modal-header bg-danger text-white border-0" style="border-radius: 14px 14px 0 0;">
                    <h5 class="modal-title fw-bold">
                        <i class="fa-solid fa-file-pdf me-2"></i>Export PDF Surat Bonus
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <label class="form-label fw-bold small text-dark text-uppercase" style="letter-spacing: 0.5px;">1.
                        Jenis Dokumen</label>

                    <label class="export-option active" id="optModeRekap">
                        <input type="radio" name="exportMode" value="rekap" checked onchange="onExportModeChanged()">
                        <span class="opt-title">Surat Rekap Seluruhnya</span>
                        <span class="opt-desc">Satu surat berisi daftar seluruh karyawan penerima bonus (sesuai filter
                            aktif).</span>
                    </label>

                    <label class="export-option" id="optModeSemua">
                        <input type="radio" name="exportMode" value="semua_surat" onchange="onExportModeChanged()">
                        <span class="opt-title">Surat Per Karyawan (Semua)</span>
                        <span class="opt-desc">Satu file PDF, setiap karyawan mendapat satu halaman surat sendiri (sesuai
                            filter aktif).</span>
                    </label>

                    <label class="export-option" id="optModePerOrang">
                        <input type="radio" name="exportMode" value="per_orang" onchange="onExportModeChanged()">
                        <span class="opt-title">Surat Satu Karyawan</span>
                        <span class="opt-desc">Pilih satu karyawan untuk dicetak suratnya.</span>
                    </label>

                    <div class="mb-3" id="groupExportKaryawan" style="display: none;">
                        <select id="exportKaryawan" class="form-select"></select>
                    </div>

                    <label class="form-label fw-bold small text-dark text-uppercase mt-2"
                        style="letter-spacing: 0.5px;">2. Format Kertas</label>

                    <label class="export-option active" id="optFormatLengkap">
                        <input type="radio" name="exportFormat" value="0" checked onchange="onExportModeChanged()">
                        <span class="opt-title">Lengkap (Kop Surat + Background)</span>
                        <span class="opt-desc">Memakai logo, kop surat, dan background perusahaan.</span>
                    </label>

                    <label class="export-option" id="optFormatPolos">
                        <input type="radio" name="exportFormat" value="1" onchange="onExportModeChanged()">
                        <span class="opt-title">Polosan</span>
                        <span class="opt-desc">Tanpa kop dan background, untuk dicetak di kertas kop surat yang sudah
                            jadi.</span>
                    </label>

                    <label class="form-label fw-bold small text-dark text-uppercase mt-2"
                        style="letter-spacing: 0.5px;">3. Nomor Surat (Opsional)</label>
                    <input type="text" id="exportNomorSurat" class="form-control"
                        placeholder="Kosongkan untuk diisi manual setelah dicetak">
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-danger px-4 fw-bold shadow-sm" onclick="submitExportPdf()">
                        <i class="fa-solid fa-download me-1"></i>Export PDF
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

    <script>
        let tampilDesimal = true;
        try { tampilDesimal = (localStorage.getItem('bonus_tampil_desimal') ?? '1') === '1'; } catch (e) {}
        let dt = null;
        let cachedBonusData = [];
        let cachedSummary = null;
        let currentFiltered = [];
        const trendCache = {};
        const charts = {};

        const formatIDR = (n) => {
            const val = typeof n === 'number' ? n : (parseFloat(n) || 0);
            const bersih = Math.round(val * 100) / 100; // buang noise float saja

            if (tampilDesimal) {
                return 'Rp ' + bersih.toLocaleString('id-ID', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            }

            return 'Rp ' + Math.trunc(bersih).toLocaleString('id-ID');
        };

        const shortRupiah = (v) => {
            const n = Math.abs(v);
            if (n >= 1e9) return 'Rp ' + (v / 1e9).toFixed(1).replace('.', ',') + ' M';
            if (n >= 1e6) return 'Rp ' + (v / 1e6).toFixed(1).replace('.', ',') + ' jt';
            if (n >= 1e3) return 'Rp ' + (v / 1e3).toFixed(0) + ' rb';
            return 'Rp ' + v;
        };

        const escapeHtml = (str) => String(str ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');

        const num = (v) => parseFloat(v) || 0;

        const SKEMA_LABEL = {
            'standar': 'Standar (Prorata)',
            '2x': '2x Basic Salary',
            '4x': '4x Basic Salary',
            'custom': 'Custom Nominal'
        };

        function showToast(message, isSuccess = true) {
            const container = document.getElementById('toast-container');
            const toast = document.createElement('div');
            toast.className = `toast-msg ${isSuccess ? 'toast-success' : 'toast-error'}`;
            toast.innerHTML =
                `<i class="fa-solid ${isSuccess ? 'fa-circle-check' : 'fa-circle-exclamation'}"></i><span>${escapeHtml(message)}</span>`;
            container.appendChild(toast);
            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transform = 'translateX(50px)';
                toast.style.transition = 'all 0.3s ease';
                setTimeout(() => toast.remove(), 300);
            }, 3500);
        }

        function isAnalitikActive() {
            return document.getElementById('tab-analitik').classList.contains('active');
        }

        function median(arr) {
            if (!arr.length) return 0;
            const s = arr.slice().sort((a, b) => a - b);
            const mid = Math.floor(s.length / 2);
            return s.length % 2 ? s[mid] : (s[mid - 1] + s[mid]) / 2;
        }

        function initTable() {
            if (dt) return;

            dt = $('#bonusTable').DataTable({
                data: [],
                pageLength: 10,
                lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'Semua']],
                searching: false,
                deferRender: true,
                autoWidth: false,
                order: [[3, 'asc']],
                dom: '<"dt-top"l>rt<"dt-bottom"ip>',
                language: {
                    lengthMenu: 'Tampilkan _MENU_ karyawan',
                    info: 'Menampilkan _START_ - _END_ dari _TOTAL_ karyawan',
                    infoEmpty: 'Menampilkan 0 - 0 dari 0 karyawan',
                    infoFiltered: '(disaring dari _MAX_ data)',
                    zeroRecords: 'Tidak ada data karyawan yang sesuai dengan filter.',
                    emptyTable: 'Tidak ada data karyawan yang sesuai dengan filter.',
                    paginate: { first: 'Awal', last: 'Akhir', next: '&raquo;', previous: '&laquo;' }
                },
                columns: [
                    {
                        data: null,
                        orderable: false,
                        className: 'text-center text-muted fw-semibold',
                        render: function () { return ''; }
                    },
                    {
                        data: 'jabatan',
                        render: function (d, type, row) {
                            if (type !== 'display') return d || '';
                            return `<strong class="text-dark d-block">${escapeHtml(d)}</strong>
                                    <small class="text-muted">${escapeHtml(row.divisi)}</small>`;
                        }
                    },
                    {
                        data: 'nama_lengkap',
                        render: function (d, type, row) {
                            if (type !== 'display') return d || '';
                            const badge = row.is_edited ? '<span class="badge-diedit">Diedit</span>' : '';
                            return `<span class="fw-bold text-dark d-block">${escapeHtml(d)}</span>
                                    <small class="text-muted">NIP: ${escapeHtml(row.nip)}</small>
                                    ${badge}`;
                        }
                    },
                    {
                        data: 'basic_salary',
                        className: 'text-end',
                        render: function (d, type) {
                            if (type !== 'display') return num(d);
                            return `<span class="fw-semibold text-secondary">${formatIDR(d)}</span>`;
                        }
                    },
                    {
                        data: 'rekening_maybank',
                        className: 'text-center',
                        render: function (d, type) {
                            if (type !== 'display') return d || '';
                            return (d && d !== '-')
                                ? `<span class="bank-badge"><i class="fa-solid fa-credit-card"></i> ${escapeHtml(d)}</span>`
                                : `<span class="text-muted small">-</span>`;
                        }
                    },
                    {
                        data: 'status_karyawan',
                        className: 'text-center',
                        render: function (d, type) {
                            if (type !== 'display') return d || '';
                            let cls = 'status-tetap';
                            if (d === 'Karyawan Kontrak') cls = 'status-kontrak';
                            else if (d === 'Karyawan Probation') cls = 'status-probation';
                            return `<span class="badge-status ${cls}">${escapeHtml(d)}</span>`;
                        }
                    },
                    {
                        data: 'bulan_kerja',
                        className: 'text-center',
                        render: function (d, type, row) {
                            if (type !== 'display') return num(d);
                            if (row.berhak) {
                                return `<span class="fw-bold text-dark d-block">${escapeHtml(row.masa_kerja)}</span>
                                        <small class="d-block text-muted" style="font-size:0.7rem;">${row.is_direksi && !row.is_edited ? 'Direksi (default 12 bln)' : 'Gabung: ' + escapeHtml(row.tanggal_bergabung)}</small>
                                        <small class="d-block text-primary" style="font-size:0.7rem;">Dihitung: ${row.bulan_kerja} bulan</small>`;
                            }
                            return `<span class="fw-bold text-muted d-block">-</span>
                                    <small class="d-block text-muted" style="font-size:0.7rem;">Gabung: ${escapeHtml(row.tanggal_bergabung)}</small>
                                    <span class="badge-tidak-berhak">Belum Berhak</span>`;
                        }
                    },
                    {
                        data: 'skema_bonus',
                        className: 'text-center',
                        render: function (d, type, row) {
                            if (type !== 'display') return d || '';
                            let skemaClass = 'skema-standar';
                            if (d === '2x') skemaClass = 'skema-2x';
                            else if (d === '4x') skemaClass = 'skema-4x';
                            else if (d === 'custom') skemaClass = 'skema-custom';
                            const dis = row.berhak ? '' : 'disabled';
                            return `<select class="form-select form-select-sm skema-select ${skemaClass}"
                                        id="select-skema-${row.karyawan_id}" ${dis}
                                        onchange="onSkemaChange(${row.karyawan_id}, this.value)">
                                        <option value="standar" ${d === 'standar' ? 'selected' : ''}>Standar (Prorata)</option>
                                        <option value="2x" ${d === '2x' ? 'selected' : ''}>2x Basic Salary</option>
                                        <option value="4x" ${d === '4x' ? 'selected' : ''}>4x Basic Salary</option>
                                        <option value="custom" ${d === 'custom' ? 'selected' : ''}>Custom Nominal</option>
                                    </select>`;
                        }
                    },
                    {
                        data: 'nominal_bonus',
                        className: 'text-end',
                        render: function (d, type, row) {
                            if (type !== 'display') return num(d);
                            return (row.berhak && num(d) > 0)
                                ? `<span class="bonus-amount" id="bonus-val-${row.karyawan_id}">${formatIDR(d)}</span>`
                                : `<span class="text-muted fw-bold" id="bonus-val-${row.karyawan_id}">Rp 0</span>`;
                        }
                    },
                    {
                        data: 'is_excluded',
                        orderable: false,
                        className: 'text-center',
                        render: function (d, type, row) {
                            if (type !== 'display') return d ? 0 : 1;
                            const dis = row.berhak_asli ? '' : 'disabled';
                            const chk = (row.berhak_asli && !row.is_excluded) ? 'checked' : '';
                            const title = row.berhak_asli ? 'Ikut / keluarkan dari perhitungan' : 'Belum berhak bonus';
                            return `<div class="form-check form-switch d-flex justify-content-center mb-0">
                                        <input class="form-check-input" type="checkbox" role="switch" title="${title}"
                                            id="ikut-${row.karyawan_id}" ${chk} ${dis}
                                            onchange="toggleIkut(${row.karyawan_id}, this.checked)">
                                    </div>`;
                        }
                    },
                    {
                        data: 'karyawan_id',
                        orderable: false,
                        className: 'text-center',
                        render: function (d, type, row) {
                            const dis = row.berhak ? '' : 'disabled';
                            return `<button type="button" class="btn btn-sm btn-light text-primary" ${dis}
                                        title="Edit Data & Bonus"
                                        onclick="openEditModal(${d})">
                                        <i class="fa-solid fa-sliders"></i>
                                    </button>`;
                        }
                    }
                ],
                createdRow: function (row, data) {
                    row.id = 'row-' + data.karyawan_id;
                    if (!data.berhak) row.classList.add('row-tidak-berhak');
                },
                drawCallback: function () {
                    const api = this.api();
                    const start = api.page.info().start;
                    api.column(0, { page: 'current' }).nodes().each(function (cell, i) {
                        cell.innerHTML = start + i + 1;
                    });
                }
            });
        }

        function applyFilters(resetPage = true) {
            initTable();

            const divisi = document.getElementById('filterDivisi').value;
            const status = document.getElementById('filterStatus').value;
            const skema = document.getElementById('filterSkema').value;
            const search = document.getElementById('searchKeyword').value.toLowerCase().trim();
            const ikut = document.getElementById('filterIkut').value;

            currentFiltered = cachedBonusData.filter(item => {
                if (divisi !== 'all' && item.divisi !== divisi) return false;
                if (status !== 'all' && item.status_karyawan !== status) return false;
                if (skema !== 'all' && item.skema_bonus !== skema) return false;
                if (ikut === 'ikut' && item.is_excluded) return false;
                if (ikut === 'keluar' && !item.is_excluded) return false;
                if (search) {
                    const hay = [item.nama_lengkap, item.nip, item.jabatan, item.divisi, item.rekening_maybank]
                        .map(v => String(v || '').toLowerCase()).join(' | ');
                    if (!hay.includes(search)) return false;
                }
                return true;
            });

            dt.clear();
            dt.rows.add(currentFiltered);
            dt.draw(resetPage);

            document.getElementById('rowCountBadge').textContent = `${currentFiltered.length} Data`;

            const eligible = currentFiltered.filter(x => x.berhak);
            document.getElementById('tfootTotalBasic').textContent = formatIDR(eligible.reduce((a, i) => a + num(i.basic_salary), 0));
            document.getElementById('tfootTotalBonus').textContent = formatIDR(eligible.reduce((a, i) => a + num(i.nominal_bonus), 0));
        }

        function resetFilter() {
            document.getElementById('filterDivisi').value = 'all';
            document.getElementById('filterStatus').value = 'all';
            document.getElementById('filterSkema').value = 'all';
            document.getElementById('searchKeyword').value = '';
            document.getElementById('filterIkut').value = 'all';
            applyFilters();
        }

        function loadData(resetPage = true) {
            const tahun = document.getElementById('filterTahun').value;

            document.getElementById('labelTahun').textContent = tahun;
            document.getElementById('labelTahunBonus').textContent = parseInt(tahun) - 1;
            document.getElementById('labelCutoffTahun').textContent = parseInt(tahun) - 1;
            document.getElementById('loadingBadge').style.display = 'inline-block';

            $.ajax({
                url: "{{ route('HR.bonus-tahunan.data') }}",
                type: "GET",
                data: { year: tahun },
                success: function(res) {
                    document.getElementById('loadingBadge').style.display = 'none';
                    if (res.success) {
                        cachedBonusData = res.data || [];
                        cachedSummary = res.summary || {};
                        trendCache[tahun] = cachedSummary;
                        applyFilters(resetPage);
                        renderAnalytics();
                        ensureTrend();
                        if (isAnalitikActive()) renderCharts();
                    } else {
                        showToast(res.message || 'Gagal mengambil data', false);
                    }
                },
                error: function(xhr) {
                    document.getElementById('loadingBadge').style.display = 'none';
                    const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message :
                        'Terjadi kesalahan saat memuat data.';
                    showToast(msg, false);
                }
            });
        }

        function ensureTrend() {
            const years = Array.from(document.querySelectorAll('#filterTahun option')).map(o => o.value);
            const missing = years.filter(y => !trendCache[y]);

            if (!missing.length) {
                renderTrendTable();
                if (isAnalitikActive()) renderCharts();
                return;
            }

            Promise.allSettled(missing.map(y => new Promise(resolve => {
                $.ajax({
                    url: "{{ route('HR.bonus-tahunan.data') }}",
                    type: "GET",
                    data: { year: y }
                }).done(function(res) {
                    if (res.success) trendCache[y] = res.summary;
                }).always(resolve);
            }))).then(() => {
                renderTrendTable();
                if (isAnalitikActive()) renderCharts();
            });
        }

        function renderAnalytics() {
            const tahun = parseInt(document.getElementById('filterTahun').value);
            const s = cachedSummary || {};
            const eligible = cachedBonusData.filter(x => x.berhak);
            const notEligible = cachedBonusData.filter(x => !x.berhak && !x.is_excluded);
            const excludedList = cachedBonusData.filter(x => x.is_excluded);

            const totalBonus = eligible.reduce((a, i) => a + num(i.nominal_bonus), 0);
            const totalBasic = eligible.reduce((a, i) => a + num(i.basic_salary), 0);

            document.getElementById('an-badge-tahun').textContent = tahun;
            document.getElementById('an-tahun-title').textContent = tahun;
            document.getElementById('an-tahun-kerja').textContent = tahun - 1;
            document.getElementById('an-cutoff').textContent = tahun - 1;

            const today = new Date();
            today.setHours(0, 0, 0, 0);
            const start = new Date(tahun, 11, 1);
            const end = new Date(tahun, 11, 31);
            const diff = Math.round((start - today) / 86400000);
            const cd = document.getElementById('an-countdown');
            if (diff > 0) {
                cd.textContent = `H-${diff} menuju bulan pencairan`;
                cd.className = 'fw-bold fs-5 text-primary';
            } else if (today <= end) {
                cd.textContent = 'Bulan pencairan sedang berjalan';
                cd.className = 'fw-bold fs-5 text-warning';
            } else {
                cd.textContent = 'Pencairan telah lewat';
                cd.className = 'fw-bold fs-5 text-muted';
            }

            document.getElementById('statTotalKaryawan').textContent = s.total_karyawan || 0;
            document.getElementById('statTidakBerhak').textContent = s.total_tidak_berhak || 0;
            document.getElementById('statTotalBonus').textContent = formatIDR(s.total_nominal_bonus || 0);
            document.getElementById('statTotalBasic').textContent = formatIDR(s.total_basic_salary || 0);
            document.getElementById('badgeStandar').textContent = '1x: ' + (s.count_standar || 0);
            document.getElementById('badge2x').textContent = '2x: ' + (s.count_2x || 0);
            document.getElementById('badge4x').textContent = '4x: ' + (s.count_4x || 0);
            document.getElementById('badgeCustom').textContent = 'Custom: ' + (s.count_custom || 0);

            const positive = eligible.filter(i => num(i.nominal_bonus) > 0);
            const values = positive.map(i => num(i.nominal_bonus));
            const avg = positive.length ? totalBonus / positive.length : 0;
            let maxI = null, minI = null;
            positive.forEach(i => {
                if (!maxI || num(i.nominal_bonus) > num(maxI.nominal_bonus)) maxI = i;
                if (!minI || num(i.nominal_bonus) < num(minI.nominal_bonus)) minI = i;
            });
            const fullCount = eligible.filter(i => num(i.bulan_kerja) >= 12).length;

            document.getElementById('m-avg').textContent = formatIDR(avg);
            document.getElementById('m-median').textContent = formatIDR(median(values));
            document.getElementById('m-max').textContent = formatIDR(maxI ? maxI.nominal_bonus : 0);
            document.getElementById('m-max-name').textContent = maxI ? maxI.nama_lengkap : '-';
            document.getElementById('m-min').textContent = formatIDR(minI ? minI.nominal_bonus : 0);
            document.getElementById('m-min-name').textContent = minI ? minI.nama_lengkap : '-';
            document.getElementById('m-rasio').textContent = (totalBasic > 0 ? (totalBonus / totalBasic * 100) : 0).toFixed(1).replace('.', ',') + '%';
            document.getElementById('m-full').textContent = (eligible.length ? (fullCount / eligible.length * 100) : 0).toFixed(0) + '%';

            const tile = (k, v) => `<div class="col-6 col-md-4 col-xl-3"><div class="info-tile"><div class="k">${k}</div><div class="v">${v}</div></div></div>`;
            const noSalary = eligible.filter(i => num(i.basic_salary) <= 0).length;
            const noRek = eligible.filter(i => !i.rekening_maybank || i.rekening_maybank === '-').length;
            const direksi = eligible.filter(i => i.is_direksi).length;
            const prorata = eligible.filter(i => num(i.bulan_kerja) < 12).length;
            const saved = eligible.filter(i => i.is_saved).length;
            const edited = eligible.filter(i => i.is_edited).length;
            const customTotal = eligible.filter(i => i.skema_bonus === 'custom').reduce((a, i) => a + num(i.nominal_bonus), 0);
            const countStatus = (st) => eligible.filter(i => i.status_karyawan === st).length;

            document.getElementById('info-ringkasan').innerHTML = [
                tile('Tahun Pencairan', 'Desember ' + tahun),
                tile('Periode Kerja', 'Jan - Des ' + (tahun - 1)),
                tile('Total Pengeluaran Bonus', formatIDR(totalBonus)),
                tile('Total Basic Salary', formatIDR(totalBasic)),
                tile('Penerima Bonus', eligible.length),
                tile('Belum Berhak', notEligible.length),
                tile('Masa Kerja Penuh (12 Bln)', fullCount),
                tile('Masa Kerja Prorata (< 12 Bln)', prorata),
                tile('Karyawan Tetap', countStatus('Karyawan Tetap')),
                tile('Karyawan Kontrak', countStatus('Karyawan Kontrak')),
                tile('Karyawan Probation', countStatus('Karyawan Probation')),
                tile('Divisi Direksi', direksi),
                tile('Total Bonus Skema Custom', formatIDR(customTotal)),
                tile('Data Tersimpan', `${saved} / ${eligible.length}`),
                tile('Data Diedit Manual', edited),
                tile('Gaji Belum Terisi', noSalary),
                tile('Rekening Belum Terisi', noRek)
            ].join('');

            const skemaKeys = ['standar', '2x', '4x', 'custom'];
            document.getElementById('info-skema-body').innerHTML = skemaKeys.map(k => {
                const grp = eligible.filter(i => i.skema_bonus === k);
                const bonus = grp.reduce((a, i) => a + num(i.nominal_bonus), 0);
                const bas = grp.reduce((a, i) => a + num(i.basic_salary), 0);
                const pct = totalBonus > 0 ? bonus / totalBonus * 100 : 0;
                return `<tr>
                    <td class="fw-semibold">${SKEMA_LABEL[k]}</td>
                    <td class="text-end">${grp.length}</td>
                    <td class="text-end">${formatIDR(bas)}</td>
                    <td class="text-end fw-bold text-primary">${formatIDR(bonus)}</td>
                    <td class="text-end">${formatIDR(grp.length ? bonus / grp.length : 0)}</td>
                    <td><div class="bar-track"><div class="bar-fill" style="width:${pct.toFixed(1)}%"></div></div>
                        <small class="text-muted">${pct.toFixed(1).replace('.', ',')}%</small></td>
                </tr>`;
            }).join('');

            const statusKeys = [['Karyawan Tetap', 'status-tetap'], ['Karyawan Kontrak', 'status-kontrak'], ['Karyawan Probation', 'status-probation']];
            document.getElementById('info-status-body').innerHTML = statusKeys.map(([st, cls]) => {
                const grp = eligible.filter(i => i.status_karyawan === st);
                const bonus = grp.reduce((a, i) => a + num(i.nominal_bonus), 0);
                const bas = grp.reduce((a, i) => a + num(i.basic_salary), 0);
                const pct = totalBonus > 0 ? bonus / totalBonus * 100 : 0;
                return `<tr>
                    <td><span class="badge-status ${cls}">${st}</span></td>
                    <td class="text-end">${grp.length}</td>
                    <td class="text-end">${formatIDR(bas)}</td>
                    <td class="text-end fw-bold text-primary">${formatIDR(bonus)}</td>
                    <td class="text-end">${formatIDR(grp.length ? bonus / grp.length : 0)}</td>
                    <td><div class="bar-track"><div class="bar-fill" style="width:${pct.toFixed(1)}%"></div></div>
                        <small class="text-muted">${pct.toFixed(1).replace('.', ',')}%</small></td>
                </tr>`;
            }).join('');

            const divMap = {};
            eligible.forEach(i => {
                const d = i.divisi && i.divisi !== '-' ? i.divisi : 'Tanpa Divisi';
                if (!divMap[d]) divMap[d] = { count: 0, bonus: 0, basic: 0 };
                divMap[d].count++;
                divMap[d].bonus += num(i.nominal_bonus);
                divMap[d].basic += num(i.basic_salary);
            });
            const divRows = Object.keys(divMap).map(k => ({ nama: k, ...divMap[k] })).sort((a, b) => b.bonus - a.bonus);
            document.getElementById('info-divisi-body').innerHTML = divRows.length ? divRows.map(r => {
                const pct = totalBonus > 0 ? r.bonus / totalBonus * 100 : 0;
                return `<tr>
                    <td class="fw-semibold">${escapeHtml(r.nama)}</td>
                    <td class="text-end">${r.count}</td>
                    <td class="text-end">${formatIDR(r.basic)}</td>
                    <td class="text-end fw-bold text-primary">${formatIDR(r.bonus)}</td>
                    <td class="text-end">${formatIDR(r.count ? r.bonus / r.count : 0)}</td>
                    <td><div class="bar-track"><div class="bar-fill" style="width:${pct.toFixed(1)}%"></div></div>
                        <small class="text-muted">${pct.toFixed(1).replace('.', ',')}%</small></td>
                </tr>`;
            }).join('') : '<tr><td colspan="6" class="text-center text-muted py-3">Belum ada data</td></tr>';

            const top = eligible.slice().sort((a, b) => num(b.nominal_bonus) - num(a.nominal_bonus)).slice(0, 10);
            document.getElementById('info-top-body').innerHTML = top.length ? top.map((i, idx) => `
                <div class="d-flex align-items-center justify-content-between py-2 ${idx < top.length - 1 ? 'border-bottom' : ''}">
                    <div class="d-flex align-items-center gap-2">
                        <span class="rank-no">${idx + 1}</span>
                        <div>
                            <div class="fw-semibold small">${escapeHtml(i.nama_lengkap)}</div>
                            <small class="text-muted">${escapeHtml(i.jabatan)} &bull; ${SKEMA_LABEL[i.skema_bonus] || i.skema_bonus}</small>
                        </div>
                    </div>
                    <span class="fw-bold text-primary small">${formatIDR(i.nominal_bonus)}</span>
                </div>`).join('') : '<div class="text-muted small py-3 text-center">Belum ada data</div>';

            const attention = [
                { title: 'Gaji Belum Terisi', icon: 'fa-money-bill-1', color: 'danger', list: eligible.filter(i => num(i.basic_salary) <= 0) },
                { title: 'Rekening Belum Terisi', icon: 'fa-credit-card', color: 'warning', list: eligible.filter(i => !i.rekening_maybank || i.rekening_maybank === '-') },
                { title: 'Skema Custom', icon: 'fa-pen-to-square', color: 'primary', list: eligible.filter(i => i.skema_bonus === 'custom') },
                { title: 'Belum Berhak (Masuk Setelah Periode)', icon: 'fa-user-clock', color: 'secondary', list: notEligible },
                { title: 'Bulan Kerja Prorata (< 12 Bln)', icon: 'fa-hourglass-half', color: 'info', list: eligible.filter(i => num(i.bulan_kerja) < 12) },
                { title: 'Skema 2x / 4x', icon: 'fa-bolt', color: 'success', list: eligible.filter(i => i.skema_bonus === '2x' || i.skema_bonus === '4x') },
                { title: 'Dikecualikan dari Perhitungan', icon: 'fa-user-slash', color: 'secondary', list: excludedList },
            ];
            document.getElementById('info-attention').innerHTML = attention.map(a => {
                const names = a.list.slice(0, 5).map(i => escapeHtml(i.nama_lengkap)).join(', ');
                const more = a.list.length > 5 ? ` <span class="text-muted">+${a.list.length - 5} lainnya</span>` : '';
                return `<div class="col-12 col-md-6">
                    <div class="attention-card">
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <span class="fw-bold small text-${a.color}"><i class="fa-solid ${a.icon} me-1"></i>${a.title}</span>
                            <span class="badge bg-${a.color}">${a.list.length}</span>
                        </div>
                        <div class="small text-muted" style="min-height: 38px;">${a.list.length ? names + more : 'Tidak ada data yang perlu diperhatikan.'}</div>
                    </div>
                </div>`;
            }).join('');

            const contoh = 5000000;
            document.getElementById('info-simulasi-body').innerHTML = [12, 9, 6, 3, 1].map(m => {
                const hitung = (p) => Math.round(contoh * m * p / 12 * 100) / 100;
                return `<tr>
                    <td class="fw-semibold">${m} Bulan</td>
                    <td class="text-end">${formatIDR(hitung(1))}</td>
                    <td class="text-end">${formatIDR(hitung(2))}</td>
                    <td class="text-end">${formatIDR(hitung(4))}</td>
                </tr>`;
            }).join('');

            renderTrendTable();
        }

        function renderTrendTable() {
            const years = Array.from(document.querySelectorAll('#filterTahun option')).map(o => parseInt(o.value)).sort((a, b) => a - b);
            const current = parseInt(document.getElementById('filterTahun').value);
            const body = document.getElementById('info-tahun-body');

            body.innerHTML = years.map((y, idx) => {
                const s = trendCache[y];
                if (!s) {
                    return `<tr><td class="fw-semibold">Desember ${y}</td><td>Jan - Des ${y - 1}</td>
                            <td colspan="5" class="text-muted">Memuat...</td></tr>`;
                }
                const cur = num(s.total_nominal_bonus);
                const prevS = idx > 0 ? trendCache[years[idx - 1]] : null;
                let selisih = '-', persen = '-';
                if (prevS) {
                    const prev = num(prevS.total_nominal_bonus);
                    const d = cur - prev;
                    selisih = `<span class="${d >= 0 ? 'text-success' : 'text-danger'}">${d >= 0 ? '+' : '-'}${formatIDR(Math.abs(d))}</span>`;
                    persen = prev > 0
                        ? `<span class="${d >= 0 ? 'text-success' : 'text-danger'}">${d >= 0 ? '▲' : '▼'} ${Math.abs(d / prev * 100).toFixed(1).replace('.', ',')}%</span>`
                        : '-';
                }
                return `<tr class="${y === current ? 'table-active' : ''}">
                    <td class="fw-semibold">Desember ${y}</td>
                    <td>Jan - Des ${y - 1}</td>
                    <td class="text-end">${s.total_karyawan || 0}</td>
                    <td class="text-end">${formatIDR(s.total_basic_salary)}</td>
                    <td class="text-end fw-bold text-primary">${formatIDR(cur)}</td>
                    <td class="text-end">${selisih}</td>
                    <td class="text-end">${persen}</td>
                </tr>`;
            }).join('');
        }

        function destroyChart(key) {
            if (charts[key]) {
                charts[key].destroy();
                delete charts[key];
            }
        }

        function renderCharts() {
            if (!window.Chart || !cachedSummary) return;

            const eligible = cachedBonusData.filter(x => x.berhak);

            destroyChart('tren');
            const years = Array.from(document.querySelectorAll('#filterTahun option')).map(o => parseInt(o.value)).sort((a, b) => a - b)
                .filter(y => trendCache[y]);
            charts.tren = new Chart(document.getElementById('chart-tren'), {
                type: 'line',
                data: {
                    labels: years.map(y => 'Des ' + y),
                    datasets: [
                        {
                            label: 'Total Bonus (Rp)',
                            data: years.map(y => num(trendCache[y].total_nominal_bonus)),
                            borderColor: '#4f46e5',
                            backgroundColor: 'rgba(79,70,229,0.12)',
                            fill: true,
                            tension: 0.35,
                            pointRadius: 4,
                            yAxisID: 'y'
                        },
                        {
                            label: 'Jumlah Penerima',
                            data: years.map(y => trendCache[y].total_karyawan || 0),
                            borderColor: '#059669',
                            tension: 0.35,
                            pointRadius: 4,
                            yAxisID: 'y1'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: { mode: 'index', intersect: false },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function (ctx) {
                                    return ctx.datasetIndex === 0 ? ' ' + formatIDR(ctx.parsed.y) : ' ' + ctx.parsed.y + ' penerima';
                                }
                            }
                        }
                    },
                    scales: {
                        y: { position: 'left', beginAtZero: true, ticks: { callback: v => shortRupiah(v) } },
                        y1: { position: 'right', beginAtZero: true, grid: { drawOnChartArea: false }, ticks: { precision: 0 } }
                    }
                }
            });

            destroyChart('distribusi');
            const buckets = Array.from({ length: 12 }, () => ({ count: 0, total: 0 }));
            eligible.forEach(i => {
                const m = Math.min(12, Math.max(1, parseInt(i.bulan_kerja) || 1));
                buckets[m - 1].count++;
                buckets[m - 1].total += num(i.nominal_bonus);
            });
            charts.distribusi = new Chart(document.getElementById('chart-distribusi'), {
                type: 'line',
                data: {
                    labels: buckets.map((b, idx) => (idx + 1) + ' bln'),
                    datasets: [
                        {
                            label: 'Jumlah Penerima',
                            data: buckets.map(b => b.count),
                            borderColor: '#0284c7',
                            backgroundColor: 'rgba(2,132,199,0.12)',
                            fill: true,
                            tension: 0.35,
                            pointRadius: 3,
                            yAxisID: 'y'
                        },
                        {
                            label: 'Total Bonus (Rp)',
                            data: buckets.map(b => b.total),
                            borderColor: '#d97706',
                            tension: 0.35,
                            pointRadius: 3,
                            yAxisID: 'y1'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: { mode: 'index', intersect: false },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function (ctx) {
                                    return ctx.datasetIndex === 0 ? ' ' + ctx.parsed.y + ' penerima' : ' ' + formatIDR(ctx.parsed.y);
                                }
                            }
                        }
                    },
                    scales: {
                        y: { position: 'left', beginAtZero: true, ticks: { precision: 0 } },
                        y1: { position: 'right', beginAtZero: true, grid: { drawOnChartArea: false }, ticks: { callback: v => shortRupiah(v) } }
                    }
                }
            });

            destroyChart('kurva');
            const sorted = eligible.slice().sort((a, b) => num(a.nominal_bonus) - num(b.nominal_bonus));
            charts.kurva = new Chart(document.getElementById('chart-kurva'), {
                type: 'line',
                data: {
                    labels: sorted.map((i, idx) => idx + 1),
                    datasets: [{
                        label: 'Nominal Bonus',
                        data: sorted.map(i => num(i.nominal_bonus)),
                        borderColor: '#7c3aed',
                        backgroundColor: 'rgba(124,58,237,0.1)',
                        fill: true,
                        tension: 0.25,
                        pointRadius: sorted.length > 60 ? 0 : 3
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                title: function (ctx) {
                                    const it = sorted[ctx[0].dataIndex];
                                    return it ? it.nama_lengkap : '';
                                },
                                label: function (ctx) { return ' ' + formatIDR(ctx.parsed.y); }
                            }
                        }
                    },
                    scales: {
                        x: { title: { display: true, text: 'Urutan Penerima (terendah → tertinggi)' }, ticks: { maxTicksLimit: 12 } },
                        y: { beginAtZero: true, ticks: { callback: v => shortRupiah(v) } }
                    }
                }
            });
        }

        function onSkemaChange(karyawanId, newSkema) {
            const item = cachedBonusData.find(x => x.karyawan_id === karyawanId);

            if (!item || !item.berhak) {
                showToast('Karyawan ini belum berhak menerima bonus.', false);
                loadData(false);
                return;
            }

            if (newSkema === 'custom' || !item.basic_salary || item.basic_salary <= 0) {
                openEditModal(karyawanId);
                document.getElementById('editSkema').value = newSkema;
                onModalSkemaChanged();
                if (!item.basic_salary || item.basic_salary <= 0) {
                    showToast('Silakan masukkan Basic Salary karyawan terlebih dahulu.', false);
                    setTimeout(() => {
                        const inputGaji = document.getElementById('inputEditBasicSalary');
                        if (inputGaji) inputGaji.focus();
                    }, 400);
                }
                return;
            }

            const tahun = document.getElementById('filterTahun').value;

            const bonusSpan = document.getElementById(`bonus-val-${karyawanId}`);
            if (bonusSpan) {
                bonusSpan.innerHTML = `<span class="spinner-border spinner-border-sm text-primary"></span>`;
            }

            $.ajax({
                url: "{{ route('HR.bonus-tahunan.update-skema') }}",
                type: "POST",
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                data: { karyawan_id: karyawanId, tahun: tahun, skema_bonus: newSkema },
                success: function(res) {
                    if (res.success) {
                        showToast(res.message, true);
                    } else {
                        showToast(res.message || 'Gagal mengubah skema', false);
                    }
                    loadData(false);
                },
                error: function(xhr) {
                    const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message :
                        'Gagal menghubungi server';
                    showToast(msg, false);
                    loadData(false);
                }
            });
        }

        function openEditModal(karyawanId) {
            const item = cachedBonusData.find(x => x.karyawan_id === karyawanId);
            if (!item || !item.berhak) return;

            const tahun = document.getElementById('filterTahun').value;

            document.getElementById('editKaryawanId').value = item.karyawan_id;
            document.getElementById('editTahun').value = tahun;
            document.getElementById('editNamaInput').value = item.nama_lengkap || '';
            document.getElementById('editNipInput').value = item.nip && item.nip !== '-' ? item.nip : '';
            document.getElementById('editJabatanInput').value = item.jabatan && item.jabatan !== '-' ? item.jabatan : '';
            document.getElementById('editDivisiInput').value = item.divisi && item.divisi !== '-' ? item.divisi : '';
            document.getElementById('editRekeningInput').value = item.rekening_maybank && item.rekening_maybank !== '-' ? item.rekening_maybank : '';
            document.getElementById('editStatusInput').value = item.status_karyawan || 'Karyawan Tetap';
            document.getElementById('editTanggalBergabung').value = item.tanggal_bergabung_raw || '';
            document.getElementById('editBulanKerja').value = item.bulan_kerja || 12;
            document.getElementById('editMasaKerjaTeks').value = item.masa_kerja && item.masa_kerja !== '-' ? item.masa_kerja : '';

            const inputBasic = document.getElementById('inputEditBasicSalary');
            inputBasic.value = (item.basic_salary && item.basic_salary > 0) ? Math.round(item.basic_salary) : '';

            document.getElementById('editSkema').value = item.skema_bonus || 'standar';
            document.getElementById('editCustomNominal').value = Math.round(item.nominal_bonus || 0);
            document.getElementById('editCatatan').value = item.catatan || '';

            toggleCustomNominalInput();
            updateLiveCalculationPreview();

            bootstrap.Modal.getOrCreateInstance(document.getElementById('modalEditBonus')).show();
        }

        function onModalSkemaChanged() {
            toggleCustomNominalInput();
            updateLiveCalculationPreview();
        }

        function toggleCustomNominalInput() {
            const skema = document.getElementById('editSkema').value;
            document.getElementById('groupCustomNominal').style.display = (skema === 'custom') ? 'block' : 'none';
        }

        function updateLiveCalculationPreview() {
            const basicSalary = parseFloat(document.getElementById('inputEditBasicSalary').value) || 0;
            const skema = document.getElementById('editSkema').value;
            const customNominal = parseFloat(document.getElementById('editCustomNominal').value) || 0;
            const bulanKerja = Math.min(12, Math.max(0, parseInt(document.getElementById('editBulanKerja').value) || 0));

            let result = 0;
            let explanation = '';

            if (skema === '2x') {
                result = Math.round(basicSalary * bulanKerja * 2 / 12 * 100) / 100;
                explanation = `2 × (${formatIDR(basicSalary)} × ${bulanKerja} bln ÷ 12)`;
            } else if (skema === '4x') {
                result = Math.round(basicSalary * bulanKerja * 4 / 12 * 100) / 100;
                explanation = `4 × (${formatIDR(basicSalary)} × ${bulanKerja} bln ÷ 12)`;
            } else if (skema === 'custom') {
                result = customNominal;
                explanation = `Nominal kustom yang ditentukan secara manual`;
            } else {
                result = Math.round(basicSalary * bulanKerja / 12 * 100) / 100;
                explanation = `(${formatIDR(basicSalary)} × ${bulanKerja} bln ÷ 12)`;
            }

            document.getElementById('liveCalcResult').textContent = formatIDR(result);
            document.getElementById('liveCalcExplanation').textContent = explanation;
        }

        function saveCustomBonus() {
            const karyawanId = document.getElementById('editKaryawanId').value;
            const tahun = document.getElementById('editTahun').value;
            const skema = document.getElementById('editSkema').value;
            const basicSalary = document.getElementById('inputEditBasicSalary').value;
            const customNominal = document.getElementById('editCustomNominal').value;
            const catatan = document.getElementById('editCatatan').value;
            const bulanKerja = parseInt(document.getElementById('editBulanKerja').value) || 0;
            const nama = document.getElementById('editNamaInput').value.trim();

            if (!nama) {
                showToast('Nama lengkap tidak boleh kosong.', false);
                return;
            }

            if (bulanKerja < 1 || bulanKerja > 12) {
                showToast('Bulan kerja harus antara 1 sampai 12.', false);
                return;
            }

            if (skema !== 'custom' && (basicSalary === '' || parseFloat(basicSalary) <= 0)) {
                showToast('Harap masukkan nominal Basic Salary (Gaji) terlebih dahulu.', false);
                const inputGaji = document.getElementById('inputEditBasicSalary');
                if (inputGaji) inputGaji.focus();
                return;
            }

            $.ajax({
                url: "{{ route('HR.bonus-tahunan.update-skema') }}",
                type: "POST",
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                data: {
                    karyawan_id: karyawanId,
                    tahun: tahun,
                    skema_bonus: skema,
                    full_edit: 1,
                    basic_salary: basicSalary === '' ? 0 : basicSalary,
                    custom_nominal: customNominal,
                    catatan: catatan,
                    nama_lengkap: nama,
                    nip: document.getElementById('editNipInput').value,
                    jabatan: document.getElementById('editJabatanInput').value,
                    divisi: document.getElementById('editDivisiInput').value,
                    rekening_maybank: document.getElementById('editRekeningInput').value,
                    status_karyawan: document.getElementById('editStatusInput').value,
                    tanggal_bergabung: document.getElementById('editTanggalBergabung').value,
                    masa_kerja_bulan: bulanKerja,
                    masa_kerja_teks: document.getElementById('editMasaKerjaTeks').value
                },
                success: function(res) {
                    if (res.success) {
                        showToast(res.message, true);
                        const modal = bootstrap.Modal.getInstance(document.getElementById('modalEditBonus'));
                        if (modal) modal.hide();
                        loadData(false);
                    } else {
                        showToast(res.message || 'Gagal menyimpan', false);
                    }
                },
                error: function(xhr) {
                    let errMsg = 'Terjadi kesalahan saat menyimpan';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errMsg = xhr.responseJSON.message;
                    }
                    showToast(errMsg, false);
                }
            });
        }

        function applyBulkScheme(skema) {
            const tahun = document.getElementById('filterTahun').value;
            const labelMap = { 'standar': 'Standar (1x/Prorata)', '2x': '2x Basic Salary', '4x': '4x Basic Salary' };

            if (!confirm(`Atur SEMUA karyawan yang berhak ke skema: ${labelMap[skema]} untuk pencairan Desember ${tahun}?`)) {
                return;
            }

            $.ajax({
                url: "{{ route('HR.bonus-tahunan.bulk-update') }}",
                type: "POST",
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                data: { tahun: tahun, skema_bonus: skema },
                success: function(res) {
                    if (res.success) {
                        showToast(res.message, true);
                        loadData(false);
                    } else {
                        showToast(res.message || 'Gagal bulk update', false);
                    }
                },
                error: function() {
                    showToast('Gagal bulk update skema', false);
                }
            });
        }

        function sendInclude(payload, onDone) {
            $.ajax({
                url: "{{ route('HR.bonus-tahunan.toggle-include') }}",
                type: "POST",
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                data: payload,
                success: function (res) {
                    showToast(res.message, !!res.success);
                    loadData(false);
                },
                error: function (xhr) {
                    const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Gagal menyimpan perubahan.';
                    showToast(msg, false);
                    loadData(false);
                }
            });
        }

        function toggleIkut(karyawanId, ikut) {
            sendInclude({
                karyawan_id: karyawanId,
                tahun: document.getElementById('filterTahun').value,
                ikut: ikut ? 1 : 0
            });
        }

        function bulkIkut(ikut) {
            const ids = currentFiltered.filter(x => x.berhak_asli).map(x => x.karyawan_id);
            if (!ids.length) {
                showToast('Tidak ada karyawan yang bisa diubah pada tampilan saat ini.', false);
                return;
            }
            const aksi = ikut ? 'diikutsertakan' : 'dikeluarkan';
            if (!confirm(`${ids.length} karyawan yang tampil akan ${aksi} dalam perhitungan bonus. Lanjutkan?`)) return;

            sendInclude({
                karyawan_ids: ids,
                tahun: document.getElementById('filterTahun').value,
                ikut: ikut ? 1 : 0
            });
        }

        function recalculateAll() {
            const tahun = document.getElementById('filterTahun').value;
            if (!confirm(`Hitung ulang dan sinkronisasikan seluruh data bonus tahunan untuk pencairan Desember ${tahun}?`)) {
                return;
            }

            $.ajax({
                url: "{{ route('HR.bonus-tahunan.recalculate') }}",
                type: "POST",
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                data: { year: tahun },
                success: function(res) {
                    if (res.success) {
                        showToast(res.message, true);
                        loadData(false);
                    } else {
                        showToast(res.message || 'Gagal menghitung ulang data', false);
                    }
                },
                error: function() {
                    showToast('Gagal menghitung ulang data', false);
                }
            });
        }

        function buildExportQuery() {
            const tahun = document.getElementById('filterTahun').value;
            const divisi = document.getElementById('filterDivisi').value;
            const status = document.getElementById('filterStatus').value;
            const skema = document.getElementById('filterSkema').value;
            const search = document.getElementById('searchKeyword').value;

            return `year=${tahun}&divisi=${encodeURIComponent(divisi)}&status_karyawan=${encodeURIComponent(status)}&skema_bonus=${skema}&search=${encodeURIComponent(search)}&desimal=${tampilDesimal ? 1 : 0}`;
        }

        function exportExcel() {
            window.location.href = `{{ route('HR.bonus-tahunan.export.excel') }}?${buildExportQuery()}`;
        }

        function openExportPdfModal() {
            const eligible = currentFiltered.filter(x => x.berhak);
            const sel = document.getElementById('exportKaryawan');

            sel.innerHTML = eligible.length ?
                eligible.map(x =>
                    `<option value="${x.karyawan_id}">${escapeHtml(x.nama_lengkap)} — ${escapeHtml(x.jabatan)}</option>`)
                .join('') :
                '<option value="">Tidak ada karyawan yang berhak</option>';

            onExportModeChanged();

            bootstrap.Modal.getOrCreateInstance(document.getElementById('modalExportPdf')).show();
        }

        function onExportModeChanged() {
            const mode = document.querySelector('input[name="exportMode"]:checked').value;
            const format = document.querySelector('input[name="exportFormat"]:checked').value;

            document.getElementById('groupExportKaryawan').style.display = (mode === 'per_orang') ? 'block' : 'none';

            document.getElementById('optModeRekap').classList.toggle('active', mode === 'rekap');
            document.getElementById('optModeSemua').classList.toggle('active', mode === 'semua_surat');
            document.getElementById('optModePerOrang').classList.toggle('active', mode === 'per_orang');
            document.getElementById('optFormatLengkap').classList.toggle('active', format === '0');
            document.getElementById('optFormatPolos').classList.toggle('active', format === '1');
        }

        function submitExportPdf() {
            const mode = document.querySelector('input[name="exportMode"]:checked').value;
            const polos = document.querySelector('input[name="exportFormat"]:checked').value;
            const nomor = document.getElementById('exportNomorSurat').value.trim();

            let query = `${buildExportQuery()}&mode=${mode}&polos=${polos}&nomor_surat=${encodeURIComponent(nomor)}`;

            if (mode === 'per_orang') {
                const id = document.getElementById('exportKaryawan').value;
                if (!id) {
                    showToast('Pilih karyawan terlebih dahulu.', false);
                    return;
                }
                query += `&karyawan_id=${id}`;
            }

            const modal = bootstrap.Modal.getInstance(document.getElementById('modalExportPdf'));
            if (modal) modal.hide();

            window.location.href = `{{ route('HR.bonus-tahunan.export.pdf') }}?${query}`;
        }

        document.addEventListener("DOMContentLoaded", function() {
            syncModeButtons();
            initTable();

            document.getElementById('tab-analitik-btn').addEventListener('shown.bs.tab', function () {
                renderCharts();
            });
            document.getElementById('tab-table-btn').addEventListener('shown.bs.tab', function () {
                if (dt) dt.columns.adjust();
            });

            loadData();
        });

        function syncModeButtons() {
            document.getElementById('btnDesimalOn').classList.toggle('active', tampilDesimal);
            document.getElementById('btnDesimalOff').classList.toggle('active', !tampilDesimal);
        }

        function setModeDesimal(on) {
            tampilDesimal = !!on;
            try { localStorage.setItem('bonus_tampil_desimal', on ? '1' : '0'); } catch (e) {}
            syncModeButtons();

            if (!cachedBonusData.length) return;
            applyFilters(false);
            renderAnalytics();
            if (isAnalitikActive()) renderCharts();
        }
    </script>
@endsection
