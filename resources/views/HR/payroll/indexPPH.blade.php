@extends('layout_HR.app')
@section('content_HR')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">

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

        /* ===== CUSTOM COMPONENTS (tidak ada di Bootstrap) ===== */
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

        .table-modern {
            border-collapse: separate;
            border-spacing: 0;
            width: 100%;
        }

        .table-modern thead th {
            font-size: .72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .5px;
            color: var(--gray-600);
            background: var(--gray-50);
            border-bottom: 2px solid var(--gray-200) !important;
            border-top: none !important;
            padding: 0.85rem 1rem;
        }

        .table-modern tbody tr {
            transition: background .15s;
        }

        .table-modern tbody tr:hover {
            background: var(--pri-light) !important;
        }

        .table-modern tbody td {
            vertical-align: middle;
            font-size: .875rem;
            border-bottom: 1px solid var(--gray-100) !important;
            border-top: none !important;
            padding: 0.85rem 1rem;
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

        .child-name {
            flex: 1;
            font-weight: 500;
            color: var(--gray-700);
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
    </style>

    <div class="container-fluid px-4 py-4">
        <!-- Page Header -->
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <div>
                <h1 class="h4 fw-bold mb-1">PPH 21 Karyawan</h1>
                <p class="text-muted small mb-0">Kelola perhitungan pajak penghasilan Pasal 21 karyawan</p>
            </div>
            <div class="d-flex gap-2 mt-2 mt-sm-0">
                <button class="btn btn-outline-sec btn-sm"><i class="fa-solid fa-file-excel me-1"></i>Export Excel</button>
                <button class="btn btn-pri btn-sm" onclick="openModal('create')"><i class="fa-solid fa-plus me-1"></i>Tambah
                    PPH 21</button>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="card card-shell mb-4">
            <div class="card-body py-3">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <span class="fw-bold small" style="color:var(--pri)">Filter Data</span>
                    <div class="d-flex gap-2 flex-wrap">
                        <select class="form-select form-select-sm" style="width:140px" id="filterDivisi">
                            <option value="">Semua Divisi</option>
                            <option>IT</option>
                            <option>HRD</option>
                            <option>Finance</option>
                            <option>Marketing</option>
                        </select>
                        <select class="form-select form-select-sm" style="width:140px" id="filterPtkp">
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
                            <span class="input-group-text bg-white border-end-0"><i
                                    class="fa-solid fa-search small text-muted"></i></span>
                            <input type="text" class="form-control border-start-0" placeholder="Cari NIK atau nama..."
                                id="filterSearch">
                        </div>
                        <button class="btn btn-pri btn-sm" onclick="renderTable()"><i
                                class="fa-solid fa-check me-1"></i>Terapkan</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row g-3 mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="card stat-card">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div>
                            <p class="text-muted small text-uppercase fw-bold mb-1" style="font-size:.75rem">Total Karyawan
                            </p>
                            <h3 class="fw-bold mb-0" id="statTotal">0</h3>
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
                            <p class="text-muted small text-uppercase fw-bold mb-1" style="font-size:.75rem">Sudah Ada PTKP
                            </p>
                            <h3 class="fw-bold mb-0" style="color:var(--success)" id="statSudah">0</h3>
                        </div>
                        <div class="stat-icon" style="background:linear-gradient(135deg,#059669,#10b981)"><i
                                class="fa-solid fa-check"></i></div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card stat-card">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div>
                            <p class="text-muted small text-uppercase fw-bold mb-1" style="font-size:.75rem">Belum Ada PTKP
                            </p>
                            <h3 class="fw-bold mb-0" style="color:var(--warning)" id="statBelum">0</h3>
                        </div>
                        <div class="stat-icon" style="background:linear-gradient(135deg,#d97706,#f59e0b)"><i
                                class="fa-solid fa-exclamation"></i></div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card stat-card">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div>
                            <p class="text-muted small text-uppercase fw-bold mb-1" style="font-size:.75rem">Total PPH
                                21/Bulan</p>
                            <h3 class="fw-bold mb-0" id="statTotalPph">Rp 0</h3>
                        </div>
                        <div class="stat-icon" style="background:linear-gradient(135deg,#0284c7,#38bdf8)"><i
                                class="fa-solid fa-calculator"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="card card-shell">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table-modern mb-0">
                        <thead>
                            <tr>
                                <th>NIK</th>
                                <th>Nama</th>
                                <th>Jabatan</th>
                                <th class="text-end">Basic Salary</th>
                                <th class="text-center">Status PTKP</th>
                                <th class="text-center">Status Menikah</th>
                                <th class="text-center">Jml Anak</th>
                                <th class="text-end">PPH 21/Bulan</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="tableBody"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Tambah/Edit PPH 21 -->
    <div class="modal fade" id="modalPph21" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content" style="border-radius:12px;border:none">
                <div class="modal-header modal-header-custom border-0">
                    <h5 class="modal-title" id="modalPph21Title">Setup PPH 21 Karyawan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="editId" value="">

                    <!-- Data Karyawan -->
                    <div class="section-divider mb-3"><i class="fa-solid fa-user"></i>Data Karyawan</div>
                    <div class="row g-2 mb-3">
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Pilih Karyawan <span
                                    class="text-danger">*</span></label>
                            <select class="form-select form-select-sm" id="inputKaryawan" onchange="onKaryawanChange()">
                                <option value="">— Pilih Karyawan —</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <div class="info-card h-100">
                                <div class="label">NIK</div>
                                <div class="value" id="infoNik">-</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-card h-100">
                                <div class="label">Basic Salary</div>
                                <div class="value" id="infoGaji">-</div>
                            </div>
                        </div>
                    </div>

                    <hr class="my-3">

                    <!-- Status PTKP -->
                    <div class="section-divider mb-3"><i class="fa-solid fa-id-card"></i>Status PTKP</div>
                    <div class="ptkp-info-box mb-3">
                        <i class="fa-solid fa-info-circle me-1"></i>
                        <strong>PTKP</strong> adalah jumlah penghasilan yang tidak dikenakan pajak, ditentukan berdasarkan
                        status perkawinan dan jumlah tanggungan.
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

                    <!-- Status Menikah -->
                    <div class="section-divider mb-3"><i class="fa-solid fa-heart"></i>Status Perkawinan</div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Status Menikah <span class="text-danger">*</span></label>
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

                    <!-- Form Anak -->
                    <div id="formAnak" class="mb-3 d-none">
                        <label class="form-label small fw-bold">Jumlah Anak</label>
                        <div class="row g-2 align-items-end mb-3">
                            <div class="col-md-4">
                                <select class="form-select form-select-sm" id="jumlahAnak" onchange="updateAnakList()">
                                    <option value="0">0 Anak</option>
                                    <option value="1">1 Anak</option>
                                    <option value="2">2 Anak</option>
                                    <option value="3">3 Anak</option>
                                    <option value="4">4 Anak</option>
                                    <option value="5">5 Anak</option>
                                    <option value="6">6 Anak</option>
                                    <option value="7">7 Anak</option>
                                    <option value="8">8 Anak</option>
                                    <option value="9">9 Anak</option>
                                    <option value="10">10 Anak</option>
                                </select>
                            </div>
                            <div class="col-md-8">
                                <small class="text-muted"><i class="fa-solid fa-circle-info me-1"></i>Semua anak dapat
                                    ditambahkan, namun hanya <strong>3 anak pertama</strong> yang menjadi tanggungan
                                    PTKP</small>
                            </div>
                        </div>

                        <div class="section-divider mb-2 mt-3"><i class="fa-solid fa-children"></i>Data Anak</div>
                        <div id="daftarAnak"></div>

                        <div id="tanggunganNote" class="tanggungan-note mt-2 d-none">
                            <i class="fa-solid fa-triangle-exclamation me-1"></i>
                            <span id="tanggunganNoteText"></span>
                        </div>
                    </div>

                    <div id="formAnakHidden" class="mb-3 d-none">
                        <div class="ptkp-info-box" style="background:var(--gray-50);border-color:var(--gray-200)">
                            <i class="fa-solid fa-info-circle me-1 text-muted"></i>
                            <span class="text-muted">Form data anak tidak diperlukan untuk status belum menikah.</span>
                        </div>
                    </div>

                    <hr class="my-3">

                    <!-- Preview Perhitungan -->
                    <div class="section-divider mb-3"><i class="fa-solid fa-calculator"></i>Preview Perhitungan PPH 21
                    </div>
                    <div class="calculation-preview">
                        <div class="calc-row mb-1">
                            <span class="text-muted">Basic Salary (Bulanan)</span>
                            <span class="fw-bold" id="prevBulanan">Rp 0</span>
                        </div>
                        <div class="calc-row mb-1">
                            <span class="text-muted">Basic Salary (Tahunan)</span>
                            <span class="fw-bold" id="prevTahunan">Rp 0</span>
                        </div>
                        <div class="calc-row mb-1">
                            <span class="text-muted">PTKP (<span id="prevPtkpCode">-</span>)</span>
                            <span class="fw-bold" style="color:var(--success)" id="prevPtkpValue">Rp 0</span>
                        </div>
                        <div class="calc-row mb-1">
                            <span class="text-muted">Penghasilan Kena Pajak (PKP)</span>
                            <span class="fw-bold" id="prevPkp">Rp 0</span>
                        </div>
                        <div class="calc-row mb-1">
                            <span class="text-muted">PPH 21 Terutang (Tahunan)</span>
                            <span class="fw-bold" id="prevPphTahunan">Rp 0</span>
                        </div>
                        <div class="calc-row calc-total">
                            <span>PPH 21 per Bulan</span>
                            <span id="prevPphBulanan">Rp 0</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer" style="border-top:1px solid var(--gray-100)">
                    <button type="button" class="btn btn-outline-sec" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-pri" onclick="simpanData()"><i
                            class="fa-solid fa-save me-1"></i>Simpan PPH 21</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Detail -->
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
                    <button type="button" class="btn btn-pri" id="btnEditFromDetail"><i
                            class="fa-solid fa-pen me-1"></i>Edit</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // ===== PTKP VALUES (Tahunan) =====
        const PTKP_VALUES = {
            'TK/0': 54000000,
            'TK/1': 58500000,
            'TK/2': 63000000,
            'TK/3': 67500000,
            'K/0': 58500000,
            'K/1': 63000000,
            'K/2': 67500000,
            'K/3': 72000000
        };

        // ===== DATA DUMMY KARYAWAN (untuk dropdown) =====
        const allKaryawans = [{
                id: 1,
                nik: '2019001',
                nama: 'Ahmad Rizki',
                jabatan: 'IT Staff',
                divisi: 'IT',
                gaji: 8500000
            },
            {
                id: 2,
                nik: '2018045',
                nama: 'Budi Santoso',
                jabatan: 'Finance Manager',
                divisi: 'Finance',
                gaji: 18000000
            },
            {
                id: 3,
                nik: '2020012',
                nama: 'Citra Dewi',
                jabatan: 'HR Specialist',
                divisi: 'HRD',
                gaji: 12000000
            },
            {
                id: 4,
                nik: '2021034',
                nama: 'Dian Pratama',
                jabatan: 'Marketing Staff',
                divisi: 'Marketing',
                gaji: 7500000
            },
            {
                id: 5,
                nik: '2017008',
                nama: 'Eko Prasetyo',
                jabatan: 'Senior Developer',
                divisi: 'IT',
                gaji: 22000000
            },
            {
                id: 6,
                nik: '2022056',
                nama: 'Fitri Handayani',
                jabatan: 'Admin Staff',
                divisi: 'HRD',
                gaji: 6500000
            },
            {
                id: 7,
                nik: '2016023',
                nama: 'Gunawan Wibowo',
                jabatan: 'CTO',
                divisi: 'IT',
                gaji: 35000000
            },
            {
                id: 8,
                nik: '2023011',
                nama: 'Hana Sari',
                jabatan: 'Junior Designer',
                divisi: 'Marketing',
                gaji: 5500000
            },
        ];

        // ===== DATA PPH 21 (disimpan) =====
        let pphData = [{
                id: 1,
                karyawan_id: 1,
                ptkp: 'TK/0',
                menikah: 0,
                anak: []
            },
            {
                id: 2,
                karyawan_id: 2,
                ptkp: 'K/2',
                menikah: 1,
                anak: [{
                    nama: 'Raka Santoso'
                }, {
                    nama: 'Aisyah Santoso'
                }]
            },
            {
                id: 3,
                karyawan_id: 3,
                ptkp: 'K/1',
                menikah: 1,
                anak: [{
                    nama: 'Bagas Pratama'
                }]
            },
            {
                id: 4,
                karyawan_id: 4,
                ptkp: 'TK/0',
                menikah: 0,
                anak: []
            },
            {
                id: 5,
                karyawan_id: 5,
                ptkp: 'K/3',
                menikah: 1,
                anak: [{
                    nama: 'Cindy Prasetyo'
                }, {
                    nama: 'Dafa Prasetyo'
                }, {
                    nama: 'Elsa Prasetyo'
                }, {
                    nama: 'Farhan Prasetyo'
                }]
            },
        ];

        let nextId = 6;

        // ===== HELPER FUNCTIONS =====
        function fmtRp(n) {
            return 'Rp ' + Math.round(n || 0).toLocaleString('id-ID');
        }

        function getKaryawan(id) {
            return allKaryawans.find(k => k.id == id);
        }

        function getPphByKaryawanId(karyawanId) {
            return pphData.find(p => p.karyawan_id == karyawanId);
        }

        // Hitung PPH 21 dengan tarif progresif UU HPP
        function hitungPph21(gajiBulanan, ptkpCode) {
            if (!gajiBulanan || !ptkpCode) return {
                bulanan: 0,
                tahunan: 0,
                pkp: 0,
                ptkp: 0
            };
            const gajiTahunan = gajiBulanan * 12;
            const ptkp = PTKP_VALUES[ptkpCode] || 0;
            let pkp = Math.max(0, gajiTahunan - ptkp);

            // Tarif progresif
            let pphTahunan = 0;
            const layers = [{
                    limit: 60000000,
                    rate: 0.05
                },
                {
                    limit: 250000000,
                    rate: 0.15
                },
                {
                    limit: 500000000,
                    rate: 0.25
                },
                {
                    limit: 5000000000,
                    rate: 0.30
                },
                {
                    limit: Infinity,
                    rate: 0.35
                }
            ];
            let remaining = pkp;
            let prevLimit = 0;
            for (const layer of layers) {
                if (remaining <= 0) break;
                const taxable = Math.min(remaining, layer.limit - prevLimit);
                pphTahunan += taxable * layer.rate;
                remaining -= taxable;
                prevLimit = layer.limit;
            }

            return {
                bulanan: Math.round(pphTahunan / 12),
                tahunan: Math.round(pphTahunan),
                pkp: Math.round(pkp),
                ptkp: ptkp,
                gajiTahunan: gajiTahunan
            };
        }

        // ===== RENDER TABLE =====
        function renderTable() {
            const filterDivisi = $('#filterDivisi').val();
            const filterPtkp = $('#filterPtkp').val();
            const filterSearch = $('#filterSearch').val().toLowerCase();

            let totalKaryawan = allKaryawans.length;
            let totalSudah = pphData.length;
            let totalBelum = totalKaryawan - totalSudah;
            let totalPph = 0;

            let html = '';
            allKaryawans.forEach(k => {
                if (filterDivisi && k.divisi !== filterDivisi) return;
                if (filterSearch && !k.nama.toLowerCase().includes(filterSearch) && !k.nik.includes(filterSearch))
                    return;

                const pph = getPphByKaryawanId(k.id);
                if (filterPtkp) {
                    if (!pph || pph.ptkp !== filterPtkp) return;
                }

                const calc = pph ? hitungPph21(k.gaji, pph.ptkp) : null;
                if (calc) totalPph += calc.bulanan;

                const statusBadge = pph ?
                    `<span class="status-badge badge-ptkp">${pph.ptkp}</span>` :
                    `<span class="text-muted small">Belum diset</span>`;

                const menikahBadge = pph ?
                    (pph.menikah ? `<span class="status-badge badge-menikah">Menikah</span>` :
                        `<span class="status-badge badge-belum">Belum</span>`) :
                    `<span class="text-muted">-</span>`;

                const jmlAnak = pph ? pph.anak.length : 0;
                const pphBulanan = calc ? `<strong>${fmtRp(calc.bulanan)}</strong>` :
                    `<span class="text-muted">Belum dihitung</span>`;

                const aksiBtn = pph ?
                    `<button class="btn btn-sm btn-outline-sec" onclick="openDetail(${k.id})" title="Detail"><i class="fa-solid fa-eye"></i></button>
                       <button class="btn btn-sm btn-pri" onclick="openModal('edit', ${k.id})" title="Edit"><i class="fa-solid fa-pen"></i></button>` :
                    `<button class="btn btn-sm btn-pri" onclick="openModal('create', ${k.id})" title="Setup"><i class="fa-solid fa-plus"></i></button>`;

                html += `
                    <tr>
                        <td><strong>${k.nik}</strong></td>
                        <td>
                            <strong style="color:var(--gray-900)">${k.nama}</strong>
                            <br><small class="text-muted">${k.jabatan}</small>
                        </td>
                        <td>${k.jabatan}</td>
                        <td class="text-end">${fmtRp(k.gaji)}</td>
                        <td class="text-center">${statusBadge}</td>
                        <td class="text-center">${menikahBadge}</td>
                        <td class="text-center">${jmlAnak}</td>
                        <td class="text-end">${pphBulanan}</td>
                        <td class="text-center"><div class="d-flex gap-1 justify-content-center">${aksiBtn}</div></td>
                    </tr>
                `;
            });

            if (!html) {
                html =
                    `<tr><td colspan="9" class="text-center text-muted py-5">Tidak ada data yang cocok dengan filter</td></tr>`;
            }

            $('#tableBody').html(html);

            // Update stats
            $('#statTotal').text(totalKaryawan);
            $('#statSudah').text(totalSudah);
            $('#statBelum').text(totalBelum);
            $('#statTotalPph').text(fmtRp(totalPph));
        }

        // ===== POPULATE DROPDOWN KARYAWAN =====
        function populateKaryawanDropdown(selectedId = '') {
            const select = $('#inputKaryawan');
            select.html('<option value="">— Pilih Karyawan —</option>');
            allKaryawans.forEach(k => {
                const hasPph = getPphByKaryawanId(k.id);
                const label = `${k.nik} - ${k.nama} (${k.jabatan})${hasPph ? ' ✓' : ''}`;
                select.append(`<option value="${k.id}" ${k.id == selectedId ? 'selected' : ''}>${label}</option>`);
            });
        }

        // ===== ON KARYAWAN CHANGE =====
        function onKaryawanChange() {
            const id = $('#inputKaryawan').val();
            if (!id) {
                $('#infoNik').text('-');
                $('#infoGaji').text('-');
                return;
            }
            const k = getKaryawan(id);
            $('#infoNik').text(k.nik);
            $('#infoGaji').text(fmtRp(k.gaji));
            updatePreview();
        }

        // ===== ON STATUS MENIKAH CHANGE =====
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

        // ===== UPDATE ANAK LIST =====
        function updateAnakList() {
            const jumlah = parseInt($('#jumlahAnak').val()) || 0;
            const container = $('#daftarAnak');
            const existingInputs = container.find('.child-name-input');
            const existingData = [];
            existingInputs.each(function() {
                existingData.push($(this).val());
            });

            container.html('');
            for (let i = 0; i < jumlah; i++) {
                const isTanggungan = i < 3;
                const nama = existingData[i] || '';
                container.append(`
                    <div class="child-item mb-2">
                        <div class="child-number">${i + 1}</div>
                        <input type="text" class="form-control form-control-sm child-name-input" placeholder="Nama Anak ${i + 1}" value="${nama}">
                        ${isTanggungan ? '<span class="child-tanggungan">Tanggungan</span>' : '<span class="text-muted small">Non-tanggungan</span>'}
                        <button type="button" class="btn btn-sm btn-outline-sec" onclick="hapusAnak(this)"><i class="fa-solid fa-trash"></i></button>
                    </div>
                `);
            }

            // Note jika anak > 3
            if (jumlah > 3) {
                $('#tanggunganNote').removeClass('d-none');
                $('#tanggunganNoteText').text(
                    `${jumlah} anak tercatat, namun hanya 3 anak pertama yang menjadi tanggungan PTKP (K/3)`);
            } else {
                $('#tanggunganNote').addClass('d-none');
            }

            updatePreview();
        }

        function hapusAnak(btn) {
            $(btn).closest('.child-item').remove();
            // Re-number
            $('#daftarAnak .child-item').each(function(i) {
                $(this).find('.child-number').text(i + 1);
                const isTanggungan = i < 3;
                $(this).find('.child-tanggungan').remove();
                $(this).find('.text-muted.small').filter(function() {
                    return $(this).text() === 'Non-tanggungan';
                }).remove();
                if (isTanggungan) {
                    $(this).find('.child-name-input').after('<span class="child-tanggungan">Tanggungan</span>');
                } else {
                    $(this).find('.child-name-input').after('<span class="text-muted small">Non-tanggungan</span>');
                }
            });
            const newCount = $('#daftarAnak .child-item').length;
            $('#jumlahAnak').val(newCount);
            if (newCount > 3) {
                $('#tanggunganNote').removeClass('d-none');
                $('#tanggunganNoteText').text(
                    `${newCount} anak tercatat, namun hanya 3 anak pertama yang menjadi tanggungan PTKP (K/3)`);
            } else {
                $('#tanggunganNote').addClass('d-none');
            }
            updatePreview();
        }

        // ===== UPDATE PREVIEW =====
        function updatePreview() {
            const kId = $('#inputKaryawan').val();
            const ptkpCode = $('#inputPtkp').val();

            if (!kId) {
                $('#prevBulanan, #prevTahunan, #prevPtkpValue, #prevPkp, #prevPphTahunan, #prevPphBulanan').text('Rp 0');
                $('#prevPtkpCode').text('-');
                $('#ptkpValueInfo').text('PTKP Tahunan: -');
                return;
            }

            const k = getKaryawan(kId);
            const calc = hitungPph21(k.gaji, ptkpCode);

            $('#prevBulanan').text(fmtRp(k.gaji));
            $('#prevTahunan').text(fmtRp(calc.gajiTahunan));
            $('#prevPtkpCode').text(ptkpCode || '-');
            $('#prevPtkpValue').text(fmtRp(calc.ptkp));
            $('#prevPkp').text(fmtRp(calc.pkp));
            $('#prevPphTahunan').text(fmtRp(calc.tahunan));
            $('#prevPphBulanan').text(fmtRp(calc.bulanan));

            if (ptkpCode) {
                $('#ptkpValueInfo').html(
                    `Tahun Pajak: <strong>2026</strong> | PTKP ${ptkpCode} = <strong>${fmtRp(PTKP_VALUES[ptkpCode])}/tahun</strong>`
                    );
            } else {
                $('#ptkpValueInfo').text('PTKP Tahunan: -');
            }
        }

        // ===== OPEN MODAL (CREATE / EDIT) =====
        function openModal(mode, karyawanId = null) {
            $('#editId').val('');
            $('#inputKaryawan').val('');
            $('#inputPtkp').val('');
            $('input[name="statusMenikah"]').prop('checked', false);
            $('#jumlahAnak').val('0');
            $('#daftarAnak').html('');
            $('#formAnak').addClass('d-none');
            $('#formAnakHidden').addClass('d-none');
            $('#tanggunganNote').addClass('d-none');
            $('#infoNik').text('-');
            $('#infoGaji').text('-');

            populateKaryawanDropdown();

            if (mode === 'edit' && karyawanId) {
                const pph = getPphByKaryawanId(karyawanId);
                if (!pph) {
                    alert('Data PPH 21 tidak ditemukan');
                    return;
                }

                $('#modalPph21Title').text('Edit PPH 21 Karyawan');
                $('#editId').val(pph.id);
                $('#inputKaryawan').val(pph.karyawan_id);
                $('#inputPtkp').val(pph.ptkp);

                const k = getKaryawan(pph.karyawan_id);
                $('#infoNik').text(k.nik);
                $('#infoGaji').text(fmtRp(k.gaji));

                if (pph.menikah) {
                    $('#menikahYes').prop('checked', true);
                    $('#formAnak').removeClass('d-none');
                    $('#jumlahAnak').val(pph.anak.length);
                    updateAnakList();
                    // Isi nama anak
                    $('#daftarAnak .child-name-input').each(function(i) {
                        if (pph.anak[i]) $(this).val(pph.anak[i].nama);
                    });
                } else {
                    $('#menikahNo').prop('checked', true);
                    $('#formAnakHidden').removeClass('d-none');
                }
            } else if (mode === 'create') {
                $('#modalPph21Title').text('Setup PPH 21 Karyawan');
                if (karyawanId) {
                    $('#inputKaryawan').val(karyawanId);
                    onKaryawanChange();
                }
            }

            updatePreview();
            new bootstrap.Modal(document.getElementById('modalPph21')).show();
        }

        // ===== SIMPAN DATA =====
        function simpanData() {
            const editId = $('#editId').val();
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
            if (!menikah && menikah !== '0') {
                alert('Pilih status menikah!');
                return;
            }

            let anak = [];
            if (menikah === '1') {
                $('#daftarAnak .child-name-input').each(function() {
                    const nama = $(this).val().trim();
                    if (nama) anak.push({
                        nama
                    });
                });
            }

            if (editId) {
                // Update
                const idx = pphData.findIndex(p => p.id == editId);
                if (idx >= 0) {
                    pphData[idx] = {
                        id: parseInt(editId),
                        karyawan_id: parseInt(kId),
                        ptkp,
                        menikah: parseInt(menikah),
                        anak
                    };
                }
                alert('Data PPH 21 berhasil diupdate!');
            } else {
                // Check duplicate
                if (getPphByKaryawanId(kId)) {
                    alert('Karyawan ini sudah memiliki data PPH 21!');
                    return;
                }
                pphData.push({
                    id: nextId++,
                    karyawan_id: parseInt(kId),
                    ptkp,
                    menikah: parseInt(menikah),
                    anak
                });
                alert('Data PPH 21 berhasil disimpan!');
            }

            bootstrap.Modal.getInstance(document.getElementById('modalPph21')).hide();
            renderTable();
        }

        // ===== OPEN DETAIL MODAL =====
        function openDetail(karyawanId) {
            const k = getKaryawan(karyawanId);
            const pph = getPphByKaryawanId(karyawanId);
            if (!k || !pph) return;

            const calc = hitungPph21(k.gaji, pph.ptkp);

            let anakHtml = '';
            if (pph.anak.length > 0) {
                pph.anak.forEach((a, i) => {
                    const isTanggungan = i < 3;
                    anakHtml += `
                        <div class="child-item mb-2">
                            <div class="child-number">${i + 1}</div>
                            <div class="child-name">${a.nama}</div>
                            ${isTanggungan ? '<span class="child-tanggungan">Tanggungan</span>' : '<span class="text-muted small">Non-tanggungan</span>'}
                        </div>
                    `;
                });
            } else {
                anakHtml = '<div class="text-muted small">Tidak ada data anak</div>';
            }

            if (pph.anak.length > 3) {
                anakHtml +=
                    `<div class="tanggungan-note mt-2"><i class="fa-solid fa-triangle-exclamation me-1"></i>${pph.anak.length} anak tercatat, hanya 3 anak pertama yang menjadi tanggungan PTKP</div>`;
            }

            const html = `
                <div class="row g-2 mb-3">
                    <div class="col-md-6"><div class="info-card"><div class="label">NIK</div><div class="value">${k.nik}</div></div></div>
                    <div class="col-md-6"><div class="info-card"><div class="label">Nama</div><div class="value">${k.nama}</div></div></div>
                    <div class="col-md-6"><div class="info-card"><div class="label">Jabatan</div><div class="value">${k.jabatan}</div></div></div>
                    <div class="col-md-6"><div class="info-card"><div class="label">Basic Salary</div><div class="value">${fmtRp(k.gaji)}</div></div></div>
                </div>

                <div class="section-divider mb-2"><i class="fa-solid fa-id-card"></i>Status PTKP</div>
                <div class="row g-2 mb-3">
                    <div class="col-md-4"><div class="info-card"><div class="label">Status PTKP</div><div class="value"><span class="status-badge badge-ptkp">${pph.ptkp}</span></div></div></div>
                    <div class="col-md-4"><div class="info-card"><div class="label">Status Menikah</div><div class="value">${pph.menikah ? '<span class="status-badge badge-menikah">Menikah</span>' : '<span class="status-badge badge-belum">Belum</span>'}</div></div></div>
                    <div class="col-md-4"><div class="info-card"><div class="label">Jumlah Anak</div><div class="value">${pph.anak.length} Anak</div></div></div>
                </div>

                ${pph.menikah ? `<div class="section-divider mb-2"><i class="fa-solid fa-children"></i>Daftar Anak</div>${anakHtml}` : ''}

                <div class="section-divider mb-2 mt-3"><i class="fa-solid fa-calculator"></i>Perhitungan PPH 21</div>
                <div class="calculation-preview">
                    <div class="calc-row mb-1"><span class="text-muted">Basic Salary (Bulanan)</span><span class="fw-bold">${fmtRp(k.gaji)}</span></div>
                    <div class="calc-row mb-1"><span class="text-muted">Basic Salary (Tahunan)</span><span class="fw-bold">${fmtRp(calc.gajiTahunan)}</span></div>
                    <div class="calc-row mb-1"><span class="text-muted">PTKP (${pph.ptkp})</span><span class="fw-bold" style="color:var(--success)">${fmtRp(calc.ptkp)}</span></div>
                    <div class="calc-row mb-1"><span class="text-muted">Penghasilan Kena Pajak</span><span class="fw-bold">${fmtRp(calc.pkp)}</span></div>
                    <div class="calc-row mb-1"><span class="text-muted">PPH 21 Terutang (Tahunan)</span><span class="fw-bold">${fmtRp(calc.tahunan)}</span></div>
                    <div class="calc-row calc-total"><span>PPH 21 per Bulan</span><span>${fmtRp(calc.bulanan)}</span></div>
                </div>
            `;

            $('#detailContent').html(html);
            $('#btnEditFromDetail').off('click').on('click', function() {
                bootstrap.Modal.getInstance(document.getElementById('modalDetail')).hide();
                setTimeout(() => openModal('edit', karyawanId), 300);
            });
            new bootstrap.Modal(document.getElementById('modalDetail')).show();
        }

        // ===== INIT =====
        $(document).ready(function() {
            renderTable();
        });
    </script>
@endsection
