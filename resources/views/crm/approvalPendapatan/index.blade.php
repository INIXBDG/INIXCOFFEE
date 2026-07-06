@extends('layouts_crm.app')

@section('crm_contents')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <div class="container-fluid mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="m-0 fw-bold">Validasi Penjualan Sales</h3>
            <span class="badge bg-primary-subtle text-primary-emphasis fs-6 px-3 py-2" id="current-period"></span>
        </div>

        <div class="card shadow-sm mb-4 border-0 bg-gradient">
            <div class="card-body">
                <div class="row g-3 align-items-end">
                    <div class="col-md-4 mx-1">
                        <label class="form-label fw-semibold small text-muted">Tahun</label>
                        <select id="year" class="form-select" aria-label="tahun">
                            <option disabled>Pilih Tahun</option>
                            @php
                                $tahun_sekarang = now()->year;
                                for ($tahun = 2020; $tahun <= $tahun_sekarang + 2; $tahun++) {
                                    $selected = $tahun == $tahun_sekarang ? 'selected' : '';
                                    echo "<option value=\"$tahun\" $selected>$tahun</option>";
                                }
                            @endphp
                        </select>
                    </div>
                    <div class="col-md-4 mx-1">
                        <label class="form-label fw-semibold small text-muted">Bulan</label>
                        <select id="month" class="form-select" aria-label="bulan">
                            <option disabled>Pilih Bulan</option>
                            @php
                                $bulan_sekarang = now()->month;
                                $nama_bulan = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
                                for ($bulan = 1; $bulan <= 12; $bulan++) {
                                    $bulan_nama = $nama_bulan[$bulan - 1];
                                    $selected = $bulan == $bulan_sekarang ? 'selected' : '';
                                    echo "<option value=\"$bulan\" $selected>$bulan_nama</option>";
                                }
                            @endphp
                        </select>
                    </div>
                    <div class="col-md-3 mx-1">
                        <button class="btn btn-primary" onclick="loadTable();">Refresh</button>
                    </div>
                </div>
            </div>
        </div>

        <div id="weekly-container"></div>
        <div id="loading-spinner" class="text-center py-5 d-none">
            <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;"></div>
            <p class="mt-3 text-muted">Memuat data...</p>
        </div>
    </div>

    {{-- ========== MODAL UPDATE ========== --}}
    <div class="modal fade" id="updateModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg overflow-hidden" style="border-radius:16px;">

                <div class="modal-header px-4 py-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="modal-icon-wrap d-flex align-items-center justify-content-center rounded-circle bg-white bg-opacity-20" style="width:40px;height:40px;">
                            <i class="bi bi-pencil-square fs-5"></i>
                        </div>
                        <div>
                            <h5 class="modal-title fw-bold mb-0">Update Validasi Penjualan Sales</h5>
                            <small class="text-white text-opacity-75" id="modal-subtitle">—</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                {{-- Step Indicator (2 step saja: tidak ada tracking di fitur sales) --}}
                <div class="px-4 py-2 border-bottom d-flex align-items-center gap-0" style="background:#f8fafc;">
                    <div class="step-pill active" data-step="1">
                        <span class="step-num">1</span>
                        <span class="step-label">Informasi</span>
                    </div>
                    <div class="step-line"></div>
                    <div class="step-pill" data-step="2">
                        <span class="step-num">2</span>
                        <span class="step-label">Perhitungan</span>
                    </div>
                </div>

                <form id="formUpdate">
                    @csrf
                    <input type="hidden" id="update_id">

                    <div class="modal-body p-0" style="background:#f8fafc;max-height:70vh;overflow-y:auto;">

                        {{-- STEP 1: Informasi Training --}}
                        <div class="step-content p-4" id="step-1">
                            <div class="row g-3">
                                <div class="col-12">
                                    <div class="section-label-bar">
                                        <i class="bi bi-file-text me-2 text-primary"></i>
                                        <span>Informasi Training</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small">No Faktur</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white"><i class="bi bi-receipt text-muted"></i></span>
                                        <input type="text" class="form-control" id="no_faktur" name="no_faktur" placeholder="Nomor faktur">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small">No Invoice</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white"><i class="bi bi-card-list text-muted"></i></span>
                                        <input type="text" class="form-control" id="no_invoice" name="no_invoice" placeholder="Nomor invoice">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small">Materi</label>
                                    <select class="form-select" id="materi" name="materi">
                                        <option value="">Pilih Materi</option>
                                        @foreach ($dataMateri as $m)
                                            <option value="{{ $m->id }}">{{ $m->nama_materi }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small">Perusahaan</label>
                                    <select class="form-select" id="perusahaan" name="perusahaan">
                                        <option value="">Pilih Perusahaan</option>
                                        @foreach ($dataPerusahaan as $p)
                                            <option value="{{ $p->id }}">{{ $p->nama_perusahaan }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small">Tanggal Mulai</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white"><i class="bi bi-calendar-event text-muted"></i></span>
                                        <input type="date" class="form-control" id="tanggal_mulai" name="tanggal_mulai">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small">Tanggal Selesai</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white"><i class="bi bi-calendar-check text-muted"></i></span>
                                        <input type="date" class="form-control" id="tanggal_selesai" name="tanggal_selesai">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small">Sales</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white"><i class="bi bi-person text-muted"></i></span>
                                        <input type="text" class="form-control bg-light" id="nama_sales" readonly placeholder="—">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small">Instruktur</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white"><i class="bi bi-person-badge text-muted"></i></span>
                                        <input type="text" class="form-control bg-light" id="instruktur" readonly placeholder="—">
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end mt-4">
                                <button type="button" class="btn btn-primary px-4" onclick="goStep(2)">
                                    Selanjutnya <i class="bi bi-arrow-right ms-1"></i>
                                </button>
                            </div>
                        </div>

                        {{-- STEP 2: Perhitungan --}}
                        <div class="step-content p-4 d-none" id="step-2">
                            <div class="row g-3">
                                <div class="col-12">
                                    <div class="section-label-bar">
                                        <i class="bi bi-calculator me-2 text-success"></i>
                                        <span>Perhitungan Penjualan</span>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fw-semibold small">Harga Net</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white text-muted small">Rp</span>
                                        <input type="number" class="form-control input-calc" id="harga" name="harga">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label fw-semibold small">Pax</label>
                                    <input type="number" class="form-control input-calc" id="pax" name="pax">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small">Total Penjualan Kotor</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-success bg-opacity-10 text-success">Rp</span>
                                        <input type="number"
                                            class="form-control bg-success bg-opacity-10 fw-bold text-success"
                                            id="total"
                                            name="total">
                                    </div>
                                </div>

                                <div class="col-12 mt-2">
                                    <div class="section-label-bar">
                                        <i class="bi bi-dash-circle me-2 text-danger"></i>
                                        <span>Pengurang</span>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fw-semibold small">Diskon / PA</label>
                                    <div class="input-group"><span class="input-group-text bg-white text-muted small">Rp</span>
                                    <input type="number" class="form-control input-calc" id="diskon" name="diskon"></div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold small">Total Diskon</label>
                                    <div class="input-group"><span class="input-group-text bg-white text-muted small">Rp</span>
                                    <input type="number" class="form-control input-calc" id="total_diskon" name="total_diskon"></div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold small">Total PA</label>
                                    <div class="input-group"><span class="input-group-text bg-white text-muted small">Rp</span>
                                    <input type="number" class="form-control input-calc" id="total_pa" name="total_pa"></div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold small">Cashback</label>
                                    <div class="input-group"><span class="input-group-text bg-white text-muted small">Rp</span>
                                    <input type="number" class="form-control input-calc" id="total_cashback" name="total_cashback"></div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold small">Uang Saku</label>
                                    <div class="input-group"><span class="input-group-text bg-white text-muted small">Rp</span>
                                    <input type="number" class="form-control input-calc" id="total_uang_saku" name="total_uang_saku"></div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold small">Akomodasi</label>
                                    <div class="input-group"><span class="input-group-text bg-white text-muted small">Rp</span>
                                    <input type="number" class="form-control input-calc" id="total_akomodasi" name="total_akomodasi"></div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold small">Oleh-Oleh Peserta</label>
                                    <div class="input-group"><span class="input-group-text bg-white text-muted small">Rp</span>
                                    <input type="number" class="form-control input-calc" id="oleh_oleh" name="oleh_oleh"></div>
                                </div>

                                <div class="col-12 mt-2">
                                    <div class="section-label-bar">
                                        <i class="bi bi-truck me-2 text-warning"></i>
                                        <span>Transport</span>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small">Jenis Transportasi</label>
                                    <select class="form-select" id="transportasi_select">
                                        <option value="">Pilih Transportasi</option>
                                        <option>Pesawat</option>
                                        <option>Kereta</option>
                                        <option>Bus</option>
                                        <option>Mobil</option>
                                        <option>Travel</option>
                                        <option>Lainnya</option>
                                    </select>
                                    <input type="text" class="form-control mt-2 d-none" id="transportasi_manual" placeholder="Transportasi lainnya">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small">Biaya Transport</label>
                                    <div class="input-group"><span class="input-group-text bg-white text-muted small">Rp</span>
                                    <input type="number" class="form-control input-calc" id="biaya_transport" name="biaya_transport"></div>
                                </div>
                            </div>

                            <div class="mt-4 p-3 rounded-3 d-flex align-items-center justify-content-between">
                                <div>
                                    <div class="small opacity-75 mb-1">Total Penjualan Sales (Bersih)</div>
                                    <input type="number"
                                        class="form-control fw-bold"
                                        id="total_penjualan_sales"
                                        name="total_penjualan_sales">
                                </div>
                                <i class="bi bi-graph-up-arrow fs-1 opacity-25"></i>
                            </div>

                            <div class="d-flex justify-content-between mt-4">
                                <button type="button" class="btn btn-light px-4" onclick="goStep(1)">
                                    <i class="bi bi-arrow-left me-1"></i> Kembali
                                </button>
                                <button type="submit" class="btn btn-success px-5 fw-semibold" id="btnSimpan">
                                    <i class="bi bi-save me-2"></i>Simpan
                                </button>
                            </div>
                        </div>

                    </div>
                </form>
            </div>
        </div>
    </div>

    <style>
        #weekly-container { overflow-y: hidden; }
        .cursor-pointer { cursor: pointer; }
        .table th { font-size: .75rem; text-transform: uppercase; letter-spacing: .5px; }
        .table td { font-size: .8rem; }
        @media (max-width:1400px) {
            .table th, .table td { padding: .4rem .5rem; }
        }
        .table-info td { background-color: #cff4fc !important; color: #055160; border-top: 2px solid #9eeaf9; }
        .table-dark td { background-color: #212529 !important; color: #fff; border-top: 3px double #495057; font-size: .85rem; }

        .sync-scroll-wrapper { overflow-x: auto; }

        .step-pill {
            display: flex; align-items: center; gap: 6px;
            padding: 6px 12px; border-radius: 20px;
            font-size: .8rem; font-weight: 500;
            color: #9ca3af; background: transparent;
            transition: all .2s;
        }
        .step-pill.active { background: #e0ecff; color: #1e3a5f; }
        .step-pill.done { color: #16a34a; }
        .step-num {
            display: inline-flex; align-items: center; justify-content: center;
            width: 22px; height: 22px; border-radius: 50%;
            background: #e5e7eb; color: #6b7280; font-size: .75rem; font-weight: 700;
        }
        .step-pill.active .step-num { background: #1e3a5f; color: #fff; }
        .step-pill.done .step-num { background: #16a34a; color: #fff; }
        .step-line { flex: 1; height: 2px; background: #e5e7eb; min-width: 24px; margin: 0 4px; }

        .section-label-bar {
            display: flex; align-items: center;
            font-size: .8rem; font-weight: 700;
            text-transform: uppercase; letter-spacing: .6px;
            color: #374151;
            padding-bottom: 8px;
            border-bottom: 2px solid #e5e7eb;
            margin-bottom: 4px;
        }

        .bg-gradient { background: linear-gradient(135deg,#f8f9fa 0%,#e9ecef 100%); }
        .badge { font-weight: 500; }
        .form-label { margin-bottom: .35rem; }
    </style>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.17.1/moment-with-locales.min.js"></script>
    <script>
        let manualTotalKotor = false;
        let manualTotalBersih = false;

        $(document).ready(function () {
            updateCurrentPeriod();
            loadTable();
        });

        $('#month, #year').on('change', function () {
            updateCurrentPeriod();
            loadTable();
        });

        function updateCurrentPeriod() {
            let bulan = $('#month option:selected').text();
            let tahun = $('#year').val();
            if (bulan && tahun && bulan !== 'Pilih Bulan') {
                $('#current-period').text('Periode: ' + bulan + ' ' + tahun);
            }
        }

        function formatRupiah(value) {
            if (!value || value === 'belum tervalidasi' || value === 'kosong' || isNaN(value)) return value;
            return 'Rp ' + parseFloat(value).toLocaleString('id-ID');
        }

        function parseNumber(value) {
            if (!value || value === 'belum tervalidasi' || value === 'kosong') return 0;
            return parseFloat(value) || 0;
        }

        $('#transportasi_select').on('change', function () {
            if ($(this).val() === 'Lainnya') {
                $('#transportasi_manual').removeClass('d-none').focus();
            } else {
                $('#transportasi_manual').addClass('d-none').val('');
            }
        });

        $('#total').on('input', function () {
            manualTotalKotor = true;
            calculateTotalPenjualanSales();
        });

        $('#total_penjualan_sales').on('input', function () {
            manualTotalBersih = true;
        });

        $(document).on('input change', '.input-calc', function () {
            calculateTotal();
            calculateTotalPenjualanSales();
        });

        function calculateTotal() {
            let harga = parseNumber($('#harga').val());
            let pax   = parseNumber($('#pax').val());
            let total = harga * pax;

            if (!manualTotalKotor) {
                $('#total').val(total);
            }
        }

        function calculateTotalPenjualanSales() {
            let total = parseNumber($('#total').val());
            let deductions =
                parseNumber($('#diskon').val()) +
                parseNumber($('#total_diskon').val()) +
                parseNumber($('#total_pa').val()) +
                parseNumber($('#total_cashback').val()) +
                parseNumber($('#total_uang_saku').val()) +
                parseNumber($('#total_akomodasi').val()) +
                parseNumber($('#biaya_transport').val()) +
                parseNumber($('#oleh_oleh').val());

            let totalPenjualanSales = Math.max(0, total - deductions);
            if (!manualTotalBersih) {
                $('#total_penjualan_sales').val(totalPenjualanSales);
            }
        }

        function goStep(n) {
            $('.step-content').addClass('d-none');
            $('#step-' + n).removeClass('d-none');

            $('.step-pill').each(function () {
                let s = parseInt($(this).data('step'));
                $(this).removeClass('active done');
                if (s === n)  $(this).addClass('active');
                if (s < n)    $(this).addClass('done').find('.step-num').html('<i class="bi bi-check-lg" style="font-size:.7rem"></i>');
                if (s >= n)   $(this).find('.step-num').text(s);
            });
        }

        function loadTable() {
            let bulan = $('#month').val();
            let tahun = $('#year').val();

            if (!bulan || !tahun) {
                $('#weekly-container').html(`<div class="alert alert-warning">Silakan pilih periode bulan dan tahun terlebih dahulu.</div>`);
                return;
            }

            $('#loading-spinner').removeClass('d-none');
            $('#weekly-container').html('');

            $.ajax({
                url: `/crm/approval-pendapatan-sales/get/${tahun}/${bulan}`,
                type: "GET",
                success: function (response) {
                    $('#loading-spinner').addClass('d-none');

                    let container = $('#weekly-container');
                    let monthDataList   = response.data || [];
                    let footerBulanan   = response.footer_bulanan || {};
                    let footerTahunan   = response.footer_tahunan || {};

                    if (monthDataList.length === 0) {
                        container.html(`
                            <div class="card shadow-sm border-0">
                                <div class="card-body text-center py-5">
                                    <i class="bi bi-inbox fs-1 text-muted mb-3"></i>
                                    <p class="text-muted fs-5 mb-0">Tidak ada data pada periode ini</p>
                                </div>
                            </div>
                        `);
                        return;
                    }

                    moment.locale('id');

                    let totalWeeks = 0;
                    monthDataList.forEach(md => totalWeeks += md.weeksData.length);
                    let currentWeekIndex = 0;

                    monthDataList.forEach(function (monthData) {
                        monthData.weeksData.forEach(function (weekData) {
                            currentWeekIndex++;
                            let isLastWeek = currentWeekIndex === totalWeeks;

                            var startOfWeek = moment(weekData.start);
                            var endOfWeek   = startOfWeek.clone().add(4, 'days');

                            let rows = '';

                            if (weekData.data.length === 0) {
                                rows = `<tr><td colspan="20" class="text-center">Tidak Ada Data pada Periode Ini</td></tr>`;
                            } else {
                                weekData.data.forEach((item, i) => {
                                    let totalVal = Number(item.total_penjualan_kotor ?? (item.harga * item.pax) ?? 0);
                                    let rowClass  = item.valid === 'valid' ? '' : 'table-warning';
                                    let encodedItem = encodeURIComponent(JSON.stringify(item));

                                    rows += `
                                        <tr class="${rowClass} cursor-pointer btn-edit" data-item="${encodedItem}">
                                            <td class="text-center fw-bold">${i + 1}</td>
                                            <td>${escapeHtml(item.no_faktur)}</td>
                                            <td>${escapeHtml(item.no_invoice)}</td>
                                            <td>${escapeHtml(item.materi)}</td>
                                            <td>${escapeHtml(item.tanggal_training)}</td>
                                            <td>${escapeHtml(item.perusahaan)}</td>
                                            <td>${escapeHtml(item.nama_sales)}</td>
                                            <td>${escapeHtml(item.instruktur)}</td>
                                            <td class="text-end">${formatRupiah(item.harga)}</td>
                                            <td class="text-center">${item.pax ?? '-'}</td>
                                            <td class="text-end">${formatRupiah(totalVal)}</td>
                                            <td class="text-end">${formatRupiah(item.diskon || 0)}</td>
                                            <td class="text-end">${formatRupiah(item.total_diskon || 0)}</td>
                                            <td class="text-end">${formatRupiah(item.total_pa || 0)}</td>
                                            <td class="text-end">${formatRupiah(item.total_cashback || 0)}</td>
                                            <td class="text-end">${formatRupiah(item.total_uang_saku || 0)}</td>
                                            <td class="text-end">${formatRupiah(item.total_akomodasi || 0)}</td>
                                            <td class="text-end">${formatRupiah(item.oleh_oleh || 0)}</td>
                                            <td>${escapeHtml(item.jenis_transport || '-')}</td>
                                            <td class="text-end">${formatRupiah(item.biaya_transport || 0)}</td>
                                            <td class="text-end">${formatRupiah(item.total_penjualan_sales || 0)}</td>
                                            <td>${escapeHtml(item.tanggal_mulai || '-')}</td>
                                            <td>${escapeHtml(item.tanggal_selesai || '-')}</td>
                                        </tr>
                                    `;
                                });
                            }

                            let footerHtml = '';
                            if (isLastWeek) {
                                footerHtml = `
                                    <tfoot>
                                        <tr class="table-info fw-bold">
                                            <td colspan="10" class="text-end">TOTAL BULANAN</td>
                                            <td class="text-end">${formatRupiah(footerBulanan.total_penjualan || 0)}</td>
                                            <td class="text-end">${formatRupiah(footerBulanan.total_diskon || 0)}</td>
                                            <td class="text-end">${formatRupiah(footerBulanan.total_pa || 0)}</td>
                                            <td class="text-end">${formatRupiah(footerBulanan.total_cashback || 0)}</td>
                                            <td class="text-end">${formatRupiah(footerBulanan.total_uang_saku || 0)}</td>
                                            <td class="text-end">${formatRupiah(footerBulanan.total_akomodasi || 0)}</td>
                                            <td class="text-end">${formatRupiah(footerBulanan.oleh_oleh || 0)}</td>
                                            <td class="text-end">-</td>
                                            <td class="text-end">${formatRupiah(footerBulanan.biaya_transport || 0)}</td>
                                            <td class="text-end">${formatRupiah(footerBulanan.total_penjualan_sales || 0)}</td>
                                            <td colspan="2"></td>
                                        </tr>
                                        <tr class="table-dark fw-bold">
                                            <td colspan="10" class="text-end">TOTAL TAHUNAN</td>
                                            <td class="text-end">${formatRupiah(footerTahunan.total_penjualan || 0)}</td>
                                            <td class="text-end">${formatRupiah(footerTahunan.total_diskon || 0)}</td>
                                            <td class="text-end">${formatRupiah(footerTahunan.total_pa || 0)}</td>
                                            <td class="text-end">${formatRupiah(footerTahunan.total_cashback || 0)}</td>
                                            <td class="text-end">${formatRupiah(footerTahunan.total_uang_saku || 0)}</td>
                                            <td class="text-end">${formatRupiah(footerTahunan.total_akomodasi || 0)}</td>
                                            <td class="text-end">${formatRupiah(footerTahunan.oleh_oleh || 0)}</td>
                                            <td class="text-end">-</td>
                                            <td class="text-end">${formatRupiah(footerTahunan.biaya_transport || 0)}</td>
                                            <td class="text-end">${formatRupiah(footerTahunan.total_penjualan_sales || 0)}</td>
                                            <td colspan="2"></td>
                                        </tr>
                                    </tfoot>
                                `;
                            }

                            container.append(`
                                <div class="card my-1">
                                    <div class="card-body p-2">
                                        <h3 class="card-title my-1 fs-6 fw-bold">Validasi Penjualan Sales</h3>
                                        <p class="card-title my-1 text-muted small">Periode : ${moment(startOfWeek).format('DD MMMM YYYY')} - ${moment(endOfWeek).format('DD MMMM YYYY')}</p>
                                        <div class="sync-scroll-wrapper table-scroll-sync">
                                            <table class="table table-striped table-hover mb-0" style="min-width:1900px;">
                                                <thead>
                                                    <tr>
                                                        <th>No</th>
                                                        <th>No Faktur</th>
                                                        <th>No Invoice</th>
                                                        <th>Materi</th>
                                                        <th>Tanggal</th>
                                                        <th>Perusahaan</th>
                                                        <th>Sales</th>
                                                        <th>Instruktur</th>
                                                        <th>Harga</th>
                                                        <th>Pax</th>
                                                        <th>Total Penjualan Kotor</th>
                                                        <th>Diskon/PA</th>
                                                        <th>Total Diskon</th>
                                                        <th>Total PA</th>
                                                        <th>Cashback</th>
                                                        <th>Uang Saku</th>
                                                        <th>Akomodasi</th>
                                                        <th>Oleh-Oleh peserta</th>
                                                        <th>Jenis Transport</th>
                                                        <th>Biaya Transport</th>
                                                        <th>Total Penjualan Sales (Bersih)</th>
                                                        <th>Tanggal Mulai</th>
                                                        <th>Tanggal Selesai</th>
                                                    </tr>
                                                </thead>
                                                <tbody>${rows}</tbody>
                                                ${footerHtml}
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            `);
                        });
                    });

                    bindSyncScroll();
                },
                error: function (xhr) {
                    $('#loading-spinner').addClass('d-none');
                    let msg = xhr.responseJSON?.error || 'Gagal memuat data';
                    $('#weekly-container').html(`<div class="alert alert-danger">${escapeHtml(msg)}</div>`);
                }
            });
        }

        function bindSyncScroll() {
            let $wrappers = $('.table-scroll-sync');
            let isSyncing = false;

            $wrappers.off('scroll.sync').on('scroll.sync', function () {
                if (isSyncing) return;
                isSyncing = true;
                let scrollLeft = this.scrollLeft;
                $wrappers.not(this).each(function () {
                    this.scrollLeft = scrollLeft;
                });
                isSyncing = false;
            });
        }

        $(document).on('click', '.btn-edit', function (e) {
            e.preventDefault();
            e.stopPropagation();
            let item = JSON.parse(decodeURIComponent($(this).data('item')));

            manualTotalKotor = false;
            manualTotalBersih = false;

            $('#update_id').val(item.id_rkm);
            $('#modal-subtitle').text((item.no_faktur ?? '') + (item.no_invoice ? ' · ' + item.no_invoice : ''));

            $('#no_faktur').val(item.no_faktur ?? '');
            $('#no_invoice').val(item.no_invoice ?? '');
            $('#materi').val(item.materi_id ?? '');
            $('#perusahaan').val(item.perusahaan_id ?? '');
            $('#tanggal_mulai').val(item.tanggal_mulai ?? '');
            $('#tanggal_selesai').val(item.tanggal_selesai ?? '');
            $('#nama_sales').val(item.nama_sales ?? '');
            $('#instruktur').val(item.instruktur ?? '');

            $('#harga').val(item.harga ?? '');
            $('#pax').val(item.pax ?? '');
            $('#diskon').val(item.diskon ?? '');
            $('#total_diskon').val(item.total_diskon ?? '');
            $('#total_pa').val(item.total_pa ?? '');
            $('#total_cashback').val(item.total_cashback ?? '');
            $('#total_uang_saku').val(item.total_uang_saku ?? '');
            $('#total_akomodasi').val(item.total_akomodasi ?? '');
            $('#oleh_oleh').val(item.oleh_oleh ?? '');
            $('#biaya_transport').val(item.biaya_transport ?? '');

            if (['Pesawat','Kereta','Bus','Mobil','Travel','Lainnya'].includes(item.jenis_transport)) {
                $('#transportasi_select').val(item.jenis_transport);
                if (item.jenis_transport === 'Lainnya') {
                    $('#transportasi_manual').removeClass('d-none').val(item.jenis_transport);
                } else {
                    $('#transportasi_manual').addClass('d-none').val('');
                }
            } else {
                $('#transportasi_select').val('');
                $('#transportasi_manual').removeClass('d-none').val(item.jenis_transport ?? '');
            }

            calculateTotal();
            calculateTotalPenjualanSales();
            goStep(1);

            new bootstrap.Modal(document.getElementById('updateModal')).show();
        });

        $('#formUpdate').submit(function (e) {
            e.preventDefault();
            let id  = $('#update_id').val();
            let jenisTransport = $('#transportasi_select').val() === 'Lainnya'
                ? $('#transportasi_manual').val()
                : $('#transportasi_select').val();
            let $btn = $('#btnSimpan');

            $.ajax({
                url: `/crm/approval-pendapatan-sales/update/${id}`,
                type: 'POST',
                data: {
                    _token: "{{ csrf_token() }}",
                    no_faktur: $('#no_faktur').val(),
                    no_invoice: $('#no_invoice').val(),
                    harga: $('#harga').val(),
                    pax: $('#pax').val(),
                    diskon: $('#diskon').val(),
                    total_diskon: $('#total_diskon').val(),
                    total_pa: $('#total_pa').val(),
                    total_cashback: $('#total_cashback').val(),
                    total_uang_saku: $('#total_uang_saku').val(),
                    total_akomodasi: $('#total_akomodasi').val(),
                    jenis_transport: jenisTransport,
                    biaya_transport: $('#biaya_transport').val(),
                    oleh_oleh: $('#oleh_oleh').val(),
                    total_penjualan_sales: $('#total_penjualan_sales').val(),
                    materi: $('#materi').val(),
                    perusahaan: $('#perusahaan').val(),
                    tanggal_mulai: $('#tanggal_mulai').val(),
                    tanggal_selesai: $('#tanggal_selesai').val(),
                },
                beforeSend: function () {
                    $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Menyimpan...');
                },
                success: function (response) {
                    if (response.success) {
                        $('#updateModal').modal('hide');
                        loadTable();
                        showAlert('success', 'Data berhasil diupdate!');
                    } else {
                        showAlert('danger', response.message || 'Gagal menyimpan data.');
                    }
                },
                error: function (xhr) {
                    let msg = xhr.responseJSON?.message || 'Gagal menyimpan data. Silakan coba lagi.';
                    showAlert('danger', msg);
                },
                complete: function () {
                    $btn.prop('disabled', false).html('<i class="bi bi-save me-2"></i>Simpan');
                }
            });
        });

        function showAlert(type, message) {
            let alertHtml = `
                <div class="position-fixed top-0 end-0 p-3" style="z-index:9999">
                    <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                        ${escapeHtml(message)}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                </div>`;
            $('body').append(alertHtml);
            setTimeout(() => $('.alert').alert('close'), 3000);
        }

        function escapeHtml(text) {
            if (!text) return '';
            const map = { '&':'&amp;', '<':'&lt;', '>':'&gt;', '"':'&quot;', "'":'&#039;' };
            return String(text).replace(/[&<>"']/g, m => map[m]);
        }
    </script>
@endsection