@extends('layouts_office.app')

@section('office_contents')
<div class="container mt-4">

    <div class="card shadow-sm glass-force">
        
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Detail Administrasi Karyawan</h5>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger mt-3 mx-3">
                <div class="fw-bold mb-2">
                    <i class="bi bi-exclamation-triangle-fill me-1"></i>
                    Terjadi kesalahan:
                </div>

                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card-body">

            <form method="POST" action="{{ route('administrasi.karyawan.update', $administrasi->id) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="row g-3"> 

                    <div class="col-md-6">
                        <label class="form-label text-uppercase">Administrasi Karyawan</label>
                        <input type="text" name="nama_administrasi" class="form-control" value="{{ $administrasi->nama_administrasi }}">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label text-uppercase">Dateline</label>
                        <input type="date" name="dateline" class="form-control" value="{{ $administrasi->dateline }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-uppercase">Tanggal Selesai</label>
                        <input type="date" name="tanggal_selesai" class="form-control" value="{{ $administrasi->tanggal_selesai }}">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label text-muted text-uppercase">Status</label>
                        <select name="status" id="status" class="form-select">
                            <option value="pending" {{ $administrasi->status === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="proses" {{ $administrasi->status === 'proses' ? 'selected' : '' }}>Proses</option>
                            <option value="selesai" {{ $administrasi->status === 'selesai' ? 'selected' : '' }}>Selesai</option>
                            <option value="terlambat" {{ $administrasi->status === 'terlambat' ? 'selected' : '' }}>Terlambat</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label text-uppercase">Bukti Transfer</label>
                        <input type="file" name="bukti_transfer" class="form-control mb-2">
                        @if ($administrasi->bukti_transfer)
                            <a href="/storage/{{ $administrasi->bukti_transfer }}" target="_blank">
                                Lihat Bukti Transfer
                            </a>
                        @else
                            <span class="text-muted ">Tidak ada bukti transfer</span>
                        @endif
                    </div>

                    <div class="col-6">
                        <label class="form-label text-uppercase">Catatan</label>
                        <textarea name="catatan" class="form-control" rows="3">{{ $administrasi->catatan }}</textarea>
                    </div>

                </div>

                <div class="mt-4 d-flex justify-content-end gap-2">
                    <a href="{{ url()->previous() }}" class="btn btn-secondary">Kembali</a>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>

            </form>

        </div>
    </div>

</div>

@endsection