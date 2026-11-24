@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">

<style>
    .table-responsive {
        min-height: 250px;
    }
</style>

<div class="container-fluid py-3 px-4">
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bx bx-check-circle me-1"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bx bx-error me-1"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Header Card --}}
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body p-4">
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center gap-3">
                <div>
                    <h3 class="mb-2 fw-bold text-dark">Data Laporan MoM</h3>
                    <p class="text-muted fs-6 mb-0">{{ now()->translatedFormat('l, d F Y') }}</p>
                </div>
                <div>
                    <a class="btn btn-primary px-3 py-2 shadow-sm" href="{{ route('mom.create') }}">
                        <i class="bx bx-plus me-1"></i> Tambah Laporan
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Table Card --}}
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-4">
            <div class="table-responsive">
                <table id="laporanTable" class="table table-bordered table-striped table-hover align-middle w-100">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 50px;">No</th>
                            <th>Jenis Meeting</th>
                            <th>Waktu Pelaksanaan</th>
                            <th>Tanggal Pelaksanaan</th>
                            <th>Topik</th>
                            <th>Jenis Catatan</th>
                            <th style="width: 100px;" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Dimuat melalui DataTables AJAX -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Konfirmasi Hapus -->
<div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold text-danger"><i class="bx bx-trash me-1"></i> Hapus Laporan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center py-3">
                <p class="mb-0">Apakah Anda yakin ingin menghapus laporan MoM ini?</p>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-danger btn-sm" id="confirmDeleteBtn">Ya, Hapus</button>
            </div>
        </div>
    </div>
</div>

@push('js')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script>
    $(document).ready(function() {
        var table = $('#laporanTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('mom.data') }}",
            columns: [
                { data: 'no', name: 'no', orderable: false, searchable: false },
                { data: 'jenis', name: 'jenis_meeting' },
                { data: 'waktu_pelaksanaan', name: 'waktu_pelaksanaan' },
                { data: 'tanggal_pelaksanaan', name: 'tanggal_pelaksanaan' },
                { data: 'topic', name: 'topic' },
                { data: 'jenis_catatan', name: 'jenis_catatan', orderable: false, searchable: false },
                { data: 'aksi', name: 'aksi', orderable: false, searchable: false }
            ],
            language: {
                search: "Cari Topik / Perusahaan:",
                lengthMenu: "Tampilkan _MENU_ data",
                info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                infoEmpty: "Menampilkan 0 sampai 0 dari 0 data",
                zeroRecords: "Tidak ada data laporan MoM ditemukan",
                paginate: {
                    first: "Awal",
                    last: "Akhir",
                    next: "Lanjut",
                    previous: "Kembali"
                }
            },
            createdRow: function(row, data, dataIndex) {
                if (data.is_draft) {
                    $(row).addClass('table-warning');
                }
            }
        });

        let currentDeleteForm = null;
        $('#laporanTable').on('click', '[data-bs-target="#confirmDeleteModal"]', function() {
            currentDeleteForm = $(this).closest('.delete-form');
        });

        $('#confirmDeleteBtn').on('click', function() {
            if (currentDeleteForm) {
                currentDeleteForm.submit();
            }
        });
    });
</script>
@endpush
@endsection
