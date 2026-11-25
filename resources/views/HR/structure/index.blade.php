@extends('layout_HR.app')

@section('title', 'Struktur Organisasi')

@section('content_HR')
    @if (session('status'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('status') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="org-head">
        <div>
            <h4 class="mb-1">Struktur Organisasi</h4>
            <p class="text-muted small mb-0">Seret jabatan lalu lepas di tengah kartu (jadi bawahan), di bagian atas kartu
                (jadi atasannya), atau di tepi kiri/kanan (sejajar). Tahan Shift untuk menambah atasan tambahan.</p>
        </div>
        <div class="dropdown">
            <button class="btn btn-light border btn-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false"
                title="Pengaturan">
                <i class="fa-solid fa-gear"></i>
            </button>
            <div class="dropdown-menu dropdown-menu-end p-3" style="min-width:320px">
                <form method="POST" action="{{ route('HR.structure.toggle.menus') }}"
                    class="d-flex align-items-center justify-content-between gap-3">
                    @csrf
                    @method('PUT')
                    <label class="small mb-0" for="switchMenuStruktur">Tampilkan menu Struktur untuk semua karyawan</label>
                    <div class="form-check form-switch mb-0">
                        <input type="hidden" name="enabled" value="0">
                        <input class="form-check-input" type="checkbox" role="switch" id="switchMenuStruktur"
                            name="enabled" value="1" @checked($menuEnabled) onchange="this.form.submit()">
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="org-tabs" id="chartTabs"></div>

    <div class="card org-card" id="mainCard">
        <div class="org-toolbar">
            <input type="text" id="chartName" class="org-chart-name" maxlength="255" placeholder="Nama struktur"
                aria-label="Nama struktur">
            <div class="org-tools">
                <div class="org-search">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" id="searchInput" placeholder="Cari jabatan" autocomplete="off">
                </div>
                <div class="org-group">
                    <button type="button" class="btn btn-light btn-sm" id="btnZoomOut" title="Perkecil"><i
                            class="fa-solid fa-minus"></i></button>
                    <span class="org-zoom" id="zoomLabel">100%</span>
                    <button type="button" class="btn btn-light btn-sm" id="btnZoomIn" title="Perbesar"><i
                            class="fa-solid fa-plus"></i></button>
                    <button type="button" class="btn btn-light btn-sm" id="btnFit" title="Sesuaikan layar"><i
                            class="fa-solid fa-expand"></i></button>
                    <button type="button" class="btn btn-light btn-sm" id="btnResetPos"
                        title="Kembalikan posisi otomatis"><i class="fa-solid fa-rotate-left"></i></button>
                </div>
                <div class="org-group">
                    <button type="button" class="btn btn-light btn-sm" id="btnFullscreen" title="Layar penuh"><i
                            class="fa-solid fa-maximize"></i></button>
                    <button type="button" class="btn btn-light btn-sm" id="btnExport" title="Unduh PNG"><i
                            class="fa-solid fa-file-arrow-down"></i></button>
                    <button type="button" class="btn btn-light btn-sm text-danger" id="btnDeleteChart"
                        title="Hapus struktur ini"><i class="fa-solid fa-trash"></i></button>
                </div>
                <button type="button" class="btn btn-primary btn-sm" id="btnAddNode"><i
                        class="fa-solid fa-plus me-1"></i>Tambah jabatan</button>
            </div>
        </div>

        <div class="org-stats" id="orgStats">
            <span><b id="statJabatan">0</b> jabatan</span>
            <span><b id="statKaryawan">0</b> karyawan</span>
            <span><b id="statLevel">0</b> level</span>
            <span><b id="statKosong">0</b> belum terisi</span>
        </div>

        <div class="org-stage-wrap" id="stageWrap">
            <div class="org-viewport" id="viewport">
                <div class="org-sizer" id="sizer">
                    <div class="org-stage" id="stage"></div>
                </div>
            </div>
            <div class="root-drop" id="rootDrop"><i class="fa-solid fa-arrow-up me-2"></i>Lepas di sini untuk menjadikan
                jabatan teratas</div>
            <div class="org-empty d-none" id="emptyState">
                <i class="fa-solid fa-sitemap"></i>
                <p>Struktur ini masih kosong</p>
                <button type="button" class="btn btn-primary btn-sm" id="btnAddFirst">Tambah jabatan pertama</button>
            </div>
            <div class="org-loading d-none" id="loadingMask">
                <div class="spinner-border text-primary"></div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalNode" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="nodeModalTitle">Tambah jabatan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3" id="nodeSourceGroup">
                        <label class="form-label">Pilih dari jabatan yang ada</label>
                        <select id="nodeSource" class="form-select"></select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nama jabatan</label>
                        <input type="text" id="nodeJabatan" class="form-control" maxlength="255">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Divisi</label>
                        <input type="text" id="nodeDivisi" class="form-control" maxlength="255">
                    </div>
                    <div class="mb-3" id="nodeParentGroup">
                        <label class="form-label">Atasan</label>
                        <select id="nodeParent" class="form-select"></select>
                    </div>
                    <div>
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label mb-0">Karyawan</label>
                            <small class="text-muted" id="nodeKarCount">0 dipilih</small>
                        </div>
                        <input type="text" id="nodeKarSearch" class="form-control form-control-sm mb-2"
                            placeholder="Cari nama atau NIP">
                        <div class="kar-list" id="nodeKarList"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" id="btnSaveNode">Simpan</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalDetail" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detail jabatan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="detailBody"></div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalParents" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Atasan tambahan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Jabatan</label>
                        <input type="text" id="parentsName" class="form-control" disabled>
                    </div>
                    <div>
                        <label class="form-label">Atasan tambahan</label>
                        <select id="parentsSelect" class="form-select" multiple size="8"></select>
                        <small class="text-muted">Tahan Ctrl (Windows) atau Cmd (Mac) untuk memilih lebih dari satu. Garis
                            putus-putus menandai atasan tambahan.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" id="btnSaveParents">Simpan</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalChart" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Struktur baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <label class="form-label">Nama struktur</label>
                    <input type="text" id="newChartName" class="form-control" maxlength="255">
                    <small class="text-muted">Struktur baru dimulai kosong. Jabatan ditambahkan satu per satu.</small>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" id="btnSaveChart">Buat struktur</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalConfirm" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmTitle">Konfirmasi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="confirmBody"></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-danger" id="btnConfirmOk">Hapus</button>
                </div>
            </div>
        </div>
    </div>

    <div class="drag-hint d-none" id="dragHint"></div>
@endsection

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"
        integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        .org-head {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .org-tabs {
            display: flex;
            gap: .4rem;
            overflow-x: auto;
            padding-bottom: .5rem;
            margin-bottom: .25rem;
            scrollbar-width: thin;
        }

        .org-tab {
            flex: 0 0 auto;
            border: 1px solid #e2e8f0;
            background: #fff;
            color: #475569;
            font-size: .85rem;
            font-weight: 500;
            padding: .45rem .95rem;
            border-radius: 8px 8px 0 0;
            border-bottom-color: transparent;
            max-width: 240px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .org-tab:hover {
            background: #f8fafc;
        }

        .org-tab.active {
            color: #1e293b;
            font-weight: 600;
            box-shadow: inset 0 3px 0 #4f46e5;
        }

        .org-tab-add {
            border-style: dashed;
            color: #4f46e5;
            background: transparent;
        }

        .org-card {
            border: 1px solid #e2e8f0;
            border-radius: 0 12px 12px 12px;
            box-shadow: none;
            background: #fff;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .org-toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: .75rem;
            padding: .85rem 1.1rem;
            border-bottom: 1px solid #eef2f7;
        }

        .org-chart-name {
            border: 0;
            border-bottom: 2px solid transparent;
            background: transparent;
            font-size: 1.15rem;
            font-weight: 700;
            color: #0f172a;
            padding: .15rem .25rem;
            min-width: 220px;
            max-width: 420px;
            flex: 1 1 260px;
            outline: none;
            border-radius: 4px;
        }

        .org-chart-name:hover {
            background: #f8fafc;
        }

        .org-chart-name:focus {
            border-bottom-color: #4f46e5;
            background: #fff;
        }

        .org-tools {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: .6rem;
        }

        .org-group {
            display: flex;
            align-items: center;
            gap: .15rem;
            padding: 2px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            background: #fff;
        }

        .org-group .btn {
            border: 0;
        }

        .org-zoom {
            min-width: 46px;
            text-align: center;
            font-size: .78rem;
            font-weight: 600;
            color: #475569;
        }

        .org-search {
            position: relative;
        }

        .org-search i {
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
            font-size: .75rem;
            color: #94a3b8;
        }

        .org-search input {
            width: 180px;
            height: 31px;
            font-size: .82rem;
            padding: 0 .6rem 0 28px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            outline: none;
        }

        .org-search input:focus {
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, .12);
        }

        .org-stats {
            display: flex;
            gap: 1.5rem;
            flex-wrap: wrap;
            padding: .5rem 1.1rem;
            font-size: .78rem;
            color: #64748b;
            border-bottom: 1px solid #eef2f7;
            background: #fcfdfe;
        }

        .org-stats b {
            color: #0f172a;
            font-weight: 700;
        }

        .org-stage-wrap {
            position: relative;
            height: calc(100vh - 330px);
            min-height: 480px;
        }

        .org-viewport {
            position: absolute;
            inset: 0;
            overflow: auto;
            cursor: grab;
            background:
                radial-gradient(circle, #d6dce6 1px, transparent 1px) 0 0 / 24px 24px,
                #f8fafc;
        }

        .org-viewport.panning {
            cursor: grabbing;
        }

        .org-sizer {
            position: relative;
            min-width: 100%;
            min-height: 100%;
        }

        .org-stage {
            position: absolute;
            left: 0;
            top: 0;
            transform-origin: 0 0;
        }

        .org-connectors {
            position: absolute;
            left: 0;
            top: 0;
            overflow: visible;
            pointer-events: none;
        }

        .org-node {
            position: absolute;
            width: 220px;
            height: 128px;
            display: flex;
            flex-direction: column;
            background: #fff;
            border: 1px solid #dbe2ec;
            border-top: 3px solid var(--accent, #4f46e5);
            border-radius: 12px;
            box-shadow: 0 1px 2px rgba(15, 23, 42, .06);
            user-select: none;
            -webkit-user-select: none;
            touch-action: none;
            cursor: grab;
            z-index: 2;
            transition: box-shadow .15s, border-color .15s, opacity .15s;
        }

        .org-node:hover {
            box-shadow: 0 6px 18px rgba(15, 23, 42, .12);
        }

        .org-node-body {
            flex: 1;
            min-height: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 3px;
            padding: 8px 12px 4px;
            text-align: center;
        }

        .org-node-title {
            font-weight: 700;
            font-size: .85rem;
            line-height: 1.25;
            color: #0f172a;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            word-break: break-word;
        }

        .org-node-sub {
            font-size: .72rem;
            color: #64748b;
            max-width: 100%;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .org-node-meta {
            display: flex;
            gap: 6px;
            margin-top: 3px;
        }

        .org-pill {
            font-size: .68rem;
            font-weight: 600;
            padding: 2px 9px;
            border-radius: 999px;
            background: #eef2ff;
            color: #4338ca;
        }

        .org-pill.muted {
            background: #f1f5f9;
            color: #94a3b8;
        }

        .org-pill.link {
            background: #fef3c7;
            color: #b45309;
        }

        .org-node-actions {
            display: flex;
            border-top: 1px solid #eef2f7;
            border-radius: 0 0 10px 10px;
            overflow: hidden;
        }

        .org-node-actions button {
            flex: 1;
            height: 30px;
            border: 0;
            background: transparent;
            color: #64748b;
            font-size: .75rem;
            cursor: pointer;
        }

        .org-node-actions button+button {
            border-left: 1px solid #eef2f7;
        }

        .org-node-actions button:hover {
            background: #f1f5f9;
            color: #4f46e5;
        }

        .org-node-actions button[data-act="delete"]:hover {
            background: #fef2f2;
            color: #dc2626;
        }

        .org-node.is-source {
            opacity: .3;
        }

        .org-node.highlighted {
            border-color: #f59e0b;
            box-shadow: 0 0 0 3px rgba(245, 158, 11, .25);
        }

        .org-node.dimmed {
            opacity: .28;
        }

        .org-node.drop-child {
            border-color: #16a34a;
            box-shadow: 0 0 0 4px rgba(22, 163, 74, .18);
        }

        .org-node.drop-invalid {
            border-color: #dc2626;
            box-shadow: 0 0 0 4px rgba(220, 38, 38, .15);
        }

        .org-node.drop-before::before,
        .org-node.drop-after::after {
            content: "";
            position: absolute;
            top: -6px;
            bottom: -6px;
            width: 5px;
            border-radius: 3px;
            background: #16a34a;
        }

        .org-node.drop-before::before {
            left: -14px;
        }

        .org-node.drop-after::after {
            right: -14px;
        }

        .org-node.drop-above::before {
            content: "";
            position: absolute;
            left: -6px;
            right: -6px;
            top: -14px;
            height: 5px;
            border-radius: 3px;
            background: #16a34a;
        }

        .org-node.drop-link {
            border-color: #f59e0b;
            box-shadow: 0 0 0 4px rgba(245, 158, 11, .22);
        }

        .org-ghost {
            position: fixed !important;
            z-index: 100000;
            pointer-events: none;
            opacity: .92;
            margin: 0;
            transform-origin: 0 0;
            box-shadow: 0 14px 34px rgba(15, 23, 42, .28);
        }

        .drag-hint {
            position: fixed;
            z-index: 100001;
            pointer-events: none;
            background: #0f172a;
            color: #fff;
            font-size: .72rem;
            padding: 4px 9px;
            border-radius: 6px;
            white-space: nowrap;
        }

        .root-drop {
            position: absolute;
            top: 12px;
            left: 50%;
            transform: translateX(-50%);
            padding: 10px 22px;
            border: 2px dashed #94a3b8;
            border-radius: 10px;
            background: rgba(255, 255, 255, .95);
            color: #475569;
            font-size: .8rem;
            font-weight: 500;
            z-index: 20;
            opacity: 0;
            pointer-events: none;
            transition: opacity .15s;
        }

        .root-drop.show {
            opacity: 1;
            pointer-events: auto;
        }

        .root-drop.hot {
            border-color: #16a34a;
            background: #f0fdf4;
            color: #166534;
        }

        .org-empty {
            position: absolute;
            inset: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: .5rem;
            color: #94a3b8;
            pointer-events: none;
        }

        .org-empty i {
            font-size: 2.2rem;
        }

        .org-empty p {
            margin: 0 0 .5rem;
            font-size: .9rem;
        }

        .org-empty .btn {
            pointer-events: auto;
        }

        .org-loading {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(248, 250, 252, .7);
            z-index: 30;
        }

        .org-card.is-fullscreen {
            position: fixed !important;
            inset: 8px;
            z-index: 99990;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(15, 23, 42, .25);
        }

        .org-card.is-fullscreen .org-stage-wrap {
            flex: 1;
            height: auto;
            min-height: 0;
        }

        .org-export-holder {
            position: absolute;
            left: 0;
            top: 0;
            z-index: -1;
            pointer-events: none;
            overflow: hidden;
            background: #f8fafc;
        }

        .org-export-title {
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.6rem;
            font-weight: 700;
            color: #0f172a;
            background: #fff;
            border-bottom: 1px solid #e2e8f0;
        }

        .kar-list {
            max-height: 220px;
            overflow-y: auto;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
        }

        .kar-item {
            display: flex;
            align-items: center;
            gap: .6rem;
            padding: .45rem .75rem;
            margin: 0;
            font-size: .85rem;
            cursor: pointer;
            border-bottom: 1px solid #f1f5f9;
        }

        .kar-item:last-child {
            border-bottom: 0;
        }

        .kar-item:hover {
            background: #f8fafc;
        }

        .kar-name {
            flex: 1;
            font-weight: 500;
        }

        .kar-nip {
            font-size: .72rem;
            color: #94a3b8;
        }

        .kar-empty {
            padding: 1rem;
            text-align: center;
            color: #94a3b8;
            font-size: .82rem;
        }

        .detail-grid {
            display: grid;
            grid-template-columns: 150px 1fr;
            gap: .5rem 1rem;
            margin: 0 0 1.25rem;
            font-size: .88rem;
        }

        .detail-grid dt {
            color: #64748b;
            font-weight: 500;
        }

        .detail-grid dd {
            margin: 0;
            font-weight: 600;
            color: #0f172a;
        }

        .detail-title {
            font-weight: 700;
            font-size: .9rem;
            margin-bottom: .6rem;
        }

        .member {
            display: flex;
            align-items: center;
            gap: .75rem;
            padding: .55rem .75rem;
            border: 1px solid #eef2f7;
            border-radius: 10px;
            margin-bottom: .5rem;
        }

        .member-avatar {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            flex-shrink: 0;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #4f46e5;
            color: #fff;
            font-weight: 700;
            font-size: .9rem;
        }

        .member-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .member-name {
            font-weight: 600;
            font-size: .88rem;
        }

        .member-meta {
            font-size: .74rem;
            color: #64748b;
        }

        @media (max-width: 768px) {
            .org-stage-wrap {
                height: calc(100vh - 420px);
                min-height: 380px;
            }

            .org-search input {
                width: 140px;
            }
        }

        .org-node.is-moving {
            pointer-events: none;
            box-shadow: 0 10px 26px rgba(15, 23, 42, .22);
        }

        .org-guides {
            position: absolute;
            left: 0;
            top: 0;
            overflow: visible;
            pointer-events: none;
            z-index: 5;
        }
    </style>
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const NS = 'http://www.w3.org/2000/svg';
            const NODE_W = 220;
            const NODE_H = 128;
            const GAP_X = 36;
            const GAP_Y = 84;
            const PAD = 64;
            const ZOOM_MIN = 0.15;
            const ZOOM_MAX = 2;
            const ZOOM_STEP = 0.1;
            const DRAG_THRESHOLD = 6;
            const STORE_KEY = 'hr_structure_active_chart';
            const ACCENTS = ['#4f46e5', '#0ea5e9', '#10b981', '#f59e0b', '#ec4899', '#8b5cf6'];

            const routes = {
                chartStore: "{{ route('HR.structure.charts.store') }}",
                chartUpdate: "{{ route('HR.structure.charts.update', ['chart' => '__ID__']) }}",
                chartDestroy: "{{ route('HR.structure.charts.destroy', ['chart' => '__ID__']) }}",
                chartTree: "{{ route('HR.structure.charts.tree', ['chart' => '__ID__']) }}",
                nodeStore: "{{ route('HR.structure.nodes.store', ['chart' => '__ID__']) }}",
                nodeUpdate: "{{ route('HR.structure.nodes.update', ['id' => '__ID__']) }}",
                nodeDestroy: "{{ route('HR.structure.nodes.destroy', ['id' => '__ID__']) }}",
                nodeMove: "{{ route('HR.structure.nodes.move', ['id' => '__ID__']) }}",
                nodeParents: "{{ route('HR.structure.nodes.parents', ['id' => '__ID__']) }}",
                karyawans: "{{ route('HR.structure.karyawans') }}",
                jabatans: "{{ route('HR.structure.jabatans') }}",
                positions: "{{ route('HR.structure.nodes.positions') }}",
                resetPositions: "{{ route('HR.structure.charts.reset.positions', ['chart' => '__ID__']) }}",
            };

            const initialCharts = @json($charts);
            const csrf = document.querySelector('meta[name="csrf-token"]').content;

            const $ = (id) => document.getElementById(id);
            const viewport = $('viewport');
            const sizer = $('sizer');
            const stage = $('stage');
            const rootDrop = $('rootDrop');
            const dragHint = $('dragHint');
            const chartName = $('chartName');
            const mainCard = $('mainCard');

            const state = {
                charts: [],
                chartId: null,
                tree: [],
                flat: [],
                byId: new Map(),
                layout: new Map(),
                W: 0,
                H: 0,
                zoom: 1,
            };

            let drag = null;
            let pan = null;
            let confirmFn = null;
            let nodeCtx = {
                mode: 'add',
                id: null
            };
            let peopleCache = [];
            let jabatanCache = [];
            let selectedPeople = new Set();
            let parentsTarget = null;

            const url = (tpl, id) => tpl.replace('__ID__', encodeURIComponent(id));
            const clamp = (v, a, b) => Math.min(b, Math.max(a, v));
            const esc = (s) => String(s ?? '').replace(/[&<>"']/g, (c) => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#39;'
            } [c]));
            const modal = (id) => bootstrap.Modal.getOrCreateInstance($(id));

            const toast = (type, msg) => {
                const el = document.createElement('div');
                el.className =
                    `alert alert-${type === 'success' ? 'success' : 'danger'} position-fixed bottom-0 end-0 m-3 shadow-sm`;
                el.style.cssText = 'z-index:100002;min-width:260px;max-width:420px;font-size:.88rem';
                el.textContent = msg;
                document.body.appendChild(el);
                setTimeout(() => el.remove(), 4000);
            };

            const api = async (target, method = 'GET', body = null) => {
                const res = await fetch(target, {
                    method,
                    credentials: 'same-origin',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: body ? JSON.stringify(body) : null,
                });
                let data = null;
                try {
                    data = await res.json();
                } catch (e) {
                    data = null;
                }
                if (!res.ok) {
                    let msg = data && data.message ? data.message : `Terjadi kesalahan (${res.status})`;
                    if (data && data.errors) msg = Object.values(data.errors).flat()[0];
                    throw new Error(msg);
                }
                return data;
            };

            const setLoading = (on) => $('loadingMask').classList.toggle('d-none', !on);

            const renderTabs = () => {
                $('chartTabs').innerHTML = state.charts.map((c) =>
                        `<button type="button" class="org-tab ${c.id === state.chartId ? 'active' : ''}" data-id="${c.id}" title="${esc(c.name)}">${esc(c.name)}</button>`
                    ).join('') +
                    `<button type="button" class="org-tab org-tab-add" id="btnNewChart"><i class="fa-solid fa-plus me-1"></i>Struktur baru</button>`;
            };

            const indexTree = () => {
                state.flat = [];
                state.byId = new Map();
                const walk = (nodes) => nodes.forEach((n) => {
                    state.flat.push(n);
                    state.byId.set(n.id, n);
                    walk(n.children || []);
                });
                walk(state.tree);
            };

            const subtreeIds = (id) => {
                const out = new Set();
                const walk = (n) => {
                    out.add(n.id);
                    (n.children || []).forEach(walk);
                };
                const root = state.byId.get(id);
                if (root) walk(root);
                return out;
            };

            const parentIdsOf = (n) => {
                const ids = [n.parent_id, ...(n.additional_parents || [])]
                    .filter((v) => v !== null && v !== undefined)
                    .map(Number)
                    .filter((v) => v !== n.id && state.byId.has(v));
                return [...new Set(ids)];
            };

            const computeLayout = () => {
                state.layout = new Map();
                state.layoutParent = new Map();
                const widths = new Map();
                const depths = new Map();
                const visiting = new Set();
                const usable = new Map();
                const seen = new Set();
                const kids = new Map();
                const roots = [];
                const order = (a, b) => (a.sort_order - b.sort_order) || (a.id - b.id);

                const depthOf = (n) => {
                    if (depths.has(n.id)) return depths.get(n.id);
                    if (visiting.has(n.id)) return 0;
                    visiting.add(n.id);
                    const ps = parentIdsOf(n);
                    const d = ps.length ? 1 + Math.max(...ps.map((p) => depthOf(state.byId.get(p)))) : 0;
                    visiting.delete(n.id);
                    depths.set(n.id, d);
                    return d;
                };

                state.flat.forEach((n) => depthOf(n));

                state.flat.forEach((n) => {
                    const ps = parentIdsOf(n);
                    if (!ps.length) {
                        roots.push(n);
                        return;
                    }
                    if (ps.length > 1) return;
                    state.layoutParent.set(n.id, ps[0]);
                    if (!kids.has(ps[0])) kids.set(ps[0], []);
                    kids.get(ps[0]).push(n);
                });

                roots.sort(order);
                kids.forEach((list) => list.sort(order));

                const measure = (n) => {
                    seen.add(n.id);
                    const list = [];
                    let total = 0;
                    (kids.get(n.id) || []).forEach((c) => {
                        if (seen.has(c.id)) return;
                        total += (list.length ? GAP_X : 0) + measure(c);
                        list.push(c);
                    });
                    usable.set(n.id, list);
                    const w = Math.max(NODE_W, total);
                    widths.set(n.id, w);
                    return w;
                };

                const place = (n, left) => {
                    const sw = widths.get(n.id);
                    const depth = depths.get(n.id) || 0;
                    state.layout.set(n.id, {
                        x: left + (sw - NODE_W) / 2,
                        y: depth * (NODE_H + GAP_Y),
                        depth
                    });
                    const list = usable.get(n.id) || [];
                    const childrenW = list.reduce((a, c, i) => a + widths.get(c.id) + (i ? GAP_X : 0), 0);
                    let cx = left + (sw - childrenW) / 2;
                    list.forEach((c) => {
                        place(c, cx);
                        cx += widths.get(c.id) + GAP_X;
                    });
                };

                let cursor = 0;
                const placeRoot = (root) => {
                    if (seen.has(root.id)) return;
                    measure(root);
                    place(root, cursor);
                    cursor += widths.get(root.id) + GAP_X;
                };

                roots.forEach(placeRoot);

                const collect = (n, out) => {
                    out.push(n.id);
                    (usable.get(n.id) || []).forEach((c) => collect(c, out));
                    return out;
                };

                const pending = state.flat
                    .filter((n) => parentIdsOf(n).length > 1)
                    .sort((a, b) => depths.get(a.id) - depths.get(b.id));

                let progress = true;
                while (pending.length && progress) {
                    progress = false;
                    for (let i = 0; i < pending.length; i++) {
                        const n = pending[i];
                        const parentPos = parentIdsOf(n).map((p) => state.layout.get(p));
                        if (parentPos.some((p) => !p)) continue;

                        const centers = parentPos.map((p) => p.x + NODE_W / 2);
                        const target = (Math.min(...centers) + Math.max(...centers)) / 2;
                        const sw = measure(n);
                        place(n, target - sw / 2);

                        const ids = new Set(collect(n, []));
                        const mine = [...ids].map((id) => state.layout.get(id));

                        for (let guard = 0; guard < 300; guard++) {
                            let shift = 0;
                            state.layout.forEach((b, bid) => {
                                if (ids.has(bid)) return;
                                mine.forEach((a) => {
                                    if (a.depth !== b.depth) return;
                                    if (a.x < b.x + NODE_W + GAP_X && b.x < a.x + NODE_W +
                                        GAP_X) {
                                        shift = Math.max(shift, b.x + NODE_W + GAP_X - a.x);
                                    }
                                });
                            });
                            if (!shift) break;
                            mine.forEach((a) => {
                                a.x += shift;
                            });
                        }

                        pending.splice(i, 1);
                        i--;
                        progress = true;
                    }
                }

                state.flat.forEach(placeRoot);

                if (!state.layout.size) {
                    state.W = 0;
                    state.H = 0;
                    return;
                }

                state.flat.forEach((n) => {
                    const p = state.layout.get(n.id);
                    if (!p) return;
                    p.x += Number(n.pos_x) || 0;
                    p.y += Number(n.pos_y) || 0;
                });

                let minX = Infinity,
                    maxX = -Infinity,
                    minY = Infinity,
                    maxY = -Infinity;
                state.layout.forEach((p) => {
                    minX = Math.min(minX, p.x);
                    maxX = Math.max(maxX, p.x + NODE_W);
                    minY = Math.min(minY, p.y);
                    maxY = Math.max(maxY, p.y + NODE_H);
                });

                const shiftX = PAD - minX;
                const shiftY = PAD - minY;
                state.layout.forEach((p) => {
                    p.x += shiftX;
                    p.y += shiftY;
                });

                state.W = Math.ceil(maxX - minX + PAD * 2);
                state.H = Math.ceil(maxY - minY + PAD * 2);
            };

            const connectorPath = (x1, y1, x2, y2, bias) => {
                if (x1 === x2) return `M ${x1} ${y1} V ${y2}`;
                const my = y1 + (y2 - y1) / 2 + bias;
                const dir = Math.sign(x2 - x1);
                const sy1 = Math.sign(my - y1) || 1;
                const sy2 = Math.sign(y2 - my) || 1;
                const r = Math.max(0, Math.min(10, Math.abs(x2 - x1) / 2, Math.abs(my - y1), Math.abs(y2 -
                my)));
                return `M ${x1} ${y1} V ${my - sy1 * r} Q ${x1} ${my} ${x1 + dir * r} ${my} H ${x2 - dir * r} Q ${x2} ${my} ${x2} ${my + sy2 * r} V ${y2}`;
            };

            const linkPath = (pp, cp) => {
                const pcx = pp.x + NODE_W / 2;
                const ccx = cp.x + NODE_W / 2;
                const pBottom = pp.y + NODE_H;
                const cBottom = cp.y + NODE_H;

                if (cp.y >= pBottom + 16) return connectorPath(pcx, pBottom, ccx, cp.y, 0);
                if (cBottom + 16 <= pp.y) return connectorPath(pcx, pp.y, ccx, cBottom, 0);

                const right = ccx >= pcx;
                const x1 = right ? pp.x + NODE_W : pp.x;
                const y1 = pp.y + NODE_H / 2;
                const x2 = right ? cp.x : cp.x + NODE_W;
                const y2 = cp.y + NODE_H / 2;
                if (right ? x2 > x1 + 8 : x2 < x1 - 8) {
                    const mx = (x1 + x2) / 2;
                    return `M ${x1} ${y1} H ${mx} V ${y2} H ${x2}`;
                }
                return `M ${pcx} ${y1} L ${ccx} ${y2}`;
            };

            const drawConnectors = (svg, layout = state.layout) => {
                const addPath = (d, color, dashed) => {
                    const p = document.createElementNS(NS, 'path');
                    p.setAttribute('d', d);
                    p.setAttribute('fill', 'none');
                    p.setAttribute('stroke', color);
                    p.setAttribute('stroke-width', '2');
                    p.setAttribute('stroke-linecap', 'round');
                    p.setAttribute('stroke-linejoin', 'round');
                    if (dashed) p.setAttribute('stroke-dasharray', '6 5');
                    svg.appendChild(p);
                };

                state.flat.forEach((n) => {
                    const cp = layout.get(n.id);
                    if (!cp) return;
                    parentIdsOf(n).forEach((pid) => {
                        const pp = layout.get(pid);
                        if (!pp) return;
                        addPath(linkPath(pp, cp), '#a5b4fc', false);
                    });
                });
            };

            const buildNode = (n) => {
                const p = state.layout.get(n.id);
                const el = document.createElement('div');
                const count = n.karyawans.length;
                const extra = (n.additional_parents || []).length;
                el.className = 'org-node';
                el.dataset.id = n.id;
                el.style.left = p.x + 'px';
                el.style.top = p.y + 'px';
                el.style.setProperty('--accent', ACCENTS[p.depth % ACCENTS.length]);
                el.innerHTML = `
                    <div class="org-node-body">
                        <div class="org-node-title" title="${esc(n.jabatan)}">${esc(n.jabatan)}</div>
                        ${n.divisi ? `<div class="org-node-sub">${esc(n.divisi)}</div>` : ''}
                        <div class="org-node-meta">
                            <span class="org-pill ${count ? '' : 'muted'}"><i class="fa-solid fa-user me-1"></i>${count}</span>
                            ${extra ? `<span class="org-pill link" title="Atasan tambahan"><i class="fa-solid fa-code-branch me-1"></i>${extra}</span>` : ''}
                        </div>
                    </div>
                    <div class="org-node-actions">
                        <button type="button" data-act="detail" title="Detail"><i class="fa-solid fa-circle-info"></i></button>
                        <button type="button" data-act="add" title="Tambah bawahan"><i class="fa-solid fa-plus"></i></button>
                        <button type="button" data-act="edit" title="Ubah"><i class="fa-solid fa-pen"></i></button>
                        <button type="button" data-act="parents" title="Atasan tambahan"><i class="fa-solid fa-code-branch"></i></button>
                        <button type="button" data-act="delete" title="Hapus"><i class="fa-solid fa-trash"></i></button>
                    </div>`;
                return el;
            };

            const render = () => {
                stage.innerHTML = '';
                const empty = !state.flat.length;
                $('emptyState').classList.toggle('d-none', !empty);
                stage.style.display = empty ? 'none' : '';
                if (empty) {
                    stage.style.width = '0px';
                    stage.style.height = '0px';
                    return;
                }

                stage.style.width = state.W + 'px';
                stage.style.height = state.H + 'px';

                const svg = document.createElementNS(NS, 'svg');
                svg.setAttribute('xmlns', NS);
                svg.setAttribute('class', 'org-connectors');
                svg.setAttribute('width', state.W);
                svg.setAttribute('height', state.H);
                svg.setAttribute('viewBox', `0 0 ${state.W} ${state.H}`);
                stage.appendChild(svg);
                drawConnectors(svg);

                state.flat.forEach((n) => stage.appendChild(buildNode(n)));
            };

            const updateStats = () => {
                const people = new Set();
                let empty = 0;
                let depth = 0;
                state.flat.forEach((n) => {
                    n.karyawans.forEach((k) => people.add(k.id));
                    if (!n.karyawans.length) empty++;
                    const p = state.layout.get(n.id);
                    if (p) depth = Math.max(depth, p.depth + 1);
                });
                $('statJabatan').textContent = state.flat.length;
                $('statKaryawan').textContent = people.size;
                $('statLevel').textContent = depth;
                $('statKosong').textContent = empty;
            };

            const applyView = () => {
                const sw = state.W * state.zoom;
                const sh = state.H * state.zoom;
                sizer.style.width = sw + 'px';
                sizer.style.height = sh + 'px';
                const cw = viewport.clientWidth;
                const ch = viewport.clientHeight;
                stage.style.transform = `scale(${state.zoom})`;
                stage.style.left = Math.max(0, (cw - sw) / 2) + 'px';
                stage.style.top = Math.max(0, (ch - sh) / 2) + 'px';
                $('zoomLabel').textContent = Math.round(state.zoom * 100) + '%';
            };

            const zoomAt = (z, cx, cy) => {
                z = Math.round(clamp(z, ZOOM_MIN, ZOOM_MAX) * 100) / 100;
                if (z === state.zoom) return;
                const px = (viewport.scrollLeft + cx - stage.offsetLeft) / state.zoom;
                const py = (viewport.scrollTop + cy - stage.offsetTop) / state.zoom;
                state.zoom = z;
                applyView();
                viewport.scrollLeft = px * z + stage.offsetLeft - cx;
                viewport.scrollTop = py * z + stage.offsetTop - cy;
            };

            const zoomCenter = (z) => zoomAt(z, viewport.clientWidth / 2, viewport.clientHeight / 2);

            const fit = () => {
                if (!state.flat.length) {
                    state.zoom = 1;
                    applyView();
                    return;
                }
                const z = Math.min((viewport.clientWidth - 32) / state.W, (viewport.clientHeight - 32) / state
                    .H, 1);
                state.zoom = Math.round(clamp(z, ZOOM_MIN, 1) * 100) / 100;
                applyView();
                viewport.scrollLeft = 0;
                viewport.scrollTop = 0;
            };

            const centerOn = (id) => {
                const p = state.layout.get(id);
                if (!p) return;
                viewport.scrollTo({
                    left: stage.offsetLeft + (p.x + NODE_W / 2) * state.zoom - viewport.clientWidth / 2,
                    top: stage.offsetTop + (p.y + NODE_H / 2) * state.zoom - viewport.clientHeight / 2,
                    behavior: 'smooth',
                });
            };

            const loadChart = async (id, keepView = false) => {
                const wasEmpty = !state.flat.length;
                state.chartId = id;
                try {
                    localStorage.setItem(STORE_KEY, String(id));
                } catch (e) {}
                renderTabs();
                setLoading(true);
                try {
                    const data = await api(url(routes.chartTree, id));
                    state.tree = data.tree || [];
                    chartName.value = data.chart.name;
                    indexTree();
                    computeLayout();
                    render();
                    updateStats();
                    $('searchInput').value = '';
                    if (!keepView || wasEmpty) fit();
                    else applyView();
                } catch (e) {
                    toast('error', e.message);
                } finally {
                    setLoading(false);
                }
            };

            $('chartTabs').addEventListener('click', (e) => {
                const tab = e.target.closest('.org-tab');
                if (!tab) return;
                if (tab.id === 'btnNewChart') {
                    $('newChartName').value = '';
                    modal('modalChart').show();
                    return;
                }
                const id = Number(tab.dataset.id);
                if (id !== state.chartId) loadChart(id);
            });

            $('modalChart').addEventListener('shown.bs.modal', () => $('newChartName').focus());
            $('newChartName').addEventListener('keydown', (e) => {
                if (e.key === 'Enter') $('btnSaveChart').click();
            });

            $('btnSaveChart').addEventListener('click', async () => {
                const name = $('newChartName').value.trim();
                if (!name) return toast('error', 'Nama struktur wajib diisi');
                const btn = $('btnSaveChart');
                btn.disabled = true;
                try {
                    const r = await api(routes.chartStore, 'POST', {
                        name
                    });
                    state.charts.push(r.data);
                    modal('modalChart').hide();
                    toast('success', r.message);
                    await loadChart(r.data.id);
                } catch (e) {
                    toast('error', e.message);
                } finally {
                    btn.disabled = false;
                }
            });

            const renameChart = async () => {
                const name = chartName.value.trim();
                const chart = state.charts.find((c) => c.id === state.chartId);
                if (!chart) return;
                if (!name) {
                    chartName.value = chart.name;
                    return;
                }
                if (name === chart.name) return;
                try {
                    const r = await api(url(routes.chartUpdate, chart.id), 'PUT', {
                        name
                    });
                    chart.name = r.data.name;
                    renderTabs();
                    toast('success', r.message);
                } catch (e) {
                    chartName.value = chart.name;
                    toast('error', e.message);
                }
            };

            chartName.addEventListener('change', renameChart);
            chartName.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') chartName.blur();
            });

            const confirmAsk = (title, html, okText, fn) => {
                $('confirmTitle').textContent = title;
                $('confirmBody').innerHTML = html;
                $('btnConfirmOk').textContent = okText;
                confirmFn = fn;
                modal('modalConfirm').show();
            };

            $('btnConfirmOk').addEventListener('click', async () => {
                if (!confirmFn) return;
                const fn = confirmFn;
                const btn = $('btnConfirmOk');
                btn.disabled = true;
                try {
                    await fn();
                    modal('modalConfirm').hide();
                } catch (e) {
                    toast('error', e.message);
                } finally {
                    btn.disabled = false;
                }
            });

            $('btnDeleteChart').addEventListener('click', () => {
                const chart = state.charts.find((c) => c.id === state.chartId);
                if (!chart) return;
                if (state.charts.length <= 1) return toast('error', 'Minimal harus ada satu struktur.');
                confirmAsk('Hapus struktur',
                    `Hapus struktur <b>${esc(chart.name)}</b> beserta seluruh jabatan di dalamnya? Tindakan ini tidak bisa dibatalkan.`,
                    'Hapus struktur', async () => {
                        const r = await api(url(routes.chartDestroy, chart.id), 'DELETE');
                        state.charts = state.charts.filter((c) => c.id !== chart.id);
                        toast('success', r.message);
                        state.flat = [];
                        await loadChart(state.charts[0].id);
                    });
            });

            const renderPeople = () => {
                const html = peopleCache.map((k) => {
                    const q = `${k.nama_lengkap || ''} ${k.nip || ''} ${k.jabatan || ''}`.toLowerCase();
                    return `<label class="kar-item" data-q="${esc(q)}">
                        <input type="checkbox" class="form-check-input mt-0" value="${k.id}" ${selectedPeople.has(Number(k.id)) ? 'checked' : ''}>
                        <span class="kar-name">${esc(k.nama_lengkap)}</span>
                        <span class="kar-nip">${esc(k.nip || '')}</span>
                    </label>`;
                }).join('');
                $('nodeKarList').innerHTML = html || '<div class="kar-empty">Tidak ada karyawan aktif</div>';
                $('nodeKarCount').textContent = `${selectedPeople.size} dipilih`;
                filterPeople();
            };

            const filterPeople = () => {
                const q = $('nodeKarSearch').value.trim().toLowerCase();
                $('nodeKarList').querySelectorAll('.kar-item').forEach((el) => {
                    el.style.display = !q || el.dataset.q.includes(q) ? '' : 'none';
                });
            };

            $('nodeKarSearch').addEventListener('input', filterPeople);
            $('nodeKarList').addEventListener('change', (e) => {
                const input = e.target.closest('input[type="checkbox"]');
                if (!input) return;
                const id = Number(input.value);
                if (input.checked) selectedPeople.add(id);
                else selectedPeople.delete(id);
                $('nodeKarCount').textContent = `${selectedPeople.size} dipilih`;
            });

            const fillParentOptions = (selected) => {
                const opts = ['<option value="">Tanpa atasan (jabatan teratas)</option>'];
                state.flat.forEach((n) => {
                    const depth = state.layout.get(n.id)?.depth ?? 0;
                    opts.push(
                        `<option value="${n.id}" ${Number(selected) === n.id ? 'selected' : ''}>${'— '.repeat(depth)}${esc(n.jabatan)}</option>`
                        );
                });
                $('nodeParent').innerHTML = opts.join('');
            };

            const fillSourceOptions = () => {
                const used = new Set(state.flat.map((n) => n.jabatan.toLowerCase()));
                const opts = ['<option value="">+ Buat jabatan baru</option>'];
                jabatanCache.forEach((j, i) => {
                    const taken = used.has(j.jabatan.toLowerCase());
                    opts.push(
                        `<option value="${i}" ${taken ? 'disabled' : ''}>${esc(j.jabatan)}${taken ? ' (sudah ada di struktur ini)' : ''}</option>`
                        );
                });
                $('nodeSource').innerHTML = opts.join('');
            };

            const setJabatanLocked = (locked) => {
                $('nodeJabatan').readOnly = locked;
            };

            $('nodeSource').addEventListener('change', () => {
                const v = $('nodeSource').value;
                if (v === '') {
                    setJabatanLocked(false);
                    $('nodeJabatan').value = '';
                    $('nodeDivisi').value = '';
                    selectedPeople = new Set();
                    renderPeople();
                    $('nodeJabatan').focus();
                    return;
                }
                const j = jabatanCache[Number(v)];
                if (!j) return;
                setJabatanLocked(true);
                $('nodeJabatan').value = j.jabatan;
                $('nodeDivisi').value = j.divisi || '';
                selectedPeople = new Set((j.karyawan_ids || []).map(Number));
                renderPeople();
            });

            const openNodeModal = async (mode, node = null, parentId = null) => {
                nodeCtx = {
                    mode,
                    id: node ? node.id : null
                };
                const isAdd = mode === 'add';
                $('nodeModalTitle').textContent = isAdd ? 'Tambah jabatan' : 'Ubah jabatan';
                $('nodeSourceGroup').classList.toggle('d-none', !isAdd);
                $('nodeParentGroup').classList.toggle('d-none', !isAdd);
                $('nodeJabatan').value = node ? node.jabatan : '';
                $('nodeDivisi').value = node ? node.divisi || '' : '';
                $('nodeKarSearch').value = '';
                setJabatanLocked(false);
                selectedPeople = new Set(node ? node.karyawans.map((k) => Number(k.id)) : []);

                try {
                    const [people, jabatans] = await Promise.all([
                        api(routes.karyawans),
                        isAdd ? api(routes.jabatans) : Promise.resolve(jabatanCache),
                    ]);
                    peopleCache = people;
                    jabatanCache = jabatans;
                } catch (e) {
                    peopleCache = [];
                    toast('error', e.message);
                }

                if (isAdd) {
                    fillSourceOptions();
                    fillParentOptions(parentId);
                }
                renderPeople();
                modal('modalNode').show();
            };

            $('modalNode').addEventListener('shown.bs.modal', () => {
                if (!$('nodeJabatan').readOnly) $('nodeJabatan').focus();
            });

            $('btnSaveNode').addEventListener('click', async () => {
                const jabatan = $('nodeJabatan').value.trim();
                if (!jabatan) return toast('error', 'Nama jabatan wajib diisi');
                const body = {
                    jabatan,
                    divisi: $('nodeDivisi').value.trim() || null,
                    karyawan_ids: [...selectedPeople],
                };
                const btn = $('btnSaveNode');
                btn.disabled = true;
                try {
                    let r;
                    if (nodeCtx.mode === 'add') {
                        body.parent_id = $('nodeParent').value ? Number($('nodeParent').value) : null;
                        r = await api(url(routes.nodeStore, state.chartId), 'POST', body);
                    } else {
                        r = await api(url(routes.nodeUpdate, nodeCtx.id), 'PUT', body);
                    }
                    modal('modalNode').hide();
                    toast('success', r.message);
                    await loadChart(state.chartId, true);
                } catch (e) {
                    toast('error', e.message);
                } finally {
                    btn.disabled = false;
                }
            });

            const openDetail = (node) => {
                const depth = (state.layout.get(node.id)?.depth ?? 0) + 1;
                const parents = parentIdsOf(node).map((i) => state.byId.get(i)).filter(Boolean);
                const rows = [
                    ['Jabatan', node.jabatan],
                    ['Divisi', node.divisi || '—'],
                    ['Level', 'Level ' + depth],
                    ['Atasan', parents.length ? parents.map((x) => x.jabatan).join(', ') :
                        'Jabatan teratas'],
                    ['Bawahan langsung', node.children.length + ' jabatan'],
                ];
                const members = node.karyawans.length ?
                    node.karyawans.map((k) => {
                        const initial = (k.nama_lengkap || '?').charAt(0).toUpperCase();
                        const avatar = k.foto ? `<img src="/storage/${esc(k.foto)}" alt="">` : esc(initial);
                        const meta = [k.nip ? 'NIP ' + k.nip : '', k.divisi || '', k.email || ''].filter(
                            Boolean).map(esc).join(' · ');
                        return `<div class="member"><div class="member-avatar">${avatar}</div><div><div class="member-name">${esc(k.nama_lengkap)}</div><div class="member-meta">${meta}</div></div></div>`;
                    }).join('') :
                    '<div class="kar-empty">Belum ada karyawan pada jabatan ini</div>';

                $('detailBody').innerHTML = `<dl class="detail-grid">${rows.map(([k, v]) => `<dt>${esc(k)}</dt><dd>${esc(v)}</dd>`).join('')}</dl>
                    <div class="detail-title">Karyawan (${node.karyawans.length})</div>${members}`;
                modal('modalDetail').show();
            };

            const openParents = (node) => {
                parentsTarget = node.id;
                $('parentsName').value = node.jabatan;
                const current = new Set((node.additional_parents || []).map(Number));
                $('parentsSelect').innerHTML = state.flat
                    .filter((n) => n.id !== node.id && n.id !== node.parent_id)
                    .map((n) => {
                        const depth = state.layout.get(n.id)?.depth ?? 0;
                        return `<option value="${n.id}" ${current.has(n.id) ? 'selected' : ''}>${'— '.repeat(depth)}${esc(n.jabatan)}</option>`;
                    }).join('');
                modal('modalParents').show();
            };

            $('btnSaveParents').addEventListener('click', async () => {
                if (!parentsTarget) return;
                const ids = [...$('parentsSelect').selectedOptions].map((o) => Number(o.value));
                const btn = $('btnSaveParents');
                btn.disabled = true;
                try {
                    const r = await api(url(routes.nodeParents, parentsTarget), 'POST', {
                        additional_parents: ids
                    });
                    modal('modalParents').hide();
                    toast('success', r.message);
                    await loadChart(state.chartId, true);
                } catch (e) {
                    toast('error', e.message);
                } finally {
                    btn.disabled = false;
                }
            });

            const askDelete = (node) => {
                confirmAsk('Hapus jabatan',
                    `Hapus jabatan <b>${esc(node.jabatan)}</b>? Sub-jabatannya akan naik ke atasan jabatan ini.`,
                    'Hapus', async () => {
                        const r = await api(url(routes.nodeDestroy, node.id), 'DELETE');
                        toast('success', r.message);
                        await loadChart(state.chartId, true);
                    });
            };

            stage.addEventListener('click', (e) => {
                const btn = e.target.closest('button[data-act]');
                if (!btn) return;
                const el = btn.closest('.org-node');
                const node = el ? state.byId.get(Number(el.dataset.id)) : null;
                if (!node) return;
                const act = btn.dataset.act;
                if (act === 'detail') openDetail(node);
                else if (act === 'add') openNodeModal('add', null, node.id);
                else if (act === 'edit') openNodeModal('edit', node);
                else if (act === 'parents') openParents(node);
                else if (act === 'delete') askDelete(node);
            });

            $('btnAddNode').addEventListener('click', () => openNodeModal('add'));
            $('btnAddFirst').addEventListener('click', () => openNodeModal('add'));

            const clearDrop = () => {
                stage.querySelectorAll(
                    '.drop-child,.drop-before,.drop-after,.drop-above,.drop-link,.drop-invalid').forEach((
                    n) =>
                    n.classList.remove('drop-child', 'drop-before', 'drop-after', 'drop-above', 'drop-link',
                        'drop-invalid'));
                rootDrop.classList.remove('hot');
            };

            const showHint = (e, text) => {
                if (!text) {
                    dragHint.classList.add('d-none');
                    return;
                }
                dragHint.textContent = text;
                dragHint.style.left = (e.clientX + 16) + 'px';
                dragHint.style.top = (e.clientY + 16) + 'px';
                dragHint.classList.remove('d-none');
            };

            const SNAP_PX = 6;

            const movingSet = (alt) => (alt ? subtreeIds(drag.id) : new Set([drag.id]));

            const edgesOf = (x, y) => ({
                xs: [x, x + NODE_W / 2, x + NODE_W],
                ys: [y, y + NODE_H / 2, y + NODE_H],
            });

            const previewMove = (d, dx, dy, ids) => {
                const temp = new Map();
                state.layout.forEach((p, id) => {
                    temp.set(id, ids.has(id) && (dx || dy) ? { ...p, x: p.x + dx, y: p.y + dy } : p);
                });

                d.els.forEach((el, id) => {
                    const t = temp.get(id);
                    if (!t) return;
                    el.style.left = t.x + 'px';
                    el.style.top = t.y + 'px';
                    el.classList.toggle('is-moving', ids.has(id));
                });

                if (d.svg) {
                    d.svg.querySelectorAll('path').forEach((p) => p.remove());
                    drawConnectors(d.svg, temp);
                }
            };

            const resetPreview = (d) => {
                previewMove(d, 0, 0, new Set());
                d.els.forEach((el) => el.classList.remove('is-moving', 'is-source'));
            };

            const drawGuides = (d, guides) => {
                if (!d.guides) return;
                d.guides.innerHTML = '';
                guides.forEach((l) => {
                    const line = document.createElementNS(NS, 'line');
                    line.setAttribute('x1', l.x1);
                    line.setAttribute('y1', l.y1);
                    line.setAttribute('x2', l.x2);
                    line.setAttribute('y2', l.y2);
                    line.setAttribute('stroke', '#ec4899');
                    line.setAttribute('stroke-width', '1.5');
                    line.setAttribute('stroke-dasharray', '5 4');
                    line.setAttribute('vector-effect', 'non-scaling-stroke');
                    d.guides.appendChild(line);
                });
            };

            const snapPosition = (nx, ny, ids, cur) => {
                const th = SNAP_PX / state.zoom;
                const mine = edgesOf(nx, ny);
                let bx = null;
                let by = null;

                state.layout.forEach((p, id) => {
                    if (ids.has(id)) return;
                    const o = edgesOf(p.x, p.y);
                    mine.xs.forEach((a, i) => {
                        const d = o.xs[i] - a;
                        if (Math.abs(d) <= th && (bx === null || Math.abs(d) < Math.abs(bx))) bx = d;
                    });
                    mine.ys.forEach((a, i) => {
                        const d = o.ys[i] - a;
                        if (Math.abs(d) <= th && (by === null || Math.abs(d) < Math.abs(by))) by = d;
                    });
                });

                if (bx !== null) nx += bx;
                if (by !== null) ny += by;

                // posisi disimpan sebagai integer
                nx = cur.x + Math.round(nx - cur.x);
                ny = cur.y + Math.round(ny - cur.y);

                const fin = edgesOf(nx, ny);
                const vLines = new Map();
                const hLines = new Map();

                state.layout.forEach((p, id) => {
                    if (ids.has(id)) return;
                    const o = edgesOf(p.x, p.y);

                    fin.xs.forEach((a, i) => {
                        const b = o.xs[i];
                        if (Math.abs(a - b) > 1) return;
                        const key = Math.round(b);
                        const g = vLines.get(key) || { pos: b, from: Infinity, to: -Infinity };
                        g.from = Math.min(g.from, ny, p.y);
                        g.to = Math.max(g.to, ny + NODE_H, p.y + NODE_H);
                        vLines.set(key, g);
                    });

                    fin.ys.forEach((a, i) => {
                        const b = o.ys[i];
                        if (Math.abs(a - b) > 1) return;
                        const key = Math.round(b);
                        const g = hLines.get(key) || { pos: b, from: Infinity, to: -Infinity };
                        g.from = Math.min(g.from, nx, p.x);
                        g.to = Math.max(g.to, nx + NODE_W, p.x + NODE_W);
                        hLines.set(key, g);
                    });
                });

                const guides = [];
                vLines.forEach((g) => guides.push({ x1: g.pos, y1: g.from - 14, x2: g.pos, y2: g.to + 14 }));
                hLines.forEach((g) => guides.push({ x1: g.from - 14, y1: g.pos, x2: g.to + 14, y2: g.pos }));

                return { nx, ny, guides };
            };

            const enterFree = (e) => {
                const cur = state.layout.get(drag.id);
                if (!cur) return;

                const sr = stage.getBoundingClientRect();
                const ids = movingSet(e.altKey);
                const rawX = (e.clientX - drag.grabX - sr.left) / state.zoom;
                const rawY = (e.clientY - drag.grabY - sr.top) / state.zoom;
                const s = snapPosition(rawX, rawY, ids, cur);

                drag.preview = { dx: s.nx - cur.x, dy: s.ny - cur.y, ids };
                drag.aligned = s.guides.length > 0;

                previewMove(drag, drag.preview.dx, drag.preview.dy, ids);
                drawGuides(drag, s.guides);

                if (drag.mode !== 'free') {
                    drag.mode = 'free';
                    drag.ghost.style.display = 'none';
                    drag.el.classList.remove('is-source');
                }
            };

            const leaveFree = () => {
                if (drag.mode === 'hier') return;
                drag.mode = 'hier';
                drag.preview = null;
                drag.aligned = false;
                previewMove(drag, 0, 0, new Set([drag.id]));
                drawGuides(drag, []);
                drag.ghost.style.display = '';
                drag.el.classList.add('is-source');
            };

            const startDrag = (e) => {
                const rect = drag.el.getBoundingClientRect();
                const ghost = drag.el.cloneNode(true);
                ghost.classList.add('org-ghost');
                ghost.querySelector('.org-node-actions')?.remove();
                ghost.style.left = rect.left + 'px';
                ghost.style.top = rect.top + 'px';
                ghost.style.width = NODE_W + 'px';
                ghost.style.height = NODE_H + 'px';
                ghost.style.transform = `scale(${state.zoom})`;
                document.body.appendChild(ghost);

                drag.ghost = ghost;
                drag.grabX = drag.sx - rect.left;
                drag.grabY = drag.sy - rect.top;
                drag.active = true;
                drag.mode = null;
                drag.preview = null;
                drag.aligned = false;
                drag.els = new Map([...stage.querySelectorAll('.org-node')].map((el) => [Number(el.dataset.id), el]));
                drag.svg = stage.querySelector('svg.org-connectors');

                const gs = document.createElementNS(NS, 'svg');
                gs.setAttribute('class', 'org-guides');
                gs.setAttribute('width', state.W);
                gs.setAttribute('height', state.H);
                gs.setAttribute('viewBox', `0 0 ${state.W} ${state.H}`);
                stage.appendChild(gs);
                drag.guides = gs;

                drag.el.classList.add('is-moving');
                rootDrop.classList.add('show');
                document.body.style.cursor = 'grabbing';
            };

            const updateTarget = (e) => {
                clearDrop();
                drag.target = null;

                const forceFree = e.ctrlKey || e.metaKey;
                const els = document.elementsFromPoint(e.clientX, e.clientY);
                let hit = null;

                if (!forceFree) {
                    if (els.includes(rootDrop)) {
                        hit = { type: 'root' };
                    } else {
                        const nodeEl = els.map((x) => x.closest('.org-node')).find((x) => x && stage.contains(x));
                        if (nodeEl && nodeEl !== drag.el) hit = { type: 'node', el: nodeEl };
                    }
                }

                if (!hit) {
                    enterFree(e);
                    drag.target = { type: 'free' };
                    const base = e.altKey ? 'Pindahkan bebas bersama bawahan' : 'Pindahkan bebas (Alt: bawahan ikut)';
                    showHint(e, base + (drag.aligned ? ' • sejajar' : ''));
                    return;
                }

                leaveFree();

                if (hit.type === 'root') {
                    drag.target = { type: 'root' };
                    rootDrop.classList.add('hot');
                    showHint(e, 'Jadikan jabatan teratas');
                    return;
                }

                const nodeEl = hit.el;
                const id = Number(nodeEl.dataset.id);
                if (drag.blocked.has(id)) {
                    nodeEl.classList.add('drop-invalid');
                    showHint(e, 'Tidak bisa dipindahkan ke bawahannya sendiri');
                    return;
                }

                const rect = nodeEl.getBoundingClientRect();
                const rx = (e.clientX - rect.left) / rect.width;
                const ry = (e.clientY - rect.top) / rect.height;
                let pos = 'child';
                if (rx < 0.25) pos = 'before';
                else if (rx > 0.75) pos = 'after';
                else if (ry < 0.3) pos = 'above';
                else if (e.shiftKey) pos = 'link';

                nodeEl.classList.add('drop-' + pos);
                drag.target = { type: 'node', id, pos };

                const hints = {
                    child: 'Jadikan bawahan (tahan Shift: atasan tambahan)',
                    link: 'Tambah sebagai atasan tambahan',
                    before: 'Sisipkan sebelum (sejajar)',
                    after: 'Sisipkan sesudah (sejajar)',
                    above: 'Jadikan atasan dari jabatan ini',
                };
                showHint(e, hints[pos]);
            };

            const autoScroll = (e) => {
                const r = viewport.getBoundingClientRect();
                const edge = 48;
                const speed = 22;
                if (e.clientX < r.left + edge) viewport.scrollLeft -= speed;
                else if (e.clientX > r.right - edge) viewport.scrollLeft += speed;
                if (e.clientY < r.top + edge) viewport.scrollTop -= speed;
                else if (e.clientY > r.bottom - edge) viewport.scrollTop += speed;
            };

            const applyFreeMove = async (d) => {
                const pv = d.preview;
                if (!pv || (!pv.dx && !pv.dy)) return;

                const { dx, dy, ids } = pv;
                const payload = [];
                ids.forEach((id) => {
                    const n = state.byId.get(id);
                    if (!n) return;
                    n.pos_x = (Number(n.pos_x) || 0) + dx;
                    n.pos_y = (Number(n.pos_y) || 0) + dy;
                    payload.push({ id, pos_x: n.pos_x, pos_y: n.pos_y });
                });

                // render langsung (optimistic), jaga agar kartu lain tidak "loncat" di layar
                const anchor = state.flat.find((n) => !ids.has(n.id));
                const before = anchor ? { ...state.layout.get(anchor.id) } : null;
                const oldLeft = stage.offsetLeft;
                const oldTop = stage.offsetTop;

                computeLayout();
                render();
                updateStats();
                applyView();

                if (before) {
                    const after = state.layout.get(anchor.id);
                    viewport.scrollLeft += (after.x - before.x) * state.zoom + (stage.offsetLeft - oldLeft);
                    viewport.scrollTop += (after.y - before.y) * state.zoom + (stage.offsetTop - oldTop);
                }

                try {
                    await api(routes.positions, 'POST', { positions: payload });
                } catch (err) {
                    toast('error', err.message);
                    await loadChart(state.chartId, true);
                }
            };

            const finishDrag = async (commit) => {
                const d = drag;
                drag = null;
                if (!d) return;

                clearDrop();
                showHint(null, null);
                rootDrop.classList.remove('show');
                document.body.style.cursor = '';
                d.ghost?.remove();
                d.guides?.remove();
                if (d.active && d.els) resetPreview(d);
                d.el.classList.remove('is-source');
                if (!d.active || !commit || !d.target) return;

                if (d.target.type === 'free') {
                    await applyFreeMove(d);
                    return;
                }

                const body = d.target.type === 'root' ? {
                    position: 'root'
                } : {
                    position: d.target.pos,
                    target_id: d.target.id
                };

                try {
                    const r = await api(url(routes.nodeMove, d.id), 'POST', body);
                    toast('success', r.message);
                } catch (err) {
                    toast('error', err.message);
                }
                await loadChart(state.chartId, true);
            };

            stage.addEventListener('pointerdown', (e) => {
                if (e.button !== 0 || e.target.closest('button')) return;
                const el = e.target.closest('.org-node');
                if (!el) return;
                e.preventDefault();
                const id = Number(el.dataset.id);
                drag = {
                    id,
                    el,
                    sx: e.clientX,
                    sy: e.clientY,
                    active: false,
                    ghost: null,
                    target: null,
                    blocked: subtreeIds(id),
                    grabX: 0,
                    grabY: 0,
                };
            });

            viewport.addEventListener('pointerdown', (e) => {
                if (e.button !== 0 || e.pointerType !== 'mouse') return;
                if (e.target.closest('.org-node')) return;
                e.preventDefault();
                pan = {
                    x: e.clientX,
                    y: e.clientY,
                    left: viewport.scrollLeft,
                    top: viewport.scrollTop
                };
                viewport.classList.add('panning');
            });

            document.addEventListener('pointermove', (e) => {
                if (pan) {
                    viewport.scrollLeft = pan.left - (e.clientX - pan.x);
                    viewport.scrollTop = pan.top - (e.clientY - pan.y);
                    return;
                }
                if (!drag) return;
                if (!drag.active) {
                    if (Math.hypot(e.clientX - drag.sx, e.clientY - drag.sy) < DRAG_THRESHOLD) return;
                    startDrag(e);
                }
                drag.ghost.style.left = (e.clientX - drag.grabX) + 'px';
                drag.ghost.style.top = (e.clientY - drag.grabY) + 'px';
                drag.lastX = e.clientX;
                drag.lastY = e.clientY;
                drag.alt = e.altKey;
                updateTarget(e);
                autoScroll(e);
            });

            document.addEventListener('pointerup', () => {
                if (pan) {
                    pan = null;
                    viewport.classList.remove('panning');
                }
                if (drag) finishDrag(true);
            });

            document.addEventListener('pointercancel', () => {
                if (pan) {
                    pan = null;
                    viewport.classList.remove('panning');
                }
                if (drag) finishDrag(false);
            });

            viewport.addEventListener('wheel', (e) => {
                if (!(e.ctrlKey || e.metaKey)) return;
                e.preventDefault();
                const r = viewport.getBoundingClientRect();
                zoomAt(state.zoom * (e.deltaY < 0 ? 1.1 : 1 / 1.1), e.clientX - r.left, e.clientY - r.top);
            }, {
                passive: false
            });

            $('btnZoomIn').addEventListener('click', () => zoomCenter(state.zoom + ZOOM_STEP));
            $('btnZoomOut').addEventListener('click', () => zoomCenter(state.zoom - ZOOM_STEP));
            $('btnFit').addEventListener('click', fit);
            $('btnResetPos').addEventListener('click', () => {
                if (!state.flat.some((n) => n.pos_x || n.pos_y)) return toast('error',
                    'Belum ada posisi manual');
                confirmAsk('Reset posisi', 'Kembalikan semua kartu ke posisi otomatis?', 'Reset',
            async () => {
                        const r = await api(url(routes.resetPositions, state.chartId), 'POST');
                        toast('success', r.message);
                        await loadChart(state.chartId, true);
                    });
            });
            let searchTimer;
            $('searchInput').addEventListener('input', (e) => {
                clearTimeout(searchTimer);
                const q = e.target.value.trim().toLowerCase();
                searchTimer = setTimeout(() => {
                    let first = null;
                    stage.querySelectorAll('.org-node').forEach((el) => {
                        if (!q) {
                            el.classList.remove('highlighted', 'dimmed');
                            return;
                        }
                        const title = el.querySelector('.org-node-title')?.textContent
                            .toLowerCase() || '';
                        const sub = el.querySelector('.org-node-sub')?.textContent
                            .toLowerCase() || '';
                        const hit = title.includes(q) || sub.includes(q);
                        el.classList.toggle('highlighted', hit);
                        el.classList.toggle('dimmed', !hit);
                        if (hit && first === null) first = Number(el.dataset.id);
                    });
                    if (first !== null) centerOn(first);
                }, 200);
            });

            let fullscreen = false;
            const setFullscreen = (on) => {
                fullscreen = on;
                mainCard.classList.toggle('is-fullscreen', on);
                document.body.style.overflow = on ? 'hidden' : '';
                $('btnFullscreen').innerHTML = on ? '<i class="fa-solid fa-minimize"></i>' :
                    '<i class="fa-solid fa-maximize"></i>';
                setTimeout(fit, 120);
            };

            $('btnFullscreen').addEventListener('click', () => setFullscreen(!fullscreen));

            document.addEventListener('keydown', (e) => {
                if (e.key !== 'Escape') return;
                if (drag) finishDrag(false);
                else if (fullscreen && !document.querySelector('.modal.show')) setFullscreen(false);
            });

            let resizeFrame = 0;
            new ResizeObserver(() => {
                cancelAnimationFrame(resizeFrame);
                resizeFrame = requestAnimationFrame(applyView);
            }).observe(viewport);

            $('btnExport').addEventListener('click', async () => {
                if (!state.flat.length) return toast('error', 'Struktur masih kosong');
                const btn = $('btnExport');
                const prevScroll = window.scrollY;
                let holder = null;
                btn.disabled = true;

                try {
                    window.scrollTo(0, 0);
                    if (document.fonts && document.fonts.ready) await document.fonts.ready;

                    const titleH = 96;
                    const w = state.W;
                    const h = state.H + titleH;

                    holder = document.createElement('div');
                    holder.className = 'org-export-holder';
                    holder.style.width = w + 'px';
                    holder.style.height = h + 'px';
                    holder.innerHTML =
                        `<div class="org-export-title" style="height:${titleH}px">${esc(chartName.value)}</div>`;

                    const clone = stage.cloneNode(true);
                    clone.removeAttribute('id');
                    clone.style.cssText =
                        `position:absolute;left:0;top:${titleH}px;width:${state.W}px;height:${state.H}px;transform:none;display:block;`;
                    clone.querySelectorAll('.org-node-actions').forEach((x) => x.remove());
                    clone.querySelectorAll('.org-node').forEach((x) =>
                        x.classList.remove('is-source', 'dimmed', 'highlighted', 'drop-child',
                            'drop-before', 'drop-after', 'drop-above', 'drop-link', 'drop-invalid'));

                    holder.appendChild(clone);
                    document.body.appendChild(holder);
                    await new Promise((r) => requestAnimationFrame(() => requestAnimationFrame(r)));

                    const scale = Math.max(0.5, Math.min(2, Math.sqrt(16000000 / (w * h))));
                    const canvas = await html2canvas(holder, {
                        backgroundColor: '#f8fafc',
                        scale,
                        useCORS: true,
                        logging: false,
                        width: w,
                        height: h,
                        windowWidth: Math.max(w, document.documentElement.clientWidth),
                        windowHeight: Math.max(h, document.documentElement.clientHeight),
                        scrollX: 0,
                        scrollY: 0,
                    });

                    const blob = await new Promise((resolve) => canvas.toBlob(resolve, 'image/png'));
                    if (!blob) throw new Error('Gagal membuat gambar');

                    const slug = (chartName.value || 'struktur').toLowerCase().replace(/[^a-z0-9]+/g,
                        '-').replace(/^-+|-+$/g, '') || 'struktur';
                    const link = document.createElement('a');
                    link.download = `${slug}-${new Date().toISOString().slice(0, 10)}.png`;
                    link.href = URL.createObjectURL(blob);
                    document.body.appendChild(link);
                    link.click();
                    link.remove();
                    setTimeout(() => URL.revokeObjectURL(link.href), 2000);
                } catch (err) {
                    toast('error', 'Gagal export: ' + err.message);
                } finally {
                    holder?.remove();
                    window.scrollTo(0, prevScroll);
                    btn.disabled = false;
                }
            });

            const init = async () => {
                state.charts = initialCharts.slice();
                let saved = null;
                try {
                    saved = Number(localStorage.getItem(STORE_KEY));
                } catch (e) {}
                const first = state.charts.find((c) => c.id === saved) || state.charts[0];
                renderTabs();
                if (first) await loadChart(first.id);
            };

            init();
        });
    </script>
@endpush
