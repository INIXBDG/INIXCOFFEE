@extends('layouts_crm.app')

@section('crm_contents')
    <div class="container mt-3">

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-body p-4">
                <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center gap-4">
                    <div>
                        <h3 class="mb-2 fw-bold text-dark">Data Laporan MoM</h3>
                        <p class="text-muted fs-6 mb-0">{{ now()->translatedFormat('l, d F Y') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <a class="btn btn-primary mb-3" href="{{ route('laporan.harian.create') }}">
            <i class="bx bx-plus"></i> Tambah Laporan
        </a>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <h5 class="mb-3 fw-bold">Daftar Laporan MoM</h5>
                <div class="table-responsive">
                    <table id="laporanTable" class="table table-bordered table-striped table-hover w-100">
                        <thead class="table-secondary">
                            <tr>
                                <th>No</th>
                                <th>Jenis</th>
                                <th>Waktu Pelaksanaan</th>
                                <th>Tanggal Pelaksanaan</th>
                                <th>Topik</th>
                                <th>Jenis Catatan</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Data akan dimuat lewat DataTables AJAX -->
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
                    <h5 class="modal-title">Konfirmasi Hapus</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Yakin ingin menghapus laporan ini?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Hapus</button>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
<script>
    $(document).ready(function() {
        // 1. Inisialisasi DataTables
        $('#laporanTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('laporan.harian.data') }}",
            columns: [
                { data: 'no', name: 'no', orderable: false, searchable: false },
                { data: 'jenis', name: 'jenis_meeting' },
                { data: 'waktu_pelaksanaan', name: 'waktu_pelaksanaan' },
                { data: 'tanggal_pelaksanaan', name: 'tanggal_pelaksanaan' },
                { data: 'topic', name: 'topic' },
                { data: 'jenis_catatan', name: 'jenis_catatan', orderable: false, searchable: false },
                { data: 'aksi', name: 'aksi', orderable: false, searchable: false }
            ],
            createdRow: function(row, data, dataIndex) {
                // Memberikan kelas warna kuning jika status draft
                if (data.is_draft) {
                    $(row).addClass('table-warning');
                }
            }
        });

        // 2. Event Listener untuk Modal Hapus (Event Delegation)
        let currentDeleteForm = null;

        // Menangkap event klik pada tombol hapus secara dinamis di dalam DataTables
        $('#laporanTable').on('click', '[data-bs-target="#confirmDeleteModal"]', function() {
            // Mencari elemen <form> terdekat dari tombol yang diklik
            currentDeleteForm = $(this).closest('.delete-form');
        });

        // Eksekusi submit form saat tombol 'Hapus' di dalam modal ditekan
        $('#confirmDeleteBtn').on('click', function() {
            if (currentDeleteForm) {
                currentDeleteForm.submit();
            }
        });
    });
</script>

@endsection




