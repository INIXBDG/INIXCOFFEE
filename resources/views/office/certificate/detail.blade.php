@extends('layouts_office.app')

@section('office_contents')
<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <h4 class="mb-1 fw-bold text-dark">Detail RKM & Peserta</h4>
            <p class="text-muted mb-0">{{ $rkm->materi->nama_materi ?? '-' }}</p>
        </div>
        <small class="text-muted fw-medium">{{ now()->translatedFormat('l, d F Y') }}</small>
    </div>

    <div class="row g-4 mb-4">
        <!-- Info RKM Card -->
        <div class="col-12">
            <div class="card shadow-lg border-0 rounded-4 overflow-hidden">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="card-title mb-0 fw-semibold">
                        <i class="bx bx-info-circle text-primary me-2" style="font-size: 1.5rem;"></i>
                        Informasi RKM
                    </h5>
                </div>
                <div class="card-body p-4">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="text-muted small text-uppercase mb-1">Materi</label>
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-sm rounded-circle me-2">
                                        <i class="bx bx-book-open text-primary"></i>
                                    </div>
                                    <div>
                                        <p class="mb-0 fw-semibold text-dark">{{ $rkm->materi->nama_materi ?? '-' }}</p>
                                        <small class="text-muted">{{ $rkm->materi->kode ?? '' }}</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="text-muted small text-uppercase mb-1">Perusahaan</label>
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-sm rounded-circle me-2">
                                        <i class="bx bx-buildings text-info"></i>
                                    </div>
                                    <p class="mb-0 fw-semibold text-dark">{{ $rkm->perusahaan->nama_perusahaan ?? '-' }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="text-muted small text-uppercase mb-1">Periode</label>
                                <div>
                                    <span class="badge bg-info-subtle text-info">
                                        <i class="bx bx-calendar me-1"></i>
                                        {{ \Carbon\Carbon::parse($rkm->tanggal_awal)->format('d M Y') }}
                                    </span>
                                    <span class="mx-1">-</span>
                                    <span class="badge bg-info-subtle text-info">
                                        {{ \Carbon\Carbon::parse($rkm->tanggal_akhir)->format('d M Y') }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="text-muted small text-uppercase mb-1">Status</label>
                                <div>
                                    @if($rkm->status == '0')
                                    <span class="badge bg-warning-subtle text-warning px-3 py-2">
                                        <i class="bx bx-time-five me-1"></i>Belum Selesai
                                    </span>
                                    @else
                                    <span class="badge bg-success-subtle text-success px-3 py-2">
                                        <i class="bx bx-check-circle me-1"></i>Selesai
                                    </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- List Peserta -->
    <div class="row g-3">
        <div class="col-12">
            <div class="card shadow-lg border-0 rounded-4 overflow-hidden">
                <div class="card-header bg-white border-bottom py-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <h5 class="card-title mb-0 fw-semibold">
                            <i class="bx bx-user-check text-primary me-2" style="font-size: 1.5rem;"></i>
                            Daftar Peserta
                        </h5>
                        <div>
                            <span class="badge bg-primary-subtle text-primary me-2">
                                Total: {{ $peserta->count() }} Peserta
                            </span>
                            <span class="badge bg-success-subtle text-success">
                                Sudah Generate: {{ count($certificateIds) }}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="sticky-top">
                                <tr>
                                    <th class="border-0 ps-4" style="min-width: 60px;">No</th>
                                    <th class="border-0" style="min-width: 250px;">Nama Peserta</th>
                                    <th class="border-0 text-center" style="min-width: 120px;">Status</th>
                                    <th class="border-0 text-center pe-4" style="min-width: 180px;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($peserta as $index => $p)
                                @php
                                    $hasCertificate = in_array($p->id_peserta, $certificateIds);
                                @endphp
                                <tr class="border-bottom hover-bg">
                                    <td class="ps-4">
                                        <span class="fw-medium text-muted">{{ $index + 1 }}</span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-sm rounded-circle me-3">
                                                <span class="text-primary fw-bold">
                                                    {{ strtoupper(substr($p->nama, 0, 1)) }}
                                                </span>
                                            </div>
                                            <div>
                                                <div class="fw-semibold text-dark">{{ $p->nama }}</div>
                                                <small class="text-muted">{{ $p->email ?? '-' }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        @if($hasCertificate)
                                        <span class="badge bg-success-subtle text-success px-3 py-2">
                                            <i class="bx bx-check-circle me-1"></i>Sudah Generate
                                        </span>
                                        @else
                                        <span class="badge bg-warning-subtle text-warning px-3 py-2">
                                            <i class="bx bx-time-five me-1"></i>Belum Generate
                                        </span>
                                        @endif
                                    </td>
                                    <td class="text-center pe-4">
                                        @if($hasCertificate)
                                        <!-- Tombol Download -->
                                        <a href="{{ route('office.certificate.downloadByPeserta', ['rkm_id' => $rkm->id, 'peserta_id' => $p->id_peserta]) }}" 
                                           class="btn btn-sm btn-success shadow-sm hover-scale">
                                            <i class="bx bx-download me-1"></i>Download PDF
                                        </a>
                                        <a href="{{ route('office.certificate.create', ['rkm_id' => $rkm->id, 'peserta_id' => $p->id_peserta]) }}" 
                                           class="btn btn-sm btn-primary shadow-sm hover-scale">
                                            <i class="bx bx-file-blank me-1"></i>Generate +
                                        </a>
                                        @else
                                        <!-- Tombol Generate -->
                                        <a href="{{ route('office.certificate.create', ['rkm_id' => $rkm->id, 'peserta_id' => $p->id_peserta]) }}" 
                                           class="btn btn-sm btn-primary shadow-sm hover-scale">
                                            <i class="bx bx-file-blank me-1"></i>Generate
                                        </a>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center py-5">
                                        <div class="d-flex flex-column align-items-center">
                                            <i class="bx bx-user-x text-muted" style="font-size: 4rem; opacity: 0.5;"></i>
                                            <p class="text-muted mt-3 mb-0 fw-medium">Tidak ada data peserta</p>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Footer -->
                <div class="card-footer border-top py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <a href="{{ route('office.certificate.index') }}" class="btn btn-light shadow-sm">
                            <i class="bx bx-arrow-back me-1"></i>Kembali
                        </a>
                        <div class="text-muted small">
                            Total {{ $peserta->count() }} peserta terdaftar
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .hover-bg:hover {
        background-color: rgba(91, 115, 232, 0.05) !important;
        transition: all 0.3s ease;
    }

    .hover-scale {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .hover-scale:hover {
        transform: translateY(-2px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
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

    .table > :not(caption) > * > * {
        padding: 1rem 0.75rem;
    }

    .badge {
        font-size: 0.75rem;
        padding: 0.35em 0.65em;
        font-weight: 500;
    }

    /* Custom Scrollbar */
    .table-responsive::-webkit-scrollbar {
        width: 8px;
        height: 8px;
    }

    .table-responsive::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }

    .table-responsive::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 10px;
    }

    .table-responsive::-webkit-scrollbar-thumb:hover {
        background: #a8a8a8;
    }

    /* Sticky Header Shadow */
    .sticky-top {
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }
</style>
@endsection