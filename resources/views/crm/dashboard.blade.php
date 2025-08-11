@extends('layouts_crm.app')

@section('crm_contents')
    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Chart dan Target Sales dalam satu baris -->
        <div class="row g-4">
            <!-- Kategori Perusahaan -->
            <div class="col-md-6">
                <div class="card h-100 shadow-sm border-0 rounded-3">
                    <div class="card-header bg-transparent border-bottom-0 pb-0">
                        <h5 class="card-title mb-0 text-primary">Distribusi Kategori Perusahaan</h5>
                    </div>
                    <div class="card-body d-flex align-items-center justify-content-center">
                        <div style="width: 100%; height: 300px;">
                            <canvas id="kategoriChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Target Aktivitas Sales -->
            <div class="col-md-6">
                <div class="card h-100 shadow-sm border-0 rounded-3">
                    <div class="card-header bg-transparent border-bottom-0 pb-0">
                        <h5 class="card-title mb-0 text-primary">Target Aktivitas per Sales</h5>
                    </div>
                    <div class="card-body" style="max-height: 300px; overflow-y: auto; font-size: 0.875rem;">
                        @forelse ($activitysales as $sales)
                            <div class="mb-3">
                                <strong class="text-dark">{{ $sales['id_sales'] }}</strong>
                                @php
                                    $aktivitas = [
                                        'Call' => [
                                            'jumlah' => $sales['call'],
                                            'target' => $sales['target_call'],
                                            'warna' => 'info',
                                        ],
                                        'Email' => [
                                            'jumlah' => $sales['email'],
                                            'target' => $sales['target_email'],
                                            'warna' => 'warning',
                                        ],
                                        'Visit' => [
                                            'jumlah' => $sales['visit'],
                                            'target' => $sales['target_visit'],
                                            'warna' => 'success',
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
                                    <div class="mb-1">
                                        <div class="d-flex justify-content-between small mb-1">
                                            <span>{{ $label }}: {{ $data['jumlah'] }}/{{ $data['target'] }}</span>
                                            <span class="badge bg-light text-dark">{{ $persen }}%</span>
                                        </div>
                                        <div class="progress" style="height: 6px;">
                                            <div class="progress-bar bg-{{ $data['warna'] }}" role="progressbar"
                                                style="width: {{ $persen }}%;" aria-valuenow="{{ $persen }}"
                                                aria-valuemin="0" aria-valuemax="100">
                                            </div>
                                        </div>
                                    </div>
                                @endforeach

                                @if (!$loop->last)
                                    <hr class="my-2">
                                @endif
                            </div>
                        @empty
                            <p class="text-muted small">Tidak ada data aktivitas sales.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const ctx = document.getElementById('kategoriChart').getContext('2d');
            const chartData = @json($chartData);

            new Chart(ctx, {
                type: 'doughnut',
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
                        borderColor: '#fff',
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                font: {
                                    size: 13
                                },
                                padding: 10
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: (context) => `${context.label}: ${context.raw}%`
                            }
                        }
                    }
                }
            });
        });
    </script>

@endsection
