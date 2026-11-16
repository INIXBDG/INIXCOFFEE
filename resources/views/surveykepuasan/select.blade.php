@extends('layouts.app')
@section('content')
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <a href="{{ route('tickets.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Kembali ke Daftar Tiket
        </a>
        @if ($isItsm)
            <a href="{{ route('surveyKepuasan.indexTable') }}" class="btn btn-primary">
                <i class="fa-solid fa-square-poll-vertical me-1"></i> Lihat Rekap Hasil Survey (ITSM)
            </a>
        @endif
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3">
            <h5 class="fw-bold mb-1 text-primary">
                <i class="fa-solid fa-list-check me-2"></i>Pilih Tiket untuk Mengisi Survey Kepuasan
            </h5>
            <p class="text-muted small mb-0">
                Berikut adalah daftar tiket Anda yang telah selesai ditangani dan menunggu penilaian Anda. Silakan pilih tiket yang ingin dinilai.
            </p>
        </div>
        <div class="card-body">
            @if (session('warning'))
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-1"></i> {{ session('warning') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-times-circle me-1"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" style="width: 50px;">No</th>
                            <th>ID Tiket</th>
                            <th>Keperluan</th>
                            <th>Detail Kendala</th>
                            <th>Waktu Selesai</th>
                            <th class="text-center" style="width: 140px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($userTickets as $index => $item)
                        <tr>
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td>
                                <span class="badge bg-primary px-2 py-1" style="font-size: 0.85rem;">
                                    {{ $item->ticket_id }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">
                                    {{ $item->keperluan ?? '-' }}
                                </span>
                            </td>
                            <td>{{ \Illuminate\Support\Str::limit($item->detail_kendala, 75) }}</td>
                            <td>
                                <small class="text-muted">
                                    {{ $item->tanggal_selesai ? \Carbon\Carbon::parse($item->tanggal_selesai)->translatedFormat('d M Y') : '-' }}
                                    {{ $item->jam_selesai ?? '' }}
                                </small>
                            </td>
                            <td class="text-center">
                                <a href="{{ route('surveykepuasan.index', ['ticket_id' => $item->ticket_id]) }}" class="btn btn-warning btn-sm fw-semibold text-dark">
                                    <i class="fas fa-star text-dark me-1"></i> Isi Survey
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                Tidak ada tiket yang perlu disurvei.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
