@extends('layout_HR.app')

@section('content_HR')
    <div class="container-fluid">

        {{-- Header --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-1">Manajemen Legalitas</h4>
                <p class="text-muted mb-0">Kelola semua dokumen legalitas perusahaan</p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambah">
                    Tambah Dokumen
                </button>
            </div>
        </div>

        {{-- Filter --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small">Pencarian</label>
                        <div class="input-group">
                            <input type="text" id="searchInput" class="form-control"
                                placeholder="Cari nama / nomor dokumen...">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold small">Status</label>
                        <select id="filter_status" class="form-select">
                            <option value="">Semua Status</option>
                            <option value="aktif">Aktif</option>
                            <option value="kadaluarsa">Kadaluarsa</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold small">Satuan</label>
                        <select id="filter_satuan" class="form-select">
                            <option value="">Semua Satuan</option>
                            <option value="hari">Hari</option>
                            <option value="bulan">Bulan</option>
                            <option value="tahun">Tahun</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold small">Tgl Terbit Dari</label>
                        <input type="date" id="filter_tgl_mulai" class="form-control">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold small">Sampai</label>
                        <input type="date" id="filter_tgl_selesai" class="form-control">
                    </div>
                </div>
                <div class="mt-3 d-flex gap-2">
                    <button type="button" id="btnFilter" class="btn btn-dark btn-sm px-4">Filter</button>
                    <button type="button" id="btnReset" class="btn btn-outline-secondary btn-sm px-4">Reset</button>
                </div>
            </div>
        </div>

        {{-- Tabel --}}
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="legalitasTable" class="table table-hover align-middle w-100">
                        <thead class="table-light">
                            <tr>
                                <th width="5%">No</th>
                                <th>Nama Dokumen</th>
                                <th>Nomor Dokumen</th>
                                <th>Tgl Terbit</th>
                                <th>Masa Berlaku</th>
                                <th>Tgl Berakhir</th>
                                <th>Status</th>
                                <th width="10%" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Tambah / Edit --}}
    <div class="modal fade" id="modalTambah" tabindex="-1" aria-labelledby="modalTambahLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <form id="formTambah" enctype="multipart/form-data" autocomplete="off">
                    @csrf
                    <input type="hidden" name="id" id="tambah_id">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold" id="modalTambahLabel">Tambah Legalitas</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>

                    <div class="modal-body">
                        <div class="row g-3">

                            <div class="col-md-6">
                                <label for="tambah_nama_dokumen" class="form-label">
                                    Nama Dokumen <span class="text-danger">*</span>
                                </label>
                                <input type="text" id="tambah_nama_dokumen" name="nama_dokumen" class="form-control"
                                    maxlength="255" placeholder="Contoh: Surat Izin Usaha Perdagangan" required>
                            </div>
                            <div class="col-md-6">
                                <label for="tambah_nomor_dokumen" class="form-label">
                                    Nomor Dokumen <span class="text-danger">*</span>
                                </label>
                                <input type="text" id="tambah_nomor_dokumen" name="nomor_dokumen"
                                    class="form-control" placeholder="Contoh: 503/123/SIUP/2025" required>
                            </div>

                            <div class="col-md-4">
                                <label for="tambah_tanggal_terbit" class="form-label">
                                    Tgl Terbit <span class="text-danger">*</span>
                                </label>
                                <input type="date" id="tambah_tanggal_terbit" name="tanggal_terbit"
                                    class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label for="tambah_masa_berlaku" class="form-label">
                                    Masa Berlaku <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <input type="number" id="tambah_masa_berlaku" name="masa_berlaku"
                                        class="form-control" min="1" placeholder="1" required>
                                    <select name="satuan_masa_berlaku" class="form-select" style="max-width: 110px;"
                                        aria-label="Satuan masa berlaku" required>
                                        <option value="hari">Hari</option>
                                        <option value="bulan">Bulan</option>
                                        <option value="tahun" selected>Tahun</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label for="tambah_tanggal_berakhir" class="form-label">Tgl Berakhir</label>
                                <input type="date" id="tambah_tanggal_berakhir" name="tanggal_berakhir"
                                    class="form-control">
                                <div class="form-text">Opsional.</div>
                            </div>

                            <div class="col-md-4">
                                <label for="tambah_status" class="form-label">
                                    Status <span class="text-danger">*</span>
                                </label>
                                <select id="tambah_status" name="status" class="form-select" required>
                                    <option value="aktif" selected>Aktif</option>
                                    <option value="kadaluarsa">Kadaluarsa</option>
                                </select>
                            </div>
                            <div class="col-md-8">
                                <label for="tambah_path_dokumen" class="form-label">
                                    File Dokumen <span class="text-danger">*</span>
                                </label>
                                <input type="file" id="tambah_path_dokumen" name="path_dokumen" class="form-control"
                                    accept=".pdf,.doc,.docx,.xls,.xlsx" required>
                                <div class="form-text" id="fileHelp">PDF, DOC, DOCX, XLS, XLSX. Maksimal 2 MB.</div>
                            </div>

                            <div class="col-12">
                                <label for="tambah_keterangan" class="form-label">Keterangan</label>
                                <textarea id="tambah_keterangan" name="keterangan" class="form-control" rows="3"
                                    placeholder="Catatan tambahan (opsional)"></textarea>
                            </div>

                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary" id="btnSimpan">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Detail --}}
    <div class="modal fade" id="modalDetail" tabindex="-1" aria-labelledby="modalDetailLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="modalDetailLabel">Detail Legalitas</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>

                <div class="modal-body p-0">
                    <ul class="nav nav-tabs px-3 pt-2" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="tabInfoBtn" data-bs-toggle="tab"
                                data-bs-target="#tabInfo" type="button" role="tab">Informasi</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="tabDokumenBtn" data-bs-toggle="tab"
                                data-bs-target="#tabDokumen" type="button" role="tab">Dokumen</button>
                        </li>
                    </ul>

                    <div class="tab-content">
                        <div class="tab-pane fade show active p-3" id="tabInfo" role="tabpanel">
                            <div id="detailContent"></div>
                        </div>

                        <div class="tab-pane fade" id="tabDokumen" role="tabpanel">
                            <div id="docLoading" class="text-center text-muted py-5 d-none">
                                <div class="spinner-border spinner-border-sm me-2"></div>Memuat dokumen...
                            </div>
                            <iframe id="docFrame" title="Pratinjau dokumen" class="d-none"
                                style="width:100%;height:70vh;border:0;background:#f8fafc"></iframe>
                            <div id="docFallback" class="text-center text-muted p-5 d-none"></div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    <a id="detailDownload" class="btn btn-success" target="_blank" rel="noopener">
                        <i class="fas fa-download me-2"></i>Download File
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Overlay kunci --}}
    <style>
        #lockOverlay {
            position: fixed;
            inset: 0;
            z-index: 99999;
            background: rgba(15, 23, 42, .92);
            backdrop-filter: blur(6px);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }

        #lockOverlay.d-none {
            display: none !important;
        }

        #lockOverlay .lock-card {
            width: 100%;
            max-width: 400px;
            background: #fff;
            color: #1e293b;
            border-radius: 1rem;
            padding: 2rem 1.75rem;
            box-shadow: 0 20px 50px rgba(0, 0, 0, .45);
            text-align: center;
        }

        #lockOverlay .lock-icon {
            width: 72px;
            height: 72px;
            margin: 0 auto 1rem;
            border-radius: 50%;
            background: #eef2ff;
            color: #4f46e5;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        #lockOverlay .lock-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: .25rem;
        }

        #lockOverlay .lock-desc {
            font-size: .875rem;
            color: #64748b;
            margin-bottom: 1.5rem;
        }

        #lockOverlay .form-control {
            background: #fff;
            color: #0f172a;
            border: 1px solid #cbd5e1;
            height: 44px;
        }

        #lockOverlay .form-control:focus {
            border-color: #4f46e5;
            box-shadow: 0 0 0 .2rem rgba(79, 70, 229, .15);
        }

        #lockOverlay .btn-toggle-pw {
            background: #f1f5f9;
            color: #475569;
            border: 1px solid #cbd5e1;
            min-width: 96px;
        }

        #lockOverlay .btn-lock-back,
        #lockOverlay .btn-lock-open {
            height: 44px;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        #lockOverlay .btn-lock-back {
            background: #f1f5f9;
            color: #334155;
            border: 1px solid #cbd5e1;
        }

        #lockOverlay .btn-lock-open {
            background: #4f46e5;
            border: 1px solid #4f46e5;
            color: #fff;
        }

        #lockOverlay .btn-lock-open:hover {
            background: #4338ca;
        }
    </style>

    {{-- Aset lokal --}}
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/dataTables.bootstrap5.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/vendorStyle/fontawesome/all.min.css') }}">

    <script src="{{ asset('assets/vendor/libs/jquery/jquery.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/dataTables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/dataTables/dataTables.bootstrap5.min.js') }}"></script>

    <script>
        function renderStatus(status) {
            return status === 'aktif' ?
                '<span class="badge bg-success">Aktif</span>' :
                '<span class="badge bg-danger">Kadaluarsa</span>';
        }

        function esc(v) {
            return $('<div>').text(v ?? '').html();
        }

        function renderAksi(row) {
            return `
                <select class="form-select form-select-sm select-aksi mx-auto" style="width: auto; min-width: 110px;">
                    <option value="">Action</option>
                    <option value="detail">Detail</option>
                    <option value="download" ${row.file_url ? '' : 'disabled'}>Download</option>
                    <option value="edit">Edit</option>
                    <option value="hapus">Hapus</option>
                </select>`;
        }

        function renderSisa(row) {
            if (row.sisa_hari === null || row.sisa_hari === undefined) {
                return '<span class="text-muted">-</span>';
            }
            if (row.sisa_hari < 0) {
                return `<span class="badge bg-danger">Lewat ${Math.abs(row.sisa_hari)} hari</span>`;
            }
            if (row.sisa_hari === 0) {
                return '<span class="badge bg-warning text-dark">Berakhir hari ini</span>';
            }
            const cls = row.sisa_hari <= 30 ? 'bg-warning text-dark' : 'bg-success';
            return `<span class="badge ${cls}">${row.sisa_hari} hari lagi</span>`;
        }

        function renderDetail(row) {
            const item = (label, value) =>
                `<dt class="col-sm-4 text-muted fw-normal">${label}</dt><dd class="col-sm-8">${value}</dd>`;
            const section = (title) =>
                `<h6 class="fw-bold border-bottom pb-2 mt-4 mb-3 text-uppercase small text-secondary">${title}</h6>`;

            return `
                ${section('Dokumen').replace('mt-4', 'mt-0')}
                <dl class="row mb-0">
                    ${item('Nama Dokumen', esc(row.nama_dokumen))}
                    ${item('Nomor Dokumen', esc(row.nomor_dokumen))}
                    ${item('Status', renderStatus(row.status))}
                </dl>

                ${section('Masa Berlaku')}
                <dl class="row mb-0">
                    ${item('Tgl Terbit', esc(row.tanggal_terbit))}
                    ${item('Masa Berlaku', esc(row.masa_berlaku_label))}
                    ${item('Tgl Berakhir', esc(row.tanggal_berakhir))}
                    ${item('Sisa Waktu', renderSisa(row))}
                </dl>

                ${section('File')}
                <dl class="row mb-0">
                    ${item('Nama File', esc(row.file_name) || '-')}
                    ${item('Tipe', row.file_ext ? esc(row.file_ext.toUpperCase()) : '-')}
                    ${item('Ukuran', esc(row.file_size) || '-')}
                </dl>

                ${section('Lainnya')}
                <dl class="row mb-0">
                    <dt class="col-sm-4 text-muted fw-normal">Keterangan</dt>
                    <dd class="col-sm-8" style="white-space: pre-line;">${esc(row.keterangan) || '-'}</dd>
                    ${item('Dibuat', esc(row.dibuat))}
                    ${item('Terakhir Diperbarui', esc(row.diperbarui))}
                </dl>`;
        }

        $(function() {
            const $filters = $('#filter_status, #filter_satuan, #filter_tgl_mulai, #filter_tgl_selesai');

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $.fn.dataTable.ext.errMode = 'none';

            const table = $('#legalitasTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('HR.legalitas.data') }}",
                    data: function(d) {
                        d.filter_status = $('#filter_status').val();
                        d.filter_satuan = $('#filter_satuan').val();
                        d.filter_tgl_mulai = $('#filter_tgl_mulai').val();
                        d.filter_tgl_selesai = $('#filter_tgl_selesai').val();
                    }
                },
                deferLoading: null,
                order: [],
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'nama_dokumen',
                        name: 'nama_dokumen',
                        render: $.fn.dataTable.render.text()
                    },
                    {
                        data: 'nomor_dokumen',
                        name: 'nomor_dokumen',
                        render: $.fn.dataTable.render.text()
                    },
                    {
                        data: 'tanggal_terbit',
                        name: 'tanggal_terbit'
                    },
                    {
                        data: 'masa_berlaku_label',
                        name: 'masa_berlaku'
                    },
                    {
                        data: 'tanggal_berakhir',
                        name: 'tanggal_berakhir'
                    },
                    {
                        data: 'status',
                        name: 'status',
                        orderable: false,
                        render: renderStatus
                    },
                    {
                        data: null,
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        className: 'text-center',
                        render: (data, type, row) => renderAksi(row)
                    },
                ],
                language: {
                    search: '',
                    searchPlaceholder: 'Cari...',
                    lengthMenu: 'Tampilkan _MENU_ data',
                    zeroRecords: 'Data tidak ditemukan',
                    info: 'Menampilkan _START_ - _END_ dari _TOTAL_ data',
                    paginate: {
                        previous: 'Prev',
                        next: 'Next'
                    },
                    processing: 'Memuat data...'
                }
            });

            let currentRow = null;

            function resetDocPane() {
                $('#docFrame').addClass('d-none').attr('src', 'about:blank').removeData('loaded');
                $('#docFallback').addClass('d-none').empty();
                $('#docLoading').addClass('d-none');
            }

            function loadDoc(row) {
                if (!row) return;

                const $frame = $('#docFrame');
                if ($frame.data('loaded') === row.id) return;

                resetDocPane();

                if (!row.file_url) {
                    $('#docFallback').removeClass('d-none')
                        .html('<p class="mb-0">Dokumen tidak ditemukan.</p>');
                    return;
                }

                if (row.file_ext === 'pdf') {
                    $('#docLoading').removeClass('d-none');
                    $frame.off('load').on('load', () => $('#docLoading').addClass('d-none'))
                        .removeClass('d-none')
                        .attr('src', row.file_url + '#view=FitH')
                        .data('loaded', row.id);
                    return;
                }

                // DOC/DOCX/XLS/XLSX tidak bisa ditampilkan langsung oleh browser
                $('#docFallback').removeClass('d-none').html(`
                        <i class="fas fa-file-alt fa-3x mb-3"></i>
                        <p class="mb-1 fw-semibold">${esc(row.file_name)}</p>
                        <p class="small mb-3">Pratinjau hanya tersedia untuk file PDF. File ${esc((row.file_ext || '').toUpperCase())} perlu diunduh untuk dibuka.</p>
                        <a href="${row.file_download_url}" class="btn btn-success btn-sm">
                            <i class="fas fa-download me-2"></i>Download File
                        </a>`);
            }

            function showDetail(row) {
                currentRow = row;
                resetDocPane();

                $('#detailContent').html(renderDetail(row));

                const $download = $('#detailDownload');
                if (row.file_download_url) {
                    $download.attr('href', row.file_download_url).removeClass('disabled');
                } else {
                    $download.removeAttr('href').addClass('disabled');
                }

                $('#tabDokumenBtn').toggleClass('disabled', !row.file_url);
                bootstrap.Tab.getOrCreateInstance('#tabInfoBtn').show();
                bootstrap.Modal.getOrCreateInstance('#modalDetail').show();
            }

            function downloadFile(row) {
                if (!row.file_download_url) return;

                const a = document.createElement('a');
                a.href = row.file_download_url;
                a.target = '_blank';
                a.rel = 'noopener';
                document.body.appendChild(a);
                a.click();
                a.remove();
            }

            document.getElementById('tabDokumenBtn').addEventListener('shown.bs.tab', () => loadDoc(currentRow));

            document.getElementById('modalDetail').addEventListener('hidden.bs.modal', () => {
                resetDocPane();
                currentRow = null;
            });

            let searchTimer;
            $('#searchInput').on('keyup', function() {
                clearTimeout(searchTimer);
                searchTimer = setTimeout(() => table.search(this.value).draw(), 300);
            });

            $('#btnFilter').on('click', () => table.draw());

            $('#btnReset').on('click', function() {
                $filters.val('');
                $('#searchInput').val('');
                table.search('').draw();
            });

            const modalFormEl = document.getElementById('modalTambah');
            const $form = $('#formTambah');
            const FILE_HELP = 'PDF, DOC, DOCX, XLS, XLSX. Maksimal 2 MB.';


            const $tglTerbit = $('#tambah_tanggal_terbit');
            const $masaBerlaku = $('#tambah_masa_berlaku');
            const $satuan = $form.find('[name="satuan_masa_berlaku"]');
            const $tglBerakhir = $('#tambah_tanggal_berakhir');

            function hitungTanggalBerakhir() {
                const terbit = $tglTerbit.val();
                const masa = parseInt($masaBerlaku.val(), 10);
                const satuan = $satuan.val();

                if (!terbit || !masa || masa < 1 || !satuan) {
                    return;
                }

                const d = new Date(terbit + 'T00:00:00');
                if (isNaN(d.getTime())) return;

                if (satuan === 'hari') {
                    d.setDate(d.getDate() + masa);
                } else if (satuan === 'bulan') {
                    d.setMonth(d.getMonth() + masa);
                } else if (satuan === 'tahun') {
                    d.setFullYear(d.getFullYear() + masa);
                }

                // Format YYYY-MM-DD
                const yyyy = d.getFullYear();
                const mm = String(d.getMonth() + 1).padStart(2, '0');
                const dd = String(d.getDate()).padStart(2, '0');
                $tglBerakhir.val(`${yyyy}-${mm}-${dd}`);
            }

            function hitungMasaBerlaku() {
                const terbit = $tglTerbit.val();
                const berakhir = $tglBerakhir.val();
                const satuan = $satuan.val();

                if (!terbit || !berakhir || !satuan) return;

                const d1 = new Date(terbit + 'T00:00:00');
                const d2 = new Date(berakhir + 'T00:00:00');
                if (isNaN(d1.getTime()) || isNaN(d2.getTime()) || d2 < d1) return;

                let selisih = 0;

                if (satuan === 'hari') {
                    selisih = Math.round((d2 - d1) / (1000 * 60 * 60 * 24));
                } else if (satuan === 'bulan') {
                    selisih = (d2.getFullYear() - d1.getFullYear()) * 12 + (d2.getMonth() - d1.getMonth());
                    // koreksi jika hari di tanggal berakhir lebih kecil
                    if (d2.getDate() < d1.getDate()) selisih--;
                } else if (satuan === 'tahun') {
                    selisih = d2.getFullYear() - d1.getFullYear();
                    // koreksi jika bulan/hari belum sampai
                    if (d2.getMonth() < d1.getMonth() ||
                        (d2.getMonth() === d1.getMonth() && d2.getDate() < d1.getDate())) {
                        selisih--;
                    }
                }

                if (selisih >= 1) {
                    $masaBerlaku.val(selisih);
                }
            }

            $tglTerbit.on('change', hitungTanggalBerakhir);
            $masaBerlaku.on('input change', hitungTanggalBerakhir);
            $satuan.on('change', function() {
                if ($tglBerakhir.val() && $tglTerbit.val()) {
                    hitungMasaBerlaku();
                } else {
                    hitungTanggalBerakhir();
                }
            });

            $tglBerakhir.on('change', hitungMasaBerlaku);

            function setFormMode(isEdit) {
                $('#modalTambahLabel').text(isEdit ? 'Edit Legalitas' : 'Tambah Legalitas');
                $('#tambah_path_dokumen').prop('required', !isEdit);
                $('#fileHelp').text(isEdit ? 'Kosongkan jika tidak ingin mengganti file. ' + FILE_HELP : FILE_HELP);
            }

            function resetForm() {
                $form[0].reset();
                $('#tambah_id').val('');
                setFormMode(false);
            }

            function openEdit(row) {
                resetForm();
                setFormMode(true);

                $('#tambah_id').val(row.id);
                ['nama_dokumen', 'nomor_dokumen', 'masa_berlaku', 'satuan_masa_berlaku', 'status', 'keterangan']
                .forEach(f => $form.find(`[name="${f}"]`).val(row[f] ?? ''));
                $form.find('[name="tanggal_terbit"]').val(row.tanggal_terbit_raw ?? '');
                $form.find('[name="tanggal_berakhir"]').val(row.tanggal_berakhir_raw ?? '');

                bootstrap.Modal.getOrCreateInstance(modalFormEl).show();
            }

            $form.on('submit', function(e) {
                e.preventDefault();

                const $btn = $('#btnSimpan');
                if ($btn.prop('disabled')) return;

                const isEdit = !!$('#tambah_id').val();
                const formData = new FormData(this);

                if (isEdit) {
                    formData.append('_method', 'PUT');
                } else {
                    formData.delete('id');
                }

                $.ajax({
                    url: isEdit ? "{{ route('HR.legalitas.update') }}" :
                        "{{ route('HR.legalitas.store') }}",
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    beforeSend: () => $btn.prop('disabled', true),
                    complete: () => $btn.prop('disabled', false),
                    success: function() {
                        table.draw(false);
                        bootstrap.Modal.getOrCreateInstance(modalFormEl).hide();
                    }
                });
            });

            modalFormEl.addEventListener('hidden.bs.modal', function() {
                resetForm();

                if (document.querySelector('.modal.show')) return;

                document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
                document.body.classList.remove('modal-open');
                document.body.style.removeProperty('overflow');
                document.body.style.removeProperty('padding-right');
            });

            function confirmDelete(row) {
                Swal.fire({
                    title: 'Hapus data?',
                    text: `"${row.nama_dokumen}" akan dihapus`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonText: 'Batal',
                    confirmButtonText: 'Ya, Hapus'
                }).then((result) => {
                    if (!result.isConfirmed) return;

                    $.ajax({
                        url: "{{ route('HR.legalitas.delete') }}",
                        type: 'POST',
                        data: {
                            _method: 'PUT',
                            id: row.id
                        },
                        success: () => table.draw(false)
                    });
                });
            }

            $('#legalitasTable tbody').on('change', '.select-aksi', function() {
                const action = $(this).val();
                const row = table.row($(this).closest('tr')).data();

                $(this).val('');

                switch (action) {
                    case 'detail':
                        showDetail(row);
                        break;
                    case 'download':
                        downloadFile(row);
                        break;
                    case 'edit':
                        openEdit(row);
                        break;
                    case 'hapus':
                        confirmDelete(row);
                        break;
                }
            });

            // ---------- Kunci Legalitas ----------
            const LOCK_ROUTES = {
                unlock: "{{ route('HR.legalitas.unlock') }}",
                lock: "{{ route('HR.legalitas.lock') }}"
            };
            const $overlay = $('#lockOverlay').appendTo('body');
            let idleLeft = IDLE_SEC;
            let idleTimer = null;

            function setInert(on) {
                const $others = $('body').children().not($overlay);
                if (on) $others.attr('inert', '');
                else $others.removeAttr('inert');
            }

            function startIdle() {
                idleLeft = IDLE_SEC;
                clearInterval(idleTimer);
                idleTimer = setInterval(function() {
                    idleLeft--;
                    const m = String(Math.floor(Math.max(idleLeft, 0) / 60)).padStart(2, '0');
                    const s = String(Math.max(idleLeft, 0) % 60).padStart(2, '0');
                    $('#lockTimer').text(m + ':' + s);
                    if (idleLeft <= 0) lockNow('Terkunci otomatis karena tidak ada aktivitas.');
                }, 1000);
            }

            function showLock(message) {
                clearInterval(idleTimer);
                document.querySelectorAll('.modal.show').forEach(el => bootstrap.Modal.getOrCreateInstance(el)
                    .hide());
                table.clear().draw();
                $('#unlockPassword').val('').attr('type', 'password');
                $('#btnTogglePw').text('Lihat');
                $('#unlockError').toggleClass('d-none', !message).text(message || '');
                $overlay.removeClass('d-none');
                setInert(true);
                setTimeout(() => $('#unlockPassword').trigger('focus'), 50);
            }

            function hideLock(reload) {
                $overlay.addClass('d-none');
                setInert(false);
                startIdle();
                if (reload) table.ajax.reload(null, false);
            }

            function lockNow(message) {
                $.post(LOCK_ROUTES.lock).always(() => showLock(message));
            }

            $('#formUnlock').on('submit', function(e) {
                e.preventDefault();
                const $btn = $('#btnUnlock').prop('disabled', true).text('Memeriksa...');
                $('#unlockError').addClass('d-none');
                $.post(LOCK_ROUTES.unlock, {
                        password: $('#unlockPassword').val()
                    })
                    .done(() => hideLock(true))
                    .fail(function(xhr) {
                        const msg = xhr.status === 429 ?
                            'Terlalu banyak percobaan. Coba lagi sebentar lagi.' :
                            (xhr.responseJSON && xhr.responseJSON.message) || 'Gagal membuka.';
                        $('#unlockError').removeClass('d-none').text(msg);
                        $('#unlockPassword').val('').trigger('focus');
                    })
                    .always(() => $btn.prop('disabled', false).text('Buka'));
            });

            $('#btnLock').on('click', () => lockNow());

            $('#btnTogglePw').on('click', function() {
                const $i = $('#unlockPassword');
                const show = $i.attr('type') === 'password';
                $i.attr('type', show ? 'text' : 'password');
                $(this).text(show ? 'Sembunyikan' : 'Lihat');
            });

            $(document).on('mousemove keydown click', function() {
                if ($overlay.hasClass('d-none')) idleLeft = IDLE_SEC;
            });

            $(document).ajaxError(function(e, xhr) {
                if (xhr.status === 423 && $overlay.hasClass('d-none')) {
                    showLock('Sesi berakhir. Masukkan password kembali.');
                }
            });
        });
    </script>
@endsection
