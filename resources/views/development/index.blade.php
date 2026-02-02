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

    {{-- Modal Create Sertifikasi --}}
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
                            {{-- <div class="col-md-6 mb-3">
                                <label class="form-label">Penyedia</label>
                                <input type="text" name="penyedia" class="form-control" required>
                            </div> --}}
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Vendor</label>
                                <input type="text" name="vendor" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Harga</label>
                                <input type="number" name="harga" class="form-control">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Tanggal Ujian</label>
                                <input type="date" name="tanggal_ujian" class="form-control" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Berlaku Dari</label>
                                <input type="date" name="tanggal_berlaku_dari" class="form-control">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Berlaku Sampai</label>
                                <input type="date" name="tanggal_berlaku_sampai" class="form-control">
                                <small class="text-muted">Kosongkan jika seumur hidup</small>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Keterangan</label>
                                <textarea name="keterangan" class="form-control" rows="3"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <small class="text-muted me-auto fst-italic">*kosongkan bagian harga, untuk sertifikasi tanpa pengajuan</small>

                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Upload Bukti Pelatihan --}}
    <div class="modal fade" id="uploadBuktiModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Upload Bukti Pelatihan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formUploadBukti" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="alert alert-info">
                            Silakan upload sertifikat atau bukti penilaian pelatihan yang telah diselesaikan.
                        </div>
                        <div class="mb-3">
                            <label class="form-label">File Bukti <span class="text-danger">*</span></label>
                            <input type="file" name="bukti_pelatihan" class="form-control" required accept=".pdf,.jpg,.jpeg,.png">
                            <small class="text-muted">Format: PDF, JPG, PNG. Maks: 5MB</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Upload</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Upload Bukti SERTIFIKASI --}}
    <div class="modal fade" id="uploadBuktiSertifikasiModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Upload Dokumen Sertifikat</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formUploadBuktiSertifikasi" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="alert alert-info">
                            Silakan upload file sertifikat asli yang telah Anda terima.
                        </div>
                        <div class="mb-3">
                            <label class="form-label">File Sertifikat <span class="text-danger">*</span></label>
                            <input type="file" name="bukti_sertifikasi" class="form-control" required accept=".pdf,.jpg,.jpeg,.png">
                            <small class="text-muted">Format: PDF, JPG, PNG. Maks: 5MB</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Upload</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Create Pelatihan --}}
    <div class="modal fade" id="createPelatihanModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Pelatihan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('pelatihan.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            {{-- Input Data Pelatihan --}}
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nama Pelatihan</label>
                                <input type="text" name="nama_pelatihan" class="form-control" required>
                            </div>
                            {{-- <div class="col-md-6 mb-3">
                                <label class="form-label">Penyedia</label>
                                <input type="text" name="penyedia" class="form-control" required>
                            </div> --}}
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Vendor</label>
                                <input type="text" name="vendor" class="form-control" required>
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

                            {{-- TOGGLE --}}
                            <div class="col-md-12 mb-3">
                                <div class="d-flex p-3 border rounded bg-light align-items-start">
                                    <div class="form-check form-switch me-3">
                                        <input class="form-check-input" type="checkbox" id="is_sertifikasi_toggle" name="is_sertifikasi" value="1" style="width: 3em; height: 1.5em; cursor: pointer;">
                                    </div>
                                    <div style="flex: 1;">
                                        <label class="form-check-label fw-bold text-dark" for="is_sertifikasi_toggle" style="cursor: pointer;">
                                            Juga sebagai Sertifikasi?
                                        </label>
                                        <div class="text-muted small mt-1" style="line-height: 1.3;">
                                            Jika diaktifkan, form tambahan akan muncul untuk data sertifikasi.
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- FORM TAMBAHAN (Hidden by Default) --}}
                            <div id="sertifikasi_inputs" class="col-md-12" style="display: none;">
                                <div class="card bg-light border-primary mb-3">
                                    <div class="card-body">
                                        <h6 class="card-title text-primary fw-bold mb-3">Detail Sertifikasi</h6>
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Nama Sertifikat <span class="text-danger">*</span></label>
                                                <input type="text" name="nama_sertifikat_manual" id="input_nama_sertifikat" class="form-control" placeholder="Masukkan nama sertifikat...">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Tanggal Ujian</label>
                                                <input type="date" name="tgl_ujian_sertifikasi" id="input_tgl_ujian" class="form-control">
                                            </div>
                                        </div>
                                    </div>
                                </div>
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

    {{-- Modal Approval --}}
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

    {{-- Modal Edit Sertifikasi --}}
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
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nama Sertifikat</label>
                                <input type="text" name="nama_sertifikat" id="edit_nama_sertifikat" class="form-control" required>
                            </div>
                            {{-- <div class="col-md-6 mb-3">
                                <label class="form-label">Penyedia</label>
                                <input type="text" name="penyedia" id="edit_penyedia" class="form-control" required>
                            </div> --}}
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
                                <input type="date" name="tanggal_berlaku_dari" id="edit_tanggal_berlaku_dari" class="form-control">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Berlaku Sampai</label>
                                <input type="date" name="tanggal_berlaku_sampai" id="edit_tanggal_berlaku_sampai" class="form-control">
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Keterangan</label>
                                <textarea name="keterangan" id="edit_keterangan_sertifikasi" class="form-control" rows="3"></textarea>
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

    {{-- Modal Edit Pelatihan --}}
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
                            {{-- <div class="col-md-6 mb-3">
                                <label class="form-label">Penyedia</label>
                                <input type="text" name="penyedia" id="edit_penyedia_pelatihan" class="form-control" required>
                            </div> --}}
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Vendor</label>
                                <input type="text" name="vendor" id="edit_penyedia_vendor" class="form-control" required>
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
                                <textarea name="keterangan" id="edit_keterangan_pelatihan" class="form-control" rows="3"></textarea>
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

                {{-- TAB : Sertifikasi --}}
                <div class="tab-pane fade show active" id="sertifikasi" role="tabpanel">
                    <div class="card mt-3 border-top-0">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h3 class="card-title">Data Sertifikasi</h3>
                                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createSertifikasiModal">
                                    <img src="{{ asset('icon/plus.svg') }}" width="20px"> Tambah Sertifikasi
                                </button>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-striped" id="tableSertifikasi">
                                    <thead>
                                        <tr>
                                            <th>Tanggal Dibuat</th>
                                            <th>Nama Karyawan</th>
                                            <th>Nama Sertifikat</th>
                                            <th>Vendor
                                            <th>Tgl Ujian</th>
                                            <th>Masa Berlaku</th>
                                            <th>Harga</th>
                                            <th>Keterangan</th>
                                            <th>Status</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($sertifikasis as $item)
                                        @php
                                            $isExpired = $item->tanggal_berlaku_sampai && \Carbon\Carbon::parse($item->tanggal_berlaku_sampai)->endOfDay()->isPast();

                                            // Cek Tracking dari Sertifikasi Langsung
                                            $trackingSertifikasi = $item->pengajuan_barang->tracking->tracking ?? null;

                                            // Cek Tracking dari Pelatihan Terkait (Jika via Pelatihan)
                                            $trackingPelatihan = $item->pelatihan->pengajuan_barang->tracking->tracking ?? null;

                                            // Ambil salah satu tracking yang tersedia
                                            $finalTracking = $trackingSertifikasi ?? $trackingPelatihan;

                                            // Ambil ID Pengajuan Barang untuk link detail
                                            $idPengajuan = $item->pengajuan_barang->id ?? ($item->pelatihan->pengajuan_barang->id ?? null);
                                        @endphp

                                        <tr>
                                            <td>{{ \Carbon\Carbon::parse($item->created_at)->translatedFormat('d F Y') }}</td>
                                            <td>{{ $item->user->karyawan->nama_lengkap ?? '-' }}</td>
                                            <td>{{ $item->nama_sertifikat }}</td>
                                            <td>{{ $item->vendor }}</td>
                                            <td>
                                                @if($item->tanggal_ujian)
                                                    {{ \Carbon\Carbon::parse($item->tanggal_ujian)->translatedFormat('d F Y') }}
                                                @else
                                                    <span class="badge bg-secondary">Belum Diisi</span>
                                                @endif
                                            </td>
                                            <td class="{{ $isExpired ? 'text-danger fw-bold' : '' }}">
                                                @if($item->tanggal_berlaku_dari)
                                                    {{ \Carbon\Carbon::parse($item->tanggal_berlaku_dari)->translatedFormat('d F Y') }} -
                                                    {{ $item->tanggal_berlaku_sampai ? \Carbon\Carbon::parse($item->tanggal_berlaku_sampai)->translatedFormat('d F Y') : 'Seumur Hidup' }}

                                                    @if($isExpired)
                                                        <div style="font-size: 0.8em;">(Kadaluarsa)</div>
                                                    @endif
                                                @else
                                                    <span class="badge bg-secondary">Belum Diisi</span>
                                                @endif
                                            </td>
                                            <td>Rp {{ number_format($item->harga, 0, ',', '.') }}</td>
                                            <td>{{($item->keterangan) }}</td>

                                            <td>
                                                @if($finalTracking)
                                                    <small class="d-block text-bold" style="font-size: 11px; line-height: 1.2;">
                                                        {{ $finalTracking }}
                                                    </small>
                                                @else
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
                                                @endif
                                            </td>
                                            <td>
                                                <div class="dropdown">
                                                    <button class="btn btn-sm btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">Actions</button>
                                                    <div class="dropdown-menu">
                                                        @if($idPengajuan)
                                                            <a class="dropdown-item" href="{{ url('/pengajuanbarang/' . $idPengajuan) }}" target="_blank">
                                                                <img src="{{ asset('icon/eye.svg') }}" width="16px"> Detail di Pengajuan Barang
                                                            </a>
                                                            <li><hr class="dropdown-divider"></li>
                                                        @endif
                                                        @if($item->status_approval === 'approved')
                                                            @if($item->bukti_sertifikasi)
                                                                <a class="dropdown-item" href="{{ asset('storage/' . $item->bukti_sertifikasi) }}" target="_blank">
                                                                    <img src="{{ asset('icon/file-text.svg') }}" width="16px"> Lihat Bukti Sertifikat
                                                                </a>
                                                                @if(auth()->id() == $item->user_id)
                                                                    <button class="dropdown-item" onclick="openUploadBuktiSertifikasiModal('{{ $item->id }}')">
                                                                        <img src="{{ asset('icon/upload.svg') }}" width="16px"> Ganti Bukti Sertifikat
                                                                    </button>
                                                                @endif
                                                            @else
                                                                @if(auth()->id() == $item->user_id)
                                                                    <button class="dropdown-item" onclick="openUploadBuktiSertifikasiModal('{{ $item->id }}')">
                                                                        <img src="{{ asset('icon/upload.svg') }}" width="16px"> Upload Bukti Sertifikat
                                                                    </button>
                                                                @endif
                                                            @endif
                                                            <li><hr class="dropdown-divider"></li>
                                                        @endif
                                                        @if(auth()->user()->karyawan->jabatan === 'Education Manager' && $item->status_approval === 'pending')
                                                            @if($item->pelatihan)
                                                                <span class="dropdown-item-text text-muted fst-italic" style="font-size: 11px; max-width: 200px; white-space: normal;">
                                                                    <i class="bi bi-info-circle"></i> Approval wajib dilakukan via menu <strong>Pelatihan</strong>.
                                                                </span>
                                                            @else
                                                                <button class="dropdown-item" onclick="openApproveModal('{{ route('sertifikasi.approve', $item->id) }}')">
                                                                    <img src="{{ asset('icon/check-circle.svg') }}"> Approval
                                                                </button>
                                                            @endif
                                                            <li><hr class="dropdown-divider"></li>
                                                        @endif
                                                            <button class="dropdown-item" onclick='openEditSertifikasi(@json($item))'>
                                                                <img src="{{ asset('icon/edit.svg') }}" width="16px"> Edit
                                                            </button>
                                                            <form action="{{ route('sertifikasi.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Hapus data ini?');">
                                                                @csrf @method('DELETE')
                                                                <button type="submit" class="dropdown-item text-danger">
                                                                    <img src="{{ asset('icon/trash-danger.svg') }}"> Hapus
                                                                </button>
                                                            </form>
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
                                            <th>Vendor</th>
                                            <th>Pelaksanaan</th>
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
                                            <td>{{ $item->vendor }}</td>
                                            <td>
                                                {{ \Carbon\Carbon::parse($item->tanggal_mulai)->translatedFormat('d F Y') }}
                                                -
                                                {{ \Carbon\Carbon::parse($item->tanggal_selesai)->translatedFormat('d F Y') }}
                                            </td>

                                            <td>{{($item->keterangan) }}</td>
                                            <td>Rp {{ number_format($item->harga, 0, ',', '.') }}</td>
                                            <td>
                                                @if($item->pengajuan_barang && $item->pengajuan_barang->tracking)
                                                    <small class="d-block text-bold" style="font-size: 11px; line-height: 1.2;">
                                                        {{ $item->pengajuan_barang->tracking->tracking }}
                                                    </small>
                                                @else
                                                    @if($item->status_approval == 'approved')
                                                        <span class="badge bg-success">Approved</span>
                                                    @elseif($item->status_approval == 'rejected')
                                                        <span class="badge bg-danger">Rejected</span>
                                                    @else
                                                        <span class="badge bg-warning text-dark">Pending</span>
                                                    @endif
                                                @endif
                                            </td>
                                            <td>
                                                <div class="dropdown">
                                                    <button class="btn btn-sm btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">Actions</button>
                                                    <div class="dropdown-menu">
                                                        @if($item->pengajuan_barang)
                                                            <a class="dropdown-item" href="{{ url('/pengajuanbarang/' . $item->pengajuan_barang->id) }}" target="_blank">
                                                                <img src="{{ asset('icon/eye.svg') }}" width="16px"> Detail di Pengajuan Barang
                                                            </a>
                                                            <li><hr class="dropdown-divider"></li>
                                                        @endif
                                                        @if($item->status_approval === 'approved')
                                                            @if($item->bukti_pelatihan)
                                                                <a class="dropdown-item" href="{{ asset('storage/' . $item->bukti_pelatihan) }}" target="_blank">
                                                                    <img src="{{ asset('icon/file-text.svg') }}" width="16px"> Lihat Bukti Pelatihan
                                                                </a>
                                                                @if(auth()->id() == $item->user_id)
                                                                    <button class="dropdown-item" onclick="openUploadBuktiModal('{{ $item->id }}')">
                                                                        <img src="{{ asset('icon/upload.svg') }}" width="16px"> Ganti Bukti Pelatihan
                                                                    </button>
                                                                @endif
                                                            @else
                                                                @if(auth()->id() == $item->user_id)
                                                                    <button class="dropdown-item" onclick="openUploadBuktiModal('{{ $item->id }}')">
                                                                        <img src="{{ asset('icon/upload.svg') }}" width="16px"> Upload Bukti Pelatihan
                                                                    </button>
                                                                @endif
                                                            @endif
                                                            <li><hr class="dropdown-divider"></li>
                                                        @endif
                                                        @if(auth()->user()->karyawan->jabatan === 'Education Manager' && $item->status_approval === 'pending')
                                                            <button class="dropdown-item" onclick="openApproveModal('{{ route('pelatihan.approve', $item->id) }}')">
                                                                <img src="{{ asset('icon/check-circle.svg') }}"> Approval
                                                            </button>
                                                        @endif

                                                            <button class="dropdown-item" onclick='openEditPelatihan(@json($item))'>
                                                                <img src="{{ asset('icon/edit.svg') }}" width="16px"> Edit
                                                            </button>
                                                            <form action="{{ route('pelatihan.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Hapus data ini?');">
                                                                @csrf @method('DELETE')
                                                                <button type="submit" class="dropdown-item text-danger">
                                                                    <img src="{{ asset('icon/trash-danger.svg') }}"> Hapus
                                                                </button>
                                                            </form>
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
        if (activeTab) {
            var tabTrigger = document.querySelector('button[data-bs-target="' + activeTab + '"]');
            if (tabTrigger) {
                var tabInstance = new bootstrap.Tab(tabTrigger);
                tabInstance.show();
            }
        }

        $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
            var target = $(e.target).attr('data-bs-target');
            localStorage.setItem('activeTab', target);
        });

        $('#tableSertifikasi').DataTable({
            "order": [[0, "desc"]],
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
                text: errorMessages,
                confirmButtonText: 'Perbaiki'
            });
        @endif

        var $toggle = $('#is_sertifikasi_toggle');
        var $inputs = $('#sertifikasi_inputs');
        var $namaSertifikat = $('#input_nama_sertifikat');
        var $tglUjian = $('#input_tgl_ujian');

        function handleToggleSertifikasi() {
            if ($toggle.is(':checked')) {
                $inputs.slideDown();
                $namaSertifikat.prop('required', true);
                $tglUjian.prop('required', false);
            } else {
                $inputs.slideUp();
                $namaSertifikat.prop('required', false);
                $tglUjian.prop('required', false);
            }
        }
        $toggle.change(handleToggleSertifikasi);
        if ($toggle.length) {
            handleToggleSertifikasi();
        }

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

    function openEditSertifikasi(data) {
        $('#formEditSertifikasi').attr('action', '/development/sertifikasi/' + data.id);
        $('#edit_nama_sertifikat').val(data.nama_sertifikat);
        // $('#edit_penyedia').val(data.penyedia);
        $('#edit_vendor').val(data.vendor);
        $('#edit_harga_sertifikasi').val(data.harga);
        $('#edit_tanggal_ujian').val(data.tanggal_ujian);
        $('#edit_tanggal_berlaku_dari').val(data.tanggal_berlaku_dari);
        $('#edit_tanggal_berlaku_sampai').val(data.tanggal_berlaku_sampai);
        $('#edit_keterangan_sertifikasi').val(data.keterangan);
        $('#editSertifikasiModal').modal('show');
    }

    function openEditPelatihan(data) {
        $('#formEditPelatihan').attr('action', '/development/pelatihan/' + data.id);
        $('#edit_nama_pelatihan').val(data.nama_pelatihan);
        // $('#edit_penyedia_pelatihan').val(data.penyedia);
        $('#edit_penyedia_vendor').val(data.vendor);
        $('#edit_tanggal_mulai').val(data.tanggal_mulai);
        $('#edit_tanggal_selesai').val(data.tanggal_selesai);
        $('#edit_harga_pelatihan').val(data.harga);
        $('#edit_keterangan_pelatihan').val(data.keterangan);
        $('#editPelatihanModal').modal('show');
    }

    function openUploadBuktiModal(id) {
        var url = "/development/pelatihan/" + id + "/upload-bukti";
        $('#formUploadBukti').attr('action', url);
        $('#formUploadBukti').find('input[type="file"]').val('');
        $('#uploadBuktiModal').modal('show');
    }

    function openUploadBuktiSertifikasiModal(id) {
        var url = "/development/sertifikasi/" + id + "/upload-bukti";
        $('#formUploadBuktiSertifikasi').attr('action', url);
        $('#formUploadBuktiSertifikasi').find('input[type="file"]').val('');
        $('#uploadBuktiSertifikasiModal').modal('show');
    }

</script>
@endpush
@endsection
