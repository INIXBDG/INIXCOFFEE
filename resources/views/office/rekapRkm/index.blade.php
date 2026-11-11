@extends('layouts_office.app')

@section('office_contents')
<div class="container-fluid">

    {{-- CARD FILTER --}}
    <div class="card mb-3">
        <div class="card-header">
            <strong>Filter Rekap RKM</strong>
        </div>

        <div class="card-body">
            <form id="filterForm" class="row g-3 align-items-end">

                <div class="col-md-3">
                    <label class="form-label">Tipe Filter</label>
                    <select name="filter_type" id="filter_type" class="form-select">
                        <option value="bulan">Per Bulan</option>
                        <option value="triwulan">Per Triwulan</option>
                        <option value="tahun">Sepanjang Tahun</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Tahun</label>
                    <select name="tahun" id="tahun" class="form-select"></select>
                </div>

                <div class="col-md-3" id="wrapper_bulan">
                    <label class="form-label">Bulan</label>
                    <select name="bulan" id="bulan" class="form-select">
                        @foreach([
                            '1'=>'Januari',
                            '2'=>'Februari',
                            '3'=>'Maret',
                            '4'=>'April',
                            '5'=>'Mei',
                            '6'=>'Juni',
                            '7'=>'Juli',
                            '8'=>'Agustus',
                            '9'=>'September',
                            '10'=>'Oktober',
                            '11'=>'November',
                            '12'=>'Desember'
                        ] as $val => $label)
                            <option value="{{ $val }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3" id="wrapper_triwulan" style="display:none;">
                    <label class="form-label">Triwulan</label>
                    <select name="triwulan" id="triwulan" class="form-select">
                        <option value="1">Triwulan 1 (Jan-Mar)</option>
                        <option value="2">Triwulan 2 (Apr-Jun)</option>
                        <option value="3">Triwulan 3 (Jul-Sep)</option>
                        <option value="4">Triwulan 4 (Okt-Des)</option>
                    </select>
                </div>

                <div class="col-md-auto">
                    <button type="button" id="btnFilter" class="btn btn-primary">
                        Terapkan
                    </button>
                </div>

                @can('Update RekapRKM Office')
                    <div class="col-md-auto">
                        <a href="{{ route('office.rekapRkm.select') }}" class="btn btn-success">
                            Update Data
                        </a>
                    </div>
                @endcan

            </form>
        </div>
    </div>

    {{-- TABS --}}
    <ul class="nav nav-tabs" id="rekapTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="chart-tab" data-bs-toggle="tab" data-bs-target="#chart-pane" type="button" role="tab">
                Chart
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="data-tab" data-bs-toggle="tab" data-bs-target="#data-pane" type="button" role="tab">
                Data
            </button>
        </li>
    </ul>

    <div class="tab-content border border-top-0 p-3" id="rekapTabContent">
        {{-- TAB CHART --}}
        <div class="tab-pane fade show active" id="chart-pane" role="tabpanel">
            <div class="card mb-3">
                <div class="card-body">
                    <canvas id="rkmChart" height="100"></canvas>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-body">
                    <canvas id="materiChart" height="120"></canvas>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <canvas id="mingguChart" height="100"></canvas>
                </div>
            </div>
        </div>

        {{-- TAB DATA --}}
        <div class="tab-pane fade" id="data-pane" role="tabpanel">
            <div class="card">
                <div class="card-header">
                    <div class="row g-2">
                        <div class="col-md-3">
                            <label class="form-label mb-1">Cari</label>
                            <input type="text" id="searchInput" class="form-control form-control-sm" placeholder="Materi / Perusahaan...">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label mb-1">Status Hide</label>
                            <select id="filterHide" class="form-select form-select-sm">
                                <option value="all">Semua</option>
                                <option value="hide">Hide</option>
                                <option value="show">Show</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label mb-1">Status Hide Materi</label>
                            <select id="filterHideMateri" class="form-select form-select-sm">
                                <option value="all">Semua</option>
                                <option value="hide">Hide</option>
                                <option value="show">Show</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label mb-1">Status Hide Perusahaan</label>
                            <select id="filterHidePerusahaan" class="form-select form-select-sm">
                                <option value="all">Semua</option>
                                <option value="hide">Hide</option>
                                <option value="show">Show</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-bordered table-striped table-sm" id="rkmTable">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Periode Mulai</th>
                                <th>Periode Selesai</th>
                                <th>Nama Materi</th>
                                <th>Nama Perusahaan</th>
                                <th>Hide</th>
                                <th>Hide Materi</th>
                                <th>Hide Perusahaan</th>
                            </tr>
                        </thead>
                        <tbody id="rkmTableBody">
                            {{-- diisi via JS --}}
                        </tbody>
                    </table>

                    <div class="d-flex justify-content-between align-items-center mt-2">
                        <small id="paginationInfo" class="text-muted"></small>
                        <nav>
                            <ul class="pagination pagination-sm mb-0" id="paginationControls">
                                {{-- diisi via JS --}}
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const apiUrl = "{{ route('office.rekapRkmJson') }}";
        const now = new Date();
        const currentYear = now.getFullYear();
        const currentMonth = now.getMonth() + 1;

        const PER_PAGE = 20;
        let currentData = [];   // data mentah dari server (semua row hasil filter periode)
        let filteredData = [];  // hasil filter status, dipakai buat tabel
        let currentPage = 1;
        let chartInstance = null;
        let materiChartInstance = null;
        let mingguChartInstance = null;

        // Isi dropdown tahun
        const tahunSelect = document.getElementById('tahun');
        for (let y = currentYear - 5; y <= currentYear + 1; y++) {
            const opt = document.createElement('option');
            opt.value = y;
            opt.text = y;
            if (y === currentYear) opt.selected = true;
            tahunSelect.appendChild(opt);
        }

        // Default bulan berjalan
        document.getElementById('bulan').value = currentMonth;

        const filterType = document.getElementById('filter_type');
        const wrapperBulan = document.getElementById('wrapper_bulan');
        const wrapperTriwulan = document.getElementById('wrapper_triwulan');

        filterType.addEventListener('change', function () {
            if (this.value === 'bulan') {
                wrapperBulan.style.display = '';
                wrapperTriwulan.style.display = 'none';
            } else if (this.value === 'triwulan') {
                wrapperBulan.style.display = 'none';
                wrapperTriwulan.style.display = '';
            } else {
                wrapperBulan.style.display = 'none';
                wrapperTriwulan.style.display = 'none';
            }
        });

        // Filter status untuk tabel (Hide, Hide Materi, Hide Perusahaan) + Search
        const filterHide = document.getElementById('filterHide');
        const filterHideMateri = document.getElementById('filterHideMateri');
        const filterHidePerusahaan = document.getElementById('filterHidePerusahaan');
        const searchInput = document.getElementById('searchInput');

        function matchStatus(value, filterValue) {
            if (filterValue === 'all') return true;
            const isHide = Number(value) === 1;
            return filterValue === 'hide' ? isHide : !isHide;
        }

        function matchSearch(item, keyword) {
            if (!keyword) return true;
            const target = keyword.toLowerCase();
            const materi = (item.nama_materi ?? '').toLowerCase();
            const perusahaan = (item.nama_perusahaan ?? '').toLowerCase();
            return materi.includes(target) || perusahaan.includes(target);
        }

        function applyTableFilter() {
            const keyword = searchInput.value.trim();

            filteredData = currentData.filter(item =>
                matchStatus(item.hide, filterHide.value) &&
                matchStatus(item.hide_materi, filterHideMateri.value) &&
                matchStatus(item.hide_perusahaan, filterHidePerusahaan.value) &&
                matchSearch(item, keyword)
            );
            currentPage = 1;
            renderTable();
        }

        [filterHide, filterHideMateri, filterHidePerusahaan].forEach(el => {
            el.addEventListener('change', applyTableFilter);
        });

        let searchDebounce;
        searchInput.addEventListener('input', function () {
            clearTimeout(searchDebounce);
            searchDebounce = setTimeout(applyTableFilter, 250);
        });

        function loadData() {
            const params = new URLSearchParams({
                filter_type: filterType.value,
                tahun: tahunSelect.value,
            });

            if (filterType.value === 'bulan') {
                params.append('bulan', document.getElementById('bulan').value);
            } else if (filterType.value === 'triwulan') {
                params.append('triwulan', document.getElementById('triwulan').value);
            }

            fetch(apiUrl + '?' + params.toString())
                .then(res => res.json())
                .then(data => {
                    currentData = data.peluang ?? [];
                    applyTableFilter();
                    renderChart(currentData);
                    renderMateriChart(data.materi_terbanyak ?? []);
                    renderMingguChart(data.rkm_per_minggu ?? []);
                })
                .catch(err => {
                    console.error('Gagal mengambil data rekap RKM:', err);
                });
        }

        function renderTable() {
            const tbody = document.getElementById('rkmTableBody');
            tbody.innerHTML = '';

            if (!filteredData.length) {
                tbody.innerHTML = '<tr><td colspan="8" class="text-center">Tidak ada data</td></tr>';
                document.getElementById('paginationInfo').textContent = '';
                document.getElementById('paginationControls').innerHTML = '';
                return;
            }

            const totalPages = Math.ceil(filteredData.length / PER_PAGE);
            if (currentPage > totalPages) currentPage = totalPages;

            const start = (currentPage - 1) * PER_PAGE;
            const end = start + PER_PAGE;
            const pageData = filteredData.slice(start, end);

            function badge(value) {
                return Number(value) === 1
                    ? '<span class="badge bg-secondary">Hide</span>'
                    : '<span class="badge bg-success">Show</span>';
            }

            pageData.forEach((item, index) => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${start + index + 1}</td>
                    <td>${item.periode_mulai ?? '-'}</td>
                    <td>${item.periode_selesai ?? '-'}</td>
                    <td>${item.nama_materi ?? '-'}</td>
                    <td>${item.nama_perusahaan ?? '-'}</td>
                    <td>${badge(item.hide)}</td>
                    <td>${badge(item.hide_materi)}</td>
                    <td>${badge(item.hide_perusahaan)}</td>
                `;
                tbody.appendChild(tr);
            });

            document.getElementById('paginationInfo').textContent =
                `Menampilkan ${start + 1}-${Math.min(end, filteredData.length)} dari ${filteredData.length} data`;

            renderPagination(totalPages);
        }

        function renderPagination(totalPages) {
            const controls = document.getElementById('paginationControls');
            controls.innerHTML = '';

            if (totalPages <= 1) return;

            function pageItem(label, page, disabled = false, active = false) {
                const li = document.createElement('li');
                li.className = `page-item ${disabled ? 'disabled' : ''} ${active ? 'active' : ''}`;
                const a = document.createElement('a');
                a.className = 'page-link';
                a.href = '#';
                a.textContent = label;
                a.addEventListener('click', function (e) {
                    e.preventDefault();
                    if (!disabled && page !== currentPage) {
                        currentPage = page;
                        renderTable();
                    }
                });
                li.appendChild(a);
                return li;
            }

            controls.appendChild(pageItem('«', currentPage - 1, currentPage === 1));

            let startPage = Math.max(1, currentPage - 2);
            let endPage = Math.min(totalPages, startPage + 4);
            if (endPage - startPage < 4) startPage = Math.max(1, endPage - 4);

            for (let p = startPage; p <= endPage; p++) {
                controls.appendChild(pageItem(p, p, false, p === currentPage));
            }

            controls.appendChild(pageItem('»', currentPage + 1, currentPage === totalPages));
        }

        function renderChart(data) {
            const hideCount = data.filter(item => Number(item.hide) === 1).length;
            const showCount = data.filter(item => Number(item.hide) !== 1).length;

            const ctx = document.getElementById('rkmChart').getContext('2d');

            if (chartInstance) chartInstance.destroy();

            chartInstance = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Hide', 'Show'],
                    datasets: [{
                        label: 'Jumlah RKM',
                        data: [hideCount, showCount],
                        backgroundColor: ['#6c757d', '#198754'],
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { display: false },
                        title: { display: true, text: 'Perbandingan Data Hide vs Show' }
                    },
                    scales: {
                        y: { beginAtZero: true, ticks: { stepSize: 1 } }
                    }
                }
            });
        }

        function renderMateriChart(materiData) {
            const topMateri = materiData.slice(0, 10);

            const labels = topMateri.map(m => m.nama_materi ?? '-');
            const values = topMateri.map(m => m.jumlah ?? 0);

            const ctx = document.getElementById('materiChart').getContext('2d');

            if (materiChartInstance) materiChartInstance.destroy();

            if (!topMateri.length) {
                materiChartInstance = new Chart(ctx, {
                    type: 'bar',
                    data: { labels: [], datasets: [] },
                    options: {
                        plugins: {
                            title: { display: true, text: 'Materi Terbanyak (Tidak ada data)' }
                        }
                    }
                });
                return;
            }

            materiChartInstance = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Jumlah',
                        data: values,
                        backgroundColor: '#0d6efd',
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    plugins: {
                        legend: { display: false },
                        title: { display: true, text: 'Top 10 Materi Terbanyak' }
                    },
                    scales: {
                        x: { beginAtZero: true, ticks: { stepSize: 1 } }
                    }
                }
            });
        }

        function renderMingguChart(mingguData) {
            const labels = mingguData.map(m => m.label);
            const values = mingguData.map(m => m.jumlah);

            const ctx = document.getElementById('mingguChart').getContext('2d');

            if (mingguChartInstance) mingguChartInstance.destroy();

            if (!mingguData.length) {
                mingguChartInstance = new Chart(ctx, {
                    type: 'line',
                    data: { labels: [], datasets: [] },
                    options: {
                        plugins: {
                            title: { display: true, text: 'RKM per Minggu (Tidak ada data)' }
                        }
                    }
                });
                return;
            }

            const maxValue = Math.max(...values);
            const pointColors = values.map(v => v === maxValue ? '#dc3545' : '#0d6efd');

            mingguChartInstance = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Jumlah RKM Berjalan',
                        data: values,
                        borderColor: '#0d6efd',
                        backgroundColor: 'rgba(13,110,253,0.1)',
                        pointBackgroundColor: pointColors,
                        pointRadius: 5,
                        tension: 0.2,
                        fill: true,
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { display: false },
                        title: { display: true, text: 'RKM Berjalan per Minggu (titik merah = minggu terbanyak)' }
                    },
                    scales: {
                        y: { beginAtZero: true, ticks: { stepSize: 1 } },
                        x: { ticks: { maxRotation: 45, minRotation: 45 } }
                    }
                }
            });
        }

        document.getElementById('btnFilter').addEventListener('click', loadData);

        filterType.value = 'bulan';
        loadData();
    });
</script>
@endsection