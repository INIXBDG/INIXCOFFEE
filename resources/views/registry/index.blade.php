@extends('layouts.app')

@section('content')
<style>
    #registryMainTabs .nav-link {
        border-radius: 8px;
        padding: 8px 16px;
        font-size: 0.9rem;
        transition: all 0.2s ease-in-out;
        color: #495057;
        background-color: #f1f3f5;
    }
    #registryMainTabs .nav-link.active {
        background-color: #0d6efd;
        color: #ffffff;
        box-shadow: 0 2px 6px rgba(13, 110, 253, 0.35);
    }
    #registryMainTabs .nav-link:hover:not(.active) {
        background-color: #e9ecef;
        color: #212529;
    }
    #pills-dashboard-pane .card {
        display: block !important;
        width: 100% !important;
    }
</style>
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12">

            <div class="d-flex justify-content-between align-items-center mb-3 mx-4 flex-wrap gap-2">
                <div class="d-flex align-items-center">
                    <h4 class="fw-bold text-dark mb-0 d-none" id="registryHeaderTitle"><i class="fas fa-layer-group text-primary me-2"></i>Data Registry Feature</h4>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <ul class="nav nav-pills" id="registryMainTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active fw-bold" id="pills-data-table-tab" data-bs-toggle="pill" data-bs-target="#pills-data-table" type="button" role="tab" aria-controls="pills-data-table" aria-selected="true">
                                <i class="fas fa-table me-1"></i> Data Registry Feature
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link fw-bold" id="pills-dashboard-tab" data-bs-toggle="pill" data-bs-target="#pills-dashboard-pane" type="button" role="tab" aria-controls="pills-dashboard-pane" aria-selected="false">
                                <i class="fas fa-chart-pie me-1"></i> Dashboard
                            </button>
                        </li>
                    </ul>

                    <a href="{{ route('registry.create') }}" class="btn btn-primary fw-bold" data-toggle="tooltip" title="Tambah Tugas Baru">
                        <i class="fas fa-plus me-1"></i> Tambah Tugas Baru
                    </a>
                </div>
            </div>

            <div class="modal fade" id="startTaskModal" tabindex="-1" aria-labelledby="startTaskModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <form id="startTaskForm" method="POST" action="">
                        @csrf
                        @method('PATCH')
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="startTaskModalLabel">Mulai Tugas</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label for="input_tanggal_mulai" class="form-label">Tanggal & Waktu Mulai <span class="text-danger">*</span></label>
                                    <input type="datetime-local" class="form-control" name="tanggal_mulai" id="input_tanggal_mulai" required>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-primary">Simpan</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="modal fade" id="finishTaskModal" tabindex="-1" aria-labelledby="finishTaskModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <form id="finishTaskForm" method="POST" action="">
                        @csrf
                        @method('PATCH')
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="finishTaskModalLabel">Tandai Selesai</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label for="input_tanggal_akhir" class="form-label">Tanggal & Waktu Selesai <span class="text-danger">*</span></label>
                                    <input type="datetime-local" class="form-control" name="tanggal_akhir" id="input_tanggal_akhir" required>
                                </div>
                                <div class="mb-3">
                                    <label for="input_kesulitan" class="form-label">Tingkat Kesulitan (Untuk Tiket)</label>
                                    <select name="kesulitan" id="input_kesulitan" class="form-select">
                                        <option value="" selected disabled>Pilih Tingkat Kesulitan</option>
                                        <option value="Major">Major</option>
                                        <option value="Moderate">Moderate</option>
                                        <option value="Minor">Minor</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="input_keterangan" class="form-label">Keterangan Selesai (Untuk Tiket)</label>
                                    <textarea class="form-control" id="input_keterangan" name="keterangan" rows="2" placeholder="Masukkan keterangan selesai..."></textarea>
                                </div>
                                <div class="mb-3">
                                    <label for="input_penanganan" class="form-label">Update Penanganan (Untuk Tiket)</label>
                                    <textarea class="form-control" id="input_penanganan" name="penanganan" rows="2" placeholder="Masukkan update penanganan..."></textarea>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-success">Simpan & Selesaikan</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="modal fade" id="detailModal" tabindex="-1" aria-labelledby="detailModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content border-0 shadow">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title fw-bold" id="detailModalLabel">
                                <i class="fas fa-clipboard-list me-2"></i> Detail Registry Feature
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-4">
                            <div class="mb-4">
                                <h6 class="text-uppercase text-muted fw-bold mb-1" style="font-size: 0.8rem;">Nama Tugas</h6>
                                <h4 class="text-dark fw-bold" id="detailTugas"></h4>
                            </div>

                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <div class="p-3 border rounded bg-light h-100 border-start border-4 border-danger">
                                        <h6 class="fw-bold text-secondary mb-2"><i class="fas fa-exclamation-circle me-1"></i> Fakta Saat Ini</h6>
                                        <p class="mb-0 text-dark" id="detailFakta" style="white-space: pre-wrap; font-size: 0.95rem;"></p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="p-3 border rounded bg-light h-100 border-start border-4 border-success">
                                        <h6 class="fw-bold text-secondary mb-2"><i class="fas fa-bullseye me-1"></i> Harapan Sistem</h6>
                                        <p class="mb-0 text-dark" id="detailHarapan" style="white-space: pre-wrap; font-size: 0.95rem;"></p>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-4">
                                <div class="p-3 border rounded bg-white">
                                    <h6 class="fw-bold text-secondary mb-2"><i class="fas fa-sticky-note me-1"></i> Catatan</h6>
                                    <p class="mb-0 text-dark" id="detailCatatan" style="white-space: pre-wrap; font-size: 0.95rem;"></p>
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="p-3 border rounded bg-white h-100 text-center shadow-sm">
                                        <h6 class="fw-bold text-muted mb-2">Durasi Pengerjaan Aktual</h6>
                                        <h5 class="mb-0 text-primary fw-bold" id="detailDurasi"></h5>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="p-3 border rounded bg-white h-100 text-center shadow-sm">
                                        <h6 class="fw-bold text-muted mb-2">Analisis Gap Waktu</h6>
                                        <h5 class="mb-0" id="detailGap"></h5>
                                    </div>
                                </div>
                            </div>

                        </div>
                        <div class="modal-footer bg-light">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup Detail</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tab-content" id="registryMainTabsContent">
                <!-- TAB 1: DATA REGISTRY FEATURE TABLE -->
                <div class="tab-pane fade show active" id="pills-data-table" role="tabpanel" aria-labelledby="pills-data-table-tab" tabindex="0">
                    <div class="card m-4 mt-2">
                        <div class="card-body table-responsive">
                            <h3 class="card-title text-center my-1">{{ __('Data Registry Feature') }}</h3>
                            <table class="table table-striped text-nowrap" id="registryTable" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>Ticket ID</th>
                                        <th>Tugas</th>
                                        <th>Fitur</th>
                                        <th>Tipe</th>
                                        <th>Pemilik</th>
                                        <th>Pengerjaan</th>
                                        <th>Waktu Perkiraan</th>
                                        <th>Status</th>
                                        <th>Mulai</th>
                                        <th>Akhir</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- TAB 2: DASHBOARD FEATURE -->
                <div class="tab-pane fade" id="pills-dashboard-pane" role="tabpanel" aria-labelledby="pills-dashboard-tab" tabindex="0">
                    <!-- Filter Section (Termasuk Filter Mingguan) -->
                    <div class="card shadow-sm border-0 mb-4 bg-light mx-4 mt-2">
                        <div class="card-body p-3">
                            <div class="row g-2 align-items-center">
                                <div class="col-md-2 col-sm-6">
                                    <label for="filterRegistryTahun" class="form-label small fw-bold text-muted mb-1">Tahun:</label>
                                    <select id="filterRegistryTahun" class="form-select form-select-sm">
                                        <option value="all">Semua Tahun</option>
                                        @php
                                            $curYear = now()->year;
                                            for ($y = $curYear + 1; $y >= 2023; $y--) {
                                                $sel = ($y == $curYear) ? 'selected' : '';
                                                echo "<option value=\"$y\" $sel>$y</option>";
                                            }
                                        @endphp
                                    </select>
                                </div>
                                <div class="col-md-2 col-sm-6">
                                    <label for="filterRegistryBulan" class="form-label small fw-bold text-muted mb-1">Bulan:</label>
                                    <select id="filterRegistryBulan" class="form-select form-select-sm">
                                        <option value="all" selected>Semua Bulan</option>
                                        <option value="1">Januari</option>
                                        <option value="2">Februari</option>
                                        <option value="3">Maret</option>
                                        <option value="4">April</option>
                                        <option value="5">Mei</option>
                                        <option value="6">Juni</option>
                                        <option value="7">Juli</option>
                                        <option value="8">Agustus</option>
                                        <option value="9">September</option>
                                        <option value="10">Oktober</option>
                                        <option value="11">November</option>
                                        <option value="12">Desember</option>
                                    </select>
                                </div>
                                <div class="col-md-3 col-sm-6">
                                    <label for="filterRegistryMinggu" class="form-label small fw-bold text-muted mb-1">Mingguan:</label>
                                    <select id="filterRegistryMinggu" class="form-select form-select-sm">
                                        <option value="all" selected>Semua Minggu</option>
                                        <option value="1">Minggu 1 (Tgl 1 - 7)</option>
                                        <option value="2">Minggu 2 (Tgl 8 - 14)</option>
                                        <option value="3">Minggu 3 (Tgl 15 - 21)</option>
                                        <option value="4">Minggu 4 (Tgl 22 - 28)</option>
                                        <option value="5">Minggu 5 (Tgl 29 - 31)</option>
                                    </select>
                                </div>
                                <div class="col-md-3 col-sm-6">
                                    <label for="filterRegistryStatus" class="form-label small fw-bold text-muted mb-1">Status:</label>
                                    <select id="filterRegistryStatus" class="form-select form-select-sm">
                                        <option value="all" selected>Semua Status</option>
                                        <option value="Belum dimulai">Belum Dimulai</option>
                                        <option value="Dalam proses">Dalam Proses</option>
                                        <option value="Selesai">Selesai</option>
                                        <option value="Menunggu Review Estimasi Waktu">Menunggu Review</option>
                                    </select>
                                </div>
                                <div class="col-md-2 col-sm-6">
                                    <label for="filterRegistryTipe" class="form-label small fw-bold text-muted mb-1">Tipe:</label>
                                    <select id="filterRegistryTipe" class="form-select form-select-sm">
                                        <option value="all" selected>Semua Tipe</option>
                                        <option value="Request">Request</option>
                                        <option value="Error">Error</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- KPI Summary Cards (6 Metrik Utama) -->
                    <div class="px-4 mb-4">
                        <div class="row g-3">
                            <div class="col-xl-2 col-md-4 col-sm-6">
                                <div class="card border-0 shadow-sm rounded-3 h-100 border-start border-4 border-primary">
                                    <div class="card-body p-3">
                                        <div class="text-muted small text-uppercase fw-bold">Total Tugas</div>
                                        <div class="fs-3 fw-bold text-primary mt-1" id="regKpiTotal">0</div>
                                        <small class="text-muted"><i class="fas fa-list me-1"></i>Semua Tugas</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-2 col-md-4 col-sm-6">
                                <div class="card border-0 shadow-sm rounded-3 h-100 border-start border-4 border-secondary">
                                    <div class="card-body p-3">
                                        <div class="text-muted small text-uppercase fw-bold">Belum Dimulai</div>
                                        <div class="fs-3 fw-bold text-secondary mt-1" id="regKpiBelum">0</div>
                                        <small class="text-muted"><i class="far fa-clock me-1"></i>Dalam Antrean</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-2 col-md-4 col-sm-6">
                                <div class="card border-0 shadow-sm rounded-3 h-100 border-start border-4 border-warning">
                                    <div class="card-body p-3">
                                        <div class="text-muted small text-uppercase fw-bold">Dalam Proses</div>
                                        <div class="fs-3 fw-bold text-warning mt-1" id="regKpiProses">0</div>
                                        <small class="text-muted"><i class="fas fa-spinner fa-spin me-1"></i>Dikerjakan</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-2 col-md-4 col-sm-6">
                                <div class="card border-0 shadow-sm rounded-3 h-100 border-start border-4 border-success">
                                    <div class="card-body p-3">
                                        <div class="text-muted small text-uppercase fw-bold">Selesai</div>
                                        <div class="fs-3 fw-bold text-success mt-1" id="regKpiSelesai">0</div>
                                        <small class="text-muted"><i class="fas fa-check-circle me-1"></i>Telah Tuntas</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-2 col-md-4 col-sm-6">
                                <div class="card border-0 shadow-sm rounded-3 h-100 border-start border-4 border-info">
                                    <div class="card-body p-3">
                                        <div class="text-muted small text-uppercase fw-bold">Total Estimasi Pengerjaan</div>
                                        <div class="fs-4 fw-bold text-info mt-1" id="regKpiEstimasi">0 Jam</div>
                                        <small class="text-muted"><i class="far fa-hourglass me-1"></i>Rencana Waktu</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-2 col-md-4 col-sm-6">
                                <div class="card border-0 shadow-sm rounded-3 h-100 border-start border-4 border-dark">
                                    <div class="card-body p-3">
                                        <div class="text-muted small text-uppercase fw-bold">Rerata Durasi Pengerjaan</div>
                                        <div class="fs-4 fw-bold text-dark mt-1" id="regKpiAvgDurasi">0 Jam</div>
                                        <small class="text-muted"><i class="fas fa-stopwatch me-1"></i>Waktu Aktual</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Charts Row 1: Trend Bulanan & Komposisi Status/Tipe -->
                    <div class="px-4 mb-4">
                        <div class="row g-3">
                            <div class="col-lg-8">
                                <div class="d-flex justify-content-between align-items-center mb-2 px-1">
                                    <h6 class="fw-bold mb-0 text-dark">
                                        <i class="fas fa-chart-line text-primary me-2"></i>Tren Tugas Registry Feature Bulanan
                                    </h6>
                                    <span class="badge bg-light text-muted border fw-normal" id="regTrendYearLabel">Tahun {{ now()->year }}</span>
                                </div>
                                <div class="card border-0 shadow-sm rounded-3">
                                    <div class="card-body p-3">
                                        <div style="height: 280px; position: relative;">
                                            <canvas id="regMonthlyTrendChart"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-4">
                                <div class="d-flex align-items-center mb-2 px-1" style="min-height: 24px;">
                                    <h6 class="fw-bold mb-0 text-dark">
                                        <i class="fas fa-chart-pie text-success me-2"></i>Distribusi Status & Tipe
                                    </h6>
                                </div>
                                <div class="card border-0 shadow-sm rounded-3">
                                    <div class="card-body p-3">
                                        <div class="row align-items-center">
                                            <div class="col-6 text-center">
                                                <small class="text-muted fw-bold d-block mb-2">Status</small>
                                                <div style="height: 240px; position: relative;">
                                                    <canvas id="regStatusChart"></canvas>
                                                </div>
                                            </div>
                                            <div class="col-6 text-center">
                                                <small class="text-muted fw-bold d-block mb-2">Tipe</small>
                                                <div style="height: 240px; position: relative;">
                                                    <canvas id="regTipeChart"></canvas>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Charts Row 2: Permintaan per Pemilik & Beban Kerja Programmer -->
                    <div class="px-4 mb-4">
                        <div class="row g-3">
                            <div class="col-lg-6">
                                <div class="d-flex align-items-center mb-2 px-1">
                                    <h6 class="fw-bold mb-0 text-dark">
                                        <i class="fas fa-users-cog text-warning me-2"></i>Permintaan Fitur per Pemilik (Divisi)
                                    </h6>
                                </div>
                                <div class="card border-0 shadow-sm rounded-3">
                                    <div class="card-body p-3">
                                        <div style="height: 280px; position: relative;">
                                            <canvas id="regPemilikChart"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="d-flex align-items-center mb-2 px-1">
                                    <h6 class="fw-bold mb-0 text-dark">
                                        <i class="fas fa-laptop-code text-info me-2"></i>Beban Kerja per Pengerja (Programmer)
                                    </h6>
                                </div>
                                <div class="card border-0 shadow-sm rounded-3">
                                    <div class="card-body p-3">
                                        <div style="height: 280px; position: relative;">
                                            <canvas id="regPengerjaChart"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    let regMonthlyTrendChart = null;
    let regStatusChart = null;
    let regTipeChart = null;
    let regPemilikChart = null;
    let regPengerjaChart = null;

    function loadRegistryAnalytics() {
        const tahun = $('#filterRegistryTahun').val() || 'all';
        const bulan = $('#filterRegistryBulan').val() || 'all';
        const minggu = $('#filterRegistryMinggu').val() || 'all';
        const status = $('#filterRegistryStatus').val() || 'all';
        const tipe = $('#filterRegistryTipe').val() || 'all';

        $('#regTrendYearLabel').text(tahun === 'all' ? 'Semua Tahun' : `Tahun ${tahun}`);

        $.ajax({
            url: "{{ route('registry.summary') }}",
            type: 'GET',
            data: {
                tahun: tahun,
                bulan: bulan,
                minggu: minggu,
                status: status,
                tipe: tipe
            },
            dataType: 'json',
            success: function (res) {
                if (res && res.kpi) {
                    $('#regKpiTotal').text(res.kpi.total || 0);
                    $('#regKpiBelum').text(res.kpi.belum_dimulai || 0);
                    $('#regKpiProses').text(res.kpi.dalam_proses || 0);
                    $('#regKpiSelesai').text(res.kpi.selesai || 0);
                    $('#regKpiEstimasi').text((res.kpi.total_estimasi_jam || 0) + ' Jam');
                    $('#regKpiAvgDurasi').text(res.kpi.avg_durasi_human || '0 Jam');
                }

                if (res && res.monthly_trend) {
                    renderRegMonthlyTrendChart(res.monthly_trend);
                }
                if (res && res.by_status) {
                    renderRegStatusChart(res.by_status);
                }
                if (res && res.by_tipe) {
                    renderRegTipeChart(res.by_tipe);
                }
                if (res && res.by_pemilik) {
                    renderRegPemilikChart(res.by_pemilik);
                }
                if (res && res.by_pengerja) {
                    renderRegPengerjaChart(res.by_pengerja);
                }
            },
            error: function (err) {
                console.error('Gagal memuat analitik registry feature:', err);
            }
        });
    }

    function renderRegMonthlyTrendChart(data) {
        const canvas = document.getElementById('regMonthlyTrendChart');
        if (!canvas) return;
        const ctx = canvas.getContext('2d');

        if (regMonthlyTrendChart) {
            regMonthlyTrendChart.destroy();
        }

        regMonthlyTrendChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: data.labels,
                datasets: [
                    {
                        label: 'Tugas Dibuat',
                        data: data.created,
                        borderColor: '#0d6efd',
                        backgroundColor: 'rgba(13, 110, 253, 0.1)',
                        fill: true,
                        tension: 0.3,
                        pointRadius: 4,
                        borderWidth: 2
                    },
                    {
                        label: 'Tugas Selesai',
                        data: data.completed,
                        borderColor: '#198754',
                        backgroundColor: 'rgba(25, 135, 84, 0.1)',
                        fill: true,
                        tension: 0.3,
                        pointRadius: 4,
                        borderWidth: 2
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { precision: 0 }
                    }
                },
                plugins: {
                    legend: {
                        position: 'top'
                    }
                }
            }
        });
    }

    function renderRegStatusChart(data) {
        const canvas = document.getElementById('regStatusChart');
        if (!canvas) return;
        const ctx = canvas.getContext('2d');

        if (regStatusChart) {
            regStatusChart.destroy();
        }

        const colorMap = {
            'Selesai': '#198754',
            'Dalam proses': '#ffc107',
            'Belum dimulai': '#6c757d',
            'Menunggu Review': '#0dcaf0',
            'Telah Direview': '#0d6efd'
        };
        const bgColors = data.labels.map(l => colorMap[l] || '#adb5bd');

        regStatusChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: data.labels,
                datasets: [{
                    data: data.values,
                    backgroundColor: bgColors
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { boxWidth: 12, font: { size: 10 } }
                    }
                }
            }
        });
    }

    function renderRegTipeChart(data) {
        const canvas = document.getElementById('regTipeChart');
        if (!canvas) return;
        const ctx = canvas.getContext('2d');

        if (regTipeChart) {
            regTipeChart.destroy();
        }

        const colorMap = {
            'Request': '#0d6efd',
            'Error': '#dc3545',
            'Maintenance': '#ffc107',
            'Lainnya': '#6c757d'
        };
        const bgColors = data.labels.map(l => colorMap[l] || '#20c997');

        regTipeChart = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: data.labels,
                datasets: [{
                    data: data.values,
                    backgroundColor: bgColors
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { boxWidth: 12, font: { size: 10 } }
                    }
                }
            }
        });
    }

    function renderRegPemilikChart(data) {
        const canvas = document.getElementById('regPemilikChart');
        if (!canvas) return;
        const ctx = canvas.getContext('2d');

        if (regPemilikChart) {
            regPemilikChart.destroy();
        }

        regPemilikChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: data.labels,
                datasets: [{
                    label: 'Jumlah Fitur',
                    data: data.values,
                    backgroundColor: '#fd7e14',
                    borderRadius: 4
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: { precision: 0 }
                    }
                },
                plugins: {
                    legend: { display: false }
                }
            }
        });
    }

    function renderRegPengerjaChart(data) {
        const canvas = document.getElementById('regPengerjaChart');
        if (!canvas) return;
        const ctx = canvas.getContext('2d');

        if (regPengerjaChart) {
            regPengerjaChart.destroy();
        }

        regPengerjaChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: data.labels,
                datasets: [{
                    label: 'Tugas Selesai & Aktif',
                    data: data.values,
                    backgroundColor: '#0dcaf0',
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { precision: 0 }
                    }
                },
                plugins: {
                    legend: { display: false }
                }
            }
        });
    }

    $(document).ready(function() {
        var csrfToken = '{{ csrf_token() }}';

        var registryTable = $('#registryTable').DataTable({
            "ajax": {
                "url": "{{ route('registry.data') }}",
                "type": "GET",
                "data": function(d) {
                    d.tahun = $('#filterRegistryTahun').val() || 'all';
                    d.bulan = $('#filterRegistryBulan').val() || 'all';
                    d.minggu = $('#filterRegistryMinggu').val() || 'all';
                    d.status = $('#filterRegistryStatus').val() || 'all';
                    d.tipe = $('#filterRegistryTipe').val() || 'all';
                }
            },
            "columns": [
                {
                    "data": "ticket_id",
                    "render": function(data) {
                        return data ? data : '-';
                    }
                },
                { "data": "tugas" },
                { "data": "fitur" },
                { "data": "tipe" },
                { "data": "pemilik" },
                {
                    "data": "pengerja",
                    "render": function(data) {
                        return data && data.karyawan ? data.karyawan.kode_karyawan : '-';
                    }
                },
                {
                    "data": "waktu_perkiraan",
                    "render": function(data) {
                        return data ? data + ' Menit' : '-';
                    }
                },
                {
                    "data": "status",
                    "render": function(data) {
                        if (data === 'Selesai') {
                            return '<span class="badge bg-success">' + data + '</span>';
                        } else if (data === 'Dalam proses') {
                            return '<span class="badge bg-warning text-dark">' + data + '</span>';
                        } else if (data === 'Belum dimulai') {
                            return '<span class="badge bg-secondary">' + data + '</span>';
                        } else {
                            return '<span class="badge bg-primary">' + data + '</span>';
                        }
                    }
                },
                {
                    "data": "tanggal_mulai",
                    "render": function(data) {
                        return data ? moment(data).format('DD/MM/YYYY HH:mm') : '-';
                    }
                },
                {
                    "data": "tanggal_akhir",
                    "render": function(data) {
                        return data ? moment(data).format('DD/MM/YYYY HH:mm') : '-';
                    }
                },
                {
                    "data": null,
                    "render": function(data, type, row) {
                        var actions = '<div class="dropdown">';
                        actions += '<button class="btn btn-sm btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">Aksi</button>';
                        actions += '<ul class="dropdown-menu shadow">';

                        actions += '<li><button type="button" class="dropdown-item btn-detail"><i class="fas fa-eye me-2"></i> Detail</button></li>';
                        actions += '<li><hr class="dropdown-divider"></li>';

                        if (row.tanggal_mulai === null) {
                            actions += '<li>';
                            actions += '<button type="button" class="dropdown-item text-primary" onclick="openStartModal(' + row.id + ')"><i class="fas fa-play me-2"></i> Mulai Tugas</button>';
                            actions += '</li>';
                        }

                        if (row.tanggal_mulai !== null && row.tanggal_akhir === null) {
                            actions += '<li>';
                            actions += '<button type="button" class="dropdown-item text-success" onclick="openFinishModal(' + row.id + ')"><i class="fas fa-check-circle me-2"></i> Tandai Selesai</button>';
                            actions += '</li>';
                        }

                        if (row.tanggal_mulai === null || (row.tanggal_mulai !== null && row.tanggal_akhir === null)) {
                            actions += '<li><hr class="dropdown-divider"></li>';
                        }

                        actions += '<li><a class="dropdown-item" href="/registry/' + row.id + '/edit"><i class="fas fa-pencil-alt me-2"></i> Edit</a></li>';

                        actions += '<li><hr class="dropdown-divider"></li>';
                        actions += '<li>';
                        actions += '<form action="/registry/' + row.id + '" method="POST" onsubmit="return confirm(\'Yakin hapus data ini?\');">';
                        actions += '<input type="hidden" name="_token" value="' + csrfToken + '">';
                        actions += '<input type="hidden" name="_method" value="DELETE">';
                        actions += '<button type="submit" class="dropdown-item text-danger"><i class="fas fa-trash-alt me-2"></i> Hapus</button>';
                        actions += '</form>';
                        actions += '</li>';

                        actions += '</ul></div>';
                        return actions;
                    }
                }
            ],
            "order": [],
            "language": {
                "emptyTable": "Belum ada data tugas."
            }
        });

        loadRegistryAnalytics();

        $('button[data-bs-toggle="pill"]').on('shown.bs.tab', function (e) {
            if (e.target.id === 'pills-dashboard-tab') {
                $('#registryHeaderTitle').removeClass('d-none');
                loadRegistryAnalytics();
            } else if (e.target.id === 'pills-data-table-tab') {
                $('#registryHeaderTitle').addClass('d-none');
                if (typeof registryTable !== 'undefined') {
                    registryTable.columns.adjust().draw(false);
                }
            }
        });

        $('#filterRegistryTahun, #filterRegistryBulan, #filterRegistryMinggu, #filterRegistryStatus, #filterRegistryTipe').on('change', function () {
            loadRegistryAnalytics();
            registryTable.ajax.reload();
        });

        $('#registryTable tbody').on('click', '.btn-detail', function () {
            var table = $('#registryTable').DataTable();
            var data = table.row($(this).parents('tr')).data();

            $('#detailTugas').text(data.tugas || '-');
            $('#detailFakta').text(data.fakta || '-');
            $('#detailHarapan').text(data.harapan || '-');
            $('#detailCatatan').text(data.catatan || '-');

            $('#detailDurasi').text(data.durasi_human || '-');

            if (data.tanggal_mulai && data.tanggal_akhir && data.waktu_perkiraan) {
                var waktuMulai = moment(data.tanggal_mulai);
                var waktuAkhir = moment(data.tanggal_akhir);

                var durasiAktual = waktuAkhir.diff(waktuMulai, 'minutes');
                var waktuPerkiraan = parseInt(data.waktu_perkiraan);

                var gap = durasiAktual - waktuPerkiraan;

                if (gap > 0) {
                    $('#detailGap').html('<span class="text-danger fw-bold">+' + gap + ' Menit (Over)</span>');
                } else if (gap < 0) {
                    $('#detailGap').html('<span class="text-success fw-bold">' + gap + ' Menit (Under)</span>');
                } else {
                    $('#detailGap').html('<span class="text-secondary fw-bold">0 Menit (Tepat)</span>');
                }
            } else {
                $('#detailGap').text('-');
            }

            $('#detailModal').modal('show');
        });
    });

    function openStartModal(id) {
        $('#startTaskForm').attr('action', '/registry/' + id + '/start');
        var now = new Date();
        now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
        $('#input_tanggal_mulai').val(now.toISOString().slice(0,16));
        $('#startTaskModal').modal('show');
    }

    function openFinishModal(id) {
        $('#finishTaskForm').attr('action', '/registry/' + id + '/finish');
        var now = new Date();
        now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
        $('#input_tanggal_akhir').val(now.toISOString().slice(0,16));
        $('#finishTaskModal').modal('show');
    }

</script>
@endsection
