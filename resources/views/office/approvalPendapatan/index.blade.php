`@extends('layouts_office.app')

@section('office_contents')
    <div class="container-fluid mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="m-0 fw-bold">Validasi Laba Kotor</h3>
            <span class="badge bg-primary-subtle text-primary-emphasis fs-6 px-3 py-2" id="current-period"></span>
        </div>

        @php
            $currentMonth = date('n');
            $currentYear = date('Y');
            $months = [
                1 => 'Januari',
                2 => 'Februari',
                3 => 'Maret',
                4 => 'April',
                5 => 'Mei',
                6 => 'Juni',
                7 => 'Juli',
                8 => 'Agustus',
                9 => 'September',
                10 => 'Oktober',
                11 => 'November',
                12 => 'Desember',
            ];
        @endphp

        <div class="card shadow-sm mb-4 border-0 bg-gradient">
            <div class="card-body">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label fw-semibold small text-muted">Bulan</label>
                        <select id="month" class="form-select form-select-lg shadow-sm">
                            @foreach ($months as $key => $month)
                                <option value="{{ $key }}" {{ $key == $currentMonth ? 'selected' : '' }}>
                                    {{ $month }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold small text-muted">Tahun</label>
                        <select id="year" class="form-select form-select-lg shadow-sm">
                            @for ($year = 2023; $year <= date('Y'); $year++)
                                <option value="{{ $year }}" {{ $year == $currentYear ? 'selected' : '' }}>
                                    {{ $year }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-md-6 text-end">
                        <button class="btn btn-primary btn-lg shadow-sm" onclick="loadTable()">
                            <i class="bi bi-search me-2"></i>Tampilkan Data
                        </button>
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

    <div class="modal fade" id="updateModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content border-0" style="height:90vh;">
                <div class="modal-header bg-white border-bottom">
                    <h5 class="modal-title fw-semibold">
                        <i class="bi bi-pencil-square me-2"></i>Update Validasi Pendapatan
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <form id="formUpdate">
                    @csrf
                    <input type="hidden" id="update_id">

                    <div class="modal-body bg-white overflow-auto">
                        <div class="container-fluid">
                            <div class="row g-4">
                                <div class="col-lg-8">
                                    <div class="card border shadow-sm mb-4">
                                        <div class="card-header bg-white border-bottom fw-semibold">Informasi Training &
                                            Invoice</div>
                                        <div class="card-body">
                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <label class="form-label text-muted small">No Faktur</label>
                                                    <input type="text" class="form-control" id="no_faktur"
                                                        name="no_faktur">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label text-muted small">No Invoice</label>
                                                    <input type="text" class="form-control" id="no_invoice"
                                                        name="no_invoice">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label text-muted small">Materi</label>
                                                    <select class="form-select" id="materi" name="materi">
                                                        <option value="">Pilih Materi</option>
                                                        @foreach ($dataMateri as $m)
                                                            <option value="{{ $m->id }}">{{ $m->nama_materi }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label text-muted small">Perusahaan</label>
                                                    <select class="form-select" id="perusahaan" name="perusahaan">
                                                        <option value="">Pilih Perusahaan</option>
                                                        @foreach ($dataPerusahaan as $p)
                                                            <option value="{{ $p->id }}">{{ $p->nama_perusahaan }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label text-muted small">Tanggal Mulai</label>
                                                    <input type="date" class="form-control" id="tanggal_mulai"
                                                        name="tanggal_mulai">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label text-muted small">Tanggal Selesai</label>
                                                    <input type="date" class="form-control" id="tanggal_selesai"
                                                        name="tanggal_selesai">
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label text-muted small">Sales</label>
                                                    <input type="text" class="form-control" id="nama_sales" readonly>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label text-muted small">Instruktur</label>
                                                    <input type="text" class="form-control" id="instruktur" readonly>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="card border shadow-sm">
                                        <div class="card-header bg-white border-bottom fw-semibold">Perhitungan Utama</div>
                                        <div class="card-body">
                                            <div class="row g-3">
                                                <div class="col-md-4">
                                                    <label class="form-label text-muted small">Harga Net</label>
                                                    <input type="number" class="form-control input-calc" id="harga"
                                                        name="harga">
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label text-muted small">Pax</label>
                                                    <input type="number" class="form-control input-calc" id="pax"
                                                        name="pax">
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label text-muted small">Total Penjualan Kotor</label>
                                                    <input type="text" class="form-control bg-light"
                                                        id="total_display" readonly>
                                                    <input type="hidden" id="total">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label text-muted small">Diskon/PA</label>
                                                    <input type="number" class="form-control input-calc" id="diskon"
                                                        name="diskon">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label text-muted small">Total Diskon</label>
                                                    <input type="number" class="form-control input-calc"
                                                        id="total_diskon" name="total_diskon">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label text-muted small">Total PA</label>
                                                    <input type="number" class="form-control input-calc" id="total_pa"
                                                        name="total_pa">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label text-muted small">Cashback</label>
                                                    <input type="number" class="form-control input-calc"
                                                        id="total_cashback" name="total_cashback">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label text-muted small">Uang Saku</label>
                                                    <input type="number" class="form-control input-calc"
                                                        id="total_uang_saku" name="total_uang_saku">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label text-muted small">Akomodasi</label>
                                                    <input type="number" class="form-control input-calc"
                                                        id="total_akomodasi" name="total_akomodasi">
                                                </div>
                                                <div class="col-md-12">
                                                    <label class="form-label text-muted small">Oleh-Oleh Peserta</label>
                                                    <input type="number" class="form-control input-calc" id="oleh_oleh"
                                                        name="oleh_oleh">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-lg-4">
                                    <div class="card border shadow-sm mb-4">
                                        <div class="card-header bg-white border-bottom fw-semibold">Transport & Pajak</div>
                                        <div class="card-body">
                                            <div class="row g-3">
                                                <div class="col-12">
                                                    <label class="form-label text-muted small">Transportasi</label>
                                                    <select class="form-select" id="transportasi_select">
                                                        <option value="">Pilih Transportasi</option>
                                                        <option>Pesawat</option>
                                                        <option>Kereta</option>
                                                        <option>Bus</option>
                                                        <option>Mobil</option>
                                                        <option>Travel</option>
                                                        <option>Lainnya</option>
                                                    </select>
                                                    <input type="text" class="form-control mt-2 d-none"
                                                        id="transportasi_manual" placeholder="Transportasi lainnya">
                                                </div>
                                                <div class="col-12">
                                                    <label class="form-label text-muted small">Biaya Transport</label>
                                                    <input type="number" class="form-control" id="biaya_transport"
                                                        name="biaya_transport">
                                                </div>
                                                <div class="col-12">
                                                    <label class="form-label text-muted small">PPN</label>
                                                    <input type="number" class="form-control" id="PPN"
                                                        name="PPN">
                                                </div>
                                                <div class="col-12">
                                                    <label class="form-label text-muted small">PPH</label>
                                                    <input type="number" class="form-control" id="PPH"
                                                        name="PPH">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="card border shadow-sm">
                                        <div class="card-header bg-white border-bottom fw-semibold">Ringkasan</div>
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <small class="text-muted">Total Penjualan Sales</small>
                                                <h4 class="fw-bold mb-0 text-success" id="total_penjualan_sales_display">
                                                    Rp 0</h4>
                                                <input type="hidden" id="total_penjualan_sales"
                                                    name="total_penjualan_sales">
                                            </div>
                                            <div class="mb-3">
                                                <small class="form-label text-muted small">Jumlah Pembayaran</small>
                                                <input type="number" class="form-control" id="jumlah_pembayaran"
                                                    name="jumlah_pembayaran">
                                            </div>
                                            <div class="mb-3">
                                                <small class="form-label text-muted small">Tanggal Pembayaran</small>
                                                <input type="date" class="form-control" id="tanggal_pembayaran"
                                                    name="tanggal_pembayaran">
                                            </div>
                                            <div class="mb-3">
                                                <small class="form-label text-muted small">Biaya Admin</small>
                                                <input type="number" class="form-control" id="biaya_admin"
                                                    name="biaya_admin">
                                            </div>
                                            <div>
                                                <small class="text-muted">Total Piutang</small>
                                                <div id="total_piutang_display" class="fw-semibold">-</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer bg-white border-top">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-save me-2"></i>Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script>
        $(document).ready(function() {
            loadTable();
            updateCurrentPeriod();
        });

        $('#month, #year').on('change', function() {
            updateCurrentPeriod();
            loadTable();
        });

        function updateCurrentPeriod() {
            let bulan = $('#month option:selected').text();
            let tahun = $('#year').val();
            $('#current-period').text(bulan + ' ' + tahun);
        }

        function formatRupiah(value) {
            if (!value || value === 'belum tervalidasi' || value === 'kosong' || isNaN(value)) {
                return value;
            }
            return 'Rp ' + parseFloat(value).toLocaleString('id-ID');
        }

        function parseNumber(value) {
            if (!value || value === 'belum tervalidasi' || value === 'kosong') return 0;
            return parseFloat(value) || 0;
        }

        $('#transportasi_select').on('change', function() {
            if ($(this).val() === 'Lainnya') {
                $('#transportasi_manual').removeClass('d-none').focus();
            } else {
                $('#transportasi_manual').addClass('d-none').val('');
            }
        });

        $('.input-calc').on('input change', function() {
            calculateTotal();
            calculateTotalPenjualanSales();
        });

        function calculateTotal() {
            let harga = parseNumber($('#harga').val());
            let pax = parseNumber($('#pax').val());
            let total = harga * pax;
            $('#total').val(total);
            $('#total_display').val(formatRupiah(total));
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
            $('#total_penjualan_sales').val(totalPenjualanSales);
            $('#total_penjualan_sales_display').text(formatRupiah(totalPenjualanSales));
        }

        function loadTable() {
            let bulan = $('#month').val();
            let tahun = $('#year').val();

            $('#loading-spinner').removeClass('d-none');
            $('#weekly-container').html('');

            $.ajax({
                url: `/office/approval-pendapatan/get/${tahun}/${bulan}`,
                type: "GET",
                success: function(response) {
                    $('#loading-spinner').addClass('d-none');

                    let container = $('#weekly-container');
                    let groupedData = response.groupedData || [];
                    let footerBulanan = response.footer_bulanan || {};
                    let footerTahunan = response.footer_tahunan || {};

                    if (groupedData.length === 0) {
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

                    groupedData.forEach((week, index) => {
                        let rows = '';

                        week.data.forEach((item, i) => {
                            let totalVal = Number(item.harga) * Number(item.pax);
                            let totalSalesVal = Number(item.total_penjualan_sales || 0);
                            let rowClass = item.valid === 'valid' ? '' : 'table-warning';
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
                                    <td class="text-end">${formatRupiah(item.total_penjualan_sales || 0)}</td>
                                    <td class="text-end">${formatRupiah(item.PPN || 0)}</td>
                                    <td class="text-end">${formatRupiah(item.PPH || 0)}</td>
                                    <td class="text-end">${escapeHtml(item.jumlah_pembayaran || '-')}</td>
                                    <td class="text-end">${escapeHtml(item.tanggal_pembayaran || '-')}</td>
                                    <td class="text-end">${escapeHtml(item.biaya_admin || '-')}</td>
                                    <td class="text-end">${escapeHtml(item.total_piutang || '-')}</td>
                                    <td>${escapeHtml(item.jenis_transport || '-')}</td>
                                    <td class="text-end">${formatRupiah(item.biaya_transport || 0)}</td>
                                    <td>${escapeHtml(item.tanggal_mulai || '-')}</td>
                                    <td>${escapeHtml(item.tanggal_selesai || '-')}</td>
                                </tr>
                            `;
                        });

                        let footerHtml = '';

                        if (index === groupedData.length - 1) {
                            footerHtml = `
                                <tfoot>
                                    <tr class="table-info fw-bold">
                                        <td colspan="10" class="text-end">TOTAL BULANAN</td>
                                        <td class="text-end">${formatRupiah(footerBulanan.total_penjualan || 0)}</td>
                                        <td class="text-end">${formatRupiah(footerBulanan.total_diskon || 0)}</td>
                                        <td class="text-end">${formatRupiah(footerBulanan.total_diskon || 0)}</td>
                                        <td class="text-end">${formatRupiah(footerBulanan.total_pa || 0)}</td>
                                        <td class="text-end">${formatRupiah(footerBulanan.total_cashback || 0)}</td>
                                        <td class="text-end">${formatRupiah(footerBulanan.total_uang_saku || 0)}</td>
                                        <td class="text-end">${formatRupiah(footerBulanan.total_akomodasi || 0)}</td>
                                        <td class="text-end">${formatRupiah(footerBulanan.oleh_oleh || 0)}</td>
                                        <td class="text-end">${formatRupiah(footerBulanan.total_penjualan_sales || 0)}</td>
                                        <td class="text-end">${formatRupiah(footerBulanan.total_ppn || 0)}</td>
                                        <td class="text-end">${formatRupiah(footerBulanan.total_pph || 0)}</td>
                                        <td class="text-end">${formatRupiah(footerBulanan.jumlah_pembayaran || 0)}</td>
                                        <td class="text-end">-</td>
                                        <td class="text-end">${formatRupiah(footerBulanan.biaya_admin || 0)}</td>
                                        <td class="text-end">${formatRupiah(footerBulanan.total_piutang || 0)}</td>
                                        <td class="text-end">-</td>
                                        <td class="text-end">${formatRupiah(footerBulanan.biaya_transport || 0)}</td>
                                        <td colspan="2"></td>
                                    </tr>

                                    <tr class="table-dark fw-bold">
                                        <td colspan="10" class="text-end">TOTAL TAHUNAN</td>
                                        <td class="text-end">${formatRupiah(footerTahunan.total_penjualan || 0)}</td>
                                        <td class="text-end">${formatRupiah(footerTahunan.total_diskon || 0)}</td>
                                        <td class="text-end">${formatRupiah(footerTahunan.total_diskon || 0)}</td>
                                        <td class="text-end">${formatRupiah(footerTahunan.total_pa || 0)}</td>
                                        <td class="text-end">${formatRupiah(footerTahunan.total_cashback || 0)}</td>
                                        <td class="text-end">${formatRupiah(footerTahunan.total_uang_saku || 0)}</td>
                                        <td class="text-end">${formatRupiah(footerTahunan.total_akomodasi || 0)}</td>
                                        <td class="text-end">${formatRupiah(footerTahunan.oleh_oleh || 0)}</td>
                                        <td class="text-end">${formatRupiah(footerTahunan.total_penjualan_sales || 0)}</td>
                                        <td class="text-end">${formatRupiah(footerTahunan.total_ppn || 0)}</td>
                                        <td class="text-end">${formatRupiah(footerTahunan.total_pph || 0)}</td>
                                        <td class="text-end">${formatRupiah(footerTahunan.jumlah_pembayaran || 0)}</td>
                                        <td class="text-end">-</td>
                                        <td class="text-end">${formatRupiah(footerTahunan.biaya_admin || 0)}</td>
                                        <td class="text-end">${formatRupiah(footerTahunan.total_piutang || 0)}</td>
                                        <td class="text-end">-</td>
                                        <td class="text-end">${formatRupiah(footerTahunan.biaya_transport || 0)}</td>
                                        <td colspan="2"></td>
                                    </tr>
                                </tfoot>
                            `;
                        }

                        container.append(`
                            <div class="card shadow-sm border-0 mb-4">
                                <div class="card-header bg-light">
                                    <h6 class="m-0 fw-bold">
                                        Minggu ${index + 1} (${escapeHtml(week.range)})
                                    </h6>
                                </div>

                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-hover table-bordered mb-0">
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
                                                    <th>Total Penjualan Sales</th>
                                                    <th>PPN</th>
                                                    <th>PPH</th>
                                                    <th>Jumlah Pembayaran</th>
                                                    <th>Tanggal Pembayaran</th>
                                                    <th>Biaya Admin</th>
                                                    <th>Total Piutang</th>
                                                    <th>Jenis Transport</th>
                                                    <th>Biaya Transport</th>
                                                    <th>Tanggal Mulai</th>
                                                    <th>Tanggal Selesai</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                ${rows}
                                            </tbody>
                                            ${footerHtml}
                                        </table>
                                    </div>
                                </div>
                            </div>
                        `);
                    });
                },

                error: function(xhr) {
                    $('#loading-spinner').addClass('d-none');

                    let msg = xhr.responseJSON?.error || 'Gagal memuat data';

                    $('#weekly-container').html(`
                        <div class="alert alert-danger">
                            ${escapeHtml(msg)}
                        </div>
                    `);
                }
            });
        }

        $(document).on('click', '.btn-edit', function(e) {
            e.preventDefault();
            e.stopPropagation();
            let encoded = $(this).data('item');
            let item = JSON.parse(decodeURIComponent(encoded));

            $('#update_id').val(item.id_rkm);
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
            $('#PPN').val(item.PPN ?? '');
            $('#PPH').val(item.PPH ?? '');
            $('#jumlah_pembayaran').val(item.jumlah_pembayaran ?? '');
            $('#tanggal_pembayaran').val(item.tanggal_pembayaran ?? '');
            $('#biaya_admin').val(item.biaya_admin ?? '');

            if (['Pesawat', 'Kereta', 'Bus', 'Mobil', 'Travel', 'Lainnya'].includes(item.jenis_transport)) {
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

            $('#total_piutang_display').text(item.total_piutang ?? '-');

            calculateTotal();
            calculateTotalPenjualanSales();

            let modal = new bootstrap.Modal(document.getElementById('updateModal'));
            modal.show();
        });

        $('#formUpdate').submit(function(e) {
            e.preventDefault();
            let id = $('#update_id').val();
            let jenisTransport = $('#transportasi_select').val() === 'Lainnya' ? $('#transportasi_manual').val() :
                $('#transportasi_select').val();
            let $btn = $(this).find('button[type="submit"]');

            $.ajax({
                url: `/office/approval-pendapatan/update/${id}`,
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
                    PPN: $('#PPN').val(),
                    PPH: $('#PPH').val(),
                    jumlah_pembayaran: $('#jumlah_pembayaran').val(),
                    tanggal_pembayaran: $('#tanggal_pembayaran').val(),
                    biaya_admin: $('#biaya_admin').val(),
                    materi: $('#materi').val(),
                    perusahaan: $('#perusahaan').val(),
                    tanggal_mulai: $('#tanggal_mulai').val(),
                    tanggal_selesai: $('#tanggal_selesai').val(),
                },
                beforeSend: function() {
                    $btn.prop('disabled', true).html(
                        '<span class="spinner-border spinner-border-sm me-2"></span>Menyimpan...');
                },
                success: function(response) {
                    if (response.success) {
                        $('#updateModal').modal('hide');
                        loadTable();
                        showAlert('success', 'Data berhasil diupdate!');
                    } else {
                        showAlert('danger', response.message || 'Gagal menyimpan data.');
                    }
                },
                error: function(xhr) {
                    let msg = xhr.responseJSON?.message || 'Gagal menyimpan data. Silakan coba lagi.';
                    showAlert('danger', msg);
                },
                complete: function() {
                    $btn.prop('disabled', false).html('<i class="bi bi-save me-2"></i>Simpan');
                }
            });
        });

        function showAlert(type, message) {
            let alertHtml = `
                <div class="position-fixed top-0 end-0 p-3" style="z-index: 9999">
                    <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                        ${escapeHtml(message)}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                </div>
            `;
            $('body').append(alertHtml);
            setTimeout(() => $('.alert').alert('close'), 3000);
        }

        function escapeHtml(text) {
            if (!text) return '';
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return String(text).replace(/[&<>"']/g, m => map[m]);
        }
    </script>

    <style>
        .cursor-pointer {
            cursor: pointer;
        }

        .form-control-lg,
        .form-select-lg {
            font-size: 0.95rem;
        }

        .card-header {
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }

        .bg-gradient {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        }

        .sticky-top {
            z-index: 1020;
        }

        .badge {
            font-weight: 500;
        }

        .form-label {
            margin-bottom: 0.35rem;
        }

        .table th {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .table td {
            font-size: 0.8rem;
        }

        @media (max-width: 1400px) {
            .table-responsive {
                font-size: 0.75rem;
            }

            .table th,
            .table td {
                padding: 0.4rem 0.5rem;
            }
        }

        @media (max-width: 991px) {
            .sticky-top {
                position: static;
            }
        }

        .table-info td {
            background-color: #cff4fc !important;
            color: #055160;
            border-top: 2px solid #9eeaf9;
        }

        .table-dark td {
            background-color: #212529 !important;
            color: #fff;
            border-top: 3px double #495057;
            font-size: 0.85rem;
        }

        .table-info .text-success,
        .table-dark .text-success {
            color: #198754 !important;
            font-weight: 700;
        }

        .table-info .text-primary,
        .table-dark .text-primary {
            color: #0d6efd !important;
            font-weight: 700;
        }
    </style>
@endsection
