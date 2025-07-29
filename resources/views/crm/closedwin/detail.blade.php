@extends('layouts_crm.app')

@section('crm_contents')
    <div class="content-wrapper">
        <div class="container-xxl flex-grow-1 container-p-y">

            <!-- Card Daftar Peluang -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Daftar Peluang</h5>
                </div>
                <div class="card-body">
                    @if ($data->isEmpty())
                        <p class="text-muted">Tidak ada peluang yang tercatat untuk sales ini.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Judul</th>
                                        <th>Tahap</th>
                                        <th>Jumlah</th>
                                        <th>Harga Akhir (Menang)</th>
                                        <th>Tanggal Tutup</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($data as $peluang)
                                        <tr>
                                            <td>{{ $peluang->judul }}</td>
                                            <td>{{ $peluang->tahap ?? '-' }}</td>
                                            <td>Rp {{ number_format($peluang->jumlah, 2, ',', '.') }}</td>
                                            <td>
                                                @if (!empty($peluang->close_win))
                                                    Rp {{ number_format($peluang->close_win, 2, ',', '.') }}
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td>
                                                {{ \Carbon\Carbon::parse($peluang->tanggal_tutup_diharapkan)->translatedFormat('d F Y') }}
                                            </td>
                                            <td>
                                                <a href="{{ route('detail.peluang', $peluang->id) }}"
                                                    class="btn btn-sm btn-info">Detail</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
