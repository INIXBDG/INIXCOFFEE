@extends('databasekpi.berandaKPI')

@section('contentKPI')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <div class="content-wrapper">
        <div class="page-header">
            <h3 class="page-title">
                <span class="page-title-icon bg-gradient-primary text-white me-2">
                    <i class="mdi mdi-home"></i>
                </span>
                Overview KPI
            </h3>
        </div>

        <div class="container-fluid bg-white p-4 rounded-4">
            <div class="mb-4 p-3 rounded-4 shadow-sm bg-light">
                <form action="{{ route('kpi.overview.get') }}" method="get" id="FormFilter">
                    <div class="row align-items-center g-3">
                        <div class="col-md-3">
                            <h5 class="fw-semibold mb-0" id="overviewTitle">
                                Overview Divisi {{ request('tahun', now()->year) }}
                            </h5>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" name="divisi" id="selectDivisi">
                                <option disabled>Pilih Departement</option>
                                @foreach ($departments as $data)
                                    <option value="{{ $data }}" {{ $divisi === $data ? 'selected' : '' }}>
                                        {{ $data }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" name="tahun" id="selectTahun">
                                <option value="">Periode Tahun</option>
                                @for ($year = 2025; $year <= now()->year; $year++)
                                    <option value="{{ $year }}"
                                        {{ request('tahun', now()->year) == $year ? 'selected' : '' }}>
                                        {{ $year }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fa-solid fa-filter me-1"></i> Filter
                            </button>
                        </div>
                    </div>
                </form>
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
                                <i class="fa-solid fa-star"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-muted">Rata Rata Progress</small>
                                <h4 class="fw-bold mb-0" id="rataProgress">0%</h4>
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
                                <h4 class="fw-bold mb-0" id="kpiAktif">0</h4>
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
                                <h4 class="fw-bold mb-0" id="kpiSelesai">0</h4>
                            </div>
                            <div class="d-flex align-items-center justify-content-center bg-info bg-opacity-10 text-info rounded-3"
                                style="width:42px;height:42px;">
                                <i class="fa-solid fa-check"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-8">
                    <div class="card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-body">
                            <h6 class="fw-semibold mb-3" id="employeeTitle"><i class="fa-solid fa-users"></i> Karyawan di Departemen</h6>
                            <div id="employeeList">
                                <div class="text-center py-5">
                                    <i class="fa-solid fa-spinner fa-spin fa-2x text-muted"></i>
                                    <p class="mt-2 text-muted">Loading data...</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-body">
                            <h6 class="fw-semibold mb-3"><i class="fa-solid fa-triangle-exclamation"></i> Perlu Perhatian</h6>
                            <p class="text-muted small">Karyawan dengan KPI belum tercapai</p>
                            <div id="lowPerformanceList">
                                <div class="text-center py-5">
                                    <i class="fa-solid fa-spinner fa-spin fa-2x text-muted"></i>
                                    <p class="mt-2 text-muted">Loading data...</p>
                                </div>
                            </div>
                            <button class="btn btn-outline-primary btn-sm w-100">
                                <i class="fa-solid fa-message me-1"></i> Jadwalkan Coaching
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-8">
                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-body">
                            <h6 class="fw-semibold mb-3">Statistik Karyawan</h6>
                            <div style="height: 400px;">
                                <canvas id="kpiChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-body">
                            <h6 class="fw-semibold mb-3">Distribusi Nilai</h6>
                            <div style="height: 300px;">
                                <canvas id="kpiPieChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body">
                    <h6 class="fw-semibold mb-3">Daftar Target KPI</h6>
                    <div class="table-responsive">
                        <table class="table align-middle" id="targetTable">
                            <thead class="table-light">
                                <tr>
                                    <th>Judul</th>
                                    <th>Periode</th>
                                    <th>Target</th>
                                    <th>Progress</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="5" class="text-center py-5">
                                        <i class="fa-solid fa-spinner fa-spin fa-2x text-muted"></i>
                                        <p class="mt-2 text-muted">Loading data...</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.17.1/moment-with-locales.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        let kpiChart = null;
        let kpiPieChart = null;

        $(document).ready(function() {
            @if (auth()->user()->hasRole('Koordinator') && auth()->user()->karyawan)
                $('#selectDivisi').val('{{ auth()->user()->karyawan->divisi }}').trigger('change');
            @endif

            $('#FormFilter').on('submit', function(e) {
                e.preventDefault();
                loadData();
            });

            loadData();
        });

        function loadData() {
            let formData = $('#FormFilter').serialize();
            let selectedDivisi = $('#selectDivisi').val() || 'Education';
            let selectedTahun = $('#selectTahun').val() || {{ now()->year }};
            $('#overviewTitle').text(`Overview ${selectedDivisi} ${selectedTahun}`);
            $('#employeeTitle').html(`<i class="fa-solid fa-users"></i>  Karyawan Di Departement ${selectedDivisi}`);

            $.ajax({
                url: "{{ route('kpi.overview.get') }}",
                type: 'GET',
                data: formData,
                dataType: 'json',
                beforeSend: function() {
                    $('#employeeList').html(
                        '<div class="text-center py-5"><i class="fa-solid fa-spinner fa-spin fa-2x text-muted"></i><p class="mt-2 text-muted">Loading...</p></div>'
                    );
                    $('#lowPerformanceList').html(
                        '<div class="text-center py-5"><i class="fa-solid fa-spinner fa-spin fa-2x text-muted"></i><p class="mt-2 text-muted">Loading...</p></div>'
                    );
                },
                success: function(response) {
                    updateStats(response);
                    updateEmployeeList(response.karyawan_departemen);
                    updateLowPerformanceList(response.karyawan_departemen);
                    updateCharts(response.statistik_karyawan, response.distribusi_nilai);
                    updateTargetTable(response.daftar_target_kpi);
                },
                error: function(xhr, status, error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Gagal memuat data. Silakan coba lagi.'
                    });
                    console.error('AJAX Error:', error);
                }
            });
        }

        function updateStats(data) {
            $('#totalTarget').text(data.total_target + ' Target');
            $('#rataProgress').text(Math.round(data.rata_rata_progress) + '%');
            $('#kpiAktif').text(data.kpi_aktif);
            $('#kpiSelesai').text(data.kpi_selesai);
        }

        function updateEmployeeList(employees) {
            if (!employees || employees.length === 0) {
                $('#employeeList').html('<p class="text-center text-muted py-5">Tidak ada data karyawan</p>');
                return;
            }

            let html = '';
            employees.forEach(emp => {
                let bgColor = emp.rata_rata_progress >= 75 ?
                    'bg-primary bg-opacity-10 border-start border-4 border-primary' :
                    (emp.rata_rata_progress < 50 ? 'bg-danger bg-opacity-10 border-start border-4 border-danger' :
                        'bg-light');
                let badge = emp.rata_rata_progress >= 75 ?
                    '<span class="badge bg-primary-subtle text-primary">Top</span>' :
                    (emp.rata_rata_progress < 50 ?
                        '<span class="badge bg-danger-subtle text-danger">⚠️ Perlu Bimbingan</span>' : '');

                html += `
                <div class="p-3 mb-3 rounded-3 shadow-sm ${bgColor}">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <strong>${emp.nama}</strong><br>
                            <small class="text-muted">${emp.jabatan}</small>
                        </div>
                        <div class="text-end">
                            <div class="fw-bold">${Math.round(emp.rata_rata_progress)}%</div>
                            ${badge}
                        </div>
                    </div>
                </div>
            `;
            });

            $('#employeeList').html(html);
        }

        function updateLowPerformanceList(employees) {
            let lowPerf = employees.filter(emp => emp.rata_rata_progress < 50);

            if (!lowPerf || lowPerf.length === 0) {
                $('#lowPerformanceList').html(
                    '<p class="text-center text-muted py-3">Tidak ada karyawan yang memerlukan perhatian khusus</p>');
                return;
            }

            let html = '';
            lowPerf.forEach(emp => {
                html += `
                <div class="p-3 mb-3 rounded-3 bg-danger bg-opacity-10 border-start border-4 border-danger">
                    <div class="d-flex justify-content-between">
                        <div>
                            <strong>${emp.nama}</strong><br>
                            <small class="text-muted">${emp.jabatan}</small>
                        </div>
                        <div class="fw-bold text-danger">${Math.round(emp.rata_rata_progress)}%</div>
                    </div>
                </div>
            `;
            });

            $('#lowPerformanceList').html(html);
        }

        function updateCharts(employeeStats, distributionData) {
            if (kpiChart) kpiChart.destroy();
            if (kpiPieChart) kpiPieChart.destroy();

            let employeeNames = employeeStats.map(item => item.nama);
            let employeeProgress = employeeStats.map(item => Math.round(item.rata_rata_progress));
            let employeeTargets = employeeStats.map(item => item.total_target);

            kpiChart = new Chart(document.getElementById('kpiChart'), {
                type: 'bar',
                data: {
                    labels: employeeNames,
                    datasets: [{
                            label: 'Progress (%)',
                            data: employeeProgress,
                            backgroundColor: employeeProgress.map(progress => {
                                if (progress >= 75) return 'rgba(182, 109, 255, 0.5)';
                                if (progress >= 50) return 'rgba(254, 124, 150, 0.6)';
                                return 'rgba(182, 109, 255, 0.5)';
                            }),
                            borderColor: employeeProgress.map(progress => {
                                if (progress >= 75) return 'rgba(182, 109, 255, 0.8)';
                                if (progress >= 50) return 'rgba(254, 124, 150, 0.9)';
                                return 'rgba(182, 109, 255, 0.8)';
                            }),
                            borderWidth: 1,
                            yAxisID: 'y'
                        },
                        {
                            label: 'Total Target',
                            data: employeeTargets,
                            type: 'line',
                            borderColor: '#5E00BC',
                            backgroundColor: 'transparent',
                            borderWidth: 3,
                            pointBackgroundColor: '#5E00BC',
                            pointRadius: 5,
                            pointHoverRadius: 7,
                            fill: false,
                            yAxisID: 'y1'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top'
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    if (context.dataset.label === 'Progress (%)') {
                                        return `Progress: ${context.parsed.y}%`;
                                    }
                                    return `Total Target: ${context.parsed.y}`;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            type: 'linear',
                            display: true,
                            position: 'left',
                            title: {
                                display: true,
                                text: 'Progress (%)'
                            },
                            min: 0,
                            max: 100,
                            ticks: {
                                callback: function(value) {
                                    return value + '%';
                                }
                            },
                            grid: {
                                drawOnChartArea: false
                            }
                        },
                        y1: {
                            type: 'linear',
                            display: true,
                            position: 'right',
                            title: {
                                display: true,
                                text: 'Total Target'
                            },
                            min: 0,
                            grid: {
                                drawOnChartArea: false
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Karyawan'
                            }
                        }
                    }
                }
            });

            let pieLabels = Object.keys(distributionData);
            let pieData = Object.values(distributionData);
            let pieColors = [
                '#FE7C96',
                '#FED718',
                '#F39F40',
                '#28CFB4'
            ];

            kpiPieChart = new Chart(document.getElementById('kpiPieChart'), {
                type: 'doughnut',
                data: {
                    labels: pieLabels,
                    datasets: [{
                        data: pieData,
                        backgroundColor: pieColors,
                        borderWidth: 0
                    }]
                },
                options: {
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
                                    let percentage = Math.round((value / total) * 100);
                                    return `${label}: ${value}`;
                                }
                            }
                        }
                    },
                    cutout: '60%'
                }
            });
        }

        function updateTargetTable(targets) {
            if (!targets || targets.length === 0) {
                $('#targetTable tbody').html(
                    '<tr><td colspan="5" class="text-center py-5">Tidak ada data target</td></tr>');
                return;
            }

            let html = '';
            targets.forEach(target => {
                let badgeClass = target.status === 'Selesai' ? 'bg-success' : 'bg-warning text-dark';
                let progress = target.progress ? Math.round(target.progress) + '%' : '-';

                html += `
                <tr>
                    <td>${target.judul}</td>
                    <td>${target.periode}</td>
                    <td>${target.target}</td>
                    <td>${progress}</td>
                    <td><span class="badge ${badgeClass}">${target.status}</span></td>
                </tr>
            `;
            });

            $('#targetTable tbody').html(html);
        }
    </script>
@endsection
