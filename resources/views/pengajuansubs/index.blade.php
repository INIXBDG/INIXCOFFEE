@extends('layouts.app')
@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12">

            @php
                $userJabatan = auth()->user()->karyawan->jabatan ?? '';
                $isTeknis = in_array($userJabatan, ['Technical Support', 'Koordinator ITSM']);
                $bolehMengajukan = in_array($userJabatan, ['Instruktur', 'Education Manager', 'Koordinator ITSM']);
            @endphp

            @if($isTeknis)
            <ul class="nav nav-tabs mb-4" id="subsTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active fw-bold" id="pengajuan-tab" data-bs-toggle="tab" data-bs-target="#pengajuan-pane" type="button" role="tab">
                        Daftar Pengajuan
                    </button>
                </li>

                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-bold" id="kelola-tab" data-bs-toggle="tab" data-bs-target="#kelola-pane" type="button" role="tab" onclick="loadMasterSubs()">
                        Kelola Subscription
                    </button>
                </li>
            </ul>
            @endif

            <div class="tab-content" id="subsTabsContent">

                <div class="tab-pane fade show active" id="pengajuan-pane" role="tabpanel">
                    @if($bolehMengajukan)
                        <div class="d-flex justify-content-end">
                            @if (isset($tracking) && $tracking == 'tutup')
                                <button class="btn btn-md btn-secondary mx-4" disabled title="Selesaikan pengajuan sebelumnya terlebih dahulu">
                                    <img src="{{ asset('icon/plus.svg') }}" width="30px"> Permintaan Subscription
                                </button>
                            @else
                                <a href="{{ route('pengajuansubs.create') }}" class="btn btn-md click-primary mx-4" data-toggle="tooltip" title="Ajukan Subscription">
                                    <img src="{{ asset('icon/plus.svg') }}" width="30px"> Permintaan Subscription
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
                        <div class="card m-4 shadow-sm border-0">
                            <div class="card-body">
                                <div class="row align-items-end">
                                    <div class="col-md-5">
                                        <label class="form-label fw-bold text-secondary small">Tahun</label>
                                        <select id="tahun" class="form-select">
                                            @for ($y = 2023; $y <= now()->year + 1; $y++)
                                                <option value="{{ $y }}" {{ $y == now()->year ? 'selected' : '' }}>{{ $y }}</option>
                                            @endfor
                                        </select>
                                    </div>
                                    <div class="col-md-5">
                                        <label class="form-label fw-bold text-secondary small">Bulan</label>
                                        <select id="bulan" class="form-select">
                                            @foreach (range(1, 12) as $m)
                                                <option value="{{ $m }}" {{ $m == now()->month ? 'selected' : '' }}>
                                                    {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2 mt-3 mt-md-0">
                                        <button onclick="loadAllTables()" class="btn click-primary w-100 d-flex align-items-center justify-content-center fw-bold" style="height: 38px; border: none;">
                                            <i class="fa-solid fa-magnifying-glass me-2"></i> Cari
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="card m-4">
                        <div class="card-body table-responsive">
                            <h3 class="card-title text-center my-1">Data Pengajuan Subs</h3>
                            <table class="table table-striped" id="pengajuanSubsTable">
                                <thead>
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Pengaju</th>
                                        <th>Divisi</th>
                                        <th>Jabatan</th>
                                        <th>Kategori</th>
                                        <th>Nama Subs</th>
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
                            <h3 class="card-title text-center my-1">Data Permintaan Subs Existing</h3>
                            <table class="table table-striped" id="pengajuanExistingTable">
                                <thead>
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Pengaju</th>
                                        <th>Divisi</th>
                                        <th>Jabatan</th>
                                        <th>Kategori</th>
                                        <th>Nama Subs</th>
                                        <th>Status Akhir</th>
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
                            <h3 class="card-title text-center my-1">Data Riwayat Pengajuan Selesai</h3>
                            <table class="table table-striped" id="pengajuanSelesaiTable">
                                <thead>
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Pengaju</th>
                                        <th>Divisi</th>
                                        <th>Jabatan</th>
                                        <th>Kategori</th>
                                        <th>Nama Subs</th>
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
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h3 class="card-title my-1">Data Subscription</h3>
                                <button class="btn btn-primary btn-md click-primary" onclick="openAddMasterSubsModal()">
                                    <img src="{{ asset('icon/plus.svg') }}" width="20px" class="me-1"> Tambah Subscription
                                </button>
                            </div>
                            <table class="table table-striped text-nowrap" id="masterSubsTable" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>Nama</th>
                                        <th>Vendor</th>
                                        <th>Tipe</th>
                                        <th>Status</th>
                                        <th>Deskripsi</th>
                                        <th>Tanggal Aktif</th>
                                        <th>Mata Uang</th>
                                        <th>Harga Asli</th>
                                        <th>Kurs</th>
                                        <th>Estimasi</th>
                                        <th>Materi Terhubung</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Modal Tambah Master Subs -->
                    <div class="modal fade" id="addMasterSubsModal" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title fw-bold">Tambah Data Subscription</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <form id="addMasterSubsForm">
                                    @csrf
                                    <div class="modal-body">
                                        <div class="mb-3 row">
                                            <label class="col-sm-4 col-form-label">Nama Subscription <span class="text-danger">*</span></label>
                                            <div class="col-sm-8">
                                                <input type="text" class="form-control" name="nama_subs" required>
                                            </div>
                                        </div>

                                        <div class="mb-3 row">
                                            <label class="col-sm-4 col-form-label">Vendor / Merk</label>
                                            <div class="col-sm-8">
                                                <input type="text" class="form-control" name="merk">
                                            </div>
                                        </div>

                                        <div class="mb-3 row">
                                            <label class="col-sm-4 col-form-label">Tipe Aset</label>
                                            <div class="col-sm-8">
                                                <select class="form-select" name="tipe" id="add_tipe">
                                                    <option value="subscription">Subscription (Berlangganan)</option>
                                                    <option value="one-time">One-Time</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="mb-3 row">
                                            <label class="col-sm-4 col-form-label">Status</label>
                                            <div class="col-sm-8">
                                                <select class="form-select" name="status">
                                                    <option value="active">Active</option>
                                                    <option value="pending">Pending</option>
                                                    <option value="expired">Expired</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="mb-3 row">
                                            <label class="col-sm-4 col-form-label">Deskripsi</label>
                                            <div class="col-sm-8">
                                                <textarea class="form-control" name="desc" rows="3"></textarea>
                                            </div>
                                        </div>

                                        <div class="mb-3 row">
                                            <label class="col-sm-4 col-form-label">Tanggal Mulai</label>
                                            <div class="col-sm-8">
                                                <input type="date" class="form-control" name="start_date">
                                            </div>
                                        </div>

                                        <div class="mb-3 row">
                                            <label class="col-sm-4 col-form-label">Tanggal Berakhir</label>
                                            <div class="col-sm-8">
                                                <input type="date" class="form-control" name="end_date">
                                            </div>
                                        </div>

                                        <div class="mb-3 row">
                                            <label class="col-sm-4 col-form-label">Mata Uang</label>
                                            <div class="col-sm-8">
                                                <select class="form-select" name="mata_uang" id="add_mata_uang">
                                                    <option value="Dollar">Dollar</option>
                                                    <option value="Rupiah">Rupiah</option>
                                                    <option value="Euro">Euro</option>
                                                    <option value="Poundsterling">Poundsterling</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="mb-3 row">
                                            <label class="col-sm-4 col-form-label">Nominal Harga Asli</label>
                                            <div class="col-sm-8">
                                                <input type="number" step="0.01" class="form-control calculate-harga-add" name="harga" id="add_harga">
                                            </div>
                                        </div>

                                        <div class="mb-3 row">
                                            <label class="col-sm-4 col-form-label">Kurs (Rate)</label>
                                            <div class="col-sm-8">
                                                <input type="number" step="0.01" class="form-control calculate-harga-add" name="kurs" id="add_kurs">
                                            </div>
                                        </div>

                                        <div class="mb-3 row">
                                            <label class="col-sm-4 col-form-label">Estimasi Rupiah</label>
                                            <div class="col-sm-8">
                                                <div class="input-group">
                                                    <span class="input-group-text bg-light">Rp.</span>
                                                    <input type="number" class="form-control bg-light" name="harga_rupiah" id="add_harga_rupiah" readonly>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="mb-3 row">
                                            <label class="col-sm-4 col-form-label">Terhubung ke Materi</label>
                                            <div class="col-sm-8">
                                                <select class="form-select" name="materi_ids[]" id="add_materi" multiple="multiple" style="width: 100%;">
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
                                        <button type="submit" class="btn btn-dark" style="background-color: #1a2a40;">Simpan Subscription</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Edit Master Subs -->
                    <div class="modal fade" id="editMasterSubsModal" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title fw-bold">Edit Data Teknis Subscription</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <form id="editMasterSubsForm">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="id" id="edit_subs_id">

                                    <div class="modal-body">
                                        <div class="mb-3 row">
                                            <label class="col-sm-4 col-form-label">Nama Subscription <span class="text-danger">*</span></label>
                                            <div class="col-sm-8">
                                                <input type="text" class="form-control" name="nama_subs" id="edit_nama_subs" required>
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
                                                <textarea class="form-control" name="desc" id="edit_deskripsi" rows="3"></textarea>
                                            </div>
                                        </div>

                                        <div class="mb-3 row">
                                            <label class="col-sm-4 col-form-label">Tanggal Mulai</label>
                                            <div class="col-sm-8">
                                                <input type="date" class="form-control" name="start_date" id="edit_tanggal_mulai">
                                            </div>
                                        </div>

                                        <div class="mb-3 row">
                                            <label class="col-sm-4 col-form-label">Tanggal Berakhir</label>
                                            <div class="col-sm-8">
                                                <input type="date" class="form-control" name="end_date" id="edit_tanggal_berakhir">
                                            </div>
                                        </div>

                                        <div class="mb-3 row">
                                            <label class="col-sm-4 col-form-label">Mata Uang</label>
                                            <div class="col-sm-8">
                                                <select class="form-select" name="mata_uang" id="edit_mata_uang">
                                                    <option value="Dollar">Dollar</option>
                                                    <option value="Rupiah">Rupiah</option>
                                                    <option value="Euro">Euro</option>
                                                    <option value="Poundsterling">Poundsterling</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="mb-3 row">
                                            <label class="col-sm-4 col-form-label">Nominal Harga Asli</label>
                                            <div class="col-sm-8">
                                                <input type="number" step="0.01" class="form-control calculate-harga" name="harga" id="edit_nominal_harga_asli">
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
        loadAllTables();

        $('.calculate-harga').on('input', calculateEstimasiRupiah);
        $('#edit_mata_uang').on('change', calculateEstimasiRupiah);

        $('.calculate-harga-add').on('input', calculateEstimasiRupiahAdd);
        $('#add_mata_uang').on('change', calculateEstimasiRupiahAdd);

        $('#edit_materi').select2({
            theme: 'bootstrap-5',
            placeholder: "Pilih Materi Terkait...",
            allowClear: true,
            dropdownParent: $('#editMasterSubsModal')
        });

        $('#add_materi').select2({
            theme: 'bootstrap-5',
            placeholder: "Pilih Materi Terkait...",
            allowClear: true,
            dropdownParent: $('#addMasterSubsModal')
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

    function calculateEstimasiRupiahAdd() {
        let mataUang = $('#add_mata_uang').val();
        let nominal = parseFloat($('#add_harga').val()) || 0;

        if (mataUang === 'Rupiah') {
            $('#add_kurs').val(1).prop('readonly', true);
            $('#add_harga_rupiah').val(nominal);
        } else {
            $('#add_kurs').prop('readonly', false);
            let kurs = parseFloat($('#add_kurs').val()) || 0;
            $('#add_harga_rupiah').val(nominal * kurs);
        }
    }

    function loadAllTables() {
        let month = $('#bulan').length ? $('#bulan').val() : (new Date().getMonth() + 1);
        let year  = $('#tahun').length ? $('#tahun').val() : new Date().getFullYear();

        $.ajax({
            url: `/getPengajuanSubs/${month}/${year}`,
            type: "GET",
            success: function(res) {
                if (res.success && res.data) {
                    let rowsAktif = '';
                    let rowsExisting = '';
                    let rowsSelesai = '';

                    $.each(res.data, function(index, item) {
                        let trackTexts = (item.tracking || []).map(t => t.tracking || '');
                        let lastTrack = trackTexts.length > 0 ? trackTexts[trackTexts.length - 1] : '';

                        let isSelesai = lastTrack && (lastTrack.includes('Selesai') || lastTrack.includes('Siap Digunakan'));

                        let isExistingApproved = item.jenis_transaksi === 'existing' &&
                                                 trackTexts.some(t => t && t.toLowerCase().includes('telah disetujui oleh koordinator itsm dan lihat akses nya di detail'));

                        if (isExistingApproved) {
                            rowsExisting += renderRowSelesai(item, lastTrack);
                        } else if (isSelesai) {
                            rowsSelesai += renderRowSelesai(item, lastTrack);
                        } else {
                            rowsAktif += renderRow(item, lastTrack);
                        }
                    });

                    if ($.fn.DataTable.isDataTable('#pengajuanSubsTable')) $('#pengajuanSubsTable').DataTable().destroy();
                    $("#pengajuanSubsTable tbody").html(rowsAktif);
                    $('#pengajuanSubsTable').DataTable({
                        "order": [[ 0, "desc" ]],
                        "language": { "emptyTable": "Tidak ada pengajuan aktif saat ini." }
                    });

                    if ($.fn.DataTable.isDataTable('#pengajuanExistingTable')) $('#pengajuanExistingTable').DataTable().destroy();
                    $("#pengajuanExistingTable tbody").html(rowsExisting);
                    $('#pengajuanExistingTable').DataTable({
                        "order": [[ 0, "desc" ]],
                        "language": { "emptyTable": "Belum ada pengajuan existing yang disetujui." }
                    });

                    if ($.fn.DataTable.isDataTable('#pengajuanSelesaiTable')) $('#pengajuanSelesaiTable').DataTable().destroy();
                    $("#pengajuanSelesaiTable tbody").html(rowsSelesai);
                    $('#pengajuanSelesaiTable').DataTable({
                        "order": [[ 0, "desc" ]],
                        "language": { "emptyTable": "Belum ada riwayat selesai." }
                    });
                }
            },
            error: function() {
                Swal.fire('Error', 'Gagal mengambil data dari server.', 'error');
            }
        });
    }

    function renderRow(item, status) {
        let kategori = item.jenis_transaksi === 'baru' ? '<span class="badge bg-info">Pengadaan Baru</span>' :
                      (item.jenis_transaksi === 'pembaharuan' ? '<span class="badge bg-warning text-dark">Pembaharuan</span>' :
                      '<span class="badge bg-success">Subs Existing</span>');

        let subsName = item.subs ? item.subs.nama_subs : '-';
        let rkmInfo = item.rkm ? `${item.rkm.materi?.nama_materi ?? '-'} <br><small class="text-muted">(${item.rkm.perusahaan?.nama_perusahaan ?? '-'})</small>` : '-';
        let btns = generateButtons(item, status);

        return `
            <tr>
                <td>${moment(item.created_at).format('DD/MM/YYYY')}</td>
                <td>${item.karyawan?.nama_lengkap ?? '-'}</td>
                <td>${item.karyawan?.divisi ?? '-'}</td>
                <td>${item.karyawan?.jabatan ?? '-'}</td>
                <td>${kategori}</td>
                <td>${subsName}</td>
                <td>${formatStatus(status)}</td>
                <td>${rkmInfo}</td>
                <td>${btns}</td>
            </tr>
        `;
    }

    function renderRowSelesai(item, status) {
        let kategori = item.jenis_transaksi === 'baru' ? '<span class="badge bg-danger">Baru</span>' :
                      (item.jenis_transaksi === 'pembaharuan' ? '<span class="badge bg-warning text-dark">Pembaharuan</span>' :
                      '<span class="badge bg-success">Existing</span>');

        let subsName = item.subs ? item.subs.nama_subs : '-';
        let rkmInfo = item.rkm ? (item.rkm.materi?.nama_materi ?? '-') : '-';

        let invoiceBtn = (item.jenis_transaksi === 'baru' || item.jenis_transaksi === 'pembaharuan') ? invoiceAction(item.id, item.invoice, item) : '';

        let btns = `
            <div class="dropdown">
                <button class="btn btn-sm btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">Aksi</button>
                <ul class="dropdown-menu shadow">
                    <li><button class="dropdown-item" onclick="viewDetail(${item.id})"><img src="{{ asset('icon/clipboard-primary.svg') }}" width="16" class="me-1"> Detail</button></li>
                    ${invoiceBtn}
                </ul>
            </div>`;

        return `
            <tr>
                <td>${moment(item.created_at).format('DD/MM/YYYY')}</td>
                <td>${item.karyawan?.nama_lengkap ?? '-'}</td>
                <td>${item.karyawan?.divisi ?? '-'}</td>
                <td>${item.karyawan?.jabatan ?? '-'}</td>
                <td>${kategori}</td>
                <td>${subsName}</td>
                <td>${formatStatus(status)}</td>
                <td>${rkmInfo}</td>
                <td>${btns}</td>
            </tr>
        `;
    }

    function generateButtons(item, status) {
        let isOwner = (item.karyawan && item.karyawan.id == userId);
        let btns = `<div class="dropdown">
            <button class="btn btn-sm btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">Aksi</button>
            <ul class="dropdown-menu shadow">
                <li><button class="dropdown-item" onclick="viewDetail(${item.id})"><img src="{{ asset('icon/clipboard-primary.svg') }}" width="16" class="me-1"> Detail</button></li>`;

        let statusLower = status ? status.toLowerCase() : '';

        if (isOwner && !statusLower.includes('selesai') && !statusLower.includes('siap digunakan')) {
            btns += `<li><button class="dropdown-item text-danger" onclick="deletePengajuan(${item.id})"><img src="{{ asset('icon/trash-danger.svg') }}" width="16" class="me-1"> Batalkan Pengajuan</button></li>`;
        }

        let canApprove = false;

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

        if ((userRole === 'Finance & Accounting' || userRole === 'Finance &amp; Accounting') && (item.jenis_transaksi === 'baru' || item.jenis_transaksi === 'pembaharuan')) {
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

             if (financeStatuses.some(finStatus => statusLower.includes(finStatus))) {
                  btns += `<li><button class="dropdown-item text-warning" onclick="openApproveRejectModal(${item.id}, 'finance-update')"><img src="{{ asset('icon/edit-warning.svg') }}" width="16" class="me-1"> Update Pencairan</button></li>`;
              }
        }

        if (item.jenis_transaksi === 'baru' || item.jenis_transaksi === 'pembaharuan') {
            btns += invoiceAction(item.id, item.invoice, item);
        }

        btns += `</ul></div>`;
        return btns;
    }

    function invoiceAction(id, invoice, item) {
        let dataLengkap = item.subs && item.subs.harga;
        if (!dataLengkap) {
            return `<li><button class="dropdown-item text-muted" disabled><img src="{{ asset('icon/upload.svg') }}" width="16" class="me-1"> Upload Invoice (Tunggu Data Teknis)</button></li>`;
        }
        if (invoice) {
            return `<li><a class="dropdown-item" href="/storage/pengajuansubs/${invoice}" target="_blank"><img src="{{ asset('icon/eye.svg') }}" width="16" class="me-1"> Lihat Invoice</a></li>`;
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
            url: `/pengajuansubs/${id}`,
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
            url: `/pengajuansubs/${id}/upload-invoice`,
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

    function viewDetail(id) { window.location.href = `/pengajuansubs/${id}`; }
    function editPengajuan(id) { window.location.href = `/pengajuansubs/${id}/edit`; }

    function deletePengajuan(id) {
        if(confirm('Yakin ingin menghapus pengajuan ini?')) {
            $.ajax({
                url: `/pengajuansubs/${id}`,
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

    let masterSubsTableInit = false;

    function loadMasterSubs() {
        if (masterSubsTableInit) {
            $('#masterSubsTable').DataTable().ajax.reload(null, false);
            return;
        }

        $('#masterSubsTable').DataTable({
            processing: true,
            serverSide: false,
            ajax: {
                url: "{{ route('api.master-subs') }}",
                type: 'GET'
            },
            columns: [
                { data: 'nama_subs' },
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
                    data: null,
                    render: function(data, type, row) {
                        let start = row.start_date ? moment(row.start_date).format('DD/MM/YYYY') : '-';
                        let end = row.end_date ? moment(row.end_date).format('DD/MM/YYYY') : '-';
                        if(start === '-' && end === '-') return '-';
                        return `${start} - <br>${end}`;
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
                            <div class="d-flex">
                                <button class="btn btn-sm btn-primary me-1" onclick='openEditMasterSubsModal(${JSON.stringify(row).replace(/'/g, "&#39;")})'>
                                    Edit
                                </button>
                                <button class="btn btn-sm btn-success" onclick="renewSubs(${row.id}, '${row.nama_subs}')" title="Perbarui/Perpanjang Subs">
                                    Perbarui
                                </button>
                            </div>
                        `;
                    }
                }
            ],
            order: [[ 3, "asc" ]]
        });

        masterSubsTableInit = true;
    }

    function openAddMasterSubsModal() {
        $('#addMasterSubsForm')[0].reset();
        $('#add_materi').val([]).trigger('change');
        $('#add_harga_rupiah').val('');
        new bootstrap.Modal(document.getElementById('addMasterSubsModal')).show();
    }

    $('#addMasterSubsForm').on('submit', function(e) {
        e.preventDefault();
        let formData = $(this).serialize();

        $.ajax({
            url: "{{ route('api.master-subs.store') }}",
            type: 'POST',
            data: formData,
            success: function(res) {
                bootstrap.Modal.getInstance(document.getElementById('addMasterSubsModal')).hide();
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: 'Subscription master berhasil ditambahkan',
                    timer: 1500,
                    showConfirmButton: false
                });
                $('#masterSubsTable').DataTable().ajax.reload(null, false);
            },
            error: function(err) {
                Swal.fire('Gagal', 'Terjadi kesalahan saat menyimpan data', 'error');
            }
        });
    });

    function openEditMasterSubsModal(subs) {
        $('#edit_subs_id').val(subs.id);
        $('#edit_nama_subs').val(subs.nama_subs);
        $('#edit_merk').val(subs.merk);
        $('#edit_tipe').val(subs.tipe).trigger('change');
        $('#edit_status').val(subs.status);
        $('#edit_deskripsi').val(subs.desc);

        let startDate = subs.start_date ? subs.start_date.split(' ')[0] : '';
        let endDate = subs.end_date ? subs.end_date.split(' ')[0] : '';

        $('#edit_tanggal_mulai').val(startDate);
        $('#edit_tanggal_berakhir').val(endDate);
        $('#edit_mata_uang').val(subs.mata_uang || 'Dollar');
        $('#edit_nominal_harga_asli').val(subs.harga);
        $('#edit_kurs').val(subs.kurs);

        calculateEstimasiRupiah();

        let selectedMateriIds = [];
        if (subs.materis && subs.materis.length > 0) {
            selectedMateriIds = subs.materis.map(m => m.id);
        }
        $('#edit_materi').val(selectedMateriIds).trigger('change');

        new bootstrap.Modal(document.getElementById('editMasterSubsModal')).show();
    }

    $('#editMasterSubsForm').on('submit', function(e) {
        e.preventDefault();
        let id = $('#edit_subs_id').val();
        let formData = $(this).serialize();

        $.ajax({
            url: `/api/master-subs/${id}`,
            type: 'PUT',
            data: formData,
            success: function(res) {
                bootstrap.Modal.getInstance(document.getElementById('editMasterSubsModal')).hide();
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: 'Data subscription berhasil diperbarui',
                    timer: 1500,
                    showConfirmButton: false
                });
                $('#masterSubsTable').DataTable().ajax.reload(null, false);
            },
            error: function(err) {
                Swal.fire('Gagal', 'Terjadi kesalahan saat menyimpan data', 'error');
            }
        });
    });

    function renewSubs(id, namaSubs) {
        Swal.fire({
            title: 'Perbarui Subscription?',
            text: `Buat pengajuan perpanjangan otomatis untuk subscription: ${namaSubs}`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Buat Pengajuan',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/api/master-subs/${id}/renew`,
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(res) {
                        Swal.fire('Berhasil!', res.message, 'success').then(() => {
                            window.location.reload();
                        });
                    },
                    error: function(err) {
                        Swal.fire('Gagal', err.responseJSON?.message || 'Terjadi kesalahan sistem', 'error');
                    }
                });
            }
        });
    }

</script>
@endpush
@endsection
