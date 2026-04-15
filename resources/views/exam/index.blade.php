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
        <div class="modal fade" id="uploadInvoiceModal" tabindex="-1" aria-labelledby="uploadInvoiceModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="uploadInvoiceModalLabel">Upload Invoice</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form id="formUploadInvoice" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="modal-body">
                            <div class="form-group mb-3">
                                <label for="file_invoice" class="form-label">Pilih File Invoice (Bisa pilih 1 atau banyak
                                    file sekaligus. Max 10MB/file)</label>
                                <input type="file" class="form-control" id="file_invoice" name="file_invoice[]" required
                                    accept=".pdf,.jpg,.jpeg,.png" multiple>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                            <button type="submit" class="btn btn-primary">Upload</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="row justify-content-center">
            <div class="col-md-12 d-flex my-2 justify-content-end">
                @can('Create Exam')
                    <a class="btn click-primary mx-1" href="{{ route('exam.createOnly') }}">Create Exam</a>
                @endcan
            </div>
            <div class="col-md-12">
                {{-- @can('Rekap Exam') --}}
                <a href="{{ route('exam.rekapexam') }}" class="btn btn-md click-primary mx-4" data-toggle="tooltip"
                    data-placement="top" title="Tambah Perusahaan"><img src="{{ asset('icon/plus.svg') }}" class=""
                        width="30px"> Rekap Exam</a>
                {{-- @endcan --}}
                <div class="card m-4">
                    <div class="card-body table-responsive">
                        <h3 class="card-title text-center my-1">{{ __('Data Pengajuan Exam') }}</h3>
                        <table class="table table-striped" id="examtable">
                            <thead>
                                <tr>
                                    <th scope="col">No</th>
                                    <th scope="col">Nama Materi</th>
                                    <th scope="col">Tanggal Periode</th>
                                    <th scope="col">Nama Perusahaan</th>
                                    <th scope="col">Pax</th>
                                    <th scope="col">sales</th>
                                    <th scope="col">instruktur</th>
                                    <th scope="col">created_at</th>
                                    <th scope="col">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>

                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card m-4">
                    <div class="card-body table-responsive">
                        <h3 class="card-title text-center my-1">{{ __('Data Histori Exam') }}</h3>
                        <table class="table table-striped" id="examhistoritable">
                            <thead>
                                <tr>
                                    <th scope="col">No</th>
                                    <th scope="col">Nama Materi</th>
                                    <th scope="col">Tanggal Pengajuan</th>
                                    <th scope="col">Nama Perusahaan</th>
                                    <th scope="col">Pax</th>
                                    <th scope="col">Status</th>
                                    <th scope="col">Sales</th>
                                    <th scope="col">instruktur</th>
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

        .loader-txt {
            p {
                font-size: 13px;
                color: #666;

                small {
                    font-size: 11.5px;
                    color: #999;
                }
            }
        }
    </style>
    @push('js')
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script type="text/javascript" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
        <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
        <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/locale/id.min.js"></script>

        <script>
            var userJabatan = '{{ auth()->user()->jabatan }}';
            var userIdSales = '{{ auth()->user()->id_sales }}';

            $(document).ready(function () {
                var userRole = '{{ auth()->user()->jabatan}}';
                var idInstruktur = "{{ auth()->user()->id_instruktur }}";
                var idSales = "{{ auth()->user()->id_sales }}";
                if (idInstruktur == 'AD') {
                    var idInstruktur = "";
                }
                if (idSales == 'AM') {
                    var idSales = "";
                }
                if (userRole == "Technical Support") {
                    var idInstruktur = "";
                }
                var tableIndex1 = 1;
                var tableIndex2 = 1;

                $('#examtable').DataTable({
                    "ajax": {
                        "url": "{{ route('getExam') }}", // URL API untuk mengambil data
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
                            "render": function (data) {
                                return tableIndex1++;
                            }
                        },
                        { "data": "materi.nama_materi" },
                        {
                            "data": null,
                            "render": function (data, type, row) {
                                if (data.tanggal_awal && data.tanggal_akhir) {
                                    var tanggalAwal = moment(data.tanggal_awal).format('LL'); // Format Tanggal dalam Bahasa Indonesia
                                    var tanggalAkhir = moment(data.tanggal_akhir).format('LL'); // Format Tanggal dalam Bahasa Indonesia
                                    return tanggalAwal + " s/d " + tanggalAkhir;
                                } else {
                                    return "";
                                }
                            }
                        },
                        { "data": "perusahaan.nama_perusahaan" },
                        { "data": "pax" },
                        {
                            "data": "sales_key",
                            "visible": false
                        },
                        {
                            "data": "instruktur_key",
                            "visible": false
                        },
                        {
                            "data": null,
                            "render": function (data, type, row) {
                                var created_at = moment(data.created_at).format('LL'); // Format Tanggal dalam Bahasa Indonesia
                                return created_at;
                            },
                            "visible": false
                        },
                        {
                            "data": null,
                            "render": function (data, type, row) {
                                var actions = "";
                                actions += '@if (auth()->user()->can('Create Exam'))'
                                    actions += '<a href="/pengajuanExam/' + data.id + '" class="btn btn-md click-primary mx-4" data-toggle="tooltip" data-placement="top" title="Pengajuan Exam"> Ajukan Exam</a>';
                                actions += '@else';
                                    actions += '<a href="/pengajuanExam/' + data.id + '" class="btn disabled btn-md click-primary mx-4" data-toggle="tooltip" data-placement="top" title="Pengajuan Exam"> Ajukan Exam</a>';
                                actions += '@endif';
                                actions += '@if (auth()->user()->can('Daftar Peserta Exam'))'
                                    actions += '<a href="/daftar-peserta-exam/create/' + data.id + '" class="btn btn-md click-primary mx-4">Assign</a>';
                                actions += '@endif';
                                return actions;
                            }
                        },
                        {
                            "data": null, // Kolom tidak terlihat untuk menyimpan tanggal dalam format ISO
                            "render": function (data, type, row) {
                                return data.tanggal_akhir ? moment(data.tanggal_akhir).format('YYYY-MM-DD') : "";
                            },
                            "visible": false
                        }
                    ],
                    "order": [[9, 'desc']], // Urutkan berdasarkan kolom tanggal akhir dalam format ISO
                    "initComplete": function () {
                        this.api().columns(6).search(idInstruktur).draw();
                        this.api().columns(5).search(idSales).draw();
                    }
                });


                $('#examhistoritable').DataTable({
                    "ajax": {
                        "url": "{{ route('getHistoriExam') }}", // URL API untuk mengambil data
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
                            "render": function (data) {
                                return tableIndex2++;
                            }
                        },
                        {   // Nama Materi
                            "data": null,
                            "render": function (data) {
                                return data.materi?.nama_materi ?? data.rkm?.materi?.nama_materi ?? '-';
                            }
                        },
                        {   // Tanggal
                            "data": null,
                            "render": function (data) {
                                return data.tanggal_pengajuan ? moment(data.tanggal_pengajuan).format('LL') : '-';
                            }
                        },
                        {   // Nama Perusahaan
                            "data": null,
                            "render": function (data) {
                                return data.perusahaan?.nama_perusahaan ?? data.rkm?.perusahaan?.nama_perusahaan ?? '-';
                            }
                        },
                        { "data": "pax" },
                        {   // Status dengan badge
                            "data": null,
                            "render": function (data) {
                                var statusBadge = '';
                                if (data.status == '3') {
                                    statusBadge = '<span class="badge bg-info">Exam Only</span>';
                                    // Check if room assigned
                                    if (data.rkm && data.rkm.ruang && data.rkm.ruang !== 'Exam') {
                                        statusBadge += ' <span class="badge bg-success">Room Assigned</span>';
                                    }
                                } else {
                                    statusBadge = '<span class="badge bg-primary">Regular Exam</span>';
                                }
                                return statusBadge;
                            }
                        },
                        {
                            "data": null,
                            "visible": true,
                            "render": function (data) {
                                return data.rkm?.sales_key ?? '-';
                            }
                        },
                        {
                            "data": null,
                            "visible": false,
                            "render": function (data) {
                                return data.rkm?.instruktur_key ?? '';
                            }
                        },
                        {
                            "data": null,
                            "render": function (data, type, row) {
                                var actions = "";
                                actions += '<div class="dropdown">';
                                actions += '<button class="btn dropdown-toggle" type="button" data-bs-toggle="dropdown">Actions</button>';
                                actions += '<div class="dropdown-menu">';
                                actions += '<a class="dropdown-item" href="{{ url('/exam') }}/' + row.id + '">Detail</a>';

                                if (row.approvalexam && row.approvalexam.spv_sales == 1) {
                                    var files = row.file_invoice;
                                    if (typeof files === 'string') {
                                        try { files = JSON.parse(files); } catch (e) { files = []; }
                                    }

                                    if (files && Array.isArray(files) && files.length > 0) {
                                        files.forEach(function (file, index) {
                                            var fileUrl = "{{ asset('uploads/invoices') }}/" + file;
                                            actions += '<a class="dropdown-item text-primary" href="' + fileUrl + '" target="_blank"><i class="fas fa-file-invoice"></i> Lihat File ' + (index + 1) + '</a>';
                                            actions += '<form onsubmit="return confirm(\'Apakah Anda yakin ingin menghapus File ' + (index + 1) + ' ini?\');" action="/exam/' + row.id + '/delete-invoice/' + file + '" method="POST" style="display:inline; margin:0; padding:0;">';
                                            actions += '@csrf @method("DELETE")';
                                            actions += '<button type="submit" class="dropdown-item text-danger"><i class="fas fa-trash"></i> Hapus File ' + (index + 1) + '</button>';
                                            actions += '</form>';
                                        });

                                        actions += '<div class="dropdown-divider"></div>';
                                        actions += '<a class="dropdown-item text-success" href="javascript:void(0)" onclick="openUploadModal(' + row.id + ')"><i class="fas fa-plus"></i> Tambah Invoice Lagi</a>';
                                    } else {
                                        actions += '<a class="dropdown-item text-success" href="javascript:void(0)" onclick="openUploadModal(' + row.id + ')"><i class="fas fa-upload"></i> Upload Invoice</a>';
                                    }
                                    actions += '<div class="dropdown-divider"></div>';
                                }

                                if (row.status == '3') {
                                    var roomAssigned = row.rkm && row.rkm.ruang && row.rkm.ruang !== 'Exam';
                                    if (!roomAssigned) {
                                        actions += '@can("Edit Exam")<a class="dropdown-item" href="{{ route('exam.assignRoom', '') }}/' + row.id + '"><i class="fas fa-home"></i> Assign Ruangan</a>@endcan';
                                    } else {
                                        actions += '<a class="dropdown-item text-success" href="#"><i class="fas fa-check"></i> Ruangan: ' + (row.rkm.ruang || '-') + '</a>';
                                    }
                                }

                                actions += '@can("Edit Exam")<a class="dropdown-item" href="{{ url('/exam') }}/' + row.id + '/edit">Edit</a>@endcan';

                                var examSalesKey = row.sales_key || (row.rkm?.sales_key) || '';
                                var canDelete = (userJabatan.trim() === 'SPV Sales') || (examSalesKey === userIdSales);

                                if (canDelete) {
                                    actions += '<form onsubmit="return confirm(\'⚠️ Yakin ingin menghapus Exam ini? Tindakan tidak dapat dibatalkan.\');" action="{{ url('/exam') }}/' + row.id + '" method="POST">';
                                    actions += '@csrf @method("DELETE")';
                                    actions += '<button type="submit" class="dropdown-item text-danger"><i class="fas fa-trash"></i> Hapus</button>';
                                    actions += '</form>';
                                }
                                actions += '</div></div>';
                                return actions;
                            }
                        }
                    ],

                    "order": [[0, 'asc']], // Ubah urutan menjadi descending untuk kolom ke-6
                    // "columnDefs" : [{"targets":[2], "type":"date"}],
                    "initComplete": function () {
                        this.api().columns(7).search(idInstruktur).draw();
                        this.api().columns(6).search(idSales).draw();
                    }
                });
            });
            function openUploadModal(id) {
                var form = document.getElementById('formUploadInvoice');
                form.action = '/exam/' + id + '/upload-invoice';
                var uploadModal = new bootstrap.Modal(document.getElementById('uploadInvoiceModal'));
                uploadModal.show();
            }
        </script>
    @endpush
@endsection