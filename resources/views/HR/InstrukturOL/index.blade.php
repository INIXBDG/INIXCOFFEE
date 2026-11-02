@extends('layout_HR.app')

@push('styles')
    <style>
        :root {
            --pri: #4f46e5;
            --pri-dark: #3730a3;
            --pri-light: #eef2ff;
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
            --gray-500: #6b7280;
            --gray-600: #4b5563;
            --gray-700: #374151;
            --gray-900: #111827;
            --radius: 10px;
            --shadow: 0 1px 3px rgba(0, 0, 0, .06), 0 1px 2px rgba(0, 0, 0, .04);
        }

        .mdk {
            color: var(--gray-900);
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }

        .modal-backdrop {
            --bs-backdrop-bg: #000;
            --bs-backdrop-opacity: .35;
        }

        .modal-backdrop.show {
            opacity: .35;
        }

        .mdk .page-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--gray-900);
            margin: 0;
        }

        .mdk .page-sub {
            color: var(--gray-500);
            font-size: .875rem;
            margin: .15rem 0 0;
        }

        .mdk h2 {
            font-size: 1rem;
            font-weight: 700;
            margin: 0;
            color: var(--gray-900);
        }

        .mdk .sub {
            color: var(--gray-500);
            font-size: .85rem;
        }

        .mdk .panel {
            background: #fff;
            border: 0;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
        }

        .mdk .panel-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .75rem;
            padding: 1.1rem 1.4rem;
            border-bottom: 1px solid var(--gray-200);
            flex-wrap: wrap;
        }

        .mdk .panel-head h2 {
            display: flex;
            align-items: center;
            gap: .5rem;
            font-size: .95rem;
            color: var(--gray-700);
        }

        .mdk .panel-head h2 i {
            color: var(--pri);
        }

        .mdk .panel-body {
            padding: 1.25rem 1.4rem;
        }

        .mdk .panel-foot {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: .75rem;
            padding: .9rem 1.4rem;
            border-top: 1px solid var(--gray-200);
        }

        .mdk .btn {
            border-radius: 8px;
            font-weight: 600;
            font-size: .85rem;
        }

        .mdk .btn-brand {
            background: var(--pri);
            color: #fff;
            border: 1px solid var(--pri);
        }

        .mdk .btn-brand:hover,
        .mdk .btn-brand:focus {
            background: var(--pri-dark);
            border-color: var(--pri-dark);
            color: #fff;
        }

        .mdk .btn-line {
            background: #fff;
            border: 1px solid var(--gray-200);
            color: var(--gray-700);
        }

        .mdk .btn-line:hover {
            border-color: var(--pri);
            color: var(--pri);
            background: var(--pri-light);
        }

        .mdk .btn-line.danger:hover {
            border-color: var(--danger);
            color: var(--danger);
            background: var(--danger-light);
        }

        .mdk .btn:focus-visible,
        .mdk .form-control:focus,
        .mdk .form-select:focus {
            box-shadow: 0 0 0 3px rgba(79, 70, 229, .15);
            border-color: var(--pri);
        }

        .mdk .form-control,
        .mdk .form-select {
            font-size: .875rem;
            border: 1px solid var(--gray-200);
            border-radius: 8px;
            color: var(--gray-700);
        }

        .mdk .form-label {
            font-size: .78rem;
            font-weight: 600;
            color: var(--gray-600);
            margin-bottom: .3rem;
        }

        .mdk .ol-req::after {
            content: ' *';
            color: var(--danger);
        }

        /* Ringkasan */
        .mdk .summary {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
        }

        .mdk .stat {
            padding: 1.1rem 1.3rem;
            position: relative;
            overflow: hidden;
        }

        .mdk .stat::before {
            content: "";
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 4px;
            background: var(--pri);
        }

        .mdk .stat.warn::before {
            background: var(--warning);
        }

        .mdk .stat.bad::before {
            background: var(--danger);
        }

        .mdk .stat.purple::before {
            background: var(--purple);
        }

        .mdk .stat-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: .75rem;
        }

        .mdk .stat .l {
            color: var(--gray-500);
            font-size: .8rem;
            font-weight: 600;
        }

        .mdk .stat .n {
            font-size: 1.9rem;
            font-weight: 700;
            line-height: 1.1;
            margin-top: .35rem;
            color: var(--gray-900);
        }

        .mdk .stat .foot {
            color: var(--gray-500);
            font-size: .78rem;
            margin-top: .35rem;
        }

        .mdk .stat-ico {
            width: 44px;
            height: 44px;
            border-radius: 10px;
            display: grid;
            place-items: center;
            font-size: 1.05rem;
            color: #fff;
            flex: none;
        }

        .mdk .bg-pri {
            background: linear-gradient(135deg, #6366f1, var(--pri));
        }

        .mdk .bg-warn {
            background: linear-gradient(135deg, #f59e0b, var(--warning));
        }

        .mdk .bg-bad {
            background: linear-gradient(135deg, #ef4444, var(--danger));
        }

        .mdk .bg-purple {
            background: linear-gradient(135deg, #8b5cf6, var(--purple));
        }

        @keyframes mdkShimmer {
            to {
                background-position: -200% 0;
            }
        }

        .mdk .summary.is-loading .n {
            color: transparent;
            width: 70px;
            border-radius: 6px;
            background: linear-gradient(90deg, var(--gray-100) 25%, var(--gray-200) 50%, var(--gray-100) 75%);
            background-size: 200% 100%;
            animation: mdkShimmer 1.5s infinite;
        }

        /* Tag */
        .mdk .tag {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            font-size: .72rem;
            font-weight: 700;
            padding: .22rem .6rem;
            border-radius: 6px;
            white-space: nowrap;
            text-decoration: none;
        }

        .mdk .tag-done {
            background: var(--success-light);
            color: var(--success);
        }

        .mdk .tag-pending {
            background: var(--warning-light);
            color: var(--warning);
        }

        .mdk .tag-info {
            background: var(--info-light);
            color: var(--info);
        }

        .mdk .tag-muted {
            background: var(--gray-100);
            color: var(--gray-500);
        }

        /* Filter & tabel */
        .mdk .filters {
            display: grid;
            grid-template-columns: minmax(0, 480px);
            gap: .75rem;
            padding: 1rem 1.4rem;
            border-bottom: 1px solid var(--gray-200);
            background: var(--gray-50);
        }

        .mdk .search-wrap {
            position: relative;
        }

        .mdk .search-wrap i {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray-400);
            font-size: .85rem;
        }

        .mdk .search-wrap .form-control {
            padding-left: 2.2rem;
        }

        .mdk table.tbl thead th {
            font-size: .7rem;
            color: var(--gray-500);
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .5px;
            background: var(--gray-50);
            white-space: nowrap;
            border-bottom: 1px solid var(--gray-200);
            padding: .85rem 1rem;
        }

        .mdk table.tbl td {
            vertical-align: middle;
            font-size: .85rem;
            padding: .85rem 1rem;
            color: var(--gray-700);
        }

        .mdk table.tbl tbody tr:hover {
            background: var(--gray-50);
        }

        .mdk table.tbl tfoot th {
            background: var(--gray-50);
            font-size: .85rem;
            padding: .75rem 1rem;
        }

        .mdk table.is-loading tbody {
            opacity: .4;
            pointer-events: none;
        }

        .mdk .ol-ket {
            max-width: 240px;
            white-space: normal;
        }

        .mdk .pagination .page-link {
            border: 1px solid var(--gray-200);
            color: var(--gray-600);
            font-size: .82rem;
            padding: .35rem .7rem;
            margin: 0 2px;
            border-radius: 6px;
        }

        .mdk .pagination .page-item.active .page-link {
            background: var(--pri);
            border-color: var(--pri);
            color: #fff;
        }

        .mdk .pagination .page-item.disabled .page-link {
            color: var(--gray-400);
        }

        .mdk .person {
            display: flex;
            align-items: center;
            gap: .7rem;
            min-width: 200px;
        }

        .mdk .avatar {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: var(--pri-light);
            color: var(--pri);
            display: grid;
            place-items: center;
            font-weight: 700;
            font-size: .8rem;
            flex: none;
        }

        .mdk .person .nm {
            font-weight: 600;
            line-height: 1.2;
            color: var(--gray-900);
        }

        .mdk .person .nip {
            color: var(--gray-500);
            font-size: .75rem;
        }

        .mdk .act .btn {
            padding: .3rem .6rem;
        }

        .mdk .empty {
            padding: 2.2rem 1rem;
            text-align: center;
            color: var(--gray-400);
            font-size: .85rem;
        }

        .mdk .empty i.big {
            display: block;
            font-size: 1.8rem;
            opacity: .45;
            margin-bottom: .5rem;
        }

        .mdk .empty.loading {
            color: var(--pri);
            font-weight: 600;
        }

        /* Modal */
        .mdk .modal-header {
            background: linear-gradient(135deg, #f8faff, #eef4ff);
            border-bottom: 1px solid var(--gray-200);
            padding: 1.1rem 1.4rem;
        }

        .mdk .modal-header h2 {
            display: flex;
            align-items: center;
            gap: .5rem;
            font-size: 1.05rem;
        }

        .mdk .modal-header h2 i {
            color: var(--pri);
        }

        .mdk .modal-footer {
            border-top: 1px solid var(--gray-200);
            background: var(--gray-50);
            padding: .9rem 1.4rem;
        }

        .mdk-modal .modal-content {
            border: 0;
            border-radius: 12px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, .2);
            overflow: hidden;
        }

        .mdk-modal .profile-head {
            display: flex;
            gap: 1rem;
            align-items: center;
            padding: 1.4rem 1.5rem;
            background: linear-gradient(135deg, var(--pri) 0%, var(--pri-dark) 100%);
            color: #fff;
        }

        .mdk-modal .profile-head h2 {
            color: #fff;
            font-size: 1.2rem;
        }

        .mdk-modal .profile-head .sub {
            color: rgba(255, 255, 255, .85);
        }

        .mdk-modal .profile-head .avatar {
            width: 64px;
            height: 64px;
            font-size: 1.2rem;
            background: rgba(255, 255, 255, .95);
            color: var(--pri);
            border: 3px solid rgba(255, 255, 255, .45);
        }

        .mdk-modal .profile-head .btn-line {
            border: 0;
        }

        .mdk-modal .profile-head .btn-close {
            filter: brightness(0) invert(1);
            opacity: .8;
        }

        .mdk-modal .nav-tabs {
            border-bottom: 2px solid var(--gray-200);
            padding: 0 1.25rem;
            gap: .25rem;
            flex-wrap: nowrap;
            overflow-x: auto;
            overflow-y: hidden;
            background: #fff;
        }

        .mdk-modal .nav-tabs .nav-link {
            display: flex;
            align-items: center;
            gap: .5rem;
            border: 0;
            border-bottom: 3px solid transparent;
            color: var(--gray-500);
            font-weight: 600;
            font-size: .85rem;
            white-space: nowrap;
            padding: .85rem 1rem;
            margin-bottom: -2px;
            background: none;
        }

        .mdk-modal .nav-tabs .nav-link:hover {
            color: var(--pri);
        }

        .mdk-modal .nav-tabs .nav-link.active {
            color: var(--pri);
            border-bottom-color: var(--pri);
            background: none;
        }

        .mdk .count {
            background: var(--gray-100);
            color: var(--gray-600);
            font-size: .72rem;
            padding: .1rem .55rem;
            border-radius: 20px;
        }

        .mdk .nav-link.active .count {
            background: var(--pri-light);
            color: var(--pri);
        }

        .mdk .nav-pills {
            gap: .35rem;
        }

        .mdk .nav-pills .nav-link {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            font-size: .82rem;
            font-weight: 600;
            color: var(--gray-600);
            background: var(--gray-100);
            border-radius: 8px;
            padding: .45rem .9rem;
        }

        .mdk .nav-pills .nav-link.active {
            background: var(--pri);
            color: #fff;
        }

        .mdk .nav-pills .nav-link.active .count {
            background: rgba(255, 255, 255, .25);
            color: #fff;
        }

        .mdk .section-title {
            display: flex;
            align-items: center;
            gap: .5rem;
            font-size: .78rem;
            color: var(--pri);
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .5px;
            margin: 1.6rem 0 .85rem;
        }

        .mdk .section-title:first-child {
            margin-top: 0;
        }

        .mdk .kv {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: .75rem;
            margin: 0;
        }

        .mdk .kv>div {
            background: var(--gray-50);
            border: 1px solid var(--gray-200);
            border-radius: 8px;
            padding: .65rem .9rem;
        }

        .mdk .kv dt {
            font-size: .7rem;
            color: var(--gray-500);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .3px;
            margin: 0 0 .15rem;
        }

        .mdk .kv dd {
            margin: 0;
            font-weight: 600;
            font-size: .88rem;
            color: var(--gray-900);
            word-break: break-word;
        }

        .mdk .note-box {
            background: var(--gray-50);
            border-left: 4px solid var(--pri);
            border-radius: 8px;
            padding: .8rem 1rem;
            font-size: .85rem;
            color: var(--gray-700);
            white-space: pre-line;
            margin: 0;
        }

        .mdk .extend-box {
            background: var(--gray-50);
            border: 1px solid var(--gray-200);
            border-radius: 10px;
            padding: 1rem;
        }

        .mdk-modal .modal-body {
            background: #fff;
        }

        .modal-dialog-scrollable {
            max-height: calc(100vh - 1.5rem);
        }

        .modal-dialog-scrollable .modal-content {
            max-height: 100%;
            overflow: hidden;
        }

        .modal-dialog-scrollable .modal-body {
            overflow-y: auto;
            max-height: calc(100vh - 220px);
        }

        .mdk .table-responsive {
            -webkit-overflow-scrolling: touch;
        }

        @media (max-width: 991px) {
            .mdk .summary {
                grid-template-columns: 1fr 1fr;
            }

            .mdk .kv {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 767.98px) {
            .mdk .summary {
                grid-template-columns: 1fr;
            }

            .mdk table.tbl td,
            .mdk table.tbl th {
                padding: .5rem .4rem;
                font-size: .8rem;
            }

            .mdk .person {
                min-width: 140px;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .mdk .summary.is-loading .n {
                animation: none;
            }
        }
    </style>
@endpush

@section('content_HR')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.3.1/css/all.min.css"
        integrity="sha512-QeR2VH+lsBE5LSAe1Q5EnTBbe7XTBubt8dG93Y7gidSgdMCr8nVqKcfKAMyN96SV8KDbZVTDXChatu5G2KQGzg=="
        crossorigin="anonymous" referrerpolicy="no-referrer">

    <div class="container-fluid px-4 py-4">
        <div class="mdk">

            <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
                <div>
                    <h1 class="page-title"><i class="fa-solid fa-chalkboard-user me-2" style="color:var(--pri)"></i>Data Instruktur OL</h1>
                    <p class="page-sub">Data instruktur online, riwayat mengajar, pembayaran, dan silabus.</p>
                </div>
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <button type="button" class="btn btn-line btn-sm" id="btnExportList"><i class="fa-solid fa-file-export me-1"></i>Export</button>
                    <button type="button" class="btn btn-line btn-sm" id="btnMigrasi"><i class="fa-solid fa-right-left me-1"></i>Migrasi dari karyawan</button>
                    <button type="button" class="btn btn-brand btn-sm" id="btnTambah"><i class="fa-solid fa-plus me-1"></i>Tambah instruktur</button>
                </div>
            </div>

            <div class="summary is-loading">
                <div class="panel stat">
                    <div class="stat-top">
                        <div>
                            <div class="l">Total instruktur OL</div>
                            <div class="n" id="sumTotal">0</div>
                        </div>
                        <div class="stat-ico bg-pri"><i class="fa-solid fa-users"></i></div>
                    </div>
                    <div class="foot">terdaftar di sistem</div>
                </div>
                <div class="panel stat warn">
                    <div class="stat-top">
                        <div>
                            <div class="l">CV belum diunggah</div>
                            <div class="n" id="sumCv">0</div>
                        </div>
                        <div class="stat-ico bg-warn"><i class="fa-regular fa-file-lines"></i></div>
                    </div>
                    <div class="foot">perlu dilengkapi</div>
                </div>
                <div class="panel stat bad">
                    <div class="stat-top">
                        <div>
                            <div class="l">KTP belum diunggah</div>
                            <div class="n" id="sumKtp">0</div>
                        </div>
                        <div class="stat-ico bg-bad"><i class="fa-regular fa-id-card"></i></div>
                    </div>
                    <div class="foot">perlu dilengkapi</div>
                </div>
                <div class="panel stat purple">
                    <div class="stat-top">
                        <div>
                            <div class="l">Kode karyawan kosong</div>
                            <div class="n" id="sumKode">0</div>
                        </div>
                        <div class="stat-ico bg-purple"><i class="fa-solid fa-hashtag"></i></div>
                    </div>
                    <div class="foot">riwayat mengajar belum terbaca</div>
                </div>
            </div>

            <div class="panel mt-4">
                <div class="panel-head">
                    <h2><i class="fa-solid fa-table-list"></i>Daftar instruktur</h2>
                    <button type="button" class="btn btn-line btn-sm" id="btnReset">
                        <i class="fa-solid fa-rotate-left me-1"></i>Reset filter
                    </button>
                </div>
                <form class="filters" id="formCari" onsubmit="return false">
                    <div class="search-wrap">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <input type="search" name="q" class="form-control" placeholder="Cari kode, nama, core, atau no. HP"
                            aria-label="Cari instruktur">
                    </div>
                </form>
                <div class="panel-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle w-100 mb-0 tbl" id="tabelInstruktur">
                            <thead>
                                <tr>
                                    <th>Instruktur</th>
                                    <th>Core</th>
                                    <th>No. HP</th>
                                    <th>Keterangan</th>
                                    <th class="text-center">CV</th>
                                    <th class="text-center">KTP</th>
                                    <th class="text-end">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="tableBody"></tbody>
                        </table>
                    </div>
                </div>
                <div class="panel-foot">
                    <small class="sub" id="paginationInfo"></small>
                    <div id="paginationContainer"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Tambah --}}
    <div class="modal fade mdk" id="modalTambah" tabindex="-1" data-bs-backdrop="static" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <form class="modal-content" id="formTambah" enctype="multipart/form-data" style="border-radius:12px">
                @csrf
                <div class="modal-header">
                    <h2><i class="fa-solid fa-user-plus"></i>Tambah instruktur OL</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-danger ol-errors d-none"></div>
                    <div class="section-title"><i class="fa-regular fa-id-card"></i>Data instruktur</div>
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Kode karyawan</label>
                            <input type="text" name="kode_karyawan" class="form-control" maxlength="20" placeholder="Contoh: SP">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label ol-req">Nama</label>
                            <input type="text" name="nama" class="form-control" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label ol-req">Core</label>
                            <input type="text" name="core" class="form-control" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label ol-req">No. HP</label>
                            <input type="text" name="no_hp" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Keterangan</label>
                            <textarea name="keterangan" rows="2" class="form-control"></textarea>
                        </div>
                    </div>
                    <div class="section-title"><i class="fa-regular fa-folder-open"></i>Dokumen</div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">CV</label>
                            <input type="file" name="cv" class="form-control" accept=".pdf,.doc,.docx">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">KTP</label>
                            <input type="file" name="ktp" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-line" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-brand">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Edit --}}
    <div class="modal fade mdk" id="modalEdit" tabindex="-1" data-bs-backdrop="static" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <form class="modal-content" id="formEdit" enctype="multipart/form-data" style="border-radius:12px">
                @csrf
                <div class="modal-header">
                    <h2><i class="fa-solid fa-user-pen"></i>Ubah data instruktur</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-danger ol-errors d-none"></div>
                    <div class="section-title"><i class="fa-regular fa-id-card"></i>Data instruktur</div>
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Kode karyawan</label>
                            <input type="text" name="kode_karyawan" id="editKode" class="form-control" maxlength="20">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label ol-req">Nama</label>
                            <input type="text" name="nama" id="editNama" class="form-control" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label ol-req">Core</label>
                            <input type="text" name="core" id="editCore" class="form-control" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label ol-req">No. HP</label>
                            <input type="text" name="no_hp" id="editHp" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Keterangan</label>
                            <textarea name="keterangan" id="editKet" rows="2" class="form-control"></textarea>
                        </div>
                    </div>
                    <div class="section-title"><i class="fa-regular fa-folder-open"></i>Dokumen</div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Ganti / unggah CV</label>
                            <input type="file" name="cv" class="form-control" accept=".pdf,.doc,.docx">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Ganti / unggah KTP</label>
                            <input type="file" name="ktp" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-line" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-brand">Simpan perubahan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Migrasi --}}
    <div class="modal fade mdk" id="modalMigrasi" tabindex="-1" data-bs-backdrop="static" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content" style="border-radius:12px">
                <div class="modal-header">
                    <div>
                        <h2><i class="fa-solid fa-right-left"></i>Migrasi data OL dari tabel karyawan</h2>
                        <div class="sub mt-1">Pilih karyawan OL yang akan dimasukkan ke Data Instruktur OL.</div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-danger d-none" id="migrasiErrors"></div>
                    <div class="alert alert-success d-none" id="migrasiOk"></div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 tbl">
                            <thead>
                                <tr>
                                    <th style="width:40px"><input type="checkbox" class="form-check-input" id="migrasiAll"></th>
                                    <th>Nama</th>
                                    <th>Jabatan</th>
                                    <th>No. HP</th>
                                    <th style="width:280px">Core <span class="text-danger">*</span></th>
                                </tr>
                            </thead>
                            <tbody id="migrasiBody"></tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <small class="sub me-auto"><span id="migrasiCount">0</span> dipilih</small>
                    <button type="button" class="btn btn-line" data-bs-dismiss="modal">Tutup</button>
                    <button type="button" class="btn btn-brand" id="btnProsesMigrasi">Migrasi yang dipilih</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Detail --}}
    <div class="modal fade mdk mdk-modal" id="modalInstruktur" tabindex="-1" data-bs-backdrop="static"
        aria-labelledby="dNama" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="profile-head">
                    <div class="avatar" id="dAvatar"></div>
                    <div class="flex-grow-1">
                        <h2 id="dNama"></h2>
                        <div class="sub" id="dSub"></div>
                        <div class="mt-2 d-flex gap-2 flex-wrap" id="dTags"></div>
                    </div>
                    <div class="d-flex gap-2 align-self-start">
                        <button type="button" class="btn btn-sm btn-line btn-edit" id="dEdit"><i class="fa-solid fa-pen me-2"></i>Ubah</button>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                </div>

                <ul class="nav nav-tabs" role="tablist">
                    <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tabProfil" type="button" role="tab"><i class="fa-regular fa-id-card"></i>Profil</button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#paneRiwayat" type="button" role="tab"><i class="fa-solid fa-clock-rotate-left"></i>Riwayat mengajar <span class="count" id="count-riwayat">0</span></button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#panePembayaran" type="button" role="tab"><i class="fa-solid fa-money-bill-wave"></i>Riwayat pembayaran <span class="count" id="count-pembayaran">0</span></button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#paneSilabus" type="button" role="tab"><i class="fa-regular fa-file-powerpoint"></i>Silabus <span class="count" id="count-silabus">0</span></button></li>
                </ul>

                <div class="modal-body p-4">
                    <div class="tab-content">

                        {{-- Profil --}}
                        <div class="tab-pane fade show active" id="tabProfil" role="tabpanel">
                            <div class="section-title"><i class="fa-solid fa-user"></i>Biodata</div>
                            <dl class="kv" id="dProfil"></dl>
                            <div class="section-title"><i class="fa-regular fa-note-sticky"></i>Keterangan</div>
                            <p class="note-box" id="dKet"></p>
                        </div>

                        {{-- Riwayat Mengajar --}}
                        <div class="tab-pane fade" id="paneRiwayat" role="tabpanel">
                            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                                <ul class="nav nav-pills">
                                    <li class="nav-item">
                                        <button type="button" class="nav-link active" id="tabRiwayatUtama" data-bs-toggle="tab" data-bs-target="#riwayat-utama">
                                            Instruktur utama <span class="count" id="count-riwayat-utama">0</span>
                                        </button>
                                    </li>
                                    <li class="nav-item">
                                        <button type="button" class="nav-link" data-bs-toggle="tab" data-bs-target="#riwayat-instruktur-2">
                                            Instruktur 2 <span class="count" id="count-riwayat-instruktur-2">0</span>
                                        </button>
                                    </li>
                                    <li class="nav-item">
                                        <button type="button" class="nav-link" data-bs-toggle="tab" data-bs-target="#riwayat-asisten">
                                            Asisten <span class="count" id="count-riwayat-asisten">0</span>
                                        </button>
                                    </li>
                                </ul>
                                <button type="button" class="btn btn-sm btn-line" id="btnExportRiwayat"><i class="fa-solid fa-file-export me-1"></i>Export riwayat</button>
                            </div>
                            <div class="tab-content">
                                <div class="tab-pane fade show active" id="riwayat-utama"><div class="riwayat-list" data-peran="utama"></div></div>
                                <div class="tab-pane fade" id="riwayat-instruktur-2"><div class="riwayat-list" data-peran="instruktur-2"></div></div>
                                <div class="tab-pane fade" id="riwayat-asisten"><div class="riwayat-list" data-peran="asisten"></div></div>
                            </div>
                        </div>

                        {{-- Pembayaran --}}
                        <div class="tab-pane fade" id="panePembayaran" role="tabpanel">
                            <div id="pembayaranList"></div>

                            <div class="section-title"><i class="fa-solid fa-pen"></i><span id="bayarFormTitle">Tambah pembayaran</span></div>
                            <form id="formBayar" class="extend-box" enctype="multipart/form-data">
                                <div class="alert alert-danger ol-errors d-none"></div>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Kelas / riwayat mengajar <small class="text-muted">(opsional)</small></label>
                                        <select name="rkm_id" id="bayarRkm" class="form-select">
                                            <option value="">— Tanpa kelas (pembayaran umum) —</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label ol-req">Judul pembayaran</label>
                                        <input type="text" name="judul" id="bayarJudul" class="form-control" maxlength="255" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label ol-req">Harga (Rp)</label>
                                        <input type="number" name="harga" id="bayarHarga" class="form-control" min="0" step="1" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Perhitungan</label>
                                        <div class="form-check mt-2">
                                            <input type="checkbox" class="form-check-input" name="kali_pax" id="bayarKali" value="1">
                                            <label class="form-check-label" for="bayarKali">Kalikan dengan pax</label>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Pax</label>
                                        <input type="number" name="pax" id="bayarPax" class="form-control" min="1" step="1" disabled>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Total</label>
                                        <input type="text" id="bayarTotal" class="form-control bg-light fw-semibold" readonly>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label ol-req">Tanggal bayar</label>
                                        <input type="date" name="tanggal_bayar" id="bayarTanggal" class="form-control" required>
                                    </div>
                                    <div class="col-md-8">
                                        <label class="form-label">Catatan</label>
                                        <input type="text" name="keterangan" id="bayarKet" class="form-control" maxlength="500" placeholder="Opsional">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Bukti pembayaran <small class="text-muted" id="bayarBuktiHint">(PDF/JPG/PNG, maks. 10 MB)</small></label>
                                        <input type="file" name="bukti" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                                    </div>
                                </div>
                                <div class="mt-3 d-flex gap-2">
                                    <button type="submit" class="btn btn-brand btn-sm" id="bayarSubmit">Simpan pembayaran</button>
                                    <button type="button" class="btn btn-line btn-sm d-none" id="btnCancelBayar">Batal edit</button>
                                </div>
                            </form>
                        </div>

                        {{-- Silabus --}}
                        <div class="tab-pane fade" id="paneSilabus" role="tabpanel">
                            <div id="silabusList"></div>

                            <div class="section-title"><i class="fa-solid fa-cloud-arrow-up"></i><span id="silabusFormTitle">Upload silabus</span></div>
                            <form id="formSilabus" class="extend-box" enctype="multipart/form-data">
                                <div class="alert alert-danger ol-errors d-none"></div>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label ol-req">Judul silabus</label>
                                        <input type="text" name="judul" class="form-control" maxlength="255" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Keterangan</label>
                                        <input type="text" name="keterangan" class="form-control" maxlength="500" placeholder="Opsional">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label ol-req" id="silabusFileLabel">File</label>
                                        <input type="file" name="file" class="form-control" accept=".pdf,.doc,.docx,.ppt,.pptx" required>
                                    </div>
                                </div>
                                <div class="mt-3 d-flex gap-2">
                                    <button type="submit" class="btn btn-brand btn-sm" id="silabusSubmit">Upload</button>
                                    <button type="button" class="btn btn-line btn-sm d-none" id="btnCancelSilabus">Batal edit</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Export --}}
    <div class="modal fade mdk" id="modalExport" tabindex="-1" data-bs-backdrop="static" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content" style="border-radius:12px">
                <div class="modal-header">
                    <h2><i class="fa-solid fa-file-export"></i><span id="exportTitle">Export data</span></h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-danger d-none" id="exportError">Pilih minimal 1 kolom untuk diexport.</div>
                    <div class="mb-3">
                        <label class="form-label">Format</label>
                        <div class="d-flex gap-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="exportFormat" id="fmtPdf" value="pdf" checked>
                                <label class="form-check-label" for="fmtPdf">PDF</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="exportFormat" id="fmtXlsx" value="xlsx">
                                <label class="form-check-label" for="fmtXlsx">Excel (.xlsx)</label>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="form-label mb-0">Kolom yang diexport</label>
                        <button type="button" class="btn btn-link btn-sm p-0" id="exportToggleAll">Pilih / kosongkan semua</button>
                    </div>
                    <div class="row g-2" id="exportColumns"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-line" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-brand" id="btnDoExport">Export</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(function() {
            const KOLOM = {
                list: @json($kolomList),
                riwayat: @json($kolomRiwayat)
            };
            const PERAN = {
                'utama': 'Instruktur Utama',
                'instruktur-2': 'Instruktur 2',
                'asisten': 'Asisten'
            };
            const ROUTES = {
                data: "{{ route('HR.instruktur-ol.data') }}",
                store: "{{ route('HR.instruktur-ol.store') }}",
                update: "{{ route('HR.instruktur-ol.update', '__ID__') }}",
                destroy: "{{ route('HR.instruktur-ol.destroy', '__ID__') }}",
                karyawanOl: "{{ route('HR.instruktur-ol.karyawan-ol') }}",
                migrasi: "{{ route('HR.instruktur-ol.migrasi-karyawan') }}",
                riwayat: "{{ route('HR.instruktur-ol.riwayat', '__ID__') }}",
                riwayatExport: "{{ route('HR.instruktur-ol.riwayat.export', '__ID__') }}",
                export: "{{ route('HR.instruktur-ol.export') }}",
                pembayaran: "{{ route('HR.instruktur-ol.pembayaran.index', '__ID__') }}",
                pembayaranStore: "{{ route('HR.instruktur-ol.pembayaran.store', '__ID__') }}",
                pembayaranUpdate: "{{ route('HR.instruktur-ol.pembayaran.update', '__ID__') }}",
                silabus: "{{ route('HR.instruktur-ol.silabus.index', '__ID__') }}",
                silabusStore: "{{ route('HR.instruktur-ol.silabus.store', '__ID__') }}",
                silabusUpdate: "{{ route('HR.instruktur-ol.silabus.update', '__ID__') }}",
            };

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            });

            let currentQ = '';
            let currentPage = 1;
            let currentId = null;
            let currentData = null;
            let editId = null;
            const INSTR = {};

            const modal = id => bootstrap.Modal.getOrCreateInstance(document.getElementById(id));

            function bukaModal(id) {
                const terbuka = $('.modal.show').not('#' + id)[0];
                if (terbuka) {
                    terbuka.addEventListener('hidden.bs.modal', () => modal(id).show(), {
                        once: true
                    });
                    bootstrap.Modal.getOrCreateInstance(terbuka).hide();
                } else {
                    modal(id).show();
                }
            }

            function esc(s) {
                return String(s ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
            }

            function url(tpl, map) {
                Object.keys(map).forEach(k => {
                    tpl = tpl.split(k).join(encodeURIComponent(map[k]));
                });
                return tpl;
            }

            function fmtDate(s) {
                return s ? String(s).substring(0, 10).split('-').reverse().join('-') : '-';
            }

            function rp(n) {
                return 'Rp ' + Number(n || 0).toLocaleString('id-ID');
            }

            function initials(n) {
                const p = String(n || '').trim().split(/\s+/);
                return (((p[0] || '')[0] || '?') + (p[1] ? p[1][0] : '')).toUpperCase();
            }

            function notifSukses(msg) {
                if (typeof Swal === 'undefined') return;
                Swal.fire({
                    icon: 'success',
                    title: msg,
                    showConfirmButton: false,
                    timer: 2000,
                    timerProgressBar: true
                });
            }

            function notifGagal(msg) {
                if (typeof Swal === 'undefined') {
                    alert(msg);
                    return;
                }
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: msg || 'Terjadi kesalahan, coba lagi.',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true
                });
            }

            function konfirmasi(teks, cb, tombol) {
                if (typeof Swal === 'undefined') {
                    if (confirm(teks)) cb();
                    return;
                }
                Swal.fire({
                    icon: 'warning',
                    title: 'Konfirmasi',
                    text: teks,
                    showCancelButton: true,
                    confirmButtonText: tombol || 'Ya, lanjutkan',
                    cancelButtonText: 'Batal',
                    confirmButtonColor: '#4f46e5'
                }).then(r => {
                    if (r.isConfirmed) cb();
                });
            }

            function failMsg(xhr, fallback) {
                return (xhr.responseJSON && xhr.responseJSON.message) || fallback;
            }

            function showErrors($form, xhr) {
                let msgs = [];
                const j = xhr.responseJSON;
                if (j && j.errors) {
                    Object.values(j.errors).forEach(a => {
                        msgs = msgs.concat(a);
                    });
                } else {
                    msgs.push((j && j.message) || 'Terjadi kesalahan pada server.');
                }
                $form.find('.ol-errors').first().removeClass('d-none')
                    .html('<ul class="mb-0">' + msgs.map(m => `<li>${esc(m)}</li>`).join('') + '</ul>');
            }

            function ajaxForm($form, targetUrl, method) {
                const fd = new FormData($form[0]);
                if (method && method !== 'POST') fd.append('_method', method);
                $form.find('.ol-errors').addClass('d-none').empty();
                const $btn = $form.find('[type=submit]').prop('disabled', true);
                return $.ajax({
                        url: targetUrl,
                        type: 'POST',
                        data: fd,
                        processData: false,
                        contentType: false
                    })
                    .fail(xhr => showErrors($form, xhr))
                    .always(() => $btn.prop('disabled', false));
            }

            function sendDelete(targetUrl) {
                return $.ajax({
                    url: targetUrl,
                    type: 'POST',
                    data: {
                        _method: 'DELETE'
                    }
                });
            }

            function fileTag(u) {
                return u ?
                    `<a href="${esc(u)}" target="_blank" rel="noopener" class="tag tag-done"><i class="fa-solid fa-circle-check"></i>Lihat</a>` :
                    '<span class="tag tag-muted">Belum</span>';
            }

            const emptyRow = (cols, html) => `<tr><td colspan="${cols}" class="empty">${html}</td></tr>`;

            function loadTable(page) {
                currentPage = page || currentPage || 1;
                $('#tabelInstruktur').addClass('is-loading');
                $('.summary').addClass('is-loading');
                $.get(ROUTES.data, {
                    q: currentQ,
                    page: currentPage
                }).done(res => {
                    if (!res.data.length && res.current_page > 1) {
                        loadTable(res.current_page - 1);
                        return;
                    }
                    renderSummary(res.summary);
                    renderTable(res);
                    renderPagination(res);
                }).fail(() => {
                    $('#tableBody').html(emptyRow(7, '<span class="text-danger">Data instruktur gagal dimuat. Coba muat ulang halaman.</span>'));
                    $('#paginationContainer').empty();
                    $('#paginationInfo').empty();
                }).always(() => {
                    $('#tabelInstruktur').removeClass('is-loading');
                    $('.summary').removeClass('is-loading');
                });
            }

            function renderSummary(s) {
                if (!s) return;
                $('#sumTotal').text(s.total);
                $('#sumCv').text(s.tanpa_cv);
                $('#sumKtp').text(s.tanpa_ktp);
                $('#sumKode').text(s.tanpa_kode);
            }

            function renderTable(res) {
                if (!res.data.length) {
                    $('#tableBody').html(emptyRow(7, '<i class="fa-regular fa-folder-open big"></i>Belum ada data instruktur.'));
                    return;
                }
                let html = '';
                res.data.forEach(i => {
                    INSTR[i.id] = i;
                    html += `<tr>
                        <td><div class="person"><span class="avatar">${esc(initials(i.nama))}</span><div><div class="nm">${esc(i.nama)}</div><div class="nip">${i.kode_karyawan ? 'Kode ' + esc(i.kode_karyawan) : 'Kode belum diisi'}</div></div></div></td>
                        <td>${esc(i.core)}</td>
                        <td>${esc(i.no_hp)}</td>
                        <td class="ol-ket">${esc(i.keterangan || '-')}</td>
                        <td class="text-center">${fileTag(i.cv_url)}</td>
                        <td class="text-center">${fileTag(i.ktp_url)}</td>
                        <td class="act text-end"><div class="d-flex justify-content-end gap-1">
                            <button type="button" class="btn btn-sm btn-line btn-detail" data-id="${i.id}" title="Detail"><i class="fa-solid fa-eye"></i></button>
                            <button type="button" class="btn btn-sm btn-line btn-edit" data-id="${i.id}" title="Ubah"><i class="fa-solid fa-pen"></i></button>
                            <button type="button" class="btn btn-sm btn-line danger btn-hapus" data-id="${i.id}" title="Hapus"><i class="fa-solid fa-trash"></i></button>
                        </div></td>
                    </tr>`;
                });
                $('#tableBody').html(html);
            }

            function renderPagination(res) {
                if (!res.total) {
                    $('#paginationInfo').text('');
                    $('#paginationContainer').empty();
                    return;
                }
                $('#paginationInfo').text(`Menampilkan ${res.from} sampai ${res.to} dari ${res.total} instruktur`);
                if (res.last_page <= 1 || !res.links) {
                    $('#paginationContainer').empty();
                    return;
                }
                const items = res.links.map(l => {
                    const label = String(l.label).replace('Previous', 'Sebelumnya').replace('Next', 'Berikutnya');
                    const page = l.url ? new URL(l.url).searchParams.get('page') : '';
                    return `<li class="page-item ${l.active ? 'active' : ''} ${l.url ? '' : 'disabled'}"><a class="page-link" href="#" data-page="${page}">${label}</a></li>`;
                }).join('');
                $('#paginationContainer').html(`<ul class="pagination pagination-sm mb-0">${items}</ul>`);
            }

            loadTable(1);

            let jeda = null;
            $('#formCari [name=q]').on('input', function() {
                clearTimeout(jeda);
                jeda = setTimeout(() => {
                    currentQ = $.trim($(this).val());
                    loadTable(1);
                }, 400);
            });

            $('#btnReset').on('click', function() {
                currentQ = '';
                $('#formCari [name=q]').val('');
                loadTable(1);
            });

            $(document).on('click', '#paginationContainer a.page-link', function(e) {
                e.preventDefault();
                const page = $(this).attr('data-page');
                if (page) loadTable(parseInt(page, 10));
            });

            $('#btnTambah').on('click', function() {
                $('#formTambah')[0].reset();
                $('#formTambah .ol-errors').addClass('d-none').empty();
                modal('modalTambah').show();
            });

            $('#formTambah').on('submit', function(e) {
                e.preventDefault();
                ajaxForm($(this), ROUTES.store, 'POST').done(res => {
                    modal('modalTambah').hide();
                    loadTable(1);
                    notifSukses(res.message || 'Instruktur berhasil ditambahkan.');
                });
            });

            $(document).on('click', '.btn-edit', function() {
                const i = INSTR[$(this).attr('data-id')];
                if (!i) return;
                editId = i.id;
                $('#editKode').val(i.kode_karyawan || '');
                $('#editNama').val(i.nama);
                $('#editCore').val(i.core);
                $('#editHp').val(i.no_hp);
                $('#editKet').val(i.keterangan || '');
                $('#formEdit input[type=file]').val('');
                $('#formEdit .ol-errors').addClass('d-none').empty();
                bukaModal('modalEdit');
            });

            $('#formEdit').on('submit', function(e) {
                e.preventDefault();
                ajaxForm($(this), url(ROUTES.update, {
                    '__ID__': editId
                }), 'PUT').done(res => {
                    modal('modalEdit').hide();
                    loadTable();
                    notifSukses(res.message || 'Data berhasil diperbarui.');
                });
            });

            $(document).on('click', '.btn-hapus', function() {
                const i = INSTR[$(this).attr('data-id')];
                if (!i) return;
                konfirmasi(`Hapus instruktur "${i.nama}"?`, () => {
                    sendDelete(url(ROUTES.destroy, {
                            '__ID__': i.id
                        }))
                        .done(res => {
                            loadTable();
                            notifSukses(res.message || 'Data berhasil dihapus.');
                        })
                        .fail(xhr => notifGagal(failMsg(xhr, 'Gagal menghapus data.')));
                }, 'Ya, hapus');
            });

            function updateMigrasiCount() {
                $('#migrasiCount').text($('#migrasiBody .migrasi-check:checked').length);
            }

            function loadKaryawanOl() {
                $('#migrasiBody').html(emptyRow(5, '<i class="fa-solid fa-spinner fa-spin me-2"></i>Memuat data...').replace('class="empty"', 'class="empty loading"'));
                $('#migrasiAll').prop('checked', false);
                updateMigrasiCount();
                $.get(ROUTES.karyawanOl).done(res => {
                    if (!res.data.length) {
                        $('#migrasiBody').html(emptyRow(5, '<i class="fa-regular fa-circle-check big"></i>Tidak ada karyawan OL yang perlu dimigrasi.'));
                        return;
                    }
                    $('#migrasiBody').html(res.data.map(k => `<tr data-id="${esc(k.id)}">
                        <td><input type="checkbox" class="form-check-input migrasi-check"></td>
                        <td class="fw-semibold">${esc(k.nama)}</td>
                        <td>${esc(k.jabatan || '-')}</td>
                        <td>${esc(k.no_hp || '-')}</td>
                        <td><input type="text" class="form-control form-control-sm migrasi-core" placeholder="Isi core"></td>
                    </tr>`).join(''));
                }).fail(() => {
                    $('#migrasiBody').html(emptyRow(5, '<span class="text-danger">Gagal memuat data.</span>'));
                });
            }

            $('#btnMigrasi').on('click', function() {
                $('#migrasiErrors, #migrasiOk').addClass('d-none').empty();
                modal('modalMigrasi').show();
                loadKaryawanOl();
            });

            $('#migrasiAll').on('change', function() {
                $('#migrasiBody .migrasi-check').prop('checked', this.checked);
                updateMigrasiCount();
            });

            $(document).on('change', '.migrasi-check', updateMigrasiCount);

            $(document).on('input', '.migrasi-core', function() {
                if ($.trim($(this).val())) {
                    $(this).closest('tr').find('.migrasi-check').prop('checked', true);
                    updateMigrasiCount();
                }
            });

            $('#btnProsesMigrasi').on('click', function() {
                const items = [];
                let kosong = false;
                $('#migrasiBody tr').each(function() {
                    const $tr = $(this);
                    if (!$tr.find('.migrasi-check').is(':checked')) return;
                    const core = $.trim($tr.find('.migrasi-core').val());
                    if (!core) {
                        kosong = true;
                        $tr.find('.migrasi-core').addClass('is-invalid');
                        return;
                    }
                    $tr.find('.migrasi-core').removeClass('is-invalid');
                    items.push({
                        karyawan_id: $tr.attr('data-id'),
                        core: core
                    });
                });

                $('#migrasiErrors, #migrasiOk').addClass('d-none').empty();

                if (kosong) {
                    $('#migrasiErrors').removeClass('d-none').text('Core wajib diisi untuk setiap karyawan yang dipilih.');
                    return;
                }
                if (!items.length) {
                    $('#migrasiErrors').removeClass('d-none').text('Pilih minimal 1 karyawan.');
                    return;
                }

                konfirmasi(`Migrasi ${items.length} karyawan ke Data Instruktur OL?`, () => {
                    const $btn = $('#btnProsesMigrasi').prop('disabled', true);
                    $.ajax({
                            url: ROUTES.migrasi,
                            type: 'POST',
                            data: JSON.stringify({
                                items: items
                            }),
                            contentType: 'application/json'
                        })
                        .done(res => {
                            $('#migrasiOk').removeClass('d-none').text(res.message);
                            if (res.gagal && res.gagal.length) {
                                $('#migrasiErrors').removeClass('d-none')
                                    .html('<ul class="mb-0">' + res.gagal.map(m => `<li>${esc(m)}</li>`).join('') + '</ul>');
                            }
                            loadKaryawanOl();
                            loadTable(1);
                        })
                        .fail(xhr => {
                            const j = xhr.responseJSON || {};
                            const msgs = j.errors ? [].concat(...Object.values(j.errors)) :
                                (j.gagal && j.gagal.length ? j.gagal : [j.message || 'Terjadi kesalahan pada server.']);
                            $('#migrasiErrors').removeClass('d-none')
                                .html('<ul class="mb-0">' + msgs.map(m => `<li>${esc(m)}</li>`).join('') + '</ul>');
                        })
                        .always(() => $btn.prop('disabled', false));
                }, 'Ya, migrasi');
            });

            function renderProfil(d) {
                $('#dAvatar').text(initials(d.nama));
                $('#dNama').text(d.nama);
                $('#dSub').text('Kode ' + (d.kode_karyawan || '-') + ' \u00b7 ' + (d.core || '-'));
                $('#dTags').html(
                    `<span class="tag ${d.cv_url ? 'tag-done' : 'tag-pending'}">CV ${d.cv_url ? 'tersedia' : 'belum ada'}</span>` +
                    `<span class="tag ${d.ktp_url ? 'tag-done' : 'tag-pending'}">KTP ${d.ktp_url ? 'tersedia' : 'belum ada'}</span>`
                );
                $('#dEdit').attr('data-id', d.id);

                const dok = (u, label) => u ?
                    `<a href="${esc(u)}" target="_blank" rel="noopener" class="btn btn-sm btn-line"><i class="fa-solid fa-eye me-1"></i>Lihat ${label}</a>` :
                    `<span class="tag tag-muted">${label} belum diunggah</span>`;

                $('#dProfil').html(`
                    <div><dt>Kode karyawan</dt><dd>${esc(d.kode_karyawan || '-')}</dd></div>
                    <div><dt>Nama</dt><dd>${esc(d.nama)}</dd></div>
                    <div><dt>Core</dt><dd>${esc(d.core)}</dd></div>
                    <div><dt>No. HP</dt><dd>${esc(d.no_hp)}</dd></div>
                    <div><dt>CV</dt><dd>${dok(d.cv_url, 'CV')}</dd></div>
                    <div><dt>KTP</dt><dd>${dok(d.ktp_url, 'KTP')}</dd></div>`);
                $('#dKet').text(d.keterangan || '-');
            }

            $(document).on('click', '.btn-detail', function(e) {
                e.preventDefault();
                const d = INSTR[$(this).attr('data-id')];
                if (!d) return;
                currentId = d.id;
                currentData = d;
                renderProfil(d);

                $('.riwayat-list, #pembayaranList, #silabusList').empty();
                $('#count-riwayat, #count-pembayaran, #count-silabus').text('0');
                resetBayarForm();
                resetSilabusForm();

                bootstrap.Tab.getOrCreateInstance($('#modalInstruktur .nav-tabs .nav-link')[0]).show();
                bootstrap.Tab.getOrCreateInstance(document.getElementById('tabRiwayatUtama')).show();
                modal('modalInstruktur').show();

                loadRiwayat();
                loadPembayaran();
                loadSilabus();
            });

            function loadRiwayat() {
                const id = currentId;
                $('.riwayat-list').html('<div class="empty loading"><i class="fa-solid fa-spinner fa-spin me-2"></i>Memuat data...</div>');
                $.get(url(ROUTES.riwayat, {
                        '__ID__': id
                    }))
                    .done(res => {
                        if (id === currentId) renderRiwayat(res);
                    })
                    .fail(() => {
                        $('.riwayat-list').html('<div class="alert alert-danger mb-0">Gagal memuat riwayat mengajar.</div>');
                    });
            }

            function renderRiwayat(res) {
                let total = 0;
                $('#btnExportRiwayat').prop('disabled', !!res.kode_kosong);

                Object.keys(PERAN).forEach(p => {
                    const items = (res.data || {})[p] || [];
                    total += items.length;
                    $('#count-riwayat-' + p).text(items.length);
                    const $box = $(`.riwayat-list[data-peran="${p}"]`);

                    if (!items.length) {
                        $box.html('<div class="empty"><i class="fa-regular fa-calendar-xmark big"></i>' + (res.kode_kosong ?
                            'Kode karyawan belum diisi.' :
                            'Belum ada riwayat sebagai ' + esc(PERAN[p].toLowerCase()) + '.') + '</div>');
                        return;
                    }

                    const rows = items.map((r, i) => `<tr>
                        <td>${i + 1}</td>
                        <td class="fw-semibold">${esc(r.materi)}</td>
                        <td>${esc(r.perusahaan)}</td>
                        <td class="text-nowrap">${fmtDate(r.tanggal_awal)} s/d ${fmtDate(r.tanggal_akhir)}</td>
                        <td>${esc(r.metode_kelas)}</td>
                        <td class="text-center">${esc(r.pax)}</td>
                    </tr>`).join('');

                    $box.html(`<div class="table-responsive"><table class="table table-hover align-middle mb-0 tbl">
                        <thead><tr><th style="width:50px">No.</th><th>Nama materi</th><th>Perusahaan</th><th>Periode</th><th>Metode kelas</th><th style="width:70px" class="text-center">Pax</th></tr></thead>
                        <tbody>${rows}</tbody></table></div>`);
                });

                $('#count-riwayat').text(total);
            }

            let editingBayarId = null;
            let pembayaranData = [];
            let rkmOptions = [];

            function hitungTotal() {
                const harga = parseInt($('#bayarHarga').val(), 10) || 0;
                const kali = $('#bayarKali').is(':checked');
                const pax = parseInt($('#bayarPax').val(), 10) || 0;
                $('#bayarTotal').val(rp(kali ? harga * pax : harga));
            }

            function resetBayarForm() {
                editingBayarId = null;
                $('#formBayar')[0].reset();
                $('#bayarPax').prop('disabled', true);
                $('#bayarFormTitle').text('Tambah pembayaran');
                $('#bayarSubmit').text('Simpan pembayaran');
                $('#btnCancelBayar').addClass('d-none');
                $('#bayarBuktiHint').text('(PDF/JPG/PNG, maks. 10 MB)');
                $('#formBayar .ol-errors').addClass('d-none').empty();
                hitungTotal();
            }

            function loadPembayaran() {
                const id = currentId;
                $('#pembayaranList').html('<div class="empty loading"><i class="fa-solid fa-spinner fa-spin me-2"></i>Memuat data...</div>');
                $.get(url(ROUTES.pembayaran, {
                        '__ID__': id
                    }))
                    .done(res => {
                        if (id === currentId) renderPembayaran(res);
                    })
                    .fail(() => {
                        $('#pembayaranList').html('<div class="alert alert-danger mb-0">Gagal memuat riwayat pembayaran.</div>');
                    });
            }

            function renderPembayaran(res) {
                pembayaranData = res.data || [];
                rkmOptions = res.rkm_options || [];
                $('#count-pembayaran').text(pembayaranData.length);

                const cur = $('#bayarRkm').val();
                $('#bayarRkm').html('<option value="">— Tanpa kelas (pembayaran umum) —</option>' +
                    rkmOptions.map(o => `<option value="${o.id}">${esc(o.label)}</option>`).join('')).val(cur);

                if (!pembayaranData.length) {
                    $('#pembayaranList').html('<div class="empty"><i class="fa-regular fa-credit-card big"></i>Belum ada riwayat pembayaran.</div>');
                    return;
                }

                const rows = pembayaranData.map((p, i) => `<tr>
                    <td>${i + 1}</td>
                    <td class="text-nowrap">${fmtDate(p.tanggal_bayar)}</td>
                    <td class="fw-semibold">${esc(p.judul)}${p.keterangan ? `<div class="small text-muted fw-normal">${esc(p.keterangan)}</div>` : ''}</td>
                    <td>${p.materi ? esc(p.materi) + `<div class="small text-muted">${esc(p.perusahaan || '-')}</div>` : '<span class="text-muted">-</span>'}</td>
                    <td class="text-end text-nowrap">${rp(p.harga)}</td>
                    <td class="text-nowrap">${p.kali_pax ? '× ' + esc(p.pax) + ' pax' : '<span class="tag tag-muted">Flat</span>'}</td>
                    <td class="text-end fw-semibold text-nowrap">${rp(p.total)}</td>
                    <td class="text-center">${fileTag(p.bukti_url)}</td>
                    <td class="act text-end"><div class="d-flex justify-content-end gap-1">
                        <button type="button" class="btn btn-sm btn-line btn-edit-bayar" data-id="${p.id}" title="Ubah"><i class="fa-solid fa-pen"></i></button>
                        <button type="button" class="btn btn-sm btn-line danger btn-del-bayar" data-id="${p.id}" title="Hapus"><i class="fa-solid fa-trash"></i></button>
                    </div></td>
                </tr>`).join('');

                $('#pembayaranList').html(`<div class="table-responsive"><table class="table table-hover align-middle mb-0 tbl">
                    <thead><tr><th style="width:50px">No.</th><th>Tanggal</th><th>Judul</th><th>Kelas</th><th class="text-end">Harga</th><th>Perhitungan</th><th class="text-end">Total</th><th class="text-center">Bukti</th><th class="text-end">Aksi</th></tr></thead>
                    <tbody>${rows}</tbody>
                    <tfoot><tr><th colspan="6" class="text-end">Total pembayaran</th><th class="text-end text-nowrap">${rp(res.total)}</th><th colspan="2"></th></tr></tfoot>
                    </table></div>`);
            }

            $('#bayarHarga, #bayarPax').on('input', hitungTotal);

            $('#bayarKali').on('change', function() {
                $('#bayarPax').prop('disabled', !this.checked);
                if (this.checked && !$('#bayarPax').val()) {
                    const o = rkmOptions.find(x => String(x.id) === String($('#bayarRkm').val()));
                    if (o && o.pax) $('#bayarPax').val(o.pax);
                }
                hitungTotal();
            });

            $('#bayarRkm').on('change', function() {
                const o = rkmOptions.find(x => String(x.id) === String($(this).val()));
                if (!o) return;
                if (o.pax) $('#bayarPax').val(o.pax);
                if (!$.trim($('#bayarJudul').val())) $('#bayarJudul').val('Honor ' + o.materi);
                hitungTotal();
            });

            $('#formBayar').on('submit', function(e) {
                e.preventDefault();
                const target = editingBayarId ?
                    url(ROUTES.pembayaranUpdate, {
                        '__ID__': editingBayarId
                    }) :
                    url(ROUTES.pembayaranStore, {
                        '__ID__': currentId
                    });
                ajaxForm($(this), target, editingBayarId ? 'PUT' : 'POST').done(res => {
                    renderPembayaran(res);
                    resetBayarForm();
                    notifSukses(res.message || 'Pembayaran tersimpan.');
                });
            });

            $(document).on('click', '.btn-edit-bayar', function() {
                const id = parseInt($(this).attr('data-id'), 10);
                const p = pembayaranData.find(x => x.id === id);
                if (!p) return;
                resetBayarForm();
                editingBayarId = id;
                $('#bayarRkm').val(p.rkm_id || '');
                $('#bayarJudul').val(p.judul);
                $('#bayarHarga').val(p.harga);
                $('#bayarKali').prop('checked', !!p.kali_pax);
                $('#bayarPax').prop('disabled', !p.kali_pax).val(p.pax || '');
                $('#bayarTanggal').val(p.tanggal_bayar || '');
                $('#bayarKet').val(p.keterangan || '');
                $('#bayarFormTitle').text('Ubah pembayaran');
                $('#bayarSubmit').text('Simpan perubahan');
                $('#btnCancelBayar').removeClass('d-none');
                $('#bayarBuktiHint').text(p.bukti_url ? '(kosongkan jika tidak ingin mengganti file bukti)' : '(PDF/JPG/PNG, maks. 10 MB)');
                hitungTotal();
                document.getElementById('formBayar').scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });
            });

            $('#btnCancelBayar').on('click', resetBayarForm);

            $(document).on('click', '.btn-del-bayar', function() {
                const id = $(this).attr('data-id');
                konfirmasi('Hapus data pembayaran ini?', () => {
                    sendDelete(url(ROUTES.pembayaranUpdate, {
                            '__ID__': id
                        }))
                        .done(res => {
                            renderPembayaran(res);
                            resetBayarForm();
                            notifSukses(res.message || 'Pembayaran dihapus.');
                        })
                        .fail(xhr => notifGagal(failMsg(xhr, 'Gagal menghapus pembayaran.')));
                }, 'Ya, hapus');
            });

            let editingSilabusId = null;
            let silabusData = [];

            function resetSilabusForm() {
                editingSilabusId = null;
                $('#formSilabus')[0].reset();
                $('#formSilabus [name=file]').prop('required', true);
                $('#silabusFileLabel').text('File').addClass('ol-req');
                $('#silabusFormTitle').text('Upload silabus');
                $('#silabusSubmit').text('Upload');
                $('#btnCancelSilabus').addClass('d-none');
                $('#formSilabus .ol-errors').addClass('d-none').empty();
            }

            function loadSilabus() {
                const id = currentId;
                $('#silabusList').html('<div class="empty loading"><i class="fa-solid fa-spinner fa-spin me-2"></i>Memuat data...</div>');
                $.get(url(ROUTES.silabus, {
                        '__ID__': id
                    }))
                    .done(res => {
                        if (id === currentId) renderSilabus(res.data);
                    })
                    .fail(() => {
                        $('#silabusList').html('<div class="alert alert-danger mb-0">Gagal memuat silabus.</div>');
                    });
            }

            function renderSilabus(items) {
                silabusData = items || [];
                $('#count-silabus').text(silabusData.length);
                if (!silabusData.length) {
                    $('#silabusList').html('<div class="empty"><i class="fa-regular fa-file-lines big"></i>Belum ada silabus.</div>');
                    return;
                }
                const rows = silabusData.map((it, i) => `<tr>
                    <td>${i + 1}</td>
                    <td class="fw-semibold">${esc(it.judul)}</td>
                    <td>${esc(it.keterangan || '-')}</td>
                    <td>${it.file_url ? `<a href="${esc(it.file_url)}" target="_blank" rel="noopener"><i class="fa-regular fa-file me-1"></i>${esc(it.file_nama_asli || 'Lihat file')}</a>` : '-'}</td>
                    <td class="text-nowrap">${fmtDate(it.created_at)}</td>
                    <td class="act text-end"><div class="d-flex justify-content-end gap-1">
                        <button type="button" class="btn btn-sm btn-line btn-edit-silabus" data-id="${it.id}" title="Ubah"><i class="fa-solid fa-pen"></i></button>
                        <button type="button" class="btn btn-sm btn-line danger btn-del-silabus" data-id="${it.id}" title="Hapus"><i class="fa-solid fa-trash"></i></button>
                    </div></td>
                </tr>`).join('');
                $('#silabusList').html(`<div class="table-responsive"><table class="table table-hover align-middle mb-0 tbl">
                    <thead><tr><th style="width:50px">No.</th><th>Judul</th><th>Keterangan</th><th>File</th><th>Diunggah</th><th class="text-end">Aksi</th></tr></thead>
                    <tbody>${rows}</tbody></table></div>`);
            }

            $('#formSilabus').on('submit', function(e) {
                e.preventDefault();
                const target = editingSilabusId ?
                    url(ROUTES.silabusUpdate, {
                        '__ID__': editingSilabusId
                    }) :
                    url(ROUTES.silabusStore, {
                        '__ID__': currentId
                    });
                ajaxForm($(this), target, editingSilabusId ? 'PUT' : 'POST').done(res => {
                    renderSilabus(res.data);
                    resetSilabusForm();
                    notifSukses(res.message || 'Silabus tersimpan.');
                });
            });

            $(document).on('click', '.btn-edit-silabus', function() {
                const id = parseInt($(this).attr('data-id'), 10);
                const it = silabusData.find(x => x.id === id);
                if (!it) return;
                resetSilabusForm();
                editingSilabusId = id;
                $('#formSilabus [name=judul]').val(it.judul);
                $('#formSilabus [name=keterangan]').val(it.keterangan || '');
                $('#formSilabus [name=file]').prop('required', false);
                $('#silabusFileLabel').text('Ganti file (opsional)').removeClass('ol-req');
                $('#silabusFormTitle').text('Ubah silabus');
                $('#silabusSubmit').text('Simpan perubahan');
                $('#btnCancelSilabus').removeClass('d-none');
                document.getElementById('formSilabus').scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });
            });

            $('#btnCancelSilabus').on('click', resetSilabusForm);

            $(document).on('click', '.btn-del-silabus', function() {
                const id = $(this).attr('data-id');
                konfirmasi('Hapus silabus ini?', () => {
                    sendDelete(url(ROUTES.silabusUpdate, {
                            '__ID__': id
                        }))
                        .done(res => {
                            renderSilabus(res.data);
                            resetSilabusForm();
                            notifSukses(res.message || 'Silabus dihapus.');
                        })
                        .fail(xhr => notifGagal(failMsg(xhr, 'Gagal menghapus silabus.')));
                }, 'Ya, hapus');
            });

            let exportCtx = null;

            function openExport(ctx) {
                exportCtx = ctx;
                $('#exportTitle').text(ctx.title);
                $('#exportError').addClass('d-none');
                $('#fmtPdf').prop('checked', true);
                $('#exportColumns').html(Object.entries(KOLOM[ctx.scope]).map(([k, label]) =>
                    `<div class="col-6"><div class="form-check">
                        <input class="form-check-input exp-col" type="checkbox" id="exp-${esc(k)}" value="${esc(k)}" checked>
                        <label class="form-check-label" for="exp-${esc(k)}">${esc(label)}</label>
                    </div></div>`).join(''));
                bukaModal('modalExport');
            }

            $('#btnExportList').on('click', function() {
                openExport({
                    scope: 'list',
                    title: 'Export data instruktur OL',
                    url: ROUTES.export,
                    params: {
                        q: currentQ
                    }
                });
            });

            $('#btnExportRiwayat').on('click', function() {
                openExport({
                    scope: 'riwayat',
                    title: 'Export riwayat mengajar: ' + (currentData ? currentData.nama : ''),
                    url: url(ROUTES.riwayatExport, {
                        '__ID__': currentId
                    }),
                    params: {}
                });
            });

            $('#exportToggleAll').on('click', function() {
                const $all = $('#exportColumns .exp-col');
                $all.prop('checked', $all.filter(':checked').length !== $all.length);
            });

            $('#btnDoExport').on('click', function() {
                const cols = $('#exportColumns .exp-col:checked').map((_, e) => e.value).get();
                if (!cols.length) {
                    $('#exportError').removeClass('d-none');
                    return;
                }
                const params = $.extend({}, exportCtx.params, {
                    format: $('input[name=exportFormat]:checked').val(),
                    columns: cols
                });
                window.location.href = exportCtx.url + '?' + $.param(params);
                modal('modalExport').hide();
            });
        });
    </script>
@endpush