@extends('layout_HR.app')
@section('content_HR')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"
        integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <style>
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        ::-webkit-scrollbar-track {
            background: transparent;
        }

        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        .page-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 0.25rem;
        }

        .page-subtitle {
            font-size: 0.9rem;
            color: var(--secondary);
        }

        .nav-tabs .nav-link {
            color: #64748b;
            font-weight: 500;
            border: none;
            border-bottom: 3px solid transparent;
            padding: 0.75rem 1.25rem;
        }

        .nav-tabs .nav-link.active {
            color: #0d6efd;
            background: transparent;
            border-bottom: 3px solid #0d6efd;
        }

        .nav-tabs .nav-link:hover:not(.active) {
            border-bottom: 3px solid #e2e8f0;
        }

        .table tfoot tr {
            background-color: #f8f9fa;
            font-weight: 700;
            color: #334155;
        }

        .filter-card {
            border: 1px solid #e2e8f0;
            border-radius: 0.75rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        .btn-danger,
        .text-danger,
        .bg-danger {
            background-color: #6c757d !important;
            border-color: #6c757d !important;
            color: #fff !important;
        }

        .loading-row td {
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: loading 1.5s infinite;
            height: 40px;
        }

        @keyframes loading {
            0% {
                background-position: 200% 0;
            }

            100% {
                background-position: -200% 0;
            }
        }
    </style>

    <div class="container-fluid px-4 py-4">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <div>
                <h1 class="page-title">Rekap SPJ</h1>
                <p class="page-subtitle mb-0">
                    Kelola dan pantau seluruh data pertanggungjawaban SPJ.
                    <span class="fw-semibold text-dark">{{ now()->translatedFormat('l, d F Y') }}</span>
                </p>
            </div>
            <div>
                <a href="#" id="btn_export_pdf" class="btn btn-danger me-2">
                    <i class="fa-solid fa-file-pdf me-1"></i> Export PDF
                </a>
                <a href="#" id="btn_export" class="btn btn-success me-2">
                    <i class="fa-solid fa-file-excel me-1"></i> Export Excel
                </a>
                <a href="{{ url('/suratperjalanan') }}" class="btn btn-primary"><i class="fa-solid fa-arrow-right me-1"></i>
                    Lihat Data SPJ</a>
            </div>
        </div>

        <div class="card filter-card mb-4">
            <div class="card-body p-4">
                <form id="form-filter-rekap" class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Divisi</label>
                        <select class="form-select" id="filter_divisi" name="divisi">
                            <option value="">-- Semua Divisi --</option>
                            @foreach ($divisis as $divisi)
                                <option value="{{ $divisi }}">{{ $divisi }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Jabatan</label>
                        <select class="form-select" id="filter_jabatan" name="jabatan" disabled>
                            <option value="">-- Pilih Divisi Dulu --</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Karyawan</label>
                        <select class="form-select" id="filter_karyawan" name="karyawan" disabled>
                            <option value="">-- Pilih Jabatan Dulu --</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Tahun</label>
                        <select class="form-select" id="filter_tahun" name="tahun">
                            @for ($y = date('Y'); $y >= 2023; $y--)
                                <option value="{{ $y }}" {{ $y == date('Y') ? 'selected' : '' }}>
                                    {{ $y }}</option>
                            @endfor
                        </select>
                    </div>

                    <div class="col-md-9">
                        <label class="form-label fw-semibold d-block">Filter Periode</label>
                        <div class="d-flex align-items-center gap-4 mt-2">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="mode_periode" id="mode_semua"
                                    value="semua" checked>
                                <label class="form-check-label" for="mode_semua">Seluruh Tahun</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="mode_periode" id="mode_bulan"
                                    value="bulan">
                                <label class="form-check-label" for="mode_bulan">Bulanan</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="mode_periode" id="mode_quartal"
                                    value="quartal">
                                <label class="form-check-label" for="mode_quartal">Per 3 Bulan (Quartal)</label>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 d-none" id="wrapper_bulan">
                        <label class="form-label fw-semibold">Pilih Bulan</label>
                        <select class="form-select" id="filter_bulan" name="bulan">
                            @for ($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}">
                                    {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}</option>
                            @endfor
                        </select>
                    </div>

                    <div class="col-md-4 d-none" id="wrapper_quartal">
                        <label class="form-label fw-semibold">Pilih Quartal</label>
                        <select class="form-select" id="filter_quartal" name="quartal">
                            <option value="1">Quartal 1 (Jan - Mar)</option>
                            <option value="2">Quartal 2 (Apr - Jun)</option>
                            <option value="3">Quartal 3 (Jul - Sep)</option>
                            <option value="4">Quartal 4 (Okt - Des)</option>
                        </select>
                    </div>

                    <div class="col-12 text-end mt-3">
                        <button type="button" class="btn btn-primary px-4" id="btn_terapkan_filter">
                            <i class="fa-solid fa-filter me-1"></i> Terapkan Filter
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-body p-0">
                <ul class="nav nav-tabs px-4 pt-3" id="rekapTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="tab-divisi-btn" data-bs-toggle="tab"
                            data-bs-target="#tab-divisi" type="button">
                            <i class="fa-solid fa-building me-1"></i> Rekap Per Divisi
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="tab-periode-btn" data-bs-toggle="tab" data-bs-target="#tab-periode"
                            type="button">
                            <i class="fa-solid fa-calendar-week me-1"></i> Rekap Per Periode
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="tab-jenis-btn" data-bs-toggle="tab" data-bs-target="#tab-jenis" type="button">
                            <i class="fa-solid fa-list-check me-1"></i> Rekap Per Jenis Dinas
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="tab-statistik-btn" data-bs-toggle="tab"
                            data-bs-target="#tab-statistik" type="button">
                            <i class="fa-solid fa-chart-column me-1"></i> Statistik Pengeluaran
                        </button>
                    </li>
                </ul>

                <div class="tab-content p-4" id="rekapTabContent">
                    <div class="tab-pane fade show active" id="tab-divisi" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th width="5%">No</th>
                                        <th>Nama Divisi</th>
                                        <th class="text-center" width="15%">Jumlah SPJ</th>
                                        <th class="text-end" width="20%">Total Pengeluaran</th>
                                        <th class="text-center" width="10%">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="tbody_divisi">
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">Memuat data...</td>
                                    </tr>
                                </tbody>
                                <tfoot id="tfoot_divisi" class="d-none">
                                    <tr>
                                        <td colspan="3" class="text-end">TOTAL KESELURUHAN:</td>
                                        <td class="text-end" id="total_divisi">Rp 0</td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="tab-periode" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th width="5%">No</th>
                                        <th>Periode</th>
                                        <th class="text-center" width="15%">Jumlah SPJ</th>
                                        <th class="text-end" width="20%">Total Pengeluaran</th>
                                        <th class="text-center" width="10%">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="tbody_periode">
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">Memuat data...</td>
                                    </tr>
                                </tbody>
                                <tfoot id="tfoot_periode" class="d-none">
                                    <tr>
                                        <td colspan="3" class="text-end">TOTAL KESELURUHAN:</td>
                                        <td class="text-end" id="total_periode">Rp 0</td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="tab-jenis" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th width="5%">No</th>
                                        <th>Jenis Dinas</th>
                                        <th class="text-center" width="15%">Jumlah SPJ</th>
                                        <th class="text-end" width="20%">Total Pengeluaran</th>
                                        <th class="text-center" width="10%">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="tbody_jenis">
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">Memuat data...</td>
                                    </tr>
                                </tbody>
                                <tfoot id="tfoot_jenis" class="d-none">
                                    <tr>
                                        <td colspan="3" class="text-end">TOTAL KESELURUHAN:</td>
                                        <td class="text-end" id="total_jenis">Rp 0</td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="tab-statistik" role="tabpanel">
                        <div class="row g-4">
                            <div class="col-lg-5">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-body">
                                        <h6 class="fw-bold text-secondary mb-4">Tren Pengeluaran per Divisi</h6>
                                        <div style="position: relative; height: 300px;">
                                            <canvas id="chartDivisi"></canvas>
                                        </div>
                                        <div id="emptyChart1" class="d-none py-5 text-muted text-center">Tidak ada data statistik.</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-7">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-body">
                                        <h6 class="fw-bold text-secondary mb-4">Perbandingan Total Pengeluaran per Jenis SPJ</h6>
                                        <div style="position: relative; height: 300px;">
                                            <canvas id="chartPerbandingan"></canvas>
                                        </div>
                                        <div id="emptyChart2" class="d-none py-5 text-muted text-center">Tidak ada data statistik.</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalDetail" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalDetailTitle">Detail SPJ</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="tableModalDetail">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>Tanggal</th>
                                    <th>Nama</th>
                                    <th>Divisi</th>
                                    <th>Jabatan</th>
                                    <th>Jenis</th>
                                    <th>Tujuan</th>
                                    <th>Alasan</th>
                                    <th class="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody id="tbody_modal_detail">
                                <tr>
                                    <td colspan="8" class="text-center py-4 text-muted">Memuat data...</td>
                                </tr>
                            </tbody>
                            <tfoot id="tfoot_modal_detail" class="d-none">
                                <tr>
                                    <td colspan="8" class="text-end fw-bold">TOTAL:</td>
                                    <td class="text-end fw-bold" id="total_modal_detail">Rp 0</td>
                                </tr>
                            </tfoot>
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
        $(document).ready(function() {
            let chartPersentase, chartPerbandingan;

            function formatRupiah(angka) {
                if (!angka) return 'Rp 0';
                let reverse = angka.toString().split('').reverse().join('');
                let rupiah = reverse.match(/\d{1,3}/g);
                rupiah = rupiah.join('.').split('').reverse().join('');
                return 'Rp ' + rupiah;
            }

            $('#filter_divisi').on('change', function() {
                let divisi = $(this).val();
                $('#filter_jabatan').html('<option value="">-- Memuat... --</option>').prop('disabled',
                    true);
                $('#filter_karyawan').html('<option value="">-- Pilih Jabatan Dulu --</option>').prop(
                    'disabled', true);

                if (divisi) {
                    $.get('{{ url('HR-dashboard/rekap-spj/ajax/jabatan') }}/' + encodeURIComponent(divisi),
                        function(data) {
                            let options = '<option value="">-- Semua Jabatan --</option>';
                            $.each(data, function(key, value) {
                                options += '<option value="' + value + '">' + value +
                                    '</option>';
                            });
                            $('#filter_jabatan').html(options).prop('disabled', false);
                        });
                } else {
                    $('#filter_jabatan').html('<option value="">-- Pilih Divisi Dulu --</option>').prop(
                        'disabled', true);
                }
            });

            $('#filter_jabatan').on('change', function() {
                let jabatan = $(this).val();
                $('#filter_karyawan').html('<option value="">-- Memuat... --</option>').prop('disabled',
                    true);

                if (jabatan) {
                    $.get('{{ url('HR-dashboard/rekap-spj/ajax/karyawan') }}/' + encodeURIComponent(
                            jabatan),
                        function(data) {
                            let options = '<option value="">-- Semua Karyawan --</option>';
                            $.each(data, function(key, value) {
                                options += '<option value="' + key + '">' + value + '</option>';
                            });
                            $('#filter_karyawan').html(options).prop('disabled', false);
                        });
                } else {
                    $('#filter_karyawan').html('<option value="">-- Pilih Jabatan Dulu --</option>').prop(
                        'disabled', true);
                }
            });

            $('input[name="mode_periode"]').on('change', function() {
                let mode = $(this).val();
                $('#wrapper_bulan, #wrapper_quartal').addClass('d-none');
                if (mode === 'bulan') $('#wrapper_bulan').removeClass('d-none');
                else if (mode === 'quartal') $('#wrapper_quartal').removeClass('d-none');
            });

            $('#btn_export_pdf').on('click', function() {
                let formData = $('#form-filter-rekap').serialize();
                let url = '{{ route('HR.rekap_spj.export_pdf') }}?' + formData;
                window.open(url);
            });

            $('#btn_export').on('click', function() {
                let formData = $('#form-filter-rekap').serialize();
                let url = '{{ route('HR.rekap_spj.export') }}?' + formData;
                window.open(url);
            });

            $('#btn_terapkan_filter').on('click', function() {
                loadDataRekap();
            });

            function loadDataRekap() {
                let formData = $('#form-filter-rekap').serialize();

                let loadingHtml = '<tr class="loading-row"><td colspan="5"></td></tr>';
                $('#tbody_divisi, #tbody_periode').html(loadingHtml);
                $('#tfoot_divisi, #tfoot_periode').addClass('d-none');

                $.get('{{ route('HR.rekap_spj.load_data') }}?' + formData, function(response) {
                    if (response.success) {
                        renderTab1(response.tab1);
                        renderTab2(response.tab2);
                        renderTabJenis(response.tabJenis);
                        renderTab3(response.tab3, response.chart, response.chart_divisi);
                    }
                }).fail(function() {
                    $('#tbody_divisi, #tbody_periode').html(
                        '<tr><td colspan="5" class="text-center py-4 text-danger">Gagal memuat data.</td></tr>'
                    );
                });
            }

            function renderTab1(data) {
                let html = '';
                let grandTotal = 0;
                if (data.length === 0) {
                    html =
                        '<tr><td colspan="5" class="text-center py-4 text-muted">Tidak ada data untuk filter yang dipilih.</td></tr>';
                } else {
                    $.each(data, function(index, item) {
                        grandTotal += parseFloat(item.total);

                        let params = new URLSearchParams($('#form-filter-rekap').serialize());
                        params.set('tipe', 'divisi');
                        params.set('nilai', item.divisi);
                        let urlDetail = '{{ route('HR.rekap_spj.detail_data') }}?' + params.toString();

                        html += `
                            <tr>
                                <td>${index + 1}</td>
                                <td>${item.divisi}</td>
                                <td class="text-center">${item.jumlah_spj} SPJ</td>
                                <td class="text-end">${formatRupiah(item.total)}</td>
                                <td class="text-center">
                                    <a href="#" class="btn btn-sm btn-info text-white btn-detail" data-url="${urlDetail}" data-title="Detail SPJ Divisi ${item.divisi}">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        `;
                    });
                }
                $('#tbody_divisi').html(html);
                $('#total_divisi').text(formatRupiah(grandTotal));
                $('#tfoot_divisi').removeClass('d-none');
            }

            function renderTabJenis(data) {
                let html = '';
                let grandTotal = 0;
                if (data.length === 0) {
                    html = '<tr><td colspan="5" class="text-center py-4 text-muted">Tidak ada data untuk filter yang dipilih.</td></tr>';
                } else {
                    $.each(data, function(index, item) {
                        grandTotal += parseFloat(item.total);

                        let params = new URLSearchParams($('#form-filter-rekap').serialize());
                        params.set('tipe', 'jenis_dinas');
                        params.set('nilai', item.jenis_dinas);
                        let urlDetail = '{{ route('HR.rekap_spj.detail_data') }}?' + params.toString();

                        html += `
                            <tr>
                                <td>${index + 1}</td>
                                <td>${item.jenis_dinas || 'Tidak Ada Jenis'}</td>
                                <td class="text-center">${item.jumlah_spj} SPJ</td>
                                <td class="text-end">${formatRupiah(item.total)}</td>
                                <td class="text-center">
                                    <a href="#" class="btn btn-sm btn-info text-white btn-detail" 
                                    data-url="${urlDetail}" 
                                    data-title="Detail SPJ Jenis ${item.jenis_dinas || 'Tidak Ada'}">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        `;
                    });
                }
                $('#tbody_jenis').html(html);
                $('#total_jenis').text(formatRupiah(grandTotal));
                $('#tfoot_jenis').removeClass('d-none');
            }

            function renderTab2(data) {
                let html = '';
                let grandTotal = 0;
                if (data.length === 0) {
                    html =
                        '<tr><td colspan="5" class="text-center py-4 text-muted">Tidak ada data untuk filter yang dipilih.</td></tr>';
                } else {
                    $.each(data, function(index, item) {
                        grandTotal += parseFloat(item.total);

                        let params = new URLSearchParams($('#form-filter-rekap').serialize());
                        params.set('tipe', 'periode');
                        params.set('filter_mode', item.filter_mode);
                        params.set('filter_value', item.filter_value);
                        let urlDetail = '{{ route('HR.rekap_spj.detail_data') }}?' + params.toString();

                        html += `
                            <tr>
                                <td>${index + 1}</td>
                                <td>${item.periode}</td>
                                <td class="text-center">${item.jumlah_spj} SPJ</td>
                                <td class="text-end">${formatRupiah(item.total)}</td>
                                <td class="text-center">
                                    <a href="#" class="btn btn-sm btn-info text-white btn-detail" data-url="${urlDetail}" data-title="Detail SPJ Periode ${item.periode}">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        `;
                    });
                }
                $('#tbody_periode').html(html);
                $('#total_periode').text(formatRupiah(grandTotal));
                $('#tfoot_periode').removeClass('d-none');
            }

            function renderTab3(data, chartData, chartDivisi) {
                if (chartPersentase) chartPersentase.destroy();
                if (chartPerbandingan) chartPerbandingan.destroy();

                // === CHART BAR PER DIVISI ===
                if (!chartDivisi || chartDivisi.labels.length === 0) {
                    $('#chartDivisi').hide();
                    $('#emptyChart1').removeClass('d-none');
                } else {
                    $('#chartDivisi').show();
                    $('#emptyChart1').addClass('d-none');

                    const ctx1 = document.getElementById('chartDivisi').getContext('2d');
                    chartPersentase = new Chart(ctx1, {
                        type: 'bar',
                        data: {
                            labels: chartDivisi.labels,
                            datasets: [
                                {
                                    label: 'Periode Saat Ini',
                                    data: chartDivisi.current,
                                    backgroundColor: '#0d6efd',
                                    borderRadius: 6,
                                    borderSkipped: false
                                },
                                {
                                    label: 'Periode Lalu',
                                    data: chartDivisi.previous,
                                    backgroundColor: '#cbd5e1',
                                    borderRadius: 6,
                                    borderSkipped: false
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    grid: { color: '#f1f5f9' },
                                    ticks: {
                                        callback: function(value) {
                                            if (value >= 1000000) return 'Rp ' + (value / 1000000).toFixed(1) + ' Jt';
                                            if (value >= 1000) return 'Rp ' + (value / 1000).toFixed(0) + ' Rb';
                                            return 'Rp ' + value;
                                        }
                                    }
                                },
                                x: {
                                    grid: { display: false },
                                    ticks: {
                                        maxRotation: 45,
                                        minRotation: 45
                                    }
                                }
                            },
                            plugins: {
                                legend: {
                                    position: 'top',
                                    align: 'end'
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            let val = context.raw.toLocaleString('id-ID');
                                            return context.dataset.label + ': Rp ' + val;
                                        }
                                    }
                                }
                            }
                        }
                    });
                }

                // === CHART BAR PERBANDINGAN JENIS SPJ ===
                if (!chartData || chartData.labels.length === 0) {
                    $('#chartPerbandingan').hide();
                    $('#emptyChart2').removeClass('d-none');
                } else {
                    $('#chartPerbandingan').show();
                    $('#emptyChart2').addClass('d-none');

                    const ctx2 = document.getElementById('chartPerbandingan').getContext('2d');
                    chartPerbandingan = new Chart(ctx2, {
                        type: 'bar',
                        data: {
                            labels: chartData.labels,
                            datasets: [
                                {
                                    label: 'Periode Saat Ini',
                                    data: chartData.current,
                                    backgroundColor: '#0d6efd',
                                    borderRadius: 6
                                },
                                {
                                    label: 'Periode Lalu',
                                    data: chartData.previous,
                                    backgroundColor: '#cbd5e1',
                                    borderRadius: 6
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    grid: { color: '#f1f5f9' },
                                    ticks: {
                                        callback: function(value) {
                                            if (value >= 1000000) return 'Rp ' + (value / 1000000).toFixed(1) + ' Jt';
                                            if (value >= 1000) return 'Rp ' + (value / 1000).toFixed(0) + ' Rb';
                                            return 'Rp ' + value;
                                        }
                                    }
                                },
                                x: { grid: { display: false } }
                            },
                            plugins: {
                                legend: { position: 'top', align: 'end' },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            let val = context.raw.toLocaleString('id-ID');
                                            return context.dataset.label + ': Rp ' + val;
                                        }
                                    }
                                }
                            }
                        }
                    });
                }
            }

            loadDataRekap();
        });

    $(document).on('click', '.btn-detail', function(e) {
        e.preventDefault();
        let url = $(this).data('url');
        let title = $(this).data('title');

        function formatRupiah(angka) {
            if (!angka) return 'Rp 0';
            let reverse = angka.toString().split('').reverse().join('');
            let rupiah = reverse.match(/\d{1,3}/g);
            rupiah = rupiah.join('.').split('').reverse().join('');
            return 'Rp ' + rupiah;
        }

        $('#modalDetailTitle').text(title);
        $('#tfoot_modal_detail').addClass('d-none');

        if ($.fn.DataTable.isDataTable('#tableModalDetail')) {
            $('#tableModalDetail').DataTable().destroy();
        }

        let modal = new bootstrap.Modal(document.getElementById('modalDetail'));
        modal.show();

        $.get(url, function(response) {
            if(response.success) {
                let grandTotal = 0;
                let tableData = [];
                
                if(response.data.length === 0) {
                    $('#tbody_modal_detail').html('<tr><td colspan="8" class="text-center py-4 text-muted">Tidak ada data.</td></tr>');
                } else {
                    $.each(response.data, function(index, item) {
                        grandTotal += parseFloat(item.total);
                        tableData.push([
                            index + 1,
                            item.tanggal,
                            item.nama,
                            item.divisi,
                            item.jabatan,
                            item.tipe,
                            item.tujuan,
                            item.alasan,
                            formatRupiah(item.total)
                        ]);
                    });

                    $('#tbody_modal_detail').empty();
                    $('#tfoot_modal_detail').removeClass('d-none');
                    $('#total_modal_detail').text(formatRupiah(grandTotal));

                    $('#tableModalDetail').DataTable({
                        data: tableData,
                        destroy: true,
                        responsive: true,
                        language: {
                            search: "Cari:",
                            lengthMenu: "Tampilkan _MENU_ data",
                            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                            infoEmpty: "Tidak ada data",
                            infoFiltered: "(difilter dari _MAX_ total data)",
                            paginate: {
                                first: "Pertama",
                                last: "Terakhir",
                                next: "<i class='fa-solid fa-angle-right'></i>",
                                previous: "<i class='fa-solid fa-angle-left'></i>"
                            }
                        },
                        columnDefs: [
                            { targets: 7, className: 'text-end' },
                            { targets: [0, 4], className: 'text-center' }
                        ],
                        order: [[0, 'asc']],
                        pageLength: 10,
                        lengthMenu: [10, 25, 50, 100]
                    });
                }
            }
        }).fail(function() {
            $('#tbody_modal_detail').html('<tr><td colspan="8" class="text-center py-4 text-danger">Gagal memuat data.</td></tr>');
        });
    });
    </script>
@endsection
