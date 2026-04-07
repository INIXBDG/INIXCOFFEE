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
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="d-flex justify-content-end mb-3">
                {{-- @can('Create Administrasi') --}}
                    <button type="button" class="btn btn-md click-primary mx-4" data-bs-toggle="modal" data-bs-target="#createModal">
                        <img src="{{ asset('icon/plus.svg') }}" class="" width="30px"> Tambah Administrasi
                    </button>
                {{-- @endcan --}}
            </div>

            <div class="modal fade" id="updateStageModal" tabindex="-1" aria-labelledby="updateStageModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="updateStageModalLabel">{{ __('Kelola Administrasi Proyek') }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form id="formUpdateStage" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" id="update_project_id" name="project_id">
                            
                            <div class="modal-body">
                                <h6 class="fw-bold mb-3 text-primary" id="update_project_name">Nama Proyek</h6>
                                
                                <div id="uploadSection">
                                    <div class="mb-3">
                                        <label for="current_stage" class="form-label">{{ __('Pilih Tahap Dokumen') }}</label>
                                        <select class="form-select" id="current_stage" name="current_stage">
                                            <option value="kak">KAK</option>
                                            <option value="penganggaran">Budget/Penganggaran</option>
                                            <option value="legal">Legal</option>
                                            <option value="dokumen_klien">Dokumen Klien</option>
                                            <option value="pembayaran">Pembayaran</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="file" class="form-label">{{ __('Unggah Dokumen (PDF/DOCX/JPG/PNG)') }}</label>
                                        <input class="form-control" type="file" id="file" name="file">
                                    </div>
                                </div>

                                <div id="decisionSection" style="display: none;">
                                    <div class="alert alert-success" role="alert">
                                        Seluruh dokumen administrasi telah lengkap. Tentukan status proyek selanjutnya.
                                    </div>
                                    <div class="mb-3">
                                        <label for="final_decision" class="form-label">{{ __('Keputusan Proyek') }}</label>
                                        <select class="form-select" id="final_decision" name="final_decision">
                                            <option value="">-- Pilih Keputusan --</option>
                                            <option value="lanjut">Lanjut ke Eksekusi (Project Task)</option>
                                            <option value="gagal">Gagal (Negosiasi Batal)</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Tutup') }}</button>
                                <button type="submit" class="btn btn-primary" id="btnUpdateSave">{{ __('Simpan Perubahan') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="createModal" tabindex="-1" aria-labelledby="createModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="createModalLabel">{{ __('Tambah Data Administrasi Proyek') }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form id="formCreateAdministrasi">
                            @csrf
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label for="nama_projek" class="form-label">{{ __('Nama Projek') }}</label>
                                    <input type="text" class="form-control" id="nama_projek" name="nama_projek" required>
                                </div>
                                <div class="mb-3">
                                    <label for="deskripsi" class="form-label">{{ __('Deskripsi Projek') }}</label>
                                    <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3"></textarea>
                                </div>
                                <div class="mb-3">
                                    <label for="perusahaan_key" class="form-label">{{ __('Klien Dari :') }}</label>
                                    <select style="height: 30px; width: 50%;" class="form-control @error('perusahaan_key') is-invalid @enderror" name="perusahaan_key" id="perusahaan_key">
                                    </select>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Tutup') }}</button>
                                <button type="submit" class="btn btn-primary" id="btnSave">{{ __('Simpan') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="card m-4">
                <div class="card-body table-responsive">
                    <h3 class="card-title text-center my-1">{{ __('Data Administrasi Projek') }}</h3>
                    <table class="table table-striped text-center" id="administrasiProjekTable">
                        <thead>
                            <tr>
                                <th scope="col">No</th>
                                <th scope="col">Nama Projek</th>
                                <th scope="col">Nama Perusahaan</th>
                                <th scope="col">Deskripsi</th>
                                <th scope="col">KAK</th>
                                <th scope="col">Budget</th>
                                <th scope="col">Legal</th>
                                <th scope="col">Dokumen Klien</th>
                                <th scope="col">Pembayaran</th>
                                <th scope="col">Status Pengerjaan</th>
                                <th scope="col">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .loader {
    position: relative;
    text-align: center;
    margin: 15px auto 35px auto;
    z-index: 9999;
    display: block;
    width: 80px;
    height: 80px;
    border: 10px solid rgba(0, 0, 0, .3);
    border-radius: 50%;
    border-top-color: #000;
    animation: spin 1s ease-in-out infinite;
    -webkit-animation: spin 1s ease-in-out infinite;
    }

    @keyframes spin {
    to {
        -webkit-transform: rotate(360deg);
    }
    }

    @-webkit-keyframes spin {
    to {
        -webkit-transform: rotate(360deg);
    }
    }
    .modal-content {
    border-radius: 0px;
    box-shadow: 0 0 20px 8px rgba(0, 0, 0, 0.7);
    }

    .modal-backdrop.show {
    opacity: 0.75;
    }

    .loader-txt p {
        font-size: 13px;
        color: #666;
    }
    .loader-txt p small {
        font-size: 11.5px;
        color: #999;
    }
</style>

@push('js')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function(){
        var userRole = '{{ auth()->user()->jabatan ?? '' }}';
        
        $('#administrasiProjekTable').DataTable({
            "ajax": {
                "url": "{{ route('getAdministrasi') }}",
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
                        console.log();
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
                {
                    "data": 'dataproject.name',
                },
                {
                    "data": 'dataproject.client.nama_perusahaan',
                },
                {
                    "data": 'dataproject.description',
                },
                {
                    "data": "kak_file",
                    "render": function(data, type, row) {
                        return data ? '<span class="text-success fw-bold">&#10004;</span>' : '<span class="text-danger fw-bold">&#10008;</span>';
                    }
                },
                {
                    "data": "budget_file",
                    "render": function(data, type, row) {
                        return data ? '<span class="text-success fw-bold">&#10004;</span>' : '<span class="text-danger fw-bold">&#10008;</span>';
                    }
                },
                {
                    "data": "legal_file",
                    "render": function(data, type, row) {
                        return data ? '<span class="text-success fw-bold">&#10004;</span>' : '<span class="text-danger fw-bold">&#10008;</span>';
                    }
                },
                {
                    "data": "client_doc_file",
                    "render": function(data, type, row) {
                        return data ? '<span class="text-success fw-bold">&#10004;</span>' : '<span class="text-danger fw-bold">&#10008;</span>';
                    }
                },
                {
                    "data": "payment_doc_file",
                    "render": function(data, type, row) {
                        return data ? '<span class="text-success fw-bold">&#10004;</span>' : '<span class="text-danger fw-bold">&#10008;</span>';
                    }
                },
                {
                    "data": "project.tasks",
                    "render": function(data, type, row) {
                        if (data && data.length > 0) {
                            var totalTasks = data.length;
                            var completedTasks = data.filter(task => task.status === 'selesai').length;
                            return completedTasks + ' / ' + totalTasks + ' Selesai';
                        }
                        return 'Belum ada tugas';
                    }
                },
                {
                    "data": null,
                    "render": function(data, type, row) {
                        var actions = "";
                        // Logika pengecekan kelengkapan dokumen
                        var isComplete = row.kak_file && row.budget_file && row.legal_file && row.client_doc_file && row.payment_doc_file;
                        
                        if (row.dataproject.phase === 'teknis') {
                            actions += '<span class="badge bg-success">Fase Teknis Aktif</span>';
                        } else if (row.dataproject.phase === 'gagal') {
                            actions += '<span class="badge bg-danger">Proyek Gagal</span>';
                        } else {
                            var btnClass = isComplete ? 'btn-success' : 'btn-primary';
                            var btnText = isComplete ? 'Keputusan Akhir' : 'Kelola Dokumen';
                            // Menyematkan kelas khusus untuk diikat pada event listener jQuery
                            actions += '<button class="btn btn-sm ' + btnClass + ' btn-update-stage">' + btnText + '</button>';
                        }
                        
                        return actions;
                    }
                },
            ]
        });

        // Inisialisasi Select2 untuk Perusahaan/Klien
        $('#perusahaan_key').select2({
            placeholder: "Pilih Perusahaan",
            allowClear: true,
            dropdownParent: $('#createModal'), // Konfigurasi wajib untuk Select2 di dalam Modal Bootstrap
            ajax: {
                url: '{{route('getPerusahaan')}}',
                processResults: function({data}){
                    return{
                        results: $.map(data, function(item){
                            return {
                                id: item.id,
                                text: item.nama_perusahaan
                            }
                        })
                    }
                }
            }
        });

        // Penanganan Submit Form Create Administrasi
        $('#formCreateAdministrasi').on('submit', function(e) {
            e.preventDefault();
            let formData = $(this).serialize();

            $.ajax({
                url: "{{ route('administrasi.store') }}",
                type: "POST",
                data: formData,
                beforeSend: function() {
                    $('#btnSave').prop('disabled', true).text('Menyimpan...');
                    $('#loadingModal').modal('show');
                },
                success: function(response) {
                    $('#createModal').modal('hide');
                    $('#formCreateAdministrasi')[0].reset();
                    $('#perusahaan_key').val(null).trigger('change'); // Reset elemen Select2
                    $('#administrasiProjekTable').DataTable().ajax.reload(null, false);
                },
                error: function(xhr) {
                    let errorMessage = 'Terjadi kesalahan saat menyimpan data.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    alert(errorMessage);
                },
                complete: function() {
                    $('#btnSave').prop('disabled', false).text('Simpan');
                    setTimeout(() => {
                        $('#loadingModal').modal('hide');
                    }, 500);
                }
            });
        });

        // Event Listener untuk tombol Kelola Dokumen di dalam DataTables
        $('#administrasiProjekTable tbody').on('click', '.btn-update-stage', function () {
            var data = $('#administrasiProjekTable').DataTable().row($(this).parents('tr')).data();
            console.log(data);
            bukaModalUpdate(data);
        });

        function bukaModalUpdate(rowData) {
            $('#formUpdateStage')[0].reset();
            $('#update_project_id').val(rowData.dataproject.id);
            $('#update_project_name').text(rowData.dataproject.name);

            // Validasi kelengkapan berkas
            var isComplete = rowData.kak_file && rowData.budget_file && rowData.legal_file && rowData.client_doc_file && rowData.payment_doc_file;

            if (isComplete) {
                // Tampilkan antarmuka keputusan akhir
                $('#uploadSection').hide();
                $('#decisionSection').show();
                
                // Menonaktifkan validasi wajib pada unggah file
                $('#file').removeAttr('required');
                $('#final_decision').attr('required', true);
            } else {
                // Tampilkan antarmuka unggah dokumen
                $('#uploadSection').show();
                $('#decisionSection').hide();
                
                $('#final_decision').removeAttr('required');
                $('#file').attr('required', true);

                // Otomatis memilih dropdown dokumen mana yang belum terisi
                if (!rowData.kak_file) $('#current_stage').val('kak');
                else if (!rowData.budget_file) $('#current_stage').val('penganggaran');
                else if (!rowData.legal_file) $('#current_stage').val('legal');
                else if (!rowData.client_doc_file) $('#current_stage').val('dokumen_klien');
                else if (!rowData.payment_doc_file) $('#current_stage').val('pembayaran');
            }

            $('#updateStageModal').modal('show');
        }

        // Penanganan Submit Form Update Stage (Menggunakan FormData untuk berkas)
        $('#formUpdateStage').on('submit', function(e) {
            e.preventDefault();
            var projectId = $('#update_project_id').val();
            var formData = new FormData(this);
            var actionUrl = "{{ url('/projects/administrasi') }}/" + projectId + "/update-stage";

            $.ajax({
                url: actionUrl,
                type: "POST",
                data: formData,
                contentType: false, // Wajib disetel false untuk transmisi berkas
                processData: false, // Wajib disetel false untuk transmisi berkas
                beforeSend: function() {
                    $('#btnUpdateSave').prop('disabled', true).text('Memproses...');
                    $('#loadingModal').modal('show');
                },
                success: function(response) {
                    $('#updateStageModal').modal('hide');
                    $('#formUpdateStage')[0].reset();
                    $('#administrasiProjekTable').DataTable().ajax.reload(null, false);
                },
                error: function(xhr) {
                    let errorMessage = 'Terjadi kesalahan saat memproses data.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    alert(errorMessage);
                },
                complete: function() {
                    $('#btnUpdateSave').prop('disabled', false).text('Simpan Perubahan');
                    setTimeout(() => {
                        $('#loadingModal').modal('hide');
                    }, 500);
                }
            });
        });
    });
</script>
@endpush
@endsection