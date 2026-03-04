@extends('layouts_office.app')

@section('office_contents')
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-5">
            <div>
                <h4 class="mb-0 fw-bold text-dark">Dashboard Souvenir & Merchandise</h4>
                <p class="text-muted mb-0 small">Monitoring stok, distribusi, dan penukaran barang.</p>
            </div>
            <div class="text-end">
                <small class="text-muted fw-medium d-block">{{ now()->translatedFormat('l, d F Y') }}</small>
            </div>
        </div>

        <div class="row mb-5 g-4">
            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm h-100 hover-card rounded-4 overflow-hidden">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="avatar avatar-lg bg-primary bg-opacity-10 rounded-circle p-3">
                                    <i class="bx bx-refresh text-primary" style="font-size: 2rem;"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="text-muted mb-1 text-uppercase small tracking-wider">Total Penukaran</h6>
                                <h2 class="mb-0 fw-bold text-dark">{{ number_format($totalPenukaran) }}</h2>
                                <small class="text-success fw-medium">
                                    <i class="bx bx-trending-up"></i> Transaksi
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm h-100 hover-card rounded-4 overflow-hidden">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="avatar avatar-lg bg-success bg-opacity-10 rounded-circle p-3">
                                    <i class="bx bx-user-voice text-success" style="font-size: 2rem;"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="text-muted mb-1 text-uppercase small tracking-wider">Favorit Peserta</h6>
                                @php $top1 = $topPeserta->first(); @endphp
                                <h5 class="mb-0 fw-bold text-dark text-truncate" style="max-width: 150px;"
                                    title="{{ $top1->souvenir->nama_souvenir ?? '-' }}">
                                    {{ $top1->souvenir->nama_souvenir ?? 'Belum ada data' }}
                                </h5>
                                <small class="text-muted">
                                    Dipilih {{ $top1->total_pilih ?? 0 }} kali
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm h-100 hover-card rounded-4 overflow-hidden">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="avatar avatar-lg bg-info bg-opacity-10 rounded-circle p-3">
                                    <i class="bx bx-building text-info" style="font-size: 2rem;"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="text-muted mb-1 text-uppercase small tracking-wider">Belanja Office</h6>
                                @php $topOffice1 = $topOffice->first(); @endphp
                                <h5 class="mb-0 fw-bold text-dark text-truncate" style="max-width: 150px;"
                                    title="{{ $topOffice1->souvenir->nama_souvenir ?? '-' }}">
                                    {{ $topOffice1->souvenir->nama_souvenir ?? 'Belum ada data' }}
                                </h5>
                                <small class="text-muted">
                                    Total {{ $topOffice1->total_beli ?? 0 }} pcs
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

             <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm h-100 hover-card rounded-4 overflow-hidden">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="avatar avatar-lg bg-warning bg-opacity-10 rounded-circle p-3">
                                    <i class="bx bx-box text-warning" style="font-size: 2rem;"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="text-muted mb-1 text-uppercase small tracking-wider">Varian Souvenir</h6>
                                <h2 class="mb-0 fw-bold text-dark">{{ $stockSouvenir->count() }}</h2>
                                <small class="text-danger fw-medium">
                                    {{ $stockSouvenir->where('stok', '<', 10)->count() }} Stok Menipis
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-xl-8">
                <div class="card border-0 shadow-lg h-100 rounded-4 overflow-hidden">
                    <div class="card-header bg-white border-bottom-0 pt-4 px-4 pb-0">
                        <h5 class="mb-0 fw-semibold text-dark d-flex align-items-center">
                            <i class="bx bx-line-chart text-primary me-2" style="font-size: 1.5rem;"></i>
                            Tren Penukaran Souvenir (Tahun {{ date('Y') }})
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <div style="height: 300px;">
                            <canvas id="penukaranChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="card border-0 shadow-lg h-100 rounded-4 overflow-hidden">
                    <div class="card-header bg-white border-bottom-0 pt-4 px-4 pb-0">
                        <h5 class="mb-0 fw-semibold text-dark d-flex align-items-center">
                            {{-- Ubah Judul agar lebih umum --}}
                            <i class="bx bx-list-ul text-primary me-2" style="font-size: 1.5rem;"></i>
                            Data Stok Souvenir
                        </h5>
                    </div>
                    <div class="card-body p-0 pt-3">
                        {{-- Container Scrollable (Penting agar dashboard tidak memanjang ke bawah) --}}
                        <div class="table-responsive scrollbar-custom" style="max-height: 350px; overflow-y: auto;">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th class="ps-4">Nama Barang</th>
                                        <th class="text-center pe-4">Sisa Stok</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {{-- PERBAIKAN: Hapus ->take(10) agar meloop semua data --}}
                                    @foreach($stockSouvenir as $item)
                                        <tr>
                                            <td class="ps-4">
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar avatar-sm bg-secondary bg-opacity-10 rounded-2 me-2">
                                                        <i class="bx bx-gift text-secondary"></i>
                                                    </div>
                                                    <div>
                                                        {{-- Hapus text-truncate jika ingin melihat nama lengkap --}}
                                                        <h6 class="mb-0 text-truncate" style="max-width: 180px;" title="{{ $item->nama_souvenir }}">
                                                            {{ $item->nama_souvenir }}
                                                        </h6>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center pe-4">
                                                @if($item->stok == 0)
                                                    <span class="badge bg-danger">Habis (0)</span>
                                                @elseif($item->stok < 10)
                                                    <span class="badge bg-warning text-dark">{{ $item->stok }}</span>
                                                @else
                                                    <span class="badge bg-success">{{ $item->stok }}</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="p-3 text-center border-top">
                            {{-- Update info footer --}}
                            <small class="text-muted">Total {{ $stockSouvenir->count() }} jenis barang ditampilkan</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-4">
                <div class="card h-100 border-0 shadow-sm rounded-4">
                    <div class="card-header bg-white border-bottom py-3">
                        <h6 class="mb-0 fw-bold"><i class="bx bx-user text-success me-2"></i>Top 5 Souvenir Pilihan Peserta</h6>
                    </div>
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush">
                            @foreach($topPeserta as $index => $item)
                                <li class="list-group-item d-flex justify-content-between align-items-center py-3 px-4 border-bottom-0">
                                    <div class="d-flex align-items-center">
                                        <span class="badge bg-light text-dark me-3 rounded-circle" style="width:25px;height:25px;display:flex;align-items:center;justify-content:center;">{{ $index+1 }}</span>
                                        <span class="fw-medium text-truncate" style="max-width: 180px;">
                                            {{ $item->souvenir->nama_souvenir ?? 'Item Terhapus' }}
                                        </span>
                                    </div>
                                    <span class="badge bg-success-subtle text-success rounded-pill">{{ $item->total_pilih }}x</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card h-100 border-0 shadow-sm rounded-4">
                    <div class="card-header bg-white border-bottom py-3">
                        <h6 class="mb-0 fw-bold"><i class="bx bx-building text-info me-2"></i>Top 5 Souvenir Pembelian Office</h6>
                    </div>
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush">
                            @foreach($topOffice as $index => $item)
                                <li class="list-group-item d-flex justify-content-between align-items-center py-3 px-4 border-bottom-0">
                                    <div class="d-flex align-items-center">
                                        <span class="badge bg-light text-dark me-3 rounded-circle" style="width:25px;height:25px;display:flex;align-items:center;justify-content:center;">{{ $index+1 }}</span>
                                        <span class="fw-medium text-truncate" style="max-width: 180px;">
                                            {{ $item->souvenir->nama_souvenir ?? 'Item Terhapus' }}
                                        </span>
                                    </div>
                                    <span class="badge bg-info-subtle text-info rounded-pill">{{ $item->total_beli }} pcs</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card h-100 border-0 shadow-sm rounded-4">
                    <div class="card-header bg-white border-bottom py-3">
                        <h6 class="mb-0 fw-bold"><i class="bx bx-plus-circle text-warning me-2"></i>Top 5 Penambahan Souvenir</h6>
                    </div>
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush">
                            @foreach($topPenambahan as $index => $item)
                                <li class="list-group-item d-flex justify-content-between align-items-center py-3 px-4 border-bottom-0">
                                    <div class="d-flex align-items-center">
                                        <span class="badge bg-light text-dark me-3 rounded-circle" style="width:25px;height:25px;display:flex;align-items:center;justify-content:center;">{{ $index+1 }}</span>
                                        <span class="fw-medium text-truncate" style="max-width: 180px;">
                                            {{ $item->souvenir->nama_souvenir ?? 'Item Terhapus' }}
                                        </span>
                                    </div>
                                    <span class="badge bg-warning-subtle text-warning rounded-pill">{{ $item->total_keluar }} pcs</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <div class="row g-4 mb-4 mt-5">
            <div class="col-12">
                <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                    <div class="card-header bg-white border-bottom-0 py-3 px-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 fw-semibold text-dark">
                                <i class="bx bx-transfer-alt text-primary me-2"></i>
                                Analisa Flow: Pembelian vs Pemakaian
                            </h5>
                            <span class="badge bg-light text-muted border">Semua Periode</span>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th class="ps-4">Nama Souvenir</th>
                                        <th class="text-center text-primary">Total Dibeli (Office)</th>
                                        <th class="text-center text-warning">Total Dipilih (Peserta)</th>
                                        <th class="text-center pe-4">Status Selisih</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($analisaSelisih as $item)
                                        <tr>
                                            <td class="ps-4 fw-medium">{{ $item->nama_souvenir }}</td>

                                            {{-- Kolom Masuk --}}
                                            <td class="text-center">
                                                <span class="badge bg-primary-subtle text-primary px-3 rounded-pill">
                                                    + {{ number_format($item->total_masuk) }}
                                                </span>
                                            </td>

                                            {{-- Kolom Keluar --}}
                                            <td class="text-center">
                                                <span class="badge bg-warning-subtle text-warning px-3 rounded-pill">
                                                    - {{ number_format($item->total_keluar) }}
                                                </span>
                                            </td>

                                            {{-- Kolom Selisih --}}
                                            <td class="text-center pe-4">
                                                @if($item->selisih_flow < 0)
                                                    {{-- Case: Defisit (Lebih banyak keluar dibanding beli) --}}
                                                    <div class="d-flex align-items-center justify-content-center text-danger">
                                                        <i class="bx bx-trending-down me-1"></i>
                                                        <span class="fw-bold">{{ number_format($item->selisih_flow) }}</span>
                                                    </div>
                                                    <small class="text-muted" style="font-size: 0.7rem;">Stok Lama Tergerus</small>
                                                @elseif($item->selisih_flow > 0)
                                                    {{-- Case: Surplus (Lebih banyak beli dibanding pakai) --}}
                                                    <div class="d-flex align-items-center justify-content-center text-success">
                                                        <i class="bx bx-trending-up me-1"></i>
                                                        <span class="fw-bold">+{{ number_format($item->selisih_flow) }}</span>
                                                    </div>
                                                    <small class="text-muted" style="font-size: 0.7rem;">Akumulasi Stok</small>
                                                @else
                                                    {{-- Case: Balance --}}
                                                    <span class="badge bg-secondary">Balance (0)</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer bg-light p-3">
                        <small class="text-muted">
                            <i class="bx bx-info-circle me-1"></i>
                            <strong>Minus (-)</strong> berarti jumlah yang diambil peserta melebihi jumlah yang dibeli Office (menggunakan stok lama).
                            <strong>Plus (+)</strong> berarti stok bertambah.
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Style CSS --}}
    <style>
        .hover-card {
            transition: all 0.3s ease;
        }
        .hover-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
        }
        .avatar {
            display: flex; align-items: center; justify-content: center;
        }
        .avatar-lg { width: 64px; height: 64px; }
        .avatar-sm { width: 32px; height: 32px; }

        .scrollbar-custom::-webkit-scrollbar { width: 6px; }
        .scrollbar-custom::-webkit-scrollbar-track { background: #f1f1f1; }
        .scrollbar-custom::-webkit-scrollbar-thumb { background: #ccc; border-radius: 4px; }
        .scrollbar-custom::-webkit-scrollbar-thumb:hover { background: #aaa; }
    </style>

    {{-- Script Chart.js --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // ================= CHART PENUKARAN =================
            const ctx = document.getElementById('penukaranChart');
            if(ctx) {
                // Siapkan data array 12 bulan (default 0)
                const monthlyData = new Array(12).fill(0);

                // Mapping data dari controller ke array bulan (index 0-11)
                @foreach($chartPenukaran as $data)
                    // $data->bulan 1 (Januari) -> index 0
                    monthlyData[{{ $data->bulan - 1 }}] = {{ $data->total }};
                @endforeach

                const gradient = ctx.getContext('2d').createLinearGradient(0, 0, 0, 300);
                gradient.addColorStop(0, 'rgba(91, 115, 232, 0.4)');
                gradient.addColorStop(1, 'rgba(91, 115, 232, 0.0)');

                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
                        datasets: [{
                            label: 'Jumlah Penukaran',
                            data: monthlyData,
                            borderColor: '#5b73e8',
                            backgroundColor: gradient,
                            borderWidth: 2,
                            fill: true,
                            tension: 0.4,
                            pointRadius: 4,
                            pointHoverRadius: 6
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                mode: 'index',
                                intersect: false,
                                backgroundColor: 'rgba(0,0,0,0.8)',
                                titleColor: '#fff',
                                bodyColor: '#fff'
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: { borderDash: [2, 4], color: '#f0f0f0' },
                                ticks: { stepSize: 1 }
                            },
                            x: {
                                grid: { display: false }
                            }
                        }
                    }
                });
            }
        });
    </script>
@endsection
