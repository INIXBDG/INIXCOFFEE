@extends('layouts_crm.app')

@section('crm_contents')
    <div class="content-wrapper">
        <div class="container-xxl flex-grow-1 container-p-y">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="fw-bold mb-0">Laporan Peluang Menang per Sales per Triwulan</h4>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Total Close Win per Sales</h5>
                </div>
                <div class="card-body">
                    @if (empty($ringkasanMerah))
                        <p class="text-muted">Tidak ada data peluang menang untuk tahun {{ $tahunDipilih }}.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered">
                                <thead class="table-primary">
                                    <tr>
                                        <th>Sales</th>
                                        <th>TR1 (Jan-Mar)</th>
                                        <th>TR2 (Apr-Jun)</th>
                                        <th>TR3 (Jul-Sep)</th>
                                        <th>TR4 (Okt-Des)</th>
                                        <th>Total</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($ringkasanMerah as $idSales => $triwulan)
                                        <tr>
                                            <td>{{ $pengguna[$idSales]['username'] ?? 'Tidak Diketahui' }}</td>
                                            <td>{{ number_format($triwulan['TR1'] ?? 0, 2, ',', '.') }}</td>
                                            <td>{{ number_format($triwulan['TR2'] ?? 0, 2, ',', '.') }}</td>
                                            <td>{{ number_format($triwulan['TR3'] ?? 0, 2, ',', '.') }}</td>
                                            <td>{{ number_format($triwulan['TR4'] ?? 0, 2, ',', '.') }}</td>
                                            <td>{{ number_format(array_sum($triwulan), 2, ',', '.') }}</td>
                                            <td>
                                                <a class="btn btn-sm btn-info"
                                                    href="{{ route('detail.ringkasanPeluang', $pengguna[$idSales]['id_sales'] ?? '') }}">
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

            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Total Close Lost per Sales</h5>
                </div>
                <div class="card-body">
                    @if (empty($ringkasanLost))
                        <p class="text-muted">Tidak ada data peluang kalah untuk tahun {{ $tahunDipilih }}.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered">
                                <thead class="table-danger">
                                    <tr>
                                        <th>Sales</th>
                                        <th>TR1 (Jan-Mar)</th>
                                        <th>TR2 (Apr-Jun)</th>
                                        <th>TR3 (Jul-Sep)</th>
                                        <th>TR4 (Okt-Des)</th>
                                        <th>Total</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($ringkasanLost as $idSales => $triwulan)
                                        <tr>
                                            <td>{{ $pengguna[$idSales]['username'] ?? 'Tidak Diketahui' }}</td>
                                            <td>{{ number_format($triwulan['TR1'] ?? 0, 2, ',', '.') }}</td>
                                            <td>{{ number_format($triwulan['TR2'] ?? 0, 2, ',', '.') }}</td>
                                            <td>{{ number_format($triwulan['TR3'] ?? 0, 2, ',', '.') }}</td>
                                            <td>{{ number_format($triwulan['TR4'] ?? 0, 2, ',', '.') }}</td>
                                            <td>{{ number_format(array_sum($triwulan), 2, ',', '.') }}</td>
                                            <td>
                                                <a class="btn btn-sm btn-danger"
                                                    href="{{ route('detail.Ringkasanlost', $pengguna[$idSales]['id_sales'] ?? '') }}">
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
@endsection
