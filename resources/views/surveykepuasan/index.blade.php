@extends('layouts.app')
@section('content')
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<div class="d-flex justify-content-between align-items-center mt-3 me-3 ms-3">
    <a href="{{ route('surveykepuasan.index') }}" class="btn btn-outline-primary"><i class="fas fa-list-check me-1"></i> Survey Tiket Pribadi</a>
    <a href="{{ route('tickets.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left me-1"></i> Ke Daftar Tiket</a>
</div>
<div class="container-fluid py-4">
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-container">
                <div class="p-3 d-flex justify-content-between align-items-center border-bottom mb-3">
                    <h4 class="fw-bold mb-0"><i class="fa-solid fa-square-poll-vertical text-primary me-2"></i>Daftar Hasil Survey Kepuasan ITSM</h4>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover table-striped align-middle">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center">No</th>
                                <th class="text-center">Ticket ID</th>
                                <th>Nama User</th>
                                <th class="text-center" data-bs-toggle="tooltip" title="Bagaimana Anda menilai kecepatan respon tim ITSM dalam menangani insiden Anda">
                                    Respon Cepat
                                </th>
                                <th class="text-center" data-bs-toggle="tooltip" title="Apakah insiden Anda teratasi dengan efektif">
                                    Efektivitas
                                </th>
                                <th data-bs-toggle="tooltip" title="Jika tidak, apa yang dapat dilakukan untuk memperbaiki proses penyelesaian insiden">
                                    Saran Perbaikan
                                </th>
                                <th class="text-center" data-bs-toggle="tooltip" title="Bagaimana Anda menilai kualitas layanan yang diberikan oleh tim ITSM dalam memenuhi request Anda">
                                    Kualitas Layanan
                                </th>
                                <th data-bs-toggle="tooltip" title="Apakah ada sesuatu yang dapat dilakukan oleh tim ITSM untuk meningkatkan kualitas layanan">
                                    Saran Layanan
                                </th>
                                <th class="text-center">Tanggal</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($data as $index => $item)
                            <tr>
                                <td class="text-center">{{ $index + 1 }}</td>
                                <td class="text-center">
                                    @if ($item->ticket_id)
                                        <a href="{{ route('surveykepuasan.index', ['ticket_id' => $item->ticket_id]) }}" class="badge bg-primary text-decoration-none">
                                            {{ $item->ticket_id }}
                                        </a>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td><strong>{{ $item->nama_user }}</strong></td>
                                <td class="text-center">
                                    <span class="badge bg-warning text-dark">{{ $item->q1 }} ⭐</span>
                                </td>
                                <td class="text-center">
                                    @if ($item->q2 === 'Ya')
                                        <span class="badge bg-success">Ya</span>
                                    @else
                                        <span class="badge bg-danger">Tidak</span>
                                    @endif
                                </td>
                                <td>{{ $item->q3 ?? '-' }}</td>
                                <td class="text-center">
                                    <span class="badge bg-warning text-dark">{{ $item->q4 }} ⭐</span>
                                </td>
                                <td>{{ $item->q5 ?? '-' }}</td>
                                <td class="text-center text-nowrap">
                                    {{ \Carbon\Carbon::parse($item->created_at)->locale('id')->translatedFormat('d M Y H:i') }}
                                </td>
                                <td class="text-center">
                                    <button class="btn btn-outline-danger btn-sm btn-delete" data-id="{{ $item->id }}" title="Hapus Data">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="10" class="text-center text-muted py-4">Belum ada data survey.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
        [...tooltipTriggerList].map(el => new bootstrap.Tooltip(el));
    });

    $(document).on('click', '.btn-delete', function() {
        const id = $(this).data('id');
        Swal.fire({
            title: 'Yakin ingin menghapus?',
            text: "Data yang dihapus tidak bisa dikembalikan!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, hapus!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/survey/kepuasan/destroy/' + id,
                    type: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Terhapus!',
                            text: response.message,
                            confirmButtonColor: '#28a745'
                        }).then(() => location.reload());
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal!',
                            text: xhr.responseJSON?.message || 'Terjadi kesalahan saat menghapus data.',
                            confirmButtonColor: '#d33'
                        });
                        console.error(xhr.responseText);
                    }
                });
            }
        });
    });
</script>
@endsection