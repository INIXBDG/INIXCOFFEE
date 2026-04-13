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
                @if (Auth::user()->jabatan === 'Office Boy')
                    <button class="btn btn-success px-4 shadow-sm d-flex align-items-center gap-2" data-bs-toggle="modal"
                        data-bs-target="#pilihTugasModal">
                        <i class="bx bx-list-plus"></i>Pilih Tugas
                    </button>
                @endif
                <button class="btn btn-primary px-4 shadow-sm d-flex align-items-center gap-2" data-bs-toggle="modal"
                    data-bs-target="#createModal">
                    <i class="bx bx-plus"></i>Buat Kategori Baru
                </button>
                <div class="btn-group">
                    <button class="btn btn-outline-success px-3 shadow-sm d-flex align-items-center gap-2" type="button"
                        data-bs-toggle="dropdown">
                        <i class="bx bx-file-export"></i> Export
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <h6 class="dropdown-header">Tipe Laporan</h6>
                        </li>
                        <li>
                            <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#modalExport">
                                <i class="bx bx-cog me-2"></i> Export dengan Filter
                            </a>
                        </li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li>
                            <a class="dropdown-item"
                                href="{{ route('office.DaftarTugas.export.excel', ['report_type' => 'tugas']) }}">
                                <i class="bx bx-file-excel text-success me-2"></i> Excel - Tugas
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item"
                                href="{{ route('office.DaftarTugas.export.pdf', ['report_type' => 'tugas']) }}">
                                <i class="bx bx-file-pdf text-danger me-2"></i> PDF - Tugas
                            </a>
                        </li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li>
                            <a class="dropdown-item"
                                href="{{ route('office.DaftarTugas.export.excel', ['report_type' => 'kategori']) }}">
                                <i class="bx bx-file-excel text-success me-2"></i> Excel - Kategori
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item"
                                href="{{ route('office.DaftarTugas.export.pdf', ['report_type' => 'kategori']) }}">
                                <i class="bx bx-file-pdf text-danger me-2"></i> PDF - Kategori
                            </a>
                        </li>
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
                            <input type="date" id="filterTanggal" class="form-control form-control-sm" style="width:auto"
                                value="{{ now()->format('Y-m-d') }}">
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
                                <th class="border-0" style="width:20%">Tipe</th>
                                <th class="border-0" style="width:15%">Deadline</th>
                                <th class="border-0 text-center" style="width:20%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="tbody"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="pilihTugasModal" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Pilih Tugas untuk Dikerjakan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Filter Tipe</label>
                        <select id="filterKategoriTipe" class="form-select form-select-sm">
                            <option value="all">Semua</option>
                            <option value="Harian">Harian</option>
                            <option value="Mingguan">Mingguan</option>
                            <option value="Bulanan">Bulanan</option>
                            <option value="Quartal">Quartal</option>
                            <option value="Semester">Semester</option>
                            <option value="Tahunan">Tahunan</option>
                        </select>
                    </div>
                    <div id="kategoriList" class="row g-2">
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-success" id="btnAktifkanTugas">
                        <span class="spinner-border spinner-border-sm d-none" id="aktifkanSpinner"></span>
                        Aktifkan Tugas Terpilih
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
                                <select name="jabatan_pembuat" required class="form-select">
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
                            <select name="Tipe" required class="form-select">
                                <option value="" disabled selected>Pilih Tipe</option>
                                <option value="Harian">Harian</option>
                                <option value="Mingguan">Mingguan</option>
                                <option value="Bulanan">Bulanan</option>
                                <option value="Quartal">Quartal</option>
                                <option value="Semester">Semester</option>
                                <option value="Tahunan">Tahunan</option>
                            </select>
                        </div>
                        <hr class="my-4">
                        <h6 class="mb-3 fw-semibold"><i class="bx bx-list-ul me-2"></i>Daftar Kategori Saat Ini</h6>
                        <div style="max-height:300px;overflow-y:auto;border:1px solid #eee;border-radius:8px">
                            <table class="table table-sm table-bordered mb-0" id="tabelKategori">
                                <thead class="table-light">
                                    <tr>
                                        <th>Tugas</th>
                                        <th width="100">Tipe</th>
                                        <th>PIC</th>
                                        <th width="130">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($dataKategori as $data)
                                        <tr data-id="{{ $data->id }}">
                                            <td>{{ $data->judul_kategori }}</td>
                                            <td><span class="badge bg-info text-dark">{{ $data->Tipe }}</span></td>
                                            <td>{{ $data->karyawan->nama_lengkap ?? '-' }}</td>
                                            <td>
                                                <div class="btn-group btn-group-sm w-100">
                                                    <button type="button"
                                                        class="btn btn-outline-primary btn-edit-kategori"
                                                        data-id="{{ $data->id }}"
                                                        data-judul="{{ $data->judul_kategori }}"
                                                        data-tipe="{{ $data->Tipe }}"
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
                                            <td colspan="4" class="text-center py-3 text-muted">Belum ada kategori.
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
                        @if (Auth::user()->jabatan === 'HRD')
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Penanggung Jawab</label>
                                <select name="jabatan_pembuat" required class="form-select">
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
                            <label class="form-label fw-semibold">File Bukti <small class="text-muted">(Max:
                                    5MB)</small></label>
                            <input type="file" class="form-control" name="bukti_file" id="inputBuktiFile"
                                accept="image/*,.pdf,.doc,.docx" required>
                            <div class="form-text">Format: JPG, PNG, PDF, DOC, DOCX</div>
                        </div>
                        <div id="previewContainer" class="d-none text-center mt-3 p-2 bg-light rounded border">
                            <img id="imagePreview" src="" class="img-fluid rounded shadow-sm"
                                style="max-height:200px">
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
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="previewModalTitle">Detail Bukti</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center bg-light p-5" id="previewModalBody" style="min-height:400px">
                    <div class="spinner-border text-primary" role="status"></div>
                </div>
                <div class="modal-footer">
                    <a href="#" id="previewDownloadLink" target="_blank" class="btn btn-outline-primary btn-sm"><i
                            class="bx bx-download me-1"></i>Download</a>
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalExport" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form id="formExport" method="GET">
                    <div class="modal-header bg-primary text-white">
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
                                <div class="col-6">
                                    <label class="form-label small">Tanggal Mulai</label>
                                    <input type="date" name="start_date" class="form-control form-control-sm">
                                </div>
                                <div class="col-6">
                                    <label class="form-label small">Tanggal Akhir</label>
                                    <input type="date" name="end_date" class="form-control form-control-sm">
                                </div>
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
                            formaction="{{ route('office.DaftarTugas.export.excel') }}" formtarget="_blank">
                            <i class="bx bx-file-excel me-1"></i> Excel
                        </button>
                        <button type="submit" class="btn btn-danger btn-sm"
                            formaction="{{ route('office.DaftarTugas.export.pdf') }}" formtarget="_blank">
                            <i class="bx bx-file-pdf me-1"></i> PDF
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            const today = new Date().toISOString().split('T')[0];
            $('#filterTanggal').val(today);
            let selectedCategories = [];

            function updateTitle() {
                const t = $('#filterTipe').val();
                const d = $('#filterTanggal').val();
                const dt = new Date(d + 'T00:00:00');
                const tipeText = t === 'all' ? 'Semua Tipe' : t;
                $('#dynamicTitle').text(
                    `Tugas Aktif ${tipeText} - ${dt.toLocaleDateString('id-ID', {weekday:'long',year:'numeric',month:'long',day:'numeric'})}`
                );
            }

            function loadData() {
                $.ajax({
                    url: "{{ route('office.DaftarTugas.get') }}",
                    type: 'GET',
                    data: {
                        tipe: $('#filterTipe').val(),
                        tanggal: $('#filterTanggal').val()
                    },
                    success: function(r) {
                        const tb = $('#tbody');
                        tb.empty();
                        if (!r.data || !r.data.length) {
                            tb.append(
                                `<tr><td colspan="5" class="text-center py-5"><div class="d-flex flex-column align-items-center gap-3"><div class="bg-light rounded-circle p-4"><i class="bx bx-clipboard text-muted" style="font-size:3rem"></i></div><h5 class="text-muted mb-1">Belum ada Tugas Aktif</h5><p class="text-muted small mb-3">Pilih tugas dari kategori yang tersedia untuk mulai mengerjakan</p><button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#pilihTugasModal"><i class="bx bx-list-plus me-1"></i>Pilih Tugas</button></div></td></tr>`
                            );
                            return;
                        }
                        r.data.forEach(function(it) {
                            const kat = it.kategori_daftar_tugas?.judul_kategori ||
                                'Tanpa Kategori';
                            const tipe = it.kategori_daftar_tugas?.Tipe || '-';
                            const dl = it.Deadline_Date || '-';
                            const chk = it.status == 1 ? 'checked' : '';
                            const done = it.status == 1 ?
                                'text-decoration-line-through text-muted opacity-50' : '';
                            const bukti = it.bukti ?
                                `<button class="btn btn-success btn-sm btn-viewBukti" data-bukti="/storage/${it.bukti}" data-judul="${kat.replace(/"/g,'&quot;')}"><i class="bx bx-show"></i>Lihat</button>` :
                                `<button class="btn btn-primary btn-sm btn-uploadBukti" data-id="${it.id}" data-judul="${kat.replace(/"/g,'&quot;')}"><i class="bx bx-upload"></i>Bukti</button>`;
                            tb.append(
                                `<tr class="${done?'bg-light':''}"><td class="ps-4"><div class="form-check"><input class="form-check-input checkStatus" type="checkbox" data-id="${it.id}" ${chk}></div></td><td class="task-text ${done} fw-medium">${kat}</td><td class="task-text ${done}"><span class="badge bg-secondary">${tipe}</span></td><td class="task-text ${done} small">${dl}</td><td class="text-center"><div class="btn-group"><button class="btn btn-outline-danger btn-sm btn-hapus" data-id="${it.id}"><i class="bx bx-trash"></i></button>${bukti}</div></td></tr>`
                            );
                        });
                    }
                });
            }

            function loadKategoriList() {
                $.ajax({
                    url: "{{ route('office.DaftarTugas.getKategori') }}",
                    type: 'GET',
                    success: function(d) {
                        const container = $('#kategoriList');
                        container.empty();
                        const filtered = d.filter(it => $('#filterKategoriTipe').val() === 'all' || it
                            .Tipe === $('#filterKategoriTipe').val());
                        if (!filtered.length) {
                            container.append(
                                '<div class="col-12 text-center py-4 text-muted">Tidak ada kategori tersedia</div>'
                                );
                            return;
                        }
                        filtered.forEach(function(it) {
                            const isSelected = selectedCategories.includes(it.id);
                            container.append(
                                `<div class="col-md-6 col-lg-4"><div class="card category-card ${isSelected?'selected':''}" data-id="${it.id}"><div class="card-body p-3"><div class="d-flex justify-content-between align-items-start"><div><h6 class="card-title mb-1 fw-semibold">${it.judul_kategori}</h6><span class="badge bg-info text-dark">${it.Tipe}</span></div><div class="form-check"><input class="form-check-input chk-kategori" type="checkbox" data-id="${it.id}" ${isSelected?'checked':''}></div></div><div class="mt-2 small text-muted">${it.karyawan?.nama_lengkap||'-'}</div></div></div></div>`
                            );
                        });
                    }
                });
            }

            function refreshKategoriTable() {
                $.ajax({
                    url: "{{ route('office.DaftarTugas.getKategori') }}",
                    type: 'GET',
                    success: function(d) {
                        const tb = $('#tabelKategori tbody');
                        tb.empty();
                        if (!d.length) {
                            tb.append(
                                '<tr><td colspan="4" class="text-center py-3 text-muted">Belum ada kategori.</td></tr>'
                                );
                            return;
                        }
                        d.forEach(function(it) {
                            tb.append(
                                `<tr data-id="${it.id}"><td>${it.judul_kategori}</td><td><span class="badge bg-info text-dark">${it.Tipe}</span></td><td>${it.karyawan?.nama_lengkap||'-'}</td><td><div class="btn-group btn-group-sm w-100"><button class="btn btn-outline-primary btn-edit-kategori" data-id="${it.id}" data-judul="${it.judul_kategori}" data-tipe="${it.Tipe}" data-user="${it.karyawan?.nama_lengkap||'N/A'}"><i class="bx bx-edit"></i></button><button class="btn btn-outline-danger btn-delete-kategori" data-id="${it.id}" data-judul="${it.judul_kategori}"><i class="bx bx-trash"></i></button></div></td></tr>`
                            );
                        });
                    }
                });
            }

            loadData();
            updateTitle();

            $('#filterTipe,#filterTanggal').on('change', function() {
                updateTitle();
                loadData();
            });
            $('#btnResetFilter').on('click', function() {
                $('#filterTipe').val('all');
                $('#filterTanggal').val(today);
                updateTitle();
                loadData();
            });
            $('#filterKategoriTipe').on('change', loadKategoriList);

            $('#pilihTugasModal').on('show.bs.modal', function() {
                selectedCategories = [];
                loadKategoriList();
            });

            $(document).on('click', '.category-card', function() {
                const id = $(this).data('id');
                const chk = $(this).find('.chk-kategori');
                chk.prop('checked', !chk.prop('checked'));
                $(this).toggleClass('selected', chk.prop('checked'));
                if (chk.prop('checked')) {
                    if (!selectedCategories.includes(id)) selectedCategories.push(id);
                } else {
                    selectedCategories = selectedCategories.filter(i => i !== id);
                }
            });

            $(document).on('change', '.chk-kategori', function() {
                const id = $(this).data('id');
                const card = $(this).closest('.category-card');
                card.toggleClass('selected', $(this).prop('checked'));
                if ($(this).prop('checked')) {
                    if (!selectedCategories.includes(id)) selectedCategories.push(id);
                } else {
                    selectedCategories = selectedCategories.filter(i => i !== id);
                }
            });

            $('#btnAktifkanTugas').on('click', function() {
                if (!selectedCategories.length) {
                    showNotification('Peringatan', 'Pilih minimal satu kategori tugas', 'warning');
                    return;
                }
                const btn = $(this);
                const sp = $('#aktifkanSpinner');
                btn.prop('disabled', true);
                sp.removeClass('d-none');
                $.ajax({
                    url: "{{ route('office.DaftarTugas.aktifkanTugas') }}",
                    type: 'POST',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content'),
                        kategori_ids: selectedCategories
                    },
                    success: function(r) {
                        if (r.success) {
                            showNotification('Berhasil!', r.message, 'success');
                            bootstrap.Modal.getInstance(document.getElementById(
                                'pilihTugasModal')).hide();
                            loadData();
                        } else {
                            showNotification('Gagal', r.message, 'danger');
                        }
                    },
                    error: function(xhr) {
                        showNotification('Gagal', xhr.responseJSON?.message ||
                            'Terjadi kesalahan', 'danger');
                    },
                    complete: function() {
                        btn.prop('disabled', false);
                        sp.addClass('d-none');
                    }
                });
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
                const user = $(this).data('user');
                $('#edit_id').val(id);
                $('#edit_judul').val(judul);
                $('#edit_tipe').val(tipe);
                const modal = new bootstrap.Modal(document.getElementById('modalEditKategori'));
                modal.show();
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
                $('#inputBuktiFile').val('');
                $('#previewContainer').addClass('d-none');
                new bootstrap.Modal(document.getElementById('modalUploadBukti')).show();
            });

            $(document).on('change', '#inputBuktiFile', function(e) {
                const f = e.target.files[0];
                if (f && f.type.startsWith('image/')) {
                    if (f.size > 5 * 1024 * 1024) {
                        alert('Ukuran file terlalu besar! Maksimal 5MB.');
                        $(this).val('');
                        $('#previewContainer').addClass('d-none');
                        return;
                    }
                    const r = new FileReader();
                    r.onload = e => {
                        $('#imagePreview').attr('src', e.target.result);
                        $('#previewContainer').removeClass('d-none');
                    };
                    r.readAsDataURL(f);
                } else {
                    $('#previewContainer').addClass('d-none');
                }
            });

            $('#formUploadBukti').on('submit', function(e) {
                e.preventDefault();
                const fd = new FormData(this);
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
                const id = $(this).data('id');
                const st = $(this).is(':checked') ? 1 : 0;
                const row = $(this).closest('tr');
                $.ajax({
                    url: "{{ route('office.DaftarTugas.updateStatus') }}",
                    method: 'POST',
                    data: {
                        id: id,
                        status: st,
                        _token: $('meta[name="csrf-token"]').attr('content')
                    }
                });
                const txt = row.find('.task-text');
                if (st === 1) {
                    txt.addClass('text-decoration-line-through text-muted opacity-50');
                    row.addClass('bg-light');
                } else {
                    txt.removeClass('text-decoration-line-through text-muted opacity-50');
                    row.removeClass('bg-light');
                }
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
                body.html('<div class="spinner-border text-primary" role="status"></div>');
                const modal = new bootstrap.Modal(document.getElementById('modalPreviewBukti'));
                modal.show();
                const ext = bukti.split('.').pop().toLowerCase();
                if (['jpg', 'jpeg', 'png', 'gif'].includes(ext)) {
                    body.html(
                        `<img src="${bukti}" class="img-fluid rounded shadow" style="max-height:500px">`
                        );
                    $('#previewDownloadLink').attr('href', bukti).show();
                } else if (ext === 'pdf') {
                    body.html(
                        `<iframe src="${bukti}" width="100%" height="400px" class="border rounded"></iframe>`
                        );
                    $('#previewDownloadLink').attr('href', bukti).show();
                } else {
                    body.html(
                        `<div class="d-flex flex-column align-items-center gap-3"><i class="bx bx-file text-primary" style="font-size:3rem"></i><p class="mb-0">File: ${bukti.split('/').pop()}</p></div>`
                        );
                    $('#previewDownloadLink').attr('href', bukti).show();
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
        });
    </script>
@endsection
