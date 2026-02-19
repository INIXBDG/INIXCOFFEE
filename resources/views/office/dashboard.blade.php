@extends('layouts_office.app')

@section('office_contents')
    <div class="container-fluid py-4">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-5">
            <h4 class="mb-0 fw-bold text-dark">Dashboard Office</h4>
            <small class="text-muted fw-medium">{{ now()->translatedFormat('l, d F Y') }}</small>
        </div>

        <!-- Total Karyawan Card -->
        <div class="row mb-5">
            <div class="col-12">
                <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="avatar avatar-xl bg-opacity-15 rounded-circle p-3">
                                    <i class="bx bx-user text-primary" style="font-size: 2.5rem;color:white;"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-4">
                                <h6 class="text-muted mb-2 text-uppercase small tracking-wider">Total Karyawan Aktif</h6>
                                <h2 class="mb-0 text-primary fw-bold">{{ $total_karyawan }}</h2>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Divisi Stats Cards -->
        <div class="row mb-5 g-4">
            @foreach ($divisiStats as $index => $divisi)
                <div class="col-xl-3 col-md-6">
                    <div class="card border-0 shadow-sm h-100 hover-card rounded-3 overflow-hidden" data-bs-toggle="modal"
                        data-bs-target="#modalDivisi{{ $index }}" role="button" tabindex="0">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center mb-3">
                                <div class="flex-shrink-0">
                                    <div class="avatar avatar-md bg-{{ $divisi['color'] }} bg-opacity-15 rounded-pill">
                                        <i class="{{ $divisi['icon'] }}" style="font-size: 1.5rem;color:white"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="text-muted mb-1 small text-uppercase tracking-wider">{{ $divisi['nama'] }}
                                    </h6>
                                    <h3 class="mb-0 fw-bold text-dark">{{ $divisi['total'] }}</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Chart & Tidak Hadir -->
        <div class="row g-4">
            <!-- Chart Kehadiran -->
            <div class="col-xl-8">
                <div class="card border-0 shadow-lg h-100 rounded-4 overflow-hidden">
                    <div class="card-header bg-white border-bottom-0 pb-0">
                        <h5 class="mb-0 fw-semibold text-dark d-flex align-items-center">
                            <i class="bx bx-line-chart text-primary me-2" style="font-size: 1.5rem;"></i>
                            Grafik Kehadiran 7 Hari Terakhir
                        </h5>
                    </div>
                    <div class="card-body p-4"
                        style="height: 320px; display: flex; flex-direction: column; justify-content: space-between;">
                        <div style="flex-grow: 1; position: relative; height: 250px;">
                            <canvas id="kehadiranChart" style="height: 100% !important; width: 100% !important;"></canvas>
                        </div>
                        <div class="mt-3 text-center">
                            <small class="text-muted">
                                Rata-rata kehadiran:
                                {{ round(array_sum($kehadiranChart['data']) / count($kehadiranChart['data']), 1) }}
                                dari {{ $total_karyawan }} karyawan
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Karyawan Tidak Hadir Hari Ini -->
            <div class="col-xl-4">
                <div class="card border-0 shadow-lg h-100 rounded-4 overflow-hidden">
                    <div class="card-header bg-white border-bottom-0 pb-0">
                        <h5 class="mb-0 fw-semibold text-dark d-flex align-items-center">
                            <i class="bx bx-user-x text-danger me-2" style="font-size: 1.5rem;"></i>
                            Tidak Hadir Hari Ini
                        </h5>
                    </div>
                    <div class="card-body p-4 scrollbar-custom" style="max-height: 480px; overflow-y: auto;">
                        @if (count($tidakHadirList) > 0)
                            <div class="list-group list-group-flush">
                                @foreach ($tidakHadirList as $item)
                                    <div class="list-group-item bg-transparent px-0 py-3 border-bottom">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-shrink-0">
                                                <div class="avatar avatar-sm bg-opacity-15 rounded-circle">
                                                    <span class="text-danger fw-bold small">
                                                        {{ strtoupper(substr($item['nama'], 0, 1)) }}
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="flex-grow-1 ms-3">
                                                <h6 class="mb-1 fw-medium text-dark">{{ $item['nama'] }}</h6>
                                                <small class="text-muted d-block">{{ $item['divisi'] }}</small>
                                                <span class="badge bg-warning text-dark mt-1">Tidak Hadir</span>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <div class="mt-3 text-center">
                                <small class="text-danger fw-medium">
                                    Total: {{ count($tidakHadirList) }} karyawan
                                </small>
                            </div>
                        @else
                            <div class="text-center py-5">
                                <i class="bx bx-check-circle text-success" style="font-size: 4rem; opacity: 0.8;"></i>
                                <p class="text-success fw-bold mt-3 mb-0" style="font-size: 1.1rem;">
                                    Semua karyawan hadir hari ini!
                                </p>
                                <small class="text-muted d-block mt-2">Kehadiran 100% 👏</small>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Chart Cuti -->
            <div class="col-xl-12">
                <div class="card border-0 shadow-lg h-100 rounded-4 overflow-hidden">
                    <div class="card-header bg-white border-bottom-0 pb-0 d-flex justify-content-between">
                        <h5 class="mb-0 fw-semibold text-dark d-flex align-items-center">
                            <i class="bx bx-pie-chart-alt text-primary me-2" style="font-size: 1.5rem;"></i>
                            Grafik Cuti<span class="ms-2" id="rentangWaktu"></span>
                        </h5>
                        <div class="d-flex gap-4 align-items-center">
                            <h6 class="mb-0">Export : </h6>
                            <button type="button" id="exportCuti" class="btn btn-outline-secondary btn-sm pdfBtn">
                            PDF
                            </button>
                        </div>
                    </div>
                    <div class="card-body pe-5 mt-5" style="height: 320px;">
                        <div class="chart-container d-flex align-items-center" style="position: relative; height: 100%;">
                            <canvas id="dataCuti"></canvas>
                            <div id="cutiEmpty" class="d-none d-flex flex-column align-items-center container-fluid">
                                <i class="bx bx-x-circle text-muted" style="font-size: 3rem;"></i>
                                <p class="text-muted mt-3 mb-0">Tidak ada data Cuti</p>
                            </div>
                        </div>
                    </div>
                    <div class="container">
                        <div class="row">
                            <div class="col">
                                <label for="filterCutiTahun" class="mb-1 ms-1">Tahun</label>
                                <select id="filterCutiTahun" class="form-select mb-3">
                                    <option value="default" disabled selected>Berdasarkan Tahun</option>
                                    @php
                                        $tahun_sekarang = now()->year;
                                        for ($tahun = 2023; $tahun <= $tahun_sekarang + 2; $tahun++) {
                                            echo "<option value=\"$tahun\">$tahun</option>";
                                        }
                                    @endphp
                                </select>
                            </div>
                            <div class="col">
                                <label for="filterCutiBulan" class="mb-1 ms-1">Bulan</label>
                                <select id="filterCutiBulan" class="form-select mb-3">
                                    <option value="default" disabled selected>Berdasarkan Bulan</option>
                                    @php
                                    $bulan_sekarang = now()->month;
                                    $nama_bulan = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                                    for ($bulan = 1; $bulan <= 12; $bulan++) {
                                        echo "<option value=\"$bulan\">{$nama_bulan[$bulan - 1]}</option>";
                                    }
                                    @endphp
                                </select>
                            </div>
                            <div class="col">
                                <label for="filterCutiTriwulan" class="mb-1 ms-1">Triwulan</label>
                                <select id="filterCutiTriwulan" class="form-select mb-3">
                                    <option value="default" disabled selected>Berdasarkan Triwulan</option>
                                    <option value="1">Quarter 1</option>
                                    <option value="2">Quarter 2</option>
                                    <option value="3">Quarter 3</option>
                                    <option value="4">Quarter 4</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            {{-- End Chart Cuti --}}

            {{-- Total Mengajar Instruktur --}}
            <div class="col-xl-12">
                <div class="card border-0 shadow-lg h-100 rounded-4 overflow-hidden">
                    <div class="card-header bg-white border-bottom-0 pb-0 d-flex justify-content-between">
                        <h5 class="mb-0 fw-semibold text-dark d-flex align-items-center">
                            <i class="bx bx-archive text-primary me-2" style="font-size: 1.5rem;"></i>
                            Total Mengajar Instruktur <span class="ms-2" id="rentangWaktuMengajar"></span>
                        </h5>
                        <div class="d-flex gap-4 align-items-center">
                            <h6 class="mb-0">Export : </h6>
                            <button type="button" id="exportTotalMengajar" class="btn btn-outline-secondary btn-sm pdfBtn">
                            PDF
                            </button>
                        </div>
                    </div>
                    <div class="container mt-5">
                        <div class="row">
                            <div class="col">
                                <label for="filterMengajarPerTahun" class="mb-1 ms-1">Tahun</label>
                                <select id="filterMengajarPerTahun" class="form-select mb-3">
                                    <option value="default" disabled selected>Berdasarkan Tahun</option>
                                    @php
                                        $tahun_sekarang = now()->year;
                                        for ($tahun = 2023; $tahun <= $tahun_sekarang + 2; $tahun++) {
                                            echo "<option value=\"$tahun\">$tahun</option>";
                                        }
                                    @endphp
                                </select>
                            </div>
                            <div class="col">
                                <label for="filterMengajarPerBulan" class="mb-1 ms-1">Bulan</label>
                                <select id="filterMengajarPerBulan" class="form-select mb-3">
                                    <option value="default" disabled selected>Berdasarkan Bulan</option>
                                    @php
                                    $bulan_sekarang = now()->month;
                                    $nama_bulan = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                                    for ($bulan = 1; $bulan <= 12; $bulan++) {
                                        echo "<option value=\"$bulan\">{$nama_bulan[$bulan - 1]}</option>";
                                    }
                                    @endphp
                                </select>
                            </div>
                            <div class="col">
                                <label for="filterMengajarPerTriwulan" class="mb-1 ms-1">Triwulan</label>
                                <select id="filterMengajarPerTriwulan" class="form-select mb-3">
                                    <option value="default" disabled selected>Berdasarkan Triwulan</option>
                                    <option value="1">Quarter 1</option>
                                    <option value="2">Quarter 2</option>
                                    <option value="3">Quarter 3</option>
                                    <option value="4">Quarter 4</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-0 mt-2">
                        <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                            <table class="table table-hover align-middle mb-0" id="tabelTotalMengajar">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <td class="border-0" style="width: 5%">No</td>
                                        <td class="border-0" style="min-width: 70%">Nama Lengkap</td>
                                        <td class="border-0" style="min-width: 15%">Kode Instruktur</td>
                                        <td class="border-0" style="min-width: 10%">Total Mengajar</td>
                                    </tr>
                                </thead>
                                <tbody class="text-muted fw-medium">
                                    
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            {{-- End Total Mengajar Instrukrur --}}

            <!-- Chart nilai Feedback -->
            <div class="card border-0 shadow-lg h-100 rounded-4 overflow-hidden">
                <div class="card-header bg-white border-bottom-0 pb-0 d-flex justify-content-between">
                    <h5 class="mb-0 fw-semibold text-dark d-flex align-items-center">
                        <i class="bx bx-line-chart text-primary me-2" style="font-size: 1.5rem;"></i>
                        Grafik Feedback
                    </h5>
                    <div class="d-flex gap-4 align-items-center">
                        <h6 class="mb-0">Export : </h6>
                        <button type="button" id="exportFeedbackInstruktur" class="btn btn-outline-secondary btn-sm pdfBtn">
                            PDF
                        </button>
                    </div>
                </div>

                <div class="card-body p-4"
                    style="height: 400px; display: flex; flex-direction: column; justify-content: space-between;">
                    <div class="card-body p-4 chart-wrapper">
                        <canvas id="feedbackChart"></canvas>
                        <div id="nilaiInstrukturEmpty" class="d-none position-absolute top-50 start-50 translate-middle
                                d-flex flex-column align-items-center text-center w-100">
                            <i class="bx bx-x-circle text-muted" style="font-size:3rem;"></i>
                            <p class="text-muted mt-2 mb-0">
                                Tidak ada data Nilai Instruktur
                            </p>
                        </div>

                    </div>

                </div>
                <div class="container">
                    <div class="row">
                        <div class="col">
                            <label for="filterFeedbackTahun" class="mb-1 ms-1">Tahun</label>
                            <select id="filterFeedbackTahun" class="form-select mb-3">
                                <option value="default" disabled selected>Berdasarkan Tahun</option>
                                @php
                                    $tahun_sekarang = now()->year;
                                    for ($tahun = 2023; $tahun <= $tahun_sekarang + 2; $tahun++) {
                                        echo "<option value=\"$tahun\">$tahun</option>";
                                    }
                                @endphp
                            </select>
                        </div>
                        <div class="col">
                            <label for="filterFeedbackBulan" class="mb-1 ms-1">Bulan</label>
                            <select id="filterFeedbackBulan" class="form-select mb-3">
                                <option value="default" disabled selected>Berdasarkan Bulan</option>
                                @php
                                    $bulan_sekarang = now()->month;
                                    $nama_bulan = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                                    for ($bulan = 1; $bulan <= 12; $bulan++) {
                                        echo "<option value=\"$bulan\">{$nama_bulan[$bulan - 1]}</option>";
                                    }
                                @endphp
                            </select>
                        </div>
                        <div class="col">
                            <label for="filterFeedbackTriwulan" class="mb-1 ms-1">Triwulan</label>
                            <select id="filterFeedbackTriwulan" class="form-select mb-3">
                                <option value="default" disabled selected>Berdasarkan Triwulan</option>
                                <option value="1">Quarter 1</option>
                                <option value="2">Quarter 2</option>
                                <option value="3">Quarter 3</option>
                                <option value="4">Quarter 4</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            {{-- End Chart nilai Feedback --}}

            {{-- RKM Berjalan Minggu Ini --}}
            <div class="row g-3 mb-4">
                <div class="col-12">
                    <div class="card h-100 shadow-sm border-0 rounded-3">
                        <div class="card-header bg-white border-bottom py-3">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <span class="badge bg-primary-subtle text-primary px-3 py-2">
                                    {{ count($rkm) }} RKM
                                </span>

                                <span class="badge bg-success-subtle text-success px-3 py-2">
                                    {{ number_format($jumlahPeserta, 0, ',', '.') }} Peserta
                                </span>

                                <span class="badge bg-success-subtle text-success px-3 py-2">
                                    {{ number_format($jumlahInstruktur, 0, ',', '.') }} Instruktur
                                </span>
                            </div>

                            <div class="card-body p-0">
                                <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead class="table-light sticky-top">
                                            <tr>
                                                <th class="border-0 ps-4" style="min-width: 70px;">Sales</th>
                                                <th class="border-0" style="min-width: 250px;">Materi</th>
                                                <th class="border-0" style="min-width: 120px;">Harga</th>
                                                <th class="border-0" style="min-width: 200px;">Periode</th>
                                                <th class="border-0 text-center" style="min-width: 80px;">Pax</th>
                                                <th class="border-0 text-center pe-4" style="min-width: 100px;">Exam</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($rkm as $index => $item)
                                                <tr class="border-bottom">
                                                    <td class="ps-4">
                                                        <div class="d-flex align-items-center">
                                                            <span class="fw-medium">{{ $item->sales_key }}</span>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="text-truncate" style="max-width: 250px;"
                                                            data-bs-toggle="tooltip"
                                                            title="{{ $item->materi->nama_materi }}">
                                                            {{ $item->materi->nama_materi }}
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <span class="text-success fw-semibold">
                                                            Rp {{ number_format($item->harga_jual, 0, ',', '.') }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <div class="d-flex flex-column small">
                                                            <span class="text-muted">
                                                                {{ \Carbon\Carbon::parse($item->tanggal_awal)->format('d M Y') }}
                                                            </span>
                                                            <span class="text-muted">
                                                                <i class="bx bx-right-arrow-alt me-1"></i>
                                                                {{ \Carbon\Carbon::parse($item->tanggal_akhir)->format('d M Y') }}
                                                            </span>
                                                        </div>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge bg-info-subtle text-info px-3 py-2">
                                                            {{ number_format($item->pax, 0, ',', '.') }}
                                                        </span>
                                                    </td>
                                                    <td class="text-center pe-4">
                                                        @if ($item->exam == '1')
                                                            <span class="badge bg-success-subtle text-success px-3 py-2">
                                                                Ya
                                                            </span>
                                                        @else
                                                            <span
                                                                class="badge bg-secondary-subtle text-secondary px-3 py-2">
                                                                Tidak
                                                            </span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="7" class="text-center py-5">
                                                        <div class="d-flex flex-column align-items-center">
                                                            <i class="bx bx-calendar-x text-muted"
                                                                style="font-size: 3rem;"></i>
                                                            <p class="text-muted mt-3 mb-0">Tidak ada data RKM minggu ini
                                                            </p>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Daftar Ticketing --}}
                <div class="row g-3 mb-4">
                    <div class="col-12">
                        <div class="card h-100 shadow-sm border-0 rounded-3">
                            <div class="card-header bg-white border-bottom py-3">
                                <div class="d-flex align-items-center justify-content-between">
                                    <h5 class="card-title mb-0 fw-semibold">
                                        <i class="bx bx-support text-primary me-2"></i>
                                        Ticketing
                                    </h5>
                                    <span class="badge bg-primary-subtle text-primary">{{ count($ticket) }} Ticket</span>
                                </div>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead class="table-light sticky-top">
                                            <tr>
                                                <th class="border-0 ps-4" style="min-width: 160px;">Timestamp</th>
                                                <th class="border-0" style="min-width: 180px;">Karyawan</th>
                                                <th class="border-0" style="min-width: 150px;">Divisi</th>
                                                <th class="border-0" style="min-width: 120px;">Kategori</th>
                                                <th class="border-0" style="min-width: 200px;">Keperluan</th>
                                                <th class="border-0" style="min-width: 250px;">Detail Kendala</th>
                                                <th class="border-0" style="min-width: 150px;">PIC</th>
                                                <th class="border-0 text-center pe-4" style="min-width: 120px;">Status
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($ticket as $item)
                                                <tr class="border-bottom">
                                                    <td class="ps-4">
                                                        <div class="small">
                                                            {{ \Carbon\Carbon::parse($item->timestamp)->format('d M Y, H:i') }}
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <div class="text-truncate" style="max-width: 150px;"
                                                                data-bs-toggle="tooltip"
                                                                title="{{ $item->nama_karyawan }}">
                                                                {{ $item->nama_karyawan }}
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-secondary-subtle text-secondary">
                                                            {{ $item->divisi }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <div class="text-truncate" style="max-width: 120px;"
                                                            data-bs-toggle="tooltip" title="{{ $item->kategori }}">
                                                            <i class="bx bx-category text-muted me-1"></i>
                                                            {{ $item->kategori }}
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="text-truncate" style="max-width: 200px;"
                                                            data-bs-toggle="tooltip" title="{{ $item->keperluan }}">
                                                            {{ $item->keperluan }}
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="text-truncate" style="max-width: 250px;"
                                                            data-bs-toggle="tooltip" title="{{ $item->detail_kendala }}">
                                                            {{ $item->detail_kendala }}
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <div class="text-truncate" style="max-width: 100px;"
                                                                data-bs-toggle="tooltip" title="{{ $item->pic }}">
                                                                {{ $item->pic ?? '-' }}
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="text-center pe-4">
                                                        @php
                                                            $statusConfig = [
                                                                'Menunggu' => [
                                                                    'color' => 'warning',
                                                                    'icon' => 'bx-time-five',
                                                                ],
                                                                'Di Proses' => [
                                                                    'color' => 'primary',
                                                                    'icon' => 'bx-loader-circle',
                                                                ],
                                                                'Selesai' => [
                                                                    'color' => 'success',
                                                                    'icon' => 'bx-check-circle',
                                                                ],
                                                                'Terkendala' => [
                                                                    'color' => 'danger',
                                                                    'icon' => 'bx-error-circle',
                                                                ],
                                                            ];
                                                            $config = $statusConfig[$item->status] ?? [
                                                                'color' => 'secondary',
                                                                'icon' => 'bx-info-circle',
                                                            ];
                                                        @endphp
                                                        <span
                                                            class="badge bg-{{ $config['color'] }}-subtle text-{{ $config['color'] }} px-3 py-2">
                                                            <i class="bx {{ $config['icon'] }} me-1"></i>
                                                            {{ $item->status }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="8" class="text-center py-5">
                                                        <div class="d-flex flex-column align-items-center">
                                                            <i class="bx bx-message-square-x text-muted"
                                                                style="font-size: 3rem;"></i>
                                                            <p class="text-muted mt-3 mb-0">Tidak ada ticket untuk
                                                                ditampilkan
                                                            </p>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

<<<<<<< HEAD
    @auth
        <div class="web-push-container" style="position: fixed; bottom: 20px; right: 20px; z-index: 9999;">
            <button id="webpush-btn" class="btn btn-primary btn-sm shadow-sm"
                style="border-radius: 20px; padding: 6px 16px;">
                <i class="fas fa-bell"></i> Aktifkan Notifikasi
            </button>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', async function() {
                if (!('serviceWorker' in navigator) || !('PushManager' in window)) {
                    document.getElementById('webpush-btn')?.remove();
                    return;
                }

                const btn = document.getElementById('webpush-btn');
                if (!btn) return;

                let isSubscribed = false;
                let vapidPublicKey = null;

                try {
                    const registration = await registerServiceWorker();
                    if (!registration) {
                        btn.style.display = 'none';
                        return;
                    }

                    try {
                        const response = await fetch('{{ route('webpush.vapid-key') }}', {
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            }
                        });
                        const data = await response.json();
                        vapidPublicKey = data.publicKey;
                    } catch (error) {
                        console.error('Error getting VAPID key:', error);
                        showToast('Gagal memuat konfigurasi notifikasi', 'error');
                        btn.disabled = true;
                        return;
                    }

                    // Check subscription status
                    await checkSubscriptionStatus();

                } catch (error) {
                    console.error('Service Worker registration failed:', error);
                    btn.style.display = 'none';
                }

                function updateButtonState() {
                    if (isSubscribed) {
                        btn.className = 'btn btn-success btn-sm shadow-sm';
                        btn.innerHTML = '<i class="fas fa-bell"></i> Notifikasi Aktif';
                    } else {
                        btn.className = 'btn btn-primary btn-sm shadow-sm';
                        btn.innerHTML = '<i class="fas fa-bell"></i> Aktifkan Notifikasi';
                    }
                    btn.disabled = false;
                }

                btn.addEventListener('click', function() {
                    if (isSubscribed) {
                        unsubscribe();
                    } else {
                        subscribe();
                    }
                });

                async function registerServiceWorker() {
                    try {
                        const registration = await navigator.serviceWorker.register('/service-worker.js', {
                            scope: '/',
                            updateViaCache: 'none'
                        });
                        console.log('[SW] Registered successfully:', registration.scope);
                        return registration;
                    } catch (error) {
                        console.error('[SW] Registration failed:', error);
                        showToast('Gagal registrasi Service Worker', 'error');
                        return null;
                    }
                }

                async function checkSubscriptionStatus() {
                    try {
                        const registration = await navigator.serviceWorker.ready;
                        const subscription = await registration.pushManager.getSubscription();
                        isSubscribed = !!subscription;
                        updateButtonState();
                    } catch (error) {
                        console.error('Check subscription error:', error);
                    }
                }

                async function subscribe() {
                    if (!vapidPublicKey) {
                        showToast('Konfigurasi tidak lengkap', 'error');
                        return;
                    }

                    btn.disabled = true;
                    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Mengaktifkan...';

                    try {
                        // Check permission first
                        const permission = await Notification.requestPermission();
                        if (permission !== 'granted') {
                            throw new Error('Izin notifikasi ditolak');
                        }

                        const registration = await navigator.serviceWorker.ready;
                        const convertedVapidKey = urlBase64ToUint8Array(vapidPublicKey);

                        const subscription = await registration.pushManager.subscribe({
                            userVisibleOnly: true,
                            applicationServerKey: convertedVapidKey
                        });

                        const response = await fetch('{{ route('webpush.subscribe') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify(subscription)
                        });

                        const data = await response.json();

                        if (data.success) {
                            isSubscribed = true;
                            updateButtonState();
                            showToast('✅ Notifikasi berhasil diaktifkan!', 'success');
                        } else {
                            throw new Error(data.message || 'Gagal subscribe ke server');
                        }

                    } catch (error) {
                        console.error('Subscribe error:', error);
                        showToast('❌ ' + getErrorMessage(error), 'error');
                        btn.disabled = false;
                        updateButtonState();
                    }
                }

                async function unsubscribe() {
                    btn.disabled = true;
                    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Mematikan...';

                    try {
                        const registration = await navigator.serviceWorker.ready;
                        const subscription = await registration.pushManager.getSubscription();

                        if (subscription) {
                            await subscription.unsubscribe();

                            await fetch('{{ route('webpush.unsubscribe') }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Accept': 'application/json'
                                },
                                body: JSON.stringify({
                                    endpoint: subscription.endpoint
                                })
                            });

                            isSubscribed = false;
                            updateButtonState();
                            showToast('ℹ️ Notifikasi berhasil dimatikan', 'info');
                        }
                    } catch (error) {
                        console.error('Unsubscribe error:', error);
                        showToast('❌ Gagal mematikan notifikasi', 'error');
                        btn.disabled = false;
                        updateButtonState();
                    }
                }

                function getErrorMessage(error) {
                    if (error.name === 'NotAllowedError') {
                        return 'Izin notifikasi ditolak. Buka pengaturan browser untuk mengaktifkan.';
                    } else if (error.name === 'InvalidStateError') {
                        return 'Service Worker error. Silakan refresh halaman.';
                    } else if (error.name === 'AbortError') {
                        return 'Operasi dibatalkan.';
                    } else if (error.message.includes('NetworkError')) {
                        return 'Koneksi internet bermasalah.';
                    }
                    return error.message || 'Terjadi kesalahan';
                }

                function urlBase64ToUint8Array(base64String) {
                    const padding = '='.repeat((4 - base64String.length % 4) % 4);
                    const base64 = (base64String + padding)
                        .replace(/-/g, '+')
                        .replace(/_/g, '/');
                    const rawData = window.atob(base64);
                    const outputArray = new Uint8Array(rawData.length);
                    for (let i = 0; i < rawData.length; ++i) {
                        outputArray[i] = rawData.charCodeAt(i);
                    }
                    return outputArray;
                }

                function showToast(message, type = 'info') {
                    const colors = {
                        success: '#28a745',
                        error: '#dc3545',
                        warning: '#ffc107',
                        info: '#17a2b8'
                    };

                    const icons = {
                        success: 'check-circle',
                        error: 'exclamation-circle',
                        warning: 'exclamation-triangle',
                        info: 'info-circle'
                    };

                    const toast = document.createElement('div');
                    toast.style.cssText = `
                                position: fixed;
                                top: 20px;
                                right: 20px;
                                background: ${colors[type] || colors.info};
                                color: white;
                                padding: 12px 20px;
                                border-radius: 6px;
                                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                                z-index: 99999;
                                animation: slideIn 0.3s, fadeOut 0.5s 2.5s forwards;
                                font-weight: 500;
                                display: flex;
                                align-items: center;
                                gap: 10px;
                                max-width: 350px;
                            `;
                    toast.innerHTML = `<i class="fas fa-${icons[type] || icons.info}"></i> ${message}`;
                    document.body.appendChild(toast);

                    setTimeout(() => toast.remove(), 3000);
                }
            });
        </script>

        <style>
            @keyframes slideIn {
                from {
                    transform: translateX(100%);
                    opacity: 0;
                }

                to {
                    transform: translateX(0);
                    opacity: 1;
                }
            }

            @keyframes fadeOut {
                from {
                    opacity: 1;
                    transform: translateX(0);
                }

                to {
                    opacity: 0;
                    transform: translateX(20px);
                }
            }
        </style>
    @endauth

    <style>
        :root {
            --bs-primary: #5b73e8;
            --bs-primary-rgb: 91, 115, 232;
        }
