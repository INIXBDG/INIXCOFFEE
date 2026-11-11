@extends('layouts.app')

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">

<style>
    /* =========================================================================
       1. CRITICAL OVERRIDES: Prevent global .card { flex-direction: row } from
          breaking this page layout. All cards here must be vertical columns!
       ========================================================================= */
    .container-laporan,
    .container-laporan .card,
    .container-laporan .card-body,
    .stat-card,
    .banner-card,
    .filter-card,
    .table-card {
        display: flex !important;
        flex-direction: column !important;
        justify-content: flex-start !important;
        width: 100% !important;
        box-sizing: border-box !important;
    }

    /* Page container */
    .container-laporan {
        max-width: 100%;
        margin: 0 auto;
    }

    /* Banner Card */
    .banner-card {
        background: linear-gradient(135deg, #182f51 0%, #20406d 100%) !important;
        border-radius: 16px !important;
        color: #ffffff;
        box-shadow: 0 4px 15px rgba(24, 47, 81, 0.2);
    }

    /* Stat KPI Cards */
    .stat-card {
        border-radius: 14px !important;
        background: #ffffff !important;
        border: 1px solid rgba(24, 47, 81, 0.1) !important;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(24, 47, 81, 0.12) !important;
    }
    .stat-icon-wrapper {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
        flex-shrink: 0;
    }
    .bg-icon-primary { background-color: #e8eef6; color: #182f51; }
    .bg-icon-warning { background-color: #fef3c7; color: #d97706; }
    .bg-icon-info    { background-color: #e0f2fe; color: #0284c7; }
    .bg-icon-success { background-color: #dcfce7; color: #16a34a; }

    /* Filter Toolbar Card */
    .filter-card {
        border-radius: 14px !important;
        background: #ffffff !important;
        border: 1px solid #e2e8f0 !important;
    }

    /* Horizontal Pill Navigation Tabs (Clean, Modern, Never Squished) */
    .nav-pill-wrapper {
        display: flex !important;
        flex-direction: row !important;
        flex-wrap: wrap !important;
        gap: 10px;
        margin-bottom: 16px;
        list-style: none;
        padding-left: 0;
    }
    .nav-pill-btn {
        background: #ffffff !important;
        color: #475569 !important;
        border: 1px solid #cbd5e1 !important;
        border-radius: 30px !important;
        padding: 9px 20px !important;
        font-weight: 600 !important;
        font-size: 0.88rem !important;
        display: inline-flex !important;
        align-items: center !important;
        transition: all 0.2s ease !important;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05) !important;
        cursor: pointer !important;
        text-decoration: none !important;
    }
    .nav-pill-btn:hover {
        background: #f8fafc !important;
        color: #182f51 !important;
        border-color: #94a3b8 !important;
    }
    .nav-pill-btn.active {
        background: #182f51 !important;
        color: #ffffff !important;
        border-color: #182f51 !important;
        box-shadow: 0 4px 12px rgba(24, 47, 81, 0.25) !important;
    }
    .nav-pill-btn .badge-pill-counter {
        font-size: 0.75rem;
        padding: 3px 8px;
        border-radius: 12px;
        margin-left: 8px;
        font-weight: 700;
    }

    /* Tab Panes explicit visibility */
    .tab-content > .tab-pane {
        display: none;
    }
    .tab-content > .tab-pane.active {
        display: block !important;
    }

    /* Table Container Card */
    .table-card {
        border-radius: 16px !important;
        background: #ffffff !important;
        border: 1px solid #e2e8f0 !important;
        overflow: hidden;
    }

    /* DataTables Table Styling */
    .table-laporan-wrapper table.dataTable {
        border-collapse: separate !important;
        border-spacing: 0 !important;
        width: 100% !important;
        border: 1px solid #e2e8f0 !important;
        border-radius: 10px;
        margin-top: 8px !important;
        margin-bottom: 12px !important;
    }
    .table-laporan-wrapper table.dataTable thead th {
        background-color: #f8fafc !important;
        color: #1e293b !important;
        font-weight: 700 !important;
        font-size: 0.82rem !important;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        padding: 12px 14px !important;
        border-bottom: 2px solid #e2e8f0 !important;
        white-space: nowrap;
        vertical-align: middle;
    }
    .table-laporan-wrapper table.dataTable tbody td {
        font-size: 0.86rem !important;
        color: #334155;
        padding: 12px 14px !important;
        vertical-align: middle !important;
        border-bottom: 1px solid #f1f5f9 !important;
    }
    .table-laporan-wrapper table.dataTable tbody tr:hover td {
        background-color: #f8fafc !important;
    }
    .table-laporan-wrapper table.dataTable tbody tr:last-child td {
        border-bottom: none !important;
    }

    /* DataTables Controls (Length, Search, Paginate) */
    .dataTables_wrapper .dataTables_length {
        font-size: 0.85rem;
        color: #475569;
        margin-bottom: 10px;
    }
    .dataTables_wrapper .dataTables_length select {
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        padding: 5px 10px;
        outline: none;
        margin: 0 4px;
    }
    .dataTables_wrapper .dataTables_filter {
        font-size: 0.85rem;
        color: #475569;
        margin-bottom: 10px;
    }
    .dataTables_wrapper .dataTables_filter input {
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        padding: 5px 12px;
        outline: none;
        min-width: 220px;
        margin-left: 8px;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }
    .dataTables_wrapper .dataTables_filter input:focus {
        border-color: #182f51;
        box-shadow: 0 0 0 3px rgba(24, 47, 81, 0.15);
    }
    .dataTables_wrapper .dataTables_info {
        font-size: 0.85rem;
        color: #64748b;
        padding-top: 12px;
    }
    .dataTables_wrapper .dataTables_paginate {
        padding-top: 10px;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button.current {
        background: #182f51 !important;
        color: #ffffff !important;
        border-radius: 6px;
        border: none;
        font-size: 0.85rem;
        padding: 4px 10px;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
        background: #a5c7ef !important;
        color: #182f51 !important;
        border: none;
        border-radius: 6px;
    }

    /* Custom Avatar Circle with Fallback Initials */
    .avatar-wrapper {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-weight: 700;
        font-size: 13px;
        color: #ffffff;
        overflow: hidden;
        border: 2px solid #e2e8f0;
    }
    .avatar-wrapper img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    /* Action Buttons in Table */
    .btn-action-group .btn {
        padding: 5px 10px;
        font-size: 0.82rem;
        border-radius: 6px;
    }

    /* Action buttons in DataTable */
    .btn-action-group .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .btn-action-group .btn:only-child {
        border-radius: 6px !important;
    }

    /* System Cube Loader (Mini) for DataTable Processing */
    .cube-mini {
        width: 38px;
        height: 38px;
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 4px;
        margin: 0 auto 6px auto;
    }
    .cube-mini .cube_item {
        width: 17px !important;
        height: 17px !important;
        border-radius: 4px !important;
    }
    .dataTables_wrapper .dataTables_processing {
        position: absolute !important;
        top: 50% !important;
        left: 50% !important;
        transform: translate(-50%, -50%) !important;
        margin: 0 !important;
        padding: 16px 28px !important;
        background: rgba(255, 255, 255, 0.98) !important;
        border: 1px solid #e2e8f0 !important;
        border-radius: 14px !important;
        box-shadow: 0 10px 30px rgba(24, 47, 81, 0.15) !important;
        z-index: 1040 !important;
        color: #182f51 !important;
        font-weight: 600 !important;
        font-size: 0.88rem !important;
        text-align: center !important;
        min-width: 160px !important;
    }

    /* Cursor utility */
    .cursor-pointer { cursor: pointer; }
</style>

@section('content')
<div class="container-fluid px-3 px-md-4 py-3 container-laporan">

    <!-- Loading Modal (System Default 3D Cube Loader) -->
    <div class="modal fade" id="loadingModal" tabindex="-1" aria-labelledby="spinnerModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered" style="background: transparent; border: none; box-shadow: none;">
            <div class="cube">
                <div class="cube_item cube_x"></div>
                <div class="cube_item cube_y"></div>
                <div class="cube_item cube_x"></div>
                <div class="cube_item cube_z"></div>
            </div>
        </div>
    </div>

    <!-- Alert Notification Box -->
    <div id="alertContainer"></div>

    <!-- Page Header Banner -->
    <div class="card banner-card border-0 mb-4 p-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                    <span class="badge bg-warning text-dark px-3 py-1 fw-bold rounded-pill">
                        <i class="bi bi-clock-history me-1"></i> Status Belum di-Approve
                    </span>
                    <span class="badge bg-light text-dark px-3 py-1 rounded-pill">
                        <i class="bi bi-person-badge me-1"></i> {{ $jabatan }}
                    </span>
                    @if(!$is_gm)
                        <span class="badge bg-info text-dark px-3 py-1 rounded-pill">
                            <i class="bi bi-diagram-3 me-1"></i> Divisi: {{ $default_division }}
                        </span>
                    @else
                        <span class="badge bg-success px-3 py-1 rounded-pill">
                            <i class="bi bi-shield-check me-1"></i> Hak Akses GM (Lintas Divisi)
                        </span>
                    @endif
                </div>
                <h3 class="fw-bold mb-1 text-white">Persetujuan Pengajuan Divisi</h3>
                <p class="text-white-50 mb-0" style="font-size: 0.92rem;">
                    Monitoring &amp; eksekusi persetujuan Pengajuan Cuti, Pengajuan Barang, dan SPJ divisi Anda yang masih menunggu approval.
                </p>
            </div>
            <!-- <div>
                <button type="button" class="btn btn-light text-primary fw-semibold rounded-pill px-3 shadow-sm" onclick="reloadAllTables()">
                    <i class="bi bi-arrow-clockwise me-1"></i> Refresh Data
                </button>
            </div> -->
        </div>
    </div>

    <!-- Summary KPI Counter Cards -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card stat-card shadow-sm p-3 h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold">Total Pengajuan Pending</span>
                        <h2 class="fw-bold mb-0 text-dark mt-1" id="kpiTotalPending">-</h2>
                        <small class="text-muted">Semua jenis pengajuan</small>
                    </div>
                    <div class="stat-icon-wrapper bg-icon-primary shadow-sm">
                        <i class="bi bi-inbox-fill"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card stat-card shadow-sm p-3 h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold">Pengajuan Cuti</span>
                        <h2 class="fw-bold mb-0 text-warning mt-1" id="kpiCutiPending">-</h2>
                        <small class="text-muted">Menunggu persetujuan</small>
                    </div>
                    <div class="stat-icon-wrapper bg-icon-warning shadow-sm">
                        <i class="bi bi-calendar-event-fill"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card stat-card shadow-sm p-3 h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold">Pengajuan Barang</span>
                        <h2 class="fw-bold mb-0 text-info mt-1" id="kpiBarangPending">-</h2>
                        <small class="text-muted">Menunggu review manager</small>
                    </div>
                    <div class="stat-icon-wrapper bg-icon-info shadow-sm">
                        <i class="bi bi-box-seam-fill"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card stat-card shadow-sm p-3 h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold">Surat Perjalanan (SPJ)</span>
                        <h2 class="fw-bold mb-0 text-success mt-1" id="kpiSpjPending">-</h2>
                        <small class="text-muted">Menunggu persetujuan SPJ</small>
                    </div>
                    <div class="stat-icon-wrapper bg-icon-success shadow-sm">
                        <i class="bi bi-send-check-fill"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Toolbar Card -->
    <div class="card filter-card shadow-sm mb-4 p-3">
        <form id="filterForm" class="row g-2 align-items-center" onsubmit="event.preventDefault(); applyFilter();">
            <div class="col-12 col-md-3">
                <label for="filterDivisi" class="form-label small fw-bold text-muted mb-1">
                    <i class="bi bi-diagram-3 me-1"></i> Filter Divisi
                </label>
                @if($is_gm)
                    <select id="filterDivisi" class="form-select form-select-sm rounded-3">
                        @foreach($divisionsList as $label => $val)
                            <option value="{{ $val }}">{{ $label }}</option>
                        @endforeach
                    </select>
                @else
                    <input type="text" class="form-control form-control-sm rounded-3 bg-light" value="{{ $default_division }}" readonly disabled>
                    <input type="hidden" id="filterDivisi" value="{{ $default_division }}">
                @endif
            </div>

            <div class="col-6 col-md-2">
                <label for="filterTahun" class="form-label small fw-bold text-muted mb-1">
                    <i class="bi bi-calendar3 me-1"></i> Tahun
                </label>
                <select id="filterTahun" class="form-select form-select-sm rounded-3">
                    <option value="all">Semua Tahun</option>
                    @foreach($years as $yr)
                        <option value="{{ $yr }}" {{ $yr == $currentYear ? 'selected' : '' }}>{{ $yr }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-6 col-md-2">
                <label for="filterBulan" class="form-label small fw-bold text-muted mb-1">
                    <i class="bi bi-calendar-month me-1"></i> Bulan
                </label>
                <select id="filterBulan" class="form-select form-select-sm rounded-3">
                    <option value="all" selected>Semua Bulan</option>
                    @foreach($months as $num => $namaBulan)
                        <option value="{{ $num }}">{{ $namaBulan }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-12 col-md-5 d-flex gap-2 align-items-end mt-3 mt-md-0">
                <button type="button" class="btn btn-sm click-primary text-white px-3 rounded-pill" onclick="applyFilter()" style="height: 35px;">
                    <i class="bi bi-funnel me-1"></i> Terapkan Filter
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary px-3 rounded-pill" onclick="resetFilter()" style="height: 35px;">
                    <i class="bi bi-x-circle me-1"></i> Reset
                </button>
                <span class="text-muted small ms-auto d-none d-lg-inline">
                    <i class="bi bi-info-circle me-1"></i> Data real-time belum di-approve
                </span>
            </div>
        </form>
    </div>

    <!-- Navigation Pills (Horizontal Bar Above Table) -->
    <ul class="nav nav-pills nav-pill-wrapper" id="laporanTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link nav-pill-btn active" id="tab-semua-btn" data-bs-toggle="pill" data-bs-target="#tab-semua" data-target="#tab-semua" type="button" role="tab" aria-controls="tab-semua" aria-selected="true">
                <i class="bi bi-grid-fill me-1"></i> Semua Pengajuan
                <span class="badge bg-danger badge-pill-counter" id="badgeTabSemua">0</span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link nav-pill-btn" id="tab-cuti-btn" data-bs-toggle="pill" data-bs-target="#tab-cuti" data-target="#tab-cuti" type="button" role="tab" aria-controls="tab-cuti" aria-selected="false">
                <i class="bi bi-calendar-event me-1"></i> Pengajuan Cuti
                <span class="badge bg-warning text-dark badge-pill-counter" id="badgeTabCuti">0</span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link nav-pill-btn" id="tab-barang-btn" data-bs-toggle="pill" data-bs-target="#tab-barang" data-target="#tab-barang" type="button" role="tab" aria-controls="tab-barang" aria-selected="false">
                <i class="bi bi-box-seam me-1"></i> Pengajuan Barang
                <span class="badge bg-info text-dark badge-pill-counter" id="badgeTabBarang">0</span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link nav-pill-btn" id="tab-spj-btn" data-bs-toggle="pill" data-bs-target="#tab-spj" data-target="#tab-spj" type="button" role="tab" aria-controls="tab-spj" aria-selected="false">
                <i class="bi bi-send me-1"></i> Pengajuan SPJ
                <span class="badge bg-success badge-pill-counter" id="badgeTabSpj">0</span>
            </button>
        </li>
    </ul>

    <!-- Table Card Container -->
    <div class="card table-card shadow-sm p-3 p-md-4">
        <div class="tab-content table-laporan-wrapper" id="laporanTabsContent">

            <!-- TAB 1: SEMUA PENGAJUAN -->
            <div class="tab-pane fade show active" id="tab-semua" role="tabpanel">
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle w-100" id="tableSemua">
                        <thead>
                            <tr>
                                <th style="width: 45px;" class="text-center">No</th>
                                <th style="width: 90px;" class="text-center">Kategori</th>
                                <th style="width: 130px;">Tgl Pengajuan</th>
                                <th style="width: 190px;">Karyawan</th>
                                <th style="width: 160px;">Divisi / Jabatan</th>
                                <th>Ringkasan Keperluan</th>
                                <th style="width: 170px;" class="text-center">Status</th>
                                <th style="width: 120px;" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>

            <!-- TAB 2: PENGAJUAN CUTI -->
            <div class="tab-pane fade" id="tab-cuti" role="tabpanel">
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle w-100" id="tableCuti">
                        <thead>
                            <tr>
                                <th style="width: 45px;" class="text-center">No</th>
                                <th style="width: 130px;">Tgl Pengajuan</th>
                                <th style="width: 190px;">Karyawan</th>
                                <th style="width: 160px;">Divisi / Jabatan</th>
                                <th style="width: 90px;" class="text-center">Tipe</th>
                                <th style="width: 170px;">Periode Cuti</th>
                                <th style="width: 80px;" class="text-center">Durasi</th>
                                <th style="width: 120px;">Kontak</th>
                                <th>Alasan Cuti</th>
                                <th style="width: 170px;" class="text-center">Status</th>
                                <th style="width: 120px;" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>

            <!-- TAB 3: PENGAJUAN BARANG -->
            <div class="tab-pane fade" id="tab-barang" role="tabpanel">
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle w-100" id="tableBarang">
                        <thead>
                            <tr>
                                <th style="width: 45px;" class="text-center">No</th>
                                <th style="width: 130px;">Tgl Pengajuan</th>
                                <th style="width: 190px;">Karyawan</th>
                                <th style="width: 160px;">Divisi / Jabatan</th>
                                <th style="width: 110px;" class="text-center">Tipe</th>
                                <th>Rincian Barang</th>
                                <th style="width: 80px;" class="text-center">Qty</th>
                                <th style="width: 130px;">Estimasi Total</th>
                                <th style="width: 180px;" class="text-center">Status Tracking</th>
                                <th style="width: 120px;" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>

            <!-- TAB 4: PENGAJUAN SPJ -->
            <div class="tab-pane fade" id="tab-spj" role="tabpanel">
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle w-100" id="tableSpj">
                        <thead>
                            <tr>
                                <th style="width: 45px;" class="text-center">No</th>
                                <th style="width: 130px;">Tgl Pengajuan</th>
                                <th style="width: 190px;">Karyawan</th>
                                <th style="width: 160px;">Divisi / Jabatan</th>
                                <th style="width: 120px;">No Surat / RKM</th>
                                <th>Tujuan &amp; Agenda</th>
                                <th style="width: 170px;">Periode</th>
                                <th style="width: 90px;" class="text-center">Tipe</th>
                                <th style="width: 180px;" class="text-center">Status</th>
                                <th style="width: 120px;" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

</div>

<!-- Universal Approval Modal -->
<div class="modal fade" id="approvalActionModal" tabindex="-1" aria-labelledby="approvalActionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow border-0" style="border-radius: 16px;">
            <div class="modal-header bg-light border-0">
                <h5 class="modal-title fw-bold text-dark" id="approvalActionModalLabel">
                    <i class="bi bi-shield-check text-primary me-2"></i>Konfirmasi Persetujuan
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formApprovalAction" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" id="modalTargetType" name="target_type" value="">
                <input type="hidden" id="modalTargetId" name="target_id" value="">

                <div class="modal-body p-4">
                    <div class="alert alert-light border d-flex align-items-center gap-3 p-3 mb-3 rounded-3">
                        <i class="bi bi-info-circle-fill text-primary fs-3"></i>
                        <div>
                            <span class="d-block small text-muted text-uppercase fw-semibold" id="modalItemCategory">Pengajuan</span>
                            <strong class="d-block text-dark" id="modalItemTitle">-</strong>
                            <small class="text-muted" id="modalItemSubtitle">-</small>
                        </div>
                    </div>

                    <label class="form-label fw-bold mb-2">Keputusan Approval:</label>
                    <div class="d-flex gap-3 mb-3">
                        <div class="form-check p-3 border rounded-3 flex-fill cursor-pointer" onclick="$('#radioApprove').prop('checked', true).trigger('change');">
                            <input class="form-check-input" type="radio" name="approval_decision" id="radioApprove" value="1" checked>
                            <label class="form-check-label fw-bold text-success ms-2" for="radioApprove">
                                <i class="bi bi-check-circle-fill me-1"></i> Setujui (Approve)
                            </label>
                        </div>
                        <div class="form-check p-3 border rounded-3 flex-fill cursor-pointer" onclick="$('#radioReject').prop('checked', true).trigger('change');">
                            <input class="form-check-input" type="radio" name="approval_decision" id="radioReject" value="2">
                            <label class="form-check-label fw-bold text-danger ms-2" for="radioReject">
                                <i class="bi bi-x-circle-fill me-1"></i> Tolak (Reject)
                            </label>
                        </div>
                    </div>

                    <div id="alasanPenolakanGroup" style="display: none;">
                        <label for="alasan_penolakan" class="form-label fw-bold text-danger">
                            Alasan Penolakan <span class="text-danger">*</span>
                        </label>
                        <textarea class="form-control rounded-3" id="alasan_penolakan" name="alasan" rows="3" placeholder="Tuliskan alasan penolakan pengajuan ini..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 bg-light p-3">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn click-primary text-white rounded-pill px-4" id="btnSubmitApproval">
                        <i class="bi bi-send me-1"></i> Simpan Keputusan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('js')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.17.1/moment-with-locales.min.js"></script>

<script>
    let dtSemua = null;
    let dtCuti = null;
    let dtBarang = null;
    let dtSpj = null;

    const CURRENT_USER_ROLE = @json($jabatan);
    const IS_GM = @json($is_gm);

    function getFilterParams() {
        return {
            divisi: $('#filterDivisi').val(),
            tahun: $('#filterTahun').val(),
            bulan: $('#filterBulan').val()
        };
    }

    function showAlert(type, message) {
        const icon = type === 'success' ? 'check-circle-fill' : 'exclamation-triangle-fill';
        const html = `
            <div class="alert alert-${type} alert-dismissible fade show shadow-sm rounded-3 mb-3" role="alert">
                <i class="bi bi-${icon} me-2"></i> ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
        $('#alertContainer').html(html);
        $('html, body').animate({ scrollTop: $('#alertContainer').offset().top - 70 }, 300);
    }

    // Helper avatar circle generator with initials fallback
    function renderAvatar(nama, foto, kode) {
        let safeNama = nama || 'Karyawan';
        let safeKode = kode || '-';
        let initials = '';
        const parts = safeNama.trim().split(/\s+/);
        if (parts.length > 1) {
            initials = (parts[0][0] + parts[1][0]).toUpperCase();
        } else {
            initials = parts[0].substring(0, 2).toUpperCase();
        }

        const colors = ['#182f51', '#1d4ed8', '#0284c7', '#0f766e', '#b45309', '#6d28d9'];
        let charSum = 0;
        for (let i = 0; i < safeNama.length; i++) charSum += safeNama.charCodeAt(i);
        const bgColor = colors[charSum % colors.length];

        const hasFoto = (foto && foto !== '' && !foto.includes('default-profile.jpg'));

        return `
            <div class="d-flex align-items-center gap-2">
                <div class="avatar-wrapper" style="background-color: ${bgColor};">
                    ${hasFoto ? `
                        <img src="${foto}" alt="" onerror="this.remove();">
                    ` : ''}
                    <span>${initials}</span>
                </div>
                <div style="min-width: 0;">
                    <div class="fw-bold text-dark text-truncate" style="max-width: 150px;" title="${safeNama}">${safeNama}</div>
                    <small class="text-muted d-block">${safeKode}</small>
                </div>
            </div>
        `;
    }

    // Helper status badge generator
    function formatStatusBadge(status) {
        if (!status) return `<span class="badge bg-secondary">Menunggu</span>`;
        let label = status;
        let icon = 'bi-hourglass-split';
        let bgClass = 'bg-warning text-dark';

        if (status.includes('Koordinator IT Service Management') || status.includes('Koordinator ITSM')) {
            label = 'Review Koordinator ITSM';
        } else if (status.includes('SPV Sales')) {
            label = 'Review SPV Sales';
        } else if (status.includes('Education Manager')) {
            label = 'Review Education Manager';
        } else if (status.includes('General Manager') || status.includes('GM')) {
            label = 'Review GM';
        } else if (status.includes('Direktur Utama')) {
            label = 'Permintaan Dirut';
        } else if (status.includes('Finance')) {
            label = 'Proses Finance';
            bgClass = 'bg-info text-dark';
        } else if (status.includes('Manager Divisi')) {
            label = 'Review Manager Divisi';
        } else if (status.toLowerCase().includes('ditolak')) {
            label = 'Ditolak';
            bgClass = 'bg-danger text-white';
            icon = 'bi-x-circle';
        } else if (status.toLowerCase().includes('disetujui') || status.toLowerCase().includes('selesai')) {
            label = 'Disetujui';
            bgClass = 'bg-success text-white';
            icon = 'bi-check-circle';
        }

        return `
            <span class="badge rounded-pill ${bgClass} px-2 py-1 text-wrap text-center d-inline-block" 
                  style="font-size: 0.78rem; font-weight: 600; line-height: 1.3;" 
                  title="${escapeHtml(status)}">
                <i class="bi ${icon} me-1"></i>${label}
            </span>
        `;
    }

    // Helper category badge generator
    function formatCategoryBadge(kategori) {
        if (kategori === 'Cuti') {
            return `<span class="badge rounded-pill bg-warning text-dark px-2 py-1"><i class="bi bi-calendar-event me-1"></i> Cuti</span>`;
        } else if (kategori === 'Barang') {
            return `<span class="badge rounded-pill bg-primary text-white px-2 py-1"><i class="bi bi-box-seam me-1"></i> Barang</span>`;
        } else if (kategori === 'SPJ') {
            return `<span class="badge rounded-pill bg-success text-white px-2 py-1"><i class="bi bi-send me-1"></i> SPJ</span>`;
        }
        return `<span class="badge rounded-pill bg-secondary text-white px-2 py-1">${kategori}</span>`;
    }

    // Global Loading Counter for System Modal Loader (#loadingModal)
    let loadingRequestsCount = 0;
    function showLoading() {
        loadingRequestsCount++;
        if (loadingRequestsCount === 1) {
            $('#loadingModal').modal('show');
            $('#loadingModal').removeAttr('inert');
        }
    }

    function hideLoading() {
        loadingRequestsCount = Math.max(0, loadingRequestsCount - 1);
        if (loadingRequestsCount === 0) {
            setTimeout(function() {
                if (loadingRequestsCount === 0) {
                    $('#loadingModal').modal('hide');
                    $('#loadingModal').attr('inert', true);
                }
            }, 350);
        }
    }

    // Refresh KPI Counter
    function loadSummaryKPI() {
        showLoading();
        $.ajax({
            url: "{{ route('laporan-pending.summary') }}",
            type: "GET",
            data: getFilterParams(),
            success: function(res) {
                if (res.success) {
                    $('#kpiTotalPending').text(res.total_pending);
                    $('#kpiCutiPending').text(res.cuti_count);
                    $('#kpiBarangPending').text(res.barang_count);
                    $('#kpiSpjPending').text(res.spj_count);

                    $('#badgeTabSemua').text(res.total_pending);
                    $('#badgeTabCuti').text(res.cuti_count);
                    $('#badgeTabBarang').text(res.barang_count);
                    $('#badgeTabSpj').text(res.spj_count);
                }
            },
            complete: function() {
                hideLoading();
            }
        });
    }

    function reloadAllTables() {
        loadSummaryKPI();
        if (dtSemua) dtSemua.ajax.reload(null, false);
        if (dtCuti) dtCuti.ajax.reload(null, false);
        if (dtBarang) dtBarang.ajax.reload(null, false);
        if (dtSpj) dtSpj.ajax.reload(null, false);
    }

    function applyFilter() {
        reloadAllTables();
    }

    function resetFilter() {
        $('#filterTahun').val('all');
        $('#filterBulan').val('all');
        @if($is_gm)
            $('#filterDivisi').val('all');
        @endif
        reloadAllTables();
    }

    $(document).ready(function() {
        moment.locale('id');

        // Toggle alasan penolakan
        $('input[name="approval_decision"]').on('change', function() {
            if ($(this).val() === '2') {
                $('#alasanPenolakanGroup').slideDown();
                $('#alasan_penolakan').prop('required', true);
            } else {
                $('#alasanPenolakanGroup').slideUp();
                $('#alasan_penolakan').prop('required', false);
            }
        });

        // System Default Loader HTML for DataTables Processing Container
        const systemProcessingHtml = `
            <div class="d-flex flex-column align-items-center justify-content-center p-2">
                <div class="cube-mini">
                    <div class="cube_item cube_x"></div>
                    <div class="cube_item cube_y"></div>
                    <div class="cube_item cube_x"></div>
                    <div class="cube_item cube_z"></div>
                </div>
                <div class="fw-bold mt-2 text-dark" style="font-size: 0.85rem;">Mohon tunggu...</div>
            </div>
        `;

        // -------------------------------------------------------------
        // TABLE 1: SEMUA PENGAJUAN
        // -------------------------------------------------------------
        dtSemua = $('#tableSemua').DataTable({
            processing: true,
            pageLength: 10,
            lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
            ajax: {
                url: "{{ route('laporan-pending.data-semua') }}",
                type: "GET",
                data: function(d) {
                    return $.extend({}, d, getFilterParams());
                },
                beforeSend: function() {
                    showLoading();
                },
                complete: function() {
                    hideLoading();
                }
            },
            columns: [
                { data: 'index', className: 'text-center' },
                {
                    data: 'kategori',
                    className: 'text-center',
                    render: function(val) {
                        return formatCategoryBadge(val);
                    }
                },
                { data: 'tgl_pengajuan' },
                {
                    data: null,
                    render: function(data) {
                        return renderAvatar(data.nama_lengkap, data.foto, data.kode_karyawan);
                    }
                },
                {
                    data: null,
                    render: function(data) {
                        return `
                            <div><strong>${data.divisi}</strong></div>
                            <small class="text-muted">${data.jabatan}</small>
                        `;
                    }
                },
                { data: 'keterangan' },
                {
                    data: 'status_label',
                    className: 'text-center',
                    render: function(val) {
                        return formatStatusBadge(val);
                    }
                },
                {
                    data: null,
                    orderable: false,
                    className: 'text-center',
                    render: function(data) {
                        const reviewBtn = data.can_approve ? `
                            <button type="button" class="btn click-primary text-white" onclick="openApprovalModal('${data.type_key}', ${data.id}, '${escapeHtml(data.nama_lengkap)}', '${data.kategori}')" title="Proses Approval">
                                <i class="bi bi-check-circle me-1"></i>Review
                            </button>
                        ` : '';

                        return `
                            <div class="btn-group btn-group-sm btn-action-group">
                                <a href="${data.detail_url}" target="_blank" class="btn btn-outline-secondary" title="Lihat Detail / Form">
                                    <i class="bi bi-eye"></i>
                                </a>
                                ${reviewBtn}
                            </div>
                        `;
                    }
                }
            ],
            order: [[2, 'desc']],
            language: {
                processing: systemProcessingHtml,
                search: "Cari Data:",
                searchPlaceholder: "Ketik untuk mencari...",
                lengthMenu: "Tampilkan _MENU_ baris",
                info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                infoEmpty: "Data tidak tersedia",
                emptyTable: "Tidak ada pengajuan yang belum di-approve untuk divisi ini.",
                paginate: {
                    first: "Pertama",
                    last: "Terakhir",
                    next: "Berikutnya",
                    previous: "Sebelumnya"
                }
            }
        });

        // -------------------------------------------------------------
        // TABLE 2: PENGAJUAN CUTI
        // -------------------------------------------------------------
        dtCuti = $('#tableCuti').DataTable({
            processing: true,
            pageLength: 10,
            ajax: {
                url: "{{ route('laporan-pending.data-cuti') }}",
                type: "GET",
                data: function(d) {
                    return $.extend({}, d, getFilterParams());
                },
                beforeSend: function() {
                    showLoading();
                },
                complete: function() {
                    hideLoading();
                }
            },
            columns: [
                { data: 'index', className: 'text-center' },
                { data: 'tgl_pengajuan' },
                {
                    data: null,
                    render: function(data) {
                        return renderAvatar(data.nama_lengkap, data.foto, data.kode_karyawan);
                    }
                },
                {
                    data: null,
                    render: function(data) {
                        return `
                            <div><strong>${data.divisi}</strong></div>
                            <small class="text-muted">${data.jabatan}</small>
                        `;
                    }
                },
                {
                    data: 'tipe',
                    className: 'text-center',
                    render: function(val) {
                        return `<span class="badge bg-secondary text-white px-2 py-1">${val}</span>`;
                    }
                },
                { data: 'periode' },
                {
                    data: 'durasi',
                    className: 'text-center',
                    render: function(val) {
                        return `<span class="badge bg-light text-dark border">${val}</span>`;
                    }
                },
                { data: 'kontak' },
                {
                    data: null,
                    render: function(data) {
                        let res = `<span>${data.alasan}</span>`;
                        if (data.surat_sakit) {
                            res += `<br><a href="${data.surat_sakit}" target="_blank" class="badge bg-info text-dark mt-1 text-decoration-none"><i class="bi bi-paperclip"></i> Surat Sakit</a>`;
                        }
                        return res;
                    }
                },
                {
                    data: 'status_label',
                    className: 'text-center',
                    render: function(val) {
                        return formatStatusBadge(val);
                    }
                },
                {
                    data: null,
                    orderable: false,
                    className: 'text-center',
                    render: function(data) {
                        const reviewBtn = data.can_approve ? `
                            <button type="button" class="btn click-primary text-white" onclick="openApprovalModal('cuti', ${data.id}, '${escapeHtml(data.nama_lengkap)}', 'Cuti - ${escapeHtml(data.tipe)}')" title="Review Approval">
                                <i class="bi bi-check-circle me-1"></i>Review
                            </button>
                        ` : '';

                        return `
                            <div class="btn-group btn-group-sm btn-action-group">
                                <a href="${data.pdf_url}" target="_blank" class="btn btn-outline-secondary" title="Lihat Form PDF">
                                    <i class="bi bi-file-earmark-pdf"></i>
                                </a>
                                ${reviewBtn}
                            </div>
                        `;
                    }
                }
            ],
            order: [[1, 'desc']],
            language: {
                processing: systemProcessingHtml,
                search: "Cari Cuti:",
                searchPlaceholder: "Ketik untuk mencari...",
                emptyTable: "Tidak ada pengajuan cuti yang belum di-approve."
            }
        });

        // -------------------------------------------------------------
        // TABLE 3: PENGAJUAN BARANG
        // -------------------------------------------------------------
        dtBarang = $('#tableBarang').DataTable({
            processing: true,
            pageLength: 10,
            ajax: {
                url: "{{ route('laporan-pending.data-barang') }}",
                type: "GET",
                data: function(d) {
                    return $.extend({}, d, getFilterParams());
                },
                beforeSend: function() {
                    showLoading();
                },
                complete: function() {
                    hideLoading();
                }
            },
            columns: [
                { data: 'index', className: 'text-center' },
                { data: 'tgl_pengajuan' },
                {
                    data: null,
                    render: function(data) {
                        return renderAvatar(data.nama_lengkap, data.foto, data.kode_karyawan);
                    }
                },
                {
                    data: null,
                    render: function(data) {
                        return `
                            <div><strong>${data.divisi}</strong></div>
                            <small class="text-muted">${data.jabatan}</small>
                        `;
                    }
                },
                {
                    data: 'tipe',
                    className: 'text-center',
                    render: function(val) {
                        return `<span class="badge bg-secondary px-2 py-1">${val}</span>`;
                    }
                },
                { data: 'rincian_barang' },
                { data: 'total_qty', className: 'text-center' },
                {
                    data: 'total_harga_rp',
                    render: function(val) {
                        return `<strong class="text-success">${val}</strong>`;
                    }
                },
                {
                    data: 'tracking',
                    className: 'text-center',
                    render: function(val) {
                        return formatStatusBadge(val);
                    }
                },
                {
                    data: null,
                    orderable: false,
                    className: 'text-center',
                    render: function(data) {
                        const reviewBtn = data.can_approve ? `
                            <button type="button" class="btn click-primary text-white" onclick="openApprovalModal('barang', ${data.id}, '${escapeHtml(data.nama_lengkap)}', 'Barang - ${data.total_harga_rp}')" title="Review Approval">
                                <i class="bi bi-check-circle me-1"></i>Review
                            </button>
                        ` : '';

                        return `
                            <div class="btn-group btn-group-sm btn-action-group">
                                <a href="${data.detail_url}" target="_blank" class="btn btn-outline-secondary" title="Detail Pengajuan Barang">
                                    <i class="bi bi-eye"></i>
                                </a>
                                ${reviewBtn}
                            </div>
                        `;
                    }
                }
            ],
            order: [[1, 'desc']],
            language: {
                processing: systemProcessingHtml,
                search: "Cari Barang:",
                searchPlaceholder: "Ketik untuk mencari...",
                emptyTable: "Tidak ada pengajuan barang yang belum di-approve."
            }
        });

        // -------------------------------------------------------------
        // TABLE 4: PENGAJUAN SPJ
        // -------------------------------------------------------------
        dtSpj = $('#tableSpj').DataTable({
            processing: true,
            pageLength: 10,
            ajax: {
                url: "{{ route('laporan-pending.data-spj') }}",
                type: "GET",
                data: function(d) {
                    return $.extend({}, d, getFilterParams());
                },
                beforeSend: function() {
                    showLoading();
                },
                complete: function() {
                    hideLoading();
                }
            },
            columns: [
                { data: 'index', className: 'text-center' },
                { data: 'tgl_pengajuan' },
                {
                    data: null,
                    render: function(data) {
                        return renderAvatar(data.nama_lengkap, data.foto, data.kode_karyawan);
                    }
                },
                {
                    data: null,
                    render: function(data) {
                        return `
                            <div><strong>${data.divisi}</strong></div>
                            <small class="text-muted">${data.jabatan}</small>
                        `;
                    }
                },
                { data: 'no_surat' },
                {
                    data: null,
                    render: function(data) {
                        return `
                            <div><strong>${data.tujuan}</strong></div>
                            <small class="text-muted">${data.keperluan}</small>
                        `;
                    }
                },
                { data: 'periode' },
                {
                    data: 'tipe',
                    className: 'text-center',
                    render: function(val) {
                        const badgeClass = val === 'Internasional' ? 'bg-danger' : 'bg-success';
                        return `<span class="badge ${badgeClass} text-white px-2 py-1">${val}</span>`;
                    }
                },
                {
                    data: 'status_label',
                    className: 'text-center',
                    render: function(val) {
                        return formatStatusBadge(val);
                    }
                },
                {
                    data: null,
                    orderable: false,
                    className: 'text-center',
                    render: function(data) {
                        const reviewBtn = data.can_approve ? `
                            <button type="button" class="btn click-primary text-white" onclick="openApprovalModal('spj', ${data.id}, '${escapeHtml(data.nama_lengkap)}', 'SPJ ke ${escapeHtml(data.tujuan)}')" title="Review Approval">
                                <i class="bi bi-check-circle me-1"></i>Review
                            </button>
                        ` : '';

                        return `
                            <div class="btn-group btn-group-sm btn-action-group">
                                <a href="${data.pdf_url}" target="_blank" class="btn btn-outline-secondary" title="Lihat SPJ / PDF">
                                    <i class="bi bi-file-earmark-pdf"></i>
                                </a>
                                ${reviewBtn}
                            </div>
                        `;
                    }
                }
            ],
            order: [[1, 'desc']],
            language: {
                processing: systemProcessingHtml,
                search: "Cari SPJ:",
                searchPlaceholder: "Ketik untuk mencari...",
                emptyTable: "Tidak ada pengajuan SPJ yang belum di-approve."
            }
        });

        // Load initial summary KPI
        loadSummaryKPI();

        // -------------------------------------------------------------
        // EXPLICIT TAB SWITCHING HANDLER (Reliable & Instant)
        // -------------------------------------------------------------
        $(document).on('click', '.nav-pill-btn', function(e) {
            e.preventDefault();

            // 1. Remove active state from all buttons and add to clicked button
            $('.nav-pill-btn').removeClass('active').attr('aria-selected', 'false');
            $(this).addClass('active').attr('aria-selected', 'true');

            // 2. Hide all tab panes and show the targeted pane
            const targetPane = $(this).attr('data-bs-target') || $(this).attr('data-target');
            if (targetPane) {
                $('#laporanTabsContent > .tab-pane').removeClass('show active').css('display', 'none');
                $(targetPane).addClass('show active').css('display', 'block');
            }

            // 3. Recalculate DataTable column widths for visible table
            setTimeout(function() {
                $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
            }, 60);
        });

        // -------------------------------------------------------------
        // SUBMIT APPROVAL ACTION FORM (AJAX)
        // -------------------------------------------------------------
        $('#formApprovalAction').on('submit', function(e) {
            e.preventDefault();

            const type = $('#modalTargetType').val();
            const id = $('#modalTargetId').val();
            const decision = $('input[name="approval_decision"]:checked').val();
            const alasan = $('#alasan_penolakan').val();

            let targetUrl = '';
            let requestData = {};

            if (type === 'cuti') {
                targetUrl = `/pengajuancuti/${id}`;
                requestData = {
                    _token: '{{ csrf_token() }}',
                    _method: 'PUT',
                    approval: decision,
                    alasan: alasan
                };
            } else if (type === 'barang') {
                targetUrl = `/pengajuanbarang/${id}`;
                requestData = {
                    _token: '{{ csrf_token() }}',
                    _method: 'PUT',
                    approval: decision,
                    alasan: alasan
                };
            } else if (type === 'spj') {
                targetUrl = `/suratperjalanan/${id}/approval`;
                requestData = {
                    _token: '{{ csrf_token() }}',
                    _method: 'PUT',
                    approval_manager: decision,
                    alasan: alasan
                };
                if (IS_GM) {
                    requestData.approval_gm = decision;
                }
            }

            $('#btnSubmitApproval').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...');

            $.ajax({
                url: targetUrl,
                type: 'POST',
                data: requestData,
                success: function(response) {
                    $('#approvalActionModal').modal('hide');
                    $('#btnSubmitApproval').prop('disabled', false).html('<i class="bi bi-send me-1"></i> Simpan Keputusan');
                    showAlert('success', 'Keputusan approval berhasil disimpan!');
                    reloadAllTables();
                },
                error: function(xhr) {
                    $('#btnSubmitApproval').prop('disabled', false).html('<i class="bi bi-send me-1"></i> Simpan Keputusan');
                    let errMsg = 'Gagal memproses approval.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errMsg = xhr.responseJSON.message;
                    }
                    showAlert('danger', errMsg);
                }
            });
        });
    });

    // Open Approval Modal
    function openApprovalModal(type, id, nama, detail) {
        $('#modalTargetType').val(type);
        $('#modalTargetId').val(id);
        $('#modalItemTitle').text(nama);
        $('#modalItemSubtitle').text(detail);

        let catText = 'Pengajuan';
        if (type === 'cuti') catText = 'Pengajuan Cuti';
        if (type === 'barang') catText = 'Pengajuan Barang';
        if (type === 'spj') catText = 'Pengajuan SPJ';
        $('#modalItemCategory').text(catText);

        $('#radioApprove').prop('checked', true).trigger('change');
        $('#alasan_penolakan').val('');
        $('#alasanPenolakanGroup').hide();

        $('#approvalActionModal').modal('show');
    }

    function escapeHtml(str) {
        if (!str) return '';
        return String(str).replace(/'/g, "\\'").replace(/"/g, '&quot;');
    }
</script>
@endpush
