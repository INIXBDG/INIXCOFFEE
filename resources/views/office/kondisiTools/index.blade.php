@extends('layouts_office.app')

@section('office_contents')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

    {{-- Alert Success --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    {{-- Alert error --}}
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @php
        $auth = auth()->user()->karyawan->jabatan;
    @endphp
    <input type="hidden" name="authUser" id="authUser" value="{{ $auth }}">

    {{-- Modal Tambah --}}
    <div class="modal fade" id="createToolModal">
        <div class="modal-dialog">
            <form
                method="POST"
                action="{{ route('office.KondisiTools.store') }}"
                class="modal-content">

                @csrf

                <input type="hidden" name="tipe" value="tool">

                <div class="modal-header">
                    <h5>Tambah Alat</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <div class="mb-3">
                        <label class="form-label">
                            Nama Alat <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                            name="nama_alat"
                            class="form-control"
                            required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">
                            Kategori <span class="text-danger">*</span>
                        </label>
                        <select
                            name="kategori"
                            id="kategori"
                            class="form-control"
                            required>
                            <option value="" disabled selected hidden>Pilih Kategori</option>
                            <option value="Elektronik">Elektronik</option>
                            <option value="Mekanik">Mekanik</option>
                            <option value="Listrik">Listrik</option>
                            <option value="Pertukangan">Pertukangan</option>
                            <option value="Kebersihan">Kebersihan</option>
                            <option value="Keselamatan">Keselamatan Kerja</option>
                            <option value="Lainnya">Lainnya (Isi Manual)</option>
                        </select>

                        {{-- Input untuk kategori lainnya --}}
                        <input type="text"
                            name="kategori_lainnya"
                            id="kategori_lainnya"
                            class="form-control mt-2 d-none"
                            placeholder="Masukkan kategori custom..."
                            maxlength="50">
                        <small class="form-text text-muted" id="hint_kategori_lainnya" style="display:none;">
                            <i class="fa fa-info-circle"></i> Masukkan nama kategori yang tidak ada di daftar
                        </small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">
                            Qty <span class="text-danger">*</span>
                        </label>
                        <input type="number"
                            name="qty"
                            class="form-control"
                            min="1"
                            required>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        Simpan
                    </button>
                </div>

            </form>
        </div>
    </div>
    <div class="modal fade" id="createKondisiModal">
        <div class="modal-dialog">
            <form
                method="POST"
                action="{{ route('office.KondisiTools.store') }}"
                class="modal-content">

                @csrf

                <input type="hidden" name="tipe" value="pengecekan">

                <div class="modal-header">
                    <h5>Tambah Pemeriksaan</h5>
                </div>

                <div class="modal-body">

                    <div class="mb-3">
                        <label class="form-label">
                            Alat <span class="text-danger">*</span>
                        </label>

                        <select
                            name="id_alat"
                            class="form-control "
                            required>
                            <option value="" disabled selected hidden>Pilih Alat</option>
                            @foreach($tools as $tool)
                                <option value="{{ $tool->id }}">{{ $tool->nama_alat }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">
                            Kondisi <span class="text-danger">*</span>
                        </label>

                        <select
                            name="kondisi"
                            class="form-control"
                            required>
                            <option value="" disabled selected hidden>Pilih Kondisi</option>
                            <option value="Baik">Baik</option>
                            <option value="Rusak Ringan">Rusak Ringan</option>
                            <option value="Rusak Berat">Rusak Berat</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">
                            Tanggal Pemeriksaan <span class="text-danger">*</span>
                        </label>

                        <input
                            type="date"
                            name="tanggal_pemeriksaan"
                            class="form-control"
                            required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">
                            Catatan
                        </label>

                        <textarea
                            name="catatan"
                            class="form-control"
                            rows="3"></textarea>
                    </div>

                </div>

                <div class="modal-footer">
                    <button class="btn btn-success">
                        Simpan
                    </button>
                </div>

            </form>
        </div>
    </div>

    {{-- Modal Edit --}}
    <div class="modal fade" id="editToolModal">
        <div class="modal-dialog">
            <form id="editToolForm" method="POST" class="modal-content">
                @csrf
                @method('POST')

                <div class="modal-header">
                    <h5>Edit Alat</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <div class="mb-3">
                        <label class="form-label">
                            Nama Alat <span class="text-danger">*</span>
                        </label>
                        <input
                            type="text"
                            name="nama_alat"
                            id="edit_nama_alat"
                            class="form-control"
                            required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">
                            Kategori <span class="text-danger">*</span>
                        </label>
                        <select
                            name="kategori"
                            id="edit_kategori"
                            class="form-control"
                            required>
                            <option value="" disabled hidden>Pilih Kategori</option>
                            <option value="Elektronik">Elektronik</option>
                            <option value="Mekanik">Mekanik</option>
                            <option value="Listrik">Listrik</option>
                            <option value="Pertukangan">Pertukangan</option>
                            <option value="Kebersihan">Kebersihan</option>
                            <option value="Keselamatan">Keselamatan Kerja</option>
                            <option value="Lainnya">Lainnya (Isi Manual)</option>
                        </select>

                        {{-- Input untuk kategori lainnya --}}
                        <input type="text"
                            name="kategori_lainnya"
                            id="edit_kategori_lainnya"
                            class="form-control mt-2 d-none"
                            placeholder="Masukkan kategori custom..."
                            maxlength="50">
                        <small class="form-text text-muted" id="edit_hint_kategori_lainnya" style="display:none;">
                            <i class="fa fa-info-circle"></i> Masukkan nama kategori yang tidak ada di daftar
                        </small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">
                            Qty <span class="text-danger">*</span>
                        </label>
                        <input
                            type="number"
                            name="qty"
                            id="edit_qty"
                            class="form-control"
                            min="1"
                            required>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        Update
                    </button>
                </div>
            </form>
        </div>
    </div>
    <div class="modal fade" id="editKondisiModal">
        <div class="modal-dialog">
            <form id="editKondisiForm" method="POST" class="modal-content">

                @csrf

                <div class="modal-header">
                    <h5>Edit Pemeriksaan</h5>
                </div>

                <div class="modal-body">

                    <div class="mb-3">
                        <label class="form-label">
                            Alat <span class="text-danger">*</span>
                        </label>

                        <select
                            name="id_alat"
                            id="edit_id_alat"
                            class="form-control"
                            required>
                            <option value="" disabled hidden>Pilih Alat</option>
                            @foreach($tools as $tool)
                                <option value="{{ $tool->id }}">{{ $tool->nama_alat }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">
                            Kondisi <span class="text-danger">*</span>
                        </label>

                        <select
                            name="kondisi"
                            id="edit_kondisi"
                            class="form-control"
                            required>
                            <option value="" disabled hidden>Pilih Kondisi</option>
                            <option value="Baik">Baik</option>
                            <option value="Rusak Ringan">Rusak Ringan</option>
                            <option value="Rusak Berat">Rusak Berat</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">
                            Tanggal Pemeriksaan <span class="text-danger">*</span>
                        </label>

                        <input
                            type="date"
                            name="tanggal_pemeriksaan"
                            id="edit_tanggal_pemeriksaan"
                            class="form-control"
                            required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">
                            Catatan
                        </label>

                        <textarea
                            name="catatan"
                            id="edit_catatan"
                            class="form-control"
                            rows="3"></textarea>
                    </div>

                </div>

                <div class="modal-footer">
                    <button class="btn btn-success">
                        Update
                    </button>
                </div>

            </form>
        </div>
    </div>

    {{-- Modal Detail --}}
    <div class="modal fade" id="detailToolModal">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">

                <div class="modal-header">
                    <h5>Detail Alat</h5>
                </div>

                <div class="modal-body">

                    <div id="toolInfo"></div>

                    <hr>

                    <h6>Riwayat Pemeriksaan</h6>

                    <table class="table">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Kondisi</th>
                                <th>Catatan</th>
                            </tr>
                        </thead>

                        <tbody id="detailKondisiBody"></tbody>
                    </table>

                </div>

            </div>
        </div>
    </div>

    {{-- Modal Import Alat --}}
    <div class="modal fade" id="modalImport" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form id="formImport" method="POST" action="{{ route('office.KondisiTools.importAlat') }}" enctype="multipart/form-data">
                    @csrf
                    @method('POST')
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold">
                            Import Alat
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">File Excel</label>
                            <input type="file" name="file" class="form-control" accept=".xlsx,.xls,.csv"
                                required>
                            <div class="form-text">Maksimal 10MB. Format: XLSX, XLS, atau CSV</div>
                        </div>

                        <div id="importPreview" class="d-none">
                            <div class="border rounded p-2 bg-light small">
                                <strong>📄 File terpilih:</strong>
                                <ul id="previewList" class="mb-0 ps-3 mt-1"></ul>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm"
                            data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary btn-sm" id="btnSubmitImport">
                            <span class="spinner-border spinner-border-sm d-none" id="importSpinner"></span>
                            <span id="importBtnText">Import Data</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Export dengan Filter --}}
    <div class="modal fade" id="modalExport" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form id="formExportFilter" method="GET" action="">
                    <div class="modal-header text-white">
                        <h5 class="modal-title fw-bold">
                            <i class="bx bx-filter-alt me-2"></i>
                            Export dengan Filter
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Tipe Laporan <span class="text-danger">*</span>
                            </label>
                            <select name="report_type" id="exportReportType" class="form-select" required>
                                <option value="alat">Data Alat</option>
                                <option value="pemeriksaan" selected>Riwayat Pemeriksaan</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Format Export <span class="text-danger">*</span>
                            </label>
                            <select name="export_format" id="exportFormat" class="form-select" required>
                                <option value="excel">Excel (.xlsx)</option>
                                <option value="pdf">PDF (.pdf)</option>
                            </select>
                        </div>

                        <div id="filterPemeriksaan">
                            <hr>
                            <h6 class="fw-bold mb-3">
                                <i class="bx bx-filter me-1"></i> Filter Riwayat Pemeriksaan
                            </h6>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold small">
                                        Tanggal Mulai
                                    </label>
                                    <input type="date" 
                                        name="tanggal_mulai" 
                                        id="export_tanggal_mulai"
                                        class="form-control">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold small">
                                        Tanggal Selesai
                                    </label>
                                    <input type="date" 
                                        name="tanggal_selesai" 
                                        id="export_tanggal_selesai"
                                        class="form-control">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold small">Preset Jangka Waktu</label>
                                <div class="d-flex flex-wrap gap-2">
                                    <button type="button" class="btn btn-outline-secondary btn-sm preset-date" 
                                        data-days="7">7 Hari</button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm preset-date" 
                                        data-days="30">30 Hari</button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm preset-date" 
                                        data-days="90">3 Bulan</button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm preset-date" 
                                        data-days="365">1 Tahun</button>
                                    <button type="button" class="btn btn-outline-danger btn-sm preset-date" 
                                        data-days="0">Reset</button>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold small">Kondisi Alat</label>
                                <select name="kondisi" class="form-select">
                                    <option value="">Semua Kondisi</option>
                                    <option value="Baik">Baik</option>
                                    <option value="Rusak Ringan">Rusak Ringan</option>
                                    <option value="Rusak Berat">Rusak Berat</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold small">Pilih Alat (Opsional)</label>
                                <select name="id_alat" class="form-select">
                                    <option value="">Semua Alat</option>
                                    @foreach($tools as $tool)
                                        <option value="{{ $tool->id }}">
                                            {{ $tool->nama_alat }} ({{ $tool->kategori }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div id="filterAlat" class="d-none">
                            <hr>
                            <h6 class="fw-bold mb-3">
                                <i class="bx bx-filter me-1"></i> Filter Data Alat
                            </h6>

                            <div class="mb-3">
                                <label class="form-label fw-semibold small">Kategori Alat</label>
                                <select name="kategori" class="form-select">
                                    <option value="">Semua Kategori</option>
                                    @foreach ($kategoriTools as $kategori)
                                        <option value="{{ $kategori }}">{{ $kategori }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="alert alert-info small mt-3 mb-0">
                            <i class="bx bx-info-circle me-1"></i>
                            <strong>Info:</strong> Filter bersifat opsional. Kosongkan jika ingin export semua data.
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
                            Batal
                        </button>
                        <button type="submit" class="btn btn-success btn-sm">
                            <i class="bx bx-download me-1"></i>
                            <span id="exportBtnText">Export</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center">
        <h4 class="fw-bold text-dark">Kondisi Tools</h4>
        <small class="text-muted fw-medium">{{ now()->translatedFormat('l, d F Y') }}</small>
    </div>

    <div class="card shadow-lg border-0 rounded-4 glass-force">
        <div class="card-header">
            <ul class="nav nav-tabs" id="toolTabs" role="tablist">
                <li class="nav-item">
                    <button
                        class="nav-link active"
                        data-bs-toggle="tab"
                        data-bs-target="#tabTools"
                        type="button">
                        Data Alat
                    </button>
                </li>

                <li class="nav-item">
                    <button
                        class="nav-link"
                        data-bs-toggle="tab"
                        data-bs-target="#tabKondisi"
                        type="button">
                        Riwayat Pemeriksaan
                    </button>
                </li>
            </ul>
        </div>

        <div class="card-body">

            <div class="tab-content">
                <div class="tab-pane fade show active" id="tabTools">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">Data Alat</h5>
                        <div class="d-flex gap-3">
                            <button
                                class="btn btn-primary"
                                data-bs-toggle="modal"
                                data-bs-target="#createToolModal">
                                <i class="fa fa-plus"></i>
                                Tambah Alat
                            </button>
                            <div class="btn-group">
                                <button class="btn btn-outline-primary px-3 shadow-sm d-flex align-items-center gap-2" type="button"
                                    data-bs-toggle="dropdown">
                                    <i class="bx bx-arrow-to-bottom"></i> Import
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#modalImport">
                                            <i class="bx bx-upload me-2"></i> Import Alat
                                        </a></li>
                                    <li><a class="dropdown-item" href="{{ asset('templates/alat_template.xlsx') }}" download>
                                            <i class="bx bx-download me-2"></i> Download Template
                                        </a></li>
                                </ul>
                            </div>
                            <div class="btn-group">
                                <button class="btn btn-outline-success shadow-sm d-flex align-items-center gap-2" type="button"
                                    data-bs-toggle="dropdown">
                                    <i class="bx bx-arrow-to-top"></i> Export
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><h6 class="dropdown-header">Export Cepat</h6></li>
                                    <li><a class="dropdown-item" href="{{ route('office.KondisiTools.exportExcel', ['report_type' => 'alat']) }}">
                                        <i class="bx bx-file text-success me-2"></i> Excel - Alat (Semua)
                                    </a></li>
                                    <li><a class="dropdown-item" href="{{ route('office.KondisiTools.exportPdf', ['report_type' => 'alat']) }}">
                                        <i class="bx bx-file text-danger me-2"></i> PDF - Alat (Semua)
                                    </a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#modalExport">
                                        <i class="bx bx-cog me-2"></i> Export dengan Filter
                                    </a></li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table id="toolsTable" class="table table-bordered table-striped w-100">
                            <thead>
                                <tr>
                                    <th width="60">No</th>
                                    <th>Nama Alat</th>
                                    <th>Kategori</th>
                                    <th width="100">Qty</th>
                                    <th width="250">Aksi</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>

                <div class="tab-pane fade" id="tabKondisi">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">Riwayat Pemeriksaan</h5>
                        <div class="d-flex gap-3">
                            <button
                                class="btn btn-success"
                                data-bs-toggle="modal"
                                data-bs-target="#createKondisiModal">
                                <i class="fa fa-plus"></i>
                                Tambah Pemeriksaan
                            </button>
                            <div class="btn-group">
                                <button class="btn btn-outline-success shadow-sm d-flex align-items-center gap-2" type="button"
                                    data-bs-toggle="dropdown">
                                    <i class="bx bx-arrow-to-top"></i> Export
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><h6 class="dropdown-header">Export Cepat</h6></li>
                                    <li><a class="dropdown-item" href="{{ route('office.KondisiTools.exportExcel', ['report_type' => 'pemeriksaan']) }}">
                                        <i class="bx bx-file text-success me-2"></i> Excel - Riwayat (Semua)
                                    </a></li>
                                    <li><a class="dropdown-item" href="{{ route('office.KondisiTools.exportPdf', ['report_type' => 'pemeriksaan']) }}">
                                        <i class="bx bx-file text-danger me-2"></i> PDF - Riwayat (Semua)
                                    </a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#modalExport">
                                        <i class="bx bx-cog me-2"></i> Export dengan Filter
                                    </a></li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <form id="formFilterPemeriksaan" class="row g-2 align-items-end">
                            <div class="col-md-3">
                                <label class="form-label small fw-semibold mb-1">
                                    Tanggal Mulai
                                </label>
                                <input type="date" 
                                    id="filter_tanggal_mulai" 
                                    class="form-control form-control-sm">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-semibold mb-1">
                                    Tanggal Selesai
                                </label>
                                <input type="date" 
                                    id="filter_tanggal_selesai" 
                                    class="form-control form-control-sm">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-semibold mb-1">
                                    Preset Cepat
                                </label>
                                <select id="preset_filter" class="form-select form-select-sm">
                                    <option value="today">Hari Ini</option>
                                    <option value="yesterday">Kemarin</option>
                                    <option value="7days">7 Hari Terakhir</option>
                                    <option value="30days">30 Hari Terakhir</option>
                                    <option value="this_month">Bulan Ini</option>
                                    <option value="last_month">Bulan Lalu</option>
                                    <option value="this_year">Tahun Ini</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <div class="d-flex gap-2">
                                    <button type="button" id="btnTerapkanFilter" class="btn btn-primary btn-sm flex-fill">
                                        <i class="fa fa-filter me-1"></i> Terapkan
                                    </button>
                                    <button type="button" id="btnResetFilter" class="btn btn-outline-secondary btn-sm">
                                        <i class="fa fa-undo me-1"></i> Reset
                                    </button>
                                </div>
                            </div>
                        </form>

                        <div id="infoFilterAktif" class="mt-2 d-none">
                            <small class="text-muted">
                                <i class="fa fa-info-circle"></i> 
                                Menampilkan data: <strong id="teksFilterAktif"></strong>
                            </small>
                        </div>
                    </div> 

                    <div class="table-responsive">
                        <table id="kondisiTable" class="table table-bordered table-striped w-100">
                            <thead>
                                <tr>
                                    <th width="60">No</th>
                                    <th width="120">Tanggal</th>
                                    <th>Alat</th>
                                    <th width="150">Kondisi</th>
                                    <th>Catatan</th>
                                    <th width="250">Aksi</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).on('click', '.detailToolBtn', function () {
            let tool = $(this).data('tool');

            $('#toolInfo').html(`
                <div class="row">
                    <div class="col-md-4">
                        <b>Nama Alat</b><br>
                        ${tool.nama_alat}
                    </div>

                    <div class="col-md-4">
                        <b>Kategori</b><br>
                        ${tool.kategori}
                    </div>

                    <div class="col-md-4">
                        <b>Qty</b><br>
                        ${tool.qty}
                    </div>
                </div>
            `);

            let rows = '';

            if(tool.kondisi_tools && tool.kondisi_tools.length > 0){

                tool.kondisi_tools.forEach(item => {

                    rows += `
                        <tr>
                            <td>${item.tanggal_pemeriksaan}</td>
                            <td>${item.kondisi}</td>
                            <td>${item.catatan ?? '-'}</td>
                        </tr>
                    `;
                });

            } else {

                rows = `
                    <tr>
                        <td colspan="3" class="text-center">
                            Belum ada riwayat pemeriksaan
                        </td>
                    </tr>
                `;
            }

            $('#detailKondisiBody').html(rows);

            let modal = new bootstrap.Modal(
                document.getElementById('detailToolModal')
            );

            modal.show();
        });

        $('#kategori').on('change', function() {
            let value = $(this).val();
            
            if (value === 'Lainnya') {
                $('#kategori_lainnya')
                    .removeClass('d-none')
                    .attr('required', true)
                    .focus();
                $('#hint_kategori_lainnya').show();
            } else {
                $('#kategori_lainnya')
                    .addClass('d-none')
                    .attr('required', false)
                    .val('');
                $('#hint_kategori_lainnya').hide();
            }
        });

        $('#edit_kategori').on('change', function() {
            let value = $(this).val();
            
            if (value === 'Lainnya') {
                $('#edit_kategori_lainnya')
                    .removeClass('d-none')
                    .attr('required', true)
                    .focus();
                $('#edit_hint_kategori_lainnya').show();
            } else {
                $('#edit_kategori_lainnya')
                    .addClass('d-none')
                    .attr('required', false)
                    .val('');
                $('#edit_hint_kategori_lainnya').hide();
            }
        });

        $(document).on('click', '.editToolBtn', function () {
            let id = $(this).data('id');
            let nama = $(this).data('nama');
            let kategori = $(this).data('kategori');
            let qty = $(this).data('qty');

            $('#edit_nama_alat').val(nama);
            $('#edit_qty').val(qty);

            let kategoriList = [
                'Elektronik', 
                'Mekanik', 
                'Listrik', 
                'Pertukangan', 
                'Kebersihan', 
                'Keselamatan',
                'Lainnya'
            ];

            if (kategoriList.includes(kategori)) {
                $('#edit_kategori').val(kategori);
                $('#edit_kategori_lainnya')
                    .addClass('d-none')
                    .attr('required', false)
                    .val('');
                $('#edit_hint_kategori_lainnya').hide();
            } else {
                $('#edit_kategori').val('Lainnya');
                $('#edit_kategori_lainnya')
                    .removeClass('d-none')
                    .attr('required', true)
                    .val(kategori);
                $('#edit_hint_kategori_lainnya').show();
            }

            $('#editToolForm').attr(
                'action',
                `/office/kondisi-tools/update/${id}?tipe=tool`
            );

            new bootstrap.Modal(
                document.getElementById('editToolModal')
            ).show();
        });

        $('#createToolModal').on('hidden.bs.modal', function () {
            $('#kategori').val('');
            $('#kategori_lainnya')
                .addClass('d-none')
                .attr('required', false)
                .val('');
            $('#hint_kategori_lainnya').hide();
        });

        $(document).on('click', '.editKondisiBtn', function () {
            let id = $(this).data('id');

            $('#edit_id_alat').val($(this).data('idalat')).trigger('change');
            $('#edit_kondisi').val($(this).data('kondisi'));
            $('#edit_tanggal_pemeriksaan').val($(this).data('tanggal'));
            $('#edit_catatan').val($(this).data('catatan'));

            $('#editKondisiForm').attr(
                'action',
                `/office/kondisi-tools/update/${id}?tipe=pengecekan`
            );

            new bootstrap.Modal(
                document.getElementById('editKondisiModal')
            ).show();
        });

        $(document).on('click', '.deleteToolBtn', function () {
            let id = $(this).data('id');
            
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Data alat ini akan dihapus permanen!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {

                if (!result.isConfirmed) return;

                $.ajax({
                    url: `/office/kondisi-tools/delete/${id}?tipe=tool`,
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },

                    beforeSend: function() {
                        Swal.fire({
                            title: 'Menghapus...',
                            text: 'Mohon tunggu',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });
                    },

                    success: function(res) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: res.message ?? 'Data berhasil dihapus'
                        });

                        location.reload();
                    },

                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: xhr.responseJSON?.message ?? 'Terjadi kesalahan'
                        });
                    }
                });
            });
        });

        $(document).on('click', '.deleteKondisiBtn', function () {
            let id = $(this).data('id');
            
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Data pemeriksaan ini akan dihapus permanen!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {

                if (!result.isConfirmed) return;

                $.ajax({
                    url: `/office/kondisi-tools/delete/${id}?tipe=pengecekan`,
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },

                    beforeSend: function() {
                        Swal.fire({
                            title: 'Menghapus...',
                            text: 'Mohon tunggu',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });
                    },

                    success: function(res) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: res.message ?? 'Data berhasil dihapus'
                        });

                        location.reload();
                    },

                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: xhr.responseJSON?.message ?? 'Terjadi kesalahan'
                        });
                    }
                });
            });
        });

        $(document).ready(function () {

            let toolsTable = $('#toolsTable').DataTable({
                processing: true,
                serverSide: true,
                info: true,
                language: {
                    processing: '<small class="text-muted">Loading...</small>',
                    emptyTable: "Belum ada data",
                    paginate: {
                        first: "Pertama",
                        last: "Terakhir",
                        next: "Selanjutnya",
                        previous: "Sebelumnya"
                    }
                },
                ajax: "{{ route('office.KondisiTools.get-tools') }}",
                drawCallback: function(settings) {
                    var api = this.api();

                    api.column(0, {
                        page: 'current'
                    }).nodes().each(function(cell, i) {
                        cell.innerHTML = api.page.info().start + i + 1;
                    });
                },
                columns: [
                    { data: null, orderable:false, searchable:false },
                    { data: 'nama_alat' },
                    { data: 'kategori' },
                    { data: 'qty' },
                    {
                        data: null,
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row) {
                            let namaAlat = row.nama_alat.replace(/'/g, "\\'").replace(/"/g, '&quot;');
                            let kategori = row.kategori.replace(/'/g, "\\'").replace(/"/g, '&quot;');
                            
                            return `
                                <div class="d-flex flex-wrap gap-2">
                                    <button
                                        class="btn btn-success btn-sm detailToolBtn"
                                        data-tool='${JSON.stringify(row)}'>
                                        Detail
                                    </button>
                                    <button
                                        class="btn btn-primary btn-sm editToolBtn"
                                        data-id="${row.id}"
                                        data-nama="${namaAlat}"
                                        data-kategori="${kategori}"
                                        data-qty="${row.qty}">
                                        Edit
                                    </button>
                                    <button
                                        class="btn btn-danger btn-sm deleteToolBtn"
                                        data-id="${row.id}">
                                        <i class="fas fa-trash me-2"></i>
                                        Hapus
                                    </button>
                                </div>
                            `;
                        }
                    }
                ]
            });

            function formatDateYMD(date) {
                let year = date.getFullYear();
                let month = String(date.getMonth() + 1).padStart(2, '0');
                let day = String(date.getDate()).padStart(2, '0');
                return `${year}-${month}-${day}`;
            }

            function formatDateDMY(dateStr) {
                if (!dateStr) return '-';
                let d = new Date(dateStr);
                return String(d.getDate()).padStart(2, '0') + '/' + 
                    String(d.getMonth() + 1).padStart(2, '0') + '/' + 
                    d.getFullYear();
            }

            $(document).ready(function() {
                let today = formatDateYMD(new Date());
                $('#filter_tanggal_mulai').val(today);
                $('#filter_tanggal_selesai').val(today);
                updateInfoFilter();
            });

            let kondisiTable = $('#kondisiTable').DataTable({
                processing: true,
                serverSide: true,
                info: true,
                ajax: {
                    url: "{{ route('office.KondisiTools.get-pemeriksaan') }}",
                    type: "GET",
                    data: function(d) {
                        d.tanggal_mulai = $('#filter_tanggal_mulai').val();
                        d.tanggal_selesai = $('#filter_tanggal_selesai').val();
                    }
                },
                language: {
                    processing: '<small class="text-muted">Loading...</small>',
                    emptyTable: "Tidak ada data pada rentang tanggal yang dipilih",
                    paginate: {
                        first: "Pertama",
                        last: "Terakhir",
                        next: "Selanjutnya",
                        previous: "Sebelumnya"
                    }
                },
                drawCallback: function(settings) {
                    var api = this.api();
                    api.column(0, { page: 'current' }).nodes().each(function(cell, i) {
                        cell.innerHTML = api.page.info().start + i + 1;
                    });
                },
                columns: [
                    { data: null, orderable: false, searchable: false },
                    { 
                        data: 'tanggal_pemeriksaan',
                        render: function(data) {
                            return formatDateDMY(data);
                        }
                    },
                    { data: 'nama_alat' },
                    { 
                        data: 'kondisi',
                        render: function(data) {
                            let badgeClass = 'bg-secondary';
                            if (data === 'Baik') badgeClass = 'bg-success';
                            else if (data === 'Rusak Ringan') badgeClass = 'bg-warning text-dark';
                            else if (data === 'Rusak Berat') badgeClass = 'bg-danger';
                            return `<span class="badge ${badgeClass}">${data}</span>`;
                        }
                    },
                    { data: 'catatan' },
                    {
                        data: null,
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row) {
                            return `
                                <div class="d-flex flex-wrap gap-2">
                                    <button
                                        class="btn btn-primary btn-sm editKondisiBtn"
                                        data-id="${row.id}"
                                        data-idalat="${row.id_alat}"
                                        data-kondisi="${row.kondisi}"
                                        data-tanggal="${row.tanggal_pemeriksaan}"
                                        data-catatan="${row.catatan ?? ''}">
                                        Edit
                                    </button>
                                    <button
                                        class="btn btn-danger btn-sm deleteKondisiBtn"
                                        data-id="${row.id}">
                                        <i class="fas fa-trash me-2"></i> Hapus
                                    </button>
                                </div>
                            `;
                        }
                    }
                ]
            });

            $('#btnTerapkanFilter').on('click', function() {
                let tglMulai = $('#filter_tanggal_mulai').val();
                let tglSelesai = $('#filter_tanggal_selesai').val();

                if (tglMulai && tglSelesai && new Date(tglMulai) > new Date(tglSelesai)) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Tanggal Tidak Valid',
                        text: 'Tanggal mulai tidak boleh lebih besar dari tanggal selesai!',
                        confirmButtonColor: '#3085d6'
                    });
                    return;
                }

                updateInfoFilter();
                kondisiTable.ajax.reload(null, false);
            });

            $('#btnResetFilter').on('click', function() {
                let today = formatDateYMD(new Date());
                $('#filter_tanggal_mulai').val(today);
                $('#filter_tanggal_selesai').val(today);
                $('#preset_filter').val('today');
                updateInfoFilter();
                kondisiTable.ajax.reload(null, true);
            });

            $('#preset_filter').on('change', function() {
                let preset = $(this).val();
                if (!preset) return;

                let today = new Date();
                let startDate, endDate;
                endDate = new Date(today);

                switch(preset) {
                    case 'today':
                        startDate = new Date(today);
                        break;
                    case 'yesterday':
                        startDate = new Date(today);
                        startDate.setDate(startDate.getDate() - 1);
                        endDate = new Date(startDate);
                        break;
                    case '7days':
                        startDate = new Date(today);
                        startDate.setDate(startDate.getDate() - 6);
                        break;
                    case '30days':
                        startDate = new Date(today);
                        startDate.setDate(startDate.getDate() - 29);
                        break;
                    case 'this_month':
                        startDate = new Date(today.getFullYear(), today.getMonth(), 1);
                        break;
                    case 'last_month':
                        startDate = new Date(today.getFullYear(), today.getMonth() - 1, 1);
                        endDate = new Date(today.getFullYear(), today.getMonth(), 0);
                        break;
                    case 'this_year':
                        startDate = new Date(today.getFullYear(), 0, 1);
                        break;
                }

                $('#filter_tanggal_mulai').val(formatDateYMD(startDate));
                $('#filter_tanggal_selesai').val(formatDateYMD(endDate));
                updateInfoFilter();
                kondisiTable.ajax.reload(null, true);
            });

            function updateInfoFilter() {
                let tglMulai = $('#filter_tanggal_mulai').val();
                let tglSelesai = $('#filter_tanggal_selesai').val();
                let teks = '';

                if (tglMulai && tglSelesai) {
                    if (tglMulai === tglSelesai) {
                        teks = `Tanggal ${formatDateDMY(tglMulai)}`;
                    } else {
                        teks = `${formatDateDMY(tglMulai)} s/d ${formatDateDMY(tglSelesai)}`;
                    }
                } else if (tglMulai) {
                    teks = `Dari ${formatDateDMY(tglMulai)} s/d sekarang`;
                } else if (tglSelesai) {
                    teks = `Sampai ${formatDateDMY(tglSelesai)}`;
                } else {
                    teks = 'Semua data';
                }

                $('#teksFilterAktif').text(teks);
                $('#infoFilterAktif').removeClass('d-none');
            }

            $('#filter_tanggal_mulai, #filter_tanggal_selesai').on('change', function() {
                $('#preset_filter').val(''); 
                updateInfoFilter();
                kondisiTable.ajax.reload(null, false);
            });

            $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function () {
                $.fn.dataTable
                    .tables({ visible: true, api: true })
                    .columns.adjust()
                    .responsive.recalc();
            });

            $('#exportReportType').on('change', function() {
                let type = $(this).val();
                
                if (type === 'pemeriksaan') {
                    $('#filterPemeriksaan').removeClass('d-none');
                    $('#filterAlat').addClass('d-none');
                } else if (type === 'alat') {
                    $('#filterPemeriksaan').addClass('d-none');
                    $('#filterAlat').removeClass('d-none');
                }
            });

            $('#exportFormat').on('change', function() {
                let format = $(this).val();
                let action = format === 'pdf' 
                    ? "{{ route('office.KondisiTools.exportPdf') }}"
                    : "{{ route('office.KondisiTools.exportExcel') }}";
                
                $('#formExportFilter').attr('action', action);
                $('#exportBtnText').text(format === 'pdf' ? 'Export PDF' : 'Export Excel');
            });

            $(document).on('click', '.preset-date', function() {
                let days = $(this).data('days');
                
                if (days === 0) {
                    $('#export_tanggal_mulai').val('');
                    $('#export_tanggal_selesai').val('');
                    return;
                }
                
                let today = new Date();
                let startDate = new Date();
                startDate.setDate(today.getDate() - days);
                
                $('#export_tanggal_mulai').val(formatDate(startDate));
                $('#export_tanggal_selesai').val(formatDate(today));
            });

            function formatDate(date) {
                let year = date.getFullYear();
                let month = String(date.getMonth() + 1).padStart(2, '0');
                let day = String(date.getDate()).padStart(2, '0');
                return `${year}-${month}-${day}`;
            }

            $('#formExportFilter').on('submit', function(e) {
                let tglMulai = $('#export_tanggal_mulai').val();
                let tglSelesai = $('#export_tanggal_selesai').val();
                
                if (tglMulai && tglSelesai) {
                    if (new Date(tglMulai) > new Date(tglSelesai)) {
                        e.preventDefault();
                        Swal.fire({
                            icon: 'warning',
                            title: 'Tanggal Tidak Valid',
                            text: 'Tanggal mulai tidak boleh lebih besar dari tanggal selesai!'
                        });
                        return false;
                    }
                }
            });

            $('#modalExport').on('shown.bs.modal', function() {
                $('#exportReportType').val('pemeriksaan').trigger('change');
                $('#exportFormat').val('excel').trigger('change');
            });

        });
    </script>
@endsection