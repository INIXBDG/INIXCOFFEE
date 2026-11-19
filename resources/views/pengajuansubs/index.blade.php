@extends('layouts.app')
@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12">

            @php
                $userJabatan = auth()->user()->karyawan->jabatan ?? '';
                $isTeknis = in_array($userJabatan, ['Technical Support', 'Koordinator ITSM']);
            @endphp

            @if($isTeknis)
            <ul class="nav nav-tabs mb-4" id="subsTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active fw-bold" id="pengajuan-tab" data-bs-toggle="tab" data-bs-target="#pengajuan-pane" type="button" role="tab">
                        Daftar Permintaan & Tracking Penggunaan
                    </button>
                </li>

                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-bold text-primary" id="kelola-tab" data-bs-toggle="tab" data-bs-target="#kelola-pane" type="button" role="tab" onclick="loadMasterSubs()">
                        Management Master Subscription
                    </button>
                </li>
            </ul>
            @endif

            <div class="tab-content" id="subsTabsContent">

                <div class="tab-pane fade show active" id="pengajuan-pane" role="tabpanel">
                    <div class="d-flex justify-content-end">
                        <a href="{{ route('pengajuansubs.create') }}" class="btn btn-md click-primary mx-4" data-toggle="tooltip" title="Ajukan Permintaan Subscription">
                            <img src="{{ asset('icon/plus.svg') }}" width="30px"> Permintaan Subscription
                        </a>
                    </div>

                    {{-- MODAL APPROVE / REJECT --}}
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
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                        <button type="submit" class="btn btn-primary">Simpan</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    {{-- MODAL UPLOAD INVOICE --}}
                    <div class="modal fade" id="uploadInvoiceModal" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog">
                            <form id="uploadInvoiceForm" enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" id="uploadInvoiceId">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Upload Invoice Subscription (Tim ITSM)</h5>
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

                    @if (in_array($userJabatan, ['Finance & Accounting', 'GM', 'Koordinator ITSM', 'Technical Support', 'Education Manager', 'SPV Sales']))
                        <div class="card m-4">
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
                                    <button onclick="loadAllTables()" class="btn click-primary w-100" style="margin-top: 32px">Cari Data</button>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- TABEL 1: ANTREAN PERMINTAAN & PENGADAAN SUBSCRIPTION --}}
                    <div class="card m-4">
                        <div class="card-body table-responsive">
                            <h3 class="card-title text-center my-1">Antrean Permintaan & Pengadaan Subscription</h3>
                            <table class="table table-striped" id="pengajuanSubsTable">
                                <thead>
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Peminta</th>
                                        <th>Divisi</th>
                                        <th>Jabatan</th>
                                        <th>Status Penanganan</th>
                                        <th>Nama Subscription & Jumlah Akun</th>
                                        <th>Status Tracking Terkini</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>

                    {{-- TABEL 2: TRACKING PENGGUNAAN SUBSCRIPTION AKTIF --}}
                    <div class="card m-4">
                        <div class="card-body table-responsive">
                            <h3 class="card-title text-center my-1">Tracking Penggunaan Subscription (Permintaan Akses & Assign Aktif)</h3>
                            <table class="table table-striped" id="pengajuanExistingTable">
                                <thead>
                                    <tr>
                                        <th>Tgl Pencatatan</th>
                                        <th>Pengguna Subscription</th>
                                        <th>Divisi / Jabatan</th>
                                        <th>Nama Subscription & Identitas Akun</th>
                                        <th>Masa Aktif Subscription</th>
                                        <th>Status Penggunaan</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>

                    {{-- TABEL 3: RIWAYAT PENGADAAN SUBSCRIPTION SELESAI / DITOLAK / DI-UNASSIGN --}}
                    <div class="card m-4">
                        <div class="card-body table-responsive">
                            <h3 class="card-title text-center my-1">Riwayat Pengadaan Subscription Selesai / Ditolak</h3>
                            <table class="table table-striped" id="pengajuanSelesaiTable">
                                <thead>
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Peminta / PIC ITSM</th>
                                        <th>Divisi</th>
                                        <th>Kategori</th>
                                        <th>Nama Subscription & Qty</th>
                                        <th>Status Akhir</th>
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
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h4 class="card-title mb-0 fw-bold">Master Data Subscription</h4>
                                <button type="button" class="btn btn-primary fw-bold" onclick="openAddMasterSubsModal()">
                                    + Tambah Subscription yang Sudah Berjalan
                                </button>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-striped align-middle" id="masterSubsTable" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th width="22%">Nama Subscription & Vendor</th>
                                            <th width="13%">Tipe & Status</th>
                                            <th width="25%">Sedang Digunakan Oleh</th>
                                            <th width="18%">Akses & Kredensial</th>
                                            <th width="15%">Masa Aktif & Biaya</th>
                                            <th width="7%" class="text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    {{-- MODAL 1: TAMBAH SUBSCRIPTION YANG SUDAH BERJALAN --}}
                    <div class="modal fade" id="addMasterSubsModal" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title fw-bold">Tambah Data Subscription yang Sudah Berjalan</h5>
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
                                            <label class="col-sm-4 col-form-label">Identitas Akun / Email Terdaftar</label>
                                            <div class="col-sm-8">
                                                <input type="text" class="form-control" name="akun_identitas">
                                                <small class="text-muted">pembeda untuk subscription yang sama.</small>
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
                                            <label class="col-sm-4 col-form-label">Status Saat Ini</label>
                                            <div class="col-sm-8">
                                                <select class="form-select" name="status">
                                                    <option value="active" selected>Active (Sedang Berjalan)</option>
                                                    <option value="pending">Pending</option>
                                                    <option value="expired">Expired</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="mb-3 row">
                                            <label class="col-sm-4 col-form-label">URL Subscription</label>
                                            <div class="col-sm-8">
                                                <input type="text" class="form-control" name="subs_url" placeholder="https://...">
                                            </div>
                                        </div>

                                        <div class="mb-3 row">
                                            <label class="col-sm-4 col-form-label">Kode Akses / Credentials</label>
                                            <div class="col-sm-8">
                                                <input type="text" class="form-control" name="access_code" placeholder="Email / Password / Key">
                                            </div>
                                        </div>

                                        <div class="mb-3 row">
                                            <label class="col-sm-4 col-form-label">Tanggal Mulai & Berakhir</label>
                                            <div class="col-sm-4">
                                                <input type="date" class="form-control" name="start_date" title="Tanggal Mulai">
                                            </div>
                                            <div class="col-sm-4">
                                                <input type="date" class="form-control" name="end_date" title="Tanggal Berakhir">
                                            </div>
                                        </div>

                                        <div class="mb-3 row">
                                            <label class="col-sm-4 col-form-label">Mata Uang & Harga</label>
                                            <div class="col-sm-3">
                                                <select class="form-select" name="mata_uang" id="add_mata_uang">
                                                    <option value="Rupiah">Rupiah</option>
                                                    <option value="Dollar">Dollar</option>
                                                    <option value="Euro">Euro</option>
                                                    <option value="Poundsterling">Poundsterling</option>
                                                </select>
                                            </div>
                                            <div class="col-sm-5">
                                                <input type="number" step="0.01" class="form-control calculate-harga-add" name="harga" id="add_harga" placeholder="Nominal Harga Asli" value="0">
                                            </div>
                                        </div>

                                        <div class="mb-3 row">
                                            <label class="col-sm-4 col-form-label">Kurs & Estimasi (Rp)</label>
                                            <div class="col-sm-3">
                                                <input type="number" step="0.01" class="form-control calculate-harga-add" name="kurs" id="add_kurs" value="1" readonly>
                                            </div>
                                            <div class="col-sm-5">
                                                <input type="number" class="form-control bg-light" name="harga_rupiah" id="add_harga_rupiah" value="0" readonly>
                                            </div>
                                        </div>

                                        <div class="mb-3 row">
                                            <label class="col-sm-4 col-form-label">Deskripsi / Catatan</label>
                                            <div class="col-sm-8">
                                                <textarea class="form-control" name="desc" rows="2"></textarea>
                                            </div>
                                        </div>

                                        <hr>
                                        <h6 class="fw-bold text-primary mb-3">Langsung Assign Karyawan yang Sedang Menggunakan (Opsional)</h6>

                                        <div class="mb-3 row">
                                            <label class="col-sm-4 col-form-label">Pilih Karyawan</label>
                                            <div class="col-sm-8">
                                                <select class="form-select" name="assigned_kode_karyawan[]" id="add_assigned_karyawan" multiple="multiple" style="width: 100%;">
                                                    @if(isset($karyawans))
                                                        @foreach($karyawans as $kar)
                                                            <option value="{{ $kar->kode_karyawan }}">{{ $kar->nama_lengkap }} ({{ $kar->jabatan }} - {{ $kar->divisi }})</option>
                                                        @endforeach
                                                    @endif
                                                </select>
                                                <small class="text-muted">Bisa pilih lebih dari satu karyawan jika subscription dipakai bersama.</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                        <button type="submit" class="btn btn-primary">Simpan Subscription & Assign</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    {{-- MODAL 2: ASSIGN / UNASSIGN PENGGUNA SUBSCRIPTION PADA MASTER SUBSCRIPTION --}}
                    <div class="modal fade" id="assignSubsUserModal" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title fw-bold">Kelola Pengguna Subscription: <span id="assign_subs_title" class="text-primary"></span></h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <form id="assignSubsUserForm">
                                    @csrf
                                    <input type="hidden" id="assign_subs_id">
                                    <div class="modal-body">

                                        {{-- DAFTAR PENGGUNA SAAT INI (UNTUK UNASSIGN) --}}
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Pengguna Aktif Saat Ini</label>
                                            <div id="current_assigned_users_list" class="border rounded p-2 bg-light" style="max-height: 160px; overflow-y: auto;">
                                            </div>
                                        </div>

                                        <hr>

                                        {{-- FORM TAMBAH ASSIGN BARU --}}
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Tambah / Assign Pengguna Baru <span class="text-danger">*</span></label>
                                            <select class="form-select" name="kode_karyawan_list[]" id="assign_kode_karyawan" multiple="multiple" style="width: 100%;" required>
                                                @if(isset($karyawans))
                                                    @foreach($karyawans as $kar)
                                                        <option value="{{ $kar->kode_karyawan }}">{{ $kar->nama_lengkap }} ({{ $kar->jabatan }} - {{ $kar->divisi }})</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Catatan Penggunaan (Opsional)</label>
                                            <textarea class="form-control" name="catatan_assign" rows="2"></textarea>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                        <button type="submit" class="btn btn-primary">Simpan & Berikan Akses</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    {{-- MODAL 3: EDIT MASTER SUBS --}}
                    <div class="modal fade" id="editMasterSubsModal" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title fw-bold">Edit Data Master Subscription</h5>
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
                                            <label class="col-sm-4 col-form-label">Identitas Akun / Email Terdaftar</label>
                                            <div class="col-sm-8">
                                                <input type="text" class="form-control" name="akun_identitas" id="edit_akun_identitas" placeholder="Contoh: claude1@inixindo.id / Seat #1">
                                                <small class="text-muted">Memudahkan TS membedakan antar akun pada layanan subscription yang sama.</small>
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
                                            <label class="col-sm-4 col-form-label">URL Subscription</label>
                                            <div class="col-sm-8">
                                                <input type="text" class="form-control" name="subs_url" id="edit_url_subs">
                                            </div>
                                        </div>

                                        <div class="mb-3 row">
                                            <label class="col-sm-4 col-form-label">Kode Akses / Key</label>
                                            <div class="col-sm-8">
                                                <input type="text" class="form-control" name="access_code" id="edit_kode_akses">
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
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    const userRole = "{{ auth()->user()->karyawan->jabatan ?? '' }}";
    const userKode = "{{ auth()->user()->karyawan->kode_karyawan ?? '' }}";

    $(document).ready(function() {
        loadAllTables();

        $('.calculate-harga').on('input', calculateEstimasiRupiah);
        $('#edit_mata_uang').on('change', calculateEstimasiRupiah);

        $('.calculate-harga-add').on('input', calculateEstimasiRupiahAdd);
        $('#add_mata_uang').on('change', calculateEstimasiRupiahAdd);

        $('#add_assigned_karyawan').select2({
            theme: 'bootstrap-5',
            placeholder: "Pilih Karyawan yang sedang menggunakan...",
            allowClear: true,
            dropdownParent: $('#addMasterSubsModal')
        });

        $('#assign_kode_karyawan').select2({
            theme: 'bootstrap-5',
            placeholder: "Pilih Karyawan...",
            allowClear: true,
            dropdownParent: $('#assignSubsUserModal')
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
                        let allTracksLower = trackTexts.join(' ').toLowerCase();

                        let isDitolak = allTracksLower.includes('ditolak');
                        let isUnassigned = allTracksLower.includes('di-unassign') || allTracksLower.includes('akses dicabut');
                        let isExistingApproved = item.jenis_transaksi === 'existing' &&
                                                 allTracksLower.includes('lihat akses nya di detail') &&
                                                 !isUnassigned;
                        let isBarangSelesai = (item.jenis_transaksi === 'baru' || item.jenis_transaksi === 'pembaharuan') &&
                                              (lastTrack.toLowerCase() === 'selesai' || lastTrack.toLowerCase().includes('pencairan sudah selesai'));

                        if (isExistingApproved) {
                            rowsExisting += renderRowTrackingAkses(item, lastTrack);
                        } else if (isBarangSelesai || isDitolak || isUnassigned) {
                            rowsSelesai += renderRowSelesai(item, lastTrack);
                        } else {
                            rowsAktif += renderRow(item, lastTrack);
                        }
                    });

                    if ($.fn.DataTable.isDataTable('#pengajuanSubsTable')) $('#pengajuanSubsTable').DataTable().destroy();
                    $("#pengajuanSubsTable tbody").html(rowsAktif);
                    $('#pengajuanSubsTable').DataTable({
                        "order": [[ 0, "desc" ]],
                        "language": { "emptyTable": "Tidak ada antrean permintaan subscription saat ini." }
                    });

                    if ($.fn.DataTable.isDataTable('#pengajuanExistingTable')) $('#pengajuanExistingTable').DataTable().destroy();
                    $("#pengajuanExistingTable tbody").html(rowsExisting);
                    $('#pengajuanExistingTable').DataTable({
                        "order": [[ 0, "desc" ]],
                        "language": { "emptyTable": "Belum ada penggunaan akses subscription aktif." }
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
        let handlerInfo = item.subs_snapshot?.handled_by ? `<br><small class="text-muted">PIC ITSM: ${item.subs_snapshot.handled_by}</small>` : '';
        let kategori = item.jenis_transaksi === 'baru' ? `<span class="fw-bold text-dark">Pengadaan Baru</span>${handlerInfo}` :
                      (item.jenis_transaksi === 'pembaharuan' ? `<span class="fw-bold text-dark">Perpanjangan Subscription</span>${handlerInfo}` :
                      `<span class="fw-bold text-dark">Menunggu Eksekusi ITSM</span>`);

        let reqQty = item.subs_snapshot?.qty || item.subs_snapshot?.requested_qty || 1;

        let subsName = item.subs
            ? `<strong>${item.subs.nama_subs}</strong> <span class="fw-bold text-dark">(${reqQty} Akun)</span><br><small class="text-muted">Vendor: ${item.subs.merk || '-'}</small>`
            : '-';
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
                <td>${btns}</td>
            </tr>
        `;
    }

    function renderRowTrackingAkses(item, status) {
        let identitasInfo = '';
        if (item.subs && item.subs.desc && item.subs.desc.startsWith('[Akun:')) {
            let match = item.subs.desc.match(/^\[Akun:\s*(.*?)\]/);
            if (match && match[1]) {
                identitasInfo = `<br><small class="text-dark">Akun: <code>${match[1]}</code></small>`;
            }
        }
        let subsName = item.subs ? `<strong>${item.subs.nama_subs}</strong> <br><small class="text-muted">${item.subs.merk || '-'}</small>${identitasInfo}` : '-';

        let masaAktifSubs = 'Mengikuti Masa Aktif Subscription';
        let statusPakai = '<span class="fw-bold text-dark">Sedang Digunakan</span>';

        if (item.subs && item.subs.end_date) {
            let startSubs = item.subs.start_date ? moment(item.subs.start_date).format('DD MMM YYYY') : '-';
            let endSubs = moment(item.subs.end_date).endOf('day');
            masaAktifSubs = `${startSubs} s/d ${endSubs.format('DD MMM YYYY')}`;
            if (moment().isAfter(endSubs)) {
                statusPakai = '<span class="text-muted">Masa Aktif Subscription Habis</span>';
            }
        }

        let safeUser = (item.karyawan?.nama_lengkap ?? 'Pengguna').replace(/'/g, "\\'");
        let safeSubs = (item.subs?.nama_subs ?? 'Subscription').replace(/'/g, "\\'");

        let unassignOption = '';
        if (userRole === 'Technical Support' || userRole === 'Koordinator ITSM') {
            unassignOption = `
                <li><hr class="dropdown-divider"></li>
                <li>
                    <button class="dropdown-item text-danger fw-bold" onclick="unassignSubsUser(${item.id}, '${safeUser}', '${safeSubs}')">
                        Unassign / Cabut Akses
                    </button>
                </li>`;
        }

        let btns = `
            <div class="dropdown">
                <button class="btn btn-sm btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">Aksi</button>
                <ul class="dropdown-menu shadow">
                    <li><button class="dropdown-item" onclick="viewDetail(${item.id})"><img src="{{ asset('icon/clipboard-primary.svg') }}" width="16" class="me-1"> Lihat Akses & Detail</button></li>
                    ${unassignOption}
                </ul>
            </div>`;

        return `
            <tr>
                <td>${moment(item.created_at).format('DD/MM/YYYY')}</td>
                <td>${item.karyawan?.nama_lengkap ?? '-'}</td>
                <td>${item.karyawan?.divisi ?? '-'} / ${item.karyawan?.jabatan ?? '-'}</td>
                <td>${subsName}</td>
                <td>${masaAktifSubs}</td>
                <td>${statusPakai}</td>
                <td>${btns}</td>
            </tr>
        `;
    }

    function renderRowSelesai(item, status) {
        let handlerInfo = item.subs_snapshot?.handled_by ? `<br><small class="text-muted">PIC ITSM: ${item.subs_snapshot.handled_by}</small>` : '';
        let kategori = item.jenis_transaksi === 'baru' ? '<span class="fw-bold text-dark">Pengadaan Baru</span>' :
                      (item.jenis_transaksi === 'pembaharuan' ? '<span class="fw-bold text-dark">Perpanjangan</span>' :
                      '<span class="fw-bold text-dark">Permintaan Subscription</span>');

        let reqQty = item.subs_snapshot?.qty || item.subs_snapshot?.requested_qty || 1;
        let subsName = item.subs ? `${item.subs.nama_subs} <small class="text-muted">(${reqQty} Akun)</small>` : '-';
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
                <td>${item.karyawan?.nama_lengkap ?? '-'}${handlerInfo}</td>
                <td>${item.karyawan?.divisi ?? '-'}</td>
                <td>${kategori}</td>
                <td>${subsName}</td>
                <td>${formatStatus(status)}</td>
                <td>${btns}</td>
            </tr>
        `;
    }

    function generateButtons(item, status) {
        let isOwner = (item.kode_karyawan === userKode);
        let statusLower = status ? status.toLowerCase() : '';

        let btns = `<div class="dropdown">
            <button class="btn btn-sm btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">Aksi</button>
            <ul class="dropdown-menu shadow">
                <li><button class="dropdown-item" onclick="viewDetail(${item.id})"><img src="{{ asset('icon/clipboard-primary.svg') }}" width="16" class="me-1"> Detail</button></li>`;

        if (isOwner && statusLower.includes('diajukan') && !statusLower.includes('ditolak')) {
            btns += `<li><button class="dropdown-item text-danger" onclick="deletePengajuan(${item.id})"><img src="{{ asset('icon/trash-danger.svg') }}" width="16" class="me-1"> Hapus</button></li>`;
        }

        let canApproveAtasan = false;
        if (userRole === 'Education Manager' && statusLower.includes('ditinjau oleh education manager')) {
            canApproveAtasan = true;
        } else if (userRole === 'SPV Sales' && statusLower.includes('ditinjau oleh spv sales')) {
            canApproveAtasan = true;
        } else if (userRole === 'GM' && statusLower.includes('ditinjau oleh gm')) {
            canApproveAtasan = true;
        }

        if (canApproveAtasan) {
            btns += `
                <li><hr class="dropdown-divider"></li>
                <li><button class="dropdown-item text-success" onclick="openApproveRejectModal(${item.id}, 'approve')"><img src="{{ asset('icon/check-circle.svg') }}" width="16" class="me-1"> Approve (Teruskan ke ITSM)</button></li>
                <li><button class="dropdown-item text-danger" onclick="openApproveRejectModal(${item.id}, 'reject')"><img src="{{ asset('icon/x-circle.svg') }}" width="16" class="me-1"> Reject</button></li>
            `;
        }

        if ((userRole === 'Technical Support' || userRole === 'Koordinator ITSM') && !statusLower.includes('selesai')) {
            btns += `
                <li><hr class="dropdown-divider"></li>
                <li><button class="dropdown-item fw-bold text-primary" onclick="editPengajuan(${item.id})"><img src="{{ asset('icon/edit-warning.svg') }}" width="16" class="me-1"> Proses Subscription (Akses / Pengadaan)</button></li>
                <li><button class="dropdown-item text-danger" onclick="openApproveRejectModal(${item.id}, 'reject')"><img src="{{ asset('icon/x-circle.svg') }}" width="16" class="me-1"> Tolak Permintaan</button></li>
            `;
        }

        if ((item.jenis_transaksi === 'baru' || item.jenis_transaksi === 'pembaharuan') && (userRole === 'Technical Support' || userRole === 'Koordinator ITSM' || userRole === 'Finance & Accounting')) {
            btns += invoiceAction(item.id, item.invoice, item);
        }

        btns += `</ul></div>`;
        return btns;
    }

    function invoiceAction(id, invoice, item) {
        let dataLengkap = item.subs && item.subs.harga;
        if (!dataLengkap) {
            return `<li><button class="dropdown-item text-muted" disabled><img src="{{ asset('icon/upload.svg') }}" width="16" class="me-1"> Upload Invoice (Isi Harga Dulu)</button></li>`;
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

        if (type === 'approve') {
            $('#actionLabel').text('Setujui permintaan subscription ini dan teruskan ke Tim ITSM?');
            $('#modalApproval').val('1');
        } else if (type === 'reject') {
            $('#actionLabel').text('Anda yakin ingin MENOLAK permintaan subscription ini?');
            $('#modalApproval').val('2');
            $('#reasonContainer').removeClass('d-none');
        }
        new bootstrap.Modal(document.getElementById('approveRejectModal')).show();
    }

    function openUploadInvoiceModal(id) {
        $('#uploadInvoiceId').val(id);
        new bootstrap.Modal(document.getElementById('uploadInvoiceModal')).show();
    }

    function openAddMasterSubsModal() {
        $('#addMasterSubsForm')[0].reset();
        $('#add_assigned_karyawan').val([]).trigger('change');
        new bootstrap.Modal(document.getElementById('addMasterSubsModal')).show();
    }

    function openAssignSubsModal(row) {
        $('#assign_subs_id').val(row.id);
        $('#assign_subs_title').text(row.nama_subs);
        $('#assignSubsUserForm')[0].reset();
        $('#assign_kode_karyawan').val([]).trigger('change');

        let listContainer = $('#current_assigned_users_list');
        listContainer.empty();

        if (row.active_users && row.active_users.length > 0) {
            let html = row.active_users.map(u => {
                let safeUser = (u.nama || '').replace(/'/g, "\\'");
                let safeSubs = (row.nama_subs || '').replace(/'/g, "\\'");
                return `
                    <div class="d-flex justify-content-between align-items-center py-1 px-2 mb-1 bg-white border rounded">
                        <div>
                            <div class="fw-bold small text-dark">${u.nama}</div>
                            <small class="text-muted" style="font-size: 0.75rem;">${u.divisi} (${u.periode})</small>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2" style="font-size: 0.75rem;"
                                onclick="unassignSubsUser(${u.pengajuan_id}, '${safeUser}', '${safeSubs}')">
                            Unassign
                        </button>
                    </div>
                `;
            }).join('');
            listContainer.html(html);
        } else {
            listContainer.html('<span class="text-muted small"><em>Belum ada pengguna yang di-assign pada subscription ini.</em></span>');
        }

        new bootstrap.Modal(document.getElementById('assignSubsUserModal')).show();
    }

    function unassignSubsUser(pengajuanId, namaUser, namaSubs) {
        Swal.fire({
            title: 'Unassign Pengguna?',
            text: `Cabut akses penggunaan subscription "${namaSubs}" untuk ${namaUser}?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Ya, Unassign',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/api/master-subs/unassign/${pengajuanId}`,
                    type: 'POST',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function(res) {
                        let modalEl = document.getElementById('assignSubsUserModal');
                        let modalInst = bootstrap.Modal.getInstance(modalEl);
                        if (modalInst) modalInst.hide();

                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: res.message,
                            timer: 1500,
                            showConfirmButton: false
                        });

                        if (masterSubsTableInit) {
                            $('#masterSubsTable').DataTable().ajax.reload(null, false);
                        }
                        loadAllTables();
                    },
                    error: function(err) {
                        Swal.fire('Gagal', err.responseJSON?.message || 'Gagal melakukan unassign.', 'error');
                    }
                });
            }
        });
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
                Swal.fire({ icon: 'success', title: 'Berhasil', text: res.message, timer: 1500, showConfirmButton: false });
                $('#masterSubsTable').DataTable().ajax.reload(null, false);
                loadAllTables();
            },
            error: function(err) {
                Swal.fire('Gagal', err.responseJSON?.message || 'Gagal menyimpan data subscription.', 'error');
            }
        });
    });

    $('#assignSubsUserForm').on('submit', function(e) {
        e.preventDefault();
        let subsId = $('#assign_subs_id').val();
        let formData = $(this).serialize();

        $.ajax({
            url: `/api/master-subs/${subsId}/assign`,
            type: 'POST',
            data: formData,
            success: function(res) {
                bootstrap.Modal.getInstance(document.getElementById('assignSubsUserModal')).hide();
                Swal.fire({ icon: 'success', title: 'Berhasil', text: res.message, timer: 1500, showConfirmButton: false });
                $('#masterSubsTable').DataTable().ajax.reload(null, false);
                loadAllTables();
            },
            error: function(err) {
                Swal.fire('Gagal', err.responseJSON?.message || 'Gagal meng-assign pengguna.', 'error');
            }
        });
    });

    $('#approvalForm').on('submit', function(e) {
        e.preventDefault();
        let id = $('#modalId').val();
        let formData = $(this).serialize();

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
            success: function() {
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
        if(confirm('Yakin ingin menghapus permintaan subscription ini?')) {
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

    function copyAccessCode(text) {
        navigator.clipboard.writeText(text).then(() => {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: 'Kode akses berhasil disalin!',
                showConfirmButton: false,
                timer: 1500
            });
        });
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
            autoWidth: false,
            ajax: {
                url: "{{ route('api.master-subs') }}",
                type: 'GET'
            },
            columns: [
                // 1. Nama Subscription, Identitas Akun & Status Pemakaian
                {
                    data: null,
                    render: function(data, type, row) {
                        let merk = row.merk ? row.merk : 'Tanpa Vendor';
                        let identitasHtml = row.akun_identitas
                            ? `<div class="small text-dark mt-1">Akun: <code>${row.akun_identitas}</code></div>`
                            : (row.clean_desc ? `<div class="small text-muted mt-1">${row.clean_desc}</div>` : '');

                        let activeCount = (row.active_users && row.active_users.length) ? row.active_users.length : 0;
                        let usageIndicator = activeCount > 0
                            ? `<div class="small fw-bold text-dark mt-1">Terpakai: ${activeCount} Akun</div>`
                            : `<div class="small text-muted mt-1">Tersedia (Idle)</div>`;

                        return `<div class="fw-bold text-dark">${row.nama_subs}</div>
                                <small class="text-muted">${merk}</small>
                                ${identitasHtml}
                                ${usageIndicator}`;
                    }
                },
                // 2. Tipe & Status (Teks Bersih Tanpa Badge Warna)
                {
                    data: null,
                    render: function(data, type, row) {
                        let tipeLabel = row.tipe === 'subscription' ? 'Subscription' : 'One-Time';
                        let statusCap = row.status ? row.status.charAt(0).toUpperCase() + row.status.slice(1) : '-';

                        return `<div class="fw-bold text-dark">${statusCap}</div>
                                <small class="text-muted">${tipeLabel}</small>`;
                    }
                },
                // 3. Sedang Digunakan Oleh (Dilengkapi Tombol Unassign Cepat)
                {
                    data: 'active_users',
                    render: function(data, type, row) {
                        if (!data || data.length === 0) {
                            return '<span class="text-muted small"><em>Tersedia (Idle) - Belum di-assign</em></span>';
                        }
                        let safeSubs = (row.nama_subs || '').replace(/'/g, "\\'");
                        return data.map(u => {
                            let safeUser = (u.nama || '').replace(/'/g, "\\'");
                            return `
                                <div class="mb-1 pb-1 border-bottom d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="fw-bold text-dark small">${u.nama}</span><br>
                                        <small class="text-muted" style="font-size: 0.78rem;">${u.divisi} (${u.periode})</small>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-danger py-0 px-1 ms-2" style="font-size: 0.72rem;"
                                            onclick="unassignSubsUser(${u.pengajuan_id}, '${safeUser}', '${safeSubs}')" title="Unassign / Cabut Akses ${u.nama}">
                                        &times; Unassign
                                    </button>
                                </div>
                            `;
                        }).join('');
                    }
                },
                // 4. Akses & Kredensial (Dilengkapi Tombol Copy Kode Akses)
                {
                    data: null,
                    render: function(data, type, row) {
                        let urlHtml = row.subs_url
                            ? `<a href="${row.subs_url}" target="_blank" class="fw-bold text-primary text-decoration-none small">Buka Link Subs ↗</a>`
                            : '<span class="text-muted small">URL: -</span>';

                        let codeHtml = '<div class="text-muted small">Kode: -</div>';
                        if (row.access_code) {
                            let safeCode = row.access_code.replace(/'/g, "\\'");
                            let shortCode = row.access_code.length > 18 ? row.access_code.substr(0, 18) + '...' : row.access_code;
                            codeHtml = `
                                <div class="mt-1 d-flex align-items-center gap-1">
                                    <code class="text-dark bg-light border px-1 rounded small" title="${row.access_code}">${shortCode}</code>
                                    <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-1" style="font-size: 0.72rem;" onclick="copyAccessCode('${safeCode}')" title="Salin Kode Akses">
                                        Salin
                                    </button>
                                </div>`;
                        }

                        return `${urlHtml}${codeHtml}`;
                    }
                },
                // 5. Masa Aktif & Estimasi Biaya
                {
                    data: null,
                    render: function(data, type, row) {
                        let start = row.start_date ? moment(row.start_date).format('DD/MM/YY') : '-';
                        let end = row.end_date ? moment(row.end_date).format('DD/MM/YY') : '-';
                        let periode = (start === '-' && end === '-') ? '<span class="text-muted small">Masa Aktif: -</span>' : `<div class="small fw-bold text-dark">${start} - ${end}</div>`;

                        let harga = row.harga_rupiah && row.harga_rupiah > 0
                            ? `<small class="text-muted">Rp ${new Intl.NumberFormat('id-ID').format(row.harga_rupiah)}</small>`
                            : '<small class="text-muted">Rp -</small>';

                        return `${periode}${harga}`;
                    }
                },
                // 6. Aksi (Dropdown Rapi)
                {
                    data: null,
                    className: 'text-center',
                    render: function(data, type, row) {
                        let safeName = (row.nama_subs || '').replace(/'/g, "\\'");
                        let rowJson = JSON.stringify(row).replace(/'/g, "&#39;");

                        return `
                            <div class="dropdown">
                                <button class="btn btn-sm btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                    Aksi
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow">
                                    <li>
                                        <button class="dropdown-item fw-bold" onclick='openAssignSubsModal(${rowJson})'>
                                            Assign / Unassign Pengguna
                                        </button>
                                    </li>
                                    <li>
                                        <button class="dropdown-item" onclick='openEditMasterSubsModal(${rowJson})'>
                                            Edit Data Subscription
                                        </button>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <button class="dropdown-item text-success fw-bold" onclick="renewSubs(${row.id}, '${safeName}')">
                                            Perbarui / Perpanjang
                                        </button>
                                    </li>
                                </ul>
                            </div>
                        `;
                    }
                }
            ],
            order: [[ 0, "asc" ]]
        });

        masterSubsTableInit = true;
    }

    function openEditMasterSubsModal(sub) {
        $('#edit_subs_id').val(sub.id);
        $('#edit_nama_subs').val(sub.nama_subs);
        $('#edit_akun_identitas').val(sub.akun_identitas || '');
        $('#edit_merk').val(sub.merk);
        $('#edit_tipe').val(sub.tipe);
        $('#edit_status').val(sub.status);
        $('#edit_deskripsi').val(sub.clean_desc !== undefined ? sub.clean_desc : sub.desc);
        $('#edit_url_subs').val(sub.subs_url);
        $('#edit_kode_akses').val(sub.access_code);

        let startDate = sub.start_date ? sub.start_date.split(' ')[0] : '';
        let endDate = sub.end_date ? sub.end_date.split(' ')[0] : '';

        $('#edit_tanggal_mulai').val(startDate);
        $('#edit_tanggal_berakhir').val(endDate);
        $('#edit_mata_uang').val(sub.mata_uang || 'Rupiah');
        $('#edit_nominal_harga_asli').val(sub.harga);
        $('#edit_kurs').val(sub.kurs);

        calculateEstimasiRupiah();

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
            success: function() {
                bootstrap.Modal.getInstance(document.getElementById('editMasterSubsModal')).hide();
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: 'Data Master Subscription berhasil diperbarui',
                    timer: 1500,
                    showConfirmButton: false
                });
                $('#masterSubsTable').DataTable().ajax.reload(null, false);
            },
            error: function() {
                Swal.fire('Gagal', 'Terjadi kesalahan saat menyimpan data', 'error');
            }
        });
    });

    function renewSubs(id, namaSubs) {
        Swal.fire({
            title: 'Perbarui / Perpanjang Subscription?',
            text: `Buat draft perpanjangan untuk subscription: ${namaSubs} atas nama Anda (${userRole})`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Buat Pengajuan',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/api/master-subs/${id}/renew`,
                    type: 'POST',
                    data: { _token: '{{ csrf_token() }}' },
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
