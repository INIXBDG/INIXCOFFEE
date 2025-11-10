@extends('layouts_office.app')

@section('office_contents')
    <div class="container-fluid py-4">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-5">
            <h4 class="mb-0 fw-bold text-dark">Detail Sertifikat</h4>
            <small class="text-muted fw-medium">{{ now()->translatedFormat('l, d F Y') }}</small>
        </div>

        <!-- Alert Success -->
        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm rounded-3 border-0 mb-4" role="alert">
            <div class="d-flex align-items-center">
                <i class="bx bx-check-circle me-2" style="font-size: 1.5rem;"></i>
                <div>{{ session('success') }}</div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        <div class="row">
            <div class="col-lg-8 mx-auto">
                <!-- Success Animation Card -->
                <div class="card shadow-lg border-0 rounded-4 overflow-hidden mb-4">
                    <div class="card-body p-5 text-center">
                        <div class="success-animation mb-4">
                            <div class="avatar-xl mx-auto mb-3 position-relative">
                                <div class="avatar-title rounded-circle bg-success-subtle text-success" 
                                     style="width: 100px; height: 100px; animation: scaleIn 0.5s ease-out;">
                                    <i class="bx bx-check" style="font-size: 60px;"></i>
                                </div>
                            </div>
                        </div>
                        <h4 class="text-success fw-bold mb-2">Sertifikat Berhasil Di-generate!</h4>
                        <p class="text-muted mb-0">PDF sertifikat telah tersimpan dan siap diunduh</p>
                    </div>
                </div>

                <!-- Card Info Sertifikat -->
                <div class="card shadow-lg border-0 rounded-4 overflow-hidden">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="card-title mb-0 fw-semibold">
                            <i class="bx bx-award text-primary me-2" style="font-size: 1.5rem;"></i>
                            Informasi Sertifikat
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <!-- Nomor Sertifikat -->
                        <div class="row mb-4 pb-3 border-bottom">
                            <label class="col-sm-4 col-form-label fw-semibold text-muted">
                                <i class="bx bx-barcode me-2"></i>Nomor Sertifikat
                            </label>
                            <div class="col-sm-8 d-flex align-items-center">
                                <span class="badge bg-primary-subtle text-primary fs-6 px-3 py-2">
                                    <i class="bx bx-file-blank me-1"></i>
                                    {{ $certificate->nomor_sertifikat }}
                                </span>
                            </div>
                        </div>

                        <!-- Nama Peserta -->
                        <div class="row mb-4 pb-3 border-bottom">
                            <label class="col-sm-4 col-form-label fw-semibold text-muted">
                                <i class="bx bx-user me-2"></i>Nama Peserta
                            </label>
                            <div class="col-sm-8 d-flex align-items-center">
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-sm bg-primary bg-opacity-15 rounded-circle me-3">
                                        <span class="text-primary fw-bold">
                                            {{ strtoupper(substr($certificate->nama_peserta, 0, 1)) }}
                                        </span>
                                    </div>
                                    <p class="mb-0 fw-semibold text-dark">{{ $certificate->nama_peserta }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Nama Materi -->
                        <div class="row mb-4 pb-3 border-bottom">
                            <label class="col-sm-4 col-form-label fw-semibold text-muted">
                                <i class="bx bx-book-open me-2"></i>Nama Materi
                            </label>
                            <div class="col-sm-8 d-flex align-items-center">
                                <p class="mb-0 fw-semibold text-dark">{{ $certificate->nama_materi }}</p>
                            </div>
                        </div>

                        <!-- Periode Pelatihan -->
                        <div class="row mb-4 pb-3 border-bottom">
                            <label class="col-sm-4 col-form-label fw-semibold text-muted">
                                <i class="bx bx-calendar me-2"></i>Periode Pelatihan
                            </label>
                            <div class="col-sm-8 d-flex align-items-center">
                                <div>
                                    <span class="badge bg-info-subtle text-info px-3 py-2 me-2">
                                        <i class="bx bx-calendar-event me-1"></i>
                                        {{ \Carbon\Carbon::parse($certificate->tanggal_awal)->translatedFormat('d F Y') }}
                                    </span>
                                    <span class="text-muted">s/d</span>
                                    <span class="badge bg-info-subtle text-info px-3 py-2 ms-2">
                                        <i class="bx bx-calendar-event me-1"></i>
                                        {{ \Carbon\Carbon::parse($certificate->tanggal_akhir)->translatedFormat('d F Y') }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Tanggal Generate -->
                        <div class="row mb-0">
                            <label class="col-sm-4 col-form-label fw-semibold text-muted">
                                <i class="bx bx-time me-2"></i>Tanggal Generate
                            </label>
                            <div class="col-sm-8 d-flex align-items-center">
                                <span class="badge bg-secondary-subtle text-secondary px-3 py-2">
                                    <i class="bx bx-calendar-check me-1"></i>
                                    {{ $certificate->created_at->translatedFormat('d F Y, H:i') }} WIB
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons Footer -->
                    <div class="card-footer bg-light border-top py-3">
                        <div class="d-flex flex-wrap gap-2 justify-content-center">
                            <a href="{{ route('office.certificate.preview', $certificate->id) }}" 
                               class="btn btn-info shadow-sm hover-scale"
                               target="_blank">
                                <i class="bx bx-show me-1"></i>Preview PDF
                            </a>
                            <a href="{{ route('office.certificate.download', $certificate->id) }}" 
                               class="btn btn-success shadow-sm hover-scale">
                                <i class="bx bx-download me-1"></i>Download PDF
                            </a>
                            <a href="{{ route('office.certificate.index') }}" 
                               class="btn btn-light shadow-sm">
                                <i class="bx bx-arrow-back me-1"></i>Kembali
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        @keyframes scaleIn {
            0% {
                transform: scale(0);
                opacity: 0;
            }
            50% {
                transform: scale(1.1);
            }
            100% {
                transform: scale(1);
                opacity: 1;
            }
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .card {
            animation: fadeInUp 0.5s ease-out;
        }

        .avatar {
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
        }

        .avatar-sm {
            width: 38px;
            height: 38px;
            font-size: 0.875rem;
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

        .border-bottom {
            border-color: rgba(0, 0, 0, 0.05) !important;
        }

        .row {
            transition: all 0.2s ease;
        }

        .row:hover {
            background-color: rgba(91, 115, 232, 0.02);
            border-radius: 8px;
        }

        @media (max-width: 576px) {
            .col-sm-4, .col-sm-8 {
                text-align: left !important;
            }
            
            .d-flex.flex-wrap {
                flex-direction: column !important;
            }
            
            .btn {
                width: 100%;
            }
        }
    </style>
@endsection