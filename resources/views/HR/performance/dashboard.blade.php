@extends('layout_HR.app')

@section('content_HR')
    <div class="container-fluid py-4">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold text-gradient mb-1">Performance Dashboard</h4>
                <p class="text-muted mb-0">Monitor progress KPI dan Penilaian 360 karyawan</p>
            </div>
        </div>

        <!-- Filter Tabs -->
        <div class="card glass-force mb-4 border-0">
            <div class="card-body p-4">
                <form id="filterForm" class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label fw-bold text-muted small text-uppercase">Periode Tahun</label>
                        <select name="tahun" id="filterTahun" class="form-select">
                            @for($y = now()->year; $y >= now()->year - 5; $y--)
                                <option value="{{ $y }}" {{ $y == now()->year ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold text-muted small text-uppercase">Filter Divisi</label>
                        <select name="divisi" id="filterDivisi" class="form-select">
                            <option value="">Semua Divisi</option>
                            @foreach($divisiList as $d)
                                <option value="{{ $d }}">{{ $d }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold text-muted small text-uppercase">Filter Jabatan</label>
                        <select name="jabatan" id="filterJabatan" class="form-select">
                            <option value="">Semua Jabatan</option>
                            @foreach($jabatanList as $j)
                                <option value="{{ $j }}">{{ $j }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold text-muted small text-uppercase">Cari Nama</label>
                        <input type="text" name="search" id="filterSearch" class="form-control"
                            placeholder="Nama karyawan...">
                    </div>
                    <div class="col-md-1">
                        <button type="submit" class="btn btn-primary w-100 btn-glow">
                            <i class="mdi mdi-filter-variant">Cari Karyawan</i>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Summary Info -->
        <div class="row mb-3">
            <div class="col-md-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="badge bg-primary rounded-pill px-3 py-2">
                            <i class="mdi mdi-account-group me-1"></i>
                            Total: <span id="totalUsers">0</span> Karyawan
                        </span>
                    </div>
                    <div class="btn-group btn-group-sm" role="group">
                        <button type="button" class="btn btn-outline-primary" id="btnExpandAll">
                            <i class="mdi mdi-arrow-expand-all me-1"></i> Buka Semua
                        </button>
                        <button type="button" class="btn btn-outline-secondary" id="btnCollapseAll">
                            <i class="mdi mdi-arrow-collapse-all me-1"></i> Tutup Semua
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Accordion Content -->
        <div id="accordionContainer">
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status"></div>
                <p class="mt-2 text-muted">Memuat data performa...</p>
            </div>
        </div>
    </div>

    <!-- Modal Detail Penilaian 360 -->
    <!-- Modal Detail Penilaian 360 -->
    <div class="modal fade" id="modalAssessment360Detail" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-gradient-primary text-white border-0">
                    <div class="d-flex align-items-center flex-grow-1">
                        <img id="modalUserFoto" src="" class="rounded-circle border border-2 border-white me-3" width="50"
                            height="50" style="object-fit: cover;">
                        <div>
                            <h5 class="modal-title fw-bold mb-0" id="modalUserName">Detail Penilaian 360°</h5>
                            <small id="modalUserMeta" class="opacity-75">-</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body p-0">
                    <div id="modalLoading" class="text-center py-5">
                        <div class="spinner-border text-primary" role="status"></div>
                        <p class="mt-2 text-muted">Memuat data penilaian...</p>
                    </div>
                    <div id="modalContent" class="p-4" style="display: none;">
                        <div class="row g-4">
                            <!-- Left Column: Info & Absensi -->
                            <div class="col-lg-4">
                                <div class="info-card mb-3">
                                    <div class="info-card-header">
                                        <i class="mdi mdi-information-outline"></i> Informasi Penilaian
                                    </div>
                                    <div class="info-card-body" id="content_utama">
                                        <!-- Info will be loaded here -->
                                    </div>
                                </div>

                                <div class="info-card">
                                    <div class="info-card-header">
                                        <i class="mdi mdi-calendar-check-outline"></i> Data Absensi
                                    </div>
                                    <div class="info-card-body">
                                        <div class="absensi-grid">
                                            <div class="absensi-item sakit">
                                                <div class="absensi-label">Sakit</div>
                                                <div class="absensi-value" id="absenSakit">0</div>
                                            </div>
                                            <div class="absensi-item telat">
                                                <div class="absensi-label">Telat</div>
                                                <div class="absensi-value" id="absenTelat">0</div>
                                            </div>
                                            <div class="absensi-item izin">
                                                <div class="absensi-label">Izin</div>
                                                <div class="absensi-value" id="absenIzin">0</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Right Column: Main Table -->
                            <div class="col-lg-8">
                                <div class="content-card">
                                    <div class="card-body">
                                        <h5 class="card-title">
                                            <i class="mdi mdi-table-large"></i> Detail Penilaian
                                        </h5>

                                        <!-- Tab Navigation -->
                                        <div class="modern-tab-nav" id="jenis-penilaian-tab" role="tablist">
                                            <!-- Tabs will be loaded via AJAX -->
                                        </div>

                                        <!-- Table -->
                                        <div class="scrollable-table-wrapper">
                                            <table class="table modern-table" id="table-fixed">
                                                <thead>
                                                    <tr>
                                                        <th style="width: 20%;">Kriteria</th>
                                                        <th style="width: 20%;">Sub Kriteria</th>
                                                        <th style="width: 10%;">Bobot</th>
                                                        <th style="width: 12%;">Nilai</th>
                                                        <th style="width: 15%;">Rata-Rata</th>
                                                        <th style="width: 13%;">Total</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="body_content">
                                                    <tr>
                                                        <td colspan="6" class="text-center py-5 text-muted">
                                                            <i class="mdi mdi-loading mdi-spin fa-2x mb-2 d-block"></i>
                                                            Memuat data...
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
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

    @push('styles')
        <style>
            .user-accordion-item {
                background: rgba(255, 255, 255, 0.12);
                backdrop-filter: blur(12px);
                -webkit-backdrop-filter: blur(12px);
                border: 1px solid rgba(255, 255, 255, 0.2);
                border-radius: 12px;
                box-shadow: 0 8px 32px rgba(0, 0, 0, 0.08);
                margin-bottom: 12px;
                overflow: hidden;
                transition: all 0.3s ease;
            }

            .user-accordion-item:hover {
                box-shadow: 0 12px 40px rgba(0, 0, 0, 0.12);
            }

            .user-accordion-header {
                padding: 1rem 1.25rem;
                cursor: pointer;
                display: flex;
                align-items: center;
                justify-content: space-between;
                background: transparent;
                transition: background 0.2s ease;
                user-select: none;
            }

            .user-accordion-header:hover {
                background: rgba(78, 115, 223, 0.05);
            }

            .user-accordion-header .user-number {
                width: 42px;
                height: 42px;
                border-radius: 50%;
                background: linear-gradient(135deg, #4e73df, #224abe);
                color: #fff;
                display: flex;
                align-items: center;
                justify-content: center;
                font-weight: 700;
                font-size: 1rem;
                margin-right: 1rem;
                flex-shrink: 0;
                box-shadow: 0 4px 12px rgba(78, 115, 223, 0.3);
            }

            .user-accordion-header .user-info-section {
                flex: 1;
                min-width: 0;
            }

            .user-accordion-header .user-name {
                font-weight: 700;
                font-size: 0.95rem;
                color: #2d3748;
                margin-bottom: 2px;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }

            .user-accordion-header .user-meta {
                font-size: 0.78rem;
                color: #718096;
            }

            .user-accordion-header .user-meta span {
                margin-right: 0.75rem;
            }

            .user-accordion-header .user-meta i {
                margin-right: 3px;
            }

            .user-accordion-header .quick-stats {
                display: flex;
                gap: 0.5rem;
                margin-right: 1rem;
                flex-shrink: 0;
            }

            .user-accordion-header .quick-stats .mini-stat {
                text-align: center;
                padding: 0.25rem 0.75rem;
                border-radius: 8px;
                background: rgba(78, 115, 223, 0.08);
                min-width: 70px;
            }

            .user-accordion-header .quick-stats .mini-stat .label {
                font-size: 0.65rem;
                color: #718096;
                text-transform: uppercase;
                font-weight: 600;
                letter-spacing: 0.5px;
            }

            .user-accordion-header .quick-stats .mini-stat .value {
                font-size: 0.95rem;
                font-weight: 700;
                color: #2d3748;
            }

            .user-accordion-header .toggle-icon {
                transition: transform 0.3s ease;
                color: #4e73df;
                flex-shrink: 0;
            }

            .user-accordion-header.active .toggle-icon {
                transform: rotate(180deg);
            }

            .user-accordion-body {
                max-height: 0;
                overflow: hidden;
                transition: max-height 0.4s ease, padding 0.3s ease;
                padding: 0 1.25rem;
                border-top: 0px solid transparent;
            }

            .user-accordion-body.show {
                max-height: 2000px;
                padding: 1.25rem;
                border-top: 1px solid rgba(0, 0, 0, 0.05);
            }

            .section-card {
                background: rgba(255, 255, 255, 0.6);
                border-radius: 10px;
                padding: 1.25rem;
                border: 1px solid rgba(0, 0, 0, 0.05);
                height: 100%;
            }

            .section-card .section-title {
                font-size: 0.75rem;
                text-transform: uppercase;
                font-weight: 700;
                color: #718096;
                letter-spacing: 1px;
                margin-bottom: 1rem;
                display: flex;
                align-items: center;
                gap: 0.5rem;
            }

            .section-card .section-title i {
                font-size: 1.1rem;
            }

            .section-card .score-display {
                text-align: center;
                padding: 1rem 0;
            }

            .section-card .score-display .big-score {
                font-size: 2.5rem;
                font-weight: 800;
                line-height: 1;
                margin-bottom: 0.5rem;
            }

            .section-card .score-display .grade-badge {
                display: inline-block;
                padding: 0.3rem 0.9rem;
                border-radius: 20px;
                font-size: 0.75rem;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }

            .score-kpi {
                color: #4e73df;
            }

            .score-360 {
                color: #1cc88a;
            }

            .grade-sangat-baik {
                background: #d4edda;
                color: #155724;
            }

            .grade-baik {
                background: #d1ecf1;
                color: #0c5460;
            }

            .grade-cukup {
                background: #fff3cd;
                color: #856404;
            }

            .grade-kurang {
                background: #f8d7da;
                color: #721c24;
            }

            .grade-sangat-kurang {
                background: #f5c6cb;
                color: #721c24;
            }

            .detail-scroll-container {
                max-height: 400px;
                overflow-y: auto;
                padding-right: 0.5rem;
            }

            .detail-scroll-container::-webkit-scrollbar {
                width: 6px;
            }

            .detail-scroll-container::-webkit-scrollbar-track {
                background: rgba(0, 0, 0, 0.05);
                border-radius: 10px;
            }

            .detail-scroll-container::-webkit-scrollbar-thumb {
                background: rgba(78, 115, 223, 0.3);
                border-radius: 10px;
            }

            .detail-scroll-container::-webkit-scrollbar-thumb:hover {
                background: rgba(78, 115, 223, 0.5);
            }

            .detail-item {
                background: rgba(255, 255, 255, 0.8);
                border-radius: 8px;
                padding: 1rem;
                margin-bottom: 0.75rem;
                border: 1px solid rgba(0, 0, 0, 0.05);
                transition: all 0.2s ease;
            }

            .detail-item:hover {
                background: rgba(255, 255, 255, 0.95);
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            }

            .detail-item:last-child {
                margin-bottom: 0;
            }

            .detail-item .detail-header {
                display: flex;
                justify-content: space-between;
                align-items: flex-start;
                margin-bottom: 0.75rem;
            }

            .detail-item .detail-title {
                font-weight: 600;
                font-size: 0.9rem;
                color: #2d3748;
                margin-bottom: 0.25rem;
                flex: 1;
            }

            .detail-item .detail-subtitle {
                font-size: 0.75rem;
                color: #718096;
            }

            .detail-item .detail-chart {
                width: 80px;
                height: 80px;
                flex-shrink: 0;
                margin-left: 1rem;
            }

            .detail-item .detail-footer {
                display: flex;
                justify-content: space-between;
                align-items: center;
                font-size: 0.8rem;
                color: #718096;
                padding-top: 0.5rem;
                border-top: 1px solid rgba(0, 0, 0, 0.05);
            }

            .detail-item .detail-footer .status-badge {
                padding: 0.2rem 0.6rem;
                border-radius: 12px;
                font-size: 0.7rem;
                font-weight: 600;
            }

            .status-selesai {
                background: #d4edda;
                color: #155724;
            }

            .status-aktif {
                background: #fff3cd;
                color: #856404;
            }

            .status-belum {
                background: #e2e3e5;
                color: #383d41;
            }

            /* Button Detail 360 */
            .btn-detail-360 {
                background: linear-gradient(135deg, #1cc88a, #17a673);
                color: white;
                border: none;
                padding: 0.5rem 1rem;
                border-radius: 8px;
                font-size: 0.8rem;
                font-weight: 600;
                display: inline-flex;
                align-items: center;
                gap: 0.4rem;
                transition: all 0.2s ease;
                box-shadow: 0 4px 12px rgba(28, 200, 138, 0.3);
                margin-top: 1rem;
                width: 100%;
                justify-content: center;
            }

            .btn-detail-360:hover {
                transform: translateY(-2px);
                box-shadow: 0 6px 20px rgba(28, 200, 138, 0.4);
                color: white;
            }

            .btn-detail-360 i {
                font-size: 1rem;
            }

            /* Modal Styles */
            /* Modal Tab Styles */
            .info-card {
                background: white;
                border-radius: 12px;
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
                overflow: hidden;
                border: 1px solid #e3e6f0;
            }

            .info-card-header {
                background: linear-gradient(135deg, #4e73df, #224abe);
                color: white;
                padding: 0.85rem 1.25rem;
                font-weight: 700;
                font-size: 0.9rem;
                display: flex;
                align-items: center;
                gap: 0.5rem;
            }

            .info-card-body {
                padding: 1.25rem;
            }

            .info-item {
                margin-bottom: 1rem;
            }

            .info-label {
                font-size: 0.75rem;
                text-transform: uppercase;
                font-weight: 700;
                color: #718096;
                letter-spacing: 0.5px;
                margin-bottom: 0.35rem;
            }

            .info-value {
                font-size: 0.9rem;
                font-weight: 600;
                color: #2d3748;
            }

            .evaluator-list {
                list-style: none;
                padding: 0;
                margin: 0;
                max-height: 200px;
                overflow-y: auto;
            }

            .evaluator-list li {
                padding: 0.5rem 0.75rem;
                background: #f8f9fc;
                border-radius: 6px;
                margin-bottom: 0.35rem;
                font-size: 0.85rem;
                cursor: pointer;
                transition: all 0.2s ease;
            }

            .evaluator-list li:hover {
                background: #e3e6f0;
                transform: translateX(4px);
            }

            .absensi-grid {
                display: grid;
                grid-template-columns: repeat(3, 1fr);
                gap: 0.75rem;
            }

            .absensi-item {
                text-align: center;
                padding: 1rem 0.5rem;
                border-radius: 8px;
                background: #f8f9fc;
            }

            .absensi-item.sakit {
                background: #fff3cd;
            }

            .absensi-item.telat {
                background: #f8d7da;
            }

            .absensi-item.izin {
                background: #d1ecf1;
            }

            .absensi-label {
                font-size: 0.7rem;
                text-transform: uppercase;
                font-weight: 700;
                color: #718096;
                margin-bottom: 0.25rem;
            }

            .absensi-value {
                font-size: 1.5rem;
                font-weight: 800;
                color: #2d3748;
            }

            .content-card {
                background: white;
                border-radius: 12px;
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
                border: 1px solid #e3e6f0;
            }

            .content-card .card-body {
                padding: 1.5rem;
            }

            .content-card .card-title {
                font-size: 1.1rem;
                font-weight: 700;
                color: #2d3748;
                margin-bottom: 1rem;
                display: flex;
                align-items: center;
                gap: 0.5rem;
            }

            /* Modern Tab Navigation */
            .modern-tab-nav {
                display: flex;
                flex-wrap: wrap;
                gap: 0.5rem;
                margin-bottom: 1.25rem;
                padding: 0.5rem;
                background: #f8f9fc;
                border-radius: 10px;
            }

            .modern-tab-btn {
                padding: 0.5rem 1rem;
                border-radius: 8px;
                font-size: 0.8rem;
                font-weight: 600;
                color: #718096;
                cursor: pointer;
                transition: all 0.2s ease;
                border: 1px solid transparent;
                background: white;
            }

            .modern-tab-btn:hover {
                background: #e3e6f0;
                color: #4e73df;
            }

            .modern-tab-btn.active-tab {
                background: linear-gradient(135deg, #4e73df, #224abe);
                color: white;
                box-shadow: 0 4px 12px rgba(78, 115, 223, 0.3);
            }

            /* Modern Table */
            .scrollable-table-wrapper {
                max-height: 500px;
                overflow-y: auto;
                border-radius: 8px;
                border: 1px solid #e3e6f0;
            }

            .modern-table {
                margin-bottom: 0;
                font-size: 0.85rem;
            }

            .modern-table thead {
                background: linear-gradient(135deg, #4e73df, #224abe);
                color: white;
                position: sticky;
                top: 0;
                z-index: 10;
            }

            .modern-table thead th {
                padding: 0.85rem 0.75rem;
                font-weight: 700;
                text-transform: uppercase;
                font-size: 0.7rem;
                letter-spacing: 0.5px;
                border: none;
            }

            .modern-table tbody tr {
                border-bottom: 1px solid #f1f1f1;
            }

            .modern-table tbody tr:hover {
                background: #f8f9fc;
            }

            .modern-table tbody td {
                padding: 0.65rem 0.75rem;
                vertical-align: middle;
            }

            .modern-table .evaluator-header {
                background: #e3e6f0 !important;
                font-weight: 700;
                color: #2d3748;
            }

            .modern-table .total-row {
                background: #fff3cd !important;
                font-weight: 700;
            }

            .modern-table .grand-total-row {
                background: linear-gradient(135deg, #d4edda, #c3e6cb) !important;
                font-weight: 800;
                font-size: 0.95rem;
            }

            .read-more-btn {
                font-size: 0.75rem;
                padding: 0;
                text-decoration: none;
            }

            /* Scrollbar Styling */
            .scrollable-table-wrapper::-webkit-scrollbar {
                width: 8px;
            }

            .scrollable-table-wrapper::-webkit-scrollbar-track {
                background: #f1f1f1;
                border-radius: 10px;
            }

            .scrollable-table-wrapper::-webkit-scrollbar-thumb {
                background: #4e73df;
                border-radius: 10px;
            }

            .scrollable-table-wrapper::-webkit-scrollbar-thumb:hover {
                background: #224abe;
            }

            .evaluator-list::-webkit-scrollbar {
                width: 6px;
            }

            .evaluator-list::-webkit-scrollbar-track {
                background: #f1f1f1;
                border-radius: 10px;
            }

            .evaluator-list::-webkit-scrollbar-thumb {
                background: #4e73df;
                border-radius: 10px;
            }

            @media (max-width: 992px) {
                .absensi-grid {
                    grid-template-columns: 1fr;
                }
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            $(document).ready(function () {
                loadData();

                $('#filterForm').on('submit', function (e) {
                    e.preventDefault();
                    loadData();
                });

                $('#btnExpandAll').on('click', function () {
                    $('.user-accordion-body').addClass('show');
                    $('.user-accordion-header').addClass('active');
                    setTimeout(() => {
                        initializeAllCharts();
                    }, 300);
                });

                $('#btnCollapseAll').on('click', function () {
                    $('.user-accordion-body').removeClass('show');
                    $('.user-accordion-header').removeClass('active');
                });

                function loadData() {
                    const formData = $('#filterForm').serialize();
                    $('#accordionContainer').html(`
                                    <div class="text-center py-5">
                                        <div class="spinner-border text-primary" role="status"></div>
                                        <p class="mt-2 text-muted">Memuat data...</p>
                                    </div>
                                `);

                    $.ajax({
                        url: "{{ route('HR.performance.dashboard.data') }}",
                        type: 'GET',
                        data: formData,
                        success: function (res) {
                            window.dashboardData = res;
                            renderAccordion(res.users);
                            $('#totalUsers').text(res.total || 0);
                        },
                        error: function () {
                            $('#accordionContainer').html('<div class="alert alert-danger glass-force">Gagal memuat data.</div>');
                        }
                    });
                }

                function renderAccordion(users) {
                    if (!users || users.length === 0) {
                        $('#accordionContainer').html('<div class="alert alert-info glass-force">Tidak ada data ditemukan untuk filter ini.</div>');
                        return;
                    }

                    let html = '<div id="userAccordionList">';

                    users.forEach((user, index) => {
                        const nomor = index + 1;
                        const itemId = `user_acc_${user.id}`;

                        html += `
                                        <div class="user-accordion-item" data-user-id="${user.id}">
                                            <div class="user-accordion-header" data-target="#${itemId}">
                                                <div class="d-flex align-items-center flex-grow-1" style="min-width: 0;">
                                                    <div class="user-number">${nomor}</div>
                                                    <div class="user-info-section">
                                                        <div class="user-name">${user.nama}</div>
                                                        <div class="user-meta">
                                                            <span><i class="mdi mdi-briefcase-outline"></i>${user.jabatan}</span>
                                                            <span><i class="mdi mdi-office-building-outline"></i>${user.divisi}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="quick-stats d-none d-md-flex">
                                                    <div class="mini-stat">
                                                        <div class="label">KPI</div>
                                                        <div class="value score-kpi">${user.kpi.score}%</div>
                                                    </div>
                                                    <div class="mini-stat">
                                                        <div class="label">360°</div>
                                                        <div class="value score-360">${user.assessment_360.score}%</div>
                                                    </div>
                                                </div>
                                                <i class="mdi mdi-chevron-down toggle-icon" style="font-size: 1.5rem;"></i>
                                            </div>
                                            <div class="user-accordion-body" id="${itemId}">
                                                <div class="row g-3">
                                                    <!-- KPI Section -->
                                                    <div class="col-md-6">
                                                        <div class="section-card">
                                                            <div class="section-title">
                                                                <i class="mdi mdi-chart-timeline-variant-shimmer text-primary"></i>
                                                                Progress KPI
                                                            </div>
                                                            <div class="score-display">
                                                                <div class="big-score score-kpi">${user.kpi.score}%</div>
                                                                <span class="grade-badge ${getGradeClass(user.kpi.grade)}">${user.kpi.grade}</span>
                                                            </div>
                                                            <div class="progress mt-3" style="height: 8px;">
                                                                <div class="progress-bar bg-primary" role="progressbar" 
                                                                     style="width: ${Math.min(user.kpi.score, 100)}%"></div>
                                                            </div>

                                                            <div class="mt-4">
                                                                <h6 class="fw-bold text-muted small mb-3">Detail Target KPI (${user.kpi.details.length})</h6>
                                                                <div class="detail-scroll-container" id="kpi-details-${user.id}">
                                                                    ${renderKPIDetails(user.kpi.details, user.id)}
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Assessment 360 Section -->
                                                    <div class="col-md-6">
                                                        <div class="section-card">
                                                            <div class="section-title">
                                                                <i class="mdi mdi-account-check-outline text-success"></i>
                                                                Penilaian 360°
                                                            </div>
                                                            <div class="score-display">
                                                                <div class="big-score score-360">${user.assessment_360.score}%</div>
                                                                <span class="grade-badge ${getGradeClass(user.assessment_360.grade)}">${user.assessment_360.grade}</span>
                                                            </div>
                                                            <div class="progress mt-3" style="height: 8px;">
                                                                <div class="progress-bar bg-success" role="progressbar" 
                                                                     style="width: ${Math.min(user.assessment_360.score, 100)}%"></div>
                                                            </div>

                                                            <div class="mt-4">
                                                                <h6 class="fw-bold text-muted small mb-3">Detail Penilaian (${user.assessment_360.details.length})</h6>
                                                                <div class="detail-scroll-container" id="assessment-360-details-${user.id}">
                                                                    ${renderAssessment360Details(user.assessment_360.details, user.id)}
                                                                </div>
                                                            </div>

                                                            <!-- Tombol Detail 360 -->
                                                            <button class="btn-detail-360" onclick="openAssessment360Modal(${user.id}, '${user.nama.replace(/'/g, "\\'")}')">
                                                                <i class="mdi mdi-file-document-outline"></i>
                                                                Lihat Detail Penilaian 360°
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    `;
                    });

                    html += '</div>';
                    $('#accordionContainer').html(html);
                    bindAccordionEvents();
                }

                function renderKPIDetails(details, userId) {
                    if (!details || details.length === 0) {
                        return '<p class="text-muted text-center py-3">Belum ada target KPI</p>';
                    }

                    let html = '';
                    details.forEach((detail, idx) => {
                        const chartId = `kpi-chart-${userId}-${idx}`;
                        const statusClass = detail.status === 'Selesai' ? 'status-selesai' :
                            (detail.status === 'Aktif' ? 'status-aktif' : 'status-belum');

                        html += `
                                        <div class="detail-item">
                                            <div class="detail-header">
                                                <div style="flex: 1;">
                                                    <div class="detail-title">${detail.judul}</div>
                                                    <div class="detail-subtitle">${detail.asistant_route}</div>
                                                </div>
                                                <div class="detail-chart" id="${chartId}"></div>
                                            </div>
                                            <div class="detail-footer">
                                                <span>Target: ${formatTarget(detail.nilai_target, detail.tipe_target)}</span>
                                                <span class="status-badge ${statusClass}">${detail.status}</span>
                                            </div>
                                        </div>
                                    `;
                    });

                    return html;
                }

                // Global variables for modal
                let modalEvaluators = [];
                let modalKriteria = [];
                let modalEvaluated = {};
                let modalChart = {};
                let modalAbsensi = {};

                // Fungsi untuk membuka modal detail 360
                window.openAssessment360Modal = function (userId, userName) {
                    const tahun = $('#filterTahun').val();

                    $('#modalUserName').text(`Detail Penilaian 360° - ${userName}`);
                    $('#modalUserMeta').text(`Periode Tahun ${tahun}`);
                    $('#modalUserFoto').attr('src', "{{ asset('assets/img/avatars/1.png') }}");

                    $('#modalLoading').show();
                    $('#modalContent').hide().html('');

                    const modal = new bootstrap.Modal(document.getElementById('modalAssessment360Detail'));
                    modal.show();

                    $.ajax({
                        url: "{{ route('HR.performance.dashboard.assessment360.detailTab') }}",
                        type: 'GET',
                        data: {
                            id_karyawan: userId,
                            tahun: tahun
                        },
                        success: function (res) {
                            $('#modalLoading').hide();
                            if (res.success) {
                                $('#modalUserFoto').attr('src', res.karyawan.foto);
                                $('#modalUserMeta').text(`${res.karyawan.jabatan} • ${res.karyawan.divisi} • Periode Tahun ${tahun}`);

                                const data = res.data[0];
                                modalEvaluators = data.data.evaluator;
                                modalKriteria = data.data.dataKriteria;
                                modalEvaluated = data.evaluated;
                                modalChart = data.chart;
                                modalAbsensi = data.dataAbsen;

                                renderModalTabContent();
                            } else {
                                $('#modalContent').html(`<div class="alert alert-warning">${res.message || 'Data tidak ditemukan'}</div>`).show();
                            }
                        },
                        error: function (xhr) {
                            $('#modalLoading').hide();
                            let message = 'Gagal memuat data';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                message = xhr.responseJSON.message;
                            }
                            $('#modalContent').html(`<div class="alert alert-warning">${message}</div>`).show();
                        }
                    });
                };

                function renderModalTabContent() {
                    // Render info card
                    let contentUtama = $('#content_utama');
                    contentUtama.empty();

                    // Render absensi
                    $('#absenSakit').text(modalAbsensi.sakit || 0);
                    $('#absenTelat').text(modalAbsensi.telat || 0);
                    $('#absenIzin').text(modalAbsensi.izin || 0);

                    // Get unique jenis penilaian
                    const jenisList = [...new Set(modalEvaluators.map(ev => ev.jenis_penilaian))];

                    // Build tab navigation
                    let jenisHTML = `
                <a class="modern-tab-btn active-tab" data-jenis="all">
                    <i class="mdi mdi-layers me-1"></i> Semua
                </a>
            `;

                    jenisList.forEach(jenis => {
                        let label, icon;
                        if (jenis === 'General Manager') {
                            label = 'General Manager';
                            icon = 'mdi-crown';
                        } else if (jenis === 'Manager/SPV/Team Leader (Atasan Langsung)') {
                            label = 'Koordinator';
                            icon = 'mdi-account-tie';
                        } else if (jenis === 'Rekan Kerja (Satu Divisi)') {
                            label = 'Satu Divisi';
                            icon = 'mdi-account-group';
                        } else if (jenis === 'Pekerja (Beda Divisi)') {
                            label = 'Beda Divisi';
                            icon = 'mdi-account-switch';
                        } else if (jenis === 'Self Apprisial') {
                            label = 'Self Appraisal';
                            icon = 'mdi-account-check';
                        } else {
                            label = jenis;
                            icon = 'mdi-account';
                        }

                        jenisHTML += `
                    <a class="modern-tab-btn" data-jenis="${jenis}">
                        <i class="mdi ${icon} me-1"></i> ${label}
                    </a>
                `;
                    });

                    $('#jenis-penilaian-tab').html(jenisHTML);

                    // Build evaluator list
                    let listEvaluatorHTML = modalEvaluators.map(ev =>
                        `<li data-target="${ev.nama}-${ev.jenis_penilaian}" class="list-group-item">
                    <i class="mdi mdi-account-circle me-2 text-primary"></i>${ev.nama}
                </li>`
                    ).join('');

                    contentUtama.append(`
                <div class="info-item">
                    <div class="info-label"><i class="mdi mdi-file-document-outline me-1"></i> Yang Dinilai</div>
                    <div class="info-value">${modalEvaluated.nama}</div>
                </div>
                <div class="info-item">
                    <div class="info-label"><i class="mdi mdi-account-multiple me-1"></i> Evaluator (${modalEvaluators.length})</div>
                    <ul class="evaluator-list">${listEvaluatorHTML}</ul>
                </div>
                <div class="row g-2">
                    <div class="col-6">
                        <div class="info-item mb-0">
                            <div class="info-label"><i class="mdi mdi-calendar me-1"></i> Periode</div>
                            <div class="info-value text-center">${modalEvaluated.quartal}</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="info-item mb-0">
                            <div class="info-label"><i class="mdi mdi-calendar-range me-1"></i> Tahun</div>
                            <div class="info-value text-center">${modalEvaluated.tahun}</div>
                        </div>
                    </div>
                </div>
                ${modalEvaluated.catatan && modalEvaluated.catatan !== '-' ? `
                    <hr class="my-3">
                    <div class="info-item mb-0">
                        <div class="info-label"><i class="mdi mdi-note-text me-1"></i> Catatan</div>
                        <div class="info-value" style="font-size: 0.85rem; font-weight: 400; color: #4a5568;">
                            ${modalEvaluated.catatan}
                        </div>
                    </div>
                ` : ''}
            `);

                    // Render table with 'all' filter
                    renderTabelModal('all');

                    $('#modalContent').show();
                }

                // Tab click handler
                $('#jenis-penilaian-tab').on('click', '.modern-tab-btn', function () {
                    $('#jenis-penilaian-tab .modern-tab-btn').removeClass('active-tab');
                    $(this).addClass('active-tab');
                    const jenis = $(this).data('jenis');
                    renderTabelModal(jenis);
                });

                function pengubahFormatModal(angka) {
                    return angka.toLocaleString('id-ID', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    });
                }

                function renderTabelModal(filterJenis) {
                    let content = $('#body_content');
                    content.empty();

                    const persentaseJenis = {
                        'General Manager': 35,
                        'Manager/SPV/Team Leader (Atasan Langsung)': 30,
                        'Rekan Kerja (Satu Divisi)': 20,
                        'Pekerja (Beda Divisi)': 10,
                        'Self Apprisial': 5
                    };

                    let groupRata2 = {};
                    let filteredEvaluators = filterJenis === 'all'
                        ? modalEvaluators
                        : modalEvaluators.filter(ev => ev.jenis_penilaian === filterJenis);

                    if (filteredEvaluators.length === 0) {
                        content.append(
                            `<tr><td colspan="6" class="text-center py-5 text-muted">
                        <i class="mdi mdi-inbox fa-2x mb-2 d-block"></i>Tidak Ada Data
                    </td></tr>`
                        );
                        return;
                    }

                    // Calculate rata-rata per sub-kriteria
                    filteredEvaluators.forEach(evaluator => {
                        let nilaiList = evaluator.nilai;
                        let nilaiIndex = 0;
                        modalKriteria.forEach(kriteria => {
                            kriteria.detailKriteria.forEach(sub => {
                                const nilaiItem = nilaiList[nilaiIndex++] || { nilai: '-', pesan: '-' };
                                const nilai = parseFloat(nilaiItem.nilai);
                                if (sub.tipe_input !== 'textarea' && !isNaN(nilai)) {
                                    groupRata2[evaluator.jenis_penilaian] = groupRata2[evaluator.jenis_penilaian] || {};
                                    groupRata2[evaluator.jenis_penilaian][kriteria.kriteria] = groupRata2[evaluator.jenis_penilaian][kriteria.kriteria] || {};
                                    groupRata2[evaluator.jenis_penilaian][kriteria.kriteria][sub.sub_kriteria] =
                                        groupRata2[evaluator.jenis_penilaian][kriteria.kriteria][sub.sub_kriteria] || [];
                                    groupRata2[evaluator.jenis_penilaian][kriteria.kriteria][sub.sub_kriteria].push(nilai);
                                }
                            });
                        });
                    });

                    let rata2Hasil = {};
                    for (const jenis in groupRata2) {
                        rata2Hasil[jenis] = {};
                        for (const kriteria in groupRata2[jenis]) {
                            rata2Hasil[jenis][kriteria] = {};
                            for (const sub in groupRata2[jenis][kriteria]) {
                                let arr = groupRata2[jenis][kriteria][sub];
                                let avg = arr.reduce((a, b) => a + b, 0) / arr.length;
                                rata2Hasil[jenis][kriteria][sub] = avg;
                            }
                        }
                    }

                    let jenisTotalRaw = {};

                    filteredEvaluators.forEach(evaluator => {
                        content.append(`
                    <tr id="${evaluator.nama}-${evaluator.jenis_penilaian}" class="evaluator-header">
                        <td colspan="6">
                            <i class="mdi mdi-account me-2"></i>${evaluator.nama} 
                            <span class="badge bg-primary bg-opacity-10 text-primary ms-2" style="font-size: .75rem;">${evaluator.jenis_penilaian}</span>
                        </td>
                    </tr>
                `);

                        let nilaiList = evaluator.nilai;
                        let nilaiIndex = 0;
                        let totalSkorEvaluator = 0;

                        modalKriteria.forEach(kriteria => {
                            const subKriteriaList = kriteria.detailKriteria;
                            subKriteriaList.forEach((sub, idxSub) => {
                                const nilaiItem = nilaiList[nilaiIndex++] || { nilai: '-', pesan: '-' };
                                const nilai = nilaiItem.nilai;
                                const pesan = nilaiItem.pesan;
                                const tipe = sub.tipe_input;
                                const bobot = parseFloat(sub.bobot);
                                let dataNilai = '';

                                if (tipe === 'textarea') {
                                    dataNilai = `<td colspan="4" style="font-style: italic; color: #64748b;">${pesan && pesan.trim() !== '' ? pesan : '-'}</td>`;
                                } else {
                                    const nilaiAngka = parseFloat(nilai);
                                    const rataData = rata2Hasil[evaluator.jenis_penilaian]?.[kriteria.kriteria]?.[sub.sub_kriteria];
                                    let rata = '-';
                                    let skor = 0;
                                    if (!isNaN(nilaiAngka)) {
                                        rata = rataData !== undefined ? rataData : nilaiAngka;
                                        skor = (rata * bobot) / 100;
                                        totalSkorEvaluator += skor;
                                    }
                                    dataNilai = `
                                <td><span class="badge bg-light text-dark border">${bobot}%</span></td>
                                <td class="fw-semibold">${nilai}</td>
                                <td>${rata === '-' ? '-' : pengubahFormatModal(rata)}</td>
                                <td class="fw-bold text-primary">${rata === '-' ? '-' : pengubahFormatModal(skor)}</td>
                            `;
                                }

                                const kriteriaText = kriteria.kriteria;
                                const subKriteriaText = sub.sub_kriteria;
                                const isKriteriaLong = kriteriaText.length > 30;
                                const isSubKriteriaLong = subKriteriaText.length > 30;
                                const displayKriteria = isKriteriaLong ? kriteriaText.substring(0, 30) + '...' : kriteriaText;
                                const displaySubKriteria = isSubKriteriaLong ? subKriteriaText.substring(0, 30) + '...' : subKriteriaText;

                                content.append(`
                            <tr>
                                ${idxSub === 0 ? `<td class="text-left fw-semibold">${displayKriteria}</td>` : ''}
                                <td style="text-align: left;">${displaySubKriteria}</td>
                                ${dataNilai}
                            </tr>
                        `);
                            });
                        });

                        content.append(`
                    <tr class="total-row">
                        <td colspan="5" class="text-end">Total (${evaluator.nama})</td>
                        <td class="text-center">${pengubahFormatModal(totalSkorEvaluator)}</td>
                    </tr>
                `);

                        const jenis = evaluator.jenis_penilaian;
                        if (!jenisTotalRaw.hasOwnProperty(jenis)) {
                            jenisTotalRaw[jenis] = totalSkorEvaluator;
                        }
                    });

                    let jenisTotalPost = {};
                    for (const jenis in jenisTotalRaw) {
                        const persen = persentaseJenis[jenis] || 0;
                        jenisTotalPost[jenis] = (jenisTotalRaw[jenis] * persen) / 100;
                    }

                    let totalSemuaSkor = 0;
                    if (filterJenis === 'all') {
                        totalSemuaSkor = Object.values(jenisTotalPost).reduce((a, b) => a + b, 0);
                    } else {
                        totalSemuaSkor = jenisTotalPost[filterJenis] || 0;
                    }

                    let grade = '';
                    let keterangan = '';
                    if (totalSemuaSkor >= 90) {
                        grade = 'A';
                        keterangan = 'Sangat Baik';
                    } else if (totalSemuaSkor >= 80) {
                        grade = 'B';
                        keterangan = 'Baik';
                    } else if (totalSemuaSkor >= 70) {
                        grade = 'C';
                        keterangan = 'Cukup';
                    } else if (totalSemuaSkor >= 60) {
                        grade = 'D';
                        keterangan = 'Kurang';
                    } else {
                        grade = 'E';
                        keterangan = 'Sangat Kurang';
                    }

                    content.append(`
                <tr class="grand-total-row">
                    <td colspan="5" class="text-end">Total Semua Nilai</td>
                    <td class="text-center fs-5">${pengubahFormatModal(totalSemuaSkor)}</td>
                </tr>
                <tr class="grand-total-row">
                    <td colspan="5" class="text-end">Kriteria</td>
                    <td class="text-center">${keterangan}</td>
                </tr>
                <tr class="grand-total-row">
                    <td colspan="5" class="text-end">Grade</td>
                    <td class="text-center fs-4 fw-bold">${grade}</td>
                </tr>
            `);
                }

                // Evaluator list click to scroll
                $(document).on('click', '.evaluator-list .list-group-item', function () {
                    const targetId = $(this).data('target');
                    const evaluatorRow = document.getElementById(targetId);
                    if (evaluatorRow) {
                        const tableWrapper = $('.scrollable-table-wrapper');
                        const rowOffset = $(evaluatorRow).position().top;
                        tableWrapper.animate({
                            scrollTop: tableWrapper.scrollTop() + rowOffset - 50
                        }, 500);
                    }
                });

                window.toggleEvaluatorDetail = function (evalId) {
                    const $detail = $(`#${evalId}`);
                    const $icon = $(`#icon_${evalId}`);

                    $detail.toggleClass('show');
                    if ($detail.hasClass('show')) {
                        $icon.css('transform', 'rotate(180deg)');
                    } else {
                        $icon.css('transform', 'rotate(0deg)');
                    }
                };

                function bindAccordionEvents() {
                    $('.user-accordion-header').off('click').on('click', function () {
                        const targetId = $(this).data('target');
                        const $body = $(targetId);
                        const $header = $(this);

                        $body.toggleClass('show');
                        $header.toggleClass('active');

                        if ($body.hasClass('show')) {
                            setTimeout(() => {
                                initializeChartsInContainer($body);
                            }, 300);
                        }
                    });
                }

                function initializeAllCharts() {
                    $('.user-accordion-body.show').each(function () {
                        initializeChartsInContainer($(this));
                    });
                }

                function initializeChartsInContainer($container) {
                    $container.find('.detail-chart[id^="kpi-chart"]').each(function () {
                        const chartId = $(this).attr('id');
                        const parts = chartId.split('-');
                        const userId = parts[2];
                        const idx = parts[3];

                        if (!$(this).data('chart-initialized')) {
                            const user = window.dashboardData?.users?.find(u => u.id == userId);
                            if (user && user.kpi.details[idx]) {
                                createCircularChart(chartId, user.kpi.details[idx].progress, '#4e73df');
                                $(this).data('chart-initialized', true);
                            }
                        }
                    });

                    $container.find('.detail-chart[id^="assessment-chart"]').each(function () {
                        const chartId = $(this).attr('id');
                        const parts = chartId.split('-');
                        const userId = parts[2];
                        const idx = parts[3];

                        if (!$(this).data('chart-initialized')) {
                            const user = window.dashboardData?.users?.find(u => u.id == userId);
                            if (user && user.assessment_360.details[idx]) {
                                createCircularChart(chartId, user.assessment_360.details[idx].score, '#1cc88a');
                                $(this).data('chart-initialized', true);
                            }
                        }
                    });
                }

                function createCircularChart(elementId, value, color) {
                    const options = {
                        series: [value],
                        chart: {
                            height: 80,
                            type: 'radialBar',
                            sparkline: { enabled: true }
                        },
                        plotOptions: {
                            radialBar: {
                                startAngle: -90,
                                endAngle: 270,
                                hollow: { margin: 0, size: '70%' },
                                track: { background: '#e7e7e7', strokeWidth: '100%', margin: 0 },
                                dataLabels: {
                                    name: { show: false },
                                    value: {
                                        offsetY: -2,
                                        fontSize: '14px',
                                        fontWeight: 700,
                                        color: color
                                    }
                                }
                            }
                        },
                        fill: {
                            type: 'gradient',
                            gradient: {
                                shade: 'light',
                                shadeIntensity: 0.4,
                                inverseColors: false,
                                opacityFrom: 1,
                                opacityTo: 1,
                                stops: [0, 50, 53, 91]
                            }
                        },
                        colors: [color],
                        stroke: { lineCap: 'round' }
                    };

                    const chart = new ApexCharts(document.querySelector(`#${elementId}`), options);
                    chart.render();
                }

                function getGradeClass(grade) {
                    switch (grade) {
                        case 'Sangat Baik': return 'grade-sangat-baik';
                        case 'Baik': return 'grade-baik';
                        case 'Cukup': return 'grade-cukup';
                        case 'Kurang': return 'grade-kurang';
                        case 'Sangat Kurang': return 'grade-sangat-kurang';
                        default: return 'grade-cukup';
                    }
                }

                function formatTarget(value, type) {
                    if (type === 'rupiah') {
                        return 'Rp ' + new Intl.NumberFormat('id-ID').format(value);
                    } else if (type === 'persen') {
                        return value + '%';
                    }
                    return new Intl.NumberFormat('id-ID').format(value);
                }
            });
        </script>
    @endpush
@endsection