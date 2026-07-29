@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<style>
    .sla-section-card {
        border-radius: 16px;
        background-color: rgba(255, 255, 255, 0.95);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
        border: none;
        margin-bottom: 2rem;
        display: flex !important;
        flex-direction: row !important;
        width: 100% !important;
    }
    .sla-section-header {
        background-color: #182f51;
        color: #ffffff;
        font-weight: 700;
        border-top-left-radius: 16px;
        border-bottom-left-radius: 16px;
        border-top-right-radius: 0px !important;
        border-bottom-right-radius: 0px !important;
        padding: 1.5rem 1rem;
        width: 200px !important;
        min-width: 200px !important;
        max-width: 200px !important;
        display: flex !important;
        flex-direction: column !important;
        justify-content: flex-start !important;
        align-items: center !important;
        text-align: center !important;
    }
    .kpi-card {
        border-radius: 12px;
        border: none;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        display: flex !important;
        flex-direction: column !important;
        width: 100% !important;
        height: auto !important;
        background-color: #ffffff !important;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075) !important;
        backdrop-filter: none !important;
    }
    .kpi-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.08) !important;
    }
    
    /* Reset card override for nested cards to behave as block/column layout stretching 100% */
    .sla-section-card .card {
        display: flex !important;
        flex-direction: column !important;
        width: 100% !important;
        height: auto !important;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.05) !important;
        background-color: #ffffff !important;
        backdrop-filter: none !important;
        border: 1px solid rgba(0, 0, 0, .125) !important;
    }
    
    .table-responsive {
        width: 100% !important;
    }
</style>

