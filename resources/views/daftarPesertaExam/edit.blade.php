@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="d-flex justify-content-between align-items-center mt-4 mb-2">
                <h3 class="mb-0 fw-bold">{{ __('Detail Dokumentasi Exam') }}</h3>
                <a href="{{ route('daftar-peserta-exam.index') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <h5 class="alert-heading">{{ __('Terjadi Kesalahan!') }}</h5>
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <form action="{{ route('daftar-peserta-exam.update', $registrasi->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">{{ __('Nama Peserta') }}</label>
                                <input type="text" class="form-control" value="{{ $registrasi->peserta?->nama }}" disabled>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">{{ __('Instansi/Perusahaan') }}</label>
                                <input type="text" class="form-control" value="{{ $perusahaan?->nama_perusahaan }}" disabled>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">{{ __('Instruktur') }}</label>
                                <input type="text" class="form-control" value="{{ $instruktur?->nama_lengkap }}" disabled>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">{{ __('Nama Exam/Kelas') }} <span class="text-danger">*</span></label>
                                <input type="text" name="nama_exam" class="form-control @error('nama_exam') is-invalid @enderror" 
                                    value="{{ old('nama_exam', $dokumentasi?->nama_exam) }}" required>
                                @error('nama_exam')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <hr>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label">{{ __('Skor') }}</label>
                                <div class="input-group">
                                    <input type="number" name="skor" class="form-control @error('skor') is-invalid @enderror" 
                                        value="{{ old('skor', $dokumentasi?->skor) }}" step="0.01" min="0" max="100">
                                    @error('skor')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">{{ __('Keterangan Lulus') }}</label>
                                <select name="keterangan_lulus" id="keterangan_lulus" class="form-control">
                                    <option value="" disable hidden>Pilih Keterangan</option>
                                    <option value="lulus" {{ old('keterangan_lulus', $dokumentasi?->keterangan_lulus) == 'lulus' ? 'selected' : '' }}>Lulus</option>
                                    <option value="tidak lulus" {{ old('keterangan_lulus', $dokumentasi?->keterangan_lulus) == 'tidak lulus' ? 'selected' : '' }}>Tidak Lulus</option>
                                </select>
                                @error('keterangan_lulus')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">{{ __('Tanggal Pelaksanaan') }} <span class="text-danger">*</span></label>
                                <input type="date" name="tanggal_pelaksanaan" class="form-control @error('tanggal_pelaksanaan') is-invalid @enderror" 
                                    value="{{ old('tanggal_pelaksanaan', $dokumentasi?->tanggal_perusahaan) }}" required>
                                @error('tanggal_pelaksanaan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <hr>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">{{ __('File Dokumentasi') }}</label>
                                <input type="file" name="dokumentasi" class="form-control @error('dokumentasi') is-invalid @enderror"
                                    accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                                @error('dokumentasi')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">Format: PDF, DOC, DOCX, JPG, PNG (Max. 10MB)</small>
                                @if ($dokumentasi?->dokumentasi)
                                    <div class="mt-2">
                                        <a href="{{ Storage::url($dokumentasi->dokumentasi) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-download"></i> Lihat File Saat Ini
                                        </a>
                                    </div>
                                @endif
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">{{ __('File Invoice') }}</label>
                                <input type="file" name="invoice" class="form-control @error('invoice') is-invalid @enderror"
                                    accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                                @error('invoice')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">Format: PDF, DOC, DOCX, JPG, PNG (Max. 10MB)</small>
                                @if ($dokumentasi?->invoice)
                                    <div class="mt-2">
                                        <a href="{{ Storage::url($dokumentasi->invoice) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-download"></i> Lihat File Saat Ini
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <hr>

                        <div class="row">
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> {{ __('Simpan Perubahan') }}
                                </button>
                                <a href="{{ route('daftar-peserta-exam.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> {{ __('Batal') }}
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .form-label {
        font-weight: 600;
        margin-bottom: 0.5rem;
        color: #333;
    }

    .input-group-text {
        background-color: #f8f9fa;
        border: 1px solid #dee2e6;
    }

    .form-control:disabled, 
    .form-control[readonly] {
        background-color: #e9ecef;
        cursor: not-allowed;
    }

    hr {
        margin: 1.5rem 0;
        border: none;
        border-top: 2px solid #f0f0f0;
    }
</style>
@endsection
