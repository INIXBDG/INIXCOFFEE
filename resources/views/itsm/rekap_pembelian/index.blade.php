@extends('layouts.app')

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">

<style>
    #sectionGrafikContainer {
        display: block !important;
        width: auto !important;
    }
    #sectionGrafikContainer .card-header {
        display: flex !important;
        flex-direction: row !important;
        justify-content: space-between !important;
        align-items: center !important;
        width: 100% !important;
        border-top-left-radius: 20px;
        border-top-right-radius: 20px;
    }
    #sectionGrafikContainer .collapse,
    #sectionGrafikContainer .card-body {
        width: 100% !important;
    }
    #sectionGrafikContainer .card {
        display: block !important;
        width: 100% !important;
        background-color: #ffffff !important;
        border-radius: 12px !important;
        box-shadow: none !important;
    }
    #analyticsSubTabs .nav-link {
        border-radius: 8px !important;
        color: #495057 !important;
        background-color: #f8f9fa !important;
        border: 1px solid #dee2e6 !important;
        font-weight: 600;
        transition: all 0.2s ease-in-out;
    }
    #analyticsSubTabs .nav-link:hover {
        background-color: #e9ecef !important;
        color: #0d6efd !important;
    }
    #analyticsSubTabs .nav-link.active {
        background-color: #0d6efd !important;
        color: #ffffff !important;
        border-color: #0d6efd !important;
        box-shadow: 0 2px 5px rgba(13, 110, 253, 0.3) !important;
    }
    #analyticsSubTabs .nav-link.active img {
        filter: brightness(0) invert(1);
    }
</style>

