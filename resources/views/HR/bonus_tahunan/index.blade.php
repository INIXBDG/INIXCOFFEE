@extends('layout_HR.app')

@section('title', 'Bonus Tahunan - INIX HR')

@section('content_HR')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
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

        .page-header {
            margin-bottom: 1.5rem;
        }

        .page-title {
            font-size: 1.65rem;
            font-weight: 800;
            color: var(--gray-900);
            letter-spacing: -0.02em;
        }

        .page-sub {
            color: var(--gray-400);
            font-size: 0.875rem;
        }

        .stat-card {
            border: none;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            transition: transform 0.2s, box-shadow 0.2s;
            overflow: hidden;
            background: #fff;
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
            font-size: 1.35rem;
            color: #fff;
        }

        .stat-value {
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--gray-900);
            margin: 0.35rem 0 0.15rem;
        }

        .stat-label {
            color: var(--gray-400);
            font-size: 0.78rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 0;
        }

        .card-shell {
            border: none;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            background: #fff;
        }

        #bonusTable {
            border-collapse: separate;
            border-spacing: 0;
            width: 100%;
        }

        #bonusTable thead th {
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            color: var(--gray-600);
            background: var(--gray-50);
            border-bottom: 2px solid var(--gray-200) !important;
            border-top: none !important;
            padding: 0.85rem 1rem;
            vertical-align: middle;
        }

        #bonusTable tbody tr {
            transition: background 0.15s;
        }

        #bonusTable tbody tr:hover {
            background: #f8faff !important;
        }

        #bonusTable tbody tr.row-tidak-berhak {
            background: #fafafa;
        }

        #bonusTable tbody tr.row-tidak-berhak td {
            opacity: 0.6;
        }

        #bonusTable tbody td {
            vertical-align: middle;
            font-size: 0.875rem;
            border-bottom: 1px solid var(--gray-100) !important;
            padding: 0.85rem 1rem;
        }

        .badge-status {
            padding: 0.32rem 0.7rem;
            border-radius: 20px;
            font-size: 0.72rem;
            font-weight: 700;
            display: inline-block;
        }

        .status-tetap {
            background: var(--success-light);
            color: var(--success);
        }

        .status-kontrak {
            background: var(--info-light);
            color: var(--info);
        }

        .status-probation {
            background: var(--warning-light);
            color: var(--warning);
        }

        .status-resign {
            background: var(--danger-light);
            color: var(--danger);
        }

        .badge-tidak-berhak {
            background: var(--danger-light);
            color: var(--danger);
            border-radius: 20px;
            font-size: 0.68rem;
            font-weight: 700;
            padding: 0.2rem 0.55rem;
            display: inline-block;
            margin-top: 0.25rem;
        }

        .bank-badge {
            background: #fff8e6;
            color: #b45309;
            border: 1px solid #fde68a;
            border-radius: 6px;
            padding: 0.2rem 0.55rem;
            font-family: 'SFMono-Regular', Consolas, monospace;
            font-size: 0.8rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
        }

        .skema-select {
            font-size: 0.8rem;
            font-weight: 600;
            padding: 0.32rem 0.6rem;
            border-radius: 8px;
            border: 1.5px solid var(--gray-200);
            transition: all 0.2s;
            cursor: pointer;
        }

        .skema-select:focus {
            border-color: var(--pri);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.15);
            outline: none;
        }

        .skema-select:disabled {
            cursor: not-allowed;
        }

        .skema-standar {
            background-color: #f3f4f6;
            color: #374151;
        }

        .skema-2x {
            background-color: #e0e7ff;
            color: #3730a3;
            font-weight: 700;
            border-color: #c7d2fe;
        }

        .skema-4x {
            background-color: #ede9fe;
            color: #5b21b6;
            font-weight: 800;
            border-color: #ddd6fe;
        }

        .skema-custom {
            background-color: #fef3c7;
            color: #92400e;
            border-color: #fde68a;
        }

        .bonus-amount {
            font-size: 0.95rem;
            font-weight: 800;
            color: var(--pri);
            letter-spacing: -0.01em;
        }

        .export-option {
            border: 1.5px solid var(--gray-200);
            border-radius: 10px;
            padding: 0.75rem 0.9rem;
            cursor: pointer;
            transition: all 0.15s;
            display: block;
            margin-bottom: 0.5rem;
        }

        .export-option:hover {
            border-color: var(--pri);
            background: var(--pri-light);
        }

        .export-option input {
            margin-right: 0.5rem;
        }

        .export-option.active {
            border-color: var(--pri);
            background: var(--pri-light);
        }

        .export-option .opt-title {
            font-weight: 700;
            font-size: 0.88rem;
            color: var(--gray-900);
        }

        .export-option .opt-desc {
            font-size: 0.75rem;
            color: var(--gray-600);
            display: block;
            margin-left: 1.5rem;
        }

        #toast-container {
            position: fixed;
            top: 1.5rem;
            right: 1.5rem;
            z-index: 99999;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .toast-msg {
            padding: 0.85rem 1.25rem;
            border-radius: 10px;
            font-weight: 600;
            font-size: 0.875rem;
            color: #fff;
            box-shadow: var(--shadow-lg);
            display: flex;
            align-items: center;
            gap: 0.6rem;
            animation: slideIn 0.25s ease-out;
        }

        .toast-success {
            background: #059669;
        }

        .toast-error {
            background: #dc2626;
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

        <div class="card card-shell mb-4">
            <div class="card-body py-3">
                <div class="row g-2 align-items-center">
                    <div class="col-12 col-md-auto">
                        <span class="fw-bold text-primary small d-flex align-items-center gap-1">
                            <i class="fa-solid fa-filter"></i> Filter Data:
                        </span>
                    </div>

                    <div class="col-6 col-md-auto">
                        <label class="visually-hidden" for="filterTahun">Tahun Pencairan</label>
                        <select id="filterTahun" class="form-select form-select-sm" style="min-width: 150px;"
                            onchange="loadData()">
                            @for ($y = date('Y') - 2; $y <= date('Y') + 1; $y++)
                                <option value="{{ $y }}" {{ $y == $tahunSekarang ? 'selected' : '' }}>Pencairan
                                    {{ $y }}</option>
                            @endfor
                        </select>
                    </div>

                    <div class="col-6 col-md-auto">
                        <label class="visually-hidden" for="filterDivisi">Divisi</label>
                        <select id="filterDivisi" class="form-select form-select-sm" style="min-width: 140px;"
                            onchange="loadData()">
                            <option value="all">Semua Divisi</option>
                            @foreach ($divisiList as $div)
                                <option value="{{ $div }}">{{ $div }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-6 col-md-auto">
                        <label class="visually-hidden" for="filterStatus">Status</label>
                        <select id="filterStatus" class="form-select form-select-sm" style="min-width: 140px;"
                            onchange="loadData()">
                            <option value="all">Semua Status</option>
                            <option value="Karyawan Tetap">Karyawan Tetap</option>
                            <option value="Karyawan Kontrak">Karyawan Kontrak</option>
                            <option value="Karyawan Probation">Karyawan Probation</option>
                            <option value="Resign">Resign</option>
                        </select>
                    </div>

                    <div class="col-6 col-md-auto">
                        <label class="visually-hidden" for="filterSkema">Skema</label>
                        <select id="filterSkema" class="form-select form-select-sm" style="min-width: 140px;"
                            onchange="loadData()">
                            <option value="all">Semua Skema</option>
                            <option value="standar">Standar (Prorata)</option>
                            <option value="2x">2x Basic Salary</option>
                            <option value="4x">4x Basic Salary</option>
                            <option value="custom">Custom</option>
                        </select>
                    </div>

                    <div class="col-12 col-md">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white border-end-0 text-muted"><i
                                    class="fa-solid fa-magnifying-glass"></i></span>
                            <input type="text" id="searchKeyword" class="form-control border-start-0"
                                placeholder="Cari Nama, NIP, Jabatan, Rekening..." onkeyup="debounceSearch()">
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

        <div class="row g-3 mb-4">
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
                            <small class="text-muted" style="font-size: 0.72rem;">Gaji Pokok + Tunj. Jabatan</small>
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
                            <div class="d-flex gap-2 mt-2" style="font-size: 0.75rem;">
                                <span class="badge bg-secondary" id="badgeStandar" title="Skema Standar/Prorata">1x:
                                    0</span>
                                <span class="badge" style="background: #4f46e5 !important;" id="badge2x"
                                    title="2x Gaji">2x: 0</span>
                                <span class="badge" style="background: #7c3aed !important;" id="badge4x"
                                    title="4x Gaji">4x: 0</span>
                            </div>
                            <small class="text-muted d-block mt-1" style="font-size: 0.7rem;">Opsi cepat via dropdown
                                tabel</small>
                        </div>
                        <div class="stat-icon" style="background: linear-gradient(135deg, #d97706, #f59e0b);">
                            <i class="fa-solid fa-calculator"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card card-shell">
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
                        <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" id="bulkActionBtn"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fa-solid fa-wand-magic-sparkles me-1"></i>Set Skema Semua
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0" aria-labelledby="bulkActionBtn">
                            <li>
                                <h6 class="dropdown-header">Ubah Skema Sekaligus:</h6>
                            </li>
                            <li><a class="dropdown-item" href="javascript:void(0)"
                                    onclick="applyBulkScheme('standar')"><i
                                        class="fa-solid fa-check text-muted me-2"></i>Semua ke Standar (1x / Prorata)</a>
                            </li>
                            <li><a class="dropdown-item" href="javascript:void(0)" onclick="applyBulkScheme('2x')"><i
                                        class="fa-solid fa-bolt text-primary me-2"></i>Semua ke 2x Basic Salary</a></li>
                            <li><a class="dropdown-item" href="javascript:void(0)" onclick="applyBulkScheme('4x')"><i
                                        class="fa-solid fa-fire me-2" style="color:#7c3aed;"></i>Semua ke 4x Basic
                                    Salary</a></li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table id="bonusTable" class="table table-hover mb-0">
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
                                <th style="width: 70px;" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="bonusTableBody">
                            <tr>
                                <td colspan="10" class="text-center py-5 text-muted">
                                    <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
                                    Memuat data bonus tahunan...
                                </td>
                            </tr>
                        </tbody>
                        <tfoot class="bg-light fw-bold">
                            <tr>
                                <td colspan="3" class="text-end text-uppercase" style="letter-spacing: 0.5px;">Total
                                    Keseluruhan:</td>
                                <td class="text-end text-dark" id="tfootTotalBasic">Rp 0</td>
                                <td colspan="4"></td>
                                <td class="text-end text-primary fs-6" id="tfootTotalBonus">Rp 0</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalEditBonus" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 14px;">
                <div class="modal-header bg-primary text-white border-0" style="border-radius: 14px 14px 0 0;">
                    <h5 class="modal-title fw-bold">
                        <i class="fa-solid fa-sliders me-2"></i>Penyesuaian Basic Salary & Bonus
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <input type="hidden" id="editKaryawanId">
                    <input type="hidden" id="editTahun">

                    <div class="p-3 bg-light rounded-3 mb-3 border">
                        <div class="row g-2">
                            <div class="col-7">
                                <small class="text-muted d-block text-uppercase"
                                    style="font-size: 0.68rem; letter-spacing: 0.5px;">Nama Karyawan</small>
                                <strong id="editNama" class="text-dark d-block text-truncate">-</strong>
                                <small id="editNip" class="text-muted" style="font-size: 0.72rem;">NIP: -</small>
                            </div>
                            <div class="col-5 text-end">
                                <small class="text-muted d-block text-uppercase"
                                    style="font-size: 0.68rem; letter-spacing: 0.5px;">Masa Kerja</small>
                                <span id="editMasaKerja" class="badge bg-secondary mb-1">-</span>
                                <small id="editStatusBadge" class="d-block text-muted"
                                    style="font-size: 0.72rem;">-</small>
                            </div>
                            <div class="col-12 border-top pt-2 mt-2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-muted small"><i class="fa-solid fa-briefcase me-1"></i><span
                                            id="editJabatan">-</span></span>
                                    <span class="text-muted small"><i class="fa-solid fa-credit-card me-1"></i><span
                                            id="editRekening">-</span></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label
                            class="form-label fw-bold small text-dark d-flex justify-content-between align-items-center">
                            <span><i class="fa-solid fa-money-bill-wave text-success me-1"></i>Basic Salary (Gaji Pokok)
                                <span class="text-danger">*</span></span>
                            <span class="badge bg-light text-primary border" id="badgeBasicStatus">Isi Langsung</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-white fw-bold text-muted">Rp</span>
                            <input type="number" id="inputEditBasicSalary" class="form-control fw-bold text-dark"
                                placeholder="Contoh: 5000000" oninput="updateLiveCalculationPreview()">
                        </div>
                        <small class="text-muted d-block mt-1" style="font-size: 0.73rem;">
                            <i class="fa-solid fa-circle-info text-primary me-1"></i>Gaji yang diinput otomatis tersimpan
                            ke master data karyawan & menjadi dasar pengali bonus.
                        </small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-dark">
                            <i class="fa-solid fa-calculator text-primary me-1"></i>Pilih Skema Bonus
                        </label>
                        <select id="editSkema" class="form-select" onchange="onModalSkemaChanged()">
                            <option value="standar">Standar (Prorata Masa Kerja / 1x Gaji)</option>
                            <option value="2x">2x Basic Salary (2 Kali Gaji Pokok)</option>
                            <option value="4x">4x Basic Salary (4 Kali Gaji Pokok)</option>
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
                        <input type="radio" name="exportFormat" value="0" checked
                            onchange="onExportModeChanged()">
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

    <script>
        let searchTimer = null;
        let cachedBonusData = [];

        const formatIDR = (num) => {
            const val = typeof num === 'number' ? num : (parseFloat(num) || 0);
            return 'Rp ' + Math.round(val).toLocaleString('id-ID');
        };

        const escapeHtml = (str) => String(str ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');

        function showToast(message, isSuccess = true) {
            const container = document.getElementById('toast-container');
            const toast = document.createElement('div');
            toast.className = `toast-msg ${isSuccess ? 'toast-success' : 'toast-error'}`;
            toast.innerHTML =
                `<i class="fa-solid ${isSuccess ? 'fa-circle-check' : 'fa-circle-exclamation'}"></i><span>${message}</span>`;
            container.appendChild(toast);
            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transform = 'translateX(50px)';
                toast.style.transition = 'all 0.3s ease';
                setTimeout(() => toast.remove(), 300);
            }, 3500);
        }

        function debounceSearch() {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(() => {
                loadData();
            }, 300);
        }

        function resetFilter() {
            document.getElementById('filterDivisi').value = 'all';
            document.getElementById('filterStatus').value = 'all';
            document.getElementById('filterSkema').value = 'all';
            document.getElementById('searchKeyword').value = '';
            loadData();
        }

        function loadData() {
            const tahun = document.getElementById('filterTahun').value;
            const divisi = document.getElementById('filterDivisi').value;
            const status = document.getElementById('filterStatus').value;
            const skema = document.getElementById('filterSkema').value;
            const search = document.getElementById('searchKeyword').value;

            document.getElementById('labelTahun').textContent = tahun;
            document.getElementById('labelTahunBonus').textContent = parseInt(tahun) - 1;
            document.getElementById('labelCutoffTahun').textContent = parseInt(tahun) - 1;

            const tbody = document.getElementById('bonusTableBody');
            tbody.innerHTML = `
                <tr>
                    <td colspan="10" class="text-center py-5 text-muted">
                        <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
                        Memuat data bonus tahunan...
                    </td>
                </tr>
            `;

            $.ajax({
                url: "{{ route('HR.bonus-tahunan.data') }}",
                type: "GET",
                data: {
                    year: tahun,
                    divisi: divisi,
                    status_karyawan: status,
                    skema_bonus: skema,
                    search: search
                },
                success: function(res) {
                    if (res.success) {
                        cachedBonusData = res.data;
                        renderTable(res.data);
                        renderSummary(res.summary);
                    } else {
                        showToast(res.message || 'Gagal mengambil data', false);
                    }
                },
                error: function(xhr) {
                    const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message :
                        'Terjadi kesalahan saat memuat data.';
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="10" class="text-center py-5 text-danger">
                                <i class="fa-solid fa-triangle-exclamation me-2"></i>${msg}
                            </td>
                        </tr>
                    `;
                    showToast(msg, false);
                }
            });
        }

        function renderSummary(summary) {
            document.getElementById('statTotalKaryawan').textContent = summary.total_karyawan || 0;
            document.getElementById('statTidakBerhak').textContent = summary.total_tidak_berhak || 0;
            document.getElementById('statTotalBonus').textContent = formatIDR(summary.total_nominal_bonus || 0);
            document.getElementById('statTotalBasic').textContent = formatIDR(summary.total_basic_salary || 0);

            document.getElementById('badgeStandar').textContent = '1x: ' + (summary.count_standar || 0);
            document.getElementById('badge2x').textContent = '2x: ' + (summary.count_2x || 0);
            document.getElementById('badge4x').textContent = '4x: ' + (summary.count_4x || 0);

            document.getElementById('tfootTotalBasic').textContent = formatIDR(summary.total_basic_salary || 0);
            document.getElementById('tfootTotalBonus').textContent = formatIDR(summary.total_nominal_bonus || 0);
        }

        function renderTable(data) {
            const tbody = document.getElementById('bonusTableBody');
            document.getElementById('rowCountBadge').textContent = `${data.length} Data`;

            if (!data || data.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="10" class="text-center py-5 text-muted">
                            <i class="fa-regular fa-folder-open fa-2x mb-2 d-block opacity-50"></i>
                            Tidak ada data karyawan yang sesuai dengan filter.
                        </td>
                    </tr>
                `;
                return;
            }

            let html = '';
            data.forEach((item, index) => {
                let statusBadgeClass = 'status-tetap';
                if (item.status_karyawan === 'Karyawan Kontrak') statusBadgeClass = 'status-kontrak';
                else if (item.status_karyawan === 'Karyawan Probation') statusBadgeClass = 'status-probation';
                else if (item.status_karyawan === 'Resign') statusBadgeClass = 'status-resign';

                let skemaClass = 'skema-standar';
                if (item.skema_bonus === '2x') skemaClass = 'skema-2x';
                else if (item.skema_bonus === '4x') skemaClass = 'skema-4x';
                else if (item.skema_bonus === 'custom') skemaClass = 'skema-custom';

                const maybankDisplay = (item.rekening_maybank && item.rekening_maybank !== '-') ?
                    `<span class="bank-badge"><i class="fa-solid fa-credit-card"></i> ${item.rekening_maybank}</span>` :
                    `<span class="text-muted small">-</span>`;

                const nominalDisplay = (item.berhak && item.nominal_bonus && item.nominal_bonus > 0) ?
                    `<span class="bonus-amount" id="bonus-val-${item.karyawan_id}">${formatIDR(item.nominal_bonus)}</span>` :
                    `<span class="text-muted fw-bold" id="bonus-val-${item.karyawan_id}">Rp 0</span>`;

                const masaKerjaCell = item.berhak ?
                    `<span class="fw-bold text-dark d-block">${item.masa_kerja}</span>
                       <small class="d-block text-muted" style="font-size:0.7rem;">${item.is_direksi ? 'Direksi (default 12 bln)' : 'Gabung: ' + item.tanggal_bergabung}</small>
                       <small class="d-block text-primary" style="font-size:0.7rem;">Dihitung: ${item.bulan_kerja} bulan</small>` :
                    `<span class="fw-bold text-muted d-block">-</span>
                       <small class="d-block text-muted" style="font-size:0.7rem;">Gabung: ${item.tanggal_bergabung}</small>
                       <span class="badge-tidak-berhak">Belum Berhak</span>`;

                const disabledAttr = item.berhak ? '' : 'disabled';

                html += `
                    <tr id="row-${item.karyawan_id}" class="${item.berhak ? '' : 'row-tidak-berhak'}">
                        <td class="text-center text-muted fw-semibold">${index + 1}</td>
                        <td>
                            <strong class="text-dark d-block">${item.jabatan}</strong>
                            <small class="text-muted">${item.divisi}</small>
                        </td>
                        <td>
                            <span class="fw-bold text-dark d-block">${item.nama_lengkap}</span>
                            <small class="text-muted">NIP: ${item.nip}</small>
                        </td>
                        <td class="text-end fw-semibold text-secondary">
                            ${formatIDR(item.basic_salary)}
                        </td>
                        <td class="text-center">
                            ${maybankDisplay}
                        </td>
                        <td class="text-center">
                            <span class="badge-status ${statusBadgeClass}">${item.status_karyawan}</span>
                        </td>
                        <td class="text-center">
                            ${masaKerjaCell}
                        </td>
                        <td class="text-center">
                            <select class="form-select form-select-sm skema-select ${skemaClass}"
                                    id="select-skema-${item.karyawan_id}"
                                    ${disabledAttr}
                                    onchange="onSkemaChange(${item.karyawan_id}, this.value)">
                                <option value="standar" ${item.skema_bonus === 'standar' ? 'selected' : ''}>Standar (Prorata)</option>
                                <option value="2x" ${item.skema_bonus === '2x' ? 'selected' : ''}>2x Basic Salary</option>
                                <option value="4x" ${item.skema_bonus === '4x' ? 'selected' : ''}>4x Basic Salary</option>
                                <option value="custom" ${item.skema_bonus === 'custom' ? 'selected' : ''}>Custom Nominal</option>
                            </select>
                        </td>
                        <td class="text-end">
                            ${nominalDisplay}
                        </td>
                        <td class="text-center">
                            <button type="button" class="btn btn-sm btn-light text-primary"
                                    ${disabledAttr}
                                    title="Isi / Sesuaikan Basic Salary & Bonus"
                                    onclick="openEditModal(${item.karyawan_id})">
                                <i class="fa-solid fa-sliders"></i>
                            </button>
                        </td>
                    </tr>
                `;
            });

            tbody.innerHTML = html;
        }

        function onSkemaChange(karyawanId, newSkema) {
            const item = cachedBonusData.find(x => x.karyawan_id === karyawanId);

            if (!item || !item.berhak) {
                showToast('Karyawan ini belum berhak menerima bonus.', false);
                loadData();
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
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {
                    karyawan_id: karyawanId,
                    tahun: tahun,
                    skema_bonus: newSkema
                },
                success: function(res) {
                    if (res.success) {
                        showToast(res.message, true);
                    } else {
                        showToast(res.message || 'Gagal mengubah skema', false);
                    }
                    loadData();
                },
                error: function(xhr) {
                    const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message :
                        'Gagal menghubungi server';
                    showToast(msg, false);
                    loadData();
                }
            });
        }

        function openEditModal(karyawanId) {
            const item = cachedBonusData.find(x => x.karyawan_id === karyawanId);
            if (!item || !item.berhak) return;

            const tahun = document.getElementById('filterTahun').value;

            document.getElementById('editKaryawanId').value = item.karyawan_id;
            document.getElementById('editTahun').value = tahun;
            document.getElementById('editNama').textContent = item.nama_lengkap;
            document.getElementById('editNip').textContent = `NIP: ${item.nip || '-'}`;
            document.getElementById('editJabatan').textContent = `${item.jabatan} • ${item.divisi}`;
            document.getElementById('editMasaKerja').textContent = `${item.masa_kerja} (${item.bulan_kerja} bln dihitung)`;
            document.getElementById('editStatusBadge').textContent = item.status_karyawan;
            document.getElementById('editRekening').textContent = (item.rekening_maybank && item.rekening_maybank !== '-') ?
                item.rekening_maybank : 'Rek: Belum ada';

            const inputBasic = document.getElementById('inputEditBasicSalary');
            inputBasic.value = (item.basic_salary && item.basic_salary > 0) ? Math.round(item.basic_salary) : '';

            document.getElementById('editSkema').value = item.skema_bonus || 'standar';
            document.getElementById('editCustomNominal').value = Math.round(item.nominal_bonus || 0);
            document.getElementById('editCatatan').value = item.catatan || '';

            toggleCustomNominalInput();
            updateLiveCalculationPreview();

            const modal = new bootstrap.Modal(document.getElementById('modalEditBonus'));
            modal.show();
        }

        function onModalSkemaChanged() {
            toggleCustomNominalInput();
            updateLiveCalculationPreview();
        }

        function toggleCustomNominalInput() {
            const skema = document.getElementById('editSkema').value;
            const grp = document.getElementById('groupCustomNominal');
            grp.style.display = (skema === 'custom') ? 'block' : 'none';
        }

        function updateLiveCalculationPreview() {
            const karyawanId = parseInt(document.getElementById('editKaryawanId').value);
            const item = cachedBonusData.find(x => x.karyawan_id === karyawanId);
            const basicSalary = parseFloat(document.getElementById('inputEditBasicSalary').value) || 0;
            const skema = document.getElementById('editSkema').value;
            const customNominal = parseFloat(document.getElementById('editCustomNominal').value) || 0;

            const bulanKerja = item ? (item.bulan_kerja ?? item.masa_kerja_bulan ?? 0) : 0;

            let result = 0;
            let explanation = '';

            if (skema === '2x') {
                result = Math.round(2.00 * (basicSalary / 12) * bulanKerja);
                explanation = `2 × (${formatIDR(basicSalary)} ÷ 12) × ${bulanKerja} bln`;
            } else if (skema === '4x') {
                result = Math.round(4.00 * (basicSalary / 12) * bulanKerja);
                explanation = `4 × (${formatIDR(basicSalary)} ÷ 12) × ${bulanKerja} bln`;
            } else if (skema === 'custom') {
                result = customNominal;
                explanation = `Nominal kustom yang ditentukan secara manual`;
            } else {
                result = Math.round((basicSalary / 12) * bulanKerja);
                explanation = `(${formatIDR(basicSalary)} ÷ 12) × ${bulanKerja} bln`;
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

            if (skema !== 'custom' && (!basicSalary || parseFloat(basicSalary) <= 0)) {
                showToast('Harap masukkan nominal Basic Salary (Gaji Pokok) terlebih dahulu.', false);
                const inputGaji = document.getElementById('inputEditBasicSalary');
                if (inputGaji) inputGaji.focus();
                return;
            }

            $.ajax({
                url: "{{ route('HR.bonus-tahunan.update-skema') }}",
                type: "POST",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {
                    karyawan_id: karyawanId,
                    tahun: tahun,
                    skema_bonus: skema,
                    basic_salary: basicSalary,
                    custom_nominal: customNominal,
                    catatan: catatan
                },
                success: function(res) {
                    if (res.success) {
                        showToast(res.message, true);
                        const modalEl = document.getElementById('modalEditBonus');
                        const modal = bootstrap.Modal.getInstance(modalEl);
                        if (modal) modal.hide();
                        loadData();
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
            const labelMap = {
                'standar': 'Standar (1x/Prorata)',
                '2x': '2x Basic Salary',
                '4x': '4x Basic Salary'
            };

            if (!confirm(
                `Atur SEMUA karyawan yang berhak ke skema: ${labelMap[skema]} untuk pencairan Desember ${tahun}?`)) {
                return;
            }

            $.ajax({
                url: "{{ route('HR.bonus-tahunan.bulk-update') }}",
                type: "POST",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {
                    tahun: tahun,
                    skema_bonus: skema
                },
                success: function(res) {
                    if (res.success) {
                        showToast(res.message, true);
                        loadData();
                    } else {
                        showToast(res.message || 'Gagal bulk update', false);
                    }
                },
                error: function() {
                    showToast('Gagal bulk update skema', false);
                }
            });
        }

        function recalculateAll() {
            const tahun = document.getElementById('filterTahun').value;
            if (!confirm(
                `Hitung ulang dan sinkronisasikan seluruh data bonus tahunan untuk pencairan Desember ${tahun}?`)) {
                return;
            }

            $.ajax({
                url: "{{ route('HR.bonus-tahunan.recalculate') }}",
                type: "POST",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {
                    year: tahun
                },
                success: function(res) {
                    if (res.success) {
                        showToast(res.message, true);
                        loadData();
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

            return `year=${tahun}&divisi=${encodeURIComponent(divisi)}&status_karyawan=${encodeURIComponent(status)}&skema_bonus=${skema}&search=${encodeURIComponent(search)}`;
        }

        function exportExcel() {
            window.location.href = `{{ route('HR.bonus-tahunan.export.excel') }}?${buildExportQuery()}`;
        }

        function openExportPdfModal() {
            const eligible = cachedBonusData.filter(x => x.berhak);
            const sel = document.getElementById('exportKaryawan');

            sel.innerHTML = eligible.length ?
                eligible.map(x =>
                    `<option value="${x.karyawan_id}">${escapeHtml(x.nama_lengkap)} — ${escapeHtml(x.jabatan)}</option>`)
                .join('') :
                '<option value="">Tidak ada karyawan yang berhak</option>';

            onExportModeChanged();

            const modal = new bootstrap.Modal(document.getElementById('modalExportPdf'));
            modal.show();
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

            const modalEl = document.getElementById('modalExportPdf');
            const modal = bootstrap.Modal.getInstance(modalEl);
            if (modal) modal.hide();

            window.location.href = `{{ route('HR.bonus-tahunan.export.pdf') }}?${query}`;
        }

        document.addEventListener("DOMContentLoaded", function() {
            loadData();
        });
    </script>
@endsection
