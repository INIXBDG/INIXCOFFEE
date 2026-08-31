@extends('layouts.app')

@section('content')
    <div class="container-fluid py-4 ks-page">
        <div class="d-flex flex-wrap align-items-end justify-content-between gap-2 mb-3">
            <div>
                <div class="text-uppercase text-muted small fw-bold" style="letter-spacing:.05em;">Jadwal &amp; Kebutuhan Ruang</div>
                <h1 class="h4 fw-bold mb-0 mt-1">Kelas Setting</h1>
            </div>
            <div class="d-flex flex-wrap align-items-center gap-2">
                <div class="container text-center">
                    <div class="row">
                        <div class="col">
                            <label for="ksViewMode" class="small text-muted fw-semibold mb-0">Tampilan</label>
                            <select id="ksViewMode" class="form-select form-select-sm ks-view-select">
                                <option value="month">Per Bulan</option>
                                <option value="month2">Per 2 Bulan</option>
                            </select>
                        </div>
                        <div class="col">
                            <label for="ksPeriod" class="small text-muted fw-semibold mb-0">Periode</label>
                            <select id="ksPeriod" class="form-select form-select-sm ks-period-select"></select>
                        </div>
                        <div class="col">
                            <label for="ksSort" class="small text-muted fw-semibold mb-0">Urutkan</label>
                            <select id="ksSort" class="form-select form-select-sm ks-sort-select">
                                <option value="dari_asc">Tanggal (Terdekat)</option>
                                <option value="dari_desc">Tanggal (Terjauh)</option>
                                <option value="kelas_asc">Kelas (A-Z)</option>
                                <option value="kelas_desc">Kelas (Z-A)</option>
                                <option value="instruktur_asc">Instruktur (A-Z)</option>
                                <option value="status_asc">Status (Hitam→Merah)</option>
                            </select>
                        </div>
                        <div class="col">
                            <div class="ks-col-dropdown">
                                <label for="ksColBtn" class="small text-muted fw-semibold mb-0">Entry</label>
                                <button type="button" class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-1" id="ksColBtn">
                                    <i class="bi bi-sliders"></i> Kolom
                                </button>
                                <div class="ks-col-menu" id="ksColMenu"></div>
                            </div>
                        </div>
                        <div class="col">
                            @if(in_array(Auth()->user()->jabatan ?? '', ['Programmer']))
                                <button type="button" class="btn btn-danger" style="height: 37px" data-bs-toggle="modal" data-bs-target="#modalClearDatabase">
                                    <i class="bi bi-trash3"></i> Reset Database
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
            <div class="ks-search-wrap">
                <i class="bi bi-search"></i>
                <input type="text" id="ksSearch" class="form-control form-control-sm" placeholder="Cari kelas, instruktur, atau ruangan...">
            </div>
        </div>

        <div id="ksTablesContainer"></div>

        <div class="ks-hint text-muted small mt-2">
            <i class="bi bi-cursor me-1"></i>Klik sel mana pun untuk mengedit langsung &middot;
            <i class="bi bi-arrow-left-right me-1 ms-1"></i>geser tabel untuk melihat kolom lainnya
        </div>
    </div>

    <div class="ks-toast-container" id="ksToastContainer"></div>

    <div class="modal fade" id="modalClearDatabase" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="bi bi-exclamation-triangle-fill me-2"></i>PERINGATAN: Reset Database</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-danger fw-bold">Tindakan ini tidak dapat dibatalkan!</p>
                    <p>Semua pengaturan kustom (Ruangan, Device, Status, Asset, dll) pada tabel <strong>Kelas Setting</strong> akan dihapus permanen.</p>
                    <p class="text-muted small">*Data asli di tabel RKM <strong>tidak akan</strong> terhapus.</p>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Ketik <span class="text-danger">HAPUS</span> untuk mengonfirmasi:</label>
                        <input type="text" id="confirmResetText" class="form-control" placeholder="Ketik HAPUS di sini..." autocomplete="off">
                        <div id="resetError" class="text-danger small mt-1 d-none">Teks konfirmasi tidak sesuai!</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" id="btnExecuteReset" class="btn btn-danger" disabled>
                        <i class="bi bi-trash3 me-1"></i> Ya, Hapus Semua Data
                    </button>
                </div>
            </div>
        </div>
    </div>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
        .ks-page { max-width: 100%; }
        @media (prefers-reduced-motion: reduce) { *, *::before, *::after { animation-duration: .001ms !important; animation-iteration-count: 1 !important; transition-duration: .001ms !important; } }
        button:focus-visible, select:focus-visible, input:focus-visible, textarea:focus-visible { outline: 2px solid #4F46E5; outline-offset: 2px; }
        .ks-view-select, .ks-period-select, .ks-sort-select { min-width: 150px; }
        .ks-period-select { min-width: 190px; }
        .ks-sort-select { min-width: 180px; }
        .ks-search-wrap { position: relative; min-width: 260px; }
        .ks-search-wrap i { position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: #9CA3AF; font-size: 12px; }
        .ks-search-wrap input { padding-left: 30px; }
        .ks-col-dropdown { position: relative; }
        .ks-col-menu { display: none; position: absolute; top: 100%; right: 0; margin-top: 6px; width: 220px; background: #fff; border: 1px solid #E3E7ED; border-radius: 10px; box-shadow: 0 8px 24px rgba(20, 24, 32, .14); z-index: 70; padding: 8px; animation: ksPopIn .14s ease; }
        .ks-col-menu.show { display: block; }
        .ks-col-menu label { display: flex; align-items: center; gap: 8px; font-size: 12.5px; padding: 5px 6px; border-radius: 6px; cursor: pointer; color: #374151; }
        .ks-col-menu label:hover { background: #F6F8FB; }
        .ks-col-reset { width: 100%; text-align: left; border: none; background: none; color: #4F46E5; font-size: 12px; font-weight: 600; padding: 6px; border-top: 1px solid #EDEFF3; margin-top: 4px; cursor: pointer; }
        .ks-table-title { font-weight: 700; font-size: 13px; color: #374151; margin: 18px 0 8px; }
        .ks-table-block:first-child .ks-table-title { margin-top: 0; }
        .ks-stats-bar { background: #fff; border: 1px solid #E3E7ED; border-radius: 10px; padding: 10px 16px; }
        .ks-stat { display: flex; align-items: baseline; gap: 8px; position: relative; }
        .ks-stat-label { font-size: 11.5px; font-weight: 700; color: #6B7280; text-transform: uppercase; letter-spacing: .04em; }
        .ks-stat-value { font-size: 20px; font-weight: 700; color: #181B20; transition: color .15s ease; }
        .ks-stat-divider { width: 1px; align-self: stretch; background: #E3E7ED; }
        .ks-stat-note { margin-left: auto; }
        .ks-info-btn { border: none; background: none; color: #9CA3AF; padding: 0; line-height: 1; cursor: pointer; transition: color .12s ease, transform .12s ease; }
        .ks-info-btn:hover { color: #4F46E5; transform: scale(1.1); }
        .ks-info-popover { position: fixed; width: 300px; max-width: calc(100vw - 24px); background: #fff; border: 1px solid #E3E7ED; border-radius: 10px; box-shadow: 0 8px 24px rgba(20, 24, 32, .14); z-index: 1050; padding: 12px 14px; font-size: 12.5px; color: #374151; line-height: 1.5; animation: ksPopIn .15s ease; }
        .ks-info-popover strong { color: #181B20; }
        .ks-info-backdrop { position: fixed; inset: 0; z-index: 55; }
        .ks-card { border-radius: 14px; border: 1px solid #E3E7ED; background: #fff; }
        .ks-card .card-body { overflow: hidden; border-radius: inherit; padding: 0; }
        .ks-scroll { overflow-x: auto; overflow-y: auto; width: 100%; display: block; max-height: 75vh; }
        .ks-scroll::-webkit-scrollbar { height: 8px; width: 8px; }
        .ks-scroll::-webkit-scrollbar-thumb { background: #D7DBE2; border-radius: 999px; }
        .ks-table { font-size: 13px; margin-bottom: 0; width: max-content; min-width: 100%; table-layout: auto; }
        .ks-table thead th { background: #181B20; color: #EDEFF3; font-size: 11.5px; font-weight: 600; white-space: nowrap; border-color: #2C303A; vertical-align: middle; position: sticky; top: 0; z-index: 3; }
        .ks-table td, .ks-table th { vertical-align: top; position: relative; }
        .ks-table td { padding: 0; border-color: #EDEFF3; }
        .ks-table td.ks-idx { padding: 9px 10px; color: #9CA3AF; vertical-align: middle; white-space: nowrap; }
        .ks-sticky-col { position: sticky; left: 0; z-index: 4; background: #181B20; }
        td.ks-sticky-col { background: #fff; z-index: 2; box-shadow: 2px 0 0 rgba(20, 24, 32, .05); }
        tbody tr:hover td.ks-sticky-col { background: #F6F8FB; }
        .ks-cell-display { padding: 9px 10px; min-height: 38px; display: flex; align-items: flex-start; cursor: text; color: #23272E; line-height: 1.45; transition: background-color .12s ease; white-space: pre-wrap; word-break: break-word; overflow-wrap: anywhere; }
        .ks-cell-display.ks-long-text { max-width: 480px; min-width: 200px; }
        .ks-cell-display:hover { background: none; }
        .ks-cell-display.empty { color: #B4BAC3; font-style: italic; }
        .ks-cell-select-wrap { padding: 8px 10px; }
        .ks-select { border: 1px solid #DDE2E8; border-radius: 6px; padding: 5px 6px; font-size: 13px; background: #F9FAFB; width: 100%; transition: border-color .12s ease; }
        .ks-select:hover { border-color: #B9C0CB; }
        .ks-status-select { border: none; border-radius: 999px; padding: 4px 10px; font-size: 12px; font-weight: 600; cursor: pointer; }
        .ks-select2-cell { padding: 6px; min-width: 220px; }
        .ks-select2-cell .select2-container { width: 100% !important; }
        .ks-select2-cell .select2-container--bootstrap-5 .select2-selection { min-height: 32px; font-size: 13px; border-color: #4F46E5; }
        .ks-cell-select-wrap .select2-container--bootstrap-5 .select2-selection { min-height: 30px; font-size: 13px; border: 1px solid #DDE2E8; background: #F9FAFB; }
        .ks-edit-wrap { padding: 6px; }
        .ks-edit-input { width: 100%; border: 1.5px solid #4F46E5; border-radius: 5px; padding: 7px 8px; font-size: 13px; outline: none; white-space: pre-wrap; word-break: break-word; font-family: inherit; }
        textarea.ks-edit-input { min-height: 80px; max-width: 480px; min-width: 200px; resize: vertical; line-height: 1.45; }
        @keyframes ksPulseSave { 0% { background-color: #E7E4FB; } 100% { background-color: transparent; } }
        .ks-just-saved .ks-cell-display, .ks-just-saved .ks-cell-select-wrap { animation: ksPulseSave .9s ease; }
        @keyframes ksPopIn { from { opacity: 0; transform: translateY(-4px) scale(.98); } to { opacity: 1; transform: translateY(0) scale(1); } }
        @keyframes ksRowIn { from { opacity: 0; transform: translateY(-6px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes ksRowOut { to { opacity: 0; transform: translateX(8px); } }
        .ks-row-enter { animation: ksRowIn .22s ease; }
        .ks-row-leaving { animation: ksRowOut .2s ease forwards; }
        .ks-skel-cell { padding: 12px 10px; }
        .ks-skel-bar { height: 12px; border-radius: 4px; background: linear-gradient(90deg, #EDEFF3 25%, #F6F7F9 37%, #EDEFF3 63%); background-size: 400% 100%; animation: ksShimmer 1.3s ease infinite; }
        @keyframes ksShimmer { 0% { background-position: 100% 50%; } 100% { background-position: 0 50%; } }
        .ks-empty-row td { padding: 34px 12px; text-align: center; color: #9CA3AF; font-size: 13px; }
        .ks-empty-row i { font-size: 20px; display: block; margin-bottom: 6px; color: #C6CBD3; }
        .ks-toast-container { position: fixed; right: 18px; bottom: 18px; z-index: 200; display: flex; flex-direction: column; gap: 8px; width: 300px; }
        .ks-toast { background: #fff; border: 1px solid #E3E7ED; border-left: 3px solid #4F46E5; border-radius: 10px; box-shadow: 0 10px 28px rgba(20, 24, 32, .16); padding: 10px 12px; display: flex; align-items: center; gap: 8px; font-size: 12.5px; color: #23272E; animation: ksToastIn .18s ease; }
        .ks-toast-out { animation: ksToastOut .18s ease forwards; }
        @keyframes ksToastIn { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes ksToastOut { to { opacity: 0; transform: translateY(8px); } }
        .ks-toast-msg { flex: 1; line-height: 1.4; }
        .ks-toast-action { border: none; background: none; color: #4F46E5; font-weight: 700; font-size: 12px; cursor: pointer; white-space: nowrap; }
        .ks-toast-close { border: none; background: none; color: #C6CBD3; cursor: pointer; font-size: 14px; line-height: 1; padding: 0; }
        .ks-toast-close:hover { color: #9CA3AF; }
        .select2-container--open .select2-dropdown { z-index: 9999 !important; }
        .select2-container--bootstrap-5 .select2-dropdown { border-color: #E3E7ED; border-radius: 8px; box-shadow: 0 8px 24px rgba(20, 24, 32, .14); z-index: 9999 !important; }
        .select2-container--bootstrap-5 .select2-results__option--highlighted { background-color: #4F46E5 !important; }
        .select2-container--bootstrap-5 .select2-search__field:focus { border-color: #4F46E5; box-shadow: 0 0 0 .2rem rgba(79, 70, 229, .25); }
        @media (max-width: 576px) {
            .ks-stats-bar { flex-direction: column; align-items: flex-start; }
            .ks-stat-note { margin-left: 0; }
            .ks-stat-divider { display: none; }
            .ks-toast-container { left: 12px; right: 12px; width: auto; }
        }
        .ks-select2-cell .select2-container--bootstrap-5 .select2-selection--multiple { min-height: 32px; font-size: 13px; border-color: #4F46E5; }
        .ks-select2-cell .select2-container--bootstrap-5 .select2-selection__choice { font-size: 12px; background: #EEF2FF; border-color: #C7D2FE; color: #4F46E5; }
        .ks-needs-wrap { background: #fff; border: 1px solid #E3E7ED; border-radius: 10px; overflow: hidden; }
        .ks-needs-table { font-size: 12.5px; }
        .ks-needs-table thead th { background: #F6F8FB; color: #374151; font-weight: 700; font-size: 11.5px; text-transform: uppercase; letter-spacing: .03em; border-color: #E3E7ED; vertical-align: middle; }
        .ks-needs-table td { vertical-align: middle; border-color: #EDEFF3; }
        .ks-needs-table .ks-need-val { font-weight: 700; color: #181B20; }
        .ks-needs-table th .ks-stat { display: inline-flex; align-items: center; gap: 6px; position: relative; }
        .ks-needs-header { display: flex; align-items: center; justify-content: space-between; gap: 10px; padding: 10px 14px; cursor: pointer; user-select: none; background: #F6F8FB; transition: background-color .12s ease; }
        .ks-needs-header:hover { background: #EEF1F5; }
        .ks-needs-header-title { font-weight: 700; font-size: 12.5px; color: #374151; display: flex; align-items: center; gap: 8px; }
        .ks-needs-chevron { transition: transform .18s ease; color: #9CA3AF; font-size: 11px; }
        .ks-needs-wrap.open .ks-needs-chevron { transform: rotate(90deg); }
        .ks-needs-body { border-top: 1px solid #E3E7ED; }
        .ks-table tbody tr { transition: background-color 0.15s ease; }
        .ks-table tbody tr td { color: #fff !important; }
        .ks-table tbody tr td.ks-idx { color: #fff !important; font-weight: 600; }
        .ks-table tbody tr td .ks-cell-display { color: #fff !important; }
        .ks-table tbody tr td .ks-cell-display.empty { color: rgba(255, 255, 255, 0.6) !important; }
        .ks-table tbody tr:hover td { filter: brightness(1.1); }
    </style>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        (function($) {
            'use strict';

            var API_BASE = '{{ url('kelas-setting') }}';
            var CSRF_TOKEN = '{{ csrf_token() }}';
            var CURRENT_USER = @json(auth()->check() ? auth()->user()->username ?? 'User' : 'Anonymous');

            var LONG_TEXT_FIELDS = ['software', 'keterangan'];
            var SELECT2_FIELDS = ['kelas', 'instruktur', 'pcits', 'asset'];

            var RUANGAN_OPTIONS = [
                'Ruang 1', 'Ruang 2', 'Ruang 3', 'Ruang 4', 'Ruang 5', 'Ruang 6',
                'Adoc', 'R. Meeting Kecil', 'R. Meeting Besar',
                'Inhouse Bandung', 'Inhouse Luar Bandung', 'Workingspace', 'Virtual'
            ];

            var DAY_NAMES = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
            var DEVICE_OPTIONS = ['Laptop', 'PC', 'PC & LAPTOP'];
            var DEVICE_INSTRUCTOR_OPTIONS = ['Laptop Instruktur', 'Laptop Kelas', 'PC Kelas'];

            var COLUMNS = [
                { key: 'kelas', label: 'Kelas', type: 'select2', options: [], width: '260px' },
                { key: 'dari', label: 'Dari', type: 'date', width: '110px' },
                { key: 'sampai', label: 'Sampai', type: 'date', width: '110px' },
                { key: 'ruangan', label: 'Ruangan', type: 'select', options: RUANGAN_OPTIONS, width: '180px' },
                { key: 'device', label: 'Device', type: 'select', options: DEVICE_OPTIONS, width: '110px' },
                { key: 'deviceInstruktur', label: 'Device Instruktur', type: 'select', options: DEVICE_INSTRUCTOR_OPTIONS, width: '140px' },
                { key: 'pax', label: 'Pax', type: 'number', width: '65px' },
                { key: 'instruktur', label: 'Instruktur', type: 'select2', options: [], width: '260px' },
                { key: 'asset', label: 'Asset', type: 'textarea', width: '240px' },
                { key: 'software', label: 'Software Install', type: 'textarea', width: '240px' },
                { key: 'keterangan', label: 'Keterangan', type: 'textarea', width: '260px' },
                { key: 'pcits', label: 'PIC TS', type: 'select2', options: [], width: '260px' },
                { key: 'status', label: 'Status', type: 'select', options: ['Merah', 'Biru', 'Hijau', 'Hitam'], width: '105px' }
            ];

            var STATUS_STYLE = {
                Merah:  { bg: '#EF4444', fg: '#FFFFFF' },
                Biru:   { bg: '#6366F1', fg: '#FFFFFF' },
                Hijau:  { bg: '#22C55E', fg: '#FFFFFF' },
                Hitam:  { bg: '#6B7280', fg: '#FFFFFF' }
            };

            var STATUS_ORDER = { Merah: 0, Biru: 1, Hijau: 2, Hitam: 3 };

            var INFO_TEXT = {
                laptop: 'Kelas dikelompokkan per kombinasi <strong>Ruangan + Instruktur</strong>. Dari kelas-kelas yang memakai Device = "Laptop", diambil <strong>pax terbesar</strong> di tiap kelompok itu.',
                pc: 'Sama seperti laptop, dikelompokkan per <strong>Ruangan + Instruktur</strong>. Untuk tiap kelompok dihitung dua angka lalu dijumlah: kebutuhan peserta dan instruktur.'
            };

            var MONTHS_SHORT = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'];
            var MONTHS_FULL = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

            var weeksData = {};
            var metaOptions = { kelas: [], instruktur: [], pcits: [], asset: [] };

            var currentViewMode = 'month';
            var currentPeriodValue = null;
            var currentSort = 'dari_asc';
            var editingCell = null;
            var openInfo = null;
            var needsOpenState = {};
            var colMenuOpen = false;
            var searchQuery = '';
            var isLoadingPeriod = false;
            var uidCounter = 0;
            var searchDebounce = null;

            var visibleCols = {};
            COLUMNS.forEach(function(c) { visibleCols[c.key] = true; });

            var justAddedRowId = null;
            var justSavedKey = null;
            var removingRowId = null;
            var toastActions = {};

            function validateRowId(rowId) {
                console.log('DEBUG validateRowId:', rowId, 'Type:', typeof rowId);
                if (!rowId || String(rowId).trim() === '' || String(rowId) === 'undefined' || String(rowId) === 'null') {
                    console.error('DIBLOKIR: rowId tidak valid!', rowId);
                    showToast('Gagal menyimpan: ID baris tidak ditemukan. Lihat Console (F12) untuk detail.', { icon: 'bi-exclamation-triangle' });
                    return false;
                }
                return true;
            }

            function uid(prefix) {
                uidCounter++;
                return prefix + Date.now().toString(36) + uidCounter;
            }

            function ck(rowId, key) {
                return rowId + ':' + key;
            }

            function esc(v) {
                return String(v == null ? '' : v).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
            }

            function pad2(n) {
                return (n < 10 ? '0' : '') + n;
            }

            function fmtDate(iso) {
                if (!iso) return '';
                var p = iso.split('-');
                return p.length === 3 ? p[2] + '/' + p[1] + '/' + p[0] : iso;
            }

            function fmtRangeLabel(startISO, endISO) {
                var s = startISO.split('-'), e = endISO.split('-');
                var sy = s[0], sm = parseInt(s[1], 10), sd = parseInt(s[2], 10);
                var ey = e[0], em = parseInt(e[1], 10), ed = parseInt(e[2], 10);
                if (sy === ey && sm === em) return pad2(sd) + ' – ' + pad2(ed) + ' ' + MONTHS_SHORT[em - 1] + ' ' + ey;
                else if (sy === ey) return pad2(sd) + ' ' + MONTHS_SHORT[sm - 1] + ' – ' + pad2(ed) + ' ' + MONTHS_SHORT[em - 1] + ' ' + ey;
                return pad2(sd) + ' ' + MONTHS_SHORT[sm - 1] + ' ' + sy + ' – ' + pad2(ed) + ' ' + MONTHS_SHORT[em - 1] + ' ' + ey;
            }

            function monthLabel(monthKey) {
                var p = monthKey.split('-');
                return MONTHS_FULL[parseInt(p[1], 10) - 1] + ' ' + p[0];
            }

            function toApiField(jsKey) {
                if (jsKey === 'deviceInstruktur') return 'device_instruktur';
                if (jsKey === 'pcits') return 'pc_its';
                return jsKey;
            }

            function apiCall(method, url, data) {
                console.log('API CALL:', method, url, data);
                return $.ajax({
                    url: url,
                    method: method,
                    data: data ? JSON.stringify(data) : undefined,
                    contentType: 'application/json',
                    headers: { 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' }
                });
            }

            function saveScrollPositions() {
                var positions = {};
                $('.ks-scroll').each(function() {
                    var tableId = $(this).closest('.ks-table-block').data('table-id');
                    if (tableId) positions[tableId] = { left: this.scrollLeft, top: this.scrollTop };
                });
                return positions;
            }

            function restoreScrollPositions(positions) {
                if (!positions) return;
                $('.ks-scroll').each(function() {
                    var tableId = $(this).closest('.ks-table-block').data('table-id');
                    if (tableId && positions[tableId]) {
                        this.scrollLeft = positions[tableId].left;
                        this.scrollTop = positions[tableId].top;
                    }
                });
            }

            function loadInitialData(opts) {
                opts = opts || {};
                return $.get(API_BASE + '?search=' + encodeURIComponent(searchQuery))
                    .done(function(res) {
                        console.log('DEBUG: Data dari server:', res);
                        if (res.success && res.data) {
                            weeksData = res.data;
                            if (res.meta) {
                                COLUMNS[0].options = (res.meta.materi || []).map(function(m) { return { id: m, text: m }; });
                                metaOptions.kelas = COLUMNS[0].options;
                                metaOptions.instruktur = res.meta.karyawan_instruktur || [];
                                metaOptions.pcits = res.meta.karyawan_ts || [];
                                metaOptions.asset = res.meta.inventaris || [];

                                COLUMNS.forEach(function(c) {
                                    if (c.key === 'instruktur') c.options = metaOptions.instruktur;
                                    if (c.key === 'pcits') c.options = metaOptions.pcits;
                                    if (c.key === 'asset') c.options = metaOptions.asset;
                                });
                            }
                            render();
                        }
                    })
                    .fail(function(xhr) {
                        if (!opts.silent) showToast('Gagal memuat data dari server.', { icon: 'bi-exclamation-triangle' });
                    });
            }

            function showToast(message, opts) {
                opts = opts || {};
                var id = uid('t');
                var actionHtml = opts.actionLabel ? '<button type="button" class="ks-toast-action" data-toast-id="' + id + '">' + esc(opts.actionLabel) + '</button>' : '';
                var $t = $('<div class="ks-toast" data-toast-id="' + id + '"><i class="bi ' + (opts.icon || 'bi-check-circle') + '"></i><span class="ks-toast-msg">' + esc(message) + '</span>' + actionHtml + '<button type="button" class="ks-toast-close" data-toast-id="' + id + '">&times;</button></div>');
                $('#ksToastContainer').append($t);
                if (opts.actionLabel && opts.onAction) toastActions[id] = opts.onAction;
                setTimeout(function() { dismissToast(id); }, opts.duration || 4500);
            }

            function dismissToast(id) {
                var $t = $('.ks-toast[data-toast-id="' + id + '"]');
                $t.addClass('ks-toast-out');
                setTimeout(function() { $t.remove(); }, 180);
                delete toastActions[id];
            }

            $(document).on('click', '.ks-toast-action', function() {
                var id = $(this).data('toast-id');
                if (toastActions[id]) toastActions[id]();
                dismissToast(id);
            });
            $(document).on('click', '.ks-toast-close', function() {
                dismissToast($(this).data('toast-id'));
            });

            function getWeekKeysSorted() {
                return Object.keys(weeksData).sort(function(a, b) {
                    return weeksData[a].start.localeCompare(weeksData[b].start);
                });
            }

            function getPeriodOptions(viewMode) {
                var wk = getWeekKeysSorted();
                var opts = [];
                if (viewMode === 'month') {
                    var byMonth = {};
                    wk.forEach(function(k) {
                        var mk = weeksData[k].start.slice(0, 7);
                        if (!byMonth[mk]) byMonth[mk] = [];
                        byMonth[mk].push(k);
                    });
                    Object.keys(byMonth).sort().forEach(function(mk) {
                        opts.push({ value: mk, label: monthLabel(mk), weeks: byMonth[mk] });
                    });
                } else if (viewMode === 'month2') {
                    var byMonth2 = {};
                    wk.forEach(function(k) {
                        var mk = weeksData[k].start.slice(0, 7);
                        if (!byMonth2[mk]) byMonth2[mk] = [];
                        byMonth2[mk].push(k);
                    });
                    var monthKeys = Object.keys(byMonth2).sort();
                    for (var j = 0; j < monthKeys.length; j += 2) {
                        var pairM = monthKeys.slice(j, j + 2);
                        var wks = [];
                        pairM.forEach(function(mk) { wks = wks.concat(byMonth2[mk]); });
                        var lbl2 = pairM.length === 2 ? (monthLabel(pairM[0]).split(' ')[0] + ' – ' + monthLabel(pairM[1])) : monthLabel(pairM[0]);
                        opts.push({ value: pairM.join('+'), label: lbl2, weeks: wks });
                    }
                }
                return opts;
            }

            function computeTableContexts() {
                var opts = getPeriodOptions(currentViewMode);
                if (!opts.length) return [];

                if (!currentPeriodValue || !opts.some(function(o) { return o.value === currentPeriodValue; })) {
                    var today = new Date();
                    var todayISO = today.getFullYear() + '-' + pad2(today.getMonth() + 1) + '-' + pad2(today.getDate());
                    var found = opts.find(function(o) {
                        return o.weeks.some(function(wk) {
                            var w = weeksData[wk];
                            return w && w.start <= todayISO && w.end >= todayISO;
                        });
                    });
                    currentPeriodValue = found ? found.value : opts[opts.length - 1].value;
                }
                var period = opts.filter(function(o) { return o.value === currentPeriodValue; })[0];
                var allWeeks = getWeekKeysSorted();
                var weeks = period.weeks.slice().sort(function(a, b) {
                    return weeksData[a].start.localeCompare(weeksData[b].start);
                });

                return weeks.map(function(wk) {
                    return {
                        id: wk,
                        title: 'Minggu ' + (allWeeks.indexOf(wk) + 1) + ' · ' + fmtRangeLabel(weeksData[wk].start, weeksData[wk].end),
                        rows: weeksData[wk].rows
                    };
                });
            }

            function findRow(rowId) {
                var found = null;
                Object.keys(weeksData).some(function(wk) {
                    var r = (weeksData[wk].rows || []).filter(function(x) { return String(x.id) === String(rowId); })[0];
                    if (r) {
                        found = { week: wk, row: r };
                        return true;
                    }
                    return false;
                });
                return found;
            }

            function sortRows(rowsArr) {
                if (!currentSort) return rowsArr;
                var arr = rowsArr.slice();
                arr.sort(function(a, b) {
                    var statusA = String(a.status || '').trim().toLowerCase();
                    var statusB = String(b.status || '').trim().toLowerCase();
                    var statusOrderA = getStatusOrder(statusA);
                    var statusOrderB = getStatusOrder(statusB);
                    if (statusOrderA !== statusOrderB) {
                        return statusOrderA - statusOrderB;
                    }
                    var dateA = a.dari || '9999-99-99';
                    var dateB = b.dari || '9999-99-99';
                    return dateA.localeCompare(dateB);
                });
                return arr;
            }

            function getStatusOrder(status) {
                var s = status.trim().toLowerCase();
                if (s === '0' || s === 'merah') return 0;
                if (s === '1' || s === 'biru') return 1;
                if (s === '3' || s === 'hijau') return 2;
                return 3;
            }

            $(document).on('change', '#ksViewMode', function() {
                currentViewMode = $(this).val();
                currentPeriodValue = null;
                editingCell = null;
                openInfo = null;
                loadPeriod();
            });
            $(document).on('change', '#ksPeriod', function() {
                currentPeriodValue = $(this).val();
                editingCell = null;
                openInfo = null;
                loadPeriod();
            });
            $(document).on('change', '#ksSort', function() {
                currentSort = $(this).val();
                render();
            });

            function loadPeriod() {
                isLoadingPeriod = true;
                render();
                loadInitialData().always(function() {
                    isLoadingPeriod = false;
                    render();
                });
            }

            function renderPeriodOptions() {
                var opts = getPeriodOptions(currentViewMode);
                if (!currentPeriodValue || !opts.some(function(o) { return o.value === currentPeriodValue; })) {
                    var today = new Date();
                    var todayISO = today.getFullYear() + '-' + pad2(today.getMonth() + 1) + '-' + pad2(today.getDate());
                    var found = null;
                    if (currentViewMode === 'month' || currentViewMode === 'month2') {
                        var currentMonthKey = today.getFullYear() + '-' + pad2(today.getMonth() + 1);
                        found = opts.find(function(o) { return o.value === currentMonthKey || o.value.indexOf(currentMonthKey) > -1; });
                    }
                    currentPeriodValue = found ? found.value : (opts.length ? opts[opts.length - 1].value : null);
                }

                var html = '';
                opts.forEach(function(o) {
                    html += '<option value="' + esc(o.value) + '"' + (o.value === currentPeriodValue ? ' selected' : '') + '>' + esc(o.label) + '</option>';
                });
                $('#ksPeriod').html(html);
                $('#ksViewMode').val(currentViewMode);
                $('#ksSort').val(currentSort);
            }

            $(document).on('input', '#ksSearch', function() {
                searchQuery = $(this).val().trim().toLowerCase();
                if (searchDebounce) clearTimeout(searchDebounce);
                isLoadingPeriod = true;
                render();
                searchDebounce = setTimeout(function() {
                    loadInitialData().always(function() {
                        isLoadingPeriod = false;
                        render();
                    });
                }, 300);
            });

            function filterRows(rowsArr) {
                if (!searchQuery) return rowsArr;
                return rowsArr.filter(function(r) {
                    var assetStr = Array.isArray(r.asset) ? r.asset.join(' ') : (r.asset || '');
                    var hay = (r.kelas + ' ' + r.instruktur + ' ' + r.ruangan + ' ' + assetStr).toLowerCase();
                    return hay.indexOf(searchQuery) > -1;
                });
            }

            function renderColMenu() {
                var html = '';
                COLUMNS.forEach(function(c) {
                    html += '<label><input type="checkbox" class="ks-col-check" data-col="' + c.key + '" checked> ' + esc(c.label) + '</label>';
                });
                html += '<button type="button" class="ks-col-reset" id="ksColReset">Tampilkan semua kolom</button>';
                $('#ksColMenu').html(html);
            }

            $(document).on('click', '#ksColBtn', function(e) {
                e.stopPropagation();
                colMenuOpen = !colMenuOpen;
                $('#ksColMenu').toggleClass('show', colMenuOpen);
            });
            $(document).on('click', '#ksColMenu', function(e) { e.stopPropagation(); });
            $(document).on('change', '.ks-col-check', function() {
                visibleCols[$(this).data('col')] = this.checked;
                render();
            });
            $(document).on('click', '#ksColReset', function() {
                COLUMNS.forEach(function(c) { visibleCols[c.key] = true; });
                $('.ks-col-check').prop('checked', true);
                render();
            });
            $(document).on('click', function(e) {
                if (colMenuOpen && !$(e.target).closest('#ksColMenu, #ksColBtn').length) {
                    colMenuOpen = false;
                    $('#ksColMenu').removeClass('show');
                }
            });
            $(document).on('keydown', function(e) {
                if (e.key !== 'Escape') return;
                if (openInfo) { openInfo = null; renderInfoPopovers(); }
                else if (colMenuOpen) { colMenuOpen = false; $('#ksColMenu').removeClass('show'); }
            });

            function getWeekDates(startISO) {
                var p = startISO.split('-').map(Number);
                var base = new Date(p[0], p[1] - 1, p[2]);
                var dates = [];
                for (var i = 0; i < 7; i++) {
                    var dt = new Date(base.getFullYear(), base.getMonth(), base.getDate() + i);
                    dates.push(dt.getFullYear() + '-' + pad2(dt.getMonth() + 1) + '-' + pad2(dt.getDate()));
                }
                return dates;
            }

            function computeNeedsForDate(rowsForTable, dateISO) {
                var activeRows = rowsForTable.filter(function(r) {
                    return r.dari && r.sampai && r.dari <= dateISO && r.sampai >= dateISO;
                });
                return computeNeeds(activeRows);
            }

            function computeNeeds(rowsForTable) {
                var groups = {};
                rowsForTable.forEach(function(r) {
                    var gKey = String(r.ruangan || '').trim() + '||' + String(r.instruktur || '').trim();
                    if (!groups[gKey]) groups[gKey] = [];
                    groups[gKey].push(r);
                });

                var laptopTotal = 0;
                Object.keys(groups).forEach(function(gKey) {
                    var laptopRows = groups[gKey].filter(function(r) { return String(r.device || '').trim() === 'Laptop'; });
                    if (laptopRows.length) {
                        laptopTotal += Math.max.apply(null, laptopRows.map(function(r) { return Number(r.pax) || 0; }));
                    }
                });

                var pcTotal = 0;
                Object.keys(groups).forEach(function(gKey) {
                    var groupRows = groups[gKey];
                    var pcRows = groupRows.filter(function(r) { return String(r.device || '').trim() === 'PC'; });
                    var studentPcNeed = pcRows.length ? Math.max.apply(null, pcRows.map(function(r) { return Number(r.pax) || 0; })) : 0;
                    var instructorPcNeed = groupRows.some(function(r) { return String(r.deviceInstruktur || '').trim() === 'PC'; }) ? 1 : 0;
                    pcTotal += studentPcNeed + instructorPcNeed;
                });

                return { laptop: laptopTotal, pc: pcTotal };
            }

            function renderNeedsTable(ctx) {
                var isOpen = !!needsOpenState[ctx.id];
                var dates = getWeekDates(ctx.id);
                var h = [];

                h.push('<div class="ks-needs-wrap mb-2 mt-3' + (isOpen ? ' open' : '') + '">');
                h.push('<div class="ks-needs-header ks-needs-toggle" data-table="' + esc(ctx.id) + '">');
                h.push('<span class="ks-needs-header-title"><i class="bi bi-chevron-right ks-needs-chevron"></i>Kebutuhan Laptop &amp; PC per Hari</span>');
                h.push('<span class="text-muted small">' + (isOpen ? 'Sembunyikan' : 'Klik untuk lihat detail') + '</span>');
                h.push('</div>');

                if (isOpen) {
                    h.push('<div class="ks-needs-body"><table class="table table-sm ks-needs-table mb-0"><thead><tr>');
                    h.push('<th style="width:170px;">Tanggal</th>');
                    h.push('<th><span class="ks-stat">Kebutuhan Laptop<button type="button" class="ks-info-btn ks-info-toggle" data-table="' + esc(ctx.id) + '" data-info="laptop" title="Cara menghitung"><i class="bi bi-question-circle"></i></button></span></th>');
                    h.push('<th><span class="ks-stat">Kebutuhan PC<button type="button" class="ks-info-btn ks-info-toggle" data-table="' + esc(ctx.id) + '" data-info="pc" title="Cara menghitung"><i class="bi bi-question-circle"></i></button></span></th>');
                    h.push('</tr></thead><tbody>');

                    if (isLoadingPeriod) {
                        h.push('<tr><td colspan="3" class="text-muted small py-2">Memuat...</td></tr>');
                    } else {
                        dates.forEach(function(dateISO) {
                            var dayIdx = new Date(dateISO + 'T00:00:00').getDay();
                            var dayLabel = DAY_NAMES[dayIdx] + ', ' + fmtDate(dateISO);
                            var needs = computeNeedsForDate(ctx.rows, dateISO);
                            h.push('<tr><td>' + esc(dayLabel) + '</td><td class="ks-need-val">' + needs.laptop + '</td><td class="ks-need-val">' + needs.pc + '</td></tr>');
                        });
                    }
                    h.push('</tbody></table></div>');
                }
                h.push('</div>');
                return h.join('');
            }

            function renderInfoPopovers() {
                $('.ks-info-popover-wrap').remove();
                if (!openInfo) return;
                var $btn = $('.ks-info-toggle[data-table="' + openInfo.table + '"][data-info="' + openInfo.type + '"]');
                if (!$btn.length) { openInfo = null; return; }

                $('body').append('<div class="ks-info-popover-wrap"><div class="ks-info-backdrop"></div><div class="ks-info-popover">' + INFO_TEXT[openInfo.type] + '</div></div>');

                var $pop = $('.ks-info-popover');
                var rect = $btn[0].getBoundingClientRect();
                var gap = 8, margin = 8, popW = 300;
                var left = rect.left;
                if (left + popW + margin > window.innerWidth) left = Math.max(margin, window.innerWidth - popW - margin);
                var top = rect.bottom + gap;
                var estHeight = $pop.outerHeight() || 90;
                if (top + estHeight + margin > window.innerHeight) top = Math.max(margin, rect.top - estHeight - gap);

                $pop.css({ top: Math.round(top) + 'px', left: Math.round(left) + 'px' });
            }

            $(document).on('click', '.ks-info-toggle', function(e) {
                e.stopPropagation();
                var table = $(this).data('table'), type = $(this).data('info');
                openInfo = (openInfo && openInfo.table === table && openInfo.type === type) ? null : { table: table, type: type };
                renderInfoPopovers();
            });

            $(document).on('click', '.ks-needs-toggle', function(e) {
                e.stopPropagation();
                var table = $(this).data('table');
                needsOpenState[table] = !needsOpenState[table];
                render();
            });

            $(document).on('click', '.ks-info-backdrop', function() {
                openInfo = null;
                renderInfoPopovers();
            });

            function visibleColumns() {
                return COLUMNS.filter(function(c) { return visibleCols[c.key]; });
            }

            function getSelect2Display(key, value) {
                if (!value) return '';
                var values = Array.isArray(value) ? value : [value];
                var labels = values.map(function(v) {
                    if (key === 'asset') {
                        var f = (metaOptions.asset || []).find(function(o) { return String(o.id) === String(v); });
                        return f ? f.text : v;
                    }
                    if (key === 'instruktur' || key === 'pcits') {
                        var g = (metaOptions[key] || []).find(function(o) { return o.id === v || o.kode === v; });
                        return g ? g.text : v;
                    }
                    return v;
                });
                return labels.join(', ');
            }

            function getStatusStyle(status) {
                var s = String(status).trim().toLowerCase();
                var matchedKey = 'Hitam';
                if (s === '0' || s === 'merah') matchedKey = 'Merah';
                else if (s === '1' || s === 'biru') matchedKey = 'Biru';
                else if (s === '3' || s === 'hijau') matchedKey = 'Hijau';
                var style = STATUS_STYLE[matchedKey];
                return 'background-color: ' + style.bg + ' !important;';
            }

            function render() {
                var scrollPos = saveScrollPositions();
                destroyAllSelect2();
                renderPeriodOptions();
                var contexts = isLoadingPeriod ? [{ id: '__loading__', title: 'Memuat...', rows: [] }] : computeTableContexts();
                var html = '';
                contexts.forEach(function(ctx) { html += renderTableBlock(ctx); });
                if (!contexts.length) html = '<div class="text-muted small py-4">Tidak ada data untuk periode ini.</div>';
                $('#ksTablesContainer').html(html);
                initAllSelect2();
                justAddedRowId = null;
                justSavedKey = null;
                removingRowId = null;

                requestAnimationFrame(function() { restoreScrollPositions(scrollPos); });
            }

            function destroyAllSelect2() {
                var $els = $('.ks-select2-input.select2-hidden-accessible');
                if ($els.length > 0) { try { $els.select2('destroy'); } catch (e) {} }
            }

            function initAllSelect2() {
                $('.ks-select2-input').each(function() {
                    var $el = $(this);
                    var field = $el.data('field');
                    var rowId = $el.data('row-id');
                    var isMultiple = (field === 'asset');
                    var options = [];

                    if (field === 'kelas') options = metaOptions.kelas;
                    else if (field === 'instruktur') options = metaOptions.instruktur;
                    else if (field === 'pcits') options = metaOptions.pcits;
                    else if (field === 'asset') options = metaOptions.asset;

                    var currentRaw = $el.attr('data-current');
                    var currentVal = currentRaw;

                    if (isMultiple) {
                        if (typeof currentRaw === 'string' && currentRaw) {
                            try { currentVal = JSON.parse(currentRaw); } catch (e) { currentVal = [currentRaw]; }
                        } else if (!currentRaw) { currentVal = []; }
                        if (!Array.isArray(currentVal)) currentVal = [currentVal];
                        currentVal = currentVal.map(String);
                    }

                    $el.empty();
                    if (!isMultiple) $el.append('<option value="">-</option>');

                    options.forEach(function(o) {
                        var id = typeof o === 'object' ? String(o.id) : String(o);
                        var text = typeof o === 'object' ? o.text : o;
                        $el.append('<option value="' + esc(id) + '">' + esc(text) + '</option>');
                    });

                    if (isMultiple) {
                        currentVal.forEach(function(v) {
                            if (!$el.find('option[value="' + esc(v) + '"]').length) $el.append('<option value="' + esc(v) + '" selected>' + esc(v) + '</option>');
                        });
                        $el.val(currentVal);
                    } else if (currentVal) {
                        if (!$el.find('option[value="' + esc(currentVal) + '"]').length) $el.append('<option value="' + esc(currentVal) + '" selected>' + esc(currentVal) + '</option>');
                        $el.val(currentVal);
                    }

                    $el.select2({
                        theme: 'bootstrap-5',
                        placeholder: 'Pilih...',
                        allowClear: true,
                        dropdownParent: $('#ksTablesContainer').length ? $('#ksTablesContainer') : $(document.body), 
                        width: '100%',
                        language: {
                            noResults: function() { return 'Tidak ditemukan'; },
                            searching: function() { return 'Mencari...'; }
                        }
                    });

                    $el.off('change.ks').on('change.ks', function() {
                        var newVal = isMultiple ? ($(this).val() || []) : ($(this).val() || '');
                        handleSelect2Change(rowId, field, newVal);
                    });
                });
            }

            function applyRowStyle(rowId) {
                var ctx = findRow(rowId);
                if (!ctx) return;
                var rowStyle = getStatusStyle(ctx.row.status);
                var $tr = $('tr[data-row-id="' + rowId + '"]');
                $tr.attr('style', rowStyle);
                $tr.find('td').each(function() {
                    // Pertahankan style yang sudah ada, tambahkan background color
                    var currentStyle = $(this).attr('style') || '';
                    if (currentStyle.indexOf('background-color') === -1) {
                        $(this).attr('style', currentStyle + ' ' + rowStyle);
                    } else {
                        $(this).attr('style', rowStyle);
                    }
                });
            }
                        
            function handleSelect2Change(rowId, field, newVal) {
                if (!validateRowId(rowId)) return;
                var ctx = findRow(rowId);
                if (!ctx) return;

                var oldVal = ctx.row[field];
                ctx.row[field] = newVal;

                if (field === 'status') {
                    applyRowStyle(rowId);
                }

                var payload = {};
                payload[toApiField(field)] = newVal;
                var targetUrl = API_BASE + '/update/' + String(rowId).trim();

                apiCall('PATCH', targetUrl, payload)
                    .done(function(res) {
                        if (res.success && res.data) {
                            if (String(res.data.id) !== String(rowId)) {
                                $('tr[data-row-id="' + rowId + '"]').attr('data-row-id', res.data.id);
                                $('tr[data-row-id="' + res.data.id + '"] .ks-select2-input').attr('data-row-id', res.data.id);
                                ctx.row.id = String(res.data.id);
                            }
                            $.extend(ctx.row, res.data);
                            if (field === 'status') applyRowStyle(ctx.row.id);
                        }
                    })
                    .fail(function(xhr) {
                        ctx.row[field] = oldVal;
                        if (field === 'status') applyRowStyle(rowId);
                        var msg = xhr.responseJSON?.message || 'Gagal menyimpan perubahan.';
                        showToast(msg, { icon: 'bi-exclamation-triangle' });
                    });
            }

            function renderTableBlock(ctx) {
                var cols = visibleColumns();
                var needs = computeNeeds(ctx.rows);
                var filtered = filterRows(ctx.rows);
                var sorted = sortRows(filtered);

                var h = [];
                h.push('<div class="ks-table-block" data-table-id="' + esc(ctx.id) + '">');
                h.push('<div class="ks-table-title"><i class="bi bi-calendar-week me-1"></i>' + esc(ctx.title) + ' <span class="text-muted fw-normal small ms-2">(' + sorted.length + ' kelas)</span></div>');
                h.push(renderNeedsTable(ctx));

                h.push('<div class="card shadow-sm ks-card mb-3"><div class="card-body p-3">');
                h.push('<div class="ks-stats-bar d-flex flex-wrap align-items-center gap-4">');
                h.push('<div class="ks-stat"><span class="ks-stat-label">Kebutuhan Laptop</span><span class="ks-stat-value">' + (isLoadingPeriod ? '-' : needs.laptop) + '</span><button type="button" class="ks-info-btn ks-info-toggle" data-table="' + esc(ctx.id) + '" data-info="laptop" title="Cara menghitung"><i class="bi bi-question-circle"></i></button></div>');
                h.push('<div class="ks-stat-divider"></div>');
                h.push('<div class="ks-stat"><span class="ks-stat-label">Kebutuhan PC</span><span class="ks-stat-value">' + (isLoadingPeriod ? '-' : needs.pc) + '</span><button type="button" class="ks-info-btn ks-info-toggle" data-table="' + esc(ctx.id) + '" data-info="pc" title="Cara menghitung"><i class="bi bi-question-circle"></i></button></div>');
                h.push('<div class="ks-stat-note text-muted small"><i class="bi bi-info-circle me-1"></i>Dihitung dari data tabel ini.</div>');
                h.push('</div></div></div>');

                h.push('<div class="card shadow-sm ks-card"><div class="card-body p-0"><div class="ks-scroll">');
                h.push('<table class="table ks-table mb-0"><thead><tr><th class="ks-sticky-col" style="width:42px;">No</th>');
                cols.forEach(function(c) { h.push('<th style="min-width:' + c.width + ';">' + esc(c.label) + '</th>'); });
                h.push('</tr></thead><tbody>');

                if (isLoadingPeriod) {
                    for (var i = 0; i < 3; i++) {
                        h.push('<tr><td class="ks-idx">&nbsp;</td>');
                        cols.forEach(function() { h.push('<td class="ks-skel-cell"><div class="ks-skel-bar" style="width:' + (55 + Math.random() * 35) + '%;"></div></td>'); });
                        h.push('<td></td></tr>');
                    }
                } else if (!sorted.length) {
                    h.push('<tr class="ks-empty-row"><td colspan="' + (cols.length + 2) + '"><i class="bi bi-inbox"></i>' + (searchQuery ? 'Tidak ada kelas yang cocok dengan pencarian "' + esc(searchQuery) + '".' : 'Belum ada kelas di minggu ini.') + '</td></tr>');
                } else {
                    sorted.forEach(function(row, idx) {
                        var rowClasses = [];
                        if (String(row.id) === String(justAddedRowId)) rowClasses.push('ks-row-enter');
                        if (String(row.id) === String(removingRowId)) rowClasses.push('ks-row-leaving');
                        
                        var rowStyle = getStatusStyle(row.status);
                        
                        h.push('<tr data-row-id="' + row.id + '" class="' + rowClasses.join(' ') + '">');
                        h.push('<td class="ks-idx ks-sticky-col" style="' + rowStyle + '">' + (idx + 1) + '</td>');
                        
                        cols.forEach(function(col) {
                            var cellHtml = renderCell(row, col);
                            // PERBAIKAN: Tambahkan style ke akhir tag <td>, bukan di tengah atribut
                            cellHtml = cellHtml.replace('<td ', '<td style="' + rowStyle + '" ');
                            h.push(cellHtml);
                        });
                        
                        h.push('</tr>');
                    });
                }
                h.push('</tbody></table></div></div></div></div>');
                return h.join('');
            }

            function renderCell(row, col) {
                console.log('DEBUG renderCell - col:', col.key, '| row.id:', row.id, '| row.id_rkm:', row.id_rkm);
                var safeRowId = String(row.id || ('rkm_' + row.id_rkm));
                console.log('DEBUG renderCell - safeRowId:', safeRowId);
                
                var value = row[col.key];
                var isEditing = editingCell && String(editingCell.rowId) === String(safeRowId) && editingCell.key === col.key;
                var justSaved = justSavedKey === ck(safeRowId, col.key);
                var isLongText = LONG_TEXT_FIELDS.indexOf(col.key) > -1 || col.type === 'textarea';
                var isSelect2 = SELECT2_FIELDS.indexOf(col.key) > -1;

                var h = [];
                h.push('<td data-row-id="' + safeRowId + '" data-key="' + col.key + '"' + (justSaved ? ' class="ks-just-saved"' : '') + '>');
                
                if (col.key === 'status') {
                    var rawStatus = String(value || '').trim();
                    var matchedStatus = col.options.find(function(o) { return o.toLowerCase() === rawStatus.toLowerCase(); }) || 'Biru';
                    var st = STATUS_STYLE[matchedStatus] || STATUS_STYLE.Biru;
                    h.push('<div class="ks-cell-select-wrap"><select class="ks-status-select ks-status-select-input" style="background:' + st.bg + ';color:' + st.fg + ';">');
                    col.options.forEach(function(o) { h.push('<option value="' + o + '"' + (o === matchedStatus ? ' selected' : '') + '>' + o + '</option>'); });
                    h.push('</select></div>');
                } else if (isSelect2) {
                    var displayLabel = getSelect2Display(col.key, value);
                    h.push('<div class="ks-cell-display ks-select2-display" data-field="' + col.key + '" style="color: #fff;">');
                    if (displayLabel) h.push(esc(displayLabel));
                    else h.push('<span class="empty" style="color: rgba(255,255,255,0.6);">Klik untuk pilih</span>');
                    h.push('</div>');
                } else if (col.type === 'select') {
                    h.push('<div class="ks-cell-select-wrap"><select class="ks-select ks-device-select-input" style="color: #374151;"><option value="">-</option>');
                    col.options.forEach(function(o) { h.push('<option value="' + o + '"' + (o === value ? ' selected' : '') + '>' + o + '</option>'); });
                    h.push('</select></div>');
                } else if (isEditing) {
                    if (col.type === 'textarea' || isLongText) {
                        h.push('<div class="ks-edit-wrap"><textarea class="ks-edit-input ks-edit-field" style="color: #374151;">' + esc(value) + '</textarea></div>');
                    } else {
                        var inputType = col.type === 'number' ? 'number' : (col.type === 'date' ? 'date' : 'text');
                        h.push('<div class="ks-edit-wrap"><input type="' + inputType + '" class="ks-edit-input ks-edit-field" value="' + esc(value) + '" style="color: #374151;"></div>');
                    }
                } else {
                    var display = value ? (col.type === 'date' ? fmtDate(value) : esc(value)) : 'Klik untuk isi';
                    var classes = 'ks-cell-display ks-cell-start-edit';
                    if (!value) classes += ' empty';
                    if (isLongText) classes += ' ks-long-text';
                    h.push('<div class="' + classes + '" style="color: #fff;">' + display + '</div>');
                }
                h.push('</td>');
                return h.join('');
            }

            $(document).on('click', '.ks-select2-display', function(e) {
                e.stopPropagation();
                var $td = $(this).closest('td');
                var rowId = $td.attr('data-row-id'); // Gunakan attr, bukan data()
                var field = $(this).data('field');
                
                console.log('DEBUG click .ks-select2-display - rowId:', rowId);
                
                if (!validateRowId(rowId)) return;
                
                var ctx = findRow(rowId);
                var currentVal = ctx ? (ctx.row[field] || '') : '';
                var currentAttr = Array.isArray(currentVal) ? JSON.stringify(currentVal) : currentVal;

                var $wrap = $('<div class="ks-select2-cell"></div>');
                var $select = $('<select class="ks-select2-input" data-field="' + field + '" data-row-id="' + rowId + '"></select>');
                $select.attr('data-current', currentAttr);
                if (field === 'asset') $select.attr('multiple', 'multiple');
                $wrap.append($select);
                $(this).replaceWith($wrap);
                
                initAllSelect2();
                
                setTimeout(function() {
                    $select.select2('open');
                }, 10);
            });

            $(window).on('resize', function() {
                if (openInfo) renderInfoPopovers();
            });

            $(document).on('click', '.ks-cell-start-edit', function() {
                var $td = $(this).closest('td');
                editingCell = { rowId: $td.attr('data-row-id'), key: $td.data('key') };
                render();
            });
            $(document).on('blur', '.ks-edit-field', function() { commitEdit($(this).val()); });
            $(document).on('keydown', '.ks-edit-field', function(e) {
                if (e.key === 'Enter' && this.tagName !== 'TEXTAREA') commitEdit($(this).val());
                else if (e.key === 'Escape') { editingCell = null; render(); }
            });

            function commitEdit(newVal) {
                if (!editingCell) return;
                var rowId = editingCell.rowId, key = editingCell.key;
                
                if (!validateRowId(rowId)) {
                    editingCell = null;
                    return;
                }
                
                var ctx = findRow(rowId);
                if (!ctx) return;
                
                var oldVal = ctx.row[key];
                ctx.row[key] = newVal;
                
                if (key === 'status') applyRowStyle(rowId);
                
                editingCell = null;

                var payload = {};
                payload[toApiField(key)] = newVal;

                apiCall('PATCH', API_BASE + '/update/' + String(rowId).trim(), payload)
                    .done(function(res) {
                        if (res.success && res.data) {
                            if (String(res.data.id) !== String(rowId)) {
                                $('tr[data-row-id="' + rowId + '"]').attr('data-row-id', res.data.id);
                                ctx.row.id = String(res.data.id);
                            }
                            $.extend(ctx.row, res.data);
                            if (key === 'status') applyRowStyle(ctx.row.id);
                        }
                    })
                    .fail(function(xhr) {
                        ctx.row[key] = oldVal;
                        if (key === 'status') applyRowStyle(rowId);
                        var msg = 'Gagal menyimpan perubahan.';
                        if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                        showToast(msg, { icon: 'bi-exclamation-triangle' });
                    });
            }

            $(document).on('change', '.ks-status-select-input', function() {
                var $select = $(this);
                var $td = $select.closest('td');
                var $tr = $td.closest('tr');
                var rowId = $td.attr('data-row-id') || $tr.attr('data-row-id') || $select.data('row-id');
                
                if (!validateRowId(rowId)) return;

                var newVal = $select.val();
                var ctx = findRow(rowId);
                var oldVal = ctx ? ctx.row.status : null;
                
                if (ctx) ctx.row.status = newVal;
                applyRowStyle(rowId);

                apiCall('PATCH', API_BASE + '/update/' + String(rowId).trim(), { status: newVal })
                    .done(function(res) {
                        if (res.success && res.data) {
                            if (String(res.data.id) !== String(rowId)) {
                                $tr.attr('data-row-id', res.data.id);
                                ctx.row.id = String(res.data.id);
                            }
                            $.extend(ctx.row, res.data);
                            applyRowStyle(ctx.row.id);
                        }
                    })
                    .fail(function(xhr) {
                        if (ctx) ctx.row.status = oldVal;
                        applyRowStyle(rowId);
                        showToast('Gagal mengubah status.', { icon: 'bi-exclamation-triangle' });
                    });
            });

            $(document).on('change', '.ks-device-select-input', function() {
                var $td = $(this).closest('td');
                var rowId = $td.attr('data-row-id');
                var key = $td.data('key');
                
                if (!validateRowId(rowId)) return;
                
                var newVal = $(this).val();
                var ctx = findRow(rowId);
                var oldVal = ctx ? ctx.row[key] : null;
                
                if (ctx) ctx.row[key] = newVal;

                var payload = {};
                payload[toApiField(key)] = newVal;

                apiCall('PATCH', API_BASE + '/update/' + String(rowId).trim(), payload)
                    .done(function(res) {
                        if (res.success && res.data) {
                            if (String(res.data.id) !== String(rowId)) {
                                $('tr[data-row-id="' + rowId + '"]').attr('data-row-id', res.data.id);
                                ctx.row.id = String(res.data.id);
                            }
                            $.extend(ctx.row, res.data);
                        }
                    })
                    .fail(function(xhr) {
                        if (ctx) ctx.row[key] = oldVal;
                        var msg = 'Gagal menyimpan perubahan.';
                        if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                        showToast(msg, { icon: 'bi-exclamation-triangle' });
                    });
            });

            $('#ksAddRow').on('click', function() {
                var opts = getPeriodOptions(currentViewMode);
                var period = opts.filter(function(o) { return o.value === currentPeriodValue; })[0] || opts[opts.length - 1];
                if (!period) {
                    showToast('Tidak ada periode aktif.', { icon: 'bi-exclamation-circle' });
                    return;
                }
                var targetWeekStart = period.weeks[period.weeks.length - 1];
                var targetWeek = weeksData[targetWeekStart];
                if (!targetWeek) {
                    showToast('Data minggu tidak ditemukan.', { icon: 'bi-exclamation-circle' });
                    return;
                }

                var tempId = uid('r');
                var tempRow = {
                    id: tempId, kelas: '', dari: '', sampai: '', ruangan: '', device: 'Laptop',
                    deviceInstruktur: '', pax: 0, instruktur: '', asset: '', software: '',
                    keterangan: '', pcits: '', status: 'Biru'
                };
                targetWeek.rows.push(tempRow);
                justAddedRowId = tempId;
                render();

                apiCall('POST', API_BASE + '/store', {
                    week_start: targetWeek.start,
                    week_end: targetWeek.end,
                    kelas: '',
                    device: 'Laptop',
                    status: 'Biru',
                    pax: 0
                }).done(function(res) {
                    if (res.success && res.data) {
                        targetWeek.rows = targetWeek.rows.filter(function(r) { return String(r.id) !== String(tempId); });
                        targetWeek.rows.push(res.data);
                        justAddedRowId = res.data.id;
                        render();
                        showToast('Baris kelas baru ditambahkan.', { icon: 'bi-plus-circle' });
                    }
                }).fail(function() {
                    targetWeek.rows = targetWeek.rows.filter(function(r) { return String(r.id) !== String(tempId); });
                    render();
                    showToast('Gagal menambahkan kelas baru.', { icon: 'bi-exclamation-triangle' });
                });
            });

            renderColMenu();
            loadInitialData().always(function() { render(); });


            // --- LOGIKA RESET DATABASE ---
            const confirmInput = document.getElementById('confirmResetText');
            const btnExecute = document.getElementById('btnExecuteReset');
            const resetError = document.getElementById('resetError');

            // Aktifkan tombol hanya jika user mengetik "HAPUS"
            if (confirmInput) {
                confirmInput.addEventListener('input', function() {
                    if (this.value.trim().toUpperCase() === 'HAPUS') {
                        btnExecute.disabled = false;
                        btnExecute.classList.remove('btn-secondary');
                        btnExecute.classList.add('btn-danger');
                        resetError.classList.add('d-none');
                    } else {
                        btnExecute.disabled = true;
                        btnExecute.classList.remove('btn-danger');
                        btnExecute.classList.add('btn-secondary');
                    }
                });
            }

            // Eksekusi Reset saat tombol diklik
            if (btnExecute) {
                btnExecute.addEventListener('click', function() {
                    const confirmText = confirmInput.value.trim();
                    
                    if (confirmText !== 'HAPUS') {
                        resetError.classList.remove('d-none');
                        return;
                    }

                    // Tampilkan loading pada tombol
                    const originalText = btnExecute.innerHTML;
                    btnExecute.disabled = true;
                    btnExecute.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Menghapus...';

                    // Kirim request ke server
                    $.ajax({
                        url: API_BASE + '/clear-all',
                        method: 'POST',
                        data: {
                            _token: CSRF_TOKEN,
                            confirm_text: confirmText
                        },
                        success: function(res) {
                            if (res.success) {
                                // Tutup modal
                                const modalEl = document.getElementById('modalClearDatabase');
                                const modal = bootstrap.Modal.getInstance(modalEl);
                                modal.hide();
                                
                                // Reset form modal
                                confirmInput.value = '';
                                btnExecute.disabled = true;
                                btnExecute.innerHTML = originalText;
                                
                                // Tampilkan pesan sukses
                                showToast(res.message, { icon: 'bi-check-circle-fill', duration: 3000 });
                                
                                // Refresh data tabel
                                loadInitialData(); 
                            } else {
                                showToast(res.message || 'Gagal menghapus data.', { icon: 'bi-exclamation-triangle' });
                                btnExecute.disabled = false;
                                btnExecute.innerHTML = originalText;
                            }
                        },
                        error: function(xhr) {
                            const msg = xhr.responseJSON?.message || 'Terjadi kesalahan server.';
                            showToast(msg, { icon: 'bi-exclamation-triangle' });
                            btnExecute.disabled = false;
                            btnExecute.innerHTML = originalText;
                        }
                    });
                });

                // Reset state modal saat ditutup
                document.getElementById('modalClearDatabase').addEventListener('hidden.bs.modal', function () {
                    confirmInput.value = '';
                    btnExecute.disabled = true;
                    btnExecute.classList.remove('btn-danger');
                    btnExecute.classList.add('btn-secondary');
                    resetError.classList.add('d-none');
                });
            }
        })(jQuery);
    </script>
@endsection