=======
        <!-- Modals untuk setiap Divisi -->
        @foreach ($divisiStats as $index => $divisi)
            <div class="modal fade" id="modalDivisi{{ $index }}" tabindex="-1"
                aria-labelledby="modalLabel{{ $index }}" aria-hidden="true">
                <div class="modal-dialog modal-xl modal-dialog-scrollable modal-dialog-centered">
                    <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                        <div class="modal-header bg-{{ $divisi['color'] }} bg-opacity-10 border-bottom-0">
                            <h5 class="modal-title fw-bold" id="modalLabel{{ $index }}">
                                <i class="{{ $divisi['icon'] }} text-{{ $divisi['color'] }} me-2"
                                    style="font-size: 1.5rem;"></i>
                                Data Karyawan - {{ $divisi['nama'] }}
                                <span class="badge bg-{{ $divisi['color'] }} ms-3 mb-2">{{ $divisi['total'] }}
                                    orang</span>
                            </h5>
                            <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-0">
                            @if ($divisi['data']->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover table-striped align-middle mb-0">
                                        <thead class="bg-light sticky-top">
                                            <tr>
                                                <th class="ps-4" width="60">#</th>
                                                <th class="ps-4">Nama Lengkap</th>
                                                <th>NIP</th>
                                                <th>Jabatan</th>
                                                <th>Email</th>
                                                <th width="100">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($divisi['data'] as $key => $karyawan)
                                                <tr class="hover-bg">
                                                    <td class="ps-4 fw-medium">{{ $key + 1 }}</td>
                                                    <td class="ps-4">
                                                        <div class="d-flex align-items-center">
                                                            <div
                                                                class="avatar avatar-sm bg-opacity-15 rounded-circle me-3">
                                                                <span class="text-{{ $divisi['color'] }} fw-bold small">
                                                                    {{ strtoupper(substr($karyawan->nama_lengkap, 0, 1)) }}
                                                                </span>
                                                            </div>
                                                            <span class="fw-medium">{{ $karyawan->nama_lengkap }}</span>
                                                        </div>
                                                    </td>
                                                    <td><code class="small">{{ $karyawan->nip ?? '-' }}</code></td>
                                                    <td>{{ $karyawan->jabatan ?? '-' }}</td>
                                                    <td><small class="text-muted">{{ $karyawan->email ?? '-' }}</small>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-success">Aktif</span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-6 text-muted">
                                    <i class="bx bx-user-x" style="font-size: 4rem; opacity: 0.5;"></i>
                                    <p class="mt-3 fw-medium">Belum ada data karyawan di divisi ini</p>
                                </div>
                            @endif
                        </div>
                        <div class="modal-footer border-top-0 bg-light" style="padding: 6px">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                <i class="bx bx-x me-1"></i> Tutup
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
>>>>>>> 47a8b4db8a19b263d9fc2de1c3a5ba1d895f0de0

        <style>
            :root {
                --bs-primary: #5b73e8;
                --bs-primary-rgb: 91, 115, 232;
            }

            .hover-card {
                transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
                cursor: pointer;
                position: relative;
                overflow: hidden;
            }

            .hover-card::before {
                content: '';
                position: absolute;
                top: 0;
                left: -100%;
                width: 100%;
                height: 100%;
                background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.1), transparent);
                transition: 0.5s;
            }

            .hover-card:hover {
                transform: translateY(-8px) scale(1.02);
                box-shadow: 0 1rem 3rem rgba(0, 0, 0, 0.175) !important;
                z-index: 1;
            }

            .hover-card:hover::before {
                left: 100%;
            }

            .hover-bg:hover {
                background-color: rgba(91, 115, 232, 0.05) !important;
            }

            .avatar {
                display: flex;
                align-items: center;
                justify-content: center;
                font-weight: 600;
            }

            .avatar-sm {
                width: 38px;
                height: 38px;
                font-size: 0.875rem;
            }

            .avatar-md {
                width: 48px;
                height: 48px;
                font-size: 1.125rem;
            }

            .avatar-xl {
                width: 80px;
                height: 80px;
            }

            .scrollbar-custom::-webkit-scrollbar {
                width: 8px;
            }

            .scrollbar-custom::-webkit-scrollbar-track {
                background: #f1f1f1;
                border-radius: 10px;
            }

            .scrollbar-custom::-webkit-scrollbar-thumb {
                background: #c1c1c1;
                border-radius: 10px;
            }

            .scrollbar-custom::-webkit-scrollbar-thumb:hover {
                background: #a8a8a8;
            }

            .card {
                transition: all 0.3s ease;
            }

            .badge {
                font-size: 0.75rem;
                padding: 0.35em 0.65em;
            }

            @media (max-width: 768px) {
                .modal-xl {
                    --bs-modal-width: 95vw;
                }
            }

        .chart-container {
            max-height: 280px;
            overflow: hidden;
        }

        .chart-wrapper {
            height: 400px;
            position: relative;
        }
    </style>

  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {  
            const ctx = document.getElementById('kehadiranChart')?.getContext('2d');
            if (!ctx) return;

            const labels = @json($kehadiranChart['labels']);
            const data = @json($kehadiranChart['data']);
            const totalKaryawan = {{ $total_karyawan }};

            // Gradient fill
            const gradient = ctx.createLinearGradient(0, 0, 0, 320);
            gradient.addColorStop(0, 'rgba(91, 115, 232, 0.2)');
            gradient.addColorStop(1, 'rgba(91, 115, 232, 0.05)');

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Jumlah Hadir',
                        data: data,
                        borderColor: '#5b73e8',
                        backgroundColor: gradient,
                        borderWidth: 3, 
                        fill: true,
                        tension: 0.45,
                        pointRadius: 6,
                        pointHoverRadius: 9,
                        pointBackgroundColor: '#fff',
                        pointBorderColor: '#5b73e8',
                        pointBorderWidth: 3,
                        pointHoverBackgroundColor: '#5b73e8',
                        pointHoverBorderColor: '#fff',
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false
                    },
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top',
                            labels: {
                                font: {
                                    size: 14,
                                    weight: 'bold'
                                },
                                color: '#333',
                                usePointStyle: true,
                                padding: 20
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            titleColor: '#fff',
                            bodyColor: '#fff',
                            cornerRadius: 8,
                            displayColors: false,
                            padding: 12,
                            callbacks: {
                                afterLabel: function(context) {
                                    const hadir = context.parsed.y;
                                    const tidakHadir = totalKaryawan - hadir;
                                    return [
                                        '',
                                        `Tidak Hadir: ${tidakHadir}`,
                                        `Persentase: ${Math.round((hadir / totalKaryawan) * 100)}%`
                                    ];
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: totalKaryawan + 5,
                            ticks: {
                                stepSize: 1,
                                font: {
                                    size: 12
                                },
                                color: '#666'
                            },
                            grid: {
                                color: 'rgba(0, 0, 0, 0.05)',
                                drawBorder: false
                            },
                            title: {
                                display: true,
                                text: 'Jumlah Karyawan',
                                font: {
                                    size: 14,
                                    weight: 'bold'
                                },
                                color: '#333'
                            }
                        },
                        x: {
                            ticks: {
                                font: {
                                    size: 12
                                },
                                color: '#666',
                                maxRotation: 0
                            },
                            grid: {
                                display: false
                            },
                            title: {
                                display: true,
                                text: 'Tanggal',
                                font: {
                                    size: 14,
                                    weight: 'bold'
                                },
                                color: '#333'
                            }
                        }
                    },
                    animation: {
                        duration: 1500,
                        easing: 'easeOutQuart'
                    }
                }
            });

            let cuti = document.getElementById('dataCuti')?.getContext('2d');
            if (!cuti) return;

            let cutiChart = null;
            let currentFilterCuti = {
                filter: null,
                value: null,
                tahun: null,
                rentangWaktu: null
            };


            function loadDataCuti(filterType, value) {
                let tahun = $('#filterCutiTahun').val();

                if (tahun === 'default' || !tahun) {
                    tahun = new Date().getFullYear();
                }

                $.ajax({
                    url: '/office/data-cuti',
                    method: 'GET',
                    data: {
                        filter: filterType,
                        value: value,
                        tahun: tahun,
                    },
                    success: function (res) {

                        if (cutiChart) {
                            cutiChart.data.labels = res.labelCuti;
                            cutiChart.data.datasets[0].data = res.totalCuti;
                            cutiChart.update();
                        } else {
                            cutiChart = new Chart(cuti, {
                                type: 'pie',
                                data: {
                                    labels: res.labelCuti,
                                    datasets: [{
                                        label: 'Jumlah Cuti',
                                        data: res.totalCuti,
                                        backgroundColor: [
                                            'rgba(75, 192, 192, 0.8)',
                                            'rgba(255, 99, 132, 0.8)',
                                            'rgba(54, 162, 235, 0.8)',
                                            'rgba(255, 206, 86, 0.8)',
                                            'rgba(153, 102, 255, 0.8)',
                                            'rgba(255, 159, 64, 0.8)',
                                            'rgba(0, 128, 128, 0.8)',
                                        ],
                                        borderWidth: 1
                                    }]
                                },
                                options: {
                                    responsive: true,
                                    maintainAspectRatio: false,
                                    plugins: {
                                        legend: {
                                            position: 'right'
                                        }
                                    }
                                }
                            });
                        }

                        $('#rentangWaktu').text(res.rentangWaktu);

                        if (res.labelCuti.length === 0) {
                            $('#exportCuti').prop('disabled', true);
                            $('#dataCuti').addClass('d-none');
                            $('#cutiEmpty').removeClass('d-none');
                            return;
                        }

                        $('#exportCuti').prop('disabled', false);
                        $('#cutiEmpty').addClass('d-none');
                        $('#dataCuti').removeClass('d-none');

                        currentFilterCuti.filter = filterType;
                        currentFilterCuti.value = value;
                        currentFilterCuti.tahun = tahun;
                        currentFilterCuti.rentangWaktu = res.rentangWaktu;
                    },
                    error: function (xhr) {
                        console.error(xhr.responseText);
                    }
                });
            }

            // reset filter per tahun
            $('#filterCutiTahun').change(function () {
                $('#filterCutiBulan, #filterCutiTriwulan').val('default');
                loadDataCuti('tahun', $(this).val());
            });

            // reset Filter per bulan
            $('#filterCutiBulan').change(function () {
                $('#filterCutiTriwulan').val('default');
                loadDataCuti('bulan', $(this).val());
            });

            // reset filter per triwulan
            $('#filterCutiTriwulan').change(function () {
                $('#filterCutiBulan').val('default');
                loadDataCuti('triwulan', $(this).val());
            });

            // Export cuti
            $('#exportCuti').click(function () {

                $.ajax({
                    url: '/office/data-cuti',
                    method: 'GET',
                    data: {
                        filter: currentFilterCuti.filter,
                        value: currentFilterCuti.value,
                        tahun: currentFilterCuti.tahun,
                        export: 1
                    },
                    xhrFields: {
                        responseType: 'blob'
                    },
                    success: function (blob) {

                        const url = window.URL.createObjectURL(blob);
                        const a = document.createElement('a');

                        a.href = url;
                        a.download = `Laporan_Cuti_${currentFilterCuti.rentangWaktu}.pdf`;
                        document.body.appendChild(a);
                        a.click();

                        a.remove();
                        window.URL.revokeObjectURL(url);
                    },
                    error: function (err) {
                        alert('Gagal export PDF' + err);
                    }
                });
            });

            
            // Filter total mengajar
            let currentFilterMengajar = {
                filter: null,
                value: null,
                tahun: null,
                rentangWaktu: null
            };

            // Load tabel total mengajar
            function loadDataMengajar(filterType, value) {
                let tahun = $('#filterMengajarPerTahun').val();

                if (tahun === 'default' || !tahun) {
                    tahun = new Date().getFullYear();
                }
                
                $.ajax({
                    url: '/office/data-mengajar',
                    type: 'GET',
                    dataType: 'json',
                    data: {
                        filter: filterType,
                        value: value,
                        tahun: tahun
                    },
                    success: function (res) {
                        let tabelMengajar = $('#tabelTotalMengajar tbody');
                        tabelMengajar.empty();
                        let data = res.dataMengajar;

                        $.each(data, function (index, item) {
                            tabelMengajar.append(`
                                <tr>
                                    <td>${index + 1}</td>
                                    <td>${item.namaKaryawan}</td>
                                    <td>${item.kodeKaryawan}</td>
                                    <td>${item.totalMengajar}</td>
                                </tr>
                            `)
                        });

                        $('#rentangWaktuMengajar').text(res.rentangWaktu);

                        if (data.length === 0) {
                            $('#exportTotalMengajar').prop('disabled', true);
                            tabelMengajar.append(`
                                <tr>
                                   <td colspan="7" class="text-center py-5">
                                        <div class="d-flex flex-column align-items-center">
                                            <i class="bx bx-archive text-muted"
                                                style="font-size: 3rem;"></i>
                                            <p class="text-muted mt-3 mb-0">Tidak ada data total mengajar instruktur</p>
                                        </div>
                                    </td>
                                </tr>
                            `);
                            return;
                        }

                        $('#exportTotalMengajar').prop('disabled', false);

                        currentFilterMengajar.filter = filterType;
                        currentFilterMengajar.value = value;
                        currentFilterMengajar.tahun = tahun;
                        currentFilterMengajar.rentangWaktu = res.rentangWaktu;
                    },
                    error: function (err) {
                        alert(err);
                    }
                });
            };

            // reset filter per tahun
            $('#filterMengajarPerTahun').change(function () {
                $('#filterMengajarPerBulan, #filterMengajarPerTriwulan').val('default');
                loadDataMengajar('tahun', $(this).val());
            });

            // reset Filter per bulan
            $('#filterMengajarPerBulan').change(function () {
                $('#filterMengajarPerTriwulan').val('default');
                loadDataMengajar('bulan', $(this).val());
            });

            // reset filter per triwulan
            $('#filterMengajarPerTriwulan').change(function () {
                $('#filterMengajarPerBulan').val('default');
                loadDataMengajar('triwulan', $(this).val());
            });

            // Export Total Mengajar
            $('#exportTotalMengajar').click(function () {

                $.ajax({
                    url: '/office/data-mengajar',
                    method: 'GET',
                    data: {
                        filter: currentFilterMengajar.filter,
                        value: currentFilterMengajar.value,
                        tahun: currentFilterMengajar.tahun,
                        exportTotalMengajar: 1
                    },
                    xhrFields: {
                        responseType: 'blob'
                    },
                    success: function (blob) {

                        const url = window.URL.createObjectURL(blob);
                        const a = document.createElement('a');

                        a.href = url;
                        a.download = `Laporan_Total_Mengajar_${currentFilterMengajar.rentangWaktu}.pdf`;
                        document.body.appendChild(a);
                        a.click();

                        a.remove();
                        window.URL.revokeObjectURL(url);
                    },
                    error: function (xhr, status, error) {
                        console.log(xhr);
                    }
                });
            });

            // Load semua AJAX
            $(document).ready(function () {
                loadDataCuti('bulan', new Date().getMonth() + 1);
                loadDataMengajar('bulan', new Date().getMonth() + 1);
            });

            // Script Chart Feedback
            let feedbackChart;

            $(document).ready(function () {
                initChart();
                loadFeedback();

                $('#filterFeedbackTahun').change(function () {
                    resetSelect('#filterFeedbackBulan', '#filterFeedbackTriwulan');
                    loadFeedback('tahun', $(this).val());
                });

                $('#filterFeedbackBulan').change(function () {
                    resetSelect('#filterFeedbackTriwulan');
                    loadFeedback('bulan', $(this).val());
                });

                $('#filterFeedbackTriwulan').change(function () {
                    resetSelect('#filterFeedbackBulan');
                    loadFeedback('triwulan', $(this).val());
                });
            });

            function initChart() {
                const ctx = document.getElementById('feedbackChart').getContext('2d');

                feedbackChart = new Chart(ctx, {
                    type: 'pie',
                    data: {
                        labels: [],
                        datasets: [{
                            data: [],
                            backgroundColor: generateColors(30)
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'right'
                            }
                        }
                    }
                });
            }

            function loadFeedback(filter = null, value = null) {
                $.ajax({
                    url: "{{ route('office.feedback.get') }}",
                    type: "GET",
                    data: {
                        filter: filter,
                        value: value,
                        tahun: $('#filterFeedbackTahun').val()
                    },
                    success: function (res) {

                        // === DATA KOSONG ===
                        if (!res || res.length === 0) {
                            feedbackChart.data.labels = [];
                            feedbackChart.data.datasets[0].data = [];
                            feedbackChart.update();

                            $('#nilaiInstrukturEmpty').removeClass('d-none');
                            $('#exportFeedbackInstruktur').prop('disabled', true);
                            return;
                        }

                        $('#nilaiInstrukturEmpty').addClass('d-none');
                        $('#exportFeedbackInstruktur').prop('disabled', false);

                        const labels = [];
                        const data = [];

                        res.forEach(item => {
                            labels.push(item.nama_instruktur);
                            data.push(item.nilai_instruktur);
                        });

                        feedbackChart.data.labels = labels;
                        feedbackChart.data.datasets[0].data = data;
                        feedbackChart.update();
                    }
                });
            }

            function resetSelect(...selectors) {
                selectors.forEach(sel => {
                    $(sel).val('default');
                });
            }

            function generateColors(count) {
                const colors = [];
                for (let i = 0; i < count; i++) {
                    colors.push(`hsl(${i * 360 / count}, 70%, 60%)`);
                }
                return colors;
            }

            // === EXPORT PDF ===
            $('#exportFeedbackInstruktur').on('click', function () {
                let tahun = $('#filterFeedbackTahun').val();
                let bulan = $('#filterFeedbackBulan').val();
                let triwulan = $('#filterFeedbackTriwulan').val();

                let filter = '';
                let value = '';

                if (bulan && bulan !== 'default') {
                    filter = 'bulan';
                    value = bulan;
                } else if (triwulan && triwulan !== 'default') {
                    filter = 'triwulan';
                    value = triwulan;
                } else if (tahun && tahun !== 'default') {
                    filter = 'tahun';
                    value = tahun;
                }

                let url = `{{ route('office.feedbackinstrukturpdf') }}?filter=${filter}&value=${value}&tahun=${tahun}`;
                window.open(url, '_blank');
            });
            // End Script Chart Feedback
        });
    </script>
@endsection