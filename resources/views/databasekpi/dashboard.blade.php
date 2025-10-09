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
                    <span></span>Dashboard <i class="mdi mdi-alert-circle-outline icon-sm text-primary align-middle" data-bs-toggle="tooltip" data-bs-placement="top" title="Lihat statistik data seputar KPI."></i>
                </li>
            </ul>
        </nav>
    </div>
    <div class="row mb-3">
        <div class="col-md-6 col-lg-3 mb-3 stretch-card d-flex">
            <div class="card bg-gradient-danger card-img-holder text-white shadow-lg rounded-4 flex-fill p-4">
                <img src="{{ asset('template_KPI/dist/assets/images/dashboard/circle.svg') }}"
                    class="card-img-absolute" alt="circle-image" />
                <h6 class="font-weight-normal mb-3">Jumlah Karyawan Aktif</h6>
                <h3 class="mb-5">
                    <span id="content_JK"><small class="loading-text">memuat...</small></span>
                </h3>
            </div>
        </div>

        <div class="col-md-6 col-lg-3 mb-3 stretch-card d-flex">
            <div class="card bg-gradient-info card-img-holder text-white shadow-lg rounded-4 flex-fill p-3"
                data-bs-toggle="modal" data-bs-target="#modalSakit" style="cursor:pointer;">
                <img src="{{ asset('template_KPI/dist/assets/images/dashboard/circle.svg') }}"
                    class="card-img-absolute" alt="circle-image" />
                <h6 class="font-weight-normal mb-3">Sakit Dalam Triwulan Ini</h6>
                <h3 class="mb-5">
                    <span id="content_KS"><small class="loading-text">memuat...</small></span>
                </h3>
            </div>
        </div>

        <div class="col-md-6 col-lg-3 mb-3 stretch-card d-flex">
            <div class="card bg-gradient-success card-img-holder text-white shadow-lg rounded-4 flex-fill p-3"
                data-bs-toggle="modal" data-bs-target="#modalIzin" style="cursor:pointer;">
                <img src="{{ asset('template_KPI/dist/assets/images/dashboard/circle.svg') }}"
                    class="card-img-absolute" alt="circle-image" />
                <h6 class="font-weight-normal mb-3">Izin Dalam Triwulan Ini</h6>
                <h3 class="mb-5">
                    <span id="content_KI"><small class="loading-text">memuat...</small></span>
                </h3>
            </div>
        </div>

        <div class="col-md-6 col-lg-3 mb-3 stretch-card d-flex">
            <div class="card bg-gradient-warning card-img-holder text-white shadow-lg rounded-4 flex-fill p-3"
                data-bs-toggle="modal" data-bs-target="#modalCuti" style="cursor:pointer;">
                <img src="{{ asset('template_KPI/dist/assets/images/dashboard/circle.svg') }}"
                    class="card-img-absolute" alt="circle-image" />
                <h6 class="font-weight-normal mb-3">Cuti Dalam Triwulan Ini</h6>
                <h3 class="mb-5">
                    <span id="content_KC"><small class="loading-text">memuat...</small></span>
                </h3>
            </div>
        </div>

        <div class="modal fade" id="modalCuti" tabindex="-1" aria-labelledby="modalCutiLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content modal-themed modal-themed-warning shadow-lg">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold" id="modalCutiLabel">Daftar Karyawan Cuti</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="list-group" id="bodyContentModalCuti">
                            <div class="text-center text-white-50">Belum ada data</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger text-dark rounded-pill" data-bs-dismiss="modal">Tutup</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="modalIzin" tabindex="-1" aria-labelledby="modalIzinLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content modal-themed modal-themed-success shadow-lg">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold" id="modalIzinLabel">Daftar Karyawan Izin</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="list-group" id="bodyContentModalIzin">
                            <div class="text-center text-white-50">Belum ada data</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger text-dark rounded-pill" data-bs-dismiss="modal">Tutup</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="modalSakit" tabindex="-1" aria-labelledby="modalSakitLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content modal-themed modal-themed-info shadow-lg">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold" id="modalSakitLabel">Daftar Karyawan Sakit</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="list-group" id="bodyContentModalSakit">
                            <div class="text-center text-white-50">Belum ada data</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger text-dark rounded-pill" data-bs-dismiss="modal">Tutup</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row d-flex align-items-stretch">
        <div class="col-md-7 grid-margin d-flex">
            <div class="card shadow-lg border-0 card-rounded bg-light text-dark w-100 h-100">
                <div class="card-body d-flex flex-column">
                    <div class="card-header bg-transparent border-0 py-2 px-0">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="container ">
                                <div class="row">
                                    <div class="col-sm-3 text-center mb-3 d-flex justify-content-center align-items-center">
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
                                                        id="select_peringkatPenilaian"
                                                        aria-label="divisi select"
                                                        name="divisi">
                                                        <option>Education</option>
                                                    </select>
                                                    <button class="btn btn-danger"
                                                        type="submit"
                                                        id="btn_exportPDF_rangking"
                                                        title="Export PDF">
                                                        <i class="fa-solid fa-file-pdf"></i>
                                                    </button>
                                                </div>
                                            </div>
                                            <input type="hidden" id="quartal" name="quartal" value="Q{{ ceil(date('m') / 3) }}">
                                            <input type="hidden" id="tahun" name="tahun" value="{{ date('Y') }}">
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-body h-100 px-0">
                        <div class="row justify-content-center align-items-end text-center row-ranking" style="min-height:300px;">
                        </div>
                        <div class="text-center mt-3">
                            <small class="text-dark-50 fst-italic">
                                *hasil diambil dari penilaian 360°, tidak termasuk yang lainnya
                            </small>
                        </div>
                        <div class="mt-4 text-center">
                            <button type="button" class="btn btn-gradient-primary rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#modalPeringkatPenilaian360">
                                lihat semua
                            </button>
                        </div>
                    </div>
                </div>
                <div class="modal fade" id="modalPeringkatPenilaian360" tabindex="-1" aria-labelledby="modalPeringkatPenilaian360Label" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content modal-themed modal-themed-warning shadow-lg">
                            <div class="modal-header">
                                <h5 class="modal-title fw-bold" id="modalPeringkatPenilaian360Label">Peringkat</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body" id="bodyContentPeringkat"></div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-danger text-dark rounded-pill" data-bs-dismiss="modal">Tutup</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-5 grid-margin d-flex flex-column"">
            <div class=" card shadow-lg border-0 card-rounded bg-light text-dark w-100 mb-4 h-100">
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

        <div class="card card-rounded flex-fill">
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

        <!-- <div class="card card-rounded w-100 h-100">
            <div class="card-body">
                <h4 class="card-title card-title-dash mb-4">Target Manager/Koordinator</h4>

                <div class="scroll-wrapper">
                    <div class="d-flex justify-content-start flex-nowrap">
                        <div class="bar-container">
                            <div class="bar-value">35%</div>
                            <div class="progress progress-vertical" style="width: 40px; height: 150px; background-color: #e9ecef;">
                                <div class="progress-bar bg-gradient-danger" role="progressbar" style="width: 100%; height: 35%;" aria-valuenow="35"></div>
                            </div>
                            <div class="bar-label">Manager<br>Education</div>
                        </div>

                        <div class="bar-container me-4">
                            <div class="bar-value">47%</div>
                            <div class="progress progress-vertical" style="width: 40px; height: 150px; background-color: #e9ecef;">
                                <div class="progress-bar bg-gradient-success" role="progressbar" style="width: 100%; height: 47%;" aria-valuenow="47"></div>
                            </div>
                            <div class="bar-label">Manager<br>Sales & Marketing</div>
                        </div>

                        <div class="bar-container me-4">
                            <div class="bar-value">53%</div>
                            <div class="progress progress-vertical" style="width: 40px; height: 150px; background-color: #e9ecef;">
                                <div class="progress-bar bg-gradient-primary" role="progressbar" style="width: 100%; height: 53%;" aria-valuenow="53"></div>
                            </div>
                            <div class="bar-label">Koordinator<br>ITSM</div>
                        </div>

                        <div class="bar-container me-4">
                            <div class="bar-value">40%</div>
                            <div class="progress progress-vertical" style="width: 40px; height: 150px; background-color: #e9ecef;">
                                <div class="progress-bar bg-gradient-info" role="progressbar" style="width: 100%; height: 40%;" aria-valuenow="40"></div>
                            </div>
                            <div class="bar-label">Koordinator<br>Office</div>
                        </div>
                    </div>
                </div>
            </div>
        </div> -->
    </div>
