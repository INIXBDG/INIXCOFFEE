@extends('layout_HR.app')

@section('content_HR')
    <div class="container-xxl flex-grow-1 container-p-y">

        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h4 mb-0 fw-bold">Dashboard</h1>
            <button class="btn btn-primary btn-sm">
                <i class="iconify me-1" data-icon="mdi:download"></i> Generate Report
            </button>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="card h-100 border-start border-4 border-primary">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col">
                                <div class="text-xs fw-bold text-primary text-uppercase mb-1">Earnings (Monthly)</div>
                                <div class="h5 mb-0 fw-bold">$40,000</div>
                            </div>
                            <div class="col-auto">
                                <i class="iconify text-muted" data-icon="mdi:calendar" data-width="32" data-height="32"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card h-100 border-start border-4 border-success">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col">
                                <div class="text-xs fw-bold text-success text-uppercase mb-1">Earnings (Annual)</div>
                                <div class="h5 mb-0 fw-bold">$215,000</div>
                            </div>
                            <div class="col-auto">
                                <i class="iconify text-muted" data-icon="mdi:cash-multiple" data-width="32"
                                    data-height="32"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card h-100 border-start border-4 border-info">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col">
                                <div class="text-xs fw-bold text-info text-uppercase mb-1">Tasks</div>
                                <div class="d-flex align-items-center">
                                    <div class="h5 mb-0 me-3 fw-bold">50%</div>
                                    <div class="flex-grow-1">
                                        <div class="progress" style="height: 8px">
                                            <div class="progress-bar bg-info" role="progressbar" style="width: 50%"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="iconify text-muted" data-icon="mdi:clipboard-list" data-width="32"
                                    data-height="32"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card h-100 border-start border-4 border-warning">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col">
                                <div class="text-xs fw-bold text-warning text-uppercase mb-1">Pending Requests</div>
                                <div class="h5 mb-0 fw-bold">18</div>
                            </div>
                            <div class="col-auto">
                                <i class="iconify text-muted" data-icon="mdi:comment-processing" data-width="32"
                                    data-height="32"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-xl-8 col-lg-7">
                <div class="card">
                    <div class="card-header d-flex flex-wrap align-items-center justify-content-between py-3">
                        <h6 class="mb-0 fw-bold">Earnings Overview</h6>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-icon dropdown-toggle hide-arrow" type="button"
                                data-bs-toggle="dropdown">
                                <i class="iconify" data-icon="mdi:dots-vertical"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <h6 class="dropdown-header">Dropdown Header</h6>
                                </li>
                                <li><a class="dropdown-item" href="#">Action</a></li>
                                <li><a class="dropdown-item" href="#">Another action</a></li>
                                <li>
                                    <div class="dropdown-divider"></div>
                                </li>
                                <li><a class="dropdown-item" href="#">Something else here</a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="card-body">
                        <div style="height: 300px">
                            <canvas id="myAreaChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-4 col-lg-5">
                <div class="card">
                    <div class="card-header d-flex flex-wrap align-items-center justify-content-between py-3">
                        <h6 class="mb-0 fw-bold">Revenue Sources</h6>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-icon dropdown-toggle hide-arrow" type="button"
                                data-bs-toggle="dropdown">
                                <i class="iconify" data-icon="mdi:dots-vertical"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <h6 class="dropdown-header">Dropdown Header</h6>
                                </li>
                                <li><a class="dropdown-item" href="#">Action</a></li>
                                <li><a class="dropdown-item" href="#">Another action</a></li>
                                <li>
                                    <div class="dropdown-divider"></div>
                                </li>
                                <li><a class="dropdown-item" href="#">Something else here</a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="card-body">
                        <div style="height: 250px" class="d-flex align-items-center justify-content-center">
                            <canvas id="myPieChart"></canvas>
                        </div>
                        <div class="mt-4 text-center">
                            <span class="me-3">
                                <i class="iconify text-primary" data-icon="mdi:circle" data-width="10"
                                    data-height="10"></i> Direct
                            </span>
                            <span class="me-3">
                                <i class="iconify text-success" data-icon="mdi:circle" data-width="10"
                                    data-height="10"></i> Social
                            </span>
                            <span>
                                <i class="iconify text-info" data-icon="mdi:circle" data-width="10"
                                    data-height="10"></i> Referral
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-6">
                <div class="card mb-4">
                    <div class="card-header py-3">
                        <h6 class="mb-0 fw-bold">Projects</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-4">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="small fw-bold">Server Migration</span>
                                <span class="small fw-bold">20%</span>
                            </div>
                            <div class="progress" style="height: 8px">
                                <div class="progress-bar bg-danger" role="progressbar" style="width: 20%"></div>
                            </div>
                        </div>
                        <div class="mb-4">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="small fw-bold">Sales Tracking</span>
                                <span class="small fw-bold">40%</span>
                            </div>
                            <div class="progress" style="height: 8px">
                                <div class="progress-bar bg-warning" role="progressbar" style="width: 40%"></div>
                            </div>
                        </div>
                        <div class="mb-4">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="small fw-bold">Customer Database</span>
                                <span class="small fw-bold">60%</span>
                            </div>
                            <div class="progress" style="height: 8px">
                                <div class="progress-bar bg-primary" role="progressbar" style="width: 60%"></div>
                            </div>
                        </div>
                        <div class="mb-4">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="small fw-bold">Payout Details</span>
                                <span class="small fw-bold">80%</span>
                            </div>
                            <div class="progress" style="height: 8px">
                                <div class="progress-bar bg-info" role="progressbar" style="width: 80%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="d-flex justify-content-between mb-1">
                                <span class="small fw-bold">Account Setup</span>
                                <span class="small fw-bold">Complete!</span>
                            </div>
                            <div class="progress" style="height: 8px">
                                <div class="progress-bar bg-success" role="progressbar" style="width: 100%"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-lg-6">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <div class="fw-bold">Primary</div>
                                <div class="small opacity-75">#4e73df</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <div class="fw-bold">Success</div>
                                <div class="small opacity-75">#1cc88a</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="card bg-info text-white">
                            <div class="card-body">
                                <div class="fw-bold">Info</div>
                                <div class="small opacity-75">#36b9cc</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="card bg-warning text-white">
                            <div class="card-body">
                                <div class="fw-bold">Warning</div>
                                <div class="small opacity-75">#f6c23e</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="card bg-danger text-white">
                            <div class="card-body">
                                <div class="fw-bold">Danger</div>
                                <div class="small opacity-75">#e74a3b</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="card bg-secondary text-white">
                            <div class="card-body">
                                <div class="fw-bold">Secondary</div>
                                <div class="small opacity-75">#858796</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="card bg-light text-dark">
                            <div class="card-body">
                                <div class="fw-bold">Light</div>
                                <div class="small opacity-75">#f8f9fc</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="card bg-dark text-white">
                            <div class="card-body">
                                <div class="fw-bold">Dark</div>
                                <div class="small opacity-75">#5a5c69</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card mb-4">
                    <div class="card-header py-3">
                        <h6 class="mb-0 fw-bold">Illustrations</h6>
                    </div>
                    <div class="card-body">
                        <div class="text-center">
                            <img class="img-fluid px-3 px-sm-4 mt-3 mb-4" style="width: 25rem"
                                src="img/undraw_posting_photo.svg" alt="Illustration">
                        </div>
                        <p>Add some quality, svg illustrations to your project courtesy of <a target="_blank"
                                rel="nofollow" href="https://undraw.co/">unDraw</a>, a constantly updated collection of
                            beautiful svg images that you can use completely free and without attribution!</p>
                        <a target="_blank" rel="nofollow" href="https://undraw.co/">Browse Illustrations on unDraw
                            &rarr;</a>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header py-3">
                        <h6 class="mb-0 fw-bold">Development Approach</h6>
                    </div>
                    <div class="card-body">
                        <p>SB Admin 2 makes extensive use of Bootstrap 4 utility classes in order to reduce CSS bloat and
                            poor page performance. Custom CSS classes are used to create custom components and custom
                            utility classes.</p>
                        <p class="mb-0">Before working with this theme, you should become familiar with the Bootstrap
                            framework, especially the utility classes.</p>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        $(document).ready(function() {
            const areaCtx = document.getElementById('myAreaChart');
            new Chart(areaCtx, {
                type: 'line',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov',
                        'Dec'
                    ],
                    datasets: [{
                        label: 'Earnings',
                        data: [0, 10000, 5000, 15000, 10000, 20000, 15000, 25000, 20000, 30000,
                            25000, 40000
                        ],
                        tension: 0.4,
                        fill: false,
                        borderColor: '#4e73df',
                        backgroundColor: '#4e73df',
                        pointBackgroundColor: '#4e73df',
                        pointBorderColor: '#fff',
                        pointHoverBackgroundColor: '#fff',
                        pointHoverBorderColor: '#4e73df'
                    }]
                },
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
                                callback: function(value) {
                                    return '$' + value.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });

            const pieCtx = document.getElementById('myPieChart');
            new Chart(pieCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Direct', 'Social', 'Referral'],
                    datasets: [{
                        data: [55, 30, 15],
                        backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc'],
                        hoverBackgroundColor: ['#2e59d9', '#17a673', '#2c9faf'],
                        hoverBorderColor: 'rgba(234, 236, 244, 1)'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    cutout: '70%'
                }
            });
        });
    </script>
@endsection
