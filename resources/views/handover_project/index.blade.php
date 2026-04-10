@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        {{-- Modal Loading Spinner (Sesuai Referensi) --}}
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

                {{-- Modal Unggah BAST --}}
                <div class="modal fade" id="uploadBastModal" tabindex="-1" aria-labelledby="uploadBastModalLabel"
                    aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="uploadBastModalLabel">{{ __('Unggah Dokumen Serah Terima') }}
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <form id="formUploadBast" enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" id="bast_project_id" name="project_id">

                                <div class="modal-body">
                                    <h6 class="fw-bold mb-3 text-primary" id="bast_project_name">Nama Proyek</h6>

                                    <div class="alert alert-success" role="alert">
                                        Tugas teknis proyek ini telah selesai 100%. Silakan unggah BAST untuk menutup
                                        proyek.
                                    </div>

                                    <div class="mb-3">
                                        <label for="bast_file"
                                            class="form-label">{{ __('Pilih Dokumen BAST (PDF/DOCX/JPG/PNG)') }}</label>
                                        <input class="form-control" type="file" id="bast_file" name="bast_file" required>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary"
                                        data-bs-dismiss="modal">{{ __('Tutup') }}</button>
                                    <button type="submit" class="btn btn-primary"
                                        id="btnSaveBast">{{ __('Simpan & Selesaikan') }}</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- Tabel Handover --}}
                <div class="card m-4">
                    <div class="card-body table-responsive">
                        <h3 class="card-title text-center my-1">{{ __('Data Serah Terima Proyek (Handover)') }}</h3>
                        <table class="table table-striped text-center" id="handoverTable">
                            <thead>
                                <tr>
                                    <th scope="col">No</th>
                                    <th scope="col">Nama Projek</th>
                                    <th scope="col">Nama Perusahaan</th>
                                    <th scope="col">Project Manager</th>
                                    <th scope="col">Status Tugas Teknis</th>
                                    <th scope="col">Dokumen BAST</th>
                                    <th scope="col">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- Diisi oleh DataTables AJAX --}}
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
        <script>
            $(document).ready(function () {
                const userRole = '{{ auth()->user()->jabatan ?? '' }}';
                const currentUserKaryawanId = '{{ auth()->user()->karyawan->kode_karyawan ?? '' }}';
                $('#handoverTable').DataTable({
                    "ajax": {
                        "url": "{{ route('handovers.data') }}",
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
                        {
                            "data": 'name',
                        },
                        {
                            "data": 'client.nama_perusahaan',
                            "render": function (data) { return data ? data : '-'; }
                        },
                        {
                            "data": 'administration.project_manager.nama_lengkap',
                            "render": function (data) { return data ? data : '-'; }
                        },
                        {
                            "data": 'tasks',
                            "render": function (data, type, row) {
                                if (data && data.length > 0) {
                                    var totalTasks = data.length;
                                    var completedTasks = data.filter(task => task.status === 'validate' || task.status === 'evaluasi').length;
                                    return completedTasks + ' / ' + totalTasks + ' Selesai';
                                }
                                return 'Belum ada tugas';
                            }
                        },
                        {
                            "data": 'handover.bast_file',
                            "render": function (data, type, row) {
                                return data ? '<span class="text-success fw-bold">Ada</span>' : '<span class="text-danger fw-bold">Menunggu</span>';
                            }
                        },
                        {
                            "data": null,
                            "render": function (data, type, row) {
                                var actions = "";

                                var pmId = null;
                                if (row.administration && row.administration.project_manager) {
                                    pmId = row.administration.project_manager.kode_karyawan;
                                }

                                var isPM = (currentUserKaryawanId === pmId);
                                var isAuthorizedRole = (userRole === 'SPV Sales' || userRole === 'GM');
                                var canUpload = (isPM || isAuthorizedRole);

                                if (row.phase === 'selesai' || (row.handover && row.handover.bast_file)) {
                                    actions += '<span class="badge bg-success">Proyek Selesai</span>';
                                } else {
                                    if (canUpload) {
                                        actions += '<button class="btn btn-sm btn-primary btn-upload-bast">Unggah BAST</button>';
                                    } else {
                                        actions += '<span class="badge bg-secondary">Menunggu PM/Sales</span>';
                                    }
                                }

                                return actions;
                            }
                        }
                    ]
                });

                // Penanganan Buka Modal Unggah BAST
                $('#handoverTable tbody').on('click', '.btn-upload-bast', function () {
                    var data = $('#handoverTable').DataTable().row($(this).parents('tr')).data();

                    $('#formUploadBast')[0].reset();
                    $('#bast_project_id').val(data.id);
                    $('#bast_project_name').text(data.name);

                    $('#uploadBastModal').modal('show');
                });

                // Penanganan Submit Form BAST (Mirip dengan Submit Administrasi)
                $('#formUploadBast').on('submit', function (e) {
                    e.preventDefault();

                    var projectId = $('#bast_project_id').val(); // Mengambil ID dari elemen input tersembunyi
                    var formData = new FormData(this);
                    var actionUrl = "{{ url('/projects/handovers') }}/" + projectId + "/upload-bast"; // Konstruksi URL dinamis

                    $.ajax({
                        url: actionUrl,
                        type: "POST",
                        data: formData,
                        contentType: false,
                        processData: false,
                        beforeSend: function () {
                            $('#btnSaveBast').prop('disabled', true).text('Memproses...');
                            $('#loadingModal').modal('show');
                        },
                        success: function (response) {
                            if (response.success) {
                                $('#uploadBastModal').modal('hide');
                                $('#formUploadBast')[0].reset();
                                $('#handoverTable').DataTable().ajax.reload(null, false);
                            } else {
                                alert(response.message);
                            }
                        },
                        error: function (xhr) {
                            let errorMessage = 'Terjadi kesalahan saat memproses data.';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMessage = xhr.responseJSON.message;
                            }
                            alert(errorMessage);
                        },
                        complete: function () {
                            $('#btnSaveBast').prop('disabled', false).text('Simpan & Selesaikan');
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