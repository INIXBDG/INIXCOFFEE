@extends('layout_HR.app')
@section('content_HR')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
        :root {
            --pri: #4f46e5; --pri-light: #eef2ff; --pri-dark: #3730a3;
            --success: #059669; --success-light: #d1fae5;
            --warning: #d97706; --warning-light: #fef3c7;
            --info: #0284c7; --info-light: #e0f2fe;
            --danger: #dc2626; --danger-light: #fee2e2;
            --gray-50: #f9fafb; --gray-100: #f3f4f6; --gray-200: #e5e7eb;
            --gray-400: #9ca3af; --gray-600: #4b5563; --gray-700: #374151; --gray-900: #111827;
            --radius: 10px;
            --shadow: 0 4px 6px rgba(0, 0, 0, .07), 0 2px 4px rgba(0, 0, 0, .05);
            --shadow-lg: 0 10px 25px rgba(0, 0, 0, .1);
        }
        body { background: #fafbfc; }
        .card-shell { border: none; border-radius: var(--radius); box-shadow: var(--shadow); background: #fff; }
        .stat-card { border: none; border-radius: var(--radius); box-shadow: var(--shadow); background: #fff; transition: transform .25s, box-shadow .25s; }
        .stat-card:hover { transform: translateY(-3px); box-shadow: var(--shadow-lg); }
        .stat-icon { width: 48px; height: 48px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.3rem; color: #fff; }
        .btn-pri { background: var(--pri); border: none; color: #fff; font-weight: 600; padding: .5rem 1.25rem; border-radius: 8px; transition: all .25s; }
        .btn-pri:hover { background: var(--pri-dark); transform: translateY(-1px); box-shadow: 0 4px 12px rgba(79, 70, 229, .35); color: #fff; }
        .btn-outline-sec { background: #fff; border: 1px solid var(--gray-200); color: var(--gray-600); font-weight: 500; border-radius: 8px; transition: all .2s; }
        .btn-outline-sec:hover { background: var(--gray-50); border-color: var(--gray-400); color: var(--gray-900); }
        
        /* DataTables Custom Style */
        .dataTables_wrapper { padding: 0 !important; }
        .dataTables_wrapper .dataTables_info { padding: 1rem 1.5rem !important; font-size: .82rem; color: var(--gray-400); margin: 0 !important; border-top: 1px solid var(--gray-100); }
        .dataTables_wrapper .dataTables_paginate { padding: 1rem 1.5rem !important; margin: 0 !important; border-top: 1px solid var(--gray-100); }
        .dataTables_wrapper .dataTables_paginate .paginate_button { padding: 0 !important; margin: 0 2px !important; border-radius: 6px !important; transition: all .2s !important; }
        .dataTables_wrapper .dataTables_paginate .paginate_button.current, .dataTables_wrapper .dataTables_paginate .paginate_button:hover { background: var(--pri) !important; color: #fff !important; border: 1px solid var(--pri) !important; font-weight: 600; }
        .dataTables_wrapper .dataTables_paginate .paginate_button.disabled { color: var(--gray-400) !important; background: transparent !important; border: 1px solid var(--gray-200) !important; opacity: .6; }
        .dataTables_wrapper .dataTables_paginate .paginate_button:not(.current):not(.disabled) { background: #fff !important; color: var(--gray-600) !important; border: 1px solid var(--gray-200) !important; }
        .dataTables_wrapper .dataTables_paginate .paginate_button:not(.current):not(.disabled):hover { background: var(--pri-light) !important; color: var(--pri) !important; border-color: var(--pri) !important; }
        #pphTable { border-collapse: separate; border-spacing: 0; width: 100%; }
        #pphTable thead th { font-size: .72rem; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; color: var(--gray-600); background: var(--gray-50); border-bottom: 2px solid var(--gray-200) !important; border-top: none !important; padding: 0.85rem 1rem; }
        #pphTable tbody tr { transition: background .15s; }
        #pphTable tbody tr:hover { background: var(--pri-light) !important; }
        #pphTable tbody td { vertical-align: middle; font-size: .875rem; border-bottom: 1px solid var(--gray-100) !important; border-top: none !important; padding: 0.85rem 1rem; color: var(--gray-700); }

        .info-card { background: var(--gray-50); border: 1px solid var(--gray-200); border-radius: 8px; padding: .85rem 1rem; }
        .info-card .label { font-size: .7rem; text-transform: uppercase; letter-spacing: .5px; color: var(--gray-400); font-weight: 700; }
        .info-card .value { font-weight: 600; color: var(--gray-900); }
        .status-badge { padding: .35rem .75rem; border-radius: 20px; font-size: .7rem; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; }
        .badge-ptkp { background: var(--pri-light); color: var(--pri); }
        .badge-menikah { background: var(--success-light); color: var(--success); }
        .badge-belum { background: var(--warning-light); color: var(--warning); }
        .child-item { background: var(--gray-50); border: 1px solid var(--gray-200); border-radius: 8px; padding: .75rem 1rem; display: flex; align-items: center; gap: .75rem; }
        .child-number { width: 28px; height: 28px; border-radius: 50%; background: var(--pri-light); color: var(--pri); display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: .8rem; flex-shrink: 0; }
        .child-tanggungan { font-size: .7rem; padding: .2rem .5rem; border-radius: 10px; background: var(--success-light); color: var(--success); font-weight: 600; }
        .ptkp-info-box { background: var(--info-light); border: 1px solid #bae6fd; border-radius: 8px; padding: .75rem 1rem; font-size: .82rem; }
        .calculation-preview { background: linear-gradient(135deg, rgba(79, 70, 229, .06) 0%, rgba(99, 102, 241, .04) 100%); border: 1.5px solid rgba(79, 70, 229, .25); border-radius: var(--radius); padding: 1.25rem; }
        .calc-row { display: flex; justify-content: space-between; }
        .calc-total { font-size: 1.1rem; font-weight: 700; color: var(--pri); border-top: 2px solid var(--gray-200); padding-top: .6rem; margin-top: .4rem; }
        .modal-header-custom { background: linear-gradient(135deg, var(--pri) 0%, var(--pri-dark) 100%); color: #fff; border-radius: 12px 12px 0 0; padding: 1.1rem 1.5rem; }
        .modal-header-custom .modal-title { font-weight: 700; }
        .modal-header-custom .btn-close { filter: brightness(0) invert(1); }
        .section-divider { font-size: .8rem; font-weight: 700; color: var(--gray-700); text-transform: uppercase; letter-spacing: .5px; display: flex; align-items: center; gap: .5rem; }
        .section-divider i { color: var(--pri); }
        .tanggungan-note { background: var(--warning-light); border: 1px solid #fcd34d; border-radius: 8px; padding: .6rem .9rem; font-size: .8rem; color: var(--warning); }

        /* Wizard Style */
        .wizard-bar { display: flex; justify-content: space-between; margin-bottom: 2rem; position: relative; }
        .wizard-bar::before { content: ''; position: absolute; top: 18px; left: 15%; right: 15%; height: 2px; background: var(--gray-200); z-index: 0; }
        .wz-step { position: relative; z-index: 1; text-align: center; flex: 1; }
        .wz-circle { width: 36px; height: 36px; border-radius: 50%; background: #fff; border: 2px solid var(--gray-200); display: flex; align-items: center; justify-content: center; margin: 0 auto .4rem; font-weight: 700; font-size: .8rem; color: var(--gray-400); transition: all .3s; }
        .wz-step.active .wz-circle { background: var(--pri); border-color: var(--pri); color: #fff; box-shadow: 0 0 0 4px rgba(79, 70, 229, .2); }
        .wz-step.done .wz-circle { background: var(--success); border-color: var(--success); color: #fff; }
        .wz-label { font-size: .75rem; color: var(--gray-400); font-weight: 600; }
        .wz-step.active .wz-label { color: var(--pri); }
    </style>

    <div class="container-fluid px-4 py-4">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <div>
                <h1 class="h4 fw-bold mb-1">PPH 21 Karyawan</h1>
                <p class="text-muted small mb-0">Kelola perhitungan pajak penghasilan Pasal 21 karyawan (Tarif Terbaru 2026)</p>
            </div>
            <div class="d-flex gap-2 mt-2 mt-sm-0">
                <button class="btn btn-outline-sec btn-sm"><i class="fa-solid fa-file-excel me-1"></i>Export Excel</button>
                <button class="btn btn-pri btn-sm" onclick="openModal('create')"><i class="fa-solid fa-plus me-1"></i>Tambah PPH 21</button>
            </div>
        </div>

        <div class="card card-shell mb-4">
            <div class="card-body py-3">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <span class="fw-bold small" style="color:var(--pri)">Filter Data</span>
                    <div class="d-flex gap-2 flex-wrap">
                        <select class="form-select form-select-sm" style="width:140px" id="filterDivisi" onchange="renderTable()">
                            <option value="">Semua Divisi</option>
                        </select>
                        <select class="form-select form-select-sm" style="width:140px" id="filterPtkp" onchange="renderTable()">
                            <option value="">Semua Status PTKP</option>
                            <option>TK/0</option><option>TK/1</option><option>TK/2</option><option>TK/3</option>
                            <option>K/0</option><option>K/1</option><option>K/2</option><option>K/3</option>
                        </select>
                        <div class="input-group input-group-sm" style="width:220px">
                            <span class="input-group-text bg-white border-end-0"><i class="fa-solid fa-search small text-muted"></i></span>
                            <input type="text" class="form-control border-start-0" placeholder="Cari NIP atau nama..." id="filterSearch" onkeyup="renderTable()">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="card stat-card">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div><p class="text-muted small text-uppercase fw-bold mb-1" style="font-size:.75rem">Total Karyawan</p><h3 class="fw-bold mb-0" id="statTotal">0</h3></div>
                        <div class="stat-icon" style="background:linear-gradient(135deg,#4f46e5,#7c3aed)"><i class="fa-solid fa-users"></i></div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card stat-card">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div><p class="text-muted small text-uppercase fw-bold mb-1" style="font-size:.75rem">Sudah Ada PTKP</p><h3 class="fw-bold mb-0" style="color:var(--success)" id="statSudah">0</h3></div>
                        <div class="stat-icon" style="background:linear-gradient(135deg,#059669,#10b981)"><i class="fa-solid fa-check"></i></div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card stat-card">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div><p class="text-muted small text-uppercase fw-bold mb-1" style="font-size:.75rem">Belum Ada PTKP</p><h3 class="fw-bold mb-0" style="color:var(--warning)" id="statBelum">0</h3></div>
                        <div class="stat-icon" style="background:linear-gradient(135deg,#d97706,#f59e0b)"><i class="fa-solid fa-exclamation"></i></div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card stat-card">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div><p class="text-muted small text-uppercase fw-bold mb-1" style="font-size:.75rem">Total PPH 21/Bulan</p><h3 class="fw-bold mb-0" id="statTotalPph">Rp 0</h3></div>
                        <div class="stat-icon" style="background:linear-gradient(135deg,#0284c7,#38bdf8)"><i class="fa-solid fa-calculator"></i></div>
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
                                <th>NIP</th><th>Nama</th><th>Divisi</th><th class="text-end">Gaji Dasar</th>
                                <th class="text-center">Status PTKP</th><th class="text-center">Menikah</th>
                                <th class="text-center">Jml Anak</th><th class="text-end">PPH 21/Bulan</th><th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Setup PPH 21 (Wizard) -->
    <div class="modal fade" id="modalPph21" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content" style="border-radius:12px;border:none">
                <div class="modal-header modal-header-custom border-0">
                    <h5 class="modal-title" id="modalPph21Title">Setup PPH 21 Karyawan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body px-4 pt-4">
                    <div class="wizard-bar mb-4">
                        <div class="wz-step active" data-step="1"><div class="wz-circle">1</div><div class="wz-label">Data PTKP</div></div>
                        <div class="wz-step" data-step="2"><div class="wz-circle">2</div><div class="wz-label">Preview & Simpan</div></div>
                    </div>

                    <!-- STEP 1 -->
                    <div class="wz-content" id="wz1">
                        <div class="section-divider mb-3"><i class="fa-solid fa-user"></i>Data Karyawan</div>
                        <div class="row g-2 mb-3">
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Pilih Karyawan <span class="text-danger">*</span></label>
                                <select class="form-select form-select-sm" id="inputKaryawan" onchange="onKaryawanChange()">
                                    <option value="">— Pilih Karyawan —</option>
                                </select>
                            </div>
                            <div class="col-md-4"><div class="info-card h-100"><div class="label">NIP</div><div class="value" id="infoNIP">-</div></div></div>
                            <div class="col-md-4"><div class="info-card h-100"><div class="label">Gaji Dasar</div><div class="value" id="infoGaji">-</div></div></div>
                        </div>
                        <hr class="my-3">
                        <div class="section-divider mb-3"><i class="fa-solid fa-id-card"></i>Status PTKP</div>
                        <div class="ptkp-info-box mb-3"><i class="fa-solid fa-info-circle me-1"></i><strong>PTKP</strong> adalah jumlah penghasilan yang tidak dikenakan pajak. Pilih sesuai status perkawinan dan tanggungan.</div>
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
                            <label class="form-label small fw-bold">Status Menikah <span class="text-danger">*</span></label>
                            <div class="d-flex gap-3">
                                <div class="form-check"><input class="form-check-input" type="radio" name="statusMenikah" id="menikahYes" value="1" onchange="onStatusMenikahChange()"><label class="form-check-label" for="menikahYes">Menikah</label></div>
                                <div class="form-check"><input class="form-check-input" type="radio" name="statusMenikah" id="menikahNo" value="0" onchange="onStatusMenikahChange()"><label class="form-check-label" for="menikahNo">Belum Menikah</label></div>
                            </div>
                        </div>
                        <div id="formAnak" class="mb-3 d-none">
                            <label class="form-label small fw-bold">Jumlah Anak</label>
                            <div class="row g-2 align-items-end mb-3">
                                <div class="col-md-4">
                                    <select class="form-select form-select-sm" id="jumlahAnak" onchange="updateAnakList()">
                                        <option value="0">0 Anak</option><option value="1">1 Anak</option><option value="2">2 Anak</option>
                                        <option value="3">3 Anak</option><option value="4">4 Anak</option><option value="5">5 Anak</option>
                                    </select>
                                </div>
                                <div class="col-md-8"><small class="text-muted"><i class="fa-solid fa-circle-info me-1"></i>Hanya <strong>3 anak pertama</strong> yang menjadi tanggungan PTKP</small></div>
                            </div>
                            <div class="section-divider mb-2 mt-3"><i class="fa-solid fa-children"></i>Data Anak</div>
                            <div id="daftarAnak"></div>
                            <div id="tanggunganNote" class="tanggungan-note mt-2 d-none"><i class="fa-solid fa-triangle-exclamation me-1"></i><span id="tanggunganNoteText"></span></div>
                        </div>
                        <div id="formAnakHidden" class="mb-3 d-none">
                            <div class="ptkp-info-box" style="background:var(--gray-50);border-color:var(--gray-200)"><i class="fa-solid fa-info-circle me-1 text-muted"></i><span class="text-muted">Form data anak tidak diperlukan untuk status belum menikah.</span></div>
                        </div>
                    </div>

                    <!-- STEP 2 -->
                    <div class="wz-content d-none" id="wz2">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <h6 class="fw-bold mb-3"><i class="fa-solid fa-calculator me-2 text-primary"></i>Preview Perhitungan PPH 21</h6>
                                <div class="row g-3 mb-3">
                                    <div class="col-md-6"><div class="info-card"><div class="label">Karyawan</div><div class="value" id="prevNama">-</div></div></div>
                                    <div class="col-md-6"><div class="info-card"><div class="label">Status PTKP</div><div class="value"><span class="status-badge badge-ptkp" id="prevPtkpBadge">-</span></div></div></div>
                                </div>
                                
                                <div class="section-divider mb-2"><i class="fa-solid fa-money-bill-wave"></i>Komponen Gaji (Dari Database)</div>
                                <div class="calculation-preview mb-3">
                                    <div class="calc-row mb-1"><span class="text-muted">Gaji Pokok</span><span class="fw-bold" id="prevGapok">Rp 0</span></div>
                                    <div class="calc-row calc-total"><span>Total Gaji Dasar (Bulanan)</span><span id="prevBulanan">Rp 0</span></div>
                                </div>

                                <div class="section-divider mb-2"><i class="fa-solid fa-calculator"></i>Perhitungan PPH 21 (Tarif Progresif UU HPP)</div>
                                <div class="calculation-preview">
                                    <div class="calc-row mb-1"><span class="text-muted">Gaji Dasar (Tahunan)</span><span class="fw-bold" id="prevTahunan">Rp 0</span></div>
                                    <div class="calc-row mb-1"><span class="text-muted">PTKP (<span id="prevPtkpCode">-</span>)</span><span class="fw-bold" style="color:var(--success)" id="prevPtkpValue">Rp 0</span></div>
                                    <div class="calc-row mb-1"><span class="text-muted">Penghasilan Kena Pajak (PKP)</span><span class="fw-bold" id="prevPkp">Rp 0</span></div>
                                    <div class="calc-row mb-1"><span class="text-muted">PPH 21 Terutang (Tahunan)</span><span class="fw-bold" id="prevPphTahunan">Rp 0</span></div>
                                    <div class="calc-row calc-total"><span>PPH 21 per Bulan</span><span id="prevPphBulanan">Rp 0</span></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button class="btn btn-outline-sec" id="wBtnPrev" style="display:none" onclick="wzPrev()"><i class="fa-solid fa-arrow-left me-1"></i>Sebelumnya</button>
                    <button class="btn btn-pri" id="wBtnNext" onclick="wzNext()">Lanjut <i class="fa-solid fa-arrow-right ms-1"></i></button>
                    <button class="btn btn-success" id="wBtnSave" style="display:none" onclick="simpanData()"><i class="fa-solid fa-save me-1"></i>Simpan PPH 21</button>
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
                    <button type="button" class="btn btn-pri" id="btnEditFromDetail"><i class="fa-solid fa-pen me-1"></i>Edit</button>
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
        // ===== PTKP VALUES 2026 (Per Bulan) =====
        const ptkp2026 = [
            { id: 'TK/0', status: 'Tidak Kawin', tanggungan: 0, nominalPerBulan: 4500000, keterangan: 'Tidak Kawin, Tanpa Tanggungan' },
            { id: 'TK/1', status: 'Tidak Kawin', tanggungan: 1, nominalPerBulan: 4875000, keterangan: 'Tidak Kawin, 1 Tanggungan' },
            { id: 'TK/2', status: 'Tidak Kawin', tanggungan: 2, nominalPerBulan: 5250000, keterangan: 'Tidak Kawin, 2 Tanggungan' },
            { id: 'TK/3', status: 'Tidak Kawin', tanggungan: 3, nominalPerBulan: 5625000, keterangan: 'Tidak Kawin, 3 Tanggungan' },
            { id: 'K/0', status: 'Kawin', tanggungan: 0, nominalPerBulan: 4875000, keterangan: 'Kawin, Tanpa Tanggungan' },
            { id: 'K/1', status: 'Kawin', tanggungan: 1, nominalPerBulan: 5250000, keterangan: 'Kawin, 1 Tanggungan' },
            { id: 'K/2', status: 'Kawin', tanggungan: 2, nominalPerBulan: 5625000, keterangan: 'Kawin, 2 Tanggungan' },
            { id: 'K/3', status: 'Kawin', tanggungan: 3, nominalPerBulan: 6000000, keterangan: 'Kawin, 3 Tanggungan' },
        ];

        let allKaryawans = [];
        let dtTable = null;
        let wzStep = 1;
        
        const URL_DATA = '{{ url('HR-dashboard/perhitungan-pph/data') }}';
        const URL_STORE = '{{ url('HR-dashboard/perhitungan-pph/store') }}';
        const URL_BASE = '{{ url('HR-dashboard/perhitungan-pph') }}';

        $(document).ready(function() {
            $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });
            initDataTable();
            loadData();
        });

        function initDataTable() {
            dtTable = $('#pphTable').DataTable({
                data: [],
                columns: [
                    { data: 'nip' },
                    { data: null, render: d => `<strong>${d.nama}</strong><br><small class="text-muted">${d.jabatan}</small>` },
                    { data: 'divisi' },
                    { data: null, render: d => fmtRp(getGajiDasar(d)), className: 'text-end' },
                    { data: null, render: d => d.pph21 ? `<span class="status-badge badge-ptkp">${d.pph21.ptkp}</span>` : `<span class="text-muted small">Belum diset</span>`, className: 'text-center' },
                    { data: null, render: d => d.pph21 ? (d.pph21.menikah ? `<span class="status-badge badge-menikah">Menikah</span>` : `<span class="status-badge badge-belum">Belum</span>`) : `<span class="text-muted">-</span>`, className: 'text-center' },
                    { data: null, render: d => d.pph21 ? d.pph21.anak.length : 0, className: 'text-center' },
                    { data: null, render: d => {
                        if(!d.pph21) return `<span class="text-muted">Belum dihitung</span>`;
                        const calc = hitungPph21(getGajiDasar(d), d.pph21.ptkp);
                        return `<strong>${fmtRp(calc.bulanan)}</strong>`;
                    }, className: 'text-end' },
                    { data: null, orderable: false, render: d => {
                        if(d.pph21) {
                            return `<div class="d-flex gap-1 justify-content-center">
                                <button class="btn btn-sm btn-outline-sec" onclick="openDetail(${d.id})" title="Detail"><i class="fa-solid fa-eye"></i></button>
                                <button class="btn btn-sm btn-pri" onclick="openModal('edit', ${d.id})" title="Edit"><i class="fa-solid fa-pen"></i></button>
                                <button class="btn btn-sm btn-outline-danger" onclick="hapusPph(${d.pph21.id})" title="Hapus"><i class="fa-solid fa-trash"></i></button>
                            </div>`;
                        } else {
                            return `<div class="d-flex gap-1 justify-content-center"><button class="btn btn-sm btn-pri" onclick="openModal('create', ${d.id})" title="Setup"><i class="fa-solid fa-plus"></i></button></div>`;
                        }
                    }, className: 'text-center' }
                ],
                language: {
                    search: '', searchPlaceholder: 'Cari di tabel...', lengthMenu: 'Tampilkan _MENU_ data',
                    info: 'Menampilkan _START_–_END_ dari _TOTAL_ karyawan', infoEmpty: 'Tidak ada data',
                    paginate: { previous: '‹', next: '›' },
                    zeroRecords: '<div class="text-center py-4 text-muted"><i class="fa-solid fa-filter-circle-xmark fa-2x mb-2 d-block"></i>Tidak ada data sesuai filter</div>',
                    loadingRecords: '<div class="text-center py-4"><i class="fa-solid fa-spinner fa-spin me-2"></i>Memuat...</div>'
                },
                responsive: true,
                pageLength: 15,
                order: [[1, 'asc']],
                dom: 'rt<"d-flex justify-content-between align-items-center mt-3"ip>',
            });
        }

        function loadData() {
            $.get(URL_DATA, function(res) {
                if (res.success) {
                    allKaryawans = res.data;
                    populateFilterDivisi();
                    renderTable();
                }
            }).fail(function() { alert('Gagal memuat data'); });
        }

        function populateFilterDivisi() {
            const divisis = [...new Set(allKaryawans.map(k => k.divisi))].filter(d => d && d !== '-');
            let html = '<option value="">Semua Divisi</option>';
            divisis.forEach(d => html += `<option>${d}</option>`);
            $('#filterDivisi').html(html);
        }

        function getGajiDasar(k) {
            return parseInt(k.gaji) || 0;
        }

        function fmtRp(n) { return 'Rp ' + Math.round(n || 0).toLocaleString('id-ID'); }
        function getKaryawan(id) { return allKaryawans.find(k => k.id == id); }
        function getPphByKaryawanId(karyawanId) {
            const k = allKaryawans.find(x => x.id == karyawanId);
            return k ? k.pph21 : null;
        }

        function hitungPph21(gajiBulanan, ptkpCode) {
            if (!gajiBulanan || !ptkpCode) return { bulanan: 0, tahunan: 0, pkp: 0, ptkp: 0, gajiTahunan: 0 };
            const gajiTahunan = gajiBulanan * 12;
            const ptkpBulananObj = ptkp2026.find(p => p.id === ptkpCode);
            const ptkp = ptkpBulananObj ? ptkpBulananObj.nominalPerBulan * 12 : 0;
            let pkp = Math.max(0, gajiTahunan - ptkp);
            let pphTahunan = 0;
            const layers = [
                { limit: 60000000, rate: 0.05 },
                { limit: 250000000, rate: 0.15 },
                { limit: 500000000, rate: 0.25 },
                { limit: 5000000000, rate: 0.30 },
                { limit: Infinity, rate: 0.35 }
            ];
            let remaining = pkp, prevLimit = 0;
            for (const layer of layers) {
                if (remaining <= 0) break;
                const taxable = Math.min(remaining, layer.limit - prevLimit);
                pphTahunan += taxable * layer.rate;
                remaining -= taxable;
                prevLimit = layer.limit;
            }
            return { bulanan: Math.round(pphTahunan / 12), tahunan: Math.round(pphTahunan), pkp: Math.round(pkp), ptkp: ptkp, gajiTahunan: gajiTahunan };
        }

        function renderTable() {
            const filterDivisi = $('#filterDivisi').val();
            const filterPtkp = $('#filterPtkp').val();
            const filterSearch = $('#filterSearch').val().toLowerCase();

            let filtered = allKaryawans.filter(k => {
                if (filterDivisi && k.divisi !== filterDivisi) return false;
                if (filterSearch && !k.nama.toLowerCase().includes(filterSearch) && !k.nip.includes(filterSearch)) return false;
                if (filterPtkp) { if (!k.pph21 || k.pph21.ptkp !== filterPtkp) return false; }
                return true;
            });

            dtTable.clear().rows.add(filtered).draw();

            let totalSudah = 0, totalPph = 0;
            allKaryawans.forEach(k => {
                if(k.pph21) {
                    totalSudah++;
                    const calc = hitungPph21(getGajiDasar(k), k.pph21.ptkp);
                    totalPph += calc.bulanan;
                }
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
                select.append(`<option value="${k.id}" ${k.id == selectedId ? 'selected' : ''}>${label}</option>`);
            });
        }

        function onKaryawanChange() {
            const id = $('#inputKaryawan').val();
            if (!id) { $('#infoNIP').text('-'); $('#infoGaji').text('-'); return; }
            const k = getKaryawan(id);
            $('#infoNIP').text(k.nip);
            $('#infoGaji').text(fmtRp(getGajiDasar(k)));
            updatePreview();
        }

        function onStatusMenikahChange() {
            const menikah = $('input[name="statusMenikah"]:checked').val();
            if (menikah === '1') {
                $('#formAnak').removeClass('d-none'); $('#formAnakHidden').addClass('d-none');
                if ($('#daftarAnak').children().length === 0) { $('#jumlahAnak').val('0'); updateAnakList(); }
            } else {
                $('#formAnak').addClass('d-none'); $('#formAnakHidden').removeClass('d-none');
                $('#jumlahAnak').val('0'); $('#daftarAnak').html(''); $('#tanggunganNote').addClass('d-none');
            }
            updatePreview();
        }

        function updateAnakList() {
            const jumlah = parseInt($('#jumlahAnak').val()) || 0;
            const container = $('#daftarAnak');
            const existingInputs = container.find('.child-name-input');
            const existingData = []; existingInputs.each(function() { existingData.push($(this).val()); });
            container.html('');
            for (let i = 0; i < jumlah; i++) {
                const isTanggungan = i < 3;
                const nama = existingData[i] || '';
                container.append(`
                    <div class="child-item mb-2">
                        <div class="child-number">${i + 1}</div>
                        <input type="text" class="form-control form-control-sm child-name-input" placeholder="Nama Anak ${i + 1}" value="${nama}">
                        ${isTanggungan ? '<span class="child-tanggungan">Tanggungan</span>' : '<span class="text-muted small">Non-tanggungan</span>'}
                    </div>`);
            }
            if (jumlah > 3) { $('#tanggunganNote').removeClass('d-none'); $('#tanggunganNoteText').text(`${jumlah} anak tercatat, namun hanya 3 anak pertama yang menjadi tanggungan PTKP`); } 
            else { $('#tanggunganNote').addClass('d-none'); }
            updatePreview();
        }

        function updatePreview() {
            const kId = $('#inputKaryawan').val();
            const ptkpCode = $('#inputPtkp').val();
            if (!kId) return;
            
            const k = getKaryawan(kId);
            const gajiDasar = getGajiDasar(k);
            const calc = hitungPph21(gajiDasar, ptkpCode);
            
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
            
            if (ptkpCode) {
                const p = ptkp2026.find(x => x.id === ptkpCode);
                $('#ptkpValueInfo').html(`Tahun Pajak: <strong>2026</strong> | PTKP ${ptkpCode} = <strong>${fmtRp(p.nominalPerBulan)}/bulan</strong> (${fmtRp(p.nominalPerBulan * 12)}/tahun)`);
            } else {
                $('#ptkpValueInfo').text('PTKP Tahunan: -');
            }
        }

        // ===== WIZARD LOGIC =====
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
                if (!kId) { alert('Pilih karyawan terlebih dahulu!'); return; }
                if (!ptkp) { alert('Pilih status PTKP!'); return; }
                if (menikah === undefined) { alert('Pilih status menikah!'); return; }
                
                updatePreview();
                goStep(2);
            }
        }

        function wzPrev() { if (wzStep > 1) goStep(wzStep - 1); }

        function resetWizard() {
            wzStep = 1;
            goStep(1);
            $('#editId').val('');
            $('#inputKaryawan').val('');
            $('#inputPtkp').val('');
            $('input[name="statusMenikah"]').prop('checked', false);
            $('#jumlahAnak').val('0');
            $('#daftarAnak').html('');
            $('#formAnak').addClass('d-none');
            $('#formAnakHidden').addClass('d-none');
            $('#tanggunganNote').addClass('d-none');
            $('#infoNIP').text('-');
            $('#infoGaji').text('-');
            $('#modalPph21Title').text('Setup PPH 21 Karyawan');
        }

        function openModal(mode, karyawanId = null) {
            resetWizard();
            populateKaryawanDropdown();

            if (mode === 'edit' && karyawanId) {
                const pph = getPphByKaryawanId(karyawanId);
                if (!pph) { alert('Data PPH 21 tidak ditemukan'); return; }
                $('#modalPph21Title').text('Edit PPH 21 Karyawan');
                $('#editId').val(pph.id);
                $('#inputKaryawan').val(pph.karyawan_id).trigger('change');
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
            } else if (mode === 'create') {
                if (karyawanId) {
                    $('#inputKaryawan').val(karyawanId).trigger('change');
                }
            }
            updatePreview();
            new bootstrap.Modal(document.getElementById('modalPph21')).show();
        }

        function simpanData() {
            const editId = $('#editId').val();
            const kId = $('#inputKaryawan').val();
            const ptkp = $('#inputPtkp').val();
            const menikah = $('input[name="statusMenikah"]:checked').val();

            let anak = [];
            if (menikah === '1') {
                $('#daftarAnak .child-name-input').each(function() {
                    const nama = $(this).val().trim();
                    if (nama) anak.push({ nama });
                });
            }

            $.ajax({
                url: URL_STORE,
                method: 'POST',
                data: { karyawan_id: kId, ptkp: ptkp, status_menikah: menikah, anak: anak },
                success: function(res) {
                    if (res.success) {
                        alert(res.message);
                        bootstrap.Modal.getInstance(document.getElementById('modalPph21')).hide();
                        loadData();
                    }
                },
                error: function(xhr) {
                    let msg = 'Gagal menyimpan data!';
                    if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                    alert(msg);
                }
            });
        }

        function hapusPph(id) {
            if (!confirm('Hapus data PPH 21 ini?')) return;
            $.ajax({
                url: URL_BASE + '/' + id,
                method: 'DELETE',
                success: function(res) { alert(res.message); loadData(); },
                error: function() { alert('Gagal menghapus data!'); }
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
                    anakHtml += `<div class="child-item mb-2"><div class="child-number">${i + 1}</div><div class="child-name" style="flex:1;font-weight:500">${a.nama}</div>${isTanggungan ? '<span class="child-tanggungan">Tanggungan</span>' : '<span class="text-muted small">Non-tanggungan</span>'}</div>`;
                });
            } else { anakHtml = '<div class="text-muted small">Tidak ada data anak</div>'; }
            if (pph.anak.length > 3) anakHtml += `<div class="tanggungan-note mt-2"><i class="fa-solid fa-triangle-exclamation me-1"></i>${pph.anak.length} anak tercatat, hanya 3 anak pertama yang menjadi tanggungan PTKP</div>`;

            const html = `
                <div class="row g-2 mb-3">
                    <div class="col-md-6"><div class="info-card"><div class="label">NIP</div><div class="value">${k.nip}</div></div></div>
                    <div class="col-md-6"><div class="info-card"><div class="label">Nama</div><div class="value">${k.nama}</div></div></div>
                    <div class="col-md-6"><div class="info-card"><div class="label">Jabatan</div><div class="value">${k.jabatan}</div></div></div>
                    <div class="col-md-6"><div class="info-card"><div class="label">Gaji Dasar</div><div class="value">${fmtRp(gajiDasar)}</div></div></div>
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
                    <div class="calc-row mb-1"><span class="text-muted">Gaji Dasar (Bulanan)</span><span class="fw-bold">${fmtRp(gajiDasar)}</span></div>
                    <div class="calc-row mb-1"><span class="text-muted">Gaji Dasar (Tahunan)</span><span class="fw-bold">${fmtRp(calc.gajiTahunan)}</span></div>
                    <div class="calc-row mb-1"><span class="text-muted">PTKP (${pph.ptkp})</span><span class="fw-bold" style="color:var(--success)">${fmtRp(calc.ptkp)}</span></div>
                    <div class="calc-row mb-1"><span class="text-muted">Penghasilan Kena Pajak</span><span class="fw-bold">${fmtRp(calc.pkp)}</span></div>
                    <div class="calc-row mb-1"><span class="text-muted">PPH 21 Terutang (Tahunan)</span><span class="fw-bold">${fmtRp(calc.tahunan)}</span></div>
                    <div class="calc-row calc-total"><span>PPH 21 per Bulan</span><span>${fmtRp(calc.bulanan)}</span></div>
                </div>`;
            $('#detailContent').html(html);
            $('#btnEditFromDetail').off('click').on('click', function() {
                bootstrap.Modal.getInstance(document.getElementById('modalDetail')).hide();
                setTimeout(() => openModal('edit', karyawanId), 300);
            });
            new bootstrap.Modal(document.getElementById('modalDetail')).show();
        }
    </script>
@endsection