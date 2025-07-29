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
                                <thead class="table-dark">
                                    <tr>
                                        <th>Sales</th>
                                        <th>Q1 (Jan-Mar)</th>
                                        <th>Q2 (Apr-Jun)</th>
                                        <th>Q3 (Jul-Sep)</th>
                                        <th>Q4 (Okt-Des)</th>
                                        <th>Total</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($dataRingkasan as $idSales => $kuartal)
                                        <tr>
                                            <td>{{ $pengguna[$idSales]['id_sales'] }}</td>
                                            <td>Rp {{ number_format($kuartal['Q1'] ?? 0, 2, ',', '.') }}</td>
                                            <td>Rp {{ number_format($kuartal['Q2'] ?? 0, 2, ',', '.') }}</td>
                                            <td>Rp {{ number_format($kuartal['Q3'] ?? 0, 2, ',', '.') }}</td>
                                            <td>Rp {{ number_format($kuartal['Q4'] ?? 0, 2, ',', '.') }}</td>
                                            <td>Rp
                                                {{ number_format(($kuartal['Q1'] ?? 0) + ($kuartal['Q2'] ?? 0) + ($kuartal['Q3'] ?? 0) + ($kuartal['Q4'] ?? 0), 2, ',', '.') }}
                                            </td>
                                            <td>
                                                <a href="{{route('detail.ringkasanPeluang', $pengguna[$idSales]['id_sales'])}}">Detail</a>
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
            const labelKuartal = ['Q1 (Jan-Mar)', 'Q2 (Apr-Jun)', 'Q3 (Jul-Sep)', 'Q4 (Okt-Des)'];

            const datasets = Object.keys(dataRingkasan).map(idSales => ({
                label: `${pengguna[idSales]?.username ?? 'Tidak Diketahui'}`,
                data: [
                    dataRingkasan[idSales].Q1 || 0,
                    dataRingkasan[idSales].Q2 || 0,
                    dataRingkasan[idSales].Q3 || 0,
                    dataRingkasan[idSales].Q4 || 0
                ],
                backgroundColor: `rgba(${Math.random() * 255}, ${Math.random() * 255}, ${Math.random() * 255}, 0.5)`,
                borderColor: `rgba(${Math.random() * 255}, ${Math.random() * 255}, ${Math.random() * 255}, 1)`,
                borderWidth: 1
            }));

            const ctx = document.getElementById('grafikPeluang').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labelKuartal,
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
