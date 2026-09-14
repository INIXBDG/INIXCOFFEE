@extends('layouts_kpi.app')

@section('kpi_contents')
    <link rel="stylesheet" href="{{ asset('assets/vendor/vendorStyle/penilaian360.css') }}">

    <script>
        window.personalConfig = {
            routes: {
                getData: `/penilaian360/get/{{ $id_karyawan ?? 0 }}`
            }
        };
    </script>

    <div class="container personal-content-wrapper">
        
        <div id="personal-skeleton-container">
            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="personal-content-card">
                        <div class="card-body">
                            <div class="personal-sk-header-row">
                                <div class="personal-skeleton personal-sk-title-large"></div>
                                <div class="personal-skeleton personal-sk-select"></div>
                            </div>
                            <div class="personal-skeleton personal-sk-title"></div>
                            <div class="personal-skeleton personal-sk-tabs"></div>
                            <div class="personal-skeleton personal-sk-title"></div>
                            <div class="personal-skeleton personal-sk-pills"></div>
                            <div class="personal-skeleton personal-sk-form-header"></div>
                            <div class="personal-skeleton personal-sk-criteria"></div>
                            <div class="personal-skeleton personal-sk-criteria"></div>
                            <div class="personal-skeleton personal-sk-criteria"></div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="personal-skeleton personal-sk-absensi-card"></div>
                </div>
            </div>
        </div>

        <div id="personal-real-container" style="display: none;">
            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="personal-content-card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4 pb-4" style="border-bottom: 1px solid #f1f5f9;">
                                <h5 class="personal-card-title mb-0">
                                    <i class="fa-solid fa-chart-simple"></i> Penilaian 360 Derajat
                                </h5>
                                <div class="d-flex align-items-center gap-3">
                                    <label class="fw-semibold text-secondary mb-0" style="font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.05em;">Periode:</label>
                                    <select id="selectPeriode" class="form-select form-select-sm" style="width: 200px; border-radius: 10px; border: 1px solid #e2e8f0; font-weight: 600; color: #334155; background-color: #ffffff; padding: 0.6rem 1rem;">
                                    </select>
                                </div>
                            </div>

                            <h5 class="personal-card-title">
                                <i class="fa-solid fa-layer-group"></i> Jenis Penilaian
                            </h5>
                            <div class="personal-tab-group" id="groupButtonJenisPenilaian">
                                <div class="personal-loading-state w-100">
                                    <i class="fa-solid fa-spinner fa-spin"></i>
                                    <p>Memuat jenis penilaian...</p>
                                </div>
                            </div>

                            <h5 class="personal-card-title">
                                <i class="fa-solid fa-user-check"></i> Pilih Evaluator
                            </h5>
                            <div class="personal-evaluator-pills" id="groupButtonEvaluator">
                                <div class="personal-empty-state w-100">
                                    <i class="fa-solid fa-hand-pointer"></i>
                                    <p>Pilih jenis penilaian terlebih dahulu</p>
                                </div>
                            </div>

                            <div id="formContainer">
                                <div class="personal-loading-state">
                                    <i class="fa-solid fa-spinner fa-spin"></i>
                                    <p>Memuat form penilaian...</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="personal-absensi-card">
                        <h5 class="personal-card-title" style="color: #f59e0b;">
                            <i class="fa-solid fa-calendar-check"></i> Data Absensi
                        </h5>
                        <table class="personal-absensi-table">
                            <thead>
                                <tr>
                                    <th><i class="fa-solid fa-clock me-1"></i> Telat</th>
                                    <th><i class="fa-solid fa-bed me-1"></i> Sakit</th>
                                    <th><i class="fa-solid fa-envelope me-1"></i> Izin</th>
                                </tr>
                            </thead>
                            <tbody id="content_body_absen">
                                <tr>
                                    <td colspan="3" style="color: #94a3b8; font-size: 0.9rem; font-weight: 500; background: transparent;">
                                        Memuat data...
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <div id="content_footer_absen">
                            <div class="personal-catatan-box">
                                <div class="catatan-label">
                                    <i class="fa-solid fa-note-sticky"></i> Informasi
                                </div>
                                <div class="catatan-content">Memuat data...</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('assets/js/penilaian360/personal-penilaian.js') }}"></script>
@endsection