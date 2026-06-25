@extends('layouts.app')

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

@section('content')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
        integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
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

        <div class="modal fade" id="approveModal" tabindex="-1" aria-labelledby="approveModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="approveModalLabel">Confirm Approval</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form id="approveForm" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="modal-body">
                            <p>Apakah Disetujui?</p>

                            <!-- Manager Row -->
                            <div id="manager-row" style="display:none;">
                                <p>Approve sebagai <strong>Manager Divisi</strong>?</p>
                                <div class="btn-group" role="group" aria-label="Approval Options">
                                    <input type="radio" class="btn-check" name="approval_manager" id="approveYesManager"
                                        value="1" autocomplete="off" checked>
                                    <label class="btn btn-outline-primary" for="approveYesManager">Ya</label>
                                    <input type="radio" class="btn-check" name="approval_manager" id="approveNoManager"
                                        value="2" autocomplete="off">
                                    <label class="btn btn-outline-danger" for="approveNoManager">Tidak</label>
                                </div>
                            </div>

                            <!-- HRD Row -->
                            <div id="hrd-row" style="display:none;">
                                <p>Approve sebagai <strong>HRD</strong>?</p>
                                <div class="btn-group" role="group" aria-label="Approval Options">
                                    <input type="radio" class="btn-check" name="approval_hrd" id="approveYesHRD" value="1"
                                        autocomplete="off" checked>
                                    <label class="btn btn-outline-primary" for="approveYesHRD">Ya</label>
                                    <input type="radio" class="btn-check" name="approval_hrd" id="approveNoHRD" value="2"
                                        autocomplete="off">
                                    <label class="btn btn-outline-danger" for="approveNoHRD">Tidak</label>
                                </div>
                            </div>

                            <!-- GM & Direksi Row -->
                            <div id="gm-direksi-row" style="display:none;">
                                <p class="text-muted small mb-2">* Menyetujui ini akan otomatis meng-approve atas nama GM
                                    dan Direksi.</p>
                                <div class="btn-group" role="group" aria-label="Approval Options">
                                    <input type="radio" class="btn-check" name="approval_gm" id="approveYesGM" value="1"
                                        autocomplete="off" checked>
                                    <label class="btn btn-outline-primary" for="approveYesGM">Ya (GM & Direksi)</label>
                                    <input type="radio" class="btn-check" name="approval_gm" id="approveNoGM" value="2"
                                        autocomplete="off">
                                    <label class="btn btn-outline-danger" for="approveNoGM">Tidak</label>
                                </div>
                            </div>

                            <!-- Upload Bukti Row -->
                            <div id="upload-row" style="display:none;">
                                <p>Upload Bukti Transfer</p>
                                <div class="mb-3">
                                    <label for="bukti_transfer" class="form-label">Pilih File Bukti Transfer</label>
                                    <input type="file" class="form-control" name="bukti_transfer" id="bukti_transfer"
                                        accept=".jpg,.jpeg,.png,.pdf" required>
                                    <small class="text-muted">Format: JPG, PNG, PDF (Max 2MB)</small>
                                </div>
                            </div>

                            <!-- Direksi Row (Fallback) -->
                            <div id="direksi-row" style="display:none;">
                                <div class="btn-group" role="group" aria-label="Approval Options">
                                    <input type="radio" class="btn-check" name="approval_direksi" id="approveYesDireksi"
                                        value="1" autocomplete="off" checked>
                                    <label class="btn btn-outline-primary" for="approveYesDireksi">Ya</label>
                                    <input type="radio" class="btn-check" name="approval_direksi" id="approveNoDireksi"
                                        value="2" autocomplete="off">
                                    <label class="btn btn-outline-danger" for="approveNoDireksi">Tidak</label>
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal Upload Bukti Transfer untuk Karyawan -->
        <div class="modal fade" id="uploadBuktiModal" tabindex="-1" aria-labelledby="uploadBuktiModalLabel"
            aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="uploadBuktiModalLabel">
                            <i class="bi bi-upload"></i> Upload Bukti Transfer
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form id="uploadBuktiForm" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="modal-body">
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle"></i>
                                Upload bukti transfer yang Anda terima dari <strong>Finance &
                                    Accounting</strong>.
                                <br><small>File yang diterima: JPG, JPEG, PNG, atau PDF (max
                                    5MB).</small>
                            </div>
                            <div class="mb-3">
                                <label for="bukti_transfer_file" class="form-label">
                                    Bukti Transfer <span class="text-danger">*</span>
                                </label>
                                <input type="file" class="form-control" name="bukti_transfer" id="bukti_transfer_file"
                                    accept=".jpg,.jpeg,.png,.pdf" required>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-upload"></i> Upload Bukti
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="d-flex justify-content-end">
                {{-- @if (auth()->user()->jabatan == 'HRD' || auth()->user()->jabatan == 'Office ') --}}
                @can('Rekap SPJ')
                    <a href="{{ route('createPrint') }}" class="btn btn-success mx-4" data-toggle="tooltip" data-placement="top"
                        title="Cetak Data"><img src="{{ asset('icon/plus.svg') }}" class="" width="30px"> Cetak</a>
                @endcan
                <a href="suratperjalanan/create" class="btn btn-md click-primary mx-4" data-toggle="tooltip"
                    data-placement="top" title="Ajukan Cuti"><img src="{{ asset('icon/plus.svg') }}" class="" width="30px">
                    Ajukan Surat Perjalanan</a>
                {{-- @endif --}}
            </div>
            <div class="card m-4">
                <div class="card-body table-responsive">
                    <h3 class="card-title text-center my-1">{{ __('Data Surat Perjalanan') }}</h3>
                    <table class="table table-striped" id="jabatantable">
                        <thead>
                            <tr>
                                <th scope="col">No</th>
                                <th scope="col">Nama Karyawan</th>
                                <th scope="col">Divisi</th>
                                <th scope="col">Jabatan</th>
                                <th scope="col">Tipe</th>
                                <th scope="col">Tujuan</th>
                                <th scope="col">Alasan</th>
                                <th scope="col">Durasi</th>
                                <th scope="col">Tanggal</th>
                                <th scope="col">Status</th>
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
        <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.17.1/moment-with-locales.min.js"></script>
        <script>
            $(document).ready(function () {
                var userRole = @json(auth()->user()->jabatan);
                var user = '{{ auth()->user()->karyawan_id }}';
                //console.log(user);
                $('#tahun, #bulan').on('change', function () {
                    updateExportLink();
                });
                $('#jabatantable').DataTable({
                    "ajax": {
                        "url": "{{ route('getSuratPerjalanan') }}", // URL API untuk mengambil data
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
                    "columns": [{
                        "data": "tanggal_berangkat",
                        "visible": false
                    },
                    {
                        "data": "karyawan.nama_lengkap"
                    },
                    {
                        "data": "karyawan.divisi"
                    },
                    {
                        "data": "karyawan.jabatan",
                        "visible": false
                    },
                    {
                        "data": "tipe"
                    },
                    {
                        "data": "tujuan"
                    },
                    {
                        "data": "alasan"
                    },
                    {
                        "data": null,
                        "render": function (data) {
                            return data.durasi + ' Hari';
                        }
                    },
                    {
                        "data": null,
                        "render": function (data) {
                            moment.locale('id');
                            return moment(data.tanggal_berangkat).format('DD MMMM YYYY') + ' s/d ' +
                                moment(data.tanggal_pulang).format('DD MMMM YYYY');
                        }
                    },
                    {
                        "data": null,
                        "render": function (data) {
                            var currentUserRole = '{{ auth()->user()->jabatan }}';
                            var currentUserId = {{ auth()->user()->karyawan_id }};
                            var isPengajuOrFinance = (currentUserRole === 'Finance & Accounting' || data.karyawan.id == currentUserId);

                            // 1. Ditolak
                            if (data.approval_manager == '2' || data.approval_hrd == '2' || data.approval_gm == '2') {
                                return `<span class="badge rounded-pill bg-danger"><i class="bi bi-x-circle me-1"></i> Ditolak</span>`;
                            }
                            // 2. Menunggu Approval (Manager/HRD/GM)
                            if (data.approval_manager == '0') return `<span class="badge rounded-pill bg-warning text-dark">Menunggu Manager</span>`;
                            if (data.approval_hrd == '0') return `<span class="badge rounded-pill bg-warning text-dark">Menunggu HRD</span>`;
                            if (data.tipe == 'Internasional' && data.approval_gm == '0') return `<span class="badge rounded-pill bg-warning text-dark">Menunggu GM</span>`;

                            // 3. Semua Approval Selesai
                            var allApproved = (data.approval_manager == '1' && data.approval_hrd == '1' && (data.tipe == 'Domestik' || data.approval_gm == '1'));

                            if (allApproved) {
                                if (!data.ratespj || data.ratespj == 0) {
                                    return `<span class="badge rounded-pill bg-info text-dark">Menunggu Isi Rate SPJ</span>`;
                                } else {
                                    // Rate SPJ sudah diisi
                                    if (!data.bukti_transfer) {
                                        // Belum upload bukti
                                        if (isPengajuOrFinance) {
                                            return `<span class="badge rounded-pill bg-warning text-dark">Menunggu Upload Bukti Transfer</span>`;
                                        } else {
                                            // Untuk Manager/HRD/GM/Direksi: tampilkan "Disetujui"
                                            return `<span class="badge rounded-pill bg-success"><i class="bi bi-check-circle me-1"></i> Disetujui</span>`;
                                        }
                                    } else {
                                        // Sudah upload bukti
                                        if (isPengajuOrFinance) {
                                            return `<span class="badge rounded-pill bg-success"><i class="bi bi-check-circle me-1"></i> Selesai (Masuk Jurnal)</span>`;
                                        } else {
                                            return `<span class="badge rounded-pill bg-success"><i class="bi bi-check-circle me-1"></i> Disetujui</span>`;
                                        }
                                    }
                                }
                            }
                            return `<span class="badge rounded-pill bg-secondary">Status Tidak Diketahui</span>`;
                        }
                    },
                    {
                        "data": null,
                        "render": function (data, type, row) {
                            var actions = "";
                            var userRole = @json(auth()->user()->jabatan);

                            // 1. LOGIKA KHUSUS UNTUK GM
                            if (userRole == 'GM') {
                                actions +=
                                    '<div class="dropdown"><button class="btn dropdown-toggle" type="button" data-bs-toggle="dropdown">Actions</button><div class="dropdown-menu">';

                                // GM sebagai Manager Divisi (Khusus Office/ID tertentu)
                                if (data.approval_manager == '0' && (data.karyawan.divisi ===
                                    'Office' || [4, 13, 14, 29].includes(Number(data.karyawan
                                        .id)))) {
                                    actions +=
                                        '<button type="button" class="dropdown-item" onclick="openApproveModal(' +
                                        row.id +
                                        ', \'Manager\')">Approve sbg Manager Divisi</button>';
                                }
                                // GM sebagai GM (Hanya jika Internasional & Manager & HRD sudah approve)
                                else if (data.tipe == 'Internasional' && data.approval_manager ==
                                    '1' && data.approval_hrd == '1' && data.approval_gm == '0') {
                                    actions +=
                                        '<button type="button" class="dropdown-item" onclick="openApproveModal(' +
                                        row.id + ', \'GM\')">Approve sbg GM & Direksi</button>';
                                } else {
                                    actions +=
                                        '<button type="button" class="dropdown-item disabled">Menunggu / Tidak Berwenang</button>';
                                }

                                if (data.approval_manager == '0') {
                                    actions +=
                                        '<form onsubmit="return confirm(\'Yakin?\');" action="{{ url('/suratperjalanan') }}/' +
                                        row.id +
                                        '" method="POST">@csrf @method('DELETE')<button type="submit" class="dropdown-item text-danger">Hapus</button></form>';
                                } else {
                                    actions +=
                                        '<a class="dropdown-item" href="{{ url('/suratperjalanan') }}/' +
                                        row.id + '">Form PDF</a>';
                                }
                                actions += '</div></div>';
                            }

                            // 2. LOGIKA KHUSUS UNTUK HRD
                            else if (userRole == 'HRD') {
                                actions +=
                                    '<div class="dropdown"><button class="btn dropdown-toggle" type="button" data-bs-toggle="dropdown">Actions</button><div class="dropdown-menu">';
                                var isAllApproved = (data.approval_manager == '1' && data
                                    .approval_hrd == '1' && (data.tipe == 'Domestik' || data
                                        .approval_gm == '1'));

                                if (data.approval_manager == '0' && data.karyawan.divisi ===
                                    'Office') {
                                    actions +=
                                        '<button type="button" class="dropdown-item" onclick="openApproveModal(' +
                                        row.id +
                                        ', \'Manager\')">Approve sbg Manager Divisi</button>';
                                } else if (data.approval_manager == '1' && data.approval_hrd ==
                                    '0') {
                                    actions +=
                                        '<button type="button" class="dropdown-item" onclick="openApproveModal(' +
                                        row.id + ', \'HRD\')">Approve sbg HRD</button>';
                                    actions +=
                                        '<a class="dropdown-item disabled" href="#">Rate SPJ (Terkunci)</a>';
                                } else if (isAllApproved) {
                                    actions +=
                                        '<a class="dropdown-item" href="{{ url('/suratperjalanan') }}/' +
                                        row.id + '/edit">Isi Rate SPJ</a>';
                                    actions +=
                                        '<a class="dropdown-item" href="{{ url('/suratperjalanan') }}/' +
                                        row.id + '">Form PDF</a>';
                                } else if (data.approval_manager == '2' || data.approval_hrd ==
                                    '2' || data.approval_gm == '2' || data.approval_direksi == '2'
                                ) {
                                    actions +=
                                        '<a class="dropdown-item disabled text-danger" href="#">Ditolak</a>';
                                } else {
                                    actions +=
                                        '<a class="dropdown-item disabled" href="#">Menunggu Approval GM/Direksi</a>';
                                }
                                actions += '</div></div>';
                            }

                            // 3. LOGIKA KHUSUS UNTUK DIREKSI (Direktur & Direktur Utama)
                            else if (userRole == 'Direktur' || userRole == 'Direktur Utama') {
                                actions +=
                                    '<div class="dropdown"><button class="btn dropdown-toggle" type="button" data-bs-toggle="dropdown">Actions</button><div class="dropdown-menu">';
                                // Direksi melakukan approve langsung dari Notifikasi, jadi di tabel hanya butuh tombol PDF
                                actions +=
                                    '<a class="dropdown-item" href="{{ url('/suratperjalanan') }}/' +
                                    row.id + '">Form PDF</a>';
                                actions += '</div></div>';
                            }

                            // 4. LOGIKA UNTUK MANAGER DIVISI LAINNYA
                            else if (['Office Manager', 'Education Manager', 'SPV Sales',
                                'Koordinator ITSM', 'Koordinator Office'
                            ].includes(userRole)) {
                                actions +=
                                    '<div class="dropdown"><button class="btn dropdown-toggle" type="button" data-bs-toggle="dropdown">Actions</button><div class="dropdown-menu">';

                                if (data.approval_manager == '0') {
                                    actions +=
                                        '<button type="button" class="dropdown-item" onclick="openApproveModal(' +
                                        row.id + ', \'Manager\')">Approve Manager Divisi</button>';
                                } else {
                                    actions +=
                                        '<button type="button" class="dropdown-item disabled">Sudah Approve / Menunggu HRD</button>';
                                }

                                if (data.approval_manager == '0') {
                                    actions +=
                                        '<form onsubmit="return confirm(\'Yakin?\');" action="{{ url('/suratperjalanan') }}/' +
                                        row.id +
                                        '" method="POST">@csrf @method('DELETE')<button type="submit" class="dropdown-item text-danger">Hapus</button></form>';
                                } else {
                                    actions +=
                                        '<a class="dropdown-item" href="{{ url('/suratperjalanan') }}/' +
                                        row.id + '">Form PDF</a>';
                                }
                                actions += '</div></div>';
                            }

                            // 4.5. LOGIKA KHUSUS UNTUK FINANCE & ACCOUNTING
                            else if (userRole == 'Finance & Accounting') {
                                actions += '<div class="dropdown"><button class="btn dropdown-toggle" type="button" data-bs-toggle="dropdown">Actions</button><div class="dropdown-menu">';

                                var allApproved = (data.approval_manager == '1' && data.approval_hrd == '1' && (data.tipe == 'Domestik' || data.approval_gm == '1'));
                                var ratespjValue = parseFloat(data.ratespj) || 0;
                                var isReadyForUpload = (allApproved && ratespjValue > 0);

                                if (isReadyForUpload && !data.bukti_transfer) {
                                    actions += '<button type="button" class="dropdown-item" onclick="openApproveModal(' + row.id + ', \'Upload\')">Upload Bukti Transfer</button>';
                                } else if (data.bukti_transfer) {
                                    actions += '<a class="dropdown-item disabled text-success" href="#">Bukti Sudah Diupload</a>';
                                    actions += '<a class="dropdown-item" href="{{ url('storage') }}/' + data.bukti_transfer + '" target="_blank">Download Bukti</a>';
                                } else {
                                    actions += '<a class="dropdown-item disabled" href="#">Menunggu Rate SPJ</a>';
                                }
                                actions += '<a class="dropdown-item" href="{{ url('/suratperjalanan') }}/' + row.id + '">Form PDF</a>';
                                actions += '</div></div>';
                            }

                            // 5. LOGIKA UNTUK USER BIASA (PENGAJU)
                            else {
                                actions += '<div class="dropdown"><button class="btn dropdown-toggle" type="button" data-bs-toggle="dropdown">Actions</button><div class="dropdown-menu">';

                                var allApproved = (data.approval_manager == '1' && data.approval_hrd == '1' && (data.tipe == 'Domestik' || data.approval_gm == '1'));
                                var ratespjValue = parseFloat(data.ratespj) || 0;
                                var isReadyForUpload = (allApproved && ratespjValue > 0);

                                if (data.approval_manager == '0') {
                                    actions += '<form onsubmit="return confirm(\'Yakin?\');" action="{{ url('/suratperjalanan') }}/' + row.id + '" method="POST">@csrf @method('DELETE')<button type="submit" class="dropdown-item text-danger">Hapus</button></form>';
                                } else {
                                    actions += '<button type="submit" class="dropdown-item disabled text-danger">Hapus</button>';
                                }

                                // Tombol Upload Bukti (Hanya untuk Pengaju)
                                if (isReadyForUpload && !data.bukti_transfer) {
                                    actions += '<button type="button" class="dropdown-item" onclick="openApproveModal(' + row.id + ', \'Upload\')">Upload Bukti Transfer</button>';
                                } else if (data.bukti_transfer) {
                                    actions += '<a class="dropdown-item disabled text-success" href="#">Bukti Sudah Diupload</a>';
                                    actions += '<a class="dropdown-item" href="{{ url('storage') }}/' + data.bukti_transfer + '" target="_blank">Download Bukti</a>';
                                }

                                actions += '<a class="dropdown-item" href="{{ url('/suratperjalanan') }}/' + row.id + '">Form PDF</a>';
                                actions += '</div></div>';
                            }

                            return actions;
                        }
                    }
                    ],
                    "order": [
                        [0, 'desc']
                    ],
                    "columnDefs": [{
                        "targets": [0],
                        "type": "date"
                    }],
                });
                $('#approveForm').on('submit', function (e) {
                    e.preventDefault();
                    const form = $(this);

                    // Fungsi untuk menonaktifkan input pada row yang tersembunyi
                    function toggleInputByVisibility(rowId) {
                        if ($(rowId).is(':hidden')) {
                            $(rowId).find('input, select, textarea').prop('disabled', true);
                        } else {
                            $(rowId).find('input, select, textarea').prop('disabled', false);
                        }
                    }

                    // Terapkan ke SEMUA row approval
                    toggleInputByVisibility('#manager-row');
                    toggleInputByVisibility('#hrd-row');
                    toggleInputByVisibility('#gm-direksi-row');
                    toggleInputByVisibility('#upload-row');
                    toggleInputByVisibility('#direksi-row');

                    const url = form.attr('action');
                    const formData = new FormData(this);

                    $.ajax({
                        url: url,
                        method: 'POST',  // Pastikan POST
                        data: formData,
                        contentType: false,
                        processData: false,
                        success: function (res) {
                            $('#approveModal').modal('hide');
                            $('#jabatantable').DataTable().ajax.reload(null, false);

                            // Tampilkan success message
                            if (res.success) {
                                alert(res.message || 'Data berhasil disimpan!');
                            }
                        },
                        error: function (err) {
                            console.error('Error:', err);
                            let errorMsg = "Gagal menyimpan data!";
                            if (err.responseJSON && err.responseJSON.errors) {
                                errorMsg = Object.values(err.responseJSON.errors).flat().join('\n');
                            } else if (err.responseJSON && err.responseJSON.message) {
                                errorMsg = err.responseJSON.message;
                            }
                            alert(errorMsg);
                        },
                        complete: function () {
                            form.find('input, select, textarea').prop('disabled', false);
                        }
                    });
                });
                const urlParams = new URLSearchParams(window.location.search);
                let financeApprovalId = urlParams.get('finance_approval_id');

                if (financeApprovalId && '{{ auth()->user()->jabatan }}' === 'Finance & Accounting') {
                    $('#jabatantable').on('xhr.dt', function () {
                        if (financeApprovalId) {
                            setTimeout(function () {
                                openApproveModal(financeApprovalId, 'Finance');
                            }, 500);

                            window.history.replaceState({}, document.title, window.location.pathname);
                            financeApprovalId = null;
                        }
                    });
                }

            });

            function openApproveModal(id, jabatan) {
                // Sembunyikan semua row terlebih dahulu
                $('#manager-row').hide();
                $('#hrd-row').hide();
                $('#gm-direksi-row').hide();
                $('#upload-row').hide();
                $('#direksi-row').hide();

                if (jabatan === 'Manager') {
                    $('#manager-row').show();
                } else if (jabatan === 'HRD') {
                    $('#hrd-row').show();
                } else if (jabatan === 'GM') {
                    $('#gm-direksi-row').show();
                } else if (jabatan === 'Upload') {
                    $('#upload-row').show();
                } else if (jabatan === 'Direksi') {
                    $('#direksi-row').show();
                }

                var approveUrl = "{{ url('/suratperjalanan') }}/" + id + "/approval";
                if (jabatan === 'Upload') {
                    approveUrl = "{{ url('/suratperjalanan') }}/" + id + "/upload-bukti";
                }

                $('#approveForm').attr('action', approveUrl);
                $('#approveModal').modal('show');
            }

            function openUploadBuktiModal(id) {
                var uploadUrl = "{{ url('/suratperjalanan') }}/" + id + "/upload-bukti";
                $('#uploadBuktiForm').attr('action', uploadUrl);

                $('#uploadBuktiForm')[0].reset();

                $('#uploadBuktiModal').modal('show');
            }

            // Handle submit form upload bukti transfer
            $('#uploadBuktiForm').on('submit', function (e) {
                e.preventDefault();
                const form = $(this);
                const url = form.attr('action');
                const formData = new FormData(this);

                $.ajax({
                    url: url,
                    method: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (res) {
                        $('#uploadBuktiModal').modal('hide');
                        $('#jabatantable').DataTable().ajax.reload(null, false);
                        location.reload(); // Reload untuk tampilkan success message
                    },
                    error: function (err) {
                        console.error(err);
                        let errorMsg = "Gagal upload bukti transfer!";
                        if (err.responseJSON && err.responseJSON.errors) {
                            errorMsg = Object.values(err.responseJSON.errors).flat().join('\n');
                        } else if (err.responseJSON && err.responseJSON.message) {
                            errorMsg = err.responseJSON.message;
                        }
                        alert(errorMsg);
                    }
                });
            });

            function toggleAlasanManager(show) {
                if (show) {
                    document.getElementById('alasanManagerInput').style.display = 'block';
                } else {
                    document.getElementById('alasanManagerInput').style.display = 'none';
                    document.getElementById('alasan_manager').value = ''; // Clear the input if hidden
                }
            }
        </script>
    @endpush
@endsection