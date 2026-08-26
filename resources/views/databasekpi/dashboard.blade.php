@extends('layouts_kpi.app')

@section('kpi_contents')
    <style>
        .btn-plain {
            all: unset;
            cursor: pointer;
            display: block;
            padding: 0;
        }

        #totalPenilaianChart {
            max-height: 250px;
        }

        .loading-spinner {
            width: 60px;
            height: 60px;
            border: 6px solid transparent;
            border-top: 6px solid #a78bfa;
            border-right: 6px solid #38bdf8;
            border-bottom: 6px solid #34d399;
            border-left: 6px solid #facc15;
            border-radius: 50%;
            animation: spin 1.2s linear infinite;
            margin: auto;
        }

        @keyframes spin {
            100% { transform: rotate(360deg); }
        }

        @media only screen and (max-width:800px) {
            .doughnutjs-wrapper {
                width: 100%;
                max-width: 400px;
                height: auto;
            }
            canvas#myChart {
                width: 100% !important;
                height: 100% !important;
            }
            .card-trafic {
                max-height: none;
                height: 300px;
            }
        }

        .card-trafic {
            max-height: 170px;
            overflow-x: hidden;
        }

        @media (max-width: 768px) {
            #select_peringkatPenilaian {
                width: 100% !important;
            }
        }

        #btn_exportPDF_rangking {
            min-width: 50px;
        }

        .card-podium-1 { transform: scale(1.05); }
        .card-podium-2 { transform: scale(0.95); }
        .card-podium-3 { transform: scale(0.9); }

        #contentKPIDivisi::-webkit-scrollbar { height: 6px; }
        #contentKPIDivisi::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 3px; }
        #contentKPIDivisi::-webkit-scrollbar-thumb { background: #c1c1c1; border-radius: 3px; }
        #contentKPIDivisi::-webkit-scrollbar-thumb:hover { background: #a8a8a8; }
        #contentKPIDivisi { -webkit-overflow-scrolling: touch; }

        .legend-box {
            display: inline-block;
            width: 15px;
            height: 15px;
            border-radius: 3px;
        }

        .plain-card {
            background: #fff;
            border: 0;
            border-radius: 1rem;
            box-shadow: 0 2px 12px rgba(0,0,0,.06);
            transition: box-shadow .2s ease;
        }
        .plain-card:hover {
            box-shadow: 0 4px 20px rgba(0,0,0,.08);
        }
        .plain-card .card-body { padding: 1.5rem; }

        .stat-icon {
            width: 48px; height: 48px;
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.4rem;
        }
        .stat-icon.aktif   { background: rgba(99,102,241,.12); color: #6366f1; }
        .stat-icon.sakit   { background: rgba(14,165,233,.12); color: #0ea5e9; }
        .stat-icon.izin    { background: rgba(16,185,129,.12); color: #10b981; }
        .stat-icon.cuti    { background: rgba(245,158,11,.12); color: #f59e0b; }

        .stat-badge {
            font-size: .7rem;
            font-weight: 600;
            padding: .35rem .7rem;
            border-radius: 999px;
        }
        .stat-badge.aktif { background: rgba(99,102,241,.1);  color: #6366f1; }
        .stat-badge.sakit { background: rgba(14,165,233,.1);  color: #0ea5e9; }
        .stat-badge.izin  { background: rgba(16,185,129,.1);  color: #10b981; }
        .stat-badge.cuti  { background: rgba(245,158,11,.1);  color: #f59e0b; }

        .podium-1 { background: linear-gradient(135deg,#fde68a,#f59e0b); }
        .podium-2 { background: linear-gradient(135deg,#e0e7ff,#a5b4fc); }
        .podium-3 { background: linear-gradient(135deg,#fed7aa,#fb923c); }

        .progress-bar.bg-danger-soft { background-color: #f59e0b !important; }

        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #9ca3af; }

        .kpi-summary-box {
            border-radius: 14px;
            padding: 1rem 1.2rem;
            background: #f8fafc;
            height: 100%;
        }

        .kpi-mini-card {
            border-radius: 12px;
            background: #f8fafc;
            padding: 1rem;
        }

        .sparkline {
            display: flex;
            align-items: flex-end;
            gap: 3px;
            height: 32px;
        }

        .sparkline-bar {
            flex: 1;
            background: linear-gradient(180deg,#a78bfa,#6366f1);
            border-radius: 3px 3px 0 0;
            min-height: 3px;
        }

        .health-legend-item {
            display: flex;
            align-items: center;
            gap: .5rem;
            font-size: .85rem;
        }

        .chip-strength {
            background: rgba(16,185,129,.1);
            color: #059669;
            border-radius: 999px;
            padding: .35rem .8rem;
            font-size: .8rem;
            font-weight: 600;
            display: inline-block;
            margin: .2rem;
        }

        .chip-growth {
            background: rgba(245,158,11,.1);
            color: #d97706;
            border-radius: 999px;
            padding: .35rem .8rem;
            font-size: .8rem;
            font-weight: 600;
            display: inline-block;
            margin: .2rem;
        }
    </style>

    <div class="container flex-grow-1 mt-4">
        <div class="content-wrapper">

            <div class="row g-4 mb-4">
                <div class="col-md-6 col-xl-3 stretch-card d-flex">
                    <div class="plain-card flex-fill w-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div class="stat-icon aktif"><i class="ti-user"></i></div>
                                <span class="stat-badge aktif">Aktif</span>
                            </div>
                            <h6 class="text-muted mb-1" style="font-size:.85rem;">Jumlah Karyawan Aktif</h6>
                            <h3 class="fw-bold mb-0 text-dark">
                                <span id="content_JK">
                                    <div class="spinner-border text-primary" role="status" style="width:1.5rem;height:1.5rem;"></div>
                                </span>
                            </h3>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-xl-3 stretch-card d-flex">
                    <div class="plain-card flex-fill w-100"
                         data-bs-toggle="modal" data-bs-target="#modalSakit" style="cursor:pointer;">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div class="stat-icon sakit"><i class="ti-medall"></i></div>
                                <span class="stat-badge sakit">Semester</span>
                            </div>
                            <h6 class="text-muted mb-1" style="font-size:.85rem;">
                                @if (auth()->user()->jabatan === 'HRD' || auth()->user()->jabatan === 'GM' || auth()->user()->jabatan === 'Direktur Utama')
                                    Sakit Dalam Semester Ini
                                @else
                                    Data Sakit Anda Semester Ini
                                @endif
                            </h6>
                            <h3 class="fw-bold mb-0 text-dark">
                                <span id="content_KS">
                                    <div class="spinner-border text-primary" role="status" style="width:1.5rem;height:1.5rem;"></div>
                                </span>
                            </h3>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-xl-3 stretch-card d-flex">
                    <div class="plain-card flex-fill w-100"
                         data-bs-toggle="modal" data-bs-target="#modalIzin" style="cursor:pointer;">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div class="stat-icon izin"><i class="ti-check-box"></i></div>
                                <span class="stat-badge izin">Triwulan</span>
                            </div>
                            <h6 class="text-muted mb-1" style="font-size:.85rem;">
                                @if (auth()->user()->jabatan === 'HRD' || auth()->user()->jabatan === 'GM' || auth()->user()->jabatan === 'Direktur Utama')
                                    Izin Dalam Triwulan Ini
                                @else
                                    Data Izin Anda Semester Ini
                                @endif
                            </h6>
                            <h3 class="fw-bold mb-0 text-dark">
                                <span id="content_KI">
                                    <div class="spinner-border text-primary" role="status" style="width:1.5rem;height:1.5rem;"></div>
                                </span>
                            </h3>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-xl-3 stretch-card d-flex">
                    <div class="plain-card flex-fill w-100"
                         data-bs-toggle="modal" data-bs-target="#modalCuti" style="cursor:pointer;">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div class="stat-icon cuti"><i class="ti-calendar"></i></div>
                                <span class="stat-badge cuti">Triwulan</span>
                            </div>
                            <h6 class="text-muted mb-1" style="font-size:.85rem;">
                                @if (auth()->user()->jabatan === 'HRD' || auth()->user()->jabatan === 'GM' || auth()->user()->jabatan === 'Direktur Utama')
                                    Cuti Dalam Triwulan Ini
                                @else
                                    Data Cuti Anda Semester Ini
                                @endif
                            </h6>
                            <h3 class="fw-bold mb-0 text-dark">
                                <span id="content_KC">
                                    <div class="spinner-border text-primary" role="status" style="width:1.5rem;height:1.5rem;"></div>
                                </span>
                            </h3>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4 mb-4" id="companyProgressRow" style="display:none;">
                <div class="col-xl-7 d-flex">
                    <div class="plain-card flex-fill">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-1 flex-wrap gap-2">
                                <div>
                                    <h5 class="fw-bold text-dark mb-1">Tren Progress Perusahaan</h5>
                                    <p class="text-muted small mb-0">Garis putus-putus adalah proyeksi 2 bulan ke depan</p>
                                </div>
                            </div>
                            <div style="height:280px;" class="mt-3">
                                <canvas id="companyProgressChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-5 d-flex">
                    <div class="plain-card flex-fill">
                        <div class="card-body">
                            <h5 class="fw-bold text-dark mb-1">Ringkasan Divisi</h5>
                            <p class="text-muted small mb-3">Klik divisi untuk lihat detail tim</p>
                            <div id="companyDivisiOverview" class="d-flex flex-column gap-2" style="max-height:300px; overflow-y:auto;"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4 mb-4 align-items-stretch">
                @if (auth()->user()->jabatan === 'HRD' ||
                        auth()->user()->jabatan === 'GM' ||
                        auth()->user()->jabatan === 'Direktur Utama')

                    <div class="col-xl-7 d-flex flex-column gap-4">

                        <div class="plain-card flex-fill">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                                    <h6 class="mb-0 fw-bold text-dark">
                                        <i class="mdi mdi-trophy-variant mdi-18px text-warning me-1"></i> Terbaik Divisi
                                    </h6>
                                    <form action="{{ route('databaseKPI.downloadDivisi') }}" method="post"
                                          class="d-flex" style="min-width:220px;">
                                        @csrf
                                        <div class="input-group">
                                            <select class="form-select bg-white text-dark" id="select_peringkatPenilaian" name="divisi">
                                                <option>Education</option>
                                            </select>
                                            <button class="btn btn-warning text-white" type="submit" id="btn_exportPDF_rangking">
                                                <i class="fa-solid fa-file-pdf"></i>
                                            </button>
                                        </div>
                                        <input type="hidden" name="quartal" value="Q{{ ceil(date('m') / 3) }}">
                                        <input type="hidden" name="tahun" value="{{ date('Y') }}">
                                    </form>
                                </div>

                                <div class="row justify-content-center align-items-end text-center row-ranking"
                                     style="min-height:300px;">
                                    <div class="loader" id="loader">
                                        <div class="bubble"></div>
                                        <div class="bubble"></div>
                                        <div class="bubble"></div>
                                        <div class="bubble"></div>
                                    </div>
                                </div>

                                <div class="text-center mt-3">
                                    <small class="text-muted fst-italic">*hasil diambil dari penilaian 360°, tidak termasuk yang lainnya</small>
                                </div>
                                <div class="text-center mt-3">
                                    <button type="button" class="btn btn-primary rounded-pill px-4"
                                            data-bs-toggle="modal" data-bs-target="#modalPeringkatPenilaian360">
                                        lihat semua
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="plain-card flex-fill">
                            <div class="card-body">
                                <h5 class="fw-bold text-dark mb-1">Assessment 360°</h5>
                                <p class="text-muted small mb-3">Radar penilaian berdasarkan jenis evaluator</p>
                                <div id="contentKPIDivisi">
                                    <div class="d-flex justify-content-center align-items-center w-100 text-muted py-4">
                                        <div class="spinner-border text-secondary me-2" style="width:1.5rem;height:1.5rem;"></div>
                                        <span class="small">Memuat data...</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-5 d-flex flex-column">
                        <div class="plain-card flex-fill h-100">
                            <div class="card-body">
                                <h5 class="fw-bold text-dark mb-4">Chart Penilaian 360°</h5>
                                <div id="containerChartPenilaian" class="position-relative" style="min-height: 250px;">
                                    <div id="loadingPenilaian"
                                         class="position-absolute top-0 start-0 w-100 h-100 d-flex flex-column justify-content-center align-items-center bg-white"
                                         style="z-index: 10; border-radius: 1rem;">
                                        <div class="spinner-border text-primary" role="status"></div>
                                        <small class="text-muted mt-2">Memuat penilaian...</small>
                                    </div>
                                    <div id="emptyPenilaian"
                                         class="position-absolute top-0 start-0 w-100 h-100 d-none flex-column justify-content-center align-items-center bg-white"
                                         style="z-index: 10; border-radius: 1rem;">
                                        <i class="fas fa-chart-pie fs-1 text-muted opacity-50"></i>
                                        <p class="text-muted small mt-2 mb-0">Belum ada data penilaian</p>
                                    </div>
                                    <div id="contentPenilaian">
                                        <p id="title_chartPenilaian" class="text-center text-muted small mb-3"></p>
                                        <div class="doughnutjs-wrapper d-flex justify-content-center">
                                            <canvas id="myChart"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                @else
                    <div class="col-xl-5 d-flex flex-column">
                        <div class="plain-card flex-fill h-100">
                            <div class="card-body">
                                <h5 class="fw-bold text-dark mb-4">Chart Penilaian 360°</h5>
                                <div id="containerChartPenilaian" class="position-relative" style="min-height: 250px;">
                                    <div id="loadingPenilaian"
                                         class="position-absolute top-0 start-0 w-100 h-100 d-flex flex-column justify-content-center align-items-center bg-white"
                                         style="z-index: 10; border-radius: 1rem;">
                                        <div class="spinner-border text-primary" role="status"></div>
                                        <small class="text-muted mt-2">Memuat penilaian...</small>
                                    </div>
                                    <div id="emptyPenilaian"
                                         class="position-absolute top-0 start-0 w-100 h-100 d-none flex-column justify-content-center align-items-center bg-white"
                                         style="z-index: 10; border-radius: 1rem;">
                                        <i class="fas fa-chart-pie fs-1 text-muted opacity-50"></i>
                                        <p class="text-muted small mt-2 mb-0">Belum ada data penilaian</p>
                                    </div>
                                    <div id="contentPenilaian">
                                        <p id="title_chartPenilaian" class="text-center text-muted small mb-3"></p>
                                        <div class="doughnutjs-wrapper d-flex justify-content-center">
                                            <canvas id="myChart"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-7 d-flex flex-column">
                        <div class="plain-card flex-fill h-100">
                            <div class="card-body d-flex flex-column justify-content-center" id="contentKPIPersonal">
                                <div class="text-center py-5">
                                    <div class="spinner-border text-secondary mb-3" style="width:2rem;height:2rem;"></div>
                                    <div class="small text-muted">Memuat data KPI...</div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <div class="row g-4 mb-4 align-items-stretch">
                @if (auth()->user()->jabatan === 'HRD' ||
                        auth()->user()->jabatan === 'GM' ||
                        auth()->user()->jabatan === 'Direktur Utama')

                    <div class="col-lg-6 d-flex flex-column">
                        <div class="plain-card flex-fill h-100">
                            <div class="card-body">
                                <h5 class="fw-bold text-dark mb-1">Data Formulir</h5>
                                <p class="text-muted small mb-4">Distribusi status karyawan</p>
                                <div class="row mt-2 align-items-center">
                                    <div class="col-sm-6 position-relative" style="min-height: 200px;">
                                        <div id="loadingFormulir"
                                             class="position-absolute top-0 start-0 w-100 h-100 d-flex flex-column justify-content-center align-items-center bg-white"
                                             style="z-index: 10; border-radius: 1rem;">
                                            <div class="spinner-border text-primary"></div>
                                        </div>
                                        <canvas id="doughnutCharthr" height="200"></canvas>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="row mb-3">
                                            <div class="col-6" id="totalFormulir"></div>
                                            <div class="col-6" id="totalRutin"></div>
                                        </div>
                                        <div class="row">
                                            <div class="col-6" id="totalProbation"></div>
                                            <div class="col-6" id="totalKontrak"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6 d-flex flex-column">
                        <div class="plain-card flex-fill h-100">
                            <div class="card-body d-flex flex-column">
                                <div class="mb-3">
                                    <h5 class="fw-bold text-dark mb-1">Insight KPI</h5>
                                    <small class="text-muted">Kesehatan target, tren, dan sorotan performa</small>
                                </div>
                                <div class="flex-grow-1 overflow-auto" style="max-height: 480px;">
                                    <div class="d-flex flex-column gap-4" id="contentKPITim">
                                        <div class="text-center py-5">
                                            <div class="spinner-border text-secondary mb-3"></div>
                                            <div class="small text-muted">Memuat data KPI...</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                @elseif (auth()->user()->jabatan === 'Koordinator ITSM')

                    <div class="col-lg-6 d-flex flex-column">
                        <div class="plain-card flex-fill h-100">
                            <div class="card-body d-flex flex-column">
                                <div class="mb-3">
                                    <h5 class="fw-bold text-dark mb-1">Insight KPI</h5>
                                    <small class="text-muted">Kesehatan target, tren, dan sorotan performa</small>
                                </div>
                                <div class="flex-grow-1 overflow-auto" style="max-height: 480px;">
                                    <div class="d-flex flex-column gap-4" id="contentKPITim">
                                        <div class="text-center py-5">
                                            <div class="spinner-border text-secondary mb-3"></div>
                                            <div class="small text-muted">Memuat data KPI...</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6 d-flex flex-column">
                        <div class="plain-card flex-fill h-100">
                            <div class="card-body">
                                <h5 class="fw-bold text-dark mb-1">Data Formulir</h5>
                                <p class="text-muted small mb-4">Distribusi status karyawan</p>
                                <div class="row mt-2 align-items-center">
                                    <div class="col-sm-6 position-relative" style="min-height: 200px;">
                                        <div id="loadingFormulir"
                                             class="position-absolute top-0 start-0 w-100 h-100 d-flex flex-column justify-content-center align-items-center bg-white"
                                             style="z-index: 10; border-radius: 1rem;">
                                            <div class="spinner-border text-primary"></div>
                                        </div>
                                        <canvas id="doughnutCharthr" height="200"></canvas>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="row mb-3">
                                            <div class="col-6" id="totalFormulir"></div>
                                            <div class="col-6" id="totalRutin"></div>
                                        </div>
                                        <div class="row">
                                            <div class="col-6" id="totalProbation"></div>
                                            <div class="col-6" id="totalKontrak"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

        </div>
    </div>

    <div class="modal fade" id="modalSakit" tabindex="-1" aria-labelledby="modalSakitLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="modalSakitLabel">Data Sakit Semester Ini</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center py-4" id="loadingModalSakit">
                        <div class="spinner-border text-primary" role="status"></div>
                        <p class="mt-2 text-muted small">Memuat data sakit...</p>
                    </div>
                    <div id="contentModalSakit" class="d-none">
                        <p class="text-muted text-center">Data sakit akan ditampilkan di sini.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalIzin" tabindex="-1" aria-labelledby="modalIzinLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="modalIzinLabel">Data Izin Triwulan Ini</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center py-4" id="loadingModalIzin">
                        <div class="spinner-border text-primary" role="status"></div>
                        <p class="mt-2 text-muted small">Memuat data izin...</p>
                    </div>
                    <div id="contentModalIzin" class="d-none">
                        <p class="text-muted text-center">Data izin akan ditampilkan di sini.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalCuti" tabindex="-1" aria-labelledby="modalCutiLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="modalCutiLabel">Data Cuti Triwulan Ini</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center py-4" id="loadingModalCuti">
                        <div class="spinner-border text-primary" role="status"></div>
                        <p class="mt-2 text-muted small">Memuat data cuti...</p>
                    </div>
                    <div id="contentModalCuti" class="d-none">
                        <p class="text-muted text-center">Data cuti akan ditampilkan di sini.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalDivisiDrilldown" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="drilldownDivisiTitle">Detail Divisi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center py-5" id="drilldownLoading">
                        <div class="spinner-border text-primary" role="status"></div>
                        <p class="mt-2 text-muted small">Memuat detail divisi...</p>
                    </div>
                    <div id="drilldownContent" class="d-none">
                        <div class="row g-4">
                            <div class="col-lg-6">
                                <h6 class="fw-bold text-dark mb-2">Tren Progress Divisi</h6>
                                <div style="height:220px;">
                                    <canvas id="drilldownChart"></canvas>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <h6 class="fw-bold text-dark mb-2">Insight</h6>
                                <div id="drilldownInsights" class="d-flex flex-column gap-2" style="max-height:220px; overflow-y:auto;"></div>
                            </div>
                        </div>
                        <hr>
                        <h6 class="fw-bold text-dark mb-3">Tim di Divisi Ini</h6>
                        <div id="drilldownTeamList" class="d-flex flex-column gap-2" style="max-height:320px; overflow-y:auto;"></div>
                    </div>
                    <div id="drilldownEmpty" class="d-none text-center py-5 text-muted">
                        Belum ada data KPI untuk divisi ini.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        let chart1, chart2;
        let dashboardData = null;

        $(document).ready(function() {
            loadData();
            loadProgressData();
            fetchChartStatistics();

            $('#modalSakit').on('shown.bs.modal', function() { renderModalData('Sakit'); });
            $('#modalIzin').on('shown.bs.modal', function() { renderModalData('Izin'); });
            $('#modalCuti').on('shown.bs.modal', function() { renderModalData('Cuti'); });

            $(document).on('click', '#jabatanPills .btn', function() {
                const filterValue = $(this).data('filter');
                setJabatanFilter(filterValue);
            });

            $(document).on('click', '#bulanPills .btn', function() {
                const bulanValue = $(this).data('bulan');
                setBulanFilter(bulanValue);
            });

            $(document).on('click', '#refreshChartBtn', function() {
                fetchChartStatistics();
            });
        });

        function loadData() {
            $("#loadingFormulir").removeClass("d-none").addClass("d-flex");
            $("#emptyFormulir").addClass("d-none").removeClass("d-flex");

            $("#loadingPenilaian").removeClass("d-none").addClass("d-flex");
            $("#emptyPenilaian").addClass("d-none").removeClass("d-flex");
            $("#contentPenilaian").hide();

            $.ajax({
                url: "{{ route('databaseKPI.dashboardContent') }}",
                type: "GET",
                dataType: "json",
                success: function(response) {
                    dashboardData = response.dataCard_first || {};
                    const dataCardFirst = response.dataCard_first || {};
                    const sakit  = dataCardFirst.dataSakit?.totalAbsenSakit  ?? 0;
                    const cuti   = dataCardFirst.dataCuti?.totalAbsenCuti   ?? 0;
                    const izin   = dataCardFirst.dataIzin?.totalAbsenIzin   ?? 0;
                    const aktif  = dataCardFirst.karyawan_aktif             ?? 0;
                    const role   = "{{ auth()->user()->jabatan }}";
                    const labelType = (role === "HRD" || role === "GM" || role === "Direktur Utama") ? "Karyawan" : "Data";

                    $("#content_JK").text(`${aktif} Karyawan`);
                    $("#content_KS").text(`${sakit} ${labelType}`);
                    $("#content_KC").text(`${cuti} ${labelType}`);
                    $("#content_KI").text(`${izin} ${labelType}`);

                    const dataChart    = response.dataChartPenilaian || {};
                    const totalSemua   = dataChart.totalSemua ?? 0;
                    const totalDilaksanakan      = dataChart.totalDilaksanakan ?? 0;
                    const totalBelumDilaksanakan = dataChart.totalBelumDilaksanakan ?? 0;

                    $("#title_chartPenilaian").empty().append(
                        totalSemua ? `Penilaian Yang Diadakan : ${totalSemua} Penilaian` : ""
                    );

                    const chartEl1 = document.getElementById("myChart");
                    $("#loadingPenilaian").addClass("d-none").removeClass("d-flex");

                    if (totalSemua > 0) {
                        $("#emptyPenilaian").addClass("d-none").removeClass("d-flex");
                        $("#contentPenilaian").show();

                        if (chartEl1) {
                            const ctx1 = chartEl1.getContext("2d");
                            const gradientBlue = ctx1.createLinearGradient(0, 0, 0, 300);
                            gradientBlue.addColorStop(0, "#8F87F1");
                            gradientBlue.addColorStop(1, "#FED2E2");
                            const gradientWarning = ctx1.createLinearGradient(0, 0, 0, 300);
                            gradientWarning.addColorStop(0, "#fbbf24");
                            gradientWarning.addColorStop(1, "#f59e0b");

                            if (window.chart1) window.chart1.destroy();

                            window.chart1 = new Chart(ctx1, {
                                type: "doughnut",
                                data: {
                                    labels: ["Dilaksanakan", "Belum Dilaksanakan"],
                                    datasets: [{
                                        data: [Number(totalDilaksanakan), Number(totalBelumDilaksanakan)],
                                        backgroundColor: [gradientBlue, gradientWarning],
                                        borderWidth: 0
                                    }]
                                }
                            });
                        }
                    } else {
                        $("#contentPenilaian").hide();
                        $("#emptyPenilaian").removeClass("d-none").addClass("d-flex");
                        if (window.chart1) window.chart1.destroy();
                    }

                    const DataFormulir  = response.dataFormulir || {};
                    const TotalFormulir = DataFormulir.totalFormulir  ?? 0;
                    const totalRutin    = DataFormulir.totalRutin     ?? 0;
                    const totalProbation= DataFormulir.totalProbation ?? 0;
                    const totalKontrak  = DataFormulir.totalKontrak   ?? 0;

                    const chartEl2 = document.getElementById("doughnutCharthr");
                    $("#loadingFormulir").addClass("d-none").removeClass("d-flex");

                    if (TotalFormulir > 0) {
                        $("#emptyFormulir").addClass("d-none").removeClass("d-flex");

                        if (chartEl2) {
                            const ctx2 = chartEl2.getContext("2d");

                            const gradientPrimary = ctx2.createLinearGradient(0, 0, 0, 200);
                            gradientPrimary.addColorStop(0, "#a78bfa");
                            gradientPrimary.addColorStop(1, "#7c3aed");

                            const gradientInfo = ctx2.createLinearGradient(0, 0, 0, 200);
                            gradientInfo.addColorStop(0, "#38bdf8");
                            gradientInfo.addColorStop(1, "#0284c7");

                            const gradientWarning = ctx2.createLinearGradient(0, 0, 0, 200);
                            gradientWarning.addColorStop(0, "#fbbf24");
                            gradientWarning.addColorStop(1, "#f59e0b");

                            const gradientSuccess = ctx2.createLinearGradient(0, 0, 0, 200);
                            gradientSuccess.addColorStop(0, "#34d399");
                            gradientSuccess.addColorStop(1, "#059669");

                            if (window.chart2) window.chart2.destroy();

                            window.chart2 = new Chart(ctx2, {
                                type: "doughnut",
                                data: {
                                    labels: ["Total", "Rutin", "Probation", "Kontrak"],
                                    datasets: [{
                                        data: [Number(TotalFormulir), Number(totalRutin), Number(totalProbation), Number(totalKontrak)],
                                        backgroundColor: [gradientPrimary, gradientInfo, gradientWarning, gradientSuccess],
                                        borderWidth: 0
                                    }]
                                },
                                options: {
                                    plugins: { legend: { display: false } },
                                    responsive: true,
                                    maintainAspectRatio: false
                                }
                            });
                        }

                        $("#totalFormulir").html(
                            `<p class="fw-bold fs-5 mb-1">${TotalFormulir}</p>
                             <p class="mb-0 small d-flex align-items-center text-muted">
                                <span class="me-2 legend-box" style="background:linear-gradient(135deg,#a78bfa,#7c3aed);"></span> Total
                             </p>`
                        );
                        $("#totalRutin").html(
                            `<p class="fw-bold fs-5 mb-1">${totalRutin}</p>
                             <p class="mb-0 small d-flex align-items-center text-muted">
                                <span class="me-2 legend-box" style="background:linear-gradient(135deg,#38bdf8,#0284c7);"></span> Rutin
                             </p>`
                        );
                        $("#totalProbation").html(
                            `<p class="fw-bold fs-5 mb-1">${totalProbation}</p>
                             <p class="mb-0 small d-flex align-items-center text-muted">
                                <span class="me-2 legend-box" style="background:linear-gradient(135deg,#fbbf24,#f59e0b);"></span> Probation
                             </p>`
                        );
                        $("#totalKontrak").html(
                            `<p class="fw-bold fs-5 mb-1">${totalKontrak}</p>
                             <p class="mb-0 small d-flex align-items-center text-muted">
                                <span class="me-2 legend-box" style="background:linear-gradient(135deg,#34d399,#059669);"></span> Kontrak
                             </p>`
                        );

                    } else {
                        $("#emptyFormulir").removeClass("d-none").addClass("d-flex");
                        if (window.chart2) window.chart2.destroy();
                        $("#totalFormulir, #totalRutin, #totalProbation, #totalKontrak").html(
                            `<span class="text-muted small">-</span>`);
                    }

                    const select = $("#select_peringkatPenilaian");
                    select.off("change").empty();
                    const Divisi = response.dataDivisi || [];

                    if (!Divisi.length) {
                        $(".row-ranking").html(
                            '<div class="col-12 text-center text-muted py-5">Belum ada data divisi untuk ditampilkan</div>'
                        );
                        return;
                    }

                    Divisi.forEach(d => select.append(`<option value="${d.divisi}">${d.divisi}</option>`));
                    const defaultDivisi = Divisi[0].divisi;
                    select.val(defaultDivisi);
                    renderPeringkat(defaultDivisi);

                    select.on("change", function() {
                        renderPeringkat($(this).val());
                    });

                    function renderPeringkat(divisi) {
                        let allData = (response.dataRangking || [])
                            .filter(i => i.divisi === divisi)
                            .sort((a, b) => b.total_nilai - a.total_nilai);

                        const dataFiltered = allData.filter(i => Number(i.total_nilai) > 0);
                        const cardContainer = $(".row-ranking");
                        const bodyContentPeringkat = $("#bodyContentPeringkat");

                        cardContainer.empty();
                        bodyContentPeringkat.empty();

                        $("#title_peringkat").text(`Terbaik Divisi ${divisi}`);

                        if (!dataFiltered.length) {
                            cardContainer.append(`
                                <div class="col-12">
                                    <div class="p-4 text-center rounded-4 bg-light">
                                        <img src="{{ asset('template_KPI/dist/assets/images/screenshots/gambar_pencarian.png') }}" width="80%" height="250" style="opacity:.5">
                                        <h6 class="text-muted mt-3">Belum ada karyawan yang memiliki peringkat di divisi ini</h6>
                                    </div>
                                </div>
                            `);
                            return;
                        }

                        const top3 = dataFiltered.slice(0, 3);
                        top3.forEach((item, index) => {
                            const posisi = index + 1;
                            const podiumClass = posisi === 1 ? 'podium-1' : posisi === 2 ? 'podium-2' : 'podium-3';
                            const baseUrl = "{{ asset('storage') }}";
                            const defaultFoto = "{{ asset('template_KPI/dist/assets/images/screenshots/user-profile.jpg') }}";
                            const foto = item.foto ? `${baseUrl}/${item.foto}` : defaultFoto;

                            cardContainer.append(`
                                <div class="col-12 col-sm-6 col-lg-4 text-center mb-4">
                                    <div class="ranking-card p-4 rounded-4 text-white ${podiumClass}" style="box-shadow: 0 6px 20px rgba(0,0,0,.1);">
                                        <img src="${foto}" class="rounded-circle border border-4 border-white my-3" style="width:110px;height:110px;object-fit:cover;">
                                        <h6 class="fw-bold mb-1">${item.nama_karyawan}</h6>
                                        <small class="opacity-75">${item.divisi}</small>
                                    </div>
                                    <h5 class="mt-3 fw-bold text-dark">${posisi}</h5>
                                </div>
                            `);
                        });

                        let lastScore = null;
                        let rank = 0;
                        let shown = 0;

                        allData.forEach(item => {
                            if (item.total_nilai !== lastScore) {
                                rank = shown + 1;
                            }
                            lastScore = item.total_nilai;
                            shown++;

                            bodyContentPeringkat.append(`
                                <div class="d-flex align-items-center mb-3 p-3 rounded-3 plain-card">
                                    <span class="me-3 fw-bold text-primary" style="min-width:28px;">${rank}.</span>
                                    <div class="flex-grow-1">
                                        <div class="fw-bold text-dark">${item.nama_karyawan}</div>
                                        <small class="text-muted">${item.divisi}</small>
                                    </div>
                                    <div class="fw-bold me-3 text-dark">${item.total_nilai}</div>
                                    <div class="progress flex-grow-1" style="max-width:250px;height:10px;background:#f1f5f9;">
                                        <div class="progress-bar" style="width:${item.total_nilai}%;background:linear-gradient(90deg,#6366f1,#a78bfa);"></div>
                                    </div>
                                </div>
                            `);
                        });
                    }
                },
                error: function(xhr, status, error) {
                    $("#loadingFormulir, #loadingPenilaian").addClass("d-none").removeClass("d-flex");
                    $(".row-ranking").html(
                        '<div class="col-12 text-center text-warning py-5">Gagal memuat data dari server</div>'
                    );
                    $("#content_JK, #content_KS, #content_KC, #content_KI").text("-");
                }
            });
        }

        function renderModalData(type) {
            const loadingId = `#loadingModal${type}`;
            const contentId = `#contentModal${type}`;
            const dataKey = `data${type}`;
            const arrayKey = `data${type}`;

            $(loadingId).removeClass('d-none').addClass('d-flex');
            $(contentId).addClass('d-none').empty();

            setTimeout(() => {
                $(loadingId).addClass('d-none').removeClass('d-flex');
                $(contentId).removeClass('d-none');

                if (!dashboardData || !dashboardData[dataKey]) {
                    $(contentId).html('<p class="text-center text-muted py-4">Data tidak tersedia.</p>');
                    return;
                }

                const items = dashboardData[dataKey][arrayKey] || [];
                const total = dashboardData[dataKey][`totalAbsen${type}`] || 0;

                if (items.length === 0) {
                    $(contentId).html(`
                        <div class="text-center py-4">
                            <i class="fas fa-inbox fa-2x text-muted mb-2 opacity-50"></i>
                            <p class="text-muted mb-0">Tidak ada data ${type.toLowerCase()} untuk periode ${dashboardData.semester || 'ini'}.</p>
                        </div>
                    `);
                } else {
                    let tableHtml = `
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="fw-bold mb-0">Total: ${total} Catatan</h6>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle table-sm">
                                <thead class="table-light">
                                    <tr>
                                        <th>Nama Karyawan</th>
                                        <th>Divisi</th>
                                        <th>Alasan</th>
                                        <th>Tanggal</th>
                                    </tr>
                                </thead>
                                <tbody>
                    `;

                    items.forEach(item => {
                        const tanggal = item.tanggalAwal === item.tanggalAkhir 
                            ? item.tanggalAwal 
                            : `${item.tanggalAwal} s/d ${item.tanggalAkhir}`;

                        tableHtml += `
                            <tr>
                                <td class="fw-semibold text-dark">${item.namaKaryawan || '-'}</td>
                                <td><span class="badge bg-light text-dark border">${item.divisi || '-'}</span></td>
                                <td>${item.alasan || '-'}</td>
                                <td><small class="text-muted"><i class="far fa-calendar-alt me-1"></i>${tanggal}</small></td>
                            </tr>
                        `;
                    });

                    tableHtml += `</tbody></table></div>`;
                    $(contentId).html(tableHtml);
                }
            }, 300);
        }

        function loadProgressData() {
            $.ajax({
                url: "{{ route('kpi.getProgressDasboard') }}",
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    renderOutput1(response.output_1);
                    renderOutput2(response.output_2);
                    renderOutput3(response.output_3);
                    renderOutput4(response.output_4);
                },
                error: function(xhr, status, error) {
                    if ($('#contentKPIPersonal').length > 0) $('#contentKPIPersonal').html(
                        '<div class="text-center py-5 text-warning">Gagal memuat data personal</div>');
                    if ($('#contentKPITim').length > 0) $('#contentKPITim').html(
                        '<div class="text-center py-5 text-warning">Gagal memuat data tim</div>');
                    if ($('#contentKPIDivisi').length > 0) $('#contentKPIDivisi').html(
                        '<div class="text-center py-4 text-warning">Gagal memuat data assessment</div>');
                    $('#companyProgressRow').hide();
                }
            });
        }

        function renderOutput1(data) {
            const contentKPIPersonal = $('#contentKPIPersonal');
            if (contentKPIPersonal.length === 0) return;

            contentKPIPersonal.empty();
            if (!data || data.titleGet_data === "Tidak ada data") {
                contentKPIPersonal.append(`
                    <div class="d-flex flex-column justify-content-center align-items-center text-center h-100 py-5">
                        <div style="font-size:60px;color:#a78bfa;">≈</div>
                        <h5 class="fw-semibold mt-3 mb-2 text-dark">Belum Ada Data KPI</h5>
                        <p class="text-muted small mb-4" style="max-width:320px;">Data performa personal belum tersedia. KPI akan muncul setelah target dan penilaian dibuat.</p>
                        <span class="badge bg-light text-muted px-3 py-2">Menunggu Data</span>
                    </div>
                `);
                return;
            }

            let performanceColor = "warning";
            let performanceIcon  = "∿";

            if (data.performance_title === "Naik") {
                performanceColor = "success";
                performanceIcon  = "↑";
            } else if (data.performance_title === "Turun") {
                performanceColor = "warning";
                performanceIcon  = "↓";
            }

            let monthlyHTML = "";
            data.progress_kpi_perbulan.forEach((item, index) => {
                const bulanShort = item.bulan.split(" ")[0].substring(0, 3);
                monthlyHTML += `
                    <div class="col">
                        <div class="fw-semibold ${index === data.progress_kpi_perbulan.length - 1 ? 'text-primary fw-bold' : 'text-dark'}">
                            ${item.nilai}%
                        </div>
                        <div class="small text-muted">${bulanShort}</div>
                    </div>
                `;
            });

            contentKPIPersonal.append(`
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h1 class="fw-bold mb-1 text-dark" data-value="${data.nilai_kpi_anda}">${data.nilai_kpi_anda}%</h1>
                        <div class="small fw-semibold text-${performanceColor}">
                            <i class="fas fa-arrow-up me-1"></i>
                            ${performanceIcon} ${data.performance}% dari bulan lalu
                        </div>
                    </div>
                    <div class="text-end">
                        <div class="small text-muted mt-2">Performa ${data.performance_title}</div>
                    </div>
                </div>
                <div class="mb-4">
                    <div class="d-flex justify-content-between small text-muted mb-2">
                        <span>Progress KPI</span>
                        <span class="fw-semibold text-dark">${data.nilai_kpi_anda}%</span>
                    </div>
                    <div class="progress" style="height:8px;background:#f1f5f9;">
                        <div class="progress-bar progress-animated" data-value="${data.nilai_kpi_anda}"
                             style="width:0%;background:linear-gradient(90deg,#6366f1,#a78bfa);"></div>
                    </div>
                </div>
                <div class="border-top pt-3 mb-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="small text-muted">Deadline</div>
                            <div class="fw-semibold text-dark">${new Date(data.deadline).toLocaleDateString('id-ID', { day:'numeric', month:'long', year:'numeric' })}</div>
                        </div>
                        <span class="badge bg-light text-dark">${data.countdown}</span>
                    </div>
                </div>
                <div class="mt-auto">
                    <div class="small text-muted mb-3">Riwayat Bulanan</div>
                    <div class="row text-center g-3">${monthlyHTML}</div>
                </div>
            `);

            setTimeout(() => {
                $('.progress-animated').each(function() {
                    let value = $(this).data('value');
                    $(this).css('width', value + '%');
                });
            }, 200);
        }

        function renderOutput2(data) {
            const container = $('#contentKPITim');
            if (container.length === 0) return;
            container.empty();

            if (!data || (data.total_kpi_tracked ?? 0) === 0) {
                container.append('<div class="text-center text-muted py-4">Belum ada data KPI</div>');
                return;
            }

            const summary = data.summary_cards || { total_kpi_tracked: 0, avg_progress: 0, on_track_percentage: 0 };
            const health = data.health_donut || { on_track: 0, at_risk: 0, behind: 0 };
            const kpiCards = data.kpi_cards || [];
            const insights = data.insights_feed || [];
            const leaderboard = data.leaderboard || { top: [], lowest: [] };

            container.append(`
                <div class="row g-3">
                    <div class="col-4">
                        <div class="kpi-summary-box text-center">
                            <div class="fw-bold fs-4 text-dark">${summary.total_kpi_tracked}</div>
                            <div class="small text-muted">Total KPI</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="kpi-summary-box text-center">
                            <div class="fw-bold fs-4 text-dark">${summary.avg_progress}%</div>
                            <div class="small text-muted">Rata-rata Progress</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="kpi-summary-box text-center">
                            <div class="fw-bold fs-4 text-success">${summary.on_track_percentage}%</div>
                            <div class="small text-muted">On Track</div>
                        </div>
                    </div>
                </div>
            `);

            container.append(`
                <div class="row g-3 align-items-center">
                    <div class="col-5">
                        <canvas id="kpiHealthDonutChart" height="140"></canvas>
                    </div>
                    <div class="col-7 d-flex flex-column gap-2">
                        <div class="health-legend-item"><span class="legend-box" style="background:#34d399;"></span> On Track: ${health.on_track}</div>
                        <div class="health-legend-item"><span class="legend-box" style="background:#fbbf24;"></span> At Risk: ${health.at_risk}</div>
                        <div class="health-legend-item"><span class="legend-box" style="background:#ef4444;"></span> Behind: ${health.behind}</div>
                    </div>
                </div>
            `);

            let kpiCardsHtml = '<div class="row g-3">';
            kpiCards.forEach(card => {
                const values = Object.values(card.sparkline || {});
                const max = Math.max(1, ...values.map(v => Number(v) || 0));
                let bars = '';
                values.forEach(v => {
                    const h = Math.max(8, Math.round((Number(v) / max) * 100));
                    bars += `<div class="sparkline-bar" style="height:${h}%;"></div>`;
                });

                let statusColor = 'warning';
                if (card.status === 'on_track') statusColor = 'success';
                else if (card.status === 'behind') statusColor = 'danger';

                let trendIcon = '∿';
                if (card.trend === 'up') trendIcon = '↑';
                else if (card.trend === 'down') trendIcon = '↓';

                kpiCardsHtml += `
                    <div class="col-md-6">
                        <div class="kpi-mini-card">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h6 class="mb-0 fw-semibold text-dark text-truncate" style="max-width:70%;" title="${card.kpi_title}">${card.kpi_title}</h6>
                                <span class="badge bg-${statusColor} bg-opacity-10 text-${statusColor} fw-bold">${card.progress}%</span>
                            </div>
                            <div class="sparkline mb-2">${bars}</div>
                            <small class="text-muted">${trendIcon} ${card.trend_value}% tren</small>
                        </div>
                    </div>
                `;
            });
            kpiCardsHtml += '</div>';
            container.append(kpiCardsHtml);

            if (insights.length > 0) {
                let insightHtml = '<div class="mt-2"><h6 class="fw-bold text-dark mb-2">Insight</h6>';
                insights.forEach(item => {
                    insightHtml += `
                        <div class="p-2 mb-2 rounded-3" style="background:#f8fafc;">
                            <div class="fw-semibold text-dark small">${item.kpi_title}</div>
                            <div class="small text-muted">${item.insight}</div>
                        </div>
                    `;
                });
                insightHtml += '</div>';
                container.append(insightHtml);
            }

            if ((leaderboard.top || []).length > 0 || (leaderboard.lowest || []).length > 0) {
                let leaderboardHtml = '<div class="row g-3 mt-1">';
                leaderboardHtml += '<div class="col-6"><h6 class="fw-bold text-dark mb-2">Top Performer</h6>';
                (leaderboard.top || []).forEach(item => {
                    leaderboardHtml += `<div class="small text-muted mb-1">${item.label} <span class="text-dark fw-semibold">(${item.value})</span></div>`;
                });
                leaderboardHtml += '</div>';
                leaderboardHtml += '<div class="col-6"><h6 class="fw-bold text-dark mb-2">Terendah</h6>';
                (leaderboard.lowest || []).forEach(item => {
                    leaderboardHtml += `<div class="small text-muted mb-1">${item.label} <span class="text-dark fw-semibold">(${item.value})</span></div>`;
                });
                leaderboardHtml += '</div></div>';
                container.append(leaderboardHtml);
            }

            const donutCanvas = document.getElementById('kpiHealthDonutChart');
            if (donutCanvas) {
                if (window.kpiHealthChart) window.kpiHealthChart.destroy();
                window.kpiHealthChart = new Chart(donutCanvas.getContext('2d'), {
                    type: 'doughnut',
                    data: {
                        labels: ['On Track', 'At Risk', 'Behind'],
                        datasets: [{
                            data: [health.on_track, health.at_risk, health.behind],
                            backgroundColor: ['#34d399', '#fbbf24', '#ef4444'],
                            borderWidth: 0
                        }]
                    },
                    options: {
                        plugins: { legend: { display: false } },
                        responsive: true,
                        maintainAspectRatio: false
                    }
                });
            }
        }

        function renderOutput3(data) {
            const container = $('#contentKPIDivisi');
            if (container.length === 0) return;
            container.empty();

            if (!data || (data.total_feedback_received ?? 0) === 0) {
                container.html(`
                    <div class="d-flex justify-content-center align-items-center w-100 text-muted py-4">
                        <span class="small">Belum ada data penilaian.</span>
                    </div>
                `);
                return;
            }

            container.append(`
                <div class="text-center mb-3">
                    <h2 class="fw-bold text-dark mb-0">${data.total_score}%</h2>
                    <small class="text-muted">Total Skor Penilaian 360°</small>
                </div>
                <div style="height:260px;">
                    <canvas id="assessment360RadarChart"></canvas>
                </div>
            `);

            let barsHtml = '<div class="mt-3">';
            (data.breakdown_bars || []).forEach(item => {
                let barColor = 'linear-gradient(90deg,#fbbf24,#f59e0b)';
                if (item.rata_rata_nilai >= 80) barColor = 'linear-gradient(90deg,#34d399,#059669)';
                else if (item.rata_rata_nilai >= 50) barColor = 'linear-gradient(90deg,#6366f1,#a78bfa)';

                barsHtml += `
                    <div class="mb-2">
                        <div class="d-flex justify-content-between small text-muted mb-1">
                            <span>${item.jenis}</span>
                            <span class="fw-semibold text-dark">${item.rata_rata_nilai}</span>
                        </div>
                        <div class="progress" style="height:6px;background:#f1f5f9;">
                            <div class="progress-bar" style="width:${item.rata_rata_nilai}%;background:${barColor};"></div>
                        </div>
                    </div>
                `;
            });
            barsHtml += '</div>';
            container.append(barsHtml);

            let chipsHtml = '<div class="mt-3">';
            if ((data.strengths || []).length > 0) {
                chipsHtml += '<div class="mb-2"><small class="text-muted d-block mb-1">Kekuatan</small>';
                data.strengths.forEach(item => {
                    chipsHtml += `<span class="chip-strength">${item.jenis}</span>`;
                });
                chipsHtml += '</div>';
            }
            if ((data.growth_areas || []).length > 0) {
                chipsHtml += '<div><small class="text-muted d-block mb-1">Area Pengembangan</small>';
                data.growth_areas.forEach(item => {
                    chipsHtml += `<span class="chip-growth">${item.jenis}</span>`;
                });
                chipsHtml += '</div>';
            }
            chipsHtml += '</div>';
            container.append(chipsHtml);

            const radarCanvas = document.getElementById('assessment360RadarChart');
            if (radarCanvas) {
                const radarData = data.radar_chart || [];
                if (window.assessment360Chart) window.assessment360Chart.destroy();
                window.assessment360Chart = new Chart(radarCanvas.getContext('2d'), {
                    type: 'radar',
                    data: {
                        labels: radarData.map(item => item.axis),
                        datasets: [{
                            label: 'Nilai',
                            data: radarData.map(item => item.value),
                            backgroundColor: 'rgba(99,102,241,0.15)',
                            borderColor: '#6366f1',
                            pointBackgroundColor: '#6366f1'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            r: { beginAtZero: true, max: 5 }
                        },
                        plugins: { legend: { display: false } }
                    }
                });
            }
        }

        function renderOutput4(data) {
            const row = $('#companyProgressRow');
            if (!data) {
                row.hide();
                return;
            }
            row.show();

            const trend = data.company_trend || { historical: {}, forecast: {} };
            const historical = trend.historical || {};
            const forecast = trend.forecast || {};
            const overview = data.overview || [];

            const historicalLabels = Object.keys(historical);
            const forecastLabels = Object.keys(forecast);
            const allLabels = [...historicalLabels, ...forecastLabels];

            const historicalValues = historicalLabels.map(k => historical[k]);
            const lastHistoricalValue = historicalValues.length ? historicalValues[historicalValues.length - 1] : null;

            const solidData = [...historicalValues, ...forecastLabels.map(() => null)];
            const dashedData = [
                ...historicalLabels.map(() => null),
            ];
            if (historicalLabels.length > 0) {
                dashedData[historicalLabels.length - 1] = lastHistoricalValue;
            }
            forecastLabels.forEach(k => dashedData.push(forecast[k]));

            const canvas = document.getElementById('companyProgressChart');
            if (canvas) {
                const existingChart = Chart.getChart(canvas);
                if (existingChart) existingChart.destroy();
                
                window.companyProgressChartInstance = new Chart(canvas.getContext('2d'), {
                    type: 'line',
                    data: {
                        labels: allLabels,
                        datasets: [
                            {
                                label: 'Historis',
                                data: solidData,
                                borderColor: '#6366f1',
                                backgroundColor: 'rgba(99,102,241,0.1)',
                                borderWidth: 3,
                                tension: 0.4,
                                fill: true,
                                spanGaps: false,
                                pointBackgroundColor: '#fff',
                                pointBorderColor: '#6366f1',
                                pointBorderWidth: 2,
                                pointRadius: 4
                            },
                            {
                                label: 'Proyeksi',
                                data: dashedData,
                                borderColor: '#f59e0b',
                                backgroundColor: 'transparent',
                                borderWidth: 3,
                                borderDash: [6, 6],
                                tension: 0.4,
                                fill: false,
                                spanGaps: true,
                                pointBackgroundColor: '#f59e0b',
                                pointBorderColor: '#f59e0b',
                                pointRadius: 4
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: true, position: 'top' } },
                        scales: {
                            y: { beginAtZero: true, max: 100, ticks: { callback: v => v + '%' } },
                            x: { grid: { display: false } }
                        }
                    }
                });
            }

            const listContainer = $('#companyDivisiOverview');
            listContainer.empty();

            if (overview.length === 0) {
                listContainer.html('<div class="text-center text-muted py-4 small">Belum ada data divisi.</div>');
                return;
            }

            overview.forEach(item => {
                let colorClass = 'warning';
                let barColor = 'linear-gradient(90deg,#fbbf24,#f59e0b)';
                if (item.avg_progress >= 80) {
                    colorClass = 'success';
                    barColor = 'linear-gradient(90deg,#34d399,#059669)';
                } else if (item.avg_progress >= 50) {
                    colorClass = 'primary';
                    barColor = 'linear-gradient(90deg,#6366f1,#a78bfa)';
                }

                const predictionDiff = item.avg_prediction - item.avg_progress;
                const predictionIcon = predictionDiff > 0 ? '↑' : (predictionDiff < 0 ? '↓' : '∿');
                const predictionColor = predictionDiff > 0 ? 'success' : (predictionDiff < 0 ? 'warning' : 'muted');

                listContainer.append(`
                    <div class="kpi-mini-card divisi-overview-item" style="cursor:pointer;" data-divisi="${item.divisi}">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <h6 class="mb-0 fw-semibold text-dark">${item.divisi}</h6>
                                <small class="text-muted">${item.total_kpi} KPI &middot; ${item.total_karyawan} Karyawan</small>
                            </div>
                            <span class="badge bg-${colorClass} bg-opacity-10 text-${colorClass} fw-bold">${item.avg_progress}%</span>
                        </div>
                        <div class="progress mb-2" style="height:6px;background:#e2e8f0;">
                            <div class="progress-bar" style="width:${item.avg_progress}%;background:${barColor};"></div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-${predictionColor}">${predictionIcon} Proyeksi: ${item.avg_prediction}%</small>
                            <small class="text-muted">
                                <span class="text-success">${item.health.on_track}</span> /
                                <span class="text-warning">${item.health.at_risk}</span> /
                                <span class="text-danger">${item.health.behind}</span>
                            </small>
                        </div>
                    </div>
                `);
            });

            $('.divisi-overview-item').off('click').on('click', function() {
                const divisi = $(this).data('divisi');
                openDivisiDrilldown(divisi);
            });
        }

        function openDivisiDrilldown(divisi) {
            $('#drilldownDivisiTitle').text('Detail Divisi: ' + divisi);
            $('#drilldownLoading').removeClass('d-none').addClass('d-flex');
            $('#drilldownContent').addClass('d-none');
            $('#drilldownEmpty').addClass('d-none');
            $('#modalDivisiDrilldown').modal('show');

            $.ajax({
                url: "{{ route('kpi.divisiDrilldown') }}",
                type: 'GET',
                data: { divisi: divisi },
                dataType: 'json',
                success: function(response) {
                    $('#drilldownLoading').addClass('d-none').removeClass('d-flex');
                    renderDrilldown(response);
                },
                error: function() {
                    $('#drilldownLoading').addClass('d-none').removeClass('d-flex');
                    $('#drilldownEmpty').removeClass('d-none').text('Gagal memuat detail divisi.');
                }
            });
        }

        function renderDrilldown(data) {
            const team = data.team || [];
            const monthlyProgress = data.monthly_progress || {};
            const insights = data.insights || [];

            if (team.length === 0 && Object.keys(monthlyProgress).length === 0) {
                $('#drilldownEmpty').removeClass('d-none');
                return;
            }

            $('#drilldownContent').removeClass('d-none');

            const chartCanvas = document.getElementById('drilldownChart');
            if (chartCanvas) {
                const existingChart = Chart.getChart(chartCanvas);
                if (existingChart) existingChart.destroy();
                
                window.drilldownChartInstance = new Chart(chartCanvas.getContext('2d'), {
                    type: 'line',
                    data: {
                        labels: Object.keys(monthlyProgress),
                        datasets: [{
                            label: 'Progress Divisi (%)',
                            data: Object.values(monthlyProgress),
                            borderColor: '#6366f1',
                            backgroundColor: 'rgba(99,102,241,0.1)',
                            borderWidth: 3,
                            tension: 0.4,
                            fill: true,
                            pointBackgroundColor: '#fff',
                            pointBorderColor: '#6366f1',
                            pointRadius: 4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            y: { beginAtZero: true, max: 100, ticks: { callback: v => v + '%' } },
                            x: { grid: { display: false } }
                        }
                    }
                });
            }

            const insightsContainer = $('#drilldownInsights');
            insightsContainer.empty();
            if (insights.length === 0) {
                insightsContainer.html('<div class="text-muted small">Belum ada insight untuk divisi ini.</div>');
            } else {
                insights.forEach(item => {
                    insightsContainer.append(`
                        <div class="p-2 rounded-3" style="background:#f8fafc;">
                            <div class="fw-semibold text-dark small">${item.kpi_title}</div>
                            <div class="small text-muted">${item.insight}</div>
                        </div>
                    `);
                });
            }

            const teamContainer = $('#drilldownTeamList');
            teamContainer.empty();
            if (team.length === 0) {
                teamContainer.html('<div class="text-center text-muted py-4 small">Belum ada anggota tim dengan KPI tercatat.</div>');
                return;
            }

            team.forEach((member, index) => {
                let barColor = 'linear-gradient(90deg,#fbbf24,#f59e0b)';
                if (member.progress >= 80) barColor = 'linear-gradient(90deg,#34d399,#059669)';
                else if (member.progress >= 50) barColor = 'linear-gradient(90deg,#6366f1,#a78bfa)';

                let trendIcon = '∿';
                let trendColor = 'muted';
                if (member.trend === 'up') { trendIcon = '↑'; trendColor = 'success'; }
                else if (member.trend === 'down') { trendIcon = '↓'; trendColor = 'warning'; }

                teamContainer.append(`
                    <div class="d-flex align-items-center gap-3 p-2 rounded-3" style="background:#f8fafc;">
                        <span class="fw-bold text-primary" style="min-width:24px;">${index + 1}.</span>
                        <div class="flex-grow-1">
                            <div class="fw-semibold text-dark small">${member.nama_karyawan}</div>
                            <small class="text-muted">${member.jabatan}</small>
                        </div>
                        <div class="text-end" style="min-width:70px;">
                            <div class="fw-bold text-dark small">${member.progress}%</div>
                            <small class="text-${trendColor}">${trendIcon}</small>
                        </div>
                        <div class="progress flex-grow-1" style="max-width:150px;height:6px;background:#e2e8f0;">
                            <div class="progress-bar" style="width:${member.progress}%;background:${barColor};"></div>
                        </div>
                    </div>
                `);
            });
        }

        const ChartStatsConfig = {
            API_URL: '/kpi-data/get-statistika',
            currentJabatan: 'all',
            currentBulan: 'all',
            currentTahun: new Date().getFullYear(),
            chartInstance: null
        };

        function fetchChartStatistics() {
            showLoading();
            hideContent();
            hideEmptyState();

            const params = {
                tahun: ChartStatsConfig.currentTahun,
                ...(ChartStatsConfig.currentJabatan !== 'all' && { jabatan: ChartStatsConfig.currentJabatan }),
                ...(ChartStatsConfig.currentBulan   !== 'all' && { bulan:  ChartStatsConfig.currentBulan })
            };

            $.ajax({
                url: ChartStatsConfig.API_URL,
                method: 'GET',
                data: params,
                dataType: 'json',
                success: function(response) {
                    hideLoading();
                    if (response.summary.total_targets === 0) {
                        showEmptyState();
                    } else {
                        renderData(response);
                        showContent();
                    }
                },
                error: function(xhr, status, error) {
                    hideLoading();
                    showEmptyState();
                    $('#emptyState h5').text('Gagal memuat data');
                    $('#emptyState p').text('Terjadi kesalahan: ' + error);
                }
            });
        }

        function renderData(data) {
            $('#overallAverage').text(data.summary.overall_average);
            $('#totalTargets').text(data.summary.total_targets);
            $('#achievedTargets').text(data.summary.achieved_targets);
            $('#completionRate').text(data.summary.completion_rate + '%');
            $('#countCompleted').text(data.summary.completed_targets);
            $('#countInProgress').text(data.summary.in_progress_targets);

            const completedWidth = data.summary.completion_rate;
            const inProgressWidth = 100 - completedWidth;

            $('#progressCompleted').css('width', completedWidth + '%').attr('aria-valuenow', completedWidth);
            $('#progressInProgress').css('width', inProgressWidth + '%').attr('aria-valuenow', inProgressWidth);

            renderChart(data.charts.monthly_trend);
            renderTargetsTable(data.targets_detail);
        }

        function renderChart(monthlyData) {
            const canvas = document.getElementById('mainChart');
            if (!canvas) return;

            const ctx = canvas.getContext('2d');
            const labels = Object.keys(monthlyData);
            const values = Object.values(monthlyData);

            if (ChartStatsConfig.chartInstance) ChartStatsConfig.chartInstance.destroy();

            ChartStatsConfig.chartInstance = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Rata-rata Progress (%)',
                        data: values,
                        borderColor: '#6366f1',
                        backgroundColor: 'rgba(99, 102, 241, 0.1)',
                        borderWidth: 3,
                        tension: 0.4,
                        fill: true,
                        pointBackgroundColor: '#fff',
                        pointBorderColor: '#6366f1',
                        pointBorderWidth: 2,
                        pointRadius: 5,
                        pointHoverRadius: 7
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: true, position: 'top' },
                        tooltip: {
                            backgroundColor: 'rgba(0,0,0,0.8)',
                            padding: 12,
                            cornerRadius: 8,
                            callbacks: {
                                label: function(context) {
                                    return 'Progress: ' + context.parsed.y + '%';
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100,
                            grid: { color: '#f0f0f0' },
                            ticks: { callback: function(value) { return value + '%'; } }
                        },
                        x: { grid: { display: false } }
                    }
                }
            });
        }

        function renderTargetsTable(targets) {
            const tbody = $('#targetsTableBody');
            tbody.empty();

            if (!targets || targets.length === 0) {
                tbody.append('<tr><td colspan="5" class="text-center text-muted py-4">Tidak ada data target</td></tr>');
                return;
            }

            targets.forEach(target => {
                const gap        = parseFloat(target.gap) || 0;
                const gapClass   = gap >= 0 ? 'text-success' : 'text-warning';
                const gapSign    = gap >= 0 ? '+' : '';

                // Format tampilan nilai aktual & target sesuai tipe
                let progressDisplay, targetDisplay;
                if (target.tipe_target === 'rupiah') {
                    progressDisplay = 'Rp ' + Number(target.raw_progress).toLocaleString('id-ID');
                    targetDisplay   = 'Rp ' + Number(target.target).toLocaleString('id-ID');
                } else if (target.tipe_target === 'persen') {
                    progressDisplay = target.progress + '%';
                    targetDisplay   = target.target + '%';
                } else {
                    progressDisplay = Number(target.raw_progress).toLocaleString('id-ID');
                    targetDisplay   = Number(target.target).toLocaleString('id-ID');
                }

                const progressClass = target.progress >= 100 ? 'bg-success' : 'bg-primary';

                const row = `
                    <tr>
                        <td class="ps-3">
                            <div class="fw-bold text-dark">${target.judul || '-'}</div>
                            <small class="text-muted">${target.asistant_route || ''}</small>
                        </td>
                        <td><span class="badge bg-light text-dark border px-2 py-1">${target.jabatan || '-'}</span></td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="progress flex-grow-1" style="height: 8px; border-radius: 4px; background:#f1f5f9;">
                                    <div class="progress-bar ${progressClass}" style="width: ${Math.min(target.progress, 100)}%" role="progressbar"></div>
                                </div>
                                <span class="small fw-bold" style="min-width: 55px;">${target.progress}%</span>
                            </div>
                            <small class="text-muted">${progressDisplay} / ${targetDisplay}</small>
                        </td>
                        <td class="text-muted">${targetDisplay}</td>
                        <td class="${gapClass} fw-bold">${gapSign}${gap.toFixed(1)}%</td>
                    </tr>
                `;
                tbody.append(row);
            });
        }

        function showLoading()    { $('#chartStatisticsContainer .loading-overlay').removeClass('d-none'); }
        function hideLoading()    { $('#chartStatisticsContainer .loading-overlay').addClass('d-none'); }
        function showContent()    { $('#contentArea').removeClass('d-none'); }
        function hideContent()    { $('#contentArea').addClass('d-none'); }
        function showEmptyState() { $('#emptyState').removeClass('d-none'); }
        function hideEmptyState() {
            $('#emptyState').addClass('d-none');
            $('#emptyState h5').text('Tidak ada data tersedia');
            $('#emptyState p').text('Silakan ubah filter untuk melihat data lainnya');
        }

        function resetFilters() {
            ChartStatsConfig.currentJabatan = 'all';
            ChartStatsConfig.currentBulan   = 'all';
            $('#jabatanPills .btn').removeClass('active').addClass('btn-outline-primary');
            $('#jabatanPills .btn[data-filter="all"]').addClass('active').removeClass('btn-outline-primary');
            $('#bulanPills .btn').removeClass('active').addClass('btn-light');
            $('#bulanPills .btn[data-bulan="all"]').addClass('active').removeClass('btn-light');
            fetchChartStatistics();
        }

        function setJabatanFilter(jabatan) {
            ChartStatsConfig.currentJabatan = jabatan;
            $('#jabatanPills .btn').removeClass('active').addClass('btn-outline-primary');
            $(`#jabatanPills .btn[data-filter="${jabatan}"]`).addClass('active').removeClass('btn-outline-primary');
            fetchChartStatistics();
        }

        function setBulanFilter(bulan) {
            ChartStatsConfig.currentBulan = bulan;
            $('#bulanPills .btn').removeClass('active').addClass('btn-light');
            $(`#bulanPills .btn[data-bulan="${bulan}"]`).addClass('active').removeClass('btn-light');
            fetchChartStatistics();
        }

        function setTahunFilter(tahun) {
            ChartStatsConfig.currentTahun = tahun;
            fetchChartStatistics();
        }
    </script>
@endsection