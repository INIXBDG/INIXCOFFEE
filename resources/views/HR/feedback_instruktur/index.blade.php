
@extends('layout_HR.app')

@section('content_HR')
<div class="container-fluid px-4 py-4">

    <div class="mb-4">
        <h1 class="h4 fw-bold">Rekap Feedback Instruktur</h1>
        <p class="text-muted">Monitoring nilai feedback instruktur berdasarkan periode RKM.</p>
    </div>

    {{-- Filter --}}
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label for="filter_tahun" class="form-label">Tahun</label>
                    <select id="filter_tahun" class="form-select">
                        @for ($y = date('Y'); $y >= 2023; $y--)
                            <option value="{{ $y }}" {{ $y == date('Y') ? 'selected' : '' }}>
                                {{ $y }}
                            </option>
                        @endfor
                    </select>
                </div>

                <div class="col-md-3">
                    <label for="filter_type" class="form-label">Periode</label>
                    <select id="filter_type" class="form-select">
                        <option value="year">Seluruh Tahun</option>
                        <option value="month">Bulanan</option>
                        <option value="quarter">Kuartalan</option>
                    </select>
                </div>

                <div class="col-md-3 d-none" id="wrapper_period">
                    <label for="filter_period" class="form-label" id="label_period">
                        Pilih Periode
                    </label>
                    <select id="filter_period" class="form-select"></select>
                </div>

                <div class="col-md-3">
                    <button type="button" id="btn_filter" class="btn btn-primary w-100">
                        <i class="fas fa-filter me-1"></i> Terapkan Filter
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Statistik rata-rata --}}
    {{-- <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Rata-rata Instruktur</div>
                    <div class="fs-2 fw-bold" id="avg_instruktur">-</div>
                </div>
            </div>
        </div>
    </div> --}}

    <div class="row g-3 mb-4">
        <div class="col-xl-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white">
                    <h6 class="mb-0">Tren Rata-rata Feedback per Instruktur</h6>
                </div>
                <div class="card-body">
                    <div style="height: 320px">
                        <canvas id="chart_feedback_instruktur"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white">
                    <h6 class="mb-0">Jumlah Kelas per Instruktur</h6>
                </div>
                <div class="card-body">
                    <div style="height: 320px">
                        <canvas id="chart_kelas_instruktur"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white">
                    <h6 class="mb-0">Persentase Kelas per Instruktur</h6>
                </div>
                <div class="card-body">
                    <div style="height: 320px">
                        <canvas id="chart_persentase_instruktur"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white">
                    <h6 class="mb-0">Jumlah Feedback dan Rata-rata per Instruktur</h6>
                </div>
                <div class="card-body">
                    <div style="height: 320px">
                        <canvas id="chart_jumlah_feedback_instruktur"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Total feedback --}}
    <div class="alert alert-light border mb-4">
        Total data feedback:
        <strong id="total_feedback">0</strong>
        <span class="ms-3 text-muted" id="label_hasil_periode"></span>
    </div>

    {{-- Tabel per instruktur --}}
    <div class="card shadow-sm">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h5 class="mb-0">Rata-rata Feedback Per Instruktur</h5>
            <div class="d-flex gap-2">
                <a href="#" id="btn_export_pdf" class="btn btn-sm btn-danger">
                    <i class="fas fa-file-pdf me-1"></i> Export PDF
                </a>
                <a href="#" id="btn_export_excel" class="btn btn-sm btn-success">
                    <i class="fas fa-file-excel me-1"></i> Export Excel
                </a>
            </div>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table id="table_instruktur" class="table table-hover table-bordered align-middle">
                    <thead class="table-light">
                        <tr id="header_instruktur">
                            <th class="text-center" width="60">No</th>
                            <th>Kode Karyawan</th>
                            <th>Nama Instruktur</th>
                            <th class="text-center">Grand Total</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="tbody_instruktur">
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">
                                Pilih periode untuk menampilkan data.
                            </td>
                        </tr>
                    </tbody>
                    <tfoot id="footer_instruktur"></tfoot>
                </table>
            </div>
        </div>
    </div>

    <div class="card shadow-sm mt-4">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0">Rekap Kelas Instruktur</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-bordered align-middle mb-0">
                    <thead class="table-light">
                        <tr id="header_kelas">
                            <th>Kode Karyawan</th>
                            <th>Nama Instruktur</th>
                            <th class="text-center">Grand Total</th>
                            <th class="text-center">Persentase</th>
                        </tr>
                    </thead>
                    <tbody id="tbody_kelas">
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">
                                Pilih periode untuk menampilkan data.
                            </td>
                        </tr>
                    </tbody>
                    <tfoot id="footer_kelas"></tfoot>
                </table>
            </div>
        </div>
    </div>

