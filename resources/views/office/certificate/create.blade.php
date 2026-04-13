@extends('layouts_office.app')

@section('office_contents')
    <div class="container-fluid py-4">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-5">
            <h4 class="mb-0 fw-bold text-dark">Form Generate Sertifikat</h4>
            <small class="text-muted fw-medium">{{ now()->translatedFormat('l, d F Y') }}</small>
        </div>

        <div class="row g-4">
            <!-- Info RKM -->
            <div class="col-lg-4">
                <div class="card h-100 shadow-lg border-0 rounded-4 overflow-hidden glass-force">
                    <div class="card-header border-bottom py-3">
                        <h5 class="card-title mb-0 fw-semibold">
                            <i class="bx bx-info-circle text-primary me-2" style="font-size: 1.5rem;"></i>
                            Informasi RKM
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-4">
                            <label class="text-muted small text-uppercase mb-2">Materi</label>
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-sm rounded-circle me-3">
                                    <i class="bx bx-book-open text-primary"></i>
                                </div>
                                <div>
                                    <p class="mb-0 fw-semibold text-dark">{{ $rkm->materi->nama_materi ?? '-' }}</p>
                                    <small class="text-muted">{{ $rkm->materi->kode ?? '' }}</small>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="text-muted small text-uppercase mb-2">Perusahaan</label>
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-sm rounded-circle me-3">
                                    <i class="bx bx-buildings text-info"></i>
                                </div>
                                <p class="mb-0 fw-semibold text-dark">{{ $rkm->perusahaan->nama_perusahaan ?? '-' }}</p>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="text-muted small text-uppercase mb-2">Tanggal Pelatihan</label>
                            <div class="d-flex flex-column">
                                <span class="badge bg-info-subtle text-info mb-2 w-100 py-2">
                                    <i class="bx bx-calendar me-1"></i>
                                    {{ \Carbon\Carbon::parse($rkm->tanggal_awal)->format('d M Y') }}
                                </span>
                                <div class="text-center small text-muted my-1">
                                    <i class="bx bx-down-arrow-alt"></i>
                                </div>
                                <span class="badge bg-info-subtle text-info w-100 py-2">
                                    <i class="bx bx-calendar me-1"></i>
                                    {{ \Carbon\Carbon::parse($rkm->tanggal_akhir)->format('d M Y') }}
                                </span>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="text-muted small text-uppercase mb-2">Peserta</label>
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-sm rounded-circle me-3">
                                    <span class="text-success fw-bold">
                                        {{ strtoupper(substr($peserta->nama, 0, 1)) }}
                                    </span>
                                </div>
                                <div>
                                    <p class="mb-0 fw-semibold text-dark">{{ $peserta->nama }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Generate -->
            <div class="col-lg-8">
                <div class="card h-100 shadow-lg border-0 rounded-4 overflow-hidden glass-force">
                    <div class="card-header border-bottom py-3">
                        <h5 class="card-title mb-0 fw-semibold">
                            <i class="bx bx-edit text-primary me-2" style="font-size: 1.5rem;"></i>
                            Data Sertifikat
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        @if ($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show shadow-sm rounded-3 mb-4"
                                role="alert">
                                <div class="d-flex align-items-center">
                                    <i class="bx bx-error-circle me-2" style="font-size: 1.5rem;"></i>
                                    <div>
                                        <strong>Ada kesalahan:</strong>
                                        <ul class="mb-0 mt-1">
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif
                        <form action="{{ route('office.certificate.store') }}" method="POST" id="formGenerate">
                            @csrf
                            <input type="hidden" name="rkm_id" value="{{ old('rkm_id', $rkm->id) }}">
                            @error('rkm_id')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                            <input type="hidden" name="id_peserta" value="{{ old('id_peserta', $peserta->id) }}">
                            @error('id_peserta')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror

                            <!-- Nomor Sertifikat -->
                            <div class="mb-4">
                                <label class="form-label fw-semibold">
                                    Nomor Sertifikat <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="bx bx-user text-primary"></i>
                                    </span>
                                    <input type="text" name="nomor_sertifikat"
                                        class="form-control border-start-0 @error('nomor_sertifikat') is-invalid @enderror"
                                        value="{{ old('nomor_sertifikat', $nomorSertifikatBaru) }}">

                                    @error('nama')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Nama Peserta (Read-only) -->
                            <div class="mb-4">
                                <label class="form-label fw-semibold">
                                    Nama Peserta <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="bx bx-user text-primary"></i>
                                    </span>
                                    <input type="text" name="nama_peserta"
                                        class="form-control border-start-0 @error('nama_peserta') is-invalid @enderror"
                                        value="{{ old('nama_peserta', $peserta->nama) }}">

                                    @error('nama')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Nama Materi (Editable) -->
                            <div class="mb-4">
                                <label class="form-label fw-semibold">
                                    Nama Materi <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="bx bx-book-open text-primary"></i>
                                    </span>
                                    <input type="text" name="nama_materi"
                                        class="form-control border-start-0 @error('nama_materi') is-invalid @enderror"
                                        value="{{ old('nama_materi', $rkm->materi->nama_materi ?? '') }}"
                                        placeholder="Masukkan nama materi">
                                    @error('nama_materi')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <small class="text-muted">
                                    <i class="bx bx-info-circle me-1"></i>
                                    Nama materi bisa diubah sesuai kebutuhan
                                </small>
                            </div>

                            {{-- Periode Pelatihan 1 (Editable) --}}
                            <div class="mb-4">
                                <span class="fw-bold fs-5 d-block mb-3">
                                    Periode 1
                                </span>

                                <div class="row">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">
                                            Tanggal Mulai <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-end-0">
                                                <i class="bx bx-calendar text-primary"></i>
                                            </span>
                                            <input type="date" name="tanggal_awal"
                                                class="form-control border-start-0 @error('tanggal_awal') is-invalid @enderror"
                                                value="{{ old('tanggal_awal', $rkm->tanggal_awal ? \Carbon\Carbon::parse($rkm->tanggal_awal)->format('Y-m-d') : '') }}">
                                            @error('tanggal_awal')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">
                                            Tanggal Selesai <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-end-0">
                                                <i class="bx bx-calendar text-primary"></i>
                                            </span>
                                            <input type="date" name="tanggal_akhir"
                                                class="form-control border-start-0 @error('tanggal_akhir') is-invalid @enderror"
                                                value="{{ old('tanggal_akhir', $rkm->tanggal_akhir ? \Carbon\Carbon::parse($rkm->tanggal_akhir)->format('Y-m-d') : '') }}">
                                            @error('tanggal_akhir')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Periode Pelatihan 2 (Opsional) --}}
                            <div class="mb-4">
                                <span class="fw-bold fs-5 d-block mb-3">
                                    Periode 2
                                </span>

                                <div class="row">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">
                                            Tanggal Mulai <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-end-0">
                                                <i class="bx bx-calendar text-primary"></i>
                                            </span>
                                            <input type="date" name="tanggal_awal2"
                                                class="form-control border-start-0 @error('tanggal_awal2') is-invalid @enderror">
                                            @error('tanggal_awal2')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">
                                            Tanggal Selesai <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-end-0">
                                                <i class="bx bx-calendar text-primary"></i>
                                            </span>
                                            <input type="date" name="tanggal_akhir2"
                                                class="form-control border-start-0 @error('tanggal_akhir2') is-invalid @enderror">
                                            @error('tanggal_akhir2')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="alert alert-info border-0 shadow-sm rounded-3 mb-4">
                                <div class="d-flex align-items-start">
                                    <i class="bx bx-info-circle me-2" style="font-size: 1.5rem;"></i>
                                    <div>
                                        <strong>Informasi:</strong>
                                        <p class="mb-0 small mt-1">
                                            • Nomor sertifikat akan di-generate otomatis<br>
                                            • TTD menggunakan Education Manager<br>
                                            • Periode bisa disesuaikan dengan kebutuhan
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex gap-2 pt-3 border-top">
                                <button type="submit" class="btn btn-primary shadow-sm hover-scale">
                                    <i class="bx bx-file-blank me-1"></i>Generate Sertifikat
                                </button>
                                <a href="{{ route('office.certificate.detail', $rkm->id) }}"
                                    class="btn btn-light shadow-sm">
                                    <i class="bx bx-arrow-back me-1"></i>Kembali
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .avatar {
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
        }

        .avatar-sm {
            width: 38px;
            height: 38px;
            font-size: 1rem;
        }

        .input-group-text {
            border: 1px solid #dee2e6;
        }

        .form-control,
        .form-select {
            border: 1px solid #dee2e6;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #5b73e8;
            box-shadow: 0 0 0 0.2rem rgba(91, 115, 232, 0.25);
        }

        .hover-scale {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .hover-scale:hover {
            transform: translateY(-2px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
        }

        .badge {
            font-size: 0.75rem;
            padding: 0.35em 0.65em;
            font-weight: 500;
        }

        @media (max-width: 991px) {

            .col-lg-4,
            .col-lg-8 {
                margin-bottom: 1rem;
            }
        }
    </style>
@endsection
