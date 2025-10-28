@extends('databasekpi.berandaKPI')

@section('contentKPI')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<style>
    .card-dashboard {
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        transition: transform 0.2s ease;
    }

    .progress-bar-custom {
        background: linear-gradient(90deg, #ff6b6b, #ff8e8e);
        border-radius: 8px;
    }

    .avatar-small {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: #f0f0f0;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        color: #666;
        margin-right: 10px;
    }

    .target-item {
        padding: 12px 16px;
        border-bottom: 1px solid #eee;
        display: flex;
        align-items: center;
        cursor: pointer;
        transition: background 0.2s;
    }

    .target-item:hover {
        background: #f8f9fa;
    }

    .target-item:last-child {
        border-bottom: none;
    }

    .badge-status {
        font-size: 0.75rem;
        padding: 4px 8px;
        border-radius: 12px;
    }

    .chart-container {
        position: relative;
        height: 200px;
        width: 100%;
    }

    #penilaianTable_paginate .paginate_button {
        background: transparent !important;
        color: black !important;
        border: none !important;
        margin: 0 5px !important;
    }

    #penilaianTable_paginate .paginate_button.current {
        background: linear-gradient(to right, #da8cff, #9a55ff) !important;
        color: white !important;
        border-radius: 8px !important;
    }

    .dataTables_wrapper .dataTables_length {
        margin-bottom: 15px !important;
    }

    .dataTables_wrapper .dataTables_filter {
        margin-bottom: 15px !important;
    }

    .dataTables_wrapper .dataTables_info {
        margin-top: 15px !important;
    }

    .dataTables_wrapper .dataTables_paginate {
        margin-top: 10px !important;
    }
