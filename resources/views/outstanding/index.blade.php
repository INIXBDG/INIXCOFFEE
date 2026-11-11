@extends('layouts.app')

@section('content')
    @php
        $tabKeys = ['hutang', 'pa', 'lunas'];
        $activeTab = in_array(request('tab'), $tabKeys) ? request('tab') : 'hutang';

        $reqYear = (int) request('tahun', date('Y'));
        $reqMonth = preg_match('/^\d{4}-\d{2}$/', (string) request('bulan')) ? request('bulan') : date('Y-m');

        $yearHutang = $activeTab === 'hutang' ? $reqYear : (int) date('Y');
        $yearLunas = $activeTab === 'lunas' ? $reqYear : (int) date('Y');
        $monthPA = $activeTab === 'pa' ? $reqMonth : date('Y-m');
    @endphp

    <div class="container-fluid">
        {{-- ================= MODAL PIC PENAGIHAN ================= --}}
        <div class="modal fade" id="modalPicPenagihan" tabindex="-1" aria-labelledby="modalPicPenagihanLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form id="formPicPenagihan" method="POST" action="{{ url('/office/pic/penagihan/store') }}">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title text-dark" id="modalPicPenagihanLabel">Input PIC Penagihan</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="id_rkm" id="input_id_rkm">
                            <input type="hidden" name="perusahaan_id" id="input_perusahaan_id">

                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Nama Perusahaan</label>
                                <input type="text" id="display_perusahaan" class="form-control bg-light" readonly>
                            </div>
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Nama Materi</label>
                                <input type="text" id="display_materi" class="form-control bg-light" readonly>
                            </div>
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Periode Pelatihan</label>
                                <input type="text" id="display_periode" class="form-control bg-light" readonly>
                            </div>
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Alamat Penagihan</label>
                                <textarea name="alamat" id="input_alamat" class="form-control" rows="2"></textarea>
                            </div>
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Kategori</label>
                                <input type="text" name="category" id="input_category" class="form-control">
                            </div>
                            <hr class="my-3">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Nama PIC</label>
                                <input type="text" name="pic" id="input_pic" class="form-control"
                                    placeholder="Input manual" required>
                            </div>
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Telepon PIC</label>
                                <input type="text" name="telepon" id="input_telepon" class="form-control"
                                    placeholder="Input manual" required>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary">Simpan Data</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-md-12">
                {{-- ================= TOOLBAR ATAS ================= --}}
                <div class="d-flex justify-content-end">
                    @can('Create Outstanding')
                        <a href="{{ route('outstanding.create') }}" class="btn btn-md click-primary mx-2"
                            data-toggle="tooltip" data-placement="top" title="Tambah Outstanding"><img
                                src="{{ asset('icon/plus.svg') }}" class="" width="30px"> List Outstanding</a>
                        <button type="button" class="btn click-primary dropdown-toggle dropdown-toggle-split mx-4"
                            data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <span class="sr-only">Singkron Data</span>
                        </button>
                        <div class="dropdown-menu">
                            <a class="dropdown-item" href="{{ route('outstanding.singkronDataOutstanding') }}">Singkron Data
                                Minggu Ini</a>
                            <a class="dropdown-item" href="#" data-bs-toggle="modal"
                                data-bs-target="#modalSinkron">Singkron Data</a>
                        </div>

                        <div class="btn-group">
                            <button class="btn btn-success dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="fas fa-file-export me-1"></i> Export Laporan
                            </button>
                            <ul class="dropdown-menu">
                                <li>
                                    <a class="dropdown-item" href="#" data-bs-toggle="modal"
                                        data-bs-target="#modalExport">
                                        <i class="fas fa-cog me-2"></i> Export dengan Filter
                                    </a>
                                </li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('outstanding.export.excel') }}">
                                        <i class="fas fa-file-excel text-success me-2"></i> Export Excel
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('outstanding.export.pdf') }}">
                                        <i class="fas fa-file-pdf text-danger me-2"></i> Export PDF
                                    </a>
                                </li>
                            </ul>
                        </div>
                    @endcan
                </div>

                {{-- ================= MODAL EXPORT ================= --}}
                <div class="modal fade" id="modalExport" tabindex="-1">
                    <div class="modal-dialog modal-dialog-centered modal-lg">
                        <div class="modal-content">
                            <form id="formExport" method="GET">
                                <div class="modal-header text-white">
                                    <h5 class="modal-title text-dark"><i class="fas fa-filter me-2"></i>Filter Export Laporan
                                    </h5>
                                    <button type="button" class="btn-close btn-close-white"
                                        data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="alert alert-info py-2 mb-3">
                                        <i class="fas fa-info-circle me-1"></i> Pilih salah satu: Preset Waktu
                                        <strong>atau</strong> Tanggal Manual
                                    </div>

                                    <div class="row g-3 mb-3">
                                        <div class="col-md-4">
                                            <label class="form-label fw-bold">Filter Cepat</label>
                                            <select name="filter_preset" id="filterPreset" class="form-select">
                                                <option value="">-- Pilih Preset --</option>
                                                <option value="tahun">Per Tahun</option>
                                                <option value="bulan">Per Bulan</option>
                                                <option value="triwulan">Per Triwulan</option>
                                            </select>
                                        </div>

                                        <div class="col-md-4 preset-input" id="inputTahun" style="display:none;">
                                            <label class="form-label">Tahun</label>
                                            <select name="filter_year" class="form-select">
                                                @for ($y = date('Y'); $y >= date('Y') - 5; $y--)
                                                    <option value="{{ $y }}">{{ $y }}</option>
                                                @endfor
                                            </select>
                                        </div>

                                        <div class="col-md-4 preset-input" id="inputBulan" style="display:none;">
                                            <label class="form-label">Bulan</label>
                                            <select name="filter_month" class="form-select">
                                                @foreach (['01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April', '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus', '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'] as $k => $v)
                                                    <option value="{{ $k }}">{{ $v }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-md-4 preset-input" id="inputTriwulan" style="display:none;">
                                            <label class="form-label">Triwulan</label>
                                            <select name="filter_quarter" class="form-select">
                                                <option value="1">Triwulan 1 (Jan-Mar)</option>
                                                <option value="2">Triwulan 2 (Apr-Jun)</option>
                                                <option value="3">Triwulan 3 (Jul-Sep)</option>
                                                <option value="4">Triwulan 4 (Okt-Des)</option>
                                            </select>
                                        </div>
                                    </div>

                                    <hr class="my-3">

                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold">Tanggal Mulai (Manual)</label>
                                            <input type="date" name="start_date" id="startDate" class="form-control">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold">Tanggal Akhir (Manual)</label>
                                            <input type="date" name="end_date" id="endDate" class="form-control">
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary"
                                        data-bs-dismiss="modal">Batal</button>
                                    <button type="submit" class="btn btn-success"
                                        formaction="{{ route('outstanding.export.excel') }}">
                                        <i class="fas fa-file-excel me-1"></i> Export Excel
                                    </button>
                                    <button type="submit" class="btn btn-danger"
                                        formaction="{{ route('outstanding.export.pdf') }}">
                                        <i class="fas fa-file-pdf me-1"></i> Export PDF
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- ================= MODAL SINKRON ================= --}}
                <div class="modal fade" id="modalSinkron" tabindex="-1" role="dialog"
                    aria-labelledby="modalSinkronLabel" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <form action="{{ route('outstanding.singkronDataOutstanding') }}" method="GET">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="modalSinkronLabel">Pilih Tanggal Untuk Singkron
                                        Outstanding
                                    </h5>
                                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <div class="form-group mb-3">
                                        <label for="tanggal_awal">Tanggal Awal Minggu</label>
                                        <input type="date" class="form-control" id="tanggal_awal" name="tanggal_awal">
                                    </div>
                                    <div class="form-group mb-3">
                                        <label for="tanggal_akhir">Tanggal Akhir Minggu</label>
                                        <input type="date" class="form-control" id="tanggal_akhir" name="tanggal_akhir"
                                            readonly>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary"
                                        data-bs-dismiss="modal">Tutup</button>
                                    <button type="submit" class="btn btn-primary">Sinkron</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- ================= CARD UTAMA BERTAB ================= --}}
                <div class="card m-4 os-card">
                    <div class="card-body">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                            <h3 class="card-title my-1">{{ __('Outstanding') }}</h3>

                            <ul class="nav nav-pills os-tabs" id="outstandingTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link {{ $activeTab === 'hutang' ? 'active' : '' }}"
                                        id="tab-hutang" data-bs-toggle="tab" data-bs-target="#pane-hutang"
                                        data-tab="hutang" type="button" role="tab" aria-controls="pane-hutang"
                                        aria-selected="{{ $activeTab === 'hutang' ? 'true' : 'false' }}">
                                        Belum Lunas <span class="badge" id="count-hutang">–</span>
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link {{ $activeTab === 'pa' ? 'active' : '' }}" id="tab-pa"
                                        data-bs-toggle="tab" data-bs-target="#pane-pa" data-tab="pa" type="button"
                                        role="tab" aria-controls="pane-pa"
                                        aria-selected="{{ $activeTab === 'pa' ? 'true' : 'false' }}">
                                        Outstanding PA <span class="badge" id="count-pa">–</span>
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link {{ $activeTab === 'lunas' ? 'active' : '' }}"
                                        id="tab-lunas" data-bs-toggle="tab" data-bs-target="#pane-lunas"
                                        data-tab="lunas" type="button" role="tab" aria-controls="pane-lunas"
                                        aria-selected="{{ $activeTab === 'lunas' ? 'true' : 'false' }}">
                                        Lunas <span class="badge" id="count-lunas">–</span>
                                    </button>
                                </li>
                            </ul>
                        </div>

                        <div class="tab-content" id="outstandingTabsContent">

                            {{-- ---------- TAB: BELUM LUNAS ---------- --}}
                            <div class="tab-pane fade {{ $activeTab === 'hutang' ? 'show active' : '' }}"
                                id="pane-hutang" role="tabpanel" aria-labelledby="tab-hutang" tabindex="0">
                                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                                    <div class="os-legend">
                                        <span><i style="background:#872819"></i>Lewat tempo &gt; 180 hari</span>
                                        <span><i style="background:#07b0ff"></i>Lewat tempo &gt; 30 hari</span>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <label for="filterTahunHutang" class="me-2 mb-0 text-nowrap fw-bold">Filter
                                            Tahun:</label>
                                        <select id="filterTahunHutang" class="form-control form-control-sm w-auto">
                                            @for ($i = date('Y'); $i >= 2020; $i--)
                                                <option value="{{ $i }}"
                                                    {{ $i == $yearHutang ? 'selected' : '' }}>{{ $i }}</option>
                                            @endfor
                                        </select>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-striped w-100" id="table-hutang">
                                        <thead>
                                            <tr>
                                                <th rowspan="2">Perusahaan</th>
                                                <th rowspan="2">Materi</th>
                                                <th rowspan="2">Periode Pelatihan</th>
                                                <th rowspan="2">Net Sales</th>
                                                <th rowspan="2">PIC</th>
                                                <th rowspan="2">Sales</th>
                                                <th rowspan="2">Tenggat Waktu</th>
                                                <th rowspan="2">Status Pembayaran</th>
                                                <th colspan="8" class="text-center">Tracking</th>
                                                <th rowspan="2" style="width: 10%">No Resi</th>
                                                <th rowspan="2" style="width: 10%">Keterangan PIC</th>
                                                <th rowspan="2">Aksi</th>
                                            </tr>
                                            <tr>
                                                <th style="width: 10%">Invoice</th>
                                                <th style="width: 10%">Faktur Pajak</th>
                                                <th style="width: 10%">Dokumen Tambahan</th>
                                                <th style="width: 10%">Konfirmasi Pengiriman RPX</th>
                                                <th style="width: 10%">Konfirmasi No Resi</th>
                                                <th style="width: 10%">Status Pengiriman</th>
                                                <th style="width: 10%">Konfirmasi PIC</th>
                                                <th style="width: 10%">Pembayaran</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                        <tfoot>
                                            <tr>
                                                <th colspan="3" class="text-end">Total:</th>
                                                <th colspan="2" id="totalNetSalesHutang"></th>
                                                <th colspan="14"></th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>

                            {{-- ---------- TAB: OUTSTANDING PA ---------- --}}
                            <div class="tab-pane fade {{ $activeTab === 'pa' ? 'show active' : '' }}" id="pane-pa"
                                role="tabpanel" aria-labelledby="tab-pa" tabindex="0">
                                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                                    <div class="os-legend">
                                        <span><i style="background:#FAB12F"></i>Sudah lunas, menunggu perhitungan PA</span>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <label for="filterBulanPA" class="me-2 mb-0 text-nowrap fw-bold">Filter
                                            Bulan:</label>
                                        <input type="month" id="filterBulanPA"
                                            class="form-control form-control-sm w-auto" value="{{ $monthPA }}">
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-striped w-100" id="table-pa">
                                        <thead>
                                            <tr>
                                                <th>Perusahaan</th>
                                                <th>Materi</th>
                                                <th>Periode Pelatihan</th>
                                                <th>Net Sales</th>
                                                <th>PIC</th>
                                                <th>Sales</th>
                                                <th>Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                        <tfoot>
                                            <tr>
                                                <th colspan="3" class="text-end">Total:</th>
                                                <th colspan="4" id="totalNetSalesPA"></th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>

                            {{-- ---------- TAB: LUNAS ---------- --}}
                            <div class="tab-pane fade {{ $activeTab === 'lunas' ? 'show active' : '' }}"
                                id="pane-lunas" role="tabpanel" aria-labelledby="tab-lunas" tabindex="0">
                                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                                    <div class="os-legend">
                                        <span><i style="background:#FAB12F"></i>Dibayar 7 hari terakhir</span>
                                        <span><i style="background:#000B58"></i>Dibayar lebih lama</span>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <label for="filterTahunLunas" class="me-2 mb-0 text-nowrap fw-bold">Filter
                                            Tahun:</label>
                                        <select id="filterTahunLunas" class="form-control form-control-sm w-auto">
                                            @for ($i = date('Y'); $i >= 2020; $i--)
                                                <option value="{{ $i }}"
                                                    {{ $i == $yearLunas ? 'selected' : '' }}>{{ $i }}</option>
                                            @endfor
                                        </select>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-striped w-100" id="table-lunas">
                                        <thead>
                                            <tr>
                                                <th>Perusahaan</th>
                                                <th>Materi</th>
                                                <th>Periode Pelatihan</th>
                                                <th>Net Sales</th>
                                                <th>PIC</th>
                                                <th>Sales</th>
                                                <th>Tenggat Waktu</th>
                                                <th>Status Pembayaran</th>
                                                <th>Jumlah Pembayaran</th>
                                                <th>Tanggal Bayar</th>
                                                <th>Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                        <tfoot>
                                            <tr>
                                                <th colspan="3" class="text-end">Total:</th>
                                                <th colspan="2" id="totalNetSalesLunas"></th>
                                                <th colspan="3" class="text-end">Total minggu ini:</th>
                                                <th colspan="3" id="totalNetSalesThisWeek"></th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* ---------- Kartu & tab ---------- */
        .os-card {
            --os-accent: #000B58;
            border-radius: 16px;
        }

        /* ---------- Cegah konten melebihi card ---------- */
        .container-fluid>.row>.col-md-12,
        .os-card,
        .os-card .card-body,
        #outstandingTabsContent,
        #outstandingTabsContent .tab-pane {
            min-width: 0;
            max-width: 100%;
        }

        .os-card {
            overflow: hidden;
        }

        /* scroll horizontal ditangani DataTables (scrollX), bukan .table-responsive */
        .os-card .table-responsive {
            overflow: visible;
        }

        .os-card .dataTables_wrapper {
            width: 100%;
        }

        .os-card .dataTables_scrollBody {
            border-bottom: 1px solid #dee2e6;
        }

        .os-card table.dataTable thead th,
        .os-card table.dataTable tbody td {
            font-size: .875rem;
        }

        /* Perusahaan & Materi tidak menyempit, Periode cukup lebar */
        .os-card table.dataTable tbody td:nth-child(-n+2) {
            min-width: 180px;
        }

        .os-card table.dataTable tbody td:nth-child(3) {
            min-width: 170px;
        }

        @media (max-width: 768px) {
            .os-card {
                margin: .75rem !important;
            }

            .os-tabs {
                width: 100%;
                overflow-x: auto;
                flex-wrap: nowrap;
            }

            .os-tabs .nav-link {
                white-space: nowrap;
            }

            .dataTables_wrapper .dataTables_filter,
            .dataTables_wrapper .dataTables_length {
                text-align: left;
                float: none;
            }
        }

        .os-tabs {
            background: rgba(0, 11, 88, .06);
            padding: 4px;
            border-radius: 999px;
            gap: 2px;
        }

        .os-tabs .nav-link {
            border-radius: 999px;
            color: #3b4252;
            font-weight: 600;
            padding: .45rem 1.1rem;
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            transition: background-color .15s, color .15s;
        }

        .os-tabs .nav-link:hover:not(.active) {
            background: rgba(0, 11, 88, .08);
        }

        .os-tabs .nav-link.active {
            background: var(--os-accent);
            color: #fff;
        }

        .os-tabs .badge {
            background: rgba(0, 0, 0, .12);
            color: inherit;
            font-weight: 600;
            min-width: 1.6rem;
        }

        .os-tabs .nav-link.active .badge {
            background: rgba(255, 255, 255, .22);
        }

        /* ---------- Legenda ---------- */
        .os-legend {
            display: flex;
            flex-wrap: wrap;
            gap: .4rem 1.1rem;
            font-size: .82rem;
            color: #5b6270;
        }

        .os-legend i {
            display: inline-block;
            width: 11px;
            height: 11px;
            border-radius: 3px;
            margin-right: .4rem;
            vertical-align: -1px;
        }

        /* ---------- Warna baris ---------- */
        #outstandingTabsContent table.dataTable tbody tr.row-overdue-180>* {
            background-color: #872819 !important;
            color: #fff !important;
            box-shadow: none !important;
        }

        #outstandingTabsContent table.dataTable tbody tr.row-overdue-30>* {
            background-color: #07b0ff !important;
            color: #fff !important;
            box-shadow: none !important;
        }

        #outstandingTabsContent table.dataTable tbody tr.row-paid-recent>*,
        #outstandingTabsContent table.dataTable tbody tr.row-pa>* {
            background-color: #FAB12F !important;
            box-shadow: none !important;
        }

        #outstandingTabsContent table.dataTable tbody tr.row-pa>* {
            color: #fff !important;
        }

        #outstandingTabsContent table.dataTable tbody tr.row-paid-old>* {
            background-color: #000B58 !important;
            color: #fff !important;
            box-shadow: none !important;
        }

        #outstandingTabsContent table.dataTable tbody td {
            vertical-align: middle;
        }

        /* ---------- Checkbox tracking (dulu .container, bentrok dgn Bootstrap) ---------- */
        .trk {
            --trk-main: #323232;
            position: relative;
            display: inline-block;
            margin: 0;
            cursor: default;
        }

        .trk input {
            position: absolute;
            opacity: 0;
        }

        .trk .checkmark {
            width: 24px;
            height: 24px;
            position: relative;
            border: 2px solid var(--trk-main);
            border-radius: 5px;
            box-shadow: 3px 3px var(--trk-main);
            background-color: #ccc;
            transition: background-color .2s;
        }

        .trk input:checked~.checkmark {
            background-color: #2d8cf0;
        }

        .trk .checkmark::after {
            content: "";
            width: 6px;
            height: 12px;
            position: absolute;
            top: 2px;
            left: 7px;
            display: none;
            border: solid #fff;
            border-width: 0 2.5px 2.5px 0;
            transform: rotate(45deg);
        }

        .trk input:checked~.checkmark::after {
            display: block;
        }

        /* ---------- DataTables ---------- */
        .dataTables_wrapper .dataTables_processing {
            border-radius: 12px;
            box-shadow: 0 6px 24px rgba(0, 11, 88, .18);
            background: #fff;
            color: #000B58;
            font-weight: 600;
            padding: .9rem 0;
            z-index: 5;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.current,
        .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
            background: #000B58 !important;
            color: #fff !important;
            border: 1px solid #000B58 !important;
            border-radius: 8px;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button {
            border-radius: 8px !important;
        }

        .dataTables_wrapper .dataTables_filter input,
        .dataTables_wrapper .dataTables_length select {
            border-radius: 8px;
        }
    </style>

    @push('js')
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script type="text/javascript" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
        <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
        <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.17.1/moment-with-locales.min.js"></script>
        <script>
            // =====================================================
            // Helper umum
            // =====================================================
            function formatRupiah(angka, prefix) {
                if (angka === null || angka === undefined || angka === '') return '';
                var number_string = angka.toString().replace(/[^0-9.,]/g, ''),
                    split = number_string.split('.'),
                    sisa = split[0].length % 3,
                    rupiah = split[0].substr(0, sisa),
                    ribuan = split[0].substr(sisa).match(/\d{3}/gi);

                if (ribuan) {
                    var separator = sisa ? '.' : '';
                    rupiah += separator + ribuan.join('.');
                }

                rupiah = split[1] !== undefined ? rupiah + ',' + split[1] : rupiah;
                return prefix === undefined ? rupiah : (rupiah ? prefix + rupiah : '');
            }

            $(function() {
                moment.locale('id');
                $.fn.dataTable.ext.errMode = 'console';

                // -------------------------------------------------
                // Konfigurasi dari server
                // -------------------------------------------------
                const CAN_EDIT = @json(auth()->user()->can('Edit Outstanding'));
                const CAN_DELETE = @json(auth()->user()->can('Delete Outstanding'));
                const CSRF = @json(csrf_token());
                const BASE = @json(url('/outstanding'));
                const ICON_EDIT = @json(asset('icon/edit-warning.svg'));
                const ICON_TRASH = @json(asset('icon/trash-danger.svg'));

                const AJAX_URL = {
                    hutang: @json(route('getOutstandingHutang')),
                    pa: @json(route('getOutstandingPA')),
                    lunas: @json(route('getOutstandingLunas')),
                };

                const TAB_KEYS = ['hutang', 'pa', 'lunas'];
                const LENGTHS = [10, 25, 50, 100];
                const FILTER_EL = {
                    hutang: '#filterTahunHutang',
                    pa: '#filterBulanPA',
                    lunas: '#filterTahunLunas'
                };
                const FILTER_PARAM = {
                    hutang: 'tahun',
                    pa: 'bulan',
                    lunas: 'tahun'
                };

                // -------------------------------------------------
                // State per tab, dibaca dari URL: ?tab=lunas&page=3&tahun=2026&q=abc&len=25
                // -------------------------------------------------
                const params = new URLSearchParams(location.search);
                let activeTab = TAB_KEYS.includes(params.get('tab')) ? params.get('tab') : 'hutang';
                let fromPopState = false;

                const state = {};
                TAB_KEYS.forEach(function(k) {
                    state[k] = {
                        page: 1,
                        len: 10,
                        q: '',
                        filter: $(FILTER_EL[k]).val()
                    };
                });

                (function applyUrlToState() {
                    const s = state[activeTab];
                    const p = parseInt(params.get('page'), 10);
                    const l = parseInt(params.get('len'), 10);
                    s.page = p > 0 ? p : 1;
                    s.len = LENGTHS.includes(l) ? l : 10;
                    s.q = params.get('q') || '';
                })();

                function syncUrl(push) {
                    const s = state[activeTab];
                    const p = new URLSearchParams();
                    p.set('tab', activeTab);
                    if (s.page > 1) p.set('page', s.page);
                    if (s.filter) p.set(FILTER_PARAM[activeTab], s.filter);
                    if (s.q) p.set('q', s.q);
                    if (s.len !== 10) p.set('len', s.len);
                    const url = location.pathname + '?' + p.toString();
                    if (url === location.pathname + location.search) return;
                    history[push ? 'pushState' : 'replaceState'](null, '', url);
                }

                // -------------------------------------------------
                // Render helpers
                // -------------------------------------------------
                const esc = (s) => $('<div>').text(s === null || s === undefined ? '' : s).html();
                const fmtDate = (d) => d ? moment(d).format('LL') : '-';

                const periodeText = (rkm) => (rkm && rkm.tanggal_awal && rkm.tanggal_akhir) ?
                    fmtDate(rkm.tanggal_awal) + ' s/d ' + fmtDate(rkm.tanggal_akhir) : '-';

                const colText = (path) => ({
                    data: path,
                    defaultContent: '-',
                    render: (d) => d ? esc(d) : '-'
                });

                const colMoney = (path) => ({
                    data: path,
                    defaultContent: '-',
                    render: (d, type) => type === 'display' ?
                        (d ? formatRupiah(d, 'Rp. ') : '-') : (parseFloat(d) || 0)
                });

                const colDate = (path) => ({
                    data: path,
                    defaultContent: '-',
                    render: (d, type) => (type === 'display' || type === 'filter') ? fmtDate(d) : (d || '')
                });

                const colPeriode = () => ({
                    data: 'rkm.tanggal_awal',
                    defaultContent: '-',
                    render: (d, type, row) => type === 'sort' || type === 'type' ?
                        (d || '') : periodeText(row.rkm)
                });

                const colStatus = () => ({
                    data: 'status_pembayaran',
                    defaultContent: '-',
                    render: (d, type) => {
                        if (type !== 'display') return d;
                        return String(d) === '1' ?
                            '<span class="badge bg-success">Sudah</span>' :
                            '<span class="badge bg-warning text-dark">Belum</span>';
                    }
                });

                const colTrack = (field) => ({
                    data: 'tracking_outstanding.' + field,
                    defaultContent: '',
                    orderable: false,
                    className: 'text-center',
                    render: (d, type) => {
                        const on = Number(d) === 1;
                        if (type !== 'display') return on ? 1 : 0;
                        return '<label class="trk"><input disabled type="checkbox" ' + (on ? 'checked' : '') +
                            '><div class="checkmark"></div></label>';
                    }
                });

                // Menu aksi. `extra` = item tambahan (hanya tab Lunas)
                function actionMenu(row, extra) {
                    if (!CAN_EDIT && !CAN_DELETE) {
                        return '<button class="btn btn-sm dropdown-toggle disabled text-white" type="button" disabled>Actions</button>';
                    }
                    let h = '<div class="dropdown">' +
                        '<button class="btn btn-sm dropdown-toggle text-white" type="button" data-bs-toggle="dropdown" aria-expanded="false">Actions</button>' +
                        '<div class="dropdown-menu">';

                    if (extra) h += extra;

                    if (CAN_EDIT) {
                        h += '<a class="dropdown-item js-edit" href="' + BASE + '/' + row.id + '/edit">' +
                            '<img src="' + ICON_EDIT + '"> Edit</a>';
                    }
                    if (CAN_DELETE) {
                        h += '<form method="POST" action="' + BASE + '/' + row.id + '" ' +
                            'onsubmit="this.return_url.value = location.href; return confirm(\'Apakah Anda yakin?\');">' +
                            '<input type="hidden" name="_token" value="' + CSRF + '">' +
                            '<input type="hidden" name="_method" value="DELETE">' +
                            '<input type="hidden" name="return_url" value="">' +
                            '<button type="submit" class="dropdown-item"><img src="' + ICON_TRASH + '"> Hapus</button>' +
                            '</form>';
                    }
                    return h + '</div></div>';
                }

                // Edit: bawa URL saat ini supaya setelah simpan kembali ke tab & halaman yang sama
                $(document).on('click', 'a.js-edit', function(e) {
                    e.preventDefault();
                    location.href = this.href + '?return=' + encodeURIComponent(location.href);
                });

                const LANG = {
                    processing: '<span class="spinner-border spinner-border-sm me-2"></span>Memuat data...',
                    search: 'Cari:',
                    lengthMenu: 'Tampilkan _MENU_ data',
                    info: 'Menampilkan _START_–_END_ dari _TOTAL_ data',
                    infoEmpty: 'Tidak ada data',
                    infoFiltered: '(disaring dari _MAX_ data)',
                    zeroRecords: 'Data tidak ditemukan',
                    emptyTable: 'Belum ada data untuk filter ini',
                    paginate: {
                        first: 'Awal',
                        last: 'Akhir',
                        next: 'Berikutnya',
                        previous: 'Sebelumnya'
                    }
                };

                // -------------------------------------------------
                // Konfigurasi kolom tiap tab
                // -------------------------------------------------
                const configs = {
                    hutang: function() {
                        return {
                            columns: [
                                colText('rkm.perusahaan.nama_perusahaan'),
                                colText('rkm.materi.nama_materi'),
                                colPeriode(),
                                colMoney('net_sales'),
                                colText('pic'),
                                colText('sales_key'),
                                colDate('due_date'),
                                colStatus(),
                                colTrack('invoice'),
                                colTrack('faktur_pajak'),
                                colTrack('dokumen_tambahan'),
                                colTrack('konfir_cs'),
                                colTrack('tracking_dokumen'),
                                colTrack('no_resi'),
                                colTrack('konfir_pic'),
                                colTrack('pembayaran'),
                                colText('tracking_outstanding.status_resi'),
                                colText('tracking_outstanding.status_pic'),
                                {
                                    data: null,
                                    orderable: false,
                                    render: (d, t, row) => actionMenu(row)
                                }
                            ],
                            order: [
                                [6, 'desc']
                            ],
                            footerCallback: function() {
                                let total = 0;
                                this.api().rows({
                                    search: 'applied'
                                }).data().each(function(r) {
                                    total += parseFloat(r.net_sales) || 0;
                                });
                                $('#totalNetSalesHutang').html(formatRupiah(total, 'Rp. '));
                            },
                            createdRow: function(row, data) {
                                if (!data.due_date) return;
                                const over = moment().startOf('day').diff(moment(data.due_date).startOf('day'),
                                    'days');
                                if (over > 180) $(row).addClass('row-overdue-180');
                                else if (over > 30) $(row).addClass('row-overdue-30');
                            }
                        };
                    },

                    pa: function() {
                        return {
                            columns: [
                                colText('perusahaan.nama_perusahaan'),
                                colText('materi.nama_materi'),
                                {
                                    data: 'tanggal_awal',
                                    defaultContent: '-',
                                    render: (d, type, row) => type === 'sort' || type === 'type' ?
                                        (d || '') : periodeText(row)
                                },
                                colMoney('outstanding.net_sales'),
                                colText('outstanding.pic'),
                                colText('sales_key'),
                                {
                                    data: 'id',
                                    orderable: false,
                                    render: (id) => '<a href="' + BASE + '/' + id +
                                        '/detail" class="btn btn-sm btn-info text-white">Detail</a>'
                                }
                            ],
                            order: [],
                            footerCallback: function() {
                                let total = 0;
                                this.api().rows({
                                    search: 'applied'
                                }).data().each(function(r) {
                                    if (r.outstanding) total += parseFloat(r.outstanding.net_sales) || 0;
                                });
                                $('#totalNetSalesPA').html(formatRupiah(total, 'Rp. '));
                            },
                            createdRow: (row) => $(row).addClass('row-pa')
                        };
                    },

                    lunas: function() {
                        return {
                            columns: [
                                colText('rkm.perusahaan.nama_perusahaan'),
                                colText('rkm.materi.nama_materi'),
                                colPeriode(),
                                colMoney('net_sales'),
                                colText('pic'),
                                colText('sales_key'),
                                colDate('due_date'),
                                colStatus(),
                                colMoney('jumlah_pembayaran'),
                                colDate('tanggal_bayar'),
                                {
                                    data: null,
                                    orderable: false,
                                    render: function(d, t, row) {
                                        let extra = '';
                                        if (CAN_EDIT || CAN_DELETE) {
                                            extra += '<a class="dropdown-item" href="/download/dokumen/' + row.id +
                                                '" target="_blank">' +
                                                '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-1"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg> Download Dokumen</a>';
                                            if (!row.has_pic_penagihan) {
                                                extra += '<div class="dropdown-divider"></div>' +
                                                    '<a class="dropdown-item btn-pic-penagihan" href="#">' +
                                                    '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-1"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg> Input PIC Penagihan</a>';
                                            }
                                            extra += '<div class="dropdown-divider"></div>';
                                        }
                                        return actionMenu(row, extra);
                                    }
                                }
                            ],
                            order: [
                                [9, 'desc']
                            ],
                            footerCallback: function() {
                                let total = 0,
                                    week = 0;
                                const oneWeekAgo = moment().subtract(7, 'days');
                                this.api().rows({
                                    search: 'applied'
                                }).data().each(function(r) {
                                    const n = parseFloat(r.net_sales) || 0;
                                    total += n;
                                    if (String(r.status_pembayaran) === '1' && r.tanggal_bayar &&
                                        moment(r.tanggal_bayar).isAfter(oneWeekAgo)) {
                                        week += n;
                                    }
                                });
                                $('#totalNetSalesLunas').html(formatRupiah(total, 'Rp. '));
                                $('#totalNetSalesThisWeek').html(formatRupiah(week, 'Rp. '));
                            },
                            createdRow: function(row, data) {
                                if (String(data.status_pembayaran) !== '1') return;
                                if (data.tanggal_bayar && moment(data.tanggal_bayar).isAfter(moment().subtract(
                                        7, 'days'))) {
                                    $(row).addClass('row-paid-recent');
                                } else {
                                    $(row).addClass('row-paid-old');
                                }
                            }
                        };
                    }
                };

                // -------------------------------------------------
                // Buat tabel (lazy: hanya saat tab dibuka pertama kali)
                // -------------------------------------------------
                const tables = {};

                function createTable(key) {
                    if (tables[key]) return tables[key];

                    const s = state[key];
                    let ready = false;

                    const options = Object.assign({
                        processing: true,
                        autoWidth: false,
                        scrollX: true,
                        lengthMenu: LENGTHS,
                        pageLength: s.len,
                        displayStart: (s.page - 1) * s.len,
                        search: {
                            search: s.q
                        },
                        language: LANG,
                        ajax: {
                            url: AJAX_URL[key],
                            type: 'GET',
                            data: function(d) {
                                d[FILTER_PARAM[key]] = $(FILTER_EL[key]).val();
                            }
                        },
                        initComplete: function() {
                            // Jaga-jaga kalau displayStart tidak terbawa saat data ajax tiba
                            const api = this.api();
                            const info = api.page.info();
                            const want = s.page - 1;
                            if (want > 0 && want < info.pages && info.page !== want) {
                                api.page(want).draw('page');
                            }
                            ready = true;
                        }
                    }, configs[key]());

                    const dt = $('#table-' + key).DataTable(options);
                    tables[key] = dt;

                    dt.on('xhr.dt', function(e, settings, json) {
                        $('#count-' + key).text(json && json.data ? json.data.length : 0);
                    });

                    // Setiap redraw (ganti halaman, cari, ubah jumlah baris, reload filter) → update URL
                    dt.on('draw.dt', function() {
                        if (!ready) return;
                        const info = dt.page.info();
                        s.page = info.page + 1;
                        s.len = info.length;
                        s.q = dt.search();
                        s.filter = $(FILTER_EL[key]).val();
                        if (key === activeTab) syncUrl(false);
                    });

                    return dt;
                }

                // Filter tahun / bulan → reload data (balik ke halaman 1)
                TAB_KEYS.forEach(function(key) {
                    $(FILTER_EL[key]).on('change', function() {
                        state[key].filter = this.value;
                        if (tables[key]) tables[key].ajax.reload(null, true);
                    });
                });

                // -------------------------------------------------
                // Pergantian tab
                // -------------------------------------------------
                document.querySelectorAll('#outstandingTabs button[data-bs-toggle="tab"]').forEach(function(btn) {
                    btn.addEventListener('shown.bs.tab', function() {
                        activeTab = this.dataset.tab;
                        const existed = !!tables[activeTab];
                        createTable(activeTab);
                        if (existed) {
                            tables[activeTab].columns.adjust();
                            syncUrl(!fromPopState);
                        }
                        fromPopState = false;
                    });
                });

                // Tombol back/forward browser
                window.addEventListener('popstate', function() {
                    const p = new URLSearchParams(location.search);
                    const key = TAB_KEYS.includes(p.get('tab')) ? p.get('tab') : 'hutang';
                    if (key === activeTab) return;
                    fromPopState = true;
                    bootstrap.Tab.getOrCreateInstance(document.querySelector('[data-tab="' + key + '"]')).show();
                });

                // Tab pertama
                createTable(activeTab);

                // -------------------------------------------------
                // Modal PIC Penagihan (tab Lunas)
                // -------------------------------------------------
                $('#table-lunas tbody').on('click', '.btn-pic-penagihan', function(e) {
                    e.preventDefault();
                    const row = tables.lunas.row($(this).closest('tr')).data();
                    if (!row) return;

                    $('#input_id_rkm').val(row.id_rkm || '');
                    const rkm = row.rkm || {};
                    $('#display_materi').val(rkm.materi ? rkm.materi.nama_materi : '-');
                    $('#display_periode').val(periodeText(rkm));

                    if (rkm.perusahaan) {
                        $('#input_perusahaan_id').val(rkm.perusahaan.id || '');
                        $('#display_perusahaan').val(rkm.perusahaan.nama_perusahaan || '-');
                        $('#input_alamat').val(rkm.perusahaan.alamat || '');
                        $('#input_category').val(rkm.perusahaan.kategori_perusahaan || '');
                    } else {
                        $('#input_perusahaan_id').val('');
                        $('#display_perusahaan').val('-');
                        $('#input_alamat').val('');
                        $('#input_category').val('');
                    }

                    $('#input_pic').val(row.pic || '');
                    $('#input_telepon').val('');
                    $('#modalPicPenagihan').modal('show');
                });

                // -------------------------------------------------
                // Modal Sinkron: tanggal akhir = akhir minggu (Minggu)
                // -------------------------------------------------
                $('#tanggal_awal').on('change', function() {
                    $('#tanggal_akhir').val(this.value ?
                        moment(this.value, 'YYYY-MM-DD').endOf('isoWeek').format('YYYY-MM-DD') : '');
                });

                // -------------------------------------------------
                // Modal Export: preset vs tanggal manual
                // -------------------------------------------------
                const preset = document.getElementById('filterPreset');
                const presetInputs = document.querySelectorAll('.preset-input');
                const startDate = document.getElementById('startDate');
                const endDate = document.getElementById('endDate');
                const presetMap = {
                    tahun: 'inputTahun',
                    bulan: 'inputBulan',
                    triwulan: 'inputTriwulan'
                };

                if (preset) {
                    preset.addEventListener('change', function() {
                        presetInputs.forEach(el => el.style.display = 'none');
                        if (presetMap[this.value]) {
                            document.getElementById(presetMap[this.value]).style.display = 'block';
                            startDate.value = '';
                            endDate.value = '';
                        }
                    });
                    [startDate, endDate].forEach(function(el) {
                        el.addEventListener('change', function() {
                            if (this.value) {
                                preset.value = '';
                                presetInputs.forEach(x => x.style.display = 'none');
                            }
                        });
                    });
                }
            });
        </script>
    @endpush
@endsection