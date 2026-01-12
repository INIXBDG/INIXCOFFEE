@extends('layouts.app')

@section('content')
<div class="container-fluid">
    {{-- 1. Loading Modal (Cube Style) --}}
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

    {{-- 2. Modal Create Sertifikasi --}}
    <div class="modal fade" id="createSertifikasiModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Sertifikasi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('sertifikasi.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nama Sertifikat</label>
                                <input type="text" name="nama_sertifikat" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Penyedia</label>
                                <input type="text" name="penyedia" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Vendor</label>
                                <input type="text" name="vendor" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Harga</label>
                                <input type="number" name="harga" class="form-control" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Tanggal Ujian</label>
                                <input type="date" name="tanggal_ujian" class="form-control" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Berlaku Dari</label>
                                <input type="date" name="tanggal_berlaku_dari" class="form-control" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Berlaku Sampai</label>
                                <input type="date" name="tanggal_berlaku_sampai" class="form-control">
                                <small class="text-muted">Kosongkan jika seumur hidup</small>
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

    {{-- 3. Modal Create Pelatihan --}}
    <div class="modal fade" id="createPelatihanModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Pelatihan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('pelatihan.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nama Pelatihan</label>
                                <input type="text" name="nama_pelatihan" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Penyedia</label>
                                <input type="text" name="penyedia" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tanggal Mulai</label>
                                <input type="date" name="tanggal_mulai" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tanggal Selesai</label>
                                <input type="date" name="tanggal_selesai" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Harga</label>
                                <input type="number" name="harga" class="form-control" required>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Keterangan</label>
                                <textarea name="keterangan" class="form-control" rows="3"></textarea>
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

    {{-- 4. Modal Approval (Shared) --}}
    <div class="modal fade" id="approveModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Konfirmasi Approval</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="approveForm" method="POST">
                        @csrf
                        <p>Apakah pengajuan ini disetujui?</p>
                        <div class="btn-group w-100" role="group">
                            <input type="radio" class="btn-check" name="status_approval" id="approveYes" value="approved" checked>
                            <label class="btn btn-outline-success" for="approveYes">Ya, Setujui</label>

                            <input type="radio" class="btn-check" name="status_approval" id="approveNo" value="rejected">
                            <label class="btn btn-outline-danger" for="approveNo">Tidak, Tolak</label>
                        </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Status</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- 5. Modal Edit Sertifikasi --}}
    <div class="modal fade" id="editSertifikasiModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Sertifikasi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formEditSertifikasi" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        {{-- Isi form sama dengan create, tapi tambahkan ID untuk JS --}}
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nama Sertifikat</label>
                                <input type="text" name="nama_sertifikat" id="edit_nama_sertifikat" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Penyedia</label>
                                <input type="text" name="penyedia" id="edit_penyedia" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Vendor</label>
                                <input type="text" name="vendor" id="edit_vendor" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Harga</label>
                                <input type="number" name="harga" id="edit_harga_sertifikasi" class="form-control" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Tanggal Ujian</label>
                                <input type="date" name="tanggal_ujian" id="edit_tanggal_ujian" class="form-control" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Berlaku Dari</label>
                                <input type="date" name="tanggal_berlaku_dari" id="edit_tanggal_berlaku_dari" class="form-control" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Berlaku Sampai</label>
                                <input type="date" name="tanggal_berlaku_sampai" id="edit_tanggal_berlaku_sampai" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- 6. Modal Edit Pelatihan --}}
    <div class="modal fade" id="editPelatihanModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Pelatihan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formEditPelatihan" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nama Pelatihan</label>
                                <input type="text" name="nama_pelatihan" id="edit_nama_pelatihan" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Penyedia</label>
                                <input type="text" name="penyedia" id="edit_penyedia_pelatihan" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tanggal Mulai</label>
                                <input type="date" name="tanggal_mulai" id="edit_tanggal_mulai" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tanggal Selesai</label>
                                <input type="date" name="tanggal_selesai" id="edit_tanggal_selesai" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Harga</label>
                                <input type="number" name="harga" id="edit_harga_pelatihan" class="form-control" required>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Keterangan</label>
                                <textarea name="keterangan" id="edit_keterangan" class="form-control" rows="3"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="row justify-content-center mt-4">
        <div class="col-md-12">

            <ul class="nav nav-tabs" id="myTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="sertifikasi-tab" data-bs-toggle="tab" data-bs-target="#sertifikasi" type="button" role="tab">Sertifikasi</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="pelatihan-tab" data-bs-toggle="tab" data-bs-target="#pelatihan" type="button" role="tab">Pelatihan</button>
                </li>
            </ul>

            <div class="tab-content" id="myTabContent">

                {{-- TAB 1: Sertifikasi --}}
                <div class="tab-pane fade show active" id="sertifikasi" role="tabpanel">
                    <div class="card mt-3 border-top-0">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h3 class="card-title">Data Sertifikasi</h3>
                                @if(auth()->user()->jabatan !== 'Education Manager')
                                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createSertifikasiModal">
                                    <img src="{{ asset('icon/plus.svg') }}" width="20px"> Tambah Sertifikasi
                                </button>
                                @endif
                            </div>

                            <div class="table-responsive">
                                <table class="table table-striped" id="tableSertifikasi">
                                    <thead>
                                        <tr>
                                            <th>Tanggal Dibuat</th>
                                            <th>Nama Karyawan</th>
                                            <th>Nama Sertifikat</th>
                                            <th>Penyedia</th>
                                            <th>Tgl Ujian</th>
                                            <th>Masa Berlaku</th>
                                            <th>Harga</th>
                                            <th>Status</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($sertifikasis as $item)
                                        {{-- LOGIKA CEK EXPIRED --}}
                                        @php
                                            $isExpired = $item->tanggal_berlaku_sampai && \Carbon\Carbon::parse($item->tanggal_berlaku_sampai)->endOfDay()->isPast();
                                        @endphp

                                        <tr>
                                            <td>{{ \Carbon\Carbon::parse($item->created_at)->translatedFormat('d F Y') }}</td>
                                            <td>{{ $item->user->karyawan->nama_lengkap ?? '-' }}</td>
                                            <td>{{ $item->nama_sertifikat }}</td>
                                            <td>{{ $item->penyedia }} <br> <small class="text-muted">{{ $item->vendor }}</small></td>
                                            <td>{{ \Carbon\Carbon::parse($item->tanggal_ujian)->translatedFormat('d F Y') }}</td>

                                            {{-- 1. Tampilkan Tanggal (Merah jika expired) --}}
                                            <td class="{{ $isExpired ? 'text-danger fw-bold' : '' }}">
                                                {{ \Carbon\Carbon::parse($item->tanggal_berlaku_dari)->translatedFormat('d F Y') }} -
                                                {{ $item->tanggal_berlaku_sampai ? \Carbon\Carbon::parse($item->tanggal_berlaku_sampai)->translatedFormat('d F Y') : 'Seumur Hidup' }}

                                                @if($isExpired)
                                                    <div style="font-size: 0.8em;">(Kadaluarsa)</div>
                                                @endif
                                            </td>

                                            <td>Rp {{ number_format($item->harga, 0, ',', '.') }}</td>

                                            {{-- 2. Tampilkan Status (Retired jika expired & approved) --}}
                                            <td>
                                                @if($item->status_approval == 'approved')
                                                    @if($isExpired)
                                                        <span class="badge bg-secondary">RETIRED</span>
                                                    @else
                                                        <span class="badge bg-success">Approved</span>
                                                    @endif
                                                @elseif($item->status_approval == 'rejected')
                                                    <span class="badge bg-danger">Rejected</span>
                                                @else
                                                    <span class="badge bg-warning text-dark">Pending</span>
                                                @endif
                                            </td>

                                            <td>
                                                <div class="dropdown">
                                                    <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">Actions</button>
                                                    <div class="dropdown-menu">
                                                        @if(auth()->user()->jabatan === 'Education Manager' && $item->status_approval === 'pending')
                                                            <button class="dropdown-item" onclick="openApproveModal('{{ route('sertifikasi.approve', $item->id) }}')">
                                                                <img src="{{ asset('icon/check-circle.svg') }}"> Approval
                                                            </button>
                                                        @endif

                                                        @if($item->status_approval !== 'approved' && auth()->id() == $item->user_id)
                                                            <button class="dropdown-item" onclick='openEditSertifikasi(@json($item))'>
                                                                <img src="{{ asset('icon/edit.svg') }}" width="16px"> Edit
                                                            </button>
                                                            <form action="{{ route('sertifikasi.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Hapus data ini?');">
                                                                @csrf @method('DELETE')
                                                                <button type="submit" class="dropdown-item text-danger">
                                                                    <img src="{{ asset('icon/trash-danger.svg') }}"> Hapus
                                                                </button>
                                                            </form>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="pelatihan" role="tabpanel">
                    <div class="card mt-3 border-top-0">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h3 class="card-title">Data Pelatihan</h3>
                                @if(auth()->user()->jabatan !== 'Education Manager')
                                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createPelatihanModal">
                                    <img src="{{ asset('icon/plus.svg') }}" width="20px"> Tambah Pelatihan
                                </button>
                                @endif
                            </div>

                            <div class="table-responsive">
                                <table class="table table-striped" id="tablePelatihan">
                                    <thead>
                                        <tr>
                                            <th>Tanggal Dibuat</th>
                                            <th>Nama Karyawan</th>
                                            <th>Nama Pelatihan</th>
                                            <th>Penyedia</th>
                                            <th>Pelaksanaan</th> {{-- Diubah dari Tgl Pelatihan --}}
                                            <th>Keterangan</th>
                                            <th>Harga</th>
                                            <th>Status</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($pelatihans as $item)
                                        <tr>
                                            <td>{{ \Carbon\Carbon::parse($item->created_at)->translatedFormat('d F Y') }}</td>
                                            <td>{{ $item->user->karyawan->nama_lengkap ?? '-' }}</td>
                                            <td>{{ $item->nama_pelatihan }}</td>
                                            <td>{{ $item->penyedia }}</td>

                                            {{-- PERBAIKAN: Menampilkan Rentang Tanggal Mulai - Selesai --}}
                                            <td>
                                                {{ \Carbon\Carbon::parse($item->tanggal_mulai)->translatedFormat('d F Y') }}
                                                -
                                                {{ \Carbon\Carbon::parse($item->tanggal_selesai)->translatedFormat('d F Y') }}
                                            </td>

                                            <td>{{ Str::limit($item->keterangan, 30) }}</td>
                                            <td>Rp {{ number_format($item->harga, 0, ',', '.') }}</td>
                                            <td>
                                                @if($item->status_approval == 'approved')
                                                    <span class="badge bg-success">Approved</span>
                                                @elseif($item->status_approval == 'rejected')
                                                    <span class="badge bg-danger">Rejected</span>
                                                @else
                                                    <span class="badge bg-warning text-dark">Pending</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="dropdown">
                                                    <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">Actions</button>
                                                    <div class="dropdown-menu">
                                                        @if(auth()->user()->jabatan === 'Education Manager' && $item->status_approval === 'pending')
                                                            <button class="dropdown-item" onclick="openApproveModal('{{ route('pelatihan.approve', $item->id) }}')">
                                                                <img src="{{ asset('icon/check-circle.svg') }}"> Approval
                                                            </button>
                                                        @endif

                                                        @if($item->status_approval !== 'approved' && auth()->id() == $item->user_id)
                                                            <button class="dropdown-item" onclick='openEditPelatihan(@json($item))'>
                                                                <img src="{{ asset('icon/edit.svg') }}" width="16px"> Edit
                                                            </button>
                                                            <form action="{{ route('pelatihan.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Hapus data ini?');">
                                                                @csrf @method('DELETE')
                                                                <button type="submit" class="dropdown-item text-danger">
                                                                    <img src="{{ asset('icon/trash-danger.svg') }}"> Hapus
                                                                </button>
                                                            </form>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<style>
    /* Styling Cube Loader */
    .cube {
        width: 40px;
        height: 40px;
        position: relative;
        margin: auto;
        transform: rotate(45deg);
    }
    .cube_item {
        position: absolute;
        width: 20px;
        height: 20px;
        background: #0d6efd; /* Primary Color */
        border-radius: 2px;
    }
    .cube_x { animation: cube_x 1.5s infinite ease-in-out; }
    .cube_y { animation: cube_y 1.5s infinite ease-in-out; }
    .cube_z { animation: cube_z 1.5s infinite ease-in-out; }

    .cube_item:nth-child(1) { top: 0; left: 0; }
    .cube_item:nth-child(2) { top: 0; right: 0; background: #0a58ca; }
    .cube_item:nth-child(3) { bottom: 0; left: 0; background: #0a58ca; }
    .cube_item:nth-child(4) { bottom: 0; right: 0; }

    @keyframes cube_x { 0%, 100% { transform: scale(1); } 50% { transform: scale(0.5); } }
    @keyframes cube_y { 0%, 100% { transform: scale(1); } 50% { transform: scale(0.5); } }
    @keyframes cube_z { 0%, 100% { transform: scale(1); } 50% { transform: scale(0.5); } }

    /* Modal Custom Shadow */
    .modal-content {
        border-radius: 0px;
        box-shadow: 0 0 20px 8px rgba(0, 0, 0, 0.2);
    }
</style>

@push('js')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.17.1/moment-with-locales.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $(document).ready(function() {
        var activeTab = localStorage.getItem('activeTab');

        // Jika ada history tab, buka tab tersebut
        if (activeTab) {
            var tabTrigger = document.querySelector('button[data-bs-target="' + activeTab + '"]');
            if (tabTrigger) {
                var tabInstance = new bootstrap.Tab(tabTrigger);
                tabInstance.show();
            }
        }

        // Event Listener: Setiap kali ganti tab, simpan ID-nya
        $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
            var target = $(e.target).attr('data-bs-target'); // Contoh: #pelatihan
            localStorage.setItem('activeTab', target);
        });

        $('#tableSertifikasi').DataTable({
            "order": [[0, "desc"]], // Urutkan berdasarkan kolom pertama (Tanggal) descending
        });

        $('#tablePelatihan').DataTable({
            "order": [[0, "desc"]],
        });

        @if(session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: "{{ session('success') }}",
                timer: 3000,
                showConfirmButton: false
            });
        @endif

        @if(session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: "{{ session('error') }}"
            });
        @endif

        @if($errors->any())
            var errorMessages = "";
            @foreach ($errors->all() as $error)
                errorMessages += "{{ $error }}\n";
            @endforeach

            Swal.fire({
                icon: 'warning',
                title: 'Validasi Gagal',
                text: errorMessages, // Menampilkan detail error validasi
                confirmButtonText: 'Perbaiki'
            });
        @endif

    });

    function openApproveModal(url) {
        $('#approveForm').attr('action', url);
        $('#approveModal').modal('show');
    }

    $('form').on('submit', function() {
        var formMethod = $(this).find('input[name="_method"]').val();

        if (formMethod !== 'DELETE') {
            $('#loadingModal').modal('show');
            $('#loadingModal').removeAttr('inert');
        }
    });

    // Fungsi Buka Modal Edit Sertifikasi
    function openEditSertifikasi(data) {
        // Set Action Form
        $('#formEditSertifikasi').attr('action', '/development/sertifikasi/' + data.id);

        // Isi Input
        $('#edit_nama_sertifikat').val(data.nama_sertifikat);
        $('#edit_penyedia').val(data.penyedia);
        $('#edit_vendor').val(data.vendor);
        $('#edit_harga_sertifikasi').val(data.harga);
        $('#edit_tanggal_ujian').val(data.tanggal_ujian);
        $('#edit_tanggal_berlaku_dari').val(data.tanggal_berlaku_dari);
        $('#edit_tanggal_berlaku_sampai').val(data.tanggal_berlaku_sampai);

        // Buka Modal
        $('#editSertifikasiModal').modal('show');
    }

    // Fungsi Buka Modal Edit Pelatihan
    function openEditPelatihan(data) {
        // Set Action Form
        $('#formEditPelatihan').attr('action', '/development/pelatihan/' + data.id);

        // Isi Input
        $('#edit_nama_pelatihan').val(data.nama_pelatihan);
        $('#edit_penyedia_pelatihan').val(data.penyedia);
        $('#edit_tanggal_mulai').val(data.tanggal_mulai);
        $('#edit_tanggal_selesai').val(data.tanggal_selesai);
        $('#edit_harga_pelatihan').val(data.harga);
        $('#edit_keterangan').val(data.keterangan);

        // Buka Modal
        $('#editPelatihanModal').modal('show');
    }

</script>
@endpush
@endsection
