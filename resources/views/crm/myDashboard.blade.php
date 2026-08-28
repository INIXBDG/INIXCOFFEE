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
                                                ({{ $item['persen'] }}%)
                                            </span>
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
                    <div class="card-header bg-light border-0 py-3 px-4">
                        <h5 class="card-title mb-2 text-primary fw-bold">Target Aktivitas Sales</h5>
                        <div class="d-flex">
                            <span class="text-muted small fw-medium">{{ $tanggal }}  |</span>
                            <span class="text-muted small fw-medium"> Minggu Ke {{ $mingguKeBulan }}</span>
                        </div>
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
                                                'warna' => 'warning',
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
                                                <span class="badge bg-{{ $data['warna'] }}-subtle text-dark"
                                                    style="cursor: pointer;"
                                                    data-sales-id="{{ $activitysales['id_sales'] }}"
                                                    data-activity="{{ $label }}">{{ $persen }}%</span>
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

            <!-- Modal for Activity Details -->
            <div id="detailAktivitas" class="w3-modal" style="display: none;">
                <div class="w3-modal-content w3-animate-zoom" style="max-width: 700px; border-radius: 0.5rem;">
                    <div class="w3-container p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="modal-title text-primary mb-0">Detail Aktivitas Sales</h5>
                            <button type="button" class="btn-close"
                                onclick="document.getElementById('detailAktivitas').style.display='none'"
                                aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <strong class="text-dark">Sales ID:</strong>
                                <span id="modalSalesId" class="text-muted"></span>
                            </div>
                            <div class="mb-3">
                                <strong class="text-dark">Aktivitas:</strong>
                                <span id="modalActivity" class="text-muted"></span>
                            </div>
                            <div class="mb-3">
                                <strong class="text-dark">Jumlah:</strong>
                                <span id="modalJumlah" class="text-muted"></span>
                            </div>
                            <div class="mb-3">
                                <strong class="text-dark">Target:</strong>
                                <span id="modalTarget" class="text-muted"></span>
                            </div>
                            <div class="mb-3" id="modalTotalGroup" style="display: none;">
                                <strong class="text-dark">Total:</strong>
                                <span id="modalTotal" class="text-muted"></span>
                            </div>
                            <div class="mb-3">
                                <strong class="text-dark">Persentase:</strong>
                                <span id="modalPersen" class="badge bg-info-subtle text-info"></span>
                            </div>
                            <div class="progress" style="height: 10px;">
                                <div id="modalProgressBar" class="progress-bar" style="width: 0%;"></div>
                            </div>
                        </div>

                        <table class="table table-hover table-striped table-bordered align-middle">
                            <thead>
                                <tr>
                                    <th scope="col">Client</th>
                                    <th scope="col">Aktivitas</th>
                                    <th scope="col">Deskripsi</th>
                                    <th scope="col">Total</th>
                                    <th scope="col">Waktu Aktivitas</th>
                                </tr>
                            </thead>
                            <tbody id="activityDetailsTableBody">
                            </tbody>
                        </table>

                        <div class="modal-footer d-flex justify-content-end">
                            <button type="button" class="btn btn-outline-secondary btn-sm"
                                onclick="document.getElementById('detailAktivitas').style.display='none'">Tutup</button>
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
                                                ({{ $item['persen'] }}%)
                                            </span>
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

            // Activity data from PHP
            const activityData = @json($activitysales);

            // Function to show modal with activity details
            function showActivityDetails(salesId, activityLabel) {
                // Handle case where activityData is an object or array
                const sales = Array.isArray(activityData) ? activityData.find(s => s.id_sales === salesId) :
                    activityData;
                if (!sales) {
                    console.error(`Sales data not found for ID: ${salesId}`);
                    return;
                }

                const activityMap = {
                    'DB': {
                        jumlah: sales.DB,
                        target: sales.target_DB,
                        warna: 'info',
                        dataKey: 'data_DB'
                    },
                    'Contact': {
                        jumlah: sales.contact,
                        target: sales.target_contact,
                        warna: 'info',
                        dataKey: 'data_contact'
                    },
                    'Call': {
                        jumlah: sales.call,
                        target: sales.target_call,
                        warna: 'info',
                        dataKey: 'data_call'
                    },
                    'Email': {
                        jumlah: sales.email,
                        target: sales.target_email,
                        warna: 'warning',
                        dataKey: 'data_email'
                    },
                    'Visit': {
                        jumlah: sales.visit,
                        target: sales.target_visit,
                        warna: 'warning',
                        dataKey: 'data_visit'
                    },
                    'Meet': {
                        jumlah: sales.meet,
                        target: sales.target_meet,
                        warna: 'warning',
                        dataKey: 'data_meet'
                    },
                    'Incharge': {
                        jumlah: sales.incharge,
                        target: sales.target_incharge,
                        warna: 'success',
                        dataKey: 'data_incharge'
                    },
                    'Penawaran Awal': {
                        jumlah: sales.PA,
                        target: sales.target_PA,
                        warna: 'success',
                        total: sales.total_PA ?? 0,
                        dataKey: 'data_PA'
                    },
                    'Penawaran Internal': {
                        jumlah: sales.PI,
                        target: sales.target_PI,
                        warna: 'success',
                        dataKey: 'data_PI'
                    },
                    'Telemarketing': {
                        jumlah: sales.Telemarketing,
                        target: sales.target_Telemarketing,
                        warna: 'danger',
                        dataKey: 'data_Telemarketing'
                    },
                    'Form Masuk': {
                        jumlah: sales.Form_Masuk,
                        target: sales.target_Form_Masuk,
                        warna: 'danger',
                        total: sales.total_Form_Masuk ?? 0,
                        dataKey: 'data_Form_Masuk'
                    },
                    'Form Keluar': {
                        jumlah: sales.Form_Keluar,
                        target: sales.target_Form_Keluar,
                        warna: 'danger',
                        total: sales.total_Form_Keluar ?? 0,
                        dataKey: 'data_Form_Keluar'
                    },
                };

                const activity = activityMap[activityLabel];
                if (!activity) {
                    console.error(`Activity not found: ${activityLabel}`);
                    return;
                }

                // Calculate percentage
                const persen = activity.target > 0 ? Math.min(Math.round((activity.jumlah / activity.target) * 100),
                    100) : 0;

                // Populate modal fields
                document.getElementById('modalSalesId').textContent = salesId || '-';
                document.getElementById('modalActivity').textContent = activityLabel || '-';
                document.getElementById('modalJumlah').textContent = Number(activity.jumlah || 0).toLocaleString(
                    'id-ID');
                document.getElementById('modalTarget').textContent = Number(activity.target || 0).toLocaleString(
                    'id-ID');
                document.getElementById('modalPersen').textContent = `${persen}%`;
                document.getElementById('modalProgressBar').style.width = `${persen}%`;
                document.getElementById('modalProgressBar').className = `progress-bar bg-${activity.warna}`;

                // Handle total field visibility
                const modalTotalGroup = document.getElementById('modalTotalGroup');
                const modalTotal = document.getElementById('modalTotal');
                if (['Penawaran Awal', 'Form Masuk', 'Form Keluar'].includes(activityLabel)) {
                    modalTotalGroup.style.display = 'block';
                    modalTotal.textContent = `Rp ${Number(activity.total || 0).toLocaleString('id-ID')}`;
                } else {
                    modalTotalGroup.style.display = 'none';
                    modalTotal.textContent = '';
                }

                // Populate activity details table
                const tableBody = document.getElementById('activityDetailsTableBody');
                tableBody.innerHTML = ''; // Clear previous content

                const activityDetails = activity.dataKey && Array.isArray(sales[activity.dataKey]) ? sales[activity
                    .dataKey] : [];
                if (activityDetails.length > 0) {
                    activityDetails.forEach(item => {
                        const clientName = item.contact?.perusahaan ?
                            `${item.contact.nama ?? '-'} (${item.contact.perusahaan.nama_perusahaan})` :
                            item.contact ?
                            `${item.contact.nama ?? '-'}` :
                            item.peserta ?
                            `${item.peserta.nama ?? '-'} (Peserta)` :
                            '-';
                        const row = `
                            <tr>
                                <td>${clientName}</td>
                                <td>${item.aktivitas ?? '-'}</td>
                                <td>${item.deskripsi ?? '-'}</td>
                                <td>${item.total ? 'Rp ' + Number(item.total).toLocaleString('id-ID') : '-'}</td>
                                <td>${item.waktu_aktivitas ? new Date(item.waktu_aktivitas).toLocaleDateString('id-ID') : '-'}</td>
                            </tr>
                        `;
                        tableBody.insertAdjacentHTML('beforeend', row);
                    });
                } else {
                    tableBody.innerHTML = `
                        <tr>
                            <td colspan="5" class="text-center text-muted py-3">
                                Tidak ada data aktivitas untuk jenis ini.
                            </td>
                        </tr>
                    `;
                }

                // Show modal
                document.getElementById('detailAktivitas').style.display = 'block';
            }

            // Attach click event to percentage badges
            document.querySelectorAll('.activity-item .badge[data-sales-id][data-activity]').forEach(badge => {
                badge.addEventListener('click', () => {
                    const salesId = badge.dataset.salesId;
                    const activityLabel = badge.dataset.activity;
                    console.log(
                    `Badge clicked: Sales ID = ${salesId}, Activity = ${activityLabel}`); // Debugging
                    showActivityDetails(salesId, activityLabel);
                });
            });

            // Initialize Kategori Chart
            const chartData = @json($chartData);
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

            // Initialize Lokasi Pie Chart
            const lokasiData = @json($totalDaerah);
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
    <style>
        /* Map container styling */
        #map {
            height: 400px;
            width: 100%;
            border-radius: 0.5rem;
            border: 1px solid #e3e6f0;
            background-color: #f8f9fa;
            z-index: 1;
        }

        /* Responsive map height */
        @media (max-width: 767.98px) {
            #map {
                height: 300px;
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

        /* Modal Styling */
        .w3-modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.5);
        }

        .w3-modal-content {
            background-color: #fff;
            margin: 5% auto;
            padding: 0;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            border-radius: 0.5rem;
        }

        .w3-animate-zoom {
            animation: zoom 0.3s;
        }

        @keyframes zoom {
            from {
                transform: scale(0);
            }

            to {
                transform: scale(1);
            }
        }

        .modal-body {
            padding: 1.5rem;
        }

        .modal-footer {
            padding: 1rem 1.5rem;
            border-top: 1px solid #dee2e6;
        }

        .btn-close {
            background: transparent;
            border: none;
            font-size: 1.2rem;
            cursor: pointer;
            color: #6c757d;
        }

        .btn-close:hover {
            color: #343a40;
        }

        /* Responsive modal */
        @media (max-width: 576px) {
            .w3-modal-content {
                margin: 10% auto;
                width: 95%;
            }

            .modal-body {
                padding: 1rem;
            }

            .modal-footer {
                padding: 0.75rem 1rem;
            }
        }
    </style>
@endsection
