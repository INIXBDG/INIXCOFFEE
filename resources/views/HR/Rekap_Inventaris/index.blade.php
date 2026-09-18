@extends('layout_HR.app')
@section('content_HR')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"
        integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <style>
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        ::-webkit-scrollbar-track {
            background: transparent;
        }

        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        .page-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 0.25rem;
        }

        .page-subtitle {
            font-size: 0.9rem;
            color: var(--secondary);
        }

        .nav-tabs .nav-link {
            color: #64748b;
            font-weight: 500;
            border: none;
            border-bottom: 3px solid transparent;
            padding: 0.75rem 1.25rem;
        }

        .nav-tabs .nav-link.active {
            color: #0d6efd;
            background: transparent;
            border-bottom: 3px solid #0d6efd;
        }

        .nav-tabs .nav-link:hover:not(.active) {
            border-bottom: 3px solid #e2e8f0;
        }

        .table tfoot tr {
            background-color: #f8f9fa;
            font-weight: 700;
            color: #334155;
        }

        .filter-card {
            border: 1px solid #e2e8f0;
            border-radius: 0.75rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        .btn-danger,
        .text-danger,
        .bg-danger {
            background-color: #6c757d !important;
            border-color: #6c757d !important;
            color: #fff !important;
        }

        .loading-row td {
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: loading 1.5s infinite;
            height: 40px;
        }

        @keyframes loading {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }

        /* ===== GENERIC SKELETON LINE (dipakai untuk full-page & modal skeleton mockup) ===== */
        .skel-line {
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: loading 1.5s infinite;
            border-radius: 6px;
            display: block;
            width: 100%;
        }

        #page-skeleton, #page-skeleton * {
            pointer-events: none;
        }
    </style>

    <div class="container-fluid px-4 py-4">

        {{-- ======================================================================
             FULL PAGE SKELETON — tampil saat pertama kali load sebelum data siap
        ======================================================================= --}}
        <div id="page-skeleton">
            {{-- Header skeleton --}}
            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <div>
                    <span class="skel-line" style="width:220px;height:24px;margin-bottom:8px"></span>
                    <span class="skel-line" style="width:320px;height:14px"></span>
                </div>
                <div class="d-flex flex-wrap gap-2 mt-2 mt-sm-0">
                    <span class="skel-line" style="width:120px;height:38px;border-radius:6px"></span>
                    <span class="skel-line" style="width:130px;height:38px;border-radius:6px"></span>
                    <span class="skel-line" style="width:110px;height:38px;border-radius:6px"></span>
                    <span class="skel-line" style="width:150px;height:38px;border-radius:6px"></span>
                </div>
            </div>

            {{-- Filter card skeleton --}}
            <div class="card filter-card mb-4">
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <span class="skel-line" style="width:70px;height:12px;margin-bottom:8px"></span>
                            <span class="skel-line" style="height:38px;border-radius:6px"></span>
                        </div>
                        <div class="col-md-4">
                            <span class="skel-line" style="width:110px;height:12px;margin-bottom:8px"></span>
                            <span class="skel-line" style="height:38px;border-radius:6px"></span>
                        </div>
                        <div class="col-md-4">
                            <span class="skel-line" style="width:90px;height:12px;margin-bottom:8px"></span>
                            <span class="skel-line" style="height:38px;border-radius:6px"></span>
                        </div>
                        <div class="col-md-3">
                            <span class="skel-line" style="width:50px;height:12px;margin-bottom:8px"></span>
                            <span class="skel-line" style="height:38px;border-radius:6px"></span>
                        </div>
                        <div class="col-md-9">
                            <span class="skel-line" style="width:120px;height:12px;margin-bottom:12px"></span>
                            <span class="skel-line" style="width:55%;height:20px"></span>
                        </div>
                        <div class="col-12 text-end mt-3">
                            <span class="skel-line" style="width:170px;height:38px;border-radius:6px;margin-left:auto"></span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tabs + table skeleton --}}
            <div class="card">
                <div class="card-body p-0">
                    <div class="d-flex flex-wrap gap-4 px-4 pt-3 pb-3" style="border-bottom:1px solid #e2e8f0">
                        <span class="skel-line" style="width:160px;height:20px"></span>
                        <span class="skel-line" style="width:160px;height:20px"></span>
                        <span class="skel-line" style="width:150px;height:20px"></span>
                        <span class="skel-line" style="width:180px;height:20px"></span>
                    </div>
                    <div class="p-4">
                        <table class="table align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th width="5%">No</th>
                                    <th>Nama Kategori</th>
                                    <th class="text-center" width="15%">Jumlah Barang</th>
                                    <th class="text-end" width="20%">Total Pembelian</th>
                                    <th class="text-center" width="10%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @for ($i = 0; $i < 6; $i++)
                                    <tr>
                                        <td><span class="skel-line" style="width:20px;height:14px"></span></td>
                                        <td><span class="skel-line" style="width:70%;height:14px"></span></td>
                                        <td class="text-center"><span class="skel-line" style="width:60%;height:14px;margin:0 auto"></span></td>
                                        <td class="text-end"><span class="skel-line" style="width:70%;height:14px;margin-left:auto"></span></td>
                                        <td class="text-center"><span class="skel-line" style="width:30px;height:30px;border-radius:6px;margin:0 auto"></span></td>
                                    </tr>
                                @endfor
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- ======================================================================
             REAL CONTENT — disembunyikan (d-none) sampai data awal siap
        ======================================================================= --}}
        <div id="page-real-content" class="d-none">

            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <div>
                    <h1 class="page-title">Rekap Inventaris</h1>
                    <p class="page-subtitle mb-0">
                        Kelola dan pantau seluruh data pembelian dan aset inventaris.
                        <span class="fw-semibold text-dark">{{ now()->translatedFormat('l, d F Y') }}</span>
                    </p>
                </div>
                <div class="d-flex flex-wrap gap-2 align-items-center justify-content-md-end mt-2 mt-sm-0">
                    <button type="button" class="btn btn-primary px-3" data-bs-toggle="modal" data-bs-target="#modalTambah">
                        <i class="fa-solid fa-plus me-1"></i> Tambah Data
                    </button>
                    <a href="{{ route('HR.rekap_inventaris.sync') }}" class="btn btn-info text-white px-3">
                        <i class="fa-solid fa-rotate me-1"></i> Sinkronisasi
                    </a>
                    <div class="dropdown">
                        <button class="btn btn-success text-white dropdown-toggle px-3" type="button" id="exportDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fa-solid fa-download me-1"></i> Export
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="exportDropdown">
                            <li>
                                <a class="dropdown-item" href="#" id="btn_export">
                                    <i class="fa-solid fa-file-excel text-success me-2"></i> Export Excel
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="#" id="btn_export_pdf">
                                    <i class="fa-solid fa-file-pdf text-danger me-2"></i> Export PDF
                                </a>
                            </li>
                        </ul>
                    </div>
                    <a href="{{ route('IndexInventaris') }}" class="btn btn-secondary px-3">
                        <i class="fa-solid fa-list me-1"></i> Daftar Inventaris
                    </a>
                </div>
            </div>

            <div class="card filter-card mb-4">
                <div class="card-body p-4">
                    <form id="form-filter-rekap" class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Kategori</label>
                            <select class="form-select" id="filter_kategori" name="kategori">
                                <option value="">-- Semua Kategori --</option>
                                @foreach ($kategoris ?? [] as $kategori)
                                    <option value="{{ $kategori }}">{{ $kategori }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Lokasi/Ruangan</label>
                            <select class="form-select" id="filter_lokasi" name="lokasi" disabled>
                                <option value="">-- Pilih Kategori Dulu --</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Jenis Barang</label>
                            <select class="form-select" id="filter_jenis" name="jenis" disabled>
                                <option value="">-- Pilih Lokasi Dulu --</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Tahun</label>
                            <select class="form-select" id="filter_tahun" name="tahun">
                                @for ($y = date('Y'); $y >= 2023; $y--)
                                    <option value="{{ $y }}" {{ $y == ($defaultTahun ?? date('Y')) ? 'selected' : '' }}>
                                        {{ $y }}</option>
                                @endfor
                            </select>
                        </div>

                        <div class="col-md-9">
                            <label class="form-label fw-semibold d-block">Filter Periode</label>
                            <div class="d-flex align-items-center gap-4 mt-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="mode_periode" id="mode_semua"
                                        value="semua" checked>
                                    <label class="form-check-label" for="mode_semua">Seluruh Tahun</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="mode_periode" id="mode_bulan"
                                        value="bulan">
                                    <label class="form-check-label" for="mode_bulan">Bulanan</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="mode_periode" id="mode_quartal"
                                        value="quartal">
                                    <label class="form-check-label" for="mode_quartal">Per 3 Bulan (Quartal)</label>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4 d-none" id="wrapper_bulan">
                            <label class="form-label fw-semibold">Pilih Bulan</label>
                            <select class="form-select" id="filter_bulan" name="bulan">
                                @for ($m = 1; $m <= 12; $m++)
                                    <option value="{{ $m }}">
                                        {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}</option>
                                @endfor
                            </select>
                        </div>

                        <div class="col-md-4 d-none" id="wrapper_quartal">
                            <label class="form-label fw-semibold">Pilih Quartal</label>
                            <select class="form-select" id="filter_quartal" name="quartal">
                                <option value="1">Quartal 1 (Jan - Mar)</option>
                                <option value="2">Quartal 2 (Apr - Jun)</option>
                                <option value="3">Quartal 3 (Jul - Sep)</option>
                                <option value="4">Quartal 4 (Okt - Des)</option>
                            </select>
                        </div>

                        <div class="col-12 text-end mt-3">
                            <button type="button" class="btn btn-primary px-4" id="btn_terapkan_filter">
                                <i class="fa-solid fa-filter me-1"></i> Terapkan Filter
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-body p-0">
                    <ul class="nav nav-tabs px-4 pt-3" id="rekapTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="tab-kategori-btn" data-bs-toggle="tab"
                                data-bs-target="#tab-kategori" type="button">
                                <i class="fa-solid fa-boxes-stacked me-1"></i> Rekap Per Kategori
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="tab-periode-btn" data-bs-toggle="tab" data-bs-target="#tab-periode"
                                type="button">
                                <i class="fa-solid fa-calendar-week me-1"></i> Rekap Per Periode
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="tab-lokasi-btn" data-bs-toggle="tab" data-bs-target="#tab-lokasi" type="button">
                                <i class="fa-solid fa-map-location-dot me-1"></i> Rekap Per Lokasi
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="tab-statistik-btn" data-bs-toggle="tab"
                                data-bs-target="#tab-statistik" type="button">
                                <i class="fa-solid fa-chart-column me-1"></i> Statistik Pembelian
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content p-4" id="rekapTabContent">
                        <div class="tab-pane fade show active" id="tab-kategori" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th width="5%">No</th>
                                            <th>Nama Kategori</th>
                                            <th class="text-center" width="15%">Jumlah Barang</th>
                                            <th class="text-end" width="20%">Total Pembelian</th>
                                            <th class="text-center" width="10%">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tbody_kategori">
                                        <tr>
                                            <td colspan="5" class="text-center py-4 text-muted">Memuat data...</td>
                                        </tr>
                                    </tbody>
                                    <tfoot id="tfoot_kategori" class="d-none">
                                        <tr>
                                            <td colspan="3" class="text-end">TOTAL KESELURUHAN:</td>
                                            <td class="text-end" id="total_kategori">Rp 0</td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="tab-periode" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th width="5%">No</th>
                                            <th>Periode</th>
                                            <th class="text-center" width="15%">Jumlah Barang</th>
                                            <th class="text-end" width="20%">Total Pembelian</th>
                                            <th class="text-center" width="10%">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tbody_periode">
                                        <tr>
                                            <td colspan="5" class="text-center py-4 text-muted">Memuat data...</td>
                                        </tr>
                                    </tbody>
                                    <tfoot id="tfoot_periode" class="d-none">
                                        <tr>
                                            <td colspan="3" class="text-end">TOTAL KESELURUHAN:</td>
                                            <td class="text-end" id="total_periode">Rp 0</td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="tab-lokasi" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th width="5%">No</th>
                                            <th>Lokasi / Ruangan</th>
                                            <th class="text-center" width="15%">Jumlah Barang</th>
                                            <th class="text-end" width="20%">Total Pembelian</th>
                                            <th class="text-center" width="10%">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tbody_lokasi">
                                        <tr>
                                            <td colspan="5" class="text-center py-4 text-muted">Memuat data...</td>
                                        </tr>
                                    </tbody>
                                    <tfoot id="tfoot_lokasi" class="d-none">
                                        <tr>
                                            <td colspan="3" class="text-end">TOTAL KESELURUHAN:</td>
                                            <td class="text-end" id="total_lokasi">Rp 0</td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="tab-statistik" role="tabpanel">
                            <div class="row g-4">
                                <div class="col-lg-5">
                                    <div class="card border-0 shadow-sm h-100">
                                        <div class="card-body">
                                            <h6 class="fw-bold text-secondary mb-4">Tren Pembelian per Kategori</h6>

                                            {{-- Skeleton chart 1 --}}
                                            <div id="chartKategori-skel" style="position: relative; height: 300px;">
                                                <span class="skel-line" style="height:100%;border-radius:8px"></span>
                                            </div>
                                            {{-- Canvas chart 1 (hidden sampai data siap) --}}
                                            <div id="chartKategori-wrap" style="position: relative; height: 300px;" class="d-none">
                                                <canvas id="chartKategori"></canvas>
                                            </div>
                                            <div id="emptyChart1" class="d-none py-5 text-muted text-center">Tidak ada data statistik.</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-7">
                                    <div class="card border-0 shadow-sm h-100">
                                        <div class="card-body">
                                            <h6 class="fw-bold text-secondary mb-4">Perbandingan Total Pembelian per Lokasi</h6>

                                            {{-- Skeleton chart 2 --}}
                                            <div id="chartPerbandingan-skel" style="position: relative; height: 300px;">
                                                <span class="skel-line" style="height:100%;border-radius:8px"></span>
                                            </div>
                                            {{-- Canvas chart 2 (hidden sampai data siap) --}}
                                            <div id="chartPerbandingan-wrap" style="position: relative; height: 300px;" class="d-none">
                                                <canvas id="chartPerbandingan"></canvas>
                                            </div>
                                            <div id="emptyChart2" class="d-none py-5 text-muted text-center">Tidak ada data statistik.</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        {{-- /#page-real-content --}}
    </div>

    <div class="modal fade" id="modalDetail" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalDetailTitle">Detail Inventaris</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">

                    {{-- Skeleton modal detail --}}
                    <div id="modalDetail-skeleton">
                        <table class="table align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>ID Barang</th>
                                    <th>ID Inventaris</th>
                                    <th>Tanggal Beli</th>
                                    <th>No. KK</th>
                                    <th>Nama Barang</th>
                                    <th>Qty</th>
                                    <th>Kategori</th>
                                    <th>Lokasi</th>
                                    <th>Keterangan</th>
                                    <th class="text-end">Total Harga</th>
                                </tr>
                            </thead>
                            <tbody>
                                @for ($i = 0; $i < 5; $i++)
                                    <tr>
                                        @for ($c = 0; $c < 11; $c++)
                                            <td><span class="skel-line" style="width:80%;height:13px"></span></td>
                                        @endfor
                                    </tr>
                                @endfor
                            </tbody>
                        </table>
                    </div>

                    {{-- Konten asli modal detail --}}
                    <div id="modalDetail-real" class="d-none">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0" id="tableModalDetail">
                                <thead class="table-light">
                                    <tr>
                                        <th>No</th>
                                        <th>ID Barang</th>
                                        <th>ID Inventaris</th>
                                        <th>Tanggal Beli</th>
                                        <th>No. KK</th>
                                        <th>Nama Barang</th>
                                        <th>Qty</th>
                                        <th>Kategori</th>
                                        <th>Lokasi</th>
                                        <th>Keterangan</th>
                                        <th class="text-end">Total Harga</th>
                                    </tr>
                                </thead>
                                <tbody id="tbody_modal_detail">
                                    <tr>
                                        <td colspan="11" class="text-center py-4 text-muted">Memuat data...</td>
                                    </tr>
                                </tbody>
                                <tfoot id="tfoot_modal_detail" class="d-none">
                                    <tr>
                                        <td colspan="6" class="text-end fw-bold">TOTAL KESELURUHAN:</td>
                                        <td class="text-center fw-bold" id="total_qty_modal_detail">0</td>
                                        <td colspan="3"></td>
                                        <td class="text-end fw-bold" id="total_modal_detail">Rp 0</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Import Excel -->
    <div class="modal fade" id="modalImport" tabindex="-1" aria-labelledby="modalImportLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalImportLabel">Import Data Rekap Inventaris</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('HR.rekap_inventaris.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="file" class="form-label">Pilih File Excel (.xlsx, .xls, .csv)</label>
                            <input type="file" name="file" id="file" class="form-control" accept=".xlsx,.xls,.csv" required>
                            <small class="text-muted d-block mt-1">
                                Pastikan file memiliki kolom: <strong>Nama Barang</strong> (atau <em>Name</em>) dan <strong>Kategori</strong>.
                            </small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Import Sekarang</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Tambah Data -->
    <div class="modal fade" id="modalTambah" tabindex="-1" aria-labelledby="modalTambahLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTambahLabel">Tambah Data Rekap Inventaris</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('HR.rekap_inventaris.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label fw-bold">Nama Barang <span class="text-danger">*</span></label>
                                <input type="text" name="name" id="name" class="form-control" placeholder="Contoh: Laptop Dell XPS" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="kategori" class="form-label fw-bold">Kategori <span class="text-danger">*</span></label>
                                <select name="kategori" id="kategori" class="form-select" required>
                                    <option value="Office">Office</option>
                                    <option value="Kelas">Kelas</option>
                                    <option value="Education">Education</option>
                                    <option value="Sales/Tim Digital">Sales/Tim Digital</option>
                                    <option value="ITSM">ITSM</option>
                                    <option value="Cicilan Kendaraan">Cicilan Kendaraan</option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="qty" class="form-label fw-bold">Qty (Jumlah) <span class="text-danger">*</span></label>
                                <input type="number" name="qty" id="qty" class="form-control" min="1" value="1" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="total_harga" class="form-label fw-bold">Total Harga (Rp) <span class="text-danger">*</span></label>
                                <input type="number" name="total_harga" id="total_harga" class="form-control" min="0" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="waktu_pembelian" class="form-label fw-bold">Tanggal Pembelian <span class="text-danger">*</span></label>
                                <input type="date" name="waktu_pembelian" id="waktu_pembelian" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="idinventaris" class="form-label fw-bold">ID Inventaris</label>
                                <input type="text" name="idinventaris" id="idinventaris" class="form-control" placeholder="Contoh: INV-10024">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="ruangan" class="form-label fw-bold">Lokasi/Ruangan</label>
                                <input type="text" name="ruangan" id="ruangan" class="form-control" placeholder="Contoh: Ruang IT">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="no_kk" class="form-label fw-bold">No. KK</label>
                                <input type="text" name="no_kk" id="no_kk" class="form-control" placeholder="Contoh: KK-001">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="deskripsi" class="form-label fw-bold">Keterangan</label>
                            <textarea name="deskripsi" id="deskripsi" class="form-control" rows="3" placeholder="Tambahkan deskripsi barang..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan Data</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        $(document).ready(function() {
            let chartPersentase, chartPerbandingan;

            function formatRupiah(angka) {
                if (!angka) return 'Rp 0';
                let reverse = angka.toString().split('').reverse().join('');
                let rupiah = reverse.match(/\d{1,3}/g);
                rupiah = rupiah.join('.').split('').reverse().join('');
                return 'Rp ' + rupiah;
            }

            // Skeleton generik untuk tabel rekap (5 kolom: No, Label, Jumlah, Total, Aksi)
            function renderTableSkeleton(tbodyId, rows = 6) {
                let html = '';
                for (let i = 0; i < rows; i++) {
                    html += `
                        <tr>
                            <td><span class="skel-line" style="width:20px;height:14px"></span></td>
                            <td><span class="skel-line" style="width:70%;height:14px"></span></td>
                            <td class="text-center"><span class="skel-line" style="width:60%;height:14px;margin:0 auto"></span></td>
                            <td class="text-end"><span class="skel-line" style="width:70%;height:14px;margin-left:auto"></span></td>
                            <td class="text-center"><span class="skel-line" style="width:30px;height:30px;border-radius:6px;margin:0 auto"></span></td>
                        </tr>
                    `;
                }
                $(tbodyId).html(html);
            }

            // ===== FULL PAGE SKELETON: tunggu request awal selesai baru tampilkan konten asli =====
            $.when(loadDataRekap()).always(function() {
                $('#page-skeleton').fadeOut(200, function() {
                    $(this).remove();
                });
                $('#page-real-content').removeClass('d-none').hide().fadeIn(250);
            });

            $('#filter_kategori').on('change', function() {
                let kategori = $(this).val();
                $('#filter_lokasi').html('<option value="">-- Memuat... --</option>').prop('disabled', true);
                $('#filter_jenis').html('<option value="">-- Pilih Lokasi Dulu --</option>').prop('disabled', true);

                if (kategori) {
                    $.get('{{ url('HR-dashboard/rekap-inventaris/ajax/lokasi') }}/' + encodeURIComponent(kategori),
                        function(data) {
                            let options = '<option value="">-- Semua Lokasi --</option>';
                            $.each(data, function(key, value) {
                                options += '<option value="' + value + '">' + value + '</option>';
                            });
                            $('#filter_lokasi').html(options).prop('disabled', false);
                        });
                } else {
                    $('#filter_lokasi').html('<option value="">-- Pilih Kategori Dulu --</option>').prop('disabled', true);
                }
            });

            $('#filter_lokasi').on('change', function() {
                let lokasi = $(this).val();
                $('#filter_jenis').html('<option value="">-- Memuat... --</option>').prop('disabled', true);

                if (lokasi) {
                    $.get('{{ url('HR-dashboard/rekap-inventaris/ajax/jenis') }}/' + encodeURIComponent(lokasi),
                        function(data) {
                            let options = '<option value="">-- Semua Jenis Barang --</option>';
                            $.each(data, function(key, value) {
                                options += '<option value="' + key + '">' + value + '</option>';
                            });
                            $('#filter_jenis').html(options).prop('disabled', false);
                        });
                } else {
                    $('#filter_jenis').html('<option value="">-- Pilih Lokasi Dulu --</option>').prop('disabled', true);
                }
            });

            $('input[name="mode_periode"]').on('change', function() {
                let mode = $(this).val();
                $('#wrapper_bulan, #wrapper_quartal').addClass('d-none');
                if (mode === 'bulan') $('#wrapper_bulan').removeClass('d-none');
                else if (mode === 'quartal') $('#wrapper_quartal').removeClass('d-none');
            });

            $('#btn_export_pdf').on('click', function() {
                let formData = $('#form-filter-rekap').serialize();
                let url = '{{ route('HR.rekap_inventaris.export_pdf') }}?' + formData;
                window.open(url);
            });

            $('#btn_export').on('click', function() {
                let formData = $('#form-filter-rekap').serialize();
                let url = '{{ route('HR.rekap_inventaris.export') }}?' + formData;
                window.open(url);
            });

            $('#btn_terapkan_filter').on('click', function() {
                loadDataRekap();
            });

            function loadDataRekap() {
                let formData = $('#form-filter-rekap').serialize();

                renderTableSkeleton('#tbody_kategori');
                renderTableSkeleton('#tbody_periode');
                renderTableSkeleton('#tbody_lokasi');
                $('#tfoot_kategori, #tfoot_periode, #tfoot_lokasi').addClass('d-none');

                // Tampilkan skeleton chart lagi setiap kali filter diterapkan ulang
                $('#chartKategori-wrap, #chartPerbandingan-wrap, #emptyChart1, #emptyChart2').addClass('d-none');
                $('#chartKategori-skel, #chartPerbandingan-skel').removeClass('d-none');

                return $.get('{{ route('HR.rekap_inventaris.load_data') }}?' + formData, function(response) {
                    if (response.success) {
                        renderTab1(response.tab1);
                        renderTab2(response.tab2);
                        renderTabLokasi(response.tabLokasi);
                        renderTab3(response.tab3, response.chart, response.chart_kategori);
                    }
                }).fail(function() {
                    $('#tbody_kategori, #tbody_periode, #tbody_lokasi').html(
                        '<tr><td colspan="5" class="text-center py-4 text-danger">Gagal memuat data.</td></tr>'
                    );
                    $('#chartKategori-skel, #chartPerbandingan-skel').addClass('d-none');
                    $('#emptyChart1, #emptyChart2').removeClass('d-none');
                });
            }

            function renderTab1(data) {
                let html = '';
                let grandTotal = 0;
                if (data.length === 0) {
                    html = '<tr><td colspan="5" class="text-center py-4 text-muted">Tidak ada data untuk filter yang dipilih.</td></tr>';
                } else {
                    $.each(data, function(index, item) {
                        grandTotal += parseFloat(item.total);

                        let params = new URLSearchParams($('#form-filter-rekap').serialize());
                        params.set('tipe', 'kategori');
                        params.set('nilai', item.kategori);
                        let urlDetail = '{{ route('HR.rekap_inventaris.detail_data') }}?' + params.toString();

                        html += `
                            <tr>
                                <td>${index + 1}</td>
                                <td>${item.kategori}</td>
                                <td class="text-center">${item.jumlah_barang} Barang</td>
                                <td class="text-end">${formatRupiah(item.total)}</td>
                                <td class="text-center">
                                    <a href="#" class="btn btn-sm btn-info text-white btn-detail" data-url="${urlDetail}" data-title="Detail Pembelian Kategori ${item.kategori}">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        `;
                    });
                }
                $('#tbody_kategori').html(html);
                $('#total_kategori').text(formatRupiah(grandTotal));
                $('#tfoot_kategori').removeClass('d-none');
            }

            function renderTabLokasi(data) {
                let html = '';
                let grandTotal = 0;
                if (data.length === 0) {
                    html = '<tr><td colspan="5" class="text-center py-4 text-muted">Tidak ada data untuk filter yang dipilih.</td></tr>';
                } else {
                    $.each(data, function(index, item) {
                        grandTotal += parseFloat(item.total);

                        let params = new URLSearchParams($('#form-filter-rekap').serialize());
                        params.set('tipe', 'lokasi');
                        params.set('nilai', item.lokasi);
                        let urlDetail = '{{ route('HR.rekap_inventaris.detail_data') }}?' + params.toString();

                        html += `
                            <tr>
                                <td>${index + 1}</td>
                                <td>${item.lokasi || 'Tidak Ada Lokasi'}</td>
                                <td class="text-center">${item.jumlah_barang} Barang</td>
                                <td class="text-end">${formatRupiah(item.total)}</td>
                                <td class="text-center">
                                    <a href="#" class="btn btn-sm btn-info text-white btn-detail" 
                                    data-url="${urlDetail}" 
                                    data-title="Detail Pembelian Lokasi ${item.lokasi || 'Tidak Ada'}">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        `;
                    });
                }
                $('#tbody_lokasi').html(html);
                $('#total_lokasi').text(formatRupiah(grandTotal));
                $('#tfoot_lokasi').removeClass('d-none');
            }

            function renderTab2(data) {
                let html = '';
                let grandTotal = 0;
                if (data.length === 0) {
                    html = '<tr><td colspan="5" class="text-center py-4 text-muted">Tidak ada data untuk filter yang dipilih.</td></tr>';
                } else {
                    $.each(data, function(index, item) {
                        grandTotal += parseFloat(item.total);

                        let params = new URLSearchParams($('#form-filter-rekap').serialize());
                        params.set('tipe', 'periode');
                        params.set('filter_mode', item.filter_mode);
                        params.set('filter_value', item.filter_value);
                        let urlDetail = '{{ route('HR.rekap_inventaris.detail_data') }}?' + params.toString();

                        html += `
                            <tr>
                                <td>${index + 1}</td>
                                <td>${item.periode}</td>
                                <td class="text-center">${item.jumlah_barang} Barang</td>
                                <td class="text-end">${formatRupiah(item.total)}</td>
                                <td class="text-center">
                                    <a href="#" class="btn btn-sm btn-info text-white btn-detail" data-url="${urlDetail}" data-title="Detail Pembelian Periode ${item.periode}">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        `;
                    });
                }
                $('#tbody_periode').html(html);
                $('#total_periode').text(formatRupiah(grandTotal));
                $('#tfoot_periode').removeClass('d-none');
            }

            function renderTab3(data, chartData, chartKategori) {
                if (chartPersentase) chartPersentase.destroy();
                if (chartPerbandingan) chartPerbandingan.destroy();

                // Data sudah tersedia, sembunyikan skeleton chart
                $('#chartKategori-skel').addClass('d-none');
                $('#chartPerbandingan-skel').addClass('d-none');

                // === CHART BAR PER KATEGORI ===
                if (!chartKategori || chartKategori.labels.length === 0) {
                    $('#chartKategori-wrap').addClass('d-none');
                    $('#emptyChart1').removeClass('d-none');
                } else {
                    $('#chartKategori-wrap').removeClass('d-none');
                    $('#emptyChart1').addClass('d-none');

                    const ctx1 = document.getElementById('chartKategori').getContext('2d');
                    chartPersentase = new Chart(ctx1, {
                        type: 'bar',
                        data: {
                            labels: chartKategori.labels,
                            datasets: [
                                {
                                    label: 'Periode Saat Ini',
                                    data: chartKategori.current,
                                    backgroundColor: '#0d6efd',
                                    borderRadius: 6,
                                    borderSkipped: false
                                },
                                {
                                    label: 'Periode Lalu',
                                    data: chartKategori.previous,
                                    backgroundColor: '#cbd5e1',
                                    borderRadius: 6,
                                    borderSkipped: false
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    grid: { color: '#f1f5f9' },
                                    ticks: {
                                        callback: function(value) {
                                            if (value >= 1000000) return 'Rp ' + (value / 1000000).toFixed(1) + ' Jt';
                                            if (value >= 1000) return 'Rp ' + (value / 1000).toFixed(0) + ' Rb';
                                            return 'Rp ' + value;
                                        }
                                    }
                                },
                                x: {
                                    grid: { display: false },
                                    ticks: { maxRotation: 45, minRotation: 45 }
                                }
                            },
                            plugins: {
                                legend: { position: 'top', align: 'end' },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            let val = context.raw.toLocaleString('id-ID');
                                            return context.dataset.label + ': Rp ' + val;
                                        }
                                    }
                                }
                            }
                        }
                    });
                }

                if (!chartData || chartData.labels.length === 0) {
                    $('#chartPerbandingan-wrap').addClass('d-none');
                    $('#emptyChart2').removeClass('d-none');
                } else {
                    $('#chartPerbandingan-wrap').removeClass('d-none');
                    $('#emptyChart2').addClass('d-none');

                    const ctx2 = document.getElementById('chartPerbandingan').getContext('2d');
                    chartPerbandingan = new Chart(ctx2, {
                        type: 'bar',
                        data: {
                            labels: chartData.labels,
                            datasets: [
                                {
                                    label: 'Periode Saat Ini',
                                    data: chartData.current,
                                    backgroundColor: '#0d6efd',
                                    borderRadius: 6
                                },
                                {
                                    label: 'Periode Lalu',
                                    data: chartData.previous,
                                    backgroundColor: '#cbd5e1',
                                    borderRadius: 6
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    grid: { color: '#f1f5f9' },
                                    ticks: {
                                        callback: function(value) {
                                            if (value >= 1000000) return 'Rp ' + (value / 1000000).toFixed(1) + ' Jt';
                                            if (value >= 1000) return 'Rp ' + (value / 1000).toFixed(0) + ' Rb';
                                            return 'Rp ' + value;
                                        }
                                    }
                                },
                                x: { grid: { display: false } }
                            },
                            plugins: {
                                legend: { position: 'top', align: 'end' },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            let val = context.raw.toLocaleString('id-ID');
                                            return context.dataset.label + ': Rp ' + val;
                                        }
                                    }
                                }
                            }
                        }
                    });
                }
            }
        });

    $(document).on('click', '.btn-detail', function(e) {
        e.preventDefault();
        let url = $(this).data('url');
        let title = $(this).data('title');

        function formatRupiah(angka) {
            if (!angka) return 'Rp 0';
            let reverse = angka.toString().split('').reverse().join('');
            let rupiah = reverse.match(/\d{1,3}/g);
            rupiah = rupiah.join('.').split('').reverse().join('');
            return 'Rp ' + rupiah;
        }

        $('#modalDetailTitle').text(title);
        $('#tfoot_modal_detail').addClass('d-none');

        if ($.fn.DataTable.isDataTable('#tableModalDetail')) {
            $('#tableModalDetail').DataTable().destroy();
        }

        // Tampilkan skeleton, sembunyikan konten asli sebelum data datang
        $('#modalDetail-real').addClass('d-none');
        $('#modalDetail-skeleton').removeClass('d-none');

        let modal = new bootstrap.Modal(document.getElementById('modalDetail'));
        modal.show();

        $.get(url, function(response) {
            $('#modalDetail-skeleton').addClass('d-none');
            $('#modalDetail-real').removeClass('d-none');

            if(response.success) {
                let grandTotal = 0;
                let tableData = [];
                
                if(response.data.length === 0) {
                    $('#tbody_modal_detail').html('<tr><td colspan="11" class="text-center py-4 text-muted">Tidak ada data.</td></tr>');
                } else {
                    let totalQty = 0;
                    $.each(response.data, function(index, item) {
                        grandTotal += parseFloat(item.total);
                        totalQty += parseInt(item.qty || 1);
                        tableData.push([
                            index + 1,
                            item.idbarang || '-',
                            item.idinventaris || '-',
                            item.tanggal,
                            item.no_kk,
                            item.nama_barang,
                            item.qty || 1,
                            item.kategori,
                            item.lokasi,
                            item.keterangan,
                            formatRupiah(item.total)
                        ]);
                    });

                    $('#tbody_modal_detail').empty();
                    $('#tfoot_modal_detail').removeClass('d-none');
                    $('#total_qty_modal_detail').text(totalQty);
                    $('#total_modal_detail').text(formatRupiah(grandTotal));

                    $('#tableModalDetail').DataTable({
                        data: tableData,
                        destroy: true,
                        responsive: true,
                        language: {
                            search: "Cari:",
                            lengthMenu: "Tampilkan _MENU_ data",
                            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                            infoEmpty: "Tidak ada data",
                            infoFiltered: "(difilter dari _MAX_ total data)",
                            paginate: {
                                first: "Pertama",
                                last: "Terakhir",
                                next: "<i class='fa-solid fa-angle-right'></i>",
                                previous: "<i class='fa-solid fa-angle-left'></i>"
                            }
                        },
                        columnDefs: [
                            { targets: 10, className: 'text-end' },
                            { targets: [0, 1, 2, 4, 6], className: 'text-center' }
                        ],
                        order: [[0, 'asc']],
                        pageLength: 10,
                        lengthMenu: [10, 25, 50, 100]
                    });
                }
            }
        }).fail(function() {
            $('#modalDetail-skeleton').addClass('d-none');
            $('#modalDetail-real').removeClass('d-none');
            $('#tbody_modal_detail').html('<tr><td colspan="11" class="text-center py-4 text-danger">Gagal memuat data.</td></tr>');
        });
    });
    </script>
@endsection