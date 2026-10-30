@extends('layout_HR.app')

@section('title', 'Perhitungan THR - INIX HR')

@section('content_HR')
<div class="container-fluid px-4 py-4">
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
        }
        .stat-value {
            font-size: 1.4rem;
            font-weight: 700;
            color: var(--gray-900);
            margin: 0.25rem 0 0;
            line-height: 1.2;
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

        /* Rule Info Box */
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

        /* Badges */
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
        .badge-tetap { background: #dbeafe; color: #1e40af; }
        .badge-kontrak { background: #fef3c7; color: #92400e; }
        .badge-probation { background: #fee2e2; color: #991b1b; }
        .badge-manual { background: #ede9fe; color: #6d28d9; border: 1px solid #c4b5fd; }
        .badge-full { background: #d1fae5; color: #065f46; }
        .badge-prorata { background: #e0f2fe; color: #0369a1; }
        .badge-zero { background: #f3f4f6; color: #6b7280; }

        /* Bank Account display */
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

        /* Avatar */
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

        /* Buttons */
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

        /* Skeleton Animation */
        .skeleton-box {
            background: linear-gradient(90deg, #f3f4f6 25%, #e5e7eb 50%, #f3f4f6 75%);
            background-size: 200% 100%;
            animation: skeleton-pulse 1.5s infinite;
            border-radius: 6px;
            display: block;
        }
        @keyframes skeleton-pulse {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
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
        .pagination-custom button:hover:not(:disabled) {
            background: var(--pri-light);
            color: var(--pri);
            border-color: var(--pri);
        }
        .pagination-custom button.active {
            background: var(--pri);
            color: white;
            border-color: var(--pri);
        }
        .pagination-custom button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
    </style>

    {{-- =========================================================================
         PAGE HEADER
    ========================================================================== --}}
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between page-header gap-3">
        <div>
            <h1 class="page-title">
                <i class="iconify text-primary" data-icon="mdi:gift-outline" data-width="28" data-height="28"></i>
                Perhitungan Tunjangan Hari Raya (THR)
            </h1>
            <p class="page-sub mb-0">
                Kelompok Administrasi &bull; Perhitungan otomatis masa kerja s/d tanggal jatuh tempo/Idulfitri & penyesuaian manual
            </p>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <button type="button" class="btn btn-outline-secondary btn-sm" id="btn-refresh" title="Muat Ulang Data">
                <i class="iconify" data-icon="mdi:refresh" data-width="18" data-height="18"></i>
                Refresh
            </button>
            <button type="button" class="btn btn-outline-success btn-sm" id="btn-export-excel" title="Ekspor Data ke File Excel">
                <i class="iconify" data-icon="mdi:file-excel-outline" data-width="18" data-height="18"></i>
                Export Excel
            </button>
            <button type="button" class="btn btn-pri btn-sm" id="btn-open-create-period">
                <i class="iconify" data-icon="mdi:plus-circle-outline" data-width="18" data-height="18"></i>
                Periode Baru
            </button>
        </div>
    </div>

    {{-- =========================================================================
         SKELETON LOADING STATE (Tampil saat data sedang dimuat via AJAX)
    ========================================================================== --}}
    <div id="thr-skeleton">
        {{-- Stat cards skeleton --}}
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

        {{-- Rule Banner skeleton --}}
        <div class="card card-shell p-3 mb-4">
            <div class="d-flex justify-content-between align-items-center">
                <span class="skeleton-box" style="width: 250px; height: 18px;"></span>
                <span class="skeleton-box" style="width: 120px; height: 28px;"></span>
            </div>
            <div class="d-flex gap-2 mt-3">
                <span class="skeleton-box" style="width: 160px; height: 24px;"></span>
                <span class="skeleton-box" style="width: 180px; height: 24px;"></span>
                <span class="skeleton-box" style="width: 190px; height: 24px;"></span>
            </div>
        </div>

        {{-- Table skeleton --}}
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

    {{-- =========================================================================
         REAL CONTENT (Tampil setelah data selesai dimuat)
    ========================================================================== --}}
    <div id="thr-real-content" style="display: none;">

        {{-- Top KPI Cards --}}
        <div class="row g-3 mb-4">
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card stat-card p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="stat-label">Total Anggaran THR</p>
                            <h3 class="stat-value text-primary" id="stat-total-anggaran">Rp 0</h3>
                            <small class="text-muted"><span id="stat-total-tersedia-gaji">0</span> Karyawan Terisi Gaji</small>
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
                            <small class="text-muted"><span id="stat-total-penerima">0</span> Berhak Menerima</small>
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
                            <p class="stat-label">1x Gaji Penuh (>= Ketentuan)</p>
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
                            <p class="stat-label">Prorata (< 12 Bulan)</p>
                            <h3 class="stat-value text-warning" id="stat-total-prorata">0</h3>
                            <small class="text-muted"><span id="stat-total-manual" class="fw-semibold text-primary">0</span> Diedit Manual</small>
                        </div>
                        <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                            <i class="iconify" data-icon="mdi:calculator-variant-outline"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Active Rules Banner --}}
        <div class="card card-shell p-4 mb-4 rule-banner">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <span class="badge bg-primary text-white" id="badge-periode-tahun">2026</span>
                        <h5 class="fw-bold text-gray-900 mb-0" id="rule-nama-periode">THR Idulfitri 1447 H / 2026</h5>
                    </div>
                    <p class="text-muted small mb-0">
                        Ketentuan kalkulasi manual berlaku untuk seluruh karyawan aktif. Nilai dapat disesuaikan per individu.
                    </p>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button class="btn btn-sm btn-outline-primary" id="btn-quick-recalculate" title="Hitung ulang otomatis berdasarkan ketentuan terkini">
                        <i class="iconify" data-icon="mdi:calculator"></i> Hitung Ulang
                    </button>
                    <button class="btn btn-sm btn-outline-danger" id="btn-delete-period" title="Hapus Periode Ini">
                        <i class="iconify" data-icon="mdi:trash-can-outline"></i>
                    </button>
                </div>
            </div>

            <hr class="my-3 opacity-25">

            <div class="d-flex flex-wrap gap-2 align-items-center">
                <div class="rule-badge">
                    <i class="iconify text-primary" data-icon="mdi:calendar-star"></i>
                    <span>Tgl Jatuh Tempo / Idulfitri: <strong class="text-dark" id="rule-tanggal-jatuh-tempo">-</strong></span>
                </div>
                <div class="rule-badge">
                    <i class="iconify text-success" data-icon="mdi:shield-check-outline"></i>
                    <span>Masa Kerja Penuh: <strong class="text-dark">12 Bulan (1x Gaji)</strong></span>
                </div>
                <div class="rule-badge">
                    <i class="iconify text-info" data-icon="mdi:information-outline"></i>
                    <span>Prorata: <strong class="text-dark" id="rule-formula-text">(Gaji Pokok / 12) &times; Masa Kerja (Bulan)</strong></span>
                </div>
                <div class="rule-badge">
                    <i class="iconify text-warning" data-icon="mdi:account-check-outline"></i>
                    <span>Ketentuan: <strong class="text-dark">Tanpa Batas Min. Masa Kerja</strong></span>
                </div>
            </div>
        </div>

        {{-- Filter & Action Bar --}}
        <div class="card card-shell p-3 mb-4">
            <div class="row g-3 align-items-center">
                <div class="col-12 col-md-3">
                    <label class="form-label small fw-bold text-muted mb-1">Pilih Periode THR</label>
                    <select class="form-select form-select-sm" id="select-period">
                        {{-- Populated via JS --}}
                    </select>
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label small fw-bold text-muted mb-1">Filter Status Karyawan</label>
                    <select class="form-select form-select-sm" id="filter-status">
                        <option value="all">Semua Status (Tetap, Kontrak, Probation)</option>
                        <option value="Tetap">Karyawan Tetap</option>
                        <option value="Kontrak">Karyawan Kontrak</option>
                        <option value="Probation">Probation</option>
                    </select>
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label small fw-bold text-muted mb-1">Filter Kategori THR</label>
                    <select class="form-select form-select-sm" id="filter-kategori">
                        <option value="all">Semua Kesesuaian Ketentuan</option>
                        <option value="full">1x Gaji Penuh (&ge; Ketentuan)</option>
                        <option value="prorata">Prorata (< Ketentuan)</option>
                        <option value="manual">Diedit Manual</option>
                        <option value="ineligible">Belum Memenuhi Ketentuan</option>
                    </select>
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label small fw-bold text-muted mb-1">Cari Karyawan</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white"><i class="iconify" data-icon="mdi:magnify"></i></span>
                        <input type="text" class="form-control" id="input-search" placeholder="Cari nama, jabatan, NIP...">
                    </div>
                </div>
            </div>
        </div>

        {{-- Main Table Card --}}
        <div class="card card-shell mb-4">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="fw-bold mb-0 text-gray-900">Daftar Penerima THR</h6>
                    <small class="text-muted">Masa kerja dihitung sampai dengan tanggal jatuh tempo/Idulfitri sesuai ketentuan aktif</small>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="small text-muted">Baris:</span>
                    <select class="form-select form-select-sm" id="table-per-page" style="width: 75px;">
                        <option value="10">10</option>
                        <option value="25" selected>25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                        <option value="all">Semua</option>
                    </select>
                    <span class="badge bg-light text-dark border" id="badge-row-count">0 Data Ditemukan</span>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="table-thr">
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
                    <tbody id="table-thr-body">
                        {{-- Populated via JS --}}
                    </tbody>
                </table>
            </div>

            <div class="card-footer bg-white py-3 border-top">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <small class="text-muted">
                        Menampilkan <span id="showingStart" class="fw-semibold text-dark">0</span> - <span id="showingEnd" class="fw-semibold text-dark">0</span>
                        dari <span id="totalItems" class="fw-semibold text-dark">0</span> data
                        &bull; Total Anggaran Tampil: <strong class="text-primary fs-6" id="footer-total-tampil">Rp 0</strong>
                    </small>
                    <div class="pagination-custom" id="paginationContainer"></div>
                </div>
            </div>
        </div>

    </div>

    {{-- Empty State (Jika belum ada periode sama sekali) --}}
    <div id="thr-empty-state" class="card card-shell p-5 text-center my-4" style="display: none;">
        <div class="my-4">
            <div class="stat-icon bg-primary bg-opacity-10 text-primary mx-auto mb-3" style="width: 64px; height: 64px; font-size: 2rem;">
                <i class="iconify" data-icon="mdi:gift-outline"></i>
            </div>
            <h4 class="fw-bold text-gray-900">Belum Ada Periode THR</h4>
            <p class="text-muted max-w-md mx-auto" style="max-width: 500px;">
                Mulai perhitungan Tunjangan Hari Raya (THR) dengan menentukan nama periode, tahun, serta tanggal jatuh tempo/Idulfitri.
            </p>
            <button class="btn btn-pri mt-2" onclick="$('#modal-create-period').modal('show');">
                <i class="iconify" data-icon="mdi:plus-circle-outline"></i> Buat Periode THR Sekarang
            </button>
        </div>
    </div>

</div>

{{-- =========================================================================
     MODAL: BUAT PERIODE BARU
========================================================================== --}}
<div class="modal fade" id="modal-create-period" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: var(--radius);">
            <div class="modal-header bg-primary text-white py-3">
                <h5 class="modal-title fw-bold text-white d-flex align-items-center gap-2">
                    <i class="iconify" data-icon="mdi:calendar-plus"></i>
                    Buat Periode THR Baru
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="form-create-period">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Nama Periode <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="nama_periode" id="create-nama-periode" placeholder="Contoh: THR Idulfitri 1447 H / 2026" required>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-5">
                            <label class="form-label small fw-bold">Tahun <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="tahun" id="create-tahun" value="{{ date('Y') }}" min="2000" max="2100" required>
                        </div>
                        <div class="col-7">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label small fw-bold mb-0">Tgl Jatuh Tempo / Idulfitri <span class="text-danger">*</span></label>
                                <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none small text-primary" id="btn-fetch-holiday-create" title="Ambil tanggal Idulfitri dari Tabel Hari Libur">
                                    <i class="iconify" data-icon="mdi:calendar-search"></i> Ambil dari Hari Libur
                                </button>
                            </div>
                            <input type="date" class="form-control" name="tanggal_jatuh_tempo" id="create-tanggal-jatuh-tempo" required>
                            <small class="text-muted d-block mt-1" id="create-holiday-hint" style="font-size: 0.72rem;">Otomatis disinkronkan dari tabel hari_liburs</small>
                        </div>
                    </div>

                    <div class="p-3 bg-light rounded-3 mb-3 border">
                        <h6 class="fw-bold small text-primary mb-1 d-flex align-items-center gap-1">
                            <i class="iconify" data-icon="mdi:information-outline"></i> Aturan Perhitungan Masa Kerja
                        </h6>
                        <small class="text-muted d-block mb-2" style="font-size: 0.75rem;">
                            Tidak ada batasan minimal masa kerja. Karyawan yang bergabung dalam tahun berjalan dihitung dari tanggal masuk s/d Idulfitri. Karyawan lama dihitung 12 bulan penuh (1x gaji).
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
                        <textarea class="form-control" name="catatan" id="create-catatan" rows="2" placeholder="Catatan kebijakan perusahaan mengenai THR ini..."></textarea>
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

{{-- =========================================================================
     MODAL: UBAH KETENTUAN PERIODE
========================================================================== --}}
<div class="modal fade" id="modal-setting-period" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: var(--radius);">
            <div class="modal-header bg-dark text-white py-3">
                <h5 class="modal-title fw-bold text-white d-flex align-items-center gap-2">
                    <i class="iconify" data-icon="mdi:tune"></i>
                    Ketentuan & Aturan Periode THR
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="form-setting-period">
                <input type="hidden" id="setting-period-id">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Nama Periode</label>
                        <input type="text" class="form-control" name="nama_periode" id="setting-nama-periode" required>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label small fw-bold mb-0">Tanggal Jatuh Tempo / Idulfitri <span class="text-danger">*</span></label>
                            <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none small text-primary" id="btn-fetch-holiday-setting" title="Ambil tanggal Idulfitri dari Tabel Hari Libur">
                                <i class="iconify" data-icon="mdi:calendar-search"></i> Ambil dari Hari Libur
                            </button>
                        </div>
                        <input type="date" class="form-control" name="tanggal_jatuh_tempo" id="setting-tanggal-jatuh-tempo" required>
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
                                Reset juga nilai yang pernah diubah manual kembali ke hitungan sistem
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

{{-- =========================================================================
     MODAL: EDIT NILAI THR MANUAL
========================================================================== --}}
<div class="modal fade" id="modal-edit-item" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: var(--radius);">
            <div class="modal-header bg-primary text-white py-3">
                <h5 class="modal-title fw-bold text-white d-flex align-items-center gap-2">
                    <i class="iconify" data-icon="mdi:account-edit"></i>
                    Penyesuaian Manual Nilai THR
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="form-edit-item">
                <input type="hidden" id="edit-item-id">
                <div class="modal-body p-4">
                    {{-- Info Box Karyawan --}}
                    <div class="p-3 bg-light rounded-3 mb-3 border">
                        <div class="d-flex align-items-center gap-3 mb-2">
                            <div class="emp-avatar" id="edit-avatar">IN</div>
                            <div>
                                <h6 class="fw-bold mb-0 text-gray-900" id="edit-nama-lengkap">-</h6>
                                <small class="text-muted" id="edit-nip-jabatan">-</small>
                            </div>
                        </div>
                        <div class="row g-2 mt-2 pt-2 border-top text-muted small">
                            <div class="col-6">
                                Status: <strong class="text-dark" id="edit-status">-</strong>
                            </div>
                            <div class="col-6">
                                Masa Kerja: <strong class="text-dark" id="edit-masa-kerja">-</strong>
                            </div>
                            <div class="col-12">
                                Rekening: <span class="bank-badge mt-1" id="edit-rekening">-</span>
                            </div>
                        </div>
                    </div>

                    {{-- Form Penyesuaian Nilai --}}
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label small fw-bold text-gray-900 mb-0">Basic Salary (Gaji Pokok)</label>
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle" style="font-size: 0.7rem;">
                                <i class="iconify" data-icon="mdi:database-sync-outline"></i> Auto-fill dari Data
                            </span>
                        </div>
                        <div class="input-group">
                            <span class="input-group-text bg-light">Rp</span>
                            <input type="number" step="1000" class="form-control" id="edit-basic-salary" name="basic_salary" placeholder="0">
                        </div>
                        <div class="form-check mt-1">
                            <input class="form-check-input" type="checkbox" id="edit-save-to-master" checked>
                            <label class="form-check-label small text-muted" for="edit-save-to-master" style="font-size: 0.75rem;">
                                Simpan ke data master karyawan (agar otomatis terisi seterusnya)
                            </label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label small fw-bold text-gray-900 mb-0">Nilai THR Final <span class="text-danger">*</span></label>
                            <small class="text-muted">Hitungan Sistem: <strong class="text-primary" id="edit-thr-sistem">Rp 0</strong></small>
                        </div>
                        <div class="input-group input-group-lg">
                            <span class="input-group-text bg-primary text-white fw-bold">Rp</span>
                            <input type="number" step="1000" class="form-control fw-bold fs-5 text-primary" id="edit-nilai-thr-final" name="nilai_thr_final" required>
                        </div>
                        <div class="mt-1 d-flex justify-content-between align-items-center">
                            <small class="text-primary fw-bold" id="edit-preview-rupiah">Rp 0</small>
                            <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none text-muted" id="btn-reset-to-system">
                                <i class="iconify" data-icon="mdi:restore"></i> Samakan dengan Sistem
                            </button>
                        </div>
                    </div>

                    <div class="mb-2">
                        <label class="form-label small fw-bold">Catatan Penyesuaian</label>
                        <textarea class="form-control" name="catatan" id="edit-catatan" rows="2" placeholder="Alasan penyesuaian (misal: pembulatan, kebijakan direksi, bonus prestasi)..."></textarea>
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

{{-- SweetAlert2 JS --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        // State variables
        let currentPeriod = null;
        let allPeriods = [];
        let allItems = [];

        // Helper Format Rupiah
        function formatRupiah(number) {
            const num = parseFloat(number) || 0;
            return 'Rp ' + Math.round(num).toLocaleString('id-ID');
        }

        // Helper Format Tanggal Indonesia
        function formatTanggalIndo(dateStr) {
            if (!dateStr) return '-';
            const options = { day: 'numeric', month: 'long', year: 'numeric' };
            try {
                return new Date(dateStr).toLocaleDateString('id-ID', options);
            } catch (e) {
                return dateStr;
            }
        }

        // Init: Load Periods
        loadPeriods();

        function loadPeriods(selectTargetId = null) {
            fetch('{{ route("HR.thr.periods") }}')
                .then(res => res.json())
                .then(res => {
                    if (res.status === 'success') {
                        allPeriods = res.data || [];
                        renderPeriodSelector(selectTargetId);
                        if (allPeriods.length > 0) {
                            const targetId = selectTargetId || allPeriods[0].id;
                            loadData(targetId);
                        } else {
                            // Show empty state
                            document.getElementById('thr-skeleton').style.display = 'none';
                            document.getElementById('thr-real-content').style.display = 'none';
                            document.getElementById('thr-empty-state').style.display = 'block';
                        }
                    }
                })
                .catch(err => {
                    console.error('Error loading periods:', err);
                    showToast('Gagal memuat data periode', 'error');
                });
        }

        function renderPeriodSelector(selectedId = null) {
            const selector = document.getElementById('select-period');
            selector.innerHTML = '';

            allPeriods.forEach(p => {
                const opt = document.createElement('option');
                opt.value = p.id;
                opt.textContent = `${p.nama_periode} (${p.tahun})`;
                if (selectedId && p.id == selectedId) {
                    opt.selected = true;
                }
                selector.appendChild(opt);
            });
        }

        // Load Data for Period
        function loadData(periodId) {
            // Show Skeleton
            document.getElementById('thr-skeleton').style.display = 'block';
            document.getElementById('thr-real-content').style.display = 'none';
            document.getElementById('thr-empty-state').style.display = 'none';

            fetch(`{{ route("HR.thr.data") }}?period_id=${periodId}`)
                .then(res => res.json())
                .then(res => {
                    if (res.status === 'success') {
                        currentPeriod = res.period;
                        allItems = res.items || [];

                        if (!currentPeriod) {
                            document.getElementById('thr-skeleton').style.display = 'none';
                            document.getElementById('thr-empty-state').style.display = 'block';
                            return;
                        }

                        renderStats(res.stats);
                        renderRuleBanner(res.period);
                        applyFiltersAndRender();

                        // Hide skeleton, show real content with smooth transition
                        document.getElementById('thr-skeleton').style.display = 'none';
                        document.getElementById('thr-real-content').style.display = 'block';
                    }
                })
                .catch(err => {
                    console.error('Error loading data:', err);
                    showToast('Gagal memuat rincian THR', 'error');
                    document.getElementById('thr-skeleton').style.display = 'none';
                });
        }

        function renderStats(stats) {
            document.getElementById('stat-total-anggaran').textContent = formatRupiah(stats.total_anggaran);
            const elTersedia = document.getElementById('stat-total-tersedia-gaji');
            if (elTersedia) {
                elTersedia.textContent = stats.total_tersedia_gaji ?? 0;
            }
            document.getElementById('stat-total-karyawan').textContent = stats.total_karyawan;
            document.getElementById('stat-total-penerima').textContent = stats.total_penerima;
            document.getElementById('stat-total-full').textContent = stats.total_full;
            document.getElementById('stat-total-prorata').textContent = stats.total_prorata;
            document.getElementById('stat-total-manual').textContent = stats.total_manual;
        }

        function renderRuleBanner(period) {
            document.getElementById('badge-periode-tahun').textContent = period.tahun;
            document.getElementById('rule-nama-periode').textContent = period.nama_periode;
            document.getElementById('rule-tanggal-jatuh-tempo').textContent = formatTanggalIndo(period.tanggal_jatuh_tempo);
            const fullEl = document.getElementById('rule-full-months');
            if (fullEl) fullEl.textContent = `${period.full_masa_kerja_bulan} Bulan`;
            const minEl = document.getElementById('rule-min-months');
            if (minEl) minEl.textContent = `${period.min_masa_kerja_bulan} Bulan`;
            // Formula prorata: (Basic Salary / 12) * Masa Kerja
            const formulaEl = document.getElementById('rule-formula-text');
            if (formulaEl) {
                formulaEl.textContent = `(Gaji Pokok / 12) × Masa Kerja (Bulan)`;
            }
        }

        let currentPage = 1;
        let currentFilteredItems = [];

        // Apply Search & Filters Client-Side for Instant Speed
        function applyFiltersAndRender(resetPage = true) {
            if (resetPage) currentPage = 1;

            const search = document.getElementById('input-search').value.toLowerCase().trim();
            const statusFilter = document.getElementById('filter-status').value;
            const kategoriFilter = document.getElementById('filter-kategori').value;

            currentFilteredItems = allItems.filter(item => {
                // Search filter
                if (search) {
                    const nama = (item.nama_lengkap || '').toLowerCase();
                    const jabatan = (item.jabatan || '').toLowerCase();
                    const nip = (item.karyawan && item.karyawan.nip ? item.karyawan.nip : '').toLowerCase();
                    const rekening = (item.rekening_maybank || '').toLowerCase();
                    const match = nama.includes(search) || jabatan.includes(search) || nip.includes(search) || rekening.includes(search);
                    if (!match) return false;
                }

                // Status Filter
                if (statusFilter !== 'all') {
                    if (item.status_karyawan !== statusFilter) return false;
                }

                // Kategori Filter — murni berdasarkan status_ketentuan (masa kerja vs threshold)
                if (kategoriFilter !== 'all') {
                    if (kategoriFilter === 'manual' && !item.is_manual) return false;
                    if (kategoriFilter === 'full' && item.status_ketentuan !== 'full') return false;
                    if (kategoriFilter === 'prorata' && item.status_ketentuan !== 'prorata') return false;
                    if (kategoriFilter === 'ineligible' && item.status_ketentuan !== 'ineligible') return false;
                }

                return true;
            });

            renderTable();
        }

        function renderTable() {
            const tbody = document.getElementById('table-thr-body');
            tbody.innerHTML = '';

            const total = currentFilteredItems.length;
            document.getElementById('badge-row-count').textContent = `${total} Data Ditemukan`;

            let totalAnggaranTampil = 0;
            currentFilteredItems.forEach(item => {
                totalAnggaranTampil += parseFloat(item.nilai_thr_final) || 0;
            });
            document.getElementById('footer-total-tampil').textContent = formatRupiah(totalAnggaranTampil);

            if (total === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="10" class="text-center py-5 text-muted">
                            <i class="iconify fs-1 d-block mb-2 opacity-50" data-icon="mdi:account-search-outline"></i>
                            Tidak ada data karyawan yang sesuai dengan kriteria filter.
                        </td>
                    </tr>
                `;
                document.getElementById('showingStart').textContent = '0';
                document.getElementById('showingEnd').textContent = '0';
                document.getElementById('totalItems').textContent = '0';
                document.getElementById('paginationContainer').innerHTML = '';
                return;
            }

            // Pagination calculation
            const perPageSelect = document.getElementById('table-per-page');
            const perPageVal = perPageSelect ? perPageSelect.value : '25';
            const isAll = perPageVal === 'all';
            const perPage = isAll ? total : (parseInt(perPageVal, 10) || 25);
            const totalPages = isAll ? 1 : Math.ceil(total / perPage);

            if (currentPage > totalPages) currentPage = totalPages;
            if (currentPage < 1) currentPage = 1;

            const startIdx = isAll ? 0 : (currentPage - 1) * perPage;
            const endIdx = isAll ? total : Math.min(startIdx + perPage, total);
            const pagedItems = currentFilteredItems.slice(startIdx, endIdx);

            document.getElementById('showingStart').textContent = startIdx + 1;
            document.getElementById('showingEnd').textContent = endIdx;
            document.getElementById('totalItems').textContent = total;

            pagedItems.forEach((item, index) => {
                // Status badge
                let statusBadge = '<span class="badge-status badge-tetap">Tetap</span>';
                if (item.status_karyawan === 'Kontrak') {
                    statusBadge = '<span class="badge-status badge-kontrak">Kontrak</span>';
                } else if (item.status_karyawan === 'Probation') {
                    statusBadge = '<span class="badge-status badge-probation">Probation</span>';
                }

                // Kesesuaian Ketentuan badge
                let thrBadge = '';
                if (item.is_manual) {
                    thrBadge = '<span class="badge-status badge-manual me-1" title="Disesuaikan secara manual"><i class="iconify" data-icon="mdi:pencil"></i> Manual</span>';
                } else if (item.status_ketentuan === 'full') {
                    thrBadge = '<span class="badge-status badge-full me-1">1x Gaji</span>';
                } else if (item.status_ketentuan === 'prorata') {
                    thrBadge = '<span class="badge-status badge-prorata me-1">Prorata</span>';
                } else {
                    thrBadge = '<span class="badge-status badge-zero me-1">Belum Memenuhi</span>';
                }

                // Initials for avatar
                const initials = (item.nama_lengkap || 'K')
                    .split(' ')
                    .slice(0, 2)
                    .map(w => w[0])
                    .join('')
                    .toUpperCase();

                // Bank Account
                const rekMaybank = item.rekening_maybank ? item.rekening_maybank : '-';
                const copyBtn = item.rekening_maybank ? `
                    <span class="btn-copy" onclick="copyToClipboard('${item.rekening_maybank}')" title="Salin Rekening">
                        <i class="iconify" data-icon="mdi:content-copy"></i>
                    </span>
                ` : '';

                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td class="text-center text-muted small">${startIdx + index + 1}</td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="emp-avatar">${initials}</div>
                            <div>
                                <span class="fw-bold text-gray-900 d-block">${escapeHtml(item.nama_lengkap)}</span>
                                <small class="text-muted font-monospace" style="font-size: 0.75rem;">NIP: ${item.karyawan && item.karyawan.nip ? item.karyawan.nip : '-'}</small>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="text-gray-800 fw-semibold">${escapeHtml(item.jabatan || '-')}</span>
                        <small class="text-muted d-block" style="font-size: 0.72rem;">${item.karyawan && item.karyawan.divisi ? item.karyawan.divisi : ''}</small>
                    </td>
                    <td>${statusBadge}</td>
                    <td>
                        <div class="bank-badge">
                            <i class="iconify text-warning" data-icon="mdi:credit-card-outline"></i>
                            <span>${escapeHtml(rekMaybank)}</span>
                            ${copyBtn}
                        </div>
                    </td>
                    <td>
                        <span class="fw-bold text-gray-800">${escapeHtml(item.masa_kerja_teks || '-')}</span>
                        <small class="text-muted d-block" style="font-size: 0.72rem;">Masuk: ${formatTanggalIndo(item.tanggal_join)}</small>
                    </td>
                    <td class="text-center">${thrBadge}</td>
                    <td class="text-end font-monospace">
                        <span class="text-muted">${formatRupiah(item.basic_salary)}</span>
                    </td>
                    <td class="text-end">
                        <span class="fw-bold fs-6 text-primary font-monospace">${formatRupiah(item.nilai_thr_final)}</span>
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-sm btn-outline-primary btn-edit-item" data-id="${item.id}" title="Edit Nilai THR Manual">
                            <i class="iconify" data-icon="mdi:pencil"></i> Edit
                        </button>
                    </td>
                `;
                tbody.appendChild(tr);
            });

            // Bind edit buttons
            document.querySelectorAll('.btn-edit-item').forEach(btn => {
                btn.addEventListener('click', function () {
                    const itemId = this.getAttribute('data-id');
                    openEditModal(itemId);
                });
            });

            // Render pagination controls
            renderPaginationControls(totalPages);
        }

        function renderPaginationControls(totalPages) {
            const container = document.getElementById('paginationContainer');
            if (!container) return;
            container.innerHTML = '';

            if (totalPages <= 1) return;

            // Prev Button
            const prevBtn = document.createElement('button');
            prevBtn.innerHTML = '<i class="iconify" data-icon="mdi:chevron-left"></i>';
            prevBtn.disabled = currentPage === 1;
            prevBtn.title = 'Halaman Sebelumnya';
            prevBtn.onclick = () => { if (currentPage > 1) { currentPage--; renderTable(); } };
            container.appendChild(prevBtn);

            // Page numbers
            let pages = [];
            if (totalPages <= 7) {
                for (let i = 1; i <= totalPages; i++) pages.push(i);
            } else {
                pages.push(1);
                if (currentPage > 3) pages.push('...');
                let start = Math.max(2, currentPage - 1);
                let end = Math.min(totalPages - 1, currentPage + 1);
                for (let i = start; i <= end; i++) pages.push(i);
                if (currentPage < totalPages - 2) pages.push('...');
                pages.push(totalPages);
            }

            pages.forEach(p => {
                if (p === '...') {
                    const dots = document.createElement('span');
                    dots.className = 'px-2 text-muted align-self-center small';
                    dots.textContent = '...';
                    container.appendChild(dots);
                } else {
                    const btn = document.createElement('button');
                    btn.textContent = p;
                    if (p === currentPage) btn.className = 'active';
                    btn.onclick = () => { currentPage = p; renderTable(); };
                    container.appendChild(btn);
                }
            });

            // Next Button
            const nextBtn = document.createElement('button');
            nextBtn.innerHTML = '<i class="iconify" data-icon="mdi:chevron-right"></i>';
            nextBtn.disabled = currentPage === totalPages;
            nextBtn.title = 'Halaman Selanjutnya';
            nextBtn.onclick = () => { if (currentPage < totalPages) { currentPage++; renderTable(); } };
            container.appendChild(nextBtn);
        }

        // Copy to clipboard helper
        window.copyToClipboard = function (text) {
            navigator.clipboard.writeText(text).then(() => {
                showToast('Nomor rekening disalin!', 'success');
            }).catch(() => {
                showToast('Gagal menyalin', 'error');
            });
        };

        // Utility: Escape HTML
        function escapeHtml(text) {
            if (!text) return '';
            return String(text)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        // Open Edit Item Modal
        function openEditModal(itemId) {
            const item = allItems.find(i => i.id == itemId);
            if (!item) return;

            document.getElementById('edit-item-id').value = item.id;
            document.getElementById('edit-nama-lengkap').textContent = item.nama_lengkap;
            document.getElementById('edit-nip-jabatan').textContent = `NIP: ${item.karyawan && item.karyawan.nip ? item.karyawan.nip : '-'} • ${item.jabatan || '-'}`;
            document.getElementById('edit-status').textContent = item.status_karyawan || '-';
            document.getElementById('edit-masa-kerja').textContent = item.masa_kerja_teks || '-';
            document.getElementById('edit-rekening').textContent = item.rekening_maybank || '-';

            const initials = (item.nama_lengkap || 'K')
                .split(' ')
                .slice(0, 2)
                .map(w => w[0])
                .join('')
                .toUpperCase();
            document.getElementById('edit-avatar').textContent = initials;

            document.getElementById('edit-basic-salary').value = item.basic_salary;
            document.getElementById('edit-thr-sistem').textContent = formatRupiah(item.nilai_thr_sistem);
            document.getElementById('edit-nilai-thr-final').value = item.nilai_thr_final;
            document.getElementById('edit-preview-rupiah').textContent = formatRupiah(item.nilai_thr_final);
            document.getElementById('edit-catatan').value = item.catatan || '';
            document.getElementById('edit-save-to-master').checked = true;

            // Recalculate auto THR if basic salary is changed
            document.getElementById('edit-basic-salary').oninput = function () {
                const salary = parseFloat(this.value) || 0;
                const fullMonths = currentPeriod.full_masa_kerja_bulan || 12;
                const minMonths = currentPeriod.min_masa_kerja_bulan || 1;
                let calcThr = 0;

                if (item.total_masa_kerja_bulan >= fullMonths) {
                    calcThr = salary;
                } else if (item.total_masa_kerja_bulan >= minMonths) {
                    const months = (item.masa_kerja_tahun * 12) + item.masa_kerja_bulan + (item.masa_kerja_hari >= 15 ? 1 : 0);
                    calcThr = Math.round((salary / 12) * months);
                }

                document.getElementById('edit-thr-sistem').textContent = formatRupiah(calcThr);
                document.getElementById('edit-nilai-thr-final').value = calcThr;
                document.getElementById('edit-preview-rupiah').textContent = formatRupiah(calcThr);
            };

            // Button reset to system
            document.getElementById('btn-reset-to-system').onclick = function () {
                document.getElementById('edit-nilai-thr-final').value = item.nilai_thr_sistem;
                document.getElementById('edit-preview-rupiah').textContent = formatRupiah(item.nilai_thr_sistem);
            };

            // Live preview
            document.getElementById('edit-nilai-thr-final').oninput = function () {
                document.getElementById('edit-preview-rupiah').textContent = formatRupiah(this.value);
            };

            const modal = new bootstrap.Modal(document.getElementById('modal-edit-item'));
            modal.show();
        }

        // Submit Edit Item Form
        document.getElementById('form-edit-item').addEventListener('submit', function (e) {
            e.preventDefault();
            const itemId = document.getElementById('edit-item-id').value;
            const finalVal = document.getElementById('edit-nilai-thr-final').value;
            const basicSalary = document.getElementById('edit-basic-salary').value;
            const catatan = document.getElementById('edit-catatan').value;
            const saveToMaster = document.getElementById('edit-save-to-master').checked;

            const submitBtn = document.getElementById('btn-submit-edit-item');
            submitBtn.disabled = true;

            fetch(`{{ url('HR-dashboard/thr/update-item') }}/${itemId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    nilai_thr_final: finalVal,
                    basic_salary: basicSalary,
                    catatan: catatan,
                    save_to_master: saveToMaster
                })
            })
            .then(res => res.json())
            .then(res => {
                submitBtn.disabled = false;
                if (res.status === 'success') {
                    bootstrap.Modal.getInstance(document.getElementById('modal-edit-item')).hide();
                    showToast('Nilai THR berhasil diperbarui!', 'success');
                    // Refresh data
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

        // Helper fetch holiday from hari_liburs table
        function fetchHolidayDate(year, targetInputId, hintId = null) {
            fetch(`{{ route('HR.thr.holiday-date') }}?year=${year}`)
                .then(res => res.json())
                .then(res => {
                    if (res.status === 'success' && res.tanggal) {
                        document.getElementById(targetInputId).value = res.tanggal;
                        if (hintId) {
                            const hintEl = document.getElementById(hintId);
                            if (hintEl) {
                                hintEl.innerHTML = `<span class="text-success"><i class="iconify" data-icon="mdi:check"></i> Ditemukan: ${res.nama} (${res.tanggal})</span>`;
                            }
                        }
                    } else {
                        if (hintId) {
                            const hintEl = document.getElementById(hintId);
                            if (hintEl) {
                                hintEl.innerHTML = `<span class="text-warning">Hari libur Idulfitri tahun ${year} belum terdaftar di tabel hari libur</span>`;
                            }
                        }
                    }
                })
                .catch(err => {
                    console.error('Error fetching holiday date:', err);
                });
        }

        // Open Create Period Modal
        document.getElementById('btn-open-create-period').addEventListener('click', function () {
            const currentYear = new Date().getFullYear();
            document.getElementById('create-tahun').value = currentYear;
            document.getElementById('create-nama-periode').value = `THR Idulfitri ${currentYear}`;
            document.getElementById('create-tanggal-jatuh-tempo').value = `${currentYear}-03-20`;

            // Auto-fetch Idulfitri dari tabel hari_liburs
            fetchHolidayDate(currentYear, 'create-tanggal-jatuh-tempo', 'create-holiday-hint');

            const modal = new bootstrap.Modal(document.getElementById('modal-create-period'));
            modal.show();
        });

        // Trigger fetch saat tahun diubah di form create
        document.getElementById('create-tahun').addEventListener('change', function () {
            const yr = this.value;
            if (yr) {
                document.getElementById('create-nama-periode').value = `THR Idulfitri ${yr}`;
                fetchHolidayDate(yr, 'create-tanggal-jatuh-tempo', 'create-holiday-hint');
            }
        });

        // Tombol manual fetch di create modal
        document.getElementById('btn-fetch-holiday-create').addEventListener('click', function () {
            const yr = document.getElementById('create-tahun').value;
            fetchHolidayDate(yr, 'create-tanggal-jatuh-tempo', 'create-holiday-hint');
        });

        // Tombol manual fetch di setting modal
        document.getElementById('btn-fetch-holiday-setting').addEventListener('click', function () {
            if (currentPeriod) {
                fetchHolidayDate(currentPeriod.tahun, 'setting-tanggal-jatuh-tempo');
            }
        });

        // Submit Create Period Form
        document.getElementById('form-create-period').addEventListener('submit', function (e) {
            e.preventDefault();
            const submitBtn = document.getElementById('btn-submit-create');
            submitBtn.disabled = true;

            const payload = {
                nama_periode: document.getElementById('create-nama-periode').value,
                tahun: document.getElementById('create-tahun').value,
                tanggal_jatuh_tempo: document.getElementById('create-tanggal-jatuh-tempo').value,
                full_masa_kerja_bulan: document.getElementById('create-full-months').value,
                min_masa_kerja_bulan: document.getElementById('create-min-months').value,
                catatan: document.getElementById('create-catatan').value,
            };

            fetch('{{ route("HR.thr.generate") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify(payload)
            })
            .then(res => res.json())
            .then(res => {
                submitBtn.disabled = false;
                if (res.status === 'success') {
                    bootstrap.Modal.getInstance(document.getElementById('modal-create-period')).hide();
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

        // Open Setting Modal (guarded if button exists)
        const btnOpenSetting = document.getElementById('btn-open-setting');
        if (btnOpenSetting) {
            btnOpenSetting.addEventListener('click', function () {
                if (!currentPeriod) {
                    showToast('Pilih atau buat periode terlebih dahulu', 'warning');
                    return;
                }

                document.getElementById('setting-period-id').value = currentPeriod.id;
                document.getElementById('setting-nama-periode').value = currentPeriod.nama_periode;
                // Format to YYYY-MM-DD
                const tgl = currentPeriod.tanggal_jatuh_tempo ? currentPeriod.tanggal_jatuh_tempo.split('T')[0] : '';
                document.getElementById('setting-tanggal-jatuh-tempo').value = tgl;
                document.getElementById('setting-full-months').value = currentPeriod.full_masa_kerja_bulan || 12;
                document.getElementById('setting-min-months').value = currentPeriod.min_masa_kerja_bulan || 1;
                document.getElementById('setting-catatan').value = currentPeriod.catatan || '';
                document.getElementById('setting-recalculate').checked = true;
                document.getElementById('setting-force-reset').checked = false;

                const modal = new bootstrap.Modal(document.getElementById('modal-setting-period'));
                modal.show();
            });
        }

        // Submit Setting Form
        document.getElementById('form-setting-period').addEventListener('submit', function (e) {
            e.preventDefault();
            const periodId = document.getElementById('setting-period-id').value;
            const submitBtn = document.getElementById('btn-submit-setting');
            submitBtn.disabled = true;

            const payload = {
                nama_periode: document.getElementById('setting-nama-periode').value,
                tanggal_jatuh_tempo: document.getElementById('setting-tanggal-jatuh-tempo').value,
                full_masa_kerja_bulan: document.getElementById('setting-full-months').value,
                min_masa_kerja_bulan: document.getElementById('setting-min-months').value,
                catatan: document.getElementById('setting-catatan').value,
                recalculate: document.getElementById('setting-recalculate').checked,
                force_reset_manual: document.getElementById('setting-force-reset').checked,
            };

            fetch(`{{ url('HR-dashboard/thr/update-setting') }}/${periodId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify(payload)
            })
            .then(res => res.json())
            .then(res => {
                submitBtn.disabled = false;
                if (res.status === 'success') {
                    bootstrap.Modal.getInstance(document.getElementById('modal-setting-period')).hide();
                    showToast('Ketentuan berhasil diperbarui!', 'success');
                    loadData(periodId);
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

        // Quick Recalculate Button
        document.getElementById('btn-quick-recalculate').addEventListener('click', function () {
            if (!currentPeriod) return;

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
                        tanggal_jatuh_tempo: currentPeriod.tanggal_jatuh_tempo.split('T')[0],
                        full_masa_kerja_bulan: currentPeriod.full_masa_kerja_bulan,
                        min_masa_kerja_bulan: currentPeriod.min_masa_kerja_bulan,
                        recalculate: true,
                        force_reset_manual: false
                    };

                    fetch(`{{ url('HR-dashboard/thr/update-setting') }}/${currentPeriod.id}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify(payload)
                    })
                    .then(res => res.json())
                    .then(res => {
                        if (res.status === 'success') {
                            showToast('Perhitungan ulang berhasil!', 'success');
                            loadData(currentPeriod.id);
                        } else {
                            Swal.fire('Gagal', res.message, 'error');
                        }
                    });
                }
            });
        });

        // Delete Period Button
        document.getElementById('btn-delete-period').addEventListener('click', function () {
            if (!currentPeriod) return;

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

        // Export Excel Button
        document.getElementById('btn-export-excel').addEventListener('click', function () {
            if (!currentPeriod) {
                showToast('Pilih periode terlebih dahulu', 'warning');
                return;
            }
            window.location.href = `{{ url('HR-dashboard/thr/export/excel') }}/${currentPeriod.id}`;
        });

        // Filter / Search event listeners
        document.getElementById('select-period').addEventListener('change', function () {
            loadData(this.value);
        });
        document.getElementById('input-search').addEventListener('input', () => applyFiltersAndRender(true));
        document.getElementById('filter-status').addEventListener('change', () => applyFiltersAndRender(true));
        document.getElementById('filter-kategori').addEventListener('change', () => applyFiltersAndRender(true));

        const perPageEl = document.getElementById('table-per-page');
        if (perPageEl) {
            perPageEl.addEventListener('change', function () {
                currentPage = 1;
                renderTable();
            });
        }

        // Refresh button
        document.getElementById('btn-refresh').addEventListener('click', function () {
            if (currentPeriod) {
                loadData(currentPeriod.id);
                showToast('Data dimuat ulang', 'info');
            } else {
                loadPeriods();
            }
        });

        // Toast Helper
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
    });
</script>
@endsection
