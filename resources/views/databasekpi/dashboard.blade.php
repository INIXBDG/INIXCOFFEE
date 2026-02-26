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
                            <div class="loader">
                                <div class="bubble"></div>
                                <div class="bubble"></div>
                                <div class="bubble"></div>
                                <div class="bubble"></div>
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
                            <div class="loader">
                                <div class="bubble"></div>
                                <div class="bubble"></div>
                                <div class="bubble"></div>
                                <div class="bubble"></div>
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
                            <div class="loader">
                                <div class="bubble"></div>
                                <div class="bubble"></div>
                                <div class="bubble"></div>
                                <div class="bubble"></div>
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
                            <div class="loader">
                                <div class="bubble"></div>
                                <div class="bubble"></div>
                                <div class="bubble"></div>
                                <div class="bubble"></div>
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
                                <span class="badge bg-light text-dark fw-semibold px-3 py-2 mt-3 mt-md-0">
                                    Tahun 2026
                                </span>
                            </div>

                            <div class="row g-4">

                                <div class="col-12 col-md-6">
                                    <div class="border rounded-4 p-4 h-100 bg-light">

                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <div>
                                                <h6 class="fw-semibold mb-1">Marketing</h6>
                                                <small class="text-muted">Meningkat bulan ini</small>
                                            </div>
                                            <div class="text-end">
                                                <h2 class="fw-bold mb-0 counter" data-value="78">0%</h2>
                                                <small class="text-success fw-semibold">
                                                    ↑ +8%
                                                </small>
                                            </div>
                                        </div>

                                        <div class="progress" style="height:6px;">
                                            <div class="progress-bar bg-success progress-animated" data-value="78"
                                                style="width:0%">
                                            </div>
                                        </div>

                                    </div>
                                </div>

                                <div class="col-12 col-md-6">
                                    <div class="border rounded-4 p-4 h-100 bg-light">

                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <div>
                                                <h6 class="fw-semibold mb-1">Operasional</h6>
                                                <small class="text-muted">Sedikit penurunan</small>
                                            </div>
                                            <div class="text-end">
                                                <h2 class="fw-bold mb-0 counter" data-value="52">0%</h2>
                                                <small class="text-danger fw-semibold">
                                                    ↓ -5%
                                                </small>
                                            </div>
                                        </div>

                                        <div class="progress" style="height:6px;">
                                            <div class="progress-bar bg-danger progress-animated" data-value="52"
                                                style="width:0%">
                                            </div>
                                        </div>

                                    </div>
                                </div>

                                <div class="col-12 col-md-6">
                                    <div class="border rounded-4 p-4 h-100 bg-light">

                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <div>
                                                <h6 class="fw-semibold mb-1">Keuangan</h6>
                                                <small class="text-muted">Stagnan</small>
                                            </div>
                                            <div class="text-end">
                                                <h2 class="fw-bold mb-0 counter" data-value="65">0%</h2>
                                                <small class="text-warning fw-semibold">
                                                    → 0%
                                                </small>
                                            </div>
                                        </div>

                                        <div class="progress" style="height:6px;">
                                            <div class="progress-bar bg-warning progress-animated" data-value="65"
                                                style="width:0%">
                                            </div>
                                        </div>

                                    </div>
                                </div>

                                <div class="col-12 col-md-6">
                                    <div class="border rounded-4 p-4 h-100 bg-light">

                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <div>
                                                <h6 class="fw-semibold mb-1">HRD</h6>
                                                <small class="text-muted">Performa sangat baik</small>
                                            </div>
                                            <div class="text-end">
                                                <h2 class="fw-bold mb-0 counter" data-value="90">0%</h2>
                                                <small class="text-primary fw-semibold">
                                                    ↑ +12%
                                                </small>
                                            </div>
                                        </div>

                                        <div class="progress" style="height:6px;">
                                            <div class="progress-bar bg-primary progress-animated" data-value="90"
                                                style="width:0%">
                                            </div>
                                        </div>

                                    </div>
                                </div>

                            </div>

                        </div>
                    </div>
                @else
                    <div class="col-md-5 grid-margin d-flex flex-column">
                        <div class=" card border-0 card-rounded bg-light shadow-sm text-dark w-100 mb-4 h-100">
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
            @endif
        </div>
        <div class="row mb-4">
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
                <div class="col d-flex">
                    <div class="card border-0 shadow-sm w-100 h-100">
                        <div class="card-body d-flex flex-column">

                            <!-- Header -->
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div>
                                    <h5 class="fw-bold mb-1">Target Karyawan</h5>
                                    <small class="text-muted">Monitoring performa individu</small>
                                </div>
                                <small class="text-muted">Updated: Juli 2026</small>
                            </div>

                            <!-- Scrollable Area -->
                            <div class="flex-grow-1 overflow-auto" style="max-height: 420px;">

                                <div class="d-flex flex-column gap-4">

                                    <!-- Item -->
                                    <div>
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <div>
                                                <h6 class="mb-0 fw-semibold">Xepi</h6>
                                                <small class="text-muted">Finance & Accounting</small>
                                            </div>
                                            <div class="text-end">
                                                <h6 class="mb-0 fw-bold">78%</h6>
                                                <small class="text-success">+8% bulan ini</small>
                                            </div>
                                        </div>
                                        <div class="progress" style="height:5px;">
                                            <div class="progress-bar bg-dark" style="width:78%"></div>
                                        </div>
                                    </div>

                                    <div>
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <div>
                                                <h6 class="mb-0 fw-semibold">Riffa</h6>
                                                <small class="text-muted">Finance & Accounting</small>
                                            </div>
                                            <div class="text-end">
                                                <h6 class="mb-0 fw-bold">62%</h6>
                                                <small class="text-danger">-3% bulan ini</small>
                                            </div>
                                        </div>
                                        <div class="progress" style="height:5px;">
                                            <div class="progress-bar bg-dark" style="width:62%"></div>
                                        </div>
                                    </div>

                                    <div>
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <div>
                                                <h6 class="mb-0 fw-semibold">Amanda</h6>
                                                <small class="text-muted">HRD</small>
                                            </div>
                                            <div class="text-end">
                                                <h6 class="mb-0 fw-bold">60%</h6>
                                                <small class="text-muted">0% perubahan</small>
                                            </div>
                                        </div>
                                        <div class="progress" style="height:5px;">
                                            <div class="progress-bar bg-dark" style="width:60%"></div>
                                        </div>
                                    </div>

                                    <div>
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <div>
                                                <h6 class="mb-0 fw-semibold">Security</h6>
                                                <small class="text-muted">Security Division</small>
                                            </div>
                                            <div class="text-end">
                                                <h6 class="mb-0 fw-bold">56%</h6>
                                                <small class="text-danger">-5% bulan ini</small>
                                            </div>
                                        </div>
                                        <div class="progress" style="height:5px;">
                                            <div class="progress-bar bg-dark" style="width:56%"></div>
                                        </div>
                                    </div>

                                </div>

                            </div>

                        </div>
                    </div>
                </div>
            @else
                <div class="col-lg-6 d-flex flex-column">
                    <div class="container-fluid">
                        <div class="card border-0 shadow-sm bg-light rounded" style="padding: 24px;">
                            <div class="text-center mb-3">
                                <div style="font-size: 3rem; font-weight: 700; color: #333;">85%</div>
                                <div class="text-success" style="font-size: 0.9rem; font-weight: 500;">
                                    <i class="fas fa-arrow-up"></i> +2.5% dari bulan lalu
                                </div>
                                <div class="mt-2" style="font-size: 0.85rem; color: #666;">
                                    Pencapaian Anda meningkat secara konsisten.
                                </div>
                            </div>

                            <div class="mt-4 mb-4"
                                style="border: 2px solid #3498db; border-radius: 8px; padding: 16px; background-color: #f8fafc;">
                                <div>
                                    <span style="font-weight: 600; color: #2c3e50;">Deadline:</span>
                                    <span style="font-size: 0.9rem; color: #555;">31 Oktober 2025</span>
                                </div>
                            </div>
                            <div class="nt-4">
                                <div class="row">
                                    <div class="col-lg-4">
                                        <span>Progress KPI anda </span>

                                    </div>
                                    <div class="col-lg-8">
                                        <div class="progress">
                                            <div class="progress-bar bg-gradient-success" role="progressbar"
                                                style="width: 85%" aria-valuenow="50" aria-valuemin="0"
                                                aria-valuemax="100"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4">
                                <div class="text-secondary" style="font-size: 0.85rem; margin-bottom: 10px;">Riwayat
                                    Bulanan</div>
                                <div>
                                    <span style="display: inline-block; margin-right: 30px; font-size: 0.9rem;">Jul:
                                        78%</span>
                                    <span style="display: inline-block; margin-right: 30px; font-size: 0.9rem;">Agt:
                                        80%</span>
                                    <span style="display: inline-block; margin-right: 30px; font-size: 0.9rem;">Sep:
                                        82%</span>
                                    <span style="display: inline-block; font-size: 0.9rem;">Okt: 85%</span>
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
            <div class="col-lg-6 d-flex">
                <div class="card border-0 shadow-sm w-100 h-100">
                    <div class="card-body d-flex flex-column p-4">

                        <!-- TOP SECTION -->
                        <div class="d-flex justify-content-between align-items-center mb-4">

                            <!-- KPI VALUE -->
                            <div>
                                <h1 class="fw-bold mb-1 counter" data-value="85">0%</h1>
                                <div class="small text-success fw-semibold">
                                    <i class="fas fa-arrow-up me-1"></i> +2.5% dari bulan lalu
                                </div>
                            </div>

                            <!-- STATUS BADGE -->
                            <div class="text-end">
                                <span class="badge bg-success-subtle text-success fw-semibold px-3 py-2">
                                    On Track
                                </span>
                                <div class="small text-muted mt-2">
                                    Performa stabil & meningkat
                                </div>
                            </div>
                        </div>

                        <!-- PROGRESS SECTION -->
                        <div class="mb-4">
                            <div class="d-flex justify-content-between small text-muted mb-2">
                                <span>Progress KPI</span>
                                <span class="fw-semibold">85%</span>
                            </div>
                            <div class="progress" style="height:8px;">
                                <div class="progress-bar bg-dark progress-animated" data-value="85" style="width:0%">
                                </div>
                            </div>
                        </div>

                        <!-- DEADLINE SECTION -->
                        <div class="border-top pt-3 mb-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="small text-muted">Deadline</div>
                                    <div class="fw-semibold">31 Oktober 2025</div>
                                </div>
                                <span class="badge bg-light text-dark">
                                    32 Hari Lagi
                                </span>
                            </div>
                        </div>

                        <!-- MONTHLY HISTORY -->
                        <div class="mt-auto">
                            <div class="small text-muted mb-3">Riwayat Bulanan</div>

                            <div class="row text-center g-3">

                                <div class="col">
                                    <div class="fw-semibold">78%</div>
                                    <div class="small text-muted">Jul</div>
                                </div>

                                <div class="col">
                                    <div class="fw-semibold">80%</div>
                                    <div class="small text-muted">Agt</div>
                                </div>

                                <div class="col">
                                    <div class="fw-semibold">82%</div>
                                    <div class="small text-muted">Sep</div>
                                </div>

                                <div class="col">
                                    <div class="fw-bold text-success">85%</div>
                                    <div class="small text-muted">Okt</div>
                                </div>

                            </div>
                        </div>

                    </div>
                </div>
            </div>


        </div>
    @endsection

    @section('script')
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            document.addEventListener("DOMContentLoaded", function() {

                document.querySelectorAll(".progress-animated").forEach(bar => {
                    const value = bar.getAttribute("data-value");
                    setTimeout(() => {
                        bar.style.width = value + "%";
                    }, 300);
                });

                document.querySelectorAll(".counter").forEach(counter => {
                    const target = +counter.getAttribute("data-value");
                    let count = 0;

                    const update = () => {
                        if (count < target) {
                            count++;
                            counter.innerText = count + "%";
                            setTimeout(update, 12);
                        } else {
                            counter.innerText = target + "%";
                        }
                    };

                    update();
                });

            });

            let chart1, chart2;

            $(document).ready(function() {
                loadData();
                loadProgressData();
            });

            function loadData() {
                $.ajax({
                    url: "{{ route('databaseKPI.dashboardContent') }}",
                    type: 'GET',
                    success: function(response) {
                        const dataChart = response.dataChartPenilaian ?? {};
                        const totalSemua = dataChart.totalSemua ?? 0;
                        const totalDilaksanakan = dataChart.totalDilaksanakan ?? 0;
                        const totalBelumDilaksanakan = dataChart.totalBelumDilaksanakan ?? 0;

                        const DataFormulir = response.dataFormulir ?? {};
                        const TotalFormulir = DataFormulir.totalFormulir ?? 0;
                        const totalRutin = DataFormulir.totalRutin ?? 0;
                        const totalProbation = DataFormulir.totalProbation ?? 0;
                        const totalKontrak = DataFormulir.totalKontrak ?? 0;

                        const title_chartPenilaian = $('#title_chartPenilaian');
                        title_chartPenilaian.empty();
                        if (totalSemua) {
                            title_chartPenilaian.append(`Penilaian Yang Diadakan : ${totalSemua} Penilaian`);
                        }

                        const ctx1 = document.getElementById('myChart').getContext('2d');
                        const gradientBlue = ctx1.createLinearGradient(0, 0, 0, 300);
                        gradientBlue.addColorStop(0, '#8F87F1');
                        gradientBlue.addColorStop(0.33, '#C68EFD');
                        gradientBlue.addColorStop(0.66, '#E9A5F1');
                        gradientBlue.addColorStop(1, '#FED2E2');

                        const gradientWarning = ctx1.createLinearGradient(0, 0, 0, 300);
                        gradientWarning.addColorStop(0, '#EA907A');
                        gradientWarning.addColorStop(0.33, '#FBC687');
                        gradientWarning.addColorStop(0.66, '#F4F7C5');
                        gradientWarning.addColorStop(1, '#AACDBE');

                        const data1 = {
                            labels: ['Dilaksanakan', 'Belum Dilaksanakan'],
                            datasets: [{
                                label: 'Total Data',
                                data: [totalDilaksanakan, totalBelumDilaksanakan],
                                backgroundColor: [gradientBlue, gradientWarning],
                                hoverOffset: 4
                            }]
                        };

                        const config1 = {
                            type: 'doughnut',
                            data: data1
                        };
                        if (chart1) chart1.destroy();
                        chart1 = new Chart(ctx1, config1);

                        const ctx2 = document.getElementById('doughnutCharthr').getContext('2d');
                        const gradientPrimary = ctx2.createLinearGradient(0, 0, 0, 200);
                        gradientPrimary.addColorStop(0, '#da8cff');
                        gradientPrimary.addColorStop(1, '#9a55ff');

                        const gradientInfo = ctx2.createLinearGradient(0, 0, 0, 200);
                        gradientInfo.addColorStop(0, '#90caf9');
                        gradientInfo.addColorStop(1, '#047edf');

                        const gradientDanger = ctx2.createLinearGradient(0, 0, 0, 200);
                        gradientDanger.addColorStop(0, '#ffbf96');
                        gradientDanger.addColorStop(1, '#fe7096');

                        const gradientSuccess = ctx2.createLinearGradient(0, 0, 0, 200);
                        gradientSuccess.addColorStop(0, '#84d9d2');
                        gradientSuccess.addColorStop(1, '#07cdae');

                        if (chart2) chart2.destroy();
                        chart2 = new Chart(ctx2, {
                            type: 'doughnut',
                            data: {
                                labels: ['Total', 'Rutin', 'Probation', 'Kontrak'],
                                datasets: [{
                                    data: [TotalFormulir, totalRutin, totalProbation, totalKontrak],
                                    backgroundColor: [gradientPrimary, gradientInfo, gradientDanger,
                                        gradientSuccess
                                    ],
                                    borderWidth: 0
                                }]
                            },
                            options: {
                                plugins: {
                                    legend: {
                                        display: false
                                    }
                                },
                                responsive: true,
                                maintainAspectRatio: false,
                            }
                        });

                        const divTotalFormulir = $('#totalFormulir');
                        const divTotalRutin = $('#totalRutin');
                        const divTotalProbation = $('#totalProbation');
                        const divTotalKontrak = $('#totalKontrak');

                        divTotalFormulir.append(`
                    <p class="legend-value">${TotalFormulir}</p>
                        <p class="legend-label align-items-center d-flex">
                        <span class="bg-gradient-primary me-2 legend-box"></span> Total
                    </p>                
                `);
                        divTotalRutin.append(`
                    <p class="legend-value">${totalRutin}</p>
                        <p class="legend-label align-items-center d-flex">
                        <span class="bg-gradient-info me-2 legend-box"></span> Rutin
                    </p>                
                `);
                        divTotalProbation.append(`
                    <p class="legend-value">${totalProbation}</p>
                        <p class="legend-label align-items-center d-flex">
                        <span class="bg-gradient-danger me-2 legend-box"></span> Probation
                    </p>                
                `);
                        divTotalKontrak.append(`
                    <p class="legend-value">${totalKontrak}</p>
                        <p class="legend-label align-items-center d-flex">
                        <span class="bg-gradient-success me-2 legend-box"></span> Kontrak
                    </p>                
                `);

                        const content_JK = $('#content_JK');
                        const content_KS = $('#content_KS');
                        const content_KC = $('#content_KC');
                        const content_KI = $('#content_KI');
                        const title_peringkat = $('#title_peringkat');
                        const bodyContentPeringkat = $('#bodyContentPeringkat');
                        const select_peringkatPenilaian = $('#select_peringkatPenilaian');
                        const contentModalBodyKC = $('#bodyContentModalCuti');
                        const contentModalBodyKI = $('#bodyContentModalIzin');
                        const contentModalBodyKS = $('#bodyContentModalSakit');

                        const dataCuti = response.dataCard_first?.dataCuti?.dataCuti ?? [];
                        const dataIzin = response.dataCard_first?.dataIzin?.dataIzin ?? [];
                        const dataSakit = response.dataCard_first?.dataSakit?.dataSakit ?? [];
                        const dataCardFirst = response.dataCard_first ?? {};

                        content_JK.text(`${dataCardFirst.karyawan_aktif ?? 0} Karyawan`);
                        const idUserLogin = "{{ auth()->user()->jabatan }}";

                        if (idUserLogin === 'HRD' || idUserLogin === 'GM' || idUserLogin === 'Direktur Utama') {
                            content_KS.text(`${dataCardFirst.dataSakit?.totalAbsenSakit ?? 0} Karyawan`);
                            content_KC.text(`${dataCardFirst.dataCuti?.totalAbsenCuti ?? 0} Karyawan`);
                            content_KI.text(`${dataCardFirst.dataIzin?.totalAbsenIzin ?? 0} Karyawan`);
                        } else {
                            content_KS.text(`${dataCardFirst.dataSakit?.totalAbsenSakit ?? 0} Data`);
                            content_KC.text(`${dataCardFirst.dataCuti?.totalAbsenCuti ?? 0} Data`);
                            content_KI.text(`${dataCardFirst.dataIzin?.totalAbsenIzin ?? 0} Data`);
                        }

                        const bgClasses = ["cl-yellow", "cl-red", "cl-green", "cl-blue", "cl-grey"];

                        contentModalBodyKC.empty();
                        if (dataCuti.length === 0) {
                            const idUserLogin = "{{ auth()->user()->jabatan }}";
                            let textUser = '';

                            if (idUserLogin === 'HRD' || idUserLogin === 'GM' || idUserLogin === 'Direktur Utama') {
                                textUser = 'Tidak ada karyawan yang cuti di quartal ini.';
                            } else {
                                textUser = 'Tidak ada data cuti anda di quartal ini.';
                            }

                            contentModalBodyKC.append(`
                        <div class="list-group-item border-0 d-flex align-items-center rounded-3 shadow-sm mb-3 p-3">
                            ${textUser}
                        </div>
                    `);

                        } else {
                            let accordionHtml = `<div class="accordion" id="accordionCuti">`;
                            const groupedCuti = {};
                            dataCuti.forEach(item => {
                                if (!groupedCuti[item.namaKaryawan]) {
                                    groupedCuti[item.namaKaryawan] = {
                                        divisi: item.divisi,
                                        records: []
                                    };
                                }
                                groupedCuti[item.namaKaryawan].records.push({
                                    alasan: item.alasan,
                                    tanggalAwal: item.tanggalAwal,
                                    tanggalAkhir: item.tanggalAkhir
                                });
                            });
                            let index = 0;
                            for (const [nama, detail] of Object.entries(groupedCuti)) {
                                let words = nama.split(" ");
                                let initials = words[0].charAt(0).toUpperCase() + (words[1] ? words[1].charAt(0)
                                    .toUpperCase() : "");
                                let randomBg = bgClasses[Math.floor(Math.random() * bgClasses.length)];
                                let alasanList = detail.records.map((rec, idx) =>
                                    `<li><strong>Cuti ${idx+1} (${rec.tanggalAwal} - ${rec.tanggalAkhir}):</strong> ${rec.alasan}</li>`
                                ).join("");
                                accordionHtml += `
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingCuti${index}">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseCuti${index}" aria-expanded="false" aria-controls="collapseCuti${index}">
                                    ${nama} - ${detail.divisi}
                                </button>
                            </h2>
                            <div id="collapseCuti${index}" class="accordion-collapse collapse" aria-labelledby="headingCuti${index}" data-bs-parent="#accordionCuti">
                                <div class="accordion-body">
                                    <div class="d-flex align-items-start">
                                        <div class="avatar ${randomBg} text-white rounded-circle d-flex justify-content-center align-items-center me-3" style="width:40px; height:40px; font-weight:bold;">${initials}</div>
                                        <div>
                                            <h6 class="mb-0 fw-semibold">${nama}</h6>
                                            <small class="text-muted">${detail.divisi}</small>
                                            <ul class="mt-2 mb-0">${alasanList}</ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>`;
                                index++;
                            }
                            accordionHtml += `</div>`;
                            contentModalBodyKC.append(accordionHtml);
                        }

                        contentModalBodyKS.empty();
                        if (dataSakit.length === 0) {
                            const idUserLogin = "{{ auth()->user()->jabatan }}";
                            let textUser = '';

                            if (idUserLogin === 'HRD' || idUserLogin === 'GM' || idUserLogin === 'Direktur Utama') {
                                textUser = 'Tidak ada karyawan yang sakit di quartal ini.';
                            } else {
                                textUser = 'Tidak ada data sakit anda di quartal ini.';
                            }

                            contentModalBodyKS.append(
                                `<div class="list-group-item border-0 d-flex align-items-center rounded-3 shadow-sm mb-3 p-3">${textUser}</div>`
                            );
                        } else {
                            let accordionHtml = `<div class="accordion" id="accordionSakit">`;
                            const groupedSakit = {};
                            dataSakit.forEach(item => {
                                if (!groupedSakit[item.namaKaryawan]) {
                                    groupedSakit[item.namaKaryawan] = {
                                        divisi: item.divisi,
                                        records: []
                                    };
                                }
                                groupedSakit[item.namaKaryawan].records.push({
                                    alasan: item.alasan,
                                    tanggalAwal: item.tanggalAwal,
                                    tanggalAkhir: item.tanggalAkhir
                                });
                            });
                            let index = 0;
                            for (const [nama, detail] of Object.entries(groupedSakit)) {
                                let words = nama.split(" ");
                                let initials = words[0].charAt(0).toUpperCase() + (words[1] ? words[1].charAt(0)
                                    .toUpperCase() : "");
                                let randomBg = bgClasses[Math.floor(Math.random() * bgClasses.length)];
                                let alasanList = detail.records.map((rec, idx) =>
                                    `<li><strong>Sakit ${idx+1} (${rec.tanggalAwal} - ${rec.tanggalAkhir}):</strong> ${rec.alasan}</li>`
                                ).join("");
                                accordionHtml += `
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingSakit${index}">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSakit${index}" aria-expanded="false" aria-controls="collapseSakit${index}">
                                    ${nama} - ${detail.divisi}
                                </button>
                            </h2>
                            <div id="collapseSakit${index}" class="accordion-collapse collapse" aria-labelledby="headingSakit${index}" data-bs-parent="#accordionSakit">
                                <div class="accordion-body">
                                    <div class="d-flex align-items-start">
                                        <div class="avatar ${randomBg} text-white rounded-circle d-flex justify-content-center align-items-center me-3" style="width:40px; height:40px; font-weight:bold;">${initials}</div>
                                        <div>
                                            <h6 class="mb-0 fw-semibold">${nama}</h6>
                                            <small class="text-muted">${detail.divisi}</small>
                                            <ul class="mt-2 mb-0">${alasanList}</ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>`;
                                index++;
                            }
                            accordionHtml += `</div>`;
                            contentModalBodyKS.append(accordionHtml);
                        }

                        contentModalBodyKI.empty();
                        if (dataIzin.length === 0) {
                            const idUserLogin = "{{ auth()->user()->jabatan }}";
                            let textUser = '';

                            if (idUserLogin === 'HRD' || idUserLogin === 'GM' || idUserLogin === 'Direktur Utama') {
                                textUser = 'Tidak ada karyawan yang izin di quartal ini.';
                            } else {
                                textUser = 'Tidak ada data izin anda di quartal ini.';
                            }

                            contentModalBodyKI.append(
                                `<div class="list-group-item border-0 d-flex align-items-center rounded-3 shadow-sm mb-3 p-3">${textUser}</div>`
                            );
                        } else {
                            let accordionHtml = `<div class="accordion" id="accordionIzin">`;
                            const groupedIzin = {};
                            dataIzin.forEach(item => {
                                if (!groupedIzin[item.namaKaryawan]) {
                                    groupedIzin[item.namaKaryawan] = {
                                        divisi: item.divisi,
                                        records: []
                                    };
                                }
                                groupedIzin[item.namaKaryawan].records.push({
                                    alasan: item.alasan,
                                    tanggalPengajuan: item.tanggalPengajuan
                                });
                            });
                            let index = 0;
                            for (const [nama, detail] of Object.entries(groupedIzin)) {
                                let words = nama.split(" ");
                                let initials = words[0].charAt(0).toUpperCase() + (words[1] ? words[1].charAt(0)
                                    .toUpperCase() : "");
                                let randomBg = bgClasses[Math.floor(Math.random() * bgClasses.length)];
                                let alasanList = detail.records.map((rec, idx) =>
                                    `<li><strong>Izin ${idx+1} (${rec.tanggalPengajuan}):</strong> ${rec.alasan}</li>`
                                ).join("");
                                accordionHtml += `
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingIzin${index}">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseIzin${index}" aria-expanded="false" aria-controls="collapseIzin${index}">
                                    ${nama} - ${detail.divisi}
                                </button>
                            </h2>
                            <div id="collapseIzin${index}" class="accordion-collapse collapse" aria-labelledby="headingIzin${index}" data-bs-parent="#accordionIzin">
                                <div class="accordion-body">
                                    <div class="d-flex align-items-start">
                                        <div class="avatar ${randomBg} text-white rounded-circle d-flex justify-content-center align-items-center me-3" style="width:40px; height:40px; font-weight:bold;">${initials}</div>
                                        <div>
                                            <h6 class="mb-0 fw-semibold">${nama}</h6>
                                            <small class="text-muted">${detail.divisi}</small>
                                            <ul class="mt-2 mb-0">${alasanList}</ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>`;
                                index++;
                            }
                            accordionHtml += `</div>`;
                            contentModalBodyKI.append(accordionHtml);
                        }

                        select_peringkatPenilaian.empty();
                        const Divisi = response.dataDivisi ?? [];
                        if (Divisi.length === 0) {
                            select_peringkatPenilaian.append(`<option>Gagal mengambil</option>`);
                        } else {
                            Divisi.forEach(function(data) {
                                select_peringkatPenilaian.append(
                                    `<option value="${data.divisi}">${data.divisi}</option>`);
                            });
                            const defaultDivisi = Divisi[0].divisi;
                            select_peringkatPenilaian.val(defaultDivisi);
                            renderPeringkat(defaultDivisi);
                        }

                        select_peringkatPenilaian.on('change', function() {
                            const divisiDipilih = $(this).val();
                            renderPeringkat(divisiDipilih);
                        });

                        function renderPeringkat(divisi) {
                            let allData = response.dataRangking.filter(item => item.divisi === divisi);
                            allData.sort((a, b) => b.total_nilai - a.total_nilai);
                            let dataFiltered = allData.filter(item => item.total_nilai > 0);
                            let cardContainer = $('.row-ranking');
                            cardContainer.empty();
                            bodyContentPeringkat.empty();
                            title_peringkat.text(`Terbaik Divisi ${divisi}`);

                            if (dataFiltered.length === 0) {
                                cardContainer.append(`
                        <div class="col-12">
                            <div class="p-4 text-center rounded-3 bg-light">
                                <img src="{{ asset('template_KPI/dist/assets/images/screenshots/gambar_pencarian.png') }}" class="mb-3" width="80%" height="250px" alt="" style="opacity: 0.5;">
                                <h6 class="mb-0 text-muted">Belum ada karyawan yang memiliki peringkat di divisi ini</h6>
                            </div>
                        </div>
                    `);
                            } else {
                                const top3 = dataFiltered.slice(0, 3);
                                top3.forEach((item, index) => {
                                    let posisi = index + 1;
                                    let colClass = "col-12";
                                    if (top3.length === 2) {
                                        colClass = "col-12 col-sm-6";
                                    } else if (top3.length === 3) {
                                        colClass = "col-12 col-sm-6 col-lg-4";
                                    }
                                    let gradient;
                                    if (posisi === 1) gradient =
                                        "linear-gradient(135deg, #f6d365, #fda085)";
                                    if (posisi === 2) gradient =
                                        "linear-gradient(135deg, #cfd9df, #e2ebf0)";
                                    if (posisi === 3) gradient =
                                        "linear-gradient(135deg, #d1913c, #ffd194)";
                                    const baseUrl = "{{ asset('storage') }}";
                                    const defaultFoto =
                                        "{{ asset('template_KPI/dist/assets/images/screenshots/user-profile.jpg') }}";
                                    const foto = item.foto ? `${baseUrl}/${item.foto}` : defaultFoto;

                                    cardContainer.append(`
                            <div class="${colClass} text-center mb-4">
                                <div class="ranking-card p-4 rounded-4 text-white position-relative overflow-hidden h-100" style="background:${gradient}; transition: transform 0.3s ease, box-shadow 0.3s ease;">
                                    <div class="position-absolute top-0 end-0 opacity-25" style="width:120px; height:120px; border-radius:50%; background:rgba(255,255,255,0.2);"></div>
                                    <div class="position-absolute bottom-0 start-0 opacity-25" style="width:80px; height:80px; border-radius:50%; background:rgba(255,255,255,0.15);"></div>
                                    <div class="position-relative">
                                        ${posisi === 1 ? `<img src="{{ asset('css/doodle-crown.png') }}" class="position-absolute top-0 start-50 translate-middle-x" style="width:70px; margin-top:-55px; filter: drop-shadow(0 2px 5px rgba(0,0,0,0.4));">` : ""}
                                        <img src="${foto}" class="rounded-circle border border-4 border-white my-3" style="width: 110px; height: 110px; object-fit:cover;">
                                    </div>
                                    <h6 class="fw-bold mb-1" style="font-size:1rem;">${item.nama_karyawan}</h6>
                                    <small class="text-light opacity-75 d-block mb-2">${item.divisi}</small>
                                </div>
                                <h5 class="mt-3 fw-bold text-dark">${posisi}</h5>
                            </div>
                        `);
                                });
                            }

                            let lastScore = null;
                            let currentRank = 0;
                            let shownRank = 0;
                            allData.forEach((item, idx) => {
                                if (item.total_nilai !== lastScore) {
                                    currentRank = shownRank + 1;
                                }
                                lastScore = item.total_nilai;
                                shownRank++;
                                const baseUrl = "{{ asset('storage') }}";
                                bodyContentPeringkat.append(`
                        <div class="d-flex align-items-center mb-3 p-2 rounded-3 shadow-sm bg-light">
                            <span class="me-3 fw-bold text-dark" style="min-width:25px;">${currentRank}.</span>
                            ${
                                item.foto 
                                ? `<img src="${baseUrl}/${item.foto}" class="rounded-circle border border-2 border-white shadow me-3" style="width:45px; height:45px; object-fit:cover;">`
                                : (() => {
                                    const namaSplit = item.nama_karyawan.split(" ");
                                    const inisial = namaSplit[0].charAt(0).toUpperCase() + (namaSplit[1] ? namaSplit[1].charAt(0).toUpperCase() : "");
                                    return `<div class="rounded-circle bg-primary text-white d-flex justify-content-center align-items-center me-3 shadow" style="width:45px; height:45px; font-weight:bold; font-size:14px;">${inisial}</div>`;
                                })()
                            }
                            <div class="flex-grow-1">
                                <div class="fw-bold">${item.nama_karyawan}</div>
                                <small class="text-muted">${item.divisi}</small>
                            </div>
                            <div class="fw-bold text-dark me-3">${item.total_nilai}</div>
                            <div class="progress flex-grow-1" style="max-width: 250px; height: 12px;">
                                <div class="progress-bar bg-gradient-info" role="progressbar" style="width: ${item.total_nilai}%;" aria-valuenow="${item.total_nilai}" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>
                    `);
                            });
                        }
                    },
                    error: function(err) {
                        console.error('Gagal load data:', err);
                    }
                });
            }

            function loadProgressData() {
                $.ajax({
                    url: "{{ route('kpi.getProgressDasboard') }}",
                    type: 'get',
                    success: function(response) {

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
