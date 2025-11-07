@extends('layouts_office.app')

@section('office_contents')
    <div class="container-fluid py-4">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-5">
            <h4 class="mb-0 fw-bold text-dark">Generate Sertifikat</h4>
            <small class="text-muted fw-medium">{{ now()->translatedFormat('l, d F Y') }}</small>
        </div>

        <!-- Alert Success -->
        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm rounded-3 border-0" role="alert">
            <div class="d-flex align-items-center">
                <i class="bx bx-check-circle me-2" style="font-size: 1.5rem;"></i>
                <div>{{ session('success') }}</div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        <!-- Card List RKM -->
        <div class="row g-3">
            <div class="col-12">
                <div class="card h-100 shadow-lg border-0 rounded-4 overflow-hidden">
                    <div class="card-header bg-white border-bottom py-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <h5 class="card-title mb-0 fw-semibold">
                                <i class="bx bx-list-ul text-primary me-2" style="font-size: 1.5rem;"></i>
                                Pilih RKM untuk Generate Sertifikat
                            </h5>
                            <span class="badge bg-primary-subtle text-primary">{{ $rkm->total() }} Data</span>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th class="border-0 ps-4" style="min-width: 60px;">No</th>
                                        <th class="border-0" style="min-width: 250px;">Materi</th>
                                        <th class="border-0" style="min-width: 200px;">Perusahaan</th>
                                        <th class="border-0" style="min-width: 200px;">Tanggal Pelatihan</th>
                                        <th class="border-0 text-center" style="min-width: 120px;">Status</th>
                                        <th class="border-0 text-center pe-4" style="min-width: 180px;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($rkm as $index => $item)
                                    <tr class="border-bottom hover-bg">
                                        <td class="ps-4">
                                            <span class="fw-medium text-muted">{{ $rkm->firstItem() + $index }}</span>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar avatar-sm bg-primary bg-opacity-15 rounded-circle me-3">
                                                    <i class="bx bx-book-open text-primary"></i>
                                                </div>
                                                <div>
                                                    <div class="fw-semibold text-dark">{{ $item->materi->nama_materi ?? '-' }}</div>
                                                    <small class="text-muted">{{ $item->materi->kode ?? '' }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="text-truncate" style="max-width: 200px;" data-bs-toggle="tooltip" title="{{ $item->perusahaan->nama ?? '-' }}">
                                                <i class="bx bx-buildings text-muted me-1"></i>
                                                {{ $item->perusahaan->nama_perusahaan ?? '-' }}
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-column small">
                                                <span class="badge bg-info-subtle text-info mb-1">
                                                    <i class="bx bx-calendar me-1"></i>
                                                    {{ \Carbon\Carbon::parse($item->tanggal_awal)->format('d M Y') }}
                                                </span>
                                                <span class="text-muted small">
                                                    <i class="bx bx-right-arrow-alt me-1"></i>
                                                    {{ \Carbon\Carbon::parse($item->tanggal_akhir)->format('d M Y') }}
                                                </span>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            @if($item->status == '0')
                                            <span class="badge bg-warning-subtle text-warning px-3 py-2">
                                                <i class="bx bx-time-five me-1"></i>Belum Selesai
                                            </span>
                                            @else
                                            <span class="badge bg-success-subtle text-success px-3 py-2">
                                                <i class="bx bx-check-circle me-1"></i>Selesai
                                            </span>
                                            @endif
                                        </td>
                                        <td class="text-center pe-4">
                                            <a href="{{ route('office.certificate.detail', $item->id) }}" 
                                               class="btn btn-sm btn-info shadow-sm hover-scale">
                                                <i class="bx bx-detail me-1"></i>Lihat Detail
                                            </a>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-5">
                                            <div class="d-flex flex-column align-items-center">
                                                <i class="bx bx-info-circle text-muted" style="font-size: 4rem; opacity: 0.5;"></i>
                                                <p class="text-muted mt-3 mb-0 fw-medium">Tidak ada data RKM untuk ditampilkan</p>
                                                <small class="text-muted">Belum ada data RKM yang tersedia</small>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Pagination -->
                    @if($rkm->hasPages())
                    <div class="card-footer bg-light border-top py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="text-muted small">
                                Menampilkan {{ $rkm->firstItem() }} - {{ $rkm->lastItem() }} dari {{ $rkm->total() }} data
                            </div>
                            <div>
                                {{ $rkm->links() }}
                            </div>
                        </div>
                    </div>
                    @endif
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
            font-size: 1rem;
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

    <!-- Initialize Tooltips -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Bootstrap Tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });
    </script>
@endsection