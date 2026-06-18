@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="modal fade" id="loadingModal" tabindex="-1" aria-labelledby="spinnerModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered justify-content-center">
            <div class="loader"></div>
        </div>
    </div>

    <div class="modal fade" id="createVisitModal" tabindex="-1" aria-labelledby="createVisitModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createVisitModalLabel">{{ __('Tambah Data Aktivitas Visit') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formCreateVisit" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="kegiatan" class="form-label">{{ __('Nama Kegiatan') }}</label>
                                <input type="text" class="form-control" id="kegiatan" name="kegiatan" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="lokasi" class="form-label">{{ __('Lokasi Visit') }}</label>
                                <input type="text" class="form-control" id="lokasi" name="lokasi" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="pic_name" class="form-label">{{ __('Nama PIC') }}</label>
                                <input type="text" class="form-control" id="pic_name" name="pic_name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="tanggal" class="form-label">{{ __('Tanggal Visit') }}</label>
                                <input type="date" class="form-control" id="tanggal" name="tanggal" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="photo_path" class="form-label">{{ __('Foto Aktivitas') }}</label>
                            <input type="file" class="form-control" id="photo_path" name="photo_path" accept="image/jpeg,image/png,image/jpg" required>
                            <small class="text-muted">Format yang diizinkan: JPG, JPEG, PNG. Maksimal 2MB.</small>
                        </div>
                        <div class="mb-3">
                            <label for="desc" class="form-label">{{ __('Deskripsi / Hasil Visit') }}</label>
                            <textarea class="form-control" id="desc" name="desc" rows="3" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Tutup') }}</button>
                        <button type="submit" class="btn btn-primary" id="btnSaveVisit">{{ __('Simpan Data') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editVisitModal" tabindex="-1" aria-labelledby="editVisitModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editVisitModalLabel">{{ __('Edit Data Aktivitas Visit') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formEditVisit" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" id="edit_visit_id" name="visit_id">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit_kegiatan" class="form-label">{{ __('Nama Kegiatan') }}</label>
                                <input type="text" class="form-control" id="edit_kegiatan" name="kegiatan" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit_lokasi" class="form-label">{{ __('Lokasi Visit') }}</label>
                                <input type="text" class="form-control" id="edit_lokasi" name="lokasi" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit_pic_name" class="form-label">{{ __('Nama PIC') }}</label>
                                <input type="text" class="form-control" id="edit_pic_name" name="pic_name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit_tanggal" class="form-label">{{ __('Tanggal Visit') }}</label>
                                <input type="date" class="form-control" id="edit_tanggal" name="tanggal" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="edit_photo_path" class="form-label">{{ __('Foto Aktivitas (Opsional)') }}</label>
                            <input type="file" class="form-control" id="edit_photo_path" name="photo_path" accept="image/jpeg,image/png,image/jpg">
                            <small class="text-muted">Biarkan kosong jika tidak ingin mengubah foto saat ini.</small>
                        </div>
                        <div class="mb-3">
                            <label for="edit_desc" class="form-label">{{ __('Deskripsi / Hasil Visit') }}</label>
                            <textarea class="form-control" id="edit_desc" name="desc" rows="3" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Tutup') }}</button>
                        <button type="submit" class="btn btn-warning" id="btnUpdateVisit">{{ __('Simpan Perubahan') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="d-flex justify-content-end mb-3">
                <button type="button" class="btn btn-md btn-primary mx-4" data-bs-toggle="modal" data-bs-target="#createVisitModal">
                    Tambah Visit
                </button>
            </div>
            <div class="card m-4">
                <div class="card-body table-responsive">
                    <h3 class="card-title text-center my-1">{{ __('Aktivitas Visit') }}</h3>
                    <table class="table table-striped" id="visitsTable" style="width: 100%;">
                        <thead>
                            <tr>
                                <th scope="col">No</th>
                                <th scope="col">Kegiatan</th>
                                <th scope="col">Lokasi</th>
                                <th scope="col">PIC</th>
                                <th scope="col">Tanggal</th>
                                <th scope="col">Foto</th>
                                <th scope="col">Deskripsi</th>
                                <th scope="col">Aksi</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .loader { position: relative; text-align: center; margin: 15px auto 35px auto; z-index: 9999; display: block; width: 80px; height: 80px; border: 10px solid rgba(0, 0, 0, .3); border-radius: 50%; border-top-color: #000; animation: spin 1s ease-in-out infinite; -webkit-animation: spin 1s ease-in-out infinite; }
    @keyframes spin { to { -webkit-transform: rotate(360deg); } }
    @-webkit-keyframes spin { to { -webkit-transform: rotate(360deg); } }
    .modal-content { border-radius: 0px; box-shadow: 0 0 20px 8px rgba(0, 0, 0, 0.7); }
    .modal-backdrop.show { opacity: 0.75; }
</style>

@push('js')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">

<script>
    $(document).ready(function(){

        var assetUrl = "{{ asset('storage') }}";

        var table = $('#visitsTable').DataTable({
            "ajax": {
                "url": "{{ route('visit-projects.get') }}",
                "type": "GET",
                "dataSrc": "data",
                "beforeSend": function () {
                    $('#loadingModal').modal('show');
                    $('#loadingModal').on('show.bs.modal', function () {
                        $('#loadingModal').removeAttr('inert');
                    });
                },
                "complete": function () {
                    setTimeout(() => {
                        $('#loadingModal').modal('hide');
                        $('#loadingModal').on('hidden.bs.modal', function () {
                            $('#loadingModal').attr('inert', true);
                        });
                    }, 1000);
                }
            },
            "columns": [
                {
                    "data": null,
                    "searchable": false,
                    "orderable": false,
                    "render": function (data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }
                },
                {"data": "kegiatan"},
                {"data": "lokasi"},
                {"data": "pic_name"},
                {
                    "data": "tanggal",
                    "render": function(data) {
                        if(data){
                            let dateObj = new Date(data);
                            let day = ("0" + dateObj.getDate()).slice(-2);
                            let month = ("0" + (dateObj.getMonth() + 1)).slice(-2);
                            let year = dateObj.getFullYear();
                            return day + "-" + month + "-" + year;
                        }
                        return "-";
                    }
                },
                {
                    "data": "photo_path",
                    "render": function(data) {
                        if(data) {
                            return '<img src="' + assetUrl + '/' + data + '" width="80" class="img-thumbnail" alt="Foto Visit">';
                        }
                        return '<span class="text-muted">Tidak ada foto</span>';
                    }
                },
                {
                    "data": "desc",
                    "render": function(data) {
                        return data ? (data.length > 50 ? data.substr(0, 50) + '...' : data) : '-';
                    }
                },
                {
                    "data": null,
                    "render": function(data, type, row) {
                        let actions = '<div class="btn-group dropup">';
                        actions += '<button type="button" class="btn btn-sm dropdown-toggle text-black" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">';
                        actions += 'Actions ';
                        actions += '</button>';
                        actions += '<div class="dropdown-menu shadow-sm" style="max-height: 250px; overflow-y: auto; border-radius: 6px;">';
                        actions += '<a class="dropdown-item btn-edit-visit" href="#" ' +
                                   'data-id="' + row.id + '" ' +
                                   'data-kegiatan="' + row.kegiatan + '" ' +
                                   'data-lokasi="' + row.lokasi + '" ' +
                                   'data-pic="' + row.pic_name + '" ' +
                                   'data-tanggal="' + row.tanggal.split('T')[0] + '" ' +
                                   'data-desc="' + row.desc + '">Edit Data</a>';
                        actions += '<div class="dropdown-divider"></div>';
                        actions += '<a class="dropdown-item text-danger btn-delete-visit" href="#" data-id="' + row.id + '">Hapus Data</a>';
                        actions += '</div></div>';
                        return actions;
                    }
                }
            ]
        });

        $('#formCreateVisit').on('submit', function(e) {
            e.preventDefault();
            let formData = new FormData(this);

            $.ajax({
                url: "{{ route('visit-projects.store') }}",
                type: "POST",
                data: formData,
                contentType: false,
                processData: false,
                beforeSend: function() {
                    $('#btnSaveVisit').prop('disabled', true).text('Menyimpan...');
                    $('#loadingModal').modal('show');
                },
                success: function(response) {
                    $('#createVisitModal').modal('hide');
                    $('#formCreateVisit')[0].reset();
                    table.ajax.reload(null, false);
                },
                error: function(xhr) {
                    alert(xhr.responseJSON.message || 'Terjadi kesalahan sistem.');
                },
                complete: function() {
                    $('#btnSaveVisit').prop('disabled', false).text('Simpan Data');
                    setTimeout(() => { $('#loadingModal').modal('hide'); }, 500);
                }
            });
        });

        $('#visitsTable tbody').on('click', '.btn-edit-visit', function (e) {
            e.preventDefault();
            $('#edit_visit_id').val($(this).data('id'));
            $('#edit_kegiatan').val($(this).data('kegiatan'));
            $('#edit_lokasi').val($(this).data('lokasi'));
            $('#edit_pic_name').val($(this).data('pic'));
            $('#edit_tanggal').val($(this).data('tanggal'));
            $('#edit_desc').val($(this).data('desc'));
            $('#edit_photo_path').val('');

            $('#editVisitModal').modal('show');
        });

        $('#formEditVisit').on('submit', function(e) {
            e.preventDefault();
            var visitId = $('#edit_visit_id').val();
            let formData = new FormData(this);
            formData.append('_method', 'PUT');

            var actionUrl = "{{ url('/visit-projects') }}/" + visitId;

            $.ajax({
                url: actionUrl,
                type: "POST",
                data: formData,
                contentType: false,
                processData: false,
                beforeSend: function() {
                    $('#btnUpdateVisit').prop('disabled', true).text('Memproses...');
                    $('#loadingModal').modal('show');
                },
                success: function(response) {
                    $('#editVisitModal').modal('hide');
                    $('#formEditVisit')[0].reset();
                    table.ajax.reload(null, false);
                },
                error: function(xhr) {
                    let errorMessage = 'Gagal memperbarui data.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    alert(errorMessage);
                },
                complete: function() {
                    $('#btnUpdateVisit').prop('disabled', false).text('Simpan Perubahan');
                    setTimeout(() => { $('#loadingModal').modal('hide'); }, 500);
                }
            });
        });

        $('#visitsTable tbody').on('click', '.btn-delete-visit', function (e) {
            e.preventDefault();
            var visitId = $(this).data('id');
            var actionUrl = "{{ url('/visit-projects') }}/" + visitId;

            if (confirm("Apakah Anda yakin ingin menghapus data log ini?")) {
                $.ajax({
                    url: actionUrl,
                    type: "POST",
                    data: {
                        _method: 'DELETE',
                        _token: '{{ csrf_token() }}'
                    },
                    beforeSend: function() {
                        $('#loadingModal').modal('show');
                    },
                    success: function(response) {
                        table.ajax.reload(null, false);
                    },
                    error: function(xhr) {
                        alert(xhr.responseJSON.message || 'Gagal menghapus data.');
                    },
                    complete: function() {
                        setTimeout(() => { $('#loadingModal').modal('hide'); }, 500);
                    }
                });
            }
        });
    });
</script>
@endpush
@endsection
