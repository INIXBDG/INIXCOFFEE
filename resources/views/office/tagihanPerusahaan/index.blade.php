@extends('layouts_office.app')

@section('office_contents')
    <div class="container-fluid py-4">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-5">
            <h4 class="mb-0 fw-semibold text-dark d-flex align-items-center">
                <i class="bx bx-task text-primary me-2" style="font-size: 1.5rem;"></i>
                Tagihan Perusahaan 
            </h4>
            <small class="text-muted fw-medium">{{ now()->translatedFormat('l, d F Y') }}</small>
        </div>

        <div class="row g-4 mb-5">
            <div class="col-12">
                <div class="card border-0 shadow-lg h-100 rounded-4 overflow-hidden glass-force">
                    <div class="card-body p-4 mb-4 h-100 " style="height: 320px;">
                        {{-- Table Tagihan --}}
                        <div class="table-responsive mb-4" style="max-height: 500px; overflow-y: auto;">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th class="border-0 ps-4"></th>
                                        <th class="border-0" style="min-width: 160px;">Due Date</th>
                                        <th class="border-0" style="min-width: 150px;">Kegiatan</th>
                                        <th class="border-0" style="min-width: 100px;">Tipe</th>
                                        <th class="border-0" style="min-width: 200px;">Nominal</th>
                                        <th class="border-0" style="min-width: 120px;">Tracking</th>
                                        <th class="border-0" style="min-width: 120px;">Tanggal Selesai</th>
                                        <th class="border-0 text-center pe-4" style="min-width: 120px;">Status</th>
                                        <th class="border-0 text-center pe-4" style="min-width: 120px;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($trackingTagihanPerusahaans as $tagihan)
                                        <tr class="border-bottom ">
                                            @if ($tagihan->status === 'selesai')
                                                <td class="text-center ps-4"><input class="custom-check" type="checkbox" checked disabled></td>
                                            @else
                                                <td class="text-center ps-4"><input class="custom-check" type="checkbox" disabled></td>
                                            @endif
                                            <td>
                                                @if ($tagihan->tanggal_perkiraan_mulai === $tagihan->tanggal_perkiraan_selesai || $tagihan->tanggal_perkiraan_selesai === null )
                                                    <div class="small">
                                                        {{ \Carbon\Carbon::parse($tagihan->tanggal_perkiraan_mulai)->format('d F') }}
                                                    </div>
                                                @else
                                                    <div class="small">
                                                        {{ \Carbon\Carbon::parse($tagihan->tanggal_perkiraan_mulai)->format('d M') }} - {{ \Carbon\Carbon::parse($tagihan->tanggal_perkiraan_selesai)->format('d M') }}
                                                    </div>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="text-truncate" style="max-width: 150px;">
                                                        {{ $tagihan->tagihanPerusahaan->kegiatan ?? $tagihan->kegiatan }}
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="text-truncate" style="max-width: 150px;">
                                                    {{ $tagihan->tagihanPerusahaan->tipe ?? $tagihan->kegiatan }} 
                                                </div>
                                            </td>
                                            <td>
                                                <span class="">
                                                    {{ $tagihan->nominal ? 'Rp. ' . number_format($tagihan->nominal, 0, ',', '.') : '-' }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="text-truncate" style="max-width: 200px;">
                                                    {{ $tagihan->tracking ?? '-' }} 
                                                </div>
                                            </td>
                                            <td>
                                                <div class="small">
                                                    {{ $tagihan->tanggal_selesai ? \Carbon\Carbon::parse($tagihan->tanggal_selesai)->format('d F') : '-' }} 
                                                </div>
                                            </td>
                                            <td class="text-center pe-4">
                                                @php
                                                    $statusConfig = [
                                                        'pending' => [
                                                            'color' => 'warning',
                                                            'icon' => 'bx-time-five',
                                                        ],
                                                        'proses' => [
                                                            'color' => 'primary',
                                                            'icon' => 'bx-loader-circle',
                                                        ],
                                                        'selesai' => [
                                                            'color' => 'success',
                                                            'icon' => 'bx-check-circle',
                                                        ],
                                                        'telat' => [
                                                            'color' => 'danger',
                                                            'icon' => 'bx-info-circle',
                                                        ],
                                                    ];
                                                    $config = $statusConfig[$tagihan->status] ?? [
                                                        'color' => 'secondary',
                                                        'icon' => 'bx-info-circle',
                                                    ];
                                                @endphp
                                                <span
                                                    class="badge bg-{{ $config['color'] }}-subtle text-{{ $config['color'] }} px-3 py-2 text-capitalize">
                                                    <i class="bx {{ $config['icon'] }} me-1"></i>
                                                    {{ $tagihan->status }}
                                                </span>
                                            </td>
                                            <td class="text-center pe-4 position-relative">
                                                <div class="dropdown">
                                                    <button class="btn btn-sm btn-outline-primary dropdown-toggle"
                                                            type="button"
                                                            data-bs-toggle="dropdown"
                                                            data-bs-boundary="viewport"
                                                            aria-expanded="false">
                                                        Aksi
                                                    </button>

                                                    <ul class="dropdown-menu dropdown-menu-end">

                                                        <li>
                                                            <a class="dropdown-item"
                                                            href="{{ route('detailTagihanPerusahaan', $tagihan->id) }}">
                                                                Detail
                                                            </a>
                                                        </li>

                                                        <li>
                                                            <form action="{{ route('hapusTagihanPerusahaan', $tagihan->id) }}" method="POST"
                                                                onsubmit="return confirm('Yakin ingin menghapus?')">
                                                                @csrf
                                                                <button type="submit" class="dropdown-item text-danger">
                                                                    Hapus
                                                                </button>
                                                            </form>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center py-5">
                                                <div class="d-flex flex-column align-items-center">
                                                    <i class="bx bx-message-square-x text-muted"
                                                        style="font-size: 3rem;"></i>
                                                    <p class="text-muted mt-3 mb-0">Tidak ada tagihan untuk
                                                        ditampilkan
                                                    </p>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<style>
    .custom-check {
        appearance: none;
        width: 20px;
        height: 20px;
        border: 2px solid #71DD37;
        border-radius: 5px;
        position: relative;
    }
    .check-blue {
        width: 20px;
        height: 20px;
        border: 2px solid #5B73E8;
        border-radius: 5px;
        position: relative;
    }

    .custom-check:checked {
        background-color: #71DD37;
    }

    .custom-check:checked::after {
        content: '✓';
        color: white;
        font-weight: bold;
        position: absolute;
        left: 2px;
        top: -2px;
    }
    .custom-fail:checked::after {
        content: '✖';
        color: white;
        font-weight: bold;
        position: absolute;
        left: 2px;
        top: -2px;
    }
    .custom-fail:checked {
        background-color: #FF3E1D;
    }
    .custom-fail {
        appearance: none;
        width: 20px;
        height: 20px;
        border: 2px solid #FF3E1D;
        border-radius: 5px;
        position: relative;
    }
</style>
@endsection