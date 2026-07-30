@extends('layouts_office.app')

@section('office_contents')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <style>
        html, body {
            height: auto !important;
            overflow-y: auto !important;
            overflow-x: hidden !important;
        }
        
        .card.shadow-sm.border-0.glass-force {
            max-height: none !important;
            overflow: visible !important;
        }

        .dataTables_wrapper .dataTables_length, 
        .dataTables_wrapper .dataTables_filter, 
        .dataTables_wrapper .dataTables_info, 
        .dataTables_wrapper .dataTables_processing, 
        .dataTables_wrapper .dataTables_paginate {
            color: #495057 !important;
        }
        
        .table>:not(caption)>*>* {
            border-bottom-width: 1px !important;
        }

        .table>:not(caption)>*>* {
            border-bottom-width: 0;
        }

        .table td,
        .table th {
            padding: 12px 10px;
            vertical-align: middle;
        }

        .table-responsive {
            overflow-x: auto;
        }

        .card {
            overflow: visible;
        }

        .dropdown-menu {
            z-index: 1055 !important;
            min-width: 180px;
        }

        .modal {
            z-index: 1070 !important;
        }

        .modal-backdrop {
            z-index: 1060 !important;
        }

        .modal-body {
            max-height: 70vh;
            overflow-y: auto;
        }

        .form-select,
        .form-control,
        .form-control:focus,
        .input-group-text {
            pointer-events: auto !important;
        }

        .biaya-item-row,
        .edit-item-row {
            position: relative;
            min-height: 120px;
        }

        .btn-remove-biaya,
        .btn-remove-item {
            position: absolute;
            top: 8px;
            right: 8px;
            z-index: 10;
        }

        .is-invalid {
            border-color: #dc3545 !important;
            background-image: none !important;
        }

        @media (max-width: 767.98px) {
            .modal-dialog {
                margin: 0.5rem;
            }

            .modal-lg,
            .modal-xl {
                max-width: 95% !important;
            }

            .table th,
            .table td {
                font-size: 0.875rem;
            }

            .btn-group-sm .btn {
                padding: 0.25rem 0.5rem;
                font-size: 0.75rem;
            }

            .d-grid.d-md-block {
                grid-template-columns: 1fr;
            }

            .card-header {
                flex-direction: column !important;
                align-items: flex-start !important;
            }

            #dataCountBadge {
                margin-top: 0.5rem;
            }
        }

        @media (max-width: 575.98px) {
            .row.g-2>div {
                margin-bottom: 0.75rem;
            }

            .biaya-item-row .row.g-2,
            .edit-item-row .row {
                flex-direction: column;
            }

            .biaya-item-row .col-md-4,
            .edit-item-row .col-md-3,
            .edit-item-row .col-md-4 {
                width: 100%;
            }
        }

        .select2-container--bootstrap-5 .select2-selection {
            box-shadow: none !important;
        }

        .select2-container--bootstrap-5 .select2-selection--single {
            height: calc(1.5em + 0.75rem + 2px) !important;
            padding: 0.375rem 0.75rem !important;
        }

        .select2-container--bootstrap-5 .select2-selection__rendered {
            line-height: 1.5 !important;
        }

        .select2-container--bootstrap-5 .select2-selection__arrow {
            height: 100% !important;
        }

        .btn-check:checked+.card {
            border-color: #0d6efd;
            background: #eaf3ff;
            box-shadow: 0 .5rem 1rem rgba(13, 110, 253, .15) !important;
            transition: .2s;
        }

        .card {
            transition: .2s;
        }

        .card:hover {
            transform: translateY(-2px);
        }
    </style>

    <div class="d-grid gap-2 d-md-flex justify-content-md-start mb-4">
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#ModalTambah">
            <i class="fas fa-plus me-1"></i> Ajukan Biaya
        </button>
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalExportFilter">
            <i class="fas fa-file-export me-1"></i> Export
        </button>

        {{-- Modal Filter Export --}}
        <div class="modal fade" id="modalExportFilter" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Filter Export</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form id="formExportFilter">
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Tanggal Mulai</label>
                                <input type="date" name="start_date" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Tanggal Akhir</label>
                                <input type="date" name="end_date" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Tipe Biaya</label>
                                <select name="tipe" class="form-select">
                                    <option value="">Semua</option>
                                    <option value="BBM">BBM</option>
                                    <option value="TOL">TOL</option>
                                    <option value="Parkir">Parkir</option>
                                    <option value="Lainnya">Lainnya</option>
                                    <option value="Budget Lebih">Budget Lebih</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    <option value="">Semua</option>
                                    <option value="Menunggu">Menunggu</option>
                                    <option value="Diajukan dan Sedang Ditinjau">Diajukan dan Sedang Ditinjau</option>
                                    <option value="Disetujui">Disetujui</option>
                                    <option value="Ditolak">Ditolak</option>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="button" class="btn btn-primary" onclick="exportData('excel')">
                                <i class="fas fa-file-excel me-1"></i> Export Excel
                            </button>
                            <button type="button" class="btn btn-danger" onclick="exportData('pdf')">
                                <i class="fas fa-file-pdf me-1"></i> Export PDF
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid px-0 px-md-3">
        <div class="card shadow-sm border-0 glass-force">
            <div class="card-header d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 py-3">
                <h5 class="mb-0 fw-semibold">Biaya Transportasi Driver</h5>
            </div>
            <div class="card-body p-0 p-md-3">
                <div class="table-responsive">
                    <table id="dataTableBiaya" class="table table-bordered table-hover align-middle mb-0" style="width:100%">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center">No</th>
                                <th>Sumber / Koordinasi</th>
                                <th>Kendaraan</th>
                                <th>Driver</th>
                                <th>Tipe</th>
                                <th>Harga</th>
                                <th>Struk</th>
                                <th>Status</th>
                                <th>Tanggal</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                   </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="detailModal" tabindex="-1" aria-labelledby="detailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="detailModalLabel">Detail Biaya Transportasi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="detailContent"></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalBukti" tabindex="-1" aria-labelledby="modalBuktiLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalBuktiLabel">Bukti Pembayaran</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="buktiContent"></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="ModalEdit" tabindex="-1" aria-labelledby="ModalEditLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <form id="formEdit" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="edit_pickup" name="id_pickup_driver">

                    <div class="modal-header">
                        <h5 class="modal-title" id="ModalEditLabel">Edit Biaya Transportasi</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Driver & Lokasi</label>
                            <input type="text" id="edit_pickup_label" class="form-control" disabled>
                        </div>

                        <hr>

                        <div id="editItemsContainer"></div>

                        <button type="button" id="btnAddItem" class="btn btn-success mt-3">
                            <i class="fas fa-plus me-1"></i> Tambah Item Biaya
                        </button>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batalkan</button>
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="ModalTambah" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false"
        aria-labelledby="ModalTambahLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <form id="formCreate" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="ModalTambahLabel">Ajukan Biaya Transportasi</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-4">
                            <label class="form-label fw-bold mb-3">
                                Sumber Data <span class="text-danger">*</span>
                            </label>

                            <div class="row g-3">

                                <div class="col-md-6">
                                    <input type="radio" class="btn-check" name="sumber_data" id="sumber_driver"
                                        value="driver" checked>

                                    <label class="card h-100 shadow-sm border-2 text-start p-3 w-100" for="sumber_driver"
                                        style="cursor:pointer;">

                                        <div class="d-flex align-items-center">
                                            <div class="me-3">
                                                <i class="fas fa-truck fa-2x text-primary"></i>
                                            </div>

                                            <div>
                                                <h6 class="mb-1">Koordinasi Driver</h6>
                                                <small class="text-muted">
                                                    Mengambil data dari koordinasi pickup driver.
                                                </small>
                                            </div>
                                        </div>

                                    </label>
                                </div>

                                <div class="col-md-6">
                                    <input type="radio" class="btn-check" name="sumber_data" id="sumber_spj"
                                        value="spj">

                                    <label class="card h-100 shadow-sm border-2 text-start p-3 w-100" for="sumber_spj"
                                        style="cursor:pointer;">

                                        <div class="d-flex align-items-center">
                                            <div class="me-3">
                                                <i class="fas fa-file-invoice fa-2x text-success"></i>
                                            </div>

                                            <div>
                                                <h6 class="mb-1">SPJ</h6>
                                                <small class="text-muted">
                                                    Mengambil data berdasarkan SPJ.
                                                </small>
                                            </div>
                                        </div>

                                    </label>
                                </div>

                            </div>
                        </div>

                        <div id="driverSection">
                            <div class="mb-3 p-3 bg-light rounded border">
                                <label class="form-label fw-bold small mb-2">Filter Pencarian Pickup</label>
                                <div class="row g-2">
                                    <div class="col-md-4">
                                        <select id="filter_driver_pickup" class="form-select form-select-sm">
                                            <option value="">Semua Driver</option>
                                            @foreach($drivers ?? [] as $drv)
                                                <option value="{{ $drv->id }}">{{ $drv->nama_lengkap }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <input type="date" id="filter_start_pickup" class="form-control form-control-sm" placeholder="Tanggal Mulai">
                                    </div>
                                    <div class="col-md-4">
                                        <input type="date" id="filter_end_pickup" class="form-control form-control-sm" placeholder="Tanggal Akhir">
                                    </div>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold">
                                    Pilih Koordinasi Pickup <span class="text-danger">*</span>
                                </label>
                                <select name="id_pickup_driver" class="form-select select2-ajax-picker" id="pickupSelect" data-url="{{ route('office.biayaTransportasi.searchPickup') }}">
                                    <option value="">-- Pilih Pickup --</option>
                                    <option value="999999999">Diluar Koordinasi Driver</option>
                                </select>
                                <small class="text-muted">
                                    <i class="fas fa-info-circle"></i> Gunakan filter di atas atau ketik nama/lokasi untuk mencari.
                                </small>
                            </div>
                        </div>

                        <div id="spjSection" style="display:none;">
                            <div class="mb-3 p-3 bg-light rounded border">
                                <label class="form-label fw-bold small mb-2">Filter Pencarian SPJ</label>
                                <div class="row g-2">
                                    <div class="col-md-4">
                                        <select id="filter_driver_spj" class="form-select form-select-sm">
                                            <option value="">Semua Driver</option>
                                            @foreach($drivers ?? [] as $drv)
                                                <option value="{{ $drv->id }}">{{ $drv->nama_lengkap }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <input type="date" id="filter_start_spj" class="form-control form-control-sm" placeholder="Tanggal Mulai">
                                    </div>
                                    <div class="col-md-4">
                                        <input type="date" id="filter_end_spj" class="form-control form-control-sm" placeholder="Tanggal Akhir">
                                    </div>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold">
                                    Pilih SPJ <span class="text-danger">*</span>
                                </label>
                                <select name="id_spj" class="form-select select2-ajax-picker" id="spjSelect" data-url="{{ route('office.biayaTransportasi.searchSpj') }}">
                                    <option value="">-- Pilih SPJ --</option>
                                </select>
                                <small class="text-muted">
                                    <i class="fas fa-info-circle"></i> Gunakan filter di atas atau ketik alasan/tujuan untuk mencari.
                                </small>
                            </div>
                        </div>

                        <div class="mb-4 form-check p-3 bg-light rounded border">
                            <input type="checkbox" class="form-check-input" id="buat_pengajuan" name="buat_pengajuan"
                                value="1">
                            <label class="form-check-label fw-bold" for="buat_pengajuan">Buat Pengajuan Barang
                                (Reimbursement)</label>
                            <small class="text-muted d-block mt-1">
                                <i class="fas fa-info-circle"></i> Default tidak dicentang. Jika tidak dicentang, data
                                hanya akan disimpan sebagai riwayat biaya transportasi tanpa membuat alur persetujuan
                                pengajuan reimbursement.
                            </small>
                        </div>

                        <hr class="my-4">
                        <label class="form-label fw-bold d-block mb-3">Rincian Biaya</label>
                        <div id="biayaItemsContainer">
                            <div class="biaya-item-row border rounded p-3 mb-3 position-relative">
                                <button type="button" class="btn btn-danger btn-sm btn-remove-biaya"><i
                                        class="fas fa-trash"></i></button>
                                <div class="row g-3">
                                    <div class="col-12 col-md-4">
                                        <label class="form-label small">Tipe</label>
                                        <select name="biaya[${idx}][tipe]" class="form-select" required>
                                            <option value="BBM">BBM</option>
                                            <option value="TOL">TOL</option>
                                            <option value="Parkir">Parkir</option>
                                            <option value="Lainnya">Lainnya</option>
                                            <option value="Budget Lebih">Budget Lebih</option>
                                        </select>
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <label class="form-label small">Harga</label>
                                        <div class="input-group">
                                            <span class="input-group-text">Rp</span>
                                            <input type="text" class="form-control input-harga-visual"
                                                data-name="biaya[${idx}][harga]" placeholder="0" required>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <label class="form-label small">Bukti</label>
                                        <input type="file" name="biaya[${idx}][bukti]" class="form-control" required>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <input type="text" name="biaya[${idx}][keterangan]" class="form-control"
                                        placeholder="Keterangan (opsional)">
                                </div>
                            </div>
                        </div>
                        <button type="button" id="btnAddBiaya" class="btn btn-success mt-3">
                            <i class="fas fa-plus me-1"></i> Tambah Item Biaya
                        </button>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan Data</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalInvoice" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="formInvoice">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Upload Invoice</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="invoice_pengajuan_id">
                        <input type="file" name="invoice" class="form-control" required>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-primary">Upload</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.30.1/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.30.1/locale/id.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        const rupiahFormat = new Intl.NumberFormat('id-ID');
        const currencyFormat = new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0
        });
        const dateFormat = d => moment(d).format('DD MMM YYYY');
        const OUTSIDE_OPS_ID = '999999999';

        let biayaTable;

        window.groupedData = window.groupedData || {};

        function isOutsideOps(pickupId) {
            return String(pickupId) === OUTSIDE_OPS_ID;
        }


        $(document).on('change', 'input[name="sumber_data"]', function() {
            if ($(this).val() === 'driver') {
                $('#driverSection').show();
                $('#spjSection').hide();
                $('#pickupSelect').prop('required', true);
                $('#spjSelect').prop('required', false);
            } else {
                $('#driverSection').hide();
                $('#spjSection').show();
                $('#pickupSelect').prop('required', false);
                $('#spjSelect').prop('required', true);
            }
        });

        function initDataTables() {
            if ($.fn.DataTable.isDataTable('#dataTableBiaya')) {
                $('#dataTableBiaya').DataTable().clear().destroy();
            }

            biayaTable = $('#dataTableBiaya').DataTable({
                ajax: {
                    url: "{{ route('office.biayaTransportasi.get') }}",
                    dataSrc: 'data'
                },
                columns: [
                    { 
                        data: null, 
                        render: (data, type, row, meta) => meta.row + 1 + meta.settings._iDisplayStart 
                    },
                    {
                        data: null,
                        render: (data, type, row) => {
                            if (row.id_pickup_driver == OUTSIDE_OPS_ID) return '<span class="badge bg-secondary">Diluar Koordinasi</span>';
                            if (row.spj) return `<span class="badge bg-success">SPJ: ${row.spj.tujuan}</span>`;
                            const lokasi = row.pickupDriver?.detail_pickup_driver?.[0]?.lokasi || row.pickupDriver?.detailPickupDriver?.[0]?.lokasi || '-';
                            return `Pickup: ${lokasi}`;
                        }
                    },
                    { 
                        data: null,
                        render: (data, type, row) => {
                            if (row.spj) return row.spj.karyawan?.kendaraan ?? 'N/A';
                            return row.pickupDriver?.karyawan?.kendaraan ?? '-';
                        }
                    },
                    {
                        data: null,
                        render: (data, type, row) => {
                            if (row.spj) return row.spj.karyawan?.nama_lengkap ?? '-';
                            return row.pickupDriver?.karyawan?.nama_lengkap ?? row.pickupDriver?.nama_driver ?? '-';
                        }
                    },
                    { data: 'tipe' },
                    { 
                        data: 'harga',
                        render: (data) => currencyFormat.format(Number(data) || 0)
                    },
                    {
                        data: 'bukti',
                        render: (data, type, row) => {
                            if (data) return `<button class="btn btn-sm btn-secondary lihat-bukti" data-src="{{ asset('storage') }}/${data}"><i class="fas fa-image"></i> Lihat</button>`;
                            return '<span class="text-muted">-</span>';
                        }
                    },
                    {
                        data: 'pengajuan_barang.tracking.tracking',
                        render: (data) => {
                            if (!data) return '<span class="badge bg-warning text-dark">Menunggu</span>';
                            if (data.toLowerCase().includes('selesai')) return '<span class="badge bg-success">Selesai</span>';
                            return `<span class="badge bg-info text-dark">${data}</span>`;
                        }
                    },
                    { 
                        data: 'created_at',
                        render: (data) => dateFormat(data)
                    },
                    {
                        data: null,
                        orderable: false,
                        render: (data, type, row) => {
                            const sourceId = row.id_pickup_driver || row.id_pengajuan_spj;
                            const isSelesai = (row.pengajuan_barang?.tracking?.tracking || '').toLowerCase().includes('selesai');
                            
                            const buttons = [
                                `<button class="btn btn-sm btn-info btn-detail" data-source="${sourceId}" title="Detail"><i class="fas fa-info-circle"></i></button>`,
                                `<button class="btn btn-sm btn-primary btn-edit" data-source="${sourceId}" title="Edit"><i class="fas fa-edit"></i></button>`,
                                `<button class="btn btn-sm btn-danger btn-delete" data-source="${sourceId}" title="Hapus"><i class="fas fa-trash"></i></button>`
                            ];

                            if (isSelesai && row.id_pengajuan_barang) {
                                buttons.push(`<button class="btn btn-sm btn-warning btn-upload-invoice" data-id="${row.id_pengajuan_barang}" title="Upload Invoice"><i class="fas fa-file-upload"></i></button>`);
                            }

                            return `<div class="btn-group" role="group">${buttons.join('')}</div>`;
                        }
                    }
                ],
                language: {
                    search: "Cari:",
                    lengthMenu: "Tampilkan _MENU_ data",
                    info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                    paginate: {
                        first: "Awal",
                        last: "Akhir",
                        next: ">",
                        previous: "<"
                    },
                    emptyTable: "Tidak ada data biaya transportasi"
                },
                order: [[8, 'desc']],
                responsive: true
            });
        }

        function getKoordinasiLabel(item) {
            if (isOutsideOps(item.id_pickup_driver)) {
                return 'Diluar Koordinasi Driver';
            }
            
            if (item.spj) {
                const namaDriver = item.spj.karyawan?.nama_lengkap ?? 'Driver';
                return `SPJ: ${item.spj.tujuan} (${namaDriver})`;
            }

            let namaDriver = '-';
            let lokasi = '-';
            if (item.karyawan?.nama_lengkap) namaDriver = item.karyawan.nama_lengkap;
            else if (item.driver_name) namaDriver = item.driver_name;
            else if (item.pickupDriver?.karyawan?.nama_lengkap) namaDriver = item.pickupDriver.karyawan.nama_lengkap;
            
            if (item.detailPickupDriver?.[0]?.lokasi) lokasi = item.detailPickupDriver[0].lokasi;
            else if (item.detail_pickup_driver?.[0]?.lokasi) lokasi = item.detail_pickup_driver[0].lokasi;
            else if (item.lokasi) lokasi = item.lokasi;
            else if (item.pickupDriver?.detailPickupDriver?.[0]?.lokasi) lokasi = item.pickupDriver.detailPickupDriver[0].lokasi;

            return `${namaDriver} | ${lokasi}`;
        }

        function exportData(type) {
            const formData = new FormData(document.getElementById('formExportFilter'));
            const params = new URLSearchParams(formData);

            let url = '';
            if (type === 'excel') {
                url = "{{ route('office.biayaTransportasi.exportExcel') }}?" + params.toString();
            } else {
                url = "{{ route('office.biayaTransportasi.exportPdf') }}?" + params.toString();
            }

            window.open(url, '_blank');

            const modal = bootstrap.Modal.getInstance(document.getElementById('modalExportFilter'));
            if (modal) modal.hide();
        }

        function formatRupiah(el) {
            let val = el.value.replace(/[^0-9]/g, '');
            $(el).data('clean-value', val);
            el.value = val ? rupiahFormat.format(val) : '';
        }

        function addCreateItem() {
            const idx = $('.biaya-item-row').length;
            const html = `
                <div class="biaya-item-row border rounded p-3 mb-3 position-relative">
                    <button type="button" class="btn btn-danger btn-sm btn-remove-biaya"><i class="fas fa-trash"></i></button>
                    <div class="row g-3">
                        <div class="col-12 col-md-4">
                            <label class="form-label small">Tipe</label>
                            <select name="biaya[${idx}][tipe]" class="form-select" required>
                                <option value="BBM">BBM</option>
                                <option value="TOL">TOL</option>
                                <option value="Parkir">Parkir</option>
                                <option value="Lainnya">Lainnya</option>
                                <option value="Budget Lebih">Budget Lebih</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label small">Harga</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="text" class="form-control input-harga-visual" data-name="biaya[${idx}][harga]" placeholder="0" required>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label small">Bukti (Max 2MB)</label>
                            <input type="file" name="biaya[${idx}][bukti]" class="form-control" required>
                        </div>
                    </div>
                    <div class="mt-3">
                        <input type="text" name="biaya[${idx}][keterangan]" class="form-control" placeholder="Keterangan (opsional)">
                    </div>
                </div>`;
            $('#biayaItemsContainer').append(html);
            $('.input-harga-visual').last().on('input', function() {
                formatRupiah(this);
            });
        }

        function addEditItem() {
            const idx = $('.edit-item-row').length;
            const html = `
                <div class="edit-item-row border rounded p-3 mb-3 position-relative" data-idx="${idx}">
                    <button type="button" class="btn btn-danger btn-sm btn-remove-item">
                        <i class="fas fa-trash"></i> Hapus
                    </button>
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Tipe Biaya</label>
                            <select class="form-select" name="items[${idx}][tipe]" required>
                                <option value="BBM">BBM</option>
                                <option value="TOL">TOL</option>
                                <option value="Parkir">Parkir</option>
                                <option value="Lainnya">Lainnya</option>
                                <option value="Budget Lebih">Budget Lebih</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Harga</label>
                            <input type="number" class="form-control" name="items[${idx}][harga]" min="500" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Keterangan</label>
                            <input type="text" class="form-control" name="items[${idx}][keterangan]">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Bukti</label>
                            <input type="file" class="form-control" name="items[${idx}][bukti]">
                        </div>
                    </div>
                </div>`;
            $('#editItemsContainer').append(html);
        }

        function reindexCreateItems() {
            $('.biaya-item-row').each((i, el) => {
                $(el).find('[name^="biaya["]').each(function() {
                    let name = $(this).attr('name').replace(/biaya\[\d+\]/, `biaya[${i}]`);
                    $(this).attr('name', name);
                    if ($(this).hasClass('input-harga-visual')) $(this).attr('data-name', name);
                });
            });
        }

        function reindexEditItems() {
            $('.edit-item-row').each((i, el) => {
                $(el).attr('data-idx', i);
                $(el).find('[name^="items["]').each(function() {
                    let name = $(this).attr('name').replace(/items\[\d+\]/, `items[${i}]`);
                    $(this).attr('name', name);
                });
            });
        }

        function initSelect2Ajax(elementId, filterDriverId, filterStartId, filterEndId) {
            const $el = $(elementId);
            const url = $el.data('url');

            if ($el.hasClass('select2-hidden-accessible')) {
                $el.select2('destroy');
            }

            $el.select2({
                theme: 'bootstrap-5',
                width: '100%',
                placeholder: 'Klik untuk memuat data berdasarkan filter...',
                allowClear: true,
                dropdownParent: $('#ModalTambah'),
                
                ajax: {
                    url: url,
                    dataType: 'json',
                    delay: 300,
                    data: function (params) {
                        return {
                            q: params.term || '', 
                            driver_id: $(filterDriverId).val(),
                            start_date: $(filterStartId).val(),
                            end_date: $(filterEndId).val(),
                            page: params.page || 1
                        };
                    },
                    processResults: function (data, params) {
                        return {
                            results: data.results
                        };
                    },
                    cache: false
                },
                language: {
                    noResults: function() { return "Tidak ada data yang ditemukan"; },
                    searching: function() { return "Mencari..."; }
                }
            });

            $(filterDriverId + ', ' + filterStartId + ', ' + filterEndId).on('change', function() {
                $el.val(null).trigger('change');
                
                setTimeout(function() {
                    $el.select2('open');
                }, 100);
            });
        }

        $(document).ready(function() {
            initDataTables();

            initSelect2Ajax('#pickupSelect', '#filter_driver_pickup', '#filter_start_pickup', '#filter_end_pickup');
            initSelect2Ajax('#spjSelect', '#filter_driver_spj', '#filter_start_spj', '#filter_end_spj');

            $('#btnAddBiaya').click(addCreateItem);
            $('#btnAddItem').click(addEditItem);

            $(document).on('input', '.input-harga-visual', function() {
                formatRupiah(this);
            });

            $(document).on('click', '.btn-remove-biaya', function() {
                if ($('.biaya-item-row').length <= 1) return Swal.fire('Minimal 1 item', '', 'warning');
                $(this).closest('.biaya-item-row').remove();
                reindexCreateItems();
            });

            $(document).on('click', '.btn-remove-item', function() {
                if ($('.edit-item-row').length <= 1) return Swal.fire('Minimal 1 item', '', 'warning');
                $(this).closest('.edit-item-row').remove();
                reindexEditItems();
            });

            $('#formCreate').submit(function(e) {
                e.preventDefault();
                let valid = true;
                $('.input-harga-visual').each(function() {
                    let val = parseInt($(this).data('clean-value') || 0);
                    if (val < 500) {
                        valid = false;
                        $(this).addClass('is-invalid');
                    } else {
                        $(this).removeClass('is-invalid');
                    }
                });
                if (!valid) return Swal.fire('Error', 'Minimal harga per item Rp 500', 'error');

                const formData = new FormData(this);
                $('.input-harga-visual').each(function() {
                    let val = $(this).data('clean-value') || '0';
                    formData.append($(this).data('name'), val);
                });

                $.ajax({
                    url: "{{ route('office.biayaTransportasi.create') }}",
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: () => {
                        const modalEl = document.getElementById('ModalTambah');
                        const modal = bootstrap.Modal.getInstance(modalEl);
                        if (modal) modal.hide();
                        $('#formCreate')[0].reset();
                        $('#biayaItemsContainer').empty();
                        $('#pickupSelect').val(null).trigger('change'); // Reset select2
                        if (biayaTable) biayaTable.ajax.reload(null, false); 
                        Swal.fire('Sukses', 'Pengajuan berhasil dikirim', 'success');
                    },
                    error: xhr => {
                        let msg = xhr.responseJSON?.message || 'Terjadi kesalahan server';
                        if (xhr.responseJSON?.errors) msg = Object.values(xhr.responseJSON
                            .errors).flat().join('<br>');
                        Swal.fire('Gagal', msg, 'error');
                    }
                });
            });

            $('#formEdit').on('submit', function(e) {
                e.preventDefault();
                const pickupId = $('#edit_pickup').val();
                const formData = new FormData(this);

                $.ajax({
                    url: `/office/biaya-transportasi/update/${pickupId}`,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(res) {
                        const modalEl = document.getElementById('ModalEdit');
                        const modal = bootstrap.Modal.getInstance(modalEl);
                        if (modal) modal.hide();
                        if (biayaTable) biayaTable.ajax.reload(null, false);
                        Swal.fire('Sukses', 'Data berhasil diperbarui', 'success');
                    },
                    error: function(err) {
                        console.log(err.responseJSON);
                        Swal.fire('Gagal', err.responseJSON?.message ||
                            'Gagal memperbarui data', 'error');
                    }
                });
            });

            $(document).on('click', '.btn-upload-invoice', function() {
                const id = $(this).data('id');
                $('#invoice_pengajuan_id').val(id);
                new bootstrap.Modal(document.getElementById('modalInvoice')).show();
            });

            $('#formInvoice').submit(function(e) {
                e.preventDefault();
                const id = $('#invoice_pengajuan_id').val();
                const formData = new FormData(this);

                $.ajax({
                    url: `/office/biaya-transportasi/upload-invoice/${id}`,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function() {
                        Swal.fire('Sukses', 'Invoice berhasil diupload', 'success');
                        $('#modalInvoice').modal('hide');
                        if (biayaTable) biayaTable.ajax.reload(null, false);
                    },
                    error: function(xhr) {
                        Swal.fire('Gagal', xhr.responseJSON?.message || 'Error', 'error');
                    }
                });
            });

            $(document).on('click', '.lihat-bukti', function() {
                const src = $(this).data('src');
                $('#buktiContent').html(`<div class="text-center"><img src="${src}" class="img-fluid rounded shadow-sm" style="max-height:600px;"></div>`);
                new bootstrap.Modal(document.getElementById('modalBukti')).show();
            });

            $(document).on('click', '.btn-detail', function() {
                const sourceId = $(this).data('source');
                // Ambil semua data dari table untuk difilter berdasarkan sourceId
                const allData = biayaTable.rows().data().toArray();
                const items = allData.filter(item => (item.id_pickup_driver || item.id_pengajuan_spj) == sourceId);
                
                let rows = '', total = 0, tracking = 'Menunggu';
                items.forEach(i => {
                    total += Number(i.harga) || 0;
                    const bukti = i.bukti ? `<a href="{{ asset('storage') }}/${i.bukti}" target="_blank" class="btn btn-sm btn-info"><i class="fas fa-eye"></i> Lihat</a>` : '-';
                    rows += `<tr>
                        <td>${i.pengajuan_barang?.tipe ?? '-'}</td>
                        <td>${i.tipe}</td>
                        <td class="text-end">${currencyFormat.format(Number(i.harga) || 0)}</td>
                        <td>${i.keterangan ?? '-'}</td>
                        <td class="text-center">${bukti}</td>
                        <td>${moment(i.created_at).format('DD MMM YYYY HH:mm')}</td>
                    </tr>`;
                    if (i.pengajuan_barang?.tracking?.tracking) tracking = i.pengajuan_barang.tracking.tracking;
                });

                $('#detailContent').html(`
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered">
                            <thead class="table-light"><tr><th>Jenis</th><th>Tipe</th><th>Harga</th><th>Keterangan</th><th>Bukti</th><th>Tanggal</th></tr></thead>
                            <tbody>${rows}</tbody>
                            <tfoot><tr><th colspan="4" class="text-end">Total</th><th colspan="2" class="text-end">${currencyFormat.format(total)}</th></tr></tfoot>
                        </table>
                    </div>
                    <p class="mt-3 mb-1 fw-bold">Status Tracking:</p>
                    <div class="alert alert-secondary mb-0">${tracking}</div>
                `);
                new bootstrap.Modal(document.getElementById('detailModal')).show();
            });

            $(document).on('click', '.btn-edit', function() {
                const sourceId = $(this).data('source');
                const allData = biayaTable.rows().data().toArray();
                const items = allData.filter(item => (item.id_pickup_driver || item.id_pengajuan_spj) == sourceId);
                const first = items[0] || {};

                $('#edit_pickup').val(sourceId); // Kirim sourceId (bisa pickup atau spj) ke form

                if (first.id_pickup_driver == OUTSIDE_OPS_ID) {
                    $('#edit_pickup_label').val('Diluar Koordinasi Driver');
                } else if (first.spj) {
                    $('#edit_pickup_label').val(`SPJ: ${first.spj.tujuan} (${first.spj.karyawan?.nama_lengkap ?? 'Driver'})`);
                } else {
                    const nama = first.pickupDriver?.karyawan?.nama_lengkap || '-';
                    const lokasi = first.pickupDriver?.detail_pickup_driver?.[0]?.lokasi || first.pickupDriver?.detailPickupDriver?.[0]?.lokasi || '-';
                    $('#edit_pickup_label').val(`${nama} | ${lokasi}`);
                }

                $('#editItemsContainer').empty();
                items.forEach((item, idx) => {
                    const row = `
                        <div class="edit-item-row border rounded p-3 mb-3 position-relative" data-idx="${idx}">
                            <input type="hidden" name="items[${idx}][id]" value="${item.id}">
                            <button type="button" class="btn btn-danger btn-sm btn-remove-item"><i class="fas fa-trash"></i> Hapus</button>
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold small">Tipe</label>
                                    <select class="form-select form-select-sm" name="items[${idx}][tipe]" required>
                                        <option value="BBM" ${item.tipe==='BBM'?'selected':''}>BBM</option>
                                        <option value="TOL" ${item.tipe==='TOL'?'selected':''}>TOL</option>
                                        <option value="Parkir" ${item.tipe==='Parkir'?'selected':''}>Parkir</option>
                                        <option value="Lainnya" ${item.tipe==='Lainnya'?'selected':''}>Lainnya</option>
                                        <option value="Budget Lebih" ${item.tipe==='Budget Lebih'?'selected':''}>Budget Lebih</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold small">Harga</label>
                                    <input type="number" class="form-control form-control-sm" name="items[${idx}][harga]" value="${item.harga}" min="500" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold small">Keterangan</label>
                                    <input type="text" class="form-control form-control-sm" name="items[${idx}][keterangan]" value="${item.keterangan ?? ''}">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold small">Bukti</label>
                                    <input type="file" class="form-control form-control-sm" name="items[${idx}][bukti]">
                                    ${item.bukti ? `<small class="text-muted d-block mt-1" style="font-size:0.75rem">Ada file: <a href="{{ asset('storage') }}/${item.bukti}" target="_blank">Lihat</a></small>` : ''}
                                </div>
                            </div>
                        </div>`;
                    $('#editItemsContainer').append(row);
                });
                new bootstrap.Modal(document.getElementById('ModalEdit')).show();
            });

            $(document).on('click', '.btn-delete', function() {
                const sourceId = $(this).data('source');
                const allData = biayaTable.rows().data().toArray();
                const items = allData.filter(item => (item.id_pickup_driver || item.id_pengajuan_spj) == sourceId);
                
                let list = items.map(i => `<li>${i.tipe}: ${currencyFormat.format(Number(i.harga) || 0)}</li>`).join('');

                Swal.fire({
                    title: 'Hapus semua biaya ini?',
                    html: `<p class="text-muted small">Semua item dalam grup ini akan dihapus:</p><ul class="text-start mb-0">${list}</ul>`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Hapus Semua',
                    cancelButtonText: 'Batal',
                    confirmButtonColor: '#dc3545'
                }).then(r => {
                    if (!r.isConfirmed) return;
                    $.ajax({
                        url: `/office/biaya-transportasi/delete/${sourceId}`,
                        type: 'DELETE',
                        data: { _token: '{{ csrf_token() }}' },
                        success: () => {
                            biayaTable.ajax.reload(null, false); // Reload DataTables tanpa reset halaman
                            Swal.fire('Sukses', 'Data berhasil dihapus', 'success');
                        },
                        error: xhr => Swal.fire('Gagal', xhr.responseJSON?.message || 'Gagal menghapus data', 'error')
                    });
                });
            });

            $('.modal').on('hidden.bs.modal', function() {
                $('.modal-backdrop').remove();
                $('body').removeClass('modal-open').css('padding-right', '');
            });

            $('#ModalTambah, #ModalEdit').on('shown.bs.modal', function() {
                $(this).find('.modal-body').css('max-height', '70vh').css('overflow-y', 'auto');
                $(this).find('#pickupSelect').first().focus();
            });
        });
    </script>
@endsection