</style>
<div class="content-wrapper">
    <div class="page-header">
        <h3 class="page-title">
            <span class="page-title-icon bg-gradient-primary text-white me-2">
                <i class="mdi mdi-file-document"></i>
            </span> Project
        </h3>
        <nav aria-label="breadcrumb">
            <ul class="breadcrumb">
                <li class="breadcrumb-item active" aria-current="page">
                    <span></span> Overview Target & Penilaian Karyawan
                    <i class="mdi mdi-alert-circle-outline icon-sm text-primary align-middle"
                        data-bs-toggle="tooltip"
                        data-bs-placement="top"
                        title="Halaman ini menampilkan pencapaian target karyawan secara real-time.">
                    </i>
                </li>
            </ul>
        </nav>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="card card-dashboard p-3">
                <h6 class="text-muted mb-2">Total Karyawan Terevaluasi</h6>
                <h3 class="fw-bold">24</h3>
                <small class="text-success"><i class="fas fa-arrow-up me-1"></i> +12% dari bulan lalu</small>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-dashboard p-3">
                <h6 class="text-muted mb-2">Rata-rata Pencapaian</h6>
                <h3 class="fw-bold">72%</h3>
                <small class="text-warning"><i class="fas fa-exclamation-triangle me-1"></i> Perlu perhatian</small>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-dashboard p-3">
                <h6 class="text-muted mb-2">Target Terpenuhi</h6>
                <h3 class="fw-bold">18</h3>
                <small class="text-info"><i class="fas fa-check me-1"></i> Dari 24 karyawan</small>
            </div>
        </div>
    </div>

    <div class="row mb-4 mt-4">
        <div class="col-md-12">
            <div class="card card-dashboard p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Filter</h5>
                    <select id="filterDivisi" class="form-select w-auto" style="max-width: 100%;">
                        <option value="">Semua Tipe</option>
                        <option value="On Target">On Target</option>
                        <option value="complete">complete</option>
                        <option value="Under target">Under target</option>
                        <option value="In Proccess">In Proccess</option>
                        <option value="Late Completion">Late Completion</option>
                        <option value="Fail">Fail</option>
                    </select>
                    <select id="filterDivisi" class="form-select w-auto" style="max-width: 100%;">
                        <option value="">Semua Divisi</option>
                        <option value="Education">Education</option>
                        <option value="Sales & Marketing">Sales & Marketing</option>
                        <option value="IT Service Management">IT Service Management</option>
                        <option value="Office">Office</option>
                    </select>
                    <select id="filterDivisi" class="form-select w-auto" style="max-width: 100%;">
                        <option value="">Pilih Bulan</option>
                        <option value="Januari">Januari</option>
                        <option value="Februari">Februari</option>
                        <option value="Maret">Maret</option>
                        <option value="April">April</option>
                        <option value="Mei">Mei</option>
                        <option value="Juni">Juni</option>
                        <option value="Juli">Juli</option>
                        <option value="Agustus">Agustus</option>
                        <option value="September">September</option>
                        <option value="Oktober">Oktober</option>
                        <option value="November">November</option>
                        <option value="Desember">Desember</option>
                    </select>
                    <select id="filterDivisi" class="form-select w-auto" style="max-width: 100%;">
                        <option value="">Pilih Tahun</option>
                        <option value="2024">2024</option>
                        <option value="2025">2025</option>
                    </select>
                    <input type="date" class="form-control w-auto" id="filterDivisi" style="max-width: 100%;">
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-12">
            <div class="card card-dashboard">
                <div class="card-body">
                    <h5 class="card-title">Detail Penilaian Karyawan</h5>
                    <div class="table-responsive">
                        <table id="penilaianTable" class="table table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Nama Karyawan</th>
                                    <th>Divisi</th>
                                    <th>Posisi</th>
                                    <th>Min Target</th>
                                    <th>Pencapaian (%)</th>
                                    <th>Proyek Terkait</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>1</td>
                                    <td><img src="{{ asset('template_KPI/dist/assets/images/screenshots/user-profile.jpg') }}" class="rounded-circle me-2"> Xepi</td>
                                    <td>Office</td>
                                    <td>Finance & Accounting</td>
                                    <td>95%</td>
                                    <td><span class="badge bg-success">96%</span></td>
                                    <td>Project Laporan Keuangan Q3</td>
                                    <td><span class="badge badge-status bg-warning">On Track</span></td>
                                    <td> <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#detailModal" onclick="showDetail('Xepi', 'Finance & Accounting', 78)"><i class="fa-solid fa-magnifying-glass"></i></button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>2</td>
                                    <td><img src="{{ asset('template_KPI/dist/assets/images/screenshots/user-profile.jpg') }}" class="rounded-circle me-2"> Riffa</td>
                                    <td>Office</td>
                                    <td>Finance & Accounting</td>
                                    <td>90%</td>
                                    <td><span class="badge bg-info">62%</span></td>
                                    <td>Project Revisi Budget</td>
                                    <td><span class="badge badge-status bg-secondary">Review Needed</span></td>
                                    <td>
                                        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#detailModal" onclick="showDetail('Riffa', 'Finance & Accounting', 62)"><i class="fa-solid fa-magnifying-glass"></i></button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>3</td>
                                    <td><img src="{{ asset('template_KPI/dist/assets/images/screenshots/user-profile.jpg') }}" class="rounded-circle me-2"> Amanda</td>
                                    <td>Office</td>
                                    <td>HRD</td>
                                    <td>85%</td>
                                    <td><span class="badge bg-danger">60%</span></td>
                                    <td>Project Onboarding New Hire</td>
                                    <td><span class="badge badge-status bg-danger">Behind Schedule</span></td>
                                    <td>
                                        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#detailModal" onclick="showDetail('Amanda', 'HRD', 60)"><i class="fa-solid fa-magnifying-glass"></i></button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>4</td>
                                    <td><img src="{{ asset('template_KPI/dist/assets/images/screenshots/user-profile.jpg') }}" class="rounded-circle me-2"> Cecep</td>
                                    <td>Office</td>
                                    <td>OB</td>
                                    <td>80%</td>
                                    <td><span class="badge bg-warning">56%</span></td>
                                    <td>Project Inventory Management</td>
                                    <td><span class="badge badge-status bg-warning">Needs Support</span></td>
                                    <td>
                                        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#detailModal" onclick="showDetail('Cecep', 'Office', 56)"><i class="fa-solid fa-magnifying-glass"></i></button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card card-dashboard h-100">
                <div class="card-body">
                    <h5 class="card-title">Data Formulir Penilaian</h5>
                    <div class="chart-container">
                        <canvas id="formulirChart"></canvas>
                    </div>
                    <div class="mt-3 d-flex justify-content-around">
                        <div class="text-center">
                            <div class="avatar-small bg-primary text-white">T</div>
                            <div>Total: 4</div>
                        </div>
                        <div class="text-center">
                            <div class="avatar-small bg-info text-white">R</div>
                            <div>Rutin: 3</div>
                        </div>
                        <div class="text-center">
                            <div class="avatar-small bg-danger text-white">P</div>
                            <div>Probation: 0</div>
                        </div>
                        <div class="text-center">
                            <div class="avatar-small bg-success text-white">K</div>
                            <div>Kontrak: 1</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card card-dashboard h-100">
                <div class="card-body">
                    <h5 class="card-title">Target Karyawan</h5>
                    <div id="targetList">
                        <div class="target-item">
                            <div class="avatar-small">X</div>
                            <div class="flex-grow-1">
                                <strong>Xepi - Finance & Accounting</strong>
                                <div class="progress mt-1" style="height: 8px;">
                                    <div class="progress-bar progress-bar-custom" role="progressbar" style="width: 78%;" aria-valuenow="78" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
                            <span class="ms-2 fw-bold">78%</span>
                            <i class="fas fa-chevron-right ms-3 text-muted"></i>
                        </div>
                        <div class="target-item">
                            <div class="avatar-small">R</div>
                            <div class="flex-grow-1">
                                <strong>Riffa - Finance & Accounting</strong>
                                <div class="progress mt-1" style="height: 8px;">
                                    <div class="progress-bar progress-bar-custom" role="progressbar" style="width: 62%;" aria-valuenow="62" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
                            <span class="ms-2 fw-bold">62%</span>
                            <i class="fas fa-chevron-right ms-3 text-muted"></i>
                        </div>
                        <div class="target-item">
                            <div class="avatar-small">A</div>
                            <div class="flex-grow-1">
                                <strong>Amanda - HRD</strong>
                                <div class="progress mt-1" style="height: 8px;">
                                    <div class="progress-bar progress-bar-custom" role="progressbar" style="width: 60%;" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
                            <span class="ms-2 fw-bold">60%</span>
                            <i class="fas fa-chevron-right ms-3 text-muted"></i>
                        </div>
                        <div class="target-item">
                            <div class="avatar-small">C</div>
                            <div class="flex-grow-1">
                                <strong>Cecep - Office</strong>
                                <div class="progress mt-1" style="height: 8px;">
                                    <div class="progress-bar progress-bar-custom" role="progressbar" style="width: 56%;" aria-valuenow="56" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
                            <span class="ms-2 fw-bold">56%</span>
                            <i class="fas fa-chevron-right ms-3 text-muted"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="detailModal" tabindex="-1" aria-labelledby="detailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="detailModalLabel">Detail Penilaian Karyawan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="text-center">
                                <img src="{{ asset('template_KPI/dist/assets/images/screenshots/user-profile.jpg') }}" class="rounded-circle mb-3" alt="Avatar">
                                <h5 id="modalNama">-</h5>
                                <p id="modalDivisi" class="text-muted">-</p>
                                <span id="modalPersen" class="badge bg-primary fs-6">-</span>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <h6>KPI Utama:</h6>
                            <ul>
                                <li>Selesaikan laporan keuangan akhir bulan sebelum tanggal 5</li>
                                <li>Verifikasi invoice masuk dengan akurat</li>
                                <li>Update database vendor setiap minggu</li>
                            </ul>
                            <h6>Proyek Terkait:</h6>
                            <p id="modalProyek">-</p>
                            <h6>Feedback Atasan:</h6>
                            <p>"Kinerja baik, namun perlu lebih cepat dalam proses verifikasi."</p>
                            <h6>Rekomendasi Pengembangan:</h6>
                            <p>- Pelatihan Excel Advanced<br>- Workshop Time Management</p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    <button type="button" class="btn btn-primary">Edit Penilaian</button>
                </div>
            </div>
        </div>
    </div>

