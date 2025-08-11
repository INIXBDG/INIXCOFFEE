@extends('databasekpi.berandaKPI')

@section('contentKPI')
<style>
    .goal-card {
        background-color: #ffffff;
        border-radius: 0.5rem;
        padding: 1.5rem;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
    }

    .badge-purple {
        background-color: #8e44ad;
        color: #fff;
    }

    .goal-progress {
        height: 10px;
        background-color: #e9ecef;
        border-radius: 5px;
        overflow: hidden;
    }

    .goal-progress-bar {
        height: 100%;
        background-color: #0d6efd;
        transition: width 0.4s ease;
    }

    .goal-footer {
        font-size: 0.9rem;
        color: #6c757d;
    }

    .goal-footer .update-link {
        color: #0d6efd;
        font-weight: 500;
        text-decoration: none;
    }

    .goal-footer .update-link:hover {
        text-decoration: underline;
    }

    .subordinate-list {
        padding-top: 2rem;
    }

    .subordinate-item {
        display: flex;
        align-items: center;
        padding: 0.75rem 0;
        border-bottom: 1px solid #f1f1f1;
    }

    .subordinate-avatar {
        width: 42px;
        height: 42px;
        border-radius: 50%;
        background-color: #dee2e6;
        color: #6c757d;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 0.875rem;
        margin-right: 0.75rem;
        flex-shrink: 0;
        overflow: hidden;
    }

    .subordinate-name {
        font-weight: 500;
        margin-bottom: 0;
        font-size: 0.95rem;
    }

    .subordinate-role {
        font-size: 0.8rem;
        color: #6c757d;
        margin-bottom: 0;
    }

    .subordinate-arrow {
        margin-left: auto;
        color: #ccc;
    }

    #reviewChart {
        height: 300px !important;
        max-height: 300px;
    }
</style>

<div class="container my-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Review Penilaian</h6>
                    <div class="form-inline">
                        <select class="form-control ml-3">
                            <option selected>Penilaian 360</option>
                        </select>
                    </div>
                </div>
                <div class="card-body">
                    <div style="height: 300px;">
                        <canvas id="reviewChart"></canvas>
                    </div>
                    <div class="mt-3 d-flex justify-content-center flex-wrap">
                        <span class="mr-3 mb-2"><span class="badge badge-primary">&nbsp;</span> GM Review</span>
                        <span class="mr-3 mb-2"><span class="badge badge-success">&nbsp;</span> Manager Review</span>
                        <span class="mr-3 mb-2"><span class="badge badge-purple" style="background-color: #8e44ad;">&nbsp;</span> Beda Divisi</span>
                        <span class="mr-3 mb-2"><span class="badge badge-purple" style="background-color: #9b59b6;">&nbsp;</span> Satu Divisi</span>
                        <span class="mr-3 mb-2"><span class="badge badge-warning">&nbsp;</span> Self Appresial</span>
                    </div>
                </div>
            </div>

        </div>
        <div class="col-md-4">
            <div class="goal-card">
                <h5 class="fw-bold mb-4">Goal Manager</h5>

                <div class="mb-4">
                    <div class="text-uppercase fw-semibold small mb-2">TEST TARGET 100JT</div>
                    <div class="goal-progress mb-2">
                        <div class="goal-progress-bar" style="width: 0%;"></div>
                    </div>
                    <div class="d-flex justify-content-between goal-footer">
                        <div>Rp0 / Rp100.000.000</div>
                        <a href="#" class="update-link">Update</a>
                    </div>
                </div>

                <div class="mb-4">
                    <div class="text-uppercase fw-semibold small mb-2">TEST TARGET 100JT</div>
                    <div class="goal-progress mb-2">
                        <div class="goal-progress-bar" style="width: 50%;"></div>
                    </div>
                    <div class="d-flex justify-content-between goal-footer">
                        <div>Rp50.000.000 / Rp100.000.000</div>
                        <a href="#" class="update-link">Update</a>
                    </div>
                </div>

                <div class="mb-4">
                    <div class="text-uppercase fw-semibold small mb-2">TEST TARGET 100JT</div>
                    <div class="goal-progress mb-2">
                        <div class="goal-progress-bar" style="width: 0%;"></div>
                    </div>
                    <div class="d-flex justify-content-between goal-footer">
                        <div>Rp0 / Rp100.000.000</div>
                        <a href="#" class="update-link">Update</a>
                    </div>
                </div>

                <div class="text-end mt-3">
                    <a href="#" class="text-decoration-none text-primary fw-semibold">
                        View Semua Goal <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>

                <div class="subordinate-list">
                    <h6 class="fw-bold mb-1">Bawahan Anda</h6>
                    <p class="text-muted small mb-3">Karyawan yang melapor langsung kepada anda</p>

                    <div class="subordinate-item">
                        <div class="subordinate-avatar">
                            <img src="https://i.pravatar.cc/40" alt="Avatar" class="img-fluid rounded-circle">
                        </div>
                        <div>
                            <p class="subordinate-name mb-0">Trubus Sumantono</p>
                            <p class="subordinate-role mb-0">CEO</p>
                        </div>
                        <i class="fas fa-chevron-right subordinate-arrow"></i>
                    </div>

                    <div class="subordinate-item">
                        <div class="subordinate-avatar">FM</div>
                        <div>
                            <p class="subordinate-name mb-0">Furqan Maulana</p>
                            <p class="subordinate-role mb-0">Product Manager</p>
                        </div>
                        <i class="fas fa-chevron-right subordinate-arrow"></i>
                    </div>

                    <div class="subordinate-item">
                        <div class="subordinate-avatar">FL</div>
                        <div>
                            <p class="subordinate-name mb-0 text-truncate" style="max-width: 140px;">
                                Flex Limit Prorate Join 1 Desember 2019
                            </p>
                            <p class="subordinate-role mb-0">CEO</p>
                        </div>
                        <i class="fas fa-chevron-right subordinate-arrow"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    const labels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul'];
    const data = {
        labels: labels,
        datasets: [{
                label: 'GM Review',
                data: [5, 3, 4, 6, 2, 8, 3],
                borderColor: '#4e73df',
                backgroundColor: '#4e73df',
            },
            {
                label: 'Manager Review',
                data: [2, 6, 3, 5, 7, 4, 2],
                borderColor: '#1cc88a',
                backgroundColor: '#1cc88a',
            },
            {
                label: 'Beda Divisi',
                data: [1, 4, 2, 3, 5, 1, 4],
                borderColor: '#8e44ad',
                backgroundColor: '#8e44ad',
            },
            {
                label: 'Satu Divisi',
                data: [3, 5, 6, 2, 4, 7, 3],
                borderColor: '#9b59b6',
                backgroundColor: '#9b59b6',
            },
            {
                label: 'Self Appresial',
                data: [7, 8, 6, 9, 10, 11, 5],
                borderColor: '#f6c23e',
                backgroundColor: '#f6c23e',
            }
        ]
    };

    const config = {
        type: 'bar',
        data: data,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 2
                    }
                }
            }
        }
    };

    const reviewChart = new Chart(
        document.getElementById('reviewChart'),
        config
    );
</script>
@endsection