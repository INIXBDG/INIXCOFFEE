@extends('layout_HR.app')

@section('title', 'Perhitungan THR - INIX HR')

@section('content_HR')
    <div class="container-fluid px-4 py-4">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">

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
                --radius: 12px;
                --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.07), 0 2px 4px -2px rgba(0, 0, 0, 0.05);
                --shadow-lg: 0 10px 25px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -4px rgba(0, 0, 0, 0.05);
            }

            .page-header {
                margin-bottom: 1.5rem;
            }

            .page-title {
                font-size: 1.5rem;
                font-weight: 700;
                color: var(--gray-900);
                margin-bottom: 0.25rem;
                display: flex;
                align-items: center;
                gap: 0.5rem;
            }

            .page-sub {
                color: var(--gray-600);
                font-size: 0.875rem;
            }

            .stat-card {
                border: 1px solid var(--gray-200);
                border-radius: var(--radius);
                background: #ffffff;
                box-shadow: var(--shadow);
                transition: transform 0.2s ease, box-shadow 0.2s ease;
                height: 100%;
            }

            .stat-card:hover {
                transform: translateY(-2px);
                box-shadow: var(--shadow-lg);
            }

            .stat-icon {
                width: 48px;
                height: 48px;
                border-radius: 10px;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 1.4rem;
                flex-shrink: 0;
            }

            .stat-value {
                font-size: 1.4rem;
                font-weight: 700;
                color: var(--gray-900);
                margin: 0.25rem 0 0;
                line-height: 1.2;
            }

            .stat-value.sm {
                font-size: 1.1rem;
            }

            .stat-label {
                color: var(--gray-600);
                font-size: 0.8rem;
                font-weight: 600;
                margin: 0;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }

            .card-shell {
                border: 1px solid var(--gray-200);
                border-radius: var(--radius);
                box-shadow: var(--shadow);
                background: #ffffff;
            }

            .rule-banner {
                background: linear-gradient(135deg, rgba(79, 70, 229, 0.05) 0%, rgba(99, 102, 241, 0.08) 100%);
                border: 1.5px solid rgba(79, 70, 229, 0.2);
                border-radius: var(--radius);
                padding: 1.15rem 1.4rem;
            }

            .rule-badge {
                background: #ffffff;
                border: 1px solid var(--gray-200);
                padding: 0.35rem 0.75rem;
                border-radius: 8px;
                font-size: 0.8rem;
                font-weight: 600;
                color: var(--gray-700);
                display: inline-flex;
                align-items: center;
                gap: 0.4rem;
            }

            .badge-status {
                padding: 0.35rem 0.7rem;
                border-radius: 20px;
                font-size: 0.72rem;
                font-weight: 700;
                letter-spacing: 0.3px;
                display: inline-flex;
                align-items: center;
                gap: 0.3rem;
            }

            .badge-tetap {
                background: #dbeafe;
                color: #1e40af;
            }

            .badge-kontrak {
                background: #fef3c7;
                color: #92400e;
            }

            .badge-probation {
                background: #fee2e2;
                color: #991b1b;
            }

            .badge-manual {
                background: #ede9fe;
                color: #6d28d9;
                border: 1px solid #c4b5fd;
            }

            .badge-full {
                background: #d1fae5;
                color: #065f46;
            }

            .badge-prorata {
                background: #e0f2fe;
                color: #0369a1;
            }

            .badge-zero {
                background: #f3f4f6;
                color: #6b7280;
            }

            .bank-badge {
                background: #fffbeb;
                border: 1px solid #fde68a;
                color: #92400e;
                padding: 0.35rem 0.65rem;
                border-radius: 6px;
                font-size: 0.78rem;
                font-family: 'Courier New', Courier, monospace;
                font-weight: 700;
                display: inline-flex;
                align-items: center;
                gap: 0.4rem;
            }

            .btn-copy {
                cursor: pointer;
                color: #b45309;
                transition: color 0.15s;
            }

            .btn-copy:hover {
                color: #78350f;
            }

            .emp-avatar {
                width: 36px;
                height: 36px;
                border-radius: 50%;
                background: linear-gradient(135deg, var(--pri), var(--pri-dark));
                color: #ffffff;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                font-weight: 700;
                font-size: 0.85rem;
                flex-shrink: 0;
            }

            .btn-pri {
                background: var(--pri);
                border: none;
                color: #ffffff;
                font-weight: 600;
                padding: 0.5rem 1.15rem;
                border-radius: 8px;
                transition: all 0.2s;
                display: inline-flex;
                align-items: center;
                gap: 0.4rem;
            }

            .btn-pri:hover {
                background: var(--pri-dark);
                color: #ffffff;
                transform: translateY(-1px);
                box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
            }

            .skeleton-box {
                background: linear-gradient(90deg, #f3f4f6 25%, #e5e7eb 50%, #f3f4f6 75%);
                background-size: 200% 100%;
                animation: skeleton-pulse 1.5s infinite;
                border-radius: 6px;
                display: block;
            }

            @keyframes skeleton-pulse {
                0% {
                    background-position: 200% 0;
                }

                100% {
                    background-position: -200% 0;
                }
            }

            .table-responsive {
                border-radius: var(--radius);
            }

            .table thead th {
                font-size: 0.75rem;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: 0.5px;
                color: var(--gray-600);
                background: var(--gray-50);
                border-bottom: 2px solid var(--gray-200) !important;
                border-top: none !important;
                padding: 0.75rem 1rem;
                white-space: nowrap;
            }

            .table tbody tr {
                transition: background 0.15s;
            }

            .table tbody tr:hover {
                background-color: var(--pri-light) !important;
            }

            .table tbody td {
                vertical-align: middle;
                font-size: 0.85rem;
                border-bottom: 1px solid var(--gray-100) !important;
                border-top: none !important;
                padding: 0.75rem 1rem;
            }

            .thr-tabs {
                border-bottom: 2px solid var(--gray-200);
                gap: 0.25rem;
                margin-bottom: 1.25rem;
            }

            .thr-tabs .nav-link {
                border: none;
                border-bottom: 3px solid transparent;
                margin-bottom: -2px;
                color: var(--gray-600);
                font-weight: 600;
                font-size: 0.9rem;
                padding: 0.7rem 1.25rem;
                display: inline-flex;
                align-items: center;
                gap: 0.45rem;
                background: transparent;
            }

            .thr-tabs .nav-link:hover {
                color: var(--pri);
            }

            .thr-tabs .nav-link.active {
                color: var(--pri);
                border-bottom-color: var(--pri);
                background: transparent;
            }

            .dt-wrap .dt-top,
            .dt-wrap .dt-bottom {
                display: flex;
                flex-wrap: wrap;
                align-items: center;
                justify-content: space-between;
                gap: 0.75rem;
                padding: 0.85rem 1.1rem;
            }

            .dt-wrap .dt-top {
                border-bottom: 1px solid var(--gray-100);
            }

            .dt-wrap .dt-bottom {
                border-top: 1px solid var(--gray-100);
            }

            .dt-wrap div.dataTables_wrapper div.dataTables_length label,
            .dt-wrap div.dataTables_wrapper div.dataTables_info {
                font-size: 0.8rem;
                color: var(--gray-600);
                margin: 0;
                padding: 0;
            }

            .dt-wrap div.dataTables_wrapper div.dataTables_length select {
                width: auto;
                display: inline-block;
            }

            .dt-wrap div.dataTables_wrapper div.dataTables_paginate {
                margin: 0;
            }

            .dt-wrap .page-link {
                font-size: 0.8rem;
                font-weight: 600;
            }

            .dt-wrap .page-item.active .page-link {
                background: var(--pri);
                border-color: var(--pri);
            }

            table.dataTable thead .sorting,
            table.dataTable thead .sorting_asc,
            table.dataTable thead .sorting_desc {
                cursor: pointer;
            }

            .section-title {
                font-size: 0.95rem;
                font-weight: 700;
                color: var(--gray-900);
                margin: 0;
                display: flex;
                align-items: center;
                gap: 0.4rem;
            }

            .section-sub {
                font-size: 0.75rem;
                color: var(--gray-600);
                margin: 0;
            }

            .chart-box {
                position: relative;
                height: 290px;
            }

            .mini-table th {
                font-size: 0.7rem;
                text-transform: uppercase;
                letter-spacing: 0.4px;
                color: var(--gray-600);
                background: var(--gray-50);
                font-weight: 700;
                white-space: nowrap;
            }

            .mini-table td,
            .mini-table th {
                padding: 0.55rem 0.75rem;
                font-size: 0.8rem;
                vertical-align: middle;
            }

            .bar-track {
                height: 6px;
                background: var(--gray-100);
                border-radius: 20px;
                overflow: hidden;
                min-width: 70px;
            }

            .bar-fill {
                height: 100%;
                background: linear-gradient(90deg, var(--pri), #818cf8);
                border-radius: 20px;
            }

            .info-tile {
                border: 1px solid var(--gray-200);
                border-radius: 10px;
                padding: 0.85rem 1rem;
                background: var(--gray-50);
                height: 100%;
            }

            .info-tile .k {
                font-size: 0.7rem;
                text-transform: uppercase;
                letter-spacing: 0.5px;
                color: var(--gray-600);
                font-weight: 700;
            }

            .info-tile .v {
                font-size: 1rem;
                font-weight: 700;
                color: var(--gray-900);
            }

            .attention-card {
                border: 1px solid var(--gray-200);
                border-radius: 10px;
                padding: 0.9rem 1rem;
                height: 100%;
                background: #fff;
            }

            .rank-no {
                width: 24px;
                height: 24px;
                border-radius: 50%;
                background: var(--pri-light);
                color: var(--pri);
                font-size: 0.72rem;
                font-weight: 700;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                flex-shrink: 0;
            }

            .accordion-button:not(.collapsed) {
                background: var(--pri-light);
                color: var(--pri-dark);
            }

            .accordion-button {
                font-size: 0.85rem;
                font-weight: 600;
            }

            .accordion-body {
                font-size: 0.82rem;
                color: var(--gray-700);
            }
        </style>

        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between page-header gap-3">
            <div>
                <h1 class="page-title">
                    <i class="iconify text-primary" data-icon="mdi:gift-outline" data-width="28" data-height="28"></i>
                    Perhitungan Tunjangan Hari Raya (THR)
                </h1>
                <p class="page-sub mb-0">
                    Kelompok Administrasi &bull; Perhitungan otomatis masa kerja s/d tanggal jatuh tempo/Idulfitri &
                    penyesuaian manual
                </p>
            </div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <button type="button" class="btn btn-outline-secondary btn-sm" id="btn-refresh" title="Muat Ulang Data">
                    <i class="iconify" data-icon="mdi:refresh" data-width="18" data-height="18"></i>
                    Refresh
                </button>
                <button type="button" class="btn btn-outline-primary btn-sm" id="btn-quick-recalculate"
                    title="Hitung ulang otomatis berdasarkan ketentuan terkini">
                    <i class="iconify" data-icon="mdi:calculator" data-width="18" data-height="18"></i>
                    Hitung Ulang
                </button>
                <button type="button" class="btn btn-outline-success btn-sm" id="btn-export-excel"
                    title="Ekspor Data ke File Excel">
                    <i class="iconify" data-icon="mdi:file-excel-outline" data-width="18" data-height="18"></i>
                    Export Excel
                </button>
                <button type="button" class="btn btn-outline-danger btn-sm" id="btn-delete-period"
                    title="Hapus Periode Ini">
                    <i class="iconify" data-icon="mdi:trash-can-outline" data-width="18" data-height="18"></i>
                    Hapus Periode
                </button>
                <button type="button" class="btn btn-pri btn-sm" id="btn-open-create-period">
                    <i class="iconify" data-icon="mdi:plus-circle-outline" data-width="18" data-height="18"></i>
                    Periode Baru
                </button>
            </div>
        </div>

        <div id="thr-skeleton">
            <div class="row g-3 mb-4">
                @for ($i = 0; $i < 4; $i++)
                    <div class="col-12 col-sm-6 col-xl-3">
                        <div class="card stat-card p-3">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <span class="skeleton-box mb-2" style="width: 100px; height: 12px;"></span>
                                    <span class="skeleton-box" style="width: 140px; height: 26px;"></span>
                                </div>
                                <span class="skeleton-box rounded-3" style="width: 48px; height: 48px;"></span>
                            </div>
                        </div>
                    </div>
                @endfor
            </div>

            <div class="card card-shell p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="skeleton-box" style="width: 200px; height: 34px;"></span>
                    <span class="skeleton-box" style="width: 250px; height: 34px;"></span>
                </div>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                @for ($col = 0; $col < 10; $col++)
                                    <th><span class="skeleton-box" style="width: 80%; height: 14px;"></span></th>
                                @endfor
                            </tr>
                        </thead>
                        <tbody>
                            @for ($row = 0; $row < 6; $row++)
                                <tr>
                                    @for ($col = 0; $col < 10; $col++)
                                        <td><span class="skeleton-box" style="width: 85%; height: 16px;"></span></td>
                                    @endfor
                                </tr>
                            @endfor
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div id="thr-real-content" style="display: none;">

            <div class="card card-shell p-3 mb-3">
                <div class="row g-3 align-items-center">
                    <div class="col-12 col-md-4">
                        <label class="form-label small fw-bold text-muted mb-1">Pilih Periode THR</label>
                        <select class="form-select form-select-sm" id="select-period"></select>
                    </div>
                    <div class="col-12 col-md-8 d-flex align-items-end">
                        <div class="d-flex flex-wrap gap-2 align-items-center small text-muted">
                            <span class="badge bg-primary text-white" id="badge-periode-tahun">2026</span>
                            <strong class="text-dark" id="rule-nama-periode">-</strong>
                            <span>&bull; Jatuh tempo: <strong class="text-dark"
                                    id="rule-tanggal-jatuh-tempo-top">-</strong></span>
                        </div>
                    </div>
                </div>
            </div>

            <ul class="nav thr-tabs" id="thrTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="tab-table-btn" data-bs-toggle="tab" data-bs-target="#tab-table"
                        type="button" role="tab" aria-controls="tab-table" aria-selected="true">
                        <i class="iconify" data-icon="mdi:table-large"></i> Tabel Data THR
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tab-analitik-btn" data-bs-toggle="tab" data-bs-target="#tab-analitik"
                        type="button" role="tab" aria-controls="tab-analitik" aria-selected="false">
                        <i class="iconify" data-icon="mdi:chart-line"></i> Dashboard & Analitik Bonus Tahunan
                    </button>
                </li>
            </ul>

            <div class="tab-content">

                <div class="tab-pane fade show active" id="tab-table" role="tabpanel" aria-labelledby="tab-table-btn"
                    tabindex="0">

                    <div class="card card-shell p-3 mb-3">
                        <div class="row g-3 align-items-end">
                            <div class="col-12 col-md-3">
                                <label class="form-label small fw-bold text-muted mb-1">Filter Status Karyawan</label>
                                <select class="form-select form-select-sm" id="filter-status">
                                    <option value="all">Semua Status (Tetap, Kontrak, Probation)</option>
                                    <option value="Tetap">Karyawan Tetap</option>
                                    <option value="Kontrak">Karyawan Kontrak</option>
                                    <option value="Probation">Probation</option>
                                </select>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label small fw-bold text-muted mb-1">Filter Kategori THR</label>
                                <select class="form-select form-select-sm" id="filter-kategori">
                                    <option value="all">Semua Kesesuaian Ketentuan</option>
                                    <option value="full">1x Gaji Penuh (&ge; Ketentuan)</option>
                                    <option value="prorata">Prorata (&lt; Ketentuan)</option>
                                    <option value="manual">Diedit Manual</option>
                                    <option value="ineligible">Belum Memenuhi Ketentuan</option>
                                </select>
                            </div>
                            <div class="col-12 col-md-5">
                                <label class="form-label small fw-bold text-muted mb-1">Cari Karyawan</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-white"><i class="iconify"
                                            data-icon="mdi:magnify"></i></span>
                                    <input type="text" class="form-control" id="input-search"
                                        placeholder="Cari nama, jabatan, NIP, divisi, rekening...">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card card-shell mb-4 dt-wrap">
                        <div
                            class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <div>
                                <h6 class="fw-bold mb-0 text-gray-900">Daftar Penerima THR</h6>
                                <small class="text-muted">Masa kerja dihitung sampai dengan tanggal jatuh tempo/Idulfitri
                                    sesuai ketentuan aktif</small>
                            </div>
                            <span class="badge bg-light text-dark border" id="badge-row-count">0 Data Ditemukan</span>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0 w-100" id="table-thr">
                                <thead>
                                    <tr>
                                        <th style="width: 45px;" class="text-center">No</th>
                                        <th>Nama Lengkap</th>
                                        <th>Jabatan</th>
                                        <th>Status Karyawan</th>
                                        <th>Rekening Maybank</th>
                                        <th>Masa Kerja</th>
                                        <th class="text-center">Kesesuaian Ketentuan</th>
                                        <th class="text-end">Basic Salary</th>
                                        <th class="text-end">Nilai THR</th>
                                        <th style="width: 85px;" class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>

                        <div class="card-footer bg-white py-2 border-top">
                            <small class="text-muted">
                                Total Anggaran Tampil: <strong class="text-primary fs-6" id="footer-total-tampil">Rp
                                    0</strong>
                            </small>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="tab-analitik" role="tabpanel" aria-labelledby="tab-analitik-btn"
                    tabindex="0">

                    <div class="card card-shell p-4 mb-4 rule-banner">
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                            <div>
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <span class="badge bg-primary text-white" id="analitik-badge-tahun">2026</span>
                                    <h5 class="fw-bold text-gray-900 mb-0" id="analitik-nama-periode">-</h5>
                                </div>
                                <p class="text-muted small mb-0" id="analitik-catatan">
                                    Ketentuan kalkulasi berlaku untuk seluruh karyawan aktif. Nilai dapat disesuaikan per
                                    individu.
                                </p>
                            </div>
                            <div class="text-md-end">
                                <div class="small text-muted">Status Jatuh Tempo</div>
                                <div class="fw-bold fs-5 text-primary" id="analitik-countdown">-</div>
                            </div>
                        </div>

                        <hr class="my-3 opacity-25">

                        <div class="d-flex flex-wrap gap-2 align-items-center">
                            <div class="rule-badge">
                                <i class="iconify text-primary" data-icon="mdi:calendar-star"></i>
                                <span>Tgl Jatuh Tempo / Idulfitri: <strong class="text-dark"
                                        id="rule-tanggal-jatuh-tempo">-</strong></span>
                            </div>
                            <div class="rule-badge">
                                <i class="iconify text-success" data-icon="mdi:shield-check-outline"></i>
                                <span>Masa Kerja Penuh: <strong class="text-dark">12 Bulan (1x Gaji)</strong></span>
                            </div>
                            <div class="rule-badge">
                                <i class="iconify text-info" data-icon="mdi:information-outline"></i>
                                <span>Prorata: <strong class="text-dark" id="rule-formula-text">(Gaji / 12) &times;
                                        Masa Kerja (Bulan)</strong></span>
                            </div>
                            <div class="rule-badge">
                                <i class="iconify text-warning" data-icon="mdi:account-check-outline"></i>
                                <span>Ketentuan: <strong class="text-dark">Tanpa Batas Min. Masa Kerja</strong></span>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-12 col-sm-6 col-xl-3">
                            <div class="card stat-card p-3">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <p class="stat-label">Total Anggaran THR</p>
                                        <h3 class="stat-value text-primary" id="stat-total-anggaran">Rp 0</h3>
                                        <small class="text-muted"><span id="stat-total-tersedia-gaji">0</span> Karyawan
                                            Terisi Gaji</small>
                                    </div>
                                    <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                                        <i class="iconify" data-icon="mdi:cash-multiple"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 col-xl-3">
                            <div class="card stat-card p-3">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <p class="stat-label">Total Karyawan</p>
                                        <h3 class="stat-value text-dark" id="stat-total-karyawan">0</h3>
                                        <small class="text-muted"><span id="stat-total-penerima">0</span> Berhak
                                            Menerima</small>
                                    </div>
                                    <div class="stat-icon bg-info bg-opacity-10 text-info">
                                        <i class="iconify" data-icon="mdi:account-group-outline"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 col-xl-3">
                            <div class="card stat-card p-3">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <p class="stat-label">1x Gaji Penuh (&ge; Ketentuan)</p>
                                        <h3 class="stat-value text-success" id="stat-total-full">0</h3>
                                        <small class="text-muted">Masa kerja memenuhi syarat penuh</small>
                                    </div>
                                    <div class="stat-icon bg-success bg-opacity-10 text-success">
                                        <i class="iconify" data-icon="mdi:check-decagram-outline"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 col-xl-3">
                            <div class="card stat-card p-3">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <p class="stat-label">Prorata (&lt; 12 Bulan)</p>
                                        <h3 class="stat-value text-warning" id="stat-total-prorata">0</h3>
                                        <small class="text-muted"><span id="stat-total-manual"
                                                class="fw-semibold text-primary">0</span> Diedit Manual</small>
                                    </div>
                                    <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                                        <i class="iconify" data-icon="mdi:calculator-variant-outline"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-6 col-md-4 col-xl-2">
                            <div class="card stat-card p-3">
                                <p class="stat-label">Rata-rata THR</p>
                                <h3 class="stat-value sm text-dark" id="m-avg">Rp 0</h3>
                                <small class="text-muted">per penerima</small>
                            </div>
                        </div>
                        <div class="col-6 col-md-4 col-xl-2">
                            <div class="card stat-card p-3">
                                <p class="stat-label">Median THR</p>
                                <h3 class="stat-value sm text-dark" id="m-median">Rp 0</h3>
                                <small class="text-muted">nilai tengah</small>
                            </div>
                        </div>
                        <div class="col-6 col-md-4 col-xl-2">
                            <div class="card stat-card p-3">
                                <p class="stat-label">THR Tertinggi</p>
                                <h3 class="stat-value sm text-success" id="m-max">Rp 0</h3>
                                <small class="text-muted text-truncate d-block" id="m-max-name">-</small>
                            </div>
                        </div>
                        <div class="col-6 col-md-4 col-xl-2">
                            <div class="card stat-card p-3">
                                <p class="stat-label">THR Terendah</p>
                                <h3 class="stat-value sm text-danger" id="m-min">Rp 0</h3>
                                <small class="text-muted text-truncate d-block" id="m-min-name">-</small>
                            </div>
                        </div>
                        <div class="col-6 col-md-4 col-xl-2">
                            <div class="card stat-card p-3">
                                <p class="stat-label">Total Basic Salary</p>
                                <h3 class="stat-value sm text-dark" id="m-total-basic">Rp 0</h3>
                                <small class="text-muted">akumulasi gaji</small>
                            </div>
                        </div>
                        <div class="col-6 col-md-4 col-xl-2">
                            <div class="card stat-card p-3">
                                <p class="stat-label">Rasio THR / Basic</p>
                                <h3 class="stat-value sm text-primary" id="m-rasio">0%</h3>
                                <small class="text-muted">beban THR terhadap gaji</small>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-12 col-lg-6">
                            <div class="card card-shell p-3 h-100">
                                <div class="mb-2">
                                    <h6 class="section-title"><i class="iconify text-primary"
                                            data-icon="mdi:chart-line"></i> Tren Anggaran THR Antar Periode</h6>
                                    <p class="section-sub">Total anggaran (Rp) dan jumlah karyawan penerima per periode</p>
                                </div>
                                <div class="chart-box"><canvas id="chart-tren-periode"></canvas></div>
                            </div>
                        </div>
                        <div class="col-12 col-lg-6">
                            <div class="card card-shell p-3 h-100">
                                <div class="mb-2">
                                    <h6 class="section-title"><i class="iconify text-success"
                                            data-icon="mdi:chart-timeline-variant"></i> Distribusi Berdasarkan Masa Kerja
                                    </h6>
                                    <p class="section-sub">Jumlah karyawan dan total THR per bulan masa kerja (0 - 12
                                        bulan)</p>
                                </div>
                                <div class="chart-box"><canvas id="chart-distribusi"></canvas></div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="card card-shell p-3">
                                <div class="mb-2">
                                    <h6 class="section-title"><i class="iconify text-warning"
                                            data-icon="mdi:chart-bell-curve-cumulative"></i> Kurva Nilai THR Seluruh
                                        Karyawan (Terurut)</h6>
                                    <p class="section-sub">Profil nilai THR dari terendah hingga tertinggi untuk melihat
                                        sebaran dan kesenjangan nilai</p>
                                </div>
                                <div class="chart-box"><canvas id="chart-kurva"></canvas></div>
                            </div>
                        </div>
                    </div>

                    <div class="card card-shell p-3 mb-4">
                        <div class="mb-3">
                            <h6 class="section-title"><i class="iconify text-primary"
                                    data-icon="mdi:information-variant-circle-outline"></i> Ringkasan Informasi Bonus
                                Tahunan (THR)</h6>
                            <p class="section-sub">Rangkuman lengkap periode aktif</p>
                        </div>
                        <div class="row g-3" id="info-ringkasan"></div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-12 col-xl-5">
                            <div class="card card-shell p-3 h-100">
                                <h6 class="section-title mb-1"><i class="iconify text-primary"
                                        data-icon="mdi:badge-account-horizontal-outline"></i> Rincian Berdasarkan Status
                                    Karyawan</h6>
                                <p class="section-sub mb-2">Jumlah, akumulasi basic salary, dan total THR per status</p>
                                <div class="table-responsive">
                                    <table class="table mini-table mb-0">
                                        <thead>
                                            <tr>
                                                <th>Status</th>
                                                <th class="text-end">Karyawan</th>
                                                <th class="text-end">Total Basic</th>
                                                <th class="text-end">Total THR</th>
                                                <th class="text-end">Rata-rata</th>
                                                <th>Porsi</th>
                                            </tr>
                                        </thead>
                                        <tbody id="info-status-body"></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-xl-7">
                            <div class="card card-shell p-3 h-100">
                                <h6 class="section-title mb-1"><i class="iconify text-info"
                                        data-icon="mdi:office-building-outline"></i> Rincian Berdasarkan Divisi</h6>
                                <p class="section-sub mb-2">Alokasi anggaran THR tiap divisi (diurutkan dari terbesar)</p>
                                <div class="table-responsive" style="max-height: 340px; overflow-y: auto;">
                                    <table class="table mini-table mb-0">
                                        <thead>
                                            <tr>
                                                <th>Divisi</th>
                                                <th class="text-end">Karyawan</th>
                                                <th class="text-end">Total THR</th>
                                                <th class="text-end">Rata-rata</th>
                                                <th>Porsi Anggaran</th>
                                            </tr>
                                        </thead>
                                        <tbody id="info-divisi-body"></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-12 col-xl-5">
                            <div class="card card-shell p-3 h-100">
                                <h6 class="section-title mb-1"><i class="iconify text-success"
                                        data-icon="mdi:trophy-outline"></i> 10 Penerima THR Tertinggi</h6>
                                <p class="section-sub mb-2">Berdasarkan nilai THR final</p>
                                <div id="info-top-body"></div>
                            </div>
                        </div>
                        <div class="col-12 col-xl-7">
                            <div class="card card-shell p-3 h-100">
                                <h6 class="section-title mb-1"><i class="iconify text-danger"
                                        data-icon="mdi:alert-circle-outline"></i> Perlu Perhatian HR</h6>
                                <p class="section-sub mb-2">Data yang perlu dilengkapi atau diperiksa sebelum pembayaran
                                </p>
                                <div class="row g-2" id="info-attention"></div>
                            </div>
                        </div>
                    </div>

                    <div class="card card-shell p-3 mb-4">
                        <h6 class="section-title mb-1"><i class="iconify text-primary"
                                data-icon="mdi:compare-horizontal"></i> Perbandingan Antar Periode</h6>
                        <p class="section-sub mb-2">Perkembangan anggaran THR dari periode ke periode</p>
                        <div class="table-responsive">
                            <table class="table mini-table mb-0">
                                <thead>
                                    <tr>
                                        <th>Periode</th>
                                        <th>Tahun</th>
                                        <th>Jatuh Tempo</th>
                                        <th class="text-end">Karyawan</th>
                                        <th class="text-end">Total Anggaran</th>
                                        <th class="text-end">Selisih</th>
                                        <th class="text-end">Perubahan</th>
                                    </tr>
                                </thead>
                                <tbody id="info-periode-body"></tbody>
                            </table>
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-12 col-xl-5">
                            <div class="card card-shell p-3 h-100">
                                <h6 class="section-title mb-1"><i class="iconify text-warning"
                                        data-icon="mdi:calculator-variant-outline"></i> Simulasi Perhitungan Prorata</h6>
                                <p class="section-sub mb-2">Contoh dengan gaji Rp 5.000.000</p>
                                <div class="table-responsive">
                                    <table class="table mini-table mb-0">
                                        <thead>
                                            <tr>
                                                <th>Masa Kerja</th>
                                                <th>Perhitungan</th>
                                                <th class="text-end">THR</th>
                                            </tr>
                                        </thead>
                                        <tbody id="info-simulasi-body"></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-xl-7">
                            <div class="card card-shell p-3 h-100">
                                <h6 class="section-title mb-1"><i class="iconify text-primary"
                                        data-icon="mdi:help-circle-outline"></i> Ketentuan & Pertanyaan Umum</h6>
                                <p class="section-sub mb-2">Penjelasan aturan yang digunakan sistem</p>
                                <div class="accordion" id="faqThr">
                                    <div class="accordion-item">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button" type="button" data-bs-toggle="collapse"
                                                data-bs-target="#faq1">Siapa saja yang dihitung THR-nya?</button>
                                        </h2>
                                        <div id="faq1" class="accordion-collapse collapse show"
                                            data-bs-parent="#faqThr">
                                            <div class="accordion-body">
                                                Seluruh karyawan yang memiliki NIP dan berstatus aktif (status karyawan 1).
                                                Tidak dihitung: karyawan berjabatan Outsource, kode karyawan berawalan OL,
                                                jabatan "Pilih Jabatan", serta karyawan nonaktif.
                                            </div>
                                        </div>
                                    </div>
                                    <div class="accordion-item">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button collapsed" type="button"
                                                data-bs-toggle="collapse" data-bs-target="#faq2">Bagaimana masa kerja
                                                dihitung?</button>
                                        </h2>
                                        <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqThr">
                                            <div class="accordion-body">
                                                Titik akhir adalah tanggal Idulfitri periode berjalan. Karyawan yang masuk
                                                sebelum Idulfitri periode sebelumnya dihitung 12 bulan penuh. Karyawan yang
                                                masuk setelahnya dihitung dari tanggal masuk hingga Idulfitri. Karyawan yang
                                                masuk setelah Idulfitri periode ini bermasa kerja 0.
                                            </div>
                                        </div>
                                    </div>
                                    <div class="accordion-item">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button collapsed" type="button"
                                                data-bs-toggle="collapse" data-bs-target="#faq3">Bagaimana pembulatan
                                                bulan dan rumus nilai THR?</button>
                                        </h2>
                                        <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqThr">
                                            <div class="accordion-body">
                                                Sisa hari 15 atau lebih dibulatkan menjadi satu bulan. Masa kerja 12 bulan
                                                atau lebih mendapat 1x gaji. Di bawah 12 bulan: (Gaji / 12) &times; masa
                                                kerja dalam bulan. Basic salary hanya berasal dari gaji, tanpa tunjangan
                                                jabatan.
                                            </div>
                                        </div>
                                    </div>
                                    <div class="accordion-item">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button collapsed" type="button"
                                                data-bs-toggle="collapse" data-bs-target="#faq4">Bagaimana jika data
                                                diedit manual?</button>
                                        </h2>
                                        <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqThr">
                                            <div class="accordion-body">
                                                Seluruh kolom pada baris dapat diedit dan hanya tersimpan pada data THR
                                                periode ini. Data master karyawan dan log gaji tidak berubah. Baris manual
                                                ditandai badge "Manual" dan dipertahankan saat Hitung Ulang. Untuk
                                                mengembalikannya ke hitungan sistem, gunakan opsi reset nilai manual pada
                                                pengaturan periode.
                                            </div>
                                        </div>
                                    </div>
                                    <div class="accordion-item">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button collapsed" type="button"
                                                data-bs-toggle="collapse" data-bs-target="#faq5">Apa yang terjadi saat
                                                periode baru dibuat?</button>
                                        </h2>
                                        <div id="faq5" class="accordion-collapse collapse" data-bs-parent="#faqThr">
                                            <div class="accordion-body">
                                                Sistem menghitung THR seluruh karyawan yang memenuhi syarat dan otomatis
                                                membuat Administrasi Karyawan "Pembayaran tunjangan hari raya karyawan"
                                                dengan deadline pada tanggal Idulfitri.
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

        <div id="thr-empty-state" class="card card-shell p-5 text-center my-4" style="display: none;">
            <div class="my-4">
                <div class="stat-icon bg-primary bg-opacity-10 text-primary mx-auto mb-3"
                    style="width: 64px; height: 64px; font-size: 2rem;">
                    <i class="iconify" data-icon="mdi:gift-outline"></i>
                </div>
                <h4 class="fw-bold text-gray-900">Belum Ada Periode THR</h4>
                <p class="text-muted max-w-md mx-auto" style="max-width: 500px;">
                    Mulai perhitungan Tunjangan Hari Raya (THR) dengan menentukan nama periode, tahun, serta tanggal jatuh
                    tempo/Idulfitri.
                </p>
                <button class="btn btn-pri mt-2" id="btn-empty-create">
                    <i class="iconify" data-icon="mdi:plus-circle-outline"></i> Buat Periode THR Sekarang
                </button>
            </div>
        </div>

    </div>

    <div class="modal fade" id="modal-create-period" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: var(--radius);">
                <div class="modal-header bg-primary text-white py-3">
                    <h5 class="modal-title fw-bold text-white d-flex align-items-center gap-2">
                        <i class="iconify" data-icon="mdi:calendar-plus"></i>
                        Buat Periode THR Baru
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <form id="form-create-period">
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Nama Periode <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="nama_periode" id="create-nama-periode"
                                placeholder="Contoh: THR Idulfitri 1447 H / 2026" required>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-5">
                                <label class="form-label small fw-bold">Tahun <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" name="tahun" id="create-tahun"
                                    value="{{ date('Y') }}" min="2000" max="2100" required>
                            </div>
                            <div class="col-7">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <label class="form-label small fw-bold mb-0">Tgl Jatuh Tempo / Idulfitri <span
                                            class="text-danger">*</span></label>
                                    <button type="button"
                                        class="btn btn-link btn-sm p-0 text-decoration-none small text-primary"
                                        id="btn-fetch-holiday-create"
                                        title="Ambil tanggal Idulfitri dari Tabel Hari Libur">
                                        <i class="iconify" data-icon="mdi:calendar-search"></i> Ambil dari Hari Libur
                                    </button>
                                </div>
                                <input type="date" class="form-control" name="tanggal_jatuh_tempo"
                                    id="create-tanggal-jatuh-tempo" required>
                                <small class="text-muted d-block mt-1" id="create-holiday-hint"
                                    style="font-size: 0.72rem;">Otomatis disinkronkan dari tabel hari_liburs</small>
                            </div>
                        </div>

                        <div class="p-3 bg-light rounded-3 mb-3 border">
                            <h6 class="fw-bold small text-primary mb-1 d-flex align-items-center gap-1">
                                <i class="iconify" data-icon="mdi:information-outline"></i> Aturan Perhitungan Masa Kerja
                            </h6>
                            <small class="text-muted d-block mb-2" style="font-size: 0.75rem;">
                                Tidak ada batasan minimal masa kerja. Karyawan yang bergabung dalam tahun berjalan dihitung
                                dari tanggal masuk s/d Idulfitri. Karyawan lama dihitung 12 bulan penuh (1x gaji).
                            </small>
                            <input type="hidden" name="full_masa_kerja_bulan" id="create-full-months" value="12">
                            <input type="hidden" name="min_masa_kerja_bulan" id="create-min-months" value="0">
                            <div class="d-flex align-items-center gap-2 small text-dark fw-semibold">
                                <i class="iconify text-success" data-icon="mdi:check-circle"></i>
                                <span>Administrasi Karyawan akan dibuat otomatis dengan deadline hari Idulfitri</span>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Catatan / Keterangan (Opsional)</label>
                            <textarea class="form-control" name="catatan" id="create-catatan" rows="2"
                                placeholder="Catatan kebijakan perusahaan mengenai THR ini..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer bg-light py-2 px-4">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary btn-sm" id="btn-submit-create">
                            <i class="iconify" data-icon="mdi:check"></i> Buat & Hitung Otomatis
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modal-setting-period" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: var(--radius);">
                <div class="modal-header bg-dark text-white py-3">
                    <h5 class="modal-title fw-bold text-white d-flex align-items-center gap-2">
                        <i class="iconify" data-icon="mdi:tune"></i>
                        Ketentuan & Aturan Periode THR
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <form id="form-setting-period">
                    <input type="hidden" id="setting-period-id">
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Nama Periode</label>
                            <input type="text" class="form-control" name="nama_periode" id="setting-nama-periode"
                                required>
                        </div>

                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label small fw-bold mb-0">Tanggal Jatuh Tempo / Idulfitri <span
                                        class="text-danger">*</span></label>
                                <button type="button"
                                    class="btn btn-link btn-sm p-0 text-decoration-none small text-primary"
                                    id="btn-fetch-holiday-setting" title="Ambil tanggal Idulfitri dari Tabel Hari Libur">
                                    <i class="iconify" data-icon="mdi:calendar-search"></i> Ambil dari Hari Libur
                                </button>
                            </div>
                            <input type="date" class="form-control" name="tanggal_jatuh_tempo"
                                id="setting-tanggal-jatuh-tempo" required>
                            <small class="text-muted" style="font-size: 0.75rem;">
                                Masa kerja karyawan dihitung secara presisi sampai dengan tanggal ini.
                            </small>
                        </div>

                        <input type="hidden" name="full_masa_kerja_bulan" id="setting-full-months" value="12">
                        <input type="hidden" name="min_masa_kerja_bulan" id="setting-min-months" value="0">

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Catatan Kebijakan</label>
                            <textarea class="form-control" name="catatan" id="setting-catatan" rows="2"></textarea>
                        </div>

                        <div class="p-3 bg-light rounded-3 border">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="setting-recalculate" checked>
                                <label class="form-check-label small fw-bold text-gray-800" for="setting-recalculate">
                                    Hitung ulang otomatis data karyawan saat disimpan
                                </label>
                            </div>
                            <div class="form-check mt-2">
                                <input class="form-check-input" type="checkbox" id="setting-force-reset">
                                <label class="form-check-label small text-danger" for="setting-force-reset">
                                    Reset juga data yang pernah diubah manual kembali ke hitungan sistem
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light py-2 px-4">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary btn-sm" id="btn-submit-setting">
                            <i class="iconify" data-icon="mdi:content-save"></i> Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modal-edit-item" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg" style="border-radius: var(--radius);">
                <div class="modal-header bg-primary text-white py-3">
                    <h5 class="modal-title fw-bold text-white d-flex align-items-center gap-2">
                        <i class="iconify" data-icon="mdi:account-edit"></i>
                        Penyesuaian Manual Data THR
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <form id="form-edit-item">
                    <input type="hidden" id="edit-item-id">
                    <div class="modal-body p-4">
                        <div class="d-flex align-items-center gap-3 mb-3 p-3 bg-light rounded-3 border">
                            <div class="emp-avatar" id="edit-avatar">IN</div>
                            <div>
                                <h6 class="fw-bold mb-0 text-gray-900" id="edit-header-nama">-</h6>
                                <small class="text-muted" id="edit-header-nip">-</small>
                            </div>
                        </div>

                        <div class="alert alert-info py-2 small mb-3">
                            <i class="iconify" data-icon="mdi:information-outline"></i>
                            Perubahan hanya tersimpan pada data THR periode ini. Data master karyawan dan log gaji tidak
                            berubah.
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-12">
                                <label class="form-label small fw-bold">Nama Lengkap <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="edit-nama-lengkap" required>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-bold">Jabatan</label>
                                <input type="text" class="form-control" id="edit-jabatan">
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-bold">Status Karyawan</label>
                                <select class="form-select" id="edit-status">
                                    <option value="Tetap">Tetap</option>
                                    <option value="Kontrak">Kontrak</option>
                                    <option value="Probation">Probation</option>
                                </select>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-bold">Rekening Maybank</label>
                                <input type="text" class="form-control" id="edit-rekening">
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-bold">Tanggal Masuk</label>
                                <input type="date" class="form-control" id="edit-tanggal-join">
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-bold">Masa Kerja (Bulan)</label>
                                <input type="number" step="1" min="0" max="120" class="form-control"
                                    id="edit-masa-bulan">
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-bold">Teks Masa Kerja</label>
                                <input type="text" class="form-control" id="edit-masa-teks"
                                    placeholder="Kosongkan untuk otomatis">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-gray-900 mb-1">Basic Salary (Gaji)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light">Rp</span>
                                <input type="number" step="1000" class="form-control" id="edit-basic-salary"
                                    placeholder="0">
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label small fw-bold text-gray-900 mb-0">Nilai THR Final <span
                                        class="text-danger">*</span></label>
                                <small class="text-muted">Hitungan Sistem: <strong class="text-primary"
                                        id="edit-thr-sistem">Rp 0</strong></small>
                            </div>
                            <div class="input-group input-group-lg">
                                <span class="input-group-text bg-primary text-white fw-bold">Rp</span>
                                <input type="number" step="1000" class="form-control fw-bold fs-5 text-primary"
                                    id="edit-nilai-thr-final" required>
                            </div>
                            <div class="mt-1 d-flex justify-content-between align-items-center">
                                <small class="text-primary fw-bold" id="edit-preview-rupiah">Rp 0</small>
                                <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none text-muted"
                                    id="btn-reset-to-system">
                                    <i class="iconify" data-icon="mdi:restore"></i> Samakan dengan Sistem
                                </button>
                            </div>
                        </div>

                        <div class="mb-2">
                            <label class="form-label small fw-bold">Catatan Penyesuaian</label>
                            <textarea class="form-control" id="edit-catatan" rows="2"
                                placeholder="Alasan penyesuaian (misal: pembulatan, kebijakan direksi, bonus prestasi)..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer bg-light py-2 px-4">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary btn-sm" id="btn-submit-edit-item">
                            <i class="iconify" data-icon="mdi:check"></i> Simpan Penyesuaian
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {

            function loadScript(src) {
                return new Promise(function(resolve, reject) {
                    const s = document.createElement('script');
                    s.src = src;
                    s.onload = resolve;
                    s.onerror = reject;
                    document.head.appendChild(s);
                });
            }

            async function ensureLibs() {
                if (!window.jQuery) {
                    await loadScript('https://code.jquery.com/jquery-3.7.1.min.js');
                }
                if (!window.jQuery.fn.DataTable) {
                    await loadScript('https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js');
                    await loadScript('https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js');
                }
                if (!window.Chart) {
                    await loadScript('https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js');
                }
            }

            ensureLibs().then(initPage).catch(function(err) {
                console.error('Gagal memuat library:', err);
                Swal.fire('Error', 'Gagal memuat library DataTables/Chart. Periksa koneksi internet.',
                    'error');
            });

            function initPage() {
                const $j = window.jQuery;
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                const byId = (id) => document.getElementById(id);

                let currentPeriod = null;
                let allPeriods = [];
                let allItems = [];
                let currentFilteredItems = [];
                let dt = null;
                const charts = {};

                function formatRupiah(number) {
                    const num = parseFloat(number) || 0;
                    return 'Rp ' + Math.round(num).toLocaleString('id-ID');
                }

                function shortRupiah(v) {
                    const n = Math.abs(v);
                    if (n >= 1e9) return 'Rp ' + (v / 1e9).toFixed(1).replace('.', ',') + ' M';
                    if (n >= 1e6) return 'Rp ' + (v / 1e6).toFixed(1).replace('.', ',') + ' jt';
                    if (n >= 1e3) return 'Rp ' + (v / 1e3).toFixed(0) + ' rb';
                    return 'Rp ' + v;
                }

                function formatTanggalIndo(dateStr) {
                    if (!dateStr) return '-';
                    const options = {
                        day: 'numeric',
                        month: 'long',
                        year: 'numeric'
                    };
                    try {
                        return new Date(dateStr).toLocaleDateString('id-ID', options);
                    } catch (e) {
                        return dateStr;
                    }
                }

                function escapeHtml(text) {
                    if (text === null || text === undefined) return '';
                    return String(text)
                        .replace(/&/g, '&amp;')
                        .replace(/</g, '&lt;')
                        .replace(/>/g, '&gt;')
                        .replace(/"/g, '&quot;')
                        .replace(/'/g, '&#039;');
                }

                function getInitials(name) {
                    return (name || 'K').split(' ').slice(0, 2).map(w => w[0]).join('').toUpperCase();
                }

                function ketentuan(item) {
                    if (item.status_ketentuan) return item.status_ketentuan;
                    const full = parseFloat(currentPeriod && currentPeriod.full_masa_kerja_bulan) || 12;
                    const m = parseFloat(item.total_masa_kerja_bulan) || 0;
                    if (m >= full) return 'full';
                    if (m > 0) return 'prorata';
                    return 'ineligible';
                }

                function showToast(message, icon = 'success') {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: icon,
                        title: message,
                        showConfirmButton: false,
                        timer: 2500,
                        timerProgressBar: true
                    });
                }

                function isAnalitikActive() {
                    return byId('tab-analitik').classList.contains('active');
                }

                function initTable() {
                    if (dt) return;

                    dt = $j('#table-thr').DataTable({
                        data: [],
                        pageLength: 10,
                        lengthMenu: [
                            [10, 25, 50, 100, -1],
                            [10, 25, 50, 100, 'Semua']
                        ],
                        searching: false,
                        deferRender: true,
                        autoWidth: false,
                        order: [
                            [1, 'asc']
                        ],
                        dom: '<"dt-top"l>rt<"dt-bottom"ip>',
                        language: {
                            lengthMenu: 'Tampilkan _MENU_ karyawan',
                            info: 'Menampilkan _START_ - _END_ dari _TOTAL_ karyawan',
                            infoEmpty: 'Menampilkan 0 - 0 dari 0 karyawan',
                            infoFiltered: '(disaring dari _MAX_ data)',
                            zeroRecords: 'Tidak ada data karyawan yang sesuai dengan kriteria filter.',
                            emptyTable: 'Tidak ada data karyawan yang sesuai dengan kriteria filter.',
                            paginate: {
                                first: 'Awal',
                                last: 'Akhir',
                                next: '&raquo;',
                                previous: '&laquo;'
                            }
                        },
                        columns: [{
                                data: null,
                                orderable: false,
                                className: 'text-center text-muted small',
                                render: function() {
                                    return '';
                                }
                            },
                            {
                                data: 'nama_lengkap',
                                render: function(d, type, row) {
                                    if (type !== 'display') return d || '';
                                    const nip = row.karyawan && row.karyawan.nip ? row.karyawan
                                        .nip : '-';
                                    return `
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="emp-avatar">${escapeHtml(getInitials(d))}</div>
                                        <div>
                                            <span class="fw-bold text-gray-900 d-block">${escapeHtml(d)}</span>
                                            <small class="text-muted font-monospace" style="font-size: 0.75rem;">NIP: ${escapeHtml(nip)}</small>
                                        </div>
                                    </div>`;
                                }
                            },
                            {
                                data: 'jabatan',
                                render: function(d, type, row) {
                                    if (type !== 'display') return d || '';
                                    const divisi = row.karyawan && row.karyawan.divisi ? row
                                        .karyawan.divisi : '';
                                    return `
                                    <span class="text-gray-800 fw-semibold">${escapeHtml(d || '-')}</span>
                                    <small class="text-muted d-block" style="font-size: 0.72rem;">${escapeHtml(divisi)}</small>`;
                                }
                            },
                            {
                                data: 'status_karyawan',
                                render: function(d, type) {
                                    if (type !== 'display') return d || '';
                                    if (d === 'Kontrak')
                                        return '<span class="badge-status badge-kontrak">Kontrak</span>';
                                    if (d === 'Probation')
                                        return '<span class="badge-status badge-probation">Probation</span>';
                                    return '<span class="badge-status badge-tetap">Tetap</span>';
                                }
                            },
                            {
                                data: 'rekening_maybank',
                                render: function(d, type) {
                                    if (type !== 'display') return d || '';
                                    const rek = d ? d : '-';
                                    const copyBtn = d ? `
                                    <span class="btn-copy" data-rek="${escapeHtml(d)}" title="Salin Rekening">
                                        <i class="iconify" data-icon="mdi:content-copy"></i>
                                    </span>` : '';
                                    return `
                                    <div class="bank-badge">
                                        <i class="iconify text-warning" data-icon="mdi:credit-card-outline"></i>
                                        <span>${escapeHtml(rek)}</span>
                                        ${copyBtn}
                                    </div>`;
                                }
                            },
                            {
                                data: 'total_masa_kerja_bulan',
                                render: function(d, type, row) {
                                    if (type !== 'display') return parseFloat(d) || 0;
                                    return `
                                    <span class="fw-bold text-gray-800">${escapeHtml(row.masa_kerja_teks || '-')}</span>
                                    <small class="text-muted d-block" style="font-size: 0.72rem;">Masuk: ${formatTanggalIndo(row.tanggal_join)}</small>`;
                                }
                            },
                            {
                                data: null,
                                className: 'text-center',
                                render: function(d, type, row) {
                                    const k = ketentuan(row);
                                    if (type !== 'display') return row.is_manual ? 'manual' : k;
                                    if (row.is_manual) {
                                        return '<span class="badge-status badge-manual" title="Disesuaikan secara manual"><i class="iconify" data-icon="mdi:pencil"></i> Manual</span>';
                                    }
                                    if (k === 'full')
                                        return '<span class="badge-status badge-full">1x Gaji</span>';
                                    if (k === 'prorata')
                                        return '<span class="badge-status badge-prorata">Prorata</span>';
                                    return '<span class="badge-status badge-zero">Belum Memenuhi</span>';
                                }
                            },
                            {
                                data: 'basic_salary',
                                className: 'text-end font-monospace',
                                render: function(d, type) {
                                    if (type !== 'display') return parseFloat(d) || 0;
                                    return `<span class="text-muted">${formatRupiah(d)}</span>`;
                                }
                            },
                            {
                                data: 'nilai_thr_final',
                                className: 'text-end',
                                render: function(d, type) {
                                    if (type !== 'display') return parseFloat(d) || 0;
                                    return `<span class="fw-bold fs-6 text-primary font-monospace">${formatRupiah(d)}</span>`;
                                }
                            },
                            {
                                data: 'id',
                                orderable: false,
                                className: 'text-center',
                                render: function(d) {
                                    return `<button type="button" class="btn btn-sm btn-outline-primary btn-edit-item" data-id="${d}" title="Edit Data THR">
                                            <i class="iconify" data-icon="mdi:pencil"></i> Edit
                                        </button>`;
                                }
                            }
                        ],
                        drawCallback: function() {
                            const api = this.api();
                            const start = api.page.info().start;
                            api.column(0, {
                                page: 'current'
                            }).nodes().each(function(cell, i) {
                                cell.innerHTML = start + i + 1;
                            });
                        }
                    });

                    $j('#table-thr tbody').on('click', '.btn-edit-item', function() {
                        openEditModal($j(this).data('id'));
                    });

                    $j('#table-thr tbody').on('click', '.btn-copy', function() {
                        copyToClipboard($j(this).data('rek'));
                    });
                }

                function copyToClipboard(text) {
                    navigator.clipboard.writeText(String(text)).then(() => {
                        showToast('Nomor rekening disalin!', 'success');
                    }).catch(() => {
                        showToast('Gagal menyalin', 'error');
                    });
                }

                function applyFiltersAndRender(resetPage = true) {
                    initTable();

                    const search = byId('input-search').value.toLowerCase().trim();
                    const statusFilter = byId('filter-status').value;
                    const kategoriFilter = byId('filter-kategori').value;

                    currentFilteredItems = allItems.filter(item => {
                        if (search) {
                            const nama = (item.nama_lengkap || '').toLowerCase();
                            const jabatan = (item.jabatan || '').toLowerCase();
                            const nip = (item.karyawan && item.karyawan.nip ? item.karyawan.nip : '')
                                .toLowerCase();
                            const divisi = (item.karyawan && item.karyawan.divisi ? item.karyawan.divisi :
                                '').toLowerCase();
                            const rekening = (item.rekening_maybank || '').toLowerCase();
                            const match = nama.includes(search) || jabatan.includes(search) || nip.includes(
                                    search) ||
                                divisi.includes(search) || rekening.includes(search);
                            if (!match) return false;
                        }

                        if (statusFilter !== 'all' && item.status_karyawan !== statusFilter) return false;

                        if (kategoriFilter !== 'all') {
                            if (kategoriFilter === 'manual' && !item.is_manual) return false;
                            if (kategoriFilter === 'full' && ketentuan(item) !== 'full') return false;
                            if (kategoriFilter === 'prorata' && ketentuan(item) !== 'prorata') return false;
                            if (kategoriFilter === 'ineligible' && ketentuan(item) !== 'ineligible')
                            return false;
                        }

                        return true;
                    });

                    dt.clear();
                    dt.rows.add(currentFilteredItems);
                    dt.draw(resetPage);

                    byId('badge-row-count').textContent = `${currentFilteredItems.length} Data Ditemukan`;
                    const total = currentFilteredItems.reduce((a, i) => a + (parseFloat(i.nilai_thr_final) || 0),
                    0);
                    byId('footer-total-tampil').textContent = formatRupiah(total);
                }

                loadPeriods();

                function loadPeriods(selectTargetId = null) {
                    fetch('{{ route('HR.thr.periods') }}')
                        .then(res => res.json())
                        .then(res => {
                            if (res.status === 'success') {
                                allPeriods = res.data || [];
                                renderPeriodSelector(selectTargetId);
                                if (allPeriods.length > 0) {
                                    const targetId = selectTargetId || allPeriods[0].id;
                                    loadData(targetId);
                                } else {
                                    currentPeriod = null;
                                    byId('thr-skeleton').style.display = 'none';
                                    byId('thr-real-content').style.display = 'none';
                                    byId('thr-empty-state').style.display = 'block';
                                }
                            }
                        })
                        .catch(err => {
                            console.error('Error loading periods:', err);
                            showToast('Gagal memuat data periode', 'error');
                        });
                }

                function renderPeriodSelector(selectedId = null) {
                    const selector = byId('select-period');
                    selector.innerHTML = '';
                    allPeriods.forEach((p, idx) => {
                        const opt = document.createElement('option');
                        opt.value = p.id;
                        opt.textContent = `${p.nama_periode} (${p.tahun})`;
                        if ((selectedId && p.id == selectedId) || (!selectedId && idx === 0)) opt.selected =
                            true;
                        selector.appendChild(opt);
                    });
                }

                function loadData(periodId) {
                    byId('thr-skeleton').style.display = 'block';
                    byId('thr-real-content').style.display = 'none';
                    byId('thr-empty-state').style.display = 'none';

                    fetch(`{{ route('HR.thr.data') }}?period_id=${periodId}`)
                        .then(res => res.json())
                        .then(res => {
                            if (res.status === 'success') {
                                currentPeriod = res.period;
                                allItems = res.items || [];

                                if (!currentPeriod) {
                                    byId('thr-skeleton').style.display = 'none';
                                    byId('thr-empty-state').style.display = 'block';
                                    return;
                                }

                                byId('select-period').value = currentPeriod.id;

                                byId('thr-skeleton').style.display = 'none';
                                byId('thr-real-content').style.display = 'block';

                                renderStats(res.stats);
                                renderRuleBanner(res.period);
                                applyFiltersAndRender();
                                renderAnalytics(res.stats);

                                if (dt) dt.columns.adjust();
                                if (isAnalitikActive()) renderCharts();
                            }
                        })
                        .catch(err => {
                            console.error('Error loading data:', err);
                            showToast('Gagal memuat rincian THR', 'error');
                            byId('thr-skeleton').style.display = 'none';
                        });
                }

                function renderStats(stats) {
                    byId('stat-total-anggaran').textContent = formatRupiah(stats.total_anggaran);
                    byId('stat-total-tersedia-gaji').textContent = stats.total_tersedia_gaji ?? 0;
                    byId('stat-total-karyawan').textContent = stats.total_karyawan;
                    byId('stat-total-penerima').textContent = stats.total_penerima;
                    byId('stat-total-full').textContent = stats.total_full;
                    byId('stat-total-prorata').textContent = stats.total_prorata;
                    byId('stat-total-manual').textContent = stats.total_manual;
                }

                function renderRuleBanner(period) {
                    const tgl = formatTanggalIndo(period.tanggal_jatuh_tempo);
                    byId('badge-periode-tahun').textContent = period.tahun;
                    byId('rule-nama-periode').textContent = period.nama_periode;
                    byId('rule-tanggal-jatuh-tempo-top').textContent = tgl;
                    byId('analitik-badge-tahun').textContent = period.tahun;
                    byId('analitik-nama-periode').textContent = period.nama_periode;
                    byId('rule-tanggal-jatuh-tempo').textContent = tgl;
                    byId('analitik-catatan').textContent = period.catatan ?
                        period.catatan :
                        'Ketentuan kalkulasi berlaku untuk seluruh karyawan aktif. Nilai dapat disesuaikan per individu.';
                    byId('rule-formula-text').textContent = '(Gaji / 12) × Masa Kerja (Bulan)';

                    const due = new Date(period.tanggal_jatuh_tempo);
                    const today = new Date();
                    due.setHours(0, 0, 0, 0);
                    today.setHours(0, 0, 0, 0);
                    const diff = Math.round((due - today) / 86400000);
                    const el = byId('analitik-countdown');
                    if (diff > 0) {
                        el.textContent = `H-${diff} (${diff} hari lagi)`;
                        el.className = 'fw-bold fs-5 text-primary';
                    } else if (diff === 0) {
                        el.textContent = 'Hari ini';
                        el.className = 'fw-bold fs-5 text-warning';
                    } else {
                        el.textContent = `Lewat ${Math.abs(diff)} hari`;
                        el.className = 'fw-bold fs-5 text-muted';
                    }
                }

                function median(arr) {
                    if (!arr.length) return 0;
                    const s = arr.slice().sort((a, b) => a - b);
                    const mid = Math.floor(s.length / 2);
                    return s.length % 2 ? s[mid] : (s[mid - 1] + s[mid]) / 2;
                }

                function renderAnalytics(stats) {
                    const items = allItems;
                    const totalThr = items.reduce((a, i) => a + (parseFloat(i.nilai_thr_final) || 0), 0);
                    const totalBasic = items.reduce((a, i) => a + (parseFloat(i.basic_salary) || 0), 0);
                    const receivers = items.filter(i => (parseFloat(i.nilai_thr_final) || 0) > 0);
                    const values = receivers.map(i => parseFloat(i.nilai_thr_final));
                    const avg = receivers.length ? totalThr / receivers.length : 0;

                    let maxItem = null,
                        minItem = null;
                    receivers.forEach(i => {
                        const v = parseFloat(i.nilai_thr_final);
                        if (!maxItem || v > parseFloat(maxItem.nilai_thr_final)) maxItem = i;
                        if (!minItem || v < parseFloat(minItem.nilai_thr_final)) minItem = i;
                    });

                    byId('m-avg').textContent = formatRupiah(avg);
                    byId('m-median').textContent = formatRupiah(median(values));
                    byId('m-max').textContent = formatRupiah(maxItem ? maxItem.nilai_thr_final : 0);
                    byId('m-max-name').textContent = maxItem ? maxItem.nama_lengkap : '-';
                    byId('m-min').textContent = formatRupiah(minItem ? minItem.nilai_thr_final : 0);
                    byId('m-min-name').textContent = minItem ? minItem.nama_lengkap : '-';
                    byId('m-total-basic').textContent = formatRupiah(totalBasic);
                    byId('m-rasio').textContent = (totalBasic > 0 ? (totalThr / totalBasic * 100) : 0).toFixed(1)
                        .replace('.', ',') + '%';

                    const tile = (k, v) =>
                        `<div class="col-6 col-md-4 col-xl-3"><div class="info-tile"><div class="k">${k}</div><div class="v">${v}</div></div></div>`;
                    const manualCount = items.filter(i => i.is_manual).length;
                    const noSalary = items.filter(i => (parseFloat(i.basic_salary) || 0) <= 0).length;
                    const noRek = items.filter(i => !i.rekening_maybank).length;
                    const tetap = items.filter(i => i.status_karyawan === 'Tetap').length;
                    const kontrak = items.filter(i => i.status_karyawan === 'Kontrak').length;
                    const prob = items.filter(i => i.status_karyawan === 'Probation').length;
                    const inel = items.filter(i => ketentuan(i) === 'ineligible').length;
                    const totalManualValue = items.filter(i => i.is_manual).reduce((a, i) => a + (parseFloat(i
                        .nilai_thr_final) || 0), 0);
                    const totalSistem = items.reduce((a, i) => a + (parseFloat(i.nilai_thr_sistem) || 0), 0);
                    const selisihManual = totalThr - totalSistem;

                    byId('info-ringkasan').innerHTML = [
                        tile('Nama Periode', escapeHtml(currentPeriod.nama_periode)),
                        tile('Tanggal Jatuh Tempo', formatTanggalIndo(currentPeriod.tanggal_jatuh_tempo)),
                        tile('Total Anggaran', formatRupiah(totalThr)),
                        tile('Total Hitungan Sistem', formatRupiah(totalSistem)),
                        tile('Selisih Akibat Edit Manual', (selisihManual >= 0 ? '+' : '-') + formatRupiah(Math
                            .abs(selisihManual))),
                        tile('Nilai THR Hasil Edit Manual',
                            `${formatRupiah(totalManualValue)} <small class="text-muted fw-normal">(${manualCount} orang)</small>`
                            ),
                        tile('Karyawan Tetap', tetap),
                        tile('Karyawan Kontrak', kontrak),
                        tile('Karyawan Probation', prob),
                        tile('Masa Kerja 0 (Belum Berhak)', inel),
                        tile('Gaji Belum Terisi', noSalary),
                        tile('Rekening Belum Terisi', noRek),
                        tile('Penerima 1x Gaji Penuh', stats.total_full),
                        tile('Penerima Prorata', stats.total_prorata),
                        tile('Karyawan Berhak', stats.total_penerima),
                        tile('Total Karyawan Terdata', stats.total_karyawan)
                    ].join('');

                    const statusList = ['Tetap', 'Kontrak', 'Probation'];
                    let statusHtml = '';
                    statusList.forEach(st => {
                        const grp = items.filter(i => i.status_karyawan === st);
                        const thr = grp.reduce((a, i) => a + (parseFloat(i.nilai_thr_final) || 0), 0);
                        const bas = grp.reduce((a, i) => a + (parseFloat(i.basic_salary) || 0), 0);
                        const pct = totalThr > 0 ? thr / totalThr * 100 : 0;
                        const cls = st === 'Tetap' ? 'badge-tetap' : (st === 'Kontrak' ? 'badge-kontrak' :
                            'badge-probation');
                        statusHtml += `
                        <tr>
                            <td><span class="badge-status ${cls}">${st}</span></td>
                            <td class="text-end">${grp.length}</td>
                            <td class="text-end font-monospace">${formatRupiah(bas)}</td>
                            <td class="text-end font-monospace fw-bold text-primary">${formatRupiah(thr)}</td>
                            <td class="text-end font-monospace">${formatRupiah(grp.length ? thr / grp.length : 0)}</td>
                            <td>
                                <div class="bar-track"><div class="bar-fill" style="width:${pct.toFixed(1)}%"></div></div>
                                <small class="text-muted">${pct.toFixed(1).replace('.', ',')}%</small>
                            </td>
                        </tr>`;
                    });
                    byId('info-status-body').innerHTML = statusHtml;

                    const divMap = {};
                    items.forEach(i => {
                        const d = (i.karyawan && i.karyawan.divisi) ? i.karyawan.divisi : 'Tanpa Divisi';
                        if (!divMap[d]) divMap[d] = {
                            count: 0,
                            total: 0
                        };
                        divMap[d].count++;
                        divMap[d].total += parseFloat(i.nilai_thr_final) || 0;
                    });
                    const divRows = Object.keys(divMap).map(k => ({
                        nama: k,
                        ...divMap[k]
                    })).sort((a, b) => b.total - a.total);
                    byId('info-divisi-body').innerHTML = divRows.length ? divRows.map(r => {
                            const pct = totalThr > 0 ? r.total / totalThr * 100 : 0;
                            return `
                        <tr>
                            <td class="fw-semibold">${escapeHtml(r.nama)}</td>
                            <td class="text-end">${r.count}</td>
                            <td class="text-end font-monospace fw-bold text-primary">${formatRupiah(r.total)}</td>
                            <td class="text-end font-monospace">${formatRupiah(r.count ? r.total / r.count : 0)}</td>
                            <td>
                                <div class="bar-track"><div class="bar-fill" style="width:${pct.toFixed(1)}%"></div></div>
                                <small class="text-muted">${pct.toFixed(1).replace('.', ',')}%</small>
                            </td>
                        </tr>`;
                        }).join('') :
                        '<tr><td colspan="5" class="text-center text-muted py-3">Belum ada data</td></tr>';

                    const top = items.slice().sort((a, b) => (parseFloat(b.nilai_thr_final) || 0) - (parseFloat(a
                        .nilai_thr_final) || 0)).slice(0, 10);
                    byId('info-top-body').innerHTML = top.length ? top.map((i, idx) => `
                    <div class="d-flex align-items-center justify-content-between py-2 ${idx < top.length - 1 ? 'border-bottom' : ''}">
                        <div class="d-flex align-items-center gap-2">
                            <span class="rank-no">${idx + 1}</span>
                            <div>
                                <div class="fw-semibold small">${escapeHtml(i.nama_lengkap)}</div>
                                <small class="text-muted">${escapeHtml(i.jabatan || '-')} &bull; ${escapeHtml(i.masa_kerja_teks || '-')}</small>
                            </div>
                        </div>
                        <span class="fw-bold text-primary font-monospace small">${formatRupiah(i.nilai_thr_final)}</span>
                    </div>`).join('') : '<div class="text-muted small py-3 text-center">Belum ada data</div>';

                    const attention = [{
                            title: 'Gaji Belum Terisi',
                            icon: 'mdi:cash-remove',
                            color: 'danger',
                            list: items.filter(i => (parseFloat(i.basic_salary) || 0) <= 0)
                        },
                        {
                            title: 'Rekening Belum Terisi',
                            icon: 'mdi:credit-card-off-outline',
                            color: 'warning',
                            list: items.filter(i => !i.rekening_maybank)
                        },
                        {
                            title: 'Diedit Manual',
                            icon: 'mdi:pencil-circle-outline',
                            color: 'primary',
                            list: items.filter(i => i.is_manual)
                        },
                        {
                            title: 'Masa Kerja 0 (Belum Berhak)',
                            icon: 'mdi:account-clock-outline',
                            color: 'secondary',
                            list: items.filter(i => ketentuan(i) === 'ineligible')
                        }
                    ];
                    byId('info-attention').innerHTML = attention.map(a => {
                        const names = a.list.slice(0, 6).map(i => escapeHtml(i.nama_lengkap)).join(', ');
                        const more = a.list.length > 6 ?
                            ` <span class="text-muted">+${a.list.length - 6} lainnya</span>` : '';
                        return `
                        <div class="col-12 col-md-6">
                            <div class="attention-card">
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <span class="fw-bold small d-flex align-items-center gap-1 text-${a.color}">
                                        <i class="iconify" data-icon="${a.icon}"></i> ${a.title}
                                    </span>
                                    <span class="badge bg-${a.color}">${a.list.length}</span>
                                </div>
                                <div class="small text-muted" style="min-height: 38px;">${a.list.length ? names + more : 'Tidak ada data yang perlu diperhatikan.'}</div>
                            </div>
                        </div>`;
                    }).join('');

                    const sortedPeriods = allPeriods.slice().sort((a, b) => (a.tahun - b.tahun) || (a.id - b.id));
                    byId('info-periode-body').innerHTML = sortedPeriods.map((p, idx) => {
                            const cur = parseFloat(p.total_anggaran) || 0;
                            const prev = idx > 0 ? (parseFloat(sortedPeriods[idx - 1].total_anggaran) || 0) :
                                null;
                            let selisih = '-',
                                persen = '-';
                            if (prev !== null) {
                                const d = cur - prev;
                                selisih =
                                    `<span class="${d >= 0 ? 'text-success' : 'text-danger'}">${d >= 0 ? '+' : '-'}${formatRupiah(Math.abs(d))}</span>`;
                                persen = prev > 0 ?
                                    `<span class="${d >= 0 ? 'text-success' : 'text-danger'}">${d >= 0 ? '▲' : '▼'} ${Math.abs(d / prev * 100).toFixed(1).replace('.', ',')}%</span>` :
                                    '-';
                            }
                            const active = currentPeriod && p.id == currentPeriod.id ? 'table-active' : '';
                            return `
                        <tr class="${active}">
                            <td class="fw-semibold">${escapeHtml(p.nama_periode)}</td>
                            <td>${p.tahun}</td>
                            <td>${formatTanggalIndo(p.tanggal_jatuh_tempo)}</td>
                            <td class="text-end">${p.items_count ?? 0}</td>
                            <td class="text-end font-monospace fw-bold text-primary">${formatRupiah(cur)}</td>
                            <td class="text-end font-monospace">${selisih}</td>
                            <td class="text-end">${persen}</td>
                        </tr>`;
                        }).join('') ||
                        '<tr><td colspan="7" class="text-center text-muted py-3">Belum ada periode</td></tr>';

                    const contoh = 5000000;
                    byId('info-simulasi-body').innerHTML = [12, 9, 6, 3, 1].map(m => {
                        const val = m >= 12 ? contoh : Math.round(contoh / 12 * m);
                        const rumus = m >= 12 ? '1 × Gaji' : `(5.000.000 / 12) × ${m}`;
                        return `
                        <tr>
                            <td class="fw-semibold">${m >= 12 ? '12 Bulan (Penuh)' : m + ' Bulan'}</td>
                            <td class="text-muted">${rumus}</td>
                            <td class="text-end font-monospace fw-bold text-primary">${formatRupiah(val)}</td>
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
                    if (!currentPeriod || !window.Chart) return;

                    destroyChart('tren');
                    const sorted = allPeriods.slice().sort((a, b) => (a.tahun - b.tahun) || (a.id - b.id));
                    charts.tren = new Chart(byId('chart-tren-periode'), {
                        type: 'line',
                        data: {
                            labels: sorted.map(p => p.nama_periode),
                            datasets: [{
                                    label: 'Total Anggaran (Rp)',
                                    data: sorted.map(p => parseFloat(p.total_anggaran) || 0),
                                    borderColor: '#4f46e5',
                                    backgroundColor: 'rgba(79,70,229,0.12)',
                                    fill: true,
                                    tension: 0.35,
                                    pointRadius: 4,
                                    yAxisID: 'y'
                                },
                                {
                                    label: 'Jumlah Karyawan',
                                    data: sorted.map(p => p.items_count || 0),
                                    borderColor: '#059669',
                                    backgroundColor: 'rgba(5,150,105,0.1)',
                                    tension: 0.35,
                                    pointRadius: 4,
                                    yAxisID: 'y1'
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
                            plugins: {
                                tooltip: {
                                    callbacks: {
                                        label: function(ctx) {
                                            return ctx.datasetIndex === 0 ?
                                                ' ' + formatRupiah(ctx.parsed.y) :
                                                ' ' + ctx.parsed.y + ' karyawan';
                                        }
                                    }
                                }
                            },
                            scales: {
                                y: {
                                    position: 'left',
                                    ticks: {
                                        callback: v => shortRupiah(v)
                                    },
                                    beginAtZero: true
                                },
                                y1: {
                                    position: 'right',
                                    grid: {
                                        drawOnChartArea: false
                                    },
                                    beginAtZero: true,
                                    ticks: {
                                        precision: 0
                                    }
                                }
                            }
                        }
                    });

                    destroyChart('distribusi');
                    const buckets = Array.from({
                        length: 13
                    }, () => ({
                        count: 0,
                        total: 0
                    }));
                    allItems.forEach(i => {
                        const m = Math.min(12, Math.max(0, Math.round(parseFloat(i
                            .total_masa_kerja_bulan) || 0)));
                        buckets[m].count++;
                        buckets[m].total += parseFloat(i.nilai_thr_final) || 0;
                    });
                    charts.distribusi = new Chart(byId('chart-distribusi'), {
                        type: 'line',
                        data: {
                            labels: buckets.map((b, idx) => idx + ' bln'),
                            datasets: [{
                                    label: 'Jumlah Karyawan',
                                    data: buckets.map(b => b.count),
                                    borderColor: '#0284c7',
                                    backgroundColor: 'rgba(2,132,199,0.12)',
                                    fill: true,
                                    tension: 0.35,
                                    pointRadius: 3,
                                    yAxisID: 'y'
                                },
                                {
                                    label: 'Total THR (Rp)',
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
                            interaction: {
                                mode: 'index',
                                intersect: false
                            },
                            plugins: {
                                tooltip: {
                                    callbacks: {
                                        label: function(ctx) {
                                            return ctx.datasetIndex === 0 ?
                                                ' ' + ctx.parsed.y + ' karyawan' :
                                                ' ' + formatRupiah(ctx.parsed.y);
                                        }
                                    }
                                }
                            },
                            scales: {
                                y: {
                                    position: 'left',
                                    beginAtZero: true,
                                    ticks: {
                                        precision: 0
                                    }
                                },
                                y1: {
                                    position: 'right',
                                    grid: {
                                        drawOnChartArea: false
                                    },
                                    beginAtZero: true,
                                    ticks: {
                                        callback: v => shortRupiah(v)
                                    }
                                }
                            }
                        }
                    });

                    destroyChart('kurva');
                    const sortedItems = allItems.slice().sort((a, b) => (parseFloat(a.nilai_thr_final) || 0) - (
                        parseFloat(b.nilai_thr_final) || 0));
                    charts.kurva = new Chart(byId('chart-kurva'), {
                        type: 'line',
                        data: {
                            labels: sortedItems.map((i, idx) => idx + 1),
                            datasets: [{
                                label: 'Nilai THR',
                                data: sortedItems.map(i => parseFloat(i.nilai_thr_final) || 0),
                                borderColor: '#7c3aed',
                                backgroundColor: 'rgba(124,58,237,0.1)',
                                fill: true,
                                tension: 0.25,
                                pointRadius: sortedItems.length > 60 ? 0 : 3
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: false
                                },
                                tooltip: {
                                    callbacks: {
                                        title: function(ctx) {
                                            const it = sortedItems[ctx[0].dataIndex];
                                            return it ? it.nama_lengkap : '';
                                        },
                                        label: function(ctx) {
                                            return ' ' + formatRupiah(ctx.parsed.y);
                                        }
                                    }
                                }
                            },
                            scales: {
                                x: {
                                    title: {
                                        display: true,
                                        text: 'Urutan Karyawan (terendah → tertinggi)'
                                    },
                                    ticks: {
                                        maxTicksLimit: 12
                                    }
                                },
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        callback: v => shortRupiah(v)
                                    }
                                }
                            }
                        }
                    });
                }

                byId('tab-analitik-btn').addEventListener('shown.bs.tab', function() {
                    renderCharts();
                });
                byId('tab-table-btn').addEventListener('shown.bs.tab', function() {
                    if (dt) dt.columns.adjust();
                });

                function hitungSistemEdit() {
                    const salary = parseFloat(byId('edit-basic-salary').value) || 0;
                    const masa = parseFloat(byId('edit-masa-bulan').value) || 0;
                    const full = parseFloat(currentPeriod.full_masa_kerja_bulan) || 12;
                    if (masa >= full) return Math.round(salary);
                    if (masa > 0) return Math.round((salary / 12) * masa);
                    return 0;
                }

                function syncSistemEdit() {
                    const calc = hitungSistemEdit();
                    byId('edit-thr-sistem').textContent = formatRupiah(calc);
                    byId('edit-nilai-thr-final').value = calc;
                    byId('edit-preview-rupiah').textContent = formatRupiah(calc);
                }

                function openEditModal(itemId) {
                    const item = allItems.find(i => i.id == itemId);
                    if (!item) return;

                    byId('edit-item-id').value = item.id;
                    byId('edit-avatar').textContent = getInitials(item.nama_lengkap);
                    byId('edit-header-nama').textContent = item.nama_lengkap;
                    byId('edit-header-nip').textContent =
                        `NIP: ${item.karyawan && item.karyawan.nip ? item.karyawan.nip : '-'} • ${item.karyawan && item.karyawan.divisi ? item.karyawan.divisi : '-'}`;

                    byId('edit-nama-lengkap').value = item.nama_lengkap || '';
                    byId('edit-jabatan').value = item.jabatan || '';
                    byId('edit-status').value = item.status_karyawan || 'Tetap';
                    byId('edit-rekening').value = item.rekening_maybank || '';
                    byId('edit-tanggal-join').value = item.tanggal_join ? String(item.tanggal_join).split('T')[0] :
                        '';
                    byId('edit-masa-bulan').value = parseFloat(item.total_masa_kerja_bulan) || 0;
                    byId('edit-masa-teks').value = item.masa_kerja_teks || '';
                    byId('edit-basic-salary').value = item.basic_salary;
                    byId('edit-thr-sistem').textContent = formatRupiah(hitungSistemEdit());
                    byId('edit-nilai-thr-final').value = item.nilai_thr_final;
                    byId('edit-preview-rupiah').textContent = formatRupiah(item.nilai_thr_final);
                    byId('edit-catatan').value = item.catatan || '';

                    byId('edit-basic-salary').oninput = syncSistemEdit;
                    byId('edit-masa-bulan').oninput = syncSistemEdit;

                    byId('btn-reset-to-system').onclick = function() {
                        const calc = hitungSistemEdit();
                        byId('edit-nilai-thr-final').value = calc;
                        byId('edit-preview-rupiah').textContent = formatRupiah(calc);
                    };

                    byId('edit-nilai-thr-final').oninput = function() {
                        byId('edit-preview-rupiah').textContent = formatRupiah(this.value);
                    };

                    bootstrap.Modal.getOrCreateInstance(byId('modal-edit-item')).show();
                }

                byId('form-edit-item').addEventListener('submit', function(e) {
                    e.preventDefault();
                    const itemId = byId('edit-item-id').value;
                    const submitBtn = byId('btn-submit-edit-item');
                    submitBtn.disabled = true;

                    const payload = {
                        nama_lengkap: byId('edit-nama-lengkap').value,
                        jabatan: byId('edit-jabatan').value,
                        status_karyawan: byId('edit-status').value,
                        rekening_maybank: byId('edit-rekening').value,
                        tanggal_join: byId('edit-tanggal-join').value || null,
                        total_masa_kerja_bulan: byId('edit-masa-bulan').value,
                        masa_kerja_teks: byId('edit-masa-teks').value,
                        basic_salary: byId('edit-basic-salary').value,
                        nilai_thr_final: byId('edit-nilai-thr-final').value,
                        catatan: byId('edit-catatan').value
                    };

                    fetch(`{{ url('HR-dashboard/thr/update-item') }}/${itemId}`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrfToken
                            },
                            body: JSON.stringify(payload)
                        })
                        .then(res => res.json())
                        .then(res => {
                            submitBtn.disabled = false;
                            if (res.status === 'success') {
                                bootstrap.Modal.getInstance(byId('modal-edit-item')).hide();
                                showToast('Data THR berhasil diperbarui!', 'success');
                                loadData(currentPeriod.id);
                            } else {
                                Swal.fire('Gagal', res.message || 'Terjadi kesalahan', 'error');
                            }
                        })
                        .catch(err => {
                            submitBtn.disabled = false;
                            console.error(err);
                            Swal.fire('Gagal', 'Terjadi kesalahan sistem', 'error');
                        });
                });

                function fetchHolidayDate(year, targetInputId, hintId = null) {
                    fetch(`{{ route('HR.thr.holiday-date') }}?year=${year}`)
                        .then(res => res.json())
                        .then(res => {
                            if (res.status === 'success' && res.tanggal) {
                                byId(targetInputId).value = res.tanggal;
                                if (hintId) {
                                    const hintEl = byId(hintId);
                                    if (hintEl) {
                                        hintEl.innerHTML =
                                            `<span class="text-success"><i class="iconify" data-icon="mdi:check"></i> Ditemukan: ${escapeHtml(res.nama)} (${res.tanggal})</span>`;
                                    }
                                }
                            } else if (hintId) {
                                const hintEl = byId(hintId);
                                if (hintEl) {
                                    hintEl.innerHTML =
                                        `<span class="text-warning">Hari libur Idulfitri tahun ${year} belum terdaftar di tabel hari libur</span>`;
                                }
                            }
                        })
                        .catch(err => {
                            console.error('Error fetching holiday date:', err);
                        });
                }

                function openCreateModal() {
                    const currentYear = new Date().getFullYear();
                    byId('create-tahun').value = currentYear;
                    byId('create-nama-periode').value = `THR Idulfitri ${currentYear}`;
                    byId('create-tanggal-jatuh-tempo').value = `${currentYear}-03-20`;
                    fetchHolidayDate(currentYear, 'create-tanggal-jatuh-tempo', 'create-holiday-hint');
                    bootstrap.Modal.getOrCreateInstance(byId('modal-create-period')).show();
                }

                byId('btn-open-create-period').addEventListener('click', openCreateModal);
                byId('btn-empty-create').addEventListener('click', openCreateModal);

                byId('create-tahun').addEventListener('change', function() {
                    const yr = this.value;
                    if (yr) {
                        byId('create-nama-periode').value = `THR Idulfitri ${yr}`;
                        fetchHolidayDate(yr, 'create-tanggal-jatuh-tempo', 'create-holiday-hint');
                    }
                });

                byId('btn-fetch-holiday-create').addEventListener('click', function() {
                    const yr = byId('create-tahun').value;
                    fetchHolidayDate(yr, 'create-tanggal-jatuh-tempo', 'create-holiday-hint');
                });

                byId('btn-fetch-holiday-setting').addEventListener('click', function() {
                    if (currentPeriod) {
                        fetchHolidayDate(currentPeriod.tahun, 'setting-tanggal-jatuh-tempo');
                    }
                });

                byId('form-create-period').addEventListener('submit', function(e) {
                    e.preventDefault();
                    const submitBtn = byId('btn-submit-create');
                    submitBtn.disabled = true;

                    const payload = {
                        nama_periode: byId('create-nama-periode').value,
                        tahun: byId('create-tahun').value,
                        tanggal_jatuh_tempo: byId('create-tanggal-jatuh-tempo').value,
                        full_masa_kerja_bulan: byId('create-full-months').value,
                        min_masa_kerja_bulan: byId('create-min-months').value,
                        catatan: byId('create-catatan').value,
                    };

                    fetch('{{ route('HR.thr.generate') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrfToken
                            },
                            body: JSON.stringify(payload)
                        })
                        .then(res => res.json())
                        .then(res => {
                            submitBtn.disabled = false;
                            if (res.status === 'success') {
                                bootstrap.Modal.getInstance(byId('modal-create-period')).hide();
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil!',
                                    text: 'Periode & kalkulasi THR berhasil dibuat.',
                                    timer: 1800,
                                    showConfirmButton: false
                                });
                                loadPeriods(res.period_id);
                            } else {
                                Swal.fire('Gagal', res.message || 'Gagal membuat periode THR', 'error');
                            }
                        })
                        .catch(err => {
                            submitBtn.disabled = false;
                            console.error(err);
                            Swal.fire('Error', 'Terjadi kesalahan jaringan/sistem', 'error');
                        });
                });

                byId('form-setting-period').addEventListener('submit', function(e) {
                    e.preventDefault();
                    const periodId = byId('setting-period-id').value;
                    const submitBtn = byId('btn-submit-setting');
                    submitBtn.disabled = true;

                    const payload = {
                        nama_periode: byId('setting-nama-periode').value,
                        tanggal_jatuh_tempo: byId('setting-tanggal-jatuh-tempo').value,
                        full_masa_kerja_bulan: byId('setting-full-months').value,
                        min_masa_kerja_bulan: byId('setting-min-months').value,
                        catatan: byId('setting-catatan').value,
                        recalculate: byId('setting-recalculate').checked,
                        force_reset_manual: byId('setting-force-reset').checked,
                    };

                    fetch(`{{ url('HR-dashboard/thr/update-setting') }}/${periodId}`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrfToken
                            },
                            body: JSON.stringify(payload)
                        })
                        .then(res => res.json())
                        .then(res => {
                            submitBtn.disabled = false;
                            if (res.status === 'success') {
                                bootstrap.Modal.getInstance(byId('modal-setting-period')).hide();
                                showToast('Ketentuan berhasil diperbarui!', 'success');
                                loadPeriods(periodId);
                            } else {
                                Swal.fire('Gagal', res.message || 'Gagal menyimpan perubahan', 'error');
                            }
                        })
                        .catch(err => {
                            submitBtn.disabled = false;
                            console.error(err);
                            Swal.fire('Error', 'Terjadi kesalahan sistem', 'error');
                        });
                });

                byId('btn-quick-recalculate').addEventListener('click', function() {
                    if (!currentPeriod) {
                        showToast('Pilih atau buat periode terlebih dahulu', 'warning');
                        return;
                    }

                    Swal.fire({
                        title: 'Hitung Ulang Nilai THR?',
                        text: 'Kalkulasi masa kerja dan THR seluruh karyawan akan dihitung ulang berdasarkan ketentuan aktif.',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#4f46e5',
                        cancelButtonColor: '#6b7280',
                        confirmButtonText: 'Ya, Hitung Ulang',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            const payload = {
                                nama_periode: currentPeriod.nama_periode,
                                tanggal_jatuh_tempo: String(currentPeriod.tanggal_jatuh_tempo)
                                    .split('T')[0],
                                full_masa_kerja_bulan: currentPeriod.full_masa_kerja_bulan,
                                min_masa_kerja_bulan: currentPeriod.min_masa_kerja_bulan,
                                recalculate: true,
                                force_reset_manual: false
                            };

                            fetch(`{{ url('HR-dashboard/thr/update-setting') }}/${currentPeriod.id}`, {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'Accept': 'application/json',
                                        'X-CSRF-TOKEN': csrfToken
                                    },
                                    body: JSON.stringify(payload)
                                })
                                .then(res => res.json())
                                .then(res => {
                                    if (res.status === 'success') {
                                        showToast('Perhitungan ulang berhasil!', 'success');
                                        loadPeriods(currentPeriod.id);
                                    } else {
                                        Swal.fire('Gagal', res.message, 'error');
                                    }
                                });
                        }
                    });
                });

                byId('btn-delete-period').addEventListener('click', function() {
                    if (!currentPeriod) {
                        showToast('Pilih atau buat periode terlebih dahulu', 'warning');
                        return;
                    }

                    Swal.fire({
                        title: 'Hapus Periode THR?',
                        text: `Anda yakin ingin menghapus seluruh data kalkulasi ${currentPeriod.nama_periode}?`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#dc2626',
                        cancelButtonColor: '#6b7280',
                        confirmButtonText: 'Ya, Hapus',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            fetch(`{{ url('HR-dashboard/thr/period') }}/${currentPeriod.id}`, {
                                    method: 'DELETE',
                                    headers: {
                                        'Accept': 'application/json',
                                        'X-CSRF-TOKEN': csrfToken
                                    }
                                })
                                .then(res => res.json())
                                .then(res => {
                                    if (res.status === 'success') {
                                        showToast('Periode berhasil dihapus', 'success');
                                        loadPeriods();
                                    } else {
                                        Swal.fire('Gagal', res.message, 'error');
                                    }
                                });
                        }
                    });
                });

                byId('btn-export-excel').addEventListener('click', function() {
                    if (!currentPeriod) {
                        showToast('Pilih periode terlebih dahulu', 'warning');
                        return;
                    }
                    window.location.href =
                    `{{ url('HR-dashboard/thr/export/excel') }}/${currentPeriod.id}`;
                });

                byId('select-period').addEventListener('change', function() {
                    loadData(this.value);
                });
                byId('input-search').addEventListener('input', () => applyFiltersAndRender(true));
                byId('filter-status').addEventListener('change', () => applyFiltersAndRender(true));
                byId('filter-kategori').addEventListener('change', () => applyFiltersAndRender(true));

                byId('btn-refresh').addEventListener('click', function() {
                    if (currentPeriod) {
                        loadData(currentPeriod.id);
                        showToast('Data dimuat ulang', 'info');
                    } else {
                        loadPeriods();
                    }
                });
            }
        });
    </script>
@endsection
