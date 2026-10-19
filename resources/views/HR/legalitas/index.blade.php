@extends('layout_HR.app')

@section('content_HR')
<div class="container-fluid">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">Manajemen Legalitas</h4>
            <p class="text-muted mb-0">Kelola semua dokumen legalitas perusahaan</p>
        </div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambah">
            Tambah Dokumen
        </button>
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

                        {{-- Identitas dokumen --}}
                        <div class="col-md-6">
                            <label for="tambah_nama_dokumen" class="form-label">
                                Nama Dokumen <span class="text-danger">*</span>
                            </label>
                            <input type="text" id="tambah_nama_dokumen" name="nama_dokumen"
                                   class="form-control" maxlength="255"
                                   placeholder="Contoh: Surat Izin Usaha Perdagangan" required>
                        </div>
                        <div class="col-md-6">
                            <label for="tambah_nomor_dokumen" class="form-label">
                                Nomor Dokumen <span class="text-danger">*</span>
                            </label>
                            <input type="text" id="tambah_nomor_dokumen" name="nomor_dokumen"
                                   class="form-control" placeholder="Contoh: 503/123/SIUP/2025" required>
                        </div>

                        {{-- Masa berlaku --}}
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

                        {{-- Status & file --}}
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
                            <input type="file" id="tambah_path_dokumen" name="path_dokumen"
                                   class="form-control" accept=".pdf,.doc,.docx,.xls,.xlsx" required>
                            <div class="form-text" id="fileHelp">PDF, DOC, DOCX, XLS, XLSX. Maksimal 2 MB.</div>
                        </div>

                        {{-- Keterangan --}}
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
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="modalDetailLabel">Detail Legalitas</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body" id="detailContent"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                <a id="detailDownload" class="btn btn-success" download target="_blank" rel="noopener">
                    <i class="fas fa-download me-2"></i>Download File
                </a>
            </div>
        </div>
    </div>
</div>

{{-- Aset lokal --}}
<link rel="stylesheet" href="{{ asset('assets/vendor/css/dataTables.bootstrap5.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/vendor/vendorStyle/fontawesome/all.min.css') }}">

<script src="{{ asset('assets/vendor/libs/jquery/jquery.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/dataTables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/dataTables/dataTables.bootstrap5.min.js') }}"></script>

<script>
// ---------- Render kolom (HTML dibangun di sisi client) ----------
function renderStatus(status) {
    return status === 'aktif'
        ? '<span class="badge bg-success">Aktif</span>'
        : '<span class="badge bg-danger">Kadaluarsa</span>';
}

function esc(v) {
    return $('<div>').text(v ?? '').html();
}

function renderAksi(row) {
    return `
        <select class="form-select form-select-sm select-aksi mx-auto" style="width: auto; min-width: 110px;">
            <option value="">Aksi</option>
            <option value="detail">Detail</option>
            <option value="download" ${row.file_url ? '' : 'disabled'}>Download</option>
            <option value="edit">Edit</option>
            <option value="hapus">Hapus</option>
        </select>`;
}

function renderDetail(row) {
    const item = (label, value) =>
        `<dt class="col-sm-4">${label}</dt><dd class="col-sm-8">${value}</dd>`;

    return `
        <dl class="row mb-0">
            ${item('Nama Dokumen', esc(row.nama_dokumen))}
            ${item('Nomor Dokumen', esc(row.nomor_dokumen))}
            ${item('Tgl Terbit', esc(row.tanggal_terbit))}
            ${item('Masa Berlaku', esc(row.masa_berlaku_label))}
            ${item('Tgl Berakhir', esc(row.tanggal_berakhir))}
            ${item('Status', renderStatus(row.status))}
            <dt class="col-sm-4">Keterangan</dt>
            <dd class="col-sm-8" style="white-space: pre-line;">${esc(row.keterangan) || '-'}</dd>
        </dl>`;
}

