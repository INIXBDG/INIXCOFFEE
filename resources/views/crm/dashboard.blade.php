@extends('layouts_crm.app')

@section('crm_contents')
    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Chart dan Target Sales -->
        <div class="row g-3 mb-4">
            <!-- Data Perusahaan -->
            <div class="col-lg-6 col-md-12 mb-3">
                <div class="card h-100 shadow-sm border-0 rounded-3">
                    <div class="card-header bg-transparent border-0 pb-0">
                        <h5 class="card-title mb-0 text-primary">Data Perusahaan</h5>
                    </div>
                    <div class="card-body p-3">
                        <div class="chart-container" style="position: relative; height: 280px;">
                            <canvas id="kategoriChart"></canvas>
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
                        <!-- Filter Section -->
                        <div class="mb-3">
                            <div class="btn-group btn-group-sm" role="group">
                                <button type="button" class="btn btn-outline-primary filter-btn active"
                                    data-filter="all">All</button>
                                <button type="button" class="btn btn-outline-primary filter-btn"
                                    data-filter="Call">Call</button>
                                <button type="button" class="btn btn-outline-primary filter-btn"
                                    data-filter="Email">Email</button>
                                <button type="button" class="btn btn-outline-primary filter-btn"
                                    data-filter="Visit">Visit</button>
                            </div>
                        </div>
                        <div class="activity-container" style="max-height: 280px; overflow-y: auto;">
                            @forelse ($activitysales as $sales)
                                <div class="mb-3 sales-item" data-sales-id="{{ $sales['id_sales'] }}">
                                    <strong class="text-dark d-block mb-2">{{ $sales['id_sales'] }}</strong>
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
                                                <span class="small text-muted">{{ $label }}:
                                                    {{ $data['jumlah'] }}/{{ $data['target'] }}</span>
                                                <span
                                                    class="badge bg-{{ $data['warna'] }}-subtle text-dark">{{ $persen }}%</span>
                                            </div>
                                            <div class="progress" style="height: 6px;">
                                                <div class="progress-bar bg-{{ $data['warna'] }}"
                                                    style="width: {{ $persen }}%;"></div>
                                            </div>
                                        </div>
                                    @endforeach
                                    @if (!$loop->last)
                                        <hr class="my-3 opacity-25">
                                    @endif
                                </div>
                            @empty
                                <div class="text-center py-3">
                                    <p class="text-muted small mb-0">Tidak ada data aktivitas sales.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Win and Total Lost -->
        <div class="row g-3 mb-4">
            <!-- Total Win -->
            <div class="col-lg-6 col-md-12 mb-3">
                <div class="card h-100 shadow-sm border-0 rounded-3">
                    <div class="card-header bg-transparent border-0 pb-0 d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0 text-primary">Total Win</h5>
                        <select class="form-select form-select-sm win-year-filter" style="max-width: 120px;" hidden>
                            @for ($year = now()->year - 5; $year <= now()->year + 1; $year++)
                                <option value="{{ $year }}" {{ $tahunDipilih == $year ? 'selected' : '' }}>
                                    {{ $year }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="card-body p-3">
                        <div class="chart-container" style="position: relative; height: 280px;">
                            <canvas id="totalWinChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Lost -->
            <div class="col-lg-6 col-md-12 mb-3">
                <div class="card h-100 shadow-sm border-0 rounded-3">
                    <div class="card-header bg-transparent border-0 pb-0 d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0 text-primary">Total Lost</h5>
                        <select class="form-select form-select-sm lost-year-filter" style="max-width: 120px;">
                            @for ($year = now()->year - 5; $year <= now()->year + 1; $year++)
                                <option value="{{ $year }}" {{ $tahunDipilih == $year ? 'selected' : '' }}>
                                    {{ $year }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="card-body p-3">
                        <div class="chart-container" style="position: relative; height: 280px;">
                            <canvas id="totalLostChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top 5 Produk -->
        <div class="row g-3">
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
        </div>
    </div>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Initialize all charts with responsive container
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
                            }
                        }
                    }
                });
            };

            // Kategori Chart
            const chartData = @json($chartData);
            initChart('kategoriChart', {
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
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: context => `${context.label}: ${context.raw}%`
                            }
                        }
                    }
                }
            });

            // Total Win Chart
            const totalWinData = @json($totalWin);
            const winLabels = ['TR1', 'TR2', 'TR3', 'TR4'];
            const winDatasets = Object.keys(totalWinData).map((id_sales, index) => {
                const sales = totalWinData[id_sales];
                const colors = [
                    'rgba(75, 192, 192, 0.8)',
                    'rgba(255, 99, 132, 0.8)',
                    'rgba(54, 162, 235, 0.8)',
                    'rgba(255, 206, 86, 0.8)',
                    'rgba(153, 102, 255, 0.8)',
                ];
                return {
                    label: sales.username.toUpperCase(),
                    data: [sales.TR1, sales.TR2, sales.TR3, sales.TR4],
                    backgroundColor: colors[index % colors.length],
                    borderWidth: 1
                };
            });

            initChart('totalWinChart', {
                type: 'bar',
                data: {
                    labels: winLabels,
                    datasets: winDatasets
                },
                options: {
                    scales: {
                        x: {
                            stacked: true
                        },
                        y: {
                            stacked: true,
                            ticks: {
                                callback: function(value) {
                                    return new Intl.NumberFormat('id-ID').format(value);
                                }
                            }
                        }
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: context =>
                                    `${context.dataset.label}: ${new Intl.NumberFormat('id-ID').format(context.raw)}`
                            }
                        }
                    }
                }
            });

            // Total Lost Chart
            const totalLostData = @json($totalLost);
            const lostDatasets = Object.keys(totalLostData).map((id_sales, index) => {
                const sales = totalLostData[id_sales];
                const colors = [
                    'rgba(255, 99, 132, 0.8)',
                    'rgba(75, 192, 192, 0.8)',
                    'rgba(54, 162, 235, 0.8)',
                    'rgba(255, 206, 86, 0.8)',
                    'rgba(153, 102, 255, 0.8)',
                ];
                return {
                    label: sales.username.toUpperCase(),
                    data: [sales.TR1, sales.TR2, sales.TR3, sales.TR4],
                    backgroundColor: colors[index % colors.length],
                    borderWidth: 1
                };
            });

            initChart('totalLostChart', {
                type: 'bar',
                data: {
                    labels: winLabels,
                    datasets: lostDatasets
                },
                options: {
                    scales: {
                        x: {
                            stacked: true
                        },
                        y: {
                            stacked: true,
                            ticks: {
                                callback: function(value) {
                                    return new Intl.NumberFormat('id-ID').format(value);
                                }
                            }
                        }
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: context =>
                                    `${context.dataset.label}: ${new Intl.NumberFormat('id-ID').format(context.raw)}`
                            }
                        }
                    }
                }
            });

            // Filter functionality for activities
            const filterButtons = document.querySelectorAll('.filter-btn');

            filterButtons.forEach(button => {
                button.addEventListener('click', () => {
                    filterButtons.forEach(btn => btn.classList.remove('active'));
                    button.classList.add('active');
                    applyActivityFilter();
                });
            });

            function applyActivityFilter() {
                const activityFilter = document.querySelector('.filter-btn.active').dataset.filter;
                const salesItems = document.querySelectorAll('.sales-item');
                const activityItems = document.querySelectorAll('.activity-item');

                activityItems.forEach(item => {
                    const activityType = item.dataset.activity;
                    item.style.display = (activityFilter === 'all' || activityType === activityFilter) ?
                        'block' : 'none';
                });

                salesItems.forEach(item => {
                    const visibleActivities = item.querySelectorAll(
                        '.activity-item[style="display: block;"]');
                    item.style.display = visibleActivities.length > 0 ? 'block' : 'none';
                });
            }

            // Handle year filters
            const winYearFilter = document.querySelector('.win-year-filter');
            const lostYearFilter = document.querySelector('.lost-year-filter');

            function handleYearChange() {
                const selectedYear = this.value;
                if (this === winYearFilter) {
                    lostYearFilter.value = selectedYear;
                } else {
                    winYearFilter.value = selectedYear;
                }
                window.location.href = `?tahun=${selectedYear}`;
            }

            winYearFilter.addEventListener('change', handleYearChange);
            lostYearFilter.addEventListener('change', handleYearChange);

            // Initialize filters
            applyActivityFilter();
        });
    </script>

    <style>
        /* Improved responsive behavior */
        @media (max-width: 767.98px) {
            .card-body {
                padding: 1rem !important;
            }

            .btn-group {
                width: 100%;
                flex-wrap: wrap;
            }

            .btn-group .btn {
                flex: 1 0 45%;
                margin-bottom: 5px;
            }

            .form-select-sm {
                width: 100% !important;
                max-width: 100% !important;
            }
        }

        /* Better scrollbars */
        .activity-container::-webkit-scrollbar,
        .card-body::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        .activity-container::-webkit-scrollbar-track,
        .card-body::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 3px;
        }

        .activity-container::-webkit-scrollbar-thumb,
        .card-body::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 3px;
        }

        /* Improved progress bars */
        .progress {
            background-color: #f0f0f0;
            border-radius: 3px;
        }

        .progress-bar {
            border-radius: 3px;
        }
    </style>
@endsection