@section('content')
<div class="container-fluid">
    {{-- Filter Tahun & Bulan --}}
    <div class="card m-4">
        <div class="card-body">
            <form method="GET" action="{{ route('itsm.rekap-pembelian.index') }}" class="d-flex flex-wrap justify-content-center align-items-center gap-3">
                <div class="d-flex align-items-center gap-2">
                    <label for="tahun" class="form-label mb-0">Tahun</label>
                    <select name="year" id="tahun" class="form-select" style="width:130px" onchange="this.form.submit()">
                        <option disabled>Pilih Tahun</option>
                        @foreach ($availableYears as $year)
                            <option value="{{ $year }}" {{ $selectedYear == $year ? 'selected' : '' }}>
                                {{ $year }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="d-flex align-items-center gap-2">
                    <label for="bulan" class="form-label mb-0">Bulan</label>
                    <select name="month" id="bulan" class="form-select" style="width:150px" onchange="this.form.submit()">
                        <option value="Semua" {{ $selectedMonth == 'Semua' ? 'selected' : '' }}>Semua Bulan</option>
                        @php
                            $nama_bulan = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                        @endphp
                        @foreach ($nama_bulan as $idx => $mName)
                            <option value="{{ $idx + 1 }}" {{ $selectedMonth == (string)($idx + 1) ? 'selected' : '' }}>
                                {{ $mName }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <button type="submit" class="btn click-primary" style="height:38px">Cari Data</button>
                @if ($selectedMonth !== 'Semua' || $selectedYear != now()->year)
                    <a href="{{ route('itsm.rekap-pembelian.index') }}" class="btn btn-secondary" style="height:38px; display:inline-flex; align-items:center;">Reset</a>
                @endif
            </form>
        </div>
    </div>

    {{-- SECTION GRAFIK & ANALITIK (DAPAT DI-HIDE / COLLAPSIBLE DENGAN TAB PILIHAN USER) --}}
    <div class="card m-4 mt-0 border-0 shadow-sm" id="sectionGrafikContainer" style="display: block !important; width: auto;">
        <div class="card-header bg-white py-3 px-4 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-3" style="width: 100%;">
            <div class="d-flex align-items-center gap-2">
                <img src="{{ asset('icon/chart-bar.svg') }}" width="24px">
                <div>
                    <h5 class="fw-bold text-dark mb-0">Dashboard Analitik Pembelian (Tahun {{ $selectedYear }})</h5>
                    <small class="text-muted">Visualisasi data pengeluaran dan barang yang diajukan</small>
                </div>
            </div>

            <div class="d-flex flex-wrap align-items-center gap-2">
                {{-- Navigasi Button Tab Grafik di Atas --}}
                <ul class="nav nav-pills gap-2" id="analyticsSubTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active btn-sm py-1 px-3" id="subtab-overview-btn" data-bs-toggle="pill" data-bs-target="#subtab-overview" type="button" role="tab" aria-selected="true">
                            <img src="{{ asset('icon/trending-up.svg') }}" width="16px" class="me-1"> Ringkasan &amp; Tren
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link btn-sm py-1 px-3" id="subtab-pribadi-btn" data-bs-toggle="pill" data-bs-target="#subtab-pribadi" type="button" role="tab" aria-selected="false">
                            <img src="{{ asset('icon/user.svg') }}" width="16px" class="me-1"> Operasional Pribadi
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link btn-sm py-1 px-3" id="subtab-divisi-btn" data-bs-toggle="pill" data-bs-target="#subtab-divisi" type="button" role="tab" aria-selected="false">
                            <img src="{{ asset('icon/users.svg') }}" width="16px" class="me-1"> Elektronik Divisi ITSM
                        </button>
                    </li>
                </ul>

                {{-- Tombol Hide / Show Grafik --}}
                <button class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-1 ms-lg-2" type="button" data-bs-toggle="collapse" data-bs-target="#collapseChartSection" aria-expanded="true" aria-controls="collapseChartSection" id="btnToggleChart" title="Sembunyikan / Tampilkan Grafik">
                    <span id="textToggleChart">Sembunyikan Grafik</span>
                    <svg id="iconToggleChart" xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16" style="transition: transform 0.25s ease;">
                        <path fill-rule="evenodd" d="M7.646 4.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1-.708.708L8 5.707l-5.646 5.647a.5.5 0 0 1-.708-.708z"/>
                    </svg>
                </button>
            </div>
        </div>

        <div class="collapse show" id="collapseChartSection" style="width: 100%;">
            <div class="card-body p-4" style="width: 100%;">
                <div class="tab-content" id="analyticsSubTabContent">
                    {{-- SUB-TAB 1: OVERVIEW / GABUNGAN --}}
                    <div class="tab-pane fade show active" id="subtab-overview" role="tabpanel">
                        <div class="row g-3 mb-4">
                            {{-- Summary Highlights --}}
                            <div class="col-md-4">
                                <div class="p-3 bg-light rounded-3 border-start border-4 border-info">
                                    <small class="text-muted text-uppercase fw-semibold">Operasional Pribadi</small>
                                    <h5 class="fw-bold text-dark mt-1 mb-0">Rp {{ number_format($totalPribadiNominal, 0, ',', '.') }}</h5>
                                    <small class="text-muted">{{ $totalPribadiCount }} Pengajuan</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="p-3 bg-light rounded-3 border-start border-4 border-primary">
                                    <small class="text-muted text-uppercase fw-semibold">Elektronik Divisi</small>
                                    <h5 class="fw-bold text-dark mt-1 mb-0">Rp {{ number_format($totalDivisiNominal, 0, ',', '.') }}</h5>
                                    <small class="text-muted">{{ $totalDivisiCount }} Pengajuan</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="p-3 bg-light rounded-3 border-start border-4 border-success">
                                    <small class="text-muted text-uppercase fw-semibold">Total Anggaran (Tahun {{ $selectedYear }})</small>
                                    <h5 class="fw-bold text-success mt-1 mb-0">Rp {{ number_format($grandTotalNominal, 0, ',', '.') }}</h5>
                                    <small class="text-muted">{{ $grandTotalCount }} Total Pengajuan</small>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3">
                            {{-- Tren Pengeluaran Gabungan --}}
                            <div class="col-lg-8">
                                <div class="card h-100 border">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <h6 class="fw-bold mb-0 text-dark">
                                                <img src="{{ asset('icon/trending-up.svg') }}" width="18px" class="me-1"> Tren Pengeluaran Bulanan (Januari - Desember {{ $selectedYear }})
                                            </h6>
                                            <span class="badge bg-light text-muted border">Operasional vs Elektronik</span>
                                        </div>
                                        <div style="height: 290px; position: relative;">
                                            <canvas id="chartMonthlyTrend"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Proporsi Anggaran --}}
                            <div class="col-lg-4">
                                <div class="card h-100 border">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <h6 class="fw-bold mb-0 text-dark">
                                                <img src="{{ asset('icon/pie-chart.svg') }}" width="18px" class="me-1"> Proporsi Pembagian Biaya
                                            </h6>
                                            <span class="badge bg-light text-muted border">Persentase</span>
                                        </div>
                                        <div style="height: 290px; position: relative;" class="d-flex align-items-center justify-content-center">
                                            <canvas id="chartProporsi"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- SUB-TAB 2: ANALITIK OPERASIONAL PRIBADI --}}
                    <div class="tab-pane fade" id="subtab-pribadi" role="tabpanel">
                        <div class="row g-3">
                            {{-- Pribadi Top Qty --}}
                            <div class="col-lg-6">
                                <div class="card h-100 border">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <h6 class="fw-bold mb-0 text-dark">
                                                <img src="{{ asset('icon/box.svg') }}" width="18px" class="me-1"> Top 5 Barang Operasional Paling Banyak Dibeli
                                            </h6>
                                            <span class="badge bg-info-subtle text-info border border-info-subtle">Kuantitas (Unit)</span>
                                        </div>
                                        <div style="height: 270px; position: relative;">
                                            <canvas id="chartPribadiTopQty"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Pribadi Top Nominal --}}
                            <div class="col-lg-6">
                                <div class="card h-100 border">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <h6 class="fw-bold mb-0 text-dark">
                                                <img src="{{ asset('icon/wallet.svg') }}" width="18px" class="me-1"> Top 5 Biaya Operasional Terbesar
                                            </h6>
                                            <span class="badge bg-success-subtle text-success border border-success-subtle">Nominal (Rp)</span>
                                        </div>
                                        <div style="height: 270px; position: relative;">
                                            <canvas id="chartPribadiTopNominal"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Pribadi Tren Bulanan --}}
                            <div class="col-lg-8">
                                <div class="card h-100 border">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <h6 class="fw-bold mb-0 text-dark">
                                                <img src="{{ asset('icon/trending-up.svg') }}" width="18px" class="me-1"> Tren Pengeluaran Operasional Pribadi Bulanan
                                            </h6>
                                            <span class="badge bg-light text-muted border">Tahun {{ $selectedYear }}</span>
                                        </div>
                                        <div style="height: 270px; position: relative;">
                                            <canvas id="chartPribadiMonthly"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Pribadi Status Breakdown --}}
                            <div class="col-lg-4">
                                <div class="card h-100 border">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <h6 class="fw-bold mb-0 text-dark">
                                                <img src="{{ asset('icon/check-circle.svg') }}" width="18px" class="me-1"> Status Pengajuan Operasional
                                            </h6>
                                            <span class="badge bg-light text-muted border">Status</span>
                                        </div>
                                        <div style="height: 270px; position: relative;" class="d-flex align-items-center justify-content-center">
                                            <canvas id="chartPribadiStatus"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- SUB-TAB 3: ANALITIK ELEKTRONIK DIVISI ITSM --}}
                    <div class="tab-pane fade" id="subtab-divisi" role="tabpanel">
                        <div class="row g-3">
                            {{-- Divisi Top Qty --}}
                            <div class="col-lg-6">
                                <div class="card h-100 border">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <h6 class="fw-bold mb-0 text-dark">
                                                <img src="{{ asset('icon/box.svg') }}" width="18px" class="me-1"> Top 5 Barang Elektronik Paling Banyak Dibeli
                                            </h6>
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle">Kuantitas (Unit)</span>
                                        </div>
                                        <div style="height: 270px; position: relative;">
                                            <canvas id="chartDivisiTopQty"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Divisi Top Nominal --}}
                            <div class="col-lg-6">
                                <div class="card h-100 border">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <h6 class="fw-bold mb-0 text-dark">
                                                <img src="{{ asset('icon/wallet.svg') }}" width="18px" class="me-1"> Top 5 Pengeluaran Elektronik Terbesar
                                            </h6>
                                            <span class="badge bg-success-subtle text-success border border-success-subtle">Nominal (Rp)</span>
                                        </div>
                                        <div style="height: 270px; position: relative;">
                                            <canvas id="chartDivisiTopNominal"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Divisi Pengeluaran per Karyawan (Top Spender) --}}
                            <div class="col-lg-7">
                                <div class="card h-100 border">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <h6 class="fw-bold mb-0 text-dark">
                                                <img src="{{ asset('icon/users.svg') }}" width="18px" class="me-1"> Pengeluaran Elektronik per Anggota Tim ITSM
                                            </h6>
                                            <span class="badge bg-light text-muted border">Distribusi Pemohon</span>
                                        </div>
                                        <div style="height: 270px; position: relative;">
                                            <canvas id="chartDivisiByEmployee"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Divisi Status Breakdown --}}
                            <div class="col-lg-5">
                                <div class="card h-100 border">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <h6 class="fw-bold mb-0 text-dark">
                                                <img src="{{ asset('icon/check-circle.svg') }}" width="18px" class="me-1"> Status Pengajuan Elektronik Divisi
                                            </h6>
                                            <span class="badge bg-light text-muted border">Status</span>
                                        </div>
                                        <div style="height: 270px; position: relative;" class="d-flex align-items-center justify-content-center">
                                            <canvas id="chartDivisiStatus"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Indikator Baris Invoice --}}
    <div class="mx-4 mb-2 d-flex align-items-center gap-2">
        <div style="width:18px;height:18px;background-color:rgba(255,0,0,0.4);border-radius:4px;flex-shrink:0;"></div>
        <small class="text-muted">Baris merah = pengajuan belum disertai invoice</small>
    </div>

    {{-- Tab Navigation Utama --}}
    <div class="mx-4 mb-3">
        <ul class="nav nav-tabs" id="rekapTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active fw-bold" id="pribadi-tab" data-bs-toggle="tab" data-bs-target="#pribadi-pane" type="button" role="tab" aria-controls="pribadi-pane" aria-selected="true">
                    <img src="{{ asset('icon/user.svg') }}" width="20px" class="me-1"> Operasional Pribadi
                    <span class="badge bg-secondary ms-1">{{ $totalPribadiCount }}</span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-bold" id="divisi-tab" data-bs-toggle="tab" data-bs-target="#divisi-pane" type="button" role="tab" aria-controls="divisi-pane" aria-selected="false">
                    <img src="{{ asset('icon/users.svg') }}" width="20px" class="me-1"> Elektronik Divisi ITSM
                    <span class="badge bg-secondary ms-1">{{ $totalDivisiCount }}</span>
                </button>
            </li>
        </ul>
    </div>

    {{-- Tab Content --}}
    <div class="tab-content" id="rekapTabsContent">
        {{-- TAB 1: OPERASIONAL PRIBADI --}}
        <div class="tab-pane fade show active" id="pribadi-pane" role="tabpanel" aria-labelledby="pribadi-tab">
            <div class="card m-4 mt-0">
                <div class="card-body table-responsive">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h3 class="card-title my-0">{{ __('Data Rekap Pengajuan Operasional Pribadi') }}</h3>
                        <button type="button" class="btn btn-sm btn-outline-info" onclick="openAnalyticsSubTab('pribadi')">
                            <img src="{{ asset('icon/chart-bar.svg') }}" width="16px" class="me-1"> Lihat Grafik Operasional
                        </button>
                    </div>
                    <table class="table table-striped align-middle" id="tablePribadi" style="width:100%">
                        <thead>
                            <tr>
                                <th scope="col">Tanggal Pengajuan</th>
                                <th scope="col">Tipe</th>
                                <th scope="col">Status</th>
                                <th scope="col">Nama Barang</th>
                                <th scope="col" class="text-center">Total Item</th>
                                <th scope="col">Total Pengajuan</th>
                                <th scope="col" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($pengajuanPribadi as $row)
                                <tr @if(!$row->invoice) style="background-color: rgba(255, 0, 0, 0.25);" @endif>
                                    <td data-order="{{ $row->created_at ? $row->created_at->timestamp : 0 }}">
                                        <div>{{ $row->created_at ? $row->created_at->format('d/m/Y') : '-' }}</div>
                                        <small class="text-muted">{{ $row->created_at ? $row->created_at->format('H:i') : '' }}</small>
                                    </td>
                                    <td>{{ $row->tipe }}</td>
                                    <td>{{ $row->status_tracking }}</td>
                                    <td>{{ $row->nama_barang_list ?: '-' }}</td>
                                    <td class="text-center" data-order="{{ $row->total_item ?? 0 }}">{{ $row->total_item ?? 0 }}</td>
                                    <td class="fw-bold" data-order="{{ $row->total_nominal }}">
                                        Rp {{ number_format($row->total_nominal, 0, ',', '.') }}
                                    </td>
                                    <td class="text-center">
                                        <div class="dropdown">
                                            <button class="btn dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                Actions
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-end">
                                                <button type="button" class="dropdown-item" onclick="openDetailModal({{ $row->id }})">
                                                    <img src="{{ asset('icon/clipboard-primary.svg') }}" class="me-1"> Detail
                                                </button>
                                                @if($row->invoice)
                                                    <a class="dropdown-item" href="{{ asset('storage/' . $row->invoice) }}" target="_blank">
                                                        <img src="{{ asset('icon/file-text.svg') }}" class="me-1"> Lihat Invoice
                                                    </a>
                                                @endif
                                                @if($row->bukti)
                                                    <a class="dropdown-item" href="{{ asset('storage/' . $row->bukti) }}" target="_blank">
                                                        <img src="{{ asset('icon/file-text.svg') }}" class="me-1"> Lihat Bukti
                                                    </a>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        @if ($pengajuanPribadi->isNotEmpty())
                            <tfoot>
                                <tr class="fw-bold table-light">
                                    <th colspan="5" class="text-end">Total Nominal:</th>
                                    <th colspan="2" class="text-success">
                                        Rp {{ number_format($totalPribadiNominal, 0, ',', '.') }}
                                    </th>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>

        {{-- TAB 2: ELEKTRONIK DIVISI ITSM --}}
        <div class="tab-pane fade" id="divisi-pane" role="tabpanel" aria-labelledby="divisi-tab">
            <div class="card m-4 mt-0">
                <div class="card-body table-responsive">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h3 class="card-title my-0">{{ __('Data Rekap Pengajuan Elektronik Divisi ITSM') }}</h3>
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="openAnalyticsSubTab('divisi')">
                            <img src="{{ asset('icon/chart-bar.svg') }}" width="16px" class="me-1"> Lihat Grafik Divisi
                        </button>
                    </div>
                    <table class="table table-striped align-middle" id="tableDivisi" style="width:100%">
                        <thead>
                            <tr>
                                <th scope="col">Tanggal Pengajuan</th>
                                <th scope="col">Nama Karyawan</th>
                                <th scope="col">Divisi</th>
                                <th scope="col">Jabatan</th>
                                <th scope="col">Tipe</th>
                                <th scope="col">Status</th>
                                <th scope="col">Nama Barang</th>
                                <th scope="col" class="text-center">Total Item</th>
                                <th scope="col">Total Pengajuan</th>
                                <th scope="col" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($pengajuanDivisi as $row)
                                <tr @if(!$row->invoice) style="background-color: rgba(255, 0, 0, 0.25);" @endif>
                                    <td data-order="{{ $row->created_at ? $row->created_at->timestamp : 0 }}">
                                        <div>{{ $row->created_at ? $row->created_at->format('d/m/Y') : '-' }}</div>
                                        <small class="text-muted">{{ $row->created_at ? $row->created_at->format('H:i') : '' }}</small>
                                    </td>
                                    <td class="fw-semibold">{{ $row->karyawan->nama_lengkap ?? '-' }}</td>
                                    <td>{{ $row->karyawan->divisi ?? '-' }}</td>
                                    <td>{{ $row->karyawan->jabatan ?? '-' }}</td>
                                    <td>{{ $row->tipe }}</td>
                                    <td>{{ $row->status_tracking }}</td>
                                    <td>{{ $row->nama_barang_list ?: '-' }}</td>
                                    <td class="text-center" data-order="{{ $row->total_item ?? 0 }}">{{ $row->total_item ?? 0 }}</td>
                                    <td class="fw-bold" data-order="{{ $row->total_nominal }}">
                                        Rp {{ number_format($row->total_nominal, 0, ',', '.') }}
                                    </td>
                                    <td class="text-center">
                                        <div class="dropdown">
                                            <button class="btn dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                Actions
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-end">
                                                <button type="button" class="dropdown-item" onclick="openDetailModal({{ $row->id }})">
                                                    <img src="{{ asset('icon/clipboard-primary.svg') }}" class="me-1"> Detail
                                                </button>
                                                @if($row->invoice)
                                                    <a class="dropdown-item" href="{{ asset('storage/' . $row->invoice) }}" target="_blank">
                                                        <img src="{{ asset('icon/file-text.svg') }}" class="me-1"> Lihat Invoice
                                                    </a>
                                                @endif
                                                @if($row->bukti)
                                                    <a class="dropdown-item" href="{{ asset('storage/' . $row->bukti) }}" target="_blank">
                                                        <img src="{{ asset('icon/file-text.svg') }}" class="me-1"> Lihat Bukti
                                                    </a>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        @if ($pengajuanDivisi->isNotEmpty())
                            <tfoot>
                                <tr class="fw-bold table-light">
                                    <th colspan="8" class="text-end">Total Nominal:</th>
                                    <th colspan="2" class="text-success">
                                        Rp {{ number_format($totalDivisiNominal, 0, ',', '.') }}
                                    </th>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- MODAL DETAIL PENGAJUAN --}}
