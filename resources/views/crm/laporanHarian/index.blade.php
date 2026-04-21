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
                <table class="table table-bordered table-striped table-hover">
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

                        @forelse($laporans as $laporan)
                            
                            <tr  @if($laporan->is_draft) class="table-warning" @endif>
                                <td>{{ ($laporans->currentPage() - 1) * $laporans->perPage() + $loop->iteration }}</td>
                                <td>
                                    <div class="row ps-3">
                                        {{ $laporan->jenis_meeting }}
                                        <ul>
                                            @foreach ($laporan->catatanClient as $client)
                                                <li>{{ $client->nama_perusahaan }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </td>
                                <td>{{ \Carbon\Carbon::parse($laporan->waktu_pelaksanaan)->format('H:i') }}</td>
                                <td>{{ \Carbon\Carbon::parse($laporan->tanggal_pelaksanaan)->format('l, d F Y') }}</td>
                                <td>{{ $laporan->topic }}</td>
                                <td>
                                    <div class="d-flex flex-column gap-1">
                                        @if (count($laporan->catatanClient) > 0)
                                            <span class="badge bg-info">Catatan Client</span>
                                            @if (count($laporan->catatanSales) > 0)
                                                <span class="badge bg-secondary">Catatan Sales</span>
                                            @endif
                                        @elseif (count($laporan->catatanSales) > 0)
                                            <span class="badge bg-secondary">Catatan Sales</span>
                                        @else
                                            <span>-</span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <a href="{{ route('laporan.harian.edit', $laporan->id) }}" class="btn btn-sm btn-warning">
                                        <i class="bx bx-edit"></i> Edit
                                    </a>
                                    <form method="POST" action="{{ route('laporan.harian.delete', $laporan->id) }}" style="display: inline;" class="delete-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#confirmDeleteModal" data-action="{{ route('laporan.harian.delete', $laporan->id) }}">
                                            <i class="bx bx-trash"></i> Hapus
                                        </button>
                                    </form>
                                    @if (!$laporan->is_draft)
                                        @if (count($laporan->catatanClient) > 0)
                                            <a href="{{ route('laporan.harian.pdf', ['id'=>$laporan->id,'type'=>'client']) }}" class="btn btn-sm btn-success">
                                                <i class="bx bx-file"></i> PDF
                                            </a>
                                        @else
                                            <a href="{{ route('laporan.harian.pdf', ['id'=>$laporan->id,'type'=>'sales']) }}" class="btn btn-sm btn-success">
                                                <i class="bx bx-file"></i> PDF
                                            </a>
                                        @endif
                                    @endif
                                </td>
                            </tr>

                        @empty

                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <i class="bx bx-info-circle text-muted" style="font-size:4rem;"></i>
                                    <p class="text-muted mt-3">Tidak ada data laporan</p>
                                </td>
                            </tr>
                        @endforelse

                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-4 d-flex justify-content-center">
                {{ $laporans->links() }}
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

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const confirmDeleteModal = document.getElementById('confirmDeleteModal');
        const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
        let currentDeleteForm = null;
        
        // Set form saat modal ditrigger
        confirmDeleteModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const action = button.getAttribute('data-action');
            const form = button.closest('.delete-form');
            currentDeleteForm = form;
        });
        
        // Submit form saat tombol Hapus di klik
        confirmDeleteBtn.addEventListener('click', function() {
            if (currentDeleteForm) {
                currentDeleteForm.submit();
            }
        });
    });
</script>

@endsection