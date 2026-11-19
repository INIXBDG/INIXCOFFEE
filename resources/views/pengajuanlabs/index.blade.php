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
                        Daftar Permintaan & Tracking Penggunaan
                    </button>
                </li>

                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-bold text-primary" id="kelola-tab" data-bs-toggle="tab" data-bs-target="#kelola-pane" type="button" role="tab" onclick="loadMasterLabs()">
                        Management Master Lab
                    </button>
                </li>
            </ul>
            @endif

            <div class="tab-content" id="labTabsContent">

                <div class="tab-pane fade show active" id="pengajuan-pane" role="tabpanel">
                    @if($bolehMengajukan)
                        <div class="d-flex justify-content-end">
                            <a href="{{ route('pengajuanlabsdansubs.create') }}" class="btn btn-md click-primary mx-4" data-toggle="tooltip" title="Ajukan Permintaan Lab">
                                <img src="{{ asset('icon/plus.svg') }}" width="30px"> Permintaan Lab
                            </a>
                        </div>
                    @endif

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
                                        <h5 class="modal-title">Upload Invoice Lab (Tim ITSM)</h5>
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

                    @if (in_array($userJabatan, ['Finance & Accounting', 'GM', 'Koordinator ITSM', 'Technical Support', 'Education Manager']))
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

                    {{-- TABEL 1: ANTREAN PERMINTAAN & PENGADAAN LAB --}}
                    <div class="card m-4">
                        <div class="card-body table-responsive">
                            <h3 class="card-title text-center my-1">Antrean Permintaan & Pengadaan Lab</h3>
                            <table class="table table-striped" id="pengajuanLabSubsTable">
                                <thead>
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Peminta</th>
                                        <th>Jabatan</th>
                                        <th>Status Penanganan</th>
                                        <th>Nama Lab & Vendor</th>
                                        <th>Status Tracking Terkini</th>
                                        <th>RKM / Materi & Jadwal</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>

                    {{-- TABEL 2: TRACKING PENGGUNAAN LAB (AKSES DIBERIKAN) --}}
                    <div class="card m-4">
                        <div class="card-body table-responsive">
                            <h3 class="card-title text-center my-1">Tracking Penggunaan Lab (Permintaan Akses & Assign Aktif)</h3>
                            <table class="table table-striped" id="pengajuanExistingTable">
                                <thead>
                                    <tr>
                                        <th>Tgl Pencatatan</th>
                                        <th>Pengguna Lab</th>
                                        <th>Jabatan</th>
                                        <th>Nama Lab & Vendor</th>
                                        <th>RKM / Materi</th>
                                        <th>Jadwal Penggunaan</th>
                                        <th>Status Penggunaan</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>

                    {{-- TABEL 3: RIWAYAT PENGADAAN BARANG SELESAI / DITOLAK --}}
                    <div class="card m-4">
                        <div class="card-body table-responsive">
                            <h3 class="card-title text-center my-1">Riwayat Pengadaan Lab Selesai / Ditolak</h3>
                            <table class="table table-striped" id="pengajuanSelesaiTable">
                                <thead>
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Peminta / PIC ITSM</th>
                                        <th>Divisi</th>
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
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h4 class="card-title mb-0 fw-bold">Master Data Laboratorium</h4>
                                <button type="button" class="btn btn-primary fw-bold" onclick="openAddMasterLabModal()">
                                    + Tambah Lab yang Sudah Berjalan
                                </button>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-striped align-middle" id="masterLabTable" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th width="18%">Nama Lab & Vendor</th>
                                            <th width="12%">Tipe & Status</th>
                                            <th width="22%">Sedang Digunakan Oleh</th>
                                            <th width="18%">Akses & Kredensial</th>
                                            <th width="14%">Masa Aktif & Biaya</th>
                                            <th width="10%">Materi</th>
                                            <th width="6%" class="text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    {{-- MODAL 1: TAMBAH LAB YANG SUDAH BERJALAN (EXISTING/LEGACY LAB) --}}
                    <div class="modal fade" id="addMasterLabModal" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title fw-bold">Tambah Data Lab yang Sudah Berjalan</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <form id="addMasterLabForm">
                                    @csrf
                                    <div class="modal-body">
                                        <div class="mb-3 row">
                                            <label class="col-sm-4 col-form-label">Nama Lab / Software <span class="text-danger">*</span></label>
                                            <div class="col-sm-8">
                                                <input type="text" class="form-control" name="nama_labs" required placeholder="Contoh: kodekloud / AWS Cloud">
                                            </div>
                                        </div>

                                        <div class="mb-3 row">
                                            <label class="col-sm-4 col-form-label">Vendor / Merk</label>
                                            <div class="col-sm-8">
                                                <input type="text" class="form-control" name="merk" placeholder="Contoh: KodeKloud / AWS">
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

                                        <div class="mb-3 row" id="row_add_duration" style="display: none;">
                                            <label class="col-sm-4 col-form-label">Durasi Akses (Menit)</label>
                                            <div class="col-sm-8">
                                                <input type="number" class="form-control" name="duration_minutes" id="add_duration_minutes">
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
                                            <label class="col-sm-4 col-form-label">URL Lab</label>
                                            <div class="col-sm-8">
                                                <input type="text" class="form-control" name="lab_url" placeholder="https://...">
                                            </div>
                                        </div>

                                        <div class="mb-3 row">
                                            <label class="col-sm-4 col-form-label">Kode Akses / Credentials</label>
                                            <div class="col-sm-8">
                                                <input type="text" class="form-control" name="access_code" placeholder="Username / Password / Key">
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
                                                <input type="number" step="0.01" class="form-control calculate-add-harga" name="harga" id="add_harga" placeholder="Nominal Harga Asli" value="0">
                                            </div>
                                        </div>

                                        <div class="mb-3 row">
                                            <label class="col-sm-4 col-form-label">Kurs & Estimasi (Rp)</label>
                                            <div class="col-sm-3">
                                                <input type="number" step="0.01" class="form-control calculate-add-harga" name="kurs" id="add_kurs" value="1" readonly>
                                            </div>
                                            <div class="col-sm-5">
                                                <input type="number" class="form-control bg-light" name="harga_rupiah" id="add_harga_rupiah" value="0" readonly>
                                            </div>
                                        </div>

                                        <div class="mb-3 row">
                                            <label class="col-sm-4 col-form-label">Deskripsi / Catatan</label>
                                            <div class="col-sm-8">
                                                <textarea class="form-control" name="desc" rows="2" placeholder="Catatan teknis lab..."></textarea>
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

                                        <hr>
                                        <h6 class="fw-bold text-primary mb-3">Langsung Assign Instruktur yang Sedang Menggunakan (Opsional)</h6>

                                        <div class="mb-3 row">
                                            <label class="col-sm-4 col-form-label">Pilih Instruktur</label>
                                            <div class="col-sm-8">
                                                <select class="form-select" name="assigned_kode_karyawan[]" id="add_assigned_instruktur" multiple="multiple" style="width: 100%;">
                                                    @if(isset($instrukturs))
                                                        @foreach($instrukturs as $inst)
                                                            <option value="{{ $inst->kode_karyawan }}">{{ $inst->nama_lengkap }} ({{ $inst->jabatan }})</option>
                                                        @endforeach
                                                    @endif
                                                </select>
                                                <small class="text-muted">Bisa pilih lebih dari satu instruktur jika lab dipakai bersama.</small>
                                            </div>
                                        </div>

                                        <div class="mb-3 row">
                                            <label class="col-sm-4 col-form-label">Tautkan ke RKM (Opsional)</label>
                                            <div class="col-sm-8">
                                                <select class="form-select" name="assigned_id_rkm">
                                                    <option value="">-- Non-RKM / Umum --</option>
                                                    @if(isset($rkms))
                                                        @foreach($rkms as $rkm)
                                                            <option value="{{ $rkm->id }}">
                                                                {{ $rkm->perusahaan->nama_perusahaan ?? '-' }} - {{ $rkm->materi->nama_materi ?? '-' }} ({{ \Carbon\Carbon::parse($rkm->tanggal_awal)->format('d/m/Y') }})
                                                            </option>
                                                        @endforeach
                                                    @endif
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                        <button type="submit" class="btn btn-primary">Simpan Lab & Assign</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    {{-- MODAL 2: ASSIGN INSTRUKTUR PENGGUNA LAB PADA MASTER LAB TERTENTU --}}
                    <div class="modal fade" id="assignLabUserModal" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title fw-bold">Kelola Pengguna Lab: <span id="assign_lab_title" class="text-primary"></span></h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <form id="assignLabUserForm">
                                    @csrf
                                    <input type="hidden" id="assign_lab_id">
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
                                                @if(isset($instrukturs))
                                                    @foreach($instrukturs as $inst)
                                                        <option value="{{ $inst->kode_karyawan }}">{{ $inst->nama_lengkap }} ({{ $inst->jabatan }})</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Jadwal RKM <span class="text-muted small">(Opsional)</span></label>
                                            <select class="form-select" name="id_rkm" id="assign_id_rkm">
                                                <option value="">-- Tanpa RKM (Penggunaan Umum / Berjalan) --</option>
                                                @if(isset($rkms))
                                                    @foreach($rkms as $rkm)
                                                        <option value="{{ $rkm->id }}">
                                                            {{ $rkm->perusahaan->nama_perusahaan ?? '-' }} - {{ $rkm->materi->nama_materi ?? '-' }} ({{ \Carbon\Carbon::parse($rkm->tanggal_awal)->format('d M Y') }})
                                                        </option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Catatan Penggunaan (Opsional)</label>
                                            <textarea class="form-control" name="catatan_assign" rows="2" placeholder="Contoh: Digunakan untuk kelas berjalan / persiapan materi"></textarea>
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

                    {{-- MODAL 3: EDIT MASTER LAB --}}
                    <div class="modal fade" id="editMasterLabModal" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title fw-bold">Edit Data Master Lab</h5>
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
                                                <textarea class="form-control" name="desc" id="edit_deskripsi" rows="3"></textarea>
                                            </div>
                                        </div>

                                        <div class="mb-3 row">
                                            <label class="col-sm-4 col-form-label">URL Lab</label>
                                            <div class="col-sm-8">
                                                <input type="text" class="form-control" name="lab_url" id="edit_url_labs">
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

        $('.calculate-add-harga').on('input', calculateAddEstimasiRupiah);
        $('#add_mata_uang').on('change', calculateAddEstimasiRupiah);

        $('#edit_materi').select2({
            theme: 'bootstrap-5',
            placeholder: "Pilih Materi Terkait...",
            allowClear: true,
            dropdownParent: $('#editMasterLabModal')
        });

        $('#add_materi').select2({
            theme: 'bootstrap-5',
            placeholder: "Pilih Materi Terkait...",
            allowClear: true,
            dropdownParent: $('#addMasterLabModal')
        });

        $('#add_assigned_instruktur').select2({
            theme: 'bootstrap-5',
            placeholder: "Pilih Instruktur yang sedang menggunakan...",
            allowClear: true,
            dropdownParent: $('#addMasterLabModal')
        });

        $('#assign_kode_karyawan').select2({
            theme: 'bootstrap-5',
            placeholder: "Pilih Instruktur...",
            allowClear: true,
            dropdownParent: $('#assignLabUserModal')
        });

        $('#edit_tipe').on('change', function() {
            if ($(this).val() === 'one-time') {
                $('#row_edit_duration').slideDown();
            } else {
                $('#row_edit_duration').slideUp();
                $('#edit_duration_minutes').val('');
            }
        });

        $('#add_tipe').on('change', function() {
            if ($(this).val() === 'one-time') {
                $('#row_add_duration').slideDown();
            } else {
                $('#row_add_duration').slideUp();
                $('#add_duration_minutes').val('');
            }
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

    function calculateAddEstimasiRupiah() {
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
            url: `/getPengajuanLabSubs/${month}/${year}`,
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

                    if ($.fn.DataTable.isDataTable('#pengajuanLabSubsTable')) $('#pengajuanLabSubsTable').DataTable().destroy();
                    $("#pengajuanLabSubsTable tbody").html(rowsAktif);
                    $('#pengajuanLabSubsTable').DataTable({
                        "order": [[ 0, "desc" ]],
                        "language": { "emptyTable": "Tidak ada antrean permintaan lab saat ini." }
                    });

                    if ($.fn.DataTable.isDataTable('#pengajuanExistingTable')) $('#pengajuanExistingTable').DataTable().destroy();
                    $("#pengajuanExistingTable tbody").html(rowsExisting);
                    $('#pengajuanExistingTable').DataTable({
                        "order": [[ 0, "desc" ]],
                        "language": { "emptyTable": "Belum ada penggunaan akses lab aktif." }
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
        let handlerInfo = item.lab_snapshot?.handled_by ? `<br><small class="text-muted">PIC ITSM: ${item.lab_snapshot.handled_by}</small>` : '';
        let kategori = item.jenis_transaksi === 'baru' ? `<span class="fw-bold text-dark">Pengadaan Baru</span>${handlerInfo}` :
                      (item.jenis_transaksi === 'pembaharuan' ? `<span class="fw-bold text-dark">Perpanjangan Lab</span>${handlerInfo}` :
                      `<span class="fw-bold text-dark">Menunggu Eksekusi ITSM</span>`);

        let labName = item.lab ? `${item.lab.nama_labs} <br><small class="text-muted">(${item.lab.merk || '-'})</small>` : '-';
        let rkmInfo = item.rkm ? `${item.rkm.materi?.nama_materi ?? '-'} <br><small class="text-muted">${item.rkm.perusahaan?.nama_perusahaan ?? '-'} (${moment(item.rkm.tanggal_awal).format('DD/MM/YY')} - ${moment(item.rkm.tanggal_akhir).format('DD/MM/YY')})</small>` : '<span class="text-muted">Non-RKM / Umum</span>';
        let btns = generateButtons(item, status);

        return `
            <tr>
                <td>${moment(item.created_at).format('DD/MM/YYYY')}</td>
                <td>${item.karyawan?.nama_lengkap ?? '-'}</td>
                <td>${item.karyawan?.jabatan ?? '-'}</td>
                <td>${kategori}</td>
                <td>${labName}</td>
                <td>${formatStatus(status)}</td>
                <td>${rkmInfo}</td>
                <td>${btns}</td>
            </tr>
        `;
    }

    function renderRowTrackingAkses(item, status) {
        let labName = item.lab ? `<strong>${item.lab.nama_labs}</strong> <br><small class="text-muted">${item.lab.merk || '-'}</small>` : '-';
        let rkmInfo = item.rkm ? `${item.rkm.materi?.nama_materi ?? '-'} <br><small class="text-muted">(${item.rkm.perusahaan?.nama_perusahaan ?? '-'})</small>` : '<span class="text-muted">Non-RKM / Umum</span>';

        let jadwalRkm = 'Mengikuti Masa Aktif Lab';
        let statusPakai = '<span class="fw-bold text-dark">Sedang Digunakan</span>';

        if (item.rkm && item.rkm.tanggal_awal && item.rkm.tanggal_akhir) {
            let start = moment(item.rkm.tanggal_awal);
            let end = moment(item.rkm.tanggal_akhir).endOf('day');
            let now = moment();

            jadwalRkm = `${start.format('DD MMM YYYY')} - ${end.format('DD MMM YYYY')}`;

            if (now.isBetween(start, end, null, '[]')) {
                statusPakai = '<span class="fw-bold text-dark">Sedang Digunakan (Kelas Berlangsung)</span>';
            } else if (now.isBefore(start)) {
                statusPakai = '<span class="fw-bold text-dark">Akses Siap (Menunggu Jadwal Kelas)</span>';
            } else {
                statusPakai = '<span class="text-muted">Selesai Digunakan (Kelas Berakhir)</span>';
            }
        } else if (item.lab && item.lab.end_date) {
            let endLab = moment(item.lab.end_date).endOf('day');
            jadwalRkm = `Exp Lab: ${endLab.format('DD MMM YYYY')}`;
            if (moment().isAfter(endLab)) {
                statusPakai = '<span class="text-muted">Masa Aktif Lab Habis</span>';
            }
        }

        let safeUser = (item.karyawan?.nama_lengkap ?? 'Pengguna').replace(/'/g, "\\'");
        let safeLab = (item.lab?.nama_labs ?? 'Lab').replace(/'/g, "\\'");

        let unassignOption = '';
        if (userRole === 'Technical Support' || userRole === 'Koordinator ITSM') {
            unassignOption = `
                <li><hr class="dropdown-divider"></li>
                <li>
                    <button class="dropdown-item text-danger fw-bold" onclick="unassignLabUser(${item.id}, '${safeUser}', '${safeLab}')">
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
                <td>${item.karyawan?.jabatan ?? '-'}</td>
                <td>${labName}</td>
                <td>${rkmInfo}</td>
                <td>${jadwalRkm}</td>
                <td>${statusPakai}</td>
                <td>${btns}</td>
            </tr>
        `;
    }

    function renderRowSelesai(item, status) {
        let handlerInfo = item.lab_snapshot?.handled_by ? `<br><small class="text-muted">PIC ITSM: ${item.lab_snapshot.handled_by}</small>` : '';
        let kategori = item.jenis_transaksi === 'baru' ? '<span class="fw-bold text-dark">Pengadaan Baru</span>' :
                      (item.jenis_transaksi === 'pembaharuan' ? '<span class="fw-bold text-dark">Perpanjangan</span>' :
                      '<span class="fw-bold text-dark">Permintaan Lab</span>');

        let labName = item.lab ? item.lab.nama_labs : '-';
        let rkmInfo = item.rkm ? (item.rkm.materi?.nama_materi ?? '-') : 'Non-RKM';
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
                <td>${labName}</td>
                <td>${formatStatus(status)}</td>
                <td>${rkmInfo}</td>
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

        if (userRole === 'Education Manager' && statusLower.includes('ditinjau oleh education manager')) {
            btns += `
                <li><hr class="dropdown-divider"></li>
                <li><button class="dropdown-item text-success" onclick="openApproveRejectModal(${item.id}, 'approve')"><img src="{{ asset('icon/check-circle.svg') }}" width="16" class="me-1"> Approve (Teruskan ke ITSM)</button></li>
                <li><button class="dropdown-item text-danger" onclick="openApproveRejectModal(${item.id}, 'reject')"><img src="{{ asset('icon/x-circle.svg') }}" width="16" class="me-1"> Reject</button></li>
            `;
        }

        if ((userRole === 'Technical Support' || userRole === 'Koordinator ITSM') && !statusLower.includes('selesai')) {
            btns += `
                <li><hr class="dropdown-divider"></li>
                <li><button class="dropdown-item fw-bold text-primary" onclick="editPengajuan(${item.id})"><img src="{{ asset('icon/edit-warning.svg') }}" width="16" class="me-1"> Proses Lab (Akses / Pengadaan)</button></li>
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
        let dataLengkap = item.lab && item.lab.harga;
        if (!dataLengkap) {
            return `<li><button class="dropdown-item text-muted" disabled><img src="{{ asset('icon/upload.svg') }}" width="16" class="me-1"> Upload Invoice (Isi Harga Dulu)</button></li>`;
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

        if (type === 'approve') {
            $('#actionLabel').text('Setujui permintaan lab ini dan teruskan ke Tim ITSM?');
            $('#modalApproval').val('1');
        } else if (type === 'reject') {
            $('#actionLabel').text('Anda yakin ingin MENOLAK permintaan lab ini?');
            $('#modalApproval').val('2');
            $('#reasonContainer').removeClass('d-none');
        }
        new bootstrap.Modal(document.getElementById('approveRejectModal')).show();
    }

    function openUploadInvoiceModal(id) {
        $('#uploadInvoiceId').val(id);
        new bootstrap.Modal(document.getElementById('uploadInvoiceModal')).show();
    }

    function openAddMasterLabModal() {
        $('#addMasterLabForm')[0].reset();
        $('#add_materi').val([]).trigger('change');
        $('#add_assigned_instruktur').val([]).trigger('change');
        new bootstrap.Modal(document.getElementById('addMasterLabModal')).show();
    }

    function openAssignLabModal(row) {
        $('#assign_lab_id').val(row.id);
        $('#assign_lab_title').text(row.nama_labs);
        $('#assignLabUserForm')[0].reset();
        $('#assign_kode_karyawan').val([]).trigger('change');

        let listContainer = $('#current_assigned_users_list');
        listContainer.empty();

        if (row.active_users && row.active_users.length > 0) {
            let html = row.active_users.map(u => {
                let safeUser = (u.nama || '').replace(/'/g, "\\'");
                let safeLab = (row.nama_labs || '').replace(/'/g, "\\'");
                return `
                    <div class="d-flex justify-content-between align-items-center py-1 px-2 mb-1 bg-white border rounded">
                        <div>
                            <div class="fw-bold small text-dark">${u.nama}</div>
                            <small class="text-muted" style="font-size: 0.75rem;">${u.perusahaan} (${u.periode})</small>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2" style="font-size: 0.75rem;"
                                onclick="unassignLabUser(${u.pengajuan_id}, '${safeUser}', '${safeLab}')">
                            Unassign
                        </button>
                    </div>
                `;
            }).join('');
            listContainer.html(html);
        } else {
            listContainer.html('<span class="text-muted small"><em>Belum ada pengguna yang di-assign pada lab ini.</em></span>');
        }

        new bootstrap.Modal(document.getElementById('assignLabUserModal')).show();
    }

    function unassignLabUser(pengajuanId, namaUser, namaLab) {
        Swal.fire({
            title: 'Unassign Pengguna?',
            text: `Cabut akses penggunaan lab "${namaLab}" untuk ${namaUser}?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Ya, Unassign',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/api/master-labs/unassign/${pengajuanId}`,
                    type: 'POST',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function(res) {
                        let modalEl = document.getElementById('assignLabUserModal');
                        let modalInst = bootstrap.Modal.getInstance(modalEl);
                        if (modalInst) modalInst.hide();

                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: res.message,
                            timer: 1500,
                            showConfirmButton: false
                        });

                        if (masterLabTableInit) {
                            $('#masterLabTable').DataTable().ajax.reload(null, false);
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

    $('#addMasterLabForm').on('submit', function(e) {
        e.preventDefault();
        let formData = $(this).serialize();

        $.ajax({
            url: "{{ route('api.master-labs.store') }}",
            type: 'POST',
            data: formData,
            success: function(res) {
                bootstrap.Modal.getInstance(document.getElementById('addMasterLabModal')).hide();
                Swal.fire({ icon: 'success', title: 'Berhasil', text: res.message, timer: 1500, showConfirmButton: false });
                $('#masterLabTable').DataTable().ajax.reload(null, false);
                loadAllTables();
            },
            error: function(err) {
                Swal.fire('Gagal', err.responseJSON?.message || 'Gagal menyimpan data lab.', 'error');
            }
        });
    });

    $('#assignLabUserForm').on('submit', function(e) {
        e.preventDefault();
        let labId = $('#assign_lab_id').val();
        let formData = $(this).serialize();

        $.ajax({
            url: `/api/master-labs/${labId}/assign`,
            type: 'POST',
            data: formData,
            success: function(res) {
                bootstrap.Modal.getInstance(document.getElementById('assignLabUserModal')).hide();
                Swal.fire({ icon: 'success', title: 'Berhasil', text: res.message, timer: 1500, showConfirmButton: false });
                $('#masterLabTable').DataTable().ajax.reload(null, false);
                loadAllTables();
            },
            error: function(err) {
                Swal.fire('Gagal', err.responseJSON?.message || 'Gagal meng-assign instruktur.', 'error');
            }
        });
    });

    $('#approvalForm').on('submit', function(e) {
        e.preventDefault();
        let id = $('#modalId').val();
        let formData = $(this).serialize();

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
            success: function() {
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
        if(confirm('Yakin ingin menghapus permintaan lab ini?')) {
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

    let masterLabTableInit = false;

    function loadMasterLabs() {
        if (masterLabTableInit) {
            $('#masterLabTable').DataTable().ajax.reload(null, false);
            return;
        }

        $('#masterLabTable').DataTable({
            processing: true,
            serverSide: false,
            autoWidth: false,
            ajax: {
                url: "{{ route('api.master-labs') }}",
                type: 'GET'
            },
            columns: [
                // 1. Nama Lab & Vendor
                {
                    data: null,
                    render: function(data, type, row) {
                        let merk = row.merk ? row.merk : 'Tanpa Vendor';
                        return `<div class="fw-bold text-dark">${row.nama_labs}</div>
                                <small class="text-muted">${merk}</small>`;
                    }
                },
                // 2. Tipe & Status (Tanpa Highlight Warna)
                {
                    data: null,
                    render: function(data, type, row) {
                        let tipeLabel = row.tipe === 'subscription' ? 'Subscription' : 'One-Time';
                        let statusCap = row.status ? row.status.charAt(0).toUpperCase() + row.status.slice(1) : '-';
                        let durasi = (row.tipe === 'one-time' && row.duration_minutes) ? `<br><small class="text-muted">${row.duration_minutes} Menit</small>` : '';

                        return `<div class="fw-bold text-dark">${statusCap}</div>
                                <small class="text-muted">${tipeLabel}</small>${durasi}`;
                    }
                },
                // 3. Sedang Digunakan Oleh (Dilengkapi Tombol Unassign Cepat)
                {
                    data: 'active_users',
                    render: function(data, type, row) {
                        if (!data || data.length === 0) {
                            return '<span class="text-muted small"><em>Belum ada instruktur di-assign</em></span>';
                        }
                        let safeLab = (row.nama_labs || '').replace(/'/g, "\\'");
                        return data.map(u => {
                            let safeUser = (u.nama || '').replace(/'/g, "\\'");
                            return `
                                <div class="mb-1 pb-1 border-bottom d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="fw-bold text-dark small">${u.nama}</span><br>
                                        <small class="text-muted" style="font-size: 0.78rem;">${u.perusahaan} (${u.periode})</small>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-danger py-0 px-1 ms-2" style="font-size: 0.72rem;"
                                            onclick="unassignLabUser(${u.pengajuan_id}, '${safeUser}', '${safeLab}')" title="Unassign / Cabut Akses ${u.nama}">
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
                        let urlHtml = row.lab_url
                            ? `<a href="${row.lab_url}" target="_blank" class="fw-bold text-primary text-decoration-none small">Buka Link Lab ↗</a>`
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
                // 6. Materi Terhubung
                {
                    data: 'materis',
                    render: function(data) {
                        if (!data || data.length === 0) {
                            return '<span class="text-muted small">-</span>';
                        }
                        return data.map(m => `<div class="small text-dark">• ${m.nama_materi}</div>`).join('');
                    }
                },
                // 7. Aksi (Dropdown Rapi)
                {
                    data: null,
                    className: 'text-center',
                    render: function(data, type, row) {
                        let safeName = (row.nama_labs || '').replace(/'/g, "\\'");
                        let rowJson = JSON.stringify(row).replace(/'/g, "&#39;");

                        return `
                            <div class="dropdown">
                                <button class="btn btn-sm btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                    Aksi
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow">
                                    <li>
                                        <button class="dropdown-item fw-bold" onclick='openAssignLabModal(${rowJson})'>
                                            Assign / Unassign Pengguna
                                        </button>
                                    </li>
                                    <li>
                                        <button class="dropdown-item" onclick='openEditMasterLabModal(${rowJson})'>
                                            Edit Data Lab
                                        </button>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <button class="dropdown-item text-success fw-bold" onclick="renewLab(${row.id}, '${safeName}')">
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
        $('#edit_mata_uang').val(lab.mata_uang || 'Rupiah');
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

    $('#editMasterLabForm').on('submit', function(e) {
        e.preventDefault();
        let id = $('#edit_lab_id').val();
        let formData = $(this).serialize();

        $.ajax({
            url: `/api/master-labs/${id}`,
            type: 'PUT',
            data: formData,
            success: function() {
                bootstrap.Modal.getInstance(document.getElementById('editMasterLabModal')).hide();
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: 'Data Master Lab berhasil diperbarui',
                    timer: 1500,
                    showConfirmButton: false
                });
                $('#masterLabTable').DataTable().ajax.reload(null, false);
            },
            error: function() {
                Swal.fire('Gagal', 'Terjadi kesalahan saat menyimpan data', 'error');
            }
        });
    });

    function renewLab(id, namaLab) {
        Swal.fire({
            title: 'Perbarui / Perpanjang Lab?',
            text: `Buat draft perpanjangan untuk lab: ${namaLab} atas nama Anda (${userRole})`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Buat Pengajuan',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/api/master-labs/${id}/renew`,
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

