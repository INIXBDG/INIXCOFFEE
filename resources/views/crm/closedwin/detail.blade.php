@extends('layouts_crm.app')

@section('crm_contents')
    <div class="content-wrapper">
        <div class="container-xxl flex-grow-1 container-p-y">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Daftar Lead (Win)</h5>
                </div>

                <div class="card-body">
                    {{-- Filter Form --}}
                    <form method="GET" action="{{ route('detail.ringkasanPeluang', $id) }}" class="row g-2 mb-4">
                        <div class="col-md-4">
                            <select name="tahun" class="form-select">
                                <option value="">-- Pilih Tahun --</option>
                                @for ($y = now()->year; $y >= 2020; $y--)
                                    <option value="{{ $y }}" {{ request('tahun') == $y ? 'selected' : '' }}>
                                        {{ $y }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-4">
                            <select name="bulan" class="form-select">
                                <option value="">-- Pilih Bulan --</option>
                                @foreach (range(1, 12) as $m)
                                    <option value="{{ $m }}" {{ request('bulan') == $m ? 'selected' : '' }}>
                                        {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 d-grid">
                            <button type="submit" class="btn btn-primary">Filter</button>
                        </div>
                    </form>

                    {{-- Tabel Data --}}
                    @if ($data->isEmpty())
                        <p class="text-muted">Tidak ada peluang yang tercatat untuk sales ini.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered align-middle">
                                <thead class="table-primary text-center">
                                    <tr>
                                        <th>Materi</th>
                                        <th>Harga Penawaran</th>
                                        <th>Netsales</th>
                                        <th>Periode</th>
                                        <th>Pax</th>
                                        <th>Total</th>
                                        <th>Sales</th>
                                        <th>Perusahaan / PIC</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($data as $peluang)
                                        <tr data-peluang='@json($peluang)'>
                                            <td>{{ $peluang->materiRelation->nama_materi }}</td>
                                            <td>Rp {{ number_format($peluang->harga, 0, ',', '.') }}</td>
                                            <td>Rp {{ number_format($peluang->netsales, 0, ',', '.') }}</td>
                                            <td>
                                                {{ \Carbon\Carbon::parse($peluang->periode_mulai)->translatedFormat('d M Y') }}
                                                -
                                                {{ \Carbon\Carbon::parse($peluang->periode_selesai)->translatedFormat('d M Y') }}
                                            </td>
                                            <td>{{ $peluang->pax }}</td>
                                            <td>
                                                @if (!is_null($peluang->netsales) && !is_null($peluang->pax))
                                                    Rp {{ number_format($peluang->netsales * $peluang->pax, 0, ',', '.') }}
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td>{{ $peluang->id_sales }}</td>
                                            <td>
                                                {{ $peluang->perusahaan->nama_perusahaan }}
                                                @if ($peluang->perusahaan->cp)
                                                    ({{ $peluang->perusahaan->cp }})
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ route('detail.peluang', $peluang->id) }}"
                                                class="btn btn-sm btn-warning w-100">
                                                    Detail
                                                </a>
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

    <script>
    </script>
@endsection
