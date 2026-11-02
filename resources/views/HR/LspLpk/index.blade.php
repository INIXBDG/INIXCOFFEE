@extends('layout_HR.app')

@push('styles')
    <style>
        /* ======================= VARIABLES ======================= */
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

            --c-hadir: #059669;
            --c-telat: #dc2626;
            --c-cuti: #0284c7;
            --c-sakit: #7c3aed;
            --c-izin: #0d9488;
            --c-pending: #f97316;
            --c-tidak_hadir: #374151;

            --bg-hadir: #d1fae5;
            --bg-telat: #fee2e2;
            --bg-cuti: #e0f2fe;
            --bg-sakit: #ede9fe;
            --bg-izin: #ccfbf1;
            --bg-pending: #ffedd5;
            --bg-tidak_hadir: #e5e7eb;

            --radius: 10px;
            --shadow-sm: 0 1px 3px rgba(0, 0, 0, .08), 0 1px 2px rgba(0, 0, 0, .05);
            --shadow: 0 4px 6px rgba(0, 0, 0, .07), 0 2px 4px rgba(0, 0, 0, .05);
            --shadow-lg: 0 10px 25px rgba(0, 0, 0, .1), 0 4px 10px rgba(0, 0, 0, .07);
        }

        body {
            background: #fafbfc;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }

        /* ======================= HEADER ======================= */
        .page-header { margin-bottom: 1.5rem; }

        .page-title {
            font-size: 1.6rem;
            font-weight: 700;
            color: var(--gray-900);
            margin-bottom: .15rem;
        }

        .page-sub {
            color: var(--gray-400);
            font-size: .875rem;
        }

        /* ======================= STAT CARD ======================= */
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
            width: 52px;
            height: 52px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            color: #fff;
            flex-shrink: 0;
        }

        .stat-value {
            font-size: 1.6rem;
            font-weight: 700;
            color: var(--gray-900);
            margin: .4rem 0 .15rem;
            line-height: 1.1;
        }

        .stat-label {
            color: var(--gray-400);
            font-size: .78rem;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: .5px;
            font-weight: 600;
        }

        .stat-trend {
            font-size: .72rem;
            font-weight: 600;
            margin-top: .3rem;
            display: inline-flex;
            align-items: center;
            gap: .25rem;
            color: var(--gray-400);
        }

        /* ======================= TABS & CARD ======================= */
        .nav-tabs-custom { border-bottom: 2px solid var(--gray-200); }

        .nav-tabs-custom .nav-link {
            border: none;
            background: transparent;
            color: var(--gray-400);
            font-weight: 600;
            padding: .85rem 1.25rem;
            font-size: .875rem;
            transition: color .2s;
            display: flex;
            align-items: center;
            gap: .5rem;
            white-space: nowrap;
        }

        .nav-tabs-custom .nav-link:hover { color: var(--pri); }

        .nav-tabs-custom .nav-link.active {
            color: var(--pri);
            border-bottom: 3px solid var(--pri);
            background: transparent;
        }

        .card-shell {
            border: none;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            background: #fff;
        }

        .card-shell .card-body { padding: 1.5rem; }

        /* ======================= BUTTONS ======================= */
        .btn-pri,
        .btn-danger-custom {
            border: none;
            color: #fff;
            font-weight: 600;
            padding: .5rem 1.25rem;
            border-radius: 8px;
            transition: all .25s;
            font-size: .85rem;
        }

        .btn-pri { background: var(--pri); }

        .btn-pri:hover {
            background: var(--pri-dark);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(79, 70, 229, .35);
            color: #fff;
        }

        .btn-danger-custom { background: var(--danger); }

        .btn-danger-custom:hover {
            background: #b91c1c;
            transform: translateY(-1px);
            color: #fff;
        }

        .btn-outline-sec {
            background: #fff;
            border: 1px solid var(--gray-200);
            color: var(--gray-600);
            font-weight: 500;
            padding: .4rem 1rem;
            border-radius: 8px;
            transition: all .2s;
            font-size: .85rem;
        }

        .btn-outline-sec:hover {
            background: var(--gray-50);
            border-color: var(--gray-400);
            color: var(--gray-900);
        }

        .btn-icon {
            width: 32px;
            height: 32px;
            padding: 0 !important;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            font-size: .8rem;
        }

        .btn-icon.danger:hover {
            background: var(--danger-light);
            border-color: var(--danger);
            color: var(--danger);
        }

        .btn-icon.edit:hover {
            background: var(--pri-light);
            border-color: var(--pri);
            color: var(--pri);
        }

        /* ======================= FORM ======================= */
        .form-control,
        .form-select {
            border: 1px solid var(--gray-200);
            border-radius: 8px;
            padding: .5rem .85rem;
            font-size: .875rem;
            color: var(--gray-700);
            transition: all .2s;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--pri);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, .12);
            outline: none;
        }

        .form-label {
            font-weight: 600;
            color: var(--gray-700);
            font-size: .72rem;
            margin-bottom: .4rem;
            text-transform: uppercase;
            letter-spacing: .3px;
        }

        .filter-field {
            display: flex;
            flex-direction: column;
        }

        .filter-field .form-label { margin-bottom: .2rem; }

        .filter-hint {
            font-size: .72rem;
            color: var(--gray-400);
            margin-top: .2rem;
        }

        /* ======================= TABLE & BADGE ======================= */
        .chart-title {
            font-size: .875rem;
            font-weight: 700;
            color: var(--gray-700);
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: .5rem;
        }

        .chart-title i { color: var(--pri); }

        .recap-table th {
            font-size: .7rem;
            text-transform: uppercase;
            letter-spacing: .3px;
            color: var(--gray-600);
            white-space: nowrap;
        }

        .recap-table td {
            font-size: .85rem;
            vertical-align: middle;
        }

        .sub {
            font-size: .72rem;
            color: var(--gray-400);
        }

        .risk-badge {
            padding: .3rem .7rem;
            border-radius: 20px;
            font-size: .68rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .5px;
            white-space: nowrap;
            display: inline-block;
        }

        .risk-high   { background: var(--danger-light);  color: var(--danger); }
        .risk-medium { background: var(--warning-light); color: var(--warning); }
        .risk-low    { background: var(--success-light); color: var(--success); }
        .risk-none   { background: var(--gray-100);      color: var(--gray-600); }

        .employee-badge {
            font-size: .65rem;
            padding: .2rem .55rem;
            border-radius: 10px;
            font-weight: 700;
            text-transform: uppercase;
            display: inline-block;
            white-space: nowrap;
        }

        .employee-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: var(--gray-200);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: .75rem;
            flex-shrink: 0;
            overflow: hidden;
        }

        .employee-avatar i {
            font-size: 1.05rem;
            color: var(--gray-400);
        }

        /* ======================= DETAIL & EMPTY STATE ======================= */
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1rem 1.5rem;
        }

        .info-item .lbl {
            font-size: .68rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .4px;
            color: var(--gray-400);
            margin-bottom: .15rem;
        }

        .info-item .val {
            font-size: .9rem;
            color: var(--gray-900);
            font-weight: 600;
            word-break: break-word;
        }

        .empty-state {
            text-align: center;
            padding: 2.5rem 1rem;
            color: var(--gray-400);
        }

        .empty-state i {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            opacity: .4;
            display: block;
        }

        .empty-state p {
            font-size: .88rem;
            margin: 0;
            font-weight: 500;
        }

        .empty-state small { font-size: .78rem; }

        /* ======================= MODAL ======================= */
        .modal-header-custom {
            background: linear-gradient(135deg, var(--pri) 0%, var(--pri-dark) 100%);
            color: #fff;
            border-radius: 12px 12px 0 0;
        }

        .modal-content.modal-custom {
            border-radius: 12px;
            border: none;
            box-shadow: var(--shadow-lg);
        }

        /* ======================= PAGINATION ======================= */
        .pager .pagination { margin-bottom: 0; }

        .pager .page-link {
            border-radius: 6px;
            margin: 0 2px;
            border: 1px solid var(--gray-200);
            color: var(--gray-600);
            font-size: .8rem;
        }

        .pager .page-item.active .page-link {
            background: var(--pri);
            border-color: var(--pri);
            color: #fff;
        }

        .pager .page-item.disabled .page-link { color: var(--gray-400); }

        /* ======================= SKELETON & SCROLLBAR ======================= */
        .skel-line {
            background: linear-gradient(90deg, var(--gray-100) 25%, var(--gray-200) 50%, var(--gray-100) 75%);
            background-size: 200% 100%;
            animation: skeleton-pulse 1.5s infinite;
            border-radius: 6px;
            display: block;
            width: 100%;
        }

        @keyframes skeleton-pulse {
            0%   { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }

        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #c1c1c1; border-radius: 3px; }

        @media (max-width: 767px) {
            .stat-value { font-size: 1.3rem; }
            .page-title { font-size: 1.3rem; }
        }
    </style>
@endpush

@section('content_HR')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">

    <div class="container-fluid px-4 py-4" id="lspApp" data-api="{{ url('HR-dashboard/lsp-lpk/') }}"
        data-csrf="{{ csrf_token() }}">

        {{-- ============ HEADER ============ --}}
        <div class="d-sm-flex align-items-center justify-content-between page-header">
            <div>
                <h1 class="page-title">
                    <i class="fa-solid fa-building-columns me-2" style="color:var(--pri)"></i>Manajemen LSP / LPK
                </h1>
                <p class="page-sub mb-0">Kelola mitra, skema sertifikasi & pelatihan, asesor, dan riwayat pembayaran</p>
            </div>
            <div class="d-flex gap-2 mt-2 mt-sm-0">
                <button class="btn btn-outline-sec" id="btnRefresh">
                    <i class="fa-solid fa-arrows-rotate me-1"></i>Refresh
                </button>
                <button class="btn btn-pri" id="btnAdd">
                    <i class="fa-solid fa-plus me-1"></i><span id="btnAddLabel">Tambah LSP / LPK</span>
                </button>
            </div>
        </div>

        <div class="row g-3 mb-4" id="statRow"></div>

        {{-- ============ TAB NAV ============ --}}
        <ul class="nav nav-tabs nav-tabs-custom mb-4 flex-nowrap overflow-auto" id="mainTabs">
            <li class="nav-item">
                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#pane-lsp" data-tab="lsp">
                    <i class="fa-solid fa-building-columns"></i>LSP / LPK
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#pane-sertifikasi" data-tab="sertifikasi">
                    <i class="fa-solid fa-certificate"></i>Sertifikasi
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#pane-pelatihan" data-tab="pelatihan">
                    <i class="fa-solid fa-chalkboard-user"></i>Pelatihan
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#pane-asesor" data-tab="asesor">
                    <i class="fa-solid fa-user-tie"></i>Asesor
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#pane-riwayat" data-tab="riwayat">
                    <i class="fa-solid fa-clock-rotate-left"></i>Riwayat
                </button>
            </li>
        </ul>

        <div class="tab-content">

            {{-- ============ TAB LSP / LPK ============ --}}
            <div class="tab-pane fade show active" id="pane-lsp">
                <div class="card card-shell mb-4">
                    <div class="card-body py-3">
                        <form class="filter-form d-flex flex-wrap align-items-end justify-content-between gap-3"
                            data-tab="lsp">
                            <span class="fw-bold" style="font-size:.875rem;color:var(--pri)">
                                <i class="fa-solid fa-filter me-1"></i>Filter
                            </span>
                            <div class="d-flex gap-2 flex-wrap align-items-end">
                                <div class="filter-field">
                                    <label class="form-label">Cari nama</label>
                                    <input type="text" id="f-lsp-q" class="form-control form-control-sm"
                                        style="width:220px" placeholder="Nama LSP / LPK">
                                </div>
                                <div class="filter-field">
                                    <label class="form-label">Jenis</label>
                                    <select id="f-lsp-jenis" class="form-select form-select-sm" style="width:110px">
                                        <option value="">Semua</option>
                                        <option>LSP 1</option>
                                        <option>LSP 3</option>
                                        <option>LPK</option>
                                    </select>
                                </div>
                                <div class="filter-field">
                                    <label class="form-label">Status</label>
                                    <select id="f-lsp-status" class="form-select form-select-sm" style="width:130px">
                                        <option value="">Semua</option>
                                        <option>Aktif</option>
                                        <option>Nonaktif</option>
                                        <option>Berakhir</option>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-pri btn-sm">
                                    <i class="fa-solid fa-check me-1"></i>Terapkan
                                </button>
                                <button type="button" class="btn btn-outline-sec btn-sm btn-reset">Reset</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card card-shell">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover recap-table align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>LSP / LPK</th>
                                        <th>Jenis</th>
                                        <th>Based</th>
                                        <th>No. PKS / MoU</th>
                                        <th>Masa Berlaku</th>
                                        <th>PIC</th>
                                        <th class="text-center">Skema</th>
                                        <th class="text-center">Asesor</th>
                                        <th>Status</th>
                                        <th class="text-end">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="tbl-lsp"></tbody>
                            </table>
                        </div>
                        <div class="pager d-flex justify-content-between align-items-center flex-wrap gap-2 mt-3"
                            id="pager-lsp"></div>
                    </div>
                </div>
            </div>

            {{-- ============ TAB SERTIFIKASI & PELATIHAN ============ --}}
            @foreach (['sertifikasi', 'pelatihan'] as $j)
                <div class="tab-pane fade" id="pane-{{ $j }}">
                    <div class="card card-shell mb-4">
                        <div class="card-body py-3">
                            <form class="filter-form d-flex flex-wrap align-items-end justify-content-between gap-3"
                                data-tab="{{ $j }}">
                                <span class="fw-bold" style="font-size:.875rem;color:var(--pri)">
                                    <i class="fa-solid fa-filter me-1"></i>Filter
                                </span>
                                <div class="d-flex gap-2 flex-wrap align-items-end">
                                    <div class="filter-field">
                                        <label class="form-label">Cari skema</label>
                                        <input type="text" id="f-{{ $j }}-q"
                                            class="form-control form-control-sm" style="width:220px"
                                            placeholder="Nama skema">
                                    </div>
                                    <div class="filter-field">
                                        <label class="form-label">LSP / LPK</label>
                                        <select id="f-{{ $j }}-lsp" class="form-select form-select-sm"
                                            style="width:220px">
                                            <option value="">Semua</option>
                                        </select>
                                    </div>
                                    <button type="submit" class="btn btn-pri btn-sm">
                                        <i class="fa-solid fa-check me-1"></i>Terapkan
                                    </button>
                                    <button type="button" class="btn btn-outline-sec btn-sm btn-reset">Reset</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="card card-shell">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover recap-table align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>LSP / LPK</th>
                                            <th>Nama Skema</th>
                                            @if ($j === 'sertifikasi')
                                                <th>Jenis Skema</th>
                                                <th>Jenjang KKNI</th>
                                            @endif
                                            <th>Pelaksanaan</th>
                                            <th class="text-end">
                                                {{ $j === 'sertifikasi' ? 'Harga / Pax' : 'Harga / Hari' }}
                                            </th>
                                            <th class="text-end">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tbl-{{ $j }}"></tbody>
                                </table>
                            </div>
                            <div class="pager d-flex justify-content-between align-items-center flex-wrap gap-2 mt-3"
                                id="pager-{{ $j }}"></div>
                        </div>
                    </div>
                </div>
            @endforeach

            {{-- ============ TAB ASESOR ============ --}}
            <div class="tab-pane fade" id="pane-asesor">
                <div class="card card-shell mb-4">
                    <div class="card-body py-3">
                        <form class="filter-form d-flex flex-wrap align-items-end justify-content-between gap-3"
                            data-tab="asesor">
                            <span class="fw-bold" style="font-size:.875rem;color:var(--pri)">
                                <i class="fa-solid fa-filter me-1"></i>Filter
                            </span>
                            <div class="d-flex gap-2 flex-wrap align-items-end">
                                <div class="filter-field">
                                    <label class="form-label">Cari nama</label>
                                    <input type="text" id="f-asesor-q" class="form-control form-control-sm"
                                        style="width:220px" placeholder="Nama asesor">
                                </div>
                                <div class="filter-field">
                                    <label class="form-label">LSP / LPK</label>
                                    <select id="f-asesor-lsp" class="form-select form-select-sm" style="width:220px">
                                        <option value="">Semua</option>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-pri btn-sm">
                                    <i class="fa-solid fa-check me-1"></i>Terapkan
                                </button>
                                <button type="button" class="btn btn-outline-sec btn-sm btn-reset">Reset</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card card-shell">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover recap-table align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Asesor</th>
                                        <th>Kontak</th>
                                        <th>NPWP</th>
                                        <th>No. KTP</th>
                                        <th>LSP / LPK</th>
                                        <th class="text-end">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="tbl-asesor"></tbody>
                            </table>
                        </div>
                        <div class="pager d-flex justify-content-between align-items-center flex-wrap gap-2 mt-3"
                            id="pager-asesor"></div>
                    </div>
                </div>
            </div>

            {{-- ============ TAB RIWAYAT ============ --}}
            <div class="tab-pane fade" id="pane-riwayat">
                <div class="card card-shell mb-4">
                    <div class="card-body py-3">
                        <form class="filter-form d-flex flex-wrap align-items-end justify-content-between gap-3"
                            data-tab="riwayat">
                            <span class="fw-bold" style="font-size:.875rem;color:var(--pri)">
                                <i class="fa-solid fa-filter me-1"></i>Filter
                            </span>
                            <div class="d-flex gap-2 flex-wrap align-items-end">
                                <div class="filter-field">
                                    <label class="form-label">Cari nama</label>
                                    <input type="text" id="f-riwayat-q" class="form-control form-control-sm"
                                        style="width:200px" placeholder="Asesor / nama manual">
                                </div>
                                <div class="filter-field">
                                    <label class="form-label">Jenis</label>
                                    <select id="f-riwayat-jenis" class="form-select form-select-sm" style="width:160px">
                                        <option value="">Semua</option>
                                        <option value="uji_kompetensi">Uji Kompetensi</option>
                                        <option value="mengajar">Mengajar</option>
                                        <option value="pembayaran">Pembayaran</option>
                                    </select>
                                </div>
                                <div class="filter-field">
                                    <label class="form-label">Asesor</label>
                                    <select id="f-riwayat-asesor" class="form-select form-select-sm"
                                        style="width:200px">
                                        <option value="">Semua</option>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-pri btn-sm">
                                    <i class="fa-solid fa-check me-1"></i>Terapkan
                                </button>
                                <button type="button" class="btn btn-outline-sec btn-sm btn-reset">Reset</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card card-shell">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover recap-table align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Nama</th>
                                        <th>Jenis</th>
                                        <th class="text-end">Nominal</th>
                                        <th>Keterangan</th>
                                        <th class="text-end">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="tbl-riwayat"></tbody>
                            </table>
                        </div>
                        <div class="pager d-flex justify-content-between align-items-center flex-wrap gap-2 mt-3"
                            id="pager-riwayat"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ================= MODAL: FORM LSP / LPK ================= --}}
    <div class="modal fade" id="modalLsp" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content modal-custom">
                <div class="modal-header modal-header-custom">
                    <h5 class="modal-title fw-bold mb-0">
                        <i class="fa-solid fa-building-columns me-2"></i><span class="form-title">Tambah LSP / LPK</span>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="formLsp">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="alert alert-danger form-errors d-none" style="border-radius:8px;font-size:.85rem">
                        </div>
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label">Nama <span class="text-danger">*</span></label>
                                <input type="text" name="nama" class="form-control" required autocomplete="off">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Jenis <span class="text-danger">*</span></label>
                                <select name="jenis" class="form-select" required>
                                    <option value="LSP 1">LSP 1</option>
                                    <option value="LSP 3">LSP 3</option>
                                    <option value="LPK">LPK</option>
                                </select>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Bidang</label>
                                <input type="text" name="bidang" class="form-control"
                                    placeholder="Contoh: Teknologi Informasi">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Provinsi</label>
                                <select name="provinsi" id="provinsi" class="form-select" required>
                                    <option value="">-- Pilih Provinsi --</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Based (Kota / Kabupaten)</label>
                                <select name="based" id="based" class="form-select" required disabled>
                                    <option value="">-- Pilih Provinsi dulu --</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Nomor PKS / MoU</label>
                                <input type="text" name="nomor_pks_mou" class="form-control">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Tanggal Mulai</label>
                                <input type="date" name="tanggal_mulai" class="form-control">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Tanggal Berakhir</label>
                                <input type="date" name="tanggal_berakhir" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">PIC</label>
                                <input type="text" name="pic" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">No. HP</label>
                                <input type="text" name="no_hp" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Website</label>
                                <input type="text" name="website" class="form-control" placeholder="contoh.id">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Status <span class="text-danger">*</span></label>
                                <select name="status" class="form-select" required>
                                    <option>Aktif</option>
                                    <option>Nonaktif</option>
                                    <option>Berakhir</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Keterangan</label>
                                <textarea name="keterangan" rows="2" class="form-control"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0 px-4 pb-4">
                        <button type="button" class="btn btn-outline-sec" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-pri">
                            <i class="fa-solid fa-floppy-disk me-1"></i>Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ================= MODAL: FORM PROGRAM (sertifikasi / pelatihan) ================= --}}
    <div class="modal fade" id="modalProgram" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content modal-custom">
                <div class="modal-header modal-header-custom">
                    <h5 class="modal-title fw-bold mb-0">
                        <i class="fa-solid fa-certificate me-2"></i><span class="form-title">Tambah Sertifikasi</span>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="formProgram">
                    @csrf
                    <input type="hidden" name="jenis" value="sertifikasi">
                    <div class="modal-body p-4">
                        <div class="alert alert-danger form-errors d-none" style="border-radius:8px;font-size:.85rem">
                        </div>
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">LSP / LPK <span class="text-danger">*</span></label>
                                <select name="lsp_id" id="programLsp" class="form-select" required>
                                    <option value="">Pilih LSP / LPK…</option>
                                </select>
                            </div>
                            <div class="col-md-7" id="colNamaSkema">
                                <label class="form-label">Nama Skema <span class="text-danger">*</span></label>
                                <input type="text" name="nama_skema" class="form-control" required
                                    autocomplete="off">
                            </div>
                            <div class="col-md-5 only-sert">
                                <label class="form-label">Jenis Skema</label>
                                <input type="text" name="jenis_skema" class="form-control" list="listJenisSkema">
                                <datalist id="listJenisSkema"></datalist>
                            </div>
                            <div class="col-md only-sert">
                                <label class="form-label">Jenjang KKNI</label>
                                <select name="jenjang_kkni" class="form-select">
                                    <option value="">-</option>
                                    @for ($i = 1; $i <= 9; $i++)
                                        <option>Level {{ $i }}</option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col-md">
                                <label class="form-label">Pelaksanaan</label>
                                <select name="pelaksanaan" class="form-select">
                                    <option value="">-</option>
                                    <option>Offline</option>
                                    <option>Online</option>
                                    <option>Hybrid</option>
                                </select>
                            </div>
                            <div class="col-md only-sert">
                                <label class="form-label">Harga Sertifikasi / Pax (Rp)
                                    <span class="text-danger">*</span></label>
                                <input type="number" name="harga_sertifikasi_per_pax" min="0" step="any"
                                    class="form-control">
                            </div>
                            <div class="col-md only-pel d-none">
                                <label class="form-label">Harga Pelatihan / Hari (Rp)
                                    <span class="text-danger">*</span></label>
                                <input type="number" name="harga_pelatihan_per_hari" min="0" step="any"
                                    class="form-control" disabled>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Keterangan</label>
                                <textarea name="keterangan" rows="2" class="form-control"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0 px-4 pb-4">
                        <button type="button" class="btn btn-outline-sec" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-pri">
                            <i class="fa-solid fa-floppy-disk me-1"></i>Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ================= MODAL: FORM ASESOR ================= --}}
    <div class="modal fade" id="modalAsesor" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content modal-custom">
                <div class="modal-header modal-header-custom">
                    <h5 class="modal-title fw-bold mb-0">
                        <i class="fa-solid fa-user-tie me-2"></i><span class="form-title">Tambah Asesor</span>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="formAsesor">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="alert alert-danger form-errors d-none" style="border-radius:8px;font-size:.85rem">
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                                <input type="text" name="nama_lengkap" class="form-control" required
                                    autocomplete="off">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">No. Telp</label>
                                <input type="text" name="no_telp" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">NPWP</label>
                                <input type="text" name="npwp" class="form-control"
                                    placeholder="00.000.000.0-000.000">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">No. KTP
                                    <span style="text-transform:none;font-weight:500;color:var(--gray-400)">(opsional)</span>
                                </label>
                                <input type="text" name="ktp" maxlength="20" inputmode="numeric"
                                    class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Alamat</label>
                                <input type="text" name="alamat" class="form-control">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Mengawasi LSP / LPK
                                    <span style="text-transform:none;font-weight:500;color:var(--gray-400)">(opsional,
                                        boleh lebih dari satu)</span>
                                </label>
                                <div class="border rounded-3 p-2" id="asesorLspChecks"
                                    style="max-height:170px;overflow-y:auto;border-color:var(--gray-200)!important">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0 px-4 pb-4">
                        <button type="button" class="btn btn-outline-sec" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-pri">
                            <i class="fa-solid fa-floppy-disk me-1"></i>Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ================= MODAL: FORM RIWAYAT ================= --}}
    <div class="modal fade" id="modalRiwayat" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content modal-custom">
                <div class="modal-header modal-header-custom">
                    <h5 class="modal-title fw-bold mb-0">
                        <i class="fa-solid fa-clock-rotate-left me-2"></i><span class="form-title">Tambah Riwayat</span>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="formRiwayat">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="alert alert-danger form-errors d-none" style="border-radius:8px;font-size:.85rem">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Jenis <span class="text-danger">*</span></label>
                            <select name="jenis" class="form-select" required>
                                <option value="uji_kompetensi">Uji Kompetensi</option>
                                <option value="mengajar">Mengajar</option>
                                <option value="pembayaran">Pembayaran</option>
                            </select>
                        </div>
                        <div class="mb-3" id="rwAsesorWrap">
                            <label class="form-label">Asesor / Instruktur</label>
                            <select name="asesor_id" id="riwAsesor" class="form-select">
                                <option value="">— Bukan asesor (isi nama manual) —</option>
                            </select>
                        </div>
                        <div class="mb-3" id="rwManualWrap">
                            <label class="form-label">Nama Manual <span class="text-danger">*</span></label>
                            <input type="text" name="nama_manual" class="form-control"
                                placeholder="Contoh: instruktur eksternal">
                            <div class="filter-hint">Dipakai untuk instruktur yang tidak ada di data asesor.</div>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Tanggal <span class="text-danger">*</span></label>
                                <input type="date" name="tanggal" class="form-control" required>
                            </div>
                            <div class="col-md-6 d-none" id="rwHargaWrap">
                                <label class="form-label">Nominal (Rp) <span class="text-danger">*</span></label>
                                <input type="number" name="harga" min="0" step="any" class="form-control">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Keterangan</label>
                                <textarea name="keterangan" rows="2" class="form-control"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0 px-4 pb-4">
                        <button type="button" class="btn btn-outline-sec" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-pri">
                            <i class="fa-solid fa-floppy-disk me-1"></i>Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ================= MODAL: DETAIL LSP / LPK ================= --}}
    <div class="modal fade" id="modalLspDetail" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content modal-custom">
                <div class="modal-header modal-header-custom">
                    <h5 class="modal-title fw-bold mb-0">
                        <i class="fa-solid fa-building-columns me-2"></i>Detail LSP / LPK
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4" id="lspDetailBody"></div>
            </div>
        </div>
    </div>

    {{-- ================= MODAL: DETAIL ASESOR ================= --}}
    <div class="modal fade" id="modalAsesorDetail" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content modal-custom">
                <div class="modal-header modal-header-custom">
                    <h5 class="modal-title fw-bold mb-0">
                        <i class="fa-solid fa-user-tie me-2"></i>Detail Asesor
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4" id="asesorDetailBody"></div>
            </div>
        </div>
    </div>

    <div class="toast-container position-fixed bottom-0 end-0 p-3" id="toastBox" style="z-index:2000"></div>

