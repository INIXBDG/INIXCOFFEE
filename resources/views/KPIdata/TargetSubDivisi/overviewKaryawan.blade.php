@extends('databasekpi.berandaKPI')

@section('contentKPI')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <div class="modal fade" id="detailTargetModal" tabindex="-1" aria-labelledby="detailTargetModalLable" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div id="bodyContentDetailTarget" class="p-3"></div>
            </div>
        </div>
    </div>

    <div class="content-wrapper">
        <div class="page-header">
            <h3 class="page-title">
                <span class="page-title-icon bg-gradient-primary text-white me-2">
                    <i class="mdi mdi-account"></i>
                </span>
                Dashboard Pribadi KPI
            </h3>
        </div>

        <div class="container-fluid bg-white p-4 rounded-4">
            <div class="mb-4 p-4 rounded-4 shadow-sm bg-gradient-primary text-white">
                <div class="d-flex align-items-center">
                    <div class="me-4">
                        <div class="bg-white text-primary rounded-circle d-flex align-items-center justify-content-center"
                            style="width: 80px; height: 80px; font-size: 2rem;">
                            <i class="fa-solid fa-user"></i>
                        </div>
                    </div>
                    <div class="text-white">
                        <h3 class="fw-bold mb-1" id="userName">Muhamad Ardhan H</h3>

                        <div class="d-flex flex-wrap gap-2">
                            <span class="badge bg-light text-primary px-3 py-2 rounded-pill">
                                <i class="fa-solid fa-briefcase me-1"></i>
                                <span id="userJabatan">Koordinator ITSM</span>
                            </span>

                            <span class="badge bg-light text-primary px-3 py-2 rounded-pill">
                                <i class="fa-solid fa-building me-1"></i>
                                <span id="userDivisi">IT Service Management</span>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-muted">Total Target</small>
                                <h4 class="fw-bold mb-0" id="totalTarget">0 Target</h4>
                            </div>
                            <div class="d-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary rounded-3"
                                style="width:42px;height:42px;">
                                <i class="fa-solid fa-bullseye"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-muted">Rata-rata Progress</small>
                                <h4 class="fw-bold mb-0 text-primary" id="rataProgress">0%</h4>
                            </div>
                            <div class="d-flex align-items-center justify-content-center bg-success bg-opacity-10 text-success rounded-3"
                                style="width:42px;height:42px;">
                                <i class="fa-solid fa-chart-line"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-muted">KPI Aktif</small>
                                <h4 class="fw-bold mb-0 text-warning" id="kpiAktif">0</h4>
                            </div>
                            <div class="d-flex align-items-center justify-content-center bg-warning bg-opacity-10 text-warning rounded-3"
                                style="width:42px;height:42px;">
                                <i class="fa-solid fa-clock"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-muted">KPI Selesai</small>
                                <h4 class="fw-bold mb-0 text-success" id="kpiSelesai">0</h4>
                            </div>
                            <div class="d-flex align-items-center justify-content-center bg-info bg-opacity-10 text-info rounded-3"
                                style="width:42px;height:42px;">
                                <i class="fa-solid fa-check-circle"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-8">
                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-body">
                            <h6 class="fw-semibold mb-3"><i class="fa-solid fa-chart-bar me-2"></i>Performa KPI Saya</h6>
                            <div style="height: 350px;">
                                <canvas id="performanceChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-body">
                            <h6 class="fw-semibold mb-3"><i class="fa-solid fa-chart-pie me-2"></i>Status Target</h6>
                            <div style="height: 250px;">
                                <canvas id="statusPieChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-semibold mb-0"><i class="fa-solid fa-list-check me-2"></i>Semua Target Pribadi Saya
                        </h6>
                        <div class="btn-group">
                            <button class="btn btn-sm btn-outline-primary active"
                                onclick="filterTargets('all')">Semua</button>
                            <button class="btn btn-sm btn-outline-primary" onclick="filterTargets('active')">Aktif</button>
                            <button class="btn btn-sm btn-outline-primary"
                                onclick="filterTargets('completed')">Selesai</button>
                        </div>
                    </div>

                    <div class="row" id="targetCardContainer">
                        <div class="col-12">
                            <div class="text-center py-5">
                                <div class="spinner-border text-primary" role="status"
                                    style="width: 3rem; height: 3rem;">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="mt-3 text-muted">Loading data...</p>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>


    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        let performanceChart = null;
        let statusPieChart = null;
        let allTargetsData = [];
        let currentFilter = 'all';

        $(document).ready(function() {
            loadDataPersonal();
        });

        function loadDataPersonal() {
            let tahun = {{ now()->year }};

            $.ajax({
                url: "{{ route('kpi.overview.dataPersonal') }}",
                type: 'GET',
                data: {
                    tahun: tahun
                },
                dataType: 'json',
                beforeSend: function() {
                    $('#userName').text('Loading...');
                    $('#userJabatan').text('Loading...');
                    $('#userDivisi').text('Loading...');
                    $('#totalTarget').text('Loading...');
                    $('#rataProgress').text('Loading...');
                    $('#kpiAktif').text('Loading...');
                    $('#kpiSelesai').text('Loading...');

                    $('#targetCardContainer').html(`
                    <div class="col-12">
                        <div class="text-center py-5">
                            <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-3 text-muted">Loading data...</p>
                        </div>
                    </div>
                `);
                },
                success: function(response) {
                    if (response.success) {
                        updateHeader(response);
                        updateStats(response);
                        updateCharts(response.statistik_per_target, response.distribusi_status);
                        updateTargetCards(response.daftar_target_pribadi);
                        allTargetsData = response.daftar_target_pribadi;
                    } else {
                        showError(response.message || 'Terjadi kesalahan saat memuat data');
                    }
                },
                error: function(xhr, status, error) {
                    showError('Gagal memuat data. Silakan coba lagi.');
                    console.error('AJAX Error:', error);
                }
            });
        }

        function updateHeader(data) {
            $('#userName').text(data.user_info?.nama || 'User');
            $('#userJabatan').text(data.user_info?.jabatan || '-');
            $('#userDivisi').text(data.user_info?.divisi || '-');
        }

        function updateStats(data) {
            $('#totalTarget').text(data.total_target + ' Target');
            $('#rataProgress').text(Math.round(data.rata_rata_progress) + '%');
            $('#kpiAktif').text(data.kpi_aktif);
            $('#kpiSelesai').text(data.kpi_selesai);
        }

        function updateCharts(statistikTargets, distribusiStatus) {
            if (performanceChart) {
                performanceChart.destroy();
            }
            if (statusPieChart) {
                statusPieChart.destroy();
            }

            let targetLabels = statistikTargets.map(item => {
                return item.judul.length > 20 ? item.judul.substring(0, 20) + '...' : item.judul;
            });
            let targetProgress = statistikTargets.map(item => Math.round(item.progress || 0));

            const performanceCtx = document.getElementById('performanceChart').getContext('2d');
            performanceChart = new Chart(performanceCtx, {
                type: 'bar',
                data: {
                    labels: targetLabels,
                    datasets: [{
                        label: 'Progress (%)',
                        data: targetProgress,
                        backgroundColor: targetProgress.map(progress => {
                            if (progress >= 75) return 'rgba(40, 207, 180, 0.7)';
                            if (progress >= 50) return 'rgba(94, 0, 188, 0.3)';
                            return 'rgba(254, 124, 150, 0.7)';
                        }),
                        borderColor: targetProgress.map(progress => {
                            if (progress >= 75) return 'rgba(40, 207, 180, 1)';
                            if (progress >= 50) return 'rgba(94, 0, 188, 1)';
                            return 'rgba(254, 124, 150, 1)';
                        }),
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return `Progress: ${context.parsed.y}%`;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100,
                            ticks: {
                                callback: function(value) {
                                    return value + '%';
                                }
                            }
                        }
                    }
                }
            });

            let pieLabels = Object.keys(distribusiStatus);
            let pieData = Object.values(distribusiStatus);

            const statusCtx = document.getElementById('statusPieChart').getContext('2d');
            statusPieChart = new Chart(statusCtx, {
                type: 'doughnut',
                data: {
                    labels: pieLabels,
                    datasets: [{
                        data: pieData,
                        backgroundColor: [
                            '#28CFB4',
                            '#FE7C96',
                            '#FED718'
                        ],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let label = context.label || '';
                                    let value = context.parsed || 0;
                                    let total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    let percentage = total > 0 ? Math.round((value / total) * 100) : 0;
                                    return `${label}: ${value} target (${percentage}%)`;
                                }
                            }
                        }
                    },
                    cutout: '60%'
                }
            });
        }

        function updateTargetCards(targets) {
            if (!targets || targets.length === 0) {
                $('#targetCardContainer').html(`
                <div class="col-12">
                    <div class="card border-0 rounded-4 bg-light h-100">
                        <div class="card-body text-center py-5">
                            <i class="fa-solid fa-inbox fa-4x text-muted mb-3"></i>
                            <h5 class="text-muted">Belum ada target KPI</h5>
                            <p class="text-muted">Silakan tambahkan target KPI Anda</p>
                        </div>
                    </div>
                </div>
            `);
                return;
            }

            let html = '';
            targets.forEach((target, index) => {
                let progressBarColor = '';
                if (target.progress >= 75) progressBarColor = 'bg-success';
                else if (target.progress >= 50) progressBarColor = 'bg-primary';
                else progressBarColor = 'bg-warning';

                let badgeClass = target.status === 'Selesai' ? 'bg-success' : 'bg-warning text-dark';

                let cardBorderColor = '';
                if (target.progress >= 75) cardBorderColor = 'border-3 border-success';
                else if (target.progress >= 50) cardBorderColor = 'border-3 border-primary';
                else cardBorderColor = 'border-3 border-warning';

                let progressBarBg = 'bg-light';

                let statusBadgeColor = target.status === 'Selesai' ? 'bg-success' : 'bg-warning text-dark';

                let targetTextColor = 'text-warning';
                let progressTextColor = 'text-primary';

                let yearBadgeColor = 'bg-primary';

                html += `
                    <div class="col-12 col-md-6 col-lg-4 mb-4">
                        <button style="background: none; border: none; padding: 0; margin: 0; text-align: left; display: block; width: 100%;" id="buttonDetailTarget" data-id="${target.id}" data-bs-toggle="modal" data-bs-target="#detailTargetModal">
                            <div class="card border-0 rounded-4 h-100 ${cardBorderColor} shadow-lg overflow-hidden">
                                <div class="card-body p-4">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <p class="${yearBadgeColor} fs-6 py-1 px-3">${target.periode}</p>
                                        <p class="${statusBadgeColor} fs-6 py-1 px-3">${target.status}</p>
                                    </div>

                                    <h5 class="card-title fw-bold mb-2 text-dark">
                                        <span class="d-inline-block bg-gradient-to-r from-purple-500 to-blue-600 text-black px-2 py-1 rounded-2">
                                            ${target.judul}
                                        </span>
                                    </h5>
                                    <p class="text-muted small mb-3">${target.deskripsi || '-'}</p>

                                    <div class="mb-4">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <small class="text-muted fw-semibold">Progress</small>
                                            <small class="fw-bold ${progressTextColor}">${target.progress_display}</small>
                                        </div>
                                        <div class="progress ${progressBarBg} rounded-3" style="height: 12px;">
                                            <div class="progress-bar ${progressBarColor} rounded-3" 
                                                role="progressbar" 
                                                style="width: ${target.progress}%" 
                                                aria-valuenow="${target.progress}" 
                                                aria-valuemin="0" 
                                                aria-valuemax="100"></div>
                                        </div>
                                    </div>

                                    <div class="row g-3 mb-4">
                                        <div class="col-6">
                                            <small class="text-muted d-block fw-semibold">Target</small>
                                            <strong class="${targetTextColor} fs-5">${target.target}</strong>
                                        </div>
                                        <div class="col-6">
                                            <small class="text-muted d-block fw-semibold">Progress</small>
                                            <strong class="${progressTextColor} fs-5">${target.progress}%</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </button>
                    </div>
                `;
            });

            $('#targetCardContainer').html(html);
        }

        function filterTargets(filter) {
            currentFilter = filter;

            let filteredTargets = allTargetsData;

            if (filter === 'active') {
                filteredTargets = allTargetsData.filter(target => target.status === 'Aktif');
            } else if (filter === 'completed') {
                filteredTargets = allTargetsData.filter(target => target.status === 'Selesai');
            }

            updateTargetCards(filteredTargets);
        }

        function viewTargetDetail(targetId) {
            Swal.fire({
                title: 'Detail Target',
                text: 'Fitur detail target akan ditampilkan di sini',
                icon: 'info',
                confirmButtonText: 'OK'
            });
        }

        function updateProgress() {
            Swal.fire({
                title: 'Update Progress',
                text: 'Silakan update progress KPI Anda melalui halaman target',
                icon: 'info',
                confirmButtonText: 'Lihat Target'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = "{{ route('kpi.index') }}";
                }
            });
        }

        function viewAchievements() {
            Swal.fire({
                title: 'Pencapaian KPI',
                text: 'Halaman pencapaian KPI akan segera hadir',
                icon: 'info'
            });
        }

        function viewHistory() {
            Swal.fire({
                title: 'Riwayat KPI',
                text: 'Halaman riwayat KPI akan segera hadir',
                icon: 'info'
            });
        }

        function showError(message) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: message,
                confirmButtonText: 'OK'
            });
        }

        $(document).on('click', '#buttonDetailTarget', function() {
            let id = $(this).data('id');

            $.ajax({
                url: "{{ route('kpi.detail') }}",
                method: 'GET',
                data: {
                    id
                },
                dataType: 'json',
                success: function(response) {
                    const body = $('#bodyContentDetailTarget');
                    if (body.length === 0) {
                        console.error("Elemen #bodyContentDetailTarget tidak ditemukan!");
                        return;
                    }

                    body.empty();

                    let detailArray = response.detail;
                    let data = detailArray && detailArray.length > 0 ? detailArray[0].data : null;

                    if (!data) {
                        body.append(`
                        <div class="modal-header">
                            <h5 class="modal-title">Detail Target</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body text-center">
                            Belum ada data
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-danger" data-bs-dismiss="modal">Tutup</button>
                        </div>
                    `);
                    } else {
                        const monthlyData = data.data_detail.monthly_data || {};
                        const dailyData = data.data_detail.daily_breakdown_per_month || {};

                        const dateNow = "{{ now()->format('Y-m-d') }}";
                        const startOfYear = "{{ now()->startOfYear()->format('Y-m-d') }}";
                        const tenggatWaktu = data.tenggat_waktu;

                        function isDateBefore(date1, date2) {
                            return date1 < date2;
                        }

                        function isDateAfter(date1, date2) {
                            return date1 > date2;
                        }

                        let Tercapai;
                        if (isDateBefore(dateNow, startOfYear)) {
                            Tercapai = "Belum Dimulai";
                        } else if (isDateAfter(dateNow, tenggatWaktu) || dateNow === tenggatWaktu) {
                            Tercapai = data.data_detail.progress >= data.nilai_target ?
                                "Mencapai Target" : "Target Gagal";
                        } else {
                            Tercapai = data.data_detail.progress >= data.nilai_target ?
                                "Mencapai Target" : "Sedang Berjalan";
                        }

                        let targetValue, progressValue, gapValue;
                        if (data.tipe_target === "persen") {
                            targetValue = data.nilai_target + ' %';
                            progressValue = data.data_detail.progress + ' %';
                            gapValue = data.data_detail.gap + ' %';
                        } else if (data.tipe_target === "rupiah") {
                            const formatter = new Intl.NumberFormat('id-ID', {
                                style: 'currency',
                                currency: 'IDR',
                                minimumFractionDigits: 0,
                                maximumFractionDigits: 0
                            });
                            targetValue = formatter.format(data.nilai_target);
                            progressValue = formatter.format(data.data_detail.progress);
                            gapValue = formatter.format(Math.abs(data.data_detail.gap));
                        } else {
                            targetValue = data.nilai_target;
                            progressValue = data.data_detail.progress;
                            gapValue = data.data_detail.gap;
                        }

                        let bgCard;

                        if (Tercapai === "Mencapai Target") {
                            bgCard = "success";
                        } else if (Tercapai === "Target Gagal") {
                            bgCard = "danger";
                        } else if (Tercapai === "Sedang Berjalan") {
                            bgCard = "warning";
                        } else if (Tercapai === "Belum Berjalan") {
                            bgCard = "secondary";
                        } else {
                            bgCard = "secondary";
                        }

                        let textTitle;

                        const pieChart = data.data_detail.pie_chart || {
                            above: 0,
                            below: 0
                        };
                        const dataPieChart = {
                            labels: ['Above', 'Below'],
                            datasets: [{
                                label: 'Jumlah',
                                data: [pieChart.above ?? 0, pieChart.below ?? 0],
                                backgroundColor: ['#B66DFF', '#FE7C96'],
                                hoverOffset: 4
                            }]
                        };

                        setTimeout(() => {
                            const ctx = document.getElementById('MyChartDoughtnut');
                            if (ctx) {
                                new Chart(ctx, {
                                    type: 'doughnut',
                                    data: dataPieChart,
                                    options: {
                                        responsive: true,
                                        maintainAspectRatio: false,
                                        plugins: {
                                            legend: {
                                                position: 'bottom',
                                                labels: {
                                                    boxWidth: 12,
                                                    padding: 15
                                                }
                                            }
                                        }
                                    }
                                });
                            }
                        }, 0);

                        const karyawanList = Array.isArray(data.karyawan) ? data.karyawan : [data
                            .karyawan
                        ];
                        let no = 1;
                        const karyawanHtml = karyawanList.map(item => `
                        <div class="d-flex align-items-center py-2 participant-item">
                            <div class="avatar me-3">${no++}</div>
                            <div class="flex-grow-1">
                                <div class="fw-semibold text-dark small">${item.nama_lengkap}</div>
                                <div class="text-muted small">${item.jabatan}</div>
                            </div>
                        </div>
                    `).join('');

                        body.append(`
                        <div class="modal-header border-0 pb-0">
                            <h5 class="modal-title fw-bold">${data.judul}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>

                        <div class="modal-body pt-3">
                            <div class="container-fluid p-3">
                                <div class="row g-4">
                                    <div class="col-lg-8">
                                        <div class="card shadow h-100">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between align-items-center mb-3">
                                                    <span class="badge bg-primary">${data.jangka_target}</span>
                                                    <span class="badge bg-${bgCard}">${Tercapai}</span>
                                                </div>

                                                <div class="row text-center mb-3">
                                                    <div class="col">
                                                        <small class="text-muted d-block">Target</small>
                                                        <h4 class="fw-bold mb-0" style="font-size: 25px">${targetValue}</h4>
                                                    </div>
                                                    <div class="col">
                                                        <small class="text-muted d-block">Progress</small>
                                                        <h1 class="fw-bold text-${bgCard} mb-0" style="font-size: 55px;">${progressValue}</h1>
                                                    </div>
                                                    <div class="col">
                                                        <small class="text-muted d-block">Gap</small>
                                                        <h4 class="fw-bold text-danger mb-0" style="font-size: 25px">-${gapValue}</h4>
                                                    </div>
                                                </div>

                                                <div class="position-relative mb-3">
                                                    <div class="progress" style="height:18px;">
                                                        <div class="progress-bar bg-${bgCard} progress-bar-striped progress-bar-animated"
                                                            style="width: ${data.data_detail.progress}%"></div>
                                                    </div>
                                                    <div class="position-absolute bg-light top-0" style="left:${data.nilai_target}%; height:18px; width:2px;"></div>
                                                </div>

                                                <div class="text-muted mb-4">
                                                    <i class="fa-solid fa-calendar-days me-1"></i>
                                                    Deadline: <strong>${data.tenggat_waktu}</strong>
                                                </div>

                                                <div class="row g-4">
                                                    <div class="col-md-6">
                                                        <div class="card border-0 shadow-sm rounded-4 kpi-card">
                                                            <div class="card-body px-4 py-3">
                                                                <div class="d-flex align-items-center mb-3">
                                                                    <div class="me-2 rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center" style="width:36px;height:36px;">
                                                                        <i class="fa-solid fa-chart-line text-primary"></i>
                                                                    </div>
                                                                    <h6 class="mb-0 fw-semibold text-secondary">INFORMASI KPI</h6>
                                                                </div>
                                                                <div class="row mb-3">
                                                                    <div class="col-4 label">KPI Divisi</div>
                                                                    <div class="col-8 value">${data.divisi_kpi}</div>
                                                                </div>
                                                                <div class="row mb-3">
                                                                    <div class="col-4 label">KPI Jabatan</div>
                                                                    <div class="col-8 value">${data.jabatan_kpi}</div>
                                                                </div>
                                                                <div class="row">
                                                                    <div class="col-4 label">Pembuat</div>
                                                                    <div class="col-8 value">${data.pembuat}</div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <div class="card border-0 shadow-sm rounded-4 participant-card h-100">
                                                            <div class="card-body px-4 py-3">
                                                                <div class="d-flex align-items-center mb-3">
                                                                    <div class="me-2 rounded-circle bg-success bg-opacity-10 d-flex align-items-center justify-content-center" style="width:36px;height:36px;">
                                                                        <i class="fa-solid fa-users text-success"></i>
                                                                    </div>
                                                                    <h6 class="mb-0 fw-semibold text-secondary">KARYAWAN</h6>
                                                                </div>
                                                                <div class="participant-list" style="overflow-y: scroll; max-height: 140px;">
                                                                    ${karyawanHtml}
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                   <div class="col-lg-4">
                                        <div class="card shadow h-100">
                                            <div class="card border-0 shadow-sm rounded-4 h-100">
                                                <div class="card-body d-flex flex-column">
                                                    <h6 class="fw-semibold mb-3 text-secondary">
                                                    Chart ${data.condition}
                                                    </h6>

                                                    <div class="chart-container flex-grow-1">
                                                        <canvas id="MyChartDoughtnut"></canvas>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-4">
                                    <div class="card shadow-sm border-0 rounded-4">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between mb-3">
                                                <h6 class="fw-semibold mb-0">Statistik ${data.condition}</h6>
                                                <div class="d-flex gap-2">
                                                    <select class="form-select form-select-sm" id="filterType">
                                                        <option value="year">Per Tahun</option>
                                                        <option value="month">Per Bulan</option>
                                                    </select>
                                                    <select class="form-select form-select-sm d-none" id="filterMonth">
                                                        <option value="">Pilih Bulan</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div style="height:300px">
                                                <canvas id="StatisticChart"></canvas>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer border-0">
                            <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Tutup</button>
                        </div>
                    `);

                        const NAMA_BULAN = [
                            'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
                        ];

                        function getNamaBulan(tahunBulan) {
                            const parts = tahunBulan.split('-');
                            if (parts.length < 2) return tahunBulan;
                            const bulanIndex = parseInt(parts[1], 10) - 1;
                            return NAMA_BULAN[bulanIndex] || tahunBulan;
                        }

                        let statisticChart = null;
                        const statisticCtx = document.getElementById('StatisticChart');

                        function renderStatistic(labels, values, label) {
                            if (statisticChart) statisticChart.destroy();

                            const maxValue = values.length > 0 ? Math.max(...values) : 0;
                            const suggestedMax = maxValue + 3;

                            statisticChart = new Chart(statisticCtx, {
                                type: 'line',
                                data: {
                                    labels,
                                    datasets: [{
                                        label,
                                        data: values,
                                        borderColor: '#4e73df',
                                        backgroundColor: 'rgba(78, 115, 223, 0.1)',
                                        tension: 0.4,
                                        fill: true
                                    }]
                                },
                                options: {
                                    responsive: true,
                                    maintainAspectRatio: false,
                                    scales: {
                                        y: {
                                            beginAtZero: true,
                                            suggestedMax: suggestedMax,
                                            ticks: {
                                                count: 6,
                                                precision: 0,
                                                callback: function(value) {
                                                    return Math.round(value);
                                                }
                                            }
                                        }
                                    }
                                }
                            });
                        }

                        const monthLabels = Object.keys(monthlyData).map(key => getNamaBulan(key));
                        const monthValues = Object.values(monthlyData);
                        renderStatistic(monthLabels, monthValues, 'Rata-rata');

                        $('#filterType').off('change').on('change', function() {
                            if (this.value === 'month') {
                                $('#filterMonth').removeClass('d-none').empty().append(
                                    '<option value="">Pilih Bulan</option>');
                                Object.keys(dailyData).forEach(monthKey => {
                                    $('#filterMonth').append(
                                        `<option value="${monthKey}">${getNamaBulan(monthKey)}</option>`
                                    );
                                });
                                if (statisticChart) statisticChart.destroy();
                            } else {
                                $('#filterMonth').addClass('d-none');
                                renderStatistic(monthLabels, monthValues, 'Rata-rata');
                            }
                        });

                        $('#filterMonth').off('change').on('change', function() {
                            const selectedMonth = this.value;
                            if (!selectedMonth || !dailyData[selectedMonth]) return;

                            const dayLabels = Object.keys(dailyData[selectedMonth]).map(d => d
                                .substring(8));
                            const dayValues = Object.values(dailyData[selectedMonth]);
                            renderStatistic(dayLabels, dayValues,
                                `Tanggal ${getNamaBulan(selectedMonth)}`);
                        });
                    }

                    const modalEl = document.getElementById('detailTargetModal');
                    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                    modal.show();
                },
                error: function() {
                    Swal.fire('Error', 'Gagal memuat detail target', 'error');
                }
            });
        });
    </script>
@endsection
