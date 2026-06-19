@extends('layouts_kpi.app')

@section('kpi_contents')
    <style>
        .btn-plain {
            all: unset;
            cursor: pointer;
            display: block;
            padding: 0;
        }

        #totalPenilaianChart {
            max-height: 250px;
        }

        .loading-spinner {
            width: 60px;
            height: 60px;
            border: 6px solid transparent;
            border-top: 6px solid #a78bfa;
            border-right: 6px solid #38bdf8;
            border-bottom: 6px solid #34d399;
            border-left: 6px solid #facc15;
            border-radius: 50%;
            animation: spin 1.2s linear infinite;
            margin: auto;
        }

        @keyframes spin {
            100% { transform: rotate(360deg); }
        }

        @media only screen and (max-width:800px) {
            .doughnutjs-wrapper {
                width: 100%;
                max-width: 400px;
                height: auto;
            }
            canvas#myChart {
                width: 100% !important;
                height: 100% !important;
            }
            .card-trafic {
                max-height: none;
                height: 300px;
            }
        }

        .card-trafic {
            max-height: 170px;
            overflow-x: hidden;
        }

        @media (max-width: 768px) {
            #select_peringkatPenilaian {
                width: 100% !important;
            }
        }

        #btn_exportPDF_rangking {
            min-width: 50px;
        }

        .card-podium-1 { transform: scale(1.05); }
        .card-podium-2 { transform: scale(0.95); }
        .card-podium-3 { transform: scale(0.9); }

        #contentKPIDivisi::-webkit-scrollbar { height: 6px; }
        #contentKPIDivisi::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 3px; }
        #contentKPIDivisi::-webkit-scrollbar-thumb { background: #c1c1c1; border-radius: 3px; }
        #contentKPIDivisi::-webkit-scrollbar-thumb:hover { background: #a8a8a8; }
        #contentKPIDivisi { -webkit-overflow-scrolling: touch; }

        .legend-box {
            display: inline-block;
            width: 15px;
            height: 15px;
            border-radius: 3px;
        }

        /* ===== Plain Card Theme ===== */
        .plain-card {
            background: #fff;
            border: 0;
            border-radius: 1rem;
            box-shadow: 0 2px 12px rgba(0,0,0,.06);
            transition: box-shadow .2s ease;
        }
        .plain-card:hover {
            box-shadow: 0 4px 20px rgba(0,0,0,.08);
        }
        .plain-card .card-body { padding: 1.5rem; }

        .stat-icon {
            width: 48px; height: 48px;
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.4rem;
        }
        .stat-icon.aktif   { background: rgba(99,102,241,.12); color: #6366f1; }
        .stat-icon.sakit   { background: rgba(14,165,233,.12); color: #0ea5e9; }
        .stat-icon.izin    { background: rgba(16,185,129,.12); color: #10b981; }
        .stat-icon.cuti    { background: rgba(245,158,11,.12); color: #f59e0b; }

        .stat-badge {
            font-size: .7rem;
            font-weight: 600;
            padding: .35rem .7rem;
            border-radius: 999px;
        }
        .stat-badge.aktif { background: rgba(99,102,241,.1);  color: #6366f1; }
        .stat-badge.sakit { background: rgba(14,165,233,.1);  color: #0ea5e9; }
        .stat-badge.izin  { background: rgba(16,185,129,.1);  color: #10b981; }
        .stat-badge.cuti  { background: rgba(245,158,11,.1);  color: #f59e0b; }

        /* Ranking podium gradients (no red) */
        .podium-1 { background: linear-gradient(135deg,#fde68a,#f59e0b); }
        .podium-2 { background: linear-gradient(135deg,#e0e7ff,#a5b4fc); }
        .podium-3 { background: linear-gradient(135deg,#fed7aa,#fb923c); }

        /* Progress bar no-red */
        .progress-bar.bg-danger-soft { background-color: #f59e0b !important; }

        /* Scrollbar tipis global */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #9ca3af; }
    </style>

    <div class="container flex-grow-1 mt-4">
        <div class="content-wrapper">

            {{-- ===== TOP 4 STAT CARDS ===== --}}
            <div class="row g-4 mb-4">
                {{-- Card 1: Jumlah Karyawan Aktif --}}
                <div class="col-md-6 col-xl-3 stretch-card d-flex">
                    <div class="plain-card flex-fill w-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div class="stat-icon aktif"><i class="ti-user"></i></div>
                                <span class="stat-badge aktif">Aktif</span>
                            </div>
                            <h6 class="text-muted mb-1" style="font-size:.85rem;">Jumlah Karyawan Aktif</h6>
                            <h3 class="fw-bold mb-0 text-dark">
                                <span id="content_JK">
                                    <div class="spinner-border text-primary" role="status" style="width:1.5rem;height:1.5rem;"></div>
                                </span>
                            </h3>
                        </div>
                    </div>
                </div>

                {{-- Card 2: Sakit --}}
                <div class="col-md-6 col-xl-3 stretch-card d-flex">
                    <div class="plain-card flex-fill w-100"
                         data-bs-toggle="modal" data-bs-target="#modalSakit" style="cursor:pointer;">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div class="stat-icon sakit"><i class="ti-medall"></i></div>
                                <span class="stat-badge sakit">Semester</span>
                            </div>
                            <h6 class="text-muted mb-1" style="font-size:.85rem;">
                                @if (auth()->user()->jabatan === 'HRD' || auth()->user()->jabatan === 'GM' || auth()->user()->jabatan === 'Direktur Utama')
                                    Sakit Dalam Semester Ini
                                @else
                                    Data Sakit Anda Semester Ini
                                @endif
                            </h6>
                            <h3 class="fw-bold mb-0 text-dark">
                                <span id="content_KS">
                                    <div class="spinner-border text-primary" role="status" style="width:1.5rem;height:1.5rem;"></div>
                                </span>
                            </h3>
                        </div>
                    </div>
                </div>

                {{-- Card 3: Izin --}}
                <div class="col-md-6 col-xl-3 stretch-card d-flex">
                    <div class="plain-card flex-fill w-100"
                         data-bs-toggle="modal" data-bs-target="#modalIzin" style="cursor:pointer;">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div class="stat-icon izin"><i class="ti-check-box"></i></div>
                                <span class="stat-badge izin">Triwulan</span>
                            </div>
                            <h6 class="text-muted mb-1" style="font-size:.85rem;">
                                @if (auth()->user()->jabatan === 'HRD' || auth()->user()->jabatan === 'GM' || auth()->user()->jabatan === 'Direktur Utama')
                                    Izin Dalam Triwulan Ini
                                @else
                                    Data Izin Anda Semester Ini
                                @endif
                            </h6>
                            <h3 class="fw-bold mb-0 text-dark">
                                <span id="content_KI">
                                    <div class="spinner-border text-primary" role="status" style="width:1.5rem;height:1.5rem;"></div>
                                </span>
                            </h3>
                        </div>
                    </div>
                </div>

                {{-- Card 4: Cuti --}}
                <div class="col-md-6 col-xl-3 stretch-card d-flex">
                    <div class="plain-card flex-fill w-100"
                         data-bs-toggle="modal" data-bs-target="#modalCuti" style="cursor:pointer;">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div class="stat-icon cuti"><i class="ti-calendar"></i></div>
                                <span class="stat-badge cuti">Triwulan</span>
                            </div>
                            <h6 class="text-muted mb-1" style="font-size:.85rem;">
                                @if (auth()->user()->jabatan === 'HRD' || auth()->user()->jabatan === 'GM' || auth()->user()->jabatan === 'Direktur Utama')
                                    Cuti Dalam Triwulan Ini
                                @else
                                    Data Cuti Anda Semester Ini
                                @endif
                            </h6>
                            <h3 class="fw-bold mb-0 text-dark">
                                <span id="content_KC">
                                    <div class="spinner-border text-primary" role="status" style="width:1.5rem;height:1.5rem;"></div>
                                </span>
                            </h3>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ===== MIDDLE SECTION ===== --}}
            <div class="row g-4 mb-4 align-items-stretch">
                @if (auth()->user()->jabatan === 'HRD' ||
                        auth()->user()->jabatan === 'GM' ||
                        auth()->user()->jabatan === 'Direktur Utama')

                    {{-- Kolom Kiri: Terbaik Divisi + Progress Divisi --}}
                    <div class="col-xl-7 d-flex flex-column gap-4">

                        {{-- Terbaik Divisi --}}
                        <div class="plain-card flex-fill">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                                    <h6 class="mb-0 fw-bold text-dark">
                                        <i class="mdi mdi-trophy-variant mdi-18px text-warning me-1"></i> Terbaik Divisi
                                    </h6>
                                    <form action="{{ route('databaseKPI.downloadDivisi') }}" method="post"
                                          class="d-flex" style="min-width:220px;">
                                        @csrf
                                        <div class="input-group">
                                            <select class="form-select bg-white text-dark" id="select_peringkatPenilaian" name="divisi">
                                                <option>Education</option>
                                            </select>
                                            <button class="btn btn-warning text-white" type="submit" id="btn_exportPDF_rangking">
                                                <i class="fa-solid fa-file-pdf"></i>
                                            </button>
                                        </div>
                                        <input type="hidden" name="quartal" value="Q{{ ceil(date('m') / 3) }}">
                                        <input type="hidden" name="tahun" value="{{ date('Y') }}">
                                    </form>
                                </div>

                                <div class="row justify-content-center align-items-end text-center row-ranking"
                                     style="min-height:300px;">
                                    <div class="loader" id="loader">
                                        <div class="bubble"></div>
                                        <div class="bubble"></div>
                                        <div class="bubble"></div>
                                        <div class="bubble"></div>
                                    </div>
                                </div>

                                <div class="text-center mt-3">
                                    <small class="text-muted fst-italic">*hasil diambil dari penilaian 360°, tidak termasuk yang lainnya</small>
                                </div>
                                <div class="text-center mt-3">
                                    <button type="button" class="btn btn-primary rounded-pill px-4"
                                            data-bs-toggle="modal" data-bs-target="#modalPeringkatPenilaian360">
                                        lihat semua
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- Progress Divisi --}}
                        <div class="plain-card flex-fill">
                            <div class="card-body">
                                <h5 class="fw-bold text-dark mb-1">Progress Divisi</h5>
                                <p class="text-muted small mb-3">Ringkasan performa tahun berjalan</p>
                                <div id="contentKPIDivisi" class="d-flex flex-nowrap overflow-x-auto pb-3 gap-3"
                                     style="scroll-behavior: smooth; -webkit-overflow-scrolling: touch;">
                                    <div class="d-flex justify-content-center align-items-center w-100 text-muted py-4">
                                        <div class="spinner-border text-secondary me-2" style="width:1.5rem;height:1.5rem;"></div>
                                        <span class="small">Memuat data...</span>
                                    </div>
                                </div>
                                <div id="scrollHint" class="text-center small text-muted" style="display:none;">
                                    <i class="bi bi-arrow-left-right me-1"></i> Geser untuk melihat divisi lain
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Kolom Kanan: Chart Penilaian 360° --}}
                    <div class="col-xl-5 d-flex flex-column">
                        <div class="plain-card flex-fill h-100">
                            <div class="card-body">
                                <h5 class="fw-bold text-dark mb-4">Chart Penilaian 360°</h5>
                                <div id="containerChartPenilaian" class="position-relative" style="min-height: 250px;">
                                    <div id="loadingPenilaian"
                                         class="position-absolute top-0 start-0 w-100 h-100 d-flex flex-column justify-content-center align-items-center bg-white"
                                         style="z-index: 10; border-radius: 1rem;">
                                        <div class="spinner-border text-primary" role="status"></div>
                                        <small class="text-muted mt-2">Memuat penilaian...</small>
                                    </div>
                                    <div id="emptyPenilaian"
                                         class="position-absolute top-0 start-0 w-100 h-100 d-none flex-column justify-content-center align-items-center bg-white"
                                         style="z-index: 10; border-radius: 1rem;">
                                        <i class="fas fa-chart-pie fs-1 text-muted opacity-50"></i>
                                        <p class="text-muted small mt-2 mb-0">Belum ada data penilaian</p>
                                    </div>
                                    <div id="contentPenilaian">
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
                    {{-- User biasa: Chart 360 + KPI Personal --}}
                    <div class="col-xl-5 d-flex flex-column">
                        <div class="plain-card flex-fill h-100">
                            <div class="card-body">
                                <h5 class="fw-bold text-dark mb-4">Chart Penilaian 360°</h5>
                                <div id="containerChartPenilaian" class="position-relative" style="min-height: 250px;">
                                    <div id="loadingPenilaian"
                                         class="position-absolute top-0 start-0 w-100 h-100 d-flex flex-column justify-content-center align-items-center bg-white"
                                         style="z-index: 10; border-radius: 1rem;">
                                        <div class="spinner-border text-primary" role="status"></div>
                                        <small class="text-muted mt-2">Memuat penilaian...</small>
                                    </div>
                                    <div id="emptyPenilaian"
                                         class="position-absolute top-0 start-0 w-100 h-100 d-none flex-column justify-content-center align-items-center bg-white"
                                         style="z-index: 10; border-radius: 1rem;">
                                        <i class="fas fa-chart-pie fs-1 text-muted opacity-50"></i>
                                        <p class="text-muted small mt-2 mb-0">Belum ada data penilaian</p>
                                    </div>
                                    <div id="contentPenilaian">
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
                                <div class="text-center py-5">
                                    <div class="spinner-border text-secondary mb-3" style="width:2rem;height:2rem;"></div>
                                    <div class="small text-muted">Memuat data KPI...</div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            {{-- ===== BOTTOM SECTION ===== --}}
            <div class="row g-4 mb-4 align-items-stretch">
                @if (auth()->user()->jabatan === 'HRD' ||
                        auth()->user()->jabatan === 'GM' ||
                        auth()->user()->jabatan === 'Direktur Utama')

                    {{-- Data Formulir --}}
                    <div class="col-lg-6 d-flex flex-column">
                        <div class="plain-card flex-fill h-100">
                            <div class="card-body">
                                <h5 class="fw-bold text-dark mb-1">Data Formulir</h5>
                                <p class="text-muted small mb-4">Distribusi status karyawan</p>
                                <div class="row mt-2 align-items-center">
                                    <div class="col-sm-6 position-relative" style="min-height: 200px;">
                                        <div id="loadingFormulir"
                                             class="position-absolute top-0 start-0 w-100 h-100 d-flex flex-column justify-content-center align-items-center bg-white"
                                             style="z-index: 10; border-radius: 1rem;">
                                            <div class="spinner-border text-primary"></div>
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

                    {{-- Target Karyawan --}}
                    <div class="col-lg-6 d-flex flex-column">
                        <div class="plain-card flex-fill h-100">
                            <div class="card-body d-flex flex-column">
                                <div class="mb-3">
                                    <h5 class="fw-bold text-dark mb-1">Target Karyawan</h5>
                                    <small class="text-muted">Monitoring performa individu</small>
                                </div>
                                <div class="flex-grow-1 overflow-auto" style="max-height: 420px;">
                                    <div class="d-flex flex-column gap-4" id="contentKPITim">
                                        <div class="text-center py-5">
                                            <div class="spinner-border text-secondary mb-3"></div>
                                            <div class="small text-muted">Memuat data KPI...</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                @elseif (auth()->user()->jabatan === 'Koordinator ITSM')

                    {{-- Target Karyawan --}}
                    <div class="col-lg-6 d-flex flex-column">
                        <div class="plain-card flex-fill h-100">
                            <div class="card-body d-flex flex-column">
                                <div class="mb-3">
                                    <h5 class="fw-bold text-dark mb-1">Target Karyawan</h5>
                                    <small class="text-muted">Monitoring performa individu</small>
                                </div>
                                <div class="flex-grow-1 overflow-auto" style="max-height: 420px;">
                                    <div class="d-flex flex-column gap-4" id="contentKPITim">
                                        <div class="text-center py-5">
                                            <div class="spinner-border text-secondary mb-3"></div>
                                            <div class="small text-muted">Memuat data KPI...</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Data Formulir --}}
                    <div class="col-lg-6 d-flex flex-column">
                        <div class="plain-card flex-fill h-100">
                            <div class="card-body">
                                <h5 class="fw-bold text-dark mb-1">Data Formulir</h5>
                                <p class="text-muted small mb-4">Distribusi status karyawan</p>
                                <div class="row mt-2 align-items-center">
                                    <div class="col-sm-6 position-relative" style="min-height: 200px;">
                                        <div id="loadingFormulir"
                                             class="position-absolute top-0 start-0 w-100 h-100 d-flex flex-column justify-content-center align-items-center bg-white"
                                             style="z-index: 10; border-radius: 1rem;">
                                            <div class="spinner-border text-primary"></div>
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
                @endif
            </div>

        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        let chart1, chart2;

        $(document).ready(function() {
            loadData();
            loadProgressData();
            fetchChartStatistics();

            $(document).on('click', '#jabatanPills .btn', function() {
                const filterValue = $(this).data('filter');
                setJabatanFilter(filterValue);
            });

            $(document).on('click', '#bulanPills .btn', function() {
                const bulanValue = $(this).data('bulan');
                setBulanFilter(bulanValue);
            });

            $(document).on('click', '#refreshChartBtn', function() {
                fetchChartStatistics();
            });
        });

        function loadData() {
            $("#loadingFormulir").removeClass("d-none").addClass("d-flex");
            $("#emptyFormulir").addClass("d-none").removeClass("d-flex");

            $("#loadingPenilaian").removeClass("d-none").addClass("d-flex");
            $("#emptyPenilaian").addClass("d-none").removeClass("d-flex");
            $("#contentPenilaian").hide();

            $.ajax({
                url: "{{ route('databaseKPI.dashboardContent') }}",
                type: "GET",
                dataType: "json",
                success: function(response) {
                    const dataCardFirst = response.dataCard_first || {};
                    const sakit  = dataCardFirst.dataSakit?.totalAbsenSakit  ?? 0;
                    const cuti   = dataCardFirst.dataCuti?.totalAbsenCuti   ?? 0;
                    const izin   = dataCardFirst.dataIzin?.totalAbsenIzin   ?? 0;
                    const aktif  = dataCardFirst.karyawan_aktif             ?? 0;
                    const role   = "{{ auth()->user()->jabatan }}";
                    const labelType = (role === "HRD" || role === "GM" || role === "Direktur Utama") ? "Karyawan" : "Data";

                    $("#content_JK").text(`${aktif} Karyawan`);
                    $("#content_KS").text(`${sakit} ${labelType}`);
                    $("#content_KC").text(`${cuti} ${labelType}`);
                    $("#content_KI").text(`${izin} ${labelType}`);

                    /* ===== Chart Penilaian 360° ===== */
                    const dataChart    = response.dataChartPenilaian || {};
                    const totalSemua   = dataChart.totalSemua ?? 0;
                    const totalDilaksanakan      = dataChart.totalDilaksanakan ?? 0;
                    const totalBelumDilaksanakan = dataChart.totalBelumDilaksanakan ?? 0;

                    $("#title_chartPenilaian").empty().append(
                        totalSemua ? `Penilaian Yang Diadakan : ${totalSemua} Penilaian` : ""
                    );

                    const chartEl1 = document.getElementById("myChart");
                    $("#loadingPenilaian").addClass("d-none").removeClass("d-flex");

                    if (totalSemua > 0) {
                        $("#emptyPenilaian").addClass("d-none").removeClass("d-flex");
                        $("#contentPenilaian").show();

                        if (chartEl1) {
                            const ctx1 = chartEl1.getContext("2d");
                            const gradientBlue = ctx1.createLinearGradient(0, 0, 0, 300);
                            gradientBlue.addColorStop(0, "#8F87F1");
                            gradientBlue.addColorStop(1, "#FED2E2");
                            const gradientWarning = ctx1.createLinearGradient(0, 0, 0, 300);
                            gradientWarning.addColorStop(0, "#fbbf24");
                            gradientWarning.addColorStop(1, "#f59e0b");

                            if (window.chart1) window.chart1.destroy();

                            window.chart1 = new Chart(ctx1, {
                                type: "doughnut",
                                data: {
                                    labels: ["Dilaksanakan", "Belum Dilaksanakan"],
                                    datasets: [{
                                        data: [Number(totalDilaksanakan), Number(totalBelumDilaksanakan)],
                                        backgroundColor: [gradientBlue, gradientWarning],
                                        borderWidth: 0
                                    }]
                                }
                            });
                        }
                    } else {
                        $("#contentPenilaian").hide();
                        $("#emptyPenilaian").removeClass("d-none").addClass("d-flex");
                        if (window.chart1) window.chart1.destroy();
                    }

                    /* ===== Chart Formulir ===== */
                    const DataFormulir  = response.dataFormulir || {};
                    const TotalFormulir = DataFormulir.totalFormulir  ?? 0;
                    const totalRutin    = DataFormulir.totalRutin     ?? 0;
                    const totalProbation= DataFormulir.totalProbation ?? 0;
                    const totalKontrak  = DataFormulir.totalKontrak   ?? 0;

                    const chartEl2 = document.getElementById("doughnutCharthr");
                    $("#loadingFormulir").addClass("d-none").removeClass("d-flex");

                    if (TotalFormulir > 0) {
                        $("#emptyFormulir").addClass("d-none").removeClass("d-flex");

                        if (chartEl2) {
                            const ctx2 = chartEl2.getContext("2d");

                            const gradientPrimary = ctx2.createLinearGradient(0, 0, 0, 200);
                            gradientPrimary.addColorStop(0, "#a78bfa");
                            gradientPrimary.addColorStop(1, "#7c3aed");

                            const gradientInfo = ctx2.createLinearGradient(0, 0, 0, 200);
                            gradientInfo.addColorStop(0, "#38bdf8");
                            gradientInfo.addColorStop(1, "#0284c7");

                            const gradientWarning = ctx2.createLinearGradient(0, 0, 0, 200);
                            gradientWarning.addColorStop(0, "#fbbf24");
                            gradientWarning.addColorStop(1, "#f59e0b");

                            const gradientSuccess = ctx2.createLinearGradient(0, 0, 0, 200);
                            gradientSuccess.addColorStop(0, "#34d399");
                            gradientSuccess.addColorStop(1, "#059669");

                            if (window.chart2) window.chart2.destroy();

                            window.chart2 = new Chart(ctx2, {
                                type: "doughnut",
                                data: {
                                    labels: ["Total", "Rutin", "Probation", "Kontrak"],
                                    datasets: [{
                                        data: [Number(TotalFormulir), Number(totalRutin), Number(totalProbation), Number(totalKontrak)],
                                        backgroundColor: [gradientPrimary, gradientInfo, gradientWarning, gradientSuccess],
                                        borderWidth: 0
                                    }]
                                },
                                options: {
                                    plugins: { legend: { display: false } },
                                    responsive: true,
                                    maintainAspectRatio: false
                                }
                            });
                        }

                        $("#totalFormulir").html(
                            `<p class="fw-bold fs-5 mb-1">${TotalFormulir}</p>
                             <p class="mb-0 small d-flex align-items-center text-muted">
                                <span class="me-2 legend-box" style="background:linear-gradient(135deg,#a78bfa,#7c3aed);"></span> Total
                             </p>`
                        );
                        $("#totalRutin").html(
                            `<p class="fw-bold fs-5 mb-1">${totalRutin}</p>
                             <p class="mb-0 small d-flex align-items-center text-muted">
                                <span class="me-2 legend-box" style="background:linear-gradient(135deg,#38bdf8,#0284c7);"></span> Rutin
                             </p>`
                        );
                        $("#totalProbation").html(
                            `<p class="fw-bold fs-5 mb-1">${totalProbation}</p>
                             <p class="mb-0 small d-flex align-items-center text-muted">
                                <span class="me-2 legend-box" style="background:linear-gradient(135deg,#fbbf24,#f59e0b);"></span> Probation
                             </p>`
                        );
                        $("#totalKontrak").html(
                            `<p class="fw-bold fs-5 mb-1">${totalKontrak}</p>
                             <p class="mb-0 small d-flex align-items-center text-muted">
                                <span class="me-2 legend-box" style="background:linear-gradient(135deg,#34d399,#059669);"></span> Kontrak
                             </p>`
                        );

                    } else {
                        $("#emptyFormulir").removeClass("d-none").addClass("d-flex");
                        if (window.chart2) window.chart2.destroy();
                        $("#totalFormulir, #totalRutin, #totalProbation, #totalKontrak").html(
                            `<span class="text-muted small">-</span>`);
                    }

                    /* ===== Ranking Divisi ===== */
                    const select = $("#select_peringkatPenilaian");
                    select.off("change").empty();
                    const Divisi = response.dataDivisi || [];

                    if (!Divisi.length) {
                        $(".row-ranking").html(
                            '<div class="col-12 text-center text-muted py-5">Belum ada data divisi untuk ditampilkan</div>'
                        );
                        return;
                    }

                    Divisi.forEach(d => select.append(`<option value="${d.divisi}">${d.divisi}</option>`));
                    const defaultDivisi = Divisi[0].divisi;
                    select.val(defaultDivisi);
                    renderPeringkat(defaultDivisi);

                    select.on("change", function() {
                        renderPeringkat($(this).val());
                    });

                    function renderPeringkat(divisi) {
                        let allData = (response.dataRangking || [])
                            .filter(i => i.divisi === divisi)
                            .sort((a, b) => b.total_nilai - a.total_nilai);

                        const dataFiltered = allData.filter(i => Number(i.total_nilai) > 0);
                        const cardContainer = $(".row-ranking");
                        const bodyContentPeringkat = $("#bodyContentPeringkat");

                        cardContainer.empty();
                        bodyContentPeringkat.empty();

                        $("#title_peringkat").text(`Terbaik Divisi ${divisi}`);

                        if (!dataFiltered.length) {
                            cardContainer.append(`
                                <div class="col-12">
                                    <div class="p-4 text-center rounded-4 bg-light">
                                        <img src="{{ asset('template_KPI/dist/assets/images/screenshots/gambar_pencarian.png') }}" width="80%" height="250" style="opacity:.5">
                                        <h6 class="text-muted mt-3">Belum ada karyawan yang memiliki peringkat di divisi ini</h6>
                                    </div>
                                </div>
                            `);
                            return;
                        }

                        const top3 = dataFiltered.slice(0, 3);
                        top3.forEach((item, index) => {
                            const posisi = index + 1;
                            const podiumClass = posisi === 1 ? 'podium-1' : posisi === 2 ? 'podium-2' : 'podium-3';
                            const baseUrl = "{{ asset('storage') }}";
                            const defaultFoto = "{{ asset('template_KPI/dist/assets/images/screenshots/user-profile.jpg') }}";
                            const foto = item.foto ? `${baseUrl}/${item.foto}` : defaultFoto;

                            cardContainer.append(`
                                <div class="col-12 col-sm-6 col-lg-4 text-center mb-4">
                                    <div class="ranking-card p-4 rounded-4 text-white ${podiumClass}" style="box-shadow: 0 6px 20px rgba(0,0,0,.1);">
                                        <img src="${foto}" class="rounded-circle border border-4 border-white my-3" style="width:110px;height:110px;object-fit:cover;">
                                        <h6 class="fw-bold mb-1">${item.nama_karyawan}</h6>
                                        <small class="opacity-75">${item.divisi}</small>
                                    </div>
                                    <h5 class="mt-3 fw-bold text-dark">${posisi}</h5>
                                </div>
                            `);
                        });

                        let lastScore = null;
                        let rank = 0;
                        let shown = 0;

                        allData.forEach(item => {
                            if (item.total_nilai !== lastScore) {
                                rank = shown + 1;
                            }
                            lastScore = item.total_nilai;
                            shown++;

                            bodyContentPeringkat.append(`
                                <div class="d-flex align-items-center mb-3 p-3 rounded-3 plain-card">
                                    <span class="me-3 fw-bold text-primary" style="min-width:28px;">${rank}.</span>
                                    <div class="flex-grow-1">
                                        <div class="fw-bold text-dark">${item.nama_karyawan}</div>
                                        <small class="text-muted">${item.divisi}</small>
                                    </div>
                                    <div class="fw-bold me-3 text-dark">${item.total_nilai}</div>
                                    <div class="progress flex-grow-1" style="max-width:250px;height:10px;background:#f1f5f9;">
                                        <div class="progress-bar" style="width:${item.total_nilai}%;background:linear-gradient(90deg,#6366f1,#a78bfa);"></div>
                                    </div>
                                </div>
                            `);
                        });
                    }
                },
                error: function(xhr, status, error) {
                    $("#loadingFormulir, #loadingPenilaian").addClass("d-none").removeClass("d-flex");
                    $(".row-ranking").html(
                        '<div class="col-12 text-center text-warning py-5">Gagal memuat data dari server</div>'
                    );
                    $("#content_JK, #content_KS, #content_KC, #content_KI").text("-");
                }
            });
        }

        function loadProgressData() {
            $.ajax({
                url: "{{ route('kpi.getProgressDasboard') }}",
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    const data = response.output_1;
                    const contentKPIPersonal = $('#contentKPIPersonal');

                    if (contentKPIPersonal.length > 0) {
                        contentKPIPersonal.empty();
                        if (!data || data.titleGet_data === "Tidak ada data") {
                            contentKPIPersonal.append(`
                                <div class="d-flex flex-column justify-content-center align-items-center text-center h-100 py-5">
                                    <div style="font-size:60px;color:#a78bfa;">≈</div>
                                    <h5 class="fw-semibold mt-3 mb-2 text-dark">Belum Ada Data KPI</h5>
                                    <p class="text-muted small mb-4" style="max-width:320px;">Data performa personal belum tersedia. KPI akan muncul setelah target dan penilaian dibuat.</p>
                                    <span class="badge bg-light text-muted px-3 py-2">Menunggu Data</span>
                                </div>
                            `);
                        } else {
                            let performanceColor = "warning";
                            let performanceIcon  = "∿";

                            if (data.performance_title === "Naik") {
                                performanceColor = "success";
                                performanceIcon  = "↑";
                            } else if (data.performance_title === "Turun") {
                                performanceColor = "warning";
                                performanceIcon  = "↓";
                            }

                            let monthlyHTML = "";
                            data.progress_kpi_perbulan.forEach((item, index) => {
                                const bulanShort = item.bulan.split(" ")[0].substring(0, 3);
                                monthlyHTML += `
                                    <div class="col">
                                        <div class="fw-semibold ${index === data.progress_kpi_perbulan.length - 1 ? 'text-primary fw-bold' : 'text-dark'}">
                                            ${item.nilai}%
                                        </div>
                                        <div class="small text-muted">${bulanShort}</div>
                                    </div>
                                `;
                            });

                            contentKPIPersonal.append(`
                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <div>
                                        <h1 class="fw-bold mb-1 text-dark" data-value="${data.nilai_kpi_anda}">${data.nilai_kpi_anda}%</h1>
                                        <div class="small fw-semibold text-${performanceColor}">
                                            <i class="fas fa-arrow-up me-1"></i>
                                            ${performanceIcon} ${data.performance}% dari bulan lalu
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <div class="small text-muted mt-2">Performa ${data.performance_title}</div>
                                    </div>
                                </div>
                                <div class="mb-4">
                                    <div class="d-flex justify-content-between small text-muted mb-2">
                                        <span>Progress KPI</span>
                                        <span class="fw-semibold text-dark">${data.nilai_kpi_anda}%</span>
                                    </div>
                                    <div class="progress" style="height:8px;background:#f1f5f9;">
                                        <div class="progress-bar progress-animated" data-value="${data.nilai_kpi_anda}"
                                             style="width:0%;background:linear-gradient(90deg,#6366f1,#a78bfa);"></div>
                                    </div>
                                </div>
                                <div class="border-top pt-3 mb-4">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <div class="small text-muted">Deadline</div>
                                            <div class="fw-semibold text-dark">${new Date(data.deadline).toLocaleDateString('id-ID', { day:'numeric', month:'long', year:'numeric' })}</div>
                                        </div>
                                        <span class="badge bg-light text-dark">${data.countdown}</span>
                                    </div>
                                </div>
                                <div class="mt-auto">
                                    <div class="small text-muted mb-3">Riwayat Bulanan</div>
                                    <div class="row text-center g-3">${monthlyHTML}</div>
                                </div>
                            `);

                            setTimeout(() => {
                                $('.progress-animated').each(function() {
                                    let value = $(this).data('value');
                                    $(this).css('width', value + '%');
                                });
                            }, 200);
                        }
                    }

                    /* ===== KPI Tim ===== */
                    let dataContentKPITim = response.output_2 ?? [];
                    const contentKPITim = $('#contentKPITim');

                    if (contentKPITim.length > 0) {
                        contentKPITim.empty();
                        if (dataContentKPITim.length === 0) {
                            contentKPITim.append('<div class="text-center text-muted py-4">Belum ada data tim</div>');
                        } else {
                            dataContentKPITim.forEach(function(item) {
                                let performanceColorTim = "warning";
                                let performanceIconTim  = "∿";

                                if (item.performance === "Naik") {
                                    performanceColorTim = "success";
                                    performanceIconTim  = "↑";
                                } else if (item.performance === "Turun") {
                                    performanceColorTim = "warning";
                                    performanceIconTim  = "↓";
                                }

                                let barColor = "linear-gradient(90deg,#fbbf24,#f59e0b)";
                                if (item.nilaitargetkpi >= 80) barColor = "linear-gradient(90deg,#34d399,#059669)";
                                else if (item.nilaitargetkpi < 50) barColor = "linear-gradient(90deg,#fbbf24,#f59e0b)";

                                contentKPITim.append(`
                                    <div class="mb-3 p-3 rounded-3" style="background:#f8fafc;">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <div>
                                                <h6 class="mb-0 fw-semibold text-dark">${item.nama_karyawan}</h6>
                                                <small class="text-muted">${item.jabatan}</small>
                                            </div>
                                            <div class="text-end">
                                                <h6 class="mb-0 fw-bold text-dark">${item.nilaitargetkpi}%</h6>
                                                <small class="text-${performanceColorTim}">${performanceIconTim} ${item.nilai_performance}% bulan ini</small>
                                            </div>
                                        </div>
                                        <div class="progress" style="height:6px;background:#e2e8f0;">
                                            <div class="progress-bar" style="width:${item.nilaitargetkpi}%;background:${barColor};"></div>
                                        </div>
                                    </div>
                                `);
                            });
                        }
                    }

                    /* ===== Progress Divisi ===== */
                    const dataDivisi = response.output_3 || [];
                    const container  = $('#contentKPIDivisi');
                    const scrollHint = $('#scrollHint');

                    if (container.length > 0) {
                        if (!Array.isArray(dataDivisi) || dataDivisi.length === 0) {
                            container.html(`
                                <div class="d-flex justify-content-center align-items-center w-100 text-muted py-4">
                                    <span class="small">Belum ada data divisi.</span>
                                </div>
                            `);
                            scrollHint.hide();
                        } else {
                            let htmlContent = '';
                            const cardWidth = Math.max(260, Math.min(300, ($(window).width() / 4) - 20));

                            dataDivisi.forEach(item => {
                                const nilai = parseFloat(item.nilai_kpi ?? 0);
                                const nilaiDisplay = nilai.toFixed(1);
                                let colorClass = 'warning';
                                let barColor   = 'linear-gradient(90deg,#fbbf24,#f59e0b)';
                                if (nilai >= 80) {
                                    colorClass = 'success';
                                    barColor   = 'linear-gradient(90deg,#34d399,#059669)';
                                } else if (nilai >= 50) {
                                    colorClass = 'primary';
                                    barColor   = 'linear-gradient(90deg,#6366f1,#a78bfa)';
                                }

                                htmlContent += `
                                    <div class="flex-shrink-0" style="width: ${cardWidth}px;">
                                        <div class="plain-card h-100">
                                            <div class="card-body p-3">
                                                <div class="d-flex justify-content-between align-items-start mb-2">
                                                    <h6 class="fw-semibold mb-1 text-dark lh-sm text-truncate" style="max-width: 65%;" title="${item.divisi}">
                                                        ${item.divisi ?? 'Divisi'}
                                                    </h6>
                                                    <span class="badge bg-${colorClass} bg-opacity-10 text-${colorClass} fw-bold ms-2 py-1 px-2" style="white-space: nowrap;">
                                                        ${nilaiDisplay}%
                                                    </span>
                                                </div>
                                                <div class="progress mb-2" style="height: 8px; border-radius: 4px; background-color: #f1f5f9;">
                                                    <div class="progress-bar" style="width: ${nilai}%; background: ${barColor};" role="progressbar"></div>
                                                </div>
                                                <small class="text-muted fw-medium" style="font-size: 0.8rem;">
                                                    ${item.performance_title || 'Stabil'}
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                `;
                            });

                            container.html(htmlContent);

                            if (container[0].scrollWidth > container[0].clientWidth) {
                                scrollHint.fadeIn();
                            } else {
                                scrollHint.hide();
                            }
                        }
                    }
                },
                error: function(xhr, status, error) {
                    if ($('#contentKPIPersonal').length > 0) $('#contentKPIPersonal').html(
                        '<div class="text-center py-5 text-warning">Gagal memuat data personal</div>');
                    if ($('#contentKPITim').length > 0) $('#contentKPITim').html(
                        '<div class="text-center py-5 text-warning">Gagal memuat data tim</div>');
                    if ($('#contentKPIDivisi').length > 0) $('#contentKPIDivisi').html(
                        '<div class="text-center py-4 text-warning">Gagal memuat data divisi</div>');
                }
            });
        }

        /* ===== Chart Statistics ===== */
        const ChartStatsConfig = {
            API_URL: '/kpi-data/get-statistika',
            currentJabatan: 'all',
            currentBulan: 'all',
            currentTahun: new Date().getFullYear(),
            chartInstance: null
        };

        function fetchChartStatistics() {
            showLoading();
            hideContent();
            hideEmptyState();

            const params = {
                tahun: ChartStatsConfig.currentTahun,
                ...(ChartStatsConfig.currentJabatan !== 'all' && { jabatan: ChartStatsConfig.currentJabatan }),
                ...(ChartStatsConfig.currentBulan   !== 'all' && { bulan:  ChartStatsConfig.currentBulan })
            };

            $.ajax({
                url: ChartStatsConfig.API_URL,
                method: 'GET',
                data: params,
                dataType: 'json',
                success: function(response) {
                    hideLoading();
                    if (response.summary.total_targets === 0) {
                        showEmptyState();
                    } else {
                        renderData(response);
                        showContent();
                    }
                },
                error: function(xhr, status, error) {
                    hideLoading();
                    console.error('Error fetching chart statistics:', error);
                    showEmptyState();
                    $('#emptyState h5').text('Gagal memuat data');
                    $('#emptyState p').text('Terjadi kesalahan: ' + error);
                }
            });
        }

        function renderData(data) {
            $('#overallAverage').text(data.summary.overall_average);
            $('#totalTargets').text(data.summary.total_targets);
            $('#achievedTargets').text(data.summary.achieved_targets);
            $('#completionRate').text(data.summary.completion_rate + '%');
            $('#countCompleted').text(data.summary.completed_targets);
            $('#countInProgress').text(data.summary.in_progress_targets);

            const completedWidth = data.summary.completion_rate;
            const inProgressWidth = 100 - completedWidth;

            $('#progressCompleted').css('width', completedWidth + '%').attr('aria-valuenow', completedWidth);
            $('#progressInProgress').css('width', inProgressWidth + '%').attr('aria-valuenow', inProgressWidth);

            renderChart(data.charts.monthly_trend);
            renderTargetsTable(data.targets_detail);
        }

        function renderChart(monthlyData) {
            const canvas = document.getElementById('mainChart');
            if (!canvas) return;

            const ctx = canvas.getContext('2d');
            const labels = Object.keys(monthlyData);
            const values = Object.values(monthlyData);

            if (ChartStatsConfig.chartInstance) ChartStatsConfig.chartInstance.destroy();

            ChartStatsConfig.chartInstance = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Rata-rata Progress (%)',
                        data: values,
                        borderColor: '#6366f1',
                        backgroundColor: 'rgba(99, 102, 241, 0.1)',
                        borderWidth: 3,
                        tension: 0.4,
                        fill: true,
                        pointBackgroundColor: '#fff',
                        pointBorderColor: '#6366f1',
                        pointBorderWidth: 2,
                        pointRadius: 5,
                        pointHoverRadius: 7
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: true, position: 'top' },
                        tooltip: {
                            backgroundColor: 'rgba(0,0,0,0.8)',
                            padding: 12,
                            cornerRadius: 8,
                            callbacks: {
                                label: function(context) {
                                    return 'Progress: ' + context.parsed.y + '%';
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100,
                            grid: { color: '#f0f0f0' },
                            ticks: { callback: function(value) { return value + '%'; } }
                        },
                        x: { grid: { display: false } }
                    }
                }
            });
        }

        function renderTargetsTable(targets) {
            const tbody = $('#targetsTableBody');
            tbody.empty();

            if (!targets || targets.length === 0) {
                tbody.append('<tr><td colspan="5" class="text-center text-muted py-4">Tidak ada data target</td></tr>');
                return;
            }

            targets.forEach(target => {
                const gap = parseFloat(target.gap) || 0;
                const gapClass = gap >= 0 ? 'text-success' : 'text-warning';
                const gapSign = gap >= 0 ? '+' : '';
                const progressClass = target.progress >= target.target ? 'bg-success' : 'bg-primary';

                const row = `
                    <tr>
                        <td class="ps-3">
                            <div class="fw-bold text-dark">${target.judul || '-'}</div>
                            <small class="text-muted">${target.asistant_route || ''}</small>
                        </td>
                        <td><span class="badge bg-light text-dark border px-2 py-1">${target.jabatan || '-'}</span></td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="progress flex-grow-1" style="height: 8px; border-radius: 4px; background:#f1f5f9;">
                                    <div class="progress-bar ${progressClass}" style="width: ${Math.min(target.progress || 0, 100)}%" role="progressbar"></div>
                                </div>
                                <span class="small fw-bold" style="min-width: 45px;">${target.progress || 0}%</span>
                            </div>
                        </td>
                        <td class="text-muted">${target.target || '-'}</td>
                        <td class="${gapClass} fw-bold">${gapSign}${gap}</td>
                    </tr>
                `;
                tbody.append(row);
            });
        }

        function showLoading()    { $('#chartStatisticsContainer .loading-overlay').removeClass('d-none'); }
        function hideLoading()    { $('#chartStatisticsContainer .loading-overlay').addClass('d-none'); }
        function showContent()    { $('#contentArea').removeClass('d-none'); }
        function hideContent()    { $('#contentArea').addClass('d-none'); }
        function showEmptyState() { $('#emptyState').removeClass('d-none'); }
        function hideEmptyState() {
            $('#emptyState').addClass('d-none');
            $('#emptyState h5').text('Tidak ada data tersedia');
            $('#emptyState p').text('Silakan ubah filter untuk melihat data lainnya');
        }

        function resetFilters() {
            ChartStatsConfig.currentJabatan = 'all';
            ChartStatsConfig.currentBulan   = 'all';
            $('#jabatanPills .btn').removeClass('active').addClass('btn-outline-primary');
            $('#jabatanPills .btn[data-filter="all"]').addClass('active').removeClass('btn-outline-primary');
            $('#bulanPills .btn').removeClass('active').addClass('btn-light');
            $('#bulanPills .btn[data-bulan="all"]').addClass('active').removeClass('btn-light');
            fetchChartStatistics();
        }

        function setJabatanFilter(jabatan) {
            ChartStatsConfig.currentJabatan = jabatan;
            $('#jabatanPills .btn').removeClass('active').addClass('btn-outline-primary');
            $(`#jabatanPills .btn[data-filter="${jabatan}"]`).addClass('active').removeClass('btn-outline-primary');
            fetchChartStatistics();
        }

        function setBulanFilter(bulan) {
            ChartStatsConfig.currentBulan = bulan;
            $('#bulanPills .btn').removeClass('active').addClass('btn-light');
            $(`#bulanPills .btn[data-bulan="${bulan}"]`).addClass('active').removeClass('btn-light');
            fetchChartStatistics();
        }

        function setTahunFilter(tahun) {
            ChartStatsConfig.currentTahun = tahun;
            fetchChartStatistics();
        }
    </script>
@endsection