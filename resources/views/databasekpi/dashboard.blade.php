@extends('databasekpi.berandaKPI')
@section('contentKPI')
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

        .chart-g-blue {
            background-color: linear-gradient(#8F87F1, #C68EFD, #E9A5F1, #FED2E2);
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
            100% {
                transform: rotate(360deg);
            }
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

        .card-podium-1 {
            transform: scale(1.05);
        }

        .card-podium-2 {
            transform: scale(0.95);
        }

        .card-podium-3 {
            transform: scale(0.9);
        }

        .progress-vertical {
            display: flex;
            align-items: flex-end;
            border-radius: 10px;
            overflow: hidden;
        }

        .progress-vertical .progress-bar {
            transition: height 0.6s ease;
            border-radius: 10px 10px 0 0;
        }

        .bar-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin: 0 15px;
        }

        .bar-label {
            margin-top: 8px;
            font-size: 14px;
            font-weight: 500;
            text-align: center;
        }

        .bar-value {
            margin-bottom: 6px;
            font-weight: bold;
            color: #444;
        }

        .legend-box {
            display: inline-block;
            width: 15px;
            height: 15px;
            border-radius: 3px;
        }

        .scroll-wrapper {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            padding-bottom: 10px;
        }

        .scroll-wrapper::-webkit-scrollbar {
            height: 6px;
        }

        .scroll-wrapper::-webkit-scrollbar-thumb {
            background: #ccc;
            border-radius: 10px;
        }

        .chart-container canvas {
            width: 100% !important;
            height: 400px !important;
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <div class="content-wrapper">
        <div class="modal fade" id="loadingModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="loading-spinner"></div>
            </div>
        </div>
        <div class="page-header">
            <h3 class="page-title">
                <span class="page-title-icon bg-gradient-primary text-white me-2">
                    <i class="mdi mdi-home"></i>
                </span> Dashboard
            </h3>
            <nav aria-label="breadcrumb">
                <ul class="breadcrumb">
                    <li class="breadcrumb-item active" aria-current="page">
                        <span></span>Dashboard <i class="mdi mdi-alert-circle-outline icon-sm text-primary align-middle"
                            data-bs-toggle="tooltip" data-bs-placement="top" title="Lihat statistik data seputar KPI."></i>
                    </li>
                </ul>
            </nav>
        </div>
        <div class="row mb-3">
            <div class="col-md-6 col-lg-3 mb-3 stretch-card d-flex">
                <div class="card bg-gradient-danger card-img-holder text-white shadow-sm rounded-4 flex-fill p-4">
                    <img src="{{ asset('template_KPI/dist/assets/images/dashboard/circle.svg') }}" class="card-img-absolute"
                        alt="circle-image" />
                    <h6 class="font-weight-normal mb-3">Jumlah Karyawan Aktif</h6>
                    <h3 class="mb-5">
                        <span id="content_JK">
                            <div class="spinner-border text-light mb-3" role="status" style="width:2rem;height:2rem;">
                            </div>
                        </span>
                    </h3>
                </div>
            </div>

            {{-- Card Sakit --}}
            <div class="col-md-6 col-lg-3 mb-3 stretch-card d-flex">
                <div class="card bg-gradient-info shadow-sm card-img-holder text-white rounded-4 flex-fill p-3"
                    data-bs-toggle="modal" data-bs-target="#modalSakit" style="cursor:pointer;">
                    <img src="{{ asset('template_KPI/dist/assets/images/dashboard/circle.svg') }}" class="card-img-absolute"
                        alt="circle-image" />
                    <h6 class="font-weight-normal mb-3">
                        @if (auth()->user()->jabatan === 'HRD' ||
                                auth()->user()->jabatan === 'GM' ||
                                auth()->user()->jabatan === 'Direktur Utama')
                            Sakit Dalam Semester Ini
                        @else
                            Data Sakit Anda Semester Ini
                        @endif
                    </h6>
                    <h3 class="mb-5">
                        <span id="content_KS">
                            <div class="spinner-border text-light mb-3" role="status" style="width:2rem;height:2rem;">
                            </div>

                        </span>
                    </h3>
                </div>
            </div>

            {{-- Card Izin --}}
            <div class="col-md-6 col-lg-3 mb-3 stretch-card d-flex">
                <div class="card bg-gradient-success shadow-sm card-img-holder text-white rounded-4 flex-fill p-3"
                    data-bs-toggle="modal" data-bs-target="#modalIzin" style="cursor:pointer;">
                    <img src="{{ asset('template_KPI/dist/assets/images/dashboard/circle.svg') }}" class="card-img-absolute"
                        alt="circle-image" />
                    <h6 class="font-weight-normal mb-3">
                        @if (auth()->user()->jabatan === 'HRD' ||
                                auth()->user()->jabatan === 'GM' ||
                                auth()->user()->jabatan === 'Direktur Utama')
                            Izin Dalam Triwulan Ini
                        @else
                            Data Izin Anda Semester Ini
                        @endif
                    </h6>
                    <h3 class="mb-5">
                        <span id="content_KI">
                            <div class="spinner-border text-light mb-3" role="status" style="width:2rem;height:2rem;">
                            </div>

                        </span>
                    </h3>
                </div>
            </div>

            {{-- Card Cuti --}}
            <div class="col-md-6 col-lg-3 mb-3 stretch-card d-flex">
                <div class="card bg-gradient-warning shadow-sm card-img-holder text-white rounded-4 flex-fill p-3"
                    data-bs-toggle="modal" data-bs-target="#modalCuti" style="cursor:pointer;">
                    <img src="{{ asset('template_KPI/dist/assets/images/dashboard/circle.svg') }}" class="card-img-absolute"
                        alt="circle-image" />
                    <h6 class="font-weight-normal mb-3">
                        @if (auth()->user()->jabatan === 'HRD' ||
                                auth()->user()->jabatan === 'GM' ||
                                auth()->user()->jabatan === 'Direktur Utama')
                            Cuti Dalam Triwulan Ini
                        @else
                            Data Cuti Anda Semester Ini
                        @endif
                    </h6>
                    <h3 class="mb-5">
                        <span id="content_KC">
                            <div class="spinner-border text-light mb-3" role="status" style="width:2rem;height:2rem;">
                            </div>

                        </span>
                    </h3>
                </div>
            </div>

            <div class="modal fade" id="modalCuti" tabindex="-1" aria-labelledby="modalCutiLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content modal-themed modal-themed-warning">
                        <div class="modal-header">
                            @if (auth()->user()->jabatan === 'HRD' ||
                                    auth()->user()->jabatan === 'GM' ||
                                    auth()->user()->jabatan === 'Direktur Utama')
                                <h5 class="modal-title fw-bold" id="modalCutiLabel">Daftar Karyawan Cuti</h5>
                            @else
                                <h5 class="modal-title fw-bold" id="modalCutiLabel">Data Cuti Anda</h5>
                            @endif
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="list-group" id="bodyContentModalCuti">
                                <div class="text-center text-white-50">Belum ada data</div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-danger text-dark rounded-pill"
                                data-bs-dismiss="modal">Tutup</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="modalIzin" tabindex="-1" aria-labelledby="modalIzinLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content modal-themed modal-themed-success">
                        <div class="modal-header">
                            @if (auth()->user()->jabatan === 'HRD' ||
                                    auth()->user()->jabatan === 'GM' ||
                                    auth()->user()->jabatan === 'Direktur Utama')
                                <h5 class="modal-title fw-bold" id="modalIzinLabel">Daftar Karyawan Cuti</h5>
                            @else
                                <h5 class="modal-title fw-bold" id="modalIzinLabel">Data Cuti Anda</h5>
                            @endif
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="list-group" id="bodyContentModalIzin">
                                <div class="text-center text-white-50">Belum ada data</div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-danger text-dark rounded-pill"
                                data-bs-dismiss="modal">Tutup</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="modalSakit" tabindex="-1" aria-labelledby="modalSakitLabel"
                aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content modal-themed modal-themed-info">
                        <div class="modal-header">
                            @if (auth()->user()->jabatan === 'HRD' ||
                                    auth()->user()->jabatan === 'GM' ||
                                    auth()->user()->jabatan === 'Direktur Utama')
                                <h5 class="modal-title fw-bold" id="modalSakitLabel">Daftar Karyawan Sakit</h5>
                            @else
                                <h5 class="modal-title fw-bold" id="modalSakitLabel">Data Sakit Anda</h5>
                            @endif
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="list-group" id="bodyContentModalSakit">
                                <div class="text-center text-white-50">Belum ada data</div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-danger text-dark rounded-pill"
                                data-bs-dismiss="modal">Tutup</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row d-flex align-items-stretch">
            @if (auth()->user()->jabatan === 'HRD' ||
                    auth()->user()->jabatan === 'GM' ||
                    auth()->user()->jabatan === 'Direktur Utama')
                <div class="col-md-7 grid-margin d-flex">
                    <div class="card border-0 card-rounded bg-light shadow-sm text-dark w-100 h-100">
                        <div class="card-body d-flex flex-column">
                            <div class="card-header bg-transparent border-0 py-2 px-0">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="container ">
                                        <div class="row">
                                            <div
                                                class="col-sm-3 text-center mb-3 d-flex justify-content-center align-items-center">
                                                <h6 class="mb-0 fw-bold d-flex align-items-center">
                                                    <i class="mdi mdi-trophy-variant mdi-18px"></i>
                                                    <span class="text-nowrap">Terbaik Divisi</span>
                                                </h6>
                                            </div>

                                            <div class="col-sm-8 mb-3">
                                                <form action="{{ route('databaseKPI.downloadDivisi') }}" method="post">
                                                    @csrf
                                                    <div class="d-flex justify-content-center">
                                                        <div class="input-group" style="width: 100%;">
                                                            <select class="form-select bg-white text-dark"
                                                                id="select_peringkatPenilaian" aria-label="divisi select"
                                                                name="divisi">
                                                                <option>Education</option>
                                                            </select>
                                                            <button class="btn btn-danger" type="submit"
                                                                id="btn_exportPDF_rangking" title="Export PDF">
                                                                <i class="fa-solid fa-file-pdf"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <input type="hidden" id="quartal" name="quartal"
                                                        value="Q{{ ceil(date('m') / 3) }}">
                                                    <input type="hidden" id="tahun" name="tahun"
                                                        value="{{ date('Y') }}">
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card-body h-100 px-0">
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
                                    <small class="text-dark-50 fst-italic">
                                        *hasil diambil dari penilaian 360°, tidak termasuk yang lainnya
                                    </small>
                                </div>
                                <div class="mt-4 text-center">
                                    <button type="button" class="btn btn-gradient-primary rounded-pill px-4"
                                        data-bs-toggle="modal" data-bs-target="#modalPeringkatPenilaian360">
                                        lihat semua
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="modal fade" id="modalPeringkatPenilaian360" tabindex="-1"
                            aria-labelledby="modalPeringkatPenilaian360Label" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content modal-themed modal-themed-warning">
                                    <div class="modal-header">
                                        <h5 class="modal-title fw-bold" id="modalPeringkatPenilaian360Label">Peringkat
                                        </h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body" id="bodyContentPeringkat"></div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-danger text-dark rounded-pill"
                                            data-bs-dismiss="modal">Tutup</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-5 grid-margin d-flex flex-column">
                    <div class=" card border-0 shadow-sm card-rounded bg-light text-dark w-100 mb-4 h-100">
                        <div class="card-body">
                            <h4 class="card-title">Chart Penilaian 360°</h4>
                            <p id="title_chartPenilaian" class="text-center">
                                memuat....
                            </p>

                            <div class="doughnutjs-wrapper d-flex justify-content-center">
                                <canvas id="myChart"></canvas>
                            </div>

                            <div id="traffic-chart-legend" class="rounded-legend legend-vertical legend-bottom-left pt-4">
                            </div>
                        </div>
                    </div>
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-4 p-lg-5">

                            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
                                <div>
                                    <h4 class="fw-bold mb-1">Progress Divisi</h4>
                                    <p class="text-muted mb-0 small">Ringkasan performa tahun berjalan</p>
                                </div>
                            </div>

                            <div class="row g-4" id="contentKPIDivisi">
                                <div
                                    class="d-flex flex-column justify-content-center align-items-center h-100 text-center py-5">

                                    <div class="spinner-border text-secondary mb-3" role="status"
                                        style="width:2rem;height:2rem;">
                                    </div>

                                    <div class="small text-muted">
                                        Memuat data KPI...
                                    </div>

                                </div>

                            </div>

                        </div>
                    </div>
                @else
                    <div class="col-md-5 grid-margin d-flex flex-column">
                        <div class=" card border-0 card-rounded bg-light shadow-sm text-dark-100 mb-4 h-100">
                            <div class="card-body">
                                <h4 class="card-title">Chart Penilaian 360°</h4>
                                <p id="title_chartPenilaian" class="text-center">
                                    memuat....
                                </p>

                                <div class="doughnutjs-wrapper d-flex justify-content-center">
                                    <canvas id="myChart"></canvas>
                                </div>

                                <div id="traffic-chart-legend"
                                    class="rounded-legend legend-vertical legend-bottom-left pt-4">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-7 grid-margin d-flex flex-column">
                        <div class="card border-0 shadow-sm h-90">
                            <div class="card-body d-flex flex-column p-4" id="contentKPIPersonal">

                                <div
                                    class="d-flex flex-column justify-content-center align-items-center h-100 text-center py-5">

                                    <div class="spinner-border text-secondary mb-3" role="status"
                                        style="width:2rem;height:2rem;">
                                    </div>

                                    <div class="small text-muted">
                                        Memuat data KPI...
                                    </div>

                                </div>

                            </div>
                        </div>
                    </div>
            @endif
        </div>
        <div class="row d-flex align-items-stretch mb-4">
            @if (auth()->user()->jabatan === 'HRD' ||
                    auth()->user()->jabatan === 'GM' ||
                    auth()->user()->jabatan === 'Direktur Utama')
                <div class="col-lg-6 d-flex flex-column">
                    <div class="card card-rounded shadow-sm bg-light flex-fill">
                        <div class="card-body">
                            <h4 class="card-title card-title-dash">Data Formulir</h4>
                            <div class="pt-3">
                                <div class="row">
                                    <div class="col-sm-6">
                                        <canvas id="doughnutCharthr" height="200"></canvas>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="row doughnut-hr-legend mb-4">
                                            <div class="col-6" id="totalFormulir"></div>
                                            <div class="col-6" id="totalRutin"></div>
                                        </div>
                                        <div class="row doughnut-hr-legend">
                                            <div class="col-6" id="totalProbation"></div>
                                            <div class="col-6" id="totalKontrak"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 d-flex flex-column">
                    <div class="card border-0 shadow-sm bg-light flex-fil">
                        <div class="card-body d-flex flex-column p-4" id="contentKPIPersonal">

                            <div
                                class="d-flex flex-column justify-content-center align-items-center h-100 text-center py-5">

                                <div class="spinner-border text-secondary mb-3" role="status"
                                    style="width:2rem;height:2rem;">
                                </div>

                                <div class="small text-muted">
                                    Memuat data KPI...
                                </div>

                            </div>

                        </div>
                    </div>
                </div>
            @elseif (auth()->user()->jabatan === 'Koordinator ITSM')
                <div class="col-lg-6 d-flex flex-column">
                    <div class="card border-0 shadow-sm w-100 h-100">
                        <div class="card-body d-flex flex-column">

                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div>
                                    <h5 class="fw-bold mb-1">Target Karyawan</h5>
                                    <small class="text-muted">Monitoring performa individu</small>
                                </div>
                            </div>

                            <div class="flex-grow-1 overflow-auto" style="max-height: 420px;">

                                <div class="d-flex flex-column gap-4" id="contentKPITim">
                                    <div
                                        class="d-flex flex-column justify-content-center align-items-center h-100 text-center py-5">

                                        <div class="spinner-border text-secondary mb-3" role="status"
                                            style="width:2rem;height:2rem;">
                                        </div>

                                        <div class="small text-muted">
                                            Memuat data KPI...
                                        </div>

                                    </div>
                                </div>

                            </div>

                        </div>
                    </div>
                </div>
                <div class="col-lg-6 d-flex flex-column">
                    <div class="card card-rounded bg-light shadow-sm flex-fill">
                        <div class="card-body">
                            <h4 class="card-title card-title-dash">Data Formulir</h4>
                            <div class="pt-3">
                                <div class="row">
                                    <div class="col-sm-6">
                                        <canvas id="doughnutCharthr" height="200"></canvas>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="row doughnut-hr-legend mb-4">
                                            <div class="col-6" id="totalFormulir"></div>
                                            <div class="col-6" id="totalRutin"></div>
                                        </div>
                                        <div class="row doughnut-hr-legend">
                                            <div class="col-6" id="totalProbation"></div>
                                            <div class="col-6" id="totalKontrak"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

        </div>
        @if (auth()->user()->jabatan === 'HRD' ||
                auth()->user()->jabatan === 'GM' ||
                auth()->user()->jabatan === 'Direktur Utama')
            <div class="row mb-4 row border d-flex align-items-stretch">
                <div class="col d-flex flex-column">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body d-flex flex-column">

                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div>
                                    <h5 class="fw-bold mb-1">Target Karyawan</h5>
                                    <small class="text-muted">Monitoring performa individu</small>
                                </div>
                            </div>

                            <div class="flex-grow-1 overflow-auto" style="max-height: 420px;">

                                <div class="d-flex flex-column gap-4" id="contentKPITim">
                                    <div
                                        class="d-flex flex-column justify-content-center align-items-center h-100 text-center py-5">

                                        <div class="spinner-border text-secondary mb-3" role="status"
                                            style="width:2rem;height:2rem;">
                                        </div>

                                        <div class="small text-muted">
                                            Memuat data KPI...
                                        </div>

                                    </div>
                                </div>

                            </div>

                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endsection

    @section('script')
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            let chart1, chart2;

            $(document).ready(function () {
                loadData();
                loadProgressData();
            });

            function loadData() {
                $.ajax({
                    url: "{{ route('databaseKPI.dashboardContent') }}",
                    type: "GET",
                    success: function (response) {

                        const dataCardFirst = response.dataCard_first || {};

                        const sakit = dataCardFirst.dataSakit?.totalAbsenSakit ?? 0;
                        const cuti = dataCardFirst.dataCuti?.totalAbsenCuti ?? 0;
                        const izin = dataCardFirst.dataIzin?.totalAbsenIzin ?? 0;
                        const aktif = dataCardFirst.karyawan_aktif ?? 0;

                        const role = "{{ auth()->user()->jabatan }}";

                        const labelType =
                            role === "HRD" ||
                            role === "GM" ||
                            role === "Direktur Utama"
                                ? "Karyawan"
                                : "Data";

                        $("#content_JK").text(`${aktif} Karyawan`);
                        $("#content_KS").text(`${sakit} ${labelType}`);
                        $("#content_KC").text(`${cuti} ${labelType}`);
                        $("#content_KI").text(`${izin} ${labelType}`);

                        const dataChart = response.dataChartPenilaian || {};
                        const totalSemua = dataChart.totalSemua ?? 0;
                        const totalDilaksanakan = dataChart.totalDilaksanakan ?? 0;
                        const totalBelumDilaksanakan = dataChart.totalBelumDilaksanakan ?? 0;

                        $("#title_chartPenilaian").empty().append(
                            totalSemua
                                ? `Penilaian Yang Diadakan : ${totalSemua} Penilaian`
                                : ""
                        );

                        const chartEl1 = document.getElementById("myChart");

                        if (chartEl1) {
                            const ctx1 = chartEl1.getContext("2d");

                            const gradientBlue = ctx1.createLinearGradient(0, 0, 0, 300);
                            gradientBlue.addColorStop(0, "#8F87F1");
                            gradientBlue.addColorStop(1, "#FED2E2");

                            const gradientWarning = ctx1.createLinearGradient(0, 0, 0, 300);
                            gradientWarning.addColorStop(0, "#EA907A");
                            gradientWarning.addColorStop(1, "#AACDBE");

                            if (chart1) chart1.destroy();

                            chart1 = new Chart(ctx1, {
                                type: "doughnut",
                                data: {
                                    labels: ["Dilaksanakan", "Belum Dilaksanakan"],
                                    datasets: [{
                                        data: [
                                            Number(totalDilaksanakan),
                                            Number(totalBelumDilaksanakan)
                                        ],
                                        backgroundColor: [
                                            gradientBlue,
                                            gradientWarning
                                        ]
                                    }]
                                }
                            });
                        }

                        const DataFormulir = response.dataFormulir || {};
                        const TotalFormulir = DataFormulir.totalFormulir ?? 0;
                        const totalRutin = DataFormulir.totalRutin ?? 0;
                        const totalProbation = DataFormulir.totalProbation ?? 0;
                        const totalKontrak = DataFormulir.totalKontrak ?? 0;

                        const chartEl2 = document.getElementById("doughnutCharthr");

                        if (chartEl2) {
                            const ctx2 = chartEl2.getContext("2d");

                            const gradientPrimary = ctx2.createLinearGradient(0, 0, 0, 200);
                            gradientPrimary.addColorStop(0, "#da8cff");
                            gradientPrimary.addColorStop(1, "#9a55ff");

                            const gradientInfo = ctx2.createLinearGradient(0, 0, 0, 200);
                            gradientInfo.addColorStop(0, "#90caf9");
                            gradientInfo.addColorStop(1, "#047edf");

                            const gradientDanger = ctx2.createLinearGradient(0, 0, 0, 200);
                            gradientDanger.addColorStop(0, "#ffbf96");
                            gradientDanger.addColorStop(1, "#fe7096");

                            const gradientSuccess = ctx2.createLinearGradient(0, 0, 0, 200);
                            gradientSuccess.addColorStop(0, "#84d9d2");
                            gradientSuccess.addColorStop(1, "#07cdae");

                            if (chart2) chart2.destroy();

                            chart2 = new Chart(ctx2, {
                                type: "doughnut",
                                data: {
                                    labels: ["Total", "Rutin", "Probation", "Kontrak"],
                                    datasets: [{
                                        data: [
                                            Number(TotalFormulir),
                                            Number(totalRutin),
                                            Number(totalProbation),
                                            Number(totalKontrak)
                                        ],
                                        backgroundColor: [
                                            gradientPrimary,
                                            gradientInfo,
                                            gradientDanger,
                                            gradientSuccess
                                        ],
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

                        $("#totalFormulir").html(`
                            <p class="legend-value">${TotalFormulir}</p>
                            <p class="legend-label d-flex align-items-center">
                                <span class="bg-gradient-primary me-2 legend-box"></span> Total
                            </p>
                        `);

                        $("#totalRutin").html(`
                            <p class="legend-value">${totalRutin}</p>
                            <p class="legend-label d-flex align-items-center">
                                <span class="bg-gradient-info me-2 legend-box"></span> Rutin
                            </p>
                        `);

                        $("#totalProbation").html(`
                            <p class="legend-value">${totalProbation}</p>
                            <p class="legend-label d-flex align-items-center">
                                <span class="bg-gradient-danger me-2 legend-box"></span> Probation
                            </p>
                        `);

                        $("#totalKontrak").html(`
                            <p class="legend-value">${totalKontrak}</p>
                            <p class="legend-label d-flex align-items-center">
                                <span class="bg-gradient-success me-2 legend-box"></span> Kontrak
                            </p>
                        `);

                        const select = $("#select_peringkatPenilaian");
                        select.off("change").empty();

                        const Divisi = response.dataDivisi || [];

                        if (!Divisi.length) return;

                        Divisi.forEach(d =>
                            select.append(
                                `<option value="${d.divisi}">${d.divisi}</option>`
                            )
                        );

                        const defaultDivisi = Divisi[0].divisi;
                        select.val(defaultDivisi);

                        renderPeringkat(defaultDivisi);

                        select.on("change", function () {
                            renderPeringkat($(this).val());
                        });

                        function renderPeringkat(divisi) {

                            let allData = (response.dataRangking || [])
                                .filter(i => i.divisi === divisi)
                                .sort((a, b) => b.total_nilai - a.total_nilai);

                            const dataFiltered = allData.filter(
                                i => Number(i.total_nilai) > 0
                            );

                            const cardContainer = $(".row-ranking");
                            const bodyContentPeringkat = $("#bodyContentPeringkat");

                            cardContainer.empty();
                            bodyContentPeringkat.empty();

                            $("#title_peringkat").text(
                                `Terbaik Divisi ${divisi}`
                            );

                            if (!dataFiltered.length) {
                                cardContainer.append(`
                                    <div class="col-12">
                                        <div class="p-4 text-center rounded-3 bg-light">
                                            <img src="{{ asset('template_KPI/dist/assets/images/screenshots/gambar_pencarian.png') }}" width="80%" height="250" style="opacity:.5">
                                            <h6 class="text-muted">Belum ada karyawan yang memiliki peringkat di divisi ini</h6>
                                        </div>
                                    </div>
                                `);
                                return;
                            }

                            const top3 = dataFiltered.slice(0, 3);

                            top3.forEach((item, index) => {

                                const posisi = index + 1;

                                let gradient =
                                    posisi === 1
                                        ? "linear-gradient(135deg,#f6d365,#fda085)"
                                        : posisi === 2
                                        ? "linear-gradient(135deg,#cfd9df,#e2ebf0)"
                                        : "linear-gradient(135deg,#d1913c,#ffd194)";

                                const baseUrl = "{{ asset('storage') }}";
                                const defaultFoto =
                                    "{{ asset('template_KPI/dist/assets/images/screenshots/user-profile.jpg') }}";

                                const foto = item.foto
                                    ? `${baseUrl}/${item.foto}`
                                    : defaultFoto;

                                cardContainer.append(`
                                    <div class="col-12 col-sm-6 col-lg-4 text-center mb-4">
                                        <div class="ranking-card p-4 rounded-4 text-white" style="background:${gradient}">
                                            <img src="${foto}" class="rounded-circle border border-4 border-white my-3" style="width:110px;height:110px;object-fit:cover;">
                                            <h6 class="fw-bold">${item.nama_karyawan}</h6>
                                            <small class="opacity-75">${item.divisi}</small>
                                        </div>
                                        <h5 class="mt-3 fw-bold">${posisi}</h5>
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
                                    <div class="d-flex align-items-center mb-3 p-2 rounded-3 shadow-sm bg-light">
                                        <span class="me-3 fw-bold">${rank}.</span>
                                        <div class="flex-grow-1">
                                            <div class="fw-bold">${item.nama_karyawan}</div>
                                            <small class="text-muted">${item.divisi}</small>
                                        </div>
                                        <div class="fw-bold me-3">${item.total_nilai}</div>
                                        <div class="progress flex-grow-1" style="max-width:250px;height:12px;">
                                            <div class="progress-bar bg-gradient-info" style="width:${item.total_nilai}%"></div>
                                        </div>
                                    </div>
                                `);
                            });
                        }
                    },
                    error: function () {
                        $("#content_JK").text("0 Karyawan");
                        $("#content_KS").text("0");
                        $("#content_KC").text("0");
                        $("#content_KI").text("0");
                    }
                });
            }

            function loadProgressData() {
                $.ajax({
                    url: "{{ route('kpi.getProgressDasboard') }}",
                    type: 'get',
                    success: function(response) {
                        let dataKPIPersonal = response.output_1

                        const contentKPIPersonal = $('#contentKPIPersonal');
                        contentKPIPersonal.empty();

                        const data = dataKPIPersonal.output_1 ?? null;

                        if (!data || data.titleGet_data === "Tidak ada data") {

                            contentKPIPersonal.append(`
                                <div class="d-flex flex-column justify-content-center align-items-center text-center h-100 py-5">
                                    <div style="font-size:60px;">≈</div>

                                    <h5 class="fw-semibold mt-3 mb-2">
                                        Belum Ada Data KPI
                                    </h5>

                                    <p class="text-muted small mb-4" style="max-width:320px;">
                                        Data performa personal belum tersedia.
                                        KPI akan muncul setelah target dan penilaian dibuat.
                                    </p>

                                    <span class="badge bg-light text-muted px-3 py-2">
                                        Menunggu Data
                                    </span>
                                </div>
                            `);

                        } else {
                            let performanceColor = "warning";
                            let performanceIcon = "∿";

                            if (data.performance_title === "Naik") {
                                performanceColor = "success";
                                performanceIcon = "↑";
                            } else if (data.performance_title === "Turun") {
                                performanceColor = "danger";
                                performanceIcon = "↓";
                            }

                            let monthlyHTML = "";

                            data.progress_kpi_perbulan.forEach((item, index) => {

                                const bulanShort = item.bulan.split(" ")[0].substring(0, 3);

                                monthlyHTML += `
                                    <div class="col">
                                        <div class="fw-semibold ${index === data.progress_kpi_perbulan.length - 1 ? 'text-success fw-bold' : ''}">
                                            ${item.nilai}%
                                        </div>
                                        <div class="small text-muted">${bulanShort}</div>
                                    </div>
                                `;
                            });

                            contentKPIPersonal.append(`
                                <div class="d-flex justify-content-between align-items-center mb-4">

                                    <div>
                                        <h1 class="fw-bold mb-1 counter"
                                            data-value="${data.nilai_kpi_anda}">
                                            ${data.nilai_kpi_anda}%
                                        </h1>

                                        <div class="small fw-semibold text-${performanceColor}">
                                            <i class="fas fa-arrow-up me-1"></i>
                                            ${performanceIcon} ${data.performance}% dari bulan lalu
                                        </div>
                                    </div>

                                    <div class="text-end">
                                        <div class="small text-muted mt-2">
                                            Performa ${data.performance_title}
                                        </div>
                                    </div>

                                </div>

                                <div class="mb-4">
                                    <div class="d-flex justify-content-between small text-muted mb-2">
                                        <span>Progress KPI</span>
                                        <span class="fw-semibold">${data.nilai_kpi_anda}%</span>
                                    </div>

                                    <div class="progress" style="height:8px;">
                                        <div 
                                            class="progress-bar progress-animated bg-${performanceColor}"
                                            data-value="${data.nilai_kpi_anda}"
                                            style="width:0%">
                                        </div>
                                    </div>
                                </div>

                                <div class="border-top pt-3 mb-4">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <div class="small text-muted">Deadline</div>
                                            <div class="fw-semibold">
                                                ${new Date(data.deadline).toLocaleDateString('id-ID',{
                                                    day:'numeric',
                                                    month:'long',
                                                    year:'numeric'
                                                })}
                                            </div>
                                        </div>

                                        <span class="badge bg-light text-dark">
                                            ${data.countdown}
                                        </span>
                                    </div>
                                </div>

                                <div class="mt-auto">
                                    <div class="small text-muted mb-3">
                                        Riwayat Bulanan
                                    </div>

                                    <div class="row text-center g-3">
                                        ${monthlyHTML}
                                    </div>
                                </div>
                            `);

                            setTimeout(() => {
                                $('.progress-animated').each(function() {
                                    let value = $(this).data('value');
                                    $(this).css('width', value + '%');
                                });
                            }, 200);
                        }

                        let dataContentKPITim = response.output_2 ?? [];

                        const contentKPITim = $('#contentKPITim');
                        contentKPITim.empty();

                        dataContentKPITim.forEach(function(item) {

                            let performanceColorTim = "warning";
                            let performanceIconTim = "∿";

                            if (item.performance === "Naik") {
                                performanceColorTim = "success";
                                performanceIconTim = "↑";
                            } else if (item.performance === "Turun") {
                                performanceColorTim = "danger";
                                performanceIconTim = "↓";
                            }

                            let progressColor = "bg-warning";

                            if (item.nilaitargetkpi >= 80) {
                                progressColor = "bg-success";
                            } else if (item.nilaitargetkpi < 50) {
                                progressColor = "bg-danger";
                            }

                            contentKPITim.append(`
                                <div class="mb-4">

                                    <div class="d-flex justify-content-between align-items-start mb-2">

                                        <div>
                                            <h6 class="mb-0 fw-semibold">
                                                ${item.nama_karyawan}
                                            </h6>
                                            <small class="text-muted">
                                                ${item.jabatan}
                                            </small>
                                        </div>

                                        <div class="text-end">
                                            <h6 class="mb-0 fw-bold">
                                                ${item.nilaitargetkpi}%
                                            </h6>

                                            <small class="text-${performanceColorTim}">
                                                ${performanceIconTim}
                                                ${item.nilai_performance}% bulan ini
                                            </small>
                                        </div>

                                    </div>

                                    <div class="progress" style="height:6px;">
                                        <div 
                                            class="progress-bar ${progressColor}"
                                            style="width:${item.nilaitargetkpi}%">
                                        </div>
                                    </div>

                                </div>
                            `);
                        });


                        let datacontentKPIDivisi = response.output_3;

                        const contentKPIDivisi = $('#contentKPIDivisi');
                        contentKPIDivisi.empty();

                        datacontentKPIDivisi.forEach(item => {

                            let nilai = parseFloat(item.nilai_kpi ?? 0).toFixed(0);
                            let performance = parseFloat(item.performance ?? 0).toFixed(0);

                            let colorClass = 'secondary';
                            let icon = '→';

                            if (performance > 0) {
                                colorClass = 'success';
                                icon = '↑';
                            } else if (performance < 0) {
                                colorClass = 'danger';
                                icon = '↓';
                            } else {
                                colorClass = 'warning';
                                icon = '→';
                            }

                            const html = `
                                <div class="col-12 col-md-6">
                                    <div class="border rounded-4 p-4 h-100 bg-light">

                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <div>
                                                <h6 class="fw-semibold mb-1">
                                                    ${item.divisi ?? '-'}
                                                </h6>
                                                <small class="text-muted">
                                                    ${item.performance_title ?? '-'}
                                                </small>
                                            </div>

                                            <div class="text-end">
                                                <h2 class="fw-bold mb-0 counter" data-value="${nilai}">
                                                ${item.nilai_kpi}%
                                                </h2>
                                            </div>
                                        </div>

                                        <div class="progress" style="height:6px;">
                                            <div 
                                                class="progress-bar bg-${colorClass} progress-animated"
                                                data-value="${nilai}"
                                                style="width:${item.nilai_kpi}%">
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            `;

                            contentKPIDivisi.append(html);
                        });


                    }
                });
            }
        </script>
        <script>
            const ctx = document.getElementById('tradingChart').getContext('2d');

            // Gradient biru lembut
            let gradient = ctx.createLinearGradient(0, 0, 0, 300);
            gradient.addColorStop(0, 'rgba(59,130,246,0.4)');
            gradient.addColorStop(1, 'rgba(59,130,246,0)');

            // Fungsi buat data dummy random
            function generateRandomData(points) {
                let data = [];
                for (let i = 0; i < points; i++) {
                    data.push((Math.random() * 100 + 20).toFixed(2)); // angka 20–120
                }
                return data;
            }

            function generateLabels(type, points) {
                switch (type) {
                    case 'weekly':
                        return ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'];
                    case 'monthly':
                        return Array(points).fill('').map((_, i) => (i === Math.floor(points / 2) ? 'Oktober' : ''));
                    case 'quarterly':
                        return Array(points).fill('').map((_, i) => {
                            if (i === 10) return 'Q1';
                            if (i === 40) return 'Q2';
                            if (i === 70) return 'Q3';
                            if (i === 90) return 'Q4';
                            return '';
                        });
                    case 'yearly':
                        return Array(points).fill('').map((_, i) => (i === Math.floor(points / 2) ? '2025' : ''));
                }
            }

            let chart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: generateLabels('weekly', 7),
                    datasets: [{
                        label: 'Nilai Kinerja',
                        data: generateRandomData(7),
                        fill: true,
                        backgroundColor: gradient,
                        borderColor: '#3b82f6',
                        borderWidth: 2,
                        pointRadius: 0,
                        tension: 0.4,
                        hoverBorderWidth: 3,
                        hoverBorderColor: '#60a5fa',
                    }]
                },
                options: {
                    responsive: true,
                    interaction: {
                        mode: 'index',
                        intersect: false
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: '#1e293b',
                            titleColor: '#fff',
                            bodyColor: '#60a5fa',
                            displayColors: false,
                            padding: 10,
                            callbacks: {
                                title: function(context) {
                                    return 'Data ke-' + context[0].label;
                                },
                                label: function(context) {
                                    return 'Nilai: ' + context.parsed.y;
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                color: '#94a3b8',
                                maxRotation: 0,
                                autoSkip: true
                            }
                        },
                        y: {
                            grid: {
                                color: 'rgba(148,163,184,0.1)'
                            },
                            ticks: {
                                color: '#94a3b8'
                            }
                        }
                    }
                }
            });

            function updateChart(type, event) {
                document.querySelectorAll('.filter-buttons button').forEach(btn => btn.classList.remove('active'));
                event.target.classList.add('active');

                let points;
                switch (type) {
                    case 'monthly':
                        points = 31;
                        break;
                    case 'quarterly':
                        points = 93;
                        break;
                }

                chart.data.labels = generateLabels(type, points);
                chart.data.datasets[0].data = generateRandomData(points);
                chart.update();
            }
        </script>
    @endsection