</div>
<!-- <div class="row">
    <div class="col-lg-6 d-flex flex-column">
        <div class="card card-rounded flex-fill">
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

    <div class="col d-flex flex-column">
        <div class="card card-rounded flex-fill">
            <div class="card-body">
                <div class="d-sm-flex justify-content-between align-items-start mb-3">
                    <h4 class="card-title card-title-dash">Target Karyawan</h4>
                </div>

                <div class="list-group">
                    <button class="list-group-item list-group-item-action d-flex align-items-center border-0 mb-2">
                        <img class="img-sm rounded-2 me-3" src="{{ asset('template_KPI/dist/assets/images/screenshots/user-profile.jpg') }}" alt="profile">
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between">
                                <h6 class="mb-1">Xepi - Finance</h6>
                                <small class="text-muted">78%</small>
                            </div>
                            <div class="progress progress-sm mt-1">
                                <div class="progress-bar bg-danger" role="progressbar" style="width: 78%"></div>
                            </div>
                        </div>
                        <i class="fa-solid fa-chevron-right ms-4"></i>
                    </button>

                    <button class="list-group-item list-group-item-action d-flex align-items-center border-0 mb-2">
                        <img class="img-sm rounded-2 me-3" src="{{ asset('template_KPI/dist/assets/images/screenshots/user-profile.jpg') }}" alt="profile">
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between">
                                <h6 class="mb-1">Networking</h6>
                                <small class="text-muted">62%</small>
                            </div>
                            <div class="progress progress-sm mt-1">
                                <div class="progress-bar bg-danger" role="progressbar" style="width: 62%"></div>
                            </div>
                        </div>
                        <i class="fa-solid fa-chevron-right ms-4"></i>
                    </button>

                    <button class="list-group-item list-group-item-action d-flex align-items-center border-0 mb-2">
                        <img class="img-sm rounded-2 me-3" src="{{ asset('template_KPI/dist/assets/images/screenshots/user-profile.jpg') }}" alt="profile">
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between">
                                <h6 class="mb-1">Compliance</h6>
                                <small class="text-muted">60%</small>
                            </div>
                            <div class="progress progress-sm mt-1">
                                <div class="progress-bar bg-danger" role="progressbar" style="width: 60%"></div>
                            </div>
                        </div>
                        <i class="fa-solid fa-chevron-right ms-4"></i>
                    </button>

                    <button class="list-group-item list-group-item-action d-flex align-items-center border-0 mb-2">
                        <img class="img-sm rounded-2 me-3" src="{{ asset('template_KPI/dist/assets/images/screenshots/user-profile.jpg') }}" alt="profile">
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between">
                                <h6 class="mb-1">Security</h6>
                                <small class="text-muted">56%</small>
                            </div>
                            <div class="progress progress-sm mt-1">
                                <div class="progress-bar bg-danger" role="progressbar" style="width: 56%"></div>
                            </div>
                        </div>
                        <i class="fa-solid fa-chevron-right ms-4"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div> -->
