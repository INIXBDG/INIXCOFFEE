@extends('layouts_office.app')
@section('office_contents')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px
        }

        ::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 4px
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8
        }

        .table-hover tbody tr:hover {
            background-color: rgba(0, 0, 0, .02)
        }

        .task-text {
            transition: all .3s ease
        }

        .category-card {
            cursor: pointer;
            border: 2px solid transparent;
            transition: all .2s
        }

        .category-card.selected {
            border-color: #0d6efd;
            background-color: rgba(13, 110, 253, .05)
        }

        .category-card:hover {
            border-color: #0d6efd;
            transform: translateY(-2px)
        }

        .bulk-action-bar {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 15px;
            display: none;
            align-items: center;
            gap: 10px;
        }

        .bukti-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 500;
        }

        .bukti-badge.both {
            background: #d1e7dd;
            color: #0f5132;
        }

        .bukti-badge.partial {
            background: #fff3cd;
            color: #664d03;
        }

        .bukti-badge.none {
            background: #f8f9fa;
            color: #6c757d;
        }

        .bukti-preview {
            cursor: pointer;
            transition: transform .2s;
        }

        .bukti-preview:hover {
            transform: scale(1.05);
        }

        .chart-container {
            position: relative;
            height: 300px;
            margin-top: 20px;
        }

        .chart-filters {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 15px;
            align-items: center;
        }

        .photo-comparison {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .photo-comparison .photo-box {
            text-align: center;
        }

        .photo-comparison .photo-box img {
            max-width: 100%;
            max-height: 300px;
            border-radius: 8px;
            border: 2px solid #dee2e6;
        }

        .photo-comparison .photo-box .label {
            font-weight: 600;
            margin-bottom: 8px;
            color: #495057;
        }
    </style>
    <div class="container-fluid py-4">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show rounded-4 shadow-sm border-0" role="alert">
                <i class="bx bx-check-circle me-2"></i><strong>Berhasil!</strong>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        <div
            class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
            <div>
                <h4 class="mb-1 fw-bold text-dark">Daftar Tugas Office Boy</h4>
                <p class="text-muted small mb-0">Pilih dan kelola tugas kebersihan serta pekerjaan harian.</p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <button class="btn btn-primary px-4 shadow-sm d-flex align-items-center gap-2" data-bs-toggle="modal"
                    data-bs-target="#createModal">
                    <i class="bx bx-plus"></i>Buat Kategori Baru
                </button>
                <div class="btn-group">
                    <button class="btn btn-outline-primary px-3 shadow-sm d-flex align-items-center gap-2" type="button"
                        data-bs-toggle="dropdown">
                        <i class="bx bx-file-import"></i> Import
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#modalImport">
                                <i class="bx bx-upload me-2"></i> Import Tugas
                            </a></li>
                        <li><a class="dropdown-item" href="{{ asset('templates/daftar_tugas_template.xlsx') }}" download>
                                <i class="bx bx-download me-2"></i> Download Template
                            </a></li>
                    </ul>
                </div>

                <div class="modal fade" id="modalImport" tabindex="-1" data-bs-backdrop="static">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <form id="formImport" enctype="multipart/form-data">
                                @csrf
                                <div class="modal-header">
                                    <h5 class="modal-title fw-bold">
                                        <i class="bx bx-file-import me-2"></i>Import Tugas Historis
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="alert alert-info small">
                                        <i class="bx bx-info-circle me-1"></i>
                                        Import tugas dengan tanggal deadline untuk data historis.
                                        <a href="{{ asset('templates/daftar_tugas_template.xlsx') }}" class="ms-1"
                                            download>
                                            📥 Download template
                                        </a>
                                    </div>

                                    @if (Auth::user()->jabatan === 'HRD')
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold small">Import untuk Office Boy</label>
                                            <select name="karyawan_id" class="form-select form-select-sm">
                                                <option value="">
                                                    Pembuat Saat Ini
                                                    ({{ Auth::user()->karyawan->nama_lengkap ?? Auth::user()->name }})
                                                </option>
                                                @foreach ($officeBoy as $ob)
                                                    <option value="{{ $ob->id }}">{{ $ob->nama_lengkap }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    @endif

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

                                    <div class="alert alert-warning small mb-0">
                                        <i class="bx bx-time me-1"></i>
                                        <strong>Penting:</strong> Setiap baris akan membuat tugas dengan
                                        <code>deadline_date</code> yang sesuai. Pastikan tanggal sudah benar.
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
                <div class="btn-group">
                    <button class="btn btn-outline-success shadow-sm d-flex align-items-center gap-2" type="button"
                        data-bs-toggle="dropdown">
                        <i class="bx bx-file-export"></i> Export
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <h6 class="dropdown-header">Tipe Laporan</h6>
                        </li>
                        <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#modalExport"><i
                                    class="bx bx-cog me-2"></i> Export dengan Filter</a></li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li><a class="dropdown-item"
                                href="{{ route('office.DaftarTugas.export.excel', ['report_type' => 'tugas']) }}"><i
                                    class="bx bx-file-excel text-success me-2"></i> Excel - Tugas</a></li>
                        <li><a class="dropdown-item"
                                href="{{ route('office.DaftarTugas.export.pdf', ['report_type' => 'tugas']) }}"><i
                                    class="bx bx-file-pdf text-danger me-2"></i> PDF - Tugas</a></li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li><a class="dropdown-item"
                                href="{{ route('office.DaftarTugas.export.excel', ['report_type' => 'kategori']) }}"><i
                                    class="bx bx-file-excel text-success me-2"></i> Excel - Kategori</a></li>
                        <li><a class="dropdown-item"
                                href="{{ route('office.DaftarTugas.export.pdf', ['report_type' => 'kategori']) }}"><i
                                    class="bx bx-file-pdf text-danger me-2"></i> PDF - Kategori</a></li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden glass-force">
            <div class="card-header border-0 py-3">
                <div class="row align-items-center g-3">
                    <div class="col-md-5">
                        <h5 class="mb-0 fw-semibold" id="dynamicTitle">Tugas Aktif -
                            {{ now()->translatedFormat('l, d F Y') }}</h5>
                    </div>
                    <div class="col-md-7">
                        <div class="d-flex flex-wrap gap-2 justify-content-md-end align-items-center">
                            <select id="filterTipe" class="form-select form-select-sm" style="width:auto">
                                <option value="all" selected>Semua Tipe</option>
                                <option value="Harian">Harian</option>
                                <option value="Mingguan">Mingguan</option>
                                <option value="Bulanan">Bulanan</option>
                                <option value="Quartal">Quartal</option>
                                <option value="Semester">Semester</option>
                                <option value="Tahunan">Tahunan</option>
                            </select>
                            <select id="filterTipeTurunan" class="form-select form-select-sm" style="width:auto">
                                <option value="all" selected>Semua Shift</option>
                                <option value="Shift 1">Shift 1</option>
                                <option value="Shift 2">Shift 2</option>
                                <option value="Sabtu">Sabtu</option>
                                <option value="Minggu">Minggu</option>
                            </select>
                            <input type="date" id="filterTanggal" class="form-control form-control-sm"
                                style="width:auto" value="{{ now()->format('Y-m-d') }}">
                            <button class="btn btn-outline-secondary btn-sm" id="btnResetFilter" title="Reset Filter"><i
                                    class="bx bx-reset"></i></button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="text-dark fw-semibold small bg-light">
                            <tr>
                                <th class="ps-4 border-0" style="width:5%">Checklist</th>
                                <th class="border-0" style="width:30%">Tugas</th>
                                <th class="border-0" style="width:15%">Tipe</th>
                                <th class="border-0" style="width:15%">Shift</th>
                                <th class="border-0" style="width:15%">Karyawan</th>
                                <th class="border-0" style="width:15%">Deadline</th>
                                <th class="border-0 text-center" style="width:20%">Bukti</th>
                                <th class="border-0 text-center" style="width:15%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="tbody"></tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4 mt-4 glass-force">
            <div class="card-header border-0 py-3">
                <h5 class="mb-0 fw-semibold">Grafik Kinerja Tugas</h5>
            </div>
            <div class="card-body">
                <div class="chart-filters">
                    <select id="chartPeriod" class="form-select form-select-sm" style="width:auto">
                        <option value="weekly">Per Minggu</option>
                        <option value="monthly" selected>Per Bulan</option>
                        <option value="quarterly">Per 3 Bulan</option>
                        <option value="yearly">Per Tahun</option>
                    </select>
                    <select id="chartKaryawan" class="form-select form-select-sm" style="width:auto">
                        <option value="all">Semua Karyawan</option>
                        @foreach ($officeBoy as $ob)
                            <option value="{{ $ob->id }}">{{ $ob->nama_lengkap }}</option>
                        @endforeach
                    </select>
                    <input type="date" id="chartStartDate" class="form-control form-control-sm" style="width:auto">
                    <input type="date" id="chartEndDate" class="form-control form-control-sm" style="width:auto">
                    <button class="btn btn-primary btn-sm" id="btnLoadChart"><i class="bx bx-refresh"></i> Load
                        Grafik</button>
                </div>
                <div class="chart-container">
                    <canvas id="taskChart"></canvas>
                </div>
            </div>
        </div>

        @if (Auth()->user()->jabatan === 'Office Boy')
                        
        @endif
        <div class="card border-0 shadow-sm rounded-4 mt-4 glass-force">
            <div class="card-header border-0 py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-semibold">
                    <i class="bx bx-list-plus me-2"></i>Kategori Tersedia
                </h5>
                <span class="badge bg-secondary" id="availableCount">0</span>
            </div>
            <div class="card-body">
                <div class="alert alert-info small mb-3">
                    <i class="bx bx-info-circle me-1"></i>
                    Pilih kategori di bawah untuk diaktifkan sebagai tugas hari ini.
                    Tugas yang sudah aktif tidak akan muncul di sini.
                </div>

                <!-- Filter mini -->
                <div class="d-flex flex-wrap gap-2 mb-3">
                    <select id="filterAvailableTipe" class="form-select form-select-sm" style="width:auto">
                        <option value="all">Semua Tipe</option>
                        <option value="Harian">Harian</option>
                        <option value="Mingguan">Mingguan</option>
                        <option value="Bulanan">Bulanan</option>
                        <option value="Quartal">Quartal</option>
                        <option value="Semester">Semester</option>
                        <option value="Tahunan">Tahunan</option>
                    </select>
                    <button class="btn btn-outline-primary btn-sm" id="btnRefreshAvailable">
                        <i class="bx bx-refresh"></i> Refresh
                    </button>
                </div>

                <!-- Loading state -->
                <div id="availableLoading" class="text-center py-4 d-none">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="text-muted small mt-2 mb-0">Memuat kategori...</p>
                </div>

                <!-- List kategori -->
                <div id="availableList" style="max-height:400px; overflow-y:auto;">
                    <div class="text-center text-muted py-4">
                        <i class="bx bx-folder-open" style="font-size:2rem"></i>
                        <p class="mt-2 mb-0 small">Klik "Refresh" untuk memuat kategori tersedia</p>
                    </div>
                </div>

                <!-- Bulk action -->
                <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top" id="availableActions"
                    style="display:none">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="checkAllAvailable">
                        <label class="form-check-label small" for="checkAllAvailable">Pilih Semua</label>
                    </div>
                    <button class="btn btn-primary btn-sm" id="btnActivateSelected">
                        <i class="bx bx-play-circle me-1"></i>Aktifkan Tugas Terpilih (<span id="selectedCount">0</span>)
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="createModal" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="formCreateKategori" action="{{ route('office.DaftarTugas.store') }}" method="POST">@csrf
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold">Kategori Tugas Baru</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        @if (Auth::user()->jabatan === 'HRD')
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Penanggung Jawab</label>
                                <select name="jabatan_pembuat" class="form-select">
                                    <option value="" disabled selected>Pilih Karyawan</option>
                                    @foreach ($officeBoy as $data)
                                        <option value="{{ $data->id }}">{{ $data->nama_lengkap }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nama Tugas</label>
                            <input type="text" class="form-control" name="tugas"
                                placeholder="Contoh : Kebersihan Ruangan Meeting" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Tipe Frekuensi</label>
                            <select name="Tipe" id="createTipe" required class="form-select">
                                <option value="" disabled selected>Pilih Tipe</option>
                                <option value="Harian">Harian</option>
                                <option value="Mingguan">Mingguan</option>
                                <option value="Bulanan">Bulanan</option>
                                <option value="Quartal">Quartal</option>
                                <option value="Semester">Semester</option>
                                <option value="Tahunan">Tahunan</option>
                            </select>
                        </div>
                        <div class="mb-3 d-none" id="createTipeTurunanContainer">
                            <label class="form-label fw-semibold" id="createTipeTurunanLabel">Tipe Turunan</label>
                            <select name="tipe_turunan" class="form-select">
                                <option selected disabled>Pilih Opsi</option>
                            </select>
                        </div>
                        <hr class="my-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="mb-0 fw-semibold"><i class="bx bx-list-ul me-2"></i>Daftar Kategori Saat Ini</h6>
                            @if (Auth::user()->jabatan === 'HRD' || Auth::id() == Auth::user()->id)
                                <button type="button" class="btn btn-sm btn-warning d-none" id="btnBulkUpdate">
                                    <i class="bx bx-edit-alt"></i> Update Shift Terpilih
                                </button>
                            @endif
                        </div>

                        <div id="bulkActionPanel" class="bulk-action-bar">
                            <span class="small text-muted">Pilih Shift untuk update:</span>
                            <select id="bulkShiftSelect" class="form-select form-select-sm" style="width:auto">
                                <option value="">-- Kosongkan Shift --</option>
                                <option value="Shift 1">Shift 1</option>
                                <option value="Shift 2">Shift 2</option>
                                <option value="Sabtu">Sabtu</option>
                                <option value="Minggu">Minggu</option>
                            </select>
                            <button type="button" class="btn btn-primary btn-sm"
                                id="confirmBulkUpdate">Terapkan</button>
                            <button type="button" class="btn btn-secondary btn-sm" id="cancelBulkUpdate">Batal</button>
                        </div>

                        <div style="max-height:300px;overflow-y:auto;border:1px solid #eee;border-radius:8px">
                            <table class="table table-sm table-bordered mb-0" id="tabelKategori">
                                <thead class="table-light">
                                    <tr>
                                        <th width="30"><input type="checkbox" id="checkAllKategori"></th>
                                        <th>Tugas</th>
                                        <th width="100">Tipe</th>
                                        <th width="100">Shift</th>
                                        <th>PIC</th>
                                        <th width="130">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($dataKategori as $data)
                                        <tr data-id="{{ $data->id }}">
                                            <td><input type="checkbox" class="chk-bulk-kategori"
                                                    value="{{ $data->id }}" data-tipe="{{ $data->Tipe }}"></td>
                                            <td>{{ $data->judul_kategori }}</td>
                                            <td><span class="badge bg-info text-dark">{{ $data->Tipe }}</span></td>
                                            <td><span class="badge bg-secondary">{{ $data->tipe_turunan ?? '-' }}</span>
                                            </td>
                                            <td>{{ $data->karyawan->nama_lengkap ?? '-' }}</td>
                                            <td>
                                                <div class="btn-group btn-group-sm w-100">
                                                    <button type="button"
                                                        class="btn btn-outline-primary btn-edit-kategori"
                                                        data-id="{{ $data->id }}"
                                                        data-judul="{{ $data->judul_kategori }}"
                                                        data-tipe="{{ $data->Tipe }}"
                                                        data-turunan="{{ $data->tipe_turunan }}"
                                                        data-user="{{ $data->karyawan->nama_lengkap ?? 'N/A' }}"><i
                                                            class="bx bx-edit"></i></button>
                                                    <button type="button"
                                                        class="btn btn-outline-danger btn-delete-kategori"
                                                        data-id="{{ $data->id }}"
                                                        data-judul="{{ $data->judul_kategori }}"><i
                                                            class="bx bx-trash"></i></button>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center py-3 text-muted">Belum ada kategori.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn btn-primary" id="btnSimpanKategori"><span
                                class="spinner-border spinner-border-sm d-none" id="createSpinner"></span>Simpan
                            Kategori</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalEditKategori" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="formEditKategori">@csrf<input type="hidden" name="id" id="edit_id">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold">Edit Kategori Tugas</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3"><label class="form-label fw-semibold">Judul Kategori</label><input
                                type="text" name="judul_kategori" id="edit_judul" class="form-control" required>
                        </div>
                        <div class="mb-3"><label class="form-label fw-semibold">Tipe</label><select name="tipe"
                                id="edit_tipe" class="form-select" required>
                                <option value="Harian">Harian</option>
                                <option value="Mingguan">Mingguan</option>
                                <option value="Bulanan">Bulanan</option>
                                <option value="Quartal">Quartal</option>
                                <option value="Semester">Semester</option>
                                <option value="Tahunan">Tahunan</option>
                            </select></div>
                        <div class="mb-3 d-none" id="editTipeTurunanContainer">
                            <label class="form-label fw-semibold" id="editTipeTurunanLabel">Tipe Turunan</label>
                            <select name="tipe_turunan" id="edit_tipe_turunan" class="form-select">
                                <option value="">Tidak Ada / Umum</option>
                            </select>
                        </div>
                        @if (Auth::user()->jabatan === 'HRD')
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Penanggung Jawab</label>
                                <select name="jabatan_pembuat" class="form-select">
                                    <option value="" disabled selected>Pilih Karyawan</option>
                                    @foreach ($officeBoy as $data)
                                        <option value="{{ $data->id }}">{{ $data->nama_lengkap }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary"><span
                                class="spinner-border spinner-border-sm d-none" id="editSpinner"></span>Simpan
                            Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalDeleteKategori" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title small fw-bold">Konfirmasi Hapus</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center py-4">
                    <i class="bx bx-error text-danger mb-3" style="font-size:2rem"></i>
                    <p class="mb-2">Apakah Anda yakin ingin menghapus kategori:</p>
                    <strong id="delete_judul" class="text-dark d-block mb-3"></strong>
                    <input type="hidden" id="delete_id">
                </div>
                <div class="modal-footer justify-content-center bg-light">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-danger" id="confirmDelete"><span
                            class="spinner-border spinner-border-sm d-none" id="deleteSpinner"></span>Ya, Hapus</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalUploadBukti" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form id="formUploadBukti" enctype="multipart/form-data">@csrf
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold"><i class="bx bx-paperclip me-2"></i>Upload Bukti Pelaksanaan</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="tugas_id" id="uploadTugasId">
                        <div class="mb-3"><label class="form-label fw-semibold small text-muted">Tugas</label><input
                                type="text" id="uploadTugasNama" class="form-control-plaintext fw-bold" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Foto Before <small class="text-danger">*</small></label>
                            <input type="file" class="form-control" name="bukti_before" id="inputBuktiBefore"
                                accept="image/*" required>
                            <div class="form-text">Format: JPG, PNG. Maksimal 5MB</div>
                            <div id="previewBeforeContainer" class="d-none text-center mt-2">
                                <img id="imagePreviewBefore" src="" class="img-fluid rounded shadow-sm"
                                    style="max-height:150px">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Foto After <small class="text-danger">*</small></label>
                            <input type="file" class="form-control" name="bukti_after" id="inputBuktiAfter"
                                accept="image/*" required>
                            <div class="form-text">Format: JPG, PNG. Maksimal 5MB</div>
                            <div id="previewAfterContainer" class="d-none text-center mt-2">
                                <img id="imagePreviewAfter" src="" class="img-fluid rounded shadow-sm"
                                    style="max-height:150px">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary" id="btnSubmitUpload"><span
                                class="spinner-border spinner-border-sm d-none" id="uploadSpinner"></span><span
                                id="btnUploadText">Upload Bukti</span></button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalPreviewBukti" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="previewModalTitle">Detail Bukti</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="previewModalBody">
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalExport" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form id="formExport" method="GET">
                    <div class="modal-header text-white">
                        <h5 class="modal-title small fw-bold"><i class="bx bx-filter me-2"></i>Filter Export Laporan</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Tipe Laporan</label>
                            <select name="report_type" class="form-select form-select-sm" id="exportReportType">
                                <option value="tugas">Pelaksanaan Tugas</option>
                                <option value="kategori">Kategori Tugas</option>
                            </select>
                        </div>
                        <div id="filterTugasSection">
                            <div class="row g-2 mb-2">
                                <div class="col-6"><label class="form-label small">Tanggal Mulai</label><input
                                        type="date" name="start_date" class="form-control form-control-sm"></div>
                                <div class="col-6"><label class="form-label small">Tanggal Akhir</label><input
                                        type="date" name="end_date" class="form-control form-control-sm"></div>
                            </div>
                            <div class="row g-2">
                                <div class="col-6">
                                    <label class="form-label small">Tipe Frekuensi</label>
                                    <select name="tipe" class="form-select form-select-sm">
                                        <option value="">Semua</option>
                                        <option value="Harian">Harian</option>
                                        <option value="Mingguan">Mingguan</option>
                                        <option value="Bulanan">Bulanan</option>
                                        <option value="Quartal">Quartal</option>
                                        <option value="Semester">Semester</option>
                                        <option value="Tahunan">Tahunan</option>
                                    </select>
                                </div>
                                <div class="col-6">
                                    <label class="form-label small">Status</label>
                                    <select name="status" class="form-select form-select-sm">
                                        <option value="">Semua</option>
                                        <option value="1">Selesai</option>
                                        <option value="0">Pending</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div id="filterKategoriSection" class="d-none">
                            <div class="mb-2">
                                <label class="form-label small">Tipe Frekuensi</label>
                                <select name="tipe" class="form-select form-select-sm">
                                    <option value="">Semua</option>
                                    <option value="Harian">Harian</option>
                                    <option value="Mingguan">Mingguan</option>
                                    <option value="Bulanan">Bulanan</option>
                                    <option value="Quartal">Quartal</option>
                                    <option value="Semester">Semester</option>
                                    <option value="Tahunan">Tahunan</option>
                                </select>
                            </div>
                        </div>
                        @if (Auth::user()->jabatan === 'HRD')
                            <div class="mb-2">
                                <label class="form-label small">Filter Office Boy</label>
                                <select name="karyawan" class="form-select form-select-sm">
                                    <option value="">Semua Office Boy</option>
                                    @foreach ($officeBoy as $ob)
                                        <option value="{{ $ob->id }}">{{ $ob->nama_lengkap }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success btn-sm"
                            formaction="{{ route('office.DaftarTugas.export.excel') }}" formtarget="_blank"><i
                                class="bx bx-file-excel me-1"></i> Excel</button>
                        <button type="submit" class="btn btn-danger btn-sm"
                            formaction="{{ route('office.DaftarTugas.export.pdf') }}" formtarget="_blank"><i
                                class="bx bx-file-pdf me-1"></i> PDF</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        $(document).ready(function() {
            const today = new Date().toISOString().split('T')[0];
            $('#filterTanggal').val(today);
            $('#chartEndDate').val(today);
            $('#chartStartDate').val(new Date(new Date().setMonth(new Date().getMonth() - 1)).toISOString().split(
                'T')[0]);

            let taskChart = null;

            function updateTitle() {
                const t = $('#filterTipe').val();
                const d = $('#filterTanggal').val();
                const dt = new Date(d + 'T00:00:00');
                const tipeText = t === 'all' ? 'Semua Tipe' : t;
                $('#dynamicTitle').text(
                    `Tugas Aktif ${tipeText} - ${dt.toLocaleDateString('id-ID', {weekday:'long',year:'numeric',month:'long',day:'numeric'})}`
                );
            }

            function parseBukti(bukti) {
                if (!bukti) return {
                    before: null,
                    after: null
                };
                try {
                    if (typeof bukti === 'string' && bukti.startsWith('{')) {
                        return JSON.parse(bukti);
                    }
                    return {
                        before: bukti,
                        after: null
                    };
                } catch (e) {
                    return {
                        before: bukti,
                        after: null
                    };
                }
            }

            function getBuktiStatus(bukti) {
                const data = parseBukti(bukti);
                if (data.before && data.after) return {
                    class: 'both',
                    text: 'Before + After',
                    rowClass: '',
                    canCheck: true
                };
                if (data.before || data.after) return {
                    class: 'partial',
                    text: '1 Foto',
                    rowClass: 'table-warning',
                    canCheck: false
                };
                return {
                    class: 'none',
                    text: 'Belum Ada',
                    rowClass: 'table-danger',
                    canCheck: false
                };
            }

            function loadData() {
                $.ajax({
                    url: "{{ route('office.DaftarTugas.get') }}",
                    type: 'GET',
                    data: {
                        tipe: $('#filterTipe').val(),
                        tipe_turunan: $('#filterTipeTurunan').val(),
                        tanggal: $('#filterTanggal').val()
                    },
                    success: function(r) {
                        const tb = $('#tbody');
                        tb.empty();
                        if (!r.data || !r.data.length) {
                            tb.append(
                                `<tr><td colspan="8" class="text-center py-5"><div class="d-flex flex-column align-items-center gap-3"><div class="bg-light rounded-circle p-4"><i class="bx bx-clipboard text-muted" style="font-size:3rem"></i></div><h5 class="text-muted mb-1">Belum ada Tugas Aktif</h5><p class="text-muted small mb-3">Pilih tugas dari kategori yang tersedia untuk mulai mengerjakan</p></div></td></tr>`
                            );
                            return;
                        }
                        r.data.forEach(function(it) {
                            const kat = it.kategori_daftar_tugas?.judul_kategori ||
                                'Tanpa Kategori';
                            const tipe = it.kategori_daftar_tugas?.Tipe || '-';
                            const turunan = it.kategori_daftar_tugas?.tipe_turunan || '-';
                            const karyawan = it.karyawan?.nama_lengkap || '-';
                            const dl = it.Deadline_Date || '-';
                            const chk = it.status == 1 ? 'checked' : '';
                            const done = it.status == 1 ?
                                'text-decoration-line-through text-muted opacity-50' : '';
                            const buktiData = parseBukti(it.bukti);
                            const buktiStatus = getBuktiStatus(it.bukti);
                            const buktiBadge =
                                `<span class="bukti-badge ${buktiStatus.class}">${buktiStatus.text}</span>`;
                            const checkboxDisabled = !buktiStatus.canCheck ? 'disabled' : '';
                            const checkboxTitle = !buktiStatus.canCheck ?
                                'title="Upload foto Before dan After terlebih dahulu"' : '';
                            const buktiBtn = buktiData.before || buktiData.after ?
                                `<button class="btn btn-sm btn-outline-primary btn-viewBukti" data-bukti='${JSON.stringify(buktiData)}' data-judul="${kat.replace(/"/g,'&quot;')}"><i class="bx bx-show"></i> Lihat</button>` :
                                `<button class="btn btn-sm btn-primary btn-uploadBukti" data-id="${it.id}" data-judul="${kat.replace(/"/g,'&quot;')}"><i class="bx bx-upload"></i> Upload</button>`;
                            tb.append(
                                `<tr class="${buktiStatus.rowClass} ${done?'bg-light':''}" data-id="${it.id}"><td class="ps-4"><div class="form-check"><input class="form-check-input checkStatus" type="checkbox" data-id="${it.id}" ${chk} ${checkboxDisabled} ${checkboxTitle}></div></td><td class="task-text ${done} fw-medium">${kat}</td><td class="task-text ${done}"><span class="badge bg-secondary">${tipe}</span></td><td class="task-text ${done}"><span class="badge bg-info text-dark">${turunan}</span></td><td class="task-text ${done} small fw-semibold">${karyawan}</td>
                    <td class="task-text ${done} small">${dl}</td><td class="text-center">${buktiBadge}</td><td class="text-center"><div class="btn-group">${buktiBtn}<button class="btn btn-outline-danger btn-sm btn-hapus" data-id="${it.id}"><i class="bx bx-trash"></i></button></div></td></tr>`
                            );
                        });
                    }
                });
            }


            function loadChartData() {
                const period = $('#chartPeriod').val();
                const karyawan = $('#chartKaryawan').val();
                const startDate = $('#chartStartDate').val();
                const endDate = $('#chartEndDate').val();

                $.ajax({
                    url: "{{ route('office.DaftarTugas.chartData') }}",
                    type: 'GET',
                    data: {
                        period,
                        karyawan,
                        start_date: startDate,
                        end_date: endDate
                    },
                    success: function(r) {
                        renderChart(r.labels, r.dataSelesai, r.dataPending);
                    }
                });
            }

            function renderChart(labels, dataSelesai, dataPending) {
                const ctx = document.getElementById('taskChart').getContext('2d');
                if (taskChart) taskChart.destroy();
                taskChart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                                label: 'Selesai',
                                data: dataSelesai,
                                backgroundColor: 'rgba(13, 189, 115, 0.7)',
                                borderColor: 'rgba(13, 189, 115, 1)',
                                borderWidth: 1
                            },
                            {
                                label: 'Pending',
                                data: dataPending,
                                backgroundColor: 'rgba(255, 193, 7, 0.7)',
                                borderColor: 'rgba(255, 193, 7, 1)',
                                borderWidth: 1
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            x: {
                                stacked: true,
                                title: {
                                    display: true,
                                    text: 'Periode'
                                }
                            },
                            y: {
                                stacked: true,
                                beginAtZero: true,
                                title: {
                                    display: true,
                                    text: 'Jumlah Tugas'
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                position: 'top'
                            },
                            tooltip: {
                                mode: 'index',
                                intersect: false
                            }
                        }
                    }
                });
            }

            $('#formImport input[name="file"]').on('change', function(e) {
                const file = e.target.files[0];
                if (!file) return;
                const validTypes = ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'application/vnd.ms-excel', 'text/csv'
                ];
                if (!validTypes.includes(file.type) && !file.name.match(/\.(xlsx|xls|csv)$/i)) {
                    alert('Format file tidak didukung. Gunakan XLSX, XLS, atau CSV.');
                    $(this).val('');
                    return;
                }
                if (file.size > 10 * 1024 * 1024) {
                    alert('Ukuran file terlalu besar. Maksimal 10MB.');
                    $(this).val('');
                    return;
                }
                $('#importPreview').removeClass('d-none');
                $('#previewList').html(
                    `<li>${file.name} <span class="text-muted">(${(file.size/1024).toFixed(1)} KB)</span></li>`
                );
            });

            $('#formImport').on('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                const btn = $('#btnSubmitImport');
                const spinner = $('#importSpinner');
                const btnText = $('#importBtnText');
                btn.prop('disabled', true);
                spinner.removeClass('d-none');
                btnText.text('Memproses...');
                $.ajax({
                    url: "{{ route('office.DaftarTugas.import') }}",
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(r) {
                        let msg = r.message;
                        if (r.warnings?.length) {
                            msg += `\n\n⚠️ Beberapa baris dilewati:`;
                            r.warnings.forEach(w => msg += `\n• ${w}`);
                        }
                        $('#formImport')[0].reset();
                        loadData();
                    },
                    error: function(xhr) {
                        let msg = xhr.responseJSON?.message || 'Import gagal';
                        if (xhr.responseJSON?.errors?.length) {
                            msg += `\n\n❌ Error validasi:`;
                            xhr.responseJSON.errors.forEach(e => msg += `\n• ${e}`);
                        }
                        showNotification('Import Gagal', msg, 'danger');
                    },
                    complete: function() {
                        btn.prop('disabled', false);
                        spinner.addClass('d-none');
                        btnText.text('Import Data');
                    }
                });
            });

            function refreshKategoriTable() {
                $.ajax({
                    url: "{{ route('office.DaftarTugas.getKategori') }}",
                    type: 'GET',
                    success: function(d) {
                        const tb = $('#tabelKategori tbody');
                        tb.empty();
                        if (!d.length) {
                            tb.append(
                                '<tr><td colspan="6" class="text-center py-3 text-muted">Belum ada kategori.</td></tr>'
                            );
                            return;
                        }
                        d.forEach(function(it) {
                            tb.append(
                                `<tr data-id="${it.id}"><td><input type="checkbox" class="chk-bulk-kategori" value="${it.id}" data-tipe="${it.Tipe}"></td><td>${it.judul_kategori}</td><td><span class="badge bg-info text-dark">${it.Tipe}</span></td><td><span class="badge bg-secondary">${it.tipe_turunan || '-'}</span></td><td>${it.karyawan?.nama_lengkap||'-'}</td><td><div class="btn-group btn-group-sm w-100"><button class="btn btn-outline-primary btn-edit-kategori" data-id="${it.id}" data-judul="${it.judul_kategori}" data-tipe="${it.Tipe}" data-turunan="${it.tipe_turunan}" data-user="${it.karyawan?.nama_lengkap||'N/A'}"><i class="bx bx-edit"></i></button><button class="btn btn-outline-danger btn-delete-kategori" data-id="${it.id}" data-judul="${it.judul_kategori}"><i class="bx bx-trash"></i></button></div></td></tr>`
                            );
                        });
                    }
                });
            }

            loadData();
            updateTitle();
            loadChartData();

            $('#filterTipe,#filterTipeTurunan,#filterTanggal').on('change', function() {
                updateTitle();
                loadData();
            });
            $('#btnResetFilter').on('click', function() {
                $('#filterTipe').val('all');
                $('#filterTipeTurunan').val('all');
                $('#filterTanggal').val(today);
                updateTitle();
                loadData();
            });

            function renderTipeTurunanDropdown(tipe, container, labelContainer, selected = '') {
                let options = '<option value="" selected disabled>Pilih Opsi</option>';

                if (tipe === 'Harian') {
                    options += '<option value="Shift 1"' + (selected === 'Shift 1' ? ' selected' : '') +
                        '>Shift 1</option>';
                    options += '<option value="Shift 2"' + (selected === 'Shift 2' ? ' selected' : '') +
                        '>Shift 2</option>';
                    labelContainer.text('Shift Harian');
                } else if (tipe === 'Mingguan') {
                    options += '<option value="Sabtu"' + (selected === 'Sabtu' ? ' selected' : '') +
                        '>Sabtu</option>';
                    options += '<option value="Minggu"' + (selected === 'Minggu' ? ' selected' : '') +
                        '>Minggu</option>';
                    labelContainer.text('Shift Akhir Pekan');
                } else if (tipe === 'Bulanan') {
                    options = '<option value="">Setiap Tanggal 1</option>';
                    for (let i = 1; i <= 31; i++) {
                        const val = String(i).padStart(2, '0');
                        const label = i === 1 ? 'Tanggal 1 (Default)' : `Tanggal ${i}`;
                        options += `<option value="${val}"${selected === val ? ' selected' : ''}>${label}</option>`;
                    }
                    labelContainer.text('Tanggal Pengerjaan');
                }

                container.find('select[name="tipe_turunan"]').html(options);
                container.removeClass('d-none');
            }

            $('#createTipe').on('change', function() {
                const tipe = $(this).val();
                const container = $('#createTipeTurunanContainer');
                const label = $('#createTipeTurunanLabel');

                if (['Harian', 'Mingguan', 'Bulanan'].includes(tipe)) {
                    renderTipeTurunanDropdown(tipe, container, label);
                } else {
                    container.addClass('d-none');
                    container.find('select[name="tipe_turunan"]').val('');
                }
            });

            $('#edit_tipe').on('change', function() {
                const tipe = $(this).val();
                const container = $('#editTipeTurunanContainer');
                const label = $('#editTipeTurunanLabel');
                const selected = $('#edit_tipe_turunan').val();

                if (['Harian', 'Mingguan', 'Bulanan'].includes(tipe)) {
                    renderTipeTurunanDropdown(tipe, container, label, selected);
                } else {
                    container.addClass('d-none');
                    container.find('select[name="tipe_turunan"]').val('');
                }
            });

            let wasCreateModalOpen = false;

            function closeCreateModalIfOpen() {
                const createModalEl = document.getElementById('createModal');
                const bsModal = bootstrap.Modal.getInstance(createModalEl);
                if (bsModal && bsModal._isShown) {
                    wasCreateModalOpen = true;
                    bsModal.hide();
                } else {
                    wasCreateModalOpen = false;
                }
            }

            function isAnyOtherModalOpen() {
                return (bootstrap.Modal.getInstance(document.getElementById('modalEditKategori'))?._isShown ||
                    bootstrap.Modal.getInstance(document.getElementById('modalDeleteKategori'))?._isShown);
            }

            function reopenCreateModalIfNeeded() {
                if (!wasCreateModalOpen) return;
                const tryReopen = () => {
                    if (!isAnyOtherModalOpen()) {
                        const createModal = new bootstrap.Modal(document.getElementById('createModal'));
                        createModal.show();
                        wasCreateModalOpen = false;
                    } else {
                        setTimeout(tryReopen, 150);
                    }
                };
                setTimeout(tryReopen, 100);
            }

            $(document).on('click', '.btn-edit-kategori', function() {
                closeCreateModalIfOpen();
                const id = $(this).data('id');
                const judul = $(this).data('judul');
                const tipe = $(this).data('tipe');
                const turunan = $(this).data('turunan');
                const user = $(this).data('user');
                $('#edit_id').val(id);
                $('#edit_judul').val(judul);
                $('#edit_tipe').val(tipe);
                $('#edit_tipe_turunan').val(turunan);
                const container = $('#editTipeTurunanContainer');
                const label = $('#editTipeTurunanLabel');
                if (['Harian', 'Mingguan', 'Bulanan'].includes(tipe)) {
                    renderTipeTurunanDropdown(tipe, container, label, turunan);
                } else {
                    container.addClass('d-none');
                }
                const modal = new bootstrap.Modal(document.getElementById('modalEditKategori'));
                modal.show();
            });

            $('#formCreateKategori').on('submit', function(e) {
                e.preventDefault();
                const f = $(this);
                const btn = $('#btnSimpanKategori');
                const sp = $('#createSpinner');
                btn.prop('disabled', true);
                sp.removeClass('d-none');
                $.ajax({
                    url: f.attr('action'),
                    type: 'POST',
                    data: f.serialize(),
                    success: function(r) {
                        if (r.success) {
                            showNotification('Berhasil!', r.message, 'success');
                            refreshKategoriTable();
                            f[0].reset();
                            $('#createTipeTurunanContainer').addClass('d-none');
                        } else {
                            showNotification('Gagal', r.message, 'danger');
                        }
                    },
                    complete: function() {
                        btn.prop('disabled', false);
                        sp.addClass('d-none');
                    }
                });
            });

            $('#formEditKategori').on('submit', function(e) {
                e.preventDefault();
                const btn = $(this).find('button[type="submit"]');
                const sp = $('#editSpinner');
                btn.prop('disabled', true);
                sp.removeClass('d-none');
                $.ajax({
                    url: "/office/daftar-tugas/kategori/update",
                    type: 'POST',
                    data: $(this).serialize() + '&_token=' + $('meta[name="csrf-token"]').attr(
                        'content'),
                    success: function(r) {
                        if (r.success) {
                            showNotification('Berhasil!', r.message ||
                                'Kategori berhasil diupdate', 'success');
                            refreshKategoriTable();
                            bootstrap.Modal.getInstance(document.getElementById(
                                'modalEditKategori')).hide();
                        } else {
                            showNotification('Gagal', r.message || 'Gagal update kategori',
                                'danger');
                        }
                    },
                    error: function(xhr) {
                        showNotification('Gagal', xhr.responseJSON?.message ||
                            'Terjadi kesalahan server', 'danger');
                    },
                    complete: function() {
                        btn.prop('disabled', false);
                        sp.addClass('d-none');
                        reopenCreateModalIfNeeded();
                    }
                });
            });

            $(document).on('click', '.btn-delete-kategori', function() {
                closeCreateModalIfOpen();
                const id = $(this).data('id');
                const judul = $(this).data('judul');
                $('#delete_id').val(id);
                $('#delete_judul').text(judul);
                const modal = new bootstrap.Modal(document.getElementById('modalDeleteKategori'));
                modal.show();
            });

            $('#confirmDelete').on('click', function() {
                const btn = $(this);
                const sp = $('#deleteSpinner');
                const id = $('#delete_id').val();
                btn.prop('disabled', true);
                sp.removeClass('d-none');
                $.ajax({
                    url: "/office/daftar-tugas/kategori/hapus",
                    type: 'POST',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content'),
                        id: id
                    },
                    success: function(r) {
                        refreshKategoriTable();
                        showNotification('Berhasil', r.message || 'Kategori berhasil dihapus',
                            'success');
                        bootstrap.Modal.getInstance(document.getElementById(
                            'modalDeleteKategori')).hide();
                    },
                    error: function(xhr) {
                        showNotification('Gagal', xhr.responseJSON?.message ||
                            'Tidak bisa menghapus kategori', 'danger');
                    },
                    complete: function() {
                        btn.prop('disabled', false);
                        sp.addClass('d-none');
                        reopenCreateModalIfNeeded();
                    }
                });
            });

            $('#modalEditKategori, #modalDeleteKategori').on('hidden.bs.modal', function() {
                reopenCreateModalIfNeeded();
            });

            $(document).on('click', '.btn-uploadBukti', function() {
                $('#uploadTugasId').val($(this).data('id'));
                $('#uploadTugasNama').val($(this).data('judul'));
                $('#inputBuktiBefore').val('');
                $('#inputBuktiAfter').val('');
                $('#previewBeforeContainer, #previewAfterContainer').addClass('d-none');
                new bootstrap.Modal(document.getElementById('modalUploadBukti')).show();
            });

            function previewImage(input, previewContainer, previewImg) {
                const file = input.files[0];
                if (file && file.type.startsWith('image/')) {
                    if (file.size > 5 * 1024 * 1024) {
                        alert('Ukuran file terlalu besar! Maksimal 5MB.');
                        input.value = '';
                        previewContainer.addClass('d-none');
                        return;
                    }
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        previewImg.attr('src', e.target.result);
                        previewContainer.removeClass('d-none');
                    };
                    reader.readAsDataURL(file);
                } else {
                    previewContainer.addClass('d-none');
                }
            }

            $('#inputBuktiBefore').on('change', function() {
                previewImage(this, $('#previewBeforeContainer'), $('#imagePreviewBefore'));
            });

            $('#inputBuktiAfter').on('change', function() {
                previewImage(this, $('#previewAfterContainer'), $('#imagePreviewAfter'));
            });

            $('#formUploadBukti').on('submit', function(e) {
                e.preventDefault();
                const beforeFile = $('#inputBuktiBefore')[0].files[0];
                const afterFile = $('#inputBuktiAfter')[0].files[0];
                if (!beforeFile || !afterFile) {
                    alert('Foto Before dan After wajib diupload!');
                    return;
                }
                const fd = new FormData();
                fd.append('_token', $('meta[name="csrf-token"]').attr('content'));
                fd.append('tugas_id', $('#uploadTugasId').val());
                fd.append('bukti_before', beforeFile);
                fd.append('bukti_after', afterFile);
                const btn = $('#btnSubmitUpload');
                const sp = $('#uploadSpinner');
                const txt = $('#btnUploadText');
                btn.prop('disabled', true);
                sp.removeClass('d-none');
                txt.text('Mengupload...');
                $.ajax({
                    url: "{{ route('office.DaftarTugas.uploadBukti') }}",
                    method: 'POST',
                    data: fd,
                    processData: false,
                    contentType: false,
                    success: function() {
                        $('#modalUploadBukti').modal('hide');
                        loadData();
                        showNotification('Berhasil!', 'Bukti berhasil diupload.', 'success');
                    },
                    error: function(x) {
                        showNotification('Gagal', x.responseJSON?.message, 'danger');
                    },
                    complete: function() {
                        btn.prop('disabled', false);
                        sp.addClass('d-none');
                        txt.text('Upload Bukti');
                    }
                });
            });

            $(document).on('change', '.checkStatus', function() {
                const checkbox = $(this);
                const row = checkbox.closest('tr');
                const taskId = checkbox.data('id');

                if (!checkbox.is(':checked')) {
                    $.ajax({
                        url: "{{ route('office.DaftarTugas.updateStatus') }}",
                        method: 'POST',
                        data: {
                            id: taskId,
                            status: 0,
                            _token: $('meta[name="csrf-token"]').attr('content')
                        }
                    });
                    const txt = row.find('.task-text');
                    txt.removeClass('text-decoration-line-through text-muted opacity-50');
                    row.removeClass('bg-light');
                    return;
                }

                const buktiEl = row.find('.bukti-badge');
                if (buktiEl.hasClass('none') || buktiEl.hasClass('partial')) {
                    checkbox.prop('checked', false);
                    showNotification('Peringatan',
                        'Upload foto Before dan After terlebih dahulu sebelum menandai tugas selesai',
                        'warning');
                    return;
                }

                $.ajax({
                    url: "{{ route('office.DaftarTugas.updateStatus') }}",
                    method: 'POST',
                    data: {
                        id: taskId,
                        status: 1,
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function() {
                        const txt = row.find('.task-text');
                        txt.addClass('text-decoration-line-through text-muted opacity-50');
                        row.addClass('bg-light');
                    }
                });
            });
            $(document).on('click', '.btn-hapus', function() {
                const btn = $(this);
                const id = btn.data('id');
                const row = btn.closest('tr');
                if (!confirm('Yakin ingin menghapus tugas ini?')) return;
                const orig = btn.html();
                btn.prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin"></i>');
                $.ajax({
                    url: "{{ route('office.DaftarTugas.delete', '') }}/" + id,
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(r) {
                        row.fadeOut(300, function() {
                            $(this).remove();
                            if (!$('#tbody tr').length) loadData();
                        });
                        showNotification('Berhasil', r.message || 'Tugas berhasil dihapus',
                            'success');
                    },
                    error: function(x) {
                        showNotification('Gagal', x.responseJSON?.message ||
                            'Gagal menghapus tugas.', 'danger');
                        btn.prop('disabled', false).html(orig);
                    }
                });
            });

            $(document).on('click', '.btn-viewBukti', function() {
                const bukti = $(this).data('bukti');
                const judul = $(this).data('judul');
                $('#previewModalTitle').text(judul);
                const body = $('#previewModalBody');
                const modal = new bootstrap.Modal(document.getElementById('modalPreviewBukti'));
                modal.show();
                try {
                    const data = typeof bukti === 'string' ? JSON.parse(bukti) : bukti;
                    const beforeUrl = data.before ? `/storage/${data.before}` : null;
                    const afterUrl = data.after ? `/storage/${data.after}` : null;
                    let html = '<div class="photo-comparison">';
                    html += `<div class="photo-box"><div class="label text-primary">Before</div>`;
                    if (beforeUrl) {
                        const ext = beforeUrl.split('.').pop().toLowerCase();
                        if (['jpg', 'jpeg', 'png', 'gif'].includes(ext)) {
                            html +=
                                `<img src="${beforeUrl}" class="img-fluid rounded shadow bukti-preview" alt="Before">`;
                        } else {
                            html +=
                                `<div class="alert alert-secondary small">File: ${beforeUrl.split('/').pop()}</div>`;
                        }
                    } else {
                        html += `<div class="alert alert-warning small">Foto Before tidak tersedia</div>`;
                    }
                    html += `</div><div class="photo-box"><div class="label text-success">After</div>`;
                    if (afterUrl) {
                        const ext = afterUrl.split('.').pop().toLowerCase();
                        if (['jpg', 'jpeg', 'png', 'gif'].includes(ext)) {
                            html +=
                                `<img src="${afterUrl}" class="img-fluid rounded shadow bukti-preview" alt="After">`;
                        } else {
                            html +=
                                `<div class="alert alert-secondary small">File: ${afterUrl.split('/').pop()}</div>`;
                        }
                    } else {
                        html += `<div class="alert alert-warning small">Foto After tidak tersedia</div>`;
                    }
                    html += `</div></div>`;
                    body.html(html);
                } catch (e) {
                    body.html(
                        '<div class="alert alert-danger">Gagal memuat bukti. Silakan coba lagi.</div>');
                }
            });

            function showNotification(title, msg, type = 'success') {
                $('.custom-toast-container').remove();
                const id = 'toast-' + Date.now();
                const html =
                    `<div class="custom-toast-container position-fixed top-0 end-0 p-3" style="z-index:9999"><div id="${id}" class="toast align-items-center text-white bg-${type} border-0 show"><div class="d-flex"><div class="toast-body"><strong>${title}</strong><br>${msg}</div><button type="button" class="btn-close btn-close-white me-2 m-auto"></button></div></div></div>`;
                $('body').append(html);
                setTimeout(function() {
                    $('#' + id).fadeOut(500, function() {
                        $(this).closest('.custom-toast-container').remove();
                    });
                }, 3500);
            }

            $('#exportReportType').on('change', function() {
                const type = $(this).val();
                if (type === 'kategori') {
                    $('#filterTugasSection').addClass('d-none');
                    $('#filterKategoriSection').removeClass('d-none');
                } else {
                    $('#filterTugasSection').removeClass('d-none');
                    $('#filterKategoriSection').addClass('d-none');
                }
            });

            $('#modalExport').on('show.bs.modal', function() {
                const today = new Date().toISOString().split('T')[0];
                const startOfMonth = new Date(new Date().getFullYear(), new Date().getMonth(), 1)
                    .toISOString().split('T')[0];
                $('input[name="start_date"]').val(startOfMonth);
                $('input[name="end_date"]').val(today);
            });

            $('#checkAllKategori').on('change', function() {
                $('.chk-bulk-kategori').prop('checked', $(this).prop('checked'));
                toggleBulkAction();
            });

            $(document).on('change', '.chk-bulk-kategori', function() {
                toggleBulkAction();
            });

            function toggleBulkAction() {
                const checked = $('.chk-bulk-kategori:checked').length;
                const btn = $('#btnBulkUpdate');
                if (checked > 0) {
                    btn.removeClass('d-none');
                    $('#bulkActionPanel').show();
                } else {
                    btn.addClass('d-none');
                    $('#bulkActionPanel').hide();
                }
            }

            $('#btnBulkUpdate').on('click', function() {
                $('#bulkActionPanel').show();
            });

            $('#cancelBulkUpdate').on('click', function() {
                $('#bulkActionPanel').hide();
                $('#checkAllKategori').prop('checked', false);
                $('.chk-bulk-kategori').prop('checked', false);
                $('#btnBulkUpdate').addClass('d-none');
            });

            $('#confirmBulkUpdate').on('click', function() {
                const ids = [];
                $('.chk-bulk-kategori:checked').each(function() {
                    const tipe = $(this).data('tipe');
                    if (tipe === 'Harian' || tipe === 'Mingguan') {
                        ids.push($(this).val());
                    }
                });
                if (ids.length === 0) {
                    showNotification('Peringatan',
                        'Pilih minimal satu kategori Harian atau Mingguan untuk diupdate',
                        'warning');
                    return;
                }
                const shift = $('#bulkShiftSelect').val();
                const btn = $(this);
                const origText = btn.text();
                btn.prop('disabled', true).text('Memproses...');
                $.ajax({
                    url: "{{ route('office.DaftarTugas.bulkUpdateTipeTurunan') }}",
                    type: 'POST',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content'),
                        ids: ids,
                        tipe_turunan: shift
                    },
                    success: function(r) {
                        if (r.success) {
                            showNotification('Berhasil', r.message, 'success');
                            refreshKategoriTable();
                            $('#bulkActionPanel').hide();
                            $('#checkAllKategori').prop('checked', false);
                            $('.chk-bulk-kategori').prop('checked', false);
                            $('#btnBulkUpdate').addClass('d-none');
                        } else {
                            showNotification('Gagal', r.message, 'danger');
                        }
                    },
                    error: function(xhr) {
                        showNotification('Gagal', xhr.responseJSON?.message ||
                            'Terjadi kesalahan', 'danger');
                    },
                    complete: function() {
                        btn.prop('disabled', false).text(origText);
                    }
                });
            });

            $('#btnLoadChart').on('click', function() {
                loadChartData();
            });

            $('#chartPeriod, #chartKaryawan, #chartStartDate, #chartEndDate').on('change', function() {
                loadChartData();
            });

            // === LOAD AVAILABLE CATEGORIES ===
            function loadAvailableCategories() {
                $('#availableLoading').removeClass('d-none');
                $('#availableList').html('');
                $('#availableActions').hide();

                $.ajax({
                    url: "{{ route('office.DaftarTugas.availableCategories') }}",
                    type: 'GET',
                    success: function(r) {
                        $('#availableCount').text(r.count);

                        if (!r.available || !r.available.length) {
                            $('#availableList').html(
                                `<div class="text-center text-muted py-4">
                        <i class="bx bx-check-circle text-success" style="font-size:2rem"></i>
                        <p class="mt-2 mb-0 small">Semua tugas sudah aktif! 🎉</p>
                    </div>`
                            );
                            return;
                        }

                        // Filter by tipe
                        const filterTipe = $('#filterAvailableTipe').val();
                        const filtered = filterTipe === 'all' ? r.available : r.available.filter(k => k
                            .Tipe === filterTipe);

                        if (!filtered.length) {
                            $('#availableList').html(
                                `<div class="text-center text-muted py-4">
                        <p class="mb-0 small">Tidak ada kategori untuk filter "${filterTipe}"</p>
                    </div>`
                            );
                            $('#availableActions').hide();
                            return;
                        }

                        let html = '<div class="list-group list-group-flush">';
                        filtered.forEach(kat => {
                            const shiftBadge = kat.tipe_turunan ?
                                `<span class="badge bg-secondary ms-1">${kat.tipe_turunan}</span>` :
                                '';
                            const picBadge = kat.karyawan ?
                                `<span class="badge bg-light text-dark border ms-1">${kat.karyawan}</span>` :
                                '';

                            html += `
                    <label class="list-group-item d-flex gap-2 py-3 available-item" style="cursor:pointer">
                        <input class="form-check-input flex-shrink-0 chk-available" type="checkbox" 
                            value="${kat.id}" data-tipe="${kat.Tipe}" data-deadline="${kat.deadline_preview}">
                        <span class="flex-grow-1">
                            <div class="d-flex align-items-center gap-2">
                                <strong class="task-text">${kat.judul_kategori}</strong>
                                <span class="badge ${kat.badge_color}">${kat.Tipe}</span>
                                ${shiftBadge}
                                ${picBadge}
                            </div>
                            <small class="text-muted">
                                <i class="bx bx-time me-1"></i>Deadline: ${kat.deadline_preview}
                            </small>
                        </span>
                    </label>
                `;
                        });
                        html += '</div>';

                        $('#availableList').html(html);
                        $('#availableActions').show();
                        updateSelectedCount();
                    },
                    error: function() {
                        $('#availableList').html(
                            `<div class="alert alert-danger small mb-0">
                    <i class="bx bx-error me-1"></i>Gagal memuat kategori. Silakan coba lagi.
                </div>`
                        );
                    },
                    complete: function() {
                        $('#availableLoading').addClass('d-none');
                    }
                });
            }

            // === UPDATE SELECTED COUNT ===
            function updateSelectedCount() {
                const count = $('.chk-available:checked').length;
                $('#selectedCount').text(count);
                $('#btnActivateSelected').prop('disabled', count === 0);
            }

            // === ACTIVATE SELECTED ===
            function activateSelectedTasks() {
                const ids = [];
                $('.chk-available:checked').each(function() {
                    ids.push($(this).val());
                });

                if (!ids.length) {
                    showNotification('Peringatan', 'Pilih minimal satu kategori untuk diaktifkan', 'warning');
                    return;
                }

                const btn = $('#btnActivateSelected');
                const originalText = btn.html();
                btn.prop('disabled', true).html(
                    '<span class="spinner-border spinner-border-sm"></span> Memproses...');

                $.ajax({
                    url: "{{ route('office.DaftarTugas.aktifkanTugas') }}",
                    type: 'POST',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content'),
                        kategori_ids: ids
                    },
                    success: function(r) {
                        showNotification('Berhasil!', r.message, 'success');
                        loadData(); // Refresh main table
                        loadAvailableCategories(); // Refresh available list
                    },
                    error: function(xhr) {
                        showNotification('Gagal', xhr.responseJSON?.message ||
                            'Gagal mengaktifkan tugas', 'danger');
                    },
                    complete: function() {
                        btn.prop('disabled', false).html(originalText);
                    }
                });
            }

            // === EVENT LISTENERS ===

            // Refresh available categories
            $('#btnRefreshAvailable').on('click', function() {
                loadAvailableCategories();
            });

            // Filter available by tipe
            $('#filterAvailableTipe').on('change', function() {
                // Re-render dari data yang sudah ada atau reload
                loadAvailableCategories();
            });

            // Check all available
            $('#checkAllAvailable').on('change', function() {
                $('.chk-available').prop('checked', $(this).prop('checked'));
                updateSelectedCount();
            });

            // Single checkbox change
            $(document).on('change', '.chk-available', function() {
                updateSelectedCount();
            });

            // Activate button
            $('#btnActivateSelected').on('click', function() {
                activateSelectedTasks();
            });

            // Load on page init
            loadAvailableCategories();
        });
    </script>
@endsection
