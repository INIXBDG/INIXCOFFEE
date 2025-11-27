@extends('layouts_crm.app')

@section('crm_contents')
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- FILTER CARD -->
        <div class="card mb-5 shadow-sm">
            <div class="card-body">
                <h5 class="card-title text-primary mb-4">Filter Laporan</h5>
                <form id="filterForm" class="needs-validation" novalidate>
                    <div class="row g-4 mb-3">
                        <div class="col-md-6 col-lg-4">
                            <label for="sales_key" class="form-label fw-semibold">Sales Key</label>
                            <select id="sales_key" class="form-select select2-sales" name="sales_key">
                                <option value="" selected>Pilih Sales Key</option>
                                @foreach ($sales as $salesItem)
                                <option value="{{ $salesItem->kode_karyawan }}">{{ $salesItem->kode_karyawan }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 col-lg-4">
                            <label for="materi_key" class="form-label fw-semibold">Materi</label>
                            <select id="materi_key" class="form-select select2-materi" name="materi_key">
                                <option value="" selected>Pilih Materi</option>
                                @foreach ($materi as $materiItem)
                                <option value="{{ $materiItem->id }}">{{ $materiItem->nama_materi }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 col-lg-4">
                            <label for="tahun" class="form-label fw-semibold">
                                Tahun <span class="text-danger">*</span>
                                <small class="text-muted">(Pilih dulu untuk filter periode)</small>
                            </label>
                            <select name="tahun" id="tahun" class="form-select">
                                <option value="" selected>Pilih Tahun</option>
                                @foreach (range(date('Y'), date('Y') - 5) as $year)
                                <option value="{{ $year }}">{{ $year }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row g-4">
                        <div class="col-md-6 col-lg-4">
                            <label for="triwulan" class="form-label fw-semibold">Triwulan</label>
                            <select name="triwulan" id="triwulan" class="form-select" disabled>
                                <option value="" selected>Pilih Tahun Dulu</option>
                                <option value="1">Triwulan 1 (Jan - Mar)</option>
                                <option value="2">Triwulan 2 (Apr - Jun)</option>
                                <option value="3">Triwulan 3 (Jul - Sep)</option>
                                <option value="4">Triwulan 4 (Okt - Des)</option>
                            </select>
                        </div>

                        <div class="col-md-6 col-lg-4">
                            <label for="bulan" class="form-label fw-semibold">Bulan</label>
                            <select name="bulan" id="bulan" class="form-select" disabled>
                                <option value="" selected>Pilih Tahun Dulu</option>
                                <option value="1">Januari</option>
                                <option value="2">Februari</option>
                                <option value="3">Maret</option>
                                <option value="4">April</option>
                                <option value="5">Mei</option>
                                <option value="6">Juni</option>
                                <option value="7">Juli</option>
                                <option value="8">Agustus</option>
                                <option value="9">September</option>
                                <option value="10">Oktober</option>
                                <option value="11">November</option>
                                <option value="12">Desember</option>
                            </select>
                        </div>

                        <div class="col-md-6 col-lg-4">
                            <label for="minggu" class="form-label fw-semibold">Minggu</label>
                            <select name="minggu" id="minggu" class="form-select" disabled>
                                <option value="" selected>Pilih Bulan & Tahun Dulu</option>
                            </select>
                        </div>
                    </div>

                    <hr class="my-4">
                    <small class="text-muted">Atau pilih rentang tanggal manual:</small>

                    <div class="row g-4 mt-1">
                        <div class="col-md-6 col-lg-3">
                            <label for="tanggal_awal_mulai" class="form-label fw-semibold">Tanggal Awal Mulai</label>
                            <input type="date" id="tanggal_awal_mulai" name="tanggal_awal_mulai" class="form-control">
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <label for="tanggal_awal_akhir" class="form-label fw-semibold">Tanggal Awal Akhir</label>
                            <input type="date" id="tanggal_awal_akhir" name="tanggal_awal_akhir" class="form-control">
                        </div>
                    </div>

                    <div class="mt-4 d-flex justify-content-end gap-2">
                        <button type="button" id="resetBtn" class="btn btn-outline-secondary">Reset</button>
                        <button type="button" id="filterBtn" class="btn btn-primary" disabled>
                            <span id="filterText">Terapkan Filter</span>
                            <span id="loadingText" class="d-none">Memuat...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- TABEL WIN -->
        <div class="card mb-4 shadow-sm">
            <div class="card-body">
                <h5 class="card-title text-success mb-4">
                    Laporan Win
                    <div class="float-end">
                        <a href="{{ route('laporan.win.pdf', request()->all()) }}" class="btn btn-danger btn-sm">
                            <i class="bx bxs-file-pdf me-1"></i> PDF
                        </a>
                        <a href="{{ route('laporan.win.excel', request()->all()) }}" class="btn btn-success btn-sm">
                            <i class="bx bx-download me-1"></i> Excel
                        </a>
                    </div>
                </h5>
                <div class="table-responsive">
                    <table id="tableStatus0" class="table table-bordered table-hover">
                        <thead class="table-success">
                            <tr>
                                <th class="text-center">ID</th>
                                <th class="text-center">Sales</th>
                                <th class="text-center">Materi</th>
                                <th class="text-center">Perusahaan</th>
                                <th class="text-center">Pax</th>
                                <th class="text-center">Harga</th>
                                <th class="text-center">Exam</th>
                                <th class="text-center">Total PA</th>
                                <th class="text-center">Netsales</th>
                                <th class="text-center">Tanggal Awal</th>
                                <th class="text-center">Tanggal Akhir</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>

                <!-- SUMMARY WIN -->
                <div id="summaryWin" class="mt-4 p-3 rounded d-none">
                    <h6 class="fw-bold text-success">Ringkasan Win</h6>
                    <div class="row text-center">
                        <div class="col-md-3">
                            <small class="text-muted">Total Harga Jual</small>
                            <p class="fw-bold text-primary mb-0" id="winHargaJual">Rp 0</p>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted">Total P</small>
                            <p class="fw-bold text-warning mb-0" id="winNetSales">Rp 0</p>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted">Total Exam</small>
                            <p class="fw-bold text-info mb-0" id="winExam">Rp 0</p>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted">Grand Total</small>
                            <p class="fw-bold text-success mb-0" id="winGrandTotal">Rp 0</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- TABEL LOST -->
        <div class="card shadow-sm">
            <div class="card-body">
                <h5 class="card-title text-danger mb-4">
                    Laporan Lost
                    <div class="float-end">
                        <a href="{{ route('laporan.lost.pdf', request()->all()) }}" class="btn btn-danger btn-sm">
                            <i class="bx bxs-file-pdf me-1"></i> PDF
                        </a>
                        <a href="{{ route('laporan.lost.excel', request()->all()) }}" class="btn btn-success btn-sm">
                            <i class="bx bx-download me-1"></i> Excel
                        </a>
                    </div>
                </h5>
                <div class="table-responsive">
                    <table id="tableStatus2" class="table table-bordered table-hover">
                        <thead class="table-danger">
                            <tr>
                                <th class="text-center">ID</th>
                                <th class="text-center">Sales</th>
                                <th class="text-center">Materi</th>
                                <th class="text-center">Perusahaan</th>
                                <th class="text-center">Pax</th>
                                <th class="text-center">Harga</th>
                                <th class="text-center">Exam</th>
                                <th class="text-center">Total PA</th>
                                <th class="text-center">NetSales</th>
                                <th class="text-center">Tanggal Awal</th>
                                <th class="text-center">Tanggal Akhir</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>

                <!-- SUMMARY LOST -->
                <div id="summaryLost" class="mt-4 p-3 rounded d-none">
                    <h6 class="fw-bold text-danger">Ringkasan Lost</h6>
                    <div class="row text-center">
                        <div class="col-md-3">
                            <small class="text-muted">Total Harga Jual</small>
                            <p class="fw-bold text-primary mb-0" id="lostHargaJual">Rp 0</p>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted">Total P</small>
                            <p class="fw-bold text-warning mb-0" id="lostNetSales">Rp 0</p>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted">Total Exam</small>
                            <p class="fw-bold text-info mb-0" id="lostExam">Rp 0</p>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted">Grand Total</small>
                            <p class="fw-bold text-danger mb-0" id="lostGrandTotal">Rp 0</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL DETAIL -->
<div class="modal fade" id="detailModal" tabindex="-1" aria-labelledby="detailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="detailModalLabel">Detail Laporan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="detailContent">
                    <p class="text-muted">Memuat detail...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/locale/id.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<link href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css" rel="stylesheet">
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    $(document).ready(function() {
        moment.locale('id');


        // Format Rupiah
        function formatRupiah(angka) {
            return 'Rp ' + (parseFloat(angka) || 0).toLocaleString('id-ID');
        }

        // Select2
        $('.select2-materi, .select2-sales').each(function() {
            $(this).select2({
                placeholder: $(this).attr('id') === 'materi_key' ? "Cari Materi..." : "Cari Sales Key...",
                allowClear: true,
                theme: "bootstrap-5",
                width: '100%'
            });
        });

        // Toggle Triwulan & Bulan
        function toggleTriwulanBulan() {
            const tahun = $('#tahun').val();
            if (!tahun) {
                $('#triwulan, #bulan').prop('disabled', true).val('');
                $('#triwulan option:first').text('Pilih Tahun Dulu');
                $('#bulan option:first').text('Pilih Tahun Dulu');
                $('#minggu').empty().append('<option value="" selected>Pilih Bulan & Tahun Dulu</option>').prop('disabled', true);
            } else {
                $('#triwulan, #bulan').prop('disabled', false);
                $('#triwulan option:first').text('Pilih Triwulan');
                $('#bulan option:first').text('Pilih Bulan');
            }
        }

        // Populate Minggu
        function populateWeeksDropdown() {
            const month = $('#bulan').val();
            const year = $('#tahun').val();
            const $minggu = $('#minggu');

            if (!month || !year) {
                $minggu.empty().append('<option value="" selected>Pilih Bulan & Tahun Dulu</option>').prop('disabled', true);
                return;
            }

            $minggu.empty().prop('disabled', false).append('<option value="" selected>Pilih Minggu</option>');
            const start = moment([year, month - 1, 1]);
            const end = start.clone().endOf('month');
            let week = 1;
            let date = start.clone();

            while (date.isSameOrBefore(end)) {
                let weekStart = date.clone().startOf('isoWeek');
                let weekEnd = date.clone().endOf('isoWeek');
                let from = weekStart.isBefore(start) ? start.clone() : weekStart;
                let to = weekEnd.isAfter(end) ? end.clone() : weekEnd;
                const label = `Minggu ${week} (${from.format('DD MMM')} - ${to.format('DD MMM')})`;
                const value = `${from.format('YYYY-MM-DD')}_${to.format('YYYY-MM-DD')}`;
                $minggu.append(`<option value="${value}">${label}</option>`);
                date.add(1, 'week').startOf('isoWeek');
                week++;
            }
        }

        // Toggle Filter Button
        function toggleFilterButton() {
            const hasValue = $('[name]:not(#tahun):not(#triwulan):not(#bulan):not(#minggu)').filter(function() {
                return this.value;
            }).length > 0 || $('#tahun').val();
            $('#filterBtn').prop('disabled', !hasValue);
        }

        // Debounce
        const debounce = (func, wait) => {
            let timeout;
            return (...args) => {
                clearTimeout(timeout);
                timeout = setTimeout(() => func.apply(this, args), wait);
            };
        };

        // Load Table
        function loadTable(status, tableId, summaryId) {
            if ($.fn.DataTable.isDataTable(tableId)) $(tableId).DataTable().destroy();

            const params = {
                status,
                sales_key: $('#sales_key').val(),
                materi_key: $('#materi_key').val(),
                tahun: $('#tahun').val(),
                bulan: $('#bulan').val(),
                triwulan: $('#triwulan').val(),
                tanggal_awal_mulai: $('#tanggal_awal_mulai').val(),
                tanggal_awal_akhir: $('#tanggal_awal_akhir').val()
            };

            const url = "{{ route('jsonLaporan') }}?" + $.param(params);

            $.ajax({
                url,
                method: 'GET',
                dataType: 'json',
                beforeSend: () => {
                    $('#filterBtn, #resetBtn').prop('disabled', true);
                    $('#filterText').addClass('d-none');
                    $('#loadingText').removeClass('d-none');
                },
                success: (response) => {
                    const {
                        data,
                        summary
                    } = response;

                    const prefix = status === 0 ? 'win' : 'lost';

                    $(tableId).DataTable({
                        data: data,
                        columns: [{
                                data: 'id',
                                className: 'text-center'
                            },
                            {
                                data: 'sales_key',
                                className: 'text-center'
                            },
                            {
                                data: 'nama_materi',
                                className: 'text-center'
                            },
                            {
                                data: 'nama_perusahaan',
                                className: 'text-center'
                            },
                            {
                                data: 'pax',
                                className: 'text-center'
                            },
                            {
                                data: 'harga',
                                render: d => formatRupiah(d),
                                className: 'text-end'
                            },
                            {
                                data: 'total_exam',
                                render: d => formatRupiah(d),
                                className: 'text-end'
                            },
                            {
                                data: 'netsales',
                                render: d => formatRupiah(d),
                                className: 'text-end'
                            },
                            {
                                data: 'grandtotal',
                                render: d => formatRupiah(d),
                                className: 'text-end fw-bold'
                            },
                            {
                                data: 'tanggal_awal',
                                render: d => d ? moment(d).format('DD-MM-YYYY') : '-',
                                className: 'text-center'
                            },
                            {
                                data: 'tanggal_akhir',
                                render: d => d ? moment(d).format('DD-MM-YYYY') : '-',
                                className: 'text-center'
                            }
                        ],
                        pageLength: 10,
                        responsive: true,
                        language: {
                            url: '//cdn.datatables.net/plug-ins/1.13.8/i18n/id.json'
                        },
                        drawCallback: () => {
                            // Update Summary
                            $(`#${prefix}HargaJual`).text(formatRupiah(summary.total_harga_jual || 0));
                            $(`#${prefix}NetSales`).text(formatRupiah(summary.total_netsales || 0));
                            $(`#${prefix}Exam`).text(formatRupiah(summary.total_exam || 0));
                            $(`#${prefix}GrandTotal`).text(formatRupiah(summary.total_grand || 0));
                            $(summaryId).removeClass('d-none');

                            // Row Click
                            $(tableId + ' tbody').off('click', 'tr').on('click', 'tr', function() {
                                const rowData = $(tableId).DataTable().row(this).data();

                                console.log(rowData.invoice);
                                if (!rowData) return;

                                const tanggalAwal = new Date(rowData.tanggal_awal).toLocaleDateString('id-ID', {
                                    day: '2-digit',
                                    month: 'long',
                                    year: 'numeric'
                                });

                                const tanggalAkhir = new Date(rowData.tanggal_akhir).toLocaleDateString('id-ID', {
                                    day: '2-digit',
                                    month: 'long',
                                    year: 'numeric'
                                });

                                let html = `
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p><strong>ID:</strong> ${rowData.id}</p>
                                            <p><strong>Sales:</strong> ${rowData.sales_key}</p>
                                            <p><strong>Materi:</strong> ${rowData.nama_materi}</p>
                                            <p><strong>Perusahaan:</strong> ${rowData.nama_perusahaan}</p>
                                            <p><strong>Pax:</strong> ${rowData.pax}</p>
                                            <p><strong>Periode:</strong> ${tanggalAwal} s/d ${tanggalAkhir}</p>
                                        </div>
                                        <div class="col-md-6">
                                            <p><strong>Harga:</strong> ${formatRupiah(rowData.harga)}</p>
                                            <p><strong>Total Penjualan:</strong> ${formatRupiah(rowData.total_penjualan)}</p>
                                            <p><strong>Exam:</strong> ${formatRupiah(rowData.total_exam)}</p>
                                            <p><strong>NetSales:</strong> ${formatRupiah(rowData.netsales)}</p>
                                            <p><strong>Grand Total:</strong> ${formatRupiah(rowData.grandtotal)}</p>
                                        </div>
</div>

                                    <hr class="my-3">
                                    <h6 class="mb-3">Informasi Invoice</h6>
                                    `;

                                if (rowData.invoice) {
                                    html += `
                                        <div class="row">
                                            <div class="col-md-6">
                                                <p><strong>Nomor Invoice:</strong> ${rowData.invoice.invoice_number}</p>
                                                <p><strong>Tanggal Invoice:</strong> ${rowData.invoice.tanggal_invoice}</p>
                                                <p><strong>Jumlah Total:</strong> ${formatRupiah(rowData.invoice.amount)}</p>
                                            </div>
                                            <div class="col-md-6">
                                                <p><strong>Bank Tujuan:</strong> ${rowData.invoice.bank_name}</p>
                                                <p><strong>Rekening Tujuan:</strong> ${rowData.invoice.account_number}</p>
                                            </div>
                                        </div>`;
                                } else {
                                    html += `
                                        <p><strong>Status Invoice:</strong> <span class="badge bg-warning text-dark">Belum ada invoice</span></p>
                                        @if(auth()->user()->jabatan === 'Finance & Accounting')
                                        <a class="btn btn-primary btn-sm mt-2" href="/invoice/create/${rowData.id}" target="_blank">
                                            <i class="bi bi-file-earmark-plus"></i> Generate Invoice
                                        </a>
                                        @endif
                                        `;
                                }


                                if (rowData.perhitungannet && rowData.perhitungannet.length > 0) {
                                    html += `<h6 class="mt-3">Detail NetSales</h6>
                                        <div class="table-responsive">
                                            <table class="table table-sm table-bordered">
                                                <thead><tr>
                                                    <th>Peserta</th><th>Trans</th><th>Penginapan</th><th>Fresh</th><th>Cashback</th><th>Diskon</th><th>Entertaint</th><th>Souvenir</th><th>Pembayaran</th>
                                                </tr></thead><tbody>`;
                                    rowData.perhitungannet.forEach(n => {
                                        const peserta = n.peserta?.nama || '-';
                                        html += `<tr>
                                            <td>${peserta}</td>
                                            <td>${formatRupiah(n.transportasi)}</td>
                                            <td>${formatRupiah(n.penginapan)}</td>
                                            <td>${formatRupiah(n.fresh_money)}</td>
                                            <td>${formatRupiah(n.cashback)}</td>
                                            <td>${formatRupiah(n.diskon)}</td>
                                            <td>${formatRupiah(n.entertaint)}</td>
                                            <td>${formatRupiah(n.souvenir)}</td>
                                            <td>${(n.tipe_pembayaran || '-').toUpperCase()}</td>
                                        </tr>`;
                                    });
                                    html += `</tbody></table></div>
                                        <a href="/crm/edit/${rowData.id}/pa" class="btn btn-primary btn-sm mt-2" target="_blank">Edit PA</a>`;
                                } else {
                                    html += `<p class="text-muted mt-3">Tidak ada detail NetSales.</p>`;
                                }

                                $('#detailContent').html(html);
                                $('#detailModalLabel').text(`Detail ${status === 0 ? 'Win' : 'Lost'}`);
                                new bootstrap.Modal('#detailModal').show();
                            });
                        }
                    });
                },
                error: () => {
                    alert('Gagal memuat data.');
                },
                complete: () => {
                    $('#filterBtn, #resetBtn').prop('disabled', false);
                    $('#filterText').removeClass('d-none');
                    $('#loadingText').addClass('d-none');
                    toggleFilterButton();
                }
            });
        }

        const loadBoth = debounce(() => {
            loadTable(0, '#tableStatus0', '#summaryWin');
            loadTable(2, '#tableStatus2', '#summaryLost');
        }, 300);

        function resetFilters() {
            $('#filterForm')[0].reset();
            $('.select2-materi, .select2-sales').val(null).trigger('change');
            toggleTriwulanBulan();
            $('#minggu').empty().append('<option value="" selected>Pilih Bulan & Tahun Dulu</option>').prop('disabled', true);

            // Reset Summary
            ['win', 'lost'].forEach(prefix => {
                $(`#${prefix}HargaJual, #${prefix}NetSales, #${prefix}Exam, #${prefix}GrandTotal`).text('Rp 0');
                $(`#summary${prefix === 'win' ? 'Win' : 'Lost'}`).addClass('d-none');
            });

            toggleFilterButton();
            loadBoth();
        }

        // Events
        $('#tahun').on('change', function() {
            toggleTriwulanBulan();
            populateWeeksDropdown();
            toggleFilterButton();
        });

        $('#bulan').on('change', function() {
            if ($(this).val()) $('#triwulan').val('');
            populateWeeksDropdown();
            toggleFilterButton();
        });

        $('#triwulan').on('change', function() {
            if ($(this).val()) {
                $('#bulan').val('');
                $('#minggu').empty().append('<option value="" selected>Pilih Bulan & Tahun Dulu</option>').prop('disabled', true);
            }
            toggleFilterButton();
        });

        $('#minggu').on('change', function() {
            const val = $(this).val();
            if (val) {
                const [start, end] = val.split('_');
                $('#tanggal_awal_mulai').val(start);
                $('#tanggal_awal_akhir').val(end);
                $('#triwulan').val('');
            } else {
                $('#tanggal_awal_mulai, #tanggal_awal_akhir').val('');
            }
            toggleFilterButton();
        });

        $('#tanggal_awal_mulai, #tanggal_awal_akhir').on('input', function() {
            if ($(this).val()) $('#minggu').val('');
            toggleFilterButton();
        });

        $('#filterForm').on('change', 'select, input', toggleFilterButton);
        $('#filterBtn').on('click', loadBoth);
        $('#resetBtn').on('click', resetFilters);

        // Initial Load
        toggleTriwulanBulan();
        toggleFilterButton();
        loadBoth();
    });
</script>
@endsection