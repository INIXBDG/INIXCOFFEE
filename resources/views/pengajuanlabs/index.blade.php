@extends('layouts.app')
@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12">

            @php
                $userJabatan = auth()->user()->karyawan->jabatan ?? '';
                $isTeknis = in_array($userJabatan, ['Technical Support', 'Koordinator ITSM']);
                $bolehMengajukan = in_array($userJabatan, ['Instruktur', 'Education Manager']);
            @endphp

            @if($isTeknis)
            <ul class="nav nav-tabs mb-4" id="labTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active fw-bold" id="pengajuan-tab" data-bs-toggle="tab" data-bs-target="#pengajuan-pane" type="button" role="tab">
                        Daftar Pengajuan
                    </button>
                </li>

                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-bold text-primary" id="kelola-tab" data-bs-toggle="tab" data-bs-target="#kelola-pane" type="button" role="tab" onclick="loadMasterLabs()">
                        Kelola Master Lab
                    </button>
                </li>
            </ul>
            @endif

            <div class="tab-content" id="labTabsContent">


                <div class="tab-pane fade show active" id="pengajuan-pane" role="tabpanel">
                    @if($bolehMengajukan)
                        <div class="d-flex justify-content-end">
                            @if (isset($tracking) && $tracking == 'tutup')
                                <button class="btn btn-md btn-secondary mx-4" disabled title="Selesaikan pengajuan sebelumnya terlebih dahulu">
                                    <img src="{{ asset('icon/plus.svg') }}" width="30px"> Permintaan Lab
                                </button>
                            @else
                                <a href="{{ route('pengajuanlabsdansubs.create') }}" class="btn btn-md click-primary mx-4" data-toggle="tooltip" title="Ajukan Lab">
                                    <img src="{{ asset('icon/plus.svg') }}" width="30px"> Permintaan Lab
                                </a>
                            @endif
                        </div>
                    @endif

                    <div class="modal fade" id="approveRejectModal" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Konfirmasi Aksi</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <form id="approvalForm">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="id" id="modalId">
                                    <input type="hidden" name="approval" id="modalApproval">

                                    <div class="modal-body">
                                        <p><i class="bi bi-info-circle-fill text-primary"></i> <span id="actionLabel" class="fw-bold"></span></p>

                                        <div class="mb-3 d-none" id="reasonContainer">
                                            <label class="form-label">Alasan Penolakan <span class="text-danger">*</span></label>
                                            <textarea class="form-control" name="alasan" id="alasan" rows="3"></textarea>
                                        </div>

                                        <div class="mb-3 d-none" id="financeStatusContainer">
                                            <label class="form-label">Update Status Pencairan</label>
                                            <select class="form-select" id="finance_status" name="finance_status">
                                                <option value="">-- Pilih Status --</option>
                                                <option value="Sedang Dikonfirmasi oleh Bagian Finance kepada General Manager">Sedang Dikonfirmasi oleh Bagian Finance kepada General Manager</option>
                                                <option value="Sedang Dikonfirmasi oleh Bagian Finance kepada Direksi">Sedang Dikonfirmasi oleh Bagian Finance kepada Direksi</option>
                                                <option value="Finance Menunggu Approve Direksi">Finance Menunggu Approve Direksi</option>
                                                <option value="Membuat Permintaan Ke Direktur Utama">Membuat Permintaan Ke Direktur Utama</option>
                                                <option value="Pengajuan sedang dalam proses Pencairan">Pengajuan sedang dalam proses Pencairan</option>
                                                <option value="Pencairan Sudah Selesai">Pencairan Sudah Selesai</option>
                                                <option value="Selesai">Selesai</option>
                                            </select>
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

                    <div class="modal fade" id="uploadInvoiceModal" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog">
                            <form id="uploadInvoiceForm" enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" id="uploadInvoiceId">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Upload Invoice</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label class="form-label">File Invoice (PDF/JPG/PNG)</label>
                                            <input type="file" name="invoice" class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="submit" class="btn btn-primary">Upload</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    @if (in_array($userJabatan, ['Finance & Accounting', 'GM', 'Koordinator ITSM', 'Technical Support','Education Manager']))
                        <div class="card my-3">
                            <div class="card-body d-flex justify-content-center">
                                <div class="col-md-4 mx-1">
                                    <label class="form-label">Tahun</label>
                                    <select id="tahun" class="form-select">
                                        @for ($y = 2023; $y <= now()->year + 1; $y++)
                                            <option value="{{ $y }}" {{ $y == now()->year ? 'selected' : '' }}>{{ $y }}</option>
                                        @endfor
                                    </select>
                                </div>
                                <div class="col-md-4 mx-1">
                                    <label class="form-label">Bulan</label>
                                    <select id="bulan" class="form-select">
                                        @foreach (range(1, 12) as $m)
                                            <option value="{{ $m }}" {{ $m == now()->month ? 'selected' : '' }}>
                                                {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4 mx-1">
                                    <button onclick="loadPengajuan()" class="btn click-primary w-100" style="margin-top: 32px">Cari Data</button>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="card m-4">
                        <div class="card-body table-responsive">
                            <h3 class="card-title text-center my-1">Data Pengajuan Lab</h3>
                            <table class="table table-striped" id="pengajuanLabSubsTable">
                                <thead>
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Pengaju</th>
                                        <th>Divisi</th>
                                        <th>Jabatan</th>
                                        <th>Kategori</th>
                                        <th>Nama Lab</th>
                                        <th>Status Tracking</th>
                                        <th>RKM / Materi</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>

                    <div class="card m-4">
                        <div class="card-body table-responsive">
                            <h3 class="card-title text-center my-1">Riwayat Selesai</h3>
                            <table class="table table-striped" id="pengajuanSelesaiTable">
                                <thead>
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Pengaju</th>
                                        <th>Divisi</th>
                                        <th>Jabatan</th>
                                        <th>Kategori</th>
                                        <th>Nama Lab</th>
                                        <th>Status Akhir</th>
                                        <th>RKM</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>

                </div>

                @if($isTeknis)
                <div class="tab-pane fade" id="kelola-pane" role="tabpanel">
                    <div class="card m-4">
                        <div class="card-body table-responsive">
                            <h3 class="card-title text-center my-1">Master Data Laboratorium</h3>
                            <table class="table table-striped text-nowrap" id="masterLabTable" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>Nama Lab</th>
                                        <th>Vendor</th>
                                        <th>Tipe</th>
                                        <th>Status</th>
                                        <th>Deskripsi</th>
                                        <th>URL Lab</th>
                                        <th>Kode Akses</th>
                                        <th>Masa Aktif</th>
                                        <th>Mata Uang</th>
                                        <th>Harga Asli</th>
                                        <th>Kurs</th>
                                        <th>Estimasi (Rp)</th>
                                        <th>Materi Terhubung</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="modal fade" id="editMasterLabModal" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title fw-bold">Edit Data Teknis Lab</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <form id="editMasterLabForm">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="id" id="edit_lab_id">

                                    <div class="modal-body">
                                        <div class="mb-3 row">
                                            <label class="col-sm-4 col-form-label">Nama Lab / Software <span class="text-danger">*</span></label>
                                            <div class="col-sm-8">
                                                <input type="text" class="form-control" name="nama_labs" id="edit_nama_labs" required>
                                            </div>
                                        </div>

                                        <div class="mb-3 row">
                                            <label class="col-sm-4 col-form-label">Vendor / Merk</label>
                                            <div class="col-sm-8">
                                                <input type="text" class="form-control" name="merk" id="edit_merk">
                                            </div>
                                        </div>

                                        <div class="mb-3 row">
                                            <label class="col-sm-4 col-form-label">Tipe Aset</label>
                                            <div class="col-sm-8">
                                                <select class="form-select" name="tipe" id="edit_tipe">
                                                    <option value="subscription">Subscription (Berlangganan)</option>
                                                    <option value="one-time">One-Time</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="mb-3 row" id="row_edit_duration" style="display: none;">
                                            <label class="col-sm-4 col-form-label">Durasi Akses (Menit)</label>
                                            <div class="col-sm-8">
                                                <input type="number" class="form-control" name="duration_minutes" id="edit_duration_minutes">
                                            </div>
                                        </div>

                                        <div class="mb-3 row">
                                            <label class="col-sm-4 col-form-label">Status</label>
                                            <div class="col-sm-8">
                                                <select class="form-select" name="status" id="edit_status">
                                                    <option value="active">Active</option>
                                                    <option value="pending">Pending</option>
                                                    <option value="expired">Expired</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="mb-3 row">
                                            <label class="col-sm-4 col-form-label">Deskripsi</label>
                                            <div class="col-sm-8">
                                                <textarea class="form-control" name="deskripsi" id="edit_deskripsi" rows="3"></textarea>
                                            </div>
                                        </div>

                                        <div class="mb-3 row">
                                            <label class="col-sm-4 col-form-label">URL Lab</label>
                                            <div class="col-sm-8">
                                                <input type="text" class="form-control" name="url_labs" id="edit_url_labs">
                                            </div>
                                        </div>

                                        <div class="mb-3 row">
                                            <label class="col-sm-4 col-form-label">Kode Akses / Key</label>
                                            <div class="col-sm-8">
                                                <input type="text" class="form-control" name="kode_akses" id="edit_kode_akses">
                                            </div>
                                        </div>

                                        <div class="mb-3 row">
                                            <label class="col-sm-4 col-form-label">Tanggal Mulai</label>
                                            <div class="col-sm-8">
                                                <input type="date" class="form-control" name="tanggal_mulai" id="edit_tanggal_mulai">
                                            </div>
                                        </div>

                                        <div class="mb-3 row">
                                            <label class="col-sm-4 col-form-label">Tanggal Berakhir</label>
                                            <div class="col-sm-8">
                                                <input type="date" class="form-control" name="tanggal_berakhir" id="edit_tanggal_berakhir">
                                            </div>
                                        </div>

                                        <div class="mb-3 row">
                                            <label class="col-sm-4 col-form-label">Mata Uang</label>
                                            <div class="col-sm-8">
                                                <select class="form-select" name="mata_uang" id="edit_mata_uang">
                                                    <option value="Dollar">Dollar</option>
                                                    <option value="Rupiah">Rupiah</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="mb-3 row">
                                            <label class="col-sm-4 col-form-label">Nominal Harga Asli</label>
                                            <div class="col-sm-8">
                                                <input type="number" step="0.01" class="form-control calculate-harga" name="nominal_harga_asli" id="edit_nominal_harga_asli">
                                            </div>
                                        </div>

                                        <div class="mb-3 row">
                                            <label class="col-sm-4 col-form-label">Kurs (Rate)</label>
                                            <div class="col-sm-8">
                                                <input type="number" step="0.01" class="form-control calculate-harga" name="kurs" id="edit_kurs">
                                            </div>
                                        </div>

                                        <div class="mb-3 row">
                                            <label class="col-sm-4 col-form-label">Estimasi Rupiah</label>
                                            <div class="col-sm-8">
                                                <div class="input-group">
                                                    <span class="input-group-text bg-light">Rp.</span>
                                                    <input type="number" class="form-control bg-light" name="harga_rupiah" id="edit_harga_rupiah" readonly>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="mb-3 row">
                                            <label class="col-sm-4 col-form-label">Terhubung ke Materi</label>
                                            <div class="col-sm-8">
                                                <select class="form-select" name="materi_ids[]" id="edit_materi" multiple="multiple" style="width: 100%;">
                                                    @if(isset($materis))
                                                        @foreach($materis as $m)
                                                            <option value="{{ $m->id }}">{{ $m->nama_materi }}</option>
                                                        @endforeach
                                                    @endif
                                                </select>
                                            </div>
                                        </div>

                                    </div>
                                    <div class="modal-footer">
                                        <button type="submit" class="btn btn-dark" style="background-color: #1a2a40;">Simpan Perubahan</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

@push('js')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    const userRole = "{{ auth()->user()->karyawan->jabatan ?? '' }}";
    const userId = {{ auth()->user()->karyawan->id ?? 'null' }};

    $(document).ready(function() {
        loadPengajuan();
        loadPengajuanSelesai();

        $('.calculate-harga').on('input', calculateEstimasiRupiah);
        $('#edit_mata_uang').on('change', calculateEstimasiRupiah);

        // --- INISIALISASI SELECT2 ---
        $('#edit_materi').select2({
            theme: 'bootstrap-5',
            placeholder: "Pilih Materi Terkait...",
            allowClear: true,
            dropdownParent: $('#editMasterLabModal')
        });
    });

    function calculateEstimasiRupiah() {
        let mataUang = $('#edit_mata_uang').val();
        let nominal = parseFloat($('#edit_nominal_harga_asli').val()) || 0;

        if (mataUang === 'Rupiah') {
            $('#edit_kurs').val(1).prop('readonly', true);
            $('#edit_harga_rupiah').val(nominal);
        } else {
            $('#edit_kurs').prop('readonly', false);
            let kurs = parseFloat($('#edit_kurs').val()) || 0;
            $('#edit_harga_rupiah').val(nominal * kurs);
        }
    }

    // --- 1. LOAD PENGAJUAN AKTIF ---
    function loadPengajuan() {
        let month = $('#bulan').length ? $('#bulan').val() : (new Date().getMonth() + 1);
        let year  = $('#tahun').length ? $('#tahun').val() : new Date().getFullYear();

        $.ajax({
            url: `/getPengajuanLabSubs/${month}/${year}`,
            type: "GET",
            beforeSend: function() {
                $("#pengajuanLabSubsTable tbody").html('<tr><td colspan="9" class="text-center">Loading data...</td></tr>');
            },
            success: function(res) {
                if (res.success && res.data.length > 0) {
                    let rows = '';
                    $.each(res.data, function(index, item) {
                        let lastTrack = item.tracking.length > 0 ? item.tracking[item.tracking.length - 1].tracking : 'Belum Ada Tracking';

                        if (lastTrack.includes('Selesai') || lastTrack.includes('Siap Digunakan')) return;

                        rows += renderRow(item, lastTrack);
                    });

                    if(!rows) rows = '<tr><td colspan="9" class="text-center text-muted">Tidak ada pengajuan aktif.</td></tr>';

                    $("#pengajuanLabSubsTable tbody").html(rows);

                    if ($.fn.DataTable.isDataTable('#pengajuanLabSubsTable')) {
                        $('#pengajuanLabSubsTable').DataTable().destroy();
                    }
                    $('#pengajuanLabSubsTable').DataTable({ "order": [[ 0, "desc" ]] });

                } else {
                    $("#pengajuanLabSubsTable tbody").html('<tr><td colspan="9" class="text-center text-muted">Data tidak ditemukan.</td></tr>');
                }
            },
            error: function() {
                $("#pengajuanLabSubsTable tbody").html('<tr><td colspan="9" class="text-center text-danger">Gagal memuat data.</td></tr>');
            }
        });
    }

    // --- 2. LOAD RIWAYAT SELESAI ---
    function loadPengajuanSelesai() {
        let month = $('#bulan').length ? $('#bulan').val() : (new Date().getMonth() + 1);
        let year  = $('#tahun').length ? $('#tahun').val() : new Date().getFullYear();

        $.ajax({
            url: `/getPengajuanLabSubs/${month}/${year}`,
            type: "GET",
            success: function(res) {
                if (res.success && res.data.length > 0) {
                    let rows = '';
                    $.each(res.data, function(index, item) {
                        let lastTrack = item.tracking.length > 0 ? item.tracking[item.tracking.length - 1].tracking : '-';

                        if (lastTrack.includes('Selesai') || lastTrack.includes('Siap Digunakan')) {
                            rows += renderRowSelesai(item, lastTrack);
                        }
                    });

                    if(!rows) rows = '<tr><td colspan="9" class="text-center text-muted">Belum ada riwayat selesai.</td></tr>';

                    $("#pengajuanSelesaiTable tbody").html(rows);

                    if ($.fn.DataTable.isDataTable('#pengajuanSelesaiTable')) {
                        $('#pengajuanSelesaiTable').DataTable().destroy();
                    }
                    $('#pengajuanSelesaiTable').DataTable({ "order": [[ 0, "desc" ]] });
                }
            }
        });
    }

    // --- RENDER ROW ---
    function renderRow(item, status) {
        let kategori = item.jenis_transaksi === 'baru'
            ? '<span class="badge bg-danger">Pengadaan Baru</span>'
            : '<span class="badge bg-success">Existing Asset</span>';

        let labName = item.lab ? item.lab.nama_labs : '-';
        let rkmInfo = item.rkm ? `${item.rkm.materi?.nama_materi ?? '-'} <br><small class="text-muted">(${item.rkm.perusahaan?.nama_perusahaan ?? '-'})</small>` : '-';
        let btns = generateButtons(item, status);

        return `
            <tr>
                <td>${moment(item.created_at).format('DD/MM/YYYY')}</td>
                <td>${item.karyawan?.nama_lengkap ?? '-'}</td>
                <td>${item.karyawan?.divisi ?? '-'}</td>
                <td>${item.karyawan?.jabatan ?? '-'}</td>
                <td>${kategori}</td>
                <td>${labName}</td>
                <td>${formatStatus(status)}</td>
                <td>${rkmInfo}</td>
                <td>${btns}</td>
            </tr>
        `;
    }

    function renderRowSelesai(item, status) {
        let kategori = item.jenis_transaksi === 'baru' ? '<span class="badge bg-danger">Baru</span>' : '<span class="badge bg-success">Existing</span>';
        let labName = item.lab ? item.lab.nama_labs : '-';
        let rkmInfo = item.rkm ? item.rkm.materi?.nama_materi : '-';

        let btns = `
            <div class="dropdown">
                <button class="btn btn-sm btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">Aksi</button>
                <ul class="dropdown-menu shadow">
                    <li><button class="dropdown-item" onclick="viewDetail(${item.id})"><img src="{{ asset('icon/clipboard-primary.svg') }}" width="16" class="me-1"> Detail</button></li>
                    ${invoiceAction(item.id, item.invoice, item)}
                </ul>
            </div>`;

        return `
            <tr>
                <td>${moment(item.created_at).format('DD/MM/YYYY')}</td>
                <td>${item.karyawan?.nama_lengkap ?? '-'}</td>
                <td>${item.karyawan?.divisi ?? '-'}</td>
                <td>${item.karyawan?.jabatan ?? '-'}</td>
                <td>${kategori}</td>
                <td>${labName}</td>
                <td>${formatStatus(status)}</td>
                <td>${rkmInfo}</td>
                <td>${btns}</td>
            </tr>
        `;
    }

    // --- LOGIC GENERATE BUTTONS ---
    function generateButtons(item, status) {
        let isOwner = (item.karyawan_id == userId);
        let btns = `<div class="dropdown">
            <button class="btn btn-sm btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">Aksi</button>
            <ul class="dropdown-menu shadow">
                <li><button class="dropdown-item" onclick="viewDetail(${item.id})"><img src="{{ asset('icon/clipboard-primary.svg') }}" width="16" class="me-1"> Detail</button></li>`;

        // Ubah string status ke huruf kecil untuk keamanan pencocokan (case-insensitive)
        let statusLower = status.toLowerCase();

        if (isOwner && statusLower.includes('diajukan') && !statusLower.includes('ditolak')) {
            btns += `<li><button class="dropdown-item text-danger" onclick="deletePengajuan(${item.id})"><img src="{{ asset('icon/trash-danger.svg') }}" width="16" class="me-1"> Hapus</button></li>`;
        }

        let canApprove = false;

        // PERBAIKAN: Gunakan statusLower untuk mencocokkan teks
        if (userRole === 'Education Manager' && statusLower.includes('ditinjau oleh education manager')) {
            canApprove = true;
        }
        else if (userRole === 'Koordinator ITSM' && statusLower.includes('ditinjau oleh koordinator itsm')) {
            canApprove = true;
        }

        if (canApprove) {
            btns += `
                <li><hr class="dropdown-divider"></li>
                <li><button class="dropdown-item text-success" onclick="openApproveRejectModal(${item.id}, 'approve')"><img src="{{ asset('icon/check-circle.svg') }}" width="16" class="me-1"> Approve</button></li>
                <li><button class="dropdown-item text-danger" onclick="openApproveRejectModal(${item.id}, 'reject')"><img src="{{ asset('icon/x-circle.svg') }}" width="16" class="me-1"> Reject</button></li>
            `;
        }

        if ((userRole === 'Technical Support' || userRole === 'Koordinator ITSM') && !statusLower.includes('selesai')) {
             btns += `<li><button class="dropdown-item" onclick="editPengajuan(${item.id})"><img src="{{ asset('icon/edit-warning.svg') }}" width="16" class="me-1"> Edit Teknis</button></li>`;
        }

        if ((userRole === 'Finance & Accounting' || userRole === 'Finance &amp; Accounting') && item.jenis_transaksi === 'baru') {
             const financeStatuses = [
                'diproses oleh finance',
                'sedang dikonfirmasi oleh bagian finance kepada general manager',
                'sedang dikonfirmasi oleh bagian finance kepada direksi',
                'finance menunggu approve direksi',
                'membuat permintaan ke direktur utama',
                'pengajuan sedang dalam proses pencairan',
                'pencairan sudah selesai',
                'selesai'
            ];

            // PERBAIKAN: Cek menggunakan statusLower
            if (financeStatuses.some(finStatus => statusLower.includes(finStatus))) {
                 btns += `<li><button class="dropdown-item text-warning" onclick="openApproveRejectModal(${item.id}, 'finance-update')"><img src="{{ asset('icon/edit-warning.svg') }}" width="16" class="me-1"> Update Pencairan</button></li>`;
             }
        }

        if (item.jenis_transaksi === 'baru') {
            btns += invoiceAction(item.id, item.invoice, item);
        }

        btns += `</ul></div>`;
        return btns;
    }

    function invoiceAction(id, invoice, item) {
        let dataLengkap = item.lab && item.lab.harga;
        if (!dataLengkap) {
            return `<li><button class="dropdown-item text-muted" disabled><img src="{{ asset('icon/upload.svg') }}" width="16" class="me-1"> Upload Invoice (Tunggu Data Teknis)</button></li>`;
        }
        if (invoice) {
            return `<li><a class="dropdown-item" href="/storage/pengajuanlabsubs/${invoice}" target="_blank"><img src="{{ asset('icon/eye.svg') }}" width="16" class="me-1"> Lihat Invoice</a></li>`;
        } else {
            return `<li><button class="dropdown-item" onclick="openUploadInvoiceModal(${id})"><img src="{{ asset('icon/upload.svg') }}" width="16" class="me-1"> Upload Invoice</button></li>`;
        }
    }

    function formatStatus(status) {
        if (!status) return '<span class="fw-bold">Draft</span>';
        return `<span class="fw-bold text-dark">${status}</span>`;
    }

    function openApproveRejectModal(id, type) {
        $('#modalId').val(id);
        $('#modalApproval').val('');
        $('#alasan').val('');
        $('#reasonContainer').addClass('d-none');
        $('#financeStatusContainer').addClass('d-none');

        if (type === 'approve') {
            $('#actionLabel').text('Anda yakin ingin MENYETUJUI pengajuan ini?');
            $('#modalApproval').val('1');
        } else if (type === 'reject') {
            $('#actionLabel').text('Anda yakin ingin MENOLAK pengajuan ini?');
            $('#modalApproval').val('2');
            $('#reasonContainer').removeClass('d-none');
        } else if (type === 'finance-update') {
            $('#actionLabel').text('Pilih status proses pencairan:');
            $('#financeStatusContainer').removeClass('d-none');
        }
        new bootstrap.Modal(document.getElementById('approveRejectModal')).show();
    }

    function openUploadInvoiceModal(id) {
        $('#uploadInvoiceId').val(id);
        new bootstrap.Modal(document.getElementById('uploadInvoiceModal')).show();
    }

    $('#approvalForm').on('submit', function(e) {
        e.preventDefault();
        let id = $('#modalId').val();
        let formData = $(this).serialize();

        if ($('#financeStatusContainer').is(':visible')) {
             formData = formData.replace('approval=', '') + '&approval=' + $('#finance_status').val();
        }

        $.ajax({
            url: `/pengajuanlabsdansubs/${id}`,
            type: 'POST',
            data: formData,
            success: function(res) {
                bootstrap.Modal.getInstance(document.getElementById('approveRejectModal')).hide();
                Swal.fire({ icon: 'success', title: 'Berhasil', text: res.message, timer: 1500, showConfirmButton: false }).then(() => {
                    window.location.reload();
                });
            },
            error: function(err) {
                Swal.fire('Gagal', err.responseJSON?.message || 'Terjadi kesalahan sistem', 'error');
            }
        });
    });

    $('#uploadInvoiceForm').on('submit', function(e) {
        e.preventDefault();
        let id = $('#uploadInvoiceId').val();
        let formData = new FormData(this);

        $.ajax({
            url: `/pengajuanlabsdansubs/${id}/upload-invoice`,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(res) {
                bootstrap.Modal.getInstance(document.getElementById('uploadInvoiceModal')).hide();
                Swal.fire({ icon: 'success', title: 'Berhasil', text: 'Invoice berhasil diupload', timer: 1500, showConfirmButton: false }).then(() => {
                    window.location.reload();
                });
            }
        });
    });

    function viewDetail(id) { window.location.href = `/pengajuanlabsdansubs/${id}`; }
    function editPengajuan(id) { window.location.href = `/pengajuanlabsdansubs/${id}/edit`; }

    function deletePengajuan(id) {
        if(confirm('Yakin ingin menghapus pengajuan ini?')) {
            $.ajax({
                url: `/pengajuanlabsdansubs/${id}`,
                type: 'DELETE',
                data: { _token: '{{ csrf_token() }}' },
                success: function() {
                    Swal.fire('Terhapus', 'Data berhasil dihapus', 'success').then(() => {
                        window.location.reload();
                    });
                }
            });
        }
    }

    let masterLabTableInit = false;

    function loadMasterLabs() {
        if (masterLabTableInit) {
            $('#masterLabTable').DataTable().ajax.reload(null, false);
            return;
        }

        $('#masterLabTable').DataTable({
            processing: true,
            serverSide: false,
            ajax: {
                url: "{{ route('api.master-labs') }}",
                type: 'GET'
            },
            columns: [
                { data: 'nama_labs' },
                { data: 'merk', render: data => data || '-' },
                {
                    data: 'tipe',
                    render: function(data) {
                        return data === 'subscription' ? 'Subscription' : 'One-Time';
                    }
                },
                {
                    data: 'status',
                    render: function(data) {
                        let color = 'secondary';
                        if(data === 'active') color = 'success';
                        if(data === 'pending') color = 'warning text-dark';
                        if(data === 'expired') color = 'danger';
                        return `<span class="badge bg-${color}">${data}</span>`;
                    }
                },
                {
                    data: 'desc',
                    render: function(data) {
                        if(!data) return '-';
                        return data.length > 20 ? data.substr(0, 20) + '...' : data;
                    }
                },
                {
                    data: 'lab_url',
                    render: function(data) {
                        if(!data) return '-';
                        return `<a href="${data}" target="_blank" class="text-primary">Link</a>`;
                    }
                },
                { data: 'access_code', render: data => data || '-' },
                {
                    data: null,
                    render: function(data, type, row) {
                        let start = row.start_date ? moment(row.start_date).format('DD/MM/YYYY') : '-';
                        let end = row.end_date ? moment(row.end_date).format('DD/MM/YYYY') : '-';
                        if(start === '-' && end === '-') return '-';
                        return `${start} - <br>${end}`; // Dibuat turun ke bawah juga untuk tanggal
                    }
                },
                { data: 'mata_uang', render: data => data || '-' },
                {
                    data: 'harga',
                    render: function(data) {
                        return data ? new Intl.NumberFormat('id-ID').format(data) : '0';
                    }
                },
                {
                    data: 'kurs',
                    render: function(data) {
                        return data ? new Intl.NumberFormat('id-ID').format(data) : '0';
                    }
                },
                {
                    data: 'harga_rupiah',
                    render: function(data) {
                        return data ? 'Rp ' + new Intl.NumberFormat('id-ID').format(data) : '-';
                    }
                },
                {
                    data: 'materis',
                    render: function(data) {
                        if(!data || data.length === 0) return '<span class="text-muted" style="font-size:0.8rem">Belum terhubung</span>';
                        return data.map(m => `<div class="mb-1"><span class="badge bg-light text-dark border text-wrap text-start" style="line-height: 1.4;">${m.nama_materi}</span></div>`).join('');
                    }
                },
                {
                    data: null,
                    render: function(data, type, row) {
                        return `
                            <button class="btn btn-sm btn-primary" onclick='openEditMasterLabModal(${JSON.stringify(row).replace(/'/g, "&#39;")})'>
                                Edit
                            </button>
                        `;
                    }
                }
            ],
            order: [[ 3, "asc" ]]
        });

        masterLabTableInit = true;
    }

    function openEditMasterLabModal(lab) {
        $('#edit_lab_id').val(lab.id);
        $('#edit_nama_labs').val(lab.nama_labs);
        $('#edit_merk').val(lab.merk);
        $('#edit_tipe').val(lab.tipe).trigger('change');
        $('#edit_duration_minutes').val(lab.duration_minutes);
        $('#edit_status').val(lab.status);
        $('#edit_deskripsi').val(lab.desc);
        $('#edit_url_labs').val(lab.lab_url);
        $('#edit_kode_akses').val(lab.access_code);

        let startDate = lab.start_date ? lab.start_date.split(' ')[0] : '';
        let endDate = lab.end_date ? lab.end_date.split(' ')[0] : '';

        $('#edit_tanggal_mulai').val(startDate);
        $('#edit_tanggal_berakhir').val(endDate);
        $('#edit_mata_uang').val(lab.mata_uang || 'Dollar');
        $('#edit_nominal_harga_asli').val(lab.harga);
        $('#edit_kurs').val(lab.kurs);

        calculateEstimasiRupiah();

        let selectedMateriIds = [];
        if (lab.materis && lab.materis.length > 0) {
            selectedMateriIds = lab.materis.map(m => m.id);
        }
        $('#edit_materi').val(selectedMateriIds).trigger('change');

        new bootstrap.Modal(document.getElementById('editMasterLabModal')).show();
    }

    // Submit Edit Master Lab
    $('#editMasterLabForm').on('submit', function(e) {
        e.preventDefault();
        let id = $('#edit_lab_id').val();
        let formData = $(this).serialize();

        $.ajax({
            url: `/api/master-labs/${id}`,
            type: 'PUT',
            data: formData,
            success: function(res) {
                bootstrap.Modal.getInstance(document.getElementById('editMasterLabModal')).hide();
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: 'Data lab berhasil diperbarui',
                    timer: 1500,
                    showConfirmButton: false
                });
                $('#masterLabTable').DataTable().ajax.reload(null, false);
            },
            error: function(err) {
                Swal.fire('Gagal', 'Terjadi kesalahan saat menyimpan data', 'error');
            }
        });
    });
</script>
@endpush
@endsection
