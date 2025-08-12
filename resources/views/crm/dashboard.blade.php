@extends('layouts_crm.app')

@section('crm_contents')
    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Chart dan Target Sales -->
        <div class="row g-4 mb-4">
            <!-- Data Perusahaan -->
            <div class="col-md-6">
                <div class="card h-100 shadow-sm border-0 rounded-3">
                    <div class="card-header bg-transparent border-0 pb-0">
                        <h5 class="card-title mb-0 text-primary">Data Perusahaan</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="kategoriChart" style="height: 280px;"></canvas>
                    </div>
                </div>
            </div>

            <!-- Target Aktivitas Sales -->
            <div class="col-md-6">
                <div class="card h-100 shadow-sm border-0 rounded-3">
                    <div class="card-header bg-transparent border-0 pb-0">
                        <h5 class="card-title mb-0 text-primary">Target Aktivitas Sales</h5>
                    </div>
                    <div class="card-body">
                        <!-- Filter Section -->
                        <div class="mb-3">
                            <div class="d-flex flex-wrap gap-2 align-items-center">
                                <div class="btn-group">
                                    <button class="btn btn-outline-primary btn-sm filter-btn active"
                                        data-filter="all">All</button>
                                    <button class="btn btn-outline-primary btn-sm filter-btn"
                                        data-filter="Call">Call</button>
                                    <button class="btn btn-outline-primary btn-sm filter-btn"
                                        data-filter="Email">Email</button>
                                    <button class="btn btn-outline-primary btn-sm filter-btn"
                                        data-filter="Visit">Visit</button>
                                </div>
                            </div>
                        </div>
                        <div style="max-height: 280px; overflow-y: auto;">
                            @forelse ($activitysales as $sales)
                                <div class="mb-3 sales-item" data-sales-id="{{ $sales['id_sales'] }}">
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
                                        <div class="mb-2 activity-item" data-activity="{{ $label }}">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <span class="small">{{ $label }}:
                                                    {{ $data['jumlah'] }}/{{ $data['target'] }}</span>
                                                <span
                                                    class="badge bg-{{ $data['warna'] }}-subtle text-dark">{{ $persen }}%</span>
                                            </div>
                                            <div class="progress" style="height: 5px;">
                                                <div class="progress-bar bg-{{ $data['warna'] }}"
                                                    style="width: {{ $persen }}%;"></div>
                                            </div>
                                        </div>
                                    @endforeach
                                    @if (!$loop->last)
                                        <hr class="my-2 opacity-25">
                                    @endif
                                </div>
                            @empty
                                <p class="text-muted small mb-0">Tidak ada data aktivitas sales.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top 5 Produk -->
        <div class="row g-2">
            <!-- Top 5 Produk Terjual -->
            <div class="col-md-4">
                <div class="card h-100 shadow-sm border-0 rounded-3">
                    <div class="card-header bg-transparent border-0 pb-0">
                        <h5 class="card-title mb-0 text-primary">Top 5 Produk Terjual</h5>
                    </div>
                    <div class="card-body" style="max-height: 280px; overflow-y: auto;">
                        @forelse ($best as $item)
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div>
                                    <strong
                                        class="text-dark">{{ $item->materi->nama_materi ?? $item->materi_key }}</strong>
                                    </p>
                                </div>
                                <span
                                    class="badge bg-success-subtle text-success">{{ number_format($item->total_pax, 0, ',', '.') }}
                                    Pax</span>
                            </div>
                            @if (!$loop->last)
                                <hr class="my-2 opacity-25">
                            @endif
                        @empty
                            <p class="text-muted small mb-0">Tidak ada data produk terjual.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Top 5 Produk Menguntungkan -->
            <div class="col-md-4">
                <div class="card h-100 shadow-sm border-0 rounded-3">
                    <div class="card-header bg-transparent border-0 pb-0">
                        <h5 class="card-title mb-0 text-primary">Top 5 Produk Menguntungkan</h5>
                    </div>
                    <div class="card-body" style="max-height: 280px; overflow-y: auto;">
                        @forelse ($profit as $item)
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div>
                                    <strong
                                        class="text-dark">{{ $item->materi->nama_materi ?? $item->materi_key }}</strong>
                                </div>
                                <span class="badge bg-info-subtle text-info">Rp
                                    {{ number_format($item->total_revenue, 0, ',', '.') }}</span>
                            </div>
                            @if (!$loop->last)
                                <hr class="my-2 opacity-25">
                            @endif
                        @empty
                            <p class="text-muted small mb-0">Tidak ada data produk menguntungkan.</p>
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
                        borderWidth: 1
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
                                    size: 12
                                },
                                padding: 10
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: context => `${context.label}: ${context.raw}%`
                            }
                        }
                    }
                }
            });

            // Filter functionality
            const filterButtons = document.querySelectorAll('.filter-btn');
            const salesItems = document.querySelectorAll('.sales-item');
            const activityItems = document.querySelectorAll('.activity-item');
            const startDateInput = document.getElementById('startDate');
            const endDateInput = document.getElementById('endDate');
            const applyDateFilter = document.getElementById('applyDateFilter');

            // Handle activity type filter
            filterButtons.forEach(button => {
                button.addEventListener('click', () => {
                    filterButtons.forEach(btn => btn.classList.remove('active'));
                    button.classList.add('active');
                    const filter = button.getAttribute('data-filter');

                    activityItems.forEach(item => {
                        const activityType = item.getAttribute('data-activity');
                        if (filter === 'all' || activityType === filter) {
                            item.style.display = 'block';
                        } else {
                            item.style.display = 'none';
                        }
                    });

                    // Show/hide sales items based on visible activities
                    salesItems.forEach(item => {
                        const visibleActivities = item.querySelectorAll(
                            '.activity-item[style="display: block;"]');
                        item.style.display = visibleActivities.length > 0 ? 'block' :
                        'none';
                    });
                });
            });
        });
    </script>
@endsection
