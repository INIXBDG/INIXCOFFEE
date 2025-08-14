@extends('databasekpi.berandaKPI')

@section('contentKPI')
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
    }

    .titik-tiga {
        color: #999;
        cursor: pointer;
    }

    .angka-persentase {
        font-size: 24px;
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
</style>

<div class="container mt-4">
    <div class="wadah-kartu-indikator">
        <div class="container">
            <div class="row">
                <div class="col-sm mb-2">
                    <div class="kartu-statistik">
                        <div class="kepala-kartu">
                            <div class="ikon-bulat telepon">
                                <i class="fa-solid fa-user-tie"></i>
                            </div>
                            <span class="judul-kartu">Jumlah karyawan</span>
                        </div>
                        <div class="angka-persentase">
                            15 karyawan
                        </div>
                    </div>
                </div>
                <div class="col-sm mb-2">
                    <div class="kartu-statistik">
                        <div class="kepala-kartu">
                            <div class="ikon-bulat pengguna">
                                <i class="fa-solid fa-stopwatch"></i>
                            </div>
                            <span class="judul-kartu">Jam Kerja</span>
                        </div>
                        <div class="angka-persentase">
                            10 jam
                        </div>
                    </div>
                </div>
                <div class="col-sm mb-2">
                    <div class="kartu-statistik">
                        <div class="kepala-kartu">
                            <div class="ikon-bulat petir">
                                <i class="fa-solid fa-book"></i>
                            </div>
                            <span class="judul-kartu">Tugas</span>
                        </div>
                        <div class="angka-persentase">
                            16 Tugas
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container text-center mt-3 mb-3">
    <div class="row">
        <div class="col-sm-7">
            <div class="card h-100">
                <div class="card-header bg-body-tertiary py-2">
                    <div class="row flex-between-center">
                        <div class="col-auto">
                            <h6 class="mb-0">Kinerja Kantor</h6>
                        </div>
                    </div>
                </div>
                <div class="card-body h-100">
                    <canvas id="topProductsChart" height="300"></canvas>
                </div>
            </div>
        </div>
        <div class="col-sm-5 mt-2">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold text-dark">Manager Goal In 2025</h6>
                    <button class="btn btn-link text-muted p-0">
                        <i class="fas fa-ellipsis-h"></i>
                    </button>
                </div>
                <div class="card-body d-flex flex-column flex-lg-row align-items-start p-4 goal-card-body">
                    <div class="flex-grow-1 me-lg-4 mb-4 mb-lg-0 goal-card-list">
                        <div class="d-flex align-items-start mb-3 p-2" style="border-left : 3px solid #24C6F9">
                            <span class="border-start border-3 border-info me-3" style="height: 30px;"></span>
                            <div class="ms-2">
                                <small class="text-muted d-block">General Manager</small>
                                <p class="fw-bold mb-0">98% <span class="badge text-bg-success ms-2 rounded-pill text-success"><i class="fa-solid fa-circle-check"></i> Selesai</span></p>
                            </div>
                        </div>
                        <div class="d-flex align-items-start mb-3 p-2" style="border-left : 3px solid #2E86FC">
                            <span class="border-start border-3 border-primary me-3" style="height: 30px;"></span>
                            <div>
                                <small class="text-muted d-block">Education Manager</small>
                                <p class="fw-bold mb-0">186% <span class="badge text-bg-primary ms-2 rounded-pill"><i class="fa-solid fa-bars-progress text-primary"></i> half way</span></p>
                            </div>
                        </div>
                        <div class="d-flex align-items-start mb-3 p-2" style="border-left : 3px solid #28A745">
                            <span class="border-start border-3 border-danger me-3" style="height: 30px;"></span>
                            <div>
                                <small class="text-muted d-block">SPV Sales</small>
                                <p class="fw-bold mb-0">70% <span class="badge text-bg-danger ms-2 rounded-pill text-danger"><i class="fa-solid fa-circle-exclamation"></i> fail</span></p>
                            </div>
                        </div>
                        <div class="d-flex align-items-start mb-3 p-2" style="border-left : 3px solid #B71CFF">
                            <span class="border-start border-3 border-danger me-3" style="height: 30px;"></span>
                            <div>
                                <small class="text-muted d-block">Koordinator ITSM</small>
                                <p class="fw-bold mb-0">90% <span class="badge text-bg-danger ms-2 rounded-pill text-success"><i class="fa-solid fa-circle-exclamation"></i> success</span></p>
                            </div>
                        </div>
                    </div>
                    <div class="goal-chart" style="width:180px;height:180px; min-width:180px; position: relative;">
                        <canvas id="weeklyGoalChart"></canvas>
                        <!-- <div class="position-absolute top-50 start-50 translate-middle text-center">
                            <h4 class="fw-bold mb-0">78%</h4>
                            <small class="text-muted">Avg. Goal</small>
                        </div> -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('script')
<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                    backgroundColor: '#d6e0f5', // abu muda
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