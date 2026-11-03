@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <!-- Modal Loading -->
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

        <!-- Modal Upload Invoice -->
        <div class="modal fade" id="uploadInvoiceModal" tabindex="-1" aria-labelledby="uploadInvoiceModalLabel" aria-hidden="true">
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
                                <label for="file_invoice" class="form-label">Pilih File Invoice (Bisa pilih 1 atau banyak file sekaligus. Max 10MB/file)</label>
                                <input type="file" class="form-control" id="file_invoice" name="file_invoice[]" required accept=".pdf,.jpg,.jpeg,.png" multiple>
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

        <!-- Modal Update Tanggal -->
        <div class="modal fade" id="updateTanggalModal" tabindex="-1" aria-labelledby="updateTanggalModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="updateTanggalModalLabel">Set Tanggal Pelaksanaan Exam</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form id="formUpdateTanggal" method="POST">
                        @csrf
                        <div class="modal-body">
                            <div class="form-group mb-3">
                                <label for="tanggal_mulai" class="form-label">Tanggal Mulai Exam <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="tanggal_mulai" name="tanggal_mulai" required>
                            </div>
                            <div class="form-group mb-3">
                                <label for="tanggal_selesai" class="form-label">Tanggal Selesai Exam <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="tanggal_selesai" name="tanggal_selesai" required>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary">Simpan Tanggal</button>
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
                    data-placement="top" title="Tambah Perusahaan">
                    <img src="{{ asset('icon/plus.svg') }}" class="" width="30px"> Rekap Exam
                </a>
                {{-- @endcan --}}

                <!-- 1. Tabel Data Pengajuan Exam -->
                <div class="card m-4">
                    <div class="card-body table-responsive">
                        <h3 class="card-title text-center my-1">{{ __('Data Pengajuan Exam') }}</h3>
                        <table class="table table-striped w-100" id="examtable">
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
                                    <th scope="col">tanggal_akhir_sort</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>

                <!-- 2. Tabel Data Exam Sertifa -->
                <div class="card m-4">
                    <div class="card-body table-responsive">
                        <h3 class="card-title text-center my-1">{{ __('Data Exam Sertifa') }}</h3>
                        <table class="table table-striped w-100" id="poExamTableIndex">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Materi</th>
                                    <th>Tanggal Exam</th>
                                    <th>Perusahaan</th>
                                    <th>Pax</th>
                                    <th>Harga</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>

                <!-- 3. Tabel Data Pengajuan Exam Kondisional -->
                @if (in_array(auth()->user()->jabatan, ['Technical Support', 'Admin Holding']))
                    <div class="card m-4">
                        <div class="card-body table-responsive">
                            <div class="d-flex justify-content-end align-items-center">
                                <a class="btn btn-md click-primary mx-4" href="{{ route('manualCreate.exam') }}">
                                    <img src="{{ asset('icon/plus.svg') }}" width="30px"> Add Pengajuan
                                </a>
                            </div>
                            <h3 class="card-title text-center my-1">{{ __('Data Pengajuan Exam BNSP - EC Council - Inixcert') }}</h3>
                            <table class="table table-striped w-100" id="examconditionaltable">
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
                                        <th scope="col">tanggal_akhir_sort</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                @endif

                <!-- 4. Tabel Data Histori Exam -->
                <div class="card m-4">
                    <div class="card-body table-responsive">
                        <h3 class="card-title text-center my-1">{{ __('Data Histori Exam') }}</h3>
                        <table class="table table-striped w-100" id="examhistoritable">
                            <thead>
                                <tr>
                                    <th scope="col">No</th>
                                    <th scope="col">Nama Materi</th>
                                    <th scope="col">Tanggal Pengajuan</th>
                                    <th scope="col">Nama Perusahaan</th>
                                    <th scope="col">Pax</th>
                                    <th scope="col">Status</th>
                                    <th scope="col">Status Approval</th>
                                    <th scope="col">Sales</th>
                                    <th scope="col">instruktur</th>
                                    <th scope="col">Aksi</th>
                                    @if (auth()->user()->jabatan == 'Finance & Accounting')
                                        <th scope="col">Pengajuan Barang</th>
                                    @endif
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
        /* Mencegah tabel blank / terpotong saat dropdown dibuka di dalam table-responsive */
        .table-responsive {
            overflow-x: auto;
            overflow-y: visible !important;
            min-height: 250px; /* Menjaga ruang agar dropdown pada baris sedikit tidak terpotong */
        }

        /* Memastikan card tidak memotong dropdown yang keluar batas */
        .card {
            overflow: visible;
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
            var csrfToken = '{{ csrf_token() }}';

            $(document).ready(function () {
                var userRole = '{{ auth()->user()->jabatan }}';
                var idInstruktur = "{{ auth()->user()->id_instruktur }}";
                var idSales = "{{ auth()->user()->id_sales }}";

                if (idInstruktur == 'AD' || userRole == "Technical Support") { idInstruktur = ""; }
                if (idSales == 'VN' || userRole == 'SPV Sales') { idSales = ""; }

                // Pengaturan event modal agar terdaftar satu kali saja
                $('#loadingModal').on('show.bs.modal', function () {
                    $(this).removeAttr('inert');
                });
                $('#loadingModal').on('hidden.bs.modal', function () {
                    $(this).attr('inert', true);
                });

                // Counter AJAX agar modal tidak saling tumpang tindih (Race Condition)
                var activeAjaxCount = 0;

                function showLoadingModal() {
                    if (activeAjaxCount === 0) {
                        $('#loadingModal').modal('show');
                    }
                    activeAjaxCount++;
                }

                function hideLoadingModal() {
                    activeAjaxCount--;
                    if (activeAjaxCount <= 0) {
                        activeAjaxCount = 0;
                        setTimeout(function () {
                            if (activeAjaxCount === 0) {
                                $('#loadingModal').modal('hide');
                            }
                        }, 500);
                    }
                }

                // Helper fungsi penomoran otomatis DataTables
                function renderRowNumber(data, type, row, meta) {
                    return meta.row + meta.settings._iDisplayStart + 1;
                }

                // =========================================================
                // 1. TABEL DATA PENGAJUAN EXAM (#examtable)
                // =========================================================
                $('#examtable').DataTable({
                    "processing": true,
                    "autoWidth": false,
                    "ajax": {
                        "url": "{{ route('getExam') }}",
                        "type": "GET",
                        "beforeSend": showLoadingModal,
                        "complete": hideLoadingModal,
                        "error": function (xhr, error, thrown) {
                            hideLoadingModal();
                            console.error("Error memuat #examtable:", thrown);
                        }
                    },
                    "columns": [
                        { "data": null, "orderable": false, "render": renderRowNumber },
                        { "data": "materi.nama_materi", "defaultContent": "-" },
                        {
                            "data": null,
                            "render": function (data) {
                                if (data.tanggal_awal && data.tanggal_akhir) {
                                    return moment(data.tanggal_awal).format('LL') + " s/d " + moment(data.tanggal_akhir).format('LL');
                                }
                                return "-";
                            }
                        },
                        { "data": "perusahaan.nama_perusahaan", "defaultContent": "-" },
                        { "data": "pax", "defaultContent": "-" },
                        { "data": "sales_key", "visible": false, "defaultContent": "" },
                        { "data": "instruktur_key", "visible": false, "defaultContent": "" },
                        {
                            "data": null,
                            "visible": false,
                            "render": function (data) {
                                return data.created_at ? moment(data.created_at).format('LL') : "";
                            }
                        },
                        {
                            "data": null,
                            "orderable": false,
                            "render": function (data) {
                                var actions = "";
                                @can("Create Exam")
                                actions += `<a href="/pengajuanExam/${data.id}" class="btn btn-md click-primary mx-4" data-toggle="tooltip" data-placement="top" title="Pengajuan Exam">Ajukan Exam</a>`;
                                @endcan
                                return actions;
                            }
                        },
                        {
                            "data": null,
                            "visible": false,
                            "render": function (data) {
                                return data.tanggal_akhir ? moment(data.tanggal_akhir).format('YYYY-MM-DD') : "";
                            }
                        }
                    ],
                    "order": [[9, 'desc']],
                    "initComplete": function () {
                        var api = this.api();
                        if (idInstruktur) api.columns(6).search(idInstruktur);
                        if (idSales) api.columns(5).search(idSales);
                        if (idInstruktur || idSales) api.draw();
                    }
                });

                // =========================================================
                // 2. TABEL DATA PENGAJUAN KONDISIONAL (#examconditionaltable)
                // =========================================================
                if ($('#examconditionaltable').length) {
                    $('#examconditionaltable').DataTable({
                        "processing": true,
                        "autoWidth": false,
                        "ajax": {
                            "url": "{{ route('getExamKondisi') }}",
                            "type": "GET",
                            "beforeSend": showLoadingModal,
                            "complete": hideLoadingModal,
                            "error": function (xhr, error, thrown) {
                                hideLoadingModal();
                                console.error("Error memuat #examconditionaltable:", thrown);
                            }
                        },
                        "columns": [
                            { "data": null, "orderable": false, "render": renderRowNumber },
                            { "data": "materi.nama_materi", "defaultContent": "-" },
                            {
                                "data": null,
                                "render": function (data) {
                                    if (data.tanggal_awal && data.tanggal_akhir) {
                                        return moment(data.tanggal_awal).format('LL') + " s/d " + moment(data.tanggal_akhir).format('LL');
                                    }
                                    return "-";
                                }
                            },
                            { "data": "perusahaan.nama_perusahaan", "defaultContent": "-" },
                            { "data": "pax", "defaultContent": "-" },
                            { "data": "sales_key", "visible": false, "defaultContent": "" },
                            { "data": "instruktur_key", "visible": false, "defaultContent": "" },
                            {
                                "data": null,
                                "visible": false,
                                "render": function (data) {
                                    return data.created_at ? moment(data.created_at).format('LL') : "";
                                }
                            },
                            {
                                "data": null,
                                "orderable": false,
                                "render": function (data) {
                                    return `<a href="/pengajuanExam/${data.id}" class="btn btn-md click-primary mx-4" data-toggle="tooltip" data-placement="top" title="Pengajuan Exam">Ajukan Exam</a>`;
                                }
                            },
                            {
                                "data": null,
                                "visible": false,
                                "render": function (data) {
                                    return data.tanggal_akhir ? moment(data.tanggal_akhir).format('YYYY-MM-DD') : "";
                                }
                            }
                        ],
                        "order": [[9, 'desc']],
                        "initComplete": function () {
                            var api = this.api();
                            if (idInstruktur) api.columns(6).search(idInstruktur);
                            if (idSales) api.columns(5).search(idSales);
                            if (idInstruktur || idSales) api.draw();
                        }
                    });
                }

                // =========================================================
                // 3. TABEL DATA HISTORI EXAM (#examhistoritable)
                // =========================================================
                var examHistoriColumns = [
                    { "data": null, "orderable": false, "render": renderRowNumber },
                    {
                        "data": null,
                        "render": function (data) {
                            return data.materi?.nama_materi ?? data.rkm?.materi?.nama_materi ?? '-';
                        }
                    },
                    {
                        "data": null,
                        "render": function (data) {
                            return data.tanggal_pengajuan ? moment(data.tanggal_pengajuan).format('LL') : '-';
                        }
                    },
                    {
                        "data": null,
                        "render": function (data) {
                            return data.perusahaan?.nama_perusahaan ?? data.rkm?.perusahaan?.nama_perusahaan ?? '-';
                        }
                    },
                    { "data": "pax", "defaultContent": "-" },
                    {
                        "data": null,
                        "render": function (data) {
                            var statusBadge = '';
                            if (data.status == '3') {
                                statusBadge = '<span class="badge bg-info">Exam Only</span>';
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
                        "render": function (data) {
                            var app = data.approvalexam;
                            if (!app) {
                                return '<span class="text-muted">-</span>';
                            }
                            if (app.status === 'Selesai') {
                                return '<span class="badge bg-success">Selesai</span>';
                            }
                            if (app.status === 'Proses Checkout') {
                                return '<span class="badge bg-warning text-dark">Proses Checkout</span>';
                            }
                            if (app.technical_support == 1) {
                                return '<span class="badge bg-primary">Dikonfirmasi Technical Support</span>';
                            }
                            if (app.office_manager == 1) {
                                return '<span class="badge bg-info text-dark">Office Manager</span>';
                            }
                            if (app.spv_sales == 1) {
                                return '<span class="badge bg-info text-dark">SPV Sales</span>';
                            }
                            return '<span class="badge bg-warning text-dark">Belum Approval</span>';
                        }
                    },
                    {
                        "data": null,
                        "visible": true,
                        "render": function (data) { return data.rkm?.sales_key ?? '-'; }
                    },
                    {
                        "data": null,
                        "visible": false,
                        "render": function (data) { return data.rkm?.instruktur_key ?? ''; }
                    },
                    {
                        "data": null,
                        "orderable": false,
                        "render": function (data, type, row) {
                            var tglMulai = row.tanggal_mulai ? row.tanggal_mulai : '';
                            var tglSelesai = row.tanggal_selesai ? row.tanggal_selesai : '';

                            var actions = `
                                <div class="dropdown">
                                    <button class="btn dropdown-toggle" type="button" data-bs-toggle="dropdown" data-bs-boundary="viewport">Actions</button>
                                    <div class="dropdown-menu">
                                        <a class="dropdown-item" href="/exam/${row.id}">Detail</a>
                                        <a class="dropdown-item text-primary" href="/daftar-peserta-exam/create/${row.id}"><i class="fas fa-users"></i> Assign Peserta</a>
                                        <div class="dropdown-divider"></div>
                                        <a class="dropdown-item text-primary" href="javascript:void(0)" onclick="openTanggalModal(${row.id}, '${tglMulai}', '${tglSelesai}')"><i class="fas fa-calendar-alt"></i> Set Tanggal Exam</a>
                                        <div class="dropdown-divider"></div>
                            `;

                            if (row.approvalexam && row.approvalexam.spv_sales == 1) {
                                var files = row.file_invoice;
                                if (typeof files === 'string') {
                                    try { files = JSON.parse(files); } catch (e) { files = []; }
                                }

                                if (files && Array.isArray(files) && files.length > 0) {
                                    files.forEach(function (file, index) {
                                        var safeFile = encodeURIComponent(file);
                                        var fileUrl = "{{ asset('uploads/invoices') }}/" + safeFile;
                                        actions += `
                                            <a class="dropdown-item text-primary" href="${fileUrl}" target="_blank"><i class="fas fa-file-invoice"></i> Lihat File ${index + 1}</a>
                                            <form onsubmit="return confirm('Apakah Anda yakin ingin menghapus File ${index + 1} ini?');" action="/exam/${row.id}/delete-invoice/${safeFile}" method="POST" style="display:inline; margin:0; padding:0;">
                                                <input type="hidden" name="_token" value="${csrfToken}">
                                                <input type="hidden" name="_method" value="DELETE">
                                                <button type="submit" class="dropdown-item text-danger"><i class="fas fa-trash"></i> Hapus File ${index + 1}</button>
                                            </form>
                                        `;
                                    });

                                    actions += `
                                        <div class="dropdown-divider"></div>
                                        <a class="dropdown-item text-success" href="javascript:void(0)" onclick="openUploadModal(${row.id})"><i class="fas fa-plus"></i> Tambah Invoice Lagi</a>
                                    `;
                                } else {
                                    actions += `<a class="dropdown-item text-success" href="javascript:void(0)" onclick="openUploadModal(${row.id})"><i class="fas fa-upload"></i> Upload Invoice</a>`;
                                }
                                actions += `<div class="dropdown-divider"></div>`;
                            }

                            if (row.status == '3') {
                                var roomAssigned = row.rkm && row.rkm.ruang && row.rkm.ruang !== 'Exam';
                                if (!roomAssigned) {
                                    @can("Edit Exam")
                                    actions += `<a class="dropdown-item" href="/exam/assign-room/${row.id}"><i class="fas fa-home"></i> Assign Ruangan</a>`;
                                    @endcan
                                } else {
                                    actions += `<a class="dropdown-item text-success" href="#"><i class="fas fa-check"></i> Ruangan: ${row.rkm.ruang || '-'}</a>`;
                                }
                                actions += `<div class="dropdown-divider"></div>`;
                            }

                            @can("Edit Exam")
                            actions += `<a class="dropdown-item" href="/exam/${row.id}/edit">Edit</a>`;
                            @endcan

                            var examSalesKey = row.sales_key || (row.rkm?.sales_key) || '';
                            var canDelete = (userJabatan.trim() === 'SPV Sales') || (examSalesKey === userIdSales);

                            if (canDelete) {
                                actions += `
                                    <form onsubmit="return confirm('Yakin ingin menghapus Exam ini? Tindakan tidak dapat dibatalkan.');" action="/exam/${row.id}" method="POST">
                                        <input type="hidden" name="_token" value="${csrfToken}">
                                        <input type="hidden" name="_method" value="DELETE">
                                        <button type="submit" class="dropdown-item text-danger"><i class="fas fa-trash"></i> Hapus</button>
                                    </form>
                                `;
                            }

                            actions += `</div></div>`;
                            return actions;
                        }
                    }
                ];

                @if (auth()->user()->jabatan == 'Finance & Accounting')
                examHistoriColumns.push({
                    "data": null,
                    "orderable": false,
                    "render": function (data, type, row) {
                        var sudahDiajukan = row.has_pengajuan_barang;
                        var officeManagerApproved = row.approvalexam && row.approvalexam.office_manager == 1;

                        if (sudahDiajukan) {
                            return '<span class="badge bg-secondary">Sudah Diajukan</span>';
                        }

                        if (!officeManagerApproved) {
                            return '<span class="text-muted small">Menunggu Approval Office Manager</span>';
                        }

                        return `
                            <form action="/exam/${row.id}/add-pengajuan-barang" method="GET" style="display:inline;" onsubmit="return confirm('Buat Pengajuan Barang untuk Exam ini?');">
                                <button type="submit" class="btn btn-sm btn-success">
                                    <i class="fas fa-plus"></i> Add Pengajuan Barang
                                </button>
                            </form>
                        `;
                    }
                });
                @endif

                $('#examhistoritable').DataTable({
                    "processing": true,
                    "autoWidth": false,
                    "ajax": {
                        "url": "{{ route('getHistoriExam') }}",
                        "type": "GET",
                        "beforeSend": showLoadingModal,
                        "complete": hideLoadingModal,
                        "error": function (xhr, error, thrown) {
                            hideLoadingModal();
                            console.error("Error memuat #examhistoritable:", thrown);
                        }
                    },
                    "columns": examHistoriColumns,
                    "order": [[0, 'asc']],
                    "initComplete": function () {
                        var api = this.api();
                        if (idInstruktur) api.columns(8).search(idInstruktur);
                        if (idSales) api.columns(7).search(idSales);
                        if (idInstruktur || idSales) api.draw();
                    }
                });

                // =========================================================
                // 4. TABEL DATA EXAM SERTIFA (#poExamTableIndex)
                // =========================================================
                $('#poExamTableIndex').DataTable({
                    "processing": true,
                    "autoWidth": false,
                    "ajax": {
                        "url": "{{ route('getPoExamSertifa') }}",
                        "type": "GET",
                        "beforeSend": showLoadingModal,
                        "complete": hideLoadingModal,
                        "error": function (xhr, error, thrown) {
                            hideLoadingModal();
                            console.error("Error memuat #poExamTableIndex:", thrown);
                        }
                    },
                    "columns": [
                        { "data": null, "orderable": false, "render": renderRowNumber },
                        {
                            "data": null,
                            "render": function (data) {
                                return data.materi?.nama_materi ?? '-';
                            }
                        },
                        {
                            "data": null,
                            "render": function (data) {
                                return data.tanggal_exam ? moment(data.tanggal_exam).format('DD MMM YYYY') : '-';
                            }
                        },
                        {
                            "data": null,
                            "render": function (data) {
                                return data.perusahaan?.nama_perusahaan ?? '-';
                            }
                        },
                        {
                            "data": null,
                            "render": function (data) {
                                return data.pax ?? '-';
                            }
                        },
                        {
                            "data": null,
                            "render": function (data) {
                                if (data.harga) {
                                    return 'Rp ' + parseInt(data.harga).toLocaleString('id-ID');
                                }
                                return 'Rp 0';
                            }
                        },
                        {
                            "data": null,
                            "orderable": false,
                            "render": function (data) {
                                return `<a href="/pengajuanExam/${data.id_rkm}" class="btn btn-md click-primary mx-4" data-toggle="tooltip" data-placement="top" title="Pengajuan Exam">Ajukan Exam</a>`;
                            }
                        }
                    ],
                    "language": {
                        "url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json"
                    }
                });
            });

            function openUploadModal(id) {
                var form = document.getElementById('formUploadInvoice');
                form.action = '/exam/' + id + '/upload-invoice';
                var uploadModal = new bootstrap.Modal(document.getElementById('uploadInvoiceModal'));
                uploadModal.show();
            }

            function openTanggalModal(id, tanggalMulai, tanggalSelesai) {
                var form = document.getElementById('formUpdateTanggal');
                form.action = '/exam/' + id + '/update-tanggal';

                document.getElementById('tanggal_mulai').value = tanggalMulai;
                document.getElementById('tanggal_selesai').value = tanggalSelesai;

                var tanggalModal = new bootstrap.Modal(document.getElementById('updateTanggalModal'));
                tanggalModal.show();
            }
        </script>
    @endpush
@endsection
