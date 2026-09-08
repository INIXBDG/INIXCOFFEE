@extends('layouts_office.app')

@section('office_contents')
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="mb-0 fw-bold">Rekap Modul</h4>
        </div>

        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label small text-muted">Tahun</label>
                        <select id="filter-tahun" class="form-select">
                            @for ($y = date('Y'); $y >= 2022; $y--)
                                <option value="{{ $y }}" {{ $y == date('Y') ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small text-muted">Tipe Periode</label>
                        <select id="filter-tipe" class="form-select">
                            <option value="">Semua periode</option>
                            <option value="bulan">Bulan</option>
                            <option value="triwulan">Triwulan</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small text-muted">Detail</label>
                        <select id="filter-sub" class="form-select" style="display:none;">
                            <option value="">Pilih...</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex">
                        <button class="btn btn-primary w-100" onclick="loadRekap()">Tampilkan</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body">
                        <div class="text-muted small">Total nomor</div>
                        <div class="fs-3 fw-bold mt-2" id="total-nomor">0</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body">
                        <div class="text-muted small">Total modul</div>
                        <div class="fs-3 fw-bold mt-2" id="total-modul">0</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body">
                        <div class="text-muted small">Regular</div>
                        <div class="fs-3 fw-bold mt-2 text-primary" id="total-regular">0</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body">
                        <div class="text-muted small">Authorize</div>
                        <div class="fs-3 fw-bold mt-2 text-warning" id="total-authorize">0</div>
                    </div>
                </div>
            </div>
        </div>

        <ul class="nav nav-tabs mb-3" id="rekap-tabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="tab-data-btn" data-bs-toggle="tab" data-bs-target="#tab-data" type="button" role="tab" aria-controls="tab-data" aria-selected="true">
                    Data
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="tab-grafik-btn" data-bs-toggle="tab" data-bs-target="#tab-grafik" type="button" role="tab" aria-controls="tab-grafik" aria-selected="false">
                    Grafik
                </button>
            </li>
        </ul>

        <div class="tab-content" id="rekap-tabs-content">
            <div class="tab-pane fade show active" id="tab-data" role="tabpanel" aria-labelledby="tab-data-btn">
                <div class="row g-4">
                    <div class="col-lg-6">
                        <div class="card border-0 shadow-sm rounded-4 h-100">
                            <div class="card-header bg-white border-0 pt-3 pb-2">
                                <h6 class="mb-0 fw-bold">Materi Regular</h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm align-middle mb-0">
                                        <thead>
                                            <tr>
                                                <th>No</th>
                                                <th>Materi</th>
                                                <th>Kategori</th>
                                                <th>Vendor</th>
                                                <th>Jumlah Modul</th>
                                            </tr>
                                        </thead>
                                        <tbody id="tbody-regular">
                                            <tr>
                                                <td colspan="5" class="text-muted text-center py-4">Belum ada data</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <nav>
                                    <ul class="pagination pagination-sm justify-content-end mb-0 mt-2" id="pagination-regular"></ul>
                                </nav>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="card border-0 shadow-sm rounded-4 h-100">
                            <div class="card-header bg-white border-0 pt-3 pb-2">
                                <h6 class="mb-0 fw-bold">Materi Authorize</h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm align-middle mb-0">
                                        <thead>
                                            <tr>
                                                <th>No</th>
                                                <th>Materi</th>
                                                <th>Kategori</th>
                                                <th>Vendor</th>
                                                <th>Jumlah Modul</th>
                                            </tr>
                                        </thead>
                                        <tbody id="tbody-authorize">
                                            <tr>
                                                <td colspan="5" class="text-muted text-center py-4">Belum ada data</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <nav>
                                    <ul class="pagination pagination-sm justify-content-end mb-0 mt-2" id="pagination-authorize"></ul>
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="tab-grafik" role="tabpanel" aria-labelledby="tab-grafik-btn">
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-white border-0 pt-3 pb-2">
                        <h6 class="mb-0 fw-bold">Materi Regular</h6>
                    </div>
                    <div class="card-body">
                        <canvas id="chart-regular" height="120"></canvas>
                        <div class="text-muted small text-center py-4 d-none" id="chart-regular-empty">Tidak ada data</div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-header bg-white border-0 pt-3 pb-2">
                        <h6 class="mb-0 fw-bold">Materi Authorize</h6>
                    </div>
                    <div class="card-body">
                        <canvas id="chart-authorize" height="120"></canvas>
                        <div class="text-muted small text-center py-4 d-none" id="chart-authorize-empty">Tidak ada data</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
    <script>
        const URL_REKAP = "{{ route('office.modul.rekap.json') }}";
        const PER_PAGE = 20;
        const CHART_TOP_N = 15;

        // State per tabel: baris hasil agregasi + halaman aktif
        const tableState = {
            regular: { rows: [], page: 1 },
            authorize: { rows: [], page: 1 },
        };

        // Instance Chart.js per key, biar bisa di-destroy sebelum re-render
        const chartInstances = {
            regular: null,
            authorize: null,
        };

        function populateSubFilter(tipe) {
            const sub = document.getElementById('filter-sub');
            sub.innerHTML = '<option value="">Pilih...</option>';

            if (tipe === 'triwulan') {
                ['Triwulan 1 (Jan-Mar)', 'Triwulan 2 (Apr-Jun)', 'Triwulan 3 (Jul-Sep)', 'Triwulan 4 (Okt-Des)']
                    .forEach((label, index) => {
                        sub.innerHTML += `<option value="${index + 1}">${label}</option>`;
                    });
                sub.style.display = '';
                return;
            }

            if (tipe === 'bulan') {
                [
                    'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                    'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
                ].forEach((label, index) => {
                    sub.innerHTML += `<option value="${index + 1}">${label}</option>`;
                });
                sub.style.display = '';
                return;
            }

            sub.style.display = 'none';
        }

        document.getElementById('filter-tipe').addEventListener('change', function () {
            populateSubFilter(this.value);
        });

        function formatCurrency(value) {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                maximumFractionDigits: 0
            }).format(value || 0);
        }

        // Ubah object hasil groupBy() jadi array baris teragregasi {name, kategori, vendor, jumlahModul, totalNominal}
        function buildRows(groupedData) {
            const groups = Object.values(groupedData || {});
            return groups
                .map((rows) => {
                    const first = rows[0] || {};
                    const materi = first.materi || {};
                    const name =
                        materi.nama_materi ||
                        first.nama_materi ||
                        materi.kode_materi ||
                        first.kode_materi ||
                        'Materi tidak diketahui';

                    const kategori = materi.kategori_materi || '-';
                    const vendor = materi.vendor || '-';

                    const totalNominal = rows.reduce(
                        (sum, r) => sum + (Number(r.total) || 0),
                        0
                    );

                    return {
                        name,
                        kategori,
                        vendor,
                        jumlahModul: rows.length,
                        totalNominal
                    };
                })
                .sort((a, b) => b.jumlahModul - a.jumlahModul);
        }

        function renderTable(key) {
            const state = tableState[key];
            const tbody = document.getElementById(`tbody-${key}`);
            const pager = document.getElementById(`pagination-${key}`);
            const colspan = 5;

            if (state.rows.length === 0) {
                tbody.innerHTML = `<tr><td colspan="${colspan}" class="text-muted text-center py-4">Tidak ada data</td></tr>`;
                pager.innerHTML = '';
                return;
            }

            const totalPages = Math.ceil(state.rows.length / PER_PAGE);
            state.page = Math.min(Math.max(state.page, 1), totalPages);

            const start = (state.page - 1) * PER_PAGE;
            const pageRows = state.rows.slice(start, start + PER_PAGE);

            tbody.innerHTML = pageRows.map((row, idx) => `
                <tr>
                    <td>${start + idx + 1}</td>
                    <td>${row.name}</td>
                    <td>${row.kategori}</td>
                    <td>${row.vendor}</td>
                    <td>${row.jumlahModul}</td>
                </tr>
            `).join('');

            renderPagination(key, totalPages);
        }

        function renderPagination(key, totalPages) {
            const pager = document.getElementById(`pagination-${key}`);
            const state = tableState[key];

            if (totalPages <= 1) {
                pager.innerHTML = '';
                return;
            }

            let html = '';
            html += pageItem(key, state.page - 1, '&laquo;', state.page === 1);

            for (let p = 1; p <= totalPages; p++) {
                html += pageItem(key, p, p, false, p === state.page);
            }

            html += pageItem(key, state.page + 1, '&raquo;', state.page === totalPages);
            pager.innerHTML = html;
        }

        function pageItem(key, targetPage, label, disabled, active = false) {
            const cls = ['page-item'];
            if (disabled) cls.push('disabled');
            if (active) cls.push('active');
            return `
                <li class="${cls.join(' ')}">
                    <a class="page-link" href="#" onclick="goToPage('${key}', ${targetPage}); return false;">${label}</a>
                </li>
            `;
        }

        function goToPage(key, page) {
            tableState[key].page = page;
            renderTable(key);
        }

        function renderChart(key) {
            const canvas = document.getElementById(`chart-${key}`);
            const emptyEl = document.getElementById(`chart-${key}-empty`);
            const rows = tableState[key].rows; // semua data, tanpa slice

            if (chartInstances[key]) {
                chartInstances[key].destroy();
                chartInstances[key] = null;
            }

            if (rows.length === 0) {
                canvas.classList.add('d-none');
                emptyEl.classList.remove('d-none');
                return;
            }

            canvas.classList.remove('d-none');
            emptyEl.classList.add('d-none');

            const barColor = key === 'authorize' ? '#ffc107' : '#0d6efd';

            chartInstances[key] = new Chart(canvas.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: rows.map((r) => r.name),
                    datasets: [{
                        label: 'Jumlah Modul',
                        data: rows.map((r) => r.jumlahModul),
                        backgroundColor: barColor,
                        borderRadius: 4,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                afterLabel: (ctx) => {
                                    const row = rows[ctx.dataIndex];
                                    return `Kategori: ${row.kategori}\nVendor: ${row.vendor}`;
                                },
                            },
                        },
                    },
                    scales: {
                        x: {
                            ticks: {
                                autoSkip: false,
                                maxRotation: 60,
                                minRotation: 45,
                            },
                        },
                        y: {
                            beginAtZero: true,
                            ticks: { precision: 0 },
                        },
                    },
                },
            });
        }

        function loadRekap() {
            const params = new URLSearchParams();
            const tahun = document.getElementById('filter-tahun').value;
            const tipe = document.getElementById('filter-tipe').value;
            const sub = document.getElementById('filter-sub').value;

            if (tahun) params.append('tahun', tahun);
            if (tipe === 'bulan' && sub) params.append('bulan', sub);
            if (tipe === 'triwulan' && sub) params.append('triwulan', sub);

            fetch(`${URL_REKAP}?${params.toString()}`)
                .then((response) => response.json())
                .then((data) => {
                    document.getElementById('total-nomor').textContent = data.total_nomor ?? 0;
                    document.getElementById('total-modul').textContent = data.total_modul ?? 0;
                    document.getElementById('total-regular').textContent = data.total_regular ?? 0;
                    document.getElementById('total-authorize').textContent = data.total_authorize ?? 0;

                    tableState.regular = { rows: buildRows(data.data_regular), page: 1 };
                    tableState.authorize = { rows: buildRows(data.data_authorize), page: 1 };

                    renderTable('regular');
                    renderTable('authorize');
                    renderChart('regular');
                    renderChart('authorize');
                })
                .catch(() => {
                    document.getElementById('total-nomor').textContent = 0;
                    document.getElementById('total-modul').textContent = 0;
                    document.getElementById('total-regular').textContent = 0;
                    document.getElementById('total-authorize').textContent = 0;
                    tableState.regular = { rows: [], page: 1 };
                    tableState.authorize = { rows: [], page: 1 };
                    renderTable('regular');
                    renderTable('authorize');
                    renderChart('regular');
                    renderChart('authorize');
                });
        }

        loadRekap();
    </script>
@endsection