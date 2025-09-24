@extends('layouts_crm.app')

@section('crm_contents')
    <div class="content-wrapper">
        <div class="container-xxl flex-grow-1 container-p-y">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="fw-bold mb-0">Laporan Peluang Menang per Sales per Triwulan</h4>
            </div>

            <!-- Card Laporan -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Total Close Win per Sales</h5>
                </div>
                <div class="card-body">
                    @if (empty($dataRingkasan))
                        <p class="text-muted">Tidak ada data peluang yang dimenangkan untuk tahun {{ $tahunDipilih }}.</p>
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
                                    @foreach ($dataRingkasan as $idSales => $triwulan)
                                        <tr>
                                            <td>{{ $pengguna[$idSales]['id_sales'] ?? 'Tidak Diketahui' }}</td>
                                            <td>{{ number_format($triwulan['TR1'] ?? 0, 2, ',', '.') }}</td>
                                            <td>{{ number_format($triwulan['TR2'] ?? 0, 2, ',', '.') }}</td>
                                            <td>{{ number_format($triwulan['TR3'] ?? 0, 2, ',', '.') }}</td>
                                            <td>{{ number_format($triwulan['TR4'] ?? 0, 2, ',', '.') }}</td>
                                            <td>
                                                {{ number_format(
                                                    ($triwulan['TR1'] ?? 0) + ($triwulan['TR2'] ?? 0) + ($triwulan['TR3'] ?? 0) + ($triwulan['TR4'] ?? 0),
                                                    2,
                                                    ',',
                                                    '.',
                                                ) }}
                                            </td>
                                            <td>
                                                <a class="btn btn-sm btn-info"
                                                    href="{{ route('detail.ringkasanPeluang', $pengguna[$idSales]['id_sales']) }}">Detail</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Grafik -->
            @if (!empty($dataRingkasan))
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Visualisasi Total Close Win per Triwulan</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="grafikPeluang"></canvas>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const dataRingkasan = @json($dataRingkasan);
        const pengguna = @json($pengguna);
        const labelTriwulan = ['TR1 (Jan-Mar)', 'TR2 (Apr-Jun)', 'TR3 (Jul-Sep)', 'TR4 (Okt-Des)'];

        const datasets = Object.keys(dataRingkasan).map(idSales => ({
            label: `${pengguna[idSales]?.username ?? 'Tidak Diketahui'}`,
            data: [
                dataRingkasan[idSales].TR1 || 0,
                dataRingkasan[idSales].TR2 || 0,
                dataRingkasan[idSales].TR3 || 0,
                dataRingkasan[idSales].TR4 || 0
            ],
            backgroundColor: `rgba(${Math.floor(Math.random() * 255)}, ${Math.floor(Math.random() * 255)}, ${Math.floor(Math.random() * 255)}, 0.5)`,
            borderColor: `rgba(${Math.floor(Math.random() * 255)}, ${Math.floor(Math.random() * 255)}, ${Math.floor(Math.random() * 255)}, 1)`,
            borderWidth: 1
        }));

        const ctx = document.getElementById('grafikPeluang').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labelTriwulan,
                datasets: datasets
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Total Close Win (Rp)'
                        }
                    }
                },
                plugins: {
                    title: {
                        display: true,
                        text: 'Total Close Win per Sales per Triwulan'
                    }
                }
            }
        });
    </script>
@endsection
