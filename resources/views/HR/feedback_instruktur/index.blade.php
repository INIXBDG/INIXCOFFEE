
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
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Rata-rata Instruktur</div>
                    <div class="fs-2 fw-bold" id="avg_instruktur">-</div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Rata-rata Instruktur Kedua</div>
                    <div class="fs-2 fw-bold" id="avg_instruktur2">-</div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Rata-rata Asisten</div>
                    <div class="fs-2 fw-bold" id="avg_asisten">-</div>
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
                        <tr>
                            <th class="text-center" width="60">No</th>
                            <th>Kode Karyawan</th>
                            <th>Nama Instruktur</th>
                            <th>Peran</th>
                            <th class="text-center">Jumlah Feedback</th>
                            <th class="text-center">Rata-rata</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="tbody_instruktur">
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                Pilih periode untuk menampilkan data.
                            </td>
                        </tr>
                    </tbody>
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

        function escapeHtml(value) {
            return $('<div>').text(value ?? '-').html();
        }

        function destroyDataTable() {
            if ($.fn.DataTable.isDataTable('#table_instruktur')) {
                $('#table_instruktur').DataTable().destroy();
            }
        }

        function initializeDataTable() {
            $('#table_instruktur').DataTable({
                pageLength: 10,
                lengthMenu: [[10, 25, 50, -1], [10, 25, 50, 'Semua']],
                order: [[2, 'asc']],
                columnDefs: [{
                    targets: [0, 6],
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

        function loadData() {
            const year = $('#filter_tahun').val();
            const type = $('#filter_type').val();
            const period = $('#filter_period').val();
            activeFilters = { year: year, type: type };

            if (type !== 'year') {
                activeFilters.period = period;
            }

            let url = baseUrl + '/' + encodeURIComponent(year) + '/' + type;

            if (type !== 'year') {
                url += '/' + encodeURIComponent(period);
            }

            destroyDataTable();
            $('#btn_filter').prop('disabled', true);
            $('#tbody_instruktur').html(`
                <tr>
                    <td colspan="7" class="text-center py-4">
                        <span class="spinner-border spinner-border-sm me-2"></span>
                        Memuat data...
                    </td>
                </tr>
            `);

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
                $('#avg_instruktur2').text(formatNilai(avg.instruktur2));
                $('#avg_asisten').text(formatNilai(avg.asisten));
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

                if (data.length === 0) {
                    $('#tbody_instruktur').html(`
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                Tidak ada data feedback instruktur pada periode ini.
                            </td>
                        </tr>
                    `);
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

                    // Di dalam looping data.forEach pada loadData()
                    const peran = {
                        instruktur: 'Instruktur',
                        instruktur2: 'Instruktur Kedua',
                        asisten: 'Asisten'
                    }[item.peran] || item.peran;

                    hasRows = true;
                    html += `
                        <tr>
                            <td class="text-center">${index + 1}</td>
                            <td>${escapeHtml(item.kode_karyawan)}</td>
                            <td>${escapeHtml(item.nama)}</td>
                            <td>${escapeHtml(peran)}</td>
                            <td class="text-center">${item.total_feedback}</td>
                            <td class="text-center fw-bold">${formatNilai(item.rata_rata)}</td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-info text-white btn-detail-rkm" 
                                    data-kode="${escapeHtml(item.kode_karyawan)}" 
                                    data-nama="${escapeHtml(item.nama)}"
                                    data-peran="${escapeHtml(item.peran)}"
                                    data-label-peran="${escapeHtml(peran)}">
                                    <i class="fas fa-eye me-1"></i>Detail RKM
                                </button>
                            </td>
                        </tr>
                    `;
                });

                if (!hasRows) {
                    html = `
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                Tidak ada data instruktur dengan penilaian valid.
                            </td>
                        </tr>
                    `;
                }

                $('#tbody_instruktur').html(html);

                if (hasRows) {
                    initializeDataTable();
                }
            })
            .fail(function (xhr) {
                const message = xhr.responseJSON?.message
                    || 'Gagal memuat data feedback instruktur.';

                $('#tbody_instruktur').html(`
                    <tr>
                        <td colspan="7" class="text-center text-danger py-4">
                            ${escapeHtml(message)}
                        </td>
                    </tr>
                `);

                $('#avg_instruktur, #avg_instruktur2, #avg_asisten').text('-');
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
            const peran = $(this).data('peran');             // 'instruktur', 'instruktur2', atau 'asisten'
            const labelPeran = $(this).data('label-peran');   // 'Instruktur', 'Instruktur Kedua', atau 'Asisten'
            
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
