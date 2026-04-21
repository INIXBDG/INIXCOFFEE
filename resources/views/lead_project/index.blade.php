@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="modal fade" id="loadingModal" tabindex="-1" aria-labelledby="spinnerModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="cube">
                <div class="cube_item cube_x"></div>
                <div class="cube_item cube_y"></div>
                <div class="cube_item cube_x"></div>
                <div class="cube_item cube_z"></div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="createLeadModal" tabindex="-1" aria-labelledby="createLeadModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createLeadModalLabel">{{ __('Tambah Data Lead') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formCreateLead">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="nama_lead" class="form-label">{{ __('Nama Lead / Prospek') }}</label>
                            <input type="text" class="form-control" id="nama_lead" name="nama_lead" required>
                        </div>
                        <div class="mb-3">
                            <label for="perusahaan_id" class="form-label">{{ __('Perusahaan (Klien)') }}</label>
                            <select class="form-control" name="perusahaan_id" id="perusahaan_id" style="width: 100%;" required></select>
                        </div>
                        <div class="mb-3">
                            <label for="estimasi_nilai" class="form-label">{{ __('Estimasi Nilai (Rp)') }}</label>
                            <input type="number" class="form-control" id="estimasi_nilai" name="estimasi_nilai" min="0" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Tutup') }}</button>
                        <button type="submit" class="btn btn-primary" id="btnSaveLead">{{ __('Simpan') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="updateStatusModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('Perbarui Tahapan Lead') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formUpdateStatus">
                    @csrf
                    <input type="hidden" id="update_lead_id" name="lead_id">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="lead_status" class="form-label">{{ __('Tahapan Saat Ini') }}</label>
                            <select class="form-select" id="lead_status" name="status" required>
                                <option value="penawaran_awal">Penawaran Awal</option>
                                <option value="permintaan_klien">Permintaan Klien</option>
                                <option value="meeting_klien">Meeting Klien</option>
                                <option value="dokumen_penawaran">Dokumen Penawaran (Mulai Administrasi)</option>
                                <option value="mengirim_proposal_teknis">Mengirim Proposal Teknis</option>
                                <option value="surat_penawaran">Surat Penawaran</option>
                                <option value="won">Won (Berhasil)</option>
                                <option value="lost">Lost (Gagal / Soft Delete)</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Tutup') }}</button>
                        <button type="submit" class="btn btn-primary" id="btnUpdateStatus">{{ __('Simpan Perubahan') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="d-flex justify-content-end mb-3">
                <button type="button" class="btn btn-md btn-primary mx-4" data-bs-toggle="modal" data-bs-target="#createLeadModal">
                    <img src="{{ asset('icon/plus.svg') }}" class="" width="20px"> Tambah Lead
                </button>
            </div>
            <div class="card m-4">
                <div class="card-body table-responsive">
                    <h3 class="card-title text-center my-1">{{ __('Data Leads (Prospek)') }}</h3>
                    <table class="table table-striped text-center" id="leadsTable">
                        <thead>
                            <tr>
                                <th scope="col">No</th>
                                <th scope="col">Nama Lead</th>
                                <th scope="col">Perusahaan</th>
                                <th scope="col">Estimasi Nilai</th>
                                <th scope="col">Tahapan / Status</th>
                                <th scope="col">Proyek Terhubung</th>
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
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    $(document).ready(function(){
        const formatRupiah = (angka) => {
            return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(angka);
        };

        const formatStatus = (status) => {
            const labels = {
                'penawaran_awal': '<span class="badge bg-secondary">Penawaran Awal</span>',
                'permintaan_klien': '<span class="badge bg-info text-dark">Permintaan Klien</span>',
                'meeting_klien': '<span class="badge bg-primary">Meeting Klien</span>',
                'dokumen_penawaran': '<span class="badge bg-warning text-dark">Dokumen Penawaran</span>',
                'mengirim_proposal_teknis': '<span class="badge bg-warning text-dark">Proposal Teknis</span>',
                'surat_penawaran': '<span class="badge bg-warning text-dark">Surat Penawaran</span>',
                'won': '<span class="badge bg-success">Won</span>',
                'lost': '<span class="badge bg-danger">Lost</span>'
            };
            return labels[status] || status;
        };

        var table = $('#leadsTable').DataTable({
            "ajax": {
                "url": "{{ route('leads.data') }}",
                "type": "GET",
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
                {"data": "nama_lead"},
                {
                    "data": "client.nama_perusahaan",
                    "render": function(data) { return data ? data : '-'; }
                },
                {
                    "data": "estimasi_nilai",
                    "render": function(data) { return formatRupiah(data); }
                },
                {
                    "data": "status",
                    "render": function(data) { return formatStatus(data); }
                },
                {
                    "data": "project.phase",
                    "render": function(data) { 
                        return data ? '<span class="text-success fw-bold">Tahap ' + data.toUpperCase() + '</span>' : '-'; 
                    }
                },
                {
                    "data": null,
                    "render": function(data, type, row) {
                        var actions = '<div class="dropdown">';
                        actions += '<button class="btn btn-sm btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Aksi</button>';
                        actions += '<div class="dropdown-menu">';
                        actions += '<a class="dropdown-item btn-edit-status" href="#" data-id="' + row.id + '" data-status="' + row.status + '">Perbarui Tahapan</a>';
                        actions += '</div></div>';
                        return actions;
                    }
                }
            ]
        });

        $('#perusahaan_id').select2({
            placeholder: "Pilih Perusahaan",
            allowClear: true,
            dropdownParent: $('#createLeadModal'),
            ajax: {
                url: '{{ route("getPerusahaan") }}',
                processResults: function({data}){
                    return {
                        results: $.map(data, function(item){
                            return { id: item.id, text: item.nama_perusahaan }
                        })
                    }
                }
            }
        });

        $('#formCreateLead').on('submit', function(e) {
            e.preventDefault();
            let formData = $(this).serialize();

            $.ajax({
                url: "{{ route('leads.store') }}",
                type: "POST",
                data: formData,
                beforeSend: function() {
                    $('#btnSaveLead').prop('disabled', true).text('Menyimpan...');
                    $('#loadingModal').modal('show');
                },
                success: function(response) {
                    $('#createLeadModal').modal('hide');
                    $('#formCreateLead')[0].reset();
                    $('#perusahaan_id').val(null).trigger('change');
                    table.ajax.reload(null, false);
                },
                error: function(xhr) {
                    alert(xhr.responseJSON.message || 'Terjadi kesalahan sistem.');
                },
                complete: function() {
                    $('#btnSaveLead').prop('disabled', false).text('Simpan');
                    setTimeout(() => { $('#loadingModal').modal('hide'); }, 500);
                }
            });
        });

        $('#leadsTable tbody').on('click', '.btn-edit-status', function (e) {
            e.preventDefault();
            var leadId = $(this).data('id');
            var currentStatus = $(this).data('status');

            $('#update_lead_id').val(leadId);
            $('#lead_status').val(currentStatus);
            $('#updateStatusModal').modal('show');
        });

        $('#formUpdateStatus').on('submit', function(e) {
            e.preventDefault();
            var leadId = $('#update_lead_id').val();
            var formData = $(this).serialize();

            $.ajax({
                url: "{{ url('/project/leads') }}/" + leadId + "/update-status",
                type: "POST",
                data: formData,
                beforeSend: function() {
                    $('#btnUpdateStatus').prop('disabled', true).text('Memproses...');
                    $('#loadingModal').modal('show');
                },
                success: function(response) {
                    $('#updateStatusModal').modal('hide');
                    table.ajax.reload(null, false);
                },
                error: function(xhr) {
                    alert(xhr.responseJSON.message || 'Gagal memperbarui tahapan.');
                },
                complete: function() {
                    $('#btnUpdateStatus').prop('disabled', false).text('Simpan Perubahan');
                    setTimeout(() => { $('#loadingModal').modal('hide'); }, 500);
                }
            });
        });
    });
</script>
@endpush
@endsection