</div>
@endsection

@section('script')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    let chart1, chart2;

    $(document).ready(function() {
        loadData();
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
                            backgroundColor: [gradientPrimary, gradientInfo, gradientDanger, gradientSuccess],
                            borderWidth: 0
                        }]
                    },
                    options: {
                        cutout: '70%',
                        plugins: {
                            legend: {
                                display: false
                            }
                        }
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
                content_KS.text(`${dataCardFirst.dataSakit?.totalAbsenSakit ?? 0} Karyawan`);
                content_KC.text(`${dataCardFirst.dataCuti?.totalAbsenCuti ?? 0} Karyawan`);
                content_KI.text(`${dataCardFirst.dataIzin?.totalAbsenIzin ?? 0} Karyawan`);

                const bgClasses = ["cl-yellow", "cl-red", "cl-green", "cl-blue", "cl-grey"];

                contentModalBodyKC.empty();
                if (dataCuti.length === 0) {
                    contentModalBodyKC.append(`<div class="list-group-item border-0 d-flex align-items-center rounded-3 shadow-sm mb-3 p-3">Tidak ada karyawan yang cuti di quartal ini</div>`);
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
                        let initials = words[0].charAt(0).toUpperCase() + (words[1] ? words[1].charAt(0).toUpperCase() : "");
                        let randomBg = bgClasses[Math.floor(Math.random() * bgClasses.length)];
                        let alasanList = detail.records.map((rec, idx) =>
                            `<li><strong>Cuti ${idx+1} (${rec.tanggalAwal} - ${rec.tanggalAkhir}):</strong> ${rec.alasan}</li>`).join("");
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
                    contentModalBodyKS.append(`<div class="list-group-item border-0 d-flex align-items-center rounded-3 shadow-sm mb-3 p-3">Tidak ada karyawan yang sakit di quartal ini</div>`);
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
                        let initials = words[0].charAt(0).toUpperCase() + (words[1] ? words[1].charAt(0).toUpperCase() : "");
                        let randomBg = bgClasses[Math.floor(Math.random() * bgClasses.length)];
                        let alasanList = detail.records.map((rec, idx) =>
                            `<li><strong>Sakit ${idx+1} (${rec.tanggalAwal} - ${rec.tanggalAkhir}):</strong> ${rec.alasan}</li>`).join("");
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
                    contentModalBodyKI.append(`<div class="list-group-item border-0 d-flex align-items-center rounded-3 shadow-sm mb-3 p-3">Tidak ada karyawan yang izin di quartal ini</div>`);
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
                        let initials = words[0].charAt(0).toUpperCase() + (words[1] ? words[1].charAt(0).toUpperCase() : "");
                        let randomBg = bgClasses[Math.floor(Math.random() * bgClasses.length)];
                        let alasanList = detail.records.map((rec, idx) =>
                            `<li><strong>Izin ${idx+1} (${rec.tanggalPengajuan}):</strong> ${rec.alasan}</li>`).join("");
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
                        select_peringkatPenilaian.append(`<option value="${data.divisi}">${data.divisi}</option>`);
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
                            if (posisi === 1) gradient = "linear-gradient(135deg, #f6d365, #fda085)";
                            if (posisi === 2) gradient = "linear-gradient(135deg, #cfd9df, #e2ebf0)";
                            if (posisi === 3) gradient = "linear-gradient(135deg, #d1913c, #ffd194)";
                            const baseUrl = "{{ asset('storage') }}";
                            const defaultFoto = "{{ asset('template_KPI/dist/assets/images/screenshots/user-profile.jpg') }}";
                            const foto = item.foto ? `${baseUrl}/${item.foto}` : defaultFoto;

                            cardContainer.append(`
                            <div class="${colClass} text-center mb-4">
                                <div class="ranking-card p-4 rounded-4 shadow-lg text-white position-relative overflow-hidden h-100" style="background:${gradient}; transition: transform 0.3s ease, box-shadow 0.3s ease;">
                                    <div class="position-absolute top-0 end-0 opacity-25" style="width:120px; height:120px; border-radius:50%; background:rgba(255,255,255,0.2);"></div>
                                    <div class="position-absolute bottom-0 start-0 opacity-25" style="width:80px; height:80px; border-radius:50%; background:rgba(255,255,255,0.15);"></div>
                                    <div class="position-relative">
                                        ${posisi === 1 ? `<img src="{{ asset('css/doodle-crown.png') }}" class="position-absolute top-0 start-50 translate-middle-x" style="width:70px; margin-top:-55px; filter: drop-shadow(0 2px 5px rgba(0,0,0,0.4));">` : ""}
                                        <img src="${foto}" class="rounded-circle border border-4 border-white shadow-lg my-3" style="width: 110px; height: 110px; object-fit:cover;">
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
</script>
@endsection