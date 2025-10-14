@extends('layouts_crm.app')

@section('crm_contents')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <!-- Data Perusahaan -->
            <div class="col-lg-6 col-md-12 mb-3">
                <div class="card h-100 shadow-sm border-0 rounded-3">
                    <div class="card-header bg-transparent border-0 pb-0">
                        <h5 class="card-title mb-0 text-primary">Data Perusahaan</h5>
                    </div>
                    <div class="card-body p-3">
                        <div class="row">
                            <div class="col-6">
                                <div class="chart-container" style="position: relative; height: 280px;">
                                    <canvas id="kategoriChart"></canvas>
                                </div>
                            </div>
                            <div class="col-6 d-flex align-items-center">
                                <div class="w-100">
                                    <h6 class="text-muted mb-3">Detail Kategori</h6>
                                    @forelse ($chartData as $item)
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="small">{{ $item['kategori'] }}</span>
                                            <span class="small text-muted">{{ number_format($item['jumlah'], 0, ',', '.') }}
                                                ({{ $item['persen'] }}%)</span>
                                        </div>
                                    @empty
                                        <p class="text-muted small mb-0">Tidak ada data kategori.</p>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Target Aktivitas Sales -->
            <div class="col-lg-6 col-md-12 mb-3">
                <div class="card h-100 shadow-sm border-0 rounded-3">
                    <div class="card-header bg-transparent border-0 pb-0">
                        <h5 class="card-title mb-0 text-primary">Target Aktivitas Sales</h5>
                    </div>
                    <div class="card-body p-3">
                        <div class="activity-container" style="max-height: 355px; overflow-y: auto;">
                            @if (!empty($activitysales))
                                <div class="mb-3 sales-item" data-sales-id="{{ $activitysales['id_sales'] }}">
                                    <strong class="text-dark d-block mb-2">Sales ID:
                                        {{ $activitysales['id_sales'] }}</strong>
                                    @php
                                        $aktivitas = [
                                            'DB' => [
                                                'jumlah' => $activitysales['DB'],
                                                'target' => $activitysales['target_DB'],
                                                'warna' => 'info',
                                            ],
                                            'Contact' => [
                                                'jumlah' => $activitysales['contact'],
                                                'target' => $activitysales['target_contact'],
                                                'warna' => 'info',
                                            ],
                                            'Call' => [
                                                'jumlah' => $activitysales['call'],
                                                'target' => $activitysales['target_call'],
                                                'warna' => 'info',
                                            ],
                                            'Email' => [
                                                'jumlah' => $activitysales['email'],
                                                'target' => $activitysales['target_email'],
                                                'warna' => 'warning',
                                            ],
                                            'Visit' => [
                                                'jumlah' => $activitysales['visit'],
                                                'target' => $activitysales['target_visit'],
                                                'warna' => 'warning',
                                            ],
                                            'Meet' => [
                                                'jumlah' => $activitysales['meet'],
                                                'target' => $activitysales['target_meet'],
                                                'warna' => 'Warning',
                                            ],
                                            'Incharge' => [
                                                'jumlah' => $activitysales['incharge'],
                                                'target' => $activitysales['target_incharge'],
                                                'warna' => 'success',
                                            ],
                                            'Penawaran Awal' => [
                                                'jumlah' => $activitysales['PA'],
                                                'target' => $activitysales['target_PA'],
                                                'warna' => 'success',
                                            ],
                                            'Penawaran Internal' => [
                                                'jumlah' => $activitysales['PI'],
                                                'target' => $activitysales['target_PI'],
                                                'warna' => 'success',
                                            ],
                                            'Telemarketing' => [
                                                'jumlah' => $activitysales['Telemarketing'],
                                                'target' => $activitysales['target_Telemarketing'],
                                                'warna' => 'danger',
                                            ],
                                            'Form Masuk' => [
                                                'jumlah' => $activitysales['Form_Masuk'],
                                                'target' => $activitysales['target_Form_Masuk'],
                                                'warna' => 'danger',
                                            ],
                                            'Form Keluar' => [
                                                'jumlah' => $activitysales['Form_Keluar'],
                                                'target' => $activitysales['target_Form_Keluar'],
                                                'warna' => 'danger',
                                            ],
                                        ];
                                    @endphp
                                    @foreach ($aktivitas as $label => $data)
                                        @php
                                            $persen =
                                                $data['target'] > 0
                                                    ? min(round(($data['jumlah'] / $data['target']) * 100), 100)
                                                    : 0;
                                        @endphp
                                        <div class="mb-2 activity-item" data-activity="{{ $label }}">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <span class="small text-muted">{{ $label }}:
                                                    {{ number_format($data['jumlah'], 0, ',', '.') }}/{{ number_format($data['target'], 0, ',', '.') }}</span>
                                                <span
                                                    class="badge bg-{{ $data['warna'] }}-subtle text-dark">{{ $persen }}%</span>
                                            </div>
                                            <div class="progress" style="height: 6px;">
                                                <div class="progress-bar bg-{{ $data['warna'] }}"
                                                    style="width: {{ $persen }}%;"></div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-3">
                                    <p class="text-muted small mb-0">Tidak ada data aktivitas sales.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Top 5 Produk Terjual -->
            <div class="col-lg-6 col-md-12 mb-3">
                <div class="card h-100 shadow-sm border-0 rounded-3">
                    <div class="card-header bg-transparent border-0 pb-0">
                        <h5 class="card-title mb-0 text-primary">Top 5 Produk Terjual</h5>
                    </div>
                    <div class="card-body p-3" style="max-height: 280px; overflow-y: auto;">
                        @forelse ($best as $item)
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="text-truncate" style="max-width: 70%;">
                                    <strong
                                        class="text-dark">{{ $item->materi->nama_materi ?? $item->materi_key }}</strong>
                                </div>
                                <span class="badge bg-success-subtle text-success">
                                    {{ number_format($item->total_pax, 0, ',', '.') }} Pax
                                </span>
                            </div>
                            @if (!$loop->last)
                                <hr class="my-2 opacity-25">
                            @endif
                        @empty
                            <div class="text-center py-3">
                                <p class="text-muted small mb-0">Tidak ada data produk terjual.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Top 5 Produk Menguntungkan -->
            <div class="col-lg-6 col-md-12 mb-3">
                <div class="card h-100 shadow-sm border-0 rounded-3">
                    <div class="card-header bg-transparent border-0 pb-0">
                        <h5 class="card-title mb-0 text-primary">Top 5 Produk Menguntungkan</h5>
                    </div>
                    <div class="card-body p-3" style="max-height: 280px; overflow-y: auto;">
                        @forelse ($profit as $item)
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="text-truncate" style="max-width: 70%;">
                                    <strong
                                        class="text-dark">{{ $item->materi->nama_materi ?? $item->materi_key }}</strong>
                                </div>
                                <span class="badge bg-info-subtle text-info">
                                    Rp {{ number_format($item->total_revenue, 0, ',', '.') }}
                                </span>
                            </div>
                            @if (!$loop->last)
                                <hr class="my-2 opacity-25">
                            @endif
                        @empty
                            <div class="text-center py-3">
                                <p class="text-muted small mb-0">Tidak ada data produk menguntungkan.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Prospek Terbuat Minggu Ini -->
            <div class="row g-3 mb-4">
                <div class="col-12">
                    <div class="card h-100 shadow-sm border-0 rounded-3">
                        <div class="card-header bg-transparent border-0 pb-0">
                            <h5 class="card-title mb-0 text-primary">Prospek Terbuat Minggu Ini</h5>
                        </div>
                        <div class="card-body p-3">
                            <div class="table-responsive" style="max-height: 280px; overflow-y: auto;">
                                <table class="table table-hover table-striped table-bordered align-middle">
                                    <thead>
                                        <tr>
                                            <th scope="col">Sales</th>
                                            <th scope="col">Materi</th>
                                            <th scope="col">Harga</th>
                                            <th scope="col">Periode</th>
                                            <th scope="col">Pax</th>
                                            <th scope="col">Tahap</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($prospek as $item)
                                            <tr>
                                                <td class="text-truncate" style="max-width: 250px;">
                                                    {{ $item->id_sales }}
                                                </td>
                                                <td class="text-truncate" style="max-width: 250px;">
                                                    {{ $item->materiRelation->nama_materi }}
                                                </td>
                                                <td>Rp {{ number_format($item->harga, 0, ',', '.') }}</td>
                                                <td>
                                                    @if ($item->tentatif == 1)
                                                        <span class="badge bg-warning-subtle text-warning">Tentatif</span>
                                                    @else
                                                        {{ \Carbon\Carbon::parse($item->periode_mulai)->format('d-m-Y') }}
                                                        s/d
                                                        {{ \Carbon\Carbon::parse($item->periode_selesai)->format('d-m-Y') }}
                                                    @endif
                                                </td>
                                                <td>{{ number_format($item->pax, 0, ',', '.') }}</td>
                                                <td>
                                                    <span class="badge bg-info-subtle text-info">
                                                        {{ strtoupper($item->tahap) }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center text-muted">
                                                    Tidak ada data prospek minggu ini
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

            <!-- Total Status Perusahaan per Sales -->
            <div class="col-12 mb-4">
                <div class="card h-100 shadow-sm border-0 rounded-3">
                    <div class="card-header bg-transparent border-0 pb-0">
                        <h5 class="card-title mb-0 text-primary">Total Status Perusahaan per Sales</h5>
                    </div>
                    <div class="card-body p-3">
                        <div class="table-responsive" style="max-height: 280px; overflow-y: auto;">
                            <table class="table table-hover table-striped table-bordered align-middle">
                                <thead>
                                    <tr>
                                        <th scope="col" class="text-primary">Sales</th>
                                        @php
                                            $statuses = $totalStatus->pluck('status')->unique()->sort();
                                        @endphp
                                        @foreach ($statuses as $status)
                                            <th scope="col" class="text-primary">{{ strtoupper($status) }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $pivotData = [];
                                        foreach ($totalStatus as $item) {
                                            $pivotData[$item->sales_key][$item->status] = $item->total;
                                        }
                                    @endphp
                                    @forelse ($pivotData as $salesKey => $statusData)
                                        <tr>
                                            <td class="text-truncate" style="max-width: 150px;">{{ $salesKey }}</td>
                                            @foreach ($statuses as $status)
                                                <td>{{ number_format($statusData[$status] ?? 0, 0, ',', '.') }}</td>
                                            @endforeach
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="{{ $statuses->count() + 1 }}" class="text-center text-muted">
                                                Tidak ada data status perusahaan.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Distribusi Perusahaan per Lokasi -->
            <div class="col-12">
                <div class="card h-100 shadow-sm border-0 rounded-3">
                    <div
                        class="card-header bg-transparent border-0 pb-0 d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0 text-primary">Distribusi Perusahaan per Lokasi</h5>
                    </div>
                    <div class="card-body p-3">
                        <div class="row">
                            <div class="col-6">
                                <div class="chart-container" style="position: relative; height: 280px;">
                                    <canvas id="lokasiPieChart"></canvas>
                                </div>
                            </div>
                            <div class="col-6 d-flex align-items-center">
                                <div class="w-100">
                                    <h6 class="text-muted mb-3">Detail Lokasi</h6>
                                    @forelse ($totalDaerah as $item)
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="small">{{ $item['lokasi'] }}</span>
                                            <span
                                                class="small text-muted">{{ number_format($item['total'], 0, ',', '.') }}
                                                ({{ $item['persen'] }}%)</span>
                                        </div>
                                    @empty
                                        <p class="text-muted small mb-0">Tidak ada data lokasi.</p>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Define initChart function
            const initChart = (id, config) => {
                const ctx = document.getElementById(id).getContext('2d');
                return new Chart(ctx, {
                    ...config,
                    options: {
                        ...config.options,
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            ...config.options?.plugins,
                            legend: {
                                position: 'bottom',
                                labels: {
                                    font: {
                                        size: 12
                                    },
                                    padding: 10,
                                    boxWidth: 12
                                }
                            },
                            tooltip: {
                                callbacks: {
                                    label: context => {
                                        const index = context.dataIndex;
                                        const dataset = context.dataset;
                                        const item = (id === 'kategoriChart' ? chartData :
                                            lokasiData)[index];
                                        const key = id === 'kategoriChart' ? 'kategori' : 'lokasi';
                                        const value = id === 'kategoriChart' ? 'jumlah' : 'total';
                                        return `${item[key]}: ${item[value]} (${item.persen}%)`;
                                    }
                                }
                            }
                        }
                    }
                });
            };

            // Kategori Chart Data (from Laravel)
            const chartData = @json($chartData);
            console.log('Chart Data:', chartData); // Debug: Cek di console apakah data valid

            // Initialize Kategori Chart
            initChart('kategoriChart', {
                type: 'pie',
                data: {
                    labels: chartData.map(item => item.kategori),
                    datasets: [{
                        label: 'Persentase Kategori',
                        data: chartData.map(item => item.persen),
                        backgroundColor: [
                            'rgba(75, 192, 192, 0.8)',
                            'rgba(255, 99, 132, 0.8)',
                            'rgba(54, 162, 235, 0.8)',
                            'rgba(255, 206, 86, 0.8)',
                            'rgba(153, 102, 255, 0.8)',
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    plugins: {
                        tooltip: {
                            enabled: true
                        }
                    }
                }
            });

            // Lokasi Chart Data (from Laravel)
            const lokasiData = @json($totalDaerah);
            console.log('Lokasi Data:', lokasiData); // Debug: Cek di console apakah data valid

            // Initialize Lokasi Pie Chart
            initChart('lokasiPieChart', {
                type: 'pie',
                data: {
                    labels: lokasiData.map(item => item.lokasi),
                    datasets: [{
                        label: 'Distribusi Perusahaan per Lokasi',
                        data: lokasiData.map(item => item.persen),
                        backgroundColor: [
                            'rgba(75, 192, 192, 0.8)',
                            'rgba(255, 99, 132, 0.8)',
                            'rgba(54, 162, 235, 0.8)',
                            'rgba(255, 206, 86, 0.8)',
                            'rgba(153, 102, 255, 0.8)',
                            'rgba(255, 159, 64, 0.8)',
                            'rgba(0, 128, 128, 0.8)',
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    plugins: {
                        tooltip: {
                            enabled: true
                        }
                    }
                }
            });
        });
    </script>
@endsection
