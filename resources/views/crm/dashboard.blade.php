@extends('layouts_crm.app')

@section('crm_contents')

    <!-- Pemuatan CSS secara asinkron (Letakkan di bagian <head> atau sebelum script) -->
    <link rel="preload" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"></noscript>

    <style>

        /* Cegah pemblokiran render teks (FOIT) */
        h1, h2, h3, h4, h5, h6, span, p, div {
            font-display: swap !important;
        }

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

    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row g-4 mb-4">
            <div class="col-xl-8 col-lg-7">
                <div class="card h-100 shadow-sm border-0 rounded-4 overflow-hidden">
                    <div class="card-header bg-primary py-3 px-4 d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0 text-white fw-bold">Target Aktivitas Sales</h5>
                        <!-- ID ditambahkan di sini, PHP Variabel dihapus -->
                        <span class="badge bg-white text-primary rounded-pill" id="badgeTanggalRange">Memuat...</span>
                    </div>
                    <div class="card-body p-4">

                        <!-- Action dan Method bawaan dihapus, diganti menjadi ID AJAX -->
                        <form id="formFilterAktivitas" class="row g-2 mb-4 align-items-end pb-3 border-bottom">
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-muted mb-1">Tanggal Mulai</label>
                                <input type="date" id="inputStartDate" class="form-control form-control-sm border-light-subtle" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-muted mb-1">Tanggal Selesai</label>
                                <input type="date" id="inputEndDate" class="form-control form-control-sm border-light-subtle" required>
                            </div>
                            <div class="col-md-4">
                                <button type="submit" id="btnFilterAktivitas" class="btn btn-sm btn-primary w-100 shadow-sm">
                                    Terapkan Filter
                                </button>
                            </div>
                        </form>

                        <div class="mb-4 overflow-auto">
                            <!-- ID ditambahkan pada wadah tombol filter dinamis -->
                            <div class="btn-group btn-group-sm mb-1" role="group" id="filterButtonsContainer">
                                <button type="button" class="btn btn-outline-primary filter-btn active" data-filter="all">Semua Sales</button>
                            </div>
                        </div>

                        <!-- ID ditambahkan pada wadah kotak aktivitas dinamis -->
                        <div class="activity-container pe-2" id="activityCardsContainer" style="max-height: 400px; overflow-y: auto;">
                            <div class="text-center py-5 text-muted">Memuat data aktivitas...</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-4 col-lg-5">
                <div class="row g-4 h-100">
                    <div class="col-12">
                        <div class="card shadow-sm border-0 h-100">
                            <div
                                class="card-header d-flex justify-content-between align-items-center bg-transparent border-0">
                                <h5 class="card-title mb-0 text-primary">Data Perusahaan</h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-container" style="height: 250px;">
                                    <canvas id="kategoriChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="card shadow-sm border-0 h-100">
                            <div
                                class="card-header d-flex justify-content-between align-items-center bg-transparent border-0">
                                <h5 class="card-title mb-0 text-primary">Pembelian per Segmen</h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-container" style="height: 250px;">
                                    <canvas id="spendChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-md-6">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-transparent border-0">
                        <h5 class="card-title mb-0 text-primary">Top Vendor</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container" style="height: 280px;">
                            <canvas id="vendorChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-transparent border-0">
                        <h5 class="card-title mb-0 text-primary">Top Tipe Materi</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container" style="height: 280px;">
                            <canvas id="materiChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-md-6">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0 text-success fw-bold">Total Win</h5>
                        {{-- <select class="form-select form-select-sm win-year-filter border-0 bg-light" style="width: auto;" id="filterTahunLaporan">
                            @for ($year = now()->year - 5; $year <= now()->year + 1; $year++)
                                <option value="{{ $year }}" {{ request('tahun', now()->year) == $year ? 'selected' : '' }}>
                                    {{ $year }}</option>
                            @endfor
                        </select> --}}
                    </div>
                    <div class="card-body">
                        <div class="chart-container" style="height: 280px;">
                            <canvas id="totalWinChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0 text-danger fw-bold">Total Lost</h5>
                        <select class="form-select form-select-sm lost-year-filter border-0 bg-light"
                            style="width: auto;">
                            @for ($year = now()->year - 5; $year <= now()->year + 1; $year++)
                                <option value="{{ $year }}" {{ request('tahun', now()->year) == $year ? 'selected' : '' }}>
                                    {{ $year }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="card-body">
                        <div class="chart-container" style="height: 280px;">
                            <canvas id="totalLostChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-lg-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header border-bottom bg-transparent py-3">
                        <h5 class="card-title mb-0 text-primary fw-bold">Top 5 Produk</h5>
                    </div>
                    <div class="card-body p-0">
                        <ul class="nav nav-tabs nav-fill border-0 bg-light" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active small py-2" data-bs-toggle="tab" href="#tab-terjual">Terjual
                                    (Pax)</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link small py-2" data-bs-toggle="tab" href="#tab-profit">Profit
                                    (Revenue)</a>
                            </li>
                        </ul>
                        <div class="tab-content p-3">
                            <div id="tab-terjual" class="tab-pane fade show active">
                                @forelse ($best as $item)
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <div class="text-truncate" style="max-width: 70%;">
                                            <small
                                                class="text-dark fw-medium d-block">{{ $item->materi->nama_materi ?? $item->materi_key }}</small>
                                        </div>
                                        <span
                                            class="badge bg-success-subtle text-success border border-success-subtle">{{ number_format($item->total_pax, 0, ',', '.') }}
                                            Pax</span>
                                    </div>
                                @empty
                                    <p class="text-center text-muted my-4 small">Tidak ada data.</p>
                                @endforelse
                            </div>
                            <div id="tab-profit" class="tab-pane fade">
                                @forelse ($profit as $item)
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <div class="text-truncate" style="max-width: 60%;">
                                            <small
                                                class="text-dark fw-medium d-block">{{ $item->materi->nama_materi ?? $item->materi_key }}</small>
                                        </div>
                                        <span class="text-primary fw-bold small">Rp
                                            {{ number_format($item->total_revenue, 0, ',', '.') }}</span>
                                    </div>
                                @empty
                                    <p class="text-center text-muted my-4 small">Tidak ada data.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-transparent border-bottom py-3">
                        <h5 class="card-title mb-0 text-primary fw-bold">Prospek Terbuat Minggu Ini</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive" style="max-height: 350px;">
                            <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;">
                                <thead class="bg-light text-muted">
                                    <tr>
                                        <th class="ps-4">Sales & Materi</th>
                                        <th>Harga</th>
                                        <th>Periode</th>
                                        <th>Pax</th>
                                        <th class="pe-4">Tahap</th>
                                    </tr>
                                </thead>
                                <tbody id="bodyProspek">
                                    <tr><td colspan="5" class="text-center py-4 text-muted">Memuat data asinkron...</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-12">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-transparent py-3 border-bottom d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0 text-primary fw-bold">Incomplete Payments Advance</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover table-striped align-middle mb-0" style="font-size: 0.85rem;">
                                <thead class="bg-primary text-white">
                                    <tr>
                                        <th>No</th>
                                        <th>Perusahaan</th>
                                        <th>Materi</th>
                                        <th>Waktu</th>
                                        <th>Total PA</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody id="bodyIncompletePA">
                                    <tr><td colspan="7" class="text-center py-4 text-muted">Memuat data asinkron...</td></tr>
                                </tbody>
                            </table>
                            <div class="p-3 d-flex justify-content-between" id="paginationPA"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-12">
                <div class="card shadow-sm border-0">
                    <div
                        class="card-header bg-transparent py-3 border-bottom d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0 text-primary fw-bold">Total Status Perusahaan per Sales</h5>
                        <div class="badge bg-label-secondary text-muted">Pivot Table View</div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover table-striped align-middle mb-0" style="font-size: 0.85rem;" id="tablePivotStatus">
                                <thead class="bg-primary text-white" id="headPivotStatus">
                                    <tr>
                                        <th class="ps-4 border-0">Sales Executive</th>
                                        <th class="text-center border-0">Memuat Status...</th>
                                    </tr>
                                </thead>
                                <tbody id="bodyPivotStatus">
                                    <tr><td colspan="2" class="text-center py-4">Memuat data asinkron...</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-12">
                <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
                    <div class="card-header bg-white py-3 px-4 border-0">
                        <h5 class="card-title mb-0 text-primary fw-bold">Distribusi Perusahaan per Lokasi</h5>
                    </div>
                    <div class="card-body p-0">
                        <div id="map" style="height: 450px; background-color: #f8f9fa;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="detailAktivitas" class="w3-modal" tabindex="-1" aria-hidden="true">
        <div class="w3-modal-content w3-animate-top shadow-lg"
            style="max-width: 800px; border-radius: 12px; overflow: hidden;">
            <div class="card border-0">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Detail Aktivitas Sales</h5>
                    <button type="button" class="btn-close btn-close-white"
                        onclick="document.getElementById('detailAktivitas').style.display='none'"></button>
                </div>
                <div class="card-body p-4">
                    <div class="row g-4 mb-4">
                        <div class="col-md-6 border-end">
                            <div class="mb-2"><small class="text-muted d-block">Sales Executive</small><strong
                                    id="modalSalesId" class="fs-5"></strong></div>
                            <div><small class="text-muted d-block">Aktivitas</small><strong id="modalActivity"
                                    class="text-primary fs-5"></strong></div>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <div class="mb-2"><small class="text-muted d-block">Progress Capaian</small><span
                                    id="modalPersen" class="badge bg-info p-2 fs-6"></span></div>
                            <div><small class="text-muted d-block">Realisasi / Target</small><strong><span
                                        id="modalJumlah"></span> / <span id="modalTarget"></span></strong></div>
                            <div id="modalTotalContainer" style="display: none; margin-top: 8px;">
                                <small class="text-muted d-block">Total Nilai</small>
                                <strong id="modalTotalValue" class="text-success fs-6"></strong>
                            </div>
                        </div>
                    </div>

                    <div class="progress mb-4" style="height: 12px; border-radius: 10px;">
                        <div id="modalProgressBar" class="progress-bar progress-bar-striped progress-bar-animated"
                            style="width: 0%;"></div>
                    </div>

                    <div class="table-responsive rounded-3 border">
                        <table class="table table-hover align-middle mb-0 shadow-none">
                            <thead class="bg-light">
                                <tr>
                                    <th class="small border-0">Client</th>
                                    <th class="small border-0">Tipe</th>
                                    <th class="small border-0">Deskripsi</th>
                                    <th class="small border-0">Foto</th>
                                    <th class="small border-0">Lokasi</th>
                                    <th class="small border-0 text-center">Waktu</th>
                                </tr>
                            </thead>
                            <tbody class="small">
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer border-0 text-end py-3">
                    <button type="button" class="btn btn-secondary px-4"
                        onclick="document.getElementById('detailAktivitas').style.display='none'">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="chartRKM" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Detail Data</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" id="tableChartRkm">
                            <thead>
                                <tr>
                                    <th>Nama Materi</th>
                                    <th>Perusahaan</th>
                                    <th>Sales</th>
                                    <th>Harga Jual</th>
                                    <th>Tanggal</th>
                                </tr>
                            </thead>
                            <tbody id="bodyChartRkm">
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="chartPerusahaan" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitles">Detail Data</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" id="tableChartPerusahaan">
                            <thead>
                                <tr>
                                    <th>Perusahaan</th>
                                    <th>Sales</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody id="bodyChartPerusahaan">
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="chartLaporanPenjualan" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitlesLaporan">Detail Data</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" id="tableChartLaporanPenjualan">
                            <thead>
                                <tr>
                                    <th>Perusahaan</th>
                                    <th>Materi</th>
                                    <th>Netsales</th>
                                    <th>Pax</th>
                                    <th>Total</th>
                                    <th>Waktu</th>
                                </tr>
                            </thead>
                            <tbody id="bodyChartLaporanPenjualan">
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pemuatan Skrip tanpa memblokir DOM Parsing -->
    <script src="{{ asset('assets/vendor/libs/leaflet/leaflet.js') }}" defer></script>
    <script src="{{ asset('assets/vendor/libs/chartjs/chart.umd.min.js') }}" defer></script>

    <script>
        document.querySelectorAll('.show-detail').forEach(btn => {
            btn.addEventListener('click', function () {
                document.getElementById('detailMateri').innerText = this.dataset.materi;
                document.getElementById('detailPerusahaan').innerText = this.dataset.perusahaan;
                document.getElementById('detailInstruktur').innerText = this.dataset.instruktur;
                document.getElementById('detailSales').innerText = this.dataset.sales;
                document.getElementById('detailTanggal').innerText = this.dataset.tanggaltraining;

                let modal = new bootstrap.Modal(document.getElementById('modalDetail'));
                modal.show();
            });
        });

        document.querySelectorAll('.checklist-checkbox').forEach(cb => {
            cb.addEventListener('change', function() {
                fetch("{{ route('checklist.update') }}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": "{{ csrf_token() }}"
                    },
                    body: JSON.stringify({
                        rkm_id: this.dataset.rkm,
                        field: this.dataset.field,
                        value: this.checked ? 1 : 0
                    })
                });
            });
        });

        document.addEventListener('DOMContentLoaded', () => {
            // Fungsi inisialisasi chart universal
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
                                    font: { size: 12 },
                                    padding: 10,
                                    boxWidth: 12
                                }
                            }
                        }
                    }
                });
            };

            function formatRupiah(angka) {
                return new Intl.NumberFormat('id-ID').format(angka);
            }

            function openModalRKM(label, type) {
                const modalTitle = document.getElementById('modalTitle');
                const tableBody = document.getElementById('bodyChartRkm');

                modalTitle.innerText = `Detail: ${label}`;
                tableBody.innerHTML = '<tr><td colspan="5" class="text-center">Ditunggu ya bro</td></tr>';

                const detailModal = new bootstrap.Modal(document.getElementById('chartRKM'));
                detailModal.show();

                fetch(`/crm/chartRKM?type=${type}&key=${encodeURIComponent(label)}`)
                    .then(response => response.json())
                    .then(data => {
                        tableBody.innerHTML = '';
                        if (data.length === 0) {
                            tableBody.innerHTML = '<tr><td colspan="4" class="text-center">Tidak ada data ditemukan.</td></tr>';
                            return;
                        }
                        data.forEach(item => {
                            const row = `
                                <tr>
                                    <td>${item.nama_materi}</td>
                                    <td>${item.nama_perusahaan}</td>
                                    <td>${item.sales_key}</td>
                                    <td>${new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(item.harga_jual)}</td>
                                    <td>${new Date(item.created_at).toLocaleDateString('id-ID')}</td>
                                </tr>
                            `;
                            tableBody.innerHTML += row;
                        });
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        tableBody.innerHTML = '<tr><td colspan="4" class="text-center text-danger">Gagal memuat data.</td></tr>';
                    });
            }

            function openModalPerusahaan(label) {
                const tableBody = document.getElementById('bodyChartPerusahaan');
                const modalTitle = document.getElementById('modalTitles');

                modalTitle.innerText = `Daftar Perusahaan: ${label}`;
                tableBody.innerHTML = '<tr><td colspan="3" class="text-center">Sabar bro...</td></tr>';

                const myModal = new bootstrap.Modal(document.getElementById('chartPerusahaan'));
                myModal.show();

                fetch(`/crm/chartPerusahaan?key=${encodeURIComponent(label)}`)
                    .then(response => response.json())
                    .then(data => {
                        tableBody.innerHTML = '';
                        if (data.length === 0) {
                            tableBody.innerHTML = '<tr><td colspan="3" class="text-center">Tidak ada data.</td></tr>';
                            return;
                        }
                        data.forEach(item => {
                            tableBody.innerHTML += `
                                <tr>
                                    <td>${item.nama_perusahaan}</td>
                                    <td>${item.sales_key ?? '-'}</td>
                                    <td>${item.status}</td>
                                </tr>
                            `;
                        });
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        tableBody.innerHTML = '<tr><td colspan="3" class="text-center text-danger">Gagal mengambil data.</td></tr>';
                    });
            }

            function openModalChartLaporan(id_sales, triwulan, tahun, status) {
                const tableBody = document.getElementById('bodyChartLaporanPenjualan');
                const modalTitle = document.getElementById('modalTitlesLaporan');

                modalTitle.innerText = `Detail ${status.toUpperCase()} ${id_sales.toUpperCase()}- ${triwulan} (${tahun})`;
                tableBody.innerHTML = `<tr><td colspan="6" class="text-center">Sabar bro...</td></tr>`;

                const modal = new bootstrap.Modal(document.getElementById('chartLaporanPenjualan'));
                modal.show();

                const url = `/crm/chartClosed?id_sales=${encodeURIComponent(id_sales)}&triwulan=${encodeURIComponent(triwulan)}&tahun=${encodeURIComponent(tahun)}&status=${encodeURIComponent(status)}`;

                fetch(url)
                    .then(response => {
                        if (!response.ok) throw new Error('Network response was not ok');
                        return response.json();
                    })
                    .then(data => {
                        tableBody.innerHTML = '';
                        if (data.length === 0) {
                            tableBody.innerHTML = `<tr><td colspan="6" class="text-center">Tidak ada data</td></tr>`;
                            return;
                        }
                        data.forEach(item => {
                            const namaPerusahaan = item.perusahaan?.nama_perusahaan ?? '-';
                            const namaMateri = item.materi_relation?.nama_materi ?? '-';
                            const netsales = formatRupiah(item.netsales ?? 0);
                            const pax = item.pax ?? 0;
                            const total = formatRupiah(item.total ?? 0);
                            const tanggal = item.merah ?? item.lost ?? '-';

                            tableBody.innerHTML += `
                                <tr>
                                    <td>${namaPerusahaan}</td>
                                    <td>${namaMateri}</td>
                                    <td>${netsales}</td>
                                    <td>${pax}</td>
                                    <td>${total}</td>
                                    <td>${tanggal}</td>
                                </tr>
                            `;
                        });
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        tableBody.innerHTML = `<tr><td colspan="6" class="text-center text-danger">Gagal mengambil data.</td></tr>`;
                    });
            }

            // Eksekusi Grafik Bertahap ke dalam Macro-Task Queue
            setTimeout(() => {
                // 1. Kategori Chart
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
                        onClick: (event, elements, chart) => {
                            if (elements.length > 0) {
                                const index = elements[0].index;
                                const label = chart.data.labels[index];
                                openModalPerusahaan(label);
                            }
                        },
                        plugins: { tooltip: { callbacks: { label: context => `${context.label}: ${context.raw}%` } } }
                    }
                });

                // 2. Vendor Chart
                const vendorData = @json($topVendors);
                initChart('vendorChart', {
                    type: 'doughnut',
                    data: {
                        labels: vendorData.map(item => item.vendor),
                        datasets: [{
                            label: 'Data Vendor',
                            data: vendorData.map(item => item.total),
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
                        onClick: (event, elements, chart) => {
                            if (elements.length > 0) {
                                const index = elements[0].index;
                                const label = chart.data.labels[index];
                                openModalRKM(label, 'vendor');
                            }
                        },
                        plugins: { tooltip: { callbacks: { label: context => `${context.label}: ${context.raw}` } } }
                    }
                });

                // 3. Materi Chart
                const materiData = @json($topKategoriMateri);
                initChart('materiChart', {
                    type: 'bar',
                    data: {
                        labels: materiData.map(item => item.kategori_materi),
                        datasets: [{
                            label: 'Top Tipe Materi',
                            data: materiData.map(item => item.total),
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
                        onClick: (event, elements, chart) => {
                            if (elements.length > 0) {
                                const index = elements[0].index;
                                const label = chart.data.labels[index];
                                openModalRKM(label, 'materi');
                            }
                        },
                        plugins: { tooltip: { callbacks: { label: context => `${context.label}: ${context.raw}` } } }
                    }
                });

                // 4. Spend Chart
                const spendData = @json($topSpendSeg);
                initChart('spendChart', {
                    type: 'bar',
                    data: {
                        labels: spendData.map(item => item.kategori_perusahaan),
                        datasets: [{
                            label: 'Pembelian berdasarkan segmen',
                            data: spendData.map(item => item.spend),
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
                        onClick: (event, elements, chart) => {
                            if (elements.length > 0) {
                                const index = elements[0].index;
                                const label = chart.data.labels[index];
                                openModalRKM(label, 'spend');
                            }
                        },
                        plugins: {
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        const index = context.dataIndex;
                                        const spend = spendData[index].spend;
                                        const total = spendData[index].total;
                                        return `${context.label}: ${formatRupiah(spend)} || ${total}`;
                                    }
                                }
                            }
                        }
                    }
                });

                // 5 & 6. Penarikan Data Asinkron Total Win & Los
                let chartWinInstance = null;
                let chartLostInstance = null;

                // Fungsi mandiri untuk menarik data dan merender grafik Win/Lost
                window.loadWinLostCharts = function(tahun) {
                    fetch(`/crm/total-win-lost?tahun=${tahun}`)
                        .then(res => res.json())
                        .then(dataApi => {
                            const winLabels = ['TR1', 'TR2', 'TR3', 'TR4'];
                            const colors = ['rgba(75, 192, 192, 0.8)', 'rgba(255, 99, 132, 0.8)', 'rgba(54, 162, 235, 0.8)', 'rgba(255, 206, 86, 0.8)', 'rgba(153, 102, 255, 0.8)'];

                            // 1. Pemrosesan Data Total Win
                            const winDatasets = Object.keys(dataApi.win).map((id_sales, index) => {
                                const sales = dataApi.win[id_sales];
                                return {
                                    label: sales.username.toUpperCase(),
                                    data: [sales.TR1, sales.TR2, sales.TR3, sales.TR4],
                                    backgroundColor: colors[index % colors.length],
                                    borderWidth: 1
                                };
                            });

                            if (chartWinInstance) {
                                chartWinInstance.destroy(); // Hancurkan kanvas grafik lama
                            }

                            chartWinInstance = initChart('totalWinChart', {
                                type: 'bar',
                                data: { labels: winLabels, datasets: winDatasets },
                                options: {
                                    scales: { x: { stacked: false }, y: { stacked: false, ticks: { callback: function(value) { return new Intl.NumberFormat('id-ID').format(value); } } } },
                                    onClick: function(evt, elements) {
                                        if (elements.length > 0) {
                                            const element = elements[0];
                                            const id_sales = Object.keys(dataApi.win)[element.datasetIndex];
                                            const triwulan = winLabels[element.index];
                                            openModalChartLaporan(id_sales, triwulan, tahun, 'win');
                                        }
                                    },
                                    plugins: { tooltip: { callbacks: { label: context => `${context.dataset.label}: ${new Intl.NumberFormat('id-ID').format(context.raw)}` } } }
                                }
                            });

                            // 2. Pemrosesan Data Total Lost
                            const lostDatasets = Object.keys(dataApi.lost).map((id_sales, index) => {
                                const sales = dataApi.lost[id_sales];
                                return {
                                    label: sales.username.toUpperCase(),
                                    data: [sales.TR1, sales.TR2, sales.TR3, sales.TR4],
                                    backgroundColor: colors[index % colors.length],
                                    borderWidth: 1
                                };
                            });

                            if (chartLostInstance) {
                                chartLostInstance.destroy(); // Hancurkan kanvas grafik lama
                            }

                            chartLostInstance = initChart('totalLostChart', {
                                type: 'bar',
                                data: { labels: winLabels, datasets: lostDatasets },
                                options: {
                                    scales: { x: { stacked: false }, y: { stacked: false, ticks: { callback: function(value) { return new Intl.NumberFormat('id-ID').format(value); } } } },
                                    onClick: function(evt, elements) {
                                        if (elements.length > 0) {
                                            const element = elements[0];
                                            const id_sales = Object.keys(dataApi.lost)[element.datasetIndex];
                                            const triwulan = winLabels[element.index];
                                            openModalChartLaporan(id_sales, triwulan, tahun, 'lost');
                                        }
                                    },
                                    plugins: { tooltip: { callbacks: { label: context => `${context.dataset.label}: ${new Intl.NumberFormat('id-ID').format(context.raw)}` } } }
                                }
                            });
                        });
                };

                // Eksekusi fungsi saat halaman pertama kali dimuat
                const tahunAwal = document.getElementById('filterTahunLaporan') ? document.getElementById('filterTahunLaporan').value : new Date().getFullYear();
                window.loadWinLostCharts(tahunAwal);

                // --- 1. Load Data Prospek Asinkron ---

                // === FILTER TAHUN WIN & LOST ASINKRON ===
                const winYearFilter = document.querySelector('.win-year-filter');
                const lostYearFilter = document.querySelector('.lost-year-filter');

                function handleYearChange() {
                    const selectedYear = this.value;
                    
                    // Sinkronisasi nilai kedua dropdown secara visual
                    if (winYearFilter) winYearFilter.value = selectedYear;
                    if (lostYearFilter) lostYearFilter.value = selectedYear;
                    
                    // Panggil fungsi AJAX untuk menarik data dan melukis ulang grafik tanpa refresh
                    if (typeof window.loadWinLostCharts === 'function') {
                        window.loadWinLostCharts(selectedYear);
                    }
                }

                // Pasang event listener pada dropdown jika elemen ditemukan
                if (winYearFilter) winYearFilter.addEventListener('change', handleYearChange);
                if (lostYearFilter) lostYearFilter.addEventListener('change', handleYearChange);

                fetch('/crm/prospek-minggu-ini')
                    .then(res => res.json())
                    .then(data => {
                        const tbody = document.getElementById('bodyProspek');
                        tbody.innerHTML = '';
                        
                        if (data.length === 0) {
                            tbody.innerHTML = '<tr><td colspan="5" class="text-center py-4 text-muted">Belum ada prospek baru.</td></tr>';
                            return;
                        }

                        data.forEach(item => {
                            const formatRupiah = new Intl.NumberFormat('id-ID').format(item.harga || 0);
                            const namaMateri = item.materi_relation?.nama_materi || '-';
                            const pax = new Intl.NumberFormat('id-ID').format(item.pax || 0);
                            const tahap = (item.tahap || '').toUpperCase();
                            
                            let periode = '';
                            if (item.tentatif == 1) {
                                periode = '<span class="badge bg-warning-subtle text-warning">Tentatif</span>';
                            } else if (item.periode_mulai && item.periode_selesai) {
                                const opt = { day: '2-digit', month: 'short', year: 'numeric' };
                                const pMulai = new Date(item.periode_mulai).toLocaleDateString('id-ID', opt);
                                const pSelesai = new Date(item.periode_selesai).toLocaleDateString('id-ID', opt);
                                periode = `<small>${pMulai} - ${pSelesai}</small>`;
                            }

                            tbody.innerHTML += `
                                <tr>
                                    <td class="ps-4">
                                        <span class="fw-bold text-dark d-block">${item.id_sales}</span>
                                        <small class="text-muted">${namaMateri}</small>
                                    </td>
                                    <td><span class="fw-medium">Rp ${formatRupiah}</span></td>
                                    <td>${periode}</td>
                                    <td>${pax}</td>
                                    <td class="pe-4">
                                        <span class="badge bg-info-subtle text-info border border-info-subtle w-100">${tahap}</span>
                                    </td>
                                </tr>
                            `;
                        });
                    });

                // --- 2. Load Data Incomplete PA Asinkron ---
                function loadIncompletePA(url = '/crm/incomplete-pa') {
                    fetch(url)
                        .then(res => res.json())
                        .then(resData => {
                            const data = resData.data;
                            const tbody = document.getElementById('bodyIncompletePA');
                            const pagination = document.getElementById('paginationPA');
                            tbody.innerHTML = '';
                            pagination.innerHTML = '';

                            if (data.length === 0) {
                                tbody.innerHTML = '<tr><td colspan="7" class="text-center py-4 text-muted">Tidak ada data PA.</td></tr>';
                                return;
                            }

                            const opt = { day: '2-digit', month: 'short', year: 'numeric' };

                            data.forEach((item, index) => {
                                const namaPerusahaan = item.rkm?.perusahaan?.nama_perusahaan || '-';
                                const namaMateri = item.rkm?.materi?.nama_materi || '-';
                                const tracking = item.tracking_net_sales?.tracking || '-';
                                const peluangId = item.rkm?.peluang?.id || null;
                                
                                let waktu = '-';
                                if (item.rkm?.tanggal_awal && item.rkm?.tanggal_akhir) {
                                    const tAwal = new Date(item.rkm.tanggal_awal).toLocaleDateString('id-ID', opt);
                                    const tAkhir = new Date(item.rkm.tanggal_akhir).toLocaleDateString('id-ID', opt);
                                    waktu = `${tAwal} - ${tAkhir}`;
                                }

                                const totalPA = Number(item.transportasi || 0) + Number(item.akomodasi_peserta || 0) + 
                                              Number(item.akomodasi_tim || 0) + Number(item.fresh_money || 0) + 
                                              Number(item.entertaint || 0) + Number(item.souvenir || 0) + 
                                              Number(item.cashback || 0) + Number(item.sewa_laptop || 0);

                                const actionBtn = peluangId ? `<a class="btn btn-sm btn-outline-primary" href="/crm/peluang/${peluangId}" target="_blank">View</a>` : '-';
                                const rowIndex = resData.from + index;

                                tbody.innerHTML += `
                                    <tr>
                                        <td>${rowIndex}</td>
                                        <td>${namaPerusahaan}</td>
                                        <td>${namaMateri}</td>
                                        <td>${waktu}</td>
                                        <td>${new Intl.NumberFormat('id-ID').format(totalPA)}</td>
                                        <td>${tracking}</td>
                                        <td>${actionBtn}</td>
                                    </tr>
                                `;
                            });

                            if (resData.prev_page_url) {
                                pagination.innerHTML += `<button class="btn btn-sm btn-outline-secondary" onclick="loadIncompletePA('${resData.prev_page_url}')">&laquo; Prev</button>`;
                            } else {
                                pagination.innerHTML += `<div></div>`;
                            }

                            if (resData.next_page_url) {
                                pagination.innerHTML += `<button class="btn btn-sm btn-outline-secondary" onclick="loadIncompletePA('${resData.next_page_url}')">Next &raquo;</button>`;
                            }
                        });
                }
                loadIncompletePA();

                // --- 3. Load Pivot Status Asinkron ---
                fetch('/crm/pivot-status')
                    .then(res => res.json())
                    .then(response => {
                        const statuses = response.statuses;
                        const data = response.data;
                        const thead = document.getElementById('headPivotStatus');
                        const tbody = document.getElementById('bodyPivotStatus');
                        
                        let headHtml = '<tr><th class="ps-4 border-0">Sales Executive</th>';
                        statuses.forEach(status => {
                            headHtml += `<th class="text-center border-0">${status}</th>`;
                        });
                        headHtml += '</tr>';
                        thead.innerHTML = headHtml;

                        tbody.innerHTML = '';
                        const salesKeys = Object.keys(data);
                        
                        if (salesKeys.length === 0) {
                            tbody.innerHTML = `<tr><td colspan="${statuses.length + 1}" class="text-center py-4">Data tidak tersedia.</td></tr>`;
                            return;
                        }

                        salesKeys.forEach(salesKey => {
                            let rowHtml = `<tr><td class="ps-4 fw-bold">${salesKey}</td>`;
                            statuses.forEach(status => {
                                const total = data[salesKey][status] || 0;
                                if (total > 0) {
                                    rowHtml += `<td class="text-center fw-medium">${new Intl.NumberFormat('id-ID').format(total)}</td>`;
                                } else {
                                    rowHtml += `<td class="text-center fw-medium"><span class="text-light-emphasis">0</span></td>`;
                                }
                            });
                            rowHtml += '</tr>';
                            tbody.innerHTML += rowHtml;
                        });
                    });

            }, 150);

            // Inisialisasi variabel global untuk peta
            let map = null;
            let markerLayer = null;
            let mapInitialized = false;
            const mapContainer = document.getElementById('map');

            function updateMapMarkers(salesKey) {
                if (!mapInitialized || !map || !markerLayer) return;

                markerLayer.clearLayers();

                let filteredLocations = [];
                if (salesKey === 'all') {
                    filteredLocations = @json($map);
                } else {
                    filteredLocations = @json($map).filter(loc => loc.sales_key === salesKey);
                }

                filteredLocations = filteredLocations.filter(loc => loc.company_count > 0);
                var totalCompanies = filteredLocations.reduce((sum, loc) => sum + (loc.company_count || 0), 0);

                filteredLocations.forEach(function(loc) {
                    if (loc.latitude && loc.longitude && loc.company_count > 0) {
                        var percentage = totalCompanies > 0 ? ((loc.company_count / totalCompanies) * 100).toFixed(2) : 0;
                        var marker = L.marker([loc.latitude, loc.longitude]);

                        var popupContent = `
                            <b>Location:</b> ${loc.lokasi}<br>
                            <b>Companies:</b> ${loc.company_count}<br>
                            <b>Percentage:</b> ${percentage}% | ${loc.company_count}
                        `;
                        marker.bindPopup(popupContent);
                        marker.bindTooltip(`${loc.lokasi}: ${percentage}%`, { permanent: false });

                        markerLayer.addLayer(marker);
                    }
                });

                if (filteredLocations.length === 0 && mapContainer) {
                    mapContainer.innerHTML = '<div class="text-center text-muted p-3">Tidak ada data lokasi tersedia</div>';
                }
            }

            function initializeMap() {
                if (!mapContainer) return;

                map = L.map('map').setView([-2.548926, 118.0148634], 5);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                }).addTo(map);

                markerLayer = L.layerGroup().addTo(map);
                updateMapMarkers('all');
            }

            const mapObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting && !mapInitialized) {
                        mapInitialized = true;
                        initializeMap();
                        observer.unobserve(entry.target);
                    }
                });
            }, { rootMargin: "200px 0px" });

            if (mapContainer) {
                mapObserver.observe(mapContainer);
            }

            // Mengganti const konstan menjadi let agar bisa di-update oleh fungsi AJAX
            let activityData = [];

            function loadTargetAktivitas(startDate = '', endDate = '') {
                const container = document.getElementById('activityCardsContainer');
                const badgeTanggal = document.getElementById('badgeTanggalRange');
                const filterBtns = document.getElementById('filterButtonsContainer');
                const btnFilter = document.getElementById('btnFilterAktivitas');

                btnFilter.disabled = true;
                btnFilter.innerHTML = 'Memproses...';

                let url = `/crm/target-filter-aktivitas`;
                if (startDate && endDate) {
                    url += `?start_date=${startDate}&end_date=${endDate}`;
                }

                fetch(url)
                    .then(res => res.json())
                    .then(resData => {
                        activityData = resData.activitysales;
                        badgeTanggal.innerText = resData.tanggalRange;

                        // 1. Render Tombol Filter
                        let btnsHtml = `<button type="button" class="btn btn-outline-primary filter-btn active" data-filter="all">Semua Sales</button>`;
                        activityData.forEach(sales => {
                            btnsHtml += `<button type="button" class="btn btn-outline-primary filter-btn" data-filter="${sales.id_sales}">${sales.id_sales}</button>`;
                        });
                        filterBtns.innerHTML = btnsHtml;

                        // 2. Render Kartu Aktivitas Sales
                        let cardsHtml = '';
                        if (activityData.length === 0) {
                            cardsHtml = `<div class="text-center text-muted py-4">Tidak ada data sales.</div>`;
                        } else {
                            activityData.forEach(sales => {
                                cardsHtml += `
                                    <div class="sales-block mb-4 p-3 rounded-3 sales-item" data-sales-id="${sales.id_sales}">
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="avatar me-2"><span class="avatar-initial rounded-circle bg-label-primary p-2"><i class="bx bx-user"></i></span></div>
                                            <strong class="text-dark fs-6">${sales.id_sales}</strong>
                                        </div>
                                        <div class="row g-3">
                                `;

                                const mapAktivitas = [
                                    { label: 'DB', key: 'DB', tgt: 'target_DB', color: 'info' },
                                    { label: 'Contact', key: 'contact', tgt: 'target_contact', color: 'info' },
                                    { label: 'Call', key: 'call', tgt: 'target_call', color: 'info' },
                                    { label: 'Email', key: 'email', tgt: 'target_email', color: 'warning' },
                                    { label: 'Visit', key: 'visit', tgt: 'target_visit', color: 'warning' },
                                    { label: 'Meet', key: 'meet', tgt: 'target_meet', color: 'warning' },
                                    { label: 'Incharge', key: 'incharge', tgt: 'target_incharge', color: 'success' },
                                    { label: 'Penawaran Awal', key: 'PA', tgt: 'target_PA', color: 'success' },
                                    { label: 'Leads', key: 'Leads', tgt: 'target_PI', color: 'success' },
                                    { label: 'Regis Form', key: 'Regis_Form', tgt: 'target_Form_Masuk', color: 'danger' }
                                ];

                                mapAktivitas.forEach(act => {
                                    const jumlah = sales[act.key] || 0;
                                    const target = sales[act.tgt] || 0;
                                    const persen = target > 0 ? Math.min(Math.round((jumlah / target) * 100), 100) : 0;

                                    cardsHtml += `
                                        <div class="col-md-6 col-lg-4 activity-item" data-activity="${act.label}">
                                            <div class="p-2 border rounded-2 bg-white h-100">
                                                <div class="d-flex justify-content-between align-items-center mb-1">
                                                    <small class="text-muted fw-bold" style="font-size: 0.75rem;">${act.label}</small>
                                                    <span class="badge bg-${act.color}-subtle text-${act.color} rounded-pill" style="font-size: 0.65rem; cursor: pointer;" data-sales-id="${sales.id_sales}" data-activity="${act.label}">${persen}%</span>
                                                </div>
                                                <div class="d-flex align-items-baseline"><h6 class="mb-1 me-1">${jumlah}</h6><small class="text-muted">/${target}</small></div>
                                                <div class="progress rounded-pill" style="height: 4px;"><div class="progress-bar bg-${act.color} rounded-pill" style="width: ${persen}%"></div></div>
                                            </div>
                                        </div>
                                    `;
                                });
                                cardsHtml += `</div></div>`;
                            });
                        }
                        container.innerHTML = cardsHtml;

                        // 3. Pasang ulang fungsi event listener pada elemen DOM yang baru dilukis
                        bindActivityDOMEvents();

                        btnFilter.disabled = false;
                        btnFilter.innerHTML = 'Terapkan Filter';
                    })
                    .catch(err => {
                        console.error(err);
                        container.innerHTML = `<div class="text-center text-danger py-4">Gagal terhubung ke peladen.</div>`;
                        btnFilter.disabled = false;
                        btnFilter.innerHTML = 'Terapkan Filter';
                    });
            }

            // Fungsi membidik event DOM setelah proses HTML injeksi
            function bindActivityDOMEvents() {
                // Event popup modal detail aktivitas (Klik presentase)
                document.querySelectorAll('.badge[data-sales-id][data-activity]').forEach(badge => {
                    badge.addEventListener('click', () => {
                        const salesId = badge.getAttribute('data-sales-id');
                        const activityLabel = badge.getAttribute('data-activity');
                        showActivityDetails(salesId, activityLabel);
                    });
                });

                // Event tombol filter nama sales
                document.querySelectorAll('.filter-btn').forEach(button => {
                    button.addEventListener('click', () => {
                        document.querySelectorAll('.filter-btn').forEach(btn => btn.classList.remove('active'));
                        button.classList.add('active');
                        
                        const selectedSalesId = button.getAttribute('data-filter');
                        document.querySelectorAll('.sales-item').forEach(item => {
                            item.style.display = (selectedSalesId === 'all' || item.getAttribute('data-sales-id') === selectedSalesId) ? 'block' : 'none';
                        });
                    });
                });
            }

            // Logika Submit Form Tanpa Refresh secara Aman
            const formFilter = document.getElementById('formFilterAktivitas');
            if (formFilter) {
                formFilter.addEventListener('submit', function(e) {
                    e.preventDefault(); // Menghentikan perilaku default form agar tidak refresh halaman
                    
                    const start = document.getElementById('inputStartDate').value;
                    const end = document.getElementById('inputEndDate').value;
                    
                    // Panggil fungsi AJAX pemuatan data target aktivitas
                    loadTargetAktivitas(start, end);
                    
                    // Perbarui URL history peramban secara senyap (tanpa reload)
                    const urlParams = new URLSearchParams(window.location.search);
                    if (start && end) {
                        urlParams.set('start_date', start);
                        urlParams.set('end_date', end);
                    } else {
                        urlParams.delete('start_date');
                        urlParams.delete('end_date');
                    }
                    window.history.replaceState({}, '', `${window.location.pathname}?${urlParams.toString()}`);
                });
            }

            // 1. Baca parameter URL jika pengguna memuat ulang halaman dengan filter yang sudah aktif
            const urlParamsActivity = new URLSearchParams(window.location.search);
            const initStart = urlParamsActivity.get('start_date') || '';
            const initEnd = urlParamsActivity.get('end_date') || '';
            
            // 2. Isi nilai input form tanggal dengan data dari URL (jika ada)
            if(initStart) document.getElementById('inputStartDate').value = initStart;
            if(initEnd) document.getElementById('inputEndDate').value = initEnd;

            // 3. Tarik data pertama kali secara latar belakang saat halaman dimuat
            loadTargetAktivitas(initStart, initEnd);

        });
    </script>

@endsection
