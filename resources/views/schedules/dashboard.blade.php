@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />

<style>
    /* Reset & enforce standard Bootstrap card direction and full width */
    .card {
        display: flex !important;
        flex-direction: column !important;
        justify-content: flex-start !important;
        width: 100% !important;
        height: 100% !important;
        background-color: #ffffff !important;
        box-sizing: border-box !important;
    }
    .card-header {
        display: block !important;
        width: 100% !important;
        background-color: transparent !important;
        flex-shrink: 0 !important;
    }
    .card-body {
        display: block !important;
        width: 100% !important;
        flex: 1 1 auto !important;
    }
    .hover-elevate {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .hover-elevate:hover {
        transform: translateY(-3px);
        box-shadow: 0 0.5rem 1.25rem rgba(0, 0, 0, 0.08) !important;
    }
    .stat-icon-box {
        width: 52px;
        height: 52px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.4rem;
    }
    .chart-container-box {
        position: relative;
        min-height: 290px;
        width: 100%;
    }
    .progress-thin {
        height: 7px;
        border-radius: 10px;
    }
    .talent-badge {
        font-size: 0.78rem;
        padding: 0.35em 0.65em;
        font-weight: 500;
    }
    .list-item-hover:hover {
        background-color: #f8fafc;
    }
</style>

<div class="container-fluid py-4">
    {{-- Header Section --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div class="d-flex align-items-center">
            <a href="{{ route('content-schedules.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-sm me-3" title="Kembali ke Jadwal Konten">
                <i class="fas fa-arrow-left me-1"></i> Jadwal Konten
            </a>
            <div>
                <h4 class="fw-bold mb-0 text-dark">
                    <i class="fas fa-chart-pie me-2 text-primary"></i> Dashboard Konten Harian
                </h4>
                <p class="text-muted small mb-0">Statistik performa, tren rilis, distribusi platform, dan keterlibatan talent</p>
            </div>
        </div>

        <div class="d-flex flex-wrap align-items-center gap-2">
            <form method="GET" action="{{ route('content-schedules.dashboard') }}" id="filterForm" class="d-flex flex-wrap align-items-center gap-2">
                {{-- Period Mode Toggle --}}
                <div class="btn-group btn-group-sm shadow-sm" role="group">
                    <input type="radio" class="btn-check" name="period" id="periodYearly" value="yearly" autocomplete="off"
                        {{ ($periodMode ?? 'yearly') === 'yearly' ? 'checked' : '' }}
                        onchange="document.getElementById('filterForm').submit()">
                    <label class="btn btn-outline-secondary fw-semibold" for="periodYearly">
                        <i class="fas fa-calendar me-1"></i>Tahunan
                    </label>

                    <input type="radio" class="btn-check" name="period" id="periodMonthly" value="monthly" autocomplete="off"
                        {{ ($periodMode ?? '') === 'monthly' ? 'checked' : '' }}
                        onchange="document.getElementById('filterForm').submit()">
                    <label class="btn btn-outline-secondary fw-semibold" for="periodMonthly">
                        <i class="fas fa-calendar-week me-1"></i>Bulanan
                    </label>

                    <input type="radio" class="btn-check" name="period" id="periodWeekly" value="weekly" autocomplete="off"
                        {{ ($periodMode ?? '') === 'weekly' ? 'checked' : '' }}
                        onchange="document.getElementById('filterForm').submit()">
                    <label class="btn btn-outline-secondary fw-semibold" for="periodWeekly">
                        <i class="fas fa-calendar-day me-1"></i>Mingguan
                    </label>
                </div>

                {{-- Filter Tahun (selalu tampil) --}}
                <div class="input-group input-group-sm shadow-sm" style="min-width: 150px; max-width: 160px;">
                    <span class="input-group-text bg-white border-end-0 text-muted">
                        <i class="fas fa-calendar-alt"></i>
                    </span>
                    <select name="year" class="form-select form-select-sm border-start-0 fw-semibold"
                        onchange="document.getElementById('filterForm').submit()">
                        <option value="all" {{ ($selectedYear ?? '') === 'all' ? 'selected' : '' }}>Semua Tahun</option>
                        @foreach($availableYears as $yr)
                            <option value="{{ $yr }}" {{ ($selectedYear ?? '') == $yr ? 'selected' : '' }}>Tahun {{ $yr }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Filter Bulan (tampil jika tahun bukan 'all' dan mode monthly/weekly) --}}
                @if(($selectedYear ?? 'all') !== 'all' && ($periodMode ?? 'yearly') !== 'yearly')
                    <div class="input-group input-group-sm shadow-sm" style="min-width: 160px; max-width: 185px;">
                        <span class="input-group-text bg-white border-end-0 text-muted">
                            <i class="fas fa-calendar-week"></i>
                        </span>
                        <select name="month" class="form-select form-select-sm border-start-0 fw-semibold"
                            onchange="document.getElementById('filterForm').submit()">
                            <option value="all" {{ ($selectedMonth ?? 'all') === 'all' ? 'selected' : '' }}>Semua Bulan</option>
                            @foreach($availableMonths as $mk => $mn)
                                <option value="{{ $mk }}" {{ ($selectedMonth ?? '') === $mk ? 'selected' : '' }}>{{ $mn }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                {{-- Filter Minggu (tampil jika mode weekly dan bulan terpilih) --}}
                @if(($periodMode ?? 'yearly') === 'weekly' && ($selectedMonth ?? 'all') !== 'all')
                    <div class="input-group input-group-sm shadow-sm" style="min-width: 190px; max-width: 230px;">
                        <span class="input-group-text bg-white border-end-0 text-muted">
                            <i class="fas fa-calendar-day"></i>
                        </span>
                        <select name="week" class="form-select form-select-sm border-start-0 fw-semibold"
                            onchange="document.getElementById('filterForm').submit()">
                            <option value="all" {{ ($selectedWeek ?? 'all') === 'all' ? 'selected' : '' }}>Semua Minggu</option>
                            @foreach($availableWeeks as $wk => $wl)
                                <option value="{{ $wk }}" {{ ($selectedWeek ?? '') == $wk ? 'selected' : '' }}>{{ $wl }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
            </form>

            <button type="button" class="btn btn-primary btn-sm px-3 shadow-sm rounded-pill" data-bs-toggle="modal" data-bs-target="#createModal">
                <i class="fas fa-plus-circle me-1"></i> Tambah Jadwal
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Baris 1: 4 Kartu KPI Utama --}}
    <div class="row g-3 mb-4">
        {{-- Total Konten --}}
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm rounded-4 h-100 hover-elevate">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <span class="text-muted small fw-bold text-uppercase tracking-wider">Total Konten</span>
                        <div class="stat-icon-box bg-primary bg-opacity-10 text-primary">
                            <i class="fas fa-photo-film"></i>
                        </div>
                    </div>
                    <h2 class="fw-bold mb-1 text-dark">{{ $totalContent }}</h2>
                    <div class="d-flex align-items-center justify-content-between text-muted small mt-2">
                        <span><i class="fas fa-database me-1"></i> Terjadwal & Tayang</span>
                        <span class="badge bg-light text-dark border">100%</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Sudah Tayang --}}
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm rounded-4 h-100 hover-elevate">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <span class="text-muted small fw-bold text-uppercase tracking-wider">Sudah Tayang</span>
                        <div class="stat-icon-box bg-success bg-opacity-10 text-success">
                            <i class="fas fa-circle-check"></i>
                        </div>
                    </div>
                    <h2 class="fw-bold mb-1 text-success">{{ $uploadedCount }}</h2>
                    <div class="d-flex align-items-center justify-content-between text-muted small mt-2">
                        <span><i class="fas fa-arrow-up-right-dots me-1 text-success"></i> Completion Rate</span>
                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25">{{ $completionRate }}%</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Menunggu Upload --}}
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm rounded-4 h-100 hover-elevate">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <span class="text-muted small fw-bold text-uppercase tracking-wider">Menunggu Upload</span>
                        <div class="stat-icon-box bg-warning bg-opacity-10 text-warning">
                            <i class="fas fa-clock"></i>
                        </div>
                    </div>
                    <h2 class="fw-bold mb-1 text-warning">{{ $pendingCount }}</h2>
                    <div class="d-flex align-items-center justify-content-between text-muted small mt-2">
                        <span><i class="fas fa-hourglass-half me-1 text-warning"></i> Butuh Publikasi</span>
                        <span class="badge bg-warning bg-opacity-10 text-dark border border-warning border-opacity-25">{{ $totalContent > 0 ? round(($pendingCount / $totalContent) * 100, 1) : 0 }}%</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tayang Bulan Ini --}}
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm rounded-4 h-100 hover-elevate">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <span class="text-muted small fw-bold text-uppercase tracking-wider">Tayang Bulan Ini</span>
                        <div class="stat-icon-box bg-info bg-opacity-10 text-info">
                            <i class="fas fa-calendar-day"></i>
                        </div>
                    </div>
                    <h2 class="fw-bold mb-1 text-info">{{ $thisMonthCount }}</h2>
                    <div class="d-flex align-items-center justify-content-between text-muted small mt-2">
                        <span><i class="fas fa-calendar-alt me-1"></i> {{ now()->translatedFormat('F Y') }}</span>
                        <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25">Rilis Terbaru</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Secondary Metric Highlight Bar --}}
    <div class="card border-0 shadow-sm rounded-4 mb-4 bg-light">
        <div class="card-body py-3 px-4">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div class="d-flex align-items-center">
                    <div class="p-2 bg-dark rounded-circle text-white me-3 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                        <i class="fab fa-tiktok"></i>
                    </div>
                    <div>
                        <span class="small text-muted d-block">Sindikasi TikTok</span>
                        <span class="fw-bold text-dark">{{ $tiktokCount }} Konten Tayang</span>
                        <span class="badge bg-dark ms-2">{{ $tiktokPercentage }}% dari total</span>
                    </div>
                </div>

                <div class="vr d-none d-md-block"></div>

                <div class="d-flex align-items-center">
                    <div class="p-2 bg-primary rounded-circle text-white me-3 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                        <i class="fas fa-users"></i>
                    </div>
                    <div>
                        <span class="small text-muted d-block">Talent Terlibat</span>
                        <span class="fw-bold text-dark">{{ $totalActiveTalents }} Orang Talent</span>
                        <span class="badge bg-primary bg-opacity-10 text-primary ms-2">Kontributor Aktif</span>
                    </div>
                </div>

                <div class="vr d-none d-md-block"></div>

                <div>
                    <a href="{{ route('content-schedules.index') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3 shadow-sm">
                        <i class="fas fa-table me-1"></i> Buka Tabel Jadwal Lengkap
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Baris 2: Tren Publikasi & Komposisi Platform --}}
    <div class="row g-4 mb-4">
        {{-- Tren Bulanan --}}
        <div class="col-xl-8 col-lg-7">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="fw-bold mb-1 text-dark">
                                <i class="fas fa-chart-line text-primary me-2"></i> {{ $trendTitle }}
                            </h5>
                            <p class="text-muted small mb-0">
                                @if($periodMode === 'weekly' && $selectedMonth !== 'all')
                                    Data harian {{ $availableMonths[$selectedMonth] ?? $selectedMonth }} {{ $selectedYear !== 'all' ? $selectedYear : '' }}
                                @elseif($periodMode === 'monthly' && $selectedYear !== 'all')
                                    Frekuensi upload per bulan Tahun {{ $selectedYear }}
                                @else
                                    Frekuensi upload konten per bulan {{ $selectedYear === 'all' ? '(Seluruh Tahun)' : '(Tahun ' . $selectedYear . ')' }}
                                @endif
                            </p>
                        </div>
                        <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill">
                            Total Upload: {{ array_sum($trendData) }} Konten
                        </span>
                    </div>
                </div>
                <div class="card-body px-4 pb-4 pt-3">
                    <div class="chart-container-box">
                        <canvas id="monthlyTrendChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        {{-- Donut Distribusi Bentuk Konten --}}
        <div class="col-xl-4 col-lg-5">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                    <h5 class="fw-bold mb-1 text-dark">
                        <i class="fas fa-pie-chart text-info me-2"></i> Distribusi Bentuk Konten
                    </h5>
                    <p class="text-muted small mb-0">Komposisi format konten harian yang dirilis</p>
                </div>
                <div class="card-body px-4 pb-4 pt-3">
                    <div class="chart-container-box" style="min-height: 230px; max-height: 240px;">
                        <canvas id="contentFormChart"></canvas>
                    </div>
                    <div class="mt-3">
                        <div class="row g-2">
                            @foreach($formStats as $formName => $stat)
                                <div class="col-6">
                                    <div class="p-2 border rounded-3 bg-light d-flex align-items-center justify-content-between">
                                        <div class="d-flex align-items-center">
                                            <span class="d-inline-block rounded-circle me-2" style="width: 10px; height: 10px; background-color: {{ $stat['color'] }};"></span>
                                            <span class="small fw-semibold">{{ $formName }}</span>
                                        </div>
                                        <span class="badge bg-white text-dark border">{{ $stat['total'] }} ({{ $stat['percentage'] }}%)</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Baris 3: Leaderboard Talent & Rincian Format --}}
    <div class="row g-4 mb-4">
        {{-- Leaderboard Talent --}}
        <div class="col-xl-6 col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="fw-bold mb-1 text-dark">
                                <i class="fas fa-trophy text-warning me-2"></i> Top Talent Kontributor
                            </h5>
                            <p class="text-muted small mb-0">Talent dengan partisipasi pembuatan konten terbanyak</p>
                        </div>
                        <span class="badge bg-warning bg-opacity-10 text-dark border border-warning border-opacity-25 px-2 py-1">
                            {{ count($topTalents) }} Teratas
                        </span>
                    </div>
                </div>
                <div class="card-body px-4 pb-4 pt-3">
                    <div class="chart-container-box" style="min-height: 260px;">
                        <canvas id="talentRankingChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        {{-- Status Progres Per Format --}}
        <div class="col-xl-6 col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                    <h5 class="fw-bold mb-1 text-dark">
                        <i class="fas fa-list-check text-success me-2"></i> Rincian Progres Per Format
                    </h5>
                    <p class="text-muted small mb-0">Status tayang vs menunggu untuk setiap bentuk konten</p>
                </div>
                <div class="card-body px-4 pb-4 pt-3">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Format</th>
                                    <th class="text-center">Total</th>
                                    <th class="text-center">Tayang</th>
                                    <th class="text-center">Menunggu</th>
                                    <th>Penyelesaian</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($formStats as $formName => $stat)
                                    @php
                                        $formRate = $stat['total'] > 0 ? round(($stat['uploaded'] / $stat['total']) * 100) : 0;
                                        $badgeBg = match($formName) {
                                            'Feed' => 'bg-success text-white',
                                            'Reels' => 'bg-warning text-dark',
                                            'Tiktok' => 'bg-dark text-white',
                                            'Story' => 'bg-info text-dark',
                                            'Youtube' => 'bg-danger text-white',
                                            default => 'bg-secondary text-white'
                                        };
                                    @endphp
                                    <tr>
                                        <td>
                                            <span class="badge rounded-pill {{ $badgeBg }} px-2 py-1">
                                                {{ $formName }}
                                            </span>
                                        </td>
                                        <td class="text-center fw-bold">{{ $stat['total'] }}</td>
                                        <td class="text-center text-success fw-bold">{{ $stat['uploaded'] }}</td>
                                        <td class="text-center text-warning fw-bold">{{ $stat['pending'] }}</td>
                                        <td style="min-width: 140px;">
                                            <div class="d-flex align-items-center">
                                                <div class="progress progress-thin flex-grow-1 me-2 bg-light border">
                                                    <div class="progress-bar {{ $formRate == 100 ? 'bg-success' : 'bg-primary' }}" 
                                                         style="width: {{ $formRate }}%"></div>
                                                </div>
                                                <span class="small fw-semibold text-muted">{{ $formRate }}%</span>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Baris 4: Antrean Menunggu Upload & Konten Terakhir Tayang --}}
    <div class="row g-4">
        {{-- Antrean Konten Menunggu Upload --}}
        <div class="col-xl-7 col-lg-7">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="fw-bold mb-1 text-dark">
                                <i class="fas fa-bell text-warning me-2"></i> Antrean Konten Menunggu Upload
                            </h5>
                            <p class="text-muted small mb-0">Konten yang telah dirancang dan butuh dipublikasikan</p>
                        </div>
                        <a href="{{ route('content-schedules.index') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3 shadow-sm">
                            Kelola Semua <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
                <div class="card-body px-4 pb-4 pt-2">
                    @if($pendingQueue->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Format</th>
                                        <th>Talent</th>
                                        <th>Keterangan</th>
                                        <th class="text-center">Aksi Cepat</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($pendingQueue as $pItem)
                                        @php
                                            $pBadge = match($pItem->content_form) {
                                                'Feed' => 'bg-success text-white',
                                                'Reels' => 'bg-warning text-dark',
                                                'Tiktok' => 'bg-dark text-white',
                                                'Story' => 'bg-info text-dark',
                                                'Youtube' => 'bg-danger text-white',
                                                default => 'bg-secondary text-white'
                                            };
                                        @endphp
                                        <tr>
                                            <td>
                                                <span class="badge rounded-pill {{ $pBadge }} px-2 py-1">
                                                    {{ $pItem->content_form }}
                                                </span>
                                                @if($pItem->is_tiktok)
                                                    <i class="fab fa-tiktok ms-1 text-dark" title="Cross-post Tiktok"></i>
                                                @endif
                                            </td>
                                            <td>
                                                @if($pItem->talents)
                                                    @foreach(array_slice(explode(',', $pItem->talents), 0, 3) as $tl)
                                                        <span class="badge bg-light text-dark border talent-badge">{{ trim($tl) }}</span>
                                                    @endforeach
                                                @else
                                                    <span class="text-muted small">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="d-inline-block text-truncate small" style="max-width: 220px;" title="{{ $pItem->description }}">
                                                    {{ $pItem->description ?? 'Tidak ada keterangan' }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <form action="{{ route('content-schedules.mark-uploaded', $pItem->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="btn btn-sm btn-outline-success rounded-pill px-2 py-1" title="Tandai Sudah Upload">
                                                        <i class="fas fa-check me-1"></i> Tayang
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="d-flex flex-column align-items-center justify-content-center py-5 text-center h-100" style="min-height: 220px;">
                            <div class="d-flex align-items-center justify-content-center bg-success bg-opacity-10 text-success rounded-circle mb-3" style="width: 64px; height: 64px;">
                                <i class="fas fa-check-circle fa-2x"></i>
                            </div>
                            <h6 class="fw-bold text-dark mb-1">Semua Konten Sudah Tayang!</h6>
                            <p class="text-muted small mb-0">Tidak ada konten yang sedang menunggu upload saat ini.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Konten Terakhir Tayang --}}
        <div class="col-xl-5 col-lg-5">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="fw-bold mb-1 text-dark">
                                <i class="fas fa-history text-primary me-2"></i> Rilis Konten Terbaru
                            </h5>
                            <p class="text-muted small mb-0">5 konten terbaru yang telah terupload</p>
                        </div>
                        <span class="badge bg-light text-primary border">Terbaru</span>
                    </div>
                </div>
                <div class="card-body px-4 pb-4 pt-2">
                    @if($recentUploaded->count() > 0)
                        <div class="list-group list-group-flush w-100">
                            @foreach($recentUploaded as $rItem)
                                @php
                                    $rBadge = match($rItem->content_form) {
                                        'Feed' => 'bg-success text-white',
                                        'Reels' => 'bg-warning text-dark',
                                        'Tiktok' => 'bg-dark text-white',
                                        'Story' => 'bg-info text-dark',
                                        'Youtube' => 'bg-danger text-white',
                                        default => 'bg-secondary text-white'
                                    };
                                @endphp
                                <div class="list-group-item list-item-hover px-2 py-3 border-bottom d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center me-3">
                                        <span class="badge rounded-pill {{ $rBadge }} me-2 px-2 py-1 flex-shrink-0">
                                            {{ $rItem->content_form }}
                                        </span>
                                        <div>
                                            <div class="small fw-semibold text-truncate" style="max-width: 200px;" title="{{ $rItem->description }}">
                                                {{ $rItem->description ?: 'Konten Kreatif' }}
                                            </div>
                                            <div class="text-muted" style="font-size: 0.75rem;">
                                                <i class="far fa-user me-1"></i> {{ $rItem->talents ?: 'Tanpa Talent' }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-end flex-shrink-0">
                                        <span class="badge bg-light text-primary border" style="font-size: 0.75rem;">
                                            {{ $rItem->upload_date ? $rItem->upload_date->format('d M Y') : '-' }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="d-flex flex-column align-items-center justify-content-center py-5 text-center text-muted h-100">
                            <i class="far fa-folder-open fa-3x mb-2 text-muted"></i>
                            <p class="small mb-0">Belum ada konten yang diupload.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

{{-- MODAL CREATE --}}
<div class="modal fade" id="createModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form action="{{ route('content-schedules.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">
                        <i class="fas fa-plus-circle text-primary me-2"></i>Tambah Jadwal Baru
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Bentuk Konten</label>
                            <select name="content_form" class="form-select" required>
                                <option value="Feed">Feed</option>
                                <option value="Reels">Reels</option>
                                <option value="Tiktok">Tiktok</option>
                                <option value="Story">Story</option>
                                <option value="Youtube">Youtube</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Talent (Pilih Multiple atau Ketik Manual)</label>
                            <select name="talents[]" id="create_talents" class="form-select" multiple style="width: 100%;">
                                @foreach($uniqueTalents as $talent)
                                    <option value="{{ $talent }}">{{ $talent }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Deskripsi</label>
                            <textarea name="description" class="form-control" rows="2" placeholder="Catatan atau judul ringkas konten..."></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Script Konten</label>
                            <textarea name="proof_script" class="form-control" rows="4" placeholder="Naskah atau narasi konten..."></textarea>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">Bukti Screenshot</label>
                            <input type="file" name="proof_image" class="form-control" accept="image/*">
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" name="is_tiktok" value="1" id="isTiktokCreate">
                                <label class="form-check-label fw-bold" for="isTiktokCreate">Upload ke Tiktok?</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm">Simpan Jadwal</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    $(document).ready(function() {
        // Inisialisasi Select2 pada Modal Create
        $('#createModal').on('shown.bs.modal', function () {
            $('#create_talents').select2({
                theme: 'bootstrap-5',
                dropdownParent: $('#createModal'),
                tags: true,
                placeholder: "Pilih atau ketik talent baru...",
                allowClear: true,
                width: '100%'
            });
        });

        // 1. Chart Tren Publikasi
        const monthlyCtx = document.getElementById('monthlyTrendChart');
        if (monthlyCtx) {
            const gradientBg = monthlyCtx.getContext('2d').createLinearGradient(0, 0, 0, 300);
            gradientBg.addColorStop(0, 'rgba(13, 110, 253, 0.28)');
            gradientBg.addColorStop(1, 'rgba(13, 110, 253, 0.01)');

            new Chart(monthlyCtx, {
                type: 'line',
                data: {
                    labels: @json($trendLabels),
                    datasets: [{
                        label: 'Konten Tayang',
                        data: @json($trendData),
                        borderColor: '#0d6efd',
                        backgroundColor: gradientBg,
                        borderWidth: 3,
                        fill: true,
                        tension: 0.38,
                        pointBackgroundColor: '#0d6efd',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                        pointRadius: 5,
                        pointHoverRadius: 7
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.parsed.y + ' Konten Terupload';
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1,
                                precision: 0
                            },
                            grid: {
                                color: 'rgba(0, 0, 0, 0.05)'
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });
        }

        // 2. Chart Distribusi Bentuk Konten (Donut)
        const formCtx = document.getElementById('contentFormChart');
        if (formCtx) {
            new Chart(formCtx, {
                type: 'doughnut',
                data: {
                    labels: @json($formChartLabels),
                    datasets: [{
                        data: @json($formChartData),
                        backgroundColor: [
                            '#198754', // Feed
                            '#ffc107', // Reels
                            '#212529', // Tiktok
                            '#0dcaf0', // Story
                            '#dc3545'  // Youtube
                        ],
                        borderWidth: 2,
                        borderColor: '#ffffff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '70%',
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                boxWidth: 12,
                                padding: 15,
                                font: { size: 11, weight: '600' }
                            }
                        }
                    }
                }
            });
        }

        // 3. Chart Leaderboard Talent (Horizontal Bar)
        const talentCtx = document.getElementById('talentRankingChart');
        if (talentCtx) {
            const talentLabels = @json(array_keys($topTalents));
            const talentData = @json(array_values($topTalents));

            new Chart(talentCtx, {
                type: 'bar',
                data: {
                    labels: talentLabels,
                    datasets: [{
                        label: 'Jumlah Konten',
                        data: talentData,
                        backgroundColor: 'rgba(255, 193, 7, 0.85)',
                        borderColor: '#ffc107',
                        borderWidth: 1,
                        borderRadius: 8
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.parsed.x + ' Konten';
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1,
                                precision: 0
                            },
                            grid: {
                                color: 'rgba(0, 0, 0, 0.05)'
                            }
                        },
                        y: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });
        }
    });
</script>
@endsection