@endsection

@push('scripts')
    <script>
        (function() {
            'use strict';

            const $app = $('#lspApp');
            const API = $app.data('api');

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $app.data('csrf'),
                    'Accept': 'application/json'
                }
            });

            // Sesi habis -> muat ulang agar token baru terpakai
            $(document).ajaxError(function(e, xhr) {
                if (xhr.status === 419) {
                    toast('Sesi berakhir, halaman dimuat ulang...', 'error');
                    setTimeout(() => location.reload(), 1500);
                }
            });

            /* ======================= UTIL ======================= */
            const ESC_MAP = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#39;'
            };
            const esc = v => String(v ?? '').replace(/[&<>"']/g, c => ESC_MAP[c]);
            const isEmpty = v => v === null || v === undefined || v === '';
            const dash = v => isEmpty(v) ? '-' : v;
            const rp = v => isEmpty(v) ? '-' : 'Rp ' + Number(v).toLocaleString('id-ID', {
                maximumFractionDigits: 0
            });
            const fd = s => s ? String(s).substring(0, 10).split('-').reverse().join('/') : '-';
            const mask = k => k ? String(k).substring(0, 6) + '*'.repeat(Math.max(0, String(k).length - 6)) : '-';
            const badge = (key, text) =>
                `<span class="employee-badge" style="background:var(--bg-${key});color:var(--c-${key})">${esc(text)}</span>`;
            const errMsg = xhr => xhr.responseJSON?.message ||
                (xhr.status === 419 ? 'Sesi berakhir, silakan muat ulang halaman.' : 'Terjadi kesalahan (' + xhr.status + ')');
            const errList = xhr => xhr.responseJSON?.errors ? Object.values(xhr.responseJSON.errors).flat() : [errMsg(xhr)];

            function sisaHari(end) {
                if (!end) return null;
                const [y, m, d] = String(end).substring(0, 10).split('-').map(Number);
                const t = new Date();
                const today = new Date(t.getFullYear(), t.getMonth(), t.getDate());
                return Math.round((new Date(y, m - 1, d) - today) / 86400000);
            }

            const G = {
                pri: 'linear-gradient(135deg,#4f46e5,#818cf8)',
                ok: 'linear-gradient(135deg,#059669,#10b981)',
                warn: 'linear-gradient(135deg,#d97706,#f59e0b)',
                info: 'linear-gradient(135deg,#0284c7,#38bdf8)',
                teal: 'linear-gradient(135deg,#0d9488,#2dd4bf)',
                vio: 'linear-gradient(135deg,#7c3aed,#a78bfa)',
                gray: 'linear-gradient(135deg,#6b7280,#9ca3af)'
            };
            const STATUS_CLS = {
                Aktif: 'risk-low',
                Nonaktif: 'risk-none',
                Berakhir: 'risk-high'
            };
            const JENIS_COLOR = {
                LSP: 'cuti',
                LPK: 'sakit'
            };
            const PEL_COLOR = {
                Online: 'cuti',
                Offline: 'izin',
                Hybrid: 'sakit'
            };
            const RW = {
                uji_kompetensi: ['Uji Kompetensi', 'sakit'],
                mengajar: ['Mengajar', 'izin'],
                pembayaran: ['Pembayaran', 'hadir']
            };

            function toast(msg, type) {
                const err = type === 'error';
                const $t = $(`
                    <div class="toast align-items-center ${err ? 'text-bg-danger' : 'text-bg-success'} border-0" role="alert">
                        <div class="d-flex">
                            <div class="toast-body">
                                <i class="fa-solid ${err ? 'fa-circle-exclamation' : 'fa-circle-check'} me-2"></i>${esc(msg)}
                            </div>
                            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                        </div>
                    </div>`);
                $('#toastBox').append($t);
                new bootstrap.Toast($t[0], {
                    delay: err ? 6000 : 2800
                }).show();
                $t[0].addEventListener('hidden.bs.toast', () => $t.remove());
            }

            /* ======================= MODAL HELPERS ======================= */
            const mEl = id => document.getElementById(id);
            const showModal = id => bootstrap.Modal.getOrCreateInstance(mEl(id)).show();
            const hideModal = id => bootstrap.Modal.getInstance(mEl(id))?.hide();

            // Tutup modal A, setelah benar-benar tertutup jalankan fn (menghindari modal bertumpuk)
            function swapModal(fromId, fn) {
                const el = mEl(fromId);
                const inst = bootstrap.Modal.getInstance(el);
                if (!inst || !el.classList.contains('show')) {
                    fn();
                    return;
                }
                el.addEventListener('hidden.bs.modal', () => fn(), {
                    once: true
                });
                inst.hide();
            }

            /* ======================= STATE ======================= */
            const state = {
                tab: 'lsp',
                page: {},
                loaded: {},
                stats: {},
                items: {},
                options: {
                    lsps: [],
                    asesors: []
                },
                returnTo: null,
                rwLock: null,
                detail: {
                    lsp: null,
                    asesor: null,
                    riwayat: {}
                }
            };

            /* ======================= STAT CARDS ======================= */
            const statCard = ([label, val, hint, icon, grad, color], col) => `
                <div class="${col}">
                    <div class="card stat-card h-100">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div>
                                <p class="stat-label">${esc(label)}</p>
                                <h3 class="stat-value" style="color:${color}">${esc(val)}</h3>
                                <div class="stat-trend">${esc(hint)}</div>
                            </div>
                            <div class="stat-icon" style="background:${grad}"><i class="fa-solid ${icon}"></i></div>
                        </div>
                    </div>
                </div>`;

            const renderCards = cards => cards
                .map(c => statCard(c, cards.length === 4 ? 'col-xl-3 col-md-6' : 'col-xl-4 col-md-6'))
                .join('');

            const skeletonStats = () => [1, 2, 3, 4].map(() => `
                <div class="col-xl-3 col-md-6">
                    <div class="card stat-card h-100">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div style="flex:1">
                                <span class="skel-line" style="width:120px;height:10px;margin-bottom:10px"></span>
                                <span class="skel-line" style="width:70px;height:24px;margin-bottom:8px"></span>
                                <span class="skel-line" style="width:140px;height:11px"></span>
                            </div>
                            <span class="skel-line" style="width:52px;height:52px;border-radius:12px;flex-shrink:0"></span>
                        </div>
                    </div>
                </div>`).join('');

            function programStats(s, lbl, icon) {
                return [
                    ['Total Skema', s.total, 'Skema terdaftar', icon, G.pri, 'var(--pri)'],
                    ['LSP / LPK Terlibat', s.lsp, 'Mitra yang punya skema', 'fa-building-columns', G.info, 'var(--info)'],
                    ['Rata-rata ' + lbl, rp(s.avg != null ? Math.round(s.avg) : null),
                        'Terendah ' + rp(s.min) + ' · Tertinggi ' + rp(s.max),
                        'fa-money-bill-wave', G.ok, 'var(--success)'
                    ]
                ];
            }

            const STATS = {
                lsp: s => [
                    ['Total LSP / LPK', s.total, 'Seluruh mitra terdaftar', 'fa-building-columns', G.pri, 'var(--pri)'],
                    ['Mitra Aktif', s.aktif, 'Status kerja sama aktif', 'fa-circle-check', G.ok, 'var(--success)'],
                    ['Segera Berakhir', s.segera, 'Berakhir dalam 90 hari', 'fa-hourglass-half', G.warn, 'var(--warning)'],
                    ['Total Skema', s.program, 'Sertifikasi + pelatihan', 'fa-certificate', G.info, 'var(--info)']
                ],
                sertifikasi: s => programStats(s, 'Harga / Pax', 'fa-certificate'),
                pelatihan: s => programStats(s, 'Harga / Hari', 'fa-chalkboard-user'),
                asesor: s => [
                    ['Total Asesor', s.total, 'Seluruh asesor terdaftar', 'fa-user-tie', G.pri, 'var(--pri)'],
                    ['Terhubung LSP / LPK', s.dengan, 'Punya minimal satu mitra', 'fa-link', G.ok, 'var(--success)'],
                    ['Tanpa LSP / LPK', s.tanpa, 'Belum dihubungkan', 'fa-link-slash', G.gray, 'var(--gray-600)']
                ],
                riwayat: s => [
                    ['Uji Kompetensi', s.uji, 'Total kegiatan uji', 'fa-clipboard-check', G.vio, 'var(--c-sakit)'],
                    ['Mengajar', s.mengajar, 'Total sesi mengajar', 'fa-chalkboard-user', G.teal, 'var(--c-izin)'],
                    ['Total Pembayaran', rp(s.pembayaran), 'Akumulasi seluruh honor', 'fa-money-bill-wave', G.ok, 'var(--success)']
                ]
            };

            function renderStats(tab) {
                const s = state.stats[tab];
                $('#statRow').html(s ? renderCards(STATS[tab](s)) : skeletonStats());
            }

            /* ======================= ROW RENDERERS ======================= */
            const actions = (tab, id, view) => `
                <td class="text-end" style="white-space:nowrap">
                    ${view ? `<button type="button" class="btn btn-outline-sec btn-icon" title="Detail" data-act="view" data-tab="${tab}" data-id="${id}"><i class="fa-solid fa-eye"></i></button> ` : ''}
                    <button type="button" class="btn btn-outline-sec btn-icon edit" title="Edit" data-act="edit" data-tab="${tab}" data-id="${id}"><i class="fa-solid fa-pen"></i></button>
                    <button type="button" class="btn btn-outline-sec btn-icon danger" title="Hapus" data-act="delete" data-tab="${tab}" data-id="${id}"><i class="fa-solid fa-trash"></i></button>
                </td>`;

            function masaBerlaku(l) {
                if (!l.tanggal_mulai && !l.tanggal_berakhir) return '-';

                let html = `${l.tanggal_mulai ? fd(l.tanggal_mulai) : '?'} – ${l.tanggal_berakhir ? fd(l.tanggal_berakhir) : '?'}`;
                const s = sisaHari(l.tanggal_berakhir);

                if (l.status === 'Aktif' && s !== null) {
                    if (s < 0) {
                        html += `<div class="sub" style="color:var(--danger);font-weight:600">Lewat ${Math.abs(s)} hari</div>`;
                    } else if (s <= 90) {
                        html += `<div class="sub" style="color:var(--warning);font-weight:600">Sisa ${s} hari</div>`;
                    }
                }
                return html;
            }

            const rowLsp = l => `
                <tr>
                    <td>
                        <a href="#" class="fw-semibold text-decoration-none" style="color:var(--gray-900)" data-act="view" data-tab="lsp" data-id="${l.id}">${esc(l.nama)}</a>
                        <div class="sub">${esc(l.bidang || '-')}</div>
                    </td>
                    <td>${badge(JENIS_COLOR[l.jenis] || 'tidak_hadir', l.jenis)}</td>
                    <td>${esc(l.based || '-')}</td>
                    <td>${esc(l.nomor_pks_mou || '-')}</td>
                    <td style="white-space:nowrap">${masaBerlaku(l)}</td>
                    <td>${esc(l.pic || '-')}<div class="sub">${esc(l.no_hp || '')}</div></td>
                    <td class="text-center">${badge('cuti', l.sertifikasis_count + ' S')} ${badge('izin', l.pelatihans_count + ' P')}</td>
                    <td class="text-center fw-bold">${l.asesors_count}</td>
                    <td><span class="risk-badge ${STATUS_CLS[l.status] || 'risk-none'}">${esc(l.status)}</span></td>
                    ${actions('lsp', l.id, true)}
                </tr>`;

            const rowProgram = jenis => p => {
                const sert = jenis === 'sertifikasi';
                return `
                    <tr>
                        <td>
                            <div class="fw-semibold" style="color:var(--gray-900)">${esc(p.lsp?.nama || '-')}</div>
                            <div class="sub">${esc(p.lsp?.jenis || '')}</div>
                        </td>
                        <td>
                            <div class="fw-semibold">${esc(p.nama_skema)}</div>
                            <div class="sub">${esc(p.keterangan || '')}</div>
                        </td>
                        ${sert ? `<td>${esc(p.jenis_skema || '-')}</td><td>${esc(p.jenjang_kkni || '-')}</td>` : ''}
                        <td>${p.pelaksanaan ? badge(PEL_COLOR[p.pelaksanaan] || 'tidak_hadir', p.pelaksanaan) : '-'}</td>
                        <td class="text-end fw-semibold" style="white-space:nowrap">${rp(sert ? p.harga_sertifikasi_per_pax : p.harga_pelatihan_per_hari)}</td>
                        ${actions(jenis, p.id, false)}
                    </tr>`;
            };

            const lspBadges = lsps => lsps.map(l =>
                `<span class="employee-badge me-1" style="background:var(--pri-light);color:var(--pri)">${esc(l.nama)}</span>`
            ).join('');

            const rowAsesor = a => `
                <tr>
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="employee-avatar"><i class="fa-solid fa-user"></i></div>
                            <div>
                                <a href="#" class="fw-semibold text-decoration-none" style="color:var(--gray-900)" data-act="view" data-tab="asesor" data-id="${a.id}">${esc(a.nama_lengkap)}</a>
                                <div class="sub">${esc(a.alamat || '-')}</div>
                            </div>
                        </div>
                    </td>
                    <td>${esc(a.no_telp || '-')}<div class="sub">${esc(a.email || '')}</div></td>
                    <td>${esc(a.npwp || '-')}</td>
                    <td>${esc(mask(a.ktp))}</td>
                    <td>${(a.lsps || []).length
                        ? lspBadges(a.lsps)
                        : '<span style="color:var(--gray-400);font-size:.8rem">Belum terhubung</span>'}</td>
                    ${actions('asesor', a.id, true)}
                </tr>`;

            const rowRiwayat = r => `
                <tr>
                    <td style="white-space:nowrap">${fd(r.tanggal)}</td>
                    <td>${r.asesor
                        ? `<a href="#" class="fw-semibold text-decoration-none" style="color:var(--gray-900)" data-act="view" data-tab="asesor" data-id="${r.asesor.id}">${esc(r.asesor.nama_lengkap)}</a>`
                        : `<span class="fw-semibold">${esc(r.nama_manual)}</span> ${badge('pending', 'Manual')}`}</td>
                    <td>${badge(RW[r.jenis][1], RW[r.jenis][0])}</td>
                    <td class="text-end fw-semibold" style="white-space:nowrap">${r.jenis === 'pembayaran' ? rp(r.harga) : '-'}</td>
                    <td style="color:var(--gray-600)">${esc(r.keterangan || '-')}</td>
                    ${actions('riwayat', r.id, false)}
                </tr>`;

            /* ======================= TAB CONFIG ======================= */
            const TABS = {
                lsp: {
                    url: API + '/lsp',
                    body: '#tbl-lsp',
                    pager: '#pager-lsp',
                    cols: 10,
                    row: rowLsp,
                    addLabel: 'Tambah LSP / LPK',
                    add: () => openLspForm(),
                    filters: () => ({
                        q: $('#f-lsp-q').val(),
                        jenis: $('#f-lsp-jenis').val(),
                        status: $('#f-lsp-status').val()
                    })
                },
                sertifikasi: {
                    url: API + '/program/sertifikasi',
                    body: '#tbl-sertifikasi',
                    pager: '#pager-sertifikasi',
                    cols: 7,
                    row: rowProgram('sertifikasi'),
                    addLabel: 'Tambah Sertifikasi',
                    add: () => openProgramForm('sertifikasi'),
                    filters: () => ({
                        q: $('#f-sertifikasi-q').val(),
                        lsp_id: $('#f-sertifikasi-lsp').val()
                    })
                },
                pelatihan: {
                    url: API + '/program/pelatihan',
                    body: '#tbl-pelatihan',
                    pager: '#pager-pelatihan',
                    cols: 5,
                    row: rowProgram('pelatihan'),
                    addLabel: 'Tambah Pelatihan',
                    add: () => openProgramForm('pelatihan'),
                    filters: () => ({
                        q: $('#f-pelatihan-q').val(),
                        lsp_id: $('#f-pelatihan-lsp').val()
                    })
                },
                asesor: {
                    url: API + '/asesor',
                    body: '#tbl-asesor',
                    pager: '#pager-asesor',
                    cols: 6,
                    row: rowAsesor,
                    addLabel: 'Tambah Asesor',
                    add: () => openAsesorForm(),
                    filters: () => ({
                        q: $('#f-asesor-q').val(),
                        lsp_id: $('#f-asesor-lsp').val()
                    })
                },
                riwayat: {
                    url: API + '/riwayat',
                    body: '#tbl-riwayat',
                    pager: '#pager-riwayat',
                    cols: 6,
                    row: rowRiwayat,
                    addLabel: 'Tambah Riwayat',
                    add: () => openRiwayatForm(),
                    filters: () => ({
                        q: $('#f-riwayat-q').val(),
                        jenis: $('#f-riwayat-jenis').val(),
                        asesor_id: $('#f-riwayat-asesor').val()
                    })
                }
            };

            const EMPTY = {
                lsp: ['fa-building-columns', 'Belum ada data LSP / LPK', 'Klik "Tambah LSP / LPK" untuk mulai mengisi.'],
                sertifikasi: ['fa-certificate', 'Belum ada data sertifikasi', 'Pastikan LSP / LPK sudah ditambahkan terlebih dahulu.'],
                pelatihan: ['fa-chalkboard-user', 'Belum ada data pelatihan', 'Pastikan LSP / LPK sudah ditambahkan terlebih dahulu.'],
                asesor: ['fa-user-tie', 'Belum ada data asesor', 'Klik "Tambah Asesor" untuk mulai mengisi.'],
                riwayat: ['fa-clock-rotate-left', 'Belum ada riwayat', 'Klik "Tambah Riwayat" untuk mencatat kegiatan atau pembayaran.']
            };

            const emptyRow = tab => `
                <tr><td colspan="${TABS[tab].cols}">
                    <div class="empty-state">
                        <i class="fa-solid ${EMPTY[tab][0]}"></i>
                        <p>${EMPTY[tab][1]}</p>
                        <small>${EMPTY[tab][2]}</small>
                    </div>
                </td></tr>`;

            const errorRow = cols => `
                <tr><td colspan="${cols}">
                    <div class="empty-state">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                        <p>Gagal memuat data</p>
                        <small>Klik Refresh untuk mencoba lagi.</small>
                    </div>
                </td></tr>`;

            const skeletonRows = cols => [1, 2, 3, 4, 5].map(() =>
                `<tr><td colspan="${cols}"><span class="skel-line" style="height:20px"></span></td></tr>`).join('');

            /* ======================= LOAD DATA ======================= */
            function loadTab(tab, page) {
                const t = TABS[tab];
                state.page[tab] = page || state.page[tab] || 1;
                state.loaded[tab] = true;
                $(t.body).html(skeletonRows(t.cols));
                if (tab === state.tab) $('#statRow').html(skeletonStats());

                return $.get(t.url, Object.assign({}, t.filters(), {
                        page: state.page[tab]
                    }))
                    .done(res => {
                        if (!res.success) {
                            toast(res.message || 'Gagal memuat data', 'error');
                            return;
                        }
                        // halaman kosong setelah hapus -> mundur ke halaman terakhir
                        if (!res.data.length && res.meta.current_page > Math.max(1, res.meta.last_page)) {
                            loadTab(tab, Math.max(1, res.meta.last_page));
                            return;
                        }
                        state.stats[tab] = res.stats;
                        state.items[tab] = {};
                        res.data.forEach(i => {
                            state.items[tab][i.id] = i;
                        });
                        $(t.body).html(res.data.length ? res.data.map(t.row).join('') : emptyRow(tab));
                        renderPager(tab, res.meta);
                        if (tab === state.tab) renderStats(tab);
                    })
                    .fail(xhr => {
                        state.loaded[tab] = false;
                        $(t.body).html(errorRow(t.cols));
                        if (tab === state.tab) $('#statRow').empty();
                        toast(errMsg(xhr), 'error');
                    });
            }

            // Tandai tab perlu dimuat ulang; muat sekarang bila tab itu sedang aktif
            function invalidate(list) {
                (list || Object.keys(TABS)).forEach(t => {
                    state.loaded[t] = false;
                });
                if (!state.loaded[state.tab]) loadTab(state.tab, state.page[state.tab] || 1);
            }

            function renderPager(tab, m) {
                const $p = $(TABS[tab].pager).data('tab', tab);
                if (!m.total) {
                    $p.empty();
                    return;
                }

                const from = (m.current_page - 1) * m.per_page + 1;
                const to = Math.min(m.current_page * m.per_page, m.total);

                let items = '';
                let prev = 0;
                for (let i = 1; i <= m.last_page; i++) {
                    if (!(i === 1 || i === m.last_page || Math.abs(i - m.current_page) <= 2)) continue;
                    if (prev && i - prev > 1) {
                        items += '<li class="page-item disabled"><span class="page-link">…</span></li>';
                    }
                    items += `<li class="page-item ${i === m.current_page ? 'active' : ''}"><a class="page-link" href="#" data-page="${i}">${i}</a></li>`;
                    prev = i;
                }

                const nav = m.last_page > 1 ? `
                    <ul class="pagination pagination-sm">
                        <li class="page-item ${m.current_page === 1 ? 'disabled' : ''}"><a class="page-link" href="#" data-page="${m.current_page - 1}">‹</a></li>
                        ${items}
                        <li class="page-item ${m.current_page === m.last_page ? 'disabled' : ''}"><a class="page-link" href="#" data-page="${m.current_page + 1}">›</a></li>
                    </ul>` : '';

                $p.html(`<small style="color:var(--gray-400)">Menampilkan ${from}–${to} dari ${m.total} data</small>${nav}`);
            }

            /* ======================= OPTIONS (dropdown & checklist) ======================= */
            function loadOptions() {
                return $.get(API + '/options').done(res => {
                    if (!res.success) return;
                    state.options = {
                        lsps: res.lsps,
                        asesors: res.asesors
                    };
                    populateOptions();
                });
            }

            // Isi ulang <select> sambil mempertahankan nilai yang sedang dipilih
            function fillSelect(sel, firstOption, optionsHtml) {
                const $s = $(sel);
                const v = $s.val() || '';
                $s.html(firstOption + optionsHtml).val(v);
            }

            function populateOptions() {
                const {
                    lsps,
                    asesors
                } = state.options;

                const lspOpts = lsps.map(l => `<option value="${l.id}">${esc(l.nama)}</option>`).join('');
                ['#f-sertifikasi-lsp', '#f-pelatihan-lsp', '#f-asesor-lsp'].forEach(sel =>
                    fillSelect(sel, '<option value="">Semua</option>', lspOpts));

                const progOpts = lsps.map(l => `<option value="${l.id}">${esc(l.nama)} (${esc(l.jenis)})</option>`).join('');
                fillSelect('#programLsp', '<option value="">Pilih LSP / LPK…</option>', progOpts);

                const asesorOpts = asesors.map(a => `<option value="${a.id}">${esc(a.nama_lengkap)}</option>`).join('');
                fillSelect('#f-riwayat-asesor', '<option value="">Semua</option>', asesorOpts);
                fillSelect('#riwAsesor', '<option value="">— Bukan asesor (isi nama manual) —</option>', asesorOpts);

                $('#asesorLspChecks').html(lsps.length
                    ? lsps.map(l => `
                        <div class="form-check py-1">
                            <input class="form-check-input lsp-check" type="checkbox" name="lsp_ids[]" value="${l.id}" id="lspChk${l.id}">
                            <label class="form-check-label" for="lspChk${l.id}" style="font-size:.85rem">${esc(l.nama)} <span class="sub">(${esc(l.jenis)})</span></label>
                        </div>`).join('')
                    : '<div class="sub p-2">Belum ada LSP / LPK. Asesor tetap bisa disimpan tanpa relasi.</div>');
            }

            /* ======================= FORMS ======================= */
            function prepForm(sel, item, titleAdd, titleEdit) {
                const $f = $(sel);
                $f[0].reset();
                $f.find('.form-errors').addClass('d-none').empty();
                $f.data('id', item && item.id ? item.id : null);
                $f.closest('.modal').find('.form-title').text(item && item.id ? titleEdit : titleAdd);
                return $f;
            }

            function fillForm($f, item, fields) {
                if (!item) return;
                fields.forEach(k => {
                    let v = item[k] ?? '';
                    if (k.indexOf('tanggal') === 0 && v) v = String(v).substring(0, 10);
                    $f.find('[name="' + k + '"]').val(v);
                });
            }

            function showErrors($f, errs) {
                $f.find('.form-errors')
                    .removeClass('d-none')
                    .html('<ul class="mb-0 ps-3">' + errs.map(e => `<li>${esc(e)}</li>`).join('') + '</ul>');
                $f.find('.modal-body').scrollTop(0);
            }

            function bindForm(sel, cfg) {
                $(sel).on('submit', function(e) {
                    e.preventDefault();
                    const $f = $(this);
                    const id = $f.data('id');
                    const $b = $f.find('button[type="submit"]');
                    const label = $b.html();

                    $f.find('.form-errors').addClass('d-none').empty();
                    $b.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...');

                    $.ajax({
                        url: id ? cfg.base + '/' + id : cfg.base,
                        method: 'POST',
                        data: $f.serialize() + (id ? '&_method=PUT' : '')
                    }).done(res => {
                        if (res.success === false) {
                            showErrors($f, [res.message || 'Gagal menyimpan']);
                            return;
                        }
                        hideModal(cfg.modal);
                        toast(res.message || 'Data tersimpan');
                        cfg.done(res);
                    }).fail(xhr => {
                        showErrors($f, errList(xhr));
                    }).always(() => {
                        $b.prop('disabled', false).html(label);
                    });
                });
            }

            function openLspForm(item) {
                const $f = prepForm('#formLsp', item, 'Tambah LSP / LPK', 'Edit LSP / LPK');
                fillForm($f, item, [
                    'nama', 'jenis', 'bidang', 'based', 'nomor_pks_mou', 'tanggal_mulai', 'tanggal_berakhir',
                    'pic', 'no_hp', 'email', 'website', 'status', 'keterangan'
                ]);
                showModal('modalLsp');
            }

            function openProgramForm(jenis, item) {
                const sert = jenis === 'sertifikasi';
                const label = sert ? 'Sertifikasi' : 'Pelatihan';
                const $f = prepForm('#formProgram', item, 'Tambah ' + label, 'Edit ' + label);

                $f.find('[name="jenis"]').val(jenis);

                // Field khusus jenis: yang tidak dipakai disembunyikan + disabled (tidak ikut terkirim)
                $f.find('.only-sert').toggleClass('d-none', !sert).find(':input').prop('disabled', !sert);
                $f.find('.only-pel').toggleClass('d-none', sert).find(':input').prop('disabled', sert);
                $f.find('[name="harga_sertifikasi_per_pax"]').prop('required', sert);
                $f.find('[name="harga_pelatihan_per_hari"]').prop('required', !sert);

                // Nama skema melebar penuh bila Jenis Skema disembunyikan (pelatihan)
                $('#colNamaSkema').toggleClass('col-md-7', sert).toggleClass('col-md-12', !sert);

                const jenisSkema = ['Okupasi', 'Klaster', 'KKNI'];
                $('#listJenisSkema').html(jenisSkema.map(o => `<option value="${o}">`).join(''));
                $f.find('[name="jenis_skema"]').attr('placeholder', jenisSkema.join(' / '));

                if (item) {
                    fillForm($f, item, [
                        'lsp_id', 'nama_skema', 'jenis_skema', 'jenjang_kkni', 'pelaksanaan',
                        'harga_sertifikasi_per_pax', 'harga_pelatihan_per_hari', 'keterangan'
                    ]);
                } else {
                    const pre = $('#f-' + jenis + '-lsp').val();
                    if (pre) $f.find('[name="lsp_id"]').val(pre);
                }
                showModal('modalProgram');
            }

            function openAsesorForm(item) {
                const $f = prepForm('#formAsesor', item, 'Tambah Asesor', 'Edit Asesor');
                fillForm($f, item, ['nama_lengkap', 'no_telp', 'email', 'npwp', 'ktp', 'alamat']);

                const ids = item ? (item.lsps || []).map(l => String(l.id)) : [];
                $('#asesorLspChecks .lsp-check').each(function() {
                    this.checked = ids.indexOf(this.value) !== -1;
                });
                showModal('modalAsesor');
            }

            function toggleRiwayat() {
                const $f = $('#formRiwayat');
                const pay = $f.find('[name="jenis"]').val() === 'pembayaran';

                $('#rwHargaWrap').toggleClass('d-none', !pay);
                $f.find('[name="harga"]').prop('required', pay);

                if (state.rwLock) {
                    $('#rwAsesorWrap, #rwManualWrap').addClass('d-none');
                    $f.find('[name="nama_manual"]').prop('required', false);
                    return;
                }

                const manual = !$f.find('[name="asesor_id"]').val();
                $('#rwAsesorWrap').removeClass('d-none');
                $('#rwManualWrap').toggleClass('d-none', !manual);
                $f.find('[name="nama_manual"]').prop('required', manual);
            }

            function openRiwayatForm(item, lockAsesorId) {
                state.rwLock = lockAsesorId || null;
                const $f = prepForm('#formRiwayat', item, 'Tambah Riwayat', 'Edit Riwayat');
                fillForm($f, item, ['jenis', 'asesor_id', 'nama_manual', 'tanggal', 'harga', 'keterangan']);
                if (state.rwLock) $f.find('[name="asesor_id"]').val(state.rwLock);
                toggleRiwayat();
                showModal('modalRiwayat');
            }

            $('#formRiwayat [name="jenis"], #formRiwayat [name="asesor_id"]').on('change', toggleRiwayat);

            bindForm('#formLsp', {
                base: API + '/lsp',
                modal: 'modalLsp',
                done: () => {
                    loadOptions();
                    invalidate();
                }
            });
            bindForm('#formProgram', {
                base: API + '/program',
                modal: 'modalProgram',
                done: () => invalidate()
            });
            bindForm('#formAsesor', {
                base: API + '/asesor',
                modal: 'modalAsesor',
                done: () => {
                    loadOptions();
                    invalidate();
                }
            });
            bindForm('#formRiwayat', {
                base: API + '/riwayat',
                modal: 'modalRiwayat',
                done: () => invalidate(['riwayat'])
            });

            // Setelah modal form ditutup, kembali ke modal detail (bila form dibuka dari detail)
            $('#modalLsp, #modalProgram, #modalAsesor, #modalRiwayat').on('hidden.bs.modal', function() {
                if (state.returnTo) {
                    const fn = state.returnTo;
                    state.returnTo = null;
                    fn();
                }
            });

            /* ======================= DELETE & ROW ACTIONS ======================= */
            function del(url, msg, done) {
                if (!confirm(msg)) return;
                $.ajax({
                        url: url,
                        method: 'POST',
                        data: {
                            _method: 'DELETE'
                        }
                    })
                    .done(res => {
                        toast(res.message || 'Data dihapus');
                        if (done) done(res);
                    })
                    .fail(xhr => toast(errMsg(xhr), 'error'));
            }

            const reloadAll = () => {
                loadOptions();
                invalidate();
            };

            const ACT = {
                'lsp:view': id => openLspDetail(id),
                'lsp:edit': (id, it) => openLspForm(it),
                'lsp:delete': (id, it) => del(API + '/lsp/' + id,
                    `Hapus ${it.nama}? Seluruh skema dan relasi asesornya ikut terhapus.`, reloadAll),

                'sertifikasi:edit': (id, it) => openProgramForm('sertifikasi', it),
                'sertifikasi:delete': (id, it) => del(API + '/program/' + id,
                    `Hapus skema ${it.nama_skema}?`, () => invalidate()),

                'pelatihan:edit': (id, it) => openProgramForm('pelatihan', it),
                'pelatihan:delete': (id, it) => del(API + '/program/' + id,
                    `Hapus skema ${it.nama_skema}?`, () => invalidate()),

                'asesor:view': id => openAsesorDetail(id),
                'asesor:edit': (id, it) => openAsesorForm(it),
                'asesor:delete': (id, it) => del(API + '/asesor/' + id,
                    `Hapus ${it.nama_lengkap}? Seluruh riwayatnya (termasuk pembayaran) ikut terhapus.`, reloadAll),

                'riwayat:edit': (id, it) => openRiwayatForm(it),
                'riwayat:delete': id => del(API + '/riwayat/' + id,
                    'Hapus riwayat ini?', () => invalidate(['riwayat']))
            };

            $(document).on('click', '[data-act]', function(e) {
                e.preventDefault();
                const tab = $(this).data('tab');
                const id = $(this).data('id');
                const act = $(this).data('act');
                const fn = ACT[tab + ':' + act];
                if (fn) fn(id, (state.items[tab] || {})[id]);
            });

            /* ======================= DETAIL LSP / LPK ======================= */
            const detailSkeleton = () => `
                <span class="skel-line" style="height:28px;width:50%;margin-bottom:16px"></span>
                <span class="skel-line" style="height:90px;margin-bottom:16px"></span>
                <span class="skel-line" style="height:220px"></span>`;

            const detailError = xhr => `
                <div class="empty-state">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    <p>${esc(errMsg(xhr))}</p>
                </div>`;

            function openLspDetail(id) {
                $('#lspDetailBody').html(detailSkeleton());
                showModal('modalLspDetail');
                return $.get(API + '/lsp/' + id)
                    .done(res => {
                        state.detail.lsp = res;
                        renderLspDetail(res);
                    })
                    .fail(xhr => $('#lspDetailBody').html(detailError(xhr)));
            }

            function programTable(rows, jenis) {
                const sert = jenis === 'sertifikasi';
                if (!rows.length) {
                    return `<div class="empty-state"><i class="fa-solid ${sert ? 'fa-certificate' : 'fa-chalkboard-user'}"></i><p>Belum ada skema ${jenis}</p></div>`;
                }
                return `
                    <div class="table-responsive">
                        <table class="table table-hover recap-table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Skema</th>${sert ? '<th>Jenis</th><th>KKNI</th>' : ''}<th>Pelaksanaan</th>
                                    <th class="text-end">${sert ? 'Harga / Pax' : 'Harga / Hari'}</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${rows.map(p => `
                                    <tr>
                                        <td><div class="fw-semibold">${esc(p.nama_skema)}</div><div class="sub">${esc(p.keterangan || '')}</div></td>
                                        ${sert ? `<td>${esc(p.jenis_skema || '-')}</td><td>${esc(p.jenjang_kkni || '-')}</td>` : ''}
                                        <td>${p.pelaksanaan ? badge(PEL_COLOR[p.pelaksanaan] || 'tidak_hadir', p.pelaksanaan) : '-'}</td>
                                        <td class="text-end fw-semibold">${rp(sert ? p.harga_sertifikasi_per_pax : p.harga_pelatihan_per_hari)}</td>
                                    </tr>`).join('')}
                            </tbody>
                        </table>
                    </div>`;
            }

            function renderLspDetail(res) {
                const l = res.lsp;
                const s = sisaHari(l.tanggal_berakhir);
                const sisaTxt = s === null ? '-' : (s < 0 ? 'Lewat ' + Math.abs(s) : s) + ' hari';
                const sisaCol = s === null ? 'var(--gray-400)' :
                    (s < 0 ? 'var(--danger)' : (s <= 90 ? 'var(--warning)' : 'var(--success)'));

                const cards = [
                    ['Sertifikasi', res.sertifikasis.length, 'Skema sertifikasi', 'fa-certificate', G.info, 'var(--info)'],
                    ['Pelatihan', res.pelatihans.length, 'Skema pelatihan', 'fa-chalkboard-user', G.teal, 'var(--c-izin)'],
                    ['Asesor', res.asesors.length, 'Asesor terhubung', 'fa-user-tie', G.vio, 'var(--c-sakit)'],
                    ['Sisa Masa Kerja Sama', sisaTxt, 'Berakhir ' + fd(l.tanggal_berakhir), 'fa-hourglass-half', G.warn, sisaCol]
                ];

                const info = [
                    ['Nomor PKS / MoU', l.nomor_pks_mou],
                    ['Tanggal Mulai', l.tanggal_mulai ? fd(l.tanggal_mulai) : null],
                    ['Tanggal Berakhir', l.tanggal_berakhir ? fd(l.tanggal_berakhir) : null],
                    ['PIC', l.pic],
                    ['No. HP', l.no_hp],
                    ['Email', l.email],
                    ['Website', l.website],
                    ['Keterangan', l.keterangan]
                ];

                const asesorList = res.asesors.length
                    ? res.asesors.map(a => `
                        <div class="d-flex align-items-center p-2 mb-2 rounded-3 border" style="background:#fff">
                            <div class="employee-avatar"><i class="fa-solid fa-user"></i></div>
                            <div class="flex-grow-1" style="min-width:0">
                                <div class="fw-semibold" style="font-size:.875rem">${esc(a.nama_lengkap)}</div>
                                <div class="sub">${esc(a.no_telp || '-')} · ${esc(a.email || '-')}</div>
                            </div>
                            <button type="button" class="btn btn-outline-sec btn-sm btn-detach" data-lsp="${l.id}" data-asesor="${a.id}" data-nama="${esc(a.nama_lengkap)}">
                                <i class="fa-solid fa-link-slash me-1"></i>Lepas
                            </button>
                        </div>`).join('')
                    : '<div class="empty-state"><i class="fa-solid fa-user-tie"></i><p>Belum ada asesor terhubung</p><small>Pilih asesor di atas untuk menghubungkan.</small></div>';

                const availableOpts = res.available_asesors
                    .map(a => `<option value="${a.id}">${esc(a.nama_lengkap)}</option>`).join('');

                $('#lspDetailBody').html(`
                    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
                        <div>
                            <h4 class="fw-bold mb-1" style="color:var(--gray-900)">${esc(l.nama)}
                                ${badge(JENIS_COLOR[l.jenis] || 'tidak_hadir', l.jenis)}
                                <span class="risk-badge ${STATUS_CLS[l.status] || 'risk-none'}">${esc(l.status)}</span>
                            </h4>
                            <div class="page-sub">${esc(l.bidang || '-')} · ${esc(l.based || '-')}</div>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-pri btn-sm" id="btnLspEdit"><i class="fa-solid fa-pen me-1"></i>Edit</button>
                            <button type="button" class="btn btn-danger-custom btn-sm" id="btnLspDelete"><i class="fa-solid fa-trash me-1"></i>Hapus</button>
                        </div>
                    </div>

                    <div class="row g-3 mb-4">${renderCards(cards)}</div>

                    <div class="card card-shell mb-4">
                        <div class="card-body">
                            <div class="chart-title"><i class="fa-solid fa-circle-info" style="color:var(--info)"></i>Profil & Kerja Sama</div>
                            <div class="info-grid">
                                ${info.map(([k, v]) => `<div class="info-item"><div class="lbl">${k}</div><div class="val">${esc(dash(v))}</div></div>`).join('')}
                            </div>
                        </div>
                    </div>

                    <ul class="nav nav-tabs nav-tabs-custom mb-3">
                        <li class="nav-item"><button type="button" class="nav-link active" data-bs-toggle="tab" data-bs-target="#ld-sert"><i class="fa-solid fa-certificate"></i>Sertifikasi (${res.sertifikasis.length})</button></li>
                        <li class="nav-item"><button type="button" class="nav-link" data-bs-toggle="tab" data-bs-target="#ld-pel"><i class="fa-solid fa-chalkboard-user"></i>Pelatihan (${res.pelatihans.length})</button></li>
                        <li class="nav-item"><button type="button" class="nav-link" data-bs-toggle="tab" data-bs-target="#ld-ases"><i class="fa-solid fa-user-tie"></i>Asesor (${res.asesors.length})</button></li>
                    </ul>

                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="ld-sert">
                            <div class="card card-shell"><div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div class="chart-title mb-0"><i class="fa-solid fa-certificate"></i>Skema Sertifikasi</div>
                                    <button type="button" class="btn btn-outline-sec btn-sm btn-goto-program" data-jenis="sertifikasi" data-lsp="${l.id}"><i class="fa-solid fa-gear me-1"></i>Kelola</button>
                                </div>
                                ${programTable(res.sertifikasis, 'sertifikasi')}
                            </div></div>
                        </div>

                        <div class="tab-pane fade" id="ld-pel">
                            <div class="card card-shell"><div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div class="chart-title mb-0"><i class="fa-solid fa-chalkboard-user"></i>Skema Pelatihan</div>
                                    <button type="button" class="btn btn-outline-sec btn-sm btn-goto-program" data-jenis="pelatihan" data-lsp="${l.id}"><i class="fa-solid fa-gear me-1"></i>Kelola</button>
                                </div>
                                ${programTable(res.pelatihans, 'pelatihan')}
                            </div></div>
                        </div>

                        <div class="tab-pane fade" id="ld-ases">
                            <div class="card card-shell"><div class="card-body">
                                <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-3">
                                    <div>
                                        <div class="chart-title mb-0"><i class="fa-solid fa-user-tie"></i>Asesor yang Mengawasi</div>
                                        <div class="filter-hint">Hubungan asesor dengan LSP/LPK bersifat opsional.</div>
                                    </div>
                                    <form id="formAttach" class="d-flex gap-2 align-items-end" data-lsp="${l.id}">
                                        <div class="filter-field">
                                            <label class="form-label">Hubungkan asesor</label>
                                            <select name="asesor_id" class="form-select form-select-sm" style="width:230px" required>
                                                <option value="">Pilih asesor…</option>${availableOpts}
                                            </select>
                                        </div>
                                        <button type="submit" class="btn btn-pri btn-sm"><i class="fa-solid fa-link me-1"></i>Hubungkan</button>
                                    </form>
                                </div>
                                ${asesorList}
                            </div></div>
                        </div>
                    </div>`);
            }

            $(document).on('click', '#btnLspEdit', function() {
                const l = state.detail.lsp.lsp;
                swapModal('modalLspDetail', () => {
                    state.returnTo = () => openLspDetail(l.id);
                    openLspForm(l);
                });
            });

            $(document).on('click', '#btnLspDelete', function() {
                const l = state.detail.lsp.lsp;
                del(API + '/lsp/' + l.id,
                    `Hapus ${l.nama}? Seluruh skema dan relasi asesornya ikut terhapus.`,
                    () => {
                        hideModal('modalLspDetail');
                        reloadAll();
                    });
            });

            $(document).on('click', '.btn-goto-program', function() {
                const jenis = $(this).data('jenis');
                const lspId = $(this).data('lsp');
                swapModal('modalLspDetail', () => {
                    $('#f-' + jenis + '-q').val('');
                    $('#f-' + jenis + '-lsp').val(String(lspId));
                    state.loaded[jenis] = false;

                    if (state.tab === jenis) {
                        loadTab(jenis, 1);
                        return;
                    }
                    bootstrap.Tab.getOrCreateInstance($('#mainTabs [data-tab="' + jenis + '"]')[0]).show();
                });
            });

            $(document).on('submit', '#formAttach', function(e) {
                e.preventDefault();
                const lspId = $(this).data('lsp');
                $.post(API + '/lsp/' + lspId + '/asesor', $(this).serialize())
                    .done(res => {
                        toast(res.message);
                        openLspDetail(lspId);
                        invalidate(['lsp', 'asesor']);
                    })
                    .fail(xhr => toast(errList(xhr).join(' '), 'error'));
            });

            $(document).on('click', '.btn-detach', function() {
                const lspId = $(this).data('lsp');
                const asesorId = $(this).data('asesor');
                del(API + '/lsp/' + lspId + '/asesor/' + asesorId,
                    `Lepas ${$(this).data('nama')} dari LSP/LPK ini? Data asesor tidak dihapus.`,
                    () => {
                        openLspDetail(lspId);
                        invalidate(['lsp', 'asesor']);
                    });
            });

            /* ======================= DETAIL ASESOR ======================= */
            function openAsesorDetail(id) {
                $('#asesorDetailBody').html(detailSkeleton());
                showModal('modalAsesorDetail');
                return $.get(API + '/asesor/' + id)
                    .done(res => {
                        state.detail.asesor = res;
                        renderAsesorDetail(res);
                    })
                    .fail(xhr => $('#asesorDetailBody').html(detailError(xhr)));
            }

            function renderAsesorDetail(res) {
                const a = res.asesor;
                const st = res.stats;

                state.detail.riwayat = {};
                res.riwayat.forEach(r => {
                    state.detail.riwayat[r.id] = r;
                });

                const cards = [
                    ['Uji Kompetensi', st.uji, 'Kali menguji', 'fa-clipboard-check', G.vio, 'var(--c-sakit)'],
                    ['Mengajar', st.mengajar, 'Kali mengajar', 'fa-chalkboard-user', G.teal, 'var(--c-izin)'],
                    ['Total Pembayaran', rp(st.total_pembayaran), st.pembayaran_count + ' kali pembayaran', 'fa-money-bill-wave', G.ok, 'var(--success)'],
                    ['LSP / LPK', (a.lsps || []).length, 'Mitra yang diawasi', 'fa-building-columns', G.pri, 'var(--pri)']
                ];

                const info = [
                    ['No. Telp', a.no_telp],
                    ['Email', a.email],
                    ['NPWP', a.npwp],
                    ['No. KTP', a.ktp],
                    ['Alamat', a.alamat]
                ];

                const rows = res.riwayat.length
                    ? res.riwayat.map(r => `
                        <tr>
                            <td style="white-space:nowrap">${fd(r.tanggal)}</td>
                            <td>${badge(RW[r.jenis][1], RW[r.jenis][0])}</td>
                            <td class="text-end fw-semibold">${r.jenis === 'pembayaran' ? rp(r.harga) : '-'}</td>
                            <td style="color:var(--gray-600)">${esc(r.keterangan || '-')}</td>
                            <td class="text-end" style="white-space:nowrap">
                                <button type="button" class="btn btn-outline-sec btn-icon edit ad-edit" title="Edit" data-id="${r.id}"><i class="fa-solid fa-pen"></i></button>
                                <button type="button" class="btn btn-outline-sec btn-icon danger ad-del" title="Hapus" data-id="${r.id}"><i class="fa-solid fa-trash"></i></button>
                            </td>
                        </tr>`).join('')
                    : '<tr><td colspan="5"><div class="empty-state"><i class="fa-solid fa-clock-rotate-left"></i><p>Belum ada riwayat</p><small>Klik "Tambah Riwayat" untuk mencatat kegiatan atau pembayaran.</small></div></td></tr>';

                $('#asesorDetailBody').html(`
                    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
                        <div>
                            <h4 class="fw-bold mb-1" style="color:var(--gray-900)">${esc(a.nama_lengkap)}</h4>
                            <div class="page-sub">Profil dan riwayat uji kompetensi, mengajar, serta pembayaran</div>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-pri btn-sm" id="btnAsesorAddRiwayat"><i class="fa-solid fa-plus me-1"></i>Tambah Riwayat</button>
                            <button type="button" class="btn btn-outline-sec btn-sm" id="btnAsesorEdit"><i class="fa-solid fa-pen me-1"></i>Edit</button>
                            <button type="button" class="btn btn-danger-custom btn-sm" id="btnAsesorDelete"><i class="fa-solid fa-trash me-1"></i>Hapus</button>
                        </div>
                    </div>

                    <div class="row g-3 mb-4">${renderCards(cards)}</div>

                    <div class="row g-4">
                        <div class="col-lg-4">
                            <div class="card card-shell h-100"><div class="card-body">
                                <div class="chart-title"><i class="fa-solid fa-id-card" style="color:var(--info)"></i>Profil</div>
                                <div class="d-flex flex-column gap-3">
                                    ${info.map(([k, v]) => `<div class="info-item"><div class="lbl">${k}</div><div class="val">${esc(dash(v))}</div></div>`).join('')}
                                    <div class="info-item">
                                        <div class="lbl">Mengawasi</div>
                                        <div class="val">${(a.lsps || []).length
                                            ? lspBadges(a.lsps)
                                            : '<span style="color:var(--gray-400);font-weight:500">Belum terhubung ke LSP / LPK</span>'}</div>
                                    </div>
                                </div>
                            </div></div>
                        </div>
                        <div class="col-lg-8">
                            <div class="card card-shell h-100"><div class="card-body">
                                <div class="chart-title"><i class="fa-solid fa-clock-rotate-left"></i>Riwayat</div>
                                <div class="table-responsive">
                                    <table class="table table-hover recap-table align-middle mb-0">
                                        <thead>
                                            <tr><th>Tanggal</th><th>Jenis</th><th class="text-end">Nominal</th><th>Keterangan</th><th class="text-end">Aksi</th></tr>
                                        </thead>
                                        <tbody>${rows}</tbody>
                                    </table>
                                </div>
                            </div></div>
                        </div>
                    </div>`);
            }

            $(document).on('click', '#btnAsesorAddRiwayat', function() {
                const a = state.detail.asesor.asesor;
                swapModal('modalAsesorDetail', () => {
                    state.returnTo = () => openAsesorDetail(a.id);
                    openRiwayatForm(null, a.id);
                });
            });

            $(document).on('click', '#btnAsesorEdit', function() {
                const a = state.detail.asesor.asesor;
                swapModal('modalAsesorDetail', () => {
                    state.returnTo = () => openAsesorDetail(a.id);
                    openAsesorForm(a);
                });
            });

            $(document).on('click', '#btnAsesorDelete', function() {
                const a = state.detail.asesor.asesor;
                del(API + '/asesor/' + a.id,
                    `Hapus ${a.nama_lengkap}? Seluruh riwayatnya (termasuk pembayaran) ikut terhapus.`,
                    () => {
                        hideModal('modalAsesorDetail');
                        reloadAll();
                    });
            });

            $(document).on('click', '.ad-edit', function() {
                const a = state.detail.asesor.asesor;
                const r = state.detail.riwayat[$(this).data('id')];
                swapModal('modalAsesorDetail', () => {
                    state.returnTo = () => openAsesorDetail(a.id);
                    openRiwayatForm(r, a.id);
                });
            });

            $(document).on('click', '.ad-del', function() {
                const a = state.detail.asesor.asesor;
                del(API + '/riwayat/' + $(this).data('id'), 'Hapus riwayat ini?', () => {
                    openAsesorDetail(a.id);
                    invalidate(['riwayat']);
                });
            });

            /* ======================= PAGE EVENTS ======================= */
            function updateHeader() {
                $('#btnAddLabel').text(TABS[state.tab].addLabel);
            }

            $('#mainTabs button[data-bs-toggle="tab"]').on('shown.bs.tab', function(e) {
                state.tab = $(e.target).data('tab');
                updateHeader();
                if (!state.loaded[state.tab]) loadTab(state.tab);
                else renderStats(state.tab);
            });

            $('.filter-form').on('submit', function(e) {
                e.preventDefault();
                loadTab($(this).data('tab'), 1);
            });

            $('.btn-reset').on('click', function() {
                const $f = $(this).closest('form');
                $f.find('input, select').val('');
                loadTab($f.data('tab'), 1);
            });

            $(document).on('click', '.pager .page-link', function(e) {
                e.preventDefault();
                const $li = $(this).parent();
                if ($li.hasClass('disabled') || $li.hasClass('active')) return;
                loadTab($(this).closest('.pager').data('tab'), parseInt($(this).data('page'), 10));
            });

            $('#btnAdd').on('click', () => TABS[state.tab].add());

            $('#btnRefresh').on('click', () => {
                loadOptions();
                state.loaded = {};
                loadTab(state.tab, state.page[state.tab] || 1);
            });

            /* ======================= WILAYAH (Provinsi -> Kota/Kabupaten) ======================= */
            function initWilayah() {
                const WILAYAH_API = 'https://www.emsifa.com/api-wilayah-indonesia/api';
                const provinsiSelect = mEl('provinsi');
                const kotaSelect = mEl('based');

                // 1. Daftar provinsi
                fetch(WILAYAH_API + '/provinces.json')
                    .then(res => res.json())
                    .then(provinces => {
                        provinces.forEach(prov => {
                            const option = document.createElement('option');
                            option.value = prov.id;
                            option.textContent = prov.name;
                            option.dataset.nama = prov.name;
                            provinsiSelect.appendChild(option);
                        });
                    })
                    .catch(err => console.error('Gagal load provinsi:', err));

                // 2. Saat provinsi diganti -> muat kota/kabupaten
                provinsiSelect.addEventListener('change', function() {
                    const provId = this.value;

                    kotaSelect.innerHTML = '<option value="">-- Pilih Kota / Kabupaten --</option>';
                    kotaSelect.disabled = true;
                    if (!provId) return;

                    fetch(`${WILAYAH_API}/regencies/${provId}.json`)
                        .then(res => res.json())
                        .then(regencies => {
                            regencies.forEach(kota => {
                                const option = document.createElement('option');
                                option.value = kota.name; // nilai yang dikirim = nama kota
                                option.textContent = kota.name;
                                option.dataset.id = kota.id;
                                kotaSelect.appendChild(option);
                            });
                            kotaSelect.disabled = false;
                        })
                        .catch(err => {
                            console.error('Gagal load kota:', err);
                            kotaSelect.innerHTML = '<option value="">Gagal memuat data</option>';
                        });
                });
            }

            /* ======================= INIT ======================= */
            $(function() {
                updateHeader();
                $('#statRow').html(skeletonStats());
                initWilayah();
                loadOptions();
                loadTab('lsp');
            });
        })();
    </script>
@endpush