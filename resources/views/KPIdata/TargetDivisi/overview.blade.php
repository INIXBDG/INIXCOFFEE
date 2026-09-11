@extends('layouts_kpi.app')
@section('kpi_contents')
    <link rel="preload" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"></noscript>

    <div class="container flex-grow-1 mt-4">

        {{-- ========================================================================== --}}
        {{-- 1. TAMPILAN SKELETON (Muncul saat loading awal) --}}
        {{-- ========================================================================== --}}
        <div id="overview-skeleton-view">
            {{-- Skeleton Filter Bar --}}
            <div class="filter-bar mb-4">
                <div class="row align-items-center g-3">
                    <div class="col-md-3"><div class="skeleton" style="width: 80%; height: 24px;"></div></div>
                    <div class="col-md-3"><div class="skeleton" style="width: 100%; height: 42px; border-radius: 10px;"></div></div>
                    <div class="col-md-2"><div class="skeleton" style="width: 100%; height: 42px; border-radius: 10px;"></div></div>
                    <div class="col-md-4 d-flex gap-2">
                        <div class="skeleton flex-grow-1" style="height: 42px; border-radius: 10px;"></div>
                        <div class="skeleton" style="width: 100px; height: 42px; border-radius: 10px;"></div>
                    </div>
                </div>
            </div>

            {{-- Skeleton Stat Cards --}}
            <div class="row g-3 mb-4">
                @for($i = 0; $i < 4; $i++)
                <div class="col-6 col-md-3">
                    <div class="stat-card h-100">
                        <div class="card-body d-flex justify-content-between align-items-center p-3">
                            <div style="flex: 1;">
                                <div class="skeleton mb-2" style="width: 60%; height: 14px;"></div>
                                <div class="skeleton" style="width: 40%; height: 28px;"></div>
                            </div>
                            <div class="skeleton" style="width: 48px; height: 48px; border-radius: 12px;"></div>
                        </div>
                    </div>
                </div>
                @endfor
            </div>

            {{-- Skeleton Employee Grid --}}
            <div class="content-card mb-4">
                <div class="card-body p-4">
                    <div class="skeleton mb-3" style="width: 40%; height: 20px;"></div>
                    <div class="skeleton mb-3" style="width: 60%; height: 14px;"></div>
                    <div class="emp-grid">
                        @for($i = 0; $i < 6; $i++)
                        <div class="emp-card" style="min-height: 72px; border-color: transparent; cursor: default;">
                            <div class="skeleton" style="width: 40px; height: 40px; border-radius: 50%;"></div>
                            <div class="emp-info">
                                <div class="skeleton" style="width: 70%; height: 14px; margin-bottom: 8px;"></div>
                                <div class="skeleton" style="width: 50%; height: 12px;"></div>
                            </div>
                        </div>
                        @endfor
                    </div>
                </div>
            </div>

            {{-- Skeleton Low Performance --}}
            <div class="content-card mb-4">
                <div class="card-body p-4">
                    <div class="skeleton mb-3" style="width: 30%; height: 20px;"></div>
                    <div class="skeleton mb-4" style="width: 100%; height: 55px; border-radius: 10px;"></div>
                    <div class="skeleton" style="width: 100%; height: 55px; border-radius: 10px;"></div>
                </div>
            </div>

            {{-- Skeleton Charts --}}
            <div class="row g-3 mb-4">
                <div class="col-lg-8">
                    <div class="content-card h-100">
                        <div class="card-body p-4">
                            <div class="skeleton mb-3" style="width: 30%; height: 20px;"></div>
                            <div class="skeleton" style="width: 100%; height: 380px; border-radius: 8px;"></div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="content-card h-100">
                        <div class="card-body p-4">
                            <div class="skeleton mb-3" style="width: 40%; height: 20px;"></div>
                            <div class="skeleton" style="width: 100%; height: 380px; border-radius: 8px;"></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Skeleton Target Table --}}
            <div class="content-card">
                <div class="card-body p-4">
                    <div class="skeleton mb-3" style="width: 30%; height: 20px;"></div>
                    <div class="table-responsive">
                        <table class="table modern-table align-middle mb-0" style="width: 100%;">
                            <thead>
                                <tr>
                                    <th style="width: 30%;"><div class="skeleton" style="width: 60px; height: 14px;"></div></th>
                                    <th style="width: 15%;"><div class="skeleton" style="width: 50px; height: 14px;"></div></th>
                                    <th style="width: 15%;"><div class="skeleton" style="width: 50px; height: 14px;"></div></th>
                                    <th style="width: 25%;"><div class="skeleton" style="width: 60px; height: 14px;"></div></th>
                                    <th style="width: 15%;"><div class="skeleton" style="width: 50px; height: 14px;"></div></th>
                                </tr>
                            </thead>
                            <tbody>
                                @for($i = 0; $i < 6; $i++)
                                <tr class="skeleton-row-table">
                                    <td><div class="skeleton" style="width: 80%; height: 14px;"></div></td>
                                    <td><div class="skeleton" style="width: 40%; height: 14px;"></div></td>
                                    <td><div class="skeleton" style="width: 50%; height: 14px;"></div></td>
                                    <td><div class="skeleton" style="width: 90%; height: 8px; border-radius: 4px;"></div></td>
                                    <td><div class="skeleton" style="width: 30%; height: 14px;"></div></td>
                                </tr>
                                @endfor
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- ========================================================================== --}}
        {{-- 2. TAMPILAN ASLI (Muncul setelah data selesai di-load) --}}
        {{-- ========================================================================== --}}
        <div id="overview-real-view" class="d-none">
            
            {{-- Filter Bar Asli --}}
            <div class="filter-bar">
                <form action="{{ route('kpi.overview.get') }}" method="get" id="FormFilter">
                    <input type="hidden" name="id_karyawan" value="{{ $user_id ?? auth()->id() }}" id="inputIdKaryawan">
                    <div class="row align-items-center g-3">
                        <div class="col-md-3">
                            <h5 class="fw-bold mb-0 text-dark" id="overviewTitle">
                                @if(isset($user_id) && $user_id == auth()->id())
                                    Overview Personal: {{ auth()->user()->karyawan->nama_lengkap ?? 'Anda' }}
                                    <span class="text-muted fw-normal">| {{ $divisi }}</span>
                                @else
                                    Overview Divisi {{ $divisi ?? 'Pilih Departemen' }}
                                @endif
                                {{ request('tahun', now()->year) }}
                            </h5>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" name="divisi" id="selectDivisi">
                                <option disabled>Pilih Departement</option>
                                @foreach ($departments as $data)
                                    <option value="{{ $data }}" {{ $divisi === $data ? 'selected' : '' }}>{{ $data }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select class="form-select" name="tahun" id="selectTahun">
                                <option value="">Periode Tahun</option>
                                @for ($year = 2025; $year <= now()->year; $year++)
                                    <option value="{{ $year }}" {{ request('tahun', now()->year) == $year ? 'selected' : '' }}>{{ $year }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-4 d-flex gap-2">
                            <button type="submit" class="btn btn-primary flex-grow-1">
                                <i class="fa-solid fa-filter me-1"></i> Filter
                            </button>
                            <div class="dropdown">
                                <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                    <i class="fa-solid fa-file-export me-1"></i> Export
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                    <li><a href="#" class="dropdown-item btn-export-dept" data-type="excel"><i class="fa-solid fa-file-excel text-success me-2"></i> Excel</a></li>
                                    <li><a href="#" class="dropdown-item btn-export-dept" data-type="pdf"><i class="fa-solid fa-file-pdf text-danger me-2"></i> PDF</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            {{-- 1. STAT CARDS Asli --}}
            <div class="row g-3 mb-4">
                <div class="col-6 col-md-3">
                    <div class="stat-card h-100">
                        <div class="card-body d-flex justify-content-between align-items-center p-3">
                            <div style="min-width: 0; flex: 1;">
                                <small>Total Target</small>
                                <h4 class="mb-0" id="totalTarget">0</h4>
                            </div>
                            <div class="stat-icon bg-primary bg-opacity-10 text-primary"><i class="fa-solid fa-star" aria-hidden="true"></i></div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="stat-card h-100">
                        <div class="card-body d-flex justify-content-between align-items-center p-3">
                            <div style="min-width: 0; flex: 1;">
                                <small>Rata-Rata Progress</small>
                                <h4 class="mb-0" id="rataProgress">0%</h4>
                            </div>
                            <div class="stat-icon bg-success bg-opacity-10 text-success"><i class="fa-solid fa-chart-line" aria-hidden="true"></i></div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="stat-card h-100">
                        <div class="card-body d-flex justify-content-between align-items-center p-3">
                            <div style="min-width: 0; flex: 1;">
                                <small>KPI Sedang Berjalan</small>
                                <h4 class="mb-0" id="kpiAktif">0</h4>
                            </div>
                            <div class="stat-icon bg-warning bg-opacity-10 text-warning"><i class="fa-solid fa-clock" aria-hidden="true"></i></div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="stat-card h-100">
                        <div class="card-body d-flex justify-content-between align-items-center p-3">
                            <div style="min-width: 0; flex: 1;">
                                <small>KPI Selesai</small>
                                <h4 class="mb-0" id="kpiSelesai">0</h4>
                            </div>
                            <div class="stat-icon bg-info bg-opacity-10 text-info"><i class="fa-solid fa-check" aria-hidden="true"></i></div>
                        </div>
                    </div>
                </div>
            </div>
            
            {{-- 2. EMPLOYEE GRID Asli --}}
            <div class="content-card mb-4">
                <div class="card-body p-4">
                    <h6 class="card-title mb-1" id="employeeTitle"><i class="fa-solid fa-users" aria-hidden="true"></i> Karyawan di Departemen</h6>
                    <p class="text-muted small mb-3">Klik kartu untuk melihat detail KPI karyawan</p>
                    <div id="employeeList">
                        <!-- Data di-inject oleh JS -->
                    </div>
                </div>
            </div>

            {{-- 3. LOW PERFORMANCE Asli --}}
            <div class="content-card mb-4">
                <div class="card-body p-4">
                    <h6 class="card-title mb-1"><i class="fa-solid fa-triangle-exclamation text-danger" aria-hidden="true"></i> Perlu Perhatian</h6>
                    <p class="text-muted small mb-3">Karyawan dengan progress KPI di bawah 50%</p>
                    <div id="lowPerformanceList">
                        <!-- Data di-inject oleh JS -->
                    </div>
                </div>
            </div>

            {{-- 4. CHARTS Asli --}}
            <div class="row g-3 mb-4">
                <div class="col-lg-8">
                    <div class="content-card h-100">
                        <div class="card-body p-4">
                            <h6 class="card-title mb-3"><i class="fa-solid fa-chart-bar" aria-hidden="true"></i> Statistik Karyawan</h6>
                            <div style="position: relative; height: 380px;">
                                <canvas id="kpiChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="content-card h-100">
                        <div class="card-body p-4">
                            <h6 class="card-title mb-3"><i class="fa-solid fa-chart-pie" aria-hidden="true"></i> Distribusi Nilai</h6>
                            <div style="position: relative; height: 380px;">
                                <canvas id="kpiPieChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 5. TARGET TABLE Asli --}}
            <div class="content-card">
                <div class="card-body p-4">
                    <h6 class="card-title mb-3"><i class="fa-solid fa-list-check" aria-hidden="true"></i> Daftar Target KPI</h6>
                    <div class="table-responsive">
                        <table class="table modern-table align-middle mb-0" id="targetTable" style="width: 100%;">
                            <thead>
                                <tr>
                                    <th style="width: 30%;">Judul</th>
                                    <th style="width: 15%;">Periode</th>
                                    <th style="width: 15%;">Target</th>
                                    <th style="width: 25%;">Progress</th>
                                    <th style="width: 15%;">Status</th>
                                </tr>
                            </thead>
                            <tbody id="targetTableBody">
                                <!-- Data di-inject oleh JS -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div> {{-- Akhir overview-real-view --}}

    </div>

    {{-- Export Filter Modal (Tetap sama) --}}
    <div class="modal fade" id="exportFilterModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Filter Export Data</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Tahun</label>
                        <select class="form-select" id="filterTahun"><option value="">Semua Tahun</option></select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Periode</label>
                        <select class="form-select" id="filterPeriode">
                            <option value="all">Semua Periode</option>
                            <option value="tahunan">Tahunan</option>
                            <option value="kuartalan">Kuartalan</option>
                            <option value="bulanan">Bulanan</option>
                        </select>
                    </div>
                    <div class="mb-3 d-none" id="filterQuarterWrap">
                        <label class="form-label fw-semibold">Kuartal</label>
                        <select class="form-select" id="filterQuarter">
                            <option value="1">Q1 (Jan – Mar)</option>
                            <option value="2">Q2 (Apr – Jun)</option>
                            <option value="3">Q3 (Jul – Sep)</option>
                            <option value="4">Q4 (Okt – Des)</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" id="btnApplyExport">Terapkan &amp; Export</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        window.KPI_CONFIG = {
            getOverview: "{{ route('kpi.overview.get') }}",
            exportDeptExcel: "{{ route('kpi.departement.export.excel') }}",
            exportDeptPdf: "{{ route('kpi.departement.export.pdf') }}",
            exportMonitoringExcel: "{{ route('kpi.monitoring.export.excel') }}",
            exportMonitoringPdf: "{{ route('kpi.monitoring.export.pdf') }}",
            currentYear: {{ now()->year }},
            userId: {{ auth()->id() }},
            userName: "{{ auth()->user()->karyawan->nama_lengkap ?? 'Anda' }}",
            userDivisi: "{{ auth()->user()->karyawan->divisi ?? '' }}"
        };
    </script>

    <script defer src="{{ asset('assets/js/kpi/kpiOverview.js') }}"></script>
@endsection 