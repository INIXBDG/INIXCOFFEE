@extends('layouts_kpi.app')
@section('kpi_contents')
    <link rel="stylesheet" href="{{ asset('assets/vendor/vendorStyle/fontawesome/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/vendorStyle/select2/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/vendorStyle/select2/select2-bootstrap-5-theme.min.css') }}">

    <div class="modal fade" id="detailTargetModal" tabindex="-1" aria-labelledby="detailTargetModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div id="bodyContentDetailTarget" class="p-0"></div>
            </div>
        </div>
    </div>

    <div class="container content-wrapper mt-4">
        <div class="plain-card">
            <div class="card-body">
                <div id="kpi-skeleton-view">
                    <div class="d-flex gap-3 mb-4 flex-wrap">
                        <div class="skeleton" style="width: 160px; height: 42px; border-radius: 10px;"></div>
                        <div class="skeleton" style="width: 160px; height: 42px; border-radius: 10px;"></div>
                        <div class="skeleton ms-auto" style="width: 260px; height: 42px; border-radius: 10px;"></div>
                    </div>

                    <div class="table-responsive">
                        <table class="table modern-table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th><div class="skeleton" style="width: 60px; height: 16px;"></div></th>
                                    <th><div class="skeleton" style="width: 50px; height: 16px;"></div></th>
                                    <th><div class="skeleton" style="width: 50px; height: 16px;"></div></th>
                                    <th><div class="skeleton" style="width: 50px; height: 16px;"></div></th>
                                    <th><div class="skeleton" style="width: 60px; height: 16px;"></div></th>
                                    <th><div class="skeleton" style="width: 50px; height: 16px;"></div></th>
                                    <th><div class="skeleton" style="width: 60px; height: 16px;"></div></th>
                                    <th><div class="skeleton" style="width: 60px; height: 16px;"></div></th>
                                    <th><div class="skeleton" style="width: 60px; height: 16px;"></div></th>
                                    <th><div class="skeleton" style="width: 50px; height: 16px;"></div></th>
                                </tr>
                            </thead>
                            <tbody>
                                @for($i = 0; $i < 10; $i++)
                                <tr class="skeleton-row">
                                    <td><div class="skeleton-text" style="width: 80%"></div></td>
                                    <td><div class="skeleton-text" style="width: 60%"></div></td>
                                    <td><div class="skeleton-text" style="width: 50%"></div></td>
                                    <td><div class="skeleton-text" style="width: 70%"></div></td>
                                    <td><div class="skeleton-text" style="width: 60%"></div></td>
                                    <td><div class="skeleton-text" style="width: 75%"></div></td>
                                    <td><div class="skeleton-text" style="width: 65%"></div></td>
                                    <td><div class="skeleton-text" style="width: 90%"></div></td>
                                    <td><div class="skeleton-text" style="width: 55%"></div></td>
                                    <td><div class="skeleton-text" style="width: 40%"></div></td>
                                </tr>
                                @endfor
                            </tbody>
                        </table>
                    </div>
                </div>


                <div id="kpi-real-view" class="d-none">
                    <div class="action-bar">
                        <button type="button" class="btn-action primary" data-bs-toggle="modal" data-bs-target="#modalBuatTarget">
                            <i class="fa-solid fa-plus"></i> <span>Buat Target Baru</span>
                        </button>
                        @if (Auth()->user()->jabatan === 'Koordinator ITSM')
                            <a href="{{ route('kpi.cleaningDatabase') }}" class="btn-action warning"
                                onclick="return confirm('Apakah Anda BENAR-BENAR yakin ingin menghapus SELURUH data dari tabel database? Tindakan ini permanen dan tidak dapat dibatalkan!');">
                                <i class="fa-solid fa-broom"></i> <span>Database Cleaning</span>
                            </a>
                        @endif
                        <button type="button" class="btn-action success" data-bs-toggle="modal" data-bs-target="#ModalImport">
                            <i class="fa-solid fa-file-import"></i> <span>Import Data</span>
                        </button>

                        <div class="ms-auto" style="min-width: 260px;">
                            <div class="position-relative">
                                <i class="fa-solid fa-magnifying-glass position-absolute" style="left: 1rem; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: .85rem;"></i>
                                <input type="text" id="searchTarget" class="form-control" placeholder="Cari judul, pembuat, divisi..." style="padding-left: 2.5rem;">
                            </div>
                        </div>
                    </div>

                    <div id="dt-space-reserver" class="table-responsive" style="position: relative;">
                        <table class="table modern-table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Judul</th><th>Jangka</th><th>Status</th><th>Target</th>
                                    <th>Jabatan</th><th>Divisi</th><th>Pembuat</th><th>Progress</th>
                                    <th>Tenggat</th><th style="width: 120px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="content_target">
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-end">
                        <div id="paginationContainer"></div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <div class="modal fade" id="ModalImport" tabindex="-1" aria-labelledby="ModalImportLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <form action="{{ route('kpi.importTarget') }}" method="post" enctype="multipart/form-data" id="formImport">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="ModalImportLabel"><span class="title-icon"><i
                                    class="fa-solid fa-file-import"></i></span> Import Data KPI</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div id="importPreview" class="alert alert-info d-none border-0"
                            style="background: rgba(99, 102, 241, .08); color: #6366f1; border-radius: 10px;">
                            <div class="d-flex align-items-center">
                                <i class="fa-solid fa-spinner fa-spin me-2"></i>
                                <span class="fw-semibold">Memproses file...</span>
                            </div>
                        </div>
                        <div class="mb-4">
                            <label for="file" class="form-label"><i
                                    class="fa-solid fa-file-excel text-success me-1"></i> Pilih File Excel/CSV</label>
                            <input type="file" class="form-control @error('file') is-invalid @enderror" id="file"
                                name="file" accept=".xlsx,.xls,.csv" required>
                            @error('file')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted mt-2 d-block"><i class="fa-solid fa-circle-info me-1"></i> Maksimal
                                10MB • Format: .xlsx, .xls, .csv</small>
                        </div>
                        <div class="options-card mb-4">
                            <div class="p-3 border-bottom">
                                <h6 class="fw-semibold mb-0 text-dark"><i class="fa-solid fa-sliders text-primary me-2"></i>
                                    Opsi Import</h6>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="skipDuplicate" name="skip_duplicate"
                                    value="1" checked>
                                <label class="form-check-label" for="skipDuplicate">
                                    <strong>Lewati data duplikat</strong>
                                    <small class="d-block text-muted">Berdasarkan kombinasi judul + pembuat</small>
                                </label>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="dryRun" name="dry_run"
                                    value="1">
                                <label class="form-check-label" for="dryRun">
                                    <strong>Mode preview</strong>
                                    <small class="d-block text-muted">Hanya validasi, tidak simpan ke database</small>
                                </label>
                            </div>
                        </div>
                        <div class="template-download-card mb-4">
                            <div class="icon-circle"><i class="fa-solid fa-file-arrow-down"></i></div>
                            <h6 class="fw-semibold mb-1 text-dark">Butuh Template?</h6>
                            <small class="text-muted d-block mb-3">Download format yang sudah disesuaikan dengan
                                sistem</small>
                            <div class="d-flex gap-2 justify-content-center flex-wrap">
                                <a href="{{ route('kpi.downloadTemplate') }}" class="btn-action success" download>
                                    <i class="fa-solid fa-download"></i> <span>Download Template</span>
                                </a>
                                <button type="button" class="btn-action"
                                    style="background: #fff; border: 1px solid #e2e8f0; color: #475569;"
                                    data-bs-toggle="modal" data-bs-target="#modalPreviewTemplate">
                                    <i class="fa-solid fa-eye"></i> <span>Lihat Contoh</span>
                                </button>
                            </div>
                        </div>
                        <div id="errorSummary" class="d-none">
                            <div class="alert mb-0 py-2"
                                style="background: rgba(245, 158, 11, .1); color: #b45309; border: 1px solid rgba(245, 158, 11, .2); border-radius: 10px;">
                                <strong><i class="fa-solid fa-triangle-exclamation me-1"></i> Ditemukan error:</strong>
                                <ul class="mb-0 mt-2 small ps-3" style="max-height: 150px; overflow-y: auto;"></ul>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn-action primary" id="btnSubmitImport">
                            <i class="fa-solid fa-upload"></i> <span>Import Sekarang</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalPreviewTemplate" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><span class="title-icon"><i class="fa-solid fa-table-cells"></i></span>
                        Format Kolom Template</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="info-box mb-4">
                        <div class="d-flex align-items-start">
                            <i class="fa-solid fa-circle-info me-2 mt-1" style="font-size: 1.1rem;"></i>
                            <div>
                                <strong class="text-dark">Catatan Penting</strong>
                                <p class="mb-0 mt-1 small">Kolom <code>Tipe Target</code>, <code>Nilai Target</code>, dan
                                    <code>Jangka Target</code> <u>tidak perlu diisi</u> karena akan diambil otomatis dari
                                    konfigurasi database berdasarkan <strong>Assistant Route</strong>.</p>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table template-table table-bordered align-middle">
                            <thead>
                                <tr>
                                    <th style="width: 18%;">Kolom</th>
                                    <th style="width: 8%;" class="text-center">Wajib</th>
                                    <th style="width: 30%;">Format/Contoh</th>
                                    <th>Keterangan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>Judul KPI</strong></td>
                                    <td class="text-center"><span class="badge bg-success">✅</span></td>
                                    <td><code>"Meningkatkan Revenue"</code></td>
                                    <td>Maksimal 255 karakter</td>
                                </tr>
                                <tr>
                                    <td><strong>Deskripsi</strong></td>
                                    <td class="text-center"><span class="badge bg-light text-muted border">❌</span></td>
                                    <td><code>"Target penjualan Q1"</code></td>
                                    <td>Opsional, maksimal 500 karakter</td>
                                </tr>
                                <tr>
                                    <td><strong>Jabatan</strong></td>
                                    <td class="text-center"><span class="badge bg-success">✅</span></td>
                                    <td><code>"Sales"</code> atau <code>"Sales, SPV Sales"</code></td>
                                    <td>Pisahkan dengan koma jika multiple. Harus sesuai database.</td>
                                </tr>
                                <tr>
                                    <td><strong>Karyawan</strong></td>
                                    <td class="text-center"><span class="badge bg-light text-muted border">❌</span></td>
                                    <td><code>"Budi Santoso, Siti Aminah"</code></td>
                                    <td>Nama lengkap sesuai database. Jika kosong, semua karyawan di jabatan akan dipilih.
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Assistant Route</strong></td>
                                    <td class="text-center"><span class="badge bg-success">✅</span></td>
                                    <td><code>"target penjualan tahunan"</code></td>
                                    <td><strong class="text-primary">Wajib sesuai</strong> dengan route yang terdaftar di
                                        sistem.</td>
                                </tr>
                                <tr>
                                    <td><strong>Detail Jangka</strong></td>
                                    <td class="text-center"><span class="badge bg-warning text-white">✅*</span></td>
                                    <td><code>"2024"</code>, <code>"2025"</code></td>
                                    <td><strong>Wajib hanya jika</strong> Assistant Route bertipe "Tahunan". Format: 4 digit
                                        tahun.</td>
                                </tr>
                                <tr class="table-secondary">
                                    <td><em>Tipe Target</em></td>
                                    <td class="text-center"><span class="badge bg-light text-muted">Auto</span></td>
                                    <td><em>-</em></td>
                                    <td><em>Diambil dari database berdasarkan Assistant Route</em></td>
                                </tr>
                                <tr class="table-secondary">
                                    <td><em>Nilai Target</em></td>
                                    <td class="text-center"><span class="badge bg-light text-muted">Auto</span></td>
                                    <td><em>-</em></td>
                                    <td><em>Diambil dari database berdasarkan Assistant Route</em></td>
                                </tr>
                                <tr class="table-secondary">
                                    <td><em>Jangka Target</em></td>
                                    <td class="text-center"><span class="badge bg-light text-muted">Auto</span></td>
                                    <td><em>-</em></td>
                                    <td><em>Diambil dari database berdasarkan Assistant Route</em></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4 p-3" style="background: #f8fafc; border-radius: 12px; border: 1px solid #e2e8f0;">
                        <h6 class="fw-semibold mb-2 text-dark"><i
                                class="fa-solid fa-lightbulb text-warning me-2"></i>Contoh Baris Valid</h6>
                        <code class="d-block p-3 bg-white border rounded" style="font-size: .85rem;">
                            Judul: "Target Q1 2024"<br>
                            Jabatan: "Sales"<br>
                            Assistant Route: "target penjualan tahunan"<br>
                            Detail Jangka: "2024"
                        </code>
                        <small class="text-muted mt-2 d-block"><i class="fa-solid fa-arrow-right me-1"></i> Sistem akan
                            otomatis mengambil: tipe=rupiah, nilai=1000000, jangka=Tahunan dari database</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-action success" data-bs-toggle="modal"
                        data-bs-target="#ModalImport">
                        <i class="fa-solid fa-arrow-left"></i> <span>Kembali ke Import</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalBuatTarget" tabindex="-1" role="dialog" aria-labelledby="modalBuatTargetLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <form action="{{ route('kpi.createTarget') }}" method="post" id="targetForm">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalBuatTargetLabel"><span class="title-icon"><i
                                    class="fa-solid fa-bullseye"></i></span> Buat Target Divisi Anda</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body" id="modal-content-form">
                        <input type="hidden" name="id_pembuat" value="{{ auth()->user()->id }}">
                        <div class="mb-4">
                            <label for="judul_kpi" class="form-label"><i
                                    class="fa-solid fa-heading text-primary me-1"></i> Judul KPI <span
                                    class="text-warning">*</span></label>
                            <input type="text" name="judul_kpi" id="judul_kpi" class="form-control"
                                placeholder="Contoh: Peningkatan Penjualan Produk A" required>
                        </div>
                        <div class="mb-4">
                            <label for="deskripsi_kpi" class="form-label"><i
                                    class="fa-solid fa-align-left text-primary me-1"></i> Deskripsi KPI</label>
                            <textarea name="deskripsi_kpi" id="deskripsi_kpi" class="form-control" rows="2"
                                placeholder="Jelaskan tujuan atau konteks dari target ini..."></textarea>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="assistant_route" class="form-label"><i
                                        class="fa-solid fa-route text-primary me-1"></i> Pilih Assistant Route <span
                                        class="text-warning">*</span></label>
                                <select name="asistant_route" id="assistant_route" class="form-select" required>
                                    <option selected disabled>-- Pilih Assistant Route --</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="jabatan" class="form-label"><i
                                        class="fa-solid fa-user-tie text-primary me-1"></i> Pilih Jabatan <span
                                        class="text-warning">*</span></label>
                                <select name="jabatan[]" id="jabatan" class="form-select select2" multiple></select>
                            </div>
                            <div class="col-12">
                                <label for="karyawan" class="form-label"><i
                                        class="fa-solid fa-users text-primary me-1"></i> Pilih Karyawan <small
                                        class="text-muted fw-normal">(Opsional - akan terisi otomatis sesuai
                                        jabatan)</small></label>
                                <select name="karyawan[]" id="karyawan" class="form-select select2" multiple></select>
                            </div>
                        </div>
                        <div class="mt-4 p-3"
                            style="background: #f8fafc; border-radius: 12px; border: 1px dashed #cbd5e1;">
                            <label class="form-label mb-2"><i
                                    class="fa-solid fa-magic-wand-sparkles text-primary me-1"></i> <strong>Detail
                                    Konfigurasi</strong> <span class="badge bg-primary bg-opacity-10 text-primary ms-1"
                                    style="font-size: .7rem;">Auto-filled</span></label>
                            <div class="row g-2">
                                <div class="col-md-4">
                                    <input type="text" id="jangka_target_display" class="form-control auto-field"
                                        readonly placeholder="Jangka Target">
                                    <input type="hidden" name="jangka_target" id="jangka_target_hidden">
                                </div>
                                <div class="col-md-4">
                                    <input type="text" id="tipe_target_display" class="form-control auto-field"
                                        readonly placeholder="Tipe Target">
                                    <input type="hidden" name="tipe_target" id="tipe_target_hidden">
                                </div>
                                <div class="col-md-4">
                                    <input type="text" id="nilai_target_display" class="form-control auto-field"
                                        readonly placeholder="Nilai Target">
                                    <input type="hidden" name="nilai_target" id="nilai_target_hidden">
                                </div>
                            </div>
                        </div>
                        <div id="detail_jangka_container" class="mt-4"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal"><i
                                class="fa-solid fa-xmark me-1"></i> Batal</button>
                        <button type="submit" class="btn-action primary"><i class="fa-solid fa-save"></i> <span>Simpan
                                Target</span></button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalFormManual" tabindex="-1" role="dialog" aria-labelledby="modalFormManualLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-md modal-dialog-scrollable" role="document">
            <form id="formManualValue" enctype="multipart/form-data">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalFormManualLabel"><span class="title-icon"><i
                                    class="fa-solid fa-pen-to-square"></i></span> Isi Manual Target</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id" id="manualValueId">
                        <div class="mb-4">
                            <label class="form-label"><i class="fa-solid fa-list text-primary me-1"></i> Format
                                Nilai</label>
                            <select class="form-select" id="manual_format">
                                <option value="angka">Angka</option>
                                <option value="persen">Persen (%)</option>
                                <option value="rupiah">Rupiah (Rp)</option>
                            </select>
                        </div>
                        <div id="doubleInputArea" style="display:none;">
                            <div class="mb-3"><label class="form-label">Biaya Gaji Tahunan</label><input type="text"
                                    class="form-control" id="biaya_gaji_display"><input type="hidden"
                                    name="biaya_gaji_tahunan" id="biaya_gaji_tahunan" required></div>
                            <div class="mb-3"><label class="form-label">Biaya BPJS Tahunan</label><input type="text"
                                    class="form-control" id="biaya_bpjs_display"><input type="hidden"
                                    name="biaya_bpjs_tahunan" id="biaya_bpjs_tahunan" required></div>
                            <div class="mb-3"><label class="form-label">Biaya Rekrutmen Tahunan</label><input
                                    type="text" class="form-control" id="biaya_rekrutmen_display"><input
                                    type="hidden" name="biaya_rekrutmen_tahunan" id="biaya_rekrutmen_tahunan" required>
                            </div>
                        </div>
                        <div class="mb-4" id="singleInputArea">
                            <label class="form-label">Masukan Nilai</label>
                            <input type="text" class="form-control" id="manual_value_display">
                            <input type="hidden" name="manual_value" id="manual_value">
                        </div>
                        <div class="mb-3">
                            <label class="form-label"><i class="fa-solid fa-paperclip text-primary me-1"></i> Dokumen
                                Pendukung</label>
                            <input type="file" class="form-control" name="manual_document" id="manual_document"
                                accept="image/*,.pdf">
                        </div>
                        <div id="documentPreview" class="mt-3"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal"><i
                                class="fa-solid fa-xmark me-1"></i> Tutup</button>
                        <button type="submit" class="btn-action primary"><i class="fa-solid fa-check"></i>
                            <span>Simpan</span></button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <script src="{{ asset('assets/vendor/libs/chartjs/chart.js') }}"></script>
    <script>
        window.KPI_CONFIG = {
            // Routes
            getDataTarget: "{{ route('kpi.getDataTarget') }}",
            getKaryawanByJabatan: "{{ route('kpi.getKaryawanByJabatan') }}",
            getRoutesByJabatan: "{{ route('kpi.getRoutesByJabatan') }}",
            getTargetByRoute: "{{ route('kpi.getTargetByRoute') }}",
            detail: "{{ route('kpi.detail') }}",
            updateTargetPerSales: "{{ route('kpi.overview.updateTargetPerSales') }}",
            updateGapKompetensi: "{{ route('kpi.updateGapKompetensi') }}",
            manualValue: "{{ route('kpi.manualValue') }}",
            assistantRoutes: "{{ route('kpi.assistantRoutes') }}",
            hapusTarget: "{{ route('kpi.dataTarget.destroy', ['id' => 'REPLACE_ID']) }}",
            
            // Tokens & Dates
            csrfToken: "{{ csrf_token() }}",
            dateNow: "{{ now()->format('Y-m-d') }}",
            startOfYear: "{{ now()->startOfYear()->format('Y-m-d') }}",
            
            // Session & Auth
            importErrors: @json(session('import_errors')),
            isKoordinatorItsm: {{ auth()->user()->jabatan === 'Koordinator ITSM' ? 'true' : 'false' }},
            storageBase: "{{ asset('storage') }}"
        };

        // Fix untuk route hapusTarget agar bisa diganti ID-nya di JS
        window.KPI_CONFIG.hapusTarget = window.KPI_CONFIG.hapusTarget.replace('__ID__', 'REPLACE_ID');
    </script>

    {{-- ===== EXTERNAL JS FILE ===== --}}
    <script src="{{ asset('assets/js/kpi/kpiIndex.js') }}"></script>
@endsection