</div>

@if (session('success'))
<script>
    Swal.fire({
        icon: 'success',
        title: 'Berhasil',
        text: "{{ session('success') }}",
        customClass: {
            confirmButton: 'btn btn-gradient-info me-3',
        },
    });
</script>
@endif

@if (session('error'))
<script>
    Swal.fire({
        icon: 'error',
        title: 'Gagal',
        text: "{{ session('error') }}",
        customClass: {
            confirmButton: 'btn btn-gradient-danger me-3',
        },
    });
</script>
@endif


@if ($errors->any())
<script>
    Swal.fire({
        icon: 'error',
        title: 'Validasi Gagal',
        html: `{!! implode('<br>', $errors->all()) !!}`,
        customClass: {
            confirmButton: 'btn btn-gradient-danger me-3',
        },
    });
</script>
@endif
<style>

</style>
@endsection
@section('script')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.17.1/moment-with-locales.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Inisialisasi Chart Donut
    const ctx = document.getElementById('formulirChart').getContext('2d');
    const formulirChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Total', 'Rutin', 'Probation', 'Kontrak'],
            datasets: [{
                data: [4, 3, 0, 1],
                backgroundColor: [
                    '#9c27b0', // Purple
                    '#2196f3', // Blue
                    '#ff5722', // Orange
                    '#4caf50' // Green
                ],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    enabled: true
                }
            }
        }
    });

    function showDetail(nama, divisi, persen) {
        document.getElementById('modalNama').textContent = nama;
        document.getElementById('modalDivisi').textContent = divisi;
        document.getElementById('modalPersen').textContent = persen + '%';
        document.getElementById('modalProyek').textContent = "Project " + (nama === 'Xepi' ? 'Laporan Keuangan Q3' :
            nama === 'Riffa' ? 'Revisi Budget' :
            nama === 'Amanda' ? 'Onboarding New Hire' :
            'Inventory Management');
    }

    document.getElementById('filterDivisi').addEventListener('change', function() {
        const selectedDivisi = this.value;
        const rows = document.querySelectorAll('#penilaianTable tbody tr');

        rows.forEach(row => {
            const divisiCell = row.children[2].textContent;
            if (selectedDivisi === '' || divisiCell === selectedDivisi) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });

    $(document).ready(function() {
        $('#penilaianTable').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/Indonesian.json'
            },
            pageLength: 10,
            ordering: true,
            searching: true,
            scrollY: '400px',
            scrollCollapse: true,
            paging: true
        });
    });
</script>
@endsection