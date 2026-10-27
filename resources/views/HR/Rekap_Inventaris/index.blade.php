@extends('layout_HR.app')
@section('content_HR')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"
        integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        .page-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: .25rem;
        }

        .page-subtitle {
            font-size: .9rem;
            color: var(--secondary);
        }

        .nav-tabs .nav-link {
            color: #64748b;
            font-weight: 500;
            border: none;
            border-bottom: 3px solid transparent;
            padding: .75rem 1.25rem;
        }

        .nav-tabs .nav-link.active {
            color: #0d6efd;
            background: transparent;
            border-bottom: 3px solid #0d6efd;
        }

        .table tfoot tr {
            background-color: #f8f9fa;
            font-weight: 700;
            color: #334155;
        }

        .filter-card {
            border: 1px solid #e2e8f0;
            border-radius: .75rem;
            background: rgba(255, 255, 255, .85);
            backdrop-filter: blur(10px);
        }

        .blob-bg {
            position: absolute;
            inset: 0;
            z-index: 0;
            overflow: hidden;
            pointer-events: none;
        }

        .blob-1 {
            position: absolute;
            top: -50px;
            right: -50px;
            width: 450px;
            height: 450px;
            background: linear-gradient(135deg, rgba(56, 189, 248, .15), rgba(99, 102, 241, .15));
            border-radius: 40% 60% 70% 30% / 40% 50% 60% 50%;
            animation: blobAnim 12s ease-in-out infinite alternate;
        }

        @keyframes blobAnim {
            0% {
                transform: scale(1) rotate(0);
            }

            100% {
                transform: scale(1.1) translate(-30px, 30px) rotate(15deg);
            }
        }

        .content-wrapper-relative {
            position: relative;
            z-index: 1;
        }

        .stat-card {
            cursor: pointer;
            border: 1px solid #e2e8f0;
            transition: border-color .15s, box-shadow .15s;
            height: 100%;
        }

        .stat-card:hover {
            border-color: #4f46e5;
            box-shadow: 0 4px 14px rgba(79, 70, 229, .12);
        }

        .stat-card .lbl {
            font-size: .78rem;
            color: #64748b;
        }

        .stat-card .val {
            font-size: 1.25rem;
            font-weight: 700;
            color: #1e293b;
            line-height: 1.3;
            word-break: break-word;
        }

        .stat-card .sub {
            font-size: .75rem;
            color: #64748b;
        }

        .stat-card .hint {
            font-size: .7rem;
            color: #4f46e5;
            opacity: 0;
            transition: opacity .15s;
        }

        .stat-card:hover .hint {
            opacity: 1;
        }

        .clickable {
            cursor: pointer;
        }

        .clickable:hover {
            background: #f1f5f9;
        }

        .up {
            color: #dc2626;
        }

        .down {
            color: #059669;
        }

        .header-actions {
            position: relative;
            z-index: 20;
        }

        #expWrap {
            position: relative;
        }

        .exp-menu {
            right: 0;
            left: auto;
            top: 100%;
            margin-top: .25rem;
            min-width: 12rem;
            z-index: 1060;
        }

        .exp-menu.show {
            display: block;
        }

        #modalPanduan {
            --pri: #4f46e5;
            --pri-light: #eef2ff;
            --pri-dark: #3730a3;
            --danger-light: #fee2e2;
            --gray-50: #f9fafb;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-400: #9ca3af;
            --gray-700: #374151;
            --gray-900: #111827;
        }

        #modalPanduan .modal-content {
            border: none;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, .1), 0 4px 10px rgba(0, 0, 0, .07);
        }

        #modalPanduan .modal-header-custom {
            background: linear-gradient(135deg, var(--pri) 0%, var(--pri-dark) 100%);
            color: #fff;
            border-radius: 12px 12px 0 0;
            padding: 1.1rem 1.5rem;
        }

        #modalPanduan .modal-header-custom .modal-title {
            font-weight: 700;
            font-size: 1rem;
        }

        #modalPanduan .modal-header-custom .btn-close {
            filter: brightness(0) invert(1);
        }

        #modalPanduan .nav-tabs-custom {
            border-bottom: 2px solid var(--gray-200);
            flex-wrap: nowrap;
            overflow-x: auto;
        }

        #modalPanduan .nav-tabs-custom .nav-link {
            border: none;
            color: var(--gray-400);
            font-weight: 600;
            padding: .85rem 1.25rem;
            font-size: .875rem;
            transition: color .2s;
            display: flex;
            align-items: center;
            gap: .5rem;
            background: transparent;
            white-space: nowrap;
        }

        #modalPanduan .nav-tabs-custom .nav-link:hover {
            color: var(--pri);
        }

        #modalPanduan .nav-tabs-custom .nav-link.active {
            color: var(--pri);
            border-bottom: 3px solid var(--pri);
            background: transparent;
        }

        #modalPanduan .guide-section {
            margin-bottom: 2rem;
        }

        #modalPanduan .guide-section h5 {
            font-size: 1rem;
            font-weight: 700;
            color: var(--gray-900);
            border-left: 4px solid var(--pri);
            padding-left: .75rem;
            margin-bottom: .75rem;
        }

        #modalPanduan .guide-section ol,
        #modalPanduan .guide-section ul {
            padding-left: 1.25rem;
            margin-bottom: .75rem;
        }

        #modalPanduan .guide-section li {
            margin-bottom: .4rem;
            font-size: .9rem;
            color: var(--gray-700);
            line-height: 1.55;
        }

        #modalPanduan .guide-section p {
            font-size: .9rem;
            color: var(--gray-700);
            line-height: 1.55;
        }

        #modalPanduan .guide-note {
            background: var(--pri-light);
            border-radius: 8px;
            padding: .75rem 1rem;
            font-size: .85rem;
            color: var(--gray-700);
        }

        #modalPanduan .guide-example {
            background: var(--gray-50);
            border: 1px dashed var(--gray-400);
            border-radius: 8px;
            padding: 1rem;
            font-size: .85rem;
        }

        #modalPanduan .guide-formula {
            background: #fff;
            border: 1px solid var(--gray-200);
            border-left: 4px solid var(--pri);
            border-radius: 8px;
            padding: .6rem .9rem;
            font-size: .85rem;
            margin-bottom: .5rem;
            color: var(--gray-700);
        }

        #modalPanduan .guide-itsm {
            background: var(--danger-light);
            border: 1px solid #fecaca;
            border-radius: 10px;
            padding: 1.25rem;
        }

        #modalPanduan .guide-qa {
            margin-bottom: 1rem;
        }

        #modalPanduan .guide-qa strong {
            display: block;
            font-size: .9rem;
            color: var(--gray-900);
            margin-bottom: .2rem;
        }

        #modalPanduan .guide-qa span {
            font-size: .88rem;
            color: var(--gray-700);
            line-height: 1.55;
        }

        #modalPanduan table {
            font-size: .85rem;
        }
    </style>

    <div class="blob-bg">
        <div class="blob-1"></div>
    </div>

    <div class="container-fluid px-4 py-4 me-3 ms-3 content-wrapper-relative">

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show shadow-sm">
                <i class="fa-solid fa-check-circle me-1"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show shadow-sm">
                <i class="fa-solid fa-circle-exclamation me-1"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show shadow-sm">
                <strong>Data belum bisa disimpan:</strong>
                <ul class="mb-0 mt-1">
                    @foreach ($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-4">
            <div>
                <h1 class="page-title">Rekap Inventaris</h1>
                <p class="page-subtitle mb-0">Pantau pembelian dan aset inventaris, lalu bandingkan per bulan, quarter,
                    dan tahun.</p>
                <p class="page-subtitle fw-semibold text-dark mb-0">{{ now()->translatedFormat('l, d F Y') }}</p>
            </div>
            <div class="d-flex flex-nowrap align-items-center gap-2 flex-shrink-0 header-actions">
                <button class="btn btn-light border" data-bs-toggle="modal" data-bs-target="#modalPanduan"
                    title="Panduan fitur">
                    <i class="fa-regular fa-circle-question"></i><span class="d-none d-xl-inline ms-1">Panduan</span>
                </button>
                <button class="btn btn-primary text-nowrap" id="btnAdd"><i class="fa-solid fa-plus me-1"></i> Tambah
                    manual</button>
                <div class="position-relative" id="expWrap">
                    <button type="button" class="btn btn-outline-secondary text-nowrap" id="expToggle"
                        aria-expanded="false">
                        <i class="fa-solid fa-file-export me-1"></i> Export / Import <i
                            class="fa-solid fa-chevron-down ms-1 small"></i>
                    </button>
                    <ul class="dropdown-menu shadow exp-menu" id="expMenu">
                        <li><a class="dropdown-item" href="#" id="btn_export_pdf"><i
                                    class="fa-solid fa-file-pdf fa-fw me-2 text-secondary"></i>Export PDF</a></li>
                        <li><a class="dropdown-item" href="#" id="btn_export"><i
                                    class="fa-solid fa-file-excel fa-fw me-2 text-success"></i>Export Excel</a></li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li><button type="button" class="dropdown-item" data-bs-toggle="modal"
                                data-bs-target="#modalImport"><i
                                    class="fa-solid fa-file-import fa-fw me-2 text-primary"></i>Import dari Excel</button>
                        </li>
                        <li><a class="dropdown-item" href="{{ route('HR.rekap_inventaris.sync') }}"><i
                                    class="fa-solid fa-rotate fa-fw me-2 text-info"></i>Sinkronisasi</a></li>
                    </ul>
                </div>
                <a href="{{ route('IndexInventaris') }}" class="btn btn-secondary text-nowrap">
                    <i class="fa-solid fa-list me-1"></i><span class="d-none d-xl-inline"> Daftar Inventaris</span>
                </a>
            </div>
        </div>

        <div class="card filter-card mb-4">
            <div class="card-body p-3">
                <form id="form-filter-rekap" class="row g-3 align-items-end" autocomplete="off">
                    <div class="col-6 col-md-2">
                        <label class="form-label fw-semibold">Tahun</label>
                        <select class="form-select" id="filter_tahun" name="tahun">
                            @foreach ($tahunList as $y)
                                <option value="{{ $y }}" {{ $y == $defaultTahun ? 'selected' : '' }}>
                                    {{ $y }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-md-3">
                        <label class="form-label fw-semibold">Kategori</label>
                        <select class="form-select" id="filter_kategori" name="kategori">
                            <option value="">Semua kategori</option>
                            @foreach ($kategoris as $kategori)
                                <option value="{{ $kategori }}">{{ $kategori }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-md-3">
                        <label class="form-label fw-semibold">Lokasi / ruangan</label>
                        <select class="form-select" id="filter_lokasi" name="lokasi" disabled>
                            <option value="">Pilih kategori dulu</option>
                        </select>
                    </div>
                    <div class="col-6 col-md-4">
                        <label class="form-label fw-semibold">Jenis barang</label>
                        <select class="form-select" id="filter_jenis" name="jenis" disabled>
                            <option value="">Pilih lokasi dulu</option>
                        </select>
                    </div>

                    <div class="col-12 col-md-5">
                        <label class="form-label fw-semibold d-block">Periode (tab rekap data)</label>
                        <div class="d-flex flex-wrap align-items-center gap-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="mode_periode" id="mode_semua"
                                    value="semua" checked>
                                <label class="form-check-label" for="mode_semua">Seluruh tahun</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="mode_periode" id="mode_bulan"
                                    value="bulan">
                                <label class="form-check-label" for="mode_bulan">Bulanan</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="mode_periode" id="mode_quartal"
                                    value="quartal">
                                <label class="form-check-label" for="mode_quartal">Per 3 bulan</label>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3 d-none" id="wrapper_bulan">
                        <label class="form-label fw-semibold">Pilih bulan</label>
                        <select class="form-select" id="filter_bulan" name="bulan">
                            @foreach (['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'] as $i => $b)
                                <option value="{{ $i + 1 }}">{{ $b }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-md-3 d-none" id="wrapper_quartal">
                        <label class="form-label fw-semibold">Pilih quartal</label>
                        <select class="form-select" id="filter_quartal" name="quartal">
                            <option value="1">Quartal 1 (Jan - Mar)</option>
                            <option value="2">Quartal 2 (Apr - Jun)</option>
                            <option value="3">Quartal 3 (Jul - Sep)</option>
                            <option value="4">Quartal 4 (Okt - Des)</option>
                        </select>
                    </div>
                    <div class="col-12 col-md d-flex gap-2 ms-md-auto justify-content-md-end">
                        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-filter me-1"></i>
                            Terapkan</button>
                        <button type="button" class="btn btn-light border" id="btn_reset" title="Reset"><i
                                class="fa-solid fa-arrows-rotate"></i></button>
                    </div>
                </form>
                <div class="small text-muted mt-2">Card, perbandingan bulan/quarter/tahun, dan tren mengikuti
                    <strong>Tahun, Kategori, Lokasi, dan Jenis</strong>. Filter <strong>Periode</strong> hanya memengaruhi
                    tab Rekap data serta perbandingan Kategori dan Lokasi.</div>
            </div>
        </div>

        @php
            $cards = [
                ['id' => 'total', 'type' => 'total', 'label' => 'Total pembelian'],
                ['id' => 'qty', 'type' => 'qty', 'label' => 'Jumlah barang'],
                ['id' => 'count', 'type' => 'count', 'label' => 'Jumlah transaksi'],
                ['id' => 'avg', 'type' => 'average', 'label' => 'Rata-rata per transaksi'],
                ['id' => 'max', 'type' => 'max', 'label' => 'Transaksi terbesar'],
                ['id' => 'kat', 'type' => 'top_kategori', 'label' => 'Kategori terbesar'],
                ['id' => 'growth', 'type' => 'growth', 'label' => 'Vs tahun lalu'],
            ];
        @endphp
        <div class="row g-3 mb-4">
            @foreach ($cards as $c)
                <div class="col-6 col-md-4 col-xl">
                    <div class="card stat-card" data-detail="{{ $c['type'] }}" data-label="{{ $c['label'] }}">
                        <div class="card-body py-3">
                            <div class="lbl">{{ $c['label'] }}</div>
                            <div class="val" id="c_{{ $c['id'] }}">...</div>
                            <div class="sub" id="s_{{ $c['id'] }}">&nbsp;</div>
                            <div class="hint mt-1">Lihat detail <i class="fa-solid fa-chevron-right"></i></div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <ul class="nav nav-tabs px-4 pt-3">
                    <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-data"
                            type="button"><i class="fa-solid fa-table-list me-1"></i> Rekap data</button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-compare"
                            type="button"><i class="fa-solid fa-scale-balanced me-1"></i> Comparison pembelian</button>
                    </li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-trend"
                            type="button"><i class="fa-solid fa-chart-line me-1"></i> Tren pembelian</button></li>
                </ul>

                <div class="tab-content p-4">

                    <div class="tab-pane fade show active" id="tab-data">
                        <ul class="nav nav-pills mb-3">
                            <li class="nav-item"><button class="nav-link active" data-bs-toggle="pill"
                                    data-bs-target="#pane-kat" type="button"><i class="fa-solid fa-boxes-stacked me-1"></i>
                                    Per kategori <span class="badge bg-light text-dark ms-1" id="cnt_kat">0</span></button>
                            </li>
                            <li class="nav-item"><button class="nav-link" data-bs-toggle="pill"
                                    data-bs-target="#pane-per" type="button"><i
                                        class="fa-solid fa-calendar-week me-1"></i> Per periode <span
                                        class="badge bg-light text-dark ms-1" id="cnt_per">0</span></button></li>
                            <li class="nav-item"><button class="nav-link" data-bs-toggle="pill"
                                    data-bs-target="#pane-lok" type="button"><i
                                        class="fa-solid fa-map-location-dot me-1"></i> Per lokasi <span
                                        class="badge bg-light text-dark ms-1" id="cnt_lok">0</span></button></li>
                        </ul>
                        <div class="tab-content">
                            <div class="tab-pane fade show active" id="pane-kat">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th width="5%">NO</th>
                                                <th>KATEGORI</th>
                                                <th class="text-end">TRANSAKSI</th>
                                                <th class="text-end">JUMLAH BARANG</th>
                                                <th class="text-end">TOTAL PEMBELIAN</th>
                                                <th class="text-end">KONTRIBUSI</th>
                                                <th class="text-center" width="8%">AKSI</th>
                                            </tr>
                                        </thead>
                                        <tbody id="tbody_kat">
                                            <tr>
                                                <td colspan="7" class="text-center py-4 text-muted">Memuat data...</td>
                                            </tr>
                                        </tbody>
                                        <tfoot id="tfoot_kat"></tfoot>
                                    </table>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="pane-per">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th width="5%">NO</th>
                                                <th>PERIODE</th>
                                                <th class="text-end">TRANSAKSI</th>
                                                <th class="text-end">JUMLAH BARANG</th>
                                                <th class="text-end">TOTAL PEMBELIAN</th>
                                                <th class="text-end">KONTRIBUSI</th>
                                                <th class="text-center" width="8%">AKSI</th>
                                            </tr>
                                        </thead>
                                        <tbody id="tbody_per">
                                            <tr>
                                                <td colspan="7" class="text-center py-4 text-muted">Memuat data...</td>
                                            </tr>
                                        </tbody>
                                        <tfoot id="tfoot_per"></tfoot>
                                    </table>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="pane-lok">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th width="5%">NO</th>
                                                <th>LOKASI / RUANGAN</th>
                                                <th class="text-end">TRANSAKSI</th>
                                                <th class="text-end">JUMLAH BARANG</th>
                                                <th class="text-end">TOTAL PEMBELIAN</th>
                                                <th class="text-end">KONTRIBUSI</th>
                                                <th class="text-center" width="8%">AKSI</th>
                                            </tr>
                                        </thead>
                                        <tbody id="tbody_lok">
                                            <tr>
                                                <td colspan="7" class="text-center py-4 text-muted">Memuat data...</td>
                                            </tr>
                                        </tbody>
                                        <tfoot id="tfoot_lok"></tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="tab-compare">
                        <ul class="nav nav-pills mb-3 flex-wrap">
                            <li class="nav-item"><button class="nav-link active" data-bs-toggle="pill"
                                    data-bs-target="#cmp-bulan" type="button">Per bulan</button></li>
                            <li class="nav-item"><button class="nav-link" data-bs-toggle="pill"
                                    data-bs-target="#cmp-quarter" type="button">Per quarter</button></li>
                            <li class="nav-item"><button class="nav-link" data-bs-toggle="pill"
                                    data-bs-target="#cmp-tahun" type="button">Per tahun</button></li>
                            <li class="nav-item"><button class="nav-link" data-bs-toggle="pill"
                                    data-bs-target="#cmp-kategori" type="button">Per kategori</button></li>
                            <li class="nav-item"><button class="nav-link" data-bs-toggle="pill"
                                    data-bs-target="#cmp-lokasi" type="button">Per lokasi</button></li>
                        </ul>
                        <div class="tab-content">
                            <div class="tab-pane fade show active" id="cmp-bulan">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="fw-bold mb-0">Pembelian per bulan: tahun ini vs tahun lalu</h6>
                                    <button class="btn btn-sm btn-outline-primary" data-detail="growth"
                                        data-label="Pertumbuhan pembelian">Detail pertumbuhan</button>
                                </div>
                                <div id="chartBulan"></div>
                                <div class="table-responsive mt-3">
                                    <table class="table table-sm table-hover align-middle">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Bulan</th>
                                                <th class="text-end">Transaksi</th>
                                                <th class="text-end">Barang</th>
                                                <th class="text-end">Total</th>
                                                <th class="text-end">Tahun lalu</th>
                                                <th class="text-end">Perubahan</th>
                                            </tr>
                                        </thead>
                                        <tbody id="tblBulan"></tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="cmp-quarter">
                                <h6 class="fw-bold mb-2">Pembelian per quarter (triwulan)</h6>
                                <div class="row g-3 mb-3" id="quarterCards"></div>
                                <div id="chartQuarter"></div>
                            </div>
                            <div class="tab-pane fade" id="cmp-tahun">
                                <h6 class="fw-bold mb-2">Pembelian per tahun (dipisah per kategori)</h6>
                                <div id="chartTahun"></div>
                                <div class="table-responsive mt-3">
                                    <table class="table table-sm table-hover align-middle">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Tahun</th>
                                                <th class="text-end">Transaksi</th>
                                                <th class="text-end">Barang</th>
                                                <th class="text-end">Total</th>
                                                <th class="text-end">Vs tahun sebelumnya</th>
                                            </tr>
                                        </thead>
                                        <tbody id="tblTahun"></tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="cmp-kategori">
                                <h6 class="fw-bold mb-1">Pembelian per kategori</h6>
                                <div class="small text-muted mb-2"><span class="lblPeriode"></span> dibanding <span
                                        class="lblPeriodeLalu"></span></div>
                                <div id="chartKategori"></div>
                            </div>
                            <div class="tab-pane fade" id="cmp-lokasi">
                                <h6 class="fw-bold mb-1">Pembelian per lokasi</h6>
                                <div class="small text-muted mb-2"><span class="lblPeriode"></span> dibanding <span
                                        class="lblPeriodeLalu"></span></div>
                                <div id="chartLokasi"></div>
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="tab-trend">
                        <div class="row g-4">
                            <div class="col-lg-8">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between">
                                            <h6 class="fw-bold">Tren pembelian bulanan</h6>
                                            <button class="btn btn-sm btn-outline-primary" data-detail="total"
                                                data-label="Total pembelian">Detail</button>
                                        </div>
                                        <div id="chartTren"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between">
                                            <h6 class="fw-bold">Komposisi kategori</h6>
                                            <button class="btn btn-sm btn-outline-primary" data-detail="top_kategori"
                                                data-label="Kategori terbesar">Detail</button>
                                        </div>
                                        <div id="chartKomposisi"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between">
                                            <h6 class="fw-bold">10 jenis barang dengan pembelian terbesar</h6>
                                            <button class="btn btn-sm btn-outline-primary" data-detail="max"
                                                data-label="Transaksi terbesar">Detail</button>
                                        </div>
                                        <div id="chartJenis"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between">
                                            <h6 class="fw-bold">Akumulasi pembelian (YTD)</h6>
                                            <button class="btn btn-sm btn-outline-primary" data-detail="count"
                                                data-label="Jumlah transaksi">Detail</button>
                                        </div>
                                        <div id="chartYTD"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="small text-muted mt-3"><i class="fa-regular fa-hand-pointer me-1"></i> Klik batang,
                            titik, atau potongan grafik untuk melihat detailnya.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalForm" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content border-0 shadow-lg">
                <form id="formInv" method="POST" action="{{ route('HR.rekap_inventaris.store') }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold">Tambah data inventaris</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Nama barang <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="name" id="f_name" maxlength="255"
                                    value="{{ old('name') }}" placeholder="Contoh: Laptop Dell XPS" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Kategori <span
                                        class="text-danger">*</span></label>
                                <select class="form-select" name="kategori" id="f_kategori" required>
                                    @foreach ($kategoriForm as $k)
                                        <option value="{{ $k }}" @selected(old('kategori') === $k)>{{ $k }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Qty (jumlah) <span
                                        class="text-danger">*</span></label>
                                <input type="number" class="form-control" name="qty" id="f_qty" min="1"
                                    value="{{ old('qty', 1) }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Total harga (Rp) <span
                                        class="text-danger">*</span></label>
                                <input type="number" class="form-control" name="total_harga" id="f_total"
                                    min="0" value="{{ old('total_harga') }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Tanggal pembelian <span
                                        class="text-danger">*</span></label>
                                <input type="date" class="form-control" name="waktu_pembelian" id="f_tanggal"
                                    value="{{ old('waktu_pembelian') }}" required>
                            </div>
                            <div class="col-12">
                                <div class="small text-muted" id="f_satuan">Harga satuan dihitung otomatis dari total
                                    harga dibagi qty.</div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">ID barang</label>
                                <input type="text" class="form-control" name="idbarang" id="f_idbarang"
                                    maxlength="255" value="{{ old('idbarang') }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">ID inventaris</label>
                                <input type="text" class="form-control" name="idinventaris" id="f_idinv"
                                    maxlength="255" value="{{ old('idinventaris') }}" placeholder="INV-10024">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">No. KK</label>
                                <input type="text" class="form-control" name="no_kk" id="f_nokk" maxlength="255"
                                    value="{{ old('no_kk') }}" placeholder="KK-001">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Lokasi / ruangan</label>
                                <input type="text" class="form-control" name="ruangan" id="f_ruangan"
                                    maxlength="255" value="{{ old('ruangan') }}" placeholder="Contoh: Ruang IT">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Keterangan</label>
                                <textarea class="form-control" name="deskripsi" id="f_ket" rows="2">{{ old('deskripsi') }}</textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-1"></i>
                            Simpan data</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalDetail" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title fw-bold mb-0" id="detailTitle">Detail</h5>
                        <div class="small text-muted" id="detailSub"></div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="detailBody"></div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalImport" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <form action="{{ route('HR.rekap_inventaris.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold">Import data rekap inventaris</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <label for="file" class="form-label fw-semibold">Pilih file Excel (.xlsx, .xls, .csv)</label>
                        <input type="file" name="file" id="file" class="form-control"
                            accept=".xlsx,.xls,.csv" required>
                        <small class="text-muted d-block mt-2">Pastikan file memiliki kolom <strong>Nama Barang</strong>
                            (atau <em>Name</em>) dan <strong>Kategori</strong>.</small>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Import sekarang</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalPanduan" tabindex="-1" aria-labelledby="panduanLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header modal-header-custom border-0">
                    <h5 class="modal-title" id="panduanLabel"><i class="fa-solid fa-book-open me-2"></i>Panduan Rekap
                        Inventaris</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <ul class="nav nav-tabs nav-tabs-custom mb-4">
                        <li class="nav-item">
                            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#guide-content-1"
                                type="button"><i class="fa-solid fa-list-ol"></i>Cara Penggunaan</button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#guide-content-2"
                                type="button"><i class="fa-solid fa-database"></i>Sumber Data &amp; Contoh
                                Perhitungan</button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#guide-content-3"
                                type="button"><i class="fa-solid fa-circle-info"></i>Istilah &amp; Bantuan</button>
                        </li>
                    </ul>

                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="guide-content-1">

                            <div class="guide-section">
                                <h5>1. Untuk apa halaman ini?</h5>
                                <p>Halaman ini merangkum seluruh <strong>pembelian barang inventaris</strong>: berapa uang
                                    yang dikeluarkan, berapa unit barang yang dibeli, di kategori dan lokasi mana, serta
                                    bagaimana perbandingannya dengan bulan, quarter, dan tahun sebelumnya.</p>
                                <div class="guide-note">Cara tercepat: pilih <strong>Tahun</strong>, klik
                                    <strong>Terapkan</strong>, lihat tujuh card di bagian atas, lalu klik card yang ingin
                                    diperiksa untuk melihat rinciannya.</div>
                            </div>

                            <div class="guide-section">
                                <h5>2. Menambah Data Secara Manual (Create)</h5>
                                <ol>
                                    <li>Klik tombol <strong>Tambah manual</strong> di kanan atas.</li>
                                    <li>Isi <strong>Nama barang</strong>, pilih <strong>Kategori</strong>, isi
                                        <strong>Qty</strong>, <strong>Total harga</strong>, dan <strong>Tanggal
                                            pembelian</strong>. Kolom bertanda * wajib diisi.</li>
                                    <li>Isi bila ada: <strong>ID barang</strong>, <strong>ID inventaris</strong>,
                                        <strong>No. KK</strong>, <strong>Lokasi / ruangan</strong>, dan
                                        <strong>Keterangan</strong>.</li>
                                    <li>Periksa tulisan <strong>Harga satuan</strong> yang muncul otomatis di bawah kolom
                                        tanggal. Angka ini harus masuk akal.</li>
                                    <li>Klik <strong>Simpan data</strong>. Halaman dimuat ulang dan data langsung masuk
                                        ke rekap pada tahun sesuai tanggal pembelian.</li>
                                </ol>
                                <div class="guide-note"><strong>Total harga</strong> adalah harga seluruh unit, bukan
                                    harga satuan. Contoh: 2 laptop seharga Rp 30.000.000 ditulis Qty 2 dan Total harga
                                    30000000. Satuan otomatis terisi "unit" dan kondisi "baik"; ubah di Daftar Inventaris
                                    bila perlu.</div>
                            </div>

                            <div class="guide-section">
                                <h5>3. Import Banyak Data dari Excel</h5>
                                <ol>
                                    <li>Buka menu <strong>Export / Import</strong>, lalu pilih <strong>Import dari
                                            Excel</strong>.</li>
                                    <li>Pilih file .xlsx, .xls, atau .csv. File wajib memiliki kolom <strong>Nama
                                            Barang</strong> (atau <em>Name</em>) dan <strong>Kategori</strong>.</li>
                                    <li>Klik <strong>Import sekarang</strong>. Hasilnya berupa pesan jumlah data yang
                                        berhasil diimpor.</li>
                                </ol>
                            </div>

                            <div class="guide-section">
                                <h5>4. Mengatur Filter</h5>
                                <ol>
                                    <li>Pilih <strong>Tahun</strong>. Daftar tahun diambil dari tanggal pembelian yang ada
                                        di data.</li>
                                    <li>Pilih <strong>Kategori</strong>. Setelah itu daftar <strong>Lokasi</strong> terisi,
                                        dan setelah memilih lokasi daftar <strong>Jenis barang</strong> ikut terisi.</li>
                                    <li>Pilih <strong>Periode</strong> untuk tab Rekap data: seluruh tahun, bulanan, atau
                                        per 3 bulan (quartal).</li>
                                    <li>Klik <strong>Terapkan</strong>. Klik tombol panah melingkar untuk mengembalikan
                                        semua filter ke awal.</li>
                                </ol>
                                <div class="guide-note">Filter <strong>Periode</strong> sengaja tidak memengaruhi card,
                                    perbandingan bulan/quarter/tahun, dan tren, supaya angka-angka itu selalu mewakili satu
                                    tahun penuh dan bisa dibandingkan.</div>
                            </div>

                            <div class="guide-section">
                                <h5>5. Membaca Card Ringkasan</h5>
                                <p>Setiap card bisa diklik dan membuka jendela detail dengan grafik dan tabel:</p>
                                <ul>
                                    <li><strong>Total pembelian:</strong> akumulasi bulanan, kontribusi tiap bulan, dan
                                        perubahan dari bulan sebelumnya.</li>
                                    <li><strong>Jumlah barang:</strong> total unit dan pembagiannya per kategori.</li>
                                    <li><strong>Jumlah transaksi:</strong> banyaknya pembelian per bulan dan bulan
                                        tersibuk.</li>
                                    <li><strong>Rata-rata per transaksi:</strong> rata-rata, median, nilai terkecil dan
                                        terbesar, simpangan baku, serta sebaran nilai.</li>
                                    <li><strong>Transaksi terbesar:</strong> 10 pembelian dengan nilai tertinggi.</li>
                                    <li><strong>Kategori terbesar:</strong> urutan kategori berdasarkan nilai pembelian dan
                                        perubahannya dari tahun lalu.</li>
                                    <li><strong>Vs tahun lalu:</strong> pertumbuhan total pembelian dan selisih tiap
                                        bulan.</li>
                                </ul>
                            </div>

                            <div class="guide-section">
                                <h5>6. Memakai Tab Rekap Data</h5>
                                <ol>
                                    <li>Pilih tampilan <strong>Per kategori</strong>, <strong>Per periode</strong>, atau
                                        <strong>Per lokasi</strong>. Angka di sebelah nama menunjukkan jumlah barisnya.
                                    </li>
                                    <li>Setiap baris menampilkan jumlah transaksi, jumlah barang, total pembelian, dan
                                        kontribusinya terhadap total.</li>
                                    <li>Klik ikon mata untuk membuka seluruh transaksi di baris itu. Baris <strong>TOTAL
                                            KESELURUHAN</strong> di bawah tabel menjumlahkan semuanya.</li>
                                </ol>
                            </div>

                            <div class="guide-section">
                                <h5>7. Memakai Tab Comparison Pembelian</h5>
                                <ol>
                                    <li><strong>Per bulan:</strong> batang tahun ini dibanding tahun lalu, dengan tabel
                                        perubahan. Klik batang atau baris untuk melihat transaksi bulan itu.</li>
                                    <li><strong>Per quarter:</strong> empat card Q1 sampai Q4 beserta perbandingan ke
                                        quarter yang sama tahun lalu.</li>
                                    <li><strong>Per tahun:</strong> total tiap tahun, dipisah per kategori. Klik batang
                                        atau baris untuk rincian.</li>
                                    <li><strong>Per kategori</strong> dan <strong>Per lokasi:</strong> membandingkan
                                        periode yang dipilih dengan periode yang sama di tahun lalu.</li>
                                </ol>
                            </div>

                            <div class="guide-section">
                                <h5>8. Memakai Tab Tren Pembelian</h5>
                                <p>Empat grafik: tren bulanan, komposisi kategori, 10 jenis barang dengan pembelian
                                    terbesar, dan akumulasi pembelian sejak Januari (YTD). Klik titik, batang, atau
                                    potongan grafik untuk membuka detailnya.</p>
                            </div>

                            <div class="guide-section">
                                <h5>9. Mengunduh Laporan</h5>
                                <ol>
                                    <li>Atur filter lalu klik <strong>Terapkan</strong>.</li>
                                    <li>Buka menu <strong>Export / Import</strong> dan pilih <strong>Export PDF</strong>
                                        atau <strong>Export Excel</strong>. Isi laporan mengikuti filter yang sedang
                                        dipilih.</li>
                                </ol>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="guide-content-2">

                            <div class="guide-section">
                                <h5>A. Dari mana data ini berasal?</h5>
                                <p>Rekap tidak memiliki tabel sendiri. Seluruh angka dibaca langsung dari data
                                    <strong>Inventaris</strong> (menu Daftar Inventaris), sehingga selalu mengikuti data
                                    terbaru. Tombol Sinkronisasi hanya menampilkan informasi ini dan tidak mengubah apa
                                    pun.</p>
                                <p class="mb-1"><strong>Tiga cara memasukkan data:</strong></p>
                                <ul>
                                    <li><strong>Tambah manual</strong> dari halaman ini.</li>
                                    <li><strong>Import dari Excel</strong> untuk banyak data sekaligus.</li>
                                    <li>Input langsung di menu <strong>Daftar Inventaris</strong>.</li>
                                </ul>
                                <p class="mb-1"><strong>Kolom yang dipakai dan fungsinya:</strong></p>
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered align-middle">
                                        <thead class="table-light">
                                            <tr>
                                                <th style="width:200px">Kolom data</th>
                                                <th>Dipakai untuk</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>Tanggal pembelian</td>
                                                <td>Menentukan bulan, quarter, dan tahun sebuah pembelian.</td>
                                            </tr>
                                            <tr>
                                                <td>Kategori</td>
                                                <td>Rekap per kategori, komposisi, dan filter.</td>
                                            </tr>
                                            <tr>
                                                <td>Lokasi / ruangan</td>
                                                <td>Rekap per lokasi dan filter.</td>
                                            </tr>
                                            <tr>
                                                <td>Nama barang</td>
                                                <td>Filter jenis barang dan grafik 10 jenis barang terbesar.</td>
                                            </tr>
                                            <tr>
                                                <td>Qty</td>
                                                <td>Dijumlahkan menjadi <em>Jumlah barang</em>.</td>
                                            </tr>
                                            <tr>
                                                <td>Total harga</td>
                                                <td>Dijumlahkan menjadi <em>Total pembelian</em>. Semua nilai uang berasal
                                                    dari kolom ini.</td>
                                            </tr>
                                            <tr>
                                                <td>No. KK, ID barang, ID inventaris, Keterangan</td>
                                                <td>Hanya ditampilkan pada tabel detail transaksi.</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="guide-section">
                                <h5>B. Cara Mendapatkan Angka yang Anda Butuhkan</h5>
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered align-middle">
                                        <thead class="table-light">
                                            <tr>
                                                <th style="width:36%">Yang ingin diketahui</th>
                                                <th>Caranya</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>Total pembelian kategori ITSM pada Quartal 1</td>
                                                <td>Kategori <strong>ITSM</strong>, Periode <strong>Per 3 bulan</strong>,
                                                    pilih Quartal 1, klik Terapkan. Lihat baris ITSM di tab Rekap data
                                                    &gt; Per kategori.</td>
                                            </tr>
                                            <tr>
                                                <td>Pembelian di Ruang IT</td>
                                                <td>Pilih Kategori, lalu Lokasi <strong>Ruang IT</strong>, klik Terapkan.
                                                    Seluruh card ikut menyempit ke lokasi itu.</td>
                                            </tr>
                                            <tr>
                                                <td>Barang apa yang paling banyak dibeli</td>
                                                <td>Tab <strong>Tren pembelian</strong> &gt; grafik 10 jenis barang
                                                    terbesar, atau card <strong>Transaksi terbesar</strong>.</td>
                                            </tr>
                                            <tr>
                                                <td>Apakah belanja naik dari tahun lalu</td>
                                                <td>Card <strong>Vs tahun lalu</strong>, atau tab Comparison &gt; Per
                                                    bulan.</td>
                                            </tr>
                                            <tr>
                                                <td>Daftar lengkap transaksi satu bulan</td>
                                                <td>Tab Comparison &gt; Per bulan, klik baris bulan yang diinginkan.</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="guide-section">
                                <h5>C. Contoh Data</h5>
                                <p>Anggap tahun 2026 baru memiliki lima transaksi berikut:</p>
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered align-middle bg-white">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Tanggal</th>
                                                <th>Nama barang</th>
                                                <th>Kategori</th>
                                                <th>Lokasi</th>
                                                <th class="text-end">Qty</th>
                                                <th class="text-end">Total harga</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>12-01-2026</td>
                                                <td>Laptop Dell XPS</td>
                                                <td>ITSM</td>
                                                <td>Ruang IT</td>
                                                <td class="text-end">2</td>
                                                <td class="text-end">Rp 30.000.000</td>
                                            </tr>
                                            <tr>
                                                <td>20-01-2026</td>
                                                <td>Kursi Kerja</td>
                                                <td>Office</td>
                                                <td>Ruang HR</td>
                                                <td class="text-end">10</td>
                                                <td class="text-end">Rp 8.500.000</td>
                                            </tr>
                                            <tr>
                                                <td>05-02-2026</td>
                                                <td>Proyektor</td>
                                                <td>Kelas</td>
                                                <td>Kelas A</td>
                                                <td class="text-end">1</td>
                                                <td class="text-end">Rp 6.000.000</td>
                                            </tr>
                                            <tr>
                                                <td>18-02-2026</td>
                                                <td>Monitor 24 inci</td>
                                                <td>ITSM</td>
                                                <td>Ruang IT</td>
                                                <td class="text-end">4</td>
                                                <td class="text-end">Rp 9.000.000</td>
                                            </tr>
                                            <tr>
                                                <td>10-03-2026</td>
                                                <td>Meja Rapat</td>
                                                <td>Office</td>
                                                <td>Ruang Meeting</td>
                                                <td class="text-end">1</td>
                                                <td class="text-end">Rp 12.500.000</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="guide-section">
                                <h5>D. Rumus yang Dipakai</h5>
                                <div class="guide-formula"><strong>Total pembelian</strong> = jumlah semua Total harga</div>
                                <div class="guide-formula"><strong>Jumlah barang</strong> = jumlah semua Qty</div>
                                <div class="guide-formula"><strong>Jumlah transaksi</strong> = banyaknya baris data</div>
                                <div class="guide-formula"><strong>Rata-rata per transaksi</strong> = Total pembelian
                                    &divide; Jumlah transaksi</div>
                                <div class="guide-formula"><strong>Kontribusi</strong> = Nilai bagian &divide; Total
                                    &times; 100%</div>
                                <div class="guide-formula"><strong>Akumulasi (YTD)</strong> = total Januari + total bulan
                                    berikutnya, dijumlahkan berjalan sampai bulan yang dilihat</div>
                                <div class="guide-formula"><strong>Perubahan / pertumbuhan</strong> = (Tahun ini &minus;
                                    Tahun lalu) &divide; Tahun lalu &times; 100%</div>
                            </div>

                            <div class="guide-section">
                                <h5>E. Contoh Perhitungan Akumulasi</h5>
                                <div class="guide-example">
                                    <p class="mb-2"><strong>1. Hasil card dari lima transaksi di atas</strong></p>
                                    <div class="table-responsive mb-3">
                                        <table class="table table-sm table-bordered bg-white mb-0">
                                            <thead>
                                                <tr>
                                                    <th>Card</th>
                                                    <th>Nilai</th>
                                                    <th>Cara menghitung</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>Total pembelian</td>
                                                    <td>Rp 66.000.000</td>
                                                    <td>30 + 8,5 + 6 + 9 + 12,5 juta</td>
                                                </tr>
                                                <tr>
                                                    <td>Jumlah barang</td>
                                                    <td>18</td>
                                                    <td>2 + 10 + 1 + 4 + 1</td>
                                                </tr>
                                                <tr>
                                                    <td>Jumlah transaksi</td>
                                                    <td>5</td>
                                                    <td>5 baris data</td>
                                                </tr>
                                                <tr>
                                                    <td>Rata-rata per transaksi</td>
                                                    <td>Rp 13.200.000</td>
                                                    <td>66.000.000 &divide; 5</td>
                                                </tr>
                                                <tr>
                                                    <td>Transaksi terbesar</td>
                                                    <td>Rp 30.000.000</td>
                                                    <td>Laptop Dell XPS, 45,5% dari total (30 &divide; 66)</td>
                                                </tr>
                                                <tr>
                                                    <td>Median</td>
                                                    <td>Rp 9.000.000</td>
                                                    <td>Nilai tengah dari 6 - 8,5 - 9 - 12,5 - 30 juta</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>

                                    <p class="mb-2"><strong>2. Rekap per kategori (tab Rekap data)</strong></p>
                                    <div class="table-responsive mb-3">
                                        <table class="table table-sm table-bordered bg-white mb-0">
                                            <thead>
                                                <tr>
                                                    <th>Kategori</th>
                                                    <th class="text-end">Transaksi</th>
                                                    <th class="text-end">Barang</th>
                                                    <th class="text-end">Total</th>
                                                    <th class="text-end">Kontribusi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>ITSM</td>
                                                    <td class="text-end">2</td>
                                                    <td class="text-end">6</td>
                                                    <td class="text-end">Rp 39.000.000</td>
                                                    <td class="text-end">59,1%</td>
                                                </tr>
                                                <tr>
                                                    <td>Office</td>
                                                    <td class="text-end">2</td>
                                                    <td class="text-end">11</td>
                                                    <td class="text-end">Rp 21.000.000</td>
                                                    <td class="text-end">31,8%</td>
                                                </tr>
                                                <tr>
                                                    <td>Kelas</td>
                                                    <td class="text-end">1</td>
                                                    <td class="text-end">1</td>
                                                    <td class="text-end">Rp 6.000.000</td>
                                                    <td class="text-end">9,1%</td>
                                                </tr>
                                                <tr class="fw-bold">
                                                    <td>TOTAL KESELURUHAN</td>
                                                    <td class="text-end">5</td>
                                                    <td class="text-end">18</td>
                                                    <td class="text-end">Rp 66.000.000</td>
                                                    <td class="text-end">100%</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>

                                    <p class="mb-2"><strong>3. Akumulasi per bulan (card Total pembelian &gt;
                                            Detail)</strong></p>
                                    <div class="table-responsive mb-3">
                                        <table class="table table-sm table-bordered bg-white mb-0">
                                            <thead>
                                                <tr>
                                                    <th>Bulan</th>
                                                    <th class="text-end">Transaksi</th>
                                                    <th class="text-end">Total bulan</th>
                                                    <th class="text-end">Akumulasi</th>
                                                    <th class="text-end">Perubahan dari bulan lalu</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>Januari</td>
                                                    <td class="text-end">2</td>
                                                    <td class="text-end">Rp 38.500.000</td>
                                                    <td class="text-end">Rp 38.500.000</td>
                                                    <td class="text-end">-</td>
                                                </tr>
                                                <tr>
                                                    <td>Februari</td>
                                                    <td class="text-end">2</td>
                                                    <td class="text-end">Rp 15.000.000</td>
                                                    <td class="text-end">Rp 53.500.000</td>
                                                    <td class="text-end down">-61,0%</td>
                                                </tr>
                                                <tr>
                                                    <td>Maret</td>
                                                    <td class="text-end">1</td>
                                                    <td class="text-end">Rp 12.500.000</td>
                                                    <td class="text-end">Rp 66.000.000</td>
                                                    <td class="text-end down">-16,7%</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <p class="mb-3">Akumulasi Februari = 38,5 + 15 = 53,5 juta. Akumulasi Maret = 53,5 +
                                        12,5 = 66 juta, sama dengan total pembelian. Perubahan Februari = (15 &minus; 38,5)
                                        &divide; 38,5 = &minus;61,0%.</p>

                                    <p class="mb-2"><strong>4. Perbandingan dengan tahun lalu</strong></p>
                                    <p class="mb-0">Jika total Januari sampai Maret 2025 adalah Rp 52.800.000, maka
                                        pertumbuhannya = (66.000.000 &minus; 52.800.000) &divide; 52.800.000 =
                                        <span class="up fw-semibold">+25,0%</span>. Warna <span
                                            class="up fw-semibold">merah</span> berarti pembelian naik, sedangkan <span
                                            class="down fw-semibold">hijau</span> berarti pembelian turun.</p>
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="guide-content-3">

                            <div class="guide-section">
                                <h5>A. Arti Istilah</h5>
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered align-middle">
                                        <thead class="table-light">
                                            <tr>
                                                <th style="width:170px">Istilah</th>
                                                <th>Artinya</th>
                                                <th style="width:240px">Contoh</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td><span class="badge bg-primary">Kategori</span></td>
                                                <td>Kelompok barang inventaris.</td>
                                                <td>Office, Kelas, ITSM</td>
                                            </tr>
                                            <tr>
                                                <td><span class="badge bg-secondary">Lokasi / ruangan</span></td>
                                                <td>Tempat barang digunakan atau disimpan.</td>
                                                <td>Ruang IT</td>
                                            </tr>
                                            <tr>
                                                <td><span class="badge bg-info text-dark">No. KK</span></td>
                                                <td>Nomor kas keluar yang menjadi bukti pembayaran pembelian.</td>
                                                <td>KK-0123</td>
                                            </tr>
                                            <tr>
                                                <td><span class="badge bg-dark">Transaksi</span></td>
                                                <td>Satu baris pembelian pada data inventaris.</td>
                                                <td>Pembelian 2 laptop = 1 transaksi</td>
                                            </tr>
                                            <tr>
                                                <td><span class="badge bg-success">Quarter</span></td>
                                                <td>Periode 3 bulan: Q1 Jan-Mar, Q2 Apr-Jun, Q3 Jul-Sep, Q4 Okt-Des.</td>
                                                <td>Q3 = Juli sampai September</td>
                                            </tr>
                                            <tr>
                                                <td><span class="badge bg-warning text-dark">YTD</span></td>
                                                <td>Akumulasi dari Januari sampai bulan yang dilihat.</td>
                                                <td>YTD Maret = Jan + Feb + Mar</td>
                                            </tr>
                                            <tr>
                                                <td><span class="badge bg-warning text-dark">YoY / MoM</span></td>
                                                <td>YoY = dibanding tahun lalu. MoM = dibanding bulan sebelumnya.</td>
                                                <td>+25% YoY</td>
                                            </tr>
                                            <tr>
                                                <td>Kontribusi</td>
                                                <td>Porsi sebuah bagian terhadap total, dalam persen.</td>
                                                <td>ITSM 59,1% dari total</td>
                                            </tr>
                                            <tr>
                                                <td>Median</td>
                                                <td>Nilai tengah setelah semua transaksi diurutkan dari terkecil.</td>
                                                <td>9 juta pada contoh</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="guide-note">Quarter dan bulan dihitung dari <strong>tanggal pembelian</strong>,
                                    bukan dari tanggal data dimasukkan.</div>
                            </div>

                            <div class="guide-section">
                                <h5>B. Pertanyaan yang Sering Muncul</h5>
                                <div class="guide-qa">
                                    <strong>Mengapa data yang baru saya tambahkan tidak muncul?</strong>
                                    <span>Periksa <strong>Tahun</strong> pada filter. Data masuk ke tahun sesuai tanggal
                                        pembelian, bukan tahun hari ini. Periksa juga filter Kategori, Lokasi, dan
                                        Jenis.</span>
                                </div>
                                <div class="guide-qa">
                                    <strong>Mengapa card tidak berubah saat saya memilih Bulanan?</strong>
                                    <span>Filter Periode hanya untuk tab Rekap data serta perbandingan Kategori dan
                                        Lokasi. Card dan grafik lain selalu menghitung satu tahun penuh.</span>
                                </div>
                                <div class="guide-qa">
                                    <strong>Mengapa kolom Lokasi tidak bisa dipilih?</strong>
                                    <span>Pilih <strong>Kategori</strong> terlebih dahulu. Daftar lokasi mengikuti kategori,
                                        dan daftar jenis barang mengikuti lokasi.</span>
                                </div>
                                <div class="guide-qa">
                                    <strong>Mengapa "Vs tahun lalu" bertanda "-" atau "Baru"?</strong>
                                    <span>Tahun sebelumnya belum memiliki data pembelian, sehingga persentase tidak bisa
                                        dihitung.</span>
                                </div>
                                <div class="guide-qa">
                                    <strong>Bagaimana mengubah atau menghapus data yang salah?</strong>
                                    <span>Halaman ini hanya untuk melihat rekap dan menambah data. Ubah atau hapus data
                                        melalui menu <strong>Daftar Inventaris</strong>, rekap otomatis mengikuti.</span>
                                </div>
                            </div>

                            <div class="guide-section">
                                <h5>C. Permintaan Perubahan atau Laporan Error</h5>
                                <div class="guide-itsm">
                                    <p class="mb-2"><strong>Jika ada permintaan perubahan fitur atau tampilan, atau Anda
                                            menemukan error atau angka yang tidak sesuai, silakan hubungi Tim ITSM.</strong>
                                    </p>
                                    <ol class="mb-3">
                                        <li>Klik tombol <strong>Hubungi Tim ITSM</strong> di bawah.</li>
                                        <li>Pilih jenis permintaan (laporan error, fitur baru, perbaikan, atau masalah
                                            data).</li>
                                        <li>Tuliskan apa yang terjadi, filter yang dipakai, dan hasil yang Anda
                                            harapkan.</li>
                                        <li>Lampirkan tangkapan layar bila ada, lalu kirim.</li>
                                    </ol>
                                    @if (\Illuminate\Support\Facades\Route::has('tickets.index'))
                                        <a href="{{ route('tickets.index') }}" class="btn btn-danger btn-sm"
                                            target="_blank"><i class="fa-solid fa-ticket me-1"></i>Hubungi Tim ITSM</a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary btn-sm"
                        data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            $(function() {
                const expBtn = document.getElementById('expToggle'),
                    expMenu = document.getElementById('expMenu');
                const expClose = () => {
                    expMenu.classList.remove('show');
                    expBtn.setAttribute('aria-expanded', 'false');
                };
                expBtn.addEventListener('click', e => {
                    e.stopPropagation();
                    expBtn.setAttribute('aria-expanded', expMenu.classList.toggle('show'));
                });
                expMenu.addEventListener('click', expClose);
                document.addEventListener('click', expClose);
                document.addEventListener('keydown', e => {
                    if (e.key === 'Escape') expClose();
                });

                const R = {
                    data: "{{ route('HR.rekap_inventaris.load_data') }}",
                    detail: "{{ route('HR.rekap_inventaris.detail_data') }}",
                    lokasi: "{{ url('HR-dashboard/rekap-inventaris/ajax/lokasi') }}",
                    jenis: "{{ url('HR-dashboard/rekap-inventaris/ajax/jenis') }}",
                    pdf: "{{ route('HR.rekap_inventaris.export_pdf') }}",
                    excel: "{{ route('HR.rekap_inventaris.export') }}"
                };
                const DEFAULT_TAHUN = "{{ $defaultTahun }}";
                const HDR = {
                    'Accept': 'application/json'
                };
                const PAL = ['#4f46e5', '#10b981', '#f59e0b', '#ef4444', '#06b6d4', '#a855f7', '#64748b', '#ec4899'];
                const MONTHS = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September',
                    'Oktober', 'November', 'Desember'
                ];
                const RANGE_Q = ['Jan-Mar', 'Apr-Jun', 'Jul-Sep', 'Okt-Des'];

                const rp = n => 'Rp ' + Math.round(n || 0).toLocaleString('id-ID');
                const num = n => Math.round(n || 0).toLocaleString('id-ID');
                const rpS = v => v >= 1e9 ? 'Rp ' + (v / 1e9).toFixed(1).replace('.0', '') + ' M' : (v >= 1e6 ? 'Rp ' +
                    (v / 1e6).toFixed(1).replace('.0', '') + ' jt' : (v >= 1e3 ? 'Rp ' + Math.round(v / 1e3) +
                        ' rb' : 'Rp ' + Math.round(v)));
                const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#39;'
                } [c]));
                const pad = n => String(n).padStart(2, '0');
                const iso = d => `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`;
                const pctTxt = (a, b) => {
                    if (!b) return a > 0 ? '<span class="text-muted">Baru</span>' : '-';
                    const d = Math.round((a - b) / b * 1000) / 10;
                    return `<span class="${d > 0 ? 'up' : (d < 0 ? 'down' : '')}">${d > 0 ? '+' : ''}${String(d).replace('.', ',')}%</span>`;
                };

                const filters = () => ({
                    tahun: $('#filter_tahun').val(),
                    kategori: $('#filter_kategori').val(),
                    lokasi: $('#filter_lokasi').val(),
                    jenis: $('#filter_jenis').val(),
                    mode_periode: $('input[name=mode_periode]:checked').val(),
                    bulan: $('#filter_bulan').val(),
                    quartal: $('#filter_quartal').val()
                });

                const charts = {},
                    pending = {};

                function destroyChart(id) {
                    if (charts[id]) {
                        try {
                            charts[id].destroy();
                        } catch (e) {}
                        delete charts[id];
                    }
                }

                function mount(id, opt) {
                    destroyChart(id);
                    const el = document.getElementById(id);
                    if (!el) return;
                    if (el.offsetParent === null) {
                        pending[id] = opt;
                        return;
                    }
                    delete pending[id];
                    el.innerHTML = '';
                    charts[id] = new ApexCharts(el, opt);
                    charts[id].render();
                }

                function show(id, ok, opt) {
                    if (ok) {
                        mount(id, opt);
                        return;
                    }
                    destroyChart(id);
                    delete pending[id];
                    $('#' + id).html('<div class="text-center text-muted py-5">Tidak ada data untuk ditampilkan.</div>');
                }

                document.addEventListener('shown.bs.tab', () => Object.keys(pending).forEach(id => {
                    const el = document.getElementById(id);
                    if (el && el.offsetParent !== null) mount(id, pending[id]);
                }));

                const chartBase = (type, events, extra, stacked) => {
                    const o = Object.assign({
                        chart: {
                            type,
                            height: 320,
                            toolbar: {
                                show: false
                            },
                            fontFamily: 'inherit',
                            events: events || {}
                        },
                        colors: PAL,
                        dataLabels: {
                            enabled: false
                        },
                        legend: {
                            position: 'top'
                        },
                        grid: {
                            borderColor: '#e2e8f0'
                        }
                    }, extra || {});
                    if (stacked) o.chart.stacked = true;
                    return o;
                };
                const moneyTip = {
                    y: {
                        formatter: v => rp(v)
                    }
                };

                const detailModalEl = document.getElementById('modalDetail');
                const detailModal = new bootstrap.Modal(detailModalEl);

                function whenShown(fn) {
                    if (detailModalEl.classList.contains('show')) fn();
                    else detailModalEl.addEventListener('shown.bs.modal', fn, {
                        once: true
                    });
                }

                $(document).on('click', '[data-detail]', function() {
                    openDetail(this.dataset.detail, this.dataset.key || '', this.dataset.label || 'Detail');
                });

                function openDetail(tipe, nilai, label, tanpaPeriode) {
                    $('#detailTitle').text(label);
                    $('#detailSub').text('');
                    $('#detailBody').html(
                        '<div class="text-center py-5 text-muted"><i class="fa-solid fa-spinner fa-spin me-1"></i> Memuat detail...</div>'
                    );
                    detailModal.show();

                    const data = Object.assign({}, filters(), {
                        tipe: tipe,
                        nilai: nilai
                    });
                    if (tanpaPeriode) data.mode_periode = 'semua';

                    $.ajax({
                            url: R.detail,
                            method: 'GET',
                            dataType: 'json',
                            headers: HDR,
                            data: data
                        })
                        .done(renderDetail)
                        .fail(function() {
                            $('#detailBody').html(
                                '<div class="alert alert-danger">Detail tidak dapat dimuat. Coba lagi.</div>');
                        });
                }

                function specToOptions(c) {
                    const money = c.money !== false;
                    const fmt = v => money ? rp(v) : num(v);

                    if (c.type === 'donut') {
                        return chartBase('donut', {}, {
                            series: c.series,
                            labels: c.labels,
                            legend: {
                                position: 'bottom'
                            },
                            tooltip: {
                                y: {
                                    formatter: fmt
                                }
                            }
                        });
                    }

                    const o = chartBase(c.type, {}, {
                        series: c.series,
                        xaxis: {
                            categories: c.labels
                        },
                        stroke: {
                            curve: 'smooth',
                            width: c.type === 'bar' ? 0 : 3
                        },
                        plotOptions: {
                            bar: {
                                horizontal: !!c.horizontal,
                                borderRadius: 4,
                                columnWidth: '55%',
                                distributed: !!c.horizontal && c.series.length === 1
                            }
                        },
                        legend: {
                            position: 'top',
                            show: !(c.horizontal && c.series.length === 1)
                        },
                        tooltip: {
                            y: {
                                formatter: fmt
                            }
                        }
                    }, !!c.stacked);

                    if (c.horizontal) {
                        o.xaxis = {
                            categories: c.labels,
                            labels: {
                                formatter: v => money ? rpS(v) : num(v)
                            }
                        };
                    } else {
                        o.yaxis = {
                            labels: {
                                formatter: v => money ? rpS(v) : num(v)
                            }
                        };
                    }
                    return o;
                }

                function renderDetail(d) {
                    if (!d.success) {
                        $('#detailBody').html('<div class="alert alert-danger">' + esc(d.message || 'Detail gagal dimuat.') +
                            '</div>');
                        return;
                    }
                    const right = d.right || [];
                    $('#detailTitle').text(d.title);
                    $('#detailSub').text(d.subtitle);

                    let h = '<div class="row g-3 mb-3">';
                    d.stats.forEach(s => h +=
                        `<div class="col-6 col-lg"><div class="border rounded p-3 h-100"><div class="small text-muted">${esc(s.label)}</div><div class="fw-bold">${esc(s.value)}</div></div></div>`
                        );
                    h += '</div>';
                    if (d.chart) h += '<div id="detailChart" class="mb-3"></div>';

                    if (d.rows.length) {
                        h +=
                            '<input type="search" class="form-control form-control-sm mb-2" id="detailFilter" placeholder="Cari di tabel...">';
                        h +=
                            '<div class="table-responsive"><table class="table table-sm table-hover align-middle" id="detailTable"><thead class="table-light"><tr>';
                        d.columns.forEach((c, i) => h +=
                            `<th class="${right.includes(i) ? 'text-end' : ''}">${esc(c)}</th>`);
                        h += '</tr></thead><tbody>';
                        d.rows.forEach(r => {
                            h += '<tr>';
                            r.forEach((v, i) => h +=
                                `<td class="${right.includes(i) ? 'text-end' : ''}">${esc(v)}</td>`);
                            h += '</tr>';
                        });
                        h += '</tbody></table></div>';
                    } else {
                        h += '<div class="text-center text-muted py-4">Belum ada data pada periode ini.</div>';
                    }

                    $('#detailBody').html(h);
                    if (d.chart) whenShown(() => mount('detailChart', specToOptions(d.chart)));
                    $('#detailFilter').on('input', function() {
                        const q = this.value.toLowerCase();
                        $('#detailTable tbody tr').each(function() {
                            $(this).toggle(this.textContent.toLowerCase().includes(q));
                        });
                    });
                }

                let D = null,
                    xhrData = null;

                function setLoading() {
                    $('[id^=c_]').text('...');
                    $('[id^=s_]').html('&nbsp;');
                    $('#tbody_kat, #tbody_per, #tbody_lok').html(
                        '<tr><td colspan="7" class="text-center py-4 text-muted"><i class="fa-solid fa-spinner fa-spin me-1"></i> Memuat data...</td></tr>'
                        );
                    $('#tfoot_kat, #tfoot_per, #tfoot_lok').empty();
                }

                function loadData() {
                    setLoading();
                    if (xhrData) xhrData.abort();

                    xhrData = $.ajax({
                            url: R.data,
                            method: 'GET',
                            dataType: 'json',
                            headers: HDR,
                            data: filters()
                        })
                        .done(function(res) {
                            D = res;
                            renderCards();
                            renderTabel();
                            renderCompare();
                            renderTrend();
                        })
                        .fail(function(xhr, status) {
                            if (status === 'abort') return;
                            $('[id^=c_]').text('-');
                            $('#tbody_kat, #tbody_per, #tbody_lok').html(
                                '<tr><td colspan="7" class="text-center py-4 text-danger">Data gagal dimuat. Coba lagi.</td></tr>'
                                );
                        });
                }

                function renderCards() {
                    const c = D.cards;
                    $('#c_total').text(rp(c.total));
                    $('#s_total').text('Tahun ' + D.tahun);
                    $('#c_qty').text(num(c.qty));
                    $('#s_qty').text('unit barang');
                    $('#c_count').text(num(c.count));
                    $('#s_count').text('transaksi');
                    $('#c_avg').text(rp(c.avg));
                    $('#s_avg').text('per transaksi');
                    $('#c_max').text(c.max ? rp(c.max.total) : '-');
                    $('#s_max').text(c.max ? c.max.nama : 'Belum ada data');
                    $('#c_kat').text(c.top_kategori ? c.top_kategori.nama : '-');
                    $('#s_kat').text(c.top_kategori ? rp(c.top_kategori.total) + ' (' + String(c.top_kategori.persen)
                        .replace('.', ',') + '%)' : 'Belum ada data');
                    if (c.growth === null) {
                        $('#c_growth').text('-');
                        $('#s_growth').text('Tahun lalu belum ada data');
                    } else {
                        $('#c_growth').html(
                            `<span class="${c.growth > 0 ? 'up' : 'down'}">${c.growth > 0 ? '+' : ''}${String(c.growth).replace('.', ',')}%</span>`
                            );
                        $('#s_growth').text('dari ' + rp(c.prev_total));
                    }
                }

                function tabel(rows, o) {
                    $(o.badge).text(rows.length);

                    if (!rows.length) {
                        $(o.body).html(
                            '<tr><td colspan="7" class="text-center py-4 text-muted">Tidak ada data untuk filter yang dipilih.</td></tr>'
                            );
                        $(o.foot).empty();
                        return;
                    }

                    let trx = 0,
                        qty = 0,
                        total = 0;

                    $(o.body).html(rows.map((r, i) => {
                        trx += r.transaksi;
                        qty += r.jumlah_barang;
                        total += r.total;
                        return `<tr>
                            <td>${i + 1}</td>
                            <td class="fw-semibold">${esc(r.nama)}</td>
                            <td class="text-end">${num(r.transaksi)}</td>
                            <td class="text-end">${num(r.jumlah_barang)}</td>
                            <td class="text-end fw-bold text-success">${rp(r.total)}</td>
                            <td class="text-end">${String(r.persen).replace('.', ',')}%</td>
                            <td class="text-center"><button type="button" class="btn btn-sm btn-info text-white" data-detail="${o.tipe}" data-key="${esc(r.key)}" data-label="${esc(o.prefix + ' ' + r.nama)}"><i class="fa-solid fa-eye"></i></button></td>
                        </tr>`;
                    }).join(''));

                    $(o.foot).html(
                        `<tr><td colspan="2" class="text-end">TOTAL KESELURUHAN:</td><td class="text-end">${num(trx)}</td><td class="text-end">${num(qty)}</td><td class="text-end">${rp(total)}</td><td class="text-end">100%</td><td></td></tr>`
                        );
                }

                function renderTabel() {
                    tabel(D.tab1, {
                        body: '#tbody_kat',
                        foot: '#tfoot_kat',
                        badge: '#cnt_kat',
                        tipe: 'kategori',
                        prefix: 'Detail kategori'
                    });
                    tabel(D.tab2, {
                        body: '#tbody_per',
                        foot: '#tfoot_per',
                        badge: '#cnt_per',
                        tipe: 'month',
                        prefix: 'Detail periode'
                    });
                    tabel(D.tabLokasi, {
                        body: '#tbody_lok',
                        foot: '#tfoot_lok',
                        badge: '#cnt_lok',
                        tipe: 'lokasi',
                        prefix: 'Detail lokasi'
                    });
                }

                function renderCompare() {
                    const m = D.months,
                        thn = D.tahun;
                    const adaData = D.cards.total > 0 || D.cards.prev_total > 0;

                    show('chartBulan', adaData, chartBase('bar', {
                        dataPointSelection: (e, c, cfg) => openDetail('month', cfg.dataPointIndex + 1, MONTHS[
                            cfg.dataPointIndex] + ' ' + thn)
                    }, {
                        series: [{
                            name: 'Tahun ' + thn,
                            data: m.map(x => x.total)
                        }, {
                            name: 'Tahun ' + (thn - 1),
                            data: m.map(x => x.prev)
                        }],
                        xaxis: {
                            categories: m.map(x => x.label)
                        },
                        colors: ['#4f46e5', '#cbd5e1'],
                        plotOptions: {
                            bar: {
                                borderRadius: 4,
                                columnWidth: '60%'
                            }
                        },
                        yaxis: {
                            labels: {
                                formatter: rpS
                            }
                        },
                        tooltip: moneyTip
                    }));

                    $('#tblBulan').html(m.map((x, i) =>
                        `<tr class="clickable" data-detail="month" data-key="${i + 1}" data-label="${MONTHS[i]} ${thn}">
                            <td>${MONTHS[i]}</td><td class="text-end">${num(x.count)}</td><td class="text-end">${num(x.qty)}</td>
                            <td class="text-end fw-bold">${rp(x.total)}</td><td class="text-end">${rp(x.prev)}</td>
                            <td class="text-end">${pctTxt(x.total, x.prev)}</td></tr>`).join(''));

                    $('#quarterCards').html(D.quarters.map((q, i) =>
                        `<div class="col-6 col-lg-3"><div class="card stat-card" data-detail="quarter" data-key="${i + 1}" data-label="Quarter ${i + 1} ${thn}">
                            <div class="card-body py-3"><div class="lbl">${q.label} ${RANGE_Q[i]}</div>
                            <div class="val">${rp(q.total)}</div>
                            <div class="sub">${pctTxt(q.total, q.prev)} dari ${rp(q.prev)} tahun lalu</div>
                            <div class="sub">${num(q.count)} transaksi | ${num(q.qty)} barang</div>
                            <div class="hint mt-1">Lihat detail</div></div></div></div>`).join(''));

                    show('chartQuarter', adaData, chartBase('bar', {
                        dataPointSelection: (e, c, cfg) => openDetail('quarter', cfg.dataPointIndex + 1,
                            'Quarter ' + (cfg.dataPointIndex + 1) + ' ' + thn)
                    }, {
                        series: [{
                            name: 'Tahun ' + thn,
                            data: D.quarters.map(x => x.total)
                        }, {
                            name: 'Tahun ' + (thn - 1),
                            data: D.quarters.map(x => x.prev)
                        }],
                        xaxis: {
                            categories: D.quarters.map(x => x.label)
                        },
                        colors: ['#10b981', '#cbd5e1'],
                        plotOptions: {
                            bar: {
                                borderRadius: 4,
                                columnWidth: '45%'
                            }
                        },
                        yaxis: {
                            labels: {
                                formatter: rpS
                            }
                        },
                        tooltip: moneyTip
                    }));

                    const y = D.years;
                    show('chartTahun', y.list.length > 0, chartBase('bar', {
                        dataPointSelection: (e, c, cfg) => openDetail('year', y.labels[cfg.dataPointIndex],
                            'Tahun ' + y.labels[cfg.dataPointIndex])
                    }, {
                        series: y.series,
                        xaxis: {
                            categories: y.labels.map(String)
                        },
                        plotOptions: {
                            bar: {
                                borderRadius: 4,
                                columnWidth: '40%'
                            }
                        },
                        yaxis: {
                            labels: {
                                formatter: rpS
                            }
                        },
                        tooltip: moneyTip
                    }, true));

                    $('#tblTahun').html(y.list.map((x, i) =>
                        `<tr class="clickable" data-detail="year" data-key="${x.year}" data-label="Tahun ${x.year}">
                            <td class="fw-semibold">${x.year}</td><td class="text-end">${num(x.count)}</td><td class="text-end">${num(x.qty)}</td>
                            <td class="text-end fw-bold">${rp(x.total)}</td>
                            <td class="text-end">${i ? pctTxt(x.total, y.list[i - 1].total) : '-'}</td></tr>`).join('') ||
                        '<tr><td colspan="5" class="text-center text-muted py-3">Belum ada data.</td></tr>');

                    $('.lblPeriode').text(D.periode);
                    $('.lblPeriodeLalu').text(D.periode_lalu);

                    const banding = (id, cd, tipe, prefix) => show(id, cd.labels.length > 0, chartBase('bar', {
                        dataPointSelection: (e, c, cfg) => openDetail(tipe, cd.labels[cfg.dataPointIndex],
                            prefix + ' ' + cd.labels[cfg.dataPointIndex])
                    }, {
                        series: [{
                            name: D.periode,
                            data: cd.current
                        }, {
                            name: D.periode_lalu,
                            data: cd.previous
                        }],
                        xaxis: {
                            categories: cd.labels
                        },
                        colors: ['#4f46e5', '#cbd5e1'],
                        plotOptions: {
                            bar: {
                                borderRadius: 4,
                                columnWidth: '55%'
                            }
                        },
                        yaxis: {
                            labels: {
                                formatter: rpS
                            }
                        },
                        tooltip: moneyTip
                    }));

                    banding('chartKategori', D.chart_kategori, 'kategori', 'Detail kategori');
                    banding('chartLokasi', D.chart_lokasi, 'lokasi', 'Detail lokasi');
                }

                function renderTrend() {
                    const m = D.months,
                        thn = D.tahun,
                        lbl = m.map(x => x.label);
                    const adaData = D.cards.total > 0 || D.cards.prev_total > 0;

                    show('chartTren', adaData, chartBase('line', {
                        markerClick: (e, c, o) => openDetail('month', o.dataPointIndex + 1, MONTHS[o
                            .dataPointIndex] + ' ' + thn)
                    }, {
                        series: [{
                            name: 'Tahun ' + thn,
                            data: m.map(x => x.total)
                        }, {
                            name: 'Tahun ' + (thn - 1),
                            data: m.map(x => x.prev)
                        }],
                        xaxis: {
                            categories: lbl
                        },
                        stroke: {
                            curve: 'smooth',
                            width: [4, 2]
                        },
                        markers: {
                            size: 4
                        },
                        colors: ['#4f46e5', '#94a3b8'],
                        yaxis: {
                            labels: {
                                formatter: rpS
                            }
                        },
                        tooltip: moneyTip
                    }));

                    const k = D.komposisi;
                    show('chartKomposisi', k.labels.length > 0, chartBase('donut', {
                        dataPointSelection: (e, c, cfg) => openDetail('kategori', k.labels[cfg.dataPointIndex],
                            'Detail kategori ' + k.labels[cfg.dataPointIndex], true)
                    }, {
                        series: k.values,
                        labels: k.labels,
                        legend: {
                            position: 'bottom'
                        },
                        tooltip: moneyTip,
                        plotOptions: {
                            pie: {
                                donut: {
                                    size: '62%'
                                }
                            }
                        }
                    }));

                    const j = D.jenis;
                    show('chartJenis', j.length > 0, chartBase('bar', {
                        dataPointSelection: (e, c, cfg) => openDetail('jenis', j[cfg.dataPointIndex].nama, j[
                            cfg.dataPointIndex].nama)
                    }, {
                        series: [{
                            name: 'Total pembelian',
                            data: j.map(x => x.total)
                        }],
                        xaxis: {
                            categories: j.map(x => x.nama),
                            labels: {
                                formatter: rpS
                            }
                        },
                        plotOptions: {
                            bar: {
                                horizontal: true,
                                borderRadius: 4,
                                distributed: true
                            }
                        },
                        legend: {
                            show: false
                        },
                        tooltip: moneyTip
                    }));

                    let s = 0,
                        p = 0;
                    const cum = m.map(x => s += x.total),
                        cumP = m.map(x => p += x.prev);

                    show('chartYTD', adaData, chartBase('area', {
                        dataPointSelection: (e, c, cfg) => openDetail('month', cfg.dataPointIndex + 1,
                            MONTHS[cfg.dataPointIndex] + ' ' + thn)
                    }, {
                        series: [{
                            name: 'Tahun ' + thn,
                            data: cum
                        }, {
                            name: 'Tahun ' + (thn - 1),
                            data: cumP
                        }],
                        xaxis: {
                            categories: lbl
                        },
                        colors: ['#4f46e5', '#94a3b8'],
                        stroke: {
                            curve: 'smooth',
                            width: 2
                        },
                        fill: {
                            type: 'gradient',
                            gradient: {
                                opacityFrom: .35,
                                opacityTo: .02
                            }
                        },
                        yaxis: {
                            labels: {
                                formatter: rpS
                            }
                        },
                        tooltip: moneyTip
                    }));
                }

                $('#form-filter-rekap').on('submit', function(e) {
                    e.preventDefault();
                    loadData();
                });

                $('input[name="mode_periode"]').on('change', function() {
                    const mode = $('input[name="mode_periode"]:checked').val();
                    $('#wrapper_bulan, #wrapper_quartal').addClass('d-none');
                    if (mode === 'bulan') $('#wrapper_bulan').removeClass('d-none');
                    else if (mode === 'quartal') $('#wrapper_quartal').removeClass('d-none');
                });

                $('#filter_kategori').on('change', function() {
                    const kategori = $(this).val();
                    $('#filter_lokasi').html('<option value="">Memuat...</option>').prop('disabled', true);
                    $('#filter_jenis').html('<option value="">Pilih lokasi dulu</option>').prop('disabled', true);

                    if (!kategori) {
                        $('#filter_lokasi').html('<option value="">Pilih kategori dulu</option>');
                        return;
                    }

                    $.getJSON(R.lokasi + '/' + encodeURIComponent(kategori))
                        .done(function(list) {
                            let o = '<option value="">Semua lokasi</option>';
                            $.each(list, function(k, v) {
                                if (v) o += '<option value="' + esc(v) + '">' + esc(v) + '</option>';
                            });
                            $('#filter_lokasi').html(o).prop('disabled', false);
                        })
                        .fail(function() {
                            $('#filter_lokasi').html('<option value="">Gagal memuat lokasi</option>');
                        });
                });

                $('#filter_lokasi').on('change', function() {
                    const lokasi = $(this).val();
                    $('#filter_jenis').html('<option value="">Memuat...</option>').prop('disabled', true);

                    if (!lokasi) {
                        $('#filter_jenis').html('<option value="">Pilih lokasi dulu</option>');
                        return;
                    }

                    $.getJSON(R.jenis + '/' + encodeURIComponent(lokasi))
                        .done(function(list) {
                            let o = '<option value="">Semua jenis barang</option>';
                            $.each(list, function(k, v) {
                                o += '<option value="' + esc(k) + '">' + esc(v) + '</option>';
                            });
                            $('#filter_jenis').html(o).prop('disabled', false);
                        })
                        .fail(function() {
                            $('#filter_jenis').html('<option value="">Gagal memuat jenis barang</option>');
                        });
                });

                $('#btn_reset').on('click', function() {
                    $('#filter_tahun').val(DEFAULT_TAHUN);
                    $('#filter_kategori').val('');
                    $('#filter_lokasi').html('<option value="">Pilih kategori dulu</option>').prop('disabled', true);
                    $('#filter_jenis').html('<option value="">Pilih lokasi dulu</option>').prop('disabled', true);
                    $('#mode_semua').prop('checked', true).trigger('change');
                    loadData();
                });

                $('#btn_export').on('click', function(e) {
                    e.preventDefault();
                    window.location.href = R.excel + '?' + $('#form-filter-rekap').serialize();
                });

                $('#btn_export_pdf').on('click', function(e) {
                    e.preventDefault();
                    window.location.href = R.pdf + '?' + $('#form-filter-rekap').serialize();
                });

                const formModal = new bootstrap.Modal(document.getElementById('modalForm'));

                function satuan() {
                    const q = parseFloat($('#f_qty').val()) || 0,
                        t = parseFloat($('#f_total').val()) || 0;
                    $('#f_satuan').text(q > 0 && t > 0 ? 'Harga satuan: ' + rp(t / q) :
                        'Harga satuan dihitung otomatis dari total harga dibagi qty.');
                }
                $('#f_qty, #f_total').on('input', satuan);

                $('#btnAdd').on('click', function() {
                    $('#formInv').find('input[type=text], input[type=number], input[type=date], textarea').val('');
                    $('#f_qty').val(1);
                    $('#f_kategori').prop('selectedIndex', 0);
                    $('#f_tanggal').val(iso(new Date()));
                    satuan();
                    formModal.show();
                });

                @if ($errors->any())
                    satuan();
                    formModal.show();
                @endif

                const apexReady = window.ApexCharts ? Promise.resolve() : new Promise(function(resolve) {
                    const s = document.createElement('script');
                    s.src = 'https://cdn.jsdelivr.net/npm/apexcharts';
                    s.onload = resolve;
                    s.onerror = resolve;
                    document.head.appendChild(s);
                });
                apexReady.then(loadData);
            });
        </script>
    @endpush
@endsection