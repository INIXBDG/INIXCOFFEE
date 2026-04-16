@extends('layouts_office.app')

@section('office_contents')
    <div class="container mt-3">

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="card border-0 shadow-sm rounded-4 mb-4 glass-force">
            <div class="card-body p-4">
                <div
                    class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center gap-4">
                    <div>
                        <h3 class="mb-2 fw-bold text-dark">{{ $nomor->no_modul }}</h3>
                        <p class="text-muted fs-5 mb-0">{{ $nomor->type }}</p>
                    </div>
                    <div class="d-flex flex-column flex-sm-row gap-3 align-items-end align-items-sm-center">
                        @if ($nomor->type == 'Authorize')
                            <button type="button" class="btn btn-outline-secondary btn-sm btnPdfPeserta"
                                data-id="{{ $nomor->id }}" data-note="{{ $nomor->note_peserta }}" data-bs-toggle="modal"
                                data-bs-target="#modalNotePeserta">
                                PDF Peserta
                            </button>

                            <button type="button" class="btn btn-outline-success btn-sm btnExcelPeserta"
                                data-id="{{ $nomor->id }}" data-note="{{ $nomor->note_peserta }}" data-bs-toggle="modal"
                                data-bs-target="#modalExcelPeserta">
                                <i class="fas fa-file-excel"></i> Excel Peserta
                            </button>

                            <button type="button" class="btn btn-outline-secondary btn-sm pdfBtn"
                                data-id="{{ $nomor->id }}" data-note="{{ $nomor->note_modul }}" data-bs-toggle="modal"
                                data-bs-target="#noteModal">
                                PDF Modul
                            </button>
                        @endif
                        @if ($nomor->type == 'Regular')
                            <button type="button" class="btn btn-outline-secondary btn-sm pdfBtn"
                                data-id="{{ $nomor->id }}" data-note="{{ $nomor->note_modul }}" data-bs-toggle="modal"
                                data-bs-target="#noteModal">
                                PDF Modul
                            </button>
                        @endif
                        <span
                            class="badge fs-5 px-4 py-3 {{ $nomor->status === 'active' ? 'bg-success' : 'bg-secondary' }}">
                            {{ ucfirst($nomor->status) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#modalTambah">
            <i class="fas fa-plus"></i> Tambah Modul
        </button>
        @if ($nomor->type == 'Authorize')
            <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#modalPeserta">
                <i class="fas fa-plus"></i> Tambah Peserta
            </button>
        @endif

        @if (Auth::user()->jabatan === 'Finance & Accounting')
            <form action="{{ route('office.modul.update.status', $nomor->id) }}" method="POST" class="d-inline">
                @csrf
                @method('PUT')
                <button type="submit" class="btn btn-success mb-3">
                    <i class="fas fa-check"></i> Setujui
                </button>
            </form>
        @endif

        <div class="card glass-force">
            <div class="card-body">
                <h5 class="mb-2">Daftar Modul</h5>
                <table class="table table-bordered table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Kode Materi</th>
                            <th>Nama Materi</th>
                            <th>Periode</th>
                            <th>Jumlah</th>
                            <th>Harga Satuan</th>
                            <th>Total</th>
                            <th>Note</th>
                            <th width="120">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($modul as $m)
                            <tr data-id="{{ $m->id }}" data-materi_id="{{ $m->materi_id }}"
                                data-nomodul="{{ $m->no_modul }}" data-kode_materi="{{ $m->kode_materi }}"
                                data-nama_materi="{{ $m->nama_materi }}" data-awal="{{ $m->awal_training }}"
                                data-akhir="{{ $m->akhir_training }}" data-jumlah="{{ $m->jumlah }}"
                                data-harga_satuan="{{ $m->harga_satuan }}" data-total="{{ $m->total }}"
                                data-note="{{ $m->note ?? '' }}">
                                <td>{{ $m->kode_materi ?? '-' }}</td>
                                <td>{{ $m->nama_materi }}</td>
                                <td>{{ \Carbon\Carbon::parse($m->awal_training)->translatedFormat('d M Y') }} s/d
                                    {{ \Carbon\Carbon::parse($m->akhir_training)->translatedFormat('d M Y') }}</td>
                                <td>{{ $m->jumlah }}</td>
                                <td>Rp {{ number_format($m->harga_satuan, 0, ',', '.') }}</td>
                                <td>Rp {{ number_format($m->total, 0, ',', '.') }}</td>
                                <td class="text-center">{{ $m->note ?? '-' }}</td>
                                <td>
                                    <button class="btn btn-warning btn-sm btnEdit" data-bs-toggle="modal"
                                        data-bs-target="#modalEdit">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>

                                    <form action="{{ route('office.modul.delete', ['id' => $m->id]) }}" method="POST"
                                        class="d-inline" onsubmit="return confirm('Yakin hapus data ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm">
                                            Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                @if ($peserta->isNotEmpty())
                    <h5 class="mt-4 mb-2">Daftar Peserta</h5>
                    <table class="table table-bordered table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>#</th>
                                <th>Nama Peserta</th>
                                <th>Instansi</th>
                                <th>Email</th>
                                <td>Periode</td>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($peserta as $p)
                                <tr data-id="{{ $p->id }}" data-nama="{{ $p->nama_peserta }}"
                                    data-email="{{ $p->email }}" data-perusahaan_id="{{ $p->perusahaan_id }}"
                                    data-awal="{{ $p->awal_training ? \Carbon\Carbon::parse($p->awal_training)->format('Y-m-d') : '' }}"
                                    data-akhir="{{ $p->akhir_training ? \Carbon\Carbon::parse($p->akhir_training)->format('Y-m-d') : '' }}">
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $p->nama_peserta }}</td>
                                    <td>{{ $p->perusahaan->nama_perusahaan ?? '-' }}</td>
                                    <td>{{ $p->email ?? '-' }}</td>
                                    <td>{{ $p->awal_training ? \Carbon\Carbon::parse($p->awal_training)->translatedFormat('d M Y') : '-' }}
                                        s/d
                                        {{ $p->akhir_training ? \Carbon\Carbon::parse($p->akhir_training)->translatedFormat('d M Y') : '-' }}
                                    </td>
                                    <td>
                                        <button class="btn btn-warning btn-sm btnEditPeserta" data-bs-toggle="modal"
                                            data-bs-target="#modalEditPeserta">
                                            Edit
                                        </button>
                                        <form action="{{ route('office.modul.delete.peserta', ['id' => $p->id]) }}"
                                            method="POST" class="d-inline"
                                            onsubmit="return confirm('Yakin hapus peserta ini?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>

        {{-- Modal Tambah --}}
        <div class="modal fade" id="modalTambah" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <form action="{{ route('office.modul.store') }}" method="POST">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title">Tambah Modul</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label>No Modul</label>
                                    <input type="text" name="nomor" class="form-control" readonly
                                        value="{{ $nomor->no_modul }}">
                                    <input type="hidden" name="no_modul" class="form-control" readonly
                                        value="{{ $nomor->id }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label>Kode & Nama Materi <span class="text-danger">*</span></label>
                                    <select name="materi_id" class="form-select select2" required>
                                        <option value="">-- Pilih Materi --</option>
                                        @foreach ($materi as $item)
                                            <option value="{{ $item->id }}">
                                                {{ $item->nama_materi }} | {{ $item->kode_materi ?? '-' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label>Awal Training <span class="text-danger">*</span></label>
                                    <input type="date" name="awal_training" class="form-control" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label>Akhir Training <span class="text-danger">*</span></label>
                                    <input type="date" name="akhir_training" class="form-control" required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label>Jumlah Peserta <span class="text-danger">*</span></label>
                                    <input type="number" name="jumlah" class="form-control text-start format-rupiah"
                                        required placeholder="0">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label>Harga Satuan <span class="text-danger">*</span></label>
                                    <input type="text" name="harga_satuan"
                                        class="form-control text-start format-rupiah" required placeholder="0">
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Modal Edit --}}
        <div class="modal fade" id="modalEdit" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <form id="formEdit" method="POST">
                        @csrf
                        @method('PUT')

                        <input type="hidden" name="nomodul" id="nomodul">

                        <div class="modal-header">
                            <h5 class="modal-title">Edit Modul</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label>No Modul</label>
                                    <input type="text" class="form-control" value="{{ $nomor->no_modul }}" readonly>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label>Kode & Nama Materi</label>
                                    <select name="materi_id" id="edit_materi_id" class="form-select select2">
                                        <option value="">-- Pilih Materi --</option>
                                        @foreach ($materi as $item)
                                            <option value="{{ $item->id }}">
                                                {{ $item->nama_materi }} | {{ $item->kode_materi ?? '-' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label>Awal Training</label>
                                    <input type="date" id="edit_awal" name="awal_training" class="form-control">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label>Akhir Training</label>
                                    <input type="date" id="edit_akhir" name="akhir_training" class="form-control">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label>Jumlah</label>
                                    <input type="text" id="edit_jumlah" name="jumlah"
                                        class="form-control text-end format-rupiah">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label>Harga Satuan</label>
                                    <input type="text" id="edit_harga_satuan" name="harga_satuan"
                                        class="form-control text-end format-rupiah">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label>Note</label>
                                <textarea id="edit_note" name="note" class="form-control" rows="3"></textarea>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary">Update</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Modal Tambah Peserta --}}
        <div class="modal fade" id="modalPeserta" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <form action="{{ route('office.modul.store.peserta') }}" method="POST">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title">Tambah Peserta</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="no_modul" value="{{ $nomor->id }}">
                            <input type="hidden" name="modul" value="{{ $modul->first()?->id }}">

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label>Nama Peserta <span class="text-danger">*</span></label>
                                    <input type="text" name="nama_peserta" class="form-control" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label>Email Peserta</label>
                                    <input type="email" name="email_peserta" class="form-control">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label>Awal Training <span class="text-danger">*</span></label>
                                    <input type="date" name="awal_training" class="form-control" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label>Akhir Training <span class="text-danger">*</span></label>
                                    <input type="date" name="akhir_training" class="form-control" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label>Instansi Peserta</label>
                                <select name="perusahaan_id" class="form-select select2">
                                    <option value="">-- Tanpa Instansi --</option>
                                    @foreach ($perusahaan as $item)
                                        <option value="{{ $item->id }}">{{ $item->nama_perusahaan }} |
                                            {{ $item->lokasi ?: '-' }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary">Simpan</button>
                        </div>
                </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Edit Peserta --}}
    <div class="modal fade" id="modalEditPeserta" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="formEditPeserta" method="POST">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="peserta_id" id="edit_peserta_id">

                    <div class="modal-header">
                        <h5 class="modal-title">Edit Peserta</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>Nama Peserta <span class="text-danger">*</span></label>
                                <input type="text" name="nama_peserta" id="edit_nama_peserta" class="form-control"
                                    required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Email Peserta</label>
                                <input type="email" name="email_peserta" id="edit_email_peserta" class="form-control">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>Awal Training <span class="text-danger">*</span></label>
                                <input type="date" name="awal_training" id="edit_awal_training" class="form-control"
                                    required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Akhir Training <span class="text-danger">*</span></label>
                                <input type="date" name="akhir_training" id="edit_akhir_training"
                                    class="form-control" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label>Instansi Peserta</label>
                            <select name="perusahaan_id" id="edit_perusahaan_id" class="form-select select2">
                                <option value="">-- Tanpa Instansi --</option>
                                @foreach ($perusahaan as $item)
                                    <option value="{{ $item->id }}">{{ $item->nama_perusahaan }} |
                                        {{ $item->lokasi ?: '-' }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Input Note Khusus PDF Peserta --}}
    <div class="modal fade" id="modalNotePeserta" tabindex="-1" aria-labelledby="modalNotePesertaLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form id="formNotePeserta" action="" method="POST" class="modal-content shadow-lg border-0 rounded-4">
                @csrf
                @method('PUT')

                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold" id="modalNotePesertaLabel">Catatan PDF Peserta</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body pt-2">
                    <div class="mb-3">
                        <label for="note_peserta_input" class="form-label fw-semibold">Note / Catatan (opsional)</label>
                        <textarea name="note" id="note_peserta_input" class="form-control" rows="5"
                            placeholder="Tuliskan catatan untuk PDF Peserta..."></textarea>
                    </div>
                </div>

                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-4">
                        Download PDF
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="noteModal" tabindex="-1" aria-labelledby="noteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form id="noteForm" action="" method="POST" class="modal-content shadow-lg border-0 rounded-4">
                @csrf
                @method('PUT')
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold" id="noteModalLabel">Catatan untuk PDF</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body pt-2">
                    <div class="mb-3">
                        <label for="note" class="form-label fw-semibold">Note / Catatan (opsional)</label>
                        <textarea name="note" id="note" class="form-control" rows="5"
                            placeholder="Tuliskan catatan tambahan yang ingin dicantumkan di PDF..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-4">
                        Download PDF
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Input Note Khusus EXCEL Peserta --}}
    <div class="modal fade" id="modalExcelPeserta" tabindex="-1" aria-labelledby="modalExcelPesertaLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form id="formExcelPeserta" action="" method="POST"
                class="modal-content shadow-lg border-0 rounded-4">
                @csrf
                @method('PUT')

                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold" id="modalExcelPesertaLabel">Download Excel Peserta</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body pt-2">
                    <div class="alert alert-info py-2 small">
                        <i class="fas fa-info-circle"></i> File akan diunduh dalam format .xlsx
                    </div>
                    <div class="mb-3">
                        <label for="note_excel_peserta_input" class="form-label fw-semibold">Note / Catatan
                            (opsional)</label>
                        <textarea name="note" id="note_excel_peserta_input" class="form-control" rows="5"
                            placeholder="Tuliskan catatan untuk Excel Peserta..."></textarea>
                    </div>
                </div>

                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success px-4">
                        Download Excel
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- CSS Select2 + Theme Bootstrap 5 --}}
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css"
        rel="stylesheet" />

    {{-- Script Select2 & Logic --}}
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script defer>
        document.addEventListener('DOMContentLoaded', function() {

            // Format Rupiah
            function formatRupiah(angka) {
                if (!angka) return '';
                let number = angka.toString().replace(/\D/g, '');
                return number.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
            }

            function unformatRupiah(angka) {
                return angka.toString().replace(/\D/g, '');
            }

            // Format saat ngetik
            $(document).on('keyup', '.format-rupiah', function() {
                this.value = formatRupiah(this.value);
            });

            // Hitung total (hidden)
            function hitungTotal(jumlahEl, hargaEl, totalHidden) {
                let jml = parseInt(unformatRupiah(jumlahEl.val())) || 0;
                let hrg = parseInt(unformatRupiah(hargaEl.val())) || 0;
                totalHidden.val(jml * hrg);
            }

            // Sebelum submit → bersihkan format titik
            function bersihkanFormat(form) {
                form.find('.format-rupiah').each(function() {
                    this.value = unformatRupiah(this.value);
                });
                // Hitung ulang total sebelum kirim
                if (form.find('input[name="jumlah"], input[name="harga_satuan"]').length === 2) {
                    hitungTotal(
                        form.find('input[name="jumlah"]'),
                        form.find('input[name="harga_satuan"]'),
                        form.find('input[name="total"]')
                    );
                }
            }

            $('#modalTambah form, #formEdit').on('submit', function() {
                bersihkanFormat($(this));
            });

            // Select2 Tambah
            $('#modalTambah .select2').select2({
                placeholder: "Cari materi...",
                allowClear: true,
                width: '100%',
                dropdownParent: $('#modalTambah'),
                theme: 'bootstrap-5'
            });

            // Select2 Edit (init saat modal muncul)
            $('#modalEdit').on('shown.bs.modal', function() {
                if ($('#edit_materi_id').hasClass('select2-hidden-accessible')) {
                    $('#edit_materi_id').select2('destroy');
                }
                $('#edit_materi_id').select2({
                    placeholder: "Cari materi...",
                    allowClear: true,
                    width: '100%',
                    dropdownParent: $('#modalEdit'),
                    theme: 'bootstrap-5'
                });
            });

            // Select2 Tambah Peserta
            $('#modalPeserta').on('shown.bs.modal', function() {
                if ($('#peserta_perusahaan').hasClass('select2-hidden-accessible')) {
                    $('#peserta_perusahaan').select2('destroy');
                }
                $('#peserta_perusahaan').select2({
                    placeholder: "Pilih perusahaan...",
                    allowClear: true,
                    width: '100%',
                    dropdownParent: $('#modalPeserta'),
                    theme: 'bootstrap-5'
                });
            });

            // Tombol Edit → isi modal
            $(document).on('click', '.btnEdit', function() {
                const tr = $(this).closest('tr');

                $('#nomodul').val(tr.data('nomodul'));
                $('#edit_materi_id').val(tr.data('materi_id'));
                $('#edit_awal').val(tr.data('awal'));
                $('#edit_akhir').val(tr.data('akhir'));
                $('#edit_jumlah').val(formatRupiah(Math.floor(tr.data('jumlah')).toString()));
                $('#edit_harga_satuan').val(formatRupiah(Math.floor(tr.data('harga_satuan')).toString()));
                $('#edit_note').val(tr.data('note') || '');
                hitungTotal($('#edit_jumlah'), $('#edit_harga_satuan'), $('#edit_total_hidden'));
                $('#formEdit').attr('action', '/office/modul/update/' + tr.data('id'));
            });

            $(document).on('click', '.btnEditPeserta', function() {
                const tr = $(this).closest('tr');

                $('#edit_nama_peserta').val(tr.data('nama'));
                $('#edit_email_peserta').val(tr.data('email'));
                $('#edit_perusahaan_id').val(tr.data('perusahaan_id'));
                $('#edit_awal_training').val(tr.data('awal'));
                $('#edit_akhir_training').val(tr.data('akhir'));
                $('#formEditPeserta').attr('action', '/office/modul/update/peserta/' + tr.data('id'));

                if ($('#edit_perusahaan_id').hasClass('select2-hidden-accessible')) {
                    $('#edit_perusahaan_id').select2('destroy');
                }
                $('#edit_perusahaan_id').select2({
                    placeholder: "Pilih instansi...",
                    allowClear: true,
                    width: '100%',
                    dropdownParent: $('#modalEditPeserta'),
                    theme: 'bootstrap-5'
                });
            });

            $(document).on('click', '.btnPdfPeserta', function() {
                const id = $(this).data('id');
                const noteContent = $(this).data('note');

                $('#note_peserta_input').val(noteContent);

                let routeUrl = '{{ route('office.modul.download.pdf.peserta', ':id') }}';
                routeUrl = routeUrl.replace(':id', id);

                $('#formNotePeserta').attr('action', routeUrl);
            });

            $('.pdfBtn').on('click', function() {
                const id = $(this).data('id');
                const noteContent = $(this).data('note');

                $('#note').val(noteContent);

                const route = '{{ route('office.modul.download.pdf', ':id') }}';
                $('#noteForm').attr('action', route.replace(':id', id));
            });

            $(document).on('click', '.btnExcelPeserta', function() {
                const id = $(this).data('id');
                const noteContent = $(this).data('note'); // Mengambil note yang sama dengan PDF

                // Masukkan note ke textarea khusus Excel
                $('#note_excel_peserta_input').val(noteContent);

                // Set action form ke route download EXCEL
                // Pastikan nama route ini sesuai dengan di web.php
                let routeUrl = '{{ route('office.modul.download.excel.peserta', ':id') }}';
                routeUrl = routeUrl.replace(':id', id);

                $('#formExcelPeserta').attr('action', routeUrl);
            });

        });
    </script>
@endsection
