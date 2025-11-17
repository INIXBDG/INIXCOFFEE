@extends('layouts_office.app')

@section('office_contents')
    <div class="container-fluid py-4">
        <!-- Modal -->
        <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="exampleModalLabel">Tambah Data Vendor</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="{{url('/office/vendor/' . $itemValue)}}" method="post">
                        @csrf
                        @method('POST')
                         <div class="mb-3">
                            <label for="nama" class="form-label">Nama</label>
                            <input type="text" name="nama" id="nama" class="form-control">
                         </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save changes</button>
                    </form>
                </div>
                </div>
            </div>
        </div>
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-5">
            <h4 class="mb-0 fw-bold text-dark">Data Vendor {{$itemValue}}</h4>
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

        <!-- Card List Vendor -->
        <div class="row g-3">
            <div class="col-12">
                <div class="card h-100 shadow-lg border-0 rounded-4 overflow-hidden">
                    <div class="card-header bg-white border-bottom py-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#exampleModal">Tambah Vendor {{$itemValue}}</button>
                            <span class="badge bg-primary-subtle text-primary">0 Data</span>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th class="border-0 ps-4" style="min-width: 60px;">No</th>
                                        <th class="border-0" style="min-width: 250px;">Nama Vendor</th>
                                        <th class="border-0 text-center" style="min-width: 200px;">Status</th>
                                        <th class="border-0 text-center pe-4" style="min-width: 180px;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {{-- {{$data}} --}}
                                    @forelse($data as $index => $item)
                                    <tr class="border-bottom hover-bg">
                                        <td class="ps-4">
                                            <span class="fw-medium text-muted">{{ $index+1 }}</span>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="fw-semibold text-dark">{{ $item->nama ?? '-' }}</div>
                                            </div>
                                        </td>
                                    
                                        <td class="text-center">
                                            @if($item->is_active == '0')
                                            <span class="badge bg-warning-subtle text-warning px-3 py-2">
                                                <i class="bx bx-time-five me-1">Tidak Aktif</i>
                                            </span>
                                            @else
                                            <span class="badge bg-success-subtle text-success px-3 py-2">
                                                <i class="bx bx-check-circle me-1"></i>Selesai
                                            </span>
                                            @endif
                                        </td>
                                        <td class="text-center pe-4">
                                            <form onsubmit="return confirm('Apakah Anda Yakin ?')" action="{{ url('/office/vendor/' . $itemValue .'/'. $item->id) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="dropdown-item"><img src="{{ asset('icon/trash-danger.svg') }}" class=""> Hapus</button>
                                            </form>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-5">
                                            <div class="d-flex flex-column align-items-center">
                                                <i class="bx bx-info-circle text-muted" style="font-size: 4rem; opacity: 0.5;"></i>
                                                <p class="text-muted mt-3 mb-0 fw-medium">Tidak ada data Vendor untuk ditampilkan</p>
                                                <small class="text-muted">Belum ada data Vendor yang tersedia</small>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Pagination -->
                    @if($data->hasPages())
                    <div class="card-footer bg-light border-top py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="text-muted small">
                                Menampilkan {{ $data->firstItem() }} - {{ $data->lastItem() }} dari {{ $data->total() }} data
                            </div>
                            <div>
                                {{ $data->links() }}
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