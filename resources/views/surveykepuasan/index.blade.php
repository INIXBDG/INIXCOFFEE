@extends('layouts.app')
@section('content')
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<div class="text-end mt-3 me-3">
    <a href="{{ route('surveykepuasan.index') }}" class="btn btn-primary">Kembali</a>
</div>
<div class="container py-4">
    <div class="card">
        <div class="card-body">
            <div class="table-container">
                <div class="p-3 d-flex justify-content-between align-items-center">
                    <h4 class="fw-bold mb-0">📋 Daftar Hasil Survey Kepuasan</h4>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama User</th>
                                <th data-bs-toggle="tooltip" title="Bagaimana Anda menilai kecepatan respon tim ITSM dalam menangani insiden Anda">
                                    Respon Cepat
                                </th>
                                <th data-bs-toggle="tooltip" title="Apakah insiden Anda teratasi dengan efektif">
                                    Efektivitas
                                </th>
                                <th data-bs-toggle="tooltip" title="Jika tidak, apa yang dapat dilakukan untuk memperbaiki proses penyelesaian insiden">
                                    Saran Perbaikan
                                </th>
                                <th data-bs-toggle="tooltip" title="Bagaimana Anda menilai kualitas layanan yang diberikan oleh tim ITSM dalam memenuhi request Anda">
                                    Kualitas Layanan
                                </th>
                                <th data-bs-toggle="tooltip" title="Apakah ada sesuatu yang dapat dilakukan oleh tim ITSM untuk meningkatkan kualitas layanan">
                                    Saran Layanan
                                </th>
                                <th>Tanggal</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($data as $index => $item)
                            <tr>
                                <td class="text-center">{{ $index + 1 }}</td>
                                <td>{{ $item->karyawan->nama_lengkap ?? 'User' }}</td>
                                <td class="text-center">{{ $item->q1 }} ⭐</td>
                                <td>{{ $item->q2 }}</td>
                                <td>{{ $item->q3 ?? '-' }}</td>
                                <td class="text-center">{{ $item->q4 }} ⭐</td>
                                <td>{{ $item->q5 ?? '-' }}</td>
                                <td class="text-center">
                                    {{ \Carbon\Carbon::parse($item->created_at)->locale('id')->translatedFormat('l, d F Y') }}
                                </td>
                                <td class="text-center">
                                    <button class="btn btn-danger btn-action btn-delete" data-id="{{ $item->id }}">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">Belum ada data survey.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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
                    type: 'get',
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