<div class="container-fluid py-4" style="background-image: url('/css/background inix office-02.svg'); background-size: cover; min-height: calc(100vh - 56px); overflow-y: auto;">
    
    <!-- Title Page -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm" style="border-radius: 16px; background-color: rgba(255, 255, 255, 0.85); backdrop-filter: blur(8px);">
                <div class="card-body p-4 d-flex justify-content-between align-items-center flex-wrap">
                    <div>
                        <h2 class="fw-bold mb-1" style="color: #182f51;">
                            <i class="fa-solid fa-gauge-high me-2"></i>SLA Management Dashboard
                        </h2>
                        <p class="text-muted mb-0">Dashboard Monitoring Pencapaian Seluruh SLA Layanan IT (ITSM)</p>
                    </div>
                    <div class="my-2">
                        <a href="{{ url('/home') }}" class="btn btn-outline-primary rounded-pill px-4">
                            <i class="fa-solid fa-arrow-left me-2"></i>Kembali ke Menu
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary KPI Bar -->
    <div class="row mb-4">
        <!-- Card 1: Programmer SLA -->
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card card-body text-center shadow-sm border-0 border-start border-4 border-primary kpi-card">
                <h6 class="text-muted small text-uppercase fw-bold">SLA Programmer (Resolusi)</h6>
                <div class="fs-2 fw-bold" id="top-prog-sla">...</div>
            </div>
        </div>
        <!-- Card 2: Technical Support SLA -->
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card card-body text-center shadow-sm border-0 border-start border-4 border-success kpi-card">
                <h6 class="text-muted small text-uppercase fw-bold">SLA Tech Support (Resolusi)</h6>
                <div class="fs-2 fw-bold" id="top-ts-sla">...</div>
            </div>
        </div>
        <!-- Card 3: Webinar SLA -->
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card card-body text-center shadow-sm border-0 border-start border-4 border-warning kpi-card">
                <h6 class="text-muted small text-uppercase fw-bold">SLA Webinar (Tepat Waktu)</h6>
                <div class="fs-2 fw-bold" id="top-event-sla">...</div>
            </div>
        </div>
        <!-- Card 4: Digital SLA -->
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card card-body text-center shadow-sm border-0 border-start border-4 border-info kpi-card">
                <h6 class="text-muted small text-uppercase fw-bold">SLA Digital (Konten)</h6>
                <div class="fs-2 fw-bold" id="top-digital-sla">...</div>
            </div>
        </div>
    </div>

    <!-- Details Sections Grid/Stack -->
    <div class="row">
        <div class="col-12">
            
            <!-- SECTION 1: SLA PROGRAMMER -->
            <div class="card sla-section-card shadow-sm">
                <div class="sla-section-header">
                    <i class="fa-solid fa-code me-2"></i>I. SLA Programmer
                </div>
                <div class="card-body p-4" id="sla-programmer-container">
                    <div id="sla-period-display" class="row mb-3">
                        <div class="col-md-12">
                            <div class="alert alert-primary mb-0" role="alert">
                                <h4 class="alert-heading mb-0 fs-5" id="sla_current_period">
                                    Memuat periode data...
                                </h4>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-12 mb-4">
                            <div class="card border-0 shadow-sm bg-light">
                                <div class="card-body">
                                    <div class="row mb-4">
                                        <div class="col-lg-3 col-md-6 mb-3">
                                            <div class="card card-body text-center h-100 shadow-sm border-0">
                                                <h6 class="card-title text-muted text-uppercase small">SLA Resolusi</h6>
                                                <div class="fs-2 fw-bold" id="tim-sla-resolution">...</div>
                                            </div>
                                        </div>
                                        <div class="col-lg-3 col-md-6 mb-3">
                                            <div class="card card-body text-center h-100 shadow-sm border-0">
                                                <h6 class="card-title text-muted text-uppercase small">SLA Respon</h6>
                                                <div class="fs-2 fw-bold" id="tim-sla-response">...</div>
                                            </div>
                                        </div>
                                        <div class="col-lg-3 col-md-6 mb-3">
                                            <div class="card card-body text-center h-100 shadow-sm border-0">
                                                <h6 class="card-title text-muted text-uppercase small">Avg. Waktu Resolusi</h6>
                                                <div class="fs-2 fw-bold" id="tim-avg-resolution">...</div>
                                            </div>
                                        </div>
                                        <div class="col-lg-3 col-md-6 mb-3">
                                            <div class="card card-body text-center h-100 shadow-sm border-0">
                                                <h6 class="card-title text-muted text-uppercase small">Total Tiket</h6>
                                                <div class="fs-2 fw-bold" id="tim-total-tickets">...</div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row" id="programmer-chart-row">
                                        <div class="col-12" style="position: relative; height:300px;">
                                            <canvas id="slaTimPriorityChart"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-12 mb-4">
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-white border-bottom-0 fw-bold fs-6 pt-3">
                                    <i class="bi bi-people-fill me-2 text-primary"></i>Kinerja SLA Per Programmer
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-striped table-hover align-middle">
                                            <thead class="table-dark">
                                                <tr>
                                                    <th>Nama Programmer</th>
                                                    <th>SLA Resolusi</th>
                                                    <th>SLA Respon</th>
                                                    <th>Avg. Resolusi (Jam)</th>
                                                    <th>Total Tiket</th>
                                                    <th>Detail (H/M/L/O)</th>
                                                </tr>
                                            </thead>
                                            <tbody id="sla-user-table-body">
                                                <tr>
                                                    <td colspan="6" class="text-center">Memuat data...</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-12">
                            <div class="card border-0 shadow-sm border-start border-4 border-danger">
                                <div class="card-header bg-white border-bottom-0 fw-bold fs-6 pt-3 text-danger">
                                    <i class="bi bi-exclamation-triangle-fill me-2"></i>Kinerja SLA Insiden Kritis (Programmer)
                                </div>
                                <div class="card-body">
                                    <div class="row mb-4">
                                        <div class="col-lg-3 col-md-6 mb-3">
                                            <div class="card card-body text-center h-100 shadow-sm border-0">
                                                <h6 class="card-title text-muted text-uppercase small">SLA Resolusi Kritis</h6>
                                                <div class="fs-2 fw-bold" id="kritis-sla-resolution">...</div>
                                            </div>
                                        </div>
                                        <div class="col-lg-3 col-md-6 mb-3">
                                            <div class="card card-body text-center h-100 shadow-sm border-0">
                                                <h6 class="card-title text-muted text-uppercase small">SLA Respon Kritis</h6>
                                                <div class="fs-2 fw-bold" id="kritis-sla-response">...</div>
                                            </div>
                                        </div>
                                        <div class="col-lg-3 col-md-6 mb-3">
                                            <div class="card card-body text-center h-100 shadow-sm border-0">
                                                <h6 class="card-title text-muted text-uppercase small">Avg. Waktu Resolusi</h6>
                                                <div class="fs-2 fw-bold" id="kritis-avg-resolution">...</div>
                                            </div>
                                        </div>
                                        <div class="col-lg-3 col-md-6 mb-3">
                                            <div class="card card-body text-center h-100 shadow-sm border-0">
                                                <h6 class="card-title text-muted text-uppercase small">Total Insiden</h6>
                                                <div class="fs-2 fw-bold" id="kritis-total-insiden">...</div>
                                            </div>
                                        </div>
                                    </div>

                                    <h5 class="fw-bold mb-3" style="color: #182f51;">Detail Insiden Kritis</h5>
                                    <div class="table-responsive">
                                        <table class="table table-striped table-hover align-middle">
                                            <thead class="table-dark">
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Laporan</th>
                                                    <th>SLA Met?</th>
                                                    <th>Waktu Resolusi (Jam)</th>
                                                    <th>Waktu Respon (Jam)</th>
                                                    <th>Responder</th>
                                                </tr>
                                            </thead>
                                            <tbody id="sla-kritis-table-body">
                                                <tr>
                                                    <td colspan="6" class="text-center">Memuat data...</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SECTION 2: SLA TECHNICAL SUPPORT -->
            <div class="card sla-section-card shadow-sm">
                <div class="sla-section-header">
                    <i class="fa-solid fa-headset me-2"></i>II. SLA Technical Support
                </div>
                <div class="card-body p-4" id="sla-tech-support-container">
                    <div id="sla-period-display-ts" class="row mb-3">
                        <div class="col-md-12">
                            <div class="alert alert-primary mb-0" role="alert">
                                <h4 class="alert-heading mb-0 fs-5" id="ts_sla_current_period">
                                    Memuat periode data...
                                </h4>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-12 mb-4">
                            <div class="card border-0 shadow-sm bg-light">
                                <div class="card-body">
                                    <div class="row mb-4">
                                        <div class="col-lg-3 col-md-6 mb-3">
                                            <div class="card card-body text-center h-100 shadow-sm border-0">
                                                <h6 class="card-title text-muted text-uppercase small">SLA Resolusi</h6>
                                                <div class="fs-2 fw-bold" id="ts-tim-sla-resolution">...</div>
                                            </div>
                                        </div>
                                        <div class="col-lg-3 col-md-6 mb-3">
                                            <div class="card card-body text-center h-100 shadow-sm border-0">
                                                <h6 class="card-title text-muted text-uppercase small">SLA Respon</h6>
                                                <div class="fs-2 fw-bold" id="ts-tim-sla-response">...</div>
                                            </div>
                                        </div>
                                        <div class="col-lg-3 col-md-6 mb-3">
                                            <div class="card card-body text-center h-100 shadow-sm border-0">
                                                <h6 class="card-title text-muted text-uppercase small">Avg. Waktu Resolusi</h6>
                                                <div class="fs-2 fw-bold" id="ts-tim-avg-resolution">...</div>
                                            </div>
                                        </div>
                                        <div class="col-lg-3 col-md-6 mb-3">
                                            <div class="card card-body text-center h-100 shadow-sm border-0">
                                                <h6 class="card-title text-muted text-uppercase small">Total Tiket</h6>
                                                <div class="fs-2 fw-bold" id="ts-tim-total-tickets">...</div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row" id="ts-chart-row">
                                        <div class="col-12" style="position: relative; height:300px;">
                                            <canvas id="tsSlaTimPriorityChart"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-12 mb-4">
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-white border-bottom-0 fw-bold fs-6 pt-3">
                                    <i class="bi bi-people-fill me-2 text-primary"></i>Kinerja SLA Per Technical Support
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-striped table-hover align-middle">
                                            <thead class="table-dark">
                                                <tr>
                                                    <th>Nama Technical Support</th>
                                                    <th>SLA Resolusi</th>
                                                    <th>SLA Respon</th>
                                                    <th>Avg. Resolusi (Jam)</th>
                                                    <th>Total Tiket</th>
                                                    <th>Detail (H/M/L/O)</th>
                                                </tr>
                                            </thead>
                                            <tbody id="ts-sla-user-table-body">
                                                <tr>
                                                    <td colspan="6" class="text-center">Memuat data...</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-12">
                            <div class="card border-0 shadow-sm border-start border-4 border-danger">
                                <div class="card-header bg-white border-bottom-0 fw-bold fs-6 pt-3 text-danger">
                                    <i class="bi bi-exclamation-triangle-fill me-2"></i>Kinerja SLA Insiden Kritis (Technical Support)
                                </div>
                                <div class="card-body">
                                    <div class="row mb-4">
                                        <div class="col-lg-3 col-md-6 mb-3">
                                            <div class="card card-body text-center h-100 shadow-sm border-0">
                                                <h6 class="card-title text-muted text-uppercase small">SLA Resolusi Kritis</h6>
                                                <div class="fs-2 fw-bold" id="ts-kritis-sla-resolution">...</div>
                                            </div>
                                        </div>
                                        <div class="col-lg-3 col-md-6 mb-3">
                                            <div class="card card-body text-center h-100 shadow-sm border-0">
                                                <h6 class="card-title text-muted text-uppercase small">SLA Respon Kritis</h6>
                                                <div class="fs-2 fw-bold" id="ts-kritis-sla-response">...</div>
                                            </div>
                                        </div>
                                        <div class="col-lg-3 col-md-6 mb-3">
                                            <div class="card card-body text-center h-100 shadow-sm border-0">
                                                <h6 class="card-title text-muted text-uppercase small">Avg. Waktu Resolusi</h6>
                                                <div class="fs-2 fw-bold" id="ts-kritis-avg-resolution">...</div>
                                            </div>
                                        </div>
                                        <div class="col-lg-3 col-md-6 mb-3">
                                            <div class="card card-body text-center h-100 shadow-sm border-0">
                                                <h6 class="card-title text-muted text-uppercase small">Total Insiden</h6>
                                                <div class="fs-2 fw-bold" id="ts-kritis-total-insiden">...</div>
                                            </div>
                                        </div>
                                    </div>

                                    <h5 class="fw-bold mb-3" style="color: #182f51;">Detail Insiden Kritis</h5>
                                    <div class="table-responsive">
                                        <table class="table table-striped table-hover align-middle">
                                            <thead class="table-dark">
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Laporan</th>
                                                    <th>SLA Met?</th>
                                                    <th>Waktu Resolusi (Jam)</th>
                                                    <th>Waktu Respon (Jam)</th>
                                                    <th>Responder</th>
                                                </tr>
                                            </thead>
                                            <tbody id="ts-sla-kritis-table-body">
                                                <tr>
                                                    <td colspan="6" class="text-center">Memuat data...</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SECTION 3: SLA WEBINAR -->
            <div class="card sla-section-card shadow-sm">
                <div class="sla-section-header">
                    <i class="fa-solid fa-video me-2"></i>III. SLA Webinar
                </div>
                <div class="card-body p-4">
                    <div id="event-sla-content" style="display: block;">
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <div class="alert alert-info d-flex justify-content-between align-items-center mb-0" role="alert">
                                    <div>
                                        <h4 class="alert-heading mb-0 fs-5" id="event-title">Memuat data...</h4>
                                        <small id="event-date" class="font-monospace">...</small>
                                    </div>
                                    <span class="badge bg-light text-dark border">Target: Overall Event SLA</span>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-lg-3 col-md-6 mb-3">
                                <div class="card card-body text-center h-100 shadow-sm border-start border-4 border-primary border-0">
                                    <h6 class="text-muted text-uppercase small">Kelengkapan</h6>
                                    <div class="fs-2 fw-bold" id="event-kpi-completion">0%</div>
                                    <small class="text-muted">Item Selesai / Total</small>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-6 mb-3">
                                <div class="card card-body text-center h-100 shadow-sm border-start border-4 border-success border-0">
                                    <h6 class="text-muted text-uppercase small">Tepat Waktu (SLA)</h6>
                                    <div class="fs-2 fw-bold" id="event-kpi-compliance">0%</div>
                                    <small class="text-muted">Dari item yang selesai</small>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-6 mb-3">
                                <div class="card card-body text-center h-100 shadow-sm border-start border-4 border-warning border-0">
                                    <h6 class="text-muted text-uppercase small">Terlambat</h6>
                                    <div class="fs-2 fw-bold text-warning" id="event-kpi-late">0</div>
                                    <small class="text-muted">Selesai tapi lewat deadline</small>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-6 mb-3">
                                <div class="card card-body text-center h-100 shadow-sm border-start border-4 border-danger border-0">
                                    <h6 class="text-muted text-uppercase small">Overdue</h6>
                                    <div class="fs-2 fw-bold text-danger" id="event-kpi-overdue">0</div>
                                    <small class="text-muted">Belum selesai & lewat deadline</small>
                                </div>
                            </div>
                        </div>

                        <div class="card-header fs-5 fw-semibold text-white mb-2" style="background-color: #182f51; border-radius: 8px;">
                            <i class="bi bi-list-check me-2"></i> Ringkasan Kinerja Per Event
                        </div>

                        <div class="card shadow-sm border-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0 align-middle w-100">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Bulan & Tema Webinar</th>
                                            <th>Tanggal Pelaksanaan (D-Day)</th>
                                            <th class="text-center">Kelengkapan</th>
                                            <th class="text-center">Kepatuhan SLA</th>
                                            <th class="text-center">Terlambat</th>
                                            <th class="text-center">Overdue</th>
                                        </tr>
                                    </thead>
                                    <tbody id="event-sla-table-body">
                                        <tr>
                                            <td colspan="6" class="text-center py-3">Memuat data...</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SECTION 4: SLA DIGITAL -->
            <div class="card sla-section-card shadow-sm">
                <div class="sla-section-header">
                    <i class="fa-solid fa-bullhorn me-2"></i>IV. SLA Digital
                </div>
                <div class="card-body p-4" id="sla-digital-container"
                    data-url="{{ \Illuminate\Support\Facades\Route::has('dashboard.digital') ? route('dashboard.digital') : url('/dashboard/digital') }}">
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <div class="alert alert-info mb-0" role="alert">
                                <h4 class="alert-heading mb-0 fs-5" id="digital_sla_period">
                                    Memuat periode data...
                                </h4>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-lg-6 mb-4">
                            <div class="card shadow-sm h-100 border-primary border-start border-4 border-0">
                                <div class="card-header bg-white fw-bold">
                                    <i class="bi bi-camera-reels-fill me-2 text-primary"></i> SLA Jadwal Konten
                                </div>
                                <div class="card-body">
                                    <div class="row text-center">
                                        <div class="col-md-6 mb-3">
                                            <h6 class="text-muted small text-uppercase">Kepatuhan Upload (Min 3/Minggu)</h6>
                                            <div class="fs-1 fw-bold" id="digital-content-sla">...</div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <h6 class="text-muted small text-uppercase">Total Konten Uploaded</h6>
                                            <div class="fs-1 fw-bold text-dark" id="digital-content-total">...</div>
                                        </div>
                                        <div class="col-12">
                                            <span class="badge bg-light text-dark border p-2">
                                                Target Terpenuhi: <span id="digital-weeks-met" class="fw-bold">...</span> dari <span id="digital-weeks-total">...</span> Minggu
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6 mb-4">
                            <div class="card shadow-sm h-100 border-warning border-start border-4 border-0">
                                <div class="card-header bg-white fw-bold">
                                    <i class="bi bi-ticket-detailed-fill me-2 text-warning"></i> SLA Ticketing (Support)
                                </div>
                                <div class="card-body">
                                    <div class="row text-center">
                                        <div class="col-md-4 mb-3">
                                            <h6 class="text-muted small text-uppercase">SLA Resolusi</h6>
                                            <div class="fs-2 fw-bold" id="digital-ticket-res-sla">...</div>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <h6 class="text-muted small text-uppercase">SLA Respon</h6>
                                            <div class="fs-2 fw-bold" id="digital-ticket-resp-sla">...</div>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <h6 class="text-muted small text-uppercase">Avg Resolusi</h6>
                                            <div class="fs-2 fw-bold text-secondary" id="digital-ticket-avg">...</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <div class="card-header text-white fs-5 fw-semibold mb-2" style="background-color: #182f51; border-radius: 8px;">
                                <i class="bi bi-calendar-week me-2"></i> Detail Pencapaian Mingguan
                            </div>
                            <div class="card shadow-sm border-0">
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-striped table-hover mb-0 align-middle">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Periode Minggu</th>
                                                    <th class="text-center">Jumlah Upload</th>
                                                    <th class="text-center">Target</th>
                                                    <th class="text-center">Status</th>
                                                </tr>
                                            </thead>
                                            <tbody id="digital-weekly-table-body">
                                                <tr>
                                                    <td colspan="4" class="text-center py-3">Memuat data...</td>
                                                </tr>
                                            </tbody>
                                        </table>
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
@endsection

@push('js')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="{{ asset('js/sla-management.js') }}"></script>
@endpush
