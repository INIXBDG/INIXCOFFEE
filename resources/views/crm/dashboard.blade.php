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
                                @foreach ($activitysales as $sales)
                                    <button type="button" class="btn btn-outline-primary filter-btn"
                                        data-filter="{{ $sales['id_sales'] }}">{{ $sales['id_sales'] }}</button>
                                @endforeach
                            </div>
                        </div>
                        <div class="activity-container" style="max-height: 280px; overflow-y: auto;">
                            @forelse ($activitysales as $sales)
                                <div class="mb-3 sales-item" data-sales-id="{{ $sales['id_sales'] }}">
                                    <strong class="text-dark d-block mb-2">{{ $sales['id_sales'] }}</strong>
                                    @php
                                        $aktivitas = [
                                            'DB' => [
                                                'jumlah' => $sales['DB'],
                                                'target' => $sales['target_DB'],
                                                'warna' => 'info',
                                            ],
                                            'Contact' => [
                                                'jumlah' => $sales['contact'],
                                                'target' => $sales['target_contact'],
                                                'warna' => 'info',
                                            ],
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
                                                'warna' => 'warning',
                                            ],
                                            'Meet' => [
                                                'jumlah' => $sales['meet'],
                                                'target' => $sales['target_meet'],
                                                'warna' => 'warning',
                                            ],
                                            'Incharge' => [
                                                'jumlah' => $sales['incharge'],
                                                'target' => $sales['target_incharge'],
                                                'warna' => 'success',
                                            ],
                                            'Penawaran Awal' => [
                                                'jumlah' => $sales['PA'],
                                                'target' => $sales['target_PA'],
                                                'warna' => 'success',
                                            ],
                                            'Penawaran Internal' => [
                                                'jumlah' => $sales['PI'],
                                                'target' => $sales['target_PI'],
                                                'warna' => 'success',
                                            ],
                                            'Telemarketing' => [
                                                'jumlah' => $sales['Telemarketing'],
                                                'target' => $sales['target_Telemarketing'],
                                                'warna' => 'danger',
                                            ],
                                            'Form Masuk' => [
                                                'jumlah' => $sales['Form_Masuk'],
                                                'target' => $sales['target_Form_Masuk'],
                                                'warna' => 'danger',
                                            ],
                                            'Form Keluar' => [
                                                'jumlah' => $sales['Form_Keluar'],
                                                'target' => $sales['target_Form_Keluar'],
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
        <div class="row g-3 mb-4">
            <div class="col-12">
                <div class="card h-100 shadow-sm border-0 rounded-3">
                    <div class="card-header bg-transparent border-0 pb-0">
                        <h5 class="card-title mb-0 text-primary">Total Status Perusahaan per Sales</h5>
                    </div>
                    <div class="card-body p-3">
                        <div class="table-responsive" style="max-height: 280px; overflow-y: auto;">
                            <table class="table table-hover table-striped">
                                <thead>
                                    <tr>
                                        <th scope="col" class="text-primary">Sales</th>
                                        @php
                                            $statuses = $totalStatus->pluck('status')->unique()->sort();
                                        @endphp
                                        @foreach ($statuses as $status)
                                            <th scope="col" class="text-primary">{{ $status }}</th>
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
                                            <td>{{ $salesKey }}</td>
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
        </div>

        <!-- Distribusi Perusahaan per Lokasi -->
        <div class="row g-3">
            <div class="col-12">
                <div class="card h-100 shadow-sm border-0 rounded-3">
                    <div
                        class="card-header bg-transparent border-0 pb-0 d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0 text-primary">Distribusi Perusahaan per Lokasi</h5>
                    </div>
                    <div class="card-body p-3">
                        <div id="map"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Leaflet.js and Chart.js -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
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
                            stacked: false
                        },
                        y: {
                            stacked: false,
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
                            stacked: false
                        },
                        y: {
                            stacked: false,
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

            // Initialize the map
            var map = L.map('map').setView([-2.548926, 118.0148634], 5); // Centered on Indonesia

            // Add OpenStreetMap tile layer
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);

            // Layer group to manage markers
            var markerLayer = L.layerGroup().addTo(map);

            // Function to update map markers
            function updateMapMarkers(salesKey) {
                // Clear existing markers
                markerLayer.clearLayers();

                // Filter locations based on sales key
                let filteredLocations = [];
                if (salesKey === 'all') {
                    filteredLocations = @json($map); // All locations
                } else {
                    filteredLocations = @json($map).filter(loc => loc.sales_key === salesKey);
                }

                // Filter out locations with no companies
                filteredLocations = filteredLocations.filter(loc => loc.company_count > 0);

                // Total number of companies for percentage calculation
                var totalCompanies = filteredLocations.reduce((sum, loc) => sum + (loc.company_count || 0), 0);

                // Add markers for each location with valid data
                filteredLocations.forEach(function(loc) {
                    if (loc.latitude && loc.longitude && loc.company_count > 0) {
                        // Calculate percentage
                        var percentage = totalCompanies > 0 ? ((loc.company_count / totalCompanies) * 100)
                            .toFixed(2) : 0;

                        // Create marker
                        var marker = L.marker([loc.latitude, loc.longitude]);

                        // Popup content
                        var popupContent = `
                            <b>Location:</b> ${loc.lokasi}<br>
                            <b>Companies:</b> ${loc.company_count}<br>
                            <b>Percentage:</b> ${percentage}% | ${loc.company_count}
                        `;
                        marker.bindPopup(popupContent);

                        // Tooltip for quick view
                        marker.bindTooltip(`${loc.lokasi}: ${percentage}%`, {
                            permanent: false
                        });

                        // Add marker to layer
                        markerLayer.addLayer(marker);
                    }
                });

                // Display message if no markers are present
                const mapContainer = document.getElementById('map');
                if (filteredLocations.length === 0) {
                    mapContainer.innerHTML =
                        '<div class="text-center text-muted p-3">Tidak ada data lokasi tersedia</div>';
                } else {
                    // Reinitialize map if it was cleared
                    if (!map._container) {
                        map = L.map('map').setView([-2.548926, 118.0148634], 5);
                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                        }).addTo(map);
                        markerLayer = L.layerGroup().addTo(map);
                        filteredLocations.forEach(function(loc) {
                            if (loc.latitude && loc.longitude && loc.company_count > 0) {
                                var marker = L.marker([loc.latitude, loc.longitude]);
                                var percentage = totalCompanies > 0 ? ((loc.company_count /
                                    totalCompanies) * 100).toFixed(2) : 0;
                                var popupContent = `
                                    <b>Location:</b> ${loc.lokasi}<br>
                                    <b>Companies:</b> ${loc.company_count}<br>
                                    <b>Percentage:</b> ${percentage}%
                                `;
                                marker.bindPopup(popupContent);
                                marker.bindTooltip(`${loc.lokasi}: ${percentage}% | ${loc.company_count}`, {
                                    permanent: false
                                });
                                markerLayer.addLayer(marker);
                            }
                        });
                    }
                }
            }

            // Initial map render
            updateMapMarkers('all');

            const filterButtons = document.querySelectorAll('.filter-btn');
            filterButtons.forEach(button => {
                button.addEventListener('click', () => {
                    filterButtons.forEach(btn => btn.classList.remove('active'));
                    button.classList.add('active');
                    applySalesFilter();
                });
            });

            function applySalesFilter() {
                const selectedSalesId = document.querySelector('.filter-btn.active')?.dataset.filter;
                if (!selectedSalesId) return;

                const salesItems = document.querySelectorAll('.sales-item');
                salesItems.forEach(item => {
                    item.style.display = (selectedSalesId === 'all' || item.dataset.salesId ===
                        selectedSalesId) ? 'block' : 'none';
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

            if (winYearFilter) winYearFilter.addEventListener('change', handleYearChange);
            if (lostYearFilter) lostYearFilter.addEventListener('change', handleYearChange);

            // Initialize filters
            applyActivityFilter();
        });
    </script>

    <style>
        /* Map container styling */
        #map {
            height: 400px;
            /* Increased height since chart is removed */
            width: 100%;
            /* Full width of card-body */
            border-radius: 0.5rem;
            /* Match card's rounded-3 style */
            border: 1px solid #e3e6f0;
            /* Subtle border */
            background-color: #f8f9fa;
            /* Light background */
            z-index: 1;
            /* Ensure below Leaflet controls */
        }

        /* Responsive map height */
        @media (max-width: 767.98px) {
            #map {
                height: 300px;
                /* Smaller height for mobile */
            }

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

        /* Ensure Leaflet container inherits dimensions */
        .leaflet-container {
            width: 100%;
            height: 100%;
            border-radius: 0.5rem;
        }

        /* Prevent overflow in card */
        .card.h-100 {
            overflow: hidden;
        }

        /* Ensure card-body has proper spacing */
        .card-body {
            padding: 1.5rem !important;
        }

        /* Style Leaflet controls */
        .leaflet-control {
            border-radius: 0.25rem;
            background-color: rgba(255, 255, 255, 0.9);
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.1);
        }

        /* Chart and activity container styling */
        .chart-container,
        .activity-container {
            max-height: 280px;
            overflow: hidden;
        }

        /* Scrollbar styling */
        .activity-container::-webkit-scrollbar,
        .card-body::-webkit-scrollbar,
        .table-responsive::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        .activity-container::-webkit-scrollbar-track,
        .card-body::-webkit-scrollbar-track,
        .table-responsive::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 3px;
        }

        .activity-container::-webkit-scrollbar-thumb,
        .card-body::-webkit-scrollbar-thumb,
        .table-responsive::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 3px;
        }

        /* Progress bars */
        .progress {
            background-color: #f0f0f0;
            border-radius: 3px;
        }

        .progress-bar {
            border-radius: 3px;
        }

        /* Table styling */
        .table {
            margin-bottom: 0;
        }

        .table th,
        .table td {
            padding: 0.75rem;
            vertical-align: middle;
            text-align: center;
        }

        .table thead th {
            position: sticky;
            top: 0;
            background: #fff;
            z-index: 1;
            border-bottom: 2px solid #dee2e6;
        }
    </style>
@endsection
