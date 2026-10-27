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
            padding: 0.85rem 1rem;
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
    </style>

    <div class="container-fluid px-4 py-4">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <div>
                <h1 class="h4 fw-bold mb-1">PPH 21 Karyawan</h1>
                <p class="text-muted small mb-0">Diisi tiap bulan. Status PTKP cukup diisi sekali, bulan berikutnya tinggal
                    pakai data sebelumnya tanpa mengetik ulang.</p>
            </div>
            <div class="d-flex gap-2 mt-2 mt-sm-0 flex-wrap">
                <button class="btn btn-outline-secondary btn-sm"><i class="fa-solid fa-file-excel me-1"></i>Export Excel</button>
                <button class="btn btn-outline-secondary btn-sm" id="btnIsiOtomatis" onclick="isiOtomatis()"><i
                        class="fa-solid fa-wand-magic-sparkles me-1"></i>Isi Otomatis dari Bulan Sebelumnya</button>
                <button class="btn btn-primary btn-sm" id="btnTambahPph" onclick="openModal('create')"><i
                        class="fa-solid fa-plus me-1"></i>Tambah PPH 21</button>
                <button class="btn btn-outline-danger btn-sm" id="btnHapusSemua" onclick="confirmDeleteAllPph()">
                    <i class="fa-solid fa-trash-can me-1"></i>Hapus Semua Data
                </button>
            </div>
        </div>

        <div class="card card-shell mb-4">
            <div class="card-body py-3">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <span class="fw-bold small" style="color:var(--pri)">Periode</span>
                        <select class="form-select form-select-sm" style="width:140px" id="filterBulan"
                            onchange="loadData()"></select>
                        <select class="form-select form-select-sm" style="width:100px" id="filterTahun"
                            onchange="loadData()"></select>
                    </div>
                    <div class="d-flex gap-2 flex-wrap">
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
                            <span class="input-group-text bg-white border-end-0"><i
                                    class="fa-solid fa-search small text-muted"></i></span>
                            <input type="text" class="form-control border-start-0" placeholder="Cari NIP atau nama..."
                                id="filterSearch" onkeyup="renderTable()">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="lock-box mb-4 d-none" id="lockNotice">
            <i class="fa-solid fa-lock me-1"></i>Periode ini sudah dilaporkan ke Kortex sehingga data terkunci. Batalkan
            lapor Kortex jika perlu mengubah data.
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
                            <p class="text-muted small text-uppercase fw-bold mb-1" style="font-size:.75rem">Sudah Diisi
                                Bulan Ini</p>
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
                            <p class="text-muted small text-uppercase fw-bold mb-1" style="font-size:.75rem">Belum Diisi
                                Bulan Ini</p>
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
                            <p class="text-muted small text-uppercase fw-bold mb-1" style="font-size:.75rem">Total PPH 21
                                Bulan Ini</p>
                            <h3 class="fw-bold mb-0" id="statTotalPph">Rp 0</h3>
                        </div>
                        <div class="stat-icon" style="background:linear-gradient(135deg,#0284c7,#38bdf8)"><i
                                class="fa-solid fa-calculator"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card card-shell">
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
                            <div class="wz-label">Preview & Simpan</div>
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
                        <div class="ptkp-info-box mb-3"><i class="fa-solid fa-info-circle me-1"></i><strong>PTKP</strong>
                            adalah jumlah penghasilan yang tidak dikenakan pajak. Pilih sesuai status perkawinan dan
                            tanggungan.</div>
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
                        <div class="section-divider mb-3"><i class="fa-solid fa-heart"></i>Status Perkawinan & Anak</div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Status Menikah <span
                                    class="text-danger">*</span></label>
                            <div class="d-flex gap-3">
                                <div class="form-check"><input class="form-check-input" type="radio"
                                        name="statusMenikah" id="menikahYes" value="1"
                                        onchange="onStatusMenikahChange()"><label class="form-check-label"
                                        for="menikahYes">Menikah</label></div>
                                <div class="form-check"><input class="form-check-input" type="radio"
                                        name="statusMenikah" id="menikahNo" value="0"
                                        onchange="onStatusMenikahChange()"><label class="form-check-label"
                                        for="menikahNo">Belum Menikah</label></div>
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
                                <div class="col-md-8"><small class="text-muted"><i
                                            class="fa-solid fa-circle-info me-1"></i>Hanya <strong>3 anak pertama</strong>
                                        yang menjadi tanggungan PTKP</small></div>
                            </div>
                            <div class="section-divider mb-2 mt-3"><i class="fa-solid fa-children"></i>Data Anak</div>
                            <div id="daftarAnak"></div>
                            <div id="tanggunganNote" class="tanggungan-note mt-2 d-none"><i
                                    class="fa-solid fa-triangle-exclamation me-1"></i><span
                                    id="tanggunganNoteText"></span></div>
                        </div>
                        <div id="formAnakHidden" class="mb-3 d-none">
                            <div class="ptkp-info-box" style="background:var(--gray-50);border-color:var(--gray-200)"><i
                                    class="fa-solid fa-info-circle me-1 text-muted"></i><span class="text-muted">Form data
                                    anak tidak diperlukan untuk status belum menikah.</span></div>
                        </div>
                    </div>

                    <div class="wz-content d-none" id="wz2">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <h6 class="fw-bold mb-3"><i class="fa-solid fa-calculator me-2 text-primary"></i>Preview
                                    Perhitungan PPH 21</h6>
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
                                            <div class="value"><span class="status-badge badge-ptkp"
                                                    id="prevPtkpBadge">-</span></div>
                                        </div>
                                    </div>
                                </div>

                                <div class="section-divider mb-2"><i class="fa-solid fa-money-bill-wave"></i>Komponen Gaji
                                    (Dari Database)</div>
                                <div class="calculation-preview mb-3">
                                    <div class="calc-row mb-1"><span class="text-muted">Gaji Pokok</span><span
                                            class="fw-bold" id="prevGapok">Rp 0</span></div>
                                    <div class="calc-row calc-total"><span>Total Gaji Dasar (Bulanan)</span><span
                                            id="prevBulanan">Rp 0</span></div>
                                </div>

                                <div class="section-divider mb-2"><i class="fa-solid fa-calculator"></i>Perhitungan PPH 21
                                    (Tarif Progresif UU HPP)</div>
                                <div class="calculation-preview mb-3">
                                    <div class="calc-row mb-1"><span class="text-muted">Gaji Dasar (Tahunan)</span><span
                                            class="fw-bold" id="prevTahunan">Rp 0</span></div>
                                    <div class="calc-row mb-1"><span class="text-muted">PTKP (<span
                                                id="prevPtkpCode">-</span>)</span><span class="fw-bold"
                                            style="color:var(--success)" id="prevPtkpValue">Rp 0</span></div>
                                    <div class="calc-row mb-1"><span class="text-muted">Penghasilan Kena Pajak
                                            (PKP)</span><span class="fw-bold" id="prevPkp">Rp 0</span></div>
                                    <div class="calc-row mb-1"><span class="text-muted">PPH 21 Terutang
                                            (Tahunan)</span><span class="fw-bold" id="prevPphTahunan">Rp 0</span></div>
                                    <div class="calc-row"><span class="text-muted">PPH 21 Hitung dari PTKP (per
                                            Bulan)</span><span class="fw-bold" id="prevPphBulanan">Rp 0</span></div>
                                </div>

                                <div class="calculation-preview">
                                    <div class="calc-row calc-total" style="border-top:none;margin-top:0;padding-top:0">
                                        <span>PPH 21 yang Disimpan <small class="text-muted fw-normal"
                                                id="prevDipakaiSumber"></small></span><span id="prevDipakai">Rp 0</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button class="btn btn-outline-sec" id="wBtnPrev" style="display:none" onclick="wzPrev()"><i
                            class="fa-solid fa-arrow-left me-1"></i>Sebelumnya</button>
                    <button class="btn btn-pri" id="wBtnNext" onclick="wzNext()">Lanjut <i
                            class="fa-solid fa-arrow-right ms-1"></i></button>
                    <button class="btn btn-success" id="wBtnSave" style="display:none" onclick="simpanData()"><i
                            class="fa-solid fa-save me-1"></i>Simpan PPH 21</button>
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
                    <button type="button" class="btn btn-pri" id="btnEditFromDetail"><i
                            class="fa-solid fa-pen me-1"></i>Edit</button>
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
                    <button type="button" class="btn btn-pri" id="btnSubmitUpload" onclick="submitUpload()"><i
                            class="fa-solid fa-upload me-1"></i>Simpan</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>

    <script>
        const ptkp2026 = [{
                id: 'TK/0',
                nominalPerBulan: 4500000
            }, {
                id: 'TK/1',
                nominalPerBulan: 4875000
            },
            {
                id: 'TK/2',
                nominalPerBulan: 5250000
            }, {
                id: 'TK/3',
                nominalPerBulan: 5625000
            },
            {
                id: 'K/0',
                nominalPerBulan: 4875000
            }, {
                id: 'K/1',
                nominalPerBulan: 5250000
            },
            {
                id: 'K/2',
                nominalPerBulan: 5625000
            }, {
                id: 'K/3',
                nominalPerBulan: 6000000
            },
        ];

        const BULAN = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September',
            'Oktober', 'November', 'Desember'
        ];

        let allKaryawans = [];
        let laporan = null;
        let terkunci = false;
        let dtTable = null;
        let wzStep = 1;
        let formMode = 'create';
        let uploadJenis = null;

        const URL_DATA = '{{ url('HR-dashboard/perhitungan-pph/data') }}';
        const URL_STORE = '{{ url('HR-dashboard/perhitungan-pph/store') }}';
        const URL_ISI = '{{ url('HR-dashboard/perhitungan-pph/isi-otomatis') }}';
        const URL_KORTEX = '{{ url('HR-dashboard/perhitungan-pph/lapor-kortex') }}';
        const URL_BATAL_KORTEX = '{{ url('HR-dashboard/perhitungan-pph/batal-kortex') }}';
        const URL_BUKTI = '{{ url('HR-dashboard/perhitungan-pph/upload-bukti-bayar') }}';
        const URL_KODE = '{{ url('HR-dashboard/perhitungan-pph/upload-kode-billing') }}';
        const URL_DELETE_ALL = "{{ route('HR.perhitungan-pph.delete-all') }}";

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

        function initPeriode() {
            const now = new Date();
            let optBulan = '';
            BULAN.forEach((b, i) => optBulan += `<option value="${i + 1}">${b}</option>`);
            $('#filterBulan').html(optBulan).val(now.getMonth() + 1);

            let optTahun = '';
            for (let y = now.getFullYear() - 3; y <= now.getFullYear() + 1; y++) optTahun +=
                `<option value="${y}">${y}</option>`;
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
                        render: d => fmtRp(getGajiDasar(d)),
                        className: 'text-end'
                    },
                    {
                        data: null,
                        className: 'text-center',
                        render: d => d.pph21 ?
                            `<span class="status-badge badge-ptkp">${esc(d.pph21.ptkp)}</span>` :
                            `<span class="text-muted small">Belum diset</span>`
                    },
                    {
                        data: null,
                        className: 'text-center',
                        render: d => d.pph21 ? (d.pph21.menikah ?
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
                            return `<strong>${fmtRp(p.nilai)}</strong><br><small class="text-muted">${sumberLabel(p)}</small>`;
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
                        orderable: false,
                        className: 'text-center',
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

        function badgeStatus(ok) {
            return ok ? `<span class="status-badge badge-menikah">Sudah</span>` :
                `<span class="status-badge badge-belum">Belum</span>`;
        }

        function sumberLabel(p) {
            if (p.sumber === 'tersimpan') return 'Tersimpan bulan ini';
            if (p.sumber === 'tunjangan') return 'Dari tunjangan (belum disimpan)';
            if (p.sumber === 'bulan_lalu') return `Dari bulan ${esc(p.dari)} (belum disimpan)`;
            return 'Hitung dari PTKP (belum disimpan)';
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
                }
            }).fail(function() {
                alert('Gagal memuat data');
            });
        }

        function applyLockState() {
            $('#lockNotice').toggleClass('d-none', !terkunci);
            $('#btnTambahPph, #btnIsiOtomatis').prop('disabled', terkunci);
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

            renderBerkas('#cardBukti', 'Bukti Bayar', 'fa-receipt', 'linear-gradient(135deg,#059669,#10b981)', l
                .bukti_bayar, 'bukti_bayar');
            renderBerkas('#cardBilling', 'Kode Billing', 'fa-barcode', 'linear-gradient(135deg,#0284c7,#38bdf8)', l
                .kode_billing, 'kode_billing');
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
            return parseInt(k.gaji) || 0;
        }

        function fmtRp(n) {
            return 'Rp ' + Math.round(n || 0).toLocaleString('id-ID');
        }

        function teksAwal(k) {
            if (!k || k.pph_tunjangan === null || k.pph_tunjangan === undefined)
                return 'Belum ada data PPH 21 di Tunjangan untuk periode ini. Nilai akan dihitung dari status PTKP.';
            return `PPH 21 dari data Tunjangan periode ini: <strong>${fmtRp(k.pph_tunjangan)}</strong>`;
        }

        function getKaryawan(id) {
            return allKaryawans.find(k => k.id == id);
        }

        function getPphByKaryawanId(karyawanId) {
            const k = getKaryawan(karyawanId);
            return k ? k.pph21 : null;
        }

        function hitungPph21(gajiBulanan, ptkpCode) {
            if (!gajiBulanan || !ptkpCode) return {
                bulanan: 0,
                tahunan: 0,
                pkp: 0,
                ptkp: 0,
                gajiTahunan: 0
            };
            const gajiTahunan = gajiBulanan * 12;
            const ptkpObj = ptkp2026.find(p => p.id === ptkpCode);
            const ptkp = ptkpObj ? ptkpObj.nominalPerBulan * 12 : 0;
            const pkp = Math.max(0, gajiTahunan - ptkp);
            let pphTahunan = 0;
            const layers = [{
                    limit: 60000000,
                    rate: 0.05
                }, {
                    limit: 250000000,
                    rate: 0.15
                },
                {
                    limit: 500000000,
                    rate: 0.25
                }, {
                    limit: 5000000000,
                    rate: 0.30
                },
                {
                    limit: Infinity,
                    rate: 0.35
                }
            ];
            let remaining = pkp,
                prevLimit = 0;
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

        function renderTable() {
            const filterDivisi = $('#filterDivisi').val();
            const filterPtkp = $('#filterPtkp').val();
            const filterSearch = $('#filterSearch').val().toLowerCase();

            const filtered = allKaryawans.filter(k => {
                if (filterDivisi && k.divisi !== filterDivisi) return false;
                if (filterSearch && !k.nama.toLowerCase().includes(filterSearch) && !String(k.nip).includes(
                        filterSearch)) return false;
                if (filterPtkp && (!k.pph21 || k.pph21.ptkp !== filterPtkp)) return false;
                return true;
            });

            dtTable.clear().rows.add(filtered).draw();

            let totalSudah = 0,
                totalPph = 0;
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

        function updatePreview() {
            const kId = $('#inputKaryawan').val();
            const ptkpCode = $('#inputPtkp').val();
            if (!kId) return;

            const k = getKaryawan(kId);
            const gajiDasar = getGajiDasar(k);
            const calc = hitungPph21(gajiDasar, ptkpCode);
            const dariTunjangan = k.pph_tunjangan !== null && k.pph_tunjangan !== undefined;

            $('#prevNama').text(`${k.nama} (${k.nip})`);
            $('#prevPtkpBadge').text(ptkpCode || '-');
            $('#prevGapok').text(fmtRp(k.gaji || 0));
            $('#prevBulanan').text(fmtRp(gajiDasar));
            $('#prevTahunan').text(fmtRp(calc.gajiTahunan));
            $('#prevPtkpCode').text(ptkpCode || '-');
            $('#prevPtkpValue').text(fmtRp(calc.ptkp));
            $('#prevPkp').text(fmtRp(calc.pkp));
            $('#prevPphTahunan').text(fmtRp(calc.tahunan));
            $('#prevPphBulanan').text(fmtRp(calc.bulanan));
            $('#prevDipakai').text(fmtRp(dariTunjangan ? k.pph_tunjangan : calc.bulanan));
            $('#prevDipakaiSumber').text(dariTunjangan ? '(dari data Tunjangan)' : '(hitung dari PTKP)');

            if (ptkpCode) {
                const p = ptkp2026.find(x => x.id === ptkpCode);
                $('#ptkpValueInfo').html(
                    `PTKP ${ptkpCode} = <strong>${fmtRp(p.nominalPerBulan)}/bulan</strong> (${fmtRp(p.nominalPerBulan * 12)}/tahun)`
                );
            } else {
                $('#ptkpValueInfo').text('PTKP Tahunan: -');
            }
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
            if (wzStep === 1) {
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
                        alert(res.message);
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
            if (terkunci) return;
            if (!confirm(
                    `Isi otomatis PPH 21 periode ${periodeLabel()} untuk semua karyawan yang belum tersimpan, memakai data bulan sebelumnya?`
                )) return;

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
                )) return;

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
            const gajiDasar = getGajiDasar(k);
            const calc = hitungPph21(gajiDasar, pph.ptkp);

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
            if (pph.anak.length > 3) anakHtml +=
                `<div class="tanggungan-note mt-2"><i class="fa-solid fa-triangle-exclamation me-1"></i>${pph.anak.length} anak tercatat, hanya 3 anak pertama yang menjadi tanggungan PTKP</div>`;

            const p = k.pph_bulan;
            const pphBulanan = p ? p.nilai : calc.bulanan;
            const sumber = p ? sumberLabel(p) : 'Hitung dari PTKP';

            const html = `
                <div class="ptkp-info-box mb-3"><i class="fa-solid fa-calendar-days me-1"></i>Periode: <strong>${esc(periodeLabel())}</strong></div>
                <div class="row g-2 mb-3">
                    <div class="col-md-6"><div class="info-card"><div class="label">NIP</div><div class="value">${esc(k.nip)}</div></div></div>
                    <div class="col-md-6"><div class="info-card"><div class="label">Nama</div><div class="value">${esc(k.nama)}</div></div></div>
                    <div class="col-md-6"><div class="info-card"><div class="label">Jabatan</div><div class="value">${esc(k.jabatan)}</div></div></div>
                    <div class="col-md-6"><div class="info-card"><div class="label">Gaji Dasar</div><div class="value">${fmtRp(gajiDasar)}</div></div></div>
                </div>
                <div class="section-divider mb-2"><i class="fa-solid fa-id-card"></i>Status PTKP</div>
                <div class="row g-2 mb-3">
                    <div class="col-md-4"><div class="info-card"><div class="label">Status PTKP</div><div class="value"><span class="status-badge badge-ptkp">${esc(pph.ptkp)}</span></div></div></div>
                    <div class="col-md-4"><div class="info-card"><div class="label">Status Menikah</div><div class="value">${pph.menikah ? '<span class="status-badge badge-menikah">Menikah</span>' : '<span class="status-badge badge-belum">Belum</span>'}</div></div></div>
                    <div class="col-md-4"><div class="info-card"><div class="label">Jumlah Anak</div><div class="value">${pph.anak.length} Anak</div></div></div>
                </div>
                ${pph.menikah ? `<div class="section-divider mb-2"><i class="fa-solid fa-children"></i>Daftar Anak</div>${anakHtml}` : ''}
                <div class="section-divider mb-2 mt-3"><i class="fa-solid fa-calculator"></i>Perhitungan PPH 21</div>
                <div class="calculation-preview">
                    <div class="calc-row mb-1"><span class="text-muted">Gaji Dasar (Bulanan)</span><span class="fw-bold">${fmtRp(gajiDasar)}</span></div>
                    <div class="calc-row mb-1"><span class="text-muted">Gaji Dasar (Tahunan)</span><span class="fw-bold">${fmtRp(calc.gajiTahunan)}</span></div>
                    <div class="calc-row mb-1"><span class="text-muted">PTKP (${esc(pph.ptkp)})</span><span class="fw-bold" style="color:var(--success)">${fmtRp(calc.ptkp)}</span></div>
                    <div class="calc-row mb-1"><span class="text-muted">Penghasilan Kena Pajak</span><span class="fw-bold">${fmtRp(calc.pkp)}</span></div>
                    <div class="calc-row mb-1"><span class="text-muted">PPH 21 Terutang (Tahunan)</span><span class="fw-bold">${fmtRp(calc.tahunan)}</span></div>
                    <div class="calc-row calc-total"><span>PPH 21 per Bulan <small class="text-muted fw-normal">(${sumber})</small></span><span>${fmtRp(pphBulanan)}</span></div>
                </div>`;
            $('#detailContent').html(html);
            $('#btnEditFromDetail').prop('disabled', terkunci).off('click').on('click', function() {
                bootstrap.Modal.getInstance(document.getElementById('modalDetail')).hide();
                setTimeout(() => openModal('edit', karyawanId), 300);
            });
            new bootstrap.Modal(document.getElementById('modalDetail')).show();
        }

        function confirmDeleteAllPph() {
            if (!confirm('Apakah Anda yakin ingin menghapus SEMUA data PPH 21 pada periode ' + periodeLabel() + '?')) {
                return;
            }

            if (!confirm('PERINGATAN!\n\nData yang dihapus TIDAK DAPAT DIKEMBALIKAN LAGI.\n\nLanjutkan menghapus semua data PPH 21 periode ini?')) {
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
    </script>
@endsection
