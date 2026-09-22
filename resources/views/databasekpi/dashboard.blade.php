@extends('layouts_kpi.app')

@section('kpi_contents')
    <link rel="stylesheet" href="{{ asset('assets/vendor/vendorStyle/penilaian360.css') }}">
    @php
        $userJabatan = auth()->user()->jabatan;
        $isExecutive = in_array($userJabatan, ['HRD', 'GM', 'Direktur Utama', 'Direktur']);
        $currentQuartal = 'Q' . ceil(date('m') / 3);
        $currentYear = date('Y');
    @endphp

    <div class="container flex-grow-1 mt-4">
        <div class="content-wrapper">

            <div class="row g-4 mb-4">
                <div class="col-md-6 col-xl-3 stretch-card d-flex">
                    <div class="plain-card flex-fill w-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div class="stat-icon aktif"><i class="ti-user"></i></div>
                                <span class="stat-badge aktif">Aktif</span>
                            </div>
                            <h6 class="text-muted mb-1" style="font-size:.85rem;">Jumlah Karyawan Aktif</h6>
                            <h3 class="fw-bold mb-0 text-dark"><span id="content_JK">{{ $criticalStats['karyawan_aktif'] }}</span></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3 stretch-card d-flex">
                    <div class="plain-card flex-fill w-100" data-bs-toggle="modal" data-bs-target="#modalSakit" style="cursor:pointer;">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div class="stat-icon sakit"><i class="ti-medall"></i></div>
                                <span class="stat-badge sakit">Semester</span>
                            </div>
                            <h6 class="text-muted mb-1" style="font-size:.85rem;">{{ $isExecutive ? 'Sakit Dalam Semester Ini' : 'Data Sakit Anda Semester Ini' }}</h6>
                            <h3 class="fw-bold mb-0 text-dark">
                                <span id="content_KS"><div class="skeleton skeleton-text-lg" style="width:100px;"></div></span>
                            </h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3 stretch-card d-flex">
                    <div class="plain-card flex-fill w-100" data-bs-toggle="modal" data-bs-target="#modalIzin" style="cursor:pointer;">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div class="stat-icon izin"><i class="ti-check-box"></i></div>
                                <span class="stat-badge izin">Triwulan</span>
                            </div>
                            <h6 class="text-muted mb-1" style="font-size:.85rem;">{{ $isExecutive ? 'Izin Dalam Triwulan Ini' : 'Data Izin Anda Semester Ini' }}</h6>
                            <h3 class="fw-bold mb-0 text-dark">
                                <span id="content_KI"><div class="skeleton skeleton-text-lg" style="width:100px;"></div></span>
                            </h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3 stretch-card d-flex">
                    <div class="plain-card flex-fill w-100" data-bs-toggle="modal" data-bs-target="#modalCuti" style="cursor:pointer;">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div class="stat-icon cuti"><i class="ti-calendar"></i></div>
                                <span class="stat-badge cuti">Triwulan</span>
                            </div>
                            <h6 class="text-muted mb-1" style="font-size:.85rem;">{{ $isExecutive ? 'Cuti Dalam Triwulan Ini' : 'Data Cuti Anda Semester Ini' }}</h6>
                            <h3 class="fw-bold mb-0 text-dark">
                                <span id="content_KC"><div class="skeleton skeleton-text-lg" style="width:100px;"></div></span>
                            </h3>
                        </div>
                    </div>
                </div>
            </div>

            @if ($isExecutive)
                <div class="row g-4 mb-4" id="companyProgressRow" style="display:none;">
                    <div class="col-xl-8 d-flex">
                        <div class="plain-card flex-fill">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-1 flex-wrap gap-2">
                                    <div>
                                        <h5 class="fw-bold text-dark mb-1">Tren Progress Perusahaan</h5>
                                        <p class="text-muted small mb-0">Garis putus-putus adalah proyeksi 2 bulan ke depan</p>
                                    </div>
                                    <div id="companyTrendBadge"></div>
                                </div>
                                <div id="companyProgressSkeleton" style="height:280px;" class="mt-3"><div class="skeleton skeleton-chart"></div></div>
                                <div style="height:280px; display:none;" class="mt-3" id="companyProgressChartWrap">
                                    <canvas id="companyProgressChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-4 d-flex">
                        <div class="plain-card flex-fill">
                            <div class="card-body">
                                <div class="section-header">
                                    <h5>Ringkasan Divisi</h5>
                                    <span class="badge-count" id="divisiCount">0 Divisi</span>
                                </div>
                                <p class="text-muted small mb-3">Klik divisi untuk lihat detail tim</p>
                                <div id="companyDivisiOverview" class="d-flex flex-column gap-2" style="max-height:300px; overflow-y:auto;">
                                    <div class="skeleton skeleton-row"></div>
                                    <div class="skeleton skeleton-row"></div>
                                    <div class="skeleton skeleton-row"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                @if ($isExecutive)
                    <div class="row g-4 mb-4">
                        <div class="col-xl-8 d-flex">
                            <div class="plain-card flex-fill">
                                <div class="card-body">
                                    <div class="section-header">
                                        <h5><i class="fas fa-fire text-danger me-2"></i>KPI Alerts & Prioritas</h5>
                                        <span class="badge-count" id="alertsCount">0 Alerts</span>
                                    </div>
                                    <div id="kpiAlertsContainer" style="max-height: 340px; overflow-y: auto;">
                                        <div class="skeleton skeleton-row"></div>
                                        <div class="skeleton skeleton-row"></div>
                                        <div class="skeleton skeleton-row"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-4 d-flex">
                            <div class="plain-card flex-fill">
                                <div class="card-body">
                                    <div class="section-header">
                                        <h5><i class="fas fa-calendar-check text-primary me-2"></i>Deadline Terdekat</h5>
                                    </div>
                                    <div id="upcomingDeadlinesContainer" style="max-height: 340px; overflow-y: auto;">
                                        <div class="skeleton skeleton-timeline"></div>
                                        <div class="skeleton skeleton-timeline"></div>
                                        <div class="skeleton skeleton-timeline"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                @if ($isExecutive)
                    <div class="row g-4 mb-4">
                        <div class="col-12">
                            <div class="plain-card">
                                <div class="card-body">
                                    <div class="section-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
                                        <h5 class="mb-0">
                                            <i class="fas fa-th text-primary me-2"></i>Heatmap Performa Divisi
                                        </h5>

                                        {{-- Select hanya untuk HRD --}}
                                        @if ($isExecutive)
                                            <div class="d-flex flex-wrap gap-2">
                                                <select id="heatmapMetricSelect" class="form-select form-select-sm" style="min-width:140px;">
                                                    <option value="progress">Progress</option>
                                                    <option value="completion">Completion</option>
                                                    <option value="engagement">Engagement</option>
                                                </select>

                                                <select id="heatmapDivisiSelect" class="form-select form-select-sm" style="min-width:180px;">
                                                    <option value="">Pilih Divisi</option>
                                                </select>
                                            </div>
                                        @else
                                            {{-- Non-HRD: hanya tampilkan metric (opsional, bisa dihilangkan) --}}
                                            <select id="heatmapMetricSelect" class="form-select form-select-sm" style="min-width:140px;">
                                                <option value="progress">Progress</option>
                                                <option value="completion">Completion</option>
                                                <option value="engagement">Engagement</option>
                                            </select>
                                        @endif
                                    </div>

                                    <!-- Penjelasan metric -->
                                    <div id="heatmapMetricDesc" class="alert alert-light border small mb-3 py-2 px-3">
                                        <i class="fas fa-info-circle text-primary me-1"></i>
                                        <span id="heatmapMetricDescText">Pilih metric untuk melihat penjelasan.</span>
                                    </div>

                                    <div id="performanceHeatmapContainer">
                                        <div id="heatmapGrid" class="d-flex gap-1 flex-wrap justify-content-between">
                                            <div class="skeleton" style="width:100%;height:90px;"></div>
                                        </div>

                                        <!-- Legend -->
                                        <div class="d-flex justify-content-end align-items-center gap-2 mt-3 small">
                                            <span>Rendah</span>
                                            <div class="d-flex gap-1">
                                                <div class="heatmap-cell heatmap-empty" style="width:16px;height:16px;padding:0;"></div>
                                                <div class="heatmap-cell heatmap-1" style="width:16px;height:16px;padding:0;"></div>
                                                <div class="heatmap-cell heatmap-2" style="width:16px;height:16px;padding:0;"></div>
                                                <div class="heatmap-cell heatmap-3" style="width:16px;height:16px;padding:0;"></div>
                                                <div class="heatmap-cell heatmap-4" style="width:16px;height:16px;padding:0;"></div>
                                                <div class="heatmap-cell heatmap-5" style="width:16px;height:16px;padding:0;"></div>
                                            </div>
                                            <span>Tinggi</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            @endif

            <div class="row g-4 mb-4 align-items-stretch">
                @if ($isExecutive && $userJabatan !== 'Koordinator ITSM')
                    <div class="col-xl-7 d-flex flex-column gap-4">
                        <div class="plain-card flex-fill">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                                    <h6 class="mb-0 fw-bold text-dark"><i class="mdi mdi-trophy-variant mdi-18px text-warning me-1"></i> Terbaik Divisi</h6>
                                    <form action="{{ route('databaseKPI.downloadDivisi') }}" method="post" class="d-flex" style="min-width:220px;">
                                        @csrf
                                        <div class="input-group">
                                            <select class="form-select bg-white text-dark" id="select_peringkatPenilaian" name="divisi">
                                                <option>Education</option>
                                            </select>
                                            <button class="btn btn-warning text-white" type="submit" id="btn_exportPDF_rangking">
                                                <i class="fa-solid fa-file-pdf"></i>
                                            </button>
                                        </div>
                                        <input type="hidden" name="quartal" value="{{ $currentQuartal }}">
                                        <input type="hidden" name="tahun" value="{{ $currentYear }}">
                                    </form>
                                </div>
                                <div class="row justify-content-center align-items-end text-center row-ranking" style="min-height:300px;">
                                    <div class="col-12 col-sm-6 col-lg-4 mb-4"><div class="skeleton skeleton-podium"></div></div>
                                    <div class="col-12 col-sm-6 col-lg-4 mb-4"><div class="skeleton skeleton-podium"></div></div>
                                    <div class="col-12 col-sm-6 col-lg-4 mb-4"><div class="skeleton skeleton-podium"></div></div>
                                </div>
                                <div class="text-center mt-3">
                                    <small class="text-muted fst-italic">*hasil diambil dari penilaian 360°, tidak termasuk yang lainnya</small>
                                </div>
                                <div class="text-center mt-3">
                                    <button type="button" class="btn btn-primary rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#modalPeringkatPenilaian360">lihat semua</button>
                                </div>
                            </div>
                        </div>
                        <div class="plain-card flex-fill">
                            <div class="card-body">
                                <h5 class="fw-bold text-dark mb-1">Assessment 360°</h5>
                                <p class="text-muted small mb-3">Radar penilaian berdasarkan jenis evaluator</p>
                                <div id="contentKPIDivisi"><div class="skeleton skeleton-chart-sm"></div></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-5 d-flex flex-column">
                        <div class="plain-card flex-fill h-100">
                            <div class="card-body">
                                <h5 class="fw-bold text-dark mb-4">Chart Penilaian 360°</h5>
                                <div id="containerChartPenilaian" class="position-relative" style="min-height: 250px;">
                                    <div id="loadingPenilaian" class="position-absolute top-0 start-0 w-100 h-100 d-flex flex-column justify-content-center align-items-center" style="z-index: 10;">
                                        <div class="skeleton skeleton-chart"></div>
                                    </div>
                                    <div id="emptyPenilaian" class="position-absolute top-0 start-0 w-100 h-100 d-none flex-column justify-content-center align-items-center bg-white" style="z-index: 10; border-radius: 1rem;">
                                        <i class="fas fa-chart-pie fs-1 text-muted opacity-50"></i>
                                        <p class="text-muted small mt-2 mb-0">Belum ada data penilaian</p>
                                    </div>
                                    <div id="contentPenilaian" style="display:none;">
                                        <p id="title_chartPenilaian" class="text-center text-muted small mb-3"></p>
                                        <div class="doughnutjs-wrapper d-flex justify-content-center">
                                            <canvas id="myChart"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="col-xl-5 d-flex flex-column">
                        <div class="plain-card flex-fill h-100">
                            <div class="card-body">
                                <h5 class="fw-bold text-dark mb-4">Chart Penilaian 360°</h5>
                                <div id="containerChartPenilaian" class="position-relative" style="min-height: 250px;">
                                    <div id="loadingPenilaian" class="position-absolute top-0 start-0 w-100 h-100 d-flex flex-column justify-content-center align-items-center" style="z-index: 10;">
                                        <div class="skeleton skeleton-chart"></div>
                                    </div>
                                    <div id="emptyPenilaian" class="position-absolute top-0 start-0 w-100 h-100 d-none flex-column justify-content-center align-items-center bg-white" style="z-index: 10; border-radius: 1rem;">
                                        <i class="fas fa-chart-pie fs-1 text-muted opacity-50"></i>
                                        <p class="text-muted small mt-2 mb-0">Belum ada data penilaian</p>
                                    </div>
                                    <div id="contentPenilaian" style="display:none;">
                                        <p id="title_chartPenilaian" class="text-center text-muted small mb-3"></p>
                                        <div class="doughnutjs-wrapper d-flex justify-content-center">
                                            <canvas id="myChart"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-7 d-flex flex-column">
                        <div class="plain-card flex-fill h-100">
                            <div class="card-body d-flex flex-column justify-content-center" id="contentKPIPersonal">
                                <div class="w-100">
                                    <div class="skeleton skeleton-title" style="width:40%;"></div>
                                    <div class="skeleton skeleton-text-lg" style="width:30%;"></div>
                                    <div class="skeleton skeleton-text-sm mt-3" style="width:60%;"></div>
                                    <div class="skeleton mt-3" style="height:8px;width:100%;"></div>
                                    <div class="skeleton skeleton-text mt-3" style="width:80%;"></div>
                                    <div class="skeleton skeleton-text" style="width:70%;"></div>
                                    <div class="skeleton skeleton-text" style="width:90%;"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            @if ($isExecutive)
                <div class="row g-4 mb-4 align-items-stretch">
                    <div class="col-lg-4 d-flex flex-column">
                        <div class="plain-card flex-fill">
                            <div class="card-body">
                                <div class="section-header"><h5><i class="fas fa-stream text-warning me-2"></i>Aktivitas Terbaru</h5></div>
                                <div id="activityTimelineContainer" style="max-height: 300px; overflow-y: auto;">
                                    <div class="skeleton skeleton-timeline"></div>
                                    <div class="skeleton skeleton-timeline"></div>
                                    <div class="skeleton skeleton-timeline"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 d-flex flex-column">
                        <div class="plain-card flex-fill">
                            <div class="card-body">
                                <div class="section-header">
                                    <h5><i class="fas fa-medal text-warning me-2"></i>Pencapaian Terbaru</h5>
                                    <span class="badge-count" id="achievementsCount">0</span>
                                </div>
                                <div id="achievementsContainer" style="max-height: 300px; overflow-y: auto;">
                                    <div class="skeleton skeleton-row"></div>
                                    <div class="skeleton skeleton-row"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 d-flex flex-column">
                        <div class="plain-card flex-fill">
                            <div class="card-body">
                                <div class="section-header">
                                    <h5><i class="fas fa-bullhorn text-primary me-2"></i>Pengumuman</h5>
                                    <span class="badge-count" id="newsCount">0</span>
                                </div>
                                <div id="newsContainer" style="max-height: 300px; overflow-y: auto;">
                                    <div class="skeleton skeleton-row"></div>
                                    <div class="skeleton skeleton-row"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-4 mb-4 align-items-stretch">
                    <div class="col-lg-6 d-flex flex-column">
                        <div class="plain-card flex-fill h-100">
                            <div class="card-body">
                                <h5 class="fw-bold text-dark mb-1">Data Formulir</h5>
                                <p class="text-muted small mb-4">Distribusi status karyawan</p>
                                <div class="row mt-2 align-items-center">
                                    <div class="col-sm-6 position-relative" style="min-height: 200px;">
                                        <div id="loadingFormulir" class="position-absolute top-0 start-0 w-100 h-100 d-flex flex-column justify-content-center align-items-center" style="z-index: 10; border-radius: 1rem;">
                                            <div class="skeleton skeleton-chart-sm"></div>
                                        </div>
                                        <canvas id="doughnutCharthr" height="200"></canvas>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="row mb-3">
                                            <div class="col-6" id="totalFormulir"></div>
                                            <div class="col-6" id="totalRutin"></div>
                                        </div>
                                        <div class="row">
                                            <div class="col-6" id="totalProbation"></div>
                                            <div class="col-6" id="totalKontrak"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 d-flex flex-column">
                        <div class="plain-card flex-fill h-100">
                            <div class="card-body d-flex flex-column">
                                <div class="mb-3">
                                    <h5 class="fw-bold text-dark mb-1">Insight KPI</h5>
                                    <small class="text-muted">Kesehatan target, tren, dan sorotan performa</small>
                                </div>
                                <div class="flex-grow-1 overflow-auto" style="max-height: 480px;">
                                    <div class="d-flex flex-column gap-4" id="contentKPITim">
                                        <div class="skeleton skeleton-card"></div>
                                        <div class="skeleton skeleton-card"></div>
                                        <div class="skeleton skeleton-card"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- (Modal-modal tetap sama seperti kode asli Anda, tidak diubah agar tetap fungsional) --}}
    <div class="modal fade" id="modalSakit" tabindex="-1" aria-labelledby="modalSakitLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="modalSakitLabel">Data Sakit Semester Ini</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center py-4" id="loadingModalSakit" style="display:none;">
                        <div class="skeleton skeleton-table-row"></div>
                        <div class="skeleton skeleton-table-row"></div>
                        <div class="skeleton skeleton-table-row"></div>
                    </div>
                    <div id="contentModalSakit" class="d-none">
                        <p class="text-muted text-center">Data sakit akan ditampilkan di sini.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    {{-- 4. Modal Drilldown Divisi (paling penting untuk heatmap) --}}
    <div class="modal fade" id="modalDivisiDrilldown" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="drilldownDivisiTitle">Detail Divisi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="drilldownLoading" class="text-center py-5">
                        <div class="spinner-border text-primary"></div>
                        <p class="text-muted mt-2 mb-0">Memuat data divisi...</p>
                    </div>
                    <div id="drilldownEmpty" class="text-center py-5 d-none text-muted">Data tidak tersedia.</div>
                    <div id="drilldownContent" class="d-none">
                        <div class="row g-4">
                            <div class="col-lg-7">
                                <h6 class="fw-bold mb-3">Tren Progress Bulanan</h6>
                                <div style="height:260px;"><canvas id="drilldownChart"></canvas></div>
                            </div>
                            <div class="col-lg-5">
                                <h6 class="fw-bold mb-3">Insight</h6>
                                <div id="drilldownInsights" class="d-flex flex-column gap-2" style="max-height:260px;overflow-y:auto;"></div>
                            </div>
                        </div>
                        <hr>
                        <h6 class="fw-bold mb-3">Anggota Tim</h6>
                        <div id="drilldownTeamList" class="d-flex flex-column gap-2" style="max-height:300px;overflow-y:auto;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 5. Modal Deadline Detail --}}
    <div class="modal fade" id="modalDeadlineDetail" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Detail Deadline</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="deadlineDetailLoading" class="text-center py-4">
                        <div class="spinner-border text-primary"></div>
                    </div>
                    <div id="deadlineDetailContent" class="d-none"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- 6. Modal Activity Detail --}}
    <div class="modal fade" id="modalActivityDetail" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Detail Aktivitas</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="activityDetailLoading" class="text-center py-4">
                        <div class="spinner-border text-primary"></div>
                    </div>
                    <div id="activityDetailContent" class="d-none"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- 7. Modal Achievement Detail --}}
    <div class="modal fade" id="modalAchievementDetail" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Detail Pencapaian</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="achievementDetailLoading" class="text-center py-4">
                        <div class="spinner-border text-primary"></div>
                    </div>
                    <div id="achievementDetailContent" class="d-none"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- 8. Modal News Detail --}}
    <div class="modal fade" id="modalNewsDetail" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Detail Pengumuman</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="newsDetailLoading" class="text-center py-4">
                        <div class="spinner-border text-primary"></div>
                    </div>
                    <div id="newsDetailContent" class="d-none"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- 9. Modal Peringkat (kalau dipakai) --}}
    <div class="modal fade" id="modalPeringkatPenilaian360" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="title_peringkat">Peringkat Divisi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="bodyContentPeringkat"></div>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('assets/vendor/libs/chartjs/chart.js') }}"></script>

    <script>
        window.dashboardContentUrl = "{{ route('databaseKPI.dashboardContent') }}";
        window.progressDashboardUrl = "{{ route('kpi.getProgressDasboard') }}";
        window.chartStatsUrl = "{{ url('/kpi-data/get-statistika') }}";
        window.divisiDrilldownUrl = "{{ route('kpi.divisiDrilldown') }}";
        window.deadlineDetailUrl = "{{ route('kpi.deadlineDetail') }}";
        window.activityDetailUrl = "{{ route('kpi.activityDetail') }}";
        window.achievementDetailUrl = "{{ route('kpi.achievementDetail') }}";
        window.newsDetailUrl = "{{ route('kpi.newsDetail') }}";
        window.assetStorageUrl = "{{ asset('storage') }}";
        window.defaultProfileUrl = "{{ asset('template_KPI/dist/assets/images/screenshots/user-profile.jpg') }}";
        window.userJabatan = "{{ $userJabatan }}";
        window.isExecutive = {{ $isExecutive ? 'true' : 'false' }};

        window.userDivisi = "{{ auth()->user()->divisi ?? '' }}";
        window.isHRD = {{ $userJabatan === 'HRD' ? 'true' : 'false' }};
    </script>

    <script src="{{ asset('assets/js/penilaian360/dashboard.js') }}" defer></script>
@endsection