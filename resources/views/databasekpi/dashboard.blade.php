@extends('databasekpi.berandaKPI')

@section('contentKPI')
<link href="https://fonts.googleapis.com/css2?family=Roboto+Flex:opsz,wght@8..144,100..1000&family=Shadows+Into+Light&display=swap" rel="stylesheet">
<style>
    .card-minimal {
        background-color: #fff;
        border: 1px solid rgba(0, 0, 0, 0.1);
        border-radius: 12px;
        box-shadow: 0 2px 6px rgba(0, 123, 255, 0.15);
        transition: box-shadow 0.3s ease;
        height: 130px;
        display: flex;
        align-items: center;
        padding: 0rem 1rem;
    }

    .card-minimal:hover {
        box-shadow: 0 4px 12px rgba(0, 123, 255, 0.3);
    }

    .icon-wrapper {
        background-color: rgba(0, 123, 255, 0.1);
        color: #007bff;
        border-radius: 50%;
        width: 60px;
        height: 60px;
        display: flex;
        justify-content: center;
        align-items: center;
        margin-right: 16px;
        flex-shrink: 0;
    }

    .card-content div {
        margin: 0;
        font-weight: 600;
        color: #333;
        font-size: 1.1rem;
    }

    .card-content p {
        margin: 0;
        color: #666;
        font-size: 12px;
        font-weight: 700;
    }

    .card-content .title-card {
        font-size: 13px;
    }

    .wadah-kartu-indikator {
        display: flex;
        gap: 15px;
        font-family: sans-serif;
    }

    .kartu-statistik {
        background: #fff;
        border-radius: 10px;
        padding: 15px;
        flex: 1;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
    }

    .kepala-kartu {
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .ikon-bulat {
        width: 35px;
        height: 35px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
    }

    .telepon {
        background: #4da3ff;
    }

    .pengguna {
        background: #6bc2ff;
    }

    .petir {
        background: #81f5b0;
        color: #2c8c5c;
    }

    .judul-kartu {
        font-weight: 600;
        margin-left: 10px;
        flex: 1;
        font-size: 12px;
    }

    .titik-tiga {
        color: #999;
        cursor: pointer;
    }

    .angka-persentase {
        font-size: 20px;
        font-weight: 700;
        margin-top: 10px;
    }

    .angka-persentase small {
        display: block;
        font-size: 12px;
        color: #888;
    }

    .naik {
        color: green;
        font-size: 14px;
    }

    .turun {
        color: red;
        font-size: 14px;
    }

    .grafik-mini {
        height: 40px;
        margin-top: 10px;
        background: linear-gradient(to right, #a0c4ff, transparent);
        border-radius: 5px;
    }

    .grafik-hijau {
        background: linear-gradient(to right, #a3f7bf, transparent);
    }

    @media (max-width: 992px) {
        .goal-card-body {
            flex-direction: column !important;
            align-items: center !important;
        }

        .goal-card-list {
            margin-right: 0 !important;
            margin-bottom: 1.5rem !important;
        }

        .goal-chart {
            width: 140px !important;
            height: 140px !important;
            min-width: 140px !important;
        }
    }

    @media (max-width: 576px) {
        .goal-chart {
            width: 120px !important;
            height: 120px !important;
            min-width: 120px !important;
        }
    }

    .loading-text {
        display: inline-block;
        overflow: hidden;
        white-space: nowrap;
        width: 0;
        animation: typing 2s steps(100, end) forwards;
    }

    @keyframes typing {
        from {
            width: 0;
        }

        to {
            width: 100%;
        }
    }

    .icon-rotate {
        animation: rotate 3s linear infinite;
    }

    @keyframes rotate {
        from {
            transform: rotate(0deg);
        }

        to {
            transform: rotate(360deg);
        }
    }

    /* 2. Success - Bounce halus */
    .icon-bounce {
        animation: bounce 2s ease-in-out infinite;
    }

    @keyframes bounce {

        0%,
        100% {
            transform: translateY(0);
        }

        50% {
            transform: translateY(-3px);
        }

        /* kecil saja biar elegan */
    }

    /* 3. Danger - Shake singkat & elegan */
    .icon-shake {
        animation: shake 1s ease-in-out 1;
        /* hanya sekali */
    }

    @keyframes shake {

        0%,
        100% {
            transform: translateX(0);
        }

        20% {
            transform: translateX(-2px);
        }

        40% {
            transform: translateX(2px);
        }

        60% {
            transform: translateX(-2px);
        }

        80% {
            transform: translateX(2px);
        }
    }

    .button-card {
        background-color: none;
        padding: 0;
        background: none;
        border: none;
    }

    .podium {
        min-height: 200px;
    }

    .podium-1 {
        min-height: 280px;
    }

    .podium-2 {
        min-height: 240px;
    }

    .podium-3 {
        min-height: 200px;
    }

    @media (max-width: 767.98px) {

        .podium,
        .podium-1,
        .podium-2,
        .podium-3 {
            min-height: auto;
        }
    }

    input[type="range"] {
        -webkit-appearance: none;
        width: 100%;
        height: 6px;
        border-radius: 5px;
        background: #e5e7eb;
        /* abu-abu lembut */
        outline: none;
    }

    input[type="range"]::-webkit-slider-thumb {
        -webkit-appearance: none;
        appearance: none;
        width: 16px;
        height: 16px;
        border-radius: 50%;
        background: #3b82f6;
        cursor: pointer;
        transition: background 0.2s ease;
    }

    input[type="range"]::-webkit-slider-thumb:hover {
        background: #2563eb;
    }

    input[type="range"]::-moz-range-thumb {
        width: 16px;
        height: 16px;
        border-radius: 50%;
        background: #3b82f6;
        cursor: pointer;
        border: none;
    }

    .contentCardSPJ {
        max-height: 300px;
        overflow-y: scroll;
    }
</style>

<div class="container mt-4 mb-4 bg-theme">
    <div class="wadah-kartu-indikator">
        <div class="container">
            <div class="row">
                <div class="col-sm mb-2">
                    <div class="kartu-statistik bg-theme border">
                        <div class="kepala-kartu">
                            <div class="ikon-bulat telepon">
                                <i class="fa-solid fa-user-tie"></i>
                            </div>
                            <span class="judul-kartu">Jumlah Karyawan Aktif</span>
                        </div>
                        <div class="angka-persentase" id="content_JK">
                            <small class="loading-text">memuat...</small>
                        </div>
                    </div>
                </div>
                <div class="col-sm mb-2">
                    <div class="kartu-statistik bg-theme border">
                        <button type="button" class="button-card text-start" data-toggle="modal" data-target="#modalCardPertamaSakit">
                            <div class="kepala-kartu">
                                <div class="ikon-bulat pengguna">
                                    <i class="fa-solid fa-house-medical"></i>
                                </div>
                                <span class="judul-kartu">Sakit Dalam Triwulan Ini</span>
                            </div>
                            <div class="angka-persentase" id="content_KS">
                                <small class="loading-text">memuat...</small>
                            </div>
                        </button>
                    </div>
                </div>

                <div class="col-sm mb-2">
                    <div class="kartu-statistik bg-theme border">
                        <button type="button" class="button-card text-start" data-toggle="modal" data-target="#modalCardPertamaIzin">
                            <div class="kepala-kartu">
                                <div class="ikon-bulat pengguna">
                                    <i class="fa-solid fa-scroll"></i>
                                </div>
                                <span class="judul-kartu">Izin Dalam Triwulan Ini</span>
                            </div>
                            <div class="angka-persentase" id="content_KI">
                                <small class="loading-text">memuat...</small>
                            </div>
                        </button>
                    </div>
                </div>

                <div class="col-sm mb-2">
                    <div class="kartu-statistik bg-theme border">
                        <button type="button" class="button-card text-start" data-toggle="modal" data-target="#modalCardPertamaCuti">
                            <div class="kepala-kartu">
                                <div class="ikon-bulat pengguna">
                                    <i class="fa-solid fa-person-hiking"></i>
                                </div>
                                <span class="judul-kartu">Cuti Dalam Triwulan Ini</span>
                            </div>
                            <div class="angka-persentase" id="content_KC">
                                <small class="loading-text">memuat...</small>
                            </div>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div id="modalCardPertama">
    <div class="modal fade" id="modalCardPertamaCuti" tabindex="-1" role="dialog" aria-labelledby="modalCardPertamaCutiLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal" role="document">
            <div class="modal-content shadow-lg rounded-3">

                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">Daftar Karyawan Cuti</h5>
                    <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div class="list-group" id="bodyContentModalCuti">
                    </div>
                </div>

                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary rounded-pill" data-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalCardPertamaIzin" tabindex="-1" role="dialog" aria-labelledby="modalCardPertamaIzinLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal" role="document">
            <div class="modal-content shadow-lg rounded-3">

                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">Daftar Karyawan Izin</h5>
                    <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div class="list-group" id="bodyContentModalIzin">
                    </div>
                </div>

                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary rounded-pill" data-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalCardPertamaSakit" tabindex="-1" role="dialog" aria-labelledby="modalCardPertamaSakitLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal" role="document">
            <div class="modal-content shadow-lg rounded-3">

                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">Daftar Karyawan Sakit</h5>
                    <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div class="list-group" id="bodyContentModalSakit">
                    </div>
                </div>

                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary rounded-pill" data-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- 
<div class="container text-center mt-3 mb-3">
    <div class="row">
        <div class="col-sm-7">
            <div class="card h-100">
                <div class="card-header bg-body-tertiary py-2">
                    <div class="row flex-between-center">
                        <div class="col-auto p-2">
                            <h6 class="mb-0">Kinerja Kantor</h6>
                        </div>
                    </div>
                </div>
                <div class="card-body h-100">
                    <canvas id="topProductsChart" height="300"></canvas>
                </div>
            </div>
        </div>
        <div class="col-sm-5">
            <div class="card h-100 shadow-sm">
                <div class="card-header bg-theme border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold">Manager Goal In 2025</h6>
                    <button class="btn btn-link text-muted p-0">
                        <i class="fas fa-ellipsis-h"></i>
                    </button>
                </div>
                <div class="card-body d-flex flex-column flex-lg-row align-items-start p-4 goal-card-body">
                    <div class="flex-grow-1 me-lg-4 mb-4 mb-lg-0 goal-card-list">
                        <div class="d-flex align-items-start mb-3 p-2" style="border-left : 3px solid #24C6F9">
                            <div class="ms-2">
                                <small class="text-muted d-block">General Manager</small>
                                <p class="fw-bold mb-0">98% <span class="ms-2 rounded-pill text-success"><i class="fa-solid fa-circle-check"></i> Selesai</span></p>
                            </div>
                        </div>
                        <div class="d-flex align-items-start mb-3 p-2" style="border-left : 3px solid #2E86FC">
                            <div>
                                <small class="text-muted d-block">Education Manager</small>
                                <p class="fw-bold mb-0">18% <span class="ms-2 rounded-pill"><i class="fa-solid fa-bars-progress text-warning"></i> Process</span></p>
                            </div>
                        </div>
                        <div class="d-flex align-items-start mb-3 p-2" style="border-left : 3px solid #28A745">
                            <div>
                                <small class="text-muted d-block">SPV Sales</small>
                                <p class="fw-bold mb-0">70% <span class="ms-2 rounded-pill text-danger"><i class="fa-solid fa-circle-exclamation"></i> fail</span></p>
                            </div>
                        </div>
                        <div class="d-flex align-items-start mb-3 p-2" style="border-left : 3px solid #B71CFF">
                            <div>
                                <small class="text-muted d-block">Koordinator ITSM</small>
                                <p class="fw-bold mb-0">90% <span class="ms-2 rounded-pill text-success"><i class="fa-solid fa-circle-exclamation"></i> success</span></p>
                            </div>
                        </div>
                    </div>
                    <div class="goal-chart" style="width:180px;height:180px; min-width:180px; position: relative;">
                        <canvas id="weeklyGoalChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> -->
<div class="container text-center mt-3 mb-3">
    <div class="row">
        <div class="col-sm-5">
            <div class="card h-100 mt-3">
                <div class="card-header bg-body-tertiary py-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">Penilaian Berjalan Quartal Ini</h6>
                    </div>
                </div>
                <div class="card-body h-100">
                    <p id="title_chartPenilaian">Penilaian Yang Diadakan : 0 Penilaian</p>
                    <canvas id="totalPenilaianChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-sm-7">
            <div class="card h-100 mt-3">
                <div class="card-header bg-body-tertiary py-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0" id="title_peringkat">Terbaik Divisi</h6>
                        <select class="form-select form-select-sm w-auto" id="select_peringkatPenilaian"></select>
                    </div>
                </div>
                <div class="card-body h-100">
                    <div class="row justify-content-center align-items-end text-center row-ranking" style="min-height:300px;"></div>
                    <div class="text-center mt-2 text-danger">
                        <small>*hasil diambil dari penilaian 360°, tidak termasuk yang lainnya</small>
                    </div>
                    <div class="mt-3">
                        <button type="button" class="btn cl-blue" data-toggle="modal" data-target="#modalPeringkatPenilaian360">
                            lihat semua
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
<div class="modal fade" id="modalPeringkatPenilaian360" tabindex="-1" role="dialog" aria-labelledby="modalPeringkatPenilaian360Label" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalPeringkatPenilaian360Label">Peringkat</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="bodyContentPeringkat"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<div class="container">
    <div class="card h-100 p-3">
        <div class="title mb-3">
            <div class="d-flex justify-content-between align-items-center">
                <p class="mb-0">Top 10 Pengajuan Barang Terbaru</p>
                <a href="/pengajuanbarang" class="btn cl-blue text-white">Lihat Semua</a>
            </div>
        </div>
        <div class="table-responsive-lg">

            <table class="table">
                <thead>
                    <tr>
                        <th scope="col">Tanggal Pengajuan</th>
                        <th scope="col">Nama Pengaju</th>
                        <th scope="col">Nama Barang</th>
                        <th scope="col">Jumlah</th>
                        <th scope="col">Harga</th>
                        <th scope="col">Total Pengajuan</th>
                    </tr>
                </thead>
                <tbody id="tbody_dataPengajuanBarang">
                    <tr>
                        <td colspan="6">memuat...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
<div class="container mt-3 mb-3">
    <div class="row">
        <div class="col">
            <div class="card h-100 p-3">
                <div class="title mb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <p class="mb-0">Data SPJ yang Harus Anda Rate</p>
                        <a href="/suratperjalanan" class="btn cl-blue text-white">Lihat Semua</a>
                    </div>
                </div>
                <div id="contentCardSPJ" class="contentCardSPJ"></div>
            </div>
        </div>
    </div>
</div>
<!-- <div class="text-end">
    <button class="btn cl-yellow"><i class="fa-solid fa-angle-right"></i></button>
</div> -->
@endsection

@section('script')
<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    let chartPenilaian;

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

                $('#title_chartPenilaian').text(`Penilaian Yang Diadakan : ${totalSemua} Penilaian`);

                const chartData = {
                    labels: ['Belum Dinilai', 'Telah Dinilai'],
                    datasets: [{
                        label: 'Penilaian',
                        data: [totalBelumDilaksanakan, totalDilaksanakan],
                        backgroundColor: [
                            'rgb(255, 99, 132)',
                            'rgb(54, 162, 235)',
                        ],
                        hoverOffset: 4
                    }]
                };

                const config = {
                    type: 'doughnut',
                    data: chartData,
                };

                if (chartPenilaian) {
                    chartPenilaian.data = chartData;
                    chartPenilaian.update();
                } else {
                    chartPenilaian = new Chart(
                        document.getElementById('totalPenilaianChart'),
                        config
                    );
                }

                const content_JK = $('#content_JK');
                const content_KS = $('#content_KS');
                const content_KC = $('#content_KC');
                const content_KI = $('#content_KI');

                const title_peringkat = $('#title_peringkat');
                const bodyContentPeringkat = $('#bodyContentPeringkat');
                const select_peringkatPenilaian = $('#select_peringkatPenilaian');

                const tbody_dataPengajuanBarang = $('#tbody_dataPengajuanBarang');

                const contentCardSPJ = $('#contentCardSPJ');

                const contentModalBodyKC = $('#bodyContentModalCuti');
                const contentModalBodyKI = $('#bodyContentModalIzin');
                const contentModalBodyKS = $('#bodyContentModalSakit');

                const dataCuti = response.dataCard_first.dataCuti.dataCuti;
                const dataIzin = response.dataCard_first.dataIzin.dataIzin;
                const dataSakit = response.dataCard_first.dataSakit.dataSakit;

                const dataCardFirst = response.dataCard_first ?? 0;

                content_JK.text(`${dataCardFirst.karyawan_aktif} Karyawan`);
                content_KS.text(`${dataCardFirst.dataSakit.totalAbsenSakit} Karyawan`);
                content_KC.text(`${dataCardFirst.dataCuti.totalAbsenCuti} Karyawan`);
                content_KI.text(`${dataCardFirst.dataIzin.totalAbsenIzin} Karyawan`);

                contentModalBodyKC.empty();

                if (dataCuti.length === 0) {
                    contentModalBodyKC.append(`
                        <div class="list-group-item border-0 d-flex align-items-center rounded-3 shadow-sm mb-3 p-3">
                            Tidak ada karyawan yang cuti di quartal ini
                        </div>
                    `);
                } else {
                    const bgClasses = ["cl-yellow", "cl-red", "cl-green", "cl-blue", "cl-grey"];
                    let accordionHtml = `<div class="accordion" id="accordionCuti">`;

                    dataCuti.forEach(function(data, index) {
                        let nama = data.namaKaryawan.trim();
                        let words = nama.split(" ");
                        let initials = words[0].charAt(0).toUpperCase();
                        if (words.length >= 2) {
                            initials += words[1].charAt(0).toUpperCase();
                        }
                        let randomBg = bgClasses[Math.floor(Math.random() * bgClasses.length)];

                        accordionHtml += `
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingCuti${index}">
                                    <button class="accordion-button collapsed" 
                                        type="button" 
                                        data-bs-toggle="collapse" 
                                        data-bs-target="#collapseCuti${index}" 
                                        aria-expanded="false" 
                                        aria-controls="collapseCuti${index}">
                                        ${data.namaKaryawan} - ${data.divisi}
                                    </button>
                                </h2>
                                <div id="collapseCuti${index}" class="accordion-collapse collapse" 
                                    aria-labelledby="headingCuti${index}" 
                                    data-bs-parent="#accordionCuti">
                                    <div class="accordion-body">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar ${randomBg} text-white rounded-circle d-flex justify-content-center align-items-center me-3"
                                                style="width:40px; height:40px; font-weight:bold;">
                                                ${initials}
                                            </div>
                                            <div>
                                                <h6 class="mb-0 fw-semibold">${data.namaKaryawan}</h6>
                                                <small class="text-muted">${data.divisi}</small>
                                                <p><small>${data.alasan}</small></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
        `;
                    });

                    accordionHtml += `</div>`;
                    contentModalBodyKC.append(accordionHtml);
                }


                contentModalBodyKS.empty();

                if (dataSakit.length === 0) {
                    contentModalBodyKS.append(`
                        <div class="list-group-item border-0 d-flex align-items-center rounded-3 shadow-sm mb-3 p-3">
                            Tidak ada karyawan yang sakit di quartal ini
                        </div>
                    `);
                } else {
                    const bgClasses = ["cl-yellow", "cl-red", "cl-green", "cl-blue", "cl-grey"];
                    let accordionHtml = `<div class="accordion" id="accordionSakit">`;

                    dataSakit.forEach(function(data, index) {
                        let nama = data.namaKaryawan.trim();
                        let words = nama.split(" ");
                        let initials = words[0].charAt(0).toUpperCase();
                        if (words.length >= 2) {
                            initials += words[1].charAt(0).toUpperCase();
                        }
                        let randomBg = bgClasses[Math.floor(Math.random() * bgClasses.length)];

                        accordionHtml += `
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingSakit${index}">
                                    <button class="accordion-button collapsed" 
                                        type="button" 
                                        data-bs-toggle="collapse" 
                                        data-bs-target="#collapseSakit${index}" 
                                        aria-expanded="false" 
                                        aria-controls="collapseSakit${index}">
                                        ${data.namaKaryawan} - ${data.divisi}
                                    </button>
                                </h2>
                                <div id="collapseSakit${index}" class="accordion-collapse collapse" 
                                    aria-labelledby="headingSakit${index}" 
                                    data-bs-parent="#accordionSakit">
                                    <div class="accordion-body">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar ${randomBg} text-white rounded-circle d-flex justify-content-center align-items-center me-3"
                                                style="width:40px; height:40px; font-weight:bold;">
                                                ${initials}
                                            </div>
                                            <div>
                                                <h6 class="mb-0 fw-semibold">${data.namaKaryawan}</h6>
                                                <small class="text-muted">${data.divisi}</small>
                                                <p><small>${data.alasan}</small></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                    });

                    accordionHtml += `</div>`;
                    contentModalBodyKS.append(accordionHtml);
                }


                contentModalBodyKI.empty();

                if (dataIzin.length === 0) {
                    contentModalBodyKI.append(`
                        <div class="list-group-item border-0 d-flex align-items-center rounded-3 shadow-sm mb-3 p-3">
                            Tidak ada karyawan yang izin di quartal ini
                        </div>
                    `);
                } else {
                    const bgClasses = ["cl-yellow", "cl-red", "cl-green", "cl-blue", "cl-grey"];
                    let accordionHtml = `<div class="accordion" id="accordionIzin">`;

                    dataIzin.forEach(function(data, index) {
                        let nama = data.namaKaryawan.trim();
                        let words = nama.split(" ");
                        let initials = words[0].charAt(0).toUpperCase();
                        if (words.length >= 2) {
                            initials += words[1].charAt(0).toUpperCase();
                        }
                        let randomBg = bgClasses[Math.floor(Math.random() * bgClasses.length)];

                        accordionHtml += `
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="heading${index}">
                                    <button class="accordion-button collapsed" 
                                        type="button" 
                                        data-bs-toggle="collapse" 
                                        data-bs-target="#collapse${index}" 
                                        aria-expanded="false" 
                                        aria-controls="collapse${index}">
                                        ${data.namaKaryawan} - ${data.divisi}
                                    </button>
                                </h2>
                                <div id="collapse${index}" class="accordion-collapse collapse" 
                                    aria-labelledby="heading${index}" 
                                    data-bs-parent="#accordionIzin">
                                    <div class="accordion-body">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar ${randomBg} text-white rounded-circle d-flex justify-content-center align-items-center me-3"
                                                style="width:40px; height:40px; font-weight:bold;">
                                                ${initials}
                                            </div>
                                            <div>
                                                <h6 class="mb-0 fw-semibold">${data.namaKaryawan}</h6>
                                                <small class="text-muted">${data.divisi}</small>
                                                <p><small>${data.alasan}</small></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                    });

                    accordionHtml += `</div>`;
                    contentModalBodyKI.append(accordionHtml);
                }



                contentCardSPJ.empty();

                const dataSPJ = response.dataSPJ;
                if (dataSPJ.length === 0) {
                    contentCardSPJ.append(`
                        <div class="card p-2 mt-2 text-center">
                            Tidak Ada Pengajuan
                        </div>
                    `);
                } else {
                    function formatTanggal(tgl) {
                        let date = new Date(tgl);
                        let options = {
                            day: 'numeric',
                            month: 'long',
                            year: 'numeric'
                        };
                        return date.toLocaleDateString('id-ID', options);
                    }

                    dataSPJ.forEach(function(data) {
                        contentCardSPJ.append(`
                            <div class="card p-2 mt-2">
                                <h6 class="fw-bold mb-0">${data.karyawan.nama_lengkap ?? 'Tanpa Nama'} - ${data.karyawan.divisi ?? 'Tidak ada divisi'}</h6>
                                <small>
                                    Tipe perjalanan ${data.tipe}, 
                                    Tujuan ${data.tujuan}, 
                                    pada tanggal ${formatTanggal(data.tanggal_berangkat)} s/d ${formatTanggal(data.tanggal_pulang)}
                                    (durasi ${data.durasi} hari)
                                    <p>alasan : ${data.alasan}</p>
                                </small>
                            </div>
                        `);
                    });
                }

                tbody_dataPengajuanBarang.empty();
                const dataPengajuanBarang = response.dataPengajuanBarang;

                if (dataPengajuanBarang.length === 0) {
                    tbody_dataPengajuanBarang.append(`
                        <tr>
                            <td colspan="6">Tidak Ada Pengajuan Barang Baru Baru Ini</td>
                        </tr>
                    `);
                } else {
                    dataPengajuanBarang.forEach(item => {
                        let tanggal = new Date(item.created_at);
                        let options = {
                            weekday: 'long',
                            day: 'numeric',
                            month: 'long',
                            year: 'numeric'
                        };
                        let tanggalFormat = tanggal.toLocaleDateString('id-ID', options);

                        let namaBarang = '';
                        let hargaBarang = '';
                        let jumlahBarang = '';
                        let total = 0;

                        item.detail.forEach(d => {
                            namaBarang += `${d.nama_barang}<br>`;
                            jumlahBarang += `${d.qty}<br>`;
                            let harga = parseFloat(d.harga) * parseInt(d.qty);
                            hargaBarang += `Rp ${harga.toLocaleString('id-ID')}<br>`;
                            total += harga;
                        });

                        tbody_dataPengajuanBarang.append(`
                            <tr>
                                <td>${tanggalFormat}</td>
                                <td>${item.karyawan.nama_lengkap} - ${item.karyawan.divisi}</td>
                                <td>${namaBarang}</td>
                                <td>${jumlahBarang}</td>
                                <td>${hargaBarang}</td>
                                <td>Rp ${total.toLocaleString('id-ID')}</td>
                            </tr>
                        `);
                    });
                }

                select_peringkatPenilaian.empty();
                const Divisi = response.dataDivisi;
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
                                <div class="p-4 text-center rounded-3 shadow-sm border bg-light">
                                    <h6 class="mb-0">Belum ada karyawan yang memiliki peringkat di divisi ini</h6>
                                </div>
                            </div>
                        `);
                    } else {
                        const top3 = dataFiltered.slice(0, 3);
                        top3.forEach((item, index) => {
                            let posisi = index + 1;
                            let tinggiBox = posisi === 1 ? 340 : (posisi === 2 ? 300 : 260);
                            const baseUrl = "{{ asset('storage') }}";
                            cardContainer.append(`
                                <div class="col-4">
                                    <div class="p-3 rounded-3 shadow-sm border" style="height:${tinggiBox}px;">
                                        <div class="position-relative">
                                            <img src="{{ asset('css/doodle-crown.png') }}" class="position-absolute top-0 translate-middle-x" style="width:100px; margin-top:-95px; margin-left: 50px;">
                                            <img src="${baseUrl}/${item.foto}" class="rounded-circle my-3" style="width: 100px; height: 100px; object-fit:cover;">
                                        </div>
                                        <h6 class="mb-0">${item.nama_karyawan}</h6>
                                        <small>${item.divisi}</small>
                                    </div>
                                    <h5 class="mt-2">${posisi}</h5>
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
                            <div class="d-flex align-items-center mb-2 border-bottom pb-2">
                                <span class="me-2 fw-bold">${currentRank}.</span>
                                ${
                                    item.foto 
                                    ? `<img src="${baseUrl}/${item.foto}" class="rounded-circle me-2" style="width:40px; height:40px; object-fit:cover;">`
                                    : (() => {
                                        const namaSplit = item.nama_karyawan.split(" ");
                                        const inisial = namaSplit[0].charAt(0).toUpperCase() + (namaSplit[1] ? namaSplit[1].charAt(0).toUpperCase() : "");
                                        return `<div class="rounded-circle bg-primary text-white d-flex justify-content-center align-items-center me-2" 
                                                    style="width:40px; height:40px; font-weight:bold; font-size:14px;">
                                                    ${inisial}
                                                </div>`;
                                    })()
                                }
                                <div>
                                    <div>${item.nama_karyawan}</div>
                                    <small class="text-muted">${item.divisi}</small>
                                </div>
                                <div class="ms-auto me-4 fw-bold">${item.total_nilai}</div>
                                 <div class="progress" style="width: 400px;"> 
                                <div class="progress-bar" role="progressbar" 
                                    style="width: ${item.total_nilai}%;" 
                                    aria-valuenow="${item.total_nilai}" 
                                    aria-valuemin="0" aria-valuemax="100">
                                ${item.total_nilai}%
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
<script>
    const ctx2 = document.getElementById('weeklyGoalChart').getContext('2d');

    new Chart(ctx2, {
        type: 'doughnut',
        data: {
            datasets: [{
                    label: 'General Manager',
                    data: [98, 2],
                    backgroundColor: ['#26C6F9', '#f0f0f0'],
                    borderWidth: 0,
                    circumference: 360,
                    rotation: -90,
                    cutout: '60%',
                    radius: '100%'
                },
                {
                    label: 'Education Manager',
                    data: [85, 15],
                    backgroundColor: ['#2E86FC', '#f0f0f0'],
                    borderWidth: 0,
                    circumference: 360,
                    rotation: -90,
                    cutout: '60%',
                    radius: '80%'
                },
                {
                    label: 'SPV Sales',
                    data: [70, 30],
                    backgroundColor: ['#28a745', '#f0f0f0'],
                    borderWidth: 0,
                    circumference: 360,
                    rotation: -90,
                    cutout: '60%',
                    radius: '60%'
                },

                {
                    label: 'Koordinator ITSM',
                    data: [90, 10],
                    backgroundColor: ['#b71cffff', '#f0f0f0'],
                    borderWidth: 0,
                    circumference: 360,
                    rotation: -90,
                    cutout: '60%',
                    radius: '40%'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    enabled: false
                }
            }
        }
    });
</script>
<script>
    const ctx = document.getElementById('topProductsChart').getContext('2d');

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Boots4', 'Reign Pro', 'Slick', 'Falcon', 'Sparrow', 'Hideway', 'Freya'],
            datasets: [{
                    label: '2019',
                    data: [42, 81, 85, 70, 78, 48, 80],
                    backgroundColor: '#2E86FC', // biru
                    borderRadius: 4,
                    barPercentage: 0.5
                },
                {
                    label: '2018',
                    data: [84, 72, 60, 50, 48, 68, 88],
                    backgroundColor: '#d6e0f5',
                    borderRadius: 4,
                    barPercentage: 0.5
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        color: '#a0a0a0'
                    }
                },
                y: {
                    beginAtZero: true,
                    grid: {
                        color: '#f2f4f8'
                    },
                    ticks: {
                        stepSize: 20
                    }
                }
            },
            plugins: {
                legend: {
                    labels: {
                        usePointStyle: true,
                        pointStyle: 'circle',
                        font: {
                            size: 13
                        }
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(0,0,0,0.7)',
                    titleFont: {
                        size: 14
                    },
                    bodyFont: {
                        size: 13
                    },
                    padding: 8
                }
            }
        }
    });
</script>
@endsection