</div>
<div class="modal fade" id="modalDetailRkm" tabindex="-1" aria-labelledby="modalDetailRkmLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title" id="modalDetailRkmLabel">
                    Detail RKM: <span id="detailNamaInstruktur" class="fw-bold"></span> 
                    <span id="detailPeranInstruktur" class="badge bg-info text-white ms-2"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" width="50">No</th>
                                <th>Nama Materi</th>
                                <th>Perusahaan</th>
                                <th>Tanggal</th>
                                <th>Metode Kelas</th>
                                <th class="text-center">Rata-rata Feedback</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyDetailRkm">
                            <!-- Data dimuat dari AJAX -->
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>

    $(document).ready(function () {
        const baseUrl = @json(url('/api/feedback-instruktur'));
        const exportPdfUrl = @json(route('HR.feedback-instruktur.export.pdf'));
        const exportExcelUrl = @json(route('HR.feedback-instruktur.export.excel'));
        let activeFilters = {};
        let feedbackChart = null;
        let kelasChart = null;
        let persentaseChart = null;
        let jumlahFeedbackChart = null;

        const namaBulan = [
            'Januari', 'Februari', 'Maret', 'April',
            'Mei', 'Juni', 'Juli', 'Agustus',
            'September', 'Oktober', 'November', 'Desember'
        ];

        function updatePeriodOptions() {
            const type = $('#filter_type').val();
            const $period = $('#filter_period');

            $period.empty();

            if (type === 'month') {
                $('#label_period').text('Pilih Bulan');

                namaBulan.forEach(function (nama, index) {
                    $period.append(
                        $('<option>', {
                            value: index + 1,
                            text: nama
                        })
                    );
                });

                $('#wrapper_period').removeClass('d-none');
            } else if (type === 'quarter') {
                $('#label_period').text('Pilih Kuartal');

                [
                    { value: 1, text: 'Kuartal 1 (Jan - Mar)' },
                    { value: 2, text: 'Kuartal 2 (Apr - Jun)' },
                    { value: 3, text: 'Kuartal 3 (Jul - Sep)' },
                    { value: 4, text: 'Kuartal 4 (Okt - Des)' }
                ].forEach(function (item) {
                    $period.append($('<option>', item));
                });

                $('#wrapper_period').removeClass('d-none');
            } else {
                $('#wrapper_period').addClass('d-none');
            }
        }

        function formatNilai(nilai) {
            const number = Number(nilai);

            return Number.isFinite(number) ? number.toFixed(1) : '0.0';
        }

        function formatPersentase(nilai) {
            const number = Number(nilai);

            return Number.isFinite(number)
                ? new Intl.NumberFormat('id-ID', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }).format(number) + '%'
                : '0,00%';
        }

        function escapeHtml(value) {
            return $('<div>').text(value ?? '-').html();
        }

        function destroyDataTable() {
            if ($.fn.DataTable.isDataTable('#table_instruktur')) {
                $('#table_instruktur').DataTable().destroy();
            }
        }

        function initializeDataTable() {
            const lastColumn = $('#header_instruktur th').length - 1;

            $('#table_instruktur').DataTable({
                pageLength: 10,
                lengthMenu: [[10, 25, 50, -1], [10, 25, 50, 'Semua']],
                order: [[2, 'asc']],
                columnDefs: [{
                    targets: [0, lastColumn],
                    orderable: false,
                    searchable: false
                }],
                language: {
                    emptyTable: 'Tidak ada data yang tersedia.',
                    info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ data',
                    infoEmpty: 'Menampilkan 0 sampai 0 dari 0 data',
                    infoFiltered: '(disaring dari _MAX_ total data)',
                    lengthMenu: 'Tampilkan _MENU_ data',
                    search: 'Cari:',
                    zeroRecords: 'Data tidak ditemukan.',
                    paginate: {
                        first: 'Pertama',
                        last: 'Terakhir',
                        next: 'Berikutnya',
                        previous: 'Sebelumnya'
                    }
                },
                drawCallback: function () {
                    const api = this.api();
                    const start = api.page.info().start;

                    api.column(0, { page: 'current' }).nodes().each(function (cell, index) {
                        cell.textContent = start + index + 1;
                    });
                }
            });
        }

        function getDisplayedMonths(type, period) {
            if (type === 'month') {
                return [Number(period)];
            }

            if (type === 'quarter') {
                const firstMonth = ((Number(period) - 1) * 3) + 1;
                return [firstMonth, firstMonth + 1, firstMonth + 2];
            }

            return namaBulan.map((_, index) => index + 1);
        }

        function renderTableHeader(months) {
            const monthHeaders = months.map(function (month) {
                return `<th class="text-center">${escapeHtml(namaBulan[month - 1])}</th>`;
            }).join('');

            $('#header_instruktur').html(`
                <th class="text-center" width="60">No</th>
                <th>Kode Karyawan</th>
                <th>Nama Instruktur</th>
                ${monthHeaders}
                <th class="text-center">Grand Total</th>
                <th class="text-center">Aksi</th>
            `);

            $('#header_kelas').html(`
                <th>Kode Karyawan</th>
                <th>Nama Instruktur</th>
                ${monthHeaders}
                <th class="text-center">Grand Total</th>
                <th class="text-center">Persentase</th>
            `);
        }

        function renderClassTable(response, months) {
            const data = response.per_kelas || [];
            const columnCount = months.length + 4;

            if (data.length === 0) {
                $('#tbody_kelas').html(`
                    <tr>
                        <td colspan="${columnCount}" class="text-center text-muted py-4">
                            Tidak ada data kelas instruktur pada periode ini.
                        </td>
                    </tr>
                `);
                $('#footer_kelas').empty();
                return;
            }

            const rows = data.map(function (item) {
                const monthlyValues = months.map(function (month) {
                    const count = item.kelas_per_bulan?.[month];
                    return `<td class="text-center">${count ?? '-'}</td>`;
                }).join('');

                return `
                    <tr>
                        <td>${escapeHtml(item.kode_karyawan)}</td>
                        <td>${escapeHtml(item.nama)}</td>
                        ${monthlyValues}
                        <td class="text-center fw-bold">${item.total_kelas}</td>
                        <td class="text-center">${formatPersentase(item.persentase)}</td>
                    </tr>
                `;
            }).join('');

            const monthlyTotals = months.map(function (month) {
                const count = response.total_kelas_per_bulan?.[month];
                return `<td class="text-center">${count ?? 0}</td>`;
            }).join('');

            $('#tbody_kelas').html(rows);
            $('#footer_kelas').html(`
                <tr class="table-light fw-bold">
                    <td colspan="2">Grand Total</td>
                    ${monthlyTotals}
                    <td class="text-center">${response.total_kelas ?? 0}</td>
                    <td class="text-center">100%</td>
                </tr>
            `);
        }

        function updateCharts(response, months) {
            [feedbackChart, kelasChart, persentaseChart, jumlahFeedbackChart].forEach(function (chart) {
                if (chart) {
                    chart.destroy();
                }
            });

            const palette = [
                '#0d6efd', '#dc3545', '#198754', '#fd7e14', '#6f42c1',
                '#20c997', '#d63384', '#0dcaf0', '#6c757d', '#ffc107'
            ];
            const monthLabels = months.map(month => namaBulan[month - 1]);
            const instructorsWithFeedback = (response.per_instruktur || []).filter(function (item) {
                return item.total_penilaian > 0;
            });
            const instructorsWithClasses = response.per_kelas || [];

            feedbackChart = new Chart(document.getElementById('chart_feedback_instruktur'), {
                type: 'line',
                data: {
                    labels: monthLabels,
                    datasets: instructorsWithFeedback.map(function (item, index) {
                        return {
                            label: item.nama,
                            data: months.map(function (month) {
                                return item.rata_rata_per_bulan?.[month] ?? null;
                            }),
                            borderColor: palette[index % palette.length],
                            backgroundColor: palette[index % palette.length],
                            tension: 0.25,
                            spanGaps: false
                        };
                    })
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: { mode: 'index', intersect: false },
                    scales: {
                        y: {
                            beginAtZero: true,
                            suggestedMax: 5,
                            title: { display: true, text: 'Rata-rata feedback' }
                        }
                    }
                }
            });

            kelasChart = new Chart(document.getElementById('chart_kelas_instruktur'), {
                type: 'bar',
                data: {
                    labels: instructorsWithClasses.map(item => item.nama),
                    datasets: months.map(function (month, index) {
                        return {
                            label: namaBulan[month - 1],
                            data: instructorsWithClasses.map(function (item) {
                                return item.kelas_per_bulan?.[month] ?? 0;
                            }),
                            backgroundColor: palette[index % palette.length]
                        };
                    })
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: { stacked: false },
                        y: {
                            beginAtZero: true,
                            title: { display: true, text: 'Jumlah kelas' }
                        }
                    }
                }
            });

            persentaseChart = new Chart(document.getElementById('chart_persentase_instruktur'), {
                type: 'doughnut',
                data: {
                    labels: instructorsWithClasses.map(item => item.nama),
                    datasets: [{
                        data: instructorsWithClasses.map(item => item.persentase),
                        backgroundColor: instructorsWithClasses.map(
                            (_, index) => palette[index % palette.length]
                        )
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    return context.label + ': ' + formatPersentase(context.raw);
                                }
                            }
                        }
                    }
                }
            });

            jumlahFeedbackChart = new Chart(
                document.getElementById('chart_jumlah_feedback_instruktur'),
                {
                    type: 'bar',
                    data: {
                        labels: instructorsWithFeedback.map(item => item.nama),
                        datasets: [{
                            type: 'bar',
                            label: 'Jumlah Feedback',
                            data: instructorsWithFeedback.map(item => item.total_feedback),
                            backgroundColor: 'rgba(13, 110, 253, 0.65)',
                            borderColor: '#0d6efd',
                            borderWidth: 1,
                            yAxisID: 'y'
                        }, {
                            type: 'line',
                            label: 'Rata-rata Feedback',
                            data: instructorsWithFeedback.map(item => item.rata_rata),
                            borderColor: '#dc3545',
                            backgroundColor: '#dc3545',
                            pointRadius: 4,
                            pointHoverRadius: 6,
                            tension: 0.25,
                            yAxisID: 'y1'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: { mode: 'index', intersect: false },
                        scales: {
                            y: {
                                beginAtZero: true,
                                title: { display: true, text: 'Jumlah feedback' },
                                ticks: { precision: 0 }
                            },
                            y1: {
                                beginAtZero: true,
                                suggestedMax: 5,
                                position: 'right',
                                title: { display: true, text: 'Rata-rata feedback' },
                                grid: { drawOnChartArea: false }
                            }
                        }
                    }
                }
            );
        }

        function loadData() {
            const year = $('#filter_tahun').val();
            const type = $('#filter_type').val();
            const period = $('#filter_period').val();
            activeFilters = { year: year, type: type };

            if (type !== 'year') {
                activeFilters.period = period;
            }
            const months = getDisplayedMonths(type, period);
            destroyDataTable();
            renderTableHeader(months);
            const columnCount = $('#header_instruktur th').length;

            let url = baseUrl + '/' + encodeURIComponent(year) + '/' + type;

            if (type !== 'year') {
                url += '/' + encodeURIComponent(period);
            }

            $('#btn_filter').prop('disabled', true);
            $('#tbody_instruktur').html(`
                <tr>
                    <td colspan="${columnCount}" class="text-center py-4">
                        <span class="spinner-border spinner-border-sm me-2"></span>
                        Memuat data...
                    </td>
                </tr>
            `);
            $('#footer_instruktur').empty();
            $('#tbody_kelas').html(`
                <tr>
                    <td colspan="${months.length + 4}" class="text-center py-4">
                        <span class="spinner-border spinner-border-sm me-2"></span>
                        Memuat data...
                    </td>
                </tr>
            `);
            $('#footer_kelas').empty();

            $.ajax({
                url: url,
                method: 'GET',
                dataType: 'json'
            })
            .done(function (response) {
                if (response.status !== 'success') {
                    throw new Error(response.message || 'Gagal mengambil data.');
                }

                const avg = response.average_feedback || {};

                $('#avg_instruktur').text(formatNilai(avg.instruktur));
                $('#total_feedback').text(response.total_feedback ?? 0);

                let label = year;

                if (type === 'month') {
                    label = namaBulan[Number(period) - 1] + ' ' + year;
                } else if (type === 'quarter') {
                    label = 'Kuartal ' + period + ' ' + year;
                } else {
                    label = 'Januari - Desember ' + year;
                }

                $('#label_hasil_periode').text(label);

                const data = response.per_instruktur || [];
                renderClassTable(response, months);
                updateCharts(response, months);

                if (data.length === 0) {
                    $('#tbody_instruktur').html(`
                        <tr>
                            <td colspan="${columnCount}" class="text-center text-muted py-4">
                                Tidak ada data feedback instruktur pada periode ini.
                            </td>
                        </tr>
                    `);
                    $('#footer_instruktur').empty();
                    return;
                }

                let html = '';
                let hasRows = false;

                data.forEach(function (item, index) {
                    if (
                        !item.kode_karyawan ||
                        item.kode_karyawan.trim() === '-' ||
                        item.total_feedback <= 0 ||
                        item.total_penilaian <= 0
                    ) {
                        return;
                    }

                    const monthlyValues = months.map(function (month) {
                        const value = item.rata_rata_per_bulan?.[month];
                        return `<td class="text-center">${value === undefined ? '-' : formatNilai(value)}</td>`;
                    }).join('');

                    hasRows = true;
                    html += `
                        <tr>
                            <td class="text-center">${index + 1}</td>
                            <td>${escapeHtml(item.kode_karyawan)}</td>
                            <td>${escapeHtml(item.nama)}</td>
                            ${monthlyValues}
                            <td class="text-center fw-bold">${formatNilai(item.rata_rata)}</td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-info text-white btn-detail-rkm" 
                                    data-kode="${escapeHtml(item.kode_karyawan)}" 
                                    data-nama="${escapeHtml(item.nama)}"
                                    data-peran="instruktur">
                                    <i class="fas fa-eye me-1"></i>Detail RKM
                                </button>
                            </td>
                        </tr>
                    `;
                });

                if (!hasRows) {
                    html = `
                        <tr>
                            <td colspan="${columnCount}" class="text-center text-muted py-4">
                                Tidak ada data instruktur dengan penilaian valid.
                            </td>
                        </tr>
                    `;
                }

                $('#tbody_instruktur').html(html);

                if (hasRows) {
                    const monthlyTotals = months.map(function (month) {
                        const value = response.average_feedback_per_bulan?.[month];
                        return `<td class="text-center">${value === undefined ? '-' : formatNilai(value)}</td>`;
                    }).join('');

                    $('#footer_instruktur').html(`
                        <tr class="table-light fw-bold">
                            <td></td>
                            <td></td>
                            <td>Grand Total</td>
                            ${monthlyTotals}
                            <td class="text-center">${formatNilai(avg.instruktur)}</td>
                            <td></td>
                        </tr>
                    `);
                    initializeDataTable();
                } else {
                    $('#footer_instruktur').empty();
                }
            })
            .fail(function (xhr) {
                const message = xhr.responseJSON?.message
                    || 'Gagal memuat data feedback instruktur.';

                $('#tbody_instruktur').html(`
                    <tr>
                        <td colspan="${columnCount}" class="text-center text-danger py-4">
                            ${escapeHtml(message)}
                        </td>
                    </tr>
                `);
                $('#footer_instruktur').empty();
                $('#tbody_kelas').html(`
                    <tr>
                        <td colspan="${months.length + 4}" class="text-center text-danger py-4">
                            ${escapeHtml(message)}
                        </td>
                    </tr>
                `);
                $('#footer_kelas').empty();
                updateCharts({
                    per_instruktur: [],
                    per_kelas: []
                }, months);

                $('#avg_instruktur').text('-');
                $('#total_feedback').text('0');
            })
            .always(function () {
                $('#btn_filter').prop('disabled', false);
            });
        }

        $('#filter_type').on('change', updatePeriodOptions);
        $('#btn_filter').on('click', loadData);
        $('#btn_export_pdf').on('click', function (event) {
            event.preventDefault();
            window.location.href = exportPdfUrl + '?' + new URLSearchParams(activeFilters).toString();
        });
        $('#btn_export_excel').on('click', function (event) {
            event.preventDefault();
            window.location.href = exportExcelUrl + '?' + new URLSearchParams(activeFilters).toString();
        });

        updatePeriodOptions();
        loadData();

         // Listener untuk tombol Detail RKM
        $(document).on('click', '.btn-detail-rkm', function () {
            const kodeKaryawan = $(this).data('kode');
            const namaInstruktur = $(this).data('nama');
            const peran = $(this).data('peran');
            const labelPeran = 'Instruktur';
            
            const year = $('#filter_tahun').val();
            const type = $('#filter_type').val();
            const period = $('#filter_period').val();

            // Set Judul Modal beserta Badge Peran
            $('#detailNamaInstruktur').text(namaInstruktur);
            $('#detailPeranInstruktur').text(labelPeran);

            $('#tbodyDetailRkm').html(`
                <tr>
                    <td colspan="6" class="text-center py-4">
                        <span class="spinner-border spinner-border-sm me-2"></span>Memuat detail RKM...
                    </td>
                </tr>
            `);
            
            $('#modalDetailRkm').modal('show');

            // Endpoint URL menyertakan kode karyawan & peran
            let detailUrl = baseUrl + '/detail/' 
                + encodeURIComponent(kodeKaryawan) + '/' 
                + encodeURIComponent(peran) + '/' 
                + encodeURIComponent(year) + '/' 
                + type;

            if (type !== 'year') {
                detailUrl += '/' + encodeURIComponent(period);
            }

            $.ajax({
                url: detailUrl,
                method: 'GET',
                dataType: 'json'
            }).done(function(response) {
                if (response.status === 'success') {
                    let html = '';
                    const data = response.data;

                    if (data.length === 0) {
                        html = `<tr><td colspan="6" class="text-center text-muted py-4">Tidak ada data RKM untuk peran ini pada periode terpilih.</td></tr>`;
                    } else {
                        data.forEach(function(item, idx) {
                            html += `
                                <tr>
                                    <td class="text-center">${idx + 1}</td>
                                    <td>${escapeHtml(item.nama_materi)}</td>
                                    <td>${escapeHtml(item.nama_perusahaan)}</td>
                                    <td>${escapeHtml(item.tanggal)}</td>
                                    <td>
                                        <span class="badge bg-secondary">${escapeHtml(item.metode_kelas)}</span>
                                    </td>
                                    <td class="text-center fw-bold text-primary">
                                        ${formatNilai(item.rata_rata_feedback)}
                                    </td>
                                </tr>
                            `;
                        });
                    }
                    $('#tbodyDetailRkm').html(html);
                } else {
                    $('#tbodyDetailRkm').html(`<tr><td colspan="6" class="text-center text-danger py-4">${escapeHtml(response.message)}</td></tr>`);
                }
            }).fail(function() {
                $('#tbodyDetailRkm').html(`<tr><td colspan="6" class="text-center text-danger py-4">Gagal memuat data dari server.</td></tr>`);
            });
        });
    });
</script>
@endsection