$(function () {
    const $filters = $('#filter_status, #filter_satuan, #filter_tgl_mulai, #filter_tgl_selesai');

    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });

    // ---------- DataTable ----------
    const table = $('#legalitasTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('HR.legalitas.data') }}",
            data: function (d) {
                d.filter_status       = $('#filter_status').val();
                d.filter_satuan       = $('#filter_satuan').val();
                d.filter_tgl_mulai    = $('#filter_tgl_mulai').val();
                d.filter_tgl_selesai  = $('#filter_tgl_selesai').val();
            }
        },
        order: [], // tanpa sorting awal: server urut data terbaru (id desc)
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'nama_dokumen', name: 'nama_dokumen', render: $.fn.dataTable.render.text() },
            { data: 'nomor_dokumen', name: 'nomor_dokumen', render: $.fn.dataTable.render.text() },
            { data: 'tanggal_terbit', name: 'tanggal_terbit' },
            { data: 'masa_berlaku_label', name: 'masa_berlaku' },
            { data: 'tanggal_berakhir', name: 'tanggal_berakhir' },
            { data: 'status', name: 'status', orderable: false, render: renderStatus },
            { data: null, name: 'action', orderable: false, searchable: false, className: 'text-center',
              render: (data, type, row) => renderAksi(row) },
        ],
        language: {
            search: '',
            searchPlaceholder: 'Cari...',
            lengthMenu: 'Tampilkan _MENU_ data',
            zeroRecords: 'Data tidak ditemukan',
            info: 'Menampilkan _START_ - _END_ dari _TOTAL_ data',
            paginate: { previous: 'Prev', next: 'Next' },
            processing: 'Memuat data...'
        }
    });

    // ---------- Detail (pakai data baris, tanpa request ke server) ----------
    function showDetail(row) {
        const $download = $('#detailDownload');

        $('#detailContent').html(renderDetail(row));

        if (row.file_url) {
            $download.attr('href', row.file_url).removeClass('disabled');
        } else {
            $download.removeAttr('href').addClass('disabled');
        }

        bootstrap.Modal.getOrCreateInstance('#modalDetail').show();
    }

    // ---------- Download langsung ----------
    function downloadFile(row) {
        if (!row.file_url) return;

        const a = document.createElement('a');
        a.href = row.file_url;
        a.download = '';
        a.target = '_blank';
        a.rel = 'noopener';
        document.body.appendChild(a);
        a.click();
        a.remove();
    }

    // ---------- Pencarian & filter ----------
    // Input custom agar tidak bentrok dengan search bawaan DataTables
    let searchTimer;
    $('#searchInput').on('keyup', function () {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => table.search(this.value).draw(), 300);
    });

    $('#btnFilter').on('click', () => table.draw());

    $('#btnReset').on('click', function () {
        $filters.val('');
        $('#searchInput').val('');
        table.search('').draw();
    });

    // ---------- Form tambah / edit (satu modal) ----------
    const modalFormEl = document.getElementById('modalTambah');
    const $form = $('#formTambah');
    const FILE_HELP = 'PDF, DOC, DOCX, XLS, XLSX. Maksimal 2 MB.';

    function setFormMode(isEdit) {
        $('#modalTambahLabel').text(isEdit ? 'Edit Legalitas' : 'Tambah Legalitas');
        $('#tambah_path_dokumen').prop('required', !isEdit);
        $('#fileHelp').text(isEdit ? 'Kosongkan jika tidak ingin mengganti file. ' + FILE_HELP : FILE_HELP);
    }

    function resetForm() {
        $form[0].reset();
        $('#tambah_id').val(''); // input hidden tidak ikut ter-reset oleh form.reset()
        setFormMode(false);
    }

    // Edit: isi form dari data baris, lalu buka modal
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

    // Submit: tambah (POST store) atau edit (PUT update)
    $form.on('submit', function (e) {
        e.preventDefault();

        const $btn = $('#btnSimpan');
        if ($btn.prop('disabled')) return; // cegah double submit

        const isEdit = !!$('#tambah_id').val();
        const formData = new FormData(this);

        if (isEdit) {
            formData.append('_method', 'PUT'); // multipart tidak terbaca di PUT asli, jadi spoof lewat POST
        } else {
            formData.delete('id');
        }

        $.ajax({
            url: isEdit ? "{{ route('HR.legalitas.update') }}" : "{{ route('HR.legalitas.store') }}",
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            beforeSend: () => $btn.prop('disabled', true),
            complete: () => $btn.prop('disabled', false),
            success: function () {
                table.draw(false);
                bootstrap.Modal.getOrCreateInstance(modalFormEl).hide();
            }
            // notifikasi sukses/error ditangani global JS
        });
    });

    // Setelah modal tertutup: kembalikan ke mode tambah & pastikan backdrop bersih
    modalFormEl.addEventListener('hidden.bs.modal', function () {
        resetForm();

        if (document.querySelector('.modal.show')) return;

        document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
        document.body.classList.remove('modal-open');
        document.body.style.removeProperty('overflow');
        document.body.style.removeProperty('padding-right');
    });

    // ---------- Hapus ----------
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
                data: { _method: 'PUT', id: row.id }, // route delete memakai PUT
                success: () => table.draw(false)
                // notifikasi sukses/error ditangani global JS
            });
        });
    }

    // ---------- Aksi (select option) ----------
    $('#legalitasTable tbody').on('change', '.select-aksi', function () {
        const action = $(this).val();
        const row = table.row($(this).closest('tr')).data();

        $(this).val(''); // kembalikan ke label "Aksi"

        switch (action) {
            case 'detail':   showDetail(row);    break;
            case 'download': downloadFile(row);  break;
            case 'edit':     openEdit(row);      break;
            case 'hapus':    confirmDelete(row); break;
        }
    });
});
</script>
@endsection