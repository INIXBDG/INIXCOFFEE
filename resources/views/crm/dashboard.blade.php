@extends('layouts_crm.app')

@section('crm_contents')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row g-4 mb-4">
            <div class="col-xl-8 col-lg-7">
                <div class="card h-100 shadow-sm border-0 rounded-4 overflow-hidden">
                    <div class="card-header bg-primary py-3 px-4 d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0 text-white fw-bold">
                            Target Aktivitas Sales
                        </h5>
                        <span class="badge bg-white text-primary rounded-pill">{{ $tanggalRange }}</span>
                    </div>
                    <div class="card-body p-4">
                        <form method="GET" class="row g-2 mb-4 align-items-end pb-3 border-bottom">
                            <div class="col-md-3">
                                <label class="form-label small fw-bold text-muted mb-1">Tahun</label>
                                <select name="tahun" class="form-select form-select-sm border-light-subtle">
                                    @for ($t = now()->year; $t >= now()->year - 3; $t--)
                                        <option value="{{ $t }}" {{ $tahun == $t ? 'selected' : '' }}>
                                            {{ $t }}</option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold text-muted mb-1">Bulan</label>
                                <select name="bulan" class="form-select form-select-sm border-light-subtle">
                                    @for ($b = 1; $b <= 12; $b++)
                                        <option value="{{ $b }}" {{ $bulan == $b ? 'selected' : '' }}>
                                            {{ \Carbon\Carbon::create()->month($b)->locale('id')->translatedFormat('F') }}
                                        </option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold text-muted mb-1">Minggu</label>
                                <select name="minggu" class="form-select form-select-sm border-light-subtle">
                                    <option value="" {{ empty($mingguKe) ? 'selected' : '' }}>Semua Minggu</option>
                                    @for ($m = 1; $m <= 5; $m++)
                                        <option value="{{ $m }}" {{ $mingguKe == $m ? 'selected' : '' }}>Minggu
                                            ke {{ $m }}</option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-sm btn-primary w-100 shadow-sm">
                                    Terapkan Filter
                                </button>
                            </div>
                        </form>

                        <div class="mb-4 overflow-auto">
                            <div class="btn-group btn-group-sm mb-1" role="group">
                                <button type="button" class="btn btn-outline-primary filter-btn active"
                                    data-filter="all">Semua Sales</button>
                                @foreach ($activitysales as $sales)
                                    <button type="button" class="btn btn-outline-primary filter-btn"
                                        data-filter="{{ $sales['id_sales'] }}">{{ $sales['id_sales'] }}</button>
                                @endforeach
                            </div>
                        </div>

                        <div class="activity-container pe-2" style="max-height: 400px; overflow-y: auto;">
                            @forelse ($activitysales as $sales)
                                <div class="sales-block mb-4 p-3 rounded-3 sales-item"
                                    data-sales-id="{{ $sales['id_sales'] }}">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="avatar me-2">
                                            <span class="avatar-initial rounded-circle bg-label-primary p-2"><i
                                                    class="bx bx-user"></i></span>
                                        </div>
                                        <strong class="text-dark fs-6">{{ $sales['id_sales'] }}</strong>
                                    </div>

                                    <div class="row g-3">
                                        @php
                                            $aktivitas = [
                                                'DB' => [
                                                    'jumlah' => $sales['DB'],
                                                    'target' => $sales['target_DB'],
                                                    'warna' => 'info',
                                                    'icon' => 'bx-data',
                                                ],
                                                'Contact' => [
                                                    'jumlah' => $sales['contact'],
                                                    'target' => $sales['target_contact'],
                                                    'warna' => 'info',
                                                    'icon' => 'bx-phone-call',
                                                ],
                                                'Call' => [
                                                    'jumlah' => $sales['call'],
                                                    'target' => $sales['target_call'],
                                                    'warna' => 'info',
                                                    'icon' => 'bx-phone-incoming',
                                                ],
                                                'Email' => [
                                                    'jumlah' => $sales['email'],
                                                    'target' => $sales['target_email'],
                                                    'warna' => 'warning',
                                                    'icon' => 'bx-envelope',
                                                ],
                                                'Visit' => [
                                                    'jumlah' => $sales['visit'],
                                                    'target' => $sales['target_visit'],
                                                    'warna' => 'warning',
                                                    'icon' => 'bx-map-pin',
                                                ],
                                                'Meet' => [
                                                    'jumlah' => $sales['meet'],
                                                    'target' => $sales['target_meet'],
                                                    'warna' => 'warning',
                                                    'icon' => 'bx-group',
                                                ],
                                                'Incharge' => [
                                                    'jumlah' => $sales['incharge'],
                                                    'target' => $sales['target_incharge'],
                                                    'warna' => 'success',
                                                    'icon' => 'bx-user-check',
                                                ],
                                                'Penawaran Awal' => [
                                                    'jumlah' => $sales['PA'],
                                                    'target' => $sales['target_PA'],
                                                    'warna' => 'success',
                                                    'icon' => 'bx-file',
                                                ],
                                                'Penawaran Internal' => [
                                                    'jumlah' => $sales['PI'],
                                                    'target' => $sales['target_PI'],
                                                    'warna' => 'success',
                                                    'icon' => 'bx-detail',
                                                ],
                                                'Telemarketing' => [
                                                    'jumlah' => $sales['Telemarketing'],
                                                    'target' => $sales['target_Telemarketing'],
                                                    'warna' => 'danger',
                                                    'icon' => 'bx-headphone',
                                                ],
                                                'Form Masuk' => [
                                                    'jumlah' => $sales['Form_Masuk'],
                                                    'target' => $sales['target_Form_Masuk'],
                                                    'warna' => 'danger',
                                                    'icon' => 'bx-log-in-circle',
                                                ],
                                                'Form Keluar' => [
                                                    'jumlah' => $sales['Form_Keluar'],
                                                    'target' => $sales['target_Form_Keluar'],
                                                    'warna' => 'danger',
                                                    'icon' => 'bx-log-out-circle',
                                                ],
                                            ];
                                        @endphp

                                        @foreach ($aktivitas as $label => $data)
                                            @php
                                                $persen =
                                                    $data['target'] > 0
                                                        ? min(round(($data['jumlah'] / $data['target']) * 100), 100)
                                                        : 0;
                                            @endphp
                                            <div class="col-md-6 col-lg-4 activity-item"
                                                data-activity="{{ $label }}">
                                                <div class="p-2 border rounded-2 bg-white h-100">
                                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                                        <small class="text-muted fw-bold"
                                                            style="font-size: 0.75rem;">{{ $label }}</small>
                                                        <span
                                                            class="badge bg-{{ $data['warna'] }}-subtle text-{{ $data['warna'] }} rounded-pill"
                                                            style="font-size: 0.65rem; cursor: pointer;"
                                                            data-sales-id="{{ $sales['id_sales'] }}"
                                                            data-activity="{{ $label }}">{{ $persen }}%</span>
                                                    </div>
                                                    <div class="d-flex align-items-baseline">
                                                        <h6 class="mb-1 me-1">{{ $data['jumlah'] }}</h6>
                                                        <small class="text-muted">/{{ $data['target'] }}</small>
                                                    </div>
                                                    <div class="progress rounded-pill" style="height: 4px;">
                                                        <div class="progress-bar bg-{{ $data['warna'] }} rounded-pill"
                                                            role="progressbar" style="width: {{ $persen }}%"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-5">
                                    <img src="https://illustrations.popsy.co/gray/no-data.svg" alt="no-data"
                                        style="width: 120px;" class="mb-3">
                                    <p class="text-muted small">Tidak ada data aktivitas sales pada periode ini.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-4 col-lg-5">
                <div class="row g-4 h-100">
                    <div class="col-12">
                        <div class="card shadow-sm border-0 h-100">
                            <div
                                class="card-header d-flex justify-content-between align-items-center bg-transparent border-0">
                                <h5 class="card-title mb-0 text-primary">Data Perusahaan</h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-container" style="height: 250px;">
                                    <canvas id="kategoriChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="card shadow-sm border-0 h-100">
                            <div
                                class="card-header d-flex justify-content-between align-items-center bg-transparent border-0">
                                <h5 class="card-title mb-0 text-primary">Pembelian per Segmen</h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-container" style="height: 250px;">
                                    <canvas id="spendChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if (auth()->user()->jabatan === 'Adm Sales')
            @php
                $tasks = ['Registratsi Form', 'Surat Kontrak', 'PA', 'PO'];
            @endphp
            @php
                $taskMap = [
                    'Registratsi Form' => 'registrasi_form',
                    'Surat Kontrak' => 'surat_kontrak',
                    'PA' => 'PA',
                    'PO' => 'PO',
                ];
            @endphp

            <div class="row g-4 mb-4">
                <div class="col-md-12">
                    <div class="card shadow-sm border-0 h-100">

                        <div class="card-header bg-transparent border-0">
                            <h5 class="card-title mb-3 text-primary">Check List RKM</h5>

                            <form method="GET">
                                <div class="row g-2">

                                    <div class="col-md-3">
                                        <input type="text" name="search" value="{{ request('search') }}"
                                            class="form-control" placeholder="Search...">
                                    </div>

                                    <div class="col-md-2">
                                        <select name="bulan" class="form-select">
                                            <option value="">Bulan</option>
                                            @foreach (range(1, 12) as $bulan)
                                                <option value="{{ $bulan }}"
                                                    {{ request('bulan') == $bulan ? 'selected' : '' }}>
                                                    {{ $bulan }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-2">
                                        <select name="tahun" class="form-select">
                                            <option value="">Tahun</option>
                                            @foreach (range(date('Y') - 3, date('Y') + 1) as $tahun)
                                                <option value="{{ $tahun }}"
                                                    {{ request('tahun') == $tahun ? 'selected' : '' }}>
                                                    {{ $tahun }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-2">
                                        <select name="minggu" class="form-select">
                                            <option value="">Minggu</option>
                                            @for ($i = 1; $i <= 4; $i++)
                                                <option value="{{ $i }}"
                                                    {{ request('minggu') == $i ? 'selected' : '' }}>
                                                    Minggu {{ $i }}
                                                </option>
                                            @endfor
                                        </select>
                                    </div>

                                    <div class="col-md-2">
                                        <button class="btn btn-primary w-100">Filter</button>
                                    </div>

                                </div>
                            </form>
                        </div>

                        <div class="card-body">

                            <div class="table-responsive">
                                <table class="table table-bordered text-center align-middle" id="rkmTable">

                                    <thead class="table-light">
                                        <tr>
                                            <th>No</th>
                                            <th>RKM</th>
                                            @foreach ($tasks as $task)
                                                <th>{{ $task }}</th>
                                            @endforeach
                                        </tr>
                                    </thead>

                                    <tbody>
                                        @foreach ($dataRKM as $item)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>
                                                    <button class="btn btn-link text-decoration-none show-detail"
                                                        data-id="{{ $item->id }}"
                                                        data-materi="{{ $item->materi->nama_materi }}"
                                                        data-perusahaan="{{ $item->perusahaan->nama_perusahaan ?? '-' }}"
                                                        data-instruktur="{{ $item->instruktur->nama_lengkap ?? '-' }}"
                                                        data-tanggaltraining="{{ $item->tanggal_awal ?? '-' }} s/d {{ $item->tanggal_akhir ?? '-' }}"
                                                        data-sales="{{ $item->sales->nama_lengkap ?? '-' }}">
                                                        {{ $item->materi->nama_materi }}
                                                    </button>
                                                </td>
                                                @foreach ($tasks as $task)
                                                    @php
                                                        $field = $taskMap[$task];
                                                        $isChecked = $item->checklist->$field ?? false;
                                                    @endphp

                                                    <td>
                                                        <input type="checkbox" class="form-check-input checklist-checkbox"
                                                            data-rkm="{{ $item->id }}"
                                                            data-field="{{ $field }}"
                                                            {{ $isChecked ? 'checked' : '' }}>
                                                    </td>
                                                @endforeach
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                                <div class="mt-3">
                                    {{ $dataRKM->links() }}
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        @endif

        <div class="modal fade" id="modalDetail" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg rounded-4">

                    <div class="modal-header border-0 pb-0">
                        <h5 class="modal-title fw-semibold text-primary">
                            Detail RKM
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body pt-3">

                        <div class="row g-3">

                            <div class="col-12">
                                <div class="p-3 bg-light rounded-3">
                                    <small class="text-muted d-block">Tanggal</small>
                                    <span id="detailTanggal" class="fw-semibold"></span>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="p-3 bg-light rounded-3">
                                    <small class="text-muted d-block">Materi</small>
                                    <span id="detailMateri" class="fw-semibold"></span>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="p-3 bg-light rounded-3">
                                    <small class="text-muted d-block">Perusahaan</small>
                                    <span id="detailPerusahaan" class="fw-semibold"></span>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="p-3 bg-light rounded-3 h-100">
                                    <small class="text-muted d-block">Instruktur</small>
                                    <span id="detailInstruktur" class="fw-semibold"></span>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="p-3 bg-light rounded-3 h-100">
                                    <small class="text-muted d-block">Sales</small>
                                    <span id="detailSales" class="fw-semibold"></span>
                                </div>
                            </div>

                        </div>

                    </div>

                    {{-- FOOTER --}}
                    <div class="modal-footer border-0 pt-0">
                        <button class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">
                            Tutup
                        </button>
                    </div>

                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-md-6">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-transparent border-0">
                        <h5 class="card-title mb-0 text-primary">Top Vendor</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container" style="height: 280px;">
                            <canvas id="vendorChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-transparent border-0">
                        <h5 class="card-title mb-0 text-primary">Top Tipe Materi</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container" style="height: 280px;">
                            <canvas id="materiChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-md-6">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0 text-success fw-bold">Total Win</h5>
                        <select class="form-select form-select-sm win-year-filter border-0 bg-light" style="width: auto;"
                            hidden id="filterTahunLaporan">
                            @for ($year = now()->year - 5; $year <= now()->year + 1; $year++)
                                <option value="{{ $year }}" {{ $tahunDipilih == $year ? 'selected' : '' }}>
                                    {{ $year }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="card-body">
                        <div class="chart-container" style="height: 280px;">
                            <canvas id="totalWinChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0 text-danger fw-bold">Total Lost</h5>
                        <select class="form-select form-select-sm lost-year-filter border-0 bg-light"
                            style="width: auto;">
                            @for ($year = now()->year - 5; $year <= now()->year + 1; $year++)
                                <option value="{{ $year }}" {{ $tahunDipilih == $year ? 'selected' : '' }}>
                                    {{ $year }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="card-body">
                        <div class="chart-container" style="height: 280px;">
                            <canvas id="totalLostChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-lg-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header border-bottom bg-transparent py-3">
                        <h5 class="card-title mb-0 text-primary fw-bold">Top 5 Produk</h5>
                    </div>
                    <div class="card-body p-0">
                        <ul class="nav nav-tabs nav-fill border-0 bg-light" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active small py-2" data-bs-toggle="tab" href="#tab-terjual">Terjual
                                    (Pax)</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link small py-2" data-bs-toggle="tab" href="#tab-profit">Profit
                                    (Revenue)</a>
                            </li>
                        </ul>
                        <div class="tab-content p-3">
                            <div id="tab-terjual" class="tab-pane fade show active">
                                @forelse ($best as $item)
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <div class="text-truncate" style="max-width: 70%;">
                                            <small
                                                class="text-dark fw-medium d-block">{{ $item->materi->nama_materi ?? $item->materi_key }}</small>
                                        </div>
                                        <span
                                            class="badge bg-success-subtle text-success border border-success-subtle">{{ number_format($item->total_pax, 0, ',', '.') }}
                                            Pax</span>
                                    </div>
                                @empty
                                    <p class="text-center text-muted my-4 small">Tidak ada data.</p>
                                @endforelse
                            </div>
                            <div id="tab-profit" class="tab-pane fade">
                                @forelse ($profit as $item)
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <div class="text-truncate" style="max-width: 60%;">
                                            <small
                                                class="text-dark fw-medium d-block">{{ $item->materi->nama_materi ?? $item->materi_key }}</small>
                                        </div>
                                        <span class="text-primary fw-bold small">Rp
                                            {{ number_format($item->total_revenue, 0, ',', '.') }}</span>
                                    </div>
                                @empty
                                    <p class="text-center text-muted my-4 small">Tidak ada data.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-transparent border-bottom py-3">
                        <h5 class="card-title mb-0 text-primary fw-bold">Prospek Terbuat Minggu Ini</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive" style="max-height: 350px;">
                            <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;">
                                <thead class="bg-light text-muted">
                                    <tr>
                                        <th class="ps-4">Sales & Materi</th>
                                        <th>Harga</th>
                                        <th>Periode</th>
                                        <th>Pax</th>
                                        <th class="pe-4">Tahap</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($prospek as $item)
                                        <tr>
                                            <td class="ps-4">
                                                <span class="fw-bold text-dark d-block">{{ $item->id_sales }}</span>
                                                <small class="text-muted">{{ $item->materiRelation->nama_materi }}</small>
                                            </td>
                                            <td><span class="fw-medium">Rp
                                                    {{ number_format($item->harga, 0, ',', '.') }}</span></td>
                                            <td>
                                                @if ($item->tentatif == 1)
                                                    <span class="badge bg-warning-subtle text-warning">Tentatif</span>
                                                @else
                                                    <small>
                                                        {{ \Carbon\Carbon::parse($item->periode_mulai)->translatedFormat('d M Y') }}
                                                        -
                                                        {{ \Carbon\Carbon::parse($item->periode_selesai)->translatedFormat('d M Y') }}
                                                    </small>
                                                @endif
                                            </td>
                                            <td>{{ number_format($item->pax, 0, ',', '.') }}</td>
                                            <td class="pe-4">
                                                <span
                                                    class="badge bg-info-subtle text-info border border-info-subtle w-100">{{ strtoupper($item->tahap) }}</span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center py-4 text-muted">Belum ada prospek baru.
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

        <div class="row g-4 mb-4">
            <div class="col-12">
                <div class="card shadow-sm border-0">
                    <div
                        class="card-header bg-transparent py-3 border-bottom d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0 text-primary fw-bold">Total Status Perusahaan per Sales</h5>
                        <div class="badge bg-label-secondary text-muted">Pivot Table View</div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover table-striped align-middle mb-0" style="font-size: 0.85rem;">
                                <thead class="bg-primary text-white">
                                    <tr>
                                        <th class="ps-4 border-0">Sales Executive</th>
                                        @php $statuses = $totalStatus->pluck('status')->unique()->sort(); @endphp
                                        @foreach ($statuses as $status)
                                            <th class="text-center border-0">{{ $status }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $pivotData = [];
                                        foreach ($totalStatus as $item) {
                                            $pivotData[$item->sales_key][$item->status] = $item->total;
                                        }
                                    @endphp
                                    @forelse ($pivotData as $salesKey => $statusData)
                                        <tr>
                                            <td class="ps-4 fw-bold">{{ $salesKey }}</td>
                                            @foreach ($statuses as $status)
                                                <td class="text-center fw-medium">
                                                    @if (isset($statusData[$status]))
                                                        {{ number_format($statusData[$status], 0, ',', '.') }}
                                                    @else
                                                        <span class="text-light-emphasis">0</span>
                                                    @endif
                                                </td>
                                            @endforeach
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="{{ $statuses->count() + 1 }}" class="text-center py-4">Data
                                                tidak tersedia.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-12">
                <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
                    <div class="card-header bg-white py-3 px-4 border-0">
                        <h5 class="card-title mb-0 text-primary fw-bold">Distribusi Perusahaan per Lokasi</h5>
                    </div>
                    <div class="card-body p-0">
                        <div id="map" style="height: 450px; background-color: #f8f9fa;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="detailAktivitas" class="w3-modal" tabindex="-1" aria-hidden="true">
        <div class="w3-modal-content w3-animate-top shadow-lg"
            style="max-width: 800px; border-radius: 12px; overflow: hidden;">
            <div class="card border-0">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Detail Aktivitas Sales</h5>
                    <button type="button" class="btn-close btn-close-white"
                        onclick="document.getElementById('detailAktivitas').style.display='none'"></button>
                </div>
                <div class="card-body p-4">
                    <div class="row g-4 mb-4">
                        <div class="col-md-6 border-end">
                            <div class="mb-2"><small class="text-muted d-block">Sales Executive</small><strong
                                    id="modalSalesId" class="fs-5"></strong></div>
                            <div><small class="text-muted d-block">Aktivitas</small><strong id="modalActivity"
                                    class="text-primary fs-5"></strong></div>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <div class="mb-2"><small class="text-muted d-block">Progress Capaian</small><span
                                    id="modalPersen" class="badge bg-info p-2 fs-6"></span></div>
                            <div><small class="text-muted d-block">Realisasi / Target</small><strong><span
                                        id="modalJumlah"></span> / <span id="modalTarget"></span></strong></div>
                        </div>
                    </div>

                    <div class="progress mb-4" style="height: 12px; border-radius: 10px;">
                        <div id="modalProgressBar" class="progress-bar progress-bar-striped progress-bar-animated"
                            style="width: 0%;"></div>
                    </div>

                    <div class="table-responsive rounded-3 border">
                        <table class="table table-hover align-middle mb-0 shadow-none">
                            <thead class="bg-light">
                                <tr>
                                    <th class="small border-0">Client</th>
                                    <th class="small border-0">Tipe</th>
                                    <th class="small border-0">Deskripsi</th>
                                    <th class="small border-0">Foto</th>
                                    <th class="small border-0">Lokasi</th>
                                    <th class="small border-0 text-center">Waktu</th>
                                </tr>
                            </thead>
                            <tbody class="small">
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer border-0 text-end py-3">
                    <button type="button" class="btn btn-secondary px-4"
                        onclick="document.getElementById('detailAktivitas').style.display='none'">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="chartRKM" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Detail Data</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" id="tableChartRkm">
                            <thead>
                                <tr>
                                    <th>Nama Materi</th>
                                    <th>Perusahaan</th>
                                    <th>Sales</th>
                                    <th>Harga Jual</th>
                                    <th>Tanggal</th>
                                </tr>
                            </thead>
                            <tbody id="bodyChartRkm">
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="chartPerusahaan" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitles">Detail Data</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" id="tableChartPerusahaan">
                            <thead>
                                <tr>
                                    <th>Perusahaan</th>
                                    <th>Sales</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody id="bodyChartPerusahaan">
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="chartLaporanPenjualan" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitlesLaporan">Detail Data</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" id="tableChartLaporanPenjualan">
                            <thead>
                                <tr>
                                    <th>Perusahaan</th>
                                    <th>Materi</th>
                                    <th>Netsales</th>
                                    <th>Pax</th>
                                    <th>Total</th>
                                    <th>Waktu</th>
                                </tr>
                            </thead>
                            <tbody id="bodyChartLaporanPenjualan">
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Leaflet.js and Chart.js -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>

    <script>
        document.querySelectorAll('.show-detail').forEach(btn => {
            btn.addEventListener('click', function () {

                document.getElementById('detailMateri').innerText = this.dataset.materi;
                document.getElementById('detailPerusahaan').innerText = this.dataset.perusahaan;
                document.getElementById('detailInstruktur').innerText = this.dataset.instruktur;
                document.getElementById('detailSales').innerText = this.dataset.sales;
                document.getElementById('detailTanggal').innerText = this.dataset.tanggaltraining;

                let modal = new bootstrap.Modal(document.getElementById('modalDetail'));
                modal.show();
            });
        });

        document.querySelectorAll('.checklist-checkbox').forEach(cb => {
            cb.addEventListener('change', function() {

                fetch("{{ route('checklist.update') }}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": "{{ csrf_token() }}"
                    },
                    body: JSON.stringify({
                        rkm_id: this.dataset.rkm,
                        field: this.dataset.field,
                        value: this.checked ? 1 : 0
                    })
                });

            });
        });

        document.addEventListener('DOMContentLoaded', () => {
            // Initialize all charts with responsive container
            const initChart = (id, config) => {
                const ctx = document.getElementById(id).getContext('2d');
                return new Chart(ctx, {
                    ...config,
                    options: {
                        ...config.options,
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            ...config.options?.plugins,
                            legend: {
                                position: 'bottom',
                                labels: {
                                    font: {
                                        size: 12
                                    },
                                    padding: 10,
                                    boxWidth: 12
                                }
                            }
                        }
                    }
                });
            };

            // Kategori Chart
            const chartData = @json($chartData);
            initChart('kategoriChart', {
                type: 'doughnut',
                data: {
                    labels: chartData.map(item => item.kategori),
                    datasets: [{
                        label: 'Persentase Kategori',
                        data: chartData.map(item => item.persen),
                        backgroundColor: [
                            'rgba(75, 192, 192, 0.8)',
                            'rgba(255, 99, 132, 0.8)',
                            'rgba(54, 162, 235, 0.8)',
                            'rgba(255, 206, 86, 0.8)',
                            'rgba(153, 102, 255, 0.8)',
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    onClick: (event, elements, chart) => {
                        if (elements.length > 0) {
                            const index = elements[0].index;
                            const label = chart.data.labels[index];
                            openModalPerusahaan(label);
                        }
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: context => `${context.label}: ${context.raw}%`
                            }
                        }
                    }
                }
            });

            // Vendor Chart
            const vendorData = @json($topVendors);
            initChart('vendorChart', {
                type: 'doughnut',
                data: {
                    labels: vendorData.map(item => item.vendor),
                    datasets: [{
                        label: 'Data Vendor',
                        data: vendorData.map(item => item.total),
                        backgroundColor: [
                            'rgba(75, 192, 192, 0.8)',
                            'rgba(255, 99, 132, 0.8)',
                            'rgba(54, 162, 235, 0.8)',
                            'rgba(255, 206, 86, 0.8)',
                            'rgba(153, 102, 255, 0.8)',
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    onClick: (event, elements, chart) => {
                        if (elements.length > 0) {
                            const index = elements[0].index;
                            const label = chart.data.labels[index];
                            openModalRKM(label, 'vendor');
                        }
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: context => `${context.label}: ${context.raw}`
                            }
                        }
                    }
                }
            });

            // Materi Chart
            const materiData = @json($topKategoriMateri);
            initChart('materiChart', {
                type: 'bar',
                data: {
                    labels: materiData.map(item => item.kategori_materi),
                    datasets: [{
                        label: 'Top Tipe Materi',
                        data: materiData.map(item => item.total),
                        backgroundColor: [
                            'rgba(75, 192, 192, 0.8)',
                            'rgba(255, 99, 132, 0.8)',
                            'rgba(54, 162, 235, 0.8)',
                            'rgba(255, 206, 86, 0.8)',
                            'rgba(153, 102, 255, 0.8)',
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    onClick: (event, elements, chart) => {
                        if (elements.length > 0) {
                            const index = elements[0].index;
                            const label = chart.data.labels[index];
                            openModalRKM(label, 'materi');
                        }
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: context => `${context.label}: ${context.raw}`
                            }
                        }
                    }
                }
            });

            // Spend Chart
            const spendData = @json($topSpendSeg);
            initChart('spendChart', {
                type: 'bar',
                data: {
                    labels: spendData.map(item => item.kategori_perusahaan),
                    datasets: [{
                        label: 'Pembelian berdasarkan segmen',
                        data: spendData.map(item => item.spend),
                        backgroundColor: [
                            'rgba(75, 192, 192, 0.8)',
                            'rgba(255, 99, 132, 0.8)',
                            'rgba(54, 162, 235, 0.8)',
                            'rgba(255, 206, 86, 0.8)',
                            'rgba(153, 102, 255, 0.8)',
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    onClick: (event, elements, chart) => {
                        if (elements.length > 0) {
                            const index = elements[0].index;
                            const label = chart.data.labels[index];
                            openModalRKM(label, 'spend');
                        }
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function(context) {

                                    const index = context.dataIndex;

                                    const spend = spendData[index].spend;
                                    const total = spendData[index].total;

                                    const formatRupiah = number =>
                                        new Intl.NumberFormat('id-ID', {
                                            style: 'currency',
                                            currency: 'IDR'
                                        }).format(number);

                                    return `${context.label}: ${formatRupiah(spend)} || ${total}`;
                                }
                            }
                        }
                    }
                }
            });

            function openModalRKM(label, type) {
                const modalTitle = document.getElementById('modalTitle');
                const tableBody = document.getElementById('bodyChartRkm');

                modalTitle.innerText = `Detail: ${label}`;
                tableBody.innerHTML = '<tr><td colspan="5" class="text-center">Ditunggu ya bro</td></tr>';

                const detailModal = new bootstrap.Modal(document.getElementById('chartRKM'));
                detailModal.show();

                fetch(`/crm/chartRKM?type=${type}&key=${encodeURIComponent(label)}`)
                    .then(response => response.json())
                    .then(data => {
                        tableBody.innerHTML = '';

                        if (data.length === 0) {
                            tableBody.innerHTML =
                                '<tr><td colspan="4" class="text-center">Tidak ada data ditemukan.</td></tr>';
                            return;
                        }

                        data.forEach(item => {
                            const row = `
                                <tr>
                                    <td>${item.nama_materi}</td>
                                    <td>${item.nama_perusahaan}</td>
                                    <td>${item.sales_key}</td>
                                    <td>${new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(item.harga_jual)}</td>
                                    <td>${new Date(item.created_at).toLocaleDateString('id-ID')}</td>
                                </tr>
                            `;
                            tableBody.innerHTML += row;
                        });
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        tableBody.innerHTML =
                            '<tr><td colspan="4" class="text-center text-danger">Gagal memuat data.</td></tr>';
                    });
            }

            function openModalPerusahaan(label) {
                const tableBody = document.getElementById('bodyChartPerusahaan');
                const modalTitle = document.getElementById('modalTitles');

                modalTitle.innerText = `Daftar Perusahaan: ${label}`;
                tableBody.innerHTML = '<tr><td colspan="3" class="text-center">Sabar bro...</td></tr>';

                const myModal = new bootstrap.Modal(document.getElementById('chartPerusahaan'));
                myModal.show();

                fetch(`/crm/chartPerusahaan?key=${encodeURIComponent(label)}`)
                    .then(response => response.json())
                    .then(data => {
                        tableBody.innerHTML = '';

                        if (data.length === 0) {
                            tableBody.innerHTML =
                                '<tr><td colspan="3" class="text-center">Tidak ada data.</td></tr>';
                            return;
                        }

                        data.forEach(item => {
                            tableBody.innerHTML += `
                                <tr>
                                    <td>${item.nama_perusahaan}</td>
                                    <td>${item.sales_key ?? '-'}</td>
                                    <td>${item.status}</td>
                                </tr>
                            `;
                        });
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        tableBody.innerHTML =
                            '<tr><td colspan="3" class="text-center text-danger">Gagal mengambil data.</td></tr>';
                    });
            }

            function openModalChartLaporan(id_sales, triwulan, tahun, status) {

                const tableBody = document.getElementById('bodyChartLaporanPenjualan');
                const modalTitle = document.getElementById('modalTitlesLaporan');

                modalTitle.innerText =
                    `Detail ${status.toUpperCase()} ${id_sales.toUpperCase()}- ${triwulan} (${tahun})`;
                tableBody.innerHTML = `
                    <tr>
                        <td colspan="6" class="text-center">Sabar bro...</td>
                    </tr>
                `;

                const modal = new bootstrap.Modal(
                    document.getElementById('chartLaporanPenjualan')
                );
                modal.show();

                const url =
                    `/crm/chartClosed?id_sales=${encodeURIComponent(id_sales)}&triwulan=${encodeURIComponent(triwulan)}&tahun=${encodeURIComponent(tahun)}&status=${encodeURIComponent(status)}`;

                fetch(url)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {

                        tableBody.innerHTML = '';

                        if (data.length === 0) {
                            tableBody.innerHTML = `
                                <tr>
                                    <td colspan="6" class="text-center">Tidak ada data</td>
                                </tr>
                            `;
                            return;
                        }

                        data.forEach(item => {

                            const namaPerusahaan = item.perusahaan?.nama_perusahaan ?? '-';
                            const namaMateri = item.materi_relation?.nama_materi ?? '-';
                            const netsales = formatRupiah(item.netsales ?? 0);
                            const pax = item.pax ?? 0;
                            const total = formatRupiah(item.total ?? 0);
                            const tanggal = item.merah ?? item.lost ?? '-';

                            tableBody.innerHTML += `
                                <tr>
                                    <td>${namaPerusahaan}</td>
                                    <td>${namaMateri}</td>
                                    <td>${netsales}</td>
                                    <td>${pax}</td>
                                    <td>${total}</td>
                                    <td>${tanggal}</td>
                                </tr>
                            `;
                        });
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        tableBody.innerHTML = `
                            <tr>
                                <td colspan="6" class="text-center text-danger">
                                    Gagal mengambil data.
                                </td>
                            </tr>
                        `;
                    });
            }


            function formatRupiah(angka) {
                return new Intl.NumberFormat('id-ID').format(angka);
            }


            // Total Win Chart
            const totalWinData = @json($totalWin);
            const winLabels = ['TR1', 'TR2', 'TR3', 'TR4'];
            const winDatasets = Object.keys(totalWinData).map((id_sales, index) => {
                const sales = totalWinData[id_sales];
                const colors = [
                    'rgba(75, 192, 192, 0.8)',
                    'rgba(255, 99, 132, 0.8)',
                    'rgba(54, 162, 235, 0.8)',
                    'rgba(255, 206, 86, 0.8)',
                    'rgba(153, 102, 255, 0.8)',
                ];
                return {
                    label: sales.username.toUpperCase(),
                    data: [sales.TR1, sales.TR2, sales.TR3, sales.TR4],
                    backgroundColor: colors[index % colors.length],
                    borderWidth: 1
                };
            });

            initChart('totalWinChart', {
                type: 'bar',
                data: {
                    labels: winLabels,
                    datasets: winDatasets
                },
                options: {
                    scales: {
                        x: {
                            stacked: false
                        },
                        y: {
                            stacked: false,
                            ticks: {
                                callback: function(value) {
                                    return new Intl.NumberFormat('id-ID').format(value);
                                }
                            }
                        }
                    },
                    onClick: function(evt, elements) {
                        if (elements.length > 0) {
                            const element = elements[0];

                            const datasetIndex = element.datasetIndex;
                            const index = element.index;

                            const id_sales = Object.keys(totalWinData)[datasetIndex];
                            const triwulan = winLabels[index];
                            const tahun = $('#filterTahun').val() ?? new Date().getFullYear();

                            openModalChartLaporan(id_sales, triwulan, tahun, 'win');
                        }
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: context =>
                                    `${context.dataset.label}: ${new Intl.NumberFormat('id-ID').format(context.raw)}`
                            }
                        }
                    }
                }
            });

            // Total Lost Chart
            const totalLostData = @json($totalLost);
            const lostDatasets = Object.keys(totalLostData).map((id_sales, index) => {
                const sales = totalLostData[id_sales];
                const colors = [
                    'rgba(255, 99, 132, 0.8)',
                    'rgba(75, 192, 192, 0.8)',
                    'rgba(54, 162, 235, 0.8)',
                    'rgba(255, 206, 86, 0.8)',
                    'rgba(153, 102, 255, 0.8)',
                ];
                return {
                    label: sales.username.toUpperCase(),
                    data: [sales.TR1, sales.TR2, sales.TR3, sales.TR4],
                    backgroundColor: colors[index % colors.length],
                    borderWidth: 1
                };
            });

            initChart('totalLostChart', {
                type: 'bar',
                data: {
                    labels: winLabels,
                    datasets: lostDatasets
                },
                options: {
                    scales: {
                        x: {
                            stacked: false
                        },
                        y: {
                            stacked: false,
                            ticks: {
                                callback: function(value) {
                                    return new Intl.NumberFormat('id-ID').format(value);
                                }
                            }
                        }
                    },
                    onClick: function(evt, elements) {
                        if (elements.length > 0) {
                            const element = elements[0];

                            const datasetIndex = element.datasetIndex;
                            const index = element.index;

                            const id_sales = Object.keys(totalLostData)[datasetIndex];
                            const triwulan = winLabels[index];
                            const tahun = $('#filterTahun').val() ?? new Date().getFullYear();

                            openModalChartLaporan(id_sales, triwulan, tahun, 'lost');
                        }
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: context =>
                                    `${context.dataset.label}: ${new Intl.NumberFormat('id-ID').format(context.raw)}`
                            }
                        }
                    }
                }
            });

            // Initialize the map
            var map = L.map('map').setView([-2.548926, 118.0148634], 5); // Centered on Indonesia

            // Add OpenStreetMap tile layer
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);

            // Layer group to manage markers
            var markerLayer = L.layerGroup().addTo(map);

            // Function to update map markers
            function updateMapMarkers(salesKey) {
                // Clear existing markers
                markerLayer.clearLayers();

                // Filter locations based on sales key
                let filteredLocations = [];
                if (salesKey === 'all') {
                    filteredLocations = @json($map); // All locations
                } else {
                    filteredLocations = @json($map).filter(loc => loc.sales_key === salesKey);
                }

                // Filter out locations with no companies
                filteredLocations = filteredLocations.filter(loc => loc.company_count > 0);

                // Total number of companies for percentage calculation
                var totalCompanies = filteredLocations.reduce((sum, loc) => sum + (loc.company_count || 0), 0);

                // Add markers for each location with valid data
                filteredLocations.forEach(function(loc) {
                    if (loc.latitude && loc.longitude && loc.company_count > 0) {
                        // Calculate percentage
                        var percentage = totalCompanies > 0 ? ((loc.company_count / totalCompanies) * 100)
                            .toFixed(2) : 0;

                        // Create marker
                        var marker = L.marker([loc.latitude, loc.longitude]);

                        // Popup content
                        var popupContent = `
                            <b>Location:</b> ${loc.lokasi}<br>
                            <b>Companies:</b> ${loc.company_count}<br>
                            <b>Percentage:</b> ${percentage}% | ${loc.company_count}
                        `;
                        marker.bindPopup(popupContent);

                        // Tooltip for quick view
                        marker.bindTooltip(`${loc.lokasi}: ${percentage}%`, {
                            permanent: false
                        });

                        // Add marker to layer
                        markerLayer.addLayer(marker);
                    }
                });

                // Display message if no markers are present
                const mapContainer = document.getElementById('map');
                if (filteredLocations.length === 0) {
                    mapContainer.innerHTML =
                        '<div class="text-center text-muted p-3">Tidak ada data lokasi tersedia</div>';
                } else {
                    // Reinitialize map if it was cleared
                    if (!map._container) {
                        map = L.map('map').setView([-2.548926, 118.0148634], 5);
                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                        }).addTo(map);
                        markerLayer = L.layerGroup().addTo(map);
                        filteredLocations.forEach(function(loc) {
                            if (loc.latitude && loc.longitude && loc.company_count > 0) {
                                var marker = L.marker([loc.latitude, loc.longitude]);
                                var percentage = totalCompanies > 0 ? ((loc.company_count /
                                    totalCompanies) * 100).toFixed(2) : 0;
                                var popupContent = `
                                    <b>Location:</b> ${loc.lokasi}<br>
                                    <b>Companies:</b> ${loc.company_count}<br>
                                    <b>Percentage:</b> ${percentage}%
                                `;
                                marker.bindPopup(popupContent);
                                marker.bindTooltip(`${loc.lokasi}: ${percentage}% | ${loc.company_count}`, {
                                    permanent: false
                                });
                                markerLayer.addLayer(marker);
                            }
                        });
                    }
                }
            }

            // Initial map render
            updateMapMarkers('all');

            // Activity data from PHP
            const activityData = @json($activitysales);

            // Function to show modal with activity details
            async function showActivityDetails(salesId, activityLabel) {
                const sales = activityData.find(s => s.id_sales === salesId);
                console.log("Membuka modal →", {
                    salesId,
                    activityLabel
                });
                console.log(sales);

                const saless = activityData.find(s => s.id_sales === salesId);
                if (!sales) {
                    console.error("Sales tidak ditemukan untuk ID:", salesId);
                    alert("Data sales tidak ditemukan");
                    return;
                }

                if (!sales) return;

                const activityMap = {
                    'DB': {
                        jumlah: sales.DB,
                        target: sales.target_DB,
                        warna: 'info'
                    },
                    'Contact': {
                        jumlah: sales.contact,
                        target: sales.target_contact,
                        warna: 'info'
                    },
                    'Call': {
                        jumlah: sales.call,
                        target: sales.target_call,
                        warna: 'info'
                    },
                    'Email': {
                        jumlah: sales.email,
                        target: sales.target_email,
                        warna: 'warning'
                    },
                    'Visit': {
                        jumlah: sales.visit,
                        target: sales.target_visit,
                        warna: 'warning'
                    },
                    'Meet': {
                        jumlah: sales.meet,
                        target: sales.target_meet,
                        warna: 'warning'
                    },
                    'Incharge': {
                        jumlah: sales.incharge,
                        target: sales.target_incharge,
                        warna: 'success'
                    },
                    'Penawaran Awal': {
                        jumlah: sales.PA,
                        target: sales.target_PA,
                        warna: 'success',
                        total: sales.total_PA ?? 0
                    },
                    'Penawaran Internal': {
                        jumlah: sales.PI,
                        target: sales.target_PI,
                        warna: 'success'
                    },
                    'Telemarketing': {
                        jumlah: sales.Telemarketing,
                        target: sales.target_Telemarketing,
                        warna: 'danger'
                    },
                    'Form Masuk': {
                        jumlah: sales.Form_Masuk,
                        target: sales.target_Form_Masuk,
                        warna: 'danger',
                        total: sales.total_Form_Masuk ?? 0
                    },
                    'Form Keluar': {
                        jumlah: sales.Form_Keluar,
                        target: sales.target_Form_Keluar,
                        warna: 'danger',
                        total: sales.total_Form_Keluar ?? 0
                    },
                };

                const activity = activityMap[activityLabel];
                if (!activity) return;

                const persen = activity.target > 0 ?
                    Math.min(Math.round((activity.jumlah / activity.target) * 100), 100) :
                    0;

                // Populate modal
                document.getElementById('modalSalesId').textContent = salesId;
                document.getElementById('modalActivity').textContent = activityLabel;
                document.getElementById('modalJumlah').textContent = activity.jumlah;
                document.getElementById('modalTarget').textContent = activity.target;
                document.getElementById('modalPersen').textContent = `${persen}%`;
                document.getElementById('modalProgressBar').style.width = `${persen}%`;
                document.getElementById('modalProgressBar').className = `progress-bar bg-${activity.warna}`;

                // 🧩 Ambil data aktivitas detail dari struktur data PHP
                let activityKey = '';
                switch (activityLabel) {
                    case 'Contact':
                        activityKey = 'data_contact';
                        break;
                    case 'Call':
                        activityKey = 'data_call';
                        break;
                    case 'Email':
                        activityKey = 'data_email';
                        break;
                    case 'Visit':
                        activityKey = 'data_visit';
                        break;
                    case 'Meet':
                        activityKey = 'data_meet';
                        break;
                    case 'Incharge':
                        activityKey = 'data_incharge';
                        break;
                    case 'Penawaran Awal':
                        activityKey = 'data_PA';
                        break;
                    case 'Penawaran Internal':
                        activityKey = 'data_PI';
                        break;
                    case 'Telemarketing':
                        activityKey = 'data_Telemarketing';
                        break;
                    case 'Form Masuk':
                        activityKey = 'data_Form_Masuk';
                        break;
                    case 'Form Keluar':
                        activityKey = 'data_Form_Keluar';
                        break;
                    case 'DB':
                        activityKey = 'data_DB';
                        break;
                    default:
                        activityKey = '';
                }

                const tableBody = document.querySelector('#detailAktivitas tbody');
                tableBody.innerHTML = '';

                if (activityKey && Array.isArray(sales[activityKey])) {

                    for (const item of sales[activityKey]) {

                        let lokasi = '-';

                        try {
                            if (item.latitude && item.longitude) {

                                const response = await fetch(
                                    `https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${item.latitude}&lon=${item.longitude}&zoom=18`
                                );

                                const data = await response.json();

                                const address = data.display_name || '-';
                                const shortAddress = address.split(',').slice(0, 2).join(',');
                                const city =
                                    data.address?.city ||
                                    data.address?.town ||
                                    data.address?.village ||
                                    '';

                                lokasi = `
                                    <span title="${address}">
                                        <strong>${shortAddress}</strong>
                                    </span><br>
                                    <a href="https://www.google.com/maps?q=${item.latitude},${item.longitude}" 
                                    target="_blank" 
                                    class="ms-1 text-primary">
                                        <small class="text-muted">${city}</small>
                                    </a>
                                `;
                            }
                        } catch (error) {
                            console.error('Reverse geocoding error:', error);
                        }

                        const row = `
                            <tr>
                                <td>
                                    ${
                                        item.contact?.perusahaan
                                        ? `${item.contact.nama ?? '-'} (${item.contact.perusahaan.nama_perusahaan})`
                                        : item.contact
                                        ? `${item.contact.nama ?? '-'}`
                                        : item.peserta
                                        ? `${item.peserta.nama ?? '-'} (Peserta)`
                                        : '-'
                                    }
                                </td>
                                <td>${item.aktivitas ?? '-'}</td>
                                <td>${item.deskripsi ?? '-'}</td>
                                <td>
                                    <img src="/${item.foto_lokasi}" 
                                        style="width:50px;border-radius:5px;cursor:pointer"
                                        onclick="window.open(this.src)">
                                </td>
                                <td>${lokasi}</td>
                                <td>
                                    ${item.waktu_aktivitas
                                        ? new Date(item.waktu_aktivitas).toLocaleDateString('id-ID')
                                        : '-'}
                                </td>
                            </tr>
                        `;

                        tableBody.insertAdjacentHTML('beforeend', row);
                    }

                } else {
                    tableBody.innerHTML = `
                        <tr>
                            <td colspan="6" class="text-center text-muted py-3">
                                Tidak ada data aktivitas untuk jenis ini.
                            </td>
                        </tr>
                    `;
                }

                // Show modal
                document.getElementById('detailAktivitas').style.display = 'block';
            }

            // Attach click event to percentage badges
            document.querySelectorAll('.badge[data-sales-id][data-activity]').forEach(badge => {
                badge.addEventListener('click', () => {
                    const salesId = badge.closest('.sales-item').dataset.salesId;
                    const activityLabel = badge.closest('.activity-item').dataset.activity;
                    showActivityDetails(salesId, activityLabel);
                });
            });

            const filterButtons = document.querySelectorAll('.filter-btn');
            filterButtons.forEach(button => {
                button.addEventListener('click', () => {
                    filterButtons.forEach(btn => btn.classList.remove('active'));
                    button.classList.add('active');
                    applySalesFilter();
                });
            });

            function applySalesFilter() {
                const selectedSalesId = document.querySelector('.filter-btn.active')?.dataset.filter;
                if (!selectedSalesId) return;

                const salesItems = document.querySelectorAll('.sales-item');
                salesItems.forEach(item => {
                    item.style.display = (selectedSalesId === 'all' || item.dataset.salesId ===
                        selectedSalesId) ? 'block' : 'none';
                });
            }

            // Handle year filters
            const winYearFilter = document.querySelector('.win-year-filter');
            const lostYearFilter = document.querySelector('.lost-year-filter');

            function handleYearChange() {
                const selectedYear = this.value;
                if (this === winYearFilter) {
                    lostYearFilter.value = selectedYear;
                } else {
                    winYearFilter.value = selectedYear;
                }
                window.location.href = `?tahun=${selectedYear}`;
            }

            if (winYearFilter) winYearFilter.addEventListener('change', handleYearChange);
            if (lostYearFilter) lostYearFilter.addEventListener('change', handleYearChange);

            // Initialize filters
            applySalesFilter();
        });
    </script>

    <style>
        /* Map container styling */
        #map {
            height: 400px;
            width: 100%;
            border-radius: 0.5rem;
            border: 1px solid #e3e6f0;
            background-color: #f8f9fa;
            z-index: 1;
        }

        /* Responsive map height */
        @media (max-width: 767.98px) {
            #map {
                height: 300px;
            }

            .card-body {
                padding: 1rem !important;
            }

            .btn-group {
                width: 100%;
                flex-wrap: wrap;
            }

            .btn-group .btn {
                flex: 1 0 45%;
                margin-bottom: 5px;
            }

            .form-select-sm {
                width: 100% !important;
                max-width: 100% !important;
            }
        }

        /* Ensure Leaflet container inherits dimensions */
        .leaflet-container {
            width: 100%;
            height: 100%;
            border-radius: 0.5rem;
        }

        /* Prevent overflow in card */
        .card.h-100 {
            overflow: hidden;
        }

        /* Ensure card-body has proper spacing */
        .card-body {
            padding: 1.5rem !important;
        }

        /* Style Leaflet controls */
        .leaflet-control {
            border-radius: 0.25rem;
            background-color: rgba(255, 255, 255, 0.9);
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.1);
        }

        /* Chart and activity container styling */
        .chart-container,
        .activity-container {
            max-height: 280px;
            overflow: hidden;
        }

        /* Scrollbar styling */
        .activity-container::-webkit-scrollbar,
        .card-body::-webkit-scrollbar,
        .table-responsive::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        .activity-container::-webkit-scrollbar-track,
        .card-body::-webkit-scrollbar-track,
        .table-responsive::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 3px;
        }

        .activity-container::-webkit-scrollbar-thumb,
        .card-body::-webkit-scrollbar-thumb,
        .table-responsive::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 3px;
        }

        /* Progress bars */
        .progress {
            background-color: #f0f0f0;
            border-radius: 3px;
        }

        .progress-bar {
            border-radius: 3px;
        }

        /* Table styling */
        .table {
            margin-bottom: 0;
        }

        .table th,
        .table td {
            padding: 0.75rem;
            vertical-align: middle;
            text-align: center;
        }

        .table thead th {
            position: sticky;
            top: 0;
            background: #fff;
            z-index: 1;
            border-bottom: 2px solid #dee2e6;
        }

        /* Modal Styling */
        .w3-modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.5);
        }

        .w3-modal-content {
            background-color: #fff;
            margin: 5% auto;
            padding: 0;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            border-radius: 0.5rem;
        }

        .w3-animate-zoom {
            animation: zoom 0.3s;
        }

        @keyframes zoom {
            from {
                transform: scale(0);
            }

            to {
                transform: scale(1);
            }
        }

        .modal-body {
            padding: 1.5rem;
        }

        .modal-footer {
            padding: 1rem 1.5rem;
            border-top: 1px solid #dee2e6;
        }

        .btn-close {
            background: transparent;
            border: none;
            font-size: 1.2rem;
            cursor: pointer;
            color: #6c757d;
        }

        .btn-close:hover {
            color: #343a40;
        }

        /* Responsive modal */
        @media (max-width: 576px) {
            .w3-modal-content {
                margin: 10% auto;
                width: 95%;
            }

            .modal-body {
                padding: 1rem;
            }

            .modal-footer {
                padding: 0.75rem 1rem;
            }
        }
    </style>
@endsection
