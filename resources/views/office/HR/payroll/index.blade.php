@extends('layouts_office.app')

@section('office_contents')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .glass-force {
            background: rgba(255, 255, 255, 0.95);
            border: 1px solid rgba(0, 0, 0, 0.1);
            border-radius: 12px;
        }

        .summary-card {
            transition: transform 0.2s;
        }

        .summary-card:hover {
            transform: translateY(-3px);
        }

        .table-payroll th {
            background: #f8f9fa;
            font-weight: 600;
            font-size: 13px;
        }

        .table-payroll td {
            vertical-align: middle;
            font-size: 14px;
        }

        .status-badge {
            font-size: 11px;
            padding: 4px 8px;
            border-radius: 12px;
        }

        .status-done {
            background: #d1e7dd;
            color: #0f5132;
        }

        .status-pending {
            background: #fff3cd;
            color: #664d03;
        }

        .pagination-custom button {
            margin: 0 4px;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 13px;
            cursor: pointer;
        }

        .pagination-custom button.active {
            background: #0d6efd;
            color: white;
            border-color: #0d6efd;
        }

        .loading-overlay {
            position: absolute;
            inset: 0;
            background: rgba(255, 255, 255, 0.85);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10;
            border-radius: 12px;
        }

        .loading-overlay.hidden {
            display: none;
        }

        .chart-card {
            background: #fff;
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 16px;
        }

        .chart-container {
            position: relative;
            height: 280px;
        }

        .stat-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: #0d6efd;
        }

        .stat-label {
            font-size: 12px;
            color: #6c757d;
        }

        .divider {
            height: 1px;
            background: #eee;
            margin: 16px 0;
        }
    </style>

    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="m-0">Payroll & Tunjangan</h4>
            <p class="text-muted mb-0">Periode: <span id="periodLabel">-</span></p>
        </div>

        <div class="card glass-force mb-4 position-relative">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="m-0">Filter & Export</h5>
                <div class="d-flex gap-2">
                    <select id="filterBulan" class="form-select form-select-sm" style="width:130px"></select>
                    <select id="filterTahun" class="form-select form-select-sm" style="width:90px"></select>
                    <button id="btnFilter" class="btn btn-sm btn-primary"><i
                            class="fa-solid fa-filter me-1"></i>Filter</button>
                    <button id="exportCsv" class="btn btn-sm btn-success"><i
                            class="fa-solid fa-file-csv me-1"></i>CSV</button>
                    <button id="exportPdf" class="btn btn-sm btn-danger"><i
                            class="fa-solid fa-file-pdf me-1"></i>PDF</button>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-3 col-6">
                        <div class="card summary-card text-center p-3">
                            <div class="stat-value" id="sumTotal">0</div>
                            <div class="stat-label">Total Karyawan</div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="card summary-card text-center p-3">
                            <div class="stat-value text-success" id="sumDone">0</div>
                            <div class="stat-label">Sudah Dihitung</div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="card summary-card text-center p-3">
                            <div class="stat-value text-warning" id="sumPending">0</div>
                            <div class="stat-label">Belum Dihitung</div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="card summary-card text-center p-3">
                            <div class="stat-value text-primary" id="sumGross">Rp 0</div>
                            <div class="stat-label">Total Payroll</div>
                        </div>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card summary-card text-center p-3">
                            <div class="stat-value" id="sumAvg">Rp 0</div>
                            <div class="stat-label">Rata-rata Gaji Bersih</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card summary-card text-center p-3">
                            <div class="stat-value" id="sumMedian">Rp 0</div>
                            <div class="stat-label">Median Gaji</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card summary-card text-center p-3">
                            <div class="stat-value text-info" id="sumAllowance">Rp 0</div>
                            <div class="stat-label">Total Tunjangan</div>
                        </div>
                    </div>
                </div>

                <div class="input-group mb-3">
                    <span class="input-group-text"><i class="fa-solid fa-search"></i></span>
                    <input type="text" id="searchPayroll" class="form-control"
                        placeholder="Cari nama, kode, divisi, jabatan...">
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="chart-card">
                            <h6 class="mb-3">Distribusi Gaji Bersih</h6>
                            <div class="chart-container"><canvas id="salaryRangeChart"></canvas></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="chart-card">
                            <h6 class="mb-3">Tunjangan per Divisi (Top 8)</h6>
                            <div class="chart-container"><canvas id="allowanceChart"></canvas></div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="chart-card">
                            <h6 class="mb-3">Potongan Terbanyak (Top 8)</h6>
                            <div class="chart-container"><canvas id="deductionChart"></canvas></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="chart-card">
                            <h6 class="mb-3">Trend Payroll Bulanan (<span id="trendYear"></span>)</h6>
                            <div class="chart-container" style="height:220px"><canvas id="trendChart"></canvas></div>
                        </div>
                    </div>
                </div>
                <div class="divider"></div>

                <h6 class="mb-3">Detail Karyawan</h6>
                <div class="table-responsive">
                    <table class="table table-payroll table-hover align-middle">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama / Kode</th>
                                <th>Divisi / Jabatan</th>
                                <th class="text-end">Gaji Pokok</th>
                                <th class="text-end">Tunjangan</th>
                                <th class="text-end">Potongan</th>
                                <th class="text-end">Gaji Bersih</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="payrollBody"></tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-3">
                    <small class="text-muted">Menampilkan <span id="showingStart">0</span>-<span id="showingEnd">0</span>
                        dari <span id="totalItems">0</span></small>
                    <div class="pagination-custom" id="paginationContainer"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalDetail" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detail Payroll</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-6"><strong>Nama:</strong> <span id="modalNama"></span></div>
                        <div class="col-md-6"><strong>Kode:</strong> <span id="modalKode"></span></div>
                        <div class="col-md-6"><strong>Divisi:</strong> <span id="modalDivisi"></span></div>
                        <div class="col-md-6"><strong>Jabatan:</strong> <span id="modalJabatan"></span></div>
                    </div>
                    <table class="table table-sm table-bordered">
                        <thead>
                            <tr>
                                <th>Komponen</th>
                                <th>Tipe</th>
                                <th>Keterangan</th>
                                <th class="text-end">Nilai</th>
                            </tr>
                        </thead>
                        <tbody id="modalDetails"></tbody>
                        <tfoot>
                            <tr class="table-light">
                                <th colspan="3">Total Bersih</th>
                                <th class="text-end" id="modalNet"></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const formatIDR = (num) => {
            const value = typeof num === 'number' ? num : 0;
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                maximumFractionDigits: 0
            }).format(value);
        };

        const formatSigned = (num) => {
            const value = typeof num === 'number' ? num : 0;
            return (value < 0 ? '-' : '') + formatIDR(Math.abs(value));
        };
        let salaryChart = null,
            allowanceChart = null,
            trendChart = null;

        $(document).ready(function() {
            let currentPage = 1,
                allData = [],
                searchQuery = '';

            for (let i = 1; i <= 12; i++) {
                const monthName = new Date(2000, i - 1).toLocaleString('id-ID', {
                    month: 'long'
                });
                $('#filterBulan').append(`<option value="${String(i).padStart(2,'0')}">${monthName}</option>`);
            }
            const currentYear = new Date().getFullYear();
            for (let i = currentYear; i >= 2023; i--) {
                $('#filterTahun').append(`<option value="${i}">${i}</option>`);
            }
            $('#filterBulan').val(String(new Date().getMonth() + 1).padStart(2, '0'));
            $('#trendYear').text(currentYear);

            window.loadData = function(page = 1) {
                currentPage = page;
                $('#mainLoading').removeClass('hidden');
                
                const params = {
                    month: $('#filterBulan').val(),
                    year: $('#filterTahun').val(),
                    search: searchQuery,
                    page: page
                };

                $.get("{{ route('office.HR.payroll.dashboard') }}", params, function(res) {
                    if(!res.success) {
                        alert(res.message);
                        $('#mainLoading').addClass('hidden');
                        return;
                    }

                    allData = res.data;
                    $('#periodLabel').text(res.period.display);
                    $('#sumTotal').text(res.summary.total_karyawan);
                    $('#sumDone').text(res.summary.sudah_dihitung);
                    $('#sumPending').text(res.summary.belum_dihitung);
                    $('#sumGross').text(formatIDR(res.summary.total_gaji_bersih));
                    $('#sumAvg').text(formatIDR(res.summary.avg_gaji_bersih));
                    $('#sumMedian').text(formatIDR(res.summary.median_gaji_bersih));
                    $('#sumAllowance').text(formatIDR(res.summary.total_tunjangan));

                    renderChartsSafe(res.charts);
                    renderTable(res);
                    $('#mainLoading').addClass('hidden');
                }).fail(function(xhr, status, error) {
                    console.error('Error:', error);
                    alert('Gagal memuat data: ' + error);
                    $('#mainLoading').addClass('hidden');
                });
            };
            
            let salaryChart = null,
                allowanceChart = null,
                trendChart = null,
                deductionChart = null;

            function renderCharts(charts) {
                ['salaryRangeChart', 'allowanceChart', 'trendChart', 'deductionChart'].forEach(id => {
                    const existing = Chart.getChart(id);
                    if (existing) existing.destroy();
                });

                // Salary Range Chart
                const ctx1 = document.getElementById('salaryRangeChart');
                if (ctx1 && charts?.salary_ranges?.labels?.length > 0) {
                    salaryChart = new Chart(ctx1, {
                        type: 'doughnut',
                        data: {
                            labels: charts.salary_ranges.labels.filter(l => l),
                            datasets: [{
                                data: charts.salary_ranges.counts.map(c => c || 0),
                                backgroundColor: ['#0d6efd', '#198754', '#ffc107', '#dc3545',
                                    '#6c757d'
                                ]
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: {
                                        boxWidth: 12,
                                        font: {
                                            size: 10
                                        },
                                        padding: 10
                                    }
                                }
                            }
                        }
                    });
                }

                // Allowance by Divisi Chart
                const ctx2 = document.getElementById('allowanceChart');
                if (ctx2 && charts?.allowance_by_divisi?.labels?.length > 0) {
                    allowanceChart = new Chart(ctx2, {
                        type: 'bar',
                        data: {
                            labels: charts.allowance_by_divisi.labels.filter(l => l),
                            datasets: [{
                                label: 'Total Tunjangan',
                                data: charts.allowance_by_divisi.allowance?.map(v => v || 0) || [],
                                backgroundColor: '#0d6efd'
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
                                        callback: v => 'Rp ' + ((v || 0) / 1000000).toFixed(1) + 'J'
                                    }
                                }
                            }
                        }
                    });
                }

                // Top Deductions Chart
                const ctx3 = document.getElementById('deductionChart');
                if (ctx3 && charts?.top_deductions?.labels?.length > 0) {
                    deductionChart = new Chart(ctx3, {
                        type: 'bar',
                        data: {
                            labels: charts.top_deductions.labels.filter(l => l),
                            datasets: [{
                                label: 'Total Potongan',
                                data: charts.top_deductions.total_values?.map(v => v || 0) || [],
                                backgroundColor: '#dc3545'
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
                                            const idx = context.dataIndex;
                                            const avg = charts.top_deductions.averages[idx] || 0;
                                            const empCount = charts.top_deductions.employee_counts[
                                                idx] || 0;
                                            return [
                                                `Total: ${formatIDR(context.parsed.y)}`,
                                                `Karyawan: ${empCount}`,
                                                `Rata-rata: ${formatIDR(avg)}`
                                            ];
                                        }
                                    }
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        callback: v => 'Rp ' + ((v || 0) / 1000000).toFixed(1) + 'J'
                                    }
                                }
                            }
                        }
                    });
                }

                // Monthly Trend Chart
                const ctx4 = document.getElementById('trendChart');
                if (ctx4 && charts?.monthly_trend?.length > 0) {
                    trendChart = new Chart(ctx4, {
                        type: 'line',
                        data: {
                            labels: charts.monthly_trend.map(t => t?.month || ''),
                            datasets: [{
                                label: 'Total Gaji',
                                data: charts.monthly_trend.map(t => t?.total_gaji || 0),
                                borderColor: '#198754',
                                backgroundColor: 'rgba(25,135,84,0.1)',
                                fill: true,
                                tension: 0.3
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
                                        callback: v => 'Rp ' + ((v || 0) / 1000000).toFixed(1) + 'J'
                                    }
                                }
                            }
                        }
                    });
                }
            }

            function renderChartsSafe(charts) {
                const safeCharts = {
                    salary_ranges: charts?.salary_ranges || {
                        labels: [],
                        counts: [],
                        averages: []
                    },
                    allowance_by_divisi: charts?.allowance_by_divisi || {
                        labels: [],
                        allowance: [],
                        avg_salary: []
                    },
                    monthly_trend: charts?.monthly_trend || [],
                    top_deductions: charts?.top_deductions || {
                        labels: [],
                        total_values: [],
                        employee_counts: [],
                        averages: []
                    }
                };
                renderCharts(safeCharts);
            }

            function renderTable(res) {
                let html = '';
                const start = (res.pagination.current_page - 1) * res.pagination.per_page + 1;
                const end = Math.min(res.pagination.current_page * res.pagination.per_page, res.pagination.total);

                $('#showingStart').text(res.pagination.total > 0 ? start : 0);
                $('#showingEnd').text(end);
                $('#totalItems').text(res.pagination.total);

                if (allData.length === 0) {
                    html = '<tr><td colspan="9" class="text-center text-muted py-4">Tidak ada data</td></tr>';
                } else {
                    allData.forEach((row, idx) => {
                        const no = (res.pagination.current_page - 1) * res.pagination.per_page + idx + 1;
                        const badge = row.status === 'Sudah Dihitung' ?
                            '<span class="status-badge status-done">Sudah</span>' :
                            '<span class="status-badge status-pending">Belum</span>';

                        html += `<tr>
                            <td>${no}</td>
                            <td><strong>${row.nama}</strong><br><small class="text-muted">${row.kode || '-'}</small></td>
                            <td>${row.divisi}<br><small class="text-muted">${row.jabatan}</small></td>
                            <td class="text-end">${formatIDR(row.gaji_pokok)}</td>
                            <td class="text-end text-success">${formatSigned(row.total_tunjangan)}</td>
                            <td class="text-end text-danger">-${formatIDR(row.total_potongan)}</td>
                            <td class="text-end fw-bold">${formatIDR(row.gaji_bersih)}</td>
                            <td class="text-center">${badge}</td>
                            <td class="text-center"><button class="btn btn-sm btn-outline-primary" onclick="openDetail(${row.id})"><i class="fa-solid fa-eye"></i></button></td>
                        </tr>`;
                    });
                }
                $('#payrollBody').html(html);

                let pagHtml = '';
                if (res.pagination.last_page > 1) {
                    for (let i = 1; i <= res.pagination.last_page; i++) {
                        const activeClass = i === currentPage ? 'active' : '';
                        pagHtml += `<button class="${activeClass}" onclick="loadData(${i})">${i}</button>`;
                    }
                }
                $('#paginationContainer').html(pagHtml);
            }

            window.openDetail = function(id) {
                const row = allData.find(d => d.id === id);
                if (!row) return;

                $('#modalNama').text(row.nama);
                $('#modalKode').text(row.kode || '-');
                $('#modalDivisi').text(row.divisi);
                $('#modalJabatan').text(row.jabatan);

                let html =
                    `<tr><td>Gaji Pokok</td><td>-</td><td>-</td><td class="text-end">${formatIDR(row.gaji_pokok)}</td></tr>`;
                row.details.forEach(d => {
                    html +=
                        `<tr><td>${d.nama || '-'}</td><td>${d.tipe || '-'}</td><td>${d.keterangan || '-'}</td><td class="text-end">${formatSigned(d.nilai)}</td></tr>`;
                });

                $('#modalDetails').html(html);
                $('#modalNet').text(formatIDR(row.gaji_bersih));
                $('#modalDetail').modal('show');
            };

            $('#btnFilter').click(function() {
                loadData(1);
            });

            $('#exportCsv').click(function() {
                const month = $('#filterBulan').val();
                const year = $('#filterTahun').val();
                window.location.href =
                    `{{ route('office.HR.payroll.export.csv') }}?month=${month}&year=${year}&search=${searchQuery}`;
            });

            $('#exportPdf').click(function() {
                const month = $('#filterBulan').val();
                const year = $('#filterTahun').val();
                window.location.href =
                    `{{ route('office.HR.payroll.export.pdf') }}?month=${month}&year=${year}`;
            });

            $('#searchPayroll').on('keyup', function(e) {
                if (e.keyCode === 13 || this.value.length > 2 || this.value === '') {
                    searchQuery = this.value;
                    loadData(1);
                }
            });

            loadData(1);
        });
    </script>
@endsection