<div class="modal fade" id="detailModal" tabindex="-1" aria-labelledby="detailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="detailModalLabel">
                    <img src="{{ asset('icon/clipboard-primary.svg') }}" class="me-1" width="24px"> Detail Pengajuan Barang
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center py-4 text-muted" id="modalLoading">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2 mb-0 small">Memuat detail pengajuan...</p>
                </div>

                <div id="modalDataContainer" style="display: none;">
                    {{-- Info Card Pemohon & Pengajuan --}}
                    <div class="card bg-light border-0 mb-3">
                        <div class="card-body p-3">
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <table class="table table-sm table-borderless mb-0">
                                        <tr>
                                            <td class="text-muted" style="width: 120px;">Pemohon</td>
                                            <td>: <strong id="modalPemohonNama" class="text-dark">-</strong></td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Divisi</td>
                                            <td>: <span id="modalPemohonDivisi" class="text-dark">-</span></td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Jabatan</td>
                                            <td>: <span id="modalPemohonJabatan" class="text-dark">-</span></td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-sm table-borderless mb-0">
                                        <tr>
                                            <td class="text-muted" style="width: 120px;">Tanggal</td>
                                            <td>: <span id="modalTanggal" class="text-dark">-</span></td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Tipe Pengajuan</td>
                                            <td>: <span id="modalTipe" class="badge bg-primary">-</span></td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted align-top">Status Tracking</td>
                                            <td>: <span id="modalStatusTracking" class="badge bg-secondary text-wrap text-start d-inline-block" style="white-space: normal; line-height: 1.4; max-width: 100%;">-</span></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Tabel Rincian Barang --}}
                    <h6 class="fw-bold mb-2">
                        <img src="{{ asset('icon/box.svg') }}" width="20px" class="me-1"> Rincian Barang
                    </h6>
                    <div class="table-responsive mb-3">
                        <table class="table table-striped table-bordered align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 40px;" class="text-center">No</th>
                                    <th>Nama Barang</th>
                                    <th class="text-center" style="width: 80px;">Qty</th>
                                    <th class="text-end" style="width: 140px;">Harga Satuan</th>
                                    <th class="text-end" style="width: 140px;">Subtotal</th>
                                    <th>Keterangan</th>
                                </tr>
                            </thead>
                            <tbody id="modalItemsBody"></tbody>
                            <tfoot>
                                <tr class="fw-bold table-light">
                                    <th colspan="4" class="text-end">Total Nominal:</th>
                                    <th class="text-end text-success" id="modalTotalNominal">Rp 0</th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer d-flex justify-content-between align-items-center">
                <div id="modalAttachments">
                    <a href="#" id="modalInvoiceLink" target="_blank" class="btn btn-sm btn-outline-primary me-1" style="display:none;">
                        <img src="{{ asset('icon/file-text.svg') }}" class="me-1"> Lihat Invoice
                    </a>
                    <a href="#" id="modalBuktiLink" target="_blank" class="btn btn-sm btn-outline-success" style="display:none;">
                        <img src="{{ asset('icon/file-text.svg') }}" class="me-1"> Lihat Bukti
                    </a>
                </div>
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const bulanLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

    function formatRupiah(number) {
        return 'Rp ' + Number(number).toLocaleString('id-ID');
    }

    function openAnalyticsSubTab(target) {
        // Buka collapse container grafik jika sedang tersembunyi
        const collapseEl = document.getElementById('collapseChartSection');
        if (collapseEl && !collapseEl.classList.contains('show')) {
            const bsCollapse = bootstrap.Collapse.getOrCreateInstance(collapseEl, { toggle: false });
            bsCollapse.show();
        }

        // Pindah ke sub-pill yang dituju
        if (target === 'pribadi') {
            const subTabEl = document.querySelector('#subtab-pribadi-btn');
            if (subTabEl) bootstrap.Tab.getOrCreateInstance(subTabEl).show();
        } else if (target === 'divisi') {
            const subTabEl = document.querySelector('#subtab-divisi-btn');
            if (subTabEl) bootstrap.Tab.getOrCreateInstance(subTabEl).show();
        } else {
            const subTabEl = document.querySelector('#subtab-overview-btn');
            if (subTabEl) bootstrap.Tab.getOrCreateInstance(subTabEl).show();
        }

        // Scroll smooth ke bagian grafik di atas
        const container = document.getElementById('sectionGrafikContainer');
        if (container) {
            container.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }

    $(document).ready(function() {
        // Inisialisasi DataTable Operasional Pribadi
        var tablePribadi = $('#tablePribadi').DataTable({
            pageLength: 10,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Semua"]],
            ordering: true,
            order: [[0, 'desc']],
            responsive: true,
            autoWidth: false,
            columnDefs: [
                { orderable: false, searchable: false, targets: 6 }
            ],
            language: {
                search: "Cari Data:",
                searchPlaceholder: "Ketik untuk mencari...",
                lengthMenu: "Tampilkan _MENU_ data",
                info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                infoEmpty: "Menampilkan 0 data",
                infoFiltered: "(disaring dari _MAX_ total data)",
                zeroRecords: "Data tidak ditemukan",
                emptyTable: "Tidak ada data pengajuan operasional pribadi pada periode ini",
                paginate: {
                    first: "Awal",
                    last: "Akhir",
                    next: "Selanjutnya",
                    previous: "Sebelumnya"
                }
            }
        });

        // Inisialisasi DataTable Elektronik Divisi ITSM
        var tableDivisi = $('#tableDivisi').DataTable({
            pageLength: 10,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Semua"]],
            ordering: true,
            order: [[0, 'desc']],
            responsive: true,
            autoWidth: false,
            columnDefs: [
                { orderable: false, searchable: false, targets: 9 }
            ],
            language: {
                search: "Cari Data:",
                searchPlaceholder: "Ketik untuk mencari...",
                lengthMenu: "Tampilkan _MENU_ data",
                info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                infoEmpty: "Menampilkan 0 data",
                infoFiltered: "(disaring dari _MAX_ total data)",
                zeroRecords: "Data tidak ditemukan",
                emptyTable: "Tidak ada data pengajuan elektronik divisi ITSM pada periode ini",
                paginate: {
                    first: "Awal",
                    last: "Akhir",
                    next: "Selanjutnya",
                    previous: "Sebelumnya"
                }
            }
        });

        // Menyesuaikan lebar kolom DataTable saat berpindah tab
        $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
            $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
            window.dispatchEvent(new Event('resize'));
        });

        $('button[data-bs-toggle="pill"]').on('shown.bs.tab', function (e) {
            window.dispatchEvent(new Event('resize'));
        });

        // Event Collapse Hide/Show Grafik
        const collapseEl = document.getElementById('collapseChartSection');
        if (collapseEl) {
            collapseEl.addEventListener('hidden.bs.collapse', function () {
                $('#textToggleChart').text('Tampilkan Grafik');
                $('#iconToggleChart').css('transform', 'rotate(180deg)');
            });
            collapseEl.addEventListener('shown.bs.collapse', function () {
                $('#textToggleChart').text('Sembunyikan Grafik');
                $('#iconToggleChart').css('transform', 'rotate(0deg)');
                window.dispatchEvent(new Event('resize'));
            });
        }

        // Buka otomatis grafik jika user mengklik pill pilihan saat grafik sedang di-hide
        $('#analyticsSubTabs button').on('click', function() {
            const collapseEl = document.getElementById('collapseChartSection');
            if (collapseEl && !collapseEl.classList.contains('show')) {
                const bsCollapse = bootstrap.Collapse.getOrCreateInstance(collapseEl, { toggle: false });
                bsCollapse.show();
            }
        });

        // ========================================================
        // 1. GRAFIK OVERVIEW / GABUNGAN
        // ========================================================
        const monthlyTrendPribadi = @json($monthlyTrendPribadi);
        const monthlyTrendDivisi  = @json($monthlyTrendDivisi);
        const totalPribadiNominal = {{ $totalPribadiNominal }};
        const totalDivisiNominal  = {{ $totalDivisiNominal }};

        if (document.getElementById('chartMonthlyTrend')) {
            new Chart(document.getElementById('chartMonthlyTrend'), {
                type: 'line',
                data: {
                    labels: bulanLabels,
                    datasets: [
                        {
                            label: 'Operasional Pribadi',
                            data: monthlyTrendPribadi,
                            borderColor: '#0dcaf0',
                            backgroundColor: 'rgba(13, 202, 240, 0.1)',
                            tension: 0.3,
                            fill: true,
                            pointRadius: 4,
                        },
                        {
                            label: 'Elektronik Divisi ITSM',
                            data: monthlyTrendDivisi,
                            borderColor: '#0d6efd',
                            backgroundColor: 'rgba(13, 110, 253, 0.1)',
                            tension: 0.3,
                            fill: true,
                            pointRadius: 4,
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'top' },
                        tooltip: {
                            callbacks: {
                                label: function(c) { return c.dataset.label + ': ' + formatRupiah(c.raw); }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(v) {
                                    if (v >= 1000000) return 'Rp ' + (v / 1000000).toFixed(1) + ' Jt';
                                    if (v >= 1000) return 'Rp ' + (v / 1000).toFixed(0) + ' Rb';
                                    return 'Rp ' + v;
                                }
                            }
                        }
                    }
                }
            });
        }

        if (document.getElementById('chartProporsi')) {
            new Chart(document.getElementById('chartProporsi'), {
                type: 'doughnut',
                data: {
                    labels: ['Operasional Pribadi', 'Elektronik Divisi ITSM'],
                    datasets: [{
                        data: (totalPribadiNominal + totalDivisiNominal > 0) ? [totalPribadiNominal, totalDivisiNominal] : [1, 1],
                        backgroundColor: ['rgba(13, 202, 240, 0.85)', 'rgba(13, 110, 253, 0.85)'],
                        borderWidth: 2,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom' },
                        tooltip: {
                            callbacks: {
                                label: function(c) {
                                    let total = totalPribadiNominal + totalDivisiNominal;
                                    if (total <= 0) return c.label + ': Rp 0 (0%)';
                                    let pct = ((c.raw / total) * 100).toFixed(1);
                                    return c.label + ': ' + formatRupiah(c.raw) + ' (' + pct + '%)';
                                }
                            }
                        }
                    }
                }
            });
        }

        // ========================================================
        // 2. GRAFIK OPERASIONAL PRIBADI
        // ========================================================
        const pribadiTopQtyLabels     = @json($pribadiTopQty->pluck('nama'));
        const pribadiTopQtyData       = @json($pribadiTopQty->pluck('qty'));
        const pribadiTopNominalLabels = @json($pribadiTopNominal->pluck('nama'));
        const pribadiTopNominalData   = @json($pribadiTopNominal->pluck('nominal'));
        const pribadiMonthlyData      = @json($pribadiMonthly);
        const pribadiStatusKeys       = @json(array_keys($pribadiStatusCounts));
        const pribadiStatusValues     = @json(array_values($pribadiStatusCounts));

        // Pribadi Top Qty
        if (document.getElementById('chartPribadiTopQty')) {
            new Chart(document.getElementById('chartPribadiTopQty'), {
                type: 'bar',
                data: {
                    labels: pribadiTopQtyLabels.length > 0 ? pribadiTopQtyLabels : ['Belum ada data'],
                    datasets: [{
                        label: 'Qty Dibeli',
                        data: pribadiTopQtyData.length > 0 ? pribadiTopQtyData : [0],
                        backgroundColor: 'rgba(13, 202, 240, 0.75)',
                        borderColor: '#0dcaf0',
                        borderWidth: 1,
                        borderRadius: 5,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: { callbacks: { label: function(c) { return 'Jumlah: ' + c.raw + ' unit'; } } }
                    },
                    scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
                }
            });
        }

        // Pribadi Top Nominal
        if (document.getElementById('chartPribadiTopNominal')) {
            new Chart(document.getElementById('chartPribadiTopNominal'), {
                type: 'bar',
                data: {
                    labels: pribadiTopNominalLabels.length > 0 ? pribadiTopNominalLabels : ['Belum ada data'],
                    datasets: [{
                        label: 'Total Nominal',
                        data: pribadiTopNominalData.length > 0 ? pribadiTopNominalData : [0],
                        backgroundColor: 'rgba(25, 135, 84, 0.75)',
                        borderColor: '#198754',
                        borderWidth: 1,
                        borderRadius: 5,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: { callbacks: { label: function(c) { return 'Total: ' + formatRupiah(c.raw); } } }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(v) {
                                    if (v >= 1000000) return 'Rp ' + (v / 1000000).toFixed(1) + ' Jt';
                                    if (v >= 1000) return 'Rp ' + (v / 1000).toFixed(0) + ' Rb';
                                    return 'Rp ' + v;
                                }
                            }
                        }
                    }
                }
            });
        }

        // Pribadi Tren Bulanan
        if (document.getElementById('chartPribadiMonthly')) {
            new Chart(document.getElementById('chartPribadiMonthly'), {
                type: 'line',
                data: {
                    labels: bulanLabels,
                    datasets: [{
                        label: 'Biaya Operasional Pribadi',
                        data: pribadiMonthlyData,
                        borderColor: '#0dcaf0',
                        backgroundColor: 'rgba(13, 202, 240, 0.15)',
                        fill: true,
                        tension: 0.3,
                        pointRadius: 4,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: { callbacks: { label: function(c) { return 'Biaya: ' + formatRupiah(c.raw); } } }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(v) {
                                    if (v >= 1000000) return 'Rp ' + (v / 1000000).toFixed(1) + ' Jt';
                                    if (v >= 1000) return 'Rp ' + (v / 1000).toFixed(0) + ' Rb';
                                    return 'Rp ' + v;
                                }
                            }
                        }
                    }
                }
            });
        }

        // Pribadi Status Breakdown
        if (document.getElementById('chartPribadiStatus')) {
            new Chart(document.getElementById('chartPribadiStatus'), {
                type: 'doughnut',
                data: {
                    labels: pribadiStatusKeys.length > 0 ? pribadiStatusKeys : ['Belum ada data'],
                    datasets: [{
                        data: pribadiStatusValues.length > 0 ? pribadiStatusValues : [1],
                        backgroundColor: ['#198754', '#ffc107', '#0dcaf0', '#6c757d', '#dc3545'],
                        borderWidth: 2,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } },
                        tooltip: { callbacks: { label: function(c) { return c.label + ': ' + c.raw + ' pengajuan'; } } }
                    }
                }
            });
        }

        // ========================================================
        // 3. GRAFIK ELEKTRONIK DIVISI ITSM
        // ========================================================
        const divisiTopQtyLabels     = @json($divisiTopQty->pluck('nama'));
        const divisiTopQtyData       = @json($divisiTopQty->pluck('qty'));
        const divisiTopNominalLabels = @json($divisiTopNominal->pluck('nama'));
        const divisiTopNominalData   = @json($divisiTopNominal->pluck('nominal'));
        const divisiEmployeeLabels   = @json(array_keys($divisiByEmployee));
        const divisiEmployeeData     = @json(array_values($divisiByEmployee));
        const divisiStatusKeys       = @json(array_keys($divisiStatusCounts));
        const divisiStatusValues     = @json(array_values($divisiStatusCounts));

        // Divisi Top Qty
        if (document.getElementById('chartDivisiTopQty')) {
            new Chart(document.getElementById('chartDivisiTopQty'), {
                type: 'bar',
                data: {
                    labels: divisiTopQtyLabels.length > 0 ? divisiTopQtyLabels : ['Belum ada data'],
                    datasets: [{
                        label: 'Qty Dibeli',
                        data: divisiTopQtyData.length > 0 ? divisiTopQtyData : [0],
                        backgroundColor: 'rgba(13, 110, 253, 0.75)',
                        borderColor: '#0d6efd',
                        borderWidth: 1,
                        borderRadius: 5,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: { callbacks: { label: function(c) { return 'Jumlah: ' + c.raw + ' unit'; } } }
                    },
                    scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
                }
            });
        }

        // Divisi Top Nominal
        if (document.getElementById('chartDivisiTopNominal')) {
            new Chart(document.getElementById('chartDivisiTopNominal'), {
                type: 'bar',
                data: {
                    labels: divisiTopNominalLabels.length > 0 ? divisiTopNominalLabels : ['Belum ada data'],
                    datasets: [{
                        label: 'Total Nominal',
                        data: divisiTopNominalData.length > 0 ? divisiTopNominalData : [0],
                        backgroundColor: 'rgba(25, 135, 84, 0.75)',
                        borderColor: '#198754',
                        borderWidth: 1,
                        borderRadius: 5,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: { callbacks: { label: function(c) { return 'Total: ' + formatRupiah(c.raw); } } }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(v) {
                                    if (v >= 1000000) return 'Rp ' + (v / 1000000).toFixed(1) + ' Jt';
                                    if (v >= 1000) return 'Rp ' + (v / 1000).toFixed(0) + ' Rb';
                                    return 'Rp ' + v;
                                }
                            }
                        }
                    }
                }
            });
        }

        // Divisi Spending by Employee
        if (document.getElementById('chartDivisiByEmployee')) {
            new Chart(document.getElementById('chartDivisiByEmployee'), {
                type: 'bar',
                data: {
                    labels: divisiEmployeeLabels.length > 0 ? divisiEmployeeLabels : ['Belum ada data'],
                    datasets: [{
                        label: 'Total Pengajuan (Rp)',
                        data: divisiEmployeeData.length > 0 ? divisiEmployeeData : [0],
                        backgroundColor: 'rgba(111, 66, 193, 0.75)',
                        borderColor: '#6f42c1',
                        borderWidth: 1,
                        borderRadius: 5,
                    }]
                },
                options: {
                    indexAxis: 'y', // Bar horizontal
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: { callbacks: { label: function(c) { return 'Pengeluaran: ' + formatRupiah(c.raw); } } }
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(v) {
                                    if (v >= 1000000) return 'Rp ' + (v / 1000000).toFixed(1) + ' Jt';
                                    if (v >= 1000) return 'Rp ' + (v / 1000).toFixed(0) + ' Rb';
                                    return 'Rp ' + v;
                                }
                            }
                        }
                    }
                }
            });
        }

        // Divisi Status Breakdown
        if (document.getElementById('chartDivisiStatus')) {
            new Chart(document.getElementById('chartDivisiStatus'), {
                type: 'doughnut',
                data: {
                    labels: divisiStatusKeys.length > 0 ? divisiStatusKeys : ['Belum ada data'],
                    datasets: [{
                        data: divisiStatusValues.length > 0 ? divisiStatusValues : [1],
                        backgroundColor: ['#198754', '#0d6efd', '#ffc107', '#6c757d', '#dc3545'],
                        borderWidth: 2,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } },
                        tooltip: { callbacks: { label: function(c) { return c.label + ': ' + c.raw + ' pengajuan'; } } }
                    }
                }
            });
        }
    });

    function openDetailModal(id) {
        const modal = new bootstrap.Modal(document.getElementById('detailModal'));
        modal.show();

        const loading = document.getElementById('modalLoading');
        const container = document.getElementById('modalDataContainer');
        loading.style.display = 'block';
        container.style.display = 'none';

        fetch(`{{ url('/itsm/rekap-pembelian/detail') }}/${id}`)
            .then(res => res.json())
            .then(res => {
                if (res.success && res.data) {
                    const d = res.data;
                    document.getElementById('modalPemohonNama').textContent = d.pemohon.nama;
                    document.getElementById('modalPemohonDivisi').textContent = d.pemohon.divisi;
                    document.getElementById('modalPemohonJabatan').textContent = d.pemohon.jabatan;
                    document.getElementById('modalTanggal').textContent = d.created_at;
                    document.getElementById('modalTipe').textContent = d.tipe;

                    const statusEl = document.getElementById('modalStatusTracking');
                    statusEl.textContent = d.status_tracking;

                    // Beri warna badge yang sesuai dan pastikan text wrap
                    let statusLower = (d.status_tracking || '').toLowerCase();
                    statusEl.className = 'badge text-wrap text-start d-inline-block';
                    if (statusLower.includes('selesai')) {
                        statusEl.classList.add('bg-success');
                    } else if (statusLower.includes('tolak') || statusLower.includes('batal')) {
                        statusEl.classList.add('bg-danger');
                    } else if (statusLower.includes('proses') || statusLower.includes('pencairan') || statusLower.includes('tinjau') || statusLower.includes('setuju')) {
                        statusEl.classList.add('bg-warning', 'text-dark');
                    } else {
                        statusEl.classList.add('bg-secondary');
                    }

                    document.getElementById('modalTotalNominal').textContent = formatRupiah(d.total_nominal);

                    // Render Items
                    const tbody = document.getElementById('modalItemsBody');
                    tbody.innerHTML = '';
                    if (d.items && d.items.length > 0) {
                        d.items.forEach((item, idx) => {
                            const tr = document.createElement('tr');
                            tr.innerHTML = `
                                <td class="text-center text-muted">${idx + 1}</td>
                                <td class="fw-semibold">${item.nama_barang}</td>
                                <td class="text-center">${item.qty}</td>
                                <td class="text-end">${formatRupiah(item.harga)}</td>
                                <td class="text-end fw-bold">${formatRupiah(item.subtotal)}</td>
                                <td class="small text-muted">${item.keterangan || '-'}</td>
                            `;
                            tbody.appendChild(tr);
                        });
                    } else {
                        tbody.innerHTML = `<tr><td colspan="6" class="text-center text-muted py-3">Tidak ada data item barang</td></tr>`;
                    }

                    // Attachments
                    const invLink = document.getElementById('modalInvoiceLink');
                    if (d.invoice) {
                        invLink.href = d.invoice;
                        invLink.style.display = 'inline-block';
                    } else {
                        invLink.style.display = 'none';
                    }

                    const buktiLink = document.getElementById('modalBuktiLink');
                    if (d.bukti) {
                        buktiLink.href = d.bukti;
                        buktiLink.style.display = 'inline-block';
                    } else {
                        buktiLink.style.display = 'none';
                    }

                    loading.style.display = 'none';
                    container.style.display = 'block';
                } else {
                    loading.innerHTML = `<div class="text-danger py-3">Gagal memuat data detail.</div>`;
                }
            })
            .catch(err => {
                console.error(err);
                loading.innerHTML = `<div class="text-danger py-3">Terjadi kesalahan saat memuat data.</div>`;
            });
    }
</script>
@endpush
