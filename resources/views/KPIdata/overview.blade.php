@extends('databasekpi.berandaKPI')

@section('contentKPI')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-chart-radial-gauge@3.1.0/dist/chartjs-chart-radial-gauge.min.js"></script>

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
            </span> KPI
        </h3>
        <nav aria-label="breadcrumb">
            <ul class="breadcrumb">
                <li class="breadcrumb-item active" aria-current="page">
                    <span></span>Overview <i class="mdi mdi-alert-circle-outline icon-sm text-primary align-middle" data-bs-toggle="tooltip" data-bs-placement="top" title="Lihat overview data seputar KPI."></i>
                </li>
            </ul>
        </nav>
    </div>

    <div class="container-fluid py-4">
        <div class="row mb-4">
            <div class="col-sm-4 grid-margin">
                <div class="card shadow-sm h-100">
                    <div class="card-body d-flex flex-column" style="padding: 0;">
                        <div class="d-flex justify-content-between align-items-center" style="padding: 25px;">
                            <h6 class="card-title card-title-dash fw-medium" style="margin: 0;">Target Gagal</h6>
                            <span class="text-danger text-medium d-flex align-items-center" style="margin: 0;">
                                <i class="mdi mdi-trending-up me-1 icon-sm"></i>+6%
                            </span>
                        </div>
                        <div style="height: 30px; overflow: hidden; margin: 0; padding: 0;">
                            <canvas id="chartTotalKPIgagal" height="30" style="margin: 0; padding: 0; display: block;"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-sm-4 grid-margin">
                <div class="card shadow-sm h-100">
                    <div class="card-body d-flex flex-column" style="padding: 0;">
                        <div class="d-flex justify-content-between align-items-center" style="padding: 25px;">
                            <h6 class="card-title card-title-dash fw-medium" style="margin: 0;">Traget Berhasil</h6>
                            <span class="text-success text-medium d-flex align-items-center" style="margin: 0;">
                                <i class="mdi mdi-trending-down me-1 icon-sm"></i>+3%
                            </span>
                        </div>
                        <div style="height: 30px; overflow: hidden; margin: 0; padding: 0;">
                            <canvas id="chartLeaves" height="30" style="margin: 0; padding: 0; display: block;"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-sm-4 grid-margin">
                <div class="card shadow-sm h-100">
                    <div class="card-body d-flex flex-column" style="padding: 0;">
                        <div class="d-flex justify-content-between align-items-center" style="padding: 25px;">
                            <h6 class="card-title card-title-dash fw-medium" style="margin: 0;">Melebihi Target</h6>
                            <span class="text-danger text-medium d-flex align-items-center" style="margin: 0;">
                                <i class="mdi mdi-trending-up me-1 icon-sm"></i>-3%
                            </span>
                        </div>
                        <div style="height: 30px; overflow: hidden; margin: 0; padding: 0;">
                            <canvas id="chartNewEmployees" height="30" style="margin: 0; padding: 0; display: block;"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-8 mb-3">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <h5 class="card-title">Perkembangan KPI Perusahaan</h5>
                        <div>
                            <canvas id="chartCompanyTrend"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4 mb-3">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <div class="mt-2">
                            <h5 class="card-title">Perlu Perhatian</h5>
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span>Ani Sri</span>
                                    <span class="badge bg-warning">-2%</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span>Beni</span>
                                    <span class="badge bg-warning">-1%</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span>Felix Irwana</span>
                                    <span class="badge bg-warning text-dark">-3%</span>
                                </li>
                            </ul>
                        </div>

                        <div class="mt-2">
                            <h5 class="card-title">Perlu Perhatian Khusus</h5>
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span>Ahmad Fauzi</span>
                                    <span class="badge bg-danger">-15%</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span>Siti Rahayu</span>
                                    <span class="badge bg-danger">-18%</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span>Budi Santoso</span>
                                    <span class="badge bg-danger text-white">+23%</span>
                                </li>
                            </ul>
                        </div>

                        <div class="mt-3 d-flex justify-center text-center align-center">
                            <div class="border-top mt-2">
                                <div class="mt-3">
                                    Diambil Berdasarkan Perkembangan Setiap Karyawan
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">Data KPI Seluruh Karyawan</h5>
                        <table class="table table-striped table-hover" id="kpiTable">
                            <thead>
                                <tr>
                                    <th>Nama</th>
                                    <th>Target</th>
                                    <th>Pencapaian</th>
                                    <th>Status</th>
                                    <th>Perubahan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Ahmad Fauzi</td>
                                    <td>100</td>
                                    <td>85</td>
                                    <td><span class="badge bg-danger">Tidak Tercapai</span></td>
                                    <td class="text-danger">↓ 15%</td>
                                </tr>
                                <tr>
                                    <td>Siti Rahayu</td>
                                    <td>100</td>
                                    <td>90</td>
                                    <td><span class="badge bg-warning text-dark">Tidak Tercapai</span></td>
                                    <td class="text-danger">↓ 10%</td>
                                </tr>
                                <tr>
                                    <td>Dina Marlina</td>
                                    <td>100</td>
                                    <td>100</td>
                                    <td><span class="badge bg-success">Tepat Target</span></td>
                                    <td class="text-success">↑ 2%</td>
                                </tr>
                                <tr>
                                    <td>Rudi Hartono</td>
                                    <td>100</td>
                                    <td>115</td>
                                    <td><span class="badge bg-primary">Melebihi Target</span></td>
                                    <td class="text-success">↑ 15%</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    function createMiniAreaChart(ctx, data, color = '#b66dff37', borderColor = '#B66DFF') {
        return new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
                datasets: [{
                    label: '',
                    data: data,
                    fill: true,
                    backgroundColor: color,
                    borderColor: borderColor,
                    tension: 0.1, // Lebih datar
                    pointRadius: 0,
                    borderWidth: 1.5,
                    lineTension: 0.1
                }]
            },
            options: {
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        enabled: false
                    }
                },
                scales: {
                    x: {
                        display: false
                    },
                    y: {
                        display: false,
                        beginAtZero: true,
                        max: Math.max(...data) * 1.1
                    }
                },
                responsive: true,
                maintainAspectRatio: false,
                layout: {
                    padding: {
                        top: 0,
                        right: 0,
                        bottom: 0,
                        left: 0
                    }
                }
            }
        });
    }

    createMiniAreaChart(document.getElementById('chartTotalKPIgagal').getContext('2d'), [23, 65, 50, 35, 58, 85, 23, 65, 50, 35, 58, 85, 23, 65, 50, 35, 58, 85], '#b66dff3d', '#B66DFF');
    createMiniAreaChart(document.getElementById('chartLeaves').getContext('2d'), [40, 35, 30, 32, 28, 25], 'rgba(255, 99, 132, 0.2)', '#e74c3c');
    createMiniAreaChart(document.getElementById('chartNewEmployees').getContext('2d'), [80, 85, 90, 95, 100, 105], 'rgba(75, 192, 192, 0.2)', '#1abc9c');

    const randomData = Array.from({
        length: 18
    }, () => Math.floor(Math.random() * 100));

    // Chart besar tetap sama
    function createDummyLineChart(ctx, color = '#b66dff2a', borderColor = '#B66DFF') {
        return new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [{
                    label: 'Perkembangan',
                    data: [65, 59, 80, 81, 56, 75],
                    fill: true,
                    backgroundColor: color,
                    borderColor: borderColor,
                    tension: 0.3
                }]
            },
            options: {
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }

    createDummyLineChart(document.getElementById('chartCompanyTrend').getContext('2d'));
</script>
@endsection