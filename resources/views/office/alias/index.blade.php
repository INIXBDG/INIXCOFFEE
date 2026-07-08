@extends('layouts_office.app')

@section('office_contents')
<div class="container-fluid px-4 py-3">
    <div class="mb-3">
        <h4 class="fw-semibold mb-0">Alias Materi</h4>
        <p class="text-muted small mb-0">Kelola alias dan kategori exam untuk setiap materi.</p>
    </div>

    <div class="card shadow-sm" style="border-radius: 12px;">
        <div class="card-body p-0">
            <div class="table-responsive p-3">
                <table id="tabelAlias" class="table table-hover table-bordered align-middle mb-0">
                    <thead class="table text-muted small">
                        <tr>
                            <th class="text-center" style="width: 50px;">No</th>
                            <th>Nama Materi</th>
                            <th>Alias</th>
                            <th>Kode Alias</th>
                            <th>Kategori Exam</th>
                            <th class="text-center" style="width: 80px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Modal Edit --}}
<div class="modal fade" id="modalEdit" tabindex="-1" aria-labelledby="modalEditLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalEditLabel">Edit Alias</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="edit-alert" class="alert alert-danger d-none small"></div>
                <input type="hidden" id="edit-id">

                <div class="mb-3">
                    <label class="form-label small fw-semibold text-muted">Nama Materi</label>
                    <input type="text" id="edit-nama-materi" class="form-control" readonly disabled>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-semibold text-muted">Alias</label>
                    <input type="text" id="edit-alias" class="form-control" placeholder="Masukkan alias">
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-semibold text-muted">Kode Alias</label>
                    <input type="text" id="edit-kode-alias" class="form-control" placeholder="Masukkan kode alias">
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-semibold text-muted">Kategori Exam</label>
                    <select name="kategori_exam" id="edit-kategori" class="form-control">
                        <option value="" selected disabled>-- Pilih Kategori --</option>
                        <option value="BNSP">BNSP</option>
                        <option value="Internasional">Internasional</option>
                        <option value="Authorize">Authorize</option>
                        <option value="Inixcert">Inixcert</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary btn-sm" id="btnEdit">Simpan</button>
            </div>
        </div>
    </div>
</div>


<script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
const urlJson   = '{{ route("office.alias.index.json") }}';
const urlUpdate = "/office/alias/update"

let table;

$(function () {
    table = $('#tabelAlias').DataTable({
        ajax: {
            url: urlJson,
            dataSrc: ''
        },
        columns: [
            {
                data: null,
                className: 'text-center text-muted',
                render: (_, __, ___, meta) => meta.row + 1,
                orderable: false,
                searchable: false
            },
            { data: 'nama_materi' },
            {
                data: 'alias',
                render: val => val
                    ? `<span class="text-secondary fw-normal">${val}</span>`
                    : '<span class="text-muted fst-italic small">-</span>'
            },
            {
                data: 'kode_alias',
                render: val => val
                    ? `<span class="text-secondary fw-normal">${val}</span>`
                    : '<span class="text-muted fst-italic small">-</span>'
            },
            {
                data: 'kategori_exam',
                render: val => val
                    ? val
                    : '<span class="text-muted fst-italic small">-</span>'
            },
            {
                data: null,
                className: 'text-center',
                orderable: false,
                searchable: false,
                render: (_, __, row) => `
                    <button class="btn btn-sm btn-outline-primary btn-edit"
                        data-id="${row.id}"
                        data-nama="${row.nama_materi ?? ''}"
                        data-alias="${row.alias ?? ''}"
                        data-kategori="${row.kategori_exam ?? ''}"
                        data-kode_alias="${row.kode_alias ?? ''}">
                        Edit
                    </button>
                `
            }
        ],
        language: {
            search: 'Cari:',
            lengthMenu: 'Tampilkan _MENU_ data',
            info: 'Menampilkan _START_–_END_ dari _TOTAL_ data',
            emptyTable: 'Tidak ada data.',
            zeroRecords: 'Data tidak ditemukan.'
        },
        order: [[0, 'asc']]
    });
    

    // Buka modal Edit
    $('#tabelAlias').on('click', '.btn-edit', function () {
        const btn = $(this);
        $('#edit-id').val(btn.data('id'));
        $('#edit-nama-materi').val(btn.data('nama'));
        $('#edit-alias').val(btn.data('alias'));
        $('#edit-kode-alias').val(btn.data('kode_alias'));
        $('#edit-kategori').val(btn.data('kategori'));
        $('#edit-alert').addClass('d-none').text('');
        $('#modalEdit').modal('show');
    });

    // Submit Edit
    $('#btnEdit').on('click', function () {
        const id = $('#edit-id').val();

        $.ajax({
            url: urlUpdate + '/' + id,
            method: 'POST',
            data: {
                alias:         $('#edit-alias').val().trim(),
                kode_alias:    $('#edit-kode-alias').val().trim(),
                kategori_exam: $('#edit-kategori').val().trim(),
                _token:        '{{ csrf_token() }}',
                _method:       'PUT'
            },
            success() {
                $('#modalEdit').modal('hide');
                table.ajax.reload(null, false);
            },
            error(xhr) {
                const errors = xhr.responseJSON?.errors;
                const msg = errors
                    ? Object.values(errors).flat().join('<br>')
                    : (xhr.responseJSON?.message ?? 'Terjadi kesalahan.');
                $('#edit-alert').removeClass('d-none').html(msg);
            }
        });
    });
});
</script>

